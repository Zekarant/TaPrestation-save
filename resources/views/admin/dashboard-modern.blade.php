@extends('layouts.admin-modern')

@section('page-title', 'Tableau de bord')

@section('content')
<!-- Header Section -->
<div class="bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 rounded-2xl shadow-2xl mb-6 sm:mb-8 overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-10"></div>
    <div class="relative px-6 sm:px-8 py-8 sm:py-12">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-6">
            <div class="text-white">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-2 sm:mb-3">Tableau de Bord Administrateur</h1>
                <p class="text-blue-100 text-sm sm:text-base lg:text-lg opacity-90">Vue d'ensemble des activités et statistiques de votre plateforme</p>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Section -->
<div class="mb-6 sm:mb-8">
    <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4 sm:mb-6 flex items-center">
        <i class="fas fa-chart-bar mr-2 sm:mr-3 text-blue-600"></i>
        Statistiques Principales
    </h2>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Total Users Card -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-4 sm:p-6 border border-blue-200 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-blue-600 text-xs sm:text-sm font-medium uppercase tracking-wide mb-1 sm:mb-2">Total Utilisateurs</p>
                    <p class="text-2xl sm:text-3xl font-bold text-blue-900">{{ $totalUsersCount ?? 0 }}</p>
                </div>
                <div class="bg-blue-500 p-2 sm:p-3 rounded-xl group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-users text-white text-lg sm:text-xl"></i>
                </div>
            </div>
            <div class="flex items-center text-xs sm:text-sm {{ ($userChange ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                <i class="fas {{ ($userChange ?? 0) >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                <span>{{ number_format($userChange ?? 0, 1) }}% ce mois</span>
            </div>
        </div>

        <!-- Approved Providers Card -->
        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-2xl p-4 sm:p-6 border border-emerald-200 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-emerald-600 text-xs sm:text-sm font-medium uppercase tracking-wide mb-1 sm:mb-2">Prestataires Approuvés</p>
                    <p class="text-2xl sm:text-3xl font-bold text-emerald-900">{{ $approvedPrestatairesCount ?? 0 }}</p>
                </div>
                <div class="bg-emerald-500 p-2 sm:p-3 rounded-xl group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-user-check text-white text-lg sm:text-xl"></i>
                </div>
            </div>
            <div class="flex items-center text-xs sm:text-sm {{ ($prestataireChange ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                <i class="fas {{ ($prestataireChange ?? 0) >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                <span>{{ number_format($prestataireChange ?? 0, 1) }}% ce mois</span>
            </div>
        </div>

        <!-- Pending Providers Card -->
        <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-2xl p-4 sm:p-6 border border-amber-200 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-amber-600 text-xs sm:text-sm font-medium uppercase tracking-wide mb-1 sm:mb-2">En Attente</p>
                    <p class="text-2xl sm:text-3xl font-bold text-amber-900">{{ $pendingPrestatairesCount ?? 0 }}</p>
                </div>
                <div class="bg-amber-500 p-2 sm:p-3 rounded-xl group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-clock text-white text-lg sm:text-xl"></i>
                </div>
            </div>
            <div class="flex items-center text-xs sm:text-sm {{ ($pendingChange ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                <i class="fas {{ ($pendingChange ?? 0) >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                <span>{{ number_format($pendingChange ?? 0, 1) }}% ce mois</span>
            </div>
        </div>

        <!-- Active Services Card -->
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-4 sm:p-6 border border-purple-200 hover:shadow-lg transition-all duration-300 group">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="text-purple-600 text-xs sm:text-sm font-medium uppercase tracking-wide mb-1 sm:mb-2">Services Actifs</p>
                    <p class="text-2xl sm:text-3xl font-bold text-purple-900">{{ $activeServicesCount ?? 0 }}</p>
                </div>
                <div class="bg-purple-500 p-2 sm:p-3 rounded-xl group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-briefcase text-white text-lg sm:text-xl"></i>
                </div>
            </div>
            <div class="flex items-center text-xs sm:text-sm {{ ($serviceChange ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                <i class="fas {{ ($serviceChange ?? 0) >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                <span>{{ number_format($serviceChange ?? 0, 1) }}% ce mois</span>
            </div>
        </div>
    </div>
</div>

<!-- Analyses et Tendances -->
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
        <i class="fas fa-chart-line mr-3 text-blue-600"></i>
        Analyses et Tendances
    </h2>
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <!-- Main Chart -->
        <div class="chart-card card-base xl:col-span-2">
        <div class="chart-header card-header">
            <div class="card-title">Évolution des Inscriptions</div>
            <div style="display: flex; gap: 1rem;" id="chart-period-buttons">
                <button class="btn btn-outline" data-period="7j">7j</button>
                <button class="btn btn-outline" data-period="30j">30j</button>
                <button class="btn btn-primary" data-period="1an">1an</button>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="registrationsChart"></canvas>
        </div>
    </div>
        
        <!-- Progress Stats -->
        <div class="chart-card card-base">
        <div class="chart-header card-header">
            <div class="card-title">Statistiques Détaillées</div>
        </div>
        <div style="padding: 1rem 0;">
            <div style="margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-weight: 500;">Total des services</span>
                    <span style="font-weight: 600; color: var(--primary);">{{ $totalServices ?? 0 }}</span>
                </div>
                <div class="progress">
                    <div class="progress-bar variant-primary" style="width: 85%;"></div>
                </div>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-weight: 500;">Services publiés</span>
                    <span style="font-weight: 600; color: var(--success);">{{ $totalServices ?? 0 }}</span>
                </div>
                <div class="progress">
                    <div class="progress-bar variant-success" style="width: 70%;"></div>
                </div>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-weight: 500;">Messages échangés</span>
                    <span style="font-weight: 600; color: var(--info);">{{ $totalMessages ?? 0 }}</span>
                </div>
                <div class="progress">
                    <div class="progress-bar variant-info" style="width: 92%;"></div>
                </div>
            </div>
            
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-weight: 500;">Taux de satisfaction</span>
                    <span style="font-weight: 600; color: var(--warning);">{{ $satisfactionRate ?? 0 }}%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar variant-warning" style="width: {{ $satisfactionRate ?? 0 }}%;"></div>
                </div>
            </div>
    </div>
</div>
</div>

<!-- Activités Récentes -->
<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
        <i class="fas fa-clock mr-3 text-blue-600"></i>
        Activités Récentes
    </h2>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Pending Prestataires -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 border-b border-blue-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-blue-900">Prestataires en Attente</h3>
                <a href="{{ route('administrateur.prestataires.pending') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center gap-2">
                    <i class="fas fa-eye text-sm"></i>
                    Voir tout
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pendingPrestataires ?? [] as $prestataire)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                                        @if($prestataire->photo)
                                            <img src="{{ asset('storage/' . $prestataire->photo) }}" alt="{{ $prestataire->user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                        @elseif($prestataire->user->avatar)
                                            <img src="{{ asset('storage/' . $prestataire->user->avatar) }}" alt="{{ $prestataire->user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <div style="width: 100%; height: 100%; background: var(--primary); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.875rem;">
                                                {{ substr($prestataire->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        @if($prestataire->isVerified())
                                            <div style="position: absolute; top: -2px; right: -2px; width: 12px; height: 12px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1px solid white;">
                                                <svg style="width: 8px; height: 8px; color: white;" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div style="font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                                            {{ $prestataire->user->name }}
                                            @if($prestataire->isVerified())
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Vérifié
                                                </span>
                                            @endif
                                        </div>
                                        <div style="font-size: 0.875rem; color: var(--secondary);">{{ $prestataire->secteur_activite ?? 'Non spécifié' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $prestataire->user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('administrateur.prestataires.show', $prestataire->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg text-xs font-medium transition-colors duration-200 inline-flex items-center gap-1">
                                    <i class="fas fa-eye"></i>
                                    Voir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-check-circle text-3xl mb-2 text-green-500"></i>
                                <div>Aucun prestataire en attente</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Recent Users -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 border-b border-blue-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-blue-900">Derniers Utilisateurs</h3>
                <a href="{{ route('administrateur.users.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center gap-2">
                    <i class="fas fa-eye text-sm"></i>
                    Voir tout
                </a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inscrit</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentUsers ?? [] as $user)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-xs font-medium
                                    @if($user->role === 'administrateur') bg-blue-100 text-blue-800
                                    @elseif($user->role === 'prestataire') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->created_at->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-users text-3xl mb-2"></i>
                                <div>Aucun utilisateur récent</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('registrationsChart').getContext('2d');
        let chart;

        const initialData = @json($chartData);

        function renderChart(data) {
            if (chart) {
                chart.destroy();
            }
            chart = new Chart(ctx, {
                type: 'line',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        }

        function updateChart(period) {
            fetch(`{{ route('administrateur.dashboard.chart') }}?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    renderChart(data);
                });
        }

        document.querySelectorAll('#chart-period-buttons button').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('#chart-period-buttons button').forEach(btn => btn.classList.remove('btn-primary'));
                this.classList.add('btn-primary');
                updateChart(this.dataset.period);
            });
        });

        // Initial render
        renderChart(initialData);

        // Animate counters on page load
        const counters = document.querySelectorAll('.stat-value');
        
        counters.forEach(counter => {
            const target = parseInt(counter.textContent);
            if (isNaN(target)) return;
            const increment = target > 0 ? target / 100 : 0;
            let current = 0;
            
            if (target === 0) {
                counter.textContent = 0;
                return;
            }

            const timer = setInterval(() => {
                current += increment;
                counter.textContent = Math.floor(current);
                
                if (current >= target) {
                    counter.textContent = target;
                    clearInterval(timer);
                }
            }, 20);
        });
    });
</script>
@endpush