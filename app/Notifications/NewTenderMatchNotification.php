<?php

namespace App\Notifications;

use App\Models\TenderRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTenderMatchNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected TenderRequest $tender;
    protected int $matchScore;

    /**
     * Create a new notification instance.
     */
    public function __construct(TenderRequest $tender, int $matchScore)
    {
        $this->tender = $tender;
        $this->matchScore = $matchScore;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🎯 Nouvel appel d\'offre correspondant à votre profil')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Un nouvel appel d\'offre correspond à vos compétences.')
            ->line('**' . $this->tender->title . '**')
            ->line('📍 ' . $this->tender->city)
            ->line('📅 ' . $this->tender->start_date->format('d/m/Y'))
            ->line('💰 ' . $this->tender->budget_display)
            ->line('Score de correspondance : ' . $this->matchScore . '%')
            ->action('Voir l\'appel d\'offre', route('prestataire.tenders.show', $this->tender->id))
            ->line('Ne manquez pas cette opportunité !');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'tender_match',
            'tender_id' => $this->tender->id,
            'tender_reference' => $this->tender->reference,
            'tender_title' => $this->tender->title,
            'match_score' => $this->matchScore,
            'message' => 'Nouvel appel d\'offre : ' . $this->tender->title,
        ];
    }
}
