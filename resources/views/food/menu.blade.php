@extends('layouts.app')

@section('title', $prestataire->business_name . ' - Menu')

@push('styles')
<style>
/* ===== CSS VARIABLES ===== */
:root {
    --primary: #f97316;
    --primary-dark: #ea580c;
    --accent: #10b981;
    --bg-page: #f8fafc;
    --bg-white: #ffffff;
    --bg-muted: #f1f5f9;
    --text-dark: #0f172a;
    --text-body: #475569;
    --text-muted: #94a3b8;
    --border: #e2e8f0;
    --radius: 16px;
    --radius-sm: 10px;
    --shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 25px -5px rgba(0,0,0,0.1);
}

/* ===== DARK MODE - Plus lisible et pro ===== */
.dark {
    --primary: #fb923c;
    --primary-dark: #f97316;
    --accent: #34d399;
    --bg-page: #0c0c0c;
    --bg-white: #161616;
    --bg-muted: #1f1f1f;
    --text-dark: #ffffff;
    --text-body: #e5e5e5;
    --text-muted: #a3a3a3;
    --border: #2a2a2a;
    --shadow: 0 4px 6px -1px rgba(0,0,0,0.4);
    --shadow-lg: 0 10px 25px -5px rgba(0,0,0,0.5);
}

/* Dark mode specific overrides */
.dark .menu-header {
    background: linear-gradient(180deg, #161616 0%, #0c0c0c 100%);
    border-bottom: 1px solid #252525;
}

.dark .cat-bar {
    background: #121212;
    border-bottom: 1px solid #252525;
}

.dark .cat-btn {
    background: #1a1a1a;
    border: 1px solid #2a2a2a;
}

.dark .cat-btn:hover {
    background: #252525;
    border-color: #333;
}

.dark .cat-btn.active {
    background: linear-gradient(135deg, var(--primary) 0%, #ea580c 100%);
    border-color: transparent;
}

.dark .cat-btn .name {
    color: #d4d4d4;
}

.dark .cat-btn .count {
    background: #2a2a2a;
    color: #a3a3a3;
}

.dark .product-card {
    background: #161616;
    border: 1px solid #252525;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.dark .product-card:hover {
    border-color: #333;
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
}

.dark .product-img {
    background: #1a1a1a;
}

.dark .product-img-placeholder {
    background: linear-gradient(135deg, #1a1a1a 0%, #252525 100%);
}

.dark .product-name {
    color: #ffffff;
}

.dark .product-desc {
    color: #a3a3a3;
}

.dark .product-time {
    background: #1f1f1f;
    color: #a3a3a3;
    border: 1px solid #2a2a2a;
}

.dark .btn-add {
    background: linear-gradient(135deg, var(--primary) 0%, #ea580c 100%);
    box-shadow: 0 4px 12px rgba(251, 146, 60, 0.25);
}

.dark .btn-add:hover {
    box-shadow: 0 6px 16px rgba(251, 146, 60, 0.35);
}

.dark .btn-login {
    background: #1f1f1f;
    color: #d4d4d4;
    border: 1px solid #2a2a2a;
}

.dark .btn-login:hover {
    background: #252525;
    border-color: #333;
}

.dark .empty-state {
    background: #161616;
    border-color: #2a2a2a;
}

.dark .cart-float-inner {
    background: linear-gradient(135deg, var(--primary) 0%, #ea580c 100%);
    box-shadow: 0 8px 30px rgba(251, 146, 60, 0.3);
}

.dark .cart-float-inner:hover {
    box-shadow: 0 12px 40px rgba(251, 146, 60, 0.4);
}

.dark .resto-logo {
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
}

.dark .resto-status {
    background: rgba(52, 211, 153, 0.15);
    border: 1px solid rgba(52, 211, 153, 0.2);
}

.dark .btn-header-outline {
    background: #1f1f1f;
    color: #d4d4d4;
    border: 1px solid #2a2a2a;
}

.dark .btn-header-outline:hover {
    background: #252525;
    border-color: #333;
}

.dark .btn-header-primary {
    background: linear-gradient(135deg, var(--primary) 0%, #ea580c 100%);
}

.dark .btn-back {
    background: #1f1f1f;
    border: 1px solid #2a2a2a;
    color: #d4d4d4;
}

.dark .btn-back:hover {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

/* ===== BASE ===== */
.food-menu {
    min-height: 100vh;
    background: var(--bg-page);
    padding-bottom: 90px;
}

/* ===== HEADER COMPACT ===== */
.menu-header {
    background: var(--bg-white);
    border-bottom: 1px solid var(--border);
    padding: 10px 12px;
}
.menu-header-inner {
    max-width: 1100px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 10px;
}
.btn-back {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: var(--bg-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-body);
    text-decoration: none;
    transition: all 0.2s;
    flex-shrink: 0;
}
.btn-back:hover {
    background: var(--primary);
    color: white;
}
.btn-back svg {
    width: 16px;
    height: 16px;
}
.resto-logo {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    overflow: hidden;
    flex-shrink: 0;
}
.resto-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.resto-details {
    flex: 1;
    min-width: 0;
}
.resto-name {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-dark);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.resto-status {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--accent);
    font-size: 0.7rem;
    font-weight: 600;
}
.resto-status::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--accent);
}
.resto-status.closed {
    color: #dc2626;
}
.resto-status.closed::before {
    background: #dc2626;
}
.resto-closed-alert {
    margin: 10px 12px 0;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #fecaca;
    background: #fff1f2;
    color: #991b1b;
    font-size: 0.82rem;
    font-weight: 600;
}
.header-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}
.btn-header {
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s;
}
.btn-header i {
    font-size: 0.8rem;
}
.btn-header-outline {
    background: var(--bg-muted);
    color: var(--text-body);
}
.btn-header-outline:hover {
    background: var(--border);
}
.btn-header-primary {
    background: var(--primary);
    color: white;
}
.btn-header-primary:hover {
    background: var(--primary-dark);
}

/* ===== CATEGORIES COMPACT ===== */
.cat-bar {
    background: var(--bg-white);
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0;
    z-index: 100;
}
.cat-bar-inner {
    max-width: 1100px;
    margin: 0 auto;
    padding: 8px 12px;
    display: flex;
    gap: 8px;
    overflow-x: auto;
    scrollbar-width: none;
}
.cat-bar-inner::-webkit-scrollbar { display: none; }
.cat-btn {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 20px;
    background: var(--bg-muted);
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.cat-btn:hover {
    background: var(--border);
}
.cat-btn.active {
    background: var(--primary);
    color: white;
}
.cat-btn .icon { font-size: 0.9rem; }
.cat-btn .name {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-body);
    white-space: nowrap;
}
.cat-btn.active .name { color: white; }
.cat-btn .count {
    background: var(--border);
    color: var(--text-muted);
    padding: 1px 6px;
    border-radius: 10px;
    font-size: 0.65rem;
    font-weight: 700;
}
.cat-btn.active .count {
    background: rgba(255,255,255,0.25);
    color: white;
}

/* ===== SWIPE PANELS ===== */
.panels-container {
    overflow: hidden;
}
.panels-track {
    display: flex;
    transition: transform 0.35s ease;
}
.panel {
    flex: 0 0 100%;
    padding: 12px;
}
.panel-inner {
    max-width: 1100px;
    margin: 0 auto;
}

/* ===== PRODUCT GRID COMPACT ===== */
.products-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}

/* ===== PRODUCT CARD COMPACT ===== */
.product-card {
    background: var(--bg-white);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: all 0.2s;
    border: 1px solid var(--border);
    display: flex;
    flex-direction: row;
    height: 110px;
}
.product-card:hover {
    box-shadow: var(--shadow-lg);
}
.product-img {
    width: 110px;
    height: 110px;
    background: var(--bg-muted);
    position: relative;
    overflow: hidden;
    flex-shrink: 0;
}
.product-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.product-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
    background: linear-gradient(135deg, var(--bg-muted), var(--border));
}
.product-badge {
    position: absolute;
    top: 6px;
    left: 6px;
    padding: 3px 6px;
    border-radius: 4px;
    font-size: 0.55rem;
    font-weight: 700;
    text-transform: uppercase;
    background: var(--primary);
    color: white;
}
.product-info {
    flex: 1;
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-width: 0;
}
.product-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
}
.product-name {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--text-dark);
    line-height: 1.2;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.product-price {
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--primary);
    white-space: nowrap;
}
.product-desc {
    font-size: 0.75rem;
    color: var(--text-muted);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin: 4px 0;
}
.product-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.product-time {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.7rem;
    color: var(--text-muted);
}
.product-time i { 
    color: var(--primary); 
    font-size: 0.65rem;
}
.btn-add {
    padding: 6px 12px;
    border-radius: 6px;
    background: var(--primary);
    border: none;
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s;
}
.btn-add:hover {
    background: var(--primary-dark);
}
.btn-add.added {
    background: var(--accent);
}
.btn-add i {
    font-size: 0.7rem;
}
.btn-login {
    padding: 6px 12px;
    border-radius: 6px;
    background: var(--bg-muted);
    color: var(--text-body);
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
}
.btn-login:hover {
    background: var(--border);
}

