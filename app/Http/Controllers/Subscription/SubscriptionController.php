<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

use App\Support\TableExistenceCache;
class SubscriptionController extends Controller
{
    private function stripeSecretKey(): ?string
    {
        $secret = trim((string) (config('stripe.secret') ?: config('services.stripe.secret')));

        return $secret !== '' ? $secret : null;
    }

    private function stripePublishableKey(): ?string
    {
        $key = trim((string) (config('stripe.key') ?: config('services.stripe.key')));

        return $key !== '' ? $key : null;
    }

    private function stripeIsConfigured(): bool
    {
        return $this->stripeSecretKey() !== null && $this->stripePublishableKey() !== null;
    }

    /**
     * Show subscription plans
     */
    public function showPlans()
    {
        // Vérifier si la table subscription_plans existe
        if (!TableExistenceCache::has('subscription_plans')) {
            return view('subscriptions.plans', [
                'plans' => collect(),
                'currentSubscription' => null,
                'tableNotExists' => true,
            ]);
        }

        try {
            $plans = SubscriptionPlan::where('is_active', true)->get();
            $userSubscription = auth()->user()->latestSubscription();

            return view('subscriptions.plans', [
                'plans' => $plans,
                'currentSubscription' => $userSubscription,
            ]);
        } catch (\Exception $e) {
            return view('subscriptions.plans', [
                'plans' => collect(),
                'currentSubscription' => null,
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe(Request $request, SubscriptionPlan $plan)
    {
        try {
            $user = auth()->user();

            if (!$plan->is_active) {
                return redirect()->back()->with('error', "Ce plan n'est pas disponible.");
            }

            if (!$this->stripeIsConfigured()) {
                return redirect()->back()->with('error', "Paiement Stripe indisponible. Contactez l'administrateur.");
            }

            if (empty($plan->stripe_price_id)) {
                return redirect()->back()->with('error', "Ce plan n'est pas encore configuré pour Stripe (stripe_price_id manquant).");
            }

            \Stripe\Stripe::setApiKey($this->stripeSecretKey());

            // Create or reuse Stripe customer
            $stripeCustomerId = $user->stripe_customer_id;
            if (!$stripeCustomerId) {
                $customer = \Stripe\Customer::create([
                    'email' => $user->email,
                    'name' => $user->name,
                    'metadata' => [
                        'user_id' => $user->id,
                        'role' => $user->role ?? null,
                    ],
                ]);
                $stripeCustomerId = $customer->id;
                $user->stripe_customer_id = $stripeCustomerId;
                $user->save();
            }

            $successUrl = route('prestataire.subscriptions.index', [], true) . '?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = route('prestataire.subscriptions.index', [], true) . '?checkout=cancel';

            $session = \Stripe\Checkout\Session::create([
                'mode' => 'subscription',
                'customer' => $stripeCustomerId,
                'client_reference_id' => (string) $user->id,
                'line_items' => [
                    [
                        'price' => $plan->stripe_price_id,
                        'quantity' => 1,
                    ],
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'user_id' => (string) $user->id,
                    'plan_id' => (string) $plan->id,
                ],
                'subscription_data' => [
                    'metadata' => [
                        'user_id' => (string) $user->id,
                        'plan_id' => (string) $plan->id,
                    ],
                ],
            ]);
            
            // Calculer une date de fin estimée (sera synchronisée via webhook)
            $endDate = match($plan->billing_cycle ?? 'monthly') {
                'weekly' => now()->addWeek(),
                'monthly' => now()->addMonth(),
                'quarterly' => now()->addMonths(3),
                'annual' => now()->addYear(),
                default => now()->addMonth(),
            };

            // Vérifier s'il existe déjà un abonnement pour ce plan
            $existingSubscription = UserSubscription::where('user_id', $user->id)
                ->where('subscription_plan_id', $plan->id)
                ->first();

            if ($existingSubscription) {
                $existingSubscription->update([
                    'stripe_subscription_id' => 'pending_' . $session->id,
                    'stripe_customer_id' => $stripeCustomerId,
                    'status' => 'paused',
                    'started_at' => now(),
                    'current_period_start' => now(),
                    'current_period_end' => $endDate,
                    'current_amount' => $plan->price,
                    'currency' => $plan->currency ?? 'EUR',
                    'auto_renew' => true,
                    'cancelled_at' => null,
                    'cancellation_reason' => null,
                    'metadata' => array_merge((array)($existingSubscription->metadata ?? []), [
                        'checkout_session_id' => $session->id,
                        'stripe_price_id' => $plan->stripe_price_id,
                        'state' => 'pending',
                    ]),
                ]);
            } else {
                UserSubscription::create([
                    'user_id' => $user->id,
                    'subscription_plan_id' => $plan->id,
                    'stripe_subscription_id' => 'pending_' . $session->id,
                    'stripe_customer_id' => $stripeCustomerId,
                    'status' => 'paused',
                    'started_at' => now(),
                    'current_period_start' => now(),
                    'current_period_end' => $endDate,
                    'current_amount' => $plan->price,
                    'currency' => $plan->currency ?? 'EUR',
                    'auto_renew' => true,
                    'metadata' => [
                        'checkout_session_id' => $session->id,
                        'stripe_price_id' => $plan->stripe_price_id,
                        'state' => 'pending',
                    ],
                ]);
            }

            return redirect()->away($session->url);

        } catch (\Exception $e) {
            Log::error('Subscription subscribe failed', [
                'user_id' => auth()->id(),
                'plan_id' => $plan->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Erreur lors de l\'abonnement. Réessayez dans quelques instants.');
        }
    }

    /**
     * Show current subscription
     */
    public function mySubscription()
    {
        $subscription = auth()->user()->latestSubscription();

        if (!$subscription) {
            return view('subscriptions.no-subscription');
        }

        return view('subscriptions.my-subscription', compact('subscription'));
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $subscription = auth()->user()->latestSubscription();

        if (!$subscription) {
            return redirect()->back()->with('error', 'Aucun abonnement actif trouvé.');
        }

        try {
            // Try to cancel in Stripe if it's a real Stripe subscription
            if ($subscription->stripe_subscription_id && str_starts_with($subscription->stripe_subscription_id, 'sub_')) {
                try {
                    \Stripe\Subscription::retrieve($subscription->stripe_subscription_id)->cancel();
                } catch (\Exception $e) {
                    // Log Stripe error but continue with local cancellation
                    Log::warning('Stripe cancellation failed: ' . $e->getMessage());
                }
            }

            // Update local record
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $validated['reason'] ?? 'Résilié par l\'utilisateur',
                'auto_renew' => false,
            ]);

            return redirect()->route('prestataire.subscriptions.index')
                ->with('success', 'Votre abonnement a été résilié avec succès. Vous pouvez vous réabonner à tout moment.');

        } catch (\Exception $e) {
            Log::error('Subscription cancel failed', [
                'user_id' => auth()->id(),
                'subscription_id' => $subscription->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Résiliation impossible pour le moment.');
        }
    }

    // ============================================================================
    // CLIENT METHODS
    // ============================================================================

    /**
     * Client subscriptions index
     */
    public function clientIndex()
    {
        $user = auth()->user();
        
        // Vérifier si les tables existent
        if (!TableExistenceCache::has('subscription_plans') || !TableExistenceCache::has('user_subscriptions')) {
            return view('client.subscriptions.index', [
                'currentSubscription' => null,
                'plans' => collect(),
                'history' => collect(),
                'tableNotExists' => true,
            ]);
        }

        try {
            $currentSubscription = $user->latestSubscription();
            $plans = \App\Models\SubscriptionPlan::where('is_active', true)->get();
            $history = \App\Models\UserSubscription::where('user_id', $user->id)
                ->with('plan')
                ->latest()
                ->get();

            return view('client.subscriptions.index', compact('currentSubscription', 'plans', 'history'));
        } catch (\Exception $e) {
            return view('client.subscriptions.index', [
                'currentSubscription' => null,
                'plans' => collect(),
                'history' => collect(),
                'tableNotExists' => true,
            ]);
        }
    }

    // ============================================================================
    // PRESTATAIRE METHODS
    // ============================================================================

    /**
     * Prestataire subscriptions index
     */
    public function prestataireIndex()
    {
        // Sync after Stripe Checkout return (optional)
        if (request()->filled('session_id') && $this->stripeSecretKey() !== null) {
            $this->syncFromCheckoutSession(request()->string('session_id')->toString(), auth()->user());
        }

        $user = auth()->user();
        
        // Vérifier si les tables existent
        if (!TableExistenceCache::has('subscription_plans') || !TableExistenceCache::has('user_subscriptions')) {
            return view('prestataire.subscriptions.index', [
                'currentSubscription' => null,
                'plans' => collect(),
                'history' => collect(),
                'benefits' => ['visibility_boost' => 1, 'featured_services' => 0, 'priority_support' => false],
                'subscriptionEnabled' => false,
                'tableNotExists' => true,
            ]);
        }

        try {
            $currentSubscription = $user->latestSubscription();
            
            // Récupérer tous les plans actifs (sans filtre par type)
            $plans = \App\Models\SubscriptionPlan::where('is_active', true)
                ->orderBy('price')
                ->get();
            
            $history = \App\Models\UserSubscription::where('user_id', $user->id)
                ->with('plan')
                ->latest()
                ->get();

            $benefits = [
                'visibility_boost' => $currentSubscription?->plan?->visibility_boost ?? 1,
                'featured_services' => $currentSubscription?->plan?->featured_services ?? 0,
                'priority_support' => $currentSubscription?->plan?->priority_support ?? false,
            ];

            // Vérifier si le système d'abonnement est activé
            $subscriptionEnabled = subscription_enabled();

            return view('prestataire.subscriptions.index', compact('currentSubscription', 'plans', 'history', 'benefits', 'subscriptionEnabled'));
        } catch (\Exception $e) {
            return view('prestataire.subscriptions.index', [
                'currentSubscription' => null,
                'plans' => collect(),
                'history' => collect(),
                'benefits' => ['visibility_boost' => 1, 'featured_services' => 0, 'priority_support' => false],
                'subscriptionEnabled' => false,
                'tableNotExists' => true,
            ]);
        }
    }

    // ============================================================================
    // ADMIN METHODS
    // ============================================================================

    /**
     * Admin subscribers list
     */
    public function adminSubscribers()
    {
        // Variables par défaut
        $defaultData = [
            'subscriptions' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 30, 1, ['path' => request()->url()]),
            'monthlyRevenue' => 0,
            'activeSubscribers' => 0,
            'cancelledThisMonth' => 0,
            'activePlans' => 0,
            'conversionRate' => 0,
            'plans' => collect(),
            'tableNotExists' => true,
        ];

        // Vérifier si les tables existent
        if (!TableExistenceCache::has('user_subscriptions')) {
            return view('admin.subscriptions.index', $defaultData);
        }

        try {
            $subscriptions = \App\Models\UserSubscription::with(['user', 'plan'])
                ->latest()
                ->paginate(30);

            $activeSubscribers = \App\Models\UserSubscription::where('status', 'active')->count();
            
            $monthlyRevenue = \App\Models\UserSubscription::where('status', 'active')
                ->whereNotNull('current_amount')
                ->sum('current_amount');
            
            $cancelledThisMonth = \App\Models\UserSubscription::where('status', 'cancelled')
                ->whereMonth('updated_at', now()->month)->count();
            
            // Plans actifs
            $plans = TableExistenceCache::has('subscription_plans') 
                ? \App\Models\SubscriptionPlan::all() 
                : collect();
            
            $activePlans = TableExistenceCache::has('subscription_plans') 
                ? \App\Models\SubscriptionPlan::where('is_active', true)->count()
                : 0;
            
            // Taux de conversion (utilisateurs avec abonnement vs total utilisateurs)
            $totalUsers = \App\Models\User::where('user_type', 'prestataire')->count();
            $conversionRate = $totalUsers > 0 ? round(($activeSubscribers / $totalUsers) * 100, 1) : 0;

            return view('admin.subscriptions.index', [
                'subscriptions' => $subscriptions,
                'monthlyRevenue' => $monthlyRevenue,
                'activeSubscribers' => $activeSubscribers,
                'cancelledThisMonth' => $cancelledThisMonth,
                'activePlans' => $activePlans,
                'conversionRate' => $conversionRate,
                'plans' => $plans,
                'tableNotExists' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Admin subscriptions error: ' . $e->getMessage());
            return view('admin.subscriptions.index', $defaultData);
        }
    }

    /**
     * Admin plans management
     */
    public function adminPlans()
    {
        // Vérifier si la table existe
        if (!TableExistenceCache::has('subscription_plans')) {
            return view('admin.subscriptions.plans', [
                'plans' => collect(),
                'tableNotExists' => true,
            ]);
        }

        try {
            $plans = \App\Models\SubscriptionPlan::all();
            return view('admin.subscriptions.plans', compact('plans'));
        } catch (\Exception $e) {
            return view('admin.subscriptions.plans', [
                'plans' => collect(),
                'tableNotExists' => true,
            ]);
        }
    }

    /**
     * Store new plan
     */
    public function adminStorePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:weekly,monthly,quarterly,annual',
            'features_text' => 'nullable|string',
            'stripe_price_id' => 'nullable|string|max:255',
            'is_active' => 'nullable',
            'is_featured' => 'nullable',
        ]);

        // Parse features from textarea (one per line)
        $features = [];
        if (!empty($validated['features_text'])) {
            $features = array_filter(array_map('trim', explode("\n", $validated['features_text'])));
        }

        $plan = \App\Models\SubscriptionPlan::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'currency' => 'EUR',
            'billing_cycle' => $validated['billing_cycle'],
            'stripe_price_id' => $validated['stripe_price_id'] ?? null,
            'features' => $features,
            'is_active' => true,
            'is_featured' => isset($validated['is_featured']),
        ]);

        return redirect()->route('admin.settings.subscription')->with('success', 'Plan "' . $plan->name . '" créé avec succès !');
    }

    /**
     * Update plan
     */
    public function adminUpdatePlan(Request $request, \App\Models\SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'string|max:100',
            'description' => 'nullable|string',
            'price' => 'numeric|min:0',
            'billing_cycle' => 'nullable|in:weekly,monthly,quarterly,annual',
            'features_text' => 'nullable|string',
            'stripe_price_id' => 'nullable|string|max:255',
            'is_active' => 'nullable',
            'is_featured' => 'nullable',
        ]);

