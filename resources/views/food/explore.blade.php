@extends('layouts.app-clean')

@section('title', 'Food - Trouvez votre repas')

@push('styles')
<style>
    .chef-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .chef-card:hover {
        transform: translateY(-8px);
    }
    .chef-card:hover .chef-image {
        transform: scale(1.05);
    }
    .chef-image {
        transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .suggestion-item:hover {
        background-color: #fff7ed;
    }
    .suggestion-item.active {
        background-color: #ffedd5;
    }
    /* Hide scrollbar */
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    
    /* Mobile optimizations */
    @media (max-width: 640px) {
        /* Hero compact */
        .bg-gradient-to-r.from-orange-500 .py-10 {
            padding-top: 1.5rem !important;
            padding-bottom: 1.5rem !important;
        }
        
        /* Titre hero plus petit */
        .text-3xl.sm\:text-4xl {
            font-size: 1.5rem !important;
        }
        
        /* Grille cuisiniers - 1 colonne sur très petit écran */
        .grid.grid-cols-1.sm\:grid-cols-2 {
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
        }
        
        /* Cards plus compactes */
        .chef-card .h-48 {
            height: 180px !important;
        }
        
        /* Filtres catégories - scroll plus fluide */
        .overflow-x-auto {
            padding-bottom: 8px;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Padding réduit pour le conteneur */
        .max-w-7xl.mx-auto.px-4 {
            padding-left: 12px !important;
            padding-right: 12px !important;
        }
        
        /* Boutons filtres full width */
        .flex.flex-col.sm\:flex-row.gap-3 button,
        .flex.flex-col.sm\:flex-row.gap-3 a {
            width: 100%;
        }
        
        /* Options checkbox empilées */
        .flex.flex-wrap.gap-4 {
            flex-direction: column;
            gap: 8px !important;
        }
        
        .flex.flex-wrap.gap-4 label {
            width: 100%;
        }
        
        /* Page title plus petit */
        .text-xl.sm\:text-2xl {
            font-size: 1.1rem !important;
        }
        
        /* Espacement bottom pour navigation */
        .pb-12 {
            padding-bottom: 100px !important;
        }
    }
    
    @media (min-width: 641px) and (max-width: 768px) {
        /* Tablette - 2 colonnes */
        .grid.grid-cols-1.sm\:grid-cols-2.lg\:grid-cols-3 {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 via-amber-50 to-yellow-50">
    
    {{-- Hero --}}
    <div class="bg-gradient-to-r from-orange-500 via-amber-500 to-yellow-500 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
            <div class="text-center">
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-3">
                    🍽️ Saveurs locales
                </h1>
                <p class="text-lg sm:text-xl text-orange-100 mb-6">
                    Des plats faits maison par des passionnés
                </p>
            </div>
        </div>
    </div>

    {{-- Section des filtres --}}
    <div class="max-w-7xl mx-auto px-3 sm:px-4 mb-6 sm:mb-8 -mt-6">
        <div class="rounded-2xl sm:rounded-3xl shadow-lg sm:shadow-xl border-2 overflow-hidden" style="background: linear-gradient(to bottom, #fff7ed 0%, #ffffff 100%); border-color: #fdba74;">
            <div class="h-1.5 sm:h-2" style="background: linear-gradient(to right, #ea580c 0%, #f97316 50%, #fbbf24 100%);"></div>
            <div class="p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-4">
                    <div>
                        <h3 class="text-base sm:text-xl font-bold flex items-center" style="color: #7c2d12;">
                            <span class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl text-white flex items-center justify-center mr-2 sm:mr-3 shadow-md" style="background: #ea580c;">
                                <i class="fas fa-filter text-sm sm:text-base"></i>
                            </span>
                            Trouver un cuisinier
                        </h3>
                    </div>
                    <button type="button" id="toggleFilters" class="text-white font-semibold py-2 px-4 sm:py-2.5 sm:px-5 rounded-lg sm:rounded-xl transition-all flex items-center justify-center text-xs sm:text-sm shadow-md" style="background: #ea580c;">
                        <span id="filterButtonText">Afficher les filtres</span>
                        <i class="fas fa-chevron-down ml-2" id="filterChevron"></i>
                    </button>
                </div>
                
                <form action="{{ route('food.explore') }}" method="GET" class="space-y-4" id="filtersForm" style="display: none;">
                    {{-- Première ligne de filtres --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        {{-- Recherche --}}
                        <div>
                            <label for="search" class="block text-xs font-bold text-orange-600 uppercase tracking-wider mb-1">Recherche</label>
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-orange-400"></i>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                       placeholder="Plat, cuisine..." 
                                       class="w-full pl-10 pr-4 py-3 text-sm bg-white border border-orange-100 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-300 text-slate-700 placeholder-slate-400">
                            </div>
                        </div>
                        
                        {{-- Catégorie --}}
                        <div>
                            <label for="category" class="block text-xs font-bold text-orange-600 uppercase tracking-wider mb-1">Catégorie</label>
                            <div class="relative">
                                <i class="fas fa-utensils absolute left-3 top-1/2 transform -translate-y-1/2 text-orange-400"></i>
                                <select name="category" id="category" class="w-full pl-10 pr-8 py-3 text-sm bg-white border border-orange-100 rounded-xl focus:ring-2 focus:ring-orange-500 text-slate-700 appearance-none">
                                    <option value="">Toutes catégories</option>
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        {{-- Prix max --}}
                        <div>
                            <label for="price_max" class="block text-xs font-bold text-orange-600 uppercase tracking-wider mb-1">Prix max</label>
                            <div class="relative">
                                <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-orange-400"></i>
                                <select name="price_max" id="price_max" class="w-full pl-10 pr-8 py-3 text-sm bg-white border border-orange-100 rounded-xl focus:ring-2 focus:ring-orange-500 text-slate-700 appearance-none">
                                    <option value="">Tous les prix</option>
                                    <option value="10" {{ request('price_max') == '10' ? 'selected' : '' }}>Jusqu'à 10€</option>
                                    <option value="15" {{ request('price_max') == '15' ? 'selected' : '' }}>Jusqu'à 15€</option>
                                    <option value="20" {{ request('price_max') == '20' ? 'selected' : '' }}>Jusqu'à 20€</option>
                                    <option value="30" {{ request('price_max') == '30' ? 'selected' : '' }}>Jusqu'à 30€</option>
                                    <option value="50" {{ request('price_max') == '50' ? 'selected' : '' }}>Jusqu'à 50€</option>
                                </select>
                            </div>
                        </div>
                        
                        {{-- Tri --}}
                        <div>
                            <label for="sort" class="block text-xs font-bold text-orange-600 uppercase tracking-wider mb-1">Trier par</label>
                            <div class="relative">
                                <i class="fas fa-sort absolute left-3 top-1/2 transform -translate-y-1/2 text-orange-400"></i>
                                <select name="sort" id="sort" class="w-full pl-10 pr-8 py-3 text-sm bg-white border border-orange-100 rounded-xl focus:ring-2 focus:ring-orange-500 text-slate-700 appearance-none">
                                    <option value="popular" {{ request('sort', 'popular') === 'popular' ? 'selected' : '' }}>Populaires</option>
                                    <option value="distance" {{ request('sort') === 'distance' ? 'selected' : '' }}>Distance</option>
                                    <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Plus récents</option>
                                    <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Alphabétique</option>
                                    <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                                    <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Deuxième ligne: Localisation --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        {{-- Localisation avec autocomplétion --}}
                        <div class="sm:col-span-2">
                            <label for="city" class="block text-xs font-bold text-orange-600 uppercase tracking-wider mb-1">Localisation</label>
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <i class="fas fa-map-marker-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-orange-400"></i>
                                    <input type="text" name="city" id="city" value="{{ request('city') }}" 
                                           placeholder="Ville ou code postal..." 
                                           class="w-full pl-10 pr-4 py-3 text-sm bg-white border border-orange-100 rounded-xl focus:ring-2 focus:ring-orange-500 text-slate-700 placeholder-slate-400"
                                           autocomplete="off">
                                    <input type="hidden" name="latitude" id="latitude" value="{{ request('latitude') }}">
                                    <input type="hidden" name="longitude" id="longitude" value="{{ request('longitude') }}">
                                    {{-- Dropdown suggestions --}}
                                    <div id="location-suggestions" class="absolute top-full left-0 right-0 bg-white border border-orange-200 rounded-xl shadow-2xl mt-1 z-[99999] hidden max-h-60 overflow-y-auto"></div>
                                </div>
                                <button type="button" id="getLocationBtn" onclick="getMyLocation()" 
                                        class="px-4 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-xl transition duration-200 flex items-center justify-center shadow-md" 
                                        title="Utiliser ma position">
                                    <i class="fas fa-crosshairs mr-2"></i>
                                    <span class="hidden sm:inline">GPS</span>
                                </button>
                            </div>
                        </div>
                        
                        {{-- Périmètre --}}
                        <div>
                            <label for="radius" class="block text-xs font-bold text-orange-600 uppercase tracking-wider mb-1">Périmètre</label>
                            <div class="relative">
                                <i class="fas fa-bullseye absolute left-3 top-1/2 transform -translate-y-1/2 text-orange-400"></i>
                                <select name="radius" id="radius" class="w-full pl-10 pr-8 py-3 text-sm bg-white border border-orange-100 rounded-xl focus:ring-2 focus:ring-orange-500 text-slate-700 appearance-none">
                                    <option value="">Partout</option>
                                    <option value="5" {{ request('radius') == '5' ? 'selected' : '' }}>5 km</option>
                                    <option value="10" {{ request('radius') == '10' ? 'selected' : '' }}>10 km</option>
                                    <option value="25" {{ request('radius') == '25' ? 'selected' : '' }}>25 km</option>
                                    <option value="50" {{ request('radius') == '50' ? 'selected' : '' }}>50 km</option>
                                    <option value="100" {{ request('radius') == '100' ? 'selected' : '' }}>100 km</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Options supplémentaires --}}
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center cursor-pointer bg-orange-50 px-4 py-2 rounded-xl border border-orange-100">
                            <input type="checkbox" name="available_now" value="1" {{ request('available_now') ? 'checked' : '' }} 
                                   class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200">
                            <span class="ml-2 text-sm text-slate-700 font-medium">🟢 Disponible maintenant</span>
                        </label>
                        <label class="flex items-center cursor-pointer bg-orange-50 px-4 py-2 rounded-xl border border-orange-100">
                            <input type="checkbox" name="with_delivery" value="1" {{ request('with_delivery') ? 'checked' : '' }} 
                                   class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200">
                            <span class="ml-2 text-sm text-slate-700 font-medium">🚚 Avec livraison</span>
                        </label>
                        <label class="flex items-center cursor-pointer bg-orange-50 px-4 py-2 rounded-xl border border-orange-100">
                            <input type="checkbox" name="vegetarian" value="1" {{ request('vegetarian') ? 'checked' : '' }} 
                                   class="rounded border-gray-300 text-orange-600 shadow-sm focus:border-orange-300 focus:ring focus:ring-orange-200">
                            <span class="ml-2 text-sm text-slate-700 font-medium">🥗 Végétarien</span>
                        </label>
                    </div>
                    
                    {{-- Boutons d'action --}}
                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-orange-100">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold py-3 px-6 text-sm rounded-xl transition duration-200 shadow-md hover:shadow-lg flex items-center justify-center">
                            <i class="fas fa-search mr-2"></i>
                            Rechercher
                        </button>
                        <a href="{{ route('food.explore') }}" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 px-6 text-sm rounded-xl transition duration-200 flex items-center justify-center">
                            <i class="fas fa-times mr-2"></i>
                            Réinitialiser
                        </a>
                    </div>
                </form>
                
                {{-- Affichage des résultats --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 border-t border-orange-100 mt-4">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-slate-600">Résultats :</span>
                        <span class="px-3 py-1.5 bg-orange-100 text-orange-800 rounded-full text-sm font-bold">
                            {{ $prestataires->total() }} cuisinier(s)
                        </span>
                    </div>
                    @if(request('city') && request('radius'))
                        <span class="text-sm text-orange-600 font-medium">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            Dans un rayon de {{ request('radius') }} km autour de {{ request('city') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Filtres catégories rapides (scrollable horizontal) --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <div class="overflow-x-auto -mx-4 px-4 scrollbar-hide">
            <div class="flex gap-2 pb-2" style="min-width: max-content;">
                <a href="{{ route('food.explore', array_merge(request()->except('category'), [])) }}" 
                   class="px-3 py-1.5 rounded-full text-sm font-medium transition whitespace-nowrap {{ !request('category') ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-orange-100 border border-gray-200' }}">
                    🍽️ Tous
                </a>
                @foreach($categories as $key => $label)
                    @php
                        $emojis = [
                            'entree' => '🥗', 'plat' => '🍝', 'dessert' => '🍰', 'boisson' => '🥤',
                            'amuse_bouche' => '🧆', 'gateau' => '🎂', 'pizza' => '🍕',
                            'sandwich' => '🥪', 'salade' => '🥗', 'autre' => '🍴',
                        ];
                    @endphp
                    <a href="{{ route('food.explore', array_merge(request()->except('category'), ['category' => $key])) }}" 
                       class="px-3 py-1.5 rounded-full text-sm font-medium transition whitespace-nowrap {{ request('category') === $key ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-white shadow-md' : 'bg-white text-gray-700 hover:bg-orange-100 border border-gray-200' }}">
                        {{ $emojis[$key] ?? '🍴' }} {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Titre résultats --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">
                @if(request('search'))
                    Résultats pour "{{ request('search') }}"
                @elseif(request('category'))
                    {{ $categories[request('category')] ?? 'Catégorie' }}
                @else
                    Tous les cuisiniers
                @endif
            </h2>
        </div>
    </div>

    {{-- Liste cuisiniers --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        @if($prestataires->isEmpty())
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gradient-to-br from-orange-100 to-amber-100 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <span class="text-5xl">🍳</span>
                </div>
                <h3 class="text-xl font-bold text-gray-700 mb-2">Aucun cuisinier trouvé</h3>
                <p class="text-gray-500 mb-6">Essayez de modifier vos critères</p>
                <a href="{{ route('food.explore') }}" class="px-6 py-3 bg-gradient-to-r from-orange-500 to-amber-500 text-white font-semibold rounded-xl hover:from-orange-600 hover:to-amber-600 transition shadow-lg">
                    Voir tous les cuisiniers
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($prestataires as $prestataire)
                    <div class="chef-card group block">
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl border border-gray-100 transition-all duration-300 hover:-translate-y-1">
                            {{-- Image principale (cliquable vers menu) --}}
                            <a href="{{ route('food.menu', $prestataire) }}" class="block relative h-48 overflow-hidden">
                                {{-- Fond dégradé par défaut --}}
                                <div class="absolute inset-0 bg-gradient-to-br from-orange-400 via-amber-400 to-orange-500 flex items-center justify-center">
                                    <span class="text-8xl opacity-90">👨‍🍳</span>
                                </div>
                                
                                {{-- Image si disponible --}}
                                @php
                                    $foodProductImage = $prestataire->foodProducts
                                        ->pluck('image')
                                        ->filter()
                                        ->first();
                                    $imagePath = $prestataire->cover_image
                                        ?? $prestataire->logo
                                        ?? $prestataire->photo
                                        ?? $prestataire->profile_image
                                        ?? $foodProductImage
                                        ?? $prestataire->profile_photo
                                        ?? $prestataire->user->profile_photo_url
                                        ?? null;
                                @endphp
                                @if($imagePath)
                                    <img src="{{ storage_asset_url($imagePath) }}" 
                                         alt="{{ $prestataire->business_name }}" 
                                         class="absolute inset-0 w-full h-full object-cover"
                                         onerror="this.style.display='none'">
                                @endif
                                
                                {{-- Badge plats --}}
                                <div class="absolute top-3 right-3 z-10">
                                    <span class="px-3 py-1.5 bg-white text-orange-600 text-sm font-bold rounded-full shadow-lg">
                                        {{ $prestataire->food_products_count ?? 0 }} plats
                                    </span>
                                </div>
                                
                                {{-- Nom du cuisinier --}}
                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent pt-16 pb-4 px-4 z-10">
                                    <h3 class="text-white font-bold text-xl drop-shadow-lg">
                                        {{ $prestataire->business_name ?? $prestataire->user->name ?? 'Cuisinier' }}
                                    </h3>
                                </div>
                            </a>
                            
                            {{-- Infos --}}
                            <div class="p-4">
                                {{-- Catégories --}}
                                @php
                                    $prestataireCategories = $prestataire->foodProducts
                                        ->where('is_available', true)
                                        ->pluck('category')
                                        ->unique()
                                        ->take(3);
                                @endphp
                                @if($prestataireCategories->count() > 0)
                                    <div class="flex flex-wrap gap-1.5 mb-3">
                                        @foreach($prestataireCategories as $cat)
                                            <span class="px-2.5 py-1 bg-orange-100 text-orange-600 text-xs rounded-full font-semibold">
                                                {{ $categories[$cat] ?? ucfirst($cat) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                
                                {{-- Prix moyen --}}
                                @php
                                    $avgPrice = $prestataire->foodProducts->where('is_available', true)->avg('price');
                                @endphp
                                @if($avgPrice)
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-500 text-sm">Prix moyen</span>
                                        <span class="font-bold text-lg text-orange-600">{{ number_format($avgPrice, 2) }} €</span>
                                    </div>
                                @endif
                            </div>
                            
                            {{-- CTA --}}
                            <div class="px-4 pb-4 space-y-2">
                                <a href="{{ route('food.menu', $prestataire) }}" class="block w-full py-3 bg-gradient-to-r from-orange-500 to-amber-500 text-white text-center font-bold rounded-xl hover:from-orange-600 hover:to-amber-600 transition shadow-md">
                                    🍽️ Voir le menu
                                </a>
                                <a href="{{ route('prestataire.profile.public', $prestataire) }}" class="block w-full py-2.5 bg-gray-100 text-gray-700 text-center font-semibold rounded-xl hover:bg-gray-200 transition border border-gray-200 text-sm">
                                    ℹ️ Voir les détails
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($prestataires->hasPages())
                <div class="mt-10">
                    {{ $prestataires->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

@push('scripts')
<script>
// Toggle filters
function toggleFilters() {
    const filtersForm = document.getElementById('filtersForm');
    const buttonText = document.getElementById('filterButtonText');
    const chevron = document.getElementById('filterChevron');
    
    if (filtersForm.style.display === 'none' || filtersForm.style.display === '') {
        filtersForm.style.display = 'block';
        buttonText.textContent = 'Masquer les filtres';
        chevron.classList.add('rotate-180');
    } else {
        filtersForm.style.display = 'none';
        buttonText.textContent = 'Afficher les filtres';
        chevron.classList.remove('rotate-180');
    }
}

// Geolocation
function getMyLocation() {
    const btn = document.getElementById('getLocationBtn');
    const cityInput = document.getElementById('city');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    
    if (!navigator.geolocation) {
        alert('La géolocalisation n\'est pas supportée par ce navigateur.');
        return;
    }
    
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span class="hidden sm:inline">Localisation...</span>';
    btn.disabled = true;
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            latInput.value = lat;
            lngInput.value = lng;
            
            // Reverse geocoding with Nominatim
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=fr`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.address) {
                        const city = data.address.city || data.address.town || data.address.village || data.address.municipality || '';
                        const postcode = data.address.postcode || '';
                        cityInput.value = city + (postcode ? ` (${postcode})` : '');
                    } else {
                        cityInput.value = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                    }
                })
                .catch(error => {
                    console.error('Geocoding error:', error);
                    cityInput.value = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                })
                .finally(() => {
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                });
        },
        function(error) {
            let msg = 'Erreur: ';
            switch(error.code) {
                case error.PERMISSION_DENIED: msg += 'Permission refusée.'; break;
                case error.POSITION_UNAVAILABLE: msg += 'Position indisponible.'; break;
                case error.TIMEOUT: msg += 'Délai dépassé.'; break;
                default: msg += 'Erreur inconnue.';
            }
            alert(msg);
            btn.innerHTML = originalContent;
            btn.disabled = false;
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 300000 }
    );
}

// Autocomplete
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggleFilters');
    if (toggleButton) {
        toggleButton.addEventListener('click', toggleFilters);
    }
    
    // Show filters if any are active
    const hasFilters = {{ request()->anyFilled(['search', 'category', 'price_max', 'city', 'radius', 'available_now', 'with_delivery', 'vegetarian']) ? 'true' : 'false' }};
    if (hasFilters) {
        toggleFilters();
    }
    
    // Autocomplete for city
    let searchTimeout;
    let currentFocus = -1;
    const cityInput = document.getElementById('city');
    const suggestionsContainer = document.getElementById('location-suggestions');
    
    if (cityInput && suggestionsContainer) {
        cityInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                hideSuggestions();
                return;
            }
            
            searchTimeout = setTimeout(() => fetchSuggestions(query), 300);
        });
        
        cityInput.addEventListener('keydown', function(e) {
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
            } else if (e.key === 'Enter' && currentFocus > -1) {
                e.preventDefault();
                if (suggestions[currentFocus]) suggestions[currentFocus].click();
            } else if (e.key === 'Escape') {
                hideSuggestions();
            }
        });
        
        document.addEventListener('click', function(e) {
            if (!cityInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                hideSuggestions();
            }
        });
    }
    
    function fetchSuggestions(query) {
        fetch(`/api/public/geolocation/cities?search=${encodeURIComponent(query)}&limit=10`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.length > 0) {
                    displaySuggestions(data.data, query);
                } else {
                    // Fallback to Nominatim
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=10&addressdetails=1&countrycodes=fr`)
                        .then(r => r.json())
                        .then(nominatimData => {
                            if (nominatimData && nominatimData.length > 0) {
                                displayNominatimSuggestions(nominatimData, query);
                            } else {
                                hideSuggestions();
                            }
                        });
                }
            })
            .catch(() => {
                // Fallback to Nominatim on error
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=10&addressdetails=1&countrycodes=fr`)
                    .then(r => r.json())
                    .then(nominatimData => {
                        if (nominatimData && nominatimData.length > 0) {
                            displayNominatimSuggestions(nominatimData, query);
                        }
                    });
            });
    }
    
    function displaySuggestions(suggestions, query) {
        suggestionsContainer.innerHTML = '';
        currentFocus = -1;
        
        suggestions.forEach((s, i) => {
            const div = document.createElement('div');
            div.className = 'suggestion-item p-3 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors';
            div.innerHTML = `<div class="font-medium text-gray-800"><i class="fas fa-map-marker-alt text-orange-400 mr-2"></i>${s.text.replace(new RegExp(`(${query})`, 'gi'), '<strong class="text-orange-600">$1</strong>')}</div>`;
            div.setAttribute('data-text', s.text);
            div.addEventListener('click', () => {
                document.getElementById('city').value = s.text;
                hideSuggestions();
            });
            suggestionsContainer.appendChild(div);
        });
        
        suggestionsContainer.classList.remove('hidden');
    }
    
    function displayNominatimSuggestions(suggestions, query) {
        suggestionsContainer.innerHTML = '';
        currentFocus = -1;
        
        suggestions.forEach((s, i) => {
            const div = document.createElement('div');
            div.className = 'suggestion-item p-3 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors';
            const displayName = s.display_name.split(',').slice(0, 3).join(', ');
            div.innerHTML = `<div class="font-medium text-gray-800"><i class="fas fa-map-marker-alt text-orange-400 mr-2"></i>${displayName}</div>`;
            div.setAttribute('data-lat', s.lat);
            div.setAttribute('data-lon', s.lon);
            div.setAttribute('data-name', displayName);
            div.addEventListener('click', () => {
                document.getElementById('city').value = displayName;
                document.getElementById('latitude').value = s.lat;
                document.getElementById('longitude').value = s.lon;
                hideSuggestions();
            });
            suggestionsContainer.appendChild(div);
        });
        
        suggestionsContainer.classList.remove('hidden');
    }
    
    function hideSuggestions() {
        suggestionsContainer.classList.add('hidden');
        suggestionsContainer.innerHTML = '';
    }
    
    function setActiveSuggestion(suggestions) {
        suggestions.forEach(s => s.classList.remove('active', 'bg-orange-50'));
        if (currentFocus >= 0 && suggestions[currentFocus]) {
            suggestions[currentFocus].classList.add('active', 'bg-orange-50');
        }
    }
});
</script>
@endpush
@endsection
