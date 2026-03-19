<?php

namespace App\Notifications;

use App\Models\EquipmentReview;

class EquipmentReviewResponseNotification extends SimpleEquipmentNotification
{
    public function __construct(EquipmentReview $review)
    {
        $prestataireName = $review->prestataire->user->name ?? 'Le prestataire';
        $equipmentName = $review->equipment->name ?? 'l\'équipement';

        $data = [
            'review_id' => $review->id,
            'equipment_id' => $review->equipment_id,
            'equipment_name' => $equipmentName,
            'prestataire_name' => $prestataireName,
            'title' => 'Réponse à votre avis',
            'message' => $prestataireName . ' a répondu à votre avis sur "' . $equipmentName . '".',
            'url' => url('/client/equipment-reviews/' . $review->id),
            'type' => 'equipment_review_response',
        ];

        parent::__construct($data);
    }
}
