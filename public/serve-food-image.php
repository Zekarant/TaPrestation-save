<?php
/**
 * Sert les images food depuis storage/app/public
 */

require_once dirname(__DIR__) . '/app/Support/DemoMarketplaceImage.php';

use App\Support\DemoMarketplaceImage;

$candidate = $_GET['path'] ?? ($_GET['file'] ?? null);

if (!$candidate) {
    http_response_code(400);
    exit('Missing file or path parameter');
}

$storageRoot = dirname(__DIR__) . '/storage/app/public';
$sameHosts = [$_SERVER['HTTP_HOST'] ?? ''];

$relativePath = DemoMarketplaceImage::normalizePath($candidate, $sameHosts, false);

if (!$relativePath && is_string($candidate)) {
    $file = basename($candidate);
    $legacyPath = 'food-products/' . $file;
    if (is_file($storageRoot . '/' . $legacyPath)) {
        $relativePath = $legacyPath;
    }
}

if (!$relativePath) {
    http_response_code(404);
    exit('Unsupported file path');
}

if (str_starts_with($relativePath, 'demo-marketplace/') && preg_match('/\.svg$/i', $relativePath)) {
    $relativePath = DemoMarketplaceImage::ensureRenderedPng($relativePath, $storageRoot);
}

$fullPath = $storageRoot . '/' . ltrim(str_replace('\\', '/', $relativePath), '/');

if (!is_file($fullPath)) {
    http_response_code(404);
    exit('File not found');
}

header('Content-Type: ' . DemoMarketplaceImage::mimeType($fullPath));
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: public, max-age=31536000, no-transform');

readfile($fullPath);
