@extends('layouts.app')

@section('title', 'Panier - ' . $prestataire->company_name)

@push('styles')
<style>
:root {
    --primary: #f97316;
    --primary-dark: #ea580c;
    --accent: #10b981;
    --danger: #ef4444;
    --bg-page: #f8fafc;
    --bg-card: #ffffff;
    --bg-muted: #f1f5f9;
    --text-dark: #0f172a;
    --text-body: #475569;
    --text-muted: #94a3b8;
    --border: #e2e8f0;
}
.dark {
    --primary: #fb923c;
    --primary-dark: #f97316;
    --accent: #34d399;
    --danger: #f87171;
    --bg-page: #0c0c0c;
    --bg-card: #161616;
    --bg-muted: #1f1f1f;
    --text-dark: #ffffff;
    --text-body: #e5e5e5;
    --text-muted: #a3a3a3;
    --border: #2a2a2a;
}

.cart-page { min-height: 100vh; background: var(--bg-page); padding: 10px; padding-bottom: 70px; }

.cart-header { margin-bottom: 8px; }
.cart-header a { color: var(--primary); text-decoration: none; font-size: 0.75rem; }
.cart-title { font-size: 0.95rem; font-weight: 700; color: var(--text-dark); margin: 2px 0 0; }
.cart-subtitle { font-size: 0.7rem; color: var(--text-muted); }

.cart-empty { background: var(--bg-card); border-radius: 10px; padding: 30px 16px; text-align: center; border: 1px solid var(--border); }
.cart-empty-icon { font-size: 2.5rem; opacity: 0.4; margin-bottom: 8px; }
.cart-empty h2 { font-size: 0.9rem; color: var(--text-dark); margin-bottom: 4px; }
.cart-empty p { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 12px; }
.btn-back-menu { display: inline-block; padding: 8px 16px; background: var(--primary); color: white; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 600; }

.cart-card { background: var(--bg-card); border-radius: 10px; border: 1px solid var(--border); overflow: hidden; margin-bottom: 8px; }
.cart-card-header { padding: 6px 10px; background: var(--bg-muted); border-bottom: 1px solid var(--border); font-size: 0.75rem; font-weight: 600; color: var(--text-dark); }

