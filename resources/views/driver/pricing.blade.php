@extends('layouts.app')

@section('title', 'Mes tarifs - Livreur')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/driver-mobile.css') }}?v={{ time() }}" />
<style>
:root {
    --primary: #10b981;
    --primary-dark: #059669;
    --warning: #f59e0b;
    --bg-base: #f8fafc;
    --bg-card: #ffffff;
    --bg-card-alt: #f3f4f6;
    --border-color: #e5e7eb;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --text-muted: #9ca3af;
    --safe-bottom: env(safe-area-inset-bottom, 0px);
}
[data-theme="dark"] {
    --bg-base: #0c1220;
    --bg-card: #141d2e;
    --bg-card-alt: #1c2a42;
    --border-color: #243552;
    --text-primary: #ffffff;
    --text-secondary: #94a3b8;
    --text-muted: #64748b;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--bg-base); font-family: system-ui, -apple-system, sans-serif; color: var(--text-primary); transition: background 0.3s, color 0.3s; }

/* Header */
.hdr {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.hdr-back { color: #fff; text-decoration: none; font-size: 1.1rem; }
.hdr h1 { font-size: 0.95rem; font-weight: 600; }

/* Form */
.form-wrap {
    padding: 10px;
    padding-bottom: calc(70px + var(--safe-bottom));
}

/* Card */
.card {
    background: var(--bg-card);
    border-radius: 12px;
    padding: 14px;
    margin-bottom: 10px;
    border: 1px solid var(--border-color);
}
.card-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 12px;
    color: var(--text-primary);
}
.card-title .ico { font-size: 1rem; }

/* Form Grid */
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}
.form-group { display: flex; flex-direction: column; gap: 4px; }
.form-group.full { grid-column: span 2; }
.form-label {
    font-size: 0.65rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.form-input {
    background: var(--bg-card-alt);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 0.85rem;
    color: var(--text-primary);
    outline: none;
}
.form-input:focus { border-color: var(--primary); }

/* Toggle */
.toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--bg-card-alt);
    padding: 10px 12px;
    border-radius: 8px;
}
.toggle-label { font-size: 0.75rem; color: var(--text-secondary); }
.toggle {
    position: relative;
    width: 44px;
    height: 24px;
}
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
    position: absolute;
    inset: 0;
    background: var(--border-color);
    border-radius: 24px;
    cursor: pointer;
    transition: 0.2s;
}
.toggle-slider::before {
    content: '';
    position: absolute;
    width: 18px; height: 18px;
    left: 3px; bottom: 3px;
    background: #fff;
    border-radius: 50%;
    transition: 0.2s;
}
.toggle input:checked + .toggle-slider { background: var(--primary); }
.toggle input:checked + .toggle-slider::before { transform: translateX(20px); }

/* Preview */
.preview-card {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: 12px;
    padding: 14px;
    margin-bottom: 10px;
}
.preview-title {
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.preview-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
}
.preview-item {
    background: rgba(255,255,255,0.15);
    padding: 8px;
    border-radius: 8px;
    text-align: center;
}
.preview-item .val { font-size: 0.9rem; font-weight: 700; }
.preview-item .lbl { font-size: 0.55rem; opacity: 0.8; margin-top: 2px; }

