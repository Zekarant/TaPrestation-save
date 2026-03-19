@extends('layouts.app')
@section('title', 'Dashboard Livreur')

@push('styles')
<style>
:root{--drv:#10b981;--drv-dark:#059669;--drv-glow:rgba(16,185,129,.25);--warn:#f59e0b;--danger:#ef4444;--blue:#3b82f6;--purple:#8b5cf6;--bg:#f8fafc;--card:#fff;--card2:#f3f4f6;--border:#e5e7eb;--t1:#1f2937;--t2:#6b7280;--t3:#9ca3af;--shadow:0 2px 8px rgba(0,0,0,.05);--r:14px;--r-sm:10px}
[data-theme=dark]{--bg:#0a0a0a;--card:#141414;--card2:#1c1c1c;--border:#262626;--t1:#fafafa;--t2:#a3a3a3;--t3:#737373;--shadow:0 2px 12px rgba(0,0,0,.4)}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);font-family:'SF Pro Display',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:var(--t1);-webkit-font-smoothing:antialiased}
.drv-app{max-width:480px;margin:0 auto;min-height:100vh;padding-bottom:80px}

/* HEADER */
.drv-head{background:linear-gradient(135deg,var(--drv),var(--drv-dark));padding:14px 16px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50}
.drv-head .left{display:flex;align-items:center;gap:10px}
.drv-ava{width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.2rem}
.drv-name{font-size:.95rem;font-weight:700;color:#fff}
.drv-sub{font-size:.65rem;color:rgba(255,255,255,.8);display:flex;align-items:center;gap:4px}
.badge-trust{padding:2px 6px;border-radius:6px;font-size:.55rem;font-weight:700}
.badge-trust.probation{background:rgba(245,158,11,.3);color:#fbbf24}
.badge-trust.verified{background:rgba(16,185,129,.3);color:#34d399}
.badge-trust.trusted{background:rgba(139,92,246,.3);color:#a78bfa}
.badge-trust.sponsored{background:rgba(59,130,246,.3);color:#93c5fd}
.status-pill{display:flex;align-items:center;gap:6px;background:rgba(0,0,0,.25);padding:6px 12px;border-radius:99px;cursor:pointer;transition:.2s;color:#fff;font-size:.75rem;font-weight:600;border:none}
.status-pill:active{transform:scale(.96)}
.status-dot{width:8px;height:8px;border-radius:50%;animation:pulse 2s infinite}
.status-dot.available{background:#22c55e;box-shadow:0 0 8px #22c55e}
.status-dot.busy{background:var(--warn);box-shadow:0 0 8px var(--warn)}
.status-dot.offline{background:#6b7280}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.7;transform:scale(.85)}}

/* SURGE BANNER */
.surge-banner{background:linear-gradient(90deg,#f59e0b,#f97316);padding:8px 16px;display:flex;align-items:center;gap:8px;font-size:.75rem;font-weight:600;color:#fff}
.surge-banner .icon{font-size:1.1rem;animation:pulse 1.5s infinite}

/* STATS ROW */
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;padding:8px 12px}
.stat-box{background:var(--card);border-radius:var(--r-sm);padding:10px 6px;text-align:center;border:1px solid var(--border);position:relative;overflow:hidden}
.stat-box::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:var(--drv)}
.stat-box.warn::before{background:var(--warn)}
.stat-box.blue::before{background:var(--blue)}
.stat-box.purple::before{background:var(--purple)}
.stat-icon{font-size:.85rem;margin-bottom:2px}
.stat-val{font-size:1rem;font-weight:800;letter-spacing:-.5px}
.stat-lbl{font-size:.5rem;color:var(--t3);text-transform:uppercase;letter-spacing:.3px;margin-top:1px}

/* STREAK + PROBATION */
.streak-bar{margin:0 12px 8px;padding:10px 14px;background:linear-gradient(135deg,var(--purple),#7c3aed);border-radius:var(--r-sm);color:#fff;display:flex;align-items:center;justify-content:space-between;font-size:.8rem}
.streak-bar .fire{font-size:1.3rem}
.probation-bar{margin:0 12px 8px;padding:10px;background:var(--card);border:1px solid var(--border);border-radius:var(--r-sm)}
.probation-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;font-size:.75rem;font-weight:600}
.probation-progress{height:6px;background:var(--card2);border-radius:99px;overflow:hidden}
.probation-fill{height:100%;background:linear-gradient(90deg,var(--drv),var(--drv-dark));border-radius:99px;transition:width .5s}
.probation-limits{display:flex;gap:8px;margin-top:6px;font-size:.6rem;color:var(--t3)}
.probation-limits span{display:flex;align-items:center;gap:2px}

/* SECTIONS */
.section{padding:0 12px;margin-bottom:10px}
.section-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px}
.section-title{display:flex;align-items:center;gap:5px;font-weight:700;font-size:.85rem}
.section-badge{background:var(--drv);color:#fff;padding:3px 8px;border-radius:99px;font-size:.6rem;font-weight:700}

/* ORDER CARD */
.ord-card{background:var(--card);border-radius:var(--r-sm);overflow:hidden;margin-bottom:8px;border:1px solid var(--border);transition:.2s}
.ord-card.active{border-left:3px solid var(--drv)}
.ord-top{display:flex;align-items:center;gap:8px;padding:10px;border-bottom:1px solid var(--border)}
.ord-icon{width:36px;height:36px;border-radius:10px;background:var(--card2);display:flex;align-items:center;justify-content:center;font-size:1.2rem}
.ord-info{flex:1}
.ord-name{font-weight:700;font-size:.8rem}
.ord-status{font-size:.65rem;margin-top:2px}
.ord-status.assigned{color:var(--blue)}
.ord-status.picked_up{color:var(--drv)}
.ord-status.in_transit{color:var(--purple)}
.ord-earn{text-align:right}
.ord-earn .amount{font-weight:800;font-size:.9rem;color:var(--drv)}
.ord-earn .id{font-size:.6rem;color:var(--t3)}
.ord-body{padding:10px}
.route-vis{display:flex;gap:10px}
.route-dots{display:flex;flex-direction:column;align-items:center;gap:0;padding-top:4px}
.route-dot{width:10px;height:10px;border-radius:50%;border:2px solid}
.route-dot.pickup{border-color:var(--blue);background:rgba(59,130,246,.2)}
.route-dot.drop{border-color:var(--drv);background:rgba(16,185,129,.2)}
.route-line{width:2px;height:20px;background:var(--border);margin:2px 0}
.route-addrs{flex:1;display:flex;flex-direction:column;gap:8px}
.route-addr{font-size:.7rem}
.route-addr .lbl{font-size:.55rem;color:var(--t3);text-transform:uppercase;letter-spacing:.3px}
.ord-meta{display:flex;gap:8px;margin-top:8px;flex-wrap:wrap}
.meta-chip{display:flex;align-items:center;gap:3px;font-size:.65rem;color:var(--t2);background:var(--card2);padding:3px 8px;border-radius:6px}
.ord-actions{display:flex;gap:6px;padding:8px 10px;border-top:1px solid var(--border)}
.btn-act{flex:1;padding:8px;border:none;border-radius:8px;font-weight:700;font-size:.75rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:4px;transition:.15s}
.btn-act:active{transform:scale(.97)}
.btn-gps{background:var(--blue);color:#fff}
.btn-pickup{background:var(--drv);color:#fff}
.btn-transit{background:var(--purple);color:#fff}
.btn-deliver{background:linear-gradient(135deg,var(--drv),#06b6d4);color:#fff}
.btn-accept{background:var(--drv);color:#fff}
.btn-details{background:var(--card2);color:var(--t1);border:1px solid var(--border)}

/* EMPTY */
.empty-state{text-align:center;padding:30px 20px}
.empty-icon{font-size:2.5rem;margin-bottom:8px}
.empty-text{font-size:.8rem;color:var(--t3);line-height:1.5}

/* BOTTOM NAV */
.bot-nav{position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:480px;background:var(--card);border-top:1px solid var(--border);display:flex;padding:6px 0 calc(6px + env(safe-area-inset-bottom,0px));z-index:100}
.nav-item{flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;padding:4px 0;font-size:.55rem;color:var(--t3);text-decoration:none;transition:.2s}
.nav-item.active{color:var(--drv)}
.nav-item .ico{font-size:1.15rem}

/* LOADING */
.loading-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);display:none;align-items:center;justify-content:center;z-index:200}
.loading-overlay.active{display:flex}
.spinner{width:40px;height:40px;border:3px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}

/* THEME BTN */
.theme-btn{position:fixed;top:60px;right:8px;width:32px;height:32px;border-radius:8px;background:var(--card);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.9rem;z-index:60}
</style>
@endpush

@section('content')
<div class="loading-overlay" id="loading"><div class="spinner"></div></div>
<button class="theme-btn" id="themeBtn" onclick="toggleTheme()">🌙</button>

<div class="drv-app">
    <!-- HEADER -->
    <header class="drv-head">
        <div class="left">
            <div class="drv-ava">{{ $driver->vehicle_icon ?? '🚗' }}</div>
            <div>
                <div class="drv-name">{{ $driver->full_name }}</div>
                <div class="drv-sub">
                    Livreur
                    @if($driver->sponsor_prestataire_id)
                        <span class="badge-trust sponsored">🤝 Parrainé</span>
                    @elseif(($driver->trust_level ?? 'probation') === 'probation')
                        <span class="badge-trust probation">⏳ Essai</span>
                    @elseif(($driver->trust_level ?? '') === 'verified')
                        <span class="badge-trust verified">✓ Vérifié</span>
                    @elseif(($driver->trust_level ?? '') === 'trusted')
                        <span class="badge-trust trusted">⭐ Fiable</span>
                    @endif
                </div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:6px">
            <button class="status-pill" onclick="toggleStatus()">
                <span class="status-dot {{ $driver->status }}"></span>
                <span id="statusText">
                    @if($driver->status === 'available') En ligne
                    @elseif($driver->status === 'busy') Occupé
                    @else Hors ligne @endif
                </span>
            </button>
            @if(!auth()->check() && session('internal_driver_id'))
                <form method="POST" action="{{ route('driver.internal.logout') }}">
                    @csrf
                    <button type="submit" class="status-pill" style="background:rgba(0,0,0,.35)">
                        Quitter
                    </button>
                </form>
            @endif
        </div>
    </header>

    <!-- SURGE BANNER -->
    @if(($surgeMultiplier ?? 1) > 1)
    <div class="surge-banner">
        <span class="icon">⚡</span>
        <span>Surge actif x{{ number_format($surgeMultiplier, 1) }} — Gains majorés !</span>
    </div>
    @endif

    <!-- STATS -->
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-icon">✅</div>
            <div class="stat-val">{{ $todayStats['completed'] }}</div>
            <div class="stat-lbl">Livrées</div>
        </div>
        <div class="stat-box warn">
            <div class="stat-icon">⏳</div>
            <div class="stat-val">{{ $todayStats['pending'] }}</div>
            <div class="stat-lbl">En cours</div>
        </div>
        <div class="stat-box blue">
            <div class="stat-icon">💰</div>
            <div class="stat-val">{{ number_format($todayStats['earnings'], 2) }}€</div>
            <div class="stat-lbl">Gains</div>
        </div>
        <div class="stat-box purple">
            <div class="stat-icon">📏</div>
            <div class="stat-val">{{ number_format($todayStats['total_km'], 1) }}</div>
            <div class="stat-lbl">Km</div>
        </div>
    </div>

    <!-- STREAK -->
    @if(($streak ?? 0) > 1)
    <div class="streak-bar">
        <span>🔥 {{ $streak }} jours consécutifs !</span>
        <span class="fire">🔥</span>
    </div>
    @endif

    <!-- PROBATION -->
    @if(($driver->trust_level ?? 'probation') === 'probation' && !$driver->sponsor_prestataire_id)
    <div class="probation-bar">
        <div class="probation-header">
            <span>🎯 Période d'essai</span>
            <span>{{ $driver->probation_deliveries_count ?? 0 }}/10</span>
        </div>
        <div class="probation-progress">
            <div class="probation-fill" style="width:{{ min(100, (($driver->probation_deliveries_count ?? 0) / 10) * 100) }}%"></div>
        </div>
        <div class="probation-limits">
            <span>📦 Max {{ $driver->daily_limit ?? 3 }}/jour</span>
            <span>💶 Max {{ number_format($driver->max_order_amount ?? 50, 0) }}€/cmd</span>
            <span>⭐ Note min 3/5</span>
        </div>
    </div>
    @endif

    <!-- ACTIVE ORDERS -->
    @if($activeOrders->count() > 0)
    <div class="section">
        <div class="section-head">
            <div class="section-title"><span>🚀</span> Livraisons en cours</div>
            <span class="section-badge">{{ $activeOrders->count() }}</span>
        </div>
        @foreach($activeOrders as $order)
        <div class="ord-card active">
            <div class="ord-top">
                <div class="ord-icon">🍽️</div>
                <div class="ord-info">
                    <div class="ord-name">{{ $order->prestataire?->company_name ?? 'Restaurant' }}</div>
                    <div class="ord-status {{ $order->delivery_status }}">
                        @if($order->delivery_status === 'assigned') 📍 À récupérer
                        @elseif($order->delivery_status === 'picked_up') ✅ Récupérée
                        @elseif($order->delivery_status === 'in_transit') 🚗 En route
                        @endif
                    </div>
                </div>
                <div class="ord-earn">
                    <div class="amount">+{{ number_format($order->driver_commission ?? 2, 2) }}€</div>
                    <div class="id">#{{ $order->id }}</div>
                </div>
            </div>
            <div class="ord-body">
                <div class="route-vis">
                    <div class="route-dots">
                        <div class="route-dot pickup"></div>
                        <div class="route-line"></div>
                        <div class="route-dot drop"></div>
                    </div>
                    <div class="route-addrs">
                        <div class="route-addr">
                            <div class="lbl">Récupération</div>
                            {{ Str::limit($order->prestataire?->address ?? 'Restaurant', 40) }}
                        </div>
                        <div class="route-addr">
                            <div class="lbl">Livraison</div>
                            {{ Str::limit($order->delivery_address ?? 'Non renseignée', 40) }}
                        </div>
                    </div>
                </div>
                <div class="ord-meta">
                    <span class="meta-chip">🛍️ {{ $order->items?->count() ?? 0 }} art.</span>
                    <span class="meta-chip">⏱️ ~{{ $order->estimated_delivery_time ?? 15 }} min</span>
                    <span class="meta-chip">📏 {{ $order->delivery_distance ?? '?' }} km</span>
                </div>
            </div>
            <div class="ord-actions">
                @if($order->delivery_status === 'assigned')
                    <a href="{{ route('driver.navigate', $order) }}" class="btn-act btn-gps">🧭 GPS</a>
                    <button class="btn-act btn-pickup" onclick="pickupOrder({{ $order->id }})">✅ Récupérée</button>
                @elseif($order->delivery_status === 'picked_up')
                    <a href="{{ route('driver.navigate', $order) }}" class="btn-act btn-gps">🧭 GPS</a>
                    <button class="btn-act btn-transit" onclick="startDelivery({{ $order->id }})">🚗 En route</button>
                @elseif($order->delivery_status === 'in_transit')
                    <a href="{{ route('driver.navigate', $order) }}" class="btn-act btn-gps">🧭 GPS</a>
                    <button class="btn-act btn-deliver" onclick="deliverOrder({{ $order->id }})">
                        🎉 Code client
                    </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- PENDING ORDERS -->
    @if($driver->status === 'available')
    <div class="section">
        <div class="section-head">
            <div class="section-title"><span>📋</span> Commandes disponibles</div>
            <span class="section-badge">{{ $pendingOrders->count() }}</span>
        </div>
        @forelse($pendingOrders as $order)
        <div class="ord-card" id="pending-{{ $order->id }}">
            <div class="ord-top">
                <div class="ord-icon">🍽️</div>
                <div class="ord-info">
                    <div class="ord-name">{{ $order->prestataire?->company_name ?? 'Restaurant' }}</div>
                    <div class="ord-status">
                        @if($order->status === 'ready') ✅ Prête
                        @elseif($order->status === 'preparing') 🍳 En préparation
                        @else ⏳ Acceptée @endif
                    </div>
                </div>
                <div class="ord-earn">
                    <div class="amount">{{ number_format($order->total ?? 0, 2) }}€</div>
                    <div class="id">#{{ $order->id }}</div>
                </div>
            </div>
            <div class="ord-body">
                <div class="route-vis">
                    <div class="route-dots">
                        <div class="route-dot pickup"></div>
                        <div class="route-line"></div>
                        <div class="route-dot drop"></div>
                    </div>
                    <div class="route-addrs">
                        <div class="route-addr">
                            <div class="lbl">Récupération</div>
                            {{ Str::limit($order->prestataire?->address ?? 'Restaurant', 40) }}
                        </div>
                        <div class="route-addr">
                            <div class="lbl">Livraison</div>
                            {{ Str::limit($order->delivery_address ?? 'Non renseignée', 40) }}
                        </div>
                    </div>
                </div>
                <div class="ord-meta">
                    <span class="meta-chip">🛍️ {{ $order->items?->count() ?? 0 }} art.</span>
                </div>
            </div>
            <div class="ord-actions">
                <button class="btn-act btn-details" onclick="viewOrderDetails({{ $order->id }})">Détails</button>
                <button class="btn-act btn-accept" onclick="acceptOrder({{ $order->id }})">✓ Accepter</button>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <div class="empty-icon">☕</div>
            <p class="empty-text">Aucune commande disponible<br>pour le moment</p>
        </div>
        @endforelse
    </div>
    @else
    <div class="section">
        <div class="empty-state">
            <div class="empty-icon">🔴</div>
            <p class="empty-text">Passez en ligne pour voir<br>les commandes disponibles</p>
        </div>
    </div>
    @endif
</div>

<!-- BOTTOM NAV -->
<nav class="bot-nav">
    <a href="{{ route('driver.dashboard') }}" class="nav-item active"><span class="ico">🏠</span>Dashboard</a>
    <a href="{{ route('driver.map') }}" class="nav-item"><span class="ico">🗺️</span>Carte</a>
    <a href="{{ route('driver.deliveries') }}" class="nav-item"><span class="ico">📦</span>Historique</a>
    <a href="{{ route('driver.tarifs') }}" class="nav-item"><span class="ico">💰</span>Tarifs</a>
    <a href="{{ route('driver.stats') }}" class="nav-item"><span class="ico">📊</span>Stats</a>
</nav>

<script>
const csrf='{{ csrf_token() }}',loading=document.getElementById('loading');
function showL(){loading.classList.add('active')}
function hideL(){loading.classList.remove('active')}

async function api(url,method='POST',body=null){
    const opts={method,headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}};
    if(body)opts.body=JSON.stringify(body);
    return fetch(url,opts).then(r=>r.json());
}

async function toggleStatus(){showL();try{const d=await api('{{ route("driver.toggle-availability") }}');if(d.success)location.reload();}catch(e){alert('Erreur')}hideL()}
async function acceptOrder(id){if(!confirm('Accepter cette livraison ?'))return;showL();try{const d=await api(`/driver/deliveries/${id}/accept`);if(d.success){if(d.surge>1)alert(`Surge x${d.surge} ! Commission: ${d.commission}€`);location.reload();}else alert(d.message||'Erreur');}catch(e){alert('Erreur')}hideL()}
async function pickupOrder(id){showL();try{const d=await api(`/driver/deliveries/${id}/pickup`);if(d.success)location.reload();}catch(e){}hideL()}
async function startDelivery(id){showL();try{const d=await api(`/driver/deliveries/${id}/start`);if(d.success)location.reload();}catch(e){}hideL()}
async function deliverOrder(id){
    const code=prompt('Code de confirmation client (4 chiffres) :');
    if(!code)return;
    if(!/^\d{4}$/.test(code)){alert('Code = 4 chiffres');return;}
    showL();try{const d=await api(`/driver/deliveries/${id}/deliver`,'POST',{delivery_code:code});if(d.success){alert(`Livraison terminée ! 🎉\nGains: +${d.earnings??0}€`);location.reload();}else alert(d.message||'Erreur');}catch(e){alert('Erreur')}hideL();}
function viewOrderDetails(id){window.location.href=`/driver/deliveries/${id}`}

// GPS tracking
if('geolocation' in navigator){navigator.geolocation.watchPosition(async p=>{try{await api('{{ route("driver.update-location") }}','POST',{lat:p.coords.latitude,lng:p.coords.longitude})}catch(e){}},()=>{},{enableHighAccuracy:true,timeout:10000,maximumAge:30000})}

// Auto refresh 30s
setInterval(()=>fetch('{{ route("driver.dashboard") }}',{headers:{'X-Requested-With':'XMLHttpRequest'}}),30000);

// Theme
function initTheme(){const t=localStorage.getItem('driver-theme')||'light';document.documentElement.setAttribute('data-theme',t);document.getElementById('themeBtn').textContent=t==='dark'?'☀️':'🌙'}
function toggleTheme(){const c=document.documentElement.getAttribute('data-theme');const n=c==='dark'?'light':'dark';document.documentElement.setAttribute('data-theme',n);localStorage.setItem('driver-theme',n);document.getElementById('themeBtn').textContent=n==='dark'?'☀️':'🌙'}
initTheme();
</script>
@endsection
