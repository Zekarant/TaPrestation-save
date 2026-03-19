<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Support\Facades\Log;

use App\Models\EquipmentRentalRequest;
use App\Notifications\SimpleNewEquipmentRentalRequestNotification;
use App\Models\EquipmentReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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

        // Filtrage par catégorie et sous-catégorie
        if ($request->filled('subcategory')) {
            // Sous-catégorie spécifique sélectionnée
            $query->where('subcategory_id', $request->subcategory);
        } elseif ($request->filled('category')) {
            // Catégorie principale - inclut la catégorie et toutes ses sous-catégories
            $categoryId = (int) $request->category;
            $subCategoryIds = \App\Models\Category::where('parent_id', $categoryId)->pluck('id')->toArray();
            $allSubIds = array_values(array_unique(array_filter(array_merge([$categoryId], $subCategoryIds))));

            $query->where(function ($q) use ($categoryId, $allSubIds) {
                $q->where('category_id', $categoryId)
                    // Normal case: subcategory_id is a child category id
                    ->orWhereIn('subcategory_id', $allSubIds)
                    // Backward compatibility: some records might store category id in subcategory_id
                    ->orWhere('subcategory_id', $categoryId);
            });
        }

        // Géocodage automatique si on a une ville mais pas de coordonnées GPS
        $latitude = $request->filled('latitude') ? (float) $request->latitude : null;
        $longitude = $request->filled('longitude') ? (float) $request->longitude : null;
        $radius = $request->filled('radius') ? (int) $request->radius : 25; // 25km par défaut

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
                    }
                }
            } catch (\Exception $e) {
                // Géocodage échoué, continuer sans coordonnées
            }
        }

        $hasGpsCoordinates = $latitude && $longitude && $radius;

        // Filtrage par localisation avec recherche fuzzy (uniquement si pas de coordonnées GPS)
        // Si on a des coordonnées GPS, le filtre GPS avec rayon sera utilisé à la place
        if ($request->filled('city') && !$hasGpsCoordinates) {
            $cityParam = $request->city;
            // Extraire le nom de la ville si la chaîne contient des virgules ou parenthèses (format GPS: "Oujda, 60000" ou "Ville (12345)")
            $cityParts = explode(',', $cityParam);
            $city = trim($cityParts[0]); // Prendre seulement la première partie (nom de la ville)
            // Retirer aussi le code postal entre parenthèses si présent
            $city = preg_replace('/\s*\(\d+\)\s*$/', '', $city);
            $city = trim($city);

            $query->where(function ($mainQ) use ($city, $cityParam) {
                // Recherche dans les propres champs de localisation de l'équipement
                $mainQ->where(function ($equipQ) use ($city, $cityParam) {
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

        // Filtre par rayon GPS (géolocalisation) - prioritaire sur le filtre texte ville
        // $latitude, $longitude et $radius sont déjà définis plus haut (avec géocodage automatique si nécessaire)
        if ($hasGpsCoordinates) {
            $cityText = $request->input('city', '');

            // Formule Haversine pour calculer la distance en km
            // Le groupe where englobe les conditions (équipement OU prestataire OU ville)
            $query->where(function ($mainQ) use ($latitude, $longitude, $radius, $cityText) {
                // 1. Recherche dans les coordonnées de l'équipement lui-même
                $mainQ->where(function ($q) use ($latitude, $longitude, $radius) {
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
                })
                    // 2. OU dans les coordonnées du prestataire
                    ->orWhereHas('prestataire', function ($subQ) use ($latitude, $longitude, $radius) {
                        $subQ->whereNotNull('latitude')
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
                    $mainQ->orWhere(function ($cityQ) use ($city) {
                        $cityQ->where('city', 'like', '%' . $city . '%')
                            ->orWhere('address', 'like', '%' . $city . '%')
                            ->orWhereHas('prestataire', function ($pq) use ($city) {
                                $pq->where('city', 'like', '%' . $city . '%')
                                    ->orWhere('address', 'like', '%' . $city . '%');
                            });
                    });
                }
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

        // Filtrage avancé par période de disponibilité (dates libres, sans chevauchement)
        $periodStartRaw = $request->input('equipment_date_from', $request->input('available_from'));
        $periodEndRaw = $request->input('equipment_date_to', $request->input('available_to'));

        $periodStart = null;
        $periodEnd = null;

        try {
            if (!empty($periodStartRaw)) {
                $periodStart = Carbon::parse($periodStartRaw)->startOfDay();
            }
            if (!empty($periodEndRaw)) {
                $periodEnd = Carbon::parse($periodEndRaw)->startOfDay();
            }
        } catch (\Throwable $e) {
            $periodStart = null;
            $periodEnd = null;
        }

        if ($periodStart && !$periodEnd) {
            $periodEnd = $periodStart->copy();
        }
        if ($periodEnd && !$periodStart) {
            $periodStart = $periodEnd->copy();
        }

        if ($periodStart && $periodEnd) {
            if ($periodEnd->lt($periodStart)) {
                [$periodStart, $periodEnd] = [$periodEnd, $periodStart];
            }

            $query->availableForPeriod(
                $periodStart->toDateString(),
                $periodEnd->toDateString()
            );
        }

        // Filtres spéciaux

        if ($request->filled('featured')) {
            $query->where('featured', true);
        }

        // Filtrer les équipements livrables
        if ($request->boolean('with_delivery')) {
            $query->where('delivery_available', true);
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
            case 'distance':
                // Tri par distance si coordonnées disponibles
                if ($request->filled('latitude') && $request->filled('longitude')) {
                    $lat = (float) $request->latitude;
                    $lon = (float) $request->longitude;
                    $query->selectRaw("
                        *, 
                        (6371 * acos(
                            cos(radians(?)) * cos(radians(COALESCE(latitude, 0))) * cos(radians(COALESCE(longitude, 0)) - radians(?)) + 
                            sin(radians(?)) * sin(radians(COALESCE(latitude, 0)))
                        )) AS distance
                    ", [$lat, $lon, $lat])
                        ->orderBy('distance', 'asc');
                } else {
                    $query->orderBy('created_at', 'desc');
                }
                break;
            default:
                $query->orderBy('featured', 'desc')
                    ->orderBy('average_rating', 'desc')
                    ->orderBy('created_at', 'desc');
        }

        // Si coordonnées GPS disponibles, calculer la distance pour chaque équipement
        if ($request->filled('latitude') && $request->filled('longitude') && $sortBy !== 'distance') {
            $lat = (float) $request->latitude;
            $lon = (float) $request->longitude;

            // Ajouter le calcul de distance
            $query->selectRaw("
                equipment.*,
                CASE 
                    WHEN equipment.latitude IS NOT NULL AND equipment.longitude IS NOT NULL THEN
                        ROUND(6371 * acos(
                            LEAST(1.0, GREATEST(-1.0,
                                cos(radians(?)) * cos(radians(equipment.latitude)) * cos(radians(equipment.longitude) - radians(?)) +
                                sin(radians(?)) * sin(radians(equipment.latitude))
                            ))
                        ), 1)
                    ELSE NULL
                END as distance_km
            ", [$lat, $lon, $lat]);
        }

        $equipments = $query->paginate(12)->withQueryString();

        // Si GPS utilisé, ajouter la distance du prestataire pour les équipements sans coordonnées propres
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $lat = (float) $request->latitude;
            $lon = (float) $request->longitude;

            $equipments->getCollection()->transform(function ($equipment) use ($lat, $lon) {
                if ((!isset($equipment->distance_km) || $equipment->distance_km === null) && $equipment->prestataire) {
                    $pLat = $equipment->prestataire->latitude;
                    $pLon = $equipment->prestataire->longitude;
                    if ($pLat && $pLon) {
                        $earthRadius = 6371;
                        $dLat = deg2rad($pLat - $lat);
                        $dLon = deg2rad($pLon - $lon);
                        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat)) * cos(deg2rad($pLat)) * sin($dLon / 2) * sin($dLon / 2);
                        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                        $equipment->distance_km = round($earthRadius * $c, 1);
                    }
                }
                return $equipment;
            });
        }

        // Données pour les filtres - uniquement catégories équipements
        $categories = \App\Models\Category::ofTypeEquipment()->whereNull('parent_id')->with('children')->orderBy('name')->get();
        $priceRange = Equipment::active()->selectRaw('MIN(price_per_day) as min_price, MAX(price_per_day) as max_price')->first();

        // Équipements en vedette
        $featuredEquipment = Equipment::active()->available()->featured()
            ->with(['prestataire.user', 'category', 'subcategory'])
            ->limit(6)
            ->get();

        return view('equipment.index-modern', compact('equipments', 'categories', 'priceRange', 'featuredEquipment'));
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
        /** @var \Carbon\Carbon|null $availFrom */
        $availFrom = $equipment->available_from;
        /** @var \Carbon\Carbon|null $availUntil */
        $availUntil = $equipment->available_until;
        $availabilityPeriod = [
            'available_from' => $availFrom?->toDateString(),
            'available_until' => $availUntil?->toDateString()
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

        // Vérifier explicitement si c'est une location horaire (valeur '1' ou 'true')
        $isHourlyMode = $request->has('is_hourly') && in_array($request->is_hourly, ['1', 'true', true], true);

        $rules = [
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];

        if ($isHourlyMode) {
            $rules['start_time'] = 'required';
            $rules['end_time'] = 'required';
        }

        $validated = $request->validate($rules);

        // Additional validation to check for availability can be added here

        $requestNumber = 'REQ-' . strtoupper(uniqid());

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $isHourly = $isHourlyMode;
        $startTime = null;
        $endTime = null;
        $durationHours = null;
        $unitPrice = $equipment->price_per_day;
        $totalAmount = 0;
        $durationDays = 0;

        if ($isHourly) {
            $startTime = $validated['start_time'];
            $endTime = $validated['end_time'];

            $startDateTime = Carbon::parse($validated['start_date'] . ' ' . $startTime);
            $endDateTime = Carbon::parse($validated['end_date'] . ' ' . $endTime);

            // If end time is before start time on same day, assume next day (though UI should handle this)
            if ($endDateTime->lt($startDateTime)) {
                $endDateTime->addDay();
            }

            $durationHours = $startDateTime->diffInHours($endDateTime);
            // Minimum 1 hour
            if ($durationHours < 1)
                $durationHours = 1;

            $unitPrice = $equipment->price_per_hour ?? ($equipment->price_per_day / 8); // Fallback to day/8 if no hourly price
            $totalAmount = $unitPrice * $durationHours;
        } else {
            $durationDays = $startDate->diffInDays($endDate) + 1;
            $unitPrice = $equipment->price_per_day;
            $totalAmount = $unitPrice * $durationDays;
        }

        try {
            $rentalRequest = EquipmentRentalRequest::create([
                'client_id' => Auth::user()->client->id,
                'equipment_id' => $equipment->id,
                'prestataire_id' => $equipment->prestataire_id,
                'request_number' => $requestNumber,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_hourly' => $isHourly ? true : false,
                'duration_hours' => $durationHours ?? null,
                'duration_days' => $durationDays > 0 ? $durationDays : 1,
                'unit_price' => $unitPrice ?? 0,
                'total_amount' => $totalAmount ?? 0,
                'security_deposit' => $equipment->security_deposit ?? 0,
                'final_amount' => $totalAmount ?? 0,
                'status' => 'pending',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur création location: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => [
                    'client_id' => Auth::user()->client->id ?? null,
                    'equipment_id' => $equipment->id,
                    'start_date' => $validated['start_date'] ?? null,
                    'end_date' => $validated['end_date'] ?? null,
                    'is_hourly' => $isHourly,
                    'duration_days' => $durationDays,
                    'unit_price' => $unitPrice,
                    'total_amount' => $totalAmount,
                ]
            ]);
            return back()->with('error', 'Une erreur est survenue lors de la création de la demande. Veuillez réessayer.');
        }

        // Vérifier si un paiement en ligne est requis pour cet équipement
        $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
            ? normalize_payment_requirement_for_mode($equipment->payment_requirement ?? 'none')
            : ($equipment->payment_requirement ?? 'none');

        if (in_array($paymentRequirement, ['deposit', 'full'])) {
            // Vérifier que le prestataire a configuré Stripe
            if (!empty($equipment->prestataire?->stripe_account_id)) {
                // Rediriger vers la page de paiement SANS notifier le prestataire
                // La notification sera envoyée après confirmation du paiement
                return redirect()->route('client.payments.rental.form', $rentalRequest)
                    ->with('info', 'Pour valider votre demande de location, veuillez procéder au paiement.');
            }
            // Sinon, pas de Stripe → fallback espèces, on continue pour notifier le prestataire
        }

        // Pas de paiement requis - Envoyer une notification push au prestataire
        try {
            $equipment->prestataire->user->notify(new SimpleNewEquipmentRentalRequestNotification($rentalRequest));
        } catch (\Exception $e) {
            Log::warning('Notification non envoyée: ' . $e->getMessage());
        }

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

        $totalAmount = $equipment->calculatePrice($startDate, $endDate);
        // final_amount = montant location hors caution (la caution est stockée séparément)
        $finalAmount = $totalAmount;

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

        // Vérifier si un paiement en ligne est requis
        $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
            ? normalize_payment_requirement_for_mode($equipment->payment_requirement ?? 'none')
            : ($equipment->payment_requirement ?? 'none');
        if (in_array($paymentRequirement, ['deposit', 'full']) && !empty($equipment->prestataire?->stripe_account_id)) {
            return redirect()->route('client.payments.rental.form', $rentalRequest)
                ->with('info', 'Pour valider votre demande de location, veuillez procéder au paiement.');
        }

        // Pas de paiement en ligne → notifier le prestataire
        $prestataire = $rentalRequest->equipment->prestataire;
        if ($prestataire && $prestataire->user) {
            try {
                $prestataire->user->notify(new SimpleNewEquipmentRentalRequestNotification($rentalRequest));
            } catch (\Exception $e) {
                Log::error('Failed to notify prestataire of rental request', [
                    'error' => $e->getMessage(),
                    'rental_request_id' => $rentalRequest->id
                ]);
            }
        }

        return redirect()->route('client.equipment-rental-requests.show', $rentalRequest)
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
            'evidence_photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
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

        $price = $available ? $equipment->calculatePrice($startDate, $endDate) : null;

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
