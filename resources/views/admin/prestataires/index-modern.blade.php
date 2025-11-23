@extends('layouts.admin-modern')

@section('title', 'Gestion des Prestataires')

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
                    Gestion des Prestataires
                </h1>
                <p class="text-base sm:text-lg text-blue-700 max-w-2xl mx-auto">
                    Gérez et supervisez tous les prestataires de la plateforme TaPrestation.
                </p>
            </div>
            
            <!-- Actions Header -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                <div class="flex items-center gap-2">
                    <span class="text-xs sm:text-sm font-semibold text-blue-800">Total :</span>
                    <span class="px-2 sm:px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs sm:text-sm font-bold">
                        {{ $stats['total'] ?? 0 }} prestataire(s)
                    </span>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('administrateur.prestataires.pending') }}" class="bg-orange-100 hover:bg-orange-200 text-orange-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-clock mr-2"></i>En attente ({{ $stats['pending'] }})
                    </a>
                    <button onclick="toggleFilters()" class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base">
                    <i class="fas fa-filter mr-2"></i>Afficher les filtres
                </button>

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
                        <p class="text-xs sm:text-sm font-medium text-blue-600 mb-1">Total Prestataires</p>
                        <p class="text-2xl sm:text-3xl font-bold text-blue-900">{{ $stats['total'] }}</p>
                        <p class="text-xs text-blue-500 mt-1">
                            <i class="fas fa-chart-line mr-1"></i>Total inscrit
                        </p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-green-600 mb-1">Prestataires Approuvés</p>
                        <p class="text-2xl sm:text-3xl font-bold text-green-900">{{ $stats['approved'] }}</p>
                        <p class="text-xs text-green-500 mt-1">
                            <i class="fas fa-thumbs-up mr-1"></i>{{ $stats['total'] > 0 ? round(($stats['approved'] / $stats['total']) * 100, 1) : 0 }}% du total
                        </p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-user-check text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-orange-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-orange-600 mb-1">Prestataires en Attente</p>
                        <p class="text-2xl sm:text-3xl font-bold text-orange-900">{{ $stats['pending'] }}</p>
                        <p class="text-xs text-orange-500 mt-1">
                            <i class="fas fa-hourglass-half mr-1"></i>{{ $stats['total'] > 0 ? round(($stats['pending'] / $stats['total']) * 100, 1) : 0 }}% du total
                        </p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-clock text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-purple-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm font-medium text-purple-600 mb-1">Nouveaux ce mois</p>
                        <p class="text-2xl sm:text-3xl font-bold text-purple-900">{{ $stats['new_this_month'] }}</p>
                        <p class="text-xs text-purple-500 mt-1">
                            <i class="fas fa-calendar-alt mr-1"></i>{{ now()->format('F Y') }}
                        </p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-user-plus text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Filters Panel -->
<div id="filtersPanel" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
    <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Filtres de recherche</h3>
        <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="clearFilters()">
            <i class="fas fa-times mr-2"></i>
            Effacer
        </button>
    </div>
    <form action="{{ route('administrateur.prestataires.index') }}" method="GET" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                <input type="text" name="name" value="{{ request('name') }}" placeholder="Rechercher par nom..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" value="{{ request('email') }}" placeholder="Rechercher par email..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
                <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Toutes les catégories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Tous les statuts</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Bloqué</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Trier par</label>
                <select name="sort" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Date d'inscription</option>
                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nom</option>
                    <option value="email" {{ request('sort') == 'email' ? 'selected' : '' }}>Email</option>
                    <option value="services_count" {{ request('sort') == 'services_count' ? 'selected' : '' }}>Nombre de services</option>
                    <option value="orders_count" {{ request('sort') == 'orders_count' ? 'selected' : '' }}>Nombre de commandes</option>
                    <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Note moyenne</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ordre</label>
                <select name="direction" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>Décroissant</option>
                    <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Croissant</option>
                </select>
            </div>
        </div>
        
        <div class="flex items-center gap-4 mt-6 pt-6 border-t border-gray-200">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-search mr-2"></i>
                Rechercher
            </button>
            <a href="{{ route('administrateur.prestataires.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-redo mr-2"></i>
                Réinitialiser
            </a>
        </div>
    </form>
</div>

