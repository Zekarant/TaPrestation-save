<?php

namespace App\Notifications;

use App\Models\EquipmentRental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentRentalCompletedNotification extends Notification implements ShouldQueue
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
            ->subject('Location finalisée - Laissez votre avis')
            ->line('Votre location de l\'équipement "' . ($this->rental->equipment->name ?? 'N/A') . '" est maintenant terminée.')
            ->line('Nous espérons que vous en êtes satisfait.')
            ->action('Laisser un avis', url('/'))
            ->line('Votre avis aide les autres utilisateurs à faire leur choix.');
    }

    public function toArray($notifiable)
    {
        return [
            'rental_id' => $this->rental->id,
            'equipment_name' => $this->rental->equipment->name ?? 'N/A',
            'prestataire_name' => $this->rental->prestataire->user->name ?? 'N/A',
            'message' => 'Votre location de "' . ($this->rental->equipment->name ?? 'N/A') . '" est terminée. Laissez un avis !',
            'type' => 'equipment_rental_completed'
        ];
    }
}
