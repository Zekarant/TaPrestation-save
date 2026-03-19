<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateClientProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\User;
use App\Models\Client;
use App\Services\ProfileUpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

use App\Support\TableExistenceCache;
class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Permettre aux prestataires d'accéder en mode client
        $this->middleware(['auth', 'role:client,prestataire']);
    }

    /**
     * Affiche le formulaire d'édition du profil.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Si c'est un prestataire, rediriger vers son profil prestataire
        if ($user->role === 'prestataire') {
            return redirect()->route('prestataire.profile.edit')
                ->with('info', 'Vous avez été redirigé vers votre profil prestataire.');
        }

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
    public function updatePersonalInfo(UpdateClientProfileRequest $request)
    {
        $user = Auth::user();
        $client = $user->client;

        $profileService = app(ProfileUpdateService::class);
        $profileService->updateUserIdentity($user, $request->name, $request->email);

        // Mise à jour ou création du profil client
        if (!$client) {
            $client = new Client();
            $client->user_id = $user->id;
        }

        $client->phone = $request->phone;
        $client->address = $request->address;
        $client->bio = $request->bio;

        // Gestion de la photo
        $newPhoto = $profileService->handlePhotoUpload($request, $client->photo, 'avatars/clients');
        if ($newPhoto !== null) {
            $client->photo = $newPhoto;
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
    public function updateSecurity(UpdatePasswordRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isGoogleUser = $user->isSocialAccount();

        // Mise à jour du mot de passe
        $user->password = Hash::make($request->new_password);
        $user->save();

        $message = $isGoogleUser
            ? 'Mot de passe créé avec succès ! Vous pouvez maintenant vous connecter avec votre email et mot de passe.'
            : 'Mot de passe mis à jour avec succès.';

        return redirect()->route('client.profile.edit')
            ->with('success', $message);
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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'current_password' => 'nullable|required_with:new_password|current_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        $profileService = app(ProfileUpdateService::class);
        $profileService->updateUserIdentity($user, $request->name, $request->email);

        // Mise à jour du mot de passe si fourni
        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
            $user->save();
        }

        // Mise à jour ou création du profil client
        if (!$client) {
            $client = new Client();
            $client->user_id = $user->id;
        }

        $client->phone = $request->phone;
        $client->address = $request->address;
        $client->bio = $request->bio;

        // Gestion de la photo
        $newPhoto = $profileService->handlePhotoUpload($request, $client->photo, 'avatars/clients');
        if ($newPhoto !== null) {
            $client->photo = $newPhoto;
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
        $recentRequests = $client ? $client->bookings()->orderBy('created_at', 'desc')->take(5)->get() : collect([]);

        // Récupérer les avis reçus
        $reviews = $client ? $client->reviews()->with(['prestataire.user', 'service'])->latest()->get() : collect([]);

        // Statistiques du client
        $stats = [
            'total_requests' => $client ? $client->bookings()->count() : 0,
            'completed_requests' => $client ? $client->bookings()->where('status', 'completed')->count() : 0,
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
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Normalize confirmation to avoid case/whitespace mismatch
        $request->merge([
            'confirmation' => strtoupper(trim((string) $request->input('confirmation'))),
        ]);

        // Vérifier si l'utilisateur est un compte Google/Apple sans mot de passe
        $isGoogleUser = $user->isSocialAccount();

        if ($isGoogleUser) {
            // Utilisateur Google/Apple : pas de vérification de mot de passe
            $request->validate([
                'confirmation' => 'required|in:DELETE,SUPPRIMER'
            ]);
        } else {
            // Utilisateur classique : vérification du mot de passe
            $request->validate([
                'password' => 'required|current_password',
                'confirmation' => 'required|in:DELETE,SUPPRIMER'
            ]);
        }

        $client = $user->client;

        try {
            DB::beginTransaction();

            // ========================================
            // SUPPRESSION COMPLÈTE DU COMPTE CLIENT
            // ========================================

            // 1. Supprimer les fichiers associés
            if ($client && $client->photo) {
                if (Storage::disk('public')->exists($client->photo)) {
                    Storage::disk('public')->delete($client->photo);
                }
            }

            // 2. Archiver les infos Stripe avant suppression
            if (
                class_exists(\App\Models\DeletedStripeAccount::class) &&
                TableExistenceCache::has('deleted_stripe_accounts')
            ) {
                try {
                    \App\Models\DeletedStripeAccount::archiveFromUser($user);
                } catch (\Throwable $stripeArchiveError) {
                    Log::warning('Failed to archive Stripe info during client account deletion', [
                        'user_id' => $user->id,
                        'error' => $stripeArchiveError->getMessage(),
                    ]);
                }
            }

            // 3. Archiver les données liées au client (soft delete)
            if ($client) {
                // Archiver les réservations du client
                \App\Models\Booking::where('client_id', $client->id)->delete();

                // Archiver les commandes food du client (soft delete)
                \App\Models\FoodOrder::where('client_id', $user->id)->delete();

                // Archiver les demandes client
                if (class_exists('App\\Models\\ClientRequest')) {
                    DB::table('client_requests')->where('client_id', $client->id)->delete();
                }

                // Archiver le profil client
                $client->delete();
            }

            // 4. Archiver l'utilisateur
            $user->delete();

            DB::commit();

            // Déconnecter l'utilisateur APRÈS succès DB
            Auth::logout();

            // Invalider la session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')
                ->with('success', 'Votre compte a été supprimé avec succès.');

        } catch (\Throwable $e) {
            DB::rollBack();

            $ref = 'ACCDEL-' . now()->format('Ymd-His') . '-' . (($user?->id) ? (string) $user->id : 'unknown');

            Log::error('Client account deletion failed', [
                'ref' => $ref,
                'user_id' => $user?->id,
                'client_id' => $client?->id ?? null,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            report($e);

            return back()->with('error', 'Une erreur technique est survenue lors de la suppression du compte. Référence: ' . $ref);
        }
    }
}