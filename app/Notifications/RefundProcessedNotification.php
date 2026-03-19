<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;
    protected $amount;
    protected $reason;

    public function __construct(Booking $booking, float $amount, ?string $reason = null)
    {
        $this->booking = $booking;
        $this->amount = $amount;
        $this->reason = $reason;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $serviceName = $this->booking->service?->name ?? 'Service';
        $prestataireName = $this->booking->prestataire?->company_name ?? 'Prestataire';
        
        return (new MailMessage)
            ->subject('💰 Remboursement effectué - ' . $serviceName)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Nous vous informons qu'un remboursement a été effectué suite au refus de votre réservation.")
            ->line('')
            ->line("**Détails du remboursement :**")
            ->line("• Service : {$serviceName}")
            ->line("• Prestataire : {$prestataireName}")
            ->line("• Montant remboursé : " . number_format($this->amount, 2, ',', ' ') . " €")
            ->when($this->reason, function ($message) {
                return $message->line("• Raison : " . $this->reason);
            })
            ->line('')
            ->line("Le remboursement sera crédité sur votre moyen de paiement initial dans un délai de 5 à 10 jours ouvrés.")
            ->action('Voir mes réservations', url('/bookings'))
            ->line('Merci de votre confiance.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'refund_processed',
            'booking_id' => $this->booking->id,
            'service_name' => $this->booking->service?->name ?? 'Service',
            'prestataire_name' => $this->booking->prestataire?->company_name ?? 'Prestataire',
            'amount' => $this->amount,
            'reason' => $this->reason,
            'title' => '💰 Remboursement effectué',
            'message' => 'Votre remboursement de ' . number_format($this->amount, 2, ',', ' ') . ' € a été traité.',
            'url' => route('bookings.show.universal', $this->booking),
        ];
    }
}
