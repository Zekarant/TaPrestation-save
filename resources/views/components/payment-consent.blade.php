{{-- 
    Composant de consentement pour les conditions de paiement
    
    Usage:
    @include('components.payment-consent', [
        'type' => 'service|equipment|urgent_sale',
        'color' => 'blue|green|red'
    ])
--}}

@php
    $type = $type ?? 'service';
    $color = $color ?? 'blue';
    $showPaymentConsent = match ($type) {
        'service' => function_exists('booking_online_payment_enabled')
            ? booking_online_payment_enabled()
            : !(function_exists('cash_only_mode') && cash_only_mode()),
        default => function_exists('payment_feature_enabled')
            ? payment_feature_enabled()
            : !(function_exists('cash_only_mode') && cash_only_mode()),
    };
    
    // Récupérer la commission selon le type depuis les paramètres admin
    $commissionRate = match($type) {
        'service' => (float) get_setting('commission_services', '10'),
        'equipment', 'rental' => (float) get_setting('commission_rentals', '8'),
        'urgent_sale', 'flash' => (float) get_setting('commission_urgent_sales', get_setting('commission_services', '10')),
        'food' => (float) get_setting('commission_food', '15'),
        default => (float) get_setting('commission_services', '10'),
    };
    $autoReleaseHours = (int) get_setting('escrow_auto_release_hours', 48);
    
    // Récupérer les infos de paiement depuis la session
    $sessionKey = match($type) {
        'service' => 'service_creation.step2',
        'equipment' => 'equipment_creation.step2',
        'urgent_sale' => 'urgent_sale_creation.step1',
        default => 'service_creation.step2'
    };
    
    $paymentRequirement = session("$sessionKey.payment_requirement", 'none');
    $depositPercentage = session("$sessionKey.deposit_percentage", 0);
    $cashOnlyMode = function_exists('cash_only_mode') && cash_only_mode();

    // Ventes urgentes : paiement intégral requis (règle produit)
    if ($type === 'urgent_sale') {
        $paymentRequirement = 'full';
        $depositPercentage = 0;
    }

    if ($cashOnlyMode) {
        $paymentRequirement = 'none';
        $depositPercentage = 0;
    }
    
    // Textes selon le mode de paiement choisi
    $paymentLabels = [
        'none' => $cashOnlyMode ? 'Paiement direct / en espèces' : 'Aucun paiement requis',
        'deposit' => 'Acompte de ' . $depositPercentage . '% requis',
        'full' => 'Paiement intégral requis'
    ];
    
    $paymentDescription = match($paymentRequirement) {
        'none' => $cashOnlyMode
            ? 'Le paiement en ligne est désactivé. Vos clients paieront directement avec vous, hors plateforme.'
            : 'Vos clients pourront réserver sans payer à l\'avance. Vous conviendrez du paiement directement avec eux.',
        'deposit' => 'Vos clients devront verser un acompte de ' . $depositPercentage . '% pour valider leur réservation. Le solde sera à régler selon vos conditions.',
        'full' => 'Vos clients devront payer l\'intégralité du montant pour valider leur réservation. L\'argent sera sécurisé jusqu\'à confirmation de la prestation.',
        default => ''
    };
    
    // Couleurs selon le type
    $borderColor = match($color) {
        'blue' => 'border-blue-200',
        'green' => 'border-green-200',
        'red' => 'border-red-200',
        default => 'border-blue-200'
    };
    
    $bgColor = match($color) {
        'blue' => 'bg-blue-50',
        'green' => 'bg-green-50',
        'red' => 'bg-red-50',
        default => 'bg-blue-50'
    };
    
    $textColor = match($color) {
        'blue' => 'text-blue-900',
        'green' => 'text-green-900',
        'red' => 'text-red-900',
        default => 'text-blue-900'
    };
    
    $checkboxColor = match($color) {
        'blue' => 'text-blue-600 focus:ring-blue-500',
        'green' => 'text-green-600 focus:ring-green-500',
        'red' => 'text-red-600 focus:ring-red-500',
        default => 'text-blue-600 focus:ring-blue-500'
    };
@endphp

