@extends('layouts.app')

@section('title', 'Abonnements')

@section('content')
<div class="container mx-auto px-4 py-8 pb-28 sm:pb-8">
    <div class="max-w-6xl mx-auto">

        {{-- Message Flash --}}
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6 flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">&times;</button>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ session('error') }}
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">&times;</button>
        </div>
        @endif

        {{-- En-tête --}}
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900">⭐ Nos Plans d'Abonnement</h1>
            <p class="text-xl text-gray-600 mt-3">Choisissez le plan adapté à votre activité</p>
        </div>

        {{-- Abonnement actuel --}}
        @if(isset($currentSubscription) && $currentSubscription && $currentSubscription->status === 'active')
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-lg p-6 mb-8 text-white">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <p class="text-indigo-200 text-sm">🎉 Votre abonnement actuel</p>
                    <h2 class="text-2xl font-bold mt-1">{{ $currentSubscription->plan->name ?? 'Abonnement' }}</h2>
                    <p class="text-indigo-200 mt-1">
                        @if($currentSubscription->current_period_end)
                            Valable jusqu'au {{ $currentSubscription->current_period_end->format('d/m/Y') }}
                        @endif
                    </p>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold">
                        {{ number_format($currentSubscription->plan->price ?? 0, 2) }} €
                        <span class="text-lg text-indigo-200">/{{ $currentSubscription->plan->billing_period_label ?? 'mois' }}</span>
                    </p>
                    
                    {{-- Bouton Résilier avec modal --}}
                    <button onclick="document.getElementById('cancelModal').classList.remove('hidden')" 
                            class="mt-3 text-indigo-200 hover:text-white text-sm underline">
                        Résilier l'abonnement
                    </button>
                </div>
            </div>
        </div>
        @elseif(isset($currentSubscription) && $currentSubscription && $currentSubscription->status === 'cancelled')
        <div class="bg-gradient-to-r from-gray-400 to-gray-500 rounded-xl shadow-lg p-6 mb-8 text-white">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <p class="text-gray-200 text-sm">Abonnement résilié</p>
                    <h2 class="text-2xl font-bold mt-1">{{ $currentSubscription->plan->name ?? 'Abonnement' }}</h2>
                    <p class="text-gray-200 mt-1">
                        Résilié le {{ $currentSubscription->cancelled_at?->format('d/m/Y') ?? 'N/A' }}
                    </p>
                </div>
                <div>
                    <span class="px-4 py-2 bg-white/20 rounded-lg">Résilié</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Plans disponibles --}}
        @if(isset($plans) && $plans->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            @foreach($plans as $plan)
            <div class="bg-white rounded-2xl shadow-lg border {{ $plan->is_featured ? 'border-indigo-300 ring-2 ring-indigo-500' : 'border-gray-200' }} overflow-hidden hover:shadow-xl transition-shadow relative">
                
                @if($plan->is_featured)
                <div class="absolute top-0 right-0 bg-indigo-500 text-white px-4 py-1 text-sm font-bold rounded-bl-xl">
                    ⭐ POPULAIRE
                </div>
                @endif

                <div class="p-8">
                    <div class="text-center">
                        <span class="inline-block px-4 py-1 {{ $plan->is_featured ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700' }} rounded-full text-sm font-medium mb-4">
                            {{ $plan->name }}
                        </span>
                        <div class="mt-4">
                            <span class="text-5xl font-bold text-gray-900">{{ number_format($plan->price, 2) }}€</span>
                            <span class="text-gray-500">/{{ $plan->billing_period_label }}</span>
                        </div>
                        @if($plan->description)
                        <p class="text-gray-500 mt-2">{{ $plan->description }}</p>
                        @endif
                    </div>
                    
                    {{-- Liste des avantages --}}
                    @if($plan->features && count($plan->features) > 0)
                    <ul class="mt-8 space-y-4">
                        @foreach($plan->features as $feature)
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-700">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <div class="mt-8 text-center text-gray-400">
                        <p>Aucun avantage spécifique défini</p>
                    </div>
                    @endif

                    {{-- Bouton d'action --}}
                    @if(isset($currentSubscription) && $currentSubscription && $currentSubscription->subscription_plan_id == $plan->id && $currentSubscription->status === 'active')
                        <button class="w-full mt-8 px-6 py-3 bg-green-100 text-green-700 font-medium rounded-xl cursor-default flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Plan actuel
                        </button>
                    @else
                        <form action="{{ route('prestataire.subscriptions.subscribe', ['plan' => $plan->id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full mt-8 px-6 py-3 {{ $plan->is_featured ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-800 hover:bg-gray-900' }} text-white font-medium rounded-xl transition">
                                S'abonner
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        {{-- Aucun plan disponible --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center mb-12">
            <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucun plan disponible pour le moment</h3>
            <p class="text-gray-500">L'application est actuellement gratuite. Des plans d'abonnement seront bientôt disponibles.</p>
        </div>
        @endif

        {{-- Avantages généraux --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-8">🚀 Pourquoi s'abonner ?</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Plus de visibilité</h3>
                    <p class="text-gray-500 mt-2">Apparaissez en priorité dans les résultats de recherche</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Avantages exclusifs</h3>
                    <p class="text-gray-500 mt-2">Accédez à des fonctionnalités premium</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto bg-purple-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Résiliation libre</h3>
                    <p class="text-gray-500 mt-2">Annulez votre abonnement à tout moment, sans engagement</p>
                </div>
            </div>
        </div>

        {{-- Historique --}}
        @if(isset($history) && $history->count() > 1)
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Historique de vos abonnements</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4">Plan</th>
                            <th class="text-left py-3 px-4">Montant</th>
                            <th class="text-left py-3 px-4">Période</th>
                            <th class="text-left py-3 px-4">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $sub)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">{{ $sub->plan->name ?? 'N/A' }}</td>
                            <td class="py-3 px-4">{{ number_format($sub->current_amount ?? 0, 2) }} €</td>
                            <td class="py-3 px-4">
                                {{ $sub->started_at?->format('d/m/Y') ?? 'N/A' }} - 
                                {{ $sub->current_period_end?->format('d/m/Y') ?? 'N/A' }}
                            </td>
                            <td class="py-3 px-4">
                                @if($sub->status === 'active')
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Actif</span>
                                @elseif($sub->status === 'cancelled')
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">Résilié</span>
                                @else
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">{{ ucfirst($sub->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Modal de résiliation --}}
<div id="cancelModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="document.getElementById('cancelModal').classList.add('hidden')"></div>
        
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6 z-10">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Résilier votre abonnement</h3>
            
            <p class="text-gray-600 mb-4">
                Êtes-vous sûr de vouloir résilier votre abonnement ? Vous perdrez tous les avantages associés.
            </p>
            
            <form action="{{ route('prestataire.subscriptions.cancel') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Raison de la résiliation (optionnel)
                    </label>
                    <textarea name="reason" rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              placeholder="Dites-nous pourquoi vous résiliez..."></textarea>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" 
                            onclick="document.getElementById('cancelModal').classList.add('hidden')"
                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Confirmer la résiliation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
