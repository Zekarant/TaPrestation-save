@extends('layouts.ambassador')

@section('ambassador-content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Mes commissions</h1>
        <a href="{{ route('ambassador.commissions.payouts') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
            <i class="fas fa-money-bill-wave mr-1"></i>Historique des payouts
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow border border-green-200 p-4">
            <p class="text-xs text-gray-500">Total gagné</p>
            <p class="text-2xl font-bold text-green-700">{{ number_format($stats['total_earned'], 2, ',', ' ') }}&euro;</p>
        </div>
        <div class="bg-white rounded-xl shadow border border-blue-200 p-4">
            <p class="text-xs text-gray-500">Déjà payé</p>
            <p class="text-2xl font-bold text-blue-700">{{ number_format($stats['total_paid'], 2, ',', ' ') }}&euro;</p>
        </div>
        <div class="bg-white rounded-xl shadow border border-orange-200 p-4">
            <p class="text-xs text-gray-500">Non payé</p>
            <p class="text-2xl font-bold text-orange-600">{{ number_format($stats['unpaid'], 2, ',', ' ') }}&euro;</p>
        </div>
        <div class="bg-white rounded-xl shadow border border-yellow-200 p-4">
            <p class="text-xs text-gray-500">En attente</p>
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['pending'], 2, ',', ' ') }}&euro;</p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white rounded-xl shadow border border-blue-200 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvé</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Payé</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
            </select>
            <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Tous les types</option>
                <option value="service" {{ request('type') === 'service' ? 'selected' : '' }}>Service</option>
                <option value="rental" {{ request('type') === 'rental' ? 'selected' : '' }}>Location</option>
                <option value="food" {{ request('type') === 'food' ? 'selected' : '' }}>Food</option>
                <option value="urgent_sale" {{ request('type') === 'urgent_sale' ? 'selected' : '' }}>Vente urgente</option>
            </select>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-sm">Filtrer</button>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow border border-blue-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Prestataire</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Type</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Transaction</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Taux</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Commission</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($commissions as $c)
                    <tr class="hover:bg-blue-50">
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $c->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm font-semibold">{{ $c->prestataire->company_name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm">{{ ucfirst($c->order_type) }}</td>
                        <td class="px-4 py-3 text-sm text-right">{{ number_format($c->base_amount, 2, ',', ' ') }}&euro;</td>
                        <td class="px-4 py-3 text-sm text-right">{{ $c->commission_rate }}%</td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-green-700">{{ number_format($c->commission_amount, 2, ',', ' ') }}&euro;</td>
                        <td class="px-4 py-3 text-center">
                            @if($c->status === 'paid')
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">Payé</span>
                            @elseif($c->status === 'pending')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold">En attente</span>
                            @elseif($c->status === 'cancelled')
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold">Annulé</span>
                            @else
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">{{ ucfirst($c->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucune commission.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($commissions->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">{{ $commissions->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
