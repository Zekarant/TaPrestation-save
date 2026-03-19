<?php

namespace App\Notifications;

use App\Models\EquipmentRental;
use Illuminate\Notifications\Notification;

/**
 * Notification de rappel 4h avant une location d'équipement.
 * Envoyée au client ET au prestataire.
 */
class EquipmentRentalReminder4h extends Notification
{
    public EquipmentRental $rental;
    public string $recipientType; // 'client' ou 'prestataire'

    public function __construct(EquipmentRental $rental, string $recipientType = 'client')
    {
        $this->rental = $rental;
        $this->recipientType = $recipientType;
    }

    public function via($notifiable): array
    {
        // Push géré par SendOneSignalPush après l'envoi de la notification database.
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $startDate = $this->rental->start_date;
        $isClient = $this->recipientType === 'client';

        // URL différente selon le destinataire
        $url = $isClient
            ? route('client.equipment-rentals.show', $this->rental)
            : route('prestataire.equipment-rentals.show', $this->rental);

        // Message personnalisé selon le destinataire
        if ($isClient) {
            $title = '⏰ Rappel : votre location démarre bientôt';
            $message = 'Votre location #' . $this->rental->rental_number . ' commence demain. Pensez à récupérer l\'équipement !';
        } else {
            $title = '⏰ Rappel : location à préparer';
            $message = 'Location #' . $this->rental->rental_number . ' commence demain. Préparez l\'équipement !';
        }

        return [
            'type' => 'equipment_rental_reminder_4h',
            'title' => $title,
            'rental_id' => $this->rental->id,
            'rental_number' => $this->rental->rental_number,
            'client_name' => $this->rental->client?->name,
            'prestataire_name' => $this->rental->prestataire?->nom ?? $this->rental->prestataire?->user?->name,
            'equipment_name' => $this->rental->equipment?->name,
            'start_date' => $startDate?->toIso8601String(),
            'recipient_type' => $this->recipientType,
            'message' => $message,
            'url' => $url,
        ];
    }
}
