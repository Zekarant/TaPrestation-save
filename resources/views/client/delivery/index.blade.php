@extends('layouts.app')

@section('title', 'Mes Livraisons')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        {{-- En-tête --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">📦 Mes Livraisons</h1>
                <p class="text-gray-600 mt-1">Suivez vos commandes et livraisons en temps réel</p>
            </div>
            <a href="{{ route('client.delivery.providers') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2 justify-center md:justify-start whitespace-nowrap">
                <i class="fas fa-plus"></i>
                Nouvelle livraison
            </a>
        </div>

        {{-- Statistiques --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 md:gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 square-card stat-card">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total commandes</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalOrders ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 square-card stat-card">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">En cours</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $inTransit ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 square-card stat-card">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Livrées</p>
                        <p class="text-2xl font-bold text-green-600">{{ $delivered ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 square-card stat-card">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Adresses</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $addressCount ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Livraisons en cours --}}
        @if(isset($activeDeliveries) && $activeDeliveries->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-xl font-semibold text-gray-900">🚚 Livraisons en cours</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($activeDeliveries as $delivery)
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                                    <h3 class="text-base sm:text-lg font-medium text-gray-900 truncate">
                                        Commande #{{ $delivery->tracking_number ?? $delivery->id }}
                                    </h3>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 w-fit">
                                        {{ $delivery->status_label ?? 'En transit' }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 truncate">{{ $delivery->provider->name ?? 'Transporteur' }}</p>
                                
                                {{-- Progress bar --}}
                                <div class="mt-3 sm:mt-4">
                                    <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                                        <span>Expédié</span>
                                        <span class="hidden sm:inline">En transit</span>
                                        <span>Livré</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        @php
                                            $progress = match($delivery->status) {
                                                'shipped' => 33,
                                                'in_transit' => 66,
                                                'delivered' => 100,
                                                default => 10
                                            };
                                        @endphp
                                        <div class="bg-indigo-600 h-2 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-1 mt-3 text-xs sm:text-sm text-gray-500">
                                    <span class="truncate">📍 {{ $delivery->destination_address ?? 'Adresse de livraison' }}</span>
                                    @if($delivery->estimated_delivery)
                                        <span>📅 Estimation: {{ $delivery->estimated_delivery->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4 sm:mt-0">
                                <a href="{{ route('client.delivery.track', $delivery) }}" class="block w-full sm:w-auto px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition text-center">
                                    Suivre
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Historique des livraisons --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-xl font-semibold text-gray-900">Historique des livraisons</h2>
            </div>
            
            @if(isset($deliveries) && $deliveries->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Suivi</th>
                                <th class="hidden sm:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transporteur</th>
                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                                <th class="hidden md:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($deliveries as $delivery)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">
                                        {{ $delivery->tracking_number ?? '#'.$delivery->id }}
                                    </td>
                                    <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $delivery->provider->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm text-gray-500 max-w-xs truncate">
                                        {{ $delivery->destination_address ?? 'N/A' }}
                                    </td>
                                    <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $delivery->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClasses = [
                                                'pending' => 'bg-gray-100 text-gray-800',
                                                'shipped' => 'bg-blue-100 text-blue-800',
                                                'in_transit' => 'bg-yellow-100 text-yellow-800',
                                                'delivered' => 'bg-green-100 text-green-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClasses[$delivery->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $delivery->status_label ?? ucfirst($delivery->status) }}
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm">
                                        <a href="{{ route('client.delivery.show', $delivery) }}" class="text-indigo-600 hover:text-indigo-900 truncate">
                                            Détails
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($deliveries->hasPages())
                    <div class="p-6 border-t border-gray-100">
                        {{ $deliveries->links() }}
                    </div>
                @endif
            @else
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Aucune livraison</h3>
                    <p class="mt-2 text-gray-500">Vous n'avez pas encore de livraisons.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
