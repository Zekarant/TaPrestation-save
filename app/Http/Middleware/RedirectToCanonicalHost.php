<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $canonicalHost = trim((string) env('CANONICAL_HOST', ''));
        if ($canonicalHost === '') {
            return $next($request);
        }

        $canonicalScheme = trim((string) env('CANONICAL_SCHEME', ''));
        if ($canonicalScheme === '') {
            $canonicalScheme = $request->getScheme();
        }

        $currentHost = $request->getHost();
        if (strcasecmp($currentHost, $canonicalHost) === 0) {
            return $next($request);
        }

        $target = $canonicalScheme . '://' . $canonicalHost . $request->getRequestUri();

        return redirect()->away($target, 301);
    }
}
