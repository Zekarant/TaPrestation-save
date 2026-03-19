<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncPrestataireFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestataires:sync-legacy-fields {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les champs legacy des prestataires (photo -> profile_image, rating -> rating_average, reviews_count -> total_reviews)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Début de la synchronisation des champs legacy pour `prestataires`.');

        // profile_image <- photo
        if (Schema::hasColumn('prestataires', 'photo') && Schema::hasColumn('prestataires', 'profile_image')) {
            $count = DB::table('prestataires')
                ->whereNull('profile_image')
                ->whereNotNull('photo')
                ->count();

            $this->info("Trouvé $count lignes à copier photo -> profile_image.");

            if (!$this->option('dry-run') && $count > 0) {
                DB::table('prestataires')
                    ->whereNull('profile_image')
                    ->whereNotNull('photo')
                    ->update(['profile_image' => DB::raw('photo')]);

                $this->info('Copie photo -> profile_image effectuée.');
            }
        } else {
            $this->warn('Colonnes `photo` ou `profile_image` manquantes; saut de cette étape.');
        }

        // rating_average <- rating
        if (Schema::hasColumn('prestataires', 'rating') && Schema::hasColumn('prestataires', 'rating_average')) {
            $count = DB::table('prestataires')
                ->whereNull('rating_average')
                ->whereNotNull('rating')
                ->count();

            $this->info("Trouvé $count lignes à copier rating -> rating_average.");

            if (!$this->option('dry-run') && $count > 0) {
                DB::table('prestataires')
                    ->whereNull('rating_average')
                    ->whereNotNull('rating')
                    ->update(['rating_average' => DB::raw('rating')]);

                $this->info('Copie rating -> rating_average effectuée.');
            }
        } else {
            $this->warn('Colonnes `rating` ou `rating_average` manquantes; saut de cette étape.');
        }

        // total_reviews <- reviews_count
        if (Schema::hasColumn('prestataires', 'reviews_count') && Schema::hasColumn('prestataires', 'total_reviews')) {
            $count = DB::table('prestataires')
                ->whereNull('total_reviews')
                ->whereNotNull('reviews_count')
                ->count();

            $this->info("Trouvé $count lignes à copier reviews_count -> total_reviews.");

            if (!$this->option('dry-run') && $count > 0) {
                DB::table('prestataires')
                    ->whereNull('total_reviews')
                    ->whereNotNull('reviews_count')
                    ->update(['total_reviews' => DB::raw('reviews_count')]);

                $this->info('Copie reviews_count -> total_reviews effectuée.');
            }
        } else {
            $this->warn('Colonnes `reviews_count` ou `total_reviews` manquantes; saut de cette étape.');
        }

        $this->info('Synchronisation terminée.');

        return 0;
    }
}
