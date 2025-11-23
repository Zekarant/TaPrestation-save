@extends('layouts.admin-modern')

@section('page-title', 'Gestion des Réservations')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Header Section -->
<div class="bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 rounded-2xl shadow-2xl mb-6 sm:mb-8 overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-10"></div>
    <div class="relative px-6 sm:px-8 py-8 sm:py-12">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-6">
            <div class="text-white">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-2 sm:mb-3">Gestion des Réservations</h1>
                <p class="text-blue-100 text-sm sm:text-base lg:text-lg opacity-90">Gérez toutes les réservations de votre plateforme</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">

                <button onclick="toggleFilters()" class="bg-white/20 hover:bg-white/30 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-xl font-medium transition-all duration-200 backdrop-blur-sm border border-white/20 hover:border-white/40 flex items-center justify-center gap-2">
                    <i class="fas fa-filter text-sm"></i>
                    <span class="text-sm sm:text-base">Afficher les filtres</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
    <!-- Total Bookings Card -->
    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-4 sm:p-6 border border-blue-200 hover:shadow-lg transition-all duration-300 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-600 text-xs sm:text-sm font-medium uppercase tracking-wide mb-1 sm:mb-2">Total Réservations</p>
                <p class="text-2xl sm:text-3xl font-bold text-blue-900">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-blue-500 p-2 sm:p-3 rounded-xl group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-calendar-check text-white text-lg sm:text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Pending Bookings Card -->
    <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-2xl p-4 sm:p-6 border border-amber-200 hover:shadow-lg transition-all duration-300 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-amber-600 text-xs sm:text-sm font-medium uppercase tracking-wide mb-1 sm:mb-2">En Attente</p>
                <p class="text-2xl sm:text-3xl font-bold text-amber-900">{{ $stats['pending'] }}</p>
            </div>
            <div class="bg-amber-500 p-2 sm:p-3 rounded-xl group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-clock text-white text-lg sm:text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Confirmed Bookings Card -->
    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-2xl p-4 sm:p-6 border border-emerald-200 hover:shadow-lg transition-all duration-300 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-emerald-600 text-xs sm:text-sm font-medium uppercase tracking-wide mb-1 sm:mb-2">Confirmées</p>
                <p class="text-2xl sm:text-3xl font-bold text-emerald-900">{{ $stats['confirmed'] }}</p>
            </div>
            <div class="bg-emerald-500 p-2 sm:p-3 rounded-xl group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-check-circle text-white text-lg sm:text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Completed Bookings Card -->
    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-4 sm:p-6 border border-blue-200 hover:shadow-lg transition-all duration-300 group">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-600 text-xs sm:text-sm font-medium uppercase tracking-wide mb-1 sm:mb-2">Terminées</p>
                <p class="text-2xl sm:text-3xl font-bold text-blue-900">{{ $stats['completed'] }}</p>
            </div>
            <div class="bg-blue-500 p-2 sm:p-3 rounded-xl group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-flag-checkered text-white text-lg sm:text-xl"></i>
            </div>
        </div>
    </div>
</div>


<!-- Filters -->
<div id="filters" class="filters-section" style="display: none;">
    <form method="GET" action="{{ route('administrateur.bookings.index') }}">
        <div class="filters-grid">
            <div class="filter-group">
                <label for="status">Statut</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Tous les statuts</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminée</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="date_from">Date de début</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            
            <div class="filter-group">
                <label for="date_to">Date de fin</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            
            <div class="filter-group">
                <label for="prestataire">Prestataire</label>
                <input type="text" name="prestataire" id="prestataire" class="form-control" placeholder="Nom du prestataire" value="{{ request('prestataire') }}">
            </div>
            
            <div class="filter-group">
                <label for="client">Client</label>
                <input type="text" name="client" id="client" class="form-control" placeholder="Nom du client" value="{{ request('client') }}">
            </div>
        </div>
        
        <div class="filters-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
                Filtrer
            </button>
            <a href="{{ route('administrateur.bookings.index') }}" class="btn btn-outline">
                <i class="fas fa-times"></i>
                Réinitialiser
            </a>
        </div>
    </form>
</div>

<!-- Bookings Cards -->
<div class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 p-2 rounded-lg">
                    <i class="fas fa-calendar-check text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Réservations</h3>
                    <p class="text-blue-100 text-sm">{{ $bookings->total() }} réservation(s) au total</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <select class="bg-white/10 border border-white/20 text-white text-sm rounded-lg px-3 py-2 focus:ring-2 focus:ring-white/50" onchange="changePerPage(this.value)">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 par page</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 par page</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 par page</option>
                </select>
                <button class="bg-white/10 hover:bg-white/20 border border-white/20 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center gap-2">
                    
                </button>
            </div>
        </div>
    </div>
    
    <!-- Cards Container -->
    <div class="p-3 sm:p-6">
        <div class="grid grid-cols-1 sm:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6">
            @forelse($bookings as $booking)
                <div class="bg-white border border-blue-100 rounded-xl p-4 sm:p-6 hover:shadow-lg transition-all duration-300 hover:border-blue-300">
                    <!-- Mobile Header -->
                    <div class="block sm:hidden mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="bg-blue-100 p-1.5 rounded-lg">
                                    <i class="fas fa-calendar-check text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-blue-900 text-base">#{{ $booking->id }}</h4>
                                    <p class="text-blue-600 text-xs">{{ $booking->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            @switch($booking->status)
                                @case('pending')
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-medium">En attente</span>
                                    @break
                                @case('confirmed')
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Confirmée</span>
                                    @break
                                @case('completed')
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">Terminée</span>
                                    @break
                                @case('cancelled')
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">Annulée</span>
                                    @break
                                @default
                                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs font-medium">{{ $booking->status }}</span>
                            @endswitch
                        </div>
                        
                        @if($booking->price)
                            <div class="text-right">
                                <span class="text-lg font-bold text-blue-900">{{ number_format($booking->price, 2) }} €</span>
                            </div>
                        @endif
                        
                        <!-- Service Info Mobile -->
                        <div class="bg-blue-50 rounded-lg p-3 mb-3">
                            <h5 class="font-semibold text-blue-900 mb-1 text-sm">{{ $booking->service->title ?? 'Service supprimé' }}</h5>
                            @if($booking->service)
                                <p class="text-blue-600 text-xs">{{ $booking->service->categories->first()->name ?? 'N/A' }}</p>
                            @endif
                        </div>
                        
                        <!-- Client and Provider Info Mobile -->
                        <div class="space-y-2 mb-3">
                            <div class="flex items-center gap-2">
                                <div class="bg-green-100 p-1.5 rounded-lg">
                                    <i class="fas fa-user text-green-600 text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 text-sm truncate">{{ $booking->client->user->name ?? 'N/A' }}</p>
                                    <p class="text-gray-600 text-xs truncate">{{ $booking->client->user->email ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="bg-purple-100 p-1.5 rounded-lg">
                                    <i class="fas fa-user-tie text-purple-600 text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 text-sm truncate">{{ $booking->prestataire->user->name ?? 'N/A' }}</p>
                                    <p class="text-gray-600 text-xs truncate">{{ $booking->prestataire->user->email ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Date and Time Mobile -->
                        <div class="flex items-center gap-2 mb-3 text-gray-700">
                            <i class="fas fa-calendar text-blue-600 text-xs"></i>
                            <span class="font-medium text-xs">{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') : 'N/A' }}</span>
                            @if($booking->booking_time)
                                <i class="fas fa-clock text-blue-600 ml-2 text-xs"></i>
                                <span class="text-xs">{{ $booking->booking_time }}</span>
                            @endif
                        </div>
                        
                        <!-- Actions Mobile -->
                        <div class="flex gap-2 pt-3 border-t border-blue-100">
                            <a href="{{ route('administrateur.bookings.show', $booking->id) }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs font-medium transition-colors duration-200 text-center">
                                <i class="fas fa-eye mr-1"></i>Voir
                            </a>
                            <button onclick="confirmDelete({{ $booking->id }})" class="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg text-xs font-medium transition-colors duration-200">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Desktop Header -->
                    <div class="hidden sm:block">
                        <!-- Header with ID and Status -->
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-100 p-2 rounded-lg">
                                    <i class="fas fa-calendar-check text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-blue-900 text-lg">#{{ $booking->id }}</h4>
                                    <p class="text-blue-600 text-sm">{{ $booking->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                @switch($booking->status)
                                    @case('pending')
                                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">En attente</span>
                                        @break
                                    @case('confirmed')
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">Confirmée</span>
                                        @break
                                    @case('completed')
                                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">Terminée</span>
                                        @break
                                    @case('cancelled')
                                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">Annulée</span>
                                        @break
                                    @default
                                        <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-medium">{{ $booking->status }}</span>
                                @endswitch
                                @if($booking->price)
                                    <span class="text-2xl font-bold text-blue-900">{{ number_format($booking->price, 2) }} €</span>
                                @else
                                    <span class="text-gray-500">Prix non défini</span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Service Info -->
                        <div class="bg-blue-50 rounded-lg p-4 mb-4">
                            <h5 class="font-semibold text-blue-900 mb-1">{{ $booking->service->title ?? 'Service supprimé' }}</h5>
                            @if($booking->service)
                                <p class="text-blue-600 text-sm">{{ $booking->service->categories->first()->name ?? 'N/A' }}</p>
                            @endif
                        </div>
                        
                        <!-- Client and Provider Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-green-100 p-2 rounded-lg">
                                    <i class="fas fa-user text-green-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $booking->client->user->name ?? 'N/A' }}</p>
                                    <p class="text-gray-600 text-sm">{{ $booking->client->user->email ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="bg-purple-100 p-2 rounded-lg">
                                    <i class="fas fa-user-tie text-purple-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $booking->prestataire->user->name ?? 'N/A' }}</p>
                                    <p class="text-gray-600 text-sm">{{ $booking->prestataire->user->email ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Date and Time -->
                        <div class="flex items-center gap-2 mb-4 text-gray-700">
                            <i class="fas fa-calendar text-blue-600"></i>
                            <span class="font-medium">{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') : 'N/A' }}</span>
                            @if($booking->booking_time)
                                <i class="fas fa-clock text-blue-600 ml-4"></i>
                                <span>{{ $booking->booking_time }}</span>
                            @endif
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex gap-2 pt-4 border-t border-blue-100">
                            <a href="{{ route('administrateur.bookings.show', $booking->id) }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 text-center">
                                <i class="fas fa-eye mr-2"></i>Voir détails
                            </a>
                            <button onclick="confirmDelete({{ $booking->id }})" class="bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-calendar-times text-6xl text-blue-200 mb-4"></i>
                    <div class="text-xl font-semibold text-blue-800 mb-2">Aucune réservation trouvée</div>
                    <div class="text-blue-600">Il n'y a aucune réservation correspondant à vos critères</div>
                </div>
            @endforelse
        </div>
    </div>
    
    @if($bookings && $bookings->hasPages())
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 mt-6 border-t-2 border-blue-200 px-6 pb-6">
            <div class="text-sm text-blue-700 font-medium">
                Affichage de {{ $bookings->firstItem() }} à {{ $bookings->lastItem() }} sur {{ $bookings->total() }} résultats
            </div>
            <div class="flex justify-center">
                {{ $bookings->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cette réservation ? Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--primary-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 0.875rem;
}

.user-details {
    flex: 1;
}

.user-name {
    font-weight: 600;
    color: var(--dark);
    font-size: 0.875rem;
}

.user-email {
    color: var(--secondary);
    font-size: 0.75rem;
}

.service-info .service-title {
    font-weight: 600;
    color: var(--dark);
    font-size: 0.875rem;
}

.service-info .service-category {
    color: var(--secondary);
    font-size: 0.75rem;
}

.date-info .booking-date {
    font-weight: 600;
    color: var(--dark);
    font-size: 0.875rem;
}

.date-info .booking-time {
    color: var(--secondary);
    font-size: 0.75rem;
}

.price {
    font-weight: 600;
    color: var(--success);
    font-size: 0.875rem;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.empty-state {
    text-align: center;
    padding: 2rem;
}

.empty-state h4 {
    color: var(--dark);
    margin-bottom: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
function toggleFilters() {
    const filters = document.getElementById('filters');
    filters.style.display = filters.style.display === 'none' ? 'block' : 'none';
}

function confirmDelete(bookingId) {
    const form = document.getElementById('deleteForm');
    form.action = `/administrateur/bookings/${bookingId}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endpush