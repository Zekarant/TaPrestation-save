<?php

namespace App\Services;

use App\Models\FoodOrder;
use App\Models\DeliveryDriver;
use App\Models\DeliveryBatch;
use App\Models\DeliveryBatchOrder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryMatchingService
{
    protected DeliveryPricingService $pricingService;

    public function __construct(DeliveryPricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Créer ou ajouter à un batch pour une commande
     */
    public function createOrAddToBatch(FoodOrder $order): DeliveryBatch
    {
        $config = config('delivery.batch');

        // Chercher un batch compatible existant
        $compatibleBatch = $this->findCompatibleBatch($order);

        if ($compatibleBatch) {
            $this->addOrderToBatch($compatibleBatch, $order);
            return $compatibleBatch->fresh();
        }

        // Créer un nouveau batch
        return $this->createBatchForOrder($order);
    }

    /**
     * Trouver un batch compatible pour grouper
     */
    protected function findCompatibleBatch(FoodOrder $order): ?DeliveryBatch
    {
        $config = config('delivery.batch');

        if (!$config['enabled']) {
            return null;
        }

        $timeWindow = $config['time_window'];
        $maxClientDistance = $config['max_client_distance'];
        $maxPickupDistance = $config['max_pickup_distance'];

        // Chercher les batches disponibles créés récemment
        /** @var \Illuminate\Database\Eloquent\Collection<int, DeliveryBatch> $candidates */
        $candidates = DeliveryBatch::where('status', DeliveryBatch::STATUS_AVAILABLE)
            ->where('created_at', '>=', now()->subMinutes($timeWindow))
            ->withCount('orders')
            ->having('orders_count', '<', $config['max_orders'])
            ->get();

        foreach ($candidates as $batch) {
            if ($this->isBatchCompatible($batch, $order, $maxPickupDistance, $maxClientDistance)) {
                return $batch;
            }
        }

        return null;
    }

    /**
     * Vérifier si un batch est compatible avec une commande
     */
    protected function isBatchCompatible(DeliveryBatch $batch, FoodOrder $order, float $maxPickupDist, float $maxClientDist): bool
    {
        $batchOrders = $batch->orders()->with(['prestataire', 'foodOrder'])->get();

        if ($batchOrders->isEmpty()) {
            return true;
        }

        // Vérifier la distance entre les restaurants
        foreach ($batchOrders as $batchOrder) {
            $restaurantDist = $this->pricingService->calculateDistance(
                $batchOrder->prestataire->latitude ?? 0,
                $batchOrder->prestataire->longitude ?? 0,
                $order->prestataire->latitude ?? 0,
                $order->prestataire->longitude ?? 0
            );

            if ($restaurantDist > $maxPickupDist) {
                return false;
            }
        }

        // Vérifier la distance entre les clients
        foreach ($batchOrders as $batchOrder) {
            $clientDist = $this->pricingService->calculateDistance(
                $batchOrder->delivery_lat ?? 0,
                $batchOrder->delivery_lng ?? 0,
                $order->delivery_lat ?? 0,
                $order->delivery_lng ?? 0
            );

            if ($clientDist > $maxClientDist) {
                return false;
            }
        }

        return true;
    }

    /**
     * Créer un nouveau batch pour une commande
     */
    protected function createBatchForOrder(FoodOrder $order): DeliveryBatch
    {
        $batch = DeliveryBatch::create([
            'status' => DeliveryBatch::STATUS_AVAILABLE,
            'first_pickup_lat' => $order->prestataire->latitude,
            'first_pickup_lng' => $order->prestataire->longitude,
            'last_dropoff_lat' => $order->delivery_lat,
            'last_dropoff_lng' => $order->delivery_lng,
        ]);

        $this->addOrderToBatch($batch, $order);

        return $batch;
    }

    /**
     * Ajouter une commande à un batch existant
     */
    protected function addOrderToBatch(DeliveryBatch $batch, FoodOrder $order): void
    {
        $existingOrders = $batch->batchOrders()->count();

        // Calculer les distances
        $pickupDistance = 0;
        $dropoffDistance = 0;

        $restaurantLat = $order->prestataire->latitude ?? 0;
        $restaurantLng = $order->prestataire->longitude ?? 0;
        $clientLat = $order->delivery_lat ?? 0;
        $clientLng = $order->delivery_lng ?? 0;

        // Distance pickup (depuis le dernier point ou le premier restaurant)
        if ($existingOrders > 0) {
            $lastDropoff = $batch->batchOrders()->orderBy('delivery_order', 'desc')->first();
            if ($lastDropoff && $lastDropoff->foodOrder) {
                $pickupDistance = $this->pricingService->calculateDistance(
                    $lastDropoff->foodOrder->delivery_lat ?? 0,
                    $lastDropoff->foodOrder->delivery_lng ?? 0,
                    $restaurantLat,
                    $restaurantLng
                );
            }
        }

        // Distance dropoff (restaurant -> client)
        $route = $this->pricingService->getRouteDistance($restaurantLat, $restaurantLng, $clientLat, $clientLng);
        $dropoffDistance = $route['distance'] ?? 0;

        DeliveryBatchOrder::create([
            'batch_id' => $batch->id,
            'food_order_id' => $order->id,
            'pickup_order' => $existingOrders + 1,
            'delivery_order' => $existingOrders + 1,
            'distance_to_pickup' => $pickupDistance,
            'distance_to_dropoff' => $dropoffDistance,
        ]);

        // Recalculer les totaux du batch
        $this->recalculateBatchTotals($batch);
    }

    /**
     * Recalculer les totaux d'un batch
     */
    public function recalculateBatchTotals(DeliveryBatch $batch): void
    {
        $batch->load('batchOrders');

        $totalDistance = 0;
        $totalTime = 0;

        foreach ($batch->batchOrders as $batchOrder) {
            $totalDistance += ($batchOrder->distance_to_pickup ?? 0) + ($batchOrder->distance_to_dropoff ?? 0);
            $totalTime += $this->pricingService->estimateTime($batchOrder->distance_to_dropoff ?? 0);
        }

        // Ajouter du temps pour chaque pickup/dropoff
        $orderCount = $batch->batchOrders->count();
        $totalTime += $orderCount * 5; // 5 min par arrêt

        $earnings = $this->pricingService->calculateDriverEarnings(
            $batch->batchOrders->sum('distance_to_pickup'),
            $batch->batchOrders->sum('distance_to_dropoff'),
            $orderCount
        );

        $batch->update([
            'total_distance' => round($totalDistance, 2),
            'total_time' => $totalTime,
            'driver_earnings' => $earnings['net'],
            'platform_fee' => $earnings['platform_fee'],
        ]);
    }

    /**
     * Trouver les livreurs disponibles près d'une position
     */
    public function findAvailableDrivers(float $lat, float $lng, ?float $radiusKm = null): Collection
    {
        $radiusKm = $radiusKm ?? config('delivery.matching.search_radius', 5.0);
        $staleThreshold = config('delivery.gps.stale_threshold', 60);
        $maxConcurrent = config('delivery.matching.max_concurrent_orders', 3);

        return DeliveryDriver::where('status', 'available')
            ->where('is_active', true)
            ->whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->where('location_updated_at', '>=', now()->subSeconds($staleThreshold))
            ->get()
            ->filter(function ($driver) use ($lat, $lng, $radiusKm, $maxConcurrent) {
                // Vérifier la distance
                $distance = $this->pricingService->calculateDistance(
                    (float) $driver->current_lat,
                    (float) $driver->current_lng,
                    $lat,
                    $lng
                );

                if ($distance > $radiusKm) {
                    return false;
                }

                // Vérifier les commandes en cours
                $activeOrders = $driver->activeFoodOrders()->count();
                if ($activeOrders >= $maxConcurrent) {
                    return false;
                }

                return true;
            })
            ->map(function ($driver) use ($lat, $lng) {
                $driver->distance_to_pickup = $this->pricingService->calculateDistance(
                    (float) $driver->current_lat,
                    (float) $driver->current_lng,
                    $lat,
                    $lng
                );
                return $driver;
            })
            ->sortBy('distance_to_pickup');
    }

    /**
     * Compter les livreurs disponibles dans une zone
     */
    public function countAvailableDrivers(float $lat, float $lng, ?float $radiusKm = null): int
    {
        return $this->findAvailableDrivers($lat, $lng, $radiusKm)->count();
    }

    /**
     * Obtenir les batches disponibles pour un livreur
     */
    public function getAvailableBatchesForDriver(DeliveryDriver $driver): Collection
    {
        if (!$driver->current_lat || !$driver->current_lng) {
            return collect();
        }

        $radiusKm = config('delivery.matching.search_radius', 5.0);

        $batches = DeliveryBatch::where('status', DeliveryBatch::STATUS_AVAILABLE)
            ->with(['orders.prestataire', 'batchOrders'])
            ->get();

        return $batches->filter(function ($batch) use ($driver, $radiusKm) {
            if (!$batch->first_pickup_lat || !$batch->first_pickup_lng) {
                return false;
            }

            $distance = $this->pricingService->calculateDistance(
                (float) $driver->current_lat,
                (float) $driver->current_lng,
                (float) $batch->first_pickup_lat,
                (float) $batch->first_pickup_lng
            );

            return $distance <= $radiusKm;
        })->map(function ($batch) use ($driver) {
            // Ajouter les infos pour le livreur
            $pickupDistance = $this->pricingService->calculateDistance(
                (float) $driver->current_lat,
                (float) $driver->current_lng,
                (float) $batch->first_pickup_lat,
                (float) $batch->first_pickup_lng
            );

            $batch->distance_to_first_pickup = $pickupDistance;
            $batch->time_to_first_pickup = $this->pricingService->estimateTime($pickupDistance, $driver->vehicle_type ?? 'scooter');

            return $batch;
        })->sortBy('distance_to_first_pickup');
    }
}
