<?php

namespace App\Http\Controllers;

use App\Models\FoodProduct;
use App\Models\Prestataire;
use App\Services\DeliveryMatchingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Support\TableExistenceCache;
class FoodExploreController extends Controller
{
    protected static ?bool $hasFoodIsOpenColumn = null;

    /**
     * Page d'exploration des prestataires food
     */
    public function index(Request $request)
    {
        $availableDate = null;
        if ($request->filled('available_date')) {
            try {
                $availableDate = Carbon::parse($request->input('available_date'))->startOfDay();
                if ($availableDate->lt(now()->startOfDay())) {
                    $availableDate = now()->startOfDay();
                }
            } catch (\Throwable $e) {
                $availableDate = null;
            }
        }

        $productAvailabilityScope = function ($query) use ($availableDate) {
            $query->availableForRequestedDate($availableDate);
        };

        // Récupérer les coordonnées et rayon pour la requête géo
        $latitude = $request->filled('latitude') ? (float) $request->latitude : null;
        $longitude = $request->filled('longitude') ? (float) $request->longitude : null;
        $radius = $request->filled('radius') ? (int) $request->radius : 25;
        
        // Géocodage automatique si on a une ville mais pas de coordonnées GPS
        if ($request->filled('city') && (!$latitude || !$longitude)) {
            $cityParam = $request->city;
            $cityParts = explode(',', $cityParam);
            $cityName = trim($cityParts[0]);
            $cityName = preg_replace('/\s*\(\d+\)\s*$/', '', $cityName);
            $cityName = trim($cityName);
            
            // Essayer de géocoder via Nominatim (OpenStreetMap) - gratuit
            try {
                $geocodeUrl = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
                    'q' => $cityName . ', France',
                    'format' => 'json',
                    'limit' => 1,
                ]);
                
                $context = stream_context_create([
                    'http' => [
                        'header' => 'User-Agent: TaPrestation/1.0',
                        'timeout' => 3,
                    ]
                ]);
                
                $response = @file_get_contents($geocodeUrl, false, $context);
                if ($response) {
                    $data = json_decode($response, true);
                    if (!empty($data) && isset($data[0]['lat'], $data[0]['lon'])) {
                        $latitude = (float) $data[0]['lat'];
                        $longitude = (float) $data[0]['lon'];
                    }
                }
            } catch (\Exception $e) {
                // Géocodage échoué, continuer sans coordonnées
            }
        }
        
        $hasGeoFilter = $latitude && $longitude && $radius;

        // Construire la requête de base
        $query = Prestataire::query()
            ->select('prestataires.*')
            ->whereHas('foodProducts', $productAvailabilityScope)
            ->withCount(['foodProducts' => $productAvailabilityScope])
            ->with(['user', 'services', 'foodProducts' => $productAvailabilityScope])
            ->where('is_active', true);

        // Ajouter le filtre de distance si coordonnées fournies
        if ($hasGeoFilter) {
            // Vérifier que le prestataire a des coordonnées valides
            $query->whereNotNull('latitude')
                  ->whereNotNull('longitude')
                  ->addSelect(DB::raw("(6371 * acos(
                      LEAST(1.0, GREATEST(-1.0,
                          cos(radians({$latitude})) * cos(radians(latitude)) * cos(radians(longitude) - radians({$longitude})) + 
                          sin(radians({$latitude})) * sin(radians(latitude))
                      ))
                  )) AS distance"))
                  ->whereRaw("
                      (6371 * acos(
                          LEAST(1.0, GREATEST(-1.0,
                              cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + 
                              sin(radians(?)) * sin(radians(latitude))
                          ))
                      )) <= ?
                  ", [$latitude, $longitude, $latitude, $radius]);
        }

        // Filtre par catégorie
        if ($request->filled('category')) {
            $category = $request->category;
            $query->whereHas('foodProducts', function ($q) use ($category, $availableDate) {
                $q->availableForRequestedDate($availableDate)
                    ->where('category', $category);
            });
        }

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('foodProducts', function ($subQ) use ($search, $availableDate) {
                      $subQ->availableForRequestedDate($availableDate)
                          ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par prix max
        if ($request->filled('price_max')) {
            $priceMax = (float) $request->price_max;
            $query->whereHas('foodProducts', function ($q) use ($priceMax, $availableDate) {
                $q->availableForRequestedDate($availableDate)
                    ->where('price', '<=', $priceMax);
            });
        }

        // Filtre par ville (si pas de coordonnées GPS)
        if (!$hasGeoFilter && $request->filled('city')) {
            $cityParam = $request->city;
            // Extraire le nom de la ville et retirer le code postal entre parenthèses
            $cityParts = explode(',', $cityParam);
            $city = trim($cityParts[0]);
            $city = preg_replace('/\s*\(\d+\)\s*$/', '', $city);
            $city = trim($city);
            
            $query->where(function ($q) use ($city) {
                $q->where('city', 'like', "%{$city}%")
                  ->orWhere('address', 'like', "%{$city}%")
                  ->orWhere('postal_code', 'like', "%{$city}%");
            });
        }

        // Options supplémentaires
        if ($request->filled('with_delivery')) {
            $query->where('delivery_available', true);
            
            // Filtrer les prestataires "external only" si pas assez de livreurs disponibles
            if ($latitude && $longitude) {
                $minDrivers = config('delivery.matching.min_drivers_for_external', 5);
                $matchingService = app(DeliveryMatchingService::class);
                $availableDrivers = $matchingService->countAvailableDrivers($latitude, $longitude);
                
                if ($availableDrivers < $minDrivers) {
                    // Exclure les prestataires qui n'ont QUE des livreurs externes
                    $query->where(function ($q) {
                        $q->where('delivery_mode', '!=', 'external')
                          ->orWhereNull('delivery_mode');
                    });
                }
            }
        }

        if ($request->filled('available_now') && self::hasPrestataireFoodOpenColumn()) {
            // Compat legacy: null = ouvert (avant ajout du toggle).
            $query->where(function ($q) {
                $q->whereNull('food_is_open')
                    ->orWhere('food_is_open', true);
            });
        }

        // Tri
        $sort = $request->filled('sort')
            ? $request->get('sort')
            : ($hasGeoFilter ? 'distance' : 'popular');
        switch ($sort) {
            case 'name':
                $query->orderBy('company_name');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'distance':
                if ($hasGeoFilter) {
                    $query->orderBy('distance', 'asc');
                } else {
                    $query->orderBy('food_products_count', 'desc');
                }
                break;
            case 'price_asc':
                // Tri par prix minimum des produits
                $query->addSelect([
                    'min_price' => FoodProduct::selectRaw('MIN(price)')
                        ->whereColumn('prestataire_id', 'prestataires.id')
                        ->where('is_available', true)
                        ->limit(1)
                ])->orderBy('min_price', 'asc');
                break;
            case 'price_desc':
                // Tri par prix maximum des produits
                $query->addSelect([
                    'max_price' => FoodProduct::selectRaw('MAX(price)')
                        ->whereColumn('prestataire_id', 'prestataires.id')
                        ->where('is_available', true)
                        ->limit(1)
                ])->orderBy('max_price', 'desc');
                break;
            case 'popular':
            default:
                $query->orderBy('food_products_count', 'desc');
                break;
        }

        $prestataires = $query->paginate(12);

        // Catégories disponibles
        $categories = FoodProduct::categories();

        // Stats globales
        $stats = [
            'total_prestataires' => Prestataire::whereHas('foodProducts', function ($q) {
                $q->where('is_available', true);
            })->where('is_active', true)->count(),
            'total_products' => FoodProduct::where('is_available', true)->count(),
            'categories_count' => FoodProduct::where('is_available', true)
                ->distinct('category')
                ->count('category'),
        ];

        return view('food.explore-modern', compact('prestataires', 'categories', 'stats'));
    }

    /**
     * Afficher le menu d'un prestataire
     */
    public function menu(Request $request, Prestataire $prestataire)
    {
        $availableDate = null;
        if ($request->filled('available_date')) {
            try {
                $availableDate = Carbon::parse($request->input('available_date'))->startOfDay();
            } catch (\Throwable $e) {
                $availableDate = null;
            }
        }

        $products = $prestataire->foodProducts()
            ->availableForRequestedDate($availableDate)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        if ($products->isEmpty()) {
            abort(404, $availableDate
                ? 'Ce prestataire n\'a pas de produits disponibles pour cette date.'
                : 'Ce prestataire n\'a pas de menu disponible.');
        }

        $categories = FoodProduct::categories();

        // Horaires si disponibles
        $openingHours = null; // À implémenter si besoin

        return view('food.menu', compact('prestataire', 'products', 'categories', 'openingHours', 'availableDate'));
    }

    protected static function hasPrestataireFoodOpenColumn(): bool
    {
        if (self::$hasFoodIsOpenColumn !== null) {
            return self::$hasFoodIsOpenColumn;
        }

        try {
            self::$hasFoodIsOpenColumn = TableExistenceCache::has('prestataires')
                && Schema::hasColumn('prestataires', 'food_is_open');
        } catch (\Throwable $e) {
            self::$hasFoodIsOpenColumn = false;
        }

        return self::$hasFoodIsOpenColumn;
    }
}
