<?php

namespace App\Notifications;

use App\Models\EquipmentRental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentReturnConfirmedNotification extends Notification implements ShouldQueue
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
            ->subject('Retour d\'équipement confirmé')
            ->line('Le client ' . ($this->rental->client->user->name ?? 'N/A') . ' a confirmé le retour de l\'équipement "' . ($this->rental->equipment->name ?? 'N/A') . '".')
            ->line('La location est maintenant terminée.')
            ->action('Voir les détails', url('/'))
            ->line('Merci pour votre service.');
    }

    public function toArray($notifiable)
    {
        return [
            'rental_id' => $this->rental->id,
            'equipment_name' => $this->rental->equipment->name ?? 'N/A',
            'client_name' => $this->rental->client->user->name ?? 'N/A',
            'message' => 'Le retour de "' . ($this->rental->equipment->name ?? 'N/A') . '" a été confirmé par le client.',
            'type' => 'equipment_return_confirmed'
        ];
    }
}