/* Alert */
.alert {
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 10px;
    font-size: 0.75rem;
}
.alert-success { background: rgba(16,185,129,0.2); color: #34d399; }
.alert-error { background: rgba(239,68,68,0.2); color: #f87171; }

/* Submit */
.btn-submit {
    width: 100%;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: #fff;
    border: none;
    padding: 14px;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    margin-top: 6px;
}
.btn-submit:active { transform: scale(0.98); }

/* Bottom Nav */
.bottom-nav {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    display: flex;
    background: var(--bg-card);
    border-top: 1px solid var(--border-color);
    padding: 6px 4px;
    padding-bottom: calc(6px + var(--safe-bottom));
    z-index: 50;
}
.nav-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    padding: 4px 2px;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.55rem;
}
.nav-item.active { color: var(--primary); }
.nav-ico { font-size: 1.1rem; }
</style>
@endpush

@section('content')
<!-- Theme Toggle -->
<button class="theme-toggle" onclick="toggleTheme()" id="themeBtn">🌙</button>

<!-- Header -->
<header class="hdr">
    <a href="{{ route('driver.dashboard') }}" class="hdr-back">←</a>
    <h1>💰 Mes tarifs</h1>
</header>

<div class="form-wrap">
    @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    <form action="{{ route('driver.pricing.update') }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Tarifs de base -->
        <div class="card">
            <div class="card-title"><span class="ico">💵</span> Tarification de base</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Frais de base (€)</label>
                    <input type="number" name="base_fee" class="form-input" step="0.5" min="0" 
                           value="{{ old('base_fee', $driver->base_fee ?? 2.50) }}" oninput="updatePreview()">
                </div>
                <div class="form-group">
                    <label class="form-label">Par km (€)</label>
                    <input type="number" name="fee_per_km" class="form-input" step="0.1" min="0" 
                           value="{{ old('fee_per_km', $driver->fee_per_km ?? 1.00) }}" oninput="updatePreview()">
                </div>
                <div class="form-group">
                    <label class="form-label">Minimum (€)</label>
                    <input type="number" name="minimum_order" class="form-input" step="0.5" min="0" 
                           value="{{ old('minimum_order', $driver->minimum_order ?? 5) }}">
                </div>
                <div class="form-group">
                    <div class="toggle-row">
                        <span class="toggle-label">Pourboires</span>
                        <label class="toggle">
                            <input type="checkbox" name="accepts_tips" value="1" {{ ($driver->accepts_tips ?? true) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aperçu -->
        <div class="preview-card">
            <div class="preview-title">📊 Aperçu des gains</div>
            <div class="preview-grid">
                <div class="preview-item">
                    <div class="val" id="preview1km">--</div>
                    <div class="lbl">1 km</div>
                </div>
                <div class="preview-item">
                    <div class="val" id="preview3km">--</div>
                    <div class="lbl">3 km</div>
                </div>
                <div class="preview-item">
                    <div class="val" id="preview5km">--</div>
                    <div class="lbl">5 km</div>
                </div>
                <div class="preview-item">
                    <div class="val" id="preview10km">--</div>
                    <div class="lbl">10 km</div>
                </div>
            </div>
        </div>

        <!-- Heures de pointe -->
        <div class="card">
            <div class="card-title"><span class="ico">⚡</span> Heures de pointe</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Multiplicateur</label>
                    <input type="number" name="peak_multiplier" class="form-input" step="0.1" min="1" max="3" 
                           value="{{ old('peak_multiplier', $driver->peak_multiplier ?? 1.5) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Créneaux</label>
                    <select name="peak_hours" class="form-input">
                        <option value="lunch" {{ ($driver->peak_hours ?? 'both') == 'lunch' ? 'selected' : '' }}>Midi (11h-14h)</option>
                        <option value="dinner" {{ ($driver->peak_hours ?? 'both') == 'dinner' ? 'selected' : '' }}>Soir (18h-22h)</option>
                        <option value="both" {{ ($driver->peak_hours ?? 'both') == 'both' ? 'selected' : '' }}>Les deux</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Paiement -->
        <div class="card">
            <div class="card-title"><span class="ico">🏦</span> Paiement</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Mode</label>
                    <select name="payment_method" class="form-input">
                        <option value="bank" {{ ($driver->payment_method ?? 'bank') == 'bank' ? 'selected' : '' }}>Virement</option>
                        <option value="cash" {{ ($driver->payment_method ?? 'bank') == 'cash' ? 'selected' : '' }}>Espèces</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fréquence</label>
                    <select name="payment_frequency" class="form-input">
                        <option value="daily" {{ ($driver->payment_frequency ?? 'weekly') == 'daily' ? 'selected' : '' }}>Journalier</option>
                        <option value="weekly" {{ ($driver->payment_frequency ?? 'weekly') == 'weekly' ? 'selected' : '' }}>Hebdo</option>
                        <option value="monthly" {{ ($driver->payment_frequency ?? 'weekly') == 'monthly' ? 'selected' : '' }}>Mensuel</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Activation -->
        <div class="card">
            <div class="toggle-row">
                <span class="toggle-label">Activer mes tarifs personnalisés</span>
                <label class="toggle">
                    <input type="checkbox" name="custom_pricing_enabled" value="1" {{ ($driver->custom_pricing_enabled ?? false) ? 'checked' : '' }}>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>

        <button type="submit" class="btn-submit">💾 Enregistrer</button>
    </form>
</div>

<!-- Bottom Nav -->
<nav class="bottom-nav">
    <a href="{{ route('driver.dashboard') }}" class="nav-item"><span class="nav-ico">🏠</span>Dashboard</a>
    <a href="{{ route('driver.map') }}" class="nav-item"><span class="nav-ico">🗺️</span>Carte</a>
    <a href="{{ route('driver.deliveries') }}" class="nav-item"><span class="nav-ico">📦</span>Historique</a>
    <a href="{{ route('driver.pricing') }}" class="nav-item active"><span class="nav-ico">💰</span>Tarifs</a>
    <a href="{{ route('driver.stats') }}" class="nav-item"><span class="nav-ico">📊</span>Stats</a>
</nav>

<script>
// Theme management
function initTheme() {
    const savedTheme = localStorage.getItem('driver-theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeBtn(savedTheme);
}
function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme');
    const newTheme = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('driver-theme', newTheme);
    updateThemeBtn(newTheme);
}
function updateThemeBtn(theme) {
    const btn = document.getElementById('themeBtn');
    if (btn) btn.textContent = theme === 'dark' ? '☀️' : '🌙';
}
initTheme();

function updatePreview() {
    const base = parseFloat(document.querySelector('input[name="base_fee"]').value) || 0;
    const km = parseFloat(document.querySelector('input[name="fee_per_km"]').value) || 0;
    document.getElementById('preview1km').textContent = (base + 1 * km).toFixed(1) + '€';
    document.getElementById('preview3km').textContent = (base + 3 * km).toFixed(1) + '€';
    document.getElementById('preview5km').textContent = (base + 5 * km).toFixed(1) + '€';
    document.getElementById('preview10km').textContent = (base + 10 * km).toFixed(1) + '€';
}
updatePreview();
</script>
@endsection
