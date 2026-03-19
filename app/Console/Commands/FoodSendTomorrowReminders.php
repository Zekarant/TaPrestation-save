<?php

namespace App\Console\Commands;

use App\Models\FoodOrder;
use App\Notifications\FoodOrderReminderTomorrow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FoodSendTomorrowReminders extends Command
{
    protected $signature = 'food:send-reminders-tomorrow {--dry-run : Afficher sans exécuter}';
    protected $description = 'Envoyer un rappel au prestataire 1 jour avant la date demandée (requested_at)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $tomorrowStart = now()->addDay()->startOfDay();
        $tomorrowEnd = now()->addDay()->endOfDay();

        $this->info('🔔 Recherche des commandes food prévues demain...');

        $orders = FoodOrder::query()
            ->with(['prestataire.user', 'client'])
            ->whereNotNull('requested_at')
            ->whereBetween('requested_at', [$tomorrowStart, $tomorrowEnd])
            ->whereNull('prestataire_reminder_sent_at')
            ->whereNotIn('status', [FoodOrder::STATUS_CANCELLED, FoodOrder::STATUS_COMPLETED])
            ->get();

        if ($orders->isEmpty()) {
            $this->info('✅ Aucun rappel à envoyer.');
            return 0;
        }

        $this->info('📋 ' . $orders->count() . ' rappel(s) à envoyer');

        $sent = 0;
        $failed = 0;

        foreach ($orders as $order) {
            $this->line('  - Commande #' . $order->order_number . ' (prestataire_id=' . $order->prestataire_id . ', requested_at=' . $order->requested_at?->format('Y-m-d H:i') . ')');

            if ($dryRun) {
                $this->comment('    [DRY-RUN] Serait notifiée');
                continue;
            }

            try {
                $user = $order->prestataire?->user;
                if (!$user) {
                    $this->warn('    ⚠️ Prestataire/user introuvable, skip');
                    continue;
                }

                $user->notify(new FoodOrderReminderTomorrow($order));

                $order->update([
                    'prestataire_reminder_sent_at' => now(),
                ]);

                $sent++;
                $this->info('    ✅ Envoyé');
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Erreur rappel food (commande_id=' . $order->id . '): ' . $e->getMessage());
                $this->error('    ❌ Erreur: ' . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("📊 Résultat: {$sent} envoyé(s), {$failed} échec(s)");

        return $failed > 0 ? 1 : 0;
    }
}
