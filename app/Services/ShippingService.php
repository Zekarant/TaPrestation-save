<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Service de gestion des transporteurs (Mondial Relay, Chronopost, Colissimo, etc.)
 */
class ShippingService
{
    /**
     * Transporteurs disponibles
     */
    const CARRIERS = [
        'mondial_relay' => [
            'name' => 'Mondial Relay',
            'logo' => '/images/carriers/mondial-relay.png',
            'tracking_url' => 'https://www.mondialrelay.fr/suivi-de-colis/?NumeroExpedition=',
            'has_relay_points' => true,
            'estimated_days' => '3-5',
        ],
        'chronopost' => [
            'name' => 'Chronopost',
            'logo' => '/images/carriers/chronopost.png',
            'tracking_url' => 'https://www.chronopost.fr/tracking-no-cms/suivi-page?liession=',
            'has_relay_points' => true,
            'estimated_days' => '1-2',
        ],
        'colissimo' => [
            'name' => 'Colissimo (La Poste)',
            'logo' => '/images/carriers/colissimo.png',
            'tracking_url' => 'https://www.laposte.fr/outils/suivre-vos-envois?code=',
            'has_relay_points' => true,
            'estimated_days' => '2-4',
        ],
        'ups' => [
            'name' => 'UPS',
            'logo' => '/images/carriers/ups.png',
            'tracking_url' => 'https://www.ups.com/track?tracknum=',
            'has_relay_points' => false,
            'estimated_days' => '2-5',
        ],
        'dhl' => [
            'name' => 'DHL',
            'logo' => '/images/carriers/dhl.png',
            'tracking_url' => 'https://www.dhl.com/fr-fr/home/suivi.html?tracking-id=',
            'has_relay_points' => false,
            'estimated_days' => '2-5',
        ],
        'hand_delivery' => [
            'name' => 'Remise en main propre',
            'logo' => '/images/carriers/hand-delivery.png',
            'tracking_url' => null,
            'has_relay_points' => false,
            'estimated_days' => '0',
        ],
    ];

