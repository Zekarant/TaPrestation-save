@extends('layouts.admin-modern')

@section('title', 'Fournisseurs de Livraison')
@section('page-title', 'Fournisseurs de Livraison')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-shipping-fast me-2"></i>Fournisseurs de Livraison
        </h1>
        <p class="page-subtitle">Gérez les transporteurs et fournisseurs de livraison de la plateforme</p>
    </div>

    @if(isset($tableNotExists) && $tableNotExists)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            La table des fournisseurs n'existe pas encore. Exécutez la migration <code>create_delivery_providers_table</code>.
        </div>
    @endif

    <!-- Add Provider -->
    <div class="card-base mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="fas fa-plus me-2"></i>Ajouter un fournisseur</h5>
        </div>
        <div class="p-4">
            <form method="POST" action="{{ route('admin.delivery.store-provider') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ex: Colissimo">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Clé API</label>
                        <input type="text" name="api_key" class="form-control" placeholder="Clé API (optionnel)">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Prix/km (€)</label>
                        <input type="number" name="price_per_km" class="form-control" step="0.01" min="0" value="0.50">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">URL API</label>
                        <input type="url" name="base_url" class="form-control" placeholder="https://...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-1"></i>Ajouter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Providers List -->
    <div class="card-base table-card">
        <div class="table-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Liste des fournisseurs ({{ count($providers ?? []) }})
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prix/km</th>
                        <th>URL API</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($providers ?? [] as $provider)
                    <tr>
                        <td><strong>{{ $provider->name }}</strong></td>
                        <td>{{ number_format($provider->price_per_km ?? 0, 2) }}€/km</td>
                        <td><small class="text-muted">{{ $provider->base_url ?? '—' }}</small></td>
                        <td>
                            @if($provider->is_active ?? false)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <form method="POST" action="{{ route('admin.delivery.update-provider', $provider) }}">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="is_active" value="{{ ($provider->is_active ?? false) ? '0' : '1' }}">
                                    <input type="hidden" name="name" value="{{ $provider->name }}">
                                    <button type="submit" class="btn btn-sm {{ ($provider->is_active ?? false) ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ ($provider->is_active ?? false) ? 'Désactiver' : 'Activer' }}">
                                        <i class="fas {{ ($provider->is_active ?? false) ? 'fa-pause' : 'fa-play' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.delivery.destroy-provider', $provider) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ce fournisseur ?')" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-shipping-fast fa-3x mb-3 opacity-50"></i>
                                <p>Aucun fournisseur configuré</p>
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
