@extends('layouts.app')

@section('title', 'Sélectionner depuis l\'inventaire')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 to-pink-100">
    <div class="container mx-auto px-4 py-6">
        <!-- En-tête -->
        <div class="bg-white rounded-xl shadow-lg border border-red-200 p-6 mb-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('prestataire.urgent-sales.index') }}" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-red-900">Créer une annonce depuis votre inventaire</h1>
                        <p class="text-red-700">Sélectionnez un article ou équipement à mettre en vente</p>
                    </div>
                </div>
                <a href="{{ route('prestataire.urgent-sales.create') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i>Créer manuellement
                </a>
            </div>
        </div>

        <!-- Onglets -->
        <div class="bg-white rounded-xl shadow-lg border border-red-200 overflow-hidden">
            <div class="flex border-b border-red-200">
                <button onclick="showTab('inventory')" id="tab-inventory" class="flex-1 px-6 py-4 text-center font-semibold text-red-600 bg-red-50 border-b-2 border-red-600 transition">
                    <i class="fas fa-boxes mr-2"></i>Articles d'inventaire
                    <span class="ml-2 bg-red-600 text-white text-xs px-2 py-1 rounded-full">{{ $inventoryItems->count() }}</span>
                </button>
                <button onclick="showTab('equipment')" id="tab-equipment" class="flex-1 px-6 py-4 text-center font-semibold text-gray-500 hover:text-red-600 transition">
                    <i class="fas fa-tools mr-2"></i>Équipements
                    <span class="ml-2 bg-gray-400 text-white text-xs px-2 py-1 rounded-full">{{ $equipments->count() }}</span>
                </button>
            </div>

            <!-- Contenu Inventaire -->
            <div id="content-inventory" class="p-6">
                @if($inventoryItems->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($inventoryItems as $item)
                            @php
                                $metadata = is_string($item->metadata) ? json_decode($item->metadata, true) : $item->metadata;
                                $hasPhoto = isset($metadata['photos']) && count($metadata['photos']) > 0;
                            @endphp
                            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition group">
                                <div class="aspect-square bg-gray-100 relative">
                                    @if($hasPhoto)
                                        <x-media-image :path="$metadata['photos'][0]" :alt="$item->name" class="w-full h-full object-cover" />
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <i class="fas fa-box text-4xl text-gray-300"></i>
                                        </div>
                                    @endif
                                    <div class="absolute top-2 right-2">
                                        <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                                            Stock: {{ $item->quantity }}
                                        </span>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900 mb-1 truncate">{{ $item->name }}</h3>
                                    <p class="text-gray-500 text-xs mb-1">{{ $item->category }}</p>
                                    <p class="text-red-600 font-bold text-lg mb-3">
                                        {{ number_format($item->selling_price ?? $item->cost_per_unit, 2) }} €
                                    </p>
                                    <a href="{{ route('prestataire.urgent-sales.from-inventory', $item->id) }}" 
                                       class="block w-full bg-red-600 hover:bg-red-700 text-white text-center py-2 rounded-lg transition">
                                        <i class="fas fa-tag mr-1"></i>Mettre en vente
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-boxes text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">Aucun article dans l'inventaire</h3>
                        <p class="text-gray-500 mb-4">Ajoutez des articles à votre inventaire pour les mettre en vente rapidement</p>
                        <a href="{{ route('prestataire.inventory.create') }}" class="inline-block bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition">
                            <i class="fas fa-plus mr-2"></i>Ajouter à l'inventaire
                        </a>
                    </div>
                @endif
            </div>

            <!-- Contenu Équipements -->
            <div id="content-equipment" class="p-6 hidden">
                @if($equipments->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($equipments as $equipment)
                            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition group">
                                <div class="aspect-square bg-gray-100 relative">
                                    @if($equipment->main_photo)
                                        <x-media-image :path="$equipment->main_photo" :alt="$equipment->name" class="w-full h-full object-cover" />
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <i class="fas fa-tools text-4xl text-gray-300"></i>
                                        </div>
                                    @endif
                                    <div class="absolute top-2 right-2">
                                        <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                                            {{ ucfirst($equipment->condition ?? 'Bon') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900 mb-1 truncate">{{ $equipment->name }}</h3>
                                    <p class="text-gray-500 text-sm mb-1">Location: {{ number_format($equipment->price_per_day, 2) }} €/jour</p>
                                    <p class="text-red-600 font-bold text-lg mb-3">
                                        Vente suggérée: {{ number_format($equipment->price_per_day * 30, 2) }} €
                                    </p>
                                    <a href="{{ route('prestataire.urgent-sales.from-equipment', $equipment->id) }}" 
                                       class="block w-full bg-red-600 hover:bg-red-700 text-white text-center py-2 rounded-lg transition">
                                        <i class="fas fa-tag mr-1"></i>Mettre en vente
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-tools text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">Aucun équipement disponible</h3>
                        <p class="text-gray-500 mb-4">Ajoutez des équipements pour les mettre en vente</p>
                        <a href="{{ route('prestataire.equipment.create') }}" class="inline-block bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition">
                            <i class="fas fa-plus mr-2"></i>Ajouter un équipement
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tab) {
    // Cacher tous les contenus
    document.getElementById('content-inventory').classList.add('hidden');
    document.getElementById('content-equipment').classList.add('hidden');
    
    // Réinitialiser les styles des onglets
    document.getElementById('tab-inventory').classList.remove('text-red-600', 'bg-red-50', 'border-b-2', 'border-red-600');
    document.getElementById('tab-inventory').classList.add('text-gray-500');
    document.getElementById('tab-equipment').classList.remove('text-red-600', 'bg-red-50', 'border-b-2', 'border-red-600');
    document.getElementById('tab-equipment').classList.add('text-gray-500');
    
    // Afficher le contenu sélectionné et activer l'onglet
    document.getElementById('content-' + tab).classList.remove('hidden');
    document.getElementById('tab-' + tab).classList.add('text-red-600', 'bg-red-50', 'border-b-2', 'border-red-600');
    document.getElementById('tab-' + tab).classList.remove('text-gray-500');
}
</script>
@endsection
