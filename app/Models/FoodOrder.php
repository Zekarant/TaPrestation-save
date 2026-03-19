<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FoodOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'client_id',
        'prestataire_id',
        'driver_id',
        'driver_accepted_at',
        'status',
        'delivery_status',
        'delivery_code',
        'code_verified_at',
        'subtotal',
        'delivery_fee',
        'driver_commission',
        'service_fee',
        'total',
        'delivery_type',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'delivery_distance',
        'estimated_delivery_time',
        'delivery_phone',
        'delivery_floor',
        'delivery_door_code',
        'delivery_building_info',
        'delivery_contact_name',
        'notes',
        'requested_at',
        'prestataire_reminder_sent_at',
        'client_reminder_4h_sent_at',
        'prestataire_reminder_4h_sent_at',
        'driver_notes',
        'accepted_at',
        'preparing_at',
        'ready_at',
        'picked_up_at',
        'delivered_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
        'client_confirmed',
        'prestataire_confirmed',
        'payment_status',
        'payment_method',
        'payment_intent_id',
        'stripe_payment_intent_id',
        'paid_at',
        'prestataire_paid_at',
        'driver_paid_at',
        'prestataire_payout',
        'driver_payout',
        'platform_fee',
        // Escrow/blocage
        'escrow_status',
        'amount_held',
        'amount_released',
        'amount_refunded',
        'held_at',
        'released_at',
        'refunded_at',
        'refund_reason',
        'stripe_transfer_id',
        'driver_stripe_transfer_id',
        // Sécurité code
        'code_attempts',
        'code_locked_until',
        'code_expires_at',
        // Acceptation conditions paiement (RGPD)
        'payment_terms_version',
        'payment_terms_accepted_at',
        'payment_terms_ip',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'driver_commission' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'delivery_distance' => 'decimal:2',
        'prestataire_payout' => 'decimal:2',
        'driver_payout' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'client_confirmed' => 'boolean',
        'prestataire_confirmed' => 'boolean',
        'accepted_at' => 'datetime',
        'requested_at' => 'datetime',
        'prestataire_reminder_sent_at' => 'datetime',
        'client_reminder_4h_sent_at' => 'datetime',
        'prestataire_reminder_4h_sent_at' => 'datetime',
        'driver_accepted_at' => 'datetime',
        'preparing_at' => 'datetime',
        'ready_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'paid_at' => 'datetime',
        'code_verified_at' => 'datetime',
        'prestataire_paid_at' => 'datetime',
        'driver_paid_at' => 'datetime',
        // Escrow
        'amount_held' => 'decimal:2',
        'amount_released' => 'decimal:2',
        'amount_refunded' => 'decimal:2',
        'held_at' => 'datetime',
        'released_at' => 'datetime',
        'refunded_at' => 'datetime',
        // Sécurité code
        'code_attempts' => 'integer',
        'code_locked_until' => 'datetime',
        'code_expires_at' => 'datetime',
    ];

    /**
     * Générer un code de livraison unique à 4 chiffres (expire après 24h)
     */
    public function generateDeliveryCode(): string
    {
        $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $this->update([
            'delivery_code' => $code,
            'code_expires_at' => now()->addHours(self::CODE_EXPIRY_HOURS),
            'code_attempts' => 0,
            'code_locked_until' => null,
        ]);
        return $code;
    }

    /**
     * Vérifier si le code est expiré
     */
    public function isCodeExpired(): bool
    {
        return $this->code_expires_at && now()->greaterThan($this->code_expires_at);
    }

    /**
     * Vérifier si le code est verrouillé (trop de tentatives)
     */
    public function isCodeLocked(): bool
    {
        return $this->code_locked_until && now()->lessThan($this->code_locked_until);
    }

    /**
     * Incrémenter les tentatives de code et verrouiller si nécessaire
     */
    public function incrementCodeAttempts(): void
    {
        $attempts = $this->code_attempts + 1;
        $data = ['code_attempts' => $attempts];

        if ($attempts >= self::MAX_CODE_ATTEMPTS) {
            $data['code_locked_until'] = now()->addMinutes(self::CODE_LOCK_MINUTES);
        }

        $this->update($data);
    }

    /**
     * Réinitialiser les tentatives après succès
     */
    public function resetCodeAttempts(): void
    {
        $this->update([
            'code_attempts' => 0,
            'code_locked_until' => null,
        ]);
    }

    /**
     * Vérifier le code de livraison
     */
    public function verifyDeliveryCode(string $code): bool
    {
        return $this->delivery_code === $code;
    }

    /**
     * ╔══════════════════════════════════════════════════════════════════════╗
     * ║              DISTRIBUTION DES PAIEMENTS (LIBÉRATION ESCROW)          ║
     * ╠══════════════════════════════════════════════════════════════════════╣
     * ║                                                                      ║
     * ║  APPELÉ QUAND : Le client confirme réception OU validation livreur   ║
     * ║                                                                      ║
     * ║  RÉPARTITION SUR UNE COMMANDE DE 100€ SUBTOTAL :                     ║
     * ║                                                                      ║
     * ║  ┌────────────────────────────────────────────────────────────┐      ║
     * ║  │ QUI REÇOIT QUOI ?                                          │      ║
     * ║  ├────────────────────────────────────────────────────────────┤      ║
     * ║  │                                                            │      ║
     * ║  │ 💰 STRIPE (prélevé automatiquement)                        │      ║
     * ║  │    ~1.4% + 0.25€ sur le total                              │      ║
     * ║  │                                                            │      ║
     * ║  │ 🏢 ADMIN/PLATEFORME reçoit :                               │      ║
     * ║  │    • Commission prestataire (15% de 100€ = 15€)            │      ║
     * ║  │    • Frais service client (5% de 100€ = 5€)                │      ║
     * ║  │    = TOTAL ADMIN : 20€                                     │      ║
     * ║  │                                                            │      ║
     * ║  │ 👨‍🍳 PRESTATAIRE reçoit :                                    │      ║
     * ║  │    Subtotal - Commission = 100€ - 15€ = 85€                │      ║
     * ║  │                                                            │      ║
     * ║  │ 🚗 LIVREUR reçoit (si livraison) :                         │      ║
     * ║  │    Frais de livraison = 5€                                 │      ║
     * ║  │                                                            │      ║
     * ║  └────────────────────────────────────────────────────────────┘      ║
     * ║                                                                      ║
     * ╚══════════════════════════════════════════════════════════════════════╝
     */
    public function processPayouts(): void
    {
        // ========================================================
        // SÉCURITÉ C3: Guard d'idempotencie - empêcher le double payout
        // Si les payouts ont déjà été traités, on ne refait rien.
        // Utilise un lock DB pour éviter les race conditions.
        // ========================================================
        if ($this->prestataire_paid_at !== null || $this->escrow_status === self::ESCROW_RELEASED) {
            \Illuminate\Support\Facades\Log::info("processPayouts: déjà traité pour FoodOrder #{$this->id}, skip.");
            return;
        }

        // Audit 2.18: lockForUpdate DOIT être dans une transaction DB pour être effectif
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
        // Lock pessimiste pour éviter que deux requêtes concurrentes traitent le même payout
        $locked = \Illuminate\Support\Facades\DB::table('food_orders')
            ->where('id', $this->id)
            ->whereNull('prestataire_paid_at')
            ->where('escrow_status', '!=', self::ESCROW_RELEASED)
            ->lockForUpdate()
            ->first();

        if (!$locked) {
            \Illuminate\Support\Facades\Log::info("processPayouts: lock échoué (déjà traité) pour FoodOrder #{$this->id}");
            \Illuminate\Support\Facades\DB::rollBack();
            return;
        }

        $prestataire = null;
        try {
            $prestataire = $this->relationLoaded('prestataire') ? $this->prestataire : (\App\Models\Prestataire::find($this->prestataire_id));
        } catch (\Throwable $e) {
            $prestataire = null;
        }

        // ═══════════════════════════════════════════════════════════════════
        // FRAIS STRIPE (prélevés automatiquement par Stripe sur le paiement)
        // → Formule EU: 1.4% + 0.25€
        // → Sur 100€ = 1.65€ prélevés par Stripe
        // → Ces frais doivent être déduits du payout prestataire
        // ═══════════════════════════════════════════════════════════════════
        $stripeFees = $this->payment_method !== 'cash' 
            ? \App\Services\CommissionService::stripeFeesAmount((float) $this->subtotal)
            : 0;
        
        // ═══════════════════════════════════════════════════════════════════
        // COMMISSION PLATEFORME (prélevée sur le subtotal du prestataire)
        // → Paramètre admin : commission_food (ex: 2%)
        // → Sur 100€ subtotal avec 2% = 2€ pour l'admin
        // ═══════════════════════════════════════════════════════════════════
        $platformFee = \App\Services\CommissionService::feeAmount(
            (float) $this->subtotal,  // Base : prix des plats
            'food',                    // Type : food
            'prestataire',             // Côté : commission prélevée au presta
            null, 
            $prestataire
        );
        
        // ═══════════════════════════════════════════════════════════════════
        // PAIEMENT PRESTATAIRE = Subtotal - Frais Stripe - Commission
        // → Ex: 100€ - 1.65€ - 2€ = 96.35€ pour le prestataire
        // → IMPORTANT: Les frais Stripe sont TOUJOURS déduits (même si commission=0%)
        // ═══════════════════════════════════════════════════════════════════
        $prestatairePayout = round((float) $this->subtotal - $stripeFees - (float) $platformFee, 2);
        
        // ═══════════════════════════════════════════════════════════════════
        // PAIEMENT LIVREUR = Frais de livraison - Frais Stripe proportionnels
        // → Les frais de livraison payés par le client vont au livreur
        // → Moins les frais Stripe sur cette partie
        // ═══════════════════════════════════════════════════════════════════
        $driverStripeFees = 0;
        $driverPayout = 0;
        if ($this->delivery_type === 'delivery' && (float) $this->delivery_fee > 0) {
            $driverStripeFees = $this->payment_method !== 'cash' 
                ? \App\Services\CommissionService::stripeFeesAmount((float) $this->delivery_fee)
                : 0;
            $driverPayout = round((float) $this->delivery_fee - $driverStripeFees, 2);
        }

        // ═══════════════════════════════════════════════════════════════════
        // NOTE : Ce que l'ADMIN reçoit au total :
        //   - platform_fee (commission sur le presta) = 2€
        //   - service_fee (frais client, déjà dans le total) = 5€
        //   - TOTAL ADMIN = 7€ (les frais Stripe sont à la charge du presta)
        // ═══════════════════════════════════════════════════════════════════

        // Montant libéré = ce qui était bloqué en escrow
        $amountReleased = $this->amount_held ?? $this->total;

        $processedAt = now();
        $prestataireTransferRequired = $this->payment_method !== 'cash' && $prestatairePayout > 0;
        $prestatairePayoutSettled = !$prestataireTransferRequired;
        $driverTransferRequired = $this->payment_method !== 'cash'
            && $this->delivery_type === 'delivery'
            && $this->driver_id
            && $driverPayout > 0;
        $driverPayoutSettled = !$driverTransferRequired;

        // ═══════════════════════════════════════════════════════════════════
        // STRIPE TRANSFER : Libérer les fonds vers le prestataire
        // ═══════════════════════════════════════════════════════════════════
        $stripeTransferId = null;
        $driverStripeTransferId = null;
        
        // Transfert au prestataire (si paiement online et compte Stripe valide)
        if ($prestataireTransferRequired && $this->payment_intent_id && $prestataire?->stripe_account_id) {
            try {
                $stripeService = app(\App\Services\StripePaymentService::class);
                
                if ($stripeService->canReceiveTransfers($prestataire->stripe_account_id)) {
                    $transfer = $stripeService->transferToConnectedAccount(
                        $prestataire->stripe_account_id,
                        $prestatairePayout,
                        "Commande food #{$this->order_number} - Payout prestataire",
                        [
                            'food_order_id' => (string) $this->id,
                            'order_number' => $this->order_number,
                            'type' => 'prestataire_payout',
                        ],
                        $this->payment_intent_id
                    );
                    $stripeTransferId = $transfer->id;
                    $prestatairePayoutSettled = true;
                    \Illuminate\Support\Facades\Log::info("Transfer Stripe {$stripeTransferId} créé pour FoodOrder #{$this->id} (prestataire)");
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Erreur Transfer Stripe prestataire pour FoodOrder #{$this->id}: " . $e->getMessage());
            }
        }
        if ($prestataireTransferRequired && !$prestatairePayoutSettled) {
            \Illuminate\Support\Facades\Log::warning("Transfer Stripe prestataire non finalisé pour FoodOrder #{$this->id}", [
                'food_order_id' => $this->id,
                'payment_intent_id' => $this->payment_intent_id,
                'prestataire_id' => $this->prestataire_id,
                'stripe_account_id' => $prestataire?->stripe_account_id,
                'amount' => $prestatairePayout,
            ]);
        }
        
        // Transfert au livreur (si livraison et livreur avec compte Stripe)
        if ($driverTransferRequired && $this->payment_intent_id) {
            try {
                $driver = $this->driver;
                $driverStripeAccountId = $driver?->stripe_account_id ?? null;
                
                if ($driverStripeAccountId) {
                    $stripeService = app(\App\Services\StripePaymentService::class);
                    
                    if ($stripeService->canReceiveTransfers($driverStripeAccountId)) {
                        $driverTransfer = $stripeService->transferToConnectedAccount(
                            $driverStripeAccountId,
                            $driverPayout,
                            "Commande food #{$this->order_number} - Commission livreur",
                            [
                                'food_order_id' => (string) $this->id,
                                'order_number' => $this->order_number,
                                'type' => 'driver_payout',
                            ],
                            $this->payment_intent_id
                        );
                        $driverStripeTransferId = $driverTransfer->id;
                        $driverPayoutSettled = true;
                        \Illuminate\Support\Facades\Log::info("Transfer Stripe {$driverStripeTransferId} créé pour FoodOrder #{$this->id} (livreur)");
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Erreur Transfer Stripe livreur pour FoodOrder #{$this->id}: " . $e->getMessage());
            }
        }
        if ($driverTransferRequired && !$driverPayoutSettled) {
            \Illuminate\Support\Facades\Log::warning("Transfer Stripe livreur non finalisé pour FoodOrder #{$this->id}", [
                'food_order_id' => $this->id,
                'payment_intent_id' => $this->payment_intent_id,
                'driver_id' => $this->driver_id,
                'amount' => $driverPayout,
            ]);
        }

        $this->update([
            'platform_fee' => $platformFee,           // Commission admin (15€)
            'prestataire_payout' => $prestatairePayout, // Ce que reçoit le presta (85€)
            'driver_payout' => $driverPayout,          // Ce que reçoit le livreur (5€)
            'prestataire_paid_at' => $prestatairePayoutSettled ? $processedAt : null,
            'driver_paid_at' => $this->driver_id && $driverPayoutSettled ? $processedAt : null,
            'code_verified_at' => $processedAt,
            'status' => self::STATUS_COMPLETED,
            'delivery_status' => self::DELIVERY_STATUS_DELIVERED,
            // Escrow: libérer les fonds
            'escrow_status' => self::ESCROW_RELEASED,
            'amount_released' => $amountReleased,
            'released_at' => $processedAt,
            // Stripe Transfer IDs
            'stripe_transfer_id' => $stripeTransferId,
            'driver_stripe_transfer_id' => $driverStripeTransferId,
        ]);

        \Illuminate\Support\Facades\DB::commit();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error("processPayouts: erreur pour FoodOrder #{$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Bloquer le montant (acompte ou total) en escrow
     */
    public function holdPayment(float $amount): void
    {
        $this->update([
            'escrow_status' => self::ESCROW_HELD,
            'amount_held' => $amount,
            'held_at' => now(),
        ]);
    }

    /**
     * Rembourser le client (refus vendeur ou timeout)
     */
    public function refundPayment(string $reason = null, ?float $partialAmount = null): bool
    {
        if ($this->escrow_status === self::ESCROW_REFUNDED) {
            return false; // Déjà remboursé
        }

        $amountToRefund = $partialAmount ?? $this->amount_held ?? $this->total;

        // Appeler Stripe refund si paiement carte
        if ($this->payment_method === 'card' && $this->payment_intent_id) {
            try {
                $stripe = new \Stripe\StripeClient(config('services.stripe.secret') ?: config('stripe.secret'));
                // SÉCURITÉ C5: Clé d'idempotence pour le refund food order
                $stripe->refunds->create([
                    'payment_intent' => $this->payment_intent_id,
                    'amount' => (int) round($amountToRefund * 100), // En centimes
                    'reason' => 'requested_by_customer',
                ], [
                    'idempotency_key' => 'food_refund_' . $this->id . '_' . md5((string) $amountToRefund),
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Erreur refund Stripe FoodOrder #' . $this->id . ': ' . $e->getMessage());
                return false;
            }
        }

        $this->update([
            'escrow_status' => $partialAmount ? self::ESCROW_PARTIAL_REFUND : self::ESCROW_REFUNDED,
            'amount_refunded' => $amountToRefund,
            'refunded_at' => now(),
            'refund_reason' => $reason,
            'payment_status' => self::PAYMENT_REFUNDED,
        ]);

        return true;
    }

    /**
     * Capturer le paiement (pour livraisons externes)
     * Appelé quand : vendeur accepte ET livreur accepte
     */
    public function capturePayment(): bool
    {
        // Vérifier que c'est une autorisation en attente de capture
        if ($this->payment_status !== self::PAYMENT_PENDING_CAPTURE) {
            \Illuminate\Support\Facades\Log::warning("FoodOrder #{$this->id} n'est pas en pending_capture");
            return false;
        }

        if (!$this->payment_intent_id) {
            \Illuminate\Support\Facades\Log::error("FoodOrder #{$this->id} n'a pas de payment_intent_id");
            return false;
        }

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret') ?: config('stripe.secret'));
            
            // Capturer le paiement autorisé
            $paymentIntent = $stripe->paymentIntents->capture($this->payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                $this->update([
                    'payment_status' => self::PAYMENT_PAID,
                    'escrow_status' => self::ESCROW_HELD,
                    'paid_at' => now(),
                ]);
                
                \Illuminate\Support\Facades\Log::info("Paiement capturé pour FoodOrder #{$this->id}");
                return true;
            }

            \Illuminate\Support\Facades\Log::error("Capture échouée pour FoodOrder #{$this->id}: status={$paymentIntent->status}");
            return false;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erreur capture Stripe FoodOrder #{$this->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Annuler l'autorisation (pas de capture - remboursement automatique)
     * Appelé quand : vendeur refuse OU timeout 24h sans validation code
     */
    public function cancelAuthorization(string $reason = null): bool
    {
        // Vérifier que c'est une autorisation en attente
        if (!in_array($this->payment_status, [self::PAYMENT_PENDING_CAPTURE, 'pending_capture'])) {
            // Si déjà capturé, utiliser refundPayment à la place
            if ($this->payment_status === self::PAYMENT_PAID) {
                return $this->refundPayment($reason);
            }
            return false;
        }

        if (!$this->payment_intent_id) {
            return false;
        }

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret') ?: config('stripe.secret'));
            
            // Annuler le PaymentIntent non capturé (libère l'autorisation)
            $stripe->paymentIntents->cancel($this->payment_intent_id);

            $this->update([
                'payment_status' => self::PAYMENT_PENDING,
                'escrow_status' => self::ESCROW_CANCELLED,
                'refund_reason' => $reason ?? 'Autorisation annulée',
                'refunded_at' => now(),
            ]);
            
            \Illuminate\Support\Facades\Log::info("Autorisation annulée pour FoodOrder #{$this->id}: {$reason}");
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erreur annulation autorisation FoodOrder #{$this->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculer le montant à payer à la commande (acompte ou total)
     */
    public function calculateAmountDueNow(): array
    {
        // Récupérer la politique de paiement dominante des produits
        $policy = $this->getPaymentPolicy();

        switch ($policy['type']) {
            case 'full_prepay':
                return [
                    'type' => 'full_prepay',
                    'amount' => $this->total,
                    'label' => 'Paiement total',
                ];
            case 'deposit':
                $depositAmount = round($this->total * ($policy['percent'] / 100), 2);
                return [
                    'type' => 'deposit',
                    'amount' => $depositAmount,
                    'percent' => $policy['percent'],
                    'remaining' => $this->total - $depositAmount,
                    'label' => 'Acompte ' . $policy['percent'] . '%',
                ];
            case 'cash':
            default:
                return [
                    'type' => 'cash',
                    'amount' => 0,
                    'label' => 'Paiement en main propre',
                ];
        }
    }

    /**
     * Déterminer la politique de paiement (basée sur les produits)
     */
    public function getPaymentPolicy(): array
    {
        if (function_exists('food_online_payment_enabled') && !food_online_payment_enabled()) {
            return ['type' => 'cash', 'percent' => 0];
        }

        if (function_exists('cash_only_mode') && cash_only_mode()) {
            return ['type' => 'cash', 'percent' => 0];
        }

        $storedMethod = (string) ($this->payment_method ?? '');

        // Priorité au mode enregistré sur la commande (snapshot au moment du checkout)
        if ($storedMethod === 'cash') {
            return ['type' => 'cash', 'percent' => 0];
        }

        // Charger les produits si pas déjà fait
        $items = $this->items()->with('foodProduct')->get();

        // Si un produit exige paiement total, c'est paiement total
        foreach ($items as $item) {
            if ($item->foodProduct && $item->foodProduct->payment_policy === 'full_prepay') {
                return ['type' => 'full_prepay', 'percent' => 100];
            }
        }

        // Si un produit exige acompte, prendre le plus élevé
        $maxDeposit = 0;
        foreach ($items as $item) {
            if ($item->foodProduct && $item->foodProduct->payment_policy === 'deposit') {
                $maxDeposit = max($maxDeposit, $item->foodProduct->deposit_percent ?? 30);
            }
        }

        if ($maxDeposit > 0) {
            return ['type' => 'deposit', 'percent' => $maxDeposit];
        }

        if (in_array($storedMethod, ['online', 'card'], true)) {
            return ['type' => 'full_prepay', 'percent' => 100];
        }

        // Par défaut: espèces
        return ['type' => 'cash', 'percent' => 0];
    }

    /**
     * Estimation financière d'une annulation.
     * Utilisé pour afficher clairement "Stripe prend X, client remboursé Y".
     *
     * @param string $cancelledBy client|prestataire
     */
    public function getCancellationBreakdown(string $cancelledBy = 'client'): array
    {
        $cancelledBy = strtolower(trim($cancelledBy));
        if (!in_array($cancelledBy, ['client', 'prestataire'], true)) {
            $cancelledBy = 'client';
        }

        $paymentMethod = (string) ($this->payment_method ?? 'cash');
        $paymentStatus = (string) ($this->payment_status ?? self::PAYMENT_PENDING);
        $escrowStatus = (string) ($this->escrow_status ?? self::ESCROW_NONE);
        $amountHeld = round((float) ($this->amount_held ?? 0), 2);

        $isOnline = $paymentMethod !== 'cash';
        $isPendingAuthorization = $paymentStatus === self::PAYMENT_PENDING_CAPTURE
            || $escrowStatus === self::ESCROW_PENDING;
        $isCaptured = in_array($escrowStatus, [self::ESCROW_HELD, self::ESCROW_PARTIAL_REFUND], true)
            || in_array($paymentStatus, [self::PAYMENT_PAID, 'partial'], true);

        $response = [
            'cancelled_by' => $cancelledBy,
            'is_online' => $isOnline,
            'action' => 'none', // none|cancel_authorization|refund
            'amount_paid' => $amountHeld,
            'stripe_fee_amount' => 0.0,
            'client_refund_amount' => 0.0,
            'fee_payer' => 'none', // none|client|prestataire
            'explanation' => 'Aucun flux de remboursement carte.',
        ];

        if (!$isOnline || $amountHeld <= 0) {
            return $response;
        }

        if ($isPendingAuthorization) {
            $response['action'] = 'cancel_authorization';
            $response['client_refund_amount'] = $amountHeld;
            $response['explanation'] = 'Paiement seulement autorisé. Pas de débit final si annulation.';
            return $response;
        }

        if ($isCaptured) {
            $stripeFee = min(
                round((float) \App\Services\CommissionService::stripeFeesAmount($amountHeld), 2),
                $amountHeld
            );
            $response['action'] = 'refund';
            $response['stripe_fee_amount'] = $stripeFee;

            if ($cancelledBy === 'prestataire') {
                $response['client_refund_amount'] = $amountHeld;
                $response['fee_payer'] = 'prestataire';
                $response['explanation'] = 'Remboursement client intégral. Les frais Stripe sont imputés au prestataire.';
            } else {
                $response['client_refund_amount'] = max(0, round($amountHeld - $stripeFee, 2));
                $response['fee_payer'] = 'client';
                $response['explanation'] = 'Annulation client après paiement: frais Stripe déduits du remboursement.';
            }
        }

        return $response;
    }

    // Statuts de commande
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_SCHEDULED = 'scheduled'; // Commande planifiée acceptée, en attente du jour J
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY = 'ready';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Statuts de livraison
    const DELIVERY_STATUS_PENDING = 'pending';
    const DELIVERY_STATUS_ASSIGNED = 'assigned';
    const DELIVERY_STATUS_PICKED_UP = 'picked_up';
    const DELIVERY_STATUS_IN_TRANSIT = 'in_transit';
    const DELIVERY_STATUS_DELIVERED = 'delivered';
    const DELIVERY_STATUS_FAILED = 'failed';

    // Types de livraison
    const DELIVERY_PICKUP = 'pickup';
    const DELIVERY_DELIVERY = 'delivery';

    // Statuts de paiement
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PENDING_CAPTURE = 'pending_capture'; // Autorisé mais pas encore prélevé
    const PAYMENT_PAID = 'paid';
    const PAYMENT_REFUNDED = 'refunded';

    // Statuts escrow (blocage financier)
    const ESCROW_NONE = 'none';
    const ESCROW_PENDING = 'pending'; // Autorisé, en attente de capture (livraison externe)
    const ESCROW_HELD = 'held';
    const ESCROW_RELEASED = 'released';
    const ESCROW_REFUNDED = 'refunded';
    const ESCROW_PARTIAL_REFUND = 'partial_refund';
    const ESCROW_CANCELLED = 'cancelled'; // Autorisation annulée (pas de capture)

    // Sécurité code
    const MAX_CODE_ATTEMPTS = 5;
    const CODE_LOCK_MINUTES = 30;
    const CODE_EXPIRY_HOURS = 24;

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_ACCEPTED => 'Acceptée',
            self::STATUS_SCHEDULED => 'Planifiée',
            self::STATUS_PREPARING => 'En préparation',
            self::STATUS_READY => 'Prête',
            self::STATUS_DELIVERED => 'Livrée',
            self::STATUS_COMPLETED => 'Terminée',
            self::STATUS_CANCELLED => 'Annulée',
        ];
    }

    public static function deliveryTypes(): array
    {
        return [
            self::DELIVERY_PICKUP => 'À emporter',
            self::DELIVERY_DELIVERY => 'Livraison',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'CMD';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "{$prefix}{$date}{$random}";
    }

    // Relations
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class);
    }

    public function driver()
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id');
    }

    public function items()
    {
        return $this->hasMany(FoodOrderItem::class);
    }

    // Méthodes livreur
    public function assignDriver(DeliveryDriver $driver): bool
    {
        $this->update([
            'driver_id' => $driver->id,
            'delivery_status' => self::DELIVERY_STATUS_ASSIGNED,
        ]);
        return true;
    }

    public function markAsPickedUp(): bool
    {
        $this->update([
            'delivery_status' => self::DELIVERY_STATUS_PICKED_UP,
            'picked_up_at' => now(),
        ]);
        return true;
    }

    public function markAsInTransit(): bool
    {
        $this->update([
            'delivery_status' => self::DELIVERY_STATUS_IN_TRANSIT,
        ]);
        return true;
    }

    public function isAssignedToDriver(): bool
    {
        return $this->driver_id !== null;
    }

    public function needsDriver(): bool
    {
        return $this->delivery_type === self::DELIVERY_DELIVERY 
            && !$this->isAssignedToDriver()
            && in_array($this->status, [self::STATUS_ACCEPTED, self::STATUS_PREPARING, self::STATUS_READY]);
    }

    public function getDeliveryStatusLabelAttribute(): string
    {
        return match($this->delivery_status) {
            self::DELIVERY_STATUS_PENDING => 'En attente',
            self::DELIVERY_STATUS_ASSIGNED => 'Livreur assigné',
            self::DELIVERY_STATUS_PICKED_UP => 'Récupérée',
            self::DELIVERY_STATUS_IN_TRANSIT => 'En cours',
            self::DELIVERY_STATUS_DELIVERED => 'Livrée',
            self::DELIVERY_STATUS_FAILED => 'Échec',
            default => 'Inconnu',
        };
    }

    // Accesseurs
    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    public function getDeliveryTypeLabelAttribute(): string
    {
        return self::deliveryTypes()[$this->delivery_type] ?? $this->delivery_type;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_ACCEPTED => 'info',
            self::STATUS_PREPARING => 'primary',
            self::STATUS_READY => 'success',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'clock',
            self::STATUS_ACCEPTED => 'check-circle',
            self::STATUS_PREPARING => 'fire',
            self::STATUS_READY => 'bag-check',
            self::STATUS_DELIVERED => 'truck',
            self::STATUS_COMPLETED => 'star-fill',
            self::STATUS_CANCELLED => 'x-circle',
            default => 'question-circle',
        };
    }

    // Méthodes de statut
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted(): bool
    {
        // Commande acceptée directement
        if ($this->status === self::STATUS_ACCEPTED) {
            return true;
        }
        
        // Commande scheduled dont le Jour J est arrivé (peut commencer la préparation)
        if ($this->status === self::STATUS_SCHEDULED) {
            // Si pas de date programmée ou date <= aujourd'hui
            if (!$this->requested_at || now()->startOfDay()->gte(\Carbon\Carbon::parse($this->requested_at)->startOfDay())) {
                return true;
            }
        }
        
        return false;
    }

    public function isPreparing(): bool
    {
        return $this->status === self::STATUS_PREPARING;
    }

    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY;
    }

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_ACCEPTED,
        ]);
    }

    public function isFullyConfirmed(): bool
    {
        return $this->client_confirmed && $this->prestataire_confirmed;
    }

    /**
     * Vérifie si cette commande nécessite un livreur externe
     * (livraison + prestataire en mode externe uniquement)
     */
    public function requiresExternalDriver(): bool
    {
        if ($this->delivery_type !== 'delivery') {
            return false;
        }
        
        $deliveryMode = $this->prestataire->delivery_mode ?? 'both';
        return $deliveryMode === 'external';
    }

    /**
     * Vérifie si un livreur a accepté cette commande
     */
    public function hasDriverAccepted(): bool
    {
        return $this->driver_id !== null && $this->driver_accepted_at !== null;
    }

    /**
     * Vérifie si la préparation peut commencer
     * Pour les commandes nécessitant un livreur externe, il faut qu'un livreur ait accepté
     */
    public function canStartPreparing(): bool
    {
        if (!$this->isAccepted()) {
            return false;
        }
        
        // Si livraison externe requise, un livreur doit avoir accepté
        if ($this->requiresExternalDriver() && !$this->hasDriverAccepted()) {
            return false;
        }
        
        return true;
    }

    // Actions sur la commande
    public function accept(): void
    {
        // Si c'est une commande planifiée pour un jour futur, elle va en "scheduled"
        // (en attente dans l'agenda, pas active en cuisine)
        if ($this->requested_at && $this->requested_at->isAfter(now()->endOfDay())) {
            $this->update([
                'status' => self::STATUS_SCHEDULED,
                'accepted_at' => now(),
            ]);
            
            // Programmer les rappels 4h avant via OneSignal
            $this->scheduleReminder4h();
        } else {
            // Commande pour aujourd'hui ou immédiate → acceptée et active en cuisine
            $this->update([
                'status' => self::STATUS_ACCEPTED,
                'accepted_at' => now(),
            ]);
            
            // Si la commande est pour plus tard dans la journée (> 4h), programmer le rappel
            if ($this->requested_at && $this->requested_at->isAfter(now()->addHours(4))) {
                $this->scheduleReminder4h();
            }
        }
    }

    /**
     * Programmer les notifications de rappel 4h avant via OneSignal
     */
    public function scheduleReminder4h(): void
    {
        if (!$this->requested_at) {
            return;
        }

        $reminderTime = $this->requested_at->copy()->subHours(4);
        
        // Si le rappel est déjà passé, ne rien faire
        if ($reminderTime->isPast()) {
            return;
        }

        try {
            $oneSignal = app(\App\Services\OneSignalService::class);
            
            if (!$oneSignal->isConfigured()) {
                \Illuminate\Support\Facades\Log::warning("OneSignal non configuré pour rappel 4h commande #{$this->id}");
                return;
            }

            $timeText = $this->requested_at->setTimezone('Europe/Paris')->format('H:i');

            // Rappel CLIENT
            if ($this->client_id) {
                $clientTitle = '⏰ Rappel : votre commande dans 4h';
                $clientMessage = "Votre commande #{$this->order_number} est prévue à {$timeText}. Préparez-vous !";
                
                $clientResult = $oneSignal->scheduleNotification(
                    $this->client_id,
                    $clientTitle,
                    $clientMessage,
                    $reminderTime,
                    [
                        'type' => 'food_order_reminder_4h',
                        'order_id' => $this->id,
                        'url' => route('food.orders.show', $this),
                    ]
                );

                if ($clientResult) {
                    \Illuminate\Support\Facades\Log::info("Rappel 4h client programmé pour commande #{$this->id}", [
                        'reminder_at' => $reminderTime->toIso8601String(),
                        'onesignal_id' => $clientResult,
                    ]);
                }
            }

            // Rappel PRESTATAIRE
            $prestataireUserId = $this->prestataire?->user_id;
            if ($prestataireUserId) {
                $prestaTitle = '⏰ Rappel : commande dans 4h';
                $prestaMessage = "Commande #{$this->order_number} prévue à {$timeText}. Préparez-vous à la traiter !";
                
                $prestaResult = $oneSignal->scheduleNotification(
                    $prestataireUserId,
                    $prestaTitle,
                    $prestaMessage,
                    $reminderTime,
                    [
                        'type' => 'food_order_reminder_4h',
                        'order_id' => $this->id,
                        'url' => route('prestataire.food-orders.show', $this),
                    ]
                );

                if ($prestaResult) {
                    \Illuminate\Support\Facades\Log::info("Rappel 4h prestataire programmé pour commande #{$this->id}", [
                        'reminder_at' => $reminderTime->toIso8601String(),
                        'onesignal_id' => $prestaResult,
                    ]);
                }
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erreur programmation rappel 4h commande #{$this->id}: " . $e->getMessage());
        }
    }

    public function startPreparing(): void
    {
        $this->update([
            'status' => self::STATUS_PREPARING,
            'preparing_at' => now(),
        ]);
    }

    public function markAsReady(): void
    {
        $this->update([
            'status' => self::STATUS_READY,
            'ready_at' => now(),
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);
    }

    public function complete(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        // Restaurer le stock (seulement si le produit existe encore)
        foreach ($this->items as $item) {
            if ($item->foodProduct && method_exists($item->foodProduct, 'incrementStock')) {
                try {
                    $item->foodProduct->incrementStock($item->quantity);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Erreur restauration stock produit #' . $item->food_product_id . ': ' . $e->getMessage());
                }
            }
        }
    }

    public function confirmByClient(): void
    {
        $this->update(['client_confirmed' => true]);
        $this->checkAndComplete();
    }

    public function confirmByPrestataire(): void
    {
        $this->update(['prestataire_confirmed' => true]);
        $this->checkAndComplete();
    }

    protected function checkAndComplete(): void
    {
        if ($this->isFullyConfirmed() && $this->status === self::STATUS_DELIVERED) {
            // D'abord traiter les paiements (si pas déjà fait)
            if ($this->escrow_status === self::ESCROW_HELD && !$this->code_verified_at) {
                // Le client a confirmé réception sans que le code soit vérifié
                // → On déclenche quand même les payouts
                $this->processPayouts();
            }
            $this->complete();
        }
    }

    public function markAsPaid(string $paymentIntentId = null): void
    {
        $this->update([
            'payment_status' => self::PAYMENT_PAID,
            'payment_intent_id' => $paymentIntentId,
            'paid_at' => now(),
        ]);
    }

    /**
     * ╔══════════════════════════════════════════════════════════════════════╗
     * ║                    CALCUL DES TOTAUX - FOOD ORDER                    ║
     * ╠══════════════════════════════════════════════════════════════════════╣
     * ║                                                                      ║
     * ║  EXEMPLE : Commande de 100€ de plats + livraison                     ║
     * ║                                                                      ║
     * ║  ┌─────────────────────────────────────────────────────────────┐     ║
     * ║  │ CE QUE LE CLIENT PAIE                                       │     ║
     * ║  ├─────────────────────────────────────────────────────────────┤     ║
     * ║  │ Subtotal (prix plats)      = 100.00€                        │     ║
     * ║  │ + Frais service client (5%)= +  5.00€  → VA À L'ADMIN       │     ║
     * ║  │ + Frais livraison          = +  5.00€  → VA AU LIVREUR      │     ║
     * ║  │ ────────────────────────────────────────                    │     ║
     * ║  │ TOTAL CLIENT               = 110.00€                        │     ║
     * ║  └─────────────────────────────────────────────────────────────┘     ║
     * ║                                                                      ║
     * ║  À LA LIVRAISON (processPayouts) :                                   ║
     * ║  ┌─────────────────────────────────────────────────────────────┐     ║
     * ║  │ STRIPE prélève           ~1.79€ (1.4% + 0.25€)              │     ║
     * ║  │ ADMIN reçoit             = 20.00€ (commission + frais client)│    ║
     * ║  │ PRESTATAIRE reçoit       = 85.00€ (subtotal - 15% commiss.) │     ║
     * ║  │ LIVREUR reçoit           =  5.00€ (frais livraison)         │     ║
     * ║  └─────────────────────────────────────────────────────────────┘     ║
     * ║                                                                      ║
     * ╚══════════════════════════════════════════════════════════════════════╝
     */
    public function calculateTotals(): void
    {
        // ═══════════════════════════════════════════════════════════════════
        // ÉTAPE 1 : Calcul du subtotal (somme des prix des plats)
        // ═══════════════════════════════════════════════════════════════════
        $subtotal = $this->items->sum('total_price');
        
        // ═══════════════════════════════════════════════════════════════════
        // ÉTAPE 2 : Frais de service CLIENT (ajouté au prix)
        // → Ce montant est PAYÉ PAR LE CLIENT en plus du subtotal
        // → Ce montant VA À L'ADMIN (plateforme)
        // → Paramètre admin : commission_client_food
        // ═══════════════════════════════════════════════════════════════════
        $clientFeeRate = (float) get_setting('commission_client_food', '0'); // Ex: 5%
        $serviceFee = round($subtotal * ($clientFeeRate / 100), 2);
        
        // ═══════════════════════════════════════════════════════════════════
        // ÉTAPE 3 : Frais de livraison (si mode livraison)
        // → Ce montant est PAYÉ PAR LE CLIENT
        // → Ce montant VA AU LIVREUR
        // ═══════════════════════════════════════════════════════════════════
        $deliveryFee = $this->delivery_type === self::DELIVERY_DELIVERY ? 5.00 : 0;
        
        // ═══════════════════════════════════════════════════════════════════
        // TOTAL = Ce que le client paie
        // ═══════════════════════════════════════════════════════════════════
        $total = round($subtotal + $serviceFee + $deliveryFee, 2);
        
        $this->update([
            'subtotal' => $subtotal,       // Prix des plats (base pour commission presta)
            'service_fee' => $serviceFee,  // Frais client → va à l'admin
            'delivery_fee' => $deliveryFee,// Frais livraison → va au livreur
            'total' => $total,             // Total payé par client
        ]);
    }

    public function getEstimatedPreparationTime(): int
    {
        return $this->items->sum(function ($item) {
            // Gérer le cas où le produit a été supprimé
            $prepTime = $item->foodProduct?->preparation_time ?? 15;
            return $prepTime * $item->quantity;
        });
    }
}
