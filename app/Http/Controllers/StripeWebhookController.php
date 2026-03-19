<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Support\PaymentMetadataNormalizer;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Support\TableExistenceCache;
class StripeWebhookController extends Controller
{
    private ?bool $webhookEventStoreAvailable = null;

    private function stripeMetadataToArray($metadata): array
    {
        if (empty($metadata)) {
            return [];
        }

        if (is_array($metadata)) {
            return $metadata;
        }

        if (is_object($metadata) && method_exists($metadata, 'toArray')) {
            return (array) $metadata->toArray();
        }

        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return (array) $metadata;
    }

    private function paymentIntentMetadata($paymentIntent): array
    {
        return PaymentMetadataNormalizer::normalize(
            $this->stripeMetadataToArray($paymentIntent->metadata ?? [])
        );
    }

    private function resolveUserIdFromPaymentIntent($paymentIntent, array $metadata): ?int
    {
        $metaUserId = (int) ($metadata['user_id'] ?? 0);
        if ($metaUserId > 0) {
            return $metaUserId;
        }

        $customerId = (string) ($paymentIntent->customer ?? '');
        if ($customerId !== '') {
            $customerUserId = (int) (User::where('stripe_customer_id', $customerId)->value('id') ?? 0);
            if ($customerUserId > 0) {
                return $customerUserId;
            }
        }

        $email = trim((string) ($metadata['user_email'] ?? ($paymentIntent->receipt_email ?? '')));
        if ($email !== '') {
            $emailUserId = (int) (User::where('email', $email)->value('id') ?? 0);
            if ($emailUserId > 0) {
                return $emailUserId;
            }
        }

        return null;
    }

    private function normalizeTransactionType(?string $type): string
    {
        return PaymentMetadataNormalizer::normalizeTransactionType($type) ?: 'payment';
    }

    private function hasWebhookEventStore(): bool
    {
        if ($this->webhookEventStoreAvailable !== null) {
            return $this->webhookEventStoreAvailable;
        }

        try {
            $this->webhookEventStoreAvailable = TableExistenceCache::has('stripe_webhook_events');
        } catch (\Throwable $e) {
            $this->webhookEventStoreAvailable = false;
        }

        return $this->webhookEventStoreAvailable;
    }

