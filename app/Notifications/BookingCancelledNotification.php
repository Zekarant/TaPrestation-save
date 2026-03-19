<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;

use App\Support\TableExistenceCache;
class BookingCancelledNotification extends Notification
{
    use Queueable;
use App\Support\TableExistenceCache;

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
        $channels = ['database'];
        
        $settings = null;
        if (TableExistenceCache::has('notification_settings')) {
            $settings = \App\Models\NotificationSetting::where('user_id', $notifiable->id)->first();
        }
        
        $emailEnabled = $settings ? ($settings->email_notifications && $settings->booking_notifications) : true;
        if ($emailEnabled && $notifiable->email) {
            $channels[] = 'mail';
        }
        
        // Note: Les notifications push sont gérées automatiquement par le listener
        // SendOneSignalPush après l'envoi de la notification database
        
        return $channels;
    }
    
    protected function sendWebPushNotification($notifiable)
    {
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->sendWebPushToUser(
                $notifiable,
                '❌ Réservation annulée',
                'La réservation #' . ($this->booking->booking_number ?? $this->booking->id) . ' a été annulée',
                ['booking_id' => $this->booking->id, 'type' => 'booking_cancelled', 'url' => route('bookings.show.universal', $this->booking)]
            );
        } catch (\Exception $e) {
            \Log::error('Web Push notification failed: ' . $e->getMessage());
        }
    }
    
    protected function sendPushNotification($deviceToken, $notifiable)
    {
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->sendPushNotification(
                $deviceToken,
                '❌ Réservation annulée',
                'La réservation #' . ($this->booking->booking_number ?? $this->booking->id) . ' a été annulée',
                ['booking_id' => $this->booking->id, 'type' => 'booking_cancelled']
            );
        } catch (\Exception $e) {
            \Log::error('Push notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = route('bookings.show.universal', $this->booking);
        $cancelledBy = $this->booking->cancellation_reason === 'Annulée par le client' ? 'le client' : 'le prestataire';
        $reason = $this->booking->cancellation_reason;
        
        return (new MailMessage)
            ->subject('Une réservation a été annulée')
            ->greeting('Bonjour ' . $notifiable->name . '!')
            ->line('La réservation #' . $this->booking->booking_number . ' a été annulée par ' . $cancelledBy . '.')
            ->line('Service: ' . $this->booking->service->name)
            ->line('Date: ' . $this->booking->start_datetime->format('d/m/Y à H:i'))
            ->line('Raison de l\'annulation: ' . $reason)
            ->action('Voir les détails', $url)
            ->line('Merci d\'utiliser notre plateforme!');
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
            'title' => 'Réservation annulée',
            'message' => 'La réservation #' . $this->booking->booking_number . ' a été annulée',
            'reason' => $this->booking->cancellation_reason,
            'type' => 'booking_cancelled',
            'url' => route('bookings.show.universal', $this->booking)
        ];
    }
}