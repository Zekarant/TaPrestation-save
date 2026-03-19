@extends('layouts.admin-modern')

@section('title', 'Analyse des revenus')

@section('content')
<div class="page-header">
    <h1 class="page-title">💰 Analyse des revenus</h1>
    <p class="page-subtitle">Suivez les performances financières</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="card-base text-center">
        <div class="text-3xl font-bold text-green-600">{{ number_format($totalRevenue ?? 0, 2) }} €</div>
        <div class="text-sm text-gray-500">Revenu total</div>
    </div>
    <div class="card-base text-center">
        <div class="text-3xl font-bold text-blue-600">{{ number_format($monthlyRevenue ?? 0, 2) }} €</div>
        <div class="text-sm text-gray-500">Ce mois</div>
    </div>
    <div class="card-base text-center">
        <div class="text-3xl font-bold text-purple-600">{{ number_format($averageOrder ?? 0, 2) }} €</div>
        <div class="text-sm text-gray-500">Panier moyen</div>
    </div>
    <div class="card-base text-center">
        <div class="text-3xl font-bold text-orange-600">{{ $totalTransactions ?? 0 }}</div>
        <div class="text-sm text-gray-500">Transactions</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card-base">
        <h3 class="font-semibold mb-4">Revenus des 6 derniers mois</h3>
        <div class="space-y-3">
            @forelse($monthlyData ?? [] as $month)
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">{{ $month['label'] }}</span>
                    <div class="flex items-center gap-4">
                        <div class="w-32 bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $month['percentage'] ?? 0 }}%"></div>
                        </div>
                        <span class="font-semibold w-24 text-right">{{ number_format($month['amount'] ?? 0, 2) }} €</span>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">Aucune donnée disponible</p>
            @endforelse
        </div>
    </div>
    
    <div class="card-base">
        <h3 class="font-semibold mb-4">Répartition par source</h3>
        <div class="space-y-3">
            @forelse($revenueSources ?? [] as $source)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full bg-{{ $source['color'] ?? 'gray' }}-500"></div>
                        <span>{{ $source['name'] }}</span>
                    </div>
                    <span class="font-semibold">{{ number_format($source['amount'] ?? 0, 2) }} €</span>
                </div>
            @empty
                <div class="p-3 bg-gray-50 rounded-lg flex justify-between">
                    <span>Abonnements</span>
                    <span class="font-semibold">{{ number_format($subscriptionRevenue ?? 0, 2) }} €</span>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg flex justify-between">
                    <span>Commissions</span>
                    <span class="font-semibold">{{ number_format($commissionRevenue ?? 0, 2) }} €</span>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
