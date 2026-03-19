<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\EquipmentRentalRequest;
use App\Models\EquipmentRental;
use App\Models\Equipment;
use App\Rules\AvailableDateRange;
use Carbon\Carbon;
use App\Notifications\SimpleNewEquipmentRentalRequestNotification;
use App\Notifications\SimpleEquipmentRentalRequestConfirmationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentTransaction;
use App\Services\InvoiceGenerationService;
use App\Services\EquipmentRentalPaymentSyncService;
use App\Services\StripePaymentService;
use App\Notifications\EquipmentRentalRequestCancelledNotification;
use App\Notifications\EquipmentRentalRequestUpdatedNotification;
use App\Support\PaymentMetadataNormalizer;
use Illuminate\Validation\ValidationException;

class EquipmentRentalRequestController extends Controller
{
    private function arrayFromJsonOrObject($value): array
    {
        if (empty($value)) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return (array) $value->toArray();
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return (array) $value;
    }

    private function normalizeTransactionType(?string $type): string
    {
        return PaymentMetadataNormalizer::normalizeTransactionType($type) ?: 'payment';
    }

    private function retrievePaymentIntentForRequest(EquipmentRentalRequest $request, string $paymentIntentId): ?object
    {
        try {
            return app(StripePaymentService::class)->retrievePaymentIntent($paymentIntentId, null);
        } catch (\Throwable $platformError) {
            $connectedAccountId = $request->prestataire?->stripe_account_id;
            if (!empty($connectedAccountId)) {
                try {
                    return app(StripePaymentService::class)->retrievePaymentIntent($paymentIntentId, $connectedAccountId);
                } catch (\Throwable $connectedError) {
                    Log::warning('Unable to retrieve PaymentIntent on platform and connected account', [
                        'request_id' => $request->id,
                        'payment_intent_id' => $paymentIntentId,
                        'platform_error' => $platformError->getMessage(),
                        'connected_error' => $connectedError->getMessage(),
                    ]);
                }
            } else {
                Log::warning('Unable to retrieve PaymentIntent on platform account', [
                    'request_id' => $request->id,
                    'payment_intent_id' => $paymentIntentId,
                    'platform_error' => $platformError->getMessage(),
                ]);
            }
        }

        return null;
    }

    private function recoverPaymentFromStripeIntent(EquipmentRentalRequest $request, string $paymentIntentId): void
    {
        try {
            $existing = PaymentTransaction::where('stripe_payment_intent_id', $paymentIntentId)->first();
            if ($existing) {
                return;
            }

            $paymentIntent = $this->retrievePaymentIntentForRequest($request, $paymentIntentId);
            if (!$paymentIntent) {
                return;
            }

            $piStatus = strtolower((string) ($paymentIntent->status ?? ''));
            if ($piStatus === 'canceled') {
                return;
            }

            $metadata = PaymentMetadataNormalizer::normalize(
                $this->arrayFromJsonOrObject($paymentIntent->metadata ?? [])
            );
            $metaUserId = (int) ($metadata['user_id'] ?? 0);
            $currentUserId = (int) Auth::id();
            if ($metaUserId > 0 && $metaUserId !== $currentUserId) {
                return;
            }

            $amount = ((int) ($paymentIntent->amount_received ?? $paymentIntent->amount ?? 0)) / 100;
            if ($amount <= 0) {
                return;
            }

            $txType = $this->normalizeTransactionType($metadata['tx_type'] ?? ($metadata['payment_type'] ?? 'payment'));
            $txStatus = in_array($piStatus, ['succeeded', 'requires_capture'], true) ? 'paid' : 'pending';
            $description = trim((string) ($paymentIntent->description ?? 'Paiement location #' . ($request->request_number ?: $request->id)));

            PaymentTransaction::systemCreate([
                'user_id' => $currentUserId,
                'stripe_payment_intent_id' => $paymentIntentId,
                'equipment_rental_id' => (int) $request->id,
                'amount' => $amount,
                'type' => $txType,
                'currency' => strtolower((string) ($paymentIntent->currency ?? 'eur')),
                'status' => $txStatus,
                'provider' => 'stripe',
                'transaction_id' => $paymentIntentId,
                'description' => $description !== '' ? $description : null,
                'metadata' => array_merge($metadata, [
                    'rental_request_id' => (string) $request->id,
                    'recovered_from' => 'stripe_query_param',
                ]),
                'paid_at' => $txStatus === 'paid' ? now() : null,
            ]);

            Log::warning('Recovered rental payment transaction from Stripe PaymentIntent query param', [
                'request_id' => $request->id,
                'payment_intent_id' => $paymentIntentId,
                'status' => $piStatus,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to recover rental payment transaction from Stripe intent', [
                'request_id' => $request->id ?? null,
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('client');
    }
    
    /**
     * Affiche la liste des demandes de location du client
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $clientId = $user->client ? $user->client->id : null;
        
        // Si pas de client_id (prestataire sans profil client), afficher une page vide
        if (!$clientId) {
            $requests = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                10,
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            $stats = [
                'total' => 0,
                'pending' => 0,
                'accepted' => 0,
                'confirmed' => 0,
                'rejected' => 0,
                'total_amount' => 0,
            ];
            return view('client.equipment-rental-requests.index', compact('requests', 'stats'))
                ->with('info', 'Vous n\'avez pas encore effectué de demandes de location.');
        }
        
        $query = EquipmentRentalRequest::where('client_id', $clientId)
                                     ->with(['equipment.prestataire.user', 'equipment.category', 'equipment.subcategory']);
        
        // Filtrage par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtrage par période
        if ($request->filled('period')) {
            switch ($request->period) {
                case 'week':
                    $query->where('created_at', '>=', now()->subWeek());
                    break;
                case 'month':
                    $query->where('created_at', '>=', now()->subMonth());
                    break;
                case '3months':
                    $query->where('created_at', '>=', now()->subMonths(3));
                    break;
                case 'year':
                    $query->where('created_at', '>=', now()->subYear());
                    break;
            }
        }
        
        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('equipment', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }
        
        // Tri
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        switch ($sortBy) {
            case 'start_date':
                $query->orderBy('start_date', $sortOrder);
                break;
            case 'amount':
                $query->orderBy('final_amount', $sortOrder);
                break;
            case 'status':
                $query->orderBy('status', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }
        
        $requests = $query->paginate(10)->withQueryString();
        
        // Statistiques
        $stats = [
            'total' => EquipmentRentalRequest::where('client_id', $clientId)->count(),
            'pending' => EquipmentRentalRequest::where('client_id', $clientId)->where('status', 'pending')->count(),
            'accepted' => EquipmentRentalRequest::where('client_id', $clientId)->where('status', 'accepted')->count(),
            'confirmed' => EquipmentRentalRequest::where('client_id', $clientId)->where('status', 'confirmed')->count(),
            'rejected' => EquipmentRentalRequest::where('client_id', $clientId)->where('status', 'rejected')->count(),
            'total_amount' => EquipmentRentalRequest::where('client_id', $clientId)
                                                  ->whereIn('status', ['accepted', 'confirmed'])
                                                  ->sum('final_amount')
        ];
        
        return view('client.equipment-rental-requests.index', compact('requests', 'stats'));
    }

    /**
     * Enregistre une nouvelle demande de location
     */
    public function store(Request $request)
    {
        try {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',

            'client_message' => 'nullable|string|max:2000',
        ]);
        } catch (ValidationException $e) {
            Log::error('Validation failed for rental request', [
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
                'equipment_id' => $request->input('equipment_id'),
            ]);
            throw $e;
        }

        $equipment = Equipment::with('prestataire.user')->findOrFail($validated['equipment_id']);
        
        // Bloquer l'auto-location pour les prestataires
        $user = Auth::user();
        if ($user->role === 'prestataire' && $user->prestataire && $user->prestataire->id === $equipment->prestataire_id) {
            return back()->with('error', 'Vous ne pouvez pas louer votre propre équipement.')->withInput();
        }
        
        // Si l'utilisateur n'a pas de profil client, en créer un automatiquement
        // Cela permet aux prestataires de faire des locations en mode client
        if (!$user->client) {
            \App\Models\Client::create([
                'user_id' => $user->id,
                'phone' => $user->phone ?? null,
                'address' => $user->address ?? null,
            ]);
            $user->load('client');
        }

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->startOfDay();

        // Vérifier la disponibilité
        if (!$equipment->isAvailableForPeriod($startDate, $endDate)) {
            return back()->withErrors(['start_date' => 'L\'équipement n\'est pas disponible pour la période sélectionnée.'])->withInput();
        }

        // Calcul de la durée et du coût
        $durationDays = $startDate->diffInDays($endDate) + 1;

        $rentalCost = $equipment->calculatePrice($startDate, $endDate);

        $data = array_merge($validated, [
            'client_id' => Auth::user()->client->id,
            'prestataire_id' => $equipment->prestataire_id,
            'request_number' => 'DMD-' . strtoupper(uniqid()),
            'status' => 'pending',
            'duration_days' => $durationDays,
            'unit_price' => $equipment->price_per_day, // Assuming daily rate is the unit price
            'total_amount' => $rentalCost,
            'security_deposit' => $equipment->security_deposit,
            'final_amount' => $rentalCost,
            'pickup_address' => $equipment->address,
        ]);

        $rentalRequest = EquipmentRentalRequest::create($data);

        // Vérifier si un paiement en ligne est requis
        $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
            ? normalize_payment_requirement_for_mode($equipment->payment_requirement ?? 'none')
            : ($equipment->payment_requirement ?? 'none');
        
        // Si paiement requis, rediriger vers la page de paiement SANS notifier le prestataire
        if (in_array($paymentRequirement, ['deposit', 'full'])) {
            // Envoyer seulement la confirmation au client
            $client = Auth::user();
            try {
                Notification::send($client, new SimpleEquipmentRentalRequestConfirmationNotification($rentalRequest));
            } catch (\Exception $e) {
                Log::error('Failed to send equipment rental confirmation to client', [
                    'error' => $e->getMessage(),
                    'client_id' => $client->id,
                    'rental_request_id' => $rentalRequest->id
                ]);
            }
            
            // Vérifier que le prestataire a configuré Stripe avant de rediriger vers le paiement
            if (empty($equipment->prestataire?->stripe_account_id)) {
                // Pas de Stripe → traiter comme espèces, notifier le prestataire
                $prestataire = $equipment->prestataire;
                if ($prestataire && $prestataire->user) {
                    try {
                        Notification::send($prestataire->user, new SimpleNewEquipmentRentalRequestNotification($rentalRequest));
                    } catch (\Exception $e) {
                        Log::error('Failed to send equipment rental notification to prestataire', ['error' => $e->getMessage()]);
                    }
                }
                return redirect()->route('client.equipment-rental-requests.show', $rentalRequest)
                                 ->with('success', 'Votre demande de location a été envoyée. Le paiement se fera directement avec le prestataire.');
            }

            return redirect()->route('client.payments.rental.form', $rentalRequest)
                             ->with('info', 'Veuillez procéder au paiement pour confirmer votre demande.');
        }

        // Pas de paiement requis - notifier le prestataire
        // Debug logging
        Log::info('Debug - Equipment loaded with relationships', [
            'equipment_id' => $equipment->id,
            'prestataire_loaded' => $equipment->prestataire ? 'yes' : 'no',
            'user_loaded' => ($equipment->prestataire && $equipment->prestataire->user) ? 'yes' : 'no',
            'prestataire_id' => $equipment->prestataire ? $equipment->prestataire->id : null,
            'user_id' => ($equipment->prestataire && $equipment->prestataire->user) ? $equipment->prestataire->user->id : null,
        ]);

        // Envoyer des notifications
        $prestataire = $equipment->prestataire;
        if ($prestataire && $prestataire->user) {
            try {
                Notification::send($prestataire->user, new SimpleNewEquipmentRentalRequestNotification($rentalRequest));
                Log::info('Equipment rental notification sent to prestataire', [
                    'prestataire_id' => $prestataire->id,
                    'user_id' => $prestataire->user->id,
                    'rental_request_id' => $rentalRequest->id
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send equipment rental notification to prestataire', [
                    'error' => $e->getMessage(),
                    'prestataire_id' => $prestataire->id,
                    'user_id' => $prestataire->user->id,
                    'rental_request_id' => $rentalRequest->id
                ]);
            }
        } else {
            Log::error('Failed to send equipment rental notification - prestataire or user not found', [
                'equipment_id' => $equipment->id,
                'prestataire_id' => $equipment->prestataire_id,
                'prestataire_loaded' => $prestataire ? 'yes' : 'no',
                'user_loaded' => ($prestataire && $prestataire->user) ? 'yes' : 'no'
            ]);
        }

        $client = Auth::user();
        try {
            Notification::send($client, new SimpleEquipmentRentalRequestConfirmationNotification($rentalRequest));
            Log::info('Equipment rental confirmation sent to client', [
                'client_id' => $client->id,
                'rental_request_id' => $rentalRequest->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send equipment rental confirmation to client', [
                'error' => $e->getMessage(),
                'client_id' => $client->id,
                'rental_request_id' => $rentalRequest->id
            ]);
        }

        return redirect()->route('client.equipment-rental-requests.show', $rentalRequest)
                         ->with('success', 'Votre demande de location a été envoyée avec succès.');
    }

    /**
     * Affiche les détails d'une demande de location
     */
    public function show(EquipmentRentalRequest $request)
    {
        try {
            // Vérifier que la demande appartient au client connecté
            // if ($request->client_id !== Auth::user()->client->id) {
            //     abort(403);
            // }
            
            $request->load([
                'equipment.prestataire.user',
                'equipment.category',
                'equipment.subcategory',
                'rental' // Si la demande a été acceptée et confirmée
            ]);

            // Rattrapage ciblé: si un payment_intent est fourni en query (retour Stripe),
            // tenter de reconstruire la transaction locale même si le callback a échoué.
            $queryPaymentIntentId = trim((string) (request()->query('payment_intent') ?: request()->query('pi')));
            if ($queryPaymentIntentId !== '') {
                $this->recoverPaymentFromStripeIntent($request, $queryPaymentIntentId);
            }

            // Sync global (transaction/escrow/facture) pour éviter les états incohérents
            // entre pages "paiements", "factures", "compte" et cette vue détail.
            try {
                app(EquipmentRentalPaymentSyncService::class)->syncForRequest($request, (int) Auth::id());
            } catch (\Throwable $syncError) {
                Log::warning('Equipment rental show sync warning', [
                    'request_id' => $request->id,
                    'error' => $syncError->getMessage(),
                ]);
            }

            // Auto-réconciliation: si une transaction payée existe mais l'état local n'est pas à jour,
            // on remet en cohérence la demande/la location pour éviter les faux boutons "Payer".
            $this->reconcileRequestPaymentState($request);
            $request->load('rental');

            $paymentContext = $this->buildPaymentContext($request);
            $paymentStatus = $paymentContext['logical_status'] ?? $this->normalizeRentalPaymentStatus($request->rental?->payment_status ?? 'pending');
            $isDepositPaid = $paymentStatus === 'partial';
            $isFullyPaid = $paymentStatus === 'paid';
            $equipmentPaymentRequirement = function_exists('normalize_payment_requirement_for_mode')
                ? normalize_payment_requirement_for_mode($request->equipment?->payment_requirement ?? 'none')
                : ($request->equipment?->payment_requirement ?? 'none');

            $canPayOnline = !$isFullyPaid
                && $paymentStatus !== 'refunded'
                && (function_exists('payment_feature_enabled') ? payment_feature_enabled() : !(function_exists('cash_only_mode') && cash_only_mode()))
                && $equipmentPaymentRequirement !== 'none'
                && !empty($request->equipment?->prestataire?->stripe_account_id);

            return response()->view('client.equipment-rental-requests.show', compact(
                'request',
                'paymentStatus',
                'isDepositPaid',
                'isFullyPaid',
                'canPayOnline',
                'paymentContext'
            ));
        } catch (\Throwable $e) {
            Log::error('Failed to render equipment rental request show page', [
                'request_id' => $request->id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('client.equipment-rental-requests.index')
                ->with('error', 'Impossible d\'afficher cette demande pour le moment. Veuillez réessayer.');
        }
    }

    private function findLatestPaidRentalTransaction(EquipmentRentalRequest $request): ?PaymentTransaction
    {
        $requestId = (int) $request->id;
        $requestNumber = trim((string) ($request->request_number ?? ''));
        $statusCandidates = ['paid', 'held', 'succeeded', 'completed', 'refunded', 'partially_refunded'];

        // Chemin principal: FK directe + utilisateur courant
        $directMatch = PaymentTransaction::query()
            ->where('user_id', Auth::id())
            ->whereIn('status', $statusCandidates)
            ->where('equipment_rental_id', $requestId)
            ->latest('id')
            ->first();
        if ($directMatch) {
            return $directMatch;
        }

        // Fallback sécurité: même demande mais transaction associée à un autre user_id
        // (ex: fallback webhook avec mapping utilisateur incomplet).
        $directWithoutUser = PaymentTransaction::query()
            ->whereIn('status', $statusCandidates)
            ->where('equipment_rental_id', $requestId)
            ->latest('id')
            ->first();
        if ($directWithoutUser) {
            return $directWithoutUser;
        }

        $promoted = $this->promotePendingRentalTransactionFromStripe($request);
        if ($promoted && in_array(strtolower((string) ($promoted->status ?? '')), $statusCandidates, true)) {
            return $promoted;
        }

        $baseQuery = PaymentTransaction::query()
            ->where('user_id', Auth::id())
            ->whereIn('status', $statusCandidates);

        // Fallback robuste: certaines transactions ont seulement metadata.rental_request_id
        // (on filtre en PHP pour éviter des incompatibilités SQL JSON selon les environnements).
        $recentTransactions = (clone $baseQuery)
            ->whereNotNull('metadata')
            ->latest('id')
            ->limit(250)
            ->get();

        foreach ($recentTransactions as $transaction) {
            $meta = PaymentMetadataNormalizer::normalize(
                $this->arrayFromJsonOrObject($transaction->metadata)
            );
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

        // Fallback élargi: scan global des transactions récentes (sans filtre user_id)
        $recentGlobal = PaymentTransaction::query()
            ->whereIn('status', $statusCandidates)
            ->whereNotNull('metadata')
            ->latest('id')
            ->limit(400)
            ->get();

        foreach ($recentGlobal as $transaction) {
            $meta = PaymentMetadataNormalizer::normalize(
                $this->arrayFromJsonOrObject($transaction->metadata)
            );
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

        // Fallback de rattrapage: paiement débité présent en escrow mais transaction locale absente
        // (cas typique si callback confirm interrompu et webhook incomplet).
        try {
            if (DB::getSchemaBuilder()->hasTable('escrow_transactions')) {
                $escrow = DB::table('escrow_transactions')
                    ->where('escrowable_id', $requestId)
                    ->where('escrowable_type', 'like', '%EquipmentRentalRequest%')
                    ->whereNotNull('stripe_payment_intent_id')
                    ->orderByDesc('id')
                    ->first();

                if ($escrow && !empty($escrow->stripe_payment_intent_id)) {
                    $escrowRow = (array) $escrow;
                    $piId = (string) ($escrowRow['stripe_payment_intent_id'] ?? '');
                    if ($piId === '') {
                        return null;
                    }

                    $existingByPi = PaymentTransaction::where('stripe_payment_intent_id', $piId)->first();
                    if ($existingByPi) {
                        return $existingByPi;
                    }

                    $escrowMeta = PaymentMetadataNormalizer::normalize(
                        $this->arrayFromJsonOrObject($escrowRow['metadata'] ?? null)
                    );
                    $escrowAmount = (float) ($escrowRow['total_amount'] ?? ($escrowRow['amount'] ?? 0));
                    $depositAmount = (float) ($escrowRow['deposit_amount'] ?? 0);
                    $amount = $escrowAmount + $depositAmount;
                    if ($amount <= 0) {
                        $amount = (float) ($request->final_amount ?? $request->total_amount ?? 0)
                            + (float) ($request->equipment?->security_deposit ?? $request->security_deposit ?? 0);
                    }

                    $escrowStatus = strtolower((string) ($escrowRow['status'] ?? ''));
                    $transactionStatus = $escrowStatus === 'refunded' ? 'refunded' : 'paid';
                    $paymentType = $this->normalizeTransactionType($escrowMeta['tx_type'] ?? ($escrowMeta['payment_type'] ?? 'payment'));
                    $paidAt = $escrowRow['paid_at'] ?? ($escrowRow['held_at'] ?? now());

                    if ($amount > 0) {
                        $created = PaymentTransaction::systemCreate([
                            'user_id' => (int) Auth::id(),
                            'stripe_payment_intent_id' => $piId,
                            'equipment_rental_id' => $requestId,
                            'amount' => $amount,
                            'type' => $paymentType,
                            'currency' => strtolower((string) ($escrowRow['currency'] ?? 'eur')),
                            'status' => $transactionStatus,
                            'provider' => 'stripe',
                            'transaction_id' => $piId,
                            'description' => 'Paiement location #' . ($request->request_number ?: $requestId),
                            'metadata' => array_merge($escrowMeta, [
                                'rental_request_id' => (string) $requestId,
                                'escrow_id' => (string) ($escrowRow['id'] ?? ''),
                                'reconciled_from' => 'escrow_transactions',
                            ]),
                            'paid_at' => $paidAt,
                        ]);

                        Log::warning('Rental payment transaction backfilled from escrow', [
                            'request_id' => $requestId,
                            'transaction_id' => $created->id,
                            'pi' => $piId,
                        ]);

                        return $created;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Rental escrow backfill skipped', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    private function findLatestPendingRentalTransaction(EquipmentRentalRequest $request): ?PaymentTransaction
    {
        $requestId = (int) $request->id;
        $requestNumber = trim((string) ($request->request_number ?? ''));
        $statusCandidates = ['pending', 'processing'];

        $directMatch = PaymentTransaction::query()
            ->where('user_id', Auth::id())
            ->whereIn('status', $statusCandidates)
            ->where('equipment_rental_id', $requestId)
            ->latest('id')
            ->first();
        if ($directMatch) {
            return $directMatch;
        }

        $directWithoutUser = PaymentTransaction::query()
            ->whereIn('status', $statusCandidates)
            ->where('equipment_rental_id', $requestId)
            ->latest('id')
            ->first();
        if ($directWithoutUser) {
            return $directWithoutUser;
        }

        $baseQuery = PaymentTransaction::query()
            ->where('user_id', Auth::id())
            ->whereIn('status', $statusCandidates);

        $recentTransactions = (clone $baseQuery)
            ->whereNotNull('metadata')
            ->latest('id')
            ->limit(250)
            ->get();

        foreach ($recentTransactions as $transaction) {
            $meta = PaymentMetadataNormalizer::normalize(
                $this->arrayFromJsonOrObject($transaction->metadata)
            );
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

        $recentGlobal = PaymentTransaction::query()
            ->whereIn('status', $statusCandidates)
            ->whereNotNull('metadata')
            ->latest('id')
            ->limit(400)
            ->get();

        foreach ($recentGlobal as $transaction) {
            $meta = PaymentMetadataNormalizer::normalize(
                $this->arrayFromJsonOrObject($transaction->metadata)
            );
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

    private function promotePendingRentalTransactionFromStripe(EquipmentRentalRequest $request): ?PaymentTransaction
    {
        try {
            $pending = $this->findLatestPendingRentalTransaction($request);
            if (!$pending) {
                return null;
            }

            $piId = trim((string) ($pending->stripe_payment_intent_id ?? $pending->transaction_id ?? ''));
            if ($piId === '') {
                return null;
            }

            $paymentIntent = $this->retrievePaymentIntentForRequest($request, $piId);
            if (!$paymentIntent) {
                return null;
            }

            $piStatus = strtolower((string) ($paymentIntent->status ?? ''));
            if (!in_array($piStatus, ['succeeded', 'requires_capture'], true)) {
                if (in_array($piStatus, ['canceled'], true) && in_array((string) $pending->status, ['pending', 'processing'], true)) {
                    $pending->status = 'failed';
                    $pending->metadata = array_merge(
                        PaymentMetadataNormalizer::normalize((array) ($pending->metadata ?? [])),
                        ['reconciled_from' => 'stripe_intent_poll', 'stripe_intent_status' => $piStatus]
                    );
                    $pending->save();
                }
                return null;
            }

            $piMeta = PaymentMetadataNormalizer::normalize(
                $this->arrayFromJsonOrObject($paymentIntent->metadata ?? [])
            );
            $metaRequestId = (int) ($piMeta['rental_request_id'] ?? 0);
            if ($metaRequestId > 0 && $metaRequestId !== (int) $request->id) {
                return null;
            }

            $amount = ((int) ($paymentIntent->amount_received ?? $paymentIntent->amount ?? 0)) / 100;
            $existingMeta = PaymentMetadataNormalizer::normalize((array) ($pending->metadata ?? []));
            $rawType = (string) ($piMeta['tx_type'] ?? ($piMeta['payment_type'] ?? ($existingMeta['tx_type'] ?? ($existingMeta['payment_type'] ?? $pending->type))));
            $txType = $this->normalizeTransactionType($rawType);
            if ($txType === '') {
                $txType = 'payment';
            }

            $pending->user_id = (int) Auth::id();
            $pending->equipment_rental_id = (int) $request->id;
            $pending->status = 'paid';
            $pending->type = $txType;
            $pending->currency = strtolower((string) ($paymentIntent->currency ?? ($pending->currency ?? 'eur')));
            $pending->transaction_id = $piId;
            if ($amount > 0) {
                $pending->amount = $amount;
            }
            if (empty($pending->description)) {
                $pending->description = (string) ($paymentIntent->description ?? ('Paiement location #' . ($request->request_number ?: $request->id)));
            }

            $pending->metadata = array_merge(
                $existingMeta,
                $piMeta,
                [
                    'rental_request_id' => (string) $request->id,
                    'reconciled_from' => 'stripe_intent_poll',
                    'stripe_intent_status' => $piStatus,
                ]
            );
            $pending->paid_at = $pending->paid_at ?? now();
            $pending->save();

            Log::warning('Promoted pending rental transaction to paid from Stripe intent poll', [
                'request_id' => $request->id,
                'transaction_id' => $pending->id,
                'payment_intent_id' => $piId,
            ]);

            return $pending->fresh();
        } catch (\Throwable $e) {
            Log::warning('Unable to promote pending rental transaction from Stripe', [
                'request_id' => $request->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function normalizeRentalPaymentStatus(?string $status): string
    {
        return match ((string) $status) {
            'deposit_paid' => 'partial',
            'full_paid', 'completed' => 'paid',
            'refunded', 'partially_refunded' => 'refunded',
            'partial', 'paid', 'pending' => (string) $status,
            default => 'pending',
        };
    }

    private function persistRentalPaymentStatus(EquipmentRental $rental, string $logicalStatus): void
    {
        if ($logicalStatus === 'partial') {
            $primary = 'partial';
            $fallback = 'deposit_paid';
        } elseif ($logicalStatus === 'refunded') {
            $primary = 'refunded';
            $fallback = 'refund_pending';
        } else {
            $primary = 'paid';
            $fallback = 'full_paid';
        }

        $lastError = null;
        foreach (array_unique([$primary, $fallback]) as $candidate) {
            try {
                $rental->payment_status = $candidate;
                $rental->save();
                return;
            } catch (\Throwable $e) {
                $lastError = $e;
            }
        }

        if ($lastError) {
            throw $lastError;
        }
    }

    private function reconcileRequestPaymentState(EquipmentRentalRequest $request): void
    {
        try {
            $this->promotePendingRentalTransactionFromStripe($request);

            $transaction = $this->findLatestPaidRentalTransaction($request);
            if (!$transaction) {
                return;
            }

            $request->loadMissing('rental', 'equipment');
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

            if ((int) ($transaction->user_id ?? 0) !== (int) Auth::id()) {
                try {
                    $transaction->user_id = (int) Auth::id();
                    $transaction->save();
                } catch (\Throwable $e) {
                    Log::warning('Unable to reassign rental transaction to current user during reconciliation', [
                        'request_id' => $request->id,
                        'transaction_id' => $transaction->id ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $meta = PaymentMetadataNormalizer::normalize(
                (array) ($transaction->metadata ?? [])
            );
            $paymentType = (string) ($meta['payment_type'] ?? ($meta['tx_type'] ?? $transaction->type ?? 'full'));
            $logicalStatus = $paymentType === 'deposit' ? 'partial' : 'paid';
            $this->persistRentalPaymentStatus($rental, $logicalStatus);

            if (in_array($request->status, ['accepted', 'pending'], true)) {
                $request->status = 'confirmed';
                $request->confirmed_at = $request->confirmed_at ?? now();
                $request->save();
            }

            try {
                app(InvoiceGenerationService::class)->generateForEquipmentRental($request, $transaction);
            } catch (\Throwable $invoiceError) {
                Log::warning('Rental request invoice reconciliation warning', [
                    'request_id' => $request->id,
                    'error' => $invoiceError->getMessage(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to reconcile equipment rental payment state', [
                'request_id' => $request->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function findLatestRentalEscrow(EquipmentRentalRequest $request): ?object
    {
        if (!DB::getSchemaBuilder()->hasTable('escrow_transactions')) {
            return null;
        }

        return DB::table('escrow_transactions')
            ->where('escrowable_id', (int) $request->id)
            ->where('escrowable_type', 'like', '%EquipmentRentalRequest%')
            ->orderByDesc('id')
            ->first();
    }

    private function buildPaymentContext(EquipmentRentalRequest $request): array
    {
        $transaction = $this->findLatestPaidRentalTransaction($request);
        $escrow = $this->findLatestRentalEscrow($request);

        if (!$transaction && !$escrow) {
            $transaction = $this->promotePendingRentalTransactionFromStripe($request);
        }

        $logicalStatus = $this->normalizeRentalPaymentStatus($request->rental?->payment_status ?? 'pending');
        $paymentType = null;

        if ($transaction) {
            $meta = PaymentMetadataNormalizer::normalize(
                $this->arrayFromJsonOrObject($transaction->metadata ?? [])
            );
            $rawType = (string) ($meta['payment_type'] ?? ($meta['tx_type'] ?? $transaction->type ?? ''));
            $normalizedTxType = $this->normalizeTransactionType($rawType);
            if ($rawType !== '') {
                $paymentType = $rawType;
            } elseif ($normalizedTxType !== '') {
                $paymentType = $normalizedTxType;
            }

            $txStatus = strtolower((string) ($transaction->status ?? ''));
            if (in_array($txStatus, ['refunded', 'partially_refunded'], true)) {
                $logicalStatus = 'refunded';
            } elseif ($normalizedTxType === 'deposit') {
                if ($logicalStatus === 'pending') {
                    $logicalStatus = 'partial';
                }
            } else {
                // Paiement "full/payment/balance" détecté -> forcer l'état payé
                // même si la location est restée marquée "partial" par un ancien flux.
                $logicalStatus = 'paid';
            }
        }

        if ($escrow) {
            $escrowRow = (array) $escrow;
            $escrowMeta = PaymentMetadataNormalizer::normalize(
                $this->arrayFromJsonOrObject($escrowRow['metadata'] ?? null)
            );
            $escrowPaymentType = strtolower((string) ($escrowMeta['payment_type'] ?? ($escrowMeta['tx_type'] ?? '')));
            if ($escrowPaymentType !== '') {
                $paymentType = $escrowPaymentType;
            }

            $escrowStatus = strtolower((string) ($escrowRow['status'] ?? ''));
            if ($escrowStatus === 'refunded') {
                $logicalStatus = 'refunded';
            } elseif (in_array($escrowStatus, ['pending', 'held', 'partial', 'released'], true) && in_array($logicalStatus, ['pending', 'partial'], true)) {
                $logicalStatus = $escrowPaymentType === 'deposit' ? 'partial' : 'paid';
            }
        }

        if ($request->rental && in_array($logicalStatus, ['partial', 'paid', 'refunded'], true)) {
            try {
                $this->persistRentalPaymentStatus($request->rental, $logicalStatus);
            } catch (\Throwable $e) {
                // keep rendering even if enum mismatch in this environment
            }
        }

        return [
            'logical_status' => $logicalStatus,
            'payment_type' => $paymentType,
            'transaction' => $transaction,
            'escrow' => $escrow,
        ];
    }
    
    /**
     * Annule une demande de location
     */
    public function cancel(EquipmentRentalRequest $request)
    {
        if ($request->client_id !== Auth::user()->client->id) {
            abort(403);
        }

        if (!in_array($request->status, ['pending', 'accepted', 'confirmed', 'in_preparation'], true)) {
            return back()->with('error', 'Cette demande ne peut pas être annulée.');
        }

        $request->loadMissing('equipment', 'rental');
        $cancellationHours = max(0, (int) ($request->equipment?->cancellation_hours ?? 24));
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : null;
        if ($startDate) {
            $cancelDeadline = $startDate->copy()->subHours($cancellationHours);
            if (now()->greaterThan($cancelDeadline)) {
                return back()->with('error', 'Le délai d\'annulation est dépassé pour cette location.');
            }
        }

        $reason = trim((string) request()->input('cancellation_reason', ''));
        if ($reason === '') {
            $reason = 'Annulée par le client';
        }

        $refundSummary = $this->processCancellationRefunds($request, 'client', $reason);
        if (!$refundSummary['ok']) {
            return back()->with('error', 'Annulation impossible pour le moment. Le remboursement n\'a pas pu être finalisé. Réessayez.');
        }

        DB::transaction(function () use ($request, $reason, $refundSummary) {
            $request->cancel($reason, Auth::id());

            if ($request->rental) {
                $rentalUpdates = [
                    'status' => 'cancelled',
                    'cancellation_reason' => $reason,
                    'cancelled_at' => now(),
                    'cancelled_by' => Auth::id(),
                ];
                $request->rental->update($rentalUpdates);

                if ($refundSummary['refund_total'] > 0) {
                    try {
                        $this->persistRentalPaymentStatus($request->rental, 'refunded');
                    } catch (\Throwable $e) {
                        // Enum legacy: fallback is handled inside persistRentalPaymentStatus
                    }
                }
            }
        });

        // Notifier le prestataire de l'annulation
        if ($request->equipment && $request->equipment->prestataire && $request->equipment->prestataire->user) {
            try {
                $request->load(['equipment', 'client.user']);
                $request->equipment->prestataire->user->notify(new EquipmentRentalRequestCancelledNotification($request));
            } catch (\Exception $e) {
                Log::error('Failed to notify prestataire of rental request cancellation', ['error' => $e->getMessage()]);
            }
        }

        $message = 'Votre demande a été annulée.';
        if ($refundSummary['refund_total'] > 0) {
            $message .= ' Remboursement déclenché: ' . number_format($refundSummary['refund_total'], 2) . ' €.';
        }
        if ($refundSummary['partial_failure']) {
            $message .= ' Attention: une partie du remboursement est en attente de reprise automatique.';
        }

        return back()->with('success', $message);
    }

    private function findRefundableEscrowsForRequest(EquipmentRentalRequest $request)
    {
        if (!DB::getSchemaBuilder()->hasTable('escrow_transactions')) {
            return collect();
        }

        return DB::table('escrow_transactions')
            ->where('escrowable_id', (int) $request->id)
            ->where('escrowable_type', 'like', '%EquipmentRentalRequest%')
            ->whereIn('status', ['pending', 'held'])
            ->orderBy('id')
            ->get();
    }

    private function markRentalTransactionsRefunded(EquipmentRentalRequest $request, float $refundAmount, string $reason, string $cancelledBy): void
    {
        if ($refundAmount <= 0) {
            return;
        }

        $transactions = PaymentTransaction::query()
            ->where('equipment_rental_id', (int) $request->id)
            ->whereIn('status', ['paid', 'held', 'released', 'completed', 'partially_refunded'])
            ->orderByDesc('id')
            ->get();

        if ($transactions->isEmpty()) {
            $latest = $this->findLatestPaidRentalTransaction($request);
            if ($latest) {
                $transactions = collect([$latest]);
            }
        }

        if ($transactions->isEmpty()) {
            return;
        }

        $totalCaptured = (float) $transactions->sum(function ($tx) {
            return (float) ($tx->amount ?? 0);
        });
        $targetStatus = ($refundAmount + 0.01 >= $totalCaptured) ? 'refunded' : 'partially_refunded';

        foreach ($transactions as $transaction) {
            try {
                $meta = PaymentMetadataNormalizer::normalize(
                    $this->arrayFromJsonOrObject($transaction->metadata ?? [])
                );
                $meta['cancelled_by'] = $cancelledBy;
                $meta['cancellation_refund_amount'] = round($refundAmount, 2);
                $meta['cancellation_refund_reason'] = $reason;
                $meta['cancellation_refunded_at'] = now()->toIso8601String();

                $transaction->status = $targetStatus;
                $transaction->refunded_at = now();
                $transaction->refund_reason = $reason;
                $transaction->metadata = $meta;
                $transaction->save();
            } catch (\Throwable $e) {
                Log::warning('Unable to update payment transaction refund status after rental cancellation', [
                    'request_id' => $request->id,
                    'transaction_id' => $transaction->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function processCancellationRefunds(EquipmentRentalRequest $request, string $cancelledBy, string $reason): array
    {
        $summary = [
            'ok' => true,
            'refund_total' => 0.0,
            'partial_failure' => false,
        ];

        $escrows = $this->findRefundableEscrowsForRequest($request);
        if ($escrows->isEmpty()) {
            return $summary;
        }

        $successCount = 0;
        $failureCount = 0;

        foreach ($escrows as $escrow) {
            try {
                $result = app(\App\Services\EscrowService::class)->cancelWithRefund((int) $escrow->id, $cancelledBy);
                if (!($result['success'] ?? false)) {
                    $failureCount++;
                    Log::warning('Rental request cancellation refund failed on escrow', [
                        'request_id' => $request->id,
                        'escrow_id' => $escrow->id ?? null,
                        'result' => $result,
                    ]);
                    continue;
                }

                $successCount++;
                $summary['refund_total'] += max(0, (float) ($result['refund_amount'] ?? 0));
            } catch (\Throwable $e) {
                $failureCount++;
                Log::warning('Rental request cancellation refund exception', [
                    'request_id' => $request->id,
                    'escrow_id' => $escrow->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($successCount === 0 && $failureCount > 0) {
            $summary['ok'] = false;
            return $summary;
        }

        $summary['partial_failure'] = $failureCount > 0;
        if ($summary['refund_total'] > 0) {
            $this->markRentalTransactionsRefunded($request, (float) $summary['refund_total'], $reason, $cancelledBy);
        }

        return $summary;
    }
    
    /**
     * Modifie une demande de location (si encore en attente)
     */
    public function edit(EquipmentRentalRequest $request)
    {
        if ($request->client_id !== Auth::user()->client->id) {
            abort(403);
        }
        
        if ($request->status !== 'pending') {
            return back()->with('error', 'Cette demande ne peut plus être modifiée.');
        }
        
        $request->load(['equipment.prestataire.user', 'equipment.category', 'equipment.subcategory']);
        
        return view('client.equipment.requests.edit', compact('request'));
    }
    
    /**
     * Met à jour une demande de location
     */
    public function update(Request $updateRequest, EquipmentRentalRequest $rentalRequest)
    {
        if ($rentalRequest->client_id !== Auth::user()->client->id) {
            abort(403);
        }
        
        if ($rentalRequest->status !== 'pending') {
            return back()->with('error', 'Cette demande ne peut plus être modifiée.');
        }
        
        $validated = $updateRequest->validate([
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',

            'pickup_address' => 'nullable|string|max:500',

            'pickup_required' => 'boolean',
            'client_message' => 'nullable|string|max:1000',
            'special_requirements' => 'nullable|string|max:500'
        ]);
        
        // Vérifier la disponibilité
        if (!$rentalRequest->equipment->isAvailableForPeriod($validated['start_date'], $validated['end_date'], $rentalRequest->id)) {
            return back()->with('error', 'L\'équipement n\'est pas disponible pour cette période.');
        }
        
        // Recalculer les montants
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $durationDays = $startDate->diffInDays($endDate) + 1;
        
        $totalAmount = $rentalRequest->equipment->calculatePrice($durationDays);
        // final_amount doit représenter le montant de location (hors caution)
        $finalAmount = $totalAmount;
        
        $rentalRequest->update(array_merge($validated, [
            'duration_days' => $durationDays,
            'total_amount' => $totalAmount,

            'final_amount' => $finalAmount,

            'pickup_required' => $validated['pickup_required'] ?? false,
            'updated_at' => now()
        ]));
        
        // Notifier le prestataire de la modification
        if ($rentalRequest->equipment && $rentalRequest->equipment->prestataire && $rentalRequest->equipment->prestataire->user) {
            try {
                $rentalRequest->load(['equipment', 'client.user']);
                $rentalRequest->equipment->prestataire->user->notify(new EquipmentRentalRequestUpdatedNotification($rentalRequest));
            } catch (\Exception $e) {
                Log::error('Failed to notify prestataire of rental request update', ['error' => $e->getMessage()]);
            }
        }
        
        return redirect()->route('client.equipment.requests.show', $rentalRequest)
                        ->with('success', 'Votre demande a été mise à jour avec succès!');
    }
    
    /**
     * Supprime une demande de location
     */
    public function destroy(EquipmentRentalRequest $request)
    {
        if ($request->client_id !== Auth::user()->client->id) {
            abort(403);
        }
        
        if (!in_array($request->status, ['pending', 'rejected', 'cancelled', 'expired'])) {
            return back()->with('error', 'Cette demande ne peut pas être supprimée.');
        }
        
        $request->delete();
        
        return redirect()->route('client.dashboard')
                        ->with('success', 'La demande a été supprimée.');
    }
    
    /**
     * Exporte les demandes en CSV
     */
    public function export(Request $request)
    {
        $query = EquipmentRentalRequest::where('client_id', Auth::user()->client->id)
                                     ->with(['equipment.prestataire.user']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }
        
        $requests = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'mes_demandes_location_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function () use ($requests) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'Numéro',
                'Équipement',
                'Prestataire',
                'Date début',
                'Date fin',
                'Durée (jours)',
                'Montant total',
                'Statut',
                'Date demande',
                'Date réponse'
            ]);
            
            foreach ($requests as $request) {
                fputcsv($file, [
                    $request->request_number,
                    $request->equipment->name,
                    $request->equipment->prestataire->user->name,
                    $request->start_date,
                    $request->end_date,
                    $request->duration_days,
                    number_format($request->final_amount, 2) . ' €',
                    ucfirst($request->status),
                    $request->created_at->format('d/m/Y H:i'),
                    $request->responded_at ? $request->responded_at->format('d/m/Y H:i') : ''
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Affiche les statistiques des demandes
     */
    public function stats()
    {
        $clientId = Auth::user()->client->id;
        
        // Statistiques générales
        $totalRequests = EquipmentRentalRequest::where('client_id', $clientId)->count();
        $totalAmount = EquipmentRentalRequest::where('client_id', $clientId)
                                           ->whereIn('status', ['accepted', 'confirmed'])
                                           ->sum('final_amount');
        
        // Répartition par statut
        $statusStats = EquipmentRentalRequest::where('client_id', $clientId)
                                           ->select('status', DB::raw('count(*) as count'))
                                           ->groupBy('status')
                                           ->pluck('count', 'status')
                                           ->toArray();
        
        // Évolution mensuelle
        $monthlyStats = EquipmentRentalRequest::where('client_id', $clientId)
                                            ->where('created_at', '>=', now()->subMonths(12))
                                            ->select(
                                                DB::raw('YEAR(created_at) as year'),
                                                DB::raw('MONTH(created_at) as month'),
                                                DB::raw('count(*) as count'),
                                                DB::raw('sum(final_amount) as amount')
                                            )
                                            ->groupBy('year', 'month')
                                            ->orderBy('year')
                                            ->orderBy('month')
                                            ->get();
        
        // Top équipements demandés
        $topEquipment = EquipmentRentalRequest::where('client_id', $clientId)
                                            ->with('equipment')
                                            ->select('equipment_id', DB::raw('count(*) as count'))
                                            ->groupBy('equipment_id')
                                            ->orderBy('count', 'desc')
                                            ->limit(5)
                                            ->get();
        
        // Temps de réponse moyen
        $avgResponseTime = EquipmentRentalRequest::where('client_id', $clientId)
                                                ->whereNotNull('responded_at')
                                                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, responded_at)) as avg_hours')
                                                ->value('avg_hours');
        
        return view('client.equipment.requests.stats', compact(
            'totalRequests',
            'totalAmount',
            'statusStats',
            'monthlyStats',
            'topEquipment',
            'avgResponseTime'
        ));
    }
}
