@extends('layouts.prestataire')

@section('title', $tender->title)

@section('content')
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

<style>
    * { box-sizing: border-box; }
    .tender-page { max-width: 800px; margin: 0 auto; padding: 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    .back-link { display: inline-flex; align-items: center; gap: 8px; color: #3b82f6; text-decoration: none; font-size: 14px; margin-bottom: 16px; }
    .back-link:hover { text-decoration: underline; }
    
    /* Match Score Card */
    .match-card { background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%); border-radius: 16px; padding: 20px; color: white; margin-bottom: 16px; }
    .match-header { display: flex; align-items: center; gap: 16px; margin-bottom: 16px; }
    .match-score { font-size: 48px; font-weight: 700; }
    .match-text h3 { margin: 0 0 4px; font-size: 16px; font-weight: 600; }
    .match-text p { margin: 0; opacity: 0.9; font-size: 14px; }
    .match-tags { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
    .match-tag { background: rgba(255,255,255,0.2); padding: 6px 12px; border-radius: 20px; font-size: 12px; }
    .btn-propose { display: block; width: 100%; background: white; color: #10b981; padding: 14px; border-radius: 12px; text-align: center; font-weight: 600; font-size: 16px; text-decoration: none; }
    .btn-propose:hover { background: #f0fdf4; }
    
    /* Info Cards */
    .card { background: white; border-radius: 16px; padding: 20px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .card-title { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600; color: #6b7280; margin: 0 0 12px; text-transform: uppercase; letter-spacing: 0.5px; }
    .card-title i { color: #3b82f6; }
    
    /* Status */
    .status-row { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
    .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; }
    .status-published { background: #d1fae5; color: #065f46; }
    .status-expires { background: #fef3c7; color: #92400e; }
    
    /* Title */
    .tender-title { font-size: 24px; font-weight: 700; color: #1f2937; margin: 0 0 16px; line-height: 1.3; }
    
    /* Meta Info */
    .meta-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 16px; }
    .meta-item { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #4b5563; }
    .meta-item i { color: #3b82f6; width: 16px; }
    
    /* Categories */
    .categories { display: flex; gap: 8px; flex-wrap: wrap; }
    .category-tag { background: #eff6ff; color: #3b82f6; padding: 8px 14px; border-radius: 20px; font-size: 13px; font-weight: 500; }
    
    /* Description */
    .description { color: #374151; line-height: 1.7; font-size: 15px; white-space: pre-wrap; }
    
    /* Photos */
    .photos-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .photo-item { aspect-ratio: 1; border-radius: 12px; overflow: hidden; background: #f3f4f6; }
    .photo-item img { width: 100%; height: 100%; object-fit: cover; }
    
    /* Client */
    .client-info { display: flex; align-items: center; gap: 12px; }
    .client-avatar { width: 48px; height: 48px; border-radius: 50%; background: #3b82f6; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; }
    .client-name { font-weight: 600; color: #1f2937; display: block; }
    .client-since { font-size: 13px; color: #6b7280; }
    
    /* Details List */
    .details-list { list-style: none; padding: 0; margin: 0; }
    .details-list li { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
    .details-list li:last-child { border-bottom: none; }
    .details-label { color: #6b7280; }
    .details-value { color: #1f2937; font-weight: 500; text-align: right; }
    
    /* CTA Bottom */
    .cta-bottom { position: fixed; bottom: 60px; left: 0; right: 0; background: white; padding: 16px; box-shadow: 0 -4px 12px rgba(0,0,0,0.1); z-index: 100; }
    .cta-bottom .btn-propose { background: #10b981; color: white; }
    .cta-bottom .btn-propose:hover { background: #059669; }
    
    /* Responsive */
    @media (max-width: 480px) {
        .meta-grid { grid-template-columns: 1fr; }
        .photos-grid { grid-template-columns: repeat(2, 1fr); }
    }
    
    /* VERY SMALL SCREENS (< 400px) */
    @media (max-width: 400px) {
        .tender-page { padding: 10px; padding-bottom: 100px; }
        .match-card { padding: 14px; border-radius: 12px; }
        .match-score { font-size: 36px; }
        .match-text h3 { font-size: 14px; }
        .match-text p { font-size: 12px; }
        .match-tag { padding: 4px 8px; font-size: 10px; }
        .btn-propose { padding: 10px; font-size: 14px; }
        .card { padding: 14px; border-radius: 12px; }
        .card-title { font-size: 12px; margin-bottom: 10px; }
        .tender-title { font-size: 18px; margin-bottom: 12px; }
        .meta-item { font-size: 12px; }
        .category-tag { padding: 6px 10px; font-size: 11px; }
        .description { font-size: 13px; }
        .client-avatar { width: 40px; height: 40px; font-size: 16px; }
        .client-name { font-size: 14px; }
        .client-since { font-size: 11px; }
        .details-list li { padding: 10px 0; font-size: 12px; }
        .status-badge { padding: 4px 8px; font-size: 10px; }
        .cta-bottom { padding: 12px; bottom: 50px; }
    }
    
    /* Space for fixed CTA */
    .tender-page { padding-bottom: 120px; }
</style>

<div class="tender-page">
    
    {{-- Back Link --}}
    <a href="{{ route('prestataire.tenders.index') }}" class="back-link">
        <i class="fas fa-arrow-left"></i>
        Retour aux appels d'offres
    </a>

    {{-- Match Score Card --}}
    <div class="match-card">
        <div class="match-header">
            <div class="match-score">{{ $matchScore }}%</div>
            <div class="match-text">
                <h3>Correspondance avec votre profil</h3>
                <p>Cette demande match avec vos compétences</p>
            </div>
        </div>
        <div class="match-tags">
            <span class="match-tag"><i class="fas fa-check"></i> Catégories compatibles</span>
            @if($matchScore >= 50)
                <span class="match-tag"><i class="fas fa-check"></i> Zone géographique</span>
            @endif
        </div>
        @if(!$existingResponse && $canRespond)
            <a href="{{ route('prestataire.tenders.respond', $tender) }}" class="btn-propose">
                <i class="fas fa-paper-plane"></i> Proposer mes services
            </a>
        @elseif($existingResponse)
            <div style="background:rgba(255,255,255,0.2);padding:12px;border-radius:8px;text-align:center;">
                <i class="fas fa-check-circle"></i> Proposition envoyée - {{ ucfirst($existingResponse->status) }}
            </div>
        @endif
    </div>

    {{-- Header Card --}}
    <div class="card">
        <div class="status-row">
            <span class="status-badge status-published">
                <i class="fas fa-check-circle"></i> Publié
            </span>
            <span class="status-badge status-expires">
                <i class="fas fa-clock"></i> Expire {{ $tender->expires_at ? $tender->expires_at->diffForHumans() : 'dans 30 jours' }}
            </span>
        </div>
        
        <h1 class="tender-title">{{ $tender->title }}</h1>
        
        <div class="meta-grid">
            <div class="meta-item">
                <i class="fas fa-map-marker-alt"></i>
                {{ $tender->city }} ({{ $tender->radius_km }} km)
            </div>
            <div class="meta-item">
                <i class="fas fa-calendar"></i>
                @if($tender->start_date)
                    {{ $tender->start_date->format('d/m/Y') }}
                    @if($tender->end_date) - {{ $tender->end_date->format('d/m/Y') }} @endif
                @else
                    Date flexible
                @endif
            </div>
            @if($tender->budget_visible && $tender->budget_min)
                <div class="meta-item">
                    <i class="fas fa-euro-sign"></i>
                    {{ number_format($tender->budget_min, 0, ',', ' ') }} € - {{ number_format($tender->budget_max, 0, ',', ' ') }} €
                </div>
            @endif
            <div class="meta-item">
                <i class="fas fa-comments"></i>
                {{ $tender->responses()->count() }}/{{ $tender->max_responses }} propositions
            </div>
        </div>
        
        <div class="categories">
            @foreach($tender->categories as $category)
                <span class="category-tag">{{ $category->name }}</span>
            @endforeach
        </div>
    </div>

    {{-- Description --}}
    <div class="card">
        <h2 class="card-title"><i class="fas fa-align-left"></i> Description</h2>
        <div class="description">{{ $tender->description }}</div>
    </div>

    {{-- Photos --}}
    @if(count($photos) > 0)
        <div class="card">
            <h2 class="card-title"><i class="fas fa-images"></i> Photos ({{ count($photos) }})</h2>
            <div class="photos-grid">
                @foreach($photos as $photo)
                    <div class="photo-item">
                        <img src="/serve-image.php?path={{ urlencode($photo) }}" 
                             alt="Photo" 
                             onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;color:#9ca3af\'><i class=\'fas fa-image\'></i></div>'">
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Vidéos --}}
    @if(count($videos) > 0)
        <div class="card">
            <h2 class="card-title"><i class="fas fa-video"></i> Vidéos ({{ count($videos) }})</h2>
            <div class="videos-grid" style="display:flex;flex-direction:column;gap:12px;">
                @foreach($videos as $video)
                    <div class="video-item" style="position:relative;background:#000;border-radius:12px;overflow:hidden;">
                        <video 
                            controls 
                            playsinline 
                            webkit-playsinline
                            preload="metadata"
                            style="width:100%;max-height:300px;display:block;"
                            src="{{ url('/serve.php?f=' . urlencode($video)) }}"
                            onerror="this.parentElement.innerHTML='<div style=\'padding:2rem;text-align:center;color:#9ca3af;\'><i class=\'fas fa-exclamation-triangle\' style=\'font-size:2rem;margin-bottom:0.5rem;display:block;\'></i>Vidéo non disponible</div>'"
                        >
                            Votre navigateur ne supporte pas la lecture vidéo.
                        </video>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Client --}}
    <div class="card">
        <h2 class="card-title"><i class="fas fa-user"></i> Le client</h2>
        <div class="client-info">
            <div class="client-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <span class="client-name">{{ $tender->client->user->name ?? 'Client' }}</span>
                <span class="client-since">Membre depuis {{ $tender->client->user->created_at->format('Y') ?? '2024' }}</span>
            </div>
        </div>
    </div>

    {{-- Details --}}
    <div class="card">
        <h2 class="card-title"><i class="fas fa-info-circle"></i> Détails</h2>
        <ul class="details-list">
            <li>
                <span class="details-label">Lieu</span>
                <span class="details-value">{{ $tender->city }}, {{ $tender->postal_code }}</span>
            </li>
            <li>
                <span class="details-label">Date souhaitée</span>
                <span class="details-value">{{ $tender->start_date ? $tender->start_date->format('d/m/Y') : 'Flexible' }}</span>
            </li>
            @if($tender->budget_visible && $tender->budget_min)
                <li>
                    <span class="details-label">Budget</span>
                    <span class="details-value">{{ number_format($tender->budget_min, 0, ',', ' ') }} € - {{ number_format($tender->budget_max, 0, ',', ' ') }} €</span>
                </li>
            @endif
            <li>
                <span class="details-label">Contact préféré</span>
                <span class="details-value">
                    @switch($tender->contact_preference)
                        @case('messaging') Messagerie @break
                        @case('email') Email @break
                        @case('phone') Téléphone @break
                        @default Messagerie @break
                    @endswitch
                </span>
            </li>
        </ul>
    </div>

</div>

{{-- Fixed CTA Button --}}
@if(!$existingResponse && $canRespond)
    <div class="cta-bottom">
        <a href="{{ route('prestataire.tenders.respond', $tender) }}" class="btn-propose">
            <i class="fas fa-paper-plane"></i> Proposer mes services
        </a>
    </div>
@endif

@endsection
