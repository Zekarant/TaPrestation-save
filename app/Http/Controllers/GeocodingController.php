<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GeocodingController extends Controller
{
    /**
     * Géocodage simple (adresse vers coordonnées)
     */
    public function geocode(Request $request): JsonResponse
    {
        $address = strtolower(trim($request->get('address', '')));
        
        if (empty($address)) {
            return response()->json(['success' => false, 'message' => 'Adresse manquante']);
        }
        
        // Base de données des villes françaises et marocaines
        $cities = $this->getCitiesDatabase();
        
        // Rechercher la ville dans l'adresse
        foreach ($cities as $city => $coords) {
            if (strpos($address, $city) !== false) {
                return response()->json([
                    'success' => true,
                    'latitude' => $coords['latitude'],
                    'longitude' => $coords['longitude'],
                    'city' => ucfirst(str_replace('-', ' ', $city)),
                    'country' => $coords['country']
                ]);
            }
        }
        
        return response()->json(['success' => false, 'message' => 'Ville non trouvée']);
    }
    
    /**
     * Géocodage inverse (coordonnées vers adresse)
     */
    public function reverseGeocode(Request $request): JsonResponse
    {
        $lat = $request->get('lat');
        $lng = $request->get('lng');
        
        if (!$lat || !$lng) {
            return response()->json(['success' => false, 'message' => 'Coordonnées manquantes']);
        }
        
        $cities = $this->getCitiesDatabase();
        
        $userLat = floatval($lat);
        $userLng = floatval($lng);
        $closestCity = null;
        $minDistance = PHP_FLOAT_MAX;
        
        // Trouver la ville la plus proche
        foreach ($cities as $city => $coords) {
            $distance = sqrt(pow($userLat - $coords['latitude'], 2) + pow($userLng - $coords['longitude'], 2));
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestCity = $city;
            }
        }
        
        if ($closestCity && $minDistance < 0.5) {
            $cityData = $cities[$closestCity];
            $cityName = ucfirst(str_replace('-', ' ', $closestCity));
            return response()->json([
                'success' => true,
                'address' => $cityName . ', ' . $cityData['country'],
                'city' => $cityName,
                'country' => $cityData['country']
            ]);
        }
        
        return response()->json(['success' => false, 'message' => 'Adresse non trouvée']);
    }
    
    /**
     * Base de données des villes
     */
    private function getCitiesDatabase(): array
    {
        return [
            // Villes françaises
            'paris' => ['latitude' => 48.8566, 'longitude' => 2.3522, 'country' => 'France'],
            'marseille' => ['latitude' => 43.2965, 'longitude' => 5.3698, 'country' => 'France'],
            'lyon' => ['latitude' => 45.7640, 'longitude' => 4.8357, 'country' => 'France'],
            'toulouse' => ['latitude' => 43.6047, 'longitude' => 1.4442, 'country' => 'France'],
            'nice' => ['latitude' => 43.7102, 'longitude' => 7.2620, 'country' => 'France'],
            'nantes' => ['latitude' => 47.2184, 'longitude' => -1.5536, 'country' => 'France'],
            'montpellier' => ['latitude' => 43.6110, 'longitude' => 3.8767, 'country' => 'France'],
            'strasbourg' => ['latitude' => 48.5734, 'longitude' => 7.7521, 'country' => 'France'],
            'bordeaux' => ['latitude' => 44.8378, 'longitude' => -0.5792, 'country' => 'France'],
            'lille' => ['latitude' => 50.6292, 'longitude' => 3.0573, 'country' => 'France'],
            'rennes' => ['latitude' => 48.1173, 'longitude' => -1.6778, 'country' => 'France'],
            'reims' => ['latitude' => 49.2583, 'longitude' => 4.0317, 'country' => 'France'],
            'saint-étienne' => ['latitude' => 45.4397, 'longitude' => 4.3872, 'country' => 'France'],
            'toulon' => ['latitude' => 43.1242, 'longitude' => 5.9280, 'country' => 'France'],
            'grenoble' => ['latitude' => 45.1885, 'longitude' => 5.7245, 'country' => 'France'],
            'dijon' => ['latitude' => 47.3220, 'longitude' => 5.0415, 'country' => 'France'],
            'angers' => ['latitude' => 47.4784, 'longitude' => -0.5632, 'country' => 'France'],
            'nîmes' => ['latitude' => 43.8367, 'longitude' => 4.3601, 'country' => 'France'],
            'villeurbanne' => ['latitude' => 45.7665, 'longitude' => 4.8795, 'country' => 'France'],
            'clermont-ferrand' => ['latitude' => 45.7797, 'longitude' => 3.0863, 'country' => 'France'],
            // Villes marocaines
            'casablanca' => ['latitude' => 33.5731, 'longitude' => -7.5898, 'country' => 'Maroc'],
            'rabat' => ['latitude' => 34.0209, 'longitude' => -6.8416, 'country' => 'Maroc'],
            'fès' => ['latitude' => 34.0181, 'longitude' => -5.0078, 'country' => 'Maroc'],
            'marrakech' => ['latitude' => 31.6295, 'longitude' => -7.9811, 'country' => 'Maroc'],
            'agadir' => ['latitude' => 30.4278, 'longitude' => -9.5981, 'country' => 'Maroc'],
            'tanger' => ['latitude' => 35.7595, 'longitude' => -5.8340, 'country' => 'Maroc'],
            'meknès' => ['latitude' => 33.8935, 'longitude' => -5.5473, 'country' => 'Maroc'],
            'oujda' => ['latitude' => 34.6814, 'longitude' => -1.9086, 'country' => 'Maroc'],
            'kenitra' => ['latitude' => 34.2610, 'longitude' => -6.5802, 'country' => 'Maroc'],
            'tétouan' => ['latitude' => 35.5889, 'longitude' => -5.3626, 'country' => 'Maroc'],
            'safi' => ['latitude' => 32.2994, 'longitude' => -9.2372, 'country' => 'Maroc'],
            'mohammedia' => ['latitude' => 33.6863, 'longitude' => -7.3830, 'country' => 'Maroc'],
            'khouribga' => ['latitude' => 32.8811, 'longitude' => -6.9063, 'country' => 'Maroc'],
            'beni-mellal' => ['latitude' => 32.3373, 'longitude' => -6.3498, 'country' => 'Maroc'],
            'el-jadida' => ['latitude' => 33.2316, 'longitude' => -8.5007, 'country' => 'Maroc']
        ];
    }
}