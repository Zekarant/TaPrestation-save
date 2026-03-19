<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Client;
use App\Models\Prestataire;
use App\Models\Category;
use App\Models\UserSignupAudit;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * Vérifie si un nom ressemble à du spam (chaîne aléatoire)
     */
    private function isSpamName(string $name): bool
    {
        $name = trim($name);

        // Trop court ou trop long sans espaces
        if (strlen($name) < 2)
            return true;
        if (strlen($name) > 15 && strpos($name, ' ') === false)
            return true;

        // Pas de voyelles = suspect (ex: "jKLmnPQrst")
        if (!preg_match('/[aeiouAEIOUàâäéèêëïîôùûüÿœæ]/u', $name))
            return true;

        // Trop de majuscules consécutives au milieu (ex: "jAJuWRzLx")
        if (preg_match('/[a-z][A-Z]{2,}[a-z]/u', $name))
            return true;

        // Alternance suspecte majuscule/minuscule (ex: "aAbBcCdD")
        $upperLowerAlternations = preg_match_all('/[a-z][A-Z]|[A-Z][a-z]/', $name);
        if ($upperLowerAlternations > 5)
            return true;

        // Ratio consonnes/voyelles anormal
        $vowels = preg_match_all('/[aeiouAEIOUàâäéèêëïîôùûüÿœæ]/u', $name);
        $consonants = preg_match_all('/[bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ]/u', $name);
        if ($consonants > 0 && $vowels > 0) {
            $ratio = $consonants / $vowels;
            if ($ratio > 5)
                return true; // Trop de consonnes
        }

        // Contient des patterns suspects
        $suspiciousPatterns = [
            '/^[a-z]{1}[A-Z]{2,}/u',  // Commence par minuscule puis majuscules
            '/[A-Z]{4,}/u',            // 4+ majuscules consécutives
            '/(.)(\1){3,}/u',          // Même caractère 4+ fois
        ];
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $name))
                return true;
        }

        return false;
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm(Request $request)
    {
        // Get main categories for the registration form (services only)
        $categories = Category::ofTypeService()->whereNull('parent_id')->orderBy('name')->get();

        // Récupérer les données sociales de la session (si connexion via Google/Apple)
        $socialData = session('social_data', []);

        return view('auth.register', compact('categories', 'socialData'));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // ========== PROTECTION ANTI-BOT ==========

        // 1. Honeypot - si ce champ caché est rempli, c'est un bot
        if ($request->filled('website_url') || $request->filled('company_website')) {
            \Log::warning('Bot détecté via honeypot', [
                'ip' => $request->ip(),
                'email' => $request->input('email'),
                'honeypot_value' => $request->input('website_url') ?: $request->input('company_website'),
            ]);
            // Simuler un succès pour ne pas alerter le bot
            return redirect()->route('login')->with('success', 'Compte créé avec succès !');
        }

        // 2. Temps minimum de remplissage (5 secondes minimum)
        $formStartTime = (int) $request->input('_form_start', 0);
        if ($formStartTime > 0) {
            $elapsedSeconds = time() - $formStartTime;
            if ($elapsedSeconds < 5) {
                \Log::warning('Bot détecté via temps trop rapide', [
                    'ip' => $request->ip(),
                    'email' => $request->input('email'),
                    'elapsed_seconds' => $elapsedSeconds,
                ]);
                return redirect()->back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors(['name' => 'Une erreur est survenue. Veuillez réessayer.']);
            }
        }

        // 3. Détection de nom suspect (chaîne aléatoire)
        $name = $request->input('name', '');
        if ($this->isSpamName($name)) {
            \Log::warning('Bot détecté via nom suspect', [
                'ip' => $request->ip(),
                'email' => $request->input('email'),
                'name' => $name,
            ]);
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['name' => 'Le nom semble invalide. Veuillez entrer votre vrai nom.']);
        }

        // 4. Rate limiting par IP (max 3 inscriptions par heure)
        $ip = $request->ip();
        $cacheKey = 'register_attempts_' . md5($ip);
        $attempts = (int) cache($cacheKey, 0);
        if ($attempts >= 3) {
            \Log::warning('Rate limit atteint pour inscription', ['ip' => $ip]);
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'Trop de tentatives d\'inscription. Réessayez dans 1 heure.']);
        }
        cache([$cacheKey => $attempts + 1], now()->addHour());

        // ========== FIN PROTECTION ANTI-BOT ==========

        // reCAPTCHA v3 verification (optional, controlled by env/config)
        $recaptchaResult = app(RecaptchaService::class)->verifyV3($request, 'register');
        if (($recaptchaResult['enabled'] ?? false) && !($recaptchaResult['success'] ?? false)) {
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['recaptcha' => 'Échec de vérification anti-bot. Veuillez réessayer.']);
        }

        \Log::info('Register request received', [
            'user_type' => $request->input('user_type'),
            'has_social_provider' => $request->filled('social_provider'),
            'method' => $request->method(),
            'url' => $request->url(),
        ]);

        // Vérifier si inscription via réseau social
        $isSocialRegister = $request->filled('social_provider') && $request->filled('social_id');

        // Vérifier si un utilisateur soft-deleted existe avec cet email
        $existingSoftDeletedUser = User::withTrashed()
            ->where('email', $request->email)
            ->whereNotNull('deleted_at')
            ->first();

        // Validation des champs communs
        // Si un utilisateur soft-deleted existe, on ne vérifie pas l'unicité de l'email
        $emailRules = ['required', 'string', 'email:rfc,dns', 'max:255'];
        if (!$existingSoftDeletedUser) {
            $emailRules[] = 'unique:users,email,NULL,id,deleted_at,NULL';
        }

        $commonRules = [
            'name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\pL\s\-]+$/u'],
            'email' => $emailRules,
            'user_type' => ['required', 'in:client,prestataire'],
        ];

        // Mot de passe obligatoire seulement si pas d'inscription sociale
        if (!$isSocialRegister) {
            $commonRules['password'] = ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'];
        } else {
            // Si inscription sociale et mot de passe fourni, le valider quand même
            $commonRules['password'] = ['nullable', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'];
        }

        $commonMessages = [
            'name.required' => 'Le nom est obligatoire.',
            'name.min' => 'Le nom doit contenir au moins 2 caractères.',
            'name.regex' => 'Le nom ne peut contenir que des lettres, espaces et tirets.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.',
            'user_type.required' => 'Vous devez choisir un type de compte.',
            'user_type.in' => 'Le type de compte sélectionné n\'est pas valide.',
        ];

        // Validation conditionnelle selon le type d'utilisateur
        if ($request->user_type === 'client') {
            $clientRules = [
                'phone' => ['required', 'string', 'max:20'],
                'location' => ['nullable', 'string', 'max:255'],
                'client_profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
            ];

            $clientMessages = [
                'phone.required' => 'Le numéro de téléphone est obligatoire.',
                'phone.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
                'location.max' => 'La localisation ne peut pas dépasser 255 caractères.',
                'client_profile_photo.image' => 'Le fichier doit être une image.',
                'client_profile_photo.mimes' => 'L\'image doit être au format JPEG, PNG, JPG ou GIF.',
            ];

            $request->validate(
                array_merge($commonRules, $clientRules),
                array_merge($commonMessages, $clientMessages)
            );

        } elseif ($request->user_type === 'prestataire') {
            $prestataireRules = [
                'company_name' => ['required', 'string', 'min:2', 'max:255'],
                'phone' => ['required', 'string', 'max:20'],
                'category_id' => ['required', 'exists:categories,id'],
                'subcategory_id' => ['required', 'exists:categories,id'],
                'city' => ['required', 'string', 'max:255'],
                'prestataire_profile_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
                'description' => ['nullable', 'string', 'max:500'],
                'portfolio_url' => ['nullable', 'url', 'max:255'],
            ];

            $prestataireMessages = [
                'company_name.required' => 'Le nom de l\'enseigne est obligatoire.',
                'company_name.min' => 'Le nom de l\'enseigne doit contenir au moins 2 caractères.',
                'company_name.max' => 'Le nom de l\'enseigne ne peut pas dépasser 255 caractères.',
                'phone.required' => 'Le numéro de téléphone est obligatoire.',
                'phone.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
                'category_id.required' => 'La catégorie est obligatoire.',
                'category_id.exists' => 'La catégorie sélectionnée n\'est pas valide.',
                'subcategory_id.required' => 'La sous-catégorie est obligatoire.',
                'subcategory_id.exists' => 'La sous-catégorie sélectionnée n\'est pas valide.',
                'city.required' => 'La ville est obligatoire.',
                'city.max' => 'La ville ne peut pas dépasser 255 caractères.',
                'prestataire_profile_photo.required' => 'Une photo de profil est obligatoire pour les prestataires.',
                'prestataire_profile_photo.image' => 'Le fichier doit être une image.',
                'prestataire_profile_photo.mimes' => 'L\'image doit être au format JPEG, PNG, JPG ou GIF.',
                'description.max' => 'La description ne peut pas dépasser 500 caractères.',
                'portfolio_url.url' => 'Le lien du portfolio doit être une URL valide.',
                'portfolio_url.max' => 'Le lien du portfolio ne peut pas dépasser 255 caractères.',
            ];

            $request->validate(
                array_merge($commonRules, $prestataireRules),
                array_merge($commonMessages, $prestataireMessages)
            );
        } else {
            // Si jamais un type inconnu arrive, on ne valide que les champs communs
            $request->validate($commonRules, $commonMessages);
        }

        // Création ou restauration de l'utilisateur
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->user_type,
            'email_verified_at' => $isSocialRegister ? now() : null, // Email vérifié si inscription sociale
            'password_setup_required' => $isSocialRegister && !$request->filled('password'),
        ];

        // Mot de passe : soit fourni, soit généré aléatoirement pour inscription sociale
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        } elseif ($isSocialRegister) {
            $userData['password'] = Hash::make(Str::random(32)); // Mot de passe aléatoire sécurisé
        }

        // Ajouter le provider_id si inscription sociale
        if ($isSocialRegister) {
            if ($request->social_provider === 'google') {
                $userData['google_id'] = $request->social_id;
            } elseif ($request->social_provider === 'apple') {
                $userData['apple_id'] = $request->social_id;
            }
        }

        // Si un utilisateur soft-deleted existe, le restaurer et mettre à jour
        if ($existingSoftDeletedUser) {
            $existingSoftDeletedUser->restore();
            $existingSoftDeletedUser->fill(collect($userData)->except(['role', 'password_setup_required'])->all());
            $existingSoftDeletedUser->role = $userData['role'];
            $existingSoftDeletedUser->password_setup_required = (bool) $userData['password_setup_required'];
            $existingSoftDeletedUser->save();
            $user = $existingSoftDeletedUser->fresh();

            // Supprimer l'ancien profil client/prestataire associé s'il existe
            if ($user->client) {
                $user->client->forceDelete();
            }
            if ($user->prestataire) {
                $user->prestataire->forceDelete();
            }

            \Log::info('Utilisateur restauré après soft-delete', ['user_id' => $user->id]);
        } else {
            $user = new User();
            $user->fill(collect($userData)->except(['role', 'password_setup_required'])->all());
            $user->role = $userData['role'];
            $user->password_setup_required = (bool) $userData['password_setup_required'];
            $user->save();
        }

        // Audit comportemental (IP, UA, clics, temps, score reCAPTCHA)
        try {
            UserSignupAudit::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => (string) $request->headers->get('referer'),
                'accept_language' => (string) $request->headers->get('accept-language'),
                'clicks' => $request->integer('behavior_clicks') ?: null,
                'keypresses' => $request->integer('behavior_keypresses') ?: null,
                'time_to_submit_ms' => $request->integer('behavior_time_ms') ?: null,
                'recaptcha_version' => ($recaptchaResult['enabled'] ?? false) ? 'v3' : null,
                'recaptcha_action' => $recaptchaResult['action'] ?? null,
                'recaptcha_score' => $recaptchaResult['score'] ?? null,
                'recaptcha_success' => (bool) ($recaptchaResult['success'] ?? false),
                'recaptcha_error_codes' => $recaptchaResult['error_codes'] ?? null,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Failed to write signup audit', ['error' => $e->getMessage()]);
        }

        // Nettoyer la session d'inscription sociale
        session()->forget('social_register');

        // Données spécifiques au type
        if ($request->user_type === 'client') {
            $clientPhotoPath = null;

            if ($request->hasFile('client_profile_photo')) {
                $clientPhotoPath = $request->file('client_profile_photo')
                    ->store('profile_photos/clients', 'public');
            }

            Client::create([
                'user_id' => $user->id,
                'photo' => $clientPhotoPath,
                'location' => $request->location,
                'phone' => $request->phone,
                'email_visible' => $request->boolean('email_visible'),
                'phone_visible' => $request->boolean('phone_visible'),
            ]);

        } elseif ($request->user_type === 'prestataire') {
            $prestatairePhotoPath = null;

            if ($request->hasFile('prestataire_profile_photo')) {
                $prestatairePhotoPath = $request->file('prestataire_profile_photo')
                    ->store('profile_photos/prestataires', 'public');
            }

            // Récupérer les noms de catégorie et sous-catégorie
            $category = Category::find($request->category_id);
            $subcategory = Category::find($request->subcategory_id);

            Prestataire::create([
                'user_id' => $user->id,
                'company_name' => $request->company_name,
                'phone' => $request->phone,
                'city' => $request->city,
                'photo' => $prestatairePhotoPath,
                'secteur_activite' => $category ? $category->name : null,
                'competences' => $subcategory ? $subcategory->name : null,
                'description' => $request->description,
                'portfolio_url' => $request->portfolio_url,
                'email_visible' => $request->boolean('email_visible'),
                'phone_visible' => $request->boolean('phone_visible', true), // Par défaut visible pour prestataires
            ]);

            // Connexion automatique
            auth()->login($user);

            // Envoyer l'email de vérification si ce n'est pas une inscription sociale
            if (!$isSocialRegister) {
                $user->sendEmailVerificationNotification();
            }

            // Si le mode abonnement est activé, rediriger vers la page de paiement
            if (function_exists('subscription_enabled') && subscription_enabled()) {
                return redirect()
                    ->route('prestataire.subscription.payment')
                    ->with('info', 'Pour finaliser votre inscription, veuillez procéder au paiement de votre abonnement.');
            }

            // Message différent selon inscription sociale ou non
            $message = $isSocialRegister
                ? 'Votre compte prestataire a été créé avec succès.'
                : 'Votre compte prestataire a été créé avec succès. Un email de vérification vous a été envoyé.';

            // Sinon, redirection normale
            return redirect()
                ->route('home')
                ->with('success', $message);
        }

        // Connexion automatique (pour les clients)
        auth()->login($user);

        // Envoyer l'email de vérification si ce n'est pas une inscription sociale
        if (!$isSocialRegister) {
            $user->sendEmailVerificationNotification();
        }

        // Message différent selon inscription sociale ou non
        $message = $isSocialRegister
            ? 'Votre compte a été créé avec succès.'
            : 'Votre compte a été créé avec succès. Un email de vérification vous a été envoyé.';

        // ✅ Redirection vers la home
        return redirect()
            ->route('home')
            ->with('success', $message);
    }
}
