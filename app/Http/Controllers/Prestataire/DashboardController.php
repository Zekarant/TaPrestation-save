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
        $pendingRequests = 0; // À implémenter: nombre de demandes en attente
        $unreadMessages = 0; // À implémenter: nombre de messages non lus
        $activeServices = $prestataire->services()->where('status', 'active')->count();
        $totalServices = $prestataire->services()->count();
        $bookingsCount = Booking::where('prestataire_id', $prestataire->id)->count();
        
        // Statistiques pour les équipements
        $equipmentCount = $prestataire->equipments()->count();
        $equipmentRentalRequestsCount = $prestataire->equipmentRentalRequests()->where('status', 'pending')->count();
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
            
            // Adding service revenue and total revenue
            'monthlyServiceRevenue' => $monthlyServiceRevenue,
            'monthlyTotalRevenue' => $monthlyTotalRevenue
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
            'missing_fields' => array_keys(array_filter($fields, function($value) { return !$value; }))
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
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
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
        $totalRequests = 0; // À implémenter
        $totalMessages = 0; // À implémenter
        $profileViews = 0; // À implémenter
        
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