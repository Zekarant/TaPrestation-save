@extends('layouts.app')

@section('content')
<div class="bg-blue-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-blue-900 mb-2">Créer un nouveau service</h1>
                <p class="text-base sm:text-lg text-blue-700">Étape 2 : Prix et Catégorie</p>
            </div>

            <!-- Indicateur d'étapes -->
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6 mb-4 sm:mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        <a href="{{ route('prestataire.services.create.step1') }}" class="text-blue-600 hover:text-blue-900 transition-colors duration-200">
                            <i class="fas fa-arrow-left text-lg sm:text-xl"></i>
                        </a>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-blue-900">Étape 2 sur 4</h2>
                            <p class="text-sm sm:text-base text-blue-700 hidden sm:block">Prix et Catégorie</p>
                        </div>
                    </div>
                </div>
                
                <!-- Barre de progression -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-1 sm:space-x-2 lg:space-x-4 w-full overflow-x-auto">
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                <i class="fas fa-check text-xs"></i>
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-600 hidden sm:inline">Informations</span>
                            <span class="ml-1 text-xs font-medium text-green-600 sm:hidden">Info</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 rounded min-w-4">
                            <div class="h-1 bg-blue-600 rounded" style="width: 50%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                2
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-blue-600 hidden sm:inline">Prix & Catégorie</span>
                            <span class="ml-1 text-xs font-medium text-blue-600 sm:hidden">Prix</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 rounded min-w-4">
                            <div class="h-1 bg-gray-200 rounded" style="width: 0%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                3
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-gray-500 hidden sm:inline">Photos</span>
                            <span class="ml-1 text-xs font-medium text-gray-500 sm:hidden">Photo</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 rounded min-w-4">
                            <div class="h-1 bg-gray-200 rounded" style="width: 0%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                4
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-gray-500 hidden sm:inline">Localisation</span>
                            <span class="ml-1 text-xs font-medium text-gray-500 sm:hidden">Lieu</span>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-400 p-3 sm:p-4 mb-4 sm:mb-6 rounded-r-lg" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Veuillez corriger les erreurs suivantes :</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('prestataire.services.create.step2.store') }}" id="step2Form">
                @csrf

                <!-- Prix -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
                    <h2 class="text-base sm:text-lg lg:text-xl font-bold text-blue-900 mb-3 sm:mb-4 border-b border-blue-200 pb-2">
                        <i class="fas fa-euro-sign text-green-600 mr-1 sm:mr-2 text-sm sm:text-base"></i>Prix du service
                    </h2>
                    <div id="price-fields-container" class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 lg:gap-6">
                        <div id="price-container">
                            <label for="price" class="block text-xs sm:text-sm font-medium text-blue-700 mb-1 sm:mb-2">Prix (€)</label>
                            <input type="number" id="price" name="price" value="{{ old('price', session('service_creation.step2.price')) }}" min="0" step="0.01" class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('price') border-red-500 @enderror">
                            @error('price')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="price_type" class="block text-xs sm:text-sm font-medium text-blue-700 mb-1 sm:mb-2">Type de tarification</label>
                            <select id="price_type" name="price_type" class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('price_type') border-red-500 @enderror">
                                <option value="">Sélectionnez un type</option>
                                <option value="fixed" {{ old('price_type', session('service_creation.step2.price_type')) == 'fixed' ? 'selected' : '' }}>Prix fixe</option>
                                <option value="hourly" {{ old('price_type', session('service_creation.step2.price_type')) == 'hourly' ? 'selected' : '' }}>Par heure</option>
                                <option value="daily" {{ old('price_type', session('service_creation.step2.price_type')) == 'daily' ? 'selected' : '' }}>Par jour</option>
                                <option value="quote" {{ old('price_type', session('service_creation.step2.price_type')) == 'quote' ? 'selected' : '' }}>Sur devis</option>
                            </select>
                            @error('price_type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Aperçu des gains après commission -->
                    <div class="mt-4">
                        <x-earnings-preview 
                            type="service" 
                            inputId="price" 
                            quantityInputId="duration"
                            :initialPrice="old('price', session('service_creation.step2.price'))" 
                            color="blue" 
                        />
                    </div>

                    <!-- Réservation directe (déplacé depuis l'étape 1) -->
                    <div class="mt-4 sm:mt-6">
                        <label for="reservable" class="inline-flex items-start">
                            <input id="reservable" type="checkbox" value="1" class="mt-0.5 rounded border-blue-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-offset-0 focus:ring-blue-200 focus:ring-opacity-50 flex-shrink-0" name="reservable" {{ old('reservable', session('service_creation.step2.reservable')) ? 'checked' : '' }}>
                            <span class="ml-2 text-xs sm:text-sm text-blue-700">
                                Activer la réservation directe pour ce service
                            </span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1">Si activé, vous devez configurer vos disponibilités.</p>
                    </div>

                    <!-- Message affiché quand "sur devis" est sélectionné -->
                    <div id="quote-info-container" class="hidden mt-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-file-invoice text-blue-500 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-semibold text-blue-800">Service sur devis</h3>
                                    <p class="mt-1 text-sm text-blue-700">
                                        Le prix sera établi après discussion avec le client. Les clients pourront vous contacter pour obtenir un devis personnalisé.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Champ dynamique pour le nombre d'heures/jours (apparaît pour hourly/daily) -->
                    <div id="quantity-container" class="mt-4 sm:mt-6" style="display: none;">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 lg:gap-6">
                            <div>
                                <label id="quantity-label" for="duration" class="block text-xs sm:text-sm font-medium text-blue-700 mb-1 sm:mb-2">Nombre d'heures</label>
                                <input type="number" id="duration" name="duration" min="1" value="{{ old('duration', session('service_creation.step2.duration')) }}" class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('duration') border-red-500 @enderror">
                                @error('duration')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Affichage du prix total (apparaît pour hourly/daily) -->
                    <div id="total-price-container" class="mt-4 sm:mt-6" style="display: none;">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm sm:text-base font-medium text-blue-700">Prix total estimé :</span>
                                <span id="total-price" class="text-lg sm:text-xl font-bold text-blue-900">0,00 €</span>
                            </div>
                        </div>
                    </div>

                    <!-- Durée estimée du service -->
                    <div id="estimated-duration-section" class="mt-4 sm:mt-6 border-t border-blue-200 pt-4">
                        <h3 class="text-sm sm:text-base font-semibold text-blue-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-clock text-indigo-600"></i>Durée estimée
                            <span class="relative group">
                                <svg class="w-4 h-4 text-gray-400 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none max-w-xs text-center">
                                    Permet aux clients de voir la durée<br>et aide à gérer les réservations
                                </span>
                            </span>
                        </h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
                            <div>
                                <label for="estimated_duration" class="block text-xs sm:text-sm font-medium text-blue-700 mb-1 sm:mb-2">Durée</label>
                                <input type="number" id="estimated_duration" name="estimated_duration" 
                                       min="1" max="999" step="1"
                                       value="{{ old('estimated_duration', session('service_creation.step2.estimated_duration', 1)) }}" 
                                       class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('estimated_duration') border-red-500 @enderror"
                                       placeholder="Ex: 2">
                                @error('estimated_duration')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="duration_unit" class="block text-xs sm:text-sm font-medium text-blue-700 mb-1 sm:mb-2">Unité</label>
                                <select id="duration_unit" name="duration_unit" 
                                        class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('duration_unit') border-red-500 @enderror">
                                    <option value="minutes" {{ old('duration_unit', session('service_creation.step2.duration_unit', 'hours')) == 'minutes' ? 'selected' : '' }}>Minutes</option>
                                    <option value="hours" {{ old('duration_unit', session('service_creation.step2.duration_unit', 'hours')) == 'hours' ? 'selected' : '' }}>Heures</option>
                                    <option value="days" {{ old('duration_unit', session('service_creation.step2.duration_unit')) == 'days' ? 'selected' : '' }}>Jours</option>
                                </select>
                                @error('duration_unit')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="buffer_time" class="block text-xs sm:text-sm font-medium text-blue-700 mb-1 sm:mb-2">
                                    Temps tampon (min)
                                    <span class="relative group inline-block ml-1">
                                        <svg class="w-3.5 h-3.5 text-gray-400 cursor-help inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none">
                                            Temps entre deux réservations
                                        </span>
                                    </span>
                                </label>
                                <input type="number" id="buffer_time" name="buffer_time" 
                                       min="0" max="120" step="5"
                                       value="{{ old('buffer_time', session('service_creation.step2.buffer_time', 15)) }}" 
                                       class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('buffer_time') border-red-500 @enderror"
                                       placeholder="Ex: 15">
                                @error('buffer_time')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Acompte et exigence de paiement -->
                    <div id="payment-options-section" class="mt-4 sm:mt-6 border-t border-blue-200 pt-4">
                        @php($cashOnlyMode = function_exists('cash_only_mode') && cash_only_mode())
                        @if($cashOnlyMode)
                            <input type="hidden" name="payment_requirement" value="none">
                            <input type="hidden" name="deposit_percentage" value="0">

                            <h3 class="text-sm sm:text-base font-semibold text-blue-800 mb-3 flex items-center gap-2">
                                <i class="fas fa-money-bill-wave text-green-600"></i>Paiement client
                            </h3>

                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4 text-sm text-green-800">
                                <p class="font-semibold">Paiement en ligne désactivé</p>
                                <p class="mt-1">Les réservations se feront sans paiement en ligne. Vos clients régleront directement en espèces ou avec vous au moment du rendez-vous.</p>
                            </div>
                        @else
                        <h3 class="text-sm sm:text-base font-semibold text-blue-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-hand-holding-usd text-green-600"></i>Options de paiement
                            <span class="relative group">
                                <svg class="w-4 h-4 text-gray-400 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-50 pointer-events-none max-w-xs text-center">
                                    Choisissez comment vos clients doivent payer :<br>
                                    • Aucun = réservation gratuite<br>
                                    • Acompte = % à la réservation<br>
                                    • Complet = 100% à l'avance
                                </span>
                            </span>
                        </h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 lg:gap-6 mb-4">
                            <div>
                                <label for="deposit_percentage" class="block text-xs sm:text-sm font-medium text-blue-700 mb-1 sm:mb-2">
                                    Pourcentage d'acompte (%)
                                </label>
                                <input type="number" id="deposit_percentage" name="deposit_percentage" 
                                       min="0" max="100" step="1"
                                       value="{{ old('deposit_percentage', session('service_creation.step2.deposit_percentage', 0)) }}" 
                                       class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('deposit_percentage') border-red-500 @enderror"
                                       placeholder="Ex: 30">
                                <p class="text-xs text-gray-500 mt-1">Laissez 0 si aucun acompte n'est requis</p>
                                @error('deposit_percentage')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="payment_requirement" class="block text-xs sm:text-sm font-medium text-blue-700 mb-1 sm:mb-2">
                                    Paiement requis pour valider
                                </label>
                                <select id="payment_requirement" name="payment_requirement" 
                                        class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('payment_requirement') border-red-500 @enderror">
                                    <option value="none" {{ old('payment_requirement', session('service_creation.step2.payment_requirement', 'none')) == 'none' ? 'selected' : '' }}>
                                        Aucun (réservation sans paiement immédiat)
                                    </option>
                                    <option value="deposit" {{ old('payment_requirement', session('service_creation.step2.payment_requirement')) == 'deposit' ? 'selected' : '' }}>
                                        Acompte obligatoire pour valider
                                    </option>
                                    <option value="full" {{ old('payment_requirement', session('service_creation.step2.payment_requirement')) == 'full' ? 'selected' : '' }}>
                                        Paiement complet obligatoire pour valider
                                    </option>
                                </select>
                                @error('payment_requirement')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-xs sm:text-sm text-yellow-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Note :</strong> Si vous choisissez "Acompte obligatoire", assurez-vous de définir un pourcentage d'acompte supérieur à 0.
                        </div>

                        <div class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-3 sm:p-4">
                            <h4 class="text-xs sm:text-sm font-semibold text-amber-800 mb-3">
                                <i class="fas fa-undo-alt mr-1"></i>Politique d'annulation client (paiement en ligne)
                            </h4>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <div>
                                    <label for="cancellation_hours" class="block text-xs sm:text-sm font-medium text-amber-700 mb-1 sm:mb-2">
                                        Délai d'annulation (heures)
                                    </label>
                                    <input
                                        type="number"
                                        id="cancellation_hours"
                                        name="cancellation_hours"
                                        min="0"
                                        max="720"
                                        step="1"
                                        value="{{ old('cancellation_hours', session('service_creation.step2.cancellation_hours', 24)) }}"
                                        class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-amber-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('cancellation_hours') border-red-500 @enderror"
                                        placeholder="Ex: 24"
                                    >
                                    @error('cancellation_hours')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="cancellation_refund_percentage" class="block text-xs sm:text-sm font-medium text-amber-700 mb-1 sm:mb-2">
                                        Remboursement dans les délais (%)
                                    </label>
                                    <input
                                        type="number"
                                        id="cancellation_refund_percentage"
                                        name="cancellation_refund_percentage"
                                        min="0"
                                        max="100"
                                        step="1"
                                        value="{{ old('cancellation_refund_percentage', session('service_creation.step2.cancellation_refund_percentage', 100)) }}"
                                        class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-amber-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('cancellation_refund_percentage') border-red-500 @enderror"
                                        placeholder="Ex: 100"
                                    >
                                    @error('cancellation_refund_percentage')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <p class="text-[11px] sm:text-xs text-amber-800 mt-2">
                                Si le client annule moins de ce délai, le remboursement est à 0%.
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Catégorie -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
                    <h2 class="text-base sm:text-lg lg:text-xl font-bold text-blue-900 mb-3 sm:mb-4 border-b border-blue-200 pb-2">
                        <i class="fas fa-tags text-purple-600 mr-1 sm:mr-2 text-sm sm:text-base"></i>Catégorie du service
                    </h2>
                    
                    <div class="space-y-3 sm:space-y-4">
                        <div>
                            <label for="category_id" class="block text-xs sm:text-sm font-medium text-blue-700 mb-1 sm:mb-2">Catégorie principale *</label>
                            <select id="category_id" name="category_id" required class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('category_id') border-red-500 @enderror">
                                <option value="">Sélectionnez une catégorie principale</option>
                                @foreach($categories->whereNull('parent_id') as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', session('service_creation.step2.category_id')) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div id="subcategory-group" style="display: none;">
                            <label for="subcategory_id" class="block text-xs sm:text-sm font-medium text-blue-700 mb-1 sm:mb-2">Sous-catégorie</label>
                            <select id="subcategory_id" name="subcategory_id" class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled>
                                <option value="">Veuillez d'abord choisir une catégorie</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Matériel requis -->
                @if(isset($equipment) && $equipment->count() > 0)
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
                    <h2 class="text-base sm:text-lg lg:text-xl font-bold text-blue-900 mb-3 sm:mb-4 border-b border-blue-200 pb-2">
                        <i class="fas fa-tools text-orange-600 mr-1 sm:mr-2 text-sm sm:text-base"></i>Matériel requis
                    </h2>
                    
                    <div class="space-y-3 sm:space-y-4">
                        <p class="text-sm text-gray-600 mb-2">Sélectionnez le matériel nécessaire pour ce service (optionnel) :</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($equipment as $item)
                                <div class="flex items-start space-x-2 p-2 border border-gray-200 rounded-lg hover:bg-gray-50">
                                    <input type="checkbox" id="equipment_{{ $item->id }}" name="equipment_ids[]" value="{{ $item->id }}" 
                                        {{ (is_array(old('equipment_ids', session('service_creation.step2.equipment_ids'))) && in_array($item->id, old('equipment_ids', session('service_creation.step2.equipment_ids')))) ? 'checked' : '' }}
                                        class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="equipment_{{ $item->id }}" class="text-sm text-gray-700 cursor-pointer w-full">
                                        <span class="font-medium">{{ $item->name }}</span>
                                        @if($item->quantity > 0)
                                            <span class="text-xs text-gray-500 block">Dispo: {{ $item->quantity }}</span>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row justify-between items-center pt-4 sm:pt-6 lg:pt-8 border-t border-blue-200 space-y-3 sm:space-y-0">
                    <a href="{{ route('prestataire.services.create.step1') }}" class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-3 sm:px-4 lg:px-6 py-2.5 sm:py-3 rounded-lg transition duration-200 font-medium text-center text-xs sm:text-sm lg:text-base">
                        <i class="fas fa-arrow-left mr-1 sm:mr-2 text-xs sm:text-sm"></i><span class="hidden xs:inline">Précédent</span><span class="xs:hidden">Retour</span>
                    </a>
                    <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 sm:px-6 lg:px-8 py-2.5 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-xs sm:text-sm lg:text-base">
                        <span class="hidden xs:inline">Suivant : Photos</span><span class="xs:hidden">Suivant</span><i class="fas fa-arrow-right ml-1 sm:ml-2 text-xs sm:text-sm"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Gestion des catégories
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    const subcategoryGroup = document.getElementById('subcategory-group');
    
    // Prevent form resubmission
    const form = document.getElementById('step2Form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Check availability before submitting
            if (!checkAvailability()) {
                e.preventDefault();
                return false;
            }
            
            // Disable the submit button to prevent double submission
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi...';
            }
        });
    }
    
    // Gestion du prix dynamique
    const priceInput = document.getElementById('price');
    const priceTypeSelect = document.getElementById('price_type');
    const priceContainer = document.getElementById('price-container');
    const priceFieldsContainer = document.getElementById('price-fields-container');
    const quoteInfoContainer = document.getElementById('quote-info-container');
    const quantityContainer = document.getElementById('quantity-container');
    const quantityLabel = document.getElementById('quantity-label');
    const quantityInput = document.getElementById('duration');
    const totalPriceContainer = document.getElementById('total-price-container');
    const totalPriceDisplay = document.getElementById('total-price');
    
    // Conteneurs pour durée estimée et options de paiement
    const estimatedDurationSection = document.getElementById('estimated-duration-section');
    const paymentOptionsSection = document.getElementById('payment-options-section');
    
    // Availability warning element
    const availabilityWarning = document.createElement('div');
    availabilityWarning.id = 'availability-warning';
    availabilityWarning.className = 'mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800 text-sm hidden';
    availabilityWarning.innerHTML = `
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium">Attention : Durée dépassant vos disponibilités</h3>
                <div class="mt-2 text-sm">
                    <p>La durée que vous avez sélectionnée dépasse vos disponibilités actuelles.</p>
                    <p class="mt-1">Veuillez augmenter vos disponibilités pour offrir ce service.</p>
                </div>
            </div>
        </div>
    `;
    totalPriceContainer.parentNode.insertBefore(availabilityWarning, totalPriceContainer.nextSibling);
    
    // Get availability data from the server
    const availabilities = @json($availabilities ?? []);
    
    // Fonction pour mettre à jour le prix total
    function updateTotalPrice() {
        const price = parseFloat(priceInput.value) || 0;
        const quantity = parseFloat(quantityInput.value) || 0;
        const priceType = priceTypeSelect.value;
        
        // Si "sur devis" est sélectionné, cacher les champs de prix, durée et paiement
        if (priceType === 'quote') {
            priceContainer.style.display = 'none';
            quantityContainer.style.display = 'none';
            totalPriceContainer.style.display = 'none';
            quoteInfoContainer.classList.remove('hidden');
            availabilityWarning.classList.add('hidden');
            
            // Cacher durée estimée et options de paiement
            if (estimatedDurationSection) estimatedDurationSection.style.display = 'none';
            if (paymentOptionsSection) paymentOptionsSection.style.display = 'none';
            
            // Réinitialiser le prix à 0 pour "sur devis"
            priceInput.value = '';
            quantityInput.value = '';
            return;
        }
        
        // Sinon afficher les champs normalement
        priceContainer.style.display = 'block';
        quoteInfoContainer.classList.add('hidden');
        
        // Afficher durée estimée et options de paiement
        if (estimatedDurationSection) estimatedDurationSection.style.display = 'block';
        if (paymentOptionsSection) paymentOptionsSection.style.display = 'block';
        
        // Afficher le conteneur de quantité uniquement pour les types "hourly" ou "daily"
        if (priceType === 'hourly' || priceType === 'daily') {
            quantityContainer.style.display = 'block';
            totalPriceContainer.style.display = 'block';
            
            // Mettre à jour le label en fonction du type
            quantityLabel.textContent = priceType === 'hourly' ? 'Nombre d\'heures' : 'Nombre de jours';
            
            // Calculer et afficher le prix total
            const total = price * quantity;
            totalPriceDisplay.textContent = total.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
            
            // Check availability
            checkAvailability();
        } else {
            quantityContainer.style.display = 'none';
            totalPriceContainer.style.display = 'none';
            availabilityWarning.classList.add('hidden');
        }
    }
    
    // Fonction pour vérifier la disponibilité
    function checkAvailability() {
        const priceType = priceTypeSelect.value;
        const quantity = parseFloat(quantityInput.value) || 0;
        
        // Only check for hourly services
        if (priceType === 'hourly' && quantity > 0) {
            // Find the maximum available hours in a single day
            let maxAvailableHoursInADay = 0;
            
            // Check each availability entry
            availabilities.forEach(availability => {
                if (availability.is_active) {
                    // Parse start and end times
                    const startTime = new Date(`1970-01-01T${availability.start_time}`);
                    const endTime = new Date(`1970-01-01T${availability.end_time}`);
                    
                    // Calculate working minutes (subtracting break time if exists)
                    let workingMinutes = (endTime - startTime) / (1000 * 60);
                    
                    // Subtract break time if it exists
                    if (availability.break_start_time && availability.break_end_time) {
                        const breakStartTime = new Date(`1970-01-01T${availability.break_start_time}`);
                        const breakEndTime = new Date(`1970-01-01T${availability.break_end_time}`);
                        const breakMinutes = (breakEndTime - breakStartTime) / (1000 * 60);
                        workingMinutes -= breakMinutes;
                    }
                    
                    // Convert to hours
                    const availableHours = workingMinutes / 60;
                    
                    // Update maximum if this day has more availability
                    if (availableHours > maxAvailableHoursInADay) {
                        maxAvailableHoursInADay = availableHours;
                    }
                }
            });
            
            // Check if quantity exceeds the maximum available hours in a single day
            if (quantity > maxAvailableHoursInADay) {
                availabilityWarning.classList.remove('hidden');
                return false;
            } else {
                availabilityWarning.classList.add('hidden');
                return true;
            }
        }
        
        availabilityWarning.classList.add('hidden');
        return true;
    }
    
    // Écouter les changements sur les champs de prix, type et quantité
    priceInput.addEventListener('input', updateTotalPrice);
    priceTypeSelect.addEventListener('change', updateTotalPrice);
    quantityInput.addEventListener('input', updateTotalPrice);
    
    // Initialiser l'affichage au chargement de la page
    updateTotalPrice();
    
    function loadSubcategories(categoryId) {
        if (!categoryId) {
            subcategoryGroup.style.display = 'none';
            subcategorySelect.disabled = true;
            subcategorySelect.innerHTML = '<option value="">Veuillez d\'abord choisir une catégorie</option>';
            return;
        }
        
        fetch(`/api/categories/${categoryId}/subcategories`)
            .then(response => response.json())
            .then(subcategories => {
                subcategorySelect.innerHTML = '';
                
                if (subcategories.length === 0) {
                    subcategorySelect.innerHTML = '<option value="">Pas de sous-catégorie disponible</option>';
                    subcategorySelect.disabled = true;
                } else {
                    const selectedSubcategoryId = @json(old('subcategory_id', session('service_creation.step2.subcategory_id')));
                    subcategorySelect.innerHTML = '<option value="">Sélectionnez une sous-catégorie (optionnel)</option>';
                    subcategories.forEach(subcategory => {
                        const option = document.createElement('option');
                        option.value = subcategory.id;
                        option.textContent = subcategory.name;
                        if (selectedSubcategoryId && String(subcategory.id) === String(selectedSubcategoryId)) {
                            option.selected = true;
                        }
                        subcategorySelect.appendChild(option);
                    });
                    subcategorySelect.disabled = false;
                }
                
                subcategoryGroup.style.display = 'block';
            })
            .catch(error => {
                console.error('Erreur lors du chargement des sous-catégories:', error);
                subcategorySelect.innerHTML = '<option value="">Erreur de chargement</option>';
                subcategorySelect.disabled = true;
                subcategoryGroup.style.display = 'block';
            });
    }
    
    // Les catégories principales sont rendues côté serveur (filtrées type service).
    if (categorySelect) {
        // Gérer le changement de catégorie principale
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            loadSubcategories(categoryId);
        });
        
        // Si une catégorie est déjà sélectionnée (old input), charger les sous-catégories
        const selectedCategoryId = categorySelect.value;
        if (selectedCategoryId) {
            loadSubcategories(selectedCategoryId);
        }
    }
});
</script>
@endpush
