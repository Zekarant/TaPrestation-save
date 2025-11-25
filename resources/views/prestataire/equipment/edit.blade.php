@extends('layouts.app')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
      crossorigin="" />
@endpush

@section('title', 'Modifier l\'équipement - ' . $equipment->name)

@section('content')
<div class="bg-green-50">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-extrabold text-green-900 mb-2">Modifier l'équipement</h1>
                <p class="text-lg text-green-700">Modifiez les informations de votre équipement</p>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('prestataire.equipment.show', $equipment) }}" class="text-green-600 hover:text-green-900 transition-colors duration-200">
                            <i class="fas fa-arrow-left text-xl"></i>
                        </a>
                        <div>
                            <h2 class="text-xl font-bold text-green-900">Modification de l'équipement</h2>
                            <p class="text-green-700">Mettez à jour les informations de votre équipement</p>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg" role="alert">
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

            <form action="{{ route('prestataire.equipment.update', $equipment) }}" method="POST" enctype="multipart/form-data" id="equipment-form">
                @csrf
                @method('PUT')

                <!-- Informations de base -->
                <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-green-900 mb-4 border-b border-green-200 pb-2">Informations de base</h2>
                    
                    <div class="space-y-6">
                        <!-- Nom de l'équipement -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-green-700 mb-2">
                                Nom de l'équipement <span class="text-green-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $equipment->name) }}" required 
                                   class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-green-700 mb-2">
                                Description détaillée <span class="text-green-500">*</span>
                            </label>
                            <textarea id="description" name="description" required rows="6" 
                                      class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('description') border-red-500 @enderror" 
                                      placeholder="Décrivez en détail votre équipement, ses caractéristiques et ce qui le rend unique...">{{ old('description', $equipment->description) }}</textarea>
                            <div class="mt-1">
                                @error('description')
                                    <p class="text-red-500 text-sm">{{ $message }}</p>
                                @enderror
                                <div class="flex justify-between items-center mt-1">
                                    <p class="text-green-600 text-sm">Recommandé : 150–600 caractères</p>
                                </div>
                            </div>
                        </div>

                        <!-- Spécifications techniques -->
                        <div>
                            <label for="technical_specifications" class="block text-sm font-medium text-green-700 mb-2">Spécifications techniques</label>
                            <textarea id="technical_specifications" name="technical_specifications" rows="3" 
                                      placeholder="Dimensions, poids, puissance, capacité..." 
                                      class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('technical_specifications') border-red-500 @enderror">{{ old('technical_specifications', $equipment->technical_specifications) }}</textarea>
                            @error('technical_specifications')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Catégorie -->
                <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-green-900 mb-4 border-b border-green-200 pb-2">Catégorie de l'équipement</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-green-700 mb-2">
                                Catégorie principale <span class="text-green-500">*</span>
                            </label>
                            <select id="category_id" name="category_id" required class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('category_id') border-red-500 @enderror">
                                <option value="">Sélectionnez une catégorie principale</option>
                                @if(isset($categories))
                                    @foreach($categories->whereNull('parent_id') as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $equipment->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('category_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div id="subcategory-group" style="display: none;">
                            <label for="subcategory_id" class="block text-sm font-medium text-green-700 mb-2">
                                Sous-catégorie
                            </label>
                            <select id="subcategory_id" name="subcategory_id" class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('subcategory_id') border-red-500 @enderror" disabled>
                                <option value="">Veuillez d'abord choisir une catégorie</option>
                            </select>
                            @error('subcategory_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Photos -->
                <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-green-900 mb-4 border-b border-green-200 pb-2">Photos</h2>
                        
                    <!-- Photos actuelles -->
                    @if(is_array($equipment->photos) && count($equipment->photos) > 0)
                    <div class="mb-4">
                        <h3 class="text-sm font-medium text-green-700 mb-3">Images actuelles</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4" id="existing-photos">
                            @foreach($equipment->photos as $index => $photo)
                                <div class="relative group" id="photo-container-{{ $index }}">
                                    <img src="{{ Storage::url($photo) }}" alt="Photo équipement" class="rounded-lg object-cover h-32 w-full border border-green-200">
                                    <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-lg">
                                        <button type="button" data-photo-index="{{ $index }}" class="delete-photo-btn text-white p-2 rounded-full bg-red-500 hover:bg-red-600 transition-colors">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                        
                    <!-- Zone d'upload -->
                    <div class="border-2 border-dashed border-green-300 rounded-lg p-6 text-center bg-green-50 hover:border-green-400 transition-colors">
                        <input type="file" id="photos" name="photos[]" multiple accept="image/*" class="hidden">
                        <div id="upload-area" class="cursor-pointer" onclick="document.getElementById('photos').click()">
                            <i class="fas fa-cloud-upload-alt text-green-400 text-4xl mb-4"></i>
                            <p class="text-green-600 mb-2">Cliquez pour ajouter des photos ou glissez-déposez</p>
                            <p class="text-green-500 text-sm">Maximum 5 photos, 5MB par photo</p>
                        </div>
                        <div id="photo-preview" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mt-4 hidden"></div>
                    </div>
                    @error('photos')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    @error('photos.*')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tarification -->
                <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-green-900 mb-4 border-b border-green-200 pb-2">Tarification</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="daily_rate" class="block text-sm font-medium text-green-700 mb-2">
                                Prix par jour (€) <span class="text-green-500">*</span>
                            </label>
                            <input type="number" name="daily_rate" id="daily_rate" value="{{ old('daily_rate', $equipment->daily_rate) }}" required 
                                   min="0" step="0.01" placeholder="50" 
                                   class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('daily_rate') border-red-500 @enderror">
                            @error('daily_rate')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="deposit_amount" class="block text-sm font-medium text-green-700 mb-2">
                                Caution (€) <span class="text-green-500">*</span>
                            </label>
                            <input type="number" name="deposit_amount" id="deposit_amount" value="{{ old('deposit_amount', $equipment->deposit_amount) }}" required 
                                   min="0" step="0.01" placeholder="100" 
                                   class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('deposit_amount') border-red-500 @enderror">
                            @error('deposit_amount')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Autres prix -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                        <div>
                            <label for="price_per_hour" class="block text-sm font-medium text-green-700 mb-2">Prix par heure (€)</label>
                            <input type="number" name="price_per_hour" id="price_per_hour" step="0.01" min="0" 
                                   class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('price_per_hour') border-red-500 @enderror"
                                   placeholder="10.00" value="{{ old('price_per_hour', $equipment->price_per_hour) }}">
                            @error('price_per_hour')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="price_per_week" class="block text-sm font-medium text-green-700 mb-2">Prix par semaine (€)</label>
                            <input type="number" name="price_per_week" id="price_per_week" step="0.01" min="0" 
                                   class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('price_per_week') border-red-500 @enderror"
                                   placeholder="300.00" value="{{ old('price_per_week', $equipment->price_per_week) }}">
                            @error('price_per_week')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="price_per_month" class="block text-sm font-medium text-green-700 mb-2">Prix par mois (€)</label>
                            <input type="number" name="price_per_month" id="price_per_month" step="0.01" min="0" 
                                   class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('price_per_month') border-red-500 @enderror"
                                   placeholder="1000.00" value="{{ old('price_per_month', $equipment->price_per_month) }}">
                            @error('price_per_month')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Disponibilité -->
                <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-green-900 mb-4 border-b border-green-200 pb-2">Disponibilité</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="available_from" class="block text-sm font-medium text-green-700 mb-2">
                                Disponible à partir de
                            </label>
                            <input type="date" name="available_from" id="available_from" 
                                   class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('available_from') border-red-500 @enderror"
                                   value="{{ old('available_from', $equipment->available_from ? $equipment->available_from->format('Y-m-d') : '') }}">
                            @error('available_from')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="available_until" class="block text-sm font-medium text-green-700 mb-2">
                                Disponible jusqu'au
                            </label>
                            <input type="date" name="available_until" id="available_until" 
                                   class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('available_until') border-red-500 @enderror"
                                   value="{{ old('available_until', $equipment->available_until ? $equipment->available_until->format('Y-m-d') : '') }}">
                            @error('available_until')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Accessoires et conditions -->
                <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-green-900 mb-4 border-b border-green-200 pb-2">Accessoires et conditions</h2>
                    <div class="space-y-6">
                        <div>
                            <label for="accessories" class="block text-sm font-medium text-green-700 mb-2">
                                Accessoires inclus
                            </label>
                            <textarea name="accessories" id="accessories" rows="2" 
                                    class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('accessories') border-red-500 @enderror"
                                    placeholder="Casques, gants, manuel d'utilisation...">{{ old('accessories', $equipment->accessories) }}</textarea>
                            @error('accessories')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="rental_conditions" class="block text-sm font-medium text-green-700 mb-2">
                                Conditions de location
                            </label>
                            <textarea name="rental_conditions" id="rental_conditions" rows="3" 
                                    class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('rental_conditions') border-red-500 @enderror"
                                    placeholder="Conditions particulières, restrictions d'usage...">{{ old('rental_conditions', $equipment->rental_conditions) }}</textarea>
                            @error('rental_conditions')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Exigences -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="license_required" id="license_required" value="1" 
                               {{ old('license_required', $equipment->license_required) ? 'checked' : '' }}
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="license_required" class="ml-2 block text-sm text-gray-900">
                            Permis ou certification requis
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_available" id="is_available" value="1" 
                               {{ old('is_available', $equipment->is_available) ? 'checked' : '' }}
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="is_available" class="ml-2 block text-sm text-gray-900">
                            Équipement disponible immédiatement
                        </label>
                    </div>
                </div>

                <!-- Détails techniques -->
                <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-green-900 mb-4 border-b border-green-200 pb-2">Détails techniques</h2>
                    <div class="space-y-6">
                        <!-- État et statut -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="condition" class="block text-sm font-medium text-green-700 mb-2">
                                    État de l'équipement
                                </label>
                                <select name="condition" id="condition" 
                                        class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="">Sélectionner l'état</option>
                                    <option value="new" {{ old('condition', $equipment->condition) == 'new' ? 'selected' : '' }}>Neuf</option>
                                    <option value="excellent" {{ old('condition', $equipment->condition) == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                    <option value="very_good" {{ old('condition', $equipment->condition) == 'very_good' ? 'selected' : '' }}>Très bon</option>
                                    <option value="good" {{ old('condition', $equipment->condition) == 'good' ? 'selected' : '' }}>Bon</option>
                                    <option value="fair" {{ old('condition', $equipment->condition) == 'fair' ? 'selected' : '' }}>Correct</option>
                                    <option value="poor" {{ old('condition', $equipment->condition) == 'poor' ? 'selected' : '' }}>Mauvais</option>
                                </select>
                                @error('condition')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-green-700 mb-2">
                                    Statut
                                </label>
                                <select name="status" id="status" 
                                        class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="active" {{ old('status', $equipment->status) == 'active' ? 'selected' : '' }}>Actif</option>
                                    <option value="inactive" {{ old('status', $equipment->status) == 'inactive' ? 'selected' : '' }}>Inactif</option>
                                    <option value="maintenance" {{ old('status', $equipment->status) == 'maintenance' ? 'selected' : '' }}>En maintenance</option>
                                    <option value="rented" {{ old('status', $equipment->status) == 'rented' ? 'selected' : '' }}>Loué</option>
                                </select>
                                @error('status')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <!-- Localisation détaillée -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-green-700 mb-2">
                                Adresse complète
                            </label>
                            <div class="map-container">
                                <div id="serviceMap" class="h-64 rounded-lg border border-green-300 shadow-inner mb-3"></div>
                                <div class="relative">
                                    <input type="text" name="address" id="address" 
                                           class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('address') border-red-500 @enderror"
                                           placeholder="123 Rue de la République" value="{{ old('address', $equipment->address) }}" autocomplete="off">
                                    <!-- Dropdown suggestions -->
                                    <div id="address-suggestions" class="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-lg shadow-xl mt-1 z-[99999] hidden max-h-60 overflow-y-auto" style="z-index: 99999 !important; position: absolute !important;">
                                        <!-- Suggestions will be populated here -->
                                    </div>
                                    <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $equipment->latitude) }}">
                                    <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $equipment->longitude) }}">
                                    @error('address')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                    <div class="flex flex-col sm:flex-row gap-3 mt-3">
                                        <button type="button" id="getCurrentLocationBtn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition duration-200 flex items-center justify-center">
                                            <i class="fas fa-location-arrow mr-2"></i>Ma position actuelle
                                        </button>
                                        <button type="button" id="clearLocationBtn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-200 flex items-center justify-center">
                                            <i class="fas fa-times mr-2"></i>Effacer la localisation
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-between items-center pt-8 border-t border-green-200">
                    <a href="{{ route('prestataire.equipment.show', $equipment) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-6 py-3 rounded-lg transition duration-200 font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Annuler
                    </a>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl">
                        <i class="fas fa-check mr-2"></i>Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
        crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Prevent form resubmission
    const form = document.getElementById('equipment-form');
    if (form) {
        form.addEventListener('submit', function() {
            // Disable the submit button to prevent double submission
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mise à jour en cours...';
            }
        });
    }
    
    // Map Initialization
    let map = null;
    let marker = null;
    window.defaultLat = 33.5731; // Casablanca
    window.defaultLng = -7.5898;
    const existingLat = document.getElementById('latitude') ? document.getElementById('latitude').value : null;
    const existingLng = document.getElementById('longitude') ? document.getElementById('longitude').value : null;
    
    // Autocomplete variables
    let searchTimeout;
    let currentFocus = -1;
    const addressInput = document.getElementById('address');
    const suggestionsContainer = document.getElementById('address-suggestions');

    function initializeMap() {
        const mapElement = document.getElementById('serviceMap');
        if (!mapElement) return;

        // Use existing coordinates if available, otherwise use default
        const initialLat = existingLat ? parseFloat(existingLat) : window.defaultLat;
        const initialLng = existingLng ? parseFloat(existingLng) : window.defaultLng;
        const initialZoom = (existingLat && existingLng) ? 13 : 6;

        window.serviceMap = L.map('serviceMap').setView([initialLat, initialLng], initialZoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(window.serviceMap);

        // Add existing marker if coordinates exist
        if (existingLat && existingLng) {
            window.serviceMapMarker = L.marker([parseFloat(existingLat), parseFloat(existingLng)]).addTo(window.serviceMap);
        }

        window.serviceMap.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            updateMarker(lat, lng);
            reverseGeocode(lat, lng);
        });
        
        // Si des coordonnées existent déjà, les afficher
        if (existingLat && existingLng && window.serviceMapMarker) {
            updateMarker(parseFloat(existingLat), parseFloat(existingLng));
            window.serviceMap.setView([parseFloat(existingLat), parseFloat(existingLng)], 13);
        }
    }
    
    // Initialize autocomplete functionality
    function initAutocomplete() {
        if (!addressInput || !suggestionsContainer) return;

        // Handle input changes
        addressInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                hideSuggestions();
                return;
            }
            
            // Debounce the search to avoid too many API calls
            searchTimeout = setTimeout(() => {
                fetchLocationSuggestions(query);
            }, 300);
        });
        
        // Handle keyboard navigation
        addressInput.addEventListener('keydown', function(e) {
            const suggestions = suggestionsContainer.querySelectorAll('.suggestion-item');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                currentFocus++;
                if (currentFocus >= suggestions.length) currentFocus = 0;
                setActiveSuggestion(suggestions);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                currentFocus--;
                if (currentFocus < 0) currentFocus = suggestions.length - 1;
                setActiveSuggestion(suggestions);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (currentFocus > -1 && suggestions[currentFocus]) {
                    suggestions[currentFocus].click();
                } else if (this.value.trim().length > 0) {
                    // If no suggestion is selected but there's text, try to geocode it
                    forwardGeocode(this.value.trim());
                }
            } else if (e.key === 'Escape') {
                hideSuggestions();
                currentFocus = -1;
            }
        });
        
        // Handle focus events
        addressInput.addEventListener('focus', function() {
            const query = this.value.trim();
            if (query.length >= 2) {
                fetchLocationSuggestions(query);
            }
        });
        
        // Handle blur event to geocode if no suggestion was selected
        addressInput.addEventListener('blur', function() {
            // Small delay to allow click on suggestions to register
            setTimeout(() => {
                if (!suggestionsContainer.classList.contains('hidden') && 
                    this.value.trim().length > 0 && 
                    !window.serviceMapMarker) {
                    // If suggestions are still visible and no marker is set, try geocoding
                    forwardGeocode(this.value.trim());
                }
            }, 200);
        });
    }

    function updateMarker(lat, lng) {
        if (window.serviceMapMarker) {
            window.serviceMapMarker.setLatLng([lat, lng]);
        } else if (window.serviceMap) {
            window.serviceMapMarker = L.marker([lat, lng]).addTo(window.serviceMap);
        }
        
        document.getElementById('latitude').value = lat.toFixed(6);
        document.getElementById('longitude').value = lng.toFixed(6);
    }
    
    // Autocomplete functions
    function fetchLocationSuggestions(query) {
        console.log('Fetching suggestions for:', query);
        fetch(`/api/public/geolocation/cities?search=${encodeURIComponent(query)}&limit=10`)
            .then(response => {
                console.log('API Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('API Data received:', data);
                if (data.success && data.data && data.data.length > 0) {
                    displaySuggestions(data.data, query);
                    if (data.warning) {
                        console.warn('Location API warning:', data.warning);
                    }
                } else {
                    displayNoSuggestions();
                }
            })
            .catch(error => {
                console.error('Error fetching location suggestions:', error);
                displayNoSuggestions();
            });
    }

    function displaySuggestions(suggestions, query) {
        let html = '';
        
        suggestions.forEach((suggestion, index) => {
            // Determine the display text and highlight matching parts
            let displayText = suggestion.text || suggestion.city;
            const regex = new RegExp(`(${escapeRegExp(query)})`, 'gi');
            const highlightedText = displayText.replace(regex, '<strong>$1</strong>');
            
            // Determine icon and styling based on source
            const isLocal = suggestion.source === 'local';
            const iconColor = isLocal ? 'text-green-500' : 'text-blue-500';
            const bgHover = isLocal ? 'hover:bg-green-50' : 'hover:bg-blue-50';
            
            html += `
                <div class="suggestion-item px-4 py-3 ${bgHover} cursor-pointer border-b border-gray-100 last:border-b-0" 
                     data-suggestion='${JSON.stringify(suggestion).replace(/'/g, "&apos;")}' 
                     onclick="selectLocationFromData(this)">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 ${iconColor} mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm text-gray-900 truncate">${highlightedText}</div>
                            ${suggestion.source === 'worldwide' ? '<div class="text-xs text-gray-500 mt-0.5">Suggestion mondiale</div>' : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        
        suggestionsContainer.innerHTML = html;
        suggestionsContainer.classList.remove('hidden');
        currentFocus = -1;
    }

    function displayNoSuggestions() {
        suggestionsContainer.innerHTML = `
            <div class="px-4 py-3 text-gray-500 text-sm">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Aucune ville trouvée
                </div>
            </div>
        `;
        suggestionsContainer.classList.remove('hidden');
    }

    function selectLocationFromData(element) {
        const suggestion = JSON.parse(element.getAttribute('data-suggestion'));
        selectLocation(suggestion);
    }
    
    // Make this function globally accessible
    window.selectLocationFromData = selectLocationFromData;

    function selectLocation(suggestion) {
        // Handle both old format (string) and new format (object)
        let locationText;
        let lat, lng;
        
        if (typeof suggestion === 'string') {
            locationText = suggestion;
        } else if (suggestion && suggestion.text) {
            locationText = suggestion.text;
            lat = suggestion.latitude || suggestion.lat;
            lng = suggestion.longitude || suggestion.lng;
        } else if (suggestion && suggestion.city) {
            locationText = suggestion.city;
            if (suggestion.postal_code) {
                locationText += ' (' + suggestion.postal_code + ')';
            }
            if (suggestion.country && suggestion.country !== 'France') {
                locationText += ', ' + suggestion.country;
            }
            lat = suggestion.latitude || suggestion.lat;
            lng = suggestion.longitude || suggestion.lng;
        } else {
            locationText = 'Localisation sélectionnée';
        }
        
        addressInput.value = locationText;
        hideSuggestions();
        currentFocus = -1;
        
        // If coordinates are available in the suggestion, use them directly
        if (lat && lng && window.serviceMap) {
            updateMarker(parseFloat(lat), parseFloat(lng));
            window.serviceMap.setView([parseFloat(lat), parseFloat(lng)], 13);
        } else {
            // If no coordinates, perform forward geocoding to get them
            forwardGeocode(locationText);
        }
    }

    // Add forward geocoding function
    async function forwardGeocode(locationText) {
        try {
            // Show loading state
            const addressInput = document.getElementById('address');
            const originalValue = addressInput.value;
            addressInput.value = 'Recherche de coordonnées...';
            
            // Perform forward geocoding
            const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(locationText)}&limit=1&addressdetails=1&accept-language=fr`, {
                method: 'GET',
                headers: {
                    'User-Agent': 'TaPrestation-App/1.0',
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data && data.length > 0) {
                const result = data[0];
                const lat = parseFloat(result.lat);
                const lng = parseFloat(result.lon);
                
                // Update the address field with the full address from geocoding
                addressInput.value = result.display_name;
                
                // Update map and marker
                if (window.serviceMap) {
                    updateMarker(lat, lng);
                    window.serviceMap.setView([lat, lng], 13);
                }
            } else {
                // If no results, keep the original text and show a message
                addressInput.value = originalValue;
                alert('Impossible de trouver les coordonnées pour cette localisation. Veuillez essayer une autre adresse ou cliquer sur la carte.');
            }
        } catch (error) {
            console.error('Error during forward geocoding:', error);
            // Restore original value and show error
            document.getElementById('address').value = document.getElementById('address').value || 'Erreur de géocodage';
            alert('Erreur lors de la récupération des coordonnées. Veuillez réessayer.');
        }
    }

    function hideSuggestions() {
        if (suggestionsContainer) {
            suggestionsContainer.classList.add('hidden');
            suggestionsContainer.innerHTML = '';
        }
    }

    function setActiveSuggestion(suggestions) {
        // Remove active class from all suggestions
        suggestions.forEach(s => s.classList.remove('bg-green-50', 'bg-blue-50'));
        
        // Add active class to current suggestion
        if (currentFocus >= 0 && suggestions[currentFocus]) {
            suggestions[currentFocus].classList.add('bg-green-100');
        }
    }

    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    async function reverseGeocode(lat, lng) {
        try {
            // Ajouter un délai pour éviter les limitations de taux
            await new Promise(resolve => setTimeout(resolve, 100));
            
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=fr`, {
                method: 'GET',
                headers: {
                    'User-Agent': 'TaPrestation-App/1.0',
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            document.getElementById('address').value = data.display_name || `Coordonnées: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        } catch (error) {
            console.error('Error during reverse geocoding:', error);
            // Fallback vers les coordonnées si l'API échoue
            document.getElementById('address').value = `Coordonnées: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        }
    }

    document.getElementById('getCurrentLocationBtn').addEventListener('click', function() {
        const btn = this;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Localisation...';
        btn.disabled = true;
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                if (window.serviceMap) {
                    window.serviceMap.setView([lat, lng], 13);
                }
                updateMarker(lat, lng);
                reverseGeocode(lat, lng);
                
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, function(error) {
                alert('Erreur de géolocalisation: ' + error.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        } else {
            alert('La géolocalisation n\'est pas supportée par votre navigateur.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });

    document.getElementById('clearLocationBtn').addEventListener('click', function() {
        if (window.serviceMapMarker) {
            window.serviceMap.removeLayer(window.serviceMapMarker);
            window.serviceMapMarker = null;
        }
        document.getElementById('latitude').value = '';
        document.getElementById('longitude').value = '';
        document.getElementById('address').value = '';
        if (window.serviceMap) {
            window.serviceMap.setView([window.defaultLat, window.defaultLng], 6);
        }
    });

    initializeMap();
    initAutocomplete();
    
    // Photo preview functionality
    const photoInput = document.getElementById('photos');
    const previewContainer = document.getElementById('photo-preview');
    const uploadArea = document.getElementById('upload-area');
    let existingFiles = [];
    let isAddingMore = false;

    // Preview photos
    window.previewPhotos = function(input) {
        // Combine existing files with new ones when adding more
        if (isAddingMore && existingFiles.length > 0) {
            const newFiles = Array.from(input.files);
            const combinedFiles = new DataTransfer();
            
            // Add existing files first
            existingFiles.forEach(file => {
                if (combinedFiles.files.length < 5) {
                    combinedFiles.items.add(file);
                }
            });
            
            // Add new files
            newFiles.forEach(file => {
                if (combinedFiles.files.length < 5) {
                    combinedFiles.items.add(file);
                }
            });
            
            input.files = combinedFiles.files;
            isAddingMore = false;
        }
        
        // Store current files
        existingFiles = Array.from(input.files);
        
        previewContainer.innerHTML = '';
        if (input.files && input.files.length > 0) {
            previewContainer.classList.remove('hidden');
            uploadArea.classList.add('hidden');
            
            const files = Array.from(input.files).slice(0, 5);
            
            files.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'relative group';
                        div.innerHTML = `
                            <img src="${e.target.result}" alt="Preview" class="rounded-lg object-cover h-32 w-full border border-green-200">
                            <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" onclick="removePhoto(${index})" class="bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        `;
                        previewContainer.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            // Add "Add more" button if under limit
            if (files.length < 5) {
                const addMore = document.createElement('div');
                addMore.className = 'flex items-center justify-center h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-gray-400 transition-colors';
                addMore.innerHTML = '<i class="fas fa-plus text-gray-400 text-xl"></i>';
                addMore.onclick = () => {
                    isAddingMore = true;
                    photoInput.click();
                };
                previewContainer.appendChild(addMore);
            }
        } else {
            previewContainer.classList.add('hidden');
            uploadArea.classList.remove('hidden');
        }
    }

    window.removePhoto = function(index) {
        const dt = new DataTransfer();
        const files = photoInput.files;
        for (let i = 0; i < files.length; i++) {
            if (i !== index) {
                dt.items.add(files[i]);
            }
        }
        photoInput.files = dt.files;
        existingFiles = Array.from(photoInput.files);
        previewPhotos(photoInput);
    }

    // Single event listener for file input changes
    if (photoInput) {
        photoInput.addEventListener('change', function() {
            previewPhotos(this);
        });
    }

    // Delete existing photos
    const deletePhotoButtons = document.querySelectorAll('.delete-photo-btn');
    deletePhotoButtons.forEach(button => {
        button.addEventListener('click', function () {
            const photoIndex = this.dataset.photoIndex;
            if (confirm('Êtes-vous sûr de vouloir supprimer cette photo ?')) {
                fetch(`/prestataire/equipment/{{ $equipment->id }}/photos/${photoIndex}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw new Error(err.message || 'Une erreur est survenue lors de la communication avec le serveur.') });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const photoElement = document.getElementById(`photo-container-${photoIndex}`);
                        if (photoElement) {
                            photoElement.remove();
                        }
                    } else {
                        alert(data.message || 'Erreur lors de la suppression de la photo.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erreur: ' + error.message);
                });
            }
        });
    });

    // Gestion des catégories et sous-catégories
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    const subcategoryGroup = document.getElementById('subcategory-group');
    
    if (categorySelect && subcategorySelect) {
        // Gérer le changement de catégorie principale
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            
            // Vider la liste des sous-catégories
            subcategorySelect.innerHTML = '<option value="">Sélectionner une sous-catégorie</option>';
            
            // Si une catégorie parente est sélectionnée, charger les sous-catégories
            if (categoryId) {
                fetch(`/prestataire/equipment/subcategories/${categoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            data.forEach(subcategory => {
                                const option = document.createElement('option');
                                option.value = subcategory.id;
                                option.textContent = subcategory.name;
                                subcategorySelect.appendChild(option);
                            });
                            
                            // Activer le champ de sous-catégorie
                            subcategorySelect.disabled = false;
                            subcategoryGroup.style.display = 'block';
                        } else {
                            // Masquer le champ de sous-catégorie s'il n'y a pas de sous-catégories
                            subcategorySelect.disabled = true;
                            subcategoryGroup.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading subcategories:', error);
                        subcategorySelect.disabled = true;
                        subcategoryGroup.style.display = 'none';
                    });
            } else {
                // Masquer le champ de sous-catégorie si aucune catégorie parente n'est sélectionnée
                subcategorySelect.disabled = true;
                subcategoryGroup.style.display = 'none';
            }
        });
        
        // Si une catégorie parente est déjà sélectionnée, déclencher l'événement change
        // pour charger les sous-catégories au chargement de la page
        if (categorySelect.value) {
            const event = new Event('change');
            categorySelect.dispatchEvent(event);
        }
    }
});
</script>
@endpush

@push('styles')
<style>
.group:hover .group-hover\:opacity-100 {
    opacity: 1;
}
</style>
@endpush
