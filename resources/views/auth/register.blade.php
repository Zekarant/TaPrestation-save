@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/register-form.css') }}">
@endpush

@push('head')
    <meta name="theme-color" content="#0f3a86" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="mobile-web-app-capable" content="yes" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,700;9..144,900&display=swap" rel="stylesheet" />

    <style>
        :root {
            --blue:      #0f3a86;
            --blue-mid:  #2d67c8;
            --amber:     #d97706;
            --amber-bg:  rgba(245,158,11,.10);
            --text:      #0f172a;
            --muted:     #64748b;
            --r:         12px;
            --font-d:    'Fraunces', Georgia, serif;
            --font-b:    'Plus Jakarta Sans', sans-serif;
        }

        html, body {
            height: 100%;
            font-family: var(--font-b);
            background: #ede5d8;
            overflow-x: hidden;
        }

        /* ── Orbs ambiants ── */
        .rp-bg { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
        .rp-bg span { position: absolute; border-radius: 50%; animation: rp-drift 16s ease-in-out infinite alternate; }
        .rp-bg .o1 { width:460px;height:460px;top:-150px;left:-120px;background:radial-gradient(circle,rgba(15,58,134,.12),transparent 65%); }
        .rp-bg .o2 { width:340px;height:340px;top:20px;right:-80px;background:radial-gradient(circle,rgba(215,182,121,.18),transparent 65%);animation-delay:-5s; }
        .rp-bg .o3 { width:320px;height:320px;bottom:-120px;left:50%;transform:translateX(-50%);background:radial-gradient(circle,rgba(255,255,255,.40),transparent 65%);animation-delay:-9s; }
        @keyframes rp-drift { from{transform:translate(0,0)} to{transform:translate(16px,12px)} }
        .rp-bg .o3 { animation-name:rp-drift-c; }
        @keyframes rp-drift-c { from{transform:translateX(-50%)} to{transform:translateX(calc(-50% + 14px))} }

        /* ── Wrapper ── */
        .rp-wrap {
            position: relative; z-index: 1;
            min-height: 100dvh;
            display: flex; justify-content: center;
            padding: max(20px, env(safe-area-inset-top, 20px)) 20px
                     max(24px, env(safe-area-inset-bottom, 24px));
            padding-bottom: 6vh;
        }

        /* ── Colonne centrale ── */
        .rp-col {
            width: 100%; max-width: 460px;
            display: flex; flex-direction: column;
            animation: rp-up .38s ease both;
        }
        @keyframes rp-up { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }

        /* ── Brand ── */
        .rp-brand {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 18px;
        }
        .rp-brand-icon {
            width: 34px; height: 34px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 10px;
            background: var(--blue); color: #fff; font-size: 11px;
            box-shadow: 0 5px 14px rgba(15,58,134,.30);
        }
        .rp-brand-name {
            font-size: 9px; font-weight: 800;
            letter-spacing: .22em; text-transform: uppercase;
            background: linear-gradient(90deg, var(--blue), var(--blue-mid));
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ── Titre ── */
        .rp-title {
            font-family: var(--font-d);
            font-size: 2.1rem; font-weight: 900;
            letter-spacing: -.05em; line-height: .92;
            color: var(--text); margin-bottom: 4px;
        }
        .rp-sub {
            font-size: 12px; color: var(--muted); line-height: 1.55;
            margin-bottom: 18px;
        }

        /* ── Alerts ── */
        .rp-alert {
            display: flex; align-items: flex-start; gap: 8px;
            padding: 9px 12px; border-radius: var(--r);
            font-size: 12px; margin-bottom: 10px; line-height: 1.5;
        }
        .rp-alert i { flex-shrink: 0; margin-top: 1px; }
        .rp-alert-error   { background: rgba(239,68,68,.09);  color: #b91c1c; }
        .rp-alert-success { background: rgba(34,197,94,.09);  color: #15803d; }
        .rp-alert-info    { background: rgba(59,130,246,.09); color: #1d4ed8; }
        .rp-alert-warning { background: rgba(234,179,8,.10);  color: #b45309; }
        .rp-alert-error ul { padding-left: 16px; margin-top: 4px; list-style: disc; }
        .rp-alert-error li { font-size: 11px; }

        /* ── Sécurité banner ── */
        .rp-secure {
            display: flex; align-items: center; gap: 10px;
            background: rgba(34,197,94,.08);
            border-radius: var(--r); padding: 10px 13px;
            margin-bottom: 16px;
        }
        .rp-secure-icon {
            width: 28px; height: 28px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 8px; background: rgba(34,197,94,.15);
            color: #15803d; font-size: 11px;
        }
        .rp-secure b  { display: block; font-size: 11px; font-weight: 700; color: #15803d; }
        .rp-secure p  { font-size: 10px; color: #166534; margin-top: 1px; }

        /* ── Type selector ── */
        .rp-type-label {
            font-size: 9px; font-weight: 700;
            letter-spacing: .16em; text-transform: uppercase;
            color: rgba(15,23,42,.42); text-align: center;
            margin-bottom: 8px;
        }
        .rp-type-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 18px; }

        .rp-type-btn {
            display: flex; flex-direction: column; align-items: center; gap: 6px;
            padding: 14px 10px;
            border-radius: var(--r);
            background: rgba(255,255,255,.52);
            backdrop-filter: blur(10px);
            box-shadow: inset 0 0 0 1px rgba(15,23,42,.09), 0 2px 8px rgba(15,23,42,.04);
            cursor: pointer; text-align: center;
            transition: background .18s, box-shadow .18s, transform .15s;
            -webkit-tap-highlight-color: transparent;
            position: relative; overflow: hidden;
        }
        .rp-type-btn::before {
            content: ''; position: absolute;
            top: 0; left: 0; right: 0; height: 3px;
            background: transparent; transition: background .2s;
        }
        .rp-type-btn:hover { background: rgba(255,255,255,.70); }
        .rp-type-btn:active { transform: scale(.97); }

        /* Client */
        .rp-type-btn[data-type="client"].selected {
            background: rgba(219,234,254,.70);
            box-shadow: inset 0 0 0 1.5px rgba(59,130,246,.40), 0 4px 14px rgba(59,130,246,.14);
        }
        .rp-type-btn[data-type="client"].selected::before,
        .rp-type-btn[data-type="client"]:hover::before {
            background: linear-gradient(90deg, #3b82f6, #6366f1);
        }
        /* Prestataire */
        .rp-type-btn[data-type="prestataire"].selected {
            background: rgba(254,243,199,.70);
            box-shadow: inset 0 0 0 1.5px rgba(245,158,11,.40), 0 4px 14px rgba(245,158,11,.14);
        }
        .rp-type-btn[data-type="prestataire"].selected::before,
        .rp-type-btn[data-type="prestataire"]:hover::before {
            background: linear-gradient(90deg, #f59e0b, #f97316);
        }

        .rp-type-icon { font-size: 1.3rem; margin-bottom: 2px; }
        .rp-type-btn[data-type="client"]      .rp-type-icon { color: #3b82f6; }
        .rp-type-btn[data-type="prestataire"] .rp-type-icon { color: #f59e0b; }
        .rp-type-title { font-size: 12px; font-weight: 700; color: var(--text); }
        .rp-type-desc  { font-size: 10px; color: var(--muted); line-height: 1.4; }

        /* ── Form section ── */
        .rp-section { display: none; }
        .rp-section.active { display: block; animation: rp-fadein .30s ease; }
        @keyframes rp-fadein { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

        /* ── Séparateur de section ── */
        .rp-sep {
            display: flex; align-items: center; gap: 10px;
            margin: 14px 0 12px;
        }
        .rp-sep-line { flex: 1; height: 1px; background: rgba(15,23,42,.10); }
        .rp-sep-txt  {
            font-size: 9px; font-weight: 700;
            letter-spacing: .16em; text-transform: uppercase;
            color: rgba(15,23,42,.28);
        }

        /* ── Step badge ── */
        .rp-step {
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 10px;
        }
        .rp-step-num {
            width: 20px; height: 20px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 50%; font-size: 9px; font-weight: 800; color: #fff;
        }
        .rp-step-num.blue   { background: #3b82f6; }
        .rp-step-num.amber  { background: #f59e0b; }
        .rp-step-title { font-size: 12px; font-weight: 700; color: var(--text); }
        .rp-step-badge {
            font-size: 9px; font-weight: 600;
            padding: 2px 8px; border-radius: 999px;
            background: rgba(15,23,42,.08); color: var(--muted);
        }
        .rp-step-badge.rec { background: rgba(245,158,11,.12); color: #92400e; }

        /* ── Photo profil ── */
        .rp-photo-wrap {
            display: flex; align-items: center; gap: 14px;
            padding: 12px 14px;
            background: rgba(255,255,255,.40);
            backdrop-filter: blur(10px);
            box-shadow: inset 0 0 0 1px rgba(15,23,42,.08);
            border-radius: var(--r);
            margin-bottom: 2px;
        }
        .rp-avatar {
            width: 52px; height: 52px; flex-shrink: 0;
            border-radius: 50%;
            background: rgba(255,255,255,.60);
            box-shadow: inset 0 0 0 1.5px rgba(15,23,42,.12);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; cursor: pointer;
            transition: box-shadow .18s, transform .15s;
        }
        .rp-avatar:hover { transform: scale(1.04); box-shadow: inset 0 0 0 2px rgba(59,130,246,.50); }
        .rp-avatar img  { width: 100%; height: 100%; object-fit: cover; }
        .rp-avatar i    { font-size: 1.1rem; color: rgba(15,23,42,.28); }
        .rp-photo-hint b { display: block; font-size: 12px; color: var(--text); }
        .rp-photo-hint p { font-size: 10px; color: var(--muted); margin-top: 2px; line-height: 1.5; }

        /* ── Google button ── */
        .rp-google-btn {
            display: flex; align-items: center; justify-content: center; gap: 9px;
            width: 100%;
            background: rgba(255,255,255,.60);
            backdrop-filter: blur(10px);
            box-shadow: inset 0 0 0 1px rgba(15,23,42,.09), 0 2px 8px rgba(15,23,42,.04);
            border-radius: var(--r); padding: 11px 16px;
            font-size: 12px; font-weight: 600; color: var(--text);
            text-decoration: none;
            transition: background .18s, transform .15s;
            -webkit-tap-highlight-color: transparent;
        }
        .rp-google-btn:hover { background: rgba(255,255,255,.80); }
        .rp-google-btn:active { transform: scale(.98); }

        /* ── Champs ── */
        .rp-fields { display: flex; flex-direction: column; gap: 8px; }
        .rp-field  { display: flex; flex-direction: column; gap: 4px; }
        .rp-field-row { display: flex; align-items: center; justify-content: space-between; }

        .rp-label {
            font-size: 9px; font-weight: 700;
            letter-spacing: .16em; text-transform: uppercase;
            color: rgba(15,23,42,.42);
        }
        .rp-hint { font-size: 10px; color: rgba(15,23,42,.35); margin-top: 2px; }

        .rp-input-wrap { position: relative; }
        .rp-pfx {
            position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
            font-size: 11px; color: rgba(15,23,42,.25); pointer-events: none;
            transition: color .15s;
        }
        .rp-input-wrap:focus-within .rp-pfx { color: var(--blue); }

        .rp-input, .rp-select, .rp-textarea {
            width: 100%;
            background: rgba(255,255,255,.52);
            backdrop-filter: blur(10px);
            border: 1.5px solid transparent;
            box-shadow: inset 0 0 0 1px rgba(15,23,42,.09), 0 2px 6px rgba(15,23,42,.04);
            border-radius: var(--r);
            padding: 10px 12px 10px 34px;
            font-family: var(--font-b);
            font-size: 13px; color: var(--text);
            outline: none;
            -webkit-appearance: none; appearance: none;
            transition: box-shadow .18s, background .18s, border-color .18s;
        }
        .rp-select { padding-left: 34px; }
        .rp-textarea { padding: 10px 12px; resize: vertical; min-height: 70px; }
        .rp-input::placeholder, .rp-textarea::placeholder { color: rgba(15,23,42,.25); }

        .rp-input:focus, .rp-select:focus, .rp-textarea:focus {
            background: rgba(255,255,255,.78);
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(15,58,134,.11);
        }
        .rp-input.is-error { border-color: #ef4444; }

        .rp-no-icon { padding-left: 12px; }

        .rp-toggle-pwd {
            position: absolute; right: 11px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            font-size: 13px; color: rgba(15,23,42,.28); padding: 3px;
            -webkit-tap-highlight-color: transparent; transition: color .15s;
        }
        .rp-toggle-pwd:hover { color: var(--muted); }
        .rp-field-error { font-size: 10px; color: #ef4444; }

        /* ── Checkbox ── */
        .rp-check-row {
            display: flex; align-items: flex-start; gap: 8px;
            font-size: 11px; color: var(--muted); cursor: pointer;
            -webkit-tap-highlight-color: transparent;
            margin-top: 4px;
        }
        .rp-check-row input[type="checkbox"] {
            width: 14px; height: 14px; flex-shrink: 0; margin-top: 1px;
            border-radius: 4px; accent-color: var(--blue); cursor: pointer;
        }

        /* ── Password requirements ── */
        .rp-pwd-reqs {
            background: rgba(255,255,255,.40);
            backdrop-filter: blur(8px);
            box-shadow: inset 0 0 0 1px rgba(15,23,42,.08);
            border-radius: var(--r); padding: 10px 12px;
            margin-top: 4px;
        }
        .rp-pwd-reqs h4 { font-size: 9px; font-weight: 700; color: var(--text); margin-bottom: 6px; letter-spacing: .06em; text-transform: uppercase; }
        .rp-pwd-reqs ul { list-style: none; display: flex; flex-direction: column; gap: 4px; }
        .rp-pwd-reqs li { display: flex; align-items: center; gap: 7px; font-size: 11px; color: #94a3b8; }
        .rp-req-icon { width: 14px; flex-shrink: 0; font-size: 9px; text-align: center; }
        .rp-req-ok  { color: #10b981; font-weight: 600; }
        .rp-req-ok .rp-req-icon { color: #10b981; }

        /* ── Google info box ── */
        .rp-google-info {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 10px 12px;
            background: rgba(219,234,254,.45);
            backdrop-filter: blur(8px);
            box-shadow: inset 0 0 0 1px rgba(59,130,246,.20);
            border-radius: var(--r); font-size: 11px; color: #1d4ed8;
        }
        .rp-google-info i { flex-shrink: 0; margin-top: 1px; }

        /* ── Geo button ── */
        .rp-geo-btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 7px 12px; border-radius: 8px;
            background: rgba(255,255,255,.45);
            box-shadow: inset 0 0 0 1px rgba(15,23,42,.10);
            font-size: 11px; font-weight: 600; color: var(--muted);
            border: none; cursor: pointer;
            -webkit-tap-highlight-color: transparent;
            transition: background .18s, color .18s;
            margin-top: 6px;
        }
        .rp-geo-btn:hover { background: rgba(255,255,255,.70); color: var(--blue); }
        .rp-geo-btn:disabled { opacity: .6; }

        /* ── Category block ── */
        .rp-cat-block {
            background: rgba(255,255,255,.35);
            backdrop-filter: blur(8px);
            box-shadow: inset 0 0 0 1px rgba(15,23,42,.08);
            border-radius: var(--r); padding: 12px 14px;
            display: flex; flex-direction: column; gap: 8px;
        }
        .rp-cat-block-label {
            display: flex; align-items: center; gap: 6px;
            font-size: 10px; font-weight: 700; color: var(--muted);
        }

        /* ── Submit button ── */
        .rp-submit {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; border: none; border-radius: var(--r);
            padding: 13px 20px;
            font-family: var(--font-b); font-size: 13px; font-weight: 700; color: #fff;
            cursor: pointer; margin-top: 14px;
            position: relative; overflow: hidden;
            -webkit-tap-highlight-color: transparent;
            transition: transform .15s, box-shadow .18s, filter .18s;
        }
        .rp-submit::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg,rgba(255,255,255,.12) 0%,transparent 55%);
            pointer-events: none;
        }
        .rp-submit:active { transform: scale(.98); }
        .rp-submit-client {
            background: linear-gradient(90deg, #2563eb, #6366f1);
            box-shadow: 0 8px 22px rgba(37,99,235,.32);
        }
        .rp-submit-client:hover { box-shadow: 0 12px 28px rgba(37,99,235,.40); filter: brightness(1.05); }
        .rp-submit-prestataire {
            background: linear-gradient(90deg, #d97706, #f97316);
            box-shadow: 0 8px 22px rgba(217,119,6,.32);
        }
        .rp-submit-prestataire:hover { box-shadow: 0 12px 28px rgba(217,119,6,.40); filter: brightness(1.05); }

        .rp-submit-note {
            font-size: 10px; color: rgba(15,23,42,.35);
            text-align: center; margin-top: 8px; line-height: 1.5;
        }
        .rp-submit-note a { color: var(--blue); text-decoration: none; }
        .rp-submit-note a:hover { text-decoration: underline; }

        /* ── Login link ── */
        .rp-login-link {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            margin-top: 18px; padding-top: 16px;
            border-top: 1px solid rgba(15,23,42,.10);
            font-size: 12px; color: var(--muted);
        }
        .rp-login-link a { font-weight: 700; color: var(--blue); text-decoration: none; }
        .rp-login-link a:hover { text-decoration: underline; }

        /* ── Autocomplete ── */
        .rp-autocomplete {
            position: absolute; top: 100%; left: 0; right: 0; z-index: 9999;
            background: rgba(255,255,255,.96);
            backdrop-filter: blur(12px);
            box-shadow: 0 16px 40px rgba(15,23,42,.14), inset 0 0 0 1px rgba(15,58,134,.15);
            border-radius: 10px; margin-top: 4px;
            max-height: 220px; overflow-y: auto; display: none;
        }
        .rp-ac-item {
            padding: 10px 14px; cursor: pointer;
            border-bottom: 1px solid rgba(15,23,42,.06);
            font-size: 12px; color: #334155;
            transition: background .12s;
        }
        .rp-ac-item:hover { background: rgba(15,58,134,.06); }
        .rp-ac-item:last-child { border-bottom: none; }

        /* ── Shake animation ── */
        @keyframes shake {
            0%,100%{transform:translateX(0)}
            10%,30%,50%,70%,90%{transform:translateX(-4px)}
            20%,40%,60%,80%{transform:translateX(4px)}
        }
        .error-shake { animation: shake .5s ease-in-out; }
    </style>
@endpush

@section('content')

@if(config('recaptcha.enabled') && config('recaptcha.site_key'))
    @push('scripts')
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.site_key') }}"></script>
    @endpush
@endif

@php
    $socialData        = session('social_register', []);
    $prefillName       = request('prefill_name',  session('prefill_name',  ''));
    $prefillEmail      = request('prefill_email', session('prefill_email', ''));
    $selectPrestataire = request('select_prestataire', '0') === '1';
@endphp

{{-- Orbs --}}
<div class="rp-bg" aria-hidden="true">
    <span class="o1"></span>
    <span class="o2"></span>
    <span class="o3"></span>
</div>

<div class="rp-wrap">
    <div class="rp-col">

        {{-- Brand --}}
        <div class="rp-brand">
            <span class="rp-brand-icon"><i class="fas fa-layer-group"></i></span>
            <span class="rp-brand-name">TaPrestation</span>
        </div>

        {{-- Titre --}}
        <h1 class="rp-title">Créer un compte</h1>
        <p class="rp-sub">Rejoignez des milliers d'utilisateurs en quelques minutes.</p>

        {{-- Bannière sécurité --}}
        <div class="rp-secure">
            <span class="rp-secure-icon"><i class="fas fa-shield-alt"></i></span>
            <div>
                <b>100% gratuit et sécurisé</b>
                <p>Vos données ne seront jamais partagées sans votre accord.</p>
            </div>
        </div>

        {{-- Alertes session --}}
        @if(session('info'))
            <div class="rp-alert rp-alert-info">
                <i class="fas fa-info-circle"></i><span>{{ session('info') }}</span>
            </div>
        @endif
        @if(session('warning'))
            <div class="rp-alert rp-alert-warning">
                <i class="fas fa-exclamation-triangle"></i><span>{{ session('warning') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="rp-alert rp-alert-error">
                <i class="fas fa-times-circle"></i><span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="rp-alert rp-alert-error rp-login-alert">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Erreur d'inscription</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Sélecteur de type --}}
        <p class="rp-type-label">👋 Je suis…</p>
        <div class="rp-type-grid">
            <div class="rp-type-btn" data-type="client">
                <div class="rp-type-icon"><i class="fas fa-user"></i></div>
                <div class="rp-type-title">Client</div>
                <div class="rp-type-desc">Je recherche des professionnels</div>
            </div>
            <div class="rp-type-btn" data-type="prestataire">
                <div class="rp-type-icon"><i class="fas fa-briefcase"></i></div>
                <div class="rp-type-title">Prestataire</div>
                <div class="rp-type-desc">Je propose mes services</div>
            </div>
        </div>

        {{-- ══════════════ FORMULAIRE CLIENT ══════════════ --}}
        <div id="client-form" class="rp-section">

            @if(empty($socialData))
                <a href="{{ route('social.login', ['provider' => 'google', 'role' => 'client']) }}" class="rp-google-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Continuer avec Google
                </a>
                <div class="rp-sep"><div class="rp-sep-line"></div><span class="rp-sep-txt">ou par email</span><div class="rp-sep-line"></div></div>
            @endif

            <form id="client-form-element" action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="user_type"        value="client" />
                <input type="hidden" name="recaptcha_token"  value="" />
                <input type="hidden" name="_form_start"      value="{{ time() }}" />
                <input type="hidden" name="behavior_clicks"  value="" />
                <input type="hidden" name="behavior_keypresses" value="" />
                <input type="hidden" name="behavior_time_ms" value="" />
                <div style="position:absolute;left:-9999px;" aria-hidden="true">
                    <input type="text" name="website_url" tabindex="-1" autocomplete="off" />
                </div>
                @if(!empty($socialData))
                    <input type="hidden" name="social_provider" value="{{ $socialData['provider'] ?? '' }}" />
                    <input type="hidden" name="social_id"       value="{{ $socialData['provider_id'] ?? '' }}" />
                @endif

                {{-- Photo --}}
                <div class="rp-step">
                    <span class="rp-step-num blue">1</span>
                    <span class="rp-step-title">Photo de profil</span>
                    <span class="rp-step-badge">Optionnel</span>
                </div>
                <div class="rp-photo-wrap" style="margin-bottom:14px">
                    <div class="rp-avatar" id="clientAvatarPreview">
                        @if(!empty($socialData['avatar']))
                            <img src="{{ $socialData['avatar'] }}" alt="Avatar" />
                        @else
                            <i class="fas fa-camera"></i>
                        @endif
                    </div>
                    <div class="rp-photo-hint">
                        <b>Cliquez pour ajouter une photo</b>
                        <p>Une photo augmente vos chances d'être contacté de 40%</p>
                    </div>
                    <input type="file" id="client_profile_photo" name="client_profile_photo" accept="image/*" style="display:none" />
                </div>

                {{-- Connexion --}}
                <div class="rp-step" style="margin-top:4px">
                    <span class="rp-step-num blue">2</span>
                    <span class="rp-step-title">Informations de connexion</span>
                </div>
                <div class="rp-fields" style="margin-bottom:14px">
                    <div class="rp-field">
                        <label for="client_name" class="rp-label">Nom complet *</label>
                        <div class="rp-input-wrap">
                            <i class="fas fa-user rp-pfx"></i>
                            <input id="client_name" name="name" type="text" autocomplete="name" required
                                   value="{{ old('name', $socialData['name'] ?? $prefillName ?? '') }}"
                                   placeholder="Jean Dupont" class="rp-input @error('name') is-error @enderror" />
                        </div>
                        @error('name')<span class="rp-field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="rp-field">
                        <label for="client_email" class="rp-label">Email *</label>
                        <div class="rp-input-wrap">
                            <i class="fas fa-envelope rp-pfx"></i>
                            <input id="client_email" name="email" type="email" autocomplete="email" inputmode="email" required
                                   value="{{ old('email', $socialData['email'] ?? $prefillEmail ?? '') }}"
                                   placeholder="votre@email.com"
                                   class="rp-input @error('email') is-error @enderror"
                                   {{ !empty($socialData['email']) ? 'readonly' : '' }} />
                        </div>
                        @if(!empty($socialData['email']))
                            <span class="rp-hint" style="color:#15803d"><i class="fas fa-check-circle mr-1"></i>Email vérifié via {{ ucfirst($socialData['provider'] ?? '') }}</span>
                        @endif
                        <label class="rp-check-row">
                            <input type="checkbox" name="email_visible" value="1" {{ old('email_visible') ? 'checked' : '' }} />
                            <span>Rendre mon email visible aux prestataires après un échange</span>
                        </label>
                        @error('email')<span class="rp-field-error">{{ $message }}</span>@enderror
                    </div>

                    @if(empty($socialData))
                    <div class="rp-field">
                        <label for="client_password" class="rp-label">Mot de passe *</label>
                        <div class="rp-input-wrap">
                            <i class="fas fa-lock rp-pfx"></i>
                            <input id="client_password" name="password" type="password" autocomplete="new-password" required
                                   placeholder="Minimum 8 caractères" class="rp-input" style="padding-right:38px" />
                            <button type="button" class="rp-toggle-pwd" data-target="client_password" aria-label="Afficher"><i class="fas fa-eye"></i></button>
                        </div>
                        <div class="rp-pwd-reqs" id="client_pwd_reqs">
                            <h4>Requis :</h4>
                            <ul>
                                <li data-check="length"><span class="rp-req-icon"><i class="fas fa-times"></i></span>8 caractères minimum</li>
                                <li data-check="upper"><span class="rp-req-icon"><i class="fas fa-times"></i></span>Une majuscule</li>
                                <li data-check="lower"><span class="rp-req-icon"><i class="fas fa-times"></i></span>Une minuscule</li>
                                <li data-check="digit"><span class="rp-req-icon"><i class="fas fa-times"></i></span>Un chiffre</li>
                            </ul>
                        </div>
                    </div>
                    <div class="rp-field">
                        <label for="client_password_confirmation" class="rp-label">Confirmer le mot de passe *</label>
                        <div class="rp-input-wrap">
                            <i class="fas fa-lock rp-pfx"></i>
                            <input id="client_password_confirmation" name="password_confirmation" type="password"
                                   autocomplete="new-password" required placeholder="••••••••"
                                   class="rp-input" style="padding-right:38px" />
                            <button type="button" class="rp-toggle-pwd" data-target="client_password_confirmation" aria-label="Afficher"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    @else
                    <div class="rp-google-info">
                        <i class="fab fa-google"></i>
                        <div>Connexion via <strong>{{ ucfirst($socialData['provider'] ?? 'Google') }}</strong> — aucun mot de passe requis. Vous pourrez en ajouter un plus tard.</div>
                    </div>
                    <input type="hidden" name="password" value="" />
                    <input type="hidden" name="password_confirmation" value="" />
                    @endif
                </div>

                {{-- Infos perso --}}
                <div class="rp-step">
                    <span class="rp-step-num blue">3</span>
                    <span class="rp-step-title">Informations personnelles</span>
                </div>
                <div class="rp-fields" style="margin-bottom:14px">
                    <div class="rp-field">
                        <label for="client_phone" class="rp-label">Téléphone *</label>
                        <div class="rp-input-wrap">
                            <i class="fas fa-phone rp-pfx"></i>
                            <input id="client_phone" name="phone" type="tel" inputmode="tel" required
                                   value="{{ old('phone') }}" placeholder="06 12 34 56 78"
                                   class="rp-input @error('phone') is-error @enderror" />
                        </div>
                        <label class="rp-check-row">
                            <input type="checkbox" name="phone_visible" value="1" {{ old('phone_visible') ? 'checked' : '' }} />
                            <span>Rendre mon téléphone visible aux prestataires après un échange</span>
                        </label>
                        @error('phone')<span class="rp-field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="rp-field">
                        <label for="client_location" class="rp-label">Ville / Adresse *</label>
                        <div class="rp-input-wrap" style="position:relative">
                            <i class="fas fa-map-marker-alt rp-pfx"></i>
                            <input id="client_location" name="location" type="text" autocomplete="off" required
                                   value="{{ old('location') }}" placeholder="Paris, 75001"
                                   class="rp-input @error('location') is-error @enderror" />
                            <div id="client_location_dropdown" class="rp-autocomplete"></div>
                        </div>
                        <button type="button" class="rp-geo-btn" id="client_geoloc_btn">
                            <i class="fas fa-location-arrow"></i> Ma position actuelle
                        </button>
                        <input type="hidden" id="client_latitude"  name="latitude" />
                        <input type="hidden" id="client_longitude" name="longitude" />
                        @error('location')<span class="rp-field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <button type="submit" class="rp-submit rp-submit-client">
                    <i class="fas fa-user-plus"></i> Créer mon compte Client
                </button>
                <p class="rp-submit-note">
                    En vous inscrivant, vous acceptez nos <a href="{{ route('cgu') }}">CGU</a> et notre <a href="{{ route('privacy') }}">politique de confidentialité</a>.
                </p>
            </form>
        </div>

        {{-- ══════════════ FORMULAIRE PRESTATAIRE ══════════════ --}}
        <div id="prestataire-form" class="rp-section">

            @if(empty($socialData))
                <a href="{{ route('social.login', ['provider' => 'google', 'role' => 'prestataire']) }}" class="rp-google-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Continuer avec Google
                </a>
                <div class="rp-sep"><div class="rp-sep-line"></div><span class="rp-sep-txt">ou par email</span><div class="rp-sep-line"></div></div>
            @endif

            <form id="prestataire-form-element" action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="user_type"        value="prestataire" />
                <input type="hidden" name="recaptcha_token"  value="" />
                <input type="hidden" name="_form_start"      value="{{ time() }}" />
                <input type="hidden" name="behavior_clicks"  value="" />
                <input type="hidden" name="behavior_keypresses" value="" />
                <input type="hidden" name="behavior_time_ms" value="" />
                <div style="position:absolute;left:-9999px;" aria-hidden="true">
                    <input type="text" name="company_website" tabindex="-1" autocomplete="off" />
                </div>
                @if(!empty($socialData))
                    <input type="hidden" name="social_provider" value="{{ $socialData['provider'] ?? '' }}" />
                    <input type="hidden" name="social_id"       value="{{ $socialData['provider_id'] ?? '' }}" />
                @endif

                {{-- Photo --}}
                <div class="rp-step">
                    <span class="rp-step-num amber">1</span>
                    <span class="rp-step-title">Photo professionnelle</span>
                    <span class="rp-step-badge rec">Recommandé</span>
                </div>
                <div class="rp-photo-wrap" style="margin-bottom:14px">
                    <div class="rp-avatar" id="prestataireAvatarPreview">
                        @if(!empty($socialData['avatar']))
                            <img src="{{ $socialData['avatar'] }}" alt="Avatar" />
                        @else
                            <i class="fas fa-camera"></i>
                        @endif
                    </div>
                    <div class="rp-photo-hint">
                        <b>Cliquez pour ajouter une photo</b>
                        <p>Les profils avec photo reçoivent 3× plus de demandes !</p>
                    </div>
                    <input type="file" id="prestataire_profile_photo" name="prestataire_profile_photo" accept="image/*" style="display:none" />
                </div>

                {{-- Connexion --}}
                <div class="rp-step">
                    <span class="rp-step-num amber">2</span>
                    <span class="rp-step-title">Informations de connexion</span>
                </div>
                <div class="rp-fields" style="margin-bottom:14px">
                    <div class="rp-field">
                        <label for="prestataire_name" class="rp-label">Identifiant / Nom *</label>
                        <div class="rp-input-wrap">
                            <i class="fas fa-user rp-pfx"></i>
                            <input id="prestataire_name" name="name" type="text" autocomplete="name" required
                                   value="{{ old('name', $socialData['name'] ?? $prefillName ?? '') }}"
                                   placeholder="Jean Dupont ou MonEntreprise"
                                   class="rp-input @error('name') is-error @enderror" />
                        </div>
                        @error('name')<span class="rp-field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="rp-field">
                        <label for="prestataire_email" class="rp-label">Email professionnel *</label>
                        <div class="rp-input-wrap">
                            <i class="fas fa-envelope rp-pfx"></i>
                            <input id="prestataire_email" name="email" type="email" autocomplete="email" inputmode="email" required
                                   value="{{ old('email', $socialData['email'] ?? $prefillEmail ?? '') }}"
                                   placeholder="contact@entreprise.com"
                                   class="rp-input @error('email') is-error @enderror"
                                   {{ !empty($socialData['email']) ? 'readonly' : '' }} />
                        </div>
                        <label class="rp-check-row">
                            <input type="checkbox" name="email_visible" value="1" {{ old('email_visible') ? 'checked' : '' }} />
                            <span>Rendre mon email visible aux clients après un échange</span>
                        </label>
                        @error('email')<span class="rp-field-error">{{ $message }}</span>@enderror
                    </div>

                    @if(empty($socialData))
                    <div class="rp-field">
                        <label for="prestataire_password" class="rp-label">Mot de passe *</label>
                        <div class="rp-input-wrap">
                            <i class="fas fa-lock rp-pfx"></i>
                            <input id="prestataire_password" name="password" type="password" autocomplete="new-password" required
                                   placeholder="Minimum 8 caractères" class="rp-input" style="padding-right:38px" />
                            <button type="button" class="rp-toggle-pwd" data-target="prestataire_password" aria-label="Afficher"><i class="fas fa-eye"></i></button>
                        </div>
                        <div class="rp-pwd-reqs" id="prestataire_pwd_reqs">
                            <h4>Requis :</h4>
                            <ul>
                                <li data-check="length"><span class="rp-req-icon"><i class="fas fa-times"></i></span>8 caractères minimum</li>
                                <li data-check="upper"><span class="rp-req-icon"><i class="fas fa-times"></i></span>Une majuscule</li>
                                <li data-check="lower"><span class="rp-req-icon"><i class="fas fa-times"></i></span>Une minuscule</li>
                                <li data-check="digit"><span class="rp-req-icon"><i class="fas fa-times"></i></span>Un chiffre</li>
                            </ul>
                        </div>
                    </div>
                    <div class="rp-field">
                        <label for="prestataire_password_confirmation" class="rp-label">Confirmer le mot de passe *</label>
                        <div class="rp-input-wrap">
                            <i class="fas fa-lock rp-pfx"></i>
                            <input id="prestataire_password_confirmation" name="password_confirmation" type="password"
                                   autocomplete="new-password" required placeholder="••••••••"
                                   class="rp-input" style="padding-right:38px" />
                            <button type="button" class="rp-toggle-pwd" data-target="prestataire_password_confirmation" aria-label="Afficher"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    @else
                    <div class="rp-google-info">
                        <i class="fab fa-google"></i>
                        <div>Connexion via <strong>{{ ucfirst($socialData['provider'] ?? 'Google') }}</strong> — aucun mot de passe requis. Vous pourrez en ajouter un plus tard.</div>
                    </div>
                    <input type="hidden" name="password" value="" />
                    <input type="hidden" name="password_confirmation" value="" />
                    @endif
                </div>

                {{-- Infos pro --}}
                <div class="rp-step">
                    <span class="rp-step-num amber">3</span>
                    <span class="rp-step-title">Informations professionnelles</span>
                </div>
                <div class="rp-fields" style="margin-bottom:14px">
                    <div class="rp-field">
                        <label for="company_name" class="rp-label">Nom de l'enseigne *</label>
                        <div class="rp-input-wrap">
                            <i class="fas fa-store rp-pfx"></i>
                            <input id="company_name" name="company_name" type="text" required
                                   value="{{ old('company_name') }}" placeholder="Plomberie Martin…"
                                   class="rp-input @error('company_name') is-error @enderror" />
                        </div>
                        @error('company_name')<span class="rp-field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="rp-field">
                        <label for="phone" class="rp-label">Téléphone professionnel *</label>
                        <div class="rp-input-wrap">
                            <i class="fas fa-phone rp-pfx"></i>
                            <input id="phone" name="phone" type="tel" inputmode="tel" required
                                   value="{{ old('phone') }}" placeholder="06 12 34 56 78"
                                   class="rp-input @error('phone') is-error @enderror" />
                        </div>
                        <label class="rp-check-row">
                            <input type="checkbox" name="phone_visible" value="1" checked />
                            <span>Rendre mon téléphone visible sur mon profil</span>
                        </label>
                        @error('phone')<span class="rp-field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="rp-field">
                        <label for="prestataire_location" class="rp-label">Zone d'intervention *</label>
                        <div class="rp-input-wrap" style="position:relative">
                            <i class="fas fa-map-marker-alt rp-pfx"></i>
                            <input id="prestataire_location" name="city" type="text" autocomplete="off" required
                                   value="{{ old('city') }}" placeholder="Lyon, 69000"
                                   class="rp-input @error('city') is-error @enderror" />
                            <div id="prestataire_location_dropdown" class="rp-autocomplete"></div>
                        </div>
                        <button type="button" class="rp-geo-btn" id="prestataire_geoloc_btn">
                            <i class="fas fa-location-arrow"></i> Ma position actuelle
                        </button>
                        <input type="hidden" id="prestataire_latitude"  name="latitude" />
                        <input type="hidden" id="prestataire_longitude" name="longitude" />
                        @error('city')<span class="rp-field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="rp-field">
                        <label for="description" class="rp-label">Description <span style="font-weight:400;opacity:.6">(optionnel)</span></label>
                        <textarea id="description" name="description" class="rp-textarea rp-no-icon"
                                  placeholder="Décrivez vos compétences et ce qui vous différencie…">{{ old('description') }}</textarea>
                    </div>

                    <div class="rp-cat-block">
                        <div class="rp-cat-block-label">
                            <i class="fas fa-tags" style="color:#f59e0b"></i> Domaine d'activité
                        </div>
                        <div class="rp-field">
                            <label for="category_id" class="rp-label">Catégorie principale *</label>
                            <div class="rp-input-wrap">
                                <i class="fas fa-layer-group rp-pfx"></i>
                                <select id="category_id" name="category_id" class="rp-select rp-no-icon" required style="padding-left:34px">
                                    <option value="">Sélectionnez une catégorie</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="rp-field">
                            <label for="subcategory_id" class="rp-label">Spécialité *</label>
                            <div class="rp-input-wrap">
                                <i class="fas fa-tag rp-pfx"></i>
                                <select id="subcategory_id" name="subcategory_id" class="rp-select" required style="padding-left:34px" disabled>
                                    <option value="">Sélectionnez d'abord une catégorie</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="rp-field">
                        <label for="portfolio_url" class="rp-label">Site web / Portfolio <span style="font-weight:400;opacity:.6">(optionnel)</span></label>
                        <div class="rp-input-wrap">
                            <i class="fas fa-globe rp-pfx"></i>
                            <input id="portfolio_url" name="portfolio_url" type="url"
                                   value="{{ old('portfolio_url') }}" placeholder="https://votre-site.com"
                                   class="rp-input" />
                        </div>
                    </div>
                </div>

                <button type="submit" class="rp-submit rp-submit-prestataire">
                    <i class="fas fa-briefcase"></i> Créer mon compte Prestataire
                </button>
                <p class="rp-submit-note">
                    En vous inscrivant, vous acceptez nos <a href="{{ route('cgu') }}">CGU</a> et notre <a href="{{ route('privacy') }}">politique de confidentialité</a>.
                </p>
            </form>
        </div>

        {{-- Lien connexion --}}
        <div class="rp-login-link">
            <span>Déjà un compte ?</span>
            <a href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i> Se connecter</a>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const hasErrors          = {{ $errors->any() ? 'true' : 'false' }};
    const hasSocialData      = {{ !empty($socialData) ? 'true' : 'false' }};
    const recaptchaEnabled   = {{ config('recaptcha.enabled') && config('recaptcha.site_key') ? 'true' : 'false' }};
    const recaptchaSiteKey   = "{{ config('recaptcha.site_key') }}";
    const initialUserType    = {!! Js::from(old('user_type', request('select_prestataire') === '1' ? 'prestataire' : request('type', 'client'))) !!};

    const typeButtons        = document.querySelectorAll('.rp-type-btn');
    const clientSection      = document.getElementById('client-form');
    const prestataireSection = document.getElementById('prestataire-form');

    const behavior = { startedAt: Date.now(), clicks: 0, keypresses: 0 };
    document.addEventListener('click',   () => behavior.clicks++,     true);
    document.addEventListener('keydown', () => behavior.keypresses++, true);

    /* ── Disable / enable fields ── */
    function disableSection(wrapper) {
        if (!wrapper) return;
        wrapper.querySelectorAll('input,select,textarea,button').forEach(el => {
            if (el.name !== '_token') { el.disabled = true; el.removeAttribute('required'); }
        });
    }

    function enableSection(wrapper, type) {
        if (!wrapper) return;
        wrapper.querySelectorAll('input,select,textarea,button').forEach(el => {
            if (el.name === '_token') return;
            el.disabled = false;
        });
        const required = type === 'client'
            ? ['client_name','client_email','client_phone','client_location',
               ...(!hasSocialData ? ['client_password','client_password_confirmation'] : [])]
            : ['prestataire_name','prestataire_email','company_name','phone',
               'prestataire_location','category_id','subcategory_id',
               ...(!hasSocialData ? ['prestataire_password','prestataire_password_confirmation'] : [])];
        required.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.setAttribute('required','required');
        });
    }

    /* ── Type selector ── */
    function selectType(type) {
        typeButtons.forEach(b => b.classList.remove('selected'));
        const btn = document.querySelector(`.rp-type-btn[data-type="${type}"]`);
        if (btn) btn.classList.add('selected');

        clientSection.classList.remove('active');
        prestataireSection.classList.remove('active');
        disableSection(clientSection);
        disableSection(prestataireSection);

        if (type === 'client') {
            clientSection.classList.add('active');
            enableSection(clientSection, 'client');
        } else {
            prestataireSection.classList.add('active');
            enableSection(prestataireSection, 'prestataire');
            loadMainCategories();
        }
    }

    typeButtons.forEach(btn => btn.addEventListener('click', () => selectType(btn.dataset.type)));
    disableSection(clientSection);
    disableSection(prestataireSection);
    if (initialUserType === 'client' || initialUserType === 'prestataire') selectType(initialUserType);

    /* ── Photo preview ── */
    function bindAvatar(previewId, inputId) {
        const preview = document.getElementById(previewId);
        const input   = document.getElementById(inputId);
        if (!preview || !input) return;
        preview.addEventListener('click', () => input.click());
        input.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const r = new FileReader();
                r.onload = e => {
                    preview.innerHTML = '';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    preview.appendChild(img);
                };
                r.readAsDataURL(this.files[0]);
            }
        });
    }
    bindAvatar('clientAvatarPreview',      'client_profile_photo');
    bindAvatar('prestataireAvatarPreview', 'prestataire_profile_photo');

    /* ── Toggle password ── */
    document.querySelectorAll('.rp-toggle-pwd').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            const icon  = this.querySelector('i');
            if (!input || !icon) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.classList.toggle('fa-eye',      !show);
            icon.classList.toggle('fa-eye-slash', show);
        });
    });

    /* ── Password requirements ── */
    function bindPwdReqs(inputId, reqsId) {
        const input = document.getElementById(inputId);
        const reqs  = document.getElementById(reqsId);
        if (!input || !reqs) return;
        const checks = { length: p => p.length >= 8, upper: p => /[A-Z]/.test(p), lower: p => /[a-z]/.test(p), digit: p => /[0-9]/.test(p) };
        input.addEventListener('input', function () {
            reqs.querySelectorAll('li').forEach(li => {
                const ok   = checks[li.dataset.check]?.(this.value);
                const icon = li.querySelector('i');
                li.classList.toggle('rp-req-ok', ok);
                if (icon) { icon.classList.toggle('fa-check', ok); icon.classList.toggle('fa-times', !ok); }
            });
        });
    }
    bindPwdReqs('client_password',      'client_pwd_reqs');
    bindPwdReqs('prestataire_password', 'prestataire_pwd_reqs');

    /* ── Autocomplete adresse ── */
    let acTimer = null;
    function bindAutocomplete(inputId, dropdownId, latId, lonId) {
        const input    = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        if (!input || !dropdown) return;

        input.addEventListener('input', function () {
            clearTimeout(acTimer);
            const q = this.value.trim();
            if (q.length < 3) { dropdown.style.display = 'none'; return; }
            dropdown.innerHTML = '<div class="rp-ac-item" style="color:#94a3b8"><i class="fas fa-spinner fa-spin"></i> Recherche…</div>';
            dropdown.style.display = 'block';
            acTimer = setTimeout(() => {
                fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(q)}&limit=5&autocomplete=1`)
                    .then(r => r.json())
                    .then(data => {
                        dropdown.innerHTML = '';
                        if (!data.features?.length) {
                            dropdown.innerHTML = '<div class="rp-ac-item" style="color:#94a3b8">Aucun résultat</div>';
                            return;
                        }
                        data.features.forEach(f => {
                            const item = document.createElement('div');
                            item.className = 'rp-ac-item';
                            item.innerHTML = `📍 <strong>${f.properties.label}</strong>`;
                            item.addEventListener('click', () => {
                                input.value = f.properties.label;
                                const lat = document.getElementById(latId);
                                const lon = document.getElementById(lonId);
                                if (lat) lat.value = f.geometry.coordinates[1];
                                if (lon) lon.value = f.geometry.coordinates[0];
                                dropdown.style.display = 'none';
                            });
                            dropdown.appendChild(item);
                        });
                        dropdown.style.display = 'block';
                    })
                    .catch(() => { dropdown.style.display = 'none'; });
            }, 300);
        });
        input.addEventListener('blur', () => setTimeout(() => dropdown.style.display = 'none', 200));
        document.addEventListener('click', e => {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) dropdown.style.display = 'none';
        });
    }
    bindAutocomplete('client_location',      'client_location_dropdown',      'client_latitude',      'client_longitude');
    bindAutocomplete('prestataire_location', 'prestataire_location_dropdown', 'prestataire_latitude', 'prestataire_longitude');

    /* ── Géolocalisation ── */
    function bindGeoloc(btnId, inputId, latId, lonId) {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        btn.addEventListener('click', function () {
            if (!navigator.geolocation) { alert('Géolocalisation non supportée'); return; }
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Localisation…';
            btn.disabled = true;
            navigator.geolocation.getCurrentPosition(pos => {
                const { latitude: lat, longitude: lon } = pos.coords;
                fetch(`https://api-adresse.data.gouv.fr/reverse/?lon=${lon}&lat=${lat}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.features?.[0]) {
                            document.getElementById(inputId).value = data.features[0].properties.label;
                            const lEl = document.getElementById(latId); const loEl = document.getElementById(lonId);
                            if (lEl) lEl.value = lat; if (loEl) loEl.value = lon;
                        }
                        btn.innerHTML = '<i class="fas fa-check"></i> Position trouvée';
                        setTimeout(() => { btn.innerHTML = '<i class="fas fa-location-arrow"></i> Ma position actuelle'; btn.disabled = false; }, 2000);
                    }).catch(() => { btn.innerHTML = '<i class="fas fa-location-arrow"></i> Ma position actuelle'; btn.disabled = false; });
            }, () => { btn.innerHTML = '<i class="fas fa-location-arrow"></i> Ma position actuelle'; btn.disabled = false; },
            { enableHighAccuracy: true, timeout: 10000 });
        });
    }
    bindGeoloc('client_geoloc_btn',      'client_location',      'client_latitude',      'client_longitude');
    bindGeoloc('prestataire_geoloc_btn', 'prestataire_location', 'prestataire_latitude', 'prestataire_longitude');

    /* ── Catégories ── */
    function loadMainCategories() {
        const sel = document.getElementById('category_id');
        if (!sel || sel.options.length > 1) return;
        fetch('/api/categories/main').then(r => r.json()).then(data => {
            sel.innerHTML = '<option value="">Sélectionnez une catégorie</option>';
            (data || []).forEach(c => {
                const o = document.createElement('option');
                o.value = c.id; o.textContent = c.name; sel.appendChild(o);
            });
            const old = "{{ old('category_id') }}";
            if (old) { sel.value = old; sel.dispatchEvent(new Event('change')); }
        }).catch(() => {});
    }

    const catSel = document.getElementById('category_id');
    const subSel = document.getElementById('subcategory_id');
    if (catSel && subSel) {
        catSel.addEventListener('change', function () {
            if (!this.value) { subSel.innerHTML = '<option value="">Sélectionnez d\'abord une catégorie</option>'; subSel.disabled = true; return; }
            fetch(`/api/categories/${this.value}/subcategories`).then(r => r.json()).then(data => {
                subSel.innerHTML = '<option value="">Sélectionnez une sous-catégorie</option>';
                (data || []).forEach(s => { const o = document.createElement('option'); o.value = s.id; o.textContent = s.name; subSel.appendChild(o); });
                subSel.disabled = false;
                const old = "{{ old('subcategory_id') }}"; if (old) subSel.value = old;
            }).catch(() => { subSel.innerHTML = '<option value="">Erreur</option>'; subSel.disabled = true; });
        });
    }

    /* ── Submit ── */
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (e) {
            const selected = document.querySelector('.rp-type-btn.selected');
            if (!selected) { e.preventDefault(); alert('Veuillez sélectionner un type de compte.'); return; }
            const type = selected.dataset.type;
            const formType = form.id === 'client-form-element' ? 'client' : 'prestataire';
            if (type !== formType) { e.preventDefault(); return; }

            const timeMs = Math.max(0, Date.now() - behavior.startedAt);
            const fillHidden = (name, val) => { const el = form.querySelector(`input[name="${name}"]`); if (el) el.value = String(val); };
            fillHidden('behavior_clicks',      behavior.clicks);
            fillHidden('behavior_keypresses',  behavior.keypresses);
            fillHidden('behavior_time_ms',     timeMs);

            if (recaptchaEnabled) {
                const rcInput = form.querySelector('input[name="recaptcha_token"]');
                if (rcInput && !rcInput.value) {
                    e.preventDefault();
                    if (!window.grecaptcha) { alert('reCAPTCHA indisponible. Rechargez la page.'); return; }
                    window.grecaptcha.ready(() => {
                        window.grecaptcha.execute(recaptchaSiteKey, { action: 'register' })
                            .then(token => { rcInput.value = token; form.submit(); })
                            .catch(() => alert('Erreur reCAPTCHA. Réessayez.'));
                    });
                    return;
                }
            }

            const btn = form.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Inscription…'; }
        });
    });

    /* ── Scroll vers erreurs ── */
    if (hasErrors) {
        setTimeout(() => {
            const el = document.querySelector('.rp-login-alert');
            if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); el.classList.add('error-shake'); }
        }, 200);
    }

    /* ── Refresh CSRF ── */
    function refreshCSRF() {
        fetch('/csrf-token').then(r => r.json()).then(data => {
            document.querySelectorAll('input[name="_token"]').forEach(i => i.value = data.csrf_token || data.token || i.value);
        }).catch(() => {});
    }
    setInterval(refreshCSRF, 30 * 60 * 1000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refreshCSRF(); });
});
</script>
@endpush

@endsection