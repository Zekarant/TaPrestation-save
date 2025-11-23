@extends('layouts.app')

@section('title', 'Mon Agenda - Prestataire')

@section('content')
<div class="min-h-screen bg-blue-50">
    <div class="container mx-auto py-4 sm:py-6 md:py-8 px-2 sm:px-4">
        <!-- En-tête amélioré responsive -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-3 sm:gap-4">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl shadow-lg">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 100-4 2 2 0 000 4zm6-6V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-3" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-blue-900 mb-1 sm:mb-2">
                            Mon Agenda
                        </h1>
                        <p class="text-sm sm:text-base md:text-lg text-blue-700">Gérez vos réservations et planifiez vos prestations</p>
                    </div>
                </div>
                
                <!-- Navigation et boutons de vue responsive -->
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                    
                    
                    <!-- Sélecteur de vue (onglets arrondis) -->
                    <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-1 flex">
                        <button onclick="changeView('month')" class="view-btn px-2 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-bold rounded-lg transition duration-200 {{ $view === 'month' ? 'bg-blue-600 text-white shadow-lg hover:shadow-xl transform hover:-translate-y-0.5' : 'text-blue-800 hover:text-blue-900 hover:bg-blue-50' }}">
                            Mois
                        </button>
                        <button onclick="changeView('week')" class="view-btn px-2 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-bold rounded-lg transition duration-200 {{ $view === 'week' ? 'bg-blue-600 text-white shadow-lg hover:shadow-xl transform hover:-translate-y-0.5' : 'text-blue-800 hover:text-blue-900 hover:bg-blue-50' }}">
                            <span class="hidden sm:inline">Semaine</span><span class="sm:hidden">Sem.</span>
                        </button>
                        
                    </div>
                </div>
            </div>
        </div>

        <!-- Légende des couleurs responsive avec filtres cliquables -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-6 md:mb-8">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4 flex items-center gap-2">
                <span class="hidden sm:inline">Code couleur des événements</span>
                <span class="sm:hidden">Légende</span>
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                <div id="allEventsBtn" class="flex items-center gap-2 sm:gap-3 p-2 sm:p-3 bg-gray-100 rounded-lg border border-gray-300 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer" onclick="filterEvents('all')">
                    <div class="w-3 h-3 sm:w-4 sm:h-4 bg-gray-500 rounded-full flex-shrink-0"></div>
                    <div>
                        <span class="text-xs sm:text-sm font-medium text-gray-800">Tous les événements</span>
                        <p class="text-xs text-gray-600 hidden sm:block">Afficher tout</p>
                    </div>
                </div>
                <div id="servicesBtn" class="flex items-center gap-2 sm:gap-3 p-2 sm:p-3 bg-blue-50 rounded-lg border border-blue-200 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer" onclick="filterEvents('service')">
                    <div class="w-3 h-3 sm:w-4 sm:h-4 bg-blue-500 rounded-full flex-shrink-0"></div>
                    <div>
                        <span class="text-xs sm:text-sm font-medium text-blue-800">Services</span>
                        <p class="text-xs text-blue-600 hidden sm:block">Prestations de services</p>
                    </div>
                </div>
                <div id="equipmentBtn" class="flex items-center gap-2 sm:gap-3 p-2 sm:p-3 bg-green-50 rounded-lg border border-green-200 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer" onclick="filterEvents('equipment')">
                    <div class="w-3 h-3 sm:w-4 sm:h-4 bg-green-500 rounded-full flex-shrink-0"></div>
                    <div>
                        <span class="text-xs sm:text-sm font-medium text-green-800">Équipements</span>
                        <p class="text-xs text-green-600 hidden sm:block">Locations d'équipements</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Disposition responsive : Desktop (liste à gauche, calendrier à droite) / Mobile (liste en haut, calendrier en dessous) -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-6 md:gap-8">
            <!-- Liste des demandes récentes (Desktop: gauche, Mobile: haut) -->
            <div class="lg:col-span-1 order-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 sticky top-4">
                    <div class="flex items-center justify-between mb-4 sm:mb-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <span class="hidden sm:inline">Mes demandes</span>
                            <span class="sm:hidden">Demandes</span>
                        </h3>
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 sm:px-2.5 py-0.5 rounded-full">
                            {{ $recentDemands->count() }}
                        </span>
                    </div>
                    
                    <!-- Filtre de recherche harmonisé -->
                    <div class="mb-3 sm:mb-4">
                        <div class="relative">
                            <input type="text" id="quickSearch" placeholder="Rechercher..." 
                                   class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Liste des demandes responsive -->
                    <div class="space-y-2 sm:space-y-3 max-h-80 sm:max-h-96 overflow-y-auto">
                        @forelse($recentDemands as $demand)
                            <div class="booking-item-wrapper mb-2 sm:mb-3" data-demand-id="{{ $demand['id'] }}">
                                <div class="p-2 sm:p-3 border rounded-lg hover:shadow-md transition-all duration-200 cursor-pointer booking-item group relative
                                    @if($demand['type'] === 'service') 
                                        border-blue-200 bg-blue-50 hover:bg-blue-100 hover:border-blue-400
                                    @else 
                                        border-green-200 bg-green-50 hover:bg-green-100 hover:border-green-400
                                    @endif" 
                                     onclick="showDemandDetails({{ $demand['id'] }}, '{{ $demand['type'] }}')">
                                    <div class="flex flex-col sm:block">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex items-start gap-2">
                                                <!-- Icon based on type -->
                                                <div class="mt-0.5 flex-shrink-0">
                                                    @if($demand['type'] === 'service')
                                                        <div class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center">
                                                            <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37.996.608 2.296.07 2.572-1.065z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            </svg>
                                                        </div>
                                                    @else
                                                        <div class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center">
                                                            <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37.996.608 2.296.07 2.572-1.065z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-xs sm:text-sm font-bold truncate flex items-center
                                                        @if($demand['type'] === 'service') text-blue-900 @else text-green-900 @endif">
                                                        {{ $demand['title'] }}
                                                        <svg class="ml-1 h-3 w-3 opacity-0 group-hover:opacity-100 transition-opacity
                                                            @if($demand['type'] === 'service') text-blue-400 @else text-green-400 @endif" 
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                        </svg>
                                                    </h4>
                                                    <!-- Status badge for desktop only - displayed below title -->
                                                    <div class="hidden sm:inline-flex mt-1 mb-1 items-center px-1.5 py-0.5 rounded-full text-xs font-bold
                                                        @if($demand['status'] === 'confirmed' || $demand['status'] === 'accepted') bg-green-100 text-green-800
                                                        @elseif($demand['status'] === 'pending') bg-orange-100 text-orange-800
                                                        @elseif($demand['status'] === 'completed') bg-blue-100 text-blue-800
                                                        @else bg-red-100 text-red-800 @endif">
                                                        {{ ucfirst($demand['status']) }}
                                                    </div>
                                                    <p class="text-xs mt-1 flex items-center
                                                        @if($demand['type'] === 'service') text-blue-700 @else text-green-700 @endif">
                                                        <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                        {{ $demand['client_name'] }}
                                                    </p>
                                                    <p class="text-xs mt-1 flex items-center
                                                        @if($demand['type'] === 'service') text-blue-600 @else text-green-600 @endif">
                                                        <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        @if($demand['type'] === 'service')
                                                            {{ $demand['start_date']->format('d/m/Y à H:i') }}
                                                        @else
                                                            {{ \Carbon\Carbon::parse($demand['start_date'])->format('d/m/Y') }}
                                                        @endif
                                                    </p>
                                                    
                                                </div>
                                            </div>
                                            
                                            <!-- Status badge for mobile only - still displayed on the right -->
                                            <span class="sm:hidden inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-bold flex-shrink-0
                                                @if($demand['status'] === 'confirmed' || $demand['status'] === 'accepted') bg-green-100 text-green-800
                                                @elseif($demand['status'] === 'pending') bg-orange-100 text-orange-800
                                                @elseif($demand['status'] === 'completed') bg-blue-100 text-blue-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($demand['status']) }}
                                            </span>
                                        </div>
                                        
                                        <!-- Action buttons for pending demands -->
                                        @if(($demand['can_accept'] ?? false) || ($demand['can_reject'] ?? false))
                                            <div class="mt-2 flex gap-2">
                                                @if($demand['can_accept'] ?? false)
                                                    @if($demand['type'] === 'service')
                                                        <button onclick="event.stopPropagation(); acceptDemand({{ $demand['id'] }}, 'service')" 
                                                                class="text-xs bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded transition duration-200">
                                                            <i class="fas fa-check mr-1"></i>Accepter
                                                        </button>
                                                    @else
                                                        <button onclick="event.stopPropagation(); acceptDemand({{ $demand['id'] }}, 'equipment')" 
                                                                class="text-xs bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded transition duration-200">
                                                            <i class="fas fa-check mr-1"></i>Accepter
                                                        </button>
                                                    @endif
                                                @endif
                                                
                                                @if($demand['can_reject'] ?? false)
                                                    @if($demand['type'] === 'service')
                                                        <button onclick="event.stopPropagation(); rejectDemand({{ $demand['id'] }}, 'service')" 
                                                                class="text-xs bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded transition duration-200">
                                                            <i class="fas fa-times mr-1"></i>Refuser
                                                        </button>
                                                    @else
                                                        <button onclick="event.stopPropagation(); rejectDemand({{ $demand['id'] }}, 'equipment')" 
                                                                class="text-xs bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded transition duration-200">
                                                            <i class="fas fa-times mr-1"></i>Refuser
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        @elseif(($demand['status'] ?? '') === 'pending' && !$demand['can_accept'] && !$demand['can_reject'])
                                            <div class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded-lg">
                                                <p class="text-yellow-800 text-xs">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    Impossible d'accepter ou refuser cette demande car la ressource a été supprimée.
                                                </p>
                                            </div>
                                        @elseif(($demand['status'] ?? '') === 'pending' && !$demand['can_accept'] && $demand['can_reject'])
                                            <div class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded-lg">
                                                <p class="text-yellow-800 text-xs">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    {{ $demand['availability_message'] ?? 'L\'équipement est en cours de location.' }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 sm:py-8">
                                <svg class="mx-auto h-8 w-8 sm:h-12 sm:w-12 text-gray-400" 
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <h3 class="mt-2 text-xs sm:text-sm font-bold text-gray-900">
                                    Aucune demande
                                </h3>
                                <p class="mt-1 text-xs sm:text-sm text-gray-600">
                                    Aucune demande récente trouvée.
                                </p>
                            </div>
                        @endforelse
                    </div>
                    
                    <!-- Lien vers toutes les demandes responsive -->
                    <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200">
                        <a href="{{ route('prestataire.bookings.index') }}" 
                           class="w-full inline-flex items-center justify-center px-3 sm:px-4 py-2 border border-blue-300 text-xs sm:text-sm font-medium rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                            <span class="hidden sm:inline">Voir toutes les demandes</span>
                            <span class="sm:hidden">Toutes les demandes</span>
                            <svg class="ml-1 sm:ml-2 h-3 w-3 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Calendrier principal responsive (Desktop: droite, Mobile: dessous) -->
            <div class="lg:col-span-3 order-2">
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden">
                    <div class="p-3 sm:p-4 md:p-6">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Modal responsive pour les détails de réservation -->
<div id="bookingModal" class="fixed inset-0 bg-blue-900 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 opacity-0 transition-opacity duration-300">
    <div class="relative top-4 sm:top-10 md:top-20 mx-auto p-3 sm:p-4 md:p-5 border border-blue-200 w-11/12 sm:w-10/12 md:w-3/4 lg:w-1/2 shadow-2xl rounded-xl bg-white transform transition-transform duration-300 scale-95">
        <div class="mt-2 sm:mt-3">
            <div class="flex justify-between items-center mb-3 sm:mb-4">
                <h3 class="text-base sm:text-lg font-bold text-blue-900" id="modalTitle">Détails de la réservation</h3>
                <button onclick="closeModal()" class="text-blue-400 hover:text-blue-600 transition duration-200 p-1">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="modalContent">
                <!-- Le contenu sera chargé dynamiquement -->
            </div>
        </div>
    </div>
</div>

<!-- Mobile-friendly modal for smaller screens -->
<style>
    @media (max-width: 480px) {
        #bookingModal .relative {
            top: 2rem;
            width: 95%;
            padding: 1rem;
        }
        
        #bookingModal h3 {
            font-size: 1.125rem;
        }
        
        #bookingModal .flex.justify-between.items-center {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        #bookingModal .flex.justify-between.items-center button {
            align-self: flex-end;
        }
    }
