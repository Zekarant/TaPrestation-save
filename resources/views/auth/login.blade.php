@extends('layouts.app')

@section('content')

@if(config('recaptcha.enabled') && config('recaptcha.site_key'))
    @push('head')
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.site_key') }}"></script>
    @endpush
@endif

@push('head')
    <meta name="theme-color" content="#0f3a86" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-title" content="TaPrestation" />
    <meta name="mobile-web-app-capable" content="yes" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,700;9..144,900&display=swap" rel="stylesheet" />

    <style>
        :root {
            --blue:     #0f3a86;
            --blue-mid: #2d67c8;
            --gold-bg:  #f4e4bf;
            --gold-bd:  #c7aa74;
            --text:     #0f172a;
            --muted:    #64748b;
            --r:        14px;
            --font-d:   'Fraunces', Georgia, serif;
            --font-b:   'Plus Jakarta Sans', sans-serif;
        }

        html, body {
            height: 100%;
            overflow: hidden;
            font-family: var(--font-b);
            background: #ede5d8;
        }

        /* ── Orbs ambiants ── */
        .lp-bg { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
        .lp-bg span { position: absolute; border-radius: 50%; animation: lp-drift 14s ease-in-out infinite alternate; }
        .lp-bg .o1 { width:420px;height:420px;top:-140px;left:-120px;background:radial-gradient(circle,rgba(15,58,134,.13),transparent 65%); }
        .lp-bg .o2 { width:320px;height:320px;top:30px;right:-90px;background:radial-gradient(circle,rgba(215,182,121,.20),transparent 65%);animation-delay:-5s; }
        .lp-bg .o3 { width:300px;height:300px;bottom:-100px;left:50%;transform:translateX(-50%);background:radial-gradient(circle,rgba(255,255,255,.45),transparent 65%);animation-delay:-9s; }
        @keyframes lp-drift { from{transform:translate(0,0)} to{transform:translate(16px,12px)} }
        .lp-bg .o3 { animation-name:lp-drift-c; }
        @keyframes lp-drift-c { from{transform:translateX(-50%)} to{transform:translateX(calc(-50% + 14px))} }

        /* ── Wrapper pleine page ── */
        .lp-wrap {
            position: relative; z-index: 1;
            height: 100dvh;
            display: flex; align-items: center; justify-content: center;
            padding: env(safe-area-inset-top,12px) 24px env(safe-area-inset-bottom,12px);
            padding-bottom: 14vh;
        }

        /* ── Colonne centrale ── */
        .lp-col {
            width: 100%; max-width: 360px;
            display: flex; flex-direction: column;
            animation: lp-up .38s ease both;
        }
        @keyframes lp-up { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }

        /* ── Brand ── */
        .lp-brand {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 16px;
        }
        .lp-brand-icon {
            width: 34px; height: 34px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 10px;
            background: var(--blue); color: #fff; font-size: 11px;
            box-shadow: 0 5px 14px rgba(15,58,134,.30);
        }
        .lp-brand-name {
            font-size: 9px; font-weight: 800;
            letter-spacing: .22em; text-transform: uppercase;
            background: linear-gradient(90deg, var(--blue), var(--blue-mid));
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ── Titre ── */
        .lp-title {
            font-family: var(--font-d);
            font-size: 2.3rem; font-weight: 900;
            letter-spacing: -.05em; line-height: .92;
            color: var(--text); margin-bottom: 4px;
        }
        .lp-sub {
            font-size: 12px; color: var(--muted); line-height: 1.55;
            margin-bottom: 16px;
        }

        /* ── Alerts ── */
        .lp-alert {
            display: flex; align-items: flex-start; gap: 8px;
            padding: 9px 12px; border-radius: var(--r);
            font-size: 12px; margin-bottom: 10px; line-height: 1.5;
        }
        .lp-alert i { flex-shrink: 0; margin-top: 1px; }
        .lp-alert-error   { background: rgba(239,68,68,.09);  color: #b91c1c; }
        .lp-alert-success { background: rgba(34,197,94,.09);  color: #15803d; }
        .lp-alert-warning { background: rgba(234,179,8,.10);  color: #b45309; }
        .lp-alert-error ul { padding-left: 16px; margin-top: 4px; list-style: disc; }
        .lp-alert-error li { font-size: 11px; }

        /* ── Boutons sociaux ── */
        .lp-social { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 14px; }

        .lp-soc-btn {
            display: flex; align-items: center; gap: 9px;
            padding: 10px 12px; border-radius: var(--r);
            text-decoration: none;
            -webkit-tap-highlight-color: transparent;
            transition: transform .15s, opacity .15s;
        }
        .lp-soc-btn:active { transform: scale(.97); }

        .lp-soc-google {
            background: rgba(255,255,255,.62);
            backdrop-filter: blur(10px);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.80), 0 2px 10px rgba(15,23,42,.07);
        }
        .lp-soc-google:hover { background: rgba(255,255,255,.82); }

        .lp-soc-driver {
            background: rgba(244,228,191,.68);
            backdrop-filter: blur(10px);
            box-shadow: inset 0 0 0 1px rgba(199,170,116,.45), 0 2px 10px rgba(15,23,42,.06);
        }
        .lp-soc-driver:hover { background: rgba(236,212,160,.80); }

        .lp-soc-ico {
            width: 26px; height: 26px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 7px;
        }
        .lp-soc-google .lp-soc-ico { background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.10); }
        .lp-soc-driver .lp-soc-ico { background: rgba(255,255,255,.65); color: #7a4a00; font-size: 11px; }

        .lp-soc-text { line-height: 1.2; }
        .lp-soc-text b  { display: block; font-size: 12px; font-weight: 700; color: var(--text); }
        .lp-soc-text em { display: block; font-size: 10px; font-style: normal; font-weight: 500; color: var(--muted); }
        .lp-soc-driver .lp-soc-text em { color: #5c4424; }

        /* ── Séparateur ── */
        .lp-divider {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 12px;
        }
        .lp-divider-line { flex: 1; height: 1px; background: rgba(15,23,42,.11); }
        .lp-divider-txt  {
            font-size: 9px; font-weight: 700;
            letter-spacing: .16em; text-transform: uppercase;
            color: rgba(15,23,42,.28);
        }

        /* ── Champs ── */
        .lp-fields { display: flex; flex-direction: column; gap: 8px; margin-bottom: 10px; }
        .lp-field { display: flex; flex-direction: column; gap: 4px; }
        .lp-field-row { display: flex; align-items: center; justify-content: space-between; }

        .lp-label {
            font-size: 9px; font-weight: 700;
            letter-spacing: .16em; text-transform: uppercase;
            color: rgba(15,23,42,.42);
        }
        .lp-forgot {
            font-size: 10px; font-weight: 600; color: var(--blue);
            text-decoration: none;
        }
        .lp-forgot:hover { text-decoration: underline; }

        .lp-input-wrap { position: relative; }
        .lp-pfx {
            position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
            font-size: 11px; color: rgba(15,23,42,.25); pointer-events: none;
            transition: color .15s;
        }
        .lp-input-wrap:focus-within .lp-pfx { color: var(--blue); }

        .lp-input {
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
        .lp-input::placeholder { color: rgba(15,23,42,.25); }
        .lp-input:focus {
            background: rgba(255,255,255,.78);
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(15,58,134,.11);
        }
        .lp-input.is-error { border-color: #ef4444; }
        .lp-input.is-error:focus { box-shadow: 0 0 0 3px rgba(239,68,68,.10); }

        .lp-toggle-pwd {
            position: absolute; right: 11px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            font-size: 13px; color: rgba(15,23,42,.28); padding: 3px;
            -webkit-tap-highlight-color: transparent;
            transition: color .15s;
        }
        .lp-toggle-pwd:hover { color: var(--muted); }

        .lp-field-error { font-size: 10px; color: #ef4444; }

        /* ── Remember ── */
        .lp-remember {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 10px; padding: 0 2px;
        }
        .lp-remember-label {
            display: flex; align-items: center; gap: 7px;
            font-size: 11px; color: var(--muted); cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }
        .lp-remember-check {
            width: 14px; height: 14px; border-radius: 4px;
            accent-color: var(--blue); flex-shrink: 0; cursor: pointer;
        }
        .lp-remember-badge { font-size: 10px; font-weight: 600; color: rgba(15,23,42,.28); }

        /* ── Bouton submit ── */
        .lp-submit {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; border: none; border-radius: var(--r);
            background: var(--blue); padding: 12px 20px;
            font-family: var(--font-b); font-size: 13px; font-weight: 700; color: #fff;
            cursor: pointer; margin-bottom: 14px;
            box-shadow: 0 8px 20px rgba(15,58,134,.30);
            -webkit-tap-highlight-color: transparent;
            position: relative; overflow: hidden;
            transition: background .18s, transform .15s, box-shadow .18s;
        }
        .lp-submit::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg,rgba(255,255,255,.11) 0%,transparent 55%);
            pointer-events: none;
        }
        .lp-submit:hover  { background: #0c2f6d; box-shadow: 0 10px 26px rgba(15,58,134,.36); }
        .lp-submit:active { transform: scale(.98); }

        /* ── Bas de page ── */
        .lp-bottom {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 6px;
        }
        .lp-register { font-size: 11px; color: var(--muted); }
        .lp-register a { font-weight: 700; color: var(--blue); text-decoration: none; }
        .lp-register a:hover { text-decoration: underline; }

        .lp-footer-links { display: flex; gap: 12px; }
        .lp-footer-links a {
            font-size: 10px; color: rgba(15,23,42,.28); text-decoration: none;
            transition: color .15s;
        }
        .lp-footer-links a:hover { color: var(--muted); }
    </style>
@endpush

{{-- Orbs --}}
<div class="lp-bg" aria-hidden="true">
    <span class="o1"></span>
    <span class="o2"></span>
    <span class="o3"></span>
</div>

<div class="lp-wrap">
    <div class="lp-col">

        {{-- Brand --}}
        <div class="lp-brand">
            <span class="lp-brand-icon"><i class="fas fa-layer-group"></i></span>
            <span class="lp-brand-name">TaPrestation</span>
        </div>

        {{-- Titre --}}
        <h1 class="lp-title">Connexion</h1>
        <p class="lp-sub">Accédez à votre espace en quelques secondes.</p>

        {{-- Alertes --}}
        @if (session('status'))
            <div class="lp-alert lp-alert-success">
                <i class="fas fa-check-circle"></i><span>{{ session('status') }}</span>
            </div>
        @endif
        @if (session('success'))
            <div class="lp-alert lp-alert-success">
                <i class="fas fa-check-circle"></i><span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="lp-alert lp-alert-error lp-login-alert">
                <i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span>
            </div>
        @endif
        @if (session('warning'))
            <div class="lp-alert lp-alert-warning">
                <i class="fas fa-exclamation-triangle"></i><span>{{ session('warning') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="lp-alert lp-alert-error lp-login-alert">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Erreur de connexion</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Sociaux --}}
        <div class="lp-social">
            <a href="{{ route('social.login', 'google') }}" class="lp-soc-btn lp-soc-google">
                <span class="lp-soc-ico">
                    <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                </span>
                <span class="lp-soc-text"><b>Google</b><em>Connexion rapide</em></span>
            </a>

            <a href="{{ route('driver.internal.access') }}" class="lp-soc-btn lp-soc-driver">
                <span class="lp-soc-ico"><i class="fas fa-route"></i></span>
                <span class="lp-soc-text"><b>Livreur</b><em>Code interne</em></span>
            </a>
        </div>

        {{-- Séparateur --}}
        <div class="lp-divider">
            <div class="lp-divider-line"></div>
            <span class="lp-divider-txt">ou par email</span>
            <div class="lp-divider-line"></div>
        </div>

        {{-- Formulaire --}}
        <form id="login-form" action="{{ route('login') }}" method="POST">
            @csrf
            <input type="hidden" name="timezone"        id="timezone"        value="" />
            <input type="hidden" name="recaptcha_token" id="recaptcha_token" value="" />

            @error('recaptcha')
                <div class="lp-alert lp-alert-error" style="margin-bottom:8px">
                    <i class="fas fa-exclamation-circle"></i><span>{{ $message }}</span>
                </div>
            @enderror

            <div class="lp-fields">
                {{-- Email --}}
                <div class="lp-field">
                    <label for="email-address" class="lp-label">Email</label>
                    <div class="lp-input-wrap">
                        <i class="fas fa-envelope lp-pfx"></i>
                        <input
                            id="email-address" name="email" type="email"
                            autocomplete="email" inputmode="email" required
                            value="{{ old('email') }}" placeholder="exemple@email.com"
                            class="lp-input @error('email') is-error @enderror"
                        />
                    </div>
                    @error('email')
                        <span class="lp-field-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Mot de passe --}}
                <div class="lp-field">
                    <div class="lp-field-row">
                        <label for="password" class="lp-label">Mot de passe</label>
                        <a href="{{ route('password.request') }}" class="lp-forgot">Oublié ?</a>
                    </div>
                    <div class="lp-input-wrap">
                        <i class="fas fa-lock lp-pfx"></i>
                        <input
                            id="password" name="password" type="password"
                            autocomplete="current-password" required
                            placeholder="••••••••"
                            class="lp-input"
                            style="padding-right: 38px"
                        />
                        <button type="button" class="lp-toggle-pwd" data-target="password" aria-label="Afficher">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Remember --}}
            <div class="lp-remember">
                <label class="lp-remember-label">
                    <input name="remember" type="checkbox" class="lp-remember-check" {{ old('remember') ? 'checked' : '' }} />
                    Rester connecté 30 jours
                </label>
                <span class="lp-remember-badge">Connexion rapide</span>
            </div>

            {{-- Soumettre --}}
            <button type="submit" class="lp-submit">
                <i class="fas fa-sign-in-alt"></i>
                Se connecter
            </button>
        </form>

        {{-- Bas --}}
        <div class="lp-bottom">
            <div class="lp-register">
                Pas de compte ? <a href="{{ route('register') }}">Créer un compte</a>
            </div>
            <nav class="lp-footer-links" aria-label="Liens utiles">
                <a href="{{ route('home') }}">Accueil</a>
                <a href="{{ route('cgu') }}">CGU</a>
                <a href="{{ route('privacy') }}">Confidentialité</a>
            </nav>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const firstAlert = document.querySelector('.lp-login-alert');
    if (firstAlert) firstAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });

    const tz = document.getElementById('timezone');
    if (tz) tz.value = Intl.DateTimeFormat().resolvedOptions().timeZone;

    document.querySelectorAll('.lp-toggle-pwd').forEach(function (btn) {
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

    const recaptchaEnabled = {{ config('recaptcha.enabled') && config('recaptcha.site_key') ? 'true' : 'false' }};
    const recaptchaSiteKey = "{{ config('recaptcha.site_key') }}";
    const loginForm        = document.getElementById('login-form');
    if (!loginForm) return;

    loginForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const form = this;

        try {
            const res = await fetch('/csrf-token', { credentials: 'same-origin' });
            if (res.ok) {
                const data = await res.json();
                const t = form.querySelector('input[name="_token"]');
                if (data.token && t) t.value = data.token;
            }
        } catch (_) {}

        if (recaptchaEnabled && window.grecaptcha) {
            try {
                const token = await new Promise(function (resolve, reject) {
                    window.grecaptcha.ready(function () {
                        window.grecaptcha.execute(recaptchaSiteKey, { action: 'login' }).then(resolve).catch(reject);
                    });
                });
                const rc = document.getElementById('recaptcha_token');
                if (rc) rc.value = token;
            } catch (err) { console.error('reCAPTCHA:', err); }
        }

        form.submit();
    });
});
</script>
@endpush

@endsection