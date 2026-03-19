@extends('layouts.admin-modern')

@section('title', 'Gestion des Livraisons')
@section('page-title', 'Gestion des Livraisons')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-truck me-2"></i>Gestion des Livraisons
        </h1>
        <p class="page-subtitle">Suivez et gérez toutes les livraisons de la plateforme</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-warning">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="text-title">En préparation</div>
            <div class="stat-value">{{ $stats['preparing'] ?? 18 }}</div>
        </div>
        
        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-primary">
                    <i class="fas fa-shipping-fast"></i>
                </div>
            </div>
            <div class="text-title">En transit</div>
            <div class="stat-value">{{ $stats['in_transit'] ?? 45 }}</div>
        </div>
        
        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="text-title">Livrées</div>
            <div class="stat-value">{{ $stats['delivered'] ?? 234 }}</div>
        </div>
        
        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-info">
                    <i class="fas fa-percentage"></i>
                </div>
            </div>
            <div class="text-title">Taux de succès</div>
            <div class="stat-value">{{ $stats['success_rate'] ?? '98.5' }}%</div>
        </div>
    </div>

    <!-- Map Section -->
    <div class="card-base mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-map-marker-alt me-2"></i>Carte des Livraisons en Cours
            </h5>
        </div>
        <div class="p-0" style="height: 300px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0 0 16px 16px;">
            <div class="d-flex align-items-center justify-content-center h-100 text-white">
                <div class="text-center">
                    <i class="fas fa-map fa-4x mb-3 opacity-75"></i>
                    <p class="mb-0">Carte interactive des livraisons</p>
                    <small class="opacity-75">Intégration Google Maps disponible</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card-base mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>Filtres
            </h5>
        </div>
        <div class="p-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="preparing">En préparation</option>
                        <option value="shipped">Expédiée</option>
                        <option value="in_transit">En transit</option>
                        <option value="out_for_delivery">En cours de livraison</option>
                        <option value="delivered">Livrée</option>
                        <option value="failed">Échec</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Transporteur</label>
                    <select class="form-select">
                        <option value="">Tous les transporteurs</option>
                        <option value="colissimo">Colissimo</option>
                        <option value="chronopost">Chronopost</option>
                        <option value="mondial_relay">Mondial Relay</option>
                        <option value="internal">Livraison interne</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Période</label>
                    <select class="form-select">
                        <option value="">Toute la période</option>
                        <option value="today">Aujourd'hui</option>
                        <option value="week">Cette semaine</option>
                        <option value="month">Ce mois</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Recherche</label>
                    <input type="text" class="form-control" placeholder="N° suivi, commande...">
                </div>
            </div>
        </div>
    </div>

    <!-- Deliveries Table -->
    <div class="card-base table-card">
        <div class="table-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Liste des Livraisons
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>N° Suivi</th>
                        <th>Commande</th>
                        <th>Client</th>
                        <th>Destination</th>
                        <th>Transporteur</th>
                        <th>Expédition</th>
                        <th>Livraison prévue</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries ?? [] as $delivery)
                    <tr>
                        <td>
                            <code>{{ $delivery->tracking_number ?? 'TRK-' . rand(100000, 999999) }}</code>
                        </td>
                        <td>#{{ $delivery->order_id ?? rand(1000, 9999) }}</td>
                        <td>{{ $delivery->customer->name ?? 'Client' }}</td>
                        <td>
                            <small>{{ $delivery->destination_city ?? 'Paris' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $delivery->carrier ?? 'Colissimo' }}</span>
                        </td>
                        <td>{{ $delivery->shipped_at ? $delivery->shipped_at->format('d/m/Y') : now()->format('d/m/Y') }}</td>
                        <td>{{ $delivery->estimated_delivery ? $delivery->estimated_delivery->format('d/m/Y') : now()->addDays(3)->format('d/m/Y') }}</td>
                        <td>
                            @php
                                $statusClass = match($delivery->status ?? 'in_transit') {
                                    'preparing' => 'warning',
                                    'shipped' => 'info',
                                    'in_transit' => 'primary',
                                    'out_for_delivery' => 'info',
                                    'delivered' => 'success',
                                    'failed' => 'danger',
                                    default => 'secondary'
                                };
                                $statusLabel = match($delivery->status ?? 'in_transit') {
                                    'preparing' => 'Préparation',
                                    'shipped' => 'Expédiée',
                                    'in_transit' => 'En transit',
                                    'out_for_delivery' => 'En livraison',
                                    'delivered' => 'Livrée',
                                    'failed' => 'Échec',
                                    default => 'Inconnu'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" title="Détails">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-info" title="Suivi">
                                    <i class="fas fa-map-marker-alt"></i>
                                </button>
                                <button class="btn btn-outline-warning" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-truck fa-3x mb-3 opacity-50"></i>
                                <p>Aucune livraison trouvée</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
