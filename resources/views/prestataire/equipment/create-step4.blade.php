@extends('layouts.app')

@section('title', 'Ajouter un équipement - Étape 4')

@section('content')
<div class="bg-green-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-green-900 mb-2">Ajouter un équipement</h1>
                <p class="text-base sm:text-lg text-green-700">Étape 4 : Localisation et résumé</p>
            </div>

            <!-- Barre de progression -->
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 sm:mb-4 space-y-2 sm:space-y-0">
                    <h2 class="text-base sm:text-lg font-semibold text-green-900">Processus de création</h2>
                    <span class="text-xs sm:text-sm text-green-600">Étape 4 sur 4</span>
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
                            ✓
                        </div>
                        <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-900 hidden sm:inline">Tarifs et conditions</span>
                    </div>
                    <div class="flex-1 h-1 bg-green-600 rounded min-w-4"></div>
                    <div class="flex items-center flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-medium">
                            ✓
                        </div>
                        <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-900 hidden sm:inline">Photos</span>
                    </div>
                    <div class="flex-1 h-1 bg-green-600 rounded min-w-4"></div>
                    <div class="flex items-center flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-medium">
                            4
                        </div>
                        <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-900 hidden sm:inline">Localisation et résumé</span>
                    </div>
                </div>
                <!-- Labels mobiles -->
                <div class="flex justify-between mt-2 sm:hidden text-xs text-gray-600">
                    <span class="text-green-600 font-medium">Info</span>
                    <span class="text-green-600 font-medium">Tarifs</span>
                    <span class="text-green-600 font-medium">Photos</span>
                    <span class="text-green-600 font-medium">Résumé</span>
                </div>
            </div>

            <!-- Formulaire Étape 4 -->
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 space-y-3 sm:space-y-0">
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('prestataire.equipment.create.step3') }}" class="text-green-600 hover:text-green-900 transition-colors duration-200 p-1">
                            <i class="fas fa-arrow-left text-base sm:text-lg"></i>
                        </a>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-green-900">Localisation et résumé</h2>
                            <p class="text-xs sm:text-sm text-green-700">Définissez la localisation et vérifiez les informations</p>
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

                <form action="{{ route('prestataire.equipment.store') }}" method="POST">
                    @csrf
                    
                    <div class="space-y-4 sm:space-y-6 lg:space-y-8">
                        <!-- Localisation -->
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-green-900 mb-3 sm:mb-4 border-b border-green-200 pb-2">Localisation</h3>
                            <div class="mb-3 sm:mb-4">
                                <p class="text-xs sm:text-sm text-green-700 mb-2">
                                    <i class="fas fa-info-circle mr-1 text-xs sm:text-sm"></i>
                                    Indiquez où vous proposez cet équipement. Cela aidera les clients à vous trouver plus facilement.
                                </p>
                                <div class="bg-orange-50 p-2 sm:p-3 rounded-lg">
                                    <h4 class="font-semibold text-orange-800 mb-1 sm:mb-2 text-xs sm:text-sm">Conseils pour la localisation :</h4>
                                    <ul class="text-xs sm:text-sm text-orange-700 space-y-0.5 sm:space-y-1">
                                        <li>• Sélectionnez l'emplacement principal où vous proposez cet équipement</li>
                                        <li>• Vous pouvez proposer votre équipement dans un rayon autour de ce point</li>
                                        <li>• Une localisation précise améliore votre visibilité</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="map-container">
                                <div id="serviceMap" class="h-40 sm:h-48 lg:h-64 rounded-lg border border-green-300 shadow-inner"></div>
                                <div class="mt-2 sm:mt-3 relative">
                                    <input type="text" id="selectedAddress" name="address" value="{{ old('address') }}" class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('address') border-red-500 @enderror" placeholder="Saisissez l'adresse ou cliquez sur la carte pour sélectionner une localisation" autocomplete="off">
                                    <!-- Dropdown suggestions -->
                                    <div id="address-suggestions" class="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-lg shadow-xl mt-1 z-[99999] hidden max-h-60 overflow-y-auto" style="z-index: 99999 !important; position: absolute !important;">
                                        <!-- Suggestions will be populated here -->
                                    </div>
                                    <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
                                    <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">
                                    @error('address')
                                        <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 mt-2 sm:mt-3">
                                        <button type="button" id="getCurrentLocationBtn" class="bg-green-600 hover:bg-green-700 text-white px-3 sm:px-4 py-2 sm:py-2.5 rounded-md transition duration-200 text-xs sm:text-sm lg:text-base flex items-center justify-center">
                                            <i class="fas fa-location-arrow mr-1 sm:mr-2 text-xs sm:text-sm"></i><span class="hidden xs:inline">Ma position actuelle</span><span class="xs:hidden">Ma position</span>
                                        </button>
                                        <button type="button" id="clearLocationBtn" class="bg-gray-500 hover:bg-gray-600 text-white px-3 sm:px-4 py-2 sm:py-2.5 rounded-md transition duration-200 text-xs sm:text-sm lg:text-base flex items-center justify-center">
                                            <i class="fas fa-times mr-1 sm:mr-2 text-xs sm:text-sm"></i><span class="hidden xs:inline">Effacer la localisation</span><span class="xs:hidden">Effacer</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Résumé complet -->
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-green-900 mb-3 sm:mb-4 border-b border-green-200 pb-2">Résumé de votre équipement</h3>
                            
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                                <!-- Informations de base -->
                                <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
                                    <h4 class="font-semibold text-gray-900 mb-2 sm:mb-3 text-sm sm:text-base">Informations de base</h4>
                                    <div class="space-y-1 sm:space-y-2 text-xs sm:text-sm">
                                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1 sm:gap-0">
                                            <span class="text-gray-600 font-medium sm:font-normal">Nom :</span>
                                            <span class="font-medium text-gray-900 break-words">{{ session('equipment_creation.step1.name', 'Non défini') }}</span>
                                        </div>
                                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1 sm:gap-0">
                                            <span class="text-gray-600 font-medium sm:font-normal">Catégorie :</span>
                                            <span class="font-medium text-gray-900 break-words">{{ $categoryName ?? 'Non définie' }}</span>
                                        </div>
                                        @if($subcategoryName)
                                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1 sm:gap-0">
                                            <span class="text-gray-600 font-medium sm:font-normal">Sous-catégorie :</span>
                                            <span class="font-medium text-gray-900 break-words">{{ $subcategoryName }}</span>
                                        </div>
                                        @endif
                                        <div class="pt-1 sm:pt-2">
                                            <span class="text-gray-600 font-medium sm:font-normal">Description :</span>
                                            <p class="text-gray-900 text-xs mt-1 break-words">{{ Str::limit(session('equipment_creation.step1.description', 'Non définie'), 100) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tarifs et conditions -->
                                <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
                                    <h4 class="font-semibold text-gray-900 mb-2 sm:mb-3 text-sm sm:text-base">Tarifs et conditions</h4>
                                    <div class="space-y-1 sm:space-y-2 text-xs sm:text-sm">
                                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1 sm:gap-0">
                                            <span class="text-gray-600 font-medium sm:font-normal">Prix par jour :</span>
                                            <span class="font-medium text-green-600">{{ session('equipment_creation.step2.price_per_day', 'Non défini') }}€</span>
                                        </div>
                                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1 sm:gap-0">
                                            <span class="text-gray-600 font-medium sm:font-normal">Caution :</span>
                                            <span class="font-medium text-gray-900">{{ session('equipment_creation.step2.security_deposit', 'Non défini') }}€</span>
                                        </div>
                                        @if(session('equipment_creation.step2.price_per_hour'))
                                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1 sm:gap-0">
                                            <span class="text-gray-600 font-medium sm:font-normal">Prix par heure :</span>
                                            <span class="font-medium text-gray-900">{{ session('equipment_creation.step2.price_per_hour') }}€</span>
                                        </div>
                                        @endif
                                        <div class="flex flex-col sm:flex-row sm:justify-between gap-1 sm:gap-0">
                                            <span class="text-gray-600 font-medium sm:font-normal">État :</span>
                                            <span class="font-medium text-gray-900">
                                                @php
                                                    $conditions = [
                                                        'excellent' => 'Excellent',
                                                        'very_good' => 'Très bon',
                                                        'good' => 'Bon',
                                                        'fair' => 'Correct',
                                                        'poor' => 'Mauvais'
                                                    ];
                                                    $condition = session('equipment_creation.step2.condition');
                                                @endphp
                                                {{ $conditions[$condition] ?? 'Non défini' }}
                                            </span>
                                        </div>
                                        <div class="pt-1 sm:pt-2">
                                            <div class="flex flex-wrap gap-1 sm:gap-2">
                                                @if(session('equipment_creation.step2.delivery_included'))
                                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Livraison incluse</span>
                                                @endif
                                                @if(session('equipment_creation.step2.license_required'))
                                                    <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">Permis requis</span>
                                                @endif
                                                @if(session('equipment_creation.step2.is_available'))
                                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Disponible immédiatement</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Photos -->
                            @if(session('equipment_creation.step3.temp_image_paths') && count(session('equipment_creation.step3.temp_image_paths')) > 0)
                            <div class="mt-4 sm:mt-6">
                                <h4 class="font-semibold text-gray-900 mb-2 sm:mb-3 text-sm sm:text-base">Photos de l'équipement ({{ count(session('equipment_creation.step3.temp_image_paths')) }})</h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    @foreach(session('equipment_creation.step3.temp_image_paths') as $imagePath)
                                        <img src="{{ asset('storage/' . $imagePath) }}" alt="Photo de l'équipement" class="w-full h-24 sm:h-32 object-cover rounded-lg border-2 border-green-300">
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center pt-4 sm:pt-6 border-t border-green-200 gap-3 sm:gap-4 mt-6 sm:mt-8">
                        <a href="{{ route('prestataire.equipment.create.step3') }}" class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg transition duration-200 font-medium text-center text-sm sm:text-base">
                            <i class="fas fa-arrow-left mr-2"></i>Précédent
                        </a>
                        
                        <button type="submit" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-6 sm:px-8 py-2.5 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-sm sm:text-base">
                            <i class="fas fa-check mr-2"></i>
                            <span class="hidden sm:inline">Publier l'équipement</span>
                            <span class="sm:hidden">Publier</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
let map, marker;

// Map Initialization
function initMap() {
    const mapElement = document.getElementById('serviceMap');
    if (!mapElement) {
        console.error('Map element not found');
        return;
    }
    
    // Get initial coordinates from form fields or use defaults
    const lat = parseFloat(document.getElementById('latitude').value) || 33.5731; // Default to Casablanca
    const lon = parseFloat(document.getElementById('longitude').value) || -7.5898;

    // Initialize map
    map = L.map('serviceMap').setView([lat, lon], 13);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    // Create or update marker
    if (marker) {
        marker.setLatLng([lat, lon]);
    } else {
        marker = L.marker([lat, lon], { draggable: true }).addTo(map);
        
        // Attach drag event listener
        marker.on('dragend', function(e) {
            const latlng = marker.getLatLng();
            updateLatLng(latlng.lat, latlng.lng);
        });
    }

    // If we have coordinates, fetch the address
    if (document.getElementById('latitude').value && document.getElementById('longitude').value) {
        fetchAddress(lat, lon);
    }

    // Map click event
    map.on('click', function(e) {
        // Update marker position
        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng, { draggable: true }).addTo(map);
            marker.on('dragend', function(e) {
                const latlng = marker.getLatLng();
                updateLatLng(latlng.lat, latlng.lng);
            });
        }
        updateLatLng(e.latlng.lat, e.latlng.lng);
    });
}

