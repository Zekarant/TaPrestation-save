@extends('layouts.app')

@section('title', 'Devenir Prestataire')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 to-orange-100 flex items-center justify-center py-12 px-4">
    <div class="max-w-lg w-full">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-8 py-10 text-center text-white">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold mb-2">Devenir Prestataire</h1>
                <p class="text-orange-100">Proposez vos services sur TaPrestation</p>
            </div>

            {{-- Content --}}
            <div class="p-8">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Bonjour {{ $user->name }} !</h2>
                    <p class="text-gray-600">
                        En un clic, transformez votre compte client en compte prestataire. 
                        Vous pourrez ensuite compléter votre profil avec vos services.
                    </p>
                </div>

                {{-- Avantages --}}
                <div class="space-y-3 mb-8">
                    <div class="flex items-center gap-3 text-gray-700">
                        <span class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-600">✓</span>
                        <span>Recevez des demandes de clients</span>
                    </div>
                    <div class="flex items-center gap-3 text-gray-700">
                        <span class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-600">✓</span>
                        <span>Gérez vos réservations facilement</span>
                    </div>
                    <div class="flex items-center gap-3 text-gray-700">
                        <span class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-600">✓</span>
                        <span>Paiements sécurisés via Stripe</span>
                    </div>
                    <div class="flex items-center gap-3 text-gray-700">
                        <span class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-600">✓</span>
                        <span>Tableau de bord complet</span>
                    </div>
                </div>

                {{-- Info --}}
                <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6">
                    <div class="flex gap-3">
                        <span class="text-blue-500">💡</span>
                        <p class="text-sm text-blue-700">
                            Après la création, vous serez redirigé vers votre profil prestataire 
                            pour ajouter vos informations, services et tarifs.
                        </p>
                    </div>
                </div>

                {{-- Form --}}
                <form action="{{ route('client.become-provider.quick') }}" method="POST">
                    @csrf
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 transform hover:scale-[1.02] shadow-lg hover:shadow-xl">
                        🚀 Devenir Prestataire maintenant
                    </button>
                </form>

                <p class="text-center text-gray-500 text-sm mt-4">
                    <a href="{{ route('client.dashboard') }}" class="hover:text-orange-500">
                        ← Retour au tableau de bord client
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
