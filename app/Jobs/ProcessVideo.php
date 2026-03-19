<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use getID3;

class ProcessVideo implements ShouldQueue
{
    use Queueable;

    protected $video;

    /**
     * Nombre de tentatives max
     */
    public int $tries = 3;

    /**
     * Timeout en secondes (5 minutes pour la compression)
     */
    public int $timeout = 300;

    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $videoPath = $this->video->video_path;
        $fullPath = Storage::disk('public')->path($videoPath);

        // Check file size (max 100MB)
        $fileSizeInMb = filesize($fullPath) / (1024 * 1024);
        if ($fileSizeInMb > 100) {
            $this->video->status = 'failed';
            $this->video->save();
            Log::warning('Video too large', ['id' => $this->video->id, 'size' => $fileSizeInMb]);
            return;
        }

        // Obtenir la durée avec getID3
        $getID3 = new getID3();
        $getID3->option_md5_data = true;
        $getID3->encoding = 'UTF-8';
        $fileInfo = $getID3->analyze($fullPath);

        $duration = $this->extractDuration($fileInfo);

        // Limite de durée (60 secondes)
        if ($duration > 60) {
            $this->video->status = 'failed';
            $this->video->save();
            Log::warning('Video too long', ['id' => $this->video->id, 'duration' => $duration]);
            return;
        }

        // Nouveau nom et chemin
        $newFilename = pathinfo($videoPath, PATHINFO_FILENAME) . '_compressed.mp4';
        $newPath = 'videos/' . $newFilename;
        $outputPath = Storage::disk('public')->path($newPath);

        // Essayer de compresser avec FFmpeg
        $compressed = $this->compressVideo($fullPath, $outputPath);

        if ($compressed) {
            // Supprimer l'original et utiliser la version compressée
            Storage::disk('public')->delete($videoPath);
            $this->video->video_path = $newPath;

            // Générer la thumbnail
            $thumbnailPath = $this->generateThumbnail($outputPath);
            if ($thumbnailPath) {
                $this->video->thumbnail_path = $thumbnailPath;
            }

            Log::info('Video compressed successfully', [
                'id' => $this->video->id,
                'original_size' => $fileSizeInMb . 'MB',
                'new_size' => round(filesize($outputPath) / (1024 * 1024), 2) . 'MB'
            ]);
        } else {
            // Si FFmpeg échoue, juste déplacer le fichier
            $simplePath = 'videos/' . basename($videoPath);
            Storage::disk('public')->move($videoPath, $simplePath);
            $this->video->video_path = $simplePath;

            Log::info('Video moved without compression (FFmpeg not available)', [
                'id' => $this->video->id
            ]);
        }

        $this->video->duration = $duration;
        $this->video->status = 'processed';
        $this->video->save();
    }

    /**
     * Extraire la durée depuis les métadonnées
     */
    private function extractDuration(array $fileInfo): float
    {
        if (isset($fileInfo['playtime_seconds']) && is_numeric($fileInfo['playtime_seconds'])) {
            return $fileInfo['playtime_seconds'];
        }
        if (isset($fileInfo['video']['playtime_seconds']) && is_numeric($fileInfo['video']['playtime_seconds'])) {
            return $fileInfo['video']['playtime_seconds'];
        }
        if (isset($fileInfo['audio']['playtime_seconds']) && is_numeric($fileInfo['audio']['playtime_seconds'])) {
            return $fileInfo['audio']['playtime_seconds'];
        }
        if (isset($fileInfo['bitrate']) && isset($fileInfo['filesize']) && $fileInfo['bitrate'] > 0) {
            return $fileInfo['filesize'] / ($fileInfo['bitrate'] / 8);
        }
        return 0;
    }

    /**
     * Compresser la vidéo avec FFmpeg
     * Cible: 720p, bitrate réduit, format optimisé pour le web
     */
    private function compressVideo(string $inputPath, string $outputPath): bool
    {
        // Vérifier si FFmpeg est disponible
        $ffmpegPath = $this->findFFmpeg();
        if (!$ffmpegPath) {
            return false;
        }

        try {
            // Paramètres optimisés pour le web:
            // - Résolution max 720p (-vf scale)
            // - Codec H.264 avec profil baseline (meilleure compatibilité)
            // - CRF 28 (qualité correcte, taille réduite)
            // - Audio AAC 128k
            // - Faststart pour streaming rapide
            $command = sprintf(
                '%s -i %s -vf "scale=\'min(1280,iw)\':\'min(720,ih)\':force_original_aspect_ratio=decrease" ' .
                '-c:v libx264 -profile:v baseline -level 3.0 -crf 28 -preset fast ' .
                '-c:a aac -b:a 128k -movflags +faststart -y %s 2>&1',
                escapeshellarg($ffmpegPath),
                escapeshellarg($inputPath),
                escapeshellarg($outputPath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($outputPath) && filesize($outputPath) > 0) {
                return true;
            }

            Log::warning('FFmpeg compression failed', [
                'return_code' => $returnCode,
                'output' => implode("\n", array_slice($output, -10))
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('FFmpeg error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Générer une thumbnail à partir de la vidéo
     */
    private function generateThumbnail(string $videoPath): ?string
    {
        $ffmpegPath = $this->findFFmpeg();
        if (!$ffmpegPath) {
            return null;
        }

        try {
            $thumbnailFilename = pathinfo($videoPath, PATHINFO_FILENAME) . '_thumb.jpg';
            $thumbnailPath = 'videos/thumbnails/' . $thumbnailFilename;
            $thumbnailAbsPath = Storage::disk('public')->path($thumbnailPath);

            // Créer le dossier si nécessaire
            $thumbnailDir = dirname($thumbnailAbsPath);
            if (!is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            // Extraire une frame à 1 seconde
            $command = sprintf(
                '%s -i %s -ss 00:00:01 -vframes 1 -vf "scale=480:-1" -q:v 2 -y %s 2>&1',
                escapeshellarg($ffmpegPath),
                escapeshellarg($videoPath),
                escapeshellarg($thumbnailAbsPath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($thumbnailAbsPath)) {
                return $thumbnailPath;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Thumbnail generation error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Trouver le chemin de FFmpeg
     */
    private function findFFmpeg(): ?string
    {
        // Chemins possibles sur différents systèmes
        $possiblePaths = [
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
            'C:\\ffmpeg\\bin\\ffmpeg.exe',
            'ffmpeg', // Si dans le PATH
        ];

        foreach ($possiblePaths as $path) {
            $testCommand = escapeshellarg($path) . ' -version';

            exec($testCommand . ' 2>&1', $output, $returnCode);

            if ($returnCode === 0) {
                return $path;
            }
        }

        return null;
    }
}