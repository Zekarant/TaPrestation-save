@extends('layouts.app')

@section('title', 'Abonnements')

@section('content')
<div class="container mx-auto px-4 py-8 pb-28 sm:pb-8">
    <div class="max-w-6xl mx-auto">
        {{-- En-tête --}}
        <div class="text-center mb-12">
            <h1 class="text-2xl sm:text-4xl font-bold text-gray-900">🌟 Choisissez votre abonnement</h1>
            <p class="text-base sm:text-xl text-gray-600 mt-3">Accédez à des fonctionnalités exclusives et économisez sur vos réservations</p>
        </div>

        {{-- Plans --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            {{-- Plan Basic --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow">
                <div class="p-8">
                    <div class="text-center">
                        <span class="inline-block px-4 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium mb-4">Basic</span>
                        <div class="mt-4">
                            <span class="text-5xl font-bold text-gray-900">Gratuit</span>
                        </div>
                        <p class="text-gray-500 mt-2">Pour découvrir la plateforme</p>
                    </div>
                    
                    <ul class="mt-8 space-y-4">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Accès aux services</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Messagerie basique</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">3 réservations/mois</span>
                        </li>
                        <li class="flex items-center text-gray-400">
                            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                            <span>Support prioritaire</span>
                        </li>
                    </ul>

                    <button class="w-full mt-8 px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition">
                        Plan actuel
                    </button>
                </div>
            </div>

            {{-- Plan Pro --}}
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-xl overflow-hidden transform scale-105 relative">
                <div class="absolute top-0 right-0 bg-yellow-400 text-yellow-900 px-4 py-1 text-sm font-bold rounded-bl-xl">
                    POPULAIRE
                </div>
                <div class="p-8">
                    <div class="text-center">
                        <span class="inline-block px-4 py-1 bg-white/20 text-white rounded-full text-sm font-medium mb-4">Pro</span>
                        <div class="mt-4">
                            <span class="text-5xl font-bold text-white">19,99€</span>
                            <span class="text-white/80">/mois</span>
                        </div>
                        <p class="text-white/80 mt-2">Pour les utilisateurs réguliers</p>
                    </div>
                    
                    <ul class="mt-8 space-y-4">
                        <li class="flex items-center text-white">
                            <svg class="w-5 h-5 text-green-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Tout de Basic +</span>
                        </li>
                        <li class="flex items-center text-white">
                            <svg class="w-5 h-5 text-green-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Réservations illimitées</span>
                        </li>
                        <li class="flex items-center text-white">
                            <svg class="w-5 h-5 text-green-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>-10% sur toutes les réservations</span>
                        </li>
                        <li class="flex items-center text-white">
                            <svg class="w-5 h-5 text-green-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Support prioritaire</span>
                        </li>
                        <li class="flex items-center text-white">
                            <svg class="w-5 h-5 text-green-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Enchères prioritaires</span>
                        </li>
                    </ul>

                    <form action="{{ route('client.subscriptions.subscribe', ['plan' => 'pro']) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full mt-8 px-6 py-3 bg-white text-indigo-600 font-bold rounded-xl hover:bg-gray-100 transition">
                            S'abonner maintenant
                        </button>
                    </form>
                </div>
            </div>

            {{-- Plan Enterprise --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow">
                <div class="p-8">
                    <div class="text-center">
                        <span class="inline-block px-4 py-1 bg-gray-900 text-white rounded-full text-sm font-medium mb-4">Enterprise</span>
                        <div class="mt-4">
                            <span class="text-5xl font-bold text-gray-900">49,99€</span>
                            <span class="text-gray-500">/mois</span>
                        </div>
                        <p class="text-gray-500 mt-2">Pour les professionnels exigeants</p>
                    </div>
                    
                    <ul class="mt-8 space-y-4">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Tout de Pro +</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">-20% sur les réservations</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Manager de compte dédié</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">API Access</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">Rapports avancés</span>
                        </li>
                    </ul>

                    <form action="{{ route('client.subscriptions.subscribe', ['plan' => 'enterprise']) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full mt-8 px-6 py-3 bg-gray-900 text-white font-medium rounded-xl hover:bg-gray-800 transition">
                            Contacter les ventes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Abonnement actuel --}}
        @if(isset($currentSubscription))
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Mon abonnement actuel</h2>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-lg font-medium text-gray-900">{{ $currentSubscription->plan->name ?? 'Basic' }}</p>
                    <p class="text-gray-500">Renouvellement le {{ $currentSubscription->ends_at?->format('d/m/Y') ?? 'N/A' }}</p>
                </div>
                <form action="{{ route('client.subscriptions.cancel') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition" onclick="return confirm('Voulez-vous vraiment annuler votre abonnement ?')">
                        Annuler l'abonnement
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
