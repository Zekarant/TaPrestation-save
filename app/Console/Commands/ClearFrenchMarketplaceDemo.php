<?php

namespace App\Console\Commands;

use App\Services\Demo\FrenchMarketplaceDemoSeeder;
use Illuminate\Console\Command;

class ClearFrenchMarketplaceDemo extends Command
{
    protected $signature = 'demo:clear-marketplace
        {--dry-run : Affiche le volume de donnees demo sans rien supprimer}
        {--force : Supprime les donnees demo sans demander de confirmation}';

    protected $description = 'Supprime les prestataires fictifs marketplace demo et leurs annonces.';

    public function handle(FrenchMarketplaceDemoSeeder $seeder): int
    {
        $summary = $seeder->previewExistingDemoData();

        $this->table(
            ['Element', 'Valeur'],
            [
                ['Utilisateurs demo', $summary['users']],
                ['Prestataires demo', $summary['prestataires']],
                ['Services demo', $summary['services']],
                ['Equipements demo', $summary['equipment']],
                ['Ventes urgentes demo', $summary['urgent_sales']],
                ['Produits food demo', $summary['food_products']],
            ]
        );

        $totalListings = $summary['services']
            + $summary['equipment']
            + $summary['urgent_sales']
            + $summary['food_products'];

        if (($summary['users'] + $totalListings) === 0) {
            $this->info('Aucune donnee demo marketplace a supprimer.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->line('Aucune suppression effectuee (`--dry-run`).');

            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm('Supprimer toutes les donnees demo marketplace listees ci-dessus ?')) {
            $this->warn('Suppression annulee.');

            return self::INVALID;
        }

        $seeder->clearExistingDemoData();

        $this->info('Donnees demo marketplace supprimees.');

        return self::SUCCESS;
    }
}
