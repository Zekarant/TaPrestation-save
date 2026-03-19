<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $messageContent;
    public $actionUrl;
    public $actionText;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $title, $messageContent, $actionUrl = null, $actionText = null)
    {
        $this->user = $user;
        $this->title = $title;
        $this->messageContent = $messageContent;
        $this->actionUrl = $actionUrl;
        $this->actionText = $actionText ?? 'Voir les détails';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
