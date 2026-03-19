@extends('layouts.admin-modern')

@section('title', 'Gestion des Livreurs')
@section('page-title', 'Gestion des Livreurs')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-motorcycle me-2"></i>Gestion des Livreurs
        </h1>
        <p class="page-subtitle">Gérez les livreurs inscrits sur la plateforme</p>
    </div>

    @if(isset($tableNotExists) && $tableNotExists)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            La table des livreurs n'existe pas encore. Exécutez les migrations.
        </div>
    @else

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-primary">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="text-title">Total livreurs</div>
            <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
        </div>

        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="text-title">Actifs</div>
            <div class="stat-value">{{ $stats['active'] ?? 0 }}</div>
        </div>

        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-warning">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="text-title">En attente</div>
            <div class="stat-value">{{ $stats['pending'] ?? 0 }}</div>
        </div>

        <div class="card-base stat-card">
            <div class="stat-header">
                <div class="icon-base variant-danger">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
            <div class="text-title">Suspendus</div>
            <div class="stat-value">{{ $stats['suspended'] ?? 0 }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card-base mb-4">
        <div class="p-4">
            <form method="GET" action="{{ route('admin.drivers.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Recherche</label>
                    <input type="text" name="search" class="form-control" placeholder="Nom, email, téléphone..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actifs</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendus</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactifs</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Filtrer
                    </button>
                </div>
                @if(request('search') || request('status'))
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('admin.drivers.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-1"></i>Reset
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Drivers Table -->
    <div class="card-base table-card">
        <div class="table-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Liste des Livreurs ({{ $drivers->total() }})
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Livreur</th>
                        <th>Contact</th>
                        <th>Véhicule</th>
                        <th>Note</th>
                        <th>Livraisons</th>
                        <th>Statut</th>
                        <th>Stripe</th>
                        <th>Inscrit le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($drivers as $driver)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;">
                                    {{ strtoupper(substr($driver->first_name ?? 'L', 0, 1)) }}
                                </div>
                                <div>
                                    <strong>{{ $driver->first_name }} {{ $driver->last_name }}</strong>
                                    @if($driver->sponsor_prestataire_id)
                                        <br><small class="text-muted"><i class="fas fa-handshake"></i> Parrainé</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <small>{{ $driver->user->email ?? '—' }}</small><br>
                            <small>{{ $driver->phone ?? '—' }}</small>
                        </td>
                        <td>
                            @php
                                $vehicleIcons = ['bicycle' => 'fa-bicycle', 'scooter' => 'fa-motorcycle', 'car' => 'fa-car', 'van' => 'fa-shuttle-van'];
                            @endphp
                            <i class="fas {{ $vehicleIcons[$driver->vehicle_type] ?? 'fa-question' }} me-1"></i>
                            {{ ucfirst($driver->vehicle_type ?? '—') }}
                        </td>
                        <td>
                            <span class="text-warning">★</span> {{ number_format($driver->rating ?? 0, 1) }}
                        </td>
                        <td>{{ $driver->total_deliveries ?? 0 }}</td>
                        <td>
                            @if($driver->is_suspended)
                                <span class="badge bg-danger">Suspendu</span>
                            @elseif($driver->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-warning">En attente</span>
                            @endif
                        </td>
                        <td>
                            @if($driver->stripe_onboarding_complete)
                                <span class="badge bg-success"><i class="fab fa-stripe-s"></i> OK</span>
                            @elseif($driver->stripe_account_id)
                                <span class="badge bg-warning"><i class="fab fa-stripe-s"></i> Incomplet</span>
                            @else
                                <span class="badge bg-secondary">Non configuré</span>
                            @endif
                        </td>
                        <td>{{ $driver->created_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.drivers.show', $driver) }}" class="btn btn-outline-primary" title="Détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(!$driver->is_active && !$driver->is_suspended)
                                    <form method="POST" action="{{ route('admin.drivers.approve', $driver) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Approuver"
                                                onclick="return confirm('Approuver ce livreur ?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($driver->is_active && !$driver->is_suspended)
                                    <button type="button" class="btn btn-outline-warning" title="Suspendre"
                                            data-bs-toggle="modal" data-bs-target="#suspendModal{{ $driver->id }}">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                @endif
                                @if($driver->is_suspended)
                                    <form method="POST" action="{{ route('admin.drivers.reactivate', $driver) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-info" title="Réactiver"
                                                onclick="return confirm('Réactiver ce livreur ?')">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <!-- Suspend Modal -->
                            <div class="modal fade" id="suspendModal{{ $driver->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('admin.drivers.suspend', $driver) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Suspendre {{ $driver->first_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <label class="form-label">Raison de la suspension *</label>
                                                <textarea name="reason" class="form-control" rows="3" required
                                                          placeholder="Indiquez la raison..."></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-warning">Suspendre</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-motorcycle fa-3x mb-3 opacity-50"></i>
                                <p>Aucun livreur trouvé</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($drivers->hasPages())
        <div class="p-3">
            {{ $drivers->links() }}
        </div>
        @endif
    </div>

    @endif
</div>
@endsection
