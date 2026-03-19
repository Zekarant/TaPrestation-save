@extends('layouts.app')

@section('title', 'Rapports Logistique')

@push('styles')
<style>
    .reports-page {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 100vh;
    }
    
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid rgba(59, 130, 246, 0.1);
    }
    
    .chart-container {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    
    .metric-ring {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    
    .metric-ring::before {
        content: '';
        position: absolute;
        inset: 4px;
        border-radius: 50%;
        background: white;
    }
    
    .metric-value {
        position: relative;
        z-index: 1;
    }
</style>
@endpush

@section('content')
<div class="reports-page py-4 sm:py-6">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        
        {{-- Section d'aide --}}
        <x-help-section page="prestataire.logistics.reports" />
        
        <!-- Header -->
        <div class="mb-6">
            <nav class="text-sm text-gray-500 mb-3">
                <a href="{{ route('prestataire.logistics.dashboard') }}" class="hover:text-blue-600">Logistique</a>
                <span class="mx-2">›</span>
                <span class="text-gray-900">Rapports & Analytics</span>
            </nav>
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                        📊 Rapports & Analytics
                    </h1>
                    <p class="text-gray-600 mt-1">Analysez les performances de vos livraisons</p>
                </div>
                
                <div class="flex gap-2">
                    <select class="px-4 py-2 border border-gray-200 rounded-lg bg-white text-sm">
                        <option value="7">7 derniers jours</option>
                        <option value="30" selected>30 derniers jours</option>
                        <option value="90">3 mois</option>
                        <option value="365">1 an</option>
                    </select>
                    <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium text-sm">
                        📥 Exporter
                    </button>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 mb-6">
            <div class="stat-card text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $stats['total'] ?? 156 }}</div>
                <div class="text-xs text-gray-500 mt-1">Total livraisons</div>
            </div>
            <div class="stat-card text-center">
                <div class="text-3xl font-bold text-green-600">{{ $stats['delivered'] ?? 142 }}</div>
                <div class="text-xs text-gray-500 mt-1">Livrées</div>
            </div>
            <div class="stat-card text-center">
                <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] ?? 8 }}</div>
                <div class="text-xs text-gray-500 mt-1">En attente</div>
            </div>
            <div class="stat-card text-center">
                <div class="text-3xl font-bold text-red-600">{{ $stats['failed'] ?? 6 }}</div>
                <div class="text-xs text-gray-500 mt-1">Échouées</div>
            </div>
            <div class="stat-card text-center">
                <div class="text-3xl font-bold text-emerald-600">{{ $stats['success_rate'] ?? 95.9 }}%</div>
                <div class="text-xs text-gray-500 mt-1">Taux de succès</div>
            </div>
            <div class="stat-card text-center">
                <div class="text-3xl font-bold text-purple-600">{{ number_format($stats['revenue'] ?? 1250, 0, ',', ' ') }}€</div>
                <div class="text-xs text-gray-500 mt-1">Revenus</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            
            <!-- Performance Chart -->
            <div class="lg:col-span-2 chart-container">
                <h3 class="font-bold text-gray-900 mb-4">📈 Livraisons par jour</h3>
                <div class="h-64 flex items-end justify-between gap-2 px-4">
                    @foreach($performanceData ?? range(1, 30) as $day)
                    @php
                        $height = is_array($day) ? ($day['count'] ?? rand(20, 100)) : rand(20, 100);
                        $value = is_array($day) ? ($day['count'] ?? rand(2, 10)) : rand(2, 10);
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-xs text-gray-500">{{ $value }}</span>
                        <div class="w-full bg-blue-500 rounded-t hover:bg-blue-600 transition cursor-pointer"
                             style="height: {{ $height }}px"
                             title="{{ $value }} livraisons"></div>
                    </div>
                    @endforeach
                </div>
                <div class="flex justify-between text-xs text-gray-400 mt-2 px-4">
                    <span>{{ now()->subDays(29)->format('d/m') }}</span>
                    <span>{{ now()->format('d/m') }}</span>
                </div>
            </div>
            
            <!-- Success Rate Ring -->
            <div class="chart-container text-center">
                <h3 class="font-bold text-gray-900 mb-4">🎯 Taux de réussite</h3>
                
                <div class="flex justify-center mb-6">
                    <div class="metric-ring" style="background: conic-gradient(#22c55e 0deg {{ ($stats['success_rate'] ?? 95.9) * 3.6 }}deg, #e5e7eb {{ ($stats['success_rate'] ?? 95.9) * 3.6 }}deg 360deg);">
                        <div class="metric-value text-center">
                            <div class="text-3xl font-bold text-gray-900">{{ $stats['success_rate'] ?? 95.9 }}%</div>
                            <div class="text-xs text-gray-500">Réussite</div>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <div class="flex justify-between items-center p-2 bg-green-50 rounded-lg">
                        <span class="text-sm text-gray-700">Livrées</span>
                        <span class="font-bold text-green-600">{{ $stats['delivered'] ?? 142 }}</span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-red-50 rounded-lg">
                        <span class="text-sm text-gray-700">Échouées</span>
                        <span class="font-bold text-red-600">{{ $stats['failed'] ?? 6 }}</span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-700">Annulées</span>
                        <span class="font-bold text-gray-600">{{ $stats['cancelled'] ?? 2 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            
            <!-- Average Delivery Time -->
            <div class="chart-container">
                <h3 class="font-bold text-gray-900 mb-4">⏱️ Temps de livraison moyen</h3>
                
                <div class="flex items-center justify-center py-6">
                    <div class="text-center">
                        <div class="text-5xl font-bold text-blue-600">{{ $stats['avg_time'] ?? 48 }}</div>
                        <div class="text-gray-500 mt-1">minutes en moyenne</div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mt-4">
                    <div class="text-center p-3 bg-green-50 rounded-lg">
                        <div class="text-xl font-bold text-green-600">{{ $stats['min_time'] ?? 15 }}min</div>
                        <div class="text-xs text-gray-500">Plus rapide</div>
                    </div>
                    <div class="text-center p-3 bg-blue-50 rounded-lg">
                        <div class="text-xl font-bold text-blue-600">{{ $stats['avg_time'] ?? 48 }}min</div>
                        <div class="text-xs text-gray-500">Moyenne</div>
                    </div>
                    <div class="text-center p-3 bg-orange-50 rounded-lg">
                        <div class="text-xl font-bold text-orange-600">{{ $stats['max_time'] ?? 120 }}min</div>
                        <div class="text-xs text-gray-500">Plus long</div>
                    </div>
                </div>
            </div>
            
            <!-- Delivery by Type -->
            <div class="chart-container">
                <h3 class="font-bold text-gray-900 mb-4">📦 Répartition par type</h3>
                
                <div class="space-y-4">
                    @php
                        $types = [
                            ['name' => 'Standard', 'count' => $stats['standard'] ?? 98, 'color' => 'blue', 'pct' => 63],
                            ['name' => 'Express', 'count' => $stats['express'] ?? 42, 'color' => 'indigo', 'pct' => 27],
                            ['name' => 'Même jour', 'count' => $stats['same_day'] ?? 12, 'color' => 'purple', 'pct' => 8],
                            ['name' => 'Planifié', 'count' => $stats['scheduled'] ?? 4, 'color' => 'gray', 'pct' => 2],
                        ];
                    @endphp
                    
                    @foreach($types as $type)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-700">{{ $type['name'] }}</span>
                            <span class="font-medium text-gray-900">{{ $type['count'] }} ({{ $type['pct'] }}%)</span>
                        </div>
                        <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-{{ $type['color'] }}-500 rounded-full" style="width: {{ $type['pct'] }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top Performing Drivers -->
        <div class="chart-container">
            <h3 class="font-bold text-gray-900 mb-4">🏆 Top Livreurs</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="text-xs text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="text-left py-3 px-4">Livreur</th>
                            <th class="text-center py-3 px-4">Livraisons</th>
                            <th class="text-center py-3 px-4">Réussite</th>
                            <th class="text-center py-3 px-4">Temps moy.</th>
                            <th class="text-center py-3 px-4">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($topDrivers ?? [] as $driver)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span>{{ $driver->vehicle_icon ?? '🚗' }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $driver->full_name }}</p>
                                        <p class="text-xs text-gray-500">{{ ucfirst($driver->vehicle_type) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center font-medium">{{ $driver->completed_deliveries }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                    {{ $driver->success_rate ?? 98 }}%
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center text-gray-600">{{ $driver->average_delivery_time ?? 45 }} min</td>
                            <td class="py-3 px-4 text-center">
                                <span class="text-yellow-500">⭐</span>
                                {{ number_format($driver->rating ?? 4.8, 1) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">
                                <p class="text-2xl mb-2">👥</p>
                                <p>Aucun livreur pour le moment</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
