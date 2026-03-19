@extends('layouts.app')

@section('content')
<style>
/* Modern Mobile-First Design System */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    --warning-gradient: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);
    --card-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    --card-shadow-hover: 0 20px 60px rgba(102, 126, 234, 0.25);
}

/* Smooth animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse-soft {
    0%, 100% { transform: scale(1); opacity: 0.8; }
    50% { transform: scale(1.05); opacity: 1; }
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out forwards;
}

/* Modern page background */
.page-bg {
    background: linear-gradient(180deg, #f0f4ff 0%, #faf5ff 50%, #fff 100%);
    min-height: 100vh;
}

/* Header gradient style */
.header-section {
    background: var(--primary-gradient);
    border-radius: 0 0 32px 32px;
    padding: 1.5rem 1rem 2rem;
    margin: -1rem -0.5rem 1.5rem;
    position: relative;
    overflow: hidden;
}

@media (min-width: 640px) {
    .header-section {
        border-radius: 0 0 48px 48px;
        padding: 2rem 2rem 3rem;
        margin: -1.5rem -1rem 2rem;
    }
}

.header-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 60%);
    animation: pulse-soft 6s ease-in-out infinite;
}

.header-section::after {
    content: '';
    position: absolute;
    bottom: -50%;
    left: -30%;
    width: 80%;
    height: 150%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 50%);
}

/* Modern filter card */
.filter-card {
    background: white;
    border-radius: 24px;
    box-shadow: 0 4px 24px rgba(102, 126, 234, 0.1);
    border: 1px solid rgba(102, 126, 234, 0.1);
    overflow: hidden;
    transition: all 0.3s ease;
}

@media (max-width: 640px) {
    .filter-card {
        border-radius: 20px;
        margin: 0 -0.25rem;
    }
}

/* Modern toggle button */
#toggleFilters {
    background: var(--primary-gradient);
    color: white;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
}

#toggleFilters:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 8px 30px rgba(102, 126, 234, 0.5);
}

#toggleFilters:active {
    transform: translateY(0) scale(0.98);
}

/* Modern booking card */
.booking-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    box-shadow: var(--card-shadow);
    position: relative;
}

.booking-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--primary-gradient);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.booking-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--card-shadow-hover);
}

.booking-card:hover::before {
    opacity: 1;
}

.booking-card:active {
    transform: translateY(-4px);
}

@media (max-width: 640px) {
    .booking-card {
        border-radius: 16px;
    }
    .booking-card:hover {
        transform: translateY(-4px);
    }
}

/* Avatar styles */
.avatar-ring {
    position: relative;
}

.avatar-ring::before {
    content: '';
    position: absolute;
    inset: -3px;
    border-radius: 50%;
    background: var(--primary-gradient);
    opacity: 0.6;
}

.avatar-ring img,
.avatar-ring .avatar-placeholder {
    position: relative;
    z-index: 1;
}

/* Modern status badges */
.status-badge {
    border-radius: 50px;
    padding: 6px 12px;
    font-weight: 700;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    backdrop-filter: blur(4px);
}

@media (min-width: 640px) {
    .status-badge {
        padding: 8px 16px;
        font-size: 11px;
        letter-spacing: 0.8px;
    }
}

.status-pending {
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.2) 0%, rgba(245, 158, 11, 0.3) 100%);
    color: #b45309;
    border: 1px solid rgba(251, 191, 36, 0.4);
}

.status-confirmed {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(5, 150, 105, 0.3) 100%);
    color: #047857;
    border: 1px solid rgba(16, 185, 129, 0.4);
}

.status-completed {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(79, 70, 229, 0.3) 100%);
    color: #4338ca;
    border: 1px solid rgba(99, 102, 241, 0.4);
}

.status-cancelled, .status-refused {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(220, 38, 38, 0.3) 100%);
    color: #b91c1c;
    border: 1px solid rgba(239, 68, 68, 0.4);
}

/* Date badge */
.date-badge {
    background: linear-gradient(135deg, #f0f4ff 0%, #e8ecff 100%);
    border-radius: 12px;
    border: 1px solid rgba(102, 126, 234, 0.15);
}

/* Modern action buttons */
.action-button {
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.action-button::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.4s, height 0.4s;
}

.action-button:active::after {
    width: 300px;
    height: 300px;
}

.btn-primary-gradient {
    background: var(--primary-gradient);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-primary-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
    color: white;
}

.btn-cancel {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.15) 100%);
    color: #dc2626;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.btn-cancel:hover {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(220, 38, 38, 0.25) 100%);
    transform: translateY(-1px);
}

