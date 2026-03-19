@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4 sm:py-6 lg:py-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 sm:mb-8">
        <div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">Historique des livraisons</h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Gérez et suivez toutes vos livraisons</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 sm:gap-4 lg:gap-6 mb-6 sm:mb-8">
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Total</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 mt-1">{{ $totalDeliveries }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Completed</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-green-600 mt-1">{{ $completedDeliveries }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">In Transit</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-blue-600 mt-1">{{ $inTransitDeliveries }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Pending</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-yellow-600 mt-1">{{ $pendingDeliveries }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 col-span-2 sm:col-span-1">
            <p class="text-gray-600 text-xs sm:text-sm">Total Spent</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 mt-1 truncate">€{{ number_format($totalSpent, 2) }}</p>
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
                    <option value="pickup">Pickup Scheduled</option>
                    <option value="in-transit">In Transit</option>
                    <option value="delivered">Delivered</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Provider</label>
                <select id="providerFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Providers</option>
                    @foreach ($providers as $provider)
                    <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Date Range</label>
                <input type="date" id="dateFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Search</label>
                <input type="text" id="searchFilter" placeholder="Search tracking..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
    </div>

    <!-- Deliveries Table (Desktop) -->
    <div class="bg-white rounded-lg shadow overflow-hidden hidden sm:block">
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Tracking #</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Provider</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">From/To</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Cost</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-900 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($deliveries as $delivery)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <a href="{{ route('delivery.tracking', $delivery->tracking_number) }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                            {{ substr($delivery->tracking_number, 0, 12) }}...
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-semibold">
                            {{ $delivery->provider->name }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <div class="text-gray-900 font-semibold">{{ Str::limit($delivery->from_address, 20) }}</div>
                        <div class="text-gray-500 text-xs">→</div>
                        <div class="text-gray-900 font-semibold">{{ Str::limit($delivery->to_address, 20) }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                            {{ $delivery->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $delivery->status === 'pickup' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $delivery->status === 'in-transit' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $delivery->status === 'delivered' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $delivery->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                        ">
                            {{ ucfirst(str_replace('-', ' ', $delivery->status)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                        €{{ number_format($delivery->cost, 2) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $delivery->created_at->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4 text-sm text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('delivery.tracking', $delivery->tracking_number) }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                                Track
                            </a>
                            <button onclick="openDeliveryModal({{ $delivery->id }})" class="text-gray-600 hover:text-gray-700 font-semibold">
                                Details
                            </button>
                            @if ($delivery->status !== 'delivered' && $delivery->status !== 'failed')
                            <form action="{{ route('delivery.cancel', $delivery) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-700 font-semibold">
                                    Cancel
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-600">
                        <p>No deliveries found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <!-- Deliveries Cards (Mobile) -->
    <div class="sm:hidden space-y-3">
        @forelse ($deliveries as $delivery)
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between mb-2">
                <a href="{{ route('delivery.tracking', $delivery->tracking_number) }}" class="text-blue-600 font-semibold text-sm">{{ substr($delivery->tracking_number, 0, 12) }}...</a>
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold
                    {{ $delivery->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $delivery->status === 'in-transit' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $delivery->status === 'delivered' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $delivery->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                ">{{ ucfirst(str_replace('-', ' ', $delivery->status)) }}</span>
            </div>
            <div class="text-xs text-gray-600 mb-2">
                <span class="font-semibold">{{ Str::limit($delivery->from_address, 25) }}</span> → <span class="font-semibold">{{ Str::limit($delivery->to_address, 25) }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="font-bold text-gray-900">€{{ number_format($delivery->cost, 2) }}</span>
                <span class="text-xs text-gray-500">{{ $delivery->created_at->format('d M Y') }}</span>
            </div>
            <div class="flex gap-2 mt-3">
                <a href="{{ route('delivery.tracking', $delivery->tracking_number) }}" class="flex-1 text-center text-blue-600 bg-blue-50 py-1.5 rounded-lg text-xs font-semibold">Track</a>
                <button onclick="openDeliveryModal({{ $delivery->id }})" class="flex-1 text-center text-gray-600 bg-gray-50 py-1.5 rounded-lg text-xs font-semibold">Details</button>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow p-6 text-center text-gray-600">No deliveries found.</div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($deliveries->hasPages())
    <div class="mt-6">
        {{ $deliveries->links() }}
    </div>
    @endif

    <!-- Export Section -->
    <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row gap-2 sm:gap-3">
        <button onclick="exportPDF()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
            📄 Export as PDF
        </button>
        <button onclick="exportCSV()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
            📊 Export as CSV
        </button>
    </div>
</div>

<!-- Delivery Details Modal -->
<div id="deliveryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg p-8 max-w-md w-full">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Delivery Details</h3>
        <div id="deliveryContent">
            <!-- Content loaded via JS -->
        </div>
        <button type="button" onclick="closeDeliveryModal()" class="mt-4 w-full bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-semibold">
            Close
        </button>
    </div>
</div>

<script>
    const deliveryModal = document.getElementById('deliveryModal');

    function openDeliveryModal(deliveryId) {
        deliveryModal.classList.remove('hidden');
        fetch(`/delivery/orders/${deliveryId}`)
            .then(r => r.text())
            .then(html => document.getElementById('deliveryContent').innerHTML = html);
    }

    function closeDeliveryModal() {
        deliveryModal.classList.add('hidden');
    }

    function exportPDF() {
        window.location.href = '{{ route('delivery.orders.export', ['format' => 'pdf']) }}';
    }

    function exportCSV() {
        window.location.href = '{{ route('delivery.orders.export', ['format' => 'csv']) }}';
    }

    // Filter functionality
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('providerFilter').addEventListener('change', applyFilters);
    document.getElementById('dateFilter').addEventListener('change', applyFilters);
    document.getElementById('searchFilter').addEventListener('input', applyFilters);

    function applyFilters() {
        const status = document.getElementById('statusFilter').value;
        const provider = document.getElementById('providerFilter').value;
        const date = document.getElementById('dateFilter').value;
        const search = document.getElementById('searchFilter').value;
        
        const params = new URLSearchParams();
        if (status) params.append('status', status);
        if (provider) params.append('provider', provider);
        if (date) params.append('date', date);
        if (search) params.append('search', search);
        
        window.location.href = `{{ route('delivery.orders') }}?${params.toString()}`;
    }
</script>
@endsection
