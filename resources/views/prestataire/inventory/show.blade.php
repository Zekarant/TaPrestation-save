@extends('layouts.app')

@section('title', $item->name . ' - Détail Article')

@push('styles')
<style>
    .inv-show-mini-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 4px;
    }
    .inv-show-mini-grid > div {
        padding: 2px !important;
        min-height: 58px;
        aspect-ratio: auto !important;
        border-radius: 8px !important;
    }
    .inv-show-mini-grid > div > div {
        gap: 2px !important;
    }
    .inv-show-mini-grid > div > div > div:first-child {
        padding: 2px !important;
        border-radius: 6px !important;
    }
    .inv-show-mini-grid svg {
        width: 12px !important;
        height: 12px !important;
    }
    .inv-show-mini-grid p:first-child {
        font-size: 14px !important;
        line-height: 1.05 !important;
    }
    .inv-show-mini-grid p:nth-child(2) {
        display: block !important;
        font-size: 9px !important;
        line-height: 1 !important;
        margin-top: 1px !important;
        white-space: nowrap !important;
    }

    @media (min-width: 640px) {
        .inv-show-mini-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }
        .inv-show-mini-grid > div {
            padding: 16px !important;
            min-height: auto;
        }
        .inv-show-mini-grid > div > div {
            gap: 12px !important;
        }
        .inv-show-mini-grid > div > div > div:first-child {
            padding: 8px !important;
        }
        .inv-show-mini-grid svg {
            width: 20px !important;
            height: 20px !important;
        }
        .inv-show-mini-grid p:first-child {
            font-size: 24px !important;
        }
        .inv-show-mini-grid p:nth-child(2) {
            display: block !important;
            font-size: 12px !important;
            margin-top: 4px !important;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-4 sm:py-6">
    <div class="container mx-auto px-3 sm:px-4">
        <div class="max-w-5xl mx-auto">
            
            {{-- En-tête avec navigation --}}
            <div class="mb-4 sm:mb-6">
                <a href="{{ route('prestataire.inventory.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 text-sm mb-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Retour à l'inventaire
                </a>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <span class="text-2xl">📦</span>
                            {{ $item->name }}
                        </h1>
                        <p class="text-gray-500 text-sm mt-1">
                            {{ $item->sku ?? 'Sans référence' }} • {{ ucfirst($item->category ?? 'Autre') }}
                            @if($item->urgent_sale_id)
                                <span class="text-orange-500 font-bold ml-2">⚡ Vente Flash Active</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('prestataire.inventory.edit', $item) }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Modifier
                        </a>
                        @if(!$item->urgent_sale_id)
                            <a href="{{ route('prestataire.urgent-sales.from-inventory', $item->id) }}" 
                               class="inline-flex items-center px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600 transition shadow-sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Vente Flash
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Légende rapide --}}
            <p class="mb-4 text-xs text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg px-3 py-2">
                💡 📦 Stock Total − 🔒 Réservé − ✅ Vendu = 📊 Disponible. Survolez les cartes pour plus d'infos.
            </p>

            {{-- Statistiques Stock & Réservations --}}
            <div class="grid grid-cols-4 sm:grid-cols-4 gap-1 sm:gap-3 mb-6 inv-show-mini-grid">
                <div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-0.5 sm:p-4 border border-gray-100 cursor-help group relative aspect-square sm:aspect-auto" title="Quantité totale en inventaire">
                    <div class="h-full flex flex-col items-center justify-center sm:flex-row sm:justify-start sm:items-center gap-0.5 sm:gap-3 text-center sm:text-left">
                        <div class="p-0.5 sm:p-2 bg-blue-50 text-blue-600 rounded-md sm:rounded-lg">
                            <svg class="w-3 h-3 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm sm:text-2xl font-bold text-gray-900 leading-none">{{ $stats['total_stock'] }}</p>
                            <p class="text-xs text-gray-500 mt-1 leading-tight">
                                <span class="sm:hidden">Total</span>
                                <span class="hidden sm:inline">Stock Total 📦</span>
                            </p>
                        </div>
                    </div>
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                        Quantité totale dans votre inventaire
                    </div>
                </div>

                <div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-0.5 sm:p-4 border border-gray-100 cursor-help group relative aspect-square sm:aspect-auto" title="Articles réservés par des clients">
                    <div class="h-full flex flex-col items-center justify-center sm:flex-row sm:justify-start sm:items-center gap-0.5 sm:gap-3 text-center sm:text-left">
                        <div class="p-0.5 sm:p-2 bg-amber-50 text-amber-600 rounded-md sm:rounded-lg">
                            <svg class="w-3 h-3 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm sm:text-2xl font-bold text-amber-600 leading-none">{{ $stats['reserved'] }}</p>
                            <p class="text-xs text-gray-500 mt-1 leading-tight">
                                <span class="sm:hidden">Réserv.</span>
                                <span class="hidden sm:inline">Réservé 🔒</span>
                            </p>
                        </div>
                    </div>
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                        Bloqué par des réservations clients
                    </div>
                </div>

                <div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-0.5 sm:p-4 border border-gray-100 cursor-help group relative aspect-square sm:aspect-auto" title="Ventes finalisées">
                    <div class="h-full flex flex-col items-center justify-center sm:flex-row sm:justify-start sm:items-center gap-0.5 sm:gap-3 text-center sm:text-left">
                        <div class="p-0.5 sm:p-2 bg-emerald-50 text-emerald-600 rounded-md sm:rounded-lg">
                            <svg class="w-3 h-3 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm sm:text-2xl font-bold text-emerald-600 leading-none">{{ $stats['sold'] }}</p>
                            <p class="text-xs text-gray-500 mt-1 leading-tight">
                                <span class="sm:hidden">Vendu</span>
                                <span class="hidden sm:inline">Vendu ✅</span>
                            </p>
                        </div>
                    </div>
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                        Réservations finalisées et payées
                    </div>
                </div>

                <div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-0.5 sm:p-4 border border-gray-100 {{ $stats['available'] <= ($item->reorder_level ?? 5) ? 'border-red-300 bg-red-50' : '' }} cursor-help group relative aspect-square sm:aspect-auto" title="Stock disponible à la vente">
                    <div class="h-full flex flex-col items-center justify-center sm:flex-row sm:justify-start sm:items-center gap-0.5 sm:gap-3 text-center sm:text-left">
                        <div class="p-0.5 sm:p-2 {{ $stats['available'] <= ($item->reorder_level ?? 5) ? 'bg-red-100 text-red-600' : 'bg-green-50 text-green-600' }} rounded-md sm:rounded-lg">
                            <svg class="w-3 h-3 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm sm:text-2xl font-bold {{ $stats['available'] <= ($item->reorder_level ?? 5) ? 'text-red-600' : 'text-green-600' }} leading-none">{{ $stats['available'] }}</p>
                            <p class="text-xs text-gray-500 mt-1 leading-tight">
                                <span class="sm:hidden">Dispo</span>
                                <span class="hidden sm:inline">Disponible 📊</span>
                            </p>
                        </div>
                    </div>
                    @if($stats['available'] <= ($item->reorder_level ?? 5))
                        <p class="hidden sm:block text-xs text-red-600 mt-2 text-center sm:text-left">⚠️ Stock faible</p>
                    @endif
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                        = Total - Réservé - Vendu
                    </div>
                </div>
            </div>

            {{-- Alerte seuil --}}
            @if($stats['reserved'] > 0 && $stats['reserved'] >= ($stats['total_stock'] * 0.7))
                <div class="bg-amber-50 border border-amber-300 rounded-xl p-4 mb-6 flex items-start gap-3">
                    <div class="p-2 bg-amber-100 rounded-full flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-amber-800">⚠️ Seuil d'alerte atteint</p>
                        <p class="text-sm text-amber-700 mt-1">
                            {{ number_format(($stats['reserved'] / $stats['total_stock']) * 100, 0) }}% du stock est réservé. 
                            Pensez à réapprovisionner ou à gérer vos réservations.
                        </p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Colonne principale --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Photos --}}
                    @php
                        $photos = $item->getPhotoUrls();
                    @endphp
                    @if(count($photos) > 0)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-100">
                                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Photos ({{ count($photos) }})
                                </h3>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    @foreach($photos as $photo)
                                        <div class="relative aspect-square rounded-lg overflow-hidden group">
                                            <img src="{{ $photo }}" alt="{{ $item->name }}" 
                                                 class="w-full h-full object-cover cursor-pointer hover:scale-105 transition-transform"
                                                 onclick="openImageModal('{{ $photo }}')">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Informations détaillées --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Informations
                            </h3>
                        </div>
                        <div class="p-4">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-xs text-gray-500 uppercase tracking-wide">Nom</dt>
                                    <dd class="text-sm font-medium text-gray-900 mt-1">{{ $item->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500 uppercase tracking-wide">SKU / Référence</dt>
                                    <dd class="text-sm font-medium text-gray-900 mt-1">{{ $item->sku ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500 uppercase tracking-wide">Catégorie</dt>
                                    <dd class="text-sm font-medium text-gray-900 mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                            {{ ucfirst($item->category ?? 'Autre') }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500 uppercase tracking-wide">Seuil de réapprovisionnement</dt>
                                    <dd class="text-sm font-medium text-gray-900 mt-1">{{ $item->reorder_level ?? 5 }} unités</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-xs text-gray-500 uppercase tracking-wide">Description</dt>
                                    <dd class="text-sm text-gray-700 mt-1">{{ $item->description ?: 'Aucune description' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- Prix et rentabilité --}}
                    @php
                        $costPrice = $item->cost_per_unit ?? 0;
                        $sellingPrice = $item->selling_price ?? 0;
                        $margin = $costPrice > 0 ? (($sellingPrice - $costPrice) / $costPrice) * 100 : 0;
                        $profitPerUnit = $sellingPrice - $costPrice;
                        $totalPotentialProfit = $profitPerUnit * $stats['available'];
                    @endphp
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-green-600 px-4 py-3">
                            <h3 class="font-semibold text-white flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Prix & Rentabilité
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <div class="text-center p-3 bg-gray-50 rounded-lg">
                                    <p class="text-xs text-gray-500 uppercase">Prix d'achat</p>
                                    <p class="text-xl font-bold text-gray-700 mt-1">{{ number_format($costPrice, 2) }}€</p>
                                </div>
                                <div class="text-center p-3 bg-gray-50 rounded-lg">
                                    <p class="text-xs text-gray-500 uppercase">Prix de vente</p>
                                    <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($sellingPrice, 2) }}€</p>
                                </div>
                                <div class="text-center p-3 bg-gray-50 rounded-lg">
                                    <p class="text-xs text-gray-500 uppercase">Marge</p>
                                    <p class="text-xl font-bold {{ $margin >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                                        {{ $margin >= 0 ? '+' : '' }}{{ number_format($margin, 0) }}%
                                    </p>
                                </div>
                                <div class="text-center p-3 bg-gray-50 rounded-lg">
                                    <p class="text-xs text-gray-500 uppercase">Profit/unité</p>
                                    <p class="text-xl font-bold {{ $profitPerUnit >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                                        {{ $profitPerUnit >= 0 ? '+' : '' }}{{ number_format($profitPerUnit, 2) }}€
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                                <p class="text-sm text-emerald-700">
                                    <strong>Profit potentiel sur stock disponible:</strong> 
                                    <span class="text-lg font-bold">{{ number_format($totalPotentialProfit, 2) }}€</span>
                                    <span class="text-xs">({{ $stats['available'] }} unités × {{ number_format($profitPerUnit, 2) }}€)</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Réservations en cours --}}
                    @if($pendingReservations->count() > 0 || $confirmedReservations->count() > 0)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="bg-amber-500 px-4 py-3">
                                <h3 class="font-semibold text-white flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Réservations actives ({{ $pendingReservations->count() + $confirmedReservations->count() }})
                                </h3>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @foreach($pendingReservations as $reservation)
                                    <div class="p-4 hover:bg-gray-50 transition">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 bg-amber-100 rounded-full">
                                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ $reservation->client->name ?? 'Client' }}</p>
                                                    <p class="text-xs text-gray-500">{{ $reservation->created_at->format('d/m/Y H:i') }}</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                                    En attente
                                                </span>
                                                <p class="text-sm font-bold text-gray-900 mt-1">Qté: {{ $reservation->quantity }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @foreach($confirmedReservations as $reservation)
                                    <div class="p-4 hover:bg-gray-50 transition">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 bg-green-100 rounded-full">
                                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ $reservation->client->name ?? 'Client' }}</p>
                                                    <p class="text-xs text-gray-500">Confirmée le {{ $reservation->confirmed_at?->format('d/m/Y H:i') ?? $reservation->updated_at->format('d/m/Y H:i') }}</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                    Confirmée
                                                </span>
                                                <p class="text-sm font-bold text-gray-900 mt-1">Qté: {{ $reservation->quantity }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    
                    {{-- Actions rapides --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-indigo-600 px-4 py-3">
                            <h3 class="font-semibold text-white flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Actions rapides
                            </h3>
                        </div>
                        <div class="p-4 space-y-3">
                            <a href="{{ route('prestataire.inventory.edit', $item) }}" 
                               class="flex items-center gap-3 p-3 bg-gray-50 hover:bg-indigo-50 rounded-lg transition group">
                                <div class="p-2 bg-indigo-100 text-indigo-600 rounded-lg group-hover:bg-indigo-600 group-hover:text-white transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-600">Modifier l'article</span>
                            </a>

                            <button onclick="openStockModal()" 
                                    class="w-full flex items-center gap-3 p-3 bg-gray-50 hover:bg-green-50 rounded-lg transition group">
                                <div class="p-2 bg-green-100 text-green-600 rounded-lg group-hover:bg-green-600 group-hover:text-white transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-green-600">Ajuster le stock</span>
                            </button>

                            @if(!$item->urgent_sale_id)
                                <a href="{{ route('prestataire.urgent-sales.from-inventory', $item->id) }}" 
                                   class="flex items-center gap-3 p-3 bg-gray-50 hover:bg-orange-50 rounded-lg transition group">
                                    <div class="p-2 bg-orange-100 text-orange-600 rounded-lg group-hover:bg-orange-600 group-hover:text-white transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-orange-600">Créer une vente flash</span>
                                </a>
                            @endif

                            <form action="{{ route('prestataire.inventory.destroy', $item) }}" method="POST" 
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet article ? Cette action est irréversible.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full flex items-center gap-3 p-3 bg-gray-50 hover:bg-red-50 rounded-lg transition group">
                                    <div class="p-2 bg-red-100 text-red-600 rounded-lg group-hover:bg-red-600 group-hover:text-white transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-red-600">Supprimer</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Ventes Flash liées --}}
                    @if(($item->urgentSales && $item->urgentSales->count() > 0) || $item->urgentSale)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="bg-orange-500 px-4 py-3">
                                <h3 class="font-semibold text-white flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    Ventes Flash
                                </h3>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @if($item->urgentSales && $item->urgentSales->count() > 0)
                                    @foreach($item->urgentSales as $sale)
                                        <a href="{{ route('prestataire.urgent-sales.show', $sale) }}" 
                                           class="block p-4 hover:bg-gray-50 transition">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="font-medium text-gray-900 text-sm">{{ $sale->title }}</p>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        {{ $sale->price }}€ • 
                                                        @if($sale->end_date && $sale->end_date->isPast())
                                                            <span class="text-red-500">Terminée</span>
                                                        @else
                                                            <span class="text-green-500">Active</span>
                                                        @endif
                                                    </p>
                                                </div>
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>
                                        </a>
                                    @endforeach
                                @elseif($item->urgentSale)
                                    <a href="{{ route('prestataire.urgent-sales.show', $item->urgentSale) }}" 
                                       class="block p-4 hover:bg-gray-50 transition">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900 text-sm">{{ $item->urgentSale->title }}</p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    {{ $item->urgentSale->price }}€ • 
                                                    @if($item->urgentSale->end_date && $item->urgentSale->end_date->isPast())
                                                        <span class="text-red-500">Terminée</span>
                                                    @else
                                                        <span class="text-green-500">Active</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </div>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Historique des ventes --}}
                    @if($completedReservations->count() > 0)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="bg-emerald-600 px-4 py-3">
                                <h3 class="font-semibold text-white flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Ventes terminées ({{ $completedReservations->count() }})
                                </h3>
                            </div>
                            <div class="max-h-64 overflow-y-auto divide-y divide-gray-100">
                                @foreach($completedReservations->take(10) as $reservation)
                                    <div class="p-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $reservation->client->name ?? 'Client' }}</p>
                                                <p class="text-xs text-gray-500">{{ $reservation->completed_at?->format('d/m/Y') ?? $reservation->updated_at->format('d/m/Y') }}</p>
                                            </div>
                                            <span class="text-sm font-bold text-emerald-600">{{ $reservation->quantity }} unité(s)</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Dates --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Dates
                            </h3>
                        </div>
                        <div class="p-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Créé le</span>
                                <span class="font-medium text-gray-900">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Modifié le</span>
                                <span class="font-medium text-gray-900">{{ $item->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal ajustement stock --}}
