<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpired extends Notification implements ShouldQueue
{
    use Queueable;

    protected UserSubscription $subscription;

    public function __construct(UserSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('❌ Votre abonnement a expiré')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Votre abonnement prestataire a expiré.')
            ->line('**Conséquences :**')
            ->line('• Votre profil n\'est plus visible')
            ->line('• Vous ne pouvez plus recevoir de réservations')
            ->line('• Vos services sont désactivés')
            ->line('Renouvelez votre abonnement pour retrouver l\'accès à toutes les fonctionnalités !')
            ->action('Renouveler maintenant', route('prestataire.subscription.payment'))
            ->line('Vos données et services sont conservés. Vous les retrouverez après renouvellement.')
            ->salutation('L\'équipe TaPrestation');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_expired',
            'title' => 'Abonnement expiré',
            'message' => 'Votre abonnement a expiré. Renouvelez-le pour retrouver l\'accès à votre espace prestataire.',
            'subscription_id' => $this->subscription->id,
            'expired_at' => $this->subscription->current_period_end?->toISOString(),
            'url' => route('prestataire.subscription.payment'),
        ];
    }
}
