@extends('layouts.app')

@section('title', 'Recevoir mes paiements')

@section('content')
@php
    $hasStripe = $paymentsEnabled ?? false;
@endphp

<div class="min-h-screen bg-gradient-to-br from-green-50 via-white to-blue-50 py-4 px-3">
    <div class="max-w-lg mx-auto">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('prestataire.dashboard') }}" class="text-blue-600 hover:underline text-sm flex items-center gap-1">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <h1 class="text-lg font-bold text-gray-800">💰 Paiements</h1>
            <div></div>
        </div>

        {{-- Messages --}}
        @if(session('success'))
            <div class="mb-3 p-3 bg-green-100 border-l-4 border-green-500 text-green-800 rounded text-sm">✓ {{ session('success') }}</div>
        @endif
        @if(session('error') || !empty($error))
            <div class="mb-3 p-3 bg-red-100 border-l-4 border-red-500 text-red-800 rounded text-sm">{{ session('error') ?? $error }}</div>
        @endif
        @if($errors->any())
            <div class="mb-3 p-3 bg-red-100 border-l-4 border-red-500 text-red-800 rounded text-sm">
                @foreach($errors->all() as $err)<p>{{ $err }}</p>@endforeach
            </div>
        @endif

        {{-- Résumé compact --}}
        @php
            $internalBalance = $prestataire->balance ?? 0;
            $totalBalance = ($totalEarnings ?? 0) + $internalBalance;
        @endphp
        
        @if(($paymentsEnabled ?? false) || $internalBalance > 0)
            <div class="mb-4 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl p-3 shadow-lg">
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle text-xl"></i>
                        <span class="font-medium text-sm">{{ ($paymentsEnabled ?? false) ? 'Paiements activés' : 'Solde disponible' }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-lg font-bold">{{ number_format($totalBalance, 2) }} €</span>
                        @if($internalBalance > 0 && ($totalEarnings ?? 0) > 0)
                            <p class="text-[10px] text-green-100">TaPrestation: {{ number_format($internalBalance, 2) }}€ + Stripe: {{ number_format($totalEarnings, 2) }}€</p>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="mb-4 bg-gradient-to-r from-orange-500 to-amber-500 rounded-xl p-3 shadow-lg">
                <div class="flex items-center gap-2 text-white">
                    <i class="fas fa-exclamation-triangle animate-pulse"></i>
                    <span class="font-medium text-sm">Configurez au moins une méthode</span>
                </div>
            </div>
        @endif

        {{-- 3 ONGLETS ALIGNÉS --}}
        <div class="flex gap-2 mb-4">
            {{-- Onglet Stripe --}}
            <button onclick="showTab('stripe')" id="tab-stripe"
                class="flex-1 py-3 px-2 rounded-xl text-center transition-all duration-200 border-2
                {{ $hasStripe ? 'border-green-400' : 'border-purple-300' }}
                bg-white shadow-md tab-button active"
                data-active-class="{{ $hasStripe ? 'bg-green-50 border-green-500' : 'bg-purple-50 border-purple-500' }}">
                <div class="text-2xl mb-1">💳</div>
                <div class="text-xs font-bold text-gray-800">Carte</div>
                @if($hasStripe)
                    <div class="text-[10px] text-green-600 font-medium">✓ Actif</div>
                @else
                    <div class="text-[10px] text-purple-600 font-medium">★ Recommandé</div>
                @endif
            </button>
        </div>

        {{-- CONTENU DES ONGLETS --}}
        
        {{-- CONTENU STRIPE --}}
        <div id="content-stripe" class="tab-content bg-white rounded-xl shadow-lg border-2 {{ $hasStripe ? 'border-green-400' : 'border-purple-300' }} overflow-hidden">
            {{-- Header avec bouton info --}}
            <div class="px-4 py-3 {{ $hasStripe ? 'bg-green-50' : 'bg-purple-50' }} border-b flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xl">💳</span>
                    <span class="font-bold text-gray-800">Carte bancaire (Stripe)</span>
                </div>
                <button onclick="openModal('stripeModal')" class="w-7 h-7 bg-purple-100 hover:bg-purple-200 rounded-full text-purple-600 flex items-center justify-center transition">
                    <i class="fas fa-question text-xs"></i>
                </button>
            </div>
            
            <div class="p-4">
                @if($hasStripe)
                    <div class="space-y-3">
                        {{-- Balance interne TaPrestation (escrow, ventes urgentes, etc.) --}}
                        @if(($prestataire->balance ?? 0) > 0)
                        <div class="bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg p-4 text-center shadow-lg mb-3">
                            <p class="text-2xl font-bold text-white">{{ number_format($prestataire->balance ?? 0, 2) }} €</p>
                            <p class="text-xs text-green-100">💰 Solde TaPrestation</p>
                            <p class="text-[10px] text-green-200 mt-1">Ventes urgentes, escrow confirmés</p>
                        </div>
                        @endif
                        
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-green-50 rounded-lg p-3 text-center">
                                <p class="text-lg font-bold text-gray-900">{{ number_format($totalEarnings ?? 0, 2) }} €</p>
                                <p class="text-xs text-gray-500">Stripe disponible</p>
                            </div>
                            <div class="bg-{{ ($escrowPending ?? 0) > 0 ? 'yellow' : 'gray' }}-50 rounded-lg p-3 text-center">
                                <p class="text-lg font-bold text-gray-900">{{ number_format($pendingBalance ?? 0, 2) }} €</p>
                                <p class="text-xs text-gray-500">En attente</p>
                                @if(($escrowPending ?? 0) > 0)
                                    <p class="text-[10px] text-yellow-600">dont {{ number_format($escrowPending, 2) }}€ escrow</p>
                                @endif
                            </div>
                        </div>
                        
                        <a href="{{ route('prestataire.payments.dashboard') }}" 
                           class="block w-full py-2.5 bg-blue-500 hover:bg-blue-600 text-white text-center text-sm font-medium rounded-lg transition">
                            📊 Voir mes paiements
                        </a>
                        <a href="{{ route('prestataire.payments.stripe.dashboard') }}" 
                           class="block w-full py-2.5 bg-purple-500 hover:bg-purple-600 text-white text-center text-sm font-medium rounded-lg transition">
                            ⚙️ Gérer Stripe
                        </a>
                    </div>
                @else
                    <div class="space-y-4">
                        <div class="text-sm text-gray-600 space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="text-green-500">✓</span>
                                <span>Paiements par carte bancaire</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-green-500">✓</span>
                                <span>Virements automatiques sur votre compte</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-green-500">✓</span>
                                <span>Commission plateforme + frais Stripe déduits</span>
                            </div>
                        </div>
                        
                        @if(!empty($onboardingUrl))
                            <a href="{{ $onboardingUrl }}" 
                               class="block w-full py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white text-center font-bold rounded-lg transition shadow-lg">
                                🚀 Activer Stripe
                            </a>
                            <p class="text-xs text-gray-500 text-center">⏱️ 2 min : IBAN + pièce d'identité</p>
                        @else
                            <a href="{{ route('prestataire.payments.connect') }}" 
                               class="block w-full py-2.5 bg-purple-500 text-white text-center text-sm font-medium rounded-lg">
                                🔄 Réessayer
                            </a>
                        @endif
                    </div>
                @endif
                
                @if($onboardingPending ?? false)
                    <div class="mt-3 p-2 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-xs text-yellow-800">
                            <i class="fas fa-clock mr-1"></i>
                            @if($requiresMoreInfo ?? false)
                                Informations à compléter
                            @else
                                Vérification en cours...
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
        
        {{-- Info sécurité --}}
        <div class="mt-4 p-3 bg-white/80 rounded-lg border border-gray-200">
            <p class="text-xs text-gray-500 text-center">
                🔒 Vos données sont sécurisées et chiffrées
            </p>
        </div>
        
    </div>
</div>

{{-- Modal Stripe --}}
<div id="stripeModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4" onclick="closeModal('stripeModal')">
    <div class="bg-white rounded-2xl max-w-sm w-full shadow-2xl" onclick="event.stopPropagation()">
        <div class="bg-gradient-to-r from-purple-600 to-blue-600 px-4 py-3 rounded-t-2xl">
            <div class="flex items-center justify-between text-white">
                <h3 class="font-bold">💳 Comment ça marche ?</h3>
                <button onclick="closeModal('stripeModal')" class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>
        <div class="p-4 space-y-3">
            <div class="flex gap-3">
                <div class="w-7 h-7 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-bold text-sm flex-shrink-0">1</div>
                <div>
                    <p class="font-semibold text-gray-800 text-sm">Le client paie par carte</p>
                    <p class="text-xs text-gray-600">Via notre plateforme sécurisée</p>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="w-7 h-7 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-bold text-sm flex-shrink-0">2</div>
                <div>
                    <p class="font-semibold text-gray-800 text-sm">L'argent est sécurisé</p>
                    <p class="text-xs text-gray-600">Jusqu'à la fin de la prestation</p>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="w-7 h-7 bg-green-100 rounded-full flex items-center justify-center text-green-600 font-bold text-sm flex-shrink-0">3</div>
                <div>
                    <p class="font-semibold text-gray-800 text-sm">Virement automatique</p>
                    <p class="text-xs text-gray-600">Sur votre compte (commission plateforme + frais Stripe déduits)</p>
                </div>
            </div>
            <div class="bg-purple-50 rounded-lg p-2 text-xs text-purple-800">
                <i class="fas fa-star mr-1"></i>
                <strong>Recommandé</strong> : Le plus simple et sécurisé !
            </div>
        </div>
    </div>
</div>


<script>
// Gestion des onglets
function showTab(tabName) {
    // Cacher tous les contenus
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    
    // Réinitialiser tous les onglets
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('ring-2', 'ring-offset-2', 'scale-105');
    });
    
    // Afficher le contenu sélectionné
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Mettre en surbrillance l'onglet sélectionné
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.add('ring-2', 'ring-offset-2', 'scale-105');
}

// Afficher Stripe par défaut
document.addEventListener('DOMContentLoaded', function() {
    showTab('stripe');
});

// Modals
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.getElementById(id).classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.getElementById(id).classList.remove('flex');
    document.body.style.overflow = 'auto';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        ['stripeModal'].forEach(id => closeModal(id));
    }
});
</script>
@endsection
