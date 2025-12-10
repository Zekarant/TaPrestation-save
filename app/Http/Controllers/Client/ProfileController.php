<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:client']);
    }

    /**
     * Affiche le formulaire d'édition du profil.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $user = Auth::user();
        $client = $user->client;
        return view('client.profile.edit', [
            'user' => $user,
            'client' => $client
        ]);
    }

    /**
     * Met à jour les informations personnelles du client.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePersonalInfo(Request $request)
    {
        $user = Auth::user();
        $client = $user->client;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'bio' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Mise à jour des informations utilisateur
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        // Mise à jour ou création du profil client
        if (!$client) {
            $client = new Client();
            $client->user_id = $user->id;
        }

        $client->phone = $request->phone;
        $client->address = $request->address;
        $client->bio = $request->bio;

        // Gestion de la photo
        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($client->photo && Storage::disk('public')->exists($client->photo)) {
                Storage::disk('public')->delete($client->photo);
            }

            // Stocker la nouvelle photo
            $photoPath = $request->file('photo')->store('avatars/clients', 'public');
            $client->photo = $photoPath;
        }

        $client->save();

        return redirect()->route('client.profile.edit')
            ->with('success', 'Informations personnelles mises à jour avec succès.');
    }

    /**
     * Met à jour la sécurité du compte (mot de passe).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSecurity(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|min:8|confirmed',
        ]);

        // Mise à jour du mot de passe
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('client.profile.edit')
            ->with('success', 'Mot de passe mis à jour avec succès.');
    }

    /**
     * Met à jour le profil du client (ancienne méthode pour compatibilité).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $client = $user->client;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'bio' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'current_password' => 'nullable|required_with:new_password|current_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        // Mise à jour des informations utilisateur
        $user->name = $request->name;
        $user->email = $request->email;

        // Mise à jour du mot de passe si fourni
        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        // Mise à jour ou création du profil client
        if (!$client) {
            $client = new Client();
            $client->user_id = $user->id;
        }

        $client->phone = $request->phone;
        $client->address = $request->address;
        $client->bio = $request->bio;

        // Gestion de la photo
        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($client->photo && Storage::disk('public')->exists($client->photo)) {
                Storage::disk('public')->delete($client->photo);
            }

            // Stocker la nouvelle photo
            $photoPath = $request->file('photo')->store('avatars/clients', 'public');
            $client->photo = $photoPath;
        }

        $client->save();

        return redirect()->route('client.profile.edit')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Affiche le profil public du client.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $user = Auth::user();
        $client = $user->client;

        // Récupérer les demandes récentes
        $recentRequests = $user->clientRequests()->orderBy('created_at', 'desc')->take(5)->get();

        // Récupérer les avis reçus
        $reviews = $client ? $client->reviews()->with(['prestataire.user', 'service'])->latest()->get() : collect([]);

        // Statistiques du client
        $stats = [
            'total_requests' => $user->clientRequests()->count(),
            'completed_requests' => $user->clientRequests()->where('status', 'completed')->count(),
            'following_count' => $client ? $client->followedPrestataires()->count() : 0,
            'average_rating' => $reviews->avg('rating'),
            'member_since' => $user->created_at->format('F Y')
        ];

        return view('client.profile.show', [
            'user' => $user,
            'client' => $client,
            'stats' => $stats,
            'recentRequests' => $recentRequests,
            'reviews' => $reviews
        ]);
    }

    /**
     * Supprime la photo du client.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAvatar()
    {
        $user = Auth::user();
        $client = $user->client;

        if ($client && $client->photo) {
            // Supprimer le fichier du stockage
            if (Storage::disk('public')->exists($client->photo)) {
                Storage::disk('public')->delete($client->photo);
            }

            // Mettre à jour la base de données
            $client->photo = null;
            $client->save();

            return response()->json(['success' => true, 'message' => 'Photo supprimée avec succès.']);
        }

        return response()->json(['success' => false, 'message' => 'Aucune photo à supprimer.']);
    }

    /**
     * Supprime définitivement le compte client.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
            'confirmation' => 'required|in:DELETE'
        ]);

        $user = Auth::user();
        $client = $user->client;

        // Supprimer les fichiers associés
        if ($client && $client->photo) {
            if (Storage::disk('public')->exists($client->photo)) {
                Storage::disk('public')->delete($client->photo);
            }
        }

        // Déconnecter l'utilisateur
        Auth::logout();

        // Supprimer l'utilisateur (cascade sur le client)
        $user->delete();

        // Invalider la session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Votre compte a été supprimé avec succès.');
    }
}