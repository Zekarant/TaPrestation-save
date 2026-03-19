<?php

namespace App\Notifications;

use App\Models\EquipmentRental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentReviewSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $rental;

    public function __construct(EquipmentRental $rental)
    {
        $this->rental = $rental;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nouvel avis sur votre équipement')
            ->line('Le client ' . ($this->rental->client->user->name ?? 'N/A') . ' a laissé un avis sur l\'équipement "' . ($this->rental->equipment->name ?? 'N/A') . '".')
            ->action('Voir l\'avis', url('/'))
            ->line('Merci de continuer à offrir un excellent service.');
    }

    public function toArray($notifiable)
    {
        return [
            'rental_id' => $this->rental->id,
            'equipment_name' => $this->rental->equipment->name ?? 'N/A',
            'client_name' => $this->rental->client->user->name ?? 'N/A',
            'message' => 'Nouvel avis reçu pour "' . ($this->rental->equipment->name ?? 'N/A') . '".',
            'type' => 'equipment_review_submitted'
        ];
    }
}
