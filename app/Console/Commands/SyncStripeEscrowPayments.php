<?php

namespace App\Console\Commands;

use App\Models\PaymentTransaction;
use App\Models\UrgentSalePurchase;
use App\Services\EscrowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncStripeEscrowPayments extends Command
{
    protected $signature = 'escrow:sync-payments 
                            {--days=30 : Nombre de jours à remonter}
                            {--dry-run : Afficher sans créer}
                            {--force : Forcer la resynchronisation même si escrow existe}';

    protected $description = 'Synchronise les paiements Stripe existants avec le système escrow (ventes urgentes)';

    public function handle(EscrowService $escrowService): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info("🔄 Synchronisation des paiements escrow (derniers {$days} jours)...");
        
        if ($dryRun) {
            $this->warn('Mode DRY-RUN : aucune modification ne sera effectuée.');
        }

        // 1. Trouver les achats de ventes urgentes sans escrow
        $purchases = UrgentSalePurchase::with(['urgentSale.prestataire', 'buyer.client', 'paymentTransaction'])
            ->where('created_at', '>=', now()->subDays($days))
            ->where('status', 'paid')
            ->when(!$force, function ($q) {
                $q->whereNull('escrow_id');
            })
            ->get();

        $this->info("📦 {$purchases->count()} achat(s) trouvé(s) à synchroniser.");

        $created = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($purchases as $purchase) {
            $this->line("---");
            $this->info("🛒 Achat #{$purchase->id} - Vente #{$purchase->urgent_sale_id}");
            
            $urgentSale = $purchase->urgentSale;
            $buyer = $purchase->buyer;
            $transaction = $purchase->paymentTransaction;

            if (!$urgentSale) {
                $this->error("  ❌ Vente urgente introuvable");
                $errors++;
                continue;
            }

            if (!$buyer || !$buyer->client) {
                $this->error("  ❌ Client/acheteur introuvable");
                $errors++;
                continue;
            }

            $clientId = $buyer->client->id;
            $prestataireId = $urgentSale->prestataire_id;

            if (!$prestataireId) {
                $this->error("  ❌ Prestataire introuvable");
                $errors++;
                continue;
            }

            // Vérifier si un escrow existe déjà pour cet achat
            $existingEscrow = DB::table('escrow_transactions')
                ->where('escrowable_type', 'like', '%UrgentSalePurchase%')
                ->where('escrowable_id', $purchase->id)
                ->first();

            if ($existingEscrow && !$force) {
                $this->warn("  ⏭️ Escrow #{$existingEscrow->id} existe déjà - ignoré");
                $skipped++;
                continue;
            }

            // Calculer les montants
            $amount = (float) $purchase->total_amount;
            $stripePaymentIntentId = $transaction?->stripe_payment_intent_id;

            // Récupérer les métadonnées du PaymentIntent si disponible
            $platformFee = null;
            $metadata = [];
            
            if ($stripePaymentIntentId && class_exists('\Stripe\Stripe')) {
                try {
                    \Stripe\Stripe::setApiKey(config('stripe.secret'));
                    $pi = \Stripe\PaymentIntent::retrieve($stripePaymentIntentId);
                    $metadata = $pi->metadata?->toArray() ?? [];
                    
                    $clientFee = (float) ($metadata['client_fee_total'] ?? 0);
                    $prestaFee = (float) ($metadata['prestataire_fee_total'] ?? 0);
                    $stripeFee = (float) ($metadata['stripe_fee_total'] ?? 0);
                    $platformFee = round($clientFee + $prestaFee + $stripeFee, 2);
                    
                    $this->info("  💳 PaymentIntent: {$stripePaymentIntentId}");
                    $this->info("  📊 Montant: {$amount}€, Commission plateforme: {$platformFee}€");
                } catch (\Exception $e) {
                    $this->warn("  ⚠️ Impossible de récupérer le PaymentIntent: " . $e->getMessage());
                }
            }

            if ($dryRun) {
                $this->info("  🔍 [DRY-RUN] Escrow serait créé: client_id={$clientId}, prestataire_id={$prestataireId}, amount={$amount}");
                $created++;
                continue;
            }

            // Créer l'escrow
            try {
                // Supprimer l'ancien escrow si force
                if ($existingEscrow && $force) {
                    DB::table('escrow_transactions')->where('id', $existingEscrow->id)->delete();
                    $this->warn("  🗑️ Ancien escrow #{$existingEscrow->id} supprimé");
                }

                $escrow = $escrowService->createEscrow(
                    escrowable: $purchase,
                    clientId: $clientId,
                    prestataireId: $prestataireId,
                    amount: $amount,
                    depositAmount: 0,
                    stripePaymentIntentId: $stripePaymentIntentId,
                    platformFeeOverride: $platformFee,
                    metadata: array_merge($metadata, [
                        'synced_at' => now()->toISOString(),
                        'purchase_id' => (string) $purchase->id,
                        'urgent_sale_id' => (string) $urgentSale->id,
                    ])
                );

                if ($escrow) {
                    // Mettre à jour le purchase avec l'escrow_id
                    DB::table('urgent_sale_purchases')
                        ->where('id', $purchase->id)
                        ->update(['escrow_id' => $escrow->id, 'updated_at' => now()]);

                    $this->info("  ✅ Escrow #{$escrow->id} créé avec succès");
                    $created++;
                } else {
                    $this->error("  ❌ Échec de création de l'escrow");
                    $errors++;
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Erreur: " . $e->getMessage());
                Log::error("Escrow sync error for purchase #{$purchase->id}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->line("===");
        $this->info("📊 Résumé:");
        $this->info("  ✅ Créés: {$created}");
        $this->info("  ⏭️ Ignorés: {$skipped}");
        $this->info("  ❌ Erreurs: {$errors}");

        if ($dryRun) {
            $this->warn("\n💡 Relancez sans --dry-run pour appliquer les changements.");
        }

        return Command::SUCCESS;
    }
}
