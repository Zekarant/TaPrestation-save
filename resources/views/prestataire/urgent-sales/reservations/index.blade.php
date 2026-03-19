@extends('layouts.prestataire')

@section('title', 'Réservations - Ventes Urgentes')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Gestion des réservations</h1>
        <p class="text-gray-600 mt-1">Gérez les demandes de réservation pour vos annonces</p>
    </div>
    
    <!-- Stats rapides -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-6">
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
            <div class="text-sm text-yellow-700">En attente</div>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $stats['confirmed'] }}</div>
            <div class="text-sm text-blue-700">Réservées</div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</div>
            <div class="text-sm text-green-700">Vendues</div>
        </div>
    </div>
    
    <!-- Filtres -->
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="{{ route('prestataire.reservations.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $status === 'all' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Toutes
        </a>
        <a href="{{ route('prestataire.reservations.index', ['status' => 'pending']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $status === 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            En attente ({{ $stats['pending'] }})
        </a>
        <a href="{{ route('prestataire.reservations.index', ['status' => 'confirmed']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $status === 'confirmed' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Réservées ({{ $stats['confirmed'] }})
        </a>
        <a href="{{ route('prestataire.reservations.index', ['status' => 'completed']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $status === 'completed' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Vendues ({{ $stats['completed'] }})
        </a>
    </div>
    
    <!-- Messages -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-xl">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-xl">
            {{ session('error') }}
        </div>
    @endif
    
    <!-- Liste des réservations -->
    @if($reservations->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <p class="text-gray-500">Aucune réservation pour le moment</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($reservations as $reservation)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-4 sm:p-5">
                        <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                            <!-- Info produit -->
                            <div class="flex-1">
                                <div class="flex items-start gap-3">
                                    <!-- Status badge -->
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $reservation->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $reservation->status === 'confirmed' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $reservation->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $reservation->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                    ">
                                        {{ $reservation->status_label }}
                                    </span>
                                    
                                    <div class="flex-1">
                                        <a href="{{ route('prestataire.urgent-sales.show', $reservation->urgentSale) }}" class="font-semibold text-gray-900 hover:text-red-600">
                                            {{ Str::limit($reservation->urgentSale->title, 50) }}
                                        </a>
                                        <div class="text-sm text-gray-500 mt-1">
                                            Quantité demandée : <span class="font-bold text-gray-900">{{ $reservation->quantity }}</span>
                                            • Total : <span class="font-bold text-red-600">{{ number_format($reservation->quantity * $reservation->urgentSale->price, 2) }}€</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Client info avec lien profil + icône message -->
                                <div class="mt-3 flex items-center gap-3 text-sm">
                                    <div class="flex items-center gap-2 flex-1">
                                        <a href="{{ route('users.public.show', $reservation->client) }}" class="flex items-center gap-2 group">
                                            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center group-hover:bg-red-100 transition">
                                                <span class="font-bold text-gray-600 text-xs group-hover:text-red-600">{{ strtoupper(substr($reservation->client->name, 0, 1)) }}</span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900 group-hover:text-red-600 transition">{{ $reservation->client->name }}</div>
                                                <div class="text-gray-500 text-xs">{{ $reservation->client->email }}</div>
                                            </div>
                                        </a>
                                    </div>
                                    
                                    <!-- Icône message -->
                                    <a href="{{ route('messaging.show', $reservation->client) }}" 
                                       class="flex items-center justify-center w-9 h-9 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 transition" 
                                       title="Envoyer un message à {{ $reservation->client->name }}">
                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                    </a>
                                    
                                    <!-- Lien profil -->
                                    <a href="{{ route('users.public.show', $reservation->client) }}" 
                                       class="flex items-center justify-center w-9 h-9 rounded-full bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-gray-700 transition" 
                                       title="Voir le profil de {{ $reservation->client->name }}">
                                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </a>
                                </div>
                                
                                @if($reservation->message)
                                    <div class="mt-3 bg-gray-50 rounded-lg p-3 text-sm text-gray-700">
                                        <span class="font-medium">Message :</span> {{ $reservation->message }}
                                    </div>
                                @endif
                                
                                <div class="mt-2 text-xs text-gray-400">
                                    Demande reçue {{ $reservation->created_at->diffForHumans() }}
                                    @if($reservation->confirmed_at)
                                        • Confirmée {{ $reservation->confirmed_at->diffForHumans() }}
                                    @endif
                                </div>
                                
                                {{-- Bloc notation client (réservation terminée) --}}
                                @if($reservation->status === 'completed')
                                    <div class="mt-4 border-t border-gray-100 pt-4">
                                        @if($reservation->client_rated_at)
                                            {{-- Déjà noté --}}
                                            <div class="flex items-center gap-2 text-sm">
                                                <span class="text-gray-500">Votre note :</span>
                                                <div class="flex items-center gap-0.5">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <svg class="w-4 h-4 {{ $i <= $reservation->client_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                    @endfor
                                                </div>
                                                @if($reservation->client_rating_comment)
                                                    <span class="text-gray-500 text-xs ml-1">— {{ Str::limit($reservation->client_rating_comment, 50) }}</span>
                                                @endif
                                            </div>
                                        @else
                                            {{-- Formulaire de notation --}}
                                            <form action="{{ route('prestataire.reservations.rate-client', $reservation) }}" method="POST" class="space-y-3">
                                                @csrf
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-medium text-gray-700">⭐ Noter le client :</span>
                                                    <div class="flex items-center gap-1" x-data="{ rating: 0, hover: 0 }">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <button type="button" 
                                                                    x-on:click="rating = {{ $i }}; $refs.ratingInput{{ $reservation->id }}.value = {{ $i }}"
                                                                    x-on:mouseenter="hover = {{ $i }}"
                                                                    x-on:mouseleave="hover = 0"
                                                                    class="focus:outline-none transition transform hover:scale-110">
                                                                <svg class="w-6 h-6 transition" 
                                                                     :class="(hover >= {{ $i }} || rating >= {{ $i }}) ? 'text-yellow-400' : 'text-gray-300'"
                                                                     fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                                </svg>
                                                            </button>
                                                        @endfor
                                                        <input type="hidden" name="client_rating" x-ref="ratingInput{{ $reservation->id }}" required>
                                                    </div>
                                                </div>
                                                <div class="flex gap-2">
                                                    <input type="text" name="client_rating_comment" 
                                                           class="flex-1 text-sm rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" 
                                                           placeholder="Commentaire (optionnel)" maxlength="500">
                                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium transition">
                                                        Envoyer
                                                    </button>
                                                </div>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex flex-row sm:flex-col gap-2">
                                @if($reservation->status === 'pending')
                                    <form action="{{ route('prestataire.reservations.confirm', $reservation) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                            ✓ Confirmer
                                        </button>
                                    </form>
                                    <form action="{{ route('prestataire.reservations.cancel', $reservation) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-medium" onclick="return confirm('Refuser cette demande ?')">
                                            ✗ Refuser
                                        </button>
                                    </form>
                                @endif
                                
                                @if($reservation->status === 'confirmed')
                                    <form action="{{ route('prestataire.reservations.complete', $reservation) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                                            ✓ Marquer vendu
                                        </button>
                                    </form>
                                    <form action="{{ route('prestataire.reservations.cancel', $reservation) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-sm font-medium" onclick="return confirm('Annuler cette réservation ?')">
                                            Annuler
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="mt-6">
            {{ $reservations->links() }}
        </div>
    @endif
</div>
@endsection
