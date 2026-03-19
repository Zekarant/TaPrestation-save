<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Support\TableExistenceCache;
class NewBookingNotification extends Notification
{
    use Queueable;
use App\Support\TableExistenceCache;

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
        $channels = ['database'];
        
        // Vérifier les préférences de notification
        $settings = null;
        if (TableExistenceCache::has('notification_settings')) {
            $settings = \App\Models\NotificationSetting::where('user_id', $notifiable->id)->first();
        }
        
        // Envoyer email si activé (par défaut: oui)
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
            $clientName = $this->booking->client->user->name ?? 'Un client';
            $serviceTitle = $this->booking->service->title ?? 'un service';
            $notificationService->sendPushNotification(
                $deviceToken,
                '📅 Nouvelle réservation',
                $clientName . ' demande une réservation pour ' . $serviceTitle,
                ['booking_id' => $this->booking->id, 'type' => 'new_booking']
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
        $clientName = $this->booking->client->user->name ?? 'Un client';
        $serviceTitle = $this->booking->service->title ?? 'un service';
        $bookingDate = $this->booking->start_datetime ? $this->booking->start_datetime->format('d/m/Y à H:i') : 'Date à définir';
        
        // Cette notification est envoyée au prestataire, donc on utilise la route prestataire
        $url = route('prestataire.bookings.show', $this->booking->id);
        
        return (new MailMessage)
                    ->subject('📅 Nouvelle réservation reçue')
                    ->greeting('Bonjour ' . $notifiable->name . ' !')
                    ->line('Vous avez reçu une nouvelle demande de réservation.')
                    ->line('**Client:** ' . $clientName)
                    ->line('**Service:** ' . $serviceTitle)
                    ->line('**Date:** ' . $bookingDate)
                    ->action('Voir la réservation', $url)
                    ->line('Veuillez confirmer ou refuser cette réservation.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        // Cette notification est envoyée au prestataire
        $url = route('prestataire.bookings.show', $this->booking->id);
        
        return [
            'booking_id' => $this->booking->id,
            'client_name' => $this->booking->client->user->name ?? 'Client',
            'service_title' => $this->booking->service->title ?? 'Service',
            'booking_date' => $this->booking->start_datetime ? $this->booking->start_datetime->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            'title' => 'Nouvelle réservation',
            'message' => 'Vous avez reçu une nouvelle réservation pour ' . ($this->booking->service->title ?? 'un service'),
            'url' => $url,
            'type' => 'new_booking'
        ];
    }
}