<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ClientMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (Response)  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier que l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        $user = Auth::user();

        // Vérifier que l'utilisateur a le rôle client
        if ($user->role !== 'client') {
            return redirect()->route('home')->with('error', 'Accès refusé. Cette page est réservée aux clients.');
        }

        // Vérifier que l'utilisateur a un profil client
        if (!$user->client) {
            return redirect()->route('home')->with('error', 'Vous devez avoir un profil client pour accéder à cette page.');
        }

        return $next($request);
    }
}