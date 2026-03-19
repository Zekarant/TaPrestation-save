@extends('layouts.app')

@section('title', 'Configurer vos paiements')

@push('styles')
<style>
:root {
    --primary: #10b981;
    --primary-dark: #059669;
    --bg-page: #f8fafc;
    --bg-card: #ffffff;
    --bg-input: #f3f4f6;
    --text-dark: #1f2937;
    --text-body: #4b5563;
    --text-muted: #9ca3af;
    --border: #e5e7eb;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
}
.dark {
    --bg-page: #0c0c0c;
    --bg-card: #161616;
    --bg-input: #1f1f1f;
    --text-dark: #ffffff;
    --text-body: #d1d5db;
    --text-muted: #6b7280;
    --border: #2a2a2a;
}

.payment-setup-page { min-height: 100vh; background: var(--bg-page); padding: 16px; padding-bottom: 100px; }

.page-header { text-align: center; margin-bottom: 24px; }
.header-icon { width: 72px; height: 72px; background: linear-gradient(135deg, #635bff, #4f46e5); border-radius: 18px; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; }
.page-title { font-size: 1.4rem; font-weight: 700; color: var(--text-dark); margin-bottom: 8px; }
.page-subtitle { font-size: 0.9rem; color: var(--text-muted); max-width: 320px; margin: 0 auto; line-height: 1.5; }

.setup-card { background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border); padding: 20px; margin-bottom: 16px; }
.card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
.card-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
.card-icon.stripe { background: linear-gradient(135deg, rgba(99, 91, 255, 0.15), rgba(79, 70, 229, 0.15)); color: #635bff; }
.card-icon.success { background: rgba(16, 185, 129, 0.15); color: var(--success); }
.card-icon.warning { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
.card-title { font-size: 1rem; font-weight: 700; color: var(--text-dark); }
.card-subtitle { font-size: 0.75rem; color: var(--text-muted); }

.status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.status-badge.connected { background: rgba(16, 185, 129, 0.15); color: var(--success); }
.status-badge.pending { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
.status-badge.error { background: rgba(239, 68, 68, 0.15); color: var(--danger); }

.benefits-grid { display: grid; gap: 12px; margin-bottom: 20px; }
.benefit-item { display: flex; align-items: flex-start; gap: 12px; padding: 12px; background: var(--bg-input); border-radius: 12px; }
.benefit-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; background: rgba(16, 185, 129, 0.15); color: var(--primary); }
.benefit-content h4 { font-size: 0.85rem; font-weight: 600; color: var(--text-dark); margin: 0 0 4px; }
.benefit-content p { font-size: 0.75rem; color: var(--text-muted); margin: 0; line-height: 1.4; }

.btn-stripe { width: 100%; padding: 16px; background: linear-gradient(135deg, #635bff, #4f46e5); color: white; border: none; border-radius: 12px; font-size: 0.95rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.2s; }
.btn-stripe:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99, 91, 255, 0.4); }
.btn-stripe:active { transform: scale(0.98); }
.btn-stripe i { font-size: 1.1rem; }

.btn-skip { width: 100%; padding: 14px; background: transparent; border: 1px solid var(--border); color: var(--text-body); border-radius: 12px; font-size: 0.85rem; font-weight: 600; cursor: pointer; margin-top: 12px; transition: all 0.2s; }
.btn-skip:hover { background: var(--bg-input); }

.info-box { display: flex; align-items: flex-start; gap: 12px; padding: 12px; background: rgba(99, 91, 255, 0.08); border-radius: 12px; margin-top: 16px; }
.info-box i { color: #635bff; font-size: 1rem; flex-shrink: 0; margin-top: 2px; }
.info-box p { font-size: 0.75rem; color: var(--text-body); margin: 0; line-height: 1.5; }

.user-info { background: var(--bg-input); border-radius: 12px; padding: 14px; margin-bottom: 16px; }
.user-info-title { font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
.user-info-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid var(--border); }
.user-info-row:last-child { border-bottom: none; }
.user-info-label { font-size: 0.8rem; color: var(--text-muted); }
.user-info-value { font-size: 0.8rem; color: var(--text-dark); font-weight: 500; }

.dark .setup-card { box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
</style>
@endpush

@section('content')
<div class="payment-setup-page">
    <div class="page-header">
        <div class="header-icon"><i class="fab fa-stripe-s"></i></div>
        <h1 class="page-title">Recevoir vos paiements</h1>
        <p class="page-subtitle">
            @if(!empty($isInternalDriver))
                Mode interne détecté: Stripe est optionnel.
            @else
                Connectez votre compte Stripe pour recevoir vos gains automatiquement.
            @endif
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.1); color: #059669; padding: 12px; border-radius: 10px; margin-bottom: 16px; font-size: 0.85rem;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; padding: 12px; border-radius: 10px; margin-bottom: 16px; font-size: 0.85rem;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if(!empty($isInternalDriver))
        <div class="alert alert-info" style="background: rgba(59, 130, 246, 0.1); color: #1d4ed8; padding: 12px; border-radius: 10px; margin-bottom: 16px; font-size: 0.85rem;">
            <i class="fas fa-info-circle"></i> Livreur interne: vous pouvez continuer sans Stripe. Activez Stripe seulement si vous voulez des versements directs livreur.
        </div>
    @endif

    <!-- Statut actuel -->
    <div class="setup-card">
        <div class="card-header">
            <div class="card-icon {{ $driver->stripe_account_id ? 'success' : 'warning' }}">
                <i class="fas {{ $driver->stripe_account_id ? 'fa-check-circle' : 'fa-clock' }}"></i>
            </div>
            <div>
                <div class="card-title">Statut du compte</div>
                @if($driver->stripe_account_id && $driver->stripe_onboarding_complete)
                    <span class="status-badge connected"><i class="fas fa-check"></i> Connecté</span>
                @elseif($driver->stripe_account_id)
                    <span class="status-badge pending"><i class="fas fa-clock"></i> Configuration incomplète</span>
                @else
                    <span class="status-badge pending"><i class="fas fa-exclamation"></i> Non configuré</span>
                @endif
            </div>
        </div>

        @if($driver->stripe_account_id && $driver->stripe_onboarding_complete)
            <p style="font-size: 0.85rem; color: var(--text-body); margin: 0;">
                ✅ Votre compte est prêt ! Vos gains seront automatiquement transférés après chaque livraison.
            </p>
        @endif
    </div>

    <!-- Infos pré-remplies -->
    <div class="setup-card">
        <div class="user-info-title">Informations utilisées pour Stripe</div>
        <div class="user-info">
            <div class="user-info-row">
                <span class="user-info-label">Nom</span>
                <span class="user-info-value">{{ $driver->first_name }} {{ $driver->last_name }}</span>
            </div>
            <div class="user-info-row">
                <span class="user-info-label">Email</span>
                <span class="user-info-value">{{ $driver->email }}</span>
            </div>
            <div class="user-info-row">
                <span class="user-info-label">Téléphone</span>
                <span class="user-info-value">{{ $driver->phone ?? 'Non renseigné' }}</span>
            </div>
            <div class="user-info-row">
                <span class="user-info-label">Adresse</span>
                <span class="user-info-value">{{ $driver->address ?? 'Non renseignée' }}</span>
            </div>
            <div class="user-info-row">
                <span class="user-info-label">Ville</span>
                <span class="user-info-value">{{ $driver->city ?? 'Non renseignée' }}, {{ $driver->postal_code ?? '' }}</span>
            </div>
        </div>
    </div>

    <!-- Avantages -->
    <div class="setup-card">
        <div class="card-header">
            <div class="card-icon stripe"><i class="fab fa-stripe-s"></i></div>
            <div>
                <div class="card-title">Pourquoi Stripe ?</div>
                <div class="card-subtitle">Paiements sécurisés et rapides</div>
            </div>
        </div>

        <div class="benefits-grid">
            <div class="benefit-item">
                <div class="benefit-icon"><i class="fas fa-bolt"></i></div>
                <div class="benefit-content">
                    <h4>Paiements instantanés</h4>
                    <p>Recevez vos gains directement après chaque livraison confirmée</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="benefit-content">
                    <h4>100% sécurisé</h4>
                    <p>Stripe est utilisé par des millions d'entreprises dans le monde</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon"><i class="fas fa-university"></i></div>
                <div class="benefit-content">
                    <h4>Transfert vers votre banque</h4>
                    <p>Virement automatique vers votre compte bancaire</p>
                </div>
            </div>
        </div>

        @if(!$driver->stripe_account_id || !$driver->stripe_onboarding_complete)
            @if(isset($hasExistingStripeFromPrestataire) && $hasExistingStripeFromPrestataire)
                <!-- Message de synchronisation depuis compte prestataire -->
                <div style="background: rgba(99, 91, 255, 0.1); border: 1px solid rgba(99, 91, 255, 0.3); border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                        <i class="fas fa-sync-alt" style="color: #635bff; font-size: 1.2rem;"></i>
                        <strong style="color: var(--text-dark); font-size: 0.9rem;">Compte Stripe détecté !</strong>
                    </div>
                    <p style="font-size: 0.8rem; color: var(--text-body); margin: 0; line-height: 1.5;">
                        Vous avez déjà un compte Stripe configuré en tant que prestataire. 
                        Cliquez sur le bouton ci-dessous pour l'utiliser automatiquement et recevoir vos paiements de livraison.
                    </p>
                </div>
            @endif

            <form action="{{ route('driver.stripe.connect') }}" method="POST">
                @csrf
                <button type="submit" class="btn-stripe">
                    <i class="fab fa-stripe-s"></i>
                    @if(isset($hasExistingStripeFromPrestataire) && $hasExistingStripeFromPrestataire)
                        Synchroniser mon compte Stripe prestataire
                    @else
                        {{ $driver->stripe_account_id ? 'Terminer la configuration' : 'Connecter mon compte Stripe' }}
                    @endif
                </button>
            </form>

            <button type="button" class="btn-skip" onclick="window.location='{{ route('driver.dashboard') }}'">
                @if(!empty($isInternalDriver))
                    Continuer sans Stripe (interne)
                @else
                    Configurer plus tard
                @endif
            </button>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                @if(isset($hasExistingStripeFromPrestataire) && $hasExistingStripeFromPrestataire)
                    <p>Votre compte Stripe prestataire sera utilisé pour recevoir également vos gains de livraison. Aucune configuration supplémentaire requise !</p>
                @else
                    <p>La création d'un compte Stripe est gratuite et prend moins de 5 minutes. Vous aurez besoin d'une pièce d'identité et de vos coordonnées bancaires.</p>
                @endif
            </div>
        @else
            <a href="{{ route('driver.stripe.dashboard') }}" class="btn-stripe" style="text-decoration: none;">
                <i class="fas fa-external-link-alt"></i>
                Accéder à mon tableau de bord Stripe
            </a>
        @endif
    </div>

    <!-- Retour au dashboard -->
    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ route('driver.dashboard') }}" style="color: var(--text-muted); font-size: 0.85rem; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>
    </div>
</div>
@endsection
