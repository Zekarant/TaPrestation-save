@extends('layouts.admin-modern')

@section('title', 'Gestion des Annonces - Administration')

@section('content')
<div class="bg-blue-50">
    <!-- Bannière d'en-tête -->
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-blue-900 mb-2 leading-tight">
                    Gestion des Annonces
                </h1>
                <p class="text-base sm:text-lg text-blue-700 max-w-2xl mx-auto">
                    Gérez toutes les annonces publiées sur la plateforme TaPrestation.
                </p>
            </div>
            
            <!-- Actions Header -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                <div class="flex flex-wrap gap-2 sm:gap-3">
                    <a href="{{ route('administrateur.announcements.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-plus mr-2"></i>
                        Nouvelle Annonce
                    </a>
                    <button class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base" onclick="toggleFilters()">
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
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-blue-600 uppercase tracking-wide">Total Annonces</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ $announcements->total() ?? 0 }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+12% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-bullhorn text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-green-600 uppercase tracking-wide">Actives</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ $announcements->where('status', 'active')->count() ?? 0 }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+15% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-orange-600 uppercase tracking-wide">En Attente</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ $announcements->where('status', 'pending')->count() ?? 0 }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-orange-600">
                            <i class="fas fa-clock mr-1"></i>
                            <span>Modération</span>
                        </div>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-hourglass-half text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-blue-600 uppercase tracking-wide">Vues Totales</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ number_format($announcements->sum('views_count') ?? 0) }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+8% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-eye text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <div id="filtersPanel" class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6 mb-6 sm:mb-8" style="display: none;">
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-center sm:text-left">
                    <h3 class="text-xl sm:text-2xl font-bold text-blue-800 mb-1 sm:mb-2">Filtres de recherche</h3>
                    <p class="text-sm sm:text-base lg:text-lg text-blue-700">Affinez votre recherche pour trouver l'annonce parfaite</p>
                </div>
                <button class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base" onclick="clearFilters()">
                    <i class="fas fa-times mr-2"></i>
                    Effacer tout
                </button>
            </div>
            
            <form action="{{ route('administrateur.announcements.index') }}" method="GET" class="space-y-4 sm:space-y-6">
                <!-- Première ligne de filtres -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <!-- Titre de l'annonce -->
                    <div>
                        <label for="title" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Titre de l'annonce</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="title" id="title" value="{{ request('title') }}" placeholder="Rechercher par titre..." class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                        </div>
                    </div>
                    
                    <!-- Catégorie -->
                    <div>
                        <label for="category" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Catégorie</label>
                        <div class="relative">
                            <i class="fas fa-tags absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select name="category" id="category" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Toutes les catégories</option>
                                <option value="service" {{ request('category') == 'service' ? 'selected' : '' }}>Service</option>
                                <option value="product" {{ request('category') == 'product' ? 'selected' : '' }}>Produit</option>
                                <option value="job" {{ request('category') == 'job' ? 'selected' : '' }}>Emploi</option>
                                <option value="event" {{ request('category') == 'event' ? 'selected' : '' }}>Événement</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Statut -->
                    <div>
                        <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Statut</label>
                        <div class="relative">
                            <i class="fas fa-info-circle absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select name="status" id="status" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Tous</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expirée</option>
                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspendue</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Auteur -->
                    <div>
                        <label for="author" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Auteur</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="author" id="author" value="{{ request('author') }}" placeholder="Nom de l'auteur..." class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
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
                                <input type="number" name="price_min" value="{{ request('price_min') }}" placeholder="Prix min" min="0" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                            </div>
                            <div class="relative flex-1">
                                <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="number" name="price_max" value="{{ request('price_max') }}" placeholder="Prix max" min="0" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Date de publication -->
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Date de publication</label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <i class="fas fa-calendar absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                            </div>
                            <div class="relative flex-1">
                                <i class="fas fa-calendar absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-4 sm:pt-6 border-t-2 border-blue-200">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-search mr-2"></i>Appliquer les filtres
                    </button>
                    
                    <button type="button" onclick="clearFilters()" class="flex-1 bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-times mr-2"></i>Effacer tout
                    </button>
                    
                    @if(request('title') || request('category') || request('status') || request('author') || request('price_min') || request('price_max') || request('date_from') || request('date_to'))
                        <a href="{{ route('administrateur.announcements.index') }}" class="bg-white hover:bg-gray-50 text-blue-600 border border-blue-200 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                            <i class="fas fa-undo mr-2"></i>Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

<!-- Announcements Cards -->
<div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6">
    <div class="flex justify-between items-center mb-6">
        <div class="text-xl font-bold text-blue-800">Liste des annonces ({{ $announcements->total() ?? 0 }})</div>
        <div class="flex gap-4 items-center">
            <select onchange="changePerPage(this.value)" class="px-3 py-2 border border-blue-200 rounded-lg text-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 par page</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 par page</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 par page</option>
            </select>
            

        </div>
    </div>
    
    <div class="space-y-4">
        @forelse($announcements ?? [] as $announcement)
            <div class="bg-white border border-blue-200 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 p-3 sm:p-4 lg:p-6">
                <!-- Mobile Layout -->
                <div class="block sm:hidden">
                    <div class="flex items-start space-x-3 mb-3">
                        <input type="checkbox" name="selected_announcements[]" value="{{ $announcement->id }}" class="announcement-checkbox w-4 h-4 text-blue-600 border-blue-300 rounded focus:ring-blue-500 mt-1">
                        
                        <!-- Vignette mobile -->
                        <div class="flex-shrink-0">
                            @if($announcement->image)
                                <img src="{{ asset('storage/' . $announcement->image) }}" alt="{{ $announcement->title }}" class="w-12 h-12 rounded-lg object-cover border border-blue-200">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-sm">
                                    {{ substr($announcement->title, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-blue-900 mb-1 line-clamp-2">{{ $announcement->title }}</h3>
                            <div class="flex flex-wrap gap-1 mb-2">
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                    {{ ucfirst($announcement->category ?? 'Non défini') }}
                                </span>
                                @if($announcement->status == 'active')
                                    <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                        <i class="fas fa-check-circle mr-1"></i>Active
                                    </span>
                                @elseif($announcement->status == 'pending')
                                    <span class="px-2 py-0.5 bg-orange-100 text-orange-800 rounded-full text-xs font-medium">
                                        <i class="fas fa-clock mr-1"></i>En attente
                                    </span>
                                @elseif($announcement->status == 'expired')
                                    <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                        <i class="fas fa-times-circle mr-1"></i>Expirée
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">
                                        <i class="fas fa-pause mr-1"></i>Suspendue
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-xs text-gray-600 mb-2 line-clamp-2">{{ Str::limit($announcement->description, 80) }}</p>
                    
                    <!-- Métriques mobiles -->
                    <div class="flex flex-wrap gap-2 text-xs text-gray-500 mb-3">
                        @if($announcement->price)
                            <div class="flex items-center gap-1">
                                <i class="fas fa-euro-sign text-blue-500"></i>
                                <span>{{ $announcement->price }}€</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-1">
                            <i class="fas fa-eye text-blue-500"></i>
                            <span>{{ $announcement->views_count ?? 0 }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <i class="fas fa-heart text-blue-500"></i>
                            <span>{{ $announcement->likes_count ?? 0 }}</span>
                        </div>
                    </div>
                    
                    <!-- Actions mobiles -->
                    <div class="flex justify-end gap-1">
                        <a href="{{ route('administrateur.announcements.show', $announcement->id) }}" class="bg-blue-100 hover:bg-blue-200 text-blue-800 p-1.5 rounded-lg transition duration-200" title="Voir">
                            <i class="fas fa-eye text-xs"></i>
                        </a>
                        <a href="{{ route('administrateur.announcements.edit', $announcement->id) }}" class="bg-yellow-100 hover:bg-yellow-200 text-yellow-800 p-1.5 rounded-lg transition duration-200" title="Modifier">
                            <i class="fas fa-edit text-xs"></i>
                        </a>
                        @if($announcement->status == 'pending')
                            <button onclick="approveAnnouncement({{ $announcement->id }})" class="bg-green-100 hover:bg-green-200 text-green-800 p-1.5 rounded-lg transition duration-200" title="Approuver">
                                <i class="fas fa-check text-xs"></i>
                            </button>
                        @endif
                        <button onclick="deleteAnnouncement({{ $announcement->id }})" class="bg-red-100 hover:bg-red-200 text-red-800 p-1.5 rounded-lg transition duration-200" title="Supprimer">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Desktop Layout -->
                <div class="hidden sm:flex items-center justify-between">
                    <!-- Zone gauche: Media + Infos clés -->
                    <div class="flex items-center space-x-4 flex-1">
                        <input type="checkbox" name="selected_announcements[]" value="{{ $announcement->id }}" class="announcement-checkbox w-4 h-4 text-blue-600 border-blue-300 rounded focus:ring-blue-500">
                        
                        <!-- Vignette de l'annonce -->
                        <div class="flex-shrink-0">
                            @if($announcement->image)
                                <img src="{{ asset('storage/' . $announcement->image) }}" alt="{{ $announcement->title }}" class="w-16 h-16 rounded-lg object-cover border border-blue-200">
                            @else
                                <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-xl">
                                    {{ substr($announcement->title, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        
                        <!-- Infos principales -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-blue-900 truncate">{{ $announcement->title }}</h3>
                                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                            {{ ucfirst($announcement->category ?? 'Non défini') }}
                                        </span>
                                        @if($announcement->status == 'active')
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                                <i class="fas fa-check-circle mr-1"></i>Active
                                            </span>
                                        @elseif($announcement->status == 'pending')
                                            <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-medium">
                                                <i class="fas fa-clock mr-1"></i>En attente
                                            </span>
                                        @elseif($announcement->status == 'expired')
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                                <i class="fas fa-times-circle mr-1"></i>Expirée
                                            </span>
                                        @else
                                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">
                                                <i class="fas fa-pause mr-1"></i>Suspendue
                                            </span>
                                        @endif
                                        @if($announcement->is_featured)
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">
                                                <i class="fas fa-star mr-1"></i>Mise en avant
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ Str::limit($announcement->description, 120) }}</p>
                            
                            <!-- Métriques -->
                            <div class="flex items-center gap-4 text-xs text-gray-500 flex-wrap">
                                @if($announcement->price)
                                    <div class="flex items-center gap-1">
                                        <i class="fas fa-euro-sign text-blue-500"></i>
                                        <span>{{ $announcement->price }}€</span>
                                    </div>
                                @endif
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-eye text-blue-500"></i>
                                    <span>{{ $announcement->views_count ?? 0 }} vues</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-heart text-blue-500"></i>
                                    <span>{{ $announcement->likes_count ?? 0 }} likes</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-user text-blue-500"></i>
                                    <span>{{ $announcement->author->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-calendar text-blue-500"></i>
                                    <span>{{ $announcement->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Zone droite: Actions -->
                    <div class="flex items-center gap-2 ml-4">
                        <a href="{{ route('administrateur.announcements.show', $announcement->id) }}" class="bg-blue-100 hover:bg-blue-200 text-blue-800 p-2 rounded-lg transition duration-200" title="Voir les détails">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('administrateur.announcements.edit', $announcement->id) }}" class="bg-yellow-100 hover:bg-yellow-200 text-yellow-800 p-2 rounded-lg transition duration-200" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if($announcement->status == 'pending')
                            <button onclick="approveAnnouncement({{ $announcement->id }})" class="bg-green-100 hover:bg-green-200 text-green-800 p-2 rounded-lg transition duration-200" title="Approuver">
                                <i class="fas fa-check"></i>
                            </button>
                        @endif
                        <button onclick="deleteAnnouncement({{ $announcement->id }})" class="bg-red-100 hover:bg-red-200 text-red-800 p-2 rounded-lg transition duration-200" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <div class="bg-gray-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bullhorn text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Aucune annonce trouvée</h3>
                <p class="text-gray-500 mb-6">Il n'y a actuellement aucune annonce correspondant à vos critères.</p>
                <a href="{{ route('administrateur.announcements.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="fas fa-plus mr-2"></i>
                    Ajouter une annonce
                </a>
            </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    @if(isset($announcements) && $announcements->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $announcements->appends(request()->query())->links() }}
        </div>
    @endif
</div>

</div>

<script>
function toggleFilters() {
    const panel = document.getElementById('filtersPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

function clearFilters() {
    document.querySelectorAll('#filtersPanel input, #filtersPanel select').forEach(element => {
        if (element.type === 'checkbox' || element.type === 'radio') {
            element.checked = false;
        } else {
            element.value = '';
        }
    });
}

function changePerPage(value) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', value);
    window.location = url;
}



function approveAnnouncement(id) {
    if (confirm('Êtes-vous sûr de vouloir approuver cette annonce ?')) {
        // Logique d'approbation à implémenter
        console.log('Approbation de l\'annonce ID:', id);
    }
}

function deleteAnnouncement(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?')) {
        // Logique de suppression à implémenter
        console.log('Suppression de l\'annonce ID:', id);
    }
}
</script>
@endsection