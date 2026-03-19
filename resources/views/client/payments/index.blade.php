@extends('layouts.app')

@section('title', 'Mes Paiements')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        {{-- En-tête --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">💳 Mes Paiements</h1>
                <p class="text-gray-600 mt-1">Historique de vos transactions et paiements</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('client.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
            </div>
        </div>

        {{-- Statistiques --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total dépensé</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalSpent ?? 0, 2) }} €</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Transactions</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $transactionsCount ?? 0 }}</p>
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
                        <p class="text-2xl font-bold text-gray-900">{{ $pendingCount ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Remboursés</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $refundedCount ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Historique des transactions --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-xl font-semibold text-gray-900">Historique des transactions</h2>
            </div>
            
            @if(isset($transactions) && $transactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($transactions as $transaction)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $transaction->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $transaction->description ?? 'Paiement réservation' }}
                                        @php
                                            $rentalTx = $transaction->equipmentRental;
                                            $depositAmount = (float) ($rentalTx->equipment->security_deposit ?? $rentalTx->security_deposit ?? 0);
                                            $depositStatus = strtolower((string) ($rentalTx->deposit_status ?? ''));
                                        @endphp
                                        @if($rentalTx && $depositAmount > 0)
                                            <div class="text-xs mt-1 {{ $depositStatus === 'returned' ? 'text-emerald-700' : ($depositStatus === 'partial' ? 'text-amber-700' : ($depositStatus === 'retained' ? 'text-red-700' : 'text-gray-500')) }}">
                                                Caution: {{ $depositStatus === 'returned' ? 'remboursée' : ($depositStatus === 'partial' ? 'partielle' : ($depositStatus === 'retained' ? 'retenue' : 'en attente')) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ number_format($transaction->amount, 2) }} €
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClasses = [
                                                'paid' => 'bg-green-100 text-green-800',
                                                'completed' => 'bg-green-100 text-green-800',
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'failed' => 'bg-red-100 text-red-800',
                                                'refunded' => 'bg-gray-100 text-gray-800',
                                            ];
                                            $statusLabels = [
                                                'paid' => 'Payé',
                                                'completed' => 'Payé',
                                                'pending' => 'En attente',
                                                'failed' => 'Échoué',
                                                'refunded' => 'Remboursé',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClasses[$transaction->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusLabels[$transaction->status] ?? $transaction->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center space-x-3">
                                            <a href="{{ route('client.payments.show', $transaction) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                👁️ Détails
                                            </a>
                                            @if($transaction->receipt_url)
                                                <a href="{{ $transaction->receipt_url }}" target="_blank" class="text-gray-500 hover:text-gray-700">
                                                    📄 Reçu
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($transactions->hasPages())
                    <div class="p-6 border-t border-gray-100">
                        {{ $transactions->links() }}
                    </div>
                @endif
            @else
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Aucune transaction</h3>
                    <p class="mt-2 text-gray-500">Vous n'avez pas encore effectué de paiement.</p>
                    <a href="{{ route('services.index') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                        Découvrir les services
                    </a>
                </div>
            @endif
        </div>

        {{-- Explication --}}
        <div class="mt-6 bg-white rounded-xl shadow-sm border overflow-hidden">
            <button onclick="this.nextElementSibling.classList.toggle('hidden')" class="w-full px-5 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                <span class="font-medium text-gray-900">❓ Comment fonctionnent mes paiements ?</span>
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div class="hidden px-5 pb-5 space-y-4 border-t bg-gray-50">
                <div class="pt-4">
                    <h4 class="font-semibold text-gray-800 mb-2">💳 Paiement sécurisé</h4>
                    <p class="text-sm text-gray-600">
                        Tous vos paiements sont traités de manière sécurisée via Stripe. 
                        Vos informations bancaires ne sont jamais stockées sur nos serveurs.
                    </p>
                </div>
                
                <div>
                    <h4 class="font-semibold text-gray-800 mb-2">📄 Reçus et factures</h4>
                    <p class="text-sm text-gray-600">
                        Après chaque paiement, vous recevez un reçu par email. 
                        Vous pouvez également télécharger vos reçus depuis cette page en cliquant sur "Reçu".
                    </p>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-800 mb-2">🔄 Statuts des paiements</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li><span class="px-2 py-0.5 bg-green-100 text-green-800 rounded text-xs">Payé</span> - Le paiement a été effectué avec succès</li>
                        <li><span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded text-xs">En attente</span> - Le paiement est en cours de traitement</li>
                        <li><span class="px-2 py-0.5 bg-gray-100 text-gray-800 rounded text-xs">Remboursé</span> - Le paiement a été remboursé sur votre carte</li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-800 mb-2">❓ Besoin d'aide ?</h4>
                    <p class="text-sm text-gray-600">
                        Pour toute question concernant un paiement, contactez directement le prestataire 
                        via la messagerie ou notre support client.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
