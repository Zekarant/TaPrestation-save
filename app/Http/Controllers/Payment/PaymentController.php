<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\CreatePaymentIntentRequest;
use App\Http\Requests\Payment\ConfirmPaymentRequest;
use App\Http\Requests\Payment\AdminRefundRequest;
use App\Http\Requests\Payment\AdminResolveDisputeRequest;
use App\Http\Requests\Payment\AdminResolveRefundRequest;
use App\Http\Requests\Payment\AdminEscrowReturnEquipmentRequest;
use App\Http\Requests\Payment\AdminEscrowRefundRequest;
use App\Models\Booking;
use App\Models\EquipmentRentalRequest;
use App\Models\FoodOrder;
use App\Models\PaymentRefund;
use App\Models\PaymentTransaction;
use App\Support\PaymentMetadataNormalizer;
use App\Services\StripePaymentService;
use App\Services\EscrowService;
use App\Services\InvoiceGenerationService;
use App\Services\EquipmentRentalPaymentSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Support\TableExistenceCache;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    protected $stripeService;
    protected $escrowService;
    protected $invoiceService;
    protected $equipmentRentalPaymentSyncService;

    private function paymentIntentMetadata($paymentIntent): array
    {
        $metadata = $paymentIntent->metadata ?? [];
        if (is_object($metadata) && method_exists($metadata, 'toArray')) {
            return (array) $metadata->toArray();
        }

        return (array) $metadata;
    }

    private function expectedAmountForPaymentType(Booking $booking, string $paymentType): float
    {
        return match ($paymentType) {
            'deposit' => (float) $booking->deposit_amount,
            'balance' => round((float) $booking->total_price - (float) $booking->deposit_amount, 2),
            default => (float) $booking->total_price,
        };
    }

    private function assertPaymentIntentMatchesBooking($paymentIntent, Booking $booking, string $paymentType): void
    {
        $metadata = $this->paymentIntentMetadata($paymentIntent);

        if (!isset($metadata['user_id'], $metadata['booking_id'], $metadata['payment_type'])) {
            throw new \RuntimeException('Métadonnées Stripe incomplètes pour ce paiement.');
        }

        if ((int) $metadata['user_id'] !== (int) auth()->id()) {
            throw new \RuntimeException('Ce paiement ne correspond pas à votre compte.');
        }

        if ((int) $metadata['booking_id'] !== (int) $booking->id) {
            throw new \RuntimeException('Ce paiement ne correspond pas à cette réservation.');
        }

        if ((string) $metadata['payment_type'] !== $paymentType) {
            throw new \RuntimeException('Type de paiement Stripe invalide.');
        }

        $expectedCents = (int) round($this->expectedAmountForPaymentType($booking, $paymentType) * 100);
        $piCents = (int) ($paymentIntent->amount ?? 0);
        if ($expectedCents <= 0 || $piCents !== $expectedCents) {
            throw new \RuntimeException('Montant Stripe invalide pour cette réservation.');
        }
    }

    private function getBookingPaymentRequirement(Booking $booking): string
    {
        return function_exists('normalize_payment_requirement_for_mode')
            ? normalize_payment_requirement_for_mode($booking->service?->payment_requirement ?? 'none')
            : ($booking->service?->payment_requirement ?? 'none');
    }

    private function expectedBookingDepositAmount(Booking $booking): float
    {
        $requirement = $this->getBookingPaymentRequirement($booking);
        if ($requirement !== 'deposit') {
            return 0.0;
        }

        $pct = (float) ($booking->service?->deposit_percentage ?? 0);
        $total = (float) ($booking->total_price ?? 0);

        if ($pct <= 0 || $total <= 0) {
            return 0.0;
        }

        return round(($total * $pct) / 100, 2);
    }

    private function assertPaymentTypeAllowed(Booking $booking, string $paymentType): void
    {
        $requirement = $this->getBookingPaymentRequirement($booking);

        if (in_array($booking->payment_status, ['paid', 'refunded'], true)) {
            throw new \Exception('Cette réservation est déjà réglée.');
        }

        if ($paymentType === 'deposit') {
            if ($booking->payment_status !== 'pending') {
                throw new \Exception('Acompte non disponible pour ce statut de paiement.');
            }
            if ($requirement !== 'deposit') {
                throw new \Exception("Le prestataire n'a pas configuré de paiement par acompte pour cette annonce.");
            }
            if ((float) $booking->deposit_amount <= 0) {
                throw new \Exception("Aucun acompte n'est requis pour cette réservation.");
            }
        }

        if ($paymentType === 'balance') {
            if ($booking->payment_status !== 'deposit_paid') {
                throw new \Exception('Le solde est disponible uniquement après paiement de l\'acompte.');
            }
            $remaining = (float) $booking->total_price - (float) $booking->deposit_amount;
            if ($remaining <= 0) {
                throw new \Exception('Aucun solde à payer.');
            }
        }

        if ($paymentType === 'full') {
            if ($booking->payment_status !== 'pending') {
                throw new \Exception('Le paiement total est disponible uniquement avant tout paiement.');
            }
            // If requirement is full, full is mandatory; otherwise full is still allowed.
        }
    }

    private function mapPaymentTypeToTransactionType(string $paymentType): string
    {
        return match ($paymentType) {
            'full' => 'payment',
            'deposit' => 'deposit',
            'balance' => 'balance',
            default => 'payment',
        };
    }

    public function __construct(
        StripePaymentService $stripeService, 
        InvoiceGenerationService $invoiceService,
        EscrowService $escrowService,
        EquipmentRentalPaymentSyncService $equipmentRentalPaymentSyncService
    )
    {
        $this->stripeService = $stripeService;
        $this->invoiceService = $invoiceService;
        $this->escrowService = $escrowService;
        $this->equipmentRentalPaymentSyncService = $equipmentRentalPaymentSyncService;
    }

    /**
     * Show payment form for a booking
     */
    public function showPaymentForm(Booking $booking)
    {
        $this->authorize('pay', $booking);

        if (function_exists('booking_online_payment_enabled') && !booking_online_payment_enabled()) {
            return redirect()->route('client.bookings.show', $booking)
                ->with('info', 'Le paiement en ligne des réservations est désactivé.');
        }

        $booking->loadMissing('service.prestataire', 'prestataire');

        $paymentRequirement = $this->getBookingPaymentRequirement($booking);
        $prestataire = $booking->prestataire ?? $booking->service?->prestataire;
        $stripeAccountId = $prestataire?->stripe_account_id;

        if ($paymentRequirement === 'none') {
            return redirect()->route('client.bookings.show', $booking)
                ->with('error', 'Ce service est configuré pour paiement en espèces uniquement.');
        }

        if (empty($stripeAccountId)) {
            return redirect()->route('client.bookings.show', $booking)
                ->with('error', 'Le paiement en ligne n\'est pas disponible pour ce prestataire.');
        }

        // Keep booking deposit_amount in sync with the service payment criteria.
        // Only adjust if nothing has been paid yet.
        if ($booking->payment_status === 'pending') {
            $expectedDeposit = $this->expectedBookingDepositAmount($booking);
            $currentDeposit = (float) ($booking->deposit_amount ?? 0);

            if (abs($currentDeposit - $expectedDeposit) > 0.009) {
                $hasAnyTx = PaymentTransaction::where('booking_id', $booking->id)->exists();
                if (!$hasAnyTx) {
                    $booking->update(['deposit_amount' => $expectedDeposit]);
                    $booking->refresh();
                }
            }
        }

        return view('payments.booking-payment', [
            'booking' => $booking,
            'clientSecret' => null,
            'stripeAccountId' => $stripeAccountId,
        ]);
    }

    /**
     * Create payment intent
     */
    public function createPaymentIntent(CreatePaymentIntentRequest $request, Booking $booking)
    {
        $this->authorize('pay', $booking);

        if (function_exists('booking_online_payment_enabled') && !booking_online_payment_enabled()) {
            return response()->json(['error' => 'Le paiement en ligne des réservations est désactivé.'], 422);
        }

        $booking->loadMissing('service.prestataire', 'prestataire');

        $paymentRequirement = $this->getBookingPaymentRequirement($booking);
        $connectedAccountId = $booking->service?->prestataire?->stripe_account_id
            ?: $booking->prestataire?->stripe_account_id;

        if ($paymentRequirement === 'none') {
            return response()->json(['error' => 'Paiement en ligne non autorisé pour cette réservation.'], 422);
        }

        if (empty($connectedAccountId)) {
            return response()->json(['error' => 'Le prestataire n\'a pas configuré le paiement en ligne.'], 422);
        }

        $validated = $request->validated();

        try {
            // Enregistrer l'acceptation des conditions si fournie (RGPD)
            if (!empty($validated['terms_version']) && !empty($validated['terms_accepted_at'])) {
                $booking->update([
                    'payment_terms_version' => $validated['terms_version'],
                    'payment_terms_accepted_at' => $validated['terms_accepted_at'],
                    'payment_terms_ip' => $request->ip(),
                ]);
            }

            $amount = 0;
            $description = "";
            $metadata = ['booking_id' => $booking->id, 'payment_type' => $validated['payment_type'], 'user_id' => auth()->id()];

            $this->assertPaymentTypeAllowed($booking, $validated['payment_type']);

            if ($validated['payment_type'] === 'deposit') {
                $amount = $booking->deposit_amount;
                $description = "Deposit for Booking #{$booking->booking_number}";
            } elseif ($validated['payment_type'] === 'balance') {
                $amount = $booking->total_price - $booking->deposit_amount;
                $description = "Balance payment for Booking #{$booking->booking_number}";
            } else {
                $amount = $booking->total_price;
                $description = "Full payment for Booking #{$booking->booking_number}";
            }

            if ($amount <= 0) {
                throw new \Exception("Montant de paiement invalide. Veuillez vérifier le montant de votre réservation.");
            }

            // Vérifier si le service nécessite un blocage (escrow)
            // Services avec payment_requirement = 'full' ou 'deposit' utilisent l'escrow
            $useEscrow = in_array($paymentRequirement, ['full', 'deposit']);
            
            if ($useEscrow) {
                // ESCROW: PaymentIntent sur le compte plateforme pour blocage des fonds
                $paymentIntent = $this->stripeService->createEscrowPaymentIntent(
                    auth()->user(),
                    $amount,
                    $description,
                    array_merge($metadata, ['connected_account_id' => $connectedAccountId]),
                    $connectedAccountId // Stocké en metadata pour le transfer ultérieur
                );
            } else {
                // Direct charge (ancien comportement)
                $paymentIntent = $this->stripeService->createPaymentIntent(
                    auth()->user(),
                    $amount,
                    $description,
                    $metadata,
                    $connectedAccountId
                );
            }

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id,
                'amount' => $amount,
            ]);
        } catch (\Exception $e) {
            \Log::error('createPaymentIntent failed: ' . $e->getMessage(), ['user' => auth()->id()]);
            return response()->json(['error' => 'Impossible de créer le paiement. Veuillez réessayer.'], 422);
        }
    }

    /**
     * Confirm payment
     */
    public function confirmPayment(ConfirmPaymentRequest $request, Booking $booking)
    {
        $this->authorize('pay', $booking);

        if (function_exists('booking_online_payment_enabled') && !booking_online_payment_enabled()) {
            return response()->json(['error' => 'Le paiement en ligne des réservations est désactivé.'], 422);
        }

        $booking->loadMissing('service.prestataire', 'prestataire');

        $validated = $request->validated();

        try {
            // Enregistrer l'acceptation des conditions si fournie et pas déjà enregistrée (RGPD)
            if (!empty($validated['terms_version']) && !empty($validated['terms_accepted_at']) && empty($booking->payment_terms_accepted_at)) {
                $booking->update([
                    'payment_terms_version' => $validated['terms_version'],
                    'payment_terms_accepted_at' => $validated['terms_accepted_at'],
                    'payment_terms_ip' => $request->ip(),
                ]);
            }

            // Idempotence: paiement déjà enregistré pour ce PI
            $existingTx = PaymentTransaction::where('stripe_payment_intent_id', $validated['payment_intent_id'])->first();
            if ($existingTx) {
                if ((int) ($existingTx->booking_id ?? 0) !== (int) $booking->id) {
                    return response()->json(['error' => 'Ce paiement est déjà associé à une autre réservation.'], 422);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Paiement déjà confirmé.',
                    'booking_id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                ]);
            }

            $this->assertPaymentTypeAllowed($booking, $validated['payment_type']);

            // Handle Stripe Payment
            $connectedAccountId = $booking->service?->prestataire?->stripe_account_id
                ?: $booking->prestataire?->stripe_account_id;

            $paymentIntent = null;
            $piOnConnectedAccount = false;

            // Escrow bookings are created on platform account; direct charges are on connected account.
            try {
                $paymentIntent = $this->stripeService->retrievePaymentIntent($validated['payment_intent_id'], null);
            } catch (\Throwable $platformError) {
                $paymentIntent = $this->stripeService->retrievePaymentIntent($validated['payment_intent_id'], $connectedAccountId);
                $piOnConnectedAccount = true;
            }

            if ($paymentIntent->status === 'requires_confirmation') {
                if (empty($validated['payment_method_id'])) {
                    return response()->json(['error' => 'Moyen de paiement manquant pour confirmer le PaymentIntent.'], 422);
                }

                $paymentIntent = $this->stripeService->confirmPayment(
                    $validated['payment_intent_id'],
                    $validated['payment_method_id'],
                    $piOnConnectedAccount ? $connectedAccountId : null
                );
            }

            if (!in_array($paymentIntent->status, ['succeeded', 'requires_capture'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paiement en cours de traitement. Veuillez patienter.',
                    'status' => $paymentIntent->status,
                ]);
            }

            $this->assertPaymentIntentMatchesBooking($paymentIntent, $booking, $validated['payment_type']);

            if (in_array($paymentIntent->status, ['succeeded', 'requires_capture'], true)) {
                $txType = $this->mapPaymentTypeToTransactionType($validated['payment_type']);
                $transaction = DB::transaction(function () use ($paymentIntent, $booking, $txType, $validated) {
                    $existing = PaymentTransaction::where('stripe_payment_intent_id', $paymentIntent->id)
                        ->lockForUpdate()
                        ->first();
                    $transaction = $existing ?: $this->stripeService->recordPayment(
                        $paymentIntent,
                        $booking->id,
                        null,
                        $txType
                    );

                    // ESCROW: Créer l'entrée escrow pour bloquer les fonds
                    $prestataireId = $booking->prestataire_id ?? $booking->service?->prestataire_id;
                    $isEscrow = $this->stripeService->isEscrowPayment($paymentIntent);

                    if ($isEscrow && $prestataireId) {
                        $existingEscrow = DB::table('escrow_transactions')
                            ->where('stripe_payment_intent_id', $paymentIntent->id)
                            ->where('escrowable_type', Booking::class)
                            ->where('escrowable_id', $booking->id)
                            ->lockForUpdate()
                            ->first();

                        if (!$existingEscrow) {
                            $createdEscrow = $this->escrowService->createEscrow(
                                $booking,                              // escrowable
                                auth()->id(),                          // clientId
                                $prestataireId,                        // prestataireId
                                (float) $transaction->amount,          // amount
                                0,                                     // depositAmount
                                $paymentIntent->id,                    // stripePaymentIntentId
                                null,                                  // platformFeeOverride (use default)
                                [                                      // metadata
                                    'payment_type' => $validated['payment_type'],
                                    'transaction_id' => $transaction->id,
                                    'booking_number' => $booking->booking_number,
                                ]
                            );

                            if (!$createdEscrow) {
                                throw new \RuntimeException('Impossible de créer l\'escrow pour ce paiement.');
                            }
                        }
                    }

                    // Update booking status
                    if ($validated['payment_type'] === 'deposit') {
                        $booking->update(['payment_status' => 'deposit_paid']);
                        if ($booking->isPending()) {
                            $booking->confirm();
                        }
                    } elseif ($validated['payment_type'] === 'balance' || $validated['payment_type'] === 'full') {
                        $booking->update(['payment_status' => 'paid']);
                        if ($booking->isPending()) {
                            $booking->confirm();
                        }
                    }

                    return $transaction;
                });

                // Générer les factures
                try {
                    $this->invoiceService->generateForBooking($booking, $transaction);
                } catch (\Exception $e) {
                    \Log::warning('Failed to generate invoice for booking payment: ' . $e->getMessage());
                }

                // Send confirmation notification to prestataire
                try {
                    $booking->prestataire->user->notify(new \App\Notifications\BookingPaymentConfirmed($booking));
                } catch (\Exception $e) {
                    \Log::warning('Failed to notify prestataire of payment: ' . $e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Paiement effectué avec succès et réservation confirmée.',
                    'booking_id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('confirmPayment failed: ' . $e->getMessage(), [
                'user' => auth()->id(),
                'booking_id' => $booking->id,
                'payment_intent_id' => $validated['payment_intent_id'] ?? null,
            ]);
            return response()->json(['error' => 'Erreur lors de la confirmation du paiement.'], 422);
        }
    }

    /**
     * API: Get transactions for the authenticated user
     */
    public function apiTransactions(Request $request)
    {
        $transactions = auth()->user()->transactions()
            ->latest()
            ->paginate(20);

        return response()->json($transactions);
    }

    /**
     * Show transaction history
     */
    public function transactionHistory()
    {
        // Vérifier si la table payment_transactions existe
        if (!TableExistenceCache::has('payment_transactions')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 20, 1, ['path' => request()->url()]
            );
            return view('payments.transaction-history', [
                'transactions' => $emptyPaginator,
                'tableNotExists' => true,
            ]);
        }

        try {
            $transactions = auth()->user()->transactions()
                ->latest()
                ->paginate(20);

            return view('payments.transaction-history', compact('transactions'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 20, 1, ['path' => request()->url()]
            );
            return view('payments.transaction-history', [
                'transactions' => $emptyPaginator,
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Request refund
     */
    public function requestRefund(Booking $booking)
    {
        $this->authorize('pay', $booking);

        if (!in_array((string) $booking->status, ['pending', 'confirmed'], true)) {
            return back()->with('error', 'Le remboursement n\'est pas disponible pour une réservation déjà terminée ou annulée.');
        }

        if (in_array((string) $booking->payment_status, ['refunded', 'failed'], true)) {
            return back()->with('error', 'Cette réservation est déjà remboursée ou en échec de paiement.');
        }

        $transaction = $booking->paymentTransaction;

        if (!$transaction || !$transaction->isPaid()) {
            return back()->with('error', 'Aucun paiement à rembourser pour cette réservation.');
        }

        try {
            $this->stripeService->refundPayment(
                $transaction,
                null,
                'Client requested refund'
            );

            return back()->with('success', 'Remboursement effectué avec succès.');
        } catch (\Exception $e) {
            \Log::error('Refund failed: ' . $e->getMessage(), ['user' => auth()->id()]);
            return back()->with('error', 'Le remboursement a échoué. Veuillez réessayer ou contacter le support.');
        }
    }

    // ============================================================================
    // CLIENT METHODS
    // ============================================================================

    /**
     * Client payments index
     */
    public function clientIndex()
    {
        $user = auth()->user();

        try {
            $this->equipmentRentalPaymentSyncService->syncForClient($user);
        } catch (\Throwable $e) {
            Log::warning('clientIndex rental payment sync warning', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
        
        // Vérifier si la table payment_transactions existe
        if (!TableExistenceCache::has('payment_transactions')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 20, 1, ['path' => request()->url()]
            );
            return view('client.payments.index', [
                'transactions' => $emptyPaginator,
                'totalSpent' => 0,
                'transactionsCount' => 0,
                'pendingCount' => 0,
                'refundedCount' => 0,
            ]);
        }

        try {
            $transactions = \App\Models\PaymentTransaction::where('user_id', $user->id)
                ->with(['equipmentRental.equipment'])
                ->latest()
                ->paginate(20);

            $totalSpent = \App\Models\PaymentTransaction::where('user_id', $user->id)
                ->where('status', 'paid')->sum('amount');
            $transactionsCount = \App\Models\PaymentTransaction::where('user_id', $user->id)->count();
            $pendingCount = \App\Models\PaymentTransaction::where('user_id', $user->id)
                ->where('status', 'pending')->count();
            $refundedCount = \App\Models\PaymentTransaction::where('user_id', $user->id)
                ->where('status', 'refunded')->count();

            return view('client.payments.index', compact(
                'transactions', 
                'totalSpent', 
                'transactionsCount', 
                'pendingCount', 
                'refundedCount'
            ));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 20, 1, ['path' => request()->url()]
            );
            return view('client.payments.index', [
                'transactions' => $emptyPaginator,
                'totalSpent' => 0,
                'transactionsCount' => 0,
                'pendingCount' => 0,
                'refundedCount' => 0,
            ]);
        }
    }

    /**
     * Client payment transaction detail view
     */
    public function clientShow(PaymentTransaction $transaction)
    {
        $user = auth()->user();
        
        // Vérifier que la transaction appartient à l'utilisateur
        if ($transaction->user_id !== $user->id) {
            abort(403, 'Accès non autorisé');
        }

        // Charger les allocations/achats liés à cette transaction
        $purchases = collect();
        $booking = null;
        $rentalRequest = null;
        $rentalEscrow = null;
        $cartItems = collect();
        
        // Charger le booking lié
        if ($transaction->booking_id) {
            $booking = \App\Models\Booking::with(['prestataire.user', 'service'])
                ->find($transaction->booking_id);
        }

        // Charger la demande de location liée (compat colonnes legacy/actuelle + metadata)
        $rentalRequestId = $transaction->equipment_rental_id ?? $transaction->equipment_rental_request_id ?? null;
        if (!$rentalRequestId) {
            $metadata = PaymentMetadataNormalizer::normalize((array) ($transaction->metadata ?? []));
            $rentalRequestId = $metadata['rental_request_id'] ?? null;
        }
        if ($rentalRequestId) {
            $rentalRequest = \App\Models\EquipmentRentalRequest::with(['equipment.prestataire.user'])
                ->find((int) $rentalRequestId);

            if ($rentalRequest && TableExistenceCache::has('escrow_transactions')) {
                $rentalEscrow = \Illuminate\Support\Facades\DB::table('escrow_transactions')
                    ->where('escrowable_id', (int) $rentalRequest->id)
                    ->where('escrowable_type', 'like', '%EquipmentRentalRequest%')
                    ->orderByDesc('id')
                    ->first();
            }
        }

        // Charger les achats Urgent Sale liés
        if (TableExistenceCache::has('urgent_sale_purchases')) {
            $purchases = \App\Models\UrgentSalePurchase::with(['urgentSale.prestataire.user'])
                ->where('payment_transaction_id', $transaction->id)
                ->get();
        }

        // Si pas de purchases, charger via les allocations
        if ($purchases->isEmpty() && TableExistenceCache::has('payment_allocations')) {
            $allocations = \App\Models\PaymentAllocation::where('payment_transaction_id', $transaction->id)->get();
            
            foreach ($allocations as $allocation) {
                if ($allocation->payable_type === 'App\\Models\\UrgentSale' || str_contains($allocation->payable_type, 'UrgentSale')) {
                    $urgentSale = \App\Models\UrgentSale::with(['prestataire.user'])->find($allocation->payable_id);
                    if ($urgentSale) {
                        $cartItems->push([
                            'type' => 'urgent_sale',
                            'item' => $urgentSale,
                            'amount' => $allocation->amount,
                            'allocation' => $allocation,
                        ]);
                    }
                } elseif ($allocation->payable_type === 'App\\Models\\Booking' || str_contains($allocation->payable_type, 'Booking')) {
                    $bookingItem = \App\Models\Booking::with(['prestataire.user', 'service'])->find($allocation->payable_id);
                    if ($bookingItem) {
                        $cartItems->push([
                            'type' => 'booking',
                            'item' => $bookingItem,
                            'amount' => $allocation->amount,
                            'allocation' => $allocation,
                        ]);
                    }
                } elseif ($allocation->payable_type === 'App\\Models\\EquipmentRentalRequest' || str_contains($allocation->payable_type, 'EquipmentRentalRequest')) {
                    $rental = \App\Models\EquipmentRentalRequest::with(['equipment.prestataire.user'])->find($allocation->payable_id);
                    if ($rental) {
                        $cartItems->push([
                            'type' => 'equipment_rental',
                            'item' => $rental,
                            'amount' => $allocation->amount,
                            'allocation' => $allocation,
                        ]);
                    }
                }
            }
        }

        // Charger les allocations génériques
        $allocations = collect();
        if (TableExistenceCache::has('payment_allocations')) {
            $allocations = \App\Models\PaymentAllocation::where('payment_transaction_id', $transaction->id)
                ->get();
        }

        return view('client.payments.show', compact(
            'transaction', 
            'purchases',
            'booking',
            'rentalRequest',
            'rentalEscrow',
            'cartItems',
            'allocations'
        ));
    }

    // ============================================================================
    // PRESTATAIRE METHODS
    // ============================================================================

    /**
     * Prestataire payments index
     */
    public function prestataireIndex()
    {
        $user = auth()->user();
        $prestataire = $user->prestataire;

        if ($prestataire) {
            try {
                $this->equipmentRentalPaymentSyncService->syncForPrestataire($prestataire);
            } catch (\Throwable $e) {
                Log::warning('prestataireIndex rental payment sync warning', [
                    'prestataire_id' => $prestataire->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Vérifier si la table payment_transactions existe
        if (!TableExistenceCache::has('payment_transactions')) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 20, 1, ['path' => request()->url()]
            );
            
            return view('prestataire.payments.index', [
                'transactions' => $emptyPaginator,
                'stats' => [
                    'total_earned' => 0,
                    'pending_payments' => 0,
                    'this_month' => 0,
                ],
                'tableNotExists' => true,
            ]);
        }

        try {
            $transactions = \App\Models\PaymentTransaction::whereHas('booking', function($q) use ($prestataire) {
                $q->whereHas('service', function($sq) use ($prestataire) {
                    $sq->where('prestataire_id', $prestataire->id);
                });
            })->latest()->paginate(20);

            $stats = [
                'total_earned' => $transactions->where('status', 'paid')->sum('amount'),
                'pending_payments' => $transactions->where('status', 'pending')->count(),
                'this_month' => \App\Models\PaymentTransaction::whereHas('booking', function($q) use ($prestataire) {
                    $q->whereHas('service', function($sq) use ($prestataire) {
                        $sq->where('prestataire_id', $prestataire->id);
                    });
                })->whereMonth('created_at', now()->month)->where('status', 'paid')->sum('amount'),
            ];

            return view('prestataire.payments.index', compact('transactions', 'stats'));
        } catch (\Exception $e) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 20, 1, ['path' => request()->url()]
            );
            
            return view('prestataire.payments.index', [
                'transactions' => $emptyPaginator,
                'stats' => [
                    'total_earned' => 0,
                    'pending_payments' => 0,
                    'this_month' => 0,
                ],
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Prestataire payment history (alias of index for now)
     */
    public function prestataireHistory()
    {
        return $this->prestataireIndex();
    }

    /**
     * Prestataire sale detail view
     */
    public function prestataireShow(PaymentTransaction $transaction)
    {
        $user = auth()->user();
        $prestataire = $user->prestataire;
        
        if (!$prestataire) {
            abort(403, 'Vous devez être prestataire pour accéder à cette page');
        }

        // Charger les achats liés à cette transaction pour ce prestataire
        $purchases = [];
        $booking = null;
        $rentalRequest = null;
        $buyer = null;
        
        // Charger le buyer
        if ($transaction->user_id) {
            $buyer = \App\Models\User::find($transaction->user_id);
        }

        // Charger le booking lié (si c'est pour ce prestataire)
        if ($transaction->booking_id) {
            $booking = \App\Models\Booking::with(['client.user', 'service'])
                ->where('prestataire_id', $prestataire->id)
                ->find($transaction->booking_id);
        }

        // Charger les achats Urgent Sale liés
        if (TableExistenceCache::has('urgent_sale_purchases')) {
            $purchases = \App\Models\UrgentSalePurchase::with(['urgentSale', 'buyer'])
                ->where('payment_transaction_id', $transaction->id)
                ->whereHas('urgentSale', function ($q) use ($prestataire) {
                    $q->where('prestataire_id', $prestataire->id);
                })
                ->get();
        }

        // Vérifier que ce prestataire a le droit de voir cette transaction
        $hasAccess = $booking || $purchases->count() > 0;
        
        if (!$hasAccess) {
            abort(403, 'Vous n\'avez pas accès à cette transaction');
        }

        // Calculer la commission et le montant net
        $type = $booking ? 'service' : ($purchases->count() > 0 ? 'urgent_sale' : 'service');

        $grossAmount = (float) $transaction->amount;
        $commissionRatePercent = \App\Services\CommissionService::ratePercent($type, 'prestataire');
        $commissionRate = $commissionRatePercent / 100;
        $commission = \App\Services\CommissionService::feeAmount($grossAmount, $type, 'prestataire', null, $prestataire);
        $netAmount = $grossAmount - $commission;

        return view('prestataire.payments.show', compact(
            'transaction', 
            'purchases',
            'booking',
            'buyer',
            'grossAmount',
            'commission',
            'netAmount',
            'commissionRate'
        ));
    }

    /**
     * Prestataire withdraw — Stripe Connect Payout vers la banque du prestataire
     */
    public function prestataireWithdraw(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return back()->with('error', 'Profil prestataire introuvable.');
        }

        // ── Vérifications préalables ────────────────────────────────────
        $stripeAccountId = $prestataire->stripe_account_id ?? null;
        if (!$stripeAccountId) {
            return back()->with('error', 'Aucun compte Stripe Connect configuré. Veuillez d\'abord connecter votre compte Stripe.');
        }

        // Seuil minimum configurable (admin setting ou 50 € par défaut)
        $minWithdrawal = (float) get_setting('min_withdrawal', '50');
        $amount = (float) $validated['amount'];

        if ($amount < $minWithdrawal) {
            return back()->with('error', "Le montant minimum de retrait est de {$minWithdrawal} €.");
        }

        // Vérifier le solde interne
        $internalBalance = (float) ($prestataire->balance ?? 0);
        if ($amount > $internalBalance) {
            return back()->with('error', 'Solde insuffisant. Votre solde disponible est de ' . number_format($internalBalance, 2) . ' €.');
        }

        // ── Créer la demande de retrait + Stripe Payout ─────────────────
        try {
            DB::beginTransaction();

            // Décrémenter le solde interne immédiatement (évite double retrait)
            DB::table('prestataires')
                ->where('id', $prestataire->id)
                ->lockForUpdate()
                ->first();

            // Re-vérifier le solde après le lock
            $currentBalance = (float) DB::table('prestataires')->where('id', $prestataire->id)->value('balance');
            if ($amount > $currentBalance) {
                DB::rollBack();
                return back()->with('error', 'Solde insuffisant après vérification.');
            }

            DB::table('prestataires')
                ->where('id', $prestataire->id)
                ->decrement('balance', $amount);

            // Créer l'enregistrement dans la table withdrawals
            $withdrawalId = DB::table('withdrawals')->insertGetId([
                'user_id' => $user->id,
                'amount' => $amount,
                'method' => 'stripe_connect',
                'status' => 'pending',
                'bank_details' => json_encode(['stripe_account_id' => $stripeAccountId]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Vérifier que le compte Connect peut recevoir des payouts
            $stripeService = app(StripePaymentService::class);

            if (!$stripeService->canReceiveTransfers($stripeAccountId)) {
                // Le compte existe mais l'onboarding n'est pas terminé
                DB::rollBack();
                return back()->with('error', 'Votre compte Stripe Connect n\'est pas encore entièrement vérifié. Veuillez compléter votre onboarding Stripe.');
            }

            // Vérifier le solde disponible sur le compte Connect
            $stripeBalance = $stripeService->getConnectedAccountBalance($stripeAccountId);
            if ($amount > $stripeBalance) {
                // Le solde Stripe Connect est insuffisant (fonds pas encore arrivés)
                DB::rollBack();
                return back()->with('error', 'Solde Stripe Connect insuffisant (' . number_format($stripeBalance, 2) . ' €). Les fonds peuvent prendre 1-2 jours pour être disponibles.');
            }

            // Initier le Payout Stripe (Connect → banque du prestataire)
            $payout = $stripeService->createPayoutOnConnectedAccount(
                $stripeAccountId,
                $amount,
                "Retrait TapRestation #{$withdrawalId}",
                [
                    'withdrawal_id' => (string) $withdrawalId,
                    'prestataire_id' => (string) $prestataire->id,
                    'user_id' => (string) $user->id,
                ]
            );

            // Mettre à jour le retrait avec la référence Stripe
            DB::table('withdrawals')->where('id', $withdrawalId)->update([
                'status' => 'completed',
                'transaction_reference' => $payout->id,
                'processed_at' => now(),
                'admin_notes' => 'Payout Stripe automatique',
                'updated_at' => now(),
            ]);

            // Enregistrer dans le finance_ledger
            DB::table('finance_ledger')->insert([
                'type' => 'payout',
                'reference_id' => $withdrawalId,
                'user_id' => $user->id,
                'prestataire_id' => $prestataire->id,
                'amount' => -$amount,
                'balance_before' => $currentBalance,
                'balance_after' => $currentBalance - $amount,
                'notes' => "Retrait #{$withdrawalId} via Stripe Payout {$payout->id}",
                'meta' => json_encode([
                    'withdrawal_id' => $withdrawalId,
                    'stripe_payout_id' => $payout->id,
                    'stripe_account_id' => $stripeAccountId,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            Log::info("Retrait #{$withdrawalId} de {$amount}€ initié pour prestataire #{$prestataire->id} → Payout {$payout->id}");

            return back()->with('success', 'Retrait de ' . number_format($amount, 2) . ' € initié avec succès. Les fonds seront sur votre compte bancaire sous 1-2 jours ouvrables.');

        } catch (\Stripe\Exception\ApiErrorException $e) {
            DB::rollBack();
            Log::error("Erreur Stripe pour retrait prestataire #{$prestataire->id}: " . $e->getMessage());
            return back()->with('error', 'Le retrait Stripe est temporairement indisponible.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur retrait prestataire #{$prestataire->id}: " . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors du retrait. Veuillez réessayer.');
        }
    }

    // ============================================================================
    // ADMIN METHODS
    // ============================================================================

    /**
     * Admin transaction history
     */
    public function adminTransactionHistory(Request $request)
    {
        $this->ensureAdminPaymentAccess();

        $filters = $this->resolveAdminPaymentFilters($request);
        $pageWarnings = [];

        return view('admin.payments.index', [
            'filters' => $filters,
            'filterOptions' => $this->adminPaymentFilterOptions(),
            'stats' => $this->safeAdminPaymentsSection('stats', fn () => $this->buildAdminPaymentStats(), $this->emptyAdminPaymentStats(), $pageWarnings),
            'transactions' => $this->safeAdminPaymentsSection('transactions', fn () => $this->buildAdminTransactionsPaginator($filters), $this->emptyPaginator('transactions_page', 20), $pageWarnings),
            'legacyTransactions' => $this->safeAdminPaymentsSection('legacyTransactions', fn () => $this->buildAdminLegacyTransactionsPaginator($filters), $this->emptyPaginator('legacy_transactions_page', 15), $pageWarnings),
            'refundRequests' => $this->safeAdminPaymentsSection('refundRequests', fn () => $this->buildAdminRefundsPaginator($filters), $this->emptyPaginator('refunds_page', 15), $pageWarnings),
            'escrows' => $this->safeAdminPaymentsSection('escrows', fn () => $this->buildAdminEscrowsPaginator($filters), $this->emptyPaginator('escrows_page', 15), $pageWarnings),
            'foodActionQueue' => $this->safeAdminPaymentsSection('foodActionQueue', fn () => $this->buildAdminFoodActionQueue($filters), collect(), $pageWarnings),
            'equipmentDepositQueue' => $this->safeAdminPaymentsSection('equipmentDepositQueue', fn () => $this->buildAdminEquipmentDepositQueue($filters), collect(), $pageWarnings),
            'disputeQueue' => $this->safeAdminPaymentsSection('disputeQueue', fn () => $this->buildAdminDisputeQueue($filters), collect(), $pageWarnings),
            'tableAvailability' => [
                'payment_transactions' => TableExistenceCache::has('payment_transactions'),
                'transactions' => TableExistenceCache::has('transactions'),
                'refunds' => TableExistenceCache::has('refunds'),
                'escrow_transactions' => TableExistenceCache::has('escrow_transactions'),
                'food_orders' => TableExistenceCache::has('food_orders'),
                'equipment_rental_requests' => TableExistenceCache::has('equipment_rental_requests'),
            ],
            'pageWarnings' => $pageWarnings,
        ]);
    }

    /**
     * Admin analytics
     */
    public function adminAnalytics()
    {
        // Vérifier si la table payment_transactions existe
        if (!TableExistenceCache::has('payment_transactions')) {
            return view('admin.payments.analytics', [
                'monthlyRevenue' => collect(),
                'tableNotExists' => true,
            ]);
        }

        try {
            $monthlyRevenue = PaymentTransaction::whereIn('status', ['paid', 'completed', 'released'])
                ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(amount) as total')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get();

            return view('admin.payments.analytics', compact('monthlyRevenue'));
        } catch (\Exception $e) {
            return view('admin.payments.analytics', [
                'monthlyRevenue' => collect(),
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Admin refund
     * SÉCURITÉ M5: Ajout d'une vérification d'autorisation explicite
     */
    public function adminRefund(AdminRefundRequest $request, PaymentTransaction $transaction)
    {
        $this->ensureAdminPaymentAccess();

        $validated = $request->validated();

        $refundAmount = isset($validated['amount']) ? round((float) $validated['amount'], 2) : null;
        $reason = trim((string) ($validated['reason'] ?? ''));
        $reason = $reason !== '' ? $reason : 'Remboursement administrateur';

        if (!in_array((string) $transaction->status, ['paid', 'completed', 'held', 'released', 'partially_refunded'], true)) {
            return back()->with('error', "Cette transaction n'est pas remboursable dans son état actuel.");
        }

        try {
            $this->stripeService->refundPayment($transaction, $refundAmount, $reason);

            if ($refundAmount !== null && $refundAmount + 0.01 < (float) ($transaction->amount ?? 0)) {
                try {
                    $transaction->forceFill([
                        'status' => 'partially_refunded',
                        'refunded_at' => now(),
                        'refund_reason' => $reason,
                    ])->save();
                } catch (\Throwable $statusError) {
                    Log::warning('Admin refund: unable to mark transaction as partially_refunded', [
                        'transaction_id' => $transaction->id,
                        'error' => $statusError->getMessage(),
                    ]);
                }
            }

            if (TableExistenceCache::has('refunds')) {
                PaymentRefund::systemCreate([
                    'transaction_id' => $transaction->id,
                    'booking_id' => $transaction->booking_id,
                    'user_id' => $transaction->user_id,
                    'amount' => $refundAmount ?? (float) ($transaction->amount ?? 0),
                    'reason' => $reason,
                    'status' => 'completed',
                    'processed_at' => now(),
                    'processed_by' => auth()->id(),
                    'created_by' => auth()->id(),
                ]);
            }

            return back()->with('success', 'Remboursement administrateur effectué avec succès.');
        } catch (\Throwable $e) {
            Log::error('Admin refund failed: ' . $e->getMessage(), ['user' => auth()->id(), 'transaction_id' => $transaction->id]);
            return back()->with('error', 'Le remboursement a échoué. Veuillez réessayer.');
        }
    }

    public function adminProcessRefundRequest(AdminResolveRefundRequest $request, PaymentRefund $refund)
    {
        $this->ensureAdminPaymentAccess();

        $validated = $request->validated();

        if ((string) $refund->status !== 'pending') {
            return back()->with('error', 'Cette demande de remboursement a déjà été traitée.');
        }

        try {
            if ($validated['decision'] === 'approve') {
                $transaction = $refund->transaction;
                if ($transaction) {
                    $refundAmount = round((float) ($refund->amount ?? 0), 2);
                    $this->stripeService->refundPayment(
                        $transaction,
                        $refundAmount > 0 ? $refundAmount : null,
                        (string) ($refund->reason ?? 'Demande approuvée par un administrateur')
                    );

                    if ($refundAmount > 0 && $refundAmount + 0.01 < (float) ($transaction->amount ?? 0)) {
                        try {
                            $transaction->forceFill([
                                'status' => 'partially_refunded',
                                'refunded_at' => now(),
                                'refund_reason' => $refund->reason,
                            ])->save();
                        } catch (\Throwable $statusError) {
                            Log::warning('Refund request approval: unable to persist partially_refunded status', [
                                'transaction_id' => $transaction->id,
                                'refund_id' => $refund->id,
                                'error' => $statusError->getMessage(),
                            ]);
                        }
                    }
                }

                $refund->forceFill([
                    'status' => 'completed',
                    'processed_at' => now(),
                    'processed_by' => auth()->id(),
                    'admin_notes' => $validated['notes'] ?? null,
                ])->save();

                return back()->with('success', 'Demande de remboursement approuvée.');
            }

            $refund->forceFill([
                'status' => 'rejected',
                'processed_at' => now(),
                'processed_by' => auth()->id(),
                'admin_notes' => $validated['notes'] ?? null,
            ])->save();

            return back()->with('success', 'Demande de remboursement rejetée.');
        } catch (\Throwable $e) {
            Log::error('Admin refund request processing failed: ' . $e->getMessage(), [
                'refund_id' => $refund->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Le traitement du remboursement a échoué.');
        }
    }

    public function adminFoodCapture(FoodOrder $foodOrder)
    {
        $this->ensureAdminPaymentAccess();

        try {
            if (!$foodOrder->capturePayment()) {
                return back()->with('error', "Impossible de capturer ce paiement food dans son état actuel.");
            }

            return back()->with('success', 'Autorisation food capturée et fonds placés en retenue.');
        } catch (\Throwable $e) {
            Log::error('Admin food capture failed: ' . $e->getMessage(), [
                'food_order_id' => $foodOrder->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'La capture du paiement food a échoué.');
        }
    }

    public function adminFoodCancelAuthorization(Request $request, FoodOrder $foodOrder)
    {
        $this->ensureAdminPaymentAccess();

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $reason = trim((string) ($validated['reason'] ?? ''));
        $reason = $reason !== '' ? $reason : "Annulation d'autorisation par un administrateur";

        try {
            if (!$foodOrder->cancelAuthorization($reason)) {
                return back()->with('error', "Impossible d'annuler cette autorisation food.");
            }

            return back()->with('success', 'Autorisation food annulée.');
        } catch (\Throwable $e) {
            Log::error('Admin food authorization cancel failed: ' . $e->getMessage(), [
                'food_order_id' => $foodOrder->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', "L'annulation de l'autorisation food a échoué.");
        }
    }

    public function adminFoodRefund(Request $request, FoodOrder $foodOrder)
    {
        $this->ensureAdminPaymentAccess();

        $validated = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $refundAmount = isset($validated['amount']) ? round((float) $validated['amount'], 2) : null;
        $reason = trim((string) ($validated['reason'] ?? ''));
        $reason = $reason !== '' ? $reason : 'Remboursement administrateur food';

        try {
            if (!$foodOrder->refundPayment($reason, $refundAmount)) {
                return back()->with('error', 'Impossible de rembourser cette commande food.');
            }

            return back()->with('success', 'Commande food remboursée.');
        } catch (\Throwable $e) {
            Log::error('Admin food refund failed: ' . $e->getMessage(), [
                'food_order_id' => $foodOrder->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Le remboursement food a échoué.');
        }
    }

    public function adminEscrowRelease(Request $request, int $escrowId)
    {
        $this->ensureAdminPaymentAccess();

        $validated = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        $amount = isset($validated['amount']) ? round((float) $validated['amount'], 2) : null;

        try {
            if (!$this->escrowService->releaseToPrestataire($escrowId, $amount)) {
                return back()->with('error', $this->escrowService->getLastError() ?? 'Impossible de libérer cet escrow.');
            }

            return back()->with('success', 'Escrow libéré vers le prestataire.');
        } catch (\Throwable $e) {
            Log::error('Admin escrow release failed: ' . $e->getMessage(), [
                'escrow_id' => $escrowId,
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', "La libération de l'escrow a échoué.");
        }
    }

    public function adminEscrowRefund(AdminEscrowRefundRequest $request, int $escrowId)
    {
        $this->ensureAdminPaymentAccess();

        $validated = $request->validated();

        $escrow = TableExistenceCache::has('escrow_transactions')
            ? DB::table('escrow_transactions')->where('id', $escrowId)->first()
            : null;

        if (!$escrow) {
            return back()->with('error', 'Escrow introuvable.');
        }

        $defaultAmount = (float) ($escrow->remaining_amount ?? $escrow->total_amount ?? $escrow->amount ?? 0);
        $amount = isset($validated['amount']) ? round((float) $validated['amount'], 2) : round($defaultAmount, 2);
        $reason = trim((string) ($validated['reason'] ?? ''));
        $reason = $reason !== '' ? $reason : 'Remboursement administrateur escrow';

        try {
            if (!$this->escrowService->refundClient(
                $escrowId,
                $amount,
                $reason,
                (bool) ($validated['allow_full_escrow_refund'] ?? false)
            )) {
                return back()->with('error', $this->escrowService->getLastError() ?? 'Impossible de rembourser cet escrow.');
            }

            return back()->with('success', 'Escrow remboursé côté client.');
        } catch (\Throwable $e) {
            Log::error('Admin escrow refund failed: ' . $e->getMessage(), [
                'escrow_id' => $escrowId,
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', "Le remboursement de l'escrow a échoué.");
        }
    }

    public function adminEscrowReturnEquipment(AdminEscrowReturnEquipmentRequest $request, int $escrowId)
    {
        $this->ensureAdminPaymentAccess();

        $validated = $request->validated();

        $result = $this->escrowService->returnEquipment(
            $escrowId,
            $validated['condition'],
            round((float) ($validated['damage_percent'] ?? 0), 2),
            $validated['notes'] ?? null,
            null
        );

        if (!($result['success'] ?? false)) {
            return back()->with('error', (string) ($result['message'] ?? "Le traitement de la caution a échoué."));
        }

        return back()->with('success', (string) ($result['message'] ?? 'Caution traitée.'));
    }

    public function adminResolveEscrowDispute(AdminResolveDisputeRequest $request, int $disputeId)
    {
        $this->ensureAdminPaymentAccess();

        $validated = $request->validated();

        $refundAmount = round((float) ($validated['refund_amount'] ?? 0), 2);
        if ($validated['resolution'] === 'resolved_partial' && $refundAmount <= 0) {
            return back()->with('error', 'Un montant de remboursement est requis pour une résolution partielle.');
        }

        try {
            if (!$this->escrowService->resolveDispute(
                $disputeId,
                $validated['resolution'],
                $refundAmount,
                (int) auth()->id(),
                $validated['notes'] ?? ''
            )) {
                return back()->with('error', 'Impossible de résoudre ce litige.');
            }

            return back()->with('success', 'Litige escrow résolu.');
        } catch (\Throwable $e) {
            Log::error('Admin escrow dispute resolution failed: ' . $e->getMessage(), [
                'dispute_id' => $disputeId,
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'La résolution du litige a échoué.');
        }
    }

    private function ensureAdminPaymentAccess(): void
    {
        if (!auth()->check() || !auth()->user()->hasRole('administrateur')) {
            abort(403, 'Accès non autorisé. Seuls les administrateurs peuvent gérer les paiements.');
        }
    }

    private function adminPaymentFilterOptions(): array
    {
        return [
            'statuses' => [
                'all' => 'Tous les statuts',
                'pending' => 'En attente',
                'validated' => 'Validés',
                'failed' => 'En échec',
                'refused' => 'Refusés / annulés',
                'refunded' => 'Remboursés',
                'disputed' => 'Litiges',
                'held' => 'Bloqués',
                'released' => 'Libérés',
                'pending_capture' => 'Autorisés à capturer',
                'partial' => 'Partiels',
            ],
            'sources' => [
                'all' => 'Tous les flux',
                'services' => 'Services',
                'equipment' => 'Location matériel',
                'food' => 'Food',
                'refunds' => 'Remboursements',
                'escrow' => 'Escrow',
                'deposits' => 'Acomptes',
                'caution' => 'Cautions',
            ],
            'modes' => [
                'all' => 'Tous les modes',
                'online' => 'Paiement en ligne',
                'cash' => 'Espèces',
                'acompte' => 'Acompte',
                'solde' => 'Solde',
                'escrow' => 'Escrow',
                'caution' => 'Caution',
            ],
        ];
    }

    private function resolveAdminPaymentFilters(Request $request): array
    {
        $options = $this->adminPaymentFilterOptions();

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(array_keys($options['statuses']))],
            'source' => ['nullable', Rule::in(array_keys($options['sources']))],
            'mode' => ['nullable', Rule::in(array_keys($options['modes']))],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'actionable_only' => ['nullable', 'boolean'],
        ]);

        return [
            'search' => trim((string) ($validated['search'] ?? '')),
            'status' => (string) ($validated['status'] ?? 'all'),
            'source' => (string) ($validated['source'] ?? 'all'),
            'mode' => (string) ($validated['mode'] ?? 'all'),
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'actionable_only' => $request->boolean('actionable_only'),
        ];
    }

    private function emptyAdminPaymentStats(): array
    {
        return [
            'gross_volume' => 0,
            'validated_count' => 0,
            'pending_count' => 0,
            'failed_count' => 0,
            'refund_pending_amount' => 0,
            'refund_pending_count' => 0,
            'refunded_amount' => 0,
            'escrow_held_amount' => 0,
            'escrow_held_count' => 0,
            'deposit_amount' => 0,
            'deposit_count' => 0,
            'caution_open_amount' => 0,
            'caution_open_count' => 0,
            'food_pending_capture_amount' => 0,
            'food_pending_capture_count' => 0,
        ];
    }

    private function buildAdminPaymentStats(): array
    {
        $stats = $this->emptyAdminPaymentStats();
        $modernTransactionsCount = 0;

        if (TableExistenceCache::has('payment_transactions')) {
            $validatedStatuses = ['paid', 'completed', 'released'];
            $pendingStatuses = ['pending', 'processing', 'held'];
            $failedStatuses = ['failed', 'cancelled'];
            $refundedStatuses = ['refunded', 'partially_refunded'];
            $modernTransactionsCount = (int) PaymentTransaction::query()->count();

            $stats['gross_volume'] = (float) PaymentTransaction::query()
                ->whereIn('status', array_merge($validatedStatuses, $pendingStatuses, $refundedStatuses))
                ->sum('amount');

            $stats['validated_count'] = (int) PaymentTransaction::query()
                ->whereIn('status', $validatedStatuses)
                ->count();

            $stats['pending_count'] = (int) PaymentTransaction::query()
                ->whereIn('status', $pendingStatuses)
                ->count();

            $stats['failed_count'] = (int) PaymentTransaction::query()
                ->whereIn('status', $failedStatuses)
                ->count();

            $stats['refunded_amount'] = (float) PaymentTransaction::query()
                ->whereIn('status', $refundedStatuses)
                ->sum('amount');

            if ($this->hasTableColumn('payment_transactions', 'type')) {
                $stats['deposit_amount'] = (float) PaymentTransaction::query()
                    ->where('type', 'deposit')
                    ->sum('amount');

                $stats['deposit_count'] = (int) PaymentTransaction::query()
                    ->where('type', 'deposit')
                    ->count();
            }
        }

        if ($modernTransactionsCount === 0 && TableExistenceCache::has('transactions')) {
            $stats['gross_volume'] = (float) DB::table('transactions')
                ->whereIn('status', ['completed', 'pending', 'failed', 'cancelled', 'refunded'])
                ->sum('amount');

            $stats['validated_count'] = (int) DB::table('transactions')
                ->where('status', 'completed')
                ->count();

            $stats['pending_count'] = (int) DB::table('transactions')
                ->where('status', 'pending')
                ->count();

            $stats['failed_count'] = (int) DB::table('transactions')
                ->whereIn('status', ['failed', 'cancelled'])
                ->count();

            $stats['refunded_amount'] = (float) DB::table('transactions')
                ->where('status', 'refunded')
                ->sum('amount');
        }

        if (TableExistenceCache::has('refunds')) {
            $stats['refund_pending_amount'] = (float) PaymentRefund::query()
                ->where('status', 'pending')
                ->sum('amount');

            $stats['refund_pending_count'] = (int) PaymentRefund::query()
                ->where('status', 'pending')
                ->count();
        }

        if (TableExistenceCache::has('escrow_transactions')) {
            $escrowAmountColumn = $this->firstExistingColumn('escrow_transactions', ['total_amount', 'amount']);
            if ($escrowAmountColumn !== null) {
                $stats['escrow_held_amount'] = (float) DB::table('escrow_transactions')
                    ->whereIn('status', ['held', 'partial', 'disputed'])
                    ->sum($escrowAmountColumn);
            }

            $stats['escrow_held_count'] = (int) DB::table('escrow_transactions')
                ->whereIn('status', ['held', 'partial', 'disputed'])
                ->count();
        }

        if (TableExistenceCache::has('equipment_rental_requests') && $this->hasTableColumn('equipment_rental_requests', 'security_deposit')) {
            $depositQuery = EquipmentRentalRequest::query()
                ->where('security_deposit', '>', 0);

            if ($this->hasTableColumn('equipment_rental_requests', 'deposit_status')) {
                $depositQuery->whereIn('deposit_status', ['pending', 'held', 'partial', 'retained']);
            }

            $stats['caution_open_amount'] = (float) $depositQuery->sum('security_deposit');
            $stats['caution_open_count'] = (int) $depositQuery->count();
        }

        if (TableExistenceCache::has('food_orders') && $this->hasTableColumn('food_orders', 'payment_status')) {
            $foodAmountColumn = $this->firstExistingColumn('food_orders', ['amount_held', 'total']);
            if ($foodAmountColumn !== null) {
                $stats['food_pending_capture_amount'] = (float) FoodOrder::query()
                    ->where('payment_status', FoodOrder::PAYMENT_PENDING_CAPTURE)
                    ->sum($foodAmountColumn);
            }

            $stats['food_pending_capture_count'] = (int) FoodOrder::query()
                ->where('payment_status', FoodOrder::PAYMENT_PENDING_CAPTURE)
                ->count();
        }

        return $stats;
    }

    private function buildAdminTransactionsPaginator(array $filters)
    {
        if (!TableExistenceCache::has('payment_transactions')) {
            return $this->emptyPaginator('transactions_page', 20);
        }

        $query = PaymentTransaction::query()
            ->with([
                'user',
                'booking.client.user',
                'booking.prestataire.user',
                'equipmentRentalRequest.client.user',
                'equipmentRentalRequest.prestataire.user',
                'foodOrder.client',
                'foodOrder.prestataire.user',
                'refunds.processedBy',
            ])
            ->orderByDesc('created_at');

        $this->applyAdminDateFilters($query, $filters, 'created_at');
        $this->applyAdminTransactionStatusFilter($query, $filters['status']);
        $this->applyAdminTransactionSourceFilter($query, $filters['source']);
        $this->applyAdminTransactionModeFilter($query, $filters['mode']);

        if ($filters['actionable_only']) {
            $query->whereIn('status', ['pending', 'processing', 'held', 'failed', 'disputed', 'partially_refunded']);
        }

        $search = $filters['search'];
        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                if (ctype_digit($search)) {
                    $searchQuery->orWhere('id', (int) $search);
                }

                $searchQuery->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhere('stripe_payment_intent_id', 'like', "%{$search}%")
                    ->orWhere('stripe_charge_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('booking', function ($bookingQuery) use ($search) {
                        $bookingQuery->where('booking_number', 'like', "%{$search}%")
                            ->orWhereHas('client.user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            })
                            ->orWhereHas('prestataire.user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('equipmentRentalRequest', function ($rentalQuery) use ($search) {
                        $rentalQuery->where('request_number', 'like', "%{$search}%")
                            ->orWhereHas('client.user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            })
                            ->orWhereHas('prestataire.user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('foodOrder', function ($foodQuery) use ($search) {
                        $foodQuery->where('order_number', 'like', "%{$search}%")
                            ->orWhereHas('client', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            })
                            ->orWhereHas('prestataire.user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            });
                    });
            });
        }

        return $query->paginate(20, ['*'], 'transactions_page');
    }

    private function buildAdminLegacyTransactionsPaginator(array $filters)
    {
        if (!TableExistenceCache::has('transactions')) {
            return $this->emptyPaginator('legacy_transactions_page', 15);
        }

        $query = DB::table('transactions as tx')
            ->leftJoin('users', 'tx.user_id', '=', 'users.id')
            ->leftJoin('prestataires', 'tx.prestataire_id', '=', 'prestataires.id')
            ->leftJoin('users as presta_user', 'prestataires.user_id', '=', 'presta_user.id')
            ->leftJoin('bookings', 'tx.booking_id', '=', 'bookings.id')
            ->select([
                'tx.*',
                'users.name as user_name',
                'users.email as user_email',
                'presta_user.name as prestataire_name',
                'presta_user.email as prestataire_email',
                'bookings.booking_number',
            ])
            ->orderByDesc('tx.created_at');

        $this->applyAdminDateFilters($query, $filters, 'tx.created_at');

        if ($filters['actionable_only']) {
            $query->whereIn('tx.status', ['pending', 'failed', 'cancelled']);
        } elseif ($filters['status'] !== 'all') {
            switch ($filters['status']) {
                case 'pending':
                case 'pending_capture':
                case 'held':
                    $query->where('tx.status', 'pending');
                    break;
                case 'validated':
                case 'released':
                    $query->where('tx.status', 'completed');
                    break;
                case 'failed':
                case 'refused':
                    $query->whereIn('tx.status', ['failed', 'cancelled']);
                    break;
                case 'refunded':
                case 'partial':
                    $query->where('tx.status', 'refunded');
                    break;
            }
        }

        switch ($filters['source']) {
            case 'services':
                $query->whereNotNull('tx.booking_id');
                break;
            case 'refunds':
                $query->where('tx.type', 'refund');
                break;
            case 'deposits':
                $query->where('tx.type', 'deposit');
                break;
            case 'food':
            case 'equipment':
            case 'escrow':
            case 'caution':
                $query->whereRaw('1 = 0');
                break;
        }

        switch ($filters['mode']) {
            case 'cash':
                $query->where('tx.payment_method', 'cash');
                break;
            case 'online':
                $query->where(function ($modeQuery) {
                    $modeQuery->whereNull('tx.payment_method')
                        ->orWhere('tx.payment_method', '!=', 'cash');
                });
                break;
        }

        $search = $filters['search'];
        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                if (ctype_digit($search)) {
                    $searchQuery->orWhere('tx.id', (int) $search);
                }

                $searchQuery->orWhere('tx.reference', 'like', "%{$search}%")
                    ->orWhere('tx.notes', 'like', "%{$search}%")
                    ->orWhere('tx.type', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('presta_user.name', 'like', "%{$search}%")
                    ->orWhere('presta_user.email', 'like', "%{$search}%")
                    ->orWhere('bookings.booking_number', 'like', "%{$search}%");
            });
        }

        return $query->paginate(15, ['*'], 'legacy_transactions_page');
    }

    private function buildAdminRefundsPaginator(array $filters)
    {
        if (!TableExistenceCache::has('refunds')) {
            return $this->emptyPaginator('refunds_page', 15);
        }

        $query = PaymentRefund::query()
            ->with([
                'transaction.user',
                'transaction.booking.client.user',
                'transaction.booking.prestataire.user',
                'transaction.equipmentRentalRequest.client.user',
                'transaction.equipmentRentalRequest.prestataire.user',
                'transaction.foodOrder.client',
                'transaction.foodOrder.prestataire.user',
                'user',
                'processedBy',
                'createdBy',
            ])
            ->orderByDesc('created_at');

        $this->applyAdminDateFilters($query, $filters, 'created_at');

        if ($filters['actionable_only']) {
            $query->where('status', 'pending');
        } elseif ($filters['status'] !== 'all') {
            switch ($filters['status']) {
                case 'pending':
                    $query->where('status', 'pending');
                    break;
                case 'validated':
                case 'refunded':
                    $query->where('status', 'completed');
                    break;
                case 'failed':
                case 'refused':
                    $query->where('status', 'rejected');
                    break;
            }
        }

        switch ($filters['source']) {
            case 'services':
                $query->whereHas('transaction', fn ($txQuery) => $txQuery->whereNotNull('booking_id'));
                break;
            case 'equipment':
            case 'caution':
                $query->whereHas('transaction', fn ($txQuery) => $txQuery->whereNotNull('equipment_rental_id'));
                break;
            case 'food':
                $query->whereHas('transaction', fn ($txQuery) => $txQuery->whereNotNull('food_order_id'));
                break;
        }

        switch ($filters['mode']) {
            case 'cash':
                $query->whereHas('transaction', function ($txQuery) {
                    if ($this->hasTableColumn('payment_transactions', 'provider')) {
                        $txQuery->where(function ($providerQuery) {
                            $providerQuery->where('provider', 'cash')
                                ->orWhere('payment_method', 'cash');
                        });
                    } else {
                        $txQuery->where('payment_method', 'cash');
                    }
                });
                break;
            case 'online':
                $query->whereHas('transaction', function ($txQuery) {
                    if ($this->hasTableColumn('payment_transactions', 'provider')) {
                        $txQuery->where(function ($providerQuery) {
                            $providerQuery->whereNull('provider')
                                ->orWhere('provider', '!=', 'cash');
                        });
                    } else {
                        $txQuery->where(function ($providerQuery) {
                            $providerQuery->whereNull('payment_method')
                                ->orWhere('payment_method', '!=', 'cash');
                        });
                    }
                });
                break;
            case 'acompte':
                if ($this->hasTableColumn('payment_transactions', 'type')) {
                    $query->whereHas('transaction', fn ($txQuery) => $txQuery->where('type', 'deposit'));
                }
                break;
        }

        $search = $filters['search'];
        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                if (ctype_digit($search)) {
                    $searchQuery->orWhere('id', (int) $search)
                        ->orWhere('transaction_id', (int) $search);
                }

                $searchQuery->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('transaction', function ($txQuery) use ($search) {
                        $txQuery->where('description', 'like', "%{$search}%")
                            ->orWhere('transaction_id', 'like', "%{$search}%")
                            ->orWhere('stripe_payment_intent_id', 'like', "%{$search}%")
                            ->orWhereHas('booking', function ($bookingQuery) use ($search) {
                                $bookingQuery->where('booking_number', 'like', "%{$search}%");
                            })
                            ->orWhereHas('equipmentRentalRequest', function ($rentalQuery) use ($search) {
                                $rentalQuery->where('request_number', 'like', "%{$search}%");
                            })
                            ->orWhereHas('foodOrder', function ($foodQuery) use ($search) {
                                $foodQuery->where('order_number', 'like', "%{$search}%");
                            });
                    });
            });
        }

        return $query->paginate(15, ['*'], 'refunds_page');
    }

    private function buildAdminEscrowsPaginator(array $filters)
    {
        if (!TableExistenceCache::has('escrow_transactions')) {
            return $this->emptyPaginator('escrows_page', 15);
        }

        $query = DB::table('escrow_transactions as esc')
            ->leftJoin('users as client_user', 'esc.client_id', '=', 'client_user.id')
            ->leftJoin('prestataires as prestataire', 'esc.prestataire_id', '=', 'prestataire.id')
            ->leftJoin('users as presta_user', 'prestataire.user_id', '=', 'presta_user.id')
            ->select([
                'esc.*',
                'client_user.name as client_name',
                'client_user.email as client_email',
                'presta_user.name as prestataire_name',
                'presta_user.email as prestataire_email',
            ])
            ->orderByDesc('esc.created_at');

        if (TableExistenceCache::has('escrow_disputes')) {
            $latestDisputeSubquery = DB::table('escrow_disputes as d1')
                ->select([
                    'd1.id',
                    'd1.escrow_id',
                    'd1.reason',
                    'd1.status',
                    'd1.refund_amount',
                    'd1.description',
                    'd1.created_at',
                ])
                ->whereRaw('d1.id = (select max(d2.id) from escrow_disputes as d2 where d2.escrow_id = d1.escrow_id)');

            $query->leftJoinSub($latestDisputeSubquery, 'latest_dispute', function ($join) {
                $join->on('latest_dispute.escrow_id', '=', 'esc.id');
            })->addSelect([
                'latest_dispute.id as dispute_id',
                'latest_dispute.reason as dispute_reason',
                'latest_dispute.status as dispute_status',
                'latest_dispute.refund_amount as dispute_refund_amount',
                'latest_dispute.description as dispute_description',
                'latest_dispute.created_at as dispute_created_at',
            ]);
        }

        $this->applyAdminDateFilters($query, $filters, 'esc.created_at');

        if ($filters['actionable_only']) {
            $query->where(function ($actionQuery) {
                $actionQuery->whereIn('esc.status', ['pending', 'held', 'partial', 'disputed']);

                if (TableExistenceCache::has('escrow_disputes')) {
                    $actionQuery->orWhereIn('latest_dispute.status', ['open', 'under_review']);
                }
            });
        } elseif ($filters['status'] !== 'all') {
            switch ($filters['status']) {
                case 'pending':
                    $query->whereIn('esc.status', ['pending', 'held', 'partial']);
                    break;
                case 'validated':
                case 'released':
                    $query->where('esc.status', 'released');
                    break;
                case 'refunded':
                    $query->where('esc.status', 'refunded');
                    break;
                case 'disputed':
                    $query->where(function ($disputeQuery) {
                        $disputeQuery->where('esc.status', 'disputed');
                        if (TableExistenceCache::has('escrow_disputes')) {
                            $disputeQuery->orWhereIn('latest_dispute.status', ['open', 'under_review']);
                        }
                    });
                    break;
                case 'held':
                    $query->where('esc.status', 'held');
                    break;
                case 'refused':
                case 'failed':
                    $query->where('esc.status', 'cancelled');
                    break;
                case 'partial':
                    $query->where('esc.status', 'partial');
                    break;
            }
        }

        switch ($filters['source']) {
            case 'services':
                $query->where('esc.escrowable_type', 'like', '%Booking%');
                break;
            case 'equipment':
            case 'caution':
                $query->where('esc.escrowable_type', 'like', '%EquipmentRental%');
                break;
        }

        switch ($filters['mode']) {
            case 'caution':
                $query->where('esc.deposit_amount', '>', 0);
                break;
            case 'online':
                $query->whereNotNull('esc.stripe_payment_intent_id');
                break;
            case 'cash':
                $query->whereRaw('1 = 0');
                break;
        }

        $search = $filters['search'];
        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                if (ctype_digit($search)) {
                    $searchQuery->orWhere('esc.id', (int) $search)
                        ->orWhere('esc.escrowable_id', (int) $search);
                }

                $searchQuery->orWhere('client_user.name', 'like', "%{$search}%")
                    ->orWhere('client_user.email', 'like', "%{$search}%")
                    ->orWhere('presta_user.name', 'like', "%{$search}%")
                    ->orWhere('presta_user.email', 'like', "%{$search}%")
                    ->orWhere('esc.stripe_payment_intent_id', 'like', "%{$search}%")
                    ->orWhere('esc.stripe_transfer_id', 'like', "%{$search}%")
                    ->orWhere('esc.notes', 'like', "%{$search}%");
            });
        }

        return $query->paginate(15, ['*'], 'escrows_page');
    }

    private function buildAdminFoodActionQueue(array $filters)
    {
        if (!TableExistenceCache::has('food_orders') || $filters['source'] === 'services' || $filters['source'] === 'equipment' || $filters['source'] === 'caution') {
            return collect();
        }

        $query = FoodOrder::query()
            ->with(['client', 'prestataire.user'])
            ->orderByDesc('created_at');

        $this->applyAdminDateFilters($query, $filters, 'created_at');

        if ($filters['actionable_only']) {
            $query->where(function ($actionQuery) {
                $actionQuery->where('payment_status', FoodOrder::PAYMENT_PENDING_CAPTURE)
                    ->orWhere(function ($paidQuery) {
                        $paidQuery->where('payment_status', FoodOrder::PAYMENT_PAID)
                            ->whereIn('escrow_status', [
                                FoodOrder::ESCROW_PENDING,
                                FoodOrder::ESCROW_HELD,
                                FoodOrder::ESCROW_PARTIAL_REFUND,
                            ]);
                    });
            });
        } elseif ($filters['status'] !== 'all') {
            switch ($filters['status']) {
                case 'pending_capture':
                    $query->where('payment_status', FoodOrder::PAYMENT_PENDING_CAPTURE);
                    break;
                case 'pending':
                    $query->whereIn('payment_status', [FoodOrder::PAYMENT_PENDING, FoodOrder::PAYMENT_PENDING_CAPTURE]);
                    break;
                case 'validated':
                    $query->where('payment_status', FoodOrder::PAYMENT_PAID);
                    break;
                case 'refunded':
                    $query->where(function ($refundQuery) {
                        $refundQuery->where('payment_status', FoodOrder::PAYMENT_REFUNDED)
                            ->orWhereIn('escrow_status', [FoodOrder::ESCROW_REFUNDED, FoodOrder::ESCROW_PARTIAL_REFUND]);
                    });
                    break;
                case 'refused':
                case 'failed':
                    $query->whereIn('status', ['rejected', FoodOrder::STATUS_CANCELLED]);
                    break;
            }
        }

        switch ($filters['mode']) {
            case 'online':
                $query->where(function ($modeQuery) {
                    $modeQuery->whereNull('payment_method')
                        ->orWhere('payment_method', '!=', 'cash');
                });
                break;
            case 'cash':
                $query->where('payment_method', 'cash');
                break;
            case 'escrow':
                $query->whereIn('escrow_status', [
                    FoodOrder::ESCROW_PENDING,
                    FoodOrder::ESCROW_HELD,
                    FoodOrder::ESCROW_RELEASED,
                    FoodOrder::ESCROW_REFUNDED,
                    FoodOrder::ESCROW_PARTIAL_REFUND,
                ]);
                break;
        }

        $search = $filters['search'];
        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                if (ctype_digit($search)) {
                    $searchQuery->orWhere('id', (int) $search);
                }

                $searchQuery->orWhere('order_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('prestataire.user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $query->limit(12)->get();
    }

    private function buildAdminEquipmentDepositQueue(array $filters)
    {
        if (!TableExistenceCache::has('equipment_rental_requests') || !in_array($filters['source'], ['all', 'equipment', 'caution'], true)) {
            return collect();
        }

        $query = EquipmentRentalRequest::query()
            ->with(['client.user', 'prestataire.user', 'equipment', 'rental'])
            ->where('security_deposit', '>', 0)
            ->orderByDesc('created_at');

        $this->applyAdminDateFilters($query, $filters, 'created_at');

        if ($this->hasTableColumn('equipment_rental_requests', 'deposit_status')) {
            if ($filters['actionable_only']) {
                $query->whereIn('deposit_status', ['pending', 'held', 'partial']);
            } elseif ($filters['status'] !== 'all') {
                switch ($filters['status']) {
                    case 'pending':
                    case 'held':
                        $query->whereIn('deposit_status', ['pending', 'held']);
                        break;
                    case 'validated':
                    case 'released':
                        $query->where('deposit_status', 'returned');
                        break;
                    case 'refused':
                        $query->where('deposit_status', 'retained');
                        break;
                    case 'partial':
                        $query->where('deposit_status', 'partial');
                        break;
                }
            }
        }

        $search = $filters['search'];
        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                if (ctype_digit($search)) {
                    $searchQuery->orWhere('id', (int) $search);
                }

                $searchQuery->orWhere('request_number', 'like', "%{$search}%")
                    ->orWhereHas('equipment', function ($equipmentQuery) use ($search) {
                        $equipmentQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('client.user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('prestataire.user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $requests = $query->limit(12)->get();
        if ($requests->isEmpty() || !TableExistenceCache::has('escrow_transactions')) {
            return $requests->map(fn (EquipmentRentalRequest $rentalRequest) => [
                'request' => $rentalRequest,
                'escrow' => null,
            ]);
        }

        $escrows = DB::table('escrow_transactions')
            ->where('escrowable_type', 'like', '%EquipmentRentalRequest%')
            ->whereIn('escrowable_id', $requests->pluck('id')->all())
            ->orderByDesc('id')
            ->get()
            ->unique('escrowable_id')
            ->keyBy('escrowable_id');

        return $requests->map(fn (EquipmentRentalRequest $rentalRequest) => [
            'request' => $rentalRequest,
            'escrow' => $escrows->get($rentalRequest->id),
        ]);
    }

    private function buildAdminDisputeQueue(array $filters)
    {
        if (!TableExistenceCache::has('escrow_disputes') || !TableExistenceCache::has('escrow_transactions')) {
            return collect();
        }

        $query = DB::table('escrow_disputes as dispute')
            ->join('escrow_transactions as esc', 'esc.id', '=', 'dispute.escrow_id')
            ->leftJoin('users as client_user', 'esc.client_id', '=', 'client_user.id')
            ->leftJoin('prestataires as prestataire', 'esc.prestataire_id', '=', 'prestataire.id')
            ->leftJoin('users as presta_user', 'prestataire.user_id', '=', 'presta_user.id')
            ->select([
                'dispute.*',
                'esc.status as escrow_status',
                'esc.escrowable_type',
                'esc.escrowable_id',
                'esc.amount',
                'esc.deposit_amount',
                'esc.platform_fee',
                'esc.released_amount',
                'esc.stripe_payment_intent_id',
                'client_user.name as client_name',
                'client_user.email as client_email',
                'presta_user.name as prestataire_name',
                'presta_user.email as prestataire_email',
            ])
            ->whereIn('dispute.status', ['open', 'under_review'])
            ->orderByDesc('dispute.created_at');

        $this->applyAdminDateFilters($query, $filters, 'dispute.created_at');

        switch ($filters['source']) {
            case 'services':
                $query->where('esc.escrowable_type', 'like', '%Booking%');
                break;
            case 'equipment':
            case 'caution':
                $query->where('esc.escrowable_type', 'like', '%EquipmentRental%');
                break;
            case 'food':
                return collect();
        }

        $search = $filters['search'];
        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                if (ctype_digit($search)) {
                    $searchQuery->orWhere('dispute.id', (int) $search)
                        ->orWhere('dispute.escrow_id', (int) $search);
                }

                $searchQuery->orWhere('dispute.description', 'like', "%{$search}%")
                    ->orWhere('client_user.name', 'like', "%{$search}%")
                    ->orWhere('client_user.email', 'like', "%{$search}%")
                    ->orWhere('presta_user.name', 'like', "%{$search}%")
                    ->orWhere('presta_user.email', 'like', "%{$search}%");
            });
        }

        return $query->limit(10)->get();
    }

    private function applyAdminDateFilters($query, array $filters, string $column = 'created_at'): void
    {
        if (!empty($filters['date_from'])) {
            $query->whereDate($column, '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate($column, '<=', $filters['date_to']);
        }
    }

    private function applyAdminTransactionStatusFilter($query, string $status): void
    {
        if ($status === 'all') {
            return;
        }

        switch ($status) {
            case 'pending':
                $query->whereIn('status', ['pending', 'processing', 'held']);
                return;
            case 'validated':
                $query->whereIn('status', ['paid', 'completed', 'released']);
                return;
            case 'failed':
            case 'refused':
                $query->whereIn('status', ['failed', 'cancelled']);
                return;
            case 'refunded':
                $query->whereIn('status', ['refunded', 'partially_refunded']);
                return;
            case 'disputed':
                $query->where('status', 'disputed');
                return;
            case 'held':
                $query->where('status', 'held');
                return;
            case 'released':
                $query->whereIn('status', ['released', 'completed']);
                return;
            case 'pending_capture':
                $query->where(function ($pendingCaptureQuery) {
                    $pendingCaptureQuery->where('status', 'pending')
                        ->orWhereHas('foodOrder', function ($foodQuery) {
                            $foodQuery->where('payment_status', FoodOrder::PAYMENT_PENDING_CAPTURE);
                        });
                });
                return;
            case 'partial':
                $query->where('status', 'partially_refunded');
                return;
            default:
                $query->where('status', $status);
                return;
        }
    }

    private function applyAdminTransactionSourceFilter($query, string $source): void
    {
        switch ($source) {
            case 'services':
                $query->whereNotNull('booking_id');
                break;
            case 'equipment':
            case 'caution':
                $query->whereNotNull('equipment_rental_id');
                break;
            case 'food':
                $query->whereNotNull('food_order_id');
                break;
            case 'refunds':
                if ($this->hasTableColumn('payment_transactions', 'type')) {
                    $query->where('type', 'refund');
                } else {
                    $query->whereIn('status', ['refunded', 'partially_refunded']);
                }
                break;
            case 'deposits':
                if ($this->hasTableColumn('payment_transactions', 'type')) {
                    $query->where('type', 'deposit');
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;
        }
    }

    private function applyAdminTransactionModeFilter($query, string $mode): void
    {
        switch ($mode) {
            case 'online':
                if ($this->hasTableColumn('payment_transactions', 'provider')) {
                    $query->where(function ($modeQuery) {
                        $modeQuery->whereNull('provider')
                            ->orWhere('provider', '!=', 'cash');
                    });
                } else {
                    $query->where(function ($modeQuery) {
                        $modeQuery->whereNull('payment_method')
                            ->orWhere('payment_method', '!=', 'cash');
                    });
                }
                break;
            case 'cash':
                if ($this->hasTableColumn('payment_transactions', 'provider')) {
                    $query->where(function ($modeQuery) {
                        $modeQuery->where('provider', 'cash')
                            ->orWhere('payment_method', 'cash');
                    });
                } else {
                    $query->where('payment_method', 'cash');
                }
                break;
            case 'acompte':
                if ($this->hasTableColumn('payment_transactions', 'type')) {
                    $query->where('type', 'deposit');
                }
                break;
            case 'solde':
                if ($this->hasTableColumn('payment_transactions', 'type')) {
                    $query->where('type', 'balance');
                }
                break;
            case 'escrow':
                $query->whereIn('status', ['held', 'released', 'disputed', 'partially_refunded']);
                break;
            case 'caution':
                $query->whereNotNull('equipment_rental_id');
                break;
        }
    }

    private function safeAdminPaymentsSection(string $section, callable $resolver, $fallback, array &$pageWarnings)
    {
        $labels = [
            'stats' => 'statistiques globales',
            'transactions' => 'transactions',
            'legacyTransactions' => 'transactions legacy',
            'refundRequests' => 'remboursements',
            'escrows' => 'escrows',
            'foodActionQueue' => 'file food',
            'equipmentDepositQueue' => 'cautions materiel',
            'disputeQueue' => 'litiges',
        ];

        try {
            return $resolver();
        } catch (\Throwable $e) {
            Log::warning('Admin payments section failed', [
                'section' => $section,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            $pageWarnings[] = $labels[$section] ?? $section;

            return $fallback;
        }
    }

    private function emptyPaginator(string $pageName, int $perPage)
    {
        return new \Illuminate\Pagination\LengthAwarePaginator(
            collect(),
            0,
            $perPage,
            (int) request()->query($pageName, 1),
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]
        );
    }

    private function hasTableColumn(string $table, string $column): bool
    {
        static $columnCache = [];

        $cacheKey = $table . '.' . $column;
        if (array_key_exists($cacheKey, $columnCache)) {
            return $columnCache[$cacheKey];
        }

        return $columnCache[$cacheKey] = TableExistenceCache::has($table) && Schema::hasColumn($table, $column);
    }

    private function firstExistingColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if ($this->hasTableColumn($table, $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Confirm cash payment - marks booking for cash payment at service time
     */
    public function confirmCashPayment(Request $request, Booking $booking)
    {
        $this->authorize('pay', $booking);

        $booking->loadMissing('service');

        $requirement = $this->getBookingPaymentRequirement($booking);
        if ($requirement !== 'none') {
            return back()->with('error', "Le prestataire exige un paiement en ligne ({$requirement}) : le paiement en espèces n'est pas autorisé pour cette annonce.");
        }

        // Platform setting may require online payment before booking.
        $requirePaymentBeforeBooking = false;
        try {
            $settings = function_exists('get_payment_settings') ? get_payment_settings() : [];
            $requirePaymentBeforeBooking = (bool) ($settings['require_payment_before_booking'] ?? false);
        } catch (\Throwable $e) {
            $requirePaymentBeforeBooking = false;
        }

        if ($requirePaymentBeforeBooking) {
            return back()->with('error', "Le paiement en ligne est requis par la plateforme : le paiement en espèces n'est pas autorisé pour cette réservation.");
        }

        if (in_array($booking->payment_status, ['paid', 'deposit_paid'], true)) {
            return back()->with('error', 'Cette réservation a déjà un paiement enregistré.');
        }

        $alreadyPendingCash = PaymentTransaction::where('booking_id', $booking->id)
            ->where('provider', 'cash')
            ->where('status', 'pending_cash')
            ->exists();
        if ($alreadyPendingCash) {
            return back()->with('info', 'Un paiement en espèces est déjà en attente pour cette réservation.');
        }

        // Check if cash payment is enabled
        $cashEnabled = function_exists('payment_method_enabled') 
            ? payment_method_enabled('cash') 
            : (get_setting('payment_cash_enabled', false));

        if (!$cashEnabled) {
            return back()->with('error', 'Le paiement en espèces n\'est pas disponible.');
        }

        try {
            // Create a pending transaction for cash
            $transaction = PaymentTransaction::systemCreate([
                'booking_id' => $booking->id,
                'user_id' => auth()->id(),
                'amount' => $booking->total_price,
                'type' => 'payment',
                'currency' => 'eur',
                'status' => 'pending_cash',
                'provider' => 'cash',
                'payment_method' => 'cash',
                'description' => 'Paiement en espèces prévu lors de la prestation',
                'metadata' => ['payment_type' => 'cash', 'confirmed_at' => now()->toIso8601String()],
            ]);

            // Update booking to cash pending (NOT confirmed - prestataire must confirm first)
            // SÉCURITÉ M7: Ne pas confirmer le booking automatiquement pour les paiements espèces
            // Le prestataire doit confirmer qu'il accepte le paiement en espèces
            $booking->update([
                'payment_status' => 'cash_pending',
                // Ne PAS changer le status à 'confirmed' ici
                // Le booking reste en 'pending' jusqu'à ce que le prestataire confirme
            ]);

            // Notify prestataire
            try {
                $booking->prestataire->user->notify(new \App\Notifications\BookingCashPaymentConfirmed($booking));
            } catch (\Exception $e) {
                \Log::warning('Failed to notify prestataire of cash payment: ' . $e->getMessage());
            }

            return redirect()->route('client.bookings.index')
                ->with('success', 'Réservation confirmée ! Vous paierez ' . number_format($booking->total_price, 2) . ' € en espèces lors de la prestation.');
        } catch (\Exception $e) {
            \Log::error('confirmCashPayment failed: ' . $e->getMessage(), ['user' => auth()->id()]);
            return back()->with('error', 'Erreur lors de la confirmation du paiement.');
        }
    }

    /**
     * Payment success callback page (after Stripe redirect)
     */
    public function paymentSuccess(Request $request, Booking $booking)
    {
        $this->authorize('pay', $booking);

        $booking->loadMissing('service.prestataire', 'prestataire');

        // Stripe redirect callback: no DB writes here.
        // Finalization is done by POST /confirm or by webhook reconciliation.
        $paymentIntentId = $request->query('payment_intent');
        if (empty($paymentIntentId)) {
            return redirect()->route('client.bookings.index')
                ->with('info', 'Retour de paiement reçu. Finalisation en cours.');
        }

        try {
            $connectedAccountId = $booking->service?->prestataire?->stripe_account_id
                ?: $booking->prestataire?->stripe_account_id;

            $paymentIntent = null;
            try {
                $paymentIntent = $this->stripeService->retrievePaymentIntent($paymentIntentId, null);
            } catch (\Throwable $platformError) {
                $paymentIntent = $this->stripeService->retrievePaymentIntent($paymentIntentId, $connectedAccountId);
            }

            $paymentType = (string) (($this->paymentIntentMetadata($paymentIntent)['payment_type'] ?? 'full'));
            if (!in_array($paymentType, ['full', 'deposit', 'balance'], true)) {
                $paymentType = 'full';
            }

            $this->assertPaymentIntentMatchesBooking($paymentIntent, $booking, $paymentType);

            if (!in_array((string) $paymentIntent->status, ['succeeded', 'requires_capture'], true)) {
                return redirect()->route('client.bookings.index')
                    ->with('info', 'Paiement en cours de traitement. La confirmation arrivera automatiquement.');
            }
        } catch (\Throwable $e) {
            Log::warning('paymentSuccess validation failed', [
                'booking_id' => $booking->id,
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('client.bookings.index')
                ->with('error', 'Retour de paiement invalide.');
        }

        return redirect()->route('client.bookings.index')
            ->with('success', 'Paiement validé. Finalisation en cours.');
    }
}
