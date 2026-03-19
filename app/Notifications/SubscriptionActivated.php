<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionActivated extends Notification implements ShouldQueue
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
        $endDate = $this->subscription->current_period_end?->format('d/m/Y') ?? 'N/A';
        
        return (new MailMessage)
            ->subject('🎉 Votre abonnement est activé !')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Félicitations ! Votre abonnement prestataire est maintenant actif.')
            ->line('**Détails de votre abonnement :**')
            ->line('• Montant : ' . number_format($this->subscription->current_amount, 2) . ' ' . $this->subscription->currency)
            ->line('• Valide jusqu\'au : ' . $endDate)
            ->action('Accéder à mon espace', route('prestataire.dashboard'))
            ->line('Vous pouvez maintenant !')
            ->line('✓ Créer et gérer vos services')
            ->line('✓ Recevoir des réservations')
            ->line('✓ Échanger avec vos clients')
            ->line('✓ Accéder à vos statistiques')
            ->salutation('Bonne continuation sur TaPrestation !');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_activated',
            'title' => 'Abonnement activé',
            'message' => 'Votre abonnement est maintenant actif jusqu\'au ' . 
                $this->subscription->current_period_end?->format('d/m/Y'),
            'subscription_id' => $this->subscription->id,
            'amount' => $this->subscription->current_amount,
            'currency' => $this->subscription->currency,
            'ends_at' => $this->subscription->current_period_end?->toISOString(),
            'url' => route('prestataire.subscriptions.index'),
        ];
    }
}
