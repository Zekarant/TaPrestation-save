@extends('layouts.admin-modern')

@section('title', 'Gestion des Factures')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">📄 Gestion des Factures</h1>
                <p class="text-sm text-gray-600 mt-1">Toutes les transactions de la plateforme</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.invoices.commissions') }}" 
                   class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Rapport Commissions
                </a>
                <a href="{{ route('admin.invoices.export', request()->all()) }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export CSV
                </a>
            </div>
        </div>

        {{-- Stats principales --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-blue-500">
                <p class="text-sm text-gray-500">Factures totales</p>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_invoices']) }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-green-500">
                <p class="text-sm text-gray-500">CA Total</p>
                <p class="text-3xl font-bold text-green-600">{{ number_format($stats['total_revenue'], 0, ',', ' ') }}€</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-purple-500">
                <p class="text-sm text-gray-500">Commissions gagnées</p>
                <p class="text-3xl font-bold text-purple-600">{{ number_format($stats['total_commission'], 0, ',', ' ') }}€</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-indigo-500">
                <p class="text-sm text-gray-500">Versé aux prestataires</p>
                <p class="text-3xl font-bold text-indigo-600">{{ number_format($stats['total_net_prestataires'], 0, ',', ' ') }}€</p>
            </div>
        </div>

        {{-- Stats aujourd'hui & ce mois --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl p-4 text-white">
                <p class="text-sm text-blue-100">Aujourd'hui</p>
                <p class="text-2xl font-bold">{{ number_format($stats['today_revenue'], 0, ',', ' ') }}€</p>
                <p class="text-xs text-blue-200">{{ $stats['today_invoices'] }} factures</p>
            </div>
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl p-4 text-white">
                <p class="text-sm text-green-100">CA ce mois</p>
                <p class="text-2xl font-bold">{{ number_format($stats['month_revenue'], 0, ',', ' ') }}€</p>
            </div>
            <div class="bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl p-4 text-white">
                <p class="text-sm text-purple-100">Commissions ce mois</p>
                <p class="text-2xl font-bold">{{ number_format($stats['month_commission'], 0, ',', ' ') }}€</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-sm text-gray-500">Par statut</p>
                <div class="flex items-center gap-3 mt-2">
                    <span class="text-green-600 font-bold">✓ {{ $stats['paid_count'] }}</span>
                    <span class="text-blue-600 font-bold">⏳ {{ $stats['pending_count'] }}</span>
                    <span class="text-red-600 font-bold">✗ {{ $stats['cancelled_count'] }}</span>
                </div>
            </div>
        </div>

        {{-- Top prestataires --}}
        @if($stats['top_prestataires']->count() > 0)
            <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
                <h3 class="font-semibold text-gray-900 mb-3">🏆 Top Prestataires ce mois</h3>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    @foreach($stats['top_prestataires'] as $top)
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <p class="font-semibold text-gray-900 truncate">{{ $top->prestataire?->user?->name ?? 'N/A' }}</p>
                            <p class="text-lg font-bold text-green-600">{{ number_format($top->total_revenue, 0) }}€</p>
                            <p class="text-xs text-gray-500">{{ $top->invoice_count }} ventes</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Filtres avancés --}}
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Rechercher..."
                           class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                    
                    <select name="type" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                        <option value="">Tous types</option>
                        <option value="client" {{ request('type') === 'client' ? 'selected' : '' }}>Client</option>
                        <option value="prestataire" {{ request('type') === 'prestataire' ? 'selected' : '' }}>Prestataire</option>
                    </select>

                    <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                        <option value="">Tous statuts</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Payée</option>
                        <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Émise</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                        <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Remboursée</option>
                    </select>

                    <select name="period" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                        <option value="">Toutes périodes</option>
                        <option value="today" {{ request('period') === 'today' ? 'selected' : '' }}>Aujourd'hui</option>
                        <option value="week" {{ request('period') === 'week' ? 'selected' : '' }}>Cette semaine</option>
                        <option value="month" {{ request('period') === 'month' ? 'selected' : '' }}>Ce mois</option>
                        <option value="quarter" {{ request('period') === 'quarter' ? 'selected' : '' }}>Ce trimestre</option>
                        <option value="year" {{ request('period') === 'year' ? 'selected' : '' }}>Cette année</option>
                    </select>

                    <select name="prestataire_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                        <option value="">Tous prestataires</option>
                        @foreach($prestataires as $prest)
                            <option value="{{ $prest->id }}" {{ request('prestataire_id') == $prest->id ? 'selected' : '' }}>
                                {{ $prest->user->name ?? 'Prestataire #' . $prest->id }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                        Filtrer
                    </button>
                </div>

                {{-- Dates personnalisées --}}
                <div class="flex flex-wrap gap-3 items-center">
                    <span class="text-sm text-gray-500">Période personnalisée:</span>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                    <span class="text-gray-400">→</span>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                           class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                </div>
            </form>
        </div>

        {{-- Tableau des factures --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Facture</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Client</th>
                            <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Prestataire</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Montant</th>
                            <th class="text-right py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Commission</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                            <th class="text-center py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($invoices as $invoice)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <p class="font-semibold text-gray-900">{{ $invoice->invoice_number }}</p>
                                    <p class="text-xs text-gray-500">{{ optional($invoice->issued_at)->format('d/m/Y H:i') }}</p>
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs 
                                        {{ $invoice->type === 'client' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                        {{ $invoice->type_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <p class="font-medium text-gray-900">{{ $invoice->billing_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $invoice->billing_email }}</p>
                                </td>
                                <td class="py-3 px-4">
                                    <p class="font-medium text-gray-900">{{ data_get($invoice, 'prestataire.user.name', '-') }}</p>
                                    <p class="text-xs text-gray-500 truncate max-w-[150px]">{{ $invoice->description }}</p>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <p class="font-bold text-gray-900">{{ number_format($invoice->total, 2, ',', ' ') }}€</p>
                                    <p class="text-xs text-gray-500">HT: {{ number_format($invoice->subtotal, 2) }}€</p>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <p class="font-bold text-purple-600">{{ number_format($invoice->commission_amount, 2, ',', ' ') }}€</p>
                                    <p class="text-xs text-gray-500">{{ number_format($invoice->commission_rate, 0) }}%</p>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium
                                        {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : 
                                           ($invoice->status === 'cancelled' ? 'bg-red-100 text-red-700' : 
                                           ($invoice->status === 'refunded' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700')) }}">
                                        {{ $invoice->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ route('admin.invoices.show', $invoice) }}" 
                                       class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 text-xs">
                                        Voir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center">
                                    <div class="text-6xl mb-4">📭</div>
                                    <p class="text-gray-500">Aucune facture trouvée</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($invoices->hasPages())
            <div class="mt-6">
                {{ $invoices->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
