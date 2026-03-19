<?php

namespace App\Notifications;

use App\Models\EquipmentRental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentReturnedNotification extends Notification implements ShouldQueue
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
            ->subject('Équipement marqué comme retourné')
            ->line('L\'équipement "' . ($this->rental->equipment->name ?? 'N/A') . '" a été marqué comme retourné par le prestataire.')
            ->line('Veuillez confirmer le retour pour finaliser la location.')
            ->action('Confirmer le retour', url('/'))
            ->line('Merci de confirmer rapidement.');
    }

    public function toArray($notifiable)
    {
        return [
            'rental_id' => $this->rental->id,
            'equipment_name' => $this->rental->equipment->name ?? 'N/A',
            'prestataire_name' => $this->rental->prestataire->user->name ?? 'N/A',
            'message' => 'L\'équipement "' . ($this->rental->equipment->name ?? 'N/A') . '" a été marqué comme retourné.',
            'type' => 'equipment_returned'
        ];
    }
}
