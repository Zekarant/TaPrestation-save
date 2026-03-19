<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserSubscription;
use App\Notifications\SubscriptionExpired;
use App\Notifications\SubscriptionExpiringSoon;
use Illuminate\Console\Command;

use App\Support\TableExistenceCache;
class CheckSubscriptionExpiry extends Command
{
    protected $signature = 'subscriptions:check-expiry';
    protected $description = 'Vérifie les abonnements qui expirent et envoie des notifications';

    public function handle(): int
    {
        if (!TableExistenceCache::has('user_subscriptions')) {
            $this->warn('Table user_subscriptions n\'existe pas.');
            return 0;
        }

        $this->info('Vérification des abonnements...');

        $notifiedCount = 0;
        $expiredCount = 0;

        // Récupérer les abonnements actifs qui expirent dans les 7 prochains jours
        $expiringSubscriptions = UserSubscription::where('status', 'active')
            ->whereNotNull('current_period_end')
            ->whereBetween('current_period_end', [now(), now()->addDays(7)])
            ->with('user')
            ->get();

        foreach ($expiringSubscriptions as $subscription) {
            if (!$subscription->user) continue;

            $daysRemaining = now()->diffInDays($subscription->current_period_end, false);
            
            // Notification à J-7, J-3 et J-1
            if (in_array($daysRemaining, [7, 3, 1])) {
                // Vérifier si déjà notifié aujourd'hui
                $alreadyNotified = $subscription->user->notifications()
                    ->where('type', SubscriptionExpiringSoon::class)
                    ->where('data->subscription_id', $subscription->id)
                    ->where('data->days_remaining', $daysRemaining)
                    ->whereDate('created_at', today())
                    ->exists();

                if (!$alreadyNotified) {
                    $subscription->user->notify(new SubscriptionExpiringSoon($subscription, $daysRemaining));
                    $notifiedCount++;
                    $this->line("→ Notification envoyée à {$subscription->user->email} (expire dans {$daysRemaining} jours)");
                }
            }
        }

        // Récupérer les abonnements expirés (mais encore marqués actifs)
        $expiredSubscriptions = UserSubscription::where('status', 'active')
            ->whereNotNull('current_period_end')
            ->where('current_period_end', '<', now())
            ->with('user')
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            // Marquer comme expiré
            $subscription->update([
                'status' => 'expired',
                'auto_renew' => false,
            ]);

            if ($subscription->user) {
                // Envoyer notification d'expiration
                $subscription->user->notify(new SubscriptionExpired($subscription));
                $this->line("→ Abonnement expiré pour {$subscription->user->email}");
            }
            
            $expiredCount++;
        }

        $this->info("Terminé : {$notifiedCount} notification(s) d'expiration envoyée(s), {$expiredCount} abonnement(s) expiré(s).");

        return 0;
    }
}
