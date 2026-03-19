<?php

namespace App\Notifications;

use App\Models\Equipment;

class EquipmentReactivatedNotification extends SimpleEquipmentNotification
{
    public function __construct(Equipment $equipment)
    {
        $data = [
            'equipment_id' => $equipment->id,
            'equipment_name' => $equipment->name ?? 'Équipement',
            'title' => 'Équipement réactivé',
            'message' => 'Votre équipement "' . ($equipment->name ?? 'N/A') . '" a été réactivé par l\'administration. Il est de nouveau visible.',
            'url' => url('/prestataire/equipment/' . $equipment->id),
            'type' => 'equipment_reactivated',
        ];

        parent::__construct($data);
    }
}
