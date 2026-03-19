<?php

namespace App\Services;

use App\Models\EquipmentRental;
use App\Models\EquipmentRentalRequest;
use App\Models\PaymentTransaction;
use App\Models\Prestataire;
use App\Models\User;
use App\Support\PaymentMetadataNormalizer;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

use App\Support\TableExistenceCache;
class EquipmentRentalPaymentSyncService
{
    private array $columnCache = [];
    private bool $stripePaymentIntentCacheLoaded = false;
    private array $stripePaymentIntentCache = [];

    public function __construct(
        private readonly StripePaymentService $stripeService,
        private readonly EscrowService $escrowService,
        private readonly InvoiceGenerationService $invoiceService
    ) {}

    public function syncForClient(User $user, int $limit = 80): void
    {
        $client = $user->client;
        if (!$client) {
            return;
        }

        $requests = EquipmentRentalRequest::query()
            ->with(['equipment.prestataire', 'client.user', 'rental'])
            ->where('client_id', (int) $client->id)
            ->orderByDesc('id')
            ->limit(max(1, $limit))
            ->get();

        foreach ($requests as $request) {
            $this->syncRequest($request, (int) $user->id, true, true);
        }
    }

    public function syncForPrestataire(Prestataire $prestataire, int $limit = 120): void
    {
        $requests = EquipmentRentalRequest::query()
            ->with(['equipment.prestataire', 'client.user', 'rental'])
            ->where('prestataire_id', (int) $prestataire->id)
            ->orderByDesc('id')
            ->limit(max(1, $limit))
            ->get();

        foreach ($requests as $request) {
            $this->syncRequest($request, null, true, true);
        }
    }

    public function syncForRequest(EquipmentRentalRequest $request, ?int $preferredUserId = null): void
    {
        $request->loadMissing(['equipment.prestataire', 'client.user', 'rental']);
        $this->syncRequest($request, $preferredUserId, true, true);
    }

