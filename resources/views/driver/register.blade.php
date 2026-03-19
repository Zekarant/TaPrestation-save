@extends('layouts.app')

@section('title', 'Devenir Livreur')

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

.register-page { min-height: 100vh; background: var(--bg-page); padding: 16px; padding-bottom: 100px; }

.register-header { text-align: center; margin-bottom: 20px; }
.register-icon { width: 64px; height: 64px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 16px; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: white; }
.register-title { font-size: 1.3rem; font-weight: 700; color: var(--text-dark); margin-bottom: 4px; }
.register-subtitle { font-size: 0.85rem; color: var(--text-muted); }

.register-card { background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border); padding: 16px; margin-bottom: 16px; }
.card-title { font-size: 0.85rem; font-weight: 700; color: var(--text-dark); margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
.card-title i { color: var(--primary); }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.form-group { margin-bottom: 12px; }
.form-group.full { grid-column: 1 / -1; }
.form-label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-body); margin-bottom: 4px; }
.form-input { width: 100%; padding: 10px 12px; background: var(--bg-input); border: 1px solid var(--border); border-radius: 10px; font-size: 0.85rem; color: var(--text-dark); transition: border 0.2s, box-shadow 0.2s; }
.form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15); }
.form-input::placeholder { color: var(--text-muted); }
select.form-input { cursor: pointer; }

