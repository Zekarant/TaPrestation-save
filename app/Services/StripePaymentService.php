<?php

/**
 * @phpstan-ignore-next-line
 * @psalm-suppress UnusedClass
 */

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\Finance\AccountingService;
use App\Models\Booking;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

/**
 * StripePaymentService
 * 
 * Service pour gérer les paiements Stripe
 * Requiert: stripe/stripe-php package et ext-curl PHP extension
 * 
 * @phpstan-ignore-next-line
 */
class StripePaymentService
{
    protected $stripeEnabled = false;
    protected ?string $stripeSecret = null;
    protected ?string $stripeCurrency = null;
    protected ?string $stripeStatementDescriptor = null;

    public function __construct()
    {
        // Support both config/stripe.php and config/services.php naming schemes
        $this->stripeSecret = config('stripe.secret')
            ?: config('services.stripe.secret');

        $this->stripeCurrency = config('stripe.intents.currency')
            ?: config('services.stripe.intents.currency')
            ?: 'eur';

        $this->stripeStatementDescriptor = config('stripe.intents.statement_descriptor')
            ?: config('services.stripe.intents.statement_descriptor');

        // Check if Stripe is available
        if (class_exists('\Stripe\Stripe') && $this->stripeSecret) {
            try {
                /** @var mixed $stripeClass */
                $stripeClass = '\Stripe\Stripe';
                $stripeClass::setApiKey($this->stripeSecret);
                $this->stripeEnabled = true;
            } catch (\Exception $e) {
                $this->stripeEnabled = false;
            }
        }
    }

    /**
     * Check if Stripe is enabled
     */
    public function isEnabled(): bool
    {
        return $this->stripeEnabled;
    }

    private function assertPositiveAmount(float $amount, string $context): int
    {
        $amountCents = (int) round($amount * 100);
        if ($amountCents < 1) {
            throw new \InvalidArgumentException("{$context}: montant invalide.");
        }

        return $amountCents;
    }

    private function normalizeForIdempotency(array $value): array
    {
        foreach ($value as $key => $item) {
            if (is_object($item)) {
                $item = method_exists($item, 'toArray') ? $item->toArray() : (array) $item;
            }

            if (is_array($item)) {
                $value[$key] = $this->normalizeForIdempotency($item);
                continue;
            }

            $value[$key] = $item;
        }

        ksort($value);
        return $value;
    }

    private function firstNonEmptyMetadata(array $metadata, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $metadata[$key] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            return (string) $value;
        }

