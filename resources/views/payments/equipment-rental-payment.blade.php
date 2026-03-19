@extends('layouts.app')

@section('content')
<div class="container mx-auto px-3 sm:px-4 py-4 pb-28 sm:py-8 sm:pb-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6 lg:p-8">
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 mb-1 sm:mb-2">Paiement de la location</h1>
            <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-8">Demande #{{ $rentalRequest->request_number ?? $rentalRequest->id }} - {{ $rentalRequest->equipment->name ?? 'Équipement' }}</p>

            {{-- Règles de paiement selon payment_requirement --}}
            @if($paymentRequirement === 'none')
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
                    <div class="flex items-start sm:items-center gap-2 text-green-800">
                        <span class="text-lg sm:text-xl">💵</span>
                        <div>
                            <p class="font-semibold text-sm sm:text-base">Paiement en direct</p>
                            <p class="text-xs sm:text-sm">Le prestataire accepte le paiement lors du retrait de l'équipement.</p>
                        </div>
                    </div>
                </div>
            @elseif($paymentRequirement === 'deposit')
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
                    <div class="flex items-start sm:items-center gap-2 text-blue-800">
                        <span class="text-lg sm:text-xl">💳</span>
                        <div>
                            <p class="font-semibold text-sm sm:text-base">Acompte + Caution requis</p>
                            <p class="text-xs sm:text-sm">{{ $rentalRequest->equipment->deposit_percentage ?? 30 }}% du montant + caution à payer maintenant. Le solde sera réglé au retrait.</p>
                        </div>
                    </div>
                </div>
            @elseif($paymentRequirement === 'full')
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
                    <div class="flex items-start sm:items-center gap-2 text-purple-800">
                        <span class="text-lg sm:text-xl">🔒</span>
                        <div>
                            <p class="font-semibold text-sm sm:text-base">Prépaiement total + Caution requis</p>
                            <p class="text-xs sm:text-sm">Le montant total + caution doivent être payés pour confirmer la réservation.</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 sm:p-6 mb-4 sm:mb-8">
                <h3 class="font-semibold text-gray-900 mb-3 sm:mb-4 text-sm sm:text-base">Récapitulatif</h3>
                
                {{-- Montant total de la location --}}
                <div class="flex justify-between items-center mb-2 sm:mb-3 p-2 bg-white rounded text-sm">
                    <span class="text-gray-700">📦 Location ({{ $rentalRequest->duration_days ?? 1 }} jour(s))</span>
                    <span class="font-semibold text-gray-900">{{ number_format($totalAmount, 2) }} €</span>
                </div>

                {{-- Séparation claire entre Acompte et Caution --}}
                @if($paymentRequirement === 'deposit' && $depositPercentage > 0)
                    {{-- Section ACOMPTE --}}
                    <div class="mb-2 sm:mb-3 p-2 sm:p-3 bg-blue-100 border-l-4 border-blue-500 rounded-r">
                        <div class="flex justify-between items-center">
                            <div class="flex-1 min-w-0">
                                <span class="font-semibold text-blue-800 text-sm sm:text-base">💳 ACOMPTE</span>
                                <p class="text-xs text-blue-600 mt-0.5 sm:mt-1">{{ $depositPercentage }}% du montant</p>
                                <p class="text-xs text-blue-500 hidden sm:block">→ Déduit du prix total, non remboursable</p>
                            </div>
                            <span class="font-bold text-blue-800 text-base sm:text-lg ml-2">{{ number_format($rentalDepositAmount, 2) }} €</span>
                        </div>
                    </div>
                @endif

                @if($securityDeposit > 0)
                    {{-- Section CAUTION --}}
                    <div class="mb-2 sm:mb-3 p-2 sm:p-3 bg-amber-100 border-l-4 border-amber-500 rounded-r">
                        <div class="flex justify-between items-center">
                            <div class="flex-1 min-w-0">
                                <span class="font-semibold text-amber-800 text-sm sm:text-base">🔐 CAUTION</span>
                                <p class="text-xs text-amber-600 mt-0.5 sm:mt-1">Garantie dommages</p>
                                <p class="text-xs text-amber-500 hidden sm:block">→ Remboursée si équipement rendu en bon état</p>
                            </div>
                            <span class="font-bold text-amber-800 text-base sm:text-lg ml-2">{{ number_format($securityDeposit, 2) }} €</span>
                        </div>
                    </div>
                @endif

                @if($paymentRequirement === 'deposit' && $depositPercentage > 0)
                    <div class="flex justify-between items-center mb-2 sm:mb-3 text-gray-500 text-xs sm:text-sm p-2 bg-gray-50 rounded">
                        <span>💵 Solde restant au retrait</span>
                        <span class="font-semibold">{{ number_format($totalAmount - $rentalDepositAmount, 2) }} €</span>
                    </div>
                @endif

                {{-- Total à payer maintenant --}}
                <div class="border-t-2 border-blue-400 pt-3 sm:pt-4 mt-3 sm:mt-4">
                    <div class="flex justify-between items-center bg-gradient-to-r from-blue-600 to-blue-700 text-white p-3 sm:p-4 rounded-lg">
                        <span class="text-sm sm:text-lg font-bold">Total à payer</span>
                        <span class="text-xl sm:text-2xl font-bold" id="displayAmount">
                            {{ number_format($amountDueNow, 2) }} €
                        </span>
                    </div>
                    
                    {{-- Détail du total à payer --}}
                    @if($paymentRequirement === 'deposit' && $securityDeposit > 0)
                        <div class="mt-2 text-xs text-gray-600 text-center">
                            Composé de : <span class="text-blue-600 font-medium">{{ number_format($rentalDepositAmount, 2) }}€ (acompte)</span> 
                            + <span class="text-amber-600 font-medium">{{ number_format($securityDeposit, 2) }}€ (caution)</span>
                        </div>
                    @elseif($paymentRequirement === 'full' && $securityDeposit > 0)
                        <div class="mt-2 text-xs text-gray-600 text-center">
                            Composé de : <span class="text-blue-600 font-medium">{{ number_format($totalAmount, 2) }}€ (location)</span> 
                            + <span class="text-amber-600 font-medium">{{ number_format($securityDeposit, 2) }}€ (caution)</span>
                        </div>
                    @endif
                </div>

                {{-- Légende explicative --}}
                <div class="mt-3 sm:mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                    <div class="flex items-center gap-2 p-2 bg-blue-50 rounded border border-blue-200">
                        <span class="w-3 h-3 bg-blue-500 rounded-full flex-shrink-0"></span>
                        <span class="text-blue-700"><strong>Acompte</strong> = Paiement partiel</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 bg-amber-50 rounded border border-amber-200">
                        <span class="w-3 h-3 bg-amber-500 rounded-full flex-shrink-0"></span>
                        <span class="text-amber-700"><strong>Caution</strong> = Garantie remboursable</span>
                    </div>
                </div>

                {{-- Info escrow --}}
                <div class="mt-3 sm:mt-4 p-2 sm:p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-start gap-2 text-green-800 text-xs sm:text-sm">
                        <span class="flex-shrink-0">🔒</span>
                        <div>
                            <p class="font-medium">Paiement sécurisé (Escrow)</p>
                            <p class="mt-0.5">Les fonds sont bloqués jusqu'au retour de l'équipement. <strong class="text-amber-700">La caution vous sera restituée</strong> si l'équipement est rendu en bon état.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Options de paiement selon payment_requirement --}}
            @if($paymentRequirement !== 'none')
            <div class="mb-4 sm:mb-8">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Option de paiement</label>
                <div class="grid grid-cols-1 {{ $paymentRequirement === 'deposit' ? 'sm:grid-cols-2' : '' }} gap-3 sm:gap-4">
                    @if($paymentRequirement === 'deposit')
                        <button type="button"
                                class="payment-option relative flex items-start p-3 sm:p-4 border-2 rounded-lg transition-colors {{ $defaultPaymentType === 'deposit' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' }}"
                                onclick="selectPaymentOption('deposit')"
                                data-type="deposit"
                                @if($paymentStatus !== 'pending') disabled @endif>
                            <div class="ml-0 text-sm text-left w-full">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-gray-700 text-sm">💳 Acompte + Caution</span>
                                    @if($paymentStatus === 'pending')
                                        <span class="text-xs bg-blue-100 text-blue-700 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded">Recommandé</span>
                                    @endif
                                </div>
                                <div class="text-blue-600 font-bold text-base sm:text-lg mt-1">{{ number_format($rentalDepositAmount + $securityDeposit, 2) }} €</div>
                                <div class="mt-1.5 sm:mt-2 space-y-0.5 sm:space-y-1">
                                    <div class="flex justify-between text-xs">
                                        <span class="text-blue-600">💳 Acompte ({{ $depositPercentage }}%)</span>
                                        <span class="font-medium text-blue-700">{{ number_format($rentalDepositAmount, 2) }}€</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-amber-600">🔐 Caution</span>
                                        <span class="font-medium text-amber-700">{{ number_format($securityDeposit, 2) }}€</span>
                                    </div>
                                </div>
                                @if($paymentStatus !== 'pending')
                                    <div class="text-xs text-green-600 mt-1.5 sm:mt-2">✅ Déjà payé</div>
                                @endif
                            </div>
                        </button>

                        <button type="button"
                                class="payment-option relative flex items-start p-3 sm:p-4 border-2 rounded-lg transition-colors {{ $defaultPaymentType === 'balance' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' }}"
                                onclick="selectPaymentOption('balance')"
                                data-type="balance"
                                @if(!$isDepositPaid) disabled @endif>
                            <div class="ml-0 text-sm text-left w-full">
                                <div class="font-medium text-gray-700 text-sm">💵 Solde restant</div>
                                <div class="text-gray-900 font-bold text-base sm:text-lg mt-1">{{ number_format($balanceAmount, 2) }} €</div>
                                <div class="text-xs text-gray-500 mt-0.5 sm:mt-1">Montant restant de la location</div>
                                @if(!$isDepositPaid)
                                    <div class="text-xs text-gray-400 mt-1.5 sm:mt-2">Disponible après paiement de l'acompte</div>
                                @else
                                    <div class="text-xs text-orange-600 mt-1.5 sm:mt-2">⏳ À payer avant le retrait</div>
                                @endif
                            </div>
                        </button>
                    @else
                        {{-- Full payment only --}}
                        <button type="button"
                                class="payment-option relative flex items-start p-3 sm:p-4 border-2 border-blue-500 bg-blue-50 rounded-lg"
                                data-type="full">
                            <div class="ml-0 text-sm text-left w-full">
                                <div class="font-medium text-gray-700 text-sm">🔒 Paiement total + Caution</div>
                                <div class="text-blue-600 font-bold text-base sm:text-lg mt-1">{{ number_format($totalAmount + $securityDeposit, 2) }} €</div>
                                <div class="mt-1.5 sm:mt-2 space-y-0.5 sm:space-y-1">
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600">📦 Location complète</span>
                                        <span class="font-medium text-gray-700">{{ number_format($totalAmount, 2) }}€</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-amber-600">🔐 Caution</span>
                                        <span class="font-medium text-amber-700">{{ number_format($securityDeposit, 2) }}€</span>
                                    </div>
                                </div>
                            </div>
                        </button>
                    @endif
                </div>
            </div>
            @endif

            <div class="bg-gray-50 rounded-lg p-4 sm:p-6 mb-4 sm:mb-8">
                <h3 class="font-semibold text-gray-900 mb-3 sm:mb-4 text-sm sm:text-base">Détails</h3>
                <div class="grid grid-cols-2 gap-3 sm:gap-4 text-xs sm:text-sm">
                    <div>
                        <p class="text-gray-600">Début</p>
                        <p class="font-semibold text-gray-900">{{ optional($rentalRequest->start_date)->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Fin</p>
                        <p class="font-semibold text-gray-900">{{ optional($rentalRequest->end_date)->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Prestataire</p>
                        <p class="font-semibold text-gray-900">{{ $rentalRequest->prestataire->user->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Paiement</p>
                        <p class="font-semibold text-green-600">
                            @if(in_array($paymentStatus, ['paid', 'full_paid', 'completed'])) Payé
                            @elseif(in_array($paymentStatus, ['partial', 'deposit_paid'])) Acompte payé
                            @else En attente
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <form id="payment-form" class="space-y-4 sm:space-y-6">
                @csrf
                <input type="hidden" id="payment_type" name="payment_type" value="{{ $defaultPaymentType }}">

                <div id="payment-element"></div>
                <div id="error-message" class="text-red-600 text-xs sm:text-sm hidden"></div>

                {{-- Conditions de paiement --}}
                @if(View::exists('components.payment-terms'))
                    @include('components.payment-terms', ['rentalRequest' => $rentalRequest])
                @endif

                <button type="submit" id="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 sm:py-3 rounded-lg transition-colors flex justify-center items-center text-sm sm:text-base">
                    <div class="spinner hidden mr-2 w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    <span id="button-text">Payer <span id="payAmount">0.00</span> €</span>
                </button>

                <p class="text-center text-xs text-gray-600 mt-3 sm:mt-4">
                    <i class="fas fa-lock mr-1"></i> Paiement sécurisé par Stripe.
                </p>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    // IMPORTANT: cette page utilise createEscrowPaymentIntent(),
    // donc le PaymentIntent est créé sur le compte PLATEFORME (pas sur le compte connecté).
    // Le front Stripe doit donc être initialisé SANS stripeAccount.
    const stripe = Stripe("{{ config('services.stripe.key') }}");
    const rentalRequestId = "{{ $rentalRequest->id }}";
    const paymentFormUrl = "{{ route('client.payments.rental.form', $rentalRequest) }}";
    const requestShowUrl = "{{ route('client.equipment-rental-requests.show', $rentalRequest) }}";

    // Montants calculés côté serveur
    const securityDeposit = Number("{{ number_format($securityDeposit, 2, '.', '') }}");
    const totalAmount = Number("{{ number_format($totalAmount, 2, '.', '') }}");
    const rentalDepositAmount = Number("{{ number_format($rentalDepositAmount, 2, '.', '') }}");
    const balanceAmount = Number("{{ number_format($balanceAmount, 2, '.', '') }}");
    const paymentRequirement = "{{ $paymentRequirement }}";

    let elements;
    let stripeInitialized = false;

    function amountForType(type) {
        if (type === 'deposit') return rentalDepositAmount + securityDeposit;
        if (type === 'balance') return balanceAmount;
        return totalAmount + securityDeposit; // full
    }

    async function initializeStripe() {
        if (stripeInitialized) return;

        const paymentType = document.getElementById('payment_type').value;

        try {
            const response = await fetch("{{ route('client.payments.rental.intent', $rentalRequest) }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ payment_type: paymentType }),
            });

            const data = await response.json();
            if (data?.already_paid && data?.redirect_url) {
                window.location.href = `${data.redirect_url}?payment_success=1`;
                return;
            }

            if (!response.ok || data.error) {
                throw new Error(data.error || 'Erreur lors de la création du paiement');
            }

            const appearance = { theme: 'stripe' };
            elements = stripe.elements({ appearance, clientSecret: data.clientSecret });

            // Configurer le Payment Element avec les wallets (Apple Pay, Google Pay)
            const paymentElement = elements.create("payment", {
                wallets: {
                    applePay: 'auto',
                    googlePay: 'auto'
                },
                layout: {
                    type: 'tabs',
                    defaultCollapsed: false
                }
            });
            paymentElement.mount("#payment-element");

            stripeInitialized = true;
            updateDisplayedAmount();
        } catch (error) {
            console.error(error);
            showMessage(error.message);
        }
    }

    function updateDisplayedAmount() {
        const type = document.getElementById('payment_type').value;
        const amount = amountForType(type);
        document.getElementById('payAmount').innerText = amount.toFixed(2);
        document.getElementById('displayAmount').innerText = amount.toFixed(2) + ' €';
    }

    function selectPaymentOption(type) {
        document.getElementById('payment_type').value = type;
        
        // Update visual selection
        document.querySelectorAll('.payment-option').forEach(btn => {
            btn.classList.remove('border-blue-500', 'bg-blue-50');
            btn.classList.add('border-gray-200');
        });
        const selected = document.querySelector(`.payment-option[data-type="${type}"]`);
        if (selected) {
            selected.classList.remove('border-gray-200');
            selected.classList.add('border-blue-500', 'bg-blue-50');
        }
        
        stripeInitialized = false;
        document.getElementById('payment-element').innerHTML = '';
        updateDisplayedAmount();
        initializeStripe();
    }

    async function handleSubmit(e) {
        e.preventDefault();

        // Vérifier l'acceptation des conditions de paiement
        const termsCheckbox = document.getElementById('accept_payment_terms');
        if (termsCheckbox && !termsCheckbox.checked) {
            showMessage("Veuillez accepter les conditions de paiement pour continuer.");
            return;
        }

        setLoading(true);
        let confirmedPaymentIntentId = null;

        try {
            if (!stripeInitialized || !elements) {
                showMessage('Le formulaire de paiement n’est pas prêt. Rechargez la page et réessayez.');
                setLoading(false);
                return;
            }

            const selectedPaymentType = document.getElementById('payment_type').value;
            const termsVersion = document.querySelector('input[name="terms_version"]')?.value || null;
            const termsAcceptedAtInput = document.getElementById('terms_accepted_at');
            const termsAcceptedAt = termsAcceptedAtInput?.value || new Date().toISOString();
            if (termsAcceptedAtInput && !termsAcceptedAtInput.value) {
                termsAcceptedAtInput.value = termsAcceptedAt;
            }

            const returnUrl = `${paymentFormUrl}?payment_return=1&payment_type=${encodeURIComponent(selectedPaymentType)}`;

            const { error, paymentIntent } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: returnUrl,
                },
                redirect: 'if_required'
            });

            if (error) {
                showMessage(error.message || 'Paiement refusé. Vérifiez vos informations et réessayez.');
                setLoading(false);
                return;
            }

            if (!paymentIntent || !paymentIntent.id) {
                showMessage('Paiement non confirmé.');
                setLoading(false);
                return;
            }
            confirmedPaymentIntentId = paymentIntent.id;

            const response = await fetch("{{ route('client.payments.rental.confirm', $rentalRequest) }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({
                    payment_intent_id: paymentIntent.id,
                    payment_type: selectedPaymentType,
                    terms_version: termsVersion,
                    terms_accepted_at: termsAcceptedAt,
                }),
            });

            const data = await response.json();
            if (!response.ok || data.error) {
                throw new Error(data.error || 'Erreur lors de la confirmation');
            }

            if (data.success === false) {
                window.location.href = `${requestShowUrl}?payment_pending=1`;
                return;
            }

            window.location.href = `${requestShowUrl}?payment_success=1`;
        } catch (err) {
            if (confirmedPaymentIntentId) {
                const safePi = encodeURIComponent(confirmedPaymentIntentId);
                window.location.href = `${requestShowUrl}?payment_pending=1&payment_intent=${safePi}`;
                return;
            }
            showMessage(err.message);
        } finally {
            setLoading(false);
        }
    }

    function showMessage(messageText) {
        const messageContainer = document.querySelector("#error-message");
        messageContainer.classList.remove("hidden");
        messageContainer.textContent = messageText;
        setTimeout(function () {
            messageContainer.classList.add("hidden");
            messageContainer.textContent = "";
        }, 5000);
    }

    function setLoading(isLoading) {
        const btn = document.querySelector("#submit");
        if (isLoading) {
            btn.disabled = true;
            document.querySelector(".spinner").classList.remove("hidden");
            document.querySelector("#button-text").classList.add("hidden");
        } else {
            btn.disabled = false;
            document.querySelector(".spinner").classList.add("hidden");
            document.querySelector("#button-text").classList.remove("hidden");
        }
    }

    async function handleStripeReturnIfAny() {
        const params = new URLSearchParams(window.location.search);
        const paymentIntentId = params.get('payment_intent');
        const isStripeReturn = params.get('payment_return') === '1';

        if (!paymentIntentId || !isStripeReturn) {
            return false;
        }

        const paymentType = params.get('payment_type') || document.getElementById('payment_type').value || 'full';
        const termsVersion = document.querySelector('input[name="terms_version"]')?.value || null;
        const termsAcceptedAt = document.getElementById('terms_accepted_at')?.value || null;

        setLoading(true);
        try {
            const response = await fetch("{{ route('client.payments.rental.confirm', $rentalRequest) }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({
                    payment_intent_id: paymentIntentId,
                    payment_type: paymentType,
                    terms_version: termsVersion,
                    terms_accepted_at: termsAcceptedAt,
                }),
            });

            const data = await response.json();
            if (!response.ok || data.error) {
                throw new Error(data.error || 'Erreur lors de la finalisation du paiement.');
            }

            if (data.success === false) {
                window.location.href = `${requestShowUrl}?payment_pending=1`;
                return true;
            }

            window.location.href = `${requestShowUrl}?payment_success=1`;
            return true;
        } catch (error) {
            // Fallback de sécurité: même si la confirmation locale échoue,
            // on renvoie vers la page demande avec le PI pour tentative de réconciliation serveur.
            const safePi = encodeURIComponent(paymentIntentId);
            window.location.href = `${requestShowUrl}?payment_pending=1&payment_intent=${safePi}`;
            return true;
        } finally {
            setLoading(false);
        }
    }

    document.getElementById("payment-form").addEventListener("submit", handleSubmit);

    (async function bootPaymentPage() {
        updateDisplayedAmount();
        const handled = await handleStripeReturnIfAny();
        if (!handled) {
            initializeStripe();
        }
    })();
</script>
@endpush
@endsection
