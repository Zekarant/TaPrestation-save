<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryDriver;
use App\Models\DriverLocation;
use App\Models\DeliveryBatch;
use App\Services\DeliveryPricingService;
use App\Services\DeliveryMatchingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DriverApiController extends Controller
{
    protected DeliveryPricingService $pricingService;
    protected DeliveryMatchingService $matchingService;

    public function __construct(DeliveryPricingService $pricingService, DeliveryMatchingService $matchingService)
    {
        $this->pricingService = $pricingService;
        $this->matchingService = $matchingService;
    }

    /**
     * Mettre à jour la position du livreur (toutes les 10 secondes)
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
            'speed' => 'nullable|numeric|min:0',
        ]);

        $driver = $this->getDriver();
        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Livreur non trouvé'], 403);
        }

        try {
            DriverLocation::recordPosition($driver->id, [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy,
                'heading' => $request->heading,
                'speed' => $request->speed,
            ]);

            return response()->json([
                'success' => true,
                'location_updated' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Driver location update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Mise à jour de position impossible pour le moment.',
            ], 400);
        }
    }

    /**
     * Obtenir les courses disponibles pour ce livreur
     */
    public function getAvailableDeliveries(Request $request): JsonResponse
    {
        $driver = $this->getDriver();
        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Livreur non trouvé'], 403);
        }

        if (!$driver->current_lat || !$driver->current_lng) {
            return response()->json([
                'success' => false,
                'message' => 'Position GPS requise. Activez la géolocalisation.',
            ], 400);
        }

        $batches = $this->matchingService->getAvailableBatchesForDriver($driver);

        $deliveries = $batches->map(function ($batch) use ($driver) {
            $orders = $batch->orders()->with('prestataire')->get();
            $firstOrder = $orders->first();

            // Calculer les gains
            $earnings = $this->pricingService->calculateBatchEarnings($batch, $driver);

            // Résumé des villes
            $pickupCity = $firstOrder?->prestataire?->ville ?? 'Restaurant';
            $dropoffCities = $orders->pluck('delivery_address')->map(function ($addr) {
                preg_match('/(\d{5})\s+([^,]+)/i', $addr ?? '', $m);
                return $m[2] ?? null;
            })->filter()->unique()->implode(', ') ?: 'Client';

            return [
                'batch_id' => $batch->id,
                'order_count' => $orders->count(),
                'is_batch' => $orders->count() > 1,

                // Distances et temps
                'distance_to_pickup_km' => round($batch->distance_to_first_pickup ?? 0, 1),
                'total_distance_km' => round($batch->total_distance, 1),
                'estimated_time_min' => $batch->total_time,
                'time_to_pickup_min' => $batch->time_to_first_pickup ?? 0,

                // Gains
                'earnings' => $earnings['net'],
                'earnings_formatted' => number_format($earnings['net'], 2) . '€',
                'earnings_breakdown' => $earnings['breakdown'],

                // Résumé villes
                'pickup_city' => $pickupCity,
                'dropoff_cities' => $dropoffCities,
                'summary' => "{$pickupCity} → {$dropoffCities}",

                // Restaurants
                'restaurants' => $orders->map(fn($o) => [
                    'id' => $o->prestataire_id,
                    'name' => $o->prestataire->nom ?? 'Restaurant',
                    'address' => $o->prestataire->adresse ?? '',
                ]),

                // Pour l'affichage
                'created_at' => $batch->created_at->diffForHumans(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'deliveries' => $deliveries,
            'driver_location' => [
                'lat' => $driver->current_lat,
                'lng' => $driver->current_lng,
                'updated_at' => $driver->location_updated_at?->toIso8601String(),
            ],
            'surge_multiplier' => $this->pricingService->getSurgeMultiplier(),
        ]);
    }

    /**
     * Détails d'une course spécifique
     */
    public function getDeliveryDetails(DeliveryBatch $batch): JsonResponse
    {
        $driver = $this->getDriver();
        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Livreur non trouvé'], 403);
        }

        $orders = $batch->orders()->with(['prestataire', 'items'])->get();
        $earnings = $this->pricingService->calculateBatchEarnings($batch, $driver);

        // Calculer la distance du livreur au premier pickup
        $distanceToPickup = 0;
        $timeToPickup = 0;
        if ($driver->current_lat && $driver->current_lng && $batch->first_pickup_lat && $batch->first_pickup_lng) {
            $route = $this->pricingService->getRouteDistance(
                $driver->current_lat,
                $driver->current_lng,
                $batch->first_pickup_lat,
                $batch->first_pickup_lng
            );
            $distanceToPickup = $route['distance'];
            $timeToPickup = $route['duration'];
        }

        return response()->json([
            'success' => true,
            'batch' => [
                'id' => $batch->id,
                'status' => $batch->status,
                'order_count' => $orders->count(),

                // Trajets
                'distance_to_pickup_km' => round($distanceToPickup, 1),
                'time_to_pickup_min' => $timeToPickup,
                'total_distance_km' => round($batch->total_distance, 1),
                'total_time_min' => $batch->total_time,

                // Gains détaillés
                'earnings' => [
                    'gross' => $earnings['gross'],
                    'platform_fee' => $earnings['platform_fee'],
                    'net' => $earnings['net'],
                    'breakdown' => $earnings['breakdown'],
                ],
            ],
            'orders' => $orders->map(function ($order, $index) use ($batch) {
                $batchOrder = $batch->batchOrders->where('food_order_id', $order->id)->first();

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'pickup_order' => $batchOrder?->pickup_order ?? ($index + 1),
                    'delivery_order' => $batchOrder?->delivery_order ?? ($index + 1),

                    // Restaurant
                    'restaurant' => [
                        'name' => $order->prestataire->nom ?? 'Restaurant',
                        'address' => $order->prestataire->adresse ?? '',
                        'city' => $order->prestataire->ville ?? '',
                        'phone' => $order->prestataire->phone ?? '',
                        'lat' => $order->prestataire->latitude,
                        'lng' => $order->prestataire->longitude,
                    ],

                    // Client (adresse masquée jusqu'à acceptation)
                    'client' => [
                        'city' => $this->extractCity($order->delivery_address),
                        'distance_km' => round($batchOrder?->distance_to_dropoff ?? 0, 1),
                    ],

                    // Contenu commande
                    'items_count' => $order->items->sum('quantity'),
                    'items_summary' => $order->items->take(3)->map(fn($i) => "{$i->quantity}x {$i->product_name}")->implode(', '),

                    // Statut
                    'status' => $order->status,
                    'delivery_status' => $order->delivery_status,
                    'notes' => $order->driver_notes,
                ];
            }),
        ]);
    }

    /**
     * Accepter une course
     */
    public function acceptDelivery(Request $request, DeliveryBatch $batch): JsonResponse
    {
        $driver = $this->getDriver();
        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Livreur non trouvé'], 403);
        }

        if ($batch->status !== DeliveryBatch::STATUS_AVAILABLE) {
            return response()->json(['success' => false, 'message' => 'Cette course n\'est plus disponible'], 400);
        }

        // Vérifier que le livreur peut prendre cette course
        $maxConcurrent = config('delivery.matching.max_concurrent_orders', 3);
        $currentOrders = $driver->activeFoodOrders()->count();
        $batchOrderCount = $batch->orders()->count();

        if ($currentOrders + $batchOrderCount > $maxConcurrent) {
            return response()->json([
                'success' => false,
                'message' => "Vous avez trop de livraisons en cours (max {$maxConcurrent})",
            ], 400);
        }

        // Assigner le livreur
        $batch->assignDriver($driver);

        // Charger les orders une seule fois avec les relations nécessaires
        $orders = $batch->orders()->with(['prestataire.user', 'client'])->get();

        // Notifier les prestataires
        foreach ($orders as $order) {
            try {
                $order->prestataire->user->notify(new \App\Notifications\DriverAcceptedDelivery($order, $driver));
            } catch (\Exception $e) {
                Log::error('Notification error: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Course acceptée !',
            'batch_id' => $batch->id,
            'orders' => $orders->map(fn($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'restaurant' => $o->prestataire->nom,
                'delivery_address' => $o->delivery_address, // Maintenant visible
                'delivery_phone' => $o->delivery_phone,
                'client_name' => $o->delivery_contact_name ?? $o->client->name,
            ]),
        ]);
    }

    /**
     * Obtenir le livreur connecté
     */
    protected function getDriver(): ?DeliveryDriver
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }
        return DeliveryDriver::where('user_id', $user->id)->where('is_active', true)->first();
    }

    /**
     * Extraire la ville d'une adresse
     */
    protected function extractCity(?string $address): string
    {
        if (!$address)
            return 'Inconnu';
        preg_match('/(\d{5})\s+([^,]+)/i', $address, $matches);
        return trim($matches[2] ?? 'Inconnu');
    }
}
