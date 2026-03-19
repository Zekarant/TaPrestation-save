{{--
    _order_card.blade.php — Carte commande réutilisable
    @param  $order  — FoodOrder model instance
    @param  $panel  — 'pending' | 'accepted' | 'preparing' | 'ready'
--}}
@php
    $isUrgent = $panel === 'pending' && $order->created_at->diffInMinutes(now()) > 5;
    $requestedAt = null;
    if (!empty($order->requested_at)) {
        try {
            $requestedAt = $order->requested_at instanceof \Carbon\CarbonInterface
                ? $order->requested_at
                : \Illuminate\Support\Carbon::parse($order->requested_at);
        } catch (\Throwable $e) {
            $requestedAt = null;
        }
    }
    $showScheduleTag = $requestedAt && in_array($order->status, ['accepted','scheduled']) && $requestedAt->isAfter(now());
    $items = $order->items ?? collect();
    $maxShow = 3;

    // ── Politique de paiement ──
    $payPolicy = method_exists($order, 'getPaymentPolicy') ? ((array) ($order->getPaymentPolicy() ?? [])) : [];
    $pType     = $payPolicy['type'] ?? 'cash';       // cash | deposit | full_prepay
    $pPercent  = $payPolicy['percent'] ?? 0;
    $pStatus   = $order->payment_status ?? 'pending'; // pending | pending_capture | paid | refunded | partial
    $pMethod   = $order->payment_method ?? '';
    $isPaid    = ($pStatus === 'paid');
    $isCash    = ($pType === 'cash');
    $isDeposit = ($pType === 'deposit');
    $isFullPay = ($pType === 'full_prepay');

    // Montants pour acompte
    $depositAmount   = $isDeposit ? round(($order->total ?? 0) * ($pPercent / 100), 2) : 0;
    $remainingAmount = $isDeposit ? round(($order->total ?? 0) - $depositAmount, 2) : 0;

    // Le paiement en ligne est-il requis mais pas encore fait ?
    $onlineRequired  = !$isCash;
    $onlinePending   = $onlineRequired && !$isPaid && $pStatus !== 'pending_capture';

    // Livraison
    $isDelivery = ($order->delivery_type === 'delivery');
    $isPickup   = !$isDelivery;
    $hasDriver   = !empty($order->driver_id);

    // Flux bloquant pour accept (pending)
    $canAccept = !($pMethod !== 'cash' && $pStatus === 'pending' && $onlineRequired);

    // Flux bloquant pour prépa (accepted) — livreur externe requis
    $requiresExternalDriver = method_exists($order, 'requiresExternalDriver') ? (bool) $order->requiresExternalDriver() : false;
    $hasDriverAccepted = method_exists($order, 'hasDriverAccepted') ? (bool) $order->hasDriverAccepted() : false;
    $needsDriver = $isDelivery && $requiresExternalDriver && !$hasDriverAccepted;
@endphp

<div
    class="fd-card {{ $isUrgent ? 'urgent' : '' }}"
    data-fd-order-link
    data-href="{{ route('prestataire.food-orders.show', $order) }}"
    role="link"
    tabindex="0"
