<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\EquipmentRental;
use App\Models\EquipmentRentalRequest;
use App\Models\PaymentAllocation;
use App\Models\PaymentTransaction;
use App\Support\PaymentMetadataNormalizer;
use App\Services\StripePaymentService;
use App\Services\InvoiceGenerationService;
use App\Services\EscrowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Contrôleur de paiement pour les locations de matériel
 * 
 * RÈGLES MÉTIER:
 * - payment_requirement = 'none' : pas de paiement en ligne requis
 * - payment_requirement = 'deposit' : acompte (deposit_percentage %) + caution requise
 * - payment_requirement = 'full' : paiement total + caution requis
 * 
 * ESCROW:
 * - Les fonds sont bloqués sur la plateforme jusqu'à :
 *   1. Confirmation retour équipement en bon état → libération au prestataire + caution au client
 *   2. Dégât constaté → caution retenue (partielle/totale)
 *   3. Auto-release après délai configuré
 */
class EquipmentRentalRequestPaymentController extends Controller
{
    private EscrowService $escrowService;

    public function __construct(
        private readonly StripePaymentService $stripe,
        private readonly InvoiceGenerationService $invoiceService,
        EscrowService $escrowService
    ) {
        $this->middleware(['auth', 'role:client,prestataire']);
        $this->escrowService = $escrowService;
    }

    public function show(EquipmentRentalRequest $request)
    {
        $clientId = auth()->user()?->client?->id;
        if (!$clientId || (int) $request->client_id !== (int) $clientId) {
            abort(403);
        }

        if (function_exists('payment_feature_enabled') && !payment_feature_enabled()) {
            return redirect()->route('client.equipment-rental-requests.show', $request)
                ->with('info', 'Le paiement en ligne est désactivé pour le moment.');
        }

        $request->load('equipment', 'prestataire.user', 'rental');
        $this->resolvePaidTransactionAndReconcile($request);
        $request->load('rental');

        // Vérifier que l'équipement existe
        if (!$request->equipment) {
            return redirect()->back()->with('error', 'Équipement non trouvé.');
        }

        // Vérifier le payment_requirement de l'équipement
        $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
            ? normalize_payment_requirement_for_mode($request->equipment->payment_requirement ?? 'none')
            : ($request->equipment->payment_requirement ?? 'none');
        if ($paymentRequirement === 'none') {
            return redirect()->route('client.equipment-rentals.show', $request->rental ?? $request)
                ->with('error', 'Cette location est configurée pour paiement hors ligne.');
        }

        $stripeAccountId = $request->prestataire?->stripe_account_id;
        if (empty($stripeAccountId)) {
            return redirect()->route('client.equipment-rentals.show', $request->rental ?? $request)
                ->with('error', 'Le paiement en ligne n\'est pas disponible pour ce prestataire.');
        }
        
        // Autoriser le paiement pour les demandes pending si le paiement est obligatoire
        $allowedStatuses = ['accepted', 'confirmed', 'in_preparation'];
        if (in_array($paymentRequirement, ['deposit', 'full'])) {
            $allowedStatuses[] = 'pending';
        }
        
        if (!in_array($request->status, $allowedStatuses, true)) {
            return redirect()->back()->with('error', 'Cette location n\'est pas prête pour le paiement.');
        }

        // Montants selon les règles métier
        // SÉCURITÉ M2: La caution est TOUJOURS lue depuis le modèle Equipment (source de vérité)
        // et jamais depuis le champ request.security_deposit qui pourrait être manipulé par l'utilisateur
        $securityDeposit = (float) ($request->equipment->security_deposit ?? 0);
        $totalAmount = $this->resolveRentalAmount($request);
        $depositPercentage = (float) ($request->equipment->deposit_percentage ?? 30);
        
        // Acompte location = pourcentage du montant total
        $rentalDepositAmount = round($totalAmount * ($depositPercentage / 100), 2);
        
        // Solde restant après acompte
        $balanceAmount = max(0, $totalAmount - $rentalDepositAmount);

        $rental = $request->rental;
        $paymentStatus = $rental?->payment_status ?? 'pending';
        $isDepositPaid = $this->isDepositPaidStatus($paymentStatus);
        $isFullyPaid = $this->isFullyPaidStatus($paymentStatus);

        if ($isFullyPaid) {
            return redirect()
                ->route('client.equipment-rental-requests.show', $request)
                ->with('success', 'Cette location est déjà réglée.');
        }

        // Déterminer le type de paiement par défaut selon le payment_requirement
        $defaultPaymentType = 'full';
        if ($paymentRequirement === 'deposit' && $paymentStatus === 'pending') {
            $defaultPaymentType = 'deposit';
        }
        if ($isDepositPaid) {
            $defaultPaymentType = 'balance';
        }

        // Calculer le montant dû maintenant
        $amountDueNow = match ($defaultPaymentType) {
            'deposit' => $rentalDepositAmount + $securityDeposit,
            'balance' => $balanceAmount,
            default => $totalAmount + $securityDeposit,
        };

        return view('payments.equipment-rental-payment', [
            'rentalRequest' => $request,
            'totalAmount' => $totalAmount,
            'securityDeposit' => $securityDeposit,
            'depositPercentage' => $depositPercentage,
            'rentalDepositAmount' => $rentalDepositAmount,
            'depositAmount' => $rentalDepositAmount, // Alias pour la vue
            'balanceAmount' => $balanceAmount,
            'amountDueNow' => $amountDueNow,
            'defaultPaymentType' => $defaultPaymentType,
            'paymentStatus' => $paymentStatus,
            'isDepositPaid' => $isDepositPaid,
            'isFullyPaid' => $isFullyPaid,
            'paymentRequirement' => $paymentRequirement,
            'stripeAccountId' => $stripeAccountId,
        ]);
    }

