<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\EquipmentRental;

class RentalPeriodEndedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $rental;

    /**
     * Create a new notification instance.
     */
    public function __construct(EquipmentRental $rental)
    {
        $this->rental = $rental;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $equipment = $this->rental->equipment;
        $client = $this->rental->client;
        
        return (new MailMessage)
                    ->subject('Période de location terminée - ' . $equipment->name)
                    ->line('La période de location de votre équipement est terminée.')
                    ->line('Équipement: ' . $equipment->name)
                    ->line('Client: ' . $client->user->name)
                    ->line('Période: du ' . $this->rental->start_date->format('d/m/Y') . ' au ' . $this->rental->end_date->format('d/m/Y'))
                    ->action('Voir les détails', route('prestataire.equipment-rentals.show', $this->rental->id))
                    ->line('Veuillez contacter le client pour organiser le retour de l\'équipement si ce n\'est pas déjà fait.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'rental_period_ended',
            'rental_id' => $this->rental->id,
            'equipment_id' => $this->rental->equipment_id,
            'equipment_name' => $this->rental->equipment->name,
            'client_id' => $this->rental->client_id,
            'client_name' => $this->rental->client->user->name,
            'start_date' => $this->rental->start_date->format('Y-m-d'),
            'end_date' => $this->rental->end_date->format('Y-m-d'),
            'message' => 'La période de location pour ' . $this->rental->equipment->name . ' est terminée.'
        ];
    }
}