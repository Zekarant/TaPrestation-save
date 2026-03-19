@extends('layouts.client')
 
@section('title', 'Facture ' . $invoice->invoice_number)
 
@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    .tp-inv-show { font-family:'Plus Jakarta Sans',system-ui,sans-serif; background:#faf7f2; min-height:100vh; }
 
    /* Hero */
    .tp-invs-hero { position:relative; overflow:hidden; padding:2rem 0 3.5rem; }
    .tp-invs-hero::before { content:''; position:absolute; inset:0; background:linear-gradient(140deg,#d97706 0%,#f59e0b 50%,#fbbf24 100%); z-index:0; }
    .tp-invs-hero::after { content:''; position:absolute; bottom:-1px; left:-5%; right:-5%; height:40px; background:#faf7f2; border-radius:50% 50% 0 0/100% 100% 0 0; z-index:2; }
    .tp-invs-hero .hero-inner { position:relative; z-index:1; }
    .tp-invs-hero h1 { font-size:1.375rem; font-weight:800; color:#fff; margin:0; text-shadow:0 1px 2px rgba(0,0,0,.1); }
    .tp-invs-hero .hero-sub { font-size:.8rem; color:rgba(255,255,255,.75); margin:.125rem 0 0; }
    .tp-invs-hero .btn-back {
        display:inline-flex; align-items:center; gap:6px;
        padding:8px 14px; background:rgba(255,255,255,.18); color:#fff;
        font-size:12px; font-weight:700; border-radius:10px; text-decoration:none;
        backdrop-filter:blur(6px); border:1px solid rgba(255,255,255,.25); transition:background .15s;
    }
    .tp-invs-hero .btn-back:hover { background:rgba(255,255,255,.30); }
    .tp-invs-hero .btn-back svg { width:16px; height:16px; }
    .tp-invs-hero .btn-print {
        display:inline-flex; align-items:center; gap:5px;
        padding:8px 14px; background:rgba(255,255,255,.90); color:#92400e;
        font-size:12px; font-weight:700; border-radius:10px; text-decoration:none;
        border:none; cursor:pointer; transition:background .15s;
    }
    .tp-invs-hero .btn-print:hover { background:#fff; }
    .tp-invs-hero .btn-print svg { width:15px; height:15px; }
 
    /* Invoice card */
    .tp-invs-card {
        background:#fff; border-radius:16px;
        box-shadow:0 2px 16px rgba(0,0,0,.06);
        border:1px solid rgba(15,58,134,.06); overflow:hidden;
    }
 
    /* Invoice header */
    .tp-invs-header {
        background:linear-gradient(135deg,#d97706,#b45309);
        padding:1.5rem; color:#fff;
    }
    .tp-invs-header-row { display:flex; flex-direction:column; gap:.75rem; }
    @media(min-width:640px) { .tp-invs-header-row { flex-direction:row; justify-content:space-between; align-items:flex-start; } }
    .tp-invs-header h2 { font-size:1.5rem; font-weight:800; margin:0; letter-spacing:-.02em; }
    .tp-invs-header .inv-num { font-size:.8rem; color:rgba(255,255,255,.7); margin:.125rem 0 0; }
    .tp-invs-status {
        display:inline-flex; padding:4px 12px; border-radius:99px;
        font-size:11px; font-weight:800;
    }
    .tp-invs-status-paid { background:rgba(5,150,105,.9); }
    .tp-invs-status-issued { background:rgba(245,158,11,.9); }
    .tp-invs-status-refunded { background:rgba(234,88,12,.9); }
    .tp-invs-header .inv-date { font-size:.75rem; color:rgba(255,255,255,.65); margin:.375rem 0 0; }
 
    /* Body */
    .tp-invs-body { padding:1.25rem; }
    @media(min-width:640px) { .tp-invs-body { padding:1.75rem; } }
 
    /* Parties */
    .tp-invs-parties { display:grid; grid-template-columns:1fr; gap:1.25rem; margin-bottom:1.5rem; padding-bottom:1.25rem; border-bottom:1px solid rgba(15,58,134,.06); }
    @media(min-width:640px) { .tp-invs-parties { grid-template-columns:1fr 1fr; } }
    .tp-invs-parties-label { font-size:9px; font-weight:800; color:#8b92a8; text-transform:uppercase; letter-spacing:.1em; margin:0 0 .375rem; }
    .tp-invs-parties-name { font-size:1rem; font-weight:800; color:#1a1f36; margin:0 0 .125rem; }
    .tp-invs-parties-line { font-size:12px; color:#5a6178; margin:.125rem 0; }
    .tp-invs-parties-small { font-size:10px; color:#8b92a8; margin:.125rem 0; }
    @media(min-width:640px) { .tp-invs-parties > div:last-child { text-align:right; } }
 
    /* Description */
    .tp-invs-desc { margin-bottom:1.25rem; }
    .tp-invs-desc-label { font-size:9px; font-weight:800; color:#8b92a8; text-transform:uppercase; letter-spacing:.1em; margin:0 0 .25rem; }
    .tp-invs-desc-text { font-size:13px; color:#3a4158; margin:0; line-height:1.5; }
 
    /* Table */
    .tp-invs-table { width:100%; border-collapse:collapse; margin-bottom:1.5rem; }
    .tp-invs-table thead th {
        padding:.625rem .75rem; font-size:9px; font-weight:800;
        color:#8b92a8; text-transform:uppercase; letter-spacing:.08em;
        background:#faf7f2; border-bottom:1px solid rgba(15,58,134,.06);
    }
    .tp-invs-table thead th:first-child { text-align:left; }
    .tp-invs-table thead th:last-child { text-align:right; }
    .tp-invs-table tbody td {
        padding:.75rem; border-bottom:1px solid rgba(15,58,134,.03);
        font-size:13px; color:#1a1f36; vertical-align:top;
    }
    .tp-invs-table tbody tr:last-child td { border-bottom:none; }
    .tp-invs-item-name { font-weight:700; margin:0; }
    .tp-invs-item-detail { font-size:11px; color:#5a6178; margin:.125rem 0 0; }
    .tp-invs-item-discount { font-size:10px; color:#059669; margin:.125rem 0 0; }
    .tp-invs-item-discount s { color:#8b92a8; }
    .tp-invs-table .col-qty,
    .tp-invs-table .col-unit { text-align:center; color:#5a6178; }
    .tp-invs-table .col-total { text-align:right; font-weight:800; }
    @media(max-width:639px) { .tp-invs-table .hide-mobile { display:none; } }
 
    /* Totals */
    .tp-invs-totals { display:flex; justify-content:flex-end; margin-bottom:1.5rem; }
    .tp-invs-totals-box { width:100%; max-width:280px; }
    .tp-invs-totals-row { display:flex; justify-content:space-between; padding:.375rem 0; font-size:13px; color:#5a6178; }
    .tp-invs-totals-row.discount { color:#059669; }
    .tp-invs-totals-row.total {
        padding-top:.625rem; margin-top:.375rem;
        border-top:2px solid #1a1f36;
        font-size:1.125rem; font-weight:800; color:#1a1f36;
    }
 
    /* Payment confirmation */
    .tp-invs-paid-box {
        display:flex; align-items:center; gap:.75rem;
        padding:.875rem; border-radius:12px;
        background:#d1fae5; border:1px solid #a7f3d0;
    }
    .tp-invs-paid-dot {
        width:36px; height:36px; border-radius:50%;
        background:#059669; color:#fff;
        display:flex; align-items:center; justify-content:center; flex-shrink:0;
    }
    .tp-invs-paid-dot svg { width:20px; height:20px; }
    .tp-invs-paid-title { font-size:13px; font-weight:800; color:#065f46; margin:0; }
    .tp-invs-paid-sub { font-size:11px; color:#047857; margin:.125rem 0 0; }
 
    /* Notes */
    .tp-invs-notes { padding-top:1rem; margin-top:1rem; border-top:1px solid rgba(15,58,134,.06); }
    .tp-invs-notes-label { font-size:9px; font-weight:800; color:#8b92a8; text-transform:uppercase; letter-spacing:.1em; margin:0 0 .25rem; }
    .tp-invs-notes-text { font-size:12px; color:#5a6178; margin:0; line-height:1.5; }
    .tp-invs-terms { font-size:10px; color:#8b92a8; margin:.5rem 0 0; line-height:1.4; }
 
    /* Footer */
    .tp-invs-footer {
        background:#faf7f2; padding:.875rem 1.25rem;
        text-align:center; font-size:10px; color:#8b92a8;
        border-top:1px solid rgba(15,58,134,.06);
    }
 
    /* Mobile */
    @media(max-width:640px) {
        .tp-invs-hero { padding:1.25rem 0 2.5rem; }
        .tp-invs-hero h1 { font-size:1.125rem; }
        .tp-invs-header { padding:1.125rem; }
        .tp-invs-header h2 { font-size:1.25rem; }
        .tp-invs-body { padding:1rem; }
        .tp-invs-totals-box { max-width:none; }
    }
 
    /* Print */
    @media print {
        body * { visibility:hidden; }
        #invoice, #invoice * { visibility:visible; }
        #invoice { position:absolute; left:0; top:0; width:100%; box-shadow:none!important; border:none!important; border-radius:0!important; }
        .tp-invs-hero, .tp-invs-footer { display:none!important; }
        .tp-invs-header { background:#d97706!important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        button, a { display:none!important; }
    }
</style>
@endpush
 
@section('content')
<div class="tp-inv-show">
 
    {{-- Hero --}}
    <section class="tp-invs-hero">
        <div class="hero-inner max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <a href="{{ route('client.invoices.index') }}" class="btn-back">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Mes factures
                </a>
                <div style="text-align:center;flex:1;">
                    <h1>Facture {{ $invoice->invoice_number }}</h1>
                    <p class="hero-sub">Détail de votre facture</p>
                </div>
                <button onclick="window.print()" class="btn-print">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Imprimer
                </button>
            </div>
        </div>
    </section>
 
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10 pb-12">
        <div class="tp-invs-card" id="invoice">
 
            {{-- Header facture --}}
            <div class="tp-invs-header">
                <div class="tp-invs-header-row">
                    <div>
                        <h2>FACTURE</h2>
                        <p class="inv-num">{{ $invoice->invoice_number }}</p>
                    </div>
                    <div style="text-align:right;">
                        @php
                            $st = $invoice->status ?? 'issued';
                            $stLabel = match($st) { 'paid'=>'✓ PAYÉE','refunded'=>'↩ REMBOURSÉE', default=>'EN ATTENTE' };
                            $stClass = match($st) { 'paid'=>'tp-invs-status-paid','refunded'=>'tp-invs-status-refunded', default=>'tp-invs-status-issued' };
                        @endphp
                        <span class="tp-invs-status {{ $stClass }}">{{ $stLabel }}</span>
                        <p class="inv-date">Émise le {{ $invoice->issued_at?->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
 
            <div class="tp-invs-body">
                {{-- Parties --}}
                <div class="tp-invs-parties">
                    <div>
                        <p class="tp-invs-parties-label">Vendeur</p>
                        <p class="tp-invs-parties-name">{{ $invoice->seller_name }}</p>
                        @if($invoice->seller_address)<p class="tp-invs-parties-line">{{ $invoice->seller_address }}</p>@endif
                        @if($invoice->seller_siret)<p class="tp-invs-parties-small">SIRET : {{ $invoice->seller_siret }}</p>@endif
                        @if($invoice->seller_vat_number)<p class="tp-invs-parties-small">TVA : {{ $invoice->seller_vat_number }}</p>@endif
                    </div>
                    <div>
                        <p class="tp-invs-parties-label">Facturé à</p>
                        <p class="tp-invs-parties-name">{{ $invoice->billing_name }}</p>
                        <p class="tp-invs-parties-line">{{ $invoice->billing_email }}</p>
                        @if($invoice->billing_phone)<p class="tp-invs-parties-line">{{ $invoice->billing_phone }}</p>@endif
                        @if($invoice->billing_address)
                            <p class="tp-invs-parties-line">
                                {{ $invoice->billing_address }}
                                @if($invoice->billing_postal_code || $invoice->billing_city)<br>{{ $invoice->billing_postal_code }} {{ $invoice->billing_city }}@endif
                            </p>
                        @endif
                    </div>
                </div>
 
                {{-- Description --}}
                @if($invoice->description)
                <div class="tp-invs-desc">
                    <p class="tp-invs-desc-label">Description</p>
                    <p class="tp-invs-desc-text">{{ $invoice->description }}</p>
                </div>
                @endif
 
                {{-- Lignes --}}
                <table class="tp-invs-table">
                    <thead>
                        <tr>
                            <th style="text-align:left;">Description</th>
                            <th class="hide-mobile" style="text-align:center;">Qté</th>
                            <th class="hide-mobile" style="text-align:right;">Prix unit.</th>
                            <th style="text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($invoice->line_items && is_array($invoice->line_items))
                            @foreach($invoice->line_items as $item)
                            <tr>
                                <td>
                                    <p class="tp-invs-item-name">{{ $item['description'] ?? 'Article' }}</p>
                                    @if(isset($item['details']))<p class="tp-invs-item-detail">{{ $item['details'] }}</p>@endif
                                    @if(isset($item['original_price']))
                                        <p class="tp-invs-item-discount">🏷️ -{{ number_format($item['discount'] ?? 0, 0) }}% <s>{{ number_format($item['original_price'], 2, ',', ' ') }} €</s></p>
                                    @endif
                                </td>
                                <td class="col-qty hide-mobile">{{ $item['quantity'] ?? 1 }}{{ isset($item['unit']) ? ' '.$item['unit'] : '' }}</td>
                                <td class="hide-mobile" style="text-align:right;color:#5a6178;">{{ number_format($item['unit_price'] ?? 0, 2, ',', ' ') }} €</td>
                                <td class="col-total">{{ number_format($item['total'] ?? 0, 2, ',', ' ') }} €</td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td><p class="tp-invs-item-name">{{ $invoice->description }}</p></td>
                                <td class="col-qty hide-mobile">1</td>
                                <td class="hide-mobile" style="text-align:right;color:#5a6178;">{{ number_format($invoice->subtotal, 2, ',', ' ') }} €</td>
                                <td class="col-total">{{ number_format($invoice->subtotal, 2, ',', ' ') }} €</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
 
                {{-- Totaux --}}
                <div class="tp-invs-totals">
                    <div class="tp-invs-totals-box">
                        <div class="tp-invs-totals-row">
                            <span>Sous-total HT</span>
                            <span>{{ number_format($invoice->subtotal, 2, ',', ' ') }} €</span>
                        </div>
                        @if($invoice->tax_amount > 0)
                        <div class="tp-invs-totals-row">
                            <span>TVA ({{ number_format($invoice->tax_rate, 0) }}%)</span>
                            <span>{{ number_format($invoice->tax_amount, 2, ',', ' ') }} €</span>
                        </div>
                        @endif
                        @if($invoice->discount_amount > 0)
                        <div class="tp-invs-totals-row discount">
                            <span>Remise</span>
                            <span>-{{ number_format($invoice->discount_amount, 2, ',', ' ') }} €</span>
                        </div>
                        @endif
                        <div class="tp-invs-totals-row total">
                            <span>Total TTC</span>
                            <span>{{ number_format($invoice->total, 2, ',', ' ') }} €</span>
                        </div>
                    </div>
                </div>
 
                {{-- Paiement --}}
                @if($invoice->status === 'paid')
                <div class="tp-invs-paid-box">
                    <div class="tp-invs-paid-dot">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <p class="tp-invs-paid-title">Facture acquittée</p>
                        <p class="tp-invs-paid-sub">
                            Payée le {{ $invoice->paid_at?->format('d/m/Y à H:i') }}
                            @if($invoice->payment_method) via {{ ucfirst($invoice->payment_method) }}@endif
                        </p>
                    </div>
                </div>
                @endif
 
                {{-- Notes --}}
                @if($invoice->notes)
                <div class="tp-invs-notes">
                    <p class="tp-invs-notes-label">Notes</p>
                    <p class="tp-invs-notes-text">{{ $invoice->notes }}</p>
                </div>
                @endif
 
                @if($invoice->terms)
                <p class="tp-invs-terms" style="margin-top:.75rem;">{{ $invoice->terms }}</p>
                @endif
            </div>
 
            {{-- Footer --}}
            <div class="tp-invs-footer">
                <p>TaPrestation — Plateforme de mise en relation prestataires / clients</p>
                <p style="margin:.25rem 0 0;">Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection