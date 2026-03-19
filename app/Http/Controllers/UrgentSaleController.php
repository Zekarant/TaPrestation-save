<?php

namespace App\Http\Controllers;

use App\Models\UrgentSale;
use App\Models\UrgentSaleContact;
use App\Models\UrgentSaleReport;
use App\Notifications\NewUrgentSaleContactNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UrgentSaleController extends Controller
{
    /**
     * Afficher la liste des annonces publiques
     */
    public function index(Request $request)
    {
        $debug = app()->environment('local')
            && $request->boolean('debug')
            && (($request->user()?->role ?? null) === 'administrateur');

        if ($debug) {
            Log::info('UrgentSale index called', [
                'search' => $request->input('search'),
                'category' => $request->input('category'),
                'city' => $request->input('city'),
            ]);
        }
        
        $query = UrgentSale::active()->with(['prestataire.user', 'user']);
        
        // Recherche par mot-clé
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        // Filtrage par catégorie et sous-catégorie
        if ($request->filled('subcategory')) {
            // Sous-catégorie spécifique sélectionnée
            $query->where('category_id', $request->subcategory);
        } elseif ($request->filled('category')) {
            // Catégorie principale - inclut la catégorie et toutes ses sous-catégories
            $categoryId = $request->category;
            $subCategoryIds = \App\Models\Category::where('parent_id', $categoryId)->pluck('id')->toArray();
            $allCategoryIds = array_merge([$categoryId], $subCategoryIds);
            $query->whereIn('category_id', $allCategoryIds);
        }
        
        // Géocodage automatique si on a une ville mais pas de coordonnées GPS
        $latitude = $request->filled('latitude') ? (float) $request->latitude : null;
        $longitude = $request->filled('longitude') ? (float) $request->longitude : null;
        $radius = $request->filled('radius') ? (int) $request->radius : 50; // 50km par défaut
        
        // Si on a une ville mais pas de coordonnées, essayer de géocoder
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
                
                $response = Http::acceptJson()
                    ->timeout(3)
                    ->withHeaders(['User-Agent' => 'TaPrestation/1.0'])
                    ->get($geocodeUrl);

                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data) && isset($data[0]['lat'], $data[0]['lon'])) {
                        $latitude = (float) $data[0]['lat'];
                        $longitude = (float) $data[0]['lon'];
                        if ($debug) {
                            Log::info('Geocoded city:', ['city' => $cityName, 'lat' => $latitude, 'lon' => $longitude]);
                        }
                    }
                }
            } catch (\Exception $e) {
                // Géocodage échoué, continuer sans coordonnées
                if ($debug) {
                    Log::info('Geocoding failed:', ['error' => $e->getMessage()]);
                }
            }
        }
        
        // Déterminer si on a des coordonnées GPS (après géocodage éventuel)
        $hasGpsCoordinates = $latitude && $longitude;
        
        // Filtrage par localisation avec recherche fuzzy (uniquement si pas de coordonnées GPS)
        if ($request->filled('city') && !$hasGpsCoordinates) {
            if ($debug) {
                Log::info('City filter applied with value:', ['city' => $request->city]);
            }
            $cityParam = $request->city;
            // Extraire le nom de la ville si la chaîne contient des virgules (format GPS: "Oujda, 60000")
            $cityParts = explode(',', $cityParam);
            $city = trim($cityParts[0]); // Prendre seulement la première partie (nom de la ville)
            // Retirer aussi le code postal entre parenthèses si présent
            $city = preg_replace('/\s*\(\d+\)\s*$/', '', $city);
            $city = trim($city);
            if ($debug) {
                Log::info('Extracted city:', ['original' => $cityParam, 'extracted' => $city]);
            }
            
            $query->where(function($mainQ) use ($city, $cityParam) {
                $mainQ->whereHas('prestataire', function ($q) use ($city, $cityParam) {
                    $q->where(function ($subQ) use ($city, $cityParam) {
                        $subQ->where('city', 'like', '%' . $city . '%')
                             ->orWhere('address', 'like', '%' . $city . '%')
                             ->orWhere('postal_code', 'like', '%' . $city . '%')
                             // Recherche aussi avec la chaîne complète au cas où
                             ->orWhere('city', 'like', '%' . $cityParam . '%')
                             ->orWhere('address', 'like', '%' . $cityParam . '%')
                             // Recherche fuzzy dans l'adresse complète du prestataire
                             ->orWhereRaw("CONCAT(COALESCE(address, ''), ', ', COALESCE(city, ''), ', ', COALESCE(postal_code, '')) LIKE ?", ['%' . $city . '%']);
                    });
                })
                // Aussi chercher dans la localisation de l'urgentSale elle-même
                ->orWhere('location', 'like', '%' . $city . '%')
                ->orWhere('location', 'like', '%' . $cityParam . '%');
            });
        }
        
        // Recherche géolocalisée - filtre par distance de l'annonce OU du prestataire
        // $latitude, $longitude et $radius sont déjà définis plus haut (avec géocodage automatique si nécessaire)
        if ($hasGpsCoordinates) {
            $cityText = $request->input('city', '');

            // Appliquer le filtre de distance sur l'annonce, le prestataire OU fallback par ville
            $query->where(function($mainQ) use ($latitude, $longitude, $radius, $cityText) {
                // 1. Recherche GPS sur l'annonce elle-même (urgent_sales.latitude/longitude)
                $mainQ->where(function($saleQ) use ($latitude, $longitude, $radius) {
                    $saleQ->whereNotNull('latitude')
                          ->whereNotNull('longitude')
                          ->whereRaw("
                              (6371 * acos(
                                  LEAST(1.0, GREATEST(-1.0,
                                      cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + 
                                      sin(radians(?)) * sin(radians(latitude))
                                  ))
                              )) <= ?
                          ", [$latitude, $longitude, $latitude, $radius]);
                });
                
                // 2. OU Recherche GPS sur le prestataire
                $mainQ->orWhereHas('prestataire', function($q) use ($latitude, $longitude, $radius) {
                    $q->whereNotNull('latitude')
                      ->whereNotNull('longitude')
                      ->whereRaw("
                          (6371 * acos(
                              LEAST(1.0, GREATEST(-1.0,
                                  cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + 
                                  sin(radians(?)) * sin(radians(latitude))
                              ))
                          )) <= ?
                      ", [$latitude, $longitude, $latitude, $radius]);
                });
                
                // 3. OU fallback par nom de ville si disponible
                if (!empty($cityText)) {
                    $cityParts = explode(',', $cityText);
                    $city = trim($cityParts[0]);
                    // Retirer aussi le code postal entre parenthèses si présent
                    $city = preg_replace('/\s*\(\d+\)\s*$/', '', $city);
                    $city = trim($city);
                    
                    // Utiliser orWhere avec closure pour grouper correctement
                    $mainQ->orWhere(function($fallbackQ) use ($city) {
                        $fallbackQ->where('location', 'like', '%' . $city . '%');
                    });
                    $mainQ->orWhereHas('prestataire', function($pq) use ($city) {
                        $pq->where('city', 'like', '%' . $city . '%')
                           ->orWhere('address', 'like', '%' . $city . '%');
                    });
                }
            });
        }
        
        // Filtrage par prix
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }
        
        // Filtrage par condition
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }
        

        
        // Tri
        $sortBy = $request->get('sort', 'created_at');
        
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'urgent':
                $query->orderBy('created_at', 'desc');
                break;
            case 'recent':
                $query->orderBy('created_at', 'desc');
                break;
            case 'distance':
                // Le tri par distance est géré dans la requête géolocalisée
                if (!$request->filled('latitude') || !$request->filled('longitude')) {
                    $query->orderBy('created_at', 'desc');
                }
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        
        if ($debug) {
            Log::info('Final SQL Query:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);
        }
        
        $urgentSales = $query->paginate(12)->withQueryString();

        if ($debug) {
            Log::info('UrgentSales result count:', ['total' => $urgentSales->total(), 'current_page_count' => $urgentSales->count()]);
        }
        
        // Si GPS utilisé, calculer la distance du prestataire pour chaque annonce
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $lat = (float) $request->latitude;
            $lon = (float) $request->longitude;
            
            $urgentSales->getCollection()->transform(function ($sale) use ($lat, $lon) {
                if ($sale->prestataire) {
                    $pLat = $sale->prestataire->latitude;
                    $pLon = $sale->prestataire->longitude;
                    if ($pLat && $pLon) {
                        $earthRadius = 6371;
                        $dLat = deg2rad($pLat - $lat);
                        $dLon = deg2rad($pLon - $lon);
                        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat)) * cos(deg2rad($pLat)) * sin($dLon/2) * sin($dLon/2);
                        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
                        $sale->distance_km = round($earthRadius * $c, 1);
                    }
                }
                return $sale;
            });
        }
        
        // Données pour les filtres
        $priceRange = UrgentSale::active()->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
        $conditions = UrgentSale::CONDITION_OPTIONS;
        $categories = \App\Models\Category::ofTypeSale()->with('children')->whereNull('parent_id')->orderBy('name')->get();
        
        return view('urgent-sales.index-modern', compact('urgentSales', 'priceRange', 'conditions', 'categories'));
    }
    
    /**
     * Afficher les détails d'une vente urgente
     */
    public function show(UrgentSale $urgentSale)
    {
        // Vérifier que la vente est active
        if (!$urgentSale->isActive()) {
            abort(404);
        }
        
        // Incrémenter le compteur de vues
        $urgentSale->increment('views_count');
        
        $urgentSale->load(['prestataire.user', 'user']);
        
        // Autres ventes du même vendeur
        $otherSales = collect();
        if ($urgentSale->prestataire) {
            // Vendeur prestataire
            $otherSales = $urgentSale->prestataire->urgentSales()
                                     ->active()
                                     ->where('id', '!=', $urgentSale->id)
                                     ->limit(3)
                                     ->get();
        } elseif ($urgentSale->user_id) {
            // Vendeur client (particulier)
            $otherSales = UrgentSale::where('user_id', $urgentSale->user_id)
                                    ->whereNull('prestataire_id')
                                    ->active()
                                    ->where('id', '!=', $urgentSale->id)
                                    ->limit(3)
                                    ->get();
        }
        
        // Ventes similaires (même gamme de prix)
        $priceMin = $urgentSale->price * 0.7;
        $priceMax = $urgentSale->price * 1.3;
        
        $similarSales = UrgentSale::active()
                                 ->where('id', '!=', $urgentSale->id)
                                 ->whereBetween('price', [$priceMin, $priceMax])
                                 ->with(['prestataire.user', 'user'])
                                 ->limit(4)
                                 ->get();
        
        return view('urgent-sales.show', compact('urgentSale', 'otherSales', 'similarSales'));
    }
    
    /**
     * Contacter le vendeur
     */
    public function contact(Request $request, UrgentSale $urgentSale)
    {
        if (!$urgentSale->canBeContacted()) {
            return back()->with('error', 'Ce produit n\'est plus disponible.');
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'phone' => 'nullable|string|max:20',
        ]);

        // Créer le contact
        $contact = UrgentSaleContact::create([
            'urgent_sale_id' => $urgentSale->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'phone' => $validated['phone'] ?? null,
            'email' => Auth::user()?->email,
            'status' => 'pending'
        ]);
        
        // Déterminer le destinataire (prestataire ou client)
        $receiverId = $urgentSale->prestataire?->user_id ?? $urgentSale->user_id;
        $receiver = $urgentSale->prestataire?->user ?? $urgentSale->user;
        
        // Créer un message dans la messagerie
        if ($receiverId) {
            \App\Models\Message::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $receiverId,
                'content' => "Concernant votre vente urgente '{$urgentSale->title}': " . $validated['message'],
                'status' => 'approved'
            ]);
        }
        
        // Notifier le vendeur
        try {
            $contact->load(['urgentSale', 'user']);
            if ($receiver) {
                $receiver->notify(new NewUrgentSaleContactNotification($contact));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send urgent sale contact notification: ' . $e->getMessage());
        }
        
        // Incrémenter le compteur de contacts
        $urgentSale->incrementContacts();
        
        return back()->with('success', 'Votre message est envoyé');
    }
    
    /**
     * Signaler une vente urgente
     */
    public function report(Request $request, UrgentSale $urgentSale)
    {
        $validated = $request->validate([
            'reason' => 'required|string|in:inappropriate,spam,fake,other',
            'details' => 'nullable|string|max:500'
        ]);

        // Vérifier si l'utilisateur a déjà signalé cette vente
        $existingReport = UrgentSaleReport::where('urgent_sale_id', $urgentSale->id)
                                         ->where('user_id', Auth::id())
                                         ->first();
        
        if ($existingReport) {
            return back()->with('error', 'Vous avez déjà signalé cette vente.');
        }
        
        UrgentSaleReport::create([
            'urgent_sale_id' => $urgentSale->id,
            'user_id' => Auth::id(),
            'reason' => $validated['reason'],
            'description' => $validated['details'] ?? null,
            'status' => 'pending'
        ]);
        
        return back()->with('success', 'Votre signalement a été envoyé. Merci de nous aider à maintenir la qualité de la plateforme.');
    }
}
