<?php

namespace App\Services;

use App\Models\FoodOrder;
use App\Models\DeliveryDriver;
use App\Models\DeliveryBatch;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DeliveryPricingService
{
    /**
     * Calculer les frais de livraison pour le CLIENT
     */
    public function calculateClientFee(float $distanceKm, float $orderTotal): float
    {
        $config = config('delivery.client');
        
        // Livraison gratuite au-dessus d'un certain montant
        if ($orderTotal >= $config['free_delivery_above']) {
            return 0;
        }
        
        $fee = $config['base_fee'] + ($distanceKm * $config['per_km']);
        
        // Appliquer les limites
        $fee = max($config['min_fee'], min($config['max_fee'], $fee));
        
        // Appliquer le surge pricing
        $fee *= $this->getSurgeMultiplier();
        
        return round($fee, 2);
    }

    /**
     * Calculer les gains du LIVREUR pour une course
     * 
     * @param float $pickupDistanceKm Distance livreur -> restaurant
     * @param float $dropoffDistanceKm Distance restaurant -> client
     * @param int $orderCount Nombre de commandes dans le batch
     */
    public function calculateDriverEarnings(float $pickupDistanceKm, float $dropoffDistanceKm, int $orderCount = 1): array
    {
        $config = config('delivery.driver');
        $platformFee = config('delivery.platform_fee', 0.05);
        $batchConfig = config('delivery.batch');
        
        // Calcul de base
        $base = $config['base_fee'];
        $pickupEarning = $pickupDistanceKm * $config['pickup_per_km'];
        $dropoffEarning = $dropoffDistanceKm * $config['dropoff_per_km'];
        
        $subtotal = $base + $pickupEarning + $dropoffEarning;
        
        // Bonus pour multi-commandes
        $batchBonus = 0;
        if ($orderCount > 1) {
            $batchBonus = ($orderCount - 1) * $batchConfig['bonus_per_extra_order'];
        }
        
        $grossEarnings = $subtotal + $batchBonus;
        
        // Appliquer le surge
        $grossEarnings *= $this->getSurgeMultiplier();
        
        // Appliquer les limites
        $grossEarnings = max($config['min_earning'], min($config['max_earning'] * $orderCount, $grossEarnings));
        
        // Déduire la commission plateforme (0.05€ fixe par livraison)
        $totalPlatformFee = $platformFee * $orderCount;
        $netEarnings = $grossEarnings - $totalPlatformFee;
        
        return [
            'gross' => round($grossEarnings, 2),
            'platform_fee' => round($totalPlatformFee, 2),
            'net' => round($netEarnings, 2),
            'breakdown' => [
                'base' => round($base, 2),
                'pickup' => round($pickupEarning, 2),
                'dropoff' => round($dropoffEarning, 2),
                'batch_bonus' => round($batchBonus, 2),
                'surge_multiplier' => $this->getSurgeMultiplier(),
            ],
        ];
    }

    /**
     * Calculer les gains pour un batch complet
     */
    public function calculateBatchEarnings(DeliveryBatch $batch, ?DeliveryDriver $driver = null): array
    {
        $orders = $batch->orders()->with('prestataire')->get();
        $totalPickupDistance = 0;
        $totalDropoffDistance = 0;
        
        foreach ($batch->batchOrders as $batchOrder) {
            $totalPickupDistance += $batchOrder->distance_to_pickup ?? 0;
            $totalDropoffDistance += $batchOrder->distance_to_dropoff ?? 0;
        }
        
        return $this->calculateDriverEarnings(
            $totalPickupDistance,
            $totalDropoffDistance,
            $orders->count()
        );
    }

    /**
     * Obtenir le multiplicateur de surge actuel
     */
    public function getSurgeMultiplier(): float
    {
        $config = config('delivery.surge');
        
        if (!$config['enabled']) {
            return 1.0;
        }
        
        $currentHour = now()->hour;
        $multiplier = 1.0;
        
        // Vérifier les heures de pointe
        foreach ($config['peak_hours'] as $peak) {
            if ($currentHour >= $peak['start'] && $currentHour < $peak['end']) {
                $multiplier = max($multiplier, $peak['multiplier']);
            }
        }
        
        // TODO: Intégrer API météo pour les conditions
        // TODO: Calculer le ratio demande/livreurs
        
        return $multiplier;
    }

    /**
     * Estimer le temps de trajet en minutes
     */
    public function estimateTime(float $distanceKm, string $vehicleType = 'scooter'): int
    {
        // Vitesses moyennes en ville (km/h)
        $speeds = [
            'bike' => 15,
            'scooter' => 25,
            'car' => 20, // Avec le trafic
        ];
        
        $speed = $speeds[$vehicleType] ?? config('delivery.routing.fallback_speed_kmh', 20);
        
        // Temps en minutes + 2 min par arrêt (stationnement, etc.)
        $travelTime = ($distanceKm / $speed) * 60;
        
        return (int) ceil($travelTime);
    }

    /**
     * Calculer la distance entre deux points (Haversine)
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return round($earthRadius * $c, 2);
    }

    /**
     * Obtenir la distance réelle via l'API de routing
     */
    public function getRouteDistance(float $fromLat, float $fromLng, float $toLat, float $toLng): ?array
    {
        $cacheKey = "route_distance:{$fromLat},{$fromLng}:{$toLat},{$toLng}";
        $cacheDuration = config('delivery.routing.cache_duration', 60);
        
        return Cache::remember($cacheKey, now()->addMinutes($cacheDuration), function () use ($fromLat, $fromLng, $toLat, $toLng) {
            try {
                $apiKey = config('delivery.routing.api_key');
                
                if (!$apiKey) {
                    // Fallback sur Haversine
                    $distance = $this->calculateDistance($fromLat, $fromLng, $toLat, $toLng);
                    return [
                        'distance' => $distance * 1.3, // +30% pour les routes
                        'duration' => $this->estimateTime($distance * 1.3),
                        'source' => 'haversine',
                    ];
                }
                
                $response = Http::timeout(10)->get('https://api.openrouteservice.org/v2/directions/driving-car', [
                    'api_key' => $apiKey,
                    'start' => "{$fromLng},{$fromLat}",
                    'end' => "{$toLng},{$toLat}",
                ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    $segment = $data['features'][0]['properties']['segments'][0] ?? null;
                    
                    if ($segment) {
                        return [
                            'distance' => round($segment['distance'] / 1000, 2), // Convertir m -> km
                            'duration' => (int) ceil($segment['duration'] / 60), // Convertir s -> min
                            'source' => 'openrouteservice',
                        ];
                    }
                }
                
                // Fallback
                $distance = $this->calculateDistance($fromLat, $fromLng, $toLat, $toLng);
                return [
                    'distance' => $distance * 1.3,
                    'duration' => $this->estimateTime($distance * 1.3),
                    'source' => 'haversine_fallback',
                ];
                
            } catch (\Exception $e) {
                Log::error('Route calculation error: ' . $e->getMessage());
                
                $distance = $this->calculateDistance($fromLat, $fromLng, $toLat, $toLng);
                return [
                    'distance' => $distance * 1.3,
                    'duration' => $this->estimateTime($distance * 1.3),
                    'source' => 'error_fallback',
                ];
            }
        });
    }
}
