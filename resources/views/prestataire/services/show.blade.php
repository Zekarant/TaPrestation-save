@extends('layouts.app')

@section('title', $service->title . ' - Mon Service')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
    <div class="container mx-auto px-4 py-6 sm:py-8">
        <div class="max-w-5xl mx-auto">
            
            {{-- En-tête avec navigation --}}
            <div class="mb-6">
                <a href="{{ route('prestataire.services.index') }}" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 transition mb-4">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour à mes services
                </a>
                
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $service->title }}</h1>
                        <p class="text-gray-500 mt-1">
                            Créé le {{ $service->created_at->format('d/m/Y') }}
                            @if($service->reservable)
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    ✓ Réservable
                                </span>
                            @else
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    Non réservable
                                </span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="flex gap-3">
                        <a href="{{ route('prestataire.services.edit', $service) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Modifier
                        </a>
                        
                        <form action="{{ route('prestataire.services.destroy', $service) }}" method="POST" 
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce service ? Cette action est irréversible.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 font-medium rounded-lg border border-red-200 transition">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Colonne principale --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Images du service --}}
                    @if($service->images->count() > 0)
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-4">
                            @foreach($service->images as $image)
                            <div class="aspect-square rounded-lg overflow-hidden bg-gray-100">
                                <img src="{{ Storage::url($image->image_path) }}" 
                                     alt="{{ $service->title }}" 
                                     class="w-full h-full object-cover hover:scale-105 transition duration-300">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 text-center">
                        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-gray-500">Aucune image pour ce service</p>
                        <a href="{{ route('prestataire.services.edit', $service) }}" class="text-blue-600 hover:underline text-sm mt-2 inline-block">
                            Ajouter des images
                        </a>
                    </div>
                    @endif
                    
                    {{-- Description --}}
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Description
                        </h2>
                        <div class="prose prose-sm max-w-none text-gray-600">
                            {!! nl2br(e($service->description)) !!}
                        </div>
                    </div>
                    
                    {{-- Catégories --}}
                    @if($service->categories->count() > 0)
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            Catégories
                        </h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($service->categories as $category)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                {{ $category->name }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    {{-- Réservations récentes --}}
                    @if($service->bookings->count() > 0)
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Réservations récentes
                        </h2>
                        <div class="space-y-3">
                            @foreach($service->bookings->take(5) as $booking)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $booking->client->name ?? 'Client' }}</p>
                                    <p class="text-sm text-gray-500">{{ $booking->start_date?->format('d/m/Y H:i') ?? 'Date non définie' }}</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                    @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                    @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($booking->status === 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-600 @endif">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                        
                        @if($service->bookings->count() > 5)
                        <a href="{{ route('prestataire.bookings.index') }}" class="block text-center text-blue-600 hover:underline mt-4">
                            Voir toutes les réservations
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
                
                {{-- Colonne latérale --}}
                <div class="space-y-6">
                    
                    {{-- Statistiques --}}
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">📊 Statistiques</h2>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Total réservations</span>
                                <span class="text-xl font-bold text-gray-900">{{ $stats['total_bookings'] ?? 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Confirmées</span>
                                <span class="text-xl font-bold text-green-600">{{ $stats['confirmed_bookings'] ?? 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">En attente</span>
                                <span class="text-xl font-bold text-yellow-600">{{ $stats['pending_bookings'] ?? 0 }}</span>
                            </div>
                            <hr class="border-gray-200">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Revenus totaux</span>
                                <span class="text-xl font-bold text-blue-600">{{ number_format($stats['total_revenue'] ?? 0, 2) }} €</span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Informations tarif --}}
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">💰 Tarification</h2>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-blue-600">{{ number_format($service->price ?? 0, 2) }} €</p>
                            <p class="text-gray-500 text-sm mt-1">
                                @if($service->price_type === 'hourly')
                                    par heure
                                @elseif($service->price_type === 'daily')
                                    par jour
                                @else
                                    prix fixe
                                @endif
                            </p>
                        </div>
                        
                        @if($service->delivery_time)
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Délai de livraison</span>
                                <span class="font-medium">{{ $service->delivery_time }} jours</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    {{-- Localisation --}}
                    @if($service->address)
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">📍 Localisation</h2>
                        <p class="text-gray-600">{{ $service->address }}</p>
                    </div>
                    @endif
                    
                    {{-- Lien public --}}
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl shadow-lg p-6 text-white">
                        <h2 class="text-lg font-bold mb-2">🔗 Partager ce service</h2>
                        <p class="text-sm opacity-90 mb-4">Partagez ce lien avec vos clients potentiels</p>
                        <div class="flex gap-2">
                            <input type="text" readonly value="{{ route('services.show', $service) }}" 
                                   class="flex-1 px-3 py-2 bg-white/20 rounded-lg text-white placeholder-white/70 text-sm border border-white/30"
                                   id="serviceUrl">
                            <button onclick="navigator.clipboard.writeText(document.getElementById('serviceUrl').value); this.textContent='✓'" 
                                    class="px-4 py-2 bg-white text-blue-600 font-medium rounded-lg hover:bg-blue-50 transition">
                                Copier
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
