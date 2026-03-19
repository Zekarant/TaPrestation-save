@extends('layouts.app')

@section('title', 'Commande #' . $foodOrder->order_number)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 1.25rem;
        top: 2.5rem;
        bottom: -1rem;
        width: 2px;
        background: linear-gradient(to bottom, #d1d5db, transparent);
    }
    .timeline-item:last-child::before {
        display: none;
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(12px);
    }
</style>
@endpush

@section('content')
{{-- Section d'aide --}}
<div class="px-4 pt-4 bg-orange-50">
    <x-help-section page="prestataire.food-orders.show" />
</div>

<div class="min-h-screen bg-gradient-to-br from-orange-50 via-amber-50 to-yellow-50">
    
    {{-- Header --}}
    @php
        // Déterminer la politique de paiement
        $paymentPolicy = $foodOrder->getPaymentPolicy();
        $requiresOnlinePayment = $paymentPolicy['type'] !== 'cash';
        $onlinePaymentPending = $requiresOnlinePayment && $foodOrder->payment_status !== 'paid';
        $prestataireCancelBreakdown = $foodOrder->getCancellationBreakdown('prestataire');
        
        $statusConfig = [
            'pending' => ['gradient' => 'from-amber-500 to-orange-500', 'icon' => '⏳', 'label' => 'En attente', 'bg' => 'bg-amber-100', 'text' => 'text-amber-700'],
            'accepted' => ['gradient' => 'from-blue-500 to-blue-600', 'icon' => '✓', 'label' => 'Acceptée', 'bg' => 'bg-blue-100', 'text' => 'text-blue-700'],
            'preparing' => ['gradient' => 'from-purple-500 to-purple-600', 'icon' => '🔥', 'label' => 'En préparation', 'bg' => 'bg-purple-100', 'text' => 'text-purple-700'],
            'ready' => ['gradient' => 'from-green-500 to-emerald-500', 'icon' => '✅', 'label' => 'Prête', 'bg' => 'bg-green-100', 'text' => 'text-green-700'],
            'delivered' => ['gradient' => 'from-emerald-500 to-teal-500', 'icon' => '📦', 'label' => 'Livrée', 'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700'],
            'completed' => ['gradient' => 'from-gray-500 to-gray-600', 'icon' => '🏁', 'label' => 'Terminée', 'bg' => 'bg-gray-100', 'text' => 'text-gray-700'],
            'cancelled' => ['gradient' => 'from-red-500 to-red-600', 'icon' => '❌', 'label' => 'Annulée', 'bg' => 'bg-red-100', 'text' => 'text-red-700'],
            'rejected' => ['gradient' => 'from-red-500 to-red-600', 'icon' => '🚫', 'label' => 'Refusée', 'bg' => 'bg-red-100', 'text' => 'text-red-700'],
        ];
        $config = $statusConfig[$foodOrder->status] ?? $statusConfig['pending'];
    @endphp

    <div class="bg-gradient-to-r {{ $config['gradient'] }} text-white">
        <div class="container mx-auto px-4 py-6 sm:py-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <a href="{{ route('prestataire.food-orders.index') }}" class="inline-flex items-center gap-2 text-white/80 hover:text-white mb-3 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Retour aux commandes
                    </a>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center shadow-lg">
                            <span class="text-3xl">{{ $config['icon'] }}</span>
                        </div>
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-bold">Commande #{{ $foodOrder->order_number }}</h1>
                            <div class="flex flex-wrap items-center gap-3 mt-1">
                                <span class="px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm font-semibold">
                                    {{ $config['label'] }}
                                </span>
                                <span class="text-white/80 text-sm">
                                    Créée le {{ $foodOrder->created_at->format('d/m/Y à H:i') }}
                                </span>
                                @if($foodOrder->requested_at)
                                    <span class="px-3 py-1 bg-white/30 backdrop-blur-sm rounded-full text-sm font-semibold flex items-center gap-1">
                                        📅 Pour le {{ $foodOrder->requested_at->format('d/m/Y à H:i') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('prestataire.food-orders.dashboard') }}" 
                       class="px-5 py-2.5 bg-white/20 backdrop-blur-sm text-white rounded-xl font-semibold hover:bg-white/30 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        </svg>
                        Cuisine Live
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Colonne principale --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Actions rapides --}}
                @if(!in_array($foodOrder->status, ['completed', 'cancelled', 'rejected', 'delivered']))
                    <div class="glass-card rounded-2xl shadow-xl p-5 border border-white/50">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Actions rapides
                        </h3>
                        
                        {{-- Alerte paiement en ligne requis --}}
                        @if($onlinePaymentPending && $foodOrder->status === 'ready')
                            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-300 rounded-xl">
                                <div class="flex items-start gap-3">
                                    <span class="text-2xl">⚠️</span>
                                    <div>
                                        <h4 class="font-bold text-yellow-800">Paiement en ligne requis</h4>
                                        <p class="text-sm text-yellow-700 mt-1">
                                            @if($paymentPolicy['type'] === 'full_prepay')
                                                Ce produit nécessite un <strong>paiement intégral en ligne</strong> avant remise.
                                            @else
                                                Ce produit nécessite un <strong>acompte de {{ $paymentPolicy['percent'] }}%</strong> avant remise.
                                            @endif
                                            <br>Le client doit d'abord payer en ligne pour que vous puissiez valider la remise.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div class="flex flex-wrap gap-3">
                            @if($foodOrder->status === 'pending')
                                <form action="{{ route('prestataire.food-orders.accept', $foodOrder) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Accepter la commande
                                    </button>
                                </form>
                                <button onclick="showRejectModal()" class="px-6 py-3 bg-red-100 hover:bg-red-200 text-red-600 font-bold rounded-xl transition-all flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Refuser
                                </button>
                            @elseif($foodOrder->status === 'accepted')
                                <form action="{{ route('prestataire.food-orders.start-preparing', $foodOrder) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
                                        🔥 Commencer la préparation
                                    </button>
                                </form>
                                <button onclick="showCancelModal()" class="px-6 py-3 bg-red-100 hover:bg-red-200 text-red-600 font-bold rounded-xl transition-all flex items-center gap-2">
                                    ❌ Annuler la commande
                                </button>
                            @elseif($foodOrder->status === 'preparing')
                                <form action="{{ route('prestataire.food-orders.ready', $foodOrder) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
                                        ✅ Marquer comme prêt
                                    </button>
                                </form>
                                <button onclick="showCancelModal()" class="px-6 py-3 bg-red-100 hover:bg-red-200 text-red-600 font-bold rounded-xl transition-all flex items-center gap-2">
                                    ❌ Annuler la commande
                                </button>
                            @elseif($foodOrder->status === 'ready')
                                @if($onlinePaymentPending)
                                    {{-- Bouton désactivé si paiement en ligne requis mais pas fait --}}
                                    <button type="button" disabled class="px-6 py-3 bg-gray-300 text-gray-500 font-bold rounded-xl cursor-not-allowed flex items-center gap-2" title="En attente du paiement en ligne">
                                        🔒 {{ $foodOrder->delivery_type === 'pickup' ? 'Client a récupéré' : 'Commande livrée' }}
                                    </button>
                                    <span class="text-sm text-yellow-600 italic">⏳ En attente du paiement du client</span>
                                @else
                                    @if($foodOrder->delivery_type === 'pickup')
                                        <form action="{{ route('prestataire.food-orders.delivered', $foodOrder) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
                                                🤝 Client a récupéré
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" disabled class="px-6 py-3 bg-gray-300 text-gray-500 font-bold rounded-xl cursor-not-allowed flex items-center gap-2" title="Le livreur doit saisir le code client pour terminer la livraison">
                                            🔐 Validation par livreur (code client)
                                        </button>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Articles commandés --}}
                <div class="glass-card rounded-2xl shadow-xl overflow-hidden border border-white/50">
                    <div class="p-5 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Articles commandés
                            <span class="ml-2 px-2.5 py-0.5 bg-orange-100 text-orange-600 text-sm rounded-full">
                                {{ $foodOrder->items->sum('quantity') }} articles
                            </span>
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach($foodOrder->items as $item)
                            <div class="p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-orange-100 to-amber-100 rounded-xl flex items-center justify-center">
                                        <span class="text-lg font-bold text-orange-600">{{ $item->quantity }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900">{{ $item->product_name }}</h4>
                                        @if($item->options)
                                            <p class="text-sm text-gray-500">{{ is_array($item->options) ? implode(', ', $item->options) : $item->options }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <span class="text-lg font-bold text-gray-900">{{ number_format($item->unit_price * $item->quantity, 2) }} €</span>
                                        <p class="text-xs text-gray-500">{{ number_format($item->unit_price, 2) }}€/unité</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="p-5 bg-gradient-to-r from-orange-50 to-amber-50 border-t border-orange-100">
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Sous-total</span>
                                <span class="font-medium">{{ number_format($foodOrder->subtotal ?? $foodOrder->total, 2) }} €</span>
                            </div>
                            @if($foodOrder->delivery_fee > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Frais de livraison</span>
                                    <span class="font-medium">{{ number_format($foodOrder->delivery_fee, 2) }} €</span>
                                </div>
                            @endif
                            @if(isset($foodOrder->discount) && $foodOrder->discount > 0)
                                <div class="flex justify-between text-sm text-green-600">
                                    <span>Réduction</span>
                                    <span class="font-medium">-{{ number_format($foodOrder->discount, 2) }} €</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-xl font-bold pt-2 border-t border-orange-200">
                                <span class="text-gray-900">Total</span>
                                <span class="bg-gradient-to-r from-orange-500 to-amber-500 bg-clip-text text-transparent">{{ number_format($foodOrder->total, 2) }} €</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notes du client --}}
                @if($foodOrder->notes)
                    <div class="glass-card rounded-2xl shadow-xl p-5 border border-white/50">
                        <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                            Notes du client
                        </h3>
                        <div class="p-4 bg-amber-50 rounded-xl border border-amber-100 text-amber-800">
                            {{ $foodOrder->notes }}
                        </div>
                    </div>
                @endif

                {{-- Historique / Timeline --}}
                <div class="glass-card rounded-2xl shadow-xl p-5 border border-white/50">
                    <h3 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Historique de la commande
                    </h3>
                    <div class="space-y-4">
                        <div class="timeline-item relative pl-12">
                            <div class="absolute left-0 top-0 w-10 h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center shadow-md">
                                <span class="text-white text-lg">📝</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Commande passée</p>
                                <p class="text-sm text-gray-500">{{ $foodOrder->created_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                        
                        @if($foodOrder->accepted_at)
                            <div class="timeline-item relative pl-12">
                                <div class="absolute left-0 top-0 w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center shadow-md">
                                    <span class="text-white text-lg">✓</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Commande acceptée</p>
                                    <p class="text-sm text-gray-500">{{ $foodOrder->accepted_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($foodOrder->preparing_at)
                            <div class="timeline-item relative pl-12">
                                <div class="absolute left-0 top-0 w-10 h-10 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center shadow-md">
                                    <span class="text-white text-lg">🔥</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Préparation démarrée</p>
                                    <p class="text-sm text-gray-500">{{ $foodOrder->preparing_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($foodOrder->ready_at)
                            <div class="timeline-item relative pl-12">
                                <div class="absolute left-0 top-0 w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-500 rounded-xl flex items-center justify-center shadow-md">
                                    <span class="text-white text-lg">✅</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Commande prête</p>
                                    <p class="text-sm text-gray-500">{{ $foodOrder->ready_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($foodOrder->delivered_at)
                            <div class="timeline-item relative pl-12">
                                <div class="absolute left-0 top-0 w-10 h-10 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-xl flex items-center justify-center shadow-md">
                                    <span class="text-white text-lg">📦</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $foodOrder->delivery_type === 'pickup' ? 'Récupérée par le client' : 'Commande livrée' }}</p>
                                    <p class="text-sm text-gray-500">{{ $foodOrder->delivered_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($foodOrder->status === 'rejected')
                            <div class="timeline-item relative pl-12">
                                <div class="absolute left-0 top-0 w-10 h-10 bg-gradient-to-br from-red-400 to-red-600 rounded-xl flex items-center justify-center shadow-md">
                                    <span class="text-white text-lg">🚫</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-red-600">Commande refusée</p>
                                    @if($foodOrder->rejection_reason)
                                        <p class="text-sm text-gray-600 mt-1">{{ $foodOrder->rejection_reason }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        @if($foodOrder->status === 'cancelled')
                            <div class="timeline-item relative pl-12">
                                <div class="absolute left-0 top-0 w-10 h-10 bg-gradient-to-br from-red-400 to-red-600 rounded-xl flex items-center justify-center shadow-md">
                                    <span class="text-white text-lg">❌</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-red-600">Commande annulée</p>
                                    @if(isset($foodOrder->cancellation_reason) && $foodOrder->cancellation_reason)
                                        <p class="text-sm text-gray-600 mt-1">{{ $foodOrder->cancellation_reason }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                
                {{-- Informations client --}}
                <div class="glass-card rounded-2xl shadow-xl p-5 border border-white/50">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Client
                    </h3>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-xl flex items-center justify-center">
                            @if($foodOrder->client && $foodOrder->client->profile_photo_url)
                                <img src="{{ $foodOrder->client->profile_photo_url }}" alt="{{ $foodOrder->client->name }}" class="w-14 h-14 rounded-xl object-cover">
                            @else
                                <span class="text-2xl font-bold text-blue-600">{{ strtoupper(substr($foodOrder->client->name ?? 'C', 0, 1)) }}</span>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">{{ $foodOrder->client->name ?? 'Client' }}</h4>
                            <p class="text-sm text-gray-500">{{ $foodOrder->client->email ?? '' }}</p>
                        </div>
                    </div>
                    
                    @if($foodOrder->client && $foodOrder->client->phone)
                        <a href="tel:{{ $foodOrder->client->phone }}" class="flex items-center gap-3 p-3 bg-green-50 rounded-xl text-green-700 hover:bg-green-100 transition-colors mb-3">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <span class="font-medium">{{ $foodOrder->client->phone }}</span>
                        </a>
                    @endif
                </div>

                {{-- Type de livraison --}}
                <div class="glass-card rounded-2xl shadow-xl p-5 border border-white/50">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        @if($foodOrder->delivery_type === 'pickup')
                            <svg class="w-5 h-5 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                            </svg>
                        @endif
                        {{ $foodOrder->delivery_type === 'pickup' ? 'À emporter' : 'Livraison' }}
                    </h3>
                    
                    @if($foodOrder->delivery_type === 'pickup')
                        <div class="p-4 bg-cyan-50 rounded-xl border border-cyan-100">
                            <p class="text-cyan-700 font-medium">Le client viendra récupérer sa commande sur place</p>
                            @if(isset($foodOrder->pickup_time) && $foodOrder->pickup_time)
                                <p class="text-sm text-cyan-600 mt-2">
                                    🕐 Heure souhaitée : <span class="font-semibold">{{ \Carbon\Carbon::parse($foodOrder->pickup_time)->format('H:i') }}</span>
                                </p>
                            @endif
                        </div>
                    @else
                        {{-- Section Livraison complète pour le livreur --}}
                        <div class="space-y-4">
                            {{-- Contact du destinataire --}}
                            <div class="p-4 bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl border border-green-200">
                                <h4 class="text-sm font-bold text-green-800 mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    Contact livraison
                                </h4>
                                @if($foodOrder->delivery_contact_name)
                                    <p class="text-green-900 font-semibold mb-1">{{ $foodOrder->delivery_contact_name }}</p>
                                @endif
                                @if($foodOrder->delivery_phone)
                                    <a href="tel:{{ $foodOrder->delivery_phone }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition-colors shadow-md">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ $foodOrder->delivery_phone }}
                                    </a>
                                @elseif($foodOrder->client && $foodOrder->client->phone)
                                    <a href="tel:{{ $foodOrder->client->phone }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition-colors shadow-md">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ $foodOrder->client->phone }}
                                    </a>
                                @else
                                    <p class="text-gray-500 italic text-sm">Pas de téléphone renseigné</p>
                                @endif
                            </div>
                            
                            {{-- Adresse complète --}}
                            <div class="p-4 bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl border border-indigo-200">
                                <h4 class="text-sm font-bold text-indigo-800 mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Adresse de livraison
                                </h4>
                                <p class="text-indigo-900 font-semibold text-base mb-3">{{ $foodOrder->delivery_address }}</p>
                                
                                {{-- Bouton GPS pour naviguer --}}
                                @if($foodOrder->delivery_lat && $foodOrder->delivery_lng)
                                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $foodOrder->delivery_lat }},{{ $foodOrder->delivery_lng }}" 
                                       target="_blank"
                                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition-colors shadow-md mb-3">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                        Ouvrir dans GPS
                                    </a>
                                @elseif($foodOrder->delivery_address)
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($foodOrder->delivery_address) }}" 
                                       target="_blank"
                                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition-colors shadow-md mb-3">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                        Ouvrir dans Maps
                                    </a>
                                @endif
                            </div>
                            
                            {{-- Détails de l'immeuble --}}
                            @if($foodOrder->delivery_floor || $foodOrder->delivery_door_code || $foodOrder->delivery_building_info)
                                <div class="p-4 bg-gradient-to-br from-amber-50 to-yellow-50 rounded-xl border border-amber-200">
                                    <h4 class="text-sm font-bold text-amber-800 mb-3 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Infos immeuble
                                    </h4>
                                    <div class="space-y-2">
                                        @if($foodOrder->delivery_floor)
                                            <div class="flex items-center gap-2 bg-white/70 px-3 py-2 rounded-lg">
                                                <span class="text-amber-600 font-medium">🏢 Étage:</span>
                                                <span class="text-amber-900 font-bold">{{ $foodOrder->delivery_floor }}</span>
                                            </div>
                                        @endif
                                        @if($foodOrder->delivery_door_code)
                                            <div class="flex items-center gap-2 bg-white/70 px-3 py-2 rounded-lg">
                                                <span class="text-amber-600 font-medium">🔑 Code:</span>
                                                <span class="text-amber-900 font-bold text-lg">{{ $foodOrder->delivery_door_code }}</span>
                                            </div>
                                        @endif
                                        @if($foodOrder->delivery_building_info)
                                            <div class="bg-white/70 px-3 py-2 rounded-lg">
                                                <span class="text-amber-600 font-medium block mb-1">📝 Instructions:</span>
                                                <p class="text-amber-900">{{ $foodOrder->delivery_building_info }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            
                            {{-- Carte de prévisualisation avec OpenStreetMap --}}
                            @if($foodOrder->delivery_lat && $foodOrder->delivery_lng)
                                <div class="rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                                    <div id="delivery_map" class="w-full h-32"></div>
                                </div>
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        if (typeof L !== 'undefined') {
                                            const map = L.map('delivery_map').setView([{{ $foodOrder->delivery_lat }}, {{ $foodOrder->delivery_lng }}], 16);
                                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                                attribution: '© OSM'
                                            }).addTo(map);
                                            L.marker([{{ $foodOrder->delivery_lat }}, {{ $foodOrder->delivery_lng }}]).addTo(map);
                                        }
                                    });
                                </script>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Livreur assigné / Assignation --}}
                @if($foodOrder->delivery_type === 'delivery')
                <div class="glass-card rounded-2xl shadow-xl p-5 border border-white/50" id="driverSection">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        Livreur
                        @if($foodOrder->driver_id)
                            <span class="ml-2 px-2.5 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded-full">Assigné</span>
                        @else
                            <span class="ml-2 px-2.5 py-0.5 bg-amber-100 text-amber-700 text-xs font-bold rounded-full">En attente</span>
                        @endif
                    </h3>

                    @if($foodOrder->driver)
                        {{-- Livreur assigné --}}
                        <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-xl p-4 border border-emerald-200">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center text-white text-2xl shadow-lg">
                                    {{ $foodOrder->driver->vehicle_icon ?? '🚗' }}
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900">{{ $foodOrder->driver->full_name }}</h4>
                                    <p class="text-sm text-gray-600">{{ ucfirst($foodOrder->driver->vehicle_type ?? 'Véhicule') }}</p>
                                    @if($foodOrder->driver->phone)
                                        <a href="tel:{{ $foodOrder->driver->phone }}" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">
                                            📞 {{ $foodOrder->driver->phone }}
                                        </a>
                                    @endif
                                </div>
                                <div class="text-right">
                                    @if($foodOrder->driver->rating)
                                        <div class="flex items-center gap-1 text-amber-500">
                                            ⭐ {{ number_format($foodOrder->driver->rating, 1) }}
                                        </div>
                                    @endif
                                    <span class="text-xs text-gray-500">{{ $foodOrder->driver->completed_deliveries ?? 0 }} livraisons</span>
                                </div>
                            </div>

                            {{-- Statut livraison --}}
                            <div class="mt-4 pt-4 border-t border-emerald-200">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Statut livraison:</span>
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold
                                        @if($foodOrder->delivery_status === 'delivered') bg-green-100 text-green-700
                                        @elseif($foodOrder->delivery_status === 'in_transit') bg-blue-100 text-blue-700
                                        @elseif($foodOrder->delivery_status === 'picked_up') bg-amber-100 text-amber-700
                                        @elseif($foodOrder->delivery_status === 'assigned') bg-indigo-100 text-indigo-700
                                        @else bg-gray-100 text-gray-700
                                        @endif">
                                        {{ $foodOrder->delivery_status_label ?? ucfirst($foodOrder->delivery_status ?? 'En attente') }}
                                    </span>
                                </div>
                                @if($foodOrder->estimated_delivery_time)
                                    <p class="text-sm text-gray-500 mt-2">
                                        ⏱️ Temps estimé: ~{{ $foodOrder->estimated_delivery_time }} min
                                    </p>
                                @endif
                                @if($foodOrder->delivery_distance)
                                    <p class="text-sm text-gray-500">
                                        📍 Distance: {{ $foodOrder->delivery_distance }} km
                                    </p>
                                @endif
                            </div>
                        </div>
                    @else
                        {{-- Pas de livreur - En attente d'un livreur --}}
                        <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl p-4 border border-amber-200">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-400 rounded-full flex items-center justify-center text-white text-xl animate-pulse">
                                    🔍
                                </div>
                                <div>
                                    <h4 class="font-bold text-amber-800">En attente d'un livreur</h4>
                                    <p class="text-sm text-amber-600">Les livreurs disponibles verront cette commande</p>
                                </div>
                            </div>
                            
                            @if(in_array($foodOrder->status, ['accepted', 'preparing', 'ready']))
                                <p class="text-xs text-amber-600 bg-white/60 rounded-lg p-2 mt-2">
                                    💡 Un livreur acceptera automatiquement cette commande quand elle sera prête, ou vous pouvez gérer la livraison vous-même.
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
                @endif

                {{-- Paiement --}}
                <div class="glass-card rounded-2xl shadow-xl p-5 border border-white/50">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        Paiement
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Montant</span>
                            <span class="text-2xl font-bold text-orange-600">{{ number_format($foodOrder->total, 2) }} €</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Méthode</span>
                            <span class="font-semibold text-gray-900">
                                @if(isset($foodOrder->payment_method) && $foodOrder->payment_method)
                                    @if($foodOrder->payment_method === 'card')
                                        💳 Carte bancaire
                                    @elseif($foodOrder->payment_method === 'cash')
                                        💵 Espèces
                                    @else
                                        {{ ucfirst($foodOrder->payment_method) }}
                                    @endif
                                @else
                                    Non défini
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Statut</span>
                            @if($foodOrder->payment_status === 'paid')
                                <span class="px-3 py-1.5 bg-green-100 text-green-700 text-sm font-bold rounded-full flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Payé
                                </span>
                            @elseif($foodOrder->payment_status === 'pending')
                                <span class="px-3 py-1.5 bg-amber-100 text-amber-700 text-sm font-bold rounded-full">⏳ En attente</span>
                            @else
                                <span class="px-3 py-1.5 bg-gray-100 text-gray-700 text-sm font-semibold rounded-full">{{ ucfirst($foodOrder->payment_status ?? 'Inconnu') }}</span>
                            @endif
                        </div>
                        
                        @if($foodOrder->paid_at)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">Payé le</span>
                                <span class="text-gray-600">{{ $foodOrder->paid_at->format('d/m/Y à H:i') }}</span>
                            </div>
                        @endif
                    </div>
                    
                    {{-- Bouton confirmation paiement espèces (uniquement si policy = cash) --}}
                    @if($foodOrder->payment_status !== 'paid' && !in_array($foodOrder->status, ['cancelled', 'rejected']))
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            @if($paymentPolicy['type'] === 'cash')
                                <p class="text-sm text-gray-500 mb-3">Le client n'a pas encore payé</p>
                                <form action="{{ route('prestataire.food-orders.confirm-cash', $foodOrder) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2">
                                        💵 Confirmer paiement espèces
                                    </button>
                                </form>
                            @else
                                {{-- Paiement en ligne requis --}}
                                <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl">
                                    <p class="text-sm text-blue-700">
                                        <i class="bi bi-credit-card me-1"></i>
                                        @if($paymentPolicy['type'] === 'full_prepay')
                                            <strong>Paiement en ligne requis</strong><br>
                                            Le client doit payer 100% en ligne avant récupération.
                                        @else
                                            <strong>Acompte en ligne requis</strong><br>
                                            Le client doit payer {{ $paymentPolicy['percent'] }}% en ligne.
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de rejet --}}
@if($foodOrder->status === 'pending')
    <div id="rejectModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="hideRejectModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Refuser la commande</h3>
                <form action="{{ route('prestataire.food-orders.reject', $foodOrder) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Raison du refus</label>
                        <textarea name="rejection_reason" rows="3" required
                            class="w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500"
                            placeholder="Ex: Produit en rupture de stock..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="hideRejectModal()" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all">
                            Annuler
                        </button>
                        <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all">
                            Confirmer le refus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
        }
        
        function hideRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
    </script>
    @endpush