</style>

@endsection

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
<style>
    /* Calendrier principal */
    #calendar {
        background: white;
        border-radius: 12px;
        font-family: 'Inter', sans-serif;
    }
    
    /* En-tête du calendrier */
    .fc-header-toolbar {
        padding: 1rem 0;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 1rem !important;
        flex-wrap: wrap;
    }
    
    .fc-toolbar-title {
        font-size: 1.5rem !important;
        font-weight: 700 !important;
        color: #1f2937;
    }
    
    /* Boutons de navigation */
    .fc-button {
        background: #f3f4f6 !important;
        border: 1px solid #d1d5db !important;
        color: #374151 !important;
        border-radius: 8px !important;
        padding: 0.5rem 1rem !important;
        font-weight: 500 !important;
        transition: all 0.2s ease !important;
        min-height: 44px;
        min-width: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .fc-button:hover {
        background: #e5e7eb !important;
        border-color: #9ca3af !important;
    }
    
    .fc-button:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }
    
    .fc-button-active {
        background: #3b82f6 !important;
        border-color: #3b82f6 !important;
        color: white !important;
    }
    
    /* Grille du calendrier */
    .fc-scrollgrid {
        border: 1px solid #e5e7eb !important;
        border-radius: 12px !important;
        overflow: hidden;
    }
    
    .fc-col-header {
        background: #f9fafb !important;
        border-bottom: 1px solid #e5e7eb !important;
    }
    
    .fc-col-header-cell {
        padding: 0.75rem 0.5rem !important;
        font-weight: 600 !important;
        color: #374151 !important;
        text-transform: uppercase !important;
        font-size: 0.75rem !important;
        letter-spacing: 0.05em !important;
    }
    
    /* Cellules des jours */
    .fc-daygrid-day {
        border: 1px solid #f3f4f6 !important;
        min-height: 120px !important;
        background: white !important;
        transition: background-color 0.2s ease !important;
    }
    
    .fc-daygrid-day:hover {
        background: #f8fafc !important;
    }
    
    /* Weekends grisés */
    .fc-day-sat, .fc-day-sun {
        background: #f9fafb !important;
    }
    
    /* Date du jour mise en avant */
    .fc-day-today {
        background: #eff6ff !important;
        border: 2px solid #3b82f6 !important;
    }
    
    .fc-day-today .fc-daygrid-day-number {
        background: #3b82f6 !important;
        color: white !important;
        border-radius: 50% !important;
        width: 28px !important;
        height: 28px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-weight: 700 !important;
        margin: 4px !important;
    }
    
    /* Numéros des jours */
    .fc-daygrid-day-number {
        padding: 4px 8px !important;
        font-weight: 600 !important;
        color: #374151 !important;
        text-decoration: none !important;
    }
    
    /* Événements */
    .fc-event {
        border: none !important;
        border-radius: 6px !important;
        padding: 4px 8px !important;
        margin: 2px 4px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
        position: relative;
        overflow: hidden;
    }
    
    .fc-event:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }
    
    /* Couleurs des événements par type - seulement deux couleurs */
    .fc-event.event-service {
        background: #3b82f6 !important; /* Bleu pour services */
        color: #000000 !important; /* Texte noir */
        border-left: 4px solid #1d4ed8 !important;
    }
    
    .fc-event.event-equipment {
        background: #22c55e !important; /* Vert pour équipements */
        color: #000000 !important; /* Texte noir */
        border-left: 4px solid #166534 !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 5px rgba(34, 197, 94, 0.2) !important;
    }
    
    .fc-event.event-urgent_sale {
        background: #22c55e !important; /* Vert pour équipements (même couleur que equipment) */
        color: #000000 !important; /* Texte noir */
        border-left: 4px solid #166534 !important;
    }
    
    /* Titre des événements en noir */
    .fc-event-title {
        font-weight: 700 !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
        position: relative;
        padding-left: 20px !important;
        color: #000000 !important; /* Texte noir pour les titres */
    }
    
    /* Icônes dans les événements */
    .fc-event-title:before {
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
        width: 16px;
        text-align: center;
    }
    
    .event-service .fc-event-title:before {
        content: '';
    }
    
    .event-equipment .fc-event-title:before {
        content: '';
    }
    
    .event-urgent_sale .fc-event-title:before {
        content: '';
    }
    
    .fc-event-time {
        display: none !important;
    }
    
    /* Tooltip personnalisé */
    .event-tooltip {
        position: absolute;
        z-index: 1000;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        max-width: 300px;
        font-size: 13px;
        line-height: 1.4;
    }
    
    .event-tooltip h4 {
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 8px;
        font-size: 14px;
    }
    
    .event-tooltip p {
        margin: 4px 0;
        color: #6b7280;
    }
    
    .event-tooltip .tooltip-type {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        margin-bottom: 8px;
    }
    
    .tooltip-type.service {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .tooltip-type.equipment {
        background: #dcfce7;
        color: #166534;
    }
    
    .tooltip-type.urgent_sale {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .event-tooltip .tooltip-link {
        display: inline-block;
        margin-top: 8px;
        padding: 6px 12px;
        background: #3b82f6;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        transition: background-color 0.2s ease;
    }
    
    .event-tooltip .tooltip-link:hover {
        background: #2563eb;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .fc-toolbar {
            flex-direction: column !important;
            gap: 1rem !important;
        }
        
        .fc-toolbar-title {
            font-size: 1.25rem !important;
        }
        
        .fc-daygrid-day {
            min-height: 80px !important;
        }
        
        .fc-event {
            font-size: 10px !important;
            padding: 2px 4px !important;
        }
        
        /* Improve calendar navigation buttons on mobile */
        .fc-button {
            padding: 0.4rem 0.6rem !important;
            font-size: 0.875rem !important;
        }
        
        /* Adjust calendar header cells for mobile */
        .fc-col-header-cell {
            padding: 0.5rem 0.25rem !important;
            font-size: 0.7rem !important;
        }
    }
    
    /* Extra small devices */
    @media (max-width: 480px) {
        .fc-toolbar-title {
            font-size: 1.1rem !important;
        }
        
        .fc-daygrid-day {
            min-height: 60px !important;
        }
        
        .fc-event {
            font-size: 9px !important;
            padding: 1px 2px !important;
        }
        
        /* Hide event times on very small screens */
        .fc-event-time {
            display: none !important;
        }
        
        /* Make event titles more compact */
        .fc-event-title {
            padding-left: 16px !important;
        }
    }
    
    /* Focus pour l'accessibilité */
    .fc-event:focus {
        outline: 2px solid #3b82f6 !important;
        outline-offset: 2px !important;
    }
    
    .fc-daygrid-day:focus-within {
        background: #f0f9ff !important;
    }
    
    /* Style for clickable booking items */
    .booking-item {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        min-height: 44px; /* Minimum touch target size */
    }
    
    .booking-item:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transform: translateX(-100%);
        transition: transform 0.6s ease;
    }
    
    .booking-item:hover:before {
        transform: translateX(100%);
    }
    
    .booking-item:active {
        transform: scale(0.98);
    }
    
    /* Improve touch target sizes for mobile */
    @media (max-width: 640px) {
        .booking-toggle-actions {
            display: none;
        }
        
        /* Improve card padding and spacing on mobile */
        .booking-item {
            padding: 0.75rem;
        }
        
        /* Adjust font sizes for better readability on mobile */
        .booking-item h4 {
            font-size: 0.875rem;
        }
        
        .booking-item p {
            font-size: 0.75rem;
        }
        
        /* Make status badges more prominent on mobile */
        .inline-flex.items-center.px-1\.5 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }
        
        /* Improve calendar responsiveness on mobile */
        .fc {
            font-size: 0.875rem;
        }
        
        .fc-toolbar-title {
            font-size: 1.25rem !important;
        }
        
        /* Improve modal responsiveness on mobile */
        #bookingModal .relative {
            margin: 1rem;
            width: calc(100% - 2rem);
        }
        
        /* Improve touch targets for better mobile UX */
        .fc-button {
            min-height: 44px;
            min-width: 44px;
        }
        
        .fc-event {
            min-height: 30px;
        }
    }
    
    /* Additional responsive improvements for medium screens */
    @media (min-width: 641px) and (max-width: 1024px) {
        .booking-item {
            padding: 1rem;
        }
    }
    
    /* Ensure proper spacing on larger screens */
    @media (min-width: 1025px) {
        .booking-item {
            padding: 1rem;
        }
    }
    

    
    /* Active filter button styles */
    .filter-active {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
        border-color: #3b82f6 !important;
    }
    
    /* Loading spinner animation */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .animate-spin {
        animation: spin 1s linear infinite;
    }
