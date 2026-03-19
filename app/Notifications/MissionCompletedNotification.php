<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;

use App\Support\TableExistenceCache;
class MissionCompletedNotification extends Notification
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
        
        // Vérifier les préférences de notification
        $settings = null;
        if (TableExistenceCache::has('notification_settings')) {
            $settings = \App\Models\NotificationSetting::where('user_id', $notifiable->id)->first();
        }
        
        // Envoyer email si activé
        $emailEnabled = $settings ? ($settings->email_notifications && $settings->booking_notifications) : true;
        if ($emailEnabled && $notifiable->email) {
            $channels[] = 'mail';
        }
        
        // Push notifications handled by SendOneSignalPush listener
        
        return $channels;
    }
    
    /**
     * Send push notification
     */
    protected function sendPushNotification($deviceToken, $notifiable)
    {
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->sendPushNotification(
                $deviceToken,
                '🎉 Mission terminée',
                'Le client a marqué la mission comme terminée',
                ['booking_id' => $this->booking->id, 'type' => 'mission_completed']
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
        $url = route('prestataire.bookings.index');
        
        return (new MailMessage)
            ->subject('Mission marquée comme terminée')
            ->greeting('Bonjour ' . $notifiable->name . '!')
            ->line('Le client a marqué la mission comme terminée.')
            ->line('Vous pouvez maintenant recevoir une évaluation pour cette mission.')
            ->action('Voir vos réservations', $url)
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
            'title' => 'Mission terminée',
            'message' => 'Le client a marqué la mission comme terminée',
            'type' => 'mission_completed',
            'url' => route('prestataire.bookings.index')
        ];
    }
}