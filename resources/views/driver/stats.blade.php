@extends('layouts.app')
@section('title', 'Statistiques Livreur')

@push('styles')
<style>
:root{--drv:#10b981;--drv-dark:#059669;--warn:#f59e0b;--danger:#ef4444;--blue:#3b82f6;--purple:#8b5cf6;--bg:#f8fafc;--card:#fff;--card2:#f3f4f6;--border:#e5e7eb;--t1:#1f2937;--t2:#6b7280;--t3:#9ca3af;--r:14px;--r-sm:10px}
[data-theme=dark]{--bg:#0a0a0a;--card:#141414;--card2:#1c1c1c;--border:#262626;--t1:#fafafa;--t2:#a3a3a3;--t3:#737373}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);font-family:'SF Pro Display',-apple-system,sans-serif;color:var(--t1)}
.drv-app{max-width:480px;margin:0 auto;min-height:100vh;padding-bottom:80px}

.page-head{background:linear-gradient(135deg,var(--purple),#6d28d9);padding:14px 16px;display:flex;align-items:center;gap:10px;position:sticky;top:0;z-index:50}
.page-back{width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-size:1rem}
.page-title{font-size:1rem;font-weight:700;color:#fff;flex:1}

/* ALL TIME HERO */
.hero-stats{margin:10px 12px;background:linear-gradient(135deg,var(--drv),var(--drv-dark));border-radius:var(--r);padding:20px;color:#fff;text-align:center}
.hero-big{font-size:2.2rem;font-weight:800}
.hero-lbl{font-size:.7rem;opacity:.8;margin-top:2px}
.hero-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:14px}
.hero-item{background:rgba(255,255,255,.15);border-radius:10px;padding:10px 4px}
.hero-item .val{font-size:.9rem;font-weight:800}
.hero-item .lbl{font-size:.5rem;opacity:.8;margin-top:2px}

/* WEEKLY CHART */
.chart-box{margin:0 12px 10px;background:var(--card);border-radius:var(--r);border:1px solid var(--border);overflow:hidden}
.chart-header{padding:12px;font-size:.75rem;font-weight:700;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:5px}
.chart-body{padding:12px}
.bar-chart{display:flex;align-items:flex-end;gap:6px;height:120px;padding-top:10px}
.bar-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:4px}
.bar-fill{width:100%;border-radius:6px 6px 0 0;transition:height .5s;min-height:2px}
.bar-fill.green{background:linear-gradient(var(--drv),var(--drv-dark))}
.bar-fill.blue{background:linear-gradient(var(--blue),#2563eb)}
.bar-val{font-size:.55rem;font-weight:700;color:var(--t2)}
.bar-lbl{font-size:.5rem;color:var(--t3)}
.chart-legend{display:flex;gap:12px;padding:8px 12px;border-top:1px solid var(--border);font-size:.6rem;color:var(--t2)}
.legend-dot{width:8px;height:8px;border-radius:3px;display:inline-block;margin-right:4px}

/* MONTH SUMMARY */
.month-card{margin:0 12px 10px;background:var(--card);border-radius:var(--r);border:1px solid var(--border)}
.month-header{padding:12px;font-size:.75rem;font-weight:700;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:5px}
.month-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1px;background:var(--border)}
.month-cell{background:var(--card);padding:14px 12px;text-align:center}
.month-cell .val{font-size:1.1rem;font-weight:800}
.month-cell .val.green{color:var(--drv)}
.month-cell .lbl{font-size:.55rem;color:var(--t3);text-transform:uppercase;margin-top:3px}

/* PEAK HOURS */
.peak-box{margin:0 12px 10px;background:var(--card);border-radius:var(--r);border:1px solid var(--border)}
.peak-header{padding:12px;font-size:.75rem;font-weight:700;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:5px}
.peak-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:3px;padding:12px}
.peak-cell{text-align:center;padding:8px 2px;border-radius:8px;font-size:.55rem;font-weight:600}
.peak-0{background:var(--card2);color:var(--t3)}
.peak-1{background:rgba(16,185,129,.1);color:var(--drv)}
.peak-2{background:rgba(16,185,129,.25);color:var(--drv)}
.peak-3{background:rgba(16,185,129,.5);color:#fff}
.peak-4{background:rgba(16,185,129,.75);color:#fff}
.peak-5{background:var(--drv);color:#fff}

/* TOP RESTAURANTS */
.top-box{margin:0 12px 10px;background:var(--card);border-radius:var(--r);border:1px solid var(--border)}
.top-header{padding:12px;font-size:.75rem;font-weight:700;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:5px}
.top-row{display:flex;align-items:center;gap:10px;padding:10px 12px;border-bottom:1px solid var(--border)}
.top-row:last-child{border:none}
.top-rank{width:26px;height:26px;border-radius:8px;background:var(--card2);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.7rem;color:var(--t2)}
.top-rank.gold{background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff}
.top-rank.silver{background:linear-gradient(135deg,#9ca3af,#6b7280);color:#fff}
.top-rank.bronze{background:linear-gradient(135deg,#d97706,#92400e);color:#fff}
.top-info{flex:1}
.top-name{font-size:.75rem;font-weight:700}
.top-count{font-size:.6rem;color:var(--t3);margin-top:1px}
.top-earn{font-size:.8rem;font-weight:700;color:var(--drv)}

/* RATE */
.rate-card{margin:0 12px 10px;background:var(--card);border-radius:var(--r);border:1px solid var(--border);padding:16px;text-align:center}
.rate-stars{font-size:1.5rem;letter-spacing:4px}
.rate-val{font-size:1.4rem;font-weight:800;margin-top:4px}
.rate-lbl{font-size:.65rem;color:var(--t3)}
.rate-bar{height:8px;background:var(--card2);border-radius:99px;margin-top:8px;overflow:hidden}
.rate-fill{height:100%;border-radius:99px;transition:width .5s}
.rate-fill.good{background:var(--drv)}
.rate-fill.mid{background:var(--warn)}
.rate-fill.bad{background:var(--danger)}

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
        <div class="page-title">📊 Mes Statistiques</div>
    </header>

    <!-- ALL-TIME HERO -->
    <div class="hero-stats">
        <div class="hero-big">{{ number_format($allTimeStats['total_earnings'], 2) }}€</div>
        <div class="hero-lbl">Gains totaux</div>
        <div class="hero-grid">
            <div class="hero-item">
                <div class="val">{{ $allTimeStats['total_deliveries'] }}</div>
                <div class="lbl">Livraisons</div>
            </div>
            <div class="hero-item">
                <div class="val">{{ $allTimeStats['success_rate'] }}%</div>
                <div class="lbl">Réussite</div>
            </div>
            <div class="hero-item">
                <div class="val">{{ $allTimeStats['failed'] }}</div>
                <div class="lbl">Échouées</div>
            </div>
            <div class="hero-item">
                <div class="val">{{ number_format($allTimeStats['avg_rating'], 1) }}</div>
                <div class="lbl">Note moy.</div>
            </div>
        </div>
    </div>

    <!-- RATING -->
    <div class="rate-card">
        @php
            $rating = $allTimeStats['avg_rating'];
            $stars = str_repeat('⭐', (int)round($rating)) . str_repeat('☆', 5 - (int)round($rating));
            $rateClass = $rating >= 4 ? 'good' : ($rating >= 3 ? 'mid' : 'bad');
        @endphp
        <div class="rate-stars">{{ $stars }}</div>
        <div class="rate-val">{{ number_format($rating, 1) }}/5</div>
        <div class="rate-lbl">Note moyenne globale</div>
        <div class="rate-bar"><div class="rate-fill {{ $rateClass }}" style="width:{{ ($rating/5)*100 }}%"></div></div>
    </div>

    <!-- WEEKLY CHART -->
    <div class="chart-box">
        <div class="chart-header">📈 Cette semaine</div>
        <div class="chart-body">
            @php
                $maxCount = $weeklyStats->max('count') ?: 1;
                $maxEarn = $weeklyStats->max('earnings') ?: 1;
                $days = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
                $weekData = [];
                for($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i)->format('Y-m-d');
                    $dayName = $days[now()->subDays($i)->dayOfWeekIso - 1];
                    $found = $weeklyStats->firstWhere('date', $date);
                    $weekData[] = [
                        'day' => $dayName,
                        'count' => $found ? $found->count : 0,
                        'earnings' => $found ? $found->earnings : 0,
                    ];
                }
            @endphp
            <div class="bar-chart">
                @foreach($weekData as $d)
                <div class="bar-col">
                    <div class="bar-val">{{ $d['count'] }}</div>
                    <div class="bar-fill green" style="height:{{ max(2, ($d['count']/$maxCount)*80) }}px"></div>
                    <div class="bar-lbl">{{ $d['day'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="chart-legend">
            <span><span class="legend-dot" style="background:var(--drv)"></span>Livraisons</span>
            <span>Total semaine: {{ $weeklyStats->sum('count') }} livr. — {{ number_format($weeklyStats->sum('earnings'), 2) }}€</span>
        </div>
    </div>

    <!-- MONTHLY SUMMARY -->
    <div class="month-card">
        <div class="month-header">📅 Ce mois</div>
        <div class="month-grid">
            <div class="month-cell">
                <div class="val">{{ $monthlyStats->total ?? 0 }}</div>
                <div class="lbl">Livraisons</div>
            </div>
            <div class="month-cell">
                <div class="val green">{{ number_format($monthlyStats->earnings ?? 0, 2) }}€</div>
                <div class="lbl">Gains</div>
            </div>
            <div class="month-cell">
                <div class="val">{{ number_format($monthlyStats->distance ?? 0, 1) }} km</div>
                <div class="lbl">Distance</div>
            </div>
            <div class="month-cell">
                <div class="val">~{{ (int)($monthlyStats->avg_time ?? 0) }} min</div>
                <div class="lbl">Temps moyen</div>
            </div>
        </div>
    </div>

    <!-- PEAK HOURS HEATMAP -->
    <div class="peak-box">
        <div class="peak-header">🕐 Heures de pointe (30 jours)</div>
        <div class="peak-grid">
            @php
                $maxPeak = $peakHours->max('count') ?: 1;
                $peakMap = $peakHours->keyBy('hour');
            @endphp
            @for($h = 8; $h <= 23; $h++)
                @php
                    $count = $peakMap->has($h) ? $peakMap[$h]->count : 0;
                    $level = $maxPeak > 0 ? min(5, (int)ceil(($count / $maxPeak) * 5)) : 0;
                @endphp
                <div class="peak-cell peak-{{ $level }}">
                    <div style="font-size:.65rem;font-weight:800">{{ $h }}h</div>
                    <div style="font-size:.5rem;margin-top:2px">{{ $count }}</div>
                    @if($peakMap->has($h))
                        <div style="font-size:.45rem;margin-top:1px">{{ number_format($peakMap[$h]->earnings, 0) }}€</div>
                    @endif
                </div>
            @endfor
        </div>
        <div style="padding:6px 12px;font-size:.55rem;color:var(--t3);text-align:center">
            💡 Les heures vertes = plus de livraisons = plus de gains
        </div>
    </div>

    <!-- TOP RESTAURANTS -->
    @if($topRestaurants->count() > 0)
    <div class="top-box">
        <div class="top-header">🏆 Top Restaurants</div>
        @foreach($topRestaurants as $i => $rest)
        <div class="top-row">
            <div class="top-rank {{ $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : '')) }}">
                {{ $i + 1 }}
            </div>
            <div class="top-info">
                <div class="top-name">{{ $rest->prestataire?->company_name ?? 'Restaurant' }}</div>
                <div class="top-count">{{ $rest->count }} livraisons</div>
            </div>
            <div class="top-earn">+{{ number_format($rest->earnings, 2) }}€</div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<!-- BOTTOM NAV -->
<nav class="bot-nav">
    <a href="{{ route('driver.dashboard') }}" class="nav-item"><span class="ico">🏠</span>Dashboard</a>
    <a href="{{ route('driver.map') }}" class="nav-item"><span class="ico">🗺️</span>Carte</a>
    <a href="{{ route('driver.deliveries') }}" class="nav-item"><span class="ico">📦</span>Historique</a>
    <a href="{{ route('driver.tarifs') }}" class="nav-item"><span class="ico">💰</span>Tarifs</a>
    <a href="{{ route('driver.stats') }}" class="nav-item active"><span class="ico">📊</span>Stats</a>
</nav>
@endsection
