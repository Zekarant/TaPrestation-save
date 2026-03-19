<?php

namespace App\Notifications;

use App\Models\EquipmentReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentReportCreated extends Notification
{
    use Queueable;

    public $report;

    /**
     * Create a new notification instance.
     */
    public function __construct(EquipmentReport $report)
    {
        $this->report = $report;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $equipmentName = $this->report->equipment->name ?? 'Équipement inconnu';
        $priority = $this->report->priority;
        $category = $this->report->category;
        
        $priorityText = [
            'urgent' => '🔴 URGENT',
            'high' => '🟠 ÉLEVÉE',
            'medium' => '🟡 MOYENNE',
            'low' => '🟢 FAIBLE'
        ][$priority] ?? $priority;
        
        return (new MailMessage)
            ->subject("Nouveau signalement d'équipement - Priorité {$priorityText}")
            ->greeting('Bonjour,')
            ->line("Un nouveau signalement a été créé pour l'équipement : **{$equipmentName}**")
            ->line("**Catégorie :** {$category}")
            ->line("**Priorité :** {$priorityText}")
            ->line("**Description :** {$this->report->description}")
            ->action('Voir le signalement', route('administrateur.reports.equipments.show', $this->report))
            ->line('Merci de traiter ce signalement dans les plus brefs délais.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $equipmentName = $this->report->equipment->name ?? 'Équipement inconnu';
        $priority = $this->report->priority;
        $priorityText = [
            'urgent' => '🔴 URGENT',
            'high' => '🟠 ÉLEVÉE',
            'medium' => '🟡 MOYENNE',
            'low' => '🟢 FAIBLE'
        ][$priority] ?? $priority;
        
        return [
            'report_id' => $this->report->id,
            'equipment_id' => $this->report->equipment_id,
            'equipment_name' => $equipmentName,
            'priority' => $priority,
            'title' => "Nouveau signalement d'équipement - {$priorityText}",
            'message' => "Un nouveau signalement a été créé pour l'équipement {$equipmentName}",
            'type' => 'equipment_report',
            'url' => route('administrateur.reports.equipments.show', $this->report)
        ];
    }
}
