<?php

namespace App\Services;

use App\Models\DeliveryDriver;
use App\Models\DriverPricing;
use App\Models\FoodOrder;
use App\Models\Prestataire;
use App\Models\PrestataireDriverPreference;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Service intelligent de matching livreur-commande
 * Prend en compte: distance, disponibilité, charge, véhicule, rating, temps de réponse
 * + préférences prestataire (whitelist/blacklist) et mode de livraison
 */
class DriverMatchingService
{
    // Poids des critères de scoring
    const WEIGHT_DISTANCE = 25;      // Proximité au restaurant
    const WEIGHT_AVAILABILITY = 20;  // Statut disponible
    const WEIGHT_WORKLOAD = 15;      // Charge actuelle
    const WEIGHT_RATING = 15;        // Note du livreur
    const WEIGHT_VEHICLE = 10;       // Type de véhicule adapté
    const WEIGHT_PREFERENCE = 15;    // Préférence prestataire (favori/normal)

    // Rayon de recherche max (km)
    const MAX_SEARCH_RADIUS = 10;
    
    // Nombre max de commandes simultanées
    const MAX_CONCURRENT_ORDERS = 3;

    /**
     * Trouver le meilleur livreur pour une commande
     */
    public function findBestDriver(FoodOrder $order): ?DeliveryDriver
    {
        $scoredDrivers = $this->scoreDriversForOrder($order);
        
        if ($scoredDrivers->isEmpty()) {
            return null;
        }

        return $scoredDrivers->first()['driver'];
    }

    /**
     * Trouver les N meilleurs livreurs pour une commande
     */
    public function findTopDrivers(FoodOrder $order, int $limit = 5): Collection
    {
        $scoredDrivers = $this->scoreDriversForOrder($order);
        
        return $scoredDrivers->take($limit)->map(fn($item) => $item['driver']);
    }

    /**
     * Calculer le score de tous les livreurs disponibles pour une commande
     */
    public function scoreDriversForOrder(FoodOrder $order): Collection
    {
        $prestataire = $order->prestataire;
        
        // Position du restaurant
        $restaurantLat = $prestataire->latitude ?? 0;
        $restaurantLng = $prestataire->longitude ?? 0;

        // Vérifier le mode de livraison du prestataire
        $deliveryMode = $prestataire->delivery_mode ?? 'both';
        
        // Si le prestataire fait que des livraisons internes, pas de matching externe
        if ($deliveryMode === 'internal') {
            return collect();
        }

        // Récupérer les préférences (blocked drivers)
        $blockedDriverIds = PrestataireDriverPreference::getBlockedDriverIds($prestataire->id);
        $preferredDriverIds = PrestataireDriverPreference::getPreferredDriverIds($prestataire->id);
        
        // Note minimum requise par le prestataire
        $minRating = $prestataire->min_driver_rating ?? 0;

        // Récupérer les livreurs potentiels
        $driversQuery = DeliveryDriver::where('is_active', true)
            ->where('is_available', true)
            ->whereIn('status', [
                DeliveryDriver::STATUS_AVAILABLE,
                DeliveryDriver::STATUS_BUSY // Peut encore accepter si < max
            ])
            ->whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->where('last_location_update', '>=', now()->subMinutes(30));
        
        // Exclure les livreurs bloqués
        if (!empty($blockedDriverIds)) {
            $driversQuery->whereNotIn('id', $blockedDriverIds);
        }
        
        // Filtrer par note minimum si définie
        if ($minRating > 0) {
            $driversQuery->where('rating', '>=', $minRating);
        }
        
        // Si mode external seulement, exclure les livreurs internes d'autres prestataires
        // mais inclure les livreurs externes et ceux qui travaillent pour ce prestataire
        $driversQuery->where(function($q) use ($prestataire) {
            $q->whereNull('employer_prestataire_id')
              ->orWhere('employer_prestataire_id', $prestataire->id);
        });

        $drivers = $driversQuery->with('pricing')->get();

        $scoredDrivers = collect();

        foreach ($drivers as $driver) {
            // Vérifier charge de travail
            $activeOrdersCount = $driver->activeFoodOrders()->count();
            if ($activeOrdersCount >= self::MAX_CONCURRENT_ORDERS) {
                continue;
            }

            // Calculer distance au restaurant
            $distance = $this->haversineDistance(
                $driver->current_lat,
                $driver->current_lng,
                $restaurantLat,
                $restaurantLng
            );

            // Vérifier le rayon max du livreur
            $driverMaxRadius = $driver->max_distance_km ?? self::MAX_SEARCH_RADIUS;
            if ($distance > min(self::MAX_SEARCH_RADIUS, $driverMaxRadius)) {
                continue;
            }

            // Calculer le score avec préférence
            $isPreferred = in_array($driver->id, $preferredDriverIds);
            $score = $this->calculateDriverScore($driver, $order, $distance, $activeOrdersCount, $isPreferred);

            // Récupérer les tarifs du livreur
            $pricing = $driver->pricing;
            $estimatedFee = $pricing ? $pricing->calculateFee($distance) : null;

            $scoredDrivers->push([
                'driver' => $driver,
                'score' => $score,
                'distance' => $distance,
                'active_orders' => $activeOrdersCount,
                'is_preferred' => $isPreferred,
                'estimated_fee' => $estimatedFee,
            ]);
        }

        // Trier par score décroissant
        return $scoredDrivers->sortByDesc('score')->values();
    }

