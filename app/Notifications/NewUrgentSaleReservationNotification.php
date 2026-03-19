<?php

namespace App\Notifications;

use App\Models\UrgentSaleReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Support\TableExistenceCache;
class NewUrgentSaleReservationNotification extends Notification
{
    use Queueable;
use App\Support\TableExistenceCache;

    public $reservation;

    /**
     * Create a new notification instance.
     */
    public function __construct(UrgentSaleReservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Get the notification's delivery channels.
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
        $emailEnabled = $settings ? ($settings->email_notifications ?? true) : true;
        
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
            $clientName = $this->reservation->client->name ?? 'Un client';
            $productTitle = $this->reservation->urgentSale->title ?? 'votre annonce';
            $quantity = $this->reservation->quantity;
            
            $notificationService->sendPushNotification(
                $deviceToken,
                '🛒 Nouvelle demande de réservation',
                $clientName . ' souhaite réserver ' . $quantity . 'x ' . $productTitle,
                [
                    'reservation_id' => $this->reservation->id,
                    'urgent_sale_id' => $this->reservation->urgent_sale_id,
                    'type' => 'new_urgent_sale_reservation'
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Push notification failed for urgent sale reservation: ' . $e->getMessage());
        }
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $clientName = $this->reservation->client->name ?? 'Un client';
        $productTitle = $this->reservation->urgentSale->title ?? 'votre annonce';
        $quantity = $this->reservation->quantity;
        $total = number_format($quantity * $this->reservation->urgentSale->price, 2) . '€';
        
        return (new MailMessage)
            ->subject('🛒 Nouvelle demande de réservation - ' . $productTitle)
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line($clientName . ' souhaite réserver **' . $quantity . ' unité(s)** de votre annonce :')
            ->line('**' . $productTitle . '**')
            ->line('Total estimé : **' . $total . '**')
            ->line($this->reservation->message ? 'Message du client : "' . $this->reservation->message . '"' : '')
            ->action('Voir la demande', route('prestataire.reservations.index'))
            ->line('Connectez-vous pour confirmer ou refuser cette réservation.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        $clientName = $this->reservation->client->name ?? 'Un client';
        $productTitle = $this->reservation->urgentSale->title ?? 'votre annonce';
        $quantity = $this->reservation->quantity;
        
        return [
            'type' => 'new_urgent_sale_reservation',
            'reservation_id' => $this->reservation->id,
            'urgent_sale_id' => $this->reservation->urgent_sale_id,
            'client_id' => $this->reservation->client_id,
            'client_name' => $clientName,
            'product_title' => $productTitle,
            'quantity' => $quantity,
            'message' => '🛒 ' . $clientName . ' veut réserver ' . $quantity . 'x ' . $productTitle,
            'url' => route('prestataire.reservations.index'),
        ];
    }
}