    public function createIntent(Request $httpRequest, EquipmentRentalRequest $request)
    {
        $clientId = auth()->user()?->client?->id;
        if (!$clientId || (int) $request->client_id !== (int) $clientId) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        if (function_exists('payment_feature_enabled') && !payment_feature_enabled()) {
            return response()->json(['error' => 'Le paiement en ligne est désactivé pour le moment.'], 422);
        }

        $request->load('rental', 'prestataire', 'equipment');
        $this->resolvePaidTransactionAndReconcile($request);
        $request->load('rental');

        // Vérifier le payment_requirement de l'équipement
        $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
            ? normalize_payment_requirement_for_mode($request->equipment->payment_requirement ?? 'none')
            : ($request->equipment->payment_requirement ?? 'none');
        if ($paymentRequirement === 'none') {
            return response()->json(['error' => 'Paiement en ligne non autorisé pour cette location.'], 422);
        }

        $connectedAccountId = $request->prestataire?->stripe_account_id;
        if (empty($connectedAccountId)) {
            return response()->json(['error' => 'Le prestataire n\'a pas configuré le paiement en ligne.'], 422);
        }
        
        // Autoriser le paiement pour les demandes pending si le paiement est obligatoire
        $allowedStatuses = ['accepted', 'confirmed', 'in_preparation'];
        if (in_array($paymentRequirement, ['deposit', 'full'])) {
            $allowedStatuses[] = 'pending';
        }

        if (!in_array($request->status, $allowedStatuses, true)) {
            return response()->json(['error' => 'Location non payable'], 422);
        }

        $paymentStatus = $request->rental?->payment_status ?? 'pending';
        if ($this->isFullyPaidStatus($paymentStatus)) {
            return response()->json([
                'error' => 'Cette location est déjà payée.',
                'already_paid' => true,
                'redirect_url' => route('client.equipment-rental-requests.show', $request),
            ], 409);
        }

        $validated = $httpRequest->validate([
            'payment_type' => 'required|in:full,deposit,balance',
        ]);

        // Calculer les montants selon les règles métier
        // SÉCURITÉ: la caution est TOUJOURS lue depuis Equipment (source de vérité)
        $securityDeposit = (float) ($request->equipment->security_deposit ?? 0);
        $totalAmount = $this->resolveRentalAmount($request);
        $depositPercentage = (float) ($request->equipment->deposit_percentage ?? 30);
        
        // Acompte location = pourcentage du montant total (hors caution)
        $rentalDeposit = round($totalAmount * ($depositPercentage / 100), 2);
        
        // Montant selon le type de paiement
        $amount = match ($validated['payment_type']) {
            'deposit' => $rentalDeposit + $securityDeposit, // Acompte + caution
            'balance' => max(0, $totalAmount - $rentalDeposit), // Solde restant
            default => $totalAmount + $securityDeposit, // Total + caution
        };

        if ($validated['payment_type'] === 'deposit' && $paymentRequirement !== 'deposit') {
            return response()->json(['error' => 'Acompte non autorisé pour cette location.'], 422);
        }

        if ($validated['payment_type'] === 'balance' && !$this->isDepositPaidStatus($paymentStatus)) {
            return response()->json(['error' => 'Solde indisponible. L\'acompte doit être payé d\'abord.'], 422);
        }

        if ($validated['payment_type'] === 'deposit' && $this->isDepositPaidStatus($paymentStatus)) {
            return response()->json(['error' => 'Acompte déjà payé pour cette location.'], 422);
        }

        if ($amount <= 0) {
            return response()->json(['error' => 'Montant invalide'], 422);
        }

        $txType = match ($validated['payment_type']) {
            'deposit' => 'deposit',
            'balance' => 'balance',
            default => 'payment',
        };
        $rentalAmountForPayment = match ($validated['payment_type']) {
            'deposit' => $rentalDeposit,
            'balance' => max(0, $totalAmount - $rentalDeposit),
            default => $totalAmount,
        };

        $description = ucfirst($validated['payment_type']) . " payment for Rental Request #{$request->request_number}";
        if ($securityDeposit > 0 && in_array($validated['payment_type'], ['deposit', 'full'])) {
            $description .= " (incl. caution {$securityDeposit}€)";
        }
        
        $metadata = [
            'user_id' => (string) auth()->id(),
            'rental_request_id' => (string) $request->id,
            'payment_type' => $validated['payment_type'],
            'tx_type' => $txType,
            'security_deposit' => (string) $securityDeposit,
            'rental_amount' => (string) $rentalAmountForPayment,
            'payment_requirement' => $paymentRequirement,
            // Force un nouvel idempotency scope à chaque création d'intent pour éviter
            // la réutilisation d'un ancien PaymentIntent terminal (source de 400 Elements).
            'intent_nonce' => (string) Str::uuid(),
        ];

        try {
            // Paiement par carte uniquement (inclut Apple Pay / Google Pay via wallet "card").
            // Cela évite certains cas de 400 Stripe Elements sur des montants/conditions incompatibles (ex: Klarna).
            $extraParams = [
                'payment_method_types' => ['card'],
            ];

            // Utiliser escrow pour les locations (blocage des fonds)
            $intent = $this->stripe->createEscrowPaymentIntent(
                auth()->user(), 
                $amount, 
                $description, 
                array_merge($metadata, ['connected_account_id' => $connectedAccountId]),
                $connectedAccountId,
                $extraParams
            );

            // Toujours créer une trace locale dès la création d'intent.
            // Cela évite les "paiements fantômes" (débit perçu côté client mais aucune trace locale).
            // Le statut sera ensuite basculé en "paid" par confirm/webhook.
            PaymentTransaction::updateOrCreate(
                ['stripe_payment_intent_id' => (string) $intent->id],
                [
                    'user_id' => (int) auth()->id(),
                    'equipment_rental_id' => (int) $request->id,
                    'amount' => (float) $amount,
                    'currency' => strtolower((string) ($intent->currency ?? 'eur')),
                    'status' => 'pending',
                    'type' => $txType,
                    'provider' => 'stripe',
                    'transaction_id' => (string) $intent->id,
                    'description' => $description,
                    'metadata' => array_merge($metadata, [
                        'payment_intent_id' => (string) $intent->id,
                    ]),
                ]
            );

            return response()->json([
                'clientSecret' => $intent->client_secret,
                'paymentIntentId' => $intent->id,
                'amount' => $amount,
                'security_deposit' => $securityDeposit,
                'rental_amount' => $rentalAmountForPayment,
            ]);
        } catch (\Exception $e) {
            Log::error('Equipment rental createIntent failed: ' . $e->getMessage(), [
                'user' => auth()->id(),
                'rental_request_id' => $request->id ?? null,
            ]);
            return response()->json(['error' => 'Impossible de créer le paiement. Veuillez réessayer.'], 422);
        }
    }

