@extends('layouts.app')
@section('title', 'Tarifs & Pricing')

@push('styles')
<style>
:root{--drv:#10b981;--drv-dark:#059669;--warn:#f59e0b;--danger:#ef4444;--blue:#3b82f6;--purple:#8b5cf6;--bg:#f8fafc;--card:#fff;--card2:#f3f4f6;--border:#e5e7eb;--t1:#1f2937;--t2:#6b7280;--t3:#9ca3af;--r:14px;--r-sm:10px}
[data-theme=dark]{--bg:#0a0a0a;--card:#141414;--card2:#1c1c1c;--border:#262626;--t1:#fafafa;--t2:#a3a3a3;--t3:#737373}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);font-family:'SF Pro Display',-apple-system,sans-serif;color:var(--t1)}
.drv-app{max-width:480px;margin:0 auto;min-height:100vh;padding-bottom:80px}

.page-head{background:linear-gradient(135deg,var(--warn),#d97706);padding:14px 16px;display:flex;align-items:center;gap:10px;position:sticky;top:0;z-index:50}
.page-back{width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-size:1rem}
.page-title{font-size:1rem;font-weight:700;color:#fff;flex:1}

/* MY AVERAGES */
.my-avg{margin:10px 12px;background:linear-gradient(135deg,var(--drv),var(--drv-dark));border-radius:var(--r);padding:16px;color:#fff;display:flex;gap:12px}
.avg-box{flex:1;text-align:center;background:rgba(255,255,255,.15);border-radius:10px;padding:10px}
.avg-val{font-size:1.2rem;font-weight:800}
.avg-lbl{font-size:.55rem;opacity:.8;margin-top:2px}

/* PRICING GRID */
.pricing-section{margin:0 12px 10px;background:var(--card);border-radius:var(--r);border:1px solid var(--border);overflow:hidden}
.pricing-header{padding:12px;font-size:.75rem;font-weight:700;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:5px}
.pricing-row{display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border-bottom:1px solid var(--border)}
.pricing-row:last-child{border:none}
.pricing-lbl{display:flex;align-items:center;gap:6px;font-size:.75rem}
.pricing-lbl .icon{font-size:1rem}
.pricing-val{font-size:.85rem;font-weight:800;color:var(--drv)}

/* SIMULATOR */
.sim-section{margin:0 12px 10px;background:var(--card);border-radius:var(--r);border:1px solid var(--border);overflow:hidden}
.sim-header{padding:12px;font-size:.75rem;font-weight:700;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:5px}
.sim-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--border)}
.sim-cell{background:var(--card);padding:14px 6px;text-align:center}
.sim-km{font-size:.65rem;color:var(--t3);font-weight:600}
.sim-earn{font-size:1rem;font-weight:800;color:var(--drv);margin-top:2px}
.sim-bar{height:4px;background:var(--card2);border-radius:99px;margin-top:6px;overflow:hidden}
.sim-fill{height:100%;background:linear-gradient(90deg,var(--drv),#06b6d4);border-radius:99px;transition:width .5s}

/* CUSTOM SIMULATOR */
.custom-sim{padding:14px 12px}
.cs-title{font-size:.7rem;font-weight:700;margin-bottom:8px;display:flex;align-items:center;gap:4px}
.cs-input-row{display:flex;gap:8px;margin-bottom:10px}
.cs-input{flex:1;height:40px;border:1px solid var(--border);border-radius:10px;padding:0 12px;font-size:.8rem;background:var(--card2);color:var(--t1);outline:none}
.cs-input:focus{border-color:var(--drv)}
.cs-btn{padding:8px 16px;background:var(--drv);color:#fff;border:none;border-radius:10px;font-weight:700;font-size:.8rem;cursor:pointer}
.cs-btn:active{transform:scale(.97)}
.cs-result{background:linear-gradient(135deg,var(--drv),var(--drv-dark));color:#fff;border-radius:10px;padding:14px;text-align:center;display:none}
.cs-result.visible{display:block}
.cs-result-val{font-size:1.5rem;font-weight:800}
.cs-result-detail{font-size:.65rem;opacity:.8;margin-top:4px}

/* SURGE INFO */
.surge-section{margin:0 12px 10px;background:var(--card);border-radius:var(--r);border:1px solid var(--border);overflow:hidden}
.surge-header{padding:12px;font-size:.75rem;font-weight:700;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:5px}
.surge-card{padding:12px}
.surge-item{display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border)}
.surge-item:last-child{border:none}
.surge-icon{font-size:1.3rem;width:36px;text-align:center}
.surge-info{flex:1}
.surge-name{font-size:.75rem;font-weight:700}
.surge-desc{font-size:.6rem;color:var(--t3);margin-top:1px}
.surge-multi{font-size:.8rem;font-weight:800;color:var(--warn);background:rgba(245,158,11,.1);padding:4px 10px;border-radius:8px}

/* BATCH INFO */
.batch-card{padding:12px}
.batch-item{display:flex;align-items:center;gap:8px;font-size:.7rem;padding:6px 0;color:var(--t2)}
.batch-item .icon{font-size:1rem;width:24px;text-align:center}

/* BOTTOM NAV */
.bot-nav{position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:480px;background:var(--card);border-top:1px solid var(--border);display:flex;padding:6px 0 calc(6px + env(safe-area-inset-bottom,0px));z-index:100}
.nav-item{flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;padding:4px 0;font-size:.55rem;color:var(--t3);text-decoration:none;transition:.2s}
.nav-item.active{color:var(--drv)}
.nav-item .ico{font-size:1.15rem}
</style>
@endpush

@section('content')
<div class="drv-app">
    <header class="page-head">
        <a href="{{ route('driver.dashboard') }}" class="page-back">←</a>
        <div class="page-title">💰 Tarifs & Pricing</div>
    </header>

    <!-- MY AVERAGES -->
    <div class="my-avg">
        <div class="avg-box">
            <div class="avg-val">{{ number_format($myAvgEarning, 2) }}€</div>
            <div class="avg-lbl">Gain moyen/livraison</div>
        </div>
        <div class="avg-box">
            <div class="avg-val">{{ number_format($myAvgDistance, 1) }} km</div>
            <div class="avg-lbl">Distance moyenne</div>
        </div>
        <div class="avg-box">
            @php $ratio = $myAvgDistance > 0 ? $myAvgEarning / $myAvgDistance : 0; @endphp
            <div class="avg-val">{{ number_format($ratio, 2) }}€</div>
            <div class="avg-lbl">Par km</div>
        </div>
    </div>

    <!-- PRICING GRID -->
    <div class="pricing-section">
        <div class="pricing-header">📋 Grille tarifaire</div>
        <div class="pricing-row">
            <div class="pricing-lbl"><span class="icon">🏁</span> Base fixe</div>
            <div class="pricing-val">{{ number_format($tarifs['base_fee'], 2) }}€</div>
        </div>
        <div class="pricing-row">
            <div class="pricing-lbl"><span class="icon">📍</span> Récupération/km</div>
            <div class="pricing-val">{{ number_format($tarifs['pickup_per_km'], 2) }}€</div>
        </div>
        <div class="pricing-row">
            <div class="pricing-lbl"><span class="icon">🏠</span> Livraison/km</div>
            <div class="pricing-val">{{ number_format($tarifs['dropoff_per_km'], 2) }}€</div>
        </div>
        <div class="pricing-row">
            <div class="pricing-lbl"><span class="icon">⬇️</span> Gain minimum</div>
            <div class="pricing-val">{{ number_format($tarifs['min_earning'], 2) }}€</div>
        </div>
        <div class="pricing-row">
            <div class="pricing-lbl"><span class="icon">⬆️</span> Gain maximum</div>
            <div class="pricing-val">{{ number_format($tarifs['max_earning'], 2) }}€</div>
        </div>
        <div class="pricing-row">
            <div class="pricing-lbl"><span class="icon">🏛️</span> Commission plateforme</div>
            <div class="pricing-val">{{ ($tarifs['platform_fee'] * 100) }}%</div>
        </div>
    </div>

    <!-- EARNING SIMULATOR (Quick) -->
    <div class="sim-section">
        <div class="sim-header">🔢 Simulateur de gains</div>
        <div class="sim-grid">
            @foreach($simulations as $sim)
            <div class="sim-cell">
                <div class="sim-km">{{ $sim['km'] }} km</div>
                <div class="sim-earn">{{ number_format($sim['earning'], 2) }}€</div>
                <div class="sim-bar">
                    <div class="sim-fill" style="width:{{ min(100, ($sim['earning'] / $tarifs['max_earning']) * 100) }}%"></div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- CUSTOM SIMULATOR -->
        <div class="custom-sim">
            <div class="cs-title">🧮 Calcul personnalisé</div>
            <div class="cs-input-row">
                <input type="number" class="cs-input" id="simPickupKm" placeholder="Km récup" min="0" step="0.5" value="1">
                <input type="number" class="cs-input" id="simDropoffKm" placeholder="Km livraison" min="0" step="0.5" value="3">
                <button class="cs-btn" onclick="calculateSim()">Calculer</button>
            </div>
            <div class="cs-result" id="simResult">
                <div class="cs-result-val" id="simResultVal">0.00€</div>
                <div class="cs-result-detail" id="simResultDetail"></div>
            </div>
        </div>
    </div>

    <!-- SURGE PRICING -->
    <div class="surge-section">
        <div class="surge-header">⚡ Surge Pricing</div>
        <div class="surge-card">
            @php $surge = $tarifs['surge'] ?? []; @endphp
            @if(!empty($surge) && ($surge['enabled'] ?? false))
            <div class="surge-item">
                <div class="surge-icon">🕐</div>
                <div class="surge-info">
                    <div class="surge-name">Heures de pointe</div>
                    <div class="surge-desc">
                        @if(isset($surge['peak_hours']) && is_array($surge['peak_hours']))
                            @foreach($surge['peak_hours'] as $ph)
                                {{ $ph['start'] ?? '?' }}h-{{ $ph['end'] ?? '?' }}h (x{{ $ph['multiplier'] ?? '1.0' }}){{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        @else
                            11h-14h, 18h-21h
                        @endif
                    </div>
                </div>
                <div class="surge-multi">
                    x{{ collect($surge['peak_hours'] ?? [])->max('multiplier') ?? '1.3' }}
                </div>
            </div>
            <div class="surge-item">
                <div class="surge-icon">🌧️</div>
                <div class="surge-info">
                    <div class="surge-name">Intempéries</div>
                    <div class="surge-desc">
                        Pluie x{{ $surge['weather']['rain'] ?? '1.15' }},
                        Forte pluie x{{ $surge['weather']['heavy_rain'] ?? '1.30' }},
                        Neige x{{ $surge['weather']['snow'] ?? '1.50' }}
                    </div>
                </div>
                <div class="surge-multi">x{{ max($surge['weather'] ?? [1.4]) }}</div>
            </div>
            <div class="surge-item">
                <div class="surge-icon">📈</div>
                <div class="surge-info">
                    <div class="surge-name">Forte demande</div>
                    <div class="surge-desc">Quand les commandes dépassent les livreurs</div>
                </div>
                <div class="surge-multi">x{{ max($surge['demand'] ?? [1.5]) }}</div>
            </div>
            @else
            <div style="padding:12px;text-align:center;font-size:.75rem;color:var(--t3)">Pas de surge configuré</div>
            @endif
        </div>
    </div>

    <!-- BATCH INFO -->
    <div class="pricing-section">
        <div class="pricing-header">📦 Système Batch</div>
        <div class="batch-card">
            @php $batch = $tarifs['batch'] ?? []; @endphp
            <div class="batch-item">
                <span class="icon">📦</span>
                Max {{ $batch['max_orders'] ?? 3 }} commandes simultanées
            </div>
            <div class="batch-item">
                <span class="icon">📏</span>
                Rayon max {{ $batch['max_client_distance'] ?? 2 }} km entre clients
            </div>
            <div class="batch-item">
                <span class="icon">🏪</span>
                Rayon max {{ $batch['max_pickup_distance'] ?? 1.5 }} km entre restaurants
            </div>
            <div class="batch-item">
                <span class="icon">💰</span>
                Bonus: +{{ number_format($batch['bonus_per_extra_order'] ?? 1.50, 2) }}€ par commande supp.
            </div>
            <div class="batch-item">
                <span class="icon">⏱️</span>
                Fenêtre: {{ $batch['time_window'] ?? 10 }} min pour grouper
            </div>
            <div class="batch-item">
                <span class="icon">💡</span>
                Acceptez plusieurs commandes proches pour maximiser vos gains !
            </div>
        </div>
    </div>

    <!-- FORMULA EXPLANATION -->
    <div class="pricing-section">
        <div class="pricing-header">📐 Formule de calcul</div>
        <div style="padding:12px">
            <div style="background:var(--card2);border-radius:10px;padding:14px;font-size:.7rem;color:var(--t2);line-height:1.8;font-family:monospace">
                <strong style="color:var(--t1)">Gain =</strong><br>
                &nbsp;&nbsp;Base ({{ number_format($tarifs['base_fee'], 2) }}€)<br>
                &nbsp;&nbsp;+ Récup × {{ number_format($tarifs['pickup_per_km'], 2) }}€/km<br>
                &nbsp;&nbsp;+ Livraison × {{ number_format($tarifs['dropoff_per_km'], 2) }}€/km<br>
                &nbsp;&nbsp;× Surge (si actif)<br>
                <br>
                <span style="color:var(--drv)">Min: {{ number_format($tarifs['min_earning'], 2) }}€ — Max: {{ number_format($tarifs['max_earning'], 2) }}€</span>
            </div>
        </div>
    </div>
</div>

<!-- BOTTOM NAV -->
<nav class="bot-nav">
    <a href="{{ route('driver.dashboard') }}" class="nav-item"><span class="ico">🏠</span>Dashboard</a>
    <a href="{{ route('driver.map') }}" class="nav-item"><span class="ico">🗺️</span>Carte</a>
    <a href="{{ route('driver.deliveries') }}" class="nav-item"><span class="ico">📦</span>Historique</a>
    <a href="{{ route('driver.tarifs') }}" class="nav-item active"><span class="ico">💰</span>Tarifs</a>
    <a href="{{ route('driver.stats') }}" class="nav-item"><span class="ico">📊</span>Stats</a>
</nav>

<script>
const baseFee={{ $tarifs['base_fee'] }},pickupKm={{ $tarifs['pickup_per_km'] }},dropoffKm={{ $tarifs['dropoff_per_km'] }},minE={{ $tarifs['min_earning'] }},maxE={{ $tarifs['max_earning'] }};
function calculateSim(){
    const p=parseFloat(document.getElementById('simPickupKm').value)||0;
    const d=parseFloat(document.getElementById('simDropoffKm').value)||0;
    let earn=baseFee+(pickupKm*Math.min(p,2))+(dropoffKm*d);
    earn=Math.max(minE,Math.min(maxE,earn));
    const el=document.getElementById('simResult');
    el.classList.add('visible');
    document.getElementById('simResultVal').textContent=earn.toFixed(2)+'€';
    document.getElementById('simResultDetail').textContent=`Base ${baseFee}€ + récup ${(pickupKm*Math.min(p,2)).toFixed(2)}€ + livraison ${(dropoffKm*d).toFixed(2)}€`;
}
</script>
@endsection