>

    {{-- HEADER --}}
    <div class="fd-card-head">
        <span class="fd-card-num">#{{ $order->order_number }}</span>
        @if($showScheduleTag)
            <span class="fd-card-time scheduled">📅 {{ $requestedAt->format('d/m H\hi') }}</span>
        @else
            <span class="fd-card-time">{{ $order->created_at->diffForHumans(null, false, true) }}</span>
        @endif
    </div>

    {{-- CLIENT --}}
    <div class="fd-card-client">
        <i class="fas fa-user" style="font-size:.72rem"></i>
        {{ $order->client->name ?? 'Client' }}
    </div>

    {{-- ITEMS --}}
    <div class="fd-card-items">
        @foreach($items->take($maxShow) as $item)
            <div class="fd-card-item">
                <span class="qty">{{ $item->quantity }}x</span>
                {{ $item->product_name ?? $item->name ?? 'Produit' }}
            </div>
        @endforeach
        @if($items->count() > $maxShow)
            <div class="fd-card-more">+{{ $items->count() - $maxShow }} autre(s)…</div>
        @endif
    </div>

    {{-- SPECIAL NOTES --}}
    @if($order->special_instructions || $order->notes)
        <div class="fd-card-notes">
            📝 {{ $order->special_instructions ?? $order->notes }}
        </div>
    @endif

    {{-- ══ PAIEMENT INFO ══ --}}
    <div class="fd-payment-info">
        @if($isCash)
            <div class="fd-pay-row cash-row">
                <span class="fd-pay-icon">💵</span>
                <span class="fd-pay-text">
                    Paiement espèces à la remise
                    <small>{{ number_format($order->total ?? 0, 2) }}€ à encaisser</small>
                </span>
                @if($isPaid)
                    <span class="fd-pay-tag paid">✓ Encaissé</span>
                @endif
            </div>
        @elseif($isDeposit)
            <div class="fd-pay-row deposit-row">
                <span class="fd-pay-icon">🏦</span>
                <span class="fd-pay-text">
                    Acompte {{ $pPercent }}% en ligne
                    <small>{{ number_format($depositAmount, 2) }}€ en ligne + {{ number_format($remainingAmount, 2) }}€ espèces</small>
                </span>
                @if($isPaid)
                    <span class="fd-pay-tag paid">✓ Acompte payé</span>
                @elseif($pStatus === 'pending_capture')
                    <span class="fd-pay-tag capture">🔒 Autorisé</span>
                @else
                    <span class="fd-pay-tag pending">⏳ Non payé</span>
                @endif
            </div>
        @elseif($isFullPay)
            <div class="fd-pay-row online-row">
                <span class="fd-pay-icon">💳</span>
                <span class="fd-pay-text">
                    Paiement intégral en ligne
                    <small>{{ number_format($order->total ?? 0, 2) }}€</small>
                </span>
                @if($isPaid)
                    <span class="fd-pay-tag paid">✓ Payé</span>
                @elseif($pStatus === 'pending_capture')
                    <span class="fd-pay-tag capture">🔒 Autorisé</span>
                @else
                    <span class="fd-pay-tag pending">⏳ Non payé</span>
                @endif
            </div>
        @endif
    </div>

    {{-- FOOTER --}}
    <div class="fd-card-foot">
        <span class="fd-card-price">{{ number_format($order->total ?? 0, 2) }}€</span>
        <span class="fd-card-type {{ $order->delivery_type }}">
            {{ $isDelivery ? '🛵 Livraison' : '🏪 Emporter' }}
        </span>
    </div>

    {{-- ══ INFOS CONTEXTUELLES (livreur, blocage, etc) ══ --}}
    @if($panel === 'accepted' && $isDelivery)
        <div class="fd-ctx-info">
            @if($hasDriver)
                <div class="fd-ctx ok"><i class="fas fa-check-circle"></i> Livreur assigné</div>
            @elseif($needsDriver)
                <div class="fd-ctx warn"><i class="fas fa-spinner fa-spin"></i> En attente d'un livreur (requis avant prépa)</div>
            @else
                <div class="fd-ctx info"><i class="fas fa-search"></i> Recherche d'un livreur…</div>
            @endif
        </div>
    @endif

    @if($panel === 'pending' && !$canAccept)
        <div class="fd-ctx-info">
            <div class="fd-ctx warn"><i class="fas fa-credit-card"></i> Le client n'a pas encore payé en ligne — acceptation bloquée</div>
        </div>
    @endif

    {{-- ══ ACTION BUTTONS ══ --}}
    <div class="fd-card-btns">

        {{-- ═════ PENDING ═════ --}}
        @if($panel === 'pending')
            @if($canAccept)
                <form method="POST" action="{{ route('prestataire.food-orders.accept', $order) }}">
                    @csrf
                    <button type="submit" class="fd-cbtn accept">
                        <i class="fas fa-check"></i> Accepter
                    </button>
                </form>
            @else
                <button class="fd-cbtn disabled" disabled title="En attente du paiement en ligne">
                    <i class="fas fa-hourglass-half"></i> Paiement en attente…
                </button>
            @endif
            <form method="POST" action="{{ route('prestataire.food-orders.reject', $order) }}" data-confirm="Refuser cette commande ?">
                @csrf
                <button type="submit" class="fd-cbtn reject" title="Refuser">
                    <i class="fas fa-times"></i>
                </button>
            </form>
        @endif

        {{-- ═════ ACCEPTED ═════ --}}
        @if($panel === 'accepted')
            @if($needsDriver)
                <button class="fd-cbtn disabled" disabled>
                    <i class="fas fa-spinner fa-spin"></i> Attente livreur…
                </button>
            @else
                <form method="POST" action="{{ route('prestataire.food-orders.start-preparing', $order) }}">
                    @csrf
                    <button type="submit" class="fd-cbtn cook">
                        <i class="fas fa-fire-alt"></i> Lancer préparation
                    </button>
                </form>
            @endif
            <form method="POST" action="{{ route('prestataire.food-orders.cancel', $order) }}" data-confirm="Annuler cette commande ?">
                @csrf
                <button type="submit" class="fd-cbtn reject" title="Annuler">
                    <i class="fas fa-times"></i>
                </button>
            </form>
        @endif

        {{-- ═════ PREPARING ═════ --}}
        @if($panel === 'preparing')
            <form method="POST" action="{{ route('prestataire.food-orders.ready', $order) }}">
                @csrf
                <button type="submit" class="fd-cbtn ready">
                    <i class="fas fa-check-circle"></i> Prête !
                </button>
            </form>
            <form method="POST" action="{{ route('prestataire.food-orders.cancel', $order) }}" data-confirm="Annuler cette commande en cours de préparation ?">
                @csrf
                <button type="submit" class="fd-cbtn reject" title="Annuler">
                    <i class="fas fa-times"></i>
                </button>
            </form>
        @endif

        {{-- ═════ READY ═════ --}}
        @if($panel === 'ready')

            {{-- PICKUP ──────────── --}}
            @if($isPickup)
                @if($isCash && !$isPaid)
                    {{-- Cash non encaissé → confirmer espèces + code --}}
                    <form method="POST" action="{{ route('prestataire.food-orders.confirm-cash', $order) }}" data-confirm="Confirmer que le client a payé {{ number_format($order->total ?? 0, 2) }}€ en espèces ?">
                        @csrf
                        <button type="submit" class="fd-cbtn cash-confirm">
                            <i class="fas fa-coins"></i> Encaisser {{ number_format($order->total ?? 0, 2) }}€
                        </button>
                    </form>
                @endif
                @if($isDeposit && !$isPaid)
                    {{-- Acompte pas payé → bloquer --}}
                    <button class="fd-cbtn disabled" disabled>
                        <i class="fas fa-hourglass-half"></i> Acompte non payé
                    </button>
                @elseif($isFullPay && !$isPaid)
                    {{-- Full pay non payé → bloquer --}}
                    <button class="fd-cbtn disabled" disabled>
                        <i class="fas fa-hourglass-half"></i> Paiement non reçu
                    </button>
                @else
                    <button class="fd-cbtn code" type="button" data-fd-open-code="{{ $order->id }}">
                        <i class="fas fa-lock"></i> Vérifier code
                    </button>
                @endif

            {{-- DELIVERY ──────────── --}}
            @else
                @if($isFullPay && !$isPaid && $pStatus !== 'pending_capture')
                    <button class="fd-cbtn disabled" disabled>
                        <i class="fas fa-hourglass-half"></i> Paiement non reçu
                    </button>
                @elseif($isDeposit && !$isPaid && $pStatus !== 'pending_capture')
                    <button class="fd-cbtn disabled" disabled>
                        <i class="fas fa-hourglass-half"></i> Acompte non payé
                    </button>
                @else
                    @if($hasDriver)
                        <button type="button" class="fd-cbtn disabled" disabled title="Le livreur doit valider avec le code client">
                            <i class="fas fa-key"></i> Validation par livreur (code)
                        </button>
                    @else
                        <form method="POST" action="{{ route('prestataire.food-orders.deliver-myself', $order) }}">
                            @csrf
                            <button type="submit" class="fd-cbtn deliver-self">
                                <i class="fas fa-walking"></i> Livrer moi-même
                            </button>
                        </form>
                        <form method="POST" action="{{ route('prestataire.food-orders.convert-to-pickup', $order) }}" data-confirm="Convertir en retrait sur place ? Le client sera notifié.">
                            @csrf
                            <button type="submit" class="fd-cbtn convert-pickup" title="Convertir en retrait">
                                <i class="fas fa-store"></i> → Retrait
                            </button>
                        </form>
                    @endif
                @endif
            @endif
        @endif

    </div>

</div>
