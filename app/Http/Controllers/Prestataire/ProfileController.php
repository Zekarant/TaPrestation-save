<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePrestataireProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\User;
use App\Models\Prestataire;
use App\Models\Category;
use App\Models\Service;
use App\Services\ProfileUpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $this->middleware(['auth', 'role:prestataire'])->except(['publicShow']);
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
    public function updatePersonalInfo(UpdatePrestataireProfileRequest $request)
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        $profileService = app(ProfileUpdateService::class);
        $profileService->updateUserIdentity($user, $request->name, $request->email);

        // Mise à jour du profil prestataire
        if (!$prestataire) {
            $prestataire = new Prestataire();
            $prestataire->user_id = $user->id;
            $prestataire->is_approved = false;
        }

        $prestataireData = $request->safe()->only([
            'company_name',
            'phone',
            'address',
            'description',
            'daily_rate',
            'average_delivery_time',
            'category_id',
            'subcategory_id',
            'secteur_activite',
            'competences',
            'latitude',
            'longitude'
        ]);

        // Gestion de la photo de profil
        $newPhoto = $profileService->handlePhotoUpload($request, $prestataire->photo, 'photos/prestataires');
        if ($newPhoto !== null) {
            $prestataireData['photo'] = $newPhoto;
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
            : 'Mot de passe mis à jour avec succès !';

        return redirect()->route('prestataire.profile.edit')
            ->with('success', $message);
    }

    /**
     * Met à jour le profil du prestataire.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
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

        $prestataireData = $request->only(['phone', 'address', 'description', 'daily_rate', 'average_delivery_time']);

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
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(?Prestataire $prestataire = null)
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
        $prestataire = Prestataire::with([
            'user',
            'services' => function ($query) {
                $query->latest();
            },
            'videos' => function ($query) {
                $query->latest();
            },
            'reviews' => function ($query) {
                $query->with(['client'])->latest();
            },
            'equipments' => function ($query) {
                $query->where('status', 'active')
                    ->where('is_available', true)
                    ->latest();
            },
            'urgentSales' => function ($query) {
                $query->latest();
            }
        ])
            ->where('is_approved', true)
            ->findOrFail($id);

        // Get limited services (10) and all services
        $limitedServices = $prestataire->services()->where('status', 'active')->take(10)->get();
        $allServices = $prestataire->services()->where('status', 'active')->get();

        // Get limited equipments (10) and all equipments
        $limitedEquipments = $prestataire->equipments()->where('status', 'active')->where('is_available', true)->take(10)->get();
        $allEquipments = $prestataire->equipments()->where('status', 'active')->where('is_available', true)->get();

        // Get limited urgent sales (10) and all urgent sales
        $limitedUrgentSales = $prestataire->urgentSales()->take(10)->get();
        $allUrgentSales = $prestataire->urgentSales()->get();

        // Load all reviews
        $allReviews = $prestataire->reviews()->with(['client'])->latest()->get();

        // Get similar services from other prestataires
        $serviceIds = $prestataire->services->pluck('id')->toArray();
        $categoryIds = DB::table('service_category')
            ->whereIn('service_id', $serviceIds)
            ->pluck('category_id')
            ->toArray();

        $similarServices = Service::with(['prestataire.user', 'categories'])
            ->where('prestataire_id', '!=', $prestataire->id)
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->where('status', 'active')
            ->inRandomOrder()
            ->limit(3)
            ->get();

        // Get food products - Afficher si food_enabled OU si le prestataire a des produits food
        $foodProducts = collect();
        $hasFoodProducts = \App\Models\FoodProduct::where('prestataire_id', $prestataire->id)->exists();

        if ($prestataire->food_enabled || $hasFoodProducts) {
            $foodProducts = \App\Models\FoodProduct::where('prestataire_id', $prestataire->id)
                ->where('is_available', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            // Si aucun produit disponible mais le presta en a (tous indisponibles), afficher quand même
            if ($foodProducts->isEmpty() && $hasFoodProducts) {
                $foodProducts = \App\Models\FoodProduct::where('prestataire_id', $prestataire->id)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get();
            }
        }

        // Check if user can leave a review
        $existingReview = null;
        $hasInteracted = false;
        if (Auth::check() && Auth::user()->client) {
            $clientId = Auth::user()->client->id;
            $existingReview = \App\Models\Review::where('client_id', $clientId)
                ->where('prestataire_id', $prestataire->id)
                ->first();
            $hasInteracted = \App\Models\Booking::where('client_id', $clientId)
                ->where('prestataire_id', $prestataire->id)
                ->exists()
                || \App\Models\Message::where('sender_id', Auth::id())
                    ->where('receiver_id', $prestataire->user_id)
                    ->exists();
        }

        return view('prestataire.profile.show', [
            'prestataire' => $prestataire,
            'limitedServices' => $limitedServices,
            'allServices' => $allServices,
            'limitedEquipments' => $limitedEquipments,
            'allEquipments' => $allEquipments,
            'limitedUrgentSales' => $limitedUrgentSales,
            'allUrgentSales' => $allUrgentSales,
            'allReviews' => $allReviews,
            'similarServices' => $similarServices,
            'foodProducts' => $foodProducts,
            'existingReview' => $existingReview,
            'hasInteracted' => $hasInteracted
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
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
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
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Used for error reporting in catch block
        $prestataire = $user?->prestataire;

        // Normalize confirmation to avoid case/whitespace mismatch
        $request->merge([
            'confirmation' => strtoupper(trim((string) $request->input('confirmation'))),
        ]);

        // Vérifier si l'utilisateur est un compte Google/Apple sans mot de passe
        $isGoogleUser = $user->isSocialAccount();

        if ($isGoogleUser) {
            // Utilisateur Google : pas de vérification de mot de passe
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

        try {
            DB::beginTransaction();

            // ========================================
            // SUPPRESSION COMPLÈTE DU COMPTE PRESTATAIRE
            // ========================================

            // 1. Nettoyer les références FK qui n'ont pas ON DELETE CASCADE/SET NULL
            DB::table('prestataires')
                ->where('approved_by', $user->id)
                ->update(['approved_by' => null]);

            DB::table('reviews')
                ->where('moderated_by', $user->id)
                ->update(['moderated_by' => null]);

            DB::table('messages')
                ->where('moderated_by', $user->id)
                ->update(['moderated_by' => null]);

            // 2. Archiver les infos Stripe avant suppression
            if (
                class_exists(\App\Models\DeletedStripeAccount::class) &&
                TableExistenceCache::has('deleted_stripe_accounts')
            ) {
                try {
                    \App\Models\DeletedStripeAccount::archiveFromUser($user);
                } catch (\Throwable $stripeArchiveError) {
                    Log::warning('Failed to archive Stripe info during account deletion', [
                        'user_id' => $user->id,
                        'error' => $stripeArchiveError->getMessage(),
                    ]);
                }
            }

            // 3. Supprimer DÉFINITIVEMENT les données du prestataire
            if ($prestataire) {
                // Archiver (soft delete) les disponibilités
                $prestataire->availabilities()->delete();

                // Audit 4.6: delete direct au lieu de boucles get()+delete() (N+1)
                $prestataire->services()->delete();
                $prestataire->equipments()->delete();
                $prestataire->urgentSales()->delete();
                $prestataire->foodProducts()->delete();

                // Archiver les vidéos
                $prestataire->videos()->delete();

                // Détacher les followers
                if (method_exists($prestataire, 'followers')) {
                    $prestataire->followers()->detach();
                }

                // Archiver les réservations liées au prestataire
                \App\Models\Booking::where('prestataire_id', $prestataire->id)->delete();

                // Archiver les commandes food (soft delete)
                \App\Models\FoodOrder::where('prestataire_id', $prestataire->id)->delete();

                // Archiver les demandes de location
                if (class_exists(\App\Models\EquipmentRentalRequest::class)) {
                    \App\Models\EquipmentRentalRequest::where('prestataire_id', $prestataire->id)->delete();
                }

                // Soft delete du prestataire (archivage)
                $prestataire->delete();
            }

            // 4. Supprimer l'utilisateur (soft delete si disponible, sinon delete normal)
            $user->delete();

            DB::commit();

            // Déconnexion et nettoyage session APRES succès DB
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')
                ->with('success', 'Votre compte a été supprimé avec succès.');

        } catch (\Throwable $e) {
            DB::rollBack();

            $ref = 'ACCDEL-' . now()->format('Ymd-His') . '-' . (($user?->id) ? (string) $user->id : 'unknown');

            Log::error('Prestataire account deletion failed', [
                'ref' => $ref,
                'user_id' => $user?->id,
                'prestataire_id' => $prestataire?->id,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            // Also send to the default exception handler/log channels
            report($e);

            return back()->with('error', 'Une erreur technique est survenue lors de la suppression du compte. Référence: ' . $ref);
        }
    }
}
