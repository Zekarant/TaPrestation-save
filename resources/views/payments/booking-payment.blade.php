@extends('layouts.app')

@php
    try {
        $paymentSettings = function_exists('get_payment_settings') ? get_payment_settings() : [];
    } catch (\Throwable $e) {
        $paymentSettings = [];
    }

    try {
        $availableMethods = function_exists('get_available_payment_methods') ? get_available_payment_methods() : [];
    } catch (\Throwable $e) {
        $availableMethods = [];
    }
    
    // Fallback: si aucune méthode n'est disponible, activer les méthodes par défaut
    if (empty($availableMethods)) {
        $availableMethods = [
            'stripe' => ['name' => 'Carte bancaire', 'icon' => 'fas fa-credit-card'],
            'apple_pay' => ['name' => 'Apple Pay', 'icon' => 'fab fa-apple-pay'],
            'google_pay' => ['name' => 'Google Pay', 'icon' => 'fab fa-google-pay'],
            'klarna' => ['name' => 'Klarna', 'icon' => 'fab fa-klarna'],
            'amazon_pay' => ['name' => 'Amazon Pay', 'icon' => 'fab fa-amazon'],
            'cash' => ['name' => 'Espèces', 'icon' => 'fas fa-money-bill-wave'],
        ];
    }
    
    // S'assurer que PayPal et virement ne sont jamais proposés
    unset($availableMethods['paypal'], $availableMethods['virement'], $availableMethods['iban']);

    $paymentRequirement = function_exists('normalize_payment_requirement_for_mode')
        ? normalize_payment_requirement_for_mode($booking->service?->payment_requirement ?? 'none')
        : ($booking->service?->payment_requirement ?? 'none');
    $depositPct = (float) ($booking->service?->deposit_percentage ?? 0);

    $requirePaymentBeforeBooking = (bool) ($paymentSettings['require_payment_before_booking'] ?? false);
    $cashAllowedByCriteria = ($paymentRequirement === 'none') && !$requirePaymentBeforeBooking;

    $currentRouteName = request()->route()?->getName();
    $isPrestataireContext = is_string($currentRouteName) && str_starts_with($currentRouteName, 'prestataire.');
    $isClientContext = is_string($currentRouteName) && str_starts_with($currentRouteName, 'client.');

    $routeForm = $isPrestataireContext && \Illuminate\Support\Facades\Route::has('prestataire.payments.form')
        ? route('prestataire.payments.form', $booking)
        : (\Illuminate\Support\Facades\Route::has('client.payments.form')
            ? route('client.payments.form', $booking)
            : (\Illuminate\Support\Facades\Route::has('payment.form') ? route('payment.form', $booking) : '#'));

    $routeIntent = $isPrestataireContext && \Illuminate\Support\Facades\Route::has('prestataire.payments.intent')
        ? route('prestataire.payments.intent', $booking)
        : (\Illuminate\Support\Facades\Route::has('client.payments.intent')
            ? route('client.payments.intent', $booking)
            : (\Illuminate\Support\Facades\Route::has('payment.intent') ? route('payment.intent', $booking) : '#'));

    $routeConfirm = $isPrestataireContext && \Illuminate\Support\Facades\Route::has('prestataire.payments.confirm')
        ? route('prestataire.payments.confirm', $booking)
        : (\Illuminate\Support\Facades\Route::has('client.payments.confirm')
            ? route('client.payments.confirm', $booking)
            : (\Illuminate\Support\Facades\Route::has('payment.confirm') ? route('payment.confirm', $booking) : '#'));

    $routeBack = $isPrestataireContext && \Illuminate\Support\Facades\Route::has('prestataire.payments.index')
        ? route('prestataire.payments.index')
        : (\Illuminate\Support\Facades\Route::has('client.bookings.index') ? route('client.bookings.index') : url('/'));

    // Stripe 3DS return URL (server callback records the transaction)
    $routeSuccess = \Illuminate\Support\Facades\Route::has('payment.success')
        ? route('payment.success', $booking)
        : $routeBack;

    $routeCash = $isPrestataireContext && \Illuminate\Support\Facades\Route::has('prestataire.payments.cash')
        ? route('prestataire.payments.cash', $booking)
        : (\Illuminate\Support\Facades\Route::has('client.payments.cash')
            ? route('client.payments.cash', $booking)
            : (\Illuminate\Support\Facades\Route::has('payment.cash') ? route('payment.cash', $booking) : '#'));
