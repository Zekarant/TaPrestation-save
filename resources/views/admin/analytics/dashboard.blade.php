@extends('layouts.admin-modern')

@section('title', 'Analytiques')
@section('page-title', 'Tableau de bord analytique')

@section('content')
<div class="page-header">
    <h1 class="page-title">📊 Tableau de bord analytique</h1>
    <p class="page-subtitle">Vue d'ensemble complète de votre plateforme</p>
</div>

<!-- KPI Cards -->
<div class="stats-grid mb-8">
    <div class="card-base stat-card primary">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Utilisateurs totaux</p>
                <p class="text-value">{{ number_format($stats['total_users'] ?? 0) }}</p>
                <p class="text-sm text-green-600">
                    <i class="fas fa-arrow-up"></i> +{{ $stats['new_users_this_month'] ?? 0 }} ce mois
                </p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-users text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-base stat-card success">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Revenu total</p>
                <p class="text-value">{{ number_format($stats['total_revenue'] ?? 0, 2) }} €</p>
                <p class="text-sm text-green-600">
                    <i class="fas fa-arrow-up"></i> {{ number_format($stats['revenue_growth'] ?? 0, 1) }}% vs mois dernier
                </p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-euro-sign text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-base stat-card warning">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Réservations</p>
                <p class="text-value">{{ number_format($stats['total_bookings'] ?? 0) }}</p>
                <p class="text-sm text-gray-600">
                    {{ $stats['pending_bookings'] ?? 0 }} en attente
                </p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-check text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="card-base stat-card info">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-title">Services actifs</p>
                <p class="text-value">{{ number_format($stats['active_services'] ?? 0) }}</p>
                <p class="text-sm text-gray-600">
                    {{ $stats['new_services_this_week'] ?? 0 }} nouveaux cette semaine
                </p>
            </div>
            <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-briefcase text-cyan-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Revenue Chart -->
    <div class="card-base">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Évolution des revenus</h3>
        <canvas id="revenueChart" height="250"></canvas>
    </div>

    <!-- Users Chart -->
    <div class="card-base">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Évolution des inscriptions</h3>
        <canvas id="usersChart" height="250"></canvas>
    </div>
</div>

<!-- Additional Stats -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Top Categories -->
    <div class="card-base">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Catégories populaires</h3>
        <div class="space-y-3">
            @foreach(($stats['top_categories'] ?? []) as $category)
            <div class="flex items-center justify-between">
                <span class="text-gray-700">{{ $category->name }}</span>
                <span class="font-semibold text-blue-600">{{ $category->count }} services</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Top Prestataires -->
    <div class="card-base">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Top prestataires</h3>
        <div class="space-y-3">
            @foreach(($stats['top_prestataires'] ?? []) as $presta)
            <div class="flex items-center justify-between">
                <span class="text-gray-700">{{ $presta->name }}</span>
                <span class="font-semibold text-green-600">{{ number_format($presta->revenue, 2) }} €</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card-base">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Activité récente</h3>
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-plus text-green-600 text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-medium">{{ $stats['new_users_today'] ?? 0 }} nouveaux utilisateurs</p>
                    <p class="text-xs text-gray-500">Aujourd'hui</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600 text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-medium">{{ $stats['bookings_today'] ?? 0 }} réservations</p>
                    <p class="text-xs text-gray-500">Aujourd'hui</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-star text-yellow-600 text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-medium">{{ $stats['reviews_today'] ?? 0 }} avis</p>
                    <p class="text-xs text-gray-500">Aujourd'hui</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Button -->
<div class="flex justify-end">
    <form action="{{ route('admin.analytics.export') }}" method="POST">
        @csrf
        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            <i class="fas fa-download mr-2"></i> Exporter les données
        </button>
    </form>
</div>

@push('scripts')
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode(isset($charts['revenue']['labels']) ? $charts['revenue']['labels'] : []) !!},
        datasets: [{
            label: 'Revenus (€)',
            data: {!! json_encode($charts['revenue']['data'] ?? []) !!},
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Users Chart
const usersCtx = document.getElementById('usersChart').getContext('2d');
new Chart(usersCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($charts['users']['labels'] ?? []) !!},
        datasets: [{
            label: 'Inscriptions',
            data: {!! json_encode($charts['users']['data'] ?? []) !!},
            backgroundColor: '#10B981'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
@endpush
@endsection
