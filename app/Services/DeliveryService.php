<?php

namespace App\Services;

use App\Models\DeliveryProvider;
use App\Models\DeliveryOrder;

class DeliveryService
{
    /**
     * Calculate shipping cost
     */
    public function calculateShippingCost(DeliveryProvider $provider, float $weight, float $distance, string $shippingType = 'standard'): float
    {
        $baseCost = $provider->base_rate;
        $distanceCost = ($distance / 1000) * $provider->per_km_rate; // distance in meters, convert to km

        switch ($shippingType) {
            case 'express':
                return ($baseCost + $distanceCost) * 1.5;
            case 'overnight':
                return ($baseCost + $distanceCost) * 2;
            default: // standard
                return $baseCost + $distanceCost;
        }
    }

    /**
     * Get available providers for a location
     */
    public function getAvailableProviders(float $latitude, float $longitude): array
    {
        return DeliveryProvider::where('is_active', true)
            ->get()
            ->filter(function ($provider) use ($latitude, $longitude) {
                return $this->isLocationCovered($provider, $latitude, $longitude);
            })
            ->values()
            ->toArray();
    }

    /**
     * Check if provider covers a location
     */
    protected function isLocationCovered(DeliveryProvider $provider, float $latitude, float $longitude): bool
    {
        if (!$provider->coverage_areas) {
            return true; // No coverage restrictions
        }

        $coverage = $provider->coverage_areas;

        // Simple rectangle coverage check
        return $latitude >= $coverage['min_lat'] && $latitude <= $coverage['max_lat'] &&
               $longitude >= $coverage['min_lng'] && $longitude <= $coverage['max_lng'];
    }

    /**
     * Create delivery order
     */
    public function createDeliveryOrder(int $providerId, array $shipmentData): DeliveryOrder
    {
        $provider = DeliveryProvider::find($providerId);
        $cost = $shipmentData['shipping_cost'] ?? $provider->base_rate;

        $data = [
            'delivery_provider_id' => $providerId,
            'booking_id' => $shipmentData['booking_id'],
            'tracking_number' => $this->generateTrackingNumber(),
            'status' => 'pending',
            'delivery_address' => $shipmentData['recipient_address'],
            'pickup_address' => $shipmentData['pickup_address'] ?? 'Adresse du prestataire',
            'shipping_cost' => $cost,
            'weight' => $shipmentData['weight'] ?? 1.0,
            'metadata' => [
                'shipping_type' => $shipmentData['shipping_type'] ?? 'standard',
                'recipient_name' => $shipmentData['recipient_name'] ?? null,
            ]
        ];

        return DeliveryOrder::create($data);
    }

    /**
     * Track delivery
     */
    public function trackDelivery(string $trackingNumber): array
    {
        $order = DeliveryOrder::where('tracking_number', $trackingNumber)->first();

        if (!$order) {
            return ['error' => 'Delivery not found'];
        }

        // Call provider API based on provider type
        return $this->fetchTrackingInfo($order);
    }

    /**
     * Fetch tracking info from provider
     */
    protected function fetchTrackingInfo(DeliveryOrder $order): array
    {
        $provider = $order->provider;

        // Example implementation for different providers
        switch ($provider->code) {
            case 'colissimo':
                return $this->trackColissimo($order->tracking_number);
            case 'chronopost':
                return $this->trackChronopost($order->tracking_number);
            case 'dpd':
                return $this->trackDPD($order->tracking_number);
            default:
                return ['status' => 'unknown'];
        }
    }

    protected function trackColissimo(string $tracking): array
    {
        // Implement Colissimo API call
        return ['status' => 'in_transit', 'tracking_number' => $tracking];
    }

    protected function trackChronopost(string $tracking): array
    {
        // Implement Chronopost API call
        return ['status' => 'in_transit', 'tracking_number' => $tracking];
    }

    protected function trackDPD(string $tracking): array
    {
        // Implement DPD API call
        return ['status' => 'in_transit', 'tracking_number' => $tracking];
    }

    protected function generateTrackingNumber(): string
    {
        return 'TP' . date('YmdHis') . strtoupper(bin2hex(random_bytes(4)));
    }

    /**
     * Get estimated delivery time
     */
    public function getEstimatedDelivery(DeliveryProvider $provider, float $distance): \Carbon\Carbon
    {
        $days = ceil($distance / 100); // Rough estimate: 100km per day
        return now()->addDays($days);
    }
}
