<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCashPaymentConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $clientName = $this->booking->client->name ?? 'Un client';
        $serviceName = $this->booking->service->name ?? 'Service';
        $amount = number_format($this->booking->total_price, 2);
        $date = $this->booking->start_datetime->format('d/m/Y à H:i');

        return (new MailMessage)
            ->subject("💵 Paiement en espèces confirmé - Réservation #{$this->booking->booking_number}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("**{$clientName}** a confirmé un paiement en espèces pour votre service.")
            ->line("**Service:** {$serviceName}")
            ->line("**Date:** {$date}")
            ->line("**Montant à percevoir:** {$amount} €")
            ->line('')
            ->line("⚠️ **Important:** Le client paiera en espèces lors de la prestation. Assurez-vous de pouvoir rendre la monnaie si nécessaire.")
            ->action('Voir la réservation', route('prestataire.bookings.show', $this->booking))
            ->line('Merci de votre confiance !');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'booking_cash_payment',
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'amount' => $this->booking->total_price,
            'client_name' => $this->booking->client->name ?? 'Client',
            'service_name' => $this->booking->service->name ?? 'Service',
            'message' => 'Paiement en espèces confirmé pour la réservation #' . $this->booking->booking_number,
            'icon' => '💵',
        ];
    }
}
