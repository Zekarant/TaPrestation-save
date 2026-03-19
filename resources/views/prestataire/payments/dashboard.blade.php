@extends('layouts.app')

@section('title', 'Mes paiements')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="container mx-auto px-4 max-w-6xl">
        
        <div class="flex items-center justify-between mb-6">
            <div>
                <a href="{{ route('prestataire.dashboard') }}" class="text-blue-600 hover:underline text-sm">← Retour</a>
                <h1 class="text-2xl font-bold text-gray-900 mt-2">💰 Mes paiements</h1>
            </div>
            <a href="{{ route('prestataire.payments.connect') }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg">
                ⚙️ Paramètres
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-6">
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($totalEarnings, 2) }} €</p>
                        <p class="text-sm text-gray-500">Total gagné</p>
                        @if(($internalBalance ?? 0) > 0)
                            <p class="text-xs text-green-600">dont {{ number_format($internalBalance, 2) }}€ TaPrestation</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($thisMonthEarnings, 2) }} €</p>
                        <p class="text-sm text-gray-500">Ce mois-ci</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($pendingAmount, 2) }} €</p>
                        <p class="text-sm text-gray-500">En attente</p>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Balance interne TaPrestation --}}
        @if(($internalBalance ?? 0) > 0)
        <div class="mb-6 bg-gradient-to-r from-emerald-500 to-green-600 rounded-xl shadow-lg p-5 text-white">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        💰
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ number_format($internalBalance, 2) }} €</p>
                        <p class="text-sm text-green-100">Solde TaPrestation</p>
                    </div>
                </div>
                <div class="text-right flex flex-col gap-2">
                    <p class="text-xs text-green-100">Ventes urgentes, escrow confirmés</p>
                    <div class="flex gap-2 items-center justify-end flex-wrap">
                        <a href="{{ route('prestataire.escrow.index') }}" class="inline-flex items-center gap-1 text-sm bg-white/20 hover:bg-white/30 px-3 py-1 rounded-lg transition">
                            📋 Voir détails
                        </a>
                        @php
                            $minWithdrawal = (float) get_setting('min_withdrawal', '50');
                            $canWithdraw = $internalBalance >= $minWithdrawal && (auth()->user()->prestataire->stripe_account_id ?? null);
                        @endphp
                        <button type="button" onclick="document.getElementById('withdrawModal').style.display='flex'"
                            class="inline-flex items-center gap-1 text-sm px-3 py-1 rounded-lg transition {{ $canWithdraw ? 'bg-white text-green-700 hover:bg-green-50 font-semibold' : 'bg-white/10 text-green-200 cursor-not-allowed' }}"
                            {{ $canWithdraw ? '' : 'disabled' }}>
                            💸 Retirer
                        </button>
                    </div>
                    @if(!$canWithdraw && $internalBalance > 0)
                        <p class="text-xs text-green-200">
                            @if(!(auth()->user()->prestataire->stripe_account_id ?? null))
                                <a href="{{ route('prestataire.stripe.connect') }}" class="underline">Connectez Stripe</a> pour retirer
                            @else
                                Min. {{ number_format($minWithdrawal, 2) }} € pour retirer
                            @endif
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Modale de retrait --}}
        <div id="withdrawModal" class="fixed inset-0 z-50 hidden items-center justify-center" style="backdrop-filter: blur(3px); background-color: rgba(0, 0, 0, 0.45); display: none;">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4 border border-green-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">💸 Demande de retrait</h3>
                <form action="{{ route('prestataire.payments.withdraw') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Montant à retirer</label>
                        <div class="relative">
                            <input type="number" name="amount" step="0.01" min="{{ $minWithdrawal }}" max="{{ $internalBalance }}"
                                value="{{ $internalBalance }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 pr-8"
                                required>
                            <span class="absolute right-3 top-2.5 text-gray-400 text-sm">€</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Disponible : {{ number_format($internalBalance, 2) }} € — Min. {{ number_format($minWithdrawal, 2) }} €</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="document.getElementById('withdrawModal').style.display='none'"
                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition font-medium">
                            Annuler
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                            Confirmer le retrait
                        </button>
                    </div>
                </form>
                <p class="text-xs text-gray-400 mt-3 text-center">Virement sous 1-2 jours ouvrables via Stripe</p>
            </div>
        </div>
        @endif

        {{-- Info commission (compact) --}}
        <p class="mb-4 text-xs text-blue-700 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
            💡 Commission {{ $commissionRate }}% déduite automatiquement — montants affichés = gains nets.
        </p>

        {{-- Liste des paiements --}}
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h2 class="font-semibold text-gray-900">Historique des paiements</h2>
            </div>

            @if($payments->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500">Aucun paiement reçu pour le moment</p>
                    <p class="text-sm text-gray-400 mt-1">Les paiements de vos clients apparaîtront ici</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-4">
                    @foreach($payments as $payment)
                        <div class="bg-gray-50 rounded-xl p-4 border hover:shadow-sm transition flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-sm
                                    @if($payment->status === 'completed') bg-green-100 text-green-600
                                    @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-600
                                    @elseif($payment->status === 'refunded') bg-red-100 text-red-600
                                    @else bg-gray-100 text-gray-600 @endif">
                                    @if($payment->status === 'completed') ✓
                                    @elseif($payment->status === 'pending') ⏳
                                    @elseif($payment->status === 'refunded') ↩
                                    @else € @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 text-sm truncate">{{ $payment->description ?? 'Paiement' }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            <div class="text-right flex items-center gap-2 flex-shrink-0">
                                <div>
                                    <p class="font-bold text-gray-900 text-sm">+{{ number_format($payment->prestataire_amount ?? $payment->amount ?? 0, 2) }}€</p>
                                    <p class="text-xs
                                        @if($payment->status === 'completed') text-green-600
                                        @elseif($payment->status === 'pending') text-yellow-600
                                        @elseif($payment->status === 'refunded') text-red-600
                                        @else text-gray-500 @endif">
                                        @if($payment->status === 'completed') Payé
                                        @elseif($payment->status === 'pending') En attente
                                        @elseif($payment->status === 'refunded') Remboursé
                                        @else {{ ucfirst($payment->status ?? 'inconnu') }} @endif
                                    </p>
                                </div>
                                @if(isset($payment->id) && is_numeric($payment->id))
                                <a href="{{ route('prestataire.payments.sales.show', $payment->id) }}" class="text-indigo-500 hover:text-indigo-700">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Lien vers Stripe --}}
        <div class="mt-6 text-center">
            <a href="{{ route('prestataire.payments.stripe.dashboard') }}" 
               class="text-purple-600 hover:underline text-sm">
                📊 Voir les détails des virements sur Stripe →
            </a>
        </div>

    </div>
</div>
@endsection