        return null;
    }

    private function buildIdempotencyKey(string $prefix, array $payload): string
    {
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException('Impossible de générer la clé d\'idempotence.');
        }

        return $prefix . '_' . hash('sha256', $json);
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        $sqlState = (string) ($e->errorInfo[0] ?? '');
        $driverCode = (string) ($e->errorInfo[1] ?? '');
        $message = (string) $e->getMessage();

        return $sqlState === '23000'
            || $sqlState === '23505'
            || $driverCode === '1062'
            || str_contains(strtolower($message), 'duplicate');
    }

    private function paymentIntentMetadata($paymentIntent): array
    {
        if (empty($paymentIntent->metadata)) {
            return [];
        }

        if (is_array($paymentIntent->metadata)) {
            return $paymentIntent->metadata;
        }

        if (is_object($paymentIntent->metadata) && method_exists($paymentIntent->metadata, 'toArray')) {
            return $paymentIntent->metadata->toArray();
        }

        return (array) $paymentIntent->metadata;
    }

    /**
     * Check if a connected account can receive transfers
     */
    public function canReceiveTransfers(string $accountId): bool
    {
        if (!$this->stripeEnabled) {
            return false;
        }

        try {
            $account = \Stripe\Account::retrieve($accountId);
            
            // Le compte doit pouvoir encaisser des paiements.
            // En mode "direct charges" (Stripe Connect), les frais Stripe sont prélevés côté compte connecté.
            // Donc on valide principalement charges_enabled + capability card_payments (ou fallback transfers/legacy).
            if (!$account->charges_enabled) {
                return false;
            }
            
            // Vérifier les capabilities
            $capabilities = $account->capabilities ?? [];
            $hasCardPayments = ($capabilities['card_payments'] ?? null) === 'active';
            $hasTransfers = ($capabilities['transfers'] ?? null) === 'active' || ($capabilities['legacy_payments'] ?? null) === 'active';

            return $hasCardPayments || $hasTransfers;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Create a payment intent for a booking
     */
    public function createPaymentIntent(
        User $user,
        float $amount,
        string $description,
        array $metadata = [],
        ?string $connectedAccountId = null,
        array $extraParams = []
    )
    {
        if (!$this->stripeEnabled) {
            throw new \Exception('Stripe payment service is not configured. Please install stripe/stripe-php and configure API keys.');
        }

        $amountCents = $this->assertPositiveAmount($amount, 'createPaymentIntent');

        $params = [
            'amount' => $amountCents, // Convert to cents
            'currency' => $this->stripeCurrency ?? 'eur',
            'description' => $description,
            // IMPORTANT (Connect direct charges):
            // If we create the PaymentIntent on a connected account, the customer must exist on that same account.
            // To avoid creating cross-account customers and breaking confirmation flows, we omit `customer` for
            // connected-account intents.
            'customer' => $connectedAccountId ? null : ($user->stripe_customer_id ?? $this->getOrCreateCustomer($user)),
            'statement_descriptor_suffix' => $this->stripeStatementDescriptor ? substr($this->stripeStatementDescriptor, 0, 22) : null,
            'metadata' => array_merge([
                'user_id' => $user->id,
            ], $metadata),
        ];

        // Allow controllers to pass safe Stripe params like receipt_email, capture_method, etc.
        if (!empty($extraParams)) {
            $extraMetadata = (array) ($extraParams['metadata'] ?? []);
            unset($extraParams['metadata']);

            // Prevent accidental override of core fields
            unset($extraParams['amount'], $extraParams['currency']);

            $params = array_merge($params, $extraParams);
            if (!empty($extraMetadata)) {
                $params['metadata'] = array_merge((array) ($params['metadata'] ?? []), $extraMetadata);
            }
        }

        // Remove null customer to keep Stripe params clean
        if (array_key_exists('customer', $params) && $params['customer'] === null) {
            unset($params['customer']);
        }

        // Si un compte connecté est spécifié:
        // On utilise des "direct charges" : le PaymentIntent est créé SUR le compte connecté.
        // Résultat: les frais Stripe sont prélevés côté prestataire (compte connecté),
        // et la plateforme ne reçoit que `application_fee_amount`.
        if ($connectedAccountId) {
            $meta = (array) ($params['metadata'] ?? []);

            // If controller provided explicit fee totals, trust them.
            $clientFee = isset($meta['client_fee_total']) ? (float) $meta['client_fee_total'] : null;
            $prestataireFee = isset($meta['prestataire_fee_total']) ? (float) $meta['prestataire_fee_total'] : null;

            $prestataire = null;
            if (isset($meta['prestataire_id'])) {
                try {
                    $prestataire = \App\Models\Prestataire::find((int) $meta['prestataire_id']);
                } catch (\Throwable $e) {
                    $prestataire = null;
                }
            }

            $type = 'service';
            if (isset($meta['cart_id'])) {
                $type = 'urgent_sale';
            } elseif (isset($meta['rental_request_id'])) {
                $type = 'rental';
            } elseif (isset($meta['booking_id']) || (($meta['type'] ?? null) === 'booking_prepayment') || isset($meta['service_id'])) {
                $type = 'service';
            } elseif (isset($meta['food_order_id'])) {
                $type = 'food';
            }

            $baseAmount = isset($meta['base_amount']) ? (float) $meta['base_amount'] : (float) $amount;

            if ($clientFee === null) {
                $clientFee = \App\Services\CommissionService::feeAmount($baseAmount, $type, 'client', $user, $prestataire);
            }
            if ($prestataireFee === null) {
                $prestataireFee = \App\Services\CommissionService::feeAmount($baseAmount, $type, 'prestataire', $user, $prestataire);
            }

            // Commission plateforme souhaitée
            $platformCommission = (float) $clientFee + (float) $prestataireFee;
            
            // Frais Stripe (configurable via CommissionService / admin settings)
            // En mode Connect, les frais Stripe sont prélevés sur l'application_fee
            // Donc on doit inclure les frais Stripe dans l'application_fee pour ne pas perdre d'argent
            $stripeFee = \App\Services\CommissionService::stripeFeesAmount((float) $amount);
            
            // Total application_fee = commission plateforme + frais Stripe
            $platformTotalFee = $platformCommission + $stripeFee;
            $applicationFee = (int) round($platformTotalFee * 100);

            // Enrich metadata for audit/debug
            $params['metadata'] = array_merge($meta, [
                'commission_type' => $type,
                'base_amount' => (string) round($baseAmount, 2),
                'client_fee_total' => (string) round((float) $clientFee, 2),
                'prestataire_fee_total' => (string) round((float) $prestataireFee, 2),
                'stripe_fee' => (string) round($stripeFee, 2),
                'platform_commission' => (string) round($platformCommission, 2),
                'connected_account_id' => (string) $connectedAccountId,
            ]);
            $params['application_fee_amount'] = $applicationFee;
        }

        // Direct Charges: le paiement est créé sur le compte du prestataire
        // Avantages: les frais Stripe sont prélevés sur le compte du prestataire
        $normalizedMetadata = $this->normalizeForIdempotency((array) ($params['metadata'] ?? []));
        $entityRef = $this->firstNonEmptyMetadata($normalizedMetadata, [
            'booking_id',
            'rental_request_id',
            'cart_id',
            'food_order_id',
            'escrowable_id',
            'service_id',
            'session_id',
        ]);

        $idempotencyKey = $this->buildIdempotencyKey('pi', [
            'user_id' => (string) $user->id,
            'amount_cents' => $amountCents,
            'currency' => strtolower((string) ($params['currency'] ?? 'eur')),
            'connected_account' => $connectedAccountId ?? 'platform',
            'entity_ref' => $entityRef,
            'description' => (string) ($params['description'] ?? ''),
            'metadata' => $normalizedMetadata,
        ]);

        if ($connectedAccountId) {
            $paymentIntent = \Stripe\PaymentIntent::create($params, [
                'stripe_account' => $connectedAccountId,
                'idempotency_key' => $idempotencyKey,
            ]);
        } else {
            $paymentIntent = \Stripe\PaymentIntent::create($params, [
                'idempotency_key' => $idempotencyKey,
            ]);
        }

        return $paymentIntent;
    }

    /**
     * Confirm a payment intent
     */
    public function confirmPayment(string $paymentIntentId, string $paymentMethodId, ?string $connectedAccountId = null)
    {
        if (!$this->stripeEnabled) {
            throw new \Exception('Stripe payment service is not configured.');
        }

        $opts = $connectedAccountId ? ['stripe_account' => $connectedAccountId] : [];
        $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId, $opts);
        
        return $paymentIntent->confirm([
            'payment_method' => $paymentMethodId,
        ], $opts);
    }

    /**
     * Retrieve a PaymentIntent from Stripe.
     */
    public function retrievePaymentIntent(string $paymentIntentId, ?string $connectedAccountId = null)
    {
        if (!$this->stripeEnabled) {
            throw new \Exception('Stripe payment service is not configured.');
        }

        $opts = $connectedAccountId ? ['stripe_account' => $connectedAccountId] : [];
        return \Stripe\PaymentIntent::retrieve($paymentIntentId, $opts);
    }

    /**
     * Record a successful payment
     */
    public function recordPayment($paymentIntent, ?int $bookingId = null, ?int $rentalId = null, string $type = 'payment'): PaymentTransaction
    {
        $piId = (string) ($paymentIntent->id ?? '');
        if ($piId === '') {
            throw new \InvalidArgumentException('PaymentIntent invalide: id manquant.');
        }

        $existing = PaymentTransaction::where('stripe_payment_intent_id', $piId)->first();
        if ($existing) {
            return $existing;
        }

        $metadata = $this->paymentIntentMetadata($paymentIntent);
        $userId = (int) ($metadata['user_id'] ?? 0);
        if ($userId <= 0) {
            throw new \RuntimeException("PaymentIntent {$piId}: metadata.user_id manquant.");
        }

        // Use latest_charge (modern API) with fallback to legacy charges->data[0]
        $chargeId = $paymentIntent->latest_charge ?? ($paymentIntent->charges->data[0]->id ?? null);
        $charge = null;
        if ($chargeId && $this->stripeEnabled) {
            try {
                $charge = \Stripe\Charge::retrieve($chargeId);
            } catch (\Exception $e) {
                // Fallback: try legacy way
                $charge = $paymentIntent->charges->data[0] ?? null;
            }
        } else {
            $charge = $paymentIntent->charges->data[0] ?? null;
        }

        // Defensive guard: some intents can be succeeded without immediate charge data (e.g. delayed capture)
        $paymentMethod = $charge?->payment_method_details?->type ?? $paymentIntent->payment_method ?? null;
        $receiptUrl = $charge?->receipt_url ?? null;
        $txPayload = [
            'user_id' => $userId,
            'stripe_payment_intent_id' => $piId,
            'stripe_charge_id' => $charge?->id,
            'booking_id' => $bookingId,
            'equipment_rental_id' => $rentalId,
            'amount' => ($paymentIntent->amount ?? 0) / 100,
            'currency' => $paymentIntent->currency,
            'status' => 'paid',
            'type' => $type,
            'payment_method' => $paymentMethod,
            'description' => $paymentIntent->description,
            'receipt_url' => $receiptUrl,
            'metadata' => $metadata,
            'paid_at' => now(),
        ];

        try {
            $tx = PaymentTransaction::systemCreate($txPayload);
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                $existing = PaymentTransaction::where('stripe_payment_intent_id', $piId)->first();
                if ($existing) {
                    return $existing;
                }
            }

            throw $e;
        }

        // Record accounting entry (commission, prestataire share, ledger)
        try {
            if ($bookingId) {
                $booking = Booking::find($bookingId);
                $prestataireId = $booking?->prestataire_id ?? null;
            } else {
                $prestataireId = null;
            }

            $accounting = new AccountingService();
            $accounting->recordClientPayment([
                'user_id' => $userId,
                'prestataire_id' => $prestataireId,
                'amount' => ($paymentIntent->amount ?? 0) / 100,
                'reference' => $tx->id,
                'type' => $type,
                'status' => 'completed',
            ]);
        } catch (\Exception $e) {
            // If accounting fails, log and continue (investigate)
            report($e);
        }

        return $tx;
    }

    /**
     * Refund a payment
     */
    public function refundPayment(PaymentTransaction $transaction, float $amount = null, string $reason = null)
    {
        if (!$this->stripeEnabled) {
            throw new \Exception('Stripe payment service is not configured.');
        }

        $chargeId = trim((string) ($transaction->stripe_charge_id ?? ''));
        $paymentIntentId = trim((string) ($transaction->stripe_payment_intent_id ?? ''));

        if ($chargeId === '' && $paymentIntentId === '') {
            throw new \InvalidArgumentException('Aucun identifiant Stripe disponible pour effectuer le remboursement.');
        }

        // Ne pas rembourser un paiement non encore capturé
        if (in_array($transaction->status, ['pending', 'processing'])) {
            throw new \InvalidArgumentException('Ce paiement n\'a pas encore été capturé et ne peut pas être remboursé. Annulez le PaymentIntent à la place.');
        }

        $amountCents = null;
        if ($amount !== null) {
            $amountCents = $this->assertPositiveAmount($amount, 'refundPayment');
        }

        $refundMetadata = [
            'transaction_id' => (string) ($transaction->id ?? ''),
            'reason' => $reason ?? 'Refund requested by prestataire',
        ];

        $buildRefundPayload = function (bool $usePaymentIntent) use ($chargeId, $paymentIntentId, $amountCents, $refundMetadata): array {
            $payload = [
                'metadata' => $refundMetadata,
            ];

            if ($amountCents !== null) {
                $payload['amount'] = $amountCents;
            }

            if ($usePaymentIntent) {
                $payload['payment_intent'] = $paymentIntentId;
            } else {
                $payload['charge'] = $chargeId;
            }

            return $payload;
        };

        $usePaymentIntent = ($chargeId === '' && $paymentIntentId !== '');
        $refundParams = $buildRefundPayload($usePaymentIntent);

        $refundIdempotencyKey = $this->buildIdempotencyKey('refund_tx', [
            'transaction_id' => (string) ($transaction->id ?? ''),
            'charge' => $chargeId !== '' ? $chargeId : 'none',
            'payment_intent' => $paymentIntentId !== '' ? $paymentIntentId : 'none',
            'amount_cents' => $amountCents ?? 'full',
        ]);

        try {
            $refund = \Stripe\Refund::create($refundParams, [
                'idempotency_key' => $refundIdempotencyKey,
            ]);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Compat legacy: certains paiements historisés n'ont pas stripe_charge_id.
            if ($usePaymentIntent || $paymentIntentId === '') {
                throw $e;
            }

            $refund = \Stripe\Refund::create($buildRefundPayload(true), [
                'idempotency_key' => $refundIdempotencyKey,
            ]);
        }

        // Update transaction -- partial vs full refund status
        $refundStatus = ($amount !== null && $amount < (float) $transaction->amount)
            ? 'partially_refunded'
            : 'refunded';
        $transaction->update([
            'status' => $refundStatus,
            'refunded_at' => now(),
            'refund_reason' => $reason,
        ]);

        return $refund;
    }

    /**
     * Refund a PaymentIntent directly (used for automatic compensation flows).
     */
    public function refundPaymentIntent(
        string $paymentIntentId,
        ?float $amount = null,
        ?string $reason = null,
        ?string $idempotencyScope = null
    )
    {
        if (!$this->stripeEnabled) {
            throw new \Exception('Stripe payment service is not configured.');
        }

        if (trim($paymentIntentId) === '') {
            throw new \InvalidArgumentException('PaymentIntent ID manquant pour remboursement.');
        }

        $amountCents = null;
        if ($amount !== null) {
            $amountCents = $this->assertPositiveAmount($amount, 'refundPaymentIntent');
        }

        $params = [
            'payment_intent' => $paymentIntentId,
            'metadata' => [
                'reason' => $reason ?? 'Auto-refund',
            ],
        ];
        if ($amountCents !== null) {
            $params['amount'] = $amountCents;
        }

        if ($idempotencyScope !== null && trim($idempotencyScope) !== '') {
            $idempotencyKey = $this->buildIdempotencyKey('refund_pi_scope', [
                'payment_intent' => $paymentIntentId,
                'scope' => trim($idempotencyScope),
            ]);
        } else {
            $idempotencyKey = $this->buildIdempotencyKey('refund_pi', [
                'payment_intent' => $paymentIntentId,
                'amount_cents' => $amountCents ?? 'full',
                'reason' => (string) ($reason ?? ''),
            ]);
        }

        return \Stripe\Refund::create($params, [
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * Get or create Stripe customer
     * Vérifie d'abord si l'utilisateur a déjà un compte Stripe archivé (réinscription)
     */
    public function getOrCreateCustomer(User $user)
    {
        // 1. Si l'utilisateur a déjà un stripe_customer_id, le retourner
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        // 2. Vérifier si un compte Stripe archivé existe (utilisateur réinscrit)
        $archivedAccount = \App\Models\DeletedStripeAccount::findByEmail($user->email);
        if ($archivedAccount && $archivedAccount->stripe_customer_id) {
            // Vérifier que le customer existe toujours sur Stripe
            try {
                $existingCustomer = \Stripe\Customer::retrieve($archivedAccount->stripe_customer_id);
                if ($existingCustomer && !$existingCustomer->deleted) {
                    // Réutiliser l'ancien customer et mettre à jour ses infos
                    \Stripe\Customer::update($archivedAccount->stripe_customer_id, [
                        'email' => $user->email,
                        'name' => $user->name,
                        'metadata' => [
                            'user_id' => $user->id,
                            'reactivated_at' => now()->toISOString(),
                        ],
                    ]);
                    $user->update(['stripe_customer_id' => $archivedAccount->stripe_customer_id]);
                    Log::info("Stripe Customer réutilisé: {$archivedAccount->stripe_customer_id} pour user {$user->id}");
                    return $archivedAccount->stripe_customer_id;
                }
            } catch (\Exception $e) {
                Log::warning("Stripe Customer archivé introuvable: {$archivedAccount->stripe_customer_id} - " . $e->getMessage());
            }
        }

        // 3. Créer un nouveau customer Stripe
        $customer = \Stripe\Customer::create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    /**
     * Setup Stripe Connect for a prestataire
     */
    public function setupStripeConnect(User $prestataire): string
    {
        $account = \Stripe\Account::create([
            'type' => 'express',
            'country' => 'FR',
            'email' => $prestataire->email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
                'link_payments' => ['requested' => true], // Stripe Link (paiement rapide)
            ],
        ]);

        $prestataire->update(['stripe_account_id' => $account->id]);

        return $account->id;
    }

    /**
     * Create account link for Stripe Connect onboarding
     */
    public function createAccountLink(string $accountId, string $returnUrl, ?string $refreshUrl = null): string
    {
        $accountLink = \Stripe\AccountLink::create([
            'account' => $accountId,
            'type' => 'account_onboarding',
            'return_url' => $returnUrl,
            'refresh_url' => $refreshUrl ?? $returnUrl,
        ]);

        return $accountLink->url;
    }

    /**
     * Get account details
     */
    public function getAccountDetails(string $accountId): \Stripe\Account
    {
        return \Stripe\Account::retrieve($accountId);
    }

    /**
     * Créer un PaymentIntent en mode ESCROW (sur le compte plateforme)
     * L'argent reste sur le compte plateforme jusqu'au Transfer explicite.
     * 
     * C'est la méthode à utiliser pour tous les paiements nécessitant un blocage :
     * - Ventes urgentes
     * - Services avec paiement en ligne
     * - Location de matériel
     * - Commandes food
     */
    public function createEscrowPaymentIntent(
        User $user,
        float $amount,
        string $description,
        array $metadata = [],
        ?string $connectedAccountId = null,
        array $extraParams = []
    ) {
        if (!$this->stripeEnabled) {
            throw new \Exception('Stripe payment service is not configured.');
        }

        $amountCents = $this->assertPositiveAmount($amount, 'createEscrowPaymentIntent');

        $params = [
            'amount' => $amountCents,
            'currency' => $this->stripeCurrency ?? 'eur',
            'description' => $description,
            'customer' => $user->stripe_customer_id ?? $this->getOrCreateCustomer($user),
            'statement_descriptor_suffix' => $this->stripeStatementDescriptor ? substr($this->stripeStatementDescriptor, 0, 22) : null,
            'metadata' => array_merge([
                'user_id' => $user->id,
                'escrow' => 'true',
                'connected_account_id' => $connectedAccountId ?? '',
            ], $metadata),
        ];

        // Allow controllers to pass safe Stripe params
        if (!empty($extraParams)) {
            $extraMetadata = (array) ($extraParams['metadata'] ?? []);
            unset($extraParams['metadata']);
            unset($extraParams['amount'], $extraParams['currency']);
            $params = array_merge($params, $extraParams);
            if (!empty($extraMetadata)) {
                $params['metadata'] = array_merge((array) ($params['metadata'] ?? []), $extraMetadata);
            }
        }

        // Si payment_method_types est passé, désactiver automatic_payment_methods
        // Cela permet de contrôler exactement les méthodes de paiement affichées
        if (isset($params['payment_method_types'])) {
            $params['automatic_payment_methods'] = ['enabled' => false];
        } else {
            // Par défaut, activer les méthodes de paiement automatiques pour Payment Element
            $params['automatic_payment_methods'] = ['enabled' => true];
        }

        // Remove null customer
        if (array_key_exists('customer', $params) && $params['customer'] === null) {
            unset($params['customer']);
        }

        // ESCROW: Le paiement est créé sur le compte PLATEFORME (pas de stripe_account option)
        // L'argent reste sur la plateforme jusqu'à un Transfer explicite
        $normalizedMetadata = $this->normalizeForIdempotency((array) ($params['metadata'] ?? []));
        $entityRef = $this->firstNonEmptyMetadata($normalizedMetadata, [
            'booking_id',
            'rental_request_id',
            'escrowable_id',
            'food_order_id',
            'cart_id',
            'session_id',
        ]);
        $escrowIdempotencyKey = $this->buildIdempotencyKey('escrow_pi', [
            'user_id' => (string) $user->id,
            'amount_cents' => $amountCents,
            'currency' => strtolower((string) ($params['currency'] ?? 'eur')),
            'entity_ref' => $entityRef,
            'description' => (string) ($params['description'] ?? ''),
            'payment_method_types' => implode(',', (array) ($params['payment_method_types'] ?? ['auto'])),
            'automatic_payment_methods' => (($params['automatic_payment_methods']['enabled'] ?? false) ? 'enabled' : 'disabled'),
            'metadata' => $normalizedMetadata,
        ]);
        $paymentIntent = \Stripe\PaymentIntent::create($params, [
            'idempotency_key' => $escrowIdempotencyKey,
        ]);

        Log::info("Escrow PaymentIntent créé: {$paymentIntent->id} pour {$amount}€");

        return $paymentIntent;
    }

    /**
     * Transférer des fonds vers un compte connecté (pour libération escrow)
     * 
     * @param string $connectedAccountId ID du compte Stripe Connect du prestataire
     * @param float $amount Montant en euros à transférer
     * @param string $description Description du transfert
     * @param array $metadata Métadonnées additionnelles
     * @param string|null $sourcePaymentIntentId PaymentIntent source (pour transfer_group)
     * @return \Stripe\Transfer
     */
    public function transferToConnectedAccount(
        string $connectedAccountId,
        float $amount,
        string $description,
        array $metadata = [],
        ?string $sourcePaymentIntentId = null
    ): \Stripe\Transfer {
        if (!$this->stripeEnabled) {
            throw new \Exception('Stripe payment service is not configured.');
        }

        $amountCents = $this->assertPositiveAmount($amount, 'transferToConnectedAccount');
        if (empty($sourcePaymentIntentId)) {
            throw new \InvalidArgumentException('Transfer refusé: source_payment_intent requis.');
        }

        $params = [
            'amount' => $amountCents,
            'currency' => $this->stripeCurrency ?? 'eur',
            'destination' => $connectedAccountId,
            'description' => $description,
            'metadata' => array_merge([
                'source_payment_intent' => $sourcePaymentIntentId ?? '',
            ], $metadata),
        ];

        // IMPORTANT: Utiliser source_transaction pour lier le transfer au charge original
        // Cela permet à Stripe de réserver les fonds automatiquement même avec payouts automatiques
        // Récupérer le PaymentIntent pour obtenir le charge ID
        $paymentIntent = \Stripe\PaymentIntent::retrieve($sourcePaymentIntentId);
        if (empty($paymentIntent->latest_charge)) {
            throw new \RuntimeException("Transfer refusé: aucun latest_charge pour le PaymentIntent {$sourcePaymentIntentId}.");
        }
        $params['source_transaction'] = $paymentIntent->latest_charge;
        Log::info("Transfer lié au charge: {$paymentIntent->latest_charge}");

        // SÉCURITÉ: Clé d'idempotence déterministe pour les Transfers
        $normalizedMetadata = $this->normalizeForIdempotency($metadata);
        $entityRef = $this->firstNonEmptyMetadata($normalizedMetadata, [
            'escrow_id',
            'food_order_id',
            'withdrawal_id',
            'type',
        ]);
        $transferIdempotencyKey = $this->buildIdempotencyKey('transfer', [
            'connected_account' => $connectedAccountId,
            'amount_cents' => $amountCents,
            'source_payment_intent' => $sourcePaymentIntentId,
            'entity_ref' => $entityRef,
            'description' => $description,
            'metadata' => $normalizedMetadata,
        ]);

        $transfer = \Stripe\Transfer::create($params, [
            'idempotency_key' => $transferIdempotencyKey,
        ]);

        Log::info("Transfer créé: {$transfer->id} de {$amount}€ vers {$connectedAccountId}");

        return $transfer;
    }

    /**
     * Débiter le solde Stripe du compte connecté au profit de la plateforme.
     * Utilise les "Account Debits" Stripe (charge avec source=acct_...).
     */
    public function debitConnectedAccountBalanceToPlatform(
        string $connectedAccountId,
        float $amount,
        string $description,
        array $metadata = []
    ) {
        if (!$this->stripeEnabled) {
            throw new \Exception('Stripe payment service is not configured.');
        }

        $amountCents = $this->assertPositiveAmount($amount, 'debitConnectedAccountBalanceToPlatform');
        $normalizedMetadata = $this->normalizeForIdempotency($metadata);

        $idempotencyKey = $this->buildIdempotencyKey('account_debit', [
            'connected_account' => $connectedAccountId,
            'amount_cents' => $amountCents,
            'description' => $description,
            'metadata' => $normalizedMetadata,
        ]);

        $charge = \Stripe\Charge::create([
            'amount' => $amountCents,
            'currency' => $this->stripeCurrency ?? 'eur',
            'source' => $connectedAccountId,
            'description' => $description,
            'metadata' => $metadata,
        ], [
            'idempotency_key' => $idempotencyKey,
        ]);

        Log::info("Account debit Stripe créé: {$charge->id} (source {$connectedAccountId}) pour {$amount}€");

        return $charge;
    }

    /**
     * Annuler un account debit Stripe précédemment créé.
     */
    public function refundConnectedAccountDebit(
        string $debitId,
        ?float $amount = null,
        ?string $reason = null,
        array $metadata = []
    ) {
        if (!$this->stripeEnabled) {
            throw new \Exception('Stripe payment service is not configured.');
        }

        if (trim($debitId) === '') {
            throw new \InvalidArgumentException('Debit ID manquant pour remboursement.');
        }

        $amountCents = null;
        if ($amount !== null) {
            $amountCents = $this->assertPositiveAmount($amount, 'refundConnectedAccountDebit');
        }

        $refundMetadata = array_merge([
            'reason' => $reason ?? 'Account debit refund',
        ], $metadata);

        $idempotencyKey = $this->buildIdempotencyKey('account_debit_refund', [
            'debit_id' => $debitId,
            'amount_cents' => $amountCents ?? 'full',
            'reason' => (string) ($reason ?? ''),
            'metadata' => $this->normalizeForIdempotency($refundMetadata),
        ]);

        $params = [
            'charge' => $debitId,
            'metadata' => $refundMetadata,
        ];
        if ($amountCents !== null) {
            $params['amount'] = $amountCents;
        }

        try {
            return \Stripe\Refund::create($params, [
                'idempotency_key' => $idempotencyKey,
            ]);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Compatibilité API Stripe "Payment object" si l'ID n'est pas un charge classique.
            if (!str_starts_with($debitId, 'py_')) {
                throw $e;
            }

            $params = [
                'payment' => $debitId,
                'metadata' => $refundMetadata,
            ];
            if ($amountCents !== null) {
                $params['amount'] = $amountCents;
            }

            return \Stripe\Refund::create($params, [
                'idempotency_key' => $idempotencyKey,
            ]);
        }
    }

    /**
     * Créer un Payout depuis le compte Connect du prestataire vers sa banque.
     *
     * @param  string  $connectedAccountId  Stripe Connect account ID (acct_...)
     * @param  float   $amount              Montant en EUR
     * @param  string  $description         Description visible
     * @param  array   $metadata            Métadonnées supplémentaires
     * @return \Stripe\Payout
     */
    public function createPayoutOnConnectedAccount(
        string $connectedAccountId,
        float $amount,
        string $description = 'Retrait',
        array $metadata = []
    ): \Stripe\Payout {
        if (!$this->stripeEnabled) {
            throw new \Exception('Stripe payment service is not configured.');
        }

        $amountCents = (int) round($amount * 100);
        if ($amountCents < 100) {
            throw new \Exception('Le montant minimum de retrait est de 1 €.');
        }

        // Clé d'idempotence pour éviter les doubles payouts
        $idempotencyKey = $this->buildIdempotencyKey('payout', [
            'connected_account' => $connectedAccountId,
            'amount_cents' => $amountCents,
            'withdrawal_id' => (string) ($metadata['withdrawal_id'] ?? ''),
            'description' => $description,
            'metadata' => $this->normalizeForIdempotency($metadata),
        ]);

        $payout = \Stripe\Payout::create([
            'amount' => $amountCents,
            'currency' => $this->stripeCurrency ?? 'eur',
            'description' => $description,
            'metadata' => $metadata,
        ], [
            'stripe_account' => $connectedAccountId,
            'idempotency_key' => $idempotencyKey,
        ]);

        Log::info("Payout créé: {$payout->id} de {$amount}€ sur compte {$connectedAccountId}");

        return $payout;
    }

    /**
     * Récupérer le solde disponible sur un compte Connect.
     *
     * @param  string  $connectedAccountId
     * @return float   Solde disponible en EUR
     */
    public function getConnectedAccountBalance(string $connectedAccountId): float
    {
        if (!$this->stripeEnabled) {
            throw new \Exception('Stripe payment service is not configured.');
        }

        $balance = \Stripe\Balance::retrieve([
            'stripe_account' => $connectedAccountId,
        ]);

        // Solde disponible en EUR (convertir depuis centimes)
        $available = 0;
        foreach ($balance->available as $fund) {
            if ($fund->currency === ($this->stripeCurrency ?? 'eur')) {
                $available = $fund->amount / 100;
                break;
            }
        }

        return (float) $available;
    }

    /**
     * Vérifier si un paiement est en mode escrow
     */
    public function isEscrowPayment($paymentIntent): bool
    {
        try {
            if (is_string($paymentIntent)) {
                $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntent);
            }

            if (!is_object($paymentIntent)) {
                return false;
            }

            $metadata = $this->paymentIntentMetadata($paymentIntent);
            return ($metadata['escrow'] ?? 'false') === 'true';
        } catch (\Exception $e) {
            return false;
        }
    }
}