<!-- Items Per Page -->
<div class="flex justify-between items-center mb-6 flex-wrap gap-4">
    <div class="flex items-center gap-2">
        <label class="text-sm text-gray-600">Afficher</label>
        <select onchange="changeItemsPerPage(this.value)" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
        </select>
        <span class="text-sm text-gray-600">éléments</span>
    </div>
</div>

<!-- Main Content -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <!-- Cards Layout -->
    <div class="p-6">
        <!-- Select All Header -->
        <div class="flex items-center justify-between mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center">
                <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes()" class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="selectAll" class="font-semibold text-gray-900">Sélectionner tout</label>
            </div>
            <div class="text-gray-600">
                {{ $prestataires->count() }} prestataire(s) affiché(s)
            </div>
        </div>

        <div class="space-y-4">
        @forelse($prestataires as $prestataire)
            <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow duration-200 {{ $prestataire->user->created_at->isCurrentMonth() ? 'ring-2 ring-blue-100' : '' }}">
                <div class="flex items-center gap-4">
                    <!-- Checkbox -->
                    <div>
                        <input type="checkbox" value="{{ $prestataire->id }}" class="prestataire-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" onchange="updateBulkActionsVisibility()">
                    </div>
                    
                    <!-- Avatar & Basic Info -->
                    <div class="flex-shrink-0">
                        <div class="relative">
                            @if($prestataire->user->profile_photo_path)
                                <img src="{{ asset('storage/' . $prestataire->user->profile_photo_path) }}" alt="{{ $prestataire->user->name }}" class="w-16 h-16 rounded-xl object-cover">
                            @elseif($prestataire->photo)
                                <img src="{{ asset('storage/' . $prestataire->photo) }}" alt="{{ $prestataire->user->name }}" class="w-16 h-16 rounded-xl object-cover">
                            @else
                                <div class="w-16 h-16 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xl font-semibold">
                                    {{ substr($prestataire->user->name, 0, 1) }}
                                </div>
                            @endif
                            @if($prestataire->isVerified())
                                <div class="absolute -top-1 -right-1 w-5 h-5 bg-green-500 rounded-full flex items-center justify-center border-2 border-white">
                                    <i class="fas fa-check text-xs text-white"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Main Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h5 class="text-lg font-semibold text-gray-900 truncate">{{ $prestataire->user->name }}</h5>
                            @if($prestataire->isVerified())
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i> Vérifié
                                </span>
                            @endif
                        </div>
                        <div class="text-gray-600 text-sm mb-2 truncate">{{ $prestataire->user->email }}</div>
                        
                        <!-- Badges -->
                        <div class="flex gap-2 flex-wrap">
                            @if($prestataire->user->created_at->isCurrentMonth())
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-star mr-1"></i> Nouveau ce mois
                                </span>
                            @endif
                            
                            @if($prestataire->category)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">{{ $prestataire->category->name }}</span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Stats -->
                    <div class="hidden md:block">
                        <div class="flex gap-6">
                            <div class="text-center">
                                <div class="text-xl font-semibold text-gray-900">{{ $prestataire->services_count ?? $prestataire->services->count() }}</div>
                                <div class="text-xs text-gray-600">Services</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xl font-semibold text-gray-900">{{ $prestataire->orders_count ?? 0 }}</div>
                                <div class="text-xs text-gray-600">Commandes</div>
                            </div>
                            <div class="text-center">
                                <div class="flex items-center gap-1 justify-center">
                                    <div class="flex text-yellow-400">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= ($prestataire->rating ?? 0))
                                                <i class="fas fa-star text-xs"></i>
                                            @else
                                                <i class="far fa-star text-xs"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="font-semibold text-gray-900 text-sm">{{ number_format($prestataire->rating ?? 0, 1) }}</span>
                                </div>
                                <div class="text-xs text-gray-600">Note</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status & Actions -->
                    <div class="flex-shrink-0">
                        <div class="flex items-center gap-4">
                            <!-- Status -->
                            <div class="flex items-center gap-2">
                                @if($prestataire->user->blocked_at)
                                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                    <span class="text-red-600 font-semibold text-sm">Bloqué</span>
                                @else
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    <span class="text-green-600 font-semibold text-sm">Actif</span>
                                @endif
                            </div>
                            
                            <!-- Actions -->
                            <div class="relative">
                                <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="toggleDropdown('dropdown-{{ $prestataire->id }}')">
                                    <i class="fas fa-cog mr-2"></i> Actions
                                </button>
                                <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10 hidden" id="dropdown-{{ $prestataire->id }}">
                                    <div class="py-1">
                                        <a href="{{ route('administrateur.prestataires.show', $prestataire->id) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <i class="fas fa-eye mr-3"></i> Voir profil
                                        </a>

                                        @if(auth()->id() != $prestataire->user_id)
                                            @if($prestataire->user->blocked_at)
                                                <button onclick="toggleBlockPrestataire('{{ $prestataire->id }}', 'unblock')" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                    <i class="fas fa-unlock mr-3"></i> Débloquer
                                                </button>
                                            @else
                                                <button onclick="toggleBlockPrestataire('{{ $prestataire->id }}', 'block')" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                    <i class="fas fa-lock mr-3"></i> Désactiver
                                                </button>
                                            @endif
                                            <button onclick="deletePrestataire('{{ $prestataire->id }}')" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                <i class="fas fa-trash mr-3"></i> Supprimer
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile Stats -->
                <div class="block mt-4 md:hidden">
                    <div class="flex justify-around text-center">
                        <div>
                            <div class="text-lg font-semibold text-gray-900">{{ $prestataire->services_count ?? $prestataire->services->count() }}</div>
                            <div class="text-xs text-gray-600">Services</div>
                        </div>
                        <div>
                            <div class="text-lg font-semibold text-gray-900">{{ $prestataire->orders_count ?? 0 }}</div>
                            <div class="text-xs text-gray-600">Commandes</div>
                        </div>
                        <div>
                            <div class="flex items-center gap-1 justify-center">
                                <div class="flex text-yellow-400">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= ($prestataire->rating ?? 0))
                                            <i class="fas fa-star text-xs"></i>
                                        @else
                                            <i class="far fa-star text-xs"></i>
                                        @endif
                                    @endfor
                                </div>
                                <span class="font-semibold text-gray-900 text-sm">{{ number_format($prestataire->rating ?? 0, 1) }}</span>
                            </div>
                            <div class="text-xs text-gray-600">Note</div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <div class="text-gray-500">
                    <i class="fas fa-users text-6xl mb-4 opacity-50"></i>
                    <h4 class="text-lg font-semibold mb-2">Aucun prestataire trouvé</h4>
                    <p class="text-sm">Aucun prestataire ne correspond aux critères de recherche.</p>
                </div>
            </div>
        @endforelse
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="px-6 py-4 flex justify-between items-center border-t border-gray-200">
        <div class="text-sm text-gray-600">
            Affichage de {{ $prestataires->firstItem() ?? 0 }} à {{ $prestataires->lastItem() ?? 0 }} sur {{ $prestataires->total() }} entrées
        </div>
        {{ $prestataires->appends(request()->query())->links() }}
    </div>