    public function confirm(Request $httpRequest, EquipmentRentalRequest $request)
    {
        $clientId = auth()->user()?->client?->id;
        if (!$clientId || (int) $request->client_id !== (int) $clientId) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        if (function_exists('payment_feature_enabled') && !payment_feature_enabled()) {
            return response()->json(['error' => 'Le paiement en ligne est désactivé pour le moment.'], 422);
        }

        $validated = $httpRequest->validate([
            'payment_intent_id' => 'required|string',
            'payment_type' => 'required|in:full,deposit,balance',
            'terms_version' => 'nullable|string|max:32',
            'terms_accepted_at' => 'nullable|date',
        ]);

        // Conserver une trace de l'acceptation des conditions (si fournie)
        if (
            empty($request->payment_terms_accepted_at) &&
            !empty($validated['terms_version']) &&
            !empty($validated['terms_accepted_at'])
        ) {
            $request->forceFill([
                'payment_terms_version' => $validated['terms_version'],
                'payment_terms_accepted_at' => $validated['terms_accepted_at'],
                'payment_terms_ip' => $httpRequest->ip(),
            ])->save();
        }

        try {
            $request->loadMissing('prestataire', 'equipment');
            
            // IMPORTANT: Pour les paiements escrow, le PaymentIntent est créé sur le compte PLATEFORME
            // donc on ne passe PAS le connectedAccountId lors de la récupération
            $paymentIntent = $this->stripe->retrievePaymentIntent($validated['payment_intent_id'], null);

            if (!in_array((string) $paymentIntent->status, ['succeeded', 'requires_capture'], true)) {
                return response()->json([
                    'success' => false,
                    'status' => $paymentIntent->status,
                    'message' => 'Paiement en cours de traitement. Veuillez patienter.',
                ]);
            }

            $metadata = PaymentMetadataNormalizer::normalize(
                (array) ($paymentIntent->metadata?->toArray() ?? [])
            );
            if (($metadata['user_id'] ?? null) && (int) $metadata['user_id'] !== (int) auth()->id()) {
                return response()->json(['error' => 'Paiement non autorisé (utilisateur).'], 422);
            }
            if (($metadata['rental_request_id'] ?? null) && (int) $metadata['rental_request_id'] !== (int) $request->id) {
                return response()->json(['error' => 'Paiement non autorisé (location).'], 422);
            }
            if (($metadata['payment_type'] ?? null) && (string) $metadata['payment_type'] !== (string) $validated['payment_type']) {
                return response()->json(['error' => 'Type de paiement invalide.'], 422);
            }

            // Récupérer les montants depuis les metadata
            $securityDeposit = (float) ($metadata['security_deposit'] ?? $request->equipment->security_deposit ?? 0);
            $rentalAmount = (float) ($metadata['rental_amount'] ?? $this->resolveRentalAmount($request));
            $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
                ? normalize_payment_requirement_for_mode($metadata['payment_requirement'] ?? $request->equipment->payment_requirement ?? 'none')
                : ($metadata['payment_requirement'] ?? $request->equipment->payment_requirement ?? 'none');

            $receivedCents = (int) ($paymentIntent->amount_received ?? $paymentIntent->amount ?? 0);
            $receivedAmount = $receivedCents / 100;

            // SÉCURITÉ: Valider le montant reçu contre le montant attendu
            $depositPercentage = (float) ($request->equipment->deposit_percentage ?? 30);
            $expectedTotal = $this->resolveRentalAmount($request);
            $expectedDeposit = round($expectedTotal * ($depositPercentage / 100), 2);
            $expectedAmount = match ($validated['payment_type']) {
                'deposit' => $expectedDeposit + $securityDeposit,
                'balance' => max(0, $expectedTotal - $expectedDeposit),
                default => $expectedTotal + $securityDeposit,
            };
            $expectedCents = (int) round($expectedAmount * 100);

            if (abs($receivedCents - $expectedCents) > 1) {
                Log::critical('Equipment rental payment amount mismatch!', [
                    'rental_request_id' => $request->id,
                    'expected_cents' => $expectedCents,
                    'received_cents' => $receivedCents,
                    'payment_type' => $validated['payment_type'],
                    'user_id' => auth()->id(),
                ]);
                return response()->json([
                    'error' => 'Montant du paiement incorrect. Veuillez réessayer.',
                ], 422);
            }

            $txType = match ($validated['payment_type']) {
                'deposit' => 'deposit',
                'balance' => 'balance',
                default => 'payment',
            };

            /** @var PaymentTransaction $transaction */
            $transaction = DB::transaction(function () use ($paymentIntent, $request, $txType, $validated, $securityDeposit, $rentalAmount, $receivedAmount, $paymentRequirement) {
                $existing = PaymentTransaction::where('stripe_payment_intent_id', $paymentIntent->id)->lockForUpdate()->first();
                $transaction = $existing ?: $this->stripe->recordPayment($paymentIntent, null, $request->id, $txType);

                PaymentAllocation::updateOrCreate(
                    [
                        'payment_transaction_id' => $transaction->id,
                        'payable_type' => $request->getMorphClass(),
                        'payable_id' => $request->id,
                        'type' => $txType,
                    ],
                    [
                        'amount' => (float) $transaction->amount,
                        'currency' => $transaction->currency ?? 'eur',
                    ]
                );

                // Ensure there is an EquipmentRental row linked to this request
                $request->load('rental');
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
                        'security_deposit' => $securityDeposit,
                        'total_amount' => $request->total_amount ?? 0,
                        'final_amount' => $request->final_amount ?? $request->total_amount ?? 0,
                        'pickup_address' => $request->pickup_address,
                        'status' => 'confirmed',
                        'payment_status' => 'pending',
                    ]);
                }

                // Mettre à jour le statut de paiement selon le type
                $this->applyRentalPaymentStatus($rental, $validated['payment_type']);

                // Mettre à jour la demande de location
                if (in_array($request->status, ['accepted', 'pending'], true)) {
                    $request->status = 'confirmed';
                    $request->confirmed_at = now();
                    $request->save();
                }

                return $transaction;
            });

            // ESCROW: Créer l'entrée escrow pour bloquer les fonds (location + caution)
            try {
                $this->escrowService->createEscrow(
                    $request,                           // escrowable
                    auth()->id(),                       // clientId
                    $request->prestataire_id,           // prestataireId
                    $rentalAmount,                      // amount (hors caution)
                    $securityDeposit,                   // depositAmount (caution)
                    $validated['payment_intent_id'],    // stripePaymentIntentId
                    null,                               // platformFeeOverride
                    [                                   // metadata
                        'type' => 'equipment_rental',
                        'payment_type' => $validated['payment_type'],
                        'payment_requirement' => $paymentRequirement,
                        'request_number' => $request->request_number,
                        'equipment_id' => $request->equipment_id,
                        'transaction_id' => $transaction->id,
                    ]
                );
                
                Log::info("Escrow créé pour location équipement #{$request->request_number}", [
                    'rental_request_id' => $request->id,
                    'rental_amount' => $rentalAmount,
                    'security_deposit' => $securityDeposit,
                ]);
            } catch (\Exception $e) {
                Log::error('CRITICAL: Failed to create escrow for equipment rental after payment confirmed (audit 2.10): ' . $e->getMessage(), [
                    'rental_request_id' => $request->id,
                ]);
                throw $e;
            }

            // Générer les factures pour la location
            try {
                $this->invoiceService->generateForEquipmentRental($request, $transaction);
            } catch (\Exception $e) {
                Log::warning('Failed to generate invoice for equipment rental: ' . $e->getMessage());
            }

            // Envoyer une notification au prestataire
            try {
                $request->loadMissing('prestataire.user', 'client.user');
                if ($request->prestataire?->user) {
                    // Notification de paiement reçu
                    $request->prestataire->user->notify(new \App\Notifications\EquipmentRentalPaymentReceived($request, $transaction));
                    
                    // Notification de nouvelle demande (car non envoyée avant le paiement)
                    $request->prestataire->user->notify(new \App\Notifications\SimpleNewEquipmentRentalRequestNotification($request));
                }
            } catch (\Exception $e) {
                Log::warning('Failed to notify prestataire of rental payment: ' . $e->getMessage());
            }

            Log::info('Equipment rental payment confirmed', [
                'rental_request_id' => $request->id,
                'transaction_id' => $transaction->id,
                'user_id' => auth()->id(),
                'payment_type' => $validated['payment_type'],
                'escrow_created' => true,
            ]);

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->id,
                'escrow' => true,
                'message' => $validated['payment_type'] === 'deposit' 
                    ? 'Acompte et caution payés. Le solde sera à régler avant le retrait.'
                    : 'Paiement confirmé. Les fonds sont bloqués jusqu\'au retour de l\'équipement.',
            ]);
        } catch (\Exception $e) {
            Log::error('Equipment rental payment confirmation failed', [
                'rental_request_id' => $request->id ?? null,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return response()->json(['error' => 'Erreur lors de la confirmation du paiement.'], 422);
        }
    }

    /**
     * Compatibilité statuts paiement location (legacy/new enum).
     * Certains environnements stockent pending/partial/paid, d'autres pending/deposit_paid/full_paid.
     */
    private function applyRentalPaymentStatus(EquipmentRental $rental, string $paymentType): void
    {
        $primary = $paymentType === 'deposit' ? 'partial' : 'paid';
        $fallback = $paymentType === 'deposit' ? 'deposit_paid' : 'full_paid';

        $lastError = null;
        foreach (array_unique([$primary, $fallback]) as $candidateStatus) {
            try {
                $rental->payment_status = $candidateStatus;
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

    private function isDepositPaidStatus(?string $status): bool
    {
        return in_array((string) $status, ['partial', 'deposit_paid'], true);
    }

    private function isFullyPaidStatus(?string $status): bool
    {
        return in_array((string) $status, ['paid', 'full_paid', 'completed'], true);
    }

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

    private function findLatestSuccessfulRentalTransaction(EquipmentRentalRequest $request): ?PaymentTransaction
    {
        $requestId = (int) $request->id;
        $requestNumber = trim((string) ($request->request_number ?? ''));
        $statusCandidates = ['paid', 'held', 'succeeded', 'completed'];

        $baseQuery = PaymentTransaction::query()
            ->where('user_id', auth()->id())
            ->whereIn('status', $statusCandidates);

        $direct = (clone $baseQuery)
            ->where('equipment_rental_id', $requestId)
            ->latest('id')
            ->first();
        if ($direct) {
            return $direct;
        }

        $recent = (clone $baseQuery)
            ->whereNotNull('metadata')
            ->latest('id')
            ->limit(250)
            ->get();

        foreach ($recent as $transaction) {
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

    private function findLatestPendingRentalTransaction(EquipmentRentalRequest $request): ?PaymentTransaction
    {
        $requestId = (int) $request->id;
        $requestNumber = trim((string) ($request->request_number ?? ''));
        $statusCandidates = ['pending', 'processing'];

        $baseQuery = PaymentTransaction::query()
            ->where('user_id', auth()->id())
            ->whereIn('status', $statusCandidates);

        $direct = (clone $baseQuery)
            ->where('equipment_rental_id', $requestId)
            ->latest('id')
            ->first();
        if ($direct) {
            return $direct;
        }

        $directWithoutUser = PaymentTransaction::query()
            ->whereIn('status', $statusCandidates)
            ->where('equipment_rental_id', $requestId)
            ->latest('id')
            ->first();
        if ($directWithoutUser) {
            return $directWithoutUser;
        }

        $recent = (clone $baseQuery)
            ->whereNotNull('metadata')
            ->latest('id')
            ->limit(250)
            ->get();

        foreach ($recent as $transaction) {
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
            ->limit(300)
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

            $piMeta = PaymentMetadataNormalizer::normalize(
                $this->arrayFromJsonOrObject($paymentIntent->metadata ?? [])
            );
            $metaRequestId = (int) ($piMeta['rental_request_id'] ?? 0);
            if ($metaRequestId > 0 && $metaRequestId !== (int) $request->id) {
                return null;
            }

            $existingMeta = PaymentMetadataNormalizer::normalize((array) ($pending->metadata ?? []));
            $rawType = (string) ($piMeta['tx_type'] ?? ($piMeta['payment_type'] ?? ($existingMeta['tx_type'] ?? ($existingMeta['payment_type'] ?? $pending->type))));
            $txType = $this->normalizeTransactionType($rawType);
            if ($txType === '') {
                $txType = 'payment';
            }

            $amount = ((int) ($paymentIntent->amount_received ?? $paymentIntent->amount ?? 0)) / 100;
            $pending->user_id = (int) auth()->id();
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

            Log::warning('Payment controller: promoted pending rental transaction to paid from Stripe intent poll', [
                'request_id' => $request->id,
                'transaction_id' => $pending->id,
                'payment_intent_id' => $piId,
            ]);

            return $pending->fresh();
        } catch (\Throwable $e) {
            Log::warning('Payment controller: unable to promote pending rental transaction from Stripe', [
                'request_id' => $request->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Récupère un PaymentIntent Stripe pour une demande de location (audit 2.9).
     */
    private function retrievePaymentIntentForRequest(EquipmentRentalRequest $request, string $paymentIntentId): ?\Stripe\PaymentIntent
    {
        try {
            return $this->stripe->retrievePaymentIntent($paymentIntentId, null);
        } catch (\Throwable $e) {
            Log::warning('retrievePaymentIntentForRequest failed', [
                'request_id' => $request->id,
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function backfillTransactionFromEscrow(EquipmentRentalRequest $request): ?PaymentTransaction
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('escrow_transactions')) {
                return null;
            }

            $escrow = DB::table('escrow_transactions')
                ->where('escrowable_id', (int) $request->id)
                ->where('escrowable_type', 'like', '%EquipmentRentalRequest%')
                ->whereNotNull('stripe_payment_intent_id')
                ->orderByDesc('id')
                ->first();

            if (!$escrow) {
                return null;
            }

            $row = (array) $escrow;
            $piId = (string) ($row['stripe_payment_intent_id'] ?? '');
            if ($piId === '') {
                return null;
            }

            $existing = PaymentTransaction::where('stripe_payment_intent_id', $piId)->first();
            if ($existing) {
                return $existing;
            }

            $meta = PaymentMetadataNormalizer::normalize(
                $this->arrayFromJsonOrObject($row['metadata'] ?? null)
            );
            $amount = (float) ($row['total_amount'] ?? ($row['amount'] ?? 0)) + (float) ($row['deposit_amount'] ?? 0);
            if ($amount <= 0) {
                $amount = $this->resolveRentalAmount($request)
                    + (float) ($request->equipment?->security_deposit ?? $request->security_deposit ?? 0);
            }

            if ($amount <= 0) {
                return null;
            }

            $status = strtolower((string) ($row['status'] ?? ''));
            $txStatus = $status === 'refunded' ? 'refunded' : 'paid';
            $txType = $this->normalizeTransactionType($meta['payment_type'] ?? 'payment');
            $paidAt = $row['paid_at'] ?? ($row['held_at'] ?? now());

            $created = PaymentTransaction::systemCreate([
                'user_id' => (int) auth()->id(),
                'stripe_payment_intent_id' => $piId,
                'equipment_rental_id' => (int) $request->id,
                'amount' => $amount,
                'type' => $txType,
                'currency' => strtolower((string) ($row['currency'] ?? 'eur')),
                'status' => $txStatus,
                'provider' => 'stripe',
                'transaction_id' => $piId,
                'description' => 'Paiement location #' . ($request->request_number ?: $request->id),
                'metadata' => array_merge($meta, [
                    'rental_request_id' => (string) $request->id,
                    'escrow_id' => (string) ($row['id'] ?? ''),
                    'reconciled_from' => 'escrow_transactions',
                ]),
                'paid_at' => $paidAt,
            ]);

            Log::warning('Equipment rental payment backfilled from escrow in payment controller', [
                'request_id' => $request->id,
                'transaction_id' => $created->id,
                'pi' => $piId,
            ]);

            return $created;
        } catch (\Throwable $e) {
            Log::warning('Equipment rental escrow backfill skipped in payment controller', [
                'request_id' => $request->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function reconcileRequestStateFromTransaction(EquipmentRentalRequest $request, PaymentTransaction $transaction): void
    {
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

        $meta = PaymentMetadataNormalizer::normalize(
            $this->arrayFromJsonOrObject($transaction->metadata)
        );
        $paymentType = strtolower((string) ($meta['payment_type'] ?? ($meta['tx_type'] ?? $transaction->type ?? 'full')));
        $normalizedPaymentType = in_array($paymentType, ['deposit', 'balance', 'full', 'payment'], true) ? $paymentType : 'full';

        $this->applyRentalPaymentStatus($rental, $normalizedPaymentType === 'deposit' ? 'deposit' : 'full');

        if (in_array((string) $request->status, ['accepted', 'pending'], true)) {
            $request->status = 'confirmed';
            $request->confirmed_at = $request->confirmed_at ?? now();
            $request->save();
        }
    }

    private function resolvePaidTransactionAndReconcile(EquipmentRentalRequest $request): ?PaymentTransaction
    {
        $promoted = $this->promotePendingRentalTransactionFromStripe($request);
        if ($promoted) {
            $this->reconcileRequestStateFromTransaction($request, $promoted);
            return $promoted;
        }

        $transaction = $this->findLatestSuccessfulRentalTransaction($request);
        if (!$transaction) {
            $transaction = $this->backfillTransactionFromEscrow($request);
        }

        if ($transaction) {
            $this->reconcileRequestStateFromTransaction($request, $transaction);
        }

        return $transaction;
    }

    /**
     * Montant location hors caution.
     * total_amount est la source de vérité; final_amount reste un fallback legacy.
     */
    private function resolveRentalAmount(EquipmentRentalRequest $request): float
    {
        $total = (float) ($request->total_amount ?? 0);
        if ($total > 0) {
            return $total;
        }

        return max(0, (float) ($request->final_amount ?? 0));
    }
}
