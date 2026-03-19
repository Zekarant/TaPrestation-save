@extends('layouts.app')
 
@section('title', 'Nouvelle annonce vente flash')
 
@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    .tp-sale-form { font-family:'Plus Jakarta Sans',system-ui,sans-serif; background:#faf7f2; min-height:100vh; }
 
    .tp-sf-hero { position:relative; overflow:hidden; padding:1.75rem 0 3rem; }
    .tp-sf-hero::before { content:''; position:absolute; inset:0; background:linear-gradient(140deg,#ea580c 0%,#f97316 50%,#fb923c 100%); z-index:0; }
    .tp-sf-hero::after { content:''; position:absolute; bottom:-1px; left:-5%; right:-5%; height:36px; background:#faf7f2; border-radius:50% 50% 0 0/100% 100% 0 0; z-index:2; }
    .tp-sf-hero .hero-inner { position:relative; z-index:1; }
    .tp-sf-hero h1 { font-size:1.25rem; font-weight:800; color:#fff; margin:0; text-align:center; }
    .tp-sf-hero p { font-size:.75rem; color:rgba(255,255,255,.7); margin:.125rem 0 0; text-align:center; }
    .tp-sf-hero .btn-back {
        display:inline-flex; align-items:center; gap:5px;
        padding:7px 12px; background:rgba(255,255,255,.18); color:#fff;
        font-size:11px; font-weight:700; border-radius:9px; text-decoration:none;
        border:1px solid rgba(255,255,255,.2); transition:background .15s;
    }
    .tp-sf-hero .btn-back:hover { background:rgba(255,255,255,.30); }
    .tp-sf-hero .btn-back svg { width:14px; height:14px; }
 
    /* Card */
    .tp-sf-card { background:#fff; border-radius:14px; box-shadow:0 1px 8px rgba(0,0,0,.04); border:1px solid rgba(15,58,134,.06); padding:1.25rem; }
 
    /* Form fields */
    .tp-sf-label { display:block; font-size:12px; font-weight:700; color:#1a1f36; margin:0 0 .375rem; }
    .tp-sf-input, .tp-sf-select, .tp-sf-textarea {
        width:100%; padding:10px 12px; border:1.5px solid rgba(15,58,134,.1);
        border-radius:10px; font-size:13px; font-weight:500;
        font-family:'Plus Jakarta Sans',system-ui,sans-serif;
        background:#faf7f2; color:#1a1f36; transition:border-color .15s;
    }
    .tp-sf-input:focus, .tp-sf-select:focus, .tp-sf-textarea:focus { outline:none; border-color:#ea580c; box-shadow:0 0 0 3px rgba(234,88,12,.1); }
    .tp-sf-textarea { resize:vertical; min-height:100px; }
    .tp-sf-error { font-size:10px; color:#dc2626; font-weight:600; margin:.25rem 0 0; }
    .tp-sf-hint { font-size:10px; color:#8b92a8; margin:.25rem 0 0; }
    .tp-sf-group { margin-bottom:1rem; }
    .tp-sf-group:last-child { margin-bottom:0; }
 
    /* Location row */
    .tp-sf-loc-row { display:flex; gap:.5rem; }
    .tp-sf-loc-row .tp-sf-input { flex:1; }
    .tp-sf-geo-btn {
        padding:0 14px; background:#ea580c; color:#fff; border:none;
        border-radius:10px; cursor:pointer; transition:background .15s;
        display:flex; align-items:center; justify-content:center; flex-shrink:0;
    }
    .tp-sf-geo-btn:hover { background:#c2410c; }
    .tp-sf-geo-btn i { font-size:14px; }
 
    /* Suggestions dropdown */
    .tp-sf-suggestions {
        position:absolute; left:0; right:0; top:100%; margin-top:4px;
        background:#fff; border:1px solid rgba(15,58,134,.1);
        border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.08);
        max-height:192px; overflow-y:auto; z-index:50;
    }
    .tp-sf-suggestions.hidden { display:none; }
    .tp-sf-sug-item {
        padding:8px 12px; cursor:pointer; border-bottom:1px solid rgba(15,58,134,.04);
        transition:background .1s; font-size:12px;
    }
    .tp-sf-sug-item:last-child { border-bottom:none; }
    .tp-sf-sug-item:hover { background:rgba(234,88,12,.05); }
    .tp-sf-sug-item .name { font-weight:700; color:#1a1f36; }
    .tp-sf-sug-item .code { font-size:10px; color:#8b92a8; }
 
    /* File input */
    .tp-sf-file {
        width:100%; padding:10px 12px; border:1.5px dashed rgba(15,58,134,.12);
        border-radius:10px; font-size:12px; background:#faf7f2; cursor:pointer;
    }
 
    /* Info box */
    .tp-sf-info {
        padding:.75rem; border-radius:10px; font-size:12px; line-height:1.5;
        display:flex; align-items:flex-start; gap:6px;
    }
    .tp-sf-info i { margin-top:1px; }
    .tp-sf-info-blue { background:#dbeafe; color:#1e40af; border:1px solid #bfdbfe; }
    .tp-sf-info-yellow { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
 
    /* Buttons */
    .tp-sf-submit {
        width:100%; padding:12px; border:none; border-radius:12px;
        font-size:14px; font-weight:800; cursor:pointer; transition:all .15s;
        display:flex; align-items:center; justify-content:center; gap:6px;
        font-family:'Plus Jakarta Sans',system-ui,sans-serif;
    }
    .tp-sf-submit-green { background:#059669; color:#fff; }
    .tp-sf-submit-green:hover { background:#047857; }
    .tp-sf-submit-blue { background:#ea580c; color:#fff; }
    .tp-sf-submit-blue:hover { background:#c2410c; }
    .tp-sf-cancel {
        display:block; width:100%; padding:12px; border:none; border-radius:12px;
        font-size:13px; font-weight:700; cursor:pointer; text-align:center;
        background:rgba(15,58,134,.06); color:#5a6178; text-decoration:none;
        transition:background .15s;
    }
    .tp-sf-cancel:hover { background:rgba(15,58,134,.10); }
 
    /* Photos grid */
    .tp-sf-photos-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:.5rem; }
    @media(min-width:640px) { .tp-sf-photos-grid { grid-template-columns:repeat(4,1fr); } }
    .tp-sf-photo-item { position:relative; border-radius:8px; overflow:hidden; }
    .tp-sf-photo-item img { width:100%; height:72px; object-fit:cover; display:block; }
    .tp-sf-photo-delete {
        position:absolute; top:4px; right:4px;
        width:20px; height:20px; border-radius:50%;
        background:#dc2626; color:#fff; border:none;
        font-size:9px; cursor:pointer;
        display:flex; align-items:center; justify-content:center;
    }
 
    @media(max-width:640px) {
        .tp-sf-hero { padding:1.25rem 0 2.25rem; }
        .tp-sf-hero h1 { font-size:1.0625rem; }
    }
</style>
@endpush
 
@section('content')
<div class="tp-sale-form">
    <section class="tp-sf-hero">
        <div class="hero-inner max-w-lg mx-auto px-4">
            <div class="flex items-center justify-between mb-3">
                <a href="{{ route('client.my-urgent-sales.index') }}" class="btn-back">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Retour
                </a>
                <div></div>
            </div>
            <h1>➕ Nouvelle annonce</h1>
            <p>Publiez votre article en vente flash</p>
        </div>
    </section>
 
    <div class="max-w-lg mx-auto px-4 -mt-4 relative z-10 pb-12">
        <div class="tp-sf-info tp-sf-info-blue" style="margin-bottom:.75rem;">
            <i class="fas fa-info-circle"></i>
            Il vous reste <strong>{{ $remainingSlots }}</strong> emplacement(s) sur 5.
        </div>
 
        <form action="{{ route('client.my-urgent-sales.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="tp-sf-card" style="margin-bottom:1rem;">
                <div class="tp-sf-group">
                    <label for="title" class="tp-sf-label">Titre *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" class="tp-sf-input" placeholder="Ex: iPhone 12 Pro 128Go" required>
                    @error('title')<p class="tp-sf-error">{{ $message }}</p>@enderror
                </div>
                <div class="tp-sf-group">
                    <label for="description" class="tp-sf-label">Description *</label>
                    <textarea name="description" id="description" class="tp-sf-textarea" placeholder="Décrivez votre article..." required>{{ old('description') }}</textarea>
                    @error('description')<p class="tp-sf-error">{{ $message }}</p>@enderror
                </div>
                <div class="tp-sf-group">
                    <label for="price" class="tp-sf-label">Prix (€) *</label>
                    <input type="number" name="price" id="price" value="{{ old('price') }}" step="0.01" min="0.01" max="10000" class="tp-sf-input" placeholder="29.99" required>
                    @error('price')<p class="tp-sf-error">{{ $message }}</p>@enderror
                </div>
                <div class="tp-sf-group">
                    <label for="condition" class="tp-sf-label">État *</label>
                    <select name="condition" id="condition" class="tp-sf-select" required>
                        <option value="">Sélectionner...</option>
                        <option value="new" {{ old('condition')=='new'?'selected':'' }}>Neuf</option>
                        <option value="good" {{ old('condition')=='good'?'selected':'' }}>Bon état</option>
                        <option value="used" {{ old('condition')=='used'?'selected':'' }}>Usagé</option>
                        <option value="fair" {{ old('condition')=='fair'?'selected':'' }}>État correct</option>
                    </select>
                    @error('condition')<p class="tp-sf-error">{{ $message }}</p>@enderror
                </div>
                <div class="tp-sf-group">
                    <label for="category_id" class="tp-sf-label">Catégorie *</label>
                    <select name="category_id" id="category_id" class="tp-sf-select" required>
                        <option value="">Sélectionner...</option>
                        @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ old('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>@endforeach
                    </select>
                    @error('category_id')<p class="tp-sf-error">{{ $message }}</p>@enderror
                </div>
                <div class="tp-sf-group" style="position:relative;">
                    <label for="location" class="tp-sf-label">Localisation *</label>
                    <div class="tp-sf-loc-row">
                        <div style="flex:1;position:relative;">
                            <input type="text" name="location" id="location" value="{{ old('location') }}" class="tp-sf-input" placeholder="Tapez une ville..." required autocomplete="off">
                            <div id="location-suggestions" class="tp-sf-suggestions hidden"></div>
                        </div>
                        <button type="button" id="geolocBtn" class="tp-sf-geo-btn" title="Ma position"><i class="fas fa-location-arrow"></i></button>
                    </div>
                    @error('location')<p class="tp-sf-error">{{ $message }}</p>@enderror
                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                </div>
                <div class="tp-sf-group">
                    <label class="tp-sf-label">Photos (max 5)</label>
                    <input type="file" name="photos[]" id="photos" multiple accept="image/*" class="tp-sf-file">
                    <p class="tp-sf-hint">JPG, PNG ou WebP. Max 5 Mo par photo.</p>
                    @error('photos')<p class="tp-sf-error">{{ $message }}</p>@enderror
                    @error('photos.*')<p class="tp-sf-error">{{ $message }}</p>@enderror
                </div>
            </div>
 
            <div class="tp-sf-info tp-sf-info-yellow" style="margin-bottom:1rem;">
                <i class="fas fa-info-circle"></i>
                Les acheteurs vous contacteront directement. Le paiement se fera en main propre.
            </div>
 
            <button type="submit" class="tp-sf-submit tp-sf-submit-green"><i class="fas fa-check"></i> Publier l'annonce</button>
        </form>
    </div>
</div>
@endsection
 
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const locInput = document.getElementById('location');
    const sugBox = document.getElementById('location-suggestions');
    const latInput = document.getElementById('latitude');
    const lonInput = document.getElementById('longitude');
    const geoBtn = document.getElementById('geolocBtn');
    let timer;
 
    locInput.addEventListener('input', function() {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length >= 2) timer = setTimeout(() => fetchSuggestions(q), 300);
        else sugBox.classList.add('hidden');
    });
    document.addEventListener('click', e => { if (!locInput.contains(e.target) && !sugBox.contains(e.target)) sugBox.classList.add('hidden'); });
 
    geoBtn.addEventListener('click', function() {
        if (!navigator.geolocation) { alert('Géolocalisation non supportée'); return; }
        geoBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        navigator.geolocation.getCurrentPosition(
            async pos => {
                const lat = pos.coords.latitude, lon = pos.coords.longitude;
                latInput.value = lat.toFixed(6); lonInput.value = lon.toFixed(6);
                try {
                    const r = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=10&addressdetails=1`, {headers:{'User-Agent':'TaPrestation/1.0'}});
                    const d = await r.json();
                    const city = d.address?.city || d.address?.town || d.address?.village || '';
                    const pc = d.address?.postcode || '';
                    locInput.value = city + (pc ? ` (${pc})` : '');
                } catch(e) { locInput.value = `${lat.toFixed(4)}, ${lon.toFixed(4)}`; }
                geoBtn.innerHTML = '<i class="fas fa-location-arrow"></i>';
            },
            err => { alert('Position indisponible'); geoBtn.innerHTML = '<i class="fas fa-location-arrow"></i>'; }
        );
    });
 
    function fetchSuggestions(q) {
        fetch(`/api/public/geolocation/cities?search=${encodeURIComponent(q)}&limit=8`)
            .then(r => r.json()).then(d => {
                if (d && d.length > 0) show(d.map(c => ({name:c.name||c.city,pc:c.postal_code||c.postcode||'',lat:c.latitude||c.lat,lon:c.longitude||c.lon||c.lng})));
                else nominatim(q);
            }).catch(() => nominatim(q));
    }
    function nominatim(q) {
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}, France&limit=8&addressdetails=1`, {headers:{'User-Agent':'TaPrestation/1.0'}})
            .then(r => r.json()).then(d => {
                if (d && d.length > 0) show(d.map(i => ({name:i.address?.city||i.address?.town||i.address?.village||i.display_name.split(',')[0],pc:i.address?.postcode||'',lat:+i.lat,lon:+i.lon})));
                else sugBox.classList.add('hidden');
            }).catch(() => sugBox.classList.add('hidden'));
    }
    function show(items) {
        sugBox.innerHTML = '';
        items.forEach(s => {
            const d = document.createElement('div'); d.className = 'tp-sf-sug-item';
            d.innerHTML = `<span class="name">${s.name}</span>${s.pc ? ` <span class="code">${s.pc}</span>` : ''}`;
            d.addEventListener('click', () => { locInput.value = s.name+(s.pc?` (${s.pc})`:''); latInput.value = s.lat; lonInput.value = s.lon; sugBox.classList.add('hidden'); });
            sugBox.appendChild(d);
        });
        sugBox.classList.remove('hidden');
    }
});
</script>
@endpush