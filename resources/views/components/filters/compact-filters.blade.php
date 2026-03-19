{{-- 
    Composant de filtres modernes - Version compacte et fonctionnelle
    Usage: @include('components.filters.compact-filters', [...])
--}}

@php
    $pageType = $pageType ?? 'services';
    $themeColor = $themeColor ?? 'blue';
    $categories = $categories ?? collect();
    $conditions = $conditions ?? [];
    
    // Couleurs par thème
    $themes = [
        'blue' => ['primary' => 'blue', 'gradient' => 'from-blue-500 to-indigo-600', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-600', 'ring' => 'ring-blue-500', 'btn' => 'bg-blue-600 hover:bg-blue-700'],
        'emerald' => ['primary' => 'emerald', 'gradient' => 'from-emerald-500 to-teal-600', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-500', 'btn' => 'bg-emerald-600 hover:bg-emerald-700'],
        'orange' => ['primary' => 'orange', 'gradient' => 'from-orange-500 to-red-500', 'bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'text' => 'text-orange-600', 'ring' => 'ring-orange-500', 'btn' => 'bg-orange-600 hover:bg-orange-700'],
    ];
    $t = $themes[$themeColor] ?? $themes['blue'];
    
    // Prix max par page (jusqu'à 50000€)
    $maxPrices = ['services' => 50000, 'equipment' => 50000, 'urgent-sales' => 50000, 'food' => 5000];
    $maxPrice = $maxPrices[$pageType] ?? 50000;
    
    // Valeurs actuelles
    $search = request('search', '');
    $category = request('category', request('main_category', ''));
    $subcategory = request('subcategory', request('sub_category', ''));
    $priceMin = request('price_min', '');
    $priceMax = request('price_max', '');
    $location = request('location', request('city', ''));
    $radius = request('radius', '25');
    $latitude = request('latitude', '');
    $longitude = request('longitude', '');
    $sort = request('sort', '');
    $serviceDate = request('service_date', '');
    $serviceTime = request('service_time', '');
    $equipmentDateFrom = request('equipment_date_from', request('available_from', ''));
    $equipmentDateTo = request('equipment_date_to', request('available_to', ''));
    
    // Préparer les sous-catégories pour JavaScript
    $categoriesWithChildren = [];
    foreach($categories as $cat) {
        if (is_object($cat) && isset($cat->children)) {
            $categoriesWithChildren[$cat->id] = $cat->children->map(function($child) {
                return ['id' => $child->id, 'name' => $child->name];
            })->toArray();
        }
    }
@endphp

<style>
    .compact-filters .range-track { height: 6px; background: #e5e7eb; border-radius: 3px; position: relative; }
    .compact-filters .range-fill { position: absolute; height: 100%; background: var(--accent-color); border-radius: 3px; }
    .compact-filters input[type="range"] { -webkit-appearance: none; width: 100%; height: 6px; background: transparent; position: relative; z-index: 10; }
    .compact-filters input[type="range"]::-webkit-slider-thumb { -webkit-appearance: none; width: 18px; height: 18px; background: var(--accent-color); border-radius: 50%; cursor: pointer; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
    .compact-filters input[type="range"]::-moz-range-thumb { width: 18px; height: 18px; background: var(--accent-color); border-radius: 50%; cursor: pointer; border: 2px solid white; }
    .compact-filters .filter-section { transition: all 0.3s ease; }
    .compact-filters .compact-filters-header { min-height: 44px; }
    .compact-filters.compact-filters--collapsed .compact-filters-header { margin-bottom: 0; }
    .compact-filters.compact-filters--expanded .compact-filters-header { margin-bottom: 0.75rem; }
    .compact-filters.compact-filters--collapsed .compact-filters-body { padding-bottom: 0.75rem; }
    .compact-filters .location-suggestions { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); z-index: 9999; max-height: 200px; overflow-y: auto; }
    .compact-filters .suggestion-item { padding: 10px 12px; cursor: pointer; transition: background 0.2s; }
    .compact-filters .suggestion-item:hover { background: #f3f4f6; }
</style>

<div
    class="compact-filters compact-filters--collapsed rounded-2xl shadow-lg border {{ $t['border'] }} bg-white overflow-hidden"
    data-compact-filters
    data-form-action="{{ $formAction }}"
    data-subcategories="{{ e(json_encode($categoriesWithChildren)) }}"
    style="--accent-color: {{ $themeColor === 'blue' ? '#2563eb' : ($themeColor === 'emerald' ? '#059669' : '#ea580c') }};"
>
    {{-- Barre de couleur --}}
    <div class="h-1.5 bg-gradient-to-r {{ $t['gradient'] }}"></div>
    
    <div class="compact-filters-body p-3 sm:p-4">
        {{-- En-tête --}}
        <div class="compact-filters-header flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-gradient-to-r {{ $t['gradient'] }} flex items-center justify-center text-white">
                    <i class="fas fa-sliders-h text-sm"></i>
                </span>
                <span class="font-bold {{ $t['text'] }}">Filtres</span>
                @php
                    $activeFilters = collect([
                        $search, $category, $priceMin, $priceMax, $location, 
                        request('verified_only'), request('with_delivery'), request('condition'),
                        $serviceDate, $serviceTime, $equipmentDateFrom, $equipmentDateTo,
                        request('available_now'), request('reservable'), request('availability')
                    ])->filter()->count();
                @endphp
                @if($activeFilters > 0)
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold text-white bg-gradient-to-r {{ $t['gradient'] }}">{{ $activeFilters }}</span>
                @endif
            </div>
            <button type="button" id="compactFiltersToggle" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-white text-sm font-medium {{ $t['btn'] }} transition-all">
                <span id="filterToggleText">Afficher</span>
                <i class="fas fa-chevron-down transition-transform" id="filterChevron"></i>
            </button>
        </div>
        
        {{-- Formulaire --}}
        <form method="GET" action="{{ $formAction }}" id="compactFiltersForm" class="filter-section mt-3 hidden">
            <div class="space-y-4">
                {{-- Ligne 1: Recherche + Catégorie + Sous-catégorie + Tri --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    {{-- Recherche --}}
                    <div>
                        <label class="block text-xs font-semibold {{ $t['text'] }} mb-1">🔍 Recherche</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Mots-clés..." 
                               class="w-full px-3 py-2 text-sm border {{ $t['border'] }} rounded-lg focus:ring-2 focus:{{ $t['ring'] }} focus:border-transparent transition">
                    </div>
                    
                    {{-- Catégorie principale --}}
                    @if($categories && (is_array($categories) ? count($categories) > 0 : $categories->count() > 0))
                    <div>
                        <label class="block text-xs font-semibold {{ $t['text'] }} mb-1">📁 Catégorie</label>
                        <select name="{{ $pageType === 'services' ? 'main_category' : 'category' }}" id="mainCategorySelect"
                                class="w-full px-3 py-2 text-sm border {{ $t['border'] }} rounded-lg focus:ring-2 focus:{{ $t['ring'] }} focus:border-transparent transition appearance-none bg-white">
                            <option value="">Toutes</option>
                            @foreach($categories as $key => $cat)
                                @if(is_object($cat))
                                    <option value="{{ $cat->id }}" {{ $category == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @else
                                    <option value="{{ $key }}" {{ $category == $key ? 'selected' : '' }}>{{ $cat }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Sous-catégorie --}}
                    <div id="subcategoryContainer" style="{{ $category ? '' : 'opacity: 0.5;' }}">
                        <label class="block text-xs font-semibold {{ $t['text'] }} mb-1">📂 Sous-catégorie</label>
                        <select name="{{ $pageType === 'services' ? 'sub_category' : 'subcategory' }}" id="subCategorySelect"
                                class="w-full px-3 py-2 text-sm border {{ $t['border'] }} rounded-lg focus:ring-2 focus:{{ $t['ring'] }} focus:border-transparent transition appearance-none bg-white"
                                {{ $category ? '' : 'disabled' }}>
                            <option value="">Toutes</option>
                            @if($category)
                                @foreach($categories as $cat)
                                    @if(is_object($cat) && $cat->id == $category && isset($cat->children))
                                        @foreach($cat->children as $child)
                                            <option value="{{ $child->id }}" {{ $subcategory == $child->id ? 'selected' : '' }}>{{ $child->name }}</option>
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>
                    @endif
                    
                    {{-- État (urgent-sales) --}}
                    @if($pageType === 'urgent-sales' && !empty($conditions))
                    <div>
                        <label class="block text-xs font-semibold {{ $t['text'] }} mb-1">📦 État</label>
                        <select name="condition" class="w-full px-3 py-2 text-sm border {{ $t['border'] }} rounded-lg focus:ring-2 focus:{{ $t['ring'] }} focus:border-transparent transition appearance-none bg-white">
                            <option value="">Tous</option>
                            @foreach($conditions as $value => $label)
                                <option value="{{ $value }}" {{ request('condition') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    {{-- Tri --}}
                    <div>
                        <label class="block text-xs font-semibold {{ $t['text'] }} mb-1">↕️ Trier par</label>
                        <select name="sort" class="w-full px-3 py-2 text-sm border {{ $t['border'] }} rounded-lg focus:ring-2 focus:{{ $t['ring'] }} focus:border-transparent transition appearance-none bg-white">
                            <option value="">Pertinence</option>
                            <option value="distance" {{ $sort == 'distance' ? 'selected' : '' }}>📍 Distance</option>
                            <option value="price_asc" {{ $sort == 'price_asc' ? 'selected' : '' }}>💰 Prix ↗</option>
                            <option value="price_desc" {{ $sort == 'price_desc' ? 'selected' : '' }}>💰 Prix ↘</option>
                            <option value="recent" {{ $sort == 'recent' ? 'selected' : '' }}>🕐 Récent</option>
                            @if($pageType === 'equipment')<option value="rating" {{ $sort == 'rating' ? 'selected' : '' }}>⭐ Note</option>@endif
                        </select>
                    </div>
                </div>
                
                {{-- Ligne 2: Prix avec sliders --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-semibold {{ $t['text'] }}">💶 Prix</label>
                        <div class="flex items-center gap-2 text-sm font-bold {{ $t['text'] }}">
                            <span id="priceMinVal">{{ $priceMin ?: 0 }}€</span>
                            <span>-</span>
                            <span id="priceMaxVal">{{ $priceMax ?: $maxPrice }}€</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="range" name="price_min" id="priceMinRange" min="0" max="{{ $maxPrice }}" value="{{ $priceMin ?: 0 }}" 
                               class="flex-1">
                        <input type="range" name="price_max" id="priceMaxRange" min="0" max="{{ $maxPrice }}" value="{{ $priceMax ?: $maxPrice }}" 
                               class="flex-1">
                    </div>
                </div>
                
                {{-- Ligne 3: Localisation --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2 relative">
                        <label class="block text-xs font-semibold {{ $t['text'] }} mb-1">📍 Localisation</label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" name="{{ $pageType === 'services' ? 'location' : 'city' }}" id="locationInput" 
                                       value="{{ $location }}" placeholder="Ville ou code postal..." autocomplete="off"
                                       class="w-full px-3 py-2 text-sm border {{ $t['border'] }} rounded-lg focus:ring-2 focus:{{ $t['ring'] }} focus:border-transparent transition">
                                <input type="hidden" name="latitude" id="latitudeInput" value="{{ $latitude }}">
                                <input type="hidden" name="longitude" id="longitudeInput" value="{{ $longitude }}">
                                <div id="locationSuggestions" class="location-suggestions hidden"></div>
                            </div>
                            <button type="button" id="gpsLocationButton" class="px-3 py-2 rounded-lg text-white {{ $t['btn'] }} transition-all" title="Ma position">
                                <i class="fas fa-crosshairs" id="gpsIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold {{ $t['text'] }} mb-1">🎯 Rayon: <span id="radiusVal">{{ $radius }}</span> km</label>
                        <input type="range" name="radius" id="radiusRange" min="5" max="100" value="{{ $radius }}" 
                               class="w-full">
                    </div>
                </div>

                {{-- Ligne 4: Disponibilités avancées --}}
                @if($pageType === 'services')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold {{ $t['text'] }} mb-1">📅 Date souhaitée</label>
                        <input type="date" name="service_date" value="{{ $serviceDate }}"
                               min="{{ now()->toDateString() }}"
                               class="w-full px-3 py-2 text-sm border {{ $t['border'] }} rounded-lg focus:ring-2 focus:{{ $t['ring'] }} focus:border-transparent transition bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold {{ $t['text'] }} mb-1">🕐 Heure souhaitée</label>
                        <input type="time" name="service_time" value="{{ $serviceTime }}"
                               class="w-full px-3 py-2 text-sm border {{ $t['border'] }} rounded-lg focus:ring-2 focus:{{ $t['ring'] }} focus:border-transparent transition bg-white">
                    </div>
                </div>
                @endif

                @if($pageType === 'equipment')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold {{ $t['text'] }} mb-1">📅 Du</label>
                        <input type="date" name="equipment_date_from" value="{{ $equipmentDateFrom }}"
                               min="{{ now()->toDateString() }}"
                               class="w-full px-3 py-2 text-sm border {{ $t['border'] }} rounded-lg focus:ring-2 focus:{{ $t['ring'] }} focus:border-transparent transition bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold {{ $t['text'] }} mb-1">📅 Au</label>
                        <input type="date" name="equipment_date_to" value="{{ $equipmentDateTo }}"
                               min="{{ now()->toDateString() }}"
                               class="w-full px-3 py-2 text-sm border {{ $t['border'] }} rounded-lg focus:ring-2 focus:{{ $t['ring'] }} focus:border-transparent transition bg-white">
                    </div>
                </div>
                @endif
                
                {{-- Ligne 5: Options --}}
                <div class="flex flex-wrap gap-3">
                    @if($pageType === 'services')
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg cursor-pointer transition">
                        <input type="checkbox" name="verified_only" value="1" {{ request('verified_only') ? 'checked' : '' }} class="rounded {{ $t['text'] }}">
                        <span class="text-sm">✓ Vérifiés</span>
                    </label>
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg cursor-pointer transition">
                        <input type="checkbox" name="available_now" value="1" {{ request('available_now') ? 'checked' : '' }} class="rounded {{ $t['text'] }}">
                        <span class="text-sm">🟢 Dispo</span>
                    </label>
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg cursor-pointer transition">
                        <input type="checkbox" name="reservable" value="1" {{ request('reservable') ? 'checked' : '' }} class="rounded {{ $t['text'] }}">
                        <span class="text-sm">📅 Réservable</span>
                    </label>
                    @endif
                    @if(in_array($pageType, ['equipment', 'food']))
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg cursor-pointer transition">
                        <input type="checkbox" name="with_delivery" value="1" {{ request('with_delivery') ? 'checked' : '' }} class="rounded {{ $t['text'] }}">
                        <span class="text-sm">🚚 Livraison</span>
                    </label>
                    @endif
                    @if($pageType === 'equipment')
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg cursor-pointer transition">
                        <input type="checkbox" name="availability" value="available" {{ request('availability') === 'available' ? 'checked' : '' }} class="rounded {{ $t['text'] }}">
                        <span class="text-sm">🟢 Disponible</span>
                    </label>
                    @endif
                    @if($pageType === 'food')
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg cursor-pointer transition">
                        <input type="checkbox" name="available_now" value="1" {{ request('available_now') ? 'checked' : '' }} class="rounded {{ $t['text'] }}">
                        <span class="text-sm">🔥 Ouvert</span>
                    </label>
                    @endif
                </div>
                
                {{-- Boutons --}}
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl text-white font-semibold {{ $t['btn'] }} shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                    <button type="button" id="compactFiltersReset" class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
