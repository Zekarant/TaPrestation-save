@extends('layouts.app')

@section('title', 'Mes demandes de location')

@section('content')
<style>
:root {
    --rq-primary: #059669;
    --rq-primary-soft: #ecfdf5;
    --rq-border: #d1fae5;
    --rq-text: #0f172a;
}

/* Enhanced toggle button */
#toggleFilters {
    background-color: #10b981;
    color: white;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
}

#toggleFilters:hover {
    background-color: #059669;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(16, 185, 129, 0.4);
}

/* Pagination enhancement */
.pagination {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}

.pagination a,
.pagination span {
    padding: 0.5rem 1rem;
    margin: 0 0.25rem;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.pagination a:hover {
    background-color: #dcfce7;
    color: #059669;
}

.pagination .active {
    background-color: #10b981;
    color: white;
}

/* Empty state enhancement */
.empty-state {
    background-color: #f0fdf4;
    border-radius: 1rem;
    border: 2px dashed #86efac;
    padding: 2rem;
    text-align: center;
}

/* Requests cards */
.request-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 1rem;
}

@media (min-width: 768px) {
    .request-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
    }
}

@media (min-width: 1280px) {
    .request-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.5rem;
    }
}

.equipment-card {
    position: relative;
    border: 1px solid var(--rq-border);
    border-radius: 1rem;
    overflow: hidden;
    background: linear-gradient(180deg, #ffffff 0%, #f8fffb 100%);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.equipment-card:hover {
    transform: translateY(-4px);
    border-color: #86efac;
    box-shadow: 0 16px 30px rgba(5, 150, 105, 0.18);
}

.equipment-media {
    position: relative;
    height: 176px;
    background: linear-gradient(135deg, #d1fae5 0%, #bbf7d0 45%, #dcfce7 100%);
}

.equipment-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.equipment-placeholder {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-badge {
    border-radius: 9999px;
    padding: 0.32rem 0.72rem;
    font-weight: 700;
    font-size: 0.74rem;
    line-height: 1;
    border: 1px solid transparent;
}

.status-overlay {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    backdrop-filter: blur(3px);
    box-shadow: 0 4px 8px rgba(15, 23, 42, 0.12);
}

.status-pending {
    background-color: #fef3c7;
    color: #92400e;
    border-color: #fcd34d;
}

.status-accepted {
    background-color: #dcfce7;
    color: #166534;
    border-color: #86efac;
}

.status-rejected {
    background-color: #fee2e2;
    color: #991b1b;
    border-color: #fca5a5;
}

.request-id {
    font-size: 0.72rem;
    letter-spacing: 0.03em;
    font-weight: 700;
    color: #047857;
    text-transform: uppercase;
}

.provider-line {
    color: #475569;
    font-size: 0.92rem;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.request-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.meta-pill {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #065f46;
    border-radius: 9999px;
    padding: 0.32rem 0.7rem;
    font-size: 0.78rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.meta-pill.amount-pill {
    background: #ecfeff;
    border-color: #bae6fd;
    color: #0c4a6e;
}

.action-button {
    border-radius: 0.7rem;
    font-weight: 700;
    transition: all 0.2s ease;
    box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08);
}

.action-button:hover {
    transform: translateY(-1px);
}

.action-button-primary {
    background: linear-gradient(135deg, #16a34a 0%, #059669 100%);
    color: white;
}

.action-button-primary:hover {
    background: linear-gradient(135deg, #15803d 0%, #047857 100%);
}

.action-button-danger {
    color: #b91c1c;
    background: #fef2f2;
    border: 1px solid #fecaca;
}

.action-button-danger:hover {
    background: #fee2e2;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<div class="bg-green-50">
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6 lg:p-8 mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                <div class="text-center sm:text-left">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-green-900 mb-2">
                        <span class="hidden sm:inline">Mes demandes de location</span>
                        <span class="sm:hidden">Mes demandes</span>
                    </h1>
                    <p class="text-sm sm:text-base lg:text-lg text-green-700">
                        <span class="hidden sm:inline">Gérez et consultez l'ensemble de vos demandes de location de matériel</span>
                        <span class="sm:hidden">Gérez vos demandes de matériel</span>
                    </p>
                </div>
                <div class="flex justify-center sm:justify-end">
                    <a href="{{ route('equipment.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 sm:px-6 py-3 rounded-lg transition duration-200 shadow-lg hover:shadow-xl font-bold text-sm sm:text-base lg:text-lg flex items-center justify-center w-full sm:w-auto" style="min-height: 44px;">
                        <i class="fas fa-plus mr-2 sm:mr-3"></i>
                        <span class="hidden sm:inline">Nouvelle demande</span>
                        <span class="sm:hidden">Nouveau</span>
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 sm:mb-6 text-sm sm:text-base">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 sm:mb-6 text-sm sm:text-base">
                {{ session('error') }}
            </div>
        @endif
        
        <!-- Statistiques -->
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6 mb-6 sm:mb-8">
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-3 sm:p-4 lg:p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center">
                    <div class="p-2 sm:p-3 rounded-full bg-linear-to-br from-green-100 to-green-200 text-green-600 shadow-lg">
                        <i class="fas fa-clipboard-list text-sm sm:text-lg lg:text-xl"></i>
                    </div>
                    <div class="ml-2 sm:ml-3 lg:ml-4">
                        <p class="text-xs sm:text-sm font-semibold text-green-700">Total</p>
                        <p class="text-lg sm:text-xl lg:text-2xl font-bold text-green-900">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-3 sm:p-4 lg:p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center">
                    <div class="p-2 sm:p-3 rounded-full bg-linear-to-br from-yellow-100 to-yellow-200 text-yellow-600 shadow-lg">
                        <i class="fas fa-clock text-sm sm:text-lg lg:text-xl"></i>
                    </div>
                    <div class="ml-2 sm:ml-3 lg:ml-4">
                        <p class="text-xs sm:text-sm font-semibold text-green-700">En attente</p>
                        <p class="text-lg sm:text-xl lg:text-2xl font-bold text-green-900">{{ $stats['pending'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-3 sm:p-4 lg:p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center">
                    <div class="p-2 sm:p-3 rounded-full bg-linear-to-br from-green-100 to-green-200 text-green-600 shadow-lg">
                        <i class="fas fa-check-circle text-sm sm:text-lg lg:text-xl"></i>
                    </div>
                    <div class="ml-2 sm:ml-3 lg:ml-4">
                        <p class="text-xs sm:text-sm font-semibold text-green-700">Acceptées</p>
                        <p class="text-lg sm:text-xl lg:text-2xl font-bold text-green-900">{{ $stats['accepted'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-3 sm:p-4 lg:p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center">
                    <div class="p-2 sm:p-3 rounded-full bg-linear-to-br from-red-100 to-red-200 text-red-600 shadow-lg">
                        <i class="fas fa-times-circle text-sm sm:text-lg lg:text-xl"></i>
                    </div>
                    <div class="ml-2 sm:ml-3 lg:ml-4">
                        <p class="text-xs sm:text-sm font-semibold text-green-700">Refusées</p>
                        <p class="text-lg sm:text-xl lg:text-2xl font-bold text-green-900">{{ $stats['rejected'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filtres -->
        <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6 mb-8">
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-center sm:text-left">
                    <h3 class="text-xl sm:text-2xl font-bold text-green-800 mb-1 sm:mb-2">Filtres de recherche</h3>
                    <p class="text-sm sm:text-base lg:text-lg text-green-700">Affinez votre recherche pour trouver la demande parfaite</p>
                </div>
                <button type="button" id="toggleFilters" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                    <span id="filterButtonText">Afficher les filtres</span>
                    <i class="fas fa-chevron-down ml-2" id="filterChevron"></i>
                </button>
            </div>
            
            <form method="GET" action="{{ route('client.equipment-rental-requests.index') }}" class="space-y-4 sm:space-y-6" id="filtersForm" style="display: none;">
                <!-- Première ligne de filtres -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4">
                    <!-- Recherche -->
                    <div>
                        <label for="search" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Recherche</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Nom de l'équipement, prestataire..."
                                   class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 text-sm sm:text-base">
                        </div>
                    </div>
                    
                    <!-- Statut -->
                    <div>
                        <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Statut</label>
                        <div class="relative">
                            <i class="fas fa-filter absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select id="status" 
                                    name="status"
                                    class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Tous les statuts</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Acceptée</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Refusée</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Date de début -->
                    <div>
                        <label for="date_from" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Date de début</label>
                        <div class="relative">
                            <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="date" 
                                   id="date_from" 
                                   name="date_from" 
                                   value="{{ request('date_from') }}"
                                   class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 text-sm sm:text-base">
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
                    
                    @if(request('search') || request('status') || request('date_from'))
                        <a href="{{ route('client.equipment-rental-requests.index') }}" class="flex-1 bg-green-100 hover:bg-green-200 text-green-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                            <i class="fas fa-undo mr-2"></i>Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
            
            <!-- Indicateur de filtres actifs -->
            @if(request('search') || request('status') || request('date_from'))
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pt-4 border-t-2 border-green-200 mt-6 space-y-2 sm:space-y-0">
                    <div class="flex items-center gap-2">
                        <span class="text-xs sm:text-sm font-semibold text-green-800">Filtres actifs :</span>
                        @if(request('search'))
                            <span class="px-2 sm:px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs sm:text-sm font-bold">
                                Recherche: {{ request('search') }}
                            </span>
                        @endif
                        @if(request('status'))
                            <span class="px-2 sm:px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs sm:text-sm font-bold">
                                Statut: {{ ucfirst(request('status')) }}
                            </span>
                        @endif
                        @if(request('date_from'))
                            <span class="px-2 sm:px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs sm:text-sm font-bold">
                                Date: {{ request('date_from') }}
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Liste des demandes -->
        @if($requests->count() > 0)
            <!-- Equipment Requests Grid -->
            <div class="request-grid">
                @foreach($requests as $request)
                    <article class="equipment-card flex flex-col h-full">
                        <div class="equipment-media">
                            @if($request->equipment && $request->equipment->photos && is_array($request->equipment->photos) && count($request->equipment->photos) > 0 && is_string($request->equipment->photos[0]) && !empty($request->equipment->photos[0]))
                                <img src="{{ url('storage/' . $request->equipment->photos[0]) }}"
                                     alt="{{ $request->equipment->name }}"
                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAyNCIgaGVpZ2h0PSI1NzYiIHZpZXdCb3g9IjAgMCAxMDI0IDU3NiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAyNCIgaGVpZ2h0PSI1NzYiIGZpbGw9IiNFNUU3RUIiLz48cGF0aCBkPSJNMzAwIDM4MEw0MjAgMjYwTDUxMCAzNTBMNjYwIDIxMEw3NjAgMzEwVjQxMEgzMDBWMzgwWiIgZmlsbD0iI0QxRDVEQiIvPjxjaXJjbGUgY3g9IjQxMCIgY3k9IjIyMCIgcj0iNDAiIGZpbGw9IiNEMUQ1REIiLz48L3N2Zz4='; this.onerror=null;">
                            @else
                                <div class="equipment-placeholder">
                                    <svg class="w-12 h-12 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                </div>
                            @endif

                            <span class="status-badge status-overlay inline-flex items-center
                                @if($request->status === 'pending') status-pending
                                @elseif($request->status === 'accepted') status-accepted
                                @elseif($request->status === 'rejected') status-rejected
                                @else status-pending
                                @endif">
                                @if($request->status === 'pending')
                                    <i class="fas fa-clock mr-1"></i> En attente
                                @elseif($request->status === 'accepted')
                                    <i class="fas fa-check-circle mr-1"></i> Acceptée
                                @elseif($request->status === 'rejected')
                                    <i class="fas fa-times-circle mr-1"></i> Refusée
                                @else
                                    {{ ucfirst($request->status) }}
                                @endif
                            </span>
                        </div>

                        <div class="p-5 flex flex-col flex-grow">
                            <div class="mb-4">
                                <p class="request-id">Demande #{{ $request->id }}</p>
                                <h3 class="mt-1 text-lg font-extrabold text-slate-900 line-clamp-2">
                                    {{ $request->equipment ? $request->equipment->name : 'Équipement supprimé' }}
                                </h3>
                                <p class="provider-line mt-2">
                                    <i class="fas fa-user-circle text-green-600"></i>
                                    @if($request->equipment && $request->equipment->prestataire)
                                        <span class="truncate">avec {{ $request->equipment->prestataire->company_name ?? $request->equipment->prestataire->first_name . ' ' . $request->equipment->prestataire->last_name }}</span>
                                    @else
                                        <span>Prestataire non disponible</span>
                                    @endif
                                </p>
                            </div>

                            <div class="request-meta mb-5">
                                <span class="meta-pill">
                                    <i class="fas fa-calendar-alt"></i>
                                    {{ $request->start_date->format('d/m/Y') }}
                                </span>
                                <span class="meta-pill">
                                    <i class="fas fa-clock"></i>
                                    {{ $request->duration_days }} jour{{ $request->duration_days > 1 ? 's' : '' }}
                                </span>
                                @if(!is_null($request->total_amount))
                                    <span class="meta-pill amount-pill">
                                        <i class="fas fa-euro-sign"></i>
                                        {{ number_format((float) $request->total_amount, 2, ',', ' ') }} €
                                    </span>
                                @endif
                            </div>

                            <div class="mt-auto pt-4 border-t border-emerald-100 flex flex-col space-y-2">
                                <a href="{{ route('client.equipment-rental-requests.show', $request) }}" class="action-button action-button-primary w-full inline-flex items-center justify-center px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <i class="fas fa-eye mr-2"></i> Voir détails
                                </a>

                                @if($request->status === 'pending')
                                    <form method="POST"
                                          action="{{ route('client.equipment-rental-requests.destroy', $request) }}"
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette demande ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="action-button action-button-danger w-full inline-flex items-center justify-center px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            <i class="fas fa-times mr-2"></i> Annuler
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                <div class="pagination">
                    {{ $requests->appends(request()->query())->links() }}
                </div>
            </div>
        @else
            <div class="col-span-1 sm:col-span-2 lg:col-span-3 empty-state">
                <div class="text-green-500 mb-4">
                    <svg class="mx-auto h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-green-900 mb-2">Aucune demande trouvée</h3>
                <p class="text-green-800 mb-6">Vous n'avez pas encore effectué de demande de location ou aucune demande ne correspond à vos critères de recherche.</p>
                <div class="space-y-4">
                    <a href="{{ route('equipment.index') }}" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 shadow-lg hover:shadow-xl">
                        Nouvelle demande
                    </a>
                    @if(request()->hasAny(['search', 'status', 'date_from']))
                        <div class="mt-6">
                            <a href="{{ route('client.equipment-rental-requests.index') }}" class="text-green-600 hover:text-green-700 font-semibold text-lg hover:underline transition-all duration-200">
                                Voir toutes mes demandes
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButton = document.getElementById('toggleFilters');
        const filtersForm = document.getElementById('filtersForm');
        const buttonText = document.getElementById('filterButtonText');
        const chevron = document.getElementById('filterChevron');
        
        toggleButton.addEventListener('click', function() {
            if (filtersForm.style.display === 'none') {
                filtersForm.style.display = 'block';
                buttonText.textContent = 'Masquer les filtres';
                chevron.classList.remove('fa-chevron-down');
                chevron.classList.add('fa-chevron-up');
            } else {
                filtersForm.style.display = 'none';
                buttonText.textContent = 'Afficher les filtres';
                chevron.classList.remove('fa-chevron-up');
                chevron.classList.add('fa-chevron-down');
            }
        });
    });
    
    function clearFilters() {
        const form = document.getElementById('filtersForm');
        form.reset();
        
        // Clear search input
        document.getElementById('search').value = '';
        
        window.location.href = '{{ route('client.equipment-rental-requests.index') }}';
    }
</script>
@endpush
