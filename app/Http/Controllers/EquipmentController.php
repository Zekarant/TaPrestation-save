<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Support\Facades\Log;

use App\Models\EquipmentRentalRequest;
use App\Models\EquipmentReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EquipmentController extends Controller
{
    /**
     * Affiche la page principale de location de matériel
     */
    public function index(Request $request)
    {


        $query = Equipment::with(['prestataire.user', 'category', 'subcategory']);
        
        // Recherche par mot-clé
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('technical_specifications', 'like', '%' . $search . '%');
            });
        }
        
        // Filtrage par catégorie
        if ($request->filled('category')) {
            $query->where(function ($q) use ($request) {
                $q->where('category_id', $request->category)
                  ->orWhere('subcategory_id', $request->category);
            });
        }
        
        // Filtrage par localisation avec recherche fuzzy
        if ($request->filled('city')) {
            $cityParam = $request->city;
            // Extraire le nom de la ville si la chaîne contient des virgules (format GPS: "Oujda, 60000")
            $cityParts = explode(',', $cityParam);
            $city = trim($cityParts[0]); // Prendre seulement la première partie (nom de la ville)
            
            $query->where(function($mainQ) use ($city, $cityParam) {
                // Recherche dans les propres champs de localisation de l'équipement
                $mainQ->where(function($equipQ) use ($city, $cityParam) {
                    $equipQ->where('city', 'like', '%' . $city . '%')
                            ->orWhere('address', 'like', '%' . $city . '%')
                            ->orWhere('postal_code', 'like', '%' . $city . '%')
                            // Recherche aussi avec la chaîne complète au cas où
                            ->orWhere('city', 'like', '%' . $cityParam . '%')
                            ->orWhere('address', 'like', '%' . $cityParam . '%')
                            ->orWhereRaw("CONCAT(COALESCE(address, ''), ', ', COALESCE(city, ''), ', ', COALESCE(postal_code, '')) LIKE ?", ['%' . $city . '%']);
                })
                // OU recherche dans la localisation du prestataire
                ->orWhereHas('prestataire', function ($q) use ($city, $cityParam) {
                    $q->where(function ($subQ) use ($city, $cityParam) {
                        $subQ->where('city', 'like', '%' . $city . '%')
                             ->orWhere('address', 'like', '%' . $city . '%')
                             ->orWhere('postal_code', 'like', '%' . $city . '%')
                             // Recherche aussi avec la chaîne complète au cas où
                             ->orWhere('city', 'like', '%' . $cityParam . '%')
                             ->orWhere('address', 'like', '%' . $cityParam . '%')
                             ->orWhereRaw("CONCAT(COALESCE(address, ''), ', ', COALESCE(city, ''), ', ', COALESCE(postal_code, '')) LIKE ?", ['%' . $city . '%']);
                    });
                });
            });
        }
        
        if ($request->filled('postal_code')) {
            $query->where('postal_code', $request->postal_code);
        }
        
        // Filtrage par prix
        if ($request->filled('price_min')) {
            $query->where('price_per_day', '>=', $request->price_min);
        }
        
        if ($request->filled('price_max')) {
            $query->where('price_per_day', '<=', $request->price_max);
        }
        
        // Filtrage par note
        if ($request->filled('rating')) {
            $query->where('average_rating', '>=', $request->rating);
        }
        
        // Filtrage par disponibilité
        if ($request->filled('available_from') && $request->filled('available_to')) {
            $query->availableForPeriod($request->available_from, $request->available_to);
        }
        
        // Filtres spéciaux
        
        if ($request->filled('featured')) {
            $query->where('featured', true);
        }
        
        // Filtrage par disponibilité
        if ($request->filled('availability')) {
            switch ($request->availability) {
                case 'available':
                    $query->where('status', 'active')->where('is_available', true);
                    break;

            }
        }
        

        
        // Tri
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price_per_day', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price_per_day', 'desc');
                break;
            case 'rating':
                $query->orderBy('average_rating', 'desc');
                break;
            case 'popular':
                $query->orderBy('total_rentals', 'desc');
                break;
            case 'recent':
                $query->orderBy('created_at', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->orderBy('featured', 'desc')
                      ->orderBy('average_rating', 'desc')
                      ->orderBy('created_at', 'desc');
        }
        
        $equipments = $query->paginate(12)->withQueryString();
        
        // Données pour les filtres
        $categories = \App\Models\Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        $priceRange = Equipment::active()->selectRaw('MIN(price_per_day) as min_price, MAX(price_per_day) as max_price')->first();
        
        // Équipements en vedette
        $featuredEquipment = Equipment::active()->available()->featured()
                                    ->with(['prestataire.user', 'category', 'subcategory'])
                                    ->limit(6)
                                    ->get();
        
        return view('equipment.index', compact('equipments', 'categories', 'priceRange', 'featuredEquipment'));
    }
    
    /**
     * Affiche les détails d'un équipement
     */
    public function show(Equipment $equipment)
    {

        
        // Incrémenter le compteur de vues
        $equipment->increment('view_count');
        
        $equipment->load([
            'prestataire.user',
            'category',
            'subcategory',
            'reviews' => function ($query) {
                $query->approved()->with('client.user')->latest();
            }
        ]);
        
        // Équipements similaires
        $similarEquipment = Equipment::active()->available()
                                   ->where('id', '!=', $equipment->id)
                                   ->where(function ($query) use ($equipment) {
                                       $query->where('category_id', $equipment->category_id)
                                             ->orWhere('subcategory_id', $equipment->subcategory_id)
                                             ->orWhere('category_id', $equipment->subcategory_id)
                                             ->orWhere('subcategory_id', $equipment->category_id);
                                   })
                                   ->inSameCity($equipment->city)
                                   ->with(['prestataire.user', 'category', 'subcategory'])
                                   ->limit(4)
                                   ->get();
        
        // Autres équipements du même prestataire
        $otherEquipment = $equipment->prestataire->equipments()
                                   ->active()
                                   ->available()
                                   ->where('id', '!=', $equipment->id)
                                   ->with(['category', 'subcategory'])
                                   ->limit(3)
                                   ->get();
        
        // Statistiques des avis
        $reviewStats = $equipment->getDetailedRatingStats();
        
        // Vérifier si l'utilisateur actuel est le propriétaire de l'équipement
        $isOwner = Auth::check() && $equipment->isOwnedBy(Auth::user());
        
        return view('equipment.show', compact('equipment', 'similarEquipment', 'otherEquipment', 'reviewStats', 'isOwner'));
    }

    /**
     * Affiche le formulaire de réservation pour un équipement.
     */
    public function showReservationForm(Equipment $equipment)
    {

        $equipment->load(['prestataire.user', 'reviews']);

        $reviewStats = $equipment->getDetailedRatingStats();

        $unavailableDates = $equipment->getUnavailableDates();
        
        // Récupérer les dates de disponibilité de l'équipement
        $availabilityPeriod = [
            'available_from' => $equipment->available_from ? $equipment->available_from->format('Y-m-d') : null,
            'available_until' => $equipment->available_until ? $equipment->available_until->format('Y-m-d') : null
        ];

        return view('equipment.reserve', compact('equipment', 'reviewStats', 'unavailableDates', 'availabilityPeriod'));
    }

    public function rent(Request $request, Equipment $equipment)
    {
        // Check if user is authenticated and has a client profile
        if (!Auth::check() || !Auth::user()->client) {
            return redirect()->route('login')
                           ->with('error', 'Vous devez être connecté en tant que client pour louer un équipement.');
        }
        
        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Additional validation to check for availability can be added here

        $requestNumber = 'REQ-' . strtoupper(uniqid());

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $durationDays = $startDate->diffInDays($endDate) + 1;

        $rentalRequest = EquipmentRentalRequest::create([
            'client_id' => Auth::user()->client->id,
            'equipment_id' => $equipment->id,
            'prestataire_id' => $equipment->prestataire_id,
            'request_number' => $requestNumber,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'duration_days' => $durationDays,
            'unit_price' => $equipment->price_per_day,
            'total_amount' => $equipment->price_per_day * $durationDays,
            'security_deposit' => $equipment->security_deposit,
            'final_amount' => $equipment->price_per_day * $durationDays,
            'status' => 'pending',
        ]);

        // Optionally, you can notify the prestataire
        // $equipment->prestataire->user->notify(new NewEquipmentRentalRequestNotification($rentalRequest));

        return redirect()->route('client.equipment-rental-requests.index')->with('success', 'Votre demande de location a été envoyée avec succès.');
    }
    
    /**
     * Affiche les équipements d'une catégorie
     */
    public function category(\App\Models\Category $category, Request $request)
    {
        // Récupérer les équipements de cette catégorie ou de ses sous-catégories
        $query = Equipment::active()->available()
                         ->where(function ($q) use ($category) {
                             $q->where('category_id', $category->id)
                               ->orWhere('subcategory_id', $category->id);
                             
                             // Si c'est une catégorie parent, inclure aussi les équipements des sous-catégories
                             if ($category->children->count() > 0) {
                                 $q->orWhereIn('subcategory_id', $category->children->pluck('id'));
                             }
                         })
                         ->with(['prestataire.user', 'category', 'subcategory']);
        
        // Appliquer les mêmes filtres que l'index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }
        
        if ($request->filled('price_min')) {
            $query->where('price_per_day', '>=', $request->price_min);
        }
        
        if ($request->filled('price_max')) {
            $query->where('price_per_day', '<=', $request->price_max);
        }
        
        if ($request->filled('rating')) {
            $query->where('average_rating', '>=', $request->rating);
        }
        
        // Tri
        $sortBy = $request->get('sort', 'featured');
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price_per_day', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price_per_day', 'desc');
                break;
            case 'rating':
                $query->orderBy('average_rating', 'desc');
                break;
            case 'popular':
                $query->orderBy('total_rentals', 'desc');
                break;
            case 'recent':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('featured', 'desc')
                      ->orderBy('average_rating', 'desc');
        }
        
        $equipments = $query->paginate(12)->withQueryString();
        
        // Sous-catégories
        $subcategories = $category->children()->active()->withCount('equipment')->get();
        
        // Statistiques de la catégorie
        $stats = $category->getStats();
        
        return view('equipment.category', compact('category', 'equipments', 'subcategories', 'stats'));
    }
    
    /**
     * Affiche le formulaire de demande de location
     */
    public function requestRental(Equipment $equipment)
    {
        if (!$equipment->isActive() || !$equipment->isAvailable()) {
            abort(404);
        }
        
        // Vérifier que l'utilisateur est connecté et est un client
        if (!Auth::check() || !Auth::user()->client) {
            return redirect()->route('login')
                           ->with('message', 'Vous devez être connecté en tant que client pour faire une demande de location.');
        }
        
        $equipment->load(['prestataire.user', 'categories']);
        
        return view('equipment.request-rental', compact('equipment'));
    }
    
    /**
     * Traite la demande de location
     */
    public function submitRentalRequest(Request $request, Equipment $equipment)
    {
        if (!Auth::check() || !Auth::user()->client) {
            return redirect()->route('login');
        }
        
        $validated = $request->validate([
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',

            'pickup_address' => 'nullable|string|max:500',

            'pickup_required' => 'boolean',
            'client_message' => 'nullable|string|max:1000',
            'special_requirements' => 'nullable|string|max:500',
            'client_contact_info' => 'nullable|array'
        ]);
        
        // Vérifier la disponibilité
        if (!$equipment->isAvailableForPeriod($validated['start_date'], $validated['end_date'])) {
            return back()->with('error', 'L\'équipement n\'est pas disponible pour cette période.');
        }
        
        // Calculer la durée et les montants
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $durationDays = $startDate->diffInDays($endDate) + 1;
        
        // Vérifier la durée minimum/maximum
        if ($durationDays < $equipment->minimum_rental_duration) {
            return back()->with('error', 'La durée minimum de location est de ' . $equipment->minimum_rental_duration . ' jour(s).');
        }
        
        if ($equipment->maximum_rental_duration && $durationDays > $equipment->maximum_rental_duration) {
            return back()->with('error', 'La durée maximum de location est de ' . $equipment->maximum_rental_duration . ' jour(s).');
        }
        
        $totalAmount = $equipment->calculatePrice($durationDays);
        $finalAmount = $totalAmount + $equipment->security_deposit;
        
        // Créer la demande
        $rentalRequest = EquipmentRentalRequest::create([
            'equipment_id' => $equipment->id,
            'client_id' => Auth::user()->client->id,
            'prestataire_id' => $equipment->prestataire_id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'duration_days' => $durationDays,
            'unit_price' => $equipment->price_per_day,
            'total_amount' => $totalAmount,
            'security_deposit' => $equipment->security_deposit,

            'final_amount' => $finalAmount,

            'pickup_address' => $validated['pickup_address'],

            'pickup_required' => $validated['pickup_required'] ?? false,
            'client_message' => $validated['client_message'],
            'special_requirements' => $validated['special_requirements'],
            'client_contact_info' => $validated['client_contact_info'] ?? [],
            'expires_at' => now()->addDays(7), // Expire dans 7 jours
            'client_ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        // TODO: Envoyer notification au prestataire
        
        return redirect()->route('client.equipment.requests.show', $rentalRequest)
                        ->with('success', 'Votre demande de location a été envoyée avec succès!');
    }
    
    /**
     * Affiche le formulaire de signalement
     */
    public function reportForm(Equipment $equipment)
    {
        return view('equipment.report', compact('equipment'));
    }
    
    /**
     * Traite le signalement
     */
    public function submitReport(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'category' => 'required|in:safety,condition,fraud,inappropriate,pricing,availability,other',
            'description' => 'required|string|min:20|max:1000',
            'evidence_photos' => 'nullable|array|max:5',
            'evidence_photos.*' => 'image|mimes:jpeg,png,jpg,webp',
            'contact_info' => 'nullable|array'
        ]);
        
        // Gestion des photos de preuve
        $evidencePhotos = [];
        if ($request->hasFile('evidence_photos')) {
            foreach ($request->file('evidence_photos') as $photo) {
                $evidencePhotos[] = $photo->store('reports/evidence', 'public');
            }
        }
        
        // Déterminer le type de rapporteur
        $reporterType = 'anonymous';
        $reporterId = null;
        
        if (Auth::check()) {
            if (Auth::user()->client) {
                $reporterType = 'client';
                $reporterId = Auth::user()->client->id;
            } elseif (Auth::user()->prestataire) {
                $reporterType = 'prestataire';
                $reporterId = Auth::user()->prestataire->id;
            }
        }
        
        // Déterminer la priorité automatiquement
        $priority = 'medium';
        if ($validated['category'] === 'safety') {
            $priority = 'urgent';
        } elseif (in_array($validated['category'], ['fraud', 'inappropriate'])) {
            $priority = 'high';
        }
        
        $report = EquipmentReport::create([
            'equipment_id' => $equipment->id,
            'reporter_id' => $reporterId,
            'reporter_type' => $reporterType,
            'reason' => $validated['reason'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'evidence_photos' => $evidencePhotos,
            'contact_info' => $validated['contact_info'] ?? [],
            'priority' => $priority,
            'reporter_ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        // Envoyer notification aux administrateurs
        $admins = \App\Models\User::where('role', 'admin')->get();
        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\EquipmentReportCreated($report));
        
        return redirect()->route('equipment.show', $equipment)
                        ->with('success', 'Votre signalement a été envoyé. Nous l\'examinerons dans les plus brefs délais.');
    }
    
    /**
     * API pour vérifier la disponibilité
     */
    public function checkAvailability(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);
        
        $available = $equipment->isAvailableForPeriod(
            $validated['start_date'], 
            $validated['end_date']
        );
        
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $durationDays = $startDate->diffInDays($endDate) + 1;
        
        $price = $available ? $equipment->calculatePrice($durationDays) : null;
        
        return response()->json([
            'available' => $available,
            'duration_days' => $durationDays,
            'price' => $price,
            'security_deposit' => $equipment->security_deposit,
            'delivery_fee' => $equipment->delivery_fee
        ]);
    }
    
    /**
     * Recherche d'équipements (AJAX)
     */
    public function search(Request $request)
    {
        $all_equipment = Equipment::all();
        Log::info('All equipment from DB:', $all_equipment->toArray());

        $query = Equipment::query();
        
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        $equipment = $query->with(['prestataire.user', 'categories'])
                          ->limit(10)
                          ->get();
        
        return response()->json($equipment);
    }
}