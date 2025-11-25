@extends('layouts.app')

@section('title', 'Tous les services - TaPrestation')

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
<div class="bg-blue-50">
    <!-- Bannière d'en-tête -->
    <div class="max-w-8xl mx-auto px-3 sm:px-4 lg:px-6 py-2 sm:py-3">
        <div class="max-w-8xl mx-auto">
            <div class="mb-2 sm:mb-3 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-blue-900 mb-1 leading-tight">
                    Services Professionnels
                </h1>
                <p class="text-base sm:text-lg text-blue-700 max-w-3xl mx-auto">
                    Découvrez l'expertise de nos prestataires qualifiés pour tous vos besoins.
                </p>
            </div>
        </div>
    </div>
                        
        <!-- Section des filtres -->
        <div class="max-w-8xl mx-auto px-3 sm:px-4 lg:px-6 py-1 sm:py-2">
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-3 sm:p-4 mb-4">
            <div class="mb-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="text-center sm:text-left">
                    <h3 class="text-xl sm:text-2xl font-bold text-blue-800 mb-0.5">Filtres de recherche</h3>
                    <p class="text-sm text-blue-700">Affinez votre recherche pour trouver le service parfait</p>
                </div>
                <button type="button" id="toggleFilters" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 px-3 sm:py-2 sm:px-4 rounded-lg transition duration-200 shadow hover:shadow-md flex items-center justify-center text-sm">
                    <span id="filterButtonText">Afficher les filtres</span>
                    <i class="fas fa-chevron-down ml-1.5" id="filterChevron"></i>
                </button>
            </div>
            
            <form method="GET" action="{{ route('services.index') }}" class="space-y-4" id="filtersForm" style="display: none;">
                <!-- Première ligne de filtres -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-2 sm:gap-3">
                    <!-- Recherche par mot-clé -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="text" name="search" id="search" value="{{ $currentSearch }}" placeholder="Services, prestataires, mots-clés..." class="w-full pl-8 pr-3 py-1.5 sm:py-2 text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </div>
                    </div>
                    
                    <!-- Catégorie principale -->
                    <div>
                        <label for="main_category" class="block text-sm font-medium text-gray-700 mb-1">Catégorie principale</label>
                        <div class="relative">
                            <i class="fas fa-tags absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                            <select name="main_category" id="main_category" class="w-full pl-8 pr-3 py-1.5 sm:py-2 text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <option value="">Toutes les catégories</option>
                                @foreach($categories->whereNull('parent_id') as $category)
                                    <option value="{{ $category->id }}" {{ request('main_category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Sous-catégorie -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Sous-catégorie</label>
                        <div class="relative">
                            <i class="fas fa-tag absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                            <select name="category" id="category" class="w-full pl-8 pr-3 py-1.5 sm:py-2 text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" disabled>
                                <option value="">Sélectionnez d'abord une catégorie principale</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Prix minimum -->
                    <div>
                        <label for="price_min" class="block text-sm font-medium text-gray-700 mb-1">Prix minimum</label>
                        <div class="relative">
                            <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="number" name="price_min" id="price_min" value="{{ $currentPriceMin }}" placeholder="Prix min" min="0" class="w-full pl-8 pr-3 py-1.5 sm:py-2 text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </div>
                    </div>
                    
                    <!-- Prix maximum -->
                    <div>
                        <label for="price_max" class="block text-sm font-medium text-gray-700 mb-1">Prix maximum</label>
                        <div class="relative">
                            <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="number" name="price_max" id="price_max" value="{{ $currentPriceMax }}" placeholder="Prix max" min="0" class="w-full pl-8 pr-3 py-1.5 sm:py-2 text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </div>
                    </div>
                    
                    <!-- Tri par -->
                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Trier par</label>
                        <div class="relative">
                            <i class="fas fa-sort absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select name="sort" id="sort" class="w-full pl-10 pr-4 py-1.5 sm:py-2 text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
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
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Localisation</label>
                        <div class="flex gap-1">
                            <div class="relative flex-1">
                                <i class="fas fa-map-marker-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                                <input type="text" name="location" id="location" value="{{ $currentLocation }}" placeholder="Ville ou code postal" class="w-full pl-8 pr-3 py-1.5 sm:py-2 text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <!-- Dropdown suggestions -->
                                <div id="location-suggestions" class="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-lg shadow-xl mt-1 z-[99999] hidden max-h-60 overflow-y-auto" style="z-index: 99999 !important; position: absolute !important;">
                                    <!-- Suggestions will be populated here -->
                                </div>
                            </div>
                            <button type="button" id="getLocationBtn" onclick="getMyLocation()" class="px-2 py-1.5 sm:py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200 flex items-center justify-center min-w-[50px] text-xs" title="Utiliser ma position">
                                <i class="fas fa-crosshairs mr-1"></i>
                                <span class="hidden sm:inline">GPS</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Prestataire certifié -->
                    <div class="flex items-center sm:col-span-2">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="verified_only" value="1" {{ $currentVerifiedOnly ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Prestataires certifiés uniquement</span>
                        </label>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row gap-2 pt-3 border-t border-blue-200">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 sm:py-2 px-3 sm:px-4 rounded-lg transition duration-200 shadow hover:shadow-md flex items-center justify-center text-sm">
                        Appliquer les filtres
                    </button>
                    
                    <button type="button" onclick="clearFilters()" class="flex-1 bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-1.5 sm:py-2 px-3 sm:px-4 rounded-lg transition duration-200 flex items-center justify-center text-sm">
                        Effacer tout
                    </button>
                    
                    @if($currentSearch || $currentCategory || $currentMainCategory || $currentPriceMin || $currentPriceMax || $currentLocation || $currentVerifiedOnly || $currentSort)
                        <a href="{{ route('services.index') }}" class="bg-white hover:bg-gray-50 text-blue-600 border border-blue-200 font-bold py-1.5 sm:py-2 px-3 sm:px-4 rounded-lg transition duration-200 flex items-center justify-center text-sm">
                            Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
            
            <!-- Affichage des résultats -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pt-3 border-t border-blue-200 mt-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs sm:text-sm font-semibold text-blue-800">Résultats :</span>
                    <span class="px-2 sm:px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs sm:text-sm font-bold">
                        {{ $services->total() }} service(s)
                    </span>
                </div>
                @if($services->total() > 0)
                    <div class="text-xs sm:text-sm font-semibold text-blue-700">
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
    const categoriesData = @json($categories->mapWithKeys(function($category) {
        return [$category->id => $category->children];
    }));
    
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
        
        console.log('Selecting location from Nominatim:', displayName);
        
        document.getElementById('location').value = displayName;
        
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
        <div class="max-w-8xl mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-8">
        @if($services->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-6">
                @foreach($services as $service)
                    <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-blue-100 service-card flex flex-col h-full">
                        <!-- Images du service -->
                        @if($service->images && $service->images->count() > 0)
                            <div class="relative h-40 sm:h-44 overflow-hidden">
                                <img src="{{ asset('storage/' . $service->images->first()->image_path) }}" 
                                     alt="{{ $service->title }}" 
                                     class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
                                @if($service->images->count() > 1)
                                    <div class="absolute top-2 right-2 bg-black bg-opacity-60 text-white px-1.5 py-1 rounded-full text-xs">
                                        <i class="fas fa-images mr-1"></i>
                                        {{ $service->images->count() }}
                                    </div>
                                @endif
                                @if($service->price)
                                    <div class="absolute bottom-2 right-2">
                                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-2 py-1.5 rounded-lg shadow-md">
                                            <span class="text-base font-bold">{{ number_format($service->price, 0, ',', ' ') }}€</span>
                                            @if($service->price_type)
                                                <div class="text-xs text-white opacity-90">/ {{ $service->price_type }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="relative h-40 sm:h-44 bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-image text-3xl text-blue-400 mb-1"></i>
                                    <p class="text-blue-600 font-medium text-sm">Aucune image</p>
                                </div>
                                @if($service->price)
                                    <div class="absolute bottom-2 right-2">
                                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-2 py-1.5 rounded-lg shadow-md">
                                            <span class="text-base font-bold">{{ number_format($service->price, 0, ',', ' ') }}€</span>
                                            @if($service->price_type)
                                                <div class="text-xs text-white opacity-90">/ {{ $service->price_type }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                        
                        <!-- Contenu de la carte -->
                        <div class="p-3 flex flex-col flex-grow">
                            <!-- En-tête avec titre et prestataire -->
                            <div class="mb-2">
                                <h3 class="text-base font-bold text-blue-900 mb-1.5 line-clamp-2">
                                {{ $service->title }}
                            </h3>
                                <div class="flex items-center text-gray-600 text-xs">
                                    <div class="relative mr-1.5">
                                        @if($service->prestataire->photo)
                                            <img src="{{ asset('storage/' . $service->prestataire->photo) }}" alt="{{ $service->prestataire->user->name }}" class="w-7 h-7 rounded-full object-cover">
                                        @else
                                            <div class="w-7 h-7 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-blue-600 text-xs"></i>
                                            </div>
                                        @endif
                                        @if($service->prestataire->isVerified())
                                            <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-green-500 rounded-full flex items-center justify-center">
                                                <i class="fas fa-check text-white" style="font-size: 6px;"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex items-center">
                                        <span class="font-medium truncate max-w-[100px]">{{ $service->prestataire->user->name }}</span>
                                        @if($service->prestataire->isVerified())
                                            <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-0.5" style="font-size: 8px;"></i>
                                                Vérifié
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <!-- Contenu flexible -->
                            <div class="flex-grow">
                                <p class="text-gray-700 mb-2 line-clamp-3 leading-relaxed text-xs">
                                    {{ Str::limit($service->description, 100) }}
                                </p>
                                
                                @if($service->categories->count() > 0)
                                    <div class="mb-2">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($service->categories->take(1) as $category)
                                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-1.5 py-0.5 rounded-full">
                                                    {{ Str::limit($category->name, 15) }}
                                                </span>
                                            @endforeach
                                            @if($service->categories->count() > 1)
                                                <span class="bg-gray-100 text-gray-600 text-xs font-medium px-1.5 py-0.5 rounded-full">
                                                    +{{ $service->categories->count() - 1 }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Informations complémentaires -->
                                <div class="flex flex-col text-xs text-gray-600 mb-2 pt-1.5 border-t border-gray-100 gap-1">
                                    <span class="flex items-center font-medium text-gray-500">
                                        <i class="fas fa-clock mr-1 text-gray-400 text-xs"></i>
                                        {{ $service->created_at->diffForHumans() }}
                                    </span>
                                    <span class="flex items-center font-medium truncate">
                                        <i class="fas fa-map-marker-alt mr-1 text-gray-400 flex-shrink-0 text-xs"></i>
                                        <span class="truncate">
                                            @if($service->city)
                                                {{ Str::limit($service->city, 15) }}
                                            @elseif($service->address)
                                                {{ Str::limit($service->address, 15) }}
                                            @elseif($service->prestataire->city)
                                                {{ Str::limit($service->prestataire->city, 15) }}
                                            @else
                                                Non spécifié
                                            @endif
                                        </span>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Actions - Toujours en bas -->
                            <div class="flex flex-col space-y-1.5 mt-auto pt-2">
                                <a href="{{ route('services.show', $service) }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 px-2 rounded-lg transition duration-200 shadow hover:shadow-md text-center flex items-center justify-center text-xs">
                                    <i class="fas fa-eye mr-1"></i>
                                    Détails
                                </a>
                                
                                @auth
                                    @if(auth()->user()->role === 'client')
                                        <a href="{{ route('bookings.create', $service) }}" class="w-full bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-1.5 px-2 rounded-lg transition duration-200 text-center flex items-center justify-center text-xs">
                                            <i class="fas fa-calendar-check mr-1"></i>
                                            Réserver
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="w-full bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-1.5 px-2 rounded-lg transition duration-200 text-center flex items-center justify-center text-xs">
                                        <i class="fas fa-sign-in-alt mr-1"></i>
                                        Connexion
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
                <p class="text-blue-700 mb-2">Nous n'avons trouvé aucun service correspondant à vos critères de recherche.</p>
                <p class="text-blue-600 mb-6">Essayez de modifier vos filtres ou explorez tous nos services.</p>
                
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    @if(request()->anyFilled(['search', 'category', 'price_min', 'price_max', 'location', 'premium', 'with_portfolio']))
                        <a href="{{ route('services.index') }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            Réinitialiser les filtres
                        </a>
                    @else
                        <a href="{{ route('services.index') }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            Voir tous les services
                        </a>
                    @endif
                    
                    <a href="{{ route('home') }}" 
                       class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-3 px-6 rounded-lg transition duration-200">
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