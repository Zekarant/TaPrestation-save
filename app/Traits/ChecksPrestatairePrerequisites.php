<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Trait pour vérifier les prérequis avant création d'annonces/services/produits
 * - Compte Stripe connecté pour paiement en ligne
 * - Disponibilités configurées pour les services réservables
 */
trait ChecksPrestatairePrerequisites
{
    /**
     * Vérifie si le prestataire a un compte Stripe configuré
     * @return array ['has_payment' => bool, 'type' => string|null]
     */
    protected function hasPaymentAccountConfigured(): array
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire) {
            return ['has_payment' => false, 'type' => null];
        }

        // Vérifier Stripe Connect : compte + onboarding terminé.
        $hasStripeAccountId = !empty($prestataire->stripe_account_id);
        $onboardingCompleted = (bool) ($prestataire->stripe_onboarding_completed ?? false);

        // Si le flag local est déjà true, pas besoin de vérifier Stripe
        if ($hasStripeAccountId && $onboardingCompleted) {
            return ['has_payment' => true, 'type' => 'stripe'];
        }

        // Si on a un compte Stripe mais que le flag n'est pas défini,
        // vérifier directement avec l'API Stripe et synchroniser
        if ($hasStripeAccountId && !$onboardingCompleted) {
            try {
                $stripeSecretKey = config('stripe.secret') ?: config('services.stripe.secret');
                if ($stripeSecretKey) {
                    \Stripe\Stripe::setApiKey($stripeSecretKey);
                    $account = \Stripe\Account::retrieve($prestataire->stripe_account_id);
                    
                    if ($account->charges_enabled && $account->payouts_enabled) {
                        // Synchroniser le flag
                        $prestataire->stripe_onboarding_completed = true;
                        $prestataire->save();
                        \Log::info('ChecksPrestatairePrerequisites: Synchronisé stripe_onboarding_completed pour prestataire ' . $prestataire->id);
                        return ['has_payment' => true, 'type' => 'stripe'];
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('ChecksPrestatairePrerequisites: Erreur vérification Stripe: ' . $e->getMessage());
            }
        }

        return ['has_payment' => false, 'type' => null];
    }

    /**
     * Vérifie si le prestataire a des disponibilités configurées
     * @return bool
     */
    protected function hasAvailabilityConfigured(): bool
    {
        $prestataire = Auth::user()->prestataire;
        
        if (!$prestataire) {
            return false;
        }

        // Vérifier les disponibilités prestataire (table prestataire_availabilities)
        // Le modèle lié est PrestataireAvailability, et le flag est is_active.
        return $prestataire->availabilities()->active()->exists();
    }

    /**
     * Redirige vers la configuration du paiement si nécessaire
     * @param string $paymentPolicy cash|deposit|full_prepay
     * @param string $returnUrl URL de retour après configuration
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function redirectIfPaymentRequired(string $paymentPolicy, string $returnUrl = null)
    {
        // Si paiement en espèces uniquement, pas besoin de compte
        if ($paymentPolicy === 'cash') {
            return null;
        }

        $paymentStatus = $this->hasPaymentAccountConfigured();

        if (!$paymentStatus['has_payment']) {
            // Stocker l'URL de retour en session
            if ($returnUrl) {
                session(['payment_setup_return_url' => $returnUrl]);
            }

            return redirect()->route('prestataire.payments.connect')
                ->with('warning', $this->getPaymentSetupMessage($paymentPolicy));
        }

        return null;
    }

    /**
     * Redirige vers la configuration des disponibilités si nécessaire
     * @param bool $isReservable Le service est-il réservable ?
     * @param string $returnUrl URL de retour après configuration
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function redirectIfAvailabilityRequired(bool $isReservable, string $returnUrl = null)
    {
        // Si le service n'est pas réservable, pas besoin de disponibilités
        if (!$isReservable) {
            return null;
        }

        if (!$this->hasAvailabilityConfigured()) {
            // Stocker l'URL de retour en session
            if ($returnUrl) {
                session(['availability_setup_return_url' => $returnUrl]);
            }

            return redirect()->route('prestataire.availability.index')
                ->with('warning', $this->getAvailabilitySetupMessage());
        }

        return null;
    }

    /**
     * Redirige vers la configuration des disponibilités si le prestataire n'en a aucune d'active.
     * Utilisé quand on veut rendre les disponibilités obligatoires, même si le service n'est pas réservable.
     */
    protected function redirectIfAvailabilityMissing(string $returnUrl = null)
    {
        if ($this->hasAvailabilityConfigured()) {
            return null;
        }

        if ($returnUrl) {
            session(['availability_setup_return_url' => $returnUrl]);
        }

        return redirect()->route('prestataire.availability.index')
            ->with('warning', $this->getAvailabilitySetupMessage());
    }

    /**
     * Message explicatif pour la configuration du paiement
     */
    protected function getPaymentSetupMessage(string $paymentPolicy): string
    {
        $typeLabel = match($paymentPolicy) {
            'deposit' => "l'acompte en ligne",
            'full_prepay' => 'le prépaiement en ligne',
            default => 'le paiement en ligne',
        };

         return "Vous n'avez pas encore de compte Stripe connecté/activé. " .
             "Pour activer {$typeLabel}, créez puis connectez votre compte Stripe (onboarding terminé). " .
             "Une fois Stripe configuré, revenez ici pour terminer la validation et publier votre annonce.";
    }

    /**
     * Message explicatif pour la configuration des disponibilités
     */
    protected function getAvailabilitySetupMessage(): string
    {
         return "📅 Pour créer un service, vous devez d'abord activer au moins un jour dans vos disponibilités. " .
             "Page : " . route('prestataire.availability.index') . " " .
             "Après enregistrement, vous serez renvoyé automatiquement vers la page précédente.";
    }

    /**
     * Vérifie les prérequis complets pour création d'une annonce
     * @param string $paymentPolicy Policy de paiement choisie
     * @param bool $isReservable Pour les services
     * @param string $context 'equipment', 'service', 'food'
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function checkCreationPrerequisites(
        string $paymentPolicy = 'cash',
        bool $isReservable = false,
        string $context = 'equipment'
    ) {
        // Vérifier le paiement si nécessaire
        if ($paymentPolicy !== 'cash') {
            $redirect = $this->redirectIfPaymentRequired(
                $paymentPolicy,
                $this->getCreationReturnUrl($context)
            );
            if ($redirect) return $redirect;
        }

        // Vérifier les disponibilités pour les services réservables
        if ($context === 'service' && $isReservable) {
            $redirect = $this->redirectIfAvailabilityRequired(
                $isReservable,
                $this->getCreationReturnUrl($context)
            );
            if ($redirect) return $redirect;
        }

        return null;
    }

    /**
     * Retourne l'URL de création selon le contexte
     */
    protected function getCreationReturnUrl(string $context): string
    {
        return match($context) {
            'equipment' => route('prestataire.equipment.create'),
            'service' => route('prestataire.services.create'),
            'food' => route('prestataire.food-products.create'),
            default => route('prestataire.dashboard'),
        };
    }
}
