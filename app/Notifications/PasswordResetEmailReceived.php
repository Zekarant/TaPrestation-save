<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetEmailReceived extends Notification
{
    use Queueable;

    protected $resetLink;
    protected $emailAddress;
    protected $receivedAt;

    /**
     * Create a new notification instance.
     */
    public function __construct($resetLink, $emailAddress, $receivedAt)
    {
        $this->resetLink = $resetLink;
        $this->emailAddress = $emailAddress;
        $this->receivedAt = $receivedAt;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Password Reset Email Received')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A password reset email has been received for the email address: ' . $this->emailAddress)
            ->line('The email was received at: ' . $this->receivedAt)
            ->action('Reset Password', $this->resetLink)
            ->line('If you did not request this password reset, please ignore this email.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'reset_link' => $this->resetLink,
            'email_address' => $this->emailAddress,
            'received_at' => $this->receivedAt,
            'message' => 'A password reset email has been received for ' . $this->emailAddress,
        ];
    }
}