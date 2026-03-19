<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingPaymentConfirmed extends Notification implements ShouldQueue
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
        $paymentStatus = $this->booking->payment_status;

        $amountPaid = match($paymentStatus) {
            'deposit_paid' => number_format($this->booking->deposit_amount, 2),
            'paid' => $amount,
            default => $amount,
        };

        $paymentType = match($paymentStatus) {
            'deposit_paid' => 'Acompte',
            'paid' => 'Paiement complet',
            default => 'Paiement',
        };

        return (new MailMessage)
            ->subject("✅ {$paymentType} reçu - Réservation #{$this->booking->booking_number}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Bonne nouvelle ! **{$clientName}** a effectué un paiement pour votre service.")
            ->line("**Service:** {$serviceName}")
            ->line("**Date:** {$date}")
            ->line("**{$paymentType}:** {$amountPaid} €")
            ->when($paymentStatus === 'deposit_paid', function ($message) use ($amount, $amountPaid) {
                $remaining = number_format($this->booking->total_price - $this->booking->deposit_amount, 2);
                return $message->line("**Reste à percevoir:** {$remaining} €");
            })
            ->action('Voir la réservation', route('prestataire.bookings.show', $this->booking))
            ->line('Merci de votre confiance !');
    }

    public function toArray($notifiable): array
    {
        $paymentType = match($this->booking->payment_status) {
            'deposit_paid' => 'Acompte',
            'paid' => 'Paiement complet',
            default => 'Paiement',
        };

        return [
            'type' => 'booking_payment_confirmed',
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
            'payment_type' => $paymentType,
            'amount' => $this->booking->payment_status === 'deposit_paid' 
                ? $this->booking->deposit_amount 
                : $this->booking->total_price,
            'client_name' => $this->booking->client->name ?? 'Client',
            'service_name' => $this->booking->service->name ?? 'Service',
            'message' => "{$paymentType} reçu pour la réservation #{$this->booking->booking_number}",
            'icon' => '✅',
        ];
    }
}
