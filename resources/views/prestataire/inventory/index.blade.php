@extends('layouts.app')

@section('title', 'Inventaire')

@push('styles')
<style>
    /* Force a compact mobile stats row even if utility classes are missing from built CSS */
    .inv-mobile-mini-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 4px;
    }
    .inv-mobile-mini-grid > div {
        padding: 2px !important;
        min-height: 56px;
        aspect-ratio: auto !important;
        border-radius: 8px !important;
    }
    .inv-mobile-mini-grid > div > div {
        gap: 2px !important;
    }
    .inv-mobile-mini-grid > div > div > div:first-child {
        padding: 2px !important;
        border-radius: 6px !important;
    }
    .inv-mobile-mini-grid svg {
        width: 12px !important;
        height: 12px !important;
    }
    .inv-mobile-mini-grid p:first-child {
        font-size: 14px !important;
        line-height: 1.05 !important;
    }
    .inv-mobile-mini-grid p:last-child {
        display: block !important;
        font-size: 9px !important;
        line-height: 1 !important;
        margin-top: 1px !important;
        white-space: nowrap !important;
    }

    @media (min-width: 640px) {
        .inv-mobile-mini-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }
        .inv-mobile-mini-grid > div {
            padding: 12px !important;
            min-height: auto;
        }
        .inv-mobile-mini-grid > div > div {
            gap: 8px !important;
        }
        .inv-mobile-mini-grid > div > div > div:first-child {
            padding: 8px !important;
        }
        .inv-mobile-mini-grid svg {
            width: 20px !important;
            height: 20px !important;
        }
        .inv-mobile-mini-grid p:first-child {
            font-size: 18px !important;
        }
        .inv-mobile-mini-grid p:last-child {
            display: block !important;
            font-size: 12px !important;
            margin-top: 4px !important;
        }
    }

    @media (min-width: 1024px) {
        .inv-mobile-mini-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-3 py-4">
        <div class="max-w-7xl mx-auto">
            
            {{-- Header & Actions --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-3">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <span class="text-2xl">📦</span> Inventaire
                    </h1>
                    <p class="text-sm text-gray-500">{{ $totalItems ?? 0 }} article(s) en stock</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('prestataire.inventory.create') }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nouvel Article
                    </a>
                    <a href="{{ route('prestataire.urgent-sales.create') }}" class="inline-flex items-center px-3 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600 transition shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Vente Flash
                    </a>
                </div>
            </div>

            {{-- Stats Cards (ultra compact mobile) --}}
            <div class="grid grid-cols-5 sm:grid-cols-3 lg:grid-cols-5 gap-1 sm:gap-3 mb-4 inv-mobile-mini-grid">
                <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-0.5 sm:p-3 aspect-square sm:aspect-auto">
                    <div class="h-full flex flex-col items-center sm:items-start justify-center gap-0.5 sm:gap-2 text-center sm:text-left">
                        <div class="p-0.5 sm:p-2 bg-blue-50 text-blue-600 rounded-md sm:rounded-lg">
                            <svg class="w-3 h-3 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs sm:text-lg font-bold text-gray-900 leading-none">{{ $totalItems ?? 0 }}</p>
                            <p class="text-xs text-gray-500 mt-1 leading-tight">
                                <span class="sm:hidden">Art.</span>
                                <span class="hidden sm:inline">Articles</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-0.5 sm:p-3 aspect-square sm:aspect-auto">
                    <div class="h-full flex flex-col items-center sm:items-start justify-center gap-0.5 sm:gap-2 text-center sm:text-left">
                        <div class="p-0.5 sm:p-2 bg-purple-50 text-purple-600 rounded-md sm:rounded-lg">
                            <svg class="w-3 h-3 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs sm:text-lg font-bold text-gray-900 leading-none">{{ number_format($totalCost ?? 0, 0) }}€</p>
                            <p class="text-xs text-gray-500 mt-1 leading-tight">
                                <span class="sm:hidden">Coût</span>
                                <span class="hidden sm:inline">Coût total</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-0.5 sm:p-3 aspect-square sm:aspect-auto">
                    <div class="h-full flex flex-col items-center sm:items-start justify-center gap-0.5 sm:gap-2 text-center sm:text-left">
                        <div class="p-0.5 sm:p-2 bg-green-50 text-green-600 rounded-md sm:rounded-lg">
                            <svg class="w-3 h-3 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs sm:text-lg font-bold text-gray-900 leading-none">{{ number_format($totalSellingValue ?? 0, 0) }}€</p>
                            <p class="text-xs text-gray-500 mt-1 leading-tight">
                                <span class="sm:hidden">Vente</span>
                                <span class="hidden sm:inline">Valeur vente</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-0.5 sm:p-3 aspect-square sm:aspect-auto">
                    <div class="h-full flex flex-col items-center sm:items-start justify-center gap-0.5 sm:gap-2 text-center sm:text-left">
                        <div class="p-0.5 sm:p-2 bg-emerald-50 text-emerald-600 rounded-md sm:rounded-lg">
                            <svg class="w-3 h-3 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs sm:text-lg font-bold text-emerald-600 leading-none">+{{ number_format($totalProfit ?? 0, 0) }}€</p>
                            <p class="text-xs text-gray-500 mt-1 leading-tight">
                                <span class="sm:hidden">Profit</span>
                                <span class="hidden sm:inline">Bénéfice est.</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-0.5 sm:p-3 aspect-square sm:aspect-auto">
                    <div class="h-full flex flex-col items-center sm:items-start justify-center gap-0.5 sm:gap-2 text-center sm:text-left">
                        <div class="p-0.5 sm:p-2 bg-amber-50 text-amber-600 rounded-md sm:rounded-lg">
                            <svg class="w-3 h-3 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs sm:text-lg font-bold text-gray-900 leading-none">{{ number_format($averageMargin ?? 0, 0) }}%</p>
                            <p class="text-xs text-gray-500 mt-1 leading-tight">
                                <span class="sm:hidden">Marge</span>
                                <span class="hidden sm:inline">Marge moy.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alertes Stock --}}
            @if(isset($lowStockItems) && $lowStockItems > 0)
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-4 flex items-center gap-3">
                <div class="p-2 bg-amber-100 rounded-full">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-amber-800">{{ $lowStockItems }} article(s) en stock faible</p>
                </div>
                <button onclick="filterLowStock()" class="px-3 py-1.5 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition text-xs font-medium">
                    Voir les articles
                </button>
            </div>
            @endif

            {{-- Filtres Compacts (Keep this part from the redesign) --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 mb-4">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex-1 min-w-[200px]">
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Rechercher un article..." 
                                   class="w-full pl-9 pr-3 py-2 text-sm bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-indigo-500 transition">
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                    <select id="categoryFilter" class="px-3 py-2 text-sm bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                        <option value="">Toutes catégories</option>
                        <option value="equipment">Équipement</option>
                        <option value="material">Matériel</option>
                        <option value="consumable">Consommable</option>
                        <option value="accessory">Accessoire</option>
                        <option value="other">Autre</option>
                    </select>
                    <select id="stockFilter" class="px-3 py-2 text-sm bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                        <option value="">Tout stock</option>
                        <option value="available">En stock</option>
                        <option value="low">Stock faible</option>
                        <option value="out">Rupture</option>
                    </select>
                    <form action="{{ route('prestataire.inventory.export') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition flex items-center gap-1.5 text-sm" title="Exporter CSV">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="hidden sm:inline">Export</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Liste des Articles --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                @if(isset($items) && $items->count() > 0)
                    
                    {{-- Vue Mobile (Cards) --}}
                    <div class="block sm:hidden divide-y divide-gray-100">
                        @foreach($items as $item)
                            @php
                                $costPrice = $item->cost_per_unit ?? 0;
                                $sellingPrice = $item->selling_price ?? 0;
                                $margin = $costPrice > 0 ? (($sellingPrice - $costPrice) / $costPrice) * 100 : 0;
                                
                                // Récupérer données de réservation
                                $reservedQty = 0;
                                $soldQty = 0;
                                if ($item->urgentSales && $item->urgentSales->count() > 0) {
                                    foreach ($item->urgentSales as $urgentSale) {
                                        $reservedQty += $urgentSale->reserved_quantity ?? 0;
                                        $soldQty += $urgentSale->sold_quantity ?? 0;
                                    }
                                } elseif ($item->urgentSale) {
                                    $reservedQty = $item->urgentSale->reserved_quantity ?? 0;
                                    $soldQty = $item->urgentSale->sold_quantity ?? 0;
                                }
                                
                                $availableStock = $item->quantity - $reservedQty - $soldQty;
                                $stockStatus = $availableStock <= 0 ? 'out' : ($availableStock <= ($item->reorder_level ?? 5) ? 'low' : 'ok');
                                $reservationAlert = ($reservedQty > 0 && $reservedQty >= ($item->quantity * 0.7));
                                $firstPhotoUrl = $item->getFirstPhotoUrl();
                                $hasPhoto = count($item->getPhotoUrls()) > 0;
                            @endphp
                            <a href="{{ route('prestataire.inventory.show', $item) }}" class="block p-3 item-row hover:bg-gray-50 transition {{ $reservationAlert ? 'bg-amber-50' : '' }}" data-name="{{ strtolower($item->name) }}" data-category="{{ $item->category }}" data-stock="{{ $stockStatus }}">
                                <div class="flex gap-3">
                                    {{-- Image --}}
                                    @if($hasPhoto)
                                        <img src="{{ $firstPhotoUrl }}" alt="{{ $item->name }}" 
                                             class="w-16 h-16 rounded-lg object-cover border border-gray-200 shrink-0"
                                             onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%23f3f4f6%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 font-size=%2210%22 fill=%22%239ca3af%22%3ENo image%3C/text%3E%3C/svg%3E';">
                                    @else
                                        <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                    @endif
                                    {{-- Infos --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-900 text-sm truncate">{{ $item->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $item->sku ?? 'N/A' }} @if($item->urgent_sale_id)⚡@endif</p>
                                            </div>
                                            <span class="font-bold text-sm text-gray-900 whitespace-nowrap">{{ number_format($sellingPrice, 0) }}€</span>
                                        </div>
                                        <div class="flex items-center justify-between mt-2">
                                            <div class="flex flex-wrap items-center gap-1">
                                                <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $stockStatus === 'out' ? 'bg-red-100 text-red-700' : ($stockStatus === 'low' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">
                                                    📦 {{ $availableStock }}
                                                </span>
                                                @if($reservedQty > 0)
                                                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">🔒 {{ $reservedQty }}</span>
                                                @endif
                                                @if($soldQty > 0)
                                                    <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">✓ {{ $soldQty }}</span>
                                                @endif
                                                @if($reservationAlert)
                                                    <span class="text-xs text-amber-600">⚠️</span>
                                                @endif
                                            </div>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    {{-- Vue Desktop (Table Restored) --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold">
                                    <th class="px-4 py-3">Article</th>
                                    <th class="px-4 py-3 hidden md:table-cell">Catégorie</th>
                                    <th class="px-4 py-3 text-center" title="Stock disponible (Total - Réservé - Vendu)">Dispo</th>
                                    <th class="px-4 py-3 text-center hidden lg:table-cell" title="Stock total">Total</th>
                                    <th class="px-4 py-3 text-center">Réservé</th>
                                    <th class="px-4 py-3 text-center">Vendu</th>
                                    <th class="px-4 py-3 text-right hidden lg:table-cell">Achat</th>
                                    <th class="px-4 py-3 text-right">Vente</th>
                                    <th class="px-4 py-3 text-right hidden lg:table-cell">Marge</th>
                                    <th class="px-4 py-3 text-right hidden xl:table-cell">Profit</th>
                                    <th class="px-4 py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($items as $item)
                                    @php
                                        $costPrice = $item->cost_per_unit ?? 0;
                                        $sellingPrice = $item->selling_price ?? 0;
                                        $margin = $costPrice > 0 ? (($sellingPrice - $costPrice) / $costPrice) * 100 : 0;
                                        $totalItemProfit = ($sellingPrice - $costPrice) * $item->quantity;
                                        
                                        // Récupérer données de réservation depuis urgent_sales liés
                                        $reservedQty = 0;
                                        $soldQty = 0;
                                        if ($item->urgentSales && $item->urgentSales->count() > 0) {
                                            foreach ($item->urgentSales as $urgentSale) {
                                                $reservedQty += $urgentSale->reserved_quantity ?? 0;
                                                $soldQty += $urgentSale->sold_quantity ?? 0;
                                            }
                                        } elseif ($item->urgentSale) {
                                            $reservedQty = $item->urgentSale->reserved_quantity ?? 0;
                                            $soldQty = $item->urgentSale->sold_quantity ?? 0;
                                        }
                                        
                                        $availableStock = $item->quantity - $reservedQty - $soldQty;
                                        $reorderLevel = $item->reorder_level ?? 5;
                                        $stockStatus = $availableStock <= 0 ? 'out' : ($availableStock <= $reorderLevel ? 'low' : 'ok');
                                        
                                        // Alerte si réservations approchent du stock
                                        $reservationAlert = ($reservedQty > 0 && $reservedQty >= ($item->quantity * 0.7));
                                        
                                        $firstPhotoUrl = $item->getFirstPhotoUrl();
                                        $hasPhoto = count($item->getPhotoUrls()) > 0;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors item-row {{ $reservationAlert ? 'bg-amber-50' : '' }}" 
                                        data-name="{{ strtolower($item->name) }}" 
                                        data-category="{{ $item->category }}"
                                        data-stock="{{ $stockStatus }}">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                @if($hasPhoto)
                                                    <img src="{{ $firstPhotoUrl }}" alt="{{ $item->name }}" 
                                                         class="w-10 h-10 rounded-lg object-cover border border-gray-200"
                                                         onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%23f3f4f6%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 font-size=%2210%22 fill=%22%239ca3af%22%3ENo image%3C/text%3E%3C/svg%3E';">
                                                @else
                                                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <a href="{{ route('prestataire.inventory.show', $item) }}" class="font-medium text-gray-900 text-sm truncate max-w-[200px] hover:text-indigo-600 block">{{ $item->name }}</a>
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-xs text-gray-500">{{ $item->sku ?? 'N/A' }}</span>
                                                        @if($item->urgent_sale_id)
                                                            <span class="text-xs text-orange-500 font-bold">⚡ Flash</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 hidden md:table-cell">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                {{ ucfirst($item->category ?? 'Autre') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex flex-col items-center">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $stockStatus === 'out' ? 'bg-red-100 text-red-700' : '' }}
                                                    {{ $stockStatus === 'low' ? 'bg-amber-100 text-amber-700' : '' }}
                                                    {{ $stockStatus === 'ok' ? 'bg-green-100 text-green-700' : '' }}">
                                                    @if($stockStatus === 'out')
                                                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                                    @elseif($stockStatus === 'low')
                                                        <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                                                    @else
                                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                                    @endif
                                                    {{ $availableStock }}
                                                </span>
                                                @if($reservationAlert)
                                                    <span class="text-xs text-amber-600 mt-1" title="Seuil atteint">⚠️ Seuil</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center hidden lg:table-cell">
                                            <span class="text-gray-600 text-sm font-medium">{{ $item->quantity }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($reservedQty > 0)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                                    🔒 {{ $reservedQty }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($soldQty > 0)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                                    ✓ {{ $soldQty }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right hidden lg:table-cell">
                                            <span class="text-gray-500 text-sm">{{ number_format($costPrice, 0) }}€</span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="font-bold text-gray-900 text-sm">{{ number_format($sellingPrice, 0) }}€</span>
                                        </td>
                                        <td class="px-4 py-3 text-right hidden lg:table-cell">
                                            <span class="text-sm font-medium {{ $margin >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $margin >= 0 ? '+' : '' }}{{ number_format($margin, 0) }}%
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right hidden xl:table-cell">
                                            <span class="text-sm font-medium {{ $totalItemProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $totalItemProfit >= 0 ? '+' : '' }}{{ number_format($totalItemProfit, 0) }}€
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center gap-1">
                                                <button onclick="openStockModal({{ $item->id }}, {{ @js($item->name) }}, {{ $item->quantity }})" 
                                                        class="p-1.5 text-gray-500 hover:text-green-600 hover:bg-green-50 rounded-lg transition" title="Ajuster stock">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                                    </svg>
                                                </button>
                                                <a href="{{ route('prestataire.inventory.edit', $item) }}" 
                                                   class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Modifier">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                                <a href="{{ route('prestataire.urgent-sales.from-inventory', $item->id) }}" 
                                                   class="p-1.5 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition" title="Vente Flash">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                    </svg>
                                                </a>
                                                <form action="{{ route('prestataire.inventory.destroy', $item) }}" method="POST" class="inline"
                                                      onsubmit="return confirm('Supprimer cet article ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Supprimer">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($items->hasPages())
                        <div class="p-3 border-t border-gray-100 bg-gray-50">
                            {{ $items->links() }}
                        </div>
                    @endif
                @else
                    {{-- État vide --}}
                    <div class="bg-white rounded-2xl border border-slate-200/60 p-8 sm:p-12 text-center">
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-800 mb-2">Inventaire vide</h3>
                        <p class="text-slate-500 text-sm mb-5 max-w-xs mx-auto">Ajoutez vos articles pour suivre votre stock et calculer vos marges.</p>
                        <div class="flex flex-wrap items-center justify-center gap-2">
                            <a href="{{ route('prestataire.inventory.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-lg text-sm font-medium hover:from-indigo-700 hover:to-indigo-800 transition shadow-lg shadow-indigo-200/50">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Ajouter un article
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal Ajustement Stock --}}
<div id="stockModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4 transition-opacity">
    <div class="bg-white rounded-xl shadow-xl max-w-sm w-full overflow-hidden transform transition-all">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Ajuster le stock</h3>
                <p id="stockItemName" class="text-sm text-gray-500 truncate max-w-[200px]"></p>
            </div>
            <button type="button" onclick="closeStockModal()" class="text-gray-400 hover:text-gray-500 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="stockForm" method="POST" class="p-5">
            @csrf
            
            <div class="flex items-center justify-between mb-6 bg-blue-50 px-4 py-3 rounded-lg border border-blue-100">
                <span class="text-sm font-medium text-blue-700">Stock actuel</span>
                <span class="text-2xl font-bold text-blue-700" id="currentStock">0</span>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4">
                <button type="button" onclick="setStockAction('add')" id="btnAdd"
                        class="flex items-center justify-center gap-2 py-2.5 px-4 border rounded-lg text-sm font-medium transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Ajouter
                </button>
                <button type="button" onclick="setStockAction('remove')" id="btnRemove"
                        class="flex items-center justify-center gap-2 py-2.5 px-4 border rounded-lg text-sm font-medium transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    Retirer
                </button>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantité</label>
                <input type="number" name="quantity" id="stockQuantity" min="1" value="1" required
                       class="w-full px-4 py-2.5 text-lg font-semibold text-center border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Raison (optionnel)</label>
                <input type="text" name="reason" placeholder="Ex: Inventaire, Perte, Retour..."
                       class="w-full px-4 py-2 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
            </div>

            <input type="hidden" name="action" id="stockAction" value="add">

            <div class="flex gap-3">
                <button type="button" onclick="closeStockModal()" 
                        class="flex-1 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition shadow-sm">
                    Annuler
                </button>
                <button type="submit" id="stockSubmitBtn"
                        class="flex-1 px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
                    Confirmer
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Filtres
    document.getElementById('searchInput').addEventListener('input', filterItems);
    document.getElementById('categoryFilter').addEventListener('change', filterItems);
    document.getElementById('stockFilter').addEventListener('change', filterItems);

    function filterItems() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const category = document.getElementById('categoryFilter').value;
        const stock = document.getElementById('stockFilter').value;
        
        document.querySelectorAll('.item-row').forEach(row => {
            const name = row.dataset.name;
            const rowCategory = row.dataset.category;
            const rowStock = row.dataset.stock;
            
            let show = true;
            if (search && !name.includes(search)) show = false;
            if (category && rowCategory !== category) show = false;
            if (stock) {
                if (stock === 'available' && rowStock !== 'ok') show = false;
                if (stock === 'low' && rowStock !== 'low') show = false;
                if (stock === 'out' && rowStock !== 'out') show = false;
            }
            
            row.style.display = show ? '' : 'none';
        });
    }

    function filterLowStock() {
        document.getElementById('stockFilter').value = 'low';
        filterItems();
    }

    // Modal Stock
    function openStockModal(id, name, quantity) {
        document.getElementById('stockItemName').textContent = name;
        document.getElementById('currentStock').textContent = quantity;
        document.getElementById('stockForm').action = `/prestataire/inventory/${id}/adjust-stock`;
        document.getElementById('stockModal').classList.remove('hidden');
        document.getElementById('stockModal').classList.add('flex');
        setStockAction('add');
    }
    
    function closeStockModal() {
        document.getElementById('stockModal').classList.add('hidden');
        document.getElementById('stockModal').classList.remove('flex');
    }
    
    function setStockAction(action) {
        document.getElementById('stockAction').value = action;
        const btnAdd = document.getElementById('btnAdd');
        const btnRemove = document.getElementById('btnRemove');
        
        if (action === 'add') {
            btnAdd.className = 'flex items-center justify-center gap-2 py-2.5 px-4 border rounded-lg text-sm font-medium transition-all border-green-200 bg-green-50 text-green-700 ring-1 ring-green-500/20';
            btnRemove.className = 'flex items-center justify-center gap-2 py-2.5 px-4 border rounded-lg text-sm font-medium transition-all border-gray-200 text-gray-600 hover:bg-gray-50';
        } else {
            btnRemove.className = 'flex items-center justify-center gap-2 py-2.5 px-4 border rounded-lg text-sm font-medium transition-all border-red-200 bg-red-50 text-red-700 ring-1 ring-red-500/20';
            btnAdd.className = 'flex items-center justify-center gap-2 py-2.5 px-4 border rounded-lg text-sm font-medium transition-all border-gray-200 text-gray-600 hover:bg-gray-50';
        }
    }
    
    // Fermer modal avec Escape ou clic extérieur
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeStockModal(); });
    document.getElementById('stockModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeStockModal(); });
    
    // Fix pour mobile - soumettre le formulaire avec JavaScript
    document.getElementById('stockForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const submitBtn = document.getElementById('stockSubmitBtn');
        
        // Désactiver le bouton pour éviter double soumission
        submitBtn.disabled = true;
        submitBtn.textContent = 'Chargement...';
        
        // Soumettre le formulaire
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || 'Une erreur est survenue');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Confirmer';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Fallback: soumettre normalement si fetch échoue
            form.submit();
        });
    });
</script>
@endpush

@endsection
