@extends('layouts.prestataire')

@section('title', 'Inventaire - ' . Str::limit($urgentSale->title, 30))

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('prestataire.reservations.index') }}" class="text-gray-500 hover:text-gray-700 text-sm flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Retour aux réservations
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Inventaire</h1>
    </div>
    
    <!-- Produit -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <div class="flex gap-4">
            @php
                $firstImage = is_array($urgentSale->images) && count($urgentSale->images) > 0 ? $urgentSale->images[0] : null;
            @endphp
            @if($firstImage)
                <img src="{{ filter_var($firstImage, FILTER_VALIDATE_URL) ? $firstImage : asset('storage/' . $firstImage) }}" 
                     alt="{{ $urgentSale->title }}" 
                     class="w-20 h-20 object-cover rounded-lg">
            @else
                <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            @endif
            <div class="flex-1">
                <h2 class="font-bold text-lg text-gray-900">{{ $urgentSale->title }}</h2>
                <p class="text-red-600 font-bold text-lg">{{ number_format($urgentSale->price, 2) }}€</p>
                <a href="{{ route('prestataire.urgent-sales.edit', $urgentSale) }}" class="text-sm text-blue-600 hover:underline">
                    Modifier l'annonce →
                </a>
            </div>
        </div>
    </div>
    
    <!-- Stats inventaire -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ $urgentSale->quantity }}</div>
            <div class="text-sm text-gray-500">Stock initial</div>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
            <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-blue-600">{{ $urgentSale->reserved_quantity }}</div>
            <div class="text-sm text-blue-700">Réservés</div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-green-600">{{ $urgentSale->sold_quantity }}</div>
            <div class="text-sm text-green-700">Vendus</div>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
            <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-yellow-600">{{ $urgentSale->available_quantity }}</div>
            <div class="text-sm text-yellow-700">Disponibles</div>
        </div>
    </div>
    
    <!-- Barre de progression -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h3 class="font-semibold text-gray-900 mb-3">Progression des ventes</h3>
        <div class="w-full bg-gray-200 rounded-full h-6 overflow-hidden">
            @php
                $total = $urgentSale->quantity ?: 1;
                $soldPercent = ($urgentSale->sold_quantity / $total) * 100;
                $reservedPercent = ($urgentSale->reserved_quantity / $total) * 100;
            @endphp
            <div class="h-full flex">
                <div class="bg-green-500 h-full transition-all" style="width: {{ $soldPercent }}%"></div>
                <div class="bg-blue-500 h-full transition-all" style="width: {{ $reservedPercent }}%"></div>
            </div>
        </div>
        <div class="flex gap-4 mt-2 text-sm">
            <span class="flex items-center gap-1">
                <span class="w-3 h-3 bg-green-500 rounded"></span> Vendus
            </span>
            <span class="flex items-center gap-1">
                <span class="w-3 h-3 bg-blue-500 rounded"></span> Réservés
            </span>
            <span class="flex items-center gap-1">
                <span class="w-3 h-3 bg-gray-200 rounded"></span> Disponibles
            </span>
        </div>
    </div>
    
    <!-- Historique des réservations -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900">Historique des réservations</h3>
        </div>
        
        @if($reservations->isEmpty())
            <div class="p-8 text-center text-gray-500">
                Aucune réservation pour ce produit
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($reservations as $reservation)
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                    <span class="font-bold text-gray-600">{{ strtoupper(substr($reservation->client->name, 0, 1)) }}</span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $reservation->client->name }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $reservation->quantity }} unité(s) • {{ $reservation->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $reservation->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $reservation->status === 'confirmed' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $reservation->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $reservation->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                ">
                                    {{ $reservation->status_label }}
                                </span>
                                
                                @if($reservation->status === 'pending')
                                    <form action="{{ route('prestataire.reservations.confirm', $reservation) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            Confirmer
                                        </button>
                                    </form>
                                @endif
                                
                                @if($reservation->status === 'confirmed')
                                    <form action="{{ route('prestataire.reservations.complete', $reservation) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                            Vendu
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="px-5 py-4 border-t border-gray-200">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