    /**
     * Calculer le score d'un livreur pour une commande
     */
    protected function calculateDriverScore(
        DeliveryDriver $driver,
        FoodOrder $order,
        float $distance,
        int $activeOrders,
        bool $isPreferred = false
    ): float {
        $score = 0;

        // Score distance (inversé: plus proche = meilleur score)
        $distanceScore = max(0, (self::MAX_SEARCH_RADIUS - $distance) / self::MAX_SEARCH_RADIUS);
        $score += $distanceScore * self::WEIGHT_DISTANCE;

        // Score disponibilité
        $availabilityScore = $driver->status === DeliveryDriver::STATUS_AVAILABLE ? 1 : 0.5;
        $score += $availabilityScore * self::WEIGHT_AVAILABILITY;

        // Score charge de travail (moins de commandes = meilleur)
        $workloadScore = 1 - ($activeOrders / self::MAX_CONCURRENT_ORDERS);
        $score += $workloadScore * self::WEIGHT_WORKLOAD;

        // Score rating
        $ratingScore = ($driver->rating ?? 4) / 5;
        $score += $ratingScore * self::WEIGHT_RATING;

        // Score véhicule adapté
        $vehicleScore = $this->getVehicleSuitabilityScore($driver->vehicle_type, $order);
        $score += $vehicleScore * self::WEIGHT_VEHICLE;

        // Score préférence (bonus pour les livreurs favoris)
        $preferenceScore = $isPreferred ? 1 : 0.5;
        $score += $preferenceScore * self::WEIGHT_PREFERENCE;

        return round($score, 2);
    }

    /**
     * Score de compatibilité véhicule
     */
    protected function getVehicleSuitabilityScore(string $vehicleType, FoodOrder $order): float
    {
        // Pour l'instant, tous véhicules OK pour la food
        // On pourrait ajouter des critères comme taille commande, distance, météo...
        
        $orderTotal = $order->total ?? 0;
        $distance = $order->delivery_distance ?? 0;

        // Vélo: idéal pour petites commandes courte distance
        if ($vehicleType === 'bike') {
            if ($distance <= 3 && $orderTotal <= 50) return 1.0;
            if ($distance <= 5) return 0.7;
            return 0.5;
        }

        // Scooter: bon compromis
        if ($vehicleType === 'scooter') {
            return 0.9;
        }

        // Voiture: bien pour grosses commandes ou mauvais temps
        if ($vehicleType === 'car') {
            if ($orderTotal > 100) return 1.0;
            return 0.8;
        }

        // Utilitaire: pour très grosses commandes
        if ($vehicleType === 'van') {
            if ($orderTotal > 200) return 1.0;
            return 0.6;
        }

        return 0.7;
    }

    /**
     * Calculer distance Haversine entre 2 points GPS
     */
    public function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
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
     * Estimer le temps d'arrivée du livreur au restaurant
     */
    public function estimatePickupTime(DeliveryDriver $driver, Prestataire $prestataire): int
    {
        $distance = $this->haversineDistance(
            $driver->current_lat,
            $driver->current_lng,
            $prestataire->latitude ?? 0,
            $prestataire->longitude ?? 0
        );

        $speeds = [
            'bike' => 12,
            'scooter' => 20,
            'car' => 30,
            'van' => 25,
        ];

        $speed = $speeds[$driver->vehicle_type] ?? 20;
        
        // Temps en minutes + marge
        return (int) ceil(($distance / $speed) * 60) + 3;
    }

