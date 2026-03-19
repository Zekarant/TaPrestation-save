<?php

namespace App\Notifications;

use App\Models\EquipmentRental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentRentalCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $rental;
    public $cancelledBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(EquipmentRental $rental, string $cancelledBy = 'client')
    {
        $this->rental = $rental;
        $this->cancelledBy = $cancelledBy;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $cancellerName = $this->cancelledBy === 'client'
            ? ($this->rental->client->user->name ?? 'Le client')
            : ($this->rental->prestataire->user->name ?? 'Le prestataire');

        $subject = $this->cancelledBy === 'client'
            ? 'Location annulée par le client'
            : 'Location annulée par le prestataire';

        return (new MailMessage)
            ->subject($subject)
            ->line($cancellerName . ' a annulé la location de l\'équipement "' . ($this->rental->equipment->name ?? 'N/A') . '".')
            ->line('Raison : ' . ($this->rental->cancellation_reason ?? 'Non spécifiée'))
            ->line('Si un paiement avait été effectué, le remboursement sera traité automatiquement.')
            ->action('Voir les détails', url('/'))
            ->line('Merci de votre compréhension.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'rental_id' => $this->rental->id,
            'equipment_name' => $this->rental->equipment->name ?? 'N/A',
            'cancelled_by' => $this->cancelledBy,
            'cancellation_reason' => $this->rental->cancellation_reason,
            'message' => 'La location de "' . ($this->rental->equipment->name ?? 'N/A') . '" a été annulée par ' . ($this->cancelledBy === 'client' ? 'le client' : 'le prestataire') . '.',
            'type' => 'equipment_rental_cancelled'
        ];
    }
}
