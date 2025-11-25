<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
        $senderName = $this->message->sender->name ?? 'Un utilisateur';
        $url = route('messaging.show', $this->message->sender->id);
        
        return (new MailMessage)
            ->subject('Nouveau message reçu')
            ->greeting('Bonjour ' . $notifiable->name . '!')
            ->line($senderName . ' vous a envoyé un nouveau message.')
            ->line('Message: "' . \Str::limit($this->message->content, 100) . '"')
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
        return [
            'type' => 'new_message',
            'message_id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name ?? 'Utilisateur',
            'title' => 'Nouveau message reçu',
            'message' => 'Vous avez reçu un nouveau message de ' . ($this->message->sender->name ?? 'un utilisateur'),
            'content_preview' => \Str::limit($this->message->content, 50),
            'url' => route('messaging.show', $this->message->sender->id)
        ];
    }
}