<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\EquipmentRental;

class RentalStartedNotification extends Notification implements ShouldQueue
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
        $isClient = $notifiable->id === $this->rental->client->user->id;
        
        $message = (new MailMessage)
                    ->subject('Période de location commencée - ' . $equipment->name);
        
        if ($isClient) {
            return $message
                    ->line('Votre période de location commence aujourd\'hui !')
                    ->line('Équipement: ' . $equipment->name)
                    ->line('Période: du ' . $this->rental->start_date->format('d/m/Y') . ' au ' . $this->rental->end_date->format('d/m/Y'))
                    ->line('Prix total: ' . number_format($this->rental->total_amount, 2, ',', ' ') . ' €')
                    ->action('Voir les détails', route('client.equipment.rentals.show', $this->rental->id))
                    ->line('Veuillez contacter le prestataire pour organiser la prise en charge de l\'équipement.');
        } else {
            return $message
                    ->line('La période de location pour votre équipement commence aujourd\'hui.')
                    ->line('Équipement: ' . $equipment->name)
                    ->line('Client: ' . $this->rental->client->user->name)
                    ->line('Période: du ' . $this->rental->start_date->format('d/m/Y') . ' au ' . $this->rental->end_date->format('d/m/Y'))
                    ->action('Voir les détails', route('prestataire.equipment-rentals.show', $this->rental->id))
                    ->line('Veuillez vous assurer que l\'équipement est prêt pour la location.');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $isClient = $notifiable->id === $this->rental->client->user->id;
        
        return [
            'type' => 'rental_started',
            'rental_id' => $this->rental->id,
            'equipment_id' => $this->rental->equipment_id,
            'equipment_name' => $this->rental->equipment->name,
            'client_id' => $this->rental->client_id,
            'client_name' => $this->rental->client->user->name,
            'start_date' => $this->rental->start_date->format('Y-m-d'),
            'end_date' => $this->rental->end_date->format('Y-m-d'),
            'message' => $isClient 
                ? 'Votre location pour ' . $this->rental->equipment->name . ' commence aujourd\'hui.'
                : 'La location de ' . $this->rental->equipment->name . ' pour ' . $this->rental->client->user->name . ' commence aujourd\'hui.'
        ];
    }
}