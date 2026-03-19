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

        // Permettre aux clients ET aux prestataires d'accéder à l'espace client
        // Les prestataires peuvent utiliser le mode client pour commander chez d'autres prestataires
        if ($user->role === 'client') {
            // Vérifier que l'utilisateur a un profil client
            if (!$user->client) {
                return redirect()->route('home')->with('error', 'Vous devez avoir un profil client pour accéder à cette page.');
            }
        } elseif ($user->role === 'prestataire') {
            // Les prestataires peuvent accéder en "mode client"
            // Ils pourront commander/réserver chez d'autres prestataires (le blocage auto-commande est dans les controllers)
            if (!$user->prestataire) {
                return redirect()->route('home')->with('error', 'Votre profil prestataire n\'est pas configuré correctement.');
            }
        } else {
            return redirect()->route('home')->with('error', 'Accès refusé. Cette page est réservée aux clients et prestataires.');
        }

        return $next($request);
    }
}