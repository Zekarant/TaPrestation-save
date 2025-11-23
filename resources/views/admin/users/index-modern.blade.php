@extends('layouts.admin-modern')

@section('title', 'Gestion des Utilisateurs')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="bg-blue-50">
    <!-- Bannière d'en-tête -->
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-blue-900 mb-2 leading-tight">
                    Gestion des Utilisateurs
                </h1>
                <p class="text-base sm:text-lg text-blue-700 max-w-2xl mx-auto">
                    Gérez tous les utilisateurs de la plateforme TaPrestation.
                </p>
            </div>
            
            <!-- Actions Header -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <span class="text-xs sm:text-sm font-semibold text-blue-800">Total :</span>
                    <span class="px-2 sm:px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs sm:text-sm font-bold">
                        {{ $users->total() ?? 0 }} utilisateur(s)
                    </span>
                </div>
                <div class="flex gap-3">
                    <button onclick="toggleFilters()" class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                    <i class="fas fa-filter mr-2"></i>Afficher les filtres
                </button>
                    <a href="{{ route('administrateur.users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-plus mr-2"></i>Nouvel utilisateur
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-6 sm:mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-blue-600 mb-1">Total Utilisateurs</p>
                        <p class="text-2xl sm:text-3xl font-bold text-blue-900">{{ $users->total() ?? 0 }}</p>
                        <p class="text-xs text-blue-500 mt-1">+ 8% ce mois</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-green-600 mb-1">Utilisateurs Actifs</p>
                        <p class="text-2xl sm:text-3xl font-bold text-green-900">{{ $users->where('is_blocked', false)->count() ?? 0 }}</p>
                        <p class="text-xs text-green-500 mt-1">+ 12% ce mois</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-user-check text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-orange-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-orange-600 mb-1">Administrateurs</p>
                        <p class="text-2xl sm:text-3xl font-bold text-orange-900">{{ $users->where('role', 'administrateur')->count() ?? 0 }}</p>
                        <p class="text-xs text-orange-500 mt-1">Stable</p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-user-shield text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-purple-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-purple-600 mb-1">Nouveaux ce mois</p>
                        <p class="text-2xl sm:text-3xl font-bold text-purple-900">{{ $users->where('created_at', '>=', now()->startOfMonth())->count() ?? 0 }}</p>
                        <p class="text-xs text-purple-500 mt-1">+ 15% vs mois dernier</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-user-plus text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section des filtres -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <div id="filtersPanel" class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6 mb-6 sm:mb-8" style="display: none;">
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-center sm:text-left">
                    <h3 class="text-xl sm:text-2xl font-bold text-blue-800 mb-1 sm:mb-2">Filtres de recherche</h3>
                    <p class="text-sm sm:text-base lg:text-lg text-blue-700">Affinez votre recherche pour trouver l'utilisateur parfait</p>
                </div>
                <button type="button" onclick="clearFilters()" class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                    <i class="fas fa-times mr-2"></i>Effacer tout
                </button>
            </div>
            
            <form method="GET" action="{{ route('administrateur.users.index') }}" class="space-y-4 sm:space-y-6">
                <!-- Première ligne de filtres -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <!-- Nom -->
                    <div>
                        <label for="name" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Nom</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="name" id="name" value="{{ request('name') }}" placeholder="Rechercher par nom..." class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                        </div>
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Email</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="email" name="email" id="email" value="{{ request('email') }}" placeholder="Rechercher par email..." class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base">
                        </div>
                    </div>
                    
                    <!-- Rôle -->
                    <div>
                        <label for="role" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Rôle</label>
                        <div class="relative">
                            <i class="fas fa-user-tag absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select name="role" id="role" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base appearance-none bg-white">
                                <option value="">Tous les rôles</option>
                                <option value="administrateur" {{ request('role') == 'administrateur' ? 'selected' : '' }}>Administrateur</option>
                                <option value="client" {{ request('role') == 'client' ? 'selected' : '' }}>Client</option>
                                <option value="prestataire" {{ request('role') == 'prestataire' ? 'selected' : '' }}>Prestataire</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                        </div>
                    </div>
                    
                    <!-- Statut -->
                    <div>
                        <label for="is_blocked" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Statut</label>
                        <div class="relative">
                            <i class="fas fa-toggle-on absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                            <select name="is_blocked" id="is_blocked" class="w-full pl-10 pr-4 py-2.5 sm:py-3 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm sm:text-base appearance-none bg-white">
                                <option value="">Tous</option>
                                <option value="0" {{ request('is_blocked') == '0' ? 'selected' : '' }}>Actif</option>
                                <option value="1" {{ request('is_blocked') == '1' ? 'selected' : '' }}>Bloqué</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 pt-4 border-t border-gray-200">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-search mr-2"></i>Appliquer les filtres
                    </button>
                    <a href="{{ route('administrateur.users.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-undo mr-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Section des résultats -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <div class="bg-white rounded-xl shadow-lg border border-blue-200 overflow-hidden">
            <!-- En-tête du tableau -->
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 sm:px-6 py-4 border-b border-blue-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="text-center sm:text-left">
                        <h3 class="text-xl sm:text-2xl font-bold text-blue-800 mb-1">Liste des utilisateurs</h3>
                        <p class="text-sm sm:text-base text-blue-700">{{ $users->total() ?? 0 }} utilisateur(s) trouvé(s)</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                        <select onchange="changePerPage(this.value)" class="px-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 par page</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 par page</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 par page</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 par page</option>
                        </select>

                    </div>
                </div>
            </div>
    
            <!-- Tableau des utilisateurs -->
            <!-- Mobile View -->
            <div class="block sm:hidden space-y-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <input type="checkbox" onchange="toggleAllCheckboxes(this)" class="mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="text-sm font-medium text-gray-700">Tout sélectionner</span>
                    </div>
                </div>
                
                @forelse($users ?? [] as $user)
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                        <div class="flex items-start space-x-3">
                            <input type="checkbox" name="selected_users[]" value="{{ $user->id }}" class="user-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                            
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold text-sm
                                @if($user->role === 'administrateur') bg-gradient-to-br from-blue-500 to-blue-700
                                @elseif($user->role === 'prestataire') bg-gradient-to-br from-green-500 to-green-700
                                @else bg-gradient-to-br from-cyan-500 to-cyan-700 @endif">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</h3>
                                        <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                                        
                                        <div class="flex flex-wrap gap-1 mt-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                @if($user->role === 'administrateur') bg-blue-100 text-blue-800
                                                @elseif($user->role === 'prestataire') bg-green-100 text-green-800
                                                @else bg-cyan-100 text-cyan-800 @endif">
                                                <i class="fas 
                                                    @if($user->role === 'administrateur') fa-user-shield
                                                    @elseif($user->role === 'prestataire') fa-user-tie
                                                    @else fa-user @endif mr-1"></i>
                                                {{ ucfirst($user->role) }}
                                            </span>
                                            
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                @if(!$user->is_blocked) bg-green-100 text-green-800
                                                @else bg-red-100 text-red-800 @endif">
                                                <i class="fas fa-circle text-xs mr-1"></i>
                                                {{ !$user->is_blocked ? 'Actif' : 'Bloqué' }}
                                            </span>
                                        </div>
                                        
                                        <div class="text-xs text-gray-500 mt-2">
                                            <div>Inscrit: {{ $user->created_at->format('d/m/Y') }}</div>
                                            <div>Dernière connexion: {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais' }}</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end gap-1 mt-3">
                                    <a href="{{ route('administrateur.users.show', $user->id) }}" class="text-blue-600 hover:text-blue-900 p-1.5 rounded-lg hover:bg-blue-50 transition-colors" title="Voir">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    
                                    @if(!$user->is_blocked)
                                        <button onclick="toggleBlockUser({{ $user->id }})" class="text-red-600 hover:text-red-900 p-1.5 rounded-lg hover:bg-red-50 transition-colors" title="Bloquer">
                                            <i class="fas fa-ban text-xs"></i>
                                        </button>
                                    @else
                                        <button onclick="toggleBlockUser({{ $user->id }})" class="text-green-600 hover:text-green-900 p-1.5 rounded-lg hover:bg-green-50 transition-colors" title="Débloquer">
                                            <i class="fas fa-check text-xs"></i>
                                        </button>
                                    @endif
                                    
                                    <button onclick="deleteUser({{ $user->id }})" class="text-red-600 hover:text-red-900 p-1.5 rounded-lg hover:bg-red-50 transition-colors" title="Supprimer">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <div class="text-gray-400">
                            <i class="fas fa-users text-5xl mb-4"></i>
                            <div class="text-lg font-medium text-gray-900 mb-2">Aucun utilisateur trouvé</div>
                            <div class="text-gray-500">Essayez de modifier vos critères de recherche</div>
                        </div>
                    </div>
                @endforelse
            </div>
            
            <!-- Desktop Table View -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <input type="checkbox" onchange="toggleAllCheckboxes(this)" class="mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    Utilisateur
                                </div>
                            </th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Dernière connexion</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Inscrit le</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users ?? [] as $user)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" name="selected_users[]" value="{{ $user->id }}" class="user-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm
                                            @if($user->role === 'administrateur') bg-gradient-to-br from-blue-500 to-blue-700
                                            @elseif($user->role === 'prestataire') bg-gradient-to-br from-green-500 to-green-700
                                            @else bg-gradient-to-br from-cyan-500 to-cyan-700 @endif">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500 truncate">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($user->role === 'administrateur') bg-blue-100 text-blue-800
                                        @elseif($user->role === 'prestataire') bg-green-100 text-green-800
                                        @else bg-cyan-100 text-cyan-800 @endif">
                                        <i class="fas 
                                            @if($user->role === 'administrateur') fa-user-shield
                                            @elseif($user->role === 'prestataire') fa-user-tie
                                            @else fa-user @endif mr-1"></i>
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if(!$user->is_blocked) bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        <i class="fas fa-circle text-xs mr-2"></i>
                                        {{ !$user->is_blocked ? 'Actif' : 'Bloqué' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">
                                    {{ $user->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('administrateur.users.show', $user->id) }}" class="text-blue-600 hover:text-blue-900 p-2 rounded-lg hover:bg-blue-50 transition-colors" title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if(!$user->is_blocked)
                                            <button onclick="toggleBlockUser({{ $user->id }})" class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-50 transition-colors" title="Bloquer">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @else
                                            <button onclick="toggleBlockUser({{ $user->id }})" class="text-green-600 hover:text-green-900 p-2 rounded-lg hover:bg-green-50 transition-colors" title="Débloquer">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        
                                        <button onclick="deleteUser({{ $user->id }})" class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-50 transition-colors" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="text-gray-400">
                                        <i class="fas fa-users text-5xl mb-4"></i>
                                        <div class="text-lg font-medium text-gray-900 mb-2">Aucun utilisateur trouvé</div>
                                        <div class="text-gray-500">Essayez de modifier vos critères de recherche</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
    
                @if($users && $users->hasPages())
                    <!-- Pagination -->
                    <div class="bg-gray-50 px-4 sm:px-6 py-3 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm text-gray-700 mb-3 sm:mb-0 text-center sm:text-left">
                            Affichage de <span class="font-medium">{{ $users->firstItem() }}</span> à <span class="font-medium">{{ $users->lastItem() }}</span> sur <span class="font-medium">{{ $users->total() }}</span> résultats
                        </div>
                        <div class="flex justify-center sm:justify-end">
                            {{ $users->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Actions groupées -->
    <div id="bulkActions" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-white rounded-xl shadow-2xl border border-gray-200 px-4 sm:px-6 py-3 sm:py-4 z-50" style="display: none;">
        <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4">
            <span class="text-sm font-medium text-gray-700">Actions groupées :</span>
            <div class="flex flex-wrap gap-2 justify-center">
                <button onclick="bulkDelete()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200 flex items-center text-sm">
                    <i class="fas fa-trash mr-2"></i>Supprimer
                </button>
                <button onclick="bulkBlock()" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200 flex items-center text-sm">
                    <i class="fas fa-ban mr-2"></i>Bloquer
                </button>
                <button onclick="bulkUnblock()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-lg transition duration-200 flex items-center text-sm">
                    <i class="fas fa-check mr-2"></i>Débloquer
                </button>
                <button onclick="clearSelection()" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-2 px-3 rounded-lg transition duration-200 flex items-center text-sm">
                    <i class="fas fa-times mr-2"></i>Annuler
                </button>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table th, .table td {
            vertical-align: middle;
            padding: 12px;
            white-space: nowrap;
        }
        
        .badge {
            font-size: 0.875rem;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .pagination {
            margin: 0;
        }
        
        .pagination .page-link {
            color: #6c757d;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr !important;
                gap: 1rem !important;
            }
            
            .filters-section {
                flex-direction: column !important;
                gap: 1rem !important;
            }
            
            .filters-section .d-flex {
                flex-direction: column !important;
                gap: 0.5rem !important;
            }
            
            .table th, .table td {
                padding: 8px 4px;
                font-size: 0.875rem;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn-group .btn {
                border-radius: 0.375rem !important;
                margin-bottom: 2px;
            }
            
            .bulk-actions {
                flex-direction: column !important;
                gap: 0.5rem !important;
            }
            
            .bulk-actions .btn {
                width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr !important;
            }
            
            .table-responsive {
                font-size: 0.8rem;
            }
            
            .card-header h5 {
                font-size: 1.1rem;
            }
        }
</style>
@endpush

@push('scripts')
<script>
// Toggle filters panel
function toggleFilters() {
    const panel = document.getElementById('filtersPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

// Clear filters
function clearFilters() {
    window.location.href = '{{ route("administrateur.users.index") }}';
}

// Change items per page
function changePerPage(value) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', value);
    window.location.href = url.toString();
}

// Toggle all checkboxes
function toggleAllCheckboxes(source) {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = source.checked;
    });
    updateBulkActions();
}

// Update bulk actions visibility
function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    
    if (checkedBoxes.length > 0) {
        bulkActions.style.display = 'block';
    } else {
        bulkActions.style.display = 'none';
    }
}

// Add event listeners to checkboxes
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
});

// Clear selection
function clearSelection() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updateBulkActions();
}

