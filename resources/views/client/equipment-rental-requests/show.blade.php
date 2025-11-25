@extends('layouts.app')

@section('title', 'Détails de la demande de location')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-4 sm:py-6 md:py-8">
    <div class="max-w-7xl mx-auto px-2 sm:px-4 md:px-6">
        <!-- Messages de session -->
        @if(session('success'))
            <div class="mb-4 sm:mb-6 bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-400 text-green-700 px-3 sm:px-4 py-3 rounded-lg shadow" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2 text-green-500"></i>
                    <span class="font-medium text-sm sm:text-base">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 sm:mb-6 bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-400 text-red-700 px-3 sm:px-4 py-3 rounded-lg shadow" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2 text-red-500"></i>
                    <span class="font-medium text-sm sm:text-base">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-4 sm:mb-6 bg-gradient-to-r from-yellow-50 to-yellow-100 border-l-4 border-yellow-400 text-yellow-700 px-3 sm:px-4 py-3 rounded-lg shadow" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2 text-yellow-500"></i>
                    <span class="font-medium text-sm sm:text-base">{{ session('warning') }}</span>
                </div>
            </div>
        @endif

        <!-- Breadcrumb -->
        <nav class="flex mb-4 sm:mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 text-xs sm:text-sm bg-white rounded-lg shadow px-2 sm:px-3 py-2 border border-green-200">
                <li class="inline-flex items-center">
                    <a href="{{ route('client.dashboard') }}" class="inline-flex items-center font-medium text-gray-700 hover:text-green-600">
                        <i class="fas fa-home mr-1 text-green-600"></i>
                        <span class="hidden xs:inline">Accueil</span>
                        <span class="xs:hidden">Accueil</span>
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                        <a href="{{ route('client.equipment-rental-requests.index') }}" class="font-medium text-gray-700 hover:text-green-600">
                            <span class="hidden xs:inline">Mes demandes</span>
                            <span class="xs:hidden">Demandes</span>
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                        <span class="font-medium text-gray-500">
                            <span class="hidden xs:inline">Demande #{{ $request->id }}</span>
                            <span class="xs:hidden">#{{ $request->id }}</span>
                        </span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- En-tête -->
        <div class="bg-white rounded-xl shadow border border-green-200 p-3 sm:p-4 md:p-6 mb-4 sm:mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center mb-3">
                        <div class="p-2 bg-gradient-to-br from-green-100 to-green-200 rounded-full mr-3 shadow">
                            <i class="fas fa-clipboard-list text-lg sm:text-xl text-green-600"></i>
                        </div>
                        <div>
                            <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-green-900">Demande #{{ $request->id }}</h1>
                            <p class="text-xs sm:text-sm text-green-700 font-medium">Demande de location d'équipement</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        <div class="flex items-center text-green-700">
                            <i class="fas fa-calendar-plus mr-1.5 text-green-600"></i>
                            <span class="font-medium">Créée le {{ $request->created_at->format('d/m/Y à H:i') }}</span>
                        </div>
                        <div class="flex items-center text-green-700">
                            <i class="fas fa-clock mr-1.5 text-green-600"></i>
                            <span class="font-medium">Mise à jour le {{ $request->updated_at->format('d/m/Y à H:i') }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-2">
                    @if($request->status === 'pending')
                        <span class="inline-flex items-center px-3 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 shadow">
                            <i class="fas fa-clock mr-1"></i>
                            En attente
                        </span>
                    @elseif($request->status === 'accepted')
                        <span class="inline-flex items-center px-3 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-green-100 to-green-200 text-green-800 shadow">
                            <i class="fas fa-check-circle mr-1"></i>
                            Acceptée
                        </span>
                    @elseif($request->status === 'rejected')
                        <span class="inline-flex items-center px-3 py-2 rounded-full text-xs font-bold bg-gradient-to-r from-red-100 to-red-200 text-red-800 shadow">
                            <i class="fas fa-times-circle mr-1"></i>
                            Refusée
                        </span>
                    @endif
                    
                    @if($request->status === 'pending')
                        <form method="POST" 
                              action="{{ route('client.equipment-rental-requests.destroy', $request) }}" 
                              onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette demande ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white text-xs font-medium rounded-lg shadow hover:shadow-md transition-all">
                                <i class="fas fa-times mr-1"></i>
                                <span class="hidden xs:inline">Annuler</span>
                                <span class="xs:inline">Annuler</span>
                            </button>
                        </form>
                    @endif
                    
                    @if($request->status === 'accepted')
                        <a href="{{ route('client.equipment-rental-requests.index') }}" 
                           class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-600 hover:to-green-800 text-white text-xs font-medium rounded-lg shadow hover:shadow-md transition-all">
                            <i class="fas fa-eye mr-1"></i>
                            <span class="hidden xs:inline">Voir mes locations</span>
                            <span class="xs:inline">Locations</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- Colonne principale -->
            <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                <!-- Détails de l'équipement demandé -->
                <div class="bg-white rounded-xl shadow border border-green-200 p-4 sm:p-6">
                    <div class="flex items-center mb-4">
                        <div class="p-2 bg-gradient-to-br from-green-100 to-green-200 rounded-full mr-3 shadow">
                            <i class="fas fa-tools text-lg text-green-600"></i>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-green-900">Équipement demandé</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                        <!-- Photos de l'équipement -->
                        <div>
                            @if($request->equipment && $request->equipment->photos && is_array($request->equipment->photos) && count($request->equipment->photos) > 0)
                                <div class="space-y-3">
                                    @php
                                        $mainPhoto = $request->equipment->photos[0];
                                        $isValidMainPhoto = is_string($mainPhoto) && !empty($mainPhoto);
                                    @endphp
                                    
                                    @if($isValidMainPhoto)
                                        <div class="relative">
                                            <img src="{{ url('storage/' . $mainPhoto) }}" 
                                                 alt="{{ $request->equipment->name }}" 
                                                 class="w-full h-48 sm:h-64 object-cover rounded-lg shadow"
                                                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPuKWiOKWiCA8L3RleHQ+PC9zdmc+'; this.onerror=null;">
                                        </div>
                                    @else
                                        <div class="w-full h-48 sm:h-64 bg-gradient-to-br from-gray-200 to-gray-300 rounded-lg flex items-center justify-center shadow">
                                            <div class="text-center">
                                                <i class="fas fa-image text-2xl sm:text-3xl text-gray-400 mb-2"></i>
                                                <p class="text-gray-500 text-xs sm:text-sm font-medium">Aucune photo disponible</p>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if(count($request->equipment->photos) > 1)
                                        <div class="grid grid-cols-4 gap-2">
                                            @foreach(array_slice($request->equipment->photos, 1, 4) as $photo)
                                                @if(is_string($photo) && !empty($photo))
                                                    <div class="relative">
                                                        <img src="{{ url('storage/' . $photo) }}" 
                                                             alt="{{ $request->equipment->name }}" 
                                                             class="w-full h-16 object-cover rounded shadow"
                                                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0iI2RkZCIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IiM5OTkiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj7ilojilojilojilojilojilojilojilojilojwvdGV4dD48L3N2Zz4='; this.onerror=null;">
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="w-full h-48 sm:h-64 bg-gradient-to-br from-gray-200 to-gray-300 rounded-lg flex items-center justify-center shadow">
                                    <div class="text-center">
                                        <i class="fas fa-image text-2xl sm:text-3xl text-gray-400 mb-2"></i>
                                        <p class="text-gray-500 text-xs sm:text-sm font-medium">Aucune photo disponible</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Informations de l'équipement -->
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg sm:text-xl font-bold text-green-900 mb-3">
                                    @if($request->equipment)
                                        <a href="{{ route('equipment.show', $request->equipment) }}" 
                                           class="hover:text-green-600 transition-colors">
                                            {{ $request->equipment->name }}
                                        </a>
                                    @else
                                        Équipement non disponible
                                    @endif
                                </h3>
                                <div class="grid grid-cols-1 gap-3">
                                    @if($request->equipment && ($request->equipment->brand || $request->equipment->model))
                                        <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                            <p class="text-xs text-green-700 font-medium mb-1">Modèle</p>
                                            <p class="text-sm font-semibold text-green-900">{{ $request->equipment->brand }} {{ $request->equipment->model }}</p>
                                        </div>
                                    @endif
                                    
                                    @if($request->equipment)
                                        <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                            <p class="text-xs text-green-700 font-medium mb-1">État</p>
                                            <p class="text-sm font-semibold text-green-900">{{ $request->equipment->formatted_condition ?? 'Non spécifié' }}</p>
                                        </div>
                                        
                                        <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                            <p class="text-xs text-green-700 font-medium mb-1">Tarif journalier</p>
                                            <p class="text-lg font-bold text-green-600">{{ number_format($request->equipment->daily_rate ?? 0, 0) }}€</p>
                                        </div>
                                    @endif
                                    
                                    @if($request->equipment && $request->equipment->description)
                                        <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                            <p class="text-xs text-green-700 font-medium mb-2">Description</p>
                                            <p class="text-green-700 text-xs sm:text-sm leading-relaxed">{{ $request->equipment->description }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Détails de la demande -->
                <div class="bg-white rounded-xl shadow border border-green-200 p-4 sm:p-6">
                    <div class="flex items-center mb-4">
                        <div class="p-2 bg-gradient-to-br from-green-100 to-green-200 rounded-full mr-3 shadow">
                            <i class="fas fa-info-circle text-lg text-green-600"></i>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-green-900">Détails de la demande</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <div class="space-y-4">
                            <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                <p class="text-xs text-green-700 font-medium mb-1">Période de location</p>
                                <p class="font-semibold text-green-900">
                                    {{ $request->start_date->format('d/m/Y') }} au {{ $request->end_date->format('d/m/Y') }}
                                </p>
                                <p class="text-sm text-green-700 mt-1">
                                    {{ $request->duration_days }} jour{{ $request->duration_days > 1 ? 's' : '' }}
                                </p>
                            </div>
                            
                            <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                <p class="text-xs text-green-700 font-medium mb-1">Montant de la location</p>
                                <p class="text-lg font-bold text-green-600">{{ number_format($request->total_amount ?? 0, 2) }}€</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            @if($request->equipment && $request->equipment->security_deposit > 0)
                                <div class="bg-orange-50 rounded-lg p-3 border border-orange-200">
                                    <p class="text-xs text-orange-700 font-medium mb-1">Caution</p>
                                    <p class="text-sm text-orange-700">À régler directement avec le prestataire</p>
                                    <span class="font-bold text-orange-600 text-sm">{{ number_format($request->equipment->security_deposit ?? 0, 0) }}€</span>
                                </div>
                            @endif
                            
                            @if($request->client_message)
                                <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                                    <p class="text-xs text-blue-700 font-medium mb-1">Votre message</p>
                                    <p class="text-blue-900 text-sm">{{ $request->client_message }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Colonne latérale -->
            <div class="space-y-4 sm:space-y-6">
                <!-- Prestataire -->
                <div class="bg-white rounded-xl shadow border border-green-200 p-4 sm:p-6">
                    <div class="flex items-center mb-4">
                        <div class="p-2 bg-gradient-to-br from-green-100 to-green-200 rounded-full mr-3 shadow">
                            <i class="fas fa-user-tie text-lg text-green-600"></i>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-green-900">Prestataire</h2>
                    </div>
                    
                    @if($request->equipment && $request->equipment->prestataire)
                        <div class="text-center">
                            <div class="mx-auto mb-3">
                                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center text-white font-bold text-xl shadow-lg mx-auto">
                                    {{ substr($request->equipment->prestataire->company_name ?? $request->equipment->prestataire->first_name, 0, 1) }}
                                </div>
                            </div>
                            
                            <h3 class="font-bold text-green-900 mb-1">
                                {{ $request->equipment->prestataire->company_name ?? $request->equipment->prestataire->first_name . ' ' . $request->equipment->prestataire->last_name }}
                            </h3>
                            
                            @if($request->equipment->prestataire->address)
                                <p class="text-xs text-gray-700 font-medium">{{ $request->equipment->prestataire->address }}</p>
                            @endif
                            
                            <div class="mt-4 pt-4 border-t border-green-200">
                                <a href="{{ route('prestataires.show', $request->equipment->prestataire) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white text-sm font-medium rounded-lg shadow hover:shadow-md transition-all">
                                    <i class="fas fa-user mr-2"></i>
                                    Voir profil
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-gray-500">Prestataire non disponible</p>
                        </div>
                    @endif
                </div>
                
                <!-- Actions -->
                <div class="bg-white rounded-xl shadow border border-green-200 p-4 sm:p-6">
                    <div class="flex items-center mb-4">
                        <div class="p-2 bg-gradient-to-br from-green-100 to-green-200 rounded-full mr-3 shadow">
                            <i class="fas fa-cogs text-lg text-green-600"></i>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-green-900">Actions</h2>
                    </div>
                    
                    <div class="space-y-3">
                        @if($request->equipment)
                            <a href="{{ route('equipment.show', $request->equipment) }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-medium rounded-lg shadow hover:shadow-md transition-all text-sm">
                                <i class="fas fa-tools mr-2"></i>
                                Voir l'équipement
                            </a>
                        @endif
                        
                        @if($request->status === 'pending')
                            <form method="POST" 
                                  action="{{ route('client.equipment-rental-requests.destroy', $request) }}" 
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette demande ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-medium rounded-lg shadow hover:shadow-md transition-all text-sm">
                                    <i class="fas fa-times mr-2"></i>
                                    Annuler la demande
                                </button>
                            </form>
                        @endif
                        

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Photo Modal -->
<div id="photoModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-full">
        <button onclick="closePhotoModal()" class="absolute top-4 right-4 text-white text-2xl z-10 bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-75 transition-all">
            <i class="fas fa-times"></i>
        </button>
        <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl">
        <p id="modalCaption" class="text-white text-center mt-2 font-medium"></p>
    </div>
</div>

<script>
    function showPhotoModal(src, caption) {
        document.getElementById('modalImage').src = src;
        document.getElementById('modalCaption').textContent = caption;
        document.getElementById('photoModal').classList.remove('hidden');
        document.getElementById('photoModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
    
    function closePhotoModal() {
        document.getElementById('photoModal').classList.add('hidden');
        document.getElementById('photoModal').classList.remove('flex');
        document.body.style.overflow = 'auto';
    }
    
    // Close modal when clicking outside the image
    document.getElementById('photoModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closePhotoModal();
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePhotoModal();
        }
    });
</script>
@endsection