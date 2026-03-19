@extends('layouts.prestataire')

@section('title', 'Finaliser votre inscription')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-6 sm:py-12 pb-28 sm:pb-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- En-tête -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-rocket text-3xl text-blue-600"></i>
            </div>
            <h1 class="text-lg sm:text-2xl lg:text-3xl font-bold text-gray-900">Bienvenue parmi nous ! 🎉</h1>
            <p class="text-gray-600 mt-2">Une dernière étape pour finaliser votre inscription</p>
        </div>

        @if(session('info'))
            <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                {{ session('info') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        @endif

        <!-- Carte de paiement -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header gradient -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-4 sm:px-8 py-6 text-white">
                <h2 class="text-xl font-semibold">Abonnement Prestataire</h2>
                <p class="text-blue-100 mt-1">Accédez à toutes les fonctionnalités de la plateforme</p>
            </div>

            <div class="p-4 sm:p-8">
                <!-- Prix -->
                <div class="text-center mb-8">
                    <div class="text-4xl sm:text-5xl font-bold text-gray-900">
                        {{ number_format($subscriptionSettings['price'], 2, ',', ' ') }}
                        <span class="text-2xl font-normal text-gray-500">
                            {{ $subscriptionSettings['currency'] ?? '€' }}
                        </span>
                    </div>
                    <div class="text-gray-500 mt-2">
                        pour {{ $subscriptionSettings['duration'] }} jours
                    </div>
                    @if($subscriptionSettings['trial_days'] > 0)
                        <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-medium">
                            <i class="fas fa-gift mr-1"></i>
                            + {{ $subscriptionSettings['trial_days'] }} jours d'essai gratuit
                        </div>
                    @endif
                </div>

                <!-- Description -->
                @if(!empty($subscriptionSettings['description']))
                    <div class="mb-8 p-4 bg-gray-50 rounded-xl">
                        <p class="text-gray-600 text-center">{{ $subscriptionSettings['description'] }}</p>
                    </div>
                @endif

                <!-- Avantages -->
                <div class="mb-8">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Ce que vous obtenez
                    </h3>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-green-500 mr-3 w-5"></i>
                            Profil visible par tous les clients
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-green-500 mr-3 w-5"></i>
                            Création de services illimités
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-green-500 mr-3 w-5"></i>
                            Réception de réservations
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-green-500 mr-3 w-5"></i>
                            Messagerie avec les clients
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-green-500 mr-3 w-5"></i>
                            Accès aux appels d'offres
                        </li>
                        <li class="flex items-center text-gray-700">
                            <i class="fas fa-check text-green-500 mr-3 w-5"></i>
                            Statistiques et tableau de bord
                        </li>
                    </ul>
                </div>

                <!-- Formulaire de paiement -->
                <form action="{{ route('prestataire.subscription.process-payment') }}" method="POST" id="payment-form">
                    @csrf
                    
                    @if(isset($stripeEnabled) && $stripeEnabled)
                    <!-- Paiement Stripe (Checkout) -->
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-3"></i>
                            <div>
                                <p class="text-sm text-blue-900 font-medium">Paiement via Stripe</p>
                                <p class="text-sm text-blue-800 mt-1">
                                    Après validation, vous serez redirigé vers Stripe pour finaliser le paiement.
                                </p>
                            </div>
                        </div>
                    </div>
                    @else
                    <!-- Mode manuel (paiement géré par admin) -->
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-yellow-500 mt-0.5 mr-3"></i>
                            <div>
                                <p class="text-sm text-yellow-800 font-medium">Mode de paiement</p>
                                <p class="text-sm text-yellow-700 mt-1">
                                    Le paiement sera géré par l'administrateur. 
                                    En cliquant sur "Activer mon compte", vous confirmez votre engagement.
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Checkbox acceptation -->
                    <div class="mb-6">
                        <label class="flex items-start cursor-pointer">
                            <input type="checkbox" id="accept-terms" required
                                class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-3 text-sm text-gray-600">
                                J'accepte les <a href="{{ route('cgv') }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">conditions générales</a>
                                (renouvellement automatique, résiliation possible à tout moment) et je m'engage à payer l'abonnement de 
                                <strong>{{ number_format($subscriptionSettings['price'], 2, ',', ' ') }} {{ $subscriptionSettings['currency'] ?? '€' }}</strong>
                            </span>
                        </label>
                    </div>

                    <!-- Bouton de soumission -->
                    <button type="submit" id="submit-button"
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-4 px-6 rounded-xl font-semibold text-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-lock mr-2"></i>
                        @if(isset($stripeEnabled) && $stripeEnabled)
                        Payer {{ number_format($subscriptionSettings['price'], 2, ',', ' ') }} {{ $subscriptionSettings['currency'] ?? '€' }}
                        @else
                        Activer mon compte
                        @endif
                    </button>
                </form>

                <!-- Lien retour -->
                <div class="mt-6 text-center">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-700 text-sm">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Annuler et se déconnecter
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Garantie -->
        <div class="mt-8 text-center">
            <div class="inline-flex items-center text-gray-500 text-sm">
                <i class="fas fa-shield-alt mr-2 text-green-500"></i>
                Paiement sécurisé • Annulation possible • Support 7j/7
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    const checkbox = document.getElementById('accept-terms');
    const button = document.getElementById('submit-button');
    
    button.disabled = true;
    
    checkbox.addEventListener('change', function() {
        button.disabled = !this.checked;
    });
    
    document.getElementById('payment-form').addEventListener('submit', function() {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Traitement en cours...';
    });
</script>
@endpush
@endsection
