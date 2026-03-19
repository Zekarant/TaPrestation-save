<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ServiceController extends Controller
{
    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    private function buildLocationVariants(?string $locationText): array
    {
        $locationText = (string) $locationText;
        $locationText = trim($locationText);

        // City candidate from "Ville, CP" or "Ville (CP)" or "Ville 75001"
        $cityParts = explode(',', $locationText);
        $city = trim($cityParts[0] ?? '');
        $city = preg_replace('/\s*\([^)]*\)\s*/', '', $city);
        $city = preg_replace('/\s+\d{4,5}\s*$/', '', $city);
        $city = trim(preg_replace('/\s+/', ' ', $city));

        $postalCode = null;
        if (preg_match('/\b(\d{4,5})\b/', $locationText, $m)) {
            $postalCode = $m[1];
        }

        $variants = [];
        if ($city !== '') {
            $variants[] = $city;
            $variants[] = str_replace('-', ' ', $city);
            $variants[] = str_replace(' ', '-', $city);

            foreach (['le ', 'la ', 'les '] as $prefix) {
                if (stripos($city, $prefix) === 0) {
                    $variants[] = trim(substr($city, strlen($prefix)));
                }
            }
            if (stripos($city, "l'") === 0) {
                $variants[] = trim(substr($city, 2));
            }
        }

        $variants = array_values(array_unique(array_filter(array_map(function ($v) {
            return trim(preg_replace('/\s+/', ' ', (string) $v));
        }, $variants))));

        return [
            'raw' => $locationText,
            'city' => $city,
            'postalCode' => $postalCode,
            'variants' => $variants,
        ];
    }

    private function canUseFilterDebug(Request $request): bool
    {
        if (!app()->environment(['local', 'testing'])) {
            return false;
        }

        $configuredKey = trim((string) config('services.filter_debug.key', ''));
        $providedKey = trim((string) $request->input('debug_key', ''));

        if ($configuredKey === '' || $providedKey === '') {
            return false;
        }

        if (!hash_equals($configuredKey, $providedKey)) {
            return false;
        }

        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && $user->hasRole('administrateur')) {
            return true;
        }

        return (string) ($user->role ?? '') === 'administrateur';
    }

    private function serviceHasFreeSlotForDate(
        Service $service,
        Carbon $targetDate,
        ?string $exactTime = null,
        ?string $minimumTime = null
    ): bool {
        if (!$service->prestataire) {
            return false;
        }

        if (!function_exists('generate_time_slots_for_service')) {
            return false;
        }

        $slots = generate_time_slots_for_service($service, $targetDate->copy()->startOfDay(), $targetDate->copy()->endOfDay());

        foreach ($slots as $slot) {
            $slotStart = $slot['datetime'] ?? null;
            if (!$slotStart) {
                continue;
            }

            if (!$slotStart instanceof Carbon) {
                $slotStart = Carbon::parse($slotStart);
            }

            if ((bool) ($slot['is_booked'] ?? false) === true) {
                continue;
            }

            $slotTime = $slotStart->format('H:i');

            if (!empty($exactTime) && $slotTime !== $exactTime) {
                continue;
            }

            if (empty($exactTime) && !empty($minimumTime) && $slotTime < $minimumTime) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Affiche la liste des services publics avec filtrage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Sauvegarder les filtres en session pour pouvoir les récupérer lors du retour
        $filters = $request->only([
            'search',
            'category',
            'main_category',
            'sub_category',
            'price_min',
            'price_max',
            'location',
            'latitude',
            'longitude',
            'radius',
            'verified_only',
            'available_now',
            'reservable',
            'service_date',
            'service_time',
            'sort',
        ]);
        session(['services_filters' => $filters]);

        $user = Auth::user();
        $userRole = $user->role ?? null;
        $isAdmin = ($userRole === 'admin');
        $currentPrestataire = $user?->prestataire;

        $query = Service::with(['prestataire', 'categories', 'availabilities']);

        // Visibilité:
        // - Utilisateurs normaux: uniquement prestataires approuvés + services visibles
        // - Prestataire connecté: services approuvés+visibles + ses propres services (même non approuvés / invisibles)
        // - Admin: tout (si prestataire existe)
        if ($isAdmin) {
            $query->whereHas('prestataire');
        } elseif ($currentPrestataire) {
            $prestataireId = $currentPrestataire->id;
            $query->where(function ($mainQ) use ($prestataireId) {
                $mainQ->where(function ($q) {
                    $q->where('is_visible', true)
                        ->whereHas('prestataire', function ($pQ) {
                            $pQ->where('is_approved', true);
                        });
                })
                    ->orWhere('prestataire_id', $prestataireId);
            });
        } else {
            $query->where('is_visible', true)
                ->whereHas('prestataire', function ($q) {
                    $q->where('is_approved', true);
                });
        }

        // Recherche par mot-clé
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%')
                    ->orWhereHas('prestataire.user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('categories', function ($catQuery) use ($keyword) {
                        $catQuery->where('name', 'like', '%' . $keyword . '%')
                            // Rechercher aussi dans les catégories parentes
                            ->orWhereHas('parent', function ($parentQuery) use ($keyword) {
                                $parentQuery->where('name', 'like', '%' . $keyword . '%');
                            });
                    });
            });
        }

        // Filtrage par catégorie (principale ou sous-catégorie)
        if ($request->filled('sub_category') && $request->sub_category != '') {
            // Utilisateur a sélectionné une sous-catégorie spécifique
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->sub_category);
            });
        } elseif ($request->filled('category') && $request->category != '') {
            // Utilisateur a sélectionné une catégorie (peut être principale ou sous)
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category);
            });
        } elseif ($request->filled('main_category') && $request->main_category != '') {
            // Utilisateur a sélectionné seulement une catégorie principale
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where(function ($subQ) use ($request) {
                    // Inclure la catégorie principale elle-même
                    $subQ->where('categories.id', $request->main_category)
                        // ET toutes ses sous-catégories
                        ->orWhere('categories.parent_id', $request->main_category);
                });
            });
        }

        // Filtrer par prix minimum
        if ($request->filled('price_min')) {
            $min = $request->price_min;
            $query->where(function ($q) use ($min) {
                $q->where('price', '>=', $min)
                    ->orWhereNull('price');
            });
        }

        // Filtrer par prix maximum
        if ($request->filled('price_max')) {
            $max = $request->price_max;
            $query->where(function ($q) use ($max) {
                $q->where('price', '<=', $max)
                    ->orWhereNull('price');
            });
        }

        // Filtrage par localisation avec recherche par rayon si coordonnées disponibles
        if ($request->filled('latitude') && $request->filled('longitude') && $request->filled('radius')) {
            $lat = (float) $request->latitude;
            $lon = (float) $request->longitude;
            $radius = (int) $request->radius;
            $locationText = $request->input('location', '');

            $loc = $this->buildLocationVariants($locationText);

            // Formule Haversine pour calculer la distance en km
            // Le groupe where englobe les deux conditions (service OU prestataire OU texte ville)
            $query->where(function ($mainQ) use ($lat, $lon, $radius, $locationText, $loc) {
                // 1. Chercher dans les services avec coordonnées
                $mainQ->where(function ($q) use ($lat, $lon, $radius) {
                    $q->whereNotNull('latitude')
                        ->whereNotNull('longitude')
                        ->whereRaw("
                          (6371 * acos(
                              LEAST(1.0, GREATEST(-1.0,
                                  cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) +
                                  sin(radians(?)) * sin(radians(latitude))
                              ))
                          )) <= ?
                      ", [$lat, $lon, $lat, $radius]);
                })
                    // 2. OU dans le prestataire avec coordonnées
                    ->orWhereHas('prestataire', function ($q) use ($lat, $lon, $radius) {
                        $q->whereNotNull('latitude')
                            ->whereNotNull('longitude')
                            ->whereRaw("
                          (6371 * acos(
                              LEAST(1.0, GREATEST(-1.0,
                                  cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) +
                                  sin(radians(?)) * sin(radians(latitude))
                              ))
                          )) <= ?
                      ", [$lat, $lon, $lat, $radius]);
                    });

                // 3. OU fallback par texte (ville / CP / adresse) si disponible
                if (!empty($locationText)) {
                    $variants = $loc['variants'];
                    $postalCode = $loc['postalCode'];

                    $mainQ->orWhere(function ($cityQ) use ($variants, $postalCode, $locationText) {
                        // Match service.city / service.postal_code / service.address
                        $cityQ->where(function ($q) use ($variants, $postalCode, $locationText) {
                            foreach ($variants as $variant) {
                                $q->orWhere('city', 'like', '%' . $variant . '%')
                                    ->orWhere('address', 'like', '%' . $variant . '%');
                            }
                            if (!empty($postalCode)) {
                                $q->orWhere('postal_code', 'like', '%' . $postalCode . '%');
                            }
                            $q->orWhere('city', 'like', '%' . $locationText . '%')
                                ->orWhere('address', 'like', '%' . $locationText . '%');
                        })
                            // OU match prestataire.city / prestataire.postal_code / prestataire.address
                            ->orWhereHas('prestataire', function ($pq) use ($variants, $postalCode, $locationText) {
                                $pq->where(function ($q) use ($variants, $postalCode, $locationText) {
                                    foreach ($variants as $variant) {
                                        $q->orWhere('city', 'like', '%' . $variant . '%')
                                            ->orWhere('address', 'like', '%' . $variant . '%');
                                    }
                                    if (!empty($postalCode)) {
                                        $q->orWhere('postal_code', 'like', '%' . $postalCode . '%');
                                    }
                                    $q->orWhere('city', 'like', '%' . $locationText . '%')
                                        ->orWhere('address', 'like', '%' . $locationText . '%');
                                });
                            });
                    });
                }
            });
        }
        // Sinon filtrage par texte classique
        elseif ($request->filled('location')) {
            $locationParam = $request->location;
            $loc = $this->buildLocationVariants($locationParam);
            $variants = $loc['variants'];
            $postalCode = $loc['postalCode'];

            $query->where(function ($mainQ) use ($variants, $postalCode, $locationParam) {
                // Recherche dans les propres champs de localisation du service
                $mainQ->where(function ($serviceQ) use ($variants, $postalCode, $locationParam) {
                    $serviceQ->where(function ($q) use ($variants, $postalCode) {
                        foreach ($variants as $variant) {
                            $q->orWhere('city', 'like', '%' . $variant . '%');
                        }
                        if (!empty($postalCode)) {
                            $q->orWhere('postal_code', 'like', '%' . $postalCode . '%');
                        }
                    })
                        ->orWhere('address', 'like', '%' . ($variants[0] ?? '') . '%')
                        // Recherche aussi avec la chaîne complète au cas où
                        ->orWhere('city', 'like', '%' . $locationParam . '%')
                        ->orWhere('address', 'like', '%' . $locationParam . '%')
                        ->orWhereRaw("CONCAT(COALESCE(address, ''), ', ', COALESCE(city, ''), ', ', COALESCE(postal_code, '')) LIKE ?", ['%' . ($variants[0] ?? '') . '%']);
                })
                    // OU recherche dans la localisation du prestataire
                    ->orWhereHas('prestataire', function ($q) use ($variants, $postalCode, $locationParam) {
                        $q->where(function ($subQ) use ($variants, $postalCode, $locationParam) {
                            $subQ->where(function ($q2) use ($variants, $postalCode) {
                                foreach ($variants as $variant) {
                                    $q2->orWhere('city', 'like', '%' . $variant . '%');
                                }
                                if (!empty($postalCode)) {
                                    $q2->orWhere('postal_code', 'like', '%' . $postalCode . '%');
                                }
                            })
                                ->orWhere('address', 'like', '%' . ($variants[0] ?? '') . '%')
                                // Recherche aussi avec la chaîne complète au cas où
                                ->orWhere('city', 'like', '%' . $locationParam . '%')
                                ->orWhere('address', 'like', '%' . $locationParam . '%')
                                // Recherche fuzzy dans les coordonnées GPS converties en adresse
                                ->orWhereRaw("CONCAT(COALESCE(address, ''), ', ', COALESCE(city, ''), ', ', COALESCE(postal_code, '')) LIKE ?", ['%' . ($variants[0] ?? '') . '%']);
                        });
                    });
            });
        }

        // Filtrage pour les prestataires certifiés
        if ($request->boolean('verified_only')) {
            $query->whereHas('prestataire', function ($q) {
                $q->where('is_verified', true);
            });
        }

        // Filtrage par services réservables en ligne
        if ($request->boolean('reservable')) {
            $query->where('reservable', true);
        }

        // Filtrage avancé de disponibilité réelle (date/heure)
        $selectedDateRaw = $request->input('service_date');
        $selectedTimeRaw = $request->input('service_time');
        $filterAvailableNow = $request->boolean('available_now');

        $selectedDate = null;
        $selectedTime = null;
        $minimumTime = null;

        if (!empty($selectedDateRaw)) {
            try {
                $selectedDate = Carbon::parse($selectedDateRaw)->startOfDay();
            } catch (\Throwable $e) {
                $selectedDate = null;
            }
        }

        if (!empty($selectedTimeRaw)) {
            try {
                $selectedTime = Carbon::createFromFormat('H:i', $selectedTimeRaw)->format('H:i');
            } catch (\Throwable $e) {
                $selectedTime = null;
            }
        }

        if ($selectedTime && !$selectedDate) {
            $selectedDate = now()->startOfDay();
        }

        if ($filterAvailableNow && !$selectedDate) {
            $selectedDate = now()->startOfDay();
            $minimumTime = now()->format('H:i');
            $query->where('reservable', true);
        }

        if ($selectedDate) {
            $targetDayOfWeek = $selectedDate->dayOfWeek;
            $query->whereHas('prestataire.availabilities', function ($q) use ($targetDayOfWeek) {
                $q->where('is_active', true)
                    ->where('day_of_week', $targetDayOfWeek);
            });

            $candidateIds = (clone $query)->pluck('services.id')->unique()->values();
            $matchingIds = collect();

            if ($candidateIds->isNotEmpty()) {
                $candidateServices = Service::with(['prestataire.availabilities'])
                    ->whereIn('id', $candidateIds->all())
                    ->get();

                $matchingIds = $candidateServices
                    ->filter(function (Service $service) use ($selectedDate, $selectedTime, $minimumTime) {
                        return $this->serviceHasFreeSlotForDate($service, $selectedDate, $selectedTime, $minimumTime);
                    })
                    ->pluck('id')
                    ->values();
            }

            if ($matchingIds->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('services.id', $matchingIds->all());
            }
        }

        // Tri
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'recent':
                    $query->latest();
                    break;
                case 'distance':
                    // Tri par distance géré ci-dessous
                    break;
            }
        } else {
            $query->latest();
        }

        // Mode debug (diagnostic filtre localisation): /services?...&debug=1&debug_key=...
        // Placé ici pour diagnostiquer le query final (incluant tous les filtres) avant pagination.
        if ($request->boolean('debug') && $this->canUseFilterDebug($request)) {
            $lat = $request->filled('latitude') ? (float) $request->latitude : null;
            $lon = $request->filled('longitude') ? (float) $request->longitude : null;
            $radius = $request->filled('radius') ? (float) $request->radius : null;
            $loc = $this->buildLocationVariants($request->input('location', ''));

            $service = Service::with('prestataire')->find(73);
            $serviceData = null;
            $eval = null;
            if ($service) {
                $p = $service->prestataire;
                $serviceData = [
                    'id' => $service->id,
                    'title' => $service->title,
                    'service_city' => $service->city,
                    'service_postal_code' => $service->postal_code,
                    'service_address' => $service->address ?? null,
                    'service_lat' => $service->latitude,
                    'service_lon' => $service->longitude,
                    'prestataire_city' => $p?->city,
                    'prestataire_postal_code' => $p?->postal_code,
                    'prestataire_address' => $p?->address,
                    'prestataire_lat' => $p?->latitude,
                    'prestataire_lon' => $p?->longitude,
                    'prestataire_approved' => (bool) ($p?->is_approved),
                    'prestataire_verified' => (bool) ($p?->is_verified),
                ];

                $variants = $loc['variants'];
                $postalCode = $loc['postalCode'];

                $matchesCity = function (?string $value) use ($variants): bool {
                    $value = (string) $value;
                    foreach ($variants as $variant) {
                        if ($variant !== '' && stripos($value, $variant) !== false) {
                            return true;
                        }
                    }
                    return false;
                };

                $matchesPostal = function (?string $value) use ($postalCode): bool {
                    if (empty($postalCode))
                        return false;
                    return stripos((string) $value, (string) $postalCode) !== false;
                };

                $matchesAddress = function (?string $value) use ($variants): bool {
                    $value = (string) $value;
                    foreach ($variants as $variant) {
                        if ($variant !== '' && stripos($value, $variant) !== false) {
                            return true;
                        }
                    }
                    return false;
                };

                $dService = null;
                if ($lat !== null && $lon !== null && $service->latitude && $service->longitude) {
                    $dService = $this->haversineKm($lat, $lon, (float) $service->latitude, (float) $service->longitude);
                }
                $dPrest = null;
                if ($lat !== null && $lon !== null && $p && $p->latitude && $p->longitude) {
                    $dPrest = $this->haversineKm($lat, $lon, (float) $p->latitude, (float) $p->longitude);
                }

                $eval = [
                    'radius_km' => $radius,
                    'distance_service_km' => $dService,
                    'distance_prestataire_km' => $dPrest,
                    'within_radius_service_coords' => ($radius !== null && $dService !== null) ? ($dService <= $radius) : null,
                    'within_radius_prestataire_coords' => ($radius !== null && $dPrest !== null) ? ($dPrest <= $radius) : null,
                    'match_city_service' => $matchesCity($service->city),
                    'match_city_prestataire' => $matchesCity($p?->city),
                    'match_postal_service' => $matchesPostal($service->postal_code),
                    'match_postal_prestataire' => $matchesPostal($p?->postal_code),
                    'match_address_service' => $matchesAddress($service->address ?? null),
                    'match_address_prestataire' => $matchesAddress($p?->address),
                ];
            }

            // Vérifier si le service 73 passe le query complet
            $queryFor73 = clone $query;
            $passesFullQuery = $queryFor73->where('services.id', 73)->exists();

            return response()->json([
                'input' => [
                    'location' => $request->input('location'),
                    'latitude' => $request->input('latitude'),
                    'longitude' => $request->input('longitude'),
                    'radius' => $request->input('radius'),
                    'verified_only' => $request->input('verified_only'),
                    'available_now' => $request->input('available_now'),
                    'reservable' => $request->input('reservable'),
                    'service_date' => $request->input('service_date'),
                    'service_time' => $request->input('service_time'),
                    'category' => $request->input('category'),
                    'main_category' => $request->input('main_category'),
                    'sub_category' => $request->input('sub_category'),
                ],
                'parsed_location' => $loc,
                'service_73' => $serviceData,
                'service_73_eval' => $eval,
                'service_73_passes_full_query' => $passesFullQuery,
                'results_total' => (clone $query)->count(),
            ]);
        }

        // Si coordonnées GPS disponibles, calculer la distance pour chaque service
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $lat = (float) $request->latitude;
            $lon = (float) $request->longitude;

            // Ajouter le calcul de distance avec priorité: service > prestataire
            $query->selectRaw("
                services.*,
                CASE 
                    WHEN services.latitude IS NOT NULL AND services.longitude IS NOT NULL THEN
                        ROUND(6371 * acos(
                            LEAST(1.0, GREATEST(-1.0,
                                cos(radians(?)) * cos(radians(services.latitude)) * cos(radians(services.longitude) - radians(?)) +
                                sin(radians(?)) * sin(radians(services.latitude))
                            ))
                        ), 1)
                    ELSE NULL
                END as distance_km
            ", [$lat, $lon, $lat]);

            // Tri par distance si demandé
            if ($request->input('sort') === 'distance') {
                $query->orderByRaw('distance_km IS NULL, distance_km ASC');
            }
        }

        $services = $query->paginate(12)->withQueryString();

        // Si GPS utilisé, ajouter la distance du prestataire pour les services sans coordonnées propres
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $lat = (float) $request->latitude;
            $lon = (float) $request->longitude;

            $services->getCollection()->transform(function ($service) use ($lat, $lon) {
                if ($service->distance_km === null && $service->prestataire) {
                    $pLat = $service->prestataire->latitude;
                    $pLon = $service->prestataire->longitude;
                    if ($pLat && $pLon) {
                        // Formule Haversine en PHP
                        $earthRadius = 6371;
                        $dLat = deg2rad($pLat - $lat);
                        $dLon = deg2rad($pLon - $lon);
                        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat)) * cos(deg2rad($pLat)) * sin($dLon / 2) * sin($dLon / 2);
                        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                        $service->distance_km = round($earthRadius * $c, 1);
                    }
                }
                return $service;
            });
        }

        $categories = Category::ofTypeService()->with('children')->whereNull('parent_id')->orderBy('name')->get();

        return view('services.index-modern', compact('services', 'categories'));
    }

    /**
     * Affiche la liste des services du prestataire connecté.
     *
     * @return \Illuminate\View\View
     */
    public function prestataireServices()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return redirect()->route('home')->with('error', 'Accès non autorisé.');
        }

        $services = Service::where('prestataire_id', $prestataire->id)
            ->with(['categories'])
            ->latest()
            ->get();

        return view('prestataire.services.index', compact('services'));
    }

    /**
     * Affiche le formulaire de création d'un nouveau service.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = Category::ofTypeService()->whereNull('parent_id')->with('children')->orderBy('name')->get();
        return view('prestataire.services.create', compact('categories'));
    }

    /**
     * Enregistre un nouveau service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return redirect()->route('home')->with('error', 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'deposit_percentage' => 'nullable|numeric|min:0|max:100',
            'duration' => 'nullable|integer|min:1',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id'
        ]);

        $service = new Service();
        $service->title = $validated['title'];
        $service->description = $validated['description'];
        $service->price = $validated['price'];
        $service->deposit_percentage = $validated['deposit_percentage'] ?? 0;
        $service->duration = $validated['duration'];
        $service->prestataire_id = $prestataire->id;
        $service->status = 'active';
        $service->save();

        if (isset($validated['categories'])) {
            $service->categories()->sync($validated['categories']);
        }

        return redirect()->route('prestataire.services.index')
            ->with('success', 'Service créé avec succès.');
    }

    /**
     * Affiche un service spécifique.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\View\View
     */
    public function show(Service $service)
    {
        $service->load(['prestataire.user', 'categories', 'reviews.client', 'images']);

        // Cohérence avec la liste: masquer les services non approuvés / non visibles
        // (sauf admin ou prestataire propriétaire)
        $user = Auth::user();
        $userRole = $user->role ?? null;
        $isAdmin = ($userRole === 'admin');
        $isOwner = ($user?->prestataire && (int) $user->prestataire->id === (int) $service->prestataire_id);

        $prestataireApproved = (bool) ($service->prestataire?->is_approved);
        $serviceVisible = (bool) ($service->is_visible ?? true);
        if ((!$prestataireApproved || !$serviceVisible) && !$isAdmin && !$isOwner) {
            abort(404);
        }

        // Incrémenter le compteur de vues
        $service->increment('views');

        // Récupérer les services similaires de la même catégorie
        $categoryIds = $service->categories->pluck('id');

        $similarServices = Service::whereHas('categories', function ($query) use ($categoryIds) {
            $query->whereIn('categories.id', $categoryIds);
        })
            ->where('id', '!=', $service->id)
            ->where('is_visible', true)
            ->latest()
            ->take(4)
            ->with(['prestataire.user', 'coverImage', 'reviews'])
            ->get();

        // Calculer la note moyenne
        $averageRating = $service->reviews->avg('rating');
        $totalReviews = $service->reviews->count();

        return view('services.show', compact('service', 'similarServices', 'averageRating', 'totalReviews'));
    }

    /**
     * Affiche le formulaire d'édition d'un service.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\View\View
     */
    public function edit(Service $service)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire || $service->prestataire_id !== $prestataire->id) {
            return redirect()->route('prestataire.services.index')
                ->with('error', 'Accès non autorisé.');
        }

        $categories = Category::ofTypeService()->whereNull('parent_id')->with('children')->orderBy('name')->get();
        return view('prestataire.services.edit', compact('service', 'categories'));
    }

    /**
     * Met à jour un service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Service $service)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire || $service->prestataire_id !== $prestataire->id) {
            return redirect()->route('prestataire.services.index')
                ->with('error', 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'deposit_percentage' => 'nullable|numeric|min:0|max:100',
            'duration' => 'nullable|integer|min:1',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id'
        ]);

        $service->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'deposit_percentage' => $validated['deposit_percentage'] ?? 0,
            'duration' => $validated['duration']
        ]);

        if (isset($validated['categories'])) {
            $service->categories()->sync($validated['categories']);
        }

        return redirect()->route('prestataire.services.index')
            ->with('success', 'Service mis à jour avec succès.');
    }

    /**
     * Supprime un service.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Service $service)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (!$prestataire || $service->prestataire_id !== $prestataire->id) {
            return redirect()->route('prestataire.services.index')
                ->with('error', 'Accès non autorisé.');
        }

        $service->delete();

        return redirect()->route('prestataire.services.index')
            ->with('success', 'Service supprimé avec succès.');
    }

    /**
     * Soumettre un signalement pour un service
     */
    public function submitReport(Request $request, Service $service)
    {
        $request->validate([
            'category' => 'required|in:inappropriate_content,fraud,misleading_info,poor_service,pricing_issue,unavailable,spam,copyright,other',
            'reason' => 'required|string|max:255',
            'description' => 'required|string|min:10|max:1000',
            'evidence_photos' => 'nullable|array|max:3',
            'evidence_photos.*' => 'image|mimes:jpeg,png,jpg'
        ]);

        $reportData = [
            'service_id' => $service->id,
            'reason' => $request->reason,
            'category' => $request->category,
            'description' => $request->description,
            'reporter_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'priority' => 'medium'
        ];

        // Gérer l'utilisateur connecté
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->client) {
                $reportData['reporter_id'] = $user->client->id;
                $reportData['reporter_type'] = 'client';
            } elseif ($user->prestataire) {
                $reportData['reporter_id'] = $user->prestataire->id;
                $reportData['reporter_type'] = 'prestataire';
            }
        } else {
            $reportData['reporter_type'] = 'anonymous';
        }

        // Gérer les photos de preuve
        if ($request->hasFile('evidence_photos')) {
            $photos = [];
            foreach ($request->file('evidence_photos') as $photo) {
                $path = $photo->store('service-reports', 'public');
                $photos[] = $path;
            }
            $reportData['evidence_photos'] = $photos;
        }

        \App\Models\ServiceReport::create($reportData);

        return response()->json([
            'success' => true,
            'message' => 'Votre signalement a été soumis avec succès. Nous examinerons votre demande dans les plus brefs délais.'
        ]);
    }
}
