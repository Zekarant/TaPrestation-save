@extends('layouts.admin-modern')

@section('title', 'Analytics Paiements')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <span class="w-12 h-12 bg-gradient-to-r from-green-600 to-emerald-600 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-chart-line text-white text-xl"></i>
                        </span>
                        Analytics Paiements
                    </h1>
                    <p class="mt-2 text-gray-600">Statistiques et tendances des revenus</p>
                </div>
                <a href="{{ route('admin.payments.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Retour aux paiements
                </a>
            </div>
        </div>

        @if(isset($tableNotExists) && $tableNotExists)
            {{-- Table non existante --}}
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center">
                <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-database text-amber-500 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Données non disponibles</h3>
                <p class="text-gray-600">La table des transactions de paiement n'existe pas encore ou n'a pas de données.</p>
            </div>
        @else
            {{-- Graphique des revenus mensuels --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
                <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white flex items-center">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Revenus mensuels (12 derniers mois)
                    </h2>
                </div>
                <div class="p-6">
                    @if($monthlyRevenue->count() > 0)
                        <div class="h-80">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-chart-bar text-gray-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Aucune donnée</h3>
                            <p class="text-gray-500 mt-1">Les revenus apparaîtront ici dès qu'il y aura des paiements.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Tableau des revenus --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white flex items-center">
                        <i class="fas fa-table mr-2"></i>
                        Détail par mois
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    @if($monthlyRevenue->count() > 0)
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Période</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenus</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Évolution</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @php
                                    $previousTotal = null;
                                    $months = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                                @endphp
                                @foreach($monthlyRevenue as $revenue)
                                    @php
                                        $evolution = null;
                                        if ($previousTotal !== null && $previousTotal > 0) {
                                            $evolution = (($revenue->total - $previousTotal) / $previousTotal) * 100;
                                        }
                                        $previousTotal = $revenue->total;
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                                                    <i class="fas fa-calendar text-indigo-600"></i>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $months[$revenue->month] ?? $revenue->month }} {{ $revenue->year }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <span class="text-lg font-semibold text-gray-900">{{ number_format($revenue->total, 2, ',', ' ') }} €</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            @if($evolution !== null)
                                                @if($evolution > 0)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-arrow-up mr-1"></i>
                                                        +{{ number_format($evolution, 1) }}%
                                                    </span>
                                                @elseif($evolution < 0)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <i class="fas fa-arrow-down mr-1"></i>
                                                        {{ number_format($evolution, 1) }}%
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        <i class="fas fa-minus mr-1"></i>
                                                        0%
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">Total</td>
                                    <td class="px-6 py-4 text-right text-lg font-bold text-green-600">{{ number_format($monthlyRevenue->sum('total'), 2, ',', ' ') }} €</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        <div class="p-12 text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Aucune donnée</h3>
                            <p class="text-gray-500 mt-1">Les statistiques apparaîtront ici après les premiers paiements.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@if(isset($monthlyRevenue) && $monthlyRevenue->count() > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    const data = @json($monthlyRevenue->reverse()->values());
    const months = ['', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(item => months[item.month] + ' ' + item.year),
            datasets: [{
                label: 'Revenus (€)',
                data: data.map(item => item.total),
                backgroundColor: 'rgba(16, 185, 129, 0.7)',
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 1,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(value);
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endif
@endsection
