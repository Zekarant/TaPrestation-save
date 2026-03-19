<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use App\Support\TableExistenceCache;
use App\Models\User;
use App\Models\Category;
use App\Models\Prestataire;

class AdminSettingsController extends Controller
{
    private const LEGACY_SECRET_SETTING_KEYS = [
        'stripe_key',
        'stripe_secret',
        'stripe_webhook_secret',
    ];

    /**
     * Helper pour récupérer les settings de manière sécurisée
     */
    private function getSettings(): array
    {
        if (!TableExistenceCache::has('settings')) {
            return [];
        }
        try {
            return DB::table('settings')->pluck('value', 'key')->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 1. Paramètres généraux du site
     */
    public function general()
    {
        $settings = $this->getSettings();
        return view('admin.settings.general', compact('settings'));
    }

    public function updateGeneral(Request $request)
    {
        if (!TableExistenceCache::has('settings')) {
            return back()->with('error', 'La table settings n\'existe pas. Veuillez exécuter les migrations.');
        }
        
        $settings = $request->only([
            'site_name', 'site_description', 'contact_email', 'contact_phone',
            'address', 'social_facebook', 'social_instagram', 'social_twitter', 'social_linkedin'
        ]);

        foreach ($settings as $key => $value) {
            if ($value !== null) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'updated_at' => now()]
                );
            }
        }

