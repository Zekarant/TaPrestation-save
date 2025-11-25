@extends('layouts.admin-modern')

@section('title', 'Rapports et Analyses')

@section('content')
<div class="bg-blue-50 min-h-screen">
    <!-- Bannière d'en-tête -->
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-blue-900 mb-2 leading-tight">
                    <i class="fas fa-chart-line text-blue-600 mr-3"></i>
                    Rapports et Analyses
                </h1>
                <p class="text-base sm:text-lg text-blue-700 max-w-2xl mx-auto">
                    Tableau de bord analytique complet de la plateforme TaPrestation.
                </p>
            </div>
            
            <!-- Actions Header -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                <div class="flex flex-wrap gap-2 sm:gap-3">
                    <button class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base" onclick="toggleFilters()">
                    <i class="fas fa-filter mr-2"></i>
                    Afficher les filtres
                </button>
                </div>
                <div class="relative">

                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-6 sm:mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-blue-600 uppercase tracking-wide">Utilisateurs Totaux</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ number_format($globalStats['total_users']) }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+{{ $globalStats['users_growth'] }}% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-green-600 uppercase tracking-wide">Services Actifs</div>
                        <div class="text-2xl sm:text-3xl font-bold text-green-900 mt-1">{{ number_format($globalStats['total_services']) }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+{{ $globalStats['services_growth'] }}% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-briefcase text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-purple-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-purple-600 uppercase tracking-wide">Réservations</div>
                        <div class="text-2xl sm:text-3xl font-bold text-purple-900 mt-1">{{ number_format($globalStats['total_bookings']) }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+{{ $globalStats['bookings_growth'] }}% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-calendar text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-yellow-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-yellow-600 uppercase tracking-wide">Revenus</div>
                        <div class="text-2xl sm:text-3xl font-bold text-yellow-900 mt-1">{{ number_format($globalStats['total_revenue']) }}€</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+{{ $globalStats['revenue_growth'] }}% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-euro-sign text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques et analyses -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-6 sm:mb-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Graphique des inscriptions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-blue-900 flex items-center">
                        <i class="fas fa-chart-area text-blue-600 mr-2"></i>
                        Évolution des Inscriptions
                    </h3>
                </div>
                <div class="p-6">
                    <canvas id="registrationsChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Graphique des revenus -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-blue-900 flex items-center">
                        <i class="fas fa-chart-line text-green-600 mr-2"></i>
                        Évolution des Revenus
                    </h3>
                </div>
                <div class="p-6">
                    <canvas id="revenueChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableaux de données -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-6 sm:mb-8">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <!-- Top Services -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-blue-900 flex items-center">
                        <i class="fas fa-star text-yellow-500 mr-2"></i>
                        Services les Plus Populaires
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($topServices as $service)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-briefcase text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $service->title }}</div>
                                    <div class="text-sm text-gray-500">{{ $service->bookings_count }} réservations</div>
                                </div>
                            </div>
                            <div class="text-lg font-bold text-blue-600">{{ $service->price }}€</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Top Prestataires -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-blue-900 flex items-center">
                        <i class="fas fa-crown text-yellow-500 mr-2"></i>
                        Prestataires les Plus Actifs
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($topPrestataires as $prestataire)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-green-600"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $prestataire->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $prestataire->services_count }} services</div>
                                </div>
                            </div>
                            <div class="text-lg font-bold text-green-600">{{ number_format($prestataire->total_revenue) }}€</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFilters() {
    // Implémentation des filtres si nécessaire
    console.log('Toggle filters');
}

// Graphiques Chart.js
document.addEventListener('DOMContentLoaded', function() {
    // Graphique des inscriptions
    const registrationsCtx = document.getElementById('registrationsChart').getContext('2d');
    new Chart(registrationsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['registrations']['labels']) !!},
            datasets: [{
                label: 'Inscriptions',
                data: {!! json_encode($chartData['registrations']['data']) !!},
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
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
                    beginAtZero: true
                }
            }
        }
    });

    // Graphique des revenus
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData['revenue']['labels']) !!},
            datasets: [{
                label: 'Revenus (€)',
                data: {!! json_encode($chartData['revenue']['data']) !!},
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: 'rgb(34, 197, 94)',
                borderWidth: 1
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
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endsection