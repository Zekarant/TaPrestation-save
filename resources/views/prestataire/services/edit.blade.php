@extends('layouts.app')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
      crossorigin="" />
@endpush

@section('title', 'Modifier le service - ' . $service->title)

@section('content')
<div class="bg-blue-50">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="mb-8 text-center">
                <h1 class="text-4xl font-extrabold text-blue-900 mb-2">Modifier un service</h1>
                <p class="text-lg text-blue-700">Modifiez votre annonce de service professionnel</p>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('prestataire.services.show', $service) }}" class="text-blue-600 hover:text-blue-900 transition-colors duration-200">
                            <i class="fas fa-arrow-left text-xl"></i>
                        </a>
                        <div>
                            <h2 class="text-xl font-bold text-blue-900">Modifier le service</h2>
                            <p class="text-blue-700">Mettez à jour les informations de votre service</p>
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

            <form action="{{ route('prestataire.services.update', $service) }}" method="POST" enctype="multipart/form-data" id="service-form">
                @csrf
                @method('PUT')
                
                <!-- Informations de base -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-blue-900 mb-4 border-b border-blue-200 pb-2">Informations de base</h2>
                    
                    <div class="space-y-6">
                        <!-- Titre -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-blue-700 mb-2">
                                Titre du service <span class="text-blue-500">*</span>
                            </label>
                            <input type="text" id="title" name="title" value="{{ old('title', $service->title) }}" required class="w-full px-3 py-2 border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-500 @enderror">
                            <div class="flex justify-between items-center mt-1">
                                <div>
                                    @error('title')
                                        <p class="text-red-500 text-sm">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-blue-700 mb-2">
                                Description détaillée <span class="text-blue-500">*</span>
                            </label>
                            <textarea id="description" name="description" required rows="6" placeholder="Décrivez en détail votre service, vos compétences et ce qui vous différencie..." class="w-full px-3 py-2 border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $service->description) }}</textarea>
                            <div class="mt-1">
                                @error('description')
                                    <p class="text-red-500 text-sm">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Reservable -->
                        <div>
                            <label for="reservable" class="inline-flex items-center">
                                <input id="reservable" name="reservable" type="checkbox" value="1" {{ old('reservable', $service->reservable) ? 'checked' : '' }} 
                                       class="rounded border-blue-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-offset-0 focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-blue-600">Activer la réservation directe pour ce service</span>
                            </label>
                        </div>

                        <!-- Delivery time -->
                        <div>
                            <label for="delivery_time" class="block text-sm font-medium text-blue-700 mb-2">Délai de livraison (en jours)</label>
                            <input type="number" name="delivery_time" id="delivery_time" min="1" 
                                   value="{{ old('delivery_time', $service->delivery_time) }}" 
                                   placeholder="Ex: 3" 
                                   class="w-full px-3 py-2 border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('delivery_time') border-red-500 @enderror">
                            @error('delivery_time')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Catégorie -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-blue-900 mb-4 border-b border-blue-200 pb-2">Catégorie du service</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="parent_category_id" class="block text-sm font-medium text-blue-700 mb-2">
                                Catégorie principale <span class="text-blue-500">*</span>
                            </label>
                            <select id="parent_category_id" name="category_id" required class="w-full px-3 py-2 border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('category_id') border-red-500 @enderror">
                                <option value="">Sélectionner une catégorie</option>
                                @foreach($categories->whereNull('parent_id') as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $service->categories->first()->parent_id ? $service->categories->first()->parent_id : $service->categories->first()->id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div id="subcategory-group" style="display: none;">
                            <label for="category_id" class="block text-sm font-medium text-blue-700 mb-2">
                                Sous-catégorie
                            </label>
                            <select id="category_id" name="subcategory_id" class="w-full px-3 py-2 border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('subcategory_id') border-red-500 @enderror" disabled>
                                <option value="">Veuillez d'abord choisir une catégorie</option>
                            </select>
                            @error('subcategory_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Prix -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-blue-900 mb-4 border-b border-blue-200 pb-2">Prix du service</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="price" class="block text-sm font-medium text-blue-700 mb-2">
                                Prix (€) <span class="text-blue-500">*</span>
                            </label>
                            <input type="number" id="price" name="price" value="{{ old('price', $service->price) }}" required min="0" step="0.01" class="w-full px-3 py-2 border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('price') border-red-500 @enderror">
                            @error('price')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="price_type" class="block text-sm font-medium text-blue-700 mb-2">
                                Type de tarification <span class="text-blue-500">*</span>
                            </label>
                            <select id="price_type" name="price_type" required class="w-full px-3 py-2 border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('price_type') border-red-500 @enderror">
                                <option value="">Sélectionnez un type</option>
                                <option value="fixe" {{ old('price_type', $service->price_type) == 'fixe' ? 'selected' : '' }}>Prix fixe</option>
                                <option value="heure" {{ old('price_type', $service->price_type) == 'heure' ? 'selected' : '' }}>Par heure</option>
                                <option value="jour" {{ old('price_type', $service->price_type) == 'jour' ? 'selected' : '' }}>Par jour</option>
                                <option value="projet" {{ old('price_type', $service->price_type) == 'projet' ? 'selected' : '' }}>Par projet</option>
                                <option value="devis" {{ old('price_type', $service->price_type) == 'devis' ? 'selected' : '' }}>Sur devis</option>
                            </select>
                            @error('price_type')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Champ dynamique pour le nombre d'heures/jours -->
                    <div id="quantity-container" class="mt-6" style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label id="quantity-label" for="quantity" class="block text-sm font-medium text-blue-700 mb-2"></label>
                                <input type="number" id="quantity" name="quantity" min="1" value="{{ old('quantity', $service->quantity) }}" class="w-full px-3 py-2 border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('quantity') border-red-500 @enderror">
                                @error('quantity')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Affichage du prix total -->
                    <div id="total-price-container" class="mt-6" style="display: none;">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-blue-700">Prix total estimé :</span>
                                <span id="total-price" class="text-lg font-bold text-blue-900">0,00 €</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photos -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-blue-900 mb-4 border-b border-blue-200 pb-2">Photos</h2>
                    
                    <!-- Images existantes -->
                    @if($service->images->count() > 0)
                    <div class="mb-4">
                        <h3 class="text-sm font-medium text-blue-700 mb-3">Images actuelles</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4" id="existing-images">
                            @foreach($service->images as $image)
                                <div class="relative group" id="image-container-{{ $image->id }}">
                                    <img src="{{ Storage::url($image->image_path) }}" alt="Service Image" class="rounded-lg object-cover h-32 w-full border border-blue-200">
                                    <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-lg">
                                        <button type="button" data-image-id="{{ $image->id }}" class="delete-image-btn text-white p-2 rounded-full bg-red-500 hover:bg-red-600 transition-colors">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <!-- Zone d'upload -->
                    <div class="border-2 border-dashed border-blue-300 rounded-lg p-6 text-center bg-blue-50 hover:border-blue-400 transition-colors">
                        <input type="file" id="images" name="images[]" multiple accept="image/*" class="hidden">
                        <div id="upload-area" class="cursor-pointer" onclick="document.getElementById('images').click()">
                            <i class="fas fa-cloud-upload-alt text-blue-400 text-4xl mb-4"></i>
                            <p class="text-blue-600 mb-2">Cliquez pour ajouter des photos ou glissez-déposez</p>
                            <p class="text-blue-500 text-sm">Maximum 5 photos, 5MB par photo</p>
                        </div>
                        <div id="image-preview" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mt-4 hidden"></div>
                    </div>
                    @error('images')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    @error('images.*')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Localisation -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-blue-900 mb-4 border-b border-blue-200 pb-2">Localisation</h2>
                    <div class="map-container">
                        <div id="serviceMap" class="h-64 rounded-lg border border-blue-300 shadow-inner mb-3"></div>
                        <div class="relative">
                            <input type="text" id="selectedAddress" name="address" value="{{ old('address', $service->address) }}" required placeholder="Saisissez l'adresse ou cliquez sur la carte pour sélectionner une localisation" class="w-full px-3 py-2 border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('address') border-red-500 @enderror" autocomplete="off">
                            <!-- Dropdown suggestions -->
                            <div id="address-suggestions" class="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-lg shadow-xl mt-1 z-[99999] hidden max-h-60 overflow-y-auto" style="z-index: 99999 !important; position: absolute !important;">
                                <!-- Suggestions will be populated here -->
                            </div>
                            <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $service->latitude) }}">
                            <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $service->longitude) }}">
                            @error('address')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <div class="flex flex-col sm:flex-row gap-3 mt-3">
                                <button type="button" id="getCurrentLocationBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition duration-200 flex items-center justify-center">
                                    <i class="fas fa-location-arrow mr-2"></i>Ma position actuelle
                                </button>
                                <button type="button" id="clearLocationBtn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-200 flex items-center justify-center">
                                    <i class="fas fa-times mr-2"></i>Effacer la localisation
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-between items-center pt-8 border-t border-blue-200">
                    <a href="{{ route('prestataire.services.show', $service) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-6 py-3 rounded-lg transition duration-200 font-medium">
                        <i class="fas fa-arrow-left mr-2"></i>Annuler
                    </a>
                    
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl">
                        <i class="fas fa-check mr-2"></i>Mettre à jour le service
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
    const form = document.getElementById('service-form');
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
    
    // Gestion du prix dynamique
    const priceInput = document.getElementById('price');
    const priceTypeSelect = document.getElementById('price_type');
    const quantityContainer = document.getElementById('quantity-container');
    const quantityLabel = document.getElementById('quantity-label');
    const quantityInput = document.getElementById('quantity');
    const totalPriceContainer = document.getElementById('total-price-container');
    const totalPriceDisplay = document.getElementById('total-price');
    
    // Fonction pour mettre à jour le prix total
    function updateTotalPrice() {
        const price = parseFloat(priceInput.value) || 0;
        const quantity = parseFloat(quantityInput.value) || 0;
        const priceType = priceTypeSelect.value;
        
        // Afficher le conteneur de quantité uniquement pour les types "heure" ou "jour"
        if (priceType === 'heure' || priceType === 'jour') {
            quantityContainer.style.display = 'block';
            totalPriceContainer.style.display = 'block';
            
            // Mettre à jour le label en fonction du type
            quantityLabel.textContent = priceType === 'heure' ? 'Nombre d\'heures' : 'Nombre de jours';
            
            // Calculer et afficher le prix total
            const total = price * quantity;
            totalPriceDisplay.textContent = total.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
        } else {
            quantityContainer.style.display = 'none';
            totalPriceContainer.style.display = 'none';
        }
    }
    
    // Écouter les changements sur les champs de prix, type et quantité
    if (priceInput && priceTypeSelect && quantityInput) {
        priceInput.addEventListener('input', updateTotalPrice);
        priceTypeSelect.addEventListener('change', updateTotalPrice);
        quantityInput.addEventListener('input', updateTotalPrice);
        
        // Initialiser l'affichage au chargement de la page
        updateTotalPrice();
    }
    
    // Gestion des catégories et sous-catégories
    const parentCategorySelect = document.getElementById('parent_category_id');
    const categorySelect = document.getElementById('category_id');
    const subcategoryGroup = document.getElementById('subcategory-group');
    
    // Charger les sous-catégories lorsque la catégorie parente change
    if (parentCategorySelect && categorySelect) {
        parentCategorySelect.addEventListener('change', function() {
            const parentId = this.value;
            
            // Vider la liste des sous-catégories
            categorySelect.innerHTML = '<option value="">Sélectionner une sous-catégorie</option>';
            
            // Si une catégorie parente est sélectionnée, charger les sous-catégories
            if (parentId) {
                fetch(`/api/categories/${parentId}/subcategories`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            data.forEach(subcategory => {
                                const option = document.createElement('option');
                                option.value = subcategory.id;
                                option.textContent = subcategory.name;
                                
                                // Vérifier si cette sous-catégorie était déjà sélectionnée
                                @if(isset($service) && $service->categories->first() && $service->categories->first()->parent_id)
                                    if (subcategory.id == {{ $service->categories->first()->id }}) {
                                        option.selected = true;
                                    }
                                @endif
                                
                                categorySelect.appendChild(option);
                            });
                            
                            // Activer le champ de sous-catégorie
                            categorySelect.disabled = false;
                            subcategoryGroup.style.display = 'block';
                        } else {
                            // Masquer le champ de sous-catégorie s'il n'y a pas de sous-catégories
                            categorySelect.disabled = true;
                            subcategoryGroup.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading subcategories:', error);
                        categorySelect.disabled = true;
                        subcategoryGroup.style.display = 'none';
                    });
            } else {
                // Masquer le champ de sous-catégorie si aucune catégorie parente n'est sélectionnée
                categorySelect.disabled = true;
                subcategoryGroup.style.display = 'none';
            }
        });
        
        // Si une catégorie parente est déjà sélectionnée, déclencher l'événement change
        // pour charger les sous-catégories au chargement de la page
        if (parentCategorySelect.value) {
            const event = new Event('change');
            parentCategorySelect.dispatchEvent(event);
        }
    }
    
    // Map Initialization
    let map = null;
    let marker = null;
    const defaultLat = 33.5731; // Casablanca
    const defaultLng = -7.5898;
    const existingLat = document.getElementById('latitude') ? document.getElementById('latitude').value : null;
    const existingLng = document.getElementById('longitude') ? document.getElementById('longitude').value : null;
    
    // Autocomplete variables
    let searchTimeout;
    let currentFocus = -1;
    const addressInput = document.getElementById('selectedAddress');
    const suggestionsContainer = document.getElementById('address-suggestions');

    function initializeMap() {
        const mapElement = document.getElementById('serviceMap');
        if (!mapElement) return;

        // Use existing coordinates if available, otherwise use default
        const initialLat = existingLat ? parseFloat(existingLat) : defaultLat;
        const initialLng = existingLng ? parseFloat(existingLng) : defaultLng;
        const initialZoom = (existingLat && existingLng) ? 13 : 6;

        map = L.map('serviceMap').setView([initialLat, initialLng], initialZoom);
        
        // Make map globally accessible
        window.serviceMap = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add existing marker if coordinates exist
        if (existingLat && existingLng) {
            marker = L.marker([parseFloat(existingLat), parseFloat(existingLng)]).addTo(map);
            // Make marker globally accessible
            window.serviceMapMarker = marker;
        }

        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            updateMarker(lat, lng);
            reverseGeocode(lat, lng);
        });
        
        // Si des coordonnées existent déjà, les afficher
        if (existingLat && existingLng) {
            updateMarker(parseFloat(existingLat), parseFloat(existingLng));
            map.setView([parseFloat(existingLat), parseFloat(existingLng)], 13);
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
            const iconColor = isLocal ? 'text-blue-500' : 'text-green-500';
            const bgHover = isLocal ? 'hover:bg-blue-50' : 'hover:bg-green-50';
            
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
            const addressInput = document.getElementById('selectedAddress');
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
            document.getElementById('selectedAddress').value = document.getElementById('selectedAddress').value || 'Erreur de géocage';
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
        suggestions.forEach(s => s.classList.remove('bg-blue-50', 'bg-green-50'));
        
        // Add active class to current suggestion
        if (currentFocus >= 0 && suggestions[currentFocus]) {
            suggestions[currentFocus].classList.add('bg-blue-100');
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
            document.getElementById('selectedAddress').value = data.display_name || `Coordonnées: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        } catch (error) {
            console.error('Error during reverse geocoding:', error);
            // Fallback vers les coordonnées si l'API échoue
            document.getElementById('selectedAddress').value = `Coordonnées: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
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
        document.getElementById('selectedAddress').value = '';
        if (window.serviceMap) {
            window.serviceMap.setView([defaultLat, defaultLng], 6);
        }
    });

    initializeMap();
    initAutocomplete();
    
    // Image Preview
    const imageInput = document.getElementById('images');
    const previewContainer = document.getElementById('image-preview');
    const uploadArea = document.getElementById('upload-area');
    
    // Variable to store existing files
    let existingFiles = [];
    let isAddingMore = false;

    window.previewImages = function(input) {
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
                            <img src="${e.target.result}" alt="Preview" class="rounded-lg object-cover h-32 w-full border border-blue-200">
                            <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" onclick="removeImage(${index})" class="bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        `;
                        previewContainer.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                }
            });

            if (files.length < 5) {
                const addMore = document.createElement('div');
                addMore.className = 'flex items-center justify-center h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-gray-400 transition-colors';
                addMore.innerHTML = '<i class="fas fa-plus text-gray-400 text-xl"></i>';
                addMore.onclick = () => {
                    isAddingMore = true;
                    imageInput.click();
                };
                previewContainer.appendChild(addMore);
            }
        } else {
            previewContainer.classList.add('hidden');
            uploadArea.classList.remove('hidden');
        }
    }

    window.removeImage = function(index) {
        const dt = new DataTransfer();
        const files = imageInput.files;
        for (let i = 0; i < files.length; i++) {
            if (i !== index) {
                dt.items.add(files[i]);
            }
        }
        imageInput.files = dt.files;
        existingFiles = Array.from(imageInput.files);
        previewImages(imageInput);
    }

    // Single event listener for file input changes
    imageInput.addEventListener('change', function() {
        previewImages(this);
    });

    // Delete existing images
    const deleteButtons = document.querySelectorAll('.delete-image-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const imageId = this.dataset.imageId;
            if (confirm('Êtes-vous sûr de vouloir supprimer cette image ?')) {
                fetch(`/prestataire/services/images/${imageId}`, {
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
                        const imageElement = document.getElementById(`image-container-${imageId}`);
                        if (imageElement) {
                            imageElement.remove();
                        }
                    } else {
                        alert(data.message || 'Erreur lors de la suppression de l\'image.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erreur: ' + error.message);
                });
            }
        });
    });

    // Validation supplémentaire lors de la soumission pour la description
    document.getElementById('service-form').addEventListener('submit', function(e) {
        const descriptionInput = document.getElementById('description');
        if (descriptionInput.value.length < 1) {
            e.preventDefault();
            alert('La description est requise.');
            descriptionInput.focus();
            return false;
        }
    });
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
