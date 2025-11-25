<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Prestataire;
use App\Models\Client;
use App\Models\User;

class GeolocationController extends Controller
{
    /**
     * Met à jour la localisation de l'utilisateur connecté.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $locationData = [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            'city' => $request->city,
            'region' => $request->region,
            'country' => $request->country,
            'location_updated_at' => now()
        ];

        try {
            // Mettre à jour selon le type d'utilisateur
            if ($user->role === 'prestataire' && $user->prestataire) {
                $user->prestataire->update($locationData);
            } elseif ($user->role === 'client' && $user->client) {
                $user->client->update($locationData);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Profil utilisateur non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Localisation mise à jour avec succès',
                'data' => $locationData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la localisation'
            ], 500);
        }
    }

    /**
     * Récupère les prestataires à proximité.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNearbyPrestataires(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:100', // en kilomètres
            'service_id' => 'nullable|exists:services,id',
            'limit' => 'nullable|integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $request->radius ?? 10; // 10km par défaut
        $serviceId = $request->service_id;
        $limit = $request->limit ?? 20;

        try {
            $query = Prestataire::select([
                'prestataires.*',
                DB::raw("(
                    6371 * acos(
                        cos(radians($latitude)) * 
                        cos(radians(latitude)) * 
                        cos(radians(longitude) - radians($longitude)) + 
                        sin(radians($latitude)) * 
                        sin(radians(latitude))
                    )
                ) AS distance")
            ])
            ->with(['user', 'services'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('is_visible', true)
            ->having('distance', '<=', $radius)
            ->orderBy('distance', 'asc');

            // Filtrer par service si spécifié
            if ($serviceId) {
                $query->whereHas('services', function ($q) use ($serviceId) {
                    $q->where('services.id', $serviceId);
                });
            }

            $prestataires = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => $prestataires->map(function ($prestataire) {
                    return [
                        'id' => $prestataire->id,
                        'name' => $prestataire->user->name,
                        'company_name' => $prestataire->company_name,
                        'description' => $prestataire->description,
                        'avatar' => $prestataire->avatar_url,
                        'rating' => $prestataire->average_rating,
                        'reviews_count' => $prestataire->reviews_count,
                        'services' => $prestataire->services->pluck('name'),
                        'location' => [
                            'latitude' => $prestataire->latitude,
                            'longitude' => $prestataire->longitude,
                            'address' => $prestataire->address,
                            'city' => $prestataire->city,
                            'region' => $prestataire->region
                        ],
                        'distance' => round($prestataire->distance, 2),
                        'profile_url' => route('prestataires.show', $prestataire->id)
                    ];
                }),
                'meta' => [
                    'total' => $prestataires->count(),
                    'radius' => $radius,
                    'center' => [
                        'latitude' => $latitude,
                        'longitude' => $longitude
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche des prestataires à proximité'
            ], 500);
        }
    }

    /**
     * Calcule la distance entre l'utilisateur et un prestataire.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Prestataire  $prestataire
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDistance(Request $request, Prestataire $prestataire)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$prestataire->latitude || !$prestataire->longitude) {
            return response()->json([
                'success' => false,
                'message' => 'Localisation du prestataire non disponible'
            ], 404);
        }

        $userLat = $request->latitude;
        $userLng = $request->longitude;
        $prestLat = $prestataire->latitude;
        $prestLng = $prestataire->longitude;

        // Formule de Haversine pour calculer la distance
        $earthRadius = 6371; // Rayon de la Terre en kilomètres
        
        $latDelta = deg2rad($prestLat - $userLat);
        $lngDelta = deg2rad($prestLng - $userLng);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($userLat)) * cos(deg2rad($prestLat)) *
             sin($lngDelta / 2) * sin($lngDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return response()->json([
            'success' => true,
            'data' => [
                'distance_km' => round($distance, 2),
                'distance_miles' => round($distance * 0.621371, 2),
                'prestataire' => [
                    'id' => $prestataire->id,
                    'name' => $prestataire->user->name,
                    'location' => [
                        'latitude' => $prestataire->latitude,
                        'longitude' => $prestataire->longitude,
                        'address' => $prestataire->address,
                        'city' => $prestataire->city
                    ]
                ]
            ]
        ]);
    }

    /**
     * Récupère la liste des villes et codes postaux disponibles dans le monde.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCities(Request $request)
    {
        $search = $request->get('search', '');
        $limit = min($request->get('limit', 10), 50); // Max 50 results

        if (strlen($search) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Au moins 2 caractères requis'
            ]);
        }

        try {
            // First, try to get suggestions from our database
            $localResults = $this->getLocalSuggestions($search, $limit);
            
            // Then get worldwide suggestions using Nominatim API
            $worldwideResults = $this->getWorldwideSuggestions($search, $limit - count($localResults));
            
            // Combine and format results
            $allResults = array_merge($localResults, $worldwideResults);
            
            // Remove duplicates and limit results
            $uniqueResults = $this->removeDuplicates($allResults);
            $finalResults = array_slice($uniqueResults, 0, $limit);

            return response()->json([
                'success' => true,
                'data' => $finalResults
            ]);
        } catch (\Exception $e) {
            // Fallback to local suggestions only if external API fails
            $localResults = $this->getLocalSuggestions($search, $limit);
            
            return response()->json([
                'success' => true,
                'data' => $localResults,
                'warning' => 'Suggestions mondiales temporairement indisponibles'
            ]);
        }
    }
    
    /**
     * Récupère les suggestions depuis la base de données locale.
     *
     * @param string $search
     * @param int $limit
     * @return array
     */
    private function getLocalSuggestions(string $search, int $limit): array
    {
        try {
            $suggestions = [];
            
            // Search cities in prestataires table
            $cities = DB::table('prestataires')
                ->select('city', 'postal_code', 'country')
                ->whereNotNull('city')
                ->where('city', '!=', '')
                ->where(function($query) use ($search) {
                    $query->where('city', 'LIKE', '%' . $search . '%')
                          ->orWhere('postal_code', 'LIKE', $search . '%');
                })
                ->distinct()
                ->limit($limit)
                ->get();

            foreach ($cities as $city) {
                $displayText = $city->city;
                if ($city->postal_code) {
                    $displayText .= ' (' . $city->postal_code . ')';
                }
                if ($city->country && $city->country !== 'France') {
                    $displayText .= ', ' . $city->country;
                }
                
                $suggestions[] = [
                    'text' => $displayText,
                    'city' => $city->city,
                    'postal_code' => $city->postal_code,
                    'country' => $city->country ?? 'France',
                    'source' => 'local'
                ];
            }
            
            return $suggestions;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Récupère les suggestions depuis l'API mondiale Nominatim.
     *
     * @param string $search
     * @param int $limit
     * @return array
     */
    private function getWorldwideSuggestions(string $search, int $limit): array
    {
        if ($limit <= 0) {
            return [];
        }
        
        try {
            // Use Nominatim API for worldwide geocoding
            $url = 'https://nominatim.openstreetmap.org/search?'
                 . http_build_query([
                     'q' => $search,
                     'format' => 'json',
                     'addressdetails' => 1,
                     'limit' => $limit,
                     'accept-language' => 'fr,en',
                     'countrycodes' => '', // No restriction - worldwide
                     'featuretype' => 'city'
                 ]);
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'TaPrestation/1.0 (Location Autocomplete)'
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                return [];
            }
            
            $data = json_decode($response, true);
            
            if (!$data || !is_array($data)) {
                return [];
            }
            
            $suggestions = [];
            
            foreach ($data as $item) {
                if (!isset($item['display_name']) || !isset($item['address'])) {
                    continue;
                }
                
                $address = $item['address'];
                
                // Extract city information
                $city = $address['city'] 
                     ?? $address['town'] 
                     ?? $address['municipality'] 
                     ?? $address['village']
                     ?? $address['hamlet']
                     ?? null;
                
                if (!$city) {
                    continue;
                }
                
                $country = $address['country'] ?? '';
                $postcode = $address['postcode'] ?? '';
                $state = $address['state'] ?? '';
                
                // Format display text
                $displayText = $city;
                
                if ($postcode) {
                    $displayText .= ' (' . $postcode . ')';
                }
                
                if ($state && $country !== 'France') {
                    $displayText .= ', ' . $state;
                }
                
                if ($country) {
                    $displayText .= ', ' . $country;
                }
                
                $suggestions[] = [
                    'text' => $displayText,
                    'city' => $city,
                    'postal_code' => $postcode,
                    'country' => $country,
                    'state' => $state,
                    'source' => 'worldwide',
                    'lat' => $item['lat'] ?? null,
                    'lon' => $item['lon'] ?? null
                ];
            }
            
            return $suggestions;
            
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Supprime les doublons des suggestions.
     *
     * @param array $suggestions
     * @return array
     */
    private function removeDuplicates(array $suggestions): array
    {
        $seen = [];
        $unique = [];
        
        foreach ($suggestions as $suggestion) {
            $key = strtolower($suggestion['city'] . '|' . ($suggestion['postal_code'] ?? '') . '|' . ($suggestion['country'] ?? ''));
            
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $suggestion;
            }
        }
        
        return $unique;
    }

    /**
     * Récupère la liste des régions disponibles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRegions(Request $request)
    {
        $search = $request->get('search', '');

        try {
            $query = DB::table('prestataires')
                ->select('region')
                ->whereNotNull('region')
                ->where('region', '!=', '')
                ->distinct();

            if ($search) {
                $query->where('region', 'LIKE', '%' . $search . '%');
            }

            $regions = $query->orderBy('region')
                ->limit(50)
                ->pluck('region')
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => $regions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des régions'
            ], 500);
        }
    }
}