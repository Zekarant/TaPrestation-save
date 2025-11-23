<?php

namespace App\Notifications;

use App\Models\EquipmentRentalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentRentalResponseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $rentalRequest;
    public $response;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\EquipmentRentalRequest  $rentalRequest
     * @param  string  $response
     * @return void
     */
    public function __construct(EquipmentRentalRequest $rentalRequest, $response)
    {
        $this->rentalRequest = $rentalRequest;
        $this->response = $response;
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
                    ->subject('Réponse à votre demande de location')
                    ->line('Le prestataire a répondu à votre demande de location pour l\'équipement "' . $this->rentalRequest->equipment->name . '".')
                    ->line('Prestataire : ' . $this->rentalRequest->prestataire->user->name)
                    ->line('Message : ' . $this->response)
                    ->action('Voir les détails', $url)
                    ->line('N\'hésitez pas à contacter le prestataire pour plus d\'informations.');
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
            'response' => $this->response,
            'message' => 'Le prestataire ' . $this->rentalRequest->prestataire->user->name . ' a répondu à votre demande de location pour ' . $this->rentalRequest->equipment->name,
            'url' => route('client.equipment-rental-requests.show', $this->rentalRequest->id),
            'type' => 'equipment_rental_response'
        ];
    }
}