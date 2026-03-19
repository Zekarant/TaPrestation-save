@extends('layouts.prestataire')

@section('title', 'Mes Factures & Relevés')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-4 sm:py-6">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">📄 Factures & Relevés</h1>
                <p class="text-sm text-gray-600 mt-1">Historique de vos ventes et commissions</p>
            </div>
            <a href="{{ route('prestataire.invoices.export') }}" 
               class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export CSV
            </a>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Factures totales</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_count'] }}</p>
                    </div>
                    <div class="text-3xl">📋</div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">CA Total</p>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_revenue'], 0, ',', ' ') }}€</p>
                    </div>
                    <div class="text-3xl">💰</div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Commissions</p>
                        <p class="text-2xl font-bold text-red-500">{{ number_format($stats['total_commission'], 0, ',', ' ') }}€</p>
                    </div>
                    <div class="text-3xl">📊</div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Net reçu</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($stats['total_net'], 0, ',', ' ') }}€</p>
                    </div>
                    <div class="text-3xl">✅</div>
                </div>
            </div>
        </div>

        {{-- Stats ce mois --}}
        <div class="grid grid-cols-2 gap-3 sm:gap-4 mb-6">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl p-4 text-white">
                <p class="text-sm text-blue-100">CA ce mois</p>
                <p class="text-3xl font-bold">{{ number_format($stats['this_month_revenue'], 0, ',', ' ') }}€</p>
            </div>
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl p-4 text-white">
                <p class="text-sm text-green-100">Net ce mois</p>
                <p class="text-3xl font-bold">{{ number_format($stats['this_month_net'], 0, ',', ' ') }}€</p>
            </div>
        </div>

        {{-- Filtres --}}
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <form method="GET" class="flex flex-wrap gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Rechercher..."
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
                <select name="status" class="px-4 py-2 border border-gray-200 rounded-lg text-sm">
                    <option value="">Tous statuts</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Payée</option>
                    <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>En attente</option>
                </select>
                <select name="period" class="px-4 py-2 border border-gray-200 rounded-lg text-sm">
                    <option value="">Toutes périodes</option>
                    <option value="month" {{ request('period') === 'month' ? 'selected' : '' }}>Ce mois</option>
                    <option value="quarter" {{ request('period') === 'quarter' ? 'selected' : '' }}>Ce trimestre</option>
                    <option value="year" {{ request('period') === 'year' ? 'selected' : '' }}>Cette année</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                    Filtrer
                </button>
            </form>
        </div>

        {{-- Liste --}}
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            {{-- Header table (desktop) --}}
            <div class="hidden sm:grid grid-cols-12 gap-2 px-4 py-2 bg-gray-50 border-b text-xs font-semibold text-gray-500 uppercase">
                <div class="col-span-4">Facture</div>
                <div class="col-span-3">Client</div>
                <div class="col-span-2 text-right">Montant</div>
                <div class="col-span-2 text-right">Net reçu</div>
                <div class="col-span-1 text-center">Statut</div>
            </div>

            @forelse($invoices as $invoice)
                <a href="{{ route('prestataire.invoices.show', $invoice) }}" 
                   class="block sm:grid sm:grid-cols-12 gap-2 px-3 sm:px-4 py-3 hover:bg-blue-50 border-b border-gray-100 last:border-0 transition-colors">
                    
                    {{-- Mobile --}}
                    <div class="sm:hidden space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-sm text-gray-900">{{ $invoice->invoice_number }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">{{ $invoice->status_label }}</span>
                        </div>
                        <p class="text-sm text-gray-700">{{ $invoice->description }}</p>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">👤 {{ $invoice->billing_name }} • {{ $invoice->issued_at?->format('d/m/Y') }}</span>
                            <span class="font-bold text-green-600">+{{ number_format($invoice->net_amount, 2) }}€</span>
                        </div>
                    </div>

                    {{-- Desktop --}}
                    <div class="hidden sm:block col-span-4">
                        <p class="font-semibold text-sm text-gray-900">{{ $invoice->invoice_number }}</p>
                        <p class="text-sm text-gray-600">{{ Str::limit($invoice->description, 40) }}</p>
                        <p class="text-xs text-gray-400">📅 {{ $invoice->issued_at?->format('d/m/Y à H:i') }}</p>
                    </div>
                    <div class="hidden sm:block col-span-3">
                        <p class="text-sm font-medium text-gray-900">{{ $invoice->billing_name }}</p>
                        <p class="text-xs text-gray-500">{{ $invoice->billing_email }}</p>
                    </div>
                    <div class="hidden sm:block col-span-2 text-right">
                        <p class="text-sm font-medium text-gray-900">{{ number_format($invoice->total, 2) }}€</p>
                        <p class="text-xs text-red-500">-{{ number_format($invoice->commission_amount, 2) }}€ com.</p>
                    </div>
                    <div class="hidden sm:block col-span-2 text-right">
                        <p class="text-base font-bold text-green-600">+{{ number_format($invoice->net_amount, 2) }}€</p>
                    </div>
                    <div class="hidden sm:flex col-span-1 items-center justify-center">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $invoice->status_label }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="text-center py-8">
                    <div class="text-4xl mb-3">📭</div>
                    <p class="text-gray-500">Aucune facture trouvée</p>
                </div>
            @endforelse
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
