<!-- Détails de la réservation -->
<div class="bg-white rounded-xl shadow border border-blue-200 p-3 sm:p-4">
    <div class="text-center mb-4">
        <h2 class="text-base sm:text-lg font-bold text-blue-800 flex items-center justify-center">
            <i class="fas fa-calendar-alt text-blue-500 mr-1.5"></i>
            Détails
        </h2>
    </div>
    
    <div class="space-y-3">
        <!-- Date et heure -->
        <div class="flex items-center justify-between py-2 border-b border-gray-100">
            <div class="flex items-center">
                <i class="fas fa-calendar text-blue-500 mr-2 w-4 text-sm"></i>
                <span class="text-gray-600 text-xs sm:text-sm">Date</span>
            </div>
            @if($booking->service->price_type === 'heure' && $booking->service->quantity)
                @php
                    $endTime = $booking->start_datetime->copy()->addHours($booking->service->quantity);
                @endphp
                <span class="font-medium text-gray-900 text-xs sm:text-sm">
                    {{ $booking->start_datetime->locale('fr')->isoFormat('dddd DD MMMM') }} à {{ $booking->start_datetime->format('H:i') }} - {{ $endTime->format('H:i') }}
                </span>
            @elseif($booking->service->price_type === 'jour' && $booking->service->quantity)
                @php
                    $endDate = $booking->start_datetime->copy()->addDays($booking->service->quantity - 1);
                @endphp
                <span class="font-medium text-gray-900 text-xs sm:text-sm">
                    {{ $booking->start_datetime->locale('fr')->isoFormat('dddd DD MMMM YYYY') }} 
                    @if($booking->service->quantity > 1)
                        - {{ $endDate->locale('fr')->isoFormat('dddd DD MMMM YYYY') }}
                    @endif
                </span>
            @else
                <span class="font-medium text-gray-900 text-xs sm:text-sm">
                    {{ $booking->start_datetime->locale('fr')->isoFormat('dddd DD MMMM') }} à {{ $booking->start_datetime->format('H:i') }} - {{ $booking->end_datetime->format('H:i') }}
                </span>
            @endif
        </div>
        
        <!-- Durée -->
        <div class="flex items-center justify-between py-2 border-b border-gray-100">
            <div class="flex items-center">
                <i class="fas fa-clock text-green-500 mr-2 w-4 text-sm"></i>
                <span class="text-gray-600 text-xs sm:text-sm">Durée</span>
            </div>
            @if($booking->service->price_type === 'heure' && $booking->service->quantity)
                <span class="font-medium text-gray-900 text-xs sm:text-sm">{{ $booking->service->quantity }} heures</span>
            @elseif($booking->service->price_type === 'jour' && $booking->service->quantity)
                <span class="font-medium text-gray-900 text-xs sm:text-sm">{{ $booking->service->quantity }} jour{{ $booking->service->quantity > 1 ? 's' : '' }}</span>
            @else
                <span class="font-medium text-gray-900 text-xs sm:text-sm">{{ $booking->getDurationFormatted() }}</span>
            @endif
        </div>
        
        <!-- Prix -->
        <div class="flex items-center justify-between py-2 border-b border-gray-100">
            <div class="flex items-center">
                <i class="fas fa-euro-sign text-yellow-500 mr-2 w-4 text-sm"></i>
                <span class="text-gray-600 text-xs sm:text-sm">Prix</span>
            </div>
            <span class="font-bold text-green-600 text-xs sm:text-sm">{{ number_format($booking->total_price, 2) }} €</span>
        </div>
        
        <!-- Créée le -->
        <div class="flex items-center justify-between py-2">
            <div class="flex items-center">
                <i class="fas fa-plus-circle text-purple-500 mr-2 w-4 text-sm"></i>
                <span class="text-gray-600 text-xs sm:text-sm">Créée le</span>
            </div>
            <span class="font-medium text-gray-900 text-xs sm:text-sm">{{ $booking->created_at->locale('fr')->isoFormat('DD MMMM YYYY') }}</span>
        </div>
    </div>
    
    <!-- Service info -->
    <div class="mt-4 pt-4 border-t border-gray-100 text-center">
        <h3 class="text-sm font-semibold text-blue-800 mb-2 flex items-center justify-center">
            <i class="fas fa-cogs text-blue-500 mr-1.5"></i>
            Service
        </h3>
        <div class="bg-blue-50 rounded-lg p-3 text-center">
            <h4 class="font-medium text-blue-900 text-sm">{{ $booking->service->name }}</h4>
            @if($booking->service->description)
                <p class="text-blue-700 mt-2 text-xs">{{ Str::limit($booking->service->description, 100) }}</p>
            @endif
            <div class="mt-2 text-xs">
                @if($booking->service->price_type === 'heure' && $booking->service->quantity)
                    <div class="text-blue-600 font-medium">
                        {{ number_format($booking->service->price, 2) }} €/heure × {{ $booking->service->quantity }} heures = 
                        <span class="font-bold">{{ number_format($booking->total_price, 2) }} €</span>
                    </div>
                @elseif($booking->service->price_type === 'jour' && $booking->service->quantity)
                    <div class="text-blue-600 font-medium">
                        {{ number_format($booking->service->price, 2) }} €/jour × {{ $booking->service->quantity }} jours = 
                        <span class="font-bold">{{ number_format($booking->total_price, 2) }} €</span>
                    </div>
                @else
                    <div class="text-blue-600 font-medium">
                        {{ number_format($booking->service->price, 2) }} €
                        @if($booking->service->price_type === 'heure')
                            /heure
                        @elseif($booking->service->price_type === 'jour')
                            /jour
                        @elseif($booking->service->price_type === 'projet')
                            (projet)
                        @elseif($booking->service->price_type === 'devis')
                            (sur devis)
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>