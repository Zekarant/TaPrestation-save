<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $booking;
    public $rejectionReason;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\Booking  $booking
     * @param  string|null  $rejectionReason
     * @return void
     */
    public function __construct(Booking $booking, $rejectionReason = null)
    {
        $this->booking = $booking;
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
        $url = route('client.bookings.show', $this->booking);
        $mailMessage = (new MailMessage)
                    ->subject('Réservation refusée')
                    ->line('Nous sommes désolés de vous informer que votre réservation a été refusée par le prestataire.')
                    ->line('Prestataire : ' . $this->booking->prestataire->user->name)
                    ->line('Service : ' . ($this->booking->service ? $this->booking->service->title : 'Service personnalisé'));
        
        if ($this->rejectionReason) {
            $mailMessage->line('Raison : ' . $this->rejectionReason);
        }
        
        return $mailMessage->action('Voir les détails', $url)
                          ->line('N\'hésitez pas à contacter le prestataire pour plus d\'informations ou à rechercher d\'autres prestataires disponibles.');
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
            'booking_id' => $this->booking->id,
            'prestataire_name' => $this->booking->prestataire->user->name,
            'service_title' => $this->booking->service ? $this->booking->service->title : 'Service personnalisé',
            'rejection_reason' => $this->rejectionReason,
            'message' => 'Votre réservation avec ' . $this->booking->prestataire->user->name . ' a été refusée.' . ($this->rejectionReason ? ' Raison : ' . $this->rejectionReason : ''),
            'url' => route('client.bookings.show', $this->booking->id),
            'type' => 'booking_rejected'
        ];
    }
}