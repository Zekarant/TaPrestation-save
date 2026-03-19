<?php

namespace App\Console\Commands;

use App\Models\FoodOrder;
use App\Notifications\FoodOrderRefunded;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Commande pour rembourser/annuler automatiquement les commandes
 * dont le code n'a pas été saisi dans les 24h après "prête"
 * 
 * GÈRE 2 CAS:
 * 1. Paiement capturé (escrow_status = 'held') → Remboursement Stripe
 * 2. Autorisation en attente (payment_status = 'pending_capture') → Annulation autorisation
 * 
 * À exécuter via cron: * * * * * cd /path && php artisan food:refund-expired >> /dev/null 2>&1
 * Ou ajouter dans app/Console/Kernel.php: $schedule->command('food:refund-expired')->everyFiveMinutes();
 */
class FoodRefundExpiredOrders extends Command
{
    protected $signature = 'food:refund-expired {--dry-run : Afficher sans exécuter}';
    protected $description = 'Rembourser/annuler les commandes food dont le code a expiré (24h sans validation)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('🔍 Recherche des commandes expirées...');

        // CAS 1: Commandes avec paiement capturé mais code non vérifié
        $capturedExpired = FoodOrder::query()
            ->whereIn('status', [FoodOrder::STATUS_READY, FoodOrder::STATUS_DELIVERED])
            ->where('escrow_status', FoodOrder::ESCROW_HELD)
            ->whereNull('code_verified_at')
            ->whereNotNull('code_expires_at')
            ->where('code_expires_at', '<', now())
            ->get();

        // CAS 2: Commandes avec autorisation non capturée expirée
        // (livraison externe où vendeur/livreur n'ont pas accepté à temps)
        $pendingCaptureExpired = FoodOrder::query()
            ->where('payment_status', 'pending_capture')
            ->where('escrow_status', FoodOrder::ESCROW_PENDING)
            ->where(function($q) {
                // Plus de 24h depuis la création OU code expiré
                $q->where('created_at', '<', now()->subHours(24))
                  ->orWhere(function($q2) {
                      $q2->whereNotNull('code_expires_at')
                         ->where('code_expires_at', '<', now());
                  });
            })
            ->get();

        $totalExpired = $capturedExpired->count() + $pendingCaptureExpired->count();

        if ($totalExpired === 0) {
            $this->info('✅ Aucune commande expirée à traiter.');
            return 0;
        }

        $this->info("📋 {$totalExpired} commande(s) expirée(s) trouvée(s)");
        $this->info("   - {$capturedExpired->count()} avec paiement capturé");
        $this->info("   - {$pendingCaptureExpired->count()} avec autorisation en attente");

        $processed = 0;
        $failed = 0;

        // Traiter les paiements capturés (remboursement)
        foreach ($capturedExpired as $order) {
            $clientName = $order->client?->name ?: 'N/A';
            $this->line("  - [REFUND] Commande #{$order->order_number} (Client: {$clientName}, Montant: {$order->amount_held}€)");

            if ($dryRun) {
                $this->comment("    [DRY-RUN] Serait remboursée");
                continue;
            }

            try {
                $reason = "Code non saisi dans les 24h (expiration automatique)";
                $success = $order->refundPayment($reason);
                
                if ($success) {
                    $order->update([
                        'status' => FoodOrder::STATUS_CANCELLED,
                        'cancelled_at' => now(),
                        'cancellation_reason' => $reason,
                    ]);

                    try {
                        if ($order->client) {
                            $order->client->notify(new FoodOrderRefunded($order, $reason));
                        }
                    } catch (\Exception $e) {
                        Log::warning("Notification refund échouée pour commande #{$order->id}: " . $e->getMessage());
                    }

                    $this->info("    ✅ Remboursée avec succès");
                    $processed++;
                } else {
                    $this->error("    ❌ Échec du remboursement");
                    $failed++;
                }
            } catch (\Exception $e) {
                Log::error("Erreur refund commande #{$order->id}: " . $e->getMessage());
                $this->error("    ❌ Erreur: " . $e->getMessage());
                $failed++;
            }
        }

        // Traiter les autorisations en attente (annulation)
        foreach ($pendingCaptureExpired as $order) {
            $clientName = $order->client?->name ?: 'N/A';
            $this->line("  - [CANCEL] Commande #{$order->order_number} (Client: {$clientName}, Montant autorisé: {$order->amount_held}€)");

            if ($dryRun) {
                $this->comment("    [DRY-RUN] Autorisation serait annulée");
                continue;
            }

            try {
                $reason = "Autorisation expirée (commande non traitée dans les 24h)";
                $success = $order->cancelAuthorization($reason);
                
                if ($success) {
                    $order->update([
                        'status' => FoodOrder::STATUS_CANCELLED,
                        'cancelled_at' => now(),
                        'cancellation_reason' => $reason,
                    ]);

                    try {
                        if ($order->client) {
                            $order->client->notify(new FoodOrderRefunded($order, $reason));
                        }
                    } catch (\Exception $e) {
                        Log::warning("Notification cancel échouée pour commande #{$order->id}: " . $e->getMessage());
                    }

                    $this->info("    ✅ Autorisation annulée");
                    $processed++;
                } else {
                    $this->error("    ❌ Échec de l'annulation");
                    $failed++;
                }
            } catch (\Exception $e) {
                Log::error("Erreur cancel autorisation commande #{$order->id}: " . $e->getMessage());
                $this->error("    ❌ Erreur: " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("📊 Résultat: {$processed} traitée(s), {$failed} échouée(s)");

        return $failed > 0 ? 1 : 0;
    }
}
