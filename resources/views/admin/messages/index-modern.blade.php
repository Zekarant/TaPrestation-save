@extends('layouts.admin-modern')

@section('title', 'Gestion des Messages - Administration')

@section('content')
<div class="bg-blue-50">
    <!-- Bannière d'en-tête -->
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-blue-900 mb-2 leading-tight">
                    Gestion des Messages
                </h1>
                <p class="text-base sm:text-lg text-blue-700 max-w-2xl mx-auto">
                    Modérez et gérez tous les messages de la plateforme TaPrestation.
                </p>
            </div>
            
            <!-- Actions Header -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                <div class="flex flex-wrap gap-2 sm:gap-3">

                    <a href="{{ route('administrateur.messages.analytics') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Analyses
                    </a>
                    <button class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base" onclick="toggleFilters()">
                    <i class="fas fa-filter mr-2"></i>
                    Afficher les filtres
                </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-6 sm:mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 sm:gap-6">
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-blue-600 uppercase tracking-wide">Total Messages</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ number_format($stats['total']) }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+5% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-envelope text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-orange-600 uppercase tracking-wide">Non Lus</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ number_format($stats['unread']) }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-red-600">
                            <i class="fas fa-arrow-down mr-1"></i>
                            <span>-2% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-exclamation-circle text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-red-600 uppercase tracking-wide">Signalés</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ number_format($stats['reported']) }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-red-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+1% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-flag text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-green-600 uppercase tracking-wide">Aujourd'hui</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ number_format($stats['today']) }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+12% aujourd'hui</span>
                        </div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-calendar-day text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-blue-600 uppercase tracking-wide">Taux Lecture</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ $stats['read_rate'] }}%</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+3% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-eye text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-purple-600 uppercase tracking-wide">Temps Réponse</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ $stats['avg_response_time'] }}h</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-down mr-1"></i>
                            <span>-15% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-clock text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6 mb-6 sm:mb-8">
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-center sm:text-left">
                    <h3 class="text-xl sm:text-2xl font-bold text-blue-800 mb-1 sm:mb-2">Actions Rapides</h3>
                    <p class="text-sm sm:text-base lg:text-lg text-blue-700">Gérez vos messages en lot pour plus d'efficacité</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <button type="button" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base" onclick="bulkModerate('pending')">
                    <i class="fas fa-clock mr-2"></i>
                    Marquer en Attente
                </button>
                
                <button type="button" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base" onclick="bulkModerate('approved')">
                    <i class="fas fa-check mr-2"></i>
                    Approuver Sélectionnés
                </button>
                
                <button type="button" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base" onclick="bulkModerate('hidden')">
                    <i class="fas fa-eye-slash mr-2"></i>
                    Masquer Sélectionnés
                </button>
                
                <form method="POST" action="{{ route('administrateur.messages.cleanup') }}" onsubmit="return confirm('Supprimer tous les messages de plus de 6 mois ?')" class="w-full">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-broom mr-2"></i>
                        Nettoyer Anciens
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <div id="filtersPanel" class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6 mb-6 sm:mb-8" style="display: none;">
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-center sm:text-left">
                    <h3 class="text-xl sm:text-2xl font-bold text-blue-800 mb-1 sm:mb-2">Filtres de recherche</h3>
                    <p class="text-sm sm:text-base lg:text-lg text-blue-700">Affinez votre recherche pour trouver les messages parfaits</p>
                </div>
                <button class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base" onclick="clearFilters()">
                    <i class="fas fa-times mr-2"></i>
                    Effacer tout
                </button>
            </div>
            <form action="{{ route('administrateur.messages.index') }}" method="GET" class="space-y-4 sm:space-y-6">
                <!-- Première ligne de filtres -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4">
                    <!-- Type -->
                    <div>
                        <label for="type" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Type</label>
                        <div class="relative">
                            <i class="fas fa-tag absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select name="type" id="type" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Tous les types</option>
                                <option value="text" {{ request('type') == 'text' ? 'selected' : '' }}>Texte</option>
                                <option value="file" {{ request('type') == 'file' ? 'selected' : '' }}>Fichier</option>
                                <option value="image" {{ request('type') == 'image' ? 'selected' : '' }}>Image</option>
                                <option value="system" {{ request('type') == 'system' ? 'selected' : '' }}>Système</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Statut -->
                    <div>
                        <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Statut</label>
                        <div class="relative">
                            <i class="fas fa-info-circle absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select name="status" id="status" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Tous les statuts</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvé</option>
                                <option value="hidden" {{ request('status') == 'hidden' ? 'selected' : '' }}>Masqué</option>
                                <option value="deleted" {{ request('status') == 'deleted' ? 'selected' : '' }}>Supprimé</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Lecture -->
                    <div>
                        <label for="read_status" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Lecture</label>
                        <div class="relative">
                            <i class="fas fa-eye absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select name="read_status" id="read_status" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Tous</option>
                                <option value="read" {{ request('read_status') == 'read' ? 'selected' : '' }}>Lus</option>
                                <option value="unread" {{ request('read_status') == 'unread' ? 'selected' : '' }}>Non lus</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Signalement -->
                    <div>
                        <label for="reported" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Signalement</label>
                        <div class="relative">
                            <i class="fas fa-flag absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select name="reported" id="reported" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Tous</option>
                                <option value="yes" {{ request('reported') == 'yes' ? 'selected' : '' }}>Signalés</option>
                                <option value="no" {{ request('reported') == 'no' ? 'selected' : '' }}>Non signalés</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Date Début -->
                    <div>
                        <label for="date_from" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Date Début</label>
                        <div class="relative">
                            <i class="fas fa-calendar absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="date" name="date_from" id="date_from" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    
                    <!-- Date Fin -->
                    <div>
                        <label for="date_to" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Date Fin</label>
                        <div class="relative">
                            <i class="fas fa-calendar absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="date" name="date_to" id="date_to" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base" value="{{ request('date_to') }}">
                        </div>
                    </div>
                </div>
                
                <!-- Deuxième ligne de filtres -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <!-- Expéditeur -->
                    <div>
                        <label for="sender" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Expéditeur</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="sender" id="sender" value="{{ request('sender') }}" placeholder="Nom ou email..." class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                        </div>
                    </div>
                    
                    <!-- Destinataire -->
                    <div>
                        <label for="recipient" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Destinataire</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="recipient" id="recipient" value="{{ request('recipient') }}" placeholder="Nom ou email..." class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                        </div>
                    </div>
                    
                    <!-- Recherche -->
                    <div>
                        <label for="search" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Recherche</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Contenu du message..." class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                        </div>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-4 sm:pt-6 border-t-2 border-blue-200">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-search mr-2"></i>Appliquer les filtres
                    </button>
                    
                    <button type="button" onclick="clearFilters()" class="flex-1 bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-times mr-2"></i>Effacer tout
                    </button>
                    
                    @if(request('type') || request('status') || request('read_status') || request('reported') || request('date_from') || request('date_to') || request('sender') || request('recipient') || request('search'))
                        <a href="{{ route('administrateur.messages.index') }}" class="bg-white hover:bg-gray-50 text-blue-600 border border-blue-200 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                            <i class="fas fa-undo mr-2"></i>Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des messages -->
    <!-- Liste des messages -->
    <div class="bg-white rounded-xl shadow-lg border border-blue-100 overflow-hidden">
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 sm:px-6 py-4 sm:py-5">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
                <h3 class="text-lg sm:text-xl font-bold text-white flex items-center">
                    <i class="fas fa-comments mr-2 sm:mr-3"></i>
                    Liste des Messages
                    <span class="ml-2 sm:ml-3 bg-blue-500 text-blue-100 px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium">
                        {{ $messages->total() }} résultats
                    </span>
                </h3>
                <div class="flex items-center space-x-2 sm:space-x-3">
                    <label class="flex items-center text-white cursor-pointer">
                        <input type="checkbox" id="selectAll" class="mr-2 rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm sm:text-base">Tout sélectionner</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6">
            @if($messages->count() > 0)
                <!-- Messages en format carte -->
                <div class="space-y-4 sm:space-y-6">
                            @foreach($messages as $message)
                                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 {{ !$message->is_read ? 'border-l-4 border-l-yellow-400 bg-yellow-50' : '' }} {{ $message->is_reported ? 'border-l-4 border-l-red-400 bg-red-50' : '' }}">
                                    <div class="p-4 sm:p-6">
                                        <!-- En-tête de la carte -->
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="flex items-center space-x-3">
                                                <input type="checkbox" name="message_ids[]" value="{{ $message->id }}" class="message-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-2 mb-1">
                                                        <span class="text-sm font-semibold text-gray-600">#{{ $message->conversation_id ?? 'N/A' }}</span>
                                                        @if($message->client_request)
                                                            <span class="text-xs text-gray-500">{{ Str::limit($message->client_request->title, 30) }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-xs text-gray-400">{{ $message->created_at->format('d/m/Y H:i') }} • {{ $message->created_at->diffForHumans() }}</div>
                                                </div>
                                            </div>
                                            
                                            <!-- Badges de statut -->
                                            <div class="flex flex-wrap gap-2">
                                                @switch($message->type)
                                                    @case('text')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            <i class="fas fa-comment mr-1"></i> Texte
                                                        </span>
                                                        @break
                                                    @case('file')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                            <i class="fas fa-file mr-1"></i> Fichier
                                                        </span>
                                                        @break
                                                    @case('image')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <i class="fas fa-image mr-1"></i> Image
                                                        </span>
                                                        @break
                                                    @default
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            <i class="fas fa-cog mr-1"></i> Système
                                                        </span>
                                                @endswitch
                                                
                                                @switch($message->moderation_status)
                                                    @case('pending')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            En attente
                                                        </span>
                                                        @break
                                                    @case('approved')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            Approuvé
                                                        </span>
                                                        @break
                                                    @case('hidden')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            Masqué
                                                        </span>
                                                        @break
                                                    @case('deleted')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            Supprimé
                                                        </span>
                                                        @break
                                                    @default
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            {{ ucfirst($message->moderation_status ?? 'N/A') }}
                                                        </span>
                                                @endswitch
                                                
                                                @if(!$message->is_read)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-exclamation mr-1"></i> Non lu
                                                    </span>
                                                @endif
                                                
                                                @if($message->is_reported)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <i class="fas fa-flag mr-1"></i> Signalé
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Contenu principal -->
                                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
                                            <!-- Expéditeur -->
                                            <div class="flex items-center space-x-3">
                                                <div class="flex-shrink-0">
                                                    @if($message->sender && $message->sender->profile_photo)
                                                        <img src="{{ asset('storage/' . $message->sender->profile_photo) }}" alt="Photo" class="w-10 h-10 rounded-full object-cover">
                                                    @else
                                                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-user text-white text-sm"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $message->sender->name ?? 'Utilisateur supprimé' }}</p>
                                                    <p class="text-xs text-gray-500 truncate">{{ $message->sender->email ?? 'N/A' }}</p>
                                                    <p class="text-xs text-blue-600 font-medium">Expéditeur</p>
                                                </div>
                                            </div>
                                            
                                            <!-- Flèche -->
                                            <div class="flex items-center justify-center">
                                                <i class="fas fa-arrow-right text-gray-400 text-lg"></i>
                                            </div>
                                            
                                            <!-- Destinataire -->
                                            <div class="flex items-center space-x-3">
                                                <div class="flex-shrink-0">
                                                    @if($message->recipient && $message->recipient->profile_photo)
                                                        <img src="{{ asset('storage/' . $message->recipient->profile_photo) }}" alt="Photo" class="w-10 h-10 rounded-full object-cover">
                                                    @else
                                                        <div class="w-10 h-10 bg-gray-500 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-user text-white text-sm"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $message->recipient->name ?? 'Utilisateur supprimé' }}</p>
                                                    <p class="text-xs text-gray-500 truncate">{{ $message->recipient->email ?? 'N/A' }}</p>
                                                    <p class="text-xs text-green-600 font-medium">Destinataire</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Contenu du message -->
                                        <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                            @if($message->type === 'text')
                                                <p class="text-sm text-gray-700">{{ Str::limit($message->content, 120) }}</p>
                                            @elseif($message->type === 'file')
                                                <div class="flex items-center text-indigo-600">
                                                    <i class="fas fa-file mr-2"></i>
                                                    <span class="text-sm">Fichier: {{ $message->file_name ?? 'fichier.ext' }}</span>
                                                </div>
                                            @elseif($message->type === 'image')
                                                <div class="flex items-center text-green-600">
                                                    <i class="fas fa-image mr-2"></i>
                                                    <span class="text-sm">Image: {{ $message->file_name ?? 'image.jpg' }}</span>
                                                </div>
                                            @else
                                                <div class="flex items-center text-gray-600">
                                                    <i class="fas fa-cog mr-2"></i>
                                                    <span class="text-sm">Message système</span>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('administrateur.messages.show', $message->id) }}" class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors duration-200">
                                                <i class="fas fa-eye mr-1"></i> Voir
                                            </a>
                                            
                                            @if($message->moderation_status !== 'approved')
                                                <form method="POST" action="{{ route('administrateur.messages.moderate', $message->id) }}" style="display: inline;">
                                                    @csrf
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-green-300 text-xs font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 transition-colors duration-200">
                                                        <i class="fas fa-check mr-1"></i> Approuver
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($message->moderation_status !== 'hidden')
                                                <form method="POST" action="{{ route('administrateur.messages.moderate', $message->id) }}" style="display: inline;">
                                                    @csrf
                                                    <input type="hidden" name="action" value="hide">
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-yellow-300 text-xs font-medium rounded-md text-yellow-700 bg-yellow-50 hover:bg-yellow-100 transition-colors duration-200">
                                                        <i class="fas fa-eye-slash mr-1"></i> Masquer
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <button type="button" onclick="confirmDelete('{{ $message->id }}')" class="inline-flex items-center px-3 py-1.5 border border-red-300 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 transition-colors duration-200">
                                                <i class="fas fa-trash mr-1"></i> Supprimer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                </div>

                <!-- Actions en masse -->
                <div class="mt-6 sm:mt-8 pt-6 border-t-2 border-blue-100">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div class="flex flex-wrap gap-2 sm:gap-3">
                            <button type="button" onclick="bulkModerate('approved')" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-200 shadow-sm hover:shadow-md">
                                <i class="fas fa-check mr-2"></i> Approuver
                            </button>
                            <button type="button" onclick="bulkModerate('hidden')" class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition duration-200 shadow-sm hover:shadow-md">
                                <i class="fas fa-eye-slash mr-2"></i> Masquer
                            </button>
                            <button type="button" onclick="bulkMarkAsRead()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 shadow-sm hover:shadow-md">
                                <i class="fas fa-eye mr-2"></i> Marquer comme lus
                            </button>
                            <button type="button" onclick="bulkDelete()" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200 shadow-sm hover:shadow-md">
                                <i class="fas fa-trash mr-2"></i> Supprimer
                            </button>
                        </div>
                        <div class="bg-blue-50 px-4 py-2 rounded-lg border border-blue-200">
                            <span id="selectedCount" class="font-semibold text-blue-800">0</span>
                            <span class="text-blue-600 text-sm">message(s) sélectionné(s)</span>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6 sm:mt-8 pt-6 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="text-sm text-gray-600">
                            Affichage de <span class="font-semibold text-blue-600">{{ $messages->firstItem() }}</span> à <span class="font-semibold text-blue-600">{{ $messages->lastItem() }}</span> sur <span class="font-semibold text-blue-600">{{ $messages->total() }}</span> résultats
                        </div>
                        <div class="pagination-wrapper">
                            {{ $messages->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            @else
                <!-- État vide -->
                <div class="text-center py-12 sm:py-16">
                    <div class="mx-auto w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mb-6">
                        <i class="fas fa-envelope-open text-3xl text-blue-500"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucun message trouvé</h3>
                    <p class="text-gray-600 mb-6">Aucun message ne correspond aux critères de recherche actuels.</p>
                    <a href="{{ route('administrateur.messages.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200">
                        <i class="fas fa-refresh mr-2"></i> Réinitialiser les filtres
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer ce message ? Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Gestion de la sélection multiple
$(document).ready(function() {
    // Toggle all checkboxes
    $('#selectAll, #selectAllTable').change(function() {
        $('.message-checkbox').prop('checked', this.checked);
        updateSelectedCount();
    });
    
    // Update count when individual checkbox changes
    $('.message-checkbox').change(function() {
        updateSelectedCount();
    });
    
    // Auto-submit form on filter change
    $('#type, #status, #read_status, #reported').change(function() {
        $(this).closest('form').submit();
    });
});

function updateSelectedCount() {
    const count = $('.message-checkbox:checked').length;
    $('#selectedCount').text(count);
    
    // Update select all checkbox state
    const total = $('.message-checkbox').length;
    $('#selectAll, #selectAllTable').prop('indeterminate', count > 0 && count < total);
    $('#selectAll, #selectAllTable').prop('checked', count === total && total > 0);
}

function confirmDelete(messageId) {
    const form = document.getElementById('deleteForm');
    form.action = `/administrateur/messages/${messageId}`;
    $('#deleteModal').modal('show');
}

function bulkModerate(action) {
    const selected = $('.message-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selected.length === 0) {
        alert('Veuillez sélectionner au moins un message.');
        return;
    }
    
    const actionText = {
        'approved': 'approuver',
        'hidden': 'masquer',
        'pending': 'marquer en attente'
    };
    
    if (confirm(`${actionText[action]} ${selected.length} message(s) ?`)) {
        submitBulkAction('{{ route("administrateur.messages.bulk-moderate") }}', selected, { action: action });
    }
}

function bulkMarkAsRead() {
    const selected = $('.message-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selected.length === 0) {
        alert('Veuillez sélectionner au moins un message.');
        return;
    }
    
    if (confirm(`Marquer ${selected.length} message(s) comme lu(s) ?`)) {
        submitBulkAction('{{ route("administrateur.messages.bulk-mark-read") }}', selected);
    }
}

function bulkDelete() {
    const selected = $('.message-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selected.length === 0) {
        alert('Veuillez sélectionner au moins un message.');
        return;
    }
    
    if (confirm(`Supprimer définitivement ${selected.length} message(s) ?`)) {
        submitBulkAction('{{ route("administrateur.messages.bulk-delete") }}', selected, {}, 'DELETE');
    }
}

function submitBulkAction(url, messageIds, extraData = {}, method = 'POST') {
    const form = $('<form>', {
        method: 'POST',
        action: url
    });
    
    form.append($('<input>', {
        type: 'hidden',
        name: '_token',
        value: '{{ csrf_token() }}'
    }));
    
    if (method === 'DELETE') {
        form.append($('<input>', {
            type: 'hidden',
            name: '_method',
            value: 'DELETE'
        }));
    }
    
    messageIds.forEach(id => {
        form.append($('<input>', {
            type: 'hidden',
            name: 'message_ids[]',
            value: id
        }));
    });
    
    Object.keys(extraData).forEach(key => {
        form.append($('<input>', {
            type: 'hidden',
            name: key,
            value: extraData[key]
        }));
    });
    
    $('body').append(form);
    form.submit();
}

// Toggle filters functionality
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggleFilters');
    const filtersForm = document.getElementById('filtersForm');
    const buttonText = document.getElementById('filterButtonText');
    const chevron = document.getElementById('filterChevron');
    
    let filtersVisible = false;
    
    toggleButton.addEventListener('click', function() {
        filtersVisible = !filtersVisible;
        
        if (filtersVisible) {
            filtersForm.style.display = 'block';
            buttonText.textContent = 'Masquer les filtres';
            chevron.classList.remove('fa-chevron-down');
            chevron.classList.add('fa-chevron-up');
        } else {
            filtersForm.style.display = 'none';
            buttonText.textContent = 'Afficher les filtres';
            chevron.classList.remove('fa-chevron-up');
            chevron.classList.add('fa-chevron-down');
        }
    });
});
</script>
@endpush