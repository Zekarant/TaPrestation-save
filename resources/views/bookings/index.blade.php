@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-100">
<div class="container mx-auto px-4 py-6 sm:py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Mes Réservations</h1>
            @if(auth()->user()->role === 'client')
                <a href="{{ route('services.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 sm:px-6 sm:py-2.5 rounded-lg transition duration-200 text-sm sm:text-base">
                    Nouvelle Réservation
                </a>
            @endif
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if($bookings->count() > 0)
            <div class="grid gap-4 sm:gap-6">
                @foreach($bookings as $booking)
                    <div class="bg-white rounded-lg shadow-lg border border-blue-200 p-4 sm:p-6">
                        <div class="flex flex-col lg:flex-row justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 mb-3 sm:mb-4">
                                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900">
                                        {{ $booking->service->name }}
                                    </h3>
                                    <span class="px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-full text-xs sm:text-sm font-medium whitespace-nowrap
                                        @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($booking->status === 'confirmed') bg-blue-100 text-blue-800
                                        @elseif($booking->status === 'completed') bg-green-100 text-green-800
                                        @elseif($booking->status === 'cancelled') bg-red-100 text-red-800
                                        @endif">
                                        @if($booking->status === 'pending') En attente
                                        @elseif($booking->status === 'confirmed') Confirmée
                                        @elseif($booking->status === 'completed') Terminée
                                        @elseif($booking->status === 'cancelled') Annulée
                                        @endif
                                    </span>
                                </div>
                                
                                <div class="text-gray-600 mb-2 text-sm sm:text-base">
                                    <strong>Numéro de réservation:</strong> {{ $booking->booking_number }}
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 text-xs sm:text-sm text-gray-600">
                                    <div>
                                        <strong>Date et heure:</strong><br>
                                        {{ $booking->start_datetime->format('d/m/Y à H:i') }}
                                        @if($booking->end_datetime)
                                            - {{ $booking->end_datetime->format('H:i') }}
                                        @endif
                                    </div>
                                    
                                    @if(auth()->user()->role === 'client')
                                        <div>
                                            <strong>Prestataire:</strong><br>
                                            {{ $booking->prestataire->user->name }}
                                        </div>
                                    @else
                                        <div>
                                            <strong>Client:</strong><br>
                                            {{ $booking->client->user->name }}
                                        </div>
                                    @endif
                                    
                                    <div>
                                        <strong>Prix:</strong><br>
                                        {{ number_format($booking->total_price, 2) }} €
                                    </div>
                                    
                                    @if($booking->status === 'confirmed' && $booking->confirmed_at)
                                        <div>
                                            <strong>Confirmée le:</strong><br>
                                            {{ $booking->confirmed_at->format('d/m/Y à H:i') }}
                                        </div>
                                    @endif
                                </div>
                                
                                @if($booking->client_notes)
                                    <div class="mt-3 sm:mt-4 p-3 bg-blue-50 rounded border border-blue-200">
                                        <strong class="text-xs sm:text-sm text-blue-700">Notes du client:</strong>
                                        <p class="text-xs sm:text-sm text-blue-600 mt-1">{{ $booking->client_notes }}</p>
                                    </div>
                                @endif
                                
                                @if($booking->prestataire_notes)
                                    <div class="mt-3 sm:mt-4 p-3 bg-blue-50 rounded border border-blue-200">
                                        <strong class="text-xs sm:text-sm text-blue-700">Notes du prestataire:</strong>
                                        <p class="text-xs sm:text-sm text-blue-600 mt-1">{{ $booking->prestataire_notes }}</p>
                                    </div>
                                @endif
                                
                                @if($booking->status === 'cancelled' && $booking->cancellation_reason)
                                    <div class="mt-3 sm:mt-4 p-3 bg-red-50 rounded">
                                        <strong class="text-xs sm:text-sm text-red-700">Raison de l'annulation:</strong>
                                        <p class="text-xs sm:text-sm text-red-600 mt-1">{{ $booking->cancellation_reason }}</p>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex flex-row lg:flex-col gap-2 lg:gap-3 lg:ml-4 w-full lg:w-auto">
                                <a href="{{ route('bookings.show', $booking) }}" 
                                   class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-2 sm:px-4 sm:py-2.5 rounded text-xs sm:text-sm transition duration-200 text-center border border-blue-300 flex-1 lg:flex-none">
                                    Voir détails
                                </a>
                                
                                @if(auth()->user()->role === 'prestataire')
                                    @if($booking->status === 'pending')
                                        <form action="{{ route('bookings.confirm', $booking) }}" method="POST" class="inline flex-1 lg:flex-none">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 sm:px-4 sm:py-2.5 rounded text-xs sm:text-sm transition duration-200 w-full">
                                                Confirmer
                                            </button>
                                        </form>
                                    @elseif($booking->status === 'confirmed')
                                        <form action="{{ route('bookings.complete', $booking) }}" method="POST" class="inline flex-1 lg:flex-none">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 sm:px-4 sm:py-2.5 rounded text-xs sm:text-sm transition duration-200 w-full">
                                                Marquer terminé
                                            </button>
                                        </form>
                                    @endif
                                @endif
                                
                                @if(in_array($booking->status, ['pending', 'confirmed']))
                                    <button onclick="openCancelModal({{ $booking->id }})" 
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 sm:px-4 sm:py-2.5 rounded text-xs sm:text-sm transition duration-200 flex-1 lg:flex-none">
                                        Annuler
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-6 sm:mt-8">
                {{ $bookings->links() }}
            </div>
        @else
            <div class="text-center py-10 sm:py-12">
                <div class="text-gray-500 text-base sm:text-lg mb-4">
                    @if(auth()->user()->role === 'client')
                        Vous n'avez encore aucune réservation.
                    @else
                        Vous n'avez encore reçu aucune réservation.
                    @endif
                </div>
                @if(auth()->user()->role === 'client')
                    <a href="{{ route('services.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 sm:px-6 sm:py-3 rounded-lg transition duration-200 text-sm sm:text-base">
                        Découvrir les services
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
</div>

<!-- Modal d'annulation -->
<div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-blue-200">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4">Annuler la réservation</h3>
                <form id="cancelForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Raison de l'annulation *
                        </label>
                        <textarea id="cancellation_reason" name="cancellation_reason" rows="3" required
                                  class="w-full px-3 py-2 sm:px-4 sm:py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm sm:text-base"
                                  placeholder="Veuillez expliquer la raison de l'annulation..."></textarea>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="submit" form="cancelForm"
                        class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:text-sm">
                    Confirmer l'annulation
                </button>
                <button type="button" onclick="closeCancelModal()" 
                        class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                    Annuler
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openCancelModal(bookingId) {
    document.getElementById('cancelForm').action = `/bookings/${bookingId}/cancel`;
    document.getElementById('cancelModal').classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
    document.getElementById('cancellation_reason').value = '';
}

// Fermer le modal en cliquant à l'extérieur
document.getElementById('cancelModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCancelModal();
    }
});
</script>
@endsection