<?php

namespace App\Notifications;

use App\Models\Equipment;

class EquipmentSuspendedNotification extends SimpleEquipmentNotification
{
    public function __construct(Equipment $equipment, string $reason)
    {
        $data = [
            'equipment_id' => $equipment->id,
            'equipment_name' => $equipment->name ?? 'Équipement',
            'reason' => $reason,
            'title' => 'Équipement suspendu',
            'message' => 'Votre équipement "' . ($equipment->name ?? 'N/A') . '" a été suspendu par l\'administration. Raison : ' . $reason,
            'url' => url('/prestataire/equipment/' . $equipment->id),
            'type' => 'equipment_suspended',
        ];

        parent::__construct($data);
    }
}