    private function reserveWebhookEvent(string $eventId, ?string $eventType, string $payloadHash): ?object
    {
        if (!$this->hasWebhookEventStore()) {
            return null;
        }

        try {
            return DB::transaction(function () use ($eventId, $eventType, $payloadHash) {
                $existing = DB::table('stripe_webhook_events')
                    ->where('event_id', $eventId)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    if ($existing->status === 'processed') {
                        return $existing;
                    }

                    if (
                        $existing->status === 'processing'
                        && !empty($existing->updated_at)
                        && \Illuminate\Support\Carbon::parse($existing->updated_at)->greaterThan(now()->subMinutes(10))
                    ) {
                        $existing->status = 'processing_duplicate';
                        return $existing;
                    }

                    DB::table('stripe_webhook_events')
                        ->where('id', $existing->id)
                        ->update([
                            'status' => 'processing',
                            'event_type' => $eventType ?: $existing->event_type,
                            'payload_hash' => $payloadHash,
                            'attempts' => ((int) ($existing->attempts ?? 0)) + 1,
                            'last_error' => null,
                            'updated_at' => now(),
                        ]);

                    return DB::table('stripe_webhook_events')->where('id', $existing->id)->first();
                }

                DB::table('stripe_webhook_events')->insert([
                    'event_id' => $eventId,
                    'event_type' => $eventType,
                    'status' => 'processing',
                    'payload_hash' => $payloadHash,
                    'attempts' => 1,
                    'processed_at' => null,
                    'last_error' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return DB::table('stripe_webhook_events')->where('event_id', $eventId)->first();
            });
        } catch (QueryException $e) {
            // Course d'insertion concurrente: relire la ligne existante
            return DB::table('stripe_webhook_events')->where('event_id', $eventId)->first();
        }
    }

    private function markWebhookEventProcessed(string $eventId): void
    {
        if (!$this->hasWebhookEventStore()) {
            return;
        }

        DB::table('stripe_webhook_events')
            ->where('event_id', $eventId)
            ->update([
                'status' => 'processed',
                'processed_at' => now(),
                'last_error' => null,
                'updated_at' => now(),
            ]);
    }

    private function markWebhookEventFailed(string $eventId, string $error): void
    {
        if (!$this->hasWebhookEventStore()) {
            return;
        }

        DB::table('stripe_webhook_events')
            ->where('event_id', $eventId)
            ->update([
                'status' => 'failed',
                'last_error' => substr($error, 0, 2000),
                'updated_at' => now(),
            ]);
    }

    public function handle(Request $request)
    {
        $secret = config('stripe.webhook_secret');
        $signature = $request->header('Stripe-Signature');
        $payload = $request->getContent();

        if (empty($secret)) {
            Log::error('Stripe webhook secret is not configured');
            return response()->json(['error' => 'Webhook non configuré. Contactez l\'administrateur.'], 500);
        }

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $signature, $secret);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Données invalides.'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Signature invalide.'], 400);
        }

        // SÉCURITÉ: anti-replay persistant en base (survit aux redémarrages)
        $eventId = (string) ($event->id ?? '');
        $eventType = (string) ($event->type ?? '');
        if ($eventId !== '') {
            $reserved = $this->reserveWebhookEvent($eventId, $eventType, hash('sha256', $payload));
            if ($reserved && in_array(($reserved->status ?? null), ['processed', 'processing_duplicate'], true)) {
                Log::info("Stripe webhook event déjà traité (DB): {$eventId}");
                return response()->json(['received' => true, 'duplicate' => true]);
            }
        }

        try {
            \Stripe\Stripe::setApiKey(config('stripe.secret'));

            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    $this->handleCheckoutSessionCompleted($session);
                    break;

                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                case 'customer.subscription.deleted':
                    $subscription = $event->data->object;
                    $this->syncStripeSubscription($subscription);
                    break;

                case 'invoice.payment_succeeded':
                case 'invoice.payment_failed':
                    $invoice = $event->data->object;
                    if (!empty($invoice->subscription)) {
                        $subscription = \Stripe\Subscription::retrieve($invoice->subscription);
                        $this->syncStripeSubscription($subscription);
                    }
                    break;

                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($event->data->object);
                    break;

                case 'payment_intent.payment_failed':
                    $this->handlePaymentIntentFailed($event->data->object);
                    break;

                case 'charge.refunded':
                    $this->handleChargeRefunded($event->data->object);
                    break;

                case 'charge.dispute.created':
                    $this->handleDisputeCreated($event->data->object);
                    break;

                case 'transfer.failed':
                    $this->handleTransferFailed($event->data->object);
                    break;

                case 'payout.failed':
                    $this->handlePayoutFailed($event->data->object);
                    break;

                case 'account.updated':
                    $this->handleAccountUpdated($event->data->object);
                    break;

                case 'charge.dispute.closed':
                    $this->handleDisputeClosed($event->data->object);
                    break;
            }
        } catch (\Throwable $e) {
            Log::error('Stripe webhook handling failed: ' . $e->getMessage(), [
                'event_type' => $event->type ?? null,
                'event_id' => $eventId !== '' ? $eventId : null,
            ]);
            if ($eventId !== '') {
                $this->markWebhookEventFailed($eventId, $e->getMessage());
            }
            return response()->json(['error' => 'Erreur de traitement du webhook.'], 500);
        }

        if ($eventId !== '') {
            $this->markWebhookEventProcessed($eventId);
        }

        return response()->json(['received' => true]);
    }

    private function handleCheckoutSessionCompleted($session): void
    {
        if (($session->mode ?? null) !== 'subscription') {
            return;
        }

        $sessionMetadata = $this->stripeMetadataToArray($session->metadata ?? []);
        $user = null;

        if (!empty($session->client_reference_id)) {
            $user = User::find($session->client_reference_id);
        }

        if (!$user && !empty($sessionMetadata['user_id'])) {
            $user = User::find($sessionMetadata['user_id']);
        }

        if (!$user && !empty($session->customer)) {
            $user = User::where('stripe_customer_id', $session->customer)->first();
        }

        if (!$user) {
            Log::warning('Stripe checkout completed but user not found', [
                'session_id' => $session->id ?? null,
                'customer' => $session->customer ?? null,
                'client_reference_id' => $session->client_reference_id ?? null,
            ]);
            return;
        }

        if (empty($session->subscription)) {
            Log::warning('Stripe checkout completed without subscription id', [
                'session_id' => $session->id ?? null,
                'user_id' => $user->id,
            ]);
            return;
        }

        $stripeSubscription = \Stripe\Subscription::retrieve($session->subscription);
        $this->upsertFromStripeSubscription($stripeSubscription, $user, $session->id ?? null);
    }

    private function syncStripeSubscription($stripeSubscription): void
    {
        $customerId = $stripeSubscription->customer ?? null;
        if (empty($customerId)) {
            return;
        }

        $user = User::where('stripe_customer_id', $customerId)->first();
        if (!$user) {
            // Fallback: try to find via user_subscriptions
            $local = UserSubscription::where('stripe_customer_id', $customerId)->first();
            $user = $local?->user;
        }

        if (!$user) {
            Log::warning('Stripe subscription event but user not found', [
                'customer' => $customerId,
                'subscription_id' => $stripeSubscription->id ?? null,
            ]);
            return;
        }

        $this->upsertFromStripeSubscription($stripeSubscription, $user);
    }

    private function upsertFromStripeSubscription($stripeSubscription, User $user, ?string $checkoutSessionId = null): void
    {
        $planId = $stripeSubscription->metadata->plan_id ?? null;
        $plan = null;

        if ($planId) {
            $plan = SubscriptionPlan::find($planId);
        }

        if (!$plan) {
            $priceId = $stripeSubscription->items->data[0]->price->id ?? null;
            if ($priceId) {
                $plan = SubscriptionPlan::where('stripe_price_id', $priceId)->first();
            }
        }

        if (!$plan) {
            Log::warning('Unable to map Stripe subscription to local plan', [
                'subscription_id' => $stripeSubscription->id ?? null,
                'user_id' => $user->id,
            ]);
            return;
        }

        $stripeStatus = $stripeSubscription->status ?? null;
        $localStatus = match ($stripeStatus) {
            'active', 'trialing' => 'active',
            'canceled' => 'cancelled',
            default => 'paused',
        };

        $periodStart = !empty($stripeSubscription->current_period_start)
            ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start)
            : now();
        $periodEnd = !empty($stripeSubscription->current_period_end)
            ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end)
            : null;

        $cancelAtPeriodEnd = (bool)($stripeSubscription->cancel_at_period_end ?? false);
        $endsAt = $cancelAtPeriodEnd && $periodEnd ? $periodEnd : null;

        $unitAmount = $stripeSubscription->items->data[0]->price->unit_amount ?? null;
        $currency = $stripeSubscription->items->data[0]->price->currency ?? null;

        $amount = $unitAmount !== null ? ($unitAmount / 100) : $plan->price;

        $data = [
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'stripe_subscription_id' => $stripeSubscription->id,
            'stripe_customer_id' => (string)($stripeSubscription->customer ?? $user->stripe_customer_id),
            'status' => $localStatus,
            'started_at' => $periodStart,
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
            'ends_at' => $endsAt,
            'current_amount' => $amount,
            'currency' => strtoupper($currency ?? ($plan->currency ?? 'EUR')),
            'auto_renew' => !$cancelAtPeriodEnd,
        ];

        $metadata = [
            'stripe_status' => $stripeStatus,
            'cancel_at_period_end' => $cancelAtPeriodEnd,
        ];
        if ($checkoutSessionId) {
            $metadata['checkout_session_id'] = $checkoutSessionId;
        }

        $existing = UserSubscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if (!$existing) {
            // Try updating a pending record
            if ($checkoutSessionId) {
                $existing = UserSubscription::where('user_id', $user->id)
                    ->where('subscription_plan_id', $plan->id)
                    ->where('stripe_subscription_id', 'pending_' . $checkoutSessionId)
                    ->first();
            }
        }

        if (!$existing) {
            $existing = UserSubscription::where('user_id', $user->id)
                ->where('subscription_plan_id', $plan->id)
                ->first();
        }

        if ($existing) {
            $existing->update($data + [
                'metadata' => array_merge((array)($existing->metadata ?? []), $metadata),
            ]);
        } else {
            UserSubscription::create($data + [
                'metadata' => $metadata,
            ]);
        }

        // If active, cancel other active local subscriptions
        if ($localStatus === 'active') {
            UserSubscription::where('user_id', $user->id)
                ->where('id', '!=', $existing?->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => 'Changement de plan',
                    'auto_renew' => false,
                ]);
        }
    }

    // ========================================================================
    //  PAYMENT INTENT HANDLERS (bookings, rentals, food, urgent sales)
    // ========================================================================

    /**
     * Handle payment_intent.succeeded — reconcile with local PaymentTransaction
     */
    private function handlePaymentIntentSucceeded($paymentIntent): void
    {
        $piId = $paymentIntent->id ?? null;
        if (!$piId) return;

        $metadata = $this->paymentIntentMetadata($paymentIntent);

        // Find existing PaymentTransaction (most flows create it on confirm)
        $transaction = PaymentTransaction::where('stripe_payment_intent_id', $piId)->first();

        if ($transaction) {
            // Update status if still pending/processing
            if (in_array($transaction->status, ['pending', 'processing', 'held'])) {
                $transaction->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
                Log::info("Webhook: PaymentTransaction #{$transaction->id} marked as paid via payment_intent.succeeded", [
                    'pi' => $piId,
                ]);
            }
        } else {
            // Create a fallback transaction if none exists (edge case: confirm callback failed)
            $amount = ($paymentIntent->amount_received ?? $paymentIntent->amount ?? 0) / 100;
            $userId = $this->resolveUserIdFromPaymentIntent($paymentIntent, $metadata);
            $txType = $this->normalizeTransactionType($metadata['tx_type'] ?? ($metadata['payment_type'] ?? 'payment'));
            $bookingId = !empty($metadata['booking_id']) ? (int) $metadata['booking_id'] : null;
            $rentalRequestId = !empty($metadata['rental_request_id']) ? (int) $metadata['rental_request_id'] : null;
            $foodOrderId = !empty($metadata['food_order_id']) ? (int) $metadata['food_order_id'] : null;
            $chargeId = $paymentIntent->latest_charge ?? ($paymentIntent->charges->data[0]->id ?? null);
            $description = (string) ($paymentIntent->description ?? '');

            if ($userId && $amount > 0) {
                try {
                    $transaction = PaymentTransaction::systemCreate([
                        'user_id' => (int) $userId,
                        'stripe_payment_intent_id' => $piId,
                        'stripe_charge_id' => $chargeId,
                        'booking_id' => $bookingId,
                        'equipment_rental_id' => $rentalRequestId,
                        'food_order_id' => $foodOrderId,
                        'amount' => $amount,
                        'currency' => strtolower($paymentIntent->currency ?? 'eur'),
                        'status' => 'paid',
                        'type' => $txType,
                        'provider' => 'stripe',
                        'transaction_id' => $piId,
                        'description' => $description !== '' ? $description : null,
                        'metadata' => $metadata,
                        'paid_at' => now(),
                    ]);
                    Log::warning("Webhook: Created fallback PaymentTransaction #{$transaction->id} (no prior confirm)", [
                        'pi' => $piId,
                        'user_id' => $userId,
                    ]);
                } catch (QueryException $e) {
                    if (str_contains(strtolower((string) $e->getMessage()), 'duplicate')) {
                        $existing = PaymentTransaction::where('stripe_payment_intent_id', $piId)->first();
                        if ($existing) {
                            $transaction = $existing;
                        }
                    } else {
                        throw $e;
                    }
                }
            } else {
                Log::critical('WEBHOOK CRITIQUE: Paiement Stripe capturé sans transaction DB - intervention manuelle requise', [
                    'pi' => $piId,
                    'amount' => $amount,
                    'resolved_user_id' => $userId,
                    'customer' => $paymentIntent->customer ?? null,
                    'metadata_keys' => array_keys($metadata),
                ]);
            }
        }

        // Reconcile connected-entity statuses (booking, rental, food order)
        $amountCents = (int) ($paymentIntent->amount ?? 0);
        $this->reconcileEntityStatus($metadata, 'succeeded', $amountCents);
    }

    /**
     * Handle payment_intent.payment_failed — log failure and update transaction
     */
    private function handlePaymentIntentFailed($paymentIntent): void
    {
        $piId = $paymentIntent->id ?? null;
        if (!$piId) return;

        $lastError = $paymentIntent->last_payment_error->message ?? 'Unknown error';

        Log::warning("Webhook: payment_intent.payment_failed", [
            'pi' => $piId,
            'error' => $lastError,
            'metadata' => $this->paymentIntentMetadata($paymentIntent),
        ]);

        $transaction = PaymentTransaction::where('stripe_payment_intent_id', $piId)->first();
        if ($transaction && $transaction->status !== 'paid') {
            $transaction->update([
                'status' => 'failed',
                'metadata' => array_merge((array) ($transaction->metadata ?? []), [
                    'failure_reason' => $lastError,
                    'failed_at' => now()->toISOString(),
                ]),
            ]);
        }
    }

    /**
     * Handle charge.refunded — update PaymentTransaction status
     */
    private function handleChargeRefunded($charge): void
    {
        $chargeId = $charge->id ?? null;
        $piId = $charge->payment_intent ?? null;

        // Find by PaymentIntent ID first (most reliable)
        $transaction = null;
        if ($piId) {
            $transaction = PaymentTransaction::where('stripe_payment_intent_id', $piId)->first();
        }
        if (!$transaction && $chargeId) {
            $transaction = PaymentTransaction::where('stripe_charge_id', $chargeId)->first();
        }

        if (!$transaction) {
            Log::info("Webhook: charge.refunded but no matching PaymentTransaction", [
                'charge' => $chargeId, 'pi' => $piId,
            ]);
            return;
        }

        $refundedAmount = ($charge->amount_refunded ?? 0) / 100;
        $totalAmount = ($charge->amount ?? 0) / 100;
        $isFullRefund = $charge->refunded ?? ($refundedAmount >= $totalAmount);

        $transaction->update([
            'status' => $isFullRefund ? 'refunded' : 'partially_refunded',
            'metadata' => array_merge((array) ($transaction->metadata ?? []), [
                'refunded_amount' => $refundedAmount,
                'refunded_at' => now()->toISOString(),
                'full_refund' => $isFullRefund,
            ]),
        ]);

        Log::info("Webhook: PaymentTransaction #{$transaction->id} marked as " . ($isFullRefund ? 'refunded' : 'partially_refunded'), [
            'charge' => $chargeId, 'refunded_amount' => $refundedAmount,
        ]);
    }

    /**
     * Handle charge.dispute.created — flag transaction and alert admin
     */
    private function handleDisputeCreated($dispute): void
    {
        $chargeId = $dispute->charge ?? null;
        $piId = $dispute->payment_intent ?? null;

        $transaction = null;
        if ($piId) {
            $transaction = PaymentTransaction::where('stripe_payment_intent_id', $piId)->first();
        }
        if (!$transaction && $chargeId) {
            $transaction = PaymentTransaction::where('stripe_charge_id', $chargeId)->first();
        }

        Log::critical("Webhook: charge.dispute.created — DISPUTE OPENED", [
            'dispute_id' => $dispute->id ?? null,
            'charge' => $chargeId,
            'amount' => ($dispute->amount ?? 0) / 100,
            'reason' => $dispute->reason ?? 'unknown',
            'transaction_id' => $transaction?->id,
        ]);

        if ($transaction) {
            $transaction->update([
                'status' => 'disputed',
                'metadata' => array_merge((array) ($transaction->metadata ?? []), [
                    'dispute_id' => $dispute->id ?? null,
                    'dispute_reason' => $dispute->reason ?? 'unknown',
                    'disputed_at' => now()->toISOString(),
                ]),
            ]);
        }
    }

    /**
     * Handle transfer.failed — mark related records for manual review
     */
    private function handleTransferFailed($transfer): void
    {
        $transferId = $transfer->id ?? null;
        $metadata = $this->stripeMetadataToArray($transfer->metadata ?? []);
        $failureCode = $transfer->failure_code ?? null;
        $failureMessage = $transfer->failure_message ?? null;

        Log::critical('Webhook: transfer.failed', [
            'transfer_id' => $transferId,
            'failure_code' => $failureCode,
            'failure_message' => $failureMessage,
            'metadata' => $metadata,
        ]);

        if ($transferId) {
            DB::table('payouts')
                ->where('transaction_reference', $transferId)
                ->whereIn('status', ['pending', 'completed'])
                ->update([
                    'status' => 'cancelled',
                    'notes' => trim("Stripe transfer.failed {$failureCode} {$failureMessage}"),
                    'updated_at' => now(),
                ]);
        }

        if (!empty($metadata['escrow_id'])) {
            DB::table('escrow_transactions')
                ->where('id', (int) $metadata['escrow_id'])
                ->whereIn('status', ['released', 'partial'])
                ->update([
                    'status' => 'disputed',
                    'updated_at' => now(),
                ]);
        }

        if (!empty($metadata['food_order_id'])) {
            DB::table('food_orders')
                ->where('id', (int) $metadata['food_order_id'])
                ->update([
                    'escrow_status' => 'held',
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Handle payout.failed — update withdrawal/payout status
     */
    private function handlePayoutFailed($payout): void
    {
        $payoutId = $payout->id ?? null;
        $failureCode = $payout->failure_code ?? null;
        $failureMessage = $payout->failure_message ?? null;
        $destination = $payout->destination ?? null;

        Log::critical('Webhook: payout.failed', [
            'payout_id' => $payoutId,
            'destination' => $destination,
            'failure_code' => $failureCode,
            'failure_message' => $failureMessage,
        ]);

        if (!$payoutId) {
            return;
        }

        DB::table('withdrawals')
            ->where('transaction_reference', $payoutId)
            ->whereIn('status', ['pending', 'completed'])
            ->update([
                'status' => 'rejected',
                'admin_notes' => trim("Stripe payout.failed {$failureCode} {$failureMessage}"),
                'updated_at' => now(),
            ]);

        DB::table('payouts')
            ->where('transaction_reference', $payoutId)
            ->whereIn('status', ['pending', 'completed'])
            ->update([
                'status' => 'cancelled',
                'notes' => trim("Stripe payout.failed {$failureCode} {$failureMessage}"),
                'updated_at' => now(),
            ]);
    }

    /**
     * Handle account.updated — keep local onboarding flags synchronized
     */
    private function handleAccountUpdated($account): void
    {
        $accountId = $account->id ?? null;
        if (!$accountId) {
            return;
        }

        $chargesEnabled = (bool) ($account->charges_enabled ?? false);
        $payoutsEnabled = (bool) ($account->payouts_enabled ?? false);
        $detailsSubmitted = (bool) ($account->details_submitted ?? false);
        $disabledReason = $account->requirements->disabled_reason ?? ($account->disabled_reason ?? null);
        $onboardingComplete = $chargesEnabled && $payoutsEnabled && $detailsSubmitted && empty($disabledReason);

        $prestataireUpdates = DB::table('prestataires')
            ->where('stripe_account_id', $accountId)
            ->update([
                'stripe_onboarding_completed' => $onboardingComplete,
                'updated_at' => now(),
            ]);

        $driverUpdates = 0;
        try {
            $driverUpdates = DB::table('delivery_drivers')
                ->where('stripe_account_id', $accountId)
                ->update([
                    'stripe_onboarding_complete' => $onboardingComplete,
                    'updated_at' => now(),
                ]);
        } catch (\Throwable $e) {
            Log::warning('Webhook: account.updated driver sync skipped', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);
        }

        if ($disabledReason) {
            Log::warning('Webhook: account.updated disabled account', [
                'account_id' => $accountId,
                'disabled_reason' => $disabledReason,
                'prestataires_updated' => $prestataireUpdates,
                'drivers_updated' => $driverUpdates,
            ]);
            return;
        }

        Log::info('Webhook: account.updated synced', [
            'account_id' => $accountId,
            'onboarding_complete' => $onboardingComplete,
            'prestataires_updated' => $prestataireUpdates,
            'drivers_updated' => $driverUpdates,
        ]);
    }

    /**
     * Handle charge.dispute.closed — persist dispute outcome on transaction
     */
    private function handleDisputeClosed($dispute): void
    {
        $chargeId = $dispute->charge ?? null;
        $piId = $dispute->payment_intent ?? null;
        $disputeStatus = $dispute->status ?? 'unknown';

        $transaction = null;
        if ($piId) {
            $transaction = PaymentTransaction::where('stripe_payment_intent_id', $piId)->first();
        }
        if (!$transaction && $chargeId) {
            $transaction = PaymentTransaction::where('stripe_charge_id', $chargeId)->first();
        }

        Log::info('Webhook: charge.dispute.closed', [
            'dispute_id' => $dispute->id ?? null,
            'status' => $disputeStatus,
            'charge' => $chargeId,
            'payment_intent' => $piId,
            'transaction_id' => $transaction?->id,
        ]);

        if (!$transaction) {
            return;
        }

        $newStatus = $transaction->status;
        if ($disputeStatus === 'won' && $transaction->status === 'disputed') {
            $newStatus = 'paid';
        } elseif ($disputeStatus === 'lost') {
            $newStatus = 'disputed';
        }

        $transaction->update([
            'status' => $newStatus,
            'metadata' => array_merge((array) ($transaction->metadata ?? []), [
                'dispute_id' => $dispute->id ?? null,
                'dispute_status' => $disputeStatus,
                'dispute_closed_at' => now()->toISOString(),
            ]),
        ]);
    }

    /**
     * Reconcile entity statuses after a payment event.
     * Verifies the paid amount matches the expected amount before confirming.
     */
    private function reconcileEntityStatus(array $metadata, string $outcome, int $amountCents = 0): void
    {
        // Booking
        if (!empty($metadata['booking_id'])) {
            try {
                $booking = \App\Models\Booking::find($metadata['booking_id']);
                if ($booking && $outcome === 'succeeded' && $booking->status === 'pending') {
                    // Vérifier que le montant payé correspond au montant attendu
                    $paymentType = $metadata['payment_type'] ?? 'full';
                    $expectedAmount = match ($paymentType) {
                        'deposit' => (float) $booking->deposit_amount,
                        'balance' => round((float) $booking->total_price - (float) $booking->deposit_amount, 2),
                        default => (float) $booking->total_price,
                    };
                    $expectedCents = (int) round($expectedAmount * 100);
                    if ($amountCents > 0 && $expectedCents > 0 && $amountCents < $expectedCents) {
                        Log::warning("Webhook: Booking #{$booking->id} amount mismatch - paid {$amountCents}c, expected {$expectedCents}c. Skipping confirmation.");
                        return;
                    }
                    $booking->update(['status' => 'confirmed']);
                    Log::info("Webhook: Booking #{$booking->id} confirmed via payment_intent.succeeded");
                }
            } catch (\Exception $e) {
                Log::warning("Webhook: Failed to reconcile booking #{$metadata['booking_id']}: " . $e->getMessage());
            }
        }

        // Equipment rental request
        if (!empty($metadata['rental_request_id'])) {
            try {
                $request = \App\Models\EquipmentRentalRequest::find($metadata['rental_request_id']);
                if ($request && $outcome === 'succeeded' && in_array($request->status, ['accepted', 'pending'])) {
                    $expectedCents = (int) round((float) ($request->total_amount ?? 0) * 100);
                    if ($amountCents > 0 && $expectedCents > 0 && $amountCents < $expectedCents) {
                        Log::warning("Webhook: Rental #{$request->id} amount mismatch - paid {$amountCents}c, expected {$expectedCents}c. Skipping confirmation.");
                        return;
                    }
                    $request->update(['status' => 'confirmed', 'confirmed_at' => now()]);
                    Log::info("Webhook: EquipmentRentalRequest #{$request->id} confirmed via payment_intent.succeeded");
                }
            } catch (\Exception $e) {
                Log::warning("Webhook: Failed to reconcile rental #{$metadata['rental_request_id']}: " . $e->getMessage());
            }
        }

        // Food order
        if (!empty($metadata['food_order_id'])) {
            try {
                $order = \App\Models\FoodOrder::find($metadata['food_order_id']);
                if ($order && $outcome === 'succeeded' && $order->status === 'pending') {
                    $expectedCents = (int) round((float) ($order->total ?? 0) * 100);
                    if ($amountCents > 0 && $expectedCents > 0 && $amountCents < $expectedCents) {
                        Log::warning("Webhook: FoodOrder #{$order->id} amount mismatch - paid {$amountCents}c, expected {$expectedCents}c. Skipping confirmation.");
                        return;
                    }
                    $order->update(['status' => 'paid']);
                    Log::info("Webhook: FoodOrder #{$order->id} marked paid via payment_intent.succeeded");
                }
            } catch (\Exception $e) {
                Log::warning("Webhook: Failed to reconcile food order #{$metadata['food_order_id']}: " . $e->getMessage());
            }
        }
    }
}