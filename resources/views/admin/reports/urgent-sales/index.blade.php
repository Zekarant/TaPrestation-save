@extends('layouts.admin-modern')

@section('title', 'Signalements Annonces')

@section('content')
<div class="bg-red-50 min-h-screen">
    <!-- Bannière d'en-tête -->
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-red-900 mb-2 leading-tight">
                    Signalements Annonces
                </h1>
                <p class="text-base sm:text-lg text-red-700 max-w-2xl mx-auto">
                    Gérer les signalements des annonces urgentes
                </p>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-6 sm:mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <div class="bg-white rounded-xl shadow-lg border border-red-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-600 uppercase tracking-wide">Total</p>
                        <p class="text-3xl font-bold text-red-900">{{ $stats['total'] }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <i class="fas fa-flag text-2xl text-red-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-red-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-yellow-600 uppercase tracking-wide">En attente</p>
                        <p class="text-3xl font-bold text-red-900">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i class="fas fa-clock text-2xl text-yellow-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-red-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 uppercase tracking-wide">Examinés</p>
                        <p class="text-3xl font-bold text-red-900">{{ $stats['reviewed'] }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-eye text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-red-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600 uppercase tracking-wide">Résolus</p>
                        <p class="text-3xl font-bold text-red-900">{{ $stats['resolved'] }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-check text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section des filtres -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6 mb-6 sm:mb-8">
            <div class="mb-4 text-center">
                <h3 class="text-xl sm:text-2xl font-bold text-red-800 mb-1 sm:mb-2">Filtres de recherche</h3>
                <p class="text-sm sm:text-base lg:text-lg text-red-700">Affinez votre recherche pour trouver les signalements</p>
            </div>
            
            <form method="GET" action="{{ route('administrateur.reports.urgent-sales.index') }}" class="space-y-4 sm:space-y-6">
                <!-- Première ligne de filtres -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <!-- Statut -->
                    <div>
                        <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Statut</label>
                        <div class="relative">
                            <i class="fas fa-flag absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select name="status" id="status" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Tous les statuts</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Examiné</option>
                                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Résolu</option>
                                <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Rejeté</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Raison -->
                    <div>
                        <label for="reason" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Raison</label>
                        <div class="relative">
                            <i class="fas fa-exclamation-triangle absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select name="reason" id="reason" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Toutes les raisons</option>
                                <option value="inappropriate_content" {{ request('reason') === 'inappropriate_content' ? 'selected' : '' }}>Contenu inapproprié</option>
                                <option value="fraud" {{ request('reason') === 'fraud' ? 'selected' : '' }}>Fraude</option>
                                <option value="spam" {{ request('reason') === 'spam' ? 'selected' : '' }}>Spam</option>
                                <option value="fake_listing" {{ request('reason') === 'fake_listing' ? 'selected' : '' }}>Fausse annonce</option>
                                <option value="other" {{ request('reason') === 'other' ? 'selected' : '' }}>Autre</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Recherche -->
                    <div class="lg:col-span-2">
                        <label for="search" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Recherche</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Titre, description, utilisateur..." class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 focus:ring-opacity-50 text-sm sm:text-base">
                        </div>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-4 sm:pt-6 border-t-2 border-red-200">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-search mr-2"></i>Appliquer les filtres
                    </button>
                    
                    <button type="button" onclick="clearFilters()" class="flex-1 bg-red-100 hover:bg-red-200 text-red-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-times mr-2"></i>Effacer tout
                    </button>
                    
                    @if(request('status') || request('reason') || request('search'))
                        <a href="{{ route('administrateur.reports.urgent-sales.index') }}" class="bg-white hover:bg-gray-50 text-red-600 border border-red-200 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                            <i class="fas fa-undo mr-2"></i>Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Section des signalements -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6 mb-6 sm:mb-8">
            <div class="mb-4 text-center">
                <h3 class="text-xl sm:text-2xl font-bold text-red-800 mb-1 sm:mb-2">Signalements ({{ $reports->total() }})</h3>
                <p class="text-sm sm:text-base lg:text-lg text-red-700">Liste des signalements d'annonces urgentes</p>
            </div>
            
            @if($reports->count() > 0)
                <!-- Version desktop -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-red-200">
                        <thead class="bg-red-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-red-800 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-red-800 uppercase tracking-wider">Titre de l'annonce</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-red-800 uppercase tracking-wider">Utilisateur</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-red-800 uppercase tracking-wider">Raison</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-red-800 uppercase tracking-wider">Statut</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-red-800 uppercase tracking-wider">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-red-800 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-red-100">
                            @foreach($reports as $report)
                                <tr class="hover:bg-red-50 transition-colors duration-200">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $report->id }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-900">
                                        @if($report->urgentSale)
                                            <a href="{{ route('urgent-sales.show', $report->urgentSale) }}" target="_blank" class="text-red-600 hover:text-red-800 font-medium hover:underline">
                                                {{ Str::limit($report->urgentSale->title, 50) }}
                                            </a>
                                        @else
                                            <span class="text-gray-500">Annonce supprimée</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @if($report->user)
                                            <div class="flex items-center">
                                                <img src="{{ $report->user->avatar ?? asset('images/default-avatar.png') }}" alt="Avatar" class="h-8 w-8 rounded-full mr-3">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $report->user->name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $report->user->email }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-500">Utilisateur supprimé</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @switch($report->reason)
                                            @case('inappropriate_content')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Contenu inapproprié</span>
                                                @break
                                            @case('fraud')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Fraude</span>
                                                @break
                                            @case('spam')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Spam</span>
                                                @break
                                            @case('fake_listing')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Fausse annonce</span>
                                                @break
                                            @default
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst(str_replace('_', ' ', $report->reason)) }}</span>
                                        @endswitch
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @switch($report->status)
                                            @case('pending')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                                                @break
                                            @case('reviewed')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Examiné</span>
                                                @break
                                            @case('resolved')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Résolu</span>
                                                @break
                                            @case('dismissed')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Rejeté</span>
                                                @break
                                            @default
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ $report->status }}</span>
                                        @endswitch
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('administrateur.reports.urgent-sales.show', $report) }}" class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 px-3 py-1 rounded-lg transition-colors duration-200" title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Version mobile -->
                <div class="lg:hidden space-y-4">
                    @foreach($reports as $report)
                        <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 mb-1">#{{ $report->id }}</h4>
                                    @if($report->urgentSale)
                                        <a href="{{ route('urgent-sales.show', $report->urgentSale) }}" target="_blank" class="text-red-600 hover:text-red-800 font-medium text-sm hover:underline">
                                            {{ Str::limit($report->urgentSale->title, 40) }}
                                        </a>
                                    @else
                                        <span class="text-gray-500 text-sm">Annonce supprimée</span>
                                    @endif
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('administrateur.reports.urgent-sales.show', $report) }}" class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 px-2 py-1 rounded text-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                            
                            @if($report->user)
                                <div class="flex items-center mb-3">
                                    <img src="{{ $report->user->avatar ?? asset('images/default-avatar.png') }}" alt="Avatar" class="h-6 w-6 rounded-full mr-2">
                                    <div class="text-sm">
                                        <span class="font-medium text-gray-900">{{ $report->user->name }}</span>
                                        <span class="text-gray-500 ml-1">{{ $report->user->email }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="text-sm text-gray-500 mb-3">Utilisateur supprimé</div>
                            @endif
                            
                            <div class="flex flex-wrap gap-2 mb-2">
                                @switch($report->reason)
                                    @case('inappropriate_content')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Contenu inapproprié</span>
                                        @break
                                    @case('fraud')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Fraude</span>
                                        @break
                                    @case('spam')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Spam</span>
                                        @break
                                    @case('fake_listing')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Fausse annonce</span>
                                        @break
                                    @default
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst(str_replace('_', ' ', $report->reason)) }}</span>
                                @endswitch
                                
                                @switch($report->status)
                                    @case('pending')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                                        @break
                                    @case('reviewed')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Examiné</span>
                                        @break
                                    @case('resolved')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Résolu</span>
                                        @break
                                    @case('dismissed')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Rejeté</span>
                                        @break
                                    @default
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ $report->status }}</span>
                                @endswitch
                            </div>
                            
                            <div class="text-xs text-gray-500">{{ $report->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                @if($reports->hasPages())
                    <div class="mt-6 bg-red-50 rounded-lg p-4 border border-red-200">
                        <div class="flex flex-col sm:flex-row items-center justify-between">
                            <div class="text-sm text-red-700 mb-4 sm:mb-0">
                                Affichage de {{ $reports->firstItem() }} à {{ $reports->lastItem() }} sur {{ $reports->total() }} résultats
                            </div>
                            <div class="flex space-x-1">
                                {{-- Bouton précédent --}}
                                @if ($reports->onFirstPage())
                                    <span class="px-3 py-2 text-sm font-medium text-gray-500 bg-gray-100 border border-gray-300 rounded-l-md cursor-not-allowed">
                                        <i class="fas fa-chevron-left"></i>
                                    </span>
                                @else
                                    <a href="{{ $reports->appends(request()->query())->previousPageUrl() }}" class="px-3 py-2 text-sm font-medium text-red-600 bg-white border border-red-300 rounded-l-md hover:bg-red-50 transition-colors duration-200">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                @endif
                                
                                {{-- Numéros de page --}}
                                @foreach ($reports->appends(request()->query())->getUrlRange(1, $reports->lastPage()) as $page => $url)
                                    @if ($page == $reports->currentPage())
                                        <span class="px-3 py-2 text-sm font-medium text-white bg-red-600 border border-red-600">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $url }}" class="px-3 py-2 text-sm font-medium text-red-600 bg-white border border-red-300 hover:bg-red-50 transition-colors duration-200">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach
                                
                                {{-- Bouton suivant --}}
                                @if ($reports->hasMorePages())
                                    <a href="{{ $reports->appends(request()->query())->nextPageUrl() }}" class="px-3 py-2 text-sm font-medium text-red-600 bg-white border border-red-300 rounded-r-md hover:bg-red-50 transition-colors duration-200">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                @else
                                    <span class="px-3 py-2 text-sm font-medium text-gray-500 bg-gray-100 border border-gray-300 rounded-r-md cursor-not-allowed">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <!-- État vide -->
                <div class="text-center py-12">
                    <div class="mx-auto h-24 w-24 text-red-300 mb-4">
                        <i class="fas fa-flag text-6xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun signalement trouvé</h3>
                    <p class="text-gray-500 mb-6">Il n'y a aucun signalement correspondant à vos critères de recherche.</p>
                    <a href="{{ route('administrateur.reports.urgent-sales.index') }}" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 transition-colors duration-200">
                        <i class="fas fa-undo mr-2"></i>Réinitialiser les filtres
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection