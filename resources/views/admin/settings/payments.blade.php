@extends('layouts.admin-modern')

@section('title', 'Paramètres de paiement')
@section('page-title', 'Paramètres de paiement')

@push('styles')
<style>
    .payment-provider-card {
        transition: all 0.3s ease;
    }
    .payment-provider-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .toggle-switch {
        position: relative;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-slider {
        width: 48px;
        height: 26px;
        background-color: #e5e7eb;
        border-radius: 26px;
        position: relative;
        transition: all 0.3s ease;
    }
    .toggle-slider::before {
        content: '';
        position: absolute;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: white;
        top: 2px;
        left: 2px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .toggle-switch input:checked + .toggle-slider {
        background: linear-gradient(135deg, #10b981, #059669);
    }
    .toggle-switch input:checked + .toggle-slider::before {
        transform: translateX(22px);
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-active {
        background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        color: #047857;
    }
    .status-inactive {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #dc2626;
    }
    .status-pending {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #d97706;
    }
</style>
@endpush

@section('content')
<div class="page-header mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title text-3xl font-bold text-gray-900">💳 Configuration des Paiements</h1>
            <p class="page-subtitle text-gray-600 mt-2">Gérez les moyens de paiement de votre plateforme</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.payments.transactions') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition flex items-center gap-2">
                <i class="fas fa-history"></i>
                <span>Transactions</span>
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center gap-3">
        <i class="fas fa-check-circle text-green-500"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center gap-3">
        <i class="fas fa-exclamation-circle text-red-500"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- Statistiques rapides --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-100 text-sm">Total Encaissé</p>
                <p class="text-3xl font-bold mt-1">{{ number_format($stats['total_collected'] ?? 0, 2) }} €</p>
            </div>
            <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-euro-sign text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm">Ce mois</p>
                <p class="text-3xl font-bold mt-1">{{ number_format($stats['this_month'] ?? 0, 2) }} €</p>
            </div>
            <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-calendar-alt text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-purple-100 text-sm">Commissions</p>
                <p class="text-3xl font-bold mt-1">{{ number_format($stats['commissions'] ?? 0, 2) }} €</p>
            </div>
            <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-percentage text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-orange-100 text-sm">En attente</p>
                <p class="text-3xl font-bold mt-1">{{ $stats['pending_count'] ?? 0 }}</p>
            </div>
            <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-clock text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('admin.settings.payments.update') }}" method="POST" id="payment-settings-form">
    @csrf
    @method('PUT')

    {{-- Onglets --}}
    <div class="mb-6 border-b border-gray-200">
        <nav class="flex gap-6 overflow-x-auto" aria-label="Tabs">
            <button type="button" onclick="showTab('providers')" id="tab-providers" class="tab-btn active py-4 px-1 border-b-2 border-blue-500 font-medium text-blue-600 whitespace-nowrap">
                <i class="fas fa-credit-card mr-2"></i>Moyens de paiement
            </button>
            <button type="button" onclick="showTab('settings')" id="tab-settings" class="tab-btn py-4 px-1 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700 whitespace-nowrap">
                <i class="fas fa-cog mr-2"></i>Paramètres généraux
            </button>
            <button type="button" onclick="showTab('deposits')" id="tab-deposits" class="tab-btn py-4 px-1 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700 whitespace-nowrap">
                <i class="fas fa-hand-holding-usd mr-2"></i>Acomptes & Cautions
            </button>
            <button type="button" onclick="showTab('commissions')" id="tab-commissions" class="tab-btn py-4 px-1 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700 whitespace-nowrap">
                <i class="fas fa-percent mr-2"></i>Commissions
            </button>
            <button type="button" onclick="showTab('escrow')" id="tab-escrow" class="tab-btn py-4 px-1 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700 whitespace-nowrap">
                <i class="fas fa-shield-alt mr-2"></i>🔒 Escrow (Blocage)
            </button>
        </nav>
    </div>

    {{-- Tab: Moyens de paiement --}}
    <div id="panel-providers" class="tab-panel">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            {{-- Stripe --}}
            <div class="payment-provider-card bg-white rounded-2xl border-2 border-gray-100 p-6 shadow-sm">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="fab fa-stripe-s text-white text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Stripe</h3>
                            <p class="text-gray-500 text-sm">Cartes bancaires, Apple Pay, Google Pay</p>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="stripe_enabled" value="1" {{ ($paymentSettings['stripe_enabled'] ?? false) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div class="space-y-4" id="stripe-config">
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-700">
                        Les identifiants Stripe ne sont plus stockes en base de donnees.
                        Renseignez-les uniquement dans le `.env` ou via la configuration serveur, puis videz le cache config.
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-key text-purple-500 mr-1"></i> Clé publique (Publishable key)
                        </label>
                        <input type="text" value="{{ $stripeConfig['publishable_key_masked'] ?? 'Non configuree' }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-gray-600"
                            readonly>
                        <p class="text-xs text-gray-500 mt-1">Valeur lue depuis `STRIPE_KEY`.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock text-purple-500 mr-1"></i> Clé secrète (Secret key)
                        </label>
                        <input type="text"
                            value="{{ $stripeConfig['secret_key_masked'] ?? 'Non configuree' }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-gray-600"
                            readonly>
                        <p class="text-xs text-gray-500 mt-1">
                            @if($stripeConfig['secret_key_configured'] ?? false)
                                Cle configuree via `STRIPE_SECRET`.
                            @else
                                Aucune cle secrete Stripe configuree.
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-globe text-purple-500 mr-1"></i> Webhook Secret
                        </label>
                        <input type="text"
                            value="{{ $stripeConfig['webhook_secret_masked'] ?? 'Non configure' }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 text-gray-600"
                            readonly>
                        <p class="text-xs text-gray-500 mt-1">URL du webhook : {{ url('/webhook/stripe') }}</p>
                    </div>
                    
                    {{-- Options Stripe --}}
                    <div class="pt-4 border-t border-gray-100">
                        <p class="text-sm font-medium text-gray-700 mb-3">Options de paiement Stripe</p>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                                <input type="checkbox" name="stripe_apple_pay" value="1" {{ ($paymentSettings['stripe_apple_pay'] ?? true) ? 'checked' : '' }} class="w-4 h-4 text-purple-600 rounded">
                                <i class="fab fa-apple text-gray-700"></i>
                                <span class="text-sm text-gray-700">Apple Pay</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                                <input type="checkbox" name="stripe_google_pay" value="1" {{ ($paymentSettings['stripe_google_pay'] ?? true) ? 'checked' : '' }} class="w-4 h-4 text-purple-600 rounded">
                                <i class="fab fa-google text-gray-700"></i>
                                <span class="text-sm text-gray-700">Google Pay</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Stripe Connect --}}
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h4 class="font-semibold text-gray-900">Stripe Connect</h4>
                            <p class="text-xs text-gray-500">Paiements automatiques aux prestataires</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="stripe_connect_enabled" value="1" {{ ($paymentSettings['stripe_connect_enabled'] ?? false) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Commission plateforme (%)</label>
                            <input type="number" name="stripe_platform_fee" value="{{ $paymentSettings['stripe_platform_fee'] ?? 5 }}" step="0.1" min="0" max="50"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Délai virement (jours)</label>
                            <input type="number" name="stripe_payout_delay" value="{{ $paymentSettings['stripe_payout_delay'] ?? 7 }}" min="0" max="30"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paiement sur place --}}
            <div class="payment-provider-card bg-white rounded-2xl border-2 border-gray-100 p-6 shadow-sm">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-amber-500 rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-hand-holding-usd text-white text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Paiement sur place</h3>
                            <p class="text-gray-500 text-sm">Espèces, Chèque, CB au prestataire</p>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="cash_payment_enabled" value="1" {{ ($paymentSettings['cash_payment_enabled'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-amber-500 mt-0.5"></i>
                        <div class="text-sm text-amber-700">
                            <p class="font-medium mb-1">Mode "Paiement différé"</p>
                            <p>Le client paie directement le prestataire lors de la prestation. La plateforme ne prend pas de commission sur ce mode.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab: Paramètres généraux --}}
    <div id="panel-settings" class="tab-panel hidden">
        <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
            <h3 class="text-xl font-bold text-gray-900 mb-6">
                <i class="fas fa-cog text-gray-400 mr-2"></i>Paramètres généraux
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Devise principale</label>
                    <select name="currency" class="w-full px-4 py-3 border border-gray-200 rounded-xl">
                        <option value="EUR" {{ ($paymentSettings['currency'] ?? 'EUR') == 'EUR' ? 'selected' : '' }}>🇪🇺 EUR (€)</option>
                        <option value="USD" {{ ($paymentSettings['currency'] ?? '') == 'USD' ? 'selected' : '' }}>🇺🇸 USD ($)</option>
                        <option value="GBP" {{ ($paymentSettings['currency'] ?? '') == 'GBP' ? 'selected' : '' }}>🇬🇧 GBP (£)</option>
                        <option value="MAD" {{ ($paymentSettings['currency'] ?? '') == 'MAD' ? 'selected' : '' }}>🇲🇦 MAD (DH)</option>
                        <option value="XOF" {{ ($paymentSettings['currency'] ?? '') == 'XOF' ? 'selected' : '' }}>🌍 XOF (CFA)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Montant minimum de paiement (€)</label>
                    <input type="number" name="min_payment_amount" step="0.01" value="{{ $paymentSettings['min_payment_amount'] ?? 1 }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Retrait minimum prestataire (€)</label>
                    <input type="number" name="min_withdrawal" step="0.01" value="{{ $paymentSettings['min_withdrawal'] ?? 50 }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl">
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100">
                <h4 class="font-semibold text-gray-900 mb-4">Options de paiement</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                        <input type="checkbox" name="require_payment_before_booking" value="1" 
                            {{ ($paymentSettings['require_payment_before_booking'] ?? false) ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 rounded">
                        <div>
                            <span class="font-medium text-gray-900">Paiement obligatoire avant réservation</span>
                            <p class="text-sm text-gray-500">Le client doit payer pour confirmer sa réservation</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                        <input type="checkbox" name="allow_partial_payment" value="1" 
                            {{ ($paymentSettings['allow_partial_payment'] ?? true) ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 rounded">
                        <div>
                            <span class="font-medium text-gray-900">Autoriser le paiement partiel (acompte)</span>
                            <p class="text-sm text-gray-500">Permet de payer un acompte puis le solde</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                        <input type="checkbox" name="auto_refund_on_cancel" value="1" 
                            {{ ($paymentSettings['auto_refund_on_cancel'] ?? false) ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 rounded">
                        <div>
                            <span class="font-medium text-gray-900">Remboursement automatique à l'annulation</span>
                            <p class="text-sm text-gray-500">Rembourser automatiquement si annulation autorisée</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                        <input type="checkbox" name="send_payment_receipts" value="1" 
                            {{ ($paymentSettings['send_payment_receipts'] ?? true) ? 'checked' : '' }}
                            class="w-5 h-5 text-blue-600 rounded">
                        <div>
                            <span class="font-medium text-gray-900">Envoyer les reçus par email</span>
                            <p class="text-sm text-gray-500">Email de confirmation après chaque paiement</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab: Acomptes & Cautions --}}
    <div id="panel-deposits" class="tab-panel hidden">
        <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
            <h3 class="text-xl font-bold text-gray-900 mb-6">
                <i class="fas fa-hand-holding-usd text-green-500 mr-2"></i>Gestion des Acomptes & Cautions
            </h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Acomptes --}}
                <div class="p-6 bg-blue-50 rounded-2xl border border-blue-200">
                    <h4 class="font-bold text-blue-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-wallet"></i>
                        Acomptes (Réservations)
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-blue-800 mb-2">Pourcentage d'acompte par défaut (%)</label>
                            <input type="number" name="default_deposit_percent" value="{{ $paymentSettings['default_deposit_percent'] ?? 30 }}" 
                                min="0" max="100" step="5"
                                class="w-full px-4 py-3 border border-blue-300 rounded-xl bg-white">
                            <p class="text-xs text-blue-600 mt-1">Ex: 30% = le client paie 30% à la réservation, 70% avant la prestation</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-blue-800 mb-2">Montant minimum d'acompte (€)</label>
                            <input type="number" name="min_deposit_amount" value="{{ $paymentSettings['min_deposit_amount'] ?? 10 }}" 
                                min="0" step="1"
                                class="w-full px-4 py-3 border border-blue-300 rounded-xl bg-white">
                        </div>
                        <label class="flex items-center gap-3 p-3 bg-white rounded-xl cursor-pointer">
                            <input type="checkbox" name="prestataire_can_set_deposit" value="1" 
                                {{ ($paymentSettings['prestataire_can_set_deposit'] ?? true) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 rounded">
                            <span class="text-sm text-gray-700">Permettre aux prestataires de définir leur propre acompte</span>
                        </label>
                    </div>
                </div>

                {{-- Cautions --}}
                <div class="p-6 bg-orange-50 rounded-2xl border border-orange-200">
                    <h4 class="font-bold text-orange-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-shield-alt"></i>
                        Cautions (Locations)
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-orange-800 mb-2">Caution par défaut pour équipements (€)</label>
                            <input type="number" name="default_security_deposit" value="{{ $paymentSettings['default_security_deposit'] ?? 100 }}" 
                                min="0" step="10"
                                class="w-full px-4 py-3 border border-orange-300 rounded-xl bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-orange-800 mb-2">Délai de restitution caution (jours)</label>
                            <input type="number" name="deposit_refund_delay" value="{{ $paymentSettings['deposit_refund_delay'] ?? 7 }}" 
                                min="0" max="30"
                                class="w-full px-4 py-3 border border-orange-300 rounded-xl bg-white">
                            <p class="text-xs text-orange-600 mt-1">Après restitution du matériel sans dommage</p>
                        </div>
                        <label class="flex items-center gap-3 p-3 bg-white rounded-xl cursor-pointer">
                            <input type="checkbox" name="hold_deposit_instead_of_charge" value="1" 
                                {{ ($paymentSettings['hold_deposit_instead_of_charge'] ?? true) ? 'checked' : '' }}
                                class="w-4 h-4 text-orange-600 rounded">
                            <span class="text-sm text-gray-700">Pré-autorisation (bloquer sans débiter) pour les cautions</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab: Commissions --}}
    <div id="panel-commissions" class="tab-panel hidden">
        <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
            <h3 class="text-xl font-bold text-gray-900 mb-6">
                <i class="fas fa-percent text-purple-500 mr-2"></i>Commissions de la plateforme
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="p-6 bg-purple-50 rounded-2xl border border-purple-200">
                    <label class="block text-sm font-medium text-purple-800 mb-2">Commission Services (%)</label>
                    <input type="number" name="commission_services" value="{{ $paymentSettings['commission_services'] ?? 10 }}" 
                        min="0" max="50" step="0.5"
                        class="w-full px-4 py-3 border border-purple-300 rounded-xl bg-white text-lg font-bold text-center">
                    <p class="text-xs text-purple-600 mt-2 text-center">Sur chaque prestation de service</p>
                </div>
                <div class="p-6 bg-green-50 rounded-2xl border border-green-200">
                    <label class="block text-sm font-medium text-green-800 mb-2">Commission Locations (%)</label>
                    <input type="number" name="commission_rentals" value="{{ $paymentSettings['commission_rentals'] ?? 8 }}" 
                        min="0" max="50" step="0.5"
                        class="w-full px-4 py-3 border border-green-300 rounded-xl bg-white text-lg font-bold text-center">
                    <p class="text-xs text-green-600 mt-2 text-center">Sur chaque location d'équipement</p>
                </div>
                <div class="p-6 bg-orange-50 rounded-2xl border border-orange-200">
                    <label class="block text-sm font-medium text-orange-800 mb-2">Commission Food (%)</label>
                    <input type="number" name="commission_food" value="{{ $paymentSettings['commission_food'] ?? 15 }}" 
                        min="0" max="50" step="0.5"
                        class="w-full px-4 py-3 border border-orange-300 rounded-xl bg-white text-lg font-bold text-center">
                    <p class="text-xs text-orange-600 mt-2 text-center">Sur chaque commande food</p>
                </div>
            </div>

            <div class="p-4 bg-gray-50 rounded-xl">
                <h4 class="font-semibold text-gray-900 mb-3">Réductions selon abonnement</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="text-center p-3 bg-white rounded-lg">
                        <span class="text-sm text-gray-500">Plan Gratuit</span>
                        <p class="font-bold text-lg">Commission complète</p>
                    </div>
                    <div class="text-center p-3 bg-indigo-50 rounded-lg border border-indigo-200">
                        <span class="text-sm text-indigo-600">Plan Pro</span>
                        <p class="font-bold text-lg text-indigo-700">-5% sur commission</p>
                    </div>
                    <div class="text-center p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                        <span class="text-sm text-yellow-600">Plan Premium</span>
                        <p class="font-bold text-lg text-yellow-700">-10% sur commission</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab: Escrow (Blocage Paiements) --}}
    <div id="panel-escrow" class="tab-panel hidden">
        <div class="bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-lock text-white text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900">🔒 Système Escrow - Protection des Paiements</h3>
                    <p class="text-gray-500">L'argent est bloqué par Stripe jusqu'à confirmation de la prestation</p>
                </div>
            </div>

            {{-- Activation --}}
            <div class="mb-8 p-6 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-amber-900 text-lg">Activer le système Escrow</h4>
                        <p class="text-amber-700 text-sm mt-1">Les paiements seront bloqués jusqu'à confirmation</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="escrow_enabled" value="1" {{ (get_setting('escrow_enabled', '1') == '1') ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Commission Escrow --}}
                <div class="p-6 bg-purple-50 rounded-2xl border border-purple-200">
                    <h4 class="font-bold text-purple-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-percent"></i>
                        Commission sur transactions Escrow
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-purple-800 mb-2">Commission plateforme (%)</label>
                            <input type="number" name="escrow_commission_rate" 
                                value="{{ get_setting('escrow_commission_rate', '20') }}" 
                                min="0" max="50" step="0.5"
                                class="w-full px-4 py-3 border border-purple-300 rounded-xl bg-white text-2xl font-bold text-center text-purple-700">
                            <p class="text-xs text-purple-600 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Cette commission est prélevée lors de la libération des fonds au prestataire.
                                Elle s'affiche aux prestataires lors de la création d'annonces.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Délai auto-release --}}
                <div class="p-6 bg-blue-50 rounded-2xl border border-blue-200">
                    <h4 class="font-bold text-blue-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-clock"></i>
                        Libération automatique
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-blue-800 mb-2">Délai auto-libération (heures)</label>
                            <input type="number" name="escrow_auto_release_hours" 
                                value="{{ get_setting('escrow_auto_release_hours', '48') }}" 
                                min="12" max="168" step="1"
                                class="w-full px-4 py-3 border border-blue-300 rounded-xl bg-white text-2xl font-bold text-center text-blue-700">
                            <p class="text-xs text-blue-600 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Si le client ne confirme pas, les fonds sont libérés automatiquement après ce délai.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Montant minimum --}}
                <div class="p-6 bg-green-50 rounded-2xl border border-green-200">
                    <h4 class="font-bold text-green-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-euro-sign"></i>
                        Montants
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-green-800 mb-2">Montant minimum pour escrow (€)</label>
                            <input type="number" name="escrow_min_amount" 
                                value="{{ get_setting('escrow_min_amount', '5') }}" 
                                min="0" max="100" step="1"
                                class="w-full px-4 py-3 border border-green-300 rounded-xl bg-white">
                            <p class="text-xs text-green-600 mt-2">En dessous de ce montant, pas de blocage escrow</p>
                        </div>
                    </div>
                </div>

                {{-- Litiges --}}
                <div class="p-6 bg-red-50 rounded-2xl border border-red-200">
                    <h4 class="font-bold text-red-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-gavel"></i>
                        Litiges
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-red-800 mb-2">Délai max pour ouvrir un litige (jours)</label>
                            <input type="number" name="escrow_max_dispute_days" 
                                value="{{ get_setting('escrow_max_dispute_days', '30') }}" 
                                min="1" max="90" step="1"
                                class="w-full px-4 py-3 border border-red-300 rounded-xl bg-white">
                            <p class="text-xs text-red-600 mt-2">Après ce délai, aucun litige ne peut être ouvert</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Récapitulatif des règles --}}
            <div class="mt-8 p-6 bg-gray-50 rounded-2xl border border-gray-200">
                <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-list-check"></i>
                    Règles actuelles du système Escrow
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="p-4 bg-white rounded-xl border">
                        <p class="font-bold text-blue-700 mb-2">🎯 Services</p>
                        <ul class="text-gray-600 space-y-1">
                            <li>✅ Service rendu → Client confirme OU {{ get_setting('escrow_auto_release_hours', '48') }}h → Presta payé</li>
                            <li>❌ Qualité insuffisante mais fait → Pas de remboursement</li>
                            <li>↩️ Annulation dans délai → Selon règles presta</li>
                        </ul>
                    </div>
                    <div class="p-4 bg-white rounded-xl border">
                        <p class="font-bold text-green-700 mb-2">🛠️ Équipement</p>
                        <ul class="text-gray-600 space-y-1">
                            <li>✅ Retour bon état → Location au presta, caution au client</li>
                            <li>⚠️ Dégât → Presta garde tout/partie caution</li>
                            <li>↩️ Annulation dans délai → Remboursement 100%</li>
                        </ul>
                    </div>
                    <div class="p-4 bg-white rounded-xl border">
                        <p class="font-bold text-red-700 mb-2">📦 Vente Urgente</p>
                        <ul class="text-gray-600 space-y-1">
                            <li>✅ Client confirme → Presta payé immédiatement</li>
                            <li>⏰ Client absent → {{ get_setting('escrow_auto_release_hours', '48') }}h auto-paiement</li>
                            <li>⚠️ Non conforme → Remboursement partiel %</li>
                        </ul>
                    </div>
                </div>
                <p class="text-center text-gray-500 mt-4">
                    <i class="fas fa-star text-yellow-500 mr-1"></i>
                    Commission actuelle : <strong class="text-purple-700">{{ get_setting('escrow_commission_rate', '20') }}%</strong> prélevée sur chaque transaction
                </p>
            </div>
        </div>
    </div>

    {{-- Bouton de sauvegarde flottant --}}
    <div class="fixed bottom-6 right-6 z-50">
        <button type="submit" class="px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-2xl font-bold shadow-2xl hover:shadow-xl transform hover:scale-105 transition-all flex items-center gap-3">
            <i class="fas fa-save"></i>
            <span>Enregistrer les modifications</span>
        </button>
    </div>
</form>

@endsection

@push('scripts')
<script>
    function showTab(tabName) {
        // Cacher tous les panels
        document.querySelectorAll('.tab-panel').forEach(panel => {
            panel.classList.add('hidden');
        });
        
        // Désactiver tous les boutons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500');
        });
        
        // Afficher le panel sélectionné
        document.getElementById('panel-' + tabName).classList.remove('hidden');
        
        // Activer le bouton sélectionné
        const activeBtn = document.getElementById('tab-' + tabName);
        activeBtn.classList.add('active', 'border-blue-500', 'text-blue-600');
        activeBtn.classList.remove('border-transparent', 'text-gray-500');
    }
</script>
@endpush
