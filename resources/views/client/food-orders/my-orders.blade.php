@extends('layouts.app')

@section('title', 'Mes Commandes')

@push('styles')
<style>
    .order-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .order-card:hover {
        transform: translateY(-4px);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 via-amber-50 to-yellow-50">
    
    {{-- Header --}}
    <div class="bg-gradient-to-r from-orange-500 via-amber-500 to-yellow-500 text-white">
        <div class="container mx-auto px-4 py-8">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center text-2xl shadow-lg">
                    🛒
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold">Mes Commandes</h1>
                    <p class="text-white/80">Historique de vos commandes alimentaires</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-6">
        @if($hasAnyOrders || $hasActiveFilters)
            <div class="max-w-6xl mx-auto mb-6">
                <details class="bg-white/90 backdrop-blur-sm border border-orange-100 rounded-3xl shadow-lg overflow-hidden" @if($hasActiveFilters) open @endif>
                    <summary class="list-none cursor-pointer px-4 py-4 sm:px-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                <span class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1.5 font-semibold text-orange-700">
                                    {{ $orders->total() }} commande{{ $orders->total() > 1 ? 's' : '' }}
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 font-semibold text-gray-700 border border-gray-200">
                                    Filtres
                                </span>
                                @if($hasActiveFilters)
                                    <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 font-semibold text-amber-700">
                                        {{ $activeFiltersCount }} filtre{{ $activeFiltersCount > 1 ? 's' : '' }} actif{{ $activeFiltersCount > 1 ? 's' : '' }}
                                    </span>
                                @endif
                            </div>

                            <span class="inline-flex items-center gap-2 text-sm font-semibold text-orange-600">
                                {{ $hasActiveFilters ? 'Afficher / masquer les filtres' : 'Ouvrir les filtres' }}
                            </span>
                        </div>
                    </summary>

                    <form method="GET" action="{{ route('food.orders') }}" class="border-t border-orange-100 p-4 sm:p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3">
                            <div class="xl:col-span-2">
                                <label for="orders-search" class="block text-sm font-semibold text-gray-700 mb-1">Recherche</label>
                                <input
                                    id="orders-search"
                                    type="text"
                                    name="search"
                                    value="{{ $filters['search'] }}"
                                    placeholder="Numéro, restaurant, produit..."
                                    class="w-full rounded-2xl border border-orange-100 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-orange-400 focus:ring focus:ring-orange-100"
                                >
                            </div>

                            <div>
                                <label for="orders-status" class="block text-sm font-semibold text-gray-700 mb-1">Statut</label>
                                <select
                                    id="orders-status"
                                    name="status"
                                    class="w-full rounded-2xl border border-orange-100 bg-white px-4 py-3 text-sm text-gray-900 focus:border-orange-400 focus:ring focus:ring-orange-100"
                                >
                                    <option value="">Tous les statuts</option>
                                    @foreach($statusOptions as $statusValue => $statusLabel)
                                        <option value="{{ $statusValue }}" @selected($filters['status'] === $statusValue)>{{ $statusLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="orders-delivery-type" class="block text-sm font-semibold text-gray-700 mb-1">Type</label>
                                <select
                                    id="orders-delivery-type"
                                    name="delivery_type"
                                    class="w-full rounded-2xl border border-orange-100 bg-white px-4 py-3 text-sm text-gray-900 focus:border-orange-400 focus:ring focus:ring-orange-100"
                                >
                                    <option value="">Tous les types</option>
                                    @foreach($deliveryTypeOptions as $deliveryTypeValue => $deliveryTypeLabel)
                                        <option value="{{ $deliveryTypeValue }}" @selected($filters['delivery_type'] === $deliveryTypeValue)>{{ $deliveryTypeLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="orders-date-from" class="block text-sm font-semibold text-gray-700 mb-1">Du</label>
                                <input
                                    id="orders-date-from"
                                    type="date"
                                    name="date_from"
                                    value="{{ $filters['date_from'] }}"
                                    class="w-full rounded-2xl border border-orange-100 bg-white px-4 py-3 text-sm text-gray-900 focus:border-orange-400 focus:ring focus:ring-orange-100"
                                >
                            </div>

                            <div>
                                <label for="orders-date-to" class="block text-sm font-semibold text-gray-700 mb-1">Au</label>
                                <input
                                    id="orders-date-to"
                                    type="date"
                                    name="date_to"
                                    value="{{ $filters['date_to'] }}"
                                    class="w-full rounded-2xl border border-orange-100 bg-white px-4 py-3 text-sm text-gray-900 focus:border-orange-400 focus:ring focus:ring-orange-100"
                                >
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2 justify-end">
                            @if($hasActiveFilters)
                                <a
                                    href="{{ route('food.orders') }}"
                                    class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50"
                                >
                                    Réinitialiser
                                </a>
                            @endif
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-orange-500 to-amber-500 px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:shadow-xl"
                            >
                                Filtrer
                            </button>
                        </div>
                    </form>
                </details>
            </div>
        @endif

        @if(!$hasAnyOrders)
            {{-- État vide --}}
            <div class="max-w-md mx-auto text-center py-16">
                <div class="w-32 h-32 bg-gradient-to-br from-orange-100 to-amber-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-xl">
                    <span class="text-6xl">🍽️</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Aucune commande</h2>
                <p class="text-gray-500 mb-8">Vous n'avez pas encore passé de commande. Découvrez nos cuisiniers locaux !</p>
                <a href="{{ route('food.explore') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transition transform hover:-translate-y-1">
                    <span class="text-xl">🔍</span>
                    Découvrir les cuisiniers
                </a>
            </div>
        @elseif($orders->isEmpty())
            <div class="max-w-2xl mx-auto text-center py-14">
                <div class="w-24 h-24 bg-gradient-to-br from-amber-100 to-orange-100 rounded-full flex items-center justify-center mx-auto mb-5 shadow-lg">
                    <span class="text-4xl">🧾</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Aucune commande trouvée</h2>
                <p class="text-gray-500 mb-6">Aucune commande ne correspond aux filtres sélectionnés.</p>
                <a href="{{ route('food.orders') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-orange-200 text-orange-600 font-bold rounded-2xl shadow-sm hover:bg-orange-50 transition">
                    Effacer les filtres
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-6xl mx-auto">
                @foreach($orders as $order)
                    @php
                        $statusConfig = [
                            'pending' => ['gradient' => 'from-amber-400 to-orange-500', 'bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'icon' => '⏳'],
                            'accepted' => ['gradient' => 'from-blue-400 to-blue-600', 'bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => '✓'],
                            'scheduled' => ['gradient' => 'from-sky-400 to-cyan-500', 'bg' => 'bg-sky-100', 'text' => 'text-sky-700', 'icon' => '📅'],
                            'preparing' => ['gradient' => 'from-purple-400 to-purple-600', 'bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'icon' => '🔥'],
                            'ready' => ['gradient' => 'from-green-400 to-emerald-500', 'bg' => 'bg-green-100', 'text' => 'text-green-700', 'icon' => '✅'],
                            'delivered' => ['gradient' => 'from-emerald-400 to-teal-500', 'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'icon' => '📦'],
                            'completed' => ['gradient' => 'from-gray-400 to-gray-600', 'bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'icon' => '🏁'],
                            'cancelled' => ['gradient' => 'from-red-400 to-red-600', 'bg' => 'bg-red-100', 'text' => 'text-red-700', 'icon' => '❌'],
                        ];
                        $config = $statusConfig[$order->status] ?? $statusConfig['pending'];
                    @endphp

                    <div class="order-card bg-white rounded-2xl shadow-lg hover:shadow-2xl overflow-hidden border border-gray-100">
                        <div class="flex">
                            {{-- Barre de couleur --}}
                            <div class="w-1.5 bg-gradient-to-b {{ $config['gradient'] }}"></div>
                            
                            <div class="flex-1 p-4">
                                {{-- En-tête compact --}}
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <div>
                                        <h3 class="font-bold text-base text-gray-900">{{ $order->prestataire->company_name ?? $order->prestataire->business_name }}</h3>
                                        <p class="text-gray-500 text-xs flex items-center gap-1">
                                            <span class="font-medium text-orange-600">#{{ $order->order_number }}</span>
                                            <span class="text-gray-300">•</span>
                                            {{ $order->created_at->format('d/m H:i') }}
                                        </p>
                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-700">
                                                {{ $order->delivery_type_label }}
                                            </span>
                                            @if($order->requested_at)
                                                <span>
                                                    Prévue le {{ $order->requested_at->format('d/m/Y \à H:i') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-lg font-bold bg-gradient-to-r from-orange-500 to-amber-500 bg-clip-text text-transparent">
                                            {{ number_format($order->total, 2) }}€
                                        </span>
                                        <div class="mt-1">
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $config['bg'] }} {{ $config['text'] }}">
                                                {{ $config['icon'] }} {{ $order->status_label }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Articles compact --}}
                                <div class="flex flex-wrap gap-1 mb-3">
                                    @foreach($order->items->take(3) as $item)
                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-lg font-medium">
                                            {{ $item->quantity }}× {{ Str::limit($item->product_name, 15) }}
                                        </span>
                                    @endforeach
                                    @if($order->items->count() > 3)
                                        <span class="px-2 py-1 bg-orange-100 text-orange-600 text-xs rounded-lg font-medium">
                                            +{{ $order->items->count() - 3 }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Actions compact --}}
                                <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-gray-100">
                                    <a href="{{ route('food.orders.show', $order) }}" 
                                       class="px-3 py-1.5 bg-gradient-to-r from-orange-500 to-amber-500 text-white font-semibold rounded-lg shadow text-xs flex items-center gap-1">
                                        👁️ Détails
                                    </a>
                                    @if($order->isReady())
                                        <a href="{{ route('food.orders.track', $order) }}" 
                                           class="px-3 py-1.5 bg-green-100 hover:bg-green-200 text-green-600 font-semibold rounded-lg text-xs flex items-center gap-1">
                                            📍 Suivre
                                        </a>
                                    @endif
                                    
                                    @if($order->isDelivered() && !$order->client_confirmed)
                                        <form action="{{ route('food.orders.confirm', $order) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-green-500 to-emerald-500 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition">
                                                ✓ Confirmer réception
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                {{-- Alerte commande prête --}}
                                @if($order->isReady())
                                    <div class="mt-4 p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200 flex items-center gap-3">
                                        <span class="text-2xl">🎉</span>
                                        <p class="text-green-700 font-medium">
                                            Votre commande est prête {{ $order->delivery_type === 'pickup' ? 'à récupérer' : 'et en cours de livraison' }} !
                                        </p>
                                    </div>
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