    private function syncRequest(
        EquipmentRentalRequest $request,
        ?int $preferredUserId = null,
        bool $generateInvoices = true,
        bool $ensureEscrow = true
    ): void {
        try {
            $request->loadMissing(['equipment.prestataire', 'client.user', 'rental']);
            $clientUserId = (int) ($preferredUserId ?: ($request->client?->user_id ?? 0));
            if ($clientUserId <= 0) {
                return;
            }

            $transaction = $this->findLatestTransactionForRequest($request, $clientUserId);
            if (!$transaction) {
                $transaction = $this->createTransactionFromEscrow($request, $clientUserId);
            }
            if (!$transaction) {
                $paymentRequirement = (string) ($request->equipment?->payment_requirement ?? 'none');
                $shouldLookupStripe = $paymentRequirement !== 'none'
                    && in_array((string) $request->status, ['pending', 'accepted', 'confirmed', 'in_preparation'], true)
                    && (!empty($request->created_at) && $request->created_at->greaterThan(now()->subDays(120)));

                if ($shouldLookupStripe) {
                    $transaction = $this->createTransactionFromStripeLookup($request, $clientUserId);
                }
            }
            if (!$transaction) {
                return;
            }

            $this->reassignTransactionUserIfNeeded($transaction, $clientUserId);
            $transaction = $this->promotePendingTransactionFromStripe($request, $transaction, $clientUserId);

            if ($ensureEscrow) {
                $this->ensureEscrowFromTransaction($request, $transaction, $clientUserId);
            }

            $this->ensureRentalState($request, $transaction);

            if ($generateInvoices && !in_array((string) $transaction->status, ['pending', 'processing', 'failed'], true)) {
                try {
                    $this->invoiceService->generateForEquipmentRental($request, $transaction);
                } catch (\Throwable $invoiceError) {
                    Log::warning('Equipment rental invoice sync warning', [
                        'request_id' => $request->id,
                        'transaction_id' => $transaction->id ?? null,
                        'error' => $invoiceError->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Equipment rental payment sync warning', [
                'request_id' => $request->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function findLatestTransactionForRequest(EquipmentRentalRequest $request, int $clientUserId): ?PaymentTransaction
    {
        $requestId = (int) $request->id;
        $requestNumber = trim((string) ($request->request_number ?? ''));

        $direct = PaymentTransaction::query()
            ->where('equipment_rental_id', $requestId)
            ->orderByDesc('id')
            ->first();
        if ($direct) {
            return $direct;
        }

        $recent = PaymentTransaction::query()
            ->whereNotNull('metadata')
            ->orderByDesc('id')
            ->limit(400)
            ->get();

        foreach ($recent as $transaction) {
            $meta = $this->normalizeMetadata($transaction->metadata ?? null);
            if ((int) ($meta['rental_request_id'] ?? 0) === $requestId) {
                return $transaction;
            }

            $description = (string) ($transaction->description ?? '');
            if ($requestNumber !== '' && stripos($description, $requestNumber) !== false) {
                return $transaction;
            }
            if (stripos($description, '#' . $requestId) !== false) {
                return $transaction;
            }
        }

        // Last fallback: direct lookup on same user for safety.
        $userRecent = PaymentTransaction::query()
            ->where('user_id', $clientUserId)
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        foreach ($userRecent as $transaction) {
            $meta = $this->normalizeMetadata($transaction->metadata ?? null);
            if ((int) ($meta['rental_request_id'] ?? 0) === $requestId) {
                return $transaction;
            }
            $description = (string) ($transaction->description ?? '');
            if (stripos($description, '#' . $requestId) !== false || ($requestNumber !== '' && stripos($description, $requestNumber) !== false)) {
                return $transaction;
            }
        }

        return null;
    }

    private function createTransactionFromEscrow(EquipmentRentalRequest $request, int $clientUserId): ?PaymentTransaction
    {
        $escrow = $this->findLatestEscrowForRequest($request);
        if (!$escrow) {
            return null;
        }

        $row = (array) $escrow;
        $piId = trim((string) ($row['stripe_payment_intent_id'] ?? ''));
        if ($piId === '') {
            return null;
        }

        $existing = PaymentTransaction::where('stripe_payment_intent_id', $piId)->first();
        if ($existing) {
            return $existing;
        }

        $meta = $this->normalizeMetadata($row['metadata'] ?? null);
        $status = strtolower((string) ($row['status'] ?? 'pending'));
        $txStatus = in_array($status, ['refunded', 'partially_refunded'], true) ? 'refunded' : 'paid';

        $amountMain = (float) ($row['total_amount'] ?? ($row['amount'] ?? 0));
        $amountDeposit = (float) ($row['deposit_amount'] ?? 0);
        $amount = $amountMain + $amountDeposit;
        if ($amount <= 0) {
            $amount = $this->resolveRentalAmount($request)
                + (float) ($request->equipment?->security_deposit ?? $request->security_deposit ?? 0);
        }
        if ($amount <= 0) {
            return null;
        }

        $paymentKinds = $this->resolvePaymentKinds(null, $meta);
        $payload = [
            'user_id' => $clientUserId,
            'stripe_payment_intent_id' => $piId,
            'equipment_rental_id' => (int) $request->id,
            'amount' => $amount,
            'currency' => strtolower((string) ($row['currency'] ?? 'eur')),
            'status' => $txStatus,
            'description' => 'Paiement location #' . ($request->request_number ?: $request->id),
            'metadata' => array_merge($meta, [
                'rental_request_id' => (string) $request->id,
                'escrow_id' => (string) ($row['id'] ?? ''),
                'recovered_from' => 'escrow_transactions',
            ]),
            'paid_at' => $row['paid_at'] ?? ($row['held_at'] ?? now()),
        ];

        if ($this->hasColumn('payment_transactions', 'type')) {
            $payload['type'] = $paymentKinds['tx_type'];
        }
        if ($this->hasColumn('payment_transactions', 'provider')) {
            $payload['provider'] = 'stripe';
        }
        if ($this->hasColumn('payment_transactions', 'transaction_id')) {
            $payload['transaction_id'] = $piId;
        }

        try {
            $created = PaymentTransaction::systemCreate($payload);
            Log::warning('Equipment rental transaction backfilled from escrow', [
                'request_id' => $request->id,
                'transaction_id' => $created->id,
                'payment_intent_id' => $piId,
            ]);
            return $created;
        } catch (QueryException $e) {
            if (str_contains(strtolower((string) $e->getMessage()), 'duplicate')) {
                return PaymentTransaction::where('stripe_payment_intent_id', $piId)->first();
            }
            throw $e;
        }
    }

    private function createTransactionFromStripeLookup(EquipmentRentalRequest $request, int $clientUserId): ?PaymentTransaction
    {
        try {
            $paymentIntents = $this->getRecentStripePaymentIntents();
            if (empty($paymentIntents)) {
                return null;
            }

            $requestId = (int) $request->id;
            $requestNumber = trim((string) ($request->request_number ?? ''));
            $clientEmail = strtolower(trim((string) ($request->client?->user?->email ?? '')));
            $createdMinTs = !empty($request->created_at)
                ? $request->created_at->copy()->subDays(2)->timestamp
                : null;

            foreach ($paymentIntents as $paymentIntent) {
                if ($createdMinTs !== null) {
                    $piCreated = (int) ($paymentIntent->created ?? 0);
                    if ($piCreated > 0 && $piCreated < $createdMinTs) {
                        continue;
                    }
                }

                $piStatus = strtolower((string) ($paymentIntent->status ?? ''));
                if (!in_array($piStatus, ['succeeded', 'requires_capture'], true)) {
                    continue;
                }

                $rawMeta = $paymentIntent->metadata ?? [];
                if (is_object($rawMeta) && method_exists($rawMeta, 'toArray')) {
                    $rawMeta = $rawMeta->toArray();
                }
                $meta = PaymentMetadataNormalizer::normalize((array) $rawMeta);

                $matchesRequest = (int) ($meta['rental_request_id'] ?? 0) === $requestId;
                if (!$matchesRequest) {
                    $description = (string) ($paymentIntent->description ?? '');
                    if ($requestNumber !== '' && stripos($description, $requestNumber) !== false) {
                        $matchesRequest = true;
                    } elseif (stripos($description, '#' . $requestId) !== false) {
                        $matchesRequest = true;
                    }
                }
                if (!$matchesRequest) {
                    continue;
                }

                $metaUserId = (int) ($meta['user_id'] ?? 0);
                if ($metaUserId > 0 && $metaUserId !== $clientUserId) {
                    continue;
                }

                $receiptEmail = strtolower(trim((string) ($paymentIntent->receipt_email ?? '')));
                if ($metaUserId <= 0 && $clientEmail !== '' && $receiptEmail !== '' && $receiptEmail !== $clientEmail) {
                    continue;
                }

                $piId = (string) ($paymentIntent->id ?? '');
                if ($piId === '') {
                    continue;
                }

                $existing = PaymentTransaction::where('stripe_payment_intent_id', $piId)->first();
                if ($existing) {
                    return $existing;
                }

                $amount = ((int) ($paymentIntent->amount_received ?? $paymentIntent->amount ?? 0)) / 100;
                if ($amount <= 0) {
                    continue;
                }

                $paymentKinds = $this->resolvePaymentKinds(null, $meta);
                $payload = [
                    'user_id' => $clientUserId,
                    'stripe_payment_intent_id' => $piId,
                    'equipment_rental_id' => $requestId,
                    'amount' => $amount,
                    'currency' => strtolower((string) ($paymentIntent->currency ?? 'eur')),
                    'status' => 'paid',
                    'description' => (string) ($paymentIntent->description ?? ('Paiement location #' . ($request->request_number ?: $requestId))),
                    'metadata' => array_merge($meta, [
                        'rental_request_id' => (string) $requestId,
                        'recovered_from' => 'stripe_payment_intent_list',
                    ]),
                    'paid_at' => now(),
                ];

                if ($this->hasColumn('payment_transactions', 'type')) {
                    $payload['type'] = $paymentKinds['tx_type'];
                }
                if ($this->hasColumn('payment_transactions', 'provider')) {
                    $payload['provider'] = 'stripe';
                }
                if ($this->hasColumn('payment_transactions', 'transaction_id')) {
                    $payload['transaction_id'] = $piId;
                }

                try {
                    $created = PaymentTransaction::systemCreate($payload);
                } catch (QueryException $e) {
                    if (str_contains(strtolower((string) $e->getMessage()), 'duplicate')) {
                        $existing = PaymentTransaction::where('stripe_payment_intent_id', $piId)->first();
                        if ($existing) {
                            return $existing;
                        }
                    }
                    throw $e;
                }
                Log::warning('Equipment rental transaction recovered from Stripe payment intent list', [
                    'request_id' => $requestId,
                    'transaction_id' => $created->id,
                    'payment_intent_id' => $piId,
                ]);

                return $created;
            }
        } catch (\Throwable $e) {
            Log::warning('Stripe payment intent lookup skipped for rental sync', [
                'request_id' => $request->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    private function getRecentStripePaymentIntents(): array
    {
        if ($this->stripePaymentIntentCacheLoaded) {
            return $this->stripePaymentIntentCache;
        }

        $this->stripePaymentIntentCacheLoaded = true;
        $this->stripePaymentIntentCache = [];

        if (!class_exists(\Stripe\Stripe::class) || !class_exists(\Stripe\PaymentIntent::class)) {
            return [];
        }

        $secret = config('stripe.secret') ?: config('services.stripe.secret');
        if (empty($secret)) {
            return [];
        }

        try {
            \Stripe\Stripe::setApiKey($secret);
            $list = \Stripe\PaymentIntent::all(['limit' => 100]);
            $this->stripePaymentIntentCache = (array) ($list->data ?? []);
        } catch (\Throwable $e) {
            Log::warning('Stripe payment intents cache load failed for rental sync', [
                'error' => $e->getMessage(),
            ]);
            $this->stripePaymentIntentCache = [];
        }

        return $this->stripePaymentIntentCache;
    }

    private function promotePendingTransactionFromStripe(
        EquipmentRentalRequest $request,
        PaymentTransaction $transaction,
        int $clientUserId
    ): PaymentTransaction {
        $status = strtolower((string) ($transaction->status ?? ''));
        if (!in_array($status, ['pending', 'processing'], true)) {
            return $transaction;
        }

        $piId = trim((string) ($transaction->stripe_payment_intent_id ?? $transaction->transaction_id ?? ''));
        if ($piId === '') {
            return $transaction;
        }

        $paymentIntent = $this->retrievePaymentIntentForRequest($request, $piId);
        if (!$paymentIntent) {
            return $transaction;
        }

        $piStatus = strtolower((string) ($paymentIntent->status ?? ''));
        $rawPiMeta = $paymentIntent->metadata ?? [];
        if (is_object($rawPiMeta) && method_exists($rawPiMeta, 'toArray')) {
            $rawPiMeta = $rawPiMeta->toArray();
        }
        $piMeta = PaymentMetadataNormalizer::normalize((array) $rawPiMeta);
        $metaRequestId = (int) ($piMeta['rental_request_id'] ?? 0);
        if ($metaRequestId > 0 && $metaRequestId !== (int) $request->id) {
            return $transaction;
        }

        $update = [];
        $existingMeta = $this->normalizeMetadata($transaction->metadata ?? null);
        if (in_array($piStatus, ['succeeded', 'requires_capture'], true)) {
            $amount = ((int) ($paymentIntent->amount_received ?? $paymentIntent->amount ?? 0)) / 100;
            $paymentKinds = $this->resolvePaymentKinds($transaction, array_merge($existingMeta, $piMeta));

            $update['user_id'] = $clientUserId;
            $update['equipment_rental_id'] = (int) $request->id;
            $update['status'] = 'paid';
            $update['currency'] = strtolower((string) ($paymentIntent->currency ?? ($transaction->currency ?? 'eur')));
            if ($amount > 0) {
                $update['amount'] = $amount;
            }
            if ($this->hasColumn('payment_transactions', 'type')) {
                $update['type'] = $paymentKinds['tx_type'];
            }
            if ($this->hasColumn('payment_transactions', 'provider')) {
                $update['provider'] = 'stripe';
            }
            if ($this->hasColumn('payment_transactions', 'transaction_id')) {
                $update['transaction_id'] = $piId;
            }
            if (empty($transaction->description)) {
                $update['description'] = (string) ($paymentIntent->description ?? ('Paiement location #' . ($request->request_number ?: $request->id)));
            }
            if (empty($transaction->paid_at)) {
                $update['paid_at'] = now();
            }
        } elseif ($piStatus === 'canceled') {
            $update['status'] = 'failed';
        } else {
            return $transaction;
        }

        $update['metadata'] = array_merge(
            $existingMeta,
            $piMeta,
            [
                'rental_request_id' => (string) $request->id,
                'reconciled_from' => 'stripe_intent_poll',
                'stripe_intent_status' => $piStatus,
            ]
        );

        if (!empty($update)) {
            $transaction->forceFill($update);
            $transaction->save();
            $transaction = $transaction->fresh();
        }

        return $transaction;
    }

    private function ensureEscrowFromTransaction(EquipmentRentalRequest $request, PaymentTransaction $transaction, int $clientUserId): void
    {
        if (!TableExistenceCache::has('escrow_transactions')) {
            return;
        }

        $status = strtolower((string) ($transaction->status ?? ''));
        if (!in_array($status, ['paid', 'completed', 'succeeded'], true)) {
            return;
        }

        $piId = trim((string) ($transaction->stripe_payment_intent_id ?? ''));
        if ($piId !== '') {
            $existingByPi = DB::table('escrow_transactions')
                ->where('stripe_payment_intent_id', $piId)
                ->first();
            if ($existingByPi) {
                return;
            }
        } else {
            $existingByRequest = $this->findLatestEscrowForRequest($request);
            if ($existingByRequest) {
                return;
            }
        }

        $meta = $this->normalizeMetadata($transaction->metadata ?? null);
        $paymentKinds = $this->resolvePaymentKinds($transaction, $meta);
        $paymentType = $paymentKinds['payment_type'];

        $totalAmount = $this->resolveRentalAmount($request);
        $securityDeposit = (float) ($request->equipment?->security_deposit ?? $request->security_deposit ?? 0);
        $depositPct = (float) ($request->equipment?->deposit_percentage ?? 30);
        $expectedDeposit = round($totalAmount * ($depositPct / 100), 2);

        $metaRentalAmount = (float) ($meta['rental_amount'] ?? 0);
        if ($metaRentalAmount > 0) {
            $rentalAmount = $metaRentalAmount;
        } elseif ($paymentType === 'deposit') {
            $rentalAmount = $expectedDeposit;
        } elseif ($paymentType === 'balance') {
            $rentalAmount = max(0, $totalAmount - $expectedDeposit);
        } else {
            $rentalAmount = $totalAmount;
        }

        if ($rentalAmount <= 0) {
            $rentalAmount = (float) $transaction->amount;
        }
        if ($rentalAmount <= 0) {
            return;
        }

        $depositAmount = $paymentType === 'balance' ? 0.0 : max(0, $securityDeposit);

        $this->escrowService->createEscrow(
            $request,
            $clientUserId,
            (int) ($request->prestataire_id ?? 0),
            $rentalAmount,
            $depositAmount,
            $transaction->stripe_payment_intent_id,
            null,
            [
                'type' => 'equipment_rental',
                'payment_type' => $paymentType,
                'payment_requirement' => $meta['payment_requirement'] ?? ($request->equipment?->payment_requirement ?? 'none'),
                'request_number' => $request->request_number,
                'equipment_id' => $request->equipment_id,
                'transaction_id' => $transaction->id,
                'rental_request_id' => (string) $request->id,
                'synced_from' => 'equipment_rental_payment_sync',
            ]
        );
    }

    private function ensureRentalState(EquipmentRentalRequest $request, PaymentTransaction $transaction): void
    {
        $request->loadMissing(['equipment', 'rental']);
        $rental = $request->rental;

        if (!$rental) {
            $rental = EquipmentRental::create([
                'rental_number' => 'LOC-' . strtoupper(uniqid()),
                'rental_request_id' => $request->id,
                'equipment_id' => $request->equipment_id,
                'client_id' => $request->client_id,
                'prestataire_id' => $request->prestataire_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'planned_duration_days' => $request->duration_days ?? 1,
                'unit_price' => $request->unit_price ?? 0,
                'base_amount' => $request->total_amount ?? 0,
                'security_deposit' => $request->equipment?->security_deposit ?? $request->security_deposit ?? 0,
                'total_amount' => $request->total_amount ?? 0,
                'final_amount' => $request->final_amount ?? $request->total_amount ?? 0,
                'pickup_address' => $request->pickup_address,
                'status' => 'confirmed',
                'payment_status' => 'pending',
            ]);
        }

        $meta = $this->normalizeMetadata($transaction->metadata ?? null);
        $paymentKinds = $this->resolvePaymentKinds($transaction, $meta);
        $status = strtolower((string) ($transaction->status ?? 'pending'));

        $logicalStatus = 'paid';
        if (in_array($status, ['refunded', 'partially_refunded'], true)) {
            $logicalStatus = 'refunded';
        } elseif ($paymentKinds['payment_type'] === 'deposit') {
            $logicalStatus = 'partial';
        }

        $this->persistRentalPaymentStatus($rental, $logicalStatus);

        if (in_array((string) $request->status, ['pending', 'accepted'], true)) {
            $request->status = 'confirmed';
            $request->confirmed_at = $request->confirmed_at ?? now();
            $request->save();
        }
    }

    private function persistRentalPaymentStatus(EquipmentRental $rental, string $logicalStatus): void
    {
        $candidates = match ($logicalStatus) {
            'partial' => ['partial', 'deposit_paid'],
            'refunded' => ['refunded'],
            default => ['paid', 'full_paid'],
        };

        foreach ($candidates as $candidate) {
            try {
                $rental->payment_status = $candidate;
                $rental->save();
                return;
            } catch (\Throwable $e) {
                // continue with fallback enum value
            }
        }
    }

    private function reassignTransactionUserIfNeeded(PaymentTransaction $transaction, int $clientUserId): void
    {
        if ((int) ($transaction->user_id ?? 0) === $clientUserId) {
            return;
        }

        try {
            $transaction->user_id = $clientUserId;
            $transaction->save();
        } catch (\Throwable $e) {
            Log::warning('Unable to reassign equipment rental transaction user during sync', [
                'transaction_id' => $transaction->id ?? null,
                'target_user_id' => $clientUserId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function retrievePaymentIntentForRequest(EquipmentRentalRequest $request, string $paymentIntentId): ?object
    {
        try {
            return $this->stripeService->retrievePaymentIntent($paymentIntentId, null);
        } catch (\Throwable $platformError) {
            $connectedAccountId = $request->prestataire?->stripe_account_id ?? $request->equipment?->prestataire?->stripe_account_id;
            if (!empty($connectedAccountId)) {
                try {
                    return $this->stripeService->retrievePaymentIntent($paymentIntentId, $connectedAccountId);
                } catch (\Throwable $connectedError) {
                    Log::warning('Rental payment sync: unable to retrieve PaymentIntent on platform and connected account', [
                        'request_id' => $request->id,
                        'payment_intent_id' => $paymentIntentId,
                        'platform_error' => $platformError->getMessage(),
                        'connected_error' => $connectedError->getMessage(),
                    ]);
                }
            } else {
                Log::warning('Rental payment sync: unable to retrieve PaymentIntent on platform account', [
                    'request_id' => $request->id,
                    'payment_intent_id' => $paymentIntentId,
                    'platform_error' => $platformError->getMessage(),
                ]);
            }
        }

        return null;
    }

    private function findLatestEscrowForRequest(EquipmentRentalRequest $request): ?object
    {
        if (!TableExistenceCache::has('escrow_transactions')) {
            return null;
        }

        return DB::table('escrow_transactions')
            ->where('escrowable_id', (int) $request->id)
            ->where('escrowable_type', 'like', '%EquipmentRentalRequest%')
            ->orderByDesc('id')
            ->first();
    }

    private function resolvePaymentKinds(?PaymentTransaction $transaction, array $metadata): array
    {
        $rawType = (string) ($metadata['payment_type'] ?? ($metadata['tx_type'] ?? ($transaction->type ?? 'payment')));
        $txType = PaymentMetadataNormalizer::normalizeTransactionType($rawType) ?: 'payment';
        $paymentType = PaymentMetadataNormalizer::normalizePaymentType($rawType);

        if ($paymentType === '') {
            $paymentType = match ($txType) {
                'deposit' => 'deposit',
                'balance' => 'balance',
                'refund' => 'refund',
                default => 'full',
            };
        }

        return [
            'tx_type' => $txType,
            'payment_type' => $paymentType,
        ];
    }

    private function normalizeMetadata($value): array
    {
        if (empty($value)) {
            return [];
        }

        if (is_array($value)) {
            return PaymentMetadataNormalizer::normalize($value);
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return PaymentMetadataNormalizer::normalize((array) $value->toArray());
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return PaymentMetadataNormalizer::normalize($decoded);
            }
        }

        return PaymentMetadataNormalizer::normalize((array) $value);
    }

    private function resolveRentalAmount(EquipmentRentalRequest $request): float
    {
        $total = (float) ($request->total_amount ?? 0);
        if ($total > 0) {
            return $total;
        }

        return max(0, (float) ($request->final_amount ?? 0));
    }

    private function hasColumn(string $table, string $column): bool
    {
        $cacheKey = $table . '.' . $column;
        if (array_key_exists($cacheKey, $this->columnCache)) {
            return $this->columnCache[$cacheKey];
        }

        try {
            $exists = TableExistenceCache::has($table) && Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            $exists = false;
        }

        $this->columnCache[$cacheKey] = $exists;

        return $exists;
    }
}
