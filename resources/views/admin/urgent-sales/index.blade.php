@extends('layouts.admin-modern')

@section('title', 'Gestion des Ventes Urgentes - Administration')

@section('content')
<div class="bg-red-50">
    <!-- Bannière d'en-tête -->
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-red-900 mb-2 leading-tight">
                    Gestion des Ventes Urgentes
                </h1>
                <p class="text-base sm:text-lg text-red-700 max-w-2xl mx-auto">
                    Gérez toutes les annonces de ventes urgentes publiées sur la plateforme TaPrestation.
                </p>
            </div>
            
            <!-- Actions Header -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                <div class="flex flex-wrap gap-2 sm:gap-3">
                    <button class="bg-red-100 hover:bg-red-200 text-red-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base" onclick="toggleFilters()">
                    <i class="fas fa-filter mr-2"></i>
                    Afficher les filtres
                </button>

                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-6 sm:mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-red-600 uppercase tracking-wide">Total Ventes</div>
                        <div class="text-2xl sm:text-3xl font-bold text-red-900 mt-1">{{ $stats['total'] ?? 0 }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+8% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-bullhorn text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-green-600 uppercase tracking-wide">Ventes Actives</div>
                        <div class="text-2xl sm:text-3xl font-bold text-red-900 mt-1">{{ $stats['active'] ?? 0 }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+12% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-orange-600 uppercase tracking-wide">Signalées</div>
                        <div class="text-2xl sm:text-3xl font-bold text-red-900 mt-1">{{ $stats['reported'] ?? 0 }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-red-600">
                            <i class="fas fa-arrow-down mr-1"></i>
                            <span>-3% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-flag text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-red-600 uppercase tracking-wide">Ventes Vendues</div>
                        <div class="text-2xl sm:text-3xl font-bold text-red-900 mt-1">{{ $urgentSales->where('status', 'sold')->count() ?? 0 }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+5% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-check text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <div id="filtersPanel" class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6 mb-6 sm:mb-8" style="display: none;">
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-center sm:text-left">
                    <h3 class="text-xl sm:text-2xl font-bold text-red-800 mb-1 sm:mb-2">Filtres de recherche</h3>
                    <p class="text-sm sm:text-base lg:text-lg text-red-700">Affinez votre recherche pour trouver la vente urgente parfaite</p>
                </div>
                <button class="bg-red-100 hover:bg-red-200 text-red-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base" onclick="clearFilters()">
                    <i class="fas fa-times mr-2"></i>
                    Effacer tout
                </button>
            </div>
            
            <form action="{{ route('admin.announcements.index') }}" method="GET" class="space-y-4 sm:space-y-6">
                <!-- Première ligne de filtres -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <!-- Titre de la vente -->
                    <div>
                        <label for="search" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Titre de la vente</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Rechercher par titre..." class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 text-sm sm:text-base">
                        </div>
                    </div>
                    
                    <!-- Prestataire -->
                    <div>
                        <label for="prestataire" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Prestataire</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="prestataire" id="prestataire" value="{{ request('prestataire') }}" placeholder="Nom du prestataire..." class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 text-sm sm:text-base">
                        </div>
                    </div>
                    
                    <!-- Catégorie -->
                    <div>
                        <label for="category" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Catégorie</label>
                        <div class="relative">
                            <i class="fas fa-tags absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select name="category" id="category" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Toutes les catégories</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Statut -->
                    <div>
                        <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Statut</label>
                        <div class="relative">
                            <i class="fas fa-info-circle absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select name="status" id="status" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Tous</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Vendu</option>
                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspendu</option>
                                <option value="reported" {{ request('status') == 'reported' ? 'selected' : '' }}>Signalé</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Deuxième ligne de filtres -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                    <!-- Prix -->
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Prix</label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="number" name="price_min" value="{{ request('price_min') }}" placeholder="Prix min" min="0" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 text-sm sm:text-base">
                            </div>
                            <div class="relative flex-1">
                                <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="number" name="price_max" value="{{ request('price_max') }}" placeholder="Prix max" min="0" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 text-sm sm:text-base">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Options -->
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Options</label>
                        <div class="flex flex-wrap gap-4 pt-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="reported" value="yes" {{ request('reported') === 'yes' ? 'checked' : '' }} class="w-4 h-4 text-red-600 border-red-300 rounded focus:ring-red-500">
                                <span class="ml-2 text-sm text-gray-700">Signalées uniquement</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-4 sm:pt-6 border-t-2 border-red-200">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-search mr-2"></i>Appliquer les filtres
                    </button>
                    
                    <button type="button" onclick="clearFilters()" class="flex-1 bg-red-100 hover:bg-red-200 text-red-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-times mr-2"></i>Effacer tout
                    </button>
                    
                    @if(request('search') || request('prestataire') || request('category') || request('status') || request('price_min') || request('price_max') || request('reported'))
                        <a href="{{ route('admin.announcements.index') }}" class="bg-white hover:bg-gray-50 text-red-600 border border-red-200 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                            <i class="fas fa-undo mr-2"></i>Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des ventes urgentes -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <!-- En-tête de la liste -->
        <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6 mb-6 sm:mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="text-center lg:text-left">
                    <h3 class="text-xl sm:text-2xl font-bold text-red-800 mb-1 sm:mb-2">Liste des ventes urgentes</h3>
                    <p class="text-sm sm:text-base text-red-700">{{ $urgentSales->total() }} vente(s) trouvée(s)</p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Tri -->
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Trier par :</label>
                        <select onchange="updateSort(this.value)" class="px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 text-sm">
                            <option value="created_at-desc" {{ request('sort') === 'created_at' && request('order') === 'desc' ? 'selected' : '' }}>Plus récentes</option>
                            <option value="created_at-asc" {{ request('sort') === 'created_at' && request('order') === 'asc' ? 'selected' : '' }}>Plus anciennes</option>
                            <option value="title-asc" {{ request('sort') === 'title' && request('order') === 'asc' ? 'selected' : '' }}>Titre A-Z</option>
                            <option value="price-desc" {{ request('sort') === 'price' && request('order') === 'desc' ? 'selected' : '' }}>Prix décroissant</option>
                            <option value="price-asc" {{ request('sort') === 'price' && request('order') === 'asc' ? 'selected' : '' }}>Prix croissant</option>
                            <option value="views-desc" {{ request('sort') === 'views' && request('order') === 'desc' ? 'selected' : '' }}>Plus vues</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        @if($urgentSales->count() > 0)
            <!-- Grille des ventes -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 sm:gap-6 mb-6 sm:mb-8">
                @foreach($urgentSales as $sale)
                    <div class="bg-white rounded-xl shadow-lg border border-red-200 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <!-- Image de la vente -->
                        <div class="relative h-48 bg-gradient-to-br from-red-50 to-red-100">
                            @if($sale->photos && is_array($sale->photos) && count($sale->photos) > 0)
                                <img src="{{ Storage::url($sale->photos[0]) }}" 
                                     alt="{{ $sale->title }}" 
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDQwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjRkVGMkYyIi8+CjxwYXRoIGQ9Ik0xNzUgMTI1SDE4NVYxMzVIMTc1VjEyNVoiIGZpbGw9IiNGQ0E1QTUiLz4KPHA+dGggZD0iTTE2NSAxNDVIMjM1VjE1NUgxNjVWMTQ1WiIgZmlsbD0iI0ZDQTVBNSIvPgo8cGF0aCBkPSJNMTg1IDEwNUMxOTEuNjI3IDEwNSAxOTcgMTEwLjM3MyAxOTcgMTE3QzE5NyAxMjMuNjI3IDE5MS42MjcgMTI5IDE4NSAxMjlDMTc4LjM3MyAxMjkgMTczIDEyMy42MjcgMTczIDExN0MxNzMgMTEwLjM3MyAxNzguMzczIDEwNSAxODUgMTA1WiIgZmlsbD0iI0ZDQTVBNSIvPgo8L3N2Zz4K'; this.classList.add('opacity-75');">
                                
                                <!-- Nombre de photos -->
                                @if(count($sale->photos) > 1)
                                    <div class="absolute top-3 left-3 bg-black/70 text-white px-2 py-1 rounded-full text-xs font-medium backdrop-blur-sm">
                                        <i class="fas fa-images mr-1"></i>{{ count($sale->photos) }}
                                    </div>
                                @endif
                            @else
                                <div class="w-full h-full flex items-center justify-center text-red-400">
                                    <div class="text-center">
                                        <i class="fas fa-image text-4xl mb-2"></i>
                                        <p class="text-sm font-medium">Aucune image</p>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Badge de statut -->
                            <div class="absolute top-3 right-3">
                                @switch($sale->status)
                                    @case('sold')
                                        <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                            <i class="fas fa-check mr-1"></i>Vendu
                                        </span>
                                        @break
                                    @case('suspended')
                                        <span class="bg-orange-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                            <i class="fas fa-pause mr-1"></i>Suspendu
                                        </span>
                                        @break
                                    @case('reported')
                                        <span class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Signalé
                                        </span>
                                        @break
                                    @default
                                        <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                            <i class="fas fa-check-circle mr-1"></i>Actif
                                        </span>
                                @endswitch
                            </div>
                        </div>
                        
                        <!-- Contenu de la carte -->
                        <div class="p-4 sm:p-6">
                            <h4 class="text-lg sm:text-xl font-bold text-gray-900 mb-2 line-clamp-2">{{ $sale->title }}</h4>
                            <p class="text-sm sm:text-base text-gray-600 mb-4 line-clamp-3">{{ Str::limit($sale->description, 120) }}</p>
                            
                            <!-- Métadonnées -->
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center text-red-600">
                                        <i class="fas fa-euro-sign mr-2"></i>
                                        <span class="font-bold text-lg">{{ number_format($sale->price, 0, ',', ' ') }} €</span>
                                    </div>
                                    <div class="flex items-center text-gray-500 text-sm">
                                        <i class="fas fa-calendar mr-1"></i>
                                        <span>{{ $sale->created_at->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center text-gray-600 text-sm">
                                    <i class="fas fa-user mr-2"></i>
                                    <span class="truncate">{{ $sale->prestataire->user->name }}</span>
                                </div>
                                
                                @if($sale->category)
                                    <div class="flex items-center text-gray-600 text-sm">
                                        <i class="fas fa-tags mr-2"></i>
                                        <span class="truncate">{{ $sale->category->name }}</span>
                                    </div>
                                @endif
                                
                                <div class="flex items-center text-gray-500 text-sm">
                                    <i class="fas fa-eye mr-2"></i>
                                    <span>{{ $sale->views_count ?? 0 }} vues</span>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.announcements.show', $sale) }}" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200 text-center text-sm">
                                    <i class="fas fa-eye mr-1"></i>Voir
                                </a>
                                
                                @if($sale->status === 'active')
                                    <button onclick="suspendSale({{ $sale->id }})" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-3 rounded-lg transition duration-200 text-sm" title="Suspendre">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                @elseif($sale->status === 'suspended')
                                    <form method="POST" action="{{ route('admin.announcements.reactivate', $sale) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-3 rounded-lg transition duration-200 text-sm" title="Réactiver">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    </form>
                                @endif
                                
                                <button onclick="deleteSale({{ $sale->id }})" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-3 rounded-lg transition duration-200 text-sm" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="text-sm text-gray-700 text-center sm:text-left">
                        Affichage de {{ $urgentSales->firstItem() }} à {{ $urgentSales->lastItem() }} sur {{ $urgentSales->total() }} résultats
                    </div>
                    <div class="flex justify-center">
                        {{ $urgentSales->links() }}
                    </div>
                </div>
            </div>
        @else
            <!-- État vide -->
            <div class="bg-white rounded-xl shadow-lg border border-red-200 p-8 sm:p-12 text-center">
                <div class="max-w-md mx-auto">
                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-bullhorn text-3xl text-red-500"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4">Aucune vente urgente trouvée</h3>
                    <p class="text-gray-600 mb-8">Aucune vente urgente ne correspond aux critères de recherche. Essayez de modifier vos filtres ou de créer une nouvelle vente.</p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ route('admin.announcements.index') }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-refresh mr-2"></i>Voir toutes les ventes
                        </a>
                        <button onclick="toggleFilters()" class="bg-red-100 hover:bg-red-200 text-red-800 font-bold py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i>Afficher les filtres
                    </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal de suspension -->
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Suspendre la vente urgente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="suspendForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="reason">Raison de la suspension *</label>
                        <textarea name="reason" id="reason" class="form-control" rows="3" required placeholder="Expliquez pourquoi cette vente est suspendue..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">Suspendre</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Supprimer la vente urgente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer définitivement cette vente urgente ?</p>
                <p class="text-danger"><strong>Cette action est irréversible.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer définitivement</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle filters panel
    function toggleFilters() {
        const filtersPanel = document.getElementById('filtersPanel');
        const isVisible = filtersPanel.style.display !== 'none';
        filtersPanel.style.display = isVisible ? 'none' : 'block';
        
        // Update button text
        const toggleButton = document.querySelector('[onclick="toggleFilters()"]');
        if (toggleButton) {
            const icon = toggleButton.querySelector('i');
            const text = toggleButton.querySelector('span');
            if (isVisible) {
                icon.className = 'fas fa-filter mr-2';
                if (text) text.textContent = 'Afficher les filtres';
            } else {
                icon.className = 'fas fa-times mr-2';
                if (text) text.textContent = 'Masquer les filtres';
            }
        }
    }
    
    // Clear all filters
    function clearFilters() {
        const form = document.querySelector('#filtersPanel form');
        if (form) {
            // Clear all input fields
            form.querySelectorAll('input[type="text"], input[type="number"]').forEach(input => {
                input.value = '';
            });
            
            // Reset all select fields
            form.querySelectorAll('select').forEach(select => {
                select.selectedIndex = 0;
            });
            
            // Uncheck all checkboxes
            form.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        }
    }
    

    
    // Update sort
    function updateSort(value) {
        const [sort, order] = value.split('-');
        const url = new URL(window.location);
        url.searchParams.set('sort', sort);
        url.searchParams.set('order', order);
        window.location.href = url.toString();
    }
    
    // Suspend sale
    function suspendSale(id) {
        if (confirm('Êtes-vous sûr de vouloir suspendre cette vente urgente ?')) {
            fetch(`/admin/announcements/${id}/suspend`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur lors de la suspension de la vente.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur lors de la suspension de la vente.');
            });
        }
    }
    
    // Delete sale
    function deleteSale(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette vente urgente ? Cette action est irréversible.')) {
            fetch(`/admin/announcements/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur lors de la suppression de la vente.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur lors de la suppression de la vente.');
            });
        }
    }
    
    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-hide success messages
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 5000);
        });
        
        // Add loading states to buttons
        const buttons = document.querySelectorAll('button[type="submit"]');
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Chargement...';
                this.disabled = true;
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 3000);
            });
        });
    });
</script>
@endpush

@push('styles')
<style>
.urgent-row {
    border-left: 4px solid #ff6b6b;
    background-color: #fff5f5;
}

.urgent-badge {
    background: #ff6b6b;
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: bold;
    margin-left: 8px;
}

.item-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.item-thumbnail {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

.item-thumbnail-placeholder {
    width: 60px;
    height: 60px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

.item-details h4 {
    margin: 0 0 4px 0;
    font-size: 14px;
    font-weight: 600;
}

.item-description {
    margin: 0 0 4px 0;
    font-size: 12px;
    color: #6c757d;
}

.category-badge {
    background: #e9ecef;
    color: #495057;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
}

.user-info strong {
    display: block;
    font-size: 13px;
}

.user-info small {
    color: #6c757d;
    font-size: 11px;
}

.price {
    font-weight: 600;
    color: #28a745;
    font-size: 14px;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.status-sold {
    background: #d1ecf1;
    color: #0c5460;
}

.status-suspended {
    background: #fff3cd;
    color: #856404;
}

.views-count {
    font-size: 12px;
    color: #6c757d;
}

.date {
    display: block;
    font-size: 12px;
}

.date small {
    color: #6c757d;
    font-size: 10px;
}

.action-buttons {
    display: flex;
    gap: 4px;
}

.stat-card .stat-icon.urgent {
    background: #ff6b6b;
}

.stat-card .stat-icon.reported {
    background: #ffc107;
}
</style>
@endpush