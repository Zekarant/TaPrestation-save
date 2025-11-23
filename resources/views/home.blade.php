@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<section class="relative bg-white text-gray-900 overflow-hidden py-12 sm:py-16 md:py-24 lg:py-32">
    <!-- Background gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-gray-50 to-white pointer-events-none"></div>
    
    <!-- Unified layout for all screen sizes -->
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Grid layout for all screen sizes -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 lg:gap-16 items-center">
            <!-- Left side: Text content -->
            <div class="text-center lg:text-left">
                <!-- Logo above title for mobile -->
                <div class="mb-4 sm:mb-6 lg:hidden">
                    <div class="w-16 h-16 bg-blue-600 rounded-lg flex items-center justify-center mx-auto">
                        <i class="fas fa-handshake text-white text-xl"></i>
                    </div>
                </div>
                
                <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-bold mb-4 sm:mb-6 md:mb-8 relative inline-block">
                    Trouvez le <span class="text-blue-600 relative">prestataire
                        <span class="absolute bottom-0 left-0 w-full h-1 bg-blue-600 transform scale-x-0 hover:scale-x-100 transition-transform duration-300 origin-left"></span>
                    </span><br>
                    pour vos projets
                </h1>
                <p class="text-sm sm:text-base md:text-lg lg:text-xl mb-6 sm:mb-8 md:mb-10 text-gray-600 max-w-2xl mx-auto lg:mx-0">
                    Mise en relation sécurisée et efficace entre clients et prestataires de services professionnels
                </p>
                <!-- Buttons container - same line on desktop, stacked on mobile -->
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-center lg:justify-start mt-6 sm:mt-8">
                    <a href="{{ route('prestataires.index') }}" class="bg-green-600 hover:bg-green-500 text-white font-medium py-3 px-6 sm:py-4 sm:px-8 rounded-lg sm:rounded-xl transition-all duration-300 text-center focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 sm:w-auto whitespace-nowrap hero-button transform hover:scale-105 hover:shadow-lg">
                        Trouver les prestataires
                    </a>
                    <a href="{{ route('services.index') }}" class="border border-blue-600 text-blue-600 hover:bg-blue-50 hover:text-blue-700 font-medium py-3 px-6 sm:py-4 sm:px-8 rounded-lg sm:rounded-xl transition-all duration-300 text-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto whitespace-nowrap hero-button transform hover:scale-105 hover:shadow-lg">
                        Voir les services
                    </a>
                </div>
            </div>
            
            <!-- Right side: Illustration/Logo - now only visible on desktop -->
            <div class="relative flex justify-center items-center mt-8 lg:mt-0 hidden lg:block">
                <!-- Logo container with enhanced styling -->
                <div class="bg-gradient-to-br from-blue-50 to-white rounded-3xl p-8 md:p-10 w-full max-w-md lg:max-w-2xl xl:max-w-3xl flex items-center justify-center aspect-square relative">
                    <!-- Halo effect behind the logo -->
                    <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-blue-200/20 to-transparent blur-xl"></div>
                    
                    <div class="text-center relative z-10">
                        <div class="w-32 h-32 sm:w-48 sm:h-48 md:w-64 md:h-64 lg:w-80 lg:h-80 mx-auto flex items-center justify-center mb-4 sm:mb-6 md:mb-8">
                            <!-- Circular background with gradient -->
                            <div class="absolute w-36 h-36 sm:w-52 sm:h-52 md:w-72 md:h-72 lg:w-96 lg:h-96 rounded-full bg-gradient-to-br from-blue-100 to-blue-50 opacity-80"></div>
                            
                            <!-- Main logo with floating effect -->
                            <div class="relative w-28 h-28 sm:w-40 sm:h-40 md:w-56 md:h-56 lg:w-72 lg:h-72 bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl sm:rounded-3xl flex items-center justify-center shadow-xl transform hover:scale-105 transition-transform duration-300 hero-decoration">
                                <i class="fas fa-handshake text-white text-4xl sm:text-6xl md:text-7xl lg:text-8xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section Raccourcis visuels -->
<section class="py-10 sm:py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8 sm:mb-12">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-3 sm:mb-4">
                Que recherchez-vous ?
            </h2>
            <p class="text-base sm:text-lg text-gray-600 max-w-3xl mx-auto">
                Découvrez nos trois univers pour répondre à tous vos besoins
            </p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 mb-10 sm:mb-16">
            <!-- Services -->
            <a href="{{ route('services.index') }}" class="group block">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 sm:p-8 text-center hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 border border-blue-200">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-briefcase text-2xl sm:text-3xl text-white"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3 sm:mb-4 group-hover:text-blue-600 transition-colors">
                         Besoin d'un service ?
                    </h3>
                    <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6">
                        Trouvez le prestataire parfait pour vos projets professionnels
                    </p>
                    <div class="inline-flex items-center text-blue-600 font-semibold group-hover:text-blue-700 text-sm sm:text-base">
                        Voir les prestations disponibles
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>
            
            <!-- Matériel à louer -->
            <a href="{{ route('equipment.index') }}" class="group block">
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 sm:p-8 text-center hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 border border-green-200">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-tools text-2xl sm:text-3xl text-white"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3 sm:mb-4 group-hover:text-green-600 transition-colors">
                         Louer un outil ?
                    </h3>
                    <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6">
                        Accédez à du matériel professionnel de qualité
                    </p>
                    <div class="inline-flex items-center text-green-600 font-semibold group-hover:text-green-700 text-sm sm:text-base">
                        Parcourir les matériels à louer
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>
            
            <!-- Vente urgente -->
            <a href="{{ route('urgent-sales.index') }}" class="group block">
                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-6 sm:p-8 text-center hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 border border-red-200">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-red-600 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-bolt text-2xl sm:text-3xl text-white"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3 sm:mb-4 group-hover:text-red-600 transition-colors">
                         Trouver une bonne affaire ?
                    </h3>
                    <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6">
                        Découvrez des produits et outils en vente rapide
                    </p>
                    <div class="inline-flex items-center text-red-600 font-semibold group-hover:text-red-700 text-sm sm:text-base">
                        Découvrir les ventes urgentes
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Section Pourquoi choisir TaPrestation -->
<section class="py-10 sm:py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8 sm:mb-12">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-3 sm:mb-4">
                Pourquoi choisir TaPrestation ?
            </h2>
            <p class="text-base sm:text-lg text-gray-600 max-w-3xl mx-auto">
                Une plateforme de confiance pour tous vos besoins en services professionnels
            </p>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 md:gap-8">
            <div class="text-center p-4 sm:p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                <div class="w-12 h-12 sm:w-16 sm:h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                    <i class="fas fa-shield-alt text-xl sm:text-2xl text-blue-600" aria-hidden="true"></i>
                </div>
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">Sécurité garantie</h3>
                <p class="text-xs sm:text-sm text-gray-600">Tous nos prestataires sont vérifiés et leurs identités confirmées</p>
            </div>
            
            <div class="text-center p-4 sm:p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                <div class="w-12 h-12 sm:w-16 sm:h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                    <i class="fas fa-calendar-check text-xl sm:text-2xl text-green-600" aria-hidden="true"></i>
                </div>
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">Réservation en ligne</h3>
                <p class="text-xs sm:text-sm text-gray-600">Planifiez et réservez vos services directement en ligne</p>
            </div>
            
            <div class="text-center p-4 sm:p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                <div class="w-12 h-12 sm:w-16 sm:h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                    <i class="fas fa-comments text-xl sm:text-2xl text-purple-600" aria-hidden="true"></i>
                </div>
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">Messagerie intégrée</h3>
                <p class="text-xs sm:text-sm text-gray-600">Communiquez facilement avec vos prestataires</p>
            </div>
            
            <div class="text-center p-4 sm:p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                <div class="w-12 h-12 sm:w-16 sm:h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                    <i class="fas fa-star text-xl sm:text-2xl text-yellow-600" aria-hidden="true"></i>
                </div>
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">Avis vérifiés</h3>
                <p class="text-xs sm:text-sm text-gray-600">Consultez les avis authentiques de nos clients</p>
            </div>
            
            <div class="text-center p-4 sm:p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                <div class="w-12 h-12 sm:w-16 sm:h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                    <i class="fas fa-headset text-xl sm:text-2xl text-red-600" aria-hidden="true"></i>
                </div>
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">Support 7j/7</h3>
                <p class="text-xs sm:text-sm text-gray-600">Notre équipe vous accompagne à chaque étape</p>
            </div>
            
            <div class="text-center p-4 sm:p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                <div class="w-12 h-12 sm:w-16 sm:h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                    <i class="fas fa-bolt text-xl sm:text-2xl text-indigo-600" aria-hidden="true"></i>
                </div>
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">Réponse rapide</h3>
                <p class="text-xs sm:text-sm text-gray-600">Recevez des devis en moins de 24h</p>
            </div>
        </div>
    </div>
</section>

<!-- Section Prestataires en vedette -->
<section class="py-10 sm:py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8 sm:mb-12">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-3 sm:mb-4">
                Nos prestataires à la une
            </h2>
            <p class="text-base sm:text-lg text-gray-600">
                Découvrez des professionnels talentueux et vérifiés
            </p>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            @foreach($featuredPrestataires as $prestataire)
            <div class="bg-white rounded-xl shadow-lg p-5 sm:p-6 text-center transform hover:-translate-y-2 transition-transform duration-300">
                <div class="mb-4">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full mx-auto bg-gray-200 flex items-center justify-center overflow-hidden relative">
                        @if($prestataire->photo)
                            <img src="{{ asset('storage/' . $prestataire->photo) }}" alt="{{ $prestataire->user->name }}" class="w-full h-full object-cover">
                        @elseif($prestataire->user->avatar)
                            <img src="{{ asset('storage/' . $prestataire->user->avatar) }}" alt="{{ $prestataire->user->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-2xl sm:text-3xl font-bold text-gray-600">{{ strtoupper(substr($prestataire->user->name, 0, 1)) }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center justify-center gap-1 sm:gap-2 mb-1">
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900">{{ $prestataire->user->name }}</h3>
                    @if($prestataire->isVerified())
                        <span class="inline-flex items-center px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 mr-0.5 sm:mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Vérifié
                        </span>
                    @endif
                </div>
                <p class="text-xs sm:text-sm text-gray-500 mb-3 sm:mb-4">{{ $prestataire->speciality ?? 'Spécialité non définie' }}</p>
                <a href="{{ route('prestataires.show', $prestataire) }}" class="inline-block bg-blue-600 text-white font-semibold px-4 py-2 sm:px-6 sm:py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm sm:text-base">
                    Voir le profil
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-gradient-to-br from-gray-900 via-gray-800 to-blue-900 text-white">
    <!-- Section principale -->
    <div class="py-10 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 sm:gap-8">
                <!-- À propos -->
                <div class="lg:col-span-2">
                    <div class="flex items-center space-x-2 sm:space-x-3 mb-4 sm:mb-6">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-handshake text-white text-base sm:text-lg"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold">TaPrestation</h3>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-3300 mb-4 sm:mb-6 leading-relaxed">
                        La plateforme de référence pour connecter clients et prestataires de services professionnels. 
                        Trouvez facilement le professionnel qu'il vous faut ou développez votre activité.
                    </p>
                    <div class="flex space-x-2 sm:space-x-4">
                        <a href="#" class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-600 hover:bg-blue-700 rounded-lg flex items-center justify-center transition-all duration-200 hover:scale-110" aria-label="Facebook">
                            <i class="fab fa-facebook-f text-white text-sm sm:text-base"></i>
                        </a>
                        <a href="#" class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-400 hover:bg-blue-500 rounded-lg flex items-center justify-center transition-all duration-200 hover:scale-110" aria-label="Twitter">
                            <i class="fab fa-twitter text-white text-sm sm:text-base"></i>
                        </a>
                        <a href="#" class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-700 hover:bg-blue-800 rounded-lg flex items-center justify-center transition-all duration-200 hover:scale-110" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in text-white text-sm sm:text-base"></i>
                        </a>
                        <a href="#" class="w-8 h-8 sm:w-10 sm:h-10 bg-pink-600 hover:bg-pink-700 rounded-lg flex items-center justify-center transition-all duration-200 hover:scale-110" aria-label="Instagram">
                            <i class="fab fa-instagram text-white text-sm sm:text-base"></i>
                        </a>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- Section copyright -->
    <div class="border-t border-gray-700/50 bg-gray-900/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-3 sm:space-y-4 md:space-y-0">
                <div class="flex items-center space-x-1 sm:space-x-2 text-xs sm:text-sm text-gray-400">
                    <i class="fas fa-copyright"></i>
                    <span>{{ date('Y') }} TaPrestation. Tous droits réservés.</span>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-6 text-xs sm:text-sm text-gray-400">
                    <span class="flex items-center">
                        <i class="fas fa-map-marker-alt mr-1 sm:mr-2 text-blue-400"></i>
                        France
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-phone mr-1 sm:mr-2 text-green-400"></i>
                        Support 24/7
                    </span>
                    
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Modal pour les photos -->
<div id="photoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 hidden">
    <div class="max-w-4xl max-h-full p-2 sm:p-4">
        <button onclick="closePhotoModal()" class="absolute top-2 sm:top-4 right-2 sm:right-4 text-white hover:text-gray-300 focus:outline-none">
            <i class="fas fa-times text-xl sm:text-2xl"></i>
        </button>
        <img id="modalImage" src="" alt="Photo agrandie" class="max-w-full max-h-[80vh] object-contain">
        <div id="modalCaption" class="text-white text-center mt-2 sm:mt-4 text-sm sm:text-base"></div>
    </div>
</div>

<script>
// Modal pour les photos
function openPhotoModal(imageSrc, caption) {
    const modal = document.getElementById('photoModal');
    const modalImage = document.getElementById('modalImage');
    const modalCaption = document.getElementById('modalCaption');
    
    modalImage.src = imageSrc;
    modalCaption.textContent = caption;
    modal.classList.remove('hidden');
    
    // Fermer avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePhotoModal();
        }
    });
}

function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    modal.classList.add('hidden');
}

// Fermer le modal en cliquant à l'extérieur
document.getElementById('photoModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePhotoModal();
    }
});
// Autocomplete functionality for location input
const locationInput = document.getElementById('search-location');
const suggestionsContainer = document.getElementById('location-suggestions');
let currentFocus = -1;
let searchTimeout;

if (locationInput) {
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
}

// Hide suggestions when clicking outside
document.addEventListener('click', function(e) {
    if (locationInput && suggestionsContainer && 
        !locationInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
        hideSuggestions();
    }
});

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
            <div class="suggestion-item px-3 sm:px-4 py-2 sm:py-3 ${bgHover} cursor-pointer border-b border-gray-100 last:border-b-0" 
                 data-suggestion='${JSON.stringify(suggestion).replace(/'/g, "&apos;")}' 
                 onclick="selectLocationFromData(this)">
                <div class="flex items-center">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4 ${iconColor} mr-2 sm:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs sm:text-sm text-gray-900 truncate">${highlightedText}</div>
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
        <div class="px-3 sm:px-4 py-2 sm:py-3 text-gray-500 text-xs sm:text-sm">
            <div class="flex items-center">
                <svg class="w-3 h-3 sm:w-4 sm:h-4 text-gray-400 mr-2 sm:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

function selectLocation(suggestion) {
    // Handle both old format (string) and new format (object)
    let locationText;
    
    if (typeof suggestion === 'string') {
        locationText = suggestion;
    } else if (suggestion && suggestion.text) {
        locationText = suggestion.text;
    } else if (suggestion && suggestion.city) {
        locationText = suggestion.city;
        if (suggestion.postal_code) {
            locationText += ' (' + suggestion.postal_code + ')';
        }
        if (suggestion.country && suggestion.country !== 'France') {
            locationText += ', ' + suggestion.country;
        }
    } else {
        locationText = 'Localisation sélectionnée';
    }
    
    locationInput.value = locationText;
    hideSuggestions();
    currentFocus = -1;
    
    // Optional: Focus on the search query input after selection
    const searchQueryInput = document.getElementById('search-query');
    if (searchQueryInput) {
        searchQueryInput.focus();
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

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

@endsection