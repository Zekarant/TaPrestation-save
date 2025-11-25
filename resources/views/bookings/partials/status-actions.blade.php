<div class="bg-white rounded-xl shadow border border-blue-200 p-3 sm:p-4">
    <div class="text-center mb-4">
        <h2 class="text-base sm:text-lg font-bold text-blue-800 flex items-center justify-center mb-3">
            @if(isset($isMultiSlotSession) && $isMultiSlotSession)
                <span class="text-xs font-normal text-gray-600 ml-1">(Session {{ $allBookings->count() }} créneaux)</span>
            @endif
        </h2>
        
        <!-- Current Status -->
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium
            @if($booking->status === 'pending') bg-yellow-100 text-yellow-800
            @elseif($booking->status === 'confirmed') bg-green-100 text-green-800
            @elseif($booking->status === 'completed') bg-blue-100 text-blue-800
            @elseif($booking->status === 'cancelled') bg-red-100 text-red-800
            @elseif($booking->status === 'refused') bg-gray-100 text-gray-800
            @endif">
            @if($booking->status === 'pending') 
                <i class="fas fa-clock mr-1"></i> En attente
            @elseif($booking->status === 'confirmed') 
                <i class="fas fa-check-circle mr-1"></i> Confirmée
            @elseif($booking->status === 'completed') 
                <i class="fas fa-check-double mr-1"></i> Terminée
            @elseif($booking->status === 'cancelled') 
                <i class="fas fa-times-circle mr-1"></i> Annulée
            @elseif($booking->status === 'refused') 
                <i class="fas fa-ban mr-1"></i> Refusée
            @endif
        </span>
        
        @if(isset($isMultiSlotSession) && $isMultiSlotSession)
            <!-- Multi-slot session warning -->
            <div class="mt-3 p-2 bg-blue-50 rounded-lg text-xs text-blue-700">
                <i class="fas fa-info-circle mr-1"></i>
                <strong>Session multi-créneaux:</strong> Les actions s'appliquent à ce créneau uniquement.
                @if(isset($relatedBookings) && $relatedBookings->count() > 0)
                    Pour gérer tous les créneaux, visitez chaque réservation individuellement.
                @endif
            </div>
        @endif
    </div>
    
    <!-- Action Buttons -->
    <div class="flex flex-wrap gap-2 justify-center">
        @if(auth()->user()->role === 'prestataire')
            @if($booking->status === 'pending')
                <form action="{{ route('bookings.confirm', $booking) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-green-500 transition-colors shadow hover:shadow-md">
                        <i class="fas fa-check mr-1"></i> 
                        @if(isset($isMultiSlotSession) && $isMultiSlotSession)
                            Confirmer ce créneau
                        @else
                            Confirmer
                        @endif
                    </button>
                </form>
                <button onclick="openRefuseModal()" 
                        class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-red-500 transition-colors shadow hover:shadow-md">
                    <i class="fas fa-ban mr-1"></i> 
                    @if(isset($isMultiSlotSession) && $isMultiSlotSession)
                        Refuser ce créneau
                    @else
                        Refuser
                    @endif
                </button>
            @elseif($booking->status === 'confirmed')
                <form action="{{ route('bookings.complete', $booking) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500 transition-colors shadow hover:shadow-md">
                        <i class="fas fa-check-double mr-1"></i> 
                        @if(isset($isMultiSlotSession) && $isMultiSlotSession)
                            Marquer ce créneau terminé
                        @else
                            Marquer terminé
                        @endif
                    </button>
                </form>
            @endif
        @endif
        
        @if(auth()->user()->role === 'client' && in_array($booking->status, ['pending', 'confirmed']))
            <button onclick="openCancelModal()" 
                    class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-red-500 transition-colors shadow hover:shadow-md">
                <i class="fas fa-times mr-1"></i> 
                @if(isset($isMultiSlotSession) && $isMultiSlotSession)
                    Annuler ce créneau
                @else
                    Annuler
                @endif
            </button>
        @endif
    </div>
    
    @if(isset($isMultiSlotSession) && $isMultiSlotSession && isset($relatedBookings) && $relatedBookings->count() > 0)
        <!-- Quick actions for related bookings -->
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="text-xs text-gray-600 mb-2 text-center">Actions rapides sur les autres créneaux :</div>
            <div class="flex flex-wrap gap-1 justify-center">
                @foreach($relatedBookings as $relatedBooking)
                    <a href="{{ route('bookings.show', $relatedBooking) }}" 
                       class="inline-flex items-center px-2 py-1 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs rounded-full transition-colors"
                       title="{{ $relatedBooking->start_datetime->format('d/m/Y à H:i') }} - Statut: {{ $relatedBooking->status }}">
                        #{{ $relatedBooking->booking_number }}
                        <span class="ml-1 w-2 h-2 rounded-full
                            @if($relatedBooking->status === 'pending') bg-yellow-400
                            @elseif($relatedBooking->status === 'confirmed') bg-green-400
                            @elseif($relatedBooking->status === 'completed') bg-blue-400
                            @elseif($relatedBooking->status === 'cancelled') bg-red-400
                            @elseif($relatedBooking->status === 'refused') bg-gray-400
                            @endif"></span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>