@extends('layouts.prestataire')

@section('title', 'Détails de la vente #' . $transaction->id)

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- En-tête avec retour -->
    <div class="mb-6">
        <a href="{{ route('prestataire.payments.dashboard') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour au tableau de bord paiements
        </a>
    </div>

    <!-- Carte principale de la vente -->
    <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-green-500 to-emerald-600">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Vente #{{ $transaction->id }}</h1>
                    <p class="text-green-100 mt-1">{{ $transaction->created_at->format('d/m/Y à H:i') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-white">{{ number_format($netAmount, 2) }} €</p>
                    <p class="text-green-100 text-sm">Net après commission</p>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium mt-2
                        @if($transaction->status === 'paid') bg-green-100 text-green-800
                        @elseif($transaction->status === 'pending') bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-800 @endif">
                        @if($transaction->status === 'paid')
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Payé
                        @elseif($transaction->status === 'pending')
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                            En attente
                        @else
                            {{ ucfirst($transaction->status) }}
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Récapitulatif financier -->
        <div class="px-6 py-5 bg-gray-50 border-b">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">💰 Récapitulatif financier</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-lg border">
                    <p class="text-sm text-gray-500">Montant brut</p>
                    <p class="text-xl font-bold text-gray-900">{{ number_format($grossAmount, 2) }} €</p>
                </div>
                <div class="bg-white p-4 rounded-lg border">
                    <p class="text-sm text-gray-500">Commission plateforme ({{ $commissionRate * 100 }}%)</p>
                    <p class="text-xl font-bold text-red-600">-{{ number_format($commission, 2) }} €</p>
                </div>
                <div class="p-4 rounded-lg border border-green-200 bg-green-50">
                    <p class="text-sm text-gray-500">Vous recevez</p>
                    <p class="text-xl font-bold text-green-600">{{ number_format($netAmount, 2) }} €</p>
                </div>
            </div>
        </div>

        <!-- Informations client -->
        @if($buyer)
        <div class="px-6 py-5 border-b">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Client
            </h2>
            <div class="flex items-center">
                @if($buyer->avatar)
                <img src="{{ asset('storage/' . $buyer->avatar) }}" alt="{{ $buyer->name }}" class="w-12 h-12 rounded-full mr-4">
                @else
                <div class="w-12 h-12 bg-indigo-100 rounded-full mr-4 flex items-center justify-center">
                    <span class="text-indigo-600 font-bold text-lg">{{ strtoupper(substr($buyer->name, 0, 1)) }}</span>
                </div>
                @endif
                <div>
                    <p class="font-semibold text-gray-900">{{ $buyer->name }}</p>
                    <p class="text-sm text-gray-500">{{ $buyer->email }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Détails des articles vendus -->
    @if($purchases->count() > 0)
    <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-6 h-6 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                Produits vendus
            </h2>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($purchases as $purchase)
            <div class="px-6 py-4 flex items-center">
                @if($purchase->urgentSale && $purchase->urgentSale->photos && count($purchase->urgentSale->photos) > 0)
                <img src="{{ asset('storage/' . $purchase->urgentSale->photos[0]) }}" 
                     alt="{{ $purchase->urgentSale->title ?? 'Produit' }}" 
                     class="w-20 h-20 object-cover rounded-lg mr-4">
                @else
                <div class="w-20 h-20 bg-gray-200 rounded-lg mr-4 flex items-center justify-center">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                @endif
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-gray-900">
                        {{ $purchase->urgentSale->title ?? 'Produit #' . $purchase->urgent_sale_id }}
                    </h3>
                    <div class="mt-1 flex items-center text-sm">
                        <span class="text-gray-500">Quantité vendue: {{ $purchase->quantity }}</span>
                        <span class="mx-2 text-gray-300">|</span>
                        <span class="text-gray-500">Prix unitaire: {{ number_format($purchase->unit_price, 2) }} €</span>
                    </div>
                    @if($purchase->urgentSale)
                    <div class="mt-1 text-sm">
                        <span class="text-gray-500">Stock restant: 
                            <span class="font-medium {{ $purchase->urgentSale->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $purchase->urgentSale->quantity }}
                            </span>
                        </span>
                    </div>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-gray-900">{{ number_format($purchase->total_amount, 2) }} €</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($purchase->status === 'paid') bg-green-100 text-green-800
                        @elseif($purchase->status === 'delivered') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800 @endif">
                        @switch($purchase->status)
                            @case('paid') Payé @break
                            @case('delivered') Livré @break
                            @default {{ ucfirst($purchase->status) }}
                        @endswitch
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Réservation associée -->
    @if($booking)
    <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Réservation associée
            </h2>
        </div>
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">
                        {{ $booking->service->name ?? 'Service #' . $booking->id }}
                    </h3>
                    @if($booking->client)
                    <p class="text-sm text-gray-500">
                        Client: {{ $booking->client->user->name ?? 'Client' }}
                    </p>
                    @endif
                    <p class="text-sm text-gray-500 mt-1">
                        Date: {{ $booking->event_date ? \Carbon\Carbon::parse($booking->event_date)->format('d/m/Y') : 'N/A' }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-gray-900">{{ number_format($booking->total_price ?? 0, 2) }} €</p>
                    <a href="{{ route('prestataire.bookings.show', $booking) }}" 
                       class="mt-2 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                        Voir la réservation
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Pas de détails disponibles -->
    @if($purchases->count() === 0 && !$booking)
    <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun détail supplémentaire</h3>
            <p class="mt-1 text-sm text-gray-500">Les détails de cette vente ne sont pas disponibles.</p>
        </div>
    </div>
    @endif

    <!-- Actions -->
    <div class="flex justify-between items-center">
        <a href="{{ route('prestataire.payments.dashboard') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour au tableau de bord
        </a>
        
        @if($transaction->status === 'paid')
        <button onclick="window.print()" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Imprimer le récapitulatif
        </button>
        @endif
    </div>
</div>

<style>
@media print {
    nav, header, footer, button, a[href]:not(.no-print) {
        display: none !important;
    }
    .shadow-lg {
        box-shadow: none !important;
    }
}
</style>
@endsection
