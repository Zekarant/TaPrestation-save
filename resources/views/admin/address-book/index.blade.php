@extends('layouts.admin-modern')

@section('title', 'Carnet d\'Adresses Global')
@section('page-title', 'Carnet d\'Adresses Global')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-address-book me-2"></i>Carnet d'Adresses Global
        </h1>
        <p class="page-subtitle">Gérez toutes les adresses de livraison et facturation des utilisateurs</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-primary">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
            </div>
            <div class="text-title">Total adresses</div>
            <div class="stat-value">{{ $stats['total'] ?? 2456 }}</div>
        </div>
        
        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="text-title">Adresses vérifiées</div>
            <div class="stat-value">{{ $stats['verified'] ?? 2189 }}</div>
        </div>
        
        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-info">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="text-title">Utilisateurs avec adresse</div>
            <div class="stat-value">{{ $stats['users_with_address'] ?? 1823 }}</div>
        </div>
        
        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-warning">
                    <i class="fas fa-globe-europe"></i>
                </div>
            </div>
            <div class="text-title">Pays couverts</div>
            <div class="stat-value">{{ $stats['countries'] ?? 5 }}</div>
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
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select class="form-select">
                        <option value="">Tous les types</option>
                        <option value="shipping">Livraison</option>
                        <option value="billing">Facturation</option>
                        <option value="both">Les deux</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Rôle utilisateur</label>
                    <select class="form-select">
                        <option value="">Tous les rôles</option>
                        <option value="client">Clients</option>
                        <option value="prestataire">Prestataires</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Pays</label>
                    <select class="form-select">
                        <option value="">Tous les pays</option>
                        <option value="FR">France</option>
                        <option value="BE">Belgique</option>
                        <option value="CH">Suisse</option>
                        <option value="LU">Luxembourg</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Ville</label>
                    <input type="text" class="form-control" placeholder="Nom de la ville">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Code postal</label>
                    <input type="text" class="form-control" placeholder="Code postal">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Recherche</label>
                    <input type="text" class="form-control" placeholder="Utilisateur, adresse...">
                </div>
            </div>
        </div>
    </div>

    <!-- Addresses Table -->
    <div class="card-base table-card">
        <div class="table-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Liste des Adresses
            </h5>
            <button class="btn btn-outline-primary btn-sm">
                <i class="fas fa-download me-1"></i> Exporter
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th>Rôle</th>
                        <th>Type</th>
                        <th>Adresse</th>
                        <th>Ville</th>
                        <th>Code postal</th>
                        <th>Pays</th>
                        <th>Par défaut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($addresses ?? [] as $address)
                    <tr>
                        <td>#{{ $address->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    {{ strtoupper(substr($address->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <span>{{ $address->user->name ?? 'Utilisateur' }}</span>
                            </div>
                        </td>
                        <td>
                            @php
                                $role = $address->user->role ?? 'client';
                                $roleClass = $role === 'prestataire' ? 'info' : 'secondary';
                            @endphp
                            <span class="badge bg-{{ $roleClass }}">{{ ucfirst($role) }}</span>
                        </td>
                        <td>
                            @php
                                $type = $address->type ?? 'shipping';
                                $typeLabel = $type === 'shipping' ? 'Livraison' : ($type === 'billing' ? 'Facturation' : 'Les deux');
                            @endphp
                            <span class="badge bg-outline-primary">{{ $typeLabel }}</span>
                        </td>
                        <td>{{ Str::limit($address->street ?? '123 Rue Example', 30) }}</td>
                        <td>{{ $address->city ?? 'Paris' }}</td>
                        <td>{{ $address->postal_code ?? '75001' }}</td>
                        <td>{{ $address->country ?? 'France' }}</td>
                        <td>
                            @if($address->is_default ?? false)
                                <span class="badge bg-success"><i class="fas fa-star me-1"></i>Oui</span>
                            @else
                                <span class="badge bg-secondary">Non</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-info" title="Vérifier">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-address-book fa-3x mb-3 opacity-50"></i>
                                <p>Aucune adresse trouvée</p>
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
