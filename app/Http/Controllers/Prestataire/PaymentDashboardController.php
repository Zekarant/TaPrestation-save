<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\EquipmentRentalRequest;
use App\Models\FoodOrder;
use App\Models\PaymentAllocation;
use App\Models\PaymentTransaction;
use App\Models\UrgentSalePurchase;
use App\Support\PaymentMetadataNormalizer;
use App\Services\CommissionService;
use App\Services\EquipmentRentalPaymentSyncService;
use App\Services\StripePaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Support\TableExistenceCache;
class PaymentDashboardController extends Controller
{
    /**
     * Dashboard principal des paiements prestataire
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $prestataire = $user->prestataire;

        if (function_exists('payment_feature_enabled') && !payment_feature_enabled()) {
            return redirect()->route('prestataire.dashboard')
                ->with('info', 'Les paiements en ligne sont désactivés pour le moment.');
        }

        if (!$prestataire) {
            return redirect()->route('prestataire.dashboard')
                ->with('error', 'Profil prestataire non trouvé.');
        }

        try {
            app(EquipmentRentalPaymentSyncService::class)->syncForPrestataire($prestataire);
        } catch (\Throwable $e) {
            Log::warning('Payment dashboard rental payment sync warning', [
                'prestataire_id' => $prestataire->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        // Filtres
        $filters = [
            'type' => $request->get('type', 'all'),
            'status' => $request->get('status', 'all'),
            'period' => $request->get('period', 'all'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'search' => $request->get('search'),
        ];

        // Collecter toutes les sources de revenus
        $allPayments = collect();

        // 1. Réservations de services (bookings)
        $bookings = $this->getBookingPayments($prestataire, $filters);
        $allPayments = $allPayments->merge($bookings);

        // 2. Locations d'équipements
        $equipmentRentals = $this->getEquipmentRentalPayments($prestataire, $filters);
        $allPayments = $allPayments->merge($equipmentRentals);

        // 3. Ventes urgentes
        $urgentSales = $this->getUrgentSalePayments($prestataire, $filters);
        $allPayments = $allPayments->merge($urgentSales);

        // 4. Commandes alimentaires (food)
        $foodOrders = $this->getFoodOrderPayments($prestataire, $filters);
        $allPayments = $allPayments->merge($foodOrders);

        // 5. Récupérer aussi les paiements depuis Stripe pour compléter
        // (paiements qui n'ont peut-être pas de correspondance locale)
        if (!empty($prestataire->stripe_account_id)) {
            $stripePayments = $this->getStripePayments($prestataire, $filters);
            
            // Ne pas ajouter les doublons - vérifier par montant et date approximative
            $existingIds = $allPayments->pluck('id')->toArray();
            $stripePayments = $stripePayments->filter(function ($payment) use ($existingIds) {
                // Garder seulement les paiements Stripe qui ne sont pas déjà dans la liste
                return !in_array($payment['id'], $existingIds);
            });
            
            $allPayments = $allPayments->merge($stripePayments);
        }

        // Trier par date décroissante
        $allPayments = $allPayments->sortByDesc('date');

        // Calculer les statistiques
        $stats = $this->calculateStats($allPayments, $prestataire);

        // Paginer manuellement
        $page = $request->get('page', 1);
        $perPage = 20;
        $total = $allPayments->count();
        $payments = $allPayments->forPage($page, $perPage)->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $payments,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Stripe Connect info - vérifier si le compte est actif
        $stripeConnected = !empty($prestataire->stripe_account_id);
        $stripeAccountId = $prestataire->stripe_account_id ?? null;

        return view('prestataire.payments.dashboard-unified', [
            'payments' => $paginator,
            'stats' => $stats,
            'filters' => $filters,
            'stripeConnected' => $stripeConnected,
            'stripeAccountId' => $stripeAccountId,
            'prestataire' => $prestataire,
        ]);
    }

    /**
     * Récupérer les paiements des réservations
     */
    private function getBookingPayments($prestataire, $filters)
    {
        $query = Booking::with(['client.user', 'service', 'paymentTransaction'])
            ->whereHas('service', function ($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id);
            })
            ->whereIn('status', ['confirmed', 'completed', 'paid']);

        // Appliquer les filtres de période
        $query = $this->applyDateFilters($query, $filters);

        // Filtre par type
        if ($filters['type'] !== 'all' && $filters['type'] !== 'booking') {
            return collect();
        }