// User actions
function toggleBlockUser(userId) {
    const action = event.target.closest('button').title === 'Bloquer' ? 'bloquer' : 'débloquer';
    if (confirm(`Êtes-vous sûr de vouloir ${action} cet utilisateur ?`)) {
        fetch(`{{ url('/administrateur/users') }}/${userId}/toggle-block`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de l\'opération');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de l\'opération');
        });
    }
}

function deleteUser(userId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')) {
        fetch(`{{ url('/administrateur/users') }}/${userId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la suppression');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la suppression');
        });
    }
}

// Bulk actions
function bulkDelete() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    if (checkedBoxes.length === 0) return;
    
    if (confirm(`Êtes-vous sûr de vouloir supprimer ${checkedBoxes.length} utilisateur(s) ? Cette action est irréversible.`)) {
        const userIds = Array.from(checkedBoxes).map(cb => cb.value);
        
        fetch('{{ route("administrateur.users.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ user_ids: userIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la suppression groupée');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la suppression groupée');
        });
    }
}

function bulkBlock() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    if (checkedBoxes.length === 0) return;
    
    if (confirm(`Êtes-vous sûr de vouloir bloquer ${checkedBoxes.length} utilisateur(s) ?`)) {
        const userIds = Array.from(checkedBoxes).map(cb => cb.value);
        
        fetch('{{ route("administrateur.users.bulk-block") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ user_ids: userIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors du blocage groupé');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du blocage groupé');
        });
    }
}

function bulkUnblock() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    if (checkedBoxes.length === 0) return;
    
    if (confirm(`Êtes-vous sûr de vouloir débloquer ${checkedBoxes.length} utilisateur(s) ?`)) {
        const userIds = Array.from(checkedBoxes).map(cb => cb.value);
        
        fetch('{{ route("administrateur.users.bulk-unblock") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ user_ids: userIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors du déblocage groupé');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du déblocage groupé');
        });
    }
}


</script>
@endpush