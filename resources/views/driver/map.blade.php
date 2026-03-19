@extends('layouts.app')
@section('title', 'Carte Pro – Livreur')

@push('styles')
<style>
:root{--drv:#10b981;--drv-dark:#059669;--warn:#f59e0b;--danger:#ef4444;--blue:#3b82f6;--purple:#8b5cf6;--orange:#f97316;--bg:#f8fafc;--card:#fff;--card2:#f3f4f6;--border:#e5e7eb;--t1:#1f2937;--t2:#6b7280;--t3:#9ca3af;--r:14px;--r-sm:10px;
--sheet-peek:100px;--sheet-half:48vh;--sheet-full:88vh;--safe-bottom:env(safe-area-inset-bottom,0px)}
[data-theme=dark]{--bg:#0a0a0a;--card:#141414;--card2:#1c1c1c;--border:#262626;--t1:#fafafa;--t2:#a3a3a3;--t3:#737373}
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden;-webkit-overflow-scrolling:touch}
body{font-family:'SF Pro Display',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:var(--t1)}

/* FULL MAP LAYOUT */
.map-wrap{position:fixed;inset:0;width:100%;height:100%;overflow:hidden}
#gmap{width:100%;height:100%}

/* TOP BAR (floating) — safe area aware */
.map-topbar{position:absolute;top:0;left:0;right:0;z-index:10;padding:max(env(safe-area-inset-top,8px),8px) 12px 6px;display:flex;flex-direction:column;gap:6px}
.topbar-row{display:flex;gap:8px;align-items:center}
.map-back{width:42px;height:42px;border-radius:12px;background:var(--card);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:1.1rem;cursor:pointer;text-decoration:none;color:var(--t1);box-shadow:0 2px 12px rgba(0,0,0,.15);flex-shrink:0}
.map-filters{display:flex;gap:6px;overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:none;-ms-overflow-style:none;padding:2px 0;flex:1}
.map-filters::-webkit-scrollbar{display:none}
.filter-chip{padding:7px 14px;border-radius:99px;background:var(--card);border:1px solid var(--border);font-size:.72rem;font-weight:600;cursor:pointer;color:var(--t2);box-shadow:0 2px 8px rgba(0,0,0,.1);white-space:nowrap;flex-shrink:0;-webkit-tap-highlight-color:transparent;transition:all .2s}
.filter-chip.active{background:var(--drv);color:#fff;border-color:var(--drv)}

/* STATUS + STATS BAR */
.map-status-bar{display:flex;gap:6px;align-items:stretch}
.map-status-pill{flex-shrink:0;padding:8px 14px;border-radius:12px;font-size:.72rem;font-weight:700;display:flex;align-items:center;gap:6px;box-shadow:0 2px 10px rgba(0,0,0,.15);cursor:pointer;-webkit-tap-highlight-color:transparent;transition:all .2s;border:none}
.map-status-pill.online{background:var(--drv);color:#fff}
.map-status-pill.offline{background:var(--danger);color:#fff}
.map-status-pill.busy{background:var(--warn);color:#fff}
.map-status-pill .pulse{width:8px;height:8px;border-radius:50%;background:#fff;animation:pulse-glow 2s infinite}
@keyframes pulse-glow{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(1.3)}}

/* MINI STATS STRIP */
.map-mini-stats{display:flex;gap:4px;flex:1;overflow-x:auto;scrollbar-width:none;-ms-overflow-style:none}
.map-mini-stats::-webkit-scrollbar{display:none}
.mini-stat{padding:6px 10px;border-radius:10px;background:var(--card);border:1px solid var(--border);box-shadow:0 2px 8px rgba(0,0,0,.1);display:flex;flex-direction:column;align-items:center;min-width:60px;flex-shrink:0}
.mini-stat .val{font-size:.82rem;font-weight:800;color:var(--t1);line-height:1}
.mini-stat .lbl{font-size:.55rem;font-weight:600;color:var(--t3);margin-top:2px;text-transform:uppercase;letter-spacing:.3px}
.mini-stat.surge{background:linear-gradient(135deg,#f59e0b10,#ef444410);border-color:var(--warn)}
.mini-stat.surge .val{color:var(--warn)}

/* FLOATING ACTION BUTTONS — positioned relative to sheet */
.map-fab-group{position:absolute;right:12px;z-index:15;display:flex;flex-direction:column;gap:8px;transition:bottom .3s cubic-bezier(.32,.72,0,1)}
.fab-btn{width:46px;height:46px;border-radius:14px;background:var(--card);border:1px solid var(--border);box-shadow:0 2px 12px rgba(0,0,0,.15);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.1rem;-webkit-tap-highlight-color:transparent;transition:all .15s;position:relative}
.fab-btn:active{transform:scale(.9)}
.fab-btn.active{background:var(--blue);color:#fff;border-color:var(--blue)}
.fab-badge{position:absolute;top:-4px;right:-4px;background:var(--danger);color:#fff;font-size:.55rem;font-weight:800;padding:2px 5px;border-radius:99px;min-width:16px;text-align:center;line-height:1.1}

/* SPEED INDICATOR */
.speed-badge{position:absolute;left:12px;z-index:15;background:var(--card);border:1px solid var(--border);border-radius:14px;padding:6px 12px;box-shadow:0 2px 12px rgba(0,0,0,.15);display:flex;align-items:center;gap:6px;transition:bottom .3s cubic-bezier(.32,.72,0,1)}
.speed-badge .spd{font-size:1.1rem;font-weight:900;color:var(--t1);line-height:1}
.speed-badge .unit{font-size:.55rem;font-weight:600;color:var(--t3);text-transform:uppercase}
.speed-badge .heading-arrow{font-size:.9rem;transition:transform .3s}

/* ============================
   BOTTOM SHEET — DRAGGABLE
   ============================ */
.bottom-sheet{position:absolute;left:0;right:0;bottom:0;z-index:20;background:var(--card);border-radius:18px 18px 0 0;box-shadow:0 -4px 24px rgba(0,0,0,.18);will-change:transform;touch-action:none;padding-bottom:var(--safe-bottom)}
.bottom-sheet.snapping{transition:transform .35s cubic-bezier(.32,.72,0,1)}

/* Handle area — larger touch target */
.sheet-drag-zone{padding:10px 0 4px;cursor:grab;-webkit-tap-highlight-color:transparent}
.sheet-drag-zone:active{cursor:grabbing}
.sheet-handle{width:36px;height:4px;border-radius:99px;background:var(--border);margin:0 auto}

/* PEEK SUMMARY — visible when collapsed */
.sheet-peek{padding:4px 16px 10px;display:flex;align-items:center;gap:10px}
.peek-icon{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0}
.peek-icon.has-orders{background:rgba(16,185,129,.12)}
.peek-icon.no-orders{background:var(--card2)}
.peek-info{flex:1;min-width:0}
.peek-title{font-weight:700;font-size:.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.peek-sub{font-size:.68rem;color:var(--t3);margin-top:1px;display:flex;align-items:center;gap:4px}
.peek-earning{font-weight:800;font-size:.95rem;color:var(--drv);flex-shrink:0}
.peek-arrow{width:28px;height:28px;border-radius:8px;background:var(--card2);display:flex;align-items:center;justify-content:center;font-size:.7rem;color:var(--t3);flex-shrink:0;transition:transform .3s}
.sheet-expanded .peek-arrow{transform:rotate(180deg)}

/* SCROLLABLE CONTENT — hidden when collapsed */
.sheet-scroll{overflow-y:auto;-webkit-overflow-scrolling:touch;overscroll-behavior:contain;padding:0 16px;opacity:0;transition:opacity .2s;max-height:0}
.sheet-expanded .sheet-scroll{opacity:1;max-height:none}

/* QUICK ACTIONS BAR */
.quick-actions{display:flex;gap:6px;padding:10px 0;overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:none;-ms-overflow-style:none}
.quick-actions::-webkit-scrollbar{display:none}
.qa-btn{display:flex;flex-direction:column;align-items:center;gap:4px;padding:10px 14px;border-radius:12px;background:var(--card2);border:1px solid var(--border);font-size:.6rem;font-weight:600;color:var(--t2);cursor:pointer;-webkit-tap-highlight-color:transparent;flex-shrink:0;min-width:68px;transition:all .2s}
.qa-btn:active{transform:scale(.95)}
.qa-btn .ico{font-size:1.3rem}
.qa-btn.active-action{background:var(--drv);color:#fff;border-color:var(--drv)}
.code-entry{padding:4px 0 10px}
.code-entry-label{font-size:.64rem;font-weight:700;color:var(--t2);margin-bottom:6px;text-transform:uppercase;letter-spacing:.3px}
.code-entry-form{display:flex;gap:6px;align-items:center}
.code-entry-input{flex:1;min-width:0;height:40px;border-radius:10px;border:1px solid var(--border);background:var(--card2);color:var(--t1);padding:0 10px;font-size:.78rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
.code-entry-input::placeholder{color:var(--t3);letter-spacing:0;text-transform:none;font-weight:500}
.code-entry-input:focus{outline:none;border-color:var(--blue);box-shadow:0 0 0 2px rgba(59,130,246,.12)}
.code-entry-btn{height:40px;border:none;border-radius:10px;background:linear-gradient(135deg,var(--blue),#2563eb);color:#fff;padding:0 12px;font-size:.72rem;font-weight:800;cursor:pointer;white-space:nowrap}
.code-entry-btn:active{transform:scale(.97)}

/* TODAY STATS CARD */
.today-stats-card{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;padding:8px 0}
.ts-item{text-align:center;padding:8px 4px;border-radius:10px;background:var(--card2)}
.ts-item .ts-val{font-size:.9rem;font-weight:800;color:var(--t1)}
.ts-item .ts-lbl{font-size:.55rem;color:var(--t3);text-transform:uppercase;font-weight:600;margin-top:2px}

/* Section headers */
.sheet-section{font-size:.7rem;font-weight:700;color:var(--t2);text-transform:uppercase;letter-spacing:.5px;padding:12px 0 6px;display:flex;align-items:center;gap:6px}
.sheet-section .count{background:var(--drv);color:#fff;font-size:.6rem;padding:1px 6px;border-radius:99px;font-weight:700}

/* ORDER CARD IN SHEET */
.sheet-order{display:flex;gap:10px;padding:10px 12px;margin-bottom:6px;border-radius:12px;background:var(--card2);border:1px solid transparent;transition:all .2s;-webkit-tap-highlight-color:transparent}
.sheet-order:active{transform:scale(.98);border-color:var(--drv)}
.sheet-order-left{display:flex;flex-direction:column;align-items:center;gap:4px;flex-shrink:0}
.sheet-ico{font-size:1.2rem;width:40px;height:40px;border-radius:10px;background:var(--card);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.order-timer{font-size:.55rem;font-weight:700;color:var(--warn);text-align:center}
.sheet-info{flex:1;min-width:0}
.sheet-name{font-weight:700;font-size:.78rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sheet-addr{font-size:.65rem;color:var(--t3);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:flex;align-items:center;gap:4px}
.sheet-items{font-size:.6rem;color:var(--t2);margin-top:3px}
.sheet-meta{display:flex;gap:4px;margin-top:5px;flex-wrap:wrap}
.sheet-chip{font-size:.58rem;padding:2px 7px;border-radius:6px;font-weight:600}
.sheet-chip.earn{background:rgba(16,185,129,.12);color:var(--drv)}
.sheet-chip.dist{background:rgba(59,130,246,.1);color:var(--blue)}
.sheet-chip.time{background:rgba(139,92,246,.1);color:var(--purple)}
.sheet-chip.price{background:rgba(245,158,11,.1);color:var(--warn)}
.sheet-chip.status-chip{background:rgba(16,185,129,.1);color:var(--drv)}
.sheet-chip.ready{background:rgba(16,185,129,.2);color:var(--drv-dark)}
.sheet-chip.preparing{background:rgba(249,115,22,.15);color:var(--orange)}
.sheet-chip.payment{background:rgba(139,92,246,.1);color:var(--purple)}
.sheet-actions{display:flex;flex-direction:column;gap:5px;flex-shrink:0;justify-content:center}
.sheet-btn{padding:8px 12px;border-radius:10px;border:none;font-weight:700;font-size:.68rem;cursor:pointer;transition:.15s;-webkit-tap-highlight-color:transparent;white-space:nowrap;text-decoration:none;text-align:center;display:flex;align-items:center;gap:4px;justify-content:center}
.sheet-btn:active{transform:scale(.94)}
.sheet-btn.gps{background:var(--blue);color:#fff}
.sheet-btn.accept{background:var(--drv);color:#fff;padding:8px 14px}
.sheet-btn.pickup-btn{background:var(--orange);color:#fff}
.sheet-btn.deliver-btn{background:var(--drv);color:#fff}
.sheet-btn.call-btn{background:var(--card);color:var(--blue);border:1px solid var(--blue)}
.sheet-btn.detail{background:var(--card);color:var(--t1);border:1px solid var(--border)}

/* EMPTY STATE */
.sheet-empty{text-align:center;padding:24px 16px}
.sheet-empty .icon{font-size:2.2rem;margin-bottom:8px;opacity:.6}
.sheet-empty p{font-size:.78rem;color:var(--t3)}
.sheet-empty .hint{font-size:.68rem;color:var(--t3);margin-top:6px;opacity:.7}

/* CONNECTION INDICATOR */
.conn-indicator{position:absolute;top:4px;right:4px;width:8px;height:8px;border-radius:50%;z-index:25}
.conn-indicator.connected{background:#10b981}
.conn-indicator.disconnected{background:#ef4444;animation:blink-conn 1s infinite}
@keyframes blink-conn{0%,100%{opacity:1}50%{opacity:.3}}

/* TOAST NOTIFICATION */
.map-toast{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) scale(.8);z-index:200;background:var(--card);border:1px solid var(--border);border-radius:16px;padding:16px 24px;box-shadow:0 8px 32px rgba(0,0,0,.2);text-align:center;opacity:0;pointer-events:none;transition:all .3s}
.map-toast.show{opacity:1;transform:translate(-50%,-50%) scale(1);pointer-events:auto}
.map-toast .toast-ico{font-size:2rem;margin-bottom:6px}
.map-toast .toast-msg{font-size:.82rem;font-weight:700;color:var(--t1)}
.map-toast .toast-sub{font-size:.68rem;color:var(--t3);margin-top:3px}

/* LOADING */
.map-loading{position:absolute;inset:0;background:var(--bg);display:flex;flex-direction:column;align-items:center;justify-content:center;z-index:100}
.map-loading .spinner{width:40px;height:40px;border:3px solid var(--border);border-top-color:var(--drv);border-radius:50%;animation:spin .6s linear infinite}
.map-loading p{margin-top:10px;font-size:.8rem;color:var(--t3)}
@keyframes spin{to{transform:rotate(360deg)}}

/* NEW ORDER ALERT */
.new-order-alert{position:absolute;top:50%;left:12px;right:12px;transform:translateY(-50%) scale(.9);z-index:150;background:var(--card);border:2px solid var(--drv);border-radius:18px;padding:20px;box-shadow:0 12px 40px rgba(0,0,0,.25);opacity:0;pointer-events:none;transition:all .4s cubic-bezier(.32,.72,0,1)}
.new-order-alert.visible{opacity:1;transform:translateY(-50%) scale(1);pointer-events:auto}
.noa-header{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.noa-icon{width:50px;height:50px;border-radius:14px;background:rgba(16,185,129,.12);display:flex;align-items:center;justify-content:center;font-size:1.5rem}
.noa-title{flex:1}
.noa-title h3{font-size:.95rem;font-weight:800;margin:0}
.noa-title p{font-size:.7rem;color:var(--t3);margin-top:2px}
.noa-countdown{font-size:1.5rem;font-weight:900;color:var(--warn);background:rgba(245,158,11,.1);width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center}
.noa-details{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px}
.noa-detail{padding:8px;border-radius:10px;background:var(--card2)}
.noa-detail .nd-lbl{font-size:.6rem;text-transform:uppercase;color:var(--t3);font-weight:600}
.noa-detail .nd-val{font-size:.82rem;font-weight:700;margin-top:2px}
.noa-actions{display:flex;gap:8px}
.noa-btn{flex:1;padding:12px;border-radius:12px;border:none;font-weight:800;font-size:.82rem;cursor:pointer;transition:all .15s}
.noa-btn:active{transform:scale(.96)}
.noa-btn.accept-btn{background:var(--drv);color:#fff}
.noa-btn.reject-btn{background:var(--card2);color:var(--t2);border:1px solid var(--border)}

/* Responsive overrides */
@media(max-width:380px){
    .filter-chip{padding:6px 10px;font-size:.65rem}
    .sheet-btn{padding:6px 10px;font-size:.62rem}
    .sheet-name{font-size:.72rem}
    .peek-title{font-size:.76rem}
    .peek-earning{font-size:.88rem}
    .mini-stat{padding:4px 8px;min-width:52px}
    .mini-stat .val{font-size:.72rem}
    .qa-btn{padding:8px 10px;min-width:60px}
    .today-stats-card{grid-template-columns:repeat(2,1fr)}
}
@media(min-width:768px){
    .bottom-sheet{left:auto;right:16px;width:400px;bottom:16px;border-radius:18px;max-height:calc(100vh - 120px)}
    .bottom-sheet.snapping{transition:transform .35s cubic-bezier(.32,.72,0,1)}
    :root{--sheet-peek:100px;--sheet-half:50vh;--sheet-full:calc(100vh - 120px)}
}

/* AUDIO INDICATOR */
.sound-on .fab-btn.sound-toggle{background:var(--drv);color:#fff;border-color:var(--drv)}

/* Nettoyage UI demandé */
.map-fab-group{display:none !important;}
.map-filters{display:none !important;}
</style>
@endpush

@section('content')
<div class="map-wrap" id="mapWrap">
    <!-- LOADING -->
    <div class="map-loading" id="mapLoading">
        <div class="spinner"></div>
        <p>Chargement de la carte...</p>
    </div>

    <!-- MAP -->
    <div id="gmap"></div>

    <!-- CONNECTION INDICATOR -->
    <div class="conn-indicator connected" id="connIndicator" title="GPS connecté"></div>

    <!-- TOP BAR -->
    <div class="map-topbar">
        <div class="topbar-row">
            <a href="{{ $internalOnlyMode ? route('driver.deliveries') : route('driver.dashboard') }}" class="map-back">←</a>
        </div>

        <div class="map-status-bar">
            <!-- Toggle status -->
            <button class="map-status-pill {{ $driver->status === 'available' ? 'online' : ($driver->status === 'busy' ? 'busy' : 'offline') }}" id="statusToggle" onclick="toggleDriverStatus()">
                <span class="pulse"></span>
                <span id="statusLabel">
                    @if($driver->status === 'available')En ligne
                    @elseif($driver->status === 'busy')Occupé
                    @else Hors ligne @endif
                </span>
            </button>

            <!-- Mini stats -->
            <div class="map-mini-stats">
                <div class="mini-stat">
                    <span class="val" id="statEarnings">{{ number_format($todayStats['earnings'] ?? 0, 2) }}€</span>
                    <span class="lbl">Gains</span>
                </div>
                <div class="mini-stat">
                    <span class="val" id="statDeliveries">{{ $todayStats['completed'] ?? 0 }}</span>
                    <span class="lbl">Courses</span>
                </div>
                <div class="mini-stat">
                    <span class="val" id="statKm">{{ number_format($todayStats['total_km'] ?? 0, 1) }}</span>
                    <span class="lbl">Km</span>
                </div>
                @if(($surgeMultiplier ?? 1) > 1)
                <div class="mini-stat surge">
                    <span class="val">x{{ number_format($surgeMultiplier, 1) }}</span>
                    <span class="lbl">Surge</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- SPEED INDICATOR -->
    <div class="speed-badge" id="speedBadge">
        <span class="heading-arrow" id="headingArrow">↑</span>
        <div>
            <div class="spd" id="speedValue">0</div>
            <div class="unit">km/h</div>
        </div>
    </div>

    <!-- NEW ORDER ALERT (shown when a new order comes in) -->
    <div class="new-order-alert" id="newOrderAlert">
        <div class="noa-header">
            <div class="noa-icon">🍽️</div>
            <div class="noa-title">
                <h3 id="noaName">Nouvelle commande</h3>
                <p id="noaAddr">Adresse du restaurant</p>
            </div>
            <div class="noa-countdown" id="noaCountdown">30</div>
        </div>
        <div class="noa-details">
            <div class="noa-detail"><div class="nd-lbl">Distance</div><div class="nd-val" id="noaDist">? km</div></div>
            <div class="noa-detail"><div class="nd-lbl">Commission</div><div class="nd-val" id="noaEarn">? €</div></div>
            <div class="noa-detail"><div class="nd-lbl">Articles</div><div class="nd-val" id="noaItems">?</div></div>
            <div class="noa-detail"><div class="nd-lbl">Montant</div><div class="nd-val" id="noaTotal">? €</div></div>
        </div>
        <div class="noa-actions">
            <button class="noa-btn reject-btn" onclick="dismissOrderAlert()">✕ Ignorer</button>
            <button class="noa-btn accept-btn" onclick="acceptAlertOrder()">✓ Accepter</button>
        </div>
    </div>

    <!-- TOAST -->
    <div class="map-toast" id="mapToast">
        <div class="toast-ico" id="toastIco">✅</div>
        <div class="toast-msg" id="toastMsg"></div>
        <div class="toast-sub" id="toastSub"></div>
    </div>

    <!-- BOTTOM SHEET — DRAGGABLE 3 STATES -->
    <div class="bottom-sheet" id="bottomSheet">
        <!-- DRAG ZONE -->
        <div class="sheet-drag-zone" id="sheetDrag">
            <div class="sheet-handle"></div>
        </div>

        <!-- PEEK SUMMARY (always visible) -->
        <div class="sheet-peek" id="sheetPeek" onclick="toggleSheet()">
            @if($activeOrders->count() > 0)
                <div class="peek-icon has-orders">🚀</div>
                <div class="peek-info">
                    <div class="peek-title">{{ $activeOrders->count() }} course{{ $activeOrders->count() > 1 ? 's' : '' }} en cours</div>
                    <div class="peek-sub">
                        @php $nextOrder = $activeOrders->first(); @endphp
                        <span>{{ $nextOrder?->prestataire?->company_name ?? 'Prochaine livraison' }}</span>
                        <span>· {{ $nextOrder->delivery_distance ?? '?' }} km</span>
                        <span>· ~{{ $nextOrder->estimated_delivery_time ?? '?' }} min</span>
                    </div>
                </div>
                <div class="peek-earning">+{{ number_format($activeOrders->sum('driver_commission'), 2) }}€</div>
            @elseif($pendingNearby->count() > 0)
                <div class="peek-icon has-orders">📋</div>
                <div class="peek-info">
                    <div class="peek-title">{{ $pendingNearby->count() }} commande{{ $pendingNearby->count() > 1 ? 's' : '' }} dispo</div>
                    <div class="peek-sub"><span>À proximité · Glissez pour voir</span></div>
                </div>
            @else
                <div class="peek-icon no-orders">☕</div>
                <div class="peek-info">
                    <div class="peek-title">Aucune commande</div>
                    <div class="peek-sub"><span>En attente… dernière vérif. <span id="lastRefresh">à l'instant</span></span></div>
                </div>
            @endif
            <div class="peek-arrow">▲</div>
        </div>

        <!-- SCROLLABLE CONTENT -->
        <div class="sheet-scroll" id="sheetScroll">

            <!-- QUICK ACTIONS -->
            <div class="quick-actions">
                @unless($internalOnlyMode)
                <button class="qa-btn" onclick="window.location='{{ route('driver.dashboard') }}'"><span class="ico">🏠</span>Dashboard</button>
                <button class="qa-btn" onclick="window.location='{{ route('driver.stats') }}'"><span class="ico">📊</span>Stats</button>
                <button class="qa-btn" onclick="window.location='{{ route('driver.tarifs') }}'"><span class="ico">💰</span>Tarifs</button>
                @endunless
                <button class="qa-btn" onclick="window.location='{{ route('driver.deliveries') }}'"><span class="ico">📦</span>Historique</button>
                <button class="qa-btn" onclick="shareLocation()"><span class="ico">📤</span>Partager</button>
            </div>

            @unless($internalOnlyMode)
            <div class="code-entry">
                <div class="code-entry-label">Code tournée interne</div>
                <form method="POST" action="{{ route('driver.internal.access.submit') }}" class="code-entry-form">
                    @csrf
                    <input type="hidden" name="redirect_to" value="/prestataire/food/food-orders/internal-map">
                    <input
                        class="code-entry-input"
                        type="text"
                        name="code"
                        maxlength="32"
                        required
                        autocomplete="off"
                        autocapitalize="characters"
                        spellcheck="false"
                        placeholder="Ex: INT-AB-1234">
                    <button class="code-entry-btn" type="submit">Entrer</button>
                </form>
            </div>
            @endunless

            @if($internalOnlyMode)
            <div style="padding:8px 10px;border-radius:10px;background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.2);font-size:.68rem;color:var(--blue);margin:4px 0 8px">
                Mode interne: vous voyez uniquement les tournées assignées par votre prestataire.
            </div>
            @endif

            <!-- TODAY SUMMARY -->
            <div class="sheet-section">📊 Aujourd'hui</div>
            <div class="today-stats-card">
                <div class="ts-item">
                    <div class="ts-val">{{ $todayStats['completed'] ?? 0 }}</div>
                    <div class="ts-lbl">Livraisons</div>
                </div>
                <div class="ts-item">
                    <div class="ts-val">{{ number_format($todayStats['earnings'] ?? 0, 2) }}€</div>
                    <div class="ts-lbl">Gains</div>
                </div>
                <div class="ts-item">
                    <div class="ts-val">{{ number_format($todayStats['total_km'] ?? 0, 1) }}</div>
                    <div class="ts-lbl">Km total</div>
                </div>
                <div class="ts-item">
                    <div class="ts-val">~{{ $todayStats['avg_time'] ?? 0 }} min</div>
                    <div class="ts-lbl">Moy. temps</div>
                </div>
            </div>

            @if($activeOrders->count() > 0)
            <div class="sheet-section">🚀 Mes courses <span class="count">{{ $activeOrders->count() }}</span></div>
            @foreach($activeOrders as $order)
            <div class="sheet-order" data-type="active" data-id="{{ $order->id }}" data-accepted-at="{{ $order->updated_at?->toIso8601String() }}">
                <div class="sheet-order-left">
                    <div class="sheet-ico">
                        @if($order->delivery_status === 'assigned')📍
                        @elseif($order->delivery_status === 'picked_up')✅
                        @else🚗@endif
                    </div>
                    <div class="order-timer" data-start="{{ $order->updated_at?->toIso8601String() }}">--:--</div>
                </div>
                <div class="sheet-info">
                    <div class="sheet-name">{{ $order->prestataire?->company_name ?? 'Restaurant' }}</div>
                    <div class="sheet-addr">
                        @if($order->delivery_status === 'assigned')
                            📍 {{ Str::limit($order->prestataire?->address ?? '', 35) }}
                        @else
                            🏠 {{ Str::limit($order->delivery_address ?? '', 35) }}
                        @endif
                    </div>
                    <div class="sheet-items">
                        {{ $order->items->count() ?? 0 }} article(s) · #{{ $order->id }}
                        · {{ $order->client->name ?? 'Client' }}
                    </div>
                    <div class="sheet-meta">
                        <span class="sheet-chip earn">+{{ number_format($order->driver_commission ?? 2, 2) }}€</span>
                        <span class="sheet-chip dist">{{ $order->delivery_distance ?? '?' }} km</span>
                        <span class="sheet-chip time">~{{ $order->estimated_delivery_time ?? '?' }} min</span>
                        @if($order->status === 'ready')
                            <span class="sheet-chip ready">✓ Prêt</span>
                        @elseif($order->status === 'preparing')
                            <span class="sheet-chip preparing">⏳ Préparation</span>
                        @endif
                        @if($order->payment_method ?? false)
                            <span class="sheet-chip payment">{{ $order->payment_method === 'cash' ? '💵 Espèces' : '💳 Carte' }}</span>
                        @endif
                    </div>
                </div>
                <div class="sheet-actions">
                    @if($order->delivery_status === 'assigned')
                        <button class="sheet-btn pickup-btn" onclick="event.stopPropagation();quickAction('pickup',{{ $order->id }})">📦 Récup</button>
                    @elseif($order->delivery_status === 'picked_up')
                        <button class="sheet-btn deliver-btn" onclick="event.stopPropagation();quickAction('deliver',{{ $order->id }})">🔐 Code client</button>
                    @endif
                    <a href="{{ route('driver.navigate', $order) }}" class="sheet-btn gps">🧭 GPS</a>
                    <button class="sheet-btn call-btn" onclick="event.stopPropagation();callContact({{ $order->id }},'{{ $order->delivery_status === 'assigned' ? 'restaurant' : 'client' }}')">📞</button>
                </div>
            </div>
            @endforeach
            @endif

            @if($pendingNearby->count() > 0)
            <div class="sheet-section">📋 Commandes à proximité <span class="count">{{ $pendingNearby->count() }}</span></div>
            @foreach($pendingNearby as $order)
            <div class="sheet-order" data-type="pending" data-id="{{ $order->id }}">
                <div class="sheet-order-left">
                    <div class="sheet-ico">🍽️</div>
                </div>
                <div class="sheet-info">
                    <div class="sheet-name">{{ $order->prestataire?->company_name ?? 'Restaurant' }}</div>
                    <div class="sheet-addr">📍 {{ Str::limit($order->prestataire?->address ?? '', 35) }}</div>
                    <div class="sheet-items">
                        {{ $order->items->count() ?? '?' }} article(s) · 🏠 {{ Str::limit($order->delivery_address ?? '', 25) }}
                    </div>
                    <div class="sheet-meta">
                        <span class="sheet-chip dist">{{ number_format($order->distance_from_driver ?? 0, 1) }} km</span>
                        <span class="sheet-chip price">{{ number_format($order->total ?? 0, 2) }}€</span>
                        <span class="sheet-chip earn">~+{{ number_format(($order->driver_commission ?? 0), 2) }}€</span>
                        @if($order->status === 'ready')
                            <span class="sheet-chip ready">✓ Prêt</span>
                        @endif
                    </div>
                </div>
                <div class="sheet-actions">
                    <button class="sheet-btn accept" onclick="event.stopPropagation();acceptFromMap({{ $order->id }})">✓ Accepter</button>
                </div>
            </div>
            @endforeach
            @endif

            @if($activeOrders->count() === 0 && $pendingNearby->count() === 0)
            <div class="sheet-empty">
                <div class="icon">☕</div>
                <p>Aucune commande à proximité</p>
                <div class="hint">Restez en ligne, les commandes arrivent bientôt.<br>Rafraîchissement auto toutes les 30s.</div>
            </div>
            @endif

            <!-- DRIVER INFO CARD -->
            <div class="sheet-section">👤 Mon profil</div>
            <div style="display:flex;gap:10px;padding:8px 0;align-items:center">
                <div style="width:42px;height:42px;border-radius:12px;background:var(--card2);display:flex;align-items:center;justify-content:center;font-size:1.2rem">
                    @if($driver->vehicle_type === 'bike')🚲
                    @elseif($driver->vehicle_type === 'scooter')🛵
                    @elseif($driver->vehicle_type === 'car')🚗
                    @else🚚@endif
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-weight:700;font-size:.78rem">{{ $driver->first_name }} {{ $driver->last_name }}</div>
                    <div style="font-size:.65rem;color:var(--t3)">
                        ⭐ {{ number_format($driver->rating ?? 0, 1) }}
                        · {{ $driver->completed_deliveries ?? 0 }} livraisons
                        · {{ ucfirst($driver->vehicle_type ?? 'véhicule') }}
                        @if($driver->vehicle_plate) · {{ $driver->vehicle_plate }} @endif
                    </div>
                </div>
            </div>

            <div style="height:calc(var(--safe-bottom) + 20px)"></div>
        </div>
    </div>
</div>

<!-- Audio for notifications -->
<audio id="notifSound" preload="auto">
    <source src="data:audio/wav;base64,UklGRiQAAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YQAAAAA=" type="audio/wav">
</audio>

<script>
/* =========================================
   DATA & CONFIG
   ========================================= */
let map,driverMarker,markers={active:[],pending:[]},directionsService,directionsRenderer,currentFilter='all';
let trafficLayer=null,trafficVisible=false,soundEnabled=true;
let currentSpeed=0,currentHeading=0,lastRefreshTime=Date.now();
let alertOrderId=null,alertCountdownInterval=null,autoRefreshInterval=null;
let fallbackRoutePolyline=null;

const csrf='{{ csrf_token() }}';
const waypoints={{ Js::from($waypoints) }};
const pendingData={{ Js::from($pendingNearby->map(fn($o)=>['id'=>$o->id,'lat'=>(float)($o->prestataire->latitude??0),'lng'=>(float)($o->prestataire->longitude??0),'name'=>$o->prestataire->company_name??'Restaurant','distance'=>$o->distance_from_driver??0,'total'=>(float)($o->total??0),'items'=>$o->items?->count()??0,'address'=>$o->prestataire->address??'','delivery_address'=>$o->delivery_address??''])->values()) }};
const driverPos={lat:{{ $driver->current_lat ?? 48.8566 }},lng:{{ $driver->current_lng ?? 2.3522 }}};
const phoneData={{ Js::from($activeOrders->mapWithKeys(fn($o)=>[$o->id=>['restaurant'=>$o->prestataire?->phone??'','client'=>$o->client?->phone??'']])) }};

/* =========================================
   TOAST & NOTIFICATIONS
   ========================================= */
function showToast(ico,msg,sub='',duration=2500){
    const t=document.getElementById('mapToast');
    document.getElementById('toastIco').textContent=ico;
    document.getElementById('toastMsg').textContent=msg;
    document.getElementById('toastSub').textContent=sub;
    t.classList.add('show');
    setTimeout(()=>t.classList.remove('show'),duration);
}

function playNotifSound(){
    if(!soundEnabled)return;
    try{
        const ctx=new(window.AudioContext||window.webkitAudioContext)();
        const osc=ctx.createOscillator();
        const gain=ctx.createGain();
        osc.connect(gain);gain.connect(ctx.destination);
        osc.frequency.setValueAtTime(880,ctx.currentTime);
        gain.gain.setValueAtTime(0.3,ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01,ctx.currentTime+0.5);
        osc.start(ctx.currentTime);osc.stop(ctx.currentTime+0.5);
        // Second beep
        setTimeout(()=>{
            const osc2=ctx.createOscillator();
            const gain2=ctx.createGain();
            osc2.connect(gain2);gain2.connect(ctx.destination);
            osc2.frequency.setValueAtTime(1100,ctx.currentTime);
            gain2.gain.setValueAtTime(0.3,ctx.currentTime);
            gain2.gain.exponentialRampToValueAtTime(0.01,ctx.currentTime+0.3);
            osc2.start(ctx.currentTime);osc2.stop(ctx.currentTime+0.3);
        },200);
    }catch(e){}
}

function toggleSound(){
    soundEnabled=!soundEnabled;
    const btn=document.getElementById('soundBtn');
    if(btn){
        btn.textContent=soundEnabled?'🔔':'🔕';
        if(soundEnabled) btn.classList.add('active');
        else btn.classList.remove('active');
    }
    showToast(soundEnabled?'🔔':'🔕',soundEnabled?'Notifications activées':'Notifications désactivées');
}

/* =========================================
   DRIVER STATUS TOGGLE
   ========================================= */
async function toggleDriverStatus(){
    const btn=document.getElementById('statusToggle');
    const lbl=document.getElementById('statusLabel');
    btn.style.opacity='.6';
    try{
        const r=await fetch('{{ route("driver.toggle-availability") }}',{
            method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}
        });
        const d=await r.json();
        if(d.success){
            btn.className='map-status-pill '+(d.status==='available'?'online':'offline');
            lbl.textContent=d.message;
            showToast(d.status==='available'?'🟢':'🔴',d.message);
            if(d.status==='available') startAutoRefresh();
            else stopAutoRefresh();
        }
    }catch(e){showToast('❌','Erreur de connexion')}
    btn.style.opacity='1';
}

/* =========================================
   ORDER TIMERS
   ========================================= */
function updateTimers(){
    document.querySelectorAll('.order-timer[data-start]').forEach(el=>{
        const start=new Date(el.dataset.start);
        const diff=Math.floor((Date.now()-start)/1000);
        const m=Math.floor(diff/60);
        const s=diff%60;
        el.textContent=`${m}:${s.toString().padStart(2,'0')}`;
        if(m>=20) el.style.color='var(--danger)';
        else if(m>=10) el.style.color='var(--warn)';
    });
}
setInterval(updateTimers,1000);
updateTimers();

/* =========================================
   QUICK ACTIONS (pickup/deliver)
   ========================================= */
async function quickAction(action,orderId){
    const labels={pickup:'Récupération confirmée',deliver:'Livraison confirmée'};
    const endpoints={pickup:'pickup',deliver:'deliver'};
    const body={};
    if(action==='deliver'){
        const code=prompt('Code de confirmation client (4 chiffres) :');
        if(!code)return;
        if(!/^\d{4}$/.test(code)){showToast('❌','Code invalide');return;}
        body.delivery_code=code;
    }
    try{
        const r=await fetch(`/driver/deliveries/${orderId}/${endpoints[action]}`,{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
            body: JSON.stringify(body)
        });
        const d=await r.json();
        if(d.success){
            showToast('✅',labels[action]);
            setTimeout(()=>location.reload(),1000);
        } else {
            showToast('❌',d.message||'Erreur');
        }
    }catch(e){showToast('❌','Erreur de connexion')}
}

/* =========================================
   CALL CONTACT
   ========================================= */
function callContact(orderId,type){
    const phones=phoneData[orderId];
    if(!phones)return showToast('❌','Numéro non disponible');
    const phone=type==='restaurant'?phones.restaurant:phones.client;
    if(!phone)return showToast('❌','Numéro non disponible');
    window.location.href='tel:'+phone;
}

/* =========================================
   SHARE LOCATION
   ========================================= */
function shareLocation(){
    if(!driverMarker)return;
    const pos=driverMarker.getPosition();
    const url=`https://www.google.com/maps?q=${pos.lat()},${pos.lng()}`;
    if(navigator.share){
        navigator.share({title:'Ma position - Livreur',text:'Voici ma position actuelle',url}).catch(()=>{});
    } else {
        navigator.clipboard.writeText(url).then(()=>showToast('📋','Position copiée !'));
    }
}

/* =========================================
   BOTTOM SHEET — DRAGGABLE (3-STATE)
   ========================================= */
const sheet=document.getElementById('bottomSheet');
const sheetDrag=document.getElementById('sheetDrag');
const sheetPeek=document.getElementById('sheetPeek');
const sheetScroll=document.getElementById('sheetScroll');
const fabGroup=document.getElementById('fabGroup');
const speedBadge=document.getElementById('speedBadge');

const STATES={PEEK:0,HALF:1,FULL:2};
let sheetState=STATES.PEEK;
let startY=0,startTranslate=0,currentTranslate=0,isDragging=false;

function getSheetHeight(state){
    const vh=window.innerHeight;
    switch(state){
        case STATES.PEEK: return 100;
        case STATES.HALF: return Math.round(vh*0.48);
        case STATES.FULL: return Math.round(vh*0.88);
    }
}

function setSheetPosition(height,animate){
    const translate=window.innerHeight-height;
    currentTranslate=translate;
    if(animate) sheet.classList.add('snapping');
    else sheet.classList.remove('snapping');
    sheet.style.transform=`translateY(${translate}px)`;

    const peekH=sheetPeek.offsetHeight+sheetDrag.offsetHeight;
    const scrollH=height-peekH;
    sheetScroll.style.maxHeight=scrollH>0 ? scrollH+'px':'0';

    if(height>110) sheet.classList.add('sheet-expanded');
    else sheet.classList.remove('sheet-expanded');

    if(fabGroup) fabGroup.style.bottom=(height+14)+'px';
    if(speedBadge) speedBadge.style.bottom=(height+14)+'px';
}

function snapToState(state,animate=true){
    sheetState=state;
    setSheetPosition(getSheetHeight(state),animate);
    sheetScroll.style.overflowY=(state>=STATES.HALF)?'auto':'hidden';
}

function toggleSheet(){
    if(sheetState===STATES.PEEK) snapToState(STATES.HALF);
    else snapToState(STATES.PEEK);
}

// Touch events — ALL passive (never preventDefault — fixes intervention warning)
let dragStartTime=0;
function onDragStart(e){
    if(sheetScroll.scrollTop>0 && e.target.closest('.sheet-scroll')) return;
    isDragging=true;
    dragStartTime=Date.now();
    const touch=e.touches?e.touches[0]:e;
    startY=touch.clientY;
    startTranslate=currentTranslate;
    sheet.classList.remove('snapping');
}
function onDragMove(e){
    if(!isDragging) return;
    const touch=e.touches?e.touches[0]:e;
    if(!touch) return;
    const y=touch.clientY;
    const delta=y-startY;
    if(Math.abs(delta)<3) return;

    let newTranslate=startTranslate+delta;
    const minTranslate=window.innerHeight-getSheetHeight(STATES.FULL);
    const maxTranslate=window.innerHeight-getSheetHeight(STATES.PEEK);
    newTranslate=Math.max(minTranslate,Math.min(maxTranslate,newTranslate));
    currentTranslate=newTranslate;
    sheet.style.transform=`translateY(${newTranslate}px)`;
    const currentH=window.innerHeight-newTranslate;
    if(fabGroup) fabGroup.style.bottom=(currentH+14)+'px';
    if(speedBadge) speedBadge.style.bottom=(currentH+14)+'px';
    if(currentH>110) sheet.classList.add('sheet-expanded');
    else sheet.classList.remove('sheet-expanded');
}
function onDragEnd(e){
    if(!isDragging) return;
    isDragging=false;
    const touch=e.changedTouches?e.changedTouches[0]:e;
    const y=touch.clientY;
    const delta=y-startY;
    const elapsed=Date.now()-dragStartTime;
    const currentH=window.innerHeight-currentTranslate;
    const halfH=getSheetHeight(STATES.HALF);

    if(Math.abs(delta)<8){
        snapToState(sheetState,true);
        return;
    }

    const speed=Math.abs(delta)/(elapsed||1)*1000;
    if(speed>300 || Math.abs(delta)>80){
        if(delta<0) snapToState(sheetState<STATES.FULL ? sheetState+1 : STATES.FULL);
        else snapToState(sheetState>STATES.PEEK ? sheetState-1 : STATES.PEEK);
        return;
    }
    if(currentH<(100+halfH)/2) snapToState(STATES.PEEK);
    else if(currentH<(halfH+getSheetHeight(STATES.FULL))/2) snapToState(STATES.HALF);
    else snapToState(STATES.FULL);
}

// Bind ALL passive to avoid [Intervention] touchstart warnings
sheetDrag.addEventListener('touchstart',onDragStart,{passive:true});
sheetDrag.addEventListener('touchmove',onDragMove,{passive:true});
sheetDrag.addEventListener('touchend',onDragEnd,{passive:true});
sheetDrag.addEventListener('mousedown',onDragStart);
document.addEventListener('mousemove',onDragMove);
document.addEventListener('mouseup',onDragEnd);

sheetPeek.addEventListener('touchstart',onDragStart,{passive:true});
sheetPeek.addEventListener('touchmove',onDragMove,{passive:true});
sheetPeek.addEventListener('touchend',onDragEnd,{passive:true});

let scrollDragActive=false;
sheetScroll.addEventListener('touchstart',function(e){
    scrollDragActive=false;
    if(this.scrollTop<=0 && sheetState>=STATES.HALF){
        scrollDragActive=true;
        onDragStart(e);
    }
},{passive:true});
sheetScroll.addEventListener('touchmove',function(e){
    if(!scrollDragActive) return;
    const touch=e.touches?e.touches[0]:null;
    if(!touch) return;
    const delta=touch.clientY-startY;
    if(delta>0 && this.scrollTop<=0){
        onDragMove(e);
    } else {
        if(isDragging){
            isDragging=false;
            scrollDragActive=false;
            snapToState(sheetState,true);
        }
    }
},{passive:true});
sheetScroll.addEventListener('touchend',function(e){
    if(scrollDragActive && isDragging) onDragEnd(e);
    scrollDragActive=false;
},{passive:true});

sheet.addEventListener('transitionend',()=>sheet.classList.remove('snapping'));
snapToState(STATES.PEEK,false);
window.addEventListener('resize',()=>snapToState(sheetState,false));

/* =========================================
   GOOGLE MAPS
   ========================================= */
function isValidLatLng(lat,lng){
    return Number.isFinite(lat) && Number.isFinite(lng)
        && lat >= -90 && lat <= 90
        && lng >= -180 && lng <= 180
        && !(Math.abs(lat) < 0.000001 && Math.abs(lng) < 0.000001);
}

function clearFallbackRouteLine(){
    if(fallbackRoutePolyline){
        fallbackRoutePolyline.setMap(null);
        fallbackRoutePolyline=null;
    }
}

function getRouteStops(){
    return (Array.isArray(waypoints)?waypoints:[])
        .map(w=>({
            order_id:Number(w.order_id||0),
            type:String(w.type||''),
            lat:Number(w.lat),
            lng:Number(w.lng),
        }))
        .filter(w=>isValidLatLng(w.lat,w.lng));
}

async function drawRouteViaOsrm(origin,stops){
    if(!isValidLatLng(origin?.lat,origin?.lng) || !Array.isArray(stops) || !stops.length){
        return false;
    }

    const coords=[origin,...stops].map(p=>`${Number(p.lng)},${Number(p.lat)}`).join(';');
    const url=`https://router.project-osrm.org/route/v1/driving/${coords}?overview=full&geometries=geojson`;

    try{
        const r=await fetch(url,{cache:'no-store'});
        if(!r.ok)return false;
        const d=await r.json();
        const route=d?.routes?.[0];
        const geometry=route?.geometry?.coordinates;
        if(!route || !Array.isArray(geometry) || geometry.length<2){
            return false;
        }

        directionsRenderer.setDirections({routes:[]});
        clearFallbackRouteLine();
        const path=geometry.map(c=>({lat:Number(c[1]),lng:Number(c[0])}))
            .filter(p=>isValidLatLng(p.lat,p.lng));
        if(path.length<2){
            return false;
        }

        fallbackRoutePolyline=new google.maps.Polyline({
            path,
            geodesic:true,
            strokeColor:'#3b82f6',
            strokeWeight:5,
            strokeOpacity:.85,
            map,
        });

        const distKm=Number((Number(route.distance||0)/1000).toFixed(1));
        const etaMin=Math.max(1,Math.round(Number(route.duration||0)/60));
        showToast('🗺️',`Itinéraire: ${distKm} km`,`~${etaMin} min estimé`,3000);
        return true;
    }catch(_e){
        return false;
    }
}

function initMap(){
    const isDark=document.documentElement.getAttribute('data-theme')==='dark';
    const darkStyles=[{elementType:'geometry',stylers:[{color:'#242f3e'}]},{elementType:'labels.text.stroke',stylers:[{color:'#242f3e'}]},{elementType:'labels.text.fill',stylers:[{color:'#746855'}]},{featureType:'road',elementType:'geometry',stylers:[{color:'#38414e'}]},{featureType:'road',elementType:'geometry.stroke',stylers:[{color:'#212a37'}]},{featureType:'road.highway',elementType:'geometry',stylers:[{color:'#746855'}]},{featureType:'water',elementType:'geometry',stylers:[{color:'#17263c'}]}];

    map=new google.maps.Map(document.getElementById('gmap'),{
        center:driverPos,zoom:14,disableDefaultUI:true,
        zoomControl:true,zoomControlOptions:{position:google.maps.ControlPosition.LEFT_CENTER},
        styles:isDark ? darkStyles : [
            {featureType:'poi',elementType:'labels',stylers:[{visibility:'off'}]},
            {featureType:'transit',stylers:[{visibility:'simplified'}]},
            {featureType:'road.highway',elementType:'geometry.fill',stylers:[{color:'#e8e0d8'}]},
        ],
        gestureHandling:'greedy',mapTypeControl:false,streetViewControl:false,fullscreenControl:false
    });

    // Driver marker — animated pulsing circle
    driverMarker=new google.maps.Marker({
        position:driverPos,map,
        icon:{path:google.maps.SymbolPath.CIRCLE,scale:14,fillColor:'#10b981',fillOpacity:1,strokeWeight:4,strokeColor:'#fff'},
        title:'Ma position',zIndex:100
    });

    // Direction ring around driver
    new google.maps.Circle({
        center:driverPos,radius:200,map,
        fillColor:'#10b981',fillOpacity:.06,strokeColor:'#10b981',strokeOpacity:.2,strokeWeight:1,
        clickable:false
    });

    directionsService=new google.maps.DirectionsService();
    directionsRenderer=new google.maps.DirectionsRenderer({
        map,suppressMarkers:true,
        polylineOptions:{strokeColor:'#3b82f6',strokeWeight:5,strokeOpacity:.8}
    });

    // Traffic layer (hidden by default)
    trafficLayer=new google.maps.TrafficLayer();

    // Active order markers
    waypoints.forEach((wp,i)=>{
        const lat=Number(wp.lat);
        const lng=Number(wp.lng);
        if(!isValidLatLng(lat,lng)) return;
        const isPickup=wp.type==='pickup';
        const markerColor=isPickup?'#3b82f6':'#10b981';
        const label=isPickup?'P':'D';
        const m=new google.maps.Marker({
            position:{lat,lng},map,
            icon:{url:`data:image/svg+xml;charset=UTF-8,${encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" width="36" height="46" viewBox="0 0 36 46"><defs><filter id="s"><feDropShadow dx="0" dy="2" stdDeviation="2" flood-opacity="0.3"/></filter></defs><path d="M18 2C9.16 2 2 9.16 2 18c0 13 16 26 16 26s16-13 16-26C34 9.16 26.84 2 18 2z" fill="${markerColor}" filter="url(#s)"/><circle cx="18" cy="16" r="8" fill="#fff"/><text x="18" y="20" text-anchor="middle" font-size="11" font-weight="bold" fill="${markerColor}">${label}</text></svg>`)}`,scaledSize:new google.maps.Size(36,46)},
            title:`${isPickup?'Récupération':'Livraison'}: ${wp.name}`,zIndex:50,
            animation:google.maps.Animation.DROP
        });
        const phoneLink=wp.phone?`<br><a href="tel:${wp.phone}" style="color:#3b82f6;font-weight:bold;text-decoration:none">📞 ${wp.phone}</a>`:'';
        const info=new google.maps.InfoWindow({content:`<div style="font-family:-apple-system,sans-serif;font-size:13px;min-width:180px;padding:4px"><strong style="font-size:14px">${isPickup?'📍 Récupération':'🏠 Livraison'}</strong><br><span style="font-size:13px;font-weight:600">${wp.name}</span><br><small style="color:#888">${wp.address}</small>${phoneLink}<br><div style="margin-top:8px;display:flex;gap:6px"><a href="/driver/navigate/${wp.order_id}" style="background:#3b82f6;color:#fff;padding:6px 14px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:12px">🧭 Naviguer</a><a href="/driver/deliveries/${wp.order_id}" style="background:#f3f4f6;color:#1f2937;padding:6px 14px;border-radius:8px;text-decoration:none;font-weight:600;font-size:12px">Détails</a></div></div>`});
        m.addListener('click',()=>info.open(map,m));
        markers.active.push(m);
    });

    // Pending markers
    pendingData.forEach(o=>{
        const lat=Number(o.lat);
        const lng=Number(o.lng);
        if(!isValidLatLng(lat,lng)) return;
        const m=new google.maps.Marker({
            position:{lat,lng},map,
            icon:{url:`data:image/svg+xml;charset=UTF-8,${encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><circle cx="16" cy="16" r="14" fill="#f59e0b" stroke="#fff" stroke-width="2.5"/><text x="16" y="21" text-anchor="middle" font-size="14" fill="#fff">🍽</text></svg>`)}`,scaledSize:new google.maps.Size(32,32)},
            title:o.name,zIndex:30,opacity:.9,
            animation:google.maps.Animation.DROP
        });
        const info=new google.maps.InfoWindow({content:`<div style="font-family:-apple-system,sans-serif;font-size:13px;min-width:200px;padding:4px"><strong style="font-size:14px">🍽️ ${o.name}</strong><br><small style="color:#888">${o.address}</small><br><div style="margin-top:4px;font-size:12px"><span style="background:#eef;padding:2px 8px;border-radius:4px">📏 ${o.distance.toFixed(1)} km</span> <span style="background:#fef3c7;padding:2px 8px;border-radius:4px">💰 ${o.total.toFixed(2)}€</span></div><br><button onclick="acceptFromMap(${o.id})" style="width:100%;margin-top:4px;background:#10b981;color:#fff;border:none;padding:10px 16px;border-radius:10px;cursor:pointer;font-weight:bold;font-size:13px">✓ Accepter la livraison</button></div>`});
        m.addListener('click',()=>info.open(map,m));
        markers.pending.push(m);
    });

    if(waypoints.length>0)drawRoute();
    document.getElementById('mapLoading').style.display='none';

    // GPS tracking with speed and heading
    if('geolocation' in navigator){
        navigator.geolocation.watchPosition(p=>{
            const pos={lat:p.coords.latitude,lng:p.coords.longitude};
            driverMarker.setPosition(pos);

            // Speed
            currentSpeed=p.coords.speed!=null ? Math.round(p.coords.speed*3.6) : 0;
            document.getElementById('speedValue').textContent=currentSpeed;

            // Heading
            if(p.coords.heading!=null && p.coords.heading>=0){
                currentHeading=p.coords.heading;
                document.getElementById('headingArrow').style.transform=`rotate(${currentHeading}deg)`;
            }

            // GPS status
            document.getElementById('connIndicator').className='conn-indicator connected';

            // Update server location
            fetch('{{ route("driver.update-location") }}',{
                method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
                body:JSON.stringify({lat:pos.lat,lng:pos.lng,speed:currentSpeed,heading:currentHeading})
            }).catch(()=>{
                document.getElementById('connIndicator').className='conn-indicator disconnected';
            });
        },err=>{
            document.getElementById('connIndicator').className='conn-indicator disconnected';
            if(err.code===err.PERMISSION_DENIED){
                showToast('📍','GPS désactivé','Activez la géolocalisation',3000);
            }
        },{enableHighAccuracy:true,timeout:10000,maximumAge:3000});
    }

    // Start auto-refresh
    startAutoRefresh();
}

function drawRoute(){
    const stops=getRouteStops();
    if(!stops.length) return;

    const markerPos=driverMarker?.getPosition?.();
    const origin={
        lat:Number(markerPos?.lat?.() ?? driverPos.lat),
        lng:Number(markerPos?.lng?.() ?? driverPos.lng),
    };
    if(!isValidLatLng(origin.lat,origin.lng)) return;

    const dest=stops[stops.length-1];
    const wps=stops.slice(0,-1).map(w=>({location:{lat:w.lat,lng:w.lng},stopover:true}));

    directionsService.route({
        origin,
        destination:{lat:dest.lat,lng:dest.lng},
        waypoints:wps,
        travelMode:google.maps.TravelMode.DRIVING,
        optimizeWaypoints:false
    }, async (result,status)=>{
        if(status==='OK' && result?.routes?.[0]){
            clearFallbackRouteLine();
            directionsRenderer.setDirections(result);
            const legs=result.routes[0].legs||[];
            let totalDur=0,totalDist=0;
            legs.forEach(l=>{totalDur+=Number(l?.duration?.value||0);totalDist+=Number(l?.distance?.value||0);});
            const min=Math.max(1,Math.round(totalDur/60));
            const km=(totalDist/1000).toFixed(1);
            showToast('🗺️',`Itinéraire: ${km} km`,`~${min} min estimé`,3000);
            return;
        }

        const osrmOk=await drawRouteViaOsrm(origin,stops);
        if(osrmOk) return;

        // Dernier secours: ligne simple (évite "plus de trajet du tout")
        directionsRenderer.setDirections({routes:[]});
        clearFallbackRouteLine();
        const fallbackPath=[origin,...stops.map(s=>({lat:s.lat,lng:s.lng}))];
        fallbackRoutePolyline=new google.maps.Polyline({
            path:fallbackPath,
            geodesic:true,
            strokeColor:'#3b82f6',
            strokeWeight:4,
            strokeOpacity:.75,
            map,
        });
        showToast('⚠️','Trajet approximatif affiché','Routing indisponible',2500);
    });
}

function filterMarkers(type,el){
    currentFilter=type;
    document.querySelectorAll('.filter-chip').forEach(c=>c.classList.remove('active'));
    el.classList.add('active');
    markers.active.forEach(m=>m.setVisible(type==='all'||type==='active'));
    markers.pending.forEach(m=>m.setVisible(type==='all'||type==='pending'||type==='restaurants'));
    document.querySelectorAll('.sheet-order').forEach(o=>{
        const t=o.dataset.type;
        o.style.display=(type==='all'||t===type||(type==='restaurants'&&t==='pending'))?'flex':'none';
    });
}

function recenter(){
    if(driverMarker)map.panTo(driverMarker.getPosition());
    map.setZoom(15);
    showToast('📍','Position recentrée');
}

function fitAllMarkers(){
    const bounds=new google.maps.LatLngBounds();
    if(driverMarker)bounds.extend(driverMarker.getPosition());
    markers.active.forEach(m=>{if(m.getVisible())bounds.extend(m.getPosition())});
    markers.pending.forEach(m=>{if(m.getVisible())bounds.extend(m.getPosition())});
    map.fitBounds(bounds);
    showToast('📐','Vue ajustée');
}

function toggleTraffic(){
    trafficVisible=!trafficVisible;
    const trafficBtn=document.getElementById('trafficBtn');
    if(trafficVisible){
        trafficLayer.setMap(map);
        if(trafficBtn) trafficBtn.classList.add('active');
    } else {
        trafficLayer.setMap(null);
        if(trafficBtn) trafficBtn.classList.remove('active');
    }
    showToast('🚦',trafficVisible?'Trafic affiché':'Trafic masqué');
}

async function optimizeRoute(){
    const wps=waypoints.map(w=>({lat:w.lat,lng:w.lng,type:w.type,order_id:w.order_id}));
    showToast('🧭','Optimisation en cours...');
    try{
        const r=await fetch('{{ route("driver.optimize-route") }}',{
            method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
            body:JSON.stringify({waypoints:wps})
        });
        const d=await r.json();
        if(d.success&&d.route){
            const coords=d.route.features?.[0]?.geometry?.coordinates;
            if(coords){
                directionsRenderer.setDirections({routes:[]});
                clearFallbackRouteLine();
                const path=coords.map(c=>({lat:c[1],lng:c[0]}));
                new google.maps.Polyline({path,geodesic:true,strokeColor:'#8b5cf6',strokeWeight:5,strokeOpacity:.8,map});
                const dist=(d.distance/1000).toFixed(1);
                const dur=Math.round((d.duration||0)/60);
                showToast('✅',`Optimisé: ${dist} km`,`~${dur} min`);
            }
        }
    }catch(e){showToast('❌','Erreur d\'optimisation')}
}

/* =========================================
   NEW ORDER ALERT FLOW
   ========================================= */
function showNewOrderAlert(order){
    alertOrderId=order.id;
    document.getElementById('noaName').textContent=order.name||'Nouvelle commande';
    document.getElementById('noaAddr').textContent=order.address||'';
    document.getElementById('noaDist').textContent=(order.distance||0).toFixed(1)+' km';
    document.getElementById('noaEarn').textContent='~'+(order.earn||0).toFixed(2)+' €';
    document.getElementById('noaItems').textContent=(order.items||'?')+' article(s)';
    document.getElementById('noaTotal').textContent=(order.total||0).toFixed(2)+' €';
    document.getElementById('newOrderAlert').classList.add('visible');

    playNotifSound();
    if(navigator.vibrate) navigator.vibrate([200,100,200]);

    let countdown=30;
    document.getElementById('noaCountdown').textContent=countdown;
    clearInterval(alertCountdownInterval);
    alertCountdownInterval=setInterval(()=>{
        countdown--;
        document.getElementById('noaCountdown').textContent=countdown;
        if(countdown<=0) dismissOrderAlert();
    },1000);
}

function dismissOrderAlert(){
    clearInterval(alertCountdownInterval);
    document.getElementById('newOrderAlert').classList.remove('visible');
    alertOrderId=null;
}

async function acceptAlertOrder(){
    if(!alertOrderId)return;
    const id=alertOrderId;
    dismissOrderAlert();
    await acceptFromMap(id);
}

async function acceptFromMap(id){
    showToast('⏳','Acceptation en cours...');
    try{
        const r=await fetch(`/driver/deliveries/${id}/accept`,{
            method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}
        });
        const d=await r.json();
        if(d.success){
            showToast('✅','Commande acceptée!','Préparez-vous');
            setTimeout(()=>location.reload(),1200);
        } else {
            showToast('❌',d.message||'Déjà prise');
        }
    }catch(e){showToast('❌','Erreur de connexion')}
}

/* =========================================
   AUTO-REFRESH — poll for new orders
   ========================================= */
let knownPendingIds=new Set(pendingData.map(o=>o.id));

function startAutoRefresh(){
    stopAutoRefresh();
    autoRefreshInterval=setInterval(refreshOrders,30000);
}

function stopAutoRefresh(){
    if(autoRefreshInterval) clearInterval(autoRefreshInterval);
}

async function refreshOrders(){
    const btn=document.getElementById('refreshBtn');
    if(btn) btn.style.animation='spin .6s linear';
    try{
        const r=await fetch(window.location.href,{headers:{'X-Requested-With':'XMLHttpRequest'}});
        const html=await r.text();
        // Parse pending order count from response
        const match=html.match(/data-type="pending"/g);
        const newCount=match?match.length:0;
        const currentCount=document.querySelectorAll('[data-type="pending"]').length;

        if(newCount>currentCount){
            const diff=newCount-currentCount;
            const badge=document.getElementById('newOrderBadge');
            if(badge){
                badge.textContent='+'+diff;
                badge.style.display='block';
            }
            playNotifSound();
            if(navigator.vibrate) navigator.vibrate([200,100,200]);
            showToast('🆕',`${diff} nouvelle(s) commande(s)!`,'Glissez pour voir',3000);
        }

        lastRefreshTime=Date.now();
        const lr=document.getElementById('lastRefresh');
        if(lr) lr.textContent='à l\'instant';
    }catch(e){}
    if(btn) setTimeout(()=>btn.style.animation='',600);
}

// Update "last refresh" text
setInterval(()=>{
    const lr=document.getElementById('lastRefresh');
    if(!lr) return;
    const sec=Math.floor((Date.now()-lastRefreshTime)/1000);
    if(sec<60) lr.textContent='il y a '+sec+'s';
    else lr.textContent='il y a '+Math.floor(sec/60)+'min';
},10000);
</script>

<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&callback=initMap&libraries=geometry,places&loading=async"></script>
@endsection
