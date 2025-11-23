@extends('layouts.admin-modern')

@section('title', 'Détails de la vente urgente')

@section('content')
<div class="admin-content">
    <div class="content-header">
        <div class="header-left">
            <div class="breadcrumb">
                <a href="{{ route('admin.announcements.index') }}" class="breadcrumb-link">
                    <i class="fas fa-bullhorn"></i> Ventes Urgentes
                </a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">{{ Str::limit($urgentSale->title, 50) }}</span>
            </div>
            <h1 class="page-title">
                Détails de la vente urgente

            </h1>
        </div>
        <div class="header-right">
            <div class="action-buttons">
                <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
                
                @if($urgentSale->status === 'active')
                    <button type="button" class="btn btn-warning" onclick="suspendSale({{ $urgentSale->id }})">
                        <i class="fas fa-pause"></i> Suspendre
                    </button>
                @elseif($urgentSale->status === 'suspended')
                    <form method="POST" action="{{ route('admin.announcements.reactivate', $urgentSale) }}" style="display: inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-play"></i> Réactiver
                        </button>
                    </form>
                @endif
                
                <button type="button" class="btn btn-danger" onclick="deleteSale({{ $urgentSale->id }})">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </div>
        </div>
    </div>

    <div class="detail-container">
        <div class="detail-main">
            <!-- Informations principales -->
            <div class="detail-card">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle"></i> Informations principales</h3>
                </div>
                <div class="card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Titre</label>
                            <div class="detail-value">
                                {{ $urgentSale->title }}

                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <label>Prix</label>
                            <div class="detail-value price">{{ number_format($urgentSale->price, 0, ',', ' ') }} €</div>
                        </div>
                        
                        <div class="detail-item">
                            <label>Statut</label>
                            <div class="detail-value">
                                <span class="status-badge status-{{ $urgentSale->status }}">
                                    @switch($urgentSale->status)
                                        @case('active')
                                            <i class="fas fa-check-circle"></i> Actif
                                            @break
                                        @case('inactive')
                                            <i class="fas fa-pause-circle"></i> Inactif
                                            @break
                                        @case('sold')
                                            <i class="fas fa-check"></i> Vendu
                                            @break
                                        @case('suspended')
                                            <i class="fas fa-ban"></i> Suspendu
                                            @break
                                        @default
                                            {{ ucfirst($urgentSale->status) }}
                                    @endswitch
                                </span>
                            </div>
                        </div>
                        
                        @if($urgentSale->category)
                        <div class="detail-item">
                            <label>Catégorie</label>
                            <div class="detail-value">
                                <span class="category-badge">{{ $urgentSale->category->name }}</span>
                            </div>
                        </div>
                        @endif
                        
                        <div class="detail-item">
                            <label>Localisation</label>
                            <div class="detail-value">
                                <i class="fas fa-map-marker-alt"></i> {{ $urgentSale->location }}
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <label>Date de création</label>
                            <div class="detail-value">
                                {{ $urgentSale->created_at->format('d/m/Y à H:i') }}
                                <small>({{ $urgentSale->created_at->diffForHumans() }})</small>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <label>Dernière modification</label>
                            <div class="detail-value">
                                {{ $urgentSale->updated_at->format('d/m/Y à H:i') }}
                                <small>({{ $urgentSale->updated_at->diffForHumans() }})</small>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <label>Nombre de vues</label>
                            <div class="detail-value">
                                <i class="fas fa-eye"></i> {{ $urgentSale->views_count ?? 0 }} vues
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="detail-card">
                <div class="card-header">
                    <h3><i class="fas fa-align-left"></i> Description</h3>
                </div>
                <div class="card-body">
                    <div class="description-content">
                        {!! nl2br(e($urgentSale->description)) !!}
                    </div>
                </div>
            </div>

            <!-- Photos -->
            @if($urgentSale->photos && is_array($urgentSale->photos) && count($urgentSale->photos) > 0)
            <div class="detail-card">
                <div class="card-header">
                    <h3><i class="fas fa-images"></i> Photos ({{ is_array($urgentSale->photos) ? count($urgentSale->photos) : 0 }})</h3>
                </div>
                <div class="card-body">
                    <div class="photos-gallery">
                        @foreach($urgentSale->photos as $photo)
                            <div class="photo-item">
                                <img src="{{ Storage::url($photo) }}" alt="Photo de {{ $urgentSale->title }}" onclick="openPhotoModal(this.src)">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Historique des actions -->
            @if($urgentSale->admin_actions && count($urgentSale->admin_actions) > 0)
            <div class="detail-card">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> Historique des actions administratives</h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($urgentSale->admin_actions as $action)
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <strong>{{ $action->action }}</strong>
                                        <span class="timeline-date">{{ $action->created_at->format('d/m/Y à H:i') }}</span>
                                    </div>
                                    @if($action->reason)
                                        <div class="timeline-reason">{{ $action->reason }}</div>
                                    @endif
                                    <div class="timeline-admin">Par: {{ $action->admin->name }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="detail-sidebar">
            <!-- Informations du prestataire -->
            <div class="sidebar-card">
                <div class="card-header">
                    <h4><i class="fas fa-user"></i> Prestataire</h4>
                </div>
                <div class="card-body">
                    <div class="user-profile">
                        @if($urgentSale->prestataire->user->avatar)
                            <img src="{{ Storage::url($urgentSale->prestataire->user->avatar) }}" alt="{{ $urgentSale->prestataire->user->name }}" class="user-avatar">
                        @else
                            <div class="user-avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                        <div class="user-info">
                            <h5>{{ $urgentSale->prestataire->user->name }}</h5>
                            <p>{{ $urgentSale->prestataire->user->email }}</p>
                            @if($urgentSale->prestataire->user->phone)
                                <p><i class="fas fa-phone"></i> {{ $urgentSale->prestataire->user->phone }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="user-stats">
                        <div class="stat-item">
                            <span class="stat-label">Membre depuis</span>
                            <span class="stat-value">{{ $urgentSale->prestataire->user->created_at->format('m/Y') }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Ventes urgentes</span>
                            <span class="stat-value">{{ $urgentSale->prestataire->urgent_sales_count ?? 0 }}</span>
                        </div>
                    </div>
                    
                    <div class="user-actions">
                        <a href="mailto:{{ $urgentSale->prestataire->user->email }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-envelope"></i> Contacter
                        </a>
                        <a href="{{ route('administrateur.users.show', $urgentSale->prestataire->user) }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-eye"></i> Voir profil
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="sidebar-card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-bar"></i> Statistiques</h4>
                </div>
                <div class="card-body">
                    <div class="stats-list">
                        <div class="stat-item">
                            <span class="stat-label">Vues totales</span>
                            <span class="stat-value">{{ $urgentSale->views_count ?? 0 }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Contacts reçus</span>
                            <span class="stat-value">{{ $urgentSale->contacts_count ?? 0 }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Signalements</span>
                            <span class="stat-value">{{ $urgentSale->reports_count ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="sidebar-card">
                <div class="card-header">
                    <h4><i class="fas fa-bolt"></i> Actions rapides</h4>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        @if($urgentSale->status === 'active')
                            <button type="button" class="btn btn-warning btn-block" onclick="suspendSale({{ $urgentSale->id }})">
                                <i class="fas fa-pause"></i> Suspendre
                            </button>
                        @elseif($urgentSale->status === 'suspended')
                            <form method="POST" action="{{ route('admin.announcements.reactivate', $urgentSale) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-play"></i> Réactiver
                                </button>
                            </form>
                        @endif
                        
                        <button type="button" class="btn btn-danger btn-block" onclick="deleteSale({{ $urgentSale->id }})">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                        
                        <a href="{{ route('urgent-sales.show', $urgentSale) }}" target="_blank" class="btn btn-info btn-block">
                            <i class="fas fa-external-link-alt"></i> Voir sur le site
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de suspension -->
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Suspendre la vente urgente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="suspendForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="reason">Raison de la suspension *</label>
                        <textarea name="reason" id="reason" class="form-control" rows="3" required placeholder="Expliquez pourquoi cette vente est suspendue..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">Suspendre</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Supprimer la vente urgente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer définitivement cette vente urgente ?</p>
                <p class="text-danger"><strong>Cette action est irréversible.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer définitivement</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de visualisation des photos -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalPhoto" src="" alt="Photo" class="img-fluid">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function suspendSale(saleId) {
    const form = document.getElementById('suspendForm');
    form.action = `/admin/announcements/${saleId}/suspend`;
    new bootstrap.Modal(document.getElementById('suspendModal')).show();
}

function deleteSale(saleId) {
    const form = document.getElementById('deleteForm');
    form.action = `/admin/announcements/${saleId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function openPhotoModal(src) {
    document.getElementById('modalPhoto').src = src;
    new bootstrap.Modal(document.getElementById('photoModal')).show();
}
</script>
@endpush

@push('styles')
<style>
.urgent-badge {
    background: #ff6b6b;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    margin-left: 8px;
}

.detail-container {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 24px;
    margin-top: 24px;
}

.detail-card, .sidebar-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 24px;
}

.card-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.card-header h3, .card-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #495057;
}

.card-body {
    padding: 20px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
}

.detail-item label {
    display: block;
    font-weight: 600;
    color: #6c757d;
    font-size: 12px;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.detail-value {
    font-size: 14px;
    color: #495057;
}

.detail-value.price {
    font-size: 18px;
    font-weight: 600;
    color: #28a745;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.status-sold {
    background: #d1ecf1;
    color: #0c5460;
}

.status-suspended {
    background: #fff3cd;
    color: #856404;
}

.category-badge {
    background: #e9ecef;
    color: #495057;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
}

.description-content {
    line-height: 1.6;
    color: #495057;
}

.photos-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
}

.photo-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s;
}

.photo-item img:hover {
    transform: scale(1.05);
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 5px;
    width: 12px;
    height: 12px;
    background: #007bff;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.timeline-date {
    font-size: 12px;
    color: #6c757d;
}

.timeline-reason {
    font-style: italic;
    color: #6c757d;
    margin-bottom: 4px;
}

.timeline-admin {
    font-size: 12px;
    color: #6c757d;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.user-avatar-placeholder {
    width: 50px;
    height: 50px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
}

.user-info h5 {
    margin: 0 0 4px 0;
    font-size: 14px;
    font-weight: 600;
}

.user-info p {
    margin: 0;
    font-size: 12px;
    color: #6c757d;
}

.user-stats, .stats-list {
    margin-bottom: 16px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f8f9fa;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    font-size: 12px;
    color: #6c757d;
}

.stat-value {
    font-weight: 600;
    color: #495057;
}

.user-actions, .quick-actions {
    display: flex;
    gap: 8px;
}

.quick-actions {
    flex-direction: column;
}

.btn-block {
    width: 100%;
    margin-bottom: 8px;
}

@media (max-width: 768px) {
    .detail-container {
        grid-template-columns: 1fr;
    }
    
    .detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush