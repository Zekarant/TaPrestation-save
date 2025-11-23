@extends('layouts.admin-modern')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('administrateur.services.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Détails du service</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h4>{{ $service->title }}</h4>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary me-2">{{ $service->price }} €</span>
                            <span class="text-muted">Délai de livraison: {{ $service->delivery_time }} jours</span>
                        </div>
                        @if($service->categories->count() > 0)
                            <div class="mb-3">
                                @foreach($service->categories as $category)
                                    <span class="badge bg-info me-1">{{ $category->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="fw-bold">Description</h6>
                        <p class="text-muted">{!! nl2br(e($service->description)) !!}</p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Date de création</h6>
                            <p>{{ $service->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">Dernière mise à jour</h6>
                            <p>{{ $service->updated_at->format('d/m/Y à H:i') }}</p>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="fw-bold">Statut</h6>
                        @if(isset($service->is_visible) && $service->is_visible)
                            <span class="badge bg-success">Visible</span>
                        @else
                            <span class="badge bg-secondary">Masqué</span>
                        @endif
                    </div>
                    
                    <div class="d-flex mt-4">
                        <form action="{{ route('administrateur.services.toggleVisibility', $service->id) }}" method="POST" class="me-2">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-warning">
                                <i class="bi {{ isset($service->is_visible) && $service->is_visible ? 'bi-eye-slash' : 'bi-eye' }}"></i>
                                {{ isset($service->is_visible) && $service->is_visible ? 'Masquer' : 'Rendre visible' }}
                            </button>
                        </form>
                        <form action="{{ route('administrateur.services.destroy', $service->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce service ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Prestataire</h6>
                </div>
                <div class="card-body">
                    @if($service->prestataire && $service->prestataire->user)
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                @if($service->prestataire->photo)
                                    <img src="{{ asset('storage/' . $service->prestataire->photo) }}" alt="{{ $service->prestataire->user->name }}" class="rounded-circle" width="60" height="60">
                                @else
                                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="bi bi-person-fill fs-4"></i>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $service->prestataire->user->name }}</h6>
                                <p class="text-muted mb-0">{{ $service->prestataire->user->email }}</p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <p class="mb-1"><strong>Secteur:</strong> {{ $service->prestataire->sector }}</p>
                            <p class="mb-1"><strong>Localisation:</strong> {{ $service->prestataire->location }}</p>
                            <p class="mb-0">
                                <strong>Statut:</strong>
                                @if($service->prestataire->is_approved)
                                    <span class="badge bg-success">Approuvé</span>
                                @else
                                    <span class="badge bg-warning">En attente</span>
                                @endif
                            </p>
                        </div>
                        
                        <a href="{{ route('administrateur.prestataires.show', $service->prestataire->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-person"></i> Voir le profil
                        </a>
                    @else
                        <div class="alert alert-warning mb-0">
                            Information du prestataire non disponible.
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Statistiques</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="mb-1"><strong>Nombre de vues:</strong> {{ $service->views_count ?? 0 }}</p>
                        <p class="mb-1"><strong>Nombre de demandes:</strong> {{ $service->requests_count ?? 0 }}</p>
                        <p class="mb-0"><strong>Nombre d'offres:</strong> {{ $service->offers_count ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection