<?php

namespace App\Console\Commands;

use App\Models\Prestataire;
use Illuminate\Console\Command;

class UpdateStripeCapabilities extends Command
{
    protected $signature = 'stripe:update-capabilities {--dry-run : Afficher sans modifier}';
    protected $description = 'Met à jour les capabilities Stripe Connect pour tous les prestataires (link_payments pour Apple Pay, Google Pay, etc.)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        \Stripe\Stripe::setApiKey(config('stripe.secret') ?: config('services.stripe.secret'));
        
        $prestataires = Prestataire::whereNotNull('stripe_account_id')->get();
        
        $this->info("Trouvé {$prestataires->count()} prestataires avec compte Stripe");
        
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        
        $bar = $this->output->createProgressBar($prestataires->count());
        $bar->start();
        
        foreach ($prestataires as $prestataire) {
            try {
                $account = \Stripe\Account::retrieve($prestataire->stripe_account_id);
                
                // Vérifier si le compte est actif
                if (!empty($account->deleted)) {
                    $this->newLine();
                    $this->warn("  Prestataire #{$prestataire->id}: Compte supprimé, ignoré");
                    $skipped++;
                    $bar->advance();
                    continue;
                }
                
                $capabilities = $account->capabilities ?? [];
                $needsUpdate = false;
                $capsToRequest = [];
                
                // Vérifier link_payments
                if (($capabilities['link_payments'] ?? null) !== 'active' && 
                    ($capabilities['link_payments'] ?? null) !== 'pending') {
                    $capsToRequest['link_payments'] = ['requested' => true];
                    $needsUpdate = true;
                }
                
                if ($needsUpdate) {
                    if ($dryRun) {
                        $this->newLine();
                        $this->info("  [DRY-RUN] Prestataire #{$prestataire->id}: Demanderait link_payments");
                    } else {
                        \Stripe\Account::update($prestataire->stripe_account_id, [
                            'capabilities' => $capsToRequest,
                        ]);
                    }
                    $updated++;
                } else {
                    $skipped++;
                }
                
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("  Prestataire #{$prestataire->id}: " . $e->getMessage());
                $errors++;
            }
            
            $bar->advance();
            usleep(100000); // 100ms entre chaque appel pour éviter rate limit
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("Résumé:");
        $this->line("  - Mis à jour: {$updated}");
        $this->line("  - Déjà OK: {$skipped}");
        $this->line("  - Erreurs: {$errors}");
        
        if ($dryRun) {
            $this->warn("Mode dry-run: aucune modification effectuée. Relancez sans --dry-run pour appliquer.");
        }
        
        return Command::SUCCESS;
    }
}
