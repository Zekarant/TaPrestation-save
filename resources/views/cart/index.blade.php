@extends('layouts.app')

@push('styles')
<style>
    /* Cart page mobile-first styles */
    .cart-page {
        padding-bottom: 100px;
    }
    .cart-page .container {
        max-width: 800px;
    }
    /* Empêcher mobile-app.css de casser le layout */
    .cart-page .cart-card {
        padding: 0 !important;
        margin-bottom: 0 !important;
        border-radius: 16px !important;
        overflow: hidden;
    }
    .cart-page .cart-item-card {
        padding: 0 !important;
        margin-bottom: 0 !important;
        border-radius: 0 !important;
    }

    /* ---- MOBILE CARD LAYOUT (< 640px) ---- */
    @media (max-width: 639px) {
        .cart-page h1 { font-size: 1.3rem !important; }
        .cart-table-desktop { display: none !important; }
        .cart-mobile-list { display: block !important; }
        .cart-page .container,
        .cart-page main {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }
    }
    /* ---- DESKTOP TABLE (>= 640px) ---- */
    @media (min-width: 640px) {
        .cart-mobile-list { display: none !important; }
        .cart-table-desktop { display: block !important; }
    }
</style>
@endpush

@section('content')
<div class="cart-page">
<div class="container mx-auto px-4 py-4 sm:py-8">
    <div class="mx-auto" style="max-width:800px;">

        {{-- En-tête --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('services.index') }}" class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 sm:hidden">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">
                    <i class="fas fa-shopping-cart text-blue-600 mr-1"></i> Panier
                </h1>
            </div>
            @if($cart && $cart->items && $cart->items->isNotEmpty() && ($canPayOnline ?? false) && function_exists('checkout_payments_enabled') && checkout_payments_enabled())
                <a href="{{ route('client.payments.cart.form') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm">
                    <i class="fas fa-credit-card mr-2 hidden sm:inline"></i> Payer
                </a>
            @endif
        </div>

        @if(session('success'))
            <div class="mb-3 p-3 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-3 p-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">{{ session('error') }}</div>
        @endif

        @if(!$cart || !$cart->items || $cart->items->isEmpty())
            {{-- Panier vide --}}
            <div class="bg-white rounded-2xl shadow-sm p-8 text-center cart-card">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-2xl text-gray-400"></i>
                </div>
                <p class="text-gray-700 font-semibold text-base">Votre panier est vide</p>
                <p class="text-gray-500 text-sm mt-1">Parcourez les services et produits pour ajouter des articles.</p>
                <a href="{{ route('services.index') }}" class="inline-flex items-center mt-5 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm">
                    <i class="fas fa-search mr-2"></i> Découvrir les services
                </a>
            </div>
        @else
            {{-- ===== VERSION MOBILE : CARTES ===== --}}
            <div class="cart-mobile-list space-y-3">
                @foreach($cart->items as $item)
                    @php
                        $p = $item->purchasable;
                        $remaining = max(0, (float)($item->line_total ?? 0) - (float)($item->line_deposit ?? 0));
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 cart-item-card overflow-hidden">
                        <div class="p-4">
                            {{-- Ligne 1 : Titre + Supprimer --}}
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-gray-900 text-sm leading-tight">
                                        @if($p instanceof \App\Models\Booking)
                                            <i class="fas fa-calendar-check text-blue-500 mr-1"></i> Réservation #{{ $p->booking_number ?? $p->id }}
                                        @elseif($p instanceof \App\Models\EquipmentRentalRequest)
                                            <i class="fas fa-tools text-teal-500 mr-1"></i> Location matériel #{{ $p->id }}
                                        @elseif($p instanceof \App\Models\UrgentSale)
                                            <i class="fas fa-tag text-orange-500 mr-1"></i> {{ $p->title ?? ('Produit #' . $p->id) }}
                                        @else
                                            <i class="fas fa-box text-gray-400 mr-1"></i> Article #{{ $item->id }}
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ class_basename($item->purchasable_type ?? 'Article') }}</div>
                                </div>
                                <form method="POST" action="{{ route('client.cart.items.remove', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center flex-shrink-0" style="min-height:32px;min-width:32px;">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </form>
                            </div>

                            {{-- Ligne 2 : Quantité --}}
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs text-gray-500 font-medium">Quantité</span>
                                @if($p instanceof \App\Models\UrgentSale)
                                    <form method="POST" action="{{ route('client.cart.items.update', $item) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        @php
                                            $maxStock = max(1, (int)(($p->quantity ?? 1) - ($p->reserved_quantity ?? 0) - ($p->sold_quantity ?? 0)));
                                        @endphp
                                        <input name="quantity" type="number" min="1" max="{{ $maxStock }}" value="{{ $item->quantity }}" class="w-16 h-9 text-center rounded-lg border-gray-300 text-sm font-semibold" style="font-size:14px;min-height:36px;" />
                                        <button class="h-9 px-3 rounded-lg bg-gray-100 text-gray-600 text-xs font-medium hover:bg-gray-200" style="min-height:36px;">OK</button>
                                    </form>
                                @else
                                    <span class="text-sm font-semibold text-gray-700 bg-gray-100 px-3 py-1 rounded-lg">{{ $item->quantity }}</span>
                                @endif
                            </div>

                            {{-- Ligne 3 : Prix --}}
                            <div class="bg-gray-50 rounded-xl p-3 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Total</span>
                                    <span class="text-sm font-bold text-gray-900">{{ number_format((float)($item->line_total ?? 0), 2) }} €</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">Acompte</span>
                                    <span class="text-sm font-bold text-blue-600">{{ number_format((float)($item->line_deposit ?? 0), 2) }} €</span>
                                </div>
                                <div class="flex items-center justify-between border-t border-gray-200 pt-1.5">
                                    <span class="text-xs text-gray-500">Reste à payer</span>
                                    <span class="text-sm font-bold text-gray-700">{{ number_format((float)($remaining ?? 0), 2) }} €</span>
                                </div>
                            </div>

                            {{-- Bouton réserve --}}
                            @if($p instanceof \App\Models\UrgentSale)
                                @php
                                    try {
                                        $itemHasOnlinePayment = (function_exists('normalize_payment_requirement_for_mode')
                                            ? normalize_payment_requirement_for_mode($p->payment_requirement ?? 'none')
                                            : ($p->payment_requirement ?? 'none')) === 'full';
                                        $itemPresta = $p->prestataire ?? null;
                                        $itemHasPayMethod = $itemPresta && !empty($itemPresta->stripe_account_id);
                                    } catch (\Throwable $ex) {
                                        $itemHasOnlinePayment = false;
                                        $itemHasPayMethod = false;
                                    }
                                @endphp
                                @if(!$itemHasOnlinePayment || !$itemHasPayMethod)
                                    @if(\Illuminate\Support\Facades\Route::has('urgent-sales.reserve'))
                                        <button type="button" onclick="openCartReservationModal({{ $p->id }}, {{ Js::from($p->title ?? 'Produit') }}, {{ $p->price ?? 0 }}, {{ $item->quantity }}, {{ max(1, (int)(($p->quantity ?? 1) - ($p->reserved_quantity ?? 0) - ($p->sold_quantity ?? 0))) }})" class="w-full mt-3 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold flex items-center justify-center gap-2" style="min-height:44px;">
                                            <i class="fas fa-calendar-check"></i> Réserver cet article
                                        </button>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ===== VERSION DESKTOP : TABLE ===== --}}
            <div class="cart-table-desktop">
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden cart-card">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Article</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Qté</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Total</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Acompte</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Reste</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($cart->items as $item)
                                @php $p = $item->purchasable; @endphp
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-gray-900">
                                            @if($p instanceof \App\Models\Booking)
                                                Réservation #{{ $p->booking_number ?? $p->id }}
                                            @elseif($p instanceof \App\Models\EquipmentRentalRequest)
                                                Location matériel #{{ $p->id }}
                                            @elseif($p instanceof \App\Models\UrgentSale)
                                                {{ $p->title ?? ('Produit #' . $p->id) }}
                                            @else
                                                Article #{{ $item->id }}
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500">{{ class_basename($item->purchasable_type ?? 'Article') }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($p instanceof \App\Models\UrgentSale)
                                            <form method="POST" action="{{ route('client.cart.items.update', $item) }}" class="inline-flex items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                @php
                                                    $maxStock = max(1, (int)(($p->quantity ?? 1) - ($p->reserved_quantity ?? 0) - ($p->sold_quantity ?? 0)));
                                                @endphp
                                                <input name="quantity" type="number" min="1" max="{{ $maxStock }}" value="{{ $item->quantity }}" class="w-20 rounded-md border-gray-300" />
                                                <button class="px-3 py-1 rounded-md border border-gray-300 text-sm hover:bg-gray-50">OK</button>
                                            </form>
                                        @else
                                            <span class="text-sm text-gray-700">{{ $item->quantity }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900">{{ number_format((float)($item->line_total ?? 0), 2) }} €</td>
                                    <td class="px-6 py-4 text-right text-sm font-semibold text-blue-700">{{ number_format((float)($item->line_deposit ?? 0), 2) }} €</td>
                                    @php
                                        $remaining = max(0, (float)($item->line_total ?? 0) - (float)($item->line_deposit ?? 0));
                                    @endphp
                                    <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900">{{ number_format((float)($remaining ?? 0), 2) }} €</td>
                                    <td class="px-6 py-4 text-right space-y-1">
                                        @if($p instanceof \App\Models\UrgentSale)
                                            @php
                                                try {
                                                $itemHasOnlinePayment = (function_exists('normalize_payment_requirement_for_mode')
                                                    ? normalize_payment_requirement_for_mode($p->payment_requirement ?? 'none')
                                                    : ($p->payment_requirement ?? 'none')) === 'full';
                                                    $itemPresta = $p->prestataire ?? null;
                                                    $itemHasPayMethod = $itemPresta && !empty($itemPresta->stripe_account_id);
                                                } catch (\Throwable $ex) {
                                                    $itemHasOnlinePayment = false;
                                                    $itemHasPayMethod = false;
                                                }
                                            @endphp
                                            @if(!$itemHasOnlinePayment || !$itemHasPayMethod)
                                                @if(\Illuminate\Support\Facades\Route::has('urgent-sales.reserve'))
                                                    <button type="button" onclick="openCartReservationModal({{ $p->id }}, {{ Js::from($p->title ?? 'Produit') }}, {{ $p->price ?? 0 }}, {{ $item->quantity }}, {{ max(1, (int)(($p->quantity ?? 1) - ($p->reserved_quantity ?? 0) - ($p->sold_quantity ?? 0))) }})" class="w-full px-3 py-1.5 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold flex items-center justify-center gap-1">
                                                        <i class="fas fa-calendar-check"></i> Réserver
                                                    </button>
                                                @endif
                                            @endif
                                        @endif
                                        <form method="POST" action="{{ route('client.cart.items.remove', $item) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="w-full px-3 py-1 rounded-md border border-red-200 text-red-700 hover:bg-red-50 text-sm">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ===== RÉCAPITULATIF (responsive) ===== --}}
            <div class="mt-4 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden cart-card">
                <div class="p-4 sm:p-6">
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Total (paiement complet)</span>
                            <span class="font-bold text-gray-900 text-base">{{ number_format((float)($totals['full'] ?? 0), 2) }} €</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Total acompte</span>
                            <span class="font-bold text-blue-600 text-base">{{ number_format((float)($totals['deposit'] ?? 0), 2) }} €</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-100 pt-2">
                            <span class="text-gray-600">Reste à payer plus tard</span>
                            <span class="font-bold text-gray-700">{{ number_format(max(0, (float)($totals['full'] ?? 0) - (float)($totals['deposit'] ?? 0)), 2) }} €</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        @if(($canPayOnline ?? false) && function_exists('checkout_payments_enabled') && checkout_payments_enabled())
                            <a href="{{ route('client.payments.cart.form') }}" class="w-full flex items-center justify-center px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm" style="min-height:48px;">
                                <i class="fas fa-lock mr-2"></i> Payer maintenant
                            </a>
                        @else
                            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm">
                                <div class="flex items-start gap-2">
                                    <i class="fas fa-info-circle mt-0.5 flex-shrink-0"></i>
                                    <div>
                                        <strong>Paiement hors plateforme</strong>
                                        <p class="mt-1 text-xs sm:text-sm">Utilisez le bouton <span class="font-semibold text-emerald-700">"Réserver"</span> sur chaque article pour envoyer une demande au vendeur.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
</div>

{{-- Modal de réservation depuis le panier --}}
@if(isset($cart) && $cart && $cart->items && $cart->items->isNotEmpty())
<div id="cartReservationModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-200" style="backdrop-filter: blur(3px); background-color: rgba(0, 0, 0, 0.45);">
    <div class="min-h-full w-full flex items-end sm:items-center justify-center p-3">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl p-4 sm:p-6 w-full sm:max-w-md border border-emerald-200 transform transition-all duration-200 scale-95 opacity-0" id="cartReservationModalContent">
            <form id="cartReservationForm" method="POST" action="">
                @csrf
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-emerald-100 p-2 rounded-full">
                            <i class="fas fa-calendar-check text-emerald-600"></i>
                        </div>
                        <h3 class="text-base md:text-lg font-semibold text-gray-900">Demander une réservation</h3>
                    </div>
                    <button type="button" onclick="closeCartReservationModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Info produit -->
                <div class="bg-gray-50 rounded-xl p-3 mb-4">
                    <div class="font-medium text-gray-900 text-sm" id="cartResProductTitle"></div>
                    <div class="text-emerald-600 font-bold" id="cartResUnitPrice"></div>
                </div>

                <div class="mb-4">
                    <label for="cartResQuantity" class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Quantité souhaitée</label>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="cartResDecrement()" class="w-10 h-10 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-xl font-bold text-gray-600">-</button>
                        <input id="cartResQuantity" name="quantity" type="number" min="1" max="1" value="1" class="flex-1 text-center text-lg font-bold border-gray-300 rounded-lg" onchange="cartResUpdateTotal()">
                        <button type="button" onclick="cartResIncrement()" class="w-10 h-10 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-xl font-bold text-gray-600">+</button>
                    </div>
                    <div class="text-xs text-gray-500 mt-1 text-center" id="cartResAvailableText"></div>
                </div>

                <!-- Total estimé -->
                <div class="bg-emerald-50 rounded-xl p-3 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Total estimé :</span>
                        <span id="cartResTotal" class="text-xl font-bold text-emerald-600"></span>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="cartResMessage" class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Message au vendeur (optionnel)</label>
                    <textarea id="cartResMessage" name="message" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm" placeholder="Précisez vos besoins, questions, ou votre disponibilité pour récupérer l'article..."></textarea>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-4 text-xs text-amber-800">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-info-circle mt-0.5 flex-shrink-0"></i>
                        <div>
                            <strong>Comment ça marche ?</strong>
                            <ul class="mt-1 space-y-0.5">
                                <li>1. Vous faites une demande de réservation</li>
                                <li>2. Le vendeur confirme et réserve le stock</li>
                                <li>3. Vous vous arrangez pour le paiement et la récupération</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                    <button type="button" onclick="closeCartReservationModal()" class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition duration-200 text-sm">
                        Annuler
                    </button>
                    <button type="submit" class="flex-1 bg-emerald-600 text-white px-4 py-2 rounded-md hover:bg-emerald-700 transition duration-200 text-sm font-bold">
                        Envoyer la demande
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
let cartResUnitPrice = 0;
let cartResMaxQty = 1;

function openCartReservationModal(saleId, title, price, currentQty, maxQty) {
    const modal = document.getElementById('cartReservationModal');
    if (!modal) return;

    // Configurer le formulaire
    const form = document.getElementById('cartReservationForm');
    form.action = '{{ url("urgent-sales") }}/' + saleId + '/reserve';

    // Remplir les infos
    document.getElementById('cartResProductTitle').textContent = title;
    document.getElementById('cartResUnitPrice').textContent = price.toFixed(2).replace('.', ',') + '€ / unité';

    cartResUnitPrice = price;
    cartResMaxQty = maxQty;

    const qtyInput = document.getElementById('cartResQuantity');
    qtyInput.value = currentQty;
    qtyInput.max = maxQty;

    document.getElementById('cartResAvailableText').textContent = maxQty + ' disponible(s)';
    document.getElementById('cartResMessage').value = '';

    cartResUpdateTotal();

    // Ouvrir
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        const content = document.getElementById('cartReservationModalContent');
        if (content) {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100');
        }
    }, 10);
}

function closeCartReservationModal() {
    const modal = document.getElementById('cartReservationModal');
    if (!modal) return;

    const content = document.getElementById('cartReservationModalContent');
    if (content) {
        content.classList.remove('scale-100');
        content.classList.add('scale-95', 'opacity-0');
    }
    modal.classList.add('opacity-0');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

function cartResIncrement() {
    const input = document.getElementById('cartResQuantity');
    if (parseInt(input.value) < cartResMaxQty) {
        input.value = parseInt(input.value) + 1;
        cartResUpdateTotal();
    }
}

function cartResDecrement() {
    const input = document.getElementById('cartResQuantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
        cartResUpdateTotal();
    }
}

function cartResUpdateTotal() {
    const qty = parseInt(document.getElementById('cartResQuantity').value) || 1;
    const total = qty * cartResUnitPrice;
    document.getElementById('cartResTotal').textContent = total.toFixed(2).replace('.', ',') + '€';
}

// Fermer en cliquant à l'extérieur
document.addEventListener('click', function(e) {
    const modal = document.getElementById('cartReservationModal');
    if (e.target === modal) closeCartReservationModal();
});
</script>
@endpush
