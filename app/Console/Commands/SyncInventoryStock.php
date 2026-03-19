<?php

namespace App\Console\Commands;

use App\Services\InventorySyncService;
use Illuminate\Console\Command;

class SyncInventoryStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:sync 
                            {--user= : Synchroniser uniquement pour un user_id spécifique}
                            {--expire-reservations : Expirer les réservations en attente}
                            {--cancel-unpaid : Annuler les réservations confirmées non payées}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise le stock inventaire avec les ventes et réservations';

    /**
     * Execute the console command.
     */
    public function handle(InventorySyncService $syncService)
    {
        $this->info('🔄 Synchronisation du stock en cours...');
        
        // Expirer les vieilles réservations en attente
        if ($this->option('expire-reservations')) {
            $expired = $syncService->expireOldReservations(24);
            $this->info("⏰ {$expired} réservation(s) expirée(s)");
        }
        
        // Annuler les réservations confirmées non payées
        if ($this->option('cancel-unpaid')) {
            $cancelled = $syncService->cancelUnpaidReservations(48);
            $this->info("❌ {$cancelled} réservation(s) non payée(s) annulée(s)");
        }
        
        // Synchroniser l'inventaire
        if ($userId = $this->option('user')) {
            $result = $syncService->syncUserInventory((int) $userId);
            $this->info("✅ {$result['synced_items']} article(s) synchronisé(s) pour l'utilisateur #{$userId}");
        } else {
            // Synchroniser tous les utilisateurs
            $users = \App\Models\InventoryItem::distinct()->pluck('user_id');
            $totalSynced = 0;
            
            foreach ($users as $userId) {
                $result = $syncService->syncUserInventory($userId);
                $totalSynced += $result['synced_items'];
            }
            
            $this->info("✅ {$totalSynced} article(s) synchronisé(s) au total");
        }
        
        $this->info('🎉 Synchronisation terminée !');
        
        return Command::SUCCESS;
    }
}
