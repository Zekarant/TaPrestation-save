@extends('layouts.ambassador')

@section('ambassador-content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('ambassador.commissions.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
            <i class="fas fa-arrow-left mr-1"></i>Retour aux commissions
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Historique des payouts</h1>
    </div>

    <div class="bg-white rounded-xl shadow border border-blue-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Montant</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payouts as $payout)
                    <tr>
                        <td class="px-4 py-3 text-sm font-mono text-gray-600">#{{ $payout->id }}</td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-green-700">{{ number_format($payout->total_amount, 2, ',', ' ') }}&euro;</td>
                        <td class="px-4 py-3 text-center">
                            @if($payout->status === 'completed')
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">Complété</span>
                            @elseif($payout->status === 'pending')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold">En attente</span>
                            @elseif($payout->status === 'processing')
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">En cours</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold">Échoué</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $payout->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">Aucun payout pour le moment.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payouts->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">{{ $payouts->links() }}</div>
        @endif
    </div>
</div>
@endsection
