@extends('layouts.app')
@section('title', 'Livraison #' . $foodOrder->id)

@push('styles')
<style>
:root{--drv:#10b981;--drv-dark:#059669;--warn:#f59e0b;--danger:#ef4444;--blue:#3b82f6;--purple:#8b5cf6;--bg:#f8fafc;--card:#fff;--card2:#f3f4f6;--border:#e5e7eb;--t1:#1f2937;--t2:#6b7280;--t3:#9ca3af;--r:14px;--r-sm:10px}
[data-theme=dark]{--bg:#0a0a0a;--card:#141414;--card2:#1c1c1c;--border:#262626;--t1:#fafafa;--t2:#a3a3a3;--t3:#737373}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);font-family:'SF Pro Display',-apple-system,sans-serif;color:var(--t1)}
.drv-app{max-width:480px;margin:0 auto;min-height:100vh;padding-bottom:30px}

.page-head{background:linear-gradient(135deg,var(--drv),var(--drv-dark));padding:14px 16px;display:flex;align-items:center;gap:10px;position:sticky;top:0;z-index:50}
.page-back{width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;font-size:1rem}
.page-title{font-size:1rem;font-weight:700;color:#fff;flex:1}
.page-badge{padding:4px 10px;border-radius:8px;font-size:.65rem;font-weight:700;color:#fff}
.page-badge.delivered{background:rgba(255,255,255,.3)}
.page-badge.failed{background:rgba(239,68,68,.8)}
.page-badge.active{background:rgba(59,130,246,.8)}

/* MAP PREVIEW */
.map-preview{height:180px;background:var(--card2);position:relative;overflow:hidden}
.map-preview img{width:100%;height:100%;object-fit:cover}
.map-overlay{position:absolute;bottom:0;left:0;right:0;padding:8px 12px;background:linear-gradient(transparent,rgba(0,0,0,.7));color:#fff;font-size:.7rem;display:flex;justify-content:space-between}

/* TIMELINE */
.timeline{padding:16px;background:var(--card);margin:10px 12px;border-radius:var(--r);border:1px solid var(--border)}
.tl-title{font-size:.75rem;font-weight:700;margin-bottom:12px;display:flex;align-items:center;gap:5px}
.tl-item{display:flex;gap:10px;padding-bottom:14px;position:relative}
.tl-item:last-child{padding-bottom:0}
.tl-dot-wrap{display:flex;flex-direction:column;align-items:center}
.tl-dot{width:12px;height:12px;border-radius:50%;border:2px solid var(--border);background:var(--card);z-index:1;flex-shrink:0}
.tl-dot.completed{background:var(--drv);border-color:var(--drv)}
.tl-dot.current{background:var(--blue);border-color:var(--blue);box-shadow:0 0 8px rgba(59,130,246,.5)}
.tl-dot.failed{background:var(--danger);border-color:var(--danger)}
.tl-line{width:2px;flex:1;background:var(--border);margin:2px 0}
.tl-line.completed{background:var(--drv)}
.tl-content{flex:1;padding-top:0}
.tl-label{font-size:.75rem;font-weight:600}
.tl-time{font-size:.6rem;color:var(--t3);margin-top:1px}
.tl-desc{font-size:.65rem;color:var(--t2);margin-top:2px}

/* DETAILS */
.detail-section{background:var(--card);margin:0 12px 10px;border-radius:var(--r);border:1px solid var(--border);overflow:hidden}
.detail-header{padding:12px;font-size:.75rem;font-weight:700;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:5px}
.detail-row{display:flex;justify-content:space-between;padding:8px 12px;border-bottom:1px solid var(--border)}
.detail-row:last-child{border:none}
.detail-lbl{font-size:.7rem;color:var(--t3)}
.detail-val{font-size:.7rem;font-weight:600;text-align:right;max-width:60%}

/* ITEMS */
.item-row{display:flex;align-items:center;gap:8px;padding:8px 12px;border-bottom:1px solid var(--border)}
.item-row:last-child{border:none}
.item-qty{width:24px;height:24px;border-radius:6px;background:var(--card2);display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:var(--drv)}
.item-name{flex:1;font-size:.75rem}
.item-price{font-size:.75rem;font-weight:600}

/* ACTIONS */
.action-section{margin:0 12px 10px;display:flex;gap:8px}
.act-btn{flex:1;padding:12px;border:none;border-radius:12px;font-weight:700;font-size:.8rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;transition:.15s}
.act-btn:active{transform:scale(.97)}
.act-btn.gps{background:var(--blue);color:#fff}
.act-btn.pickup{background:var(--drv);color:#fff}
.act-btn.transit{background:var(--purple);color:#fff}
.act-btn.deliver{background:linear-gradient(135deg,var(--drv),#06b6d4);color:#fff}
.act-btn.problem{background:#fee2e2;color:#ef4444;border:1px solid #fecaca}

/* EARNING CARD */
.earning-card{margin:0 12px 10px;background:linear-gradient(135deg,var(--drv),var(--drv-dark));border-radius:var(--r);padding:16px;color:#fff;text-align:center}
.earn-amount{font-size:1.8rem;font-weight:800}
.earn-label{font-size:.7rem;opacity:.8;margin-top:2px}
.earn-breakdown{display:flex;justify-content:center;gap:16px;margin-top:10px;font-size:.65rem;opacity:.9}
</style>
@endpush

@section('content')
<div class="drv-app">
    <header class="page-head">
        <a href="{{ route('driver.deliveries') }}" class="page-back">←</a>
        <div class="page-title">Livraison #{{ $foodOrder->id }}</div>
        <span class="page-badge {{ $foodOrder->delivery_status === 'delivered' ? 'delivered' : ($foodOrder->delivery_status === 'failed' ? 'failed' : 'active') }}">
            @if($foodOrder->delivery_status === 'delivered') ✅ Livrée
            @elseif($foodOrder->delivery_status === 'failed') ❌ Échouée
            @elseif($foodOrder->delivery_status === 'in_transit') 🚗 En route
            @elseif($foodOrder->delivery_status === 'picked_up') 📦 Récupérée
            @elseif($foodOrder->delivery_status === 'assigned') 📍 Assignée
            @else ⏳ En attente
            @endif
        </span>
    </header>

    <!-- MAP PREVIEW -->
    @php
        $pLat = $foodOrder->prestataire->latitude ?? 48.8566;
        $pLng = $foodOrder->prestataire->longitude ?? 2.3522;
        $dLat = $foodOrder->delivery_lat ?? 48.8600;
        $dLng = $foodOrder->delivery_lng ?? 2.3600;
        $gmKey = config('services.google_maps.key', env('GOOGLE_MAPS_API_KEY', ''));
        $mapUrl = "https://maps.googleapis.com/maps/api/staticmap?size=480x180&scale=2&markers=color:blue|label:P|{$pLat},{$pLng}&markers=color:green|label:D|{$dLat},{$dLng}&path=color:0x3b82f6ff|weight:3|{$pLat},{$pLng}|{$dLat},{$dLng}&key={$gmKey}";
    @endphp
    <div class="map-preview">
        @if($gmKey)
            <img src="{{ $mapUrl }}" alt="Carte" loading="lazy">
        @else
            <div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:2rem">🗺️</div>
        @endif
        <div class="map-overlay">
            <span>📏 {{ $foodOrder->delivery_distance ?? '?' }} km</span>
            <span>⏱️ {{ $foodOrder->estimated_delivery_time ?? '?' }} min</span>
        </div>
    </div>

    <!-- EARNING CARD (if delivered) -->
    @if($foodOrder->delivery_status === 'delivered')
    <div class="earning-card">
        <div class="earn-amount">+{{ number_format($foodOrder->driver_commission ?? 0, 2) }}€</div>
        <div class="earn-label">Gains pour cette livraison</div>
        <div class="earn-breakdown">
            <span>📏 {{ $foodOrder->delivery_distance ?? 0 }} km</span>
            <span>⏱️ {{ $foodOrder->estimated_delivery_time ?? 0 }} min</span>
            <span>💰 Total cmd: {{ number_format($foodOrder->total ?? 0, 2) }}€</span>
        </div>
    </div>
    @endif

    <!-- ACTIVE ACTIONS -->
    @if(!in_array($foodOrder->delivery_status, ['delivered', 'failed']))
    <div class="action-section">
        <a href="{{ route('driver.navigate', $foodOrder) }}" class="act-btn gps">🧭 GPS</a>
        @if($foodOrder->delivery_status === 'assigned')
            <button class="act-btn pickup" onclick="doAction('pickup')">✅ Récupérée</button>
        @elseif($foodOrder->delivery_status === 'picked_up')
            <button class="act-btn transit" onclick="doAction('start')">🚗 En route</button>
        @elseif($foodOrder->delivery_status === 'in_transit')
            <button class="act-btn deliver" onclick="doAction('deliver')">🎉 Livrée</button>
        @endif
    </div>
    <div style="padding:0 12px 10px"><button class="act-btn problem" style="width:100%" onclick="doReport()">⚠️ Signaler un problème</button></div>
    @endif

    <!-- TIMELINE -->
    <div class="timeline">
        <div class="tl-title">📋 Chronologie</div>
        @foreach($timeline as $i => $step)
        <div class="tl-item">
            <div class="tl-dot-wrap">
                <div class="tl-dot {{ $step['status'] === 'done' ? 'completed' : ($step['status'] === 'failed' ? 'failed' : 'current') }}"></div>
                @if(!$loop->last)<div class="tl-line {{ $step['status'] === 'done' ? 'completed' : '' }}"></div>@endif
            </div>
            <div class="tl-content">
                <div class="tl-label">{{ $step['icon'] ?? '' }} {{ $step['label'] }}</div>
                @if($step['time'])<div class="tl-time">{{ $step['time'] instanceof \Carbon\Carbon ? $step['time']->format('d/m/Y H:i') : $step['time'] }}</div>@endif
                @if($step['description'] ?? null)<div class="tl-desc">{{ $step['description'] }}</div>@endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- ROUTE DETAILS -->
    <div class="detail-section">
        <div class="detail-header">📍 Itinéraire</div>
        <div class="detail-row">
            <span class="detail-lbl">Récupération</span>
            <span class="detail-val">{{ $foodOrder->prestataire?->company_name ?? 'Restaurant' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Adresse resto</span>
            <span class="detail-val">{{ Str::limit($foodOrder->prestataire?->address ?? '?', 30) }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Livraison</span>
            <span class="detail-val">{{ Str::limit($foodOrder->delivery_address ?? '?', 30) }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Client</span>
            <span class="detail-val">{{ $foodOrder->client?->name ?? 'Client' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Distance</span>
            <span class="detail-val">{{ $foodOrder->delivery_distance ?? '?' }} km</span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Temps estimé</span>
            <span class="detail-val">~{{ $foodOrder->estimated_delivery_time ?? '?' }} min</span>
        </div>
    </div>

    <!-- ORDER ITEMS -->
    @if($foodOrder->items && $foodOrder->items->count() > 0)
    <div class="detail-section">
        <div class="detail-header">🛍️ Articles ({{ $foodOrder->items->count() }})</div>
        @foreach($foodOrder->items as $item)
        <div class="item-row">
            <div class="item-qty">{{ $item->quantity ?? 1 }}</div>
            <div class="item-name">{{ $item->product_name ?? $item->foodProduct?->name ?? 'Article' }}</div>
            <div class="item-price">{{ number_format((float) ($item->total_price ?? (($item->unit_price ?? 0) * ($item->quantity ?? 1))), 2) }}€</div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- FINANCIAL -->
    <div class="detail-section">
        <div class="detail-header">💰 Détails financiers</div>
        <div class="detail-row">
            <span class="detail-lbl">Total commande</span>
            <span class="detail-val">{{ number_format($foodOrder->total ?? 0, 2) }}€</span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Frais livraison</span>
            <span class="detail-val">{{ number_format($foodOrder->delivery_fee ?? 0, 2) }}€</span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Ma commission</span>
            <span class="detail-val" style="color:var(--drv);font-weight:800">+{{ number_format($foodOrder->driver_commission ?? 0, 2) }}€</span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Paiement</span>
            <span class="detail-val">{{ ($foodOrder->payment_method ?? 'card') === 'cash' ? '💵 Espèces' : '💳 Carte' }}</span>
        </div>
    </div>

    <!-- INFO -->
    <div class="detail-section">
        <div class="detail-header">ℹ️ Informations</div>
        <div class="detail-row">
            <span class="detail-lbl">Commande</span>
            <span class="detail-val">#{{ $foodOrder->id }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Créée le</span>
            <span class="detail-val">{{ $foodOrder->created_at->format('d/m/Y H:i') }}</span>
        </div>
        @if($foodOrder->delivered_at)
        <div class="detail-row">
            <span class="detail-lbl">Livrée le</span>
            <span class="detail-val">{{ \Carbon\Carbon::parse($foodOrder->delivered_at)->format('d/m/Y H:i') }}</span>
        </div>
        @endif
        @if($foodOrder->delivery_notes)
        <div class="detail-row">
            <span class="detail-lbl">Notes</span>
            <span class="detail-val">{{ $foodOrder->delivery_notes }}</span>
        </div>
        @endif
    </div>
</div>

<script>
const csrf='{{ csrf_token() }}';
async function doAction(type){
    let url,body={};
    if(type==='pickup')url='/driver/deliveries/{{ $foodOrder->id }}/pickup';
    else if(type==='start')url='/driver/deliveries/{{ $foodOrder->id }}/start';
    else if(type==='deliver'){
        const code=prompt('Code de confirmation client (4 chiffres) :');
        if(!code)return;
        if(!/^\d{4}$/.test(code)){alert('Code invalide');return;}
        body.delivery_code=code;
        url='/driver/deliveries/{{ $foodOrder->id }}/deliver';
    }
    try{
        const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(body)});
        const d=await r.json();
        if(d.success){if(type==='deliver')alert(`Livrée ! 🎉 +${d.earnings??0}€`);location.reload();}
        else alert(d.message||'Erreur');
    }catch(e){alert('Erreur')}
}
async function doReport(){
    const reason=prompt('Décrivez le problème :');
    if(!reason)return;
    try{
        const r=await fetch('/driver/deliveries/{{ $foodOrder->id }}/problem',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({reason})});
        const d=await r.json();
        if(d.success){alert('Problème signalé');location.reload();}
    }catch(e){alert('Erreur')}
}
</script>
@endsection
