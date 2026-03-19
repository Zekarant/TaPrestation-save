<?php

namespace App\Http\Controllers;

use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use Carbon\Carbon;
use App\Models\Service;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\UrgentSale;

class PrestataireController extends Controller
{
    /**
     * Affiche la liste des prestataires approuvés.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Prestataire::with(['user', 'services'])
            ->where('is_approved', true);
        
        // Filtrage par nom
        if ($request->has('name')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }
        
        // Filtrage par secteur d'activité
        if ($request->has('secteur')) {
            $query->where('secteur_activite', 'like', '%' . $request->secteur . '%');
        }
        
        // Filtrage par catégorie de service
        if ($request->has('category')) {
            $query->whereHas('services', function($q) use ($request) {
                $q->whereHas('categories', function($q2) use ($request) {
                    $q2->where('categories.id', $request->category);
                });
            });
        }
        
        // Filtrage par sous-catégorie de service
        if ($request->has('subcategory')) {
            $query->whereHas('services', function($q) use ($request) {
                $q->whereHas('categories', function($q2) use ($request) {
                    $q2->where('categories.id', $request->subcategory);
                });
            });
        }
        
        // Filtrage par ville - improved case-insensitive search
        if ($request->has('city') && !empty($request->city)) {
            $city = trim($request->city);
            $query->where(function($q) use ($city) {
                $q->where('city', 'like', '%' . $city . '%')
                  ->orWhere('postal_code', 'like', '%' . $city . '%')
                  ->orWhere('address', 'like', '%' . $city . '%');
            });
        }
        
        $prestataires = $query->paginate(12);
        
        // Récupérer les catégories pour le filtre (services uniquement)
        $categories = Category::ofTypeService()->whereNull('parent_id')->orderBy('name')->get();
        
        // Récupérer les sous-catégories si une catégorie est sélectionnée
        $subcategories = collect();
        if ($request->has('category') && $request->category) {
            $subcategories = Category::ofTypeService()->where('parent_id', $request->category)->orderBy('name')->get();
        }
        
        return view('prestataires.index', compact('prestataires', 'categories', 'subcategories'));
    }

    /**
     * Affiche le profil public d'un prestataire.
     *
     * @param  \App\Models\Prestataire  $prestataire
     * @return \Illuminate\View\View
     */
    public function show(Prestataire $prestataire)
    {
        // Si le prestataire consulte sa propre page, rediriger vers son dashboard
        if (auth()->check() && auth()->user()->prestataire && auth()->user()->prestataire->id === $prestataire->id) {
            return redirect()->route('prestataire.dashboard');
        }

        // Vérifier que le prestataire est approuvée
        if (!$prestataire->is_approved) {
            abort(404);
        }
        
        // Load all necessary relationships for the show view
        $prestataire->load([
            'user', 
            'services' => function($query) {
                $query->latest();
            },
            'videos' => function($query) {
                $query->latest();
            },
            'reviews' => function($query) {
                $query->with(['client'])->latest();
            },
            'equipments' => function($query) {
                $query->where('status', 'active')
                      ->where('is_available', true)
                      ->latest();
            },
            'urgentSales' => function($query) {
                $query->latest();
            }
        ]);
        
        // Get limited services (10) and all services
        $limitedServices = $prestataire->services()
            ->with(['images', 'categories'])
            ->where('status', 'active')
            ->latest()
            ->take(10)
            ->get();
        $allServices = $prestataire->services()
            ->with(['images', 'categories'])
            ->where('status', 'active')
            ->latest()
            ->get();
        
        // Get limited equipments (10) and all equipments
        $limitedEquipments = $prestataire->equipments()
            ->with(['category'])
            ->where('status', 'active')
            ->where('is_available', true)
            ->latest()
            ->take(10)
            ->get();
        $allEquipments = $prestataire->equipments()
            ->with(['category'])
            ->where('status', 'active')
            ->where('is_available', true)
            ->latest()
            ->get();
        
        // Get limited urgent sales (10) and all urgent sales
        $limitedUrgentSales = $prestataire->urgentSales()
            ->with(['category'])
            ->latest()
            ->take(10)
            ->get();
        $allUrgentSales = $prestataire->urgentSales()
            ->with(['category'])
            ->latest()
            ->get();
        
        // Load all reviews without pagination to ensure we get all reviews for display
        $allReviews = $prestataire->reviews()->with(['client'])->latest()->get();
        
        // Récupérer les services similaires d'autres prestataires
        // Obtenir d'abord les IDs des services du prestataire
        $serviceIds = $prestataire->services->pluck('id')->toArray();
        
        // Obtenir les IDs des catégories associées à ces services
        $categoryIds = \DB::table('service_category')
            ->whereIn('service_id', $serviceIds)
            ->pluck('category_id')
            ->toArray();
            
        // Obtenir les services similaires
        $similarServices = Service::with(['prestataire.user', 'categories'])
            ->where('prestataire_id', '!=', $prestataire->id)
            ->whereHas('categories', function($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->where('status', 'active')
            ->inRandomOrder()
            ->limit(3)
            ->get();

        // Get food products if food_enabled
        $foodProducts = collect();
        if ($prestataire->food_enabled) {
            $foodProducts = \App\Models\FoodProduct::where('prestataire_id', $prestataire->id)
                ->where('is_available', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        // Check if user can leave a review
        $existingReview = null;
        $hasInteracted = false;
        if (auth()->check() && auth()->user()->client) {
            $clientId = auth()->user()->client->id;
            // Check if user already left a review for this prestataire
            $existingReview = \App\Models\Review::where('client_id', $clientId)
                ->where('prestataire_id', $prestataire->id)
                ->first();
            // Check if user has interacted with this prestataire (message or booking)
            $hasInteracted = \App\Models\Booking::where('client_id', $clientId)
                ->where('prestataire_id', $prestataire->id)
                ->exists()
                || \App\Models\Message::where('sender_id', auth()->id())
                    ->where('receiver_id', $prestataire->user_id)
                    ->exists();
        }

        return view('prestataires.show', [
            'prestataire' => $prestataire,
            'limitedServices' => $limitedServices,
            'allServices' => $allServices,
            'limitedEquipments' => $limitedEquipments,
            'allEquipments' => $allEquipments,
            'limitedUrgentSales' => $limitedUrgentSales,
            'allUrgentSales' => $allUrgentSales,
            'allReviews' => $allReviews,
            'similarServices' => $similarServices,
            'foodProducts' => $foodProducts,
            'existingReview' => $existingReview,
            'hasInteracted' => $hasInteracted
        ]);
    }

    /**
     * Page publique: Boutique du prestataire (annonces / ventes urgentes)
     */
    public function boutique(Prestataire $prestataire)
    {
        if (!$prestataire->is_approved) {
            abort(404);
        }

        $prestataire->load(['user']);

        $urgentSales = UrgentSale::query()
            ->where('prestataire_id', $prestataire->id)
            ->where('status', UrgentSale::STATUS_ACTIVE)
            ->latest()
            ->paginate(24);

        $counts = [
            'services' => Service::query()
                ->where('prestataire_id', $prestataire->id)
                ->where('status', 'active')
                ->count(),
            'boutique' => $urgentSales->total(),
            'equipements' => Equipment::query()
                ->where('prestataire_id', $prestataire->id)
                ->where('status', 'active')
                ->where('is_available', true)
                ->count(),
        ];

        return view('prestataires.boutique', [
            'prestataire' => $prestataire,
            'urgentSales' => $urgentSales,
            'counts' => $counts,
            'activeTab' => 'boutique',
        ]);
    }

    /**
     * Page publique: Services du prestataire
     */
    public function services(Prestataire $prestataire)
    {
        if (!$prestataire->is_approved) {
            abort(404);
        }

        $prestataire->load(['user']);

        $services = Service::query()
            ->with(['images'])
            ->where('prestataire_id', $prestataire->id)
            ->where('status', 'active')
            ->latest()
            ->paginate(24);

        $counts = [
            'services' => $services->total(),
            'boutique' => UrgentSale::query()
                ->where('prestataire_id', $prestataire->id)
                ->where('status', UrgentSale::STATUS_ACTIVE)
                ->count(),
            'equipements' => Equipment::query()
                ->where('prestataire_id', $prestataire->id)
                ->where('status', 'active')
                ->where('is_available', true)
                ->count(),
        ];

        return view('prestataires.services', [
            'prestataire' => $prestataire,
            'services' => $services,
            'counts' => $counts,
            'activeTab' => 'services',
        ]);
    }

    /**
     * Page publique: Équipements à louer du prestataire
     */
    public function equipements(Prestataire $prestataire)
    {
        if (!$prestataire->is_approved) {
            abort(404);
        }

        $prestataire->load(['user']);

        $equipments = Equipment::query()
            ->where('prestataire_id', $prestataire->id)
            ->where('status', 'active')
            ->where('is_available', true)
            ->latest()
            ->paginate(24);

        $counts = [
            'services' => Service::query()
                ->where('prestataire_id', $prestataire->id)
                ->where('status', 'active')
                ->count(),
            'boutique' => UrgentSale::query()
                ->where('prestataire_id', $prestataire->id)
                ->where('status', UrgentSale::STATUS_ACTIVE)
                ->count(),
            'equipements' => $equipments->total(),
        ];

        return view('prestataires.equipements', [
            'prestataire' => $prestataire,
            'equipments' => $equipments,
            'counts' => $counts,
            'activeTab' => 'equipements',
        ]);
    }


}
