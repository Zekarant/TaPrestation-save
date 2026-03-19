@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    /* Autocomplete dropdown styles */
    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        max-height: 280px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        margin-top: 4px;
    }
    
    .autocomplete-item {
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: background-color 0.15s ease;
    }
    
    .autocomplete-item:last-child {
        border-bottom: none;
    }
    
    .autocomplete-item:hover {
        background-color: #f9fafb;
    }
</style>
@endpush

@section('content')
@php
    $client = auth()->user()->client;
    $hasPhone = $client && !empty($client->phone);
    $hasAddress = $client && !empty($client->address);
    $hasBio = $client && !empty($client->bio) && strlen($client->bio) >= 20;
    $isProfileComplete = $hasPhone && $hasAddress;
    $isGoogleUser = auth()->user()->isSocialAccount();
@endphp

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-4 px-3">
    <div class="max-w-6xl mx-auto">
        
        {{-- Alerte profil incomplet - TRÈS VISIBLE --}}
        @if(!$isProfileComplete)
            <div class="mb-4 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl p-4 shadow-lg">
                <div class="flex items-center gap-3 text-white">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center animate-pulse">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-lg">⚠️ Profil incomplet !</p>
                        <p class="text-white/90 text-sm">
                            Complétez votre profil pour effectuer des réservations :
                            @if(!$hasPhone) <span class="bg-white/20 px-2 py-0.5 rounded">📱 Téléphone</span> @endif
                            @if(!$hasAddress) <span class="bg-white/20 px-2 py-0.5 rounded">📍 Adresse</span> @endif
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="mb-4 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl p-3 shadow-lg">
                <div class="flex items-center gap-3 text-white">
                    <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-check text-lg"></i>
                    </div>
                    <p class="font-medium">✅ Profil complet - Vous pouvez effectuer des réservations !</p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-3 rounded">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @if (session('success'))
            <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="mb-4 bg-amber-50 border-l-4 border-amber-500 text-amber-700 p-3 rounded">
                {{ session('warning') }}
            </div>
        @endif

        {{-- Layout en 2 colonnes --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            
            {{-- COLONNE 1 : Identité --}}
            <div class="bg-white rounded-xl shadow-md border-2 {{ ($hasPhone && $hasAddress) ? 'border-green-400' : 'border-orange-300' }} overflow-hidden">
                <div class="px-4 py-3 {{ ($hasPhone && $hasAddress) ? 'bg-green-50' : 'bg-orange-50' }} border-b flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 {{ ($hasPhone && $hasAddress) ? 'bg-green-500' : 'bg-orange-500' }} rounded-lg flex items-center justify-center">
                            @if($hasPhone && $hasAddress)
                                <i class="fas fa-check text-white"></i>
                            @else
                                <i class="fas fa-user text-white"></i>
                            @endif
                        </div>
                        <h3 class="font-bold text-gray-800">👤 Mon Identité</h3>
                    </div>
                    @if($hasPhone && $hasAddress)
                        <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full font-medium">✓ Complet</span>
                    @endif
                </div>
                
                <form action="{{ route('client.profile.update.personal') }}" method="POST" enctype="multipart/form-data" class="p-4">
                    @csrf
                    @method('PUT')
                    
                    {{-- Photo + Nom en ligne --}}
                    <div class="flex items-center gap-3 mb-4">
                        <div class="relative">
                            @if($client && $client->photo)
                                <img class="h-14 w-14 rounded-full object-cover border-2 border-blue-300" src="{{ asset('storage/' . $client->photo) }}" alt="{{ auth()->user()->name ?? 'Photo de profil' }}">
                            @else
                                <div class="h-14 w-14 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white text-xl font-bold">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                            @endif
                            <label class="absolute -bottom-1 -right-1 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center cursor-pointer hover:bg-blue-600">
                                <i class="fas fa-camera text-white text-xs"></i>
                                <input type="file" name="photo" class="hidden" accept="image/*">
                            </label>
                        </div>
                        <div class="flex-1">
                            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" 
                                class="w-full text-lg font-semibold border-0 border-b-2 border-gray-200 focus:border-blue-500 focus:ring-0 px-0 py-1" required>
                            <p class="text-xs text-gray-500 flex items-center gap-1">
                                {{ auth()->user()->email }}
                                @if(auth()->user()->email_verified_at)
                                    <i class="fas fa-check-circle text-green-500"></i>
                                @endif
                                @if($isGoogleUser)
                                    <img src="https://www.google.com/favicon.ico" alt="" class="w-3 h-3">
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                    
                    {{-- Téléphone --}}
                    <div class="mb-3">
                        <label class="text-sm font-medium text-gray-700 flex items-center gap-1">
                            📱 Téléphone
                            @if($hasPhone) <i class="fas fa-check-circle text-green-500 text-xs"></i> @else <span class="text-red-500">*</span> @endif
                        </label>
                        <input type="tel" name="phone" value="{{ old('phone', $client->phone ?? '') }}" 
                            placeholder="06 12 34 56 78"
                            class="mt-1 w-full rounded-lg border {{ $hasPhone ? 'border-green-300 bg-green-50' : 'border-orange-300 bg-orange-50' }} px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    {{-- Adresse --}}
                    <div class="mb-3">
                        <label class="text-sm font-medium text-gray-700 flex items-center gap-1">
                            📍 Adresse
                            @if($hasAddress) <i class="fas fa-check-circle text-green-500 text-xs"></i> @else <span class="text-red-500">*</span> @endif
                        </label>
                        <div class="relative">
                            <input type="text" name="address" id="address" value="{{ old('address', $client->address ?? '') }}" 
                                placeholder="Votre ville ou adresse..."
                                autocomplete="off"
                                class="mt-1 w-full rounded-lg border {{ $hasAddress ? 'border-green-300 bg-green-50' : 'border-orange-300 bg-orange-50' }} px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required>
                            <div id="address-dropdown" class="autocomplete-dropdown"></div>
                        </div>
                        <button type="button" id="geoloc_btn" class="mt-1 text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1">
                            <i class="fas fa-location-arrow"></i> Ma position
                        </button>
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-2 rounded-lg font-medium hover:from-blue-600 hover:to-blue-700 transition">
                        💾 Enregistrer
                    </button>
                </form>
            </div>
            
            {{-- COLONNE 2 : Présentation --}}
            <div class="bg-white rounded-xl shadow-md border-2 {{ $hasBio ? 'border-green-400' : 'border-gray-200' }} overflow-hidden">
                <div class="px-4 py-3 {{ $hasBio ? 'bg-green-50' : 'bg-gray-50' }} border-b flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 {{ $hasBio ? 'bg-green-500' : 'bg-gray-400' }} rounded-lg flex items-center justify-center">
                            @if($hasBio)
                                <i class="fas fa-check text-white"></i>
                            @else
                                <i class="fas fa-pen text-white"></i>
                            @endif
                        </div>
                        <h3 class="font-bold text-gray-800">📝 Ma Présentation</h3>
                    </div>
                    @if($hasBio)
                        <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full font-medium">✓ Complet</span>
                    @else
                        <span class="bg-gray-400 text-white text-xs px-2 py-1 rounded-full font-medium">Optionnel</span>
                    @endif
                </div>
                
                <form action="{{ route('client.profile.update.personal') }}" method="POST" class="p-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="name" value="{{ auth()->user()->name }}">
                    <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                    <input type="hidden" name="phone" value="{{ $client->phone ?? '' }}">
                    <input type="hidden" name="address" value="{{ $client->address ?? '' }}">
                    
                    <textarea name="bio" id="bio" rows="5" 
                        placeholder="Décrivez vos besoins habituels, vos préférences..."
                        class="w-full rounded-lg border {{ $hasBio ? 'border-green-300 bg-green-50' : 'border-gray-300' }} px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500">{{ old('bio', $client->bio ?? '') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Minimum 20 caractères pour être complet</p>
                    
                    <button type="submit" class="w-full mt-3 bg-gradient-to-r from-amber-500 to-amber-600 text-white py-2 rounded-lg font-medium hover:from-amber-600 hover:to-amber-700 transition">
                        💾 Enregistrer
                    </button>
                </form>
            </div>
            
            {{-- COLONNE 3 : Sécurité --}}
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 bg-blue-50 border-b flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-lock text-white"></i>
                    </div>
                    <h3 class="font-bold text-gray-800">🔐 Sécurité</h3>
                </div>
                
                <form action="{{ route('client.profile.update.security') }}" method="POST" class="p-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="is_google_user" value="{{ $isGoogleUser ? '1' : '0' }}">
                    
                    @if($isGoogleUser)
                        <div class="mb-3 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-3">
                            <p class="text-sm text-gray-600 flex items-center gap-2">
                                <img src="https://www.google.com/favicon.ico" alt="" class="w-4 h-4">
                                Connecté via Google
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Vous pouvez créer un mot de passe pour vous connecter aussi avec email + mot de passe.
                            </p>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="text-sm font-medium text-gray-700">Mot de passe actuel</label>
                            <input type="password" name="current_password" 
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                        </div>
                    @endif
                    
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div>
                            <label class="text-xs font-medium text-gray-700">Nouveau</label>
                            <input type="password" name="new_password" id="new_password"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700">Confirmer</label>
                            <input type="password" name="new_password_confirmation" 
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                        </div>
                    </div>
                    
                    {{-- Indicateurs de validation mot de passe --}}
                    <div class="password-requirements mb-3 text-xs text-gray-500">
                        <div class="grid grid-cols-2 gap-1">
                            <div class="flex items-center gap-1" id="req-length">
                                <i class="fas fa-times text-red-400"></i> 8 caractères min
                            </div>
                            <div class="flex items-center gap-1" id="req-upper">
                                <i class="fas fa-times text-red-400"></i> 1 majuscule
                            </div>
                            <div class="flex items-center gap-1" id="req-lower">
                                <i class="fas fa-times text-red-400"></i> 1 minuscule
                            </div>
                            <div class="flex items-center gap-1" id="req-number">
                                <i class="fas fa-times text-red-400"></i> 1 chiffre
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg text-sm font-medium hover:bg-blue-600 transition">
                        Changer mot de passe
                    </button>
                </form>
            </div>
            
            {{-- COLONNE 4 : Zone dangereuse --}}
            <div class="bg-white rounded-xl shadow-md border border-red-200 overflow-hidden">
                <div class="p-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-trash text-red-500"></i>
                        </div>
                        <div>
                            <p class="font-medium text-red-600 text-sm">Supprimer le compte</p>
                            <p class="text-xs text-gray-500">Action irréversible</p>
                        </div>
                    </div>
                    <button type="button" onclick="openDeleteModal()" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-sm font-medium transition">
                        Supprimer
                    </button>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-xl rounded-xl bg-white border-gray-200">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mt-3">Confirmer la suppression</h3>
            <div class="mt-4 px-7 py-3">
                <p class="text-gray-700 mb-4">
                    Pour confirmer la suppression de votre compte :
                </p>
                <form id="deleteForm" method="POST" action="{{ route('client.profile.destroy') }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="is_google_user" value="{{ $isGoogleUser ? '1' : '0' }}">
                    
                    @if($isGoogleUser)
                        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-center gap-2">
                                <img src="https://www.google.com/favicon.ico" alt="" class="w-4 h-4">
                                <span class="text-sm text-blue-800">Compte Google - pas de mot de passe requis</span>
                            </div>
                        </div>
                    @else
                        <div class="mb-4">
                            <label for="delete_password" class="block text-sm font-medium text-gray-800 mb-2">Mot de passe :</label>
                            <input type="password" name="password" id="delete_password" 
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500" 
                                required placeholder="Entrez votre mot de passe">
                        </div>
                    @endif
                    
                    <div class="mb-4">
                        <label for="delete_confirmation" class="block text-sm font-medium text-gray-800 mb-2">
                            Tapez <span class="text-red-600 font-bold">SUPPRIMER</span> :
                        </label>
                        <input type="text" name="confirmation" id="delete_confirmation" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500" 
                            required placeholder="SUPPRIMER">
                    </div>
                    
                    <div class="flex gap-3 mt-5">
                        <button type="button" onclick="closeDeleteModal()" 
                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition font-medium">
                            Annuler
                        </button>
                        <button type="submit" 
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                            Supprimer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Modal functions
function openDeleteModal() {
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Fermer le modal en cliquant à l'extérieur
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Password validation
const newPasswordInput = document.getElementById('new_password');
if (newPasswordInput) {
    newPasswordInput.addEventListener('input', function() {
        const password = this.value;
        
        // Check requirements
        const hasLength = password.length >= 8;
        const hasUpper = /[A-Z]/.test(password);
        const hasLower = /[a-z]/.test(password);
        const hasNumber = /\d/.test(password);
        
        // Update indicators
        updateReq('req-length', hasLength);
        updateReq('req-upper', hasUpper);
        updateReq('req-lower', hasLower);
        updateReq('req-number', hasNumber);
    });
}

function updateReq(id, valid) {
    const el = document.getElementById(id);
    if (el) {
        const icon = el.querySelector('i');
        if (valid) {
            icon.className = 'fas fa-check text-green-500';
            el.classList.add('text-green-600');
            el.classList.remove('text-gray-500');
        } else {
            icon.className = 'fas fa-times text-red-400';
            el.classList.remove('text-green-600');
            el.classList.add('text-gray-500');
        }
    }
}

// Autocomplétion de l'adresse
let autocompleteTimeout = null;
const addressInput = document.getElementById('address');
const addressDropdown = document.getElementById('address-dropdown');
const geolocBtn = document.getElementById('geoloc_btn');

if (addressInput && addressDropdown) {
    addressInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        if (query.length < 3) {
            addressDropdown.style.display = 'none';
            return;
        }
        
        if (autocompleteTimeout) {
            clearTimeout(autocompleteTimeout);
        }
        
        autocompleteTimeout = setTimeout(() => {
            addressDropdown.innerHTML = '<div class="autocomplete-item" style="color: #94a3b8; text-align: center;"><i class="fas fa-spinner fa-spin"></i> Recherche...</div>';
            addressDropdown.style.display = 'block';
            
            fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&limit=6&autocomplete=1`)
                .then(response => response.json())
                .then(data => {
                    addressDropdown.innerHTML = '';
                    
                    if (data.features && data.features.length > 0) {
                        data.features.forEach(feature => {
                            const props = feature.properties;
                            
                            const item = document.createElement('div');
                            item.className = 'autocomplete-item';
                            item.innerHTML = `
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="color: #3b82f6;">📍</span>
                                    <div>
                                        <div style="font-weight: 500; color: #1e293b;">${props.label}</div>
                                        ${props.postcode ? `<div style="font-size: 0.75rem; color: #64748b;">${props.postcode} ${props.city || ''}</div>` : ''}
                                    </div>
                                </div>
                            `;
                            
                            item.addEventListener('click', function() {
                                addressInput.value = props.label;
                                addressDropdown.style.display = 'none';
                                addressInput.classList.remove('border-orange-300', 'bg-orange-50');
                                addressInput.classList.add('border-green-300', 'bg-green-50');
                            });
                            
                            addressDropdown.appendChild(item);
                        });
                        addressDropdown.style.display = 'block';
                    } else {
                        addressDropdown.innerHTML = '<div class="autocomplete-item" style="color: #94a3b8; text-align: center;">Aucun résultat trouvé</div>';
                    }
                })
                .catch(() => {
                    addressDropdown.style.display = 'none';
                });
        }, 300);
    });
    
    document.addEventListener('click', function(e) {
        if (!addressInput.contains(e.target) && !addressDropdown.contains(e.target)) {
            addressDropdown.style.display = 'none';
        }
    });
}

// Géolocalisation
if (geolocBtn) {
    geolocBtn.addEventListener('click', function() {
        if (!navigator.geolocation) {
            alert('La géolocalisation n\'est pas supportée par votre navigateur');
            return;
        }
        
        geolocBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Localisation...';
        geolocBtn.disabled = true;
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                
                fetch(`https://api-adresse.data.gouv.fr/reverse/?lon=${lon}&lat=${lat}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.features && data.features.length > 0) {
                            const addr = data.features[0].properties;
                            addressInput.value = addr.label;
                            addressInput.classList.remove('border-orange-300', 'bg-orange-50');
                            addressInput.classList.add('border-green-300', 'bg-green-50');
                        }
                        
                        geolocBtn.innerHTML = '<i class="fas fa-check text-green-500"></i> Trouvé !';
                        
                        setTimeout(() => {
                            geolocBtn.innerHTML = '<i class="fas fa-location-arrow"></i> Ma position';
                            geolocBtn.disabled = false;
                        }, 2000);
                    })
                    .catch(() => {
                        geolocBtn.innerHTML = '<i class="fas fa-location-arrow"></i> Ma position';
                        geolocBtn.disabled = false;
                    });
            },
            function(error) {
                geolocBtn.innerHTML = '<i class="fas fa-location-arrow"></i> Ma position';
                geolocBtn.disabled = false;
                
                if (error.code === 1) {
                    alert('Veuillez autoriser l\'accès à votre position');
                } else {
                    alert('Impossible d\'obtenir votre position');
                }
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    });
}
</script>
@endpush
