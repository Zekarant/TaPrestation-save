<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

class RefreshCsrfToken
{
    public function handle(Request $request, Closure $next)
    {
        // Start session if not started
        if (!$request->session()->isStarted()) {
            $request->session()->start();
        }

        // For POST requests, check if CSRF token is valid
        if ($request->isMethod('POST')) {
            $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
            $sessionToken = $request->session()->token();
            
            // If tokens don't match, regenerate and try again
            if (!hash_equals($sessionToken, $token)) {
                // Log the mismatch for debugging
                \Log::warning('CSRF token mismatch', [
                    'request_token' => $token,
                    'session_token' => $sessionToken,
                    'url' => $request->url(),
                    'user_agent' => $request->userAgent()
                ]);
                
                // For AJAX requests, return JSON error
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'CSRF token mismatch. Please refresh the page.',
                        'csrf_token' => csrf_token()
                    ], 419);
                }
                
                // For form requests, redirect back with new token
                return redirect()->back()
                    ->withInput($request->except(['password', 'password_confirmation', '_token']))
                    ->withErrors(['csrf' => 'Votre session a expiré. Un nouveau token a été généré, veuillez réessayer.'])
                    ->with('new_csrf_token', csrf_token());
            }
        }

        return $next($request);
    }
}