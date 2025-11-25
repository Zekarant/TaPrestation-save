<?php

namespace App\Notifications;

use App\Models\EquipmentRentalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEquipmentRentalRequestNotification extends Notification
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
        // Check if required relationships exist
        if (!$this->rentalRequest->equipment || !$this->rentalRequest->client) {
            // If relationships are missing, we can't send a meaningful email
            return null;
        }

        try {
            $url = route('prestataire.equipment-rental-requests.show', $this->rentalRequest);
        } catch (\Exception $e) {
            // If route generation fails, use a fallback
            $url = url('/prestataire/equipment-rental-requests/' . $this->rentalRequest->id);
        }

        $equipmentName = $this->rentalRequest->equipment->name ?? 'Équipement';
        $clientName = $this->rentalRequest->client->user->name ?? 'Client';

        return (new MailMessage)
                    ->subject('Nouvelle demande de location pour votre matériel')
                    ->line('Vous avez reçu une nouvelle demande de location pour l\'un de vos équipements.')
                    ->line('Équipement : ' . $equipmentName)
                    ->line('Client : ' . $clientName)
                    ->action('Voir la demande', $url)
                    ->line('Veuillez répondre à cette demande dans les plus brefs délais.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        // Check if required relationships exist
        if (!$this->rentalRequest->equipment || !$this->rentalRequest->client) {
            // Return a minimal notification if relationships are missing
            return [
                'rental_request_id' => $this->rentalRequest->id,
                'equipment_name' => 'Équipement',
                'client_name' => 'Client',
                'title' => 'Nouvelle demande de location',
                'message' => 'Vous avez une nouvelle demande de location',
                'url' => url('/prestataire/equipment-rental-requests/' . $this->rentalRequest->id),
                'type' => 'equipment_rental_request'
            ];
        }

        try {
            $url = route('prestataire.equipment-rental-requests.show', $this->rentalRequest->id);
        } catch (\Exception $e) {
            // If route generation fails, use a fallback
            $url = url('/prestataire/equipment-rental-requests/' . $this->rentalRequest->id);
        }

        $equipmentName = $this->rentalRequest->equipment->name ?? 'Équipement';
        $clientName = $this->rentalRequest->client->user->name ?? 'Client';

        return [
            'rental_request_id' => $this->rentalRequest->id,
            'equipment_name' => $equipmentName,
            'client_name' => $clientName,
            'title' => 'Nouvelle demande de location',
            'message' => 'Vous avez une nouvelle demande de location pour ' . $equipmentName,
            'url' => $url,
            'type' => 'equipment_rental_request'
        ];
    }
}