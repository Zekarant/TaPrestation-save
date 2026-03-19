@extends('layouts.app')
@section('title', 'Navigation – Livraison #' . $foodOrder->id)

@push('styles')
<style>
:root{--drv:#10b981;--drv-dark:#059669;--blue:#3b82f6;--purple:#8b5cf6;--warn:#f59e0b;--bg:#f8fafc;--card:#fff;--card2:#f3f4f6;--border:#e5e7eb;--t1:#1f2937;--t2:#6b7280;--t3:#9ca3af;--r:14px;--r-sm:10px}
[data-theme=dark]{--bg:#0a0a0a;--card:#141414;--card2:#1c1c1c;--border:#262626;--t1:#fafafa;--t2:#a3a3a3;--t3:#737373}
*{box-sizing:border-box;margin:0;padding:0}body{font-family:'SF Pro Display',-apple-system,sans-serif;color:var(--t1)}

.nav-wrap{position:relative;width:100%;height:100vh;overflow:hidden}
#navMap{width:100%;height:100%}

/* TOP NAV BAR */
.nav-topbar{position:absolute;top:0;left:0;right:0;z-index:10;padding:10px 12px;display:flex;align-items:center;gap:8px}
.nav-back{width:40px;height:40px;border-radius:12px;background:var(--card);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:1.1rem;cursor:pointer;text-decoration:none;color:var(--t1);box-shadow:0 2px 10px rgba(0,0,0,.15)}
.nav-info-bar{flex:1;background:var(--card);border-radius:12px;padding:8px 12px;box-shadow:0 2px 10px rgba(0,0,0,.15);display:flex;align-items:center;justify-content:space-between}
.nav-order-info .label{font-size:.55rem;color:var(--t3);text-transform:uppercase;letter-spacing:.3px}
.nav-order-info .value{font-size:.85rem;font-weight:700}
.nav-eta{text-align:right}
.nav-eta .eta-val{font-size:1rem;font-weight:800;color:var(--drv)}
.nav-eta .eta-lbl{font-size:.55rem;color:var(--t3)}

/* INSTRUCTION PANEL */
.instruction-panel{position:absolute;top:68px;left:12px;right:12px;z-index:10;background:var(--card);border-radius:12px;padding:10px 14px;box-shadow:0 2px 12px rgba(0,0,0,.15);display:none}
.instruction-panel.visible{display:flex;gap:10px;align-items:center}
.instr-icon{font-size:1.5rem;min-width:40px;text-align:center}
.instr-text{font-size:.8rem;font-weight:600;line-height:1.3}
.instr-dist{font-size:.65rem;color:var(--t3);margin-top:2px}

/* BOTTOM ACTION */
.nav-bottom{position:absolute;bottom:0;left:0;right:0;z-index:20;background:var(--card);border-radius:16px 16px 0 0;box-shadow:0 -4px 20px rgba(0,0,0,.15);padding:14px 16px calc(14px + env(safe-area-inset-bottom,0px))}
.nav-dest{display:flex;align-items:center;gap:10px;margin-bottom:10px}
.nav-dest-ico{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.3rem}
.nav-dest-ico.pickup{background:rgba(59,130,246,.15);color:var(--blue)}
.nav-dest-ico.delivery{background:rgba(16,185,129,.15);color:var(--drv)}
.nav-dest-info{flex:1}
.nav-dest-lbl{font-size:.6rem;text-transform:uppercase;color:var(--t3);letter-spacing:.3px}
.nav-dest-name{font-size:.85rem;font-weight:700}
.nav-dest-addr{font-size:.7rem;color:var(--t2);margin-top:1px}
.nav-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:10px}
.nav-stat{text-align:center;background:var(--card2);border-radius:8px;padding:8px}
.nav-stat .val{font-size:.9rem;font-weight:800}
.nav-stat .lbl{font-size:.5rem;color:var(--t3);text-transform:uppercase;margin-top:1px}
.nav-actions{display:flex;gap:8px}
.nav-btn{flex:1;padding:12px;border:none;border-radius:12px;font-weight:700;font-size:.8rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;transition:.15s}
.nav-btn:active{transform:scale(.97)}
.nav-btn.primary{background:var(--drv);color:#fff}
.nav-btn.secondary{background:var(--card2);color:var(--t1);border:1px solid var(--border)}
.nav-btn.call{background:var(--blue);color:#fff}
.nav-btn.ext{background:var(--purple);color:#fff}
.nav-btn.danger{background:#fee2e2;color:#ef4444;border:1px solid #fecaca}

/* Pulse ring on marker */
@keyframes navPulse{0%{transform:scale(1);opacity:.6}100%{transform:scale(2.5);opacity:0}}
</style>
@endpush

@section('content')
<div class="nav-wrap">
    <div id="navMap"></div>

    <!-- TOP BAR -->
    <div class="nav-topbar">
        <a href="{{ route('driver.map') }}" class="nav-back">←</a>
        <div class="nav-info-bar">
            <div class="nav-order-info">
                <div class="label">Commande #{{ $foodOrder->id }}</div>
                <div class="value">
                    @if($waypoint['type'] === 'pickup') Récupération @else Livraison @endif
                </div>
            </div>
            <div class="nav-eta">
                <div class="eta-val" id="etaValue">--</div>
                <div class="eta-lbl">min restantes</div>
            </div>
        </div>
    </div>

    <!-- STEP INSTRUCTION -->
    <div class="instruction-panel" id="instrPanel">
        <div class="instr-icon" id="instrIcon">↑</div>
        <div>
            <div class="instr-text" id="instrText">Calcul de l'itinéraire...</div>
            <div class="instr-dist" id="instrDist"></div>
        </div>
    </div>

    <!-- BOTTOM -->
    <div class="nav-bottom">
        <div class="nav-dest">
            <div class="nav-dest-ico {{ $waypoint['type'] }}">
                {{ $waypoint['type'] === 'pickup' ? '📍' : '🏠' }}
            </div>
            <div class="nav-dest-info">
                <div class="nav-dest-lbl">{{ $waypoint['type'] === 'pickup' ? 'Récupération' : 'Livraison' }}</div>
                <div class="nav-dest-name">{{ $waypoint['name'] }}</div>
                <div class="nav-dest-addr">{{ $waypoint['address'] }}</div>
            </div>
        </div>

        <div class="nav-stats">
            <div class="nav-stat"><div class="val" id="distVal">--</div><div class="lbl">Km restant</div></div>
            <div class="nav-stat"><div class="val" id="timeVal">--</div><div class="lbl">Minutes</div></div>
            <div class="nav-stat"><div class="val">+{{ number_format($foodOrder->driver_commission ?? 2, 2) }}€</div><div class="lbl">Gain</div></div>
        </div>

        <div class="nav-actions">
            @if($waypoint['type'] === 'pickup')
                <button class="nav-btn secondary" onclick="openExtNav()">🗺️ Ext.</button>
                <button class="nav-btn call" onclick="callPlace()">📞</button>
                <button class="nav-btn primary" onclick="confirmPickup()">✅ Récupérée</button>
            @else
                <button class="nav-btn secondary" onclick="openExtNav()">🗺️ Ext.</button>
                <button class="nav-btn call" onclick="callClient()">📞</button>
                @if($foodOrder->delivery_status === 'picked_up')
                    <button class="nav-btn ext" onclick="confirmTransit()">🚗 En route</button>
                @else
                    <button class="nav-btn primary" onclick="confirmDeliver()">🎉 Livrée</button>
                @endif
            @endif
        </div>

        <div style="margin-top:8px;text-align:center">
            <button class="nav-btn danger" style="flex:none;padding:8px 16px;font-size:.7rem" onclick="reportProblem()">⚠️ Signaler un problème</button>
        </div>
    </div>
</div>

<script>
let map,driverMarker,destMarker,directionsService,directionsRenderer,currentLeg=null;
const csrf='{{ csrf_token() }}';
const dest={lat:{{ $waypoint['lat'] }},lng:{{ $waypoint['lng'] }}};
const driverPos={lat:{{ $driver->current_lat ?? 48.8566 }},lng:{{ $driver->current_lng ?? 2.3522 }}};

function initMap(){
    map=new google.maps.Map(document.getElementById('navMap'),{
        center:driverPos,zoom:15,disableDefaultUI:true,
        styles:[
            {featureType:'poi',elementType:'labels',stylers:[{visibility:'off'}]},
            {featureType:'transit',stylers:[{visibility:'simplified'}]}
        ],
        gestureHandling:'greedy'
    });

    driverMarker=new google.maps.Marker({
        position:driverPos,map,
        icon:{path:google.maps.SymbolPath.FORWARD_CLOSED_ARROW,scale:7,fillColor:'#10b981',fillOpacity:1,strokeWeight:2,strokeColor:'#fff',rotation:0},
        zIndex:100
    });

    destMarker=new google.maps.Marker({
        position:dest,map,
        icon:{url:`data:image/svg+xml;charset=UTF-8,${encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" width="36" height="44" viewBox="0 0 36 44"><path d="M18 0C8 0 0 8 0 18c0 14 18 26 18 26s18-12 18-26C36 8 28 0 18 0z" fill="${'{{ $waypoint["type"] }}'==='pickup'?'#3b82f6':'#10b981'}"/><circle cx="18" cy="16" r="8" fill="#fff"/><text x="18" y="20" text-anchor="middle" font-size="14" fill="${'{{ $waypoint["type"] }}'==='pickup'?'#3b82f6':'#10b981'}">${'{{ $waypoint["type"] }}'==='pickup'?'P':'🏠'}</text></svg>`)}`,scaledSize:new google.maps.Size(36,44)},
        zIndex:50
    });

    directionsService=new google.maps.DirectionsService();
    directionsRenderer=new google.maps.DirectionsRenderer({
        map,suppressMarkers:true,
        polylineOptions:{strokeColor:'#3b82f6',strokeWeight:5,strokeOpacity:.8}
    });

    calculateRoute();

    // GPS tracking
    if('geolocation' in navigator){
        navigator.geolocation.watchPosition(p=>{
            const pos={lat:p.coords.latitude,lng:p.coords.longitude};
            driverMarker.setPosition(pos);
            if(p.coords.heading)driverMarker.setIcon({...driverMarker.getIcon(),rotation:p.coords.heading});

            fetch('{{ route("driver.update-location") }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({lat:pos.lat,lng:pos.lng})});

            updateETA(pos);
        },()=>{},{enableHighAccuracy:true,timeout:8000,maximumAge:3000});
    }
}

function calculateRoute(){
    directionsService.route({origin:driverMarker.getPosition(),destination:dest,travelMode:google.maps.TravelMode.DRIVING},
    (result,status)=>{
        if(status==='OK'){
            directionsRenderer.setDirections(result);
            const leg=result.routes[0].legs[0];
            currentLeg=leg;
            document.getElementById('distVal').textContent=(leg.distance.value/1000).toFixed(1);
            document.getElementById('timeVal').textContent=Math.round(leg.duration.value/60);
            document.getElementById('etaValue').textContent=Math.round(leg.duration.value/60);

            // Show first instruction
            if(leg.steps.length>0){
                const step=leg.steps[0];
                document.getElementById('instrText').innerHTML=step.instructions;
                document.getElementById('instrDist').textContent=step.distance.text;
                document.getElementById('instrIcon').textContent=getManeuverIcon(step.maneuver);
                document.getElementById('instrPanel').classList.add('visible');
            }

            // Fit bounds
            const bounds=new google.maps.LatLngBounds();
            bounds.extend(driverMarker.getPosition());
            bounds.extend(dest);
            map.fitBounds(bounds,{top:120,bottom:280,left:30,right:30});
        }
    });
}

function updateETA(pos){
    const service=new google.maps.DistanceMatrixService();
    service.getDistanceMatrix({
        origins:[pos],destinations:[dest],travelMode:google.maps.TravelMode.DRIVING
    },(r,s)=>{
        if(s==='OK'&&r.rows[0].elements[0].status==='OK'){
            const el=r.rows[0].elements[0];
            document.getElementById('distVal').textContent=(el.distance.value/1000).toFixed(1);
            document.getElementById('timeVal').textContent=Math.round(el.duration.value/60);
            document.getElementById('etaValue').textContent=Math.round(el.duration.value/60);
        }
    });
}

function getManeuverIcon(m){
    const icons={'turn-left':'↰','turn-right':'↱','turn-slight-left':'↖','turn-slight-right':'↗','uturn-left':'↩','uturn-right':'↪','roundabout-left':'🔄','roundabout-right':'🔄','merge':'↗','fork-left':'↖','fork-right':'↗','straight':'↑'};
    return icons[m]||'↑';
}

function openExtNav(){
    const url=`https://www.google.com/maps/dir/?api=1&destination=${dest.lat},${dest.lng}&travelmode=driving`;
    window.open(url,'_blank');
}

function callPlace(){
    @if($foodOrder->prestataire?->phone)
        window.location.href='tel:{{ $foodOrder->prestataire->phone }}';
    @else
        alert('Numéro non disponible');
    @endif
}

function callClient(){
    @if($foodOrder->client?->phone)
        window.location.href='tel:{{ $foodOrder->client->phone }}';
    @else
        alert('Numéro non disponible');
    @endif
}

async function confirmPickup(){
    if(!confirm('Confirmer la récupération ?'))return;
    try{
        const r=await fetch(`/driver/deliveries/{{ $foodOrder->id }}/pickup`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}});
        const d=await r.json();
        if(d.success)location.reload();else alert(d.message||'Erreur');
    }catch(e){alert('Erreur')}
}

async function confirmTransit(){
    try{
        const r=await fetch(`/driver/deliveries/{{ $foodOrder->id }}/start`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}});
        const d=await r.json();
        if(d.success)location.reload();else alert(d.message||'Erreur');
    }catch(e){alert('Erreur')}
}

async function confirmDeliver(){
    const code=prompt('Code de confirmation client (4 chiffres) :');
    if(!code)return;
    if(!/^\d{4}$/.test(code)){alert('Code invalide');return;}
    const body={delivery_code:code};
    try{
        const r=await fetch(`/driver/deliveries/{{ $foodOrder->id }}/deliver`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(body)});
        const d=await r.json();
        if(d.success){alert(`Livraison terminée ! 🎉\nGains: +${d.earnings??0}€`);window.location.href='{{ route("driver.dashboard") }}';}
        else alert(d.message||'Erreur');
    }catch(e){alert('Erreur')}
}

async function reportProblem(){
    const reason=prompt('Décrivez le problème :');
    if(!reason)return;
    try{
        const r=await fetch(`/driver/deliveries/{{ $foodOrder->id }}/problem`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({reason})});
        const d=await r.json();
        if(d.success){alert('Problème signalé.');window.location.href='{{ route("driver.dashboard") }}';}
    }catch(e){alert('Erreur')}
}

// Recalculate every 60s
setInterval(()=>{if(driverMarker)calculateRoute()},60000);
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&callback=initMap&libraries=geometry,places"></script>
@endsection
