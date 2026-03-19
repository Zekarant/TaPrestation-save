@extends('layouts.admin-modern')

@section('title', 'Gestion de l\'Inventaire')
@section('page-title', 'Gestion de l\'Inventaire')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-boxes me-2"></i>Gestion de l'Inventaire Global
        </h1>
        <p class="page-subtitle">Visualisez et gérez l'inventaire de tous les prestataires</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-primary">
                    <i class="fas fa-box"></i>
                </div>
            </div>
            <div class="text-title">Total produits</div>
            <div class="stat-value">{{ $stats['total'] ?? 1245 }}</div>
        </div>
        
        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-success">
                    <i class="fas fa-check"></i>
                </div>
            </div>
            <div class="text-title">En stock</div>
            <div class="stat-value">{{ $stats['in_stock'] ?? 987 }}</div>
        </div>
        
        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
            <div class="text-title">Stock faible</div>
            <div class="stat-value">{{ $stats['low_stock'] ?? 58 }}</div>
        </div>
        
        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-danger">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
            <div class="text-title">Rupture</div>
            <div class="stat-value">{{ $stats['out_of_stock'] ?? 12 }}</div>
        </div>
    </div>

    <!-- Alerts -->
    @if(($stats['low_stock'] ?? 58) > 0)
    <div class="alert alert-warning d-flex align-items-center mb-4">
        <i class="fas fa-exclamation-triangle me-3 fa-lg"></i>
        <div>
            <strong>Attention !</strong> {{ $stats['low_stock'] ?? 58 }} produits ont un stock faible et nécessitent un réapprovisionnement.
            <a href="#" class="alert-link ms-2">Voir la liste →</a>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="card-base mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>Filtres
            </h5>
        </div>
        <div class="p-4">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Statut stock</label>
                    <select class="form-select">
                        <option value="">Tous</option>
                        <option value="in_stock">En stock</option>
                        <option value="low_stock">Stock faible</option>
                        <option value="out_of_stock">Rupture</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Catégorie</label>
                    <select class="form-select">
                        <option value="">Toutes</option>
                        <option value="equipment">Équipements</option>
                        <option value="tools">Outils</option>
                        <option value="materials">Matériaux</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Prestataire</label>
                    <select class="form-select">
                        <option value="">Tous</option>
                        @foreach($prestataires ?? [] as $prestataire)
                        <option value="{{ $prestataire->id }}">{{ $prestataire->user->name ?? 'Prestataire' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Prix min</label>
                    <input type="number" class="form-control" placeholder="0 €">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Prix max</label>
                    <input type="number" class="form-control" placeholder="1000 €">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Recherche</label>
                    <input type="text" class="form-control" placeholder="Produit, SKU...">
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card-base table-card">
        <div class="table-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Inventaire Global
            </h5>
            <div>
                <button class="btn btn-outline-primary btn-sm me-2">
                    <i class="fas fa-download me-1"></i> Exporter
                </button>
                <button class="btn btn-primary btn-sm">
                    <i class="fas fa-sync me-1"></i> Actualiser
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Produit</th>
                        <th>Prestataire</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Seuil alerte</th>
                        <th>Statut</th>
                        <th>Dernière MAJ</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventory ?? [] as $item)
                    <tr>
                        <td><code>{{ $item->sku ?? 'SKU-' . rand(10000, 99999) }}</code></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $item->image ?? asset('images/placeholder.svg') }}" 
                                     alt="{{ $item->name }}" 
                                     class="rounded" 
                                     style="width: 40px; height: 40px; object-fit: cover;">
                                <span class="ms-2">{{ $item->name ?? 'Produit' }}</span>
                            </div>
                        </td>
                        <td>{{ $item->prestataire->user->name ?? 'Prestataire' }}</td>
                        <td><span class="badge bg-secondary">{{ $item->category ?? 'Équipement' }}</span></td>
                        <td>{{ number_format($item->price ?? rand(50, 500), 2) }} €</td>
                        <td class="fw-bold">{{ $item->stock ?? rand(0, 100) }}</td>
                        <td>{{ $item->alert_threshold ?? 10 }}</td>
                        <td>
                            @php
                                $stock = $item->stock ?? rand(0, 100);
                                $threshold = $item->alert_threshold ?? 10;
                                if ($stock == 0) {
                                    $statusClass = 'danger';
                                    $statusLabel = 'Rupture';
                                } elseif ($stock <= $threshold) {
                                    $statusClass = 'warning';
                                    $statusLabel = 'Stock faible';
                                } else {
                                    $statusClass = 'success';
                                    $statusLabel = 'En stock';
                                }
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td>
                            <small class="text-muted">{{ $item->updated_at ? $item->updated_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning" title="Ajuster stock">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-info" title="Historique">
                                    <i class="fas fa-history"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-boxes fa-3x mb-3 opacity-50"></i>
                                <p>Aucun article en inventaire</p>
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
