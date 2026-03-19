@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4 sm:py-6 lg:py-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 sm:mb-8">
        <div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">Mes offres</h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Historique de vos offres placées</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-6 sm:mb-8">
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Total Bids</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 mt-1">{{ $totalBids }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Accepted</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-green-600 mt-1">{{ $acceptedBids }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Pending</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-yellow-600 mt-1">{{ $pendingBids }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Rejected</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-red-600 mt-1">{{ $rejectedBids }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6 sm:mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Filtrer par statut</label>
                <select id="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="accepted">Accepted</option>
                    <option value="rejected">Rejected</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Sort by</label>
                <select id="sortBy" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="highest">Highest Amount</option>
                    <option value="lowest">Lowest Amount</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Min Amount (€)</label>
                <input type="number" id="minAmount" placeholder="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Max Amount (€)</label>
                <input type="number" id="maxAmount" placeholder="9999" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
    </div>

    <!-- Bids Table (Desktop) -->
    <div class="bg-white rounded-lg shadow overflow-hidden hidden sm:block">
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Service</th>
                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Bid Amount</th>
                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Status</th>
                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase hidden md:table-cell">Prestataire</th>
                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase hidden lg:table-cell">Expires</th>
                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase hidden lg:table-cell">Date</th>
                    <th class="px-4 lg:px-6 py-3 text-right text-xs font-semibold text-gray-900 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($bids as $bid)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 lg:px-6 py-4 text-sm">
                        <a href="{{ route('service.show', $bid->service) }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                            {{ Str::limit($bid->service->name, 30) }}
                        </a>
                    </td>
                    <td class="px-4 lg:px-6 py-4 text-sm font-semibold text-gray-900">
                        €{{ number_format($bid->bid_amount, 2) }}
                    </td>
                    <td class="px-4 lg:px-6 py-4 text-sm">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                            {{ $bid->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $bid->status === 'accepted' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $bid->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $bid->status === 'expired' ? 'bg-gray-100 text-gray-800' : '' }}
                        ">
                            {{ ucfirst($bid->status) }}
                        </span>
                    </td>
                    <td class="px-4 lg:px-6 py-4 text-sm hidden md:table-cell">
                        <a href="{{ route('prestataire.show', $bid->service->prestataire) }}" class="text-gray-700 hover:text-gray-900">
                            {{ $bid->service->prestataire->name }}
                        </a>
                    </td>
                    <td class="px-4 lg:px-6 py-4 text-sm text-gray-600 hidden lg:table-cell">
                        {{ $bid->expires_at->format('d M Y') }}
                        @if ($bid->expires_at < now() && $bid->status === 'pending')
                        <span class="block text-red-600 font-semibold">Expired</span>
                        @endif
                    </td>
                    <td class="px-4 lg:px-6 py-4 text-sm text-gray-600 hidden lg:table-cell">
                        {{ $bid->created_at->format('d M Y H:i') }}
                    </td>
                    <td class="px-4 lg:px-6 py-4 text-sm text-right">
                        <div class="flex justify-end gap-2">
                            @if ($bid->status === 'pending' && $bid->expires_at > now())
                            <button onclick="openEditBidModal({{ $bid->id }}, {{ $bid->bid_amount }})" 
                                class="text-blue-600 hover:text-blue-700 font-semibold">
                                Edit
                            </button>
                            @endif
                            <button onclick="openDetailModal({{ $bid->id }})" 
                                class="text-gray-600 hover:text-gray-700 font-semibold">
                                Details
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-600">
                        <p>You haven't placed any bids yet.</p>
                        <a href="{{ route('services.index') }}" class="text-blue-600 hover:text-blue-700 mt-2 inline-block">
                            Browse services to get started
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <!-- Bids Cards (Mobile) -->
    <div class="sm:hidden space-y-3">
        @forelse ($bids as $bid)
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-start justify-between mb-2">
                <a href="{{ route('service.show', $bid->service) }}" class="text-blue-600 font-semibold text-sm flex-1 mr-2">{{ Str::limit($bid->service->name, 35) }}</a>
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold shrink-0
                    {{ $bid->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $bid->status === 'accepted' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $bid->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                    {{ $bid->status === 'expired' ? 'bg-gray-100 text-gray-800' : '' }}
                ">{{ ucfirst($bid->status) }}</span>
            </div>
            <div class="flex items-center justify-between text-sm mb-1">
                <span class="font-bold text-gray-900">€{{ number_format($bid->bid_amount, 2) }}</span>
                <span class="text-xs text-gray-500">{{ $bid->created_at->format('d M Y') }}</span>
            </div>
            <p class="text-xs text-gray-600 mb-3">{{ $bid->service->prestataire->name }} · Exp: {{ $bid->expires_at->format('d M Y') }}
                @if ($bid->expires_at < now() && $bid->status === 'pending')
                <span class="text-red-600 font-semibold">Expired</span>
                @endif
            </p>
            <div class="flex gap-2">
                @if ($bid->status === 'pending' && $bid->expires_at > now())
                <button onclick="openEditBidModal({{ $bid->id }}, {{ $bid->bid_amount }})" class="flex-1 text-center text-blue-600 bg-blue-50 py-1.5 rounded-lg text-xs font-semibold">Edit</button>
                @endif
                <button onclick="openDetailModal({{ $bid->id }})" class="flex-1 text-center text-gray-600 bg-gray-50 py-1.5 rounded-lg text-xs font-semibold">Details</button>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow p-6 text-center text-gray-600">
            <p>You haven't placed any bids yet.</p>
            <a href="{{ route('services.index') }}" class="text-blue-600 hover:text-blue-700 mt-2 inline-block">Browse services</a>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($bids->hasPages())
    <div class="mt-6">
        {{ $bids->links() }}
    </div>
    @endif
</div>

<!-- Edit Bid Modal -->
<div id="editBidModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg p-8 max-w-md w-full">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Edit Bid</h3>
        <form id="editBidForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-900 mb-2">New Bid Amount (€) *</label>
                <input type="number" id="editAmount" name="bid_amount" required min="0" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Message</label>
                <textarea name="message" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold">
                    Update Bid
                </button>
                <button type="button" onclick="closeEditBidModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg p-8 max-w-md w-full">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Bid Details</h3>
        <div id="detailContent" class="space-y-4">
            <!-- Content loaded via JS -->
        </div>
        <button type="button" onclick="closeDetailModal()" class="mt-4 w-full bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-semibold">
            Close
        </button>
    </div>
</div>

<script>
    const editBidModal = document.getElementById('editBidModal');
    const detailModal = document.getElementById('detailModal');
    let currentBidId = null;

    function openEditBidModal(bidId, amount) {
        currentBidId = bidId;
        document.getElementById('editAmount').value = amount;
        document.getElementById('editBidForm').action = `/auction/bids/${bidId}/update`;
        editBidModal.classList.remove('hidden');
    }

    function closeEditBidModal() {
        editBidModal.classList.add('hidden');
    }

    function openDetailModal(bidId) {
        detailModal.classList.remove('hidden');
        // Load bid details via AJAX
        fetch(`/auction/bids/${bidId}/details`)
            .then(r => r.text())
            .then(html => document.getElementById('detailContent').innerHTML = html);
    }

    function closeDetailModal() {
        detailModal.classList.add('hidden');
    }

    // Filter functionality
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('sortBy').addEventListener('change', applyFilters);

    function applyFilters() {
        const status = document.getElementById('statusFilter').value;
        const sort = document.getElementById('sortBy').value;
        const minAmount = document.getElementById('minAmount').value;
        const maxAmount = document.getElementById('maxAmount').value;
        
        const params = new URLSearchParams();
        if (status) params.append('status', status);
        if (sort) params.append('sort', sort);
        if (minAmount) params.append('min', minAmount);
        if (maxAmount) params.append('max', maxAmount);
        
        window.location.href = `{{ route('auction.my-bids') }}?${params.toString()}`;
    }
</script>
@endsection
