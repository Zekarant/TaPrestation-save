@extends('layouts.app')

@section('title', 'Gestion des Livraisons')

@push('styles')
<style>
    .logistics-page {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 100vh;
    }
    
    .filter-chip {
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s ease;
        border: 1px solid #e5e7eb;
        background: white;
        color: #6b7280;
    }
    
    .filter-chip:hover {
        border-color: #3b82f6;
        color: #3b82f6;
    }
    
    .filter-chip.active {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }
    
    .delivery-row {
        transition: all 0.2s ease;
    }
    
    .delivery-row:hover {
        background: #f8fafc;
    }
    
    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }
    
    .status-pending { background: #fbbf24; }
    .status-preparing { background: #8b5cf6; }
    .status-ready { background: #06b6d4; }
    .status-picked_up { background: #3b82f6; }
    .status-in_transit { background: #6366f1; }
    .status-out_for_delivery { background: #10b981; }
    .status-delivered { background: #22c55e; }
    .status-failed { background: #ef4444; }
    .status-cancelled { background: #9ca3af; }
    
    .priority-badge {
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .priority-urgent { background: #fef2f2; color: #dc2626; }
    .priority-high { background: #fff7ed; color: #ea580c; }
    .priority-normal { background: #f0fdf4; color: #16a34a; }
    .priority-low { background: #f1f5f9; color: #64748b; }
    
    .bulk-action-bar {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: #1e293b;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        z-index: 50;
        display: none;
    }
    
    .bulk-action-bar.show {
        display: flex;
    }
</style>
@endpush

@section('content')
<div class="logistics-page py-4 sm:py-6">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        
        {{-- Section d'aide --}}
        <x-help-section page="prestataire.logistics.index" />
        
        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <nav class="text-sm text-gray-500 mb-2">
                        <a href="{{ route('prestataire.logistics.dashboard') }}" class="hover:text-blue-600">Logistique</a>
                        <span class="mx-2">›</span>
                        <span class="text-gray-900">Toutes les livraisons</span>
                    </nav>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                        📦 Gestion des Expéditions
                    </h1>
                </div>
                <a href="{{ route('prestataire.logistics.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouvelle Expédition
                </a>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
            <div class="flex flex-col lg:flex-row gap-4">
                
                <!-- Search -->
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" 
                               id="searchInput"
                               placeholder="Rechercher par n° suivi, client, ville..." 
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="{{ request('search') }}">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                
                <!-- Status Filters -->
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('prestataire.logistics.index') }}" 
                       class="filter-chip {{ !request('status') ? 'active' : '' }}">
                        Tout
                    </a>
                    <a href="{{ route('prestataire.logistics.index', ['status' => 'pending']) }}" 
                       class="filter-chip {{ request('status') == 'pending' ? 'active' : '' }}">
                        En attente
                    </a>
                    <a href="{{ route('prestataire.logistics.index', ['status' => 'in_transit']) }}" 
                       class="filter-chip {{ request('status') == 'in_transit' ? 'active' : '' }}">
                        En transit
                    </a>
                    <a href="{{ route('prestataire.logistics.index', ['status' => 'delivered']) }}" 
                       class="filter-chip {{ request('status') == 'delivered' ? 'active' : '' }}">
                        Livrées
                    </a>
                    <a href="{{ route('prestataire.logistics.index', ['status' => 'failed']) }}" 
                       class="filter-chip {{ request('status') == 'failed' ? 'active' : '' }}">
                        Échouées
                    </a>
                </div>
                
                <!-- Date Filter -->
                <div class="flex gap-2">
                    <input type="date" 
                           name="date_from" 
                           class="px-3 py-2 border border-gray-200 rounded-lg text-sm"
                           value="{{ request('date_from') }}"
                           placeholder="Du">
                    <input type="date" 
                           name="date_to" 
                           class="px-3 py-2 border border-gray-200 rounded-lg text-sm"
                           value="{{ request('date_to') }}"
                           placeholder="Au">
                </div>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 mb-4">
            <div class="bg-white rounded-lg p-3 border border-gray-100 text-center">
                <div class="text-2xl font-bold text-gray-900">{{ $deliveries->total() ?? 0 }}</div>
                <div class="text-xs text-gray-500">Total</div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-100 text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</div>
                <div class="text-xs text-gray-500">En attente</div>
            </div>
            <div class="bg-blue-50 rounded-lg p-3 border border-blue-100 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['in_transit'] ?? 0 }}</div>
                <div class="text-xs text-gray-500">En transit</div>
            </div>
            <div class="bg-green-50 rounded-lg p-3 border border-green-100 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $stats['delivered'] ?? 0 }}</div>
                <div class="text-xs text-gray-500">Livrées</div>
            </div>
            <div class="bg-red-50 rounded-lg p-3 border border-red-100 text-center">
                <div class="text-2xl font-bold text-red-600">{{ $stats['failed'] ?? 0 }}</div>
                <div class="text-xs text-gray-500">Échouées</div>
            </div>
            <div class="bg-emerald-50 rounded-lg p-3 border border-emerald-100 text-center">
                <div class="text-2xl font-bold text-emerald-600">{{ $stats['success_rate'] ?? 100 }}%</div>
                <div class="text-xs text-gray-500">Succès</div>
            </div>
        </div>

        <!-- Deliveries Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            
            <!-- Table Header -->
            <div class="hidden lg:grid lg:grid-cols-12 gap-4 px-4 py-3 bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <div class="col-span-1">
                    <input type="checkbox" id="selectAll" class="rounded border-gray-300">
                </div>
                <div class="col-span-2">N° Suivi</div>
                <div class="col-span-2">Destinataire</div>
                <div class="col-span-2">Destination</div>
                <div class="col-span-1">Priorité</div>
                <div class="col-span-2">Statut</div>
                <div class="col-span-2">Actions</div>
            </div>
            
            <!-- Table Body -->
            <div class="divide-y divide-gray-100">
                @forelse($deliveries as $delivery)
                <div class="delivery-row p-4 lg:grid lg:grid-cols-12 lg:gap-4 lg:items-center">
                    
                    <!-- Checkbox -->
                    <div class="hidden lg:block col-span-1">
                        <input type="checkbox" name="delivery_ids[]" value="{{ $delivery->id }}" class="delivery-checkbox rounded border-gray-300">
                    </div>
                    
                    <!-- Tracking Number -->
                    <div class="col-span-2 mb-2 lg:mb-0">
                        <div class="flex items-center gap-2">
                            <span class="status-dot status-{{ $delivery->status }}"></span>
                            <a href="{{ route('prestataire.logistics.show', $delivery) }}" 
                               class="font-mono text-sm text-blue-600 hover:text-blue-800 font-medium">
                                {{ $delivery->tracking_number }}
                            </a>
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5 lg:hidden">
                            {{ $delivery->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    
                    <!-- Recipient -->
                    <div class="col-span-2 mb-2 lg:mb-0">
                        <div class="font-medium text-gray-900 text-sm">
                            {{ $delivery->delivery_contact_name ?? 'N/A' }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $delivery->delivery_contact_phone ?? '' }}
                        </div>
                    </div>
                    
                    <!-- Destination -->
                    <div class="col-span-2 mb-2 lg:mb-0">
                        <div class="text-sm text-gray-900">
                            {{ $delivery->delivery_city ?? 'Non défini' }}
                        </div>
                        <div class="text-xs text-gray-500 truncate">
                            {{ Str::limit($delivery->delivery_address, 30) }}
                        </div>
                    </div>
                    
                    <!-- Priority -->
                    <div class="col-span-1 mb-2 lg:mb-0">
                        <span class="priority-badge priority-{{ $delivery->priority ?? 'normal' }}">
                            {{ $delivery->priority_label ?? 'Normal' }}
                        </span>
                    </div>
                    
                    <!-- Status -->
                    <div class="col-span-2 mb-3 lg:mb-0">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 bg-{{ $delivery->status_color ?? 'gray' }}-100 text-{{ $delivery->status_color ?? 'gray' }}-700 text-xs font-medium rounded-full">
                                {{ $delivery->status_label ?? ucfirst($delivery->status) }}
                            </span>
                        </div>
                        @if($delivery->driver)
                        <div class="text-xs text-gray-500 mt-1">
                            🚗 {{ $delivery->driver->full_name }}
                        </div>
                        @endif
                    </div>
                    
                    <!-- Actions -->
                    <div class="col-span-2 flex gap-2">
                        <a href="{{ route('prestataire.logistics.show', $delivery) }}" 
                           class="flex-1 lg:flex-none px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded-lg transition text-center">
                            Détails
                        </a>
                        
                        @if($delivery->status === 'pending')
                        <form action="{{ route('prestataire.logistics.ready-for-pickup', $delivery) }}" method="POST" class="flex-1 lg:flex-none">
                            @csrf
                            <button type="submit" class="w-full px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition">
                                Préparer
                            </button>
                        </form>
                        @elseif($delivery->status === 'ready_for_pickup')
                        <form action="{{ route('prestataire.logistics.picked-up', $delivery) }}" method="POST" class="flex-1 lg:flex-none">
                            @csrf
                            <button type="submit" class="w-full px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition">
                                Enlèvement
                            </button>
                        </form>
                        @elseif(in_array($delivery->status, ['in_transit', 'out_for_delivery']))
                        <form action="{{ route('prestataire.logistics.delivered', $delivery) }}" method="POST" class="flex-1 lg:flex-none">
                            @csrf
                            <button type="submit" class="w-full px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition">
                                Livré ✓
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">📭</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Aucune livraison</h3>
                    <p class="text-gray-500 text-sm mb-4">Commencez par créer une nouvelle expédition</p>
                    <a href="{{ route('prestataire.logistics.create') }}" 
                       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Créer une expédition
                    </a>
                </div>
                @endforelse
            </div>
            
            <!-- Pagination -->
            @if(isset($deliveries) && $deliveries->hasPages())
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                {{ $deliveries->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Action Bar -->
<div class="bulk-action-bar" id="bulkActionBar">
    <span class="mr-4"><span id="selectedCount">0</span> sélectionnés</span>
    <div class="flex gap-2">
        <button class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition">
            Préparer
        </button>
        <button class="px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg transition">
            Exporter
        </button>
        <button class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg transition">
            Annuler
        </button>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.delivery-checkbox');
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedCount = document.getElementById('selectedCount');
    
    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });
    
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });
    
    function updateBulkBar() {
        const count = document.querySelectorAll('.delivery-checkbox:checked').length;
        selectedCount.textContent = count;
        bulkActionBar.classList.toggle('show', count > 0);
    }
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;
    
    searchInput?.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const params = new URLSearchParams(window.location.search);
            if (this.value) {
                params.set('search', this.value);
            } else {
                params.delete('search');
            }
            window.location.search = params.toString();
        }, 500);
    });
});
</script>
@endpush
@endsection
