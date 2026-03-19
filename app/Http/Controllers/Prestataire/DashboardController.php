<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Skill;
use App\Models\Booking;
use App\Models\UrgentSale;
use App\Models\InventoryItem;
use App\Models\DeliveryOrder;
use App\Models\PaymentTransaction;
use App\Models\PrestataireAvailability;
use App\Models\TenderRequest;
use App\Models\TenderResponse;
use App\Models\TenderInvitation;
use App\Models\FoodOrder;
use App\Models\UrgentSaleReservation;
use App\Models\EquipmentRental;
use App\Models\EquipmentRentalRequest;
use Illuminate\Support\Facades\Schema;
use App\Support\TableExistenceCache;
use Illuminate\Support\Facades\DB;
use App\Models\Message;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord du prestataire.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        // Récupérer les statistiques pour le tableau de bord
        // IMPORTANT: certaines anciennes données peuvent ne pas avoir prestataire_id rempli -> fallback via service->prestataire_id
        $pendingRequests = Booking::query()
            ->where(function ($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id)
                    ->orWhereHas('service', fn($s) => $s->where('prestataire_id', $prestataire->id));
            })
            ->where('status', 'pending')
            ->count();

        $unreadMessages = \App\Models\Message::where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->count();
        $activeServices = $prestataire->services()->where('status', 'active')->count();
        $totalServices = $prestataire->services()->count();

        // Compter les bookings avec fallback via service
        $bookingsCount = Booking::query()
            ->where(function ($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id)
                    ->orWhereHas('service', fn($s) => $s->where('prestataire_id', $prestataire->id));
            })
            ->count();

        // Statistiques pour les équipements
        $equipmentCount = $prestataire->equipments()->count();
        // Demandes = toutes les demandes encore "en cours" (pas rejetées/annulées/expirées)
        // IMPORTANT: certaines anciennes données peuvent ne pas remplir prestataire_id -> fallback via equipment->prestataire_id
        $equipmentRentalRequestsCount = EquipmentRentalRequest::query()
            ->where(function ($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id)
                    ->orWhereHas('equipment', fn($eq) => $eq->where('prestataire_id', $prestataire->id));
            })
            ->whereIn('status', [
                EquipmentRentalRequest::STATUS_PENDING,
                EquipmentRentalRequest::STATUS_ACCEPTED,
                'confirmed',
                'in_preparation',
            ])
            ->count();

        // Locations = toutes les locations en cours (statuts réels en DB)
        // Même fallback pour les données legacy
        $activeRentalsCount = EquipmentRental::query()
            ->where(function ($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id)
                    ->orWhereHas('equipment', fn($eq) => $eq->where('prestataire_id', $prestataire->id));
            })
            ->whereIn('status', [
                EquipmentRental::STATUS_CONFIRMED,
                EquipmentRental::STATUS_IN_PREPARATION,
                EquipmentRental::STATUS_READY_FOR_DELIVERY,
                EquipmentRental::STATUS_DELIVERED,
                EquipmentRental::STATUS_IN_USE,
                'active',
                'pending_pickup',
                EquipmentRental::STATUS_READY_FOR_PICKUP,
                EquipmentRental::STATUS_DISPUTED,
            ])
            ->count();
        $monthlyEquipmentRevenue = $prestataire->equipmentRentals()
            ->where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->sum('total_amount');

        // Statistiques pour les services (revenus mensuels)
        $monthlyServiceRevenue = $prestataire->bookings()
            ->where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->sum('total_price');

        // Revenus totaux du mois (services + équipements)
        $monthlyTotalRevenue = $monthlyServiceRevenue + $monthlyEquipmentRevenue;

        // Statistiques pour les annonces
        $urgentSalesCount = UrgentSale::where('prestataire_id', $prestataire->id)
            ->where('status', 'active')
            ->count();
        $urgentProductsCount = UrgentSale::where('prestataire_id', $prestataire->id)
            ->count();

        // Statistiques supplémentaires (Inventaire, Livraisons, Paiements)
        // Vérifier si la table inventory existe avant de faire la requête
        $inventoryCount = 0;
        $lowStockCount = 0;
        if (TableExistenceCache::has('inventory')) {
            try {
                // Charger les items avec leurs relations urgentSales pour calculer les réservations
                $inventoryItems = InventoryItem::where('user_id', $user->id)
                    ->with(['urgentSales', 'urgentSale'])
                    ->get();
                $inventoryCount = $inventoryItems->count();

                // Calculer le stock bas en prenant en compte les réservations
                foreach ($inventoryItems as $item) {
                    // Stock disponible = quantity - réservations en cours (via urgentSales)
                    $reservedQty = 0;
                    $soldQty = 0;

                    // Via inventory_item_id sur urgent_sales (hasMany)
                    if ($item->urgentSales && $item->urgentSales->count() > 0) {
                        $reservedQty = $item->urgentSales->sum('reserved_quantity');
                        $soldQty = $item->urgentSales->sum('sold_quantity');
                    }

                    // Via urgent_sale_id sur inventory (belongsTo)
                    if ($item->urgentSale) {
                        $reservedQty += $item->urgentSale->reserved_quantity ?? 0;
                        $soldQty += $item->urgentSale->sold_quantity ?? 0;
                    }

                    $availableStock = $item->quantity - $reservedQty - $soldQty;
                    $reorderLevel = $item->reorder_level ?? 5;

                    if ($availableStock <= $reorderLevel && $availableStock > 0) {
                        $lowStockCount++;
                    }
                }
            } catch (\Exception $e) {
                $inventoryCount = 0;
                $lowStockCount = 0;
            }
        }

        // Livraisons (compteur utilisé par le bouton "Livraison")
        // On agrège les éléments réellement liés à une livraison/expédition.
        $deliveriesCount = 0;

        // 1) Food deliveries
        if (TableExistenceCache::has('food_orders')) {
            try {
                $deliveriesCount += FoodOrder::where('prestataire_id', $prestataire->id)
                    ->where('delivery_type', 'delivery')
                    ->whereIn('delivery_status', ['pending', 'accepted', 'preparing', 'ready', 'assigned', 'picked_up', 'in_transit'])
                    ->count();
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // 2) Urgent sales reservations to deliver
        if (TableExistenceCache::has('urgent_sale_reservations')) {
            try {
                $deliveriesCount += UrgentSaleReservation::whereHas('urgentSale', fn($q) => $q->where('prestataire_id', $prestataire->id))
                    ->where('status', UrgentSaleReservation::STATUS_CONFIRMED)
                    ->count();
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // 3) Equipment rentals requiring delivery/pickup handling
        if (TableExistenceCache::has('equipment_rentals')) {
            try {
                $deliveriesCount += EquipmentRental::where('prestataire_id', $prestataire->id)
                    ->whereIn('status', [
                        EquipmentRental::STATUS_CONFIRMED,
                        EquipmentRental::STATUS_IN_PREPARATION,
                        EquipmentRental::STATUS_READY_FOR_DELIVERY,
                        EquipmentRental::STATUS_DELIVERED,
                        EquipmentRental::STATUS_IN_USE,
                        'active',
                        'pending_pickup',
                        EquipmentRental::STATUS_READY_FOR_PICKUP,
                    ])
                    ->count();
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // 4) Advanced logistics delivery orders (if used)
        if (TableExistenceCache::has('delivery_orders')) {
            try {
                $deliveriesCount += DeliveryOrder::whereHas('booking.service', function ($q) use ($prestataire) {
                    $q->where('prestataire_id', $prestataire->id);
                })
                    ->whereIn('status', [
                        DeliveryOrder::STATUS_PENDING,
                        DeliveryOrder::STATUS_CONFIRMED,
                        DeliveryOrder::STATUS_PREPARING,
                        DeliveryOrder::STATUS_DRIVER_ASSIGNED,
                        DeliveryOrder::STATUS_PICKED_UP,
                        DeliveryOrder::STATUS_IN_TRANSIT,
                        DeliveryOrder::STATUS_OUT_FOR_DELIVERY,
                    ])
                    ->count();
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $paymentsCount = 0;
        try {
            $paymentsQuery = PaymentTransaction::query();

            $paymentsQuery->where(function ($q) use ($prestataire) {
                // Paiements liés aux bookings services
                $q->whereHas('booking', function ($bookingQuery) use ($prestataire) {
                    $bookingQuery->where('prestataire_id', $prestataire->id);
                });

                // Paiements liés aux locations matériel (demande)
                if (TableExistenceCache::has('equipment_rental_requests')) {
                    $q->orWhereHas('equipmentRentalRequest', function ($rentalQuery) use ($prestataire) {
                        $rentalQuery->where(function ($rq) use ($prestataire) {
                            $rq->where('prestataire_id', $prestataire->id)
                                ->orWhereHas('equipment', function ($eq) use ($prestataire) {
                                    $eq->where('prestataire_id', $prestataire->id);
                                });
                        });
                    });
                }

                // Paiements liés aux commandes food
                if (Schema::hasColumn('payment_transactions', 'food_order_id') && TableExistenceCache::has('food_orders')) {
                    $q->orWhereHas('foodOrder', function ($foodQuery) use ($prestataire) {
                        $foodQuery->where('prestataire_id', $prestataire->id);
                    });
                }
            });

            // Paiements des ventes urgentes via urgent_sale_purchases.payment_transaction_id
            if (TableExistenceCache::has('urgent_sale_purchases') && TableExistenceCache::has('urgent_sales')) {
                $urgentSalePaymentIds = DB::table('urgent_sale_purchases as usp')
                    ->join('urgent_sales as us', 'us.id', '=', 'usp.urgent_sale_id')
                    ->where('us.prestataire_id', $prestataire->id)
                    ->whereNotNull('usp.payment_transaction_id')
                    ->pluck('usp.payment_transaction_id')
                    ->filter()
                    ->values();

                if ($urgentSalePaymentIds->isNotEmpty()) {
                    $paymentsQuery->orWhereIn('id', $urgentSalePaymentIds->all());
                }
            }

            $paymentsCount = (clone $paymentsQuery)->count();
        } catch (\Throwable $e) {
            $paymentsCount = 0;
        }

        $invoicesCount = 0;
        try {
            if (TableExistenceCache::has('invoices')) {
                $invoiceQuery = DB::table('invoices');
                if (Schema::hasColumn('invoices', 'prestataire_id')) {
                    $invoiceQuery->where('prestataire_id', $prestataire->id);
                } elseif (Schema::hasColumn('invoices', 'user_id')) {
                    $invoiceQuery->where('user_id', $user->id);
                }
                $invoicesCount = (int) $invoiceQuery->count();
            }
        } catch (\Throwable $e) {
            $invoicesCount = 0;
        }

        $escrowCount = 0;
        try {
            if (TableExistenceCache::has('escrow_transactions')) {
                $escrowQuery = DB::table('escrow_transactions');
                if (Schema::hasColumn('escrow_transactions', 'prestataire_id')) {
                    $escrowQuery->where('prestataire_id', $prestataire->id);
                }
                if (Schema::hasColumn('escrow_transactions', 'status')) {
                    $escrowQuery->whereIn('status', ['pending', 'held', 'partial', 'disputed']);
                }
                $escrowCount = (int) $escrowQuery->count();
            }
        } catch (\Throwable $e) {
            $escrowCount = 0;
        }

        $paymentAccountsCount = !empty($prestataire->stripe_account_id) ? 1 : 0;
        $financeCount = $paymentsCount + $invoicesCount + $escrowCount + $paymentAccountsCount;

        // Totaux demandes multi-modules
        $foodOrdersCount = FoodOrder::where('prestataire_id', $prestataire->id)->count();
        $equipmentRequestsTotalCount = EquipmentRentalRequest::query()
            ->where(function ($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id)
                    ->orWhereHas('equipment', fn($eq) => $eq->where('prestataire_id', $prestataire->id));
            })
            ->count();
        $urgentSaleReservationsTotalCount = UrgentSaleReservation::whereHas('urgentSale', fn($q) => $q->where('prestataire_id', $prestataire->id))
            ->count();

        // Total demandes affiché sur la tuile principale
        $requestsCount = $bookingsCount
            + $equipmentRequestsTotalCount
            + $foodOrdersCount
            + $urgentSaleReservationsTotalCount;

        // Détail des demandes "en attente"
        $pendingServiceBookingsCount = $pendingRequests;
        $pendingEquipmentRequestsCount = EquipmentRentalRequest::query()
            ->where(function ($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id)
                    ->orWhereHas('equipment', fn($eq) => $eq->where('prestataire_id', $prestataire->id));
            })
            ->where('status', EquipmentRentalRequest::STATUS_PENDING)
            ->count();
        $pendingFoodOrdersCount = FoodOrder::where('prestataire_id', $prestataire->id)
            ->whereIn('status', ['pending', 'received'])
            ->count();
        $pendingUrgentSaleReservationsCount = UrgentSaleReservation::whereHas('urgentSale', fn($q) => $q->where('prestataire_id', $prestataire->id))
            ->where('status', UrgentSaleReservation::STATUS_PENDING)
            ->count();

        // Disponibilités hebdomadaires
        $weeklyAvailability = PrestataireAvailability::where('prestataire_id', $prestataire->id)->get();

        // Calcul du pourcentage de completion du profil
        $profileCompletion = $this->calculateProfileCompletion($prestataire);

        // Services récents
        $recentServices = $prestataire->services()
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        // Activité récente (exemple basique)
        $recentActivity = collect([
            [
                'type' => 'service',
                'title' => 'Service publié',
                'description' => 'Service publié',
                'date' => $recentServices->first()?->created_at ?? now(),
                'icon' => 'briefcase'
            ],
            [
                'type' => 'message',
                'title' => 'Dernier message reçu',
                'description' => 'Dernier message reçu',
                'date' => now()->subDays(2),
                'icon' => 'mail'
            ]
        ])->sortByDesc('date')->take(3);

        // Prestations du jour
        $todayBookings = Booking::where('prestataire_id', $prestataire->id)
            ->whereDate('start_datetime', today())
            ->with(['client.user', 'service'])
            ->orderBy('start_datetime')
            ->get();

        // Prochaines prestations (exemple)
        $upcomingServices = collect([
            (object) [
                'service' => (object) ['title' => 'Consultation marketing'],
                'client' => (object) ['user' => (object) ['name' => 'Client ABC']],
                'scheduled_date' => now()->addDays(3),
                'status' => 'confirmé'
            ]
        ]);

        // Notifications count
        $notificationsCount = $user->unreadNotifications()->count();

        // Statistiques des appels d'offres (avec gestion des tables manquantes)
        $prestataireCategories = $prestataire->categories ? $prestataire->categories->pluck('id')->toArray() : [];

        $tenderStats = [
            'available' => 0,
            'responded' => 0,
            'shortlisted' => 0,
            'accepted' => 0,
            'pending' => 0,
        ];
        $unreadInvitations = 0;
        $recentTenders = collect();

        try {
            if (TableExistenceCache::has('tender_requests') && TableExistenceCache::has('tender_responses')) {
                // Compter TOUS les appels d'offres actifs (sans filtrer par catégorie pour correspondre à la page tenders)
                $availableTendersCount = TenderRequest::published()->active()->notExpired()->count();

                $tenderStats = [
                    'available' => $availableTendersCount,
                    'responded' => TenderResponse::where('prestataire_id', $prestataire->id)->count(),
                    'shortlisted' => TenderResponse::where('prestataire_id', $prestataire->id)
                        ->where('status', 'shortlisted')->count(),
                    'accepted' => TenderResponse::where('prestataire_id', $prestataire->id)
                        ->where('status', 'accepted')->count(),
                    'pending' => TenderResponse::where('prestataire_id', $prestataire->id)
                        ->where('status', 'pending')->count(),
                ];

                // Appels d'offres récents (prioriser ceux qui matchent les catégories du prestataire, sinon les plus récents)
                if (count($prestataireCategories) > 0) {
                    $recentTenders = TenderRequest::published()->active()->notExpired()
                        ->whereHas('categories', fn($q) => $q->whereIn('categories.id', $prestataireCategories))
                        ->with(['client.user', 'categories'])
                        ->latest('published_at')
                        ->take(3)
                        ->get();
                }

                // Si pas assez de résultats avec les catégories, compléter avec les plus récents
                if ($recentTenders->count() < 3) {
                    $existingIds = $recentTenders->pluck('id')->toArray();
                    $additionalTenders = TenderRequest::published()->active()->notExpired()
                        ->whereNotIn('id', $existingIds)
                        ->with(['client.user', 'categories'])
                        ->latest('published_at')
                        ->take(3 - $recentTenders->count())
                        ->get();
                    $recentTenders = $recentTenders->concat($additionalTenders);
                }
            }

            if (TableExistenceCache::has('tender_invitations')) {
                $unreadInvitations = TenderInvitation::where('prestataire_id', $prestataire->id)
                    ->whereNull('read_at')
                    ->count();
            }
        } catch (\Exception $e) {
            // Tables n'existent pas encore, on garde les valeurs par défaut
        }

        return view('prestataire.dashboard', [
            'prestataire' => $prestataire,
            'pendingRequests' => $pendingRequests,
            'unreadMessages' => $unreadMessages,
            'activeServices' => $activeServices,
            'totalServices' => $totalServices,
            'bookingsCount' => $bookingsCount,
            'requestsCount' => $requestsCount,
            'profileCompletion' => $profileCompletion,
            'recentServices' => $recentServices,
            'recentActivity' => $recentActivity,
            'upcomingServices' => $upcomingServices,
            'upcomingBookings' => $upcomingServices, // Using the same data for now
            'todayBookings' => $todayBookings,

            'equipmentCount' => $equipmentCount,
            'equipmentRentalRequestsCount' => $equipmentRentalRequestsCount,
            'activeRentalsCount' => $activeRentalsCount,
            'monthlyEquipmentRevenue' => $monthlyEquipmentRevenue,

            'urgentSalesCount' => $urgentSalesCount,
            'urgentProductsCount' => $urgentProductsCount,

            'inventoryCount' => $inventoryCount,
            'lowStockCount' => $lowStockCount,
            'deliveriesCount' => $deliveriesCount,
            'paymentsCount' => $paymentsCount,
            'invoicesCount' => $invoicesCount,
            'escrowCount' => $escrowCount,
            'paymentAccountsCount' => $paymentAccountsCount,
            'financeCount' => $financeCount,
            'notificationsCount' => $notificationsCount,
            'weeklyAvailability' => $weeklyAvailability,

            // Adding service revenue and total revenue
            'monthlyServiceRevenue' => $monthlyServiceRevenue,
            'monthlyTotalRevenue' => $monthlyTotalRevenue,

            // Appels d'offres
            'tenderStats' => $tenderStats,
            'unreadInvitations' => $unreadInvitations,
            'recentTenders' => $recentTenders,

            // Food orders
            'foodOrdersCount' => $foodOrdersCount,

            // Demandes en attente par type
            'pendingServiceBookings' => $pendingServiceBookingsCount,
            'pendingEquipmentRequests' => $pendingEquipmentRequestsCount,
            'pendingFoodOrders' => $pendingFoodOrdersCount,
            'pendingUrgentSaleReservations' => $pendingUrgentSaleReservationsCount,
        ]);
    }

    /**
     * Calcule le pourcentage de completion du profil
     */
    private function calculateProfileCompletion($prestataire)
    {
        $fields = [
            'company_name' => !empty($prestataire->company_name),
            'description' => !empty($prestataire->description),
            'phone' => !empty($prestataire->phone),
            'sector' => !empty($prestataire->sector),
            // 'hourly_rate' => !empty($prestataire->hourly_rate), // Supprimé pour des raisons de confidentialité
            'profile_photo' => !empty($prestataire->user->profile_photo_path),
            'has_services' => $prestataire->services()->count() > 0,
            'portfolio_url' => !empty($prestataire->portfolio_url)
        ];

        $completedFields = array_filter($fields);
        $percentage = (count($completedFields) / count($fields)) * 100;

        return [
            'percentage' => round($percentage),
            'missing_fields' => array_keys(array_filter($fields, function ($value) {
                return !$value; }))
        ];
    }

    /**
     * Affiche le profil du prestataire.
     *
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;
        $skills = Skill::all();
        $selectedSkills = $prestataire->skills->pluck('id')->toArray();

        return view('prestataire.profile', [
            'user' => $user,
            'prestataire' => $prestataire,
            'skills' => $skills,
            'selectedSkills' => $selectedSkills
        ]);
    }

    /**
     * Met à jour le profil du prestataire.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'sector' => 'required|string|max:255',
            'description' => 'required|string',
            // 'hourly_rate' => 'nullable|numeric|min:0', // Supprimé pour des raisons de confidentialité
            'delivery_time' => 'nullable|string|max:255',
            'portfolio_url' => 'nullable|url|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'skills' => 'nullable|array',
            'skills.*' => 'exists:skills,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Mise à jour des informations de l'utilisateur
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        // Traitement de la photo de profil
        if ($request->hasFile('profile_photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($prestataire->profile_photo) {
                Storage::disk('public')->delete($prestataire->profile_photo);
            }

            // Stocker la nouvelle photo
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $prestataire->profile_photo = $path;
        }

        // Mise à jour des informations du prestataire
        $prestataire->phone = $request->phone;
        $prestataire->company_name = $request->company_name;
        $prestataire->sector = $request->sector;
        $prestataire->description = $request->description;
        // $prestataire->hourly_rate = $request->hourly_rate; // Supprimé pour des raisons de confidentialité
        $prestataire->delivery_time = $request->delivery_time;
        $prestataire->portfolio_url = $request->portfolio_url;
        $prestataire->save();

        // Mise à jour des compétences
        if ($request->has('skills')) {
            $prestataire->skills()->sync($request->skills);
        } else {
            $prestataire->skills()->detach();
        }

        return redirect()->route('prestataire.profile')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Affiche les statistiques du prestataire.
     *
     * @return \Illuminate\View\View
     */
    public function statistics()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        // Statistiques à implémenter
        $totalServices = $prestataire->services()->count();

        // Total des demandes (bookings + rental requests + food orders)
        $totalRequests = Booking::where('prestataire_id', $prestataire->id)->count();
        if (TableExistenceCache::has('equipment_rental_requests')) {
            $totalRequests += EquipmentRentalRequest::where('prestataire_id', $prestataire->id)->count();
        }
        $totalRequests += FoodOrder::where('prestataire_id', $prestataire->id)->count();

        // Messages non lus
        $totalMessages = Message::where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->count();

        // Vues du profil (depuis les logs ou compteur)
        $profileViews = (int) ($prestataire->profile_views ?? 0);

        return view('prestataire.statistics', [
            'prestataire' => $prestataire,
            'totalServices' => $totalServices,
            'totalRequests' => $totalRequests,
            'totalMessages' => $totalMessages,
            'profileViews' => $profileViews
        ]);
    }

    /**
     * Affiche le profil public du prestataire.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function publicProfile($id)
    {
        $prestataire = \App\Models\Prestataire::findOrFail($id);

        // Vérifier que le prestataire est approuvé
        if (!$prestataire->is_approved) {
            abort(404);
        }

        // Vérifier si l'utilisateur connecté est un client qui suit ce prestataire
        $isFollowing = false;
        $canViewServices = false;

        if (auth()->check()) {
            $user = auth()->user();

            // Les administrateurs peuvent voir tous les services
            if ($user->role === 'administrateur') {
                $canViewServices = true;
            }
            // Les prestataires peuvent voir leurs propres services
            elseif ($user->isPrestataire() && $user->prestataire->id === $prestataire->id) {
                $canViewServices = true;
            }
            // Les clients ne peuvent voir que les services des prestataires qu'ils suivent
            elseif ($user->isClient()) {
                $isFollowing = $user->client->isFollowing($prestataire->id);
                $canViewServices = $isFollowing;
            }
        }

        $services = [];
        if ($canViewServices) {
            $services = $prestataire->services()->latest()->get();
        }

        return view('prestataire.public-profile', [
            'prestataire' => $prestataire,
            'services' => $services,
            'isFollowing' => $isFollowing,
            'canViewServices' => $canViewServices
        ]);
    }
}
