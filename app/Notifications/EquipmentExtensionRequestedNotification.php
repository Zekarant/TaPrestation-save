<?php

namespace App\Notifications;

use App\Models\EquipmentRental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentExtensionRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $rental;
    public $extensionDays;

    public function __construct(EquipmentRental $rental, int $extensionDays = 0)
    {
        $this->rental = $rental;
        $this->extensionDays = $extensionDays;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Demande de prolongation de location')
            ->line('Le client ' . ($this->rental->client->user->name ?? 'N/A') . ' demande une prolongation pour l\'équipement "' . ($this->rental->equipment->name ?? 'N/A') . '".')
            ->line('Durée demandée : ' . $this->extensionDays . ' jour(s) supplémentaire(s)')
            ->action('Voir la demande', url('/'))
            ->line('Merci de répondre à cette demande rapidement.');
    }

    public function toArray($notifiable)
    {
        return [
            'rental_id' => $this->rental->id,
            'equipment_name' => $this->rental->equipment->name ?? 'N/A',
            'client_name' => $this->rental->client->user->name ?? 'N/A',
            'extension_days' => $this->extensionDays,
            'message' => 'Demande de prolongation de ' . $this->extensionDays . ' jour(s) pour "' . ($this->rental->equipment->name ?? 'N/A') . '".',
            'type' => 'equipment_extension_requested'
        ];
    }
}
