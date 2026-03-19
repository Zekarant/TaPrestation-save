<?php

namespace App\Console\Commands;

use App\Models\FoodOrder;
use App\Notifications\FoodOrderPreparing;
use App\Notifications\FoodOrderReady;
use App\Notifications\FoodOrderReadyForDriver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FoodProcessScheduledOrders extends Command
{
    protected $signature = 'food:process-scheduled {--dry-run : Afficher sans exécuter}';
    protected $description = 'Traiter automatiquement les commandes food planifiées (auto-complete si jour passé)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $now = now();
        $yesterday = now()->subDay()->endOfDay();

        $this->info('⏱️ Traitement des commandes food planifiées...');

        // 1. AUTO-COMPLETE: Commandes "scheduled" dont la date est passée (hier ou avant)
        //    Ces commandes n'ont pas été traitées le jour J → on les marque comme terminées
        $scheduledToComplete = FoodOrder::query()
            ->with(['prestataire', 'client'])
            ->whereNotNull('requested_at')
            ->where('requested_at', '<', $yesterday)
            ->where('status', FoodOrder::STATUS_SCHEDULED)
            ->get();

        $autoCompleted = 0;
        foreach ($scheduledToComplete as $order) {
            $this->line('  - autoComplete #' . $order->order_number . ' (requested_at=' . $order->requested_at?->format('Y-m-d H:i') . ', jour passé)');

            if ($dryRun) {
                continue;
            }

            try {
                $order->update([
                    'status' => FoodOrder::STATUS_COMPLETED,
                    'completed_at' => now(),
                ]);
                $autoCompleted++;
                Log::info('food:process-scheduled auto-completed order #' . $order->id . ' (scheduled date passed)');
            } catch (\Throwable $e) {
                Log::error('food:process-scheduled autoComplete failed (order_id=' . $order->id . '): ' . $e->getMessage());
            }
        }

        // 2. ACCEPTED → PREPARING: Commandes acceptées (jour J) prêtes à démarrer
        $acceptedToStart = FoodOrder::query()
            ->with(['prestataire', 'client', 'driver', 'items.foodProduct'])
            ->whereNotNull('requested_at')
            ->where('requested_at', '<=', $now)
            ->where('status', FoodOrder::STATUS_ACCEPTED)
            ->whereNotIn('status', [FoodOrder::STATUS_CANCELLED, FoodOrder::STATUS_COMPLETED])
            ->get();

        $started = 0;
        foreach ($acceptedToStart as $order) {
            if ($order->requiresExternalDriver() && !$order->hasDriverAccepted()) {
                continue;
            }

            $this->line('  - startPreparing #' . $order->order_number . ' (requested_at=' . $order->requested_at?->format('Y-m-d H:i') . ')');

            if ($dryRun) {
                continue;
            }

            try {
                $order->startPreparing();

                if ($order->client) {
                    $order->client->notify(new FoodOrderPreparing($order));
                }

                $started++;
            } catch (\Throwable $e) {
                Log::error('food:process-scheduled startPreparing failed (order_id=' . $order->id . '): ' . $e->getMessage());
            }
        }

        $preparingToReady = FoodOrder::query()
            ->with(['prestataire', 'client', 'driver', 'items.foodProduct'])
            ->whereNotNull('requested_at')
            ->where('requested_at', '<=', $now)
            ->where('status', FoodOrder::STATUS_PREPARING)
            ->whereNull('ready_at')
            ->whereNotIn('status', [FoodOrder::STATUS_CANCELLED, FoodOrder::STATUS_COMPLETED])
            ->get();

        $readied = 0;
        foreach ($preparingToReady as $order) {
            if ($order->requiresExternalDriver() && !$order->hasDriverAccepted()) {
                continue;
            }

            $prepMinutes = (int) $order->getEstimatedPreparationTime();
            if ($prepMinutes <= 0) {
                $prepMinutes = 15;
            }
            $prepMinutes = max(5, min(240, $prepMinutes));

            $base = $order->requested_at ?? $order->preparing_at ?? $order->accepted_at ?? $order->created_at;
            $due = $base ? $base->copy()->addMinutes($prepMinutes) : $now;

            if ($now->lt($due)) {
                continue;
            }

            $this->line('  - markAsReady #' . $order->order_number . ' (due=' . $due->format('Y-m-d H:i') . ')');

            if ($dryRun) {
                continue;
            }

            try {
                $order->markAsReady();

                if ($order->client) {
                    $order->client->notify(new FoodOrderReady($order));
                }

                if ($order->delivery_type === 'delivery' && $order->driver) {
                    $order->driver->notify(new FoodOrderReadyForDriver($order));
                }

                $readied++;
            } catch (\Throwable $e) {
                Log::error('food:process-scheduled markAsReady failed (order_id=' . $order->id . '): ' . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('📊 Résultat: ' . $autoCompleted . ' auto-terminée(s), ' . $started . ' passée(s) en préparation, ' . $readied . ' passée(s) en prête');

        return 0;
    }
}
