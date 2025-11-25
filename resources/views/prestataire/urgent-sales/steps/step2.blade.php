@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 to-pink-100">
    <div class="max-w-6xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 py-4 sm:py-6 lg:py-8">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl font-bold text-red-900 mb-2">Créer une nouvelle annonce</h1>
                <p class="text-sm sm:text-base text-red-700 px-2 sm:px-0">Publiez votre équipement ou service à vendre</p>
            </div>
            
            <!-- Indicateur d'étapes -->
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl border border-red-200 p-4 sm:p-6 lg:p-8 mb-6 sm:mb-8">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        <a href="{{ route('prestataire.urgent-sales.index') }}" class="text-red-600 hover:text-red-900 transition-colors duration-200">
                            <i class="fas fa-arrow-left text-lg sm:text-xl"></i>
                        </a>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-red-900">Nouvelle annonce</h2>
                            <p class="text-sm sm:text-base text-red-700 hidden sm:block">Étape par étape</p>
                        </div>
                    </div>
                </div>
                
                <!-- Barre de progression -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-1 sm:space-x-2 lg:space-x-4 w-full overflow-x-auto">
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-red-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                1
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-red-600 hidden sm:inline">Informations</span>
                            <span class="ml-1 text-xs font-medium text-red-600 sm:hidden">Info</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 rounded min-w-4">
                            <div class="h-1 bg-red-600 rounded" style="width: 50%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-red-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                2
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-red-600 hidden sm:inline">Localisation</span>
                            <span class="ml-1 text-xs font-medium text-red-600 sm:hidden">Lieu</span>
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
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-gray-500 hidden sm:inline">Détails</span>
                            <span class="ml-1 text-xs font-medium text-gray-500 sm:hidden">Détails</span>
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

            <!-- Étape 2: Localisation -->
            <div class="bg-white rounded-xl shadow-lg border border-red-200 p-3 sm:p-4 md:p-6 mb-4 sm:mb-6">
                <div class="flex items-center mb-3 sm:mb-4">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-red-600 text-white flex items-center justify-center text-xs sm:text-sm font-bold mr-2 sm:mr-3">
                        2
                    </div>
                    <h2 class="text-base sm:text-lg md:text-xl font-bold text-red-900">Localisation</h2>
                </div>
                
                <form id="urgentSaleStep2Form" action="{{ route('prestataire.urgent-sales.create.step2.store') }}" method="POST">
                    @csrf
                    <div class="space-y-3 sm:space-y-4">
                        <p class="text-xs sm:text-sm text-red-700 mb-3 sm:mb-4">
                            <i class="fas fa-info-circle mr-1 sm:mr-2"></i>
                            Cliquez sur la carte pour sélectionner l'emplacement de votre vente urgente
                        </p>
                        
                        <!-- Carte -->
                        <div class="map-container">
                            <div id="urgentSaleMap" class="h-64 sm:h-80 rounded-lg border border-gray-300 shadow-sm"></div>
                        </div>
                        
                        <!-- Adresse sélectionnée -->
                        <div class="mt-3 sm:mt-4 relative z-50">
                            <label for="selectedAddress" class="block text-xs sm:text-sm font-medium text-red-700 mb-1 sm:mb-2">Adresse sélectionnée *</label>
                            <input type="text" id="selectedAddress" name="location" value="{{ old('location') }}" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 @error('location') border-red-500 @enderror" placeholder="Tapez pour rechercher ou cliquez sur la carte">
                            <div id="address-suggestions" class="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-lg shadow-xl mt-1 hidden max-h-60 overflow-y-auto" style="z-index: 999999 !important; position: absolute !important;">
                                <!-- Suggestions will be populated here -->
                            </div>
                            <input type="hidden" id="selectedLatitude" name="latitude" value="{{ old('latitude') }}" required>
                            <input type="hidden" id="selectedLongitude" name="longitude" value="{{ old('longitude') }}" required>
                            @error('location')
                                <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                            @enderror
                            @error('latitude')
                                <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                            @enderror
                            @error('longitude')
                                <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 mt-3 sm:mt-4">
                            <button type="button" id="getCurrentLocationBtn" class="bg-red-600 hover:bg-red-700 text-white px-3 sm:px-4 py-2 rounded-md transition duration-200 text-xs sm:text-sm md:text-base flex items-center justify-center">
                                <i class="fas fa-location-arrow mr-1 sm:mr-2 text-xs sm:text-sm"></i>Ma position actuelle
                            </button>
                            <button type="button" id="clearLocationBtn" class="bg-gray-500 hover:bg-gray-600 text-white px-3 sm:px-4 py-2 rounded-md transition duration-200 text-xs sm:text-sm md:text-base flex items-center justify-center">
                                <i class="fas fa-times mr-1 sm:mr-2 text-xs sm:text-sm"></i>Effacer la localisation
                            </button>
                        </div>
                        
                        <!-- Conseils -->
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3 sm:p-4 mt-3 sm:mt-4">
                            <h4 class="text-xs sm:text-sm font-semibold text-red-800 mb-2">
                                <i class="fas fa-lightbulb mr-1 sm:mr-2 text-xs sm:text-sm"></i>Conseils pour la localisation
                            </h4>
                            <ul class="text-xs sm:text-sm text-red-700 space-y-1">
                                <li>• Choisissez un lieu facilement accessible pour les acheteurs</li>
                                <li>• Privilégiez les lieux publics et sécurisés pour les rencontres</li>
                                <li>• Vous pourrez ajuster la localisation précise lors du contact avec l'acheteur</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="flex justify-between mt-6 sm:mt-8">
                        <a href="{{ route('prestataire.urgent-sales.create.step1') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-sm sm:text-base">
                            <i class="fas fa-arrow-left mr-1 sm:mr-2"></i>Précédent
                        </a>
                        <button type="submit" id="step2SubmitBtn" class="bg-red-600 hover:bg-red-700 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-sm sm:text-base flex items-center">
                            Suivant<i class="fas fa-arrow-right ml-1 sm:ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endpush

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
// Global variables for map and marker
let urgentSaleMap;
let currentMarker;