    /**
     * Obtenir les transporteurs activés
     */
    public function getEnabledCarriers(): array
    {
        $enabled = json_decode(get_setting('shipping_carriers_enabled', '["mondial_relay","chronopost","colissimo"]'), true);
        
        return array_filter(self::CARRIERS, function($key) use ($enabled) {
            return in_array($key, $enabled) || $key === 'hand_delivery';
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Calculer les frais de livraison
     */
    public function calculateShippingCost(string $carrier, float $weight, array $dimensions, string $fromPostalCode, string $toPostalCode): array
    {
        // Tarifs de base simplifiés (à adapter avec les vraies APIs)
        $basePrices = [
            'mondial_relay' => ['base' => 3.99, 'per_kg' => 0.50],
            'chronopost' => ['base' => 7.99, 'per_kg' => 1.00],
            'colissimo' => ['base' => 4.99, 'per_kg' => 0.75],
            'ups' => ['base' => 9.99, 'per_kg' => 1.50],
            'dhl' => ['base' => 12.99, 'per_kg' => 2.00],
            'hand_delivery' => ['base' => 0, 'per_kg' => 0],
        ];

        if (!isset($basePrices[$carrier])) {
            return ['success' => false, 'error' => 'Transporteur inconnu'];
        }

        $pricing = $basePrices[$carrier];
        $cost = $pricing['base'] + ($weight * $pricing['per_kg']);

        // Zone éloignée = supplément
        $fromDept = substr($fromPostalCode, 0, 2);
        $toDept = substr($toPostalCode, 0, 2);
        
        $remoteDepts = ['97', '98', '20']; // DOM-TOM, Corse
        if (in_array($toDept, $remoteDepts) || in_array($fromDept, $remoteDepts)) {
            $cost *= 1.5;
        }

        return [
            'success' => true,
            'carrier' => $carrier,
            'carrier_name' => self::CARRIERS[$carrier]['name'] ?? $carrier,
            'cost' => round($cost, 2),
            'estimated_days' => self::CARRIERS[$carrier]['estimated_days'] ?? '3-5',
            'weight' => $weight,
        ];
    }

    /**
     * Créer une expédition
     */
    public function createShipment(
        string $carrier,
        array $senderAddress,
        array $recipientAddress,
        float $weight,
        ?array $dimensions = null,
        ?string $relayPointId = null
    ): array {
        try {
            // Générer un numéro de suivi (en prod, ça viendrait de l'API du transporteur)
            $trackingNumber = $this->generateTrackingNumber($carrier);

            $shippingCost = $this->calculateShippingCost(
                $carrier,
                $weight,
                $dimensions ?? [],
                $senderAddress['postal_code'] ?? '',
                $recipientAddress['postal_code'] ?? ''
            );

            $shipmentId = \DB::table('shipments')->insertGetId([
                'shippable_type' => 'App\\Models\\UrgentSalePurchase', // Par défaut
                'shippable_id' => 0, // À mettre à jour après
                'carrier' => $carrier,
                'tracking_number' => $trackingNumber,
                'tracking_url' => $this->getTrackingUrl($carrier, $trackingNumber),
                'sender_address' => json_encode($senderAddress),
                'recipient_address' => json_encode($recipientAddress),
                'relay_point_id' => $relayPointId,
                'status' => 'label_created',
                'shipping_cost' => $shippingCost['cost'] ?? 0,
                'weight' => $weight,
                'dimensions' => $dimensions ? json_encode($dimensions) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'success' => true,
                'shipment_id' => $shipmentId,
                'tracking_number' => $trackingNumber,
                'tracking_url' => $this->getTrackingUrl($carrier, $trackingNumber),
                'shipping_cost' => $shippingCost['cost'] ?? 0,
                'label_url' => null, // URL de l'étiquette à imprimer
            ];
        } catch (\Exception $e) {
            Log::error("Erreur création expédition: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Générer un numéro de suivi
     */
    private function generateTrackingNumber(string $carrier): string
    {
        $prefix = match($carrier) {
            'mondial_relay' => 'MR',
            'chronopost' => 'CP',
            'colissimo' => 'CL',
            'ups' => '1Z',
            'dhl' => 'JD',
            default => 'TP',
        };

        return $prefix . strtoupper(uniqid()) . rand(1000, 9999);
    }

    /**
     * Obtenir l'URL de suivi
     */
    public function getTrackingUrl(string $carrier, string $trackingNumber): ?string
    {
        $baseUrl = self::CARRIERS[$carrier]['tracking_url'] ?? null;
        
        if (!$baseUrl) {
            return null;
        }

        return $baseUrl . $trackingNumber;
    }

    /**
     * Mettre à jour le statut d'une expédition
     */
    public function updateShipmentStatus(int $shipmentId, string $status, ?array $metadata = null): bool
    {
        try {
            $data = [
                'status' => $status,
                'updated_at' => now(),
            ];

            if ($status === 'shipped') {
                $data['shipped_at'] = now();
            } elseif ($status === 'delivered') {
                $data['delivered_at'] = now();
            }

            \DB::table('shipments')->where('id', $shipmentId)->update($data);

            // Si livré, mettre à jour l'escrow associé
            if ($status === 'delivered') {
                $shipment = \DB::table('shipments')->find($shipmentId);
                if ($shipment && $shipment->escrow_id) {
                    // Libérer partiellement (80% à la livraison, 20% après validation conformité)
                    $escrow = \DB::table('escrow_transactions')->find($shipment->escrow_id);
                    if ($escrow) {
                        $partialRelease = $escrow->amount * 0.8;
                        app(EscrowService::class)->releaseToPrestataire($shipment->escrow_id, $partialRelease);
                    }
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Erreur MAJ statut expédition: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Confirmer la conformité (client valide que le produit est OK)
     */
    public function confirmConformity(int $shipmentId): bool
    {
        try {
            $shipment = \DB::table('shipments')->find($shipmentId);
            
            if (!$shipment || $shipment->conformity_validated) {
                return false;
            }

            \DB::table('shipments')->where('id', $shipmentId)->update([
                'conformity_validated' => true,
                'conformity_validated_at' => now(),
                'updated_at' => now(),
            ]);

            // Libérer le reste (20%) de l'escrow
            if ($shipment->escrow_id) {
                app(EscrowService::class)->releaseToPrestataire($shipment->escrow_id);
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Erreur confirmation conformité: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rechercher des points relais (Mondial Relay)
     */
    public function searchRelayPoints(string $carrier, string $postalCode, string $country = 'FR'): array
    {
        // En production, appeler l'API du transporteur
        // Ici on retourne des données de démo
        
        $cacheKey = "relay_points_{$carrier}_{$postalCode}_{$country}";
        
        return Cache::remember($cacheKey, 3600, function() use ($postalCode) {
            // Données de démonstration
            return [
                [
                    'id' => 'MR001',
                    'name' => 'Tabac Presse du Centre',
                    'address' => '15 Rue de la Liberté',
                    'postal_code' => $postalCode,
                    'city' => 'Paris',
                    'distance' => '150m',
                    'hours' => 'Lun-Sam 8h-20h',
                ],
                [
                    'id' => 'MR002',
                    'name' => 'Supermarché Express',
                    'address' => '42 Avenue des Champs',
                    'postal_code' => $postalCode,
                    'city' => 'Paris',
                    'distance' => '300m',
                    'hours' => 'Lun-Dim 7h-22h',
                ],
                [
                    'id' => 'MR003',
                    'name' => 'Station Service Total',
                    'address' => '8 Boulevard National',
                    'postal_code' => $postalCode,
                    'city' => 'Paris',
                    'distance' => '500m',
                    'hours' => '24h/24',
                ],
            ];
        });
    }
}
