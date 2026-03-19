@extends('layouts.admin-modern')

@section('title', 'Rapport des Commissions')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">💰 Rapport des Commissions</h1>
                <p class="text-gray-600 mt-1">Suivi détaillé des commissions de la plateforme</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.invoices.index') }}" class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-50">
                    ← Factures
                </a>
                <a href="{{ route('admin.invoices.commissions', ['export' => 'csv']) }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exporter CSV
                </a>
            </div>
        </div>

        {{-- Filtres --}}
        <form method="GET" class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Période</label>
                    <select name="period" class="w-full rounded-lg border-gray-200 text-sm" onchange="this.form.submit()">
                        <option value="all" {{ $period === 'all' ? 'selected' : '' }}>Tout</option>
                        <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                        <option value="week" {{ $period === 'week' ? 'selected' : '' }}>Cette semaine</option>
                        <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Ce mois</option>
                        <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Cette année</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Date début</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" 
                           class="w-full rounded-lg border-gray-200 text-sm" onchange="this.form.submit()">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Date fin</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" 
                           class="w-full rounded-lg border-gray-200 text-sm" onchange="this.form.submit()">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Prestataire</label>
                    <select name="prestataire_id" class="w-full rounded-lg border-gray-200 text-sm" onchange="this.form.submit()">
                        <option value="">Tous les prestataires</option>
                        @foreach($prestataires as $presta)
                            <option value="{{ $presta->id }}" {{ request('prestataire_id') == $presta->id ? 'selected' : '' }}>
                                {{ $presta->user->name ?? 'Prestataire #'.$presta->id }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        {{-- Stats principales --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-5 text-white">
                <p class="text-blue-100 text-sm">CA Total</p>
                <p class="text-2xl font-bold mt-1">{{ number_format($stats['total_revenue'], 0, ',', ' ') }}€</p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-5 text-white">
                <p class="text-purple-100 text-sm">Commissions</p>
                <p class="text-2xl font-bold mt-1">{{ number_format($stats['total_commission'], 0, ',', ' ') }}€</p>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-5 text-white">
                <p class="text-green-100 text-sm">Net Prestataires</p>
                <p class="text-2xl font-bold mt-1">{{ number_format($stats['total_net'], 0, ',', ' ') }}€</p>
            </div>
            <div class="bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl p-5 text-white">
                <p class="text-amber-100 text-sm">Taux moyen</p>
                <p class="text-2xl font-bold mt-1">{{ number_format($stats['avg_rate'], 1) }}%</p>
            </div>
        </div>

        {{-- Graphiques --}}
        <div class="grid lg:grid-cols-2 gap-6 mb-6">
            {{-- Évolution mensuelle --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-4">📈 Évolution mensuelle</h3>
                <div class="space-y-3">
                    @foreach($monthlyStats as $month)
                        @php
                            $maxRevenue = $monthlyStats->max('revenue') ?: 1;
                            $percentage = ($month->revenue / $maxRevenue) * 100;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700">{{ \Carbon\Carbon::createFromFormat('Y-m', $month->month)->format('M Y') }}</span>
                                <span class="text-gray-500">
                                    <span class="font-semibold text-blue-600">{{ number_format($month->revenue, 0) }}€</span>
                                    <span class="text-purple-600 ml-2">({{ number_format($month->commission, 0) }}€)</span>
                                </span>
                            </div>
                            <div class="w-full h-4 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-blue-500 to-purple-500 rounded-full" 
                                     style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Top prestataires --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-4">🏆 Top Prestataires</h3>
                <div class="space-y-3">
                    @foreach($topPrestataires as $index => $presta)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 flex items-center justify-center rounded-full 
                                {{ $index === 0 ? 'bg-yellow-100 text-yellow-600' : 
                                   ($index === 1 ? 'bg-gray-100 text-gray-600' : 
                                   ($index === 2 ? 'bg-amber-100 text-amber-600' : 'bg-gray-100 text-gray-500')) }}
                                font-bold text-sm">
                                #{{ $index + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 truncate">{{ $presta->prestataire_name }}</p>
                                <p class="text-xs text-gray-500">{{ $presta->invoices_count }} factures</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-blue-600">{{ number_format($presta->total_revenue, 0) }}€</p>
                                <p class="text-xs text-purple-600">{{ number_format($presta->total_commission, 0) }}€ com.</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Détail par prestataire --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">📋 Détail des commissions par prestataire</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Prestataire</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Factures</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">CA Total</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Commission</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Net versé</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Taux</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($prestataireStats as $stat)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <p class="font-medium text-gray-900">{{ $stat->prestataire_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $stat->prestataire_email }}</p>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-1 bg-gray-100 rounded text-sm font-medium">{{ $stat->invoices_count }}</span>
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-gray-900">
                                    {{ number_format($stat->total_revenue, 2, ',', ' ') }}€
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-purple-600">
                                    {{ number_format($stat->total_commission, 2, ',', ' ') }}€
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-green-600">
                                    {{ number_format($stat->total_net, 2, ',', ' ') }}€
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="text-sm font-medium text-gray-600">{{ number_format($stat->avg_rate, 1) }}%</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-500">
                                    Aucune commission trouvée pour cette période
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($prestataireStats->isNotEmpty())
                        <tfoot class="bg-gray-50 font-bold">
                            <tr>
                                <td class="py-4 px-4 text-gray-900">TOTAL</td>
                                <td class="py-4 px-4 text-center">{{ $prestataireStats->sum('invoices_count') }}</td>
                                <td class="py-4 px-4 text-right text-blue-600">{{ number_format($stats['total_revenue'], 2, ',', ' ') }}€</td>
                                <td class="py-4 px-4 text-right text-purple-600">{{ number_format($stats['total_commission'], 2, ',', ' ') }}€</td>
                                <td class="py-4 px-4 text-right text-green-600">{{ number_format($stats['total_net'], 2, ',', ' ') }}€</td>
                                <td class="py-4 px-4 text-center text-gray-600">{{ number_format($stats['avg_rate'], 1) }}%</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
