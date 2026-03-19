<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;

use App\Support\TableExistenceCache;
class BookingConfirmedNotification extends Notification
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
            $serviceName = $this->booking->service->name ?? 'Service';
            $notificationService->sendPushNotification(
                $deviceToken,
                '✅ Réservation confirmée',
                'Votre réservation pour ' . $serviceName . ' a été confirmée!',
                ['booking_id' => $this->booking->id, 'type' => 'booking_confirmed']
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
        try {
            $url = route('bookings.show.universal', $this->booking);
        } catch (\Exception $e) {
            $url = url('/bookings/' . $this->booking->id);
        }
        
        $serviceName = $this->booking->service->name ?? $this->booking->service->title ?? 'votre service';
        $bookingDate = $this->booking->start_datetime ? $this->booking->start_datetime->format('d/m/Y à H:i') : 'Date à confirmer';
        
        return (new MailMessage)
            ->subject('✅ Votre réservation a été confirmée')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Bonne nouvelle ! La réservation #' . $this->booking->id . ' a été confirmée par le prestataire.')
            ->line('**Service:** ' . $serviceName)
            ->line('**Date:** ' . $bookingDate)
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
            'url' => route('bookings.show.universal', $this->booking)
        ];
    }
}