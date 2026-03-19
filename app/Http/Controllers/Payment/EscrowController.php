<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\ClientDisputeRequest;
use App\Http\Requests\Payment\PrestataireConfirmRequest;
use App\Http\Requests\Payment\DisputeUrgentSaleRequest;
use App\Http\Requests\Payment\ConfirmUrgentSaleReturnRequest;
use App\Services\EscrowService;
use App\Services\EquipmentRentalPaymentSyncService;
use App\Services\MutualRatingService;
use App\Services\ShippingService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\TableExistenceCache;

class EscrowController extends Controller
{
    protected EscrowService $escrowService;
    protected EquipmentRentalPaymentSyncService $equipmentRentalPaymentSyncService;
    protected MutualRatingService $ratingService;
    protected ShippingService $shippingService;

    public function __construct(
        EscrowService $escrowService,
        EquipmentRentalPaymentSyncService $equipmentRentalPaymentSyncService,
        MutualRatingService $ratingService,
        ShippingService $shippingService
    ) {
        $this->escrowService = $escrowService;
        $this->equipmentRentalPaymentSyncService = $equipmentRentalPaymentSyncService;
        $this->ratingService = $ratingService;
        $this->shippingService = $shippingService;
    }

    private function emptyPaginator(int $perPage = 15): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            collect(),
            0,
            $perPage,
            Paginator::resolveCurrentPage() ?: 1,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }

    private function renderClientEscrowIndexResponse(
        LengthAwarePaginator $escrows,
        ?string $errorMessage = null
    ) {
        try {
            return response(view('client.escrow.index', [
                'escrows' => $escrows,
            ])->render());
        } catch (\Throwable $e) {
            \Log::error('Escrow clientIndex view render failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            try {
                return response(view('client.escrow.index-fallback', [
                    'escrows' => $escrows,
                    'errorMessage' => $errorMessage,
                ])->render());
            } catch (\Throwable $fallbackError) {
                \Log::critical('Escrow clientIndex fallback render failed', [
                    'user_id' => Auth::id(),
                    'error' => $fallbackError->getMessage(),
                ]);

                return redirect()
                    ->route('client.dashboard')
                    ->with('error', $errorMessage ?: 'La page paiements sécurisés est temporairement indisponible.');
            }
        }
    }

    private function renderClientEscrowShowResponse(array $data)
    {
        try {
            return response(view('client.escrow.show', $data)->render());
        } catch (\Throwable $e) {
            \Log::error('Escrow clientShow view render failed', [
                'user_id' => Auth::id(),
                'escrow_id' => $data['escrow']->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('client.escrow.index')
                ->with('error', 'Les details du paiement securise n\'ont pas pu etre affiches.');
        }
    }

    private function applyClientEscrowOwnershipScope($query, int $userId, ?int $clientId): void
    {
        $escrowTableExists = TableExistenceCache::has('escrow_transactions');
        $hasUserIdColumn = $escrowTableExists && Schema::hasColumn('escrow_transactions', 'user_id');
        $hasClientIdColumn = $escrowTableExists && Schema::hasColumn('escrow_transactions', 'client_id');
        $hasEscrowableTypeColumn = $escrowTableExists && Schema::hasColumn('escrow_transactions', 'escrowable_type');
        $hasEscrowableIdColumn = $escrowTableExists && Schema::hasColumn('escrow_transactions', 'escrowable_id');
        $hasEquipmentRequestsTable = TableExistenceCache::has('equipment_rental_requests');
        $hasEquipmentClientIdColumn = $hasEquipmentRequestsTable && Schema::hasColumn('equipment_rental_requests', 'client_id');

        $query->where(function ($scopedQuery) use (
            $userId,
            $clientId,
            $hasUserIdColumn,
            $hasClientIdColumn,
            $hasEscrowableTypeColumn,
            $hasEscrowableIdColumn,
            $hasEquipmentRequestsTable,
            $hasEquipmentClientIdColumn
        ) {
            $hasCondition = false;

            if ($hasUserIdColumn) {
                $scopedQuery->where('et.user_id', $userId);
                $hasCondition = true;
            }

            if ($hasClientIdColumn) {
                if (!empty($clientId)) {
                    if ($hasCondition) {
                        $scopedQuery->orWhere('et.client_id', $clientId);
                    } else {
                        $scopedQuery->where('et.client_id', $clientId);
                    }
                    $hasCondition = true;
                }

                if (!$hasUserIdColumn) {
                    if ($hasCondition) {
                        $scopedQuery->orWhere('et.client_id', $userId);
                    } else {
                        $scopedQuery->where('et.client_id', $userId);
                    }
                    $hasCondition = true;
                }

                if (
                    !empty($clientId)
                    && $hasEscrowableTypeColumn
                    && $hasEscrowableIdColumn
                    && $hasEquipmentRequestsTable
                    && $hasEquipmentClientIdColumn
                ) {
                    $method = $hasCondition ? 'orWhere' : 'where';
                    $scopedQuery->{$method}(function ($ownedRequestQuery) use ($clientId) {
                        $ownedRequestQuery
                            ->where('et.escrowable_type', 'like', '%EquipmentRentalRequest%')
                            ->whereIn('et.escrowable_id', function ($sub) use ($clientId) {
                                $sub->select('id')
                                    ->from('equipment_rental_requests')
                                    ->where('client_id', (int) $clientId);
                            });
                    });
                    $hasCondition = true;
                }
            }

            if (!$hasCondition) {
                $scopedQuery->whereRaw('1 = 0');
            }
        });
    }

    private function currentUserOwnsUrgentSaleEscrow(object $escrow, int $userId): bool
    {
        $type = (string) ($escrow->escrowable_type ?? '');
        if (!str_contains($type, 'UrgentSale')) {
            return false;
        }

        // Strong ownership check for urgent-sale purchase escrows.
        if (str_contains($type, 'UrgentSalePurchase') && !empty($escrow->escrowable_id)) {
            $purchase = DB::table('urgent_sale_purchases')
                ->where('id', $escrow->escrowable_id)
                ->select('buyer_user_id')
                ->first();

            if ($purchase) {
                return (int) ($purchase->buyer_user_id ?? 0) === $userId;
            }
        }

        // Fallback for legacy rows: only allow strict user id match (no orWhere on client model id).
        return (int) ($escrow->client_id ?? 0) === $userId;
    }

    // =========================================================================
    // VUES CLIENT
    // =========================================================================

    /**
     * Liste des transactions escrow du client
     */
    public function clientIndex()
    {
        try {
            $user = Auth::user();
            $client = $user->client;

            try {
                $this->equipmentRentalPaymentSyncService->syncForClient($user);
            } catch (\Throwable $e) {
                \Log::warning('Escrow clientIndex rental payment sync warning', [
                    'user_id' => $user->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }

            // SÉCURITÉ: Utiliser uniquement client_id pour éviter les collisions
            // entre user.id et client.id d'utilisateurs différents
            $userId = $user->id;
            $clientId = $client?->id;

            if (!TableExistenceCache::has('escrow_transactions')) {
                return $this->renderClientEscrowIndexResponse($this->emptyPaginator());
            }

            try {
                $escrowsQuery = DB::table('escrow_transactions as et');
                $this->applyClientEscrowOwnershipScope($escrowsQuery, $userId, $clientId);

                $escrows = $escrowsQuery
                    ->orderBy('et.created_at', 'desc')
                    ->paginate(15);
            } catch (\Throwable $e) {
                \Log::error('Escrow clientIndex failed', [
                    'user_id' => $user->id ?? null,
                    'client_id' => $clientId,
                    'error' => $e->getMessage(),
                ]);

                return $this->renderClientEscrowIndexResponse(
                    $this->emptyPaginator(),
                    'Impossible de charger vos paiements securises pour le moment.'
                );
            }

            return $this->renderClientEscrowIndexResponse($escrows);
        } catch (\Throwable $e) {
            \Log::error('Escrow clientIndex fatal', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return $this->renderClientEscrowIndexResponse(
                $this->emptyPaginator(),
                'Impossible de charger vos paiements securises pour le moment.'
            );
        }
    }

    /**
     * Détails d'une transaction escrow
     */
    public function clientShow(int $escrowId)
    {
        try {
            $user = Auth::user();
            $client = $user->client;

        try {
            $this->equipmentRentalPaymentSyncService->syncForClient($user);
        } catch (\Throwable $e) {
            \Log::warning('Escrow clientShow rental payment sync warning', [
                'user_id' => $user->id ?? null,
                'escrow_id' => $escrowId,
                'error' => $e->getMessage(),
            ]);
        }

        // Pour un prestataire en mode client, $client peut être null
        $clientId = $client ? $client->id : null;
        $userId = $user->id;

        if (!TableExistenceCache::has('escrow_transactions')) {
            return redirect()->route('client.escrow.index')
                ->with('error', 'Aucune transaction sécurisée disponible.');
        }

        $escrowQuery = DB::table('escrow_transactions as et')
            ->where('et.id', $escrowId);

        $this->applyClientEscrowOwnershipScope($escrowQuery, $userId, $clientId);

        $escrow = $escrowQuery->first();

        if (!$escrow) {
            return redirect()->route('client.escrow.index')
                ->with('error', 'Transaction non trouvée');
        }

        // Récupérer l'objet lié (booking, rental, etc.)
        $relatedItem = $this->getRelatedItem($escrow);
        
        // Pour les ventes urgentes, récupérer aussi le produit
        $urgentSaleProduct = null;
        if ($relatedItem && str_contains((string) ($escrow->escrowable_type ?? ''), 'UrgentSale')) {
            try {
                $urgentSaleProduct = DB::table('urgent_sales')->find($relatedItem->urgent_sale_id ?? null);
            } catch (\Throwable $e) {
                // Ignorer si la table n'existe pas
            }
        }
        
        // Récupérer le prestataire (vendeur)
        $prestataire = null;
        if ($escrow->prestataire_id) {
            try {
                $prestataire = DB::table('prestataires')
                    ->leftJoin('users', 'prestataires.user_id', '=', 'users.id')
                    ->where('prestataires.id', $escrow->prestataire_id)
                    ->select(
                        'prestataires.id',
                        'prestataires.user_id',
                        'prestataires.company_name',
                        'prestataires.photo',
                        'prestataires.profile_image',
                        'prestataires.rating_average',
                        'prestataires.total_reviews',
                        'users.name as user_name'
                    )
                    ->first();
            } catch (\Throwable $e) {
                \Log::error('Erreur récupération prestataire escrow: ' . $e->getMessage());
            }
        }
        
        // Récupérer le shipment si existe (vérifier si la colonne escrow_id existe)
        $shipment = null;
        try {
            if (Schema::hasColumn('shipments', 'escrow_id')) {
                $shipment = DB::table('shipments')
                    ->where('escrow_id', $escrowId)
                    ->first();
            }
        } catch (\Exception $e) {
            // Ignorer si la table ou colonne n'existe pas
        }

        $dispute = null;
        try {
            if (TableExistenceCache::has('escrow_disputes')) {
                $dispute = DB::table('escrow_disputes')
                    ->where('escrow_id', $escrowId)
                    ->orderByDesc('created_at')
                    ->first();
            }
        } catch (\Throwable $e) {
            // Ignorer si la table n'existe pas
        }

        // Vérifier si peut noter
        $canRate = false;
        if ($escrow->status === 'released') {
            try {
                // Vérifier si la table mutual_ratings existe
                if (!TableExistenceCache::has('mutual_ratings')) {
                    $canRate = true; // Pas de table = pas encore noté
                } else {
                    // Vérifier qu'on n'a pas déjà noté
                    $ratableType = !empty($escrow->escrowable_type) ? $escrow->escrowable_type : 'escrow';
                    $ratableId = !empty($escrow->escrowable_id) ? $escrow->escrowable_id : $escrow->id;
                    
                    $alreadyRated = DB::table('mutual_ratings')
                        ->where('rater_id', $user->id)
                        ->where('ratable_type', $ratableType)
                        ->where('ratable_id', $ratableId)
                        ->exists();
                    
                    $canRate = !$alreadyRated;
                }
            } catch (\Throwable $e) {
                // En cas d'erreur, permettre la notation par défaut pour escrow released
                \Log::warning('canRate check failed: ' . $e->getMessage());
                $canRate = true;
            }
        }

            return $this->renderClientEscrowShowResponse(compact(
                'escrow',
                'relatedItem',
                'urgentSaleProduct',
                'prestataire',
                'shipment',
                'dispute',
                'canRate'
            ));
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('client.escrow.index')
                ->with('error', 'Erreur lors de l\'affichage de la transaction');
        }
    }

    /**
     * Client confirme la réception/satisfaction
     */
    public function clientConfirm(Request $request, int $escrowId)
    {
        $user = Auth::user();
        $client = $user->client;
        $clientId = $client ? $client->id : null;
        $userId = $user->id;

        $allowedIds = array_filter([$userId, $clientId]);
        $escrow = DB::table('escrow_transactions')
            ->where('id', $escrowId)
            ->whereIn('client_id', $allowedIds)
            ->first();

        if (!$escrow) {
            return redirect()->back()->with('error', 'Transaction non trouvée');
        }

        if (!in_array((string) $escrow->status, ['pending', 'held'], true)) {
            return redirect()->back()->with('error', 'Cette transaction ne peut plus être confirmée');
        }

        $result = $this->escrowService->clientConfirm($escrowId);

        if ($result) {
            $escrowType = (string) ($escrow->escrowable_type ?? '');
            $isEquipmentRental = str_contains($escrowType, 'EquipmentRental');
            $message = $isEquipmentRental
                ? 'Confirmation enregistrée. Le prestataire doit maintenant valider le retour.'
                : 'Merci ! Le paiement a été libéré au prestataire.';

            return redirect()->back()
                ->with('success', $message);
        }

        return redirect()->back()->with('error', 'Confirmation enregistrée, mais la libération automatique n\'a pas pu être finalisée. Veuillez réessayer.');
    }

    /**
     * Client signale un problème (ouvre un litige)
     */
    public function clientDispute(ClientDisputeRequest $request, int $escrowId)
    {

        $user = Auth::user();
        $client = $user->client;
        $clientId = $client ? $client->id : null;
        $userId = $user->id;

        $escrow = DB::table('escrow_transactions')
            ->where('id', $escrowId)
            ->where('client_id', $userId)
            ->first();

        if (!$escrow) {
            return redirect()->back()->with('error', 'Transaction non trouvée');
        }

        // Télécharger les preuves
        $evidencePaths = [];
        if ($request->hasFile('evidence')) {
            foreach ($request->file('evidence') as $file) {
                $path = $file->store('disputes/' . $escrowId, 'public');
                $evidencePaths[] = $path;
            }
        }

        $disputeId = $this->escrowService->openDispute(
            $escrowId,
            $user->id,
            $request->reason,
            $request->description,
            $evidencePaths
        );

        if ($disputeId === -1) {
            // M4: La fenêtre de litige est expirée — message explicite
            $disputeWindow = (int) DB::table('settings')->where('key', 'escrow_dispute_window_hours')->value('value') ?: 48;
            return redirect()->back()->with('error', 'La fenêtre de litige de ' . $disputeWindow . ' heures est expirée. Contactez le support pour toute réclamation.');
        }

        if ($disputeId) {
            $successMessage = 'Votre réclamation a été enregistrée. Pensez à contacter le vendeur/prestataire et à conserver vos preuves (photos, échanges, etc.).';

            if ($request->reason === 'service_not_provided') {
                $successMessage = 'Votre réclamation a été enregistrée. Si le paiement est encore bloqué, le remboursement est déclenché automatiquement via Stripe.';
            }

            return redirect()->route('client.escrow.show', $escrowId)
                ->with('success', $successMessage);
        }

        return redirect()->back()->with('error', 'Erreur lors de l\'ouverture du litige');
    }

    /**
     * Client note le prestataire
     */
    public function clientRate(Request $request, int $escrowId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'would_recommend' => 'required|boolean',
        ]);

        $user = Auth::user();
        $client = $user->client;
        $clientId = $client ? $client->id : null;
        $userId = $user->id;

        $escrow = DB::table('escrow_transactions')
            ->where('id', $escrowId)
            ->where('client_id', $userId)
            ->where('status', 'released')
            ->first();

        if (!$escrow) {
            return redirect()->back()->with('error', 'Transaction non trouvée ou non terminée');
        }

        // Déterminer le type et ID pour la notation
        $ratableType = $escrow->escrowable_type;
        $ratableId = $escrow->escrowable_id;
        
        // Si escrowable n'est pas défini, utiliser l'escrow lui-même
        if (empty($ratableType) || empty($ratableId)) {
            $ratableType = 'escrow';
            $ratableId = $escrow->id;
        }

        $ratingId = $this->ratingService->createRating(
            raterId: $user->id,
            ratedId: $escrow->prestataire_id,
            raterType: 'client',
            ratableType: $ratableType,
            ratableId: $ratableId,
            rating: $request->rating,
            comment: $request->comment,
            wouldRecommend: $request->would_recommend
        );

        if ($ratingId) {
            return redirect()->back()->with('success', 'Merci pour votre avis !');
        }

        return redirect()->back()->with('error', 'Vous avez déjà noté cette transaction');
    }

    // =========================================================================
    // VUES PRESTATAIRE
    // =========================================================================

    /**
     * Liste des transactions escrow du prestataire
     */
    public function prestataireIndex()
    {
        try {
            $user = Auth::user();
            $prestataire = $user->prestataire;

            if (!$prestataire) {
                return redirect()->route('home')->with('error', 'Profil prestataire requis');
            }

            try {
                $this->equipmentRentalPaymentSyncService->syncForPrestataire($prestataire);
            } catch (\Throwable $e) {
                \Log::warning('Escrow prestataireIndex rental payment sync warning', [
                    'prestataire_id' => $prestataire->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }

            $escrows = DB::table('escrow_transactions')
                ->where('prestataire_id', $prestataire->id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            // Check once if shipments table has escrow_id column (avoid calling Schema in loop)
            $shipmentsHasEscrowId = false;
            try {
                $shipmentsHasEscrowId = TableExistenceCache::has('shipments') && Schema::hasColumn('shipments', 'escrow_id');
            } catch (\Exception $e) {
                \Log::error('Failed to check shipments schema: ' . $e->getMessage());
            }

            // Enrich each escrow with client name
            foreach ($escrows as $e) {
                try {
                    $e->client_name = DB::table('users')->where('id', $e->client_id)->value('name') ?? 'Client';
                    $e->client_phone = DB::table('users')->where('id', $e->client_id)->value('phone');
                } catch (\Exception $ex) {
                    $e->client_name = 'Client';
                    $e->client_phone = null;
                }
                try {
                    $e->action_needed = $this->getActionNeeded($e, $shipmentsHasEscrowId);
                    $e->block_reason = $this->getBlockReason($e, $shipmentsHasEscrowId);
                } catch (\Exception $ex) {
                    $e->action_needed = null;
                    $e->block_reason = null;
                }
            }

            // Global stats
            $stats = [
                'en_attente'     => 0,
                'total_held'     => 0,
                'libérés'        => 0,
                'total_released' => 0,
                'litiges'        => 0,
                'remboursés'     => 0,
                'total'          => 0,
                'need_action'    => 0,
            ];

            try {
                $allEscrows = DB::table('escrow_transactions')
                    ->where('prestataire_id', $prestataire->id);

                $stats['en_attente']     = (clone $allEscrows)->whereIn('status', ['pending', 'held', 'partial'])->count();
                $stats['total_held']     = (float) (clone $allEscrows)->whereIn('status', ['pending', 'held', 'partial'])->sum('amount');
                $stats['libérés']        = (float) (clone $allEscrows)->where('status', 'released')->sum('amount');
                $stats['total_released'] = $stats['libérés'];
                $stats['litiges']        = (clone $allEscrows)->where('status', 'disputed')->count();
                $stats['remboursés']     = (float) (clone $allEscrows)->where('status', 'refunded')->sum('amount');
                $stats['total']          = (clone $allEscrows)->count();
                $stats['need_action']    = (clone $allEscrows)->whereIn('status', ['pending', 'held', 'partial'])
                    ->where(function ($q) {
                        $q->whereNull('prestataire_confirmed_at');
                    })->count();
            } catch (\Exception $e) {
                \Log::warning('Escrow stats error: ' . $e->getMessage());
            }

            return view('prestataire.escrow.index', compact('escrows', 'stats'));
        } catch (\Exception $e) {
            \Log::error('prestataireIndex error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->with('error', 'Erreur lors du chargement des paiements sécurisés.');
        }
    }

    /**
     * Get action needed for an escrow transaction (helper)
     */
    private function getActionNeeded($escrow, bool $shipmentsHasEscrowId = false): ?array
    {
        if (in_array($escrow->status ?? '', ['released', 'refunded', 'cancelled'])) return null;

        $type = $escrow->escrowable_type ?? '';
        $activeStatuses = ['pending', 'held', 'partial'];

        // Vente urgente without shipment
        if (str_contains($type, 'UrgentSale') && in_array($escrow->status ?? '', $activeStatuses)) {
            $hasShipment = false;
            if ($shipmentsHasEscrowId) {
                try {
                    $hasShipment = DB::table('shipments')->where('escrow_id', $escrow->id)->exists();
                } catch (\Exception $e) {
                    \Log::error('Failed to check shipment for escrow #' . $escrow->id . ': ' . $e->getMessage());
                }
            }
            if (!$hasShipment) {
                return ['type' => 'shipment', 'label' => 'Ajouter l\'expédition', 'icon' => '📦', 'color' => 'blue', 'urgency' => 'high'];
            }
            if (!($escrow->prestataire_confirmed_at ?? null)) {
                return ['type' => 'mark_delivered', 'label' => 'Confirmer la livraison', 'icon' => '🚚', 'color' => 'green', 'urgency' => 'medium'];
            }
        }

        // Equipment return not validated
        if (str_contains($type, 'Equipment') && in_array($escrow->status ?? '', $activeStatuses) && !($escrow->prestataire_confirmed_at ?? null)) {
            return ['type' => 'validate_return', 'label' => 'Valider le retour', 'icon' => '🔧', 'color' => 'purple', 'urgency' => 'high'];
        }

        // Dispute
        if (($escrow->status ?? '') === 'disputed') {
            return ['type' => 'dispute', 'label' => 'Litige à traiter', 'icon' => '⚠️', 'color' => 'red', 'urgency' => 'high'];
        }

        // Client has confirmed — prestataire can request release
        if (in_array($escrow->status ?? '', $activeStatuses) && ($escrow->client_confirmed_at ?? null)) {
            $autoReleaseAt = $escrow->auto_release_at ?? null;
            $isOverdue = $autoReleaseAt && now()->gt(\Carbon\Carbon::parse($autoReleaseAt));

            if ($isOverdue) {
                return ['type' => 'request_release', 'label' => 'Demander le versement maintenant', 'icon' => '💸', 'color' => 'green', 'urgency' => 'high'];
            }
            return ['type' => 'request_release', 'label' => 'Demander le versement', 'icon' => '💰', 'color' => 'green', 'urgency' => 'medium'];
        }

        // Waiting for client confirmation
        if (in_array($escrow->status ?? '', $activeStatuses) && !($escrow->client_confirmed_at ?? null)) {
            return ['type' => 'waiting_client', 'label' => 'Le client doit confirmer', 'icon' => '⏳', 'color' => 'yellow', 'urgency' => 'wait'];
        }

        return null;
    }

    /**
     * Get block reason explanation
     */
    private function getBlockReason($escrow, bool $shipmentsHasEscrowId = false): ?string
    {
        if (in_array($escrow->status ?? '', ['released', 'refunded', 'cancelled'])) return null;

        $type = $escrow->escrowable_type ?? '';
        $activeStatuses = ['pending', 'held', 'partial'];

        if (($escrow->status ?? '') === 'disputed') {
            return 'Un litige a été ouvert par le client. La plateforme examine le dossier. Les fonds restent bloqués jusqu\'à résolution.';
        }

        if (str_contains($type, 'UrgentSale') && in_array($escrow->status ?? '', $activeStatuses)) {
            $hasShipment = false;
            if ($shipmentsHasEscrowId) {
                try {
                    $hasShipment = DB::table('shipments')->where('escrow_id', $escrow->id)->exists();
                } catch (\Exception $e) {
                    \Log::error('Failed to check shipment for escrow #' . $escrow->id . ': ' . $e->getMessage());
                }
            }
            if (!$hasShipment) {
                return 'Vous devez enregistrer les informations d\'expédition pour que le client puisse suivre son colis. Une fois livré et confirmé par le client, l\'argent vous sera versé.';
            }
            if ($escrow->client_confirmed_at ?? null) {
                return 'Le client a confirmé la réception. Vous pouvez maintenant demander le versement de vos fonds.';
            }
            return 'En attente que le client confirme la réception du produit. L\'argent sera libéré automatiquement sous 48 h si le client ne réagit pas.';
        }

        if (str_contains($type, 'Equipment')) {
            if (!($escrow->prestataire_confirmed_at ?? null)) {
                return 'L\'équipement doit être retourné et vous devez valider son état. Une fois validé, la caution sera restituée au client et votre paiement sera libéré.';
            }
            if ($escrow->client_confirmed_at ?? null) {
                return 'Le client a confirmé. Vous pouvez maintenant demander le versement de vos fonds.';
            }
            return 'En attente de la confirmation du client ou de la libération automatique.';
        }

        if (str_contains($type, 'Booking')) {
            if ($escrow->client_confirmed_at ?? null) {
                $autoReleaseAt = $escrow->auto_release_at ?? null;
                if ($autoReleaseAt && now()->gt(\Carbon\Carbon::parse($autoReleaseAt))) {
                    return 'Le client a confirmé et le délai de 48 h est dépassé. Vos fonds auraient dû être libérés automatiquement. Cliquez sur « Demander le versement » pour déclencher le paiement.';
                }
                return 'Le client a confirmé la prestation ! Vos fonds seront libérés automatiquement ou vous pouvez demander le versement maintenant.';
            }
            if (!($escrow->client_confirmed_at ?? null)) {
                return 'En attente que le client confirme que la prestation a bien été réalisée. L\'argent sera libéré automatiquement sous 48 h si le client ne réagit pas.';
            }
        }

        // Cas générique pour partial avec client confirmé
        if (($escrow->status ?? '') === 'partial' && ($escrow->client_confirmed_at ?? null)) {
            return 'Le client a confirmé mais le paiement est encore en attente de libération complète. Cliquez sur « Demander le versement » pour finaliser.';
        }

        return 'L\'argent est sécurisé sur la plateforme en attendant que toutes les parties confirment la transaction.';
    }

    /**
     * Détails d'une transaction escrow
     */
    public function prestataireShow(int $escrowId)
    {
        try {
            $user = Auth::user();
            $prestataire = $user->prestataire;

            if (!$prestataire) {
                return redirect()->route('prestataire.escrow.index')
                    ->with('error', 'Profil prestataire non trouvé');
            }

            try {
                $this->equipmentRentalPaymentSyncService->syncForPrestataire($prestataire);
            } catch (\Throwable $e) {
                \Log::warning('Escrow prestataireShow rental payment sync warning', [
                    'prestataire_id' => $prestataire->id ?? null,
                    'escrow_id' => $escrowId,
                    'error' => $e->getMessage(),
                ]);
            }

            $escrow = DB::table('escrow_transactions')
                ->where('id', $escrowId)
                ->where('prestataire_id', $prestataire->id)
                ->first();

            if (!$escrow) {
                return redirect()->route('prestataire.escrow.index')
                    ->with('error', 'Transaction non trouvée');
            }

            $relatedItem = $this->getRelatedItem($escrow);
            
            // Récupérer le shipment si la colonne escrow_id existe
            $shipment = null;
            try {
                if (Schema::hasColumn('shipments', 'escrow_id')) {
                    $shipment = DB::table('shipments')->where('escrow_id', $escrowId)->first();
                }
            } catch (\Exception $e) {
                \Log::error('Failed to retrieve shipment for escrow #' . $escrowId . ': ' . $e->getMessage());
            }

            // Récupérer le dispute si la table existe
            $dispute = null;
            try {
                if (TableExistenceCache::has('escrow_disputes')) {
                    $dispute = DB::table('escrow_disputes')
                        ->where('escrow_id', $escrowId)
                        ->orderByDesc('created_at')
                        ->first();
                }
            } catch (\Exception $e) {
                \Log::error('Failed to retrieve dispute for escrow #' . $escrowId . ': ' . $e->getMessage());
            }

            // Vérifier si peut noter le client
            $canRate = false;
            try {
                $canRate = $escrow->status === 'released' && 
                    $this->ratingService->canRate($user->id, $escrow->escrowable_type ?? '', $escrow->escrowable_id ?? 0);
            } catch (\Exception $e) {
                \Log::warning('MutualRatingService error: ' . $e->getMessage());
            }

            // Enrichir avec infos client
            $client = null;
            try {
                $client = DB::table('users')->where('id', $escrow->client_id)->first();
            } catch (\Exception $e) {
                \Log::error('Failed to retrieve client for escrow #' . $escrowId . ': ' . $e->getMessage());
            }

            // Action needed + block reason
            $actionNeeded = $this->getActionNeeded($escrow);
            $blockReason = $this->getBlockReason($escrow);

            // Timeline events
            $timeline = $this->buildTimeline($escrow, $shipment, $dispute);

            return view('prestataire.escrow.show', compact(
                'escrow', 'relatedItem', 'shipment', 'dispute', 'canRate',
                'client', 'actionNeeded', 'blockReason', 'timeline'
            ));
        } catch (\Exception $e) {
            \Log::error('prestataireShow error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return redirect()->route('prestataire.escrow.index')
                ->with('error', 'Erreur lors du chargement de la transaction.');
        }
    }

    /**
     * Build a timeline of events for the escrow transaction
     */
    private function buildTimeline($escrow, $shipment = null, $dispute = null): array
    {
        $events = [];

        // Payment received
        $events[] = [
            'date' => $escrow->created_at,
            'label' => 'Paiement client reçu',
            'detail' => number_format($escrow->total_amount ?? $escrow->amount ?? 0, 2) . ' € sécurisé',
            'icon' => '💳',
            'status' => 'done',
        ];

        // Held
        if ($escrow->held_at ?? null) {
            $events[] = [
                'date' => $escrow->held_at,
                'label' => 'Fonds sécurisés',
                'detail' => 'Argent bloqué en toute sécurité sur la plateforme',
                'icon' => '🔒',
                'status' => 'done',
            ];
        }

        // Shipment created
        if ($shipment) {
            $events[] = [
                'date' => $shipment->created_at ?? $escrow->created_at,
                'label' => 'Expédition enregistrée',
                'detail' => ucfirst(str_replace('_', ' ', $shipment->carrier ?? '')) . ' — ' . ($shipment->tracking_number ?? 'N/A'),
                'icon' => '📦',
                'status' => 'done',
            ];
            if (($shipment->status ?? '') === 'delivered') {
                $events[] = [
                    'date' => $shipment->delivered_at ?? $shipment->updated_at ?? null,
                    'label' => 'Colis livré',
                    'detail' => 'Le client a reçu le colis',
                    'icon' => '🏠',
                    'status' => 'done',
                ];
            }
        }

        // Client confirmed
        if ($escrow->client_confirmed_at ?? null) {
            $events[] = [
                'date' => $escrow->client_confirmed_at,
                'label' => 'Confirmé par le client',
                'detail' => 'Le client a validé la transaction',
                'icon' => '✅',
                'status' => 'done',
            ];
        } elseif (in_array($escrow->status, ['pending', 'held'])) {
            $events[] = [
                'date' => null,
                'label' => 'En attente de confirmation client',
                'detail' => ($escrow->auto_release_at ? 'Libération auto le ' . \Carbon\Carbon::parse($escrow->auto_release_at)->format('d/m/Y H:i') : 'Le client doit confirmer'),
                'icon' => '⏳',
                'status' => 'waiting',
            ];
        }

        // Dispute
        if ($dispute) {
            $events[] = [
                'date' => $dispute->created_at ?? null,
                'label' => 'Litige ouvert',
                'detail' => ucfirst(str_replace('_', ' ', $dispute->reason ?? 'Non spécifié')),
                'icon' => '⚠️',
                'status' => 'alert',
            ];
        }

        // Prestataire confirmed
        if ($escrow->prestataire_confirmed_at ?? null) {
            $events[] = [
                'date' => $escrow->prestataire_confirmed_at,
                'label' => 'Validé par vous',
                'detail' => 'Vous avez confirmé cette transaction',
                'icon' => '👍',
                'status' => 'done',
            ];
        }

        // Released
        if ($escrow->released_at ?? null) {
            $events[] = [
                'date' => $escrow->released_at,
                'label' => 'Paiement versé',
                'detail' => 'Argent transféré sur votre compte',
                'icon' => '💰',
                'status' => 'done',
            ];
        }

        // Refunded
        if ($escrow->refunded_at ?? null) {
            $events[] = [
                'date' => $escrow->refunded_at,
                'label' => 'Remboursé au client',
                'detail' => 'Les fonds ont été restitués',
                'icon' => '↩️',
                'status' => 'done',
            ];
        }

        // Sort by date (null dates last)
        usort($events, function ($a, $b) {
            if (!$a['date'] && !$b['date']) return 0;
            if (!$a['date']) return 1;
            if (!$b['date']) return -1;
            return strcmp($a['date'], $b['date']);
        });

        return $events;
    }

    /**
     * Prestataire confirme (pour location équipement = vérif état)
     */
    public function prestataireConfirm(PrestataireConfirmRequest $request, int $escrowId)
    {

        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return redirect()->back()->with('error', 'Profil prestataire non trouvé');
        }

        $escrow = DB::table('escrow_transactions')
            ->where('id', $escrowId)
            ->where('prestataire_id', $prestataire->id)
            ->first();

        if (!$escrow) {
            return redirect()->back()->with('error', 'Transaction non trouvée');
        }

        $equipmentCondition = 'good';
        $retainedAmount = 0.0;

        // Si équipement endommagé, calculer la rétention autorisée.
        if ($request->equipment_status === 'damaged' && (float) ($escrow->deposit_amount ?? 0) > 0) {
            // Sans photos de preuve, max 50% de la caution.
            // Avec photos, jusqu'à 100% (le client garde la possibilité d'ouvrir un litige).
            $maxRetainPercent = $request->hasFile('damage_photos') ? 100 : 50;
            $retainPercent = min((int) $request->retain_deposit_percent, $maxRetainPercent);
            $retainedAmount = ((float) $escrow->deposit_amount * $retainPercent) / 100;
            $equipmentCondition = 'damaged';
        }

        // Confirmer côté prestataire (inclut la gestion de caution une seule fois).
        $result = $this->escrowService->prestataireConfirm(
            $escrowId,
            $equipmentCondition,
            (float) $retainedAmount
        );

        if ($result) {
            // Garde-fou: certains environnements legacy peuvent réussir le flux
            // tout en laissant ce champ vide (cache/schema partiel). On le force ici.
            DB::table('escrow_transactions')
                ->where('id', $escrowId)
                ->whereNull('prestataire_confirmed_at')
                ->update([
                    'prestataire_confirmed_at' => now(),
                    'updated_at' => now(),
                ]);

            $retainedFormatted = number_format((float) $retainedAmount, 2, ',', ' ');
            $successMessage = $equipmentCondition === 'damaged'
                ? "Retour validé. {$retainedFormatted} € de caution retenus."
                : 'Retour d\'équipement validé. Paiement traité.';

            return redirect()->back()
                ->with('success', $successMessage);
        }

        $message = 'Validation impossible pour le moment. Le système n\'a pas pu finaliser le versement ou le traitement de la caution.';

        return redirect()->back()->with('error', $message);
    }

    /**
     * Prestataire note le client
     */
    public function prestataireRate(Request $request, int $escrowId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
            'would_recommend' => 'required|boolean',
        ]);

        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return redirect()->back()->with('error', 'Profil prestataire non trouvé');
        }

        $escrow = DB::table('escrow_transactions')
            ->where('id', $escrowId)
            ->where('prestataire_id', $prestataire->id)
            ->where('status', 'released')
            ->first();

        if (!$escrow) {
            return redirect()->back()->with('error', 'Transaction non trouvée ou non terminée');
        }

        $ratingId = $this->ratingService->createRating(
            raterId: $user->id,
            ratedId: $escrow->client_id,
            raterType: 'prestataire',
            ratableType: $escrow->escrowable_type,
            ratableId: $escrow->escrowable_id,
            rating: $request->rating,
            comment: $request->comment,
            wouldRecommend: $request->would_recommend
        );

        if ($ratingId) {
            return redirect()->back()->with('success', 'Merci pour votre avis sur ce client !');
        }

        return redirect()->back()->with('error', 'Vous avez déjà noté ce client');
    }

    /**
     * Prestataire crée une expédition (pour vente urgente)
     */
    public function createShipment(Request $request, int $escrowId)
    {
        $request->validate([
            'carrier' => 'required|string',
            'tracking_number' => 'required|string|max:100',
            'weight' => 'nullable|numeric|min:0',
            'package_description' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return redirect()->back()->with('error', 'Profil prestataire non trouvé');
        }

        $escrow = DB::table('escrow_transactions')
            ->where('id', $escrowId)
            ->where('prestataire_id', $prestataire->id)
            ->first();

        if (!$escrow) {
            return redirect()->back()->with('error', 'Transaction non trouvée');
        }

        if (!str_contains((string) ($escrow->escrowable_type ?? ''), 'UrgentSale')) {
            return redirect()->back()->with('error', 'L\'expédition est disponible uniquement pour les ventes urgentes');
        }

        // Récupérer l'adresse de livraison
        $client = DB::table('clients')
            ->join('users', 'clients.user_id', '=', 'users.id')
            ->where('clients.id', $escrow->client_id)
            ->select('users.address', 'users.city', 'users.postal_code')
            ->first();

        $senderAddress = [
            'address' => $user->address ?? null,
            'city' => $user->city ?? null,
            'postal_code' => $user->postal_code ?? null,
        ];

        $recipientAddress = [
            'address' => $client->address ?? null,
            'city' => $client->city ?? null,
            'postal_code' => $client->postal_code ?? null,
        ];

        $shipmentId = DB::table('shipments')->insertGetId([
            'escrow_id' => $escrowId,
            'shippable_type' => $escrow->escrowable_type,
            'shippable_id' => $escrow->escrowable_id,
            'carrier' => $request->carrier,
            'tracking_number' => $request->tracking_number,
            'tracking_url' => $this->shippingService->getTrackingUrl($request->carrier, $request->tracking_number),
            'label_url' => null,
            'sender_address' => json_encode($senderAddress),
            'recipient_address' => json_encode($recipientAddress),
            'relay_point_id' => null,
            'status' => 'shipped',
            'shipped_at' => now(),
            'estimated_delivery' => null,
            'delivery_confirmed' => false,
            'delivery_confirmed_at' => null,
            'conformity_validated' => false,
            'conformity_validated_at' => null,
            'shipping_cost' => 0,
            'weight' => (float) ($request->weight ?? 0),
            'dimensions' => null,
            'notes' => $request->package_description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($shipmentId) {
            return redirect()->back()
                ->with('success', 'Expédition enregistrée avec le numéro de suivi.');
        }

        return redirect()->back()->with('error', 'Erreur lors de la création de l\'expédition');
    }

    // =========================================================================
    // PARAMÈTRES PRESTATAIRE
    // =========================================================================

    /**
     * Page de choix du mode de paiement
     */
    public function paymentModeSettings()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return redirect()->route('home')->with('error', 'Profil prestataire requis');
        }

        if (function_exists('prestataire_stripe_connect_enabled') && !prestataire_stripe_connect_enabled()) {
            return redirect()->route('prestataire.dashboard')
                ->with('info', 'La configuration Stripe prestataire est désactivée pour le moment.');
        }

        return view('prestataire.settings.payment-mode', [
            'currentMode' => $prestataire->payment_mode ?? 'direct',
            'escrowTermsAccepted' => $prestataire->escrow_terms_accepted_at ?? null,
        ]);
    }

    /**
     * Changer le mode de paiement
     */
    public function updatePaymentMode(Request $request)
    {
        if (function_exists('prestataire_stripe_connect_enabled') && !prestataire_stripe_connect_enabled()) {
            return redirect()->route('prestataire.dashboard')
                ->with('info', 'La configuration Stripe prestataire est désactivée pour le moment.');
        }

        $request->validate([
            'payment_mode' => 'required|in:escrow,direct',
            'accept_terms' => 'required_if:payment_mode,escrow|accepted',
        ]);

        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return redirect()->back()->with('error', 'Profil prestataire non trouvé');
        }

        $updateData = ['payment_mode' => $request->payment_mode];

        if ($request->payment_mode === 'escrow' && !$prestataire->escrow_terms_accepted_at) {
            $updateData['escrow_terms_accepted_at'] = now();
        }

        DB::table('prestataires')
            ->where('id', $prestataire->id)
            ->update($updateData);

        $message = $request->payment_mode === 'escrow'
            ? 'Mode sécurisé activé ! Vos clients verront le badge "Paiement Sécurisé".'
            : 'Mode paiement direct activé.';

        return redirect()->back()->with('success', $message);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Récupérer l'objet lié à l'escrow
     */
    protected function getRelatedItem($escrow)
    {
        $escrowableType = (string) ($escrow->escrowable_type ?? '');

        $table = match (true) {
            str_contains($escrowableType, 'Booking') => 'bookings',
            str_contains($escrowableType, 'EquipmentRental') => 'equipment_rental_requests',
            str_contains($escrowableType, 'UrgentSale') => 'urgent_sale_purchases',
            str_contains($escrowableType, 'FoodOrder') => 'food_orders',
            default => null,
        };

        if (!$table) {
            return null;
        }

        try {
            $escrowableId = $escrow->escrowable_id ?? null;
            if (!$escrowableId) {
                return null;
            }

            return DB::table($table)->find($escrowableId);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * API: Obtenir les transporteurs disponibles
     */
    public function getCarriers()
    {
        return response()->json([
            'carriers' => $this->shippingService->getEnabledCarriers(),
        ]);
    }

    /**
     * API: Rechercher des points relais
     */
    public function searchRelayPoints(Request $request)
    {
        $request->validate([
            'carrier' => 'required|string',
            'postal_code' => 'required|string|size:5',
        ]);

        $points = $this->shippingService->searchRelayPoints(
            $request->carrier,
            $request->postal_code
        );

        return response()->json(['relay_points' => $points]);
    }

    // =========================================================================
    // VENTES URGENTES
    // =========================================================================

    /**
     * Client confirme la réception d'une vente urgente (produit conforme)
     */
    public function confirmUrgentSale(Request $request, int $escrowId)
    {
        $user = Auth::user();
        $userId = (int) $user->id;

        $escrow = DB::table('escrow_transactions')
            ->where('id', $escrowId)
            ->first();

        if (!$escrow) {
            return redirect()->back()->with('error', 'Transaction non trouvée');
        }

        if (!$this->currentUserOwnsUrgentSaleEscrow($escrow, $userId)) {
            return redirect()->back()->with('error', 'Accès non autorisé à cette transaction.');
        }

        if (!str_contains((string) ($escrow->escrowable_type ?? ''), 'UrgentSale')) {
            return redirect()->back()->with('error', 'Cette action est réservée aux ventes urgentes');
        }

        $result = $this->escrowService->confirmUrgentSaleDelivery($escrowId);

        if ($result) {
            return redirect()->back()
                ->with('success', 'Merci ! La conformité est confirmée. Le solde restant a été libéré au vendeur.');
        }

        return redirect()->back()->with('error', 'Erreur lors de la confirmation');
    }

    /**
     * Client signale un produit non conforme (vente urgente)
     */
    public function reportNonConformity(DisputeUrgentSaleRequest $request, int $escrowId)
    {

        $user = Auth::user();
        $userId = (int) $user->id;

        $escrow = DB::table('escrow_transactions')
            ->where('id', $escrowId)
            ->first();

        if (!$escrow) {
            return redirect()->back()->with('error', 'Transaction non trouvée');
        }

        if (!$this->currentUserOwnsUrgentSaleEscrow($escrow, $userId)) {
            return redirect()->back()->with('error', 'Accès non autorisé à cette transaction.');
        }

        // Télécharger les photos
        $evidencePaths = [];
        foreach ($request->file('evidence') as $file) {
            $path = $file->store('non-conformity/' . $escrowId, 'public');
            $evidencePaths[] = $path;
        }

        $result = $this->escrowService->reportNonConformity(
            $escrowId,
            $request->description,
            $evidencePaths
        );

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message'] ?? 'Non-conformité enregistrée.');
        }

        return redirect()->back()->with('error', $result['message'] ?? 'Erreur lors du signalement');
    }

    /**
     * Prestataire: marquer une vente urgente comme livrée (démarre le délai d'auto-libération)
     */
    public function markUrgentSaleDelivered(Request $request, int $escrowId)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return redirect()->back()->with('error', 'Profil prestataire non trouvé');
        }

        $escrow = DB::table('escrow_transactions')
            ->where('id', $escrowId)
            ->where('prestataire_id', $prestataire->id)
            ->first();

        if (!$escrow) {
            return redirect()->back()->with('error', 'Transaction non trouvée');
        }

        if (!str_contains((string) ($escrow->escrowable_type ?? ''), 'UrgentSale')) {
            return redirect()->back()->with('error', 'Cette action est réservée aux ventes urgentes');
        }

        $result = $this->escrowService->markUrgentSaleDelivered($escrowId);

        if (!empty($result['success'])) {
            return redirect()->back()->with('success', $result['message'] ?? 'Livraison confirmée');
        }

        return redirect()->back()->with('error', $result['message'] ?? 'Erreur lors de la mise à jour');
    }

    /**
     * Prestataire: confirmer "retour reçu" (accord) et déclencher remboursement total
     */
    public function confirmUrgentSaleReturnReceived(Request $request, int $escrowId)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return redirect()->back()->with('error', 'Profil prestataire non trouvé');
        }

        $escrow = DB::table('escrow_transactions')
            ->where('id', $escrowId)
            ->where('prestataire_id', $prestataire->id)
            ->first();

        if (!$escrow) {
            return redirect()->back()->with('error', 'Transaction non trouvée');
        }

        if (!str_contains((string) ($escrow->escrowable_type ?? ''), 'UrgentSale')) {
            return redirect()->back()->with('error', 'Cette action est réservée aux ventes urgentes');
        }

        $result = $this->escrowService->confirmUrgentSaleReturnReceivedAndRefundFull($escrowId);

        if (!empty($result['success'])) {
            $amount = number_format((float) ($result['refund_amount'] ?? 0), 2);
            return redirect()->back()->with('success', 'Retour confirmé. Remboursement total effectué (' . $amount . '€).');
        }

        return redirect()->back()->with('error', $result['message'] ?? 'Erreur lors du remboursement');
    }

    // =========================================================================
    // ÉQUIPEMENT - RETOUR
    // =========================================================================

    /**
     * Prestataire valide le retour d'équipement
     */
    public function returnEquipment(ConfirmUrgentSaleReturnRequest $request, int $escrowId)
    {

        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return redirect()->back()->with('error', 'Profil prestataire non trouvé');
        }

        $escrow = DB::table('escrow_transactions')
            ->where('id', $escrowId)
            ->where('prestataire_id', $prestataire->id)
            ->first();

        if (!$escrow) {
            return redirect()->back()->with('error', 'Transaction non trouvée');
        }

        // Télécharger les photos si dégâts
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store('equipment-returns/' . $escrowId, 'public');
                $photoPaths[] = $path;
            }
        }

        $result = $this->escrowService->returnEquipment(
            $escrowId,
            $request->condition,
            $request->damage_percent ?? 0,
            $request->damage_description,
            $photoPaths
        );

        if ($result['success']) {
            $message = $result['condition'] === 'good'
                ? 'Équipement validé en bon état. Paiement effectué !'
                : "Dégâts signalés. {$result['deposit_retained']}€ de caution retenus.";
            
            return redirect()->back()->with('success', $message);
        }

        return redirect()->back()->with('error', $result['message'] ?? 'Erreur lors de la validation');
    }

    /**
     * Prestataire demande le versement des fonds
     * Utilisable quand le client a confirmé ou que le délai 48h est dépassé
     */
    public function requestRelease(int $escrowId)
    {
        try {
            $user = Auth::user();
            $prestataire = $user->prestataire;

            if (!$prestataire) {
                return redirect()->back()->with('error', 'Profil prestataire non trouvé');
            }

            $escrow = DB::table('escrow_transactions')
                ->where('id', $escrowId)
                ->where('prestataire_id', $prestataire->id)
                ->first();

            if (!$escrow) {
                return redirect()->back()->with('error', 'Transaction non trouvée');
            }

            // Vérifier que le statut permet une demande de versement
            if (in_array($escrow->status, ['released', 'refunded', 'cancelled'])) {
                return redirect()->back()->with('error', 'Cette transaction est déjà finalisée.');
            }

            if ($escrow->status === 'disputed') {
                return redirect()->back()->with('error', 'Impossible de demander le versement pendant un litige.');
            }

            // Vérifier que le client a confirmé OU que le délai auto-release est dépassé
            $clientConfirmed = !empty($escrow->client_confirmed_at);
            $autoReleaseOverdue = false;
            if ($escrow->auto_release_at) {
                $autoReleaseOverdue = now()->gt(\Carbon\Carbon::parse($escrow->auto_release_at));
            }

            if (!$clientConfirmed && !$autoReleaseOverdue) {
                return redirect()->back()->with('error', 'Le client n\'a pas encore confirmé et le délai de 48 h n\'est pas dépassé.');
            }

            // Appel au service pour libérer les fonds
            $result = $this->escrowService->releaseToPrestataire($escrowId);

            if ($result) {
                return redirect()->back()->with('success', '✅ Versement effectué ! Les fonds ont été libérés sur votre compte.');
            }

            return redirect()->back()->with('error', 'Erreur lors du versement. Veuillez réessayer ou contacter le support.');
        } catch (\Exception $e) {
            \Log::error('requestRelease error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Erreur technique lors de la demande de versement.');
        }
    }
}
