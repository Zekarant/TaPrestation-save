<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Prestataire;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Support\TableExistenceCache;
class BecomeProviderController extends Controller
{
    /**
     * Convertir directement le client en prestataire et rediriger vers le profil
     * Plus de formulaire multi-étapes, le prestataire complète son profil après
     */
    public function index()
    {
        $user = Auth::user();
        
        // Vérifier si l'utilisateur est déjà prestataire actif
        if ($user->prestataire) {
            return redirect()->route('prestataire.dashboard')
                ->with('info', 'Vous êtes déjà prestataire !');
        }

        // Afficher la page de confirmation rapide
        return view('client.become-provider.quick-confirm', [
            'user' => $user,
            'hasArchivedAccount' => false,
            'archivedPrestataire' => null,
        ]);
    }

    /**
     * Conversion rapide : créer le prestataire et rediriger vers le profil
     */
    public function quickConvert(Request $request)
    {
        $user = Auth::user();
        
        // Vérifier si l'utilisateur est déjà prestataire actif
        if ($user->prestataire) {
            return redirect()->route('prestataire.dashboard');
        }

        try {
            DB::beginTransaction();

            // Récupérer les données du client s'il existe
            $client = $user->client;
            
            // Récupérer téléphone et adresse depuis le client OU le user
            $phone = '';
            $address = '';
            $city = '';
            $postalCode = '';
            
            if ($client) {
                $phone = $client->phone ?? '';
                $address = $client->address ?? '';
                // Le client n'a pas de city/postal_code, vérifier le user
                $city = $user->city ?? '';
                $postalCode = $user->postal_code ?? '';
            } else {
                // Sinon prendre du user directement
                $phone = $user->phone ?? '';
                $address = $user->address ?? '';
                $city = $user->city ?? '';
                $postalCode = $user->postal_code ?? '';
            }

            // Créer le profil prestataire avec les données existantes
            $prestataire = Prestataire::create([
                'user_id' => $user->id,
                'company_name' => '', // Vide - à remplir dans le profil
                'bio' => '',
                'phone' => $phone,
                'address' => $address,
                'city' => $city,
                'postal_code' => $postalCode,
                'is_verified' => false,
                'is_active' => true,
            ]);

            // Mettre à jour le rôle de l'utilisateur
            $user->update(['role' => 'prestataire']);

            DB::commit();

            // Rafraîchir et reconnecter
            $user->refresh();
            Auth::login($user);

            // Vérifier si l'abonnement est activé
            $subscriptionEnabled = false;
            try {
                if (TableExistenceCache::has('settings')) {
                    $subscriptionEnabled = DB::table('settings')
                        ->where('key', 'subscription_enabled')
                        ->value('value') === '1';
                }
            } catch (\Exception $e) {
                // Ignorer
            }

            // Rediriger vers le paiement si abonnement activé, sinon profil
            if ($subscriptionEnabled) {
                return redirect()->route('prestataire.subscription.payment')
                    ->with('success', 'Activez votre abonnement pour continuer.');
            }

            // Sinon rediriger vers le profil pour remplir le nom de l'enseigne
            return redirect()->route('prestataire.profile.edit')
                ->with('warning', 'Complétez votre profil (nom de l\'enseigne, etc.) pour accéder au tableau de bord.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('BecomeProvider quickConvert error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->with('error', 'Une erreur est survenue lors de l\'activation du compte prestataire.');
        }
    }

    /**
     * Sauvegarder une étape du formulaire (legacy - gardé pour compatibilité)
     */
    public function storeStep(Request $request, $step)
    {
        $user = Auth::user();
        
        $sessionKey = 'become_provider_step_' . $step;

        // Valider selon l'étape
        switch ($step) {
            case 1: // Informations de base
                $validated = $request->validate([
                    'company_name' => 'nullable|string|max:255',
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'phone' => 'required|string|max:20',
                    'is_company' => 'boolean',
                    'siret' => 'nullable|string|max:14',
                ]);
                break;

            case 2: // Adresse et localisation
                $validated = $request->validate([
                    'address' => 'required|string|max:500',
                    'city' => 'required|string|max:255',
                    'postal_code' => 'required|string|max:10',
                    'country' => 'required|string|max:100',
                    'latitude' => 'nullable|numeric',
                    'longitude' => 'nullable|numeric',
                    'service_radius' => 'nullable|integer|min:5|max:200',
                ]);
                break;

            case 3: // Catégories et services
                $validated = $request->validate([
                    'categories' => 'required|array|min:1',
                    'categories.*' => 'exists:categories,id',
                    'custom_services' => 'nullable|string|max:1000',
                ]);
                break;

            case 4: // Profil et description
                $validated = $request->validate([
                    'bio' => 'required|string|min:50|max:2000',
                    'experience_years' => 'nullable|integer|min:0|max:50',
                    'certifications' => 'nullable|string|max:500',
                    'languages' => 'nullable|array',
                ]);
                break;

            case 5: // Photos et documents
                $validated = $request->validate([
                    'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                    'portfolio_images' => 'nullable|array|max:10',
                    'portfolio_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                    'documents' => 'nullable|array|max:5',
                    'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
                ]);

                // Gérer l'upload des fichiers
                if ($request->hasFile('profile_photo')) {
                    $path = $request->file('profile_photo')->store('prestataires/profiles', 'public');
                    session(['become_provider_profile_photo' => $path]);
                }

                if ($request->hasFile('portfolio_images')) {
                    $portfolioPaths = [];
                    foreach ($request->file('portfolio_images') as $image) {
                        $portfolioPaths[] = $image->store('prestataires/portfolio', 'public');
                    }
                    session(['become_provider_portfolio' => $portfolioPaths]);
                }

                if ($request->hasFile('documents')) {
                    $docPaths = [];
                    foreach ($request->file('documents') as $doc) {
                        $docPaths[] = $doc->store('prestataires/documents', 'public');
                    }
                    session(['become_provider_documents' => $docPaths]);
                }
                break;

            case 6: // Tarifs et disponibilités
                $validated = $request->validate([
                    'hourly_rate' => 'nullable|numeric|min:0',
                    'daily_rate' => 'nullable|numeric|min:0',
                    'minimum_price' => 'nullable|numeric|min:0',
                    'accepts_remote' => 'boolean',
                    'available_weekends' => 'boolean',
                    'available_evenings' => 'boolean',
                ]);
                break;
        }

        foreach (['is_company', 'accepts_remote', 'available_weekends', 'available_evenings'] as $booleanField) {
            if ($request->has($booleanField) || array_key_exists($booleanField, $validated)) {
                $validated[$booleanField] = $request->boolean($booleanField);
            }
        }

        session([$sessionKey => $validated]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'step' => $step,
                'next_step' => $step + 1,
            ]);
        }

