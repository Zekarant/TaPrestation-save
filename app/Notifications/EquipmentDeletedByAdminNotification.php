<?php

namespace App\Notifications;

class EquipmentDeletedByAdminNotification extends SimpleEquipmentNotification
{
    public function __construct(string $equipmentName, string $reason)
    {
        $data = [
            'equipment_name' => $equipmentName,
            'reason' => $reason,
            'title' => 'Équipement supprimé',
            'message' => 'Votre équipement "' . $equipmentName . '" a été supprimé par l\'administration. Raison : ' . $reason,
            'url' => url('/prestataire/equipment'),
            'type' => 'equipment_deleted_by_admin',
        ];

        parent::__construct($data);
    }
}
