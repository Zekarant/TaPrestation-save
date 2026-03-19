<?php

namespace App\Notifications;

use App\Models\EquipmentRental;

class SimpleRentalPeriodEndedNotification extends SimpleEquipmentNotification
{
    /**
     * Create a new notification instance.
     *
     * @param EquipmentRental $rental
     * @return void
     */
    public function __construct(EquipmentRental $rental)
    {
        $equipmentName = $rental->equipment->name ?? 'Équipement';
        $clientName = $rental->client->user->name ?? 'Client';

        $data = [
            'type' => 'rental_ended',
            'rental_id' => $rental->id,
            'equipment_name' => $equipmentName,
            'client_name' => $clientName,
            'start_date' => $rental->start_date->format('Y-m-d'),
            'end_date' => $rental->end_date->format('Y-m-d'),
            'message' => 'La période de location de votre équipement est terminée.',
            'url' => route('prestataire.equipment-rentals.show', $rental->id)
        ];

        parent::__construct($data);
    }
}