<div id="stockModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeStockModal()"></div>
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('prestataire.inventory.adjust-stock', $item) }}" method="POST">
                @csrf
                <div class="px-6 pt-5 pb-4 bg-white">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ajuster le stock</h3>
                    <p class="text-sm text-gray-600 mb-4">Stock actuel: <strong>{{ $item->quantity }}</strong> unités</p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type d'ajustement</label>
                            <select name="adjustment_type" id="adjustmentType" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="add">➕ Ajouter au stock</option>
                                <option value="remove">➖ Retirer du stock</option>
                                <option value="set">🔄 Définir nouveau stock</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantité</label>
                            <input type="number" name="quantity" min="1" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                   placeholder="Entrez la quantité">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Raison (optionnel)</label>
                            <input type="text" name="reason" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                   placeholder="Ex: Réapprovisionnement, Vente directe...">
                        </div>
                    </div>
                </div>
                <div class="px-6 py-3 bg-gray-50 flex justify-end gap-3">
                    <button type="button" onclick="closeStockModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                        Confirmer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal image --}}
<div id="imageModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-90 items-center justify-center" onclick="closeImageModal()">
    <button class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300" onclick="closeImageModal()">&times;</button>
    <img id="modalImage" src="" alt="Image en grand" class="max-w-full max-h-[90vh] object-contain">
</div>

@endsection

@push('scripts')
<script>
    
    function openStockModal() {
        document.getElementById('stockModal').classList.remove('hidden');
    }
    
    function closeStockModal() {
        document.getElementById('stockModal').classList.add('hidden');
    }
    
    function openImageModal(src) {
        document.getElementById('modalImage').src = src;
        const modal = document.getElementById('imageModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    
    // Fermer les modals avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeStockModal();
            closeImageModal();
        }
    });
</script>
@endpush
