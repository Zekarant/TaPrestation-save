<!-- Détails de la réservation multiple -->
<div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6">
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-blue-800 flex items-center justify-center">
            <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
            Détails de la session
        </h2>
    </div>
    
    <div class="space-y-4">
        <!-- Créneaux totaux -->
        <div class="flex items-center justify-between py-3 border-b border-gray-100">
            <div class="flex items-center">
                <i class="fas fa-list text-blue-500 mr-3 w-5 text-base"></i>
                <span class="text-gray-600">Créneaux</span>
            </div>
            <span class="font-medium text-gray-900">{{ $allBookings->count() }} créneaux</span>
        </div>
        
        <!-- Période totale -->
        <div class="flex items-center justify-between py-3 border-b border-gray-100">
            <div class="flex items-center">
                <i class="fas fa-calendar-week text-green-500 mr-3 w-5 text-base"></i>
                <span class="text-gray-600">Période</span>
            </div>
            <div class="text-right">
                <div class="font-medium text-gray-900">{{ $allBookings->first()->start_datetime->locale('fr')->isoFormat('DD MMMM YYYY') }}</div>
                @if($allBookings->first()->start_datetime->format('Y-m-d') !== $allBookings->last()->start_datetime->format('Y-m-d'))
                    <div class="text-sm text-gray-500">au {{ $allBookings->last()->start_datetime->locale('fr')->isoFormat('DD MMMM YYYY') }}</div>
                @endif
            </div>
        </div>
        
        <!-- Durée totale -->
        @php
            // Use service quantity if available, otherwise calculate actual duration
            if($currentBooking->service->price_type === 'heure' && $currentBooking->service->quantity) {
                $totalDuration = $currentBooking->service->quantity * 60 * $allBookings->count();
            } elseif($currentBooking->service->price_type === 'jour' && $currentBooking->service->quantity) {
                // For daily services, calculate total days
                $totalDuration = $currentBooking->service->quantity * 24 * 60 * $allBookings->count();
            } else {
                $totalDuration = $allBookings->sum(function($booking) {
                    return $booking->start_datetime->diffInMinutes($booking->end_datetime);
                });
            }
            $hours = floor($totalDuration / 60);
            $minutes = $totalDuration % 60;
        @endphp
        <div class="flex items-center justify-between py-3 border-b border-gray-100">
            <div class="flex items-center">
                <i class="fas fa-clock text-orange-500 mr-3 w-5 text-base"></i>
                <span class="text-gray-600">Durée totale</span>
            </div>
            <span class="font-medium text-gray-900">
                @if($currentBooking->service->price_type === 'jour' && $currentBooking->service->quantity)
                    {{ $currentBooking->service->quantity * $allBookings->count() }} jour{{ ($currentBooking->service->quantity * $allBookings->count()) > 1 ? 's' : '' }}
                @else
                    @if($hours > 0)
                        {{ $hours }}h{{ $minutes > 0 ? sprintf('%02d', $minutes) : '' }}
                    @else
                        {{ $minutes }} min
                    @endif
                @endif
            </span>
        </div>
        
        <!-- Prix total -->
        <div class="flex items-center justify-between py-3 border-b border-gray-100">
            <div class="flex items-center">
                <i class="fas fa-euro-sign text-yellow-500 mr-3 w-5 text-base"></i>
                <span class="text-gray-600">Prix total</span>
            </div>
            <span class="font-bold text-green-600">{{ number_format($totalSessionPrice, 2) }} €</span>
        </div>
        
        <!-- Créée le -->
        <div class="flex items-center justify-between py-3">
            <div class="flex items-center">
                <i class="fas fa-plus-circle text-purple-500 mr-3 w-5 text-base"></i>
                <span class="text-gray-600">Créée le</span>
            </div>
            <span class="font-medium text-gray-900">{{ $currentBooking->created_at->locale('fr')->isoFormat('DD MMMM YYYY') }}</span>
        </div>
    </div>
    
    <!-- Service info -->
    <div class="mt-6 pt-6 border-t border-gray-100 text-center">
        <h3 class="text-lg font-semibold text-blue-800 mb-3 flex items-center justify-center">
            <i class="fas fa-cogs text-blue-500 mr-2"></i>
            Service
        </h3>
        <div class="bg-blue-50 rounded-lg p-4 text-center">
            <h4 class="font-medium text-blue-900">{{ $currentBooking->service->name }}</h4>
            @if($currentBooking->service->description)
                <p class="text-blue-700 mt-2">{{ Str::limit($currentBooking->service->description, 100) }}</p>
            @endif
            <div class="mt-2 text-sm">
                @if($currentBooking->service->price_type === 'heure' && $currentBooking->service->quantity)
                    <div class="text-blue-600 font-medium">
                        {{ number_format($currentBooking->service->price, 2) }} €/heure × {{ $currentBooking->service->quantity }} heures = 
                        <span class="font-bold">{{ number_format($currentBooking->service->price * $currentBooking->service->quantity, 2) }} € par créneau</span>
                    </div>
                    <div class="text-green-600 font-bold mt-1">
                        Total ({{ $allBookings->count() }} créneaux) : {{ number_format($totalSessionPrice, 2) }} €
                    </div>
                @elseif($currentBooking->service->price_type === 'jour' && $currentBooking->service->quantity)
                    <div class="text-blue-600 font-medium">
                        {{ number_format($currentBooking->service->price, 2) }} €/jour × {{ $currentBooking->service->quantity }} jours = 
                        <span class="font-bold">{{ number_format($currentBooking->service->price * $currentBooking->service->quantity, 2) }} € par créneau</span>
                    </div>
                    <div class="text-green-600 font-bold mt-1">
                        Total ({{ $allBookings->count() }} créneaux) : {{ number_format($totalSessionPrice, 2) }} €
                    </div>
                @else
                    <div class="text-blue-600 font-medium">
                        {{ number_format($currentBooking->service->price, 2) }} € par créneau
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Détail du créneau actuel -->
    <div class="mt-6 pt-6 border-t border-gray-100">
        <h3 class="text-lg font-semibold text-blue-800 mb-3 flex items-center justify-center">
            <i class="fas fa-eye text-green-500 mr-2"></i>
            Créneau actuel
        </h3>
        <div class="bg-green-50 rounded-lg p-4 text-center">
            @if($currentBooking->service->price_type === 'heure' && $currentBooking->service->quantity)
                @php
                    $endTime = $currentBooking->start_datetime->copy()->addHours($currentBooking->service->quantity);
                @endphp
                <div class="font-medium text-green-900">
                    {{ $currentBooking->start_datetime->locale('fr')->isoFormat('DD MMMM YYYY à H:i') }} - {{ $endTime->format('H:i') }}
                </div>
            @elseif($currentBooking->service->price_type === 'jour' && $currentBooking->service->quantity)
                @php
                    $endDate = $currentBooking->start_datetime->copy()->addDays($currentBooking->service->quantity - 1);
                @endphp
                <div class="font-medium text-green-900">
                    {{ $currentBooking->start_datetime->locale('fr')->isoFormat('DD MMMM YYYY') }}
                    @if($currentBooking->service->quantity > 1)
                        - {{ $endDate->locale('fr')->isoFormat('DD MMMM YYYY') }}
                    @endif
                </div>
            @else
                <div class="font-medium text-green-900">
                    {{ $currentBooking->start_datetime->locale('fr')->isoFormat('DD MMMM YYYY à H:i') }}
                </div>
                <div class="text-green-700 mt-1">
                    Fin prévue: {{ $currentBooking->end_datetime->format('H:i') }}
                </div>
            @endif
            <div class="text-green-600 mt-1 font-medium">
                @if($currentBooking->service->price_type === 'heure' && $currentBooking->service->quantity)
                    {{ $currentBooking->service->quantity }} heures
                @elseif($currentBooking->service->price_type === 'jour' && $currentBooking->service->quantity)
                    {{ $currentBooking->service->quantity }} jour{{ $currentBooking->service->quantity > 1 ? 's' : '' }}
                @else
                    {{ $currentBooking->getDurationFormatted() }}
                @endif
            </div>
            <div class="text-green-700 mt-2">
                Prix: <span class="font-bold">{{ number_format($currentBooking->total_price, 2) }} €</span>
            </div>
        </div>
    </div>
</div>