<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
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
        
        // Vérifier si l'utilisateur existe
        $user = User::where('email', $request->email)->first();

        // Plus de vérification d'approbation pour les prestataires
        // Tous les prestataires peuvent se connecter directement

        if (Auth::attempt($credentials, $request->boolean('remember-me'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Redirection intelligente selon le rôle
            if ($user->role === 'client') {
                return redirect()->route('client.dashboard')->with('success', 'Connexion réussie ! Bienvenue dans votre espace client.');
            } elseif ($user->role === 'prestataire') {
                // Redirection directe vers le dashboard prestataire
                return redirect()->route('prestataire.dashboard')->with('success', 'Connexion réussie ! Bienvenue dans votre espace prestataire.');
            } elseif ($user->role === 'admin') {
                 return redirect()->route('administrateur.dashboard')->with('success', 'Connexion réussie ! Bienvenue dans l\'espace administrateur.');
            }
            
            // Fallback vers dashboard générique
            return redirect()->route('dashboard');
        }

        throw ValidationException::withMessages([
            'email' => __('Ces identifiants ne correspondent à aucun compte. Vérifiez votre email et mot de passe.'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
