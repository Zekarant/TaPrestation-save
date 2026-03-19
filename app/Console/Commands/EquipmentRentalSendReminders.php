<?php

namespace App\Console\Commands;

use App\Models\EquipmentRental;
use App\Notifications\EquipmentRentalReminder4h;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Envoie un rappel la veille des locations d'équipement.
 * Notifie le client ET le prestataire.
 * 
 * Note: Pour les équipements, on envoie la veille car start_date est une date (pas datetime)
 */
class EquipmentRentalSendReminders extends Command
{
    protected $signature = 'rentals:send-reminders {--dry-run : Afficher sans exécuter}';
    protected $description = 'Envoyer un rappel au client et prestataire la veille de la location (start_date)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // Locations qui commencent demain
        $tomorrowStart = now()->addDay()->startOfDay();
        $tomorrowEnd = now()->addDay()->endOfDay();

        $this->info('🔔 Recherche des locations qui commencent demain...');

        $rentals = EquipmentRental::query()
            ->with(['prestataire.user', 'client', 'equipment'])
            ->whereNotNull('start_date')
            ->whereBetween('start_date', [$tomorrowStart, $tomorrowEnd])
            ->whereNull('client_reminder_sent_at')
            ->whereIn('status', ['confirmed', 'accepted', 'pending_pickup'])
            ->get();

        if ($rentals->isEmpty()) {
            $this->info('✅ Aucun rappel location à envoyer.');
            return 0;
        }

        $this->info('📋 ' . $rentals->count() . ' location(s) à traiter');

        $clientSent = 0;
        $prestataireSent = 0;
        $failed = 0;

        foreach ($rentals as $rental) {
            $this->line('  - Location #' . $rental->rental_number . ' (start_date=' . $rental->start_date?->format('Y-m-d') . ')');

            if ($dryRun) {
                $this->comment('    [DRY-RUN] Serait notifiée');
                continue;
            }

            // === Notification CLIENT ===
            if ($rental->client) {
                try {
                    $rental->client->notify(new EquipmentRentalReminder4h($rental, 'client'));
                    $clientSent++;
                    $this->info('    ✅ Client notifié');
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('Erreur rappel rental client (rental_id=' . $rental->id . '): ' . $e->getMessage());
                    $this->error('    ❌ Erreur client: ' . $e->getMessage());
                }
            }

            // === Notification PRESTATAIRE ===
            $prestataireUser = $rental->prestataire?->user;
            if ($prestataireUser) {
                try {
                    $prestataireUser->notify(new EquipmentRentalReminder4h($rental, 'prestataire'));
                    $prestataireSent++;
                    $this->info('    ✅ Prestataire notifié');
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('Erreur rappel rental prestataire (rental_id=' . $rental->id . '): ' . $e->getMessage());
                    $this->error('    ❌ Erreur prestataire: ' . $e->getMessage());
                }
            }

            // Marquer comme envoyé
            $rental->update([
                'client_reminder_sent_at' => now(),
                'prestataire_reminder_sent_at' => now(),
            ]);
        }

        $this->newLine();
        $this->info("📊 Résultat: {$clientSent} client(s), {$prestataireSent} prestataire(s), {$failed} échec(s)");

        return $failed > 0 ? 1 : 0;
    }
}