        // Parse features from textarea
        $features = [];
        if (!empty($validated['features_text'])) {
            $features = array_filter(array_map('trim', explode("\n", $validated['features_text'])));
        }

        $plan->update([
            'name' => $validated['name'] ?? $plan->name,
            'description' => $validated['description'] ?? $plan->description,
            'price' => $validated['price'] ?? $plan->price,
            'billing_cycle' => $validated['billing_cycle'] ?? $plan->billing_cycle,
            'stripe_price_id' => $validated['stripe_price_id'] ?? $plan->stripe_price_id,
            'features' => !empty($features) ? $features : $plan->features,
            'is_active' => isset($validated['is_active']),
            'is_featured' => isset($validated['is_featured']),
        ]);

        return redirect()->route('admin.settings.subscription')->with('success', 'Plan mis à jour avec succès');
    }

    /**
     * Delete plan
     */
    public function adminDestroyPlan(\App\Models\SubscriptionPlan $plan)
    {
        $planName = $plan->name;
        $plan->delete();
        return redirect()->route('admin.settings.subscription')->with('success', 'Plan "' . $planName . '" supprimé avec succès');
    }

    /**
     * Admin: Activer manuellement un abonnement
     */
    public function adminActivate(UserSubscription $subscription)
    {
        $subscription->update([
            'status' => 'active',
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);

        // Notifier l'utilisateur
        if ($subscription->user) {
            $subscription->user->notify(new \App\Notifications\SubscriptionActivated($subscription));
        }

        return back()->with('success', 'Abonnement activé avec succès.');
    }

    /**
     * Admin: Désactiver manuellement un abonnement
     */
    public function adminDeactivate(Request $request, UserSubscription $subscription)
    {
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->input('reason', 'Désactivé par l\'administrateur'),
            'auto_renew' => false,
        ]);

        return back()->with('success', 'Abonnement désactivé.');
    }

    /**
     * Admin: Prolonger un abonnement
     */
    public function adminExtend(Request $request, UserSubscription $subscription)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $currentEnd = $subscription->current_period_end ?? now();
        $newEnd = $currentEnd->addDays($validated['days']);

        $subscription->update([
            'current_period_end' => $newEnd,
            'status' => 'active', // Réactiver si expiré
        ]);

        return back()->with('success', "Abonnement prolongé de {$validated['days']} jours (jusqu'au {$newEnd->format('d/m/Y')}).");
    }

    /**
     * Admin: Créer un abonnement pour un utilisateur
     */
    public function adminCreateForUser(Request $request, \App\Models\User $user)
    {
        $validated = $request->validate([
            'duration_days' => 'required|integer|min:1',
            'amount' => 'nullable|numeric|min:0',
        ]);

        // Vérifier si l'utilisateur a déjà un abonnement actif
        $existingActive = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($existingActive) {
            return back()->with('error', 'Cet utilisateur a déjà un abonnement actif.');
        }

        $subscriptionSettings = function_exists('get_subscription_settings') 
            ? get_subscription_settings() 
            : ['price' => 29.99, 'currency' => 'EUR'];

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => null,
            'stripe_subscription_id' => 'admin_manual_' . uniqid(),
            'stripe_customer_id' => $user->stripe_customer_id ?? 'admin_' . $user->id,
            'status' => 'active',
            'started_at' => now(),
            'current_period_start' => now(),
            'current_period_end' => now()->addDays($validated['duration_days']),
            'current_amount' => $validated['amount'] ?? $subscriptionSettings['price'],
            'currency' => $subscriptionSettings['currency'] ?? 'EUR',
            'auto_renew' => false,
            'metadata' => [
                'created_by' => 'admin',
                'admin_id' => auth()->id(),
            ],
        ]);

        // Notifier l'utilisateur
        $user->notify(new \App\Notifications\SubscriptionActivated($subscription));

        return back()->with('success', "Abonnement créé pour {$user->name} ({$validated['duration_days']} jours).");
    }

    // ============================================================================
    // PAYMENT PAGE FOR NEW PRESTATAIRES
    // ============================================================================

    /**
     * Show the subscription payment page for new prestataires
     */
    public function showPaymentPage(Request $request)
    {
        $user = auth()->user();
        
        // Vérifier que c'est bien un prestataire
        if ($user->role !== 'prestataire') {
            return redirect()->route('home');
        }
        
        // Vérifier si déjà abonné
        if ($user->latestSubscription()?->isActive()) {
            return redirect()->route('prestataire.dashboard')
                ->with('info', 'Vous avez déjà un abonnement actif.');
        }
        
        // Récupérer les paramètres d'abonnement
        $subscriptionSettings = function_exists('get_subscription_settings') 
            ? get_subscription_settings() 
            : [
                'enabled' => false,
                'price' => 29.99,
                'currency' => 'EUR',
                'duration' => 30,
                'trial_days' => 0,
                'description' => 'Abonnement prestataire',
            ];
        
        // Si le mode abonnement n'est pas activé, rediriger vers le dashboard
        if (!$subscriptionSettings['enabled']) {
            return redirect()->route('prestataire.dashboard')
                ->with('success', 'Bienvenue ! Votre compte est prêt.');
        }

        // Sync after Stripe Checkout return (optional)
        $stripeEnabled = $this->stripeIsConfigured();
        if ($stripeEnabled && $request->filled('session_id')) {
            $this->syncFromCheckoutSession($request->string('session_id')->toString(), $user);

            if ($user->latestSubscription()?->isActive()) {
                $prestataire = $user->prestataire;
                if ($prestataire && empty($prestataire->company_name)) {
                    return redirect()->route('prestataire.profile.edit')
                        ->with('success', 'Abonnement activé ! Complétez votre profil (nom de l\'enseigne) pour accéder au tableau de bord.');
                }

                return redirect()->route('prestataire.dashboard')
                    ->with('success', 'Félicitations ! Votre abonnement est maintenant actif.');
            }
        }
        
        $stripeKey = $this->stripePublishableKey();

        return view('prestataire.subscription-payment', compact(
            'subscriptionSettings',
            'stripeKey',
            'stripeEnabled'
        ));
    }

    /**
     * Process the subscription payment
     */
    public function processPayment(Request $request)
    {
        $user = auth()->user();
        
        // Vérifier que c'est bien un prestataire
        if ($user->role !== 'prestataire') {
            return redirect()->route('home');
        }
        
        $subscriptionSettings = function_exists('get_subscription_settings') 
            ? get_subscription_settings() 
            : ['enabled' => false, 'price' => 29.99, 'duration' => 30];
        
        try {
            $stripeEnabled = $this->stripeIsConfigured();

            // Récupérer le plan par défaut (le premier actif, ou le featured)
            $defaultPlanQuery = \App\Models\SubscriptionPlan::where('is_active', 1)
                ->orderBy('is_featured', 'desc');
            if (Schema::hasColumn('subscription_plans', 'sort_order')) {
                $defaultPlanQuery->orderBy('sort_order', 'asc');
            } else {
                $defaultPlanQuery->orderBy('price', 'asc');
            }
            $defaultPlan = $defaultPlanQuery->first();

            if (!$defaultPlan) {
                return redirect()->back()->with('error', 'Aucun plan actif disponible.');
            }

            if ($stripeEnabled) {
                if (empty($defaultPlan->stripe_price_id)) {
                    return redirect()->back()->with('error', "Le plan par défaut n'est pas configuré pour Stripe (stripe_price_id manquant).");
                }

                \Stripe\Stripe::setApiKey($this->stripeSecretKey());

                $stripeCustomerId = $user->stripe_customer_id;
                if (!$stripeCustomerId) {
                    $customer = \Stripe\Customer::create([
                        'email' => $user->email,
                        'name' => $user->name,
                        'metadata' => [
                            'user_id' => $user->id,
                            'type' => 'subscription',
                        ],
                    ]);
                    $stripeCustomerId = $customer->id;
                    $user->stripe_customer_id = $stripeCustomerId;
                    $user->save();
                }

                $successUrl = route('prestataire.subscription.payment', [], true) . '?session_id={CHECKOUT_SESSION_ID}';
                $cancelUrl = route('prestataire.subscription.payment', [], true);

                $session = \Stripe\Checkout\Session::create([
                    'mode' => 'subscription',
                    'customer' => $stripeCustomerId,
                    'client_reference_id' => (string) $user->id,
                    'line_items' => [
                        [
                            'price' => $defaultPlan->stripe_price_id,
                            'quantity' => 1,
                        ],
                    ],
                    'success_url' => $successUrl,
                    'cancel_url' => $cancelUrl,
                    'metadata' => [
                        'user_id' => (string) $user->id,
                        'plan_id' => (string) $defaultPlan->id,
                        'context' => 'prestataire_registration',
                    ],
                    'subscription_data' => [
                        'metadata' => [
                            'user_id' => (string) $user->id,
                            'plan_id' => (string) $defaultPlan->id,
                            'context' => 'prestataire_registration',
                        ],
                    ],
                ]);

                // Create/update a pending local record
                $pendingEnd = match($defaultPlan->billing_cycle ?? 'monthly') {
                    'weekly' => now()->addWeek(),
                    'monthly' => now()->addMonth(),
                    'quarterly' => now()->addMonths(3),
                    'annual' => now()->addYear(),
                    default => now()->addMonth(),
                };

                UserSubscription::updateOrCreate([
                    'user_id' => $user->id,
                    'subscription_plan_id' => $defaultPlan->id,
                ], [
                    'stripe_subscription_id' => 'pending_' . $session->id,
                    'stripe_customer_id' => $stripeCustomerId,
                    'status' => 'paused',
                    'started_at' => now(),
                    'current_period_start' => now(),
                    'current_period_end' => $pendingEnd,
                    'current_amount' => $defaultPlan->price,
                    'currency' => $defaultPlan->currency ?? 'EUR',
                    'auto_renew' => true,
                    'metadata' => [
                        'checkout_session_id' => $session->id,
                        'stripe_price_id' => $defaultPlan->stripe_price_id,
                        'state' => 'pending',
                        'context' => 'prestataire_registration',
                    ],
                ]);

                return redirect()->away($session->url);
            }

            // Mode manuel : nécessite validation admin, pas d'activation automatique
            if (!($subscriptionSettings['allow_manual_activation'] ?? false)) {
                return redirect()->back()
                    ->with('error', 'Le paiement en ligne n\'est pas configuré. Veuillez contacter l\'administrateur.');
            }

            $subscription = UserSubscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $defaultPlan->id,
                'stripe_subscription_id' => 'manual_' . uniqid(),
                'stripe_customer_id' => $user->stripe_customer_id ?? 'manual_' . $user->id,
                'status' => 'active',
                'started_at' => now(),
                'current_period_start' => now(),
                'current_period_end' => now()->addDays($subscriptionSettings['duration'] ?? 30),
                'current_amount' => $subscriptionSettings['price'],
                'currency' => $subscriptionSettings['currency'] ?? 'EUR',
                'auto_renew' => false,
                'metadata' => [
                    'payment_method' => 'manual',
                ],
            ]);

            $user->notify(new \App\Notifications\SubscriptionActivated($subscription));

            $prestataire = $user->prestataire;
            if ($prestataire && empty($prestataire->company_name)) {
                return redirect()->route('prestataire.profile.edit')
                    ->with('success', 'Abonnement activé ! Complétez votre profil (nom de l\'enseigne) pour accéder au tableau de bord.');
            }

            return redirect()->route('prestataire.dashboard')
                ->with('success', 'Félicitations ! Votre abonnement est maintenant actif. Bienvenue sur notre plateforme !');

        } catch (\Exception $e) {
            Log::error('Subscription payment error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Activation de l\'abonnement impossible pour le moment.');
        }
    }

    private function syncFromCheckoutSession(string $sessionId, User $user): void
    {
        try {
            if ($this->stripeSecretKey() === null) {
                return;
            }

            \Stripe\Stripe::setApiKey($this->stripeSecretKey());
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            // Basic safety: ensure session is for this user
            if (!empty($session->client_reference_id) && (string)$session->client_reference_id !== (string)$user->id) {
                return;
            }

            if (empty($session->subscription)) {
                return;
            }

            $stripeSubscription = \Stripe\Subscription::retrieve($session->subscription);

            // Map plan
            $planId = $stripeSubscription->metadata->plan_id ?? ($session->metadata->plan_id ?? null);
            $plan = $planId ? SubscriptionPlan::find($planId) : null;
            if (!$plan) {
                $priceId = $stripeSubscription->items->data[0]->price->id ?? null;
                if ($priceId) {
                    $plan = SubscriptionPlan::where('stripe_price_id', $priceId)->first();
                }
            }
            if (!$plan) {
                return;
            }

            $stripeStatus = $stripeSubscription->status ?? null;
            $localStatus = match ($stripeStatus) {
                'active', 'trialing' => 'active',
                'canceled' => 'cancelled',
                default => 'paused',
            };

            $periodStart = !empty($stripeSubscription->current_period_start)
                ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start)
                : now();
            $periodEnd = !empty($stripeSubscription->current_period_end)
                ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end)
                : null;

            $cancelAtPeriodEnd = (bool)($stripeSubscription->cancel_at_period_end ?? false);
            $endsAt = $cancelAtPeriodEnd && $periodEnd ? $periodEnd : null;

            $unitAmount = $stripeSubscription->items->data[0]->price->unit_amount ?? null;
            $currency = $stripeSubscription->items->data[0]->price->currency ?? null;
            $amount = $unitAmount !== null ? ($unitAmount / 100) : $plan->price;

            $existing = UserSubscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
            if (!$existing) {
                $existing = UserSubscription::where('user_id', $user->id)
                    ->where('subscription_plan_id', $plan->id)
                    ->where('stripe_subscription_id', 'pending_' . $sessionId)
                    ->first();
            }
            if (!$existing) {
                $existing = UserSubscription::where('user_id', $user->id)
                    ->where('subscription_plan_id', $plan->id)
                    ->first();
            }

            $payload = [
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'stripe_subscription_id' => $stripeSubscription->id,
                'stripe_customer_id' => (string)($stripeSubscription->customer ?? $user->stripe_customer_id),
                'status' => $localStatus,
                'started_at' => $periodStart,
                'current_period_start' => $periodStart,
                'current_period_end' => $periodEnd,
                'ends_at' => $endsAt,
                'current_amount' => $amount,
                'currency' => strtoupper($currency ?? ($plan->currency ?? 'EUR')),
                'auto_renew' => !$cancelAtPeriodEnd,
                'metadata' => array_merge((array)($existing?->metadata ?? []), [
                    'checkout_session_id' => $sessionId,
                    'stripe_status' => $stripeStatus,
                    'state' => 'synced',
                ]),
            ];

            if ($existing) {
                $existing->update($payload);
            } else {
                UserSubscription::create($payload);
            }
        } catch (\Throwable $e) {
            Log::warning('Stripe checkout sync failed: ' . $e->getMessage());
        }
    }
}
