@extends('layouts.admin-modern')

@section('title', 'Détail du Signalement Service #' . $report->id)

@section('content')
<div class="bg-blue-50 min-h-screen">
    <!-- En-tête -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('administrateur.reports.services.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-700 hover:bg-blue-800 rounded-lg transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Retour à la liste
                    </a>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold">Signalement Service #{{ $report->id }}</h1>
                        <p class="text-blue-100 mt-1">Détails du signalement</p>
                    </div>
                </div>
                <div class="mt-4 sm:mt-0">
                    @switch($report->status)
                        @case('pending')
                            <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                            @break
                        @case('reviewed')
                            <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Examiné</span>
                            @break
                        @case('resolved')
                            <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full bg-green-100 text-green-800">Résolu</span>
                            @break
                        @case('dismissed')
                            <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">Rejeté</span>
                            @break
                    @endswitch
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Contenu principal -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Informations du signalement -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden">
                    <div class="bg-blue-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-info-circle mr-3"></i>
                            Informations du signalement
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-blue-800 mb-2">Catégorie du signalement</label>
                                    <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                        @switch($report->category)
                                            @case('inappropriate_content')
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Contenu inapproprié</span>
                                                @break
                                            @case('false_information')
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Informations fausses</span>
                                                @break
                                            @case('spam')
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">Spam</span>
                                                @break
                                            @case('fraud')
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Fraude</span>
                                                @break
                                            @case('copyright')
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800">Violation de droits d'auteur</span>
                                                @break
                                            @default
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst(str_replace('_', ' ', $report->category)) }}</span>
                                        @endswitch
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-blue-800 mb-2">Priorité</label>
                                    <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                        @switch($report->priority)
                                            @case('low')
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Faible</span>
                                                @break
                                            @case('medium')
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Moyenne</span>
                                                @break
                                            @case('high')
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-orange-100 text-orange-800">Élevée</span>
                                                @break
                                            @case('urgent')
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Urgente</span>
                                                @break
                                            @default
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($report->priority) }}</span>
                                        @endswitch
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-blue-800 mb-2">Statut</label>
                                    <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                        @switch($report->status)
                                            @case('pending')
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                                                @break
                                            @case('reviewed')
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Examiné</span>
                                                @break
                                            @case('resolved')
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Résolu</span>
                                                @break
                                            @case('dismissed')
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">Rejeté</span>
                                                @break
                                        @endswitch
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-blue-800 mb-2">Date de signalement</label>
                                    <div class="p-3 bg-blue-50 rounded-lg border border-blue-200 text-gray-700">
                                        <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                                        {{ $report->created_at->format('d/m/Y à H:i') }}
                                    </div>
                                </div>
                                
                                @if($report->resolved_at)
                                    <div>
                                        <label class="block text-sm font-medium text-blue-800 mb-2">Date de résolution</label>
                                        <div class="p-3 bg-blue-50 rounded-lg border border-blue-200 text-gray-700">
                                            <i class="fas fa-check-circle mr-2 text-green-600"></i>
                                            {{ $report->resolved_at->format('d/m/Y à H:i') }}
                                        </div>
                                    </div>
                                @endif
                                
                                @if($report->admin_notes)
                                    <div>
                                        <label class="block text-sm font-medium text-blue-800 mb-2">Notes administrateur</label>
                                        <div class="p-3 bg-blue-50 rounded-lg border border-blue-200 text-gray-700">
                                            {{ $report->admin_notes }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Détails du signalement -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden">
                    <div class="bg-blue-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-file-alt mr-3"></i>
                            Détails du signalement
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-6">
                            @if($report->reason)
                                <div>
                                    <label class="block text-sm font-medium text-blue-800 mb-2">Raison du signalement</label>
                                    <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                        <p class="text-gray-700 leading-relaxed">{{ $report->reason }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            @if($report->description)
                                <div>
                                    <label class="block text-sm font-medium text-blue-800 mb-2">Description détaillée</label>
                                    <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                        <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $report->description }}</p>
                                    </div>
                                </div>
                            @endif
                            
                            @if($report->evidence_photos && count($report->evidence_photos) > 0)
                                <div>
                                    <label class="block text-sm font-medium text-blue-800 mb-2">Photos de preuve</label>
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                        @foreach($report->evidence_photos as $photo)
                                            <div class="relative group">
                                                <img src="{{ Storage::url($photo) }}" alt="Preuve" class="w-full h-32 object-cover rounded-lg border border-blue-200 cursor-pointer hover:opacity-75 transition-opacity" onclick="openImageModal('{{ Storage::url($photo) }}')">
                                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-search-plus text-white opacity-0 group-hover:opacity-100 transition-opacity text-xl"></i>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Informations du service -->
                @if($report->service)
                    <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden">
                        <div class="bg-blue-600 px-6 py-4">
                            <h2 class="text-xl font-bold text-white flex items-center">
                                <i class="fas fa-cogs mr-3"></i>
                                Informations du service signalé
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-blue-800 mb-2">Nom du service</label>
                                        <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                            <h3 class="font-semibold text-gray-900">{{ $report->service->title }}</h3>
                                        </div>
                                    </div>
                                    
                                    @if($report->service->description)
                                        <div>
                                            <label class="block text-sm font-medium text-blue-800 mb-2">Description</label>
                                            <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                                <p class="text-gray-700 text-sm">{{ Str::limit($report->service->description, 200) }}</p>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if($report->service->price)
                                        <div>
                                            <label class="block text-sm font-medium text-blue-800 mb-2">Prix</label>
                                            <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                                <span class="text-lg font-bold text-blue-600">{{ number_format($report->service->price, 2) }} €</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="space-y-4">
                                    @if($report->service->location)
                                        <div>
                                            <label class="block text-sm font-medium text-blue-800 mb-2">Localisation</label>
                                            <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                                <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>
                                                {{ $report->service->location }}
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-blue-800 mb-2">Date de publication</label>
                                        <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                            <i class="fas fa-calendar mr-2 text-blue-600"></i>
                                            {{ $report->service->created_at->format('d/m/Y à H:i') }}
                                        </div>
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        <a href="{{ route('services.show', $report->service) }}" target="_blank" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-center">
                                            <i class="fas fa-external-link-alt mr-2"></i>Voir le service
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            @if($report->service->main_photo)
                                <div class="mt-6">
                                    <label class="block text-sm font-medium text-blue-800 mb-2">Photo principale</label>
                                    <div class="relative group max-w-md">
                                        <img src="{{ Storage::url($report->service->main_photo) }}" alt="{{ $report->service->title }}" class="w-full h-48 object-cover rounded-lg border border-blue-200 cursor-pointer hover:opacity-75 transition-opacity" onclick="openImageModal('{{ Storage::url($report->service->main_photo) }}')">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-search-plus text-white opacity-0 group-hover:opacity-100 transition-opacity text-xl"></i>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-xl shadow-lg border border-red-200 overflow-hidden">
                        <div class="bg-red-600 px-6 py-4">
                            <h2 class="text-xl font-bold text-white flex items-center">
                                <i class="fas fa-exclamation-triangle mr-3"></i>
                                Service supprimé
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="text-center py-8">
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                                    <i class="fas fa-trash text-red-600 text-xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Service supprimé</h3>
                                <p class="text-gray-500">Le service signalé a été supprimé et n'est plus disponible.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Informations du rapporteur -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden">
                    <div class="bg-blue-600 px-6 py-4">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <i class="fas fa-user mr-3"></i>
                            Rapporteur
                        </h2>
                    </div>
                    <div class="p-6">
                        @if($report->user)
                            <div class="text-center">
                                @if($report->user->profile_photo)
                                    <img src="{{ Storage::url($report->user->profile_photo) }}" alt="{{ $report->user->name }}" class="w-16 h-16 rounded-full mx-auto mb-4 border-2 border-blue-200">
                                @else
                                    <div class="w-16 h-16 rounded-full mx-auto mb-4 bg-blue-100 flex items-center justify-center border-2 border-blue-200">
                                        <i class="fas fa-user text-blue-600 text-xl"></i>
                                    </div>
                                @endif
                                <h3 class="font-semibold text-gray-900">{{ $report->user->name }}</h3>
                                <p class="text-sm text-gray-500 mb-4">{{ $report->user->email }}</p>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Membre depuis:</span>
                                        <span class="font-medium">{{ $report->user->created_at->format('m/Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 mb-4">
                                    <i class="fas fa-user-slash text-gray-400 text-xl"></i>
                                </div>
                                <p class="text-gray-500">Utilisateur supprimé</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions administrateur -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden">
                    <div class="bg-blue-600 px-6 py-4">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <i class="fas fa-cogs mr-3"></i>
                            Actions administrateur
                        </h2>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('administrateur.reports.services.update-status', $report) }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PATCH')
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Changer le statut</label>
                                <select name="status" id="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>En attente</option>
                                    <option value="reviewed" {{ $report->status === 'reviewed' ? 'selected' : '' }}>Examiné</option>
                                    <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Résolu</option>
                                    <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>Rejeté</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-2">Notes administrateur</label>
                                <textarea name="admin_notes" id="admin_notes" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" placeholder="Ajouter des notes...">{{ $report->admin_notes }}</textarea>
                            </div>
                            
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                <i class="fas fa-save mr-2"></i>Mettre à jour
                            </button>
                        </form>
                        
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <form action="{{ route('administrateur.reports.services.destroy', $report) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce signalement ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                                    <i class="fas fa-trash mr-2"></i>Supprimer le signalement
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour les images -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-full">
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
            <i class="fas fa-times text-2xl"></i>
        </button>
        <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg">
    </div>
</div>

<script>
    function openImageModal(src) {
        document.getElementById('modalImage').src = src;
        document.getElementById('imageModal').classList.remove('hidden');
    }
    
    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
    }
    
    // Fermer le modal en cliquant à l'extérieur
    document.getElementById('imageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeImageModal();
        }
    });
</script>
@endsection