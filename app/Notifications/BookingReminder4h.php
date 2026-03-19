<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Notification;

/**
 * Notification de rappel 4h avant une réservation de service.
 * Envoyée au client ET au prestataire.
 */
class BookingReminder4h extends Notification
{
    public Booking $booking;
    public string $recipientType; // 'client' ou 'prestataire'

    public function __construct(Booking $booking, string $recipientType = 'client')
    {
        $this->booking = $booking;
        $this->recipientType = $recipientType;
    }

    public function via($notifiable): array
    {
        // Push géré par SendOneSignalPush après l'envoi de la notification database.
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $startDatetime = $this->booking->start_datetime;
        $timeText = $startDatetime ? $startDatetime->format('H:i') : null;
        $isClient = $this->recipientType === 'client';

        // URL différente selon le destinataire
        $url = $isClient
            ? route('client.bookings.show', $this->booking)
            : route('prestataire.bookings.show', $this->booking);

        // Message personnalisé selon le destinataire
        if ($isClient) {
            $title = '⏰ Rappel : votre rendez-vous dans 4h';
            $message = $timeText
                ? 'Votre rendez-vous #' . $this->booking->booking_number . ' est prévu à ' . $timeText . '. Pensez à vous préparer !'
                : 'Votre rendez-vous #' . $this->booking->booking_number . ' est prévu bientôt !';
        } else {
            $title = '⏰ Rappel : rendez-vous dans 4h';
            $message = $timeText
                ? 'Rendez-vous #' . $this->booking->booking_number . ' prévu à ' . $timeText . '. Préparez-vous !'
                : 'Rendez-vous #' . $this->booking->booking_number . ' prévu bientôt !';
        }

        return [
            'type' => 'booking_reminder_4h',
            'title' => $title,
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'client_name' => $this->booking->client?->name,
            'prestataire_name' => $this->booking->prestataire?->nom ?? $this->booking->prestataire?->user?->name,
            'service_name' => $this->booking->service?->title,
            'start_datetime' => $startDatetime?->toIso8601String(),
            'recipient_type' => $this->recipientType,
            'message' => $message,
            'url' => $url,
        ];
    }
}
