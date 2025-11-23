<?php

namespace App\Notifications;

use App\Models\EquipmentRentalRequest;

class SimpleEquipmentRentalResponseNotification extends SimpleEquipmentNotification
{
    /**
     * Create a new notification instance.
     *
     * @param EquipmentRentalRequest $rentalRequest
     * @param string $response
     * @return void
     */
    public function __construct(EquipmentRentalRequest $rentalRequest, $response)
    {
        $data = [
            'rental_request_id' => $rentalRequest->id,
            'equipment_name' => $rentalRequest->equipment->name ?? 'Équipement',
            'prestataire_name' => $rentalRequest->prestataire->user->name ?? 'Prestataire',
            'response' => $response,
            'message' => 'Le prestataire ' . ($rentalRequest->prestataire->user->name ?? 'prestataire') . ' a répondu à votre demande de location pour ' . ($rentalRequest->equipment->name ?? 'équipement'),
            'url' => route('client.equipment-rental-requests.show', $rentalRequest->id),
            'type' => 'equipment_rental_response'
        ];

        parent::__construct($data);
    }
}