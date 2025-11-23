@extends('layouts.admin-modern')

@section('title', 'Signalements Services')

@section('content')
<div class="bg-blue-50 min-h-screen">
    <!-- Bannière d'en-tête -->
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-blue-900 mb-2 leading-tight">
                    Signalements Services
                </h1>
                <p class="text-base sm:text-lg text-blue-700 max-w-2xl mx-auto">
                    Gérer les signalements des services
                </p>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-6 sm:mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 uppercase tracking-wide">Total</p>
                        <p class="text-3xl font-bold text-blue-900">{{ $stats['total'] }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-exclamation-triangle text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-yellow-600 uppercase tracking-wide">En attente</p>
                        <p class="text-3xl font-bold text-blue-900">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i class="fas fa-clock text-2xl text-yellow-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-indigo-600 uppercase tracking-wide">En cours d'examen</p>
                        <p class="text-3xl font-bold text-blue-900">{{ $stats['under_review'] }}</p>
                    </div>
                    <div class="p-3 bg-indigo-100 rounded-full">
                        <i class="fas fa-eye text-2xl text-indigo-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600 uppercase tracking-wide">Résolus</p>
                        <p class="text-3xl font-bold text-blue-900">{{ $stats['resolved'] }}</p>
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
        <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6 mb-6 sm:mb-8">
            <div class="mb-4 text-center">
                <h3 class="text-xl sm:text-2xl font-bold text-blue-800 mb-1 sm:mb-2">Filtres de recherche</h3>
                <p class="text-sm sm:text-base lg:text-lg text-blue-700">Affinez votre recherche pour trouver les signalements</p>
            </div>
            
            <form method="GET" action="{{ route('administrateur.reports.services.index') }}" class="space-y-4 sm:space-y-6">
                <!-- Première ligne de filtres -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <!-- Statut -->
                    <div>
                        <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Statut</label>
                        <div class="relative">
                            <i class="fas fa-flag absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select name="status" id="status" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Tous les statuts</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>Examiné</option>
                                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Résolu</option>
                                <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Rejeté</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Catégorie -->
                    <div>
                        <label for="category" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Catégorie</label>
                        <div class="relative">
                            <i class="fas fa-tag absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select name="category" id="category" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                                <option value="">Toutes les catégories</option>
                                <option value="inappropriate_content" {{ request('category') === 'inappropriate_content' ? 'selected' : '' }}>Contenu inapproprié</option>
                                <option value="false_information" {{ request('category') === 'false_information' ? 'selected' : '' }}>Informations fausses</option>
                                <option value="spam" {{ request('category') === 'spam' ? 'selected' : '' }}>Spam</option>
                                <option value="fraud" {{ request('category') === 'fraud' ? 'selected' : '' }}>Fraude</option>
                                <option value="copyright" {{ request('category') === 'copyright' ? 'selected' : '' }}>Violation de droits d'auteur</option>
                                <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Autre</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Recherche -->
                    <div class="lg:col-span-2">
                        <label for="search" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Recherche</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nom service, description, utilisateur..." class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
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
                    
                    @if(request('status') || request('category') || request('search'))
                        <a href="{{ route('administrateur.reports.services.index') }}" class="bg-white hover:bg-gray-50 text-blue-600 border border-blue-200 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                            <i class="fas fa-undo mr-2"></i>Réinitialiser
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Section des signalements -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6 mb-6 sm:mb-8">
            <div class="mb-4 text-center">
                <h3 class="text-xl sm:text-2xl font-bold text-blue-800 mb-1 sm:mb-2">Signalements ({{ $reports->total() }})</h3>
                <p class="text-sm sm:text-base lg:text-lg text-blue-700">Liste des signalements de services</p>
            </div>
            
            @if($reports->count() > 0)
                <!-- Version desktop -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-blue-200">
                        <thead class="bg-blue-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">Service</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">Catégorie</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">Priorité</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">Statut</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-blue-100">
                            @foreach($reports as $report)
                                <tr class="hover:bg-blue-50 transition-colors duration-200">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $report->id }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-900">
                                        @if($report->service)
                                            <a href="{{ route('services.show', $report->service) }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium hover:underline">
                                                {{ Str::limit($report->service->title, 50) }}
                                            </a>
                                        @else
                                            <span class="text-gray-500">Service supprimé</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @switch($report->category)
                                            @case('inappropriate_content')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Contenu inapproprié</span>
                                                @break
                                            @case('false_information')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Informations fausses</span>
                                                @break
                                            @case('spam')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Spam</span>
                                                @break
                                            @case('fraud')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Fraude</span>
                                                @break
                                            @case('copyright')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Droits d'auteur</span>
                                                @break
                                            @default
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst(str_replace('_', ' ', $report->category)) }}</span>
                                        @endswitch
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @switch($report->priority)
                                            @case('low')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Faible</span>
                                                @break
                                            @case('medium')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Moyenne</span>
                                                @break
                                            @case('high')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Élevée</span>
                                                @break
                                            @case('urgent')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Urgente</span>
                                                @break
                                            @default
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($report->priority) }}</span>
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
                                            <a href="{{ route('administrateur.reports.services.show', $report) }}" class="text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 px-3 py-1 rounded-lg transition-colors duration-200" title="Voir les détails">
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
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 mb-1">#{{ $report->id }}</h4>
                                    @if($report->service)
                                        <a href="{{ route('services.show', $report->service) }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium text-sm hover:underline">
                                            {{ Str::limit($report->service->title, 40) }}
                                        </a>
                                    @else
                                        <span class="text-gray-500 text-sm">Service supprimé</span>
                                    @endif
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('administrateur.reports.services.show', $report) }}" class="text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 px-2 py-1 rounded text-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap gap-2 mb-2">
                                @switch($report->category)
                                    @case('inappropriate_content')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Contenu inapproprié</span>
                                        @break
                                    @case('false_information')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Informations fausses</span>
                                        @break
                                    @case('spam')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Spam</span>
                                        @break
                                    @case('fraud')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Fraude</span>
                                        @break
                                    @case('copyright')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Droits d'auteur</span>
                                        @break
                                    @default
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst(str_replace('_', ' ', $report->category)) }}</span>
                                @endswitch
                                
                                @switch($report->priority)
                                    @case('low')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Faible</span>
                                        @break
                                    @case('medium')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Moyenne</span>
                                        @break
                                    @case('high')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Élevée</span>
                                        @break
                                    @case('urgent')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Urgente</span>
                                        @break
                                    @default
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($report->priority) }}</span>
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
                            
                            <div class="text-xs text-gray-500 mt-2">
                                {{ $report->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="mt-6 sm:mt-8">
                    {{ $reports->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-8 sm:py-12">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun signalement trouvé</h3>
                    <p class="mt-1 text-sm text-gray-500">Aucun signalement ne correspond à vos critères de recherche.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function clearFilters() {
        document.getElementById('status').value = '';
        document.getElementById('category').value = '';
        document.getElementById('search').value = '';
    }
</script>
@endsection