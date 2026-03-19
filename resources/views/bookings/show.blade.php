@extends('layouts.app')

@section('content')
@php
    // Ensure variables are defined for backward compatibility
    $isMultiSlotSession = $isMultiSlotSession ?? false;
    $allBookings = $allBookings ?? collect([$booking]);
    $relatedBookings = $relatedBookings ?? collect();
    $totalSessionPrice = $totalSessionPrice ?? $booking->total_price;
    
    // Function to clean session ID from notes for display
    function cleanNotesForDisplay($notes) {
        if (!$notes) return null;
        return trim(preg_replace('/\[SESSION:[^\]]+\]/', '', $notes)) ?: null;
    }
    
    // Status configuration
    $statusConfig = [
        'pending' => ['bg' => 'bg-gradient-to-r from-amber-400 to-orange-400', 'text' => 'text-white', 'icon' => 'fa-hourglass-half', 'label' => 'En attente', 'light' => 'bg-amber-50 border-amber-200 text-amber-800'],
        'confirmed' => ['bg' => 'bg-gradient-to-r from-emerald-400 to-green-500', 'text' => 'text-white', 'icon' => 'fa-check-circle', 'label' => 'Confirmée', 'light' => 'bg-emerald-50 border-emerald-200 text-emerald-800'],
        'completed' => ['bg' => 'bg-gradient-to-r from-blue-400 to-indigo-500', 'text' => 'text-white', 'icon' => 'fa-flag-checkered', 'label' => 'Terminée', 'light' => 'bg-blue-50 border-blue-200 text-blue-800'],
        'cancelled' => ['bg' => 'bg-gradient-to-r from-red-400 to-rose-500', 'text' => 'text-white', 'icon' => 'fa-times-circle', 'label' => 'Annulée', 'light' => 'bg-red-50 border-red-200 text-red-800'],
        'refused' => ['bg' => 'bg-gradient-to-r from-gray-400 to-slate-500', 'text' => 'text-white', 'icon' => 'fa-ban', 'label' => 'Refusée', 'light' => 'bg-gray-50 border-gray-200 text-gray-800'],
    ];
    $currentStatus = $statusConfig[$booking->status] ?? $statusConfig['pending'];
@endphp

