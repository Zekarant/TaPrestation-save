@extends('layouts.admin-modern')

@section('title', 'Gestion Abonnements')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        {{-- En-tête --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">⭐ Gestion des Abonnements</h1>
                <p class="text-gray-600 mt-1">Gérez les plans et les abonnés</p>
            </div>
            <div class="mt-4 md:mt-0">
                <button onclick="openPlanModal()" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouveau Plan
                </button>
            </div>
        </div>

        {{-- Statistiques --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-linear-to-r from-purple-500 to-indigo-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-200">Revenus abonnements</p>
                        <p class="text-3xl font-bold mt-1">{{ number_format($monthlyRevenue ?? 0, 2) }} €</p>
                        <p class="text-purple-200 text-sm">ce mois</p>
                    </div>
                    <div class="p-3 bg-white/20 rounded-full">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Abonnés actifs</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $activeSubscribers ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Plans actifs</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $activePlans ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Taux conversion</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $conversionRate ?? 0 }}%</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Plans d'abonnement --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-xl font-semibold text-gray-900">Plans d'abonnement</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @forelse($plans ?? [] as $plan)
                        <div class="border border-gray-200 rounded-xl p-6 {{ $plan->is_popular ? 'ring-2 ring-indigo-500' : '' }}">
                            @if($plan->is_popular)
                                <span class="inline-block px-3 py-1 bg-indigo-100 text-indigo-700 text-xs font-medium rounded-full mb-3">Populaire</span>
                            @endif
                            <h3 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h3>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($plan->price, 2) }} €<span class="text-lg text-gray-500">/{{ $plan->interval }}</span></p>
                            <p class="text-gray-500 mt-2">{{ $plan->subscribers_count ?? 0 }} abonnés</p>
                            
                            <div class="flex items-center space-x-2 mt-4">
                                <button onclick="editPlan('{{ $plan->id }}')" class="px-3 py-1 text-indigo-600 hover:bg-indigo-50 rounded-lg text-sm">
                                    Modifier
                                </button>
                                <form action="{{ route('admin.subscriptions.destroy-plan', $plan) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 text-red-600 hover:bg-red-50 rounded-lg text-sm" onclick="return confirm('Supprimer ce plan ?')">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 text-center py-8">
                            <p class="text-gray-500">Aucun plan configuré</p>
                            <button onclick="openPlanModal()" class="mt-2 text-indigo-600 hover:text-indigo-800">
                                Créer votre premier plan
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Liste des abonnés --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Abonnés</h2>
                    <div class="flex items-center space-x-2">
                        <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">Tous les plans</option>
                            @foreach($plans ?? [] as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            @if(isset($subscriptions) && $subscriptions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Début</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($subscriptions as $subscription)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                <span class="text-gray-600 font-medium">{{ substr($subscription->user->name ?? 'U', 0, 1) }}</span>
                                            </div>
                                            <div class="ml-3">
                                                <p class="font-medium text-gray-900">{{ $subscription->user->name ?? 'N/A' }}</p>
                                                <p class="text-sm text-gray-500">{{ $subscription->user->email ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ ucfirst($subscription->user->role ?? 'N/A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full">
                                            {{ $subscription->plan->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $subscription->started_at?->format('d/m/Y') ?? $subscription->starts_at?->format('d/m/Y') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $subscription->current_period_end?->format('d/m/Y') ?? $subscription->ends_at?->format('d/m/Y') ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClasses = [
                                                'active' => 'bg-green-100 text-green-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                                'expired' => 'bg-gray-100 text-gray-800',
                                                'paused' => 'bg-yellow-100 text-yellow-800',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClasses[$subscription->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($subscription->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center space-x-2">
                                            @if($subscription->status !== 'active')
                                            <form action="{{ route('admin.subscriptions.activate', $subscription) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-800" title="Activer">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            </form>
                                            @else
                                            <form action="{{ route('admin.subscriptions.deactivate', $subscription) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-800" title="Désactiver" onclick="return confirm('Désactiver cet abonnement ?')">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            </form>
                                            @endif
                                            
                                            <button onclick="openExtendModal({{ $subscription->id }}, '{{ $subscription->user->name ?? 'Utilisateur' }}')" class="text-blue-600 hover:text-blue-800" title="Prolonger">
                                                <i class="fas fa-calendar-plus"></i>
                                            </button>
                                            
                                            @if($subscription->user)
                                            <a href="{{ route('admin.users.show', $subscription->user) }}" class="text-indigo-600 hover:text-indigo-900" title="Voir profil">
                                                <i class="fas fa-user"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($subscriptions->hasPages())
                    <div class="p-6 border-t border-gray-100">
                        {{ $subscriptions->links() }}
                    </div>
                @endif
            @else
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Aucun abonné</h3>
                    <p class="mt-2 text-gray-500">Les abonnements apparaîtront ici.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Nouveau Plan --}}
<div id="planModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50" onclick="closePlanModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Nouveau Plan</h3>
                <button onclick="closePlanModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('admin.subscriptions.store-plan') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom du plan</label>
                        <input type="text" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prix (€)</label>
                        <input type="number" name="price" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Intervalle</label>
                        <select name="interval" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="month">Mensuel</option>
                            <option value="year">Annuel</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_popular" id="is_popular" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="is_popular" class="ml-2 text-sm text-gray-700">Marquer comme populaire</label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closePlanModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                        Annuler
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                        Créer le plan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Prolonger Abonnement --}}
<div id="extendModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50" onclick="closeExtendModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">
                    <i class="fas fa-calendar-plus text-blue-600 mr-2"></i>
                    Prolonger l'abonnement
                </h3>
                <button onclick="closeExtendModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <p class="text-gray-600 mb-4">Prolonger l'abonnement de <strong id="extendUserName"></strong></p>
            
            <form id="extendForm" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de jours</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-2">
                            <button type="button" onclick="setDays(7)" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">7 jours</button>
                            <button type="button" onclick="setDays(30)" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">30 jours</button>
                            <button type="button" onclick="setDays(90)" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">90 jours</button>
                            <button type="button" onclick="setDays(365)" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">1 an</button>
                        </div>
                        <input type="number" name="days" id="extendDays" min="1" max="365" value="30" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeExtendModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                        Annuler
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        <i class="fas fa-check mr-1"></i> Prolonger
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openPlanModal() {
    document.getElementById('planModal').classList.remove('hidden');
}

function closePlanModal() {
    document.getElementById('planModal').classList.add('hidden');
}

function editPlan(id) {
    openPlanModal();
}

function openExtendModal(subscriptionId, userName) {
    document.getElementById('extendUserName').textContent = userName;
    document.getElementById('extendForm').action = '/admin/subscriptions/' + subscriptionId + '/extend';
    document.getElementById('extendModal').classList.remove('hidden');
}

function closeExtendModal() {
    document.getElementById('extendModal').classList.add('hidden');
}

function setDays(days) {
    document.getElementById('extendDays').value = days;
}
</script>
@endpush
@endsection
