<?php

namespace App\Console\Commands;

use App\Services\EscrowService;
use Illuminate\Console\Command;

class ProcessExpiredEscrows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'escrow:process-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Libère automatiquement les escrows expirés et traite les litiges auto (split J+7)';

    /**
     * Execute the console command.
     */
    public function handle(EscrowService $escrowService): int
    {
        $this->info('Traitement des escrows expirés...');
        
        $count = $escrowService->processExpiredEscrows();

        $this->info('Traitement des litiges expirés (auto-split)...');
        $disputesCount = $escrowService->processExpiredDisputesAutoSplit();
        
        if ($count > 0) {
            $this->info("✅ {$count} escrow(s) libéré(s) automatiquement.");
        } else {
            $this->info('Aucun escrow à libérer.');
        }

        if ($disputesCount > 0) {
            $this->info("✅ {$disputesCount} litige(s) traité(s) automatiquement.");
        } else {
            $this->info('Aucun litige à traiter.');
        }

        return Command::SUCCESS;
    }
}
