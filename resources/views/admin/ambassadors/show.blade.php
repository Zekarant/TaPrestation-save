@extends('layouts.admin-modern')

@section('title', 'Ambassadeur : ' . ($ambassador->user->name ?? ''))

@section('content')
<div class="bg-blue-50 min-h-screen">
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div>
                    <a href="{{ route('admin.ambassadors.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Retour aux ambassadeurs
                    </a>
                    <h1 class="text-2xl font-bold text-blue-900 mt-2">
                        <i class="fas fa-handshake mr-2"></i>{{ $ambassador->user->name ?? 'N/A' }}
                    </h1>
                    <p class="text-sm text-gray-500">{{ $ambassador->user->email ?? '' }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.ambassadors.edit', $ambassador) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg text-sm">
                        <i class="fas fa-edit mr-1"></i>Modifier
                    </a>
                    <a href="{{ route('admin.ambassadors.commissions', $ambassador) }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg text-sm">
                        <i class="fas fa-coins mr-1"></i>Commissions
                    </a>
                </div>
            </div>

            <!-- Info Card -->
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6 mb-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Code de parrainage</p>
                        <code class="bg-blue-50 px-3 py-1 rounded text-sm font-mono font-bold text-blue-800">{{ $ambassador->referral_code }}</code>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Lien de parrainage</p>
                        <input type="text" value="{{ $ambassador->referral_url }}" readonly
                            class="w-full text-xs bg-gray-50 border border-gray-200 rounded px-2 py-1 font-mono"
                            onclick="this.select(); navigator.clipboard.writeText(this.value);">
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Statut</p>
                        @if($ambassador->status === 'active')
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">Actif</span>
                        @elseif($ambassador->status === 'suspended')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold">Suspendu</span>
                        @else
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold">Inactif</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Membre depuis</p>
                        <p class="text-sm font-semibold">{{ $ambassador->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow border border-blue-200 p-4 text-center">
                    <p class="text-xs text-gray-500">Prestataires</p>
                    <p class="text-2xl font-bold text-blue-900">{{ $stats['total_prestataires'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow border border-green-200 p-4 text-center">
                    <p class="text-xs text-gray-500">Commission totale</p>
                    <p class="text-2xl font-bold text-green-700">{{ number_format($stats['total_earned'], 2, ',', ' ') }}&euro;</p>
                </div>
                <div class="bg-white rounded-xl shadow border border-blue-200 p-4 text-center">
                    <p class="text-xs text-gray-500">Déjà payé</p>
                    <p class="text-2xl font-bold text-blue-700">{{ number_format($stats['total_paid'], 2, ',', ' ') }}&euro;</p>
                </div>
                <div class="bg-white rounded-xl shadow border border-orange-200 p-4 text-center">
                    <p class="text-xs text-gray-500">Non payé</p>
                    <p class="text-2xl font-bold text-orange-600">{{ number_format($stats['unpaid'], 2, ',', ' ') }}&euro;</p>
                </div>
                <div class="bg-white rounded-xl shadow border border-yellow-200 p-4 text-center">
                    <p class="text-xs text-gray-500">En attente</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['pending_commissions'], 2, ',', ' ') }}&euro;</p>
                </div>
                <div class="bg-white rounded-xl shadow border border-purple-200 p-4 text-center">
                    <p class="text-xs text-gray-500">Visites lien</p>
                    <p class="text-2xl font-bold text-purple-700">{{ $stats['referral_visits'] }}</p>
                </div>
                <div class="bg-white rounded-xl shadow border border-teal-200 p-4 text-center">
                    <p class="text-xs text-gray-500">Taux conversion</p>
                    <p class="text-2xl font-bold text-teal-700">{{ $stats['conversion_rate'] }}%</p>
                </div>
            </div>

            <!-- Payout Button -->
            @if($stats['unpaid'] > 0)
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 mb-6">
                <form method="POST" action="{{ route('admin.ambassadors.payout', $ambassador) }}" class="flex items-center justify-between">
                    @csrf
                    <div>
                        <p class="font-semibold text-gray-900">Créer un payout de {{ number_format($stats['unpaid'], 2, ',', ' ') }}&euro;</p>
                        <p class="text-xs text-gray-500">Toutes les commissions pending/approved seront marquées comme payées.</p>
                    </div>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg text-sm"
                        onclick="return confirm('Confirmer le payout ?')">
                        <i class="fas fa-money-bill-wave mr-1"></i>Créer le payout
                    </button>
                </form>
            </div>
            @endif

            <!-- Assign Existing Prestataire -->
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 mb-6">
                <form method="POST" action="{{ route('admin.ambassadors.assign-prestataire', $ambassador) }}" class="flex items-center gap-3">
                    @csrf
                    <label class="text-sm font-semibold text-gray-700 whitespace-nowrap">Assigner un prestataire existant :</label>
                    <input type="number" name="prestataire_id" required placeholder="ID du prestataire"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-40">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-sm">
                        <i class="fas fa-link mr-1"></i>Assigner
                    </button>
                </form>
                @error('prestataire_id')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Prestataires List -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-blue-200 bg-blue-50">
                        <h2 class="font-bold text-blue-900"><i class="fas fa-users mr-2"></i>Prestataires ({{ $stats['total_prestataires'] }})</h2>
                    </div>
                    <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                        @forelse($ambassador->assignments as $assignment)
                        <div class="px-4 py-3 flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-sm text-gray-900">{{ $assignment->prestataire->company_name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $assignment->prestataire->user->email ?? '' }}
                                    &middot; {{ ucfirst(str_replace('_', ' ', $assignment->source)) }}
                                    &middot; {{ $assignment->assigned_at->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                        @empty
                        <div class="px-4 py-6 text-center text-gray-400 text-sm">Aucun prestataire</div>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-blue-200 bg-blue-50">
                        <h2 class="font-bold text-blue-900"><i class="fas fa-history mr-2"></i>Activité récente</h2>
                    </div>
                    <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                        @forelse($recentActivity as $log)
                        <div class="px-4 py-3">
                            <p class="text-sm text-gray-900">{{ $log->description }}</p>
                            <p class="text-xs text-gray-400">{{ $log->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @empty
                        <div class="px-4 py-6 text-center text-gray-400 text-sm">Aucune activité</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Commissions -->
            @if($recentCommissions->isNotEmpty())
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden mt-6">
                <div class="px-4 py-3 border-b border-blue-200 bg-blue-50">
                    <h2 class="font-bold text-blue-900"><i class="fas fa-coins mr-2"></i>Dernières commissions</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Date</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Prestataire</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Type</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600">Montant</th>
                                <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600">Commission</th>
                                <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentCommissions as $c)
                            <tr>
                                <td class="px-4 py-2 text-xs text-gray-600">{{ $c->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 text-sm">{{ $c->prestataire->company_name ?? 'N/A' }}</td>
                                <td class="px-4 py-2 text-xs">{{ ucfirst($c->order_type) }}</td>
                                <td class="px-4 py-2 text-sm text-right">{{ number_format($c->base_amount, 2, ',', ' ') }}&euro;</td>
                                <td class="px-4 py-2 text-sm text-right font-semibold text-green-700">{{ number_format($c->commission_amount, 2, ',', ' ') }}&euro;</td>
                                <td class="px-4 py-2 text-center">
                                    @if($c->status === 'paid')
                                        <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs">Payé</span>
                                    @elseif($c->status === 'pending')
                                        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs">En attente</span>
                                    @elseif($c->status === 'cancelled')
                                        <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded-full text-xs">Annulé</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs">{{ ucfirst($c->status) }}</span>
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
</div>
@endsection