// Initialize the map when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the map
    initMap();
    
    // Set up event listeners
    setupEventListeners();
});

function initMap() {
    // Set default view to France
    const defaultLat = 46.603354;
    const defaultLon = 1.888334;
    
    // Create the map
    urgentSaleMap = L.map('urgentSaleMap').setView([defaultLat, defaultLon], 6);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(urgentSaleMap);
    
    // Add click event to the map
    urgentSaleMap.on('click', function(e) {
        placeMarker(e.latlng);
    });
}

function placeMarker(latlng) {
    // Remove existing marker if present
    if (currentMarker) {
        urgentSaleMap.removeLayer(currentMarker);
    }
    
    // Add new marker
    currentMarker = L.marker(latlng).addTo(urgentSaleMap);
    
    // Reverse geocode to get address
    reverseGeocode(latlng.lat, latlng.lng);
    
    // Update form fields
    document.getElementById('selectedLatitude').value = latlng.lat.toFixed(6);
    document.getElementById('selectedLongitude').value = latlng.lng.toFixed(6);
}

function reverseGeocode(lat, lon) {
    // Use Nominatim for reverse geocoding
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1`)
        .then(response => response.json())
        .then(data => {
            if (data && data.display_name) {
                document.getElementById('selectedAddress').value = data.display_name;
            }
        })
        .catch(error => {
            console.error('Reverse geocoding error:', error);
            // Fallback to coordinates
            document.getElementById('selectedAddress').value = `Lat: ${lat.toFixed(6)}, Lon: ${lon.toFixed(6)}`;
        });
}

function setupEventListeners() {
    // Address input with autocomplete
    const addressInput = document.getElementById('selectedAddress');
    if (addressInput) {
        addressInput.addEventListener('input', function() {
            clearTimeout(autocompleteTimeout);
            const query = this.value.trim();
            if (query.length >= 2) {
                autocompleteTimeout = setTimeout(() => fetchLocationSuggestions(query), 300);
            } else {
                hideSuggestions();
            }
        });
        
        // Close suggestions when clicking elsewhere
        document.addEventListener('click', function(e) {
            if (!addressInput.contains(e.target)) {
                hideSuggestions();
            }
        });
    }
    
    // Current location button
    const getCurrentLocationBtn = document.getElementById('getCurrentLocationBtn');
    if (getCurrentLocationBtn) {
        getCurrentLocationBtn.addEventListener('click', getCurrentLocation);
    }
    
    // Clear location button
    const clearLocationBtn = document.getElementById('clearLocationBtn');
    if (clearLocationBtn) {
        clearLocationBtn.addEventListener('click', clearLocation);
    }
}

function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                
                // Center map on current location
                urgentSaleMap.setView([lat, lon], 15);
                
                // Place marker
                placeMarker({lat: lat, lng: lon});
            },
            function(error) {
                console.error('Geolocation error:', error);
                alert('Impossible de récupérer votre position actuelle. Veuillez vérifier les permissions de localisation.');
            }
        );
    } else {
        alert('La géolocalisation n\'est pas supportée par votre navigateur.');
    }
}

function clearLocation() {
    // Clear form fields
    document.getElementById('selectedAddress').value = '';
    document.getElementById('selectedLatitude').value = '';
    document.getElementById('selectedLongitude').value = '';
    
    // Remove marker if present
    if (currentMarker) {
        urgentSaleMap.removeLayer(currentMarker);
        currentMarker = null;
    }
}

// Autocomplete functionality
let autocompleteTimeout;

// Utility function to hide suggestions
function hideSuggestions() {
    const suggestionsContainer = document.getElementById('address-suggestions');
    if (suggestionsContainer) {
        suggestionsContainer.classList.add('hidden');
        suggestionsContainer.style.display = 'none';
        console.log('Suggestions hidden');
    }
}

function fetchLocationSuggestions(query) {
    if (query.length < 2) {
        hideSuggestions();
        return;
    }

    console.log('Fetching suggestions for:', query);
    
    // Try the public geolocation API first
    fetch(`/api/public/geolocation/cities?search=${encodeURIComponent(query)}&limit=10`)
        .then(response => {
            console.log('API response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('API response data:', data);
            if (data.success && data.data && data.data.length > 0) {
                displaySuggestions(data.data, query);
            } else {
                // Fallback to simple geocoding API
                console.log('No results from cities API, trying fallback');
                tryFallbackSuggestions(query);
            }
        })
        .catch(error => {
            console.error('Error fetching suggestions:', error);
            // Fallback to simple geocoding API
            tryFallbackSuggestions(query);
        });
}

function tryFallbackSuggestions(query) {
    // Simple fallback with common French cities
    const commonCities = [
        { display_name: 'Paris, France', lat: 48.8566, lon: 2.3522, country: 'France' },
        { display_name: 'Marseille, France', lat: 43.2965, lon: 5.3698, country: 'France' },
        { display_name: 'Lyon, France', lat: 45.7640, lon: 4.8357, country: 'France' },
        { display_name: 'Toulouse, France', lat: 43.6047, lon: 1.4442, country: 'France' },
        { display_name: 'Nice, France', lat: 43.7102, lon: 7.2620, country: 'France' },
        { display_name: 'Nantes, France', lat: 47.2184, lon: -1.5536, country: 'France' },
        { display_name: 'Montpellier, France', lat: 43.6110, lon: 3.8767, country: 'France' },
        { display_name: 'Strasbourg, France', lat: 48.5734, lon: 7.7521, country: 'France' },
        { display_name: 'Bordeaux, France', lat: 44.8378, lon: -0.5792, country: 'France' },
        { display_name: 'Lille, France', lat: 50.6292, lon: 3.0573, country: 'France' }
    ];
    
    const filteredCities = commonCities.filter(city => 
        city.display_name.toLowerCase().includes(query.toLowerCase())
    );
    
    if (filteredCities.length > 0) {
        console.log('Using fallback suggestions:', filteredCities);
        displaySuggestions(filteredCities, query);
    } else {
        hideSuggestions();
    }
}

function displaySuggestions(suggestions, query) {
    const container = document.getElementById('address-suggestions');
    if (!container) {
        console.error('Suggestions container not found');
        return;
    }
    
    console.log('Displaying suggestions:', suggestions);
    container.innerHTML = '';

    suggestions.forEach((suggestion, index) => {
        const div = document.createElement('div');
        div.className = 'p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors';
        
        const highlightedText = suggestion.display_name.replace(
            new RegExp(`(${query})`, 'gi'),
            '<strong class="text-red-600">$1</strong>'
        );
        
        div.innerHTML = `
            <div class="font-medium text-gray-800">${highlightedText}</div>
            <div class="text-sm text-gray-600 mt-1">${suggestion.country || 'France'}</div>
        `;
        
        div.setAttribute('data-lat', suggestion.lat);
        div.setAttribute('data-lon', suggestion.lon);
        div.setAttribute('data-display-name', suggestion.display_name);
        
        div.addEventListener('click', () => selectLocationFromData(div));
        
        container.appendChild(div);
    });

    // Force show the container
    container.classList.remove('hidden');
    container.style.display = 'block';
    container.style.zIndex = '999999';
    
    console.log('Suggestions container is now visible:', !container.classList.contains('hidden'));
}

function selectLocationFromData(element) {
    const lat = parseFloat(element.getAttribute('data-lat'));
    const lon = parseFloat(element.getAttribute('data-lon'));
    const displayName = element.getAttribute('data-display-name');
    
    console.log('Selecting location:', displayName, lat, lon);
    
    document.getElementById('selectedAddress').value = displayName;
    document.getElementById('selectedLatitude').value = lat.toFixed(6);
    document.getElementById('selectedLongitude').value = lon.toFixed(6);
    
    // Hide the suggestions dropdown
    hideSuggestions();
    
    // Update map
    if (urgentSaleMap) {
        urgentSaleMap.setView([lat, lon], 15);
        if (currentMarker) urgentSaleMap.removeLayer(currentMarker);
        currentMarker = L.marker([lat, lon]).addTo(urgentSaleMap);
    }
}

// Prevent form resubmission
document.getElementById('urgentSaleStep2Form').addEventListener('submit', function() {
    const submitBtn = document.getElementById('step2SubmitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>En cours...';
});
</script>
@endpush