/* ===== EMPTY STATE ===== */
.empty-state {
    padding: 40px 20px;
    text-align: center;
    background: var(--bg-white);
    border-radius: 12px;
    border: 2px dashed var(--border);
}
.empty-state .icon {
    font-size: 2.5rem;
    margin-bottom: 12px;
    opacity: 0.5;
}
.empty-state .text {
    color: var(--text-muted);
    font-size: 0.9rem;
}

/* ===== FLOATING CART COMPACT ===== */
.cart-float {
    position: fixed;
    bottom: 16px;
    left: 12px;
    right: 12px;
    max-width: 400px;
    margin: 0 auto;
    z-index: 200;
}
.cart-float-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--primary);
    padding: 10px 14px;
    border-radius: 12px;
    text-decoration: none;
    color: white;
    box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
    transition: all 0.2s;
}
.cart-float-inner:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(249, 115, 22, 0.5);
}
.cart-float-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.cart-float-icon {
    font-size: 1.1rem;
}
.cart-float-text {
    font-weight: 700;
    font-size: 0.85rem;
}
.cart-float-count {
    font-size: 0.7rem;
    opacity: 0.9;
}
.cart-float-total {
    background: rgba(255,255,255,0.2);
    padding: 6px 10px;
    border-radius: 6px;
    font-weight: 800;
    font-size: 0.85rem;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 640px) {
    .btn-header span {
        display: none;
    }
    .btn-header {
        padding: 8px 10px;
    }
}

@media (min-width: 640px) {
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .product-card {
        flex-direction: column;
        height: auto;
    }
    .product-img {
        width: 100%;
        height: 140px;
    }
    .product-info {
        padding: 12px;
    }
}

@media (min-width: 1024px) {
    .products-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    .menu-header {
        padding: 14px 20px;
    }
    .resto-logo {
        width: 50px;
        height: 50px;
    }
    .resto-name {
        font-size: 1.15rem;
    }
}
</style>
@endpush

@section('content')
@php
    $emojis = [
        'entree' => '🥗', 'plat' => '🍝', 'dessert' => '🍰', 'boisson' => '🥤',
        'amuse_bouche' => '🧆', 'gateau' => '🎂', 'pizza' => '🍕', 'sandwich' => '🥪',
        'salade' => '🥗', 'burger' => '🍔', 'autre' => '🍴'
    ];
    $totalProducts = 0;
    foreach($products as $items) {
        $totalProducts += $items->count();
    }
    $isFoodOpen = (bool) ($prestataire->food_is_open ?? true);
    $restaurantAvatar = $prestataire->profile_photo
        ?? $prestataire->photo
        ?? $prestataire->profile_image
        ?? $prestataire->foodProducts->pluck('image')->filter()->first()
        ?? $prestataire->user?->profile_photo_url
        ?? null;
@endphp

