@extends('layouts.admin-modern')

@section('page-title', 'Gestion des Commandes')

@section('content')
<!-- Header Section -->
<div class="bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 rounded-2xl shadow-2xl mb-6 sm:mb-8 overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-10"></div>
    <div class="relative px-6 sm:px-8 py-8 sm:py-12">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-6">
            <div class="text-white">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-2 sm:mb-3">Gestion des Commandes</h1>
                <p class="text-blue-100 text-sm sm:text-base lg:text-lg opacity-90">Gérez toutes les commandes et transactions de votre plateforme</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                <button onclick="toggleFilters()" class="bg-white/20 hover:bg-white/30 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-xl font-medium transition-all duration-200 backdrop-blur-sm border border-white/20 hover:border-white/40 flex items-center justify-center gap-2">
                    <i class="fas fa-filter text-sm"></i>
                    <span class="text-sm sm:text-base">Afficher les filtres</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-6 sm:mb-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs sm:text-sm font-medium text-blue-600 uppercase tracking-wide">Total Commandes</div>
                    <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ $pagination['total'] ?? 0 }}</div>
                    <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                        <i class="fas fa-arrow-up mr-1"></i>
                        <span>+8% ce mois</span>
                    </div>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs sm:text-sm font-medium text-green-600 uppercase tracking-wide">Confirmées</div>
                    <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ $stats['confirmed_orders'] ?? 0 }}</div>
                    <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                        <i class="fas fa-arrow-up mr-1"></i>
                        <span>+12% ce mois</span>
                    </div>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs sm:text-sm font-medium text-orange-600 uppercase tracking-wide">En Attente</div>
                    <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ $stats['pending_orders'] ?? 0 }}</div>
                    <div class="flex items-center mt-2 text-xs sm:text-sm text-gray-600">
                        <i class="fas fa-minus mr-1"></i>
                        <span>Stable</span>
                    </div>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs sm:text-sm font-medium text-blue-600 uppercase tracking-wide">Chiffre d'Affaires</div>
                    <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">***</div>
                    <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                        <i class="fas fa-arrow-up mr-1"></i>
                        <span>+15% ce mois</span>
                    </div>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-euro-sign text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
<div class="chart-card" style="margin-bottom: 2rem;">
    <div class="chart-header">
        <div class="chart-title">Évolution du Chiffre d'Affaires</div>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <select style="padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.875rem;">
                <option>7 derniers jours</option>
                <option>30 derniers jours</option>
                <option>3 derniers mois</option>
                <option>12 derniers mois</option>
            </select>
        </div>
    </div>
    <div style="padding: 1.5rem;">
        <canvas id="revenueChart" style="max-height: 300px;"></canvas>
    </div>
</div>

<!-- Filters Panel -->
<div id="filtersPanel" class="chart-card" style="display: none; margin-bottom: 2rem;">
    <div class="chart-header">
        <div class="chart-title">Filtres de recherche</div>
        <button class="btn btn-outline" onclick="clearFilters()">
            <i class="fas fa-times"></i>
            Effacer
        </button>
    </div>
    <form action="{{ route('administrateur.orders.index') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; padding: 1rem 0;">
        <div>
            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">Numéro de commande</label>
            <input type="text" name="order_number" value="{{ request('order_number') }}" placeholder="#ORD-..." style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem;">
        </div>
        
        <div>
            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">Client</label>
            <input type="text" name="client" value="{{ request('client') }}" placeholder="Nom du client..." style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem;">
        </div>
        
        <div>
            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">Prestataire</label>
            <input type="text" name="prestataire" value="{{ request('prestataire') }}" placeholder="Nom du prestataire..." style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem;">
        </div>
        
        <div>
            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">Statut</label>
            <select name="status" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem;">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En cours</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminée</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
            </select>
        </div>
        
        <div>
            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">Date de début</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem;">
        </div>
        
        <div>
            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">Date de fin</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem;">
        </div>
        
        <div>
            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">Montant minimum</label>
            <input type="number" name="min_amount" value="{{ request('min_amount') }}" placeholder="0" step="0.01" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem;">
        </div>
        
        <div>
            <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--dark);">Montant maximum</label>
            <input type="number" name="max_amount" value="{{ request('max_amount') }}" placeholder="1000" step="0.01" style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem;">
        </div>
        
        <div style="display: flex; align-items: end; gap: 1rem;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
                Rechercher
            </button>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="chart-card">
    <div class="chart-header">
        <div class="chart-title">Liste des Commandes</div>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <select onchange="changeItemsPerPage(this.value)" style="padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.875rem;">
                <option value="10">10 par page</option>
                <option value="25">25 par page</option>
                <option value="50">50 par page</option>
                <option value="100">100 par page</option>
            </select>
        </div>
    </div>
    
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--dark);">
                        <input type="checkbox" id="selectAll" onchange="toggleAllOrders(this)">
                    </th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--dark);">Commande</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--dark);">Client</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--dark);">Prestataire</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--dark);">Service</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--dark);">Montant</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--dark);">Statut</th>
                    <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--dark);">Date</th>
                    <th style="padding: 1rem; text-align: center; font-weight: 600; color: var(--dark);">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders ?? [] as $order)
                    <tr style="border-bottom: 1px solid #e2e8f0; transition: background-color 0.2s ease;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='transparent'">
                        <td style="padding: 1rem;">
                            <input type="checkbox" name="selected_orders[]" value="{{ $order['id'] }}-{{ $order['type'] }}" class="order-checkbox">
                        </td>
                        
                        <td style="padding: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $order['type'] === 'service' ? 'var(--primary)' : 'var(--success)' }}; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.8rem;">
                                    <i class="{{ $order['type'] === 'service' ? 'fas fa-concierge-bell' : 'fas fa-tools' }}"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--primary); margin-bottom: 0.25rem;">#{{ $order['type'] === 'service' ? 'SRV' : 'EQP' }}-{{ str_pad($order['id'], 6, '0', STR_PAD_LEFT) }}</div>
                                    <div style="font-size: 0.8rem; color: var(--secondary);">{{ $order['type'] === 'service' ? 'Réservation de service' : 'Location d\'équipement' }}</div>
                                </div>
                            </div>
                        </td>
                        
                        <td style="padding: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--info); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.8rem;">
                                    {{ strtoupper(substr($order['client_name'] ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 500; color: var(--dark);">{{ $order['client_name'] ?? 'Client inconnu' }}</div>
                                    <div style="font-size: 0.8rem; color: var(--secondary);">Client</div>
                                </div>
                            </div>
                        </td>
                        
                        <td style="padding: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--warning); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.8rem;">
                                    {{ strtoupper(substr($order['prestataire_name'] ?? 'P', 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 500; color: var(--dark);">{{ $order['prestataire_name'] ?? 'Prestataire inconnu' }}</div>
                                    <div style="font-size: 0.8rem; color: var(--secondary);">Prestataire</div>
                                </div>
                            </div>
                        </td>
                        
                        <td style="padding: 1rem;">
                            <div style="font-weight: 500; color: var(--dark); margin-bottom: 0.25rem;">{{ $order['service_name'] ?? 'N/A' }}</div>
                            <div style="font-size: 0.8rem; color: var(--secondary);">
                                <span style="padding: 0.25rem 0.5rem; background: {{ $order['type'] === 'service' ? 'rgba(59, 130, 246, 0.1)' : 'rgba(16, 185, 129, 0.1)' }}; color: {{ $order['type'] === 'service' ? 'var(--primary)' : 'var(--success)' }}; border-radius: 4px; font-size: 0.75rem; font-weight: 500;">
                                    {{ $order['type'] === 'service' ? 'Service' : 'Équipement' }}
                                </span>
                            </div>
                        </td>
                        
                        <td style="padding: 1rem;">
                            <div style="font-weight: 600; color: var(--dark); font-size: 1.1rem;">{{ number_format($order['amount'] ?? 0, 2, ',', ' ') }}€</div>
                        </td>
                        
                        <td style="padding: 1rem;">
                            @php
                                $statusConfig = [
                                    'pending' => ['color' => 'var(--warning)', 'bg' => 'rgba(245, 158, 11, 0.1)', 'icon' => 'fas fa-clock', 'text' => 'En attente'],
                                    'confirmed' => ['color' => 'var(--info)', 'bg' => 'rgba(59, 130, 246, 0.1)', 'icon' => 'fas fa-check', 'text' => 'Confirmée'],
                                    'in_progress' => ['color' => 'var(--primary)', 'bg' => 'rgba(99, 102, 241, 0.1)', 'icon' => 'fas fa-play', 'text' => 'En cours'],
                                    'completed' => ['color' => 'var(--success)', 'bg' => 'rgba(16, 185, 129, 0.1)', 'icon' => 'fas fa-check-circle', 'text' => 'Terminée'],
                                    'cancelled' => ['color' => 'var(--danger)', 'bg' => 'rgba(239, 68, 68, 0.1)', 'icon' => 'fas fa-times-circle', 'text' => 'Annulée'],
                                    'accepted' => ['color' => 'var(--info)', 'bg' => 'rgba(59, 130, 246, 0.1)', 'icon' => 'fas fa-check', 'text' => 'Acceptée'],
                                    'active' => ['color' => 'var(--primary)', 'bg' => 'rgba(99, 102, 241, 0.1)', 'icon' => 'fas fa-play', 'text' => 'Active']
                                ];
                                $status = $statusConfig[$order['status'] ?? 'pending'] ?? $statusConfig['pending'];
                            @endphp
                            
                            <span style="padding: 0.375rem 0.75rem; background: {{ $status['bg'] }}; color: {{ $status['color'] }}; border-radius: 6px; font-size: 0.8rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.375rem;">
                                <i class="{{ $status['icon'] }}"></i>
                                {{ $status['text'] }}
                            </span>
                        </td>
                        
                        <td style="padding: 1rem;">
                            <div style="font-weight: 500; color: var(--dark);">{{ \Carbon\Carbon::parse($order['created_at'])->format('d/m/Y') }}</div>
                            <div style="font-size: 0.8rem; color: var(--secondary);">{{ \Carbon\Carbon::parse($order['created_at'])->format('H:i') }}</div>
                        </td>
                        
                        <td style="padding: 1rem; text-align: center;">
                            <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                <a href="{{ route('administrateur.orders.show', $order['id']) }}" class="btn btn-outline" style="padding: 0.375rem 0.75rem; font-size: 0.8rem;" title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($order['status'] === 'pending')
                                    <button onclick="confirmOrder({{ $order['id'] }})" class="btn btn-success" style="padding: 0.375rem 0.75rem; font-size: 0.8rem;" title="Confirmer">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                                
                                @if(in_array($order['status'], ['pending', 'confirmed']))
                                    <button onclick="cancelOrder({{ $order['id'] }})" class="btn btn-danger" style="padding: 0.375rem 0.75rem; font-size: 0.8rem;" title="Annuler">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                                
                                <div style="position: relative; display: inline-block;">
                                    <button onclick="toggleOrderMenu({{ $order['id'] }})" class="btn btn-outline" style="padding: 0.375rem 0.75rem; font-size: 0.8rem;" title="Plus d'actions">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div id="orderMenu{{ $order['id'] }}" style="position: absolute; top: 100%; right: 0; background: white; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); z-index: 100; min-width: 150px; display: none;">
                                        <a href="{{ route('administrateur.orders.edit', $order['id']) }}" style="display: block; padding: 0.75rem 1rem; color: var(--dark); text-decoration: none; border-bottom: 1px solid #e2e8f0;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='transparent'">
                                            <i class="fas fa-edit" style="margin-right: 0.5rem;"></i>
                                            Modifier
                                        </a>
                                        <button onclick="duplicateOrder({{ $order['id'] }})" style="width: 100%; text-align: left; padding: 0.75rem 1rem; background: none; border: none; color: var(--dark); border-bottom: 1px solid #e2e8f0;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='transparent'">
                                            <i class="fas fa-copy" style="margin-right: 0.5rem;"></i>
                                            Dupliquer
                                        </button>
                                        <button onclick="deleteOrder({{ $order['id'] }})" style="width: 100%; text-align: left; padding: 0.75rem 1rem; background: none; border: none; color: var(--danger);" onmouseover="this.style.backgroundColor='#fef2f2'" onmouseout="this.style.backgroundColor='transparent'">
                                            <i class="fas fa-trash" style="margin-right: 0.5rem;"></i>
                                            Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="padding: 3rem; text-align: center; color: var(--secondary);">
                            <i class="fas fa-shopping-cart" style="font-size: 3rem; margin-bottom: 1rem; color: #e2e8f0;"></i>
                            <div style="font-size: 1.125rem; font-weight: 500; margin-bottom: 0.5rem;">Aucune commande trouvée</div>
                            <div>Les commandes apparaîtront ici une fois créées</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($orders && $orders->hasPages())
        <div style="padding: 1.5rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: between; align-items: center;">
            <div style="color: var(--secondary); font-size: 0.875rem;">
                Affichage de {{ $orders->firstItem() }} à {{ $orders->lastItem() }} sur {{ $orders->total() }} résultats
            </div>
            <div>
                {{ $orders->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Bulk Actions -->
<div id="bulkActions" style="position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%); background: white; padding: 1rem 2rem; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); display: none; z-index: 1000;">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <span style="font-weight: 500;">Actions groupées :</span>
        <button class="btn btn-success" onclick="bulkConfirm()">
            <i class="fas fa-check"></i>
            Confirmer
        </button>
        <button class="btn btn-warning" onclick="bulkCancel()">
            <i class="fas fa-times"></i>
            Annuler
        </button>

        <button class="btn btn-outline" onclick="clearSelection()">
            <i class="fas fa-times"></i>
            Annuler
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
        datasets: [{
            label: 'Chiffre d\'affaires',
            data: [1200, 1900, 3000, 5000, 2000, 3000, 4500],
            borderColor: 'rgb(99, 102, 241)',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            tension: 0.4,
            fill: true
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
                ticks: {
                    callback: function(value) {
                        return value + '€';
                    }
                }
            }
        }
    }
});

// Toggle filters panel
function toggleFilters() {
    const panel = document.getElementById('filtersPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

// Clear filters
function clearFilters() {
    window.location.href = '{{ route("administrateur.orders.index") }}';
}

// Change items per page
function changeItemsPerPage(value) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', value);
    window.location.href = url.toString();
}

// Toggle all orders
function toggleAllOrders(checkbox) {
    const orderCheckboxes = document.querySelectorAll('.order-checkbox');
    orderCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActions();
}

// Update bulk actions visibility
function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    
    if (checkedBoxes.length > 0) {
        bulkActions.style.display = 'block';
    } else {
        bulkActions.style.display = 'none';
    }
}

// Add event listeners to checkboxes
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
});

// Clear selection
function clearSelection() {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

// Toggle order menu
function toggleOrderMenu(orderId) {
    const menu = document.getElementById('orderMenu' + orderId);
    // Close all other menus
    document.querySelectorAll('[id^="orderMenu"]').forEach(m => {
        if (m.id !== 'orderMenu' + orderId) {
            m.style.display = 'none';
        }
    });
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

// Close menus when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('[id^="orderMenu"]') && !event.target.closest('button[onclick*="toggleOrderMenu"]')) {
        document.querySelectorAll('[id^="orderMenu"]').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});

// Order actions
function confirmOrder(orderId) {
    if (confirm('Êtes-vous sûr de vouloir confirmer cette commande ?')) {
        // Implement confirm order logic
        console.log('Confirming order:', orderId);
    }
}

function cancelOrder(orderId) {
    if (confirm('Êtes-vous sûr de vouloir annuler cette commande ?')) {
        // Implement cancel order logic
        console.log('Cancelling order:', orderId);
    }
}

function duplicateOrder(orderId) {
    if (confirm('Êtes-vous sûr de vouloir dupliquer cette commande ?')) {
        // Implement duplicate order logic
        console.log('Duplicating order:', orderId);
    }
}

function deleteOrder(orderId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette commande ? Cette action est irréversible.')) {
        // Implement delete order logic
        console.log('Deleting order:', orderId);
    }
}

// Bulk actions
function bulkConfirm() {
    const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
    if (checkedBoxes.length === 0) return;
    
    if (confirm(`Êtes-vous sûr de vouloir confirmer ${checkedBoxes.length} commande(s) ?`)) {
        // Implement bulk confirm logic
        console.log('Bulk confirming orders');
    }
}

function bulkCancel() {
    const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
    if (checkedBoxes.length === 0) return;
    
    if (confirm(`Êtes-vous sûr de vouloir annuler ${checkedBoxes.length} commande(s) ?`)) {
        // Implement bulk cancel logic
        console.log('Bulk cancelling orders');
    }
}


</script>
@endpush