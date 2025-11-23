<?php

namespace App\Notifications;

use App\Models\EquipmentRentalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentRentalRequestConfirmationNotification extends Notification
{
    use Queueable;

    public $rentalRequest;

    /**
     * Create a new notification instance.
     *
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
        return ['database'];
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
                    ->subject('Confirmation de votre demande de location')
                    ->line('Votre demande de location pour l\'équipement "' . $this->rentalRequest->equipment->name . '" a bien été envoyée.')
                    ->line('Le prestataire a été notifié et vous recevrez une réponse prochainement.')
                    ->action('Voir ma demande', $url)
                    ->line('Merci de votre confiance.');
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
            'title' => 'Confirmation de demande de location',
            'message' => 'Votre demande de location pour ' . $this->rentalRequest->equipment->name . ' a été envoyée.',
            'url' => route('client.equipment-rental-requests.show', $this->rentalRequest->id),
            'type' => 'equipment_rental_request'
        ];
    }
}