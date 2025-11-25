@extends('layouts.admin-modern')

@section('title', 'Signalement #' . $report->id)

@section('content')
<!-- En-tête avec style rouge -->
<div class="bg-gradient-to-r from-red-600 to-red-800 text-white py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex-1">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-2">Signalement #{{ $report->id }}</h1>
                <p class="text-red-100 text-sm sm:text-base lg:text-lg">Détails du signalement de vente urgente</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                <a href="{{ route('administrateur.reports.urgent-sales.index') }}" class="bg-white text-red-600 hover:bg-red-50 font-bold py-2 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                    <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
                </a>
            </div>
        </div>
    </div>
</div>

<div class="bg-gray-50 min-h-screen py-6 sm:py-8">

    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
            <!-- Informations du signalement -->
            <div class="lg:col-span-2 space-y-6 sm:space-y-8">
                <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6">
                    <div class="mb-4 text-center">
                        <h3 class="text-xl sm:text-2xl font-bold text-red-800 mb-1 sm:mb-2">Détails du signalement</h3>
                        <p class="text-sm sm:text-base lg:text-lg text-red-700">Informations complètes sur ce signalement</p>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-6">
                        <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                            <h6 class="font-bold text-red-800 mb-2">Raison du signalement</h6>
                            @switch($report->reason)
                                @case('inappropriate_content')
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Contenu inapproprié</span>
                                    @break
                                @case('fraud')
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Fraude</span>
                                    @break
                                @case('spam')
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">Spam</span>
                                    @break
                                @case('fake_listing')
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">Fausse annonce</span>
                                    @break
                                @default
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst(str_replace('_', ' ', $report->reason)) }}</span>
                            @endswitch
                        </div>
                        
                        <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                            <h6 class="font-bold text-red-800 mb-2">Statut</h6>
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
                                @default
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">{{ $report->status }}</span>
                            @endswitch
                        </div>
                    </div>

                    @if($report->description)
                        <div class="bg-red-50 rounded-lg p-4 border border-red-200 mb-6">
                            <h6 class="font-bold text-red-800 mb-2">Description</h6>
                            <p class="text-gray-700">{{ $report->description }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-6">
                        <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                            <h6 class="font-bold text-red-800 mb-2">Date du signalement</h6>
                            <p class="text-gray-700 flex items-center">
                                <i class="fas fa-calendar-alt text-red-600 mr-2"></i>
                                {{ $report->created_at->format('d/m/Y à H:i') }}
                            </p>
                        </div>
                        @if($report->reviewed_at)
                            <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                                <h6 class="font-bold text-red-800 mb-2">Date de traitement</h6>
                                <p class="text-gray-700 flex items-center">
                                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                    {{ $report->reviewed_at->format('d/m/Y à H:i') }}
                                </p>
                            </div>
                        @endif
                    </div>

                    @if($report->admin_notes)
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <h6 class="font-bold text-blue-800 mb-2 flex items-center">
                                <i class="fas fa-sticky-note text-blue-600 mr-2"></i>
                                Notes administrateur
                            </h6>
                            <p class="text-blue-700">{{ $report->admin_notes }}</p>
                        </div>
                    @endif
                </div>

                <!-- Informations sur l'annonce -->
                @if($report->urgentSale)
                    <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6">
                        <div class="mb-4 text-center">
                            <h3 class="text-xl sm:text-2xl font-bold text-red-800 mb-1 sm:mb-2">Annonce signalée</h3>
                            <p class="text-sm sm:text-base lg:text-lg text-red-700">Détails de l'annonce concernée</p>
                        </div>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                            <div class="lg:col-span-2">
                                <div class="bg-red-50 rounded-lg p-4 border border-red-200 mb-4">
                                    <h5 class="text-lg font-bold text-red-800 mb-2">{{ $report->urgentSale->title }}</h5>
                                    <p class="text-gray-700 mb-3">{{ Str::limit($report->urgentSale->description, 200) }}</p>
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                    <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                        <p class="text-sm font-medium text-green-800 mb-1">Prix</p>
                                        <p class="text-lg font-bold text-green-700 flex items-center">
                                            <i class="fas fa-euro-sign text-green-600 mr-2"></i>
                                            {{ number_format($report->urgentSale->price, 0, ',', ' ') }} €
                                        </p>
                                    </div>
                                    
                                    <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                                        <p class="text-sm font-medium text-blue-800 mb-1">Localisation</p>
                                        <p class="text-blue-700 flex items-center">
                                            <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                                            {{ $report->urgentSale->city }}
                                        </p>
                                    </div>
                                    
                                    <div class="bg-purple-50 rounded-lg p-3 border border-purple-200 sm:col-span-2">
                                        <p class="text-sm font-medium text-purple-800 mb-1">Publié le</p>
                                        <p class="text-purple-700 flex items-center">
                                            <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>
                                            {{ $report->urgentSale->created_at->format('d/m/Y à H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="lg:col-span-1">
                                @if($report->urgentSale->photos && count($report->urgentSale->photos) > 0)
                                    <div class="bg-gray-50 rounded-lg p-2 border border-gray-200">
                                        <img src="{{ Storage::url($report->urgentSale->photos[0]) }}" 
                                             alt="Photo de l'annonce" class="w-full h-48 object-cover rounded-lg">
                                    </div>
                                @else
                                    <div class="bg-gray-50 rounded-lg p-8 border border-gray-200 text-center">
                                        <i class="fas fa-image text-gray-400 text-3xl mb-2"></i>
                                        <p class="text-gray-500 text-sm">Aucune photo</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mt-6 text-center">
                            <a href="{{ route('urgent-sales.show', $report->urgentSale) }}" target="_blank" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 inline-flex items-center">
                                <i class="fas fa-external-link-alt mr-2"></i>Voir l'annonce complète
                            </a>
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6">
                        <div class="text-center py-8">
                            <div class="mx-auto h-24 w-24 text-yellow-400 mb-4">
                                <i class="fas fa-exclamation-triangle text-6xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Annonce supprimée</h3>
                            <p class="text-gray-500">L'annonce associée à ce signalement a été supprimée.</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar avec informations utilisateur et actions -->
            <div class="space-y-6 sm:space-y-8">
                <!-- Informations sur l'utilisateur -->
                @if($report->user)
                    <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6">
                        <div class="mb-4 text-center">
                            <h3 class="text-lg sm:text-xl font-bold text-red-800 mb-1">Utilisateur signalant</h3>
                            <p class="text-sm text-red-700">Informations sur le signaleur</p>
                        </div>
                        
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 bg-red-600 text-white rounded-full flex items-center justify-center text-xl font-bold mx-auto mb-3">
                                {{ strtoupper(substr($report->user->name, 0, 2)) }}
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900">{{ $report->user->name }}</h4>
                            <p class="text-gray-600">{{ $report->user->email }}</p>
                        </div>
                        
                        <div class="border-t border-red-200 pt-4 space-y-3">
                            <div class="bg-red-50 rounded-lg p-3 border border-red-200">
                                <p class="text-sm font-medium text-red-800 mb-1">Membre depuis</p>
                                <p class="text-red-700 flex items-center">
                                    <i class="fas fa-user-clock text-red-600 mr-2"></i>
                                    {{ $report->user->created_at->format('d/m/Y') }}
                                </p>
                            </div>
                            
                            <div class="bg-red-50 rounded-lg p-3 border border-red-200">
                                <p class="text-sm font-medium text-red-800 mb-1">Signalements effectués</p>
                                <p class="text-red-700 flex items-center">
                                    <i class="fas fa-flag text-red-600 mr-2"></i>
                                    {{ \App\Models\UrgentSaleReport::where('user_id', $report->user->id)->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Actions administrateur -->
                <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6">
                    <div class="mb-4 text-center">
                        <h3 class="text-lg sm:text-xl font-bold text-red-800 mb-1">Actions administrateur</h3>
                        <p class="text-sm text-red-700">Gérer ce signalement</p>
                    </div>
                    
                    <form action="{{ route('administrateur.reports.urgent-sales.update-status', $report) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Changer le statut</label>
                            <div class="relative">
                                <i class="fas fa-flag absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <select name="status" id="status" class="w-full pl-10 pr-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50" required>
                                    <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>En attente</option>
                                    <option value="reviewed" {{ $report->status === 'reviewed' ? 'selected' : '' }}>Examiné</option>
                                    <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Résolu</option>
                                    <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>Rejeté</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-2">Notes administrateur</label>
                            <textarea name="admin_notes" id="admin_notes" rows="4" placeholder="Ajoutez vos notes sur ce signalement..." class="w-full px-4 py-3 rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 resize-none">{{ $report->admin_notes }}</textarea>
                        </div>
                        
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center">
                            <i class="fas fa-save mr-2"></i>Mettre à jour le signalement
                        </button>
                    </form>
                    
                    <div class="border-t border-red-200 mt-6 pt-6">
                        <form action="{{ route('administrateur.reports.urgent-sales.destroy', $report) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce signalement ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-100 hover:bg-red-200 text-red-800 font-bold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center border border-red-300">
                                <i class="fas fa-trash mr-2"></i>Supprimer le signalement
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: bold;
    margin: 0 auto;
}
</style>
@endsection