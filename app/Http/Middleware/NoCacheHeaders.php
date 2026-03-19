<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCacheHeaders
{
    /**
     * Handle an incoming request.
     * 
     * Prevents browser from caching HTML pages which causes issues with:
     * - Back/forward cache (bfcache) serving stale pages
     * - CSRF token mismatches
     * - Navigation appearing to not work (clicking links twice)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only add no-cache headers for HTML responses (not API/JSON/assets)
        if ($this->isHtmlResponse($response)) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }

        return $response;
    }

    /**
     * Check if the response is HTML content.
     */
    private function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        return str_contains($contentType, 'text/html') || empty($contentType);
    }
}
