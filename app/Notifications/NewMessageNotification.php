<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

use App\Support\TableExistenceCache;
class NewMessageNotification extends Notification
{
    use Queueable;
use App\Support\TableExistenceCache;

    protected $message;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\Message  $message
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
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
        
        // Vérifier les préférences de notification de l'utilisateur
        $settings = null;
        if (TableExistenceCache::has('notification_settings')) {
            $settings = \App\Models\NotificationSetting::where('user_id', $notifiable->id)->first();
        }
        
        // Par défaut, envoyer les emails si pas de settings
        $emailEnabled = $settings ? $settings->email_notifications && $settings->message_notifications : true;
        
        if ($emailEnabled && $notifiable->email) {
            $channels[] = 'mail';
        }
        
        // Note: Les notifications push sont gérées automatiquement par le listener
        // SendOneSignalPush après l'envoi de la notification database
        
        return $channels;
    }
    
    protected function sendPushNotification($deviceToken, $notifiable)
    {
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $senderName = $this->message->sender->name ?? 'Quelqu\'un';
            $notificationService->sendPushNotification(
                $deviceToken,
                '💬 Nouveau message',
                $senderName . ' vous a envoyé un message',
                ['message_id' => $this->message->id, 'type' => 'new_message']
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Push notification failed: ' . $e->getMessage());
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
        $senderName = $this->message->sender->name ?? 'Un utilisateur';
        $senderId = $this->message->sender_id;
        $url = config('app.url') . '/client/messaging/' . $senderId;
        
        return (new MailMessage)
            ->subject('💬 Nouveau message reçu')
            ->greeting('Bonjour ' . $notifiable->name . '!')
            ->line($senderName . ' vous a envoyé un nouveau message.')
            ->line('Message: "' . Str::limit($this->message->content, 100) . '"')
            ->action('Voir le message', $url)
            ->line('Répondez rapidement pour maintenir une bonne communication!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $senderId = $this->message->sender_id;
        
        // Utiliser une URL absolue avec le préfixe /client/
        $url = config('app.url') . '/client/messaging/' . $senderId;
        
        return [
            'type' => 'new_message',
            'message_id' => $this->message->id,
            'sender_id' => $senderId,
            'sender_name' => $this->message->sender->name ?? 'Utilisateur',
            'title' => '💬 Nouveau message',
            'message' => 'Vous avez reçu un nouveau message de ' . ($this->message->sender->name ?? 'un utilisateur'),
            'content_preview' => Str::limit($this->message->content, 50),
            'url' => $url
        ];
    }
}