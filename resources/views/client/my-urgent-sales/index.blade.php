@extends('layouts.app')
 
@section('title', 'Mes annonces vente flash')
 
@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">
 
<style>
/* ═══ SCOPED — no wildcard reset on border ═══ */
div.tp-ms {
    font-family: 'DM Sans', system-ui, -apple-system, sans-serif !important;
    background: #f6f2ec !important;
    min-height: 100vh;
    padding-bottom: calc(85px + env(safe-area-inset-bottom, 0px));
    color: #1c1917 !important;
    -webkit-font-smoothing: antialiased;
    line-height: 1.5 !important;
}
 
/* ═══ HERO ═══ */
div.tp-ms section.ms-hero {
    position: relative !important;
    overflow: hidden !important;
    padding: 1.75rem 1rem 2.75rem !important;
    margin: 0 !important;
    background: linear-gradient(145deg, #c2410c 0%, #ea580c 35%, #f97316 70%, #fdba74 100%) !important;
    display: block !important;
}
div.tp-ms section.ms-hero::after {
    content: '' !important;
    position: absolute !important;
    bottom: -1px !important;
    left: -5% !important;
    right: -5% !important;
    height: 32px !important;
    background: #f6f2ec !important;
    border-radius: 50% 50% 0 0 / 100% 100% 0 0 !important;
    z-index: 2 !important;
    border: none !important;
}
div.tp-ms .ms-hero-wrap {
    position: relative !important;
    z-index: 1 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    max-width: 56rem !important;
    margin: 0 auto !important;
}
div.tp-ms h1.ms-hero-title {
    font-size: 1.25rem !important;
    font-weight: 800 !important;
    color: #fff !important;
    letter-spacing: -0.02em !important;
    line-height: 1.2 !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
}
div.tp-ms p.ms-hero-sub {
    font-size: .8rem !important;
    color: rgba(255,255,255,.65) !important;
    margin: .2rem 0 0 !important;
    padding: 0 !important;
    font-weight: 500 !important;
}
div.tp-ms a.ms-back {
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
    padding: 7px 14px !important;
    background: rgba(255,255,255,.15) !important;
    backdrop-filter: blur(8px) !important;
    color: #fff !important;
    font-size: .6875rem !important;
    font-weight: 700 !important;
    border-radius: 10px !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    flex-shrink: 0 !important;
    border: none !important;
}
div.tp-ms a.ms-back svg {
    width: 14px !important;
    height: 14px !important;
    display: block !important;
}
 
/* ═══ CONTENT ═══ */
div.tp-ms .ms-content {
    max-width: 56rem !important;
    margin: -14px auto 0 !important;
    padding: 0 .75rem !important;
    position: relative !important;
    z-index: 10 !important;
}
 
/* ═══ ALERTS ═══ */
div.tp-ms .ms-alert {
    padding: .5rem .75rem !important;
    border-radius: 10px !important;
    font-size: .75rem !important;
    font-weight: 600 !important;
    margin-bottom: .5rem !important;
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    border: none !important;
}
div.tp-ms .ms-alert-ok { background: #ecfdf5 !important; color: #065f46 !important; }
div.tp-ms .ms-alert-err { background: #fef2f2 !important; color: #991b1b !important; }
 
/* ═══ STATS BAR ═══ */
div.tp-ms .ms-stats {
    background: #fff !important;
    border-radius: 14px !important;
    box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 12px rgba(0,0,0,.03) !important;
    padding: .75rem 1rem !important;
    margin-bottom: .625rem !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: .5rem !important;
    border: none !important;
}
div.tp-ms .ms-stats-label {
    font-size: .625rem !important;
    color: #a8a29e !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: .04em !important;
    margin: 0 !important;
}
div.tp-ms .ms-stats-val {
    font-size: 1.375rem !important;
    font-weight: 800 !important;
    color: #1c1917 !important;
    letter-spacing: -.03em !important;
    line-height: 1.1 !important;
    margin: 2px 0 !important;
}
div.tp-ms .ms-stats-hint { font-size: .6875rem !important; font-weight: 700 !important; margin: 0 !important; }
div.tp-ms .ms-stats-hint.ok { color: #059669 !important; }
div.tp-ms .ms-stats-hint.full { color: #ea580c !important; }
 
/* ═══ SHARED BTN ═══ */
div.tp-ms a.ms-btn {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 9px 16px !important;
    border-radius: 10px !important;
    font-size: .75rem !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    cursor: pointer !important;
    font-family: 'DM Sans', system-ui, sans-serif !important;
    box-shadow: 0 1px 3px rgba(0,0,0,.08) !important;
    line-height: 1.4 !important;
    white-space: nowrap !important;
    border: none !important;
}
div.tp-ms a.ms-btn i { font-size: .6875rem !important; }
div.tp-ms a.ms-btn-green { background: #059669 !important; color: #fff !important; }
div.tp-ms a.ms-btn-violet { background: #7c3aed !important; color: #fff !important; }
 
/* ═══ PROMO ═══ */
div.tp-ms .ms-promo {
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 60%, #5b21b6 100%) !important;
    border-radius: 14px !important;
    padding: 1rem 1.125rem !important;
    color: #fff !important;
    margin-bottom: .625rem !important;
    display: flex !important;
    align-items: flex-start !important;
    gap: .75rem !important;
    box-shadow: 0 4px 16px rgba(124,58,237,.2) !important;
    border: none !important;
}
div.tp-ms .ms-promo-emoji { font-size: 1.5rem !important; flex-shrink: 0 !important; }
div.tp-ms .ms-promo h3 { font-size: .8125rem !important; font-weight: 800 !important; margin: 0 0 .25rem !important; color: #fff !important; border: none !important; }
div.tp-ms .ms-promo p { font-size: .75rem !important; color: rgba(255,255,255,.75) !important; margin: 0 0 .5rem !important; line-height: 1.5 !important; }
div.tp-ms a.ms-promo-link {
    display: inline-block !important;
    padding: 6px 14px !important;
    background: #fff !important;
    color: #7c3aed !important;
    font-size: .6875rem !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    text-decoration: none !important;
    border: none !important;
}
 
/* ═══ LIST ═══ */
div.tp-ms .ms-list {
    background: #fff !important;
    border-radius: 14px !important;
    box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 4px 12px rgba(0,0,0,.03) !important;
    overflow: hidden !important;
    border: none !important;
}
 
/* ═══ ITEM ═══ */
div.tp-ms .ms-item {
    display: flex !important;
    align-items: stretch !important;
    background: transparent !important;
    border: none !important;
}
div.tp-ms .ms-item + .ms-item {
    box-shadow: inset 0 1px 0 0 rgba(28,25,23,.05) !important;
}
 
div.tp-ms .ms-item-img {
    width: 100px !important;
    min-height: 100px !important;
    flex-shrink: 0 !important;
    background: #f0ebe4 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    overflow: hidden !important;
    position: relative !important;
    padding: 0 !important;
    margin: 0 !important;
    border: none !important;
}
div.tp-ms .ms-item-img img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block !important;
    margin: 0 !important;
    padding: 0 !important;
    border-radius: 0 !important;
    border: none !important;
    position: absolute !important;
    inset: 0 !important;
}
div.tp-ms .ms-item-img i { font-size: 1.25rem !important; color: #c4b5a0 !important; }
 
div.tp-ms .ms-item-body {
    flex: 1 !important;
    padding: .625rem .75rem !important;
    min-width: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    gap: 3px !important;
}
div.tp-ms .ms-item-top {
    display: flex !important;
    align-items: flex-start !important;
    justify-content: space-between !important;
    gap: .375rem !important;
}
div.tp-ms .ms-item-info { min-width: 0 !important; flex: 1 !important; }
div.tp-ms p.ms-item-title {
    font-size: .8125rem !important;
    font-weight: 700 !important;
    color: #1c1917 !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    line-height: 1.3 !important;
    margin: 0 !important;
    padding: 0 !important;
}
div.tp-ms p.ms-item-price {
    font-size: .875rem !important;
    font-weight: 800 !important;
    color: #059669 !important;
    letter-spacing: -.02em !important;
    line-height: 1.2 !important;
    margin: 1px 0 0 !important;
    padding: 0 !important;
}
 
div.tp-ms span.ms-badge {
    display: inline-flex !important;
    align-items: center !important;
    padding: 2px 8px !important;
    border-radius: 99px !important;
    font-size: .5625rem !important;
    font-weight: 700 !important;
    flex-shrink: 0 !important;
    letter-spacing: .02em !important;
    line-height: 1.4 !important;
    white-space: nowrap !important;
    border: none !important;
}
div.tp-ms .ms-badge-active { background: #ecfdf5 !important; color: #065f46 !important; }
div.tp-ms .ms-badge-sold { background: #eff6ff !important; color: #1e40af !important; }
div.tp-ms .ms-badge-other { background: #f5f5f4 !important; color: #57534e !important; }
 
div.tp-ms .ms-item-meta {
    display: flex !important;
    gap: .5rem !important;
    font-size: .6875rem !important;
    color: #a8a29e !important;
    font-weight: 500 !important;
    margin: 0 !important;
    padding: 0 !important;
}
div.tp-ms .ms-item-meta i { margin-right: 2px !important; font-size: .5625rem !important; }
 
/* ═══ ACTIONS — inline, compact, NO borders ═══ */
div.tp-ms .ms-item-acts {
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    margin-top: 5px !important;
    flex-wrap: nowrap !important;
}
div.tp-ms .ms-item-acts form {
    display: inline-flex !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
}
 
/* ── Modifier (link) ── */
div.tp-ms a.ms-act-edit {
    display: inline-flex !important;
    align-items: center !important;
    padding: 5px 10px !important;
    border-radius: 6px !important;
    font-size: .6875rem !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    font-family: 'DM Sans', system-ui, sans-serif !important;
    line-height: 1.3 !important;
    white-space: nowrap !important;
    cursor: pointer !important;
    background: #dbeafe !important;
    color: #1e40af !important;
    border: none !important;
    border-width: 0 !important;
    outline: none !important;
    box-shadow: none !important;
    margin: 0 !important;
    -webkit-appearance: none !important;
    appearance: none !important;
}
 
/* ── Vendu (button) ── */
div.tp-ms button.ms-act-sold {
    display: inline-flex !important;
    align-items: center !important;
    padding: 5px 10px !important;
    border-radius: 6px !important;
    font-size: .6875rem !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    font-family: 'DM Sans', system-ui, sans-serif !important;
    line-height: 1.3 !important;
    white-space: nowrap !important;
    cursor: pointer !important;
    background: #d1fae5 !important;
    color: #065f46 !important;
    border: none !important;
    border-width: 0 !important;
    outline: none !important;
    box-shadow: none !important;
    margin: 0 !important;
    -webkit-appearance: none !important;
    appearance: none !important;
}
 
/* ── Supprimer (button) ── */
div.tp-ms button.ms-act-del {
    display: inline-flex !important;
    align-items: center !important;
    padding: 5px 10px !important;
    border-radius: 6px !important;
    font-size: .6875rem !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    font-family: 'DM Sans', system-ui, sans-serif !important;
    line-height: 1.3 !important;
    white-space: nowrap !important;
    cursor: pointer !important;
    background: #fee2e2 !important;
    color: #991b1b !important;
    border: none !important;
    border-width: 0 !important;
    outline: none !important;
    box-shadow: none !important;
    margin: 0 !important;
    -webkit-appearance: none !important;
    appearance: none !important;
}
 
/* Separator dot */
div.tp-ms span.ms-act-sep {
    display: none !important;
}
 
/* ═══ EMPTY ═══ */
div.tp-ms .ms-empty { padding: 2.5rem 1.25rem !important; text-align: center !important; }
div.tp-ms .ms-empty-emoji { font-size: 2.5rem !important; margin-bottom: .5rem !important; display: block !important; }
div.tp-ms .ms-empty h3 { font-size: .9375rem !important; font-weight: 700 !important; color: #1c1917 !important; margin: 0 0 .25rem !important; border: none !important; }
div.tp-ms .ms-empty p { font-size: .8125rem !important; color: #78716c !important; margin: 0 0 1rem !important; }
 
div.tp-ms .ms-pagination { margin-top: .75rem !important; }
 
/* ═══ SMALL MOBILE ═══ */
@media (max-width: 420px) {
    div.tp-ms section.ms-hero { padding: 1.25rem .75rem 2.25rem !important; }
    div.tp-ms h1.ms-hero-title { font-size: 1.0625rem !important; }
    div.tp-ms .ms-item-img { width: 88px !important; min-height: 88px !important; }
    div.tp-ms .ms-item-body { padding: .5rem .625rem !important; }
    div.tp-ms p.ms-item-title { font-size: .75rem !important; }
    div.tp-ms p.ms-item-price { font-size: .8125rem !important; }
}
</style>
@endpush
 
@section('content')
<div class="tp-ms">
 
    <section class="ms-hero">
        <div class="ms-hero-wrap">
            <div>
                <h1 class="ms-hero-title">⚡ Mes annonces flash</h1>
                <p class="ms-hero-sub">Gérez vos ventes rapides</p>
            </div>
            <a href="{{ route('client.dashboard') }}" class="ms-back">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Retour
            </a>
        </div>
    </section>
 
    <div class="ms-content">
 
        @if(session('success'))
            <div class="ms-alert ms-alert-ok">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="ms-alert ms-alert-err">{{ session('error') }}</div>
        @endif
 
        <div class="ms-stats">
            <div>
                <div class="ms-stats-label">Annonces actives</div>
                <div class="ms-stats-val">{{ $activeCount }} / 5</div>
                @if($remainingSlots > 0)
                    <div class="ms-stats-hint ok">{{ $remainingSlots }} dispo</div>
                @else
                    <div class="ms-stats-hint full">Limite atteinte</div>
                @endif
            </div>
            @if($remainingSlots > 0)
                <a href="{{ route('client.my-urgent-sales.create') }}" class="ms-btn ms-btn-green">
                    <i class="fas fa-plus"></i> Nouvelle
                </a>
            @else
                <a href="{{ Route::has('prestataire.register') ? route('prestataire.register') : url('/prestataire/register') }}" class="ms-btn ms-btn-violet">
                    <i class="fas fa-star"></i> Devenir prestataire
                </a>
            @endif
        </div>
 
        @if($remainingSlots == 0)
        <div class="ms-promo">
            <div class="ms-promo-emoji">⭐</div>
            <div>
                <h3>Devenez prestataire !</h3>
                <p>Annonces illimitées, paiements en ligne, gestion d'inventaire.</p>
                <a href="{{ Route::has('prestataire.register') ? route('prestataire.register') : url('/prestataire/register') }}" class="ms-promo-link">Créer mon profil →</a>
            </div>
        </div>
        @endif
 
        @if($urgentSales->count() > 0)
            <div class="ms-list">
                @foreach($urgentSales as $sale)
                <div class="ms-item">
                    <div class="ms-item-img">
                        @if($sale->photos && count($sale->photos) > 0)
                            <img src="{{ asset('storage/' . $sale->photos[0]) }}" alt="{{ $sale->title }}" loading="lazy">
                        @else
                            <i class="fas fa-image"></i>
                        @endif
                    </div>
                    <div class="ms-item-body">
                        <div class="ms-item-top">
                            <div class="ms-item-info">
                                <p class="ms-item-title">{{ $sale->title }}</p>
                                <p class="ms-item-price">{{ number_format($sale->price, 2) }} €</p>
                            </div>
                            @if($sale->status == 'active')
                                <span class="ms-badge ms-badge-active">Actif</span>
                            @elseif($sale->status == 'sold')
                                <span class="ms-badge ms-badge-sold">Vendu</span>
                            @else
                                <span class="ms-badge ms-badge-other">{{ ucfirst($sale->status) }}</span>
                            @endif
                        </div>
                        <div class="ms-item-meta">
                            <span><i class="fas fa-eye"></i> {{ $sale->views_count ?? 0 }}</span>
                            <span><i class="fas fa-envelope"></i> {{ $sale->contact_count ?? 0 }}</span>
                        </div>
                        <div class="ms-item-acts">
                            <a href="{{ route('client.my-urgent-sales.edit', $sale) }}" class="ms-act-edit" style="display:inline-flex;padding:5px 10px;border-radius:6px;font-size:11px;font-weight:700;background:#dbeafe;color:#1e40af;text-decoration:none;border:none">Modifier</a>
                            @if($sale->status == 'active')
                            <form action="{{ route('client.my-urgent-sales.mark-sold', $sale) }}" method="POST" style="display:inline;margin:0;padding:0;border:none">
                                @csrf @method('PATCH')
                                <button type="submit" class="ms-act-sold" style="display:inline-flex;padding:5px 10px;border-radius:6px;font-size:11px;font-weight:700;background:#d1fae5;color:#065f46;cursor:pointer;border:none;font-family:inherit">Vendu</button>
                            </form>
                            @endif
                            <form action="{{ route('client.my-urgent-sales.destroy', $sale) }}" method="POST" style="display:inline;margin:0;padding:0;border:none" onsubmit="return confirm('Supprimer cette annonce ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="ms-act-del" style="display:inline-flex;padding:5px 10px;border-radius:6px;font-size:11px;font-weight:700;background:#fee2e2;color:#991b1b;cursor:pointer;border:none;font-family:inherit">Supprimer</button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="ms-pagination">{{ $urgentSales->links() }}</div>
        @else
            <div class="ms-list">
                <div class="ms-empty">
                    <span class="ms-empty-emoji">🏷️</span>
                    <h3>Aucune annonce</h3>
                    <p>Publiez votre première vente flash.</p>
                    @if($remainingSlots > 0)
                        <a href="{{ route('client.my-urgent-sales.create') }}" class="ms-btn ms-btn-green">Créer une annonce</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection