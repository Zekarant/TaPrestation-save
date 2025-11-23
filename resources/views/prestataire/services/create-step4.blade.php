@extends('layouts.app')

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
      crossorigin="" />
@endpush

@section('content')
<div class="bg-blue-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="mb-4 sm:mb-6 lg:mb-8 text-center">
                <h1 class="text-xl sm:text-2xl lg:text-3xl xl:text-4xl font-extrabold text-blue-900 mb-1 sm:mb-2">Créer un nouveau service</h1>
                <p class="text-sm sm:text-base lg:text-lg text-blue-700">Étape 4 : Localisation</p>
            </div>

            <!-- Indicateur d'étapes -->
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <div class="flex items-center space-x-2 sm:space-x-3 lg:space-x-4">
                        <a href="{{ route('prestataire.services.create.step3') }}" class="text-blue-600 hover:text-blue-900 transition-colors duration-200">
                            <i class="fas fa-arrow-left text-base sm:text-lg lg:text-xl"></i>
                        </a>
                        <div>
                            <h2 class="text-base sm:text-lg lg:text-xl font-bold text-blue-900">Étape 4 sur 4</h2>
                            <p class="text-xs sm:text-sm lg:text-base text-blue-700 hidden sm:block">Localisation</p>
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
                            <div class="h-1 bg-green-600 rounded" style="width: 100%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                <i class="fas fa-check text-xs"></i>
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-600 hidden sm:inline">Prix & Catégorie</span>
                            <span class="ml-1 text-xs font-medium text-green-600 sm:hidden">Prix</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 rounded min-w-4">
                            <div class="h-1 bg-green-600 rounded" style="width: 100%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                <i class="fas fa-check text-xs"></i>
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-600 hidden sm:inline">Photos</span>
                            <span class="ml-1 text-xs font-medium text-green-600 sm:hidden">Photo</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 rounded min-w-4">
                            <div class="h-1 bg-blue-600 rounded" style="width: 100%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                4
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-blue-600 hidden sm:inline">Localisation</span>
                            <span class="ml-1 text-xs font-medium text-blue-600 sm:hidden">Lieu</span>
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

            <form method="POST" action="{{ route('prestataire.services.store') }}" id="step4Form">
                @csrf

                <!-- Localisation -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
                    <h2 class="text-base sm:text-lg lg:text-xl font-bold text-blue-900 mb-3 sm:mb-4 border-b border-blue-200 pb-2">
                        <i class="fas fa-map-marker-alt text-orange-600 mr-1 sm:mr-2 text-sm sm:text-base"></i>Localisation de votre service
                    </h2>
                    
                    <div class="mb-3 sm:mb-4">
                        <p class="text-xs sm:text-sm text-blue-700 mb-2">
                            <i class="fas fa-info-circle mr-1 text-xs sm:text-sm"></i>
                            Indiquez où vous proposez ce service. Cela aidera les clients à vous trouver plus facilement.
                        </p>
                        <div class="bg-orange-50 p-2 sm:p-3 rounded-lg">
                            <h4 class="font-semibold text-orange-800 mb-1 sm:mb-2 text-xs sm:text-sm">Conseils pour la localisation :</h4>
                            <ul class="text-xs sm:text-sm text-orange-700 space-y-0.5 sm:space-y-1">
                                <li>• Sélectionnez l'emplacement principal où vous exercez</li>
                                <li>• Vous pouvez proposer vos services dans un rayon autour de ce point</li>
                                <li>• Une localisation précise améliore votre visibilité</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="map-container">
                        <div id="serviceMap" class="h-40 sm:h-48 lg:h-64 rounded-lg border border-blue-300 shadow-inner"></div>
                        <div class="mt-2 sm:mt-3 relative">
                            <input type="text" id="selectedAddress" name="address" value="{{ old('address', session('service_creation.step4.address')) }}" class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('address') border-red-500 @enderror" placeholder="Saisissez l'adresse ou cliquez sur la carte pour sélectionner une localisation" autocomplete="off">
                            <!-- Dropdown suggestions -->
                            <div id="address-suggestions" class="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-lg shadow-xl mt-1 z-[99999] hidden max-h-60 overflow-y-auto" style="z-index: 99999 !important; position: absolute !important;">
                                <!-- Suggestions will be populated here -->
                            </div>
                            <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', session('service_creation.step4.latitude')) }}">
                            <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', session('service_creation.step4.longitude')) }}">
                            @error('address')
                                <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 mt-2 sm:mt-3">
                                <button type="button" id="getCurrentLocationBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-2 sm:py-2.5 rounded-md transition duration-200 text-xs sm:text-sm lg:text-base flex items-center justify-center">
                                    <i class="fas fa-location-arrow mr-1 sm:mr-2 text-xs sm:text-sm"></i><span class="hidden xs:inline">Ma position actuelle</span><span class="xs:hidden">Ma position</span>
                                </button>
                                <button type="button" id="clearLocationBtn" class="bg-gray-500 hover:bg-gray-600 text-white px-3 sm:px-4 py-2 sm:py-2.5 rounded-md transition duration-200 text-xs sm:text-sm lg:text-base flex items-center justify-center">
                                    <i class="fas fa-times mr-1 sm:mr-2 text-xs sm:text-sm"></i><span class="hidden xs:inline">Effacer la localisation</span><span class="xs:hidden">Effacer</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Récapitulatif -->
                <div class="bg-white rounded-xl shadow-lg border border-green-200 p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
                    <h2 class="text-base sm:text-lg lg:text-xl font-bold text-green-900 mb-3 sm:mb-4 border-b border-green-200 pb-2">
                        <i class="fas fa-check-circle text-green-600 mr-1 sm:mr-2 text-sm sm:text-base"></i>Récapitulatif de votre service
                    </h2>
                    
                    <div class="space-y-3 sm:space-y-4">
                        <!-- Titre -->
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start space-y-1 sm:space-y-0">
                            <span class="text-xs sm:text-sm font-medium text-gray-600">Titre :</span>
                            <p class="text-blue-900 font-semibold text-sm sm:text-base sm:text-right sm:max-w-xs" id="recap-title">{{ session('service_creation.step1.title', 'Non défini') }}</p>
                        </div>
                        
                        <!-- Prix -->
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start space-y-1 sm:space-y-0">
                            <span class="text-xs sm:text-sm font-medium text-gray-600">Prix :</span>
                            <p class="text-green-600 font-semibold text-sm sm:text-base sm:text-right" id="recap-price">
                                {{ session('service_creation.step2.price') ? session('service_creation.step2.price') . '€' : 'Non défini' }}
                                @if(session('service_creation.step2.price_type'))
                                    <span class="text-xs sm:text-sm text-gray-600">({{ session('service_creation.step2.price_type') }})</span>
                                @endif
                            </p>
                        </div>
                        
                        <!-- Réservable -->
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start space-y-1 sm:space-y-0">
                            <span class="text-xs sm:text-sm font-medium text-gray-600">Réservable :</span>
                            <p class="text-blue-900 font-semibold text-sm sm:text-base" id="recap-reservable">
                                {{ session('service_creation.step1.reservable') ? 'Oui' : 'Non' }}
                            </p>
                        </div>
                        
                        <!-- Délai de livraison -->
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start space-y-1 sm:space-y-0">
                            <span class="text-xs sm:text-sm font-medium text-gray-600">Délai de livraison :</span>
                            <p class="text-blue-900 font-semibold text-sm sm:text-base sm:text-right sm:max-w-xs" id="recap-delivery">
                                {{ session('service_creation.step1.delivery_time') ? session('service_creation.step1.delivery_time') . ' jours' : 'Non défini' }}
                            </p>
                        </div>
                        
                        <!-- Description -->
                        <div class="flex flex-col space-y-2 pt-3 sm:pt-4 border-t border-green-200">
                            <span class="text-xs sm:text-sm font-medium text-gray-600">Description :</span>
                            <p class="text-gray-800 text-xs sm:text-sm leading-relaxed" id="recap-description">{{ Str::limit(session('service_creation.step1.description', 'Non définie'), 150) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row justify-between items-center pt-6 sm:pt-8 border-t border-blue-200 space-y-3 sm:space-y-0">
                    <a href="{{ route('prestataire.services.create.step3') }}" class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-4 sm:px-6 py-3 rounded-lg transition duration-200 font-medium text-center text-sm sm:text-base">
                        <i class="fas fa-arrow-left mr-2"></i>Précédent
                    </a>
                    <button type="submit" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-6 sm:px-8 py-3 rounded-lg transition duration-200 font-bold shadow-lg hover:shadow-xl text-sm sm:text-base">
                        <i class="fas fa-check-circle mr-2"></i>Créer mon service
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
    const form = document.getElementById('step4Form');
    if (form) {
        form.addEventListener('submit', function() {
            // Disable the submit button to prevent double submission
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Création en cours...';
            }
        });
    }
    
    // Map Initialization
    let map = null;
    let marker = null;
    const defaultLat = 33.5731; // Casablanca
    const defaultLng = -7.5898;
    
    // Autocomplete variables
    let searchTimeout;
    let currentFocus = -1;
    const addressInput = document.getElementById('selectedAddress');
    const suggestionsContainer = document.getElementById('address-suggestions');

    function initializeMap() {
        const mapElement = document.getElementById('serviceMap');
        if (!mapElement) return;

        map = L.map('serviceMap').setView([defaultLat, defaultLng], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            updateMarker(lat, lng);
            reverseGeocode(lat, lng);
        });
        
        // Si des coordonnées existent déjà, les afficher
        const existingLat = document.getElementById('latitude').value;
        const existingLng = document.getElementById('longitude').value;
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
    }

    function updateMarker(lat, lng) {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng]).addTo(map);
        }
        // Make marker globally accessible
        window.serviceMapMarker = marker;
        window.serviceMap = map;
        
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
        
        // Update map if coordinates are available
        if (lat && lng && map) {
            updateMarker(parseFloat(lat), parseFloat(lng));
            map.setView([parseFloat(lat), parseFloat(lng)], 13);
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
                map.setView([lat, lng], 13);
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
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
        document.getElementById('latitude').value = '';
        document.getElementById('longitude').value = '';
        document.getElementById('selectedAddress').value = '';
        map.setView([defaultLat, defaultLng], 6);
    });

    initializeMap();
    initAutocomplete();
});

// Hide suggestions when clicking outside
document.addEventListener('click', function(e) {
    const addressInput = document.getElementById('selectedAddress');
    const suggestionsContainer = document.getElementById('address-suggestions');
    
    if (addressInput && suggestionsContainer && 
        !addressInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
        suggestionsContainer.classList.add('hidden');
    }
});
</script>
@endpush