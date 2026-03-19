<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizationService
{
    /**
     * Tailles de redimensionnement
     */
    const SIZES = [
        'thumb' => 150,    // Pour les listes/grilles
        'medium' => 600,   // Pour l'affichage normal
        'large' => 1200,   // Pour le zoom (optionnel)
    ];

    /**
     * Qualité de compression JPEG (0-100)
     */
    const QUALITY = 80;

    /**
     * Stocker et optimiser une image uploadée
     * Retourne le chemin de l'image originale
     */
    public static function store(UploadedFile $file, string $folder, bool $generateVariants = true): string
    {
        // Générer un nom unique
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $originalPath = $folder . '/' . $filename;
        
        // Stocker l'original
        $path = $file->storeAs($folder, $filename, 'public');
        
        // Générer les variantes en arrière-plan si demandé
        if ($generateVariants) {
            dispatch(new \App\Jobs\OptimizeImage($path));
        }
        
        return $path;
    }

    /**
     * Optimiser une image existante et créer les variantes
     */
    public static function optimize(string $path): array
    {
        $disk = Storage::disk('public');
        
        if (!$disk->exists($path)) {
            return ['error' => 'File not found'];
        }

        $absolutePath = $disk->path($path);
        $pathInfo = pathinfo($path);
        $folder = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = strtolower($pathInfo['extension']);

        // Vérifier si c'est une image supportée
        $supportedFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $supportedFormats)) {
            return ['error' => 'Unsupported format'];
        }

        $variants = [];

        try {
            // Charger l'image source
            $imageData = file_get_contents($absolutePath);
            $sourceImage = imagecreatefromstring($imageData);
            
            if (!$sourceImage) {
                return ['error' => 'Cannot read image'];
            }

            $originalWidth = imagesx($sourceImage);
            $originalHeight = imagesy($sourceImage);

            // Créer les variantes
            foreach (self::SIZES as $sizeName => $maxDimension) {
                // Ne pas agrandir les petites images
                if ($originalWidth <= $maxDimension && $originalHeight <= $maxDimension) {
                    continue;
                }

                // Calculer les nouvelles dimensions
                $ratio = $originalWidth / $originalHeight;
                if ($originalWidth > $originalHeight) {
                    $newWidth = $maxDimension;
                    $newHeight = (int) ($maxDimension / $ratio);
                } else {
                    $newHeight = $maxDimension;
                    $newWidth = (int) ($maxDimension * $ratio);
                }

                // Créer l'image redimensionnée
                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                
                // Préserver la transparence pour PNG
                if ($extension === 'png') {
                    imagealphablending($resizedImage, false);
                    imagesavealpha($resizedImage, true);
                    $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                    imagefilledrectangle($resizedImage, 0, 0, $newWidth, $newHeight, $transparent);
                }

                // Redimensionner avec antialiasing
                imagecopyresampled(
                    $resizedImage, $sourceImage,
                    0, 0, 0, 0,
                    $newWidth, $newHeight,
                    $originalWidth, $originalHeight
                );

                // Sauvegarder la variante
                $variantFilename = $sizeName . '_' . $filename . '.webp'; // Convertir en WebP pour économiser l'espace
                $variantPath = $folder . '/' . $variantFilename;
                $variantAbsolutePath = $disk->path($variantPath);

                // Sauvegarder en WebP (meilleure compression)
                imagewebp($resizedImage, $variantAbsolutePath, self::QUALITY);
                imagedestroy($resizedImage);

                $variants[$sizeName] = $variantPath;
            }

            // Compresser l'original si c'est un JPEG
            if (in_array($extension, ['jpg', 'jpeg'])) {
                // Réduire la qualité de l'original si le fichier est trop gros
                $originalSize = filesize($absolutePath);
                if ($originalSize > 500000) { // > 500KB
                    imagejpeg($sourceImage, $absolutePath, self::QUALITY);
                }
            }

            imagedestroy($sourceImage);

            return [
                'success' => true,
                'original' => $path,
                'variants' => $variants,
                'original_size' => filesize($absolutePath),
            ];

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Obtenir l'URL d'une variante d'image
     */
    public static function getVariantUrl(string $originalPath, string $size = 'medium'): string
    {
        $pathInfo = pathinfo($originalPath);
        $folder = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        
        // Essayer de trouver la variante WebP
        $variantPath = $folder . '/' . $size . '_' . $filename . '.webp';
        
        if (Storage::disk('public')->exists($variantPath)) {
            return asset('storage/' . $variantPath);
        }
        
        // Fallback sur l'original
        return asset('storage/' . $originalPath);
    }

    /**
     * Obtenir toutes les URLs disponibles pour une image
     */
    public static function getAllUrls(string $originalPath): array
    {
        $pathInfo = pathinfo($originalPath);
        $folder = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $disk = Storage::disk('public');

        $urls = [
            'original' => asset('storage/' . $originalPath),
        ];

        foreach (array_keys(self::SIZES) as $size) {
            $variantPath = $folder . '/' . $size . '_' . $filename . '.webp';
            if ($disk->exists($variantPath)) {
                $urls[$size] = asset('storage/' . $variantPath);
            }
        }

        return $urls;
    }

    /**
     * Supprimer une image et toutes ses variantes
     */
    public static function delete(string $originalPath): void
    {
        $pathInfo = pathinfo($originalPath);
        $folder = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $disk = Storage::disk('public');

        // Supprimer l'original
        $disk->delete($originalPath);

        // Supprimer les variantes
        foreach (array_keys(self::SIZES) as $size) {
            $variantPath = $folder . '/' . $size . '_' . $filename . '.webp';
            $disk->delete($variantPath);
        }
    }
}
