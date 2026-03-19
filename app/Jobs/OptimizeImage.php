<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ImageOptimizationService;
use Illuminate\Support\Facades\Log;

class OptimizeImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Le chemin de l'image à optimiser
     */
    protected string $imagePath;

    /**
     * Nombre de tentatives max
     */
    public int $tries = 3;

    /**
     * Timeout en secondes
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(string $imagePath)
    {
        $this->imagePath = $imagePath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Optimizing image: ' . $this->imagePath);
            
            $result = ImageOptimizationService::optimize($this->imagePath);
            
            if (isset($result['error'])) {
                Log::warning('Image optimization failed: ' . $result['error'], [
                    'path' => $this->imagePath
                ]);
                return;
            }
            
            Log::info('Image optimized successfully', [
                'path' => $this->imagePath,
                'variants' => $result['variants'] ?? [],
                'original_size' => $result['original_size'] ?? 0,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Image optimization error: ' . $e->getMessage(), [
                'path' => $this->imagePath,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