/* Article en ligne horizontal */
.cart-item { display: flex; align-items: center; gap: 8px; padding: 6px 10px; border-bottom: 1px solid var(--border); }
.cart-item:last-of-type { border-bottom: none; }
.cart-item-img { width: 36px; height: 36px; border-radius: 6px; background: var(--bg-muted); flex-shrink: 0; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.cart-item-img img { width: 100%; height: 100%; object-fit: cover; }
.cart-item-img i { color: var(--text-muted); font-size: 0.8rem; }
.cart-item-name { flex: 1; font-size: 0.8rem; font-weight: 600; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cart-item-qty { display: flex; align-items: center; gap: 4px; }
.qty-btn { width: 20px; height: 20px; border-radius: 4px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 0.65rem; }
.qty-btn-minus { background: var(--bg-muted); color: var(--text-body); }
.qty-btn-plus { background: rgba(249, 115, 22, 0.15); color: var(--primary); }
.qty-value { font-size: 0.75rem; font-weight: 700; color: var(--text-dark); min-width: 14px; text-align: center; }
.cart-item-total { font-size: 0.8rem; font-weight: 700; color: var(--primary); min-width: 55px; text-align: right; }
.cart-item-remove { background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 2px; font-size: 0.7rem; margin-left: 4px; }
.cart-item-remove:hover { color: var(--danger); }

.cart-actions { display: flex; justify-content: space-between; align-items: center; padding: 6px 10px; background: var(--bg-muted); }
.btn-clear { background: none; border: none; color: var(--danger); font-size: 0.7rem; font-weight: 600; cursor: pointer; }
.btn-add-more { color: var(--primary); font-size: 0.7rem; font-weight: 600; text-decoration: none; }

/* Résumé unique */
.cart-summary { background: var(--bg-card); border-radius: 10px; border: 1px solid var(--border); padding: 10px; }
.summary-row { display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 4px; color: var(--text-muted); }
.summary-row span:last-child { color: var(--text-body); }
.summary-row.small span:last-child { font-size: 0.65rem; }
.summary-divider { border: none; border-top: 1px solid var(--border); margin: 6px 0; }
.summary-total { display: flex; justify-content: space-between; font-size: 0.9rem; font-weight: 800; color: var(--text-dark); }
.summary-total span:last-child { color: var(--primary); }
.summary-info { margin-top: 6px; padding: 6px; background: rgba(59, 130, 246, 0.08); border-radius: 6px; font-size: 0.65rem; color: #3b82f6; text-align: center; }

/* Barre checkout fixe */
.checkout-bar { position: fixed; bottom: 0; left: 0; right: 0; background: var(--bg-card); border-top: 1px solid var(--border); padding: 8px 10px; z-index: 100; }
.checkout-bar-inner { max-width: 500px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.checkout-total-label { font-size: 0.6rem; color: var(--text-muted); }
.checkout-total-value { font-size: 0.95rem; font-weight: 800; color: var(--primary); }
.btn-checkout { padding: 10px 20px; background: var(--primary); color: white; border: none; border-radius: 8px; font-size: 0.8rem; font-weight: 700; text-decoration: none; }

.dark .cart-card, .dark .cart-summary { box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
.dark .checkout-bar { background: #161616; border-color: #252525; }
</style>
@endpush

@section('content')
<div class="cart-page">
    <div class="cart-header">
        <a href="{{ route('food.menu', $prestataire) }}"><i class="fas fa-arrow-left"></i> Retour</a>
        <h1 class="cart-title">Panier</h1>
        <p class="cart-subtitle">{{ $prestataire->company_name }}</p>
    </div>

    @if(empty($cartItems))
        <div class="cart-empty">
            <div class="cart-empty-icon">🛒</div>
            <h2>Panier vide</h2>
            <p>Ajoutez des produits depuis le menu</p>
            <a href="{{ route('food.menu', $prestataire) }}" class="btn-back-menu">Voir le menu</a>
        </div>
    @else
        <div class="cart-card">
            <div class="cart-card-header">Articles ({{ count($cartItems) }})</div>
            @foreach($cartItems as $key => $item)
                <div class="cart-item" id="cart-item-{{ $key }}">
                    <div class="cart-item-img">
                        @if($item['product']->image)
                            <img src="{{ asset('storage/' . $item['product']->image) }}" alt="{{ $item['product']->name ?? 'Produit' }}">
                        @else
                            <i class="fas fa-utensils"></i>
                        @endif
                    </div>
                    <span class="cart-item-name">{{ $item['product']->name }}</span>
                    <div class="cart-item-qty">
                        <button class="qty-btn qty-btn-minus" onclick="updateQuantity('{{ $key }}', -1)"><i class="fas fa-minus"></i></button>
                        <span class="qty-value" id="quantity-{{ $key }}">{{ $item['quantity'] }}</span>
                        <button class="qty-btn qty-btn-plus" onclick="updateQuantity('{{ $key }}', 1)"><i class="fas fa-plus"></i></button>
                    </div>
                    <span class="cart-item-total">{{ number_format($item['total'], 2) }}€</span>
                    <button class="cart-item-remove" onclick="removeItem('{{ $key }}')"><i class="fas fa-times"></i></button>
                </div>
            @endforeach
            <div class="cart-actions">
                <form action="{{ route('food.cart.clear', $prestataire) }}" method="POST" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-clear" onclick="return confirm('Vider ?')"><i class="fas fa-trash"></i> Vider</button>
                </form>
                <a href="{{ route('food.menu', $prestataire) }}" class="btn-add-more"><i class="fas fa-plus"></i> Ajouter</a>
            </div>
        </div>

        <div class="cart-summary">
            <div class="summary-row"><span>Sous-total</span><span>{{ number_format($totals['subtotal'], 2) }}€</span></div>
            <div class="summary-row"><span>Frais de service</span><span>{{ number_format($totals['service_fee'], 2) }}€</span></div>
            <div class="summary-row small"><span>Livraison</span><span>À l'étape suivante</span></div>
            <hr class="summary-divider">
            <div class="summary-total"><span>Total</span><span>{{ number_format($totals['subtotal'] + $totals['service_fee'], 2) }}€</span></div>
            @if($scheduleConfig['requires_advance_order'])
                <div class="summary-info" style="background: rgba(245, 158, 11, 0.12); color: #b45309;">
                    📅 Ce panier contient des produits sur commande. Première date possible : {{ $scheduleConfig['earliest_date']->format('d/m/Y') }}.
                </div>
            @else
                <div class="summary-info">💡 Paiement à la réception • Suivi en temps réel</div>
            @endif
        </div>

        <div class="checkout-bar">
            <div class="checkout-bar-inner">
                <div>
                    <div class="checkout-total-label">Total</div>
                    <div class="checkout-total-value">{{ number_format($totals['subtotal'] + $totals['service_fee'], 2) }}€</div>
                </div>
                <a href="{{ route('food.checkout', $prestataire) }}" class="btn-checkout">
                    <i class="fas fa-credit-card"></i> {{ $scheduleConfig['requires_advance_order'] ? 'Choisir la date' : 'Commander' }}
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function updateQuantity(k, d) {
    const q = document.getElementById('quantity-' + k);
    let n = parseInt(q.textContent) + d;
    if (n < 1) { if (confirm('Supprimer ?')) removeItem(k); return; }
    fetch('{{ route('food.cart.update', $prestataire) }}', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ item_key: k, quantity: n })
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
function removeItem(k) {
    if (!confirm('Supprimer ?')) return;
    fetch('{{ route('food.cart.remove', $prestataire) }}', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ item_key: k })
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
}
</script>
@endpush