.vehicle-options { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
.vehicle-option { text-align: center; padding: 10px 6px; background: var(--bg-input); border: 2px solid var(--border); border-radius: 10px; cursor: pointer; transition: all 0.2s; }
.vehicle-option:hover { border-color: var(--primary); }
.vehicle-option.selected { border-color: var(--primary); background: rgba(16, 185, 129, 0.1); }
.vehicle-option input { display: none; }
.vehicle-option i { font-size: 1.3rem; color: var(--text-muted); margin-bottom: 4px; display: block; }
.vehicle-option.selected i { color: var(--primary); }
.vehicle-option span { font-size: 0.65rem; color: var(--text-body); font-weight: 600; }

.mode-options { display: grid; gap: 8px; }
.mode-option { display: block; border: 2px solid var(--border); border-radius: 12px; padding: 12px; background: var(--bg-input); cursor: pointer; transition: all .2s; }
.mode-option.selected { border-color: var(--primary); background: rgba(16, 185, 129, 0.08); }
.mode-option input { display: none; }
.mode-option-title { font-size: 0.78rem; font-weight: 700; color: var(--text-dark); margin-bottom: 3px; display: flex; align-items: center; gap: 6px; }
.mode-option-desc { font-size: 0.68rem; color: var(--text-body); line-height: 1.4; }
.mode-inline { display: flex; align-items: center; gap: 8px; margin-top: 8px; }
.mode-badge { font-size: 0.62rem; font-weight: 700; color: #1d4ed8; background: rgba(59, 130, 246, 0.12); border-radius: 6px; padding: 2px 7px; }
.mode-helper { font-size: 0.65rem; color: var(--text-muted); margin-top: 6px; }

.terms-group { display: flex; align-items: flex-start; gap: 10px; }
.terms-checkbox { width: 18px; height: 18px; accent-color: var(--primary); margin-top: 2px; flex-shrink: 0; }
.terms-label { font-size: 0.75rem; color: var(--text-body); line-height: 1.4; }
.terms-label a { color: var(--primary); text-decoration: none; }

.benefits-list { display: grid; gap: 8px; }
.benefit-item { display: flex; align-items: center; gap: 10px; padding: 10px; background: var(--bg-input); border-radius: 10px; }
.benefit-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.benefit-icon.green { background: rgba(16, 185, 129, 0.15); color: var(--primary); }
.benefit-icon.blue { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
.benefit-icon.orange { background: rgba(249, 115, 22, 0.15); color: #f97316; }
.benefit-icon.purple { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; }
.benefit-text h4 { font-size: 0.8rem; font-weight: 700; color: var(--text-dark); margin-bottom: 2px; }
.benefit-text p { font-size: 0.7rem; color: var(--text-muted); }

.submit-bar { position: fixed; bottom: 0; left: 0; right: 0; background: var(--bg-card); border-top: 1px solid var(--border); padding: 12px 16px; z-index: 100; }
.submit-bar-inner { max-width: 500px; margin: 0 auto; }
.btn-submit { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; border: none; border-radius: 12px; font-size: 0.9rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: transform 0.2s, box-shadow 0.2s; }
.btn-submit:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
.btn-submit:active { transform: scale(0.98); }

.error-message { color: var(--danger); font-size: 0.7rem; margin-top: 4px; }
.alert { padding: 10px 14px; border-radius: 10px; font-size: 0.8rem; margin-bottom: 16px; }
.alert-danger { background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); }

/* Sponsorship Section */
.sponsor-info { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 10px; }
.sponsor-badge { width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.1rem; flex-shrink: 0; }
.sponsor-text p { font-size: 0.75rem; color: var(--text-body); margin: 0; line-height: 1.4; }
.sponsor-text p:first-child { color: var(--text-dark); margin-bottom: 4px; }
.sponsor-benefits { list-style: none; padding: 0; margin: 8px 0 0; display: grid; gap: 4px; }
.sponsor-benefits li { font-size: 0.7rem; color: var(--text-body); display: flex; align-items: center; gap: 6px; }
.sponsor-benefits li i { color: var(--primary); font-size: 0.6rem; }
.form-hint { font-size: 0.65rem; color: var(--text-muted); margin-top: 4px; display: block; }

/* Progress Steps */
.info-card { background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(59, 130, 246, 0.05)); }
.progress-steps { display: grid; gap: 10px; }
.progress-step { display: flex; align-items: center; gap: 10px; padding: 10px; background: var(--bg-input); border-radius: 10px; border: 1px solid var(--border); }
.progress-step.active { background: rgba(16, 185, 129, 0.1); border-color: var(--primary); }
.step-number { width: 28px; height: 28px; border-radius: 50%; background: var(--bg-card); border: 2px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); flex-shrink: 0; }
.progress-step.active .step-number { background: var(--primary); border-color: var(--primary); color: white; }
.step-content h4 { font-size: 0.75rem; font-weight: 700; color: var(--text-dark); margin: 0 0 2px; }
.step-content p { font-size: 0.65rem; color: var(--text-muted); margin: 0; }
.warning-note { display: flex; align-items: center; gap: 8px; margin-top: 10px; padding: 8px 10px; background: rgba(239, 68, 68, 0.08); border-radius: 8px; font-size: 0.7rem; color: #dc2626; }
.warning-note i { font-size: 0.75rem; }

.dark .register-card { box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
.dark .submit-bar { background: #161616; border-color: #252525; }
</style>
@endpush

@section('content')
<div class="register-page">
    @php
        $stripeDriverEnabled = function_exists('prestataire_stripe_connect_enabled')
            ? prestataire_stripe_connect_enabled()
            : true;
        $oldDriverMode = old('driver_mode');
        $defaultDriverMode = $stripeDriverEnabled
            ? ($oldDriverMode ?: 'stripe')
            : 'internal_code';
    @endphp

    <div class="register-header">
        <div class="register-icon">🚴</div>
        <h1 class="register-title">Devenir Livreur</h1>
        <p class="register-subtitle">Gagnez de l'argent en livrant avec TaPrestation</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 16px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('driver.register.submit') }}" method="POST">
        @csrf

        <div class="register-card">
            <div class="card-title"><i class="fas fa-sliders-h"></i> Mode livreur</div>
            @if(!$stripeDriverEnabled)
                <div class="alert alert-info" style="margin-bottom: 16px;">
                    Les paiements Stripe livreur sont désactivés. Seul le mode avec code prestataire reste disponible.
                </div>
            @endif
            <div class="mode-options">
                @if($stripeDriverEnabled)
                    <label class="mode-option {{ $defaultDriverMode === 'stripe' ? 'selected' : '' }}" data-mode-option>
                        <input type="radio" name="driver_mode" value="stripe" {{ $defaultDriverMode === 'stripe' ? 'checked' : '' }}>
                        <div class="mode-option-title"><i class="fas fa-credit-card"></i> J'ai Stripe (ou je l'active maintenant)</div>
                        <div class="mode-option-desc">Compte livreur complet avec paiements en ligne.</div>
                    </label>

                    <label class="mode-option {{ $defaultDriverMode === 'create_stripe' ? 'selected' : '' }}" data-mode-option>
                        <input type="radio" name="driver_mode" value="create_stripe" {{ $defaultDriverMode === 'create_stripe' ? 'checked' : '' }}>
                        <div class="mode-option-title"><i class="fas fa-link"></i> Je n'ai pas Stripe, je veux le créer</div>
                        <div class="mode-option-desc">Après validation, vous serez dirigé vers la configuration Stripe.</div>
                    </label>
                @endif

                <label class="mode-option {{ $defaultDriverMode === 'internal_code' ? 'selected' : '' }}" data-mode-option>
                    <input type="radio" name="driver_mode" value="internal_code" {{ $defaultDriverMode === 'internal_code' ? 'checked' : '' }}>
                    <div class="mode-option-title"><i class="fas fa-key"></i> Je n'ai pas Stripe, j'ai un code prestataire</div>
                    <div class="mode-option-desc">Accès interne: carte + tournées assignées uniquement.</div>
                    <div class="mode-inline">
                        <span class="mode-badge">MODE INTERNE</span>
                        <span class="mode-helper">Utilisez le code fourni par votre prestataire.</span>
                    </div>
                </label>
            </div>

            <div class="form-group full" id="internalCodeGroup" style="{{ $defaultDriverMode === 'internal_code' ? '' : 'display:none;' }}">
                <label class="form-label">Code prestataire *</label>
                <input type="text" name="internal_access_code" id="internal_access_code" class="form-input" value="{{ old('internal_access_code') }}" placeholder="INT-AB-1234">
                <small class="form-hint">Ce code vous donne accès à vos tournées sur la carte.</small>
            </div>
        </div>

        <div class="register-card">
            <div class="card-title"><i class="fas fa-user"></i> Informations personnelles</div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Prénom *</label>
                    <input type="text" name="first_name" class="form-input" value="{{ old('first_name', auth()->user()->name ?? '') }}" required placeholder="Jean">
                </div>
                <div class="form-group">
                    <label class="form-label">Nom *</label>
                    @php
                        $defaultLastName = '';
                        if (auth()->user()->prestataire) {
                            $defaultLastName = auth()->user()->prestataire->company_name ?? '';
                        } elseif (auth()->user()->client) {
                            $nameParts = explode(' ', auth()->user()->name ?? '');
                            $defaultLastName = count($nameParts) > 1 ? end($nameParts) : '';
                        }
                    @endphp
                    <input type="text" name="last_name" class="form-input" value="{{ old('last_name', $defaultLastName) }}" required placeholder="Dupont">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Téléphone *</label>
                    @php
                        $defaultPhone = auth()->user()->phone ?? '';
                        if (!$defaultPhone && auth()->user()->prestataire) {
                            $defaultPhone = auth()->user()->prestataire->phone ?? '';
                        } elseif (!$defaultPhone && auth()->user()->client) {
                            $defaultPhone = auth()->user()->client->phone ?? '';
                        }
                    @endphp
                    <input type="tel" name="phone" class="form-input" value="{{ old('phone', $defaultPhone) }}" required placeholder="06 12 34 56 78">
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', auth()->user()->email ?? '') }}" required placeholder="jean@email.com">
                </div>
            </div>
            <div class="form-group full">
                <label class="form-label">Date de naissance * (18 ans minimum)</label>
                <input type="date" name="birth_date" class="form-input" value="{{ old('birth_date') }}" required max="{{ now()->subYears(18)->format('Y-m-d') }}">
            </div>
        </div>

        <!-- Adresse (pour Stripe Connect) -->
        <div class="register-card">
            <div class="card-title"><i class="fas fa-map-marker-alt"></i> Adresse postale</div>
            <small class="form-hint" id="addressHint" style="display: block; margin-bottom: 12px;">Requise pour la configuration de votre compte de paiement Stripe</small>
            @php
                $defaultAddress = '';
                $defaultCity = '';
                $defaultPostalCode = '';
                
                if (auth()->user()->prestataire) {
                    $defaultAddress = auth()->user()->prestataire->address ?? '';
                    $defaultCity = auth()->user()->prestataire->city ?? '';
                    $defaultPostalCode = auth()->user()->prestataire->postal_code ?? '';
                } elseif (auth()->user()->client) {
                    $defaultAddress = auth()->user()->client->address ?? '';
                    $defaultCity = auth()->user()->client->city ?? '';
                    $defaultPostalCode = auth()->user()->client->postal_code ?? '';
                }
            @endphp
            <div class="form-group full">
                <label class="form-label">Adresse *</label>
                <input type="text" name="address" id="addressField" class="form-input" value="{{ old('address', $defaultAddress) }}" placeholder="123 rue de la Livraison">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Ville *</label>
                    <input type="text" name="city" id="cityField" class="form-input" value="{{ old('city', $defaultCity) }}" placeholder="Paris">
                </div>
                <div class="form-group">
                    <label class="form-label">Code postal *</label>
                    <input type="text" name="postal_code" id="postalCodeField" class="form-input" value="{{ old('postal_code', $defaultPostalCode) }}" placeholder="75001" maxlength="10">
                </div>
            </div>
        </div>

        <div class="register-card">
            <div class="card-title"><i class="fas fa-motorcycle"></i> Véhicule</div>
            <div class="form-group">
                <label class="form-label">Type de véhicule *</label>
                <div class="vehicle-options">
                    <label class="vehicle-option {{ old('vehicle_type') == 'bike' ? 'selected' : '' }}">
                        <input type="radio" name="vehicle_type" value="bike" {{ old('vehicle_type') == 'bike' ? 'checked' : '' }}>
                        <i class="fas fa-bicycle"></i>
                        <span>Vélo</span>
                    </label>
                    <label class="vehicle-option {{ old('vehicle_type') == 'scooter' ? 'selected' : '' }}">
                        <input type="radio" name="vehicle_type" value="scooter" {{ old('vehicle_type') == 'scooter' ? 'checked' : '' }}>
                        <i class="fas fa-motorcycle"></i>
                        <span>Scooter</span>
                    </label>
                    <label class="vehicle-option {{ old('vehicle_type') == 'car' ? 'selected' : '' }}">
                        <input type="radio" name="vehicle_type" value="car" {{ old('vehicle_type') == 'car' ? 'checked' : '' }}>
                        <i class="fas fa-car"></i>
                        <span>Voiture</span>
                    </label>
                    <label class="vehicle-option {{ old('vehicle_type') == 'van' ? 'selected' : '' }}">
                        <input type="radio" name="vehicle_type" value="van" {{ old('vehicle_type') == 'van' ? 'checked' : '' }}>
                        <i class="fas fa-truck"></i>
                        <span>Camion</span>
                    </label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Plaque d'immatriculation</label>
                    <input type="text" name="vehicle_plate" class="form-input" value="{{ old('vehicle_plate') }}" placeholder="AB-123-CD">
                </div>
                <div class="form-group">
                    <label class="form-label">N° Permis</label>
                    <input type="text" name="license_number" class="form-input" value="{{ old('license_number') }}" placeholder="123456789">
                </div>
            </div>
        </div>

        <div class="register-card">
            <div class="card-title"><i class="fas fa-map-marker-alt"></i> Zone de livraison</div>
            <div class="form-group">
                <label class="form-label">Rayon maximum (km)</label>
                <select name="max_radius" class="form-input">
                    <option value="5" {{ old('max_radius', 10) == 5 ? 'selected' : '' }}>5 km</option>
                    <option value="10" {{ old('max_radius', 10) == 10 ? 'selected' : '' }}>10 km</option>
                    <option value="15" {{ old('max_radius', 10) == 15 ? 'selected' : '' }}>15 km</option>
                    <option value="20" {{ old('max_radius', 10) == 20 ? 'selected' : '' }}>20 km</option>
                    <option value="30" {{ old('max_radius', 10) == 30 ? 'selected' : '' }}>30 km</option>
                    <option value="50" {{ old('max_radius', 10) == 50 ? 'selected' : '' }}>50 km</option>
                </select>
            </div>
            <input type="hidden" name="current_lat" id="current_lat" value="{{ old('current_lat') }}">
            <input type="hidden" name="current_lng" id="current_lng" value="{{ old('current_lng') }}">
            <input type="hidden" name="working_hours" value="{}">
        </div>

        <!-- Section Parrainage -->
        <div class="register-card">
            <div class="card-title"><i class="fas fa-handshake"></i> Parrainage (optionnel mais recommandé)</div>
            <div class="sponsor-info">
                <div class="sponsor-badge"><i class="fas fa-shield-alt"></i></div>
                <div class="sponsor-text">
                    <p><strong>Boostez votre démarrage !</strong></p>
                    <p>Faites-vous parrainer par un prestataire bien noté (4+ étoiles, 10+ avis) pour bénéficier d'avantages :</p>
                </div>
            </div>
            <ul class="sponsor-benefits">
                <li><i class="fas fa-check"></i> Limite journalière plus élevée (5 au lieu de 3)</li>
                <li><i class="fas fa-check"></i> Montant max plus élevé (100€ au lieu de 50€)</li>
                <li><i class="fas fa-check"></i> Confiance accrue auprès des restaurants</li>
            </ul>
            <div class="form-group" style="margin-top: 12px;">
                <label class="form-label">Code ou nom du prestataire parrain</label>
                <input type="text" name="sponsor_code" class="form-input" value="{{ old('sponsor_code') }}" placeholder="Ex: PIZZA-MARIO ou code parrain">
                <small class="form-hint">Demandez à un restaurant partenaire de vous parrainer</small>
            </div>
        </div>

        <!-- Section Vérification Progressive -->
        <div class="register-card info-card">
            <div class="card-title"><i class="fas fa-chart-line"></i> Comment ça marche ?</div>
            <div class="progress-steps">
                <div class="progress-step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>Période d'essai</h4>
                        <p>Max 3 livraisons/jour, commandes ≤50€</p>
                    </div>
                </div>
                <div class="progress-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>10 livraisons réussies</h4>
                        <p>Avec note moyenne ≥3/5 étoiles</p>
                    </div>
                </div>
                <div class="progress-step active">
                    <div class="step-number"><i class="fas fa-unlock"></i></div>
                    <div class="step-content">
                        <h4>Compte débloqué !</h4>
                        <p>Livraisons illimitées, tous montants</p>
                    </div>
                </div>
            </div>
            <div class="warning-note">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Note ≤3/5 après 5 livraisons = compte suspendu automatiquement</span>
            </div>
        </div>

        <div class="register-card">
            <div class="card-title"><i class="fas fa-gift"></i> Avantages</div>
            <div class="benefits-list">
                <div class="benefit-item">
                    <div class="benefit-icon green"><i class="fas fa-wallet"></i></div>
                    <div class="benefit-text">
                        <h4>Revenus attractifs</h4>
                        <p>Gagnez jusqu'à 2000€/mois</p>
                    </div>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon blue"><i class="fas fa-clock"></i></div>
                    <div class="benefit-text">
                        <h4>Flexibilité totale</h4>
                        <p>Choisissez vos horaires librement</p>
                    </div>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon orange"><i class="fas fa-bolt"></i></div>
                    <div class="benefit-text">
                        <h4>Paiement rapide</h4>
                        <p>Virements hebdomadaires</p>
                    </div>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon purple"><i class="fas fa-headset"></i></div>
                    <div class="benefit-text">
                        <h4>Support 24/7</h4>
                        <p>Assistance dédiée aux livreurs</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="register-card">
            <div class="terms-group">
                <input type="checkbox" name="terms" id="terms" class="terms-checkbox" value="1" {{ old('terms') ? 'checked' : '' }} required>
                <label for="terms" class="terms-label">
                    J'accepte les <a href="{{ route('terms') }}" target="_blank">Conditions Générales d'Utilisation</a> et la <a href="{{ route('privacy') }}" target="_blank">Politique de Confidentialité</a>. 
                    Je certifie avoir au moins 18 ans et être autorisé à travailler en France.
                </label>
            </div>
        </div>

        <div class="submit-bar">
            <div class="submit-bar-inner">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i> S'inscrire comme livreur
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Vehicle selection
document.querySelectorAll('.vehicle-option').forEach(opt => {
    opt.addEventListener('click', () => {
        document.querySelectorAll('.vehicle-option').forEach(o => o.classList.remove('selected'));
        opt.classList.add('selected');
    });
});

function applyDriverModeUI(mode) {
    const internalCodeGroup = document.getElementById('internalCodeGroup');
    const internalCodeInput = document.getElementById('internal_access_code');
    const addressField = document.getElementById('addressField');
    const cityField = document.getElementById('cityField');
    const postalCodeField = document.getElementById('postalCodeField');
    const addressHint = document.getElementById('addressHint');

    const stripeRequired = mode !== 'internal_code';

    if (internalCodeGroup) {
        internalCodeGroup.style.display = mode === 'internal_code' ? '' : 'none';
    }
    if (internalCodeInput) {
        internalCodeInput.required = mode === 'internal_code';
    }

    [addressField, cityField, postalCodeField].forEach((field) => {
        if (field) {
            field.required = stripeRequired;
        }
    });

    if (addressHint) {
        addressHint.textContent = stripeRequired
            ? 'Requise pour la configuration de votre compte de paiement Stripe'
            : 'Optionnelle en mode code prestataire (livreur interne).';
    }
}

const modeInputs = document.querySelectorAll('input[name="driver_mode"]');
modeInputs.forEach((input) => {
    input.addEventListener('change', () => {
        document.querySelectorAll('[data-mode-option]').forEach((card) => {
            card.classList.remove('selected');
        });
        input.closest('[data-mode-option]')?.classList.add('selected');
        applyDriverModeUI(input.value);
    });
});

const selectedMode = document.querySelector('input[name="driver_mode"]:checked')?.value || @json($defaultDriverMode);
applyDriverModeUI(selectedMode);

// Get current location
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(pos => {
        document.getElementById('current_lat').value = pos.coords.latitude;
        document.getElementById('current_lng').value = pos.coords.longitude;
    }, null, { enableHighAccuracy: true });
}
</script>
@endpush