        return back()->with('success', 'Étape enregistrée');
    }

    /**
     * Finaliser la création du compte prestataire
     */
    public function finalize(Request $request)
    {
        $user = Auth::user();

        // Vérifier si l'utilisateur est déjà prestataire
        if ($user->prestataire) {
            return redirect()->route('prestataire.dashboard');
        }

        // Valider les données du formulaire simplifié
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'portfolio_url' => 'nullable|url|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Mettre à jour l'utilisateur
            $user->update([
                'phone' => $validated['phone'],
                'city' => $validated['city'],
                'role' => 'prestataire',
            ]);

            // Créer le profil prestataire (champs minimaux comme à l'inscription)
            $prestataire = Prestataire::create([
                'user_id' => $user->id,
                'company_name' => $validated['company_name'],
                'bio' => $validated['description'],
                'portfolio_url' => $validated['portfolio_url'] ?? null,
                'is_verified' => false,
                'is_active' => true,
            ]);

            // Note: Les catégories seront ajoutées via les services que le prestataire créera

            DB::commit();

            // Rafraîchir le modèle utilisateur pour avoir le nouveau rôle
            $user->refresh();
            
            // Forcer la mise à jour de la session avec le nouveau rôle
            Auth::login($user);

            // Si requête AJAX, retourner JSON (le JS gère la redirection)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Félicitations ! Votre compte prestataire a été créé avec succès.',
                    'redirect' => route('prestataire.dashboard')
                ]);
            }

            return redirect()->route('prestataire.dashboard')
                ->with('success', 'Félicitations ! Votre compte prestataire a été créé avec succès. Bienvenue sur TaPrestation !');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log l'erreur pour debug
            \Log::error('BecomeProvider finalize error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Une erreur est survenue lors de la création du compte prestataire.',
                ], 500);
            }

            return back()->with('error', 'Une erreur est survenue lors de la création du compte prestataire.');
        }
    }

    /**
     * Afficher les avantages de devenir prestataire
     */
    public function benefits()
    {
        try {
            $stats = [
                'prestataires' => Prestataire::count(),
                'categories' => Category::count(),
                // La colonne 'rating' n'existe pas sur prestataires - utiliser une valeur par défaut
                'average_rating' => 4.5,
            ];
        } catch (\Exception $e) {
            $stats = [
                'prestataires' => 0,
                'categories' => 0,
                'average_rating' => 4.5,
            ];
        }

        return view('client.become-provider.benefits', compact('stats'));
    }

    /**
     * Calculer le pourcentage de complétion
     */
    private function calculateCompletion($data)
    {
        $fields = ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'postal_code'];
        $filled = 0;

        foreach ($fields as $field) {
            if (!empty($data[$field])) {
                $filled++;
            }
        }

        return round(($filled / count($fields)) * 100);
    }

    /**
     * Nettoyer les données de session
     */
    private function clearSessionData()
    {
        for ($i = 1; $i <= 6; $i++) {
            session()->forget('become_provider_step_' . $i);
        }
        session()->forget('become_provider_profile_photo');
        session()->forget('become_provider_portfolio');
        session()->forget('become_provider_documents');
    }

    /**
     * Réactiver un profil prestataire existant pour l'utilisateur courant.
     */
    public function reactivate(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return redirect()->route('client.become-provider.index')
                ->with('error', 'Aucun profil prestataire trouvé pour votre compte.');
        }

        try {
            $prestataire->is_active = true;
            $prestataire->save();

            // S'assurer que l'utilisateur a bien le rôle prestataire
            if ($user->role !== 'prestataire') {
                $user->update(['role' => 'prestataire']);
            }

            // Rafraîchir et reconnecter proprement pour mettre à jour la session (éviter 403 middleware role)
            $user->refresh();
            // Forcer un relogin complet
            \Auth::logout();
            \Auth::loginUsingId($user->id);
            session()->regenerate();

            return redirect()->route('prestataire.dashboard')
                ->with('success', 'Votre profil prestataire a été réactivé.');
        } catch (\Exception $e) {
            \Log::error('Reactivate prestataire error: ' . $e->getMessage(), ['user_id' => $user->id]);
            return back()->with('error', 'Impossible de réactiver votre profil pour le moment.');
        }
    }
}
