@props([
    'type' => 'service',
    'inputId' => 'priceInput',
    'quantityInputId' => null,
    'initialPrice' => null,
    'color' => 'green'
])

@php
    $commissionRate = \App\Services\CommissionService::ratePercent($type, 'prestataire');
    $stripeFeePercent = (float) get_setting('stripe_fee_percent', '1.4');
    $stripeFeeFixed = (float) get_setting('stripe_fee_fixed', '0.25');
    
    $colorClasses = [
        'green' => ['bg' => 'from-green-50 to-emerald-50', 'border' => 'border-green-200', 'text' => 'text-green-600', 'highlight' => 'border-green-400'],
        'blue' => ['bg' => 'from-blue-50 to-indigo-50', 'border' => 'border-blue-200', 'text' => 'text-blue-600', 'highlight' => 'border-blue-400'],
        'orange' => ['bg' => 'from-orange-50 to-amber-50', 'border' => 'border-orange-200', 'text' => 'text-orange-600', 'highlight' => 'border-orange-400'],
        'red' => ['bg' => 'from-red-50 to-pink-50', 'border' => 'border-red-200', 'text' => 'text-red-600', 'highlight' => 'border-red-400'],
    ];
    $colors = $colorClasses[$color] ?? $colorClasses['green'];
    
    // Calcul initial si prix fourni
    if ($initialPrice && $initialPrice > 0) {
        $initialStripeFee = round(($initialPrice * $stripeFeePercent / 100) + $stripeFeeFixed, 2);
        $initialCommission = round($initialPrice * ($commissionRate / 100), 2);
        $initialReceive = round($initialPrice - $initialStripeFee - $initialCommission, 2);
    } else {
        $initialStripeFee = 0;
        $initialCommission = 0;
        $initialReceive = 0;
    }
@endphp

<!-- Aperçu des gains après commission - Mobile optimisé -->
<div id="earningsPreview_{{ $inputId }}" 
     class="p-3 sm:p-4 bg-gradient-to-r {{ $colors['bg'] }} rounded-lg sm:rounded-xl border {{ $colors['border'] }} mt-3 {{ $initialPrice ? '' : 'hidden' }}"
     data-commission-rate="{{ $commissionRate }}"
     data-stripe-fee-percent="{{ $stripeFeePercent }}"
     data-stripe-fee-fixed="{{ $stripeFeeFixed }}"
     data-input-id="{{ $inputId }}">
    <h4 class="text-xs sm:text-sm font-semibold text-gray-700 mb-2 sm:mb-3 flex items-center">
        <span class="text-base sm:text-lg mr-1 sm:mr-2">💰</span> 
        <span class="hidden sm:inline">Aperçu de vos gains nets</span>
        <span class="sm:hidden">Vos gains nets</span>
    </h4>
    <div class="space-y-1.5 sm:space-y-2">
        <!-- Client paie -->
        <div class="flex justify-between items-center bg-white p-1.5 sm:p-2 rounded-md sm:rounded-lg">
            <span class="text-xs sm:text-sm text-gray-600 flex items-center">
                <span class="hidden sm:inline">💳 Client paie</span>
                <span class="sm:hidden">💳 Payé</span>
            </span>
            <span class="font-bold text-gray-800 text-sm sm:text-base"><span class="earnings-client-pays">{{ number_format($initialPrice ?? 0, 2) }}</span>€</span>
        </div>
        
        <!-- Frais Stripe -->
        <div class="flex justify-between items-center bg-white p-1.5 sm:p-2 rounded-md sm:rounded-lg">
            <span class="text-xs sm:text-sm text-gray-600 flex items-center flex-wrap">
                <i class="fab fa-stripe text-purple-500 mr-1"></i>
                <span class="hidden sm:inline">Frais Stripe (~{{ $stripeFeePercent }}% + {{ number_format($stripeFeeFixed, 2) }}€)</span>
                <span class="sm:hidden">Stripe</span>
            </span>
            <span class="font-bold text-purple-600 text-sm sm:text-base whitespace-nowrap">-<span class="earnings-stripe-fee">{{ number_format($initialStripeFee, 2) }}</span>€</span>
        </div>
        
        <!-- Commission plateforme -->
        <div class="flex justify-between items-center bg-white p-1.5 sm:p-2 rounded-md sm:rounded-lg">
            <span class="text-xs sm:text-sm text-gray-600 flex items-center flex-wrap">
                <span class="hidden sm:inline">🏢 Commission TaPrestation ({{ $commissionRate }}%)</span>
                <span class="sm:hidden">🏢 Commission ({{ $commissionRate }}%)</span>
            </span>
            <span class="font-bold text-orange-500 text-sm sm:text-base whitespace-nowrap">-<span class="earnings-commission">{{ number_format($initialCommission, 2) }}</span>€</span>
        </div>
        
        <!-- Séparateur -->
        <hr class="border-gray-300 my-1.5 sm:my-2">
        
        <!-- Vous recevez -->
        <div class="flex justify-between items-center bg-white p-2 sm:p-3 rounded-md sm:rounded-lg border-2 {{ $colors['highlight'] }}">
            <span class="text-xs sm:text-sm font-semibold text-gray-700">✅ Vous recevez</span>
            <span class="text-base sm:text-xl font-bold {{ $colors['text'] }}"><span class="earnings-you-receive">{{ number_format($initialReceive, 2) }}</span>€</span>
        </div>
    </div>
    <p class="text-[10px] sm:text-xs text-gray-500 mt-2 sm:mt-3 text-center">
        <i class="fas fa-info-circle mr-1"></i>
        <span class="hidden sm:inline">Montant versé après validation par le client</span>
        <span class="sm:hidden">Après validation client</span>
    </p>
