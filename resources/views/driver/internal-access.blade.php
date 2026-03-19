@extends('layouts.app')

@section('title', 'Accès livreur interne')

@section('content')
<style>
    .internal-access-page { min-height: calc(100vh - 80px); background: #f8fafc; padding: 20px 14px 100px; }
    .internal-access-card { max-width: 520px; margin: 0 auto; background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 20px; box-shadow: 0 10px 30px rgba(15,23,42,.07); }
    .internal-access-title { font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0 0 6px; }
    .internal-access-sub { color: #475569; font-size: .92rem; margin: 0 0 16px; }
    .internal-label { display:block; font-size:.85rem; color:#334155; font-weight:700; margin:0 0 7px; }
    .internal-input { width:100%; border:2px solid #cbd5e1; border-radius:12px; padding:12px 14px; font-size:1rem; font-weight:700; letter-spacing:.3px; color:#0f172a; text-transform:uppercase; }
    .internal-input:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
    .internal-btn { width:100%; margin-top:12px; border:0; border-radius:12px; background:#2563eb; color:#fff; font-size:.95rem; font-weight:800; padding:12px 16px; cursor:pointer; }
    .internal-btn:hover { background:#1d4ed8; }
    .internal-alert { border-radius:10px; padding:10px 12px; font-size:.85rem; margin-bottom:12px; }
    .internal-alert.ok { background:#ecfdf5; color:#166534; border:1px solid #86efac; }
    .internal-alert.err { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
    .internal-active { background:#eff6ff; color:#1e3a8a; border:1px solid #bfdbfe; border-radius:10px; padding:10px 12px; font-size:.82rem; margin-bottom:12px; }
</style>

<div class="internal-access-page">
    <div class="internal-access-card">
        <h1 class="internal-access-title">Accès livreur interne</h1>
        <p class="internal-access-sub">Entrez le code fourni par votre prestataire pour ouvrir la carte de vos tournées.</p>

        @if(!empty($activeDriver))
            <div class="internal-active">
                Session active: {{ trim(($activeDriver->first_name ?? '') . ' ' . ($activeDriver->last_name ?? '')) ?: ('#' . $activeDriver->id) }}.
                Vous pouvez saisir un autre code si nécessaire.
            </div>
        @endif

        @if(session('success'))
            <div class="internal-alert ok">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="internal-alert err">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('driver.internal.access.submit') }}">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ old('redirect_to', $redirectTo ?? request('redirect_to')) }}">
            <label for="code" class="internal-label">Code livreur</label>
            <input id="code"
                   name="code"
                   type="text"
                   value="{{ old('code') }}"
                   placeholder="INT-AB-1234"
                   required
                   autocomplete="one-time-code"
                   class="internal-input">
            @error('code')
                <p style="color:#b91c1c;font-size:.8rem;margin-top:6px;">{{ $message }}</p>
            @enderror

            <button type="submit" class="internal-btn">Accéder à ma tournée</button>
        </form>

        <div style="margin-top:14px;font-size:.78rem;color:#64748b;">
            Vous avez un compte livreur classique ?
            <a href="{{ route('login') }}" style="color:#1d4ed8;font-weight:700;text-decoration:none;">Connexion</a>
        </div>
    </div>
</div>
@endsection