function updateLatLng(lat, lng) {
    // Update hidden form fields with higher precision
    document.getElementById('latitude').value = lat.toFixed(6);
    document.getElementById('longitude').value = lng.toFixed(6);
    
    // Fetch address information
    fetchAddress(lat, lng);
}

function fetchAddress(lat, lng) {
    // Use Nominatim for reverse geocoding
    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&addressdetails=1`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data && data.display_name) {
                // Update the address field
                document.getElementById('selectedAddress').value = data.display_name;
            }
        })
        .catch(error => {
            console.error('Erreur lors de la récupération de l\'adresse:', error);
            // Fallback to coordinates if we can't get an address
            document.getElementById('selectedAddress').value = `Lat: ${lat.toFixed(6)}, Lon: ${lng.toFixed(6)}`;
        });
}

// Autocomplete functionality
document.addEventListener('DOMContentLoaded', function() {
    initMap();

    // Autocomplete variables
    let searchTimeout;
    let currentFocus = -1;
    const addressInput = document.getElementById('selectedAddress');
    const suggestionsContainer = document.getElementById('address-suggestions');

    // Initialize autocomplete functionality
    if (addressInput && suggestionsContainer) {
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
        
        // Close suggestions when clicking elsewhere
        document.addEventListener('click', function(e) {
            if (!addressInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                hideSuggestions();
                currentFocus = -1;
            }
        });
    }

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
            
            const highlightedText = suggestion.display_name.replace(
                new RegExp(`(${query})`, 'gi'),
                '<strong class="text-green-600">$1</strong>'
            );
            
            div.innerHTML = `
                <div class="font-medium text-gray-800">${highlightedText}</div>
                <div class="text-sm text-gray-600 mt-1">${suggestion.country || 'France'}</div>
            `;
            
            div.setAttribute('data-lat', suggestion.lat);
            div.setAttribute('data-lon', suggestion.lon);
            div.setAttribute('data-display-name', suggestion.display_name);
            
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
                '<strong class="text-green-600">$1</strong>'
            );
            
            div.innerHTML = `
                <div class="font-medium text-gray-800">${highlightedText}</div>
            `;
            
            div.setAttribute('data-lat', suggestion.lat);
            div.setAttribute('data-lon', suggestion.lon);
            div.setAttribute('data-display-name', suggestion.display_name);
            
            div.addEventListener('click', () => selectLocationFromData(div));
            
            suggestionsContainer.appendChild(div);
        });

        // Show the container
        suggestionsContainer.classList.remove('hidden');
        suggestionsContainer.style.display = 'block';
    }

    function selectLocationFromData(element) {
        const lat = parseFloat(element.getAttribute('data-lat'));
        const lon = parseFloat(element.getAttribute('data-lon'));
        const displayName = element.getAttribute('data-display-name');
        
        console.log('Selecting location:', displayName, lat, lon);
        
        document.getElementById('selectedAddress').value = displayName;
        document.getElementById('latitude').value = lat.toFixed(6);
        document.getElementById('longitude').value = lon.toFixed(6);
        
        // Hide the suggestions dropdown
        hideSuggestions();
        
        // Update map
        if (map) {
            map.setView([lat, lon], 15);
            if (marker) map.removeLayer(marker);
            marker = L.marker([lat, lon], { draggable: true }).addTo(map);
            
            marker.on('dragend', function(e) {
                const latlng = marker.getLatLng();
                updateLatLng(latlng.lat, latlng.lng);
            });
        }
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

    document.getElementById('getCurrentLocationBtn').addEventListener('click', function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                // Success callback
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    const newLatLng = new L.LatLng(lat, lng);
                    
                    // Update or create marker
                    if (marker) {
                        marker.setLatLng(newLatLng);
                    } else {
                        marker = L.marker(newLatLng, { draggable: true }).addTo(map);
                        // Reattach drag event listener
                        marker.on('dragend', function(e) {
                            const latlng = marker.getLatLng();
                            updateLatLng(latlng.lat, latlng.lng);
                        });
                    }
                    
                    // Center map on current location
                    map.setView(newLatLng, 15);
                    
                    // Update form fields
                    updateLatLng(lat, lng);
                    
                    // Reverse geocode to get address
                    fetchAddress(lat, lng);
                },
                // Error callback
                function(error) {
                    let errorMessage = 'Impossible de récupérer votre position.';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage = "L'utilisateur a refusé la demande de géolocalisation.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage = "Les informations de localisation ne sont pas disponibles.";
                            break;
                        case error.TIMEOUT:
                            errorMessage = "La demande de géolocalisation a expiré.";
                            break;
                        case error.UNKNOWN_ERROR:
                            errorMessage = "Une erreur inconnue est survenue.";
                            break;
                    }
                    // Display error in a more user-friendly way
                    alert(errorMessage);
                    console.error('Geolocation error:', error);
                }
            );
        } else {
            alert('La géolocalisation n\'est pas supportée par votre navigateur.');
        }
    });

    document.getElementById('clearLocationBtn').addEventListener('click', function() {
        const defaultLat = 33.5731; // Casablanca
        const defaultLon = -7.5898;
        const defaultLatLng = new L.LatLng(defaultLat, defaultLon);
        
        // Update or create marker
        if (marker) {
            marker.setLatLng(defaultLatLng);
        } else {
            marker = L.marker(defaultLatLng, { draggable: true }).addTo(map);
            // Reattach drag event listener
            marker.on('dragend', function(e) {
                const latlng = marker.getLatLng();
                updateLatLng(latlng.lat, latlng.lng);
            });
        }
        
        // Center map
        map.setView(defaultLatLng, 13);
        
        // Clear form fields
        document.getElementById('latitude').value = '';
        document.getElementById('longitude').value = '';
        document.getElementById('selectedAddress').value = '';
    });
    
    // Prevent form resubmission
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function() {
            // Disable the submit button to prevent double submission
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Publication en cours...';
            }
        });
    }
});
</script>
@endsection