/* Empty state */
.empty-state {
    background: linear-gradient(135deg, #f0f4ff 0%, #faf5ff 100%);
    border-radius: 24px;
    border: 2px dashed rgba(102, 126, 234, 0.4);
    padding: 2rem 1.5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.empty-state::before {
    content: '';
    position: absolute;
    top: -80px;
    right: -80px;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(102, 126, 234, 0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.empty-state::after {
    content: '';
    position: absolute;
    bottom: -60px;
    left: -60px;
    width: 150px;
    height: 150px;
    background: radial-gradient(circle, rgba(118, 75, 162, 0.1) 0%, transparent 70%);
    border-radius: 50%;
}

@media (min-width: 640px) {
    .empty-state {
        border-radius: 32px;
        padding: 3rem 2rem;
    }
}

/* Modern pagination */
.pagination {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 2rem;
}

.pagination a,
.pagination span {
    padding: 10px 14px;
    margin: 0;
    border-radius: 12px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.3s ease;
    background: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    border: 1px solid rgba(102, 126, 234, 0.1);
}

@media (max-width: 640px) {
    .pagination a,
    .pagination span {
        padding: 8px 12px;
        font-size: 12px;
        border-radius: 10px;
    }
}

.pagination a:hover {
    background: linear-gradient(135deg, #f0f4ff 0%, #e8ecff 100%);
    color: #4338ca;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
}

.pagination .active span,
.pagination span[aria-current="page"] {
    background: var(--primary-gradient);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

/* Filter chips */
.filter-chip {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.15) 100%);
    border: 1px solid rgba(102, 126, 234, 0.3);
    border-radius: 50px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    color: #5b21b6;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

@media (min-width: 640px) {
    .filter-chip {
        padding: 8px 16px;
        font-size: 13px;
    }
}

/* Input styles */
.modern-select {
    border-radius: 14px;
    border: 2px solid #e5e7eb;
    transition: all 0.3s ease;
    background: white;
}

.modern-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
    outline: none;
}

/* Service info mini card */
.service-info {
    background: linear-gradient(135deg, #fafbff 0%, #f5f7ff 100%);
    border-radius: 10px;
    padding: 8px 10px;
    border: 1px solid rgba(102, 126, 234, 0.1);
}

/* Booking grid responsive */
.booking-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: 1fr;
}

@media (min-width: 400px) {
    .booking-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }
}

@media (min-width: 768px) {
    .booking-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
    }
}

@media (min-width: 1024px) {
    .booking-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }
}

@media (min-width: 1280px) {
    .booking-grid {
        grid-template-columns: repeat(5, 1fr);
        gap: 24px;
    }
}

/* Quick stats */
.quick-stat {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 12px 16px;
    border: 1px solid rgba(255, 255, 255, 0.3);
}
</style>

<div class="page-bg py-2 sm:py-4 lg:py-6">
<div class="container mx-auto px-2 sm:px-4">
    <div class="max-w-7xl mx-auto">
        
        <!-- Modern Header Section -->
        <div class="header-section mb-4 sm:mb-6">
            <div class="relative z-10">
                <!-- PWA Back Button -->
                <div class="mb-4">
                    <x-pwa-back-button :href="route('client.dashboard')" class="text-white/90 hover:text-white" />
                </div>
                
                <div class="text-center">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-white mb-2 tracking-tight">
                        <i class="fas fa-calendar-check mr-2 opacity-90"></i>
                        Mes Réservations
                    </h1>
                    <p class="text-white/80 text-sm sm:text-base max-w-md mx-auto">
                        Suivez et gérez toutes vos réservations en un seul endroit
                    </p>
                    
                    <!-- Quick Stats -->
                    <div class="flex justify-center gap-3 mt-4 flex-wrap">
                        <div class="quick-stat">
                            <span class="text-white/70 text-xs block">Total</span>
                            <span class="text-white font-bold text-lg">{{ $bookings->total() ?? $bookings->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 text-green-800 p-4 rounded-xl mb-4 shadow-lg animate-fade-in-up flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-lg"></i>
                </div>
                <p class="font-medium text-sm sm:text-base">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 text-red-800 p-4 rounded-xl mb-4 shadow-lg animate-fade-in-up flex items-center gap-3">
                <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-circle text-red-600 text-lg"></i>
                </div>
                <p class="font-medium text-sm sm:text-base">{{ session('error') }}</p>
            </div>
        @endif
        
        <!-- Modern Filter Card -->
        <div class="filter-card p-4 sm:p-5 lg:p-6 mb-4 sm:mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-sliders-h text-indigo-500"></i>
                        Filtres
                    </h3>
                    <p class="text-gray-500 text-xs sm:text-sm mt-0.5">Affinez votre recherche</p>
                </div>
                <button type="button" id="toggleFilters" class="py-2.5 px-5 text-sm font-semibold flex items-center justify-center gap-2">
                    <span id="filterButtonText">Afficher</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-300" id="filterChevron"></i>
                </button>
            </div>
            
            <form action="{{ route('client.bookings.index') }}" method="GET" class="mt-4 space-y-4" id="filtersForm" style="display: none;">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <!-- Statut -->
                    <div>
                        <label for="status" class="block text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wide">Statut</label>
                        <div class="relative">
                            <i class="fas fa-filter absolute left-4 top-1/2 transform -translate-y-1/2 text-indigo-400 text-sm"></i>
                            <select id="status" name="status" 
                                class="modern-select w-full pl-11 pr-4 py-3 text-sm font-medium text-gray-700">
                                <option value="">Tous les statuts</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ En attente</option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>✅ Confirmée</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>🏁 Terminée</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>❌ Annulée</option>
                                <option value="refused" {{ request('status') == 'refused' ? 'selected' : '' }}>🚫 Refusée</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Période -->
                    <div>
                        <label for="date_range" class="block text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wide">Période</label>
                        <div class="relative">
                            <i class="fas fa-calendar-alt absolute left-4 top-1/2 transform -translate-y-1/2 text-indigo-400 text-sm"></i>
                            <select id="date_range" name="date_range" 
                                class="modern-select w-full pl-11 pr-4 py-3 text-sm font-medium text-gray-700">
                                <option value="">Toutes les dates</option>
                                <option value="upcoming" {{ request('date_range') == 'upcoming' ? 'selected' : '' }}>📅 À venir</option>
                                <option value="past" {{ request('date_range') == 'past' ? 'selected' : '' }}>📆 Passées</option>
                                <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>📆 Dernier mois</option>
                                <option value="last_3months" {{ request('date_range') == 'last_3months' ? 'selected' : '' }}>📆 3 derniers mois</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="action-button btn-primary-gradient flex-1 py-3 px-5 text-sm font-bold flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i>
                        <span>Rechercher</span>
                    </button>
                    
                    @if(request('status') || request('date_range'))
                        <a href="{{ route('client.bookings.index') }}" class="action-button flex-1 py-3 px-5 text-sm font-bold flex items-center justify-center gap-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-xl transition-all">
                            <i class="fas fa-undo"></i>
                            <span>Réinitialiser</span>
                        </a>
                    @endif
                </div>
            </form>
            
            <!-- Active Filters Display -->
            @if(request('status') || request('date_range'))
                <div class="flex flex-wrap items-center gap-2 pt-4 mt-4 border-t border-gray-100">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Actifs:</span>
                    @if(request('status'))
                        <span class="filter-chip">
                            <i class="fas fa-tag text-xs"></i>
                            {{ ucfirst(request('status')) }}
                        </span>
                    @endif
                    @if(request('date_range'))
                        <span class="filter-chip">
                            <i class="fas fa-calendar text-xs"></i>
                            {{ ucfirst(str_replace('_', ' ', request('date_range'))) }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <!-- Bookings List -->
        @if($bookings->isEmpty())
            <div class="empty-state mx-1 sm:mx-0">
                <div class="relative z-10">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-4 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-times text-3xl sm:text-4xl text-indigo-400"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-2">Aucune réservation</h3>
                    <p class="text-gray-500 text-sm sm:text-base mb-6 max-w-sm mx-auto">
                        Vous n'avez pas encore de réservation correspondant à vos critères.
                    </p>
                    <div class="space-y-3">
                        <a href="{{ route('services.index') }}" class="action-button btn-primary-gradient inline-flex items-center justify-center gap-2 py-3 px-6 text-sm font-bold">
                            <i class="fas fa-plus-circle"></i>
                            Réserver un service
                        </a>
                        @if(request('status') || request('date_range'))
                            <div class="pt-2">
                                <a href="{{ route('client.bookings.index') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold text-sm hover:underline inline-flex items-center gap-1">
                                    <i class="fas fa-arrow-left text-xs"></i>
                                    Voir toutes les réservations
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <!-- Bookings Grid -->
            <div class="booking-grid px-1 sm:px-0">
                @foreach($bookings as $index => $booking)
                    <div class="booking-card animate-fade-in-up" style="animation-delay: {{ $index * 0.05 }}s">
                        <div class="p-3 sm:p-4 flex flex-col h-full">
                            <!-- Header: Avatar + Info -->
                            <div class="flex items-start gap-3 mb-3">
                                <!-- Avatar -->
                                <div class="avatar-ring flex-shrink-0">
                                    @if($booking->prestataire && $booking->prestataire->photo)
                                        <img class="h-11 w-11 sm:h-12 sm:w-12 rounded-full object-cover" src="{{ asset('storage/' . $booking->prestataire->photo) }}" alt="{{ $booking->prestataire->user->name }}">
                                    @elseif($booking->prestataire && $booking->prestataire->user && $booking->prestataire->user->avatar)
                                        <img class="h-11 w-11 sm:h-12 sm:w-12 rounded-full object-cover" src="{{ asset('storage/' . $booking->prestataire->user->avatar) }}" alt="{{ $booking->prestataire->user->name }}">
                                    @elseif($booking->prestataire && $booking->prestataire->user && $booking->prestataire->user->profile_photo_url)
                                        <img class="h-11 w-11 sm:h-12 sm:w-12 rounded-full object-cover" src="{{ $booking->prestataire->user->profile_photo_url }}" alt="{{ $booking->prestataire->user->name }}">
                                    @else
                                        <div class="avatar-placeholder h-11 w-11 sm:h-12 sm:w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-base sm:text-lg">
                                            {{ $booking->prestataire && $booking->prestataire->user ? strtoupper(substr($booking->prestataire->user->name, 0, 1)) : 'P' }}
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Info -->
                                <div class="flex-grow min-w-0">
                                    <h3 class="text-sm sm:text-base font-bold text-gray-900 truncate leading-tight">
                                        {{ $booking->service ? $booking->service->name : ($booking->prestataire && $booking->prestataire->user ? $booking->prestataire->user->name : 'Service supprimé') }}
                                    </h3>
                                    <p class="text-gray-500 text-xs sm:text-sm truncate mt-0.5">
                                        {{ $booking->prestataire && $booking->prestataire->user ? $booking->prestataire->user->name : 'Prestataire' }}
                                    </p>
                                    <p class="text-gray-400 text-xs mt-1">
                                        #{{ $booking->id }}
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Date Badge -->
                            <div class="date-badge p-2.5 mb-3">
                                <div class="flex items-center justify-between text-xs sm:text-sm">
                                    <div class="flex items-center gap-2 text-gray-700">
                                        <i class="fas fa-calendar-day text-indigo-500"></i>
                                        <span class="font-semibold">{{ $booking->start_datetime->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-gray-600">
                                        <i class="fas fa-clock text-indigo-400 text-xs"></i>
                                        <span class="font-medium">{{ $booking->start_datetime->format('H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status Badge -->
                            <div class="mb-3">
                                <span class="status-badge
                                    @if($booking->status === 'pending') status-pending
                                    @elseif($booking->status === 'confirmed') status-confirmed
                                    @elseif($booking->status === 'completed') status-completed
                                    @elseif($booking->status === 'cancelled') status-cancelled
                                    @elseif($booking->status === 'refused') status-refused
                                    @else status-completed
                                    @endif">
                                    @if($booking->status === 'pending')
                                        <i class="fas fa-hourglass-half"></i> En attente
                                    @elseif($booking->status === 'confirmed')
                                        <i class="fas fa-check-circle"></i> Acceptée
                                    @elseif($booking->status === 'completed')
                                        <i class="fas fa-trophy"></i> Terminée
                                    @elseif($booking->status === 'cancelled')
                                        <i class="fas fa-times-circle"></i> Annulée
                                    @elseif($booking->status === 'refused')
                                        <i class="fas fa-ban"></i> Refusée
                                    @else
                                        {{ ucfirst($booking->status) }}
                                    @endif
                                </span>
                            </div>
                            
                            <!-- Actions -->
                            <div class="mt-auto pt-3 border-t border-gray-50 space-y-2">
                                <a href="{{ route('bookings.show', $booking) }}" class="action-button btn-primary-gradient w-full flex items-center justify-center gap-2 py-2.5 text-xs sm:text-sm font-semibold">
                                    <i class="fas fa-eye"></i>
                                    <span>Détails</span>
                                </a>
                                
                                @if(($booking->status === 'pending' || $booking->status === 'confirmed') && $booking->start_datetime->isFuture())
                                    <form action="{{ route('bookings.cancel', $booking) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="cancellation_reason" value="Annulée par le client">
                                        <button type="submit" class="action-button btn-cancel w-full flex items-center justify-center gap-2 py-2.5 text-xs sm:text-sm font-semibold rounded-xl">
                                            <i class="fas fa-times"></i>
                                            <span>Annuler</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6 sm:mt-8">
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
        
        // Check if there are active filters and show the form
        @if(request('status') || request('date_range'))
            filtersForm.style.display = 'block';
            buttonText.textContent = 'Masquer';
            chevron.style.transform = 'rotate(180deg)';
        @endif
        
        toggleButton.addEventListener('click', function() {
            if (filtersForm.style.display === 'none') {
                filtersForm.style.display = 'block';
                buttonText.textContent = 'Masquer';
                chevron.style.transform = 'rotate(180deg)';
            } else {
                filtersForm.style.display = 'none';
                buttonText.textContent = 'Afficher';
                chevron.style.transform = 'rotate(0deg)';
            }
        });
    });
</script>
@endpush
