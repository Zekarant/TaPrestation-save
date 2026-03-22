@extends('layouts.ambassador')

@section('ambassador-content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Tableau de bord</h1>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow border border-blue-200 p-5">
            <p class="text-xs text-gray-500 mb-1">Mes prestataires</p>
            <p class="text-3xl font-bold text-blue-900">{{ $stats['total_prestataires'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow border border-green-200 p-5">
            <p class="text-xs text-gray-500 mb-1">Commission totale</p>
            <p class="text-3xl font-bold text-green-700">{{ number_format($stats['total_earned'], 2, ',', ' ') }}&euro;</p>
        </div>
        <div class="bg-white rounded-xl shadow border border-orange-200 p-5">
            <p class="text-xs text-gray-500 mb-1">En attente de paiement</p>
            <p class="text-3xl font-bold text-orange-600">{{ number_format($stats['unpaid'], 2, ',', ' ') }}&euro;</p>
        </div>
        <div class="bg-white rounded-xl shadow border border-purple-200 p-5">
            <p class="text-xs text-gray-500 mb-1">Visites lien (ce mois)</p>
            <p class="text-3xl font-bold text-purple-700">{{ $stats['referral_visits_month'] }}</p>
            <p class="text-xs text-gray-400">{{ $stats['conversions_month'] }} conversion(s)</p>
        </div>
    </div>

    <!-- Referral Link -->
    <div class="bg-white rounded-xl shadow border border-blue-200 p-5 mb-6">
        <h2 class="font-bold text-gray-900 mb-3"><i class="fas fa-link mr-2 text-blue-600"></i>Votre lien de parrainage</h2>
        <div class="flex items-center gap-3">
            <input type="text" value="{{ $ambassador->referral_url }}" readonly id="referral-link"
                class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 font-mono text-sm">
            <button onclick="navigator.clipboard.writeText(document.getElementById('referral-link').value); this.innerHTML='<i class=\'fas fa-check mr-1\'></i>Copié !'; setTimeout(() => this.innerHTML='<i class=\'fas fa-copy mr-1\'></i>Copier', 2000)"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg text-sm whitespace-nowrap">
                <i class="fas fa-copy mr-1"></i>Copier
            </button>
        </div>
        <p class="text-xs text-gray-500 mt-2">Partagez ce lien aux prestataires. Lorsqu'ils s'inscrivent via ce lien, ils seront automatiquement rattachés à votre compte.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Commissions -->
        <div class="bg-white rounded-xl shadow border border-blue-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 flex justify-between items-center">
                <h2 class="font-bold text-gray-900"><i class="fas fa-coins mr-2 text-green-600"></i>Dernières commissions</h2>
                <a href="{{ route('ambassador.commissions.index') }}" class="text-blue-600 text-xs hover:underline">Voir tout</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentCommissions as $c)
                <div class="px-5 py-3 flex justify-between items-center">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $c->prestataire->company_name ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">{{ ucfirst($c->order_type) }} &middot; {{ $c->created_at->format('d/m/Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-green-700">+{{ number_format($c->commission_amount, 2, ',', ' ') }}&euro;</p>
                        @if($c->status === 'paid')
                            <span class="text-xs text-green-600">Payé</span>
                        @elseif($c->status === 'pending')
                            <span class="text-xs text-yellow-600">En attente</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="px-5 py-6 text-center text-gray-400 text-sm">Aucune commission pour le moment.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow border border-blue-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200">
                <h2 class="font-bold text-gray-900"><i class="fas fa-history mr-2 text-blue-600"></i>Activité récente</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentActivity as $log)
                <div class="px-5 py-3">
                    <p class="text-sm text-gray-900">{{ $log->description }}</p>
                    <p class="text-xs text-gray-400">{{ $log->created_at->format('d/m/Y H:i') }}</p>
                </div>
                @empty
                <div class="px-5 py-6 text-center text-gray-400 text-sm">Aucune activité.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
