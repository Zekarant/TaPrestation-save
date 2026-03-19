<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\FoodOrder;
use App\Models\PaymentTransaction;
use App\Models\Invoice;
use App\Services\StripePaymentService;
use App\Services\EscrowService;
use App\Services\Finance\AccountingService;
use App\Notifications\NewFoodOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FoodPaymentController extends Controller
{
    protected $stripeService;
    protected $escrowService;

    public function __construct(StripePaymentService $stripeService, EscrowService $escrowService)
    {
        $this->middleware('auth');
        $this->stripeService = $stripeService;
        $this->escrowService = $escrowService;
    }

    /**
     * Page de paiement pour une commande food
     */
    public function showPaymentForm(FoodOrder $foodOrder)
    {
        $this->authorizeOrder($foodOrder);

        if (function_exists('food_online_payment_enabled') && !food_online_payment_enabled()) {
            return redirect()->route('food.orders.show', $foodOrder)
                ->with('info', 'Le paiement en ligne des commandes food est désactivé.');
        }

        if (function_exists('cash_only_mode') && cash_only_mode()) {
            return redirect()->route('food.orders.show', $foodOrder)
                ->with('info', 'Le paiement en ligne est désactivé. Cette commande est en espèces uniquement.');
        }

        // Vérifier que la commande peut être payée
        if ($foodOrder->payment_status === FoodOrder::PAYMENT_PAID) {
            return redirect()->route('food.orders.show', $foodOrder)
                ->with('info', 'Cette commande a déjà été payée.');
        }

        if (in_array($foodOrder->status, ['cancelled', 'rejected'])) {
            return redirect()->route('food.orders.show', $foodOrder)
                ->with('error', 'Cette commande ne peut pas être payée.');
        }

        // Charger les relations nécessaires pour la vue
        $foodOrder->load(['prestataire', 'items.foodProduct']);

        // Récupérer la politique de paiement
        $paymentPolicy = $foodOrder->getPaymentPolicy();
        $amountDue = $foodOrder->calculateAmountDueNow();

        $stripeKey = config('services.stripe.key');

        return view('client.food-orders.payment', [
            'foodOrder' => $foodOrder,
            'stripeKey' => $stripeKey,
            'paymentPolicy' => $paymentPolicy,
            'amountDue' => $amountDue,
        ]);
    }

    /**
     * Créer un Payment Intent Stripe (avec support escrow/acompte)
     */
    public function createPaymentIntent(FoodOrder $foodOrder)
    {
        $this->authorizeOrder($foodOrder);

        try {
            if (function_exists('food_online_payment_enabled') && !food_online_payment_enabled()) {
                return response()->json([
                    'error' => 'Le paiement en ligne des commandes food est désactivé.',
                    'payment_policy' => 'cash',
                ], 422);
            }

            if (function_exists('cash_only_mode') && cash_only_mode()) {
                return response()->json([
                    'error' => 'Le paiement en ligne est désactivé. Cette commande est en espèces uniquement.',
                    'payment_policy' => 'cash',
                ], 422);
            }

            if ($foodOrder->payment_status === FoodOrder::PAYMENT_PAID) {
                return response()->json(['error' => 'Cette commande a déjà été payée.'], 422);
            }

            // Calculer le montant à payer maintenant (acompte ou total)
            $paymentPolicy = $foodOrder->getPaymentPolicy();
            $policyType = (string) ($paymentPolicy['type'] ?? 'cash');
            $amountDueInfo = $foodOrder->calculateAmountDueNow();
            $amountDue = (float) ($amountDueInfo['amount'] ?? 0);
            
            // Si espèces uniquement, pas de paiement en ligne
            if ($policyType === 'cash') {
                return response()->json([
                    'error' => 'Cette commande est en paiement espèces uniquement.',
                    'payment_policy' => 'cash',
                ], 422);
            }

            $description = "Commande Food #{$foodOrder->order_number}";
            if ($policyType === 'deposit') {
                $description .= " (Acompte)";
            }
            
            $metadata = [
                'food_order_id' => $foodOrder->id,
                'order_number' => $foodOrder->order_number,
                'user_id' => Auth::id(),
                'client_id' => Auth::id(),
                'prestataire_id' => $foodOrder->prestataire_id,
                'payment_policy' => $policyType,
                'amount_due' => $amountDue,
                'total_order' => $foodOrder->total,
            ];

            // Base amount for commission calculation (deposit/full): use the amount actually charged now.
            // This matches how cart deposits are handled.
            $metadata['base_amount'] = (string) round((float) $amountDue, 2);

            // Paiement en mode ESCROW pour le food
            // L'argent reste sur le compte plateforme jusqu'à confirmation de livraison
            // puis est transféré au prestataire via Stripe Transfer
            $foodOrder->loadMissing('prestataire');
            $connectedAccountId = $foodOrder->prestataire?->stripe_account_id;

            // Déterminer le mode de capture selon le type de livraison
            // - pickup (emporter) : capture automatique immédiate
            // - delivery (livraison externe) : capture manuelle (après acceptation vendeur + livreur)
            $isExternalDelivery = $foodOrder->delivery_type === 'delivery';
            $captureMethod = $isExternalDelivery ? 'manual' : 'automatic';

            // Utiliser escrow pour le food (blocage des fonds)
            $paymentIntent = $this->stripeService->createEscrowPaymentIntent(
                Auth::user(),
                (float) $amountDue,
                $description,
                array_merge($metadata, [
                    'delivery_type' => $foodOrder->delivery_type,
                    'capture_method' => $captureMethod,
                ]),
                $connectedAccountId, // Stocké en metadata pour le transfer ultérieur
                [
                    'capture_method' => $captureMethod,
                    // Laisser Stripe proposer les moyens de paiement disponibles (Apple Pay/Google Pay, etc.)
                    // selon le device et la configuration du compte.
                    'automatic_payment_methods' => ['enabled' => true],
                    'receipt_email' => Auth::user()->email,
                ]
            );

            // Sauvegarder le payment_intent_id sur la commande
            $foodOrder->update(['payment_intent_id' => $paymentIntent->id]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id,
                'amount' => $amountDue,
                'total_order' => $foodOrder->total,
                'payment_policy' => $policyType,
                'is_deposit' => $policyType === 'deposit',
                'remaining_amount' => $foodOrder->total - $amountDue,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur création Payment Intent Food', [
                'food_order_id' => $foodOrder->id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Impossible de préparer le paiement pour le moment.',
            ], 500);
        }
    }

    /**
     * Confirmer le paiement (avec escrow/blocage)
     */
    public function confirmPayment(Request $request, FoodOrder $foodOrder)
    {
        $this->authorizeOrder($foodOrder);

        if (function_exists('food_online_payment_enabled') && !food_online_payment_enabled()) {
            return response()->json(['error' => 'Le paiement en ligne des commandes food est désactivé.'], 422);
        }

        if (function_exists('cash_only_mode') && cash_only_mode()) {
            return response()->json(['error' => 'Le paiement en ligne est désactivé pour le moment.'], 422);
        }

        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
            'payment_method' => 'nullable|string',
        ]);

        try {
            if (
                in_array((string) $foodOrder->payment_status, [FoodOrder::PAYMENT_PAID, 'partial', FoodOrder::PAYMENT_PENDING_CAPTURE], true)
                && !empty($foodOrder->payment_intent_id)
                && $foodOrder->payment_intent_id !== $validated['payment_intent_id']
            ) {
                return response()->json(['error' => 'Cette commande est déjà payée avec un autre paiement Stripe.'], 422);
            }

            $existingTx = PaymentTransaction::where('stripe_payment_intent_id', $validated['payment_intent_id'])->first();
            if ($existingTx) {
                if ((int) ($existingTx->food_order_id ?? 0) !== (int) $foodOrder->id) {
                    return response()->json(['error' => 'Ce paiement est déjà associé à une autre commande.'], 422);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Paiement déjà confirmé.',
                    'redirect' => route('food.orders.show', $foodOrder),
                    'escrow' => true,
                    'amount_held' => (float) ($foodOrder->amount_held ?? $existingTx->amount),
                ]);
            }

            // Vérifier le statut du Payment Intent
            // Pour les escrow, le PI est sur le compte plateforme (null)
            $foodOrder->loadMissing('prestataire');
            $connectedAccountId = $foodOrder->prestataire?->stripe_account_id;
            $orderPaymentIntentId = (string) ($foodOrder->payment_intent_id ?? '');
            if ($orderPaymentIntentId !== '' && $orderPaymentIntentId !== $validated['payment_intent_id']) {
                return response()->json(['error' => 'PaymentIntent invalide pour cette commande.'], 422);
            }

            // Essayer d'abord sur le compte plateforme (escrow), puis sur le compte connecté
            try {
                $paymentIntent = $this->stripeService->retrievePaymentIntent($validated['payment_intent_id'], null);
            } catch (\Exception $e) {
                // Fallback sur le compte connecté si non trouvé sur la plateforme
                if ($connectedAccountId) {
                    $paymentIntent = $this->stripeService->retrievePaymentIntent($validated['payment_intent_id'], $connectedAccountId);
                } else {
                    throw $e;
                }
            }

            $metadata = (array) ($paymentIntent->metadata?->toArray() ?? []);
            if (!isset($metadata['user_id'], $metadata['food_order_id'])) {
                return response()->json(['error' => 'Métadonnées Stripe incomplètes pour cette commande.'], 422);
            }
            if ((int) $metadata['user_id'] !== (int) Auth::id()) {
                return response()->json(['error' => 'Ce paiement ne correspond pas à votre compte.'], 422);
            }
            if ((int) $metadata['food_order_id'] !== (int) $foodOrder->id) {
                return response()->json(['error' => 'Ce paiement ne correspond pas à cette commande.'], 422);
            }

            $amountDueInfo = $foodOrder->calculateAmountDueNow();
            $expectedAmount = round((float) ($amountDueInfo['amount'] ?? 0), 2);
            $expectedCents = (int) round($expectedAmount * 100);
            $piCents = (int) ($paymentIntent->amount ?? 0);
            if ($expectedCents <= 0 || $piCents !== $expectedCents) {
                return response()->json(['error' => 'Montant du paiement invalide pour cette commande.'], 422);
            }

            // Pour les livraisons externes avec capture manuelle, le statut sera 'requires_capture'
            // Pour les autres, ce sera 'succeeded'
            $isExternalDelivery = $foodOrder->delivery_type === 'delivery';
            $validStatuses = $isExternalDelivery ? ['succeeded', 'requires_capture'] : ['succeeded'];
            
            if (!in_array($paymentIntent->status, $validStatuses)) {
                return response()->json([
                    'error' => 'Le paiement n\'a pas été confirmé.',
                    'status' => $paymentIntent->status,
                ], 422);
            }

            $amountPaid = $paymentIntent->amount / 100; // Convertir de centimes
            $paymentPolicy = $foodOrder->getPaymentPolicy();
            $policyType = (string) ($paymentPolicy['type'] ?? 'cash');
            $result = DB::transaction(function () use ($foodOrder, $validated, $paymentIntent, $amountPaid, $policyType) {
                // Déterminer le statut de paiement selon le mode
                // - requires_capture = autorisé mais pas prélevé (pending_capture)
                // - succeeded = prélevé (paid ou partial)
                if ($paymentIntent->status === 'requires_capture') {
                    $paymentStatus = 'pending_capture'; // Autorisé, en attente de capture
                    $escrowStatus = FoodOrder::ESCROW_PENDING; // En attente
                } else {
                    $paymentStatus = FoodOrder::PAYMENT_PAID;
                    if ($policyType === 'deposit' && $amountPaid < $foodOrder->total) {
                        $paymentStatus = 'partial';
                    }
                    $escrowStatus = FoodOrder::ESCROW_HELD;
                }

                // Mettre à jour la commande
                $foodOrder->update([
                    'payment_status' => $paymentStatus,
                    'payment_method' => $validated['payment_method'] ?? 'card',
                    'payment_intent_id' => $validated['payment_intent_id'],
                    'paid_at' => now(),
                    // Escrow : statut selon le mode de capture
                    'escrow_status' => $escrowStatus,
                    'amount_held' => $amountPaid,
                    'held_at' => now(),
                ]);

                // Créer la transaction (idempotent par PI)
                // Status: 'held' si escrow actif (fonds bloqués), 'pending' si autorisation
                $transactionStatus = ($escrowStatus === FoodOrder::ESCROW_HELD) ? 'held' : 'pending';
                $transaction = PaymentTransaction::systemCreate([
                    'user_id' => Auth::id(),
                    'food_order_id' => $foodOrder->id,
                    'amount' => $amountPaid,
                    'type' => 'payment',
                    'currency' => 'eur',
                    'status' => $transactionStatus,
                    'provider' => 'stripe',
                    'transaction_id' => $validated['payment_intent_id'],
                    'stripe_payment_intent_id' => $validated['payment_intent_id'],
                    'payment_method' => $validated['payment_method'] ?? 'card',
                    'paid_at' => now(),
                    'description' => $policyType === 'deposit'
                        ? "Acompte commande food #{$foodOrder->order_number}"
                        : "Paiement commande food #{$foodOrder->order_number}",
                    'metadata' => [
                        'food_order_id' => $foodOrder->id,
                        'order_number' => $foodOrder->order_number,
                        'prestataire_id' => $foodOrder->prestataire_id,
                        'payment_policy' => $policyType,
                        'escrow' => true,
                    ],
                ]);

                // Comptabilisation (mais fonds pas encore libérés)
                try {
                    $accounting = new AccountingService();
                    $accounting->recordClientPayment([
                        'user_id' => Auth::id(),
                        'prestataire_id' => $foodOrder->prestataire_id,
                        'amount' => $amountPaid,
                        'type' => 'food', // Type for CommissionService rate lookup
                        'service_fee' => $foodOrder->service_fee,
                        'food_order_id' => $foodOrder->id,
                        'transaction_id' => $transaction->id,
                        'escrow' => true, // Marquer comme bloqué
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erreur comptabilisation paiement food: ' . $e->getMessage());
                }

                // Créer une transaction escrow pour le suivi (ne pas ignorer silencieusement les erreurs)
                $existingEscrow = DB::table('escrow_transactions')
                    ->where('stripe_payment_intent_id', $validated['payment_intent_id'])
                    ->where('escrowable_type', FoodOrder::class)
                    ->where('escrowable_id', $foodOrder->id)
                    ->lockForUpdate()
                    ->first();

                if (!$existingEscrow) {
                    $escrow = $this->escrowService->createEscrow(
                        $foodOrder,
                        Auth::id(),
                        $foodOrder->prestataire_id,
                        $amountPaid,
                        0, // pas de caution pour food
                        $validated['payment_intent_id'],
                        null, // commission calculée automatiquement selon type
                        [
                            'type' => 'food_order',
                            'order_number' => $foodOrder->order_number,
                            'payment_policy' => $policyType,
                        ],
                        'food' // Type pour utiliser la commission food du dashboard admin
                    );

                    if (!$escrow) {
                        throw new \RuntimeException('Erreur création escrow food.');
                    }
                }

                return [
                    'amount_paid' => $amountPaid,
                    'policy_type' => $policyType,
                ];
            });

            // Notifier le prestataire maintenant que le paiement est confirmé
            try {
                $foodOrder->prestataire->user->notify(new NewFoodOrder($foodOrder));
            } catch (\Exception $e) {
                Log::error('Erreur notification NewFoodOrder après paiement: ' . $e->getMessage());
            }

            // Créer une facture pour le client
            try {
                $this->createInvoiceForFoodOrder(
                    $foodOrder,
                    (float) $result['amount_paid'],
                    $validated['payment_intent_id'],
                    (string) $result['policy_type']
                );
            } catch (\Exception $e) {
                Log::error('Erreur création facture food: ' . $e->getMessage());
            }

            $message = ((string) $result['policy_type']) === 'deposit'
                ? 'Acompte payé ! Le solde sera à régler à la réception.'
                : 'Paiement confirmé ! Les fonds seront libérés après validation.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('food.orders.show', $foodOrder),
                'escrow' => true,
                'amount_held' => (float) $result['amount_paid'],
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur confirmation paiement Food: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la confirmation du paiement.'], 422);
        }
    }

    /**
     * Paiement en espèces (marqué comme en attente côté prestataire)
     */
    public function payCash(FoodOrder $foodOrder)
    {
        $this->authorizeOrder($foodOrder);

        if ($foodOrder->payment_status === FoodOrder::PAYMENT_PAID) {
            return back()->with('info', 'Cette commande a déjà été payée.');
        }

        // SÉCURITÉ: Vérifier que la politique de paiement autorise les espèces
        $paymentPolicy = $foodOrder->getPaymentPolicy();
        if ($paymentPolicy['type'] === 'full_prepay') {
            Log::warning("Tentative de paiement cash bloquée pour commande #{$foodOrder->id} (full_prepay)", [
                'order_id' => $foodOrder->id,
                'client_id' => $foodOrder->client_id,
                'policy' => $paymentPolicy,
            ]);
            return back()->with('error', 'Cette commande nécessite un paiement intégral en ligne. Le paiement en espèces n\'est pas autorisé.');
        }
        
        if ($paymentPolicy['type'] === 'deposit') {
            Log::warning("Tentative de paiement cash bloquée pour commande #{$foodOrder->id} (deposit requis)", [
                'order_id' => $foodOrder->id,
                'client_id' => $foodOrder->client_id,
                'policy' => $paymentPolicy,
            ]);
            return back()->with('error', "Cette commande nécessite un acompte de {$paymentPolicy['percent']}% en ligne. Veuillez d'abord payer l'acompte par carte.");
        }

        // Marquer comme paiement en espèces attendu
        $foodOrder->update([
            'payment_method' => 'cash',
        ]);

        return redirect()->route('food.orders.show', $foodOrder)
            ->with('success', 'Mode de paiement enregistré. Vous paierez en espèces à la réception.');
    }

    /**
     * Historique des paiements food du client
     */
    public function paymentHistory()
    {
        $transactions = PaymentTransaction::where('user_id', Auth::id())
            ->whereNotNull('food_order_id')
            ->with('foodOrder')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total_paid' => PaymentTransaction::where('user_id', Auth::id())
                ->whereNotNull('food_order_id')
                ->whereIn('status', ['paid', 'held', 'released']) // Include all completed payment states
                ->sum('amount'),
            'orders_count' => FoodOrder::where('client_id', Auth::id())
                ->where('payment_status', FoodOrder::PAYMENT_PAID)
                ->count(),
        ];

        return view('client.food-orders.payment-history', compact('transactions', 'stats'));
    }

    /**
     * Vérifier que la commande appartient au client connecté
     */
    protected function authorizeOrder(FoodOrder $foodOrder): void
    {
        if ($foodOrder->client_id !== Auth::id()) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à cette commande.');
        }
    }

    /**
     * Créer une facture pour une commande food
     */
    protected function createInvoiceForFoodOrder(FoodOrder $foodOrder, float $amountPaid, string $paymentIntentId, string $policyType): Invoice
    {
        $user = Auth::user();
        $prestataire = $foodOrder->prestataire;
        $foodOrder->load('items.foodProduct');

        // Préparer les lignes de facture
        $lineItems = [];
        foreach ($foodOrder->items as $item) {
            $lineItems[] = [
                'description' => $item->product_name ?? $item->foodProduct->name ?? 'Article',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total_price,
            ];
        }

        // Ajouter les frais de livraison si présents
        if ($foodOrder->delivery_fee > 0) {
            $lineItems[] = [
                'description' => 'Frais de livraison',
                'quantity' => 1,
                'unit_price' => $foodOrder->delivery_fee,
                'total' => $foodOrder->delivery_fee,
            ];
        }

        // Ajouter les frais de service si présents
        if ($foodOrder->service_fee > 0) {
            $lineItems[] = [
                'description' => 'Frais de service',
                'quantity' => 1,
                'unit_price' => $foodOrder->service_fee,
                'total' => $foodOrder->service_fee,
            ];
        }

        // Déterminer la description
        $description = "Commande #{$foodOrder->order_number}";
        if ($policyType === 'deposit') {
            $description .= " (Acompte)";
        }
        $description .= " - " . ($prestataire->company_name ?? $prestataire->business_name ?? $prestataire->user->name);

        // Créer la facture
        $invoice = Invoice::systemCreate([
            'type' => 'client',
            'user_id' => $user->id,
            'prestataire_id' => $prestataire->id,
            'invoiceable_type' => FoodOrder::class,
            'invoiceable_id' => $foodOrder->id,
            'billing_name' => $user->name,
            'billing_email' => $user->email,
            'billing_phone' => $user->phone ?? null,
            'seller_name' => $prestataire->company_name ?? $prestataire->business_name ?? $prestataire->user->name,
            'seller_address' => $prestataire->address . ', ' . $prestataire->postal_code . ' ' . $prestataire->city,
            'seller_siret' => $prestataire->siret ?? null,
            'subtotal' => $foodOrder->subtotal,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => $amountPaid,
            'currency' => 'EUR',
            'status' => 'paid',
            'issued_at' => now(),
            'paid_at' => now(),
            'payment_method' => 'card',
            'payment_reference' => $paymentIntentId,
            'description' => $description,
            'line_items' => $lineItems,
            'notes' => $policyType === 'deposit' 
                ? "Acompte de {$amountPaid}€ sur un total de {$foodOrder->total}€. Solde à régler: " . ($foodOrder->total - $amountPaid) . "€"
                : null,
        ]);

        Log::info("Facture {$invoice->invoice_number} créée pour commande food #{$foodOrder->order_number}");

        return $invoice;
    }
}
