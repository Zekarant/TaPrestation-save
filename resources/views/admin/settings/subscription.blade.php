@extends('layouts.admin-modern')

@section('title', 'Système d\'abonnement')
@section('page-title', 'Système d\'abonnement')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="page-header mb-8">
        <h1 class="text-3xl font-bold text-gray-900">💳 Système d'abonnement</h1>
        <p class="text-gray-600 mt-1">Activez/désactivez le mode abonnement et gérez les plans</p>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- GROS BOUTON ON/OFF --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 mb-8 overflow-hidden">
        <div class="p-8 {{ ($settings['subscription_enabled'] ?? '0') === '1' ? 'bg-gradient-to-r from-green-500 to-emerald-600' : 'bg-gradient-to-r from-gray-400 to-gray-500' }}">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="text-white text-center md:text-left">
                    <h2 class="text-2xl font-bold flex items-center justify-center md:justify-start gap-3">
                        @if(($settings['subscription_enabled'] ?? '0') === '1')
                            <i class="fas fa-toggle-on text-3xl"></i>
                            Mode abonnement ACTIVÉ
                        @else
                            <i class="fas fa-toggle-off text-3xl"></i>
                            Mode abonnement DÉSACTIVÉ
                        @endif
                    </h2>
                    <p class="mt-2 text-white/80">
                        @if(($settings['subscription_enabled'] ?? '0') === '1')
                            Les nouveaux prestataires doivent payer un abonnement pour accéder à leur espace.
                        @else
                            L'application est actuellement <strong>gratuite</strong> pour tous les prestataires.
                        @endif
                    </p>
                </div>
                
                <form action="{{ route('admin.settings.subscription.toggle') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-8 py-4 rounded-xl font-bold text-lg transition-all transform hover:scale-105 shadow-lg
                        {{ ($settings['subscription_enabled'] ?? '0') === '1' 
                            ? 'bg-white text-red-600 hover:bg-red-50' 
                            : 'bg-white text-green-600 hover:bg-green-50' }}">
                        @if(($settings['subscription_enabled'] ?? '0') === '1')
                            <i class="fas fa-power-off mr-2"></i> DÉSACTIVER
                        @else
                            <i class="fas fa-rocket mr-2"></i> ACTIVER MAINTENANT
                        @endif
                    </button>
                </form>
            </div>
        </div>
        
        {{-- Statistiques rapides --}}
        <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-gray-100 border-t border-gray-100">
            <div class="p-4 text-center">
                <div class="text-2xl font-bold text-gray-900">{{ $stats['total_plans'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">Plans créés</div>
            </div>
            <div class="p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $stats['active_subscribers'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">Abonnés actifs</div>
            </div>
            <div class="p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['monthly_revenue'] ?? 0, 2) }} €</div>
                <div class="text-sm text-gray-500">Revenus/mois</div>
            </div>
            <div class="p-4 text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $stats['expiring_soon'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">Expirent bientôt</div>
            </div>
        </div>
    </div>

    {{-- GESTION DES PLANS --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 mb-8">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-layer-group mr-2 text-indigo-600"></i>
                    Plans d'abonnement
                </h3>
                <p class="text-sm text-gray-500 mt-1">Créez différents plans avec des durées et prix variés</p>
            </div>
            <button onclick="openCreatePlanModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition flex items-center">
                <i class="fas fa-plus mr-2"></i> Nouveau Plan
            </button>
        </div>
        
        <div class="p-6">
            @if(isset($plans) && $plans->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($plans as $plan)
                        <div class="border border-gray-200 rounded-xl p-6 relative {{ $plan->is_featured ? 'ring-2 ring-indigo-500 bg-indigo-50/30' : 'bg-white' }}">
                            @if($plan->is_featured)
                                <span class="absolute -top-3 left-1/2 transform -translate-x-1/2 px-3 py-1 bg-indigo-600 text-white text-xs font-bold rounded-full">
                                    ⭐ POPULAIRE
                                </span>
                            @endif
                            
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-bold text-gray-900">{{ $plan->name }}</h4>
                                <span class="px-2 py-1 text-xs rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $plan->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                            
                            {{-- Stripe Price ID status --}}
                            <div class="mb-3">
                                @if($plan->stripe_price_id)
                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">
                                        <i class="fas fa-check-circle mr-1"></i> Stripe configuré
                                    </span>
                                    <span class="text-xs text-gray-400 ml-1" title="{{ $plan->stripe_price_id }}">{{ Str::limit($plan->stripe_price_id, 20) }}</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">
                                        <i class="fas fa-exclamation-circle mr-1"></i> Stripe non configuré
                                    </span>
                                @endif
                            </div>
                            
                            <div class="mb-4">
                                <span class="text-3xl font-bold text-gray-900">{{ number_format($plan->price, 2, ',', ' ') }}</span>
                                <span class="text-gray-500">€ / {{ $plan->billing_period_label ?? $plan->billing_cycle }}</span>
                            </div>
                            
                            @if($plan->description)
                                <p class="text-sm text-gray-600 mb-4">{{ $plan->description }}</p>
                            @endif
                            
                            @if($plan->features && count($plan->features) > 0)
                                <ul class="space-y-2 mb-4">
                                    @foreach($plan->features as $feature)
                                        <li class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-check text-green-500 mr-2"></i>
                                            {{ $feature }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            
                            <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                                <button onclick='editPlan(@json($plan))' class="flex-1 px-3 py-2 text-indigo-600 hover:bg-indigo-50 rounded-lg text-sm font-medium transition">
                                    <i class="fas fa-edit mr-1"></i> Modifier
                                </button>
                                <form action="{{ route('admin.subscriptions.destroy-plan', $plan) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm font-medium transition" onclick="return confirm('Supprimer ce plan ?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            
                            <div class="mt-3 text-center text-xs text-gray-400">
                                {{ $plan->subscriptions_count ?? 0 }} abonné(s)
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-layer-group text-2xl text-gray-400"></i>
                    </div>
                    <h4 class="text-lg font-medium text-gray-900">Aucun plan créé</h4>
                    <p class="text-gray-500 mt-1">Créez votre premier plan d'abonnement</p>
                    <button onclick="openCreatePlanModal()" class="mt-4 px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                        <i class="fas fa-plus mr-2"></i> Créer un plan
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- PARAMÈTRES PAR DÉFAUT --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-900">
                <i class="fas fa-cog mr-2 text-gray-600"></i>
                Paramètres par défaut
            </h3>
            <p class="text-sm text-gray-500 mt-1">Configuration pour les abonnements simples (si pas de plans)</p>
        </div>
        
        <form action="{{ route('admin.settings.subscription.update') }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Prix par défaut (€)</label>
                    <input type="number" name="subscription_price" step="0.01" min="0"
                        value="{{ $settings['subscription_price'] ?? '29.99' }}" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durée par défaut</label>
                    <select name="subscription_duration" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="7" {{ ($settings['subscription_duration'] ?? '') == '7' ? 'selected' : '' }}>7 jours (1 semaine)</option>
                        <option value="30" {{ ($settings['subscription_duration'] ?? '30') == '30' ? 'selected' : '' }}>30 jours (1 mois)</option>
                        <option value="90" {{ ($settings['subscription_duration'] ?? '') == '90' ? 'selected' : '' }}>90 jours (3 mois)</option>
                        <option value="365" {{ ($settings['subscription_duration'] ?? '') == '365' ? 'selected' : '' }}>365 jours (1 an)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Devise</label>
                    <select name="subscription_currency" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="EUR" {{ ($settings['subscription_currency'] ?? 'EUR') === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                        <option value="USD" {{ ($settings['subscription_currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD ($)</option>
                        <option value="MAD" {{ ($settings['subscription_currency'] ?? '') === 'MAD' ? 'selected' : '' }}>MAD (DH)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Période d'essai (jours)</label>
                    <input type="number" name="subscription_trial_days" min="0" max="90"
                        value="{{ $settings['subscription_trial_days'] ?? '0' }}" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">0 = pas d'essai gratuit</p>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-save mr-2"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>

    {{-- Lien vers les abonnés --}}
    <div class="mt-8 text-center">
        <a href="{{ route('admin.subscriptions.index') }}" class="inline-flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
            <i class="fas fa-users mr-2"></i> Voir tous les abonnés
        </a>
    </div>
</div>

{{-- Modal Création/Édition Plan --}}
<div id="planModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="fixed inset-0 bg-black/50" onclick="closePlanModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900" id="modalTitle">
                    <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>
                    Nouveau Plan
                </h3>
                <button onclick="closePlanModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="planForm" method="POST" action="{{ route('admin.subscriptions.store-plan') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom du plan *</label>
                        <input type="text" name="name" id="planName" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Ex: Mensuel, Premium, Annuel..." required>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prix (€) *</label>
                            <input type="number" name="price" id="planPrice" step="0.01" min="0" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Durée *</label>
                            <select name="billing_cycle" id="planBillingCycle" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="weekly">Semaine</option>
                                <option value="monthly" selected>Mois</option>
                                <option value="quarterly">Trimestre</option>
                                <option value="annual">Année</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" id="planDescription" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Courte description du plan..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stripe Price ID</label>
                        <input type="text" name="stripe_price_id" id="planStripePriceId" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="price_...">
                        <p class="text-xs text-gray-500 mt-1">Obligatoire pour les abonnements récurrents Stripe Checkout.</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Avantages <span class="text-gray-400">(un par ligne)</span>
                        </label>
                        <textarea name="features_text" id="planFeatures" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Accès illimité
Support prioritaire
Statistiques avancées"></textarea>
                    </div>
                    
                    <div class="flex items-center gap-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" id="planIsActive" value="1" checked class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Actif</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_featured" id="planIsFeatured" value="1" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Mis en avant ⭐</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-100">
                    <button type="button" onclick="closePlanModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                        Annuler
                    </button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                        <i class="fas fa-save mr-2"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openCreatePlanModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle mr-2 text-indigo-600"></i> Nouveau Plan';
    document.getElementById('planForm').action = '{{ route("admin.subscriptions.store-plan") }}';
    document.getElementById('formMethod').value = 'POST';
    
    // Reset form
    document.getElementById('planName').value = '';
    document.getElementById('planPrice').value = '';
    document.getElementById('planBillingCycle').value = 'monthly';
    document.getElementById('planDescription').value = '';
    document.getElementById('planStripePriceId').value = '';
    document.getElementById('planFeatures').value = '';
    document.getElementById('planIsActive').checked = true;
    document.getElementById('planIsFeatured').checked = false;
    
    document.getElementById('planModal').classList.remove('hidden');
}

function editPlan(plan) {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit mr-2 text-indigo-600"></i> Modifier le Plan';
    document.getElementById('planForm').action = '/admin/subscriptions/plans/' + plan.id;
    document.getElementById('formMethod').value = 'PUT';
    
    document.getElementById('planName').value = plan.name || '';
    document.getElementById('planPrice').value = plan.price || '';
    document.getElementById('planBillingCycle').value = plan.billing_cycle || 'monthly';
    document.getElementById('planDescription').value = plan.description || '';
    document.getElementById('planStripePriceId').value = plan.stripe_price_id || '';
    document.getElementById('planFeatures').value = plan.features ? plan.features.join('\n') : '';
    document.getElementById('planIsActive').checked = plan.is_active;
    document.getElementById('planIsFeatured').checked = plan.is_featured || false;
    
    document.getElementById('planModal').classList.remove('hidden');
}

function closePlanModal() {
    document.getElementById('planModal').classList.add('hidden');
}
</script>
@endpush
@endsection
