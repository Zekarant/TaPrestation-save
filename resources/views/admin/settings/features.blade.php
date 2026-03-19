@extends('layouts.admin-modern')

@section('title', 'Visibilité des Fonctionnalités')
@section('page-title', 'Visibilité des Fonctionnalités')

@section('content')
<div class="page-header">
    <h1 class="page-title">🎛️ Visibilité des Fonctionnalités</h1>
    <p class="page-subtitle">Activez ou désactivez les fonctionnalités de paiement, d'abonnement et autres modules</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
@endif

{{-- Alerte d'information --}}
<div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fas fa-info-circle text-blue-600"></i>
        </div>
        <div>
            <h4 class="font-semibold text-blue-800">Comment ça fonctionne ?</h4>
            <p class="text-sm text-blue-700 mt-1">
                Désactivez une fonctionnalité pour la masquer complètement sur le site. 
                Les boutons, liens et interfaces associés ne seront plus visibles pour les utilisateurs.
                Vous pouvez les réactiver à tout moment.
            </p>
        </div>
    </div>
</div>

<form action="{{ route('admin.settings.features.update') }}" method="POST" id="featuresForm">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($features as $groupKey => $group)
            <div class="card-base p-6">
                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-200">
                    <span class="text-2xl">{{ $group['icon'] }}</span>
                    <h3 class="text-lg font-bold text-gray-900">{{ $group['label'] }}</h3>
                </div>

                <div class="space-y-4">
                    @foreach($group['features'] as $featureKey => $feature)
                        @php $inputName = 'feature_' . $featureKey; @endphp
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition cursor-pointer"
                             data-feature-toggle="{{ $inputName }}"
                             role="button"
                             tabindex="0"
                             aria-pressed="{{ $feature['enabled'] ? 'true' : 'false' }}">
                            <span class="text-gray-700 font-medium">{{ $feature['label'] }}</span>
                            <div class="relative">
                                {{-- Input caché qui stocke la vraie valeur --}}
                                <input type="hidden" 
                                       name="{{ $inputName }}" 
                                       id="input_{{ $inputName }}"
                                       value="{{ $feature['enabled'] ? '1' : '0' }}">
                                {{-- Toggle visuel --}}
                                <div id="track_{{ $inputName }}"
                                     style="width: 44px; height: 24px; border-radius: 12px; cursor: pointer; transition: background-color 0.2s; position: relative; {{ $feature['enabled'] ? 'background-color: #22c55e;' : 'background-color: #d1d5db;' }}">
                                    <div id="thumb_{{ $inputName }}"
                                         style="width: 20px; height: 20px; border-radius: 50%; background-color: white; position: absolute; top: 2px; transition: left 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); {{ $feature['enabled'] ? 'left: 22px;' : 'left: 2px;' }}"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Section d'aide --}}
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 bg-gradient-to-br from-orange-50 to-amber-50 rounded-xl border border-orange-200">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xl">💳</span>
                <h4 class="font-semibold text-orange-800">Paiements</h4>
            </div>
            <p class="text-sm text-orange-700">
                Désactivez les paiements pour passer en mode "catalogue" sans transaction en ligne.
            </p>
        </div>
        
        <div class="p-4 bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl border border-purple-200">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xl">🔄</span>
                <h4 class="font-semibold text-purple-800">Abonnements</h4>
            </div>
            <p class="text-sm text-purple-700">
                Activez les abonnements pour monétiser l'accès des prestataires à la plateforme.
            </p>
        </div>
        
        <div class="p-4 bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl border border-green-200">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xl">👨‍🍳</span>
                <h4 class="font-semibold text-green-800">Connexion Paiement</h4>
            </div>
            <p class="text-sm text-green-700">
                Permettez aux prestataires de connecter leur compte Stripe pour recevoir des paiements.
            </p>
        </div>
    </div>

    {{-- Bouton de sauvegarde --}}
    <div class="mt-8 flex justify-end">
        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5 flex items-center gap-2">
            <i class="fas fa-save"></i>
            Enregistrer les modifications
        </button>
    </div>
</form>

{{-- État actuel (résumé) --}}
<div class="mt-8 card-base p-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
        <i class="fas fa-chart-bar text-blue-600"></i>
        Résumé de l'état actuel
    </h3>
    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @php
            $enabledCount = 0;
            $disabledCount = 0;
            foreach($features as $group) {
                foreach($group['features'] as $feature) {
                    if($feature['enabled']) $enabledCount++;
                    else $disabledCount++;
                }
            }
        @endphp
        
        <div class="text-center p-4 bg-green-50 rounded-xl">
            <p class="text-3xl font-bold text-green-600">{{ $enabledCount }}</p>
            <p class="text-sm text-green-700">Fonctionnalités actives</p>
        </div>
        
        <div class="text-center p-4 bg-gray-100 rounded-xl">
            <p class="text-3xl font-bold text-gray-500">{{ $disabledCount }}</p>
            <p class="text-sm text-gray-600">Fonctionnalités désactivées</p>
        </div>
        
        <div class="text-center p-4 bg-blue-50 rounded-xl">
            <p class="text-3xl font-bold text-blue-600">{{ count($features) }}</p>
            <p class="text-sm text-blue-700">Catégories</p>
        </div>
        
        <div class="text-center p-4 bg-purple-50 rounded-xl">
            <p class="text-3xl font-bold text-purple-600">{{ $enabledCount + $disabledCount }}</p>
            <p class="text-sm text-purple-700">Total fonctionnalités</p>
        </div>
    </div>
</div>

<script>
function toggleSwitch(inputName) {
    const input = document.getElementById('input_' + inputName);
    const track = document.getElementById('track_' + inputName);
    const thumb = document.getElementById('thumb_' + inputName);
    const toggle = document.querySelector('[data-feature-toggle="' + inputName + '"]');

    if (!input || !track || !thumb) {
        return;
    }
    
    // Inverser la valeur
    const newValue = input.value === '1' ? '0' : '1';
    input.value = newValue;
    if (toggle) {
        toggle.setAttribute('aria-pressed', newValue === '1' ? 'true' : 'false');
    }
    
    // Mettre à jour l'apparence
    if (newValue === '1') {
        track.style.backgroundColor = '#22c55e'; // green-500
        thumb.style.left = '22px';
    } else {
        track.style.backgroundColor = '#d1d5db'; // gray-300
        thumb.style.left = '2px';
    }
    
    console.log(inputName + ' = ' + newValue);
}

document.querySelectorAll('[data-feature-toggle]').forEach(function(toggle) {
    toggle.addEventListener('click', function() {
        toggleSwitch(this.dataset.featureToggle);
    });

    toggle.addEventListener('keydown', function(event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            toggleSwitch(this.dataset.featureToggle);
        }
    });
});

// Debug: Afficher les données envoyées
document.getElementById('featuresForm').addEventListener('submit', function(e) {
    const formData = new FormData(this);
    console.log('=== DONNÉES DU FORMULAIRE ===');
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }
});
</script>
@endsection
