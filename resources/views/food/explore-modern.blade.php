@extends('layouts.app')
 
@section('title', 'Food - Trouvez votre repas')
 
@section('content')
<div class="tp-food-page">
 
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
 
    /* ─── BASE ─── */
    .tp-food-page {
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        background: #faf7f2;
        min-height: 100vh;
        padding-bottom: 6rem;
    }
    @media (min-width:640px) { .tp-food-page { padding-bottom: 3rem; } }
 
    /* ─── HERO ─── */
    .tp-food-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(140deg, #c2410c 0%, #ea580c 35%, #f97316 65%, #fb923c 100%);
    }
    .tp-food-hero::after {
        content: '';
        position: absolute;
        bottom: -1px; left: 0; right: 0;
        height: 36px;
        background: #faf7f2;
        border-radius: 36px 36px 0 0;
    }
    .tp-food-hero-inner {
        position: relative; z-index: 1;
        max-width: 44rem; margin: 0 auto;
        padding: 1.75rem 1rem 3rem; text-align: center;
    }
    @media (min-width:640px) { .tp-food-hero-inner { padding: 2.25rem 1.5rem 3.5rem; } }
    .tp-food-hero-emoji { font-size: 2.5rem; margin-bottom: .5rem; }
    .tp-food-hero h1 {
        font-size: 1.4rem; font-weight: 800; color: #fff;
        letter-spacing: -.02em; margin-bottom: .25rem; line-height: 1.2;
    }
    @media (min-width:640px) { .tp-food-hero h1 { font-size: 2rem; } }
    .tp-food-hero p { font-size: .8rem; color: rgba(254,215,170,.85); font-weight: 500; margin-bottom: 1.25rem; }
    @media (min-width:640px) { .tp-food-hero p { font-size: .95rem; } }
 
    /* ─── SEARCH BAR ─── */
    .tp-food-search {
        display: flex; flex-direction: column; gap: 8px;
        background: rgba(255,255,255,.15);
        backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 16px; padding: 10px;
    }
    @media (min-width:640px) {
        .tp-food-search { flex-direction: row; border-radius: 18px; padding: 8px; }
    }
    .tp-food-search-field {
        display: flex; align-items: center; gap: 8px;
        background: #fff; border-radius: 10px; padding: 0 12px;
        flex: 1; min-width: 0;
    }
    .tp-food-search-field i { color: #9ca3af; font-size: 13px; flex-shrink: 0; }
    .tp-food-search-field input {
        width: 100%; border: none; outline: none;
        font-family: inherit; font-size: 13px; font-weight: 500;
        color: #1f2937; padding: 10px 0; background: transparent;
    }
    .tp-food-search-field input::placeholder { color: #9ca3af; }
    .tp-food-search-loc {
        display: flex; align-items: center; gap: 6px;
        position: relative;
    }
    .tp-food-search-loc .tp-food-search-field { flex: 1; }
    .tp-food-gps-btn {
        border: none; background: #fff; color: #ea580c;
        border-radius: 8px; padding: 8px 10px; font-size: 12px;
        font-weight: 800; cursor: pointer; flex-shrink: 0;
        transition: background 150ms;
    }
    .tp-food-gps-btn:hover { background: #fff7ed; }
    .tp-food-gps-btn:disabled { opacity: .6; cursor: wait; }
    .tp-food-search-submit {
        border: none; background: #fff; color: #ea580c;
        border-radius: 10px; padding: 10px 16px;
        font-family: inherit; font-size: 13px; font-weight: 800;
        cursor: pointer; display: flex; align-items: center; gap: 6px;
        transition: background 150ms;
        white-space: nowrap;
    }
    .tp-food-search-submit:hover { background: #fff7ed; }
    @media (min-width:640px) {
        .tp-food-search-submit { border-radius: 12px; }
    }
 
    /* Suggestions dropdown */
    .hero-location-suggestions {
        position: absolute;
        top: calc(100% + 6px); left: 0; right: 0;
        background: #fff; border: 1px solid #fed7aa; border-radius: 12px;
        box-shadow: 0 16px 40px rgba(15,23,42,.12); z-index: 100;
        max-height: 240px; overflow-y: auto;
    }
    .hero-location-suggestions.hidden { display: none; }
    .hero-suggestion-item {
        padding: 10px 12px; cursor: pointer;
        border-bottom: 1px solid #f3f4f6; transition: background 150ms;
    }
    .hero-suggestion-item:last-child { border-bottom: none; }
    .hero-suggestion-item:hover, .hero-suggestion-item.active { background: #fff7ed; }
 
    /* ─── CATÉGORIES ─── */
    .tp-food-cats {
        max-width: 72rem; margin: -1rem auto 0; padding: 0 .75rem;
        position: relative; z-index: 2;
    }
    @media (min-width:640px) { .tp-food-cats { margin-top: -1.25rem; padding: 0 1rem; } }
    .tp-food-cats-scroll {
        display: flex; gap: 6px; overflow-x: auto; padding: 4px 2px 8px;
        -webkit-overflow-scrolling: touch; scrollbar-width: none;
    }
    .tp-food-cats-scroll::-webkit-scrollbar { display: none; }
    .tp-food-cat {
        display: flex; flex-direction: column; align-items: center; gap: 2px;
        padding: 8px 12px; border-radius: 12px;
        background: #fff; border: 1.5px solid #e5e7eb;
        font-size: 10px; font-weight: 700; color: #666;
        text-decoration: none; white-space: nowrap;
        transition: all 150ms; flex-shrink: 0;
    }
    .tp-food-cat:hover { border-color: #fed7aa; background: #fff7ed; color: #9a3412; }
    .tp-food-cat.active {
        background: #ea580c; border-color: #ea580c; color: #fff;
    }
    .tp-food-cat-emoji { font-size: 1.25rem; line-height: 1; }
    @media (min-width:640px) {
        .tp-food-cat { font-size: 11px; padding: 10px 14px; }
    }
 
    /* ─── FILTRES BAR ─── */
    .tp-food-bar {
        max-width: 72rem; margin: .75rem auto 0; padding: 0 .75rem;
    }
    @media (min-width:640px) { .tp-food-bar { padding: 0 1rem; } }
    .tp-food-bar-inner {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 8px;
    }
    .tp-food-bar-count { font-size: 12px; font-weight: 700; color: #5a6178; }
    .tp-food-bar-count strong { color: #ea580c; }
    .tp-food-bar-right { display: flex; align-items: center; gap: 6px; }
    .tp-food-sort-wrap {
        position: relative; display: flex; align-items: center;
    }
    .tp-food-sort-wrap select {
        appearance: none; -webkit-appearance: none;
        font-family: inherit; font-size: 11px; font-weight: 700;
        color: #374151; background: #fff;
        border: 1.5px solid #e5e7eb; border-radius: 8px;
        padding: 6px 28px 6px 10px; cursor: pointer;
        transition: border-color 150ms;
    }
    .tp-food-sort-wrap select:focus { outline: none; border-color: #f97316; }
    .tp-food-sort-wrap i {
        position: absolute; right: 8px; font-size: 9px; color: #9ca3af;
        pointer-events: none;
    }
    .tp-food-filter-btn {
        display: flex; align-items: center; gap: 5px;
        font-family: inherit; font-size: 11px; font-weight: 700;
        color: #374151; background: #fff;
        border: 1.5px solid #e5e7eb; border-radius: 8px;
        padding: 6px 10px; cursor: pointer;
        transition: all 150ms;
    }
    .tp-food-filter-btn:hover { border-color: #f97316; color: #ea580c; }
 
    /* Panneau filtres avancés */
    .tp-food-adv-filters {
        display: none; margin-top: 10px;
        background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;
        padding: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.04);
    }
    .tp-food-adv-filters.open { display: block; }
    .tp-food-adv-filters form {
        display: grid; grid-template-columns: 1fr; gap: 12px;
    }
    @media (min-width:640px) {
        .tp-food-adv-filters form { grid-template-columns: repeat(2, 1fr); }
    }
    @media (min-width:1024px) {
        .tp-food-adv-filters form { grid-template-columns: repeat(4, 1fr); }
    }
    .tp-food-fg label {
        display: block; font-size: 11px; font-weight: 700; color: #555;
        margin-bottom: 4px; text-transform: uppercase; letter-spacing: .03em;
    }
    .tp-food-fg select,
    .tp-food-fg input[type="date"] {
        width: 100%; padding: 8px 10px;
        border: 1.5px solid #e5e7eb; border-radius: 8px;
        font-family: inherit; font-size: 13px; color: #1f2937;
        background: #fff; transition: border-color 150ms;
    }
    .tp-food-fg select:focus,
    .tp-food-fg input[type="date"]:focus { outline: none; border-color: #f97316; }
    .tp-food-fg .tp-price-range {
        display: flex; align-items: center; gap: 8px;
    }
    .tp-food-fg .tp-price-range input[type="range"] {
        flex: 1; accent-color: #ea580c;
    }
    .tp-food-fg .tp-price-range span {
        font-size: 12px; font-weight: 800; color: #ea580c; min-width: 40px;
    }
    .tp-food-fg .tp-checkboxes { display: flex; flex-direction: column; gap: 6px; }
    .tp-food-fg .tp-check-label {
        display: flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 600; color: #374151; cursor: pointer;
    }
    .tp-food-fg .tp-check-label input { accent-color: #ea580c; width: 16px; height: 16px; }
    .tp-food-adv-actions {
        grid-column: 1 / -1;
        display: flex; justify-content: flex-end; gap: 8px; padding-top: 4px;
    }
    .tp-food-adv-actions .tp-btn-reset {
        font-family: inherit; font-size: 12px; font-weight: 700;
        color: #666; background: transparent; border: none;
        padding: 8px 14px; cursor: pointer; text-decoration: none;
    }
    .tp-food-adv-actions .tp-btn-apply {
        font-family: inherit; font-size: 12px; font-weight: 700;
        color: #fff; background: #ea580c; border: none; border-radius: 8px;
        padding: 8px 18px; cursor: pointer;
    }
    .tp-food-adv-actions .tp-btn-apply:hover { background: #c2410c; }
 
    /* ─── GRILLE RESTAURANTS ─── */
    .tp-food-grid-wrap {
        max-width: 72rem; margin: 1rem auto 0; padding: 0 .75rem;
    }
    @media (min-width:640px) { .tp-food-grid-wrap { padding: 0 1rem; } }
    .tp-food-grid {
        display: grid; grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .5rem;
    }
    @media (min-width:640px) { .tp-food-grid { gap: .75rem; } }
    @media (min-width:1024px) { .tp-food-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .875rem; } }
    @media (min-width:1280px) { .tp-food-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
 
    /* ─── CARTE RESTAURANT ─── */
    .tp-food-card {
        display: block; border-radius: 12px; background: #fff;
        border: 1px solid #e5e7eb; overflow: hidden;
        box-shadow: 0 1px 8px rgba(0,0,0,.05);
        transition: transform 200ms ease, box-shadow 200ms ease;
    }
    .tp-food-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(234,88,12,.10); }
    .tp-food-card.is-closed { opacity: .7; }
 
    .tp-food-card-link { text-decoration: none; color: inherit; display: block; }
 
    /* Image */
    .tp-food-card-img {
        width: 100%; height: 130px; overflow: hidden;
        position: relative; background: #f0ebe3;
    }
    @media (min-width:640px) { .tp-food-card-img { height: 150px; } }
    @media (min-width:1024px) { .tp-food-card-img { height: 160px; } }
    .tp-food-card-img img {
        width: 100% !important; height: 100% !important;
        object-fit: cover !important; display: block !important;
        position: absolute !important; top: 0 !important; left: 0 !important; z-index: 0;
    }
    .tp-food-card-img .tp-food-placeholder {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #ede5d8, #fff1e6);
        font-size: 2rem;
    }
    .tp-food-card-badges {
        position: absolute; top: 6px; left: 6px; z-index: 2;
        display: flex; gap: 4px;
    }
    .tp-food-badge {
        font-size: 9px; font-weight: 800; color: #fff;
        background: rgba(0,0,0,.55); border-radius: 5px;
        padding: 2px 6px; line-height: 1.3;
        display: flex; align-items: center; gap: 3px;
    }
    .tp-food-badge.delivery { background: rgba(234,88,12,.80); }
    .tp-food-dishes-count {
        position: absolute; bottom: 6px; right: 6px; z-index: 2;
        font-size: 9px; font-weight: 800; color: #fff;
        background: rgba(0,0,0,.50); border-radius: 5px;
        padding: 2px 6px; line-height: 1.3;
    }
 
    /* Contenu */
    .tp-food-card-body { padding: 8px 10px 6px; }
    .tp-food-card-header {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 6px;
    }
    .tp-food-card-header h3 {
        font-size: 13px; font-weight: 700; color: #1a1a1a;
        margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        flex: 1; min-width: 0;
    }
    @media (min-width:640px) { .tp-food-card-header h3 { font-size: 14px; } }
    .tp-food-status {
        font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em;
        padding: 2px 6px; border-radius: 4px; flex-shrink: 0; line-height: 1.4;
    }
    .tp-food-status.open { color: #065f46; background: #d1fae5; }
    .tp-food-status.closed { color: #991b1b; background: #fee2e2; }
    .tp-food-card-cats {
        font-size: 10px; color: #888; margin-top: 2px;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .tp-food-card-footer {
        display: flex; gap: 8px; margin-top: 4px;
        font-size: 10px; font-weight: 600; color: #999;
    }
    .tp-food-card-footer span { display: flex; align-items: center; gap: 3px; }
    .tp-food-card-footer .fa-tag { color: #ea580c; font-size: 9px; }
    .tp-food-card-footer .fa-map-marker-alt { color: #f97316; font-size: 9px; }
 
    /* Actions */
    .tp-food-card-actions {
        padding: 0 10px 8px; display: flex; gap: 4px;
    }
    .tp-food-card-actions .tp-food-btn-menu {
        flex: 1;
        display: flex; align-items: center; justify-content: center; gap: 4px;
        font-family: inherit; font-size: 10px !important; font-weight: 700 !important;
        color: #ffffff !important; background: #ea580c !important;
        border: 1.5px solid #ea580c !important;
        border-radius: 6px; padding: 5px 8px;
        text-decoration: none; transition: all 150ms;
    }
    .tp-food-card-actions .tp-food-btn-menu:hover {
        background: #c2410c !important; border-color: #c2410c !important;
    }
    .tp-food-card-actions .tp-food-btn-info {
        display: flex; align-items: center; justify-content: center;
        width: 30px; height: 30px;
        font-size: 12px; color: #9a3412 !important; background: #fff !important;
        border: 1.5px solid #ea580c !important;
        border-radius: 6px; text-decoration: none; transition: all 150ms;
    }
    .tp-food-card-actions .tp-food-btn-info:hover {
        background: #ea580c !important; color: #fff !important;
    }
    @media (min-width:640px) {
        .tp-food-card-actions .tp-food-btn-menu { font-size: 11px !important; padding: 6px 10px; }
        .tp-food-card-actions .tp-food-btn-info { width: 32px; height: 32px; }
    }
 
    /* ─── ÉTAT VIDE ─── */
    .tp-food-empty {
        background: #fff; border-radius: 16px; border: 1px solid #e5e7eb;
        box-shadow: 0 1px 8px rgba(0,0,0,.05);
        padding: 2.5rem 1.5rem; text-align: center;
        max-width: 420px; margin: 2rem auto;
    }
    .tp-food-empty-emoji { font-size: 2.5rem; margin-bottom: .75rem; }
    .tp-food-empty h3 { font-size: 1rem; font-weight: 800; color: #1a1f36; margin-bottom: .375rem; }
    .tp-food-empty p { font-size: .8rem; color: #666; margin-bottom: 1rem; }
    .tp-food-empty-btn {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 13px; font-weight: 700; color: #fff;
        background: #ea580c; padding: 10px 18px; border-radius: 8px;
        text-decoration: none;
    }
    .tp-food-empty-btn:hover { background: #c2410c; }
 
    /* ─── STATS FOOTER ─── */
    .tp-food-stats {
        max-width: 72rem; margin: 2rem auto 0; padding: 0 .75rem;
    }
    .tp-food-stats-inner {
        display: flex; justify-content: center; gap: 2rem;
        background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;
        padding: 16px 12px; box-shadow: 0 1px 8px rgba(0,0,0,.04);
    }
    .tp-food-stat { text-align: center; }
    .tp-food-stat-val { font-size: 1.25rem; font-weight: 800; color: #ea580c; display: block; }
    .tp-food-stat-label { font-size: 10px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: .04em; }
 
    /* ─── PAGINATION ─── */
    .tp-food-pagination { margin-top: 2rem; display: flex; justify-content: center; }
 
    /* ─── ANIMATION ─── */
    @keyframes tp-fadeUp { from { opacity:0; transform:translateY(12px) } to { opacity:1; transform:translateY(0) } }
    .tp-food-card { animation: tp-fadeUp .35s ease both; }
    .tp-food-card:nth-child(1){animation-delay:.02s} .tp-food-card:nth-child(2){animation-delay:.05s}
    .tp-food-card:nth-child(3){animation-delay:.08s} .tp-food-card:nth-child(4){animation-delay:.11s}
    .tp-food-card:nth-child(5){animation-delay:.14s} .tp-food-card:nth-child(6){animation-delay:.17s}
    .tp-food-card:nth-child(7){animation-delay:.20s} .tp-food-card:nth-child(8){animation-delay:.23s}
</style>
 
    {{-- ═══ HERO ═══ --}}
    <section class="tp-food-hero">
        <div class="tp-food-hero-inner">
            <div class="tp-food-hero-emoji">🍔</div>
            <h1>Qu'est-ce qui vous ferait plaisir ?</h1>
            <p>Découvrez les meilleurs cuisiniers près de chez vous</p>
 
            <form action="{{ route('food.explore') }}" method="GET" class="tp-food-search">
                <div class="tp-food-search-field">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un plat, un cuisinier...">
                </div>
                <div class="tp-food-search-loc">
                    <div class="tp-food-search-field">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" name="city" id="food-city-main" value="{{ request('city') }}" placeholder="Ville, code postal..." autocomplete="off">
                    </div>
                    <button type="button" id="food-location-btn" class="tp-food-gps-btn" title="Utiliser ma position">GPS</button>
                    <input type="hidden" name="latitude" id="food-latitude-main" value="{{ request('latitude') }}">
                    <input type="hidden" name="longitude" id="food-longitude-main" value="{{ request('longitude') }}">
                    <input type="hidden" name="radius" id="food-radius-main" value="{{ request('radius', '25') }}">
                    <div id="food-location-suggestions" class="hero-location-suggestions hidden"></div>
                </div>
                <button type="submit" class="tp-food-search-submit">
                    <i class="fas fa-search"></i>
                    <span>Rechercher</span>
                </button>
            </form>
        </div>
    </section>
 
    {{-- ═══ CATÉGORIES ═══ --}}
    <section class="tp-food-cats">
        <div class="tp-food-cats-scroll">
            <a href="{{ route('food.explore', request()->except('category')) }}"
               class="tp-food-cat {{ !request('category') ? 'active' : '' }}">
                <span class="tp-food-cat-emoji">🍽️</span>
                <span>Tous</span>
            </a>
            @php
                $catEmojis = [
                    'entree'=>'🥗','plat'=>'🍝','dessert'=>'🍰','boisson'=>'🥤',
                    'amuse_bouche'=>'🧆','gateau'=>'🎂','pizza'=>'🍕','sandwich'=>'🥪',
                    'salade'=>'🥗','burger'=>'🍔','sushi'=>'🍣','tacos'=>'🌮',
                    'pates'=>'🍝','poulet'=>'🍗','vegan'=>'🥬','petit_dejeuner'=>'🥐','autre'=>'🍴'
                ];
            @endphp
            @foreach($categories as $key => $label)
                <a href="{{ route('food.explore', array_merge(request()->except('category'), ['category' => $key])) }}"
                   class="tp-food-cat {{ request('category') === $key ? 'active' : '' }}">
                    <span class="tp-food-cat-emoji">{{ $catEmojis[$key] ?? '🍴' }}</span>
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </div>
    </section>
 
    {{-- ═══ FILTRES BAR ═══ --}}
    <section class="tp-food-bar">
        <div class="tp-food-bar-inner">
            <div class="tp-food-bar-count">
                <strong>{{ $prestataires->total() }}</strong> restaurant{{ $prestataires->total() > 1 ? 's' : '' }}
                @if(request('search')) pour "{{ request('search') }}"
                @elseif(request('category')) - {{ $categories[request('category')] ?? '' }}
                @endif
                @if(request('city')) - {{ request('city') }} @endif
                @if(request('available_date'))
                    @php try { $dLabel = \Carbon\Carbon::parse(request('available_date'))->format('d/m/Y'); } catch (\Throwable $e) { $dLabel = request('available_date'); } @endphp
                    - dispo le {{ $dLabel }}
                @endif
            </div>
            <div class="tp-food-bar-right">
                <div class="tp-food-sort-wrap">
                    <select name="sort" id="food-sort-select" form="filter-form">
                        <option value="popular" {{ request('sort')=='popular'?'selected':'' }}>Populaires</option>
                        <option value="distance" {{ request('sort')=='distance'?'selected':'' }}>Distance</option>
                        <option value="price_asc" {{ request('sort')=='price_asc'?'selected':'' }}>Prix ↑</option>
                        <option value="price_desc" {{ request('sort')=='price_desc'?'selected':'' }}>Prix ↓</option>
                        <option value="newest" {{ request('sort')=='newest'?'selected':'' }}>Nouveaux</option>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <button type="button" id="food-filter-toggle" class="tp-food-filter-btn">
                    <i class="fas fa-sliders-h"></i> Filtres
                </button>
            </div>
        </div>
 
        <div class="tp-food-adv-filters" id="advanced-filters">
            <form action="{{ route('food.explore') }}" method="GET" id="filter-form">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="category" value="{{ request('category') }}">
                <input type="hidden" name="city" id="food-city-filter" value="{{ request('city') }}">
                <input type="hidden" name="latitude" id="food-latitude-filter" value="{{ request('latitude') }}">
                <input type="hidden" name="longitude" id="food-longitude-filter" value="{{ request('longitude') }}">
 
                <div class="tp-food-fg">
                    <label>Périmètre</label>
                    <select name="radius" id="food-radius-filter">
                        <option value="">Partout</option>
                        <option value="5" {{ request('radius')=='5'?'selected':'' }}>5 km</option>
                        <option value="10" {{ request('radius')=='10'?'selected':'' }}>10 km</option>
                        <option value="25" {{ request('radius','25')=='25'?'selected':'' }}>25 km</option>
                        <option value="50" {{ request('radius')=='50'?'selected':'' }}>50 km</option>
                        <option value="100" {{ request('radius')=='100'?'selected':'' }}>100 km</option>
                    </select>
                </div>
                <div class="tp-food-fg">
                    <label>Date dispo</label>
                    <input type="date" name="available_date" id="food-available-date-filter" value="{{ request('available_date') }}" min="{{ now()->toDateString() }}">
                </div>
                <div class="tp-food-fg">
                    <label>Prix maximum</label>
                    <div class="tp-price-range">
                        <input type="range" name="price_max" id="food-price-max-range" min="5" max="100" value="{{ request('price_max', 50) }}">
                        <span id="food-price-max-value">{{ request('price_max', 50) }} €</span>
                    </div>
                </div>
                <div class="tp-food-fg">
                    <label>Options</label>
                    <div class="tp-checkboxes">
                        <label class="tp-check-label">
                            <input type="checkbox" name="with_delivery" value="1" {{ request('with_delivery')?'checked':'' }}>
                            Livraison disponible
                        </label>
                        <label class="tp-check-label">
                            <input type="checkbox" name="available_now" value="1" {{ request('available_now')?'checked':'' }}>
                            Ouvert maintenant
                        </label>
                    </div>
                </div>
                <div class="tp-food-adv-actions">
                    <a href="{{ route('food.explore') }}" class="tp-btn-reset">Réinitialiser</a>
                    <button type="submit" class="tp-btn-apply">Appliquer</button>
                </div>
            </form>
        </div>
    </section>
 
    {{-- ═══ LISTE RESTAURANTS ═══ --}}
    <section class="tp-food-grid-wrap">
        @if($prestataires->isEmpty())
            <div class="tp-food-empty">
                <div class="tp-food-empty-emoji">🍽️</div>
                <h3>Aucun restaurant trouvé</h3>
                <p>Essayez de modifier vos critères de recherche</p>
                <a href="{{ route('food.explore') }}" class="tp-food-empty-btn"><i class="fas fa-undo"></i> Voir tous</a>
            </div>
        @else
            <div class="tp-food-grid">
                @foreach($prestataires as $prestataire)
                    @php
                        $foodProductImage = $prestataire->foodProducts->pluck('image')->filter()->first();
                        $imagePath = $prestataire->cover_image ?? $prestataire->logo ?? $prestataire->photo ?? $prestataire->profile_image ?? $foodProductImage ?? $prestataire->profile_photo ?? $prestataire->user->profile_photo_url ?? null;
                        $avgPrice = $prestataire->foodProducts->where('is_available', true)->avg('price');
                        $prestataireCategories = $prestataire->foodProducts->where('is_available', true)->pluck('category')->unique()->take(3);
                        $isOpen = (bool) ($prestataire->food_is_open ?? true);
                    @endphp
                    <article class="tp-food-card {{ $isOpen ? '' : 'is-closed' }}">
                        <a href="{{ route('food.menu', ['prestataire' => $prestataire, 'available_date' => request('available_date')]) }}" class="tp-food-card-link">
                            <div class="tp-food-card-img">
                                @if($imagePath)
                                    <img src="{{ storage_asset_url($imagePath) }}"
                                         alt="{{ $prestataire->business_name ?? $prestataire->user->name }}"
                                         data-food-card-image
                                         style="width:100%;height:100%;object-fit:cover;display:block">
                                @endif
                                <div class="tp-food-placeholder">
                                    <span>👨‍🍳</span>
                                </div>
 
                                <div class="tp-food-card-badges">
                                    @if($prestataire->delivery_available)
                                        <span class="tp-food-badge delivery"><i class="fas fa-motorcycle"></i> Livraison</span>
                                    @endif
                                    @if(isset($prestataire->distance))
                                        <span class="tp-food-badge">{{ number_format($prestataire->distance, 1) }} km</span>
                                    @endif
                                </div>
                                <span class="tp-food-dishes-count">{{ $prestataire->food_products_count ?? 0 }} plats</span>
                            </div>
 
                            <div class="tp-food-card-body">
                                <div class="tp-food-card-header">
                                    <h3>{{ $prestataire->business_name ?? $prestataire->user->name ?? 'Restaurant' }}</h3>
                                    <span class="tp-food-status {{ $isOpen ? 'open' : 'closed' }}">{{ $isOpen ? 'Ouvert' : 'Fermé' }}</span>
                                </div>
                                @if($prestataireCategories->count() > 0)
                                    <div class="tp-food-card-cats">
                                        @foreach($prestataireCategories as $cat)
                                            {{ $categories[$cat] ?? ucfirst($cat) }}@if(!$loop->last) • @endif
                                        @endforeach
                                    </div>
                                @endif
                                <div class="tp-food-card-footer">
                                    @if($avgPrice)
                                        <span><i class="fas fa-tag"></i> ~{{ number_format($avgPrice, 0) }} €</span>
                                    @endif
                                    @if($prestataire->city)
                                        <span><i class="fas fa-map-marker-alt"></i> {{ $prestataire->city }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
 
                        <div class="tp-food-card-actions">
                            <a href="{{ route('food.menu', ['prestataire' => $prestataire, 'available_date' => request('available_date')]) }}" class="tp-food-btn-menu">
                                <i class="fas fa-book-open"></i> Voir le menu
                            </a>
                            <a href="{{ route('prestataires.show', $prestataire) }}" class="tp-food-btn-info">
                                <i class="fas fa-info-circle"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
 
            @if($prestataires->hasPages())
                <div class="tp-food-pagination">{{ $prestataires->withQueryString()->links() }}</div>
            @endif
        @endif
    </section>
 
    {{-- ═══ STATS ═══ --}}
    @if($stats['total_prestataires'] > 0)
    <section class="tp-food-stats">
        <div class="tp-food-stats-inner">
            <div class="tp-food-stat">
                <span class="tp-food-stat-val">{{ $stats['total_prestataires'] }}</span>
                <span class="tp-food-stat-label">Restaurants</span>
            </div>
            <div class="tp-food-stat">
                <span class="tp-food-stat-val">{{ $stats['total_products'] }}</span>
                <span class="tp-food-stat-label">Plats</span>
            </div>
            <div class="tp-food-stat">
                <span class="tp-food-stat-val">{{ $stats['categories_count'] }}</span>
                <span class="tp-food-stat-label">Catégories</span>
            </div>
        </div>
    </section>
    @endif
 
</div>
 
<script>
document.addEventListener('DOMContentLoaded', () => {
    const panel = document.getElementById('advanced-filters');
    const filterBtn = document.getElementById('food-filter-toggle');
    const cityInput = document.getElementById('food-city-main');
    const cityFilterInput = document.getElementById('food-city-filter');
    const latitudeMainInput = document.getElementById('food-latitude-main');
    const longitudeMainInput = document.getElementById('food-longitude-main');
    const radiusMainInput = document.getElementById('food-radius-main');
    const latitudeInput = document.getElementById('food-latitude-filter');
    const longitudeInput = document.getElementById('food-longitude-filter');
    const radiusInput = document.getElementById('food-radius-filter');
    const gpsButton = document.getElementById('food-location-btn');
    const suggestionsContainer = document.getElementById('food-location-suggestions');
    const sortSelect = document.getElementById('food-sort-select');
    const filterForm = document.getElementById('filter-form');
    const priceMaxRange = document.getElementById('food-price-max-range');
    const priceMaxValue = document.getElementById('food-price-max-value');
 
    let suggestionFocus = -1;
    let debounceHandle = null;
 
    const toggleFilters = () => { if (panel) panel.classList.toggle('open'); };
    const hideSuggestions = () => { if (suggestionsContainer) { suggestionsContainer.classList.add('hidden'); suggestionsContainer.innerHTML = ''; suggestionFocus = -1; } };
 
    if (filterBtn) filterBtn.addEventListener('click', toggleFilters);
 
    if (priceMaxRange && priceMaxValue) {
        const sync = () => { priceMaxValue.textContent = priceMaxRange.value + ' €'; };
        sync(); priceMaxRange.addEventListener('input', sync); priceMaxRange.addEventListener('change', sync);
    }
 
    document.querySelectorAll('[data-food-card-image]').forEach(img => {
        img.addEventListener('error', () => { if (img.parentElement) img.parentElement.classList.add('no-image'); img.style.display = 'none'; }, { once: true });
    });
 
    const markActive = () => { if (!suggestionsContainer) return; suggestionsContainer.querySelectorAll('.hero-suggestion-item').forEach((item, i) => { item.classList.toggle('active', i === suggestionFocus); }); };
    const ensureDistanceCtx = () => { if (sortSelect && sortSelect.value === 'distance' && radiusInput && !radiusInput.value) radiusInput.value = '25'; };
 
    const selectLocation = (label, lat = '', lon = '') => {
        if (cityInput) cityInput.value = label;
        if (cityFilterInput) cityFilterInput.value = label;
        if (latitudeMainInput) latitudeMainInput.value = lat;
        if (longitudeMainInput) longitudeMainInput.value = lon;
        if (latitudeInput) latitudeInput.value = lat;
        if (longitudeInput) longitudeInput.value = lon;
        if (radiusInput && !radiusInput.value) radiusInput.value = '25';
        if (radiusMainInput) radiusMainInput.value = radiusInput && radiusInput.value ? radiusInput.value : '25';
        if (sortSelect && (!sortSelect.value || sortSelect.value === 'popular')) sortSelect.value = 'distance';
        hideSuggestions();
    };
 
    const renderSuggestions = (suggestions) => {
        if (!suggestionsContainer) return;
        suggestionsContainer.innerHTML = ''; suggestionFocus = -1;
        suggestions.forEach(s => {
            const item = document.createElement('div');
            item.className = 'hero-suggestion-item';
            item.innerHTML = '<div style="display:flex;align-items:flex-start;gap:10px"><i class="fas fa-map-marker-alt" style="color:#f97316;margin-top:3px"></i><div><div style="font-weight:600;color:#111827">' + s.label + '</div>' + (s.meta ? '<div style="font-size:12px;color:#6b7280;margin-top:2px">' + s.meta + '</div>' : '') + '</div></div>';
            item.addEventListener('click', () => selectLocation(s.label, s.latitude, s.longitude));
            suggestionsContainer.appendChild(item);
        });
        suggestionsContainer.classList.remove('hidden');
    };
 
    const fetchSuggestions = (query) => {
        const isAddr = /[0-9,]/.test(query) || query.trim().split(/\s+/).length >= 2;
        const local = fetch('/api/public/geolocation/cities?search=' + encodeURIComponent(query) + '&limit=6').then(r => r.json()).then(d => d.success && Array.isArray(d.data) ? d.data : []).catch(() => []);
        const nominatim = fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query) + '&limit=8&addressdetails=1&countrycodes=fr').then(r => r.json()).then(d => Array.isArray(d) ? d : []).catch(() => []);
        Promise.all([local, nominatim]).then(([lc, nm]) => {
            const normL = lc.map(c => ({ label: c.text, meta: 'Ville', latitude: c.latitude || c.lat || '', longitude: c.longitude || c.lng || '' }));
            const normN = nm.map(i => { const p = String(i.display_name||'').split(',').map(s=>s.trim()).filter(Boolean); const short = p.slice(0, Math.min(p.length, isAddr?5:3)).join(', '); const cat = i.type ? String(i.type).replace(/_/g,' ') : 'Adresse'; return { label: short, meta: cat.charAt(0).toUpperCase()+cat.slice(1), latitude: i.lat||'', longitude: i.lon||'' }; });
            const ordered = isAddr ? [...normN,...normL] : [...normL,...normN];
            const unique = []; const seen = new Set();
            ordered.forEach(s => { const k = (s.label+'|'+s.latitude+'|'+s.longitude).toLowerCase(); if (s.label && !seen.has(k)) { seen.add(k); unique.push(s); } });
            if (unique.length === 0) { hideSuggestions(); return; }
            renderSuggestions(unique.slice(0,8));
        }).catch(() => hideSuggestions());
    };
 
    if (cityInput) {
        cityInput.addEventListener('input', () => {
            if (cityFilterInput) cityFilterInput.value = cityInput.value;
            [latitudeMainInput, longitudeMainInput, latitudeInput, longitudeInput].forEach(i => { if (i) i.value = ''; });
            const q = cityInput.value.trim(); clearTimeout(debounceHandle);
            if (q.length < 2) { hideSuggestions(); return; }
            debounceHandle = setTimeout(() => fetchSuggestions(q), 250);
        });
        cityInput.addEventListener('keydown', e => {
            const items = suggestionsContainer ? suggestionsContainer.querySelectorAll('.hero-suggestion-item') : [];
            if (e.key === 'ArrowDown' && items.length) { e.preventDefault(); suggestionFocus = (suggestionFocus+1) % items.length; markActive(); }
            else if (e.key === 'ArrowUp' && items.length) { e.preventDefault(); suggestionFocus = suggestionFocus<=0 ? items.length-1 : suggestionFocus-1; markActive(); }
            else if (e.key === 'Enter' && suggestionFocus>-1 && items[suggestionFocus]) { e.preventDefault(); items[suggestionFocus].click(); }
            else if (e.key === 'Escape') hideSuggestions();
        });
    }
 
    if (gpsButton) {
        gpsButton.addEventListener('click', () => {
            if (!navigator.geolocation) { alert('Géolocalisation non disponible.'); return; }
            const orig = gpsButton.innerHTML; gpsButton.disabled = true; gpsButton.innerHTML = '...';
            navigator.geolocation.getCurrentPosition(pos => {
                const lat = pos.coords.latitude, lon = pos.coords.longitude;
                [latitudeMainInput,latitudeInput].forEach(i=>{if(i)i.value=lat});
                [longitudeMainInput,longitudeInput].forEach(i=>{if(i)i.value=lon});
                if (radiusInput && !radiusInput.value) radiusInput.value = '25';
                if (radiusMainInput) radiusMainInput.value = radiusInput && radiusInput.value ? radiusInput.value : '25';
                if (sortSelect && (!sortSelect.value || sortSelect.value === 'popular')) sortSelect.value = 'distance';
                fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat='+lat+'&lon='+lon+'&accept-language=fr')
                    .then(r=>r.json()).then(d=>{ const a=d.address||{}; const c=a.city||a.town||a.village||a.municipality||''; const p=a.postcode||''; selectLocation(c?c+(p?' ('+p+')':''):lat.toFixed(4)+', '+lon.toFixed(4),lat,lon); })
                    .catch(()=>selectLocation(lat.toFixed(4)+', '+lon.toFixed(4),lat,lon))
                    .finally(()=>{ gpsButton.disabled=false; gpsButton.innerHTML=orig; });
            }, ()=>{ gpsButton.disabled=false; gpsButton.innerHTML=orig; alert('Position indisponible.'); }, { enableHighAccuracy:true, timeout:10000, maximumAge:300000 });
        });
    }
 
    if (sortSelect) sortSelect.addEventListener('change', () => { ensureDistanceCtx(); if (filterForm) { typeof filterForm.requestSubmit==='function' ? filterForm.requestSubmit() : filterForm.submit(); } });
    if (radiusInput) radiusInput.addEventListener('change', () => { if (radiusMainInput) radiusMainInput.value=radiusInput.value; if (radiusInput.value && sortSelect && (!sortSelect.value||sortSelect.value==='popular')) sortSelect.value='distance'; });
 
    if (cityInput && cityFilterInput) cityFilterInput.value = cityInput.value;
    if (latitudeMainInput && latitudeInput) latitudeMainInput.value = latitudeInput.value;
    if (longitudeMainInput && longitudeInput) longitudeMainInput.value = longitudeInput.value;
    if (radiusMainInput && radiusInput) radiusMainInput.value = radiusInput.value || radiusMainInput.value;
 
    const hasFilters = {{ request()->anyFilled(['search','category','price_max','city','radius','available_now','with_delivery','available_date']) ? 'true' : 'false' }};
    if (hasFilters && panel) panel.classList.add('open');
 
    document.addEventListener('click', e => {
        if (panel && filterBtn && panel.classList.contains('open') && !panel.contains(e.target) && !filterBtn.contains(e.target)) panel.classList.remove('open');
        if (cityInput && suggestionsContainer && !cityInput.contains(e.target) && !suggestionsContainer.contains(e.target)) hideSuggestions();
    });
});
</script>
@endsection