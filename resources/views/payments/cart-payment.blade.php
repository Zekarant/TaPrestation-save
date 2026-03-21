@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 pb-28 sm:pb-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-4 sm:p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Paiement du panier</h1>
            <p class="text-gray-600 mb-8">Panier #{{ $cart->id }}</p>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                @if(!($requiresFullPayment ?? false))
                <div class="flex justify-between items-center mb-3">
                    <span class="text-gray-700">Total acompte</span>
                    <span class="font-semibold text-blue-800" id="depositAmount" data-raw="{{ (float) $totals['deposit'] }}">{{ number_format($totals['deposit'], 2) }} €</span>
                </div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-gray-700">Total paiement complet</span>
                    <span class="font-semibold text-gray-900" id="fullAmount" data-raw="{{ (float) $totals['full'] }}">{{ number_format($totals['full'], 2) }} €</span>
                </div>
                @else
                <input type="hidden" id="depositAmount" data-raw="{{ (float) $totals['deposit'] }}">
                <input type="hidden" id="fullAmount" data-raw="{{ (float) $totals['full'] }}">
                @endif
                <div class="{{ !($requiresFullPayment ?? false) ? 'border-t border-blue-300 pt-4' : '' }} flex justify-between items-center">
                    <span class="text-lg font-bold text-gray-900">Total à payer</span>
                    <span class="text-2xl font-bold text-blue-600" id="displayAmount">{{ ($requiresFullPayment ?? false) ? number_format($totals['full'], 2) : number_format($totals['deposit'], 2) }} €</span>
                </div>
            </div>

            @if(!($requiresFullPayment ?? false))
            <div class="mb-8">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Option de paiement</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="relative flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors border-blue-500 bg-blue-50" onclick="selectPaymentOption('deposit')">
                        <div class="flex items-center h-5">
                            <input id="option_deposit" name="payment_option" type="radio" value="deposit" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300" checked>
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="option_deposit" class="font-medium text-gray-700">Payer l'acompte</label>
                            <p class="text-gray-500">{{ number_format($totals['deposit'], 2) }} € maintenant</p>
                        </div>
                    </div>
                    <div class="relative flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors" onclick="selectPaymentOption('full')">
                        <div class="flex items-center h-5">
                            <input id="option_full" name="payment_option" type="radio" value="full" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="option_full" class="font-medium text-gray-700">Payer la totalité</label>
                            <p class="text-gray-500">{{ number_format($totals['full'], 2) }} € maintenant</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Sélection du mode de paiement --}}
            <div class="mb-8">
                <label class="block text-sm font-semibold text-gray-900 mb-3">Mode de paiement</label>
                <input type="hidden" id="selected_payment_method" name="payment_method" value="stripe">
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                    <button type="button" onclick="selectPaymentMethod('stripe')" 
                            class="payment-method-btn flex flex-col items-center justify-center p-3 border-2 rounded-lg transition-all border-blue-500 bg-blue-50" 
                            data-method="stripe">
                        <i class="fas fa-credit-card text-xl mb-1"></i>
                        <span class="text-xs">Carte</span>
                    </button>
                    <button type="button" onclick="selectPaymentMethod('apple_pay')" 
                            class="payment-method-btn flex flex-col items-center justify-center p-3 border-2 rounded-lg transition-all border-gray-300 hover:border-gray-400" 
                            data-method="apple_pay">
                        <i class="fab fa-apple text-xl mb-1"></i>
                        <span class="text-xs">Apple Pay</span>
                    </button>
                    <button type="button" onclick="selectPaymentMethod('google_pay')" 
                            class="payment-method-btn flex flex-col items-center justify-center p-3 border-2 rounded-lg transition-all border-gray-300 hover:border-gray-400" 
                            data-method="google_pay">
                        <i class="fab fa-google text-xl mb-1"></i>
                        <span class="text-xs">Google Pay</span>
                    </button>
                    <button type="button" onclick="selectPaymentMethod('klarna')" 
                            class="payment-method-btn flex flex-col items-center justify-center p-3 border-2 rounded-lg transition-all border-gray-300 hover:border-gray-400" 
                            data-method="klarna">
                        <svg class="w-5 h-5 mb-1" viewBox="0 0 24 24" fill="currentColor"><path d="M4.592 2H19.408A2.592 2.592 0 0122 4.592v14.816A2.592 2.592 0 0119.408 22H4.592A2.592 2.592 0 012 19.408V4.592A2.592 2.592 0 014.592 2zm4.066 13.398a1.398 1.398 0 100 2.796 1.398 1.398 0 000-2.796zm6.684-9.396h-2.286a5.49 5.49 0 01-2.042 4.282l-.766.614 3.056 4.892h2.766l-2.88-4.608a7.463 7.463 0 002.152-5.18zm-6.004 0H6.652v10.002h2.686V6.002z"/></svg>
                        <span class="text-xs">Klarna</span>
                    </button>
                    <button type="button" onclick="selectPaymentMethod('amazon_pay')" 
                            class="payment-method-btn flex flex-col items-center justify-center p-3 border-2 rounded-lg transition-all border-gray-300 hover:border-gray-400" 
                            data-method="amazon_pay">
                        <i class="fab fa-amazon text-xl mb-1"></i>
                        <span class="text-xs">Amazon</span>
                    </button>
                </div>
            </div>

            {{-- Conditions de paiement avec acceptation obligatoire --}}
            @include('components.payment-terms', ['cartItems' => $cart->items])

            {{-- Section Stripe (Carte, Apple Pay, Google Pay) --}}
            <div id="stripe-section" class="payment-section">
                @if(isset($paymentInfo) && $paymentInfo['has_stripe_connect'])
                <form id="payment-form" class="space-y-6">
                    @csrf
                    <input type="hidden" id="payment_type" name="payment_type" value="{{ ($requiresFullPayment ?? false) ? 'full' : 'deposit' }}">
                    <input type="hidden" id="terms_consent_data" name="terms_consent_data" value="">

                    <div id="payment-element"></div>
                    <div id="error-message" class="text-red-600 text-sm hidden"></div>

                    <button id="submit" type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition-colors flex justify-center items-center">
                        <div class="spinner hidden mr-2 w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                        <span id="button-text">Payer <span id="payAmount">{{ ($requiresFullPayment ?? false) ? number_format($totals['full'], 2) : number_format($totals['deposit'], 2) }}</span> €</span>
                    </button>

                    <p class="text-center text-xs text-gray-600 mt-4">
                        <i class="fas fa-lock mr-1"></i> Paiement sécurisé par Stripe.
                    </p>
                </form>
                @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                    <p class="text-yellow-700">Paiement par carte non disponible pour ce vendeur.</p>
                </div>
                @endif
            </div>

            {{-- Section Klarna --}}
            <div id="klarna-section" class="payment-section hidden">
                <div class="space-y-4">
                    <div class="bg-pink-50 border border-pink-200 rounded-lg p-4">
                        <p class="text-sm text-pink-800 font-medium"><i class="fas fa-clock mr-2"></i>Klarna - Payez en 3 fois sans frais</p>
                        <p class="text-xs text-pink-600 mt-1">Divisez votre paiement en 3 mensualités égales.</p>
                    </div>
                    <button type="button" onclick="processKlarnaPayment()" id="klarna-button" 
                            class="w-full bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 rounded-lg transition-colors flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M4.592 2H19.408A2.592 2.592 0 0122 4.592v14.816A2.592 2.592 0 0119.408 22H4.592A2.592 2.592 0 012 19.408V4.592A2.592 2.592 0 014.592 2z"/></svg>
                        Payer <span class="klarna-amount">{{ ($requiresFullPayment ?? false) ? number_format($totals['full'], 2) : number_format($totals['deposit'], 2) }}</span> € avec Klarna
                    </button>
                    <p class="text-center text-xs text-gray-500">Vous serez redirigé vers Klarna pour finaliser</p>
                </div>
            </div>

            {{-- Section Amazon Pay --}}
            <div id="amazon_pay-section" class="payment-section hidden">
                <div class="space-y-4">
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <p class="text-sm text-orange-800 font-medium"><i class="fab fa-amazon mr-2"></i>Amazon Pay</p>
                        <p class="text-xs text-orange-600 mt-1">Utilisez vos informations Amazon pour payer rapidement.</p>
                    </div>
                    <button type="button" onclick="processAmazonPayment()" id="amazon-button" 
                            class="w-full bg-orange-400 hover:bg-orange-500 text-gray-900 font-bold py-3 rounded-lg transition-colors flex justify-center items-center gap-2">
                        <i class="fab fa-amazon"></i>
                        Payer <span class="amazon-amount">{{ ($requiresFullPayment ?? false) ? number_format($totals['full'], 2) : number_format($totals['deposit'], 2) }}</span> € avec Amazon Pay
                    </button>
                    <p class="text-center text-xs text-gray-500">Redirection vers Amazon Pay</p>
                </div>
            </div>

            {{-- Message si aucun moyen de paiement --}}
            @if(!isset($paymentInfo) || (!($paymentInfo['has_stripe_connect'] ?? false)))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                <p class="text-red-700">Aucun moyen de paiement configuré par le vendeur.</p>
            </div>
            @endif

            <div class="mt-6 text-center">
                <a href="{{ route('client.cart.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Retour au panier</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    // Variables globales
    // ESCROW: Si useEscrow est true, le PaymentIntent est créé sur le compte PLATEFORME
    // donc on n'utilise PAS stripeAccount. Sinon, on utilise le compte connecté.
    const useEscrow = {{ ($useEscrow ?? false) ? 'true' : 'false' }};
    const stripeAccountId = useEscrow ? '' : "{{ $paymentInfo['stripe_account_id'] ?? '' }}";
    const stripeOptions = stripeAccountId ? { stripeAccount: stripeAccountId } : {};
    const stripe = Stripe("{{ config('services.stripe.key') }}", stripeOptions);
    let elements = null;
    let paymentElement = null;
    let stripeInitialized = false;
    let paymentElementReady = false;
    let currentClientSecret = null;
    let currentPaymentMethod = 'stripe';

    const depositAmount = parseFloat(document.getElementById('depositAmount')?.dataset?.raw || '0');
    const fullAmount = parseFloat(document.getElementById('fullAmount')?.dataset?.raw || '0');

    // Sélection du mode de paiement
    function selectPaymentMethod(method) {
        currentPaymentMethod = method;
        document.getElementById('selected_payment_method').value = method;
        
        // Masquer toutes les sections
        document.querySelectorAll('.payment-section').forEach(section => {
            section.classList.add('hidden');
        });
        
        // Afficher la section correspondante
        const sectionId = method + '-section';
        const section = document.getElementById(sectionId);
        if (section) {
            section.classList.remove('hidden');
        }
        
        // Mettre à jour les boutons
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.classList.remove('border-blue-500', 'bg-blue-50');
            btn.classList.add('border-gray-300');
        });
        const activeBtn = document.querySelector(`.payment-method-btn[data-method="${method}"]`);
        if (activeBtn) {
            activeBtn.classList.remove('border-gray-300');
            activeBtn.classList.add('border-blue-500', 'bg-blue-50');
        }
        
        // Initialiser Stripe si nécessaire
        if ((method === 'stripe' || method === 'apple_pay' || method === 'google_pay') && !stripeInitialized) {
            initializeStripe();
        }
    }

    const urlParams = new URLSearchParams(window.location.search);
    const redirectedClientSecret = urlParams.get('payment_intent_client_secret');

    if (redirectedClientSecret) {
        finalizePaymentIntent(redirectedClientSecret).catch((err) => {
            showMessage(err.message || 'Erreur lors de la finalisation du paiement');
        });
    } else {
        initializeStripe();
    }

    async function initializeStripe() {
        if (stripeInitialized) return;

        const paymentType = document.getElementById('payment_type')?.value || 'full';
        
        // Afficher un indicateur de chargement
        const paymentElementContainer = document.getElementById('payment-element');
        if (!paymentElementContainer) return;
        
        paymentElementContainer.innerHTML = '<div class="flex items-center justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><span class="ml-3 text-gray-600">Chargement du formulaire de paiement...</span></div>';

        try {
            const response = await fetch("{{ route('client.payments.cart.intent') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ payment_type: paymentType }),
            });

            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || 'Erreur lors de la création du paiement');
            }

            currentClientSecret = data.clientSecret;
            
            // Configuration avec tous les modes de paiement activés
            const appearance = { 
                theme: 'stripe',
                variables: {
                    colorPrimary: '#2563eb',
                    colorBackground: '#ffffff',
                    colorText: '#1f2937',
                    colorDanger: '#dc2626',
                    fontFamily: 'system-ui, sans-serif',
                    spacingUnit: '4px',
                    borderRadius: '8px',
                }
            };
            
            elements = stripe.elements({ 
                appearance, 
                clientSecret: currentClientSecret,
                locale: 'fr'
            });

            // Effacer le conteneur
            paymentElementContainer.innerHTML = '';
            
            // Créer le Payment Element avec toutes les options
            paymentElement = elements.create("payment", {
                layout: {
                    type: 'tabs',
                    defaultCollapsed: false,
                },
                wallets: {
                    applePay: 'auto',
                    googlePay: 'auto',
                },
                paymentMethodOrder: ['card', 'apple_pay', 'google_pay', 'link', 'klarna']
            });
            
            // Écouter l'événement ready
            paymentElement.on('ready', () => {
                paymentElementReady = true;
            });
            
            // Écouter les erreurs
            paymentElement.on('loaderror', (event) => {
                showMessage('Erreur de chargement: ' + (event.error?.message || 'Impossible de charger le formulaire'));
            });
            
            paymentElement.mount("#payment-element");
            stripeInitialized = true;
            
        } catch (error) {
            console.error('Stripe init error:', error);
            paymentElementContainer.innerHTML = '';
            showMessage(error.message || 'Erreur lors de l\'initialisation du paiement');
        }
    }

    async function handleSubmit(e) {
        e.preventDefault();
        
        // Vérifier que les conditions sont acceptées
        const termsCheckbox = document.getElementById('accept_payment_terms');
        if (termsCheckbox && !termsCheckbox.checked) {
            showMessage('Veuillez accepter les conditions de paiement sécurisé avant de continuer.');
            termsCheckbox.focus();
            return;
        }

        // Enregistrer les données de consentement
        const consentData = {
            accepted: true,
            version: document.querySelector('[name="terms_version"]')?.value || 'v1.0',
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent
        };
        document.getElementById('terms_consent_data').value = JSON.stringify(consentData);
        
        // Vérifier que le Payment Element est prêt
        if (!elements || !paymentElement) {
            showMessage('Le formulaire de paiement n\'est pas encore chargé. Veuillez patienter ou rafraîchir la page.');
            return;
        }
        
        setLoading(true);

        try {
            const { error } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: window.location.href,
                },
                redirect: 'if_required'
            });

            if (error) {
                throw error;
            }

            if (!currentClientSecret) {
                throw new Error('Client secret manquant');
            }

            await finalizePaymentIntent(currentClientSecret, document.getElementById('payment_type').value);
        } catch (error) {
            showMessage(error.message || String(error));
            setLoading(false);
            return;
        }

        setLoading(false);
    }

    const paymentForm = document.getElementById("payment-form");
    if (paymentForm) {
        paymentForm.addEventListener("submit", handleSubmit);
    }

    async function finalizePaymentIntent(clientSecret, fallbackPaymentType = null) {
        const { paymentIntent } = await stripe.retrievePaymentIntent(clientSecret);
        if (!paymentIntent?.id) {
            throw new Error('Paiement introuvable');
        }

        const paymentType = paymentIntent?.metadata?.payment_type || fallbackPaymentType || document.getElementById('payment_type').value;

        const response = await fetch("{{ route('client.payments.cart.confirm') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({
                payment_type: paymentType,
                payment_intent_id: paymentIntent.id,
            }),
        });

        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.error || data.message || 'Erreur lors de la confirmation du paiement');
        }

        window.location.href = "{{ route('client.cart.index') }}?payment_success=true";
    }

    function selectPaymentOption(type) {
        document.getElementById('payment_type').value = type;
        const amount = type === 'deposit' ? depositAmount : fullAmount;
        document.getElementById('payAmount').innerText = amount.toFixed(2);
        document.getElementById('displayAmount').innerText = amount.toFixed(2) + ' €';
        
        // Mettre à jour les montants dans toutes les sections
        document.querySelectorAll('.klarna-amount, .amazon-amount').forEach(el => {
            el.innerText = amount.toFixed(2);
        });
        
        // Mettre à jour le style des options
        document.querySelectorAll('[onclick^="selectPaymentOption"]').forEach(el => {
            el.classList.remove('border-blue-500', 'bg-blue-50');
        });
        document.querySelector(`[onclick="selectPaymentOption('${type}')"]`)?.classList.add('border-blue-500', 'bg-blue-50');

        // Réinitialiser Stripe avec le nouveau montant
        stripeInitialized = false;
        paymentElementReady = false;
        currentClientSecret = null;
        elements = null;
        paymentElement = null;
        if (currentPaymentMethod === 'stripe' || currentPaymentMethod === 'apple_pay' || currentPaymentMethod === 'google_pay') {
            initializeStripe();
        }
    }

    // Traitement Klarna
    async function processKlarnaPayment() {
        const termsCheckbox = document.getElementById('accept_payment_terms');
        if (termsCheckbox && !termsCheckbox.checked) {
            alert('Veuillez accepter les conditions de paiement sécurisé avant de continuer.');
            termsCheckbox.focus();
            return;
        }

        const btn = document.getElementById('klarna-button');
        btn.disabled = true;
        btn.innerHTML = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>';

        try {
            // Créer un PaymentIntent pour Klarna si pas déjà fait
            if (!currentClientSecret) {
                const paymentType = document.getElementById('payment_type')?.value || 'full';
                const response = await fetch("{{ route('client.payments.cart.intent') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    body: JSON.stringify({ payment_type: paymentType, payment_method_types: ['klarna'] }),
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.error || 'Erreur');
                currentClientSecret = data.clientSecret;
            }

            const { error } = await stripe.confirmKlarnaPayment(currentClientSecret, {
                payment_method: {
                    billing_details: {
                        email: {!! Js::from(auth()->user()->email ?? '') !!},
                        address: { country: 'FR' }
                    }
                },
                return_url: window.location.href
            });

            if (error) throw error;
        } catch (error) {
            alert(error.message || 'Erreur Klarna');
        }

        btn.disabled = false;
        btn.innerHTML = '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M4.592 2H19.408A2.592 2.592 0 0122 4.592v14.816A2.592 2.592 0 0119.408 22H4.592A2.592 2.592 0 012 19.408V4.592A2.592 2.592 0 014.592 2z"/></svg> Payer avec Klarna';
    }

    // Traitement Amazon Pay
    function processAmazonPayment() {
        const termsCheckbox = document.getElementById('accept_payment_terms');
        if (termsCheckbox && !termsCheckbox.checked) {
            alert('Veuillez accepter les conditions de paiement sécurisé avant de continuer.');
            termsCheckbox.focus();
            return;
        }

        alert('Amazon Pay sera bientôt disponible. Veuillez utiliser un autre mode de paiement.');
    }

    function showMessage(messageText) {
        const messageContainer = document.querySelector("#error-message");
        if (messageContainer) {
            messageContainer.classList.remove("hidden");
            messageContainer.textContent = messageText;
        }
    }

    function setLoading(isLoading) {
        const submitBtn = document.querySelector("#submit");
        const spinner = document.querySelector(".spinner");
        const buttonText = document.querySelector("#button-text");
        
        if (!submitBtn) return;
        
        if (isLoading) {
            submitBtn.disabled = true;
            if (spinner) spinner.classList.remove("hidden");
            if (buttonText) buttonText.classList.add("hidden");
        } else {
            submitBtn.disabled = false;
            if (spinner) spinner.classList.add("hidden");
            if (buttonText) buttonText.classList.remove("hidden");
        }
    }
</script>
@endpush
@endsection
