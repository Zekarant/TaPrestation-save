<div class="mb-8">
    <!-- Title Section -->
    <div class="text-center mb-6">
        <h1 class="text-4xl font-extrabold text-blue-900 mb-2">Détails de la réservation</h1>
        <div class="flex items-center justify-center gap-4">
            <p class="text-lg text-blue-700 font-semibold">Numéro: {{ $booking->booking_number }}</p>
            <span class="px-3 py-1 rounded-full text-sm font-medium
                @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                @elseif($booking->status === 'confirmed') bg-green-100 text-green-800
                @elseif($booking->status === 'completed') bg-blue-100 text-blue-800
                @elseif($booking->status === 'cancelled') bg-red-100 text-red-800
                @elseif($booking->status === 'refused') bg-gray-100 text-gray-800
                @endif">
                @if($booking->status === 'pending') En attente
                @elseif($booking->status === 'confirmed') Confirmée
                @elseif($booking->status === 'completed') Terminée
                @elseif($booking->status === 'cancelled') Annulée
                @elseif($booking->status === 'refused') Refusée
                @endif
            </span>
        </div>
    </div>
    
    <!-- Navigation -->
    <div class="flex justify-center">
        <a href="{{ route('bookings.index') }}" 
           class="inline-flex items-center bg-white hover:bg-gray-50 text-blue-800 font-semibold px-6 py-3 rounded-lg shadow-md border border-blue-200 transition duration-200 hover:shadow-lg">
            <i class="fas fa-arrow-left mr-2"></i> 
            Retour aux réservations
        </a>
    </div>
</div>