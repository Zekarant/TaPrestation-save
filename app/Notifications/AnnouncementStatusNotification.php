<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnnouncementStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $announcement;
    protected $status;
    protected $reason;

    /**
     * Create a new notification instance.
     *
     * @param  mixed  $announcement
     * @param  string  $status
     * @param  string|null  $reason
     * @return void
     */
    public function __construct($announcement, $status, $reason = null)
    {
        $this->announcement = $announcement;
        $this->status = $status; // 'approved' ou 'rejected'
        $this->reason = $reason;
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
        $isApproved = $this->status === 'approved';
        $subject = $isApproved ? 'Votre annonce a été validée' : 'Votre annonce a été refusée';
        
        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting('Bonjour ' . $notifiable->name . '!');
            
        if ($isApproved) {
            $mailMessage
                ->line('Excellente nouvelle ! Votre annonce a été validée par notre équipe.')
                ->line('Elle est maintenant visible par tous les utilisateurs de la plateforme.')
                ->action('Voir votre annonce', $this->getAnnouncementUrl())
                ->line('Merci de contribuer à notre communauté !');
        } else {
            $mailMessage
                ->line('Nous regrettons de vous informer que votre annonce n\'a pas pu être validée.')
                ->line('Raison: ' . ($this->reason ?? 'Non conforme aux conditions d\'utilisation'))
                ->line('Vous pouvez modifier votre annonce et la soumettre à nouveau.')
                ->action('Modifier l\'annonce', $this->getAnnouncementUrl())
                ->line('Notre équipe reste à votre disposition pour toute question.');
        }
        
        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $isApproved = $this->status === 'approved';
        
        return [
            'type' => 'announcement_status',
            'announcement_id' => $this->announcement->id ?? null,
            'status' => $this->status,
            'title' => $isApproved ? 'Annonce validée' : 'Annonce refusée',
            'message' => $isApproved 
                ? 'Votre annonce a été validée et est maintenant visible.'
                : 'Votre annonce a été refusée. Raison: ' . ($this->reason ?? 'Non conforme'),
            'reason' => $this->reason,
            'url' => $this->getAnnouncementUrl()
        ];
    }

    /**
     * Get the URL for the announcement.
     *
     * @return string
     */
    private function getAnnouncementUrl()
    {
        // Adapter selon le type d'annonce (service, équipement, etc.)
        if (isset($this->announcement->id)) {
            return route('announcements.show', $this->announcement->id);
        }
        
        return route('dashboard');
    }
}