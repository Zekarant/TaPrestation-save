{{--
    Composant d'affichage des conditions de paiement selon le type de produit.
    Usage: @include('components.payment-terms', ['cartItems' => $cart->items])

    Affiche les règles escrow automatiques pour que l'acheteur les accepte avant paiement.
--}}
@php
    $hasServices = false;
    $hasEquipment = false;
    $hasUrgentSales = false;

    foreach ($cartItems ?? [] as $item) {
        $purchasable = $item->purchasable ?? null;
        if (!$purchasable) continue;

        $class = get_class($purchasable);
        if (str_contains($class, 'Booking')) {
            $hasServices = true;
        } elseif (str_contains($class, 'EquipmentRental') || str_contains($class, 'EquipmentRentalRequest')) {
            $hasEquipment = true;
        } elseif (str_contains($class, 'UrgentSale')) {
            $hasUrgentSales = true;
        }
    }

    // Support des pages paiement hors panier (booking/rental/urgent sale direct)
    if (!empty($rentalRequest) || !empty($equipmentRentalRequest)) {
        $hasEquipment = true;
    }
    if (!empty($booking) || !empty($bookingRequest)) {
        $hasServices = true;
    }
    if (!empty($urgentSale) || !empty($urgentSalePurchase)) {
        $hasUrgentSales = true;
    }

    $termsVersion = 'v1.0';
@endphp

<div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6" id="payment-terms-section">
    <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        Conditions de paiement sécurisé
    </h3>

    <div class="space-y-4 text-sm text-gray-700">
        {{-- Services --}}
        @if($hasServices)
        <div class="bg-white rounded-lg p-3 border border-blue-100">
            <h4 class="font-medium text-blue-800 mb-2">🎯 Services</h4>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
                <li>Votre paiement est <strong>bloqué</strong> jusqu'à la réalisation du service</li>
                <li>Après le service, vous avez <strong>48h</strong> pour confirmer ou signaler un problème</li>
                <li>Sans action de votre part, le paiement est <strong>libéré automatiquement</strong> au prestataire</li>
                <li>Si le service n'est pas réalisé : <strong>remboursement total automatique</strong></li>
                <li>Annulation dans les délais : remboursement selon les conditions du prestataire</li>
                <li>Annulation hors délai : <strong>aucun remboursement</strong></li>
            </ul>
        </div>
        @endif

        {{-- Équipement --}}
        @if($hasEquipment)
        <div class="bg-white rounded-lg p-3 border border-purple-100">
            <h4 class="font-medium text-purple-800 mb-2">🛠️ Location d'équipement</h4>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
                <li>Montant de la location + caution <strong>bloqués</strong> jusqu'au retour</li>
                <li>Retour en bon état : location → prestataire, caution → <strong>remboursée</strong></li>
                <li>Équipement endommagé : tout ou partie de la caution <strong>retenue</strong></li>
                <li>Annulation dans les délais : <strong>remboursement total</strong></li>
                <li>Annulation hors délai : remboursement selon les conditions du prestataire</li>
            </ul>
        </div>
        @endif

        {{-- Ventes urgentes / Annonces --}}
        @if($hasUrgentSales)
        <div class="bg-white rounded-lg p-3 border border-orange-100">
            <h4 class="font-medium text-orange-800 mb-2">📦 Annonces / Ventes urgentes</h4>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
                <li>Votre paiement est <strong>bloqué sur la plateforme</strong> (escrow)</li>
                <li>Après livraison, vous avez <strong>48h</strong> pour confirmer la conformité</li>
                <li>Produit conforme ou sans action de votre part : paiement <strong>libéré au vendeur</strong></li>
                <li>Produit non conforme : <strong>litige ouvert automatiquement</strong></li>
                <li>Sans accord sous <strong>7 jours</strong> : partage automatique <strong>40% vous / 60% vendeur</strong></li>
                <li>Si retour reçu par le vendeur : <strong>remboursement total</strong></li>
                <li>En cas de litige/remboursement : la <strong>commission est rendue</strong></li>
                <li class="text-red-600 font-medium">⚠️ Pas d'annulation possible après paiement</li>
            </ul>
        </div>
        @endif

        {{-- Commission --}}
        <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-200">
            <h4 class="font-medium text-yellow-800 mb-2">💰 Commission plateforme</h4>
            <ul class="list-disc list-inside space-y-1 text-gray-600">
                <li>Une commission est prélevée sur chaque transaction</li>
                <li>En cas de litige ou remboursement, la <strong>commission est rendue</strong></li>
            </ul>
        </div>
    </div>

    {{-- Checkbox d'acceptation --}}
    <div class="mt-4 pt-4 border-t border-gray-200">
        <label class="flex items-start cursor-pointer group">
            <input type="checkbox" 
                   id="accept_payment_terms" 
                   name="accept_payment_terms" 
                   class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                   required>
            <span class="ml-3 text-sm text-gray-700 group-hover:text-gray-900">
                J'ai lu et j'accepte les <strong>conditions de paiement sécurisé</strong> décrites ci-dessus.
                Je comprends les règles de remboursement automatique et de gestion des litiges.
                <span class="text-gray-500">(Version {{ $termsVersion }})</span>
            </span>
        </label>
    </div>

    {{-- Champs cachés pour le suivi RGPD --}}
    <input type="hidden" name="terms_version" value="{{ $termsVersion }}">
    <input type="hidden" name="terms_accepted_at" id="terms_accepted_at" value="">
</div>

<script>
document.getElementById('accept_payment_terms')?.addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('terms_accepted_at').value = new Date().toISOString();
    } else {
        document.getElementById('terms_accepted_at').value = '';
    }
});
</script>