</style>
@endpush

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
<script>
    let calendar;
    let currentView = '{{ $view }}';
    let touchEvents = {};
    let currentFilter = 'all'; // Default filter is 'all'
    
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded, view:', currentView);
        // Initialize calendar regardless of view (removed the condition)
        console.log('Initializing calendar');
        initCalendar();
        
        // Initialize touch gestures for mobile
        initTouchGestures();
        
        // Set the initial active filter button
        document.getElementById('allEventsBtn').classList.add('filter-active', 'bg-gray-100');
    });
    
    function initCalendar() {
        const calendarEl = document.getElementById('calendar');
        console.log('Calendar element:', calendarEl);
        
        if (!calendarEl) {
            console.error('Calendar element not found');
            return;
        }
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: currentView === 'week' ? 'timeGridWeek' : 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            locale: 'fr',
            firstDay: 1,
            height: 'auto',
            events: {
                url: '{{ route("prestataire.agenda.events") }}',
                extraParams: function() {
                    console.log('Sending filter parameter:', currentFilter);
                    return {
                        filter: currentFilter
                    };
                },
                failure: function(error) {
                    console.error('Failed to load events:', error);
                },
                success: function(events) {
                    console.log('Successfully loaded events:', events.length);
                }
            },
            eventClick: function(info) {
                const event = info.event;
                const props = event.extendedProps;
                
                // Pour les locations d'équipement, rediriger vers la vue dédiée
                if (props.itemType === 'equipment_rental' || props.type === 'equipment') {
                    // Extract the rental ID from the event ID (format: 'equipment_X')
                    const rentalId = event.id.replace('equipment_', '');
                    window.location.href = `/prestataire/equipment-rental-requests/${rentalId}`;
                    return;
                }
                
                // Pour les services, rediriger vers la vue de réservation dédiée
                if (props.id) {
                    window.location.href = `/prestataire/bookings/${props.id}`;
                }
            },
            eventDidMount: function(info) {
                // Ajouter des tooltips
                info.el.setAttribute('title', 
                    info.event.title + '\n' +
                    'Client: ' + info.event.extendedProps.clientName + '\n' +
                    'Statut: ' + info.event.extendedProps.status
                );
                
                // Ajouter des classes spécifiques selon le type d'événement
                if (info.event.extendedProps.type === 'service') {
                    info.el.classList.add('event-service');
                    info.el.style.color = '#000000'; // Texte noir pour services
                } else if (info.event.extendedProps.type === 'equipment') {
                    info.el.classList.add('event-equipment');
                    info.el.style.color = '#000000'; // Texte noir pour équipements
                } else if (info.event.extendedProps.type === 'urgent_sale') {
                    info.el.classList.add('event-urgent_sale');
                    info.el.style.color = '#000000'; // Texte noir pour ventes urgentes
                }
                
                // Pour les locations d’équipements, s'assurer qu'elles sont bien visibles
                if (info.event.extendedProps.itemType === 'equipment_rental') {
                    info.el.classList.add('event-equipment');
                    info.el.style.color = '#000000'; // Texte noir pour locations
                }
            }
        });
        
        calendar.render();
        console.log('Calendar rendered');
    }
    
    function changeView(view) {
        currentView = view;
        
        // Mettre à jour les boutons
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.classList.remove('bg-blue-100', 'text-blue-700');
            btn.classList.add('text-gray-500', 'hover:text-gray-700');
        });
        
        event.target.classList.remove('text-gray-500', 'hover:text-gray-700');
        event.target.classList.add('bg-blue-100', 'text-blue-700');
        
        // Rediriger avec le nouveau paramètre de vue
        const url = new URL(window.location);
        url.searchParams.set('view', view);
        window.location.href = url.toString();
    }
    
    function filterEvents(filterType) {
        console.log('Filtering events by:', filterType);
        
        // Update the current filter
        currentFilter = filterType;
        
        // Update button styles to show active filter
        document.querySelectorAll('#allEventsBtn, #servicesBtn, #equipmentBtn').forEach(btn => {
            btn.classList.remove('filter-active', 'bg-gray-100', 'bg-blue-50', 'bg-green-50');
            btn.classList.add('bg-gray-50');
        });
        
        // Add active class to the selected button
        if (filterType === 'all') {
            document.getElementById('allEventsBtn').classList.add('filter-active', 'bg-gray-100');
        } else if (filterType === 'service') {
            document.getElementById('servicesBtn').classList.add('filter-active', 'bg-blue-50');
        } else if (filterType === 'equipment') {
            document.getElementById('equipmentBtn').classList.add('filter-active', 'bg-green-50');
        }
        
        // Reload calendar events with the new filter
        if (calendar) {
            console.log('Refetching events with filter:', currentFilter);
            calendar.refetchEvents();
        } else {
            console.log('Calendar not initialized, cannot refetch events');
        }
    }
    
    function applyFilters() {
        const url = new URL(window.location);
        
        // Récupérer les valeurs des filtres
        const search = document.getElementById('search').value;
        const service = document.getElementById('service_filter').value;
        const status = document.getElementById('status_filter').value;
        
        // Mettre à jour les paramètres URL
        if (search) url.searchParams.set('search', search);
        else url.searchParams.delete('search');
        
        if (service) url.searchParams.set('service', service);
        else url.searchParams.delete('service');
        
        if (status) url.searchParams.set('status', status);
        else url.searchParams.delete('status');
        
        window.location.href = url.toString();
    }
    
    function clearFilters() {
        const url = new URL(window.location);
        url.searchParams.delete('search');
        url.searchParams.delete('service');
        url.searchParams.delete('status');
        url.searchParams.delete('client');
        window.location.href = url.toString();
    }
    
    function initTouchGestures() {
        const bookingItems = document.querySelectorAll('.booking-item');
        
        bookingItems.forEach(item => {
            const hammer = new Hammer(item);
            
            hammer.on('swipeleft', function() {
                // Enlever la classe is-swiping de tous les items
                document.querySelectorAll('.booking-item').forEach(i => {
                    i.classList.remove('is-swiping');
                });
                
                // Ajouter la classe is-swiping à cet item
                item.classList.add('is-swiping');
            });
            
            hammer.on('swiperight', function() {
                // Enlever la classe is-swiping
                item.classList.remove('is-swiping');
            });
        });
    }
    
    function showEquipmentRentalDetails(rentalId) {
        // Redirect to the equipment rental request detail page
        window.location.href = `/prestataire/equipment-rental-requests/${rentalId}`;
    }
    
    function showBookingDetails(bookingId) {
        // Redirect to the booking detail page
        window.location.href = `/prestataire/bookings/${bookingId}`;
    }
    
    function showModal(title, content) {
        const modal = document.getElementById('bookingModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalContent = document.getElementById('modalContent');
        
        modalTitle.textContent = title;
        modalContent.innerHTML = content;
        
        modal.classList.add('opacity-100');
        modal.classList.remove('hidden');
    }
    
    function closeModal() {
        const modal = document.getElementById('bookingModal');
        modal.classList.add('hidden');
        modal.classList.remove('opacity-100');
    }
    
    function acceptDemand(demandId, type) {
        if (!confirm('Êtes-vous sûr de vouloir accepter cette demande ?')) {
            return;
        }
        
        // Show loading state
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 118-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Traitement...';
        button.disabled = true;
        
        // Determine the correct API endpoint based on demand type
        let apiUrl;
        if (type === 'service') {
            // Use the correct route pattern from web.php - PATCH method to /prestataire/bookings/{booking}/accept
            apiUrl = `/prestataire/bookings/${demandId}/accept`;
        } else if (type === 'equipment') {
            // Use the correct route pattern from web.php - PATCH method to /prestataire/equipment-rental-requests/{request}/accept
            apiUrl = `/prestataire/equipment-rental-requests/${demandId}/accept`;
        } else {
            showNotification('Type de demande non reconnu', 'error');
            button.innerHTML = originalText;
            button.disabled = false;
            return;
        }
        
        // Use the correct route pattern and ensure proper CSRF token
        fetch(apiUrl, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            // Check if response is OK
            if (!response.ok) {
                // Try to parse error response
                return response.json().then(errorData => {
                    // Check if this is the specific error for deleted resources
                    if (errorData.message && (errorData.message.includes('introuvable') || errorData.message.includes('supprimé') || errorData.message.includes('not found'))) {
                        throw new Error('Impossible d\'accepter cette demande car la ressource a été supprimée.');
                    }
                    // Check if this is the specific error for unavailable equipment
                    if (errorData.message && errorData.message.includes('déjà loué')) {
                        throw new Error('Impossible d\'accepter cette demande car l\'équipement est en cours de location.');
                    }
                    throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
                }).catch(() => {
                    // If we can't parse JSON, throw a generic error
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                });
            }
            
            // Try to parse success response
            return response.json().catch(() => {
                // If we can't parse JSON but response is OK, throw a generic error
                throw new Error('Invalid response format');
            });
        })
        .then(data => {
            if (data && data.success) {
                // Show success message
                showNotification('Demande acceptée avec succès', 'success');
                
                // Update the demand item in the list to reflect the new status
                const demandItem = document.querySelector(`[data-demand-id="${demandId}"]`);
                if (demandItem) {
                    // Remove the action buttons
                    const actionButtons = demandItem.querySelector('.flex.gap-2');
                    if (actionButtons) {
                        actionButtons.remove();
                    }
                    
                    // Update the status badge
                    const statusBadges = demandItem.querySelectorAll('[class*="bg-"]');
                    statusBadges.forEach(badge => {
                        // Remove existing status classes
                        badge.classList.remove('bg-orange-100', 'text-orange-800', 'bg-green-100', 'text-green-800');
                        // Add accepted status classes
                        badge.classList.add('bg-green-100', 'text-green-800');
                        badge.textContent = 'Acceptée';
                    });
                }
                
                // Refresh calendar events
                if (calendar) {
                    calendar.refetchEvents();
                }
            } else {
                throw new Error(data.message || 'Erreur lors de l\'acceptation');
            }
        })
        .catch(error => {
            console.error('Error accepting demand:', error);
            showNotification('Erreur lors de l\'acceptation: ' + (error.message || 'Erreur inconnue'), 'error');
        })
        .finally(() => {
            // Restore button state
            if (button) {
                button.innerHTML = originalText;
                button.disabled = false;
            }
        });
    }
    
    function rejectDemand(demandId, type) {
        if (!confirm('Êtes-vous sûr de vouloir refuser cette demande ?')) {
            return;
        }
        
        // Show loading state
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 118-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Traitement...';
        button.disabled = true;
        
        // Determine the correct API endpoint based on demand type
        let apiUrl;
        if (type === 'service') {
            // Use the correct route pattern from web.php - PATCH method to /prestataire/bookings/{booking}/reject
            apiUrl = `/prestataire/bookings/${demandId}/reject`;
        } else if (type === 'equipment') {
            // Use the correct route pattern from web.php - PATCH method to /prestataire/equipment-rental-requests/{request}/reject
            apiUrl = `/prestataire/equipment-rental-requests/${demandId}/reject`;
        } else {
            showNotification('Type de demande non reconnu', 'error');
            button.innerHTML = originalText;
            button.disabled = false;
            return;
        }
        
        // Use the correct route pattern and ensure proper CSRF token
        fetch(apiUrl, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            // Check if response is OK
            if (!response.ok) {
                // Try to parse error response
                return response.json().then(errorData => {
                    // Check if this is the specific error for deleted resources
                    if (errorData.message && (errorData.message.includes('introuvable') || errorData.message.includes('supprimé') || errorData.message.includes('not found'))) {
                        throw new Error('Impossible de refuser cette demande car la ressource a été supprimée.');
                    }
                    // Check if this is the specific error for unavailable equipment
                    if (errorData.message && errorData.message.includes('déjà loué')) {
                        throw new Error('Impossible de refuser cette demande car l\'équipement est en cours de location.');
                    }
                    throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`);
                }).catch(() => {
                    // If we can't parse JSON, throw a generic error
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                });
            }
            
            // Try to parse success response
            return response.json().catch(() => {
                // If we can't parse JSON but response is OK, throw a generic error
                throw new Error('Invalid response format');
            });
        })
        .then(data => {
            if (data && data.success) {
                // Show success message
                showNotification('Demande refusée avec succès', 'success');
                
                // Update the demand item in the list to reflect the new status
                const demandItem = document.querySelector(`[data-demand-id="${demandId}"]`);
                if (demandItem) {
                    // Remove the action buttons
                    const actionButtons = demandItem.querySelector('.flex.gap-2');
                    if (actionButtons) {
                        actionButtons.remove();
                    }
                    
                    // Update the status badge
                    const statusBadges = demandItem.querySelectorAll('[class*="bg-"]');
                    statusBadges.forEach(badge => {
                        // Remove existing status classes
                        badge.classList.remove('bg-orange-100', 'text-orange-800', 'bg-red-100', 'text-red-800');
                        // Add rejected status classes
                        badge.classList.add('bg-red-100', 'text-red-800');
                        badge.textContent = 'Refusée';
                    });
                }
                
                // Refresh calendar events
                if (calendar) {
                    calendar.refetchEvents();
                }
            } else {
                throw new Error(data.message || 'Erreur lors du refus');
            }
        })
        .catch(error => {
            console.error('Error rejecting demand:', error);
            showNotification('Erreur lors du refus: ' + (error.message || 'Erreur inconnue'), 'error');
        })
        .finally(() => {
            // Restore button state
            if (button) {
                button.innerHTML = originalText;
                button.disabled = false;
            }
        });
    }
    
    function showDemandDetails(demandId, type) {
        // Redirect to the appropriate view based on the type
        if (type === 'service') {
            window.location.href = `/prestataire/bookings/${demandId}`;
        } else if (type === 'equipment') {
            window.location.href = `/prestataire/equipment-rental-requests/${demandId}`;
        } else {
            showNotification('Type de demande non reconnu', 'error');
        }
    }
    
    // Function no longer needed as buttons are always visible
    function toggleActions(button) {
        // Keeping the function for backward compatibility
        console.log("Action buttons are now always visible");
        return;
    }
    
    // Function to show notifications
    function showNotification(message, type = 'info') {
        // Remove any existing notifications
        const existingNotification = document.querySelector('.notification-popup');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification-popup fixed top-4 right-4 z-[100] px-5 py-4 rounded-lg shadow-lg text-white font-medium text-sm max-w-md transform transition-transform duration-300 ${
            type === 'success' ? 'bg-green-600' : 
            type === 'error' ? 'bg-red-600' : 
            type === 'warning' ? 'bg-yellow-600' : 'bg-blue-600'
        }`;
        
        // Add icon based on type
        let icon = '';
        if (type === 'success') {
            icon = '<svg class="inline-block h-5 w-5 mr-2 -mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>';
        } else if (type === 'error') {
            icon = '<svg class="inline-block h-5 w-5 mr-2 -mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>';
        } else if (type === 'warning') {
            icon = '<svg class="inline-block h-5 w-5 mr-2 -mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';
        } else {
            icon = '<svg class="inline-block h-5 w-5 mr-2 -mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
        }
        
        notification.innerHTML = icon + message;
        
        // Add to document
        document.body.appendChild(notification);
        
        // Auto remove after 4 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 4000);
    }
</script>
