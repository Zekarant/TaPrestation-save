<?php

namespace App\Notifications;

use App\Models\EquipmentRentalRequest;

class SimpleNewEquipmentRentalRequestNotification extends SimpleEquipmentNotification
{
    /**
     * Create a new notification instance.
     *
     * @param EquipmentRentalRequest $rentalRequest
     * @return void
     */
    public function __construct(EquipmentRentalRequest $rentalRequest)
    {
        // Check if required relationships exist
        if (!$rentalRequest->equipment || !$rentalRequest->client) {
            // Return a minimal notification if relationships are missing
            $data = [
                'rental_request_id' => $rentalRequest->id,
                'equipment_name' => 'Équipement',
                'client_name' => 'Client',
                'title' => 'Nouvelle demande de location',
                'message' => 'Vous avez une nouvelle demande de location',
                'url' => url('/prestataire/equipment-rental-requests/' . $rentalRequest->id),
                'type' => 'equipment_rental_request'
            ];
        } else {
            try {
                $url = route('prestataire.equipment-rental-requests.show', $rentalRequest->id);
            } catch (\Exception $e) {
                // If route generation fails, use a fallback
                $url = url('/prestataire/equipment-rental-requests/' . $rentalRequest->id);
            }

            $equipmentName = $rentalRequest->equipment->name ?? 'Équipement';
            $clientName = $rentalRequest->client->user->name ?? 'Client';

            $data = [
                'rental_request_id' => $rentalRequest->id,
                'equipment_name' => $equipmentName,
                'client_name' => $clientName,
                'title' => 'Nouvelle demande de location',
                'message' => 'Vous avez une nouvelle demande de location pour ' . $equipmentName,
                'url' => $url,
                'type' => 'equipment_rental_request'
            ];
        }

        parent::__construct($data);
    }
}