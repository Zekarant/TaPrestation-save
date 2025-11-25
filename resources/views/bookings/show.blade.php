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
@endphp

<div class="bg-blue-50 min-h-screen">
    <div class="container mx-auto px-2 sm:px-4 py-4 sm:py-6">
        <div class="max-w-5xl mx-auto">
            <!-- Header -->
            <div class="mb-4 sm:mb-6 text-center">
                @if($isMultiSlotSession)
                    <h1 class="text-xl sm:text-2xl font-extrabold text-blue-900 mb-1">Réservations multiples #{{ $booking->booking_number }}</h1>
                    <p class="text-sm sm:text-base text-blue-700">{{ $booking->service->name }}</p>
                    <p class="text-xs text-blue-600 font-medium mt-1">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        Du {{ $allBookings->first()->start_datetime->format('d/m/Y à H:i') }} 
                        au {{ $allBookings->last()->end_datetime->format('d/m/Y à H:i') }}
                        <span class="ml-1">({{ $allBookings->count() }} créneaux)</span>
                    </p>
                @else
                    <h1 class="text-xl sm:text-2xl font-extrabold text-blue-900 mb-1">Réservation {{ $booking->booking_number }}</h1>
                    <p class="text-sm sm:text-base text-blue-700">{{ $booking->service->name }}</p>
                    <p class="text-xs text-blue-600 font-medium mt-1">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        {{ $booking->start_datetime->format('d/m/Y à H:i') }}
                    </p>
                @endif
            </div>

            <!-- Flash Messages -->
            @include('bookings.partials.flash-messages')

            <!-- Multi-Slot Booking Overview -->
            @if($isMultiSlotSession)
                <div class="bg-white rounded-xl shadow border border-blue-200 p-3 sm:p-4 mb-4 sm:mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3 sm:mb-4">
                        <h2 class="text-base sm:text-lg font-bold text-blue-800 mb-0">
                            <i class="fas fa-calendar-check text-blue-500 mr-1.5"></i>
                            Créneaux réservés ({{ $allBookings->count() }})
                        </h2>
                        <div class="text-right">
                            <div class="text-xs text-gray-600">Prix total</div>
                            <div class="text-base sm:text-lg font-bold text-blue-600">{{ number_format($totalSessionPrice, 2) }} €</div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
                        @foreach($allBookings as $sessionBooking)
                            <div class="border-2 rounded-lg p-2 sm:p-3 @if($sessionBooking->id === $booking->id) bg-blue-50 border-blue-300 @else bg-white border-gray-200 @endif transition-all">
                                <div class="flex items-center justify-between mb-1.5 sm:mb-2">
                                    <div class="text-xs font-medium text-gray-900">
                                        #{{ $sessionBooking->booking_number }}
                                    </div>
                                    <span class="px-1.5 py-0.5 rounded-full text-xs font-medium
                                        @if($sessionBooking->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($sessionBooking->status === 'confirmed') bg-green-100 text-green-800
                                        @elseif($sessionBooking->status === 'completed') bg-blue-100 text-blue-800
                                        @elseif($sessionBooking->status === 'cancelled') bg-red-100 text-red-800
                                        @elseif($sessionBooking->status === 'refused') bg-gray-100 text-gray-800
                                        @endif">
                                        @if($sessionBooking->status === 'pending') En attente
                                        @elseif($sessionBooking->status === 'confirmed') Confirmée
                                        @elseif($sessionBooking->status === 'completed') Terminée
                                        @elseif($sessionBooking->status === 'cancelled') Annulée
                                        @elseif($sessionBooking->status === 'refused') Refusée
                                        @endif
                                    </span>
                                </div>
                                <div class="space-y-1 text-xs text-gray-600">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar text-blue-500 mr-1.5 w-3"></i>
                                        <span>{{ $sessionBooking->start_datetime->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-clock text-green-500 mr-1.5 w-3"></i>
                                        <span>{{ $sessionBooking->start_datetime->format('H:i') }} - {{ $sessionBooking->end_datetime->format('H:i') }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-euro-sign text-yellow-500 mr-1.5 w-3"></i>
                                        <span class="font-medium">{{ number_format($sessionBooking->total_price, 2) }} €</span>
                                    </div>
                                </div>
                                @if($sessionBooking->id === $booking->id)
                                    <div class="mt-1.5 text-xs text-blue-700 font-medium text-center">
                                        <i class="fas fa-eye mr-1"></i> Créneau actuel
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Quick navigation to other bookings -->
                    @if($relatedBookings->count() > 0)
                        <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200">
                            <div class="text-xs text-gray-600 mb-1.5">Navigation rapide :</div>
                            <div class="flex flex-wrap gap-1">
                                @foreach($allBookings as $sessionBooking)
                                    @if($sessionBooking->id !== $booking->id)
                                        <a href="{{ route('bookings.show', $sessionBooking) }}" 
                                           class="inline-flex items-center px-2 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs rounded-full transition-colors">
                                            <i class="fas fa-external-link-alt mr-1 text-xs"></i>
                                            #{{ $sessionBooking->booking_number }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Mobile Actions Section - Show at the top on mobile -->
            <div class="lg:hidden mb-4">
                @include('bookings.partials.status-actions', [
                    'booking' => $booking, 
                    'isMultiSlotSession' => $isMultiSlotSession,
                    'allBookings' => $allBookings ?? collect(),
                    'relatedBookings' => $relatedBookings ?? collect()
                ])
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-4 sm:mb-6">
                <!-- Booking Details Card -->
                <div class="lg:col-span-2">
                    @if($isMultiSlotSession)
                        @include('bookings.partials.booking-details-multi', ['currentBooking' => $booking, 'allBookings' => $allBookings, 'totalSessionPrice' => $totalSessionPrice])
                    @else
                        @include('bookings.partials.booking-details')
                    @endif
                </div>
                
                <!-- Right Column: Actions and User Profile (Hidden on mobile, shown on desktop) -->
                <div class="hidden lg:block space-y-4 sm:space-y-6">
                    <!-- Status and Actions -->
                    <div>
                        @include('bookings.partials.status-actions', [
                            'booking' => $booking, 
                            'isMultiSlotSession' => $isMultiSlotSession,
                            'allBookings' => $allBookings ?? collect(),
                            'relatedBookings' => $relatedBookings ?? collect()
                        ])
                    </div>
                    
                    <!-- User Profile Card -->
                    <div>
                        @include('bookings.partials.user-profile')
                    </div>
                </div>
            </div>
            
            <!-- User Profile Card - Show below details on mobile -->
            <div class="lg:hidden mb-4 sm:mb-6">
                @include('bookings.partials.user-profile')
            </div>
            
            <!-- Status Notifications -->
            @if($booking->status === 'confirmed' && $booking->confirmed_at)
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-2 sm:p-3 mb-4 sm:mb-6">
                    <div class="flex items-center justify-center">
                        <i class="fas fa-check-circle text-blue-500 mr-1.5"></i>
                        <span class="text-blue-800 font-medium text-xs sm:text-sm">Confirmée le {{ $booking->confirmed_at->format('d/m/Y à H:i') }}</span>
                    </div>
                </div>
            @elseif($booking->status === 'completed' && $booking->completed_at)
                <div class="bg-green-50 border border-green-200 rounded-xl p-2 sm:p-3 mb-4 sm:mb-6">
                    <div class="flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-500 mr-1.5"></i>
                        <span class="text-green-800 font-medium text-xs sm:text-sm">Terminé le {{ $booking->completed_at->format('d/m/Y à H:i') }}</span>
                    </div>
                </div>
            @elseif($booking->status === 'cancelled' || $booking->status === 'refused')
                <div class="bg-red-50 border border-red-200 rounded-xl p-2 sm:p-3 mb-4 sm:mb-6">
                    <div class="text-center">
                        <div class="flex flex-col items-center gap-1.5 mb-1.5">
                            <i class="fas fa-times-circle text-red-500"></i>
                            <span class="text-red-800 font-medium text-xs sm:text-sm">
                                @if($booking->status === 'cancelled')
                                    Annulée le {{ $booking->cancelled_at->format('d/m/Y à H:i') }}
                                @else
                                    Refusée le {{ $booking->cancelled_at->format('d/m/Y à H:i') }}
                                @endif
                            </span>
                        </div>
                        @if($booking->cancellation_reason)
                            <p class="text-red-700 text-xs mt-1"><strong>Raison:</strong> {{ $booking->cancellation_reason }}</p>
                        @endif
                    </div>
                </div>
            @endif
            
            <!-- Notes -->
            @php
                $cleanClientNotes = cleanNotesForDisplay($booking->client_notes);
                $cleanPrestataireNotes = cleanNotesForDisplay($booking->prestataire_notes);
            @endphp
            @if($cleanClientNotes || $cleanPrestataireNotes)
                <div class="bg-white rounded-xl shadow border border-blue-200 p-3 sm:p-4">
                    <h3 class="text-base sm:text-lg font-bold text-blue-800 mb-3 sm:mb-4 text-center">Notes</h3>
                    
                    <div class="space-y-3 sm:space-y-4">
                        @if($cleanClientNotes)
                            <div class="text-center">
                                <h4 class="text-sm font-medium text-gray-700 mb-1.5 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-400 mr-1.5"></i>
                                    Client
                                </h4>
                                <p class="text-gray-600 bg-gray-50 p-2 sm:p-3 rounded-lg mx-auto max-w-md text-xs sm:text-sm">{{ $cleanClientNotes }}</p>
                            </div>
                        @endif
                        
                        @if($cleanPrestataireNotes)
                            <div class="text-center">
                                <h4 class="text-sm font-medium text-gray-700 mb-1.5 flex items-center justify-center">
                                    <i class="fas fa-user-tie text-blue-500 mr-1.5"></i>
                                    Prestataire
                                </h4>
                                <p class="text-gray-600 bg-blue-50 p-2 sm:p-3 rounded-lg mx-auto max-w-md text-xs sm:text-sm">{{ $cleanPrestataireNotes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
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

@endsection