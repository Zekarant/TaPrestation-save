@extends('layouts.app')

@section('title', 'Mes Enchères')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        {{-- En-tête --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">🏆 Mes Enchères</h1>
                <p class="text-gray-600 mt-1">Gérez vos enchères et suivez vos offres</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ route('client.auctions.stats') }}" class="inline-flex items-center px-4 py-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Statistiques
                </a>
            </div>
        </div>

        {{-- Statistiques rapides --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Enchères actives</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $activeBids ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Gagnées</p>
                        <p class="text-2xl font-bold text-green-600">{{ $wonBids ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">En attente</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $pendingBids ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total économisé</p>
                        <p class="text-2xl font-bold text-purple-600">{{ number_format($totalSaved ?? 0, 2) }} €</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Liste des enchères --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Mes offres</h2>
                    <div class="flex space-x-2">
                        <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="all">Toutes</option>
                            <option value="active">Actives</option>
                            <option value="won">Gagnées</option>
                            <option value="lost">Perdues</option>
                        </select>
                    </div>
                </div>
            </div>
            
            @if(isset($bids) && $bids->count() > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($bids as $bid)
                        <div class="p-6 hover:bg-gray-50 transition">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <h3 class="text-lg font-medium text-gray-900">
                                            {{ $bid->service->title ?? 'Service' }}
                                        </h3>
                                        @php
                                            $statusClasses = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'accepted' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                                'expired' => 'bg-gray-100 text-gray-800',
                                            ];
                                            $statusLabels = [
                                                'pending' => 'En attente',
                                                'accepted' => 'Acceptée',
                                                'rejected' => 'Refusée',
                                                'expired' => 'Expirée',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClasses[$bid->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusLabels[$bid->status] ?? $bid->status }}
                                        </span>
                                    </div>
                                    <p class="text-gray-500 mt-1">
                                        Prestataire: {{ $bid->service->prestataire->user->name ?? 'N/A' }}
                                    </p>
                                    <div class="flex items-center space-x-4 mt-3 text-sm text-gray-500">
                                        <span>📅 {{ $bid->created_at->format('d/m/Y H:i') }}</span>
                                        <span>💰 Mon offre: <strong class="text-indigo-600">{{ number_format($bid->amount, 2) }} €</strong></span>
                                        @if($bid->service->price)
                                            <span>📌 Prix initial: {{ number_format($bid->service->price, 2) }} €</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-col items-end space-y-2">
                                    @if($bid->status === 'pending')
                                        <form action="{{ route('client.auctions.cancel', $bid) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Annuler cette enchère ?')">
                                                Annuler
                                            </button>
                                        </form>
                                    @endif
                                    @if($bid->status === 'accepted')
                                        <a href="{{ route('client.bookings.create', ['service' => $bid->service_id]) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition">
                                            Réserver maintenant
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if($bids->hasPages())
                    <div class="p-6 border-t border-gray-100">
                        {{ $bids->links() }}
                    </div>
                @endif
            @else
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Aucune enchère</h3>
                    <p class="mt-2 text-gray-500">Vous n'avez pas encore placé d'enchère.</p>
                    <a href="{{ route('services.index') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                        Découvrir les services
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
