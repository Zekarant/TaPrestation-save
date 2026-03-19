<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\DeliveryBatchOrder;
use App\Models\DeliveryDriver;
use App\Models\FoodOrder;
use App\Models\Prestataire;
use App\Models\User;
use App\Notifications\FoodOrderAccepted;
use App\Notifications\FoodOrderReady;
use App\Notifications\FoodOrderRejected;
use App\Notifications\FoodOrderCompleted;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

use App\Support\TableExistenceCache;
class FoodOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['internalMap', 'internalMapData']);
    }

    /**
     * Liste des commandes du prestataire - redirige vers la page unifiée
     */
    public function index(Request $request)
    {
        // Rediriger vers le dashboard unifié
        return $this->dashboard($request);
    }

    /**
     * Afficher les commandes en temps réel (tableau de bord cuisine unifié)
     */
    public function dashboard(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire) {
            return redirect()->route('prestataire.register')
                ->with('error', 'Vous devez d\'abord créer votre profil prestataire.');
        }

        try {
            // Commandes actives groupées par statut
            // IMPORTANT: Ne pas afficher les commandes dont le paiement en ligne n'est pas encore fait.
            // (Le prestataire doit être notifié/voir la commande seulement après paiement)
            // NOTE: En prod on a des commandes avec payment_status='pending' déjà acceptées.
            // Sans l'inclure ici, elles disparaissent de la cuisine + agenda.
            $visiblePaymentStatuses = [FoodOrder::PAYMENT_PAID, FoodOrder::PAYMENT_PENDING_CAPTURE, FoodOrder::PAYMENT_PENDING, 'partial', 'pending'];
            
            // Flow des commandes planifiées:
            // 1. Commande avec date future → "En attente" (cuisine) pour accept/refuse
            // 2. Vendeur accepte → Va dans l'agenda (sort de la cuisine)
            // 3. Jour J → Revient dans "Acceptées" (cuisine)
            $today = now()->startOfDay();
            $hasPaymentIntentId = self::hasFoodOrdersColumn('payment_intent_id');
            $hasRequestedAt = self::hasFoodOrdersColumn('requested_at');
            $pendingSortExpr = $hasRequestedAt ? 'COALESCE(requested_at, created_at) ASC' : 'created_at ASC';
            $acceptedSortExpr = $hasRequestedAt ? 'COALESCE(requested_at, accepted_at, created_at) ASC' : 'COALESCE(accepted_at, created_at) ASC';
            $preparingSortExpr = $hasRequestedAt ? 'COALESCE(requested_at, preparing_at, updated_at, created_at) ASC' : 'COALESCE(preparing_at, updated_at, created_at) ASC';
            $readySortExpr = $hasRequestedAt ? 'COALESCE(requested_at, ready_at, created_at) ASC' : 'COALESCE(ready_at, created_at) ASC';

            $orders = [
                // PENDING: Afficher TOUTES les commandes en attente (y compris futures)
                // pour que le vendeur puisse accepter ou refuser
                'pending' => FoodOrder::where('prestataire_id', $prestataire->id)
                    ->where('status', 'pending')
                    ->where(function ($q) use ($visiblePaymentStatuses) {
                        $q->where('payment_method', 'cash')
                            ->orWhereIn('payment_status', $visiblePaymentStatuses);
                    })
                    ->with(['client', 'items'])
                    ->orderByRaw($pendingSortExpr)
                    ->get(),
                // ACCEPTED: Commandes acceptées pour aujourd'hui ou sans date
                // INCLUT aussi les commandes 'scheduled' dont la date est aujourd'hui (Jour J)
                'accepted' => FoodOrder::where('prestataire_id', $prestataire->id)
                    ->where(function($q) use ($today, $hasRequestedAt) {
                        // Commandes accepted (pas de date ou date aujourd'hui/passée)
                        $q->where(function($q2) use ($today, $hasRequestedAt) {
                            $q2->where('status', 'accepted');

                            if ($hasRequestedAt) {
                                $q2->where(function ($q3) use ($today) {
                                    $q3->whereNull('requested_at')
                                        ->orWhereDate('requested_at', '<=', $today);
                                });
                            }
                        })
                        // OU commandes scheduled dont le Jour J est arrivé
                        ->orWhere(function($q2) use ($today, $hasRequestedAt) {
                            $q2->where('status', 'scheduled');
                            if ($hasRequestedAt) {
                                $q2->whereDate('requested_at', '<=', $today);
                            }
                        });
                    })
                    ->where(function ($q) use ($visiblePaymentStatuses) {
                        $q->where('payment_method', 'cash')
                            ->orWhereIn('payment_status', $visiblePaymentStatuses);
                    })
                    ->with(['client', 'items'])
                    ->orderByRaw($acceptedSortExpr)
                    ->get(),
                // PREPARING: Seulement aujourd'hui ou sans date
                'preparing' => FoodOrder::where('prestataire_id', $prestataire->id)
                    ->where('status', 'preparing')
                    ->where(function ($q) use ($visiblePaymentStatuses) {
                        $q->where('payment_method', 'cash')
                            ->orWhereIn('payment_status', $visiblePaymentStatuses);
                    })
                    ->with(['client', 'items'])
                    ->when($hasRequestedAt, function ($query) use ($today) {
                        $query->where(function ($q) use ($today) {
                            $q->whereNull('requested_at')
                                ->orWhereDate('requested_at', '<=', $today);
                        });
                    })
                    ->orderByRaw($preparingSortExpr)
                    ->get(),
                // READY: Seulement aujourd'hui ou sans date
                'ready' => FoodOrder::where('prestataire_id', $prestataire->id)
                    ->where('status', 'ready')
                    ->where(function ($q) use ($visiblePaymentStatuses) {
                        $q->where('payment_method', 'cash')
                            ->orWhereIn('payment_status', $visiblePaymentStatuses);
                    })
                    ->with(['client', 'items'])
                    ->when($hasRequestedAt, function ($query) use ($today) {
                        $query->where(function ($q) use ($today) {
                            $q->whereNull('requested_at')
                                ->orWhereDate('requested_at', '<=', $today);
                        });
                    })
                    ->orderByRaw($readySortExpr)
                    ->get(),
            ];

            $activeTab = $request->input('tab', 'kitchen');

            // Historique des commandes terminées (pour l'onglet historique)
            $historyQuery = FoodOrder::where('prestataire_id', $prestataire->id)
                ->whereIn('status', ['completed', 'delivered', 'cancelled'])
                ->with(['client'])
                ->orderBy('created_at', 'desc');

            $status = $request->input('status');
            if (in_array($status, ['completed', 'delivered', 'cancelled'], true)) {
                $historyQuery->where('status', $status);
            }

            $historySearch = trim(ltrim((string) $request->input('search', ''), '#'));
            if ($historySearch !== '') {
                $historyQuery->where('order_number', 'like', '%' . $historySearch . '%');
            }

            $date = $request->input('date');
            if (!empty($date)) {
                try {
                    $dateString = Carbon::createFromFormat('Y-m-d', $date)->toDateString();
                    $historyQuery->whereDate('created_at', $dateString);
                } catch (\Exception $e) {
                    // Ignore invalid date input
                }
            }

            $historyOrders = $historyQuery->take(50)->get();

            // Commandes planifiées (agenda)
            $scheduledOrders = collect();
            if ($hasRequestedAt) {
                $scheduledOrders = FoodOrder::where('prestataire_id', $prestataire->id)
                    ->where('status', 'scheduled')
                    ->whereDate('requested_at', '>', now()->toDateString())
                    ->with(['client', 'items'])
                    ->orderBy('requested_at')
                    ->get();
            }

            // Commandes en livraison (toutes les courses actives non terminées).
            // Important: inclure aussi accepted/preparing + pending/assigned/self_delivery
            // pour éviter de "perdre" des livraisons dans l'onglet delivery.
            $deliveryOrders = FoodOrder::where('prestataire_id', $prestataire->id)
                ->where('delivery_type', 'delivery')
                ->where(function ($q) use ($visiblePaymentStatuses) {
                    $q->where('payment_method', 'cash')
                        ->orWhereIn('payment_status', $visiblePaymentStatuses);
                })
                ->where(function ($query) use ($hasPaymentIntentId) {
                    // Exclure de la liste active les courses cash/mixed déjà encaissées.
                    // Compat legacy: payment_method null + payment_intent_id null => assimilé espèces.
                    $query->whereNull('payment_status')
                        ->orWhere('payment_status', '!=', FoodOrder::PAYMENT_PAID)
                        ->orWhere(function ($paidQuery) use ($hasPaymentIntentId) {
                            $paidQuery->where('payment_status', FoodOrder::PAYMENT_PAID)
                                ->where(function ($methodQuery) use ($hasPaymentIntentId) {
                                    $methodQuery->whereNotIn('payment_method', ['cash', 'mixed']);

                                    // Fallback schéma: certaines bases legacy n'ont pas payment_intent_id
                                    if ($hasPaymentIntentId) {
                                        $methodQuery->orWhere(function ($legacyOnlineQuery) {
                                            $legacyOnlineQuery->whereNull('payment_method')
                                                ->whereNotNull('payment_intent_id');
                                        });
                                    }
                                });
                        });
                })
                ->where(function($query) {
                    // Etats cuisine actifs
                    $query->whereIn('status', ['accepted', 'preparing', 'ready'])
                        // OU états livraison actifs
                        ->orWhereIn('delivery_status', ['pending', 'assigned', 'self_delivery', 'picked_up', 'in_transit'])
                        // OU déjà affectées à un livreur (sécurité anti-perte d'affichage)
                        ->orWhereNotNull('driver_id');
                })
                ->whereNotIn('status', ['completed', 'delivered', 'cancelled'])
                ->with(['client', 'driver'])
                ->orderByRaw('COALESCE(ready_at, accepted_at, created_at) ASC')
                ->get();

            // Statistiques de paiement du jour
            $today = now()->startOfDay();
            $paymentStats = self::buildPaymentStats($prestataire->id, $today);
            $statDetails = self::buildDashboardStatDetails($prestataire->id, $orders);

            $totalDeliveries = $deliveryOrders->count();
            $internalBoard = $this->buildInternalDeliveryBoard($prestataire->id);
            $deliverySyncKey = self::buildDeliverySyncKey($deliveryOrders, $internalBoard);

            return view('prestataire.food-orders.unified', compact(
                'orders',
                'historyOrders',
                'scheduledOrders',
                'deliveryOrders',
                'totalDeliveries',
                'prestataire',
                'paymentStats',
                'statDetails',
                'activeTab',
                'internalBoard',
                'deliverySyncKey'
            ));
        } catch (\Throwable $e) {
            Log::error('FoodOrderController dashboard error: ' . $e->getMessage(), [
                'prestataire_id' => $prestataire->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            $orders = [
                'pending' => collect(),
                'accepted' => collect(),
                'preparing' => collect(),
                'ready' => collect(),
            ];

            $historyOrders = collect();
            $scheduledOrders = collect();
            $deliveryOrders = collect();
            $totalDeliveries = 0;
            $paymentStats = [
                'revenue_today' => 0.0,
                'revenue_month' => 0.0,
                'pending_payments' => 0.0,
                'paid_count_today' => 0,
                'pending_count' => 0,
            ];
            $statDetails = self::emptyDashboardStatDetails();
            $activeTab = $request->input('tab', 'kitchen');
            $internalBoard = [
                'fleet' => collect(),
                'stats' => [
                    'drivers_total' => 0,
                    'drivers_available' => 0,
                    'drivers_on_mission' => 0,
                    'active_orders_total' => 0,
                    'remaining_points_total' => 0,
                ],
            ];
            $deliverySyncKey = self::buildDeliverySyncKey($deliveryOrders, $internalBoard);

            return view('prestataire.food-orders.unified', compact(
                'orders',
                'historyOrders',
                'scheduledOrders',
                'deliveryOrders',
                'totalDeliveries',
                'prestataire',
                'paymentStats',
                'statDetails',
                'activeTab',
                'internalBoard',
                'deliverySyncKey'
            ));
        }
    }

    /**
     * Basculer l'état Ouvert/Fermé du module food pour le prestataire.
     */
    public function toggleOpenStatus(Request $request)
    {
        $prestataire = null;

        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expirée. Reconnectez-vous.',
                ], 401);
            }

            $prestataire = $user->prestataire;
            if (!$prestataire) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil prestataire introuvable.',
                ], 403);
            }

            $table = $prestataire->getTable();
            if (!TableExistenceCache::has($table) || !Schema::hasColumn($table, 'food_is_open')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paramètre indisponible (colonne food_is_open absente).',
                    'is_open' => (bool) ($prestataire->food_is_open ?? true),
                ], 422);
            }

            $isOpen = !(bool) ($prestataire->food_is_open ?? true);

            DB::table($table)
                ->where('id', (int) $prestataire->id)
                ->update(['food_is_open' => $isOpen]);

            return response()->json([
                'success' => true,
                'is_open' => $isOpen,
                'message' => $isOpen ? 'Restaurant ouvert.' : 'Restaurant fermé.',
            ]);
        } catch (\Throwable $e) {
            Log::error('FoodOrderController toggleOpenStatus error: ' . $e->getMessage(), [
                'prestataire_id' => $prestataire->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de mettre à jour le statut.',
            ], 500);
        }
    }

    /**
     * Carte pro des livraisons internes (prestataire -> livreurs internes)
     */
    public function internalMap(Request $request)
    {
        $context = $this->resolveInternalMapContext($request);
        if (!$context) {
            return redirect()->route('driver.internal.access', [
                'redirect_to' => '/prestataire/food/food-orders/internal-map',
            ])->with('error', 'Accès interne requis. Entrez votre code livreur.');
        }

        /** @var Prestataire $prestataire */
        $prestataire = $context['prestataire'];
        /** @var DeliveryDriver|null $internalDriver */
        $internalDriver = $context['internal_driver'] ?? null;
        $internalDriverMode = (bool) ($context['internal_driver_mode'] ?? false);

        if ($internalDriverMode && $internalDriver) {
            $selectedDriverId = (int) $internalDriver->id;
        } else {
            $selectedDriverId = (int) $request->integer('driver_id');
            if ($selectedDriverId <= 0) {
                $selectedDriverId = null;
            }
        }

        $search = trim((string) $request->input('q', ''));
        $initialFocusOrderId = max(0, (int) $request->integer('focus_order'));
        $mapPayload = $this->buildInternalMapPayload(
            $prestataire->id,
            (float) ($prestataire->latitude ?? 0),
            (float) ($prestataire->longitude ?? 0),
            $selectedDriverId,
            $search
        );

        $googleMapsKey = config('services.google_maps.key', env('GOOGLE_MAPS_API_KEY', ''));

        return view('prestataire.food-orders.internal-map', [
            'prestataire' => $prestataire,
            'selectedDriverId' => $selectedDriverId,
            'search' => $search,
            'initialFocusOrderId' => $initialFocusOrderId,
            'mapPayload' => $mapPayload,
            'googleMapsKey' => $googleMapsKey,
            'internalDriverMode' => $internalDriverMode,
            'internalDriver' => $internalDriver,
        ]);
    }

    /**
     * Données JSON de la carte interne (refresh frontend)
     */
    public function internalMapData(Request $request)
    {
        $context = $this->resolveInternalMapContext($request);
        if (!$context) {
            return response()->json([
                'success' => false,
                'message' => 'Accès interne requis.',
            ], 403);
        }

        /** @var Prestataire $prestataire */
        $prestataire = $context['prestataire'];
        /** @var DeliveryDriver|null $internalDriver */
        $internalDriver = $context['internal_driver'] ?? null;
        $internalDriverMode = (bool) ($context['internal_driver_mode'] ?? false);

        if ($internalDriverMode && $internalDriver) {
            $selectedDriverId = (int) $internalDriver->id;
        } else {
            $selectedDriverId = (int) $request->integer('driver_id');
            if ($selectedDriverId <= 0) {
                $selectedDriverId = null;
            }
        }

        $search = trim((string) $request->input('q', ''));
        $payload = $this->buildInternalMapPayload(
            $prestataire->id,
            (float) ($prestataire->latitude ?? 0),
            (float) ($prestataire->longitude ?? 0),
            $selectedDriverId,
            $search
        );

        return response()->json([
            'success' => true,
            'data' => $payload,
        ]);
    }

    /**
     * Afficher une commande
     */
    public function show(FoodOrder $foodOrder)
    {
        $this->authorizeOrder($foodOrder);

        $foodOrder->load(['client', 'items.foodProduct']);

        return view('prestataire.food-orders.show', compact('foodOrder'));
    }

    /**
     * Accepter une commande
     */
    public function accept(FoodOrder $foodOrder)
    {
        $this->authorizeOrder($foodOrder);

        // Bloquer l'acceptation tant que le client n'a pas payé (si paiement en ligne requis)
        if ($foodOrder->payment_method !== 'cash' && $foodOrder->payment_status === FoodOrder::PAYMENT_PENDING) {
            return back()->with('error', 'Cette commande est en attente de paiement.');
        }

        if (!$foodOrder->isPending()) {
            return back()->with('error', 'Cette commande ne peut pas être acceptée.');
        }

        $foodOrder->accept();

        // Si c'est une livraison, notifier les livreurs disponibles dès l'acceptation
        if ($foodOrder->delivery_type === 'delivery' && !$foodOrder->driver_id) {
            $this->notifyAvailableDrivers($foodOrder);
        }

        // CAPTURE DU PAIEMENT : si la commande est en attente de capture (livraison externe payée)
        // et qu'un livreur a déjà accepté, on capture le paiement maintenant
        if ($foodOrder->payment_status === 'pending_capture' && $foodOrder->driver_id !== null) {
            $captureSuccess = $foodOrder->capturePayment();
            if (!$captureSuccess) {
                Log::warning("Échec capture paiement pour FoodOrder #{$foodOrder->id} lors acceptation vendeur");
            } else {
                Log::info("Paiement capturé pour FoodOrder #{$foodOrder->id} après acceptation vendeur");
            }
        }

        // Notifier le client
        try {
            if ($foodOrder->client) {
                $foodOrder->client->notify(new FoodOrderAccepted($foodOrder));
            } else {
                Log::warning("FoodOrder #{$foodOrder->id} n'a pas de client associé - notification non envoyée");
            }
        } catch (\Exception $e) {
            Log::error('Erreur notification FoodOrderAccepted: ' . $e->getMessage());
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Commande acceptée !',
                'order' => $foodOrder->fresh(),
            ]);
        }

        return back()->with('success', 'Commande acceptée !');
    }

    /**
     * Rejeter une commande (+ remboursement automatique si paiement bloqué)
     */
    public function reject(Request $request, FoodOrder $foodOrder)
    {
        $this->authorizeOrder($foodOrder);

        if (!$foodOrder->canBeCancelled()) {
            return back()->with('error', 'Cette commande ne peut pas être refusée.');
        }

        $reason = $request->input('reason', 'Commande refusée par le prestataire');

        $finance = $this->handleCancellationFinanceAsPrestataire($foodOrder, "Refus vendeur: {$reason}");
        if (!($finance['success'] ?? false)) {
            return back()->with('error', (string) ($finance['message'] ?? 'Impossible de finaliser le remboursement.'));
        }

        $foodOrder->cancel($reason);

        // Notifier le client
        try {
            if ($foodOrder->client) {
                $foodOrder->client->notify(new FoodOrderRejected($foodOrder, $reason));
            } else {
                Log::warning("FoodOrder #{$foodOrder->id} n'a pas de client associé - notification non envoyée");
            }
        } catch (\Exception $e) {
            Log::error('Erreur notification FoodOrderRejected: ' . $e->getMessage());
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => trim('Commande refusée. ' . (string) ($finance['message'] ?? '')),
            ]);
        }

        return back()->with('success', trim('Commande refusée. ' . (string) ($finance['message'] ?? '')));
    }

    /**
     * Annuler une commande APRÈS l'avoir acceptée (par le prestataire)
     * Différent de reject() qui est pour les commandes en pending
     */
    public function cancelByPrestataire(Request $request, FoodOrder $foodOrder)
    {
        try {
            $this->authorizeOrder($foodOrder);

            // Vérifier que la commande peut être annulée par le prestataire
            // On peut annuler tant que pas "delivered" ou "completed" ou "cancelled"
            $nonCancellableStatuses = ['delivered', 'completed', 'cancelled', 'rejected'];
            if (in_array($foodOrder->status, $nonCancellableStatuses)) {
                return back()->with('error', 'Cette commande ne peut plus être annulée.');
            }

            // Construire la raison avec les détails si fournis
            $reason = $request->input('reason', 'Commande annulée par le prestataire');
            if ($request->input('reason_details')) {
                $reason .= ' - ' . $request->input('reason_details');
            }

            $finance = $this->handleCancellationFinanceAsPrestataire($foodOrder, "Annulation prestataire: {$reason}");
            if (!($finance['success'] ?? false)) {
                return back()->with('error', (string) ($finance['message'] ?? 'Impossible de finaliser le remboursement.'));
            }

            // Annuler la commande
            $foodOrder->status = 'cancelled';
            $foodOrder->cancellation_reason = $reason;
            $foodOrder->cancelled_at = now();
            
            // Vérifier si la colonne cancelled_by existe avant de l'assigner
            if (\Schema::hasColumn('food_orders', 'cancelled_by')) {
                $foodOrder->cancelled_by = 'prestataire';
            }
            
            $foodOrder->save();

            // Notifier le client de l'annulation
            try {
                if ($foodOrder->client) {
                    $foodOrder->client->notify(new \App\Notifications\FoodOrderCancelled($foodOrder, $reason, 'prestataire'));
                }
            } catch (\Exception $e) {
                Log::error('Erreur notification FoodOrderCancelled: ' . $e->getMessage());
            }

            // Libérer le livreur si assigné
            if ($foodOrder->driver_id) {
                try {
                    $foodOrder->loadMissing('driver.user');
                    if ($foodOrder->driver && $foodOrder->driver->user) {
                        $foodOrder->driver->user->notify(new \App\Notifications\FoodOrderCancelledForDriver($foodOrder, $reason));
                    }
                } catch (\Exception $e) {
                    Log::error('Erreur notification driver annulation: ' . $e->getMessage());
                }
            }

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => trim('Commande annulée. ' . (string) ($finance['message'] ?? '')),
                ]);
            }

            return redirect()->route('prestataire.food-orders.index')
                ->with('success', trim('Commande annulée. ' . (string) ($finance['message'] ?? '')));
                
        } catch (\Exception $e) {
            Log::error('Erreur cancelByPrestataire: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Une erreur est survenue lors de l\'annulation.'], 500);
            }
            
            return back()->with('error', 'Une erreur est survenue lors de l\'annulation.');
        }
    }

    /**
     * Commencer la préparation
     * Note: On ne bloque plus la préparation - les livreurs sont notifiés en parallèle
     */
    public function startPreparing(FoodOrder $foodOrder)
    {
        $this->authorizeOrder($foodOrder);

        $foodOrder->loadMissing('prestataire');

        if (!$foodOrder->isAccepted()) {
            return back()->with('error', 'Cette commande doit d\'abord être acceptée.');
        }

        // Déblocage livraison externe : un livreur doit avoir accepté avant la préparation
        if ($foodOrder->requiresExternalDriver() && !$foodOrder->hasDriverAccepted()) {
            $this->notifyAvailableDrivers($foodOrder);
            return back()->with('error', 'En attente d\'un livreur : la livraison externe doit être acceptée avant de démarrer la préparation.');
        }

        $foodOrder->startPreparing();

        // Notifier le client
        try {
            if ($foodOrder->client) {
                $foodOrder->client->notify(new \App\Notifications\FoodOrderPreparing($foodOrder));
            } else {
                Log::warning("FoodOrder #{$foodOrder->id} n'a pas de client associé - notification non envoyée");
            }
        } catch (\Exception $e) {
            Log::error('Erreur notification FoodOrderPreparing: ' . $e->getMessage());
        }

        // Si c'est une livraison et pas encore de livreur, notifier les livreurs disponibles
        if ($foodOrder->delivery_type === 'delivery' && !$foodOrder->driver_id) {
            $this->notifyAvailableDrivers($foodOrder);
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Préparation commencée !',
                'order' => $foodOrder->fresh(),
            ]);
        }

        return back()->with('success', 'Préparation commencée !');
    }

    /**
     * Marquer comme prête
     */
    public function markReady(FoodOrder $foodOrder)
    {
        $this->authorizeOrder($foodOrder);

        if (!$foodOrder->isPreparing()) {
            return back()->with('error', 'Cette commande n\'est pas en préparation.');
        }

        $foodOrder->markAsReady();
        
        // Générer le code de livraison pour sécuriser la remise
        $foodOrder->generateDeliveryCode();

        // Notifier le client
        try {
            if ($foodOrder->client) {
                $foodOrder->client->notify(new FoodOrderReady($foodOrder));
            } else {
                Log::warning("FoodOrder #{$foodOrder->id} n'a pas de client associé - notification non envoyée");
            }
        } catch (\Exception $e) {
            Log::error('Erreur notification FoodOrderReady: ' . $e->getMessage());
        }

        // Notifier le livreur si une livraison est assignée
        try {
            if ($foodOrder->delivery_type === 'delivery' && $foodOrder->driver_id) {
                $foodOrder->loadMissing('driver.user');
                if ($foodOrder->driver && $foodOrder->driver->user) {
                    $foodOrder->driver->user->notify(new \App\Notifications\FoodOrderReadyForDriver($foodOrder));
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur notification FoodOrderReadyForDriver: ' . $e->getMessage());
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Commande prête ! Le client a été notifié.',
                'order' => $foodOrder->fresh(),
                'delivery_code' => $foodOrder->delivery_code,
            ]);
        }

        return back()->with('success', 'Commande prête ! Le client a été notifié.');
    }

    /**
     * Marquer comme livrée/récupérée
     */
    public function markDelivered(FoodOrder $foodOrder)
    {
        $this->authorizeOrder($foodOrder);
        $wantsJson = request()->expectsJson() || request()->wantsJson() || request()->ajax();

        if (!$foodOrder->isReady()) {
            return back()->with('error', 'Cette commande n\'est pas encore prête.');
        }

        // En livraison, la validation doit se faire via le code client (livreur interne/externe).
        if (($foodOrder->delivery_type ?? '') === FoodOrder::DELIVERY_DELIVERY) {
            $message = 'Validation impossible ici. Le livreur doit valider la livraison avec le code client.';
            return $wantsJson
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        // Vérifier que le paiement en ligne est fait si requis
        $paymentPolicy = $foodOrder->getPaymentPolicy();
        $pType   = $paymentPolicy['type'] ?? 'cash';
        $pStatus = $foodOrder->payment_status;

        if ($pType === 'full_prepay') {
            // Paiement intégral requis → doit être 'paid' ou 'pending_capture' (autorisé)
            if (!in_array($pStatus, [FoodOrder::PAYMENT_PAID, FoodOrder::PAYMENT_PENDING_CAPTURE])) {
                return back()->with('error', 'Le client doit payer intégralement en ligne avant la remise de la commande.');
            }
        } elseif ($pType === 'deposit') {
            // Acompte requis → doit être 'paid' ou 'pending_capture' (acompte autorisé/payé)
            if (!in_array($pStatus, [FoodOrder::PAYMENT_PAID, FoodOrder::PAYMENT_PENDING_CAPTURE, 'partial'])) {
                return back()->with('error', "Le client doit payer l'acompte de {$paymentPolicy['percent']}% en ligne avant la remise.");
            }
        }
        // cash → pas de vérification, passage direct

        $foodOrder->markAsDelivered();

        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'message' => 'Commande livrée !',
                'order' => $foodOrder->fresh(),
            ]);
        }

        return back()->with('success', 'Commande livrée !');
    }

    /**
     * Confirmer la réception (côté prestataire)
     */
    public function confirmReception(FoodOrder $foodOrder)
    {
        $this->authorizeOrder($foodOrder);

        if (!$foodOrder->isDelivered()) {
            return back()->with('error', 'Cette commande n\'a pas encore été livrée.');
        }

        $foodOrder->confirmByPrestataire();

        // Si les deux ont confirmé, notifier
        if ($foodOrder->fresh()->isCompleted()) {
            try {
                if ($foodOrder->client) {
                    $foodOrder->client->notify(new FoodOrderCompleted($foodOrder));
                } else {
                    Log::warning("FoodOrder #{$foodOrder->id} n'a pas de client associé - notification non envoyée");
                }
            } catch (\Exception $e) {
                Log::error('Erreur notification FoodOrderCompleted: ' . $e->getMessage());
            }
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Réception confirmée !',
                'order' => $foodOrder->fresh(),
            ]);
        }

        return back()->with('success', 'Réception confirmée !');
    }

    /**
     * Confirmer le paiement en espèces (côté prestataire)
     */
    public function confirmCashPayment(FoodOrder $foodOrder)
    {
        $this->authorizeOrder($foodOrder);

        if ($foodOrder->payment_status === FoodOrder::PAYMENT_PAID) {
            return back()->with('info', 'Cette commande a déjà été payée.');
        }

        // Vérifier la politique de paiement
        $paymentPolicy = $foodOrder->getPaymentPolicy();
        $pType = $paymentPolicy['type'] ?? 'cash';

        if ($pType === 'full_prepay') {
            // Full prepay → pas de cash autorisé, tout doit être en ligne
            return back()->with('error', 'Cette commande nécessite un paiement intégral en ligne. Le client doit payer via son espace.');
        }

        // Calculer le montant à encaisser en espèces
        if ($pType === 'deposit') {
            // Acompte payé en ligne → le reste en espèces
            $depositAmount = round($foodOrder->total * ($paymentPolicy['percent'] / 100), 2);
            $cashAmount = round($foodOrder->total - $depositAmount, 2);
            $description = "Paiement espèces (reste après acompte {$paymentPolicy['percent']}%) commande #{$foodOrder->order_number}";
        } else {
            // Cash total
            $cashAmount = $foodOrder->total;
            $description = "Paiement espèces commande #{$foodOrder->order_number}";
        }

        $foodOrder->update([
            'payment_status' => FoodOrder::PAYMENT_PAID,
            'payment_method' => $pType === 'deposit' ? 'mixed' : 'cash',
            'paid_at' => now(),
        ]);

        // Créer la transaction
        \App\Models\PaymentTransaction::systemCreate([
            'user_id' => $foodOrder->client_id,
            'food_order_id' => $foodOrder->id,
            'amount' => $cashAmount,
            'type' => 'payment',
            'currency' => 'eur',
            'status' => 'paid',
            'provider' => 'cash',
            'transaction_id' => 'CASH-' . $foodOrder->order_number,
            'payment_method' => 'cash',
            'description' => $description,
            'metadata' => [
                'food_order_id' => $foodOrder->id,
                'confirmed_by' => Auth::id(),
                'payment_policy' => $pType,
                'cash_amount' => $cashAmount,
            ],
        ]);

        $message = $pType === 'deposit'
            ? "Paiement espèces de {$cashAmount}€ confirmé (reste après acompte)."
            : "Paiement en espèces de {$cashAmount}€ confirmé !";

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Obtenir les nouvelles commandes + stats à jour (pour le refresh AJAX)
     */
    public function getNewOrders()
    {
        $prestataire = Auth::user()->prestataire;
        $hasPaymentIntentId = self::hasFoodOrdersColumn('payment_intent_id');
        $visiblePaymentStatuses = [
            FoodOrder::PAYMENT_PAID,
            FoodOrder::PAYMENT_PENDING_CAPTURE,
            FoodOrder::PAYMENT_PENDING,
            'partial',
            'pending',
        ];

        $pendingCount = FoodOrder::where('prestataire_id', $prestataire->id)
            ->where('status', 'pending')
            ->where(function ($q) use ($visiblePaymentStatuses) {
                $q->where('payment_method', 'cash')
                    ->orWhereIn('payment_status', $visiblePaymentStatuses);
            })
            ->count();

        $kitchenCount = FoodOrder::where('prestataire_id', $prestataire->id)
            ->whereIn('status', ['pending', 'accepted', 'preparing', 'ready'])
            ->where(function ($q) use ($visiblePaymentStatuses) {
                $q->where('payment_method', 'cash')
                    ->orWhereIn('payment_status', $visiblePaymentStatuses);
            })
            ->count();

        $activeDeliveryQuery = FoodOrder::where('prestataire_id', $prestataire->id)
            ->where('delivery_type', 'delivery')
            ->whereNotIn('status', ['completed', 'delivered', 'cancelled'])
            ->where(function ($q) use ($visiblePaymentStatuses) {
                $q->where('payment_method', 'cash')
                    ->orWhereIn('payment_status', $visiblePaymentStatuses);
            })
            ->where(function ($query) {
                $query->whereIn('status', ['accepted', 'preparing', 'ready'])
                    ->orWhereIn('delivery_status', ['pending', 'assigned', 'self_delivery', 'picked_up', 'in_transit'])
                    ->orWhereNotNull('driver_id');
            })
            ->where(function ($query) use ($hasPaymentIntentId) {
                // Même logique que dashboard/internal-map pour éviter les points fantômes.
                $query->whereNull('payment_status')
                    ->orWhere('payment_status', '!=', FoodOrder::PAYMENT_PAID)
                    ->orWhere(function ($paidQuery) use ($hasPaymentIntentId) {
                        $paidQuery->where('payment_status', FoodOrder::PAYMENT_PAID)
                            ->where(function ($methodQuery) use ($hasPaymentIntentId) {
                                $methodQuery->whereNotIn('payment_method', ['cash', 'mixed']);

                                // Fallback schéma: certaines bases legacy n'ont pas payment_intent_id
                                if ($hasPaymentIntentId) {
                                    $methodQuery->orWhere(function ($legacyOnlineQuery) {
                                        $legacyOnlineQuery->whereNull('payment_method')
                                            ->whereNotNull('payment_intent_id');
                                    });
                                }
                            });
                    });
            });

        $deliveryCount = (clone $activeDeliveryQuery)->count();
        $deliveryUpdatedAt = (clone $activeDeliveryQuery)->max('updated_at');
        $deliveryUpdatedAtIso = $deliveryUpdatedAt
            ? Carbon::parse($deliveryUpdatedAt)->toIso8601String()
            : null;
        $deliverySyncKey = self::buildDeliverySyncKey(
            (clone $activeDeliveryQuery)->get([
                'id',
                'order_number',
                'status',
                'delivery_status',
                'driver_id',
                'payment_status',
                'updated_at',
            ]),
            $this->buildInternalDeliveryBoard($prestataire->id)
        );

        // Stats à jour pour le refresh sans rechargement
        $today = now()->startOfDay();
        $stats = self::buildPaymentStats($prestataire->id, $today);

        return response()->json([
            'count' => $pendingCount,
            'kitchen' => $kitchenCount,
            'delivery_count' => $deliveryCount,
            'delivery_updated_at' => $deliveryUpdatedAtIso,
            'delivery_sync_key' => $deliverySyncKey,
            'stats' => $stats,
        ]);
    }

    /**
     * Empreinte légère mais fiable de l'état livraison affiché sur le dashboard.
     */
    protected static function buildDeliverySyncKey($deliveryOrders, array $internalBoard = []): string
    {
        $orderSignature = collect($deliveryOrders)
            ->map(function ($order) {
                $updatedAt = data_get($order, 'updated_at');

                if ($updatedAt instanceof \DateTimeInterface) {
                    $updatedAt = $updatedAt->format(DATE_ATOM);
                } elseif (!empty($updatedAt)) {
                    try {
                        $updatedAt = Carbon::parse($updatedAt)->toIso8601String();
                    } catch (\Throwable $e) {
                        $updatedAt = (string) $updatedAt;
                    }
                } else {
                    $updatedAt = '';
                }

                return implode(':', [
                    (string) data_get($order, 'id', ''),
                    (string) data_get($order, 'order_number', ''),
                    (string) data_get($order, 'status', ''),
                    (string) data_get($order, 'delivery_status', ''),
                    (string) data_get($order, 'driver_id', ''),
                    (string) data_get($order, 'payment_status', ''),
                    (string) $updatedAt,
                ]);
            })
            ->sort()
            ->values()
            ->implode('|');

        $fleetSignature = collect(data_get($internalBoard, 'fleet', []))
            ->map(function ($driver) {
                $routeSignature = collect(data_get($driver, 'route_points', []))
                    ->map(function ($point) {
                        return implode(':', [
                            (string) data_get($point, 'kind', ''),
                            (string) data_get($point, 'sequence', ''),
                            (string) data_get($point, 'order_number', ''),
                            (string) data_get($point, 'status', ''),
                        ]);
                    })
                    ->implode(',');

                return implode(':', [
                    (string) data_get($driver, 'driver_id', ''),
                    (string) data_get($driver, 'driver_name', ''),
                    (string) ((int) ((bool) data_get($driver, 'is_available', false))),
                    (string) data_get($driver, 'driver_status', ''),
                    (string) data_get($driver, 'active_orders_count', 0),
                    (string) data_get($driver, 'remaining_points_count', 0),
                    (string) data_get($driver, 'remaining_eta_minutes', 0),
                    $routeSignature,
                ]);
            })
            ->sort()
            ->values()
            ->implode('|');

        $statsSignature = implode(':', [
            (string) data_get($internalBoard, 'stats.drivers_total', 0),
            (string) data_get($internalBoard, 'stats.drivers_available', 0),
            (string) data_get($internalBoard, 'stats.drivers_on_mission', 0),
            (string) data_get($internalBoard, 'stats.active_orders_total', 0),
            (string) data_get($internalBoard, 'stats.remaining_points_total', 0),
        ]);

        return sha1($orderSignature . '//' . $fleetSignature . '//' . $statsSignature);
    }

    /**
     * Construire les statistiques de paiement (réutilisable dashboard + polling)
     */
    protected static function buildPaymentStats(int $prestataireId, $today): array
    {
        // Statuts non-payés = tout sauf paid et refunded
        $unpaidStatuses = ['pending', 'pending_capture', 'partial'];
        $default = [
            'revenue_today' => 0.0,
            'revenue_month' => 0.0,
            'pending_payments' => 0.0,
            'paid_count_today' => 0,
            'pending_count' => 0,
        ];

        if (!self::hasFoodOrdersColumn('payment_status')) {
            return $default;
        }

        $paidDateColumn = self::hasFoodOrdersColumn('paid_at') ? 'paid_at' : 'updated_at';

        try {
            return [
                'revenue_today' => FoodOrder::where('prestataire_id', $prestataireId)
                    ->where('payment_status', 'paid')
                    ->whereDate($paidDateColumn, $today)
                    ->sum('total'),
                'revenue_month' => FoodOrder::where('prestataire_id', $prestataireId)
                    ->where('payment_status', 'paid')
                    ->whereMonth($paidDateColumn, now()->month)
                    ->whereYear($paidDateColumn, now()->year)
                    ->sum('total'),
                'pending_payments' => FoodOrder::where('prestataire_id', $prestataireId)
                    ->whereIn('payment_status', $unpaidStatuses)
                    ->whereNotIn('status', ['cancelled', 'rejected'])
                    ->sum('total'),
                'paid_count_today' => FoodOrder::where('prestataire_id', $prestataireId)
                    ->where('payment_status', 'paid')
                    ->whereDate($paidDateColumn, $today)
                    ->count(),
                'pending_count' => FoodOrder::where('prestataire_id', $prestataireId)
                    ->whereIn('payment_status', $unpaidStatuses)
                    ->whereNotIn('status', ['cancelled', 'rejected'])
                    ->count(),
            ];
        } catch (\Throwable $e) {
            Log::warning('FoodOrderController payment stats fallback: ' . $e->getMessage());
            return $default;
        }
    }

    /**
     * Détail des chiffres de la barre de stats du dashboard.
     */
    protected static function buildDashboardStatDetails(int $prestataireId, array $orders = []): array
    {
        $paidDateColumn = self::hasFoodOrdersColumn('paid_at') ? 'paid_at' : 'updated_at';
        $unpaidStatuses = ['pending', 'pending_capture', 'partial'];
        $itemLimit = 100;

        $mapOrder = static function (FoodOrder $order, string $dateField = 'created_at', ?string $datePrefix = null): array {
            $dateValue = $order->{$dateField} ?? $order->created_at;
            $dateText = $dateValue instanceof \DateTimeInterface
                ? $dateValue->format('d/m/Y H:i')
                : null;

            return [
                'number' => (string) ($order->order_number ?? ('#' . $order->id)),
                'client' => (string) ($order->client->name ?? 'Client'),
                'total' => (float) ($order->total ?? 0),
                'status' => (string) ($order->status ?? ''),
                'delivery_type' => (string) ($order->delivery_type ?? ''),
                'date' => $dateText,
                'date_prefix' => $datePrefix,
                'url' => route('prestataire.food-orders.show', $order),
            ];
        };

        $buildPayload = static function (
            string $title,
            string $subtitle,
            int $totalCount,
            float $totalAmount,
            $items,
            int $itemLimit
        ): array {
            $items = collect($items)->values();

            return [
                'title' => $title,
                'subtitle' => $subtitle,
                'total_count' => $totalCount,
                'total_amount' => round($totalAmount, 2),
                'truncated' => $totalCount > $items->count() || $items->count() >= $itemLimit,
                'items' => $items->all(),
            ];
        };

        try {
            $today = now()->toDateString();
            $month = now()->month;
            $year = now()->year;

            $todayPaidBase = FoodOrder::where('prestataire_id', $prestataireId)
                ->where('payment_status', 'paid')
                ->whereDate($paidDateColumn, $today);

            $todayPaidOrders = (clone $todayPaidBase)
                ->with(['client'])
                ->orderByDesc($paidDateColumn)
                ->limit($itemLimit)
                ->get()
                ->map(fn (FoodOrder $order) => $mapOrder($order, $paidDateColumn, 'Payee le'));

            $monthPaidBase = FoodOrder::where('prestataire_id', $prestataireId)
                ->where('payment_status', 'paid')
                ->whereMonth($paidDateColumn, $month)
                ->whereYear($paidDateColumn, $year);

            $monthPaidOrders = (clone $monthPaidBase)
                ->with(['client'])
                ->orderByDesc($paidDateColumn)
                ->limit($itemLimit)
                ->get()
                ->map(fn (FoodOrder $order) => $mapOrder($order, $paidDateColumn, 'Payee le'));

            $pendingBase = FoodOrder::where('prestataire_id', $prestataireId)
                ->whereIn('payment_status', $unpaidStatuses)
                ->whereNotIn('status', ['cancelled', 'rejected']);

            $pendingOrders = (clone $pendingBase)
                ->with(['client'])
                ->orderByDesc('created_at')
                ->limit($itemLimit)
                ->get()
                ->map(fn (FoodOrder $order) => $mapOrder($order, 'created_at', 'Creee le'));

            $kitchenOrders = collect([
                ...($orders['pending'] ?? collect())->all(),
                ...($orders['accepted'] ?? collect())->all(),
                ...($orders['preparing'] ?? collect())->all(),
                ...($orders['ready'] ?? collect())->all(),
            ])
                ->sortBy(function (FoodOrder $order) {
                    $priority = match ((string) $order->status) {
                        'pending' => 0,
                        'accepted' => 1,
                        'preparing' => 2,
                        'ready' => 3,
                        default => 9,
                    };

                    $createdAt = $order->created_at instanceof \DateTimeInterface
                        ? $order->created_at->getTimestamp()
                        : 0;

                    return sprintf('%02d-%010d', $priority, $createdAt);
                })
                ->take($itemLimit)
                ->map(fn (FoodOrder $order) => $mapOrder($order, 'created_at', 'Creee le'))
                ->values();

            return [
                'today' => $buildPayload(
                    "CA aujourd'hui",
                    "Commandes payees aujourd'hui",
                    (clone $todayPaidBase)->count(),
                    (float) (clone $todayPaidBase)->sum('total'),
                    $todayPaidOrders,
                    $itemLimit
                ),
                'paid' => $buildPayload(
                    'Commandes payees',
                    "Nombre de commandes payees aujourd'hui",
                    (clone $todayPaidBase)->count(),
                    (float) (clone $todayPaidBase)->sum('total'),
                    $todayPaidOrders,
                    $itemLimit
                ),
                'pending' => $buildPayload(
                    'En attente',
                    'Paiements en attente',
                    (clone $pendingBase)->count(),
                    (float) (clone $pendingBase)->sum('total'),
                    $pendingOrders,
                    $itemLimit
                ),
                'month' => $buildPayload(
                    'CA ce mois',
                    'Commandes payees ce mois',
                    (clone $monthPaidBase)->count(),
                    (float) (clone $monthPaidBase)->sum('total'),
                    $monthPaidOrders,
                    $itemLimit
                ),
                'kitchen' => $buildPayload(
                    'En cuisine',
                    'Commandes actives en cuisine',
                    collect($orders)
                        ->only(['pending', 'accepted', 'preparing', 'ready'])
                        ->flatten(1)
                        ->count(),
                    (float) collect($orders)
                        ->only(['pending', 'accepted', 'preparing', 'ready'])
                        ->flatten(1)
                        ->sum('total'),
                    $kitchenOrders,
                    $itemLimit
                ),
            ];
        } catch (\Throwable $e) {
            Log::warning('FoodOrderController stat details fallback: ' . $e->getMessage());
            return self::emptyDashboardStatDetails();
        }
    }

    protected static function emptyDashboardStatDetails(): array
    {
        return [
            'today' => ['title' => "CA aujourd'hui", 'subtitle' => '', 'total_count' => 0, 'total_amount' => 0.0, 'truncated' => false, 'items' => []],
            'paid' => ['title' => 'Commandes payees', 'subtitle' => '', 'total_count' => 0, 'total_amount' => 0.0, 'truncated' => false, 'items' => []],
            'pending' => ['title' => 'En attente', 'subtitle' => '', 'total_count' => 0, 'total_amount' => 0.0, 'truncated' => false, 'items' => []],
            'month' => ['title' => 'CA ce mois', 'subtitle' => '', 'total_count' => 0, 'total_amount' => 0.0, 'truncated' => false, 'items' => []],
            'kitchen' => ['title' => 'En cuisine', 'subtitle' => '', 'total_count' => 0, 'total_amount' => 0.0, 'truncated' => false, 'items' => []],
        ];
    }

    /**
     * Vérifie la présence d'une colonne food_orders (tolérance schéma legacy).
     */
    private static function hasFoodOrdersColumn(string $column): bool
    {
        static $columnsCache = null;

        if ($columnsCache === null) {
            try {
                if (!TableExistenceCache::has('food_orders')) {
                    $columnsCache = [];
                } else {
                    $columnsCache = array_fill_keys(Schema::getColumnListing('food_orders'), true);
                }
            } catch (\Throwable $e) {
                Log::warning('FoodOrderController schema introspection failed: ' . $e->getMessage());
                $columnsCache = [];
            }
        }

        return isset($columnsCache[$column]);
    }

    /**
     * Statistiques des commandes
     */
    public function stats(Request $request)
    {
        $prestataire = Auth::user()->prestataire;
        
        $period = $request->input('period', 'week');
        $startDate = match ($period) {
            'today' => today(),
            'week' => today()->subWeek(),
            'month' => today()->subMonth(),
            'year' => today()->subYear(),
            default => today()->subWeek(),
        };

        $orders = FoodOrder::where('prestataire_id', $prestataire->id)
            ->where('created_at', '>=', $startDate);

        $stats = [
            'total_orders' => (clone $orders)->count(),
            'completed_orders' => (clone $orders)->where('status', 'completed')->count(),
            'cancelled_orders' => (clone $orders)->where('status', 'cancelled')->count(),
            'total_revenue' => (clone $orders)->where('status', 'completed')->sum('total'),
            'average_order' => (clone $orders)->where('status', 'completed')->avg('total') ?? 0,
            'top_products' => DB::table('food_order_items')
                ->join('food_orders', 'food_order_items.food_order_id', '=', 'food_orders.id')
                ->where('food_orders.prestataire_id', $prestataire->id)
                ->where('food_orders.created_at', '>=', $startDate)
                ->where('food_orders.status', 'completed')
                ->select('food_order_items.product_name', DB::raw('SUM(food_order_items.quantity) as total_quantity'))
                ->groupBy('food_order_items.product_name')
                ->orderByDesc('total_quantity')
                ->limit(5)
                ->get(),
        ];

        return view('prestataire.food-orders.stats', compact('stats', 'period', 'prestataire'));
    }

    /**
     * Vérifier le code de livraison et déclencher les paiements
     */
    public function verifyDeliveryCode(Request $request, FoodOrder $foodOrder)
    {
        $this->authorizeOrder($foodOrder);
        $wantsJson = $request->expectsJson() || $request->wantsJson() || $request->isJson();

        $request->validate([
            'code' => 'required|string|size:4',
        ]);

        // Vérifier si le code est expiré (24h)
        if ($foodOrder->isCodeExpired()) {
            $message = 'Ce code a expiré. La commande sera automatiquement remboursée.';
            return $wantsJson
                ? response()->json(['success' => false, 'message' => $message, 'expired' => true], 410)
                : back()->with('error', $message);
        }

        // Vérifier si trop de tentatives (verrouillage temporaire)
        if ($foodOrder->isCodeLocked()) {
            $unlockTime = $foodOrder->code_locked_until->format('H:i');
            $message = "Trop de tentatives. Code verrouillé jusqu'à {$unlockTime}.";
            return $wantsJson
                ? response()->json(['success' => false, 'message' => $message, 'locked' => true], 429)
                : back()->with('error', $message);
        }

        if (!$foodOrder->isReady() && !$foodOrder->isDelivered()) {
            $message = 'Cette commande n\'est pas prête pour la validation.';
            return $wantsJson
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        if ($foodOrder->code_verified_at) {
            $message = 'Ce code a déjà été vérifié.';
            return $wantsJson
                ? response()->json(['success' => false, 'message' => $message], 409)
                : back()->with('error', $message);
        }

        // Logger la tentative (anti brute-force)
        try {
            DB::table('food_order_code_attempts')->insert([
                'food_order_id' => $foodOrder->id,
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'code_entered' => substr($request->code, 0, 4),
                'success' => 0,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Table peut ne pas exister, ignorer
        }

        if (!$foodOrder->verifyDeliveryCode($request->code)) {
            // Incrémenter les tentatives
            $foodOrder->incrementCodeAttempts();
            
            $attemptsLeft = FoodOrder::MAX_CODE_ATTEMPTS - $foodOrder->fresh()->code_attempts;
            $message = $attemptsLeft > 0
                ? "Code incorrect. {$attemptsLeft} tentative(s) restante(s)."
                : 'Code incorrect. Trop de tentatives, verrouillé 30 min.';
            
            return $wantsJson
                ? response()->json(['success' => false, 'message' => $message, 'attempts_left' => max(0, $attemptsLeft)], 422)
                : back()->with('error', $message);
        }

        // Marquer la tentative comme réussie
        try {
            DB::table('food_order_code_attempts')
                ->where('food_order_id', $foodOrder->id)
                ->orderByDesc('created_at')
                ->limit(1)
                ->update(['success' => 1]);
        } catch (\Exception $e) {
            // Ignorer
        }

        // Réinitialiser les tentatives
        $foodOrder->resetCodeAttempts();

        // Sécurité paiement selon la politique
        $paymentPolicy = $foodOrder->getPaymentPolicy();
        $pType = $paymentPolicy['type'] ?? 'cash';
        $isCash = ($pType === 'cash');
        $isDeposit = ($pType === 'deposit');
        
        // Pour full_prepay : doit être payé intégralement en ligne
        if ($pType === 'full_prepay' && $foodOrder->payment_status !== FoodOrder::PAYMENT_PAID) {
            $message = 'Paiement intégral en ligne non confirmé. Le client doit payer avant la validation du code.';
            return $wantsJson
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }
        
        // Pour deposit : l'acompte doit être payé (paid ou pending_capture), le reste peut être cash
        if ($isDeposit && !in_array($foodOrder->payment_status, [FoodOrder::PAYMENT_PAID, FoodOrder::PAYMENT_PENDING_CAPTURE, 'partial'])) {
            $message = "L'acompte de {$paymentPolicy['percent']}% n'a pas été payé en ligne.";
            return $wantsJson
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        // Code correct → Traiter les paiements
        $foodOrder->processPayouts();

        // Flux espèces / mixte: à la remise (code vérifié) on considère le paiement encaissé
        $needsCashCompletion = ($isCash || $isDeposit) 
            && $foodOrder->payment_status !== FoodOrder::PAYMENT_PAID;
        
        if ($needsCashCompletion) {
            // Calculer le montant cash encaissé
            if ($isDeposit) {
                $depositAmount = round($foodOrder->total * ($paymentPolicy['percent'] / 100), 2);
                $cashAmount = round($foodOrder->total - $depositAmount, 2);
                $payMethod = 'mixed';
                $description = "Paiement espèces {$cashAmount}€ (reste après acompte {$paymentPolicy['percent']}%) commande #{$foodOrder->order_number}";
            } else {
                $cashAmount = $foodOrder->total;
                $payMethod = 'cash';
                $description = "Paiement espèces commande #{$foodOrder->order_number}";
            }

            $foodOrder->update([
                'payment_status' => FoodOrder::PAYMENT_PAID,
                'payment_method' => $payMethod,
                'paid_at' => now(),
            ]);

            // Créer la transaction (si pas déjà créée)
            try {
                if (class_exists(\App\Models\PaymentTransaction::class)) {
                    $alreadyExists = \App\Models\PaymentTransaction::where('food_order_id', $foodOrder->id)
                        ->where('type', 'payment')
                        ->where('status', 'paid')
                        ->exists();

                    if (!$alreadyExists) {
                        \App\Models\PaymentTransaction::systemCreate([
                            'user_id' => $foodOrder->client_id,
                            'food_order_id' => $foodOrder->id,
                            'amount' => $cashAmount,
                            'type' => 'payment',
                            'currency' => 'eur',
                            'status' => 'paid',
                            'provider' => 'cash',
                            'transaction_id' => 'CASH-' . $foodOrder->order_number,
                            'payment_method' => 'cash',
                            'description' => $description,
                            'metadata' => [
                                'food_order_id' => $foodOrder->id,
                                'confirmed_by' => Auth::id(),
                                'confirmed_via' => 'delivery_code',
                                'payment_policy' => $pType,
                                'cash_amount' => $cashAmount,
                            ],
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Ne pas bloquer la validation du code si la transaction échoue
                Log::error('Erreur création PaymentTransaction (cash via code): ' . $e->getMessage());
            }
        }

        // Notifier le client
        try {
            if ($foodOrder->client) {
                $foodOrder->client->notify(new FoodOrderCompleted($foodOrder));
            } else {
                Log::warning("FoodOrder #{$foodOrder->id} n'a pas de client associé - notification non envoyée");
            }
        } catch (\Exception $e) {
            Log::error('Erreur notification FoodOrderCompleted: ' . $e->getMessage());
        }

        if ($wantsJson) {
            return response()->json(['success' => true, 'message' => 'Code vérifié ! Commande terminée.', 'order' => $foodOrder->fresh()]);
        }

        return back()->with('success', '✅ Code vérifié ! Commande terminée et paiements traités.');
    }

    /**
     * Le prestataire décide de livrer lui-même
     */
    public function deliverMyself(FoodOrder $foodOrder)
    {
        $this->authorizeOrder($foodOrder);

        if (!$foodOrder->isReady()) {
            return back()->with('error', 'La commande doit être prête avant de pouvoir la livrer.');
        }

        // Retirer le livreur externe s'il y en avait un
        $foodOrder->update([
            'driver_id' => null,
            'delivery_status' => 'self_delivery',
            'driver_commission' => 0,
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vous livrez cette commande vous-même.',
            ]);
        }

        return back()->with('success', 'C\'est noté ! Vous livrez cette commande vous-même.');
    }

    /**
     * Affecter/Désaffecter un livreur interne à une commande food.
     * Utilisé par la carte interne des livraisons.
     */
    public function assignInternalDriver(Request $request, FoodOrder $foodOrder)
    {
        $this->authorizeOrder($foodOrder);
        $wantsJson = $request->expectsJson() || $request->wantsJson() || $request->ajax();

        if ($foodOrder->delivery_type !== FoodOrder::DELIVERY_DELIVERY) {
            $message = 'Affectation impossible: cette commande n\'est pas en livraison.';
            return $wantsJson
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        if (in_array($foodOrder->status, [
            FoodOrder::STATUS_CANCELLED,
            FoodOrder::STATUS_COMPLETED,
            FoodOrder::STATUS_DELIVERED,
        ], true)) {
            $message = 'Affectation impossible: commande déjà terminée ou annulée.';
            return $wantsJson
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        $validated = $request->validate([
            'driver_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $driverId = isset($validated['driver_id']) ? (int) $validated['driver_id'] : null;
        $assigning = $driverId !== null;

        if (!TableExistenceCache::has('delivery_drivers')) {
            $message = 'Table des livreurs indisponible.';
            return $wantsJson
                ? response()->json(['success' => false, 'message' => $message], 500)
                : back()->with('error', $message);
        }

        $hasEmployer = Schema::hasColumn('delivery_drivers', 'employer_prestataire_id');
        $hasInternalFlag = Schema::hasColumn('delivery_drivers', 'is_internal');
        $hasIsActive = Schema::hasColumn('delivery_drivers', 'is_active');

        if (!$hasEmployer) {
            $message = 'Affectation interne non disponible: colonne employeur manquante.';
            return $wantsJson
                ? response()->json(['success' => false, 'message' => $message], 500)
                : back()->with('error', $message);
        }

        $allowedDeliveryStatuses = [
            FoodOrder::DELIVERY_STATUS_PENDING,
            FoodOrder::DELIVERY_STATUS_ASSIGNED,
            'self_delivery',
            null,
            '',
        ];

        if (!in_array($foodOrder->delivery_status, $allowedDeliveryStatuses, true)) {
            $message = 'Affectation verrouillée: la commande est déjà en cours de tournée.';
            return $wantsJson
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        $driver = null;
        if ($assigning) {
            $driverQuery = DeliveryDriver::query()
                ->where('id', $driverId)
                ->where('employer_prestataire_id', $foodOrder->prestataire_id);

            if ($hasInternalFlag) {
                $driverQuery->where('is_internal', true);
            }

            if ($hasIsActive) {
                $driverQuery->where('is_active', true);
            }

            $driver = $driverQuery->with('user')->first();
            if (!$driver) {
                $message = 'Livreur interne introuvable pour ce prestataire.';
                return $wantsJson
                    ? response()->json(['success' => false, 'message' => $message], 422)
                    : back()->with('error', $message);
            }
        }

        DB::beginTransaction();
        try {
            if ($driver) {
                $foodOrder->update([
                    'driver_id' => $driver->id,
                    'driver_accepted_at' => now(),
                    'delivery_status' => FoodOrder::DELIVERY_STATUS_ASSIGNED,
                ]);

                if (
                    $foodOrder->payment_status === FoodOrder::PAYMENT_PENDING_CAPTURE
                    && $foodOrder->status === FoodOrder::STATUS_ACCEPTED
                ) {
                    $foodOrder->capturePayment();
                }
            } else {
                $foodOrder->update([
                    'driver_id' => null,
                    'driver_accepted_at' => null,
                    'delivery_status' => FoodOrder::DELIVERY_STATUS_PENDING,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('assignInternalDriver failed for food_order #' . $foodOrder->id . ': ' . $e->getMessage());
            $message = 'Affectation impossible pour le moment.';

            return $wantsJson
                ? response()->json(['success' => false, 'message' => $message], 500)
                : back()->with('error', $message);
        }

        $foodOrder->loadMissing(['client', 'driver.user', 'prestataire']);

        if ($driver && $driver->user) {
            try {
                $driver->user->notify(new \App\Notifications\FoodOrderReadyForDriver($foodOrder));
            } catch (\Throwable $e) {
                Log::warning('Notification FoodOrderReadyForDriver failed: ' . $e->getMessage());
            }
        }

        if ($driver && $foodOrder->client) {
            try {
                $foodOrder->client->notify(new \App\Notifications\FoodOrderDriverAssigned($foodOrder));
            } catch (\Throwable $e) {
                Log::warning('Notification FoodOrderDriverAssigned failed: ' . $e->getMessage());
            }
        }

        $message = $driver
            ? 'Livreur affecté: ' . ($driver->full_name ?: 'Livreur') . '.'
            : 'Livreur retiré de la commande.';

        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'order_id' => $foodOrder->id,
                'driver_id' => $foodOrder->driver_id,
                'delivery_status' => $foodOrder->delivery_status,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Convertir une commande livraison en retrait sur place
     * Utilisé quand aucun livreur n'est disponible et le prestataire ne peut pas livrer
     */
    public function convertToPickup(FoodOrder $foodOrder)
    {
        $this->authorizeOrder($foodOrder);

        if ($foodOrder->delivery_type !== 'delivery') {
            return back()->with('error', 'Cette commande est déjà en retrait.');
        }

        if (!$foodOrder->isReady() && !$foodOrder->isPreparing()) {
            return back()->with('error', 'Action non disponible pour cette commande.');
        }

        // Sauvegarder l'ancien montant livraison pour remboursement
        $deliveryFee = $foodOrder->delivery_fee;

        // Convertir en retrait
        $foodOrder->update([
            'delivery_type' => 'pickup',
            'delivery_fee' => 0,
            'driver_id' => null,
            'delivery_status' => null,
            'driver_commission' => 0,
            'total' => $foodOrder->subtotal, // Le total devient le sous-total (sans frais livraison)
        ]);

        // Notifier le client du changement
        try {
            if ($foodOrder->client) {
                $foodOrder->client->notify(new \App\Notifications\FoodOrderConvertedToPickup($foodOrder, $deliveryFee));
            } else {
                Log::warning("FoodOrder #{$foodOrder->id} n'a pas de client associé - notification non envoyée");
            }
        } catch (\Exception $e) {
            Log::error('Erreur notification ConvertedToPickup: ' . $e->getMessage());
        }

        // Rembourser les frais de livraison si paiement en ligne
        if ($deliveryFee > 0 && $foodOrder->payment_method === 'card' && $foodOrder->payment_intent_id) {
            try {
                $refunded = $foodOrder->refundPayment(
                    'Remboursement frais de livraison - conversion en retrait sur place',
                    $deliveryFee
                );
                if ($refunded) {
                    Log::info("FoodOrder #{$foodOrder->id}: frais de livraison {$deliveryFee}€ remboursés (conversion pickup)");
                } else {
                    Log::warning("FoodOrder #{$foodOrder->id}: échec remboursement frais livraison {$deliveryFee}€");
                }
            } catch (\Exception $e) {
                Log::error("FoodOrder #{$foodOrder->id}: erreur remboursement frais livraison: " . $e->getMessage());
            }
        }

        $message = 'Commande convertie en retrait sur place. Le client a été notifié.';
        if ($deliveryFee > 0) {
            $message .= ' Les frais de livraison (' . number_format($deliveryFee, 2) . '€) seront remboursés.';
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Gère le flux financier d'annulation quand le prestataire annule/refuse.
     * Règle métier:
     * - Autorisation non capturée => annuler l'autorisation (pas de débit final client)
     * - Paiement capturé => rembourser le client à 100%
     * - Les frais Stripe d'annulation sont imputés au prestataire (jamais à la plateforme)
     */
    // Audit 4.1: logique financière extraite dans FoodOrderCancellationService
    protected function handleCancellationFinanceAsPrestataire(FoodOrder $foodOrder, string $reason): array
    {
        return app(\App\Services\FoodOrderCancellationService::class)->handleCancellationFinance($foodOrder, $reason);
    }

    // Audit 4.1: méthodes déléguées au FoodOrderCancellationService
    protected function chargeCancellationStripeFeeToPrestataire(FoodOrder $foodOrder, float $feeAmount, string $reason): array
    {
        return app(\App\Services\FoodOrderCancellationService::class)->chargeStripeFeeToPrestataire($foodOrder, $feeAmount, $reason);
    }

    protected function rollbackCancellationStripeFeeToPrestataire(array $chargeContext, float $feeAmount, string $reason): void
    {
        app(\App\Services\FoodOrderCancellationService::class)->rollbackStripeFee($chargeContext, $feeAmount, $reason);
    }

    /**
     * Vérifier que la commande appartient au prestataire connecté
     */
    protected function authorizeOrder(FoodOrder $foodOrder): void
    {
        $user = Auth::user();
        $prestataire = $user?->prestataire;

        if (!$prestataire || $foodOrder->prestataire_id !== $prestataire->id) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à cette commande.');
        }
    }

    private function resolveInternalMapContext(Request $request): ?array
    {
        $user = Auth::user();
        if ($user && $user->prestataire) {
            return [
                'prestataire' => $user->prestataire,
                'internal_driver_mode' => false,
                'internal_driver' => null,
            ];
        }

        $internalDriver = $this->resolveActiveInternalDriverFromSession($request);
        if (!$internalDriver) {
            return null;
        }

        $prestataireId = (int) ($internalDriver->employer_prestataire_id ?? 0);
        if ($prestataireId <= 0) {
            return null;
        }

        $prestataire = Prestataire::find($prestataireId);
        if (!$prestataire) {
            return null;
        }

        return [
            'prestataire' => $prestataire,
            'internal_driver_mode' => true,
            'internal_driver' => $internalDriver,
        ];
    }

    private function resolveActiveInternalDriverFromSession(Request $request): ?DeliveryDriver
    {
        if (!TableExistenceCache::has('delivery_drivers')) {
            return null;
        }

        $internalDriverId = (int) $request->session()->get('internal_driver_id', 0);
        if ($internalDriverId <= 0) {
            return null;
        }

        $query = DeliveryDriver::query()->where('id', $internalDriverId);

        if (Schema::hasColumn('delivery_drivers', 'is_internal')) {
            $query->where('is_internal', true);
        }
        if (Schema::hasColumn('delivery_drivers', 'is_active')) {
            $query->where('is_active', true);
        }
        if (Schema::hasColumn('delivery_drivers', 'employer_prestataire_id')) {
            $query->whereNotNull('employer_prestataire_id');
        }

        return $query->with('user')->first();
    }

    /**
     * Construire le tableau de suivi des tournées internes (livreurs du prestataire)
     */
    private function buildInternalDeliveryBoard(int $prestataireId): array
    {
        $default = [
            'fleet' => collect(),
            'stats' => [
                'drivers_total' => 0,
                'drivers_available' => 0,
                'drivers_on_mission' => 0,
                'active_orders_total' => 0,
                'remaining_points_total' => 0,
            ],
        ];

        try {
            if (!TableExistenceCache::has('delivery_drivers') || !TableExistenceCache::has('food_orders')) {
                return $default;
            }

            $hasEmployer = Schema::hasColumn('delivery_drivers', 'employer_prestataire_id');
            $hasInternalFlag = Schema::hasColumn('delivery_drivers', 'is_internal');
            $hasIsActive = Schema::hasColumn('delivery_drivers', 'is_active');

            if (!$hasEmployer) {
                return $default;
            }

            $driverQuery = DeliveryDriver::query();

            if ($hasIsActive) {
                $driverQuery->where('is_active', true);
            }

            $driverQuery->where('employer_prestataire_id', $prestataireId);

            if ($hasInternalFlag) {
                $driverQuery->where('is_internal', true);
            }

            $drivers = $driverQuery
                ->with('user')
                ->with(['foodOrders' => function ($q) use ($prestataireId) {
                    $q->where('prestataire_id', $prestataireId)
                        ->where('delivery_type', FoodOrder::DELIVERY_DELIVERY)
                        ->whereIn('delivery_status', [
                            FoodOrder::DELIVERY_STATUS_ASSIGNED,
                            FoodOrder::DELIVERY_STATUS_PICKED_UP,
                            FoodOrder::DELIVERY_STATUS_IN_TRANSIT,
                        ])
                        ->whereNotIn('status', [
                            FoodOrder::STATUS_CANCELLED,
                            FoodOrder::STATUS_COMPLETED,
                        ])
                        // Sync métier demandé: une commande "encaissée" sort de la tournée.
                        // Compat legacy: payment_method null + payment_intent_id null => espèces.
                        ->where(function ($query) {
                            $query->whereNull('payment_status')
                                ->orWhere('payment_status', '!=', FoodOrder::PAYMENT_PAID)
                                ->orWhere(function ($paidQuery) {
                                    $paidQuery->where('payment_status', FoodOrder::PAYMENT_PAID)
                                        ->where(function ($methodQuery) {
                                            $methodQuery->whereNotIn('payment_method', ['cash', 'mixed'])
                                                ->orWhere(function ($legacyOnlineQuery) {
                                                    $legacyOnlineQuery->whereNull('payment_method')
                                                        ->whereNotNull('payment_intent_id');
                                                });
                                        });
                                });
                        })
                        ->with(['client', 'prestataire']);
                }])
                ->orderBy('is_available', 'desc')
                ->orderBy('rating', 'desc')
                ->get();

            $activeOrderIds = $drivers
                ->pluck('foodOrders')
                ->flatten()
                ->pluck('id')
                ->unique()
                ->values();

            $batchOrdersByFoodOrder = collect();
            if ($activeOrderIds->isNotEmpty() && TableExistenceCache::has('delivery_batch_orders')) {
                $batchOrdersByFoodOrder = DeliveryBatchOrder::whereIn('food_order_id', $activeOrderIds)
                    ->get(['food_order_id', 'pickup_order', 'delivery_order'])
                    ->keyBy('food_order_id');
            }

            $fleet = $drivers->map(function ($driver) use ($batchOrdersByFoodOrder) {
                $orders = $driver->foodOrders
                    ->sortBy(function ($order) use ($batchOrdersByFoodOrder) {
                        $meta = $batchOrdersByFoodOrder->get($order->id);
                        $sequence = $meta?->delivery_order ?? 9999;
                        $baseTime = optional($order->ready_at ?? $order->created_at)->getTimestamp() ?? 0;
                        return sprintf('%05d-%010d', $sequence, $baseTime);
                    })
                    ->values();

                $routePoints = collect();

                foreach ($orders as $order) {
                    $meta = $batchOrdersByFoodOrder->get($order->id);

                    if ($order->delivery_status === FoodOrder::DELIVERY_STATUS_ASSIGNED) {
                        $routePoints->push([
                            'kind' => 'pickup',
                            'sequence' => $meta?->pickup_order,
                            'order_number' => $order->order_number ?? ('#' . $order->id),
                            'label' => 'Recuperer la commande',
                            'address' => $order->prestataire->adresse ?? $order->prestataire->address ?? 'Restaurant',
                            'status' => 'assigned',
                        ]);
                    }

                    if (in_array($order->delivery_status, [
                        FoodOrder::DELIVERY_STATUS_ASSIGNED,
                        FoodOrder::DELIVERY_STATUS_PICKED_UP,
                        FoodOrder::DELIVERY_STATUS_IN_TRANSIT,
                    ], true)) {
                        $routePoints->push([
                            'kind' => 'dropoff',
                            'sequence' => $meta?->delivery_order,
                            'order_number' => $order->order_number ?? ('#' . $order->id),
                            'label' => 'Livrer la commande',
                            'address' => $order->delivery_address ?? 'Adresse client',
                            'status' => $order->delivery_status,
                        ]);
                    }
                }

                $routePoints = $routePoints
                    ->sortBy(function ($point) {
                        $seq = $point['sequence'] ?? 9999;
                        $kindWeight = $point['kind'] === 'pickup' ? 0 : 1;
                        return sprintf('%05d-%d', $seq, $kindWeight);
                    })
                    ->values();

                $driverPhone = $driver->phone ?: optional($driver->user)->phone;
                $remainingEtaMinutes = (int) $orders->sum(function ($order) {
                    return (int) ($order->estimated_delivery_time ?? 0);
                });

                return [
                    'driver_id' => $driver->id,
                    'driver_name' => $driver->full_name,
                    'driver_phone' => $driverPhone,
                    'driver_status' => $driver->status ?? 'offline',
                    'is_available' => (bool) ($driver->is_available ?? false),
                    'active_orders_count' => $orders->count(),
                    'remaining_points_count' => $routePoints->count(),
                    'remaining_eta_minutes' => $remainingEtaMinutes,
                    'route_points' => $routePoints,
                ];
            })->values()
                ->sortBy(function (array $orderData) {
                    $priorityRank = (int) ($orderData['priority_rank'] ?? 4);
                    $priorityScore = (int) ($orderData['priority_score'] ?? 0);
                    $dueAt = !empty($orderData['priority_due_at'])
                        ? strtotime((string) $orderData['priority_due_at'])
                        : 9999999999;
                    $sequence = (int) ($orderData['sequence'] ?? 9999);

                    return sprintf(
                        '%d-%06d-%010d-%06d',
                        $priorityRank,
                        999999 - max(0, $priorityScore),
                        $dueAt > 0 ? $dueAt : 9999999999,
                        $sequence
                    );
                })
                ->values();

            $stats = [
                'drivers_total' => $drivers->count(),
                'drivers_available' => $drivers->filter(function ($driver) {
                    return (bool) ($driver->is_available ?? false);
                })->count(),
                'drivers_on_mission' => $fleet->where('active_orders_count', '>', 0)->count(),
                'active_orders_total' => (int) $fleet->sum('active_orders_count'),
                'remaining_points_total' => (int) $fleet->sum('remaining_points_count'),
            ];

            return [
                'fleet' => $fleet,
                'stats' => $stats,
            ];
        } catch (\Throwable $e) {
            Log::warning('FoodOrderController internal board error: ' . $e->getMessage());
            return $default;
        }
    }

    /**
     * Calcul de priorité opérationnelle d'une livraison interne.
     */
    private function buildInternalDeliveryPriority(FoodOrder $order, string $deliveryStatus): array
    {
        $now = now();
        $dueAt = $order->requested_at ?: $order->ready_at;
        $anchorAt = $order->ready_at ?: $order->updated_at ?: $order->created_at;

        $waitMinutes = max(0, (int) optional($anchorAt)->diffInMinutes($now));
        $overdueMinutes = $dueAt ? max(0, (int) $dueAt->diffInMinutes($now, false)) : 0;

        $base = match ($deliveryStatus) {
            FoodOrder::DELIVERY_STATUS_IN_TRANSIT => 100,
            FoodOrder::DELIVERY_STATUS_PICKED_UP => 90,
            FoodOrder::DELIVERY_STATUS_ASSIGNED => 70,
            FoodOrder::DELIVERY_STATUS_PENDING => 55,
            default => 45,
        };

        $score = $base;
        $score += min(40, (int) floor($overdueMinutes / 5));
        $score += min(20, (int) floor($waitMinutes / 6));

        if ($dueAt && $dueAt->isFuture()) {
            $minsToDue = max(0, $now->diffInMinutes($dueAt, false));
            if ($minsToDue <= 45) {
                $score += (int) max(0, 12 - floor($minsToDue / 5));
            }
        }

        $rank = 4;
        $label = 'Basse';
        if ($score >= 120) {
            $rank = 1;
            $label = 'Urgente';
        } elseif ($score >= 92) {
            $rank = 2;
            $label = 'Haute';
        } elseif ($score >= 68) {
            $rank = 3;
            $label = 'Normale';
        }

        return [
            'score' => (int) $score,
            'rank' => $rank,
            'label' => $label,
            'wait_minutes' => $waitMinutes,
            'overdue_minutes' => $overdueMinutes,
            'due_at_iso' => $dueAt ? $dueAt->toIso8601String() : null,
            'due_at_human' => $dueAt ? $dueAt->format('d/m H:i') : null,
            'is_overdue' => $overdueMinutes > 0,
        ];
    }

    /**
     * Payload complet pour la carte interne "pro"
     */
    private function buildInternalMapPayload(
        int $prestataireId,
        float $defaultLat = 0,
        float $defaultLng = 0,
        ?int $selectedDriverId = null,
        string $search = ''
    ): array {
        $payload = [
            'generated_at' => now()->toIso8601String(),
            'origin' => [
                'lat' => $defaultLat ?: null,
                'lng' => $defaultLng ?: null,
            ],
            'center' => [
                'lat' => $defaultLat ?: 46.603354,
                'lng' => $defaultLng ?: 1.888334,
            ],
            'stats' => [
                'drivers_total' => 0,
                'drivers_available' => 0,
                'drivers_on_mission' => 0,
                'active_orders_total' => 0,
                'active_points_total' => 0,
            ],
            'drivers' => [],
            'orders' => [],
            'points' => [],
            'paths' => [],
            'routing' => [
                'mode' => 'driving',
                'use_live_traffic' => true,
                'max_waypoints_per_request' => 23,
            ],
            'filters' => [
                'selected_driver_id' => $selectedDriverId,
                'selected_driver_ids' => !empty($selectedDriverId) ? [(int) $selectedDriverId] : [],
                'search' => $search,
            ],
        ];

        try {
            if (!TableExistenceCache::has('food_orders')) {
                return $payload;
            }

            $hasDriversTable = TableExistenceCache::has('delivery_drivers');
            $hasEmployer = $hasDriversTable && Schema::hasColumn('delivery_drivers', 'employer_prestataire_id');
            $hasInternalFlag = $hasDriversTable && Schema::hasColumn('delivery_drivers', 'is_internal');
            $hasIsActive = $hasDriversTable && Schema::hasColumn('delivery_drivers', 'is_active');

            $drivers = collect();
            if ($hasDriversTable && $hasEmployer) {
                $driverQuery = DeliveryDriver::query();

                $driverQuery->where('employer_prestataire_id', $prestataireId);

                if ($hasInternalFlag) {
                    $driverQuery->where('is_internal', true);
                }

                if ($hasIsActive) {
                    $driverQuery->where('is_active', true);
                }

                if (!empty($selectedDriverId)) {
                    $driverQuery->where('id', $selectedDriverId);
                }

                $drivers = $driverQuery
                    ->with('user')
                    ->orderBy('is_available', 'desc')
                    ->orderBy('rating', 'desc')
                    ->get();
            }

            $internalDriverIds = $drivers->pluck('id')->values();

            $orderQuery = FoodOrder::query()
                ->where('prestataire_id', $prestataireId)
                ->whereNotIn('status', [
                    FoodOrder::STATUS_CANCELLED,
                    FoodOrder::STATUS_COMPLETED,
                    FoodOrder::STATUS_DELIVERED,
                ])
                ->with(['client', 'driver.user', 'items', 'prestataire'])
                ->orderBy('ready_at')
                ->orderBy('updated_at');

            if (!empty($selectedDriverId)) {
                // Mode livreur interne:
                // - afficher les commandes actives affectées au livreur de session
                // - tolérer les historiques où un même compte a plusieurs lignes delivery_drivers
                $selectedDriverIds = collect([(int) $selectedDriverId]);

                if ($hasDriversTable && Schema::hasColumn('delivery_drivers', 'user_id')) {
                    $selectedDriver = $drivers->firstWhere('id', (int) $selectedDriverId);
                    $selectedDriverUserId = (int) ($selectedDriver->user_id ?? 0);

                    if ($selectedDriverUserId <= 0) {
                        $selectedDriverUserId = (int) (DeliveryDriver::query()
                            ->where('id', (int) $selectedDriverId)
                            ->value('user_id') ?? 0);
                    }

                    if ($selectedDriverUserId > 0) {
                        $aliasQuery = DeliveryDriver::query()
                            ->where('user_id', $selectedDriverUserId);

                        if ($hasEmployer) {
                            $aliasQuery->where('employer_prestataire_id', $prestataireId);
                        }
                        if ($hasInternalFlag) {
                            $aliasQuery->where('is_internal', true);
                        }

                        $aliasIds = $aliasQuery->pluck('id')
                            ->map(fn ($id) => (int) $id)
                            ->filter(fn ($id) => $id > 0)
                            ->values();

                        if ($aliasIds->isNotEmpty()) {
                            $selectedDriverIds = $selectedDriverIds
                                ->merge($aliasIds)
                                ->unique()
                                ->values();
                        }
                    }
                }

                $payload['filters']['selected_driver_ids'] = $selectedDriverIds->all();

                // Toujours montrer les commandes actives de CE livreur (non finalisées),
                // même si delivery_type est incohérent sur des anciennes données.
                $orderQuery->whereIn('driver_id', $selectedDriverIds->all());
            } else {
                // Vue globale prestataire: masquer immédiatement de la carte interne
                // les commandes encaissées (cash/mixte + legacy espèces).
                $orderQuery->where(function ($query) {
                    $query->whereNull('payment_status')
                        ->orWhere('payment_status', '!=', FoodOrder::PAYMENT_PAID)
                        ->orWhere(function ($paidQuery) {
                            $paidQuery->where('payment_status', FoodOrder::PAYMENT_PAID)
                                ->where(function ($methodQuery) {
                                    $methodQuery->whereNotIn('payment_method', ['cash', 'mixed'])
                                        ->orWhere(function ($legacyOnlineQuery) {
                                            $legacyOnlineQuery->whereNull('payment_method')
                                                ->whereNotNull('payment_intent_id');
                                        });
                                });
                        });
                });

                $orderQuery->where('delivery_type', FoodOrder::DELIVERY_DELIVERY)
                    ->where(function ($query) {
                        $query->whereIn('status', [
                            FoodOrder::STATUS_ACCEPTED,
                            FoodOrder::STATUS_PREPARING,
                            FoodOrder::STATUS_READY,
                        ])
                            ->orWhereIn('delivery_status', [
                                FoodOrder::DELIVERY_STATUS_PENDING,
                                FoodOrder::DELIVERY_STATUS_ASSIGNED,
                                FoodOrder::DELIVERY_STATUS_PICKED_UP,
                                FoodOrder::DELIVERY_STATUS_IN_TRANSIT,
                                'self_delivery',
                            ])
                            ->orWhereNotNull('driver_id');
                    });

                if ($internalDriverIds->isNotEmpty()) {
                    $orderQuery->where(function ($q) use ($internalDriverIds) {
                        $q->whereNull('driver_id')
                            ->orWhereIn('driver_id', $internalDriverIds);
                    });
                }
            }

            if ($search !== '') {
                $orderQuery->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', '%' . $search . '%')
                        ->orWhere('delivery_address', 'like', '%' . $search . '%')
                        ->orWhere('delivery_contact_name', 'like', '%' . $search . '%')
                        ->orWhere('delivery_phone', 'like', '%' . $search . '%');
                });
            }

            $orders = $orderQuery->get();

            $batchOrdersByFoodOrder = collect();
            if ($orders->isNotEmpty() && TableExistenceCache::has('delivery_batch_orders')) {
                $batchOrdersByFoodOrder = DeliveryBatchOrder::whereIn('food_order_id', $orders->pluck('id'))
                    ->get(['food_order_id', 'pickup_order', 'delivery_order'])
                    ->keyBy('food_order_id');
            }

            $palette = ['#2563eb', '#10b981', '#f97316', '#8b5cf6', '#ef4444', '#0ea5e9', '#84cc16', '#f59e0b'];
            $driverColors = [0 => '#ef4444'];
            foreach ($drivers->values() as $idx => $driver) {
                $driverColors[$driver->id] = $palette[$idx % count($palette)];
            }

            $ordersPayload = $orders->map(function ($order) use ($batchOrdersByFoodOrder, $driverColors) {
                $meta = $batchOrdersByFoodOrder->get($order->id);
                $driverId = (int) ($order->driver_id ?? 0);
                $deliveryStatus = (string) ($order->delivery_status ?: FoodOrder::DELIVERY_STATUS_PENDING);
                $priority = $this->buildInternalDeliveryPriority($order, $deliveryStatus);
                $itemsCount = (int) $order->items->sum('quantity');
                $itemsPreview = $order->items->take(3)->map(function ($item) {
                    $name = $item->product_name ?? ($item->foodProduct->name ?? 'Article');
                    return ($item->quantity ?? 1) . 'x ' . $name;
                })->implode(', ');

                $pickupLat = (float) ($order->prestataire->latitude ?? 0);
                $pickupLng = (float) ($order->prestataire->longitude ?? 0);
                $dropoffLat = (float) ($order->delivery_lat ?? 0);
                $dropoffLng = (float) ($order->delivery_lng ?? 0);
                $canAssignDriver = in_array($deliveryStatus, [
                    FoodOrder::DELIVERY_STATUS_PENDING,
                    FoodOrder::DELIVERY_STATUS_ASSIGNED,
                    'self_delivery',
                    '',
                ], true);
                $assignLockedReason = null;

                if (!in_array((string) $order->status, [
                    FoodOrder::STATUS_ACCEPTED,
                    FoodOrder::STATUS_PREPARING,
                    FoodOrder::STATUS_READY,
                ], true)) {
                    $canAssignDriver = false;
                    $assignLockedReason = 'Affectation indisponible pour cet état de commande.';
                }

                if (in_array($deliveryStatus, [
                    FoodOrder::DELIVERY_STATUS_PICKED_UP,
                    FoodOrder::DELIVERY_STATUS_IN_TRANSIT,
                ], true)) {
                    $canAssignDriver = false;
                    $assignLockedReason = 'Course déjà en tournée, affectation verrouillée.';
                }

                $canVerifyCode = !$order->code_verified_at
                    && !in_array((string) $order->status, [
                        FoodOrder::STATUS_CANCELLED,
                        FoodOrder::STATUS_COMPLETED,
                    ], true)
                    && (
                        $order->status === FoodOrder::STATUS_READY
                        || in_array($deliveryStatus, [
                            FoodOrder::DELIVERY_STATUS_ASSIGNED,
                            FoodOrder::DELIVERY_STATUS_PICKED_UP,
                            FoodOrder::DELIVERY_STATUS_IN_TRANSIT,
                            FoodOrder::DELIVERY_STATUS_DELIVERED,
                        ], true)
                    );

                $driverShowUrl = Route::has('driver.deliveries.show')
                    ? route('driver.deliveries.show', ['foodOrder' => $order->id])
                    : url('/driver/deliveries/' . $order->id);
                $internalShowUrl = route('prestataire.food-orders.internal-map', [
                    'focus_order' => $order->id,
                ]);

                return [
                    'id' => $order->id,
                    'order_number' => (string) ($order->order_number ?? ('#' . $order->id)),
                    'driver_id' => $driverId,
                    'driver_name' => $driverId > 0
                        ? ($order->driver?->full_name ?? ($order->driver?->user?->name ?? 'Livreur'))
                        : 'Non assigne',
                    'driver_color' => $driverColors[$driverId] ?? '#6b7280',
                    'client_name' => $order->delivery_contact_name ?: ($order->client->name ?? 'Client'),
                    'client_phone' => $order->delivery_phone ?: ($order->client->phone ?? null),
                    'amount' => (float) ($order->total ?? 0),
                    'delivery_fee' => (float) ($order->delivery_fee ?? 0),
                    'delivery_distance_km' => $order->delivery_distance !== null ? (float) $order->delivery_distance : null,
                    'status' => (string) ($order->status ?? ''),
                    'delivery_status' => $deliveryStatus,
                    'can_assign_driver' => $canAssignDriver,
                    'assign_locked_reason' => $assignLockedReason,
                    'can_verify_code' => $canVerifyCode,
                    'code_verified_at' => optional($order->code_verified_at)->toIso8601String(),
                    'items_count' => $itemsCount,
                    'items_preview' => $itemsPreview,
                    'pickup_address' => $order->prestataire->adresse ?? $order->prestataire->address ?? 'Restaurant',
                    'pickup_lat' => $pickupLat,
                    'pickup_lng' => $pickupLng,
                    'dropoff_address' => $order->delivery_address ?? 'Adresse client',
                    'dropoff_lat' => $dropoffLat,
                    'dropoff_lng' => $dropoffLng,
                    'pickup_order' => $meta?->pickup_order,
                    'delivery_order' => $meta?->delivery_order,
                    'sequence' => (int) ($meta?->delivery_order ?? 9999),
                    'priority_score' => (int) ($priority['score'] ?? 0),
                    'priority_rank' => (int) ($priority['rank'] ?? 4),
                    'priority_label' => (string) ($priority['label'] ?? 'Basse'),
                    'priority_wait_minutes' => (int) ($priority['wait_minutes'] ?? 0),
                    'priority_overdue_minutes' => (int) ($priority['overdue_minutes'] ?? 0),
                    'priority_due_at' => $priority['due_at_iso'] ?? null,
                    'priority_due_at_human' => $priority['due_at_human'] ?? null,
                    'priority_is_overdue' => (bool) ($priority['is_overdue'] ?? false),
                    'requested_at' => optional($order->requested_at)->toIso8601String(),
                    'ready_at' => optional($order->ready_at)->toIso8601String(),
                    'updated_at_iso' => optional($order->updated_at)->toIso8601String(),
                    'show_pickup_point' => in_array($deliveryStatus, [
                        FoodOrder::DELIVERY_STATUS_PENDING,
                        FoodOrder::DELIVERY_STATUS_ASSIGNED,
                        'self_delivery',
                    ], true) || in_array((string) $order->status, [
                        FoodOrder::STATUS_ACCEPTED,
                        FoodOrder::STATUS_PREPARING,
                        FoodOrder::STATUS_READY,
                    ], true),
                    'updated_at' => optional($order->updated_at)->format('d/m/Y H:i'),
                    'show_url' => route('prestataire.food-orders.show', $order),
                    'driver_show_url' => $driverShowUrl,
                    'internal_show_url' => $internalShowUrl,
                    'assign_url' => route('prestataire.food-orders.assign-driver', $order),
                    'verify_url' => route('prestataire.food-orders.verify-code', $order),
                    'driver_deliver_url' => route('driver.deliver', $order),
                ];
            })->values();

            $points = collect();
            $paths = [];
            $driversPayload = [];
            $driverLocationById = [];

            foreach ($drivers as $driver) {
                $driverColor = $driverColors[$driver->id] ?? '#2563eb';

                if ($driver->current_lat && $driver->current_lng) {
                    $points->push([
                        'id' => 'driver-' . $driver->id,
                        'driver_id' => $driver->id,
                        'driver_name' => $driver->full_name,
                        'order_id' => null,
                        'order_number' => null,
                        'type' => 'driver',
                        'sequence' => -1,
                        'title' => $driver->full_name,
                        'subtitle' => 'Position actuelle',
                        'address' => $driver->address ?: 'Position GPS',
                        'lat' => (float) $driver->current_lat,
                        'lng' => (float) $driver->current_lng,
                        'color' => $driverColor,
                        'status' => (string) ($driver->status ?? 'offline'),
                    ]);

                    $driverLocationById[$driver->id] = [
                        'lat' => (float) $driver->current_lat,
                        'lng' => (float) $driver->current_lng,
                    ];
                }

                $driversPayload[] = [
                    'id' => $driver->id,
                    'name' => $driver->full_name,
                    'phone' => $driver->phone ?: optional($driver->user)->phone,
                    'status' => (string) ($driver->status ?? 'offline'),
                    'is_available' => (bool) ($driver->is_available ?? false),
                    'color' => $driverColor,
                    'active_orders_count' => 0,
                    'remaining_points_count' => 0,
                    'current_lat' => $driver->current_lat ? (float) $driver->current_lat : null,
                    'current_lng' => $driver->current_lng ? (float) $driver->current_lng : null,
                ];
            }

            foreach ($ordersPayload as $orderData) {
                $driverId = (int) ($orderData['driver_id'] ?? 0);
                $driverColor = $driverColors[$driverId] ?? '#6b7280';
                $driverName = (string) ($orderData['driver_name'] ?? 'Non assigne');

                if ($driverId > 0) {
                    foreach ($driversPayload as &$driverPayload) {
                        if ((int) $driverPayload['id'] === $driverId) {
                            $driverPayload['active_orders_count']++;
                            break;
                        }
                    }
                    unset($driverPayload);
                }

                $deliveryLabel = match ($orderData['delivery_status']) {
                    FoodOrder::DELIVERY_STATUS_PENDING => 'A affecter',
                    FoodOrder::DELIVERY_STATUS_ASSIGNED => 'A recuperer',
                    FoodOrder::DELIVERY_STATUS_PICKED_UP => 'Recuperee',
                    FoodOrder::DELIVERY_STATUS_IN_TRANSIT => 'En livraison',
                    default => 'En cours',
                };

                if (
                    ($orderData['show_pickup_point'] ?? false)
                    && !empty($orderData['pickup_lat'])
                    && !empty($orderData['pickup_lng'])
                ) {
                    $points->push([
                        'id' => 'pickup-' . $orderData['id'],
                        'driver_id' => $driverId,
                        'driver_name' => $driverName,
                        'order_id' => $orderData['id'],
                        'order_number' => $orderData['order_number'],
                        'type' => 'pickup',
                        'sequence' => (int) (($orderData['sequence'] ?? 9999) * 10),
                        'title' => $driverId > 0
                            ? ('Pickup ' . $orderData['order_number'])
                            : ('Pickup a affecter ' . $orderData['order_number']),
                        'subtitle' => $deliveryLabel,
                        'address' => $orderData['pickup_address'],
                        'lat' => (float) $orderData['pickup_lat'],
                        'lng' => (float) $orderData['pickup_lng'],
                        'color' => $driverColor,
                        'status' => $orderData['delivery_status'],
                        'priority_rank' => (int) ($orderData['priority_rank'] ?? 4),
                        'priority_label' => (string) ($orderData['priority_label'] ?? 'Basse'),
                        'priority_score' => (int) ($orderData['priority_score'] ?? 0),
                    ]);
                }

                if (!empty($orderData['dropoff_lat']) && !empty($orderData['dropoff_lng'])) {
                    $points->push([
                        'id' => 'dropoff-' . $orderData['id'],
                        'driver_id' => $driverId,
                        'driver_name' => $driverName,
                        'order_id' => $orderData['id'],
                        'order_number' => $orderData['order_number'],
                        'type' => 'dropoff',
                        'sequence' => (int) (($orderData['sequence'] ?? 9999) * 10 + 1),
                        'title' => $driverId > 0
                            ? ('Livraison ' . $orderData['order_number'])
                            : ('Livraison a affecter ' . $orderData['order_number']),
                        'subtitle' => $deliveryLabel,
                        'address' => $orderData['dropoff_address'],
                        'lat' => (float) $orderData['dropoff_lat'],
                        'lng' => (float) $orderData['dropoff_lng'],
                        'color' => $driverColor,
                        'status' => $orderData['delivery_status'],
                        'priority_rank' => (int) ($orderData['priority_rank'] ?? 4),
                        'priority_label' => (string) ($orderData['priority_label'] ?? 'Basse'),
                        'priority_score' => (int) ($orderData['priority_score'] ?? 0),
                    ]);
                }
            }

            $pointsByDriver = $points
                ->whereIn('type', ['pickup', 'dropoff'])
                ->groupBy('driver_id');

            foreach ($driversPayload as &$driverPayload) {
                $driverId = (int) $driverPayload['id'];
                $routePoints = ($pointsByDriver->get($driverId, collect()) ?? collect())
                    ->sortBy('sequence')
                    ->values();

                $driverPayload['remaining_points_count'] = $routePoints->count();

                if ($routePoints->isEmpty()) {
                    $paths[$driverId] = [];
                    continue;
                }

                $path = collect();
                if (isset($driverLocationById[$driverId])) {
                    $path->push($driverLocationById[$driverId]);
                }

                foreach ($routePoints as $point) {
                    $path->push([
                        'lat' => (float) $point['lat'],
                        'lng' => (float) $point['lng'],
                    ]);
                }

                $paths[$driverId] = $path->values()->all();
            }
            unset($driverPayload);

            $firstPoint = $points->first();
            if ($firstPoint && !empty($firstPoint['lat']) && !empty($firstPoint['lng'])) {
                $payload['center'] = [
                    'lat' => (float) $firstPoint['lat'],
                    'lng' => (float) $firstPoint['lng'],
                ];
            } elseif (!empty($driverLocationById)) {
                $firstDriverLocation = reset($driverLocationById);
                if (!empty($firstDriverLocation['lat']) && !empty($firstDriverLocation['lng'])) {
                    $payload['center'] = [
                        'lat' => (float) $firstDriverLocation['lat'],
                        'lng' => (float) $firstDriverLocation['lng'],
                    ];
                }
            }

            $driversAvailableCount = collect($driversPayload)->where('is_available', true)->count();
            $driversOnMissionCount = collect($driversPayload)->where('active_orders_count', '>', 0)->count();

            $payload['stats'] = [
                'drivers_total' => count($driversPayload),
                'drivers_available' => $driversAvailableCount,
                'drivers_on_mission' => $driversOnMissionCount,
                'active_orders_total' => $ordersPayload->count(),
                'active_points_total' => $points->whereIn('type', ['pickup', 'dropoff'])->count(),
            ];
            $payload['drivers'] = array_values($driversPayload);
            $payload['orders'] = $ordersPayload->values()->all();
            $payload['points'] = $points->values()->all();
            $payload['paths'] = $paths;

            return $payload;
        } catch (\Throwable $e) {
            Log::warning('FoodOrderController internal map payload error: ' . $e->getMessage());
            return $payload;
        }
    }

    /**
     * Notifier les livreurs disponibles pour une commande
     */
    protected function notifyAvailableDrivers(FoodOrder $foodOrder): void
    {
        try {
            // Trouver les livreurs disponibles dans la zone
            $drivers = \App\Models\DeliveryDriver::where('status', 'available')
                ->where('is_active', true)
                ->get();

            foreach ($drivers as $driver) {
                if ($driver->user) {
                    $driver->user->notify(new \App\Notifications\NewDeliveryAvailable($foodOrder));
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur notification livreurs: ' . $e->getMessage());
        }
    }
}