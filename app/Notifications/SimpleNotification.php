<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class SimpleNotification extends Notification
{
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
        return ['database'];
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