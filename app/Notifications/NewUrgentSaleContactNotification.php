<?php

namespace App\Notifications;

use App\Models\UrgentSaleContact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Support\TableExistenceCache;
class NewUrgentSaleContactNotification extends Notification
{
    use Queueable;
use App\Support\TableExistenceCache;

    public $contact;

    /**
     * Create a new notification instance.
     */
    public function __construct(UrgentSaleContact $contact)
    {
        $this->contact = $contact;
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
        
        // Note: Les notifications push sont gérées automatiquement par le listener
        // SendOneSignalPush après l'envoi de la notification database
        
        return $channels;
    }
    
    /**
     * Send push notification
     */
    protected function sendPushNotification($deviceToken, $notifiable)
    {
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $senderName = $this->contact->user->name ?? $this->contact->name ?? 'Quelqu\'un';
            $productTitle = $this->contact->urgentSale->title ?? 'votre annonce';
            
            $notificationService->sendPushNotification(
                $deviceToken,
                '💬 Nouveau message - Annonce',
                $senderName . ' vous a contacté pour "' . mb_substr($productTitle, 0, 30) . '"',
                [
                    'contact_id' => $this->contact->id,
                    'urgent_sale_id' => $this->contact->urgent_sale_id,
                    'type' => 'new_urgent_sale_contact'
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Push notification failed for urgent sale contact: ' . $e->getMessage());
        }
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $senderName = $this->contact->user->name ?? $this->contact->name ?? 'Quelqu\'un';
        $productTitle = $this->contact->urgentSale->title ?? 'votre annonce';
        
        return (new MailMessage)
            ->subject('💬 Nouveau message pour votre annonce - ' . $productTitle)
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line($senderName . ' vous a envoyé un message concernant votre annonce :')
            ->line('**' . $productTitle . '**')
            ->line('---')
            ->line('"' . $this->contact->message . '"')
            ->line('---')
            ->action('Voir et répondre', route('prestataire.urgent-sales.contacts', $this->contact->urgent_sale_id))
            ->line('Connectez-vous pour répondre à ce message.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        $senderName = $this->contact->user->name ?? $this->contact->name ?? 'Quelqu\'un';
        $productTitle = $this->contact->urgentSale->title ?? 'votre annonce';
        
        return [
            'type' => 'new_urgent_sale_contact',
            'contact_id' => $this->contact->id,
            'urgent_sale_id' => $this->contact->urgent_sale_id,
            'sender_name' => $senderName,
            'product_title' => $productTitle,
            'message' => '💬 ' . $senderName . ' vous a contacté pour "' . $productTitle . '"',
            'url' => route('prestataire.urgent-sales.contacts', $this->contact->urgent_sale_id),
        ];
    }
}
