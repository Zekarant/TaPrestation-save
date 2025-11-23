@extends('layouts.admin-modern')

@section('title', 'Signalements Équipements')

@section('content')
<div class="bg-green-50 min-h-screen">
    <!-- Bannière d'en-tête -->
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-green-900 mb-2 leading-tight">
                    Signalements Équipements
                </h1>
                <p class="text-base sm:text-lg text-green-700 max-w-2xl mx-auto">
                    Gérer les signalements des équipements
                </p>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-6 sm:mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600 uppercase tracking-wide">Total</p>
                        <p class="text-3xl font-bold text-green-900">{{ $stats['total'] }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-tools text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-yellow-600 uppercase tracking-wide">En attente</p>
                        <p class="text-3xl font-bold text-green-900">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i class="fas fa-clock text-2xl text-yellow-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 uppercase tracking-wide">En cours</p>
                        <p class="text-3xl font-bold text-green-900">{{ $stats['under_review'] }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-search text-2xl text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6 hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600 uppercase tracking-wide">Résolus</p>
                        <p class="text-3xl font-bold text-green-900">{{ $stats['resolved'] }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-check text-2xl text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-6 sm:mb-8">
        <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6">
            <div class="flex items-center mb-6">
                <div class="p-2 bg-green-100 rounded-lg mr-3">
                    <i class="fas fa-filter text-green-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-green-900">Filtres</h3>
            </div>
            <form method="GET" action="{{ route('administrateur.reports.equipments.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div>
                        <label for="status" class="block text-sm font-medium text-green-700 mb-2">Statut</label>
                        <select name="status" id="status" class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                            <option value="">Tous les statuts</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>En cours</option>
                            <option value="investigating" {{ request('status') === 'investigating' ? 'selected' : '' }}>Investigation</option>
                            <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Résolu</option>
                            <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Rejeté</option>
                            <option value="escalated" {{ request('status') === 'escalated' ? 'selected' : '' }}>Escaladé</option>
                        </select>
                    </div>
                    <div>
                        <label for="category" class="block text-sm font-medium text-green-700 mb-2">Catégorie</label>
                        <select name="category" id="category" class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                            <option value="">Toutes les catégories</option>
                            <option value="safety" {{ request('category') === 'safety' ? 'selected' : '' }}>Sécurité</option>
                            <option value="condition" {{ request('category') === 'condition' ? 'selected' : '' }}>État du matériel</option>
                            <option value="fraud" {{ request('category') === 'fraud' ? 'selected' : '' }}>Fraude</option>
                            <option value="inappropriate" {{ request('category') === 'inappropriate' ? 'selected' : '' }}>Contenu inapproprié</option>
                            <option value="pricing" {{ request('category') === 'pricing' ? 'selected' : '' }}>Prix abusif</option>
                            <option value="availability" {{ request('category') === 'availability' ? 'selected' : '' }}>Disponibilité</option>
                            <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Autre</option>
                        </select>
                    </div>
                    <div>
                        <label for="search" class="block text-sm font-medium text-green-700 mb-2">Recherche</label>
                        <input type="text" name="search" id="search" class="w-full px-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" 
                               placeholder="Nom équipement, description..." value="{{ request('search') }}">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center justify-center">
                            <i class="fas fa-search mr-2"></i> Filtrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des signalements -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-6 sm:mb-8">
        <div class="bg-white rounded-xl shadow-lg border border-green-200">
            <div class="px-6 py-4 border-b border-green-200">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg mr-3">
                        <i class="fas fa-list text-green-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-green-900">Signalements ({{ $reports->total() }})</h3>
                </div>
            </div>
            <div class="p-6">
                @if($reports->count() > 0)
                    <!-- Vue desktop -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-green-200">
                            <thead class="bg-green-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Équipement</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Catégorie</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Priorité</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-green-200">
                                @foreach($reports as $report)
                                    <tr class="hover:bg-green-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-900">#{{ $report->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if($report->equipment && $report->equipment->images->count() > 0)
                                                    <img src="{{ asset('storage/' . $report->equipment->images->first()->image_path) }}" 
                                                         alt="{{ $report->equipment->title }}" 
                                                         class="w-12 h-12 rounded-lg object-cover mr-3">
                                                @endif
                                                <div>
                                                    <div class="text-sm font-medium text-green-900">{{ $report->equipment->title ?? 'Équipement supprimé' }}</div>
                                                    <div class="text-sm text-green-600">{{ Str::limit($report->equipment->description ?? '', 50) }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @switch($report->category)
                                                @case('safety')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Sécurité</span>
                                                    @break
                                                @case('condition')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">État</span>
                                                    @break
                                                @case('fraud')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Fraude</span>
                                                    @break
                                                @case('inappropriate')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Inapproprié</span>
                                                    @break
                                                @case('pricing')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Prix</span>
                                                    @break
                                                @case('availability')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">Disponibilité</span>
                                                    @break
                                                @default
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($report->category) }}</span>
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @switch($report->priority)
                                                @case('urgent')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Urgent</span>
                                                    @break
                                                @case('high')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Élevée</span>
                                                    @break
                                                @case('medium')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Moyenne</span>
                                                    @break
                                                @case('low')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Faible</span>
                                                    @break
                                                @default
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($report->priority) }}</span>
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @switch($report->status)
                                                @case('pending')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                                                    @break
                                                @case('under_review')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">En cours</span>
                                                    @break
                                                @case('investigating')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Investigation</span>
                                                    @break
                                                @case('resolved')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Résolu</span>
                                                    @break
                                                @case('dismissed')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Rejeté</span>
                                                    @break
                                                @case('escalated')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Escaladé</span>
                                                    @break
                                                @default
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ $report->status }}</span>
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('administrateur.reports.equipments.show', $report) }}" 
                                               class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg text-sm transition-colors inline-flex items-center">
                                                <i class="fas fa-eye mr-1"></i> Voir
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Vue mobile -->
                    <div class="lg:hidden space-y-4">
                        @foreach($reports as $report)
                            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                <div class="flex justify-between items-start mb-3">
                                    <span class="text-sm font-medium text-green-900">#{{ $report->id }}</span>
                                    <span class="text-xs text-green-600">{{ $report->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="flex items-center mb-3">
                                    @if($report->equipment && $report->equipment->images->count() > 0)
                                        <img src="{{ asset('storage/' . $report->equipment->images->first()->image_path) }}" 
                                             alt="{{ $report->equipment->title }}" 
                                             class="w-12 h-12 rounded-lg object-cover mr-3">
                                    @endif
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-green-900">{{ $report->equipment->title ?? 'Équipement supprimé' }}</div>
                                        <div class="text-xs text-green-600">{{ Str::limit($report->equipment->description ?? '', 30) }}</div>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2 mb-3">
                                    @switch($report->category)
                                        @case('safety')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Sécurité</span>
                                            @break
                                        @case('condition')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">État</span>
                                            @break
                                        @case('fraud')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Fraude</span>
                                            @break
                                        @case('inappropriate')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Inapproprié</span>
                                            @break
                                        @case('pricing')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Prix</span>
                                            @break
                                        @case('availability')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">Disponibilité</span>
                                            @break
                                        @default
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($report->category) }}</span>
                                    @endswitch
                                    @switch($report->priority)
                                        @case('urgent')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Urgent</span>
                                            @break
                                        @case('high')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Élevée</span>
                                            @break
                                        @case('medium')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Moyenne</span>
                                            @break
                                        @case('low')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Faible</span>
                                            @break
                                        @default
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($report->priority) }}</span>
                                    @endswitch
                                    @switch($report->status)
                                        @case('pending')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                                            @break
                                        @case('under_review')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">En cours</span>
                                            @break
                                        @case('investigating')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Investigation</span>
                                            @break
                                        @case('resolved')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Résolu</span>
                                            @break
                                        @case('dismissed')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Rejeté</span>
                                            @break
                                        @case('escalated')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Escaladé</span>
                                            @break
                                        @default
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ $report->status }}</span>
                                    @endswitch
                                </div>
                                <div class="flex justify-end">
                                    <a href="{{ route('administrateur.reports.equipments.show', $report) }}" 
                                       class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg text-sm transition-colors inline-flex items-center">
                                        <i class="fas fa-eye mr-1"></i> Voir
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6 flex justify-center">
                        {{ $reports->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="p-4 bg-green-100 rounded-full inline-block mb-4">
                            <i class="fas fa-inbox text-4xl text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-medium text-green-900 mb-2">Aucun signalement trouvé</h3>
                        <p class="text-green-600">Il n'y a actuellement aucun signalement d'équipement correspondant à vos critères.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection