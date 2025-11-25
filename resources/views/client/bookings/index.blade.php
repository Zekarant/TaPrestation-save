@extends('layouts.app')

@section('content')
<style>
/* Adding the blue color scheme and styling from bookings/create.blade.php */
.slot-option {
    transition: all 0.2s ease-in-out;
}

.slot-option:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.slot-checkbox:checked + div {
    border-color: #3b82f6 !important;
    background-color: #dbeafe !important;
    color: #1e3a8a !important;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    transform: scale(1.02);
}

.slot-selected {
    border-color: #3b82f6 !important;
    background-color: #dbeafe !important;
    color: #1e3a8a !important;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    transform: scale(1.02);
}

@keyframes slotSelect {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1.02); }
}

.slot-grid {
    display: grid;
    gap: 1rem;
}

/* Responsive adjustments for slot grid */
@media (max-width: 640px) {
    .slot-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
}

@media (min-width: 641px) and (max-width: 768px) {
    .slot-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .slot-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    }
}

@media (min-width: 1025px) {
    .slot-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    }
}

/* Additional responsive improvements */
@media (max-width: 1024px) {
    .sticky {
        position: static;
    }
}

/* Enhanced button styles */
.btn-primary {
    background-color: #3b82f6;
    color: white;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.2s ease-in-out;
    border: none;
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2);
}

.btn-primary:hover {
    background-color: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 6px 8px rgba(59, 130, 246, 0.3);
}

.btn-secondary {
    background-color: #e5e7eb;
    color: #374151;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.2s ease;
    border: none;
}

.btn-secondary:hover {
    background-color: #d1d5db;
    transform: translateY(-1px);
}

/* Enhanced toggle button */
#toggleFilters {
    background-color: #3b82f6;
    color: white;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
}

#toggleFilters:hover {
    background-color: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(59, 130, 246, 0.4);
}

/* Enhanced rating stars */
.rating-star {
    transition: all 0.2s ease;
}

.rating-star:hover {
    transform: scale(1.1);
}

