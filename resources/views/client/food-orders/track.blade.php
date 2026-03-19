@extends('layouts.app')

@section('title', 'Suivi - Commande #' . $foodOrder->order_number)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .pulse-ring {
        animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }
    @keyframes pulse-ring {
        0% { transform: scale(0.9); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.5; }
        100% { transform: scale(0.9); opacity: 1; }
    }
    .bounce-icon {
        animation: bounce 2s ease-in-out infinite;
    }
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .shimmer {
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        background-size: 200% 100%;
        animation: shimmer 2s infinite;
    }
    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    .status-glow {
        box-shadow: 0 0 40px rgba(249, 115, 22, 0.3);
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
    }
    #trackingMap {
        height: 300px;
        border-radius: 0;
        z-index: 1;
    }
    .live-indicator {
        animation: blink 1.5s ease-in-out infinite;
    }
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    .driver-marker-icon {
        background: none;
        border: none;
    }
    .driver-dot {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #f97316, #ea580c);
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        position: relative;
    }
    .driver-dot::after {
        content: '';
        position: absolute;
        inset: -6px;
        border-radius: 50%;
        border: 2px solid rgba(249, 115, 22, 0.4);
        animation: pulse-ring 2s ease-out infinite;
    }
    .restaurant-dot {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    .destination-dot {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #10b981, #059669);
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    .eta-badge {
        position: absolute;
        bottom: 8px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
        z-index: 1000;
        pointer-events: none;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 via-amber-50 to-yellow-50">
    <div class="container mx-auto px-4 py-6">
        
        {{-- Header avec retour --}}
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('food.orders.show', $foodOrder) }}" 
               class="flex items-center gap-2 text-gray-600 hover:text-orange-600 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="font-medium">Retour</span>
            </a>
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <span class="w-2 h-2 bg-green-500 rounded-full live-indicator" id="liveIndicator"></span>
                <span id="liveText">Suivi en direct</span>
            </div>
        </div>

        <div class="max-w-lg mx-auto">
            
            {{-- Carte de statut principal --}}
            @php
                $statusConfig = [
                    'pending' => [
                        'gradient' => 'from-amber-400 to-orange-500',
                        'icon' => '⏳',
                        'title' => 'En attente',
                        'message' => 'Le prestataire va bientôt confirmer votre commande',
                    ],
                    'accepted' => [
                        'gradient' => 'from-blue-400 to-indigo-500',
                        'icon' => '✅',
                        'title' => 'Acceptée !',
                        'message' => 'Votre commande a été acceptée et va être préparée',
                    ],
                    'preparing' => [
                        'gradient' => 'from-purple-400 to-pink-500',
                        'icon' => '👨‍🍳',
                        'title' => 'En préparation',
                        'message' => 'Le chef prépare votre commande avec soin',
                    ],
                    'ready' => [
                        'gradient' => 'from-green-400 to-emerald-500',
                        'icon' => $foodOrder->delivery_type === 'pickup' ? '🎉' : '🚚',
                        'title' => $foodOrder->delivery_type === 'pickup' ? 'Prête !' : 'En route !',
                        'message' => $foodOrder->delivery_type === 'pickup' 
                            ? 'Votre commande vous attend ! Venez la récupérer' 
                            : 'Votre commande est en cours de livraison',
                    ],
                    'delivered' => [
                        'gradient' => 'from-green-500 to-teal-500',
                        'icon' => '📦',
                        'title' => 'Livrée',
                        'message' => 'Votre commande a été livrée. Bon appétit !',
                    ],
                    'completed' => [
                        'gradient' => 'from-green-500 to-emerald-600',
                        'icon' => '⭐',
                        'title' => 'Terminée',
                        'message' => 'Merci pour votre commande !',
                    ],
                    'cancelled' => [
                        'gradient' => 'from-red-400 to-rose-500',
                        'icon' => '❌',
                        'title' => 'Annulée',
                        'message' => 'Cette commande a été annulée',
                    ],
                    'rejected' => [
                        'gradient' => 'from-red-400 to-rose-500',
                        'icon' => '❌',
                        'title' => 'Refusée',
                        'message' => 'Cette commande a été refusée par le prestataire',
                    ],
                ];
                $config = $statusConfig[$foodOrder->status] ?? $statusConfig['pending'];
                $clientCancelBreakdown = $foodOrder->getCancellationBreakdown('client');
            @endphp

            <div class="glass-card rounded-3xl shadow-2xl overflow-hidden mb-6 status-glow" id="statusCard">
                {{-- Header animé --}}
                <div class="bg-gradient-to-r {{ $config['gradient'] }} p-6 text-white text-center relative overflow-hidden">
                    <div class="absolute inset-0 shimmer opacity-30"></div>
                    <div class="relative z-10">
                        <div class="w-20 h-20 mx-auto bg-white/20 rounded-full flex items-center justify-center mb-3 pulse-ring">
                            <span class="text-4xl bounce-icon">{{ $config['icon'] }}</span>
                        </div>
                        <h2 class="text-2xl font-bold mb-1" id="statusTitle">{{ $config['title'] }}</h2>
                        <p class="text-white/90 text-sm" id="statusMessage">{{ $config['message'] }}</p>
                        <div class="mt-3 inline-flex items-center gap-2 bg-white/20 px-3 py-1.5 rounded-full text-sm">
                            <span class="font-medium">Commande</span>
                            <span class="font-bold">#{{ $foodOrder->order_number }}</span>
                        </div>
                    </div>
                </div>

                {{-- Temps estimé --}}
                @if(!in_array($foodOrder->status, ['cancelled', 'rejected', 'completed', 'delivered']))
                    <div class="bg-orange-50 border-b border-orange-100 p-3">
                        <div class="flex items-center justify-center gap-3">
                            <span class="text-xl">⏱️</span>
                            <div class="text-center">
                                <p class="text-xs text-gray-600">Temps estimé</p>
                                <p class="text-lg font-bold text-orange-600" id="estimatedTime">
                                    @if($foodOrder->status === 'pending')
                                        En attente de confirmation
                                    @elseif($foodOrder->status === 'accepted' || $foodOrder->status === 'preparing')
                                        {{ $foodOrder->prestataire->food_estimated_prep_time ?? 30 }} min
                                    @elseif($foodOrder->status === 'ready')
                                        {{ $foodOrder->delivery_type === 'pickup' ? 'Prête à récupérer' : '~10 min' }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Timeline --}}
                <div class="p-5">
                    @php
                        $steps = [
                            ['key' => 'created', 'label' => 'Commande passée', 'time' => $foodOrder->created_at, 'icon' => '🛒', 'completed' => true],
                            ['key' => 'accepted', 'label' => 'Acceptée', 'time' => $foodOrder->accepted_at, 'icon' => '✓', 'completed' => $foodOrder->accepted_at !== null],
                            ['key' => 'preparing', 'label' => 'En préparation', 'time' => $foodOrder->preparing_at, 'icon' => '👨‍🍳', 'completed' => $foodOrder->preparing_at !== null],
                            ['key' => 'ready', 'label' => 'Prête', 'time' => $foodOrder->ready_at, 'icon' => '✨', 'completed' => $foodOrder->ready_at !== null],
                            ['key' => 'delivered', 'label' => $foodOrder->delivery_type === 'pickup' ? 'Récupérée' : 'Livrée', 'time' => $foodOrder->delivered_at, 'icon' => '🎉', 'completed' => $foodOrder->delivered_at !== null],
                        ];
                        $currentStepIndex = 0;
                        foreach($steps as $i => $step) {
                            if ($step['completed']) $currentStepIndex = $i;
                        }
                    @endphp

                    <div class="flex items-center justify-between">
                        @foreach($steps as $index => $step)
                            <div class="flex flex-col items-center {{ $index > 0 ? 'flex-1' : '' }}">
                                @if($index > 0)
                                    <div class="w-full h-1 rounded {{ $step['completed'] ? 'bg-green-400' : 'bg-gray-200' }} mb-2"></div>
                                @endif
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs shadow
                                    {{ $step['completed'] 
                                        ? 'bg-green-500 text-white' 
                                        : ($index === $currentStepIndex + 1 && !in_array($foodOrder->status, ['cancelled', 'rejected'])
                                            ? 'bg-orange-400 text-white animate-pulse' 
                                            : 'bg-gray-200 text-gray-400') }}">
                                    @if($step['completed'])
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @else
                                        {{ $step['icon'] }}
                                    @endif
                                </div>
                                <span class="text-[10px] mt-1 text-center {{ $step['completed'] ? 'text-gray-900 font-medium' : 'text-gray-400' }}">
                                    {{ $step['label'] }}
                                </span>
                                @if($step['time'])
                                    <span class="text-[9px] text-green-600">{{ $step['time']->format('H:i') }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Code de vérification (visible quand commande prête/livrée) --}}
            @if($foodOrder->delivery_code && in_array($foodOrder->status, ['ready', 'delivered']))
                <div class="glass-card rounded-2xl shadow-xl overflow-hidden mb-6" id="deliveryCodeCard">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-5 text-white text-center">
                        <div class="flex items-center justify-center gap-3 mb-3">
                            <span class="text-3xl">🔐</span>
                            <div class="text-left">
                                <p class="font-bold text-lg">{{ $foodOrder->delivery_type === 'pickup' ? 'Code de retrait' : 'Code de confirmation' }}</p>
                                <p class="text-sm text-white/80">{{ $foodOrder->delivery_type === 'pickup' ? 'Donnez ce code au prestataire lors du retrait' : 'Donnez ce code au livreur à la réception' }}</p>
                            </div>
                        </div>
                        <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-5">
                            <p class="text-5xl font-mono font-bold tracking-[0.5em] text-center">{{ $foodOrder->delivery_code }}</p>
                        </div>
                        <p class="text-xs text-white/60 mt-3">⏱️ Ce code expire dans 24h</p>
                    </div>
                </div>
            @endif

            {{-- Carte GPS avec suivi livreur en temps réel --}}
            @if($foodOrder->delivery_type === 'delivery' && $foodOrder->delivery_lat && $foodOrder->delivery_lng)
                <div class="glass-card rounded-2xl shadow-xl overflow-hidden mb-6">
                    <div class="p-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900 flex items-center gap-2 text-sm">
                            <span>🗺️</span> Suivi GPS en direct
                        </h3>
                        <div class="flex items-center gap-1.5" id="driverStatusBadge">
                            @if($foodOrder->driver_id)
                                <span class="w-2 h-2 bg-green-500 rounded-full live-indicator"></span>
                                <span class="text-xs text-green-600 font-medium">Livreur connecté</span>
                            @else
                                <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                                <span class="text-xs text-gray-500">En attente de livreur</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="relative">
                        <div id="trackingMap"></div>
                        <div class="eta-badge" id="etaBadge" style="display: none;">
                            <span id="etaText"></span>
                        </div>
                    </div>

                    {{-- Légende --}}
                    <div class="p-3 bg-gray-50 flex items-center justify-around text-xs text-gray-600">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-purple-500 inline-block"></span>
                            Restaurant
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-orange-500 inline-block"></span>
                            Livreur
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span>
                            Vous
                        </div>
                    </div>
                </div>
            @elseif($foodOrder->delivery_type === 'pickup' && $foodOrder->prestataire->latitude && $foodOrder->prestataire->longitude)
                <div class="glass-card rounded-2xl shadow-xl overflow-hidden mb-6">
                    <div class="p-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-bold text-gray-900 flex items-center gap-2 text-sm">
                            <span>📍</span> Adresse de retrait
                        </h3>
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $foodOrder->prestataire->latitude }},{{ $foodOrder->prestataire->longitude }}" 
                           target="_blank"
                           class="text-xs text-orange-600 hover:text-orange-700 font-medium flex items-center gap-1">
                            Itinéraire
                        </a>
                    </div>
                    <div id="trackingMap"></div>
                    <div class="p-3 bg-gray-50">
                        <p class="text-gray-700 font-medium text-sm">{{ $foodOrder->prestataire->company_name ?? $foodOrder->prestataire->business_name }}</p>
                        <p class="text-gray-500 text-xs">{{ $foodOrder->prestataire->address }}, {{ $foodOrder->prestataire->postal_code }} {{ $foodOrder->prestataire->city }}</p>
                    </div>
                </div>
            @endif

            {{-- Info livreur (si assigné) --}}
            @if($foodOrder->driver_id && $foodOrder->delivery_type === 'delivery')
                @php $driver = $foodOrder->driver; @endphp
                @if($driver)
                    <div class="glass-card rounded-2xl shadow-xl p-4 mb-6">
                        <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2 text-sm">
                            <span>🛵</span> Votre livreur
                        </h3>
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-orange-100 to-amber-100 flex items-center justify-center text-2xl shadow">
                                🏍️
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-gray-900">{{ $driver->first_name ?? 'Livreur' }} {{ substr($driver->last_name ?? '', 0, 1) }}.</h4>
                                <p class="text-gray-500 text-xs">{{ ucfirst($driver->vehicle_type ?? 'scooter') }}</p>
                                <div class="flex items-center gap-1 mt-0.5">
                                    @if($driver->average_rating)
                                        <span class="text-yellow-400 text-xs">★</span>
                                        <span class="text-xs text-gray-600 font-medium">{{ number_format($driver->average_rating, 1) }}</span>
                                        <span class="text-xs text-gray-400">({{ $driver->total_ratings ?? 0 }})</span>
                                    @endif
                                </div>
                            </div>
                            @if($driver->phone)
                                <a href="tel:{{ $driver->phone }}" 
                                   class="w-12 h-12 bg-green-500 hover:bg-green-600 text-white rounded-xl flex items-center justify-center shadow-lg transition">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            @endif

            {{-- Infos prestataire --}}
            <div class="glass-card rounded-2xl shadow-xl p-4 mb-6">
                <h3 class="font-bold text-gray-900 mb-3 flex items-center gap-2 text-sm">
                    <span>👨‍🍳</span> Votre prestataire
                </h3>
                <a href="{{ route('food.menu', $foodOrder->prestataire) }}" class="flex items-center gap-3 group">
                    @if($foodOrder->prestataire->profile_image)
                        <img src="{{ asset('storage/' . $foodOrder->prestataire->profile_image) }}"
                             alt="{{ $foodOrder->prestataire->business_name ?? 'Restaurant' }}"
                             class="w-14 h-14 rounded-xl object-cover shadow group-hover:ring-2 ring-orange-400 transition">
                    @else
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-orange-100 to-amber-100 flex items-center justify-center text-2xl shadow group-hover:ring-2 ring-orange-400 transition">
                            👨‍🍳
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-gray-900 truncate group-hover:text-orange-600 transition text-sm">{{ $foodOrder->prestataire->company_name ?? $foodOrder->prestataire->business_name }}</h4>
                        <p class="text-gray-500 text-xs">{{ $foodOrder->prestataire->city }}</p>
                    </div>
                </a>
                <div class="mt-3 flex gap-2">
                    @if($foodOrder->prestataire->phone)
                        <a href="tel:{{ $foodOrder->prestataire->phone }}" 
                           class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-gradient-to-r from-green-500 to-emerald-500 text-white font-bold rounded-xl shadow text-sm">
                            📞 Appeler
                        </a>
                    @endif
                    <a href="{{ route('food.menu', $foodOrder->prestataire) }}" 
                       class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-orange-100 hover:bg-orange-200 text-orange-600 font-bold rounded-xl text-sm">
                        Profil
                    </a>
                </div>
            </div>

            {{-- Résumé commande compact --}}
            <div class="glass-card rounded-2xl shadow-xl overflow-hidden mb-6">
                <div class="p-3 border-b border-gray-100 bg-gradient-to-r from-orange-50 to-amber-50">
                    <h3 class="font-bold text-gray-900 flex items-center gap-2 text-sm">
                        <span>🛒</span> Résumé
                    </h3>
                </div>
                <div class="p-3">
                    <div class="space-y-2">
                        @foreach($foodOrder->items as $item)
                            <div class="flex justify-between items-center text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 bg-orange-100 rounded flex items-center justify-center text-xs font-bold text-orange-600">
                                        {{ $item->quantity }}
                                    </span>
                                    <span class="text-gray-700">{{ $item->product_name }}</span>
                                </div>
                                <span class="font-semibold text-gray-900">{{ number_format($item->total_price, 2) }} €</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t border-gray-100 mt-3 pt-3 flex justify-between items-center">
                        <span class="font-bold text-gray-900">Total</span>
                        <span class="text-xl font-bold bg-gradient-to-r from-orange-500 to-amber-500 bg-clip-text text-transparent">
                            {{ number_format($foodOrder->total, 2) }} €
                        </span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="space-y-3 mb-8">
                @if($foodOrder->isDelivered() && !$foodOrder->client_confirmed)
                    <form action="{{ route('food.orders.confirm', $foodOrder) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2">
                            ✅ Confirmer la réception
                        </button>
                    </form>
                @endif

                <a href="{{ route('food.orders.show', $foodOrder) }}" 
                   class="w-full py-3 bg-white border-2 border-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition flex items-center justify-center gap-2 text-sm">
                    📄 Voir les détails complets
                </a>

                @if($foodOrder->canBeCancelled())
                    <button onclick="document.getElementById('cancelModal').classList.remove('hidden')" 
                            class="w-full py-2 text-red-500 hover:text-red-600 font-medium transition text-sm">
                        Annuler la commande
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal annulation --}}
<div id="cancelModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-md w-full">
        <h3 class="font-bold text-xl text-gray-900 mb-2">Annuler la commande ?</h3>
        <p class="text-gray-500 mb-3">Cette action ne peut pas être annulée.</p>

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
            <textarea name="reason" rows="3" class="w-full rounded-xl border-gray-300 focus:border-orange-500 focus:ring-orange-500 mb-4" 
                      placeholder="Raison de l'annulation (optionnel)"></textarea>
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('cancelModal').classList.add('hidden')" 
                        class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition">
                    Non, garder
                </button>
                <button type="submit" class="flex-1 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-xl transition">
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
document.addEventListener('DOMContentLoaded', function() {

    // ===== CONFIGURATION =====
    const ORDER_ID = {{ $foodOrder->id }};
    const DELIVERY_TYPE = '{{ $foodOrder->delivery_type }}';
    const INITIAL_STATUS = '{{ $foodOrder->status }}';
    const ROUTE_PROXY_URL = '{{ route("food.orders.route-proxy") }}';
    
    const STATUS_URL = '{{ route("food.orders.status", $foodOrder) }}';
    const DRIVER_URL = '{{ route("food.orders.driver-location", $foodOrder) }}';
    
    // Coordonnées statiques
    const RESTAURANT = {
        lat: {{ $foodOrder->prestataire->latitude ?? 0 }},
        lng: {{ $foodOrder->prestataire->longitude ?? 0 }}
    };
    const DESTINATION = {
        lat: {{ $foodOrder->delivery_lat ?? ($foodOrder->prestataire->latitude ?? 0) }},
        lng: {{ $foodOrder->delivery_lng ?? ($foodOrder->prestataire->longitude ?? 0) }}
    };

    // ===== ÉTAT =====
    let map = null;
    let driverMarker = null;
    let restaurantMarker = null;
    let destinationMarker = null;
    let routePolyline = null;
    let lastStatus = INITIAL_STATUS;
    let lastDriverLat = null;
    let lastDriverLng = null;
    let routeCache = null;

    // ===== INITIALISER LA CARTE =====
    const mapContainer = document.getElementById('trackingMap');
    if (mapContainer) {
        let centerLat, centerLng, zoom;
        if (DELIVERY_TYPE === 'pickup') {
            centerLat = RESTAURANT.lat;
            centerLng = RESTAURANT.lng;
            zoom = 15;
        } else {
            centerLat = (RESTAURANT.lat + DESTINATION.lat) / 2;
            centerLng = (RESTAURANT.lng + DESTINATION.lng) / 2;
            zoom = 13;
        }

        if (centerLat && centerLng) {
            map = L.map('trackingMap', {
                zoomControl: false,
                attributionControl: false
            }).setView([centerLat, centerLng], zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);

            L.control.zoom({ position: 'bottomright' }).addTo(map);

            if (DELIVERY_TYPE === 'pickup') {
                // Mode retrait : marqueur restaurant seulement
                const pickupIcon = L.divIcon({
                    className: 'driver-marker-icon',
                    html: '<div class="restaurant-dot">🍽️</div>',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });
                L.marker([RESTAURANT.lat, RESTAURANT.lng], { icon: pickupIcon })
                    .addTo(map)
                    .bindPopup({!! Js::from($foodOrder->prestataire->company_name ?? $foodOrder->prestataire->business_name) !!});
            } else {
                // Mode livraison : restaurant + destination + futur livreur
                
                // Marqueur restaurant
                const restIcon = L.divIcon({
                    className: 'driver-marker-icon',
                    html: '<div class="restaurant-dot">🍽️</div>',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });
                restaurantMarker = L.marker([RESTAURANT.lat, RESTAURANT.lng], { icon: restIcon })
                    .addTo(map)
                    .bindPopup({!! Js::from($foodOrder->prestataire->company_name ?? $foodOrder->prestataire->business_name) !!});

                // Marqueur destination (adresse client)
                const destIcon = L.divIcon({
                    className: 'driver-marker-icon',
                    html: '<div class="destination-dot">🏠</div>',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });
                destinationMarker = L.marker([DESTINATION.lat, DESTINATION.lng], { icon: destIcon })
                    .addTo(map)
                    .bindPopup('Votre adresse');

                // Ajuster la vue pour montrer les deux points
                const bounds = L.latLngBounds([
                    [RESTAURANT.lat, RESTAURANT.lng],
                    [DESTINATION.lat, DESTINATION.lng]
                ]);
                map.fitBounds(bounds, { padding: [40, 40] });

                // Commencer le polling de la position du livreur
                pollDriverLocation();
                setInterval(pollDriverLocation, 5000);
            }
        }
    }

    // ===== POLLING POSITION LIVREUR =====
    function pollDriverLocation() {
        if (DELIVERY_TYPE !== 'delivery') return;
        if (['cancelled', 'rejected', 'completed'].includes(lastStatus)) return;

        fetch(DRIVER_URL, {
            headers: { 
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.driver && data.driver.lat && data.driver.lng) {
                updateDriverMarker(data.driver);
                updateDriverStatus(data.driver);

                // Tracer la route via le proxy backend
                if (ROUTE_PROXY_URL && !routeCache) {
                    fetchRoute(data.driver, DESTINATION);
                }
            }
        })
        .catch(err => console.log('GPS polling error:', err));
    }

    // ===== METTRE À JOUR LE MARQUEUR LIVREUR =====
    function updateDriverMarker(driver) {
        const lat = driver.lat;
        const lng = driver.lng;

        // Vérifier si la position a changé significativement (> ~10m)
        if (lastDriverLat !== null) {
            const dist = Math.sqrt(Math.pow(lat - lastDriverLat, 2) + Math.pow(lng - lastDriverLng, 2));
            if (dist < 0.0001) return;
        }

        lastDriverLat = lat;
        lastDriverLng = lng;

        const driverIcon = L.divIcon({
            className: 'driver-marker-icon',
            html: '<div class="driver-dot">🛵</div>',
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });

        if (!driverMarker) {
            driverMarker = L.marker([lat, lng], { 
                icon: driverIcon,
                zIndexOffset: 1000 
            }).addTo(map)
              .bindPopup(driver.name || 'Livreur');
        } else {
            driverMarker.setLatLng([lat, lng]);
            driverMarker.setIcon(driverIcon);
        }

        // Ajuster la vue pour inclure tous les points
        const allPoints = [
            [lat, lng],
            [DESTINATION.lat, DESTINATION.lng]
        ];
        if (RESTAURANT.lat && RESTAURANT.lng) {
            allPoints.push([RESTAURANT.lat, RESTAURANT.lng]);
        }
        const bounds = L.latLngBounds(allPoints);
        map.fitBounds(bounds, { padding: [50, 50], maxZoom: 16, animate: true });

        // Recalculer la route
        routeCache = null;
        if (ROUTE_PROXY_URL) {
            fetchRoute({ lat, lng }, DESTINATION);
        }
    }

    // ===== METTRE À JOUR LE STATUT DU LIVREUR =====
    function updateDriverStatus(driver) {
        const badge = document.getElementById('driverStatusBadge');
        if (!badge) return;

        if (driver.is_online) {
            badge.innerHTML = '<span class="w-2 h-2 bg-green-500 rounded-full live-indicator"></span>' +
                '<span class="text-xs text-green-600 font-medium">Livreur en route</span>';
        } else {
            badge.innerHTML = '<span class="w-2 h-2 bg-yellow-500 rounded-full"></span>' +
                '<span class="text-xs text-yellow-600 font-medium">Dernière position connue</span>';
        }
    }

    // ===== TRACER LA ROUTE VIA PROXY BACKEND =====
    function fetchRoute(from, to) {
        if (!ROUTE_PROXY_URL) return;

        const url = ROUTE_PROXY_URL + '?start_lng=' + from.lng + '&start_lat=' + from.lat +
            '&end_lng=' + to.lng + '&end_lat=' + to.lat;

        fetch(url)
        .then(r => r.json())
        .then(data => {
            if (data.features && data.features.length > 0) {
                const coords = data.features[0].geometry.coordinates.map(function(c) { return [c[1], c[0]]; });
                const props = data.features[0].properties.summary;

                // Supprimer ancienne route
                if (routePolyline) {
                    map.removeLayer(routePolyline);
                }

                // Tracer la nouvelle route
                routePolyline = L.polyline(coords, {
                    color: '#f97316',
                    weight: 4,
                    opacity: 0.8,
                    dashArray: '8, 8',
                    lineCap: 'round'
                }).addTo(map);

                routeCache = data;

                // Afficher l'ETA
                if (props && props.duration) {
                    const minutes = Math.ceil(props.duration / 60);
                    const distance = (props.distance / 1000).toFixed(1);
                    const etaBadge = document.getElementById('etaBadge');
                    const etaText = document.getElementById('etaText');
                    if (etaBadge && etaText) {
                        etaText.textContent = '🛵 ' + minutes + ' min • ' + distance + ' km';
                        etaBadge.style.display = 'block';
                    }
                    // Mettre à jour le temps estimé en haut
                    const estimatedEl = document.getElementById('estimatedTime');
                    if (estimatedEl && lastStatus === 'ready') {
                        estimatedEl.textContent = '~' + minutes + ' min (' + distance + ' km)';
                    }
                }
            }
        })
        .catch(function(err) { console.log('Route API error:', err); });
    }

    // ===== AUTO-REFRESH STATUT =====
    function checkStatus() {
        fetch(STATUS_URL, {
            headers: { 
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.status !== lastStatus) {
                lastStatus = data.status;
                // Si le code est disponible, l'injecter avant le reload
                if (data.delivery_code && !document.getElementById('deliveryCodeCard')) {
                    const codeHtml = `
                        <div class="glass-card rounded-2xl shadow-xl overflow-hidden mb-6" id="deliveryCodeCard" style="animation: fadeInUp .4s ease">
                            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-5 text-white text-center">
                                <div class="flex items-center justify-center gap-3 mb-3">
                                    <span class="text-3xl">🔐</span>
                                    <div class="text-left">
                                        <p class="font-bold text-lg">${DELIVERY_TYPE === 'pickup' ? 'Code de retrait' : 'Code de confirmation'}</p>
                                        <p class="text-sm text-white/80">${DELIVERY_TYPE === 'pickup' ? 'Donnez ce code au prestataire' : 'Donnez ce code au livreur'}</p>
                                    </div>
                                </div>
                                <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-5">
                                    <p class="text-5xl font-mono font-bold tracking-[0.5em] text-center">${data.delivery_code}</p>
                                </div>
                            </div>
                        </div>`;
                    const statusCard = document.getElementById('statusCard');
                    if (statusCard) statusCard.insertAdjacentHTML('afterend', codeHtml);
                }
                playNotificationSound();
                location.reload();
            }
        })
        .catch(function(err) { console.log('Status check error:', err); });
    }

    function playNotificationSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 800;
            osc.type = 'sine';
            gain.gain.value = 0.3;
            osc.start();
            setTimeout(function() { osc.stop(); }, 200);
        } catch (e) {}
    }

    // Vérifier le statut toutes les 10 secondes si commande active
    if (!['cancelled', 'rejected', 'completed'].includes(INITIAL_STATUS)) {
        setInterval(checkStatus, 10000);
    }

});
</script>
@endpush
