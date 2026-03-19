<?php
/**
 * Sert une image depuis storage/app/public
 */

require_once dirname(__DIR__) . '/app/Support/DemoMarketplaceImage.php';

use App\Support\DemoMarketplaceImage;

$requestedPath = $_GET['path'] ?? '';
$storageRoot = dirname(__DIR__) . '/storage/app/public';
$sameHosts = [$_SERVER['HTTP_HOST'] ?? ''];

$relativePath = DemoMarketplaceImage::normalizePath($requestedPath, $sameHosts, false);

if (!$relativePath) {
    http_response_code(400);
    exit('Missing path parameter');
}

if (str_starts_with($relativePath, 'demo-marketplace/') && preg_match('/\.svg$/i', $relativePath)) {
    $relativePath = DemoMarketplaceImage::ensureRenderedPng($relativePath, $storageRoot);
}

$fullPath = $storageRoot . '/' . ltrim(str_replace('\\', '/', $relativePath), '/');

if (!is_file($fullPath)) {
    http_response_code(404);
    exit('Image not found');
}

header('Content-Type: ' . DemoMarketplaceImage::mimeType($fullPath));
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: public, max-age=31536000, no-transform');

readfile($fullPath);
