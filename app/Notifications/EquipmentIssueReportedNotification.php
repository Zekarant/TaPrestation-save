<?php

namespace App\Notifications;

use App\Models\EquipmentRental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentIssueReportedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $rental;
    public $reportedBy;

    public function __construct(EquipmentRental $rental, string $reportedBy = 'client')
    {
        $this->rental = $rental;
        $this->reportedBy = $reportedBy;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $lastIssue = collect($this->rental->damage_reports ?? [])->last();
        $severity = $lastIssue['severity'] ?? 'medium';
        $issueType = $lastIssue['type'] ?? 'other';

        $urgency = in_array($severity, ['high', 'critical']) ? ' [URGENT]' : '';

        return (new MailMessage)
            ->subject('Problème signalé sur un équipement' . $urgency)
            ->line('Un problème a été signalé sur l\'équipement "' . ($this->rental->equipment->name ?? 'N/A') . '".')
            ->line('Type de problème : ' . $issueType)
            ->line('Sévérité : ' . $severity)
            ->line('Description : ' . ($lastIssue['description'] ?? 'Non spécifiée'))
            ->action('Voir les détails', url('/'))
            ->line('Merci de traiter ce signalement rapidement.');
    }

    public function toArray($notifiable)
    {
        $lastIssue = collect($this->rental->damage_reports ?? [])->last();

        return [
            'rental_id' => $this->rental->id,
            'equipment_name' => $this->rental->equipment->name ?? 'N/A',
            'issue_type' => $lastIssue['type'] ?? 'other',
            'severity' => $lastIssue['severity'] ?? 'medium',
            'reported_by' => $this->reportedBy,
            'message' => 'Un problème a été signalé sur "' . ($this->rental->equipment->name ?? 'N/A') . '".',
            'type' => 'equipment_issue_reported'
        ];
    }
}