@endphp

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-8 pb-28 sm:pb-8">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            
            {{-- Retour --}}
            <a href="{{ $routeBack }}" class="inline-flex items-center text-blue-600 hover:text-blue-700 mb-6">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour à mes réservations
            </a>
            
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-4 sm:px-8 py-6 text-white">
                    <h1 class="text-2xl font-bold">💳 Paiement sécurisé</h1>
                    <p class="text-blue-100 mt-1">Réservation #{{ $booking->booking_number }}</p>
                </div>
                
                <div class="p-4 sm:p-8">
                    {{-- Critères de paiement du prestataire --}}
                    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-6">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-sliders-h text-blue-600"></i>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-900">Critères de paiement de l'annonce</div>
                                <div class="text-sm text-gray-600 mt-1">
                                    @if($paymentRequirement === 'full')
                                        Paiement total requis pour valider la réservation.
                                    @elseif($paymentRequirement === 'deposit')
                                        Acompte requis ({{ $depositPct > 0 ? $depositPct . '%' : 'pourcentage non défini' }}).
                                    @else
                                        Aucun paiement n'est requis à l'avance.
                                    @endif
                                </div>
                                @if($requirePaymentBeforeBooking)
                                    <div class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mt-3">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Le paiement avant réservation est requis par la plateforme.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Résumé de la réservation --}}
                    <div class="bg-gray-50 rounded-xl p-6 mb-8">
                        <h3 class="font-semibold text-gray-900 mb-4">📋 Détails de la réservation</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Service</p>
                                <p class="font-medium text-gray-900">{{ $booking->service?->name ?? 'Service' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Prestataire</p>
                                <p class="font-medium text-gray-900">{{ $booking->prestataire?->user?->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Date</p>
                                <p class="font-medium text-gray-900">{{ $booking->start_datetime?->format('d/m/Y à H:i') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Statut paiement</p>
                                <p class="font-medium {{ $booking->payment_status === 'paid' ? 'text-green-600' : ($booking->payment_status === 'deposit_paid' ? 'text-blue-600' : 'text-yellow-600') }}">
                                    @if($booking->payment_status === 'paid')
                                        ✅ Payé
                                    @elseif($booking->payment_status === 'deposit_paid')
                                        💰 Acompte payé
                                    @else
                                        ⏳ En attente
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Montants --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
                        <div class="space-y-3">
                            <div class="flex justify-between text-gray-700">
                                <span>Montant total du service</span>
                                <span class="font-semibold">{{ number_format($booking->total_price, 2) }} €</span>
                            </div>

                            @if($booking->deposit_amount > 0)
                            <div class="flex justify-between text-blue-700">
                                @if($booking->total_price > 0)
                                    <span>Acompte ({{ round(($booking->deposit_amount / $booking->total_price) * 100) }}%)</span>
                                @else
                                    <span>Acompte</span>
                                @endif
                                <span class="font-semibold">{{ number_format($booking->deposit_amount, 2) }} €</span>
                            </div>
                            @endif

                            <div class="border-t border-blue-200 pt-3 flex justify-between text-lg font-bold">
                                <span class="text-gray-900">Montant à payer</span>
                                <span class="text-blue-600" id="displayAmount">
                                    {{ number_format($booking->deposit_amount > 0 && $booking->payment_status === 'pending' ? $booking->deposit_amount : ($booking->payment_status === 'deposit_paid' ? max(0, $booking->total_price - $booking->deposit_amount) : $booking->total_price), 2) }} €
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Options de paiement (acompte/total) --}}
                    @if($booking->deposit_amount > 0 && $booking->payment_status === 'pending')
                    <div class="mb-8">
                        <label class="block text-sm font-semibold text-gray-900 mb-3">Choisir le montant à payer</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="payment-option relative flex items-center p-4 border-2 border-blue-500 bg-blue-50 rounded-xl cursor-pointer transition-all hover:shadow-md" onclick="selectPaymentOption('deposit')">
                                <input type="radio" name="payment_option" value="deposit" checked class="hidden">
                                <div class="flex-1">
                                    <span class="font-medium text-gray-900">Payer l'acompte</span>
                                    <p class="text-sm text-gray-500">{{ number_format($booking->deposit_amount, 2) }} € maintenant</p>
                                </div>
                                <div class="w-5 h-5 border-2 border-blue-500 rounded-full flex items-center justify-center">
                                    <div class="w-3 h-3 bg-blue-500 rounded-full" id="dot-deposit"></div>
                                </div>
                            </label>
                            <label class="payment-option relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer transition-all hover:border-gray-300 hover:shadow-md" onclick="selectPaymentOption('full')">
                                <input type="radio" name="payment_option" value="full" class="hidden">
                                <div class="flex-1">
                                    <span class="font-medium text-gray-900">Payer la totalité</span>
                                    <p class="text-sm text-gray-500">{{ number_format($booking->total_price, 2) }} € maintenant</p>
                                </div>
                                <div class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center" id="circle-full">
                                    <div class="w-3 h-3 rounded-full hidden" id="dot-full"></div>
                                </div>
                            </label>
                        </div>
                    </div>
                    @endif

                    @if($booking->payment_status === 'deposit_paid')
                    <div class="mb-8 p-4 bg-green-50 border border-green-200 rounded-xl">
                        <p class="text-green-700">
                            <strong>✅ Acompte déjà payé :</strong> {{ number_format($booking->deposit_amount, 2) }} €<br>
                            <span class="text-sm">Il vous reste {{ number_format(max(0, $booking->total_price - $booking->deposit_amount), 2) }} € à payer.</span>
                        </p>
                    </div>
                    @endif

                    {{-- Sélection du moyen de paiement --}}
                    <div class="mb-8">
                        <label class="block text-sm font-semibold text-gray-900 mb-3">💳 Moyen de paiement</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @if(isset($availableMethods['stripe']))
                            <button type="button" onclick="selectPaymentMethod('stripe')" id="btn-stripe" 
                                class="payment-method-btn flex flex-col items-center p-4 border-2 border-blue-500 bg-blue-50 rounded-xl transition-all hover:shadow-md">
                                <i class="fas fa-credit-card text-2xl text-blue-600 mb-2"></i>
                                <span class="text-sm font-medium text-gray-900">Carte</span>
                            </button>
                            @endif
                            
                            @if(isset($availableMethods['apple_pay']))
                            <button type="button" onclick="selectPaymentMethod('apple_pay')" id="btn-apple_pay"
                                class="payment-method-btn flex flex-col items-center p-4 border-2 border-gray-200 rounded-xl transition-all hover:border-gray-300 hover:shadow-md">
                                <i class="fab fa-apple text-2xl text-gray-800 mb-2"></i>
                                <span class="text-sm font-medium text-gray-900">Apple Pay</span>
                            </button>
                            @endif
                            
                            @if(isset($availableMethods['google_pay']))
                            <button type="button" onclick="selectPaymentMethod('google_pay')" id="btn-google_pay"
                                class="payment-method-btn flex flex-col items-center p-4 border-2 border-gray-200 rounded-xl transition-all hover:border-gray-300 hover:shadow-md">
                                <i class="fab fa-google text-2xl text-gray-800 mb-2"></i>
                                <span class="text-sm font-medium text-gray-900">Google Pay</span>
                            </button>
                            @endif
                            
                            @if(isset($availableMethods['cash']) && $cashAllowedByCriteria)
                            <button type="button" onclick="selectPaymentMethod('cash')" id="btn-cash"
                                class="payment-method-btn flex flex-col items-center p-4 border-2 border-gray-200 rounded-xl transition-all hover:border-gray-300 hover:shadow-md">
                                <i class="fas fa-money-bill-wave text-2xl text-green-600 mb-2"></i>
                                <span class="text-sm font-medium text-gray-900">Espèces</span>
                            </button>
                            @endif
                            
                            @if(isset($availableMethods['klarna']))
                            <button type="button" onclick="selectPaymentMethod('klarna')" id="btn-klarna"
                                class="payment-method-btn flex flex-col items-center p-4 border-2 border-gray-200 rounded-xl transition-all hover:border-gray-300 hover:shadow-md">
                                <svg class="w-10 h-6 mb-2" viewBox="0 0 100 40" fill="none">
                                    <path d="M10 8h8v24h-8V8zm12 0c0 6.627 2.686 12.631 7.029 16.971L22 32h9.6l8-8c-4.418 0-8-3.582-8-8V8H22zm24 20a4 4 0 100-8 4 4 0 000 8z" fill="#FFB3C7"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-900">Klarna</span>
                            </button>
                            @endif
                            
                            @if(isset($availableMethods['amazon_pay']))
                            <button type="button" onclick="selectPaymentMethod('amazon_pay')" id="btn-amazon_pay"
                                class="payment-method-btn flex flex-col items-center p-4 border-2 border-gray-200 rounded-xl transition-all hover:border-gray-300 hover:shadow-md">
                                <i class="fab fa-amazon text-2xl text-orange-500 mb-2"></i>
                                <span class="text-sm font-medium text-gray-900">Amazon Pay</span>
                            </button>
                            @endif
                        </div>
                        @if(isset($availableMethods['cash']) && !$cashAllowedByCriteria)
                            <p class="text-xs text-gray-500 mt-2">Le paiement en espèces n'est pas disponible pour cette annonce selon les critères du prestataire.</p>
                        @endif
                    </div>

                    {{-- Section Stripe (Carte) --}}
                    <div id="stripe-section">
                        <form id="payment-form" class="space-y-6">
                            @csrf
                            <input type="hidden" id="payment_type" name="payment_type" value="{{ $booking->deposit_amount > 0 && $booking->payment_status === 'pending' ? 'deposit' : ($booking->payment_status === 'deposit_paid' ? 'balance' : 'full') }}">
                            
                            <div id="payment-element" class="p-4 border-2 border-gray-200 rounded-xl bg-white min-h-[100px]">
                                <div class="flex items-center justify-center py-4">
                                    <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    <span class="ml-3 text-gray-500">Chargement du formulaire de paiement...</span>
                                </div>
                            </div>
                            
                            <div id="error-message" class="text-red-600 text-sm hidden p-3 bg-red-50 rounded-lg"></div>

                            {{-- Conditions de paiement --}}
                            @if(View::exists('components.payment-terms'))
                                @include('components.payment-terms', ['booking' => $booking])
                            @endif

                            <button id="submit" type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-4 rounded-xl transition-all shadow-lg hover:shadow-xl flex justify-center items-center">
                                <div class="spinner hidden mr-2 w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span id="button-text">Payer <span id="payAmount">{{ number_format($booking->deposit_amount > 0 && $booking->payment_status === 'pending' ? $booking->deposit_amount : ($booking->payment_status === 'deposit_paid' ? max(0, $booking->total_price - $booking->deposit_amount) : $booking->total_price), 2) }}</span> €</span>
                            </button>
                        </form>
                    </div>

                    {{-- Section Espèces --}}
                    <div id="cash-section" class="hidden">
                        <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-4">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-green-800 mb-1">Paiement en espèces</h4>
                                    <p class="text-sm text-green-700">
                                        Vous paierez <strong>{{ number_format($booking->total_price, 2) }} €</strong> directement au prestataire lors de la prestation.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <form action="{{ $routeCash }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white font-bold py-4 rounded-xl transition-all shadow-lg">
                                <i class="fas fa-check-circle mr-2"></i>
                                Confirmer le paiement en espèces
                            </button>
                        </form>
                    </div>

                    {{-- Section Klarna --}}
                    <div id="klarna-section" class="hidden">
                        <div class="bg-pink-50 border border-pink-200 rounded-xl p-6 mb-4">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-8 h-5" viewBox="0 0 100 40" fill="none">
                                        <path d="M10 8h8v24h-8V8zm12 0c0 6.627 2.686 12.631 7.029 16.971L22 32h9.6l8-8c-4.418 0-8-3.582-8-8V8H22zm24 20a4 4 0 100-8 4 4 0 000 8z" fill="#FFB3C7"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-pink-800 mb-1">Payer en 3 fois sans frais avec Klarna</h4>
                                    <p class="text-sm text-pink-700">
                                        Divisez votre paiement de <strong>{{ number_format($booking->total_price, 2) }} €</strong> en 3 mensualités de <strong>{{ number_format($booking->total_price / 3, 2) }} €</strong>.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <button onclick="processKlarnaPayment()" id="klarna-pay-btn" class="w-full bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 text-white font-bold py-4 rounded-xl transition-all shadow-lg flex items-center justify-center">
                            <div class="klarna-spinner hidden mr-2 w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            <svg class="w-6 h-4 mr-2" viewBox="0 0 100 40" fill="none">
                                <path d="M10 8h8v24h-8V8zm12 0c0 6.627 2.686 12.631 7.029 16.971L22 32h9.6l8-8c-4.418 0-8-3.582-8-8V8H22zm24 20a4 4 0 100-8 4 4 0 000 8z" fill="#fff"/>
                            </svg>
                            <span>Payer avec Klarna</span>
                        </button>
                    </div>

                    {{-- Section Amazon Pay --}}
                    <div id="amazon_pay-section" class="hidden">
                        <div class="bg-orange-50 border border-orange-200 rounded-xl p-6 mb-4">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fab fa-amazon text-orange-600 text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-orange-800 mb-1">Payer avec Amazon Pay</h4>
                                    <p class="text-sm text-orange-700">
                                        Utilisez les informations de paiement de votre compte Amazon pour payer <strong>{{ number_format($booking->total_price, 2) }} €</strong>.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <button onclick="processAmazonPayment()" id="amazon-pay-btn" class="w-full bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-gray-900 font-bold py-4 rounded-xl transition-all shadow-lg flex items-center justify-center">
                            <div class="amazon-spinner hidden mr-2 w-5 h-5 border-2 border-gray-900 border-t-transparent rounded-full animate-spin"></div>
                            <i class="fab fa-amazon text-xl mr-2"></i>
                            <span>Payer avec Amazon</span>
                        </button>
                    </div>

                    {{-- Sécurité --}}
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <div class="flex items-center justify-center gap-4 text-xs text-gray-500">
                            <div class="flex items-center gap-1">
                                <i class="fas fa-lock text-green-500"></i>
                                <span>Cryptage SSL</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i class="fas fa-shield-alt text-blue-500"></i>
                                <span>Paiement sécurisé</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i class="fab fa-stripe text-purple-500"></i>
                                <span>Stripe</span>
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
    const stripeKey = "{{ config('services.stripe.key') ?: ($paymentSettings['stripe_key'] ?? '') }}";
    // Escrow: payment_requirement full/deposit => PaymentIntent créé sur la plateforme.
    // Dans ce cas, il ne faut PAS initialiser Stripe avec stripeAccount.
    const useEscrow = {{ in_array($paymentRequirement, ['full', 'deposit']) ? 'true' : 'false' }};
    const stripeAccountId = useEscrow ? '' : {!! Js::from($stripeAccountId ?? '') !!};
    const stripeOptions = stripeAccountId ? { stripeAccount: stripeAccountId } : {};
    const stripe = stripeKey ? Stripe(stripeKey, stripeOptions) : null;
    const bookingId = "{{ $booking->id }}";
    let elements;
    let paymentElement;
    let stripeInitialized = false;
    let currentMethod = 'stripe';
    let currentPaymentIntentId = null;
    
    const depositAmount = Number("{{ number_format($booking->deposit_amount, 2, '.', '') }}");
    const totalAmount = Number("{{ number_format($booking->total_price, 2, '.', '') }}");
    const balanceAmount = Math.max(0, totalAmount - depositAmount);
    const hasDeposit = depositAmount > 0;

    // Initialize Stripe by default
    if (stripe) {
        initializeStripe();
    }

    function selectPaymentMethod(method) {
        currentMethod = method;
        
        // Hide all sections
        document.querySelectorAll('#stripe-section, #cash-section, #klarna-section, #amazon_pay-section').forEach(el => {
            el.classList.add('hidden');
        });
        
        // Reset all buttons
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.classList.remove('border-blue-500', 'bg-blue-50');
            btn.classList.add('border-gray-200');
        });
        
        // Activate selected button
        const activeBtn = document.getElementById('btn-' + method);
        if (activeBtn) {
            activeBtn.classList.remove('border-gray-200');
            activeBtn.classList.add('border-blue-500', 'bg-blue-50');
        }
        
        // Show selected section
        if (method === 'stripe' || method === 'apple_pay' || method === 'google_pay') {
            document.getElementById('stripe-section').classList.remove('hidden');
            if (!stripeInitialized && stripe) {
                initializeStripe();
            }
        } else if (method === 'cash') {
            document.getElementById('cash-section').classList.remove('hidden');
        } else if (method === 'klarna') {
            document.getElementById('klarna-section').classList.remove('hidden');
        } else if (method === 'amazon_pay') {
            document.getElementById('amazon_pay-section').classList.remove('hidden');
        }
    }

    async function initializeStripe() {
        if (stripeInitialized || !stripe) return;
        
        const paymentType = document.getElementById('payment_type').value;
        
        // Afficher un indicateur de chargement
        const paymentElementContainer = document.getElementById('payment-element');
        paymentElementContainer.innerHTML = '<div class="flex items-center justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><span class="ml-3 text-gray-600">Chargement...</span></div>';
        
        try {
            const response = await fetch("{{ $routeIntent }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ payment_type: paymentType }),
            });
            
            const data = await response.json();
            if (data.error) {
                paymentElementContainer.innerHTML = '';
                showMessage(data.error);
                return;
            }
            
            const { clientSecret } = data;
            currentPaymentIntentId = data.paymentIntentId || null;

            const appearance = { 
                theme: 'stripe',
                variables: {
                    colorPrimary: '#4f46e5',
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
                clientSecret,
                locale: 'fr'
            });

            paymentElementContainer.innerHTML = '';
            
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
            });
            
            // Écouter les erreurs
            paymentElement.on('loaderror', (event) => {
                showMessage('Erreur de chargement: ' + (event.error?.message || 'Impossible de charger le formulaire'));
            });
            
            paymentElement.mount("#payment-element");
            stripeInitialized = true;
        } catch (error) {
            console.error("Error initializing Stripe:", error);
            paymentElementContainer.innerHTML = '';
            showMessage("Erreur lors de l'initialisation du paiement. Veuillez rafraîchir la page.");
        }
    }

    // Klarna payment via Stripe
    async function processKlarnaPayment() {
        if (!stripe) {
            showMessage("Stripe n'est pas encore chargé. Veuillez patienter.");
            return;
        }
        
        const btn = document.getElementById('klarna-pay-btn');
        const spinner = btn.querySelector('.klarna-spinner');
        btn.disabled = true;
        spinner.classList.remove('hidden');
        
        const paymentType = document.getElementById('payment_type').value;
        
        try {
            // Créer un PaymentIntent avec Klarna
            const response = await fetch("{{ $routeIntent }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ 
                    payment_type: paymentType,
                    provider: 'klarna',
                    payment_method_types: ['klarna']
                }),
            });
            
            const data = await response.json();
            
            if (data.error) {
                showMessage(data.error);
                btn.disabled = false;
                spinner.classList.add('hidden');
                return;
            }
            
            // Confirmer avec Klarna
            const { error } = await stripe.confirmKlarnaPayment(data.clientSecret, {
                payment_method: {
                    billing_details: {
                        email: {!! Js::from(auth()->user()->email ?? '') !!},
                        address: {
                            country: 'FR'
                        }
                    }
                },
                return_url: "{{ $routeSuccess }}?provider=klarna"
            });
            
            if (error) {
                showMessage(error.message);
            }
        } catch (err) {
            console.error("Klarna error:", err);
            showMessage("Erreur lors du paiement Klarna. Veuillez réessayer.");
        }
        
        btn.disabled = false;
        spinner.classList.add('hidden');
    }

    // Amazon Pay (placeholder - nécessite une intégration complète)
    async function processAmazonPayment() {
        const btn = document.getElementById('amazon-pay-btn');
        const spinner = btn.querySelector('.amazon-spinner');
        btn.disabled = true;
        spinner.classList.remove('hidden');
        
        // Amazon Pay nécessite une intégration séparée avec leur SDK
        // Pour l'instant, on affiche un message
        setTimeout(() => {
            alert('L\'intégration Amazon Pay sera bientôt disponible. Veuillez utiliser une autre méthode de paiement pour le moment.');
            btn.disabled = false;
            spinner.classList.add('hidden');
        }, 500);
    }

    async function handleSubmit(e) {
        e.preventDefault();
        
        if (!stripe) {
            showMessage("Stripe n'est pas encore chargé. Veuillez patienter.");
            return;
        }
        
        if (!elements || !stripeInitialized) {
            showMessage("Le formulaire de paiement n'est pas prêt. Veuillez patienter.");
            return;
        }

        // Vérifier l'acceptation des conditions de paiement
        const termsCheckbox = document.getElementById('accept_payment_terms');
        if (termsCheckbox && !termsCheckbox.checked) {
            showMessage("Veuillez accepter les conditions de paiement pour continuer.");
            return;
        }
        
        setLoading(true);

        try {
            const { error, paymentIntent } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: "{{ $routeSuccess }}",
                },
                redirect: 'if_required'
            });

            if (error) {
                if (error.type === "card_error" || error.type === "validation_error") {
                    showMessage(error.message);
                } else {
                    showMessage("Une erreur inattendue s'est produite.");
                }
                setLoading(false);
            } else {
                // Paiement réussi côté Stripe — appeler le serveur pour enregistrer la transaction
                try {
                    const paymentIntentId = paymentIntent?.id || currentPaymentIntentId;
                    if (!paymentIntentId) {
                        showMessage("Impossible d'identifier le paiement Stripe. Veuillez réessayer.");
                        setLoading(false);
                        return;
                    }

                    const confirmResponse = await fetch("{{ $routeConfirm }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        },
                        body: JSON.stringify({
                            payment_intent_id: paymentIntentId,
                            payment_type: document.getElementById('payment_type').value,
                            provider: 'stripe',
                        }),
                    });
                    const confirmData = await confirmResponse.json();
                    if (confirmData.success) {
                        window.location.href = "{{ $routeBack }}?payment_success=true";
                    } else {
                        // Le paiement Stripe a réussi mais l'enregistrement serveur a échoué
                        // Rediriger quand même — le webhook rattrapera
                        console.warn('Server confirm returned non-success:', confirmData);
                        window.location.href = "{{ $routeBack }}?payment_success=true";
                    }
                } catch (confirmErr) {
                    console.error('Server confirm error:', confirmErr);
                    // Le paiement Stripe a réussi, rediriger quand même
                    window.location.href = "{{ $routeBack }}?payment_success=true";
                }
            }
        } catch (err) {
            console.error("Payment error:", err);
            showMessage("Erreur lors du paiement. Veuillez réessayer.");
            setLoading(false);
        }
    }

    document.getElementById("payment-form").addEventListener("submit", handleSubmit);

    function selectPaymentOption(type) {
        document.getElementById('payment_type').value = type;
        const amount = type === 'deposit' ? depositAmount : (type === 'balance' ? balanceAmount : totalAmount);
        document.getElementById('payAmount').innerText = amount.toFixed(2);
        document.getElementById('displayAmount').innerText = amount.toFixed(2) + ' €';
        
        // Update visual selection
        document.querySelectorAll('.payment-option').forEach(opt => {
            opt.classList.remove('border-blue-500', 'bg-blue-50');
            opt.classList.add('border-gray-200');
            opt.querySelector('[id^="dot-"]')?.classList.add('hidden');
            opt.querySelector('[id^="circle-"]')?.classList.remove('border-blue-500');
            opt.querySelector('[id^="circle-"]')?.classList.add('border-gray-300');
        });
        
        const selectedOption = document.querySelector(`input[value="${type}"]`).closest('.payment-option');
        selectedOption.classList.remove('border-gray-200');
        selectedOption.classList.add('border-blue-500', 'bg-blue-50');
        document.getElementById('dot-' + type)?.classList.remove('hidden');
        document.getElementById('dot-' + type)?.classList.add('bg-blue-500');
        
        // Re-initialize Stripe with new amount
        if (currentMethod === 'stripe' && stripeInitialized) {
            stripeInitialized = false;
            document.getElementById('payment-element').innerHTML = '<div class="flex items-center justify-center py-4"><svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></div>';
            initializeStripe();
        }
    }

    function showMessage(messageText) {
        const messageContainer = document.querySelector("#error-message");
        messageContainer.classList.remove("hidden");
        messageContainer.textContent = messageText;
        setTimeout(() => {
            messageContainer.classList.add("hidden");
        }, 5000);
    }

    function setLoading(isLoading) {
        const button = document.querySelector("#submit");
        const spinner = button.querySelector(".spinner");
        const text = button.querySelector("#button-text");
        
        if (isLoading) {
            button.disabled = true;
            spinner.classList.remove("hidden");
            text.classList.add("opacity-50");
        } else {
            button.disabled = false;
            spinner.classList.add("hidden");
            text.classList.remove("opacity-50");
        }
    }
</script>

<style>
    .payment-method-btn.active,
    .payment-option.active {
        border-color: #3b82f6;
        background-color: #eff6ff;
    }
</style>
@endpush
@endsection
