<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFileUploadSize
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si la requête contient des fichiers
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            
            // Vérifier la taille du fichier (100MB = 104857600 bytes)
            if ($file->getSize() > 104857600) {
                return redirect()->back()
                    ->with('error', 'Le fichier vidéo est trop volumineux. La taille maximale autorisée est de 100MB.')
                    ->withInput();
            }
            
            // Vérifier le type MIME
            $allowedMimes = ['video/mp4', 'video/quicktime', 'video/webm'];
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                return redirect()->back()
                    ->with('error', 'Format de fichier non supporté. Utilisez MP4, MOV ou WebM.')
                    ->withInput();
            }
        }
        
        // Vérifier si la taille de la requête POST dépasse la limite
        $contentLength = $request->server('CONTENT_LENGTH');
        $postMaxSize = $this->parseSize(ini_get('post_max_size'));
        
        if ($contentLength > $postMaxSize) {
            return redirect()->back()
                ->with('error', 'Le fichier est trop volumineux pour être traité. Veuillez réduire la taille de votre vidéo.')
                ->withInput();
        }
        
        return $next($request);
    }
    
    /**
     * Parse size string to bytes
     */
    private function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        
        return round($size);
    }
}