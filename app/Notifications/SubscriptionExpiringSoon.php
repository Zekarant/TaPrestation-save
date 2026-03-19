<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringSoon extends Notification implements ShouldQueue
{
    use Queueable;

    protected UserSubscription $subscription;
    protected int $daysRemaining;

    public function __construct(UserSubscription $subscription, int $daysRemaining)
    {
        $this->subscription = $subscription;
        $this->daysRemaining = $daysRemaining;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $endDate = $this->subscription->current_period_end?->format('d/m/Y') ?? 'N/A';
        
        return (new MailMessage)
            ->subject('⚠️ Votre abonnement expire dans ' . $this->daysRemaining . ' jour(s)')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Votre abonnement prestataire arrive à expiration.')
            ->line('**Date d\'expiration :** ' . $endDate)
            ->line('**Jours restants :** ' . $this->daysRemaining)
            ->line('Sans renouvellement, vous perdrez l\'accès à :')
            ->line('• La gestion de vos services')
            ->line('• Les nouvelles réservations')
            ->line('• La messagerie avec les clients')
            ->action('Renouveler maintenant', route('prestataire.subscription.payment'))
            ->line('Ne perdez pas vos clients, renouvelez dès maintenant !')
            ->salutation('L\'équipe TaPrestation');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_expiring',
            'title' => 'Abonnement expire bientôt',
            'message' => 'Votre abonnement expire dans ' . $this->daysRemaining . ' jour(s). Pensez à le renouveler !',
            'subscription_id' => $this->subscription->id,
            'days_remaining' => $this->daysRemaining,
            'ends_at' => $this->subscription->current_period_end?->toISOString(),
            'url' => route('prestataire.subscription.payment'),
        ];
    }
}
