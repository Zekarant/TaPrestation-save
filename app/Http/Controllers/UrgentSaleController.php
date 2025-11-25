<?php

namespace App\Http\Controllers;

use App\Models\UrgentSale;
use App\Models\UrgentSaleContact;
use App\Models\UrgentSaleReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UrgentSaleController extends Controller
{
    /**
     * Afficher la liste des annonces publiques
     */
    public function index(Request $request)
    {
        // Debug logging - remove after fixing
        \Log::info('UrgentSale index called with parameters:', $request->all());
        
        $query = UrgentSale::active()->with(['prestataire.user']);
        
        // Recherche par mot-clé
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        // Filtrage par localisation avec recherche fuzzy
        if ($request->filled('city')) {
            \Log::info('City filter applied with value:', ['city' => $request->city]);
            $cityParam = $request->city;
            // Extraire le nom de la ville si la chaîne contient des virgules (format GPS: "Oujda, 60000")
            $cityParts = explode(',', $cityParam);
            $city = trim($cityParts[0]); // Prendre seulement la première partie (nom de la ville)
            \Log::info('Extracted city:', ['original' => $cityParam, 'extracted' => $city]);
            
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
        
        // Recherche géolocalisée - seulement si on a une ville ET des prestataires de cette ville avec GPS
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $radius = $request->filled('radius') ? $request->radius : 50; // 50km par défaut

            \Log::info('GPS coordinates provided, checking for relevant prestataires with GPS data');
            
            // Si on a une ville spécifiée, vérifier s'il y a des prestataires de cette ville avec GPS
            if ($request->filled('city')) {
                $cityParam = $request->city;
                // Extraction du nom de ville (même logique que pour le filtre de ville)
                $city = $cityParam;
                if (strpos($cityParam, ',') !== false) {
                    $city = trim(explode(',', $cityParam)[0]);
                }
                
                \Log::info('Checking GPS for city:', ['original' => $cityParam, 'extracted' => $city]);
                
                // Vérifier s'il y a des prestataires dans cette ville avec des coordonnées GPS
                $cityPrestataireWithGps = \App\Models\Prestataire::where(function($q) use ($city, $cityParam) {
                    $q->where('city', 'like', '%' . $city . '%')
                      ->orWhere('address', 'like', '%' . $city . '%')
                      ->orWhere('postal_code', 'like', '%' . $city . '%')
                      ->orWhere('city', 'like', '%' . $cityParam . '%')
                      ->orWhere('address', 'like', '%' . $cityParam . '%');
                })
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->exists();
                
                \Log::info('City prestataires with GPS check result:', ['city' => $city, 'has_gps' => $cityPrestataireWithGps]);
                
                // Appliquer le filtre GPS seulement s'il y a des prestataires de cette ville avec GPS
                if ($cityPrestataireWithGps) {
                    \Log::info('Applying GPS distance filter for city prestataires');
                    $query->whereHas('prestataire', function($q) use ($latitude, $longitude, $radius) {
                        $q->selectRaw(
                            'prestataires.*, 
                            ( 6371 * acos( cos( radians(?) ) * 
                              cos( radians( latitude ) ) * 
                              cos( radians( longitude ) - radians(?) ) + 
                              sin( radians(?) ) * 
                              sin( radians( latitude ) ) ) ) AS distance',
                            [$latitude, $longitude, $latitude]
                        )
                        ->whereNotNull('latitude')
                        ->whereNotNull('longitude')
                        ->havingRaw('distance <= ?', [$radius]);
                    });
                } else {
                    \Log::info('Skipping GPS filter - no prestataires in this city have GPS coordinates');
                }
            } else {
                // Pas de ville spécifiée, appliquer le filtre GPS global
                \Log::info('No city specified, applying global GPS filter');
                $query->whereHas('prestataire', function($q) use ($latitude, $longitude, $radius) {
                    $q->selectRaw(
                        'prestataires.*, 
                        ( 6371 * acos( cos( radians(?) ) * 
                          cos( radians( latitude ) ) * 
                          cos( radians( longitude ) - radians(?) ) + 
                          sin( radians(?) ) * 
                          sin( radians( latitude ) ) ) ) AS distance',
                        [$latitude, $longitude, $latitude]
                    )
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->havingRaw('distance <= ?', [$radius]);
                });
            }
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
        
        // Debug logging - remove after fixing
        \Log::info('Final SQL Query:', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);
        
        $urgentSales = $query->paginate(12)->withQueryString();
        
        // Debug logging - remove after fixing
        \Log::info('UrgentSales result count:', ['total' => $urgentSales->total(), 'current_page_count' => $urgentSales->count()]);
        
        // Données pour les filtres
        $priceRange = UrgentSale::active()->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
        $conditions = UrgentSale::CONDITION_OPTIONS;
        
        return view('urgent-sales.index', compact('urgentSales', 'priceRange', 'conditions'));
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
        
        $urgentSale->load(['prestataire.user']);
        
        // Autres ventes du même prestataire
        $otherSales = $urgentSale->prestataire->urgentSales()
                                 ->active()
                                 ->where('id', '!=', $urgentSale->id)
                                 ->limit(3)
                                 ->get();
        
        // Ventes similaires (même gamme de prix)
        $priceMin = $urgentSale->price * 0.7;
        $priceMax = $urgentSale->price * 1.3;
        
        $similarSales = UrgentSale::active()
                                 ->where('id', '!=', $urgentSale->id)
                                 ->whereBetween('price', [$priceMin, $priceMax])
                                 ->with(['prestataire.user'])
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
        
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Créer le contact
        UrgentSaleContact::create([
            'urgent_sale_id' => $urgentSale->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'phone' => $request->phone,
            'email' => $request->email ?? Auth::user()->email,
            'status' => 'pending'
        ]);
        
        // Créer un message dans la messagerie
        \App\Models\Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $urgentSale->prestataire->user_id,
            'content' => "Concernant votre vente urgente '{$urgentSale->title}': " . $request->message,
            'status' => 'approved'
        ]);
        
        // Incrémenter le compteur de contacts
        $urgentSale->incrementContacts();
        
        return back()->with('success', 'Votre message est envoyé');
    }
    
    /**
     * Signaler une vente urgente
     */
    public function report(Request $request, UrgentSale $urgentSale)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|in:inappropriate,spam,fake,other',
            'details' => 'nullable|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
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
            'reason' => $request->reason,
            'description' => $request->details,
            'status' => 'pending'
        ]);
        
        return back()->with('success', 'Votre signalement a été envoyé. Merci de nous aider à maintenir la qualité de la plateforme.');
    }
}