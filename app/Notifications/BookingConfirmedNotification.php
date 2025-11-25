<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\Booking  $booking
     * @return void
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
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
        $url = route('bookings.show', $this->booking);
        
        return (new MailMessage)
            ->subject('Votre réservation a été confirmée')
            ->greeting('Bonjour ' . $notifiable->name . '!')
            ->line('Bonne nouvelle ! La réservation #' . $this->booking->id . ' a été confirmée par le prestataire.')
            ->line('Service: ' . $this->booking->service->name)
            ->line('Date: ' . $this->booking->start_datetime->format('d/m/Y à H:i'))
            ->action('Voir les détails de la réservation', $url)
            ->line('Merci de votre confiance!');
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
            'title' => 'Réservation confirmée',
            'message' => 'Votre réservation pour le service ' . $this->booking->service->name . ' a été confirmée.',
            'type' => 'booking_confirmed',
            'url' => route('bookings.show', $this->booking)
        ];
    }
}