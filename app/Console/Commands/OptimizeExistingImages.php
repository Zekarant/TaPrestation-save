<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImageOptimizationService;
use Illuminate\Support\Facades\Storage;

class OptimizeExistingImages extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'images:optimize 
                            {--folder= : Dossier spécifique à optimiser (ex: services, equipment_photos)}
                            {--dry-run : Simuler sans modifier les fichiers}
                            {--limit=100 : Nombre max d\'images à traiter}';

    /**
     * The console command description.
     */
    protected $description = 'Optimiser et compresser les images existantes pour économiser de l\'espace';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $folder = $this->option('folder');
        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');

        $this->info('🖼️  Optimisation des images...');
        
        if ($dryRun) {
            $this->warn('Mode simulation activé - aucune modification ne sera effectuée');
        }

        $disk = Storage::disk('public');
        
        // Dossiers à scanner
        $folders = $folder 
            ? [$folder] 
            : ['services', 'equipment_photos', 'profile_photos', 'avatars', 'portfolio', 'reviews'];

        $totalProcessed = 0;
        $totalSaved = 0;
        $errors = [];

        foreach ($folders as $scanFolder) {
            if (!$disk->exists($scanFolder)) {
                $this->line("  ⏭️  Dossier '$scanFolder' non trouvé, ignoré");
                continue;
            }

            $files = $disk->allFiles($scanFolder);
            $images = array_filter($files, function ($file) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            });

            // Exclure les variantes déjà créées
            $images = array_filter($images, function ($file) {
                $filename = pathinfo($file, PATHINFO_FILENAME);
                return !preg_match('/^(thumb|medium|large)_/', $filename);
            });

            $this->info("  📂 Dossier: $scanFolder (" . count($images) . " images)");

            foreach ($images as $imagePath) {
                if ($totalProcessed >= $limit) {
                    $this->warn("  ⚠️  Limite de $limit images atteinte");
                    break 2;
                }

                $originalSize = $disk->size($imagePath);

                if ($dryRun) {
                    $this->line("    [DRY] $imagePath (" . $this->formatBytes($originalSize) . ")");
                    $totalProcessed++;
                    continue;
                }

                $result = ImageOptimizationService::optimize($imagePath);

                if (isset($result['error'])) {
                    $errors[] = "$imagePath: " . $result['error'];
                    $this->error("    ❌ $imagePath: " . $result['error']);
                } else {
                    $newSize = $result['original_size'] ?? $originalSize;
                    $saved = $originalSize - $newSize;
                    $totalSaved += $saved;
                    
                    $variants = count($result['variants'] ?? []);
                    $this->line("    ✅ $imagePath: " . $this->formatBytes($originalSize) . " → " . $this->formatBytes($newSize) . " ($variants variantes)");
                }

                $totalProcessed++;
            }
        }

        $this->newLine();
        $this->info('📊 Résumé:');
        $this->line("   Images traitées: $totalProcessed");
        $this->line("   Espace économisé: " . $this->formatBytes($totalSaved));
        
        if (count($errors) > 0) {
            $this->warn("   Erreurs: " . count($errors));
        }

        return Command::SUCCESS;
    }

    /**
     * Formater les bytes en unité lisible
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}
