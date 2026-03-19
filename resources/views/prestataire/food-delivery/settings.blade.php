@extends('layouts.app')

@section('title', 'Paramètres de livraison - Food')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-amber-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Bannière d'aide --}}
        <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-5 border border-blue-100">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-lightbulb text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-blue-900 mb-1">Comment configurer vos livraisons ?</h3>
                    <p class="text-blue-700 text-sm leading-relaxed">
                        Cette page vous permet de définir <strong>comment vos clients reçoivent leurs commandes</strong>. 
                        Vous pouvez proposer le retrait sur place, la livraison, ou les deux. 
                        Configurez vos zones, tarifs et horaires pour offrir la meilleure expérience à vos clients.
                    </p>
                </div>
            </div>
        </div>
        
        {{-- En-tête --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                    <i class="fas fa-truck text-orange-500 mr-3"></i>
                    Paramètres de livraison
                </h1>
                <p class="text-gray-600 mt-1">Configurez vos options de livraison et de retrait</p>
            </div>
            <a href="{{ route('prestataire.food-products.index') }}" 
               class="inline-flex items-center text-gray-600 hover:text-orange-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Retour aux produits
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

        @if($errors->any())
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 p-4 rounded-r-lg">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <p class="text-red-700 font-medium">Erreurs de validation</p>
                </div>
                <ul class="list-disc list-inside text-red-600 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('prestataire.food-delivery.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Modes de récupération --}}
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                    <i class="fas fa-toggle-on text-orange-500 mr-2"></i>
                    Modes de récupération
                </h2>
                <p class="text-sm text-gray-500 mb-4">
                    <i class="fas fa-info-circle text-gray-400 mr-1"></i>
                    Choisissez comment vos clients peuvent récupérer leurs commandes. Vous pouvez activer les deux options.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Retrait sur place --}}
                    <label class="mode-card relative flex items-start p-4 border-2 rounded-xl cursor-pointer transition-all
                                  {{ $prestataire->food_pickup_enabled ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                        <input type="checkbox" 
                               name="food_pickup_enabled" 
                               value="1"
                               {{ $prestataire->food_pickup_enabled ? 'checked' : '' }}
                               class="hidden mode-checkbox"
                               onchange="toggleModeStyle(this)">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-store text-orange-500 text-xl"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Retrait sur place</p>
                                <p class="text-sm text-gray-500">Le client vient chercher sa commande</p>
                            </div>
                        </div>
                        <div class="absolute top-4 right-4">
                            <div class="w-6 h-6 border-2 rounded-full flex items-center justify-center
                                        {{ $prestataire->food_pickup_enabled ? 'border-orange-500 bg-orange-500' : 'border-gray-300' }}">
                                @if($prestataire->food_pickup_enabled)
                                    <i class="fas fa-check text-white text-xs"></i>
                                @endif
                            </div>
                        </div>
                    </label>

                    {{-- Livraison --}}
                    <label class="mode-card relative flex items-start p-4 border-2 rounded-xl cursor-pointer transition-all
                                  {{ $prestataire->food_delivery_enabled ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                        <input type="checkbox" 
                               name="food_delivery_enabled" 
                               value="1"
                               {{ $prestataire->food_delivery_enabled ? 'checked' : '' }}
                               class="hidden mode-checkbox"
                               onchange="toggleModeStyle(this); toggleDeliverySettings()">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-motorcycle text-orange-500 text-xl"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Livraison</p>
                                <p class="text-sm text-gray-500">Vous livrez à domicile</p>
                            </div>
                        </div>
                        <div class="absolute top-4 right-4">
                            <div class="w-6 h-6 border-2 rounded-full flex items-center justify-center
                                        {{ $prestataire->food_delivery_enabled ? 'border-orange-500 bg-orange-500' : 'border-gray-300' }}">
                                @if($prestataire->food_delivery_enabled)
                                    <i class="fas fa-check text-white text-xs"></i>
                                @endif
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Mode de livraison (Interne vs Externe) --}}
            <div id="deliveryModeSection" class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 {{ $prestataire->food_delivery_enabled ? '' : 'hidden' }}">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-users-cog text-orange-500 mr-2"></i>
                    Qui effectue vos livraisons ?
                </h2>
                <div class="mb-6 p-4 bg-amber-50 rounded-xl border border-amber-100">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-question-circle text-amber-500 mt-0.5"></i>
                        <div class="text-sm">
                            <p class="font-medium text-amber-800 mb-1">Quelle option choisir ?</p>
                            <ul class="text-amber-700 space-y-1">
                                <li><strong>Interne :</strong> Vous gérez vos propres livreurs (salariés ou sous contrat)</li>
                                <li><strong>Externe :</strong> Des livreurs indépendants de la plateforme prennent vos commandes</li>
                                <li><strong>Les deux :</strong> Vos livreurs en priorité, externes en renfort si besoin</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @php
                        $externalAvailable = $externalDriversCheck['available'] ?? false;
                        $externalCount = $externalDriversCheck['count'] ?? 0;
                        $externalRequired = $externalDriversCheck['required'] ?? 4;
                        $externalRadius = $externalDriversCheck['radius_km'] ?? 10;
                        $externalDisabled = !$externalAvailable;

                        // Si external/both sont bloqués, forcer internal
                        $effectiveMode = $prestataire->delivery_mode ?? 'both';
                        if ($externalDisabled && in_array($effectiveMode, ['external', 'both'])) {
                            $effectiveMode = 'internal';
                        }
                    @endphp
                    
                    {{-- Livraison interne --}}
                    <label class="delivery-mode-card relative flex flex-col p-4 border-2 rounded-xl cursor-pointer transition-all hover:shadow-md
                                  {{ $effectiveMode === 'internal' ? 'border-orange-500 bg-orange-50' : 'border-gray-200' }}">
                        <input type="radio" 
                               name="delivery_mode" 
                               value="internal"
                               {{ $effectiveMode === 'internal' ? 'checked' : '' }}
                               class="hidden delivery-mode-radio"
                               onchange="toggleDeliveryMode()">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-building text-blue-600 text-lg"></i>
                            </div>
                            <span class="font-semibold text-gray-900">Interne</span>
                        </div>
                        <p class="text-sm text-gray-600">Je livre moi-même ou avec mon équipe</p>
                        <div class="absolute top-3 right-3">
                            <div class="w-5 h-5 border-2 rounded-full flex items-center justify-center
                                        {{ $effectiveMode === 'internal' ? 'border-orange-500 bg-orange-500' : 'border-gray-300' }}">
                                @if($effectiveMode === 'internal')
                                    <i class="fas fa-check text-white text-xs"></i>
                                @endif
                            </div>
                        </div>
                    </label>

                    {{-- Livreurs externes --}}
                    <label class="delivery-mode-card relative flex flex-col p-4 border-2 rounded-xl transition-all
                                  {{ $externalDisabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:shadow-md' }}
                                  {{ ($prestataire->delivery_mode ?? 'both') === 'external' && !$externalDisabled ? 'border-orange-500 bg-orange-50' : 'border-gray-200' }}">
                        <input type="radio" 
                               name="delivery_mode" 
                               value="external"
                               {{ ($prestataire->delivery_mode ?? 'both') === 'external' && !$externalDisabled ? 'checked' : '' }}
                               {{ $externalDisabled ? 'disabled' : '' }}
                               class="hidden delivery-mode-radio"
                               onchange="toggleDeliveryMode()">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 {{ $externalDisabled ? 'bg-gray-100' : 'bg-green-100' }} rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-motorcycle {{ $externalDisabled ? 'text-gray-400' : 'text-green-600' }} text-lg"></i>
                            </div>
                            <span class="font-semibold {{ $externalDisabled ? 'text-gray-400' : 'text-gray-900' }}">Externe</span>
                            @if($externalDisabled)
                                <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-600 text-xs font-medium rounded-full">Indisponible</span>
                            @endif
                        </div>
                        <p class="text-sm {{ $externalDisabled ? 'text-gray-400' : 'text-gray-600' }}">
                            @if($externalDisabled)
                                {{ $externalCount }}/{{ $externalRequired }} livreurs dans {{ $externalRadius }}km
                            @else
                                Livreurs indépendants de la plateforme
                            @endif
                        </p>
                        @if($externalDisabled)
                            <p class="text-xs text-red-500 mt-1">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Minimum {{ $externalRequired }} livreurs requis
                            </p>
                        @endif
                        <div class="absolute top-3 right-3">
                            <div class="w-5 h-5 border-2 rounded-full flex items-center justify-center
                                        {{ ($prestataire->delivery_mode ?? 'both') === 'external' && !$externalDisabled ? 'border-orange-500 bg-orange-500' : 'border-gray-300' }}">
                                @if(($prestataire->delivery_mode ?? 'both') === 'external' && !$externalDisabled)
                                    <i class="fas fa-check text-white text-xs"></i>
                                @endif
                            </div>
                        </div>
                    </label>

                    {{-- Les deux --}}
                    <label class="delivery-mode-card relative flex flex-col p-4 border-2 rounded-xl transition-all
                                  {{ $externalDisabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:shadow-md' }}
                                  {{ ($prestataire->delivery_mode ?? 'both') === 'both' && !$externalDisabled ? 'border-orange-500 bg-orange-50' : 'border-gray-200' }}">
                        <input type="radio" 
                               name="delivery_mode" 
                               value="both"
                               {{ ($prestataire->delivery_mode ?? 'both') === 'both' && !$externalDisabled ? 'checked' : '' }}
                               {{ $externalDisabled ? 'disabled' : '' }}
                               class="hidden delivery-mode-radio"
                               onchange="toggleDeliveryMode()">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 {{ $externalDisabled ? 'bg-gray-100' : 'bg-purple-100' }} rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-random {{ $externalDisabled ? 'text-gray-400' : 'text-purple-600' }} text-lg"></i>
                            </div>
                            <span class="font-semibold {{ $externalDisabled ? 'text-gray-400' : 'text-gray-900' }}">Les deux</span>
                            @if($externalDisabled)
                                <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-600 text-xs font-medium rounded-full">Indisponible</span>
                            @endif
                        </div>
                        <p class="text-sm {{ $externalDisabled ? 'text-gray-400' : 'text-gray-600' }}">
                            @if($externalDisabled)
                                Requiert des livreurs externes
                            @else
                                Interne + livreurs externes en renfort
                            @endif
                        </p>
                        <div class="absolute top-3 right-3">
                            <div class="w-5 h-5 border-2 rounded-full flex items-center justify-center
                                        {{ ($prestataire->delivery_mode ?? 'both') === 'both' && !$externalDisabled ? 'border-orange-500 bg-orange-500' : 'border-gray-300' }}">
                                @if(($prestataire->delivery_mode ?? 'both') === 'both' && !$externalDisabled)
                                    <i class="fas fa-check text-white text-xs"></i>
                                @endif
                            </div>
                        </div>
                    </label>
                </div>

                {{-- Options avancées pour livreurs externes --}}
                <div id="externalDriverOptions" class="mt-6 pt-6 border-t border-gray-100 {{ in_array($prestataire->delivery_mode ?? 'both', ['external', 'both']) ? '' : 'hidden' }}">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                        <i class="fas fa-sliders-h text-gray-400 mr-2"></i>
                        Options livreurs externes
                    </h3>
                    <p class="text-xs text-gray-500 mb-4">
                        Configurez les critères pour les livreurs externes qui livreront vos commandes.
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Attribution automatique --}}
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg" title="Si activé, un livreur sera automatiquement assigné à vos commandes. Sinon, vous devrez choisir manuellement.">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Attribution auto</p>
                                <p class="text-xs text-gray-500">Le système choisit le meilleur livreur</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       name="auto_assign_drivers" 
                                       value="1"
                                       {{ ($prestataire->auto_assign_drivers ?? true) ? 'checked' : '' }}
                                       class="sr-only peer" id="autoAssignToggle">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-orange-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                            </label>
                        </div>

                        {{-- Note minimum --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Note minimum livreur
                            </label>
                            <select name="min_driver_rating" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm">
                                <option value="">Aucun minimum</option>
                                <option value="3" {{ ($prestataire->min_driver_rating ?? '') == 3 ? 'selected' : '' }}>3+ étoiles</option>
                                <option value="3.5" {{ ($prestataire->min_driver_rating ?? '') == 3.5 ? 'selected' : '' }}>3.5+ étoiles</option>
                                <option value="4" {{ ($prestataire->min_driver_rating ?? '') == 4 ? 'selected' : '' }}>4+ étoiles</option>
                                <option value="4.5" {{ ($prestataire->min_driver_rating ?? '') == 4.5 ? 'selected' : '' }}>4.5+ étoiles</option>
                            </select>
                        </div>
                    </div>

                    {{-- Lien vers gestion livreurs --}}
                    <div class="mt-4 p-4 bg-gradient-to-r from-orange-50 to-amber-50 rounded-xl border border-orange-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user-cog text-orange-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Gérer vos livreurs</p>
                                    <p class="text-sm text-gray-500">Noter, choisir vos favoris, ou bloquer des livreurs</p>
                                </div>
                            </div>
                            <a href="{{ route('prestataire.drivers.index') }}" class="inline-flex items-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm font-medium transition-colors">
                                <i class="fas fa-cog mr-2"></i>
                                Gérer
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paramètres de livraison (tarifs) - Uniquement si interne ou both --}}
            @php
                $showDeliveryParams = $prestataire->food_delivery_enabled && in_array($prestataire->delivery_mode ?? 'both', ['internal', 'both']);
            @endphp
            <div id="deliverySettings" class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 {{ $showDeliveryParams ? '' : 'hidden' }}">
                <h2 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                    <i class="fas fa-cog text-orange-500 mr-2"></i>
                    Vos tarifs de livraison
                </h2>
                <p class="text-sm text-gray-500 mb-4">
                    <i class="fas fa-info-circle text-gray-400 mr-1"></i>
                    Ces paramètres s'appliquent quand <strong>vous</strong> effectuez les livraisons (interne). Si vous utilisez uniquement les livreurs externes de la plateforme, ils définissent leurs propres tarifs.
                </p>
                
                {{-- Info box calcul frais --}}
                <div class="mb-6 p-4 bg-green-50 rounded-xl border border-green-100">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-calculator text-green-600 mt-0.5"></i>
                        <div class="text-sm">
                            <p class="font-medium text-green-800 mb-1">Comment sont calculés les frais de livraison ?</p>
                            <p class="text-green-700">
                                <strong>Frais totaux = Frais de base + (Distance × Frais par km)</strong><br>
                                <span class="text-xs">Exemple : Si base = 3€ et 0.50€/km, une livraison à 4km coûtera 3€ + (4 × 0.50€) = <strong>5€</strong></span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Zone de livraison --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt text-orange-400 mr-1"></i>
                            Zone de livraison (rayon en km)
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   name="food_delivery_radius_km" 
                                   value="{{ old('food_delivery_radius_km', $prestataire->food_delivery_radius_km ?? 5) }}"
                                   min="1" 
                                   max="50"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 pr-12">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">km</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Distance maximale depuis votre adresse</p>
                    </div>

                    {{-- Frais de base --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-euro-sign text-orange-400 mr-1"></i>
                            Frais de livraison de base
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   name="food_delivery_base_fee" 
                                   value="{{ old('food_delivery_base_fee', $prestataire->food_delivery_base_fee ?? 3.00) }}"
                                   step="0.50"
                                   min="0" 
                                   max="50"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 pr-8">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">€</span>
                        </div>
                    </div>

                    {{-- Frais par km --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-route text-orange-400 mr-1"></i>
                            Frais supplémentaires par km
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   name="food_delivery_fee_per_km" 
                                   value="{{ old('food_delivery_fee_per_km', $prestataire->food_delivery_fee_per_km ?? 0.50) }}"
                                   step="0.10"
                                   min="0" 
                                   max="5"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 pr-12">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">€/km</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Ajouté aux frais de base</p>
                    </div>

                    {{-- Temps de préparation --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-clock text-orange-400 mr-1"></i>
                            Temps de préparation moyen
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   name="food_estimated_prep_time" 
                                   value="{{ old('food_estimated_prep_time', $prestataire->food_estimated_prep_time ?? 30) }}"
                                   min="5" 
                                   max="180"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 pr-12">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">min</span>
                        </div>
                    </div>
                </div>

                {{-- Minimums et livraison gratuite --}}
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Minimums de commande & Livraison gratuite</h3>
                    <p class="text-xs text-gray-500 mb-4">
                        <i class="fas fa-lightbulb text-amber-400 mr-1"></i>
                        <strong>Astuce :</strong> Un minimum de commande réduit les petites commandes non rentables. La livraison gratuite encourage les clients à commander plus.
                    </p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Minimum livraison --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-shopping-basket text-orange-400 mr-1"></i>
                            Min. commande livraison
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   name="food_min_order_delivery" 
                                   value="{{ old('food_min_order_delivery', $prestataire->food_min_order_delivery) }}"
                                   step="0.50"
                                   min="0"
                                   placeholder="Aucun"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 pr-8">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">€</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Laisser vide = pas de minimum</p>
                    </div>

                    {{-- Minimum retrait --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-store text-orange-400 mr-1"></i>
                            Min. commande retrait
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   name="food_min_order_pickup" 
                                   value="{{ old('food_min_order_pickup', $prestataire->food_min_order_pickup) }}"
                                   step="0.50"
                                   min="0"
                                   placeholder="Aucun"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 pr-8">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">€</span>
                        </div>
                    </div>

                    {{-- Livraison gratuite --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-gift text-orange-400 mr-1"></i>
                            Livraison gratuite à partir de
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   name="food_free_delivery_above" 
                                   value="{{ old('food_free_delivery_above', $prestataire->food_free_delivery_above) }}"
                                   step="1"
                                   min="0"
                                   placeholder="Non applicable"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500 pr-8">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500">€</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Horaires de livraison --}}
            <div id="deliverySchedule" class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 {{ $prestataire->food_delivery_enabled ? '' : 'hidden' }}">
                <h2 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                    <i class="fas fa-calendar-alt text-orange-500 mr-2"></i>
                    Horaires de livraison
                </h2>
                <p class="text-sm text-gray-500 mb-2">Définissez un ou plusieurs créneaux de livraison pour chaque jour</p>
                <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-100">
                    <p class="text-xs text-blue-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Important :</strong> Les clients ne pourront commander en livraison que pendant ces horaires. 
                        Décochez un jour pour ne pas livrer ce jour-là, ou ajoutez plusieurs créneaux si vous faites une coupure.
                    </p>
                </div>

                @php
                    $days = [
                        'lundi' => 'Lundi',
                        'mardi' => 'Mardi',
                        'mercredi' => 'Mercredi',
                        'jeudi' => 'Jeudi',
                        'vendredi' => 'Vendredi',
                        'samedi' => 'Samedi',
                        'dimanche' => 'Dimanche',
                    ];
                    $oldSchedule = old('delivery_schedule');
                    $schedule = is_array($oldSchedule) ? $oldSchedule : ($prestataire->food_delivery_schedule ?? []);

                    $normalizeTime = static function ($value) {
                        if (!is_string($value)) {
                            return null;
                        }

                        $value = trim($value);
                        if ($value === '') {
                            return null;
                        }

                        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $value) === 1
                            ? substr($value, 0, 5)
                            : null;
                    };

                    $resolveDaySchedule = static function ($daySchedule) use ($normalizeTime) {
                        $daySchedule = is_array($daySchedule) ? $daySchedule : [];
                        $enabled = array_key_exists('enabled', $daySchedule) ? (bool) $daySchedule['enabled'] : true;
                        $rawSlots = [];

                        if (isset($daySchedule['slots']) && is_array($daySchedule['slots'])) {
                            $rawSlots = $daySchedule['slots'];
                        } elseif (array_key_exists('start', $daySchedule) || array_key_exists('end', $daySchedule)) {
                            $rawSlots = [[
                                'start' => $daySchedule['start'] ?? null,
                                'end' => $daySchedule['end'] ?? null,
                            ]];
                        }

                        $slots = [];
                        foreach ($rawSlots as $slot) {
                            if (!is_array($slot)) {
                                continue;
                            }

                            $start = $normalizeTime($slot['start'] ?? null);
                            $end = $normalizeTime($slot['end'] ?? null);

                            if (!$start || !$end) {
                                continue;
                            }

                            $slots[] = [
                                'start' => $start,
                                'end' => $end,
                            ];
                        }

                        usort($slots, static fn ($left, $right) => strcmp($left['start'], $right['start']));

                        if (empty($slots)) {
                            $slots[] = [
                                'start' => '11:00',
                                'end' => '22:00',
                            ];
                        }

                        return [
                            'enabled' => $enabled,
                            'slots' => array_values($slots),
                        ];
                    };
                @endphp

                <div class="space-y-4">
                    @foreach($days as $key => $day)
                        @php
                            $daySchedule = $resolveDaySchedule($schedule[$key] ?? null);
                        @endphp
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           name="delivery_schedule[{{ $key }}][enabled]" 
                                           value="1"
                                           {{ $daySchedule['enabled'] ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-orange-500 focus:ring-orange-500"
                                           onchange="toggleScheduleDay('{{ $key }}')">
                                    <span class="ml-3 text-sm font-semibold text-gray-800">{{ $day }}</span>
                                </label>
                                <button type="button"
                                        onclick="addDeliverySlot('{{ $key }}')"
                                        class="inline-flex items-center justify-center rounded-xl border border-orange-200 bg-orange-50 px-3 py-2 text-xs font-semibold text-orange-600 transition hover:bg-orange-100">
                                    <i class="fas fa-plus mr-2 text-xs"></i>
                                    Ajouter un créneau
                                </button>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                Exemple: 11:30 - 14:00 puis 18:00 - 22:30 si vous faites une coupure.
                            </p>
                            <div id="delivery-schedule-day-{{ $key }}"
                                 data-day-slots="{{ $key }}"
                                 class="mt-3 space-y-3 {{ $daySchedule['enabled'] ? '' : 'hidden' }}">
                                @foreach($daySchedule['slots'] as $slotIndex => $slot)
                                    <div class="rounded-xl border border-gray-200 bg-white p-3" data-slot-row>
                                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-4 sm:items-end">
                                            <div>
                                                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Début</label>
                                                <input type="time"
                                                       data-field="start"
                                                       name="delivery_schedule[{{ $key }}][slots][{{ $slotIndex }}][start]"
                                                       value="{{ $slot['start'] }}"
                                                       class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm">
                                            </div>
                                            <div class="hidden text-center text-gray-400 sm:block sm:pb-3">à</div>
                                            <div>
                                                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Fin</label>
                                                <input type="time"
                                                       data-field="end"
                                                       name="delivery_schedule[{{ $key }}][slots][{{ $slotIndex }}][end]"
                                                       value="{{ $slot['end'] }}"
                                                       class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm">
                                            </div>
                                            <div class="sm:pt-6">
                                                <button type="button"
                                                        data-remove-slot
                                                        onclick="removeDeliverySlot(this)"
                                                        class="inline-flex w-full items-center justify-center rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs font-medium text-red-600 transition hover:bg-red-100 sm:w-auto">
                                                    <i class="fas fa-trash-alt mr-2 text-xs"></i>
                                                    Supprimer
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <p id="delivery-schedule-disabled-{{ $key }}"
                               class="mt-3 text-xs text-gray-500 {{ $daySchedule['enabled'] ? 'hidden' : '' }}">
                                Livraison coupée ce jour-là. Réactivez le jour pour définir un ou plusieurs créneaux.
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Instructions de livraison --}}
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-2 flex items-center">
                    <i class="fas fa-info-circle text-orange-500 mr-2"></i>
                    Instructions pour les clients
                </h2>
                <p class="text-sm text-gray-500 mb-3">
                    Ces instructions aideront vos clients à récupérer leur commande facilement.
                </p>
                <div class="mb-3 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-600 mb-2"><strong>Exemples d'instructions utiles :</strong></p>
                    <ul class="text-xs text-gray-500 space-y-1">
                        <li>• "Sonnez au portail, nous vous apportons la commande"</li>
                        <li>• "Appelez au 06 XX XX XX XX à votre arrivée"</li>
                        <li>• "Livraison sans contact possible sur demande"</li>
                        <li>• "Stationnement gratuit devant le restaurant"</li>
                    </ul>
                </div>
                <textarea name="food_delivery_instructions" 
                          rows="3"
                          placeholder="Ex: Sonnez au portail, appelez à l'arrivée, livraison sans contact possible..."
                          class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-orange-500 focus:border-orange-500">{{ old('food_delivery_instructions', $prestataire->food_delivery_instructions) }}</textarea>
                <p class="text-xs text-gray-500 mt-2">Ces instructions seront affichées au client lors de la commande</p>
            </div>

            {{-- Aperçu des frais - uniquement si interne ou both --}}
            <div id="deliveryFeePreview" class="bg-gradient-to-r from-orange-500 to-amber-500 rounded-2xl shadow-lg p-6 text-white {{ $showDeliveryParams ? '' : 'hidden' }}">
                <h2 class="text-lg font-semibold mb-2 flex items-center">
                    <i class="fas fa-calculator mr-2"></i>
                    Aperçu des frais de livraison
                </h2>
                <p class="text-white/80 text-sm mb-4">
                    Voici ce que paieront vos clients selon la distance. Les valeurs se mettent à jour automatiquement.
                </p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div class="bg-white/20 backdrop-blur rounded-lg p-3">
                        <p class="text-2xl font-bold" id="preview1km">{{ number_format(($prestataire->food_delivery_base_fee ?? 3) + (1 * ($prestataire->food_delivery_fee_per_km ?? 0.5)), 2) }} €</p>
                        <p class="text-sm opacity-80">à 1 km</p>
                    </div>
                    <div class="bg-white/20 backdrop-blur rounded-lg p-3">
                        <p class="text-2xl font-bold" id="preview3km">{{ number_format(($prestataire->food_delivery_base_fee ?? 3) + (3 * ($prestataire->food_delivery_fee_per_km ?? 0.5)), 2) }} €</p>
                        <p class="text-sm opacity-80">à 3 km</p>
                    </div>
                    <div class="bg-white/20 backdrop-blur rounded-lg p-3">
                        <p class="text-2xl font-bold" id="preview5km">{{ number_format(($prestataire->food_delivery_base_fee ?? 3) + (5 * ($prestataire->food_delivery_fee_per_km ?? 0.5)), 2) }} €</p>
                        <p class="text-sm opacity-80">à 5 km</p>
                    </div>
                    <div class="bg-white/20 backdrop-blur rounded-lg p-3">
                        <p class="text-2xl font-bold" id="preview10km">{{ number_format(($prestataire->food_delivery_base_fee ?? 3) + (10 * ($prestataire->food_delivery_fee_per_km ?? 0.5)), 2) }} €</p>
                        <p class="text-sm opacity-80">à 10 km</p>
                    </div>
                </div>
            </div>

            {{-- Info pour livraison externe --}}
            <div id="externalDeliveryInfo" class="bg-gradient-to-r from-blue-500 to-indigo-500 rounded-2xl shadow-lg p-6 text-white {{ ($prestataire->food_delivery_enabled && ($prestataire->delivery_mode ?? 'both') === 'external') ? '' : 'hidden' }}">
                <h2 class="text-lg font-semibold mb-2 flex items-center">
                    <i class="fas fa-motorcycle mr-2"></i>
                    Livraison externe activée
                </h2>
                <p class="text-white/90 text-sm">
                    Vous avez choisi d'utiliser les livreurs indépendants de la plateforme. Les frais de livraison sont calculés automatiquement par la plateforme en fonction de la distance et des tarifs des livreurs disponibles.
                </p>
                <div class="mt-4 p-3 bg-white/20 rounded-lg">
                    <p class="text-sm"><i class="fas fa-check-circle mr-2"></i>Pas de gestion de tarifs à faire de votre côté</p>
                    <p class="text-sm mt-1"><i class="fas fa-check-circle mr-2"></i>Les livreurs sont automatiquement assignés</p>
                    <p class="text-sm mt-1"><i class="fas fa-check-circle mr-2"></i>Suivi en temps réel pour vous et vos clients</p>
                </div>
            </div>

            {{-- Bouton de sauvegarde --}}
            <div class="flex justify-end">
                <button type="submit" 
                        class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-orange-500 to-amber-500 text-white font-semibold rounded-xl shadow-lg hover:from-orange-600 hover:to-amber-600 transition-all transform hover:scale-105">
                    <i class="fas fa-save mr-2"></i>
                    Enregistrer les paramètres
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function toggleModeStyle(checkbox) {
        const label = checkbox.closest('label');
        const checkIcon = label.querySelector('.w-6');
        
        if (checkbox.checked) {
            label.classList.remove('border-gray-200', 'hover:border-gray-300');
            label.classList.add('border-orange-500', 'bg-orange-50');
            if (checkIcon) {
                checkIcon.classList.remove('border-gray-300');
                checkIcon.classList.add('border-orange-500', 'bg-orange-500');
                checkIcon.innerHTML = '<i class="fas fa-check text-white text-xs"></i>';
            }
        } else {
            label.classList.add('border-gray-200', 'hover:border-gray-300');
            label.classList.remove('border-orange-500', 'bg-orange-50');
            if (checkIcon) {
                checkIcon.classList.add('border-gray-300');
                checkIcon.classList.remove('border-orange-500', 'bg-orange-500');
                checkIcon.innerHTML = '';
            }
        }
    }

    function toggleDeliverySettings() {
        const deliveryCheckbox = document.querySelector('input[name="food_delivery_enabled"]');
        const deliveryEnabled = deliveryCheckbox ? deliveryCheckbox.checked : false;
        const modeSection = document.getElementById('deliveryModeSection');
        const scheduleDiv = document.getElementById('deliverySchedule');
        
        // Toujours afficher/masquer le choix du mode quand livraison activée/désactivée
        if (deliveryEnabled) {
            if (modeSection) modeSection.classList.remove('hidden');
            if (scheduleDiv) scheduleDiv.classList.remove('hidden');
        } else {
            if (modeSection) modeSection.classList.add('hidden');
            if (scheduleDiv) scheduleDiv.classList.add('hidden');
        }
        
        // Appeler toggleDeliveryMode pour gérer l'affichage des paramètres de tarification
        toggleDeliveryMode();
    }

    function buildDeliverySlotRow(dayKey, slotIndex, startValue = '', endValue = '') {
        return `
            <div class="rounded-xl border border-gray-200 bg-white p-3" data-slot-row>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-4 sm:items-end">
                    <div>
                        <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Debut</label>
                        <input type="time"
                               data-field="start"
                               name="delivery_schedule[${dayKey}][slots][${slotIndex}][start]"
                               value="${startValue}"
                               class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm">
                    </div>
                    <div class="hidden text-center text-gray-400 sm:block sm:pb-3">a</div>
                    <div>
                        <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Fin</label>
                        <input type="time"
                               data-field="end"
                               name="delivery_schedule[${dayKey}][slots][${slotIndex}][end]"
                               value="${endValue}"
                               class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm">
                    </div>
                    <div class="sm:pt-6">
                        <button type="button"
                                data-remove-slot
                                onclick="removeDeliverySlot(this)"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs font-medium text-red-600 transition hover:bg-red-100 sm:w-auto">
                            <i class="fas fa-trash-alt mr-2 text-xs"></i>
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    function getScheduleSlotsContainer(dayKey) {
        return document.querySelector(`[data-day-slots="${dayKey}"]`);
    }

    function getScheduleDayCheckbox(dayKey) {
        return document.querySelector(`input[name="delivery_schedule[${dayKey}][enabled]"]`);
    }

    function updateDeliverySlotButtons(dayKey) {
        const container = getScheduleSlotsContainer(dayKey);
        if (!container) {
            return;
        }

        const rows = Array.from(container.querySelectorAll('[data-slot-row]'));
        rows.forEach((row) => {
            const button = row.querySelector('[data-remove-slot]');
            if (!button) {
                return;
            }

            const disabled = rows.length <= 1;
            button.disabled = disabled;
            button.classList.toggle('opacity-40', disabled);
            button.classList.toggle('cursor-not-allowed', disabled);
        });
    }

    function refreshDeliverySlotIndexes(dayKey) {
        const container = getScheduleSlotsContainer(dayKey);
        if (!container) {
            return;
        }

        Array.from(container.querySelectorAll('[data-slot-row]')).forEach((row, slotIndex) => {
            row.querySelectorAll('input[type="time"]').forEach((input) => {
                input.name = `delivery_schedule[${dayKey}][slots][${slotIndex}][${input.dataset.field}]`;
            });
        });

        updateDeliverySlotButtons(dayKey);
    }

    function toggleScheduleDay(dayKey) {
        const checkbox = getScheduleDayCheckbox(dayKey);
        const container = getScheduleSlotsContainer(dayKey);
        const disabledNote = document.getElementById(`delivery-schedule-disabled-${dayKey}`);
        const enabled = checkbox ? checkbox.checked : false;

        if (container) {
            container.classList.toggle('hidden', !enabled);
            container.querySelectorAll('input[type="time"]').forEach((input) => {
                input.disabled = !enabled;
            });
        }

        if (disabledNote) {
            disabledNote.classList.toggle('hidden', enabled);
        }

        updateDeliverySlotButtons(dayKey);
    }

    function addDeliverySlot(dayKey) {
        const checkbox = getScheduleDayCheckbox(dayKey);
        const container = getScheduleSlotsContainer(dayKey);

        if (!container) {
            return;
        }

        if (checkbox && !checkbox.checked) {
            checkbox.checked = true;
        }

        const slotIndex = container.querySelectorAll('[data-slot-row]').length;
        container.insertAdjacentHTML('beforeend', buildDeliverySlotRow(dayKey, slotIndex));
        toggleScheduleDay(dayKey);
        refreshDeliverySlotIndexes(dayKey);
    }

    function removeDeliverySlot(button) {
        const row = button.closest('[data-slot-row]');
        const container = row ? row.closest('[data-day-slots]') : null;

        if (!row || !container) {
            return;
        }

        const dayKey = container.dataset.daySlots;
        const rows = container.querySelectorAll('[data-slot-row]');

        if (rows.length <= 1) {
            return;
        }

        row.remove();
        refreshDeliverySlotIndexes(dayKey);
    }

    function toggleDeliveryMode() {
        const deliveryCheckbox = document.querySelector('input[name="food_delivery_enabled"]');
        const deliveryEnabled = deliveryCheckbox ? deliveryCheckbox.checked : false;
        const checkedRadio = document.querySelector('input[name="delivery_mode"]:checked');
        const mode = checkedRadio ? checkedRadio.value : 'both';
        const externalOptions = document.getElementById('externalDriverOptions');
        const settingsDiv = document.getElementById('deliverySettings');
        const previewDiv = document.getElementById('deliveryFeePreview');
        const externalInfoDiv = document.getElementById('externalDeliveryInfo');
        
        // Update radio button styles
        document.querySelectorAll('input[name="delivery_mode"]').forEach(radio => {
            const label = radio.closest('label');
            const checkIcon = label.querySelector('.w-5');
            
            if (radio.checked) {
                label.classList.add('border-orange-500', 'bg-orange-50');
                label.classList.remove('border-gray-200');
                if (checkIcon) {
                    checkIcon.classList.add('border-orange-500', 'bg-orange-500');
                    checkIcon.classList.remove('border-gray-300');
                    checkIcon.innerHTML = '<i class="fas fa-check text-white text-xs"></i>';
                }
            } else {
                label.classList.remove('border-orange-500', 'bg-orange-50');
                label.classList.add('border-gray-200');
                if (checkIcon) {
                    checkIcon.classList.remove('border-orange-500', 'bg-orange-500');
                    checkIcon.classList.add('border-gray-300');
                    checkIcon.innerHTML = '';
                }
            }
        });
        
        // Show/hide external driver options (pour external et both)
        if (externalOptions) {
            if (deliveryEnabled && (mode === 'external' || mode === 'both')) {
                externalOptions.classList.remove('hidden');
            } else {
                externalOptions.classList.add('hidden');
            }
        }
        
        // Show/hide delivery tarification settings (pour internal et both seulement)
        // Si mode = external, le prestataire ne gère pas les tarifs, c'est la plateforme
        if (settingsDiv) {
            if (deliveryEnabled && (mode === 'internal' || mode === 'both')) {
                settingsDiv.classList.remove('hidden');
            } else {
                settingsDiv.classList.add('hidden');
            }
        }
        
        // Show/hide fee preview (pour internal et both)
        if (previewDiv) {
            if (deliveryEnabled && (mode === 'internal' || mode === 'both')) {
                previewDiv.classList.remove('hidden');
            } else {
                previewDiv.classList.add('hidden');
            }
        }
        
        // Show/hide external info (pour external uniquement)
        if (externalInfoDiv) {
            if (deliveryEnabled && mode === 'external') {
                externalInfoDiv.classList.remove('hidden');
            } else {
                externalInfoDiv.classList.add('hidden');
            }
        }
    }

    // Mise à jour de l'aperçu des frais en temps réel
    function updateFeePreview() {
        const baseFeeInput = document.querySelector('input[name="food_delivery_base_fee"]');
        const feePerKmInput = document.querySelector('input[name="food_delivery_fee_per_km"]');
        
        if (!baseFeeInput || !feePerKmInput) return;
        
        const baseFee = parseFloat(baseFeeInput.value) || 0;
        const feePerKm = parseFloat(feePerKmInput.value) || 0;
        
        const preview1 = document.getElementById('preview1km');
        const preview3 = document.getElementById('preview3km');
        const preview5 = document.getElementById('preview5km');
        const preview10 = document.getElementById('preview10km');
        
        if (preview1) preview1.textContent = (baseFee + 1 * feePerKm).toFixed(2) + ' €';
        if (preview3) preview3.textContent = (baseFee + 3 * feePerKm).toFixed(2) + ' €';
        if (preview5) preview5.textContent = (baseFee + 5 * feePerKm).toFixed(2) + ' €';
        if (preview10) preview10.textContent = (baseFee + 10 * feePerKm).toFixed(2) + ' €';
    }

    // Initialiser au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        // Écouter les changements pour l'aperçu des frais
        const baseFeeInput = document.querySelector('input[name="food_delivery_base_fee"]');
        const feePerKmInput = document.querySelector('input[name="food_delivery_fee_per_km"]');
        
        if (baseFeeInput) baseFeeInput.addEventListener('input', updateFeePreview);
        if (feePerKmInput) feePerKmInput.addEventListener('input', updateFeePreview);

        document.querySelectorAll('[data-day-slots]').forEach((container) => {
            refreshDeliverySlotIndexes(container.dataset.daySlots);
            toggleScheduleDay(container.dataset.daySlots);
        });
        
        // Initialiser l'état correct au chargement
        toggleDeliveryMode();
    });
</script>
@endpush
@endsection
