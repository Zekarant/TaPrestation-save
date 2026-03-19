@extends('layouts.app')
@section('title', 'Mes Livraisons')

@push('styles')
<style>
:root{--drv:#10b981;--drv-dark:#059669;--warn:#f59e0b;--danger:#ef4444;--blue:#3b82f6;--purple:#8b5cf6;--bg:#f8fafc;--card:#fff;--card2:#f3f4f6;--border:#e5e7eb;--t1:#1f2937;--t2:#6b7280;--t3:#9ca3af;--shadow:0 2px 8px rgba(0,0,0,.05);--r:14px;--r-sm:10px}
[data-theme=dark]{--bg:#0a0a0a;--card:#141414;--card2:#1c1c1c;--border:#262626;--t1:#fafafa;--t2:#a3a3a3;--t3:#737373;--shadow:0 2px 12px rgba(0,0,0,.4)}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);font-family:'SF Pro Display',-apple-system,sans-serif;color:var(--t1)}
.drv-app{max-width:480px;margin:0 auto;min-height:100vh;padding-bottom:80px}

/* HEADER */
.page-head{background:linear-gradient(135deg,var(--drv),var(--drv-dark));padding:14px 16px;display:flex;align-items:center;gap:10px;position:sticky;top:0;z-index:50}
.page-back{width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-size:1rem}
.page-title{font-size:1rem;font-weight:700;color:#fff;flex:1}

/* SUMMARY CARD */
.summary-card{margin:10px 12px;background:var(--card);border-radius:var(--r);border:1px solid var(--border);overflow:hidden}
.summary-grid{display:grid;grid-template-columns:repeat(4,1fr)}
.sum-item{text-align:center;padding:12px 6px;border-right:1px solid var(--border)}
.sum-item:last-child{border:none}
.sum-val{font-size:.95rem;font-weight:800}
.sum-val.green{color:var(--drv)}
.sum-lbl{font-size:.5rem;color:var(--t3);text-transform:uppercase;margin-top:2px}

/* FILTERS */
.filter-bar{display:flex;gap:6px;padding:8px 12px;overflow-x:auto;scrollbar-width:none}
.filter-bar::-webkit-scrollbar{display:none}
.f-chip{padding:6px 14px;border-radius:99px;font-size:.7rem;font-weight:600;border:1px solid var(--border);background:var(--card);color:var(--t2);cursor:pointer;white-space:nowrap;transition:.2s;text-decoration:none}
.f-chip.active{background:var(--drv);color:#fff;border-color:var(--drv)}
.f-chip:active{transform:scale(.96)}

.period-bar{display:flex;gap:6px;padding:0 12px 6px;overflow-x:auto;scrollbar-width:none}
.period-bar::-webkit-scrollbar{display:none}
.p-chip{padding:4px 10px;border-radius:8px;font-size:.65rem;font-weight:600;border:1px solid var(--border);background:var(--card);color:var(--t3);cursor:pointer;white-space:nowrap;text-decoration:none}
.p-chip.active{background:var(--purple);color:#fff;border-color:var(--purple)}

/* DELIVERY LIST */
.dlv-list{padding:0 12px}
.dlv-card{background:var(--card);border-radius:var(--r-sm);border:1px solid var(--border);margin-bottom:8px;overflow:hidden;transition:.2s}
.dlv-card:active{transform:scale(.99)}
.dlv-top{display:flex;align-items:center;gap:10px;padding:10px 12px}
.dlv-ico{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem}
.dlv-ico.delivered{background:rgba(16,185,129,.12);color:var(--drv)}
.dlv-ico.failed{background:rgba(239,68,68,.12);color:var(--danger)}
.dlv-ico.active{background:rgba(59,130,246,.12);color:var(--blue)}
.dlv-ico.pending{background:rgba(245,158,11,.12);color:var(--warn)}
.dlv-info{flex:1}
.dlv-name{font-weight:700;font-size:.8rem}
.dlv-date{font-size:.6rem;color:var(--t3);margin-top:1px}
.dlv-right{text-align:right}
.dlv-amount{font-weight:800;font-size:.85rem}
.dlv-amount.green{color:var(--drv)}
.dlv-amount.red{color:var(--danger)}
.dlv-status{font-size:.55rem;padding:2px 8px;border-radius:6px;margin-top:3px;display:inline-block;font-weight:600}
.dlv-status.delivered{background:rgba(16,185,129,.12);color:var(--drv)}
.dlv-status.failed{background:rgba(239,68,68,.12);color:var(--danger)}
.dlv-status.assigned,.dlv-status.picked_up,.dlv-status.in_transit{background:rgba(59,130,246,.12);color:var(--blue)}
.dlv-status.pending_d{background:rgba(245,158,11,.12);color:var(--warn)}
.dlv-bottom{padding:6px 12px;border-top:1px solid var(--border);display:flex;gap:8px;flex-wrap:wrap}
.dlv-meta{font-size:.6rem;color:var(--t3);display:flex;align-items:center;gap:3px}

/* PAGINATION */
.pag-wrap{display:flex;justify-content:center;gap:4px;padding:16px 12px}
.pag-btn{padding:6px 12px;border:1px solid var(--border);border-radius:8px;font-size:.7rem;background:var(--card);color:var(--t2);text-decoration:none}
.pag-btn.active{background:var(--drv);color:#fff;border-color:var(--drv)}

/* EMPTY */
.empty-state{text-align:center;padding:40px 20px}
.empty-icon{font-size:2.5rem;margin-bottom:8px}
.empty-text{font-size:.8rem;color:var(--t3);line-height:1.5}

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
        <div class="page-title">📦 Mes Livraisons</div>
    </header>

    <!-- SUMMARY -->
    <div class="summary-card">
        <div class="summary-grid">
            <div class="sum-item">
                <div class="sum-val">{{ $summary['total'] }}</div>
                <div class="sum-lbl">Livrées</div>
            </div>
            <div class="sum-item">
                <div class="sum-val green">{{ number_format($summary['earnings'], 2) }}€</div>
                <div class="sum-lbl">Gains</div>
            </div>
            <div class="sum-item">
                <div class="sum-val">{{ number_format($summary['distance'], 1) }} km</div>
                <div class="sum-lbl">Distance</div>
            </div>
            <div class="sum-item">
                <div class="sum-val">~{{ $summary['avg_time'] }} min</div>
                <div class="sum-lbl">Moy./Livr.</div>
            </div>
        </div>
    </div>

    <!-- FILTERS -->
    <div class="filter-bar">
        <a href="{{ route('driver.deliveries', ['status' => 'all', 'period' => $period]) }}" class="f-chip {{ $status === 'all' ? 'active' : '' }}">Toutes</a>
        <a href="{{ route('driver.deliveries', ['status' => 'delivered', 'period' => $period]) }}" class="f-chip {{ $status === 'delivered' ? 'active' : '' }}">✅ Livrées</a>
        <a href="{{ route('driver.deliveries', ['status' => 'assigned', 'period' => $period]) }}" class="f-chip {{ $status === 'assigned' ? 'active' : '' }}">📍 Assignées</a>
        <a href="{{ route('driver.deliveries', ['status' => 'in_transit', 'period' => $period]) }}" class="f-chip {{ $status === 'in_transit' ? 'active' : '' }}">🚗 En route</a>
        <a href="{{ route('driver.deliveries', ['status' => 'failed', 'period' => $period]) }}" class="f-chip {{ $status === 'failed' ? 'active' : '' }}">❌ Échouées</a>
    </div>
    <div class="period-bar">
        <a href="{{ route('driver.deliveries', ['status' => $status, 'period' => 'all']) }}" class="p-chip {{ $period === 'all' ? 'active' : '' }}">Tout</a>
        <a href="{{ route('driver.deliveries', ['status' => $status, 'period' => 'today']) }}" class="p-chip {{ $period === 'today' ? 'active' : '' }}">Aujourd'hui</a>
        <a href="{{ route('driver.deliveries', ['status' => $status, 'period' => 'week']) }}" class="p-chip {{ $period === 'week' ? 'active' : '' }}">7 jours</a>
        <a href="{{ route('driver.deliveries', ['status' => $status, 'period' => 'month']) }}" class="p-chip {{ $period === 'month' ? 'active' : '' }}">Ce mois</a>
    </div>

    <!-- LIST -->
    <div class="dlv-list">
        @forelse($deliveries as $order)
        <a href="{{ route('driver.deliveries.show', $order) }}" class="dlv-card" style="text-decoration:none;color:inherit">
            <div class="dlv-top">
                <div class="dlv-ico {{ $order->delivery_status === 'delivered' ? 'delivered' : ($order->delivery_status === 'failed' ? 'failed' : ($order->delivery_status === 'pending' ? 'pending' : 'active')) }}">
                    @if($order->delivery_status === 'delivered')✅
                    @elseif($order->delivery_status === 'failed')❌
                    @elseif($order->delivery_status === 'in_transit')🚗
                    @elseif($order->delivery_status === 'picked_up')📦
                    @elseif($order->delivery_status === 'assigned')📍
                    @else ⏳
                    @endif
                </div>
                <div class="dlv-info">
                    <div class="dlv-name">{{ $order->prestataire?->company_name ?? 'Restaurant #'.$order->id }}</div>
                    <div class="dlv-date">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div class="dlv-right">
                    <div class="dlv-amount {{ $order->delivery_status === 'delivered' ? 'green' : ($order->delivery_status === 'failed' ? 'red' : '') }}">
                        {{ $order->delivery_status !== 'failed' ? '+'.number_format($order->driver_commission ?? 0, 2).'€' : '0.00€' }}
                    </div>
                    <span class="dlv-status {{ $order->delivery_status }}">
                        @if($order->delivery_status === 'delivered') Livrée
                        @elseif($order->delivery_status === 'failed') Échouée
                        @elseif($order->delivery_status === 'in_transit') En route
                        @elseif($order->delivery_status === 'picked_up') Récupérée
                        @elseif($order->delivery_status === 'assigned') Assignée
                        @else En attente
                        @endif
                    </span>
                </div>
            </div>
            <div class="dlv-bottom">
                <span class="dlv-meta">📍 {{ Str::limit($order->delivery_address ?? '?', 25) }}</span>
                <span class="dlv-meta">📏 {{ $order->delivery_distance ?? '?' }} km</span>
                <span class="dlv-meta">⏱️ {{ $order->estimated_delivery_time ?? '?' }} min</span>
                <span class="dlv-meta">#{{ $order->id }}</span>
            </div>
        </a>
        @empty
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <p class="empty-text">Aucune livraison trouvée<br>pour ces filtres</p>
        </div>
        @endforelse
    </div>

    <!-- PAGINATION -->
    @if($deliveries->hasPages())
    <div class="pag-wrap">
        @if($deliveries->onFirstPage())
            <span class="pag-btn" style="opacity:.3">←</span>
        @else
            <a href="{{ $deliveries->previousPageUrl() }}" class="pag-btn">←</a>
        @endif
        <span class="pag-btn active">{{ $deliveries->currentPage() }}/{{ $deliveries->lastPage() }}</span>
        @if($deliveries->hasMorePages())
            <a href="{{ $deliveries->nextPageUrl() }}" class="pag-btn">→</a>
        @else
            <span class="pag-btn" style="opacity:.3">→</span>
        @endif
    </div>
    @endif
</div>

<!-- BOTTOM NAV -->
<nav class="bot-nav">
    <a href="{{ route('driver.dashboard') }}" class="nav-item"><span class="ico">🏠</span>Dashboard</a>
    <a href="{{ route('driver.map') }}" class="nav-item"><span class="ico">🗺️</span>Carte</a>
    <a href="{{ route('driver.deliveries') }}" class="nav-item active"><span class="ico">📦</span>Historique</a>
    <a href="{{ route('driver.tarifs') }}" class="nav-item"><span class="ico">💰</span>Tarifs</a>
    <a href="{{ route('driver.stats') }}" class="nav-item"><span class="ico">📊</span>Stats</a>
</nav>
@endsection
