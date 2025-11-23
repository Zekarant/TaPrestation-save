@extends('layouts.admin-modern')

@section('title', 'Signalement #' . $report->id)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 to-green-100">
    <!-- En-tête -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 py-6 sm:py-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 sm:mb-8">
            <div class="mb-4 sm:mb-0">
                <div class="flex items-center mb-2">
                    <div class="p-2 bg-green-100 rounded-lg mr-3">
                        <i class="fas fa-exclamation-triangle text-green-600"></i>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-green-900">Signalement #{{ $report->id }}</h1>
                </div>
                <p class="text-green-600 ml-11">Détails du signalement d'équipement</p>
            </div>
            <a href="{{ route('administrateur.reports.equipments.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            <!-- Informations du signalement -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg border border-green-200 mb-6">
                    <div class="px-6 py-4 border-b border-green-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg mr-3">
                                <i class="fas fa-info-circle text-green-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-green-900">Détails du signalement</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <h6 class="text-sm font-medium text-green-700 mb-2">Catégorie</h6>
                                @switch($report->category)
                                    @case('safety')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Sécurité</span>
                                        @break
                                    @case('condition')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">État du matériel</span>
                                        @break
                                    @case('fraud')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">Fraude</span>
                                        @break
                                    @case('inappropriate')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800">Contenu inapproprié</span>
                                        @break
                                    @case('pricing')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Prix abusif</span>
                                        @break
                                    @case('availability')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-indigo-100 text-indigo-800">Disponibilité</span>
                                        @break
                                    @default
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($report->category) }}</span>
                                @endswitch
                            </div>
                            <div>
                                <h6 class="text-sm font-medium text-green-700 mb-2">Priorité</h6>
                                @switch($report->priority)
                                    @case('urgent')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Urgent</span>
                                        @break
                                    @case('high')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-orange-100 text-orange-800">Élevée</span>
                                        @break
                                    @case('medium')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Moyenne</span>
                                        @break
                                    @case('low')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">Faible</span>
                                        @break
                                    @default
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($report->priority) }}</span>
                                @endswitch
                            </div>
                            <div>
                                <h6 class="text-sm font-medium text-green-700 mb-2">Statut</h6>
                                @switch($report->status)
                                    @case('pending')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                                        @break
                                    @case('under_review')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">En cours</span>
                                        @break
                                    @case('investigating')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800">Investigation</span>
                                        @break
                                    @case('resolved')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Résolu</span>
                                        @break
                                    @case('dismissed')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">Rejeté</span>
                                        @break
                                    @case('escalated')
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Escaladé</span>
                                        @break
                                    @default
                                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">{{ $report->status }}</span>
                                @endswitch
                        </div>

                        <div class="space-y-6">
                            <div>
                                <h6 class="text-sm font-medium text-green-700 mb-2">Raison du signalement</h6>
                                <p class="text-green-900 bg-green-50 p-3 rounded-lg">{{ $report->reason }}</p>
                            </div>

                            @if($report->description)
                                <div>
                                    <h6 class="text-sm font-medium text-green-700 mb-2">Description détaillée</h6>
                                    <p class="text-green-900 bg-green-50 p-3 rounded-lg">{{ $report->description }}</p>
                                </div>
                            @endif

                            @if($report->evidence_photos && count($report->evidence_photos) > 0)
                                <div>
                                    <h6 class="text-sm font-medium text-green-700 mb-3">Photos de preuve</h6>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        @foreach($report->evidence_photos as $photo)
                                            <div class="relative group">
                                                <img src="{{ Storage::url($photo) }}" alt="Preuve" class="w-full h-24 object-cover rounded-lg cursor-pointer hover:opacity-75 transition-opacity" 
                                                     data-toggle="modal" data-target="#photoModal{{ $loop->index }}">
                                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-search-plus text-white opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                                </div>
                                                
                                                <!-- Modal pour agrandir la photo -->
                                                <div class="modal fade" id="photoModal{{ $loop->index }}" tabindex="-1">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Photo de preuve {{ $loop->index + 1 }}</h5>
                                                                <button type="button" class="close" data-dismiss="modal">
                                                                    <span>&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body text-center">
                                                                <img src="{{ Storage::url($photo) }}" alt="Preuve" class="img-fluid">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h6 class="text-sm font-medium text-green-700 mb-2">Date du signalement</h6>
                                    <p class="text-green-900 font-medium">{{ $report->created_at->format('d/m/Y à H:i') }}</p>
                                </div>
                                @if($report->resolved_at)
                                    <div>
                                        <h6 class="text-sm font-medium text-green-700 mb-2">Date de résolution</h6>
                                        <p class="text-green-900 font-medium">{{ $report->resolved_at->format('d/m/Y à H:i') }}</p>
                                    </div>
                                @endif
                            </div>

                            @if($report->admin_notes)
                                <div>
                                    <h6 class="text-sm font-medium text-green-700 mb-2">Notes administrateur</h6>
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <p class="text-blue-900">{{ $report->admin_notes }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($report->resolution)
                                <div>
                                    <h6 class="text-sm font-medium text-green-700 mb-2">Résolution</h6>
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                        <p class="text-green-900">{{ $report->resolution }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                </div>
            </div>

            <!-- Informations sur l'équipement -->
            @if($report->equipment)
                <div class="bg-white rounded-xl shadow-lg border border-green-200 mb-6">
                    <div class="px-6 py-4 border-b border-green-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg mr-3">
                                <i class="fas fa-tools text-green-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-green-900">Équipement signalé</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-2">
                                <h5 class="text-xl font-bold text-green-900 mb-3">{{ $report->equipment->name }}</h5>
                                <p class="text-green-700 mb-4">{{ Str::limit($report->equipment->description, 200) }}</p>
                                
                                <div class="space-y-3">
                                    <div>
                                        <span class="text-sm font-medium text-green-700">Prix par jour:</span>
                                        <span class="text-green-900 font-semibold ml-2">{{ number_format($report->equipment->price_per_day, 0, ',', ' ') }} €</span>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-green-700">Localisation:</span>
                                        <span class="text-green-900 ml-2">{{ $report->equipment->city }}</span>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-green-700">État:</span>
                                        @switch($report->equipment->condition)
                                            @case('excellent')
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800 ml-2">Excellent</span>
                                                @break
                                            @case('good')
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800 ml-2">Bon</span>
                                                @break
                                            @case('fair')
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800 ml-2">Correct</span>
                                                @break
                                            @case('poor')
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800 ml-2">Mauvais</span>
                                                @break
                                            @default
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800 ml-2">{{ ucfirst($report->equipment->condition) }}</span>
                                        @endswitch
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-green-700">Publié le:</span>
                                        <span class="text-green-900 ml-2">{{ $report->equipment->created_at->format('d/m/Y à H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                @if($report->equipment->main_photo)
                                    <img src="{{ Storage::url($report->equipment->main_photo) }}" 
                                         alt="Photo de l'équipement" class="w-full h-48 object-cover rounded-lg shadow-md">
                                @endif
                            </div>
                        </div>
                        <div class="mt-6 pt-4 border-t border-green-200">
                            <a href="{{ route('equipment.show', $report->equipment) }}" 
                               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center" target="_blank">
                                <i class="fas fa-external-link-alt mr-2"></i> Voir l'équipement
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-lg border border-green-200 mb-6">
                    <div class="p-6 text-center">
                        <div class="p-4 bg-yellow-100 rounded-full w-20 h-20 mx-auto mb-4 flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-3xl text-yellow-600"></i>
                        </div>
                        <h5 class="text-lg font-semibold text-green-900 mb-2">Équipement supprimé</h5>
                        <p class="text-green-700">L'équipement associé à ce signalement a été supprimé.</p>
                    </div>
                </div>
            @endif
        </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Informations sur le rapporteur -->
                <div class="bg-white rounded-xl shadow-lg border border-green-200 mb-6">
                    <div class="px-6 py-4 border-b border-green-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg mr-3">
                                <i class="fas fa-user text-green-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-green-900">Informations du rapporteur</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <span class="text-sm font-medium text-green-700">Type:</span>
                            @switch($report->reporter_type)
                                @case('client')
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800 ml-2">Client</span>
                                    @break
                                @case('prestataire')
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800 ml-2">Prestataire</span>
                                    @break
                                @case('anonymous')
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800 ml-2">Anonyme</span>
                                    @break
                                @default
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800 ml-2">{{ ucfirst($report->reporter_type) }}</span>
                            @endswitch
                        </div>
                        
                        @if($report->contact_info && count($report->contact_info) > 0)
                            <div class="border-t border-green-200 pt-4">
                                <h6 class="text-sm font-medium text-green-700 mb-3">Informations de contact</h6>
                                <div class="space-y-2">
                                    @foreach($report->contact_info as $key => $value)
                                        <div class="flex justify-between">
                                            <span class="text-sm text-green-700 font-medium">{{ ucfirst($key) }}:</span>
                                            <span class="text-sm text-green-900">{{ $value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions administrateur -->
                <div class="bg-white rounded-xl shadow-lg border border-green-200 mb-6">
                    <div class="px-6 py-4 border-b border-green-200">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg mr-3">
                                <i class="fas fa-cogs text-green-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-green-900">Actions</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('administrateur.reports.equipments.update-status', $report) }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PATCH')
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-green-700 mb-2">Changer le statut</label>
                                <select name="status" id="status" class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                                    <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>En attente</option>
                                    <option value="under_review" {{ $report->status === 'under_review' ? 'selected' : '' }}>En cours</option>
                                    <option value="investigating" {{ $report->status === 'investigating' ? 'selected' : '' }}>Investigation</option>
                                    <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Résolu</option>
                                    <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>Rejeté</option>
                                    <option value="escalated" {{ $report->status === 'escalated' ? 'selected' : '' }}>Escaladé</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="admin_notes" class="block text-sm font-medium text-green-700 mb-2">Notes administrateur</label>
                                <textarea name="admin_notes" id="admin_notes" class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" rows="4" 
                                          placeholder="Ajoutez vos notes sur ce signalement...">{{ $report->admin_notes }}</textarea>
                            </div>
                            
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center justify-center">
                                <i class="fas fa-save mr-2"></i> Mettre à jour
                            </button>
                        </form>
                        
                        <div class="border-t border-green-200 mt-6 pt-6">
                            <form action="{{ route('administrateur.reports.equipments.destroy', $report) }}" 
                                  method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce signalement ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center justify-center">
                                    <i class="fas fa-trash mr-2"></i> Supprimer le signalement
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
@endsection