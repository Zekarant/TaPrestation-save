<?php

namespace App\Notifications;

use App\Models\EquipmentRentalRequest;

class EquipmentRentalRequestCancelledNotification extends SimpleEquipmentNotification
{
    public function __construct(EquipmentRentalRequest $rentalRequest)
    {
        $equipmentName = $rentalRequest->equipment->name ?? 'Équipement';
        $clientName = $rentalRequest->client->user->name ?? 'Le client';

        $data = [
            'rental_request_id' => $rentalRequest->id,
            'equipment_name' => $equipmentName,
            'client_name' => $clientName,
            'title' => 'Demande de location annulée',
            'message' => $clientName . ' a annulé sa demande de location pour "' . $equipmentName . '".',
            'url' => url('/prestataire/equipment-rental-requests/' . $rentalRequest->id),
            'type' => 'equipment_rental_request_cancelled',
        ];

        parent::__construct($data);
    }
}
