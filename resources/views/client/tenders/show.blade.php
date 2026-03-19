@extends('layouts.client')

@php
    $storageUrl = function($path) {
        return storage_asset_url($path, 'images/placeholder.svg');
    };
@endphp

@section('title', $tender->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tender-show.css') }}">
<style>
/* Corrections CSS inline pour l'affichage mobile */
.tender-show-container {
    max-width: 100%;
    padding: 1rem;
    overflow-x: hidden;
}

/* Empêcher le débordement du texte */
.content-card,
.description-content,
.tender-title,
.info-value,
.card-title {
    word-wrap: break-word;
    overflow-wrap: break-word;
    word-break: break-word;
}

.description-content {
    white-space: pre-wrap;
    max-width: 100%;
}

.tender-header-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
}

.tender-header-card .tender-title {
    color: white;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.tender-header-card .meta-item {
    color: rgba(255,255,255,0.9);
}

.tender-header-card .meta-item i {
    color: rgba(255,255,255,0.8);
}

.tender-header-card .category-tag {
    background: rgba(255,255,255,0.2);
    color: white;
    backdrop-filter: blur(10px);
}

.status-badge.published {
    background: rgba(255,255,255,0.25);
    color: white;
    backdrop-filter: blur(10px);
}

.tender-content-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.content-card {
    background: white;
    border-radius: 16px;
    padding: 1.25rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border: 1px solid #f0f0f0;
}

.card-title {
    font-size: 1.1rem;
    color: #1a1a2e;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-title i {
    color: #667eea;
}

/* Photos Gallery - CORRIGÉ */
.media-gallery {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
}

.media-thumb {
    aspect-ratio: 1;
    border-radius: 12px;
    overflow: hidden;
    background: #f5f5f5;
    cursor: pointer;
    transition: transform 0.2s;
}

.media-thumb:hover {
    transform: scale(1.02);
}

.media-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Empty state */
.empty-responses {
    text-align: center;
    padding: 2rem;
    color: #64748b;
}

.empty-responses i {
    font-size: 3rem;
    color: #cbd5e1;
    margin-bottom: 1rem;
}

.empty-responses h4 {
    color: #475569;
    margin-bottom: 0.5rem;
}

/* Sidebar */
.tender-sidebar .content-card {
    background: #f8fafc;
}

.info-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-list li {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e2e8f0;
}

.info-list li:last-child {
    border-bottom: none;
}

.info-label {
    color: #64748b;
    font-size: 0.9rem;
}

.info-value {
    color: #1e293b;
    font-weight: 500;
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}

.modal-content {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    max-width: 400px;
    width: 100%;
}

.modal-content h3 {
    margin-bottom: 1rem;
}

.modal-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}

.btn-secondary {
    background: #f1f5f9;
    color: #475569;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-outline-danger {
    background: rgba(255,255,255,0.2);
    border: 2px solid rgba(255,255,255,0.5);
    color: white;
    backdrop-filter: blur(10px);
}

.btn-outline-danger:hover {
    background: rgba(255,255,255,0.3);
}

/* Sidebar Cards */
.sidebar-card {
    background: white;
    border-radius: 16px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

.sidebar-card h3 {
    font-size: 1rem;
    color: #1e293b;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stats-list {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
}

.stat-label {
    font-size: 0.75rem;
    color: #64748b;
}

/* Responses filter */
.responses-filter {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.responses-filter button {
    padding: 0.5rem 1rem;
    border: none;
    background: #f1f5f9;
    border-radius: 20px;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
}

.responses-filter button.active {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.count-badge {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 0.125rem 0.5rem;
    border-radius: 10px;
    font-size: 0.75rem;
    margin-left: 0.5rem;
}

@media (min-width: 768px) {
    .tender-content-grid {
        display: grid;
        grid-template-columns: 1fr 300px;
    }
    
    .media-gallery {
        grid-template-columns: repeat(4, 1fr);
    }
}
</style>
@endpush

@section('content')
<div class="tender-show-container" x-data="{ showCancelModal: false, rejectingResponse: null, expandedResponse: null, filter: 'all' }">
    
    {{-- Breadcrumb --}}
    <nav class="breadcrumb">
        <a href="{{ route('client.tenders.index') }}">
            <i class="fas fa-arrow-left"></i>
            Mes appels d'offres
        </a>
    </nav>

    {{-- En-tête --}}
    <div class="tender-header-card">
        <div class="header-top">
            <div class="status-badges">
                <span class="status-badge {{ $tender->status }}">
                    {{ $tender->status_label }}
                </span>
                @if($tender->urgency !== 'normal')
                    <span class="urgency-badge {{ $tender->urgency }}">
                        @if($tender->urgency === 'urgent')
                            <i class="fas fa-exclamation-triangle"></i> Urgent
                        @elseif($tender->urgency === 'high')
                            <i class="fas fa-bolt"></i> Prioritaire
                        @else
                            <i class="fas fa-clock"></i> Pas pressé
                        @endif
                    </span>
                @endif
            </div>

            @if(in_array($tender->status, ['published', 'in_progress']))
                <div class="header-actions">
                    <button @click="showCancelModal = true" class="btn btn-outline-danger">
                        <i class="fas fa-times"></i>
                        Annuler
                    </button>
                </div>
            @endif
        </div>

        <h1 class="tender-title">{{ $tender->title }}</h1>

        <div class="tender-meta-row">
            <div class="meta-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>{{ $tender->city }}</span>
            </div>
            <div class="meta-item">
                <i class="fas fa-calendar"></i>
                <span>{{ $tender->start_date ? $tender->start_date->format('d/m/Y') : 'Date flexible' }}</span>
            </div>
            <div class="meta-item">
                <i class="fas fa-euro-sign"></i>
                <span>{{ $tender->budget_display }}</span>
            </div>
            <div class="meta-item">
                <i class="fas fa-hourglass-half"></i>
                <span>Expire {{ $tender->expires_at ? $tender->expires_at->diffForHumans() : 'jamais' }}</span>
            </div>
        </div>

        <div class="tender-categories">
            @foreach($tender->categories as $category)
                <span class="category-tag">
                    <i class="{{ $category->icon ?? 'fas fa-tag' }}"></i>
                    {{ $category->name }}
                </span>
            @endforeach
        </div>
    </div>

    <div class="tender-content-grid">
        {{-- Colonne principale --}}
        <div class="tender-main">
            {{-- Description --}}
            <div class="content-card">
                <h2 class="card-title">
                    <i class="fas fa-align-left"></i>
                    Description
                </h2>
                <div class="description-content">
                    {!! nl2br(e($tender->description)) !!}
                </div>
            </div>

            {{-- Médias --}}
            @php
                $photos = $tender->photos;
                if (is_string($photos)) {
                    $photos = json_decode($photos, true) ?? [];
                }
                $photos = $photos ?? [];
                
                $videos = $tender->videos;
                if (is_string($videos)) {
                    $videos = json_decode($videos, true) ?? [];
                }
                $videos = $videos ?? [];
            @endphp
            @if(count($photos) > 0 || count($videos) > 0 || count($tender->documents ?? []) > 0)
                <div class="content-card">
                    <h2 class="card-title">
                        <i class="fas fa-images"></i>
                        Médias et documents
                    </h2>
                    
                    @if(count($photos) > 0)
                        <div class="media-gallery">
                            @foreach($photos as $photo)
                                <div class="media-thumb" onclick="window.open('{{ $storageUrl($photo) }}', '_blank')">
                                    <img src="{{ $storageUrl($photo) }}" alt="Photo" onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;background:#f1f5f9;color:#94a3b8;\'><i class=\'fas fa-image\'></i></div>'">
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(count($videos) > 0)
                        <div class="video-gallery" style="margin-top: 1rem;">
                            @foreach($videos as $video)
                                <div class="video-container" style="position: relative; background: #000; border-radius: 12px; overflow: hidden; margin-bottom: 1rem;">
                                    <video 
                                        controls 
                                        playsinline 
                                        webkit-playsinline
                                        preload="metadata"
                                        style="width: 100%; max-height: 400px; display: block;"
                                        src="{{ $storageUrl($video) }}"
                                        onerror="this.parentElement.innerHTML='<div style=\'padding:2rem;text-align:center;color:#94a3b8;\'><i class=\'fas fa-exclamation-triangle\' style=\'font-size:2rem;margin-bottom:0.5rem;display:block;\'></i>Vidéo non disponible</div>'"
                                    >
                                        Votre navigateur ne supporte pas la lecture vidéo.
                                    </video>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(count($tender->documents ?? []) > 0)
                        <div class="documents-list">
                            @foreach($tender->documents as $doc)
                                <a href="{{ $storageUrl($doc) }}" class="document-item" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                    <span>{{ basename($doc) }}</span>
                                    <i class="fas fa-download"></i>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- Propositions reçues --}}
            <div class="content-card responses-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-comments"></i>
                        Propositions reçues
                        <span class="count-badge">{{ $tender->responses->count() }}</span>
                    </h2>
                    
                    <div class="responses-filter">
                        <button @click="filter = 'all'" :class="{ 'active': filter === 'all' }">Toutes</button>
                        <button @click="filter = 'shortlisted'" :class="{ 'active': filter === 'shortlisted' }">
                            <i class="fas fa-star"></i> Présélection
                        </button>
                    </div>
                </div>

                @if($tender->responses->count() > 0)
                    <div class="responses-list">
                        @foreach($tender->responses->sortByDesc('match_score') as $response)
                            <div class="response-card" 
                                 data-status="{{ $response->status }}"
                                 x-show="filter === 'all' || (filter === 'shortlisted' && '{{ $response->status }}' === 'shortlisted')">
                                
                                <div class="response-header">
                                    <div class="prestataire-info">
                                        <div class="prestataire-avatar">
                                            @if($response->prestataire->user->avatar)
                                                <img src="{{ $storageUrl($response->prestataire->user->avatar) }}" alt="{{ $response->prestataire->user->name ?? 'Prestataire' }}">
                                            @else
                                                <i class="fas fa-user"></i>
                                            @endif
                                        </div>
                                        <div class="prestataire-details">
                                            <h4>{{ $response->prestataire->user->name }}</h4>
                                            <div class="prestataire-meta">
                                                @if($response->prestataire->is_verified)
                                                    <span class="verified-badge">
                                                        <i class="fas fa-check-circle"></i> Vérifié
                                                    </span>
                                                @endif
                                                @if($response->prestataire->average_rating > 0)
                                                    <span class="rating">
                                                        <i class="fas fa-star"></i>
                                                        {{ number_format($response->prestataire->average_rating, 1) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="match-score-badge" style="--score: {{ $response->match_score }}%">
                                        <span class="score-value">{{ $response->match_score }}%</span>
                                        <span class="score-label">Match</span>
                                    </div>
                                </div>

                                <div class="response-content">
                                    <div class="response-price">
                                        <span class="price-value">{{ number_format($response->proposed_price, 2) }} €</span>
                                        <span class="price-type">
                                            @switch($response->price_type)
                                                @case('fixed') Prix fixe @break
                                                @case('hourly') /heure @break
                                                @case('daily') /jour @break
                                                @default Négociable @break
                                            @endswitch
                                        </span>
                                    </div>

                                    <div class="response-message">
                                        <p>{{ Str::limit($response->message, 200) }}</p>
                                        @if(strlen($response->message) > 200)
                                            <button class="read-more" @click="expandedResponse = {{ $response->id }}">
                                                Lire la suite
                                            </button>
                                        @endif
                                    </div>

                                    <div class="response-availability">
                                        <i class="fas fa-calendar-check"></i>
                                        Disponible à partir du {{ $response->availability_start ? $response->availability_start->format('d/m/Y') : 'Non spécifié' }}
                                        @if($response->estimated_duration)
                                            • Durée estimée : {{ $response->estimated_duration }}
                                        @endif
                                    </div>
                                </div>

                                <div class="response-footer">
                                    <span class="response-date">
                                        <i class="fas fa-clock"></i>
                                        {{ $response->created_at->diffForHumans() }}
                                    </span>

                                    @if($response->status === 'pending' || $response->status === 'viewed')
                                        <div class="response-actions">
                                            <form action="{{ route('client.tenders.respond-to-proposal', [$tender, $response]) }}" 
                                                  method="POST" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="action" value="shortlist">
                                                <button type="submit" class="btn btn-secondary btn-sm">
                                                    <i class="fas fa-star"></i>
                                                    Présélectionner
                                                </button>
                                            </form>
                                            
                                            <button @click="rejectingResponse = {{ $response->id }}" 
                                                    class="btn btn-outline btn-sm">
                                                <i class="fas fa-times"></i>
                                                Refuser
                                            </button>

                                            <form action="{{ route('client.tenders.respond-to-proposal', [$tender, $response]) }}" 
                                                  method="POST" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="action" value="accept">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i>
                                                    Accepter
                                                </button>
                                            </form>
                                        </div>
                                    @elseif($response->status === 'shortlisted')
                                        <div class="response-actions">
                                            <span class="status-label shortlisted">
                                                <i class="fas fa-star"></i> Présélectionné
                                            </span>
                                            <form action="{{ route('client.tenders.respond-to-proposal', [$tender, $response]) }}" 
                                                  method="POST" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="action" value="accept">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i>
                                                    Choisir ce prestataire
                                                </button>
                                            </form>
                                        </div>
                                    @elseif($response->status === 'accepted')
                                        <span class="status-label accepted">
                                            <i class="fas fa-check-circle"></i> Accepté
                                        </span>
                                    @elseif($response->status === 'rejected')
                                        <span class="status-label rejected">
                                            <i class="fas fa-times-circle"></i> Refusé
                                        </span>
                                    @endif
                                </div>

                                @if($response->status === 'pending')
                                    <div class="new-indicator">
                                        <span>Nouveau</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-responses">
                        <i class="fas fa-inbox"></i>
                        <h4>Aucune proposition pour le moment</h4>
                        <p>Les prestataires correspondant à votre demande seront notifiés et pourront vous soumettre des propositions.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="tender-sidebar">
            {{-- Prestataire attribué --}}
            @if($tender->awardedPrestataire)
                <div class="sidebar-card awarded-card">
                    <div class="card-icon success">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3>Prestataire sélectionné</h3>
                    <div class="awarded-prestataire">
                        <div class="prestataire-avatar large">
                            @if($tender->awardedPrestataire->user->avatar)
                                <img src="{{ $storageUrl($tender->awardedPrestataire->user->avatar) }}" alt="{{ $tender->awardedPrestataire->user->name ?? 'Prestataire' }}">
                            @else
                                <i class="fas fa-user"></i>
                            @endif
                        </div>
                        <div class="prestataire-name">
                            {{ $tender->awardedPrestataire->user->name }}
                        </div>
                        @if($tender->awardedPrestataire->is_verified)
                            <span class="verified-badge">
                                <i class="fas fa-check-circle"></i> Vérifié
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('client.messaging.start', $tender->awardedPrestataire) }}" 
                       class="btn btn-primary btn-block">
                        <i class="fas fa-comments"></i>
                        Contacter
                    </a>
                </div>
            @endif

            {{-- Statistiques --}}
            <div class="sidebar-card stats-card">
                <h3>Statistiques</h3>
                <div class="stats-list">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">{{ $tender->responses->count() }}</span>
                            <span class="stat-label">Propositions</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">{{ $tender->invitations->count() }}</span>
                            <span class="stat-label">Prestataires notifiés</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">{{ $tender->invitations->whereNotNull('read_at')->count() }}</span>
                            <span class="stat-label">Ont consulté</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">{{ $tender->responses->where('status', 'shortlisted')->count() }}</span>
                            <span class="stat-label">Présélectionnés</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Informations --}}
            <div class="sidebar-card info-card">
                <h3>Informations</h3>
                <ul class="info-list">
                    <li>
                        <span class="info-label">Créé le</span>
                        <span class="info-value">{{ $tender->created_at->format('d/m/Y à H:i') }}</span>
                    </li>
                    @if($tender->published_at)
                        <li>
                            <span class="info-label">Publié le</span>
                            <span class="info-value">{{ $tender->published_at->format('d/m/Y à H:i') }}</span>
                        </li>
                    @endif
                    <li>
                        <span class="info-label">Expire le</span>
                        <span class="info-value">{{ $tender->expires_at ? $tender->expires_at->format('d/m/Y') : '-' }}</span>
                    </li>
                    <li>
                        <span class="info-label">Rayon de recherche</span>
                        <span class="info-value">{{ $tender->radius_km }} km</span>
                    </li>
                    <li>
                        <span class="info-label">Max propositions</span>
                        <span class="info-value">{{ $tender->max_responses }}</span>
                    </li>
                    <li>
                        <span class="info-label">Visibilité</span>
                        <span class="info-value">{{ $tender->public_visibility ? 'Public' : 'Sur invitation' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Modal Annulation --}}
    <div x-show="showCancelModal" 
         x-transition
         class="modal-overlay"
         @click.self="showCancelModal = false">
        <div class="modal-content">
            <h3>Annuler l'appel d'offre ?</h3>
            <p>Cette action est irréversible. Les prestataires ayant soumis des propositions seront notifiés.</p>
            <div class="modal-actions">
                <button @click="showCancelModal = false" class="btn btn-secondary">
                    Non, revenir
                </button>
                <form action="{{ route('client.tenders.cancel', $tender) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        Oui, annuler
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Refus --}}
    <div x-show="rejectingResponse !== null" 
         x-transition
         class="modal-overlay"
         @click.self="rejectingResponse = null">
        <div class="modal-content">
            <h3>Refuser cette proposition ?</h3>
            <form :action="'{{ route('client.tenders.show', $tender) }}/responses/' + rejectingResponse" method="POST">
                @csrf
                <input type="hidden" name="action" value="reject">
                <div class="form-group">
                    <label>Raison du refus (optionnel)</label>
                    <textarea name="rejection_reason" rows="3" class="form-textarea"
                              placeholder="Expliquez brièvement pourquoi cette proposition ne convient pas..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" @click="rejectingResponse = null" class="btn btn-secondary">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-danger">
                        Confirmer le refus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Fonction pour ouvrir les images en plein écran
function openLightbox(src) {
    window.open(src, '_blank');
}
</script>
@endsection