<div class="food-menu">
    <!-- HEADER -->
    <header class="menu-header">
        <div class="menu-header-inner">
            <a href="{{ route('food.explore', ['available_date' => request('available_date')]) }}" class="btn-back">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 14l-5-5 5-5"/></svg>
            </a>
            <div class="resto-logo">
                @if($restaurantAvatar)
                    <img src="{{ storage_asset_url($restaurantAvatar) }}" alt="{{ $prestataire->business_name ?? $prestataire->user->name ?? 'Restaurant' }}">
                @else
                    👨‍🍳
                @endif
            </div>
            <div class="resto-details">
                <h1 class="resto-name">{{ $prestataire->business_name ?? $prestataire->user->name }}</h1>
                <span class="resto-status {{ $isFoodOpen ? 'open' : 'closed' }}">{{ $isFoodOpen ? 'Ouvert' : 'Fermé' }}</span>
            </div>
            <div class="header-actions">
                @if($prestataire->phone)
                    <a href="tel:{{ $prestataire->phone }}" class="btn-header btn-header-outline">
                        <i class="fas fa-phone"></i>
                    </a>
                @endif
                @auth
                    <a href="{{ route('food.cart', $prestataire) }}" class="btn-header btn-header-primary">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Panier</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-header btn-header-primary">
                        <i class="fas fa-sign-in-alt"></i>
                    </a>
                @endauth
            </div>
        </div>
    </header>
    @if(!$isFoodOpen)
        <div class="resto-closed-alert">Ce restaurant est actuellement fermé. La prise de commande est indisponible.</div>
    @endif
    @if($availableDate)
        <div class="resto-closed-alert" style="border-color:#fcd34d;background:#fffbeb;color:#92400e;">
            Menu filtré pour le {{ $availableDate->format('d/m/Y') }}.
        </div>
    @endif

    <!-- CATEGORIES -->
    <nav class="cat-bar">
        <div class="cat-bar-inner" id="catBar">
            @foreach($products as $cat => $items)
                <button type="button" class="cat-btn {{ $loop->first ? 'active' : '' }}" data-index="{{ $loop->index }}" onclick="goTo({{ $loop->index }})">
                    <span class="icon">{{ $emojis[$cat] ?? '🍴' }}</span>
                    <span class="name">{{ $categories[$cat] ?? ucfirst($cat) }}</span>
                    <span class="count">{{ $items->count() }}</span>
                </button>
            @endforeach
        </div>
    </nav>

    <!-- PANELS -->
    <div class="panels-container" id="panelsContainer">
        <div class="panels-track" id="panelsTrack">
            @foreach($products as $cat => $items)
                <section class="panel">
                    <div class="panel-inner">
                        @if($items->count() > 0)
                            <div class="products-grid">
                                @foreach($items as $product)
                                    <article class="product-card">
                                        <div class="product-img">
                                            @if($product->image)
                                                <img src="{{ storage_asset_url($product->image) }}" alt="{{ $product->name }}" loading="lazy">
                                            @else
                                                <div class="product-img-placeholder">{{ $emojis[$cat] ?? '🍴' }}</div>
                                            @endif
                                            @if($product->advance_order_label)
                                                <span class="product-badge" style="background:#92400e;">Sur commande</span>
                                            @elseif($loop->first)
                                                <span class="product-badge">Populaire</span>
                                            @endif
                                        </div>
                                        <div class="product-info">
                                            <div class="product-top">
                                                <h3 class="product-name">{{ $product->name }}</h3>
                                                <span class="product-price">{{ number_format($product->price, 2) }}€</span>
                                            </div>
                                            @if($product->description)
                                                <p class="product-desc">{{ $product->description }}</p>
                                            @endif
                                            <div class="product-bottom">
                                                @if($product->advance_order_label)
                                                    <span class="product-time">
                                                        <i class="fas fa-calendar-day"></i>
                                                        {{ $product->advance_order_label }}
                                                    </span>
                                                @elseif($product->preparation_time)
                                                    <span class="product-time">
                                                        <i class="fas fa-clock"></i>
                                                        {{ $product->preparation_time }} min
                                                    </span>
                                                @else
                                                    <span></span>
                                                @endif
                                                @auth
                                                    @if($isFoodOpen && $product->is_available && (!method_exists($product, 'isInStock') || $product->isInStock()))
                                                        <form action="{{ route('food.cart.add', [$prestataire, $product]) }}" method="POST" class="form-add">
                                                            @csrf
                                                            <input type="hidden" name="quantity" value="1">
                                                            <button type="submit" class="btn-add">
                                                                <i class="fas fa-plus"></i>
                                                                Ajouter
                                                            </button>
                                                        </form>
                                                    @elseif(!$isFoodOpen)
                                                        <span class="text-xs text-red-600 font-medium px-2 py-1 bg-red-50 rounded-lg">Restaurant fermé</span>
                                                    @else
                                                        <span class="text-xs text-red-500 font-medium px-2 py-1 bg-red-50 rounded-lg">Indisponible</span>
                                                    @endif
                                                @else
                                                    <a href="{{ route('login') }}" class="btn-login">Connexion</a>
                                                @endauth
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="icon">{{ $emojis[$cat] ?? '🍴' }}</div>
                                <p class="text">Aucun produit dans cette catégorie</p>
                            </div>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    <!-- FLOATING CART -->
    @auth
        @php
            $cart = session()->get("food_cart.{$prestataire->id}", []);
            $cartCount = 0;
            $cartTotal = 0;
            foreach($cart as $item) {
                $cartCount += $item['quantity'] ?? 0;
                $cartTotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
            }
        @endphp
        @if($cartCount > 0)
            <div class="cart-float">
                <a href="{{ route('food.cart', $prestataire) }}" class="cart-float-inner">
                    <div class="cart-float-left">
                        <span class="cart-float-icon">🛒</span>
                        <div>
                            <div class="cart-float-text">Voir le panier</div>
                            <div class="cart-float-count">{{ $cartCount }} article{{ $cartCount > 1 ? 's' : '' }}</div>
                        </div>
                    </div>
                    <span class="cart-float-total">{{ number_format($cartTotal, 2) }}€</span>
                </a>
            </div>
        @endif
    @endauth
