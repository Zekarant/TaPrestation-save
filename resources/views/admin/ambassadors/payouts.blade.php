@extends('layouts.admin-modern')

@section('title', 'Payouts Ambassadeurs')

@section('content')
<div class="bg-blue-50 min-h-screen">
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6">
                <a href="{{ route('admin.ambassadors.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Retour aux ambassadeurs
                </a>
                <h1 class="text-2xl font-bold text-blue-900 mt-2">
                    <i class="fas fa-money-bill-wave mr-2"></i>Historique des Payouts
                </h1>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-blue-50 border-b border-blue-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase">Ambassadeur</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-blue-700 uppercase">Montant</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-blue-700 uppercase">Statut</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($payouts as $payout)
                            <tr class="hover:bg-blue-50">
                                <td class="px-4 py-3 text-sm font-mono text-gray-600">#{{ $payout->id }}</td>
                                <td class="px-4 py-3 text-sm font-semibold">{{ $payout->ambassador->user->name ?? 'N/A' }}</td>
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
                                <td class="px-4 py-3 text-xs text-gray-500">{{ Str::limit($payout->notes, 50) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">Aucun payout.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($payouts->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $payouts->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
