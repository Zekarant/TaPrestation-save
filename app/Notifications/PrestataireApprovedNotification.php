<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Prestataire;

class PrestataireApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $prestataire;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\Prestataire  $prestataire
     * @return void
     */
    public function __construct(Prestataire $prestataire)
    {
        $this->prestataire = $prestataire;
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
        $url = route('prestataire.dashboard');
        
        return (new MailMessage)
            ->subject('Félicitations ! Votre compte prestataire a été approuvé')
            ->greeting('Bonjour ' . $notifiable->name . '!')
            ->line('Excellente nouvelle ! Votre demande de validation en tant que prestataire a été approuvée.')
            ->line('Vous pouvez maintenant:')
            ->line('• Consulter et répondre aux demandes de clients')
            ->line('• Proposer vos services')
            ->line('• Gérer vos offres et missions')
            ->action('Accéder à votre tableau de bord', $url)
            ->line('Bienvenue dans la communauté des prestataires !');
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
            'type' => 'prestataire_approved',
            'prestataire_id' => $this->prestataire->id,
            'title' => 'Compte prestataire approuvé',
            'message' => 'Félicitations ! Votre compte prestataire a été validé. Vous pouvez maintenant proposer vos services.',
            'url' => route('prestataire.dashboard')
        ];
    }
}