</div>
@endsection

@push('scripts')
<script>
(function() {
    let current = 0;
    const total = {{ $products->count() }};
    const track = document.getElementById('panelsTrack');
    const btns = document.querySelectorAll('.cat-btn');
    const container = document.getElementById('panelsContainer');

    window.goTo = function(idx) {
        if (idx < 0 || idx >= total) return;
        current = idx;
        track.style.transform = 'translateX(-' + (idx * 100) + '%)';
        btns.forEach((b, i) => b.classList.toggle('active', i === idx));
        btns[idx]?.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    };

    // Touch swipe
    let startX = 0, isDragging = false;
    container.addEventListener('touchstart', e => { startX = e.touches[0].clientX; isDragging = true; }, { passive: true });
    container.addEventListener('touchend', e => {
        if (!isDragging) return;
        isDragging = false;
        const dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 50) goTo(current + (dx < 0 ? 1 : -1));
    }, { passive: true });

    // Add to cart with AJAX
    document.querySelectorAll('.form-add').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('.btn-add');
            const formData = new FormData(this);
            
            btn.disabled = true;
            btn.classList.add('added');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                btn.innerHTML = '<i class="fas fa-check"></i> Ajouté';
                
                // Update cart UI
                updateCartUI(data);
                
                setTimeout(() => {
                    btn.classList.remove('added');
                    btn.innerHTML = '<i class="fas fa-plus"></i> Ajouter';
                    btn.disabled = false;
                }, 1200);
            })
            .catch(error => {
                // Fallback: reload page
                window.location.reload();
            });
        });
    });
    
    function updateCartUI(data) {
        const cartFloat = document.querySelector('.cart-float');
        const count = data.cart_count || 0;
        const total = data.cart_total || 0;
        
        if (count > 0) {
            if (!cartFloat) {
                // Create cart float if doesn't exist
                const cartHtml = `
                    <div class="cart-float">
                        <a href="${data.cart_url || '#'}" class="cart-float-inner">
                            <div class="cart-float-left">
                                <span class="cart-float-icon">🛒</span>
                                <div>
                                    <div class="cart-float-text">Voir le panier</div>
                                    <div class="cart-float-count">${count} article${count > 1 ? 's' : ''}</div>
                                </div>
                            </div>
                            <span class="cart-float-total">${parseFloat(total).toFixed(2)}€</span>
                        </a>
                    </div>
                `;
                document.querySelector('.food-menu').insertAdjacentHTML('beforeend', cartHtml);
            } else {
                // Update existing cart
                cartFloat.querySelector('.cart-float-count').textContent = count + ' article' + (count > 1 ? 's' : '');
                cartFloat.querySelector('.cart-float-total').textContent = parseFloat(total).toFixed(2) + '€';
            }
        }
    }
})();
</script>
@endpush
