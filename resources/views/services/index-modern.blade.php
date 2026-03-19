@extends('layouts.app')
 
@section('title', 'Tous les services - TaPrestation')
 
@section('content')
<div class="tp-services-page pb-24 sm:pb-12">
 
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
 
        .tp-services-page {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: #faf7f2;
            min-height: 100vh;
        }
 
        /* ─── HERO ─── */
        .tp-hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(140deg, #0f3a86 0%, #1a4ca0 40%, #2d67c8 70%, #4338ca 100%);
        }
        .tp-hero::after {
            content: '';
            position: absolute;
            bottom: -1px; left: 0; right: 0;
            height: 40px;
            background: #faf7f2;
            border-radius: 40px 40px 0 0;
        }
        .tp-hero-inner {
            position: relative; z-index: 1;
            max-width: 72rem; margin: 0 auto;
            padding: 2rem 1rem 3rem; text-align: center;
        }
        @media (min-width:640px) { .tp-hero-inner { padding: 2.5rem 1.5rem 3.5rem; } }
        .tp-hero-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 48px; height: 48px; border-radius: 12px;
            background: rgba(255,255,255,.15); margin-bottom: .75rem;
        }
        .tp-hero-icon i { font-size: 20px; color: #fff; }
        .tp-hero h1 {
            font-size: 1.625rem; font-weight: 800; color: #fff;
            letter-spacing: -.02em; margin-bottom: .25rem; line-height: 1.2;
        }
        @media (min-width:640px) { .tp-hero h1 { font-size: 2.25rem; } }
        .tp-hero-sub { font-size: .85rem; color: rgba(191,219,254,.85); font-weight: 500; }
        @media (min-width:640px) { .tp-hero-sub { font-size: 1rem; } }
        .tp-hero-stats { display: flex; justify-content: center; gap: 1.25rem; margin-top: 1rem; }
        .tp-hero-stat { display: flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; color: rgba(255,255,255,.65); }
 
        /* ─── FILTRES ─── */
        .tp-filters-wrap { max-width: 72rem; margin: -1.25rem auto 0; padding: 0 .75rem; position: relative; z-index: 2; }
        @media (min-width:640px) { .tp-filters-wrap { margin-top: -1.75rem; padding: 0 1rem; } }
 
        /* ─── RÉSULTATS ─── */
        .tp-results-count { font-size: 13px; font-weight: 700; color: #5a6178; margin-bottom: .75rem; padding: 0 2px; }
        .tp-results-count strong { color: #0f3a86; }
 
        /* ─── GRILLE ─── */
        .tp-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: .5rem; }
        @media (min-width:640px) { .tp-grid { gap: .75rem; } }
        @media (min-width:1024px) { .tp-grid { grid-template-columns: repeat(3,minmax(0,1fr)); gap: .875rem; } }
        @media (min-width:1280px) { .tp-grid { grid-template-columns: repeat(4,minmax(0,1fr)); } }
 
        /* ─── CARTE ─── */
        .tp-card {
            display: block;
            border-radius: 12px; background: #fff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 8px rgba(0,0,0,.05);
            overflow: hidden; cursor: pointer;
            transition: transform 200ms ease, box-shadow 200ms ease;
        }
        .tp-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(15,58,134,.10); }
 
        /* ═══ IMAGE — approche simple qui fonctionne ═══
         * Hauteur fixe + overflow hidden sur le conteneur.
         * Le composant x-media-image reçoit des styles inline width/height/object-fit.
         * En backup, on force aussi via CSS tout <img> descendant.
         */
        .tp-card-img {
            width: 100%;
            height: 150px;
            overflow: hidden;
            position: relative;
            background: #f0ebe3;
        }
        @media (min-width:640px) { .tp-card-img { height: 165px; } }
        @media (min-width:1024px) { .tp-card-img { height: 175px; } }
 
        /* Force TOUT img descendant à couvrir le conteneur */
        /* Force tout <img> à couvrir le cadre, peu importe la profondeur */
        .tp-card-img img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            display: block !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            z-index: 0;
        }
 
        .tp-card-placeholder {
            width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #ede5d8, #e8eef8);
        }
        .tp-card-placeholder i { font-size: 26px; color: rgba(15,58,134,.12); }
 
        .tp-card-time {
            position: absolute; top: 6px; right: 6px; z-index: 3;
            font-size: 10px; font-weight: 700; color: #fff;
            background: rgba(0,0,0,.55); border-radius: 5px;
            padding: 2px 6px; line-height: 1.3;
        }
 
        /* ─── CONTENU ─── */
        .tp-card-body { padding: 8px 10px 10px; }
        .tp-card-price { font-size: 17px; font-weight: 800; color: #1a1a1a; line-height: 1.2; }
        .tp-card-price-devis {
            font-size: 12px; font-weight: 700; color: #0f3a86;
            background: #e8eef8; padding: 2px 7px; border-radius: 5px;
        }
        .tp-card-title {
            font-size: 13px; font-weight: 600; color: #333;
            margin-top: 3px; overflow: hidden; text-overflow: ellipsis;
            white-space: nowrap; line-height: 1.3;
        }
        @media (min-width:640px) { .tp-card-title { font-size: 14px; } }
        .tp-card-provider {
            font-size: 12px; color: #888; margin-top: 1px;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .tp-card-meta {
            display: flex; flex-wrap: wrap; gap: 3px 8px;
            margin-top: 6px; font-size: 11px; font-weight: 600; color: #999;
        }
        .tp-card-meta span { display: inline-flex; align-items: center; gap: 3px; }
        .tp-card-meta i { font-size: 9px; }
        .tp-card-meta .fa-map-marker-alt { color: #2d67c8; }
        .tp-card-meta .fa-star { color: #f59e0b; }
        .tp-card-meta .fa-route { color: #2d67c8; }
 
        /* ═══ BOUTONS ═══ */
        .tp-card-footer { margin-top: 6px; display: flex; gap: 4px; justify-content: flex-end; }
 
        .tp-card-btn {
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 10px !important; font-weight: 700 !important;
            padding: 4px 8px; border-radius: 6px;
            text-decoration: none !important; white-space: nowrap; cursor: pointer;
            transition: all 150ms ease;
            color: #0c2f6d !important;
            background: #ffffff !important;
            border: 1.5px solid #0f3a86 !important;
            line-height: 1.3;
        }
        .tp-card-btn:hover {
            background: #0f3a86 !important;
            color: #ffffff !important;
        }
        .tp-card-btn-primary {
            color: #ffffff !important;
            background: #0f3a86 !important;
            border: 1.5px solid #0f3a86 !important;
        }
        .tp-card-btn-primary:hover {
            background: #0c2f6d !important;
            border-color: #0c2f6d !important;
        }
        @media (min-width: 640px) {
            .tp-card-btn { font-size: 11px !important; padding: 5px 10px; }
        }
 
        /* ─── ÉTAT VIDE ─── */
        .tp-empty {
            background: #fff; border-radius: 16px; border: 1px solid #e5e7eb;
            box-shadow: 0 1px 8px rgba(0,0,0,.05);
            padding: 2.5rem 1.5rem; text-align: center;
            max-width: 420px; margin: 2rem auto;
        }
        .tp-empty-icon {
            width: 64px; height: 64px; border-radius: 50%;
            background: linear-gradient(135deg, #ede5d8, #e8eef8);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
        }
        .tp-empty-icon i { font-size: 24px; color: #0f3a86; opacity: .5; }
        .tp-empty h3 { font-size: 1rem; font-weight: 800; color: #1a1f36; margin-bottom: .375rem; }
        .tp-empty p { font-size: .8rem; color: #666; margin-bottom: 1rem; line-height: 1.4; }
        .tp-empty-btn {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 13px; font-weight: 700; color: #fff;
            background: #0f3a86; padding: 10px 18px; border-radius: 8px;
            text-decoration: none;
        }
        .tp-empty-btn:hover { background: #0c2f6d; }
 
        /* ─── PAGINATION ─── */
        .tp-pagination { margin-top: 2rem; display: flex; justify-content: center; }
 
        /* ─── ANIMATION ─── */
        @keyframes tp-fadeUp { from { opacity:0; transform:translateY(12px) } to { opacity:1; transform:translateY(0) } }
        .tp-card { animation: tp-fadeUp .35s ease both; }
        .tp-card:nth-child(1){animation-delay:.02s} .tp-card:nth-child(2){animation-delay:.05s}
        .tp-card:nth-child(3){animation-delay:.08s} .tp-card:nth-child(4){animation-delay:.11s}
        .tp-card:nth-child(5){animation-delay:.14s} .tp-card:nth-child(6){animation-delay:.17s}
        .tp-card:nth-child(7){animation-delay:.20s} .tp-card:nth-child(8){animation-delay:.23s}
    </style>
 
    {{-- ═══ HERO ═══ --}}
    <div class="tp-hero">
        <div class="tp-hero-inner">
            <div class="tp-hero-icon"><i class="fas fa-briefcase"></i></div>
            <h1>Services Professionnels</h1>
            <p class="tp-hero-sub">Trouvez l'expert idéal pour votre projet</p>
            @if($services->count() > 0)
                <div class="tp-hero-stats">
                    <div class="tp-hero-stat"><i class="fas fa-check-circle"></i> {{ $services->total() }} service{{ $services->total() > 1 ? 's' : '' }}</div>
                    @if($categories->count() > 0)
                        <div class="tp-hero-stat"><i class="fas fa-th-large"></i> {{ $categories->whereNull('parent_id')->count() }} catégorie{{ $categories->whereNull('parent_id')->count() > 1 ? 's' : '' }}</div>
                    @endif
                </div>
            @endif
        </div>
    </div>
 
    {{-- ═══ FILTRES ═══ --}}
    <div class="tp-filters-wrap">
        @include('components.filters.compact-filters', [
            'pageType' => 'services',
            'themeColor' => 'blue',
            'formAction' => route('services.index'),
            'categories' => $categories->whereNull('parent_id')
        ])
    </div>
 
    {{-- ═══ CONTENU ═══ --}}
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mt-4 sm:mt-6">
        @if($services->count() > 0)
            <div class="tp-results-count">
                <strong>{{ $services->total() }}</strong> résultat{{ $services->total() > 1 ? 's' : '' }}@if(request('q')) pour « {{ request('q') }} »@endif
            </div>
 
            <div class="tp-grid">
                @foreach($services as $service)
                    @php
                        $photoCount = $service->images?->count() ?? 0;
                        $firstPhoto = $photoCount > 0 ? $service->images->first()->image_path : null;
                        $prestataire = $service->prestataire;
                        $prestataireName = $prestataire?->user?->name ?? $prestataire?->company_name ?? $prestataire?->first_name ?? 'Prestataire';
                        $canContact = auth()->check() && auth()->user()->role === 'client' && $prestataire && auth()->user()->id !== $prestataire->user_id;
                        $showBooking = function_exists('feature_enabled') && feature_enabled('booking_payment_enabled');
                        $city = $service->city ?: ($prestataire?->city ?: null);
                        $ratingAvg = $prestataire?->rating_average;
                        $ratingCount = $prestataire?->total_reviews;
                    @endphp
 
                    <div class="tp-card" data-card-link role="link" tabindex="0" data-href="{{ route('services.show', $service) }}">
                        {{-- Image — on garde le style inline comme l'original qui fonctionnait --}}
                        <div class="tp-card-img">
                            @if($firstPhoto)
                                <x-media-image :path="$firstPhoto" :alt="$service->title" style="width:100%;height:100%;object-fit:cover;display:block" />
                            @else
                                <div class="tp-card-placeholder"><i class="fas fa-briefcase"></i></div>
                            @endif
                            <div class="tp-card-time">{{ $service->created_at->diffForHumans(null, true, true) }}</div>
                        </div>
 
                        <div class="tp-card-body">
                            @if($service->price)
                                <div class="tp-card-price">{{ number_format($service->price, 0, ',', ' ') }} €</div>
                            @else
                                <div><span class="tp-card-price-devis">Sur devis</span></div>
                            @endif
                            <div class="tp-card-title">{{ $service->title }}</div>
                            <div class="tp-card-provider">{{ $prestataireName }}</div>
                            <div class="tp-card-meta">
                                @if($city)<span><i class="fas fa-map-marker-alt"></i> {{ Str::limit($city, 18) }}</span>@endif
                                @if(is_numeric($ratingAvg) && (float)$ratingAvg > 0)<span><i class="fas fa-star"></i> {{ number_format((float)$ratingAvg, 1, '.', '') }}@if(is_numeric($ratingCount) && (int)$ratingCount > 0) ({{ (int)$ratingCount }})@endif</span>@endif
                                @if(isset($service->distance_km) && $service->distance_km !== null)<span><i class="fas fa-route"></i> {{ $service->distance_km }} km</span>@endif
                            </div>
 
                            {{-- Boutons bien contrastés --}}
                            <div class="tp-card-footer">
                                @auth
                                    @php $isOwner = $prestataire && auth()->user()->id === ($prestataire->user_id ?? null); @endphp
                                    @if(!$isOwner)
                                        <a href="{{ $canContact ? route('client.messaging.start', $prestataire) : route('services.show', $service) }}" class="tp-card-btn" onclick="event.stopPropagation()">Contacter</a>
                                        <a href="{{ ($showBooking && \Illuminate\Support\Facades\Route::has('bookings.create')) ? route('bookings.create', $service) : route('services.show', $service) }}" class="tp-card-btn tp-card-btn-primary" onclick="event.stopPropagation()">{{ ($service->reservable ?? true) ? 'Réserver' : 'Demander' }}</a>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="tp-card-btn" onclick="event.stopPropagation()">Contacter</a>
                                    <a href="{{ route('login') }}" class="tp-card-btn tp-card-btn-primary" onclick="event.stopPropagation()">Réserver</a>
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
 
            @if($services->hasPages())
                <div class="tp-pagination">{{ $services->appends(request()->query())->links() }}</div>
            @endif
        @else
            <div class="tp-empty">
                <div class="tp-empty-icon"><i class="fas fa-search"></i></div>
                <h3>Aucun service trouvé</h3>
                <p>Essayez de modifier vos filtres ou explorez tous nos services.</p>
                <a href="{{ route('services.index') }}" class="tp-empty-btn"><i class="fas fa-redo" style="font-size:10px"></i> Réinitialiser</a>
            </div>
        @endif
    </div>
</div>
@endsection