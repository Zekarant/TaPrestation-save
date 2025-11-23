@extends('layouts.app')

@section('title', 'Annonces - TaPrestation')

@section('content')
<div class="bg-red-50">
    <!-- Bannière d'en-tête -->
    <div class="max-w-8xl mx-auto px-3 sm:px-4 lg:px-6 py-2 sm:py-3">
        <div class="max-w-8xl mx-auto">
            <div class="mb-2 sm:mb-3 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-red-900 mb-1 leading-tight">
                    Annonces
                </h1>
                <p class="text-base sm:text-lg text-red-700 max-w-3xl mx-auto">
                    Saisissez les meilleures affaires avant qu'il ne soit trop tard.
                </p>
            </div>
        </div>
    </div>

    <div class="max-w-8xl mx-auto px-3 sm:px-4 lg:px-6 py-1 sm:py-2">
        <!-- Section des filtres -->
        <div class="bg-white rounded-xl shadow-lg border border-red-200 p-3 sm:p-4 mb-4">
            <div class="mb-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-xl sm:text-2xl font-bold text-red-800 mb-0.5">Filtres de recherche</h3>
                    <p class="text-sm text-red-700">Affinez votre recherche pour trouver les meilleures annonces</p>
                </div>
                <button type="button" id="toggleFilters" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1.5 px-3 sm:py-2 sm:px-4 rounded-lg transition duration-200 shadow hover:shadow-md flex items-center justify-center text-sm">
                    <span id="filterButtonText">Afficher les filtres</span>
                    <i class="fas fa-chevron-down ml-1.5" id="filterChevron"></i>
                </button>
            </div>
            
            <form method="GET" action="{{ route('urgent-sales.index') }}" class="space-y-4" id="filtersForm" style="display: none;">
                <!-- Conserver les paramètres de recherche principaux -->
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                
                <!-- Première ligne de filtres -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
                    <!-- Mot-clé -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Mot-clé</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ordinateur portable, etc." class="w-full pl-8 pr-3 py-1.5 sm:py-2 text-sm rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                        </div>
                    </div>
                    
                    <!-- Localisation -->
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">Localisation</label>
                        <div class="flex gap-1">
                            <div class="relative flex-1">
                                <i class="fas fa-map-marker-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                                <input type="text" name="city" id="city" value="{{ request('city') }}" placeholder="Ville ou code postal" class="w-full pl-8 pr-3 py-1.5 sm:py-2 text-sm rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                <input type="hidden" name="latitude" id="latitude" value="{{ request('latitude') }}">
                                <input type="hidden" name="longitude" id="longitude" value="{{ request('longitude') }}">
                                <!-- Dropdown suggestions -->
                                <div id="location-suggestions" class="absolute top-full left-0 right-0 bg-white border border-red-200 rounded-lg shadow-lg mt-1 z-[99999] hidden max-h-60 overflow-y-auto" style="z-index: 99999 !important; position: absolute !important;">
                                    <!-- Suggestions will be populated here -->
                                </div>
                            </div>
                            <button type="button" id="getLocationBtn" onclick="getMyLocation()" class="px-2 py-1.5 sm:py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition duration-200 flex items-center justify-center min-w-[50px] text-xs" title="Utiliser ma position">
                                <i class="fas fa-crosshairs mr-1"></i>
                                <span class="hidden sm:inline">GPS</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- État -->
                    <div>
                        <label for="condition" class="block text-sm font-medium text-gray-700 mb-1">État</label>
                        <div class="relative">
                            <i class="fas fa-cog absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                            <select name="condition" id="condition" class="w-full pl-8 pr-3 py-1.5 sm:py-2 text-sm rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                <option value="">Tous les états</option>
                                @foreach($conditions as $value => $label)
                                    <option value="{{ $value }}" {{ request('condition') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Prix maximum -->
                    <div>
                        <label for="price_max" class="block text-sm font-medium text-gray-700 mb-1">Prix maximum</label>
                        <div class="relative">
                            <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="number" name="price_max" id="price_max" value="{{ request('price_max') }}" placeholder="Prix max" min="0" class="w-full pl-8 pr-3 py-1.5 sm:py-2 text-sm rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                        </div>
                    </div>
                </div>
                
                <!-- Deuxième ligne de filtres -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3">
                    <!-- Tri par -->
                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">Trier par</label>
                        <div class="relative">
                            <i class="fas fa-sort absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select name="sort" id="sort" onchange="handleSortChange()" class="w-full pl-10 pr-4 py-1.5 sm:py-2 text-sm rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                <option value="">Pertinence</option>
                                <option value="recent" {{ request('sort') == 'recent' ? 'selected' : '' }}>Plus récent</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                                <option value="urgent" {{ request('sort') == 'urgent' ? 'selected' : '' }}>Urgence</option>
                                <option value="distance" {{ request('sort') == 'distance' ? 'selected' : '' }}>Distance</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Rayon de recherche -->
                    <div>
                        <label for="radius" class="block text-sm font-medium text-gray-700 mb-1">Rayon (km)</label>
                        <div class="relative">
                            <i class="fas fa-circle-notch absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <select name="radius" id="radius" onchange="handleRadiusChange()" class="w-full pl-10 pr-4 py-1.5 sm:py-2 text-sm rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                <option value="" {{ request('radius') == '' ? 'selected' : '' }}>Tous</option>
                                <option value="5" {{ request('radius') == '5' ? 'selected' : '' }}>5 km</option>
                                <option value="10" {{ request('radius') == '10' ? 'selected' : '' }}>10 km</option>
                                <option value="25" {{ request('radius') == '25' ? 'selected' : '' }}>25 km</option>
                                <option value="50" {{ request('radius') == '50' ? 'selected' : '' }}>50 km</option>
                                <option value="100" {{ request('radius') == '100' ? 'selected' : '' }}>100 km</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Avec livraison -->
                    <div class="flex items-center sm:col-span-2 md:col-span-2">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="with_delivery" value="1" {{ request('with_delivery') ? 'checked' : '' }} class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Avec livraison</span>
                        </label>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row gap-2 pt-3 border-t border-red-200">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-1.5 sm:py-2 px-3 sm:px-4 rounded-lg transition duration-200 shadow hover:shadow-md flex items-center justify-center text-sm">
                        Appliquer les filtres
                    </button>
                    
                    <button type="button" onclick="clearFilters()" class="flex-1 bg-red-100 hover:bg-red-200 text-red-800 font-bold py-1.5 sm:py-2 px-3 sm:px-4 rounded-lg transition duration-200 flex items-center justify-center text-sm">
                        Effacer tout
                    </button>
                    
                    @if(request()->anyFilled(['search', 'city', 'condition', 'price_max', 'sort', 'urgent_only', 'with_delivery']))
                        <a href="{{ route('urgent-sales.index') }}" class="bg-white hover:bg-gray-50 text-red-600 border border-red-200 font-bold py-1.5 sm:py-2 px-3 sm:px-4 rounded-lg transition duration-200 flex items-center justify-center text-sm">
                            Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
            
            <!-- Affichage des résultats -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pt-3 border-t border-red-200 mt-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs sm:text-sm font-semibold text-red-800">Résultats :</span>
                    <span class="px-2 sm:px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs sm:text-sm font-bold">
                        {{ $urgentSales->total() }} vente(s)
                    </span>
                </div>
                @if($urgentSales->total() > 0)
                    <div class="text-xs sm:text-sm font-semibold text-red-700">
                        {{ $urgentSales->pluck('prestataire_id')->unique()->count() }} prestataires actifs
                    </div>
                @endif
            </div>
        </div>

        <!-- Résultats -->
        <div>

                <!-- Liste des ventes -->
                @if($urgentSales->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-6">
                        @foreach($urgentSales as $sale)
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col" data-lat="{{ $sale->prestataire->latitude }}" data-lon="{{ $sale->prestataire->longitude }}">
                                <a href="{{ route('urgent-sales.show', $sale) }}" class="flex flex-col flex-grow">
                                    <!-- Image -->
                                    <div class="relative">
                                        @if($sale->photos && count($sale->photos ?? []) > 0)
                                            <img src="{{ filter_var($sale->photos[0], FILTER_VALIDATE_URL) ? $sale->photos[0] : Storage::url($sale->photos[0]) }}" 
                                                 alt="{{ $sale->title }}" 
                                                 class="w-full h-48 object-cover"
                                                 onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDQwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0xNzUgMTI1SDE4NVYxMzVIMTc1VjEyNVoiIGZpbGw9IiM5Q0EzQUYiLz4KPHA+dGggZD0iTTE2NSAxNDVIMjM1VjE1NUgxNjVWMTQ1WiIgZmlsbD0iIzlDQTNBRiIvPgo8cGF0aCBkPSJNMTg1IDEwNUMxOTEuNjI3IDEwNSAxOTcgMTEwLjM3MyAxOTcgMTE3QzE5NyAxMjMuNjI3IDE5MS42MjcgMTI5IDE4NSAxMjlDMTc4LjM3MyAxMjkgMTczIDEyMy42MjcgMTczIDExN0MxNzMgMTEwLjM3MyAxNzguMzczIDEwNSAxODUgMTA1WiIgZmlsbD0iIzlDQTNBRiIvPgo8L3N2Zz4K'; this.classList.add('opacity-50');">
                                        @else
                                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                                <div class="text-center">
                                                    <i class="fas fa-image text-gray-400 text-3xl mb-2"></i>
                                                    <p class="text-gray-500 text-sm">Aucune image</p>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- Badge urgent -->
                                        <div class="absolute top-2 left-2">
                                            <span class="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                                                <i class="fas fa-bolt mr-1"></i>URGENT
                                            </span>
                                        </div>
                                        
                                        <!-- Nombre de photos -->
                                        @if($sale->photos && count($sale->photos ?? []) > 1)
                                            <div class="absolute top-2 right-2 bg-black/70 text-white px-2 py-1 rounded-full text-xs font-medium backdrop-blur-sm">
                                                <i class="fas fa-images mr-1"></i>{{ count($sale->photos ?? []) }}
                                            </div>
                                        @endif
                                        
                                        <!-- Indicateur de qualité d'image -->
                                        @if($sale->photos && count($sale->photos ?? []) > 0)
                                            <div class="absolute bottom-2 left-2 bg-green-500/80 text-white px-2 py-1 rounded text-xs font-medium backdrop-blur-sm">
                                                <i class="fas fa-check mr-1"></i>Avec photos
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Contenu -->
                                    <div class="p-4 sm:p-5 flex-grow">
                                        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 text-base sm:text-lg">{{ $sale->title }}</h3>
                                        <p class="text-gray-600 text-xs sm:text-sm mb-3 sm:mb-4 line-clamp-2">{{ $sale->description }}</p>
                                        
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="text-xl sm:text-2xl font-bold text-red-600">{{ number_format($sale->price, 2) }}€</div>
                                            @if($sale->quantity > 1)
                                                <div class="text-xs sm:text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-md">Qté: {{ $sale->quantity }}</div>
                                            @endif
                                        </div>
                                        
                                        <!-- Localisation -->
                        @if($sale->location)
                            <div class="flex items-center text-xs sm:text-sm text-gray-500 mb-3">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                <span>{{ $sale->location }}</span>
                            </div>
                        @endif
                                    </div>
                                    
                                    <!-- Profile and Contact Button - Fixed at bottom -->
                                    <div class="sticky bottom-0 bg-white border-t border-gray-100 p-4 sm:p-5 mt-auto">
                                        <div class="flex items-center text-xs sm:text-sm text-gray-600 mb-3">
                                            <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full mr-2 flex items-center justify-center overflow-hidden">
                                                @if($sale->prestataire->photo)
                                                    <img src="{{ Storage::url($sale->prestataire->photo) }}" alt="{{ $sale->prestataire->user->name }}" class="w-6 h-6 sm:w-8 sm:h-8 rounded-full object-cover">
                                                @elseif($sale->prestataire->user->avatar)
                                                    <img src="{{ Storage::url($sale->prestataire->user->avatar) }}" alt="{{ $sale->prestataire->user->name }}" class="w-6 h-6 sm:w-8 sm:h-8 rounded-full object-cover">
                                                @elseif($sale->prestataire->user->profile_photo_path)
                                                    <img src="{{ asset('storage/' . $sale->prestataire->user->profile_photo_path) }}" alt="{{ $sale->prestataire->user->name }}" class="w-6 h-6 sm:w-8 sm:h-8 rounded-full object-cover">
                                                @else
                                                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                                        <span class="text-xs font-medium">{{ substr($sale->prestataire->user->name, 0, 1) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <span class="truncate">{{ $sale->prestataire->user->name }}</span>
                                        </div>
                                        <button class="w-full px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 text-sm font-medium">
                                            <i class="fas fa-envelope mr-2"></i>Contacter
                                        </button>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-6 md:mt-8">
                        <div class="flex justify-center">
                            <div class="w-full max-w-md md:max-w-none">
                                {{ $urgentSales->links() }}
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Message d'état vide -->
                    <div class="text-center py-8 md:py-12">
                        <div class="max-w-md mx-auto px-4">
                            <div class="w-20 h-20 md:w-24 md:h-24 mx-auto mb-4 md:mb-6 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="w-10 h-10 md:w-12 md:h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg md:text-xl font-semibold text-gray-900 mb-2">Aucune vente urgente trouvée</h3>
                            <p class="text-sm md:text-base text-gray-600 mb-4 md:mb-6">Nous n'avons trouvé aucune vente urgente correspondant à vos critères de recherche. Essayez de modifier vos filtres ou explorez toutes nos ventes.</p>
                            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                @if(request()->anyFilled(['search', 'city', 'condition']))
                                    <a href="{{ route('urgent-sales.index') }}" class="inline-flex items-center px-4 md:px-6 py-2 md:py-3 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors duration-200 text-sm md:text-base">
                                        <svg class="w-4 h-4 md:w-5 md:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Réinitialiser les filtres
                                    </a>
                                @else
                                    <a href="{{ route('urgent-sales.index') }}" class="inline-flex items-center px-4 md:px-6 py-2 md:py-3 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors duration-200 text-sm md:text-base">
                                        <svg class="w-4 h-4 md:w-5 md:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                        </svg>
                                        Voir toutes les ventes
                                    </a>
                                @endif
                                <a href="{{ route('home') }}" class="inline-flex items-center px-4 md:px-6 py-2 md:py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors duration-200 text-sm md:text-base">
                                    <svg class="w-4 h-4 md:w-5 md:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    Retour à l'accueil
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Fonction pour basculer l'affichage des filtres
function toggleFilters() {
    const filtersForm = document.getElementById('filtersForm');
    const filterButtonText = document.getElementById('filterButtonText');
    const filterChevron = document.getElementById('filterChevron');
    
    if (filtersForm.style.display === 'none' || filtersForm.style.display === '') {
        filtersForm.style.display = 'block';
        filterButtonText.textContent = 'Masquer les filtres';
        filterChevron.classList.remove('fa-chevron-down');
        filterChevron.classList.add('fa-chevron-up');
    } else {
        filtersForm.style.display = 'none';
        filterButtonText.textContent = 'Afficher les filtres';
        filterChevron.classList.remove('fa-chevron-up');
        filterChevron.classList.add('fa-chevron-down');
    }
}

// Fonction pour effacer tous les filtres
function clearFilters() {
    const form = document.getElementById('filtersForm');
    const inputs = form.querySelectorAll('input[type="text"], input[type="number"], select');
    const checkboxes = form.querySelectorAll('input[type="checkbox"]');
    
    inputs.forEach(input => {
        input.value = '';
    });
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Rediriger vers la page sans paramètres
    window.location.href = '{{ route("urgent-sales.index") }}';
}

// Fonction pour calculer la distance entre deux points (formule de Haversine)
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Rayon de la Terre en kilomètres
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

// Fonction pour afficher les distances
function displayDistances() {
    const userLat = parseFloat(document.getElementById('latitude').value);
    const userLon = parseFloat(document.getElementById('longitude').value);
    
    if (!userLat || !userLon) return;
    
    document.querySelectorAll('[data-lat][data-lon]').forEach(element => {
        const lat = parseFloat(element.getAttribute('data-lat'));
        const lon = parseFloat(element.getAttribute('data-lon'));
        
        if (lat && lon) {
            const distance = calculateDistance(userLat, userLon, lat, lon);
            const distanceText = distance < 1 ? 
                Math.round(distance * 1000) + ' m' : 
                distance.toFixed(1) + ' km';
            
            // Créer ou mettre à jour le badge de distance
            let distanceBadge = element.querySelector('.distance-badge');
            if (!distanceBadge) {
                distanceBadge = document.createElement('span');
                distanceBadge.className = 'distance-badge inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-2';
                element.appendChild(distanceBadge);
            }
            distanceBadge.innerHTML = `<i class="fas fa-map-marker-alt mr-1"></i>${distanceText}`;
        }
    });
}

// Fonction pour obtenir la position de l'utilisateur
function getMyLocation() {
    const btn = document.getElementById('getLocationBtn');
    const originalContent = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1 sm:mr-2"></i><span class="hidden sm:inline">Localisation...</span><span class="sm:hidden">GPS</span>';
    btn.disabled = true;
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lon;
                
                // Géocodage inverse pour obtenir le nom de la ville
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=10&addressdetails=1`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.address) {
                            const city = data.address.city || data.address.town || data.address.village || data.address.municipality || '';
                            const postcode = data.address.postcode || '';
                            const displayName = city + (postcode ? ` (${postcode})` : '');
                            document.getElementById('city').value = displayName;
                        }
                        
                        btn.innerHTML = originalContent;
                        btn.disabled = false;
                        
                        // Afficher les distances
                        displayDistances();
                        
                        // Soumettre automatiquement le formulaire si un rayon est sélectionné
                        const radius = document.getElementById('radius').value;
                        if (radius) {
                            document.getElementById('filtersForm').submit();
                        }
                    })
                    .catch(error => {
                        console.error('Erreur de géocodage:', error);
                        btn.innerHTML = originalContent;
                        btn.disabled = false;
                        displayDistances();
                    });
            },
            function(error) {
                console.error('Erreur de géolocalisation:', error);
                btn.innerHTML = originalContent;
                btn.disabled = false;
                alert('Impossible d\'obtenir votre position. Veuillez vérifier les autorisations de géolocalisation.');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000
            }
        );
    } else {
        alert('La géolocalisation n\'est pas supportée par ce navigateur.');
        btn.innerHTML = originalContent;
        btn.disabled = false;
    }
}

// Fonction pour gérer le changement de tri
function handleSortChange() {
    const sort = document.getElementById('sort').value;
    if (sort === 'distance') {
        const lat = document.getElementById('latitude').value;
        const lon = document.getElementById('longitude').value;
        if (!lat || !lon) {
            alert('Veuillez d\'abord définir votre position pour trier par distance.');
            document.getElementById('sort').value = '';
            return;
        }
    }
    document.getElementById('filtersForm').submit();
}

// Fonction pour gérer le changement de rayon
function handleRadiusChange() {
    const radius = document.getElementById('radius').value;
    if (radius) {
        const lat = document.getElementById('latitude').value;
        const lon = document.getElementById('longitude').value;
        if (!lat || !lon) {
            alert('Veuillez d\'abord définir votre position pour utiliser la recherche par rayon.');
            document.getElementById('radius').value = '';
            return;
        }
    }
    document.getElementById('filtersForm').submit();
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggleFilters');
    toggleButton.addEventListener('click', toggleFilters);
    
    // Afficher les filtres si des paramètres sont présents
    const urlParams = new URLSearchParams(window.location.search);
    const hasFilters = Array.from(urlParams.keys()).some(key => 
        ['search', 'city', 'condition', 'price_max', 'sort', 'urgent_only', 'with_delivery', 'latitude', 'longitude', 'radius'].includes(key) && urlParams.get(key)
    );
    
    if (hasFilters) {
        toggleFilters();
    }
    
    // Afficher les distances si la position est disponible
    displayDistances();
});

// Autocomplete functionality for location input
document.addEventListener('DOMContentLoaded', function() {
    // Autocomplete variables
    let searchTimeout;
    let currentFocus = -1;
    const locationInput = document.getElementById('city');
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
                '<strong class="text-red-600">$1</strong>'
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
                '<strong class="text-red-600">$1</strong>'
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
        
        document.getElementById('city').value = text;
        
        // Hide the suggestions dropdown
        hideSuggestions();
    }

    function selectLocationFromNominatimData(element) {
        const displayName = element.getAttribute('data-display-name');
        
        console.log('Selecting location from Nominatim:', displayName);
        
        document.getElementById('city').value = displayName;
        
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

@endsection