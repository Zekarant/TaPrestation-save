@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="text-gray-600 mt-2">Platform overview and key metrics</p>
    </div>

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mt-8 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm">Total Users</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total_users'] }}</p>
            <p class="text-xs text-green-600 mt-1">+{{ $stats['new_users_month'] }} this month</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm">Total Revenue</p>
            <p class="text-3xl font-bold text-green-600 mt-2">€{{ number_format($stats['total_revenue'], 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">All time</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm">Active Services</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ $stats['active_services'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Listed on platform</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm">Completed Bookings</p>
            <p class="text-3xl font-bold text-purple-600 mt-2">{{ $stats['completed_bookings'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Total transactions</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm">Avg Rating</p>
            <p class="text-3xl font-bold text-yellow-600 mt-2">{{ number_format($stats['avg_rating'], 1) }}/5</p>
            <p class="text-xs text-gray-500 mt-1">From {{ $stats['total_reviews'] }} reviews</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Revenue Trend -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Revenue Trend (Last 12 Months)</h3>
            <div class="h-64 flex items-end gap-1">
                @foreach ($stats['revenue_trend'] as $month => $amount)
                <div class="flex-1 bg-gradient-to-t from-blue-500 to-blue-400 rounded-t relative group" 
                    style="height: {{ isset($stats['revenue_trend']) && max($stats['revenue_trend']) > 0 ? (($amount / max($stats['revenue_trend'])) * 100) : 10 }}%">
                    <div class="absolute -top-8 left-0 right-0 text-center hidden group-hover:block bg-gray-900 text-white text-xs rounded py-1 whitespace-nowrap">
                        €{{ number_format($amount, 0) }}
                    </div>
                </div>
                @endforeach
            </div>
            <div class="flex gap-1 text-xs text-gray-600 mt-4">
                @foreach (array_keys($stats['revenue_trend']) as $month)
                <div class="flex-1 text-center">{{ substr($month, 5) }}</div>
                @endforeach
            </div>
        </div>

        <!-- User Growth -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">User Breakdown</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-gray-900">Clients</span>
                        <span class="text-sm font-semibold text-gray-600">{{ $stats['clients_count'] }} ({{ number_format($stats['clients_percentage'], 0) }}%)</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-blue-600 h-3 rounded-full" style="width: {{ isset($stats['clients_percentage']) ? $stats['clients_percentage'] : 0 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-gray-900">Prestataires</span>
                        <span class="text-sm font-semibold text-gray-600">{{ $stats['prestataires_count'] }} ({{ number_format($stats['prestataires_percentage'], 0) }}%)</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-green-600 h-3 rounded-full" style="width: {{ isset($stats['prestataires_percentage']) ? $stats['prestataires_percentage'] : 0 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-gray-900">Verified</span>
                        <span class="text-sm font-semibold text-gray-600">{{ $stats['verified_count'] }} ({{ number_format($stats['verified_percentage'], 0) }}%)</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-purple-600 h-3 rounded-full" style="width: {{ isset($stats['verified_percentage']) ? $stats['verified_percentage'] : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Top Services -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Top 5 Services</h3>
            <div class="space-y-3">
                @foreach ($stats['top_services'] as $index => $service)
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 last:border-b-0">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-100 text-blue-600 rounded-full text-xs font-bold">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $service['name'] }}</p>
                            <p class="text-xs text-gray-600">by {{ $service['prestataire'] }}</p>
                        </div>
                    </div>
                    <span class="text-sm font-semibold text-gray-900">{{ $service['bookings'] }} bookings</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Payments</h3>
            <div class="space-y-3">
                @foreach ($stats['recent_payments'] as $payment)
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 last:border-b-0">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $payment['client'] }}</p>
                        <p class="text-xs text-gray-600">{{ $payment['service'] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">€{{ number_format($payment['amount'], 2) }}</p>
                        <span class="inline-block px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold">
                            Paid
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Management Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="{{ route('admin.users') }}" class="bg-white rounded-lg shadow hover:shadow-lg p-6 transition-shadow border-t-4 border-blue-600">
            <h3 class="text-lg font-bold text-gray-900">👥 Manage Users</h3>
            <p class="text-gray-600 text-sm mt-2">{{ $stats['pending_verifications'] }} pending verifications</p>
            <p class="text-blue-600 font-semibold mt-4">View All →</p>
        </a>

        <a href="{{ route('admin.payments') }}" class="bg-white rounded-lg shadow hover:shadow-lg p-6 transition-shadow border-t-4 border-green-600">
            <h3 class="text-lg font-bold text-gray-900">💳 Payment Management</h3>
            <p class="text-gray-600 text-sm mt-2">€{{ number_format($stats['monthly_revenue'], 2) }} this month</p>
            <p class="text-green-600 font-semibold mt-4">View All →</p>
        </a>

        <a href="{{ route('admin.finance.index') }}" class="bg-white rounded-lg shadow hover:shadow-lg p-6 transition-shadow border-t-4 border-purple-600">
            <h3 class="text-lg font-bold text-gray-900">💰 Finance & Escrow</h3>
            <p class="text-gray-600 text-sm mt-2">Gestion des paiements sécurisés</p>
            <p class="text-purple-600 font-semibold mt-4">Gérer →</p>
        </a>

        <a href="{{ route('admin.reports') }}" class="bg-white rounded-lg shadow hover:shadow-lg p-6 transition-shadow border-t-4 border-red-600">
            <h3 class="text-lg font-bold text-gray-900">⚠️ Reports & Issues</h3>
            <p class="text-gray-600 text-sm mt-2">{{ $stats['pending_reports'] }} pending reports</p>
            <p class="text-red-600 font-semibold mt-4">View All →</p>
        </a>
    </div>
</div>
@endsection
