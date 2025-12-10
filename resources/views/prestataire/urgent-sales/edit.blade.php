@extends('layouts.app')

@push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush

@section('title', 'Modifier la vente - ' . $urgentSale->title)

@section('content')
    <div class="bg-red-50">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <!-- En-tête -->
                <div class="mb-8 text-center">
                    <h1 class="text-4xl font-extrabold text-red-900 mb-2">Modifier une vente urgente</h1>
                    <p class="text-lg text-red-700">Modifiez votre annonce de vente urgente</p>
                </div>

                <div class="bg-white rounded-xl shadow-lg border border-red-200 p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('prestataire.urgent-sales.show', $urgentSale) }}"
                                class="text-red-600 hover:text-red-900 transition-colors duration-200">
                                <i class="fas fa-arrow-left text-xl"></i>
                            </a>
                            <div>
                                <h2 class="text-xl font-bold text-red-900">Modifier la vente urgente</h2>
                                <p class="text-red-700">Mettez à jour les informations de votre annonce</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg" role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
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

                <form action="{{ route('prestataire.urgent-sales.update', $urgentSale) }}" method="POST"
                    enctype="multipart/form-data" id="urgent-sale-form">
                    @csrf
                    @method('PUT')

                    <!-- Informations de base -->
                    <div class="bg-white rounded-xl shadow-lg border border-red-200 p-6 mb-6">
                        <h2 class="text-xl font-bold text-red-900 mb-4 border-b border-red-200 pb-2">Informations de base
                        </h2>

                        <div class="space-y-6">
                            <!-- Titre -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-red-700 mb-2">
                                    Titre de la vente <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="title" name="title" value="{{ old('title', $urgentSale->title) }}"
                                    required
                                    class="w-full px-3 py-2 border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('title') border-red-500 @enderror">
                                <div class="flex justify-between items-center mt-1">
                                    <div>
                                        @error('title')
                                            <p class="text-red-500 text-sm">{{ $message }}</p>
                                        @enderror
                                        <p id="title-warning" class="text-yellow-600 text-sm hidden">Titre trop court,
                                            précisez la marque ou le modèle</p>
                                        <p id="title-tip" class="text-red-600 text-sm">Idéal : 5–9 mots, incluez marque et
                                            état</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-red-700 mb-2">
                                    Description détaillée <span class="text-red-500">*</span>
                                </label>
                                <textarea id="description" name="description" required rows="6"
                                    placeholder="Décrivez votre article en détail..."
                                    class="w-full px-3 py-2 border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('description') border-red-500 @enderror">{{ old('description', $urgentSale->description) }}</textarea>
                                <div class="mt-1">
                                    @error('description')
                                        <p class="text-red-500 text-sm">{{ $message }}</p>
                                    @enderror
                                    <div class="flex justify-between items-center mt-1">
                                        <p class="text-red-600 text-sm">Recommandé : 150–500 caractères</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Catégorie -->
                    <div class="bg-white rounded-xl shadow-lg border border-red-200 p-6 mb-6">
                        <h2 class="text-xl font-bold text-red-900 mb-4 border-b border-red-200 pb-2">Catégorie de la vente
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <label for="parent_category_id" class="block text-sm font-medium text-red-700 mb-2">
                                    Catégorie principale <span class="text-red-500">*</span>
                                </label>
                                <select id="parent_category_id" name="parent_category_id" required
                                    class="w-full px-3 py-2 border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('parent_category_id') border-red-500 @enderror">
                                    <option value="">Sélectionner une catégorie</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('parent_category_id', $urgentSale->category->parent_id ?? $urgentSale->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('parent_category_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div id="subcategory-group" style="display: none;">
                                <label for="category_id" class="block text-sm font-medium text-red-700 mb-2">
                                    Sous-catégorie
                                </label>
                                <select id="category_id" name="category_id"
                                    class="w-full px-3 py-2 border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('category_id') border-red-500 @enderror"
                                    disabled>
                                    <option value="">Veuillez d'abord choisir une catégorie</option>
                                </select>
                                @error('category_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Détails -->
                    <div class="bg-white rounded-xl shadow-lg border border-red-200 p-6 mb-6">
                        <h2 class="text-xl font-bold text-red-900 mb-4 border-b border-red-200 pb-2">Détails de la vente
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Prix -->
                            <div>
                                <label for="price" class="block text-sm font-medium text-red-700 mb-2">
                                    Prix (€) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="price" name="price" value="{{ old('price', $urgentSale->price) }}"
                                    required min="0" step="0.01"
                                    class="w-full px-3 py-2 border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('price') border-red-500 @enderror">
                                @error('price')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- État -->
                            <div>
                                <label for="condition" class="block text-sm font-medium text-red-700 mb-2">
                                    État <span class="text-red-500">*</span>
                                </label>
                                <select id="condition" name="condition" required
                                    class="w-full px-3 py-2 border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('condition') border-red-500 @enderror">
                                    <option value="">Sélectionner un état</option>
                                    <option value="new" {{ old('condition', $urgentSale->condition) === 'new' ? 'selected' : '' }}>Neuf</option>
                                    <option value="like_new" {{ old('condition', $urgentSale->condition) === 'like_new' ? 'selected' : '' }}>Comme neuf</option>
                                    <option value="very_good" {{ old('condition', $urgentSale->condition) === 'very_good' ? 'selected' : '' }}>Très bon état</option>
                                    <option value="good" {{ old('condition', $urgentSale->condition) === 'good' ? 'selected' : '' }}>Bon état</option>
                                    <option value="fair" {{ old('condition', $urgentSale->condition) === 'fair' ? 'selected' : '' }}>État correct</option>
                                    <option value="poor" {{ old('condition', $urgentSale->condition) === 'poor' ? 'selected' : '' }}>Mauvais état</option>
                                </select>
                                @error('condition')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Quantité -->
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-red-700 mb-2">
                                    Quantité <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="quantity" name="quantity"
                                    value="{{ old('quantity', $urgentSale->quantity) }}" required min="1"
                                    class="w-full px-3 py-2 border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('quantity') border-red-500 @enderror">
                                @error('quantity')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Localisation -->
                    <div class="bg-white rounded-xl shadow-lg border border-red-200 p-6 mb-6">
                        <h2 class="text-xl font-bold text-red-900 mb-4 border-b border-red-200 pb-2">Localisation</h2>
                        <div class="map-container">
                            <div id="serviceMap" class="h-64 rounded-lg border border-red-300 shadow-inner mb-3"></div>
                            <div class="relative">
                                <input type="text" id="address" name="address"
                                    value="{{ old('address', $urgentSale->location) }}" required
                                    placeholder="Saisissez l'adresse ou cliquez sur la carte pour sélectionner une localisation"
                                    class="w-full px-3 py-2 border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('address') border-red-500 @enderror"
                                    autocomplete="off">
                                <!-- Dropdown suggestions -->
                                <div id="address-suggestions"
                                    class="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-lg shadow-xl mt-1 z-[99999] hidden max-h-60 overflow-y-auto"
                                    style="z-index: 99999 !important; position: absolute !important;">
                                    <!-- Suggestions will be populated here -->
                                </div>
                                <input type="hidden" id="latitude" name="latitude"
                                    value="{{ old('latitude', $urgentSale->latitude ?? '') }}">
                                <input type="hidden" id="longitude" name="longitude"
                                    value="{{ old('longitude', $urgentSale->longitude ?? '') }}">
                                @error('address')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <div class="flex flex-col sm:flex-row gap-3 mt-3">
                                    <button type="button" id="getCurrentLocationBtn"
                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition duration-200 flex items-center justify-center">
                                        <i class="fas fa-location-arrow mr-2"></i>Ma position actuelle
                                    </button>
                                    <button type="button" id="clearLocationBtn"
                                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-200 flex items-center justify-center">
                                        <i class="fas fa-times mr-2"></i>Effacer la localisation
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Photos -->
                    <div class="bg-white rounded-xl shadow-lg border border-red-200 p-6 mb-6">
                        <h2 class="text-xl font-bold text-red-900 mb-4 border-b border-red-200 pb-2">Photos</h2>

                        <!-- Photos actuelles -->
                        @if($urgentSale->photos && is_array($urgentSale->photos) && count($urgentSale->photos) > 0)
                            <div class="mb-4">
                                <h3 class="text-sm font-medium text-red-700 mb-3">Images actuelles</h3>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4" id="existing-photos">
                                    @foreach($urgentSale->photos as $index => $photo)
                                        <div class="relative group" id="photo-container-{{ $index }}">
                                            <img src="{{ Storage::url($photo) }}" alt="Photo vente urgente"
                                                class="rounded-lg object-cover h-32 w-full border border-red-200">
                                            <div
                                                class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-lg">
                                                <button type="button" data-photo-index="{{ $index }}"
                                                    class="delete-photo-btn text-white p-2 rounded-full bg-red-500 hover:bg-red-600 transition-colors">
                                                    <i class="fas fa-trash text-sm"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Zone d'upload -->
                        <div
                            class="border-2 border-dashed border-red-300 rounded-lg p-6 text-center bg-red-50 hover:border-red-400 transition-colors">
                            <input type="file" id="photos" name="photos[]" multiple accept="image/*" class="hidden">
                            <div id="upload-area" class="cursor-pointer"
                                onclick="document.getElementById('photos').click()">
                                <i class="fas fa-cloud-upload-alt text-red-400 text-4xl mb-4"></i>
                                <p class="text-red-600 mb-2">Cliquez pour ajouter des photos ou glissez-déposez</p>
                                <p class="text-red-500 text-sm">Maximum 5 photos, 5MB par photo</p>
                            </div>
                            <div id="photo-preview"
                                class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mt-4 hidden"></div>
                        </div>
                        @error('photos')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        @error('photos.*')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center pt-8 border-t border-red-200">
                        <a href="{{ route('prestataire.urgent-sales.show', $urgentSale) }}"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-6 py-3 rounded-lg transition duration-200 font-medium">
                            <i class="fas fa-arrow-left mr-2"></i>Annuler
                        </a>

                        <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl">
                            <i class="fas fa-check mr-2"></i>Mettre à jour et publier
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
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            console.log('DOM content loaded');

            // Gestion des catégories et sous-catégories
            const parentCategorySelect = document.getElementById('parent_category_id');
            const categorySelect = document.getElementById('category_id');
            const subcategoryGroup = document.getElementById('subcategory-group');

            // Charger les sous-catégories lorsque la catégorie parente change
            if (parentCategorySelect && categorySelect) {
                parentCategorySelect.addEventListener('change', function () {
                    const parentId = this.value;

                    // Vider la liste des sous-catégories
                    categorySelect.innerHTML = '<option value="">Sélectionner une sous-catégorie</option>';

                    // Si une catégorie parente est sélectionnée, charger les sous-catégories
                    if (parentId) {
                        fetch(`/prestataire/urgent-sales/subcategories/${parentId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.length > 0) {
                                    data.forEach(subcategory => {
                                        const option = document.createElement('option');
                                        option.value = subcategory.id;
                                        option.textContent = subcategory.name;

                                        // Vérifier si cette sous-catégorie était déjà sélectionnée
                                        @if(isset($urgentSale) && $urgentSale->category && $urgentSale->category->parent_id)
                                            if (subcategory.id == {{ $urgentSale->category->id }}) {
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

                window.serviceMap.on('click', function (e) {
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
                addressInput.addEventListener('input', function () {
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
                addressInput.addEventListener('keydown', function (e) {
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
                        }
                    } else if (e.key === 'Escape') {
                        hideSuggestions();
                        currentFocus = -1;
                    }
                });

                // Handle focus events
                addressInput.addEventListener('focus', function () {
                    const query = this.value.trim();
                    if (query.length >= 2) {
                        fetchLocationSuggestions(query);
                    }
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
                    const iconColor = isLocal ? 'text-red-500' : 'text-blue-500';
                    const bgHover = isLocal ? 'hover:bg-red-50' : 'hover:bg-blue-50';

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

                // Update map if coordinates are available
                if (lat && lng && window.serviceMap) {
                    updateMarker(parseFloat(lat), parseFloat(lng));
                    window.serviceMap.setView([parseFloat(lat), parseFloat(lng)], 13);
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
                suggestions.forEach(s => s.classList.remove('bg-red-50', 'bg-blue-50'));

                // Add active class to current suggestion
                if (currentFocus >= 0 && suggestions[currentFocus]) {
                    suggestions[currentFocus].classList.add('bg-red-100');
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

            document.getElementById('getCurrentLocationBtn').addEventListener('click', function () {
                const btn = this;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Localisation...';
                btn.disabled = true;

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function (position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        if (window.serviceMap) {
                            window.serviceMap.setView([lat, lng], 13);
                        }
                        updateMarker(lat, lng);
                        reverseGeocode(lat, lng);

                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }, function (error) {
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

            document.getElementById('clearLocationBtn').addEventListener('click', function () {
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

            // Validation supplémentaire lors de la soumission pour la description

            // Photo preview functionality
            const photoInput = document.getElementById('photos');
            const previewContainer = document.getElementById('photo-preview');
            const uploadArea = document.getElementById('upload-area');
            let existingFiles = [];
            let isAddingMore = false;

            // Preview photos
            window.previewPhotos = function (input) {
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
                            reader.onload = function (e) {
                                const div = document.createElement('div');
                                div.className = 'relative group';
                                div.innerHTML = `
                                <img src="${e.target.result}" alt="Preview" class="rounded-lg object-cover h-32 w-full border border-red-200">
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

                    // Reinitialize photo deletion for any existing photos
                    initializePhotoDeletion();
                } else {
                    previewContainer.classList.add('hidden');
                    uploadArea.classList.remove('hidden');
                }
            }

            window.removePhoto = function (index) {
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
            photoInput.addEventListener('change', function () {
                previewPhotos(this);
            });

            // Delete existing photos
            function initializePhotoDeletion() {
                // Use a slight delay to ensure all DOM elements are loaded
                setTimeout(function () {
                    const deletePhotoButtons = document.querySelectorAll('.delete-photo-btn');
                    deletePhotoButtons.forEach(button => {
                        // Remove any existing event listeners to prevent duplicates
                        button.removeEventListener('click', handlePhotoDeletion);
                        // Add the event listener with proper event parameter
                        button.addEventListener('click', handlePhotoDeletion);
                    });
                }, 100);
            }

            // Handle photo deletion
            function handlePhotoDeletion(event) {
                // Store reference to the button that was clicked
                const button = event.currentTarget;
                const photoIndex = button.dataset.photoIndex;

                // Set the photo index in the modal
                document.getElementById('photoIndexToDelete').value = photoIndex;

                // Show the delete photo modal
                const deletePhotoModal = document.getElementById('deletePhotoModal');
                deletePhotoModal.classList.remove('hidden');

                // Add animation classes
                setTimeout(() => {
                    deletePhotoModal.classList.remove('opacity-0');
                    const modalContent = deletePhotoModal.querySelector('.modal-show');
                    modalContent.classList.remove('scale-95');
                    modalContent.classList.add('scale-100');
                    modalContent.classList.remove('opacity-0');
                }, 10);
            }

            // Initialize photo deletion when DOM is loaded
            initializePhotoDeletion();

            // Also re-initialize after any dynamic content changes
            document.addEventListener('DOMContentLoaded', function () {
                initializePhotoDeletion();
            });

            // Fallback: initialize after a longer delay to ensure all content is loaded
            setTimeout(function () {
                initializePhotoDeletion();
            }, 1000);

            // Additional fallback for when all resources are loaded
            window.addEventListener('load', function () {
                initializePhotoDeletion();
            });

            // Handle photo deletion modal
            const deletePhotoModal = document.getElementById('deletePhotoModal');
            const cancelDeletePhotoBtn = document.getElementById('cancelDeletePhotoBtn');
            const confirmDeletePhotoBtn = document.getElementById('confirmDeletePhotoBtn');

            // Handle cancel photo delete
            if (cancelDeletePhotoBtn) {
                cancelDeletePhotoBtn.addEventListener('click', function () {
                    closePhotoModal();
                });
            }

            // Handle confirm photo delete
            if (confirmDeletePhotoBtn) {
                confirmDeletePhotoBtn.addEventListener('click', function () {
                    const photoIndex = document.getElementById('photoIndexToDelete').value;

                    // Get the delete button for this photo to show loading indicator
                    const deleteButton = document.querySelector(`[data-photo-index="${photoIndex}"]`);
                    let originalContent = '';
                    if (deleteButton) {
                        originalContent = deleteButton.innerHTML;
                        deleteButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                        deleteButton.disabled = true;
                    }

                    // Get CSRF token
                    const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
                    if (!csrfTokenElement) {
                        console.error('CSRF token not found');
                        alert('Erreur: Token de sécurité non trouvé.');
                        // Restore button
                        if (deleteButton) {
                            deleteButton.innerHTML = originalContent;
                            deleteButton.disabled = false;
                        }
                        closePhotoModal();
                        return;
                    }

                    const csrfToken = csrfTokenElement.getAttribute('content');
                    if (!csrfToken) {
                        console.error('CSRF token content is empty');
                        alert('Erreur: Token de sécurité invalide.');
                        // Restore button
                        if (deleteButton) {
                            deleteButton.innerHTML = originalContent;
                            deleteButton.disabled = false;
                        }
                        closePhotoModal();
                        return;
                    }

                    fetch(`/prestataire/urgent-sales/{{ $urgentSale->id }}/photos/${photoIndex}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        }
                    })
                        .then(response => {
                            console.log('Response status:', response.status);
                            // Check if the response is OK (status in the range 200-299)
                            if (!response.ok) {
                                // Try to parse the error response
                                return response.json().then(err => {
                                    console.error('Server error response:', err);
                                    // Create a more descriptive error message
                                    const errorMessage = err.message || `Erreur serveur: ${response.status} ${response.statusText}`;
                                    throw new Error(errorMessage);
                                }).catch(parseError => {
                                    // If we can't parse JSON, throw a generic error
                                    console.error('Failed to parse error response:', parseError);
                                    throw new Error(`Erreur serveur: ${response.status} ${response.statusText}`);
                                });
                            }
                            // Try to parse the success response
                            return response.json().catch(parseError => {
                                console.error('Failed to parse success response:', parseError);
                                throw new Error('Réponse invalide du serveur');
                            });
                        })
                        .then(data => {
                            console.log('Success response:', data);
                            if (data.success) {
                                const photoElement = document.getElementById(`photo-container-${photoIndex}`);
                                if (photoElement) {
                                    photoElement.remove();
                                } else {
                                    console.warn('Photo element not found for index:', photoIndex);
                                    // Fallback: try to find and remove the element by traversing from the button
                                    if (deleteButton && deleteButton.closest) {
                                        const container = deleteButton.closest('.group');
                                        if (container) {
                                            container.remove();
                                        }
                                    }
                                }

                                // Also remove any duplicate elements with the same ID
                                const duplicateElements = document.querySelectorAll(`[id="photo-container-${photoIndex}"]`);
                                duplicateElements.forEach(element => {
                                    if (element !== photoElement) {
                                        element.remove();
                                    }
                                });
                            } else {
                                alert(data.message || 'Erreur lors de la suppression de la photo.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Erreur: ' + error.message);
                        })
                        .finally(() => {
                            // Restore button
                            if (deleteButton) {
                                deleteButton.innerHTML = originalContent;
                                deleteButton.disabled = false;
                            }
                            closePhotoModal();
                        });
                });
            }

            // Close photo modal when clicking outside
            if (deletePhotoModal) {
                deletePhotoModal.addEventListener('click', function (e) {
                    if (e.target === deletePhotoModal) {
                        closePhotoModal();
                    }
                });
            }

            // Close photo modal with Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && deletePhotoModal && !deletePhotoModal.classList.contains('hidden')) {
                    closePhotoModal();
                }
            });

            // Function to close photo modal with animation
            function closePhotoModal() {
                const modalContent = deletePhotoModal.querySelector('.modal-show');
                if (modalContent) {
                    modalContent.classList.remove('scale-100');
                    modalContent.classList.add('scale-95');
                    modalContent.classList.add('opacity-0');
                }
                if (deletePhotoModal) {
                    deletePhotoModal.classList.add('opacity-0');

                    setTimeout(() => {
                        deletePhotoModal.classList.add('hidden');
                    }, 300);
                }
            }
        });
    </script>
@endpush

<!-- Modal de confirmation de suppression de photo -->
<div id="deletePhotoModal"
    class="fixed inset-0 flex items-center justify-center z-50 hidden transition-opacity duration-300"
    style="backdrop-filter: blur(5px); background-color: rgba(239, 68, 68, 0.8);">
    <div
        class="bg-white rounded-xl shadow-2xl p-6 sm:p-8 max-w-md w-full mx-4 border-4 border-red-500 transform transition-all duration-300">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100">
                <svg class="h-10 w-10 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mt-4">Confirmation de suppression</h3>
            <p class="text-gray-600 mt-2">
                Êtes-vous sûr de vouloir supprimer cette photo ?
            </p>
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <button id="cancelDeletePhotoBtn"
                    class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200 font-medium">
                    Annuler
                </button>
                <button id="confirmDeletePhotoBtn"
                    class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 font-medium">
                    Supprimer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden input to store the photo index to delete -->
<input type="hidden" id="photoIndexToDelete" value="">