/* Enhanced action buttons */
.action-button {
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.action-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Verified badge enhancement */
.verified-badge {
    background-color: #10b981;
    color: white;
    font-weight: 600;
    border-radius: 9999px;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

/* Empty state enhancement */
.empty-state {
    background-color: #f0f9ff;
    border-radius: 1rem;
    border: 2px dashed #93c5fd;
    padding: 2rem;
    text-align: center;
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
    background-color: #dbeafe;
    color: #1e40af;
}

.pagination .active {
    background-color: #3b82f6;
    color: white;
}

/* Booking card enhancement */
.booking-card {
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    overflow: hidden;
    background: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.booking-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(59, 130, 246, 0.25);
    border-color: #93c5fd;
}

/* Status badge enhancement */
.status-badge {
    border-radius: 9999px;
    padding: 0.25rem 0.75rem;
    font-weight: 600;
    font-size: 0.75rem;
}

.status-pending {
    background-color: #fef3c7;
    color: #92400e;
    border: 1px solid #fcd34d;
}

.status-confirmed {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
}

.status-completed {
    background-color: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.status-cancelled {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

.status-refused {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

/* Filter container enhancement */
.filter-container {
    background-color: white;
    border-radius: 1rem;
    border: 1px solid #bfdbfe;
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.1);
}
</style>

<div class="bg-blue-50 min-h-screen py-8">
<div class="container mx-auto px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6 sm:mb-8 text-center">
            <h1 class="text-2xl xs:text-3xl sm:text-4xl font-extrabold text-blue-900 mb-1 sm:mb-2">Historique de mes réservations</h1>
            <p class="text-base sm:text-lg text-blue-700 px-2">Consultez et gérez l'ensemble de vos réservations de services</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 sm:p-4 rounded-md mb-5 sm:mb-6 shadow-md">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 sm:p-4 rounded-md mb-5 sm:mb-6 shadow-md">
                {{ session('error') }}
            </div>
        @endif
        
        <!-- Filtres de réservation -->
        <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6 mb-6 sm:mb-8">
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-center sm:text-left">
                    <h3 class="text-xl sm:text-2xl font-bold text-blue-800 mb-1 sm:mb-2">Filtres de recherche</h3>
                    <p class="text-sm sm:text-base lg:text-lg text-blue-700">Affinez votre recherche pour trouver la réservation parfaite</p>
                </div>
                <button type="button" id="toggleFilters" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                    <span id="filterButtonText">Afficher les filtres</span>
                    <i class="fas fa-chevron-down ml-2" id="filterChevron"></i>
                </button>
            </div>
            
            <form action="{{ route('client.bookings.index') }}" method="GET" class="space-y-4 sm:space-y-6" id="filtersForm" style="display: none;">
                <!-- Première ligne de filtres -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4">
                    <!-- Statut -->
                    <div>
                        <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Statut</label>
                        <div class="relative">
                            <i class="fas fa-filter absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select id="status" name="status" 
                                class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Tous les statuts</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminée</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                                <option value="refused" {{ request('status') == 'refused' ? 'selected' : '' }}>Refusée</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Période -->
                    <div>
                        <label for="date_range" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Période</label>
                        <div class="relative">
                            <i class="fas fa-calendar-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select id="date_range" name="date_range" 
                                class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Toutes les dates</option>
                                <option value="upcoming" {{ request('date_range') == 'upcoming' ? 'selected' : '' }}>À venir</option>
                                <option value="past" {{ request('date_range') == 'past' ? 'selected' : '' }}>Passées</option>
                                <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>Dernier mois</option>
                                <option value="last_3months" {{ request('date_range') == 'last_3months' ? 'selected' : '' }}>3 derniers mois</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-4 sm:pt-6 border-t-2 border-blue-200">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-search mr-2"></i>Appliquer les filtres
                    </button>
                    
                    @if(request('status') || request('date_range'))
                        <a href="{{ route('client.bookings.index') }}" class="flex-1 bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                            <i class="fas fa-undo mr-2"></i>Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
            
            <!-- Indicateur de filtres actifs -->
            @if(request('status') || request('date_range'))
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pt-4 border-t-2 border-blue-200 mt-6 space-y-2 sm:space-y-0">
                    <div class="flex items-center gap-2">
                        <span class="text-xs sm:text-sm font-semibold text-blue-800">Filtres actifs :</span>
                        @if(request('status'))
                            <span class="px-2 sm:px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs sm:text-sm font-bold">
                                Statut: {{ ucfirst(request('status')) }}
                            </span>
                        @endif
                        @if(request('date_range'))
                            <span class="px-2 sm:px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs sm:text-sm font-bold">
                                Période: {{ ucfirst(str_replace('_', ' ', request('date_range'))) }}
                            </span>
                        @endif
                    </div>
                </div>
            @endif
            </div>
        </div>

        <!-- Bookings List -->
        @if($bookings->isEmpty())
            <div class="col-span-1 sm:col-span-2 lg:col-span-3 empty-state">
                <div class="text-blue-500 mb-4">
                    <svg class="mx-auto h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-blue-900 mb-2">Aucune réservation trouvée</h3>
                <p class="text-blue-800 mb-6">Vous n'avez pas encore effectué de réservation ou aucune réservation ne correspond à vos critères de recherche.</p>
                <div class="space-y-4">
                    <a href="{{ route('services.index') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 shadow-lg hover:shadow-xl">
                        Nouvelle réservation
                    </a>
                    @if(request('status') || request('date_range'))
                        <div class="mt-6">
                            <a href="{{ route('client.bookings.index') }}" class="text-blue-600 hover:text-blue-700 font-semibold text-lg hover:underline transition-all duration-200">
                                Voir toutes mes réservations
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- Bookings Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                @foreach($bookings as $booking)
                    <div class="booking-card bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl flex flex-col h-full">
                        <!-- Card Content -->
                        <div class="p-5 flex flex-col flex-grow">
                            <!-- Avatar and Info -->
                            <div class="flex items-start space-x-4 mb-4">
                                <!-- Avatar -->
                                <div class="flex-shrink-0">
                                    @if($booking->prestataire && $booking->prestataire->photo)
                                        <img class="h-12 w-12 rounded-full object-cover shadow-lg" src="{{ asset('storage/' . $booking->prestataire->photo) }}" alt="{{ $booking->prestataire->user->name }}">
                                    @elseif($booking->prestataire && $booking->prestataire->user && $booking->prestataire->user->avatar)
                                        <img class="h-12 w-12 rounded-full object-cover shadow-lg" src="{{ asset('storage/' . $booking->prestataire->user->avatar) }}" alt="{{ $booking->prestataire->user->name }}">
                                    @elseif($booking->prestataire && $booking->prestataire->user && $booking->prestataire->user->profile_photo_url)
                                        <img class="h-12 w-12 rounded-full object-cover shadow-lg" src="{{ $booking->prestataire->user->profile_photo_url }}" alt="{{ $booking->prestataire->user->name }}">
                                    @else
                                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                            {{ $booking->prestataire && $booking->prestataire->user ? strtoupper(substr($booking->prestataire->user->name, 0, 1)) : 'P' }}
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Title and Subtitle -->
                                <div class="flex-grow min-w-0">
                                    <h3 class="text-lg font-bold text-gray-900 truncate">
                                        {{ $booking->service ? $booking->service->name : ($booking->prestataire && $booking->prestataire->user ? $booking->prestataire->user->name : 'Service supprimé') }}
                                    </h3>
                                    <p class="text-gray-600 text-sm truncate">
                                        avec {{ $booking->prestataire && $booking->prestataire->user ? $booking->prestataire->user->name : 'Prestataire supprimé' }}
                                    </p>
                                    <p class="text-gray-500 text-xs mt-1">
                                        Réservation #{{ $booking->id }}
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Date and Time -->
                            <div class="mb-4">
                                <div class="flex items-center text-gray-600 text-sm">
                                    <i class="fas fa-calendar-alt mr-2 text-blue-500"></i>
                                    <span>{{ $booking->start_datetime->format('d/m/Y à H:i') }}</span>
                                </div>
                            </div>
                            
                            <!-- Status Badge -->
                            <div class="mb-4">
                                <span class="status-badge inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                    @if($booking->status === 'pending') status-pending
                                    @elseif($booking->status === 'confirmed') status-confirmed
                                    @elseif($booking->status === 'completed') status-completed
                                    @elseif($booking->status === 'cancelled') status-cancelled
                                    @elseif($booking->status === 'refused') status-refused
                                    @else status-completed
                                    @endif">
                                    @if($booking->status === 'pending')
                                        <i class="fas fa-clock mr-1"></i> En attente
                                    @elseif($booking->status === 'confirmed')
                                        <i class="fas fa-check-circle mr-1"></i> Acceptée
                                    @elseif($booking->status === 'completed')
                                        <i class="fas fa-flag-checkered mr-1"></i> Terminée
                                    @elseif($booking->status === 'cancelled')
                                        <i class="fas fa-times-circle mr-1"></i> Annulée
                                    @elseif($booking->status === 'refused')
                                        <i class="fas fa-ban mr-1"></i> Refusée
                                    @else
                                        {{ ucfirst($booking->status) }}
                                    @endif
                                </span>
                            </div>
                            
                            <!-- Actions - Always at the bottom -->
                            <div class="mt-auto pt-4 border-t border-gray-100 flex flex-col space-y-2">
                                <a href="{{ route('bookings.show', $booking) }}" class="action-button w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                                    <i class="fas fa-eye mr-2"></i> Voir détails
                                </a>
                                
                                @if(($booking->status === 'pending' || $booking->status === 'confirmed') && $booking->start_datetime->isFuture())
                                    <form action="{{ route('bookings.cancel', $booking) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="cancellation_reason" value="Annulée par le client">
                                        <button type="submit" class="action-button w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-200">
                                            <i class="fas fa-times mr-2"></i> Annuler
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                <div class="pagination">
                    {{ $bookings->links() }}
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
</script>
@endpush