@extends('layouts.app')

@section('title', 'Commande #' . $foodOrder->order_number)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .glass-card { background: rgba(255,255,255,0.97); backdrop-filter: blur(12px); }
    .pulse-ring { animation: pulse-ring 2s ease-in-out infinite; }
    @keyframes pulse-ring { 0%,100%{transform:scale(1);opacity:1} 50%{transform:scale(1.05);opacity:0.8} }
    .bounce-soft { animation: bounce-soft 2s ease-in-out infinite; }
    @keyframes bounce-soft { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-5px)} }
    .shimmer { background:linear-gradient(90deg,transparent,rgba(255,255,255,0.3),transparent); background-size:200% 100%; animation:shimmer 2s infinite; }
    @keyframes shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
    .live-dot { animation: blink 1.5s ease-in-out infinite; }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }
    #orderMap { height: 180px; border-radius: 12px; }
    .step-line { height: 2px; }
    .accordion-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
    .accordion-content.open { max-height: 500px; }
</style>
@endpush

@section('content')
@php
    $statusConfig = [
        'pending' => ['gradient' => 'from-amber-400 to-orange-500', 'bg' => 'bg-amber-500', 'icon' => '⏳', 'label' => 'En attente', 'desc' => 'Le prestataire va confirmer votre commande', 'step' => 1],
        'accepted' => ['gradient' => 'from-blue-400 to-indigo-500', 'bg' => 'bg-blue-500', 'icon' => '👍', 'label' => 'Acceptée', 'desc' => 'Votre commande va être préparée', 'step' => 2],
        'preparing' => ['gradient' => 'from-purple-400 to-pink-500', 'bg' => 'bg-purple-500', 'icon' => '👨‍🍳', 'label' => 'En préparation', 'desc' => 'Le chef prépare votre commande', 'step' => 3],
        'ready' => ['gradient' => 'from-green-400 to-emerald-500', 'bg' => 'bg-green-500', 'icon' => $foodOrder->delivery_type === 'pickup' ? '🎉' : '🚚', 'label' => $foodOrder->delivery_type === 'pickup' ? 'Prête !' : 'En route', 'desc' => $foodOrder->delivery_type === 'pickup' ? 'Venez récupérer votre commande' : 'Votre commande arrive bientôt', 'step' => 4],
        'delivered' => ['gradient' => 'from-emerald-400 to-teal-500', 'bg' => 'bg-teal-500', 'icon' => '📦', 'label' => 'Livrée', 'desc' => 'Bon appétit !', 'step' => 5],
        'completed' => ['gradient' => 'from-gray-400 to-gray-500', 'bg' => 'bg-gray-500', 'icon' => '✅', 'label' => 'Terminée', 'desc' => 'Merci pour votre commande', 'step' => 5],
        'cancelled' => ['gradient' => 'from-red-400 to-rose-500', 'bg' => 'bg-red-500', 'icon' => '❌', 'label' => 'Annulée', 'desc' => 'Commande annulée', 'step' => 0],
        'rejected' => ['gradient' => 'from-red-400 to-rose-500', 'bg' => 'bg-red-500', 'icon' => '🚫', 'label' => 'Refusée', 'desc' => 'Le prestataire a refusé', 'step' => 0],
    ];
    $config = $statusConfig[$foodOrder->status] ?? $statusConfig['pending'];
    $isActive = !in_array($foodOrder->status, ['cancelled', 'rejected', 'completed']);
    $effectivePaymentPolicy = $foodOrder->getPaymentPolicy();
    $effectivePaymentType = (string) ($effectivePaymentPolicy['type'] ?? (($foodOrder->payment_method ?? 'cash') === 'cash' ? 'cash' : 'full_prepay'));
    $isCashOnlyPayment = $effectivePaymentType === 'cash';
    $canOfferOnlinePayment = function_exists('food_online_payment_enabled')
        && food_online_payment_enabled()
        && ($foodOrder->prestataire->stripe_onboarding_completed ?? false)
        && !$isCashOnlyPayment
        && !in_array($foodOrder->payment_status, ['paid', 'partial', 'pending_capture'], true)
        && !in_array($foodOrder->status, ['cancelled', 'rejected'], true);
    $clientCancelBreakdown = $foodOrder->getCancellationBreakdown('client');
