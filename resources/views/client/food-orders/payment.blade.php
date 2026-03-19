@extends('layouts.app')

@section('title', 'Paiement - Commande #' . $foodOrder->order_number)

@push('styles')
<style>
    .payment-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.9));
        backdrop-filter: blur(12px);
    }
    #card-element {
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        background: white;
        transition: all 0.3s ease;
    }
    #card-element:focus-within {
        border-color: #f97316;
        box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
    }
    .card-error {
        border-color: #ef4444 !important;
    }
    .secure-badge {
        background: linear-gradient(135deg, #10b981, #059669);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 via-amber-50 to-yellow-50 py-6 pb-28 sm:pb-8">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto">
            
            {{-- Header --}}
            <div class="mb-6">
                <a href="{{ route('food.orders.show', $foodOrder) }}" class="inline-flex items-center gap-2 text-orange-600 hover:text-orange-700 mb-4">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour à la commande
                </a>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">💳 Paiement sécurisé</h1>
                <p class="text-gray-500 mt-1">Commande #{{ $foodOrder->order_number }}</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                
                {{-- Formulaire de paiement --}}
                <div class="lg:col-span-3">
                    <div class="payment-card rounded-2xl shadow-xl p-4 sm:p-6 border border-white/50">
                        
                        {{-- Badge sécurité --}}
                        <div class="flex items-center gap-3 mb-6 p-3 bg-green-50 rounded-xl border border-green-100">
                            <div class="w-10 h-10 secure-badge rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-green-700">Paiement sécurisé</p>
                                <p class="text-sm text-green-600">Cryptage SSL 256 bits • Stripe</p>
                            </div>
                        </div>

                        {{-- Méthodes de paiement --}}
                        <div class="mb-6">
                            <h3 class="font-bold text-gray-900 mb-4">💳 Choisir le mode de paiement</h3>
                            
                            @php
                                $policyType = ($paymentPolicy['type'] ?? 'cash');
                            @endphp
                            
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
                                {{-- Carte bancaire --}}
                                <button type="button" data-payment-method="card" id="btn-card"
                                    class="payment-method-btn flex flex-col items-center p-4 border-2 border-orange-500 bg-orange-50 rounded-xl transition-all hover:shadow-md">
                                    <i class="fas fa-credit-card text-2xl text-orange-600 mb-2"></i>
                                    <span class="text-sm font-medium text-gray-900">Carte</span>
                                </button>

                                {{-- Paiement rapide (Apple Pay / Google Pay si disponible) --}}
                                <button type="button" data-payment-method="express" id="btn-express"
                                    class="payment-method-btn hidden flex-col items-center p-4 border-2 border-gray-200 rounded-xl transition-all hover:border-gray-300 hover:shadow-md">
                                    <i class="fas fa-bolt text-2xl text-gray-800 mb-2"></i>
                                    <span class="text-sm font-medium text-gray-900">Paiement rapide</span>
                                </button>
                                
                                {{-- Espèces - uniquement si politique = cash --}}
                                @if($policyType === 'cash')
                                <button type="button" data-payment-method="cash" id="btn-cash"
                                    class="payment-method-btn flex flex-col items-center p-4 border-2 border-gray-200 rounded-xl transition-all hover:border-gray-300 hover:shadow-md">
                                    <i class="fas fa-money-bill-wave text-2xl text-green-600 mb-2"></i>
                                    <span class="text-sm font-medium text-gray-900">Espèces</span>
                                </button>
                                @endif
                            </div>
                            
                            @if($policyType !== 'cash')
                            <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl mb-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">🔒</span>
                                    <div>
                                        <p class="font-medium text-blue-900">
                                            @if($policyType === 'full_prepay')
                                                Paiement intégral requis
                                            @else
                                                Acompte requis ({{ $paymentPolicy['percent'] ?? 30 }}%)
                                            @endif
                                        </p>
                                        <p class="text-xs text-blue-700">Le prestataire exige un paiement en ligne pour cette commande.</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Formulaire carte Stripe --}}
                        <div id="card-payment-section">
                            <h3 class="font-bold text-gray-900 mb-4">Informations de carte</h3>
                            
                            <form id="payment-form">
                                <div id="card-element" class="mb-4"></div>
                                <div id="card-errors" class="text-red-500 text-sm mb-4 hidden"></div>
                                
                                <button type="submit" id="submit-button" 
                                    class="w-full py-4 bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold text-lg rounded-xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    @php
                                        $amountInfo = $foodOrder->calculateAmountDueNow();
                                        $paymentPolicy = $foodOrder->getPaymentPolicy();
                                        $amountToPay = $amountInfo['amount'] ?? $foodOrder->total;
                                        
                                        if ($paymentPolicy['type'] === 'deposit') {
                                            $buttonLabel = "Payer l'acompte " . number_format($amountToPay, 2) . " €";
                                        } elseif ($paymentPolicy['type'] === 'full_prepay') {
                                            $buttonLabel = "Payer " . number_format($amountToPay, 2) . " €";
                                        } else {
                                            $buttonLabel = "Confirmer la commande";
                                        }
                                    @endphp
                                    <span id="button-text">{{ $buttonLabel }}</span>
                                    <span id="spinner" class="hidden">
                                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                            </form>
                        </div>

                        {{-- Bouton paiement espèces --}}
                        <div id="cash-payment-section" class="hidden">
                            <div class="p-4 bg-amber-50 rounded-xl border border-amber-200 mb-4">
                                <p class="text-amber-700">
                                    <strong>💵 Paiement en espèces</strong><br>
                                    Vous paierez <strong>{{ number_format($foodOrder->total, 2) }} €</strong> directement au prestataire lors de la réception de votre commande.
                                </p>
                            </div>
                            
                            <form action="{{ route('food.orders.payment.cash', $foodOrder) }}" method="POST" id="cash-payment-form">
                                @csrf
                                <button type="submit" 
                                    class="w-full py-4 bg-gradient-to-r from-green-500 to-emerald-500 text-white font-bold text-lg rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-3">
                                    <span class="text-2xl">💵</span>
                                    Confirmer le paiement en espèces
                                </button>
                            </form>
                        </div>


                        {{-- Paiement rapide (Apple Pay / Google Pay si disponible) --}}
                        <div id="express-payment-section" class="hidden">
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 mb-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">⚡</span>
                                    <p class="text-sm text-gray-700">Si votre appareil le permet, Apple Pay / Google Pay apparaîtra ici.</p>
                                </div>
                            </div>
                            <div id="payment-request-button" class="w-full"></div>
                            <div id="express-error" class="text-red-500 text-sm mt-3 hidden"></div>
                        </div>
                    </div>
                </div>

                {{-- Récapitulatif --}}
                <div class="lg:col-span-2">
                    <div class="payment-card rounded-2xl shadow-xl p-4 sm:p-6 border border-white/50 sticky top-24">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="text-xl">🧾</span> Récapitulatif
                        </h3>
                        
                        {{-- Prestataire --}}
                        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-100">
                            @if($foodOrder->prestataire->logo)
                                <img src="{{ Storage::url($foodOrder->prestataire->logo) }}" alt="{{ $foodOrder->prestataire->company_name ?? 'Restaurant' }}" class="w-12 h-12 rounded-xl object-cover">
                            @else
                                <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-amber-500 rounded-xl flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($foodOrder->prestataire->company_name ?? 'P', 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <p class="font-semibold text-gray-900">{{ $foodOrder->prestataire->company_name ?? $foodOrder->prestataire->business_name }}</p>
                                <p class="text-sm text-gray-500">{{ $foodOrder->items->count() }} article(s)</p>
                            </div>
                        </div>

                        {{-- Articles --}}
                        <div class="space-y-2 mb-4 max-h-40 overflow-y-auto">
                            @foreach($foodOrder->items as $item)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ $item->quantity }}× {{ $item->product_name }}</span>
                                    <span class="font-medium">{{ number_format($item->total_price, 2) }} €</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Totaux --}}
                        <div class="border-t border-gray-100 pt-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Sous-total</span>
                                <span>{{ number_format($foodOrder->subtotal, 2) }} €</span>
                            </div>
                            @if($foodOrder->delivery_fee > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Livraison</span>
                                    <span>{{ number_format($foodOrder->delivery_fee, 2) }} €</span>
                                </div>
                            @endif
                            @if($foodOrder->service_fee > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Frais de service</span>
                                    <span>{{ number_format($foodOrder->service_fee, 2) }} €</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                                <span>Total</span>
                                <span class="text-orange-600">{{ number_format($foodOrder->total, 2) }} €</span>
                            </div>
                            
                            {{-- Affichage acompte/prépaiement --}}
                            @php
                                $paymentPolicyInfo = $foodOrder->getPaymentPolicy();
                                $paymentPolicyType = $paymentPolicyInfo['type'] ?? 'cash';
                                $amountDueInfo = $foodOrder->calculateAmountDueNow();
                                $amountDue = $amountDueInfo['amount'] ?? $foodOrder->total;
                                $remaining = $foodOrder->total - $amountDue;
                            @endphp
                            
                            @if($paymentPolicyType === 'deposit' && $amountDue < $foodOrder->total)
                                <div class="mt-3 p-3 bg-blue-50 rounded-xl border border-blue-200">
                                    <p class="text-sm font-semibold text-blue-700 mb-1">💰 Acompte requis</p>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-blue-600">À payer maintenant</span>
                                        <span class="font-bold text-blue-700">{{ number_format($amountDue, 2) }} €</span>
                                    </div>
                                    <div class="flex justify-between text-sm mt-1">
                                        <span class="text-gray-500">Solde à la réception</span>
                                        <span class="text-gray-700">{{ number_format($remaining, 2) }} €</span>
                                    </div>
                                </div>
                            @elseif($paymentPolicyType === 'full_prepay')
                                <div class="mt-3 p-3 bg-purple-50 rounded-xl border border-purple-200">
                                    <p class="text-sm font-semibold text-purple-700">💳 Prépaiement intégral</p>
                                    <p class="text-xs text-purple-600 mt-1">Le paiement sera bloqué jusqu'à validation de la commande.</p>
                                </div>
                            @elseif($paymentPolicyType === 'cash')
                                <div class="mt-3 p-3 bg-green-50 rounded-xl border border-green-200">
                                    <p class="text-sm font-semibold text-green-700">💵 Paiement en espèces</p>
                                    <p class="text-xs text-green-600 mt-1">Vous paierez à la réception de la commande.</p>
                                </div>
                            @endif
                            
                            {{-- Info escrow --}}
                            @if($paymentPolicyType !== 'cash')
                            <div class="mt-3 p-3 bg-indigo-50 rounded-xl border border-indigo-200">
                                <div class="flex items-start gap-2">
                                    <span class="text-lg">🔒</span>
                                    <div>
                                        <p class="text-sm font-semibold text-indigo-700">Protection acheteur</p>
                                        <p class="text-xs text-indigo-600 mt-1">Vos fonds sont bloqués en toute sécurité. Ils ne seront libérés au prestataire qu'après validation de votre commande avec le code de confirmation.</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Type de livraison --}}
                        <div class="mt-4 p-3 bg-gray-50 rounded-xl">
                            <div class="flex items-center gap-2 text-sm">
                                @if($foodOrder->delivery_type === 'pickup')
                                    <span class="text-lg">🏪</span>
                                    <span class="text-gray-700">À emporter</span>
                                @else
                                    <span class="text-lg">🚚</span>
                                    <span class="text-gray-700">Livraison</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe('{{ $stripeKey }}');
    const elements = stripe.elements({ locale: 'fr' });
    let currentMethod = 'card';
    let currentClientSecret = null;
    let currentPaymentIntentId = null;
    let currentAmount = null;
    let paymentRequest = null;
    let paymentRequestButton = null;
    
    const cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#1f2937',
                fontFamily: 'system-ui, sans-serif',
                '::placeholder': { color: '#9ca3af' }
            },
            invalid: { color: '#ef4444', iconColor: '#ef4444' }
        }
    });
    
    cardElement.mount('#card-element');
    
    // Gestion des erreurs de carte
    cardElement.on('change', function(event) {
        const errorDiv = document.getElementById('card-errors');
        if (event.error) {
            errorDiv.textContent = event.error.message;
            errorDiv.classList.remove('hidden');
        } else {
            errorDiv.classList.add('hidden');
        }
    });
    
    // Sélection de la méthode de paiement
    function selectPaymentMethod(method) {
        currentMethod = method;
        
        // Cacher toutes les sections
        const sections = ['card-payment-section', 'cash-payment-section', 'express-payment-section'];
        sections.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        });
        
        // Reset tous les boutons
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.classList.remove('border-orange-500', 'bg-orange-50');
            btn.classList.add('border-gray-200');
        });
        
        // Activer le bouton sélectionné
        const activeBtn = document.getElementById('btn-' + method);
        if (activeBtn) {
            activeBtn.classList.remove('border-gray-200');
            activeBtn.classList.add('border-orange-500', 'bg-orange-50');
        }
        
        // Afficher la section correspondante
        switch(method) {
            case 'card':
                document.getElementById('card-payment-section').classList.remove('hidden');
                break;
            case 'express':
                document.getElementById('express-payment-section').classList.remove('hidden');
                break;
            case 'cash':
                document.getElementById('cash-payment-section').classList.remove('hidden');
                break;
        }
    }

    document.querySelectorAll('[data-payment-method]').forEach((button) => {
        button.addEventListener('click', () => {
            selectPaymentMethod(button.dataset.paymentMethod);
        });
    });

    async function ensurePaymentIntent() {
        if (currentClientSecret && currentPaymentIntentId) {
            return { clientSecret: currentClientSecret, paymentIntentId: currentPaymentIntentId, amount: currentAmount };
        }

        const intentResponse = await fetch('{{ route("food.orders.payment.intent", $foodOrder) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const intentData = await intentResponse.json();
        if (intentData.error) throw new Error(intentData.error);

        currentClientSecret = intentData.clientSecret;
        currentPaymentIntentId = intentData.paymentIntentId;
        currentAmount = intentData.amount ?? null;
        return intentData;
    }

    async function setupExpressPayment() {
        try {
            const intentData = await ensurePaymentIntent();
            const amountCents = Math.round((intentData.amount ?? 0) * 100);

            // Stripe Payment Request (Apple Pay / Google Pay)
            paymentRequest = stripe.paymentRequest({
                country: 'FR',
                currency: 'eur',
                total: {
                    label: 'Commande Food #{{ $foodOrder->order_number }}',
                    amount: amountCents,
                },
                requestPayerName: true,
                requestPayerEmail: true,
            });

            const result = await paymentRequest.canMakePayment();
            if (!result) {
                return;
            }

            const expressBtn = document.getElementById('btn-express');
            if (expressBtn) {
                expressBtn.classList.remove('hidden');
                expressBtn.classList.add('flex');
            }

            paymentRequestButton = elements.create('paymentRequestButton', {
                paymentRequest,
                style: {
                    paymentRequestButton: {
                        type: 'default',
                        theme: 'dark',
                        height: '44px',
                    },
                },
            });
            paymentRequestButton.mount('#payment-request-button');

            paymentRequest.on('paymentmethod', async (ev) => {
                const expressError = document.getElementById('express-error');
                try {
                    const data = await ensurePaymentIntent();

                    const { error, paymentIntent } = await stripe.confirmCardPayment(
                        data.clientSecret,
                        { payment_method: ev.paymentMethod.id },
                        { handleActions: false }
                    );

                    if (error) {
                        ev.complete('fail');
                        throw error;
                    }

                    ev.complete('success');

                    // Handle next actions if needed
                    if (paymentIntent.status === 'requires_action') {
                        const { error: actionError, paymentIntent: finalIntent } = await stripe.confirmCardPayment(data.clientSecret);
                        if (actionError) throw actionError;

                        await finalizeFoodPayment(finalIntent.id, 'express');
                    } else {
                        await finalizeFoodPayment(paymentIntent.id, 'express');
                    }
                } catch (err) {
                    if (expressError) {
                        expressError.textContent = err.message || 'Erreur de paiement.';
                        expressError.classList.remove('hidden');
                    }
                }
            });
        } catch (e) {
            // Ignore: express payment isn't available or intent failed; card still works
            console.warn('Express payment unavailable:', e);
        }
    }

    async function finalizeFoodPayment(paymentIntentId, method) {
        const confirmResponse = await fetch('{{ route("food.orders.payment.confirm", $foodOrder) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ payment_intent_id: paymentIntentId, payment_method: method })
        });
        const confirmData = await confirmResponse.json();
        if (confirmData.error) throw new Error(confirmData.error);
        window.location.href = confirmData.redirect;
    }
    
    // Soumission du paiement carte
    const form = document.getElementById('payment-form');
    const submitButton = document.getElementById('submit-button');
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');
    const initialButtonText = buttonText.textContent;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        submitButton.disabled = true;
        buttonText.textContent = 'Traitement...';
        spinner.classList.remove('hidden');
        
        try {
            const intentData = await ensurePaymentIntent();
            
            const { error, paymentIntent } = await stripe.confirmCardPayment(intentData.clientSecret, {
                payment_method: { card: cardElement }
            });
            
            if (error) throw new Error(error.message);
            
            await finalizeFoodPayment(paymentIntent.id, 'card');
            
            buttonText.textContent = '✓ Paiement réussi !';
            spinner.classList.add('hidden');
            
            // finalizeFoodPayment redirects
            
        } catch (error) {
            document.getElementById('card-errors').textContent = error.message;
            document.getElementById('card-errors').classList.remove('hidden');
            submitButton.disabled = false;
            buttonText.textContent = initialButtonText;
            spinner.classList.add('hidden');
        }
    });

    document.getElementById('cash-payment-form')?.addEventListener('submit', function() {
        const cashSubmitButton = this.querySelector('button[type=submit]');
        if (!cashSubmitButton) {
            return;
        }

        cashSubmitButton.disabled = true;
        cashSubmitButton.innerHTML = "<span class='animate-spin inline-block w-5 h-5 border-2 border-white border-t-transparent rounded-full'></span> Traitement en cours...";
    });

    // Init express payment if available
    setupExpressPayment();
</script>
@endpush
@endsection
