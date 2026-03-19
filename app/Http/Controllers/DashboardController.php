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
use Illuminate\Support\Facades\DB;
use App\Models\Message;
use App\Models\FoodOrder;
use App\Models\UrgentSaleReservation;

use App\Support\TableExistenceCache;
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
        $equipmentRentalRequestsCount = $prestataire->equipmentRentalRequests()->count();
        $pendingEquipmentRequests = $prestataire->equipmentRentalRequests()->where('status', 'pending')->count();
        $activeRentalsCount = $prestataire->equipmentRentals()->where('status', 'active')->count();
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

        // Pending service bookings (pour synchro dashboard)
        $pendingServiceBookings = $pendingRequests;

        // Food orders
        $foodOrdersCount = 0;
        $pendingFoodOrders = 0;
        try {
            $foodOrdersCount = $prestataire->foodOrders()->count();
            $pendingFoodOrders = $prestataire->foodOrders()->where('status', 'pending')->count();
        } catch (\Exception $e) {
            $foodOrdersCount = 0;
            $pendingFoodOrders = 0;
        }

        // Pending urgent sale reservations
        $pendingUrgentSaleReservations = 0;
        try {
            $pendingUrgentSaleReservations = UrgentSaleReservation::whereHas('urgentSale', function ($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id);
            })->where('status', 'pending')->count();
        } catch (\Exception $e) {
            $pendingUrgentSaleReservations = 0;
        }

        // Statistiques supplémentaires (Inventaire, Livraisons, Paiements)
        // Vérifier si la table inventory existe avant de faire la requête
        $inventoryCount = 0;
        $lowStockCount = 0;
        if (TableExistenceCache::has('inventory')) {
            try {
                $inventoryCount = InventoryItem::where('user_id', $user->id)->count();
                $lowStockCount = InventoryItem::where('user_id', $user->id)
                    ->whereColumn('quantity', '<=', 'min_quantity')
                    ->count();
            } catch (\Exception $e) {
                $inventoryCount = 0;
                $lowStockCount = 0;
            }
        }

        $deliveriesCount = 0;
        try {
            $deliveriesCount = DeliveryOrder::whereHas('booking', function ($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id);
            })->count();
        } catch (\Exception $e) {
            $deliveriesCount = 0;
        }

        $paymentsCount = 0;
        try {
            $paymentsCount = PaymentTransaction::whereHas('booking', function ($q) use ($prestataire) {
                $q->where('prestataire_id', $prestataire->id);
            })->count();
        } catch (\Exception $e) {
            $paymentsCount = 0;
        }

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

        // Statistiques des appels d'offres
        $prestataireCategories = $prestataire->categories ? $prestataire->categories->pluck('id')->toArray() : [];

        $tenderStats = [
            'available' => count($prestataireCategories) > 0
                ? TenderRequest::published()->active()->notExpired()
                    ->whereHas('categories', fn($q) => $q->whereIn('categories.id', $prestataireCategories))
                    ->count()
                : 0,
            'responded' => TenderResponse::where('prestataire_id', $prestataire->id)->count(),
            'shortlisted' => TenderResponse::where('prestataire_id', $prestataire->id)
                ->where('status', 'shortlisted')->count(),
            'accepted' => TenderResponse::where('prestataire_id', $prestataire->id)
                ->where('status', 'accepted')->count(),
            'pending' => TenderResponse::where('prestataire_id', $prestataire->id)
                ->where('status', 'pending')->count(),
        ];

        $unreadInvitations = TenderInvitation::where('prestataire_id', $prestataire->id)
            ->whereNull('read_at')
            ->count();

        // Appels d'offres récents correspondants
        $recentTenders = count($prestataireCategories) > 0
            ? TenderRequest::published()->active()->notExpired()
                ->whereHas('categories', fn($q) => $q->whereIn('categories.id', $prestataireCategories))
                ->with(['client.user', 'categories'])
                ->latest('published_at')
                ->take(3)
                ->get()
            : collect();

        return view('prestataire.dashboard', [
            'prestataire' => $prestataire,
            'pendingRequests' => $pendingRequests,
            'unreadMessages' => $unreadMessages,
            'activeServices' => $activeServices,
            'totalServices' => $totalServices,
            'bookingsCount' => $bookingsCount,
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
            'notificationsCount' => $notificationsCount,
            'weeklyAvailability' => $weeklyAvailability,

            // Adding service revenue and total revenue
            'monthlyServiceRevenue' => $monthlyServiceRevenue,
            'monthlyTotalRevenue' => $monthlyTotalRevenue,

            // Compteurs pending pour synchro dashboard
            'pendingServiceBookings' => $pendingServiceBookings,
            'pendingEquipmentRequests' => $pendingEquipmentRequests,
            'foodOrdersCount' => $foodOrdersCount,
            'pendingFoodOrders' => $pendingFoodOrders,
            'pendingUrgentSaleReservations' => $pendingUrgentSaleReservations,

            // Appels d'offres
            'tenderStats' => $tenderStats,
            'unreadInvitations' => $unreadInvitations,
            'recentTenders' => $recentTenders,
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

        // Total des demandes (bookings + rental requests)
        $totalRequests = Booking::where('prestataire_id', $prestataire->id)->count();
        if (TableExistenceCache::has('equipment_rental_requests')) {
            $totalRequests += DB::table('equipment_rental_requests')
                ->where('prestataire_id', $prestataire->id)->count();
        }

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