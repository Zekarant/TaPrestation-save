@extends('layouts.app')

@section('title', 'Tous les services - TaPrestation')

@push('styles')
    @include('components.listing-desktop-cards-style')
@endpush

@php
    // Récupérer les filtres de session s'ils existent, sinon utiliser les paramètres de requête
    $sessionFilters = session('services_filters', []);
    $currentSearch = request('search', $sessionFilters['search'] ?? '');
    $currentCategory = request('category', $sessionFilters['category'] ?? '');
    $currentMainCategory = request('main_category', $sessionFilters['main_category'] ?? '');
    $currentPriceMin = request('price_min', $sessionFilters['price_min'] ?? '');
    $currentPriceMax = request('price_max', $sessionFilters['price_max'] ?? '');
    $currentLocation = request('location', $sessionFilters['location'] ?? '');
    $currentVerifiedOnly = request('verified_only', $sessionFilters['verified_only'] ?? false);
    $currentSort = request('sort', $sessionFilters['sort'] ?? '');
@endphp

@section('content')
<div class="pb-24 sm:pb-12">
    <!-- Bannière d'en-tête -->
    <div class="text-white" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #4338ca 100%);">
        <div class="max-w-8xl mx-auto px-4 py-6 sm:py-10 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl mb-3" style="background: rgba(255,255,255,0.2);">
                <i class="fas fa-briefcase text-xl sm:text-2xl text-white"></i>
            </div>
            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight mb-1 sm:mb-2">
                Services Professionnels
            </h1>
            <p class="text-sm sm:text-lg max-w-2xl mx-auto" style="color: #bfdbfe;">
                Trouvez l'expert idéal pour votre projet.
            </p>
        </div>
    </div>
                        
    <!-- Section des filtres -->
    <div class="max-w-8xl mx-auto px-3 sm:px-4 mb-6 sm:mb-8 -mt-4 sm:-mt-6">
        <div class="rounded-2xl sm:rounded-3xl shadow-lg sm:shadow-xl border-2 overflow-hidden" style="background: linear-gradient(to bottom, #eff6ff 0%, #ffffff 100%); border-color: #93c5fd;">
            <div class="h-1.5 sm:h-2" style="background: linear-gradient(to right, #2563eb 0%, #3b82f6 50%, #6366f1 100%);"></div>
            <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-4 sm:mb-6">
                <div>
                    <h3 class="text-base sm:text-xl font-bold flex items-center" style="color: #1e3a8a;">
                        <span class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl text-white flex items-center justify-center mr-2 sm:mr-3 shadow-md" style="background: #2563eb;">
                            <i class="fas fa-filter text-sm sm:text-base"></i>
                        </span>
                        Filtrer les services
                    </h3>
                </div>
                <button type="button" id="toggleFilters" class="text-white font-semibold py-2 px-4 sm:py-2.5 sm:px-5 rounded-lg sm:rounded-xl transition-all flex items-center justify-center text-xs sm:text-sm shadow-md" style="background: #2563eb;">
                    <span id="filterButtonText">Afficher les filtres</span>
                    <i class="fas fa-chevron-down ml-2" id="filterChevron"></i>
                </button>
            </div>
            
            <form method="GET" action="{{ route('services.index') }}" class="space-y-6" id="filtersForm" style="display: none;">
                <!-- Première ligne de filtres -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-2 sm:gap-3">
                    <!-- Recherche par mot-clé -->
                    <div>
                        <label for="search" class="block text-xs font-bold text-blue-600 uppercase tracking-wider mb-1 sm:mb-2">Recherche</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 text-blue-400"></i>
                            <input type="text" name="search" id="search" value="{{ $currentSearch }}" placeholder="Mots-clés..." class="w-full pl-9 sm:pl-10 pr-3 sm:pr-4 py-2.5 sm:py-3 text-sm bg-white border border-blue-100 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-300 text-slate-700 placeholder-slate-400 transition-all">
                        </div>
                    </div>
                    
                    <!-- Catégorie principale -->
                    <div>
                        <label for="main_category" class="block text-xs font-bold text-blue-600 uppercase tracking-wider mb-1 sm:mb-2">Catégorie</label>
                        <div class="relative">
                            <i class="fas fa-tags absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 text-blue-400"></i>
                            <select name="main_category" id="main_category" class="w-full pl-9 sm:pl-10 pr-6 sm:pr-8 py-2.5 sm:py-3 text-sm bg-white border border-blue-100 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-300 text-slate-700 appearance-none transition-all">
                                <option value="">Toutes</option>
                                @foreach($categories->whereNull('parent_id') as $category)
                                    <option value="{{ $category->id }}" {{ request('main_category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Sous-catégorie -->
                    <div>
                        <label for="category" class="block text-xs font-bold text-blue-600 uppercase tracking-wider mb-1 sm:mb-2">Sous-catégorie</label>
                        <div class="relative">
                            <i class="fas fa-tag absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 text-blue-400"></i>
                            <select name="category" id="category" class="w-full pl-9 sm:pl-10 pr-6 sm:pr-8 py-2.5 sm:py-3 text-sm bg-white border border-blue-100 rounded-xl focus:ring-2 focus:ring-blue-500 text-slate-700 appearance-none transition-all disabled:opacity-50" disabled>
                                <option value="">Choisir catégorie...</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Prix minimum -->
                    <div>
                        <label for="price_min" class="block text-xs font-bold text-blue-600 uppercase tracking-wider mb-1 sm:mb-2">Prix min</label>
                        <div class="relative">
                            <i class="fas fa-euro-sign absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 text-blue-400"></i>
                            <input type="number" name="price_min" id="price_min" value="{{ $currentPriceMin }}" placeholder="0" min="0" class="w-full pl-9 sm:pl-10 pr-3 sm:pr-4 py-2.5 sm:py-3 text-sm bg-white border border-blue-100 rounded-xl focus:ring-2 focus:ring-blue-500 text-slate-700 placeholder-slate-400 transition-all">
                        </div>
                    </div>
                    
                    <!-- Prix maximum -->
                    <div>
                        <label for="price_max" class="block text-xs font-bold text-blue-600 uppercase tracking-wider mb-1 sm:mb-2">Prix max</label>
                        <div class="relative">
                            <i class="fas fa-euro-sign absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 text-blue-400"></i>
                            <input type="number" name="price_max" id="price_max" value="{{ $currentPriceMax }}" placeholder="Max" min="0" class="w-full pl-9 sm:pl-10 pr-3 sm:pr-4 py-2.5 sm:py-3 text-sm bg-white border border-blue-100 rounded-xl focus:ring-2 focus:ring-blue-500 text-slate-700 placeholder-slate-400 transition-all">
                        </div>
                    </div>
                    
                    <!-- Tri par -->
                    <div>
                        <label for="sort" class="block text-xs font-bold text-blue-600 uppercase tracking-wider mb-1 sm:mb-2">Trier par</label>
                        <div class="relative">
                            <i class="fas fa-sort absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 text-blue-400"></i>
                            <select name="sort" id="sort" class="w-full pl-9 sm:pl-10 pr-6 sm:pr-8 py-2.5 sm:py-3 text-sm bg-white border border-blue-100 rounded-xl focus:ring-2 focus:ring-blue-500 text-slate-700 appearance-none transition-all">
                                <option value="">Pertinence</option>
                                <option value="price_asc" {{ $currentSort == 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                                <option value="price_desc" {{ $currentSort == 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                                <option value="recent" {{ $currentSort == 'recent' ? 'selected' : '' }}>Plus récents</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Deuxième ligne de filtres -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
                    <!-- Localisation -->
                    <div>
                        <label for="location" class="block text-xs font-bold text-blue-600 uppercase tracking-wider mb-1 sm:mb-2">Localisation</label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <i class="fas fa-map-marker-alt absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 text-blue-400"></i>
                                <input type="text" name="location" id="location" value="{{ $currentLocation }}" placeholder="Ville ou code postal" class="w-full pl-9 sm:pl-10 pr-3 sm:pr-4 py-2.5 sm:py-3 text-sm bg-white border border-blue-100 rounded-xl focus:ring-2 focus:ring-blue-500 text-slate-700 placeholder-slate-400 transition-all">
                                <!-- Dropdown suggestions -->
                                <div id="location-suggestions" class="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-lg shadow-xl mt-1 z-[99999] hidden max-h-60 overflow-y-auto" style="z-index: 99999 !important; position: absolute !important;">
                                    <!-- Suggestions will be populated here -->
                                </div>
                            </div>
                            <button type="button" id="getLocationBtn" onclick="getMyLocation()" class="px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition duration-200 flex items-center justify-center" title="Utiliser ma position">
                                <i class="fas fa-crosshairs mr-2"></i>
                                <span class="hidden sm:inline">GPS</span>
                            </button>
                        </div>
                        <input type="hidden" name="latitude" id="filter_latitude" value="{{ request('latitude') }}">
                        <input type="hidden" name="longitude" id="filter_longitude" value="{{ request('longitude') }}">
                    </div>
                    
                    <!-- Rayon de recherche -->
                    <div>
                        <label for="radius" class="block text-xs font-bold text-blue-600 uppercase tracking-wider mb-1 sm:mb-2">Périmètre</label>
                        <div class="relative">
                            <i class="fas fa-bullseye absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 text-blue-400"></i>
                            <select name="radius" id="radius" class="w-full pl-9 sm:pl-10 pr-6 sm:pr-8 py-2.5 sm:py-3 text-sm bg-white border border-blue-100 rounded-xl focus:ring-2 focus:ring-blue-500 text-slate-700 appearance-none transition-all">
                                <option value="">Partout</option>
                                <option value="5" {{ request('radius') == '5' ? 'selected' : '' }}>5 km</option>
                                <option value="10" {{ request('radius') == '10' ? 'selected' : '' }}>10 km</option>
                                <option value="25" {{ request('radius') == '25' ? 'selected' : '' }}>25 km</option>
                                <option value="50" {{ request('radius') == '50' ? 'selected' : '' }}>50 km</option>
                                <option value="100" {{ request('radius') == '100' ? 'selected' : '' }}>100 km</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Prestataire certifié -->
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer bg-blue-50 px-4 py-3 rounded-xl border border-blue-100">
                            <input type="checkbox" name="verified_only" value="1" {{ $currentVerifiedOnly ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-3 text-sm text-slate-700 font-medium">Prestataires certifiés uniquement</span>
                        </label>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 text-sm rounded-xl transition duration-200 shadow hover:shadow-md flex items-center justify-center">
                        <i class="fas fa-search mr-2"></i>
                        Appliquer les filtres
                    </button>
                    
                    <button type="button" onclick="clearFilters()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 sm:py-3 px-4 sm:px-6 text-sm rounded-xl transition duration-200 flex items-center justify-center">
                        <i class="fas fa-times mr-2"></i>
                        Effacer tout
                    </button>
                    
                    @if($currentSearch || $currentCategory || $currentMainCategory || $currentPriceMin || $currentPriceMax || $currentLocation || $currentVerifiedOnly || $currentSort)
                        <a href="{{ route('services.index') }}" class="bg-white hover:bg-gray-50 text-blue-600 border-2 border-blue-200 font-bold py-2.5 sm:py-3 px-4 sm:px-6 text-sm rounded-xl transition duration-200 flex items-center justify-center">
                            <i class="fas fa-redo mr-2"></i>
                            Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
            
            <!-- Affichage des résultats -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 border-t border-slate-100 mt-4">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-slate-600">Résultats :</span>
                    <span class="px-3 py-1.5 bg-blue-100 text-blue-800 rounded-full text-sm font-bold">
                        {{ $services->total() }} service(s)
                    </span>
                </div>
                @if($services->total() > 0)
                    <div class="text-sm font-semibold text-slate-500">
                        {{ $services->pluck('prestataire_id')->unique()->count() }} prestataires actifs
                    </div>
                @endif
            </div>
            </div>
        </div>

<script>
// Global variables for geolocation
let userLatitude = null;
let userLongitude = null;

document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggleFilters');
    const filtersForm = document.getElementById('filtersForm');
    const buttonText = document.getElementById('filterButtonText');
    const chevron = document.getElementById('filterChevron');
    
    // Check if there are any active filters to determine initial state
    const hasActiveFilters = document.querySelector('input[name="search"]').value || 
                            document.querySelector('select[name="main_category"]').value || 
                            document.querySelector('select[name="category"]').value || 
                            document.querySelector('input[name="price_min"]').value || 
                            document.querySelector('input[name="price_max"]').value || 
                            document.querySelector('input[name="location"]').value || 
                            document.querySelector('input[name="verified_only"]').checked || 
                            document.querySelector('select[name="sort"]').value;
    
    // If there are active filters, show the form by default
    if (hasActiveFilters) {
        filtersForm.style.display = 'block';
        buttonText.textContent = 'Masquer les filtres';
        chevron.classList.remove('fa-chevron-down');
        chevron.classList.add('fa-chevron-up');
    }
    
    toggleButton.addEventListener('click', function() {
        if (filtersForm.style.display === 'none' || filtersForm.style.display === '') {
            filtersForm.style.display = 'block';
            buttonText.textContent = 'Masquer les filtres';
            chevron.classList.remove('fa-chevron-down');
            chevron.classList.add('fa-chevron-up');
        } else {
            filtersForm.style.display = 'none';
            buttonText.textContent = 'Afficher les filtres';
            chevron.classList.remove('fa-chevron-up');
            chevron.classList.add('fa-chevron-down');
        }
    });
    
    // Gestion des catégories hiérarchiques
    const mainCategorySelect = document.getElementById('main_category');
    const subcategorySelect = document.getElementById('category');
    
    // Données des catégories (passées depuis le contrôleur)
    @php
        $categoriesData = $categories->mapWithKeys(function($category) {
            return [$category->id => $category->children];
        });
    @endphp
    const categoriesData = @json($categoriesData);
    
    // Fonction pour charger les sous-catégories
    function loadSubcategories(mainCategoryId) {
        subcategorySelect.innerHTML = '<option value="">Toutes les sous-catégories</option>';
        
        if (mainCategoryId && categoriesData[mainCategoryId]) {
            const subcategories = categoriesData[mainCategoryId];
            
            if (subcategories.length > 0) {
                subcategorySelect.disabled = false;
                subcategories.forEach(function(subcategory) {
                    const option = document.createElement('option');
                    option.value = subcategory.id;
                    option.textContent = subcategory.name;
                    if ('{{ $currentCategory }}' == subcategory.id) {
                        option.selected = true;
                    }
                    subcategorySelect.appendChild(option);
                });
            } else {
                subcategorySelect.disabled = true;
                subcategorySelect.innerHTML = '<option value="">Aucune sous-catégorie disponible</option>';
            }
        } else {
            subcategorySelect.disabled = true;
            subcategorySelect.innerHTML = '<option value="">Sélectionnez d\'abord une catégorie principale</option>';
        }
    }
    
    // Écouter les changements de catégorie principale
    if (mainCategorySelect) {
        mainCategorySelect.addEventListener('change', function() {
            loadSubcategories(this.value);
        });
        
        // Charger les sous-catégories si une catégorie principale est déjà sélectionnée
        const selectedMainCategory = mainCategorySelect.value;
        if (selectedMainCategory) {
            loadSubcategories(selectedMainCategory);
        }
    }

    // Autocomplete variables
    let searchTimeout;
    let currentFocus = -1;
    const locationInput = document.getElementById('location');
    const suggestionsContainer = document.getElementById('location-suggestions');

    // Initialize autocomplete functionality
    if (locationInput && suggestionsContainer) {
        // Handle input changes
        locationInput.addEventListener('input', function() {
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
        locationInput.addEventListener('keydown', function(e) {
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
        locationInput.addEventListener('focus', function() {
            const query = this.value.trim();
            if (query.length >= 2) {
                fetchLocationSuggestions(query);
            }
        });
        
        // Close suggestions when clicking elsewhere
        document.addEventListener('click', function(e) {
            if (locationInput && suggestionsContainer && 
                !locationInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                hideSuggestions();
                currentFocus = -1;
            }
        });
    }

    function clearFilters() {
        const form = document.getElementById('filtersForm');
        form.reset();
        
        // Clear search input
        document.getElementById('search').value = '';
        
        // Reset subcategory dropdown
        const subcategorySelect = document.getElementById('category');
        subcategorySelect.innerHTML = '<option value="">Sélectionnez d\'abord une catégorie principale</option>';
        subcategorySelect.disabled = true;
        
        window.location.href = '{{ route('services.index') }}';
    }

    // Fonction pour obtenir la géolocalisation (version améliorée)
    window.getMyLocation = function() {
        const locationInput = document.getElementById('location');
        const btn = document.getElementById('getLocationBtn');
        
        if (!navigator.geolocation) {
            alert('La géolocalisation n\'est pas supportée par ce navigateur.');
            return;
        }
        
        // Changer l'état du bouton pendant le chargement
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span class="hidden sm:inline">Localisation...</span>';
        btn.disabled = true;
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // Stocker les coordonnées globalement
                userLatitude = lat;
                userLongitude = lng;
                
                // Stocker dans les champs hidden pour le filtre par rayon
                const latInput = document.getElementById('filter_latitude');
                const lngInput = document.getElementById('filter_longitude');
                if (latInput) latInput.value = lat;
                if (lngInput) lngInput.value = lng;
                
                // Utiliser l'API de géocodage inverse gratuite de Nominatim (OpenStreetMap)
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=fr`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.address) {
                            const address = data.address;
                            const city = address.city || address.town || address.village || address.municipality || '';
                            const postcode = address.postcode || '';
                            
                            if (city) {
                                locationInput.value = postcode ? `${city}, ${postcode}` : city;
                            } else if (data.display_name) {
                                // Extraire les parties pertinentes de l'adresse complète
                                const parts = data.display_name.split(',');
                                locationInput.value = parts.slice(0, 2).join(',').trim();
                            } else {
                                locationInput.value = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                            }
                        } else {
                            // Fallback: utiliser les coordonnées
                            locationInput.value = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                        }
                    })
                    .catch(error => {
                        console.error('Erreur de géocodage:', error);
                        // Fallback: utiliser les coordonnées
                        locationInput.value = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                    })
                    .finally(() => {
                        // Restaurer l'état du bouton
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    });
            },
            function(error) {
                let errorMessage = 'Erreur de géolocalisation: ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage += 'Permission refusée.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage += 'Position indisponible.';
                        break;
                    case error.TIMEOUT:
                        errorMessage += 'Délai d\'attente dépassé.';
                        break;
                    default:
                        errorMessage += 'Erreur inconnue.';
                        break;
                }
                alert(errorMessage);
                
                // Restaurer l'état du bouton
                btn.innerHTML = originalText;
                btn.disabled = false;
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000
            }
        );
    }

    function fetchLocationSuggestions(query) {
        console.log('Fetching suggestions for:', query); // Debug log
        fetch(`/api/public/geolocation/cities?search=${encodeURIComponent(query)}&limit=10`)
            .then(response => {
                console.log('API Response status:', response.status); // Debug log
                return response.json();
            })
            .then(data => {
                console.log('API Data received:', data); // Debug log
                if (data.success && data.data && data.data.length > 0) {
                    displaySuggestions(data.data, query);
                } else {
                    // Fallback to Nominatim if our API doesn't return results
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=10&addressdetails=1`)
                        .then(response => response.json())
                        .then(fallbackData => {
                            if (fallbackData && fallbackData.length > 0) {
                                displayNominatimSuggestions(fallbackData, query);
                            } else {
                                hideSuggestions();
                            }
                        })
                        .catch(error => {
                            console.error('Fallback geocoding error:', error);
                            hideSuggestions();
                        });
                }
            })
            .catch(error => {
                console.error('Primary geocoding error:', error);
                // Fallback to Nominatim
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=10&addressdetails=1`)
                    .then(response => response.json())
                    .then(fallbackData => {
                        if (fallbackData && fallbackData.length > 0) {
                            displayNominatimSuggestions(fallbackData, query);
                        } else {
                            hideSuggestions();
                        }
                    })
                    .catch(fallbackError => {
                        console.error('Fallback geocoding error:', fallbackError);
                        hideSuggestions();
                    });
            });
    }

    function displaySuggestions(suggestions, query) {
        if (!suggestionsContainer) return;
        
        suggestionsContainer.innerHTML = '';
        currentFocus = -1;

        suggestions.forEach((suggestion, index) => {
            const div = document.createElement('div');
            div.className = 'suggestion-item p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors';
            
            const highlightedText = suggestion.text.replace(
                new RegExp(`(${query})`, 'gi'),
                '<strong class="text-blue-600">$1</strong>'
            );
            
            div.innerHTML = `
                <div class="font-medium text-gray-800">${highlightedText}</div>
            `;
            
            div.setAttribute('data-city', suggestion.city);
            div.setAttribute('data-text', suggestion.text);
            
            div.addEventListener('click', () => selectLocationFromData(div));
            
            suggestionsContainer.appendChild(div);
        });

        // Show the container
        suggestionsContainer.classList.remove('hidden');
        suggestionsContainer.style.display = 'block';
    }

    function displayNominatimSuggestions(suggestions, query) {
        if (!suggestionsContainer) return;
        
        suggestionsContainer.innerHTML = '';
        currentFocus = -1;

        suggestions.forEach((suggestion, index) => {
            const div = document.createElement('div');
            div.className = 'suggestion-item p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors';
            
            const highlightedText = suggestion.display_name.replace(
                new RegExp(`(${query})`, 'gi'),
                '<strong class="text-blue-600">$1</strong>'
            );
            
            div.innerHTML = `
                <div class="font-medium text-gray-800">${highlightedText}</div>
            `;
            
            div.setAttribute('data-display-name', suggestion.display_name);
            div.setAttribute('data-lat', suggestion.lat);
            div.setAttribute('data-lon', suggestion.lon);
            
            div.addEventListener('click', () => selectLocationFromNominatimData(div));
            
            suggestionsContainer.appendChild(div);
        });

        // Show the container
        suggestionsContainer.classList.remove('hidden');
        suggestionsContainer.style.display = 'block';
    }

    function selectLocationFromData(element) {
        const text = element.getAttribute('data-text');
        
        console.log('Selecting location:', text);
        
        document.getElementById('location').value = text;
        
        // Hide the suggestions dropdown
        hideSuggestions();
    }

    function selectLocationFromNominatimData(element) {
        const displayName = element.getAttribute('data-display-name');
        const lat = element.getAttribute('data-lat');
        const lon = element.getAttribute('data-lon');
        
        console.log('Selecting location from Nominatim:', displayName, 'lat:', lat, 'lon:', lon);
        
        document.getElementById('location').value = displayName;
        
        // Stocker les coordonnées dans les champs hidden pour le filtre par rayon
        if (lat && lon) {
            const latInput = document.getElementById('filter_latitude');
            const lngInput = document.getElementById('filter_longitude');
            if (latInput) latInput.value = lat;
            if (lngInput) lngInput.value = lon;
            
            // Stocker aussi globalement
            userLatitude = parseFloat(lat);
            userLongitude = parseFloat(lon);
        }
        
        // Hide the suggestions dropdown
        hideSuggestions();
    }

    function hideSuggestions() {
        if (suggestionsContainer) {
            suggestionsContainer.classList.add('hidden');
            suggestionsContainer.style.display = 'none';
        }
    }

    function setActiveSuggestion(suggestions) {
        // Remove active class from all suggestions
        suggestions.forEach(suggestion => suggestion.classList.remove('bg-gray-100'));
        
        // Add active class to current suggestion
        if (currentFocus >= 0 && suggestions[currentFocus]) {
            suggestions[currentFocus].classList.add('bg-gray-100');
        }
    }
});
</script>

        <!-- Section des résultats -->
        <div class="max-w-8xl mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-8 desktop-listing-scope">
        @if($services->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4 lg:gap-6 desktop-listing-grid">
                @foreach($services as $service)
                    <div class="bg-white rounded-lg sm:rounded-xl shadow-sm sm:shadow-md overflow-hidden hover:shadow-lg sm:hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100 desktop-listing-item w-full min-w-0 flex flex-col h-full">
                        <!-- Images du service -->
                        @if($service->images && $service->images->count() > 0)
                            <div class="relative h-40 sm:h-44 overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 desktop-item-media">
                                <x-media-image :path="$service->images->first()->image_path" :alt="$service->title" class="w-full h-full object-cover transition-transform duration-300 hover:scale-105" />
                                @if($service->images->count() > 1)
                                    <div class="absolute top-2 sm:top-3 left-2 sm:left-3 bg-black bg-opacity-60 text-white px-2 py-1 rounded-full text-xs flex items-center gap-1 desktop-item-badge">
                                        <i class="fas fa-images"></i>
                                        <span>{{ $service->images->count() }}</span>
                                    </div>
                                @endif
                                @if($service->price)
                                    <div class="absolute top-2 sm:top-3 right-2 sm:right-3">
                                        <div class="bg-white/90 backdrop-blur-sm text-gray-900 text-xs sm:text-sm font-bold px-2 sm:px-3 py-1 sm:py-1.5 rounded-full shadow-sm desktop-item-badge">
                                            {{ number_format($service->price, 0, ',', ' ') }}€
                                            @if($service->price_type)
                                                <span class="text-gray-600 font-normal">/{{ $service->price_type }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="relative h-40 sm:h-44 bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center desktop-item-media">
                                <div class="text-center">
                                    <i class="fas fa-image text-3xl text-blue-300 mb-1"></i>
                                    <p class="text-blue-400 font-medium text-xs">Photo à venir</p>
                                </div>
                                @if($service->price)
                                    <div class="absolute top-2 sm:top-3 right-2 sm:right-3">
                                        <div class="bg-white/90 backdrop-blur-sm text-gray-900 text-xs sm:text-sm font-bold px-2 sm:px-3 py-1 sm:py-1.5 rounded-full shadow-sm desktop-item-badge">
                                            {{ number_format($service->price, 0, ',', ' ') }}€
                                            @if($service->price_type)
                                                <span class="text-gray-600 font-normal">/{{ $service->price_type }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                        
                        <!-- Contenu de la carte -->
                        <div class="p-2 sm:p-3 flex flex-col flex-grow desktop-item-body">
                            <!-- En-tête avec titre -->
                            <div class="mb-1">
                                <h3 class="text-sm sm:text-base font-bold text-gray-900 mb-1 line-clamp-2 leading-tight">
                                    {{ $service->title }}
                                </h3>
                            </div>
                            
                            <!-- Prestataire avec design amélioré -->
                            <div class="flex items-center gap-1.5 sm:gap-2 mb-2 p-1.5 bg-blue-50 rounded-lg">
                                <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full flex items-center justify-center flex-shrink-0">
                                    @if($service->prestataire->photo)
                                        <x-media-image :path="$service->prestataire->photo" :alt="$service->prestataire->user->name" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover" />
                                    @else
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                                            {{ strtoupper(substr($service->prestataire->user->name ?? '', 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs sm:text-sm font-medium text-gray-900 truncate desktop-item-muted">
                                        {{ $service->prestataire->user->name }}
                                    </p>
                                    @if($service->prestataire->isVerified())
                                        <p class="text-xs text-green-600 desktop-item-meta">Prestataire vérifié</p>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Description courte -->
                            <p class="text-gray-600 mb-2 line-clamp-2 leading-relaxed text-xs desktop-item-muted">
                                {{ Str::limit($service->description, 80) }}
                            </p>
                            
                            <!-- Catégories -->
                            @if($service->categories->count() > 0)
                                <div class="flex flex-wrap gap-1 sm:gap-1.5 mb-2">
                                    @foreach($service->categories->take(1) as $category)
                                        <span class="inline-flex items-center bg-blue-100 text-blue-800 text-xs font-medium px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full desktop-item-chip">
                                            {{ Str::limit($category->name, 15) }}
                                        </span>
                                    @endforeach
                                    @if($service->categories->count() > 1)
                                        <span class="inline-flex items-center bg-gray-100 text-gray-600 text-xs font-medium px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full desktop-item-chip">
                                            +{{ $service->categories->count() - 1 }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                            
                            <!-- Boutons d'action améliorés -->
                            <div class="space-y-1.5 sm:space-y-2 mt-auto">
                                <a href="{{ route('services.show', $service) }}" 
                                   class="block w-full text-center bg-gradient-to-r from-blue-600 to-blue-600 hover:from-blue-700 hover:to-blue-700 text-white font-semibold py-2 sm:py-2.5 lg:py-3 px-3 sm:px-4 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg text-xs sm:text-sm lg:text-base desktop-item-btn">
                                    <span class="hidden sm:inline">Voir les détails</span>
                                    <span class="sm:hidden">Détails</span>
                                </a>
                                @auth
                                    @if(in_array(auth()->user()->role, ['client', 'prestataire']) && auth()->user()->id !== $service->prestataire->user_id)
                                        <a href="{{ route('bookings.create', $service) }}" 
                                           class="block w-full text-center bg-blue-100 hover:bg-blue-200 text-blue-800 font-medium py-1.5 sm:py-2 px-3 sm:px-4 rounded-lg transition-colors duration-200 text-xs sm:text-sm desktop-item-btn">
                                            <span class="hidden sm:inline">Réservation rapide</span>
                                            <span class="sm:hidden">Réserver</span>
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" 
                                       class="block w-full text-center bg-blue-100 hover:bg-blue-200 text-blue-800 font-medium py-1.5 sm:py-2 px-3 sm:px-4 rounded-lg transition-colors duration-200 text-xs sm:text-sm desktop-item-btn">
                                        <span class="hidden sm:inline">Connexion pour réserver</span>
                                        <span class="sm:hidden">Connexion</span>
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Message d'état vide harmonisé -->
            <div class="bg-white rounded-xl shadow-md p-12 text-center border border-blue-100">
                <div class="w-24 h-24 mx-auto mb-6 bg-blue-100 rounded-full flex items-center justify-center">
                    <div class="text-3xl text-blue-600">Recherche</div>
                </div>
                <h3 class="text-xl font-bold text-blue-900 mb-3">Aucun service trouvé</h3>
                <p class="text-blue-700 mb-1 sm:mb-2">Nous n'avons trouvé aucun service correspondant à vos critères de recherche.</p>
                <p class="text-blue-600 mb-6">Essayez de modifier vos filtres ou explorez tous nos services.</p>
                
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    @if(request()->anyFilled(['search', 'category', 'price_min', 'price_max', 'location', 'premium', 'with_portfolio']))
                        <a href="{{ route('services.index') }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 text-sm rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            Réinitialiser les filtres
                        </a>
                    @else
                        <a href="{{ route('services.index') }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 text-sm rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            Voir tous les services
                        </a>
                    @endif
                    
                    <a href="{{ route('home') }}" 
                       class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 text-sm rounded-lg transition duration-200">
                        Retour à l'accueil
                    </a>
                </div>
            </div>
        @endif
    
        <!-- Pagination -->
        @if($services->hasPages())
            <div class="mt-12 flex justify-center">
                {{ $services->appends(request()->query())->links() }}
            </div>
        @endif
        </div>
    </div>
</div>
@endsection
