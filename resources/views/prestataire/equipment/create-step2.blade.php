@extends('layouts.app')

@section('title', 'Ajouter un équipement - Étape 2')

@section('content')
<div class="bg-green-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-green-900 mb-2">Ajouter un équipement</h1>
                <p class="text-base sm:text-lg text-green-700">Étape 2 : Tarifs et conditions</p>
            </div>

            <!-- Barre de progression -->
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 sm:mb-4 space-y-2 sm:space-y-0">
                    <h2 class="text-base sm:text-lg font-semibold text-green-900">Processus de création</h2>
                    <span class="text-xs sm:text-sm text-green-600">Étape 2 sur 4</span>
                </div>
                <div class="flex items-center space-x-1 sm:space-x-2 lg:space-x-4 overflow-x-auto">
                    <div class="flex items-center flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-medium">
                            ✓
                        </div>
                        <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-900 hidden sm:inline">Informations de base</span>
                    </div>
                    <div class="flex-1 h-1 bg-green-600 rounded min-w-4"></div>
                    <div class="flex items-center flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-medium">
                            2
                        </div>
                        <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-900 hidden sm:inline">Tarifs et conditions</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 rounded min-w-4"></div>
                    <div class="flex items-center flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-medium">
                            3
                        </div>
                        <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-gray-500 hidden sm:inline">Photos</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 rounded min-w-4"></div>
                    <div class="flex items-center flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-medium">
                            4
                        </div>
                        <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-gray-500 hidden sm:inline">Localisation et résumé</span>
                    </div>
                </div>
                <!-- Labels mobiles -->
                <div class="flex justify-between mt-2 sm:hidden text-xs text-gray-600">
                    <span class="text-green-600 font-medium">Info</span>
                    <span class="text-green-600 font-medium">Tarifs</span>
                    <span>Photos</span>
                    <span>Résumé</span>
                </div>
            </div>

            <!-- Formulaire Étape 2 -->
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 space-y-3 sm:space-y-0">
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('prestataire.equipment.create.step1') }}" class="text-green-600 hover:text-green-900 transition-colors duration-200 p-1">
                            <i class="fas fa-arrow-left text-base sm:text-lg"></i>
                        </a>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-green-900">Tarifs et conditions</h2>
                            <p class="text-xs sm:text-sm text-green-700">Définissez vos prix et les conditions de location</p>
                        </div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong class="font-bold">Oups!</strong>
                        <span class="block sm:inline">Quelque chose s'est mal passé.</span>
                        <ul class="mt-2">
                            @foreach ($errors->all() as $error)
                                <li>- {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('prestataire.equipment.store.step2') }}" method="POST">
                    @csrf
                    
                    <div class="space-y-6 sm:space-y-8">
                        <!-- Tarifs principaux -->
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-green-900 mb-3 sm:mb-4 border-b border-green-200 pb-2">Tarifs principaux</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <div>
                                    <label for="price_per_day" class="block text-sm font-medium text-green-700 mb-2">Prix par jour (€) *</label>
                                    <input type="number" name="price_per_day" id="price_per_day" value="{{ old('price_per_day', session('equipment_step2.price_per_day')) }}" required min="0" step="0.01" placeholder="50" class="w-full px-3 py-2 text-sm sm:text-base border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('price_per_day') border-red-500 @enderror">
                                    @error('price_per_day')
                                        <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="security_deposit" class="block text-sm font-medium text-green-700 mb-2">Caution (€) *</label>
                                    <input type="number" name="security_deposit" id="security_deposit" value="{{ old('security_deposit', session('equipment_step2.security_deposit')) }}" required min="0" step="0.01" placeholder="100" class="w-full px-3 py-2 text-sm sm:text-base border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('security_deposit') border-red-500 @enderror">
                                    @error('security_deposit')
                                        <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Tarifs optionnels -->
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-green-900 mb-3 sm:mb-4 border-b border-green-200 pb-2">Tarifs optionnels</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                                <div>
                                    <label for="price_per_hour" class="block text-sm font-medium text-green-700 mb-2">Prix par heure (€)</label>
                                    <input type="number" name="price_per_hour" id="price_per_hour" step="0.01" min="0" class="w-full px-3 py-2 text-sm sm:text-base border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="10.00" value="{{ old('price_per_hour', session('equipment_step2.price_per_hour')) }}">
                                </div>
                                <div>
                                    <label for="price_per_week" class="block text-sm font-medium text-green-700 mb-2">Prix par semaine (€)</label>
                                    <input type="number" name="price_per_week" id="price_per_week" step="0.01" min="0" class="w-full px-3 py-2 text-sm sm:text-base border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="300.00" value="{{ old('price_per_week', session('equipment_step2.price_per_week')) }}">
                                </div>
                                <div class="sm:col-span-2 lg:col-span-1">
                                    <label for="price_per_month" class="block text-sm font-medium text-green-700 mb-2">Prix par mois (€)</label>
                                    <input type="number" name="price_per_month" id="price_per_month" step="0.01" min="0" class="w-full px-3 py-2 text-sm sm:text-base border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="1000.00" value="{{ old('price_per_month', session('equipment_step2.price_per_month')) }}">
                                </div>
                            </div>
                        </div>

                        <!-- Options et conditions -->
                        <div>
                            <h3 class="text-lg font-semibold text-green-900 mb-4 border-b border-green-200 pb-2">Options et conditions</h3>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <input id="delivery_included" name="delivery_included" type="checkbox" value="1" {{ old('delivery_included', session('equipment_step2.delivery_included')) ? 'checked' : '' }} class="h-4 w-4 text-green-600 focus:ring-green-500 border-green-300 rounded">
                                    <label for="delivery_included" class="ml-3 block text-sm font-medium text-green-700">Livraison incluse dans le prix</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="license_required" id="license_required" value="1" {{ old('license_required', session('equipment_step2.license_required')) ? 'checked' : '' }} class="h-4 w-4 text-green-600 focus:ring-green-500 border-green-300 rounded">
                                    <label for="license_required" class="ml-3 block text-sm font-medium text-green-700">Permis ou certification requis</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="is_available" id="is_available" value="1" {{ old('is_available', session('equipment_step2.is_available', true)) ? 'checked' : '' }} class="h-4 w-4 text-green-600 focus:ring-green-500 border-green-300 rounded">
                                    <label for="is_available" class="ml-3 block text-sm font-medium text-green-700">Équipement disponible immédiatement</label>
                                </div>

                                <div>
                                    <label for="condition" class="block text-sm font-medium text-green-700 mb-2">État de l'équipement</label>
                                    <select name="condition" id="condition" class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <option value="">Sélectionner l'état</option>
                                        <option value="excellent" {{ old('condition', session('equipment_step2.condition')) == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                        <option value="very_good" {{ old('condition', session('equipment_step2.condition')) == 'very_good' ? 'selected' : '' }}>Très bon</option>
                                        <option value="good" {{ old('condition', session('equipment_step2.condition')) == 'good' ? 'selected' : '' }}>Bon</option>
                                        <option value="fair" {{ old('condition', session('equipment_step2.condition')) == 'fair' ? 'selected' : '' }}>Correct</option>
                                        <option value="poor" {{ old('condition', session('equipment_step2.condition')) == 'poor' ? 'selected' : '' }}>Mauvais</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="rental_conditions" class="block text-sm font-medium text-green-700 mb-2">Conditions de location</label>
                                    <textarea name="rental_conditions" id="rental_conditions" rows="3" class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Conditions particulières, restrictions d'usage...">{{ old('rental_conditions', session('equipment_step2.rental_conditions')) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Période de disponibilité -->
                        <div>
                            <h3 class="text-lg font-semibold text-green-900 mb-4 border-b border-green-200 pb-2">Période de disponibilité</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="available_from" class="block text-sm font-medium text-green-700 mb-2">Disponible à partir du</label>
                                    <input type="date" name="available_from" id="available_from" value="{{ old('available_from', session('equipment_step2.available_from')) }}" class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('available_from') border-red-500 @enderror">
                                    @error('available_from')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-green-500 text-xs mt-1">Laissez vide si disponible immédiatement</p>
                                </div>
                                
                                <div>
                                    <label for="available_until" class="block text-sm font-medium text-green-700 mb-2">Disponible jusqu'au</label>
                                    <input type="date" name="available_until" id="available_until" value="{{ old('available_until', session('equipment_step2.available_until')) }}" class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('available_until') border-red-500 @enderror">
                                    @error('available_until')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-green-500 text-xs mt-1">Laissez vide si pas de limite de temps</p>
                                </div>
                            </div>
                            
                            <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="text-sm text-green-700">
                                        <p class="font-medium mb-1">Information sur les dates de disponibilité :</p>
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>Ces dates définissent la période générale où votre équipement peut être loué</li>
                                            <li>Vous pourrez toujours bloquer des dates spécifiques plus tard</li>
                                            <li>Les clients ne pourront réserver que dans cette période</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center pt-4 sm:pt-6 border-t border-green-200 gap-3 sm:gap-4 mt-6 sm:mt-8">
                        <a href="{{ route('prestataire.equipment.create.step1') }}" class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg transition duration-200 font-medium text-center text-sm sm:text-base">
                            <i class="fas fa-arrow-left mr-2"></i>Précédent
                        </a>
                        
                        <button type="submit" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-6 sm:px-8 py-2.5 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-sm sm:text-base">
                            <span class="hidden sm:inline">Suivant : Photos</span>
                            <span class="sm:hidden">Suivant</span>
                            <i class="fas fa-arrow-right ml-2"></i>
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
document.addEventListener('DOMContentLoaded', function () {
    // Prevent form resubmission
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function() {
            // Disable the submit button to prevent double submission
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi...';
            }
        });
    }
});
</script>
@endpush
