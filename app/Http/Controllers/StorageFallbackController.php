<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class StorageFallbackController extends Controller
{
    public function serve(string $path)
    {
        $relativePath = ltrim(str_replace('\\', '/', $path), '/');
        if (
            $relativePath === '' ||
            str_contains($relativePath, "\0") ||
            str_contains($relativePath, '../') ||
            str_starts_with($relativePath, '..')
        ) {
            abort(404);
        }

        $publicStorageRoot = realpath(storage_path('app/public'));
        if ($publicStorageRoot === false) {
            abort(500);
        }

        $absolutePath = realpath($publicStorageRoot . DIRECTORY_SEPARATOR . $relativePath);
        $rootPrefix = rtrim($publicStorageRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (
            $absolutePath === false ||
            !str_starts_with($absolutePath, $rootPrefix) ||
            !is_file($absolutePath)
        ) {
            abort(404);
        }

        $mimeType = File::mimeType($absolutePath) ?: 'application/octet-stream';

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
