<?php

namespace App\Notifications;

use App\Models\EquipmentRentalRequest;

class EquipmentRentalRequestUpdatedNotification extends SimpleEquipmentNotification
{
    public function __construct(EquipmentRentalRequest $rentalRequest)
    {
        $equipmentName = $rentalRequest->equipment->name ?? 'Équipement';
        $clientName = $rentalRequest->client->user->name ?? 'Le client';

        $data = [
            'rental_request_id' => $rentalRequest->id,
            'equipment_name' => $equipmentName,
            'client_name' => $clientName,
            'title' => 'Demande de location modifiée',
            'message' => $clientName . ' a modifié sa demande de location pour "' . $equipmentName . '". Nouvelles dates : du ' . ($rentalRequest->start_date ? \Carbon\Carbon::parse($rentalRequest->start_date)->format('d/m/Y') : '?') . ' au ' . ($rentalRequest->end_date ? \Carbon\Carbon::parse($rentalRequest->end_date)->format('d/m/Y') : '?') . '.',
            'url' => url('/prestataire/equipment-rental-requests/' . $rentalRequest->id),
            'type' => 'equipment_rental_request_updated',
        ];

        parent::__construct($data);
    }
}
