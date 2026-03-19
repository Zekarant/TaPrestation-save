<?php

namespace App\Notifications;

use App\Models\EquipmentRentalRequest;

class SimpleEquipmentRentalRequestConfirmationNotification extends SimpleEquipmentNotification
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
            'title' => 'Confirmation de demande de location',
            'message' => 'Votre demande de location pour ' . ($rentalRequest->equipment->name ?? 'équipement') . ' a été envoyée.',
            'url' => route('client.equipment-rental-requests.show', $rentalRequest->id),
            'type' => 'equipment_rental_request'
        ];

        parent::__construct($data);
    }
}