<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    /**
     * Routes exclues de la vérification (permettre l'accès même avec profil incomplet)
     */
    protected $excludedRoutes = [
        'client.profile.edit',
        'client.profile.update',
        'client.profile.update.personal',
        'client.profile.update.security',
        'prestataire.profile.edit',
        'prestataire.profile.update',
        'prestataire.profile.update.personal',
        'prestataire.profile.update.security',
        'logout',
        'home',
        'services.index',
        'services.show',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        
        // Vérifier si c'est une route exclue
        $currentRoute = $request->route()?->getName();
        if ($currentRoute && in_array($currentRoute, $this->excludedRoutes)) {
            return $next($request);
        }

        // Vérifier si le profil est complet selon le rôle
        if ($this->isProfileIncomplete($user)) {
            $redirectRoute = $user->role === 'prestataire' 
                ? 'prestataire.profile.edit' 
                : 'client.profile.edit';

            return redirect()->route($redirectRoute)
                ->with('warning', 'Veuillez compléter votre profil (téléphone et adresse) pour accéder à cette fonctionnalité.');
        }

        return $next($request);
    }

    /**
     * Vérifier si le profil utilisateur est incomplet
     */
    protected function isProfileIncomplete($user): bool
    {
        // Ne pas vérifier les admins
        if ($user->role === 'admin') {
            return false;
        }

        // Vérifier selon le rôle
        if ($user->role === 'client') {
            $client = $user->client;
            if (!$client) {
                return true;
            }
            
            // Profil incomplet si pas de téléphone OU pas d'adresse
            return empty($client->phone) || empty($client->address);
        }

        if ($user->role === 'prestataire') {
            $prestataire = $user->prestataire;
            if (!$prestataire) {
                return true;
            }
            
            // Profil incomplet si pas de téléphone OU pas d'adresse
            return empty($prestataire->phone) || empty($prestataire->address);
        }

        return false;
    }
}
