<?php

namespace App\Notifications;

use App\Models\EquipmentRental;

class SimpleRentalStartedNotification extends SimpleEquipmentNotification
{
    /**
     * Create a new notification instance.
     *
     * @param EquipmentRental $rental
     * @return void
     */
    public function __construct(EquipmentRental $rental)
    {
        $isClient = false; // This would be determined by the notifiable user
        $equipmentName = $rental->equipment->name ?? 'Équipement';
        $clientName = $rental->client->user->name ?? 'Client';

        $data = [
            'type' => 'rental_started',
            'rental_id' => $rental->id,
            'equipment_id' => $rental->equipment_id,
            'equipment_name' => $equipmentName,
            'client_id' => $rental->client_id,
            'client_name' => $clientName,
            'start_date' => $rental->start_date->format('Y-m-d'),
            'end_date' => $rental->end_date->format('Y-m-d'),
            'message' => 'La location de ' . $equipmentName . ' pour ' . $clientName . ' commence aujourd\'hui.',
            'url' => route('prestataire.equipment-rentals.show', $rental->id)
        ];

        parent::__construct($data);
    }
}