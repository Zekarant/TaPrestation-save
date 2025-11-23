<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookingNotification extends Notification
{
    use Queueable;

    public $booking;

    /**
     * Create a new notification instance.
     *
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
        return (new MailMessage)
                    ->line('You have a new booking.')
                    ->action('View Booking', url('/bookings/' . $this->booking->id))
                    ->line('Thank you for using our application!');
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
            'client_name' => $this->booking->client->user->name ?? 'Client',
            'service_title' => $this->booking->service->title ?? 'Service',
            'booking_date' => $this->booking->start_datetime ? $this->booking->start_datetime->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            'title' => 'Nouvelle réservation',
            'message' => 'Vous avez reçu une nouvelle réservation pour ' . ($this->booking->service->title ?? 'un service'),
            'url' => route('bookings.show', $this->booking->id),
            'type' => 'new_booking'
        ];
    }
}