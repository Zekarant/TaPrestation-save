<?php
/**
 * Serveur de fichiers média ultra-simple pour iOS Safari
 * Version minimale et robuste - pas de fonctions avancées
 */

// Désactiver toute sortie avant les headers
error_reporting(0);
ini_set('display_errors', 0);
ini_set('zlib.output_compression', 'Off');

// Nettoyer tous les buffers
while (ob_get_level()) {
    ob_end_clean();
}

// Récupérer le fichier demandé
$file = isset($_GET['f']) ? $_GET['f'] : '';
if (empty($file)) {
    http_response_code(400);
    exit;
}

// Sécurité basique
$file = str_replace(['../', '..\\', '..'], '', $file);
$file = ltrim($file, '/\\');

// Chemin du fichier
$filePath = __DIR__ . '/../storage/app/public/' . $file;

// Vérifier existence
if (!file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    exit;
}

// Obtenir infos fichier
$fileSize = filesize($filePath);
if ($fileSize === false || $fileSize === 0) {
    http_response_code(404);
    exit;
}

// Type MIME simple
$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$mimeTypes = [
    'mp4' => 'video/mp4',
    'mov' => 'video/mp4',
    'webm' => 'video/webm',
    'm4v' => 'video/mp4',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
];
$mime = isset($mimeTypes[$ext]) ? $mimeTypes[$ext] : 'application/octet-stream';

// Headers de base
header('Content-Type: ' . $mime);
header('Accept-Ranges: bytes');
header('Cache-Control: public, max-age=86400');
header('Access-Control-Allow-Origin: *');

// OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// HEAD
if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
    header('Content-Length: ' . $fileSize);
    exit;
}

// Désactiver timeout
set_time_limit(0);

// Range Request
$start = 0;
$end = $fileSize - 1;
$length = $fileSize;

if (isset($_SERVER['HTTP_RANGE'])) {
    $range = $_SERVER['HTTP_RANGE'];
    
    if (preg_match('/bytes=(\d*)-(\d*)/', $range, $m)) {
        $rangeStart = $m[1];
        $rangeEnd = $m[2];
        
        if ($rangeStart === '' && $rangeEnd !== '') {
            $start = $fileSize - intval($rangeEnd);
            $end = $fileSize - 1;
        } elseif ($rangeStart !== '' && $rangeEnd === '') {
            $start = intval($rangeStart);
            $end = $fileSize - 1;
        } else {
            $start = intval($rangeStart);
            $end = intval($rangeEnd);
        }
        
        if ($start < 0) $start = 0;
        if ($end >= $fileSize) $end = $fileSize - 1;
        if ($start > $end) {
            http_response_code(416);
            header("Content-Range: bytes */$fileSize");
            exit;
        }
        
        $length = $end - $start + 1;
        
        http_response_code(206);
        header("Content-Range: bytes $start-$end/$fileSize");
        header("Content-Length: $length");
    }
} else {
    header('Content-Length: ' . $fileSize);
}

// Ouvrir et lire le fichier
$fp = @fopen($filePath, 'rb');
if (!$fp) {
    http_response_code(500);
    exit;
}

if ($start > 0) {
    fseek($fp, $start);
}

// Envoyer par morceaux de 512KB
$chunkSize = 512 * 1024;
$sent = 0;

while ($sent < $length && !feof($fp) && connection_status() === CONNECTION_NORMAL) {
    $toRead = min($chunkSize, $length - $sent);
    $data = fread($fp, $toRead);
    if ($data === false) break;
    echo $data;
    flush();
    $sent += strlen($data);
}

fclose($fp);
exit;
