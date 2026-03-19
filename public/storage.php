<?php
/**
 * Sert les fichiers depuis storage/app/public
 * Support: GET param ?file= ou REQUEST_URI /storage/...
 */

// Récupérer le chemin depuis GET ou URI
if (isset($_GET['file']) && $_GET['file']) {
    $path = urldecode($_GET['file']);
} else {
    $uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $path = preg_replace('#^/storage/#', '', $uri);
}

// Sécurité
$path = str_replace(['..', '\\'], ['', '/'], $path);
$path = ltrim($path, '/');

if (empty($path)) {
    http_response_code(400);
    exit('No file specified');
}

$file = dirname(__DIR__) . '/storage/app/public/' . $path;

if (!is_file($file)) {
    http_response_code(404);
    exit('Not found: ' . $path);
}

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$types = [
    'jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png',
    'gif'=>'image/gif','webp'=>'image/webp','svg'=>'image/svg+xml',
    'pdf'=>'application/pdf','mp4'=>'video/mp4','webm'=>'video/webm','mov'=>'video/quicktime'
];

header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
header('Content-Length: ' . filesize($file));
header('Cache-Control: public, max-age=31536000');
readfile($file);
