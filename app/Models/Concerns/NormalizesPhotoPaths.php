<?php

namespace App\Models\Concerns;

/**
 * Normalise la lecture de colonnes JSON contenant des chemins de photos.
 * Gère le double-encodage JSON, les backslashes, et normalise les chemins.
 */
trait NormalizesPhotoPaths
{
    public function getPhotosAttribute($value): array
    {
        if (is_null($value) || $value === '' || $value === '[]' || $value === '""') {
            return [];
        }

        $photos = is_array($value) ? $value : $this->decodeJsonPhotos($value);

        return array_values(array_filter(array_map(
            static fn($photo) => is_string($photo) && $photo !== ''
            ? self::normalizePhotoPath($photo)
            : null,
            $photos
        )));
    }

    private function decodeJsonPhotos(string $value): array
    {
        $clean = stripslashes(trim($value, '"'));
        $decoded = json_decode($clean, true);

        // Handle double-encoded JSON
        while (is_string($decoded)) {
            $decoded = json_decode(stripslashes($decoded), true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    private static function normalizePhotoPath(string $path): string
    {
        $clean = str_replace(['\\\\', '\\', '//'], '/', $path);
        $clean = trim($clean, '/');

        return function_exists('normalize_storage_asset_path')
            ? (normalize_storage_asset_path($clean) ?? $clean)
            : $clean;
    }
}
