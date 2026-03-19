@extends('layouts.admin-modern')

@section('title', 'Gestion des Enchères')
@section('page-title', 'Gestion des Enchères')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-gavel me-2"></i>Gestion des Enchères
        </h1>
        <p class="page-subtitle">Supervisez et gérez toutes les enchères de la plateforme</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-primary">
                    <i class="fas fa-gavel"></i>
                </div>
            </div>
            <div class="text-title">Enchères actives</div>
            <div class="stat-value">{{ $stats['active'] ?? 24 }}</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> +8% cette semaine
            </div>
        </div>
        
        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="text-title">Enchères terminées</div>
            <div class="stat-value">{{ $stats['completed'] ?? 156 }}</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> +12% ce mois
            </div>
        </div>
        
        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-warning">
                    <i class="fas fa-euro-sign"></i>
                </div>
            </div>
            <div class="text-title">Volume total</div>
            <div class="stat-value">{{ number_format($stats['volume'] ?? 45600, 0, ',', ' ') }} €</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> +15%
            </div>
        </div>
        
        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-info">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="text-title">Participants actifs</div>
            <div class="stat-value">{{ $stats['participants'] ?? 89 }}</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> +5%
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
                        <option value="active">Active</option>
                        <option value="pending">En attente</option>
                        <option value="completed">Terminée</option>
                        <option value="cancelled">Annulée</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Catégorie</label>
                    <select class="form-select">
                        <option value="">Toutes les catégories</option>
                        <option value="equipment">Équipements</option>
                        <option value="services">Services</option>
                        <option value="products">Produits</option>
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
                    <input type="text" class="form-control" placeholder="ID, titre, vendeur...">
                </div>
            </div>
        </div>
    </div>

    <!-- Auctions Table -->
    <div class="card-base table-card">
        <div class="table-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Liste des Enchères
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Article</th>
                        <th>Vendeur</th>
                        <th>Prix de départ</th>
                        <th>Enchère actuelle</th>
                        <th>Enchérisseurs</th>
                        <th>Fin</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auctions ?? [] as $auction)
                    <tr>
                        <td>#{{ $auction->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $auction->image ?? asset('images/placeholder.svg') }}" 
                                     alt="{{ $auction->title }}" 
                                     class="rounded" 
                                     style="width: 40px; height: 40px; object-fit: cover;">
                                <span class="ms-2">{{ $auction->title }}</span>
                            </div>
                        </td>
                        <td>{{ $auction->seller->name ?? 'N/A' }}</td>
                        <td>{{ number_format($auction->starting_price ?? 0, 2) }} €</td>
                        <td class="text-success fw-bold">{{ number_format($auction->current_bid ?? 0, 2) }} €</td>
                        <td>{{ $auction->bidders_count ?? 0 }}</td>
                        <td>{{ $auction->ends_at ? $auction->ends_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        <td>
                            @php
                                $statusClass = match($auction->status ?? 'pending') {
                                    'active' => 'success',
                                    'completed' => 'info',
                                    'cancelled' => 'danger',
                                    default => 'warning'
                                };
                                $statusLabel = match($auction->status ?? 'pending') {
                                    'active' => 'Active',
                                    'completed' => 'Terminée',
                                    'cancelled' => 'Annulée',
                                    default => 'En attente'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-danger" title="Suspendre">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-gavel fa-3x mb-3 opacity-50"></i>
                                <p>Aucune enchère trouvée</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($auctions) && $auctions->hasPages())
        <div class="p-3 border-top">
            {{ $auctions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