</div>

<!-- Bulk Actions -->
<div id="bulkActions" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-white px-8 py-4 rounded-xl shadow-lg border border-gray-200 hidden z-50 max-w-[90vw]">
    <div class="flex gap-4 items-center flex-wrap justify-center">
        <span id="selectedCount" class="font-medium whitespace-nowrap text-gray-700">0 sélectionné(s)</span>
        <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="clearSelection()">
            <i class="fas fa-times mr-2"></i>
            <span class="hidden sm:inline">Annuler</span>
        </button>
        <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" onclick="bulkUnblock()">
            <i class="fas fa-unlock mr-2"></i>
            <span class="hidden sm:inline">Débloquer</span>
        </button>
        <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500" onclick="bulkBlock()">
            <i class="fas fa-lock mr-2"></i>
            <span class="hidden sm:inline">Bloquer</span>
        </button>
        <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="bulkDelete()">
            <i class="fas fa-trash mr-2"></i>
            <span class="hidden sm:inline">Supprimer</span>
        </button>
    </div>
</div>

<style>
    :root {
        --primary: #3b82f6;
        --primary-dark: #2563eb;
        --secondary: #6b7280;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #06b6d4;
        --light: #f8fafc;
        --dark: #1f2937;
        --border: #e5e7eb;
        --text-muted: #6b7280;
        --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --card-shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    .stat-card {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border: none;
        border-radius: 16px;
        color: white;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        padding: 1.5rem;
    }
    
    .stat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .stat-title {
        font-size: 0.875rem;
        opacity: 0.9;
        margin-bottom: 0.5rem;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .stats-trend {
        font-size: 0.75rem;
        opacity: 0.8;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 50%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--card-shadow-hover);
    }
    
    .stat-card:hover::before {
        opacity: 1;
    }
    
    .stat-card.success {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    }
    
    .stat-card.warning {
        background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
    }
    
    .stat-card.info {
        background: linear-gradient(135deg, var(--info) 0%, #0891b2 100%);
    }
    
    .stat-icon {
        width: 56px;
        height: 56px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        backdrop-filter: blur(10px);
    }
    
    .prestataire-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        border: 1px solid var(--border);
        position: relative;
        overflow: hidden;
    }
    
    .prestataire-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--primary);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .prestataire-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--card-shadow-hover);
    }
    
    .prestataire-card:hover::before {
        opacity: 1;
    }
    
    .prestataire-avatar {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        object-fit: cover;
        border: 3px solid var(--border);
        transition: all 0.3s ease;
    }
    
    .prestataire-card:hover .prestataire-avatar {
        border-color: var(--primary);
        transform: scale(1.05);
    }
    
    .rating-display {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 14px;
    }
    
    .rating-stars {
        color: #fbbf24;
        font-size: 16px;
    }
    
    .rating-value {
        font-weight: 600;
        color: var(--dark);
    }
    
    /* Styles responsifs supplémentaires */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr !important;
        }
        
        .table th, .table td {
            padding: 0.5rem !important;
            font-size: 0.875rem;
        }
        
        .avatar {
            width: 32px !important;
            height: 32px !important;
        }
        
        .avatar-initials {
            font-size: 0.75rem !important;
        }
        
        .btn {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .dropdown-menu {
            position: absolute;
            right: 0;
            left: auto;
            min-width: 150px;
        }
        
        .prestataire-avatar {
            width: 56px;
            height: 56px;
        }
        
        .stats-icon {
            width: 48px;
            height: 48px;
            font-size: 24px;
        }
    }
    
    @media (max-width: 576px) {
        .content {
            padding: 1rem !important;
        }
        
        .chart-card, .content-card {
            margin: 0 -0.5rem;
            border-radius: 0;
        }
        
        #bulkActions {
            bottom: 1rem !important;
            left: 1rem !important;
            right: 1rem !important;
            transform: none !important;
            max-width: none !important;
            width: auto !important;
        }
        
        .prestataire-card {
            padding: 16px;
        }
    }
    
    /* Amélioration des badges */
    .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border: 1px solid transparent;
    }
    
    .badge.success {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border-color: rgba(16, 185, 129, 0.2);
    }
    
    .badge.warning {
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning);
        border-color: rgba(245, 158, 11, 0.2);
    }
    
    .badge.info {
        background: rgba(6, 182, 212, 0.1);
        color: var(--info);
        border-color: rgba(6, 182, 212, 0.2);
    }
    
    .badge.primary {
        background: rgba(59, 130, 246, 0.1);
        color: var(--primary);
        border-color: rgba(59, 130, 246, 0.2);
    }
    
    .badge.secondary {
        background: rgba(107, 114, 128, 0.1);
        color: var(--secondary);
        border-color: rgba(107, 114, 128, 0.2);
    }
    
    .badge.danger {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border-color: rgba(239, 68, 68, 0.2);
    }
    
    /* Amélioration des boutons d'action */
    .actions-dropdown {
        position: relative;
    }
    
    .dropdown-menu {
        background: white;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
        padding: 0.5rem 0;
        min-width: 160px;
        z-index: 1000;
    }
    
    .dropdown-item {
        display: block;
        width: 100%;
        padding: 0.5rem 1rem;
        color: var(--dark);
        text-decoration: none;
        border: none;
        background: none;
        text-align: left;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    
    .dropdown-item:hover {
        background-color: var(--light);
    }
    
    .dropdown-item.text-danger {
        color: var(--danger);
    }
    
    .dropdown-item.text-danger:hover {
        background-color: rgba(239, 68, 68, 0.1);
    }
</style>

<script>
    // Toggle filters panel
    function toggleFilters() {
        const filtersPanel = document.getElementById('filtersPanel');
        filtersPanel.style.display = filtersPanel.style.display === 'none' ? 'block' : 'none';
    }
    
    // Clear filters
    function clearFilters() {
        window.location.href = '{{ route("administrateur.prestataires.index") }}';
    }
    
    // Change items per page
    function changeItemsPerPage(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        window.location.href = url.toString();
    }
    
    // Toggle dropdown menu
    function toggleDropdown(menuId) {
        const menu = document.getElementById(menuId);
        const allMenus = document.querySelectorAll('.dropdown-menu');
        
        // Close all other menus
        allMenus.forEach(item => {
            if (item.id !== menuId) {
                item.style.display = 'none';
            }
        });
        
        // Toggle current menu
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }
    
    // Toggle all checkboxes
    function toggleAllCheckboxes() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.prestataire-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        
        updateBulkActionsVisibility();
    }
    
    // Update bulk actions visibility
    function updateBulkActionsVisibility() {
        const checkboxes = document.querySelectorAll('.prestataire-checkbox:checked');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');
        
        if (checkboxes.length > 0) {
            bulkActions.style.display = 'block';
            selectedCount.textContent = `${checkboxes.length} sélectionné(s)`;
        } else {
            bulkActions.style.display = 'none';
        }
    }
    
    // Clear selection
    function clearSelection() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.prestataire-checkbox');
        
        selectAll.checked = false;
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        updateBulkActionsVisibility();
    }
    
    // Toggle block prestataire
    function toggleBlockPrestataire(prestatairesId, action) {
        const message = action === 'block' ? 'Êtes-vous sûr de vouloir bloquer ce prestataire ?' : 'Êtes-vous sûr de vouloir débloquer ce prestataire ?';
        
        if (confirm(message)) {
            fetch(`{{ url('/administrateur/prestataires') }}/${prestatairesId}/toggle-block`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ action: action })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Erreur lors de l\'opération: ' + (data.message || 'Erreur inconnue'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'opération');
            });
        }
    }
    
    // Delete prestataire
    function deletePrestataire(prestatairesId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce prestataire ? Cette action est irréversible.')) {
            fetch(`{{ url('/administrateur/prestataires') }}/${prestatairesId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Erreur lors de la suppression: ' + (data.message || 'Erreur inconnue'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la suppression');
            });
        }
    }
    
    // Bulk unblock
    function bulkUnblock() {
        const selectedIds = getSelectedIds();
        
        if (selectedIds.length === 0) {
            alert('Veuillez sélectionner au moins un prestataire.');
            return;
        }
        
        if (confirm(`Êtes-vous sûr de vouloir débloquer ${selectedIds.length} prestataire(s) ?`)) {
            fetch(`{{ url('/administrateur/prestataires/bulk-unblock') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ ids: selectedIds })
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    alert('Déblocage réussi!');
                    window.location.reload();
                } else {
                    alert('Erreur lors du déblocage: ' + (data.message || 'Erreur inconnue'));
                }
            })
            .catch(error => {
                console.error('Erreur complète:', error);
                alert('Erreur lors du déblocage: ' + error.message);
            });
        }
    }
    
    // Bulk block
    function bulkBlock() {
        const selectedIds = getSelectedIds();
        
        if (selectedIds.length === 0) {
            alert('Veuillez sélectionner au moins un prestataire.');
            return;
        }
        
        if (confirm(`Êtes-vous sûr de vouloir bloquer ${selectedIds.length} prestataire(s) ?`)) {
            fetch(`{{ url('/administrateur/prestataires/bulk-block') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ ids: selectedIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Erreur lors du blocage: ' + (data.message || 'Erreur inconnue'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors du blocage');
            });
        }
    }
    
    // Bulk delete
    function bulkDelete() {
        const selectedIds = getSelectedIds();
        
        if (selectedIds.length === 0) {
            alert('Veuillez sélectionner au moins un prestataire.');
            return;
        }
        
        if (confirm(`Êtes-vous sûr de vouloir supprimer ${selectedIds.length} prestataire(s) ? Cette action est irréversible.`)) {
            fetch(`{{ url('/administrateur/prestataires/bulk-delete') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ ids: selectedIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Erreur lors de la suppression: ' + (data.message || 'Erreur inconnue'));
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la suppression');
            });
        }
    }
    
    // Get selected IDs
    function getSelectedIds() {
        const checkboxes = document.querySelectorAll('.prestataire-checkbox:checked');
        return Array.from(checkboxes).map(checkbox => checkbox.value);
    }
    

    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.actions-dropdown')) {
            const dropdowns = document.querySelectorAll('.dropdown-menu');
            dropdowns.forEach(dropdown => {
                dropdown.style.display = 'none';
            });
        }
    });
</script>
@endsection