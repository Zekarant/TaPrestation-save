<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReviewNotification extends Notification
{

    protected $review;

    /**
     * Create a new notification instance.
     */
    public function __construct(Review $review)
    {
        $this->review = $review;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $clientName = $this->review->client->name ?? 'Un client';
        $rating = $this->review->rating;
        
        return (new MailMessage)
            ->subject('Nouvelle évaluation reçue')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line($clientName . ' a laissé une évaluation sur votre prestation.')
            ->line('Note: ' . $rating . '/5')
            ->line('Commentaire: "' . $this->review->comment . '"')
            ->action('Voir l\'évaluation', route('prestataire.reviews.index'))
            ->line('Merci d\'utiliser notre plateforme !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $clientName = $this->review->client->name ?? 'Un client';
        
        return [
            'title' => 'Nouvelle évaluation',
            'message' => $clientName . ' a laissé une évaluation de ' . $this->review->rating . '/5 sur votre prestation.',
            'review_id' => $this->review->id,
            'client_id' => $this->review->client_id,
        ];
    }
}