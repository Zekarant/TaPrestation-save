@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4 sm:py-6 lg:py-8">
    <div>
        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">Statistiques des offres</h1>
        <p class="text-gray-600 mt-1 text-sm sm:text-base">Analysez vos performances et tendances d'enchères</p>
    </div>

    <!-- Top Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 sm:gap-4 lg:gap-6 mt-6 sm:mt-8 mb-6 sm:mb-8">
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Total Bids</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_bids'] }}</p>
            <p class="text-xs text-gray-500 mt-1">All time</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Success Rate</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-green-600 mt-1">{{ $stats['success_rate'] }}%</p>
            <p class="text-xs text-gray-500 mt-1">Bids accepted</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Avg Bid</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-blue-600 mt-1 truncate">€{{ number_format($stats['avg_bid_amount'], 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Per bid</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Total Spent</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 mt-1 truncate">€{{ number_format($stats['total_spent'], 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">All bids</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 col-span-2 sm:col-span-1">
            <p class="text-gray-600 text-xs sm:text-sm">Active Bids</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-yellow-600 mt-1">{{ $stats['active_bids'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Pending response</p>
        </div>
    </div>

    <!-- Charts & Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Bids by Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Offres par statut</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-green-600">Accepted</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $stats['accepted'] }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-green-500 h-3 rounded-full" style="width: {{ ($stats['accepted'] / $stats['total_bids'] * 100) ?? 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-yellow-600">Pending</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $stats['pending'] }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-yellow-500 h-3 rounded-full" style="width: {{ ($stats['pending'] / $stats['total_bids'] * 100) ?? 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-red-600">Rejected</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $stats['rejected'] }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-red-500 h-3 rounded-full" style="width: {{ ($stats['rejected'] / $stats['total_bids'] * 100) ?? 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-gray-600">Expired</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $stats['expired'] }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-gray-500 h-3 rounded-full" style="width: {{ ($stats['expired'] / $stats['total_bids'] * 100) ?? 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Trend -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Bidding Trend (Last 6 Months)</h3>
            <div class="h-64 flex items-end gap-1">
                @foreach ($stats['monthly_trend'] as $month => $count)
                <div class="flex-1 bg-gray-200 rounded-t relative group" style="height: {{ (($count / max($stats['monthly_trend'])) * 100) ?? 10 }}%">
                    <div class="absolute -top-8 left-0 right-0 text-center hidden group-hover:block bg-gray-900 text-white text-xs rounded py-1 whitespace-nowrap">
                        {{ $count }} bids
                    </div>
                </div>
            </div>
            <div class="flex gap-1 text-xs text-gray-600 mt-4">
                @foreach (array_keys($stats['monthly_trend']) as $month)
                <div class="flex-1 text-center">{{ substr($month, 5) }}</div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Bid Analysis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Highest & Lowest -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Bid Range</h3>
            <div class="space-y-6">
                <div>
                    <p class="text-gray-600 text-sm mb-2">Highest Bid</p>
                    <p class="text-2xl font-bold text-gray-900">€{{ number_format($stats['highest_bid'], 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">On {{ $stats['highest_bid_service'] ?? 'Service' }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm mb-2">Lowest Bid</p>
                    <p class="text-2xl font-bold text-gray-900">€{{ number_format($stats['lowest_bid'], 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">On {{ $stats['lowest_bid_service'] ?? 'Service' }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm mb-2">Median Bid</p>
                    <p class="text-2xl font-bold text-gray-900">€{{ number_format($stats['median_bid'], 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Middle value</p>
                </div>
            </div>
        </div>

        <!-- Top Categories -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Offres par catégorie</h3>
            <div class="space-y-3">
                @foreach ($stats['top_categories'] as $category)
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-900">{{ $category['name'] }}</span>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-600">{{ $category['count'] }} bids</span>
                        <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold">
                            €{{ number_format($category['total'], 2) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Bids -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Bids</h3>
        <div class="space-y-3">
            @forelse ($recentBids as $bid)
            <div class="flex items-center justify-between pb-3 border-b border-gray-200 last:border-b-0">
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900">{{ $bid->service->name }}</p>
                    <p class="text-xs text-gray-600">{{ $bid->service->prestataire->name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-900">€{{ number_format($bid->bid_amount, 2) }}</p>
                    <span class="inline-block px-2 py-1 rounded text-xs font-semibold
                        {{ $bid->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $bid->status === 'accepted' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $bid->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                    ">
                        {{ ucfirst($bid->status) }}
                    </span>
                </div>
            </div>
            @empty
            <p class="text-gray-600 text-center py-4">Aucune offre pour le moment</p>
            @endforelse
        </div>
    </div>

    <!-- Export Section -->
    <div class="mt-8 flex gap-3">
        <button onclick="exportPDF()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
            📄 Exporter en PDF
        </button>
        <button onclick="exportCSV()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
            📊 Exporter en CSV
        </button>
    </div>
</div>

<script>
    function exportPDF() {
        // Implementation for PDF export
        alert('Export PDF bientôt disponible');
    }

    function exportCSV() {
        window.location.href = '{{ route('auction.stats.export', ['format' => 'csv']) }}';
    }
</script>
@endsection
