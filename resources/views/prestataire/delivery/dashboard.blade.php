@extends('layouts.app')

@section('title', 'Tableau de Bord Logistique')

@push('styles')
<style>
    .logistics-dashboard {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        min-height: 100vh;
    }
    
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.25rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid rgba(59, 130, 246, 0.1);
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
    }
    
    .delivery-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
    }
    
    .delivery-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .priority-urgent { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .priority-high { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
    .priority-normal { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
    .priority-low { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
    
    .progress-ring {
        transform: rotate(-90deg);
    }
    
    .live-indicator {
        width: 8px;
        height: 8px;
        background: #22c55e;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.2); }
    }
    
    .map-placeholder {
        background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
        border-radius: 12px;
        min-height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@section('content')
<div class="logistics-dashboard py-4 sm:py-6">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        
        {{-- Section d'aide --}}
        <x-help-section page="prestataire.logistics.dashboard" />
        
        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center gap-2">
                        <span class="text-3xl">🚚</span>
                        Centre de Logistique
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">Gérez vos expéditions en temps réel</p>
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <a href="{{ route('prestataire.logistics.create') }}" 
                       class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="hidden sm:inline">Nouvelle Expédition</span>
                        <span class="sm:hidden">Nouvelle</span>
                    </a>
                    <a href="{{ route('prestataire.logistics.index') }}" 
                       class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg font-medium transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        <span class="hidden sm:inline">Tout voir</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 mb-6">
            <div class="stat-card">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <span class="text-lg">📦</span>
                    </div>
                </div>
                <div class="stat-value text-yellow-600">{{ $stats['pending'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-1">En attente</div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <span class="text-lg">⚙️</span>
                    </div>
                </div>
                <div class="stat-value text-indigo-600">{{ $stats['preparing'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-1">Préparation</div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <span class="text-lg">🚚</span>
                    </div>
                    <div class="live-indicator"></div>
                </div>
                <div class="stat-value text-blue-600">{{ $stats['in_transit'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-1">En transit</div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <span class="text-lg">✅</span>
                    </div>
                </div>
                <div class="stat-value text-green-600">{{ $stats['delivered_today'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-1">Livrées aujourd'hui</div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <span class="text-lg">📊</span>
                    </div>
                </div>
                <div class="stat-value text-emerald-600">{{ $stats['success_rate'] ?? 100 }}%</div>
                <div class="text-xs text-gray-500 mt-1">Taux de succès</div>
            </div>
            
            <div class="stat-card">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <span class="text-lg">💰</span>
                    </div>
                </div>
                <div class="stat-value text-purple-600 text-xl">{{ number_format($stats['total_revenue'] ?? 0, 0, ',', ' ') }}€</div>
                <div class="text-xs text-gray-500 mt-1">Ce mois</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            
            <!-- Left Column: Pending & Active -->
            <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                
                <!-- Actions Urgentes -->
                @if(isset($pendingDeliveries) && $pendingDeliveries->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-4 py-3 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-amber-100">
                        <h2 class="font-bold text-gray-900 flex items-center gap-2">
                            <span class="text-xl">⚡</span>
                            Actions Requises
                            <span class="bg-amber-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $pendingDeliveries->count() }}</span>
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach($pendingDeliveries->take(5) as $delivery)
                        <div class="p-4 hover:bg-gray-50 transition">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-mono text-sm text-blue-600">{{ $delivery->tracking_number }}</span>
                                        <span class="status-badge priority-{{ $delivery->priority ?? 'normal' }}">
                                            {{ $delivery->priority_label ?? 'Normal' }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 truncate">
                                        {{ $delivery->delivery_contact_name ?? $delivery->booking?->client?->user?->name ?? 'Client' }}
                                        • {{ $delivery->delivery_city ?? 'Ville' }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        Créée {{ $delivery->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <form action="{{ route('prestataire.logistics.ready-for-pickup', $delivery) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition">
                                            Préparer
                                        </button>
                                    </form>
                                    <a href="{{ route('prestataire.logistics.show', $delivery) }}" 
                                       class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded-lg transition">
                                        Détails
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($pendingDeliveries->count() > 5)
                    <div class="px-4 py-3 bg-gray-50 border-t">
                        <a href="{{ route('prestataire.logistics.index', ['status' => 'pending']) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Voir tout ({{ $pendingDeliveries->count() }}) →
                        </a>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Livraisons en cours -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-100">
                        <h2 class="font-bold text-gray-900 flex items-center gap-2">
                            <span class="text-xl">🚚</span>
                            En Transit
                            <div class="live-indicator ml-1"></div>
                            @if(isset($activeDeliveries))
                            <span class="bg-blue-500 text-white text-xs px-2 py-0.5 rounded-full ml-1">{{ $activeDeliveries->count() }}</span>
                            @endif
                        </h2>
                    </div>
                    
                    @if(isset($activeDeliveries) && $activeDeliveries->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($activeDeliveries as $delivery)
                        <div class="p-4 hover:bg-gray-50 transition">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-lg">{{ $delivery->driver?->vehicle_icon ?? '📦' }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-mono text-sm text-blue-600">{{ $delivery->tracking_number }}</span>
                                        <span class="px-2 py-0.5 bg-{{ $delivery->status_color }}-100 text-{{ $delivery->status_color }}-700 text-xs rounded-full font-medium">
                                            {{ $delivery->status_label }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        {{ $delivery->driver?->full_name ?? 'Livreur non assigné' }}
                                        → {{ $delivery->delivery_city ?? 'Destination' }}
                                    </p>
                                    
                                    <!-- Progress Bar -->
                                    <div class="mt-2">
                                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                                            <span>Progression</span>
                                            <span>{{ $delivery->progress_percentage }}%</span>
                                        </div>
                                        <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-blue-500 rounded-full transition-all duration-500"
                                                 style="width: {{ $delivery->progress_percentage }}%"></div>
                                        </div>
                                    </div>
                                    
                                    @if($delivery->estimated_delivery)
                                    <p class="text-xs text-gray-400 mt-2">
                                        🕐 ETA: {{ $delivery->estimated_delivery->format('H:i') }}
                                    </p>
                                    @endif
                                </div>
                                <a href="{{ route('prestataire.logistics.show', $delivery) }}" 
                                   class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded-lg transition">
                                    Suivre
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="p-8 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <span class="text-3xl">🚚</span>
                        </div>
                        <p class="text-gray-500">Aucune livraison en cours</p>
                    </div>
                    @endif
                </div>

                <!-- Performance Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="text-lg">📈</span>
                        Performance (7 derniers jours)
                    </h3>
                    <div class="h-48 flex items-end justify-between gap-2">
                        @foreach($performanceData ?? [] as $day)
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="w-full bg-blue-100 rounded-t-lg transition-all duration-500 hover:bg-blue-200"
                                 style="height: {{ max(10, ($day['count'] ?? 0) * 20) }}px"
                                 title="{{ $day['count'] ?? 0 }} livraisons">
                            </div>
                            <span class="text-xs text-gray-500">{{ $day['day'] ?? '' }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Column: Today & Quick Actions -->
            <div class="space-y-4 sm:space-y-6">
                
                <!-- Livraisons du jour -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-4 py-3 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-green-100">
                        <h2 class="font-bold text-gray-900 flex items-center gap-2">
                            <span class="text-xl">📅</span>
                            Aujourd'hui
                        </h2>
                        <p class="text-sm text-gray-600">{{ now()->isoFormat('dddd D MMMM') }}</p>
                    </div>
                    
                    @if(isset($todayDeliveries) && $todayDeliveries->count() > 0)
                    <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                        @foreach($todayDeliveries as $delivery)
                        <a href="{{ route('prestataire.logistics.show', $delivery) }}" 
                           class="block p-3 hover:bg-gray-50 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-{{ $delivery->status_color }}-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-sm">{{ $delivery->driver?->vehicle_icon ?? '📦' }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $delivery->delivery_contact_name ?? 'Destinataire' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $delivery->scheduled_delivery_at?->format('H:i') ?? '--:--' }} • {{ $delivery->delivery_city ?? '' }}
                                    </p>
                                </div>
                                <span class="px-2 py-0.5 bg-{{ $delivery->status_color }}-100 text-{{ $delivery->status_color }}-700 text-xs rounded-full">
                                    {{ $delivery->status_label }}
                                </span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @else
                    <div class="p-6 text-center">
                        <span class="text-3xl">📭</span>
                        <p class="text-sm text-gray-500 mt-2">Aucune livraison prévue</p>
                    </div>
                    @endif
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <h3 class="font-bold text-gray-900 mb-3">⚡ Actions rapides</h3>
                    <div class="space-y-2">
                        <a href="{{ route('prestataire.logistics.create') }}" 
                           class="w-full flex items-center gap-3 p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                            <span class="text-xl">📦</span>
                            <span class="text-sm font-medium text-blue-800">Créer une expédition</span>
                        </a>
                        <a href="{{ route('prestataire.logistics.index') }}" 
                           class="w-full flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                            <span class="text-xl">📋</span>
                            <span class="text-sm font-medium text-gray-700">Toutes les livraisons</span>
                        </a>
                        <a href="{{ route('prestataire.logistics.reports') }}" 
                           class="w-full flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                            <span class="text-xl">📊</span>
                            <span class="text-sm font-medium text-gray-700">Rapports & Analytics</span>
                        </a>
                        <a href="{{ route('prestataire.logistics.drivers') }}" 
                           class="w-full flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                            <span class="text-xl">🚗</span>
                            <span class="text-sm font-medium text-gray-700">Gérer les livreurs</span>
                        </a>
                    </div>
                </div>

                <!-- Stats Summary -->
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl p-4 text-white">
                    <h3 class="font-bold mb-3">📊 Ce mois</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-blue-100 text-sm">Livrées</span>
                            <span class="font-bold text-lg">{{ $stats['delivered_this_month'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-blue-100 text-sm">Temps moyen</span>
                            <span class="font-bold">{{ $stats['avg_delivery_time'] ?? 0 }} min</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-blue-100 text-sm">Échecs</span>
                            <span class="font-bold">{{ $stats['failed'] ?? 0 }}</span>
                        </div>
                        <div class="border-t border-blue-500 pt-3 mt-3">
                            <div class="flex justify-between items-center">
                                <span class="text-blue-100 text-sm">Revenus livraison</span>
                                <span class="font-bold text-xl">{{ number_format($stats['total_revenue'] ?? 0, 0, ',', ' ') }}€</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
