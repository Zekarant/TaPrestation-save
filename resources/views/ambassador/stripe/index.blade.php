@extends('layouts.ambassador')

@section('ambassador-content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Stripe Connect</h1>

    <div class="bg-white rounded-xl shadow border border-blue-200 p-6">
        @if($ambassador->stripe_account_id)
            <div class="mb-4">
                <p class="text-sm text-gray-700"><strong>Compte Stripe :</strong> {{ $ambassador->stripe_account_id }}</p>
                <p class="text-sm text-gray-700 mt-1">
                    <strong>Statut :</strong>
                    @if($ambassador->stripe_account_status === 'verified')
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">Vérifié</span>
                    @elseif($ambassador->stripe_account_status === 'pending')
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold">En attente</span>
                    @else
                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold">Restreint</span>
                    @endif
                </p>
            </div>

            @if($ambassador->stripe_account_status !== 'verified')
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-sm text-yellow-700">Votre compte Stripe n'est pas encore vérifié. Complétez le processus d'onboarding pour recevoir vos paiements.</p>
                <form method="POST" action="{{ route('ambassador.stripe.connect') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg text-sm">
                        Compléter la vérification
                    </button>
                </form>
            </div>
            @else
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-sm text-green-700">Votre compte Stripe est vérifié. Vous êtes prêt à recevoir vos paiements de commissions.</p>
            </div>
            @endif
        @else
            <div class="text-center py-8">
                <i class="fab fa-stripe text-6xl text-gray-300 mb-4"></i>
                <h2 class="text-lg font-bold text-gray-900 mb-2">Connectez votre compte Stripe</h2>
                <p class="text-sm text-gray-500 mb-6">Pour recevoir vos commissions, vous devez connecter un compte Stripe.</p>
                <form method="POST" action="{{ route('ambassador.stripe.connect') }}">
                    @csrf
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg">
                        <i class="fab fa-stripe-s mr-2"></i>Connecter Stripe
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
