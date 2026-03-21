@extends('layouts.app')

@section('title', 'Paiement préalable - ' . $service->title)

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
<div class="min-h-screen bg-gradient-to-br from-orange-50 via-amber-50 to-yellow-50 py-6">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            
            {{-- Header --}}
            <div class="mb-6">
                <a href="{{ route('bookings.create', $service) }}" class="inline-flex items-center gap-2 text-orange-600 hover:text-orange-700 mb-4">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Modifier la réservation
                </a>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">💳 Paiement préalable obligatoire</h1>
                <p class="text-gray-500 mt-1">
                    Ce service requiert un 
                    <strong class="text-orange-600">
                        {{ $paymentType === 'full' ? 'paiement complet' : 'acompte de ' . $service->deposit_percentage . '%' }}
                    </strong>
                    avant d'envoyer la demande au prestataire.
                </p>
            </div>

            {{-- Info remboursement --}}
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-blue-800">🔄 Remboursement automatique garanti</p>
                        <p class="text-sm text-blue-700 mt-1">
                            Si le prestataire refuse votre demande de réservation, vous serez <strong>automatiquement remboursé(e)</strong> du montant payé. Vous recevrez une notification par email.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                
                {{-- Formulaire de paiement --}}
                <div class="lg:col-span-3">
                    <div class="payment-card rounded-2xl shadow-xl p-6 border border-white/50">
                        
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

                        {{-- Formulaire carte Stripe --}}
                        <div id="card-payment-section">
                            <h3 class="font-bold text-gray-900 mb-4">Mode de paiement</h3>
                            
                            {{-- Sélection des modes de paiement --}}
                            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 mb-6">
                                <button type="button" onclick="selectPaymentMethod('stripe')" id="btn-stripe"
                                    class="payment-method-btn flex flex-col items-center justify-center p-3 border-2 rounded-lg transition-all border-orange-500 bg-orange-50">
                                    <i class="fas fa-credit-card text-xl text-orange-600 mb-1"></i>
                                    <span class="text-xs">Carte</span>
                                </button>
                                <button type="button" onclick="selectPaymentMethod('apple_pay')" id="btn-apple_pay"
                                    class="payment-method-btn flex flex-col items-center justify-center p-3 border-2 rounded-lg transition-all border-gray-200 hover:border-gray-300">
                                    <i class="fab fa-apple text-xl mb-1"></i>
                                    <span class="text-xs">Apple Pay</span>
                                </button>
                                <button type="button" onclick="selectPaymentMethod('google_pay')" id="btn-google_pay"
                                    class="payment-method-btn flex flex-col items-center justify-center p-3 border-2 rounded-lg transition-all border-gray-200 hover:border-gray-300">
                                    <i class="fab fa-google text-xl mb-1"></i>
                                    <span class="text-xs">Google Pay</span>
                                </button>
                                <button type="button" onclick="selectPaymentMethod('klarna')" id="btn-klarna"
                                    class="payment-method-btn flex flex-col items-center justify-center p-3 border-2 rounded-lg transition-all border-gray-200 hover:border-gray-300">
                                    <svg class="w-8 h-5 mb-1" viewBox="0 0 100 40" fill="none">
                                        <path d="M10 8h8v24h-8V8zm12 0c0 6.627 2.686 12.631 7.029 16.971L22 32h9.6l8-8c-4.418 0-8-3.582-8-8V8H22zm24 20a4 4 0 100-8 4 4 0 000 8z" fill="#FFB3C7"/>
                                    </svg>
                                    <span class="text-xs">Klarna</span>
                                </button>
                                <button type="button" onclick="selectPaymentMethod('amazon_pay')" id="btn-amazon_pay"
                                    class="payment-method-btn flex flex-col items-center justify-center p-3 border-2 rounded-lg transition-all border-gray-200 hover:border-gray-300">
                                    <i class="fab fa-amazon text-xl text-orange-500 mb-1"></i>
                                    <span class="text-xs">Amazon</span>
                                </button>
                            </div>
                            
                            <input type="hidden" id="selected_payment_method" value="stripe">
                            
                            {{-- Section Stripe (Payment Element) --}}
                            <div id="stripe-section">
                                <form id="payment-form">
                                    @csrf
                                    <div id="payment-element" class="mb-4 p-4 border-2 border-gray-200 rounded-xl bg-white min-h-[100px]">
                                        <div class="flex items-center justify-center py-4">
                                            <svg class="animate-spin h-6 w-6 text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            <span class="ml-3 text-gray-500">Chargement...</span>
                                        </div>
                                    </div>
                                    <div id="card-errors" class="text-red-500 text-sm mb-4 hidden"></div>
                                    
                                    <button type="submit" id="submit-button" 
                                        class="w-full py-4 bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold text-lg rounded-xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <span id="button-text">Payer {{ number_format($amount, 2) }} € et envoyer la demande</span>
                                        <span id="spinner" class="hidden">
                                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                </form>
                            </div>
                            
                            {{-- Section Amazon Pay --}}
                            <div id="amazon-section" class="hidden">
                                <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-4">
                                    <div class="flex items-center gap-3">
                                        <i class="fab fa-amazon text-2xl text-orange-500"></i>
                                        <p class="text-sm text-orange-800">Paiement via votre compte Amazon (fonctionnalité bientôt disponible).</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Annuler --}}
                        <div class="mt-4 text-center">
                            <a href="{{ route('bookings.create', $service) }}" class="text-gray-500 hover:text-gray-700 text-sm">
                                Annuler et revenir à la sélection
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Récapitulatif --}}
                <div class="lg:col-span-2">
                    <div class="payment-card rounded-2xl shadow-xl p-6 border border-white/50 sticky top-24">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="text-xl">🧾</span> Récapitulatif
                        </h3>
                        
                        {{-- Prestataire --}}
                        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-100">
                            @if($prestataire->logo)
                                <img src="{{ Storage::url($prestataire->logo) }}" alt="{{ $prestataire->company_name ?? $prestataire->business_name ?? 'Prestataire' }}" class="w-12 h-12 rounded-xl object-cover">
                            @else
                                <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-amber-500 rounded-xl flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($prestataire->company_name ?? $prestataire->business_name ?? 'P', 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <p class="font-semibold text-gray-900">{{ $prestataire->company_name ?? $prestataire->business_name }}</p>
                                <p class="text-sm text-gray-500">Prestataire</p>
                            </div>
                        </div>

                        {{-- Service --}}
                        <div class="mb-4 pb-4 border-b border-gray-100">
                            <div class="flex items-start gap-3">
                                @if($service->cover_image)
                                    <img src="{{ Storage::url($service->cover_image) }}" alt="{{ $service->title ?? 'Service' }}" class="w-16 h-16 rounded-lg object-cover">
                                @else
                                    <div class="w-16 h-16 bg-gradient-to-br from-gray-200 to-gray-300 rounded-lg flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900">{{ $service->title }}</p>
                                    <p class="text-sm text-gray-500">{{ $service->category->first()?->name ?? 'Service' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Créneaux sélectionnés --}}
                        <div class="mb-4 pb-4 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-700 mb-2">Créneaux sélectionnés ({{ count($selectedSlots) }})</p>
                            <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto">
                                @foreach($selectedSlots as $slot)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-orange-100 text-orange-700">
                                        {{ \Carbon\Carbon::parse($slot)->format('d/m/Y H:i') }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Notes client --}}
                        @if($clientNotes)
                        <div class="mb-4 pb-4 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-700 mb-1">Vos notes</p>
                            <p class="text-sm text-gray-600 italic">{{ $clientNotes }}</p>
                        </div>
                        @endif

                        {{-- Totaux --}}
                        <div class="space-y-2">
                            {{-- Prix du service --}}
                            <div class="flex justify-between text-sm p-2 bg-white rounded">
                                <span class="text-gray-700">📦 Prix du service</span>
                                <span class="font-semibold">{{ number_format($totalPrice, 2) }} €</span>
                            </div>
                            
                            @if($paymentType === 'deposit')
                                {{-- Section ACOMPTE --}}
                                <div class="p-3 bg-blue-100 border-l-4 border-blue-500 rounded-r">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <span class="font-semibold text-blue-800">💳 ACOMPTE</span>
                                            <p class="text-xs text-blue-600 mt-1">{{ $service->deposit_percentage }}% du montant total</p>
                                            <p class="text-xs text-blue-500">→ Déduit du prix, non remboursable si vous annulez</p>
                                        </div>
                                        <span class="font-bold text-blue-800 text-lg">{{ number_format($amount, 2) }} €</span>
                                    </div>
                                </div>
                                
                                {{-- Solde restant --}}
                                <div class="flex justify-between text-sm text-gray-500 p-2 bg-gray-50 rounded">
                                    <span>💵 Solde à payer après confirmation</span>
                                    <span class="font-semibold">{{ number_format($totalPrice - $amount, 2) }} €</span>
                                </div>
                            @else
                                {{-- Paiement intégral --}}
                                <div class="p-3 bg-green-100 border-l-4 border-green-500 rounded-r">
                                    <div class="flex justify-between items-start gap-3">
                                        <div class="flex-1">
                                            <span class="font-semibold text-green-800">💰 PAIEMENT INTÉGRAL</span>
                                            <p class="text-xs text-green-600 mt-1">100% du montant payé maintenant</p>
                                            <p class="text-xs text-green-500">→ Aucun solde à régler ultérieurement</p>
                                        </div>
                                        <span class="font-bold text-green-800 text-lg whitespace-nowrap">{{ number_format($amount, 2) }} €</span>
                                    </div>
                                </div>
                            @endif
                            
                            {{-- Total à payer maintenant --}}
                            <div class="border-t-2 border-orange-400 pt-3 mt-3">
                                <div class="flex justify-between items-center bg-gradient-to-r from-orange-500 to-amber-500 text-white p-4 rounded-lg">
                                    <span class="text-lg font-bold">À payer maintenant</span>
                                    <span class="text-2xl font-bold">{{ number_format($amount, 2) }} €</span>
                                </div>
                            </div>
                            
                            {{-- Info remboursement --}}
                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-start gap-2 text-blue-800 text-sm">
                                    <span>🔄</span>
                                    <div>
                                        <p class="font-medium">Garantie remboursement</p>
                                        <p class="text-xs">Si le prestataire refuse votre demande, vous serez <strong>automatiquement remboursé(e)</strong>.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Type de paiement --}}
                        <div class="mt-4 p-3 bg-gray-50 rounded-xl">
                            <div class="flex items-center gap-2 text-sm">
                                @if($paymentType === 'full')
                                    <span class="text-lg">💰</span>
                                    <span class="text-gray-700">Paiement intégral - pas de solde à régler</span>
                                @else
                                    <span class="text-lg">💳</span>
                                    <span class="text-gray-700">Acompte {{ $service->deposit_percentage }}% - solde de {{ number_format($totalPrice - $amount, 2) }}€ après confirmation</span>
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
    let elements = null;
    let paymentElement = null;
    let stripeInitialized = false;
    let currentClientSecret = null;
    let currentPaymentMethod = 'stripe';
    const amount = {{ $amount }};
    
    // Sélection du mode de paiement
    function selectPaymentMethod(method) {
        currentPaymentMethod = method;
        document.getElementById('selected_payment_method').value = method;
        
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
        
        // Masquer toutes les sections
        document.getElementById('stripe-section').classList.add('hidden');
        document.getElementById('amazon-section').classList.add('hidden');
        
        // Afficher la section correspondante
        if (method === 'amazon_pay') {
            document.getElementById('amazon-section').classList.remove('hidden');
        } else {
            // stripe, apple_pay, google_pay, klarna utilisent tous le Payment Element
            document.getElementById('stripe-section').classList.remove('hidden');
            if (!stripeInitialized) initializeStripe();
        }
    }
    
    // Initialiser Stripe Payment Element
    async function initializeStripe() {
        if (stripeInitialized) return;
        
        try {
            // Créer le Payment Intent
            const response = await fetch('{{ route("bookings.prepayment.intent") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || data.message || 'Erreur serveur: ' + response.status);
            }
            
            if (data.error) throw new Error(data.error);
            
            if (!data.clientSecret) {
                throw new Error('Client secret manquant dans la réponse');
            }
            
            currentClientSecret = data.clientSecret;
            
            // Créer les Elements Stripe
            elements = stripe.elements({
                clientSecret: currentClientSecret,
                appearance: {
                    theme: 'stripe',
                    variables: {
                        colorPrimary: '#f97316',
                        colorBackground: '#ffffff',
                        colorText: '#1f2937',
                        fontFamily: 'system-ui, sans-serif',
                        borderRadius: '8px',
                    }
                },
                locale: 'fr'
            });
            
            // Créer le Payment Element
            paymentElement = elements.create('payment', {
                layout: { type: 'tabs', defaultCollapsed: false },
                wallets: { applePay: 'auto', googlePay: 'auto' },
                paymentMethodOrder: ['card', 'apple_pay', 'google_pay', 'link', 'klarna']
            });
            
            document.getElementById('payment-element').innerHTML = '';
            paymentElement.mount('#payment-element');
            
            // Attendre que le Payment Element soit prêt
            paymentElement.on('ready', function() {
                stripeInitialized = true;
                document.getElementById('card-errors').classList.add('hidden');
            });
            
            paymentElement.on('loaderror', function(event) {
                console.error('Payment Element load error:', event);
                const errorMsg = event.error?.message || 'Erreur de chargement du formulaire de paiement.';
                document.getElementById('card-errors').textContent = errorMsg;
                document.getElementById('card-errors').classList.remove('hidden');
            });
            
            // Marquer comme initialisé après un court délai si l'événement ready ne se déclenche pas
            setTimeout(() => {
                if (!stripeInitialized) stripeInitialized = true;
            }, 2000);
            
        } catch (error) {
            console.error('Stripe init error:', error);
            document.getElementById('card-errors').textContent = error.message || 'Erreur de chargement du paiement.';
            document.getElementById('card-errors').classList.remove('hidden');
        }
    }
    
    // Soumission du paiement Stripe
    const form = document.getElementById('payment-form');
    const submitButton = document.getElementById('submit-button');
    const buttonText = document.getElementById('button-text');
    const spinner = document.getElementById('spinner');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!stripeInitialized || !elements || !paymentElement) {
            document.getElementById('card-errors').textContent = 'Formulaire de paiement non chargé. Veuillez patienter ou rafraîchir la page.';
            document.getElementById('card-errors').classList.remove('hidden');
            // Tenter de réinitialiser Stripe
            stripeInitialized = false;
            await initializeStripe();
            return;
        }
        
        submitButton.disabled = true;
        buttonText.textContent = 'Traitement en cours...';
        spinner.classList.remove('hidden');
        
        try {
            // Confirmer le paiement avec Stripe
            const { error, paymentIntent } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: '{{ route("bookings.prepayment.process") }}?redirect=true'
                },
                redirect: 'if_required'
            });
            
            if (error) {
                throw new Error(error.message);
            }
            
            if (paymentIntent && paymentIntent.status === 'succeeded') {
                // Confirmer et créer la réservation
                const confirmResponse = await fetch('{{ route("bookings.prepayment.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        payment_intent_id: paymentIntent.id
                    })
                });
                
                const confirmData = await confirmResponse.json();
                
                if (confirmData.error) {
                    throw new Error(confirmData.error);
                }
                
                // Succès !
                buttonText.textContent = '✓ Paiement réussi ! Réservation envoyée...';
                spinner.classList.add('hidden');
                
                setTimeout(() => {
                    window.location.href = confirmData.redirect;
                }, 1500);
            }
            
        } catch (error) {
            document.getElementById('card-errors').textContent = error.message;
            document.getElementById('card-errors').classList.remove('hidden');
            
            submitButton.disabled = false;
            buttonText.textContent = 'Payer {{ number_format($amount, 2) }} € et envoyer la demande';
            spinner.classList.add('hidden');
        }
    });
    
    // Initialiser Stripe au chargement
    document.addEventListener('DOMContentLoaded', function() {
        initializeStripe();
    });
</script>
@endpush
@endsection
