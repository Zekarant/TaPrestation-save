<?php

namespace App\Notifications;

use App\Models\EquipmentRentalRequest;

class SimpleEquipmentRentalAcceptedNotification extends SimpleEquipmentNotification
{
    /**
     * Create a new notification instance.
     *
     * @param EquipmentRentalRequest $rentalRequest
     * @return void
     */
    public function __construct(EquipmentRentalRequest $rentalRequest)
    {
        $data = [
            'rental_request_id' => $rentalRequest->id,
            'equipment_name' => $rentalRequest->equipment->name ?? 'Équipement',
            'prestataire_name' => $rentalRequest->prestataire->user->name ?? 'Prestataire',
            'message' => 'Votre demande de location pour ' . ($rentalRequest->equipment->name ?? 'équipement') . ' a été acceptée.',
            'url' => route('client.equipment-rental-requests.show', $rentalRequest->id),
            'type' => 'equipment_rental_accepted'
        ];

        parent::__construct($data);
    }
}