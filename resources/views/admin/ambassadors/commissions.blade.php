@extends('layouts.admin-modern')

@section('title', 'Commissions - ' . ($ambassador->user->name ?? ''))

@section('content')
<div class="bg-blue-50 min-h-screen">
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6">
                <a href="{{ route('admin.ambassadors.show', $ambassador) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Retour au profil
                </a>
                <h1 class="text-2xl font-bold text-blue-900 mt-2">
                    <i class="fas fa-coins mr-2"></i>Commissions de {{ $ambassador->user->name ?? 'N/A' }}
                </h1>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-blue-50 border-b border-blue-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase">Prestataire</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase">Type</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-blue-700 uppercase">Transaction</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-blue-700 uppercase">Taux</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-blue-700 uppercase">Commission</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-blue-700 uppercase">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($commissions as $c)
                            <tr class="hover:bg-blue-50">
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $c->created_at->format('d/m/Y H:i') }}</td>
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
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">Aucune commission.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($commissions->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $commissions->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
