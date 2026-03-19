
Copier

@extends('layouts.app')
 
@push('styles')
<link rel="stylesheet" href="{{ asset('css/client-dashboard-v2.css') }}">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    .tp-escrow { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; background: #faf7f2; min-height: 100vh; }
 
    /* ── Hero ── */
    .tp-escrow-hero { position: relative; overflow: hidden; padding: 2rem 0 3.5rem; }
    .tp-escrow-hero::before { content:''; position:absolute; inset:0; background: linear-gradient(140deg, #7c3aed 0%, #8b5cf6 45%, #a78bfa 100%); z-index:0; }
    .tp-escrow-hero::after { content:''; position:absolute; bottom:-1px; left:-5%; right:-5%; height:40px; background:#faf7f2; border-radius:50% 50% 0 0 / 100% 100% 0 0; z-index:2; }
    .tp-escrow-hero .hero-inner { position:relative; z-index:1; }
    .tp-escrow-hero h1 { font-size:1.5rem; font-weight:800; color:#fff; margin:0; }
    .tp-escrow-hero p { font-size:.875rem; color:rgba(255,255,255,.75); margin:.25rem 0 0; }
    .tp-escrow-hero .btn-back {
        display:inline-flex; align-items:center; gap:6px;
        padding:8px 14px; background:rgba(255,255,255,.18); color:#fff;
        font-size:12px; font-weight:700; border-radius:10px; text-decoration:none;
        backdrop-filter:blur(6px); border:1px solid rgba(255,255,255,.2);
        transition: background .15s;
    }
    .tp-escrow-hero .btn-back:hover { background:rgba(255,255,255,.30); }
    .tp-escrow-hero .btn-back svg { width:16px; height:16px; }
 
    /* ── Stat cards ── */
    .tp-stat-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:.5rem; }
    @media(min-width:768px) { .tp-stat-grid { grid-template-columns:repeat(4,1fr); gap:.75rem; } }
    .tp-stat {
        background:#fff; border-radius:12px; padding:.875rem 1rem;
        box-shadow:0 1px 6px rgba(0,0,0,.04);
        display:flex; align-items:center; gap:.75rem;
        border-left:3px solid transparent;
    }
    .tp-stat-pending { border-left-color:#f59e0b; }
    .tp-stat-done { border-left-color:#059669; }
    .tp-stat-dispute { border-left-color:#dc2626; }
    .tp-stat-amount { border-left-color:#7c3aed; }
    .tp-stat-icon {
        width:36px; height:36px; border-radius:10px;
        display:flex; align-items:center; justify-content:center; flex-shrink:0;
    }
    .tp-stat-icon svg { width:18px; height:18px; }
    .tp-stat-label { font-size:11px; color:#8b92a8; font-weight:600; }
    .tp-stat-value { font-size:1.25rem; font-weight:800; color:#1a1f36; line-height:1.1; }
 
    /* ── Table card ── */
    .tp-card {
        background:#fff; border-radius:14px;
        box-shadow:0 1px 8px rgba(0,0,0,.04);
        border:1px solid rgba(15,58,134,.06); overflow:hidden;
    }
    .tp-card-header {
        padding:1rem 1.25rem; border-bottom:1px solid rgba(15,58,134,.06);
        font-size:1rem; font-weight:800; color:#1a1f36;
    }
 
    /* ── Transaction row ── */
    .tp-tx {
        padding:1rem 1.25rem; border-bottom:1px solid rgba(15,58,134,.04);
        transition: background .12s;
    }
    .tp-tx:last-child { border-bottom:none; }
    .tp-tx:hover { background:rgba(139,92,246,.03); }
    .tp-tx-top { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; }
    .tp-tx-left { display:flex; align-items:flex-start; gap:.75rem; flex:1; min-width:0; }
    .tp-tx-icon {
        width:40px; height:40px; border-radius:10px;
        display:flex; align-items:center; justify-content:center; flex-shrink:0;
    }
    .tp-tx-icon svg { width:20px; height:20px; }
    .tp-tx-title { font-size:13px; font-weight:700; color:#1a1f36; margin:0; }
    .tp-tx-title span { color:#8b92a8; font-weight:500; }
    .tp-tx-date { font-size:11px; color:#8b92a8; margin:.125rem 0 0; }
    .tp-tx-auto { font-size:11px; color:#d97706; margin:.25rem 0 0; display:flex; align-items:center; gap:3px; }
    .tp-tx-auto svg { width:13px; height:13px; }
    .tp-tx-amount { font-size:1.125rem; font-weight:800; color:#1a1f36; text-align:right; white-space:nowrap; }
    .tp-tx-deposit { font-size:11px; color:#8b92a8; font-weight:500; }
 
    /* Badges */
    .tp-badge {
        display:inline-flex; align-items:center; gap:3px;
        padding:3px 10px; border-radius:99px;
        font-size:10px; font-weight:700; margin-top:4px;
    }
    .tp-badge-pending { background:#fef3c7; color:#92400e; }
    .tp-badge-released { background:#d1fae5; color:#065f46; }
    .tp-badge-refunded { background:#dbeafe; color:#1e40af; }
    .tp-badge-partial-refund { background:#e0e7ff; color:#3730a3; }
    .tp-badge-disputed { background:#fee2e2; color:#991b1b; }
    .tp-badge-dispute-review { background:#ffedd5; color:#9a3412; }
    .tp-badge-cancelled { background:#f3f4f6; color:#4b5563; }
 
    /* Actions row */
    .tp-tx-actions {
        margin-top:.75rem; padding-top:.75rem; border-top:1px solid rgba(15,58,134,.04);
        display:flex; align-items:center; justify-content:flex-end; gap:.5rem; flex-wrap:wrap;
    }
    .tp-btn {
        display:inline-flex; align-items:center; gap:5px;
        padding:7px 14px; border-radius:8px;
        font-size:12px; font-weight:700; text-decoration:none;
        transition: all .15s; cursor:pointer; border:none;
    }
    .tp-btn svg { width:16px; height:16px; }
    .tp-btn-green { background:#059669; color:#fff; }
    .tp-btn-green:hover { background:#047857; }
    .tp-btn-outline { background:#fff; color:#5a6178; border:1px solid rgba(15,58,134,.12); }
    .tp-btn-outline:hover { border-color:rgba(15,58,134,.25); color:#1a1f36; }
    .tp-btn-red-outline { background:#fff; color:#dc2626; border:1px solid rgba(220,38,38,.2); }
    .tp-btn-red-outline:hover { background:#fef2f2; border-color:rgba(220,38,38,.35); }
    .tp-btn-blue { background:#7c3aed; color:#fff; }
    .tp-btn-blue:hover { background:#6d28d9; }
 
    /* Empty state */
    .tp-empty { padding:3rem 1.5rem; text-align:center; }
    .tp-empty svg { width:56px; height:56px; color:#c4b5fd; margin:0 auto .75rem; }
    .tp-empty h3 { font-size:1rem; font-weight:700; color:#5a6178; margin:0 0 .25rem; }
    .tp-empty p { font-size:.8rem; color:#8b92a8; margin:0; }
 
    /* Pagination */
    .tp-pagination { padding:1rem 1.25rem; border-top:1px solid rgba(15,58,134,.06); }
 
    /* Modal */
    .tp-modal-backdrop { background:rgba(15,23,42,.45)!important; backdrop-filter:blur(4px); }
    .tp-modal-panel {
        position:relative; z-index:1; width:min(100%,34rem);
        margin:2rem auto; padding:1.25rem;
        background:#fff; border-radius:16px;
        border:1px solid rgba(15,23,42,.08);
        box-shadow:0 24px 64px rgba(15,23,42,.2);
        text-align:left;
    }
    .tp-modal-head { margin-top:.75rem; text-align:center; }
    .tp-modal-title { margin:0; font-size:1.05rem; font-weight:800; color:#111827; }
    .tp-modal-copy { margin:.35rem 0 0; font-size:.9rem; color:#6b7280; }
    .tp-modal-fields { display:grid; gap:1rem; margin-top:1.25rem; }
    .tp-modal-label {
        display:block; margin-bottom:.4rem;
        font-size:.875rem; font-weight:700; color:#374151;
    }
    .tp-modal-fields select,
    .tp-modal-fields textarea,
    .tp-modal-fields input[type="file"] {
        width:100%;
        border:1px solid #d1d5db;
        border-radius:10px;
        background:#fff;
        color:#111827;
        font-size:.875rem;
    }
    .tp-modal-fields select,
    .tp-modal-fields textarea { padding:.75rem .875rem; }
    .tp-modal-fields input[type="file"] { padding:.625rem .75rem; }
    .tp-modal-actions { display:grid; gap:.75rem; margin-top:1.25rem; }
    .tp-modal-btn {
        display:inline-flex; align-items:center; justify-content:center;
        width:100%; min-height:44px; padding:.75rem 1rem;
        border-radius:12px; border:1px solid transparent;
        font-size:.9rem; font-weight:800; cursor:pointer;
        transition:background .15s,border-color .15s,color .15s;
    }
    .tp-modal-btn-danger { background:#dc2626; color:#fff; }
    .tp-modal-btn-danger:hover { background:#b91c1c; }
    .tp-modal-btn-neutral { background:#fff; color:#374151; border-color:#d1d5db; }
    .tp-modal-btn-neutral:hover { background:#f9fafb; }
 
    /* Mobile */
    @media(max-width:640px) {
        .tp-escrow-hero { padding:1.25rem 0 2.5rem; }
        .tp-escrow-hero h1 { font-size:1.125rem; }
        .tp-tx { padding:.75rem; }
        .tp-tx-top { flex-direction:column; gap:.5rem; }
        .tp-tx-amount { text-align:left; }
        .tp-tx-actions { justify-content:stretch; }
        .tp-tx-actions .tp-btn { flex:1; justify-content:center; }
    }
    @media(min-width:640px) {
        .tp-modal-actions { grid-template-columns:1fr 1fr; }
    }
</style>
@endpush
 
@section('content')
@php
    $fmtDt = static function ($v) { try { return $v ? \Carbon\Carbon::parse($v)->format('d/m/Y à H:i') : '—'; } catch (\Throwable $e) { return '—'; } };
    $fmtDiff = static function ($v) { try { return $v ? \Carbon\Carbon::parse($v)->diffForHumans() : '—'; } catch (\Throwable $e) { return '—'; } };
@endphp
 
<div class="tp-escrow" x-data="{ showDisputeModal: false, selectedEscrow: null }">
 
    {{-- Hero --}}
    <section class="tp-escrow-hero">
        <div class="hero-inner max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1>🔒 Mes Paiements Sécurisés</h1>
                    <p>Suivez vos transactions protégées</p>
                </div>
                <a href="{{ route('client.dashboard') }}" class="btn-back">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Retour
                </a>
            </div>
        </div>
    </section>
 
    {{-- Stats --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10 mb-6">
        <div class="tp-stat-grid">
            <div class="tp-stat tp-stat-pending">
                <div class="tp-stat-icon" style="background:#fef3c7;color:#d97706;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div><div class="tp-stat-label">En attente</div><div class="tp-stat-value">{{ $escrows->where('status','pending')->count() }}</div></div>
            </div>
            <div class="tp-stat tp-stat-done">
                <div class="tp-stat-icon" style="background:#d1fae5;color:#059669;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div><div class="tp-stat-label">Terminées</div><div class="tp-stat-value">{{ $escrows->where('status','released')->count() }}</div></div>
            </div>
            <div class="tp-stat tp-stat-dispute">
                <div class="tp-stat-icon" style="background:#fee2e2;color:#dc2626;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div><div class="tp-stat-label">Litiges</div><div class="tp-stat-value">{{ $escrows->whereIn('status',['disputed','dispute_review'])->count() }}</div></div>
            </div>
            <div class="tp-stat tp-stat-amount">
                <div class="tp-stat-icon" style="background:#ede9fe;color:#7c3aed;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div><div class="tp-stat-label">Total bloqué</div><div class="tp-stat-value">{{ number_format($escrows->where('status','pending')->sum('total_amount'),2) }} €</div></div>
            </div>
        </div>
    </section>
 
    {{-- Liste --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <div class="tp-card">
            <div class="tp-card-header">Vos transactions sécurisées</div>
 
            @if($escrows->isEmpty())
                <div class="tp-empty">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <h3>Aucune transaction sécurisée</h3>
                    <p>Vos prochains achats avec paiement sécurisé apparaîtront ici</p>
                </div>
            @else
                @foreach($escrows as $escrow)
                    @php
                        $eType = (string)($escrow->escrowable_type ?? '');
                        $eSt = (string)($escrow->status ?? '');
                        $eAmt = (float)($escrow->total_amount ?? $escrow->amount ?? 0);
                        $eDep = (float)($escrow->deposit_amount ?? 0);
                        $eConfirmed = $escrow->client_confirmed_at ?? null;
                        $eAuto = $escrow->auto_release_at ?? null;
 
                        $iconBg = str_contains($eType,'Booking') ? '#dbeafe' : (str_contains($eType,'Equipment') ? '#ede9fe' : (str_contains($eType,'UrgentSale') ? '#ffedd5' : (str_contains($eType,'FoodOrder') ? '#fef3c7' : '#f3f4f6')));
                        $iconColor = str_contains($eType,'Booking') ? '#2563eb' : (str_contains($eType,'Equipment') ? '#7c3aed' : (str_contains($eType,'UrgentSale') ? '#ea580c' : (str_contains($eType,'FoodOrder') ? '#d97706' : '#6b7280')));
                        $badgeClass = match($eSt) { 'pending'=>'tp-badge-pending','released'=>'tp-badge-released','refunded'=>'tp-badge-refunded','partially_refunded'=>'tp-badge-partial-refund','disputed'=>'tp-badge-disputed','dispute_review'=>'tp-badge-dispute-review','cancelled'=>'tp-badge-cancelled', default=>'tp-badge-pending' };
                        $badgeText = match($eSt) { 'pending'=>'🔒 En attente','released'=>'✅ Libéré','refunded'=>'💰 Remboursé','partially_refunded'=>'💸 Remb. partiel','disputed'=>'⚠️ Litige','dispute_review'=>'📁 Dossier litige','cancelled'=>'❌ Annulé', default=>ucfirst($eSt) };
                        $typeLabel = str_contains($eType,'Booking') ? 'Réservation de service' : (str_contains($eType,'Equipment') ? 'Location d\'équipement' : (str_contains($eType,'UrgentSale') ? 'Vente urgente' : (str_contains($eType,'FoodOrder') ? '🍽️ Commande food' : 'Transaction')));
                    @endphp
                    <div class="tp-tx">
                        <div class="tp-tx-top">
                            <div class="tp-tx-left">
                                <div class="tp-tx-icon" style="background:{{ $iconBg }};color:{{ $iconColor }};">
                                    @if(str_contains($eType,'Booking'))
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    @elseif(str_contains($eType,'Equipment'))
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    @elseif(str_contains($eType,'UrgentSale'))
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    @else
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="tp-tx-title">{{ $typeLabel }} <span>#{{ $escrow->id }}</span></p>
                                    <p class="tp-tx-date">{{ $fmtDt($escrow->created_at) }}</p>
                                    @if($eAuto && $eSt === 'pending')
                                        <p class="tp-tx-auto">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Libération auto : {{ $fmtDiff($eAuto) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="tp-tx-amount">{{ number_format($eAmt,2) }} €</div>
                                @if($eDep > 0)<div class="tp-tx-deposit">+ {{ number_format($eDep,2) }} € caution</div>@endif
                                <div class="tp-badge {{ $badgeClass }}">{{ $badgeText }}</div>
                            </div>
                        </div>
 
                        {{-- Actions --}}
                        @if($eSt === 'pending' && empty($eConfirmed))
                            @php
                                $isUS = str_contains($eType,'UrgentSale');
                                $confirmR = $isUS ? route('client.escrow.confirm-urgent-sale',$escrow->id) : route('client.escrow.confirm',$escrow->id);
                            @endphp
                            <div class="tp-tx-actions">
                                <form action="{{ $confirmR }}" method="POST" onsubmit="return confirm('Confirmer ? Le paiement sera libéré.')">
                                    @csrf
                                    <button type="submit" class="tp-btn tp-btn-green"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Confirmer & Libérer</button>
                                </form>
                                <a href="{{ route('client.escrow.show',$escrow->id) }}" class="tp-btn tp-btn-outline">Détails</a>
                                <button type="button" @click="showDisputeModal=true;selectedEscrow={{ $escrow->id }}" class="tp-btn tp-btn-red-outline"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>Signaler</button>
                            </div>
                        @elseif($eSt === 'released')
                            <div class="tp-tx-actions">
                                <a href="{{ route('client.escrow.show',$escrow->id) }}" class="tp-btn tp-btn-blue"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>Noter</a>
                            </div>
                        @else
                            <div class="tp-tx-actions">
                                <a href="{{ route('client.escrow.show',$escrow->id) }}" class="tp-btn tp-btn-outline">Voir détails</a>
                            </div>
                        @endif
                    </div>
                @endforeach
 
                <div class="tp-pagination">{{ $escrows->links() }}</div>
            @endif
        </div>
    </section>
 
    {{-- Modal Litige --}}
    <div x-show="showDisputeModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showDisputeModal" @click="showDisputeModal=false" class="fixed inset-0 tp-modal-backdrop transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="showDisputeModal" class="tp-modal-panel inline-block align-bottom overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <form :action="'/client/escrow/'+selectedEscrow+'/dispute'" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div class="tp-modal-head">
                        <h3 class="tp-modal-title">Signaler un problème</h3>
                        <p class="tp-modal-copy">Votre dossier est enregistré et visible par les parties.</p>
                    </div>
                    <div class="tp-modal-fields">
                        <div>
                            <label class="tp-modal-label">Type</label>
                            <select name="reason" required>
                                <option value="">Sélectionnez...</option>
                                <option value="non_conformity">Non conforme</option>
                                <option value="service_not_provided">Service non réalisé</option>
                                <option value="not_received">Non reçu</option>
                                <option value="damaged">Endommagé</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>
                        <div>
                            <label class="tp-modal-label">Description</label>
                            <textarea name="description" rows="4" required minlength="20" maxlength="2000" placeholder="Décrivez le problème (min. 20 caractères)..."></textarea>
                        </div>
                        <div>
                            <label class="tp-modal-label">Photos (optionnel)</label>
                            <input type="file" name="evidence[]" multiple accept="image/*">
                        </div>
                    </div>
                    <div class="tp-modal-actions">
                        <button type="submit" class="tp-modal-btn tp-modal-btn-danger">Envoyer</button>
                        <button type="button" @click="showDisputeModal=false" class="tp-modal-btn tp-modal-btn-neutral">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
