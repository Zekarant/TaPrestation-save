<?php

namespace App\Notifications;

use App\Models\EquipmentRentalRequest;

class SimpleEquipmentRentalRejectedNotification extends SimpleEquipmentNotification
{
    /**
     * Create a new notification instance.
     *
     * @param EquipmentRentalRequest $rentalRequest
     * @param string|null $rejectionReason
     * @return void
     */
    public function __construct(EquipmentRentalRequest $rentalRequest, $rejectionReason = null)
    {
        $message = 'Votre demande de location pour ' . ($rentalRequest->equipment->name ?? 'équipement') . ' a été refusée.';
        if ($rejectionReason) {
            $message .= ' Raison : ' . $rejectionReason;
        }

        $data = [
            'rental_request_id' => $rentalRequest->id,
            'equipment_name' => $rentalRequest->equipment->name ?? 'Équipement',
            'prestataire_name' => $rentalRequest->prestataire->user->name ?? 'Prestataire',
            'rejection_reason' => $rejectionReason,
            'message' => $message,
            'url' => route('client.equipment-rental-requests.show', $rentalRequest->id),
            'type' => 'equipment_rental_rejected'
        ];

        parent::__construct($data);
    }
}