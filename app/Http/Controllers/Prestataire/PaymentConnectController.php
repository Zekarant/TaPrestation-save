<?php

namespace App\Http\Controllers\Prestataire;

use App\Http\Controllers\Controller;
use App\Services\CommissionService;
use App\Services\EquipmentRentalPaymentSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentConnectController extends Controller
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

    private function isMissingOrRevokedStripeAccount(\Throwable $e): bool
    {
        $message = strtolower((string) $e->getMessage());

        return str_contains($message, 'does not exist')
            || str_contains($message, 'access may have been revoked')
            || str_contains($message, 'no such account');
    }

    /**
     * Affiche la page de connexion des comptes de paiement
     */
    public function index()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (function_exists('prestataire_stripe_connect_enabled') && !prestataire_stripe_connect_enabled()) {
            return redirect()->route('prestataire.dashboard')
                ->with('info', 'La connexion Stripe prestataire est désactivée pour le moment.');
        }

        if ($prestataire) {
            try {
                app(EquipmentRentalPaymentSyncService::class)->syncForPrestataire($prestataire);
            } catch (\Throwable $e) {
                \Log::warning('Payment connect index rental payment sync warning', [
                    'prestataire_id' => $prestataire->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Récupérer le taux de commission depuis CommissionService (dynamique par type)
        $commissionRate = CommissionService::ratePercent('service', 'prestataire');

        // Variables par défaut
        $paymentsEnabled = false;
        $onboardingPending = false;
        $requiresMoreInfo = false;
        $totalEarnings = 0;
        $pendingBalance = 0;
        $stripeAccountId = null;
        $onboardingUrl = null;
        $error = null;

        // Récupérer la clé secrète Stripe (priorité: .env via config)
        $stripeSecretKey = $this->stripeSecretKey();
        $stripePublishableKey = $this->stripePublishableKey();

        // Vérifier que les clés Stripe sont configurées
        if (empty($stripeSecretKey)) {
            \Log::error('Stripe: Clé secrète non configurée');
            return view('prestataire.payments.connect', compact(
                'prestataire',
                'paymentsEnabled',
                'onboardingPending',
                'requiresMoreInfo',
                'totalEarnings',
                'pendingBalance',
                'commissionRate',
                'onboardingUrl',
                'stripePublishableKey'
            ))->with('error', 'Les paiements ne sont pas configurés. Contactez l\'administrateur.');
        }

        try {
            \Stripe\Stripe::setApiKey($stripeSecretKey);

            // Créer le compte Connect si pas encore fait
            if (empty($prestataire->stripe_account_id)) {

                $existingStripeAccountId = null;

                // ETAPE 1: Vérifier d'abord si un compte Stripe archivé existe dans notre table
                $archivedAccount = \App\Models\DeletedStripeAccount::findByEmail($user->email);

                if ($archivedAccount && !empty($archivedAccount->stripe_account_id)) {
                    \Log::info('Stripe: Compte archivé trouvé dans DB pour user#' . $user->id);
                    $existingStripeAccountId = $archivedAccount->stripe_account_id;
                }

                // SÉCURITÉ (audit 2.15): Supprimé le matching automatique par email sur Stripe.
                // Un attaquant pouvait changer son email pour matcher le compte Stripe d'un autre prestataire.
                // Seule la table DeletedStripeAccount (liée par user_id) est fiable.

                // ETAPE 3: Si on a trouvé un compte existant, essayer de le réutiliser
                if ($existingStripeAccountId) {
                    try {
                        $existingAccount = \Stripe\Account::retrieve($existingStripeAccountId);

                        // Vérifier que le compte n'est pas supprimé
                        if (empty($existingAccount->deleted)) {
                            // Réutiliser le compte existant
                            $prestataire->stripe_account_id = $existingStripeAccountId;
                            $prestataire->save();

                            // Mettre à jour les metadata du compte Stripe
                            try {
                                \Stripe\Account::update($existingStripeAccountId, [
                                    'metadata' => [
                                        'prestataire_id' => $prestataire->id,
                                        'user_id' => $user->id,
                                        'platform' => 'taprestation.com',
                                        'restored' => 'true',
                                        'restored_at' => now()->toISOString(),
                                    ],
                                ]);
                            } catch (\Exception $updateError) {
                                \Log::warning('Stripe: Impossible de mettre à jour les metadata: ' . $updateError->getMessage());
                            }

                            \Log::info('Stripe: Compte restauré pour prestataire ' . $prestataire->id . ' => ' . $existingStripeAccountId);

                            // Supprimer l'entrée d'archive si elle existe
                            if ($archivedAccount) {
                                $archivedAccount->delete();
                            }

                            // Ne pas créer de nouveau compte, continuer avec celui restauré
                            $stripeAccountId = $prestataire->stripe_account_id;
                            $account = $existingAccount;

                            // Passer directement à la vérification du statut
                            goto check_account_status;
                        }
                    } catch (\Exception $retrieveError) {
                        \Log::warning('Stripe: Compte existant invalide/supprimé: ' . $retrieveError->getMessage());
                    }
                }

                // Préparer les infos pré-remplies pour simplifier l'onboarding
                $accountData = [
                    'type' => 'express',
                    'country' => 'FR',
                    'email' => $user->email,
                    'capabilities' => [
                        'card_payments' => ['requested' => true],
                        'transfers' => ['requested' => true],
                        'link_payments' => ['requested' => true], // Stripe Link (paiement rapide)
                    ],
                    'business_type' => 'individual',
                    'business_profile' => [
                        'name' => 'TaPrestation - ' . ($prestataire->business_name ?? $user->name),
                        'mcc' => '7299',
                        'product_description' => $prestataire->description ?? 'Services de prestation',
                        'url' => route('prestataires.show', $prestataire->slug ?? $prestataire->id),
                    ],
                    'individual' => [
                        'first_name' => $user->first_name ?? explode(' ', $user->name)[0] ?? '',
                        'last_name' => $user->last_name ?? (explode(' ', $user->name)[1] ?? ''),
                        'email' => $user->email,
                    ],
                    'settings' => [
                        'payouts' => [
                            'schedule' => [
                                'interval' => 'daily',
                            ],
                        ],
                    ],
                    'metadata' => [
                        'prestataire_id' => $prestataire->id,
                        'user_id' => $user->id,
                        'platform' => 'taprestation.com',
                    ],
                ];

                // Ajouter le téléphone si disponible
                if (!empty($user->phone) || !empty($prestataire->phone)) {
                    $phone = $prestataire->phone ?? $user->phone;
                    // Formater le téléphone pour Stripe (format international)
                    $phone = preg_replace('/[^0-9+]/', '', $phone);
                    if (strlen($phone) === 10 && $phone[0] === '0') {
                        $phone = '+33' . substr($phone, 1);
                    }
                    $accountData['individual']['phone'] = $phone;
                }

                // Ajouter l'adresse si disponible
                if (!empty($prestataire->city)) {
                    $accountData['individual']['address'] = [
                        'city' => $prestataire->city,
                        'country' => 'FR',
                    ];
                    if (!empty($prestataire->address)) {
                        $accountData['individual']['address']['line1'] = $prestataire->address;
                    }
                    if (!empty($prestataire->postal_code)) {
                        $accountData['individual']['address']['postal_code'] = $prestataire->postal_code;
                    }
                }

                $account = \Stripe\Account::create($accountData);

                $prestataire->stripe_account_id = $account->id;
                $prestataire->save();

                \Log::info('Stripe: Compte créé pour prestataire ' . $prestataire->id . ' => ' . $account->id);
            }

            $stripeAccountId = $prestataire->stripe_account_id;

            // Label pour le goto (utilisé quand on restaure un compte archivé)
            check_account_status:

            // Vérifier le statut du compte
            if (!isset($account)) {
                $account = \Stripe\Account::retrieve($stripeAccountId);
            }

            // Compte supprimé/invalide ?
            if (!empty($account->deleted)) {
                $prestataire->stripe_account_id = null;
                $prestataire->save();
                return redirect()->route('prestataire.payments.connect');
            }

            // Vérifier si les paiements sont activés
            if ($account->charges_enabled && $account->payouts_enabled) {
                $paymentsEnabled = true;

                // SYNCHRONISER le flag stripe_onboarding_completed si nécessaire
                if (!$prestataire->stripe_onboarding_completed) {
                    $prestataire->stripe_onboarding_completed = true;
                    $prestataire->save();
                    \Log::info('Stripe: Flag onboarding_completed synchronisé pour prestataire ' . $prestataire->id);
                }

                // MISE À JOUR AUTOMATIQUE des capabilities pour Apple Pay, Google Pay, Link
                // Cela se fait automatiquement quand le prestataire visite la page
                $capabilities = $account->capabilities ?? [];
                if (
                    ($capabilities['link_payments'] ?? null) !== 'active' &&
                    ($capabilities['link_payments'] ?? null) !== 'pending'
                ) {
                    try {
                        \Stripe\Account::update($stripeAccountId, [
                            'capabilities' => [
                                'link_payments' => ['requested' => true],
                            ],
                        ]);
                        \Log::info('Stripe: link_payments capability demandée automatiquement pour prestataire ' . $prestataire->id);
                    } catch (\Exception $capError) {
                        \Log::warning('Stripe: Impossible de demander link_payments: ' . $capError->getMessage());
                    }
                }

                // Récupérer le solde
                try {
                    $balance = \Stripe\Balance::retrieve(['stripe_account' => $stripeAccountId]);
                    $pendingBalance = collect($balance->pending)->sum('amount') / 100;
                    $totalEarnings = collect($balance->available)->sum('amount') / 100;
                } catch (\Exception $e) {
                    \Log::warning('Stripe Balance Error: ' . $e->getMessage());
                }
            } else {
                $onboardingPending = true;
                $requiresMoreInfo = !empty($account->requirements->currently_due) && count($account->requirements->currently_due) > 0;
            }

            // Créer le lien d'onboarding si pas encore activé
            if (!$paymentsEnabled) {
                try {
                    $accountLink = \Stripe\AccountLink::create([
                        'account' => $stripeAccountId,
                        'refresh_url' => route('prestataire.payments.connect'),
                        'return_url' => route('prestataire.payments.stripe.callback'),
                        'type' => 'account_onboarding',
                    ]);
                    $onboardingUrl = $accountLink->url;
                    \Log::info('Stripe: Lien onboarding créé pour ' . $stripeAccountId);
                } catch (\Exception $linkError) {
                    \Log::error('Stripe AccountLink Error: ' . $linkError->getMessage());
                    $error = 'Impossible de générer le lien de finalisation Stripe pour le moment.';
                }
            }

        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Compte invalide - réinitialiser
            \Log::error('Stripe InvalidRequest FULL: ' . $e->getMessage() . ' | Code: ' . $e->getStripeCode() . ' | Param: ' . $e->getStripeParam());
            if (!empty($prestataire->stripe_account_id)) {
                $prestataire->stripe_account_id = null;
                $prestataire->save();
                return redirect()->route('prestataire.payments.connect')
                    ->with('error', 'Le compte Stripe enregistré a été réinitialisé. Recommencez la connexion.');
            }
            $error = 'Connexion Stripe temporairement indisponible.';
        } catch (\Stripe\Exception\AuthenticationException $e) {
            \Log::error('Stripe Auth Error: ' . $e->getMessage());
            $error = 'Erreur de configuration Stripe. Contactez l\'administrateur.';
        } catch (\Exception $e) {
            \Log::error('Stripe Error FULL: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ':' . $e->getLine());

            // Détecter si le compte n'existe plus ou accès révoqué
            if ($this->isMissingOrRevokedStripeAccount($e)) {
                if (!empty($prestataire->stripe_account_id)) {
                    $prestataire->stripe_account_id = null;
                    $prestataire->save();
                    return redirect()->route('prestataire.payments.connect')
                        ->with('error', 'Le compte Stripe lié n\'est plus valide. Une reconnexion est nécessaire.');
                }
            }

            $error = 'Connexion Stripe temporairement indisponible.';
        }

        // Calculer les escrows en attente (held = pas encore confirmé par client)
        $escrowPending = 0;
        try {
            $escrowPending = (float) \Illuminate\Support\Facades\DB::table('escrow_transactions')
                ->where('prestataire_id', $prestataire->id)
                ->whereIn('status', ['pending', 'held', 'partial'])
                ->selectRaw('COALESCE(SUM(COALESCE(remaining_amount, prestataire_amount, total_amount, amount, 0)), 0) as total')
                ->value('total');
            $pendingBalance += $escrowPending;
        } catch (\Exception $e) {
            \Log::warning('Escrow pending calculation error: ' . $e->getMessage());
        }

        return view('prestataire.payments.connect', compact(
            'prestataire',
            'paymentsEnabled',
            'onboardingPending',
            'requiresMoreInfo',
            'totalEarnings',
            'pendingBalance',
            'escrowPending',
            'commissionRate',
            'stripeAccountId',
            'stripePublishableKey',
            'onboardingUrl',
            'error'
        ));
    }

    /**
     * Initie la connexion Stripe Connect
     */
    public function stripeConnect()
    {
        if (!function_exists('feature_enabled') || !feature_enabled('prestataire_stripe_connect')) {
            return redirect()->back()->with('error', 'Stripe Connect n\'est pas disponible actuellement.');
        }

        $user = Auth::user();
        $prestataire = $user->prestataire;

        // Vérifier si déjà connecté
        if (!empty($prestataire->stripe_account_id)) {
            return redirect()->route('prestataire.payments.stripe.dashboard');
        }

        try {
            \Stripe\Stripe::setApiKey($this->stripeSecretKey());

            $existingStripeAccountId = null;
            $archivedAccount = null;

            // ETAPE 1: Vérifier si un compte archivé existe dans notre table
            $archivedAccount = \App\Models\DeletedStripeAccount::findByEmail($user->email);
            if ($archivedAccount && !empty($archivedAccount->stripe_account_id)) {
                \Log::info('Stripe Connect: Compte archivé trouvé dans DB pour user#' . $user->id);
                $existingStripeAccountId = $archivedAccount->stripe_account_id;
            }

            // ETAPE 2: Si pas trouvé, chercher directement sur Stripe par email
            // PRIORITÉ: Prendre le compte ACTIVÉ (charges_enabled + payouts_enabled) en premier
            if (!$existingStripeAccountId) {
                try {
                    $accounts = \Stripe\Account::all(['limit' => 100]);
                    $matchingAccounts = [];

                    // Collecter tous les comptes avec cet email
                    foreach ($accounts->data as $stripeAccount) {
                        if (
                            !empty($stripeAccount->email) &&
                            strtolower($stripeAccount->email) === strtolower($user->email) &&
                            empty($stripeAccount->deleted)
                        ) {
                            $matchingAccounts[] = $stripeAccount;
                        }
                    }

                    // Trier: comptes activés en premier
                    usort($matchingAccounts, function ($a, $b) {
                        $aEnabled = ($a->charges_enabled && $a->payouts_enabled) ? 1 : 0;
                        $bEnabled = ($b->charges_enabled && $b->payouts_enabled) ? 1 : 0;
                        return $bEnabled - $aEnabled;
                    });

                    if (!empty($matchingAccounts)) {
                        $bestAccount = $matchingAccounts[0];
                        \Log::info('Stripe Connect: Meilleur compte pour user#' . $user->id .
                            ' (charges=' . ($bestAccount->charges_enabled ? 'yes' : 'no') .
                            ', payouts=' . ($bestAccount->payouts_enabled ? 'yes' : 'no') . ')');
                        $existingStripeAccountId = $bestAccount->id;
                    }
                } catch (\Exception $searchError) {
                    \Log::warning('Stripe Connect: Erreur recherche comptes: ' . $searchError->getMessage());
                }
            }

            // ETAPE 3: Si on a trouvé un compte existant, le réutiliser
            if ($existingStripeAccountId) {
                try {
                    $existingAccount = \Stripe\Account::retrieve($existingStripeAccountId);

                    if (empty($existingAccount->deleted)) {
                        // Restaurer le compte Stripe existant
                        $prestataire->stripe_account_id = $existingStripeAccountId;
                        $prestataire->save();

                        // Mettre à jour les métadonnées sur Stripe
                        try {
                            \Stripe\Account::update($existingStripeAccountId, [
                                'metadata' => [
                                    'prestataire_id' => $prestataire->id,
                                    'user_id' => $user->id,
                                    'platform' => 'taprestation.com',
                                    'restored_at' => now()->toISOString(),
                                ],
                            ]);
                        } catch (\Exception $e) {
                            \Log::warning('Stripe: Erreur mise à jour metadata: ' . $e->getMessage());
                        }

                        // Supprimer l'entrée archivée si elle existe
                        if ($archivedAccount) {
                            $archivedAccount->delete();
                        }

                        \Log::info('Stripe Connect: Compte restauré pour prestataire ' . $prestataire->id . ' => ' . $existingStripeAccountId);

                        // Vérifier si l'onboarding est complet
                        if ($existingAccount->charges_enabled && $existingAccount->payouts_enabled) {
                            return redirect()->route('prestataire.payments.stripe.dashboard')
                                ->with('success', 'Votre compte Stripe a été restauré avec succès !');
                        } else {
                            // L'onboarding n'est pas complet, créer un lien pour continuer
                            $accountLink = \Stripe\AccountLink::create([
                                'account' => $existingStripeAccountId,
                                'refresh_url' => route('prestataire.payments.connect'),
                                'return_url' => route('prestataire.payments.stripe.callback'),
                                'type' => 'account_onboarding',
                            ]);
                            return redirect($accountLink->url);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Stripe Connect: Compte existant invalide: ' . $e->getMessage());
                    if ($archivedAccount) {
                        $archivedAccount->delete();
                    }
                }
            }

            // Créer un compte Stripe Connect Express
            // URL du profil prestataire sur la plateforme
            $profileUrl = route('prestataire.public.profile', ['slug' => $prestataire->slug ?? $prestataire->id]);

            $account = \Stripe\Account::create([
                'type' => 'express',
                'country' => 'FR',
                'email' => $user->email,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                    'link_payments' => ['requested' => true], // Stripe Link (paiement rapide)
                ],
                'business_type' => 'individual',
                'business_profile' => [
                    'url' => $profileUrl,
                    'mcc' => '7299', // Services divers
                ],
                'individual' => [
                    'first_name' => $user->first_name ?? explode(' ', $user->name)[0] ?? '',
                    'last_name' => $user->last_name ?? (explode(' ', $user->name)[1] ?? ''),
                    'email' => $user->email,
                ],
                'metadata' => [
                    'prestataire_id' => $prestataire->id,
                    'user_id' => $user->id,
                    'platform' => 'taprestation.com',
                ],
            ]);

            // Sauvegarder l'ID du compte
            $prestataire->stripe_account_id = $account->id;
            $prestataire->save();

            // Créer le lien d'onboarding
            $accountLink = \Stripe\AccountLink::create([
                'account' => $account->id,
                'refresh_url' => route('prestataire.payments.connect'),
                'return_url' => route('prestataire.payments.stripe.callback'),
                'type' => 'account_onboarding',
            ]);

            return redirect($accountLink->url);

        } catch (\Exception $e) {
            \Log::error('Stripe Connect Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de la connexion à Stripe. Réessayez dans quelques instants.');
        }
    }

    /**
     * Callback après connexion Stripe
     * 
     * IMPORTANT: Cette méthode gère le retour après onboarding Stripe.
     * Si l'utilisateur était en train de créer une annonce (service, équipement, etc.),
     * il sera redirigé vers l'étape où il s'était arrêté grâce à payment_setup_return_url.
     */
    public function stripeCallback()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        try {
            \Stripe\Stripe::setApiKey($this->stripeSecretKey());

            // Vérifier le statut du compte
            $account = \Stripe\Account::retrieve($prestataire->stripe_account_id);

            // Mettre à jour les capabilities si link_payments n'est pas encore activé
            $capabilities = $account->capabilities ?? [];
            if (($capabilities['link_payments'] ?? null) !== 'active') {
                try {
                    \Stripe\Account::update($prestataire->stripe_account_id, [
                        'capabilities' => [
                            'link_payments' => ['requested' => true],
                        ],
                    ]);
                    \Log::info('Stripe Callback: link_payments capability demandée pour prestataire ' . $prestataire->id);
                } catch (\Exception $e) {
                    \Log::warning('Stripe Callback: Impossible de demander link_payments: ' . $e->getMessage());
                }
            }

            // Récupérer l'URL de retour (stockée avant la redirection vers Stripe)
            $returnUrl = $this->normalizeInternalRedirectPath(session()->pull('payment_setup_return_url'));

            if ($account->charges_enabled && $account->payouts_enabled) {
                $prestataire->stripe_onboarding_completed = true;
                $prestataire->save();

                \Log::info('Stripe Callback: Onboarding terminé pour prestataire ' . $prestataire->id . ', returnUrl=' . ($returnUrl ?? 'none'));

                // Si une URL de retour existe (ex: création de service step4), y revenir
                if ($returnUrl !== null) {
                    return redirect($returnUrl)
                        ->with('success', 'Votre compte Stripe a été activé ! Vous pouvez maintenant terminer la création de votre annonce.');
                }

                return redirect()->route('prestataire.payments.connect')
                    ->with('success', 'Votre compte Stripe a été connecté avec succès !');
            } else {
                // Onboarding pas encore terminé - stocker à nouveau l'URL pour la prochaine tentative
                if ($returnUrl !== null) {
                    session(['payment_setup_return_url' => $returnUrl]);
                }

                return redirect()->route('prestataire.payments.connect')
                    ->with('info', 'Votre compte Stripe est en cours de vérification. Vous recevrez un email de confirmation. Une fois activé, vous pourrez terminer la création de votre annonce.');
            }

        } catch (\Exception $e) {
            \Log::error('Stripe Callback Error: ' . $e->getMessage());
            return redirect()->route('prestataire.payments.connect')
                ->with('error', 'Erreur lors de la vérification du compte Stripe.');
        }
    }

    /**
     * Redirige vers le dashboard Stripe Express
     */
    public function stripeDashboard()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (empty($prestataire->stripe_account_id)) {
            return redirect()->route('prestataire.payments.connect')
                ->with('error', 'Vous devez d\'abord activer vos paiements.');
        }

        try {
            \Stripe\Stripe::setApiKey($this->stripeSecretKey());

            $loginLink = \Stripe\Account::createLoginLink($prestataire->stripe_account_id);

            return redirect($loginLink->url);

        } catch (\Exception $e) {
            \Log::error('Stripe Dashboard Error: ' . $e->getMessage());
            return redirect()->route('prestataire.payments.connect')
                ->with('error', 'Erreur lors de l\'accès au dashboard Stripe.');
        }
    }

    /**
     * Compléter l'onboarding Stripe si des infos manquent
     */
    public function stripeComplete()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if (empty($prestataire->stripe_account_id)) {
            return redirect()->route('prestataire.payments.stripe.connect');
        }

        try {
            \Stripe\Stripe::setApiKey($this->stripeSecretKey());

            $accountLink = \Stripe\AccountLink::create([
                'account' => $prestataire->stripe_account_id,
                'refresh_url' => route('prestataire.payments.connect'),
                'return_url' => route('prestataire.payments.stripe.callback'),
                'type' => 'account_onboarding',
            ]);

            return redirect($accountLink->url);

        } catch (\Exception $e) {
            \Log::error('Stripe Complete Error: ' . $e->getMessage());
            return redirect()->route('prestataire.payments.connect')
                ->with('error', 'Impossible de finaliser la configuration Stripe pour le moment.');
        }
    }

    /**
     * Page "Mes paiements" - Liste des transactions du prestataire
     */
    public function dashboard()
    {
        $user = Auth::user();
        $prestataire = $user->prestataire;

        if ($prestataire) {
            try {
                app(EquipmentRentalPaymentSyncService::class)->syncForPrestataire($prestataire);
            } catch (\Throwable $e) {
                \Log::warning('Payment connect dashboard rental payment sync warning', [
                    'prestataire_id' => $prestataire->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Stats depuis Stripe
        $totalEarnings = 0;
        $pendingAmount = 0;
        $thisMonthEarnings = 0;
        $payments = collect(); // Collection vide par défaut

        if (!empty($prestataire->stripe_account_id)) {
            try {
                \Stripe\Stripe::setApiKey($this->stripeSecretKey());

                // Récupérer le solde depuis Stripe
                $balance = \Stripe\Balance::retrieve(['stripe_account' => $prestataire->stripe_account_id]);
                $totalEarnings = collect($balance->available)->sum('amount') / 100;
                $pendingAmount = collect($balance->pending)->sum('amount') / 100;

                // Récupérer les derniers paiements depuis Stripe (PaymentIntents API)
                $paymentIntents = \Stripe\PaymentIntent::all([
                    'limit' => 20,
                ], ['stripe_account' => $prestataire->stripe_account_id]);

                $payments = collect($paymentIntents->data)
                    ->filter(fn($pi) => $pi->status === 'succeeded')
                    ->map(function ($pi) {
                        $chargeId = $pi->latest_charge;
                        $appFee = 0;
                        if ($chargeId) {
                            try {
                                $charge = \Stripe\Charge::retrieve($chargeId);
                                $appFee = $charge->application_fee_amount ?? 0;
                            } catch (\Exception $e) {
                                \Log::warning('Stripe charge retrieve failed', ['charge' => $chargeId, 'error' => $e->getMessage()]);
                            }
                        }
                        return (object) [
                            'id' => $pi->id,
                            'amount' => $pi->amount / 100,
                            'prestataire_amount' => ($pi->amount - $appFee) / 100,
                            'status' => 'completed',
                            'created_at' => \Carbon\Carbon::createFromTimestamp($pi->created),
                            'description' => $pi->description ?? 'Paiement',
                            'customer_email' => $pi->receipt_email ?? '',
                        ];
                    });

                // Calculer ce mois-ci
                $thisMonthEarnings = $payments->filter(function ($p) {
                    return $p->created_at->isCurrentMonth() && $p->status === 'completed';
                })->sum('prestataire_amount');

            } catch (\Exception $e) {
                \Log::error('Stripe Dashboard Error: ' . $e->getMessage());
            }
        }

        $commissionRate = CommissionService::ratePercent('service', 'prestataire');

        // Ajouter la balance interne TaPrestation (escrow, ventes urgentes)
        $internalBalance = $prestataire->balance ?? 0;
        $totalEarnings += $internalBalance; // Inclure dans le total

        // Calculer les escrows en attente (held = pas encore confirmé par le client)
        $escrowPending = 0;
        try {
            $escrowPending = \Illuminate\Support\Facades\DB::table('escrow_transactions')
                ->where('prestataire_id', $prestataire->id)
                ->where('status', 'held')
                ->sum('prestataire_amount') ?? 0;

            $pendingAmount += $escrowPending;
        } catch (\Exception $e) {
            \Log::error('Escrow pending calculation error: ' . $e->getMessage());
        }

        return view('prestataire.payments.dashboard', compact(
            'prestataire',
            'payments',
            'totalEarnings',
            'internalBalance',
            'escrowPending',
            'pendingAmount',
            'thisMonthEarnings',
            'commissionRate'
        ));
    }

    private function normalizeInternalRedirectPath($url): ?string
    {
        $value = trim((string) $url);
        if ($value === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            $targetHost = strtolower((string) parse_url($value, PHP_URL_HOST));
            $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
            if ($targetHost === '' || $appHost === '' || $targetHost !== $appHost) {
                return null;
            }
            $value = (string) parse_url($value, PHP_URL_PATH);
        }

        $path = (string) parse_url($value, PHP_URL_PATH);
        if ($path === '' || !str_starts_with($path, '/')) {
            return null;
        }

        return $path;
    }
}