@endphp

<div class="min-h-screen bg-gray-50">
    
    {{-- Header Sticky avec statut --}}
    <div class="sticky top-0 z-40 bg-gradient-to-r {{ $config['gradient'] }} text-white shadow-lg">
        <div class="safe-area-top"></div>
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <a href="{{ route('food.orders') }}" class="w-10 h-10 flex items-center justify-center rounded-full bg-white/20 hover:bg-white/30 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="text-center">
                    <p class="text-xs opacity-80">Commande</p>
                    <p class="font-bold">#{{ $foodOrder->order_number }}</p>
                </div>
                @if($isActive)
                    <div class="flex items-center gap-1.5 text-xs bg-white/20 px-2 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 bg-white rounded-full live-dot"></span>
                        <span>Live</span>
                    </div>
                @else
                    <div class="w-10"></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Statut principal --}}
    <div class="bg-gradient-to-r {{ $config['gradient'] }} text-white px-4 pb-8 pt-2 relative overflow-hidden">
        <div class="absolute inset-0 shimmer opacity-20"></div>
        <div class="relative text-center">
            <div class="w-20 h-20 mx-auto bg-white/20 rounded-2xl flex items-center justify-center text-4xl mb-3 {{ $isActive ? 'bounce-soft' : '' }}">
                {{ $config['icon'] }}
            </div>
            <h2 class="text-2xl font-bold mb-1">{{ $config['label'] }}</h2>
            <p class="text-white/80 text-sm">{{ $config['desc'] }}</p>
            
            @if($isActive && in_array($foodOrder->status, ['accepted', 'preparing']))
                <div class="mt-3 inline-flex items-center gap-2 bg-white/20 px-4 py-2 rounded-full text-sm">
                    <span>⏱️</span>
                    <span>≈ {{ $foodOrder->prestataire->food_estimated_prep_time ?? 30 }} min</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Progress bar horizontale minimaliste --}}
    @if(!in_array($foodOrder->status, ['cancelled', 'rejected']))
        <div class="bg-white border-b px-4 py-4">
            <div class="flex items-center justify-between max-w-md mx-auto">
                @php
                    $steps = [
                        ['step' => 1, 'icon' => '📝', 'label' => 'Reçue'],
                        ['step' => 2, 'icon' => '✓', 'label' => 'Acceptée'],
                        ['step' => 3, 'icon' => '🔥', 'label' => 'Préparation'],
                        ['step' => 4, 'icon' => $foodOrder->delivery_type === 'pickup' ? '📍' : '🚚', 'label' => $foodOrder->delivery_type === 'pickup' ? 'Prête' : 'Livrée'],
                    ];
                @endphp
                @foreach($steps as $i => $step)
                    <div class="flex flex-col items-center {{ $i > 0 ? 'flex-1' : '' }}">
                        @if($i > 0)
                            <div class="step-line w-full {{ $config['step'] >= $step['step'] ? $config['bg'] : 'bg-gray-200' }} mb-2"></div>
                        @endif
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm {{ $config['step'] >= $step['step'] ? $config['bg'] . ' text-white' : 'bg-gray-200 text-gray-400' }} {{ $config['step'] == $step['step'] ? 'ring-4 ring-opacity-30 ring-current pulse-ring' : '' }}">
                            @if($config['step'] > $step['step'])
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            @else
                                {{ $step['icon'] }}
                            @endif
                        </div>
                        <span class="text-xs mt-1 {{ $config['step'] >= $step['step'] ? 'text-gray-900 font-medium' : 'text-gray-400' }}">{{ $step['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="px-4 py-4 space-y-4 pb-32">
        
        {{-- Code (visible côté client) --}}
        @if($foodOrder->delivery_code && in_array($foodOrder->status, ['ready', 'delivered']) && in_array($foodOrder->delivery_type, ['delivery', 'pickup']))
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl p-4 text-white">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-2xl">🔐</span>
                    <div>
                        <p class="font-bold">{{ $foodOrder->delivery_type === 'pickup' ? 'Code de retrait' : 'Code de confirmation' }}</p>
                        <p class="text-sm opacity-80">{{ $foodOrder->delivery_type === 'pickup' ? 'Donnez ce code au prestataire lors du retrait' : 'Donnez ce code au livreur à la réception' }}</p>
                    </div>
                </div>
                <div class="bg-white/20 rounded-xl p-4 text-center">
                    <p class="text-4xl font-mono font-bold tracking-[0.5em]">{{ $foodOrder->delivery_code }}</p>
                </div>
            </div>
        @endif

        {{-- Alerte action requise --}}
        @if($foodOrder->isDelivered() && !$foodOrder->client_confirmed)
            <div class="bg-green-50 border-2 border-green-200 rounded-2xl p-4">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-2xl">✅</span>
                    <div>
                        <p class="font-bold text-green-800">Commande reçue ?</p>
                        <p class="text-sm text-green-600">Confirmez la réception pour terminer</p>
                    </div>
                </div>
                <form action="{{ route('food.orders.confirm', $foodOrder) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl transition">
                        Confirmer la réception
                    </button>
                </form>
            </div>
        @endif

        {{-- Carte - Adresse livraison/retrait --}}
        @if($foodOrder->delivery_type === 'pickup' || ($foodOrder->delivery_lat && $foodOrder->delivery_lng))
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div id="orderMap"></div>
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">
                                {{ $foodOrder->delivery_type === 'pickup' ? '📍 Adresse de retrait' : '🏠 Livraison' }}
                            </p>
                            <p class="font-medium text-gray-900 truncate">
                                @if($foodOrder->delivery_type === 'pickup')
                                    {{ $foodOrder->prestataire->address }}, {{ $foodOrder->prestataire->city }}
                                @else
                                    {{ $foodOrder->delivery_address }}
                                @endif
                            </p>
                            @if($foodOrder->delivery_floor)
                                <p class="text-sm text-gray-500">Étage {{ $foodOrder->delivery_floor }}@if($foodOrder->delivery_door_code) • Code: {{ $foodOrder->delivery_door_code }}@endif</p>
                            @endif
                        </div>
                        @php
                            $lat = $foodOrder->delivery_type === 'pickup' ? $foodOrder->prestataire->latitude : $foodOrder->delivery_lat;
                            $lng = $foodOrder->delivery_type === 'pickup' ? $foodOrder->prestataire->longitude : $foodOrder->delivery_lng;
                        @endphp
                        @if($lat && $lng)
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $lat }},{{ $lng }}" 
                               target="_blank"
                               class="flex-shrink-0 w-12 h-12 bg-blue-500 hover:bg-blue-600 text-white rounded-xl flex items-center justify-center transition">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Bouton Suivi GPS en direct (livraison active avec livreur) --}}
        @if($isActive && $foodOrder->delivery_type === 'delivery' && $foodOrder->driver_id)
            <a href="{{ route('food.orders.track', $foodOrder) }}" 
               class="block bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-2xl shadow-lg p-4 hover:shadow-xl transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🛵</span>
                        <div>
                            <p class="font-bold">Suivre le livreur en direct</p>
                            <p class="text-white/80 text-xs">GPS temps réel sur la carte</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        @elseif($isActive)
            <a href="{{ route('food.orders.track', $foodOrder) }}" 
               class="block bg-white border border-gray-200 rounded-2xl shadow-sm p-4 hover:bg-gray-50 transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">📍</span>
                        <div>
                            <p class="font-bold text-gray-900">Suivi de commande</p>
                            <p class="text-gray-500 text-xs">Voir la progression en temps réel</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        @endif

        {{-- Informations de la commande --}}
        <div class="bg-white rounded-2xl shadow-sm p-4">
            <div class="flex items-center gap-3 mb-3">
                <span class="text-xl">📋</span>
                <h3 class="font-bold text-gray-900">Détails de la commande</h3>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-50 rounded-xl p-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Date de commande</p>
                    <p class="font-semibold text-gray-900">{{ $foodOrder->created_at->format('d/m/Y') }}</p>
                    <p class="text-sm text-gray-500">{{ $foodOrder->created_at->format('H:i') }}</p>
                </div>
                @if($foodOrder->requested_at)
                    <div class="bg-blue-50 rounded-xl p-3">
                        <p class="text-xs text-blue-600 uppercase tracking-wide">📅 Livraison souhaitée</p>
                        <p class="font-semibold text-blue-900">{{ \Carbon\Carbon::parse($foodOrder->requested_at)->format('d/m/Y') }}</p>
                        <p class="text-sm text-blue-600">{{ \Carbon\Carbon::parse($foodOrder->requested_at)->format('H:i') }}</p>
                    </div>
                @else
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Mode de livraison</p>
                        <p class="font-semibold text-gray-900">
                            @if($foodOrder->delivery_type === 'pickup')
                                🏪 À emporter
                            @elseif($foodOrder->delivery_type === 'delivery')
                                🚚 Livraison
                            @else
                                📍 Sur place
                            @endif
                        </p>
                    </div>
                @endif
                @if($foodOrder->requested_at)
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Mode de livraison</p>
                        <p class="font-semibold text-gray-900">
                            @if($foodOrder->delivery_type === 'pickup')
                                🏪 À emporter
                            @elseif($foodOrder->delivery_type === 'delivery')
                                🚚 Livraison
                            @else
                                📍 Sur place
                            @endif
                        </p>
                    </div>
                @endif
                @if($foodOrder->notes)
                    <div class="col-span-2 bg-amber-50 rounded-xl p-3">
                        <p class="text-xs text-amber-600 uppercase tracking-wide mb-1">📝 Notes</p>
                        <p class="text-sm text-gray-700">{{ $foodOrder->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Prestataire - Compact --}}
        <div class="bg-white rounded-2xl shadow-sm p-4">
            <a href="{{ route('food.menu', $foodOrder->prestataire) }}" class="flex items-center gap-3 group">
                @if($foodOrder->prestataire->profile_image)
                    <img src="{{ asset('storage/' . $foodOrder->prestataire->profile_image) }}" alt="{{ $foodOrder->prestataire->business_name ?? 'Restaurant' }}" class="w-12 h-12 rounded-xl object-cover group-hover:ring-2 ring-orange-400 transition">
                @else
                    <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center text-xl group-hover:ring-2 ring-orange-400 transition">👨‍🍳</div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-gray-900 truncate group-hover:text-orange-600 transition">{{ $foodOrder->prestataire->company_name ?? $foodOrder->prestataire->business_name }}</p>
                    <p class="text-sm text-gray-500">{{ $foodOrder->prestataire->city }}</p>
                    <p class="text-xs text-orange-500 flex items-center gap-1 mt-0.5">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        Voir le menu
                    </p>
                </div>
            </a>
            @if($foodOrder->prestataire->phone)
                <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100">
                    <a href="tel:{{ $foodOrder->prestataire->phone }}" 
                       class="flex-1 h-10 bg-green-500 hover:bg-green-600 text-white rounded-xl flex items-center justify-center gap-2 transition text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Appeler
                    </a>
                    <a href="{{ route('food.menu', $foodOrder->prestataire) }}" 
                       class="h-10 px-4 bg-orange-100 hover:bg-orange-200 text-orange-600 rounded-xl flex items-center justify-center gap-2 transition text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profil
                    </a>
                </div>
            @endif
        </div>

        {{-- Articles - Accordion --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <button onclick="toggleAccordion('itemsAccordion')" class="w-full p-4 flex items-center justify-between text-left">
                <div class="flex items-center gap-3">
                    <span class="text-xl">🛒</span>
                    <div>
                        <p class="font-bold text-gray-900">{{ $foodOrder->items->sum('quantity') }} article{{ $foodOrder->items->sum('quantity') > 1 ? 's' : '' }}</p>
                        <p class="text-sm text-gray-500">{{ number_format($foodOrder->total, 2) }} €</p>
                    </div>
                </div>
                <svg id="itemsAccordionIcon" class="w-5 h-5 text-gray-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div id="itemsAccordion" class="accordion-content">
                <div class="px-4 pb-4 border-t border-gray-100 pt-3">
                    <div class="space-y-3">
                        @foreach($foodOrder->items as $item)
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center text-xs font-bold">{{ $item->quantity }}x</span>
                                <span class="flex-1 text-gray-900 text-sm">{{ $item->product_name }}</span>
                                <span class="font-medium text-gray-900 text-sm">{{ number_format($item->total_price, 2) }} €</span>
                            </div>
                            @if($item->special_instructions)
                                <p class="text-xs text-orange-500 ml-10">💬 {{ $item->special_instructions }}</p>
                            @endif
                        @endforeach
                    </div>
                    <div class="border-t border-gray-100 mt-4 pt-3 space-y-1">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Sous-total</span>
                            <span>{{ number_format($foodOrder->subtotal, 2) }} €</span>
                        </div>
                        @if($foodOrder->service_fee > 0)
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Frais de service</span>
                                <span>{{ number_format($foodOrder->service_fee, 2) }} €</span>
                            </div>
                        @endif
                        @if($foodOrder->delivery_fee > 0)
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Livraison</span>
                                <span>{{ number_format($foodOrder->delivery_fee, 2) }} €</span>
                            </div>
                        @endif
                        <div class="flex justify-between font-bold text-gray-900 pt-2">
                            <span>Total</span>
                            <span class="text-orange-600">{{ number_format($foodOrder->total, 2) }} €</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Paiement --}}
        <div class="bg-white rounded-2xl shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-xl">💳</span>
                    <div>
                        <p class="font-medium text-gray-900">
                            @if($isCashOnlyPayment)
                                Espèces
                            @elseif($foodOrder->payment_method === 'card')
                                Carte bancaire
                            @elseif(($foodOrder->payment_method ?? null) === 'online')
                                Paiement en ligne
                            @else
                                Paiement en ligne
                            @endif
                        </p>
                        <p class="text-sm {{ $foodOrder->payment_status === 'paid' ? 'text-green-600' : 'text-amber-600' }}">
                            {{ $foodOrder->payment_status === 'paid' ? '✓ Payé' : 'En attente' }}
                        </p>
                    </div>
                </div>
                <span class="text-lg font-bold text-gray-900">{{ number_format($foodOrder->total, 2) }} €</span>
            </div>
            
            {{-- Bouton payer uniquement si un paiement en ligne est réellement requis --}}
            @if($canOfferOnlinePayment)
                <a href="{{ route('food.orders.payment', $foodOrder) }}" 
                   class="mt-3 w-full flex items-center justify-center gap-2 py-3 bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold rounded-xl">
                    💳 Payer maintenant
                </a>
            @endif
        </div>

        {{-- Infos supplémentaires - Accordion --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <button onclick="toggleAccordion('infoAccordion')" class="w-full p-4 flex items-center justify-between text-left">
                <div class="flex items-center gap-3">
                    <span class="text-xl">ℹ️</span>
                    <span class="font-medium text-gray-900">Détails & historique</span>
                </div>
                <svg id="infoAccordionIcon" class="w-5 h-5 text-gray-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div id="infoAccordion" class="accordion-content">
                <div class="px-4 pb-4 border-t border-gray-100 pt-3 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Commandé le</span>
                        <span class="text-gray-900">{{ $foodOrder->created_at->format('d/m/Y à H:i') }}</span>
                    </div>
                    @if($foodOrder->accepted_at)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Acceptée</span>
                            <span class="text-gray-900">{{ $foodOrder->accepted_at->format('H:i') }}</span>
                        </div>
                    @endif
                    @if($foodOrder->preparing_at)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">En préparation</span>
                            <span class="text-gray-900">{{ $foodOrder->preparing_at->format('H:i') }}</span>
                        </div>
                    @endif
                    @if($foodOrder->ready_at)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Prête</span>
                            <span class="text-gray-900">{{ $foodOrder->ready_at->format('H:i') }}</span>
                        </div>
                    @endif
                    @if($foodOrder->delivered_at)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Livrée</span>
                            <span class="text-gray-900">{{ $foodOrder->delivered_at->format('H:i') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Type</span>
                        <span class="text-gray-900">{{ $foodOrder->delivery_type === 'pickup' ? 'À emporter' : 'Livraison' }}</span>
                    </div>
                    @if($foodOrder->delivery_contact_name)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Contact</span>
                            <span class="text-gray-900">{{ $foodOrder->delivery_contact_name }}</span>
                        </div>
                    @endif
                    @if($foodOrder->notes)
                        <div class="pt-2 border-t border-gray-100">
                            <p class="text-xs text-gray-500 mb-1">Notes</p>
                            <p class="text-sm text-gray-900">{{ $foodOrder->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Annuler la commande --}}
        @if($foodOrder->canBeCancelled())
            <button onclick="document.getElementById('cancelModal').classList.remove('hidden')" 
                    class="w-full py-3 text-red-500 hover:text-red-600 text-sm font-medium">
                Annuler la commande
            </button>
        @endif

    </div>

    {{-- Bottom bar fixe - Commander à nouveau --}}
    @if(in_array($foodOrder->status, ['completed', 'delivered']))

        {{-- Rating section --}}
        @if(!$foodOrder->rated_at)
        <div class="px-4 pb-4">
            <div class="bg-white rounded-2xl shadow-sm p-4">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-xl">⭐</span>
                    <h3 class="font-bold text-gray-900">Notez votre expérience</h3>
                </div>
                <form action="{{ route('food.orders.rate', $foodOrder) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    {{-- Note commande --}}
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-2">Note de la commande</label>
                        <div class="flex gap-2" x-data="{ rating: 0 }">
                            @for($i = 1; $i <= 5; $i++)
                            <button type="button" onclick="setRating({{ $i }})" id="star-{{ $i }}"
                                    class="w-10 h-10 text-2xl text-gray-300 hover:text-yellow-400 transition cursor-pointer">★</button>
                            @endfor
                            <input type="hidden" name="rating" id="ratingInput" required>
                        </div>
                    </div>

                    <div>
                        <textarea name="comment" rows="2" class="w-full rounded-xl border-gray-200 text-sm" 
                                  placeholder="Un commentaire ? (optionnel)"></textarea>
                    </div>

                    {{-- Note livreur (uniquement si livraison avec livreur) --}}
                    @if($foodOrder->delivery_type === 'delivery' && $foodOrder->driver_id)
                    <div class="border-t pt-4">
                        <label class="text-sm font-medium text-gray-700 block mb-2">
                            🛵 Note du livreur
                            @if($foodOrder->driver)
                                <span class="text-gray-400 font-normal">— {{ $foodOrder->driver->first_name ?? '' }}</span>
                            @endif
                        </label>
                        <div class="flex gap-2">
                            @for($i = 1; $i <= 5; $i++)
                            <button type="button" onclick="setDriverRating({{ $i }})" id="dstar-{{ $i }}"
                                    class="w-10 h-10 text-2xl text-gray-300 hover:text-yellow-400 transition cursor-pointer">★</button>
                            @endfor
                            <input type="hidden" name="driver_rating" id="driverRatingInput">
                        </div>
                        <textarea name="driver_comment" rows="1" class="w-full rounded-xl border-gray-200 text-sm mt-2" 
                                  placeholder="Commentaire sur le livreur (optionnel)"></textarea>
                    </div>
                    @endif

                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold rounded-xl transition hover:opacity-90">
                        Envoyer mon avis
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="px-4 pb-4">
            <div class="bg-green-50 rounded-2xl p-4 text-center">
                <span class="text-2xl">✅</span>
                <p class="text-green-800 font-medium mt-1">Merci pour votre avis !</p>
                <p class="text-sm text-green-600">{{ $foodOrder->rating }}/5 ★</p>
            </div>
        </div>
        @endif

        <div class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg p-4 safe-area-bottom">
            <a href="{{ route('food.menu', $foodOrder->prestataire) }}" 
               class="block w-full py-4 bg-gradient-to-r from-orange-500 to-amber-500 text-white text-center font-bold rounded-xl">
                🔄 Commander à nouveau
            </a>
        </div>
    @endif
</div>

{{-- Modal annulation --}}
<div id="cancelModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-end sm:items-center justify-center z-50">
    <div class="bg-white w-full sm:max-w-md sm:rounded-2xl rounded-t-3xl p-6 safe-area-bottom">
        <div class="w-12 h-1 bg-gray-300 rounded-full mx-auto mb-4 sm:hidden"></div>
        <h3 class="font-bold text-xl text-gray-900 mb-2">Annuler la commande ?</h3>
        <p class="text-gray-500 mb-3 text-sm">Cette action est irréversible.</p>

        @if(($clientCancelBreakdown['action'] ?? 'none') === 'cancel_authorization')
            <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 p-3 text-sm">
                <p class="font-semibold text-blue-800">Paiement non débité</p>
                <p class="text-blue-700 mt-1">Le paiement est seulement autorisé. En annulant maintenant, aucun débit final ne sera pris.</p>
            </div>
        @elseif(($clientCancelBreakdown['action'] ?? 'none') === 'refund')
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm">
                <p class="font-semibold text-amber-800">Estimation remboursement après annulation</p>
                <p class="text-amber-700 mt-1">Montant payé: {{ number_format((float) ($clientCancelBreakdown['amount_paid'] ?? 0), 2, ',', ' ') }} €</p>
                <p class="text-amber-700">Frais Stripe: -{{ number_format((float) ($clientCancelBreakdown['stripe_fee_amount'] ?? 0), 2, ',', ' ') }} €</p>
                <p class="font-semibold text-amber-900 mt-1">Vous serez remboursé: {{ number_format((float) ($clientCancelBreakdown['client_refund_amount'] ?? 0), 2, ',', ' ') }} €</p>
            </div>
        @endif

        <form action="{{ route('food.orders.cancel', $foodOrder) }}" method="POST">
            @csrf
            <textarea name="reason" rows="2" class="w-full rounded-xl border-gray-200 text-sm mb-4" placeholder="Raison (optionnel)"></textarea>
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('cancelModal').classList.add('hidden')" 
                        class="flex-1 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl">
                    Non
                </button>
                <button type="submit" class="flex-1 py-3 bg-red-500 text-white font-semibold rounded-xl">
                    Oui, annuler
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Accordion toggle
function toggleAccordion(id) {
    const content = document.getElementById(id);
    const icon = document.getElementById(id + 'Icon');
    content.classList.toggle('open');
    icon.style.transform = content.classList.contains('open') ? 'rotate(180deg)' : '';
}

// Map
document.addEventListener('DOMContentLoaded', function() {
    const mapEl = document.getElementById('orderMap');
    if (mapEl) {
        @if($foodOrder->delivery_type === 'pickup' && $foodOrder->prestataire->latitude && $foodOrder->prestataire->longitude)
            const lat = {{ $foodOrder->prestataire->latitude }};
            const lng = {{ $foodOrder->prestataire->longitude }};
        @elseif($foodOrder->delivery_lat && $foodOrder->delivery_lng)
            const lat = {{ $foodOrder->delivery_lat ?? 0 }};
            const lng = {{ $foodOrder->delivery_lng ?? 0 }};
        @else
            const lat = null; const lng = null;
        @endif
        
        if (lat && lng) {
            const map = L.map('orderMap', { zoomControl: false, attributionControl: false }).setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            L.marker([lat, lng]).addTo(map);
        }
    }
});

// Auto-refresh toutes les 15 sec si commande active
@if($isActive)
setInterval(() => {
    fetch('{{ route('food.orders.status', $foodOrder) }}')
        .then(r => r.json())
        .then(d => { if(d.status !== '{{ $foodOrder->status }}') location.reload(); })
        .catch(() => {});
}, 15000);
@endif

// Star rating functions
function setRating(n) {
    document.getElementById('ratingInput').value = n;
    for (let i = 1; i <= 5; i++) {
        document.getElementById('star-' + i).classList.toggle('text-yellow-400', i <= n);
        document.getElementById('star-' + i).classList.toggle('text-gray-300', i > n);
    }
}
function setDriverRating(n) {
    document.getElementById('driverRatingInput').value = n;
    for (let i = 1; i <= 5; i++) {
        const el = document.getElementById('dstar-' + i);
        if (el) {
            el.classList.toggle('text-yellow-400', i <= n);
            el.classList.toggle('text-gray-300', i > n);
        }
    }
}
</script>
@endpush
