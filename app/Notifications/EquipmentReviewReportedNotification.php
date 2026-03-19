<?php

namespace App\Notifications;

use App\Models\EquipmentReview;

class EquipmentReviewReportedNotification extends SimpleEquipmentNotification
{
    public function __construct(EquipmentReview $review, string $reportedBy, string $reason)
    {
        $equipmentName = $review->equipment->name ?? 'N/A';

        $data = [
            'review_id' => $review->id,
            'equipment_id' => $review->equipment_id,
            'equipment_name' => $equipmentName,
            'reported_by' => $reportedBy,
            'report_reason' => $reason,
            'title' => 'Avis signalé',
            'message' => 'Un avis sur "' . $equipmentName . '" a été signalé par un ' . $reportedBy . '. Raison : ' . $reason,
            'url' => url('/administrateur/equipment/reviews'),
            'type' => 'equipment_review_reported',
        ];

        parent::__construct($data);
    }
}
