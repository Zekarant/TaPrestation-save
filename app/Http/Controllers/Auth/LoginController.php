<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\RecaptchaService;

class LoginController extends Controller
{
    private const LOGIN_MAX_ATTEMPTS = 5;
    private const LOGIN_DECAY_SECONDS = 60;

    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Détecter si la requête vient d'une PWA iOS
     */
    protected function isPwaOrMobile(Request $request): bool
    {
        $userAgent = $request->header('User-Agent', '');

        // Détection PWA iOS (mode standalone)
        $isPwaStandalone = $request->header('X-Pwa-Mode') === 'standalone'
            || str_contains($userAgent, 'Mobile')
            || str_contains($userAgent, 'iPhone')
            || str_contains($userAgent, 'iPad')
            || str_contains($userAgent, 'Android');

        return $isPwaStandalone;
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $throttleKey = $this->throttleKey($request);
        if (RateLimiter::tooManyAttempts($throttleKey, self::LOGIN_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Trop de tentatives de connexion. Réessayez dans {$seconds} seconde(s).",
            ]);
        }

        // reCAPTCHA v3 verification
        $recaptchaResult = app(RecaptchaService::class)->verifyV3($request, 'login');
        if (($recaptchaResult['enabled'] ?? false) && !($recaptchaResult['success'] ?? false)) {
            RateLimiter::hit($throttleKey, self::LOGIN_DECAY_SECONDS);
            return back()->withInput($request->only('email'))
                ->withErrors(['recaptcha' => 'Échec de vérification anti-bot. Veuillez réessayer.']);
        }

        // Plus de vérification d'approbation pour les prestataires
        // Tous les prestataires peuvent se connecter directement

        // Pour PWA iOS: forcer remember-me pour éviter les déconnexions
        // Car iOS Safari en PWA ne persiste pas les cookies de session sans Max-Age
        $rememberMe = $request->boolean('remember-me');

        // Si c'est une PWA ou mobile, toujours activer remember-me
        if ($this->isPwaOrMobile($request)) {
            $rememberMe = true;
        }

        if (Auth::attempt($credentials, $rememberMe)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            // Prolonger la durée de vie du cookie de session pour iOS PWA
            if ($this->isPwaOrMobile($request)) {
                // Cookie de session avec durée étendue (3 jours — compromis UX mobile / sécurité)
                $sessionLifetime = 60 * 24 * 3; // 3 jours en minutes
                config(['session.lifetime' => $sessionLifetime]);

                // Régénérer le cookie avec la nouvelle durée
                Cookie::queue(
                    Cookie::make(
                        config('session.cookie'),
                        $request->session()->getId(),
                        $sessionLifetime,
                        config('session.path'),
                        config('session.domain'),
                        config('session.secure'),
                        config('session.http_only'),
                        false,
                        config('session.same_site')
                    )
                );
            }

            $user = Auth::user();

            // Sauvegarder l'abonnement push s'il existe en localStorage
            // L'utilisateur sera rattaché via JS côté client

            // Redirection intelligente selon le rôle
            if ($user->role === 'client') {
                return redirect()->route('client.dashboard')->with('success', 'Connexion réussie ! Bienvenue dans votre espace client.');
            } elseif ($user->role === 'prestataire') {
                // Redirection directe vers le dashboard prestataire
                return redirect()->route('prestataire.dashboard')->with('success', 'Connexion réussie ! Bienvenue dans votre espace prestataire.');
            } elseif ($user->role === 'admin') {
                return redirect()->route('administrateur.dashboard')->with('success', 'Connexion réussie ! Bienvenue dans l\'espace administrateur.');
            } elseif ($user->role === 'ambassador') {
                return redirect()->route('ambassador.dashboard')->with('success', 'Connexion réussie ! Bienvenue dans votre espace ambassadeur.');
            }

            // Fallback vers dashboard générique
            return redirect()->route('dashboard');
        }

        RateLimiter::hit($throttleKey, self::LOGIN_DECAY_SECONDS);

        throw ValidationException::withMessages([
            'email' => __('Ces identifiants ne correspondent à aucun compte. Vérifiez votre email et mot de passe.'),
        ]);
    }

    private function throttleKey(Request $request): string
    {
        $email = Str::lower(trim((string) $request->input('email', '')));
        return 'login|' . $email . '|' . $request->ip();
    }

    public function logout(Request $request)
    {
        $guard = Auth::guard();
        $recallerName = method_exists($guard, 'getRecallerName')
            ? $guard->getRecallerName()
            : null;

        $guard->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        foreach (array_unique([config('session.domain'), null]) as $domain) {
            Cookie::queue(Cookie::forget(
                config('session.cookie'),
                config('session.path'),
                $domain
            ));

            if (!empty($recallerName)) {
                Cookie::queue(Cookie::forget(
                    $recallerName,
                    config('session.path'),
                    $domain
                ));
            }
        }

        return redirect()->route('login')->with('status', 'Vous êtes déconnecté.');
    }
}