        return $query->get()->map(function ($booking) {
            $transaction = $booking->paymentTransaction;
            $amount = $transaction->amount ?? $booking->total_price ?? 0;
            $platformCommission = CommissionService::feeAmount($amount, 'booking', 'prestataire');
            $stripeFee = CommissionService::stripeFeesAmount($amount);
            $totalDeductions = $platformCommission + $stripeFee;
            $netAmount = round($amount - $totalDeductions, 2);

            $clientUser = $booking->client?->user;

            return [
                'id' => 'booking_' . $booking->id,
                'type' => 'booking',
                'type_label' => 'Réservation Service',
                'type_icon' => 'calendar',
                'type_color' => 'blue',
                'reference' => $booking->booking_number ?? 'RES-' . $booking->id,
                'title' => $booking->service->name ?? 'Service',
                'client_name' => $clientUser->name ?? 'Client',
                'client_email' => $clientUser->email ?? '',
                'amount' => $amount,
                'platform_commission' => $platformCommission,
                'platform_commission_rate' => CommissionService::ratePercent('booking', 'prestataire'),
                'stripe_fee' => $stripeFee,
                'total_deductions' => $totalDeductions,
                'commission' => $totalDeductions, // Pour compatibilité
                'net_amount' => $netAmount,
                'status' => $this->mapStatus($booking->status, $transaction->status ?? null),
                'payment_type' => $this->getPaymentType($booking),
                'date' => $booking->created_at,
                'paid_at' => $transaction->paid_at ?? $booking->updated_at,
                'booking' => $booking,
                'transaction' => $transaction,
            ];
        });
    }

    /**
     * Récupérer les paiements de locations d'équipement
     */
    private function getEquipmentRentalPayments($prestataire, $filters)
    {
        if (!TableExistenceCache::has('equipment_rental_requests')) {
            return collect();
        }

        // Filtre par type
        if ($filters['type'] !== 'all' && $filters['type'] !== 'equipment') {
            return collect();
        }

        $query = EquipmentRentalRequest::with(['client.user', 'equipment', 'equipment.prestataire', 'rental'])
            ->whereHas('equipment', function ($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id);
            });

        $query = $this->applyDateFilters($query, $filters);

        return $query->get()->map(function ($rental) {
            $paymentContext = $this->resolveEquipmentPaymentContext($rental);
            $transaction = $paymentContext['transaction'] ?? null;
            $escrow = $paymentContext['escrow'] ?? null;
            $paymentType = strtolower((string) ($paymentContext['payment_type'] ?? 'full'));

            if (!$transaction && !$escrow) {
                return null;
            }

            $escrowRow = is_object($escrow) ? (array) $escrow : [];
            $escrowMeta = [];
            try {
                $rawMeta = !empty($escrowRow['metadata'] ?? null)
                    ? (json_decode((string) $escrowRow['metadata'], true) ?: [])
                    : [];
                $escrowMeta = PaymentMetadataNormalizer::normalize(is_array($rawMeta) ? $rawMeta : []);
            } catch (\Throwable $e) {
                $escrowMeta = [];
            }

            $requestAmount = (float) ($rental->total_amount ?? $rental->final_amount ?? 0);
            $securityDeposit = (float) ($rental->equipment->security_deposit ?? $rental->security_deposit ?? ($escrowRow['deposit_amount'] ?? 0));
            if ($securityDeposit <= 0) {
                $securityDeposit = (float) ($escrowMeta['security_deposit'] ?? 0);
            }

            // Montant "vente location" (hors caution) = base de calcul réelle du net prestataire.
            $rentalAmount = (float) ($escrowRow['total_amount'] ?? ($escrowRow['amount'] ?? 0));
            if ($rentalAmount <= 0) {
                $rentalAmount = (float) ($escrowMeta['rental_amount'] ?? ($escrowMeta['breakdown']['client_pays'] ?? 0));
            }
            if ($rentalAmount <= 0) {
                $rentalAmount = (float) $requestAmount;
            }

            // Montant débité client (location + caution), utile en info mais pas pour le net prestataire.
            $grossAmount = (float) ($transaction->amount ?? 0);
            if ($grossAmount <= 0) {
                $grossAmount = $rentalAmount + $securityDeposit;
            }
            if ($grossAmount <= 0) {
                return null;
            }

            if ($rentalAmount <= 0) {
                $rentalAmount = max(0, $grossAmount - $securityDeposit);
            }
            if ($rentalAmount <= 0) {
                $rentalAmount = $grossAmount;
            }

            $platformCommission = (float) ($escrowRow['commission_amount'] ?? ($escrowRow['platform_fee'] ?? 0));
            if ($platformCommission <= 0) {
                $platformCommission = (float) ($escrowMeta['platform_commission'] ?? ($escrowMeta['breakdown']['platform_commission'] ?? 0));
            }
            if ($platformCommission <= 0) {
                $platformCommission = CommissionService::feeAmount($rentalAmount, 'equipment', 'prestataire');
            }

            $stripeFee = (float) ($escrowRow['stripe_fees'] ?? 0);
            if ($stripeFee <= 0) {
                $stripeFee = (float) ($escrowMeta['stripe_fees'] ?? ($escrowMeta['breakdown']['stripe_fees'] ?? 0));
            }
            if ($stripeFee <= 0) {
                $stripeFee = CommissionService::stripeFeesAmount($rentalAmount);
            }

            $totalDeductions = round($platformCommission + $stripeFee, 2);

            $netAmount = (float) ($escrowRow['prestataire_amount'] ?? 0);
            if ($netAmount <= 0) {
                $netAmount = (float) ($escrowMeta['prestataire_receives'] ?? ($escrowMeta['breakdown']['prestataire_receives'] ?? 0));
            }
            if ($netAmount <= 0) {
                $netAmount = round($rentalAmount - $totalDeductions, 2);
            }

            $platformCommissionRate = (float) ($escrowRow['commission_rate'] ?? 0);
            if ($platformCommissionRate <= 0) {
                $platformCommissionRate = (float) ($escrowMeta['commission_rate'] ?? 0);
            }
            if ($platformCommissionRate <= 0) {
                $platformCommissionRate = CommissionService::ratePercent('equipment', 'prestataire');
            }

            $clientUser = $rental->client?->user;
            $status = $this->mapEquipmentPaymentStatus($paymentContext, $rental);

            $paidAt = $transaction->paid_at ?? null;
            if (!$paidAt) {
                $paidAtCandidate = $escrowRow['paid_at'] ?? ($escrowRow['held_at'] ?? ($escrowRow['created_at'] ?? null));
                if (!empty($paidAtCandidate)) {
                    try {
                        $paidAt = Carbon::parse($paidAtCandidate);
                    } catch (\Throwable $e) {
                        $paidAt = null;
                    }
                }
            }

            if (!in_array($paymentType, ['deposit', 'balance', 'full', 'payment', 'refund'], true)) {
                $paymentType = 'full';
            }
            if ($paymentType === 'payment') {
                $paymentType = 'full';
            }

            return [
                'id' => 'equipment_' . $rental->id,
                'type' => 'equipment',
                'type_label' => 'Location Équipement',
                'type_icon' => 'wrench',
                'type_color' => 'purple',
                'reference' => $rental->request_number ?? ('LOC-' . $rental->id),
                'title' => $rental->equipment->name ?? 'Équipement',
                'client_name' => $clientUser->name ?? 'Client',
                'client_email' => $clientUser->email ?? '',
                'amount' => $rentalAmount,
                'gross_amount' => $grossAmount,
                'security_deposit' => $securityDeposit,
                'platform_commission' => $platformCommission,
                'platform_commission_rate' => $platformCommissionRate,
                'stripe_fee' => $stripeFee,
                'total_deductions' => $totalDeductions,
                'commission' => $totalDeductions,
                'net_amount' => $netAmount,
                'status' => $status,
                'payment_type' => $paymentType,
                'date' => $rental->created_at,
                'paid_at' => $paidAt ?? $rental->updated_at,
                'rental' => $rental,
                'transaction' => $transaction,
                'escrow' => $escrow,
            ];
        })->filter()->values();
    }

    private function resolveEquipmentPaymentContext(EquipmentRentalRequest $rental): array
    {
        $transaction = $this->findLatestEquipmentTransaction($rental);
        $escrow = $this->findLatestEquipmentEscrow($rental);

        if (!$transaction && !$escrow) {
            $transaction = $this->promotePendingEquipmentTransactionFromStripe($rental);
        }

        $paymentType = null;

        if ($transaction) {
            $meta = PaymentMetadataNormalizer::normalize((array) ($transaction->metadata ?? []));
            $paymentType = (string) ($meta['payment_type'] ?? ($meta['tx_type'] ?? $transaction->type ?? ''));
        }

        if ($escrow) {
            $escrowRow = (array) $escrow;
            $escrowMeta = PaymentMetadataNormalizer::normalize((array) json_decode((string) ($escrowRow['metadata'] ?? ''), true));
            if (!empty($escrowMeta['payment_type'])) {
                $paymentType = (string) $escrowMeta['payment_type'];
            }
            if (empty($paymentType) && !empty($escrowMeta['tx_type'])) {
                $paymentType = (string) $escrowMeta['tx_type'];
            }
        }

        return [
            'transaction' => $transaction,
            'escrow' => $escrow,
            'payment_type' => $paymentType,
        ];
    }

    private function retrievePaymentIntentForEquipmentRental(EquipmentRentalRequest $rental, string $paymentIntentId): ?object
    {
        try {
            return app(StripePaymentService::class)->retrievePaymentIntent($paymentIntentId, null);
        } catch (\Throwable $platformError) {
            $connectedAccountId = $rental->prestataire?->stripe_account_id ?? $rental->equipment?->prestataire?->stripe_account_id;
            if (!empty($connectedAccountId)) {
                try {
                    return app(StripePaymentService::class)->retrievePaymentIntent($paymentIntentId, $connectedAccountId);
                } catch (\Throwable $connectedError) {
                    Log::warning('Dashboard payments: unable to retrieve equipment PaymentIntent on platform and connected account', [
                        'rental_request_id' => $rental->id,
                        'payment_intent_id' => $paymentIntentId,
                        'platform_error' => $platformError->getMessage(),
                        'connected_error' => $connectedError->getMessage(),
                    ]);
                }
            } else {
                Log::warning('Dashboard payments: unable to retrieve equipment PaymentIntent on platform account', [
                    'rental_request_id' => $rental->id,
                    'payment_intent_id' => $paymentIntentId,
                    'platform_error' => $platformError->getMessage(),
                ]);
            }
        }

        return null;
    }

    private function findLatestPendingEquipmentTransaction(EquipmentRentalRequest $rental): ?PaymentTransaction
    {
        $requestId = (int) $rental->id;
        $requestNumber = trim((string) ($rental->request_number ?? ''));
        $statusCandidates = ['pending', 'processing'];

        $direct = PaymentTransaction::query()
            ->whereIn('status', $statusCandidates)
            ->where('equipment_rental_id', $requestId)
            ->latest('id')
            ->first();
        if ($direct) {
            return $direct;
        }

        $recent = PaymentTransaction::query()
            ->whereIn('status', $statusCandidates)
            ->whereNotNull('metadata')
            ->latest('id')
            ->limit(400)
            ->get();

        foreach ($recent as $transaction) {
            $meta = PaymentMetadataNormalizer::normalize((array) ($transaction->metadata ?? []));
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

        return null;
    }

    private function promotePendingEquipmentTransactionFromStripe(EquipmentRentalRequest $rental): ?PaymentTransaction
    {
        try {
            $pending = $this->findLatestPendingEquipmentTransaction($rental);
            if (!$pending) {
                return null;
            }

            $piId = trim((string) ($pending->stripe_payment_intent_id ?? $pending->transaction_id ?? ''));
            if ($piId === '') {
                return null;
            }

            $paymentIntent = $this->retrievePaymentIntentForEquipmentRental($rental, $piId);
            if (!$paymentIntent) {
                return null;
            }

            $piStatus = strtolower((string) ($paymentIntent->status ?? ''));
            if (!in_array($piStatus, ['succeeded', 'requires_capture'], true)) {
                if ($piStatus === 'canceled' && in_array((string) $pending->status, ['pending', 'processing'], true)) {
                    $pending->status = 'failed';
                    $pending->metadata = array_merge(
                        PaymentMetadataNormalizer::normalize((array) ($pending->metadata ?? [])),
                        ['reconciled_from' => 'stripe_intent_poll', 'stripe_intent_status' => $piStatus]
                    );
                    $pending->save();
                }
                return null;
            }

            $rawPiMeta = $paymentIntent->metadata ?? [];
            if (is_object($rawPiMeta) && method_exists($rawPiMeta, 'toArray')) {
                $rawPiMeta = $rawPiMeta->toArray();
            }
            $piMeta = PaymentMetadataNormalizer::normalize((array) $rawPiMeta);
            $metaRequestId = (int) ($piMeta['rental_request_id'] ?? 0);
            if ($metaRequestId > 0 && $metaRequestId !== (int) $rental->id) {
                return null;
            }

            $existingMeta = PaymentMetadataNormalizer::normalize((array) ($pending->metadata ?? []));
            $rawType = (string) ($piMeta['tx_type'] ?? ($piMeta['payment_type'] ?? ($existingMeta['tx_type'] ?? ($existingMeta['payment_type'] ?? $pending->type))));
            $txType = PaymentMetadataNormalizer::normalizeTransactionType($rawType) ?: 'payment';
            $amount = ((int) ($paymentIntent->amount_received ?? $paymentIntent->amount ?? 0)) / 100;

            if (empty($pending->user_id) && !empty($piMeta['user_id'])) {
                $pending->user_id = (int) $piMeta['user_id'];
            }
            $pending->equipment_rental_id = (int) $rental->id;
            $pending->status = 'paid';
            $pending->type = $txType;
            $pending->currency = strtolower((string) ($paymentIntent->currency ?? ($pending->currency ?? 'eur')));
            $pending->transaction_id = $piId;
            if ($amount > 0) {
                $pending->amount = $amount;
            }
            if (empty($pending->description)) {
                $pending->description = (string) ($paymentIntent->description ?? ('Paiement location #' . ($rental->request_number ?: $rental->id)));
            }
            $pending->metadata = array_merge(
                $existingMeta,
                $piMeta,
                [
                    'rental_request_id' => (string) $rental->id,
                    'reconciled_from' => 'stripe_intent_poll',
                    'stripe_intent_status' => $piStatus,
                ]
            );
            $pending->paid_at = $pending->paid_at ?? now();
            $pending->save();

            Log::warning('Dashboard payments: promoted pending equipment transaction to paid from Stripe intent poll', [
                'rental_request_id' => $rental->id,
                'transaction_id' => $pending->id,
                'payment_intent_id' => $piId,
            ]);

            return $pending->fresh();
        } catch (\Throwable $e) {
            Log::warning('Dashboard payments: unable to promote pending equipment transaction from Stripe', [
                'rental_request_id' => $rental->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function findLatestEquipmentTransaction(EquipmentRentalRequest $rental): ?PaymentTransaction
    {
        $requestId = (int) $rental->id;
        $requestNumber = trim((string) ($rental->request_number ?? ''));
        $statusCandidates = ['paid', 'held', 'succeeded', 'completed', 'refunded', 'partially_refunded'];

        $direct = PaymentTransaction::query()
            ->whereIn('status', $statusCandidates)
            ->where('equipment_rental_id', $requestId)
            ->latest('id')
            ->first();
        if ($direct) {
            return $direct;
        }

        $recent = PaymentTransaction::query()
            ->whereIn('status', $statusCandidates)
            ->whereNotNull('metadata')
            ->latest('id')
            ->limit(400)
            ->get();

        foreach ($recent as $transaction) {
            $meta = PaymentMetadataNormalizer::normalize((array) ($transaction->metadata ?? []));
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

        return null;
    }

    private function findLatestEquipmentEscrow(EquipmentRentalRequest $rental): ?object
    {
        if (!TableExistenceCache::has('escrow_transactions')) {
            return null;
        }

        return DB::table('escrow_transactions')
            ->where('escrowable_id', (int) $rental->id)
            ->where('escrowable_type', 'like', '%EquipmentRentalRequest%')
            ->orderByDesc('id')
            ->first();
    }

    private function mapEquipmentPaymentStatus(array $paymentContext, EquipmentRentalRequest $rental): string
    {
        $transaction = $paymentContext['transaction'] ?? null;
        $escrow = $paymentContext['escrow'] ?? null;

        $txStatus = strtolower((string) ($transaction->status ?? ''));
        $escrowStatus = strtolower((string) ((array) $escrow)['status'] ?? '');

        if (in_array($txStatus, ['refunded', 'partially_refunded'], true) || $escrowStatus === 'refunded') {
            return 'refunded';
        }

        if ((string) $rental->status === 'cancelled') {
            return 'cancelled';
        }

        if (in_array($escrowStatus, ['pending', 'held', 'partial'], true)) {
            return 'pending';
        }

        if ($escrowStatus === 'released') {
            return 'completed';
        }

        if (in_array($txStatus, ['held', 'pending', 'processing'], true)) {
            return 'pending';
        }

        if (in_array($txStatus, ['paid', 'succeeded', 'completed'], true)) {
            return 'completed';
        }

        return $this->mapEquipmentStatus($rental->status);
    }

    /**
     * Récupérer les paiements des ventes urgentes
     */
    private function getUrgentSalePayments($prestataire, $filters)
    {
        if (!TableExistenceCache::has('urgent_sale_purchases')) {
            return collect();
        }

        // Filtre par type
        if ($filters['type'] !== 'all' && $filters['type'] !== 'urgent_sale') {
            return collect();
        }

        $query = UrgentSalePurchase::with(['buyer', 'urgentSale', 'urgentSale.prestataire'])
            ->whereHas('urgentSale', function ($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id);
            })
            ->whereIn('status', ['paid', 'completed', 'shipped', 'delivered']);

        $query = $this->applyDateFilters($query, $filters);

        return $query->get()->map(function ($purchase) {
            $amount = $purchase->total_amount ?? $purchase->urgentSale->price ?? 0;
            $platformCommission = CommissionService::feeAmount($amount, 'urgent_sale', 'prestataire');
            $stripeFee = CommissionService::stripeFeesAmount($amount);
            $totalDeductions = $platformCommission + $stripeFee;
            $netAmount = round($amount - $totalDeductions, 2);

            return [
                'id' => 'urgent_sale_' . $purchase->id,
                'type' => 'urgent_sale',
                'type_label' => 'Vente Flash',
                'type_icon' => 'fire',
                'type_color' => 'orange',
                'reference' => 'VF-' . $purchase->id,
                'title' => $purchase->urgentSale->title ?? 'Article',
                'client_name' => $purchase->buyer->name ?? 'Client',
                'client_email' => $purchase->buyer->email ?? '',
                'amount' => $amount,
                'platform_commission' => $platformCommission,
                'platform_commission_rate' => CommissionService::ratePercent('urgent_sale', 'prestataire'),
                'stripe_fee' => $stripeFee,
                'total_deductions' => $totalDeductions,
                'commission' => $totalDeductions,
                'net_amount' => $netAmount,
                'status' => $this->mapUrgentSaleStatus($purchase->status),
                'payment_type' => 'full',
                'date' => $purchase->created_at,
                'paid_at' => $purchase->paid_at ?? $purchase->created_at,
                'purchase' => $purchase,
            ];
        });
    }

    /**
     * Récupérer les paiements des commandes food
     */
    private function getFoodOrderPayments($prestataire, $filters)
    {
        if (!TableExistenceCache::has('food_orders')) {
            return collect();
        }

        // Filtre par type
        if ($filters['type'] !== 'all' && $filters['type'] !== 'food') {
            return collect();
        }

        $query = FoodOrder::with(['client'])
            ->where('prestataire_id', $prestataire->id)
            ->whereIn('status', ['paid', 'preparing', 'ready', 'delivered', 'completed']);

        $query = $this->applyDateFilters($query, $filters);

        return $query->get()->map(function ($order) {
            $amount = $order->total ?? 0;
            $platformCommission = CommissionService::feeAmount($amount, 'food', 'prestataire');
            $stripeFee = CommissionService::stripeFeesAmount($amount);
            $totalDeductions = $platformCommission + $stripeFee;
            $netAmount = round($amount - $totalDeductions, 2);

            return [
                'id' => 'food_' . $order->id,
                'type' => 'food',
                'type_label' => 'Commande Food',
                'type_icon' => 'utensils',
                'type_color' => 'green',
                'reference' => $order->order_number ?? 'FOOD-' . $order->id,
                'title' => 'Commande #' . ($order->order_number ?? $order->id),
                'client_name' => $order->client->name ?? $order->customer_name ?? 'Client',
                'client_email' => $order->client->email ?? $order->customer_email ?? '',
                'amount' => $amount,
                'platform_commission' => $platformCommission,
                'platform_commission_rate' => CommissionService::ratePercent('food', 'prestataire'),
                'stripe_fee' => $stripeFee,
                'total_deductions' => $totalDeductions,
                'commission' => $totalDeductions,
                'net_amount' => $netAmount,
                'status' => $this->mapFoodStatus($order->status),
                'payment_type' => $order->payment_method === 'cash' ? 'cash' : 'online',
                'date' => $order->created_at,
                'paid_at' => $order->paid_at ?? $order->created_at,
                'order' => $order,
            ];
        });
    }

    /**
     * Appliquer les filtres de date
     */
    private function applyDateFilters($query, $filters)
    {
        // Filtre par période prédéfinie
        switch ($filters['period']) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'quarter':
                $query->whereBetween('created_at', [Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()]);
                break;
            case 'year':
                $query->whereYear('created_at', Carbon::now()->year);
                break;
            case 'custom':
                if ($filters['date_from']) {
                    $query->whereDate('created_at', '>=', $filters['date_from']);
                }
                if ($filters['date_to']) {
                    $query->whereDate('created_at', '<=', $filters['date_to']);
                }
                break;
        }

        // Filtre par statut
        if ($filters['status'] !== 'all') {
            // Ce filtre sera appliqué après le merge
        }

        return $query;
    }

    /**
     * Calculer les statistiques
     */
    private function calculateStats($allPayments, $prestataire)
    {
        $now = Carbon::now();

        // Total gagné (net après commission)
        $totalEarned = $allPayments->where('status', 'completed')->sum('net_amount');

        // En attente
        $pendingAmount = $allPayments->where('status', 'pending')->sum('net_amount');

        // Ce mois
        $thisMonthPayments = $allPayments->filter(function ($p) use ($now) {
            return Carbon::parse($p['date'])->month === $now->month 
                && Carbon::parse($p['date'])->year === $now->year;
        });
        $thisMonthEarned = $thisMonthPayments->where('status', 'completed')->sum('net_amount');

        // Mois dernier
        $lastMonth = $now->copy()->subMonth();
        $lastMonthPayments = $allPayments->filter(function ($p) use ($lastMonth) {
            return Carbon::parse($p['date'])->month === $lastMonth->month 
                && Carbon::parse($p['date'])->year === $lastMonth->year;
        });
        $lastMonthEarned = $lastMonthPayments->where('status', 'completed')->sum('net_amount');

        // Evolution
        $evolution = $lastMonthEarned > 0 
            ? round((($thisMonthEarned - $lastMonthEarned) / $lastMonthEarned) * 100, 1)
            : ($thisMonthEarned > 0 ? 100 : 0);

        // Aujourd'hui
        $todayPayments = $allPayments->filter(function ($p) {
            return Carbon::parse($p['date'])->isToday();
        });
        $todayEarned = $todayPayments->where('status', 'completed')->sum('net_amount');

        // Acomptes en attente
        $pendingDeposits = $allPayments->where('payment_type', 'deposit')
            ->where('status', 'pending')
            ->sum('net_amount');

        // Répartition par type
        $byType = [
            'booking' => $allPayments->where('type', 'booking')->sum('net_amount'),
            'equipment' => $allPayments->where('type', 'equipment')->sum('net_amount'),
            'urgent_sale' => $allPayments->where('type', 'urgent_sale')->sum('net_amount'),
            'food' => $allPayments->where('type', 'food')->sum('net_amount'),
        ];

        // Commission totale prélevée
        $totalCommission = $allPayments->where('status', 'completed')->sum('commission');

        // Nombre de transactions
        $totalTransactions = $allPayments->count();
        $completedTransactions = $allPayments->where('status', 'completed')->count();

        // Solde disponible (depuis Stripe si connecté)
        $availableBalance = $this->getStripeBalance($prestataire);

        return [
            'total_earned' => $totalEarned,
            'pending_amount' => $pendingAmount,
            'this_month' => $thisMonthEarned,
            'last_month' => $lastMonthEarned,
            'evolution' => $evolution,
            'today' => $todayEarned,
            'pending_deposits' => $pendingDeposits,
            'by_type' => $byType,
            'total_commission' => $totalCommission,
            'total_transactions' => $totalTransactions,
            'completed_transactions' => $completedTransactions,
            'available_balance' => $availableBalance,
        ];
    }

    /**
     * Récupérer le solde Stripe
     */
    private function getStripeBalance($prestataire)
    {
        // Vérifier seulement si le compte Stripe existe
        if (empty($prestataire->stripe_account_id)) {
            return null;
        }

        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret') ?: config('services.stripe.secret'));
            $balance = $stripe->balance->retrieve([], [
                'stripe_account' => $prestataire->stripe_account_id,
            ]);

            $available = collect($balance->available)->sum('amount') / 100;
            $pending = collect($balance->pending)->sum('amount') / 100;

            return [
                'available' => $available,
                'pending' => $pending,
                'total' => $available + $pending,
            ];
        } catch (\Exception $e) {
            \Log::error('Stripe Balance Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupérer les paiements directement depuis Stripe
     */
    private function getStripePayments($prestataire, $filters)
    {
        if (empty($prestataire->stripe_account_id)) {
            return collect();
        }

        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret') ?: config('services.stripe.secret'));
            
            $params = ['limit' => 100];
            
            // Filtres de date
            if (!empty($filters['date_from'])) {
                $params['created'] = $params['created'] ?? [];
                $params['created']['gte'] = Carbon::parse($filters['date_from'])->timestamp;
            }
            if (!empty($filters['date_to'])) {
                $params['created'] = $params['created'] ?? [];
                $params['created']['lte'] = Carbon::parse($filters['date_to'])->endOfDay()->timestamp;
            }
            
            $charges = $stripe->paymentIntents->all($params, [
                'stripe_account' => $prestataire->stripe_account_id,
            ]);

            return collect($charges->data)
                ->filter(fn($pi) => $pi->status === 'succeeded')
                ->map(function ($pi) {
                $amount = $pi->amount / 100;
                
                // Frais Stripe (configurable via CommissionService)
                $stripeFee = CommissionService::stripeFeesAmount($amount);
                
                // Récupérer l'application_fee depuis la charge associée
                $totalFees = 0;
                if ($pi->latest_charge) {
                    try {
                        $charge = \Stripe\Charge::retrieve($pi->latest_charge);
                        $totalFees = isset($charge->application_fee_amount) ? ($charge->application_fee_amount / 100) : 0;
                    } catch (\Exception $e) {
                        \Log::error('Failed to retrieve Stripe charge fees for PI ' . $pi->id . ': ' . $e->getMessage());
                    }
                }
                
                // Commission plateforme = total prélevé - frais Stripe réels
                $platformCommission = round($totalFees - $stripeFee, 2);
                
                // Net = ce que le prestataire reçoit vraiment
                $netAmount = $amount - $totalFees;
                
                // Déterminer le statut
                $status = 'completed';
                if ($pi->latest_charge) {
                    try {
                        $charge = $charge ?? \Stripe\Charge::retrieve($pi->latest_charge);
                        if (isset($charge->refunded) && $charge->refunded) {
                            $status = 'refunded';
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to retrieve Stripe charge refund status for PI ' . $pi->id . ': ' . $e->getMessage());
                    }
                }

                return [
                    'id' => $pi->id,
                    'type' => 'stripe',
                    'type_label' => 'Paiement Stripe',
                    'reference' => $pi->id,
                    'title' => $pi->description ?: 'Paiement Stripe',
                    'description' => $pi->description ?? 'Paiement',
                    'amount' => $amount,
                    'commission' => $totalFees,
                    'platform_commission' => $platformCommission,
                    'stripe_fee' => $stripeFee,
                    'net_amount' => $netAmount,
                    'status' => $status,
                    'payment_type' => 'full',
                    'date' => Carbon::createFromTimestamp($pi->created),
                    'client_name' => $pi->receipt_email ?? 'Client',
                    'client_email' => $pi->receipt_email ?? '',
                    'link' => null,
                ];
            });

        } catch (\Exception $e) {
            \Log::error('Stripe Payments Error: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Mapper les statuts de booking
     */
    private function mapStatus($bookingStatus, $transactionStatus = null)
    {
        if ($transactionStatus === 'paid' || $bookingStatus === 'completed') {
            return 'completed';
        }
        if ($transactionStatus === 'refunded') {
            return 'refunded';
        }
        if (in_array($bookingStatus, ['confirmed', 'pending'])) {
            return 'pending';
        }
        return 'pending';
    }

    /**
     * Mapper les statuts d'équipement
     */
    private function mapEquipmentStatus($status)
    {
        return match ($status) {
            'paid', 'completed', 'returned' => 'completed',
            'approved' => 'pending',
            'cancelled' => 'cancelled',
            default => 'pending',
        };
    }

    /**
     * Mapper les statuts de vente urgente
     */
    private function mapUrgentSaleStatus($status)
    {
        return match ($status) {
            'paid', 'completed', 'shipped', 'delivered' => 'completed',
            'pending' => 'pending',
            'cancelled', 'refunded' => 'cancelled',
            default => 'pending',
        };
    }

    /**
     * Mapper les statuts food
     */
    private function mapFoodStatus($status)
    {
        return match ($status) {
            'paid', 'completed', 'delivered' => 'completed',
            'preparing', 'ready' => 'processing',
            'pending' => 'pending',
            'cancelled' => 'cancelled',
            default => 'pending',
        };
    }

    /**
     * Déterminer le type de paiement (acompte/solde/complet)
     */
    private function getPaymentType($booking)
    {
        $transaction = $booking->paymentTransaction;
        if (!$transaction) return 'pending';

        $type = $transaction->type ?? null;
        return match ($type) {
            'deposit' => 'deposit',
            'balance' => 'balance',
            'full' => 'full',
            default => 'full',
        };
    }

    /**
     * Export CSV des transactions
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $prestataire = $user->prestataire;

        // Récupérer toutes les données sans pagination
        $allPayments = collect();
        $filters = [
            'type' => $request->get('type', 'all'),
            'status' => $request->get('status', 'all'),
            'period' => $request->get('period', 'all'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'search' => null,
        ];

        $allPayments = $allPayments->merge($this->getBookingPayments($prestataire, $filters));
        $allPayments = $allPayments->merge($this->getEquipmentRentalPayments($prestataire, $filters));
        $allPayments = $allPayments->merge($this->getUrgentSalePayments($prestataire, $filters));
        $allPayments = $allPayments->merge($this->getFoodOrderPayments($prestataire, $filters));

        $allPayments = $allPayments->sortByDesc('date');

        $filename = 'paiements_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($allPayments) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            // En-têtes
            fputcsv($file, [
                'Référence',
                'Type',
                'Description',
                'Client',
                'Email',
                'Montant brut (€)',
                'Commission (€)',
                'Montant net (€)',
                'Statut',
                'Date',
            ], ';');

            foreach ($allPayments as $payment) {
                fputcsv($file, [
                    $payment['reference'],
                    $payment['type_label'],
                    $payment['title'],
                    $payment['client_name'],
                    $payment['client_email'],
                    number_format($payment['amount'], 2, ',', ''),
                    number_format($payment['commission'], 2, ',', ''),
                    number_format($payment['net_amount'], 2, ',', ''),
                    $this->getStatusLabel($payment['status']),
                    Carbon::parse($payment['date'])->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Obtenir le label du statut
     */
    private function getStatusLabel($status)
    {
        return match ($status) {
            'completed' => 'Complété',
            'pending' => 'En attente',
            'processing' => 'En cours',
            'refunded' => 'Remboursé',
            'cancelled' => 'Annulé',
            default => ucfirst($status),
        };
    }

    /**
     * Détail d'une transaction
     */
    public function show(Request $request, $type, $id)
    {
        $user = auth()->user();
        $prestataire = $user->prestataire;

        $payment = null;

        switch ($type) {
            case 'booking':
                $booking = Booking::with(['client.user', 'service', 'paymentTransaction'])
                    ->whereHas('service', fn($q) => $q->where('prestataire_id', $prestataire->id))
                    ->findOrFail($id);
                $payment = $this->getBookingPayments($prestataire, ['type' => 'all', 'status' => 'all', 'period' => 'all'])
                    ->firstWhere('id', 'booking_' . $id);
                break;

            case 'equipment':
                $rental = EquipmentRentalRequest::with(['client.user', 'equipment'])
                    ->whereHas('equipment', fn($q) => $q->where('prestataire_id', $prestataire->id))
                    ->findOrFail($id);
                $payment = $this->getEquipmentRentalPayments($prestataire, ['type' => 'all', 'status' => 'all', 'period' => 'all'])
                    ->firstWhere('id', 'equipment_' . $id);
                break;

            case 'urgent_sale':
                $purchase = UrgentSalePurchase::with(['buyer', 'urgentSale'])
                    ->whereHas('urgentSale', fn($q) => $q->where('prestataire_id', $prestataire->id))
                    ->findOrFail($id);
                
                // Construire le payment directement si pas dans le cache filtré
                $amount = $purchase->total_amount ?? $purchase->urgentSale->price ?? 0;
                $platformCommission = CommissionService::feeAmount($amount, 'urgent_sale', 'prestataire');
                $stripeFee = CommissionService::stripeFeesAmount($amount);
                $totalDeductions = $platformCommission + $stripeFee;
                $netAmount = round($amount - $totalDeductions, 2);
                
                $payment = [
                    'id' => 'urgent_sale_' . $purchase->id,
                    'type' => 'urgent_sale',
                    'type_label' => 'Vente Flash',
                    'type_icon' => 'fire',
                    'type_color' => 'orange',
                    'reference' => 'VF-' . $purchase->id,
                    'title' => $purchase->urgentSale->title ?? 'Article',
                    'client_name' => $purchase->buyer->name ?? 'Client',
                    'client_email' => $purchase->buyer->email ?? '',
                    'amount' => $amount,
                    'platform_commission' => $platformCommission,
                    'platform_commission_rate' => CommissionService::ratePercent('urgent_sale', 'prestataire'),
                    'stripe_fee' => $stripeFee,
                    'total_deductions' => $totalDeductions,
                    'commission' => $totalDeductions,
                    'net_amount' => $netAmount,
                    'status' => $this->mapUrgentSaleStatus($purchase->status),
                    'payment_type' => 'full',
                    'date' => $purchase->created_at,
                    'paid_at' => $purchase->paid_at ?? $purchase->created_at,
                    'purchase' => $purchase,
                ];
                break;

            case 'food':
                $order = FoodOrder::with(['client', 'items'])
                    ->where('prestataire_id', $prestataire->id)
                    ->findOrFail($id);
                $payment = $this->getFoodOrderPayments($prestataire, ['type' => 'all', 'status' => 'all', 'period' => 'all'])
                    ->firstWhere('id', 'food_' . $id);
                break;

            case 'stripe':
                // Récupérer les détails depuis Stripe (PaymentIntent API)
                try {
                    $stripe = new \Stripe\StripeClient(config('stripe.secret') ?: config('services.stripe.secret'));
                    
                    // L'ID peut être un PaymentIntent (pi_) ou un ancien Charge (py_/ch_)
                    $piId = $id;
                    
                    // Try to retrieve as PaymentIntent first
                    $pi = null;
                    $charge = null;
                    try {
                        $pi = $stripe->paymentIntents->retrieve($piId, [], [
                            'stripe_account' => $prestataire->stripe_account_id,
                        ]);
                        if ($pi->latest_charge) {
                            $charge = $stripe->charges->retrieve($pi->latest_charge, [], [
                                'stripe_account' => $prestataire->stripe_account_id,
                            ]);
                        }
                    } catch (\Exception $e) {
                        // Fallback: try as a Charge ID
                        $chargeId = $id;
                        if (!str_starts_with($id, 'py_') && !str_starts_with($id, 'ch_')) {
                            $chargeId = 'py_' . $id;
                        }
                        $charge = $stripe->charges->retrieve($chargeId, [], [
                            'stripe_account' => $prestataire->stripe_account_id,
                        ]);
                    }
                    
                    $amount = $pi ? ($pi->amount / 100) : ($charge->amount / 100);
                    
                    // Frais Stripe (configurable via CommissionService)
                    $stripeFee = CommissionService::stripeFeesAmount($amount);
                    
                    // Total prélevé au prestataire (application_fee)
                    $totalFees = $charge && isset($charge->application_fee_amount) 
                        ? ($charge->application_fee_amount / 100) 
                        : CommissionService::feeAmount($amount, 'service', 'prestataire');
                    
                    // Commission plateforme = total - frais Stripe (peut être négatif = absorbé)
                    $platformCommission = round($totalFees - $stripeFee, 2);
                    
                    // Net = ce que le prestataire reçoit
                    $netAmount = $amount - $totalFees;
                    
                    $refId = $pi ? $pi->id : $charge->id;
                    $payment = [
                        'id' => $refId,
                        'type' => 'stripe',
                        'type_label' => 'Paiement Stripe',
                        'type_icon' => 'credit-card',
                        'type_color' => 'purple',
                        'reference' => $refId,
                        'title' => ($pi->description ?? $charge->description ?? null) ?: 'Paiement Stripe',
                        'client_name' => $charge->billing_details->name ?? ($pi->receipt_email ?? 'Client'),
                        'client_email' => $charge->billing_details->email ?? ($pi->receipt_email ?? ''),
                        'amount' => $amount,
                        'platform_commission' => $platformCommission,
                        'stripe_fee' => $stripeFee,
                        'total_deductions' => $totalFees,
                        'commission' => $totalFees,
                        'net_amount' => $netAmount,
                        'status' => ($pi && $pi->status === 'succeeded') || ($charge && $charge->status === 'succeeded') ? 'completed' : 'pending',
                        'payment_type' => 'full',
                        'date' => Carbon::createFromTimestamp($pi ? $pi->created : $charge->created),
                        'paid_at' => Carbon::createFromTimestamp($pi ? $pi->created : $charge->created),
                        'receipt_url' => $charge->receipt_url ?? null,
                    ];
                } catch (\Exception $e) {
                    \Log::error('Stripe payment retrieve error: ' . $e->getMessage() . ' - ID: ' . $id);
                    abort(404, 'Paiement Stripe non trouvé.');
                }
                break;
        }

        if (!$payment) {
            abort(404, 'Paiement non trouvé');
        }

        return view('prestataire.payments.show-unified', compact('payment', 'type'));
    }
}