<style>
    .booking-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }
    .hero-gradient {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
    }
    .status-glow {
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
    }
    .animate-float {
        animation: float 3s ease-in-out infinite;
    }
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-5px); }
    }
    .slot-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .slot-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .action-btn {
        transition: all 0.2s ease;
    }
    .action-btn:hover {
        transform: translateY(-1px);
    }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <!-- Hero Header -->
    <div class="hero-gradient text-white">
        <div class="container mx-auto px-4 py-6 sm:py-8">
            <div class="max-w-5xl mx-auto">
                <!-- Back Button -->
                @php
                    $previousUrl = url()->previous();
                    $currentUrl = url()->current();
                    if ($previousUrl === $currentUrl || empty($previousUrl)) {
                        $backUrl = auth()->user()->role === 'prestataire' 
                            ? route('prestataire.bookings.index') 
                            : route('client.bookings.index');
                    } else {
                        $backUrl = $previousUrl;
                    }
                @endphp
                <a href="{{ $backUrl }}" 
                   class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-xl text-white text-sm font-medium transition-all mb-6">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Retour aux réservations
                </a>
                
                <!-- Booking Header Info -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="px-3 py-1 {{ $currentStatus['bg'] }} rounded-full text-sm font-bold shadow-lg flex items-center">
                                <i class="fas {{ $currentStatus['icon'] }} mr-1.5"></i>
                                {{ $currentStatus['label'] }}
                            </span>
                            @if($isMultiSlotSession)
                                <span class="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm font-medium">
                                    <i class="fas fa-layer-group mr-1"></i>
                                    {{ $allBookings->count() }} créneaux
                                </span>
                            @endif
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-bold mb-2">
                            @if($isMultiSlotSession)
                                Session #{{ $booking->booking_number }}
                            @else
                                Réservation #{{ $booking->booking_number }}
                            @endif
                        </h1>
                        <p class="text-blue-100 text-lg">{{ $booking->service->name }}</p>
                    </div>
                    
                    <div class="glass-card rounded-2xl p-4 text-gray-900">
                        <div class="text-sm text-gray-500 mb-1">Montant total</div>
                        <div class="text-3xl font-bold text-blue-600">
                            {{ number_format($isMultiSlotSession ? $totalSessionPrice : $booking->total_price, 2) }} €
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            @if($isMultiSlotSession)
                                <i class="fas fa-calendar-alt mr-1"></i>
                                Du {{ $allBookings->first()->start_datetime->format('d/m') }} au {{ $allBookings->last()->end_datetime->format('d/m/Y') }}
                            @else
                                <i class="fas fa-calendar-alt mr-1"></i>
                                {{ $booking->start_datetime->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-6 sm:py-8 -mt-4">
        <div class="max-w-5xl mx-auto">
            <!-- Flash Messages -->
            @include('bookings.partials.flash-messages')

            <!-- Multi-Slot Overview (if applicable) -->
            @if($isMultiSlotSession)
                <div class="glass-card rounded-2xl shadow-xl border border-white/50 p-4 sm:p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center mr-3">
                                <i class="fas fa-calendar-check text-white"></i>
                            </div>
                            Tous les créneaux
                        </h2>
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                        @foreach($allBookings as $sessionBooking)
                            @php $slotStatus = $statusConfig[$sessionBooking->status] ?? $statusConfig['pending']; @endphp
                            <a href="{{ route('bookings.show', $sessionBooking) }}" 
                               class="slot-card block rounded-xl p-3 border-2 {{ $sessionBooking->id === $booking->id ? 'border-blue-400 bg-blue-50 ring-2 ring-blue-200' : 'border-gray-100 bg-white hover:border-blue-200' }}">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-gray-600">#{{ Str::afterLast($sessionBooking->booking_number, 'BK') }}</span>
                                    <span class="w-2.5 h-2.5 rounded-full {{ str_replace(['bg-gradient-to-r from-', ' to-amber-400', ' to-orange-400', ' to-green-500', ' to-indigo-500', ' to-rose-500', ' to-slate-500'], ['bg-', '', '', '', '', '', ''], $slotStatus['bg']) }}"></span>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-bold text-gray-900">{{ $sessionBooking->start_datetime->format('d') }}</div>
                                    <div class="text-xs text-gray-500 uppercase">{{ $sessionBooking->start_datetime->locale('fr')->isoFormat('MMM') }}</div>
                                    <div class="text-xs font-medium text-blue-600 mt-1">
                                        {{ $sessionBooking->start_datetime->format('H:i') }}
                                    </div>
                                </div>
                                @if($sessionBooking->id === $booking->id)
                                    <div class="text-center mt-2">
                                        <span class="text-xs font-medium text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full">Actuel</span>
                                    </div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Booking Details Card -->
                    <div class="glass-card rounded-2xl shadow-xl border border-white/50 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-white px-5 py-4 border-b border-gray-100">
                            <h2 class="text-lg font-bold text-gray-900 flex items-center">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center mr-3">
                                    <i class="fas fa-info-circle text-white"></i>
                                </div>
                                Détails de la réservation
                            </h2>
                        </div>
                        
                        <div class="p-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Date -->
                                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4">
                                    <div class="flex items-center mb-2">
                                        <div class="w-8 h-8 rounded-lg bg-blue-500 flex items-center justify-center mr-2">
                                            <i class="fas fa-calendar-day text-white text-sm"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-600">Date</span>
                                    </div>
                                    <div class="text-base font-bold text-gray-900">
                                        {{ $booking->start_datetime->locale('fr')->isoFormat('dddd D MMMM') }}
                                    </div>
                                    <div class="text-sm text-gray-600">{{ $booking->start_datetime->format('Y') }}</div>
                                </div>
                                
                                <!-- Time -->
                                <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-xl p-4">
                                    <div class="flex items-center mb-2">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center mr-2">
                                            <i class="fas fa-clock text-white text-sm"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-600">Horaire</span>
                                    </div>
                                    <div class="text-base font-bold text-gray-900">
                                        {{ $booking->start_datetime->format('H:i') }} - {{ $booking->end_datetime->format('H:i') }}
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        @if($booking->service->price_type === 'heure' && $booking->service->quantity)
                                            {{ $booking->service->quantity }} heure{{ $booking->service->quantity > 1 ? 's' : '' }}
                                        @elseif($booking->service->price_type === 'jour' && $booking->service->quantity)
                                            {{ $booking->service->quantity }} jour{{ $booking->service->quantity > 1 ? 's' : '' }}
                                        @else
                                            {{ $booking->getDurationFormatted() }}
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Price -->
                                <div class="bg-gradient-to-br from-amber-50 to-yellow-50 rounded-xl p-4">
                                    <div class="flex items-center mb-2">
                                        <div class="w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center mr-2">
                                            <i class="fas fa-euro-sign text-white text-sm"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-600">Prix</span>
                                    </div>
                                    <div class="text-base font-bold text-green-600">{{ number_format($booking->total_price, 2) }} €</div>
                                    <div class="text-sm text-gray-600">
                                        @if($booking->service->price_type === 'heure')
                                            {{ number_format($booking->service->price, 2) }} €/h
                                        @elseif($booking->service->price_type === 'jour')
                                            {{ number_format($booking->service->price, 2) }} €/jour
                                        @else
                                            Forfait
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Created -->
                                <div class="bg-gradient-to-br from-purple-50 to-violet-50 rounded-xl p-4">
                                    <div class="flex items-center mb-2">
                                        <div class="w-8 h-8 rounded-lg bg-purple-500 flex items-center justify-center mr-2">
                                            <i class="fas fa-plus-circle text-white text-sm"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-600">Créée le</span>
                                    </div>
                                    <div class="text-base font-bold text-gray-900">{{ $booking->created_at->locale('fr')->isoFormat('D MMM YYYY') }}</div>
                                    <div class="text-sm text-gray-600">à {{ $booking->created_at->format('H:i') }}</div>
                                </div>
                            </div>
                            
                            <!-- Service Info -->
                            <div class="mt-6 pt-6 border-t border-gray-100">
                                <h3 class="font-bold text-gray-900 mb-3 flex items-center">
                                    <i class="fas fa-concierge-bell text-blue-500 mr-2"></i>
                                    Service réservé
                                </h3>
                                <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-blue-50 rounded-xl p-4">
                                    <div class="flex items-start gap-4">
                                        @if($booking->service->image)
                                            <img src="{{ asset('storage/' . $booking->service->image) }}" 
                                                 alt="{{ $booking->service->name }}"
                                                 class="w-16 h-16 rounded-xl object-cover flex-shrink-0">
                                        @else
                                            <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-cogs text-white text-xl"></i>
                                            </div>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-gray-900">{{ $booking->service->name }}</h4>
                                            @if($booking->service->description)
                                                <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ Str::limit($booking->service->description, 120) }}</p>
                                            @endif
                                            <a href="{{ route('services.show', $booking->service) }}" 
                                               class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium mt-2">
                                                Voir le service <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Timeline / Notifications -->
                    @if($booking->status === 'confirmed' && $booking->confirmed_at)
                        <div class="glass-card rounded-2xl shadow-lg border border-emerald-200 p-4 bg-gradient-to-r from-emerald-50 to-green-50">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-400 to-green-500 flex items-center justify-center mr-4 animate-float">
                                    <i class="fas fa-check-circle text-white text-xl"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-emerald-800">Réservation confirmée</div>
                                    <div class="text-sm text-emerald-600">Le {{ $booking->confirmed_at->locale('fr')->isoFormat('D MMMM YYYY à HH:mm') }}</div>
                                </div>
                            </div>
                        </div>
                    @elseif($booking->status === 'completed' && $booking->completed_at)
                        <div class="glass-card rounded-2xl shadow-lg border border-blue-200 p-4 bg-gradient-to-r from-blue-50 to-indigo-50">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center mr-4">
                                    <i class="fas fa-flag-checkered text-white text-xl"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-blue-800">Prestation terminée</div>
                                    <div class="text-sm text-blue-600">Le {{ $booking->completed_at->locale('fr')->isoFormat('D MMMM YYYY à HH:mm') }}</div>
                                </div>
                            </div>
                        </div>
                    @elseif($booking->status === 'cancelled' || $booking->status === 'refused')
                        <div class="glass-card rounded-2xl shadow-lg border border-red-200 p-4 bg-gradient-to-r from-red-50 to-rose-50">
                            <div class="flex items-start">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-400 to-rose-500 flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-times-circle text-white text-xl"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-red-800">
                                        {{ $booking->status === 'cancelled' ? 'Réservation annulée' : 'Réservation refusée' }}
                                    </div>
                                    @if($booking->cancelled_at)
                                        <div class="text-sm text-red-600">Le {{ $booking->cancelled_at->locale('fr')->isoFormat('D MMMM YYYY à HH:mm') }}</div>
                                    @endif
                                    @if($booking->cancellation_reason)
                                        <div class="mt-2 text-sm text-red-700 bg-red-100 rounded-lg px-3 py-2">
                                            <strong>Raison:</strong> {{ $booking->cancellation_reason }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Notes Section -->
                    @php
                        $cleanClientNotes = cleanNotesForDisplay($booking->client_notes);
                        $cleanPrestataireNotes = cleanNotesForDisplay($booking->prestataire_notes);
                    @endphp
                    @if($cleanClientNotes || $cleanPrestataireNotes)
                        <div class="glass-card rounded-2xl shadow-xl border border-white/50 overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-50 to-white px-5 py-4 border-b border-gray-100">
                                <h2 class="text-lg font-bold text-gray-900 flex items-center">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gray-500 to-slate-600 flex items-center justify-center mr-3">
                                        <i class="fas fa-sticky-note text-white"></i>
                                    </div>
                                    Notes
                                </h2>
                            </div>
                            <div class="p-5 space-y-4">
                                @if($cleanClientNotes)
                                    <div class="bg-gray-50 rounded-xl p-4">
                                        <div class="flex items-center mb-2">
                                            <i class="fas fa-user text-gray-400 mr-2"></i>
                                            <span class="text-sm font-medium text-gray-600">Note du client</span>
                                        </div>
                                        <p class="text-gray-800">{{ $cleanClientNotes }}</p>
                                    </div>
                                @endif
                                @if($cleanPrestataireNotes)
                                    <div class="bg-blue-50 rounded-xl p-4">
                                        <div class="flex items-center mb-2">
                                            <i class="fas fa-user-tie text-blue-500 mr-2"></i>
                                            <span class="text-sm font-medium text-gray-600">Note du prestataire</span>
                                        </div>
                                        <p class="text-gray-800">{{ $cleanPrestataireNotes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Right Column: Actions & Profile -->
                <div class="space-y-6">
                    <!-- Actions Card -->
                    <div class="glass-card rounded-2xl shadow-xl border border-white/50 overflow-hidden lg:sticky lg:top-4">
                        <div class="bg-gradient-to-r from-gray-50 to-white px-5 py-4 border-b border-gray-100">
                            <h2 class="text-lg font-bold text-gray-900 flex items-center">
                                <div class="w-10 h-10 rounded-xl {{ $currentStatus['bg'] }} flex items-center justify-center mr-3">
                                    <i class="fas {{ $currentStatus['icon'] }} text-white"></i>
                                </div>
                                Actions
                            </h2>
                        </div>
                        
                        <div class="p-5">
                            <!-- Current Status Display -->
                            <div class="text-center mb-5">
                                <span class="inline-flex items-center px-4 py-2 {{ $currentStatus['bg'] }} rounded-full text-sm font-bold text-white shadow-lg">
                                    <i class="fas {{ $currentStatus['icon'] }} mr-2"></i>
                                    {{ $currentStatus['label'] }}
                                </span>
                            </div>
                            
                            @if(isset($isMultiSlotSession) && $isMultiSlotSession)
                                <div class="mb-4 p-3 bg-blue-50 rounded-xl text-sm text-blue-700 text-center">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Session de <strong>{{ $allBookings->count() }}</strong> créneaux
                                </div>
                            @endif
                            
                            <!-- Action Buttons -->
                            <div class="space-y-3">
                                @if(auth()->user()->role === 'prestataire')
                                    @if($booking->status === 'pending')
                                        <form action="{{ route('bookings.confirm', $booking) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="action-btn w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl">
                                                <i class="fas fa-check-circle mr-2"></i>
                                                Confirmer la réservation
                                            </button>
                                        </form>
                                        <button onclick="openRefuseModal()" 
                                                class="action-btn w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl">
                                            <i class="fas fa-ban mr-2"></i>
                                            Refuser la réservation
                                        </button>
                                    @elseif($booking->status === 'confirmed')
                                        <form action="{{ route('bookings.complete', $booking) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="action-btn w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl">
                                                <i class="fas fa-flag-checkered mr-2"></i>
                                                Marquer comme terminée
                                            </button>
                                        </form>
                                    @endif
                                @endif
                                
                                @if(auth()->user()->role === 'client' && in_array($booking->status, ['pending', 'confirmed']))
                                    <button onclick="openCancelModal()" 
                                            class="action-btn w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl">
                                        <i class="fas fa-times-circle mr-2"></i>
                                        Annuler la réservation
                                    </button>
                                    
                                    @if(\Illuminate\Support\Facades\Route::has('client.payments.form') && function_exists('booking_online_payment_enabled') && booking_online_payment_enabled())
                                        @php
                                            $payReq = function_exists('normalize_payment_requirement_for_mode')
                                                ? normalize_payment_requirement_for_mode($booking->service->payment_requirement ?? 'none')
                                                : ($booking->service->payment_requirement ?? 'none');
                                            $presta = $booking->prestataire ?? $booking->service?->prestataire;
                                            $hasStripe = $presta && !empty($presta->stripe_account_id);
                                        @endphp
                                        @if($payReq !== 'none' && $hasStripe)
                                            @if($booking->payment_status === 'pending')
                                                <a href="{{ route('client.payments.form', $booking) }}"
                                                   class="action-btn w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl">
                                                    <i class="fas fa-credit-card mr-2"></i>
                                                    Payer maintenant
                                                </a>
                                            @elseif($booking->payment_status === 'deposit_paid')
                                                <a href="{{ route('client.payments.form', $booking) }}"
                                                   class="action-btn w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl">
                                                    <i class="fas fa-coins mr-2"></i>
                                                    Payer le solde
                                                </a>
                                            @endif
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile Card -->
                    <div class="glass-card rounded-2xl shadow-xl border border-white/50 overflow-hidden">
                        @if(auth()->user()->role === 'client')
                            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-5 py-4 text-white text-center">
                                <i class="fas fa-user-tie text-2xl mb-2"></i>
                                <h3 class="font-bold">Votre prestataire</h3>
                            </div>
                            <div class="p-5">
                                <div class="flex flex-col items-center text-center mb-4">
                                    <div class="relative mb-3">
                                        @if($booking->prestataire->photo)
                                            <img src="{{ asset('storage/' . $booking->prestataire->photo) }}" 
                                                 alt="{{ $booking->prestataire->user->name }}" 
                                                 class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg">
                                        @elseif($booking->prestataire->user->avatar)
                                            <img src="{{ asset('storage/' . $booking->prestataire->user->avatar) }}" 
                                                 alt="{{ $booking->prestataire->user->name }}" 
                                                 class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg">
                                        @else
                                            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center border-4 border-white shadow-lg">
                                                <span class="text-white font-bold text-2xl">{{ substr($booking->prestataire->user->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                        @if($booking->prestataire->isVerified())
                                            <div class="absolute -bottom-1 -right-1 w-7 h-7 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center border-2 border-white shadow">
                                                <i class="fas fa-check text-white text-xs"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <h4 class="font-bold text-gray-900 text-lg">{{ $booking->prestataire->user->name }}</h4>
                                    @if($booking->prestataire->isVerified())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 mt-1">
                                            <i class="fas fa-check mr-1"></i>Profil vérifié
                                        </span>
                                    @endif
                                    @if($booking->prestataire->location)
                                        <p class="text-gray-500 text-sm mt-2">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            {{ $booking->prestataire->location }}
                                        </p>
                                    @endif
                                </div>
                                
                                <div class="space-y-2">
                                    <a href="{{ route('prestataires.show', $booking->prestataire) }}" 
                                       class="w-full flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-medium rounded-xl transition-all">
                                        <i class="fas fa-user mr-2"></i> Voir le profil
                                    </a>
                                    <a href="{{ route('messaging.conversation', $booking->prestataire->user) }}" 
                                       class="w-full flex items-center justify-center px-4 py-2.5 bg-white border-2 border-blue-200 text-blue-600 hover:bg-blue-50 font-medium rounded-xl transition-all">
                                        <i class="fas fa-comment mr-2"></i> Envoyer un message
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="bg-gradient-to-r from-gray-600 to-slate-700 px-5 py-4 text-white text-center">
                                <i class="fas fa-user text-2xl mb-2"></i>
                                <h3 class="font-bold">Votre client</h3>
                            </div>
                            <div class="p-5">
                                <div class="flex flex-col items-center text-center mb-4">
                                    <div class="mb-3">
                                        @if($booking->client->user->avatar)
                                            <img src="{{ asset('storage/' . $booking->client->user->avatar) }}" 
                                                 alt="{{ $booking->client->user->name }}" 
                                                 class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg">
                                        @else
                                            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-gray-400 to-slate-500 flex items-center justify-center border-4 border-white shadow-lg">
                                                <span class="text-white font-bold text-2xl">{{ substr($booking->client->user->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <h4 class="font-bold text-gray-900 text-lg">{{ $booking->client->user->name }}</h4>
                                    <span class="text-gray-500 text-sm mt-1">
                                        <i class="fas fa-user-tag mr-1"></i>Client
                                    </span>
                                </div>
                                
                                <a href="{{ route('messaging.conversation', $booking->client->user) }}" 
                                   class="w-full flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-medium rounded-xl transition-all">
                                    <i class="fas fa-comment mr-2"></i> Envoyer un message
                                </a>
                            </div>
                        @endif
                        
                        @if($booking->status === 'completed' && auth()->user()->role === 'client')
                            <div class="border-t border-gray-100 p-5">
                                <a href="{{ route('reviews.create', ['prestataire' => $booking->prestataire->id, 'booking' => $booking->id]) }}" 
                                   class="w-full flex items-center justify-center px-4 py-3 bg-gradient-to-r from-amber-400 to-yellow-500 hover:from-amber-500 hover:to-yellow-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all">
                                    <i class="fas fa-star mr-2"></i> Laisser un avis
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('bookings.partials.modals')

@push('scripts')
<script>
    function openCancelModal() {
        document.getElementById('cancelModal').classList.remove('hidden');
    }

    function closeCancelModal() {
        document.getElementById('cancelModal').classList.add('hidden');
    }

    function openRefuseModal() {
        document.getElementById('refuseModal').classList.remove('hidden');
    }

    function closeRefuseModal() {
        document.getElementById('refuseModal').classList.add('hidden');
    }
</script>
@endpush

{{-- Rappel pour activer les notifications (contexte réservation) --}}
@include('components.notification-context-alert', ['context' => 'booking'])

@endsection