</div>

<script>
(function() {
    const inputId = '{{ $inputId }}';
    const quantityInputId = '{{ $quantityInputId ?? '' }}';
    const commissionRate = {{ $commissionRate }};
    const stripeFeePercent = {{ $stripeFeePercent }};
    const stripeFeeFixed = {{ $stripeFeeFixed }};
    const previewEl = document.getElementById('earningsPreview_' + inputId);
    
    function updateEarningsPreview() {
        const priceInput = document.getElementById(inputId);
        if (!priceInput || !previewEl) return;
        
        let unitPrice = parseFloat(priceInput.value) || 0;
        let quantity = 1;
        
        // Si un champ quantité est défini, multiplier
        if (quantityInputId) {
            const quantityInput = document.getElementById(quantityInputId);
            if (quantityInput) {
                quantity = parseFloat(quantityInput.value) || 1;
            }
        }
        
        const totalPrice = unitPrice * quantity;
        
        if (totalPrice > 0) {
            // Frais Stripe: X% + 0.25€
            const stripeFee = Math.round(((totalPrice * stripeFeePercent / 100) + stripeFeeFixed) * 100) / 100;
            // Commission plateforme sur le prix total
            const commission = Math.round(totalPrice * (commissionRate / 100) * 100) / 100;
            // Ce que le presta reçoit = prix - stripe - commission
            const youReceive = Math.round((totalPrice - stripeFee - commission) * 100) / 100;
            
            previewEl.querySelector('.earnings-client-pays').textContent = totalPrice.toFixed(2);
            previewEl.querySelector('.earnings-stripe-fee').textContent = stripeFee.toFixed(2);
            previewEl.querySelector('.earnings-commission').textContent = commission.toFixed(2);
            previewEl.querySelector('.earnings-you-receive').textContent = youReceive.toFixed(2);
            previewEl.classList.remove('hidden');
        } else {
            previewEl.classList.add('hidden');
        }
    }
    
    // Attacher l'événement au champ de prix
    document.addEventListener('DOMContentLoaded', function() {
        const priceInput = document.getElementById(inputId);
        if (priceInput) {
            priceInput.addEventListener('input', updateEarningsPreview);
            priceInput.addEventListener('change', updateEarningsPreview);
        }
        
        // Aussi écouter le champ quantité si défini
        if (quantityInputId) {
            const quantityInput = document.getElementById(quantityInputId);
            if (quantityInput) {
                quantityInput.addEventListener('input', updateEarningsPreview);
                quantityInput.addEventListener('change', updateEarningsPreview);
            }
        }
        
        // Initialiser
        updateEarningsPreview();
    });
})();
</script>
