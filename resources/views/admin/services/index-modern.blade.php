@extends('layouts.admin-modern')

@section('title', 'Gestion des Services - Administration')

@section('content')
<div class="bg-blue-50">
    <!-- Bannière d'en-tête -->
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-blue-900 mb-2 leading-tight">
                    Gestion des Services
                </h1>
                <p class="text-base sm:text-lg text-blue-700 max-w-2xl mx-auto">
                    Gérez tous les services publiés sur la plateforme TaPrestation.
                </p>
            </div>
            
            <!-- Actions Header -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                <div class="flex flex-wrap gap-2 sm:gap-3">
                    <a href="{{ route('administrateur.services.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-plus mr-2"></i>
                        Nouveau Service
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
                        <div class="text-xs sm:text-sm font-medium text-blue-600 uppercase tracking-wide">Total Services</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ $services->total() ?? 0 }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+8% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-briefcase text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-green-600 uppercase tracking-wide">Services Actifs</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ $services->where('status', 'active')->count() ?? 0 }}</div>
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
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-orange-600 uppercase tracking-wide">En Attente</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ $services->where('status', 'pending')->count() ?? 0 }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-red-600">
                            <i class="fas fa-arrow-down mr-1"></i>
                            <span>-3% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-clock text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-blue-600 uppercase tracking-wide">Revenus Moyens</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">€€€</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+5% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-euro-sign text-blue-600 text-xl"></i>
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
                    <p class="text-sm sm:text-base lg:text-lg text-blue-700">Affinez votre recherche pour trouver le service parfait</p>
                </div>
                <button class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base" onclick="clearFilters()">
                    <i class="fas fa-times mr-2"></i>
                    Effacer tout
                </button>
            </div>
            
            <form action="{{ route('administrateur.services.index') }}" method="GET" class="space-y-4 sm:space-y-6">
                <!-- Première ligne de filtres -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <!-- Titre du service -->
                    <div>
                        <label for="title" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Titre du service</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="title" id="title" value="{{ request('title') }}" placeholder="Rechercher par titre..." class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                        </div>
                    </div>
                    
                    <!-- Prestataire -->
                    <div>
                        <label for="prestataire" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Prestataire</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="prestataire" id="prestataire" value="{{ request('prestataire') }}" placeholder="Nom du prestataire..." class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                        </div>
                    </div>
                    
                    <!-- Catégorie -->
                    <div>
                        <label for="category" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Catégorie</label>
                        <div class="relative">
                            <i class="fas fa-tags absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select name="category" id="category" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
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
                            <select name="status" id="status" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Tous</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
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
                                <input type="number" name="price_min" value="{{ request('price_min') }}" placeholder="Prix min" min="0" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                            </div>
                            <div class="relative flex-1">
                                <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="number" name="price_max" value="{{ request('price_max') }}" placeholder="Prix max" min="0" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
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
                    
                    @if(request('title') || request('prestataire') || request('category') || request('status') || request('price_min') || request('price_max'))
                        <a href="{{ route('administrateur.services.index') }}" class="bg-white hover:bg-gray-50 text-blue-600 border border-blue-200 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                            <i class="fas fa-undo mr-2"></i>Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

<!-- Services Cards -->
<div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6">
    <div class="flex justify-between items-center mb-6">
        <div class="text-xl font-bold text-blue-800">Liste des services ({{ $services->total() ?? 0 }})</div>
        <div class="flex gap-4 items-center">
            <select onchange="changePerPage(this.value)" class="px-3 py-2 border border-blue-200 rounded-lg text-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 par page</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 par page</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 par page</option>
            </select>
            

        </div>
    </div>
    
    <div class="space-y-4">
        @forelse($services ?? [] as $service)
            <div class="bg-white border border-blue-200 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 p-6">
                <div class="flex items-center justify-between">
                    <!-- Zone gauche: Media + Infos clés -->
                    <div class="flex items-center space-x-4 flex-1">
                        <input type="checkbox" name="selected_services[]" value="{{ $service->id }}" class="service-checkbox w-4 h-4 text-blue-600 border-blue-300 rounded focus:ring-blue-500">
                        
                        <!-- Vignette du service -->
                        <div class="flex-shrink-0">
                            @if($service->image)
                                <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->title }}" class="w-16 h-16 rounded-lg object-cover border border-blue-200">
                            @else
                                <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-xl">
                                    {{ substr($service->title, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        
                        <!-- Infos principales -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-blue-900 truncate">{{ $service->title }}</h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        @if($service->categories->first())
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                                {{ $service->categories->first()->name }}
                                            </span>
                                        @else
                                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">Non catégorisé</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ Str::limit($service->description, 120) }}</p>
                            
                            <!-- Métriques -->
                            <div class="flex items-center gap-4 text-xs text-gray-500">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-eye text-blue-500"></i>
                                    <span>{{ $service->views_count ?? 0 }} vues</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-envelope text-blue-500"></i>
                                    <span>{{ $service->requests_count ?? 0 }} demandes</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-calendar-check text-blue-500"></i>
                                    <span>{{ $service->bookings_count ?? 0 }} réservations</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    @php
                                        $rating = $service->average_rating ?? 0;
                                        $fullStars = floor($rating);
                                        $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                    @endphp
                                    <div class="flex">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $fullStars)
                                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                            @elseif($i == $fullStars + 1 && $hasHalfStar)
                                                <i class="fas fa-star-half-alt text-yellow-400 text-xs"></i>
                                            @else
                                                <i class="far fa-star text-gray-300 text-xs"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="ml-1">{{ number_format($rating, 1) }} ({{ $service->reviews_count ?? 0 }})</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Zone centrale: Prix & Statut -->
                    <div class="flex flex-col items-center px-6 border-l border-r border-blue-100">
                        <div class="text-center mb-3">
                            <div class="text-2xl font-bold text-blue-900">€{{ number_format($service->price, 0) }}</div>
                            @if($service->price_type)
                                <div class="text-xs text-gray-500">/ {{ $service->price_type }}</div>
                            @endif
                        </div>
                        
                        <div class="mb-2">
                            @switch($service->status)
                                @case('active')
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium flex items-center">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Actif
                                    </span>
                                    @break
                                @case('pending')
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium flex items-center">
                                        <i class="fas fa-clock mr-1"></i>
                                        En révision
                                    </span>
                                    @break
                                @case('inactive')
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium flex items-center">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Inactif
                                    </span>
                                    @break
                                @default
                                    <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">Inconnu</span>
                            @endswitch
                        </div>
                        
                        <div class="text-xs text-gray-500 text-center">
                            Mis à jour le<br>{{ $service->updated_at->format('d/m/Y') }}
                        </div>
                    </div>
                    
                    <!-- Zone droite: Actions rapides -->
                    <div class="flex flex-col gap-2 pl-6">
                        <div class="flex gap-2">
                            <a href="{{ route('administrateur.services.show', $service->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs font-medium transition duration-200 flex items-center" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            <a href="{{ route('administrateur.services.edit', $service->id) }}" class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-2 rounded-lg text-xs font-medium transition duration-200 flex items-center" title="Éditer">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <button onclick="duplicateService({{ $service->id }})" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-xs font-medium transition duration-200 flex items-center" title="Dupliquer">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        
                        <div class="flex gap-2">
                            @if($service->status === 'active')
                                <button onclick="deactivateService({{ $service->id }})" class="bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-3 py-2 rounded-lg text-xs font-medium transition duration-200 flex items-center" title="Désactiver">
                                    <i class="fas fa-pause"></i>
                                </button>
                            @else
                                <button onclick="activateService({{ $service->id }})" class="bg-green-100 hover:bg-green-200 text-green-800 px-3 py-2 rounded-lg text-xs font-medium transition duration-200 flex items-center" title="Activer">
                                    <i class="fas fa-play"></i>
                                </button>
                            @endif
                            
                            <button onclick="archiveService({{ $service->id }})" class="bg-orange-100 hover:bg-orange-200 text-orange-800 px-3 py-2 rounded-lg text-xs font-medium transition duration-200 flex items-center" title="Archiver">
                                <i class="fas fa-archive"></i>
                            </button>
                            
                            <button onclick="deleteService({{ $service->id }})" class="bg-red-100 hover:bg-red-200 text-red-800 px-3 py-2 rounded-lg text-xs font-medium transition duration-200 flex items-center" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        
                        <!-- Actions secondaires -->
                        <div class="flex gap-2 mt-2 pt-2 border-t border-blue-100">
                            <a href="{{ route('services.show', $service->id) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-xs flex items-center" title="Aperçu côté client">
                                <i class="fas fa-external-link-alt mr-1"></i>
                                Aperçu
                            </a>
                            <button onclick="copyServiceLink({{ $service->id }})" class="text-blue-600 hover:text-blue-800 text-xs flex items-center" title="Copier le lien">
                                <i class="fas fa-link mr-1"></i>
                                Copier
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <i class="fas fa-briefcase text-6xl text-blue-200 mb-4"></i>
                <div class="text-xl font-semibold text-blue-800 mb-2">Aucun service trouvé</div>
                <div class="text-blue-600">Essayez de modifier vos critères de recherche</div>
            </div>
        @endforelse
    </div>
    
    @if($services && $services->hasPages())
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 mt-6 border-t-2 border-blue-200">
            <div class="text-sm text-blue-700 font-medium">
                Affichage de {{ $services->firstItem() }} à {{ $services->lastItem() }} sur {{ $services->total() }} résultats
            </div>
            <div class="flex justify-center">
                {{ $services->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Bulk Actions -->
<div id="bulkActions" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-white rounded-xl shadow-2xl border border-blue-200 p-4 z-50" style="display: none;">
    <div class="flex flex-wrap items-center justify-center gap-3">
        <span class="text-blue-800 font-semibold text-sm">Actions groupées :</span>
        <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center" onclick="bulkApprove()">
            <i class="fas fa-check mr-2"></i>
            Approuver
        </button>
        <button class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center" onclick="bulkDeactivate()">
            <i class="fas fa-pause mr-2"></i>
            Désactiver
        </button>
        <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center" onclick="bulkDelete()">
            <i class="fas fa-trash mr-2"></i>
            Supprimer
        </button>
        <button class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-4 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center" onclick="clearSelection()">
            <i class="fas fa-times mr-2"></i>
            Annuler
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Toggle filters panel
function toggleFilters() {
    const panel = document.getElementById('filtersPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

// Clear filters
function clearFilters() {
    window.location.href = '{{ route("administrateur.services.index") }}';
}

// Change items per page
function changePerPage(value) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', value);
    window.location.href = url.toString();
}

// Toggle all checkboxes
function toggleAllCheckboxes(source) {
    const checkboxes = document.querySelectorAll('.service-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = source.checked;
    });
    updateBulkActions();
}

// Update bulk actions visibility
function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.service-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    
    if (checkedBoxes.length > 0) {
        bulkActions.style.display = 'block';
    } else {
        bulkActions.style.display = 'none';
    }
}

// Add event listeners to checkboxes
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.service-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
});

// Clear selection
function clearSelection() {
    const checkboxes = document.querySelectorAll('.service-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updateBulkActions();
}

// Service actions
function approveService(serviceId) {
    if (confirm('Êtes-vous sûr de vouloir approuver ce service ?')) {
        // Implement approve service logic
        console.log('Approving service:', serviceId);
    }
}

function activateService(serviceId) {
    if (confirm('Êtes-vous sûr de vouloir activer ce service ?')) {
        // Implement activate service logic
        console.log('Activating service:', serviceId);
    }
}

function deactivateService(serviceId) {
    if (confirm('Êtes-vous sûr de vouloir désactiver ce service ?')) {
        // Implement deactivate service logic
        console.log('Deactivating service:', serviceId);
    }
}

function deleteService(serviceId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce service ? Cette action est irréversible.')) {
        // Implement delete service logic
        console.log('Deleting service:', serviceId);
    }
}

// Bulk actions
function bulkApprove() {
    const checkedBoxes = document.querySelectorAll('.service-checkbox:checked');
    if (checkedBoxes.length === 0) return;
    
    if (confirm(`Êtes-vous sûr de vouloir approuver ${checkedBoxes.length} service(s) ?`)) {
        // Implement bulk approve logic
        console.log('Bulk approving services');
    }
}

function bulkDeactivate() {
    const checkedBoxes = document.querySelectorAll('.service-checkbox:checked');
    if (checkedBoxes.length === 0) return;
    
    if (confirm(`Êtes-vous sûr de vouloir désactiver ${checkedBoxes.length} service(s) ?`)) {
        // Implement bulk deactivate logic
        console.log('Bulk deactivating services');
    }
}

function bulkDelete() {
    const checkedBoxes = document.querySelectorAll('.service-checkbox:checked');
    if (checkedBoxes.length === 0) return;
    
    if (confirm(`Êtes-vous sûr de vouloir supprimer ${checkedBoxes.length} service(s) ? Cette action est irréversible.`)) {
        // Implement bulk delete logic
        console.log('Bulk deleting services');
    }
}



function duplicateService(serviceId) {
    if (confirm('Êtes-vous sûr de vouloir dupliquer ce service ?')) {
        fetch(`/administrateur/services/${serviceId}/duplicate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la duplication du service.');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la duplication du service.');
        });
    }
}

function archiveService(serviceId) {
    if (confirm('Êtes-vous sûr de vouloir archiver ce service ?')) {
        fetch(`/administrateur/services/${serviceId}/archive`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de l\'archivage du service.');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de l\'archivage du service.');
        });
    }
}

function copyServiceLink(serviceId) {
    const serviceUrl = `{{ url('/services') }}/${serviceId}`;
    
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(serviceUrl).then(() => {
            // Créer une notification temporaire
            const notification = document.createElement('div');
            notification.textContent = 'Lien copié dans le presse-papiers !';
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #10b981;
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                z-index: 10000;
                font-weight: 500;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 3000);
        }).catch(err => {
            console.error('Erreur lors de la copie:', err);
            fallbackCopyTextToClipboard(serviceUrl);
        });
    } else {
        fallbackCopyTextToClipboard(serviceUrl);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.top = '0';
    textArea.style.left = '0';
    textArea.style.position = 'fixed';
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            alert('Lien copié dans le presse-papiers !');
        } else {
            alert('Impossible de copier le lien.');
        }
    } catch (err) {
        console.error('Erreur lors de la copie:', err);
        alert('Impossible de copier le lien.');
    }
    
    document.body.removeChild(textArea);
}


</script>
@endpush