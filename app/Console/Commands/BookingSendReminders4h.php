<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\BookingReminder4h;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Envoie un rappel 4h avant les réservations de service.
 * Notifie le client ET le prestataire.
 */
class BookingSendReminders4h extends Command
{
    protected $signature = 'bookings:send-reminders-4h {--dry-run : Afficher sans exécuter}';
    protected $description = 'Envoyer un rappel au client et prestataire 4h avant la réservation (start_datetime)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // Fenêtre de 4h : réservations prévues entre maintenant+3h30 et maintenant+4h30
        $windowStart = now()->addHours(3)->addMinutes(30);
        $windowEnd = now()->addHours(4)->addMinutes(30);

        $this->info('🔔 Recherche des réservations prévues dans ~4h...');
        $this->info('   Fenêtre: ' . $windowStart->format('H:i') . ' - ' . $windowEnd->format('H:i'));

        $bookings = Booking::query()
            ->with(['prestataire.user', 'client', 'service'])
            ->whereNotNull('start_datetime')
            ->whereBetween('start_datetime', [$windowStart, $windowEnd])
            ->whereNull('client_reminder_4h_sent_at')
            ->where('status', 'confirmed')
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('✅ Aucun rappel 4h à envoyer.');
            return 0;
        }

        $this->info('📋 ' . $bookings->count() . ' réservation(s) à traiter');

        $clientSent = 0;
        $prestataireSent = 0;
        $failed = 0;

        foreach ($bookings as $booking) {
            $this->line('  - Réservation #' . $booking->booking_number . ' (start_datetime=' . $booking->start_datetime?->format('Y-m-d H:i') . ')');

            if ($dryRun) {
                $this->comment('    [DRY-RUN] Serait notifiée');
                continue;
            }

            // === Notification CLIENT ===
            if ($booking->client) {
                try {
                    $booking->client->notify(new BookingReminder4h($booking, 'client'));
                    $clientSent++;
                    $this->info('    ✅ Client notifié');
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('Erreur rappel 4h booking client (booking_id=' . $booking->id . '): ' . $e->getMessage());
                    $this->error('    ❌ Erreur client: ' . $e->getMessage());
                }
            }

            // === Notification PRESTATAIRE ===
            $prestataireUser = $booking->prestataire?->user;
            if ($prestataireUser) {
                try {
                    $prestataireUser->notify(new BookingReminder4h($booking, 'prestataire'));
                    $prestataireSent++;
                    $this->info('    ✅ Prestataire notifié');
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('Erreur rappel 4h booking prestataire (booking_id=' . $booking->id . '): ' . $e->getMessage());
                    $this->error('    ❌ Erreur prestataire: ' . $e->getMessage());
                }
            }

            // Marquer comme envoyé
            $booking->update([
                'client_reminder_4h_sent_at' => now(),
                'prestataire_reminder_4h_sent_at' => now(),
            ]);
        }

        $this->newLine();
        $this->info("📊 Résultat: {$clientSent} client(s), {$prestataireSent} prestataire(s), {$failed} échec(s)");

        return $failed > 0 ? 1 : 0;
    }
}
