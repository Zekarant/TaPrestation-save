<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client;
use App\Models\Prestataire;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private function buildSocialUserAttributes(object $socialUser, string $provider, string $role): array
    {
        return [
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Utilisateur',
            'email' => $socialUser->getEmail(),
            'password' => Hash::make(Str::random(64)),
            'email_verified_at' => now(),
            'role' => $role,
            'google_id' => $provider === 'google' ? $socialUser->getId() : null,
            'apple_id' => $provider === 'apple' ? $socialUser->getId() : null,
            'avatar' => $socialUser->getAvatar(),
            'password_setup_required' => true,
        ];
    }

    private function createSocialUser(array $attributes): User
    {
        $user = new User();
        $user->fill(collect($attributes)->except(['role', 'password_setup_required'])->all());
        $user->role = $attributes['role'];
        $user->password_setup_required = (bool) ($attributes['password_setup_required'] ?? false);
        $user->save();

        return $user;
    }

    /**
     * Redirect the user to the provider authentication page.
     *
     * @param  string  $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider($provider)
    {
        if (!in_array($provider, ['google', 'apple'])) {
            abort(404);
        }

        // Stocker le mode (connexion ou inscription) et le rôle si fourni
        if (request()->has('role')) {
            // Mode INSCRIPTION : rôle fourni (client ou prestataire)
            session(['social_login_role' => request('role')]);
            session(['social_login_mode' => 'register']);
        } else {
            // Mode CONNEXION : pas de rôle = tentative de connexion uniquement
            session(['social_login_mode' => 'login']);
            session()->forget('social_login_role');
        }

        try {
            return Socialite::driver($provider)->redirect();
        } catch (\Exception $e) {
            return redirect()->route('register')
                ->with('error', 'La connexion avec ' . ucfirst($provider) . ' n\'est pas configurée. Veuillez vous inscrire manuellement.');
        }
    }

    /**
     * Obtain the user information from the provider.
     *
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback($provider)
    {
        if (!in_array($provider, ['google', 'apple'])) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            \Log::warning('Social auth callback failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('login')->with('error', 'Erreur lors de la connexion avec ' . ucfirst($provider) . '. Veuillez réessayer.');
        }

        // Chercher l'utilisateur actif
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Utilisateur existant actif - connexion directe
            if ($provider === 'google' && !$user->google_id) {
                $user->update(['google_id' => $socialUser->getId()]);
            } elseif ($provider === 'apple' && !$user->apple_id) {
                $user->update(['apple_id' => $socialUser->getId()]);
            }
            
            // Update avatar if not set
            if (!$user->avatar && $socialUser->getAvatar()) {
                $user->update(['avatar' => $socialUser->getAvatar()]);
            }

            // Régénérer la session pour éviter les problèmes de fixation
            request()->session()->regenerate();
            
            // Connecter l'utilisateur avec "remember me"
            Auth::login($user, true);
            
            // Nettoyer les sessions
            session()->forget(['social_login_mode', 'social_login_role']);
            
            // Rediriger selon le rôle
            $redirectTo = match($user->role) {
                'prestataire' => route('prestataire.dashboard'),
                'driver' => route('driver.dashboard'),
                'admin' => route('admin.dashboard'),
                default => route('home'),
            };
            
            return redirect($redirectTo)->with('success', 'Connexion réussie !');
        }

        // Vérifier si un utilisateur archivé existe (compte supprimé)
        $archivedUser = User::withTrashed()
            ->where('email', $socialUser->getEmail())
            ->whereNotNull('deleted_at')
            ->first();

        // L'utilisateur n'existe pas (ou est archivé) - vérifier le mode
        $mode = session('social_login_mode', 'login');
        $role = session('social_login_role', 'client');
        
        // Nettoyer les sessions
        session()->forget(['social_login_mode', 'social_login_role']);

        // MODE CONNEXION : l'utilisateur n'a pas de compte actif → afficher page de choix
        if ($mode === 'login') {
            // Stocker les données en session pour création/restauration ultérieure
            session([
                'social_pending_user' => [
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Utilisateur',
                    'email' => $socialUser->getEmail(),
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'has_archived_account' => $archivedUser !== null,
                    'archived_role' => $archivedUser?->role,
                ]
            ]);
            
            // Afficher la page de choix Client ou Prestataire
            return view('auth.social-choose-role', [
                'socialUser' => [
                    'name' => $socialUser->getName() ?? 'Utilisateur',
                    'email' => $socialUser->getEmail(),
                ],
                'provider' => $provider,
                'hasArchivedAccount' => $archivedUser !== null,
                'archivedRole' => $archivedUser?->role,
            ]);
        }

        // MODE INSCRIPTION : créer le compte avec le rôle choisi
        if (!in_array($role, ['client', 'prestataire'])) {
            $role = 'client';
        }

        // Créer l'utilisateur directement
        $user = $this->createSocialUser(
            $this->buildSocialUserAttributes($socialUser, $provider, $role)
        );

        // Créer le profil selon le rôle
        if ($role === 'prestataire') {
            Prestataire::create([
                'user_id' => $user->id,
                'is_approved' => false,
            ]);
        } else {
            Client::create([
                'user_id' => $user->id,
            ]);
        }

        // Connecter l'utilisateur
        Auth::login($user, true);

        // Rediriger selon le rôle
        $redirectTo = $role === 'prestataire' 
            ? route('prestataire.dashboard') 
            : route('home');

        return redirect($redirectTo)
            ->with('success', 'Bienvenue ' . $user->name . ' ! Votre compte a été créé avec succès.');
    }

    /**
     * Créer un client directement depuis les données sociales
     */
    protected function createClientFromSocial($socialUser, $provider)
    {
        $user = $this->createSocialUser(
            $this->buildSocialUserAttributes($socialUser, $provider, 'client')
        );
        
        // Créer le profil client
        Client::create([
            'user_id' => $user->id,
        ]);

        Auth::login($user);
        session()->forget('social_register');

        return redirect()->route('home')
            ->with('success', 'Bienvenue ' . $user->name . ' ! Votre compte a été créé avec succès.');
    }

    /**
     * Créer un compte depuis la page de choix (après callback Google en mode connexion)
     */
    public function createAccountFromChoice(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'role' => 'required|in:client,prestataire',
            'provider' => 'required|in:google,apple',
        ]);

        // Récupérer les données stockées en session
        $pendingUser = session('social_pending_user');
        
        if (!$pendingUser) {
            return redirect()->route('login')
                ->with('error', 'Session expirée. Veuillez réessayer.');
        }

        $role = $request->role;
        $provider = $request->provider;

        // Vérifier que l'utilisateur actif n'existe pas
        $activeUser = User::where('email', $pendingUser['email'])->first();
        if ($activeUser) {
            session()->forget('social_pending_user');
            Auth::login($activeUser, true);
            return redirect()->route($activeUser->role === 'prestataire' ? 'prestataire.dashboard' : 'client.dashboard')
                ->with('success', 'Content de vous revoir !');
        }

        // Vérifier s'il existe un compte archivé à restaurer
        $archivedUser = null;
        try {
            $archivedUser = User::withTrashed()
                ->where('email', $pendingUser['email'])
                ->whereNotNull('deleted_at')
                ->first();
        } catch (\Exception $e) {
            // Ignorer si colonne deleted_at manquante
        }

        if ($archivedUser) {
            // Restaurer le compte mais il repart à zéro
            $archivedUser->restore();
            
            // Mettre à jour avec le nouveau rôle choisi
            $archivedUser->google_id = $provider === 'google' ? $pendingUser['provider_id'] : $archivedUser->google_id;
            $archivedUser->apple_id = $provider === 'apple' ? $pendingUser['provider_id'] : $archivedUser->apple_id;
            $archivedUser->avatar = $pendingUser['avatar'] ?? $archivedUser->avatar;
            $archivedUser->role = $role;
            $archivedUser->password_setup_required = true;
            $archivedUser->password = Hash::make(Str::random(64));
            $archivedUser->save();

            // Créer le nouveau profil selon le rôle (ne pas restaurer l'ancien)
            if ($role === 'prestataire') {
                // Supprimer l'ancien prestataire s'il existe
                try {
                    Prestataire::withTrashed()->where('user_id', $archivedUser->id)->forceDelete();
                } catch (\Exception $e) {
                    \Log::warning('Failed to force-delete trashed prestataire for user #' . $archivedUser->id . ': ' . $e->getMessage());
                }
                
                Prestataire::create([
                    'user_id' => $archivedUser->id,
                    'is_approved' => false,
                ]);
            } else {
                // Supprimer l'ancien client s'il existe
                try {
                    Client::withTrashed()->where('user_id', $archivedUser->id)->forceDelete();
                } catch (\Exception $e) {
                    \Log::warning('Failed to force-delete trashed client for user #' . $archivedUser->id . ': ' . $e->getMessage());
                }
                
                Client::create([
                    'user_id' => $archivedUser->id,
                ]);
            }

            session()->forget('social_pending_user');
            Auth::login($archivedUser, true);

            // Rediriger vers profile pour qu'il remplisse ses infos
            return redirect()->route('profile.edit')
                ->with('warning', 'Bienvenue ! Complétez votre profil pour accéder à toutes les fonctionnalités.');
        }

        // Créer un nouveau compte
        $user = $this->createSocialUser([
            'name' => $pendingUser['name'],
            'email' => $pendingUser['email'],
            'password' => Hash::make(Str::random(64)),
            'email_verified_at' => now(),
            'role' => $role,
            'google_id' => $provider === 'google' ? $pendingUser['provider_id'] : null,
            'apple_id' => $provider === 'apple' ? $pendingUser['provider_id'] : null,
            'avatar' => $pendingUser['avatar'] ?? null,
            'password_setup_required' => true,
        ]);

        // Créer le profil selon le rôle
        if ($role === 'prestataire') {
            Prestataire::create([
                'user_id' => $user->id,
                'is_approved' => false,
            ]);
        } else {
            Client::create([
                'user_id' => $user->id,
            ]);
        }

        // Nettoyer la session
        session()->forget('social_pending_user');

        // Connecter l'utilisateur
        Auth::login($user, true);

        // Rediriger selon le rôle
        if ($role === 'prestataire') {
            // Pour les nouveaux prestataires, rediriger vers le paiement d'abonnement si activé
            return redirect()->route('prestataire.subscription.payment')
                ->with('success', 'Bienvenue ' . $user->name . ' ! Veuillez activer votre abonnement pour commencer.');
        }

        return redirect()->route('home')
            ->with('success', 'Bienvenue ' . $user->name . ' ! Votre compte a été créé avec succès.');
    }

    /**
     * Restaurer un compte archivé et ses données associées
     */
    protected function restoreArchivedAccount($archivedUser, $newRole, $provider, $pendingUser)
    {
        $oldRole = $archivedUser->role;
        
        // Restaurer l'utilisateur
        $archivedUser->restore();
        
        // Mettre à jour les infos de connexion sociale
        $archivedUser->google_id = $provider === 'google' ? $pendingUser['provider_id'] : $archivedUser->google_id;
        $archivedUser->apple_id = $provider === 'apple' ? $pendingUser['provider_id'] : $archivedUser->apple_id;
        $archivedUser->avatar = $pendingUser['avatar'] ?? $archivedUser->avatar;
        $archivedUser->role = $newRole;
        $archivedUser->password_setup_required = true;
        $archivedUser->password = Hash::make(Str::random(64));
        $archivedUser->save();

        // Si le rôle est le même qu'avant, restaurer les données associées
        if ($oldRole === $newRole) {
            if ($newRole === 'prestataire') {
                // Restaurer le prestataire et toutes ses données
                $prestataire = Prestataire::withTrashed()
                    ->where('user_id', $archivedUser->id)
                    ->first();
                
                if ($prestataire) {
                    $prestataire->restore();
                    
                    // Restaurer les services
                    $prestataire->services()->onlyTrashed()->restore();
                    
                    // Restaurer les équipements
                    $prestataire->equipments()->onlyTrashed()->restore();
                    
                    // Restaurer les ventes urgentes
                    $prestataire->urgentSales()->onlyTrashed()->restore();
                    
                    // Restaurer les produits food
                    $prestataire->foodProducts()->onlyTrashed()->restore();
                    
                    $message = 'Bon retour ' . $archivedUser->name . ' ! Votre compte prestataire et toutes vos données ont été restaurés.';
                } else {
                    // Créer un nouveau profil prestataire
                    Prestataire::create([
                        'user_id' => $archivedUser->id,
                        'is_approved' => false,
                    ]);
                    $message = 'Bon retour ' . $archivedUser->name . ' ! Votre compte a été restauré.';
                }
            } else {
                // Restaurer le client
                $client = Client::withTrashed()
                    ->where('user_id', $archivedUser->id)
                    ->first();
                
                if ($client) {
                    $client->restore();
                    $message = 'Bon retour ' . $archivedUser->name . ' ! Votre compte client a été restauré.';
                } else {
                    Client::create([
                        'user_id' => $archivedUser->id,
                    ]);
                    $message = 'Bon retour ' . $archivedUser->name . ' ! Votre compte a été restauré.';
                }
            }
        } else {
            // Rôle différent : créer un nouveau profil sans restaurer les anciennes données
            if ($newRole === 'prestataire') {
                // Vérifier s'il n'y a pas déjà un prestataire (actif ou archivé)
                $existingPrestataire = Prestataire::withTrashed()
                    ->where('user_id', $archivedUser->id)
                    ->first();
                
                if (!$existingPrestataire) {
                    Prestataire::create([
                        'user_id' => $archivedUser->id,
                        'is_approved' => false,
                    ]);
                } else {
                    $existingPrestataire->restore();
                }
            } else {
                // Vérifier s'il n'y a pas déjà un client (actif ou archivé)
                $existingClient = Client::withTrashed()
                    ->where('user_id', $archivedUser->id)
                    ->first();
                
                if (!$existingClient) {
                    Client::create([
                        'user_id' => $archivedUser->id,
                    ]);
                } else {
                    $existingClient->restore();
                }
            }
            $message = 'Bienvenue ' . $archivedUser->name . ' ! Votre compte a été recréé en tant que ' . ($newRole === 'prestataire' ? 'prestataire' : 'client') . '.';
        }

        // Nettoyer la session
        session()->forget('social_pending_user');

        // Connecter l'utilisateur
        Auth::login($archivedUser, true);

        // Rediriger - pour prestataire, aller vers paiement abonnement
        if ($newRole === 'prestataire') {
            return redirect()->route('prestataire.subscription.payment')
                ->with('success', $message);
        }

        return redirect()->route('home')->with('success', $message);
    }
}
