<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            // Si l'utilisateur n'est pas connecté, rediriger vers login au lieu de 403
            return redirect()->route('login')->with('warning', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // Flatten roles - handle both "role:client|prestataire" and "role:client,prestataire" formats
        $allRoles = [];
        foreach ($roles as $role) {
            // Split by comma or pipe to handle multiple roles in one argument
            $splitRoles = preg_split('/[,|]/', $role) ?: [];
            foreach ($splitRoles as $r) {
                $r = trim($r);
                if ($r !== '') {
                    $allRoles[] = $r;
                }
            }
        }

        // Vérifier si l'utilisateur a au moins un des rôles requis
        foreach ($allRoles as $role) {
            // Role explicit
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }

            // Fallback: si on demande le rôle 'prestataire' mais le champ role
            // n'a pas été mis à jour, autoriser si l'utilisateur a un profil
            // prestataire actif (utile pour migrations / updates partielles).
            if ($role === 'prestataire') {
                $user = $request->user();
                if (method_exists($user, 'prestataire') && $user->prestataire) {
                    // Si la colonne is_active existe sur prestataires, respecter son état
                    if (isset($user->prestataire->is_active)) {
                        if ($user->prestataire->is_active) {
                            return $next($request);
                        }
                    } else {
                        // Pas de flag is_active : autoriser
                        return $next($request);
                    }
                }
            }
        }

        // L'utilisateur est connecté mais n'a pas le bon rôle
        $currentRole = $request->user()->role ?? 'inconnu';
        $requiredRoles = implode(' ou ', $allRoles);

        // Déconnecter et rediriger vers login pour permettre de se reconnecter avec le bon compte
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('error', "Vous étiez connecté en tant que '$currentRole' mais cette page nécessite un compte '$requiredRoles'. Veuillez vous connecter avec le bon compte.");
    }
}