    /**
     * Notifier les livreurs disponibles d'une nouvelle commande
     */
    public function notifyAvailableDrivers(FoodOrder $order): int
    {
        $topDrivers = $this->findTopDrivers($order, 10);
        
        $notified = 0;
        
        foreach ($topDrivers as $driver) {
            try {
                if ($driver->user) {
                    $driver->user->notify(new \App\Notifications\NewDeliveryAvailable($order));
                }
                $notified++;
            } catch (\Exception $e) {
                Log::error('Failed to notify driver', [
                    'driver_id' => $driver->id,
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("Notified {$notified} drivers for order {$order->order_number}");
        
        return $notified;
    }

    /**
     * Auto-assigner une commande au meilleur livreur
     */
    public function autoAssignOrder(FoodOrder $order): ?DeliveryDriver
    {
        $bestDriver = $this->findBestDriver($order);
        
        if (!$bestDriver) {
            Log::warning("No driver available for order {$order->order_number}");
            return null;
        }

        // Calculer les infos de livraison
        $distance = $this->haversineDistance(
            $order->prestataire->latitude ?? 0,
            $order->prestataire->longitude ?? 0,
            $order->delivery_lat,
            $order->delivery_lng
        );

        $speeds = ['bike' => 15, 'scooter' => 25, 'car' => 35, 'van' => 30];
        $speed = $speeds[$bestDriver->vehicle_type] ?? 25;
        $estimatedTime = (int) ceil(($distance / $speed) * 60) + 5;

        // Commission: 15% des frais de livraison, min 2€
        $commission = max(2.00, ($order->delivery_fee ?? 3) * 0.85);

        // Assigner la commande
        $order->update([
            'driver_id' => $bestDriver->id,
            'delivery_status' => 'assigned',
            'delivery_distance' => $distance,
            'estimated_delivery_time' => $estimatedTime,
            'driver_commission' => $commission,
        ]);

        // Mettre à jour statut livreur si nécessaire
        if ($bestDriver->activeFoodOrders()->count() >= self::MAX_CONCURRENT_ORDERS) {
            $bestDriver->update(['status' => DeliveryDriver::STATUS_BUSY]);
        }

        Log::info("Auto-assigned order {$order->order_number} to driver {$bestDriver->id}");

        return $bestDriver;
    }

    /**
     * Récupérer les commandes en attente de livreur
     */
    public function getPendingDeliveryOrders(): Collection
    {
        return FoodOrder::where('delivery_type', 'delivery')
            ->where('status', 'ready')
            ->whereNull('driver_id')
            ->with(['prestataire', 'client'])
            ->orderBy('ready_at')
            ->get();
    }

    /**
     * Récupérer les commandes disponibles pour un livreur spécifique
     * Filtre les prestataires qui ont bloqué ce livreur
     */
    public function getAvailableOrdersForDriver(DeliveryDriver $driver): Collection
    {
        $pendingOrders = $this->getPendingDeliveryOrders();
        
        // Récupérer la liste des prestataires qui ont bloqué ce livreur
        $blockedByPrestataires = PrestataireDriverPreference::where('driver_id', $driver->id)
            ->where('status', 'blocked')
            ->pluck('prestataire_id')
            ->toArray();
        
        return $pendingOrders->filter(function($order) use ($driver, $blockedByPrestataires) {
            // Exclure si bloqué par ce prestataire
            if (in_array($order->prestataire_id, $blockedByPrestataires)) {
                return false;
            }
            
            // Vérifier mode de livraison du prestataire
            $deliveryMode = $order->prestataire->delivery_mode ?? 'both';
            if ($deliveryMode === 'internal') {
                // Si le prestataire fait que des livraisons internes, 
                // seuls ses livreurs internes peuvent voir
                if ($driver->employer_prestataire_id !== $order->prestataire_id) {
                    return false;
                }
            }
            
            // Vérifier la note minimum requise
            $minRating = $order->prestataire->min_driver_rating ?? 0;
            if ($minRating > 0 && ($driver->rating ?? 5) < $minRating) {
                return false;
            }
            
            // Calculer distance
            $distance = $this->haversineDistance(
                $driver->current_lat ?? 0,
                $driver->current_lng ?? 0,
                $order->prestataire->latitude ?? 0,
                $order->prestataire->longitude ?? 0
            );
            
            // Vérifier rayon max du livreur
            $driverMaxRadius = $driver->max_distance_km ?? self::MAX_SEARCH_RADIUS;
            
            return $distance <= min(self::MAX_SEARCH_RADIUS, $driverMaxRadius);
        })->map(function($order) use ($driver) {
            $distance = $this->haversineDistance(
                $driver->current_lat ?? 0,
                $driver->current_lng ?? 0,
                $order->prestataire->latitude ?? 0,
                $order->prestataire->longitude ?? 0
            );
            
            $order->distance_to_pickup = $distance;
            $order->estimated_pickup_time = $this->estimatePickupTime($driver, $order->prestataire);
            
            // Vérifier si favori de ce prestataire
            $order->is_preferred_driver = PrestataireDriverPreference::isDriverPreferred(
                $order->prestataire_id, 
                $driver->id
            );
            
            return $order;
        })->sortBy('distance_to_pickup');
    }
}