        Cache::forget('site_settings');
        return back()->with('success', 'Paramètres généraux mis à jour avec succès.');
    }

    /**
     * 2. Paramètres des commissions
     */
    public function commissions()
    {
        $commissions = TableExistenceCache::has('commission_settings') 
            ? DB::table('commission_settings')->get() 
            : collect();
        $categories = Category::all();

        // Settings commissions (par type) + exemptions
        $paymentSettings = function_exists('get_payment_settings') ? get_payment_settings() : $this->getSettings();

        $prestataires = TableExistenceCache::has('prestataires')
            ? Prestataire::with('user')->latest('id')->paginate(15, ['*'], 'prestataires_page')
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, ['path' => request()->url(), 'pageName' => 'prestataires_page']);

        $clients = TableExistenceCache::has('users')
            ? User::query()->whereHas('client')->latest('id')->paginate(15, ['*'], 'clients_page')
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, ['path' => request()->url(), 'pageName' => 'clients_page']);

        return view('admin.settings.commissions', compact('commissions', 'categories', 'paymentSettings', 'prestataires', 'clients'));
    }

    public function updateCommissions(Request $request)
    {
        if (!TableExistenceCache::has('settings')) {
            return back()->with('error', 'La table settings n\'existe pas. Veuillez exécuter les migrations.');
        }

        $validated = $request->validate([
            // Legacy category-based commissions
            'default_commission' => 'nullable|numeric|min:0|max:100',
            'category_commissions' => 'array',

            // Commissions par type (prestataire)
            'commission_services' => 'nullable|numeric|min:0|max:100',
            'commission_rentals' => 'nullable|numeric|min:0|max:100',
            'commission_urgent_sales' => 'nullable|numeric|min:0|max:100',
            'commission_food' => 'nullable|numeric|min:0|max:100',

            // Commissions côté client
            'commission_client_services' => 'nullable|numeric|min:0|max:100',
            'commission_client_rentals' => 'nullable|numeric|min:0|max:100',
            'commission_client_urgent_sales' => 'nullable|numeric|min:0|max:100',
            'commission_client_food' => 'nullable|numeric|min:0|max:100',
        ]);

        // Save per-type commissions into settings table
        foreach ([
            'commission_services',
            'commission_rentals',
            'commission_urgent_sales',
            'commission_food',
            'commission_client_services',
            'commission_client_rentals',
            'commission_client_urgent_sales',
            'commission_client_food',
        ] as $key) {
            if (array_key_exists($key, $validated) && $validated[$key] !== null) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => (string) $validated[$key], 'updated_at' => now()]
                );
            }
        }

        // Legacy: default commission + per-category overrides
        if (array_key_exists('default_commission', $validated) && $validated['default_commission'] !== null) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'default_commission'],
                ['value' => (string) $validated['default_commission'], 'updated_at' => now()]
            );
        }

        if ($request->has('category_commissions') && TableExistenceCache::has('commission_settings')) {
            foreach ((array) $request->category_commissions as $categoryId => $commission) {
                if ($commission === null || $commission === '') {
                    continue;
                }
                DB::table('commission_settings')->updateOrInsert(
                    ['category_id' => $categoryId],
                    ['commission_rate' => $commission, 'updated_at' => now()]
                );
            }
        }

        Cache::forget('site_settings');
        Cache::forget('payment_settings');

        return back()->with('success', 'Commissions mises à jour avec succès.');
    }

    public function togglePrestataireCommission(Request $request, Prestataire $prestataire)
    {
        if (!TableExistenceCache::has('prestataires') || !Schema::hasColumn('prestataires', 'commission_prestataire_disabled')) {
            return back()->with('error', 'Colonne commission_prestataire_disabled manquante. Exécutez les migrations.');
        }

        $prestataire->commission_prestataire_disabled = !$prestataire->commission_prestataire_disabled;
        $prestataire->save();

        return back()->with('success', 'Statut de commission prestataire mis à jour.');
    }

    public function toggleClientCommission(Request $request, User $user)
    {
        if (!TableExistenceCache::has('users') || !Schema::hasColumn('users', 'commission_client_disabled')) {
            return back()->with('error', 'Colonne commission_client_disabled manquante. Exécutez les migrations.');
        }

        $user->commission_client_disabled = !$user->commission_client_disabled;
        $user->save();

        return back()->with('success', 'Statut de commission client mis à jour.');
    }

    /**
     * 3. Paramètres email/SMTP
     */
    public function email()
    {
        $emailSettings = [
            'mail_mailer' => config('mail.default'),
            'mail_host' => config('mail.mailers.smtp.host'),
            'mail_port' => config('mail.mailers.smtp.port'),
            'mail_username' => config('mail.mailers.smtp.username'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
        ];
        return view('admin.settings.email', compact('emailSettings'));
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'mail_host' => 'required|string',
            'mail_port' => 'required|numeric',
            'mail_username' => 'required|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        // Sauvegarder dans le fichier .env ou base de données
        $settings = $request->only(['mail_host', 'mail_port', 'mail_username', 'mail_from_address', 'mail_from_name']);
        foreach ($settings as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );
        }

        return back()->with('success', 'Paramètres email mis à jour.');
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            Mail::raw('Test email from admin panel', function ($message) use ($request) {
                $message->to($request->test_email)->subject('Test Email');
            });
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Email de test envoyé avec succès.']);
            }

            return back()->with('success', 'Email de test envoyé avec succès.');
        } catch (\Exception $e) {
            \Log::warning('Admin test email failed', ['error' => $e->getMessage()]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Envoi du mail de test impossible avec la configuration actuelle.'], 422);
            }

            return back()->with('error', 'Envoi du mail de test impossible avec la configuration actuelle.');
        }
    }

    /**
     * 4. Paramètres de paiement
     */
    public function payments()
    {
        $paymentSettings = function_exists('get_payment_settings')
            ? get_payment_settings()
            : $this->getSettings();

        $stripeConfig = [
            'publishable_key' => (string) config('services.stripe.key', ''),
            'publishable_key_masked' => $this->maskSensitiveValue((string) config('services.stripe.key', ''), 10, 6),
            'secret_key_configured' => filled((string) config('services.stripe.secret', '')),
            'secret_key_masked' => $this->maskSensitiveValue((string) config('services.stripe.secret', ''), 7, 4),
            'webhook_secret_configured' => filled((string) config('stripe.webhook_secret', '')),
            'webhook_secret_masked' => $this->maskSensitiveValue((string) config('stripe.webhook_secret', ''), 7, 4),
        ];
        
        // Statistiques de paiement
        $stats = [
            'total_collected' => 0,
            'this_month' => 0,
            'commissions' => 0,
            'pending_count' => 0,
        ];
        
        if (TableExistenceCache::has('payment_transactions')) {
            try {
                $stats['total_collected'] = DB::table('payment_transactions')
                    ->where('status', 'paid')
                    ->sum('amount');
                    
                $stats['this_month'] = DB::table('payment_transactions')
                    ->where('status', 'paid')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('amount');
                    
                $stats['pending_count'] = DB::table('payment_transactions')
                    ->where('status', 'pending')
                    ->count();
                    
                // Calcul des commissions (approximatif basé sur le taux par défaut)
                $commissionRate = floatval($paymentSettings['commission_services'] ?? 10) / 100;
                $stats['commissions'] = $stats['total_collected'] * $commissionRate;
            } catch (\Exception $e) {
                // Ignorer les erreurs
            }
        }
        
        return view('admin.settings.payments', compact('paymentSettings', 'stats', 'stripeConfig'));
    }

    public function updatePayments(Request $request)
    {
        if (!TableExistenceCache::has('settings')) {
            return back()->with('error', 'La table settings n\'existe pas.');
        }

        $validated = $request->validate([
            'stripe_enabled' => 'nullable|boolean',
            'stripe_apple_pay' => 'nullable|boolean',
            'stripe_google_pay' => 'nullable|boolean',
            'stripe_connect_enabled' => 'nullable|boolean',
            'stripe_platform_fee' => 'nullable|numeric|min:0|max:50',
            'stripe_payout_delay' => 'nullable|integer|min:0|max:30',
            'cash_payment_enabled' => 'nullable|boolean',
            'currency' => 'nullable|string|size:3',
            'min_payment_amount' => 'nullable|numeric|min:0',
            'min_withdrawal' => 'nullable|numeric|min:0',
            'require_payment_before_booking' => 'nullable|boolean',
            'allow_partial_payment' => 'nullable|boolean',
            'auto_refund_on_cancel' => 'nullable|boolean',
            'send_payment_receipts' => 'nullable|boolean',
            'default_deposit_percent' => 'nullable|integer|min:0|max:100',
            'min_deposit_amount' => 'nullable|numeric|min:0',
            'prestataire_can_set_deposit' => 'nullable|boolean',
            'default_security_deposit' => 'nullable|numeric|min:0',
            'deposit_refund_delay' => 'nullable|integer|min:0|max:30',
            'hold_deposit_instead_of_charge' => 'nullable|boolean',
            'commission_services' => 'nullable|numeric|min:0|max:100',
            'commission_rentals' => 'nullable|numeric|min:0|max:100',
            'commission_food' => 'nullable|numeric|min:0|max:100',
            'escrow_enabled' => 'nullable|boolean',
            'escrow_commission_rate' => 'nullable|numeric|min:0|max:100',
            'escrow_auto_release_hours' => 'nullable|integer|min:1|max:336',
            'escrow_min_amount' => 'nullable|numeric|min:0',
            'escrow_max_dispute_days' => 'nullable|integer|min:1|max:365',
        ]);

        foreach ($validated as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value ?? '', 'updated_at' => now()]
            );
        }

        DB::table('settings')
            ->whereIn('key', self::LEGACY_SECRET_SETTING_KEYS)
            ->delete();
        
        // Vider le cache des settings
        Cache::forget('site_settings');
        Cache::forget('payment_settings');

        return back()->with('success', 'Paramètres de paiement mis à jour avec succès.');
    }

    /**
     * 5. Paramètres SEO
     */
    public function seo()
    {
        $settings = $this->getSettings();
        return view('admin.settings.seo', compact('settings'));
    }

    public function updateSeo(Request $request)
    {
        if (!TableExistenceCache::has('settings')) {
            return back()->with('error', 'La table settings n\'existe pas.');
        }
        
        $settings = $request->only(['meta_title', 'meta_description', 'meta_keywords', 'google_analytics_id', 'google_site_verification']);

        foreach ($settings as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value ?? '', 'updated_at' => now()]
            );
        }

        return back()->with('success', 'Paramètres SEO mis à jour.');
    }

    /**
     * 6. Paramètres des notifications
     */
    public function notifications()
    {
        $notificationSettings = TableExistenceCache::has('notification_settings') 
            ? DB::table('notification_settings')->get() 
            : collect();
        return view('admin.settings.notifications', compact('notificationSettings'));
    }

    public function updateNotifications(Request $request)
    {
        $notifications = $request->input('notifications', []);

        foreach ($notifications as $type => $settings) {
            DB::table('notification_settings')->updateOrInsert(
                ['type' => $type],
                [
                    'email_enabled' => isset($settings['email']),
                    'sms_enabled' => isset($settings['sms']),
                    'push_enabled' => isset($settings['push']),
                    'updated_at' => now()
                ]
            );
        }

        return back()->with('success', 'Paramètres de notifications mis à jour.');
    }

    /**
     * 7. Gestion des catégories
     */
    public function categories()
    {
        $categories = Category::withCount(['services', 'prestataires'])->orderBy('name')->get();
        return view('admin.settings.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        Category::create($validated);
        return back()->with('success', 'Catégorie créée avec succès.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category->update($validated);
        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function deleteCategory(Category $category)
    {
        if ($category->services()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer: des services utilisent cette catégorie.');
        }
        $category->delete();
        return back()->with('success', 'Catégorie supprimée.');
    }

    /**
     * 8. Paramètres de localisation
     */
    public function localization()
    {
        $locales = ['fr' => 'Français', 'en' => 'English', 'ar' => 'العربية', 'es' => 'Español'];
        $timezones = timezone_identifiers_list();
        $currentLocale = config('app.locale');
        $currentTimezone = config('app.timezone');
        
        return view('admin.settings.localization', compact('locales', 'timezones', 'currentLocale', 'currentTimezone'));
    }

    public function updateLocalization(Request $request)
    {
        DB::table('settings')->updateOrInsert(['key' => 'locale'], ['value' => $request->locale]);
        DB::table('settings')->updateOrInsert(['key' => 'timezone'], ['value' => $request->timezone]);
        DB::table('settings')->updateOrInsert(['key' => 'date_format'], ['value' => $request->date_format]);
        DB::table('settings')->updateOrInsert(['key' => 'currency_symbol'], ['value' => $request->currency_symbol]);

        return back()->with('success', 'Paramètres de localisation mis à jour.');
    }

    /**
     * 9. Termes et conditions / Politique de confidentialité
     */
    public function legal()
    {
        $legal = DB::table('settings')
            ->whereIn('key', ['terms_conditions', 'privacy_policy', 'refund_policy', 'cookie_policy'])
            ->pluck('value', 'key');
        return view('admin.settings.legal', compact('legal'));
    }

    public function updateLegal(Request $request)
    {
        $pages = ['terms_conditions', 'privacy_policy', 'refund_policy', 'cookie_policy'];
        
        foreach ($pages as $page) {
            if ($request->has($page)) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $page],
                    ['value' => $request->$page, 'updated_at' => now()]
                );
            }
        }

        return back()->with('success', 'Pages légales mises à jour.');
    }

    /**
     * 10. Import/Export des paramètres
     */
    public function exportSettings()
    {
        $settings = DB::table('settings')->get();
        $filename = 'settings_backup_' . date('Y-m-d_H-i-s') . '.json';
        
        return response()->json($settings)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function importSettings(Request $request)
    {
        $request->validate(['settings_file' => 'required|file|mimes:json|max:1024']);

        $content = file_get_contents($request->file('settings_file')->path());
        $settings = json_decode($content, true);

        if (!is_array($settings)) {
            return back()->with('error', 'Format JSON invalide.');
        }

        // Clés sensibles qui ne doivent jamais être importées via fichier
        $forbiddenKeys = ['stripe_secret', 'stripe_webhook_secret', 'app_key', 'db_password', 'mail_password', 'twilio_auth_token'];

        $imported = 0;
        foreach ($settings as $setting) {
            // Valider la structure de chaque entrée
            if (!is_array($setting) || !isset($setting['key']) || !isset($setting['value'])) {
                continue;
            }

            // Vérifier que la clé est une chaîne alphanumérique valide
            if (!is_string($setting['key']) || !preg_match('/^[a-zA-Z0-9_\-.]+$/', $setting['key'])) {
                continue;
            }

            // Bloquer les clés sensibles
            if (in_array(strtolower($setting['key']), $forbiddenKeys, true)) {
                continue;
            }

            // Limiter la taille de la valeur
            $value = is_string($setting['value']) ? $setting['value'] : json_encode($setting['value']);
            if (strlen($value) > 10000) {
                continue;
            }

            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                ['value' => $value, 'updated_at' => now()]
            );
            $imported++;
        }

        Cache::forget('site_settings');
        return back()->with('success', "$imported paramètres importés avec succès.");
    }

    /**
     * 11. Paramètres d'abonnement prestataire
     */
    public function subscription()
    {
        $settings = [
            'subscription_enabled' => $this->getSetting('subscription_enabled', '0'),
            'subscription_price' => $this->getSetting('subscription_price', '29.99'),
            'subscription_currency' => $this->getSetting('subscription_currency', 'EUR'),
            'subscription_duration' => $this->getSetting('subscription_duration', '30'),
            'subscription_trial_days' => $this->getSetting('subscription_trial_days', '0'),
            'subscription_description' => $this->getSetting('subscription_description', 'Abonnement prestataire mensuel'),
        ];
        
        // Charger les plans d'abonnement
        $plans = collect();
        if (TableExistenceCache::has('subscription_plans')) {
            $plans = \App\Models\SubscriptionPlan::withCount('subscriptions')->get();
        }
        
        // Statistiques
        $stats = [
            'total_plans' => $plans->count(),
            'active_subscribers' => 0,
            'monthly_revenue' => 0,
            'expiring_soon' => 0,
        ];
        
        if (TableExistenceCache::has('user_subscriptions')) {
            $stats['active_subscribers'] = \App\Models\UserSubscription::where('status', 'active')->count();
            $stats['monthly_revenue'] = \App\Models\UserSubscription::where('status', 'active')
                ->whereNotNull('current_amount')
                ->sum('current_amount');
            $stats['expiring_soon'] = \App\Models\UserSubscription::where('status', 'active')
                ->whereNotNull('current_period_end')
                ->whereBetween('current_period_end', [now(), now()->addDays(7)])
                ->count();
        }
        
        return view('admin.settings.subscription', compact('settings', 'plans', 'stats'));
    }

    /**
     * Toggle subscription mode ON/OFF
     */
    public function toggleSubscription()
    {
        $current = $this->getSetting('subscription_enabled', '0');
        $newValue = $current === '1' ? '0' : '1';
        
        DB::table('settings')->updateOrInsert(
            ['key' => 'subscription_enabled'],
            ['value' => $newValue, 'updated_at' => now()]
        );
        
        // Aussi mettre à jour la feature
        DB::table('settings')->updateOrInsert(
            ['key' => 'feature_subscription_enabled'],
            ['value' => $newValue, 'updated_at' => now()]
        );
        
        Cache::forget('subscription_settings');
        Cache::forget('feature_subscription_enabled');
        
        $message = $newValue === '1' 
            ? 'Mode abonnement ACTIVÉ ! Les prestataires devront payer pour s\'inscrire.'
            : 'Mode abonnement DÉSACTIVÉ. L\'inscription est maintenant gratuite.';
        
        return back()->with('success', $message);
    }

    public function updateSubscription(Request $request)
    {
        $request->validate([
            'subscription_price' => 'required|numeric|min:0',
            'subscription_currency' => 'required|string|max:3',
            'subscription_duration' => 'required|integer|min:1',
            'subscription_trial_days' => 'required|integer|min:0',
        ]);

        $settings = [
            'subscription_price',
            'subscription_currency',
            'subscription_duration',
            'subscription_trial_days',
        ];

        foreach ($settings as $key) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $request->input($key), 'updated_at' => now()]
            );
        }

        Cache::forget('subscription_settings');
        return back()->with('success', 'Paramètres d\'abonnement mis à jour avec succès.');
    }

    /**
     * Helper pour récupérer un paramètre
     */
    private function getSetting($key, $default = null)
    {
        $setting = DB::table('settings')->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * 12. Gestion de la visibilité des fonctionnalités
     */
    public function features()
    {
        $features = function_exists('get_all_features') ? get_all_features() : [];
        return view('admin.settings.features', compact('features'));
    }

    public function updateFeatures(Request $request)
    {
        if (!TableExistenceCache::has('settings')) {
            return back()->with('error', 'La table settings n\'existe pas.');
        }

        // Liste des features possibles
        $featureKeys = [
            'feature_payments_enabled',
            'feature_stripe_enabled',
            'feature_subscription_enabled',
            'feature_subscription_button_visible',
            'feature_prestataire_stripe_connect',
            'feature_booking_payment_enabled',
            'feature_booking_deposit_enabled',
            'feature_food_payment_enabled',
            'feature_food_cash_enabled',
            'feature_cart_enabled',
            'feature_checkout_enabled',
            'feature_wallet_enabled',
            'feature_withdrawal_enabled',
        ];

        $updated = [];
        
        // Utiliser une transaction pour garantir l'intégrité
        DB::beginTransaction();
        try {
            foreach ($featureKeys as $key) {
                // Lire la valeur envoyée directement (hidden input envoie '0' ou '1')
                $rawValue = $request->input($key);
                $value = ($rawValue === '1' || $rawValue === 1 || $rawValue === true) ? '1' : '0';
                
                $updated[$key] = $value;
                
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'updated_at' => now()]
                );
            }
            
            DB::commit();
            \Log::info('Features updated successfully', ['updated' => $updated]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Features update failed: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la mise à jour des fonctionnalités.');
        }

        // Vider TOUS les caches possibles
        Cache::forget('site_settings');
        Cache::forget('site_settings_v2');
        Cache::flush(); // Vider tout le cache pour être sûr
        
        // Vider aussi via la fonction helper
        if (function_exists('clear_settings_cache')) {
            clear_settings_cache();
        }
        
        return back()->with('success', 'Visibilité des fonctionnalités mise à jour avec succès.');
    }

    private function maskSensitiveValue(?string $value, int $prefix = 6, int $suffix = 4): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return 'Non configure';
        }

        if (strlen($value) <= ($prefix + $suffix)) {
            return str_repeat('*', max(8, strlen($value)));
        }

        return substr($value, 0, $prefix)
            . str_repeat('*', max(8, strlen($value) - $prefix - $suffix))
            . substr($value, -$suffix);
    }
}
