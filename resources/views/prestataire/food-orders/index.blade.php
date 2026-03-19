@extends('layouts.app')

@section('title', 'Commandes Alimentaires')

@push('styles')
<style>
    .order-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .order-card:hover {
        transform: translateY(-4px);
    }
    .status-badge {
        backdrop-filter: blur(8px);
    }
    .stat-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.7));
        backdrop-filter: blur(10px);
    }
    .glass-header {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 50%, #dc2626 100%);
    }
</style>
@endpush

@section('content')
{{-- Section d'aide --}}
<div class="px-4 pt-4">
    <x-help-section page="prestataire.food-orders.dashboard" />
</div>

<div class="min-h-screen bg-gradient-to-br from-orange-50 via-amber-50 to-yellow-50">
    
    {{-- Header Premium --}}
    <div class="glass-header text-white">
        <div class="container mx-auto px-4 py-6 sm:py-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold">Mes Commandes</h1>
                        <p class="text-white/80 text-sm sm:text-base">Historique et gestion complète</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <a href="{{ route('prestataire.food-orders.dashboard') }}" 
                       class="px-5 py-2.5 bg-white text-orange-600 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        </svg>
                        Cuisine Live
                    </a>
                    <a href="{{ route('prestataire.food-products.index') }}" 
                       class="px-5 py-2.5 bg-white/20 backdrop-blur-sm text-white rounded-xl font-semibold hover:bg-white/30 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        Menu
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistiques --}}
    <div class="container mx-auto px-4 -mt-4 relative z-10">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <div class="stat-card rounded-2xl p-4 shadow-xl border border-white/50">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">En attente</p>
                    </div>
                </div>
            </div>
            <div class="stat-card rounded-2xl p-4 shadow-xl border border-white/50">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['in_progress'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">En cours</p>
                    </div>
                </div>
            </div>
            <div class="stat-card rounded-2xl p-4 shadow-xl border border-white/50">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['today_completed'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Aujourd'hui</p>
                    </div>
                </div>
            </div>
            <div class="stat-card rounded-2xl p-4 shadow-xl border border-white/50">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['today_revenue'] ?? 0, 0) }}€</p>
                        <p class="text-xs text-gray-500">Recettes</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="container mx-auto px-4 py-6">
        <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-5 border border-gray-100">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Statut</label>
                    <select name="status" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                        <option value="">Tous les statuts</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ En attente</option>
                        <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>✓ Acceptées</option>
                        <option value="preparing" {{ request('status') == 'preparing' ? 'selected' : '' }}>🔥 En préparation</option>
                        <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>✅ Prêtes</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>📦 Livrées</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>🏁 Terminées</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>❌ Annulées</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wide">Période</label>
                    <select name="period" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                        <option value="">Toutes les périodes</option>
                        <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                        <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>Cette semaine</option>
                        <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Ce mois</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filtrer
                    </button>
                    @if(request()->hasAny(['status', 'period']))
                        <a href="{{ route('prestataire.food-orders.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Liste des commandes --}}
    <div class="container mx-auto px-4 pb-8">
        {{-- Guide pour le prestataire --}}
        <div class="mb-6 p-5 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <span class="text-2xl">📚</span>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-blue-900 mb-2">Guide de gestion des commandes</h4>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                        <div class="p-3 bg-white/70 rounded-xl">
                            <p class="font-semibold text-blue-800 mb-1">1️⃣ Nouvelle commande</p>
                            <p class="text-blue-600 text-xs">Vous recevez une notification. Acceptez ou refusez rapidement.</p>
                        </div>
                        <div class="p-3 bg-white/70 rounded-xl">
                            <p class="font-semibold text-purple-800 mb-1">2️⃣ Préparation</p>
                            <p class="text-purple-600 text-xs">Passez en "préparation" quand vous commencez à cuisiner.</p>
                        </div>
                        <div class="p-3 bg-white/70 rounded-xl">
                            <p class="font-semibold text-green-800 mb-1">3️⃣ Prête</p>
                            <p class="text-green-600 text-xs">Marquez "prête" - le client est notifié automatiquement.</p>
                        </div>
                        <div class="p-3 bg-white/70 rounded-xl">
                            <p class="font-semibold text-emerald-800 mb-1">4️⃣ Terminée</p>
                            <p class="text-emerald-600 text-xs">Confirmez la livraison/récupération pour finaliser.</p>
                        </div>
                    </div>
                    <p class="text-xs text-blue-500 mt-3 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Astuce : Plus vous êtes réactif, plus vous recevez de bonnes évaluations !
                    </p>
                </div>
            </div>
        </div>
        
        @if($orders->isEmpty())
            <div class="bg-white rounded-3xl shadow-xl p-12 text-center border border-gray-100">
                <div class="w-24 h-24 bg-gradient-to-br from-orange-100 to-amber-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Aucune commande</h3>
                <p class="text-gray-500 mb-6">Vous n'avez pas encore reçu de commandes alimentaires</p>
                <a href="{{ route('prestataire.food-products.index') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-orange-500 to-amber-500 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Gérer mon menu
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($orders as $order)
                    @php
                        $statusConfig = [
                            'pending' => ['gradient' => 'from-amber-400 to-orange-500', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'icon' => '⏳', 'label' => 'En attente'],
                            'accepted' => ['gradient' => 'from-blue-400 to-blue-600', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'icon' => '✓', 'label' => 'Acceptée'],
                            'preparing' => ['gradient' => 'from-purple-400 to-purple-600', 'bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'icon' => '🔥', 'label' => 'En préparation'],
                            'ready' => ['gradient' => 'from-green-400 to-emerald-500', 'bg' => 'bg-green-50', 'text' => 'text-green-700', 'icon' => '✅', 'label' => 'Prête'],
                            'delivered' => ['gradient' => 'from-emerald-400 to-teal-500', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'icon' => '📦', 'label' => 'Livrée'],
                            'completed' => ['gradient' => 'from-gray-400 to-gray-600', 'bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'icon' => '🏁', 'label' => 'Terminée'],
                            'cancelled' => ['gradient' => 'from-red-400 to-red-600', 'bg' => 'bg-red-50', 'text' => 'text-red-700', 'icon' => '❌', 'label' => 'Annulée'],
                            'rejected' => ['gradient' => 'from-red-400 to-red-600', 'bg' => 'bg-red-50', 'text' => 'text-red-700', 'icon' => '🚫', 'label' => 'Refusée'],
                        ];
                        $config = $statusConfig[$order->status] ?? $statusConfig['pending'];
                    @endphp

                    <div class="order-card bg-white rounded-2xl shadow-lg hover:shadow-2xl overflow-hidden border border-gray-100">
                        <div class="flex flex-col lg:flex-row">
                            {{-- Barre de statut --}}
                            <div class="lg:w-2 bg-gradient-to-b {{ $config['gradient'] }}"></div>
                            
                            {{-- Contenu principal --}}
                            <div class="flex-1 p-5">
                                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                                    <div>
                                        <div class="flex items-center gap-3 mb-1">
                                            <span class="text-xl font-bold text-gray-900">#{{ $order->order_number }}</span>
                                            <span class="status-badge px-3 py-1 text-xs font-bold rounded-full {{ $config['bg'] }} {{ $config['text'] }}">
                                                {{ $config['icon'] }} {{ $config['label'] }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm text-gray-500">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            {{ $order->client->name ?? 'Client' }}
                                            <span class="text-gray-300">•</span>
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $order->created_at->format('d/m H:i') }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold bg-gradient-to-r from-orange-500 to-amber-500 bg-clip-text text-transparent">
                                            {{ number_format($order->total, 2) }} €
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg {{ $order->delivery_type === 'pickup' ? 'bg-cyan-50 text-cyan-700' : 'bg-indigo-50 text-indigo-700' }}">
                                            @if($order->delivery_type === 'pickup')
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                </svg>
                                                À emporter
                                            @else
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                                </svg>
                                                Livraison
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                {{-- Articles --}}
                                <div class="flex flex-wrap gap-2 mb-3">
                                    @foreach($order->items->take(4) as $item)
                                        <span class="px-3 py-1.5 bg-gray-100 text-gray-700 text-sm rounded-lg font-medium">
                                            {{ $item->quantity }}× {{ Str::limit($item->product_name, 20) }}
                                        </span>
                                    @endforeach
                                    @if($order->items->count() > 4)
                                        <span class="px-3 py-1.5 bg-orange-100 text-orange-600 text-sm rounded-lg font-medium">
                                            +{{ $order->items->count() - 4 }} autres
                                        </span>
                                    @endif
                                </div>

                                @if($order->notes)
                                    <div class="flex items-start gap-2 p-3 bg-amber-50 rounded-xl text-sm text-amber-700 border border-amber-100">
                                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                        </svg>
                                        {{ Str::limit($order->notes, 100) }}
                                    </div>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex lg:flex-col items-stretch justify-end gap-2 p-4 bg-gray-50 border-t lg:border-t-0 lg:border-l border-gray-100 lg:w-48">
                                <a href="{{ route('prestataire.food-orders.show', $order) }}" 
                                   class="flex-1 lg:flex-none px-4 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Détails
                                </a>
                                
                                @if($order->status === 'pending')
                                    <form action="{{ route('prestataire.food-orders.accept', $order) }}" method="POST" class="flex-1 lg:flex-none">
                                        @csrf
                                        <button type="submit" class="w-full px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-500 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Accepter
                                        </button>
                                    </form>
                                @elseif($order->status === 'accepted')
                                    <form action="{{ route('prestataire.food-orders.start-preparing', $order) }}" method="POST" class="flex-1 lg:flex-none">
                                        @csrf
                                        <button type="submit" class="w-full px-4 py-2.5 bg-gradient-to-r from-purple-500 to-purple-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2">
                                            🔥 Préparer
                                        </button>
                                    </form>
                                @elseif($order->status === 'preparing')
                                    <form action="{{ route('prestataire.food-orders.ready', $order) }}" method="POST" class="flex-1 lg:flex-none">
                                        @csrf
                                        <button type="submit" class="w-full px-4 py-2.5 bg-gradient-to-r from-green-500 to-emerald-500 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2">
                                            ✅ Prêt
                                        </button>
                                    </form>
                                @elseif($order->status === 'ready')
                                    @if(($order->delivery_type ?? '') === 'pickup')
                                        <form action="{{ route('prestataire.food-orders.delivered', $order) }}" method="POST" class="flex-1 lg:flex-none">
                                            @csrf
                                            <button type="submit" class="w-full px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2">
                                                🤝 Récupérée
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" disabled class="w-full px-4 py-2.5 bg-gray-300 text-gray-500 text-sm font-semibold rounded-xl cursor-not-allowed flex items-center justify-center gap-2">
                                            🔐 Validation livreur (code)
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($orders->hasPages())
                <div class="mt-8">
                    {{ $orders->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