@endif

{{-- Modal d'annulation (pour commandes acceptées, en préparation, prêtes) --}}
@if(in_array($foodOrder->status, ['accepted', 'preparing', 'ready']))
    <div id="cancelModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="hideCancelModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">⚠️ Annuler la commande</h3>
                <p class="text-gray-600 mb-4">
                    Êtes-vous sûr de vouloir annuler cette commande ?
                </p>

                @if(($prestataireCancelBreakdown['action'] ?? 'none') === 'cancel_authorization')
                    <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 p-3 text-sm">
                        <p class="font-semibold text-blue-800">Paiement non débité</p>
                        <p class="text-blue-700 mt-1">Le paiement est seulement autorisé. L’annulation stoppe le débit final client.</p>
                    </div>
                @elseif(($prestataireCancelBreakdown['action'] ?? 'none') === 'refund')
                    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm">
                        <p class="font-semibold text-amber-800">Résultat financier de l’annulation</p>
                        <p class="text-amber-700 mt-1">Montant payé client: {{ number_format((float) ($prestataireCancelBreakdown['amount_paid'] ?? 0), 2, ',', ' ') }} €</p>
                        <p class="text-amber-700">Client remboursé: {{ number_format((float) ($prestataireCancelBreakdown['client_refund_amount'] ?? 0), 2, ',', ' ') }} €</p>
                        <p class="font-semibold text-amber-900 mt-1">Frais Stripe débités sur votre compte: {{ number_format((float) ($prestataireCancelBreakdown['stripe_fee_amount'] ?? 0), 2, ',', ' ') }} €</p>
                    </div>
                @endif

                <form action="{{ route('prestataire.food-orders.cancel', $foodOrder) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Raison de l'annulation *</label>
                        <select name="reason" required class="w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500 mb-2">
                            <option value="">-- Sélectionner une raison --</option>
                            <option value="Rupture de stock">Rupture de stock</option>
                            <option value="Établissement fermé">Établissement fermé</option>
                            <option value="Problème technique en cuisine">Problème technique en cuisine</option>
                            <option value="Personnel insuffisant">Personnel insuffisant</option>
                            <option value="Autre">Autre (préciser ci-dessous)</option>
                        </select>
                        <textarea name="reason_details" rows="2"
                            class="w-full rounded-xl border-gray-300 focus:border-red-500 focus:ring-red-500"
                            placeholder="Détails supplémentaires (optionnel)..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="hideCancelModal()" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all">
                            Non, garder
                        </button>
                        <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all">
                            Oui, annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showCancelModal() {
            document.getElementById('cancelModal').classList.remove('hidden');
        }
        
        function hideCancelModal() {
            document.getElementById('cancelModal').classList.add('hidden');
        }
        
        // Combiner la raison sélectionnée avec les détails
        document.querySelector('#cancelModal form')?.addEventListener('submit', function(e) {
            const select = this.querySelector('select[name="reason"]');
            const details = this.querySelector('textarea[name="reason_details"]');
            if (select.value === 'Autre' && details.value.trim()) {
                select.value = details.value.trim();
            } else if (details.value.trim()) {
                select.value = select.value + ' - ' + details.value.trim();
            }
        });
    </script>
    @endpush
@endif
@endsection
