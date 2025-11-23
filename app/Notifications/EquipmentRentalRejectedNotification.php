<?php

namespace App\Notifications;

use App\Models\EquipmentRentalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentRentalRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $rentalRequest;
    public $rejectionReason;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\EquipmentRentalRequest  $rentalRequest
     * @param  string|null  $rejectionReason
     * @return void
     */
    public function __construct(EquipmentRentalRequest $rentalRequest, $rejectionReason = null)
    {
        $this->rentalRequest = $rentalRequest;
        $this->rejectionReason = $rejectionReason;
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
        $mailMessage = (new MailMessage)
                    ->subject('Demande de location refusée')
                    ->line('Nous sommes désolés de vous informer que votre demande de location pour l\'équipement "' . $this->rentalRequest->equipment->name . '" a été refusée.')
                    ->line('Prestataire : ' . $this->rentalRequest->prestataire->user->name);
        
        if ($this->rejectionReason) {
            $mailMessage->line('Raison : ' . $this->rejectionReason);
        }
        
        return $mailMessage->action('Voir les détails', $url)
                          ->line('N\'hésitez pas à contacter le prestataire pour plus d\'informations ou à rechercher d\'autres équipements disponibles.');
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
            'rejection_reason' => $this->rejectionReason,
            'message' => 'Votre demande de location pour ' . $this->rentalRequest->equipment->name . ' a été refusée.' . ($this->rejectionReason ? ' Raison : ' . $this->rejectionReason : ''),
            'url' => route('client.equipment-rental-requests.show', $this->rentalRequest->id),
            'type' => 'equipment_rental_rejected'
        ];
    }
}