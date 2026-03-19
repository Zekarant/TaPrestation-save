@extends('layouts.app')
 
@push('styles')
<link rel="stylesheet" href="{{ asset('css/client-dashboard-v2.css') }}">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    .tp-escrow-show { font-family:'Plus Jakarta Sans',system-ui,sans-serif; background:#faf7f2; min-height:100vh; }
 
    /* Hero */
    .tp-show-hero { position:relative; overflow:hidden; padding:2rem 0 3.5rem; }
    .tp-show-hero::before { content:''; position:absolute; inset:0; background:linear-gradient(140deg,#7c3aed 0%,#8b5cf6 45%,#a78bfa 100%); z-index:0; }
    .tp-show-hero::after { content:''; position:absolute; bottom:-1px; left:-5%; right:-5%; height:40px; background:#faf7f2; border-radius:50% 50% 0 0/100% 100% 0 0; z-index:2; }
    .tp-show-hero .hero-inner { position:relative; z-index:1; }
    .tp-show-hero h1 { font-size:1.375rem; font-weight:800; color:#fff; margin:0; }
    .tp-show-hero p { font-size:.8rem; color:rgba(255,255,255,.7); margin:.125rem 0 0; }
    .tp-show-hero .btn-back {
        display:inline-flex; align-items:center; gap:6px;
        padding:8px 14px; background:rgba(255,255,255,.18); color:#fff;
        font-size:12px; font-weight:700; border-radius:10px; text-decoration:none;
        backdrop-filter:blur(6px); border:1px solid rgba(255,255,255,.2); transition:background .15s;
    }
    .tp-show-hero .btn-back:hover { background:rgba(255,255,255,.30); }
    .tp-show-hero .btn-back svg { width:16px; height:16px; }
 
    /* Cards */
    .tp-s-card {
        background:#fff; border-radius:14px;
        box-shadow:0 1px 8px rgba(0,0,0,.04);
        border:1px solid rgba(15,58,134,.06);
        padding:1.25rem; margin-bottom:1rem;
    }
    .tp-s-card h2 {
        font-size:.95rem; font-weight:800; color:#1a1f36;
        margin:0 0 1rem; display:flex; align-items:center; gap:6px;
    }
    .tp-s-card h2 svg { width:18px; height:18px; }
 
    /* Status badge */
    .tp-s-status {
        display:inline-flex; align-items:center; gap:4px;
        padding:6px 14px; border-radius:99px;
        font-size:11px; font-weight:700;
    }
    .tp-s-status-pending { background:#fef3c7; color:#92400e; }
    .tp-s-status-partial { background:#ede9fe; color:#5b21b6; }
    .tp-s-status-released { background:#d1fae5; color:#065f46; }
    .tp-s-status-refunded { background:#dbeafe; color:#1e40af; }
    .tp-s-status-partially_refunded { background:#e0e7ff; color:#3730a3; }
    .tp-s-status-disputed { background:#fee2e2; color:#991b1b; }
    .tp-s-status-dispute_review { background:#ffedd5; color:#9a3412; }
    .tp-s-status-cancelled { background:#f3f4f6; color:#4b5563; }
 
    /* Timeline */
    .tp-timeline { position:relative; padding-left:20px; }
    .tp-timeline::before { content:''; position:absolute; left:15px; top:0; bottom:0; width:2px; background:#e5e7eb; }
    .tp-tl-item { position:relative; display:flex; align-items:flex-start; gap:12px; padding-bottom:1.25rem; }
    .tp-tl-item:last-child { padding-bottom:0; }
    .tp-tl-dot {
        width:28px; height:28px; border-radius:50%; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
        position:relative; z-index:1;
    }
    .tp-tl-dot svg { width:14px; height:14px; }
    .tp-tl-dot-done { background:#059669; color:#fff; }
    .tp-tl-dot-active { background:#f59e0b; color:#fff; }
    .tp-tl-dot-pending { background:#e5e7eb; color:#9ca3af; }
    .tp-tl-text p { margin:0; }
    .tp-tl-text .tp-tl-label { font-size:13px; font-weight:700; color:#1a1f36; }
    .tp-tl-text .tp-tl-label.pending { color:#d97706; }
    .tp-tl-text .tp-tl-label.muted { color:#8b92a8; }
    .tp-tl-text .tp-tl-sub { font-size:11px; color:#8b92a8; }
 
    /* Detail rows */
    .tp-s-row { display:flex; justify-content:space-between; align-items:center; padding:.5rem 0; border-bottom:1px solid rgba(15,58,134,.04); }
    .tp-s-row:last-child { border-bottom:none; }
    .tp-s-row-label { font-size:12px; color:#8b92a8; }
    .tp-s-row-value { font-size:13px; font-weight:700; color:#1a1f36; text-align:right; }
 
    /* Prestataire card */
    .tp-s-presta { display:flex; align-items:center; gap:.75rem; }
    .tp-s-presta-avatar {
        width:48px; height:48px; border-radius:50%; flex-shrink:0; object-fit:cover;
        border:2px solid rgba(139,92,246,.15);
    }
    .tp-s-presta-fallback {
        width:48px; height:48px; border-radius:50%; flex-shrink:0;
        background:linear-gradient(135deg,#8b5cf6,#7c3aed);
        display:flex; align-items:center; justify-content:center;
        color:#fff; font-weight:800; font-size:1rem;
    }
    .tp-s-presta-name { font-size:14px; font-weight:800; color:#1a1f36; }
    .tp-s-presta-stars { display:flex; align-items:center; gap:1px; margin-top:2px; }
    .tp-s-presta-stars svg { width:14px; height:14px; }
    .tp-s-presta-actions { margin-left:auto; display:flex; gap:6px; }
 
    /* Financial summary */
    .tp-s-total { font-size:1.375rem; font-weight:800; color:#1a1f36; }
    .tp-s-total-green { color:#059669; }
 
    /* Info box */
    .tp-s-info {
        padding:.75rem; border-radius:10px; font-size:12px; line-height:1.5;
        display:flex; align-items:flex-start; gap:6px;
    }
    .tp-s-info svg { width:16px; height:16px; flex-shrink:0; margin-top:1px; }
    .tp-s-info-blue { background:#ede9fe; color:#5b21b6; }
    .tp-s-info-green { background:#d1fae5; color:#065f46; }
 
    /* Deposit tracker */
    .tp-s-deposit-box {
        padding:.75rem; border-radius:10px; font-size:12px; margin-top:.5rem;
    }
 
    /* Buttons */
    .tp-s-btn {
        display:flex; align-items:center; justify-content:center; gap:6px;
        width:100%; padding:10px; border-radius:10px;
        font-size:13px; font-weight:700; cursor:pointer; border:none; text-decoration:none;
        transition: all .15s;
    }
    .tp-s-btn svg { width:18px; height:18px; }
    .tp-s-btn-green { background:#059669; color:#fff; }
    .tp-s-btn-green:hover { background:#047857; }
    .tp-s-btn-red-outline { background:#fff; color:#dc2626; border:1.5px solid rgba(220,38,38,.2); }
    .tp-s-btn-red-outline:hover { background:#fef2f2; }
    .tp-s-btn-blue { background:#7c3aed; color:#fff; }
    .tp-s-btn-blue:hover { background:#6d28d9; }
    .tp-s-btn-msg { background:rgba(37,99,235,.1); color:#2563eb; width:38px; height:38px; border-radius:50%; padding:0; min-width:38px; }
    .tp-s-btn-profile { background:rgba(139,92,246,.1); color:#7c3aed; padding:6px 12px; width:auto; border-radius:8px; font-size:11px; }
 
    /* Grid */
    .tp-show-grid { display:grid; grid-template-columns:1fr; gap:1rem; }
    @media(min-width:1024px) { .tp-show-grid { grid-template-columns:2fr 1fr; } }
 
    /* Shipment */
    .tp-s-tracking-link { display:inline-flex; align-items:center; gap:4px; font-size:12px; font-weight:700; color:#2563eb; text-decoration:none; margin-top:.5rem; }
    .tp-s-tracking-link:hover { color:#1d4ed8; }
    .tp-s-tracking-link svg { width:14px; height:14px; }
 
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
    .tp-modal-btn-primary { background:#7c3aed; color:#fff; }
    .tp-modal-btn-primary:hover { background:#6d28d9; }
    .tp-modal-btn-neutral { background:#fff; color:#374151; border-color:#d1d5db; }
    .tp-modal-btn-neutral:hover { background:#f9fafb; }
 
    /* Mobile */
    @media(max-width:640px) {
        .tp-show-hero { padding:1.25rem 0 2.5rem; }
        .tp-show-hero h1 { font-size:1.125rem; }
        .tp-s-card { padding:1rem; }
        .tp-s-presta { flex-wrap:wrap; }
        .tp-s-presta-actions { margin-left:0; margin-top:.5rem; width:100%; }
    }
    @media(min-width:640px) {
        .tp-modal-actions { grid-template-columns:1fr 1fr; }
    }
</style>
@endpush
 
@section('content')
@php
    $escrowType = $escrow->escrowable_type ?? '';
    $isUrgentSale = str_contains($escrowType, 'UrgentSale');
    $isBooking = str_contains($escrowType, 'Booking');
    $isEquipment = str_contains($escrowType, 'Equipment');
    $totalAmount = (float)($escrow->total_amount ?? $escrow->amount ?? 0);
    $depositAmount = (float)($escrow->deposit_amount ?? 0);
    $escrowMetaTop = [];
    try { $escrowMetaTop = !empty($escrow->metadata) ? (json_decode((string)$escrow->metadata, true) ?: []) : []; } catch (\Throwable $e) { $escrowMetaTop = []; }
    if ($depositAmount <= 0) { $depositAmount = (float)($escrowMetaTop['deposit_amount'] ?? ($escrowMetaTop['security_deposit'] ?? 0)); }
    if ($depositAmount <= 0 && isset($relatedItem) && is_object($relatedItem) && property_exists($relatedItem, 'security_deposit')) { $depositAmount = (float)($relatedItem->security_deposit ?? 0); }
    $urgentSaleProduct = $urgentSaleProduct ?? null;
    $fmtDt = function($v) { try { return $v ? \Carbon\Carbon::parse($v)->format('d/m/Y à H:i') : '—'; } catch(\Throwable $e) { return '—'; } };
    $fmtD = function($v) { try { return $v ? \Carbon\Carbon::parse($v)->format('d/m/Y') : '—'; } catch(\Throwable $e) { return '—'; } };
    $fmtDiff = function($v) { try { return $v ? \Carbon\Carbon::parse($v)->diffForHumans() : '—'; } catch(\Throwable $e) { return '—'; } };
@endphp
 
<div class="tp-escrow-show" x-data="{ showRatingModal:false, showDisputeModal:false, rating:5, setRating(v){this.rating=v} }">
 
    {{-- Hero --}}
    <section class="tp-show-hero">
        <div class="hero-inner max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1>Transaction #{{ $escrow->id }}</h1>
                    <p>Détails de votre paiement sécurisé</p>
                </div>
                <a href="{{ route('client.escrow.index') }}" class="btn-back">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Retour
                </a>
            </div>
        </div>
    </section>
 
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12 -mt-4 relative z-10">
        <div class="tp-show-grid">
            {{-- Colonne principale --}}
            <div>
                {{-- Statut --}}
                <div class="tp-s-card">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                        <h2 style="margin:0;">Statut</h2>
                        @php
                            $stClass = 'tp-s-status-'.($escrow->status ?? 'pending');
                            $stText = match($escrow->status) { 'pending'=>'🔒 En attente','partial'=>'🟣 Partiel','released'=>'✅ Libéré','refunded'=>'💰 Remboursé','partially_refunded'=>'💸 Remb. partiel','disputed'=>'⚠️ Litige','dispute_review'=>'📁 Dossier litige','cancelled'=>'❌ Annulé', default=>ucfirst($escrow->status ?? '') };
                        @endphp
                        <span class="tp-s-status {{ $stClass }}">{{ $stText }}</span>
                    </div>
 
                    {{-- Timeline --}}
                    <div class="tp-timeline">
                        <div class="tp-tl-item">
                            <div class="tp-tl-dot tp-tl-dot-done"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>
                            <div class="tp-tl-text"><p class="tp-tl-label">Paiement reçu et bloqué</p><p class="tp-tl-sub">{{ $fmtDt($escrow->created_at) }}</p></div>
                        </div>
                        <div class="tp-tl-item">
                            <div class="tp-tl-dot {{ $escrow->client_confirmed_at ? 'tp-tl-dot-done' : ($escrow->status === 'pending' ? 'tp-tl-dot-active' : 'tp-tl-dot-pending') }}">
                                @if($escrow->client_confirmed_at)<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @elseif($escrow->status === 'pending')<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @else<span style="font-size:10px;">2</span>@endif
                            </div>
                            <div class="tp-tl-text">
                                @if($escrow->client_confirmed_at)
                                    <p class="tp-tl-label">Vous avez confirmé</p><p class="tp-tl-sub">{{ $fmtDt($escrow->client_confirmed_at) }}</p>
                                @elseif($escrow->status === 'pending')
                                    <p class="tp-tl-label pending">En attente de votre confirmation</p>
                                    @if($escrow->auto_release_at)<p class="tp-tl-sub">Libération auto {{ $fmtDiff($escrow->auto_release_at) }}</p>@endif
                                @else<p class="tp-tl-label muted">Confirmation client</p>@endif
                            </div>
                        </div>
                        <div class="tp-tl-item">
                            <div class="tp-tl-dot {{ $escrow->released_at ? 'tp-tl-dot-done' : 'tp-tl-dot-pending' }}">
                                @if($escrow->released_at)<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @else<span style="font-size:10px;">3</span>@endif
                            </div>
                            <div class="tp-tl-text">
                                @if($escrow->released_at)<p class="tp-tl-label">Paiement libéré</p><p class="tp-tl-sub">{{ $fmtDt($escrow->released_at) }}</p>
                                @else<p class="tp-tl-label muted">Libération du paiement</p>@endif
                            </div>
                        </div>
                    </div>
                </div>
 
                {{-- Détails --}}
                @if($relatedItem)
                <div class="tp-s-card">
                    <h2>Détails</h2>
                    @if($isBooking)
                        <div class="tp-s-row"><span class="tp-s-row-label">Type</span><span class="tp-s-row-value">Réservation de service</span></div>
                        <div class="tp-s-row"><span class="tp-s-row-label">Date</span><span class="tp-s-row-value">{{ $fmtD($relatedItem->start_date ?? ($relatedItem->booking_date ?? null)) }}</span></div>
                    @elseif($isEquipment)
                        <div class="tp-s-row"><span class="tp-s-row-label">Type</span><span class="tp-s-row-value">Location d'équipement</span></div>
                        <div class="tp-s-row"><span class="tp-s-row-label">Période</span><span class="tp-s-row-value">{{ $fmtD($relatedItem->start_date ?? null) }} → {{ $fmtD($relatedItem->end_date ?? null) }}</span></div>
                    @elseif($isUrgentSale)
                        <div class="tp-s-row"><span class="tp-s-row-label">Type</span><span class="tp-s-row-value">Vente urgente</span></div>
                        @if($urgentSaleProduct)<div class="tp-s-row"><span class="tp-s-row-label">Produit</span><span class="tp-s-row-value">{{ $urgentSaleProduct->title ?? '—' }}</span></div>@endif
                        <div class="tp-s-row"><span class="tp-s-row-label">Quantité</span><span class="tp-s-row-value">{{ $relatedItem->quantity ?? 1 }}</span></div>
                        <div class="tp-s-row"><span class="tp-s-row-label">Prix unitaire</span><span class="tp-s-row-value">{{ number_format((float)($relatedItem->unit_price ?? 0),2,',',' ') }} €</span></div>
                        <div class="tp-s-row"><span class="tp-s-row-label">Montant total</span><span class="tp-s-row-value" style="color:#059669;">{{ number_format((float)($relatedItem->total_amount ?? 0),2,',',' ') }} €</span></div>
                        @php $rSt = $relatedItem->status ?? ''; @endphp
                        <div class="tp-s-row"><span class="tp-s-row-label">Statut achat</span><span class="tp-s-row-value"><span class="tp-s-status tp-s-status-{{ $rSt === 'paid' ? 'released' : ($rSt === 'pending' ? 'pending' : 'cancelled') }}" style="font-size:10px;padding:2px 8px;">{{ match($rSt) { 'paid'=>'Payé','pending'=>'En attente','refunded'=>'Remboursé','cancelled'=>'Annulé', default=>ucfirst($rSt ?: '—') } }}</span></span></div>
                        <div class="tp-s-row"><span class="tp-s-row-label">Date d'achat</span><span class="tp-s-row-value">{{ $fmtDt($relatedItem->created_at ?? null) }}</span></div>
                    @endif
                </div>
                @endif
 
                {{-- Vendeur --}}
                @php $prestataire = $prestataire ?? null; @endphp
                @if($prestataire)
                @php
                    $pPhoto = $prestataire->profile_image ?? $prestataire->photo ?? null;
                    $pName = $prestataire->company_name ?? $prestataire->user_name ?? 'Vendeur';
                    $pRating = $prestataire->rating_average ?? null;
                    $pReviews = $prestataire->total_reviews ?? 0;
                @endphp
                <div class="tp-s-card">
                    <h2><svg style="color:#7c3aed;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>Vendeur</h2>
                    <div class="tp-s-presta">
                        @if($pPhoto)<img src="{{ asset('storage/'.$pPhoto) }}" alt="{{ $pName }}" class="tp-s-presta-avatar">
                        @else<div class="tp-s-presta-fallback">{{ strtoupper(substr($pName,0,1)) }}</div>@endif
                        <div style="flex:1;min-width:0;">
                            <div class="tp-s-presta-name">{{ $pName }}</div>
                            @if($pRating)
                            <div class="tp-s-presta-stars">
                                @for($i=1;$i<=5;$i++)<svg fill="currentColor" viewBox="0 0 20 20" style="color:{{ $i<=round($pRating) ? '#f59e0b' : '#d1d5db' }};"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                                <span style="margin-left:4px;font-size:11px;color:#8b92a8;">{{ number_format($pRating,1) }}@if($pReviews > 0) ({{ $pReviews }})@endif</span>
                            </div>
                            @endif
                        </div>
                        <div class="tp-s-presta-actions">
                            @if($prestataire->user_id)<a href="{{ route('messaging.show',['user'=>$prestataire->user_id]) }}" class="tp-s-btn tp-s-btn-msg" title="Message"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></a>@endif
                            @if($prestataire->id)<a href="{{ route('prestataires.show',['prestataire'=>$prestataire->id]) }}" class="tp-s-btn tp-s-btn-profile">Voir profil</a>@endif
                        </div>
                    </div>
                </div>
                @endif
 
                {{-- Livraison --}}
                @if($shipment)
                <div class="tp-s-card">
                    <h2><svg style="color:#2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>Livraison</h2>
                    <div class="tp-s-row"><span class="tp-s-row-label">Transporteur</span><span class="tp-s-row-value">{{ ucfirst(str_replace('_',' ',(string)($shipment->carrier ?? ''))) }}</span></div>
                    <div class="tp-s-row"><span class="tp-s-row-label">N° suivi</span><span class="tp-s-row-value" style="font-family:monospace;">{{ $shipment->tracking_number }}</span></div>
                    @php $shSt = $shipment->status; $shLabel = match($shSt) { 'pending'=>'En préparation','shipped'=>'Expédié','in_transit'=>'En transit','out_for_delivery'=>'En livraison','delivered'=>'Livré','returned'=>'Retourné', default=>ucfirst($shSt) }; @endphp
                    <div class="tp-s-row"><span class="tp-s-row-label">Statut</span><span class="tp-s-row-value"><span class="tp-s-status {{ $shSt==='delivered' ? 'tp-s-status-released' : ($shSt==='in_transit' ? 'tp-s-status-refunded' : 'tp-s-status-pending') }}" style="font-size:10px;padding:2px 8px;">{{ $shLabel }}</span></span></div>
                    @if($shipment->tracking_url)<a href="{{ $shipment->tracking_url }}" target="_blank" class="tp-s-tracking-link"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>Suivre le colis</a>@endif
                </div>
                @endif
 
                {{-- Litige --}}
                @if(!empty($dispute))
                <div class="tp-s-card">
                    <h2 style="color:#dc2626;">⚠️ Dossier litige</h2>
                    <div class="tp-s-row"><span class="tp-s-row-label">Motif</span><span class="tp-s-row-value">{{ ucfirst(str_replace('_',' ',$dispute->reason ?? '')) }}</span></div>
                    <div style="padding:.5rem 0;"><p style="font-size:11px;color:#8b92a8;margin:0 0 4px;">Description</p><p style="font-size:13px;color:#1a1f36;margin:0;">{{ $dispute->description }}</p></div>
                    @php $evidence = []; try { $evidence = $dispute->evidence ? json_decode($dispute->evidence,true) : []; } catch(\Exception $e) {} @endphp
                    @if(!empty($evidence))
                    <div style="padding:.5rem 0;"><p style="font-size:11px;color:#8b92a8;margin:0 0 4px;">Preuves</p><div style="display:flex;flex-wrap:wrap;gap:6px;">@foreach($evidence as $path)<a href="{{ asset('storage/'.$path) }}" target="_blank" style="font-size:11px;font-weight:700;color:#2563eb;">Voir</a>@endforeach</div></div>
                    @endif
                    <p style="font-size:10px;color:#8b92a8;margin:.5rem 0 0;">La plateforme enregistre le dossier. Elle ne prend parti de personne.</p>
                </div>
                @endif
            </div>
 
            {{-- Sidebar --}}
            <div>
                {{-- Financier --}}
                <div class="tp-s-card">
                    <h2>💰 Résumé financier</h2>
                    <div class="tp-s-row"><span class="tp-s-row-label">Montant bloqué</span><span class="tp-s-total">{{ number_format($totalAmount,2) }} €</span></div>
                    @if($depositAmount > 0)
                        <div class="tp-s-row"><span class="tp-s-row-label">Caution</span><span class="tp-s-row-value">{{ number_format($depositAmount,2) }} €</span></div>
                        @if(str_contains((string)($escrow->escrowable_type ?? ''),'EquipmentRental'))
                            @php
                                $escrowMeta = $escrowMetaTop;
                                $depositStatus = strtolower((string)(($relatedItem->deposit_status ?? null) ?: ($escrowMeta['deposit_status'] ?? 'pending')));
                                $depositRetained = (float)(($relatedItem->deposit_retained ?? null) ?? ($escrowMeta['deposit_retained'] ?? 0));
                                $depositReason = trim((string)(($relatedItem->deposit_retention_reason ?? null) ?: ($escrowMeta['deposit_retention_reason'] ?? '')));
                                $depositReturned = max(0,$depositAmount - $depositRetained);
                                if(isset($escrowMeta['deposit_returned'])) $depositReturned = max(0,(float)$escrowMeta['deposit_returned']);
                                $depositProcessedAt = null;
                                $pc = ($relatedItem->equipment_returned_at ?? null) ?: ($escrowMeta['deposit_processed_at'] ?? null);
                                if(!empty($pc)) { try { $depositProcessedAt = \Carbon\Carbon::parse($pc)->format('d/m/Y à H:i'); } catch(\Throwable $e) {} }
                                $isReturned = $depositStatus==='returned' || ($depositRetained<=0 && !empty($depositProcessedAt));
                                $isRetained = $depositStatus==='retained' || ($depositRetained>=$depositAmount && $depositAmount>0);
                                $isPartial = $depositStatus==='partial' || (!$isReturned && !$isRetained && $depositRetained>0);
                            @endphp
                            <div class="tp-s-deposit-box" style="background:{{ $isReturned ? '#d1fae5' : ($isPartial ? '#fef3c7' : ($isRetained ? '#fee2e2' : '#f3f4f6')) }};border:1px solid {{ $isReturned ? '#a7f3d0' : ($isPartial ? '#fcd34d' : ($isRetained ? '#fecaca' : '#e5e7eb')) }};">
                                <div style="font-size:11px;font-weight:800;color:{{ $isReturned ? '#065f46' : ($isPartial ? '#92400e' : ($isRetained ? '#991b1b' : '#4b5563')) }};">Suivi caution</div>
                                @if($isReturned)<div style="font-size:12px;font-weight:700;color:#065f46;">Remboursée: {{ number_format($depositReturned,2) }} €</div>
                                @elseif($isPartial)<div style="font-size:12px;font-weight:700;color:#92400e;">Remboursée: {{ number_format($depositReturned,2) }} € · Retenue: {{ number_format($depositRetained,2) }} €</div>
                                @elseif($isRetained)<div style="font-size:12px;font-weight:700;color:#991b1b;">Retenue: {{ number_format($depositRetained,2) }} €</div>
                                @else<div style="font-size:12px;font-weight:700;color:#4b5563;">En attente après retour</div>@endif
                                @if($depositReason)<div style="font-size:10px;margin-top:2px;color:{{ $isRetained||$isPartial ? '#991b1b' : '#6b7280' }};">{{ $depositReason }}</div>@endif
                                @if($depositProcessedAt)<div style="font-size:10px;margin-top:2px;color:#6b7280;">Traitée le {{ $depositProcessedAt }}</div>@endif
                            </div>
                        @endif
                    @endif
                    <div class="tp-s-row" style="border-top:1px solid rgba(15,58,134,.06);margin-top:.5rem;padding-top:.5rem;">
                        <span class="tp-s-row-label">Total</span>
                        <span class="tp-s-total tp-s-total-green">{{ number_format($totalAmount + $depositAmount,2) }} €</span>
                    </div>
                    @if($escrow->status === 'pending')
                        <div class="tp-s-info tp-s-info-blue" style="margin-top:.75rem;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Votre argent est en sécurité. {{ $isUrgentSale && $shipment && $shipment->status !== 'delivered' ? 'Libération après livraison + confirmation.' : 'Libération après confirmation ou automatiquement après 48h.' }}</span>
                        </div>
                    @endif
                </div>
 
                {{-- Actions --}}
                @if(in_array($escrow->status,['pending','partial']))
                @php $clientAlreadyConfirmed = !empty($escrow->client_confirmed_at); @endphp
                <div class="tp-s-card">
                    <h2>⚡ Actions</h2>
                    <div style="display:flex;flex-direction:column;gap:.5rem;">
                        @if($isUrgentSale && $shipment && $shipment->status === 'delivered')
                            @if(!$clientAlreadyConfirmed)
                            <form action="{{ route('client.escrow.confirm-urgent-sale',$escrow->id) }}" method="POST" onsubmit="return confirm('Produit conforme ? Le solde sera libéré.')">@csrf
                                <button type="submit" class="tp-s-btn tp-s-btn-green"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Conforme — Libérer</button>
                            </form>
                            @endif
                            <form action="{{ route('client.escrow.non-conformity',$escrow->id) }}" method="POST" enctype="multipart/form-data">@csrf
                                <div style="padding:.75rem;border:1.5px solid rgba(220,38,38,.15);border-radius:10px;background:#fef2f2;">
                                    <p style="font-size:12px;font-weight:800;color:#dc2626;margin:0 0 4px;">Non conforme</p>
                                    <textarea name="description" rows="3" required minlength="20" maxlength="2000" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Décrivez le problème (min. 20 car.)..."></textarea>
                                    <input type="file" name="evidence[]" required multiple accept="image/*" class="mt-2 block w-full text-sm">
                                    <button type="submit" class="tp-s-btn tp-s-btn-green" style="background:#dc2626;margin-top:.5rem;">Signaler</button>
                                </div>
                            </form>
                        @else
                            @if(!$clientAlreadyConfirmed)
                                @if($isUrgentSale && !$shipment)
                                    <form action="{{ route('client.escrow.confirm-urgent-sale',$escrow->id) }}" method="POST" onsubmit="return confirm('Produit conforme ?')">@csrf
                                        <button type="submit" class="tp-s-btn tp-s-btn-green"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Conforme — Libérer</button>
                                    </form>
                                @elseif(!$isUrgentSale)
                                    <form action="{{ route('client.escrow.confirm',$escrow->id) }}" method="POST" onsubmit="return confirm('Tout OK ? Le paiement sera libéré.')">@csrf
                                        <button type="submit" class="tp-s-btn tp-s-btn-green"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>OK — Libérer</button>
                                    </form>
                                @endif
                            @endif
                            <button type="button" @click="showDisputeModal=true" class="tp-s-btn tp-s-btn-red-outline"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>Signaler un problème</button>
                        @endif
                        @if($clientAlreadyConfirmed)
                            <div class="tp-s-info tp-s-info-green">✅ Confirmation enregistrée.</div>
                        @endif
                    </div>
                </div>
                @endif
 
                {{-- Notation --}}
                @if($canRate)
                <div class="tp-s-card">
                    <h2>⭐ Notez cette expérience</h2>
                    <button type="button" @click="showRatingModal=true" class="tp-s-btn tp-s-btn-blue"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>Noter le prestataire</button>
                </div>
                @endif
            </div>
        </div>
    </div>
 
    {{-- Modal Litige --}}
    <div x-show="showDisputeModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showDisputeModal" @click="showDisputeModal=false" class="fixed inset-0 tp-modal-backdrop transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="showDisputeModal" class="tp-modal-panel inline-block align-bottom overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <form action="{{ route('client.escrow.dispute',$escrow->id) }}" method="POST" enctype="multipart/form-data">@csrf
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100"><svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></div>
                    <div class="tp-modal-head"><h3 class="tp-modal-title">Signaler un problème</h3><p class="tp-modal-copy">Dossier enregistré et visible par les parties.</p></div>
                    <div class="tp-modal-fields">
                        <div><label class="tp-modal-label">Type</label><select name="reason" required><option value="">Sélectionnez...</option><option value="non_conformity">Non conforme</option><option value="service_not_provided">Service non réalisé</option><option value="not_received">Non reçu</option><option value="damaged">Endommagé</option><option value="other">Autre</option></select></div>
                        <div><label class="tp-modal-label">Description</label><textarea name="description" rows="4" required minlength="20"></textarea></div>
                        <div><label class="tp-modal-label">Photos (optionnel)</label><input type="file" name="evidence[]" multiple accept="image/*"></div>
                    </div>
                    <div class="tp-modal-actions">
                        <button type="submit" class="tp-modal-btn tp-modal-btn-danger">Envoyer</button>
                        <button type="button" @click="showDisputeModal=false" class="tp-modal-btn tp-modal-btn-neutral">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
 
    {{-- Modal Notation --}}
    <div x-show="showRatingModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showRatingModal" @click="showRatingModal=false" class="fixed inset-0 tp-modal-backdrop transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="showRatingModal" class="tp-modal-panel inline-block align-bottom overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <form action="{{ route('client.escrow.rate',$escrow->id) }}" method="POST">@csrf
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100"><svg class="h-6 w-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg></div>
                    <div class="tp-modal-head"><h3 class="tp-modal-title">Noter le prestataire</h3></div>
                    <div class="tp-modal-fields">
                        <div class="text-center"><label class="block text-sm font-medium text-gray-700 mb-3">Votre note</label>
                            <div class="flex justify-center space-x-2">
                                <template x-for="i in 5" :key="i"><button type="button" @click="setRating(i)" class="focus:outline-none"><svg class="w-10 h-10 transition-colors" :class="i<=rating?'text-yellow-400':'text-gray-300'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg></button></template>
                            </div>
                            <input type="hidden" name="rating" x-bind:value="rating">
                        </div>
                        <div><label class="tp-modal-label">Commentaire (optionnel)</label><textarea name="comment" rows="3" maxlength="500"></textarea></div>
                        <label class="flex items-center"><input type="checkbox" name="would_recommend" value="1" checked class="rounded border-gray-300 text-purple-600"><span class="ml-2 text-sm text-gray-700">Je recommande ce prestataire</span></label>
                    </div>
                    <div class="tp-modal-actions">
                        <button type="submit" class="tp-modal-btn tp-modal-btn-primary">Envoyer</button>
                        <button type="button" @click="showRatingModal=false" class="tp-modal-btn tp-modal-btn-neutral">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
