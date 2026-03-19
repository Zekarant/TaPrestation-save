@extends('layouts.app')

@section('title', 'Zones de Livraison')

@push('styles')
<style>
    .zones-page {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 100vh;
    }
    
    .zone-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid rgba(59, 130, 246, 0.1);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .zone-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }
    
    .zone-color-bar {
        height: 6px;
    }
    
    .zone-map {
        height: 200px;
        background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .fee-badge {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="zones-page py-4 sm:py-6">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        
        {{-- Section d'aide --}}
        <x-help-section page="prestataire.logistics.zones" />
        
        <!-- Header -->
        <div class="mb-6">
            <nav class="text-sm text-gray-500 mb-3">
                <a href="{{ route('prestataire.logistics.dashboard') }}" class="hover:text-blue-600">Logistique</a>
                <span class="mx-2">›</span>
                <span class="text-gray-900">Zones de livraison</span>
            </nav>
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                        🗺️ Zones de Livraison
                    </h1>
                    <p class="text-gray-600 mt-1">Définissez vos zones et tarifs de livraison</p>
                </div>
                
                <button onclick="openAddZoneModal()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajouter une zone
                </button>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 border border-gray-100 text-center">
                <div class="text-3xl font-bold text-gray-900">{{ $zones->count() ?? 0 }}</div>
                <div class="text-sm text-gray-500">Zones actives</div>
            </div>
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-100 text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $zones->sum(fn($z) => count($z->cities ?? [])) ?? 0 }}</div>
                <div class="text-sm text-gray-500">Villes couvertes</div>
            </div>
            <div class="bg-green-50 rounded-xl p-4 border border-green-100 text-center">
                <div class="text-3xl font-bold text-green-600">{{ number_format($zones->avg('base_delivery_fee') ?? 5, 2) }}€</div>
                <div class="text-sm text-gray-500">Tarif moy.</div>
            </div>
            <div class="bg-purple-50 rounded-xl p-4 border border-purple-100 text-center">
                <div class="text-3xl font-bold text-purple-600">{{ $zones->where('is_active', true)->count() ?? 0 }}</div>
                <div class="text-sm text-gray-500">Activées</div>
            </div>
        </div>

        <!-- Zones Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @forelse($zones ?? [] as $zone)
            <div class="zone-card">
                <div class="zone-color-bar" style="background: {{ $zone->color ?? '#3b82f6' }}"></div>
                
                <div class="p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">{{ $zone->name }}</h3>
                            <p class="text-sm text-gray-500">Code: {{ $zone->code }}</p>
                        </div>
                        <span class="px-2 py-1 {{ $zone->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }} rounded-full text-xs font-medium">
                            {{ $zone->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    
                    <!-- Cities List -->
                    <div class="mb-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Villes</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach(array_slice($zone->cities ?? [], 0, 5) as $city)
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs">{{ $city }}</span>
                            @endforeach
                            @if(count($zone->cities ?? []) > 5)
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-600 rounded text-xs">+{{ count($zone->cities) - 5 }}</span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Postal Codes -->
                    @if(!empty($zone->postal_codes))
                    <div class="mb-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Codes postaux</p>
                        <p class="text-sm text-gray-600">
                            {{ implode(', ', array_slice($zone->postal_codes, 0, 6)) }}
                            @if(count($zone->postal_codes) > 6)
                            <span class="text-blue-600">+{{ count($zone->postal_codes) - 6 }}</span>
                            @endif
                        </p>
                    </div>
                    @endif
                    
                    <!-- Pricing -->
                    <div class="bg-gray-50 rounded-lg p-3 mb-4">
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div>
                                <div class="text-lg font-bold text-gray-900">{{ number_format($zone->base_delivery_fee ?? 0, 2) }}€</div>
                                <div class="text-xs text-gray-500">Base</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-gray-900">{{ number_format($zone->per_km_fee ?? 0, 2) }}€</div>
                                <div class="text-xs text-gray-500">/km</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-orange-600">+{{ number_format($zone->express_surcharge ?? 0, 2) }}€</div>
                                <div class="text-xs text-gray-500">Express</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Delivery Time -->
                    @if($zone->estimated_delivery_time)
                    <div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
                        <span>⏱️</span>
                        <span>Délai estimé: {{ $zone->estimated_delivery_time }} min</span>
                    </div>
                    @endif
                    
                    <!-- Actions -->
                    <div class="flex gap-2 pt-3 border-t border-gray-100">
                        <button class="flex-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition">
                            Modifier
                        </button>
                        <button class="px-3 py-2 {{ $zone->is_active ? 'bg-red-100 hover:bg-red-200 text-red-700' : 'bg-green-100 hover:bg-green-200 text-green-700' }} rounded-lg text-sm font-medium transition">
                            {{ $zone->is_active ? 'Désactiver' : 'Activer' }}
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <!-- Empty State -->
            <div class="col-span-full">
                <div class="bg-white rounded-xl p-12 text-center border border-gray-100">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-4xl">🗺️</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucune zone configurée</h3>
                    <p class="text-gray-500 mb-6">Définissez des zones pour gérer vos tarifs de livraison</p>
                    <button onclick="openAddZoneModal()" 
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Créer une zone
                    </button>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Default Pricing Info -->
        <div class="mt-8 bg-blue-50 rounded-xl p-6 border border-blue-100">
            <div class="flex items-start gap-4">
                <div class="text-3xl">💡</div>
                <div>
                    <h3 class="font-bold text-gray-900 mb-1">Tarification par défaut</h3>
                    <p class="text-gray-600 text-sm mb-3">
                        Pour les zones non couvertes, les frais seront calculés automatiquement selon la distance.
                    </p>
                    <div class="flex flex-wrap gap-4 text-sm">
                        <span class="px-3 py-1 bg-white rounded-lg">Base: <strong>5,00€</strong></span>
                        <span class="px-3 py-1 bg-white rounded-lg">Par km: <strong>0,50€</strong></span>
                        <span class="px-3 py-1 bg-white rounded-lg">Express: <strong>+50%</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Zone Modal -->
<div id="addZoneModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-end sm:items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" onclick="closeAddZoneModal()"></div>
        
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-4 bg-blue-600 text-white">
                <h3 class="text-lg font-bold">🗺️ Ajouter une zone de livraison</h3>
            </div>
            
            <form action="{{ route('prestataire.logistics.zones.store') }}" method="POST" class="p-6">
                @csrf
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom de la zone *</label>
                            <input type="text" name="name" required placeholder="Ex: Paris Centre"
                                   class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Code *</label>
                            <input type="text" name="code" required placeholder="Ex: PAR-01"
                                   class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Villes (séparées par virgule)</label>
                        <input type="text" name="cities" placeholder="Paris, Boulogne, Neuilly..."
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Codes postaux (séparés par virgule)</label>
                        <input type="text" name="postal_codes" placeholder="75001, 75002, 75003..."
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Frais de base (€) *</label>
                            <input type="number" name="base_delivery_fee" step="0.01" required value="5.00"
                                   class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Par km (€)</label>
                            <input type="number" name="per_km_fee" step="0.01" value="0.50"
                                   class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Supp. Express (€)</label>
                            <input type="number" name="express_surcharge" step="0.01" value="5.00"
                                   class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Délai estimé (minutes)</label>
                        <input type="number" name="estimated_delivery_time" placeholder="45"
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="zoneActive" checked class="rounded border-gray-300">
                        <label for="zoneActive" class="text-sm text-gray-700">Zone active</label>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeAddZoneModal()" 
                            class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                        Créer la zone
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openAddZoneModal() {
    document.getElementById('addZoneModal').classList.remove('hidden');
}

function closeAddZoneModal() {
    document.getElementById('addZoneModal').classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAddZoneModal();
});
</script>
@endpush
@endsection
