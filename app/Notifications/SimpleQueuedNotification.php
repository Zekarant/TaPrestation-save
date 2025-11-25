<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SimpleQueuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;
    protected $actionUrl;
    protected $actionText;

    /**
     * Create a new notification instance.
     *
     * @param string $message
     * @param string|null $actionUrl
     * @param string|null $actionText
     * @return void
     */
    public function __construct($message, $actionUrl = null, $actionText = null)
    {
        $this->message = $message;
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Simple Notification')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($this->message);

        if ($this->actionUrl && $this->actionText) {
            $mail->action($this->actionText, $this->actionUrl);
        }

        $mail->line('Thank you for using our application!');

        return $mail;
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
            'message' => $this->message,
            'action_url' => $this->actionUrl,
            'action_text' => $this->actionText,
        ];
    }
}