@extends('layouts.admin-modern')

@section('title', 'Tableau de bord financier')
@section('page-title', 'Finance')

@section('content')
<div class="page-header">
    <h1 class="page-title">💰 Tableau de bord financier</h1>
    <p class="page-subtitle">Vue d'ensemble des finances de la plateforme</p>
</div>

<!-- Financial KPIs -->
<div class="stats-grid mb-8">
    <div class="card-base stat-card success">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Revenu total</p>
                <p class="text-value text-green-600">{{ number_format($stats['total_revenue'] ?? 0, 2) }} €</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-euro-sign text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-base stat-card primary">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Aujourd'hui</p>
                <p class="text-value text-blue-600">{{ number_format($stats['revenue_today'] ?? 0, 2) }} €</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-day text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-base stat-card info">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Ce mois</p>
                <p class="text-value text-cyan-600">{{ number_format($stats['revenue_month'] ?? 0, 2) }} €</p>
            </div>
            <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-alt text-cyan-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-base stat-card warning">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Commissions</p>
                <p class="text-value text-yellow-600">{{ number_format($stats['total_commissions'] ?? 0, 2) }} €</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-percent text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="card-base">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-orange-600 text-2xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-600">Retraits en attente</p>
                <p class="text-2xl font-bold text-orange-600">{{ number_format($stats['pending_withdrawals'] ?? 0, 2) }} €</p>
            </div>
        </div>
    </div>

    <div class="card-base">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-undo text-red-600 text-2xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-600">Remboursements en attente</p>
                <p class="text-2xl font-bold text-red-600">{{ number_format($stats['pending_refunds'] ?? 0, 2) }} €</p>
            </div>
        </div>
    </div>

    <div class="card-base">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-wallet text-purple-600 text-2xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-600">Solde prestataires</p>
                <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['total_prestataires_balance'] ?? 0, 2) }} €</p>
            </div>
        </div>
    </div>
    
    <div class="card-base">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-indigo-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-shield-alt text-indigo-600 text-2xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-600">Escrow en cours</p>
                <p class="text-2xl font-bold text-indigo-600">{{ number_format($stats['escrow_held'] ?? 0, 2) }} €</p>
            </div>
        </div>
    </div>
</div>

<!-- Escrow Section -->
<div class="card-base mb-8">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-900">🔒 Paiements sécurisés (Escrow)</h3>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-yellow-700">{{ $escrowStats['held_count'] ?? 0 }}</p>
            <p class="text-sm text-yellow-600">En attente confirmation</p>
            <p class="text-lg font-semibold text-yellow-800">{{ number_format($escrowStats['held_amount'] ?? 0, 2) }} €</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-blue-700">{{ $escrowStats['partial_count'] ?? 0 }}</p>
            <p class="text-sm text-blue-600">Confirmation partielle</p>
            <p class="text-lg font-semibold text-blue-800">{{ number_format($escrowStats['partial_amount'] ?? 0, 2) }} €</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-green-700">{{ $escrowStats['released_count'] ?? 0 }}</p>
            <p class="text-sm text-green-600">Libérés</p>
            <p class="text-lg font-semibold text-green-800">{{ number_format($escrowStats['released_amount'] ?? 0, 2) }} €</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-red-700">{{ $escrowStats['pending_transfer_count'] ?? 0 }}</p>
            <p class="text-sm text-red-600">⚠️ Transfers en attente</p>
            <p class="text-lg font-semibold text-red-800">{{ number_format($escrowStats['pending_transfer_amount'] ?? 0, 2) }} €</p>
        </div>
    </div>
    
    @if(($escrowStats['pending_transfer_count'] ?? 0) > 0)
    <div class="bg-red-100 border-l-4 border-red-500 p-4 mb-4">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-red-600 mr-3"></i>
            <div>
                <p class="font-semibold text-red-800">Attention : {{ $escrowStats['pending_transfer_count'] }} transfers Stripe en attente</p>
                <p class="text-sm text-red-700">Ces escrows ont été confirmés mais le transfer Stripe n'a pas été effectué (fonds insuffisants sur le compte plateforme).</p>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Liste des escrows récents -->
    @if(isset($recentEscrows) && count($recentEscrows) > 0)
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b bg-gray-50">
                    <th class="text-left py-3 px-4">ID</th>
                    <th class="text-left py-3 px-4">Type</th>
                    <th class="text-left py-3 px-4">Client</th>
                    <th class="text-left py-3 px-4">Prestataire</th>
                    <th class="text-left py-3 px-4">Montant</th>
                    <th class="text-left py-3 px-4">Prestataire €</th>
                    <th class="text-left py-3 px-4">Statut</th>
                    <th class="text-left py-3 px-4">Transfer</th>
                    <th class="text-left py-3 px-4">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentEscrows as $escrow)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4 font-mono text-sm">#{{ $escrow->id }}</td>
                    <td class="py-3 px-4 text-xs">
                        @php
                            $type = str_replace('App\\Models\\', '', $escrow->escrowable_type ?? '');
                        @endphp
                        <span class="px-2 py-1 bg-gray-100 rounded">{{ $type }}</span>
                    </td>
                    <td class="py-3 px-4">{{ $escrow->client_name ?? 'N/A' }}</td>
                    <td class="py-3 px-4">{{ $escrow->prestataire_name ?? 'N/A' }}</td>
                    <td class="py-3 px-4 font-semibold">{{ number_format($escrow->total_amount ?? 0, 2) }} €</td>
                    <td class="py-3 px-4 text-green-600 font-semibold">{{ number_format($escrow->prestataire_amount ?? 0, 2) }} €</td>
                    <td class="py-3 px-4">
                        @php
                            $statusColors = [
                                'pending' => 'yellow',
                                'held' => 'yellow',
                                'partial' => 'blue',
                                'released' => 'green',
                                'refunded' => 'red',
                            ];
                            $color = $statusColors[$escrow->status ?? ''] ?? 'gray';
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                            {{ ucfirst($escrow->status ?? 'N/A') }}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        @if($escrow->stripe_transfer_id)
                            <span class="text-green-600 text-xs">✅ {{ substr($escrow->stripe_transfer_id, 0, 15) }}...</span>
                        @elseif(in_array($escrow->status, ['partial', 'released']))
                            <span class="text-red-600 text-xs">❌ Manquant</span>
                        @else
                            <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-600">
                        {{ \Carbon\Carbon::parse($escrow->created_at)->format('d/m/Y H:i') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p class="text-center text-gray-500 py-4">Aucun escrow récent</p>
    @endif
</div>

<!-- Revenue Chart -->
<div class="card-base mb-8">
    <h3 class="text-lg font-bold text-gray-900 mb-4">Revenus mensuels</h3>
    <canvas id="revenueChart" height="100"></canvas>
</div>

<!-- Recent Transactions -->
<div class="card-base">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-900">Transactions récentes</h3>
        <a href="{{ route('admin.finance.transactions') }}" class="text-blue-600 hover:text-blue-800">
            Voir tout <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-3 px-4">Référence</th>
                    <th class="text-left py-3 px-4">Utilisateur</th>
                    <th class="text-left py-3 px-4">Montant</th>
                    <th class="text-left py-3 px-4">Type</th>
                    <th class="text-left py-3 px-4">Statut</th>
                    <th class="text-left py-3 px-4">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions ?? [] as $transaction)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-3 px-4 font-mono text-sm">{{ $transaction->reference ?? 'N/A' }}</td>
                    <td class="py-3 px-4">{{ $transaction->user_name ?? 'N/A' }}</td>
                    <td class="py-3 px-4 font-semibold">{{ number_format($transaction->amount ?? 0, 2) }} €</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst($transaction->type ?? 'N/A') }}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        @php
                            $statusColors = [
                                'pending' => 'yellow',
                                'completed' => 'green',
                                'failed' => 'red',
                                'cancelled' => 'gray'
                            ];
                            $color = $statusColors[$transaction->status ?? ''] ?? 'gray';
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                            {{ ucfirst($transaction->status ?? 'N/A') }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-600">
                        {{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-500">
                        Aucune transaction récente
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(($monthlyRevenue ?? collect())->pluck('month')->toArray()) !!},
        datasets: [{
            label: 'Revenus (€)',
            data: {!! json_encode(($monthlyRevenue ?? collect())->pluck('total')->toArray()) !!},
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            borderColor: 'rgb(16, 185, 129)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
@endpush
@endsection
