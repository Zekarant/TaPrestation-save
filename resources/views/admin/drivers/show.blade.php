@extends('layouts.admin-modern')

@section('title', 'Livreur - ' . ($driver->first_name ?? '') . ' ' . ($driver->last_name ?? ''))
@section('page-title', 'Détail du Livreur')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('admin.drivers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour à la liste
        </a>
    </div>

    <!-- Driver Header -->
    <div class="card-base mb-4">
        <div class="p-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="avatar-lg bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width:64px;height:64px;font-size:1.5rem;">
                        {{ strtoupper(substr($driver->first_name ?? 'L', 0, 1)) }}{{ strtoupper(substr($driver->last_name ?? '', 0, 1)) }}
                    </div>
                </div>
                <div class="col">
                    <h3 class="mb-1">{{ $driver->first_name }} {{ $driver->last_name }}</h3>
                    <div class="d-flex gap-3 text-muted">
                        <span><i class="fas fa-envelope me-1"></i>{{ $driver->user->email ?? '—' }}</span>
                        <span><i class="fas fa-phone me-1"></i>{{ $driver->phone ?? '—' }}</span>
                        @php
                            $vehicleIcons = ['bicycle' => 'fa-bicycle', 'scooter' => 'fa-motorcycle', 'car' => 'fa-car', 'van' => 'fa-shuttle-van'];
                        @endphp
                        <span><i class="fas {{ $vehicleIcons[$driver->vehicle_type] ?? 'fa-question' }} me-1"></i>{{ ucfirst($driver->vehicle_type ?? '—') }}</span>
                    </div>
                </div>
                <div class="col-auto">
                    @if($driver->is_suspended)
                        <span class="badge bg-danger fs-6">Suspendu</span>
                    @elseif($driver->is_active)
                        <span class="badge bg-success fs-6">Actif</span>
                    @else
                        <span class="badge bg-warning fs-6">En attente d'approbation</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Stats -->
        <div class="col-md-4">
            <div class="card-base mb-4">
                <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Statistiques</h5></div>
                <div class="p-4">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Note</span>
                            <strong><span class="text-warning">★</span> {{ number_format($driver->rating ?? 0, 1) }}/5</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Livraisons totales</span>
                            <strong>{{ $driver->total_deliveries ?? 0 }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Livraisons ce mois</span>
                            <strong>{{ $monthlyStats['deliveries'] ?? 0 }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Gains ce mois</span>
                            <strong class="text-success">{{ number_format($monthlyStats['earnings'] ?? 0, 2) }}€</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Temps moyen</span>
                            <strong>{{ $monthlyStats['avg_time'] ?? 0 }} min</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Inscrit le</span>
                            <strong>{{ $driver->created_at?->format('d/m/Y') ?? '—' }}</strong>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Stripe Status -->
            <div class="card-base mb-4">
                <div class="card-header"><h5 class="card-title mb-0"><i class="fab fa-stripe me-2"></i>Stripe Connect</h5></div>
                <div class="p-4">
                    @if($driver->stripe_onboarding_complete)
                        <div class="alert alert-success mb-2">
                            <i class="fas fa-check-circle me-1"></i>Compte Stripe vérifié
                        </div>
                        <small class="text-muted">ID: {{ $driver->stripe_account_id }}</small>
                    @elseif($driver->stripe_account_id)
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i>Onboarding Stripe incomplet
                        </div>
                    @else
                        <div class="alert alert-secondary mb-0">
                            <i class="fas fa-info-circle me-1"></i>Stripe non configuré
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sponsor -->
            @if($driver->sponsor_prestataire_id)
            <div class="card-base mb-4">
                <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-handshake me-2"></i>Parrainage</h5></div>
                <div class="p-4">
                    <p><strong>Parrainé par :</strong> {{ $driver->sponsorPrestataire->business_name ?? $driver->sponsorPrestataire->user->name ?? 'Prestataire #' . $driver->sponsor_prestataire_id }}</p>
                    <p class="mb-0"><strong>Depuis :</strong> {{ $driver->sponsored_at ? \Carbon\Carbon::parse($driver->sponsored_at)->format('d/m/Y') : '—' }}</p>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="card-base mb-4">
                <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5></div>
                <div class="p-4 d-grid gap-2">
                    @if(!$driver->is_active && !$driver->is_suspended)
                        <form method="POST" action="{{ route('admin.drivers.approve', $driver) }}">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Approuver ce livreur ?')">
                                <i class="fas fa-check me-1"></i>Approuver
                            </button>
                        </form>
                    @endif

                    @if($driver->is_active && !$driver->is_suspended)
                        <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#suspendModal">
                            <i class="fas fa-pause me-1"></i>Suspendre
                        </button>
                    @endif

                    @if($driver->is_suspended)
                        <form method="POST" action="{{ route('admin.drivers.reactivate', $driver) }}">
                            @csrf
                            <button type="submit" class="btn btn-info w-100 text-white" onclick="return confirm('Réactiver ce livreur ?')">
                                <i class="fas fa-play me-1"></i>Réactiver
                            </button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('admin.drivers.destroy', $driver) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce livreur ? Cette action est irréversible.')">
                            <i class="fas fa-trash me-1"></i>Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="col-md-8">
            <div class="card-base">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Dernières livraisons</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Commande</th>
                                <th>Client</th>
                                <th>Prestataire</th>
                                <th>Total</th>
                                <th>Commission</th>
                                <th>Statut</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                            <tr>
                                <td><code>#{{ $order->order_number ?? $order->id }}</code></td>
                                <td>{{ $order->client->name ?? '—' }}</td>
                                <td>{{ $order->prestataire->business_name ?? $order->prestataire->user->name ?? '—' }}</td>
                                <td>{{ number_format($order->total ?? 0, 2) }}€</td>
                                <td class="text-success">{{ number_format($order->driver_commission ?? 0, 2) }}€</td>
                                <td>
                                    @php
                                        $dsClass = match($order->delivery_status ?? 'pending') {
                                            'delivered' => 'success',
                                            'in_transit' => 'primary',
                                            'picked_up' => 'info',
                                            'assigned' => 'warning',
                                            'failed' => 'danger',
                                            default => 'secondary'
                                        };
                                        $dsLabel = match($order->delivery_status ?? 'pending') {
                                            'delivered' => 'Livrée',
                                            'in_transit' => 'En route',
                                            'picked_up' => 'Récupérée',
                                            'assigned' => 'Assignée',
                                            'failed' => 'Échouée',
                                            default => 'En attente'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $dsClass }}">{{ $dsLabel }}</span>
                                </td>
                                <td>{{ $order->created_at?->format('d/m H:i') ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Aucune livraison</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Suspend Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1">
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
                    <textarea name="reason" class="form-control" rows="3" required placeholder="Indiquez la raison de la suspension..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">Suspendre</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
