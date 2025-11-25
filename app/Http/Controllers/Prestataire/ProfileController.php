<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Prestataire;
use App\Models\Category;
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
        $this->middleware(['auth', 'role:prestataire']);
    }

    /**
     * Affiche le formulaire d'édition du profil.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;
        $categories = Category::orderBy('name')->get();
        
        // Calculer le pourcentage de complétion du profil
        $completionPercentage = $prestataire ? $this->calculateProfileCompletion($prestataire) : 0;
        
        return view('prestataire.profile.edit', [
            'user' => $user,
            'prestataire' => $prestataire,
            'categories' => $categories,
            'completion_percentage' => $completionPercentage
        ]);
    }

    /**
     * Met à jour les informations personnelles du prestataire.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePersonalInfo(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

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
            'description' => 'nullable|string',
            'daily_rate' => 'nullable|numeric|min:0|max:9999.99',
            'average_delivery_time' => 'nullable|integer|min:1|max:365',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        // Mise à jour des informations utilisateur
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        // Mise à jour du profil prestataire
        if (!$prestataire) {
            $prestataire = new Prestataire();
            $prestataire->user_id = $user->id;
            $prestataire->is_approved = false;
        }

        $prestataireData = $request->only(['phone', 'description', 'daily_rate', 'average_delivery_time']);

        // Gestion de la photo de profil
        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo s'il existe
            if ($prestataire->photo && Storage::disk('public')->exists($prestataire->photo)) {
                Storage::disk('public')->delete($prestataire->photo);
            }

            // Stocker la nouvelle photo
            $prestataireData['photo'] = $request->file('photo')->store('photos/prestataires', 'public');
        }

        $prestataire->fill($prestataireData);
        $prestataire->save();

        // Calculer le pourcentage de complétion du profil
        $completionPercentage = $this->calculateProfileCompletion($prestataire);

        return redirect()->route('prestataire.profile.edit')
            ->with('success', 'Informations personnelles mises à jour avec succès !')
            ->with('completion_percentage', $completionPercentage);
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

        return redirect()->route('prestataire.profile.edit')
            ->with('success', 'Mot de passe mis à jour avec succès !');
    }

    /**
     * Met à jour le profil du prestataire.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

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
            'description' => 'nullable|string',
            'daily_rate' => 'nullable|numeric|min:0|max:9999.99',
            'average_delivery_time' => 'nullable|integer|min:1|max:365',
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

        // Mise à jour du profil prestataire
        if (!$prestataire) {
            $prestataire = new Prestataire();
            $prestataire->user_id = $user->id;
            $prestataire->is_approved = false;
        }

        $prestataireData = $request->only(['phone', 'description', 'daily_rate', 'average_delivery_time']);

        // Gestion de la photo de profil
        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo s'il existe
            if ($prestataire->photo && Storage::disk('public')->exists($prestataire->photo)) {
                Storage::disk('public')->delete($prestataire->photo);
            }

            // Stocker la nouvelle photo
            $prestataireData['photo'] = $request->file('photo')->store('photos/prestataires', 'public');
        }

        $prestataire->fill($prestataireData);
        $prestataire->save();

        // Calculer le pourcentage de complétion du profil
        $completionPercentage = $this->calculateProfileCompletion($prestataire);

        return redirect()->route('prestataire.profile.edit')
            ->with('success', 'Profil mis à jour avec succès !')
            ->with('completion_percentage', $completionPercentage);
    }

    /**
     * Affiche le profil public du prestataire.
     *
     * @param  \App\Models\Prestataire|null  $prestataire
     * @return \Illuminate\View\View
     */
    public function show(Prestataire $prestataire = null)
    {
        // Si aucun prestataire n'est fourni, utiliser celui de l'utilisateur connecté
        if (!$prestataire) {
            $user = Auth::user();
            $prestataire = $user->prestataire;
            
            if (!$prestataire) {
                return redirect()->route('prestataire.dashboard')
                    ->with('error', 'Profil prestataire non trouvé.');
            }
        }
        
        // Statistiques du prestataire
        $stats = [
            'total_services' => $prestataire->services()->count(),
            'active_services' => $prestataire->services()->where('status', 'active')->count(),
            'total_reviews' => $prestataire->reviews()->count(),
            'average_rating' => $prestataire->reviews()->avg('rating') ?: 0,
            'member_since' => $prestataire->user->created_at->format('F Y'),
            'approval_status' => $prestataire->is_approved ? 'approved' : 'pending'
        ];
        
        // Services récents
        $recentServices = $prestataire->services()
            ->where('status', 'active')
            ->latest()
            ->take(3)
            ->get();
        
        // Avis récents
        $recentReviews = $prestataire->reviews()
            ->with('client.user')
            ->latest()
            ->take(5)
            ->get();
        
        return view('prestataire.profile.show', [
            'user' => $prestataire->user,
            'prestataire' => $prestataire,
            'stats' => $stats,
            'recentServices' => $recentServices,
            'recentReviews' => $recentReviews
        ]);
    }

    /**
     * Supprime la photo du prestataire.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deletePhoto()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;
        
        if ($prestataire && $prestataire->photo) {
            // Supprimer le fichier du stockage
            if (Storage::disk('public')->exists($prestataire->photo)) {
                Storage::disk('public')->delete($prestataire->photo);
            }
            
            // Mettre à jour la base de données
            $prestataire->photo = null;
            $prestataire->save();
        }
        
        // Calculer le pourcentage de complétion du profil
        $completionPercentage = $this->calculateProfileCompletion($prestataire);
        
        return redirect()->route('prestataire.profile.edit')
            ->with('success', 'Photo supprimée avec succès.')
            ->with('completion_percentage', $completionPercentage);
    }

    /**
     * Affiche le profil public d'un prestataire (accessible à tous).
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function publicShow($id)
    {
        $prestataire = Prestataire::with(['user', 'services', 'reviews.client'])
            ->where('is_approved', true)
            ->findOrFail($id);
        
        // Statistiques publiques
        $stats = [
            'total_services' => $prestataire->services()->where('status', 'active')->count(),
            'total_reviews' => $prestataire->reviews()->count(),
            'average_rating' => round($prestataire->reviews()->avg('rating') ?: 0, 1),
            'member_since' => $prestataire->user->created_at->format('F Y')
        ];
        
        // Services actifs
        $services = $prestataire->services()
            ->where('status', 'active')
            ->latest()
            ->paginate(6);
        
        // Avis récents
        $reviews = $prestataire->reviews()
            ->with('client.user')
            ->latest()
            ->paginate(10);
        
        return view('prestataires.show', [
            'prestataire' => $prestataire,
            'stats' => $stats,
            'services' => $services,
            'reviews' => $reviews
        ]);
    }
    
    /**
     * Calcule le pourcentage de complétion du profil.
     *
     * @param  \App\Models\Prestataire  $prestataire
     * @return int
     */
    private function calculateProfileCompletion($prestataire)
    {
        // Define all the fields that contribute to profile completion
        $totalFields = 8; // Total number of fields we're checking
        $completedFields = 0;
        
        // User-related fields
        if ($prestataire && $prestataire->user && $prestataire->user->name) {
            $completedFields++;
        }
        
        if ($prestataire && $prestataire->user && $prestataire->user->email) {
            $completedFields++;
        }
        
        // Prestataire-specific fields
        if ($prestataire && $prestataire->phone) {
            $completedFields++;
        }
        
        if ($prestataire && $prestataire->description && strlen($prestataire->description) >= 50) {
            $completedFields++;
        }
        
        if ($prestataire && $prestataire->photo) {
            $completedFields++;
        }
        
        if ($prestataire && $prestataire->address && $prestataire->city && $prestataire->postal_code) {
            $completedFields++;
        }
        
        if ($prestataire && $prestataire->services()->count() > 0) {
            $completedFields++;
        }
        
        if ($prestataire && $prestataire->user && $prestataire->user->email_verified_at) {
            $completedFields++;
        }
        
        // Calculate percentage
        return $totalFields > 0 ? round(($completedFields / $totalFields) * 100) : 0;
    }
    
    /**
     * Affiche l'aperçu du profil public.
     *
     * @return \Illuminate\View\View
     */
    public function preview()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;
        
        if (!$prestataire) {
            return redirect()->route('prestataire.dashboard')
                ->with('error', 'Profil prestataire non trouvé.');
        }
        
        // Statistiques du prestataire
        $stats = [
            'total_services' => $prestataire->services()->where('status', 'active')->count(),
            'total_reviews' => $prestataire->reviews()->count(),
            'average_rating' => round($prestataire->reviews()->avg('rating') ?: 0, 1),
            'member_since' => $prestataire->user->created_at->format('F Y')
        ];
        
        // Services actifs
        $services = $prestataire->services()
            ->where('status', 'active')
            ->latest()
            ->take(6)
            ->get();
        
        // Avis récents
        $reviews = $prestataire->reviews()
            ->with('client.user')
            ->latest()
            ->take(5)
            ->get();
        
        return view('prestataire.profile.preview', [
            'prestataire' => $prestataire,
            'stats' => $stats,
            'services' => $services,
            'reviews' => $reviews,
            'is_preview' => true
        ]);
    }

    /**
     * Supprime définitivement le compte prestataire.
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
        $prestataire = $user->prestataire;

        // Supprimer les fichiers associés
        if ($prestataire) {
            // Supprimer la photo de profil
            if ($prestataire->photo && Storage::disk('public')->exists($prestataire->photo)) {
                Storage::disk('public')->delete($prestataire->photo);
            }
        }

        // Déconnecter l'utilisateur
        Auth::logout();

        // Supprimer l'utilisateur (cascade sur le prestataire)
        $user->delete();

        // Invalider la session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Votre compte a été supprimé avec succès.');
    }
}