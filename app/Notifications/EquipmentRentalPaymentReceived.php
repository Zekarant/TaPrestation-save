<?php

namespace App\Notifications;

use App\Models\EquipmentRentalRequest;
use App\Models\PaymentTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EquipmentRentalPaymentReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public EquipmentRentalRequest $rentalRequest;
    public PaymentTransaction $transaction;

    public function __construct(EquipmentRentalRequest $rentalRequest, PaymentTransaction $transaction)
    {
        $this->rentalRequest = $rentalRequest;
        $this->transaction = $transaction;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $amount = number_format($this->transaction->amount ?? 0, 2, ',', ' ');
        $equipmentName = $this->rentalRequest->equipment?->name ?? 'Équipement';
        $clientName = $this->rentalRequest->client?->user?->name ?? 'Un client';
        
        return (new MailMessage)
            ->subject('💰 Paiement location reçu - ' . $amount . ' €')
            ->greeting('Bonjour ' . ($notifiable->name ?? 'Prestataire') . ' !')
            ->line('Vous avez reçu un paiement pour la location de **' . $equipmentName . '**.')
            ->line('**Client :** ' . $clientName)
            ->line('**Montant :** ' . $amount . ' €')
            ->line('**Référence :** Demande #' . ($this->rentalRequest->request_number ?? $this->rentalRequest->id))
            ->action('Voir la demande', route('prestataire.equipment-rental-requests.show', $this->rentalRequest))
            ->line('Merci d\'utiliser TaPrestation !');
    }

    public function toArray($notifiable): array
    {
        $amount = number_format($this->transaction->amount ?? 0, 2, ',', ' ');
        $equipmentName = $this->rentalRequest->equipment?->name ?? 'Équipement';
        $clientName = $this->rentalRequest->client?->user?->name ?? 'Un client';
        
        return [
            'type' => 'equipment_rental_payment_received',
            'title' => 'Paiement location reçu',
            'message' => $clientName . ' a payé ' . $amount . ' € pour la location de ' . $equipmentName,
            'rental_request_id' => $this->rentalRequest->id,
            'transaction_id' => $this->transaction->id,
            'amount' => $this->transaction->amount,
            'equipment_name' => $equipmentName,
            'client_name' => $clientName,
            'icon' => 'fa-tools',
            'color' => 'green',
            'url' => route('prestataire.equipment-rental-requests.show', $this->rentalRequest),
        ];
    }
}
