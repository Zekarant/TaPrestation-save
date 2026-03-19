@extends('layouts.app')

@section('title', 'Analytics Inventaire')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        {{-- En-tête --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">📊 Analytics Inventaire</h1>
                <p class="text-gray-600 mt-1">Statistiques et analyses de votre stock</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('prestataire.inventory.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour à l'inventaire
                </a>
            </div>
        </div>

        @if(isset($tableNotExists) && $tableNotExists)
            <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg">
                ⚠️ La table d'inventaire n'est pas encore configurée.
            </div>
        @endif

        @if(isset($error))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                {{ $error }}
            </div>
        @endif

        {{-- Statistiques principales --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total articles</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_items'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Valeur de vente</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($stats['selling_value'] ?? 0, 2) }} €</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Bénéfice potentiel</p>
                        <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['profit'] ?? 0, 2) }} €</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-indigo-100 rounded-full">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Marge moyenne</p>
                        <p class="text-2xl font-bold text-indigo-600">{{ $stats['margin'] ?? 0 }}%</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alertes stock --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-yellow-200 rounded-full mr-3">
                        <svg class="w-5 h-5 text-yellow-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-yellow-800">Stock faible</h3>
                </div>
                <p class="text-3xl font-bold text-yellow-700">{{ $stats['low_stock_count'] ?? 0 }}</p>
                <p class="text-sm text-yellow-600 mt-1">articles à réapprovisionner</p>
            </div>

            <div class="bg-red-50 border border-red-200 rounded-xl p-6">
                <div class="flex items-center mb-4">
                    <div class="p-2 bg-red-200 rounded-full mr-3">
                        <svg class="w-5 h-5 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-red-800">Rupture de stock</h3>
                </div>
                <p class="text-3xl font-bold text-red-700">{{ $stats['out_of_stock_count'] ?? 0 }}</p>
                <p class="text-sm text-red-600 mt-1">articles en rupture</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Répartition par catégorie --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📦 Répartition par catégorie</h3>
                @if(isset($stats['items_by_category']) && $stats['items_by_category']->count() > 0)
                    <div class="space-y-3">
                        @foreach($stats['items_by_category'] as $category)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <span class="font-medium text-gray-900">{{ ucfirst($category->category ?? 'Non catégorisé') }}</span>
                                    <span class="text-sm text-gray-500 ml-2">({{ $category->count }} articles)</span>
                                </div>
                                <span class="font-semibold text-indigo-600">{{ number_format($category->value ?? 0, 2) }} €</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">Aucune donnée disponible</p>
                @endif
            </div>

            {{-- Articles en stock faible --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">⚠️ Articles à réapprovisionner</h3>
                @if(isset($stats['low_stock_items']) && $stats['low_stock_items']->count() > 0)
                    <div class="space-y-2">
                        @foreach($stats['low_stock_items'] as $item)
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                <div>
                                    <span class="font-medium text-gray-900">{{ $item->name }}</span>
                                    <span class="text-sm text-gray-500 block">SKU: {{ $item->sku ?? 'N/A' }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-semibold {{ $item->quantity <= 0 ? 'text-red-600' : 'text-yellow-600' }}">
                                        {{ $item->quantity }} {{ $item->unit ?? 'unités' }}
                                    </span>
                                    <span class="text-xs text-gray-500 block">min: {{ $item->reorder_level ?? 5 }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-green-600 text-center py-4">✅ Tous vos stocks sont OK !</p>
                @endif
            </div>
        </div>

        {{-- Articles récents --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🕐 Articles récemment ajoutés</h3>
            @if(isset($stats['recent_items']) && $stats['recent_items']->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Article</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantité</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Prix</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ajouté le</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($stats['recent_items'] as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $item->name }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ ucfirst($item->category ?? 'N/A') }}</td>
                                    <td class="px-4 py-3 text-gray-900">{{ $item->quantity }} {{ $item->unit ?? '' }}</td>
                                    <td class="px-4 py-3 text-gray-900">{{ number_format($item->selling_price ?? 0, 2) }} €</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $item->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Aucun article récent</p>
            @endif
        </div>
    </div>
</div>
@endsection