@if($showPaymentConsent)
<!-- Section Consentement aux conditions de paiement -->
<div class="bg-white rounded-xl shadow-lg border {{ $borderColor }} p-4 sm:p-6 mb-4 sm:mb-6">
    <h3 class="text-base sm:text-lg font-bold {{ $textColor }} mb-4 border-b {{ $borderColor }} pb-2 flex items-center gap-2">
        <i class="fas fa-shield-alt text-amber-500"></i>
        Conditions de paiement et consentement
    </h3>
    
    <!-- Résumé du mode de paiement choisi -->
    <div class="{{ $bgColor }} rounded-lg p-4 mb-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                @if($paymentRequirement === 'none')
                    <i class="fas fa-handshake text-gray-500 text-xl"></i>
                @elseif($paymentRequirement === 'deposit')
                    <i class="fas fa-percent text-amber-500 text-xl"></i>
                @else
                    <i class="fas fa-lock text-green-500 text-xl"></i>
                @endif
            </div>
            <div>
                <p class="font-semibold {{ $textColor }} text-sm sm:text-base">
                    {{ $paymentLabels[$paymentRequirement] ?? 'Mode de paiement' }}
                </p>
                <p class="text-xs sm:text-sm text-gray-600 mt-1">
                    {{ $paymentDescription }}
                </p>
            </div>
        </div>
    </div>

    @if($paymentRequirement === 'none')
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
            <h4 class="font-semibold text-gray-800 text-sm flex items-center gap-2 mb-2">
                <i class="fas fa-info-circle"></i>
                Paiement non requis
            </h4>
            <p class="text-xs sm:text-sm text-gray-700">
                @if($cashOnlyMode)
                    Le paiement en ligne est actuellement désactivé. Le règlement se fera directement entre vous et le client, en espèces ou selon vos modalités.
                @else
                    Aucun paiement n'est bloqué via Stripe pour cet élément. Le paiement se fait directement entre vous et le client.
                @endif
            </p>
        </div>
    @else
    
    <!-- Explication du système de protection -->
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
        <h4 class="font-semibold text-amber-800 text-sm flex items-center gap-2 mb-2">
            <i class="fas fa-info-circle"></i>
            Comment fonctionne la protection des paiements ?
        </h4>
        <ul class="text-xs sm:text-sm text-amber-700 space-y-2">
            <li class="flex items-start gap-2">
                <i class="fas fa-check text-green-500 mt-0.5 flex-shrink-0"></i>
                <span><strong>L'argent est sécurisé par Stripe</strong> : la plateforme ne stocke jamais votre argent. Stripe, prestataire de paiement agréé, conserve les fonds en toute sécurité.</span>
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-check text-green-500 mt-0.5 flex-shrink-0"></i>
                <span><strong>Libération après confirmation</strong> : les fonds vous sont transférés une fois que le client confirme la bonne réalisation.</span>
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-check text-green-500 mt-0.5 flex-shrink-0"></i>
                <span><strong>Libération automatique sous {{ $autoReleaseHours }}h</strong> : si le client ne se manifeste pas, les fonds sont automatiquement libérés après {{ $autoReleaseHours }} heures.</span>
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-check text-green-500 mt-0.5 flex-shrink-0"></i>
                <span><strong>Commission</strong> : une commission de <strong class="text-purple-700">{{ $commissionRate }}%</strong> est prélevée lors du transfert sur votre compte Stripe Connect.</span>
            </li>
        </ul>
    </div>
    
    <!-- Règles spécifiques selon le type -->
    @if($type === 'service')
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
        <h4 class="font-semibold text-blue-800 text-sm flex items-center gap-2 mb-2">
            <i class="fas fa-briefcase"></i>
            Règles pour les services
        </h4>
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-blue-200">
                        <th class="text-left py-2 text-blue-800">Situation</th>
                        <th class="text-left py-2 text-blue-800">Résultat</th>
                    </tr>
                </thead>
                <tbody class="text-blue-700">
                    <tr class="border-b border-blue-100">
                        <td class="py-2">Client paie</td>
                        <td class="py-2"><i class="fas fa-lock text-amber-500 mr-1"></i> Argent bloqué sur Stripe</td>
                    </tr>
                    <tr class="border-b border-blue-100">
                        <td class="py-2">Service rendu</td>
                        <td class="py-2"><i class="fas fa-check text-green-500 mr-1"></i> Client confirme OU {{ $autoReleaseHours }}h → Vous êtes payé</td>
                    </tr>
                    <tr class="border-b border-blue-100">
                        <td class="py-2">Qualité pas top MAIS service fait</td>
                        <td class="py-2"><i class="fas fa-times text-red-500 mr-1"></i> Pas de remboursement client</td>
                    </tr>
                    <tr class="border-b border-blue-100">
                        <td class="py-2">Annulation dans les délais</td>
                        <td class="py-2"><i class="fas fa-undo text-green-500 mr-1"></i> Remboursement selon vos règles d'annulation</td>
                    </tr>
                    <tr>
                        <td class="py-2">Annulation hors délai</td>
                        <td class="py-2"><i class="fas fa-times text-red-500 mr-1"></i> Pas de remboursement client</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @elseif($type === 'equipment')
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
        <h4 class="font-semibold text-green-800 text-sm flex items-center gap-2 mb-2">
            <i class="fas fa-tools"></i>
            Règles pour la location de matériel
        </h4>
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-green-200">
                        <th class="text-left py-2 text-green-800">Situation</th>
                        <th class="text-left py-2 text-green-800">Résultat</th>
                    </tr>
                </thead>
                <tbody class="text-green-700">
                    <tr class="border-b border-green-100">
                        <td class="py-2">Client paie (location + caution)</td>
                        <td class="py-2"><i class="fas fa-lock text-amber-500 mr-1"></i> Tout est bloqué sur Stripe</td>
                    </tr>
                    <tr class="border-b border-green-100">
                        <td class="py-2">Retour en bon état</td>
                        <td class="py-2"><i class="fas fa-check text-green-500 mr-1"></i> Location → Vous / Caution → Client</td>
                    </tr>
                    <tr class="border-b border-green-100">
                        <td class="py-2">Dégât constaté</td>
                        <td class="py-2"><i class="fas fa-exclamation-triangle text-amber-500 mr-1"></i> Vous gardez tout/partie de la caution</td>
                    </tr>
                    <tr class="border-b border-green-100">
                        <td class="py-2">Annulation dans les délais</td>
                        <td class="py-2"><i class="fas fa-undo text-green-500 mr-1"></i> Remboursement total au client</td>
                    </tr>
                    <tr>
                        <td class="py-2">Annulation hors délai</td>
                        <td class="py-2"><i class="fas fa-percent text-amber-500 mr-1"></i> Selon le % que vous avez configuré</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @elseif($type === 'urgent_sale')
    @php
        $disputeDeadlineDays = (int) get_setting('escrow_dispute_deadline_days', 7);
        $autoSplitBuyer = (int) get_setting('escrow_auto_split_buyer_percent', 40);
        $autoSplitSeller = (int) get_setting('escrow_auto_split_seller_percent', 60);
    @endphp
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
        <h4 class="font-semibold text-red-800 text-sm flex items-center gap-2 mb-2">
            <i class="fas fa-bolt"></i>
            Règles pour les ventes urgentes (système escrow)
        </h4>
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-red-200">
                        <th class="text-left py-2 text-red-800">Situation</th>
                        <th class="text-left py-2 text-red-800">Résultat</th>
                    </tr>
                </thead>
                <tbody class="text-red-700">
                    <tr class="border-b border-red-100">
                        <td class="py-2">Client paie</td>
                        <td class="py-2"><i class="fas fa-lock text-amber-500 mr-1"></i> Argent bloqué sur Stripe (escrow)</td>
                    </tr>
                    <tr class="border-b border-red-100">
                        <td class="py-2">Produit livré, client confirme</td>
                        <td class="py-2"><i class="fas fa-check text-green-500 mr-1"></i> Vous êtes payé (- commission {{ $commissionRate }}%)</td>
                    </tr>
                    <tr class="border-b border-red-100">
                        <td class="py-2">Produit livré, client ne fait rien</td>
                        <td class="py-2"><i class="fas fa-clock text-blue-500 mr-1"></i> Après {{ $autoReleaseHours }}h → Paiement automatique</td>
                    </tr>
                    <tr class="border-b border-red-100">
                        <td class="py-2">Client signale un problème</td>
                        <td class="py-2"><i class="fas fa-exclamation-triangle text-amber-500 mr-1"></i> Litige ouvert ({{ $disputeDeadlineDays }} jours pour résoudre)</td>
                    </tr>
                    <tr class="border-b border-red-100">
                        <td class="py-2">Client retourne le produit</td>
                        <td class="py-2"><i class="fas fa-undo text-blue-500 mr-1"></i> Remboursement total au client (0€ de commission)</td>
                    </tr>
                    <tr>
                        <td class="py-2">Litige non résolu après {{ $disputeDeadlineDays }} jours</td>
                        <td class="py-2"><i class="fas fa-balance-scale text-purple-500 mr-1"></i> Partage automatique : {{ $autoSplitBuyer }}% client / {{ $autoSplitSeller }}% vendeur</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mt-3 space-y-2">
            <p class="text-xs text-red-600">
                <i class="fas fa-shield-alt mr-1"></i>
                <strong>Système 100% automatique</strong> : aucun admin n'intervient. Les règles sont appliquées automatiquement par le système.
            </p>
            <p class="text-xs text-red-600">
                <i class="fas fa-star mr-1"></i>
                <strong>Notes mutuelles</strong> : après chaque transaction, le client vous note ET vous notez le client.
            </p>
        </div>
    </div>
    @endif
    
    <!-- Section En cas de problème -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
        <h4 class="font-semibold text-gray-800 text-sm flex items-center gap-2 mb-2">
            <i class="fas fa-question-circle"></i>
            En cas de problème ?
        </h4>
        <ul class="text-xs sm:text-sm text-gray-700 space-y-2">
            @if($type === 'service')
            <li class="flex items-start gap-2">
                <i class="fas fa-info-circle text-blue-500 mt-0.5 flex-shrink-0"></i>
                <span><strong>Service non réalisé</strong> : remboursement automatique via Stripe (sur votre compte Stripe Connect).</span>
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-info-circle text-blue-500 mt-0.5 flex-shrink-0"></i>
                <span><strong>Qualité insuffisante mais service fait</strong> : pas de remboursement obligatoire. Le client peut laisser un avis.</span>
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-clock text-blue-500 mt-0.5 flex-shrink-0"></i>
                <span><strong>Client absent</strong> : après {{ $autoReleaseHours }}h sans confirmation, vous êtes payé automatiquement.</span>
            </li>
            @elseif($type === 'equipment')
            <li class="flex items-start gap-2">
                <i class="fas fa-info-circle text-green-500 mt-0.5 flex-shrink-0"></i>
                <span><strong>Matériel endommagé</strong> : vous décidez du montant de caution à retenir (via Stripe / Stripe Connect).</span>
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-info-circle text-green-500 mt-0.5 flex-shrink-0"></i>
                <span><strong>Non-retour du matériel</strong> : la caution vous est intégralement versée.</span>
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-undo text-green-500 mt-0.5 flex-shrink-0"></i>
                <span><strong>Annulation</strong> : vous pouvez rembourser le client via Stripe (sur votre compte Stripe Connect).</span>
            </li>
            @elseif($type === 'urgent_sale')
            @php
                $disputeDeadlineDays = $disputeDeadlineDays ?? (int) get_setting('escrow_dispute_deadline_days', 7);
                $autoSplitBuyer = $autoSplitBuyer ?? (int) get_setting('escrow_auto_split_buyer_percent', 40);
                $autoSplitSeller = $autoSplitSeller ?? (int) get_setting('escrow_auto_split_seller_percent', 60);
            @endphp
            <li class="flex items-start gap-2">
                <i class="fas fa-info-circle text-red-500 mt-0.5 flex-shrink-0"></i>
                <span><strong>Produit non conforme</strong> : le client ouvre un litige. Vous avez {{ $disputeDeadlineDays }} jours pour convenir d'un retour.</span>
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-undo text-red-500 mt-0.5 flex-shrink-0"></i>
                <span><strong>Retour accepté</strong> : une fois le produit retourné et confirmé, remboursement total au client (la plateforme renonce à sa commission).</span>
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-balance-scale text-red-500 mt-0.5 flex-shrink-0"></i>
                <span><strong>Litige non résolu après {{ $disputeDeadlineDays }} jours</strong> : partage automatique {{ $autoSplitBuyer }}% client / {{ $autoSplitSeller }}% vendeur (0% commission).</span>
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-clock text-red-500 mt-0.5 flex-shrink-0"></i>
                <span><strong>Produit non récupéré</strong> : après {{ $autoReleaseHours }}h sans nouvelle du client, vous êtes payé automatiquement.</span>
            </li>
            @endif
        </ul>
        <p class="text-xs text-gray-500 mt-3 italic">
            <i class="fas fa-lightbulb mr-1"></i>
            Conseil : gardez toujours des preuves (photos avant/après, échanges, etc.) pour vous protéger.
        </p>
    </div>
    
    <!-- Pour les équipements avec caution -->
    @if($type === 'equipment')
    @php
        $securityDeposit = session("$sessionKey.security_deposit", 0);
    @endphp
    @if($securityDeposit > 0)
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
        <h4 class="font-semibold text-purple-800 text-sm flex items-center gap-2 mb-2">
            <i class="fas fa-shield-alt"></i>
            Caution de {{ number_format($securityDeposit, 2, ',', ' ') }} €
        </h4>
        <ul class="text-xs sm:text-sm text-purple-700 space-y-1">
            <li><i class="fas fa-arrow-right mr-1"></i> La caution est bloquée sur le moyen de paiement du client</li>
            <li><i class="fas fa-arrow-right mr-1"></i> Elle est libérée automatiquement au retour de l'équipement en bon état</li>
            <li><i class="fas fa-arrow-right mr-1"></i> En cas de dégât, vous pouvez demander une retenue partielle ou totale</li>
        </ul>
    </div>
    @endif
    @endif

    @endif
    
    <!-- Case de consentement obligatoire -->
    <div class="border-t {{ $borderColor }} pt-4 mt-4">
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input 
                    type="checkbox" 
                    id="payment_terms_consent" 
                    name="payment_terms_consent" 
                    value="1"
                    required
                    class="h-5 w-5 {{ $checkboxColor }} border-gray-300 rounded cursor-pointer"
                    {{ old('payment_terms_consent') ? 'checked' : '' }}
                >
            </div>
            <label for="payment_terms_consent" class="ml-3 cursor-pointer">
                <span class="text-sm font-medium {{ $textColor }}">
                    J'accepte les conditions de paiement <span class="text-red-500">*</span>
                </span>
                <p class="text-xs text-gray-600 mt-1">
                    En cochant cette case, je confirme avoir lu et compris les conditions de paiement décrites ci-dessus.
                    @if($paymentRequirement === 'none')
                        @if($cashOnlyMode)
                            Je comprends que le paiement en ligne est désactivé et que le règlement se fera directement entre moi et le client.
                        @else
                            Je comprends que le paiement se fait directement entre moi et le client.
                        @endif
                    @else
                        J'accepte que les paiements soient traités via Stripe et que la commission de <strong>{{ $commissionRate }}%</strong> soit prélevée sur mes revenus.
                        @if($type === 'service')
                        Je comprends que l'argent sera bloqué jusqu'à confirmation du client ou libération automatique après {{ $autoReleaseHours }}h, et qu'un service réalisé mais de qualité insuffisante ne donne pas lieu à remboursement.
                        @elseif($type === 'equipment')
                        Je comprends que le montant de la location et la caution seront bloqués. La caution sera rendue au client sauf en cas de dégât constaté.
                        @elseif($type === 'urgent_sale')
                        @php
                            $disputeDeadlineDays = $disputeDeadlineDays ?? (int) get_setting('escrow_dispute_deadline_days', 7);
                            $autoSplitBuyer = $autoSplitBuyer ?? (int) get_setting('escrow_auto_split_buyer_percent', 40);
                            $autoSplitSeller = $autoSplitSeller ?? (int) get_setting('escrow_auto_split_seller_percent', 60);
                        @endphp
                        Je comprends que l'argent sera bloqué (escrow) jusqu'à confirmation du client ou libération automatique après {{ $autoReleaseHours }}h. En cas de litige non résolu après {{ $disputeDeadlineDays }} jours, le partage automatique {{ $autoSplitBuyer }}%/{{ $autoSplitSeller }}% sera appliqué. En cas de retour produit accepté, le client sera remboursé intégralement et la plateforme renonce à sa commission. Les notes mutuelles seront demandées après chaque transaction.
                        @endif
                    @endif
                </p>
            </label>
        </div>
        
        @error('payment_terms_consent')
            <p class="text-red-500 text-xs mt-2 ml-8">{{ $message }}</p>
        @enderror
    </div>
    
    <!-- Champs cachés pour enregistrer le consentement -->
    <input type="hidden" name="consent_timestamp" value="{{ now()->toISOString() }}">
    <input type="hidden" name="consent_ip" value="{{ request()->ip() }}">
    <input type="hidden" name="consent_user_agent" value="{{ request()->userAgent() }}">
</div>
@endif
