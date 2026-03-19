<?php

namespace App\Console\Commands;

use App\Models\FoodOrder;
use App\Notifications\FoodOrderReminder4h;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Envoie un rappel 4h avant les commandes food planifiées.
 * Notifie le client ET le prestataire.
 */
class FoodSendReminders4h extends Command
{
    protected $signature = 'food:send-reminders-4h {--dry-run : Afficher sans exécuter}';
    protected $description = 'Envoyer un rappel au client et prestataire 4h avant la date prévue (requested_at)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // Fenêtre de 4h : commandes prévues entre maintenant+3h30 et maintenant+4h30
        // Cela permet une marge si le cron tourne toutes les 15-30 min
        $windowStart = now()->addHours(3)->addMinutes(30);
        $windowEnd = now()->addHours(4)->addMinutes(30);

        $this->info('🔔 Recherche des commandes food prévues dans ~4h...');
        $this->info('   Fenêtre: ' . $windowStart->format('H:i') . ' - ' . $windowEnd->format('H:i'));

        $orders = FoodOrder::query()
            ->with(['prestataire.user', 'client'])
            ->whereNotNull('requested_at')
            ->whereBetween('requested_at', [$windowStart, $windowEnd])
            ->whereNotIn('status', [
                FoodOrder::STATUS_CANCELLED,
                FoodOrder::STATUS_COMPLETED,
                FoodOrder::STATUS_READY,
                FoodOrder::STATUS_DELIVERED,
            ])
            ->get();

        if ($orders->isEmpty()) {
            $this->info('✅ Aucun rappel 4h à envoyer.');
            return 0;
        }

        $this->info('📋 ' . $orders->count() . ' commande(s) à traiter');

        $clientSent = 0;
        $prestataireSent = 0;
        $failed = 0;

        foreach ($orders as $order) {
            $this->line('  - Commande #' . $order->order_number . ' (requested_at=' . $order->requested_at?->format('Y-m-d H:i') . ')');

            if ($dryRun) {
                $this->comment('    [DRY-RUN] Serait notifiée');
                continue;
            }

            // === Notification CLIENT ===
            if (!$order->client_reminder_4h_sent_at && $order->client) {
                try {
                    $order->client->notify(new FoodOrderReminder4h($order, 'client'));
                    $order->update(['client_reminder_4h_sent_at' => now()]);
                    $clientSent++;
                    $this->info('    ✅ Client notifié');
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('Erreur rappel 4h client (commande_id=' . $order->id . '): ' . $e->getMessage());
                    $this->error('    ❌ Erreur client: ' . $e->getMessage());
                }
            } elseif ($order->client_reminder_4h_sent_at) {
                $this->comment('    ⏭️ Client déjà notifié');
            } elseif (!$order->client) {
                $this->warn('    ⚠️ Pas de client associé');
            }

            // === Notification PRESTATAIRE ===
            $prestataireUser = $order->prestataire?->user;
            if (!$order->prestataire_reminder_4h_sent_at && $prestataireUser) {
                try {
                    $prestataireUser->notify(new FoodOrderReminder4h($order, 'prestataire'));
                    $order->update(['prestataire_reminder_4h_sent_at' => now()]);
                    $prestataireSent++;
                    $this->info('    ✅ Prestataire notifié');
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('Erreur rappel 4h prestataire (commande_id=' . $order->id . '): ' . $e->getMessage());
                    $this->error('    ❌ Erreur prestataire: ' . $e->getMessage());
                }
            } elseif ($order->prestataire_reminder_4h_sent_at) {
                $this->comment('    ⏭️ Prestataire déjà notifié');
            } elseif (!$prestataireUser) {
                $this->warn('    ⚠️ Pas d\'utilisateur prestataire');
            }
        }

        $this->newLine();
        $this->info("📊 Résultat: {$clientSent} client(s), {$prestataireSent} prestataire(s), {$failed} échec(s)");

        return $failed > 0 ? 1 : 0;
    }
}
