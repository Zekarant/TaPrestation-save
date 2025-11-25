@extends('layouts.admin-modern')

@section('title', 'Rapports et Analyses')

@section('content')
<div class="container-fluid px-4">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Rapports et Analyses</h1>
            <p class="mb-0 text-muted">Tableau de bord analytique de la plateforme</p>
        </div>

    </div>

    <!-- Statistiques globales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Utilisateurs Totaux</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($globalStats['total_users']) }}</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> +{{ $globalStats['users_growth'] }}% ce mois
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Revenus Totaux</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($globalStats['total_revenue'], 2) }}€</div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> +{{ $globalStats['revenue_growth'] }}% ce mois
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-euro-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Réservations</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($globalStats['total_bookings']) }}</div>
                            <div class="text-xs text-info">
                                <i class="fas fa-calendar"></i> {{ $globalStats['bookings_this_month'] }} ce mois
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Taux Conversion</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $globalStats['conversion_rate'] }}%</div>
                            <div class="text-xs text-warning">
                                <i class="fas fa-chart-line"></i> Demandes → Réservations
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques principaux -->
    <div class="row mb-4">
        <!-- Évolution mensuelle -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Évolution Mensuelle</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <a class="dropdown-item" href="#" onclick="updateChart('users')">Utilisateurs</a>
                            <a class="dropdown-item" href="#" onclick="updateChart('bookings')">Réservations</a>
                            <a class="dropdown-item" href="#" onclick="updateChart('revenue')">Revenus</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top catégories -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Catégories</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="categoriesChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        @foreach($topCategories as $index => $category)
                            <span class="mr-2">
                                <i class="fas fa-circle" style="color: {{ ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'][$index % 5] }}"></i>
                                {{ $category['name'] }} ({{ $category['count'] }})
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rapports détaillés -->
    <div class="row mb-4">
        <!-- Utilisateurs -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Rapport Utilisateurs</h6>
                    <a href="{{ route('administrateur.reports.users') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Voir détails
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-primary">{{ number_format($userStats['new_users_this_month']) }}</div>
                                <div class="text-xs text-gray-600">Nouveaux ce mois</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-success">{{ number_format($userStats['active_users']) }}</div>
                                <div class="text-xs text-gray-600">Utilisateurs actifs</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-4 text-center">
                            <div class="text-sm font-weight-bold text-info">{{ $userStats['clients_percentage'] }}%</div>
                            <div class="text-xs text-gray-600">Clients</div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="text-sm font-weight-bold text-warning">{{ $userStats['prestataires_percentage'] }}%</div>
                            <div class="text-xs text-gray-600">Prestataires</div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="text-sm font-weight-bold text-secondary">{{ $userStats['admins_percentage'] }}%</div>
                            <div class="text-xs text-gray-600">Admins</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Rapport Services</h6>
                    <a href="{{ route('administrateur.reports.services') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Voir détails
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-info">{{ number_format($serviceStats['total_services']) }}</div>
                                <div class="text-xs text-gray-600">Services totaux</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-success">{{ number_format($serviceStats['avg_price'], 0) }}€</div>
                                <div class="text-xs text-gray-600">Prix moyen</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <div class="text-sm font-weight-bold text-primary">Top Service</div>
                        <div class="text-xs text-gray-600">{{ $serviceStats['most_popular_service'] ?? 'N/A' }}</div>
                        <div class="text-xs text-muted">{{ $serviceStats['most_popular_count'] ?? 0 }} réservations</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Réservations et Finances -->
    <div class="row mb-4">
        <!-- Réservations -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Rapport Réservations</h6>
                    <a href="{{ route('administrateur.reports.bookings') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Voir détails
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-warning">{{ $bookingStats['pending_percentage'] }}%</div>
                                <div class="text-xs text-gray-600">En attente</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-success">{{ $bookingStats['completed_percentage'] }}%</div>
                                <div class="text-xs text-gray-600">Terminées</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <div class="text-sm font-weight-bold text-info">Top Prestataire</div>
                        <div class="text-xs text-gray-600">{{ $bookingStats['top_prestataire'] ?? 'N/A' }}</div>
                        <div class="text-xs text-muted">{{ $bookingStats['top_prestataire_bookings'] ?? 0 }} réservations</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Finances -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Rapport Financier</h6>
                    <a href="{{ route('administrateur.reports.finances') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Voir détails
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-success">{{ number_format($financeStats['monthly_revenue'], 0) }}€</div>
                                <div class="text-xs text-gray-600">Revenus ce mois</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 font-weight-bold text-info">{{ number_format($financeStats['commission_revenue'], 0) }}€</div>
                                <div class="text-xs text-gray-600">Commission plateforme</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <div class="text-sm font-weight-bold text-warning">Panier Moyen</div>
                        <div class="text-xs text-gray-600">{{ number_format($financeStats['avg_order_value'], 2) }}€</div>
                        <div class="text-xs text-muted">{{ $financeStats['growth_trend'] > 0 ? '+' : '' }}{{ $financeStats['growth_trend'] }}% vs mois dernier</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activité récente -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Activité Récente</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Utilisateur</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentActivity as $activity)
                                    <tr>
                                        <td>{{ $activity['date'] }}</td>
                                        <td>
                                            <span class="badge badge-{{ $activity['type_color'] }}">
                                                <i class="fas fa-{{ $activity['icon'] }}"></i>
                                                {{ $activity['type'] }}
                                            </span>
                                        </td>
                                        <td>{{ $activity['description'] }}</td>
                                        <td>{{ $activity['user'] }}</td>
                                        <td>
                                            @if($activity['amount'])
                                                <span class="font-weight-bold text-success">{{ number_format($activity['amount'], 2) }}€</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $activity['status_color'] }}">
                                                {{ $activity['status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Configuration des graphiques
const chartData = {
    users: {
        labels: {!! json_encode($monthlyData['labels']) !!},
        data: {!! json_encode($monthlyData['users']) !!},
        label: 'Nouveaux Utilisateurs',
        color: '#4e73df'
    },
    bookings: {
        labels: {!! json_encode($monthlyData['labels']) !!},
        data: {!! json_encode($monthlyData['bookings']) !!},
        label: 'Réservations',
        color: '#1cc88a'
    },
    revenue: {
        labels: {!! json_encode($monthlyData['labels']) !!},
        data: {!! json_encode($monthlyData['revenue']) !!},
        label: 'Revenus (€)',
        color: '#36b9cc'
    }
};

// Graphique mensuel
let monthlyChart;
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');

function initMonthlyChart(type = 'users') {
    if (monthlyChart) {
        monthlyChart.destroy();
    }
    
    const data = chartData[type];
    
    monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: data.label,
                data: data.data,
                borderColor: data.color,
                backgroundColor: data.color + '20',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#e3e6f0'
                    }
                },
                x: {
                    grid: {
                        color: '#e3e6f0'
                    }
                }
            }
        }
    });
}

// Graphique des catégories
const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
const categoriesChart = new Chart(categoriesCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode(array_column($topCategories, 'name')) !!},
        datasets: [{
            data: {!! json_encode(array_column($topCategories, 'count')) !!},
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        cutout: '70%'
    }
});

// Fonction pour mettre à jour le graphique mensuel
function updateChart(type) {
    initMonthlyChart(type);
}

// Initialiser le graphique mensuel
initMonthlyChart();

// Actualisation automatique toutes les 5 minutes
setInterval(function() {
    location.reload();
}, 300000);
</script>
@endpush