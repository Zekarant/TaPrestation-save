@extends('layouts.app')

@section('title', 'Gestion des livreurs')

@push('styles')
<style>
    /* Fix z-index pour le menu */
    .dropdown-menu, .mobile-menu, [class*="dropdown"], nav, header {
        z-index: 9999 !important;
    }
    .driver-card {
        z-index: 1;
    }
    .modal-backdrop {
        z-index: 10000;
    }
    .modal {
        z-index: 10001;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-amber-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Section d'aide - Commentée temporairement pour debug --}}
        {{-- <x-help-section page="prestataire.drivers.index" /> --}}
        
        {{-- En-tête --}}
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                    <i class="fas fa-users text-orange-500 mr-3"></i>
                    Gestion des livreurs
                </h1>
                <p class="text-gray-600 mt-1">Notez, choisissez vos favoris ou bloquez des livreurs</p>
            </div>
            <a href="{{ route('prestataire.food-delivery.settings') }}" 
               class="inline-flex items-center text-gray-600 hover:text-orange-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour aux paramètres
            </a>
        </div>

        {{-- Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <p class="text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        @endif
        
        {{-- Migration Warning --}}
        @if(isset($migrationNeeded) && $migrationNeeded)
            <div class="mb-6 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-amber-500 mr-3 mt-0.5"></i>
                    <div>
                        <p class="font-medium text-amber-800">Configuration requise</p>
                        <p class="text-amber-700 text-sm mt-1">
                            Certaines fonctionnalités (notation, favoris, blocage) nécessitent l'exécution des migrations de base de données.
                            Contactez l'administrateur ou exécutez: <code class="bg-amber-100 px-2 py-0.5 rounded">php artisan migrate</code>
                        </p>
                    </div>
                </div>
            </div>
        @endif
        
        {{-- Error Message --}}
        @if(isset($error))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                <div class="flex items-center">
                    <i class="fas fa-times-circle text-red-500 mr-3"></i>
                    <p class="text-red-700">{{ $error }}</p>
                </div>
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <a href="{{ route('prestataire.drivers.index', ['filter' => 'all']) }}" 
               class="bg-white rounded-xl shadow p-4 hover:shadow-lg transition-shadow {{ $filter === 'all' ? 'ring-2 ring-orange-500' : '' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                        <p class="text-sm text-gray-500">Tous</p>
                    </div>
                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-gray-600"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('prestataire.drivers.index', ['filter' => 'worked']) }}" 
               class="bg-white rounded-xl shadow p-4 hover:shadow-lg transition-shadow {{ $filter === 'worked' ? 'ring-2 ring-orange-500' : '' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-blue-600">{{ $stats['worked'] }}</p>
                        <p class="text-sm text-gray-500">Ont livré</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-history text-blue-600"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('prestataire.drivers.index', ['filter' => 'preferred']) }}" 
               class="bg-white rounded-xl shadow p-4 hover:shadow-lg transition-shadow {{ $filter === 'preferred' ? 'ring-2 ring-orange-500' : '' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['preferred'] }}</p>
                        <p class="text-sm text-gray-500">Favoris</p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-star text-green-600"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('prestataire.drivers.index', ['filter' => 'blocked']) }}" 
               class="bg-white rounded-xl shadow p-4 hover:shadow-lg transition-shadow {{ $filter === 'blocked' ? 'ring-2 ring-orange-500' : '' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-red-600">{{ $stats['blocked'] }}</p>
                        <p class="text-sm text-gray-500">Bloqués</p>
                    </div>
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-ban text-red-600"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('prestataire.drivers.index', ['filter' => 'internal']) }}" 
               class="bg-white rounded-xl shadow p-4 hover:shadow-lg transition-shadow {{ $filter === 'internal' ? 'ring-2 ring-orange-500' : '' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-purple-600">{{ $stats['internal'] }}</p>
                        <p class="text-sm text-gray-500">Internes</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-id-badge text-purple-600"></i>
                    </div>
                </div>
            </a>
        </div>

        {{-- Recherche --}}
        <div class="bg-white rounded-xl shadow p-4 mb-6">
            <form action="{{ route('prestataire.drivers.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}"
                           placeholder="Rechercher un livreur (nom, téléphone...)"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                </div>
                <button type="submit" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors">
                    <i class="fas fa-search mr-2"></i>
                    Rechercher
                </button>
                @if($search)
                    <a href="{{ route('prestataire.drivers.index', ['filter' => $filter]) }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
        </div>

        {{-- Liste des livreurs --}}
        @if($drivers->isEmpty())
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-motorcycle text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun livreur trouvé</h3>
                <p class="text-gray-500">
                    @if($search)
                        Aucun livreur ne correspond à votre recherche.
                    @elseif($filter === 'preferred')
                        Vous n'avez pas encore de livreurs favoris.
                    @elseif($filter === 'blocked')
                        Vous n'avez bloqué aucun livreur.
                    @elseif($filter === 'internal')
                        Vous n'avez pas de livreurs internes.
                    @else
                        Aucun livreur disponible pour le moment.
                    @endif
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($drivers as $driver)
                    @php
                        $pref = $preferences->get($driver->id);
                        $prefStatus = $pref?->status ?? 'neutral';
                        $myRating = $myRatings->get($driver->id);
                        $deliveryCount = $deliveryCounts->get($driver->id, 0);
                        $isInternal = isset($driver->employer_prestataire_id) && $driver->employer_prestataire_id === $prestataire->id;
                    @endphp
                    <div class="driver-card bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 hover:shadow-xl transition-all relative">
                        {{-- Badge statut --}}
                        @if($prefStatus === 'preferred')
                            <div class="absolute top-3 left-3 px-2 py-1 bg-green-500 text-white text-xs font-medium rounded-full flex items-center">
                                <i class="fas fa-star mr-1"></i> Favori
                            </div>
                        @elseif($prefStatus === 'blocked')
                            <div class="absolute top-3 left-3 px-2 py-1 bg-red-500 text-white text-xs font-medium rounded-full flex items-center">
                                <i class="fas fa-ban mr-1"></i> Bloqué
                            </div>
                        @endif

                        @if($isInternal)
                            <div class="absolute top-3 right-3 px-2 py-1 bg-purple-500 text-white text-xs font-medium rounded-full flex items-center">
                                <i class="fas fa-id-badge mr-1"></i> Interne
                            </div>
                        @endif

                        <div class="p-5">
                            {{-- En-tête livreur --}}
                            <div class="flex items-start gap-4 mb-4">
                                <div class="w-14 h-14 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    @if($driver->photo)
                                        <img src="{{ asset('storage/' . $driver->photo) }}" 
                                             alt="{{ $driver->full_name }}" 
                                             class="w-14 h-14 rounded-full object-cover">
                                    @else
                                        <span class="text-xl font-bold text-orange-600">
                                            {{ strtoupper(substr($driver->first_name, 0, 1) . substr($driver->last_name, 0, 1)) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 truncate">{{ $driver->full_name }}</h3>
                                    <div class="flex items-center gap-2 text-sm text-gray-500">
                                        <span>{{ $driver->vehicle_icon }}</span>
                                        <span class="capitalize">{{ $driver->vehicle_type }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 mt-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star text-sm {{ $i <= ($driver->rating ?? 0) ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                        @endfor
                                        <span class="text-sm text-gray-600 ml-1">{{ number_format($driver->rating ?? 0, 1) }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Stats --}}
                            <div class="grid grid-cols-3 gap-2 mb-4 p-3 bg-gray-50 rounded-lg">
                                <div class="text-center">
                                    <p class="text-lg font-bold text-gray-900">{{ $driver->completed_deliveries ?? 0 }}</p>
                                    <p class="text-xs text-gray-500">Total</p>
                                </div>
                                <div class="text-center border-x border-gray-200">
                                    <p class="text-lg font-bold text-orange-600">{{ $deliveryCount }}</p>
                                    <p class="text-xs text-gray-500">Pour vous</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-lg font-bold text-gray-900">{{ $driver->success_rate ?? 100 }}%</p>
                                    <p class="text-xs text-gray-500">Succès</p>
                                </div>
                            </div>

                            {{-- Ma note --}}
                            @if($myRating)
                                <div class="flex items-center justify-between mb-4 p-2 bg-orange-50 rounded-lg">
                                    <span class="text-sm text-gray-600">Ma note:</span>
                                    <div class="flex items-center gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star text-sm {{ $i <= $myRating->avg_rating ? 'text-orange-500' : 'text-gray-300' }}"></i>
                                        @endfor
                                        <span class="text-sm text-gray-600 ml-1">({{ $myRating->count }})</span>
                                    </div>
                                </div>
                            @endif

                            {{-- Tarifs livreur --}}
                            @if($driver->pricing)
                                <div class="flex items-center gap-2 mb-4 text-sm text-gray-600">
                                    <i class="fas fa-euro-sign text-gray-400"></i>
                                    <span>{{ number_format($driver->pricing->base_fee, 2) }}€ + {{ number_format($driver->pricing->fee_per_km, 2) }}€/km</span>
                                </div>
                            @endif

                            {{-- Actions --}}
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('prestataire.drivers.show', $driver) }}" 
                                   class="flex-1 text-center px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                                    <i class="fas fa-eye mr-1"></i> Profil
                                </a>

                                @if($prefStatus !== 'preferred')
                                    <form action="{{ route('prestataire.drivers.quick-action', $driver) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="prefer">
                                        <button type="submit" 
                                                class="px-3 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-sm"
                                                title="Ajouter aux favoris">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('prestataire.drivers.quick-action', $driver) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="unprefer">
                                        <button type="submit" 
                                                class="px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm"
                                                title="Retirer des favoris">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </form>
                                @endif

                                @if($prefStatus !== 'blocked')
                                    <button type="button" 
                                            onclick="openBlockModal({{ $driver->id }}, '{{ $driver->full_name }}')"
                                            class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm"
                                            title="Bloquer">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @else
                                    <form action="{{ route('prestataire.drivers.quick-action', $driver) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="action" value="unblock">
                                        <button type="submit" 
                                                class="px-3 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors text-sm"
                                                title="Débloquer">
                                            <i class="fas fa-unlock"></i>
                                        </button>
                                    </form>
                                @endif

                                <button type="button" 
                                        onclick="openRatingModal({{ $driver->id }}, '{{ $driver->full_name }}')"
                                        class="px-3 py-2 bg-orange-100 text-orange-700 rounded-lg hover:bg-orange-200 transition-colors text-sm"
                                        title="Noter">
                                    <i class="fas fa-star-half-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $drivers->appends(['filter' => $filter, 'search' => $search])->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal bloquer livreur --}}
<div id="blockModal" class="fixed inset-0 z-[10000] hidden">
    <div class="modal-backdrop absolute inset-0 bg-black/50" onclick="closeBlockModal()"></div>
    <div class="modal absolute inset-4 sm:inset-auto sm:top-1/2 sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2 sm:w-full sm:max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-ban text-red-500 mr-2"></i>
                    Bloquer le livreur
                </h3>
                <button onclick="closeBlockModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-gray-600 mb-4">
                Voulez-vous vraiment bloquer <strong id="blockDriverName"></strong> ?
                Ce livreur ne pourra plus recevoir vos commandes.
            </p>
            <form id="blockForm" method="POST">
                @csrf
                <input type="hidden" name="status" value="blocked">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Raison (optionnel)</label>
                    <input type="text" 
                           name="block_reason" 
                           placeholder="Ex: Retards répétés, comportement..."
                           class="w-full border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeBlockModal()" 
                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                        <i class="fas fa-ban mr-2"></i>
                        Bloquer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal noter livreur --}}
<div id="ratingModal" class="fixed inset-0 z-[10000] hidden">
    <div class="modal-backdrop absolute inset-0 bg-black/50" onclick="closeRatingModal()"></div>
    <div class="modal absolute inset-4 sm:inset-auto sm:top-1/2 sm:left-1/2 sm:-translate-x-1/2 sm:-translate-y-1/2 sm:w-full sm:max-w-lg bg-white rounded-2xl shadow-2xl overflow-auto max-h-[90vh]">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-star text-orange-500 mr-2"></i>
                    Noter le livreur
                </h3>
                <button onclick="closeRatingModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-gray-600 mb-4">
                Donnez une note à <strong id="ratingDriverName"></strong>
            </p>
            <form id="ratingForm" method="POST">
                @csrf
                
                {{-- Note globale --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Note globale *</label>
                    <div class="flex items-center gap-2" id="globalRating">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" 
                                    onclick="setRating('rating', {{ $i }})"
                                    class="star-btn w-10 h-10 rounded-full bg-gray-100 hover:bg-orange-100 flex items-center justify-center transition-all"
                                    data-star="{{ $i }}">
                                <i class="fas fa-star text-xl text-gray-300"></i>
                            </button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" required>
                </div>

                {{-- Notes détaillées --}}
                <div class="grid grid-cols-1 gap-4 mb-4 p-4 bg-gray-50 rounded-lg">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-clock text-gray-400 mr-1"></i> Ponctualité
                        </label>
                        <div class="flex items-center gap-1" id="punctualityRating">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" 
                                        onclick="setRating('punctuality_rating', {{ $i }})"
                                        class="star-btn-sm w-8 h-8 rounded-full hover:bg-orange-100 flex items-center justify-center"
                                        data-star="{{ $i }}">
                                    <i class="fas fa-star text-gray-300"></i>
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="punctuality_rating" id="punctuality_ratingInput">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-user-tie text-gray-400 mr-1"></i> Professionnalisme
                        </label>
                        <div class="flex items-center gap-1" id="professionalismRating">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" 
                                        onclick="setRating('professionalism_rating', {{ $i }})"
                                        class="star-btn-sm w-8 h-8 rounded-full hover:bg-orange-100 flex items-center justify-center"
                                        data-star="{{ $i }}">
                                    <i class="fas fa-star text-gray-300"></i>
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="professionalism_rating" id="professionalism_ratingInput">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-box text-gray-400 mr-1"></i> Soin des commandes
                        </label>
                        <div class="flex items-center gap-1" id="careRating">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" 
                                        onclick="setRating('care_rating', {{ $i }})"
                                        class="star-btn-sm w-8 h-8 rounded-full hover:bg-orange-100 flex items-center justify-center"
                                        data-star="{{ $i }}">
                                    <i class="fas fa-star text-gray-300"></i>
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="care_rating" id="care_ratingInput">
                    </div>
                </div>

                {{-- Commentaire --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Commentaire (optionnel)</label>
                    <textarea name="comment" 
                              rows="3"
                              placeholder="Décrivez votre expérience avec ce livreur..."
                              class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"></textarea>
                </div>

                {{-- Rendre public --}}
                <div class="mb-4 flex items-center">
                    <input type="checkbox" 
                           name="is_public" 
                           value="1"
                           id="isPublicCheck"
                           class="rounded border-gray-300 text-orange-500 focus:ring-orange-500">
                    <label for="isPublicCheck" class="ml-2 text-sm text-gray-600">
                        Rendre cette note visible par le livreur
                    </label>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeRatingModal()" 
                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600">
                        <i class="fas fa-star mr-2"></i>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openBlockModal(driverId, driverName) {
        document.getElementById('blockDriverName').textContent = driverName;
        document.getElementById('blockForm').action = `/prestataire/drivers/${driverId}/preference`;
        document.getElementById('blockModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeBlockModal() {
        document.getElementById('blockModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function openRatingModal(driverId, driverName) {
        document.getElementById('ratingDriverName').textContent = driverName;
        document.getElementById('ratingForm').action = `/prestataire/drivers/${driverId}/rate`;
        document.getElementById('ratingModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Reset stars
        document.querySelectorAll('.star-btn i, .star-btn-sm i').forEach(star => {
            star.classList.remove('text-orange-500');
            star.classList.add('text-gray-300');
        });
        document.querySelectorAll('input[type="hidden"][id$="Input"]').forEach(input => {
            input.value = '';
        });
    }

    function closeRatingModal() {
        document.getElementById('ratingModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function setRating(field, value) {
        document.getElementById(field + 'Input').value = value;
        
        // Determine container based on field
        let containerId;
        switch(field) {
            case 'rating': containerId = 'globalRating'; break;
            case 'punctuality_rating': containerId = 'punctualityRating'; break;
            case 'professionalism_rating': containerId = 'professionalismRating'; break;
            case 'care_rating': containerId = 'careRating'; break;
        }
        
        const container = document.getElementById(containerId);
        container.querySelectorAll('button').forEach((btn, index) => {
            const star = btn.querySelector('i');
            if (index < value) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-orange-500');
            } else {
                star.classList.remove('text-orange-500');
                star.classList.add('text-gray-300');
            }
        });
    }

    // Close modals on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBlockModal();
            closeRatingModal();
        }
    });
</script>
@endpush
@endsection
