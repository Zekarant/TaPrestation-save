<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = bin2hex(random_bytes(16));
        View::share('cspNonce', $nonce);

        $response = $next($request);

        if (str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            $content = (string) $response->getContent();

            if ($content !== '') {
                $content = preg_replace('/<script(?![^>]*\bnonce=)/i', '<script nonce="' . $nonce . '"', $content) ?? $content;

                $response->setContent($content);
            }
        }

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(self), camera=(self), microphone=(self)');

        $response->headers->set('Content-Security-Policy', implode(' ', [
            "default-src 'self' https: data: blob:;",
            "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval' https://js.stripe.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://maps.googleapis.com https://cdn.onesignal.com blob:;",
            "style-src 'self' https: 'unsafe-inline';",
            "img-src 'self' https: data: blob:;",
            "font-src 'self' https: data:;",
            "connect-src 'self' https: wss:;",
            "frame-src 'self' https:;",
            "media-src 'self' https: data: blob:;",
            "object-src 'none';",
            "frame-ancestors 'self';",
            "base-uri 'self';",
        ]));

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
