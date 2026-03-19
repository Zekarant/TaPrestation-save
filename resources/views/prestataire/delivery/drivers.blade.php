@extends('layouts.app')

@section('title', 'Gestion des Livreurs')

@push('styles')
<style>
    .drivers-page {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 100vh;
    }
    
    .driver-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid rgba(59, 130, 246, 0.1);
        transition: all 0.3s ease;
    }
    
    .driver-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .status-available { background: #d1fae5; color: #059669; }
    .status-busy { background: #fef3c7; color: #d97706; }
    .status-offline { background: #f3f4f6; color: #6b7280; }
    .status-on_break { background: #e0e7ff; color: #4f46e5; }
    
    .vehicle-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .stat-mini {
        text-align: center;
        padding: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="drivers-page py-4 sm:py-6">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        
        {{-- Section d'aide --}}
        <x-help-section page="prestataire.logistics.drivers" />
        
        <!-- Header -->
        <div class="mb-6">
            <nav class="text-sm text-gray-500 mb-3">
                <a href="{{ route('prestataire.logistics.dashboard') }}" class="hover:text-blue-600">Logistique</a>
                <span class="mx-2">›</span>
                <span class="text-gray-900">Livreurs</span>
            </nav>
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                        🚗 Gestion des Livreurs
                    </h1>
                    <p class="text-gray-600 mt-1">Gérez votre équipe de livraison</p>
                </div>
                
                <button onclick="openAddDriverModal()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajouter un livreur
                </button>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 border border-gray-100 text-center">
                <div class="text-3xl font-bold text-gray-900">{{ $drivers->count() ?? 0 }}</div>
                <div class="text-sm text-gray-500">Total</div>
            </div>
            <div class="bg-green-50 rounded-xl p-4 border border-green-100 text-center">
                <div class="text-3xl font-bold text-green-600">{{ $drivers->where('status', 'available')->count() ?? 0 }}</div>
                <div class="text-sm text-gray-500">Disponibles</div>
            </div>
            <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-100 text-center">
                <div class="text-3xl font-bold text-yellow-600">{{ $drivers->where('status', 'busy')->count() ?? 0 }}</div>
                <div class="text-sm text-gray-500">En livraison</div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 text-center">
                <div class="text-3xl font-bold text-gray-600">{{ $drivers->where('status', 'offline')->count() ?? 0 }}</div>
                <div class="text-sm text-gray-500">Hors ligne</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl p-4 border border-gray-100 mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" 
                           placeholder="Rechercher un livreur..." 
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex gap-2 flex-wrap">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium">Tous</button>
                    <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">Disponibles</button>
                    <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">En mission</button>
                </div>
            </div>
        </div>

        <!-- Drivers Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @forelse($drivers ?? [] as $driver)
            <div class="driver-card p-4 sm:p-6">
                <!-- Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="vehicle-icon bg-blue-100">
                            {{ match($driver->vehicle_type ?? 'car') {
                                'bike' => '🚲',
                                'scooter' => '🛵',
                                'car' => '🚗',
                                'van' => '🚐',
                                'truck' => '🚛',
                                default => '🚗'
                            } }}
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $driver->full_name }}</h3>
                            <p class="text-sm text-gray-500">{{ ucfirst($driver->vehicle_type ?? 'Voiture') }}</p>
                        </div>
                    </div>
                    <span class="status-badge status-{{ $driver->status ?? 'offline' }}">
                        {{ match($driver->status ?? 'offline') {
                            'available' => 'Disponible',
                            'busy' => 'En livraison',
                            'on_break' => 'En pause',
                            default => 'Hors ligne'
                        } }}
                    </span>
                </div>
                
                <!-- Stats -->
                <div class="grid grid-cols-3 gap-2 mb-4 bg-gray-50 rounded-lg p-2">
                    <div class="stat-mini">
                        <div class="text-lg font-bold text-gray-900">{{ $driver->completed_deliveries ?? 0 }}</div>
                        <div class="text-xs text-gray-500">Livrées</div>
                    </div>
                    <div class="stat-mini">
                        <div class="text-lg font-bold text-green-600">{{ $driver->success_rate ?? 100 }}%</div>
                        <div class="text-xs text-gray-500">Réussite</div>
                    </div>
                    <div class="stat-mini">
                        <div class="flex items-center justify-center gap-1">
                            <span class="text-yellow-500">⭐</span>
                            <span class="text-lg font-bold text-gray-900">{{ number_format($driver->rating ?? 5, 1) }}</span>
                        </div>
                        <div class="text-xs text-gray-500">Note</div>
                    </div>
                </div>
                
                <!-- Contact Info -->
                <div class="space-y-2 mb-4 text-sm">
                    @if($driver->phone)
                    <div class="flex items-center gap-2 text-gray-600">
                        <span>📞</span>
                        <span>{{ $driver->phone }}</span>
                    </div>
                    @endif
                    @if($driver->email)
                    <div class="flex items-center gap-2 text-gray-600">
                        <span>📧</span>
                        <span class="truncate">{{ $driver->email }}</span>
                    </div>
                    @endif
                    @if($driver->last_location_update)
                    <div class="flex items-center gap-2 text-gray-400 text-xs">
                        <span>📍</span>
                        <span>Dernière position: {{ $driver->last_location_update->diffForHumans() }}</span>
                    </div>
                    @endif
                </div>
                
                <!-- Actions -->
                <div class="flex gap-2 pt-4 border-t border-gray-100">
                    <button class="flex-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition">
                        Voir profil
                    </button>
                    @if($driver->status === 'available')
                    <button class="flex-1 px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm font-medium transition">
                        Assigner
                    </button>
                    @endif
                    @if($driver->phone)
                    <a href="tel:{{ $driver->phone }}" class="px-3 py-2 bg-green-100 hover:bg-green-200 text-green-700 rounded-lg transition">
                        📞
                    </a>
                    @endif
                </div>
            </div>
            @empty
            <!-- Empty State -->
            <div class="col-span-full">
                <div class="bg-white rounded-xl p-12 text-center border border-gray-100">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-4xl">🚗</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucun livreur</h3>
                    <p class="text-gray-500 mb-6">Commencez par ajouter des livreurs à votre équipe</p>
                    <button onclick="openAddDriverModal()" 
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Ajouter un livreur
                    </button>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Add Driver Modal -->
<div id="addDriverModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-end sm:items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-black/50" onclick="closeAddDriverModal()"></div>
        
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-4 bg-blue-600 text-white">
                <h3 class="text-lg font-bold">➕ Ajouter un livreur</h3>
            </div>
            
            <form action="{{ route('prestataire.logistics.drivers.store') }}" method="POST" class="p-6">
                @csrf
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prénom *</label>
                            <input type="text" name="first_name" required
                                   class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                            <input type="text" name="last_name" required
                                   class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email"
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                        <input type="tel" name="phone" required
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type de véhicule *</label>
                        <select name="vehicle_type" required
                                class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="bike">🚲 Vélo</option>
                            <option value="scooter">🛵 Scooter</option>
                            <option value="car" selected>🚗 Voiture</option>
                            <option value="van">🚐 Utilitaire</option>
                            <option value="truck">🚛 Camion</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">N° de plaque (optionnel)</label>
                        <input type="text" name="vehicle_plate"
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeAddDriverModal()" 
                            class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                        Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openAddDriverModal() {
    document.getElementById('addDriverModal').classList.remove('hidden');
}

function closeAddDriverModal() {
    document.getElementById('addDriverModal').classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAddDriverModal();
});
</script>
@endpush
@endsection
