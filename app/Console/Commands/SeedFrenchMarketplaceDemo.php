<?php

namespace App\Console\Commands;

use App\Services\Demo\FrenchMarketplaceDemoSeeder;
use Database\Seeders\NewCategoriesSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SeedFrenchMarketplaceDemo extends Command
{
    protected $signature = 'demo:seed-marketplace
        {--count=240 : Nombre de profils prestataires a generer}
        {--refresh : Supprime les anciennes donnees demo avant regeneration}
        {--without-remote-images : Utilise uniquement des placeholders locaux pour les annonces}';

    protected $description = 'Genere un jeu de donnees marketplace francais fictif pour la demo.';

    public function handle(FrenchMarketplaceDemoSeeder $seeder): int
    {
        $count = (int) $this->option('count');

        if ($count < 1 || $count > 500) {
            $this->error('`--count` doit etre compris entre 1 et 500.');

            return self::INVALID;
        }

        if (!Schema::hasTable('categories')) {
            $this->error("La table `categories` n'existe pas. Lancez d'abord les migrations.");

            return self::FAILURE;
        }

        if (Schema::hasTable('categories') && \App\Models\Category::count() === 0) {
            $this->line('Seed des categories...');
            $this->call('db:seed', ['--class' => NewCategoriesSeeder::class, '--force' => true]);
        }

        if ($this->option('refresh')) {
            $this->line('Suppression des anciennes donnees demo...');
            $seeder->clearExistingDemoData();
        }

        $summary = $seeder->seed($count, !$this->option('without-remote-images'));

        $this->table(
            ['Element', 'Valeur'],
            [
                ['Profils crees', $summary['profiles_created']],
                ['Profils mis a jour', $summary['profiles_updated']],
                ['Services crees', $summary['services_created']],
                ['Equipements crees', $summary['equipment_created']],
                ['Ventes urgentes creees', $summary['urgent_sales_created']],
                ['Produits food crees', $summary['food_products_created']],
            ]
        );

        $this->line('');
        $this->line('Identifiants demo: mot de passe unique `Password@123`.');
        $this->line('Emails demo: `*@demo-fr.example`.');
        $this->line("Telephones et adresses: format francais fictif, non destines a un usage reel.");

        return self::SUCCESS;
    }
}
