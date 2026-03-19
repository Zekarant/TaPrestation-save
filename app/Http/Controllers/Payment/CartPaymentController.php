<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\EquipmentRental;
use App\Models\EquipmentRentalRequest;
use App\Models\PaymentAllocation;
use App\Models\PaymentTransaction;
use App\Models\UrgentSale;
use App\Models\UrgentSalePurchase;
use App\Services\CartPricingService;
use App\Services\StripePaymentService;
use App\Services\EscrowService;
use App\Services\InvoiceGenerationService;
use App\Services\PaymentConsentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CartPaymentController extends Controller
{
    public function __construct(
        private readonly StripePaymentService $stripe,
        private readonly CartPricingService $pricing,
        private readonly InvoiceGenerationService $invoiceService,
        private readonly EscrowService $escrowService,
        private readonly PaymentConsentService $consentService
    ) {
        // Permettre aux clients ET prestataires de payer via le panier
        $this->middleware(['auth', 'role:client|prestataire']);
    }

    public function show()
    {
        if (function_exists('checkout_payments_enabled') && !checkout_payments_enabled()) {
            return redirect()->route('client.cart.index')
                ->with('info', 'Le checkout en ligne est désactivé pour le moment.');
        }

        $cart = Cart::forUserActive(Auth::id());
        $cart->load('items.purchasable');

        if ($cart->items->isEmpty()) {
            return redirect()->route('client.cart.index')->with('error', 'Votre panier est vide.');
        }

        $urgentSaleError = $this->firstUrgentSaleEligibilityError($cart, (int) Auth::id());
        if ($urgentSaleError) {
            return redirect()->route('client.cart.index')->with('error', $urgentSaleError);
        }

        $totals = [
            'deposit' => $cart->items->sum(fn (CartItem $i) => (float) $i->line_deposit),
            'full' => $cart->items->sum(fn (CartItem $i) => (float) $i->line_total),
        ];

        // Vérifier si un des items a payment_requirement = 'full' (paiement obligatoire)
        // Dans ce cas, on ne montre pas l'option acompte
        $requiresFullPayment = $cart->items->contains(function (CartItem $item) {
            $purchasable = $item->purchasable;
            if ($purchasable instanceof UrgentSale) {
                $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
                    ? normalize_payment_requirement_for_mode($purchasable->payment_requirement ?? 'none')
                    : ($purchasable->payment_requirement ?? 'none');

                return $paymentRequirement === 'full';
            }
            return false;
        });

        // Vérifier si le(s) prestataire(s) ont configuré un moyen de paiement
        $paymentInfo = $this->getCartPaymentInfo($cart);

        // Si aucun moyen de paiement configuré, message d'erreur
        if (!$paymentInfo['has_stripe_connect']) {
            return redirect()->route('client.cart.index')
                ->with('error', 'Le vendeur n\'a pas encore configuré de moyen de paiement. Contactez-le directement.');
        }

        // Détecter si le panier nécessite un paiement escrow (urgent sales, equipment rentals)
        // Dans ce cas, le PaymentIntent est créé sur le compte plateforme, pas sur le compte connecté
        $hasUrgentSale = $cart->items->contains(fn (CartItem $i) => $i->purchasable instanceof UrgentSale);
        $hasEquipmentRental = $cart->items->contains(fn (CartItem $i) => $i->purchasable instanceof EquipmentRentalRequest);
        $useEscrow = $hasUrgentSale || $hasEquipmentRental;

        return view('payments.cart-payment', compact('cart', 'totals', 'requiresFullPayment', 'paymentInfo', 'useEscrow'));
    }

    /**
     * Récupère les infos de paiement des prestataires du panier
     */
    private function getCartPaymentInfo(Cart $cart): array
    {
        $prestataires = [];
        $hasStripeConnect = false;
        $noPaymentMethod = true;
        $validStripeAccountId = null;

        foreach ($cart->items as $item) {
            $purchasable = $item->purchasable;
            $prestataire = null;

            if ($purchasable instanceof UrgentSale) {
                $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
                    ? normalize_payment_requirement_for_mode($purchasable->payment_requirement ?? 'none')
                    : ($purchasable->payment_requirement ?? 'none');

                if ($paymentRequirement !== 'full') {
                    continue;
                }
                $prestataire = $purchasable->prestataire;
            } elseif ($purchasable instanceof Booking) {
                $prestataire = $purchasable->service?->prestataire;
            } elseif ($purchasable instanceof EquipmentRentalRequest) {
                $prestataire = $purchasable->equipment?->prestataire;
            }

            if ($prestataire && !isset($prestataires[$prestataire->id])) {
                $pHasStripe = !empty($prestataire->stripe_account_id);

                // Vérifier que le compte Stripe peut recevoir des transferts
                $stripeAccountValid = false;
                if ($pHasStripe) {
                    $stripeAccountValid = $this->stripe->canReceiveTransfers($prestataire->stripe_account_id);
                    if ($stripeAccountValid && !$validStripeAccountId) {
                        $validStripeAccountId = $prestataire->stripe_account_id;
                    }
                }

                $prestataires[$prestataire->id] = [
                    'prestataire' => $prestataire,
                    'has_stripe' => $pHasStripe && $stripeAccountValid,
                    'stripe_account_id' => $stripeAccountValid ? $prestataire->stripe_account_id : null,
                ];

                if ($pHasStripe && $stripeAccountValid) {
                    $hasStripeConnect = true;
                    $noPaymentMethod = false;
                }
            }
        }

        return [
            'prestataires' => $prestataires,
            'has_stripe_connect' => $hasStripeConnect,
            'no_payment_method' => $noPaymentMethod,
            'stripe_account_id' => $validStripeAccountId,
        ];
    }

    private function cartFingerprint(Cart $cart): string
    {
        $payload = $cart->items
            ->map(function (CartItem $item) {
                return [
                    'type' => (string) $item->purchasable_type,
                    'id' => (int) $item->purchasable_id,
                    'qty' => (int) $item->quantity,
                    'unit' => round((float) ($item->unit_price ?? 0), 2),
                    'line_total' => round((float) ($item->line_total ?? 0), 2),
                    'line_deposit' => round((float) ($item->line_deposit ?? 0), 2),
                ];
            })
            ->sortBy(fn ($row) => $row['type'] . '#' . $row['id'])
            ->values()
            ->all();

        return hash('sha256', json_encode($payload));
    }

    private function firstUrgentSaleStockError(Cart $cart): ?string
    {
        foreach ($cart->items as $item) {
            $sale = $item->purchasable;
            if (!$sale instanceof UrgentSale) {
                continue;
            }

            if ((int) ($item->quantity ?? 0) <= 0) {
                return 'Quantité invalide dans le panier.';
            }

            if (($sale->status ?? 'active') !== 'active') {
                return "Le produit \"{$sale->title}\" n'est plus disponible.";
            }

            if ((int) ($sale->quantity ?? 0) < (int) $item->quantity) {
                return "Stock insuffisant pour \"{$sale->title}\".";
            }
        }

        return null;
    }

    private function firstUrgentSaleEligibilityError(Cart $cart, ?int $buyerUserId = null): ?string
    {
        foreach ($cart->items as $item) {
            $sale = $item->purchasable;
            if (!$sale instanceof UrgentSale) {
                continue;
            }

            if (($sale->status ?? 'active') !== 'active') {
                return "Le produit \"{$sale->title}\" n'est plus disponible.";
            }

            $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
                ? normalize_payment_requirement_for_mode($sale->payment_requirement ?? 'none')
                : ($sale->payment_requirement ?? 'none');

            if ($paymentRequirement !== 'full') {
                return "Le produit \"{$sale->title}\" n'accepte pas le paiement en ligne.";
            }

            $sellerUserId = (int) ($sale->prestataire?->user_id ?? 0);
            if ($buyerUserId && $sellerUserId > 0 && $sellerUserId === $buyerUserId) {
                return "Vous ne pouvez pas acheter votre propre annonce (\"{$sale->title}\").";
            }

            if (empty($sale->prestataire?->stripe_account_id)) {
                return "Le vendeur de \"{$sale->title}\" n'a pas activé le paiement en ligne.";
            }
        }

        return null;
    }

    public function createIntent(Request $request)
    {
        $cart = Cart::forUserActive(Auth::id());
        $cart->load('items.purchasable');

        if ($cart->items->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 422);
        }

        $validated = $request->validate([
            'payment_type' => 'required|in:deposit,full',
        ]);

        // Ensure snapshots are fresh enough (recalculate urgent sales in case quantities changed)
        foreach ($cart->items as $item) {
            if ($item->purchasable instanceof UrgentSale) {
                $p = $this->pricing->pricingFor($item->purchasable, $item->quantity);
                $item->update([
                    'unit_price' => $p['unit_price'],
                    'line_total' => $p['line_total'],
                    'line_deposit' => $p['line_deposit'],
                    'currency' => $p['currency'],
                ]);
            }
        }
        $cart->refresh()->load('items.purchasable');

        $stockError = $this->firstUrgentSaleStockError($cart);
        if ($stockError) {
            return response()->json(['error' => $stockError], 409);
        }

        $urgentSaleError = $this->firstUrgentSaleEligibilityError($cart, (int) Auth::id());
        if ($urgentSaleError) {
            return response()->json(['error' => $urgentSaleError], 422);
        }

        $amount = $validated['payment_type'] === 'deposit'
            ? (float) $cart->items->sum(fn (CartItem $i) => (float) $i->line_deposit)
            : (float) $cart->items->sum(fn (CartItem $i) => (float) $i->line_total);

        if ($amount <= 0) {
            return response()->json(['error' => 'Montant invalide. Le montant du panier doit être supérieur à zéro.'], 422);
        }

        // Récupérer les infos de paiement du prestataire
        $paymentInfo = $this->getCartPaymentInfo($cart);

        $hasUrgentSale = $cart->items->contains(fn (CartItem $i) => $i->purchasable instanceof UrgentSale);

        // Pour les ventes urgentes (annonces) en escrow: PaymentIntent sur la plateforme
        // afin de pouvoir bloquer les fonds et effectuer les remboursements automatiquement.
        // Sinon: Connect direct charges.
        $stripeAccountId = $hasUrgentSale ? null : ($paymentInfo['stripe_account_id'] ?? null);

        $description = "Cart #{$cart->id} - {$validated['payment_type']}";
        $metadata = [
            'cart_id' => (string) $cart->id,
            'payment_type' => $validated['payment_type'],
            'user_id' => (string) Auth::id(),
            'cart_fingerprint' => $this->cartFingerprint($cart),
        ];

        if ($hasUrgentSale) {
            $metadata['escrow_flow'] = 'platform_hold';
        }

        $prestataireId = null;
        $prestataires = (array) ($paymentInfo['prestataires'] ?? []);
        if (count($prestataires) === 1) {
            $prestataireId = (int) array_key_first($prestataires);
        } else {
            foreach ($prestataires as $pid => $pInfo) {
                if (!empty($pInfo['stripe_account_id']) && $pInfo['stripe_account_id'] === $stripeAccountId) {
                    $prestataireId = (int) $pid;
                    break;
                }
            }
        }
        if ($prestataireId) {
            $metadata['prestataire_id'] = (string) $prestataireId;
        }

        // Optional client fees (adds on top of base cart amount)
        $prestataire = null;
        if ($prestataireId) {
            $prestataire = (\App\Models\Prestataire::find($prestataireId));
        }

        $clientFeeTotal = 0.0;
        $prestataireFeeTotal = 0.0;

        foreach ($cart->items as $item) {
            $lineBase = $validated['payment_type'] === 'deposit'
                ? (float) $item->line_deposit
                : (float) $item->line_total;

            $type = 'service';
            if ($item->purchasable instanceof UrgentSale) {
                $type = 'urgent_sale';
            } elseif ($item->purchasable instanceof EquipmentRentalRequest) {
                $type = 'rental';
            } elseif ($item->purchasable instanceof Booking) {
                $type = 'service';
            }

            $clientFeeTotal += \App\Services\CommissionService::feeAmount($lineBase, $type, 'client', Auth::user(), $prestataire);
            $prestataireFeeTotal += \App\Services\CommissionService::feeAmount($lineBase, $type, 'prestataire', Auth::user(), $prestataire);
        }

        $clientFeeTotal = round($clientFeeTotal, 2);
        $prestataireFeeTotal = round($prestataireFeeTotal, 2);

        $baseAmount = (float) $amount;
        $amountToCharge = round($baseAmount + $clientFeeTotal, 2);

        $metadata['base_amount'] = (string) $baseAmount;
        $metadata['client_fee_total'] = (string) $clientFeeTotal;
        $metadata['prestataire_fee_total'] = (string) $prestataireFeeTotal;

        // Stripe fee total (configurable via CommissionService / admin settings)
        $stripeFeeTotal = \App\Services\CommissionService::stripeFeesAmount($amountToCharge);
        $metadata['stripe_fee_total'] = (string) round($stripeFeeTotal, 2);

        // Vérifier si on a des items qui nécessitent l'escrow
        $hasEquipmentRental = $cart->items->contains(fn (CartItem $i) => $i->purchasable instanceof EquipmentRentalRequest);

        try {
            // Utiliser escrow pour: ventes urgentes, locations d'équipement
            // Les fonds sont bloqués sur la plateforme jusqu'à confirmation
            $useEscrow = $hasUrgentSale || $hasEquipmentRental;
            
            if ($useEscrow) {
                // ESCROW: PaymentIntent sur le compte plateforme pour blocage des fonds
                $intent = $this->stripe->createEscrowPaymentIntent(
                    Auth::user(),
                    $amountToCharge,
                    $description,
                    $metadata,
                    $paymentInfo['stripe_account_id'] ?? null // Stocké en metadata pour le transfer ultérieur
                );
            } else {
                $intent = $this->stripe->createPaymentIntent(Auth::user(), $amountToCharge, $description, $metadata, $stripeAccountId);
            }

            return response()->json([
                'clientSecret' => $intent->client_secret,
                'paymentIntentId' => $intent->id,
                'amount' => $amountToCharge,
            ]);
        } catch (\Exception $e) {
            Log::error('Cart createIntent failed: ' . $e->getMessage(), ['user' => Auth::id()]);
            return response()->json(['error' => 'Impossible de créer le paiement. Veuillez réessayer.'], 422);
        }
    }

    public function confirm(Request $request)
    {
        $cart = Cart::forUserActive(Auth::id());
        $cart->load('items.purchasable');

        if ($cart->status === 'checked_out') {
            return response()->json([
                'success' => true,
                'message' => 'Cart already checked out',
            ]);
        }

        if ($cart->items->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 422);
        }

        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
            'payment_type' => 'required|in:deposit,full',
        ]);

        $urgentSaleError = $this->firstUrgentSaleEligibilityError($cart, (int) Auth::id());
        if ($urgentSaleError) {
            return response()->json(['error' => $urgentSaleError], 422);
        }

        $paymentIntent = null;

        try {
            $hasUrgentSale = $cart->items->contains(fn (CartItem $i) => $i->purchasable instanceof UrgentSale);
            $hasEquipmentRental = $cart->items->contains(fn (CartItem $i) => $i->purchasable instanceof EquipmentRentalRequest);

            // Pour escrow (plateforme PI), essayer d'abord sur la plateforme
            // Les ventes urgentes et locations d'équipement utilisent l'escrow
            $stripeAccountId = null;

            // Toujours essayer d'abord sur la plateforme (escrow)
            try {
                $paymentIntent = $this->stripe->retrievePaymentIntent($validated['payment_intent_id'], null);
            } catch (\Throwable $e) {
                // fallback vers le compte connecté
                $paymentIntent = null;
            }

            if (!$paymentIntent) {
                // Connect direct charges: le PaymentIntent peut être sur le compte connecté
                $paymentInfo = $this->getCartPaymentInfo($cart);
                $stripeAccountId = $paymentInfo['stripe_account_id'] ?? null;
                $paymentIntent = $this->stripe->retrievePaymentIntent($validated['payment_intent_id'], $stripeAccountId);
            }

            if ($paymentIntent->status !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'status' => $paymentIntent->status,
                    'message' => 'Paiement en cours de traitement. Veuillez patienter.',
                ]);
            }

            $metadata = (array) ($paymentIntent->metadata?->toArray() ?? []);
            if (!isset($metadata['user_id'], $metadata['cart_id'], $metadata['payment_type'], $metadata['cart_fingerprint'])) {
                return response()->json(['error' => 'Métadonnées Stripe invalides pour ce panier.'], 422);
            }
            if ((int) $metadata['user_id'] !== (int) Auth::id()) {
                return response()->json(['error' => 'Paiement non autorisé (utilisateur).'], 422);
            }
            if ((int) $metadata['cart_id'] !== (int) $cart->id) {
                return response()->json(['error' => 'Paiement non autorisé (panier).'], 422);
            }
            if ((string) $metadata['payment_type'] !== (string) $validated['payment_type']) {
                return response()->json(['error' => 'Type de paiement invalide.'], 422);
            }
            if ((string) $metadata['cart_fingerprint'] !== (string) $this->cartFingerprint($cart)) {
                return response()->json(['error' => 'Le contenu du panier a changé. Veuillez recréer le paiement.'], 409);
            }

            $expectedBase = $validated['payment_type'] === 'deposit'
                ? (float) $cart->items->sum(fn (CartItem $i) => (float) $i->line_deposit)
                : (float) $cart->items->sum(fn (CartItem $i) => (float) $i->line_total);

            // SÉCURITÉ M1: Re-valider les prix en LIVE au moment de la confirmation
            // pour détecter toute modification entre createIntent et confirm (TOCTOU)
            foreach ($cart->items as $item) {
                if ($item->purchasable) {
                    $freshPricing = $this->pricing->pricingFor($item->purchasable, $item->quantity);
                    $storedTotal = (float) $item->line_total;
                    $freshTotal = (float) ($freshPricing['line_total'] ?? $storedTotal);
                    
                    // Tolérance de 0.01€ pour les arrondis
                    if (abs($storedTotal - $freshTotal) > 0.01) {
                        \Illuminate\Support\Facades\Log::warning('Cart TOCTOU: price changed between intent and confirm', [
                            'cart_id' => $cart->id,
                            'item_id' => $item->id,
                            'stored_total' => $storedTotal,
                            'fresh_total' => $freshTotal,
                            'purchasable_type' => get_class($item->purchasable),
                        ]);
                        return response()->json([
                            'error' => 'Le prix d\'un article a changé depuis la création du paiement. Veuillez actualiser votre panier.',
                        ], 409);
                    }
                }
            }

            $stockError = $this->firstUrgentSaleStockError($cart);
            if ($stockError) {
                throw new \RuntimeException($stockError);
            }

            $paymentInfo = $this->getCartPaymentInfo($cart);
            $prestataireId = null;
            $prestataires = (array) ($paymentInfo['prestataires'] ?? []);
            if (count($prestataires) === 1) {
                $prestataireId = (int) array_key_first($prestataires);
            }
            $prestataire = $prestataireId ? (\App\Models\Prestataire::find($prestataireId)) : null;

            $clientFeeTotal = 0.0;
            foreach ($cart->items as $item) {
                $lineBase = $validated['payment_type'] === 'deposit'
                    ? (float) $item->line_deposit
                    : (float) $item->line_total;

                $type = 'service';
                if ($item->purchasable instanceof UrgentSale) {
                    $type = 'urgent_sale';
                } elseif ($item->purchasable instanceof EquipmentRentalRequest) {
                    $type = 'rental';
                } elseif ($item->purchasable instanceof Booking) {
                    $type = 'service';
                }

                $clientFeeTotal += \App\Services\CommissionService::feeAmount($lineBase, $type, 'client', Auth::user(), $prestataire);
            }
            $clientFeeTotal = round($clientFeeTotal, 2);
            $expectedAmount = round((float) $expectedBase + (float) $clientFeeTotal, 2);

            $receivedCents = (int) ($paymentIntent->amount_received ?? $paymentIntent->amount ?? 0);
            $expectedCents = (int) round($expectedAmount * 100);
            if ($expectedCents <= 0 || $receivedCents !== $expectedCents) {
                return response()->json(['error' => 'Montant du paiement invalide.'], 422);
            }

            $txType = $validated['payment_type'] === 'deposit' ? 'deposit' : 'payment';

            // Compute totals for escrow fee allocation (platform PI)
            $totalCharged = ((int) ($paymentIntent->amount_received ?? $paymentIntent->amount ?? 0)) / 100;
            $stripeFeeTotal = (float) ($metadata['stripe_fee_total'] ?? 0);

            /** @var PaymentTransaction $transaction */
            $transaction = DB::transaction(function () use ($paymentIntent, $cart, $validated, $txType, $totalCharged, $stripeFeeTotal) {
                // Lock le cart EN PREMIER pour éviter le double checkout (race condition)
                $cartLocked = \App\Models\Cart::where('id', $cart->id)->lockForUpdate()->first();
                if ($cartLocked && $cartLocked->status === 'checked_out') {
                    $existing = PaymentTransaction::where('stripe_payment_intent_id', $paymentIntent->id)->first();
                    if ($existing) {
                        return $existing;
                    }
                    throw new \RuntimeException('Ce panier a déjà été validé.');
                }

                $existing = PaymentTransaction::where('stripe_payment_intent_id', $paymentIntent->id)->lockForUpdate()->first();
                $transaction = $existing ?: $this->stripe->recordPayment($paymentIntent, null, null, $txType);

                $clientId = Auth::user()?->client?->id;

                // Allocate + apply to items
                foreach ($cart->items as $item) {
                    $lineAmount = $validated['payment_type'] === 'deposit'
                        ? (float) $item->line_deposit
                        : (float) $item->line_total;

                    if ($lineAmount <= 0) {
                        continue;
                    }

                    PaymentAllocation::updateOrCreate(
                        [
                            'payment_transaction_id' => $transaction->id,
                            'payable_type' => $item->purchasable_type,
                            'payable_id' => $item->purchasable_id,
                            'type' => $txType,
                        ],
                        [
                            'amount' => $lineAmount,
                            'currency' => $item->currency ?? 'eur',
                        ]
                    );

                    $p = $item->purchasable;

                    if ($p instanceof Booking) {
                        if (!$clientId || (int) $p->client_id !== (int) $clientId) {
                            throw new \Exception('Réservation non autorisée');
                        }
                        if ($validated['payment_type'] === 'deposit' && (float) ($p->deposit_amount ?? 0) > 0) {
                            $p->update(['payment_status' => 'deposit_paid']);
                        } else {
                            $p->update(['payment_status' => 'paid']);
                        }
                        if (method_exists($p, 'isPending') && $p->isPending()) {
                            $p->confirm();
                        }
                    } elseif ($p instanceof EquipmentRentalRequest) {
                        if (!$clientId || (int) $p->client_id !== (int) $clientId) {
                            throw new \Exception('Demande de location non autorisée');
                        }
                        // Mark request confirmed
                        if (isset($p->status) && in_array($p->status, ['pending', 'accepted'], true)) {
                            $p->status = 'confirmed';
                            if (isset($p->confirmed_at)) {
                                $p->confirmed_at = now();
                            }
                            $p->save();
                        }

                        // Ensure there is an EquipmentRental row and update payment_status
                        $p->load('rental');
                        $rental = $p->rental;
                        if (!$rental) {
                            $rental = EquipmentRental::create([
                                'rental_number' => 'LOC-' . strtoupper(uniqid()),
                                'rental_request_id' => $p->id,
                                'equipment_id' => $p->equipment_id,
                                'client_id' => $p->client_id,
                                'prestataire_id' => $p->prestataire_id,
                                'start_date' => $p->start_date,
                                'end_date' => $p->end_date,
                                'planned_duration_days' => $p->duration_days ?? 1,
                                'unit_price' => $p->unit_price ?? 0,
                                'base_amount' => $p->total_amount ?? 0,
                                'security_deposit' => $p->security_deposit ?? 0,
                                'total_amount' => $p->total_amount ?? 0,
                                'final_amount' => $p->final_amount ?? $p->total_amount ?? 0,
                                'pickup_address' => $p->pickup_address,
                                'status' => 'confirmed',
                                'payment_status' => 'pending',
                            ]);
                        }

                        $deposit = (float) ($p->security_deposit ?? 0);
                        $total = (float) ($p->final_amount ?? $p->total_amount ?? 0);
                        $hasBalance = $deposit > 0 && $total > 0 && $deposit < $total;
                        if ($validated['payment_type'] === 'deposit' && $hasBalance) {
                            $rental->payment_status = 'partial';
                        } else {
                            $rental->payment_status = 'paid';
                        }
                        $rental->save();
                    } elseif ($p instanceof UrgentSale) {
                        // Idempotency: if purchase already exists for this tx+user+product, skip stock decrement
                        $alreadyPurchased = UrgentSalePurchase::where('payment_transaction_id', $transaction->id)
                            ->where('buyer_user_id', Auth::id())
                            ->where('urgent_sale_id', $p->id)
                            ->exists();

                        if ($alreadyPurchased) {
                            continue;
                        }

                        // Lock + decrement stock and create purchase record
                        $locked = UrgentSale::whereKey($p->id)->lockForUpdate()->first();
                        if (!$locked) {
                            throw new \Exception('Produit indisponible');
                        }

                        if ((int) $locked->quantity < (int) $item->quantity) {
                            throw new \Exception('Stock insuffisant pour un produit du panier');
                        }

                        // Décrémenter le stock disponible
                        $locked->quantity = (int) $locked->quantity - (int) $item->quantity;
                        
                        // Incrémenter la quantité vendue pour l'inventaire
                        $locked->sold_quantity = (int) ($locked->sold_quantity ?? 0) + (int) $item->quantity;
                        
                        $locked->save();

                        UrgentSalePurchase::firstOrCreate(
                            [
                                'urgent_sale_id' => $locked->id,
                                'buyer_user_id' => Auth::id(),
                                'payment_transaction_id' => $transaction->id,
                            ],
                            [
                                'quantity' => (int) $item->quantity,
                                'unit_price' => (float) $item->unit_price,
                                'total_amount' => (float) $item->line_total,
                                'currency' => $item->currency ?? 'eur',
                                'status' => 'paid',
                            ]
                        );

                        // We keep a per-purchase escrow, used by /client/escrow and automation.
                        // IMPORTANT: for escrow, we do NOT credit prestataire balance here.
                        // Balance is credited only when escrow releases (or partially releases).
                        $purchase = UrgentSalePurchase::where('payment_transaction_id', $transaction->id)
                            ->where('buyer_user_id', Auth::id())
                            ->where('urgent_sale_id', $locked->id)
                            ->first();

                        if ($purchase) {
                            $lineBase = $validated['payment_type'] === 'deposit'
                                ? (float) $item->line_deposit
                                : (float) $item->line_total;

                            $prestataire = $locked->prestataire ?? null;
                            $clientFeeLine = \App\Services\CommissionService::feeAmount($lineBase, 'urgent_sale', 'client', Auth::user(), $prestataire);
                            $prestataireFeeLine = \App\Services\CommissionService::feeAmount($lineBase, 'urgent_sale', 'prestataire', Auth::user(), $prestataire);
                            $lineCharged = round($lineBase + $clientFeeLine, 2);

                            $stripeFeeAlloc = 0.0;
                            if ($totalCharged > 0 && $stripeFeeTotal > 0) {
                                $stripeFeeAlloc = round(((float) $stripeFeeTotal) * ($lineCharged / (float) $totalCharged), 2);
                            }

                            $commissionMin = (float) (get_setting('commission_minimum', null) ?? config('finance.commission_min', 0));
                            $platformFee = round($clientFeeLine + $prestataireFeeLine + $stripeFeeAlloc, 2);
                            if ($commissionMin > 0) {
                                $platformFee = max($platformFee, $commissionMin);
                            }

                            $clientId = Auth::user()?->client?->id;
                            $prestataireId = (int) ($locked->prestataire_id ?? 0);

                            if ($clientId && $prestataireId > 0) {
                                $escrow = $this->escrowService->createEscrow(
                                    escrowable: $purchase,
                                    clientId: (int) $clientId,
                                    prestataireId: $prestataireId,
                                    amount: $lineCharged,
                                    depositAmount: 0,
                                    stripePaymentIntentId: $paymentIntent->id,
                                    platformFeeOverride: $platformFee,
                                    metadata: [
                                        'cart_id' => (string) $cart->id,
                                        'payment_transaction_id' => (string) $transaction->id,
                                        'urgent_sale_id' => (string) $locked->id,
                                        'purchase_id' => (string) $purchase->id,
                                        'line_base' => (string) round($lineBase, 2),
                                        'client_fee' => (string) round($clientFeeLine, 2),
                                        'prestataire_fee' => (string) round($prestataireFeeLine, 2),
                                        'stripe_fee_alloc' => (string) round($stripeFeeAlloc, 2),
                                    ]
                                );

                                if (!$escrow) {
                                    throw new \RuntimeException('Échec de création escrow pour vente urgente.');
                                }

                                if (isset($escrow->id) && Schema::hasColumn('urgent_sale_purchases', 'escrow_id')) {
                                    DB::table('urgent_sale_purchases')
                                        ->where('id', $purchase->id)
                                        ->update(['escrow_id' => $escrow->id, 'updated_at' => now()]);
                                }
                            }
                        }

                        if ((int) $locked->quantity <= 0 && isset($locked->status)) {
                            $locked->status = 'sold';
                            $locked->save();
                        }
                    }
                }

                $cart->update([
                    'status' => 'checked_out',
                    'checked_out_at' => now(),
                ]);

                return $transaction;
            });

            // Générer les factures pour chaque item du panier
            try {
                foreach ($cart->items as $item) {
                    $p = $item->purchasable;
                    
                    if ($p instanceof Booking) {
                        $this->invoiceService->generateForBooking($p, $transaction);
                    } elseif ($p instanceof EquipmentRentalRequest) {
                        $this->invoiceService->generateForEquipmentRental($p, $transaction);
                    } elseif ($p instanceof UrgentSale) {
                        // Trouver le purchase créé pour cette vente
                        $purchase = UrgentSalePurchase::where('payment_transaction_id', $transaction->id)
                            ->where('urgent_sale_id', $p->id)
                            ->first();
                        if ($purchase) {
                            $this->invoiceService->generateForUrgentSale($purchase, $transaction);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to generate invoices for cart payment: ' . $e->getMessage());
            }

            // Envoyer les notifications au(x) prestataire(s) concernés
            try {
                $paymentInfo = $this->getCartPaymentInfo($cart);
                foreach ($paymentInfo['prestataires'] ?? [] as $prestataireData) {
                    $prestataire = $prestataireData['prestataire'] ?? null;
                    if ($prestataire && $prestataire->user) {
                        $prestataire->user->notify(new \App\Notifications\CartPaymentReceived($cart, $transaction));
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to notify prestataire(s) of cart payment: ' . $e->getMessage());
            }

            // Enregistrer le consentement aux conditions de paiement (RGPD)
            try {
                $this->consentService->recordConsentForCart(
                    Auth::id(),
                    $cart->items,
                    $request,
                    $request->input('terms_version', 'v1.0')
                );
            } catch (\Exception $e) {
                Log::warning('Failed to record payment consent: ' . $e->getMessage());
            }

            Log::info('Cart payment confirmed', [
                'cart_id' => $cart->id,
                'transaction_id' => $transaction->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->id,
            ]);
        } catch (\Exception $e) {
            $piId = $validated['payment_intent_id'] ?? null;
            $hasRecordedTx = $piId
                ? PaymentTransaction::where('stripe_payment_intent_id', $piId)->exists()
                : false;

            // If Stripe already captured money but DB confirmation failed, auto-refund to protect client.
            if (
                !$hasRecordedTx
                && $paymentIntent
                && (string) ($paymentIntent->status ?? '') === 'succeeded'
                && !empty($paymentIntent->id)
            ) {
                try {
                    $this->stripe->refundPaymentIntent(
                        (string) $paymentIntent->id,
                        null,
                        'Auto-refund: cart confirmation failed after capture'
                    );
                    Log::critical('Cart auto-refund triggered after confirmation failure', [
                        'cart_id' => $cart->id ?? null,
                        'payment_intent_id' => $paymentIntent->id,
                        'user_id' => Auth::id(),
                    ]);
                } catch (\Throwable $refundError) {
                    Log::critical('Cart auto-refund failed', [
                        'cart_id' => $cart->id ?? null,
                        'payment_intent_id' => $paymentIntent->id ?? null,
                        'refund_error' => $refundError->getMessage(),
                    ]);
                }
            }

            Log::error('Cart payment confirmation failed', [
                'cart_id' => $cart->id ?? null,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return response()->json(['error' => 'Erreur lors de la confirmation du paiement.'], 422);
        }
    }
}
