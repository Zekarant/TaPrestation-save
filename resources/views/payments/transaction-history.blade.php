@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4 sm:py-6 lg:py-8">
    <div class="mb-6 sm:mb-8">
        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">Historique des transactions</h1>
        <p class="text-gray-600 mt-1 text-sm sm:text-base">Consultez tous vos paiements et transactions</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Total</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-blue-600">{{ $transactions->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Paid</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-green-600 truncate">€{{ number_format($transactions->where('status', 'paid')->sum('amount'), 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Pending</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-yellow-600 truncate">€{{ number_format($transactions->where('status', 'pending')->sum('amount'), 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Refunded</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-red-600 truncate">€{{ number_format($transactions->where('status', 'refunded')->sum('amount'), 2) }}</p>
        </div>
    </div>

    <!-- Transactions Table (Desktop) -->
    @if ($transactions->count() > 0)
    <div class="bg-white rounded-lg shadow-lg overflow-hidden hidden sm:block">
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-900">Transaction ID</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-900 hidden md:table-cell">Booking</th>
                    <th class="px-3 sm:px-6 py-3 text-center text-xs sm:text-sm font-semibold text-gray-900">Amount</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-900">Status</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-gray-900 hidden lg:table-cell">Date</th>
                    <th class="px-3 sm:px-6 py-3 text-center text-xs sm:text-sm font-semibold text-gray-900">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($transactions as $transaction)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm font-mono text-gray-900">
                        {{ substr($transaction->stripe_payment_intent_id, 0, 12) }}...
                    </td>
                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-gray-600 text-sm hidden md:table-cell">
                        @if ($transaction->booking)
                            <a href="{{ route('bookings.show', $transaction->booking) }}" class="text-blue-600 hover:underline">
                                #{{ $transaction->booking->id }}
                            </a>
                        @else
                            <span class="text-gray-400">N/A</span>
                        @endif
                    </td>
                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-center font-semibold text-gray-900 text-sm">
                        €{{ number_format($transaction->amount, 2) }}
                    </td>
                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold 
                            {{ $transaction->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $transaction->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $transaction->status === 'refunded' ? 'bg-blue-100 text-blue-800' : '' }}
                        ">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </td>
                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-gray-600 text-sm hidden lg:table-cell">
                        {{ $transaction->created_at->format('d M Y H:i') }}
                    </td>
                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-center text-sm">
                        <button onclick="viewDetails({{ $transaction->id }})" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                            View
                        </button>
                        @if ($transaction->status === 'paid' && !$transaction->refunded_at)
                        <button onclick="refundModal({{ $transaction->id }})" class="text-red-600 hover:text-red-800 font-semibold text-sm ml-2">
                            Refund
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <!-- Transactions Cards (Mobile) -->
    <div class="sm:hidden space-y-3">
        @foreach ($transactions as $transaction)
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="font-mono text-xs text-gray-500">{{ substr($transaction->stripe_payment_intent_id, 0, 15) }}...</span>
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold 
                    {{ $transaction->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $transaction->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                    {{ $transaction->status === 'refunded' ? 'bg-blue-100 text-blue-800' : '' }}
                ">{{ ucfirst($transaction->status) }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-lg font-bold text-gray-900">€{{ number_format($transaction->amount, 2) }}</span>
                <span class="text-xs text-gray-500">{{ $transaction->created_at->format('d M Y') }}</span>
            </div>
            @if ($transaction->booking)
            <p class="text-xs text-blue-600 mt-1">Booking #{{ $transaction->booking->id }}</p>
            @endif
            <div class="flex gap-2 mt-3">
                <button onclick="viewDetails({{ $transaction->id }})" class="flex-1 text-center text-blue-600 bg-blue-50 py-1.5 rounded-lg text-xs font-semibold">View</button>
                @if ($transaction->status === 'paid' && !$transaction->refunded_at)
                <button onclick="refundModal({{ $transaction->id }})" class="flex-1 text-center text-red-600 bg-red-50 py-1.5 rounded-lg text-xs font-semibold">Refund</button>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $transactions->links() }}
    </div>
    @else
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-12 text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Aucune transaction</h2>
        <p class="text-gray-600">Vous n'avez effectué aucun paiement pour le moment.</p>
    </div>
    @endif
</div>

<!-- Refund Modal -->
<div id="refundModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg p-8 max-w-md w-full">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Demander un remboursement</h3>
        <form id="refundForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Refund Reason</label>
                <textarea name="reason" required rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-semibold">
                    Request Refund
                </button>
                <button type="button" onclick="closeRefundModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const refundModal = document.getElementById('refundModal');
    const refundForm = document.getElementById('refundForm');

    function refundModal(transactionId) {
        refundForm.action = `/payments/transaction/${transactionId}/refund`;
        refundModal.classList.remove('hidden');
    }

    function closeRefundModal() {
        refundModal.classList.add('hidden');
    }

    function viewDetails(transactionId) {
        alert('Détail de la transaction bientôt disponible !');
    }
</script>
@endsection
