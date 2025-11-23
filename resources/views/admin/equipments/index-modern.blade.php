@extends('layouts.admin-modern')

@section('title', 'Gestion des Équipements - Administration')

@section('content')
<div class="bg-green-50">
    <!-- Bannière d'en-tête -->
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-green-900 mb-2 leading-tight">
                    Gestion des Équipements
                </h1>
                <p class="text-base sm:text-lg text-green-700 max-w-2xl mx-auto">
                    Gérez tous les équipements disponibles sur la plateforme TaPrestation.
                </p>
            </div>
            
            <!-- Actions Header -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                <div class="flex flex-wrap gap-2 sm:gap-3">
                    <button disabled class="bg-gray-400 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg flex items-center justify-center text-sm sm:text-base cursor-not-allowed opacity-60" title="Création d'équipements non disponible">
                        <i class="fas fa-plus mr-2"></i>
                        Nouvel Équipement
                    </button>
                    <button class="bg-green-100 hover:bg-green-200 text-green-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base" onclick="toggleFilters()">
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
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-green-600 uppercase tracking-wide">Total Équipements</div>
                        <div class="text-2xl sm:text-3xl font-bold text-green-900 mt-1">{{ $equipment->total() ?? 0 }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+5% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-wrench text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-green-600 uppercase tracking-wide">Disponibles</div>
                        <div class="text-2xl sm:text-3xl font-bold text-green-900 mt-1">{{ $equipment->where('status', 'available')->count() ?? 0 }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+8% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-orange-600 uppercase tracking-wide">En Maintenance</div>
                        <div class="text-2xl sm:text-3xl font-bold text-green-900 mt-1">{{ $equipment->where('status', 'maintenance')->count() ?? 0 }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-red-600">
                            <i class="fas fa-arrow-down mr-1"></i>
                            <span>-2% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-tools text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-green-600 uppercase tracking-wide">Valeur Totale</div>
                        <div class="text-2xl sm:text-3xl font-bold text-green-900 mt-1">€€€</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+3% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-euro-sign text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <div id="filtersPanel" class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6 mb-6 sm:mb-8" style="display: none;">
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-center sm:text-left">
                    <h3 class="text-xl sm:text-2xl font-bold text-green-800 mb-1 sm:mb-2">Filtres de recherche</h3>
                    <p class="text-sm sm:text-base lg:text-lg text-green-700">Affinez votre recherche pour trouver l'équipement parfait</p>
                </div>
                <button class="bg-green-100 hover:bg-green-200 text-green-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base" onclick="clearFilters()">
                    <i class="fas fa-times mr-2"></i>
                    Effacer tout
                </button>
            </div>
            
            <form action="{{ route('admin.equipments.index') }}" method="GET" class="space-y-4 sm:space-y-6">
                <!-- Première ligne de filtres -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <!-- Nom de l'équipement -->
                    <div>
                        <label for="name" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Nom de l'équipement</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="name" id="name" value="{{ request('name') }}" placeholder="Rechercher par nom..." class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 text-sm sm:text-base">
                        </div>
                    </div>
                    
                    <!-- Type -->
                    <div>
                        <label for="type" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Type</label>
                        <div class="relative">
                            <i class="fas fa-tags absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select name="type" id="type" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Tous les types</option>
                                <option value="tool" {{ request('type') == 'tool' ? 'selected' : '' }}>Outil</option>
                                <option value="machine" {{ request('type') == 'machine' ? 'selected' : '' }}>Machine</option>
                                <option value="vehicle" {{ request('type') == 'vehicle' ? 'selected' : '' }}>Véhicule</option>
                                <option value="electronic" {{ request('type') == 'electronic' ? 'selected' : '' }}>Électronique</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Statut -->
                    <div>
                        <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Statut</label>
                        <div class="relative">
                            <i class="fas fa-info-circle absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select name="status" id="status" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Tous</option>
                                <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Disponible</option>
                                <option value="rented" {{ request('status') == 'rented' ? 'selected' : '' }}>Loué</option>
                                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>En maintenance</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Propriétaire -->
                    <div>
                        <label for="owner" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Propriétaire</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="owner" id="owner" value="{{ request('owner') }}" placeholder="Nom du propriétaire..." class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 text-sm sm:text-base">
                        </div>
                    </div>
                </div>
                
                <!-- Deuxième ligne de filtres -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                    <!-- Prix de location -->
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Prix de location (par jour)</label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="number" name="price_min" value="{{ request('price_min') }}" placeholder="Prix min" min="0" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 text-sm sm:text-base">
                            </div>
                            <div class="relative flex-1">
                                <i class="fas fa-euro-sign absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="number" name="price_max" value="{{ request('price_max') }}" placeholder="Prix max" min="0" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 text-sm sm:text-base">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-4 sm:pt-6 border-t-2 border-green-200">
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-search mr-2"></i>Appliquer les filtres
                    </button>
                    
                    <button type="button" onclick="clearFilters()" class="flex-1 bg-green-100 hover:bg-green-200 text-green-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-times mr-2"></i>Effacer tout
                    </button>
                    
                    @if(request('name') || request('type') || request('status') || request('owner') || request('price_min') || request('price_max'))
                        <a href="{{ route('admin.equipments.index') }}" class="bg-white hover:bg-gray-50 text-green-600 border border-green-200 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                            <i class="fas fa-undo mr-2"></i>Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

<!-- Equipments Cards -->
<div class="bg-white rounded-xl shadow-lg border border-green-200 p-6">
    <div class="flex justify-between items-center mb-6">
        <div class="text-xl font-bold text-green-800">Liste des équipements ({{ $equipment->total() ?? 0 }})</div>
        <div class="flex gap-4 items-center">
            <select onchange="changePerPage(this.value)" class="px-3 py-2 border border-green-200 rounded-lg text-sm focus:border-green-500 focus:ring focus:ring-green-200">
                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 par page</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 par page</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 par page</option>
            </select>
            

        </div>
    </div>
    
    <div class="space-y-4">
        @forelse($equipment ?? [] as $equipmentItem)
            <div class="bg-white border border-green-200 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 p-6">
                <div class="flex items-center justify-between">
                    <!-- Zone gauche: Media + Infos clés -->
                    <div class="flex items-center space-x-4 flex-1">
                        <input type="checkbox" name="selected_equipments[]" value="{{ $equipmentItem->id }}" class="equipment-checkbox w-4 h-4 text-green-600 border-green-300 rounded focus:ring-green-500">
                        
                        <!-- Vignette de l'équipement -->
                        <div class="flex-shrink-0">
                            @if($equipmentItem->image)
                                <img src="{{ asset('storage/' . $equipmentItem->image) }}" alt="{{ $equipmentItem->name }}" class="w-16 h-16 rounded-lg object-cover border border-green-200">
                            @else
                                <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white font-bold text-xl">
                                    {{ substr($equipmentItem->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        
                        <!-- Infos principales -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-green-900 truncate">{{ $equipmentItem->name }}</h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                            {{ ucfirst($equipmentItem->type ?? 'Non défini') }}
                                        </span>
                                        @if($equipmentItem->status == 'available')
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                                <i class="fas fa-check-circle mr-1"></i>Disponible
                                            </span>
                                        @elseif($equipmentItem->status == 'rented')
                                            <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-medium">
                                                <i class="fas fa-clock mr-1"></i>Loué
                                            </span>
                                        @elseif($equipmentItem->status == 'maintenance')
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                                <i class="fas fa-tools mr-1"></i>Maintenance
                                            </span>
                                        @else
                                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">
                                                <i class="fas fa-pause mr-1"></i>Inactif
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ Str::limit($equipmentItem->description, 120) }}</p>
                            
                            <!-- Métriques -->
                            <div class="flex items-center gap-4 text-xs text-gray-500">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-euro-sign text-green-500"></i>
                                    <span>{{ $equipmentItem->daily_price ?? 0 }}€/jour</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-calendar-check text-green-500"></i>
                                    <span>{{ $equipmentItem->rentals_count ?? 0 }} locations</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-user text-green-500"></i>
                                    <span>{{ $equipmentItem->owner->name ?? 'N/A' }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-calendar text-green-500"></i>
                                    <span>Ajouté {{ $equipmentItem->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Zone droite: Actions -->
                    <div class="flex items-center gap-2 ml-4">
                        <a href="{{ route('admin.equipments.show', $equipmentItem->id) }}" class="bg-green-100 hover:bg-green-200 text-green-800 p-2 rounded-lg transition duration-200" title="Voir les détails">
                            <i class="fas fa-eye"></i>
                        <a href="{{ route('admin.equipments.edit', $equipmentItem->id) }}" class="bg-green-100 hover:bg-green-200 text-green-800 p-2 rounded-lg transition duration-200" title="Modifier l'équipement">
                             <i class="fas fa-edit"></i>
                         </a>
                        <button disabled class="bg-gray-100 text-gray-400 p-2 rounded-lg cursor-not-allowed" title="Suppression non disponible">
                             <i class="fas fa-trash"></i>
                         </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <div class="bg-gray-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-wrench text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Aucun équipement trouvé</h3>
                <p class="text-gray-500 mb-6">Il n'y a actuellement aucun équipement correspondant à vos critères.</p>
                <button disabled class="bg-gray-400 text-white font-bold py-3 px-6 rounded-lg cursor-not-allowed opacity-60" title="Création d'équipements non disponible">
                    <i class="fas fa-plus mr-2"></i>
                    Ajouter un équipement
                </button>
            </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    @if(isset($equipment) && $equipment->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $equipment->appends(request()->query())->links() }}
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



function deleteEquipment(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet équipement ?')) {
        // Logique de suppression à implémenter
        console.log('Suppression de l\'équipement ID:', id);
    }
}
</script>
@endsection