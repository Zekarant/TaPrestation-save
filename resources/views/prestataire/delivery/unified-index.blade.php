@extends('layouts.app')

@section('title', 'Hub Livraisons - Toutes vos expéditions')

@push('styles')
<style>
    .delivery-hub {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 100vh;
    }
    
    /* Type badges */
    .type-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .type-food_internal { background: #fff7ed; color: #c2410c; }
    .type-food_external { background: #fef3c7; color: #b45309; }
    .type-urgent_sale { background: #fef2f2; color: #dc2626; }
    .type-service { background: #eff6ff; color: #2563eb; }
    .type-equipment { background: #faf5ff; color: #7c3aed; }
    
    /* Filter tabs */
    .filter-tab {
        padding: 0.24rem 0.45rem;
        border-radius: 9999px;
        font-size: 0.6rem;
        font-weight: 500;
        transition: all 0.2s;
        border: 1px solid #e5e7eb;
        background: white;
        color: #6b7280;
        text-decoration: none;
        line-height: 1.1;
    }
    .filter-tab:hover { border-color: #3b82f6; color: #3b82f6; }
    .filter-tab.active { background: #3b82f6; color: white; border-color: #3b82f6; }
    @media (min-width: 640px) {
        .filter-tab {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            line-height: 1.2;
        }
    }

    .mini-stats-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.25rem;
    }
    .mini-stat {
        border-radius: 8px;
        padding: 0.2rem;
        text-align: center;
        aspect-ratio: 1 / 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .mini-stat-value {
        font-size: 0.96rem;
        line-height: 1;
        font-weight: 700;
    }
    .mini-stat-label {
        margin-top: 0.08rem;
        font-size: 0.54rem;
        line-height: 1.05;
        color: #6b7280;
    }
    @media (max-width: 639px) {
        .mini-stat-label {
            display: block;
            font-size: 0.54rem;
            line-height: 1;
            margin-top: 0.06rem;
            white-space: nowrap;
        }
    }
    @media (min-width: 640px) {
        .mini-stats-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.5rem;
        }
        .mini-stat {
            border-radius: 10px;
            padding: 0.5rem;
            aspect-ratio: auto;
        }
        .mini-stat-value { font-size: 0.9rem; }
        .mini-stat-label { font-size: 0.68rem; line-height: 1.1; }
    }
    @media (min-width: 1024px) {
        .mini-stats-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
    }
    
    /* Status chips */
    .status-chip {
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .status-yellow { background: #fef3c7; color: #b45309; }
    .status-orange { background: #ffedd5; color: #c2410c; }
    .status-blue { background: #dbeafe; color: #1d4ed8; }
    .status-cyan { background: #cffafe; color: #0891b2; }
    .status-indigo { background: #e0e7ff; color: #4338ca; }
    .status-green { background: #dcfce7; color: #16a34a; }
    .status-red { background: #fee2e2; color: #dc2626; }
    .status-gray { background: #f3f4f6; color: #6b7280; }
    
    /* Delivery card */
    .delivery-card {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        border: 1px solid #e5e7eb;
        transition: all 0.2s;
    }
    .delivery-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    }
    
    /* Stats cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.25rem;
    }
    @media (min-width: 640px) {
        .stats-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.75rem;
        }
    }
    .stat-card {
        background: white;
        border-radius: 7px;
        padding: 0.2rem;
        text-align: center;
        border: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        aspect-ratio: 1 / 1;
    }
    @media (min-width: 640px) {
        .stat-card {
            border-radius: 12px;
            padding: 0.8rem;
            aspect-ratio: auto;
            min-height: 92px;
        }
    }
    .stat-card .value {
        font-size: 1.08rem;
        line-height: 1;
        font-weight: 700;
    }
    .stat-card .label {
        margin-top: 0.08rem;
        font-size: 0.56rem;
        line-height: 1.05;
        color: #6b7280;
    }
    @media (max-width: 639px) {
        .stat-card .label {
            display: block;
            font-size: 0.56rem;
            line-height: 1;
            margin-top: 0.06rem;
            white-space: nowrap;
        }
    }
    @media (min-width: 640px) {
        .stat-card .value { font-size: 1.4rem; }
        .stat-card .label {
            margin-top: 0.3rem;
            font-size: 0.68rem;
            line-height: 1.15;
        }
    }
    
    /* Action buttons */
    .btn-action {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }
    .btn-view { background: #f3f4f6; color: #374151; }
    .btn-view:hover { background: #e5e7eb; }
    .btn-start { background: #3b82f6; color: white; }
    .btn-start:hover { background: #2563eb; }
    .btn-complete { background: #10b981; color: white; }
    .btn-complete:hover { background: #059669; }
    .btn-call { background: #8b5cf6; color: white; }
    .btn-call:hover { background: #7c3aed; }
</style>
@endpush

@section('content')
<div class="delivery-hub py-4 sm:py-6">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center gap-3">
                        🚚 Hub Livraisons
                    </h1>
                    <p class="text-gray-500 mt-1">Gérez toutes vos livraisons depuis un seul endroit</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('prestataire.food-orders.dashboard') }}" class="px-4 py-2 bg-orange-100 text-orange-700 rounded-lg text-sm font-medium hover:bg-orange-200 transition">
                        🍔 Food Dashboard
                    </a>
                </div>
            </div>
        </div>
        
        {{-- Stats Overview --}}
        <div class="stats-grid mb-6">
            <div class="stat-card bg-yellow-50 border-yellow-100">
                <div class="value text-yellow-600">{{ $stats['total_pending'] }}</div>
                <div class="label"><span class="sm:hidden">À liv.</span><span class="hidden sm:inline">À livrer</span></div>
            </div>
            <div class="stat-card bg-blue-50 border-blue-100">
                <div class="value text-blue-600">{{ $stats['total_in_progress'] }}</div>
                <div class="label"><span class="sm:hidden">Cours</span><span class="hidden sm:inline">En cours</span></div>
            </div>
            <div class="stat-card bg-green-50 border-green-100">
                <div class="value text-green-600">{{ $stats['total_completed'] }}</div>
                <div class="label"><span class="sm:hidden">Livr.</span><span class="hidden sm:inline">Livrées</span></div>
            </div>
            <div class="stat-card">
                <div class="value text-gray-900">{{ $allDeliveries->count() }}</div>
                <div class="label"><span class="sm:hidden">Aff.</span><span class="hidden sm:inline">Affichées</span></div>
            </div>
        </div>
        
        {{-- Type Filter --}}
        <div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-gray-100 p-1.5 sm:p-4 mb-3 sm:mb-4">
            <div class="flex flex-col lg:flex-row gap-1.5 sm:gap-4">
                {{-- Type filters --}}
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <span class="hidden sm:inline text-sm text-gray-500 self-center mr-2">Type:</span>
                    <a href="{{ route('prestataire.delivery.index', ['type' => 'all', 'status' => $statusFilter]) }}" 
                       class="filter-tab {{ $typeFilter === 'all' ? 'active' : '' }}">
                        Tout
                    </a>
                    <a href="{{ route('prestataire.delivery.index', ['type' => 'food_internal', 'status' => $statusFilter]) }}" 
                       class="filter-tab {{ $typeFilter === 'food_internal' ? 'active' : '' }}">
                        <span class="sm:hidden">🍔 Vous</span>
                        <span class="hidden sm:inline">🍔 Food (Vous)</span>
                    </a>
                    <a href="{{ route('prestataire.delivery.index', ['type' => 'food_external', 'status' => $statusFilter]) }}" 
                       class="filter-tab {{ $typeFilter === 'food_external' ? 'active' : '' }}">
                        <span class="sm:hidden">🍔 Livr.</span>
                        <span class="hidden sm:inline">🍔 Food (Livreur)</span>
                    </a>
                    <a href="{{ route('prestataire.delivery.index', ['type' => 'urgent_sale', 'status' => $statusFilter]) }}" 
                       class="filter-tab {{ $typeFilter === 'urgent_sale' ? 'active' : '' }}">
                        🏷️ Ventes
                    </a>
                    <a href="{{ route('prestataire.delivery.index', ['type' => 'service', 'status' => $statusFilter]) }}" 
                       class="filter-tab {{ $typeFilter === 'service' ? 'active' : '' }}">
                        🛠️ Services
                    </a>
                    <a href="{{ route('prestataire.delivery.index', ['type' => 'equipment', 'status' => $statusFilter]) }}" 
                       class="filter-tab {{ $typeFilter === 'equipment' ? 'active' : '' }}">
                        🔧 Équipements
                    </a>
                </div>
                
                {{-- Status filters --}}
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 lg:ml-auto">
                    <span class="hidden sm:inline text-sm text-gray-500 self-center mr-2">Statut:</span>
                    <a href="{{ route('prestataire.delivery.index', ['type' => $typeFilter, 'status' => 'pending']) }}" 
                       class="filter-tab {{ $statusFilter === 'pending' ? 'active' : '' }}">
                        <span class="sm:hidden">À liv.</span>
                        <span class="hidden sm:inline">À livrer</span>
                    </a>
                    <a href="{{ route('prestataire.delivery.index', ['type' => $typeFilter, 'status' => 'in_progress']) }}" 
                       class="filter-tab {{ $statusFilter === 'in_progress' ? 'active' : '' }}">
                        <span class="sm:hidden">Cours</span>
                        <span class="hidden sm:inline">En cours</span>
                    </a>
                    <a href="{{ route('prestataire.delivery.index', ['type' => $typeFilter, 'status' => 'completed']) }}" 
                       class="filter-tab {{ $statusFilter === 'completed' ? 'active' : '' }}">
                        <span class="sm:hidden">Term.</span>
                        <span class="hidden sm:inline">Terminées</span>
                    </a>
                </div>
            </div>
        </div>
        
        {{-- Mini stats by type --}}
        <div class="mini-stats-grid mb-3 sm:mb-4">
            <div class="mini-stat bg-orange-50">
                <div class="mini-stat-value text-orange-600">{{ $stats['food_pending'] }}</div>
                <div class="mini-stat-label"><span class="sm:hidden">Food</span><span class="hidden sm:inline">Food en attente</span></div>
            </div>
            <div class="mini-stat bg-red-50">
                <div class="mini-stat-value text-red-600">{{ $stats['urgent_pending'] }}</div>
                <div class="mini-stat-label"><span class="sm:hidden">Urg.</span><span class="hidden sm:inline">Ventes urgentes</span></div>
            </div>
            <div class="mini-stat bg-blue-50">
                <div class="mini-stat-value text-blue-600">{{ $stats['bookings_pending'] }}</div>
                <div class="mini-stat-label">Services</div>
            </div>
            <div class="mini-stat bg-purple-50">
                <div class="mini-stat-value text-purple-600">{{ $stats['equipment_pending'] }}</div>
                <div class="mini-stat-label"><span class="sm:hidden">Équip.</span><span class="hidden sm:inline">Équipements</span></div>
            </div>
            <div class="mini-stat bg-green-50">
                <div class="mini-stat-value text-green-600">{{ $stats['total_completed'] }}</div>
                <div class="mini-stat-label"><span class="sm:hidden">OK</span><span class="hidden sm:inline">Complétées</span></div>
            </div>
        </div>
        
        {{-- Deliveries List --}}
        <div class="space-y-3">
            @forelse($allDeliveries as $delivery)
            <div class="delivery-card">
                <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                    
                    {{-- Type & Reference --}}
                    <div class="flex items-center gap-3 lg:w-48">
                        <span class="type-badge type-{{ $delivery['type'] }}">
                            {{ $delivery['type_label'] }}
                        </span>
                    </div>
                    
                    {{-- Reference & Items --}}
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-mono text-sm font-medium text-gray-900">{{ $delivery['reference'] }}</span>
                            <span class="status-chip status-{{ $delivery['status_color'] }}">
                                {{ $delivery['status_label'] }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ $delivery['items_count'] }}x {{ Str::limit($delivery['items_preview'], 40) }}
                        </div>
                    </div>
                    
                    {{-- Client --}}
                    <div class="lg:w-48">
                        <div class="font-medium text-gray-900 text-sm">{{ $delivery['client_name'] }}</div>
                        @if($delivery['client_phone'])
                        <div class="text-xs text-gray-500">{{ $delivery['client_phone'] }}</div>
                        @endif
                        @if($delivery['address'])
                        <div class="text-xs text-gray-400 truncate">{{ Str::limit($delivery['address'], 30) }}</div>
                        @endif
                    </div>
                    
                    {{-- Amount --}}
                    <div class="lg:w-24 text-right">
                        <div class="font-bold text-gray-900">{{ number_format($delivery['amount'], 2) }}€</div>
                        <div class="text-xs text-gray-400">{{ $delivery['created_at']->format('d/m H:i') }}</div>
                    </div>
                    
                    {{-- Driver (if external) --}}
                    @if($delivery['driver_name'])
                    <div class="lg:w-32">
                        <div class="text-xs text-gray-500">Livreur:</div>
                        <div class="text-sm font-medium text-indigo-600">🚗 {{ $delivery['driver_name'] }}</div>
                    </div>
                    @endif
                    
                    {{-- Actions --}}
                    <div class="flex gap-2 lg:w-auto">
                        <a href="{{ $delivery['show_route'] }}" class="btn-action btn-view">
                            Voir
                        </a>
                        
                        @if($delivery['client_phone'])
                        <a href="tel:{{ $delivery['client_phone'] }}" class="btn-action btn-call">
                            📞
                        </a>
                        @endif
                        
                        @if(in_array($delivery['type'], ['food_internal']) && in_array($delivery['status'], ['ready', 'preparing', 'accepted']))
                        <form action="{{ route('prestataire.delivery.start', ['type' => $delivery['type'], 'id' => $delivery['id']]) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn-action btn-start">
                                🚗 Partir
                            </button>
                        </form>
                        @endif
                        
                        @if(in_array($delivery['status'], ['in_transit', 'confirmed']) && $delivery['type'] !== 'food_external')
                        <form action="{{ route('prestataire.delivery.complete', ['type' => $delivery['type'], 'id' => $delivery['id']]) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn-action btn-complete">
                                ✓ Livrée
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl p-12 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-4xl">📭</span>
                </div>
                <h3 class="font-semibold text-gray-900 text-lg mb-2">Aucune livraison</h3>
                <p class="text-gray-500 mb-4">
                    @if($statusFilter === 'pending')
                        Vous n'avez pas de livraisons en attente
                    @elseif($statusFilter === 'in_progress')
                        Aucune livraison en cours
                    @else
                        Aucune livraison terminée
                    @endif
                </p>
                <div class="flex justify-center gap-3">
                    <a href="{{ route('prestataire.food-orders.dashboard') }}" class="px-4 py-2 bg-orange-500 text-white rounded-lg font-medium hover:bg-orange-600 transition">
                        🍔 Voir les commandes Food
                    </a>
                    <a href="{{ route('prestataire.reservations.index') }}" class="px-4 py-2 bg-red-500 text-white rounded-lg font-medium hover:bg-red-600 transition">
                        🏷️ Voir les ventes urgentes
                    </a>
                </div>
            </div>
            @endforelse
        </div>
        
        {{-- Legend --}}
        <div class="mt-6 bg-white rounded-xl p-4 border border-gray-100">
            <h4 class="font-semibold text-gray-700 mb-3">Légende des types</h4>
            <div class="flex flex-wrap gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <span class="type-badge type-food_internal">🍔 Food (Vous)</span>
                    <span class="text-gray-500">Vous livrez vous-même</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="type-badge type-food_external">🍔 Food (Livreur)</span>
                    <span class="text-gray-500">Livreur partenaire assigné</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="type-badge type-urgent_sale">🏷️ Vente Urgente</span>
                    <span class="text-gray-500">Vente urgente réservée</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="type-badge type-service">🛠️ Service</span>
                    <span class="text-gray-500">Prestation à effectuer</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="type-badge type-equipment">🔧 Équipement</span>
                    <span class="text-gray-500">Location à livrer</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
