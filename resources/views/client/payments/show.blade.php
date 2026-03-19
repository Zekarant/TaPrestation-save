@extends('layouts.client')

@section('title', 'Reçu de paiement #' . $transaction->id)

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Bouton retour -->
        <div class="mb-6">
            <a href="{{ route('client.payments.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Retour à mes paiements
            </a>
        </div>

        <!-- Reçu de paiement -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden" id="receipt">
            
            <!-- En-tête du reçu -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                            @if($transaction->status === 'paid' || $transaction->status === 'completed')
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @else
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-white">
                                @if($transaction->status === 'paid' || $transaction->status === 'completed')
                                    Paiement confirmé
                                @elseif($transaction->status === 'pending')
                                    Paiement en attente
                                @elseif($transaction->status === 'refunded')
                                    Paiement remboursé
                                @else
                                    Paiement {{ $transaction->status }}
                                @endif
                            </h1>
                            <p class="text-indigo-100 text-sm">Reçu n° {{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-white">{{ number_format($transaction->amount, 2) }} €</p>
                    </div>
                </div>
            </div>

            <!-- Corps du reçu -->
            <div class="px-8 py-6">
                
                <!-- Informations générales -->
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Date du paiement</span>
                            <p class="font-medium text-gray-900">{{ $transaction->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-gray-500">Méthode de paiement</span>
                            <p class="font-medium text-gray-900">
                                @if($transaction->provider === 'stripe')
                                    💳 Carte bancaire
                                @elseif($transaction->payment_method === 'cash')
                                    💵 Espèces
                                @else
                                    {{ ucfirst($transaction->payment_method ?? $transaction->provider ?? 'Carte') }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Détails des articles -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Détails de l'achat</h2>
                    
                    <div class="space-y-4">
                        @php $hasItems = false; @endphp
                        
                        {{-- Achats Urgent Sale via purchases --}}
                        @if($purchases && $purchases->count() > 0)
                            @php $hasItems = true; @endphp
                            @foreach($purchases as $purchase)
                                <div class="flex items-start bg-gray-50 rounded-xl p-4">
                                    @if($purchase->urgentSale && $purchase->urgentSale->photos && count($purchase->urgentSale->photos) > 0)
                                        <img src="{{ asset('storage/' . $purchase->urgentSale->photos[0]) }}" 
                                             alt="{{ $purchase->urgentSale->title }}" 
                                             class="w-16 h-16 object-cover rounded-lg mr-4 flex-shrink-0">
                                    @else
                                        <div class="w-16 h-16 bg-indigo-100 rounded-lg mr-4 flex-shrink-0 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 mb-1">
                                            🔥 Vente Flash
                                        </span>
                                        <h3 class="font-medium text-gray-900 truncate">
                                            {{ $purchase->urgentSale->title ?? 'Article' }}
                                        </h3>
                                        @if($purchase->urgentSale && $purchase->urgentSale->prestataire)
                                            <a href="{{ route('prestataires.show', $purchase->urgentSale->prestataire->slug ?? $purchase->urgentSale->prestataire->id) }}" 
                                               class="text-sm text-indigo-600 hover:text-indigo-800 hover:underline inline-flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                {{ $purchase->urgentSale->prestataire->user->name ?? 'Vendeur' }}
                                            </a>
                                        @endif
                                        <p class="text-sm text-gray-500 mt-1">
                                            Quantité: {{ $purchase->quantity }} × {{ number_format($purchase->unit_price, 2) }} €
                                        </p>
                                    </div>
                                    <div class="text-right ml-4">
                                        <p class="font-semibold text-gray-900">{{ number_format($purchase->total_amount, 2) }} €</p>
                                        @if($purchase->urgentSale)
                                            <a href="{{ route('urgent-sales.show', $purchase->urgentSale->slug ?? $purchase->urgentSale->id) }}" 
                                               class="text-xs text-indigo-600 hover:underline">
                                                Voir l'article →
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        {{-- Articles via cartItems (allocations) --}}
                        @if(isset($cartItems) && $cartItems->count() > 0)
                            @php $hasItems = true; @endphp
                            @foreach($cartItems as $cartItem)
                                <div class="flex items-start bg-gray-50 rounded-xl p-4">
                                    @if($cartItem['type'] === 'urgent_sale')
                                        @php $item = $cartItem['item']; @endphp
                                        @if($item->photos && count($item->photos) > 0)
                                            <img src="{{ asset('storage/' . $item->photos[0]) }}" 
                                                 alt="{{ $item->title }}" 
                                                 class="w-16 h-16 object-cover rounded-lg mr-4 flex-shrink-0">
                                        @else
                                            <div class="w-16 h-16 bg-orange-100 rounded-lg mr-4 flex-shrink-0 flex items-center justify-center">
                                                <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 mb-1">
                                                🔥 Vente Flash
                                            </span>
                                            <h3 class="font-medium text-gray-900 truncate">{{ $item->title }}</h3>
                                            @if($item->prestataire)
                                                <a href="{{ route('prestataires.show', $item->prestataire->slug ?? $item->prestataire->id) }}" 
                                                   class="text-sm text-indigo-600 hover:text-indigo-800 hover:underline inline-flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                    {{ $item->prestataire->user->name ?? 'Vendeur' }}
                                                </a>
                                            @endif
                                        </div>
                                        <div class="text-right ml-4">
                                            <p class="font-semibold text-gray-900">{{ number_format($cartItem['amount'], 2) }} €</p>
                                            <a href="{{ route('urgent-sales.show', $item->slug ?? $item->id) }}" 
                                               class="text-xs text-indigo-600 hover:underline">
                                                Voir l'article →
                                            </a>
                                        </div>
                                    @elseif($cartItem['type'] === 'booking')
                                        @php $item = $cartItem['item']; @endphp
                                        <div class="w-16 h-16 bg-blue-100 rounded-lg mr-4 flex-shrink-0 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mb-1">
                                                📅 Réservation
                                            </span>
                                            <h3 class="font-medium text-gray-900 truncate">{{ $item->service->name ?? 'Service' }}</h3>
                                            @if($item->prestataire)
                                                <a href="{{ route('prestataires.show', $item->prestataire->slug ?? $item->prestataire->id) }}" 
                                                   class="text-sm text-indigo-600 hover:text-indigo-800 hover:underline inline-flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                    {{ $item->prestataire->user->name ?? 'Prestataire' }}
                                                </a>
                                            @endif
                                            @if($item->event_date)
                                                <p class="text-sm text-gray-500">📆 {{ \Carbon\Carbon::parse($item->event_date)->format('d/m/Y') }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right ml-4">
                                            <p class="font-semibold text-gray-900">{{ number_format($cartItem['amount'], 2) }} €</p>
                                            <a href="{{ route('client.bookings.show', $item) }}" 
                                               class="text-xs text-indigo-600 hover:underline">
                                                Voir la réservation →
                                            </a>
                                        </div>
                                    @elseif($cartItem['type'] === 'equipment_rental')
                                        @php $item = $cartItem['item']; @endphp
                                        <div class="w-16 h-16 bg-purple-100 rounded-lg mr-4 flex-shrink-0 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 mb-1">
                                                🔧 Location
                                            </span>
                                            <h3 class="font-medium text-gray-900 truncate">{{ $item->equipment->name ?? 'Équipement' }}</h3>
                                            @if($item->equipment && $item->equipment->prestataire)
                                                <a href="{{ route('prestataires.show', $item->equipment->prestataire->slug ?? $item->equipment->prestataire->id) }}" 
                                                   class="text-sm text-indigo-600 hover:text-indigo-800 hover:underline inline-flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                    {{ $item->equipment->prestataire->user->name ?? 'Loueur' }}
                                                </a>
                                            @endif
                                        </div>
                                        <div class="text-right ml-4">
                                            <p class="font-semibold text-gray-900">{{ number_format($cartItem['amount'], 2) }} €</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif

                        {{-- Booking direct --}}
                        @if($booking)
                            @php $hasItems = true; @endphp
                            <div class="flex items-start bg-gray-50 rounded-xl p-4">
                                <div class="w-16 h-16 bg-blue-100 rounded-lg mr-4 flex-shrink-0 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mb-1">
                                        📅 Réservation
                                    </span>
                                    <h3 class="font-medium text-gray-900 truncate">{{ $booking->service->name ?? 'Service' }}</h3>
                                    @if($booking->prestataire)
                                        <a href="{{ route('prestataires.show', $booking->prestataire->slug ?? $booking->prestataire->id) }}" 
                                           class="text-sm text-indigo-600 hover:text-indigo-800 hover:underline inline-flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            {{ $booking->prestataire->user->name ?? 'Prestataire' }}
                                        </a>
                                    @endif
                                    @if($booking->event_date)
                                        <p class="text-sm text-gray-500">📆 {{ \Carbon\Carbon::parse($booking->event_date)->format('d/m/Y') }}</p>
                                    @endif
                                </div>
                                <div class="text-right ml-4">
                                    <p class="font-semibold text-gray-900">{{ number_format($booking->total_price ?? $transaction->amount, 2) }} €</p>
                                    <a href="{{ route('client.bookings.show', $booking) }}" 
                                       class="text-xs text-indigo-600 hover:underline">
                                        Voir la réservation →
                                    </a>
                                </div>
                            </div>
                        @endif

                        {{-- Location d'équipement directe --}}
                        @if($rentalRequest)
                            @php $hasItems = true; @endphp
                            <div class="flex items-start bg-gray-50 rounded-xl p-4">
                                <div class="w-16 h-16 bg-purple-100 rounded-lg mr-4 flex-shrink-0 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 mb-1">
                                        🔧 Location d'équipement
                                    </span>
                                    <h3 class="font-medium text-gray-900 truncate">{{ $rentalRequest->equipment->name ?? 'Équipement' }}</h3>
                                    @if($rentalRequest->equipment && $rentalRequest->equipment->prestataire)
                                        <a href="{{ route('prestataires.show', $rentalRequest->equipment->prestataire->slug ?? $rentalRequest->equipment->prestataire->id) }}" 
                                           class="text-sm text-indigo-600 hover:text-indigo-800 hover:underline inline-flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            {{ $rentalRequest->equipment->prestataire->user->name ?? 'Loueur' }}
                                        </a>
                                    @endif
                                    @php
                                        $depositAmount = (float) ($rentalRequest->equipment->security_deposit ?? $rentalRequest->security_deposit ?? 0);
                                        $escrowMeta = [];
                                        try {
                                            $escrowMeta = !empty($rentalEscrow->metadata ?? null)
                                                ? (json_decode((string) $rentalEscrow->metadata, true) ?: [])
                                                : [];
                                        } catch (\Throwable $e) {
                                            $escrowMeta = [];
                                        }
                                        $depositStatus = strtolower((string) (($rentalRequest->deposit_status ?? null) ?: ($escrowMeta['deposit_status'] ?? 'pending')));
                                        $depositRetained = (float) (($rentalRequest->deposit_retained ?? null) ?? ($escrowMeta['deposit_retained'] ?? 0));
                                        $depositReturned = max(0, $depositAmount - $depositRetained);
                                        if (isset($escrowMeta['deposit_returned'])) {
                                            $depositReturned = max(0, (float) $escrowMeta['deposit_returned']);
                                        }
                                    @endphp
                                    @if($depositAmount > 0)
                                        <div class="mt-2 rounded-lg p-2 border {{ $depositStatus === 'returned' ? 'bg-emerald-50 border-emerald-200' : ($depositStatus === 'partial' ? 'bg-amber-50 border-amber-200' : ($depositStatus === 'retained' ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200')) }}">
                                            <p class="text-xs font-medium {{ $depositStatus === 'returned' ? 'text-emerald-700' : ($depositStatus === 'partial' ? 'text-amber-700' : ($depositStatus === 'retained' ? 'text-red-700' : 'text-gray-700')) }}">Caution</p>
                                            @if($depositStatus === 'returned')
                                                <p class="text-xs text-emerald-800 font-semibold">Remboursée: {{ number_format($depositReturned, 2) }} €</p>
                                            @elseif($depositStatus === 'partial')
                                                <p class="text-xs text-amber-800 font-semibold">Partielle: {{ number_format($depositReturned, 2) }} € remboursés, {{ number_format($depositRetained, 2) }} € retenus</p>
                                            @elseif($depositStatus === 'retained')
                                                <p class="text-xs text-red-800 font-semibold">Retenue: {{ number_format($depositRetained, 2) }} €</p>
                                            @else
                                                <p class="text-xs text-gray-700 font-semibold">En attente de restitution</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right ml-4">
                                    <p class="font-semibold text-gray-900">{{ number_format($rentalRequest->total_price ?? $transaction->amount, 2) }} €</p>
                                </div>
                            </div>
                        @endif

                        {{-- Aucun détail disponible --}}
                        @if(!$hasItems)
                            <div class="flex items-start bg-gray-50 rounded-xl p-4">
                                <div class="w-16 h-16 bg-gray-100 rounded-lg mr-4 flex-shrink-0 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-medium text-gray-900">{{ $transaction->description ?? 'Paiement' }}</h3>
                                    <p class="text-sm text-gray-500">
                                        @switch($transaction->type)
                                            @case('payment') Paiement complet @break
                                            @case('deposit') Acompte @break
                                            @case('balance') Solde @break
                                            @case('cart_payment') Achat panier @break
                                            @default {{ ucfirst($transaction->type ?? 'Paiement') }}
                                        @endswitch
                                    </p>
                                </div>
                                <div class="text-right ml-4">
                                    <p class="font-semibold text-gray-900">{{ number_format($transaction->amount, 2) }} €</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Total -->
                <div class="border-t border-gray-200 pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-900">Total payé</span>
                        <span class="text-2xl font-bold text-indigo-600">{{ number_format($transaction->amount, 2) }} €</span>
                    </div>
                </div>

                <!-- Informations de référence -->
                <div class="mt-6 pt-6 border-t border-dashed border-gray-300">
                    <div class="grid grid-cols-2 gap-4 text-xs text-gray-500">
                        <div>
                            <p>Référence transaction</p>
                            <p class="font-mono text-gray-700">{{ $transaction->transaction_id ?? $transaction->stripe_payment_intent_id ?? 'TX-' . str_pad($transaction->id, 8, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div class="text-right">
                            <p>Statut</p>
                            <p class="font-medium 
                                @if($transaction->status === 'paid' || $transaction->status === 'completed') text-green-600
                                @elseif($transaction->status === 'pending') text-yellow-600
                                @elseif($transaction->status === 'refunded') text-red-600
                                @else text-gray-600 @endif">
                                @if($transaction->status === 'paid' || $transaction->status === 'completed') ✓ Payé
                                @elseif($transaction->status === 'pending') ⏳ En attente
                                @elseif($transaction->status === 'refunded') ↩ Remboursé
                                @else {{ ucfirst($transaction->status) }} @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pied du reçu -->
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-200">
                <p class="text-center text-xs text-gray-500">
                    Merci pour votre achat ! • TaPrestation.com
                </p>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
            <button onclick="window.print()" 
                    class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition shadow-lg shadow-indigo-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Imprimer le reçu
            </button>
            
            <a href="{{ route('client.payments.index') }}" 
               class="inline-flex items-center justify-center px-6 py-3 bg-white hover:bg-gray-50 text-gray-700 font-medium rounded-xl border border-gray-300 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Retour aux paiements
            </a>
        </div>

        <!-- Besoin d'aide -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500">
                Un problème avec cette commande ? 
                <a href="#" class="text-indigo-600 hover:text-indigo-800 font-medium">Contactez-nous</a>
            </p>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #receipt, #receipt * {
        visibility: visible;
    }
    #receipt {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none !important;
    }
    .bg-gradient-to-r {
        background: #4f46e5 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    button, a {
        display: none !important;
    }
}
</style>
@endsection
