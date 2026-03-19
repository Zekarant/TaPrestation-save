@extends('layouts.app')

@section('title', 'Historique de mes prestations')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        {{-- En-tête --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">📅 Historique de mes prestations</h1>
                <p class="text-gray-600 mt-1">Suivi complet de vos réservations et revenus</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('prestataire.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                    ← Retour au tableau de bord
                </a>
            </div>
        </div>

        {{-- Filtres --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('prestataire.bookings.history') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <select name="status" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        <option value="">Tous les statuts</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Complétée</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Période</label>
                    <select name="period" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        <option value="">Tous les périodes</option>
                        <option value="today" {{ request('period') === 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                        <option value="week" {{ request('period') === 'week' ? 'selected' : '' }}>Cette semaine</option>
                        <option value="month" {{ request('period') === 'month' ? 'selected' : '' }}>Ce mois</option>
                        <option value="year" {{ request('period') === 'year' ? 'selected' : '' }}>Cette année</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Trier par</label>
                    <select name="sort" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        <option value="recent" {{ request('sort') === 'recent' ? 'selected' : '' }}>Plus récent</option>
                        <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Plus ancien</option>
                        <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Prix décroissant</option>
                        <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Prix croissant</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                        Filtrer
                    </button>
                </div>
            </form>
        </div>

        {{-- Statistiques --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                <p class="text-sm text-gray-500">Total réservations</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_bookings'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                <p class="text-sm text-gray-500">Total gagné</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($stats['total_earned'] ?? 0, 2) }} €</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                <p class="text-sm text-gray-500">Complétées</p>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['completed'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                <p class="text-sm text-gray-500">Moyenne par prestation</p>
                <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['avg_price'] ?? 0, 2) }} €</p>
            </div>
        </div>

        {{-- Liste des prestations --}}
        <div class="space-y-4">
            @forelse($bookings as $booking)
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
                    <div class="md:flex">
                        <div class="md:w-1/4 bg-gray-50 p-6 flex items-center justify-center">
                            @if($booking->service && $booking->service->image)
                                <img src="{{ Storage::url($booking->service->image) }}" alt="{{ $booking->service->name }}" class="w-full h-40 object-cover rounded">
                            @else
                                <div class="w-full h-40 bg-gray-200 rounded flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="p-6 md:w-3/4">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $booking->service->name ?? 'Service' }}</h3>
                                    <p class="text-sm text-gray-500">Réservation #{{ $booking->booking_number }}</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    @if($booking->status === 'completed') bg-green-100 text-green-800
                                    @elseif($booking->status === 'confirmed') bg-blue-100 text-blue-800
                                    @elseif($booking->status === 'cancelled') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4 text-sm">
                                <div>
                                    <p class="text-gray-500">Client</p>
                                    <p class="font-medium text-gray-900">{{ $booking->client->user->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Date de prestation</p>
                                    <p class="font-medium text-gray-900">{{ $booking->start_datetime->format('d/m/Y H:i') }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Statut paiement</p>
                                    <p class="font-medium">
                                        @if($booking->payment_transaction && $booking->payment_transaction->isPaid())
                                            <span class="text-green-600">✓ Payé</span>
                                        @elseif($booking->payment_transaction)
                                            <span class="text-yellow-600">⏳ En attente</span>
                                        @else
                                            <span class="text-gray-600">—</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Montant</p>
                                    <p class="font-bold text-green-600">{{ number_format($booking->total_price, 2) }} €</p>
                                </div>
                            </div>

                            <div class="flex gap-2 justify-start pt-4 border-t border-gray-100">
                                <a href="{{ route('prestataire.bookings.show', $booking) }}" class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-sm font-medium transition">
                                    Voir détails
                                </a>
                                @if($booking->status === 'completed' && (!$booking->review || !$booking->review->prestataire_notes))
                                    <button class="px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-600 rounded-lg text-sm font-medium transition" data-booking-id="{{ $booking->id }}">
                                        Ajouter notes
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-8 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500 mb-4">Aucune prestation trouvée</p>
                    <a href="{{ route('prestataire.services.index') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                        Gérer mes services
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
@endsection
