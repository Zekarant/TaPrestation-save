@extends('layouts.client')
 
@section('title', 'Mes Factures')
 
@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    .tp-invoices { font-family:'Plus Jakarta Sans',system-ui,sans-serif; background:#faf7f2; min-height:100vh; }
 
    /* Hero */
    .tp-inv-hero { position:relative; overflow:hidden; padding:2rem 0 3.5rem; }
    .tp-inv-hero::before { content:''; position:absolute; inset:0; background:linear-gradient(140deg,#d97706 0%,#f59e0b 50%,#fbbf24 100%); z-index:0; }
    .tp-inv-hero::after { content:''; position:absolute; bottom:-1px; left:-5%; right:-5%; height:40px; background:#faf7f2; border-radius:50% 50% 0 0/100% 100% 0 0; z-index:2; }
    .tp-inv-hero .hero-inner { position:relative; z-index:1; }
    .tp-inv-hero h1 { font-size:1.5rem; font-weight:800; color:#fff; margin:0; text-shadow:0 1px 2px rgba(0,0,0,.1); }
    .tp-inv-hero p { font-size:.8rem; color:rgba(255,255,255,.8); margin:.25rem 0 0; }
    .tp-inv-hero .btn-back {
        display:inline-flex; align-items:center; gap:6px;
        padding:8px 14px; background:rgba(255,255,255,.18); color:#fff;
        font-size:12px; font-weight:700; border-radius:10px; text-decoration:none;
        backdrop-filter:blur(6px); border:1px solid rgba(255,255,255,.25); transition:background .15s;
    }
    .tp-inv-hero .btn-back:hover { background:rgba(255,255,255,.30); }
    .tp-inv-hero .btn-back svg { width:16px; height:16px; }
 
    /* Stat grid */
    .tp-inv-stats { display:grid; grid-template-columns:repeat(2,1fr); gap:.5rem; }
    @media(min-width:768px) { .tp-inv-stats { grid-template-columns:repeat(4,1fr); gap:.75rem; } }
    .tp-inv-stat {
        background:#fff; border-radius:12px; padding:.75rem 1rem;
        box-shadow:0 1px 6px rgba(0,0,0,.04);
        display:flex; align-items:center; gap:.625rem;
    }
    .tp-inv-stat-icon {
        width:36px; height:36px; border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        font-size:16px; flex-shrink:0;
    }
    .tp-inv-stat-label { font-size:10px; color:#8b92a8; font-weight:600; text-transform:uppercase; letter-spacing:.02em; }
    .tp-inv-stat-value { font-size:1.125rem; font-weight:800; color:#1a1f36; line-height:1.1; }
 
    /* Filter bar */
    .tp-inv-filters {
        background:#fff; border-radius:12px; padding:.75rem;
        box-shadow:0 1px 6px rgba(0,0,0,.04);
        border:1px solid rgba(15,58,134,.06);
    }
    .tp-inv-filters form { display:flex; flex-wrap:wrap; gap:.5rem; align-items:center; }
    .tp-inv-filters input,
    .tp-inv-filters select {
        padding:8px 12px; border:1px solid rgba(15,58,134,.1);
        border-radius:8px; font-size:12px; font-weight:500;
        font-family:'Plus Jakarta Sans',system-ui,sans-serif;
        background:#faf7f2; color:#1a1f36; transition:border-color .15s;
    }
    .tp-inv-filters input:focus,
    .tp-inv-filters select:focus { outline:none; border-color:#d97706; box-shadow:0 0 0 2px rgba(217,119,6,.12); }
    .tp-inv-filters input { flex:1; min-width:180px; }
    .tp-inv-filter-btn {
        padding:8px 16px; background:#d97706; color:#fff;
        border:none; border-radius:8px; font-size:12px; font-weight:700;
        cursor:pointer; transition:background .15s;
        font-family:'Plus Jakarta Sans',system-ui,sans-serif;
    }
    .tp-inv-filter-btn:hover { background:#b45309; }
 
    /* Card container */
    .tp-inv-card {
        background:#fff; border-radius:14px;
        box-shadow:0 1px 8px rgba(0,0,0,.04);
        border:1px solid rgba(15,58,134,.06); overflow:hidden;
    }
 
    /* Invoice row */
    .tp-inv-row {
        display:flex; align-items:center; justify-content:space-between; gap:1rem;
        padding:1rem 1.25rem; border-bottom:1px solid rgba(15,58,134,.04);
        text-decoration:none; color:inherit; transition:background .12s;
    }
    .tp-inv-row:last-child { border-bottom:none; }
    .tp-inv-row:hover { background:rgba(217,119,6,.03); }
 
    .tp-inv-row-left { display:flex; align-items:center; gap:.75rem; flex:1; min-width:0; }
    .tp-inv-row-icon {
        width:40px; height:40px; border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        font-size:18px; flex-shrink:0;
    }
    .tp-inv-row-icon.paid { background:#d1fae5; }
    .tp-inv-row-icon.refunded { background:#ffedd5; }
    .tp-inv-row-icon.issued { background:#dbeafe; }
 
    .tp-inv-row-info { min-width:0; }
    .tp-inv-row-number { font-size:13px; font-weight:800; color:#1a1f36; margin:0; }
    .tp-inv-row-desc {
        font-size:11px; color:#5a6178; margin:.125rem 0; font-weight:500;
        overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:260px;
    }
    @media(min-width:640px) { .tp-inv-row-desc { max-width:none; } }
    .tp-inv-row-meta { font-size:10px; color:#8b92a8; margin:0; }
 
    .tp-inv-row-right { text-align:right; flex-shrink:0; }
    .tp-inv-row-amount { font-size:1rem; font-weight:800; color:#1a1f36; margin:0; }
 
    /* Badge */
    .tp-inv-badge {
        display:inline-flex; padding:2px 8px; border-radius:99px;
        font-size:9px; font-weight:700; margin-top:3px;
    }
    .tp-inv-badge-paid { background:#d1fae5; color:#065f46; }
    .tp-inv-badge-issued { background:#dbeafe; color:#1e40af; }
    .tp-inv-badge-refunded { background:#ffedd5; color:#9a3412; }
 
    /* Chevron */
    .tp-inv-chevron {
        width:16px; height:16px; color:#c4b5fd; flex-shrink:0;
        opacity:0; transition:opacity .15s;
    }
    .tp-inv-row:hover .tp-inv-chevron { opacity:1; }
 
    /* Empty */
    .tp-inv-empty { padding:3rem 1.5rem; text-align:center; }
    .tp-inv-empty-icon { font-size:3rem; margin-bottom:.5rem; }
    .tp-inv-empty p { font-size:.85rem; color:#8b92a8; margin:0; }
 
    /* Pagination */
    .tp-inv-pagination { padding:1rem 1.25rem; border-top:1px solid rgba(15,58,134,.06); }
 
    /* Mobile */
    @media(max-width:640px) {
        .tp-inv-hero { padding:1.25rem 0 2.5rem; }
        .tp-inv-hero h1 { font-size:1.125rem; }
        .tp-inv-row { padding:.75rem; flex-wrap:wrap; }
        .tp-inv-row-right { width:100%; text-align:left; display:flex; align-items:center; justify-content:space-between; margin-top:.375rem; padding-top:.375rem; border-top:1px solid rgba(15,58,134,.04); }
        .tp-inv-chevron { display:none; }
    }
</style>
@endpush
 
@section('content')
<div class="tp-invoices">
 
    {{-- Hero --}}
    <section class="tp-inv-hero">
        <div class="hero-inner max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1>📄 Mes Factures</h1>
                    <p>Retrouvez toutes vos factures d'achat</p>
                </div>
                <a href="{{ route('client.dashboard') }}" class="btn-back">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Retour
                </a>
            </div>
        </div>
    </section>
 
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10 pb-12">
 
        {{-- Stats --}}
        <div class="tp-inv-stats mb-5">
            <div class="tp-inv-stat">
                <div class="tp-inv-stat-icon" style="background:#fef3c7;">📋</div>
                <div>
                    <div class="tp-inv-stat-label">Factures</div>
                    <div class="tp-inv-stat-value">{{ $stats['total_count'] }}</div>
                </div>
            </div>
            <div class="tp-inv-stat">
                <div class="tp-inv-stat-icon" style="background:#d1fae5;">💰</div>
                <div>
                    <div class="tp-inv-stat-label">Total dépensé</div>
                    <div class="tp-inv-stat-value" style="color:#059669;">{{ number_format($stats['total_amount'], 0, ',', ' ') }}€</div>
                </div>
            </div>
            <div class="tp-inv-stat">
                <div class="tp-inv-stat-icon" style="background:#dbeafe;">📅</div>
                <div>
                    <div class="tp-inv-stat-label">Ce mois</div>
                    <div class="tp-inv-stat-value" style="color:#2563eb;">{{ number_format($stats['this_month'], 0, ',', ' ') }}€</div>
                </div>
            </div>
            <div class="tp-inv-stat">
                <div class="tp-inv-stat-icon" style="background:#ede9fe;">📊</div>
                <div>
                    <div class="tp-inv-stat-label">Cette année</div>
                    <div class="tp-inv-stat-value" style="color:#7c3aed;">{{ number_format($stats['this_year'], 0, ',', ' ') }}€</div>
                </div>
            </div>
        </div>
 
        {{-- Filtres --}}
        <div class="tp-inv-filters mb-5">
            <form method="GET">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher une facture...">
                <select name="status">
                    <option value="">Tous les statuts</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Payée</option>
                    <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>En attente</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Remboursée</option>
                </select>
                <select name="period">
                    <option value="">Toutes les périodes</option>
                    <option value="month" {{ request('period') === 'month' ? 'selected' : '' }}>Ce mois</option>
                    <option value="quarter" {{ request('period') === 'quarter' ? 'selected' : '' }}>Ce trimestre</option>
                    <option value="year" {{ request('period') === 'year' ? 'selected' : '' }}>Cette année</option>
                </select>
                <button type="submit" class="tp-inv-filter-btn">Filtrer</button>
            </form>
        </div>
 
        {{-- Liste --}}
        <div class="tp-inv-card">
            @forelse($invoices as $invoice)
                @php
                    $st = $invoice->status ?? 'issued';
                    $iconClass = match($st) { 'paid'=>'paid','refunded'=>'refunded', default=>'issued' };
                    $icon = match($st) { 'paid'=>'✅','refunded'=>'↩️', default=>'⏳' };
                    $badgeClass = match($st) { 'paid'=>'tp-inv-badge-paid','refunded'=>'tp-inv-badge-refunded', default=>'tp-inv-badge-issued' };
                @endphp
                <a href="{{ route('client.invoices.show', $invoice) }}" class="tp-inv-row">
                    <div class="tp-inv-row-left">
                        <div class="tp-inv-row-icon {{ $iconClass }}">{{ $icon }}</div>
                        <div class="tp-inv-row-info">
                            <p class="tp-inv-row-number">{{ $invoice->invoice_number }}</p>
                            <p class="tp-inv-row-desc">{{ $invoice->description }}</p>
                            <p class="tp-inv-row-meta">{{ $invoice->issued_at?->format('d/m/Y') }} · {{ $invoice->seller_name }}</p>
                        </div>
                    </div>
                    <div class="tp-inv-row-right">
                        <p class="tp-inv-row-amount">{{ number_format($invoice->total, 2, ',', ' ') }} €</p>
                        <span class="tp-inv-badge {{ $badgeClass }}">{{ $invoice->status_label }}</span>
                    </div>
                    <svg class="tp-inv-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @empty
                <div class="tp-inv-empty">
                    <div class="tp-inv-empty-icon">📭</div>
                    <p>Aucune facture trouvée</p>
                </div>
            @endforelse
        </div>
 
        {{-- Pagination --}}
        @if($invoices->hasPages())
            <div class="tp-inv-pagination mt-4">
                {{ $invoices->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection