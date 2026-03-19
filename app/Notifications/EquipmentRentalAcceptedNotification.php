<?php

namespace App\Notifications;

use App\Models\EquipmentRentalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentRentalAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $rentalRequest;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\EquipmentRentalRequest  $rentalRequest
     * @return void
     */
    public function __construct(EquipmentRentalRequest $rentalRequest)
    {
        $this->rentalRequest = $rentalRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = route('client.equipment-rental-requests.show', $this->rentalRequest);

        return (new MailMessage)
                    ->subject('Demande de location acceptée')
                    ->line('Bonne nouvelle ! Votre demande de location pour l\'équipement "' . $this->rentalRequest->equipment->name . '" a été acceptée.')
                    ->line('Prestataire : ' . $this->rentalRequest->prestataire->user->name)
                    ->line('Période : du ' . $this->rentalRequest->start_date . ' au ' . $this->rentalRequest->end_date)
                    ->action('Voir les détails', $url)
                    ->line('Vous pouvez maintenant procéder à la confirmation de votre location.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'rental_request_id' => $this->rentalRequest->id,
            'equipment_name' => $this->rentalRequest->equipment->name,
            'prestataire_name' => $this->rentalRequest->prestataire->user->name,
            'message' => 'Votre demande de location pour ' . $this->rentalRequest->equipment->name . ' a été acceptée.',
            'url' => route('client.equipment-rental-requests.show', $this->rentalRequest->id),
            'type' => 'equipment_rental_accepted'
        ];
    }
}