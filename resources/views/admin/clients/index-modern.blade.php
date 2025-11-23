@extends('layouts.admin-modern')

@section('page-title', 'Gestion des Clients')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="bg-blue-50 min-h-screen">
    <!-- Bannière d'en-tête -->
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-blue-900 mb-2 leading-tight">
                    Gestion des Clients
                </h1>
                <p class="text-base sm:text-lg text-blue-700 max-w-2xl mx-auto">
                    Gérez tous les clients de la plateforme avec des outils avancés
                </p>
            </div>
            <div class="flex justify-center">
                <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base" onclick="toggleFilters()">
                    <i class="fas fa-filter mr-2"></i>
                    <span>Afficher les filtres</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-6 sm:mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-blue-700 mb-1">Total Clients</div>
                        <div class="text-2xl font-bold text-blue-900">{{ $clients->total() ?? 0 }}</div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-blue-700 mb-1">Clients Actifs</div>
                        <div class="text-2xl font-bold text-blue-900">{{ $clients->where('blocked_at', null)->count() ?? 0 }}</div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-user-check text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-blue-700 mb-1">Demandes Totales</div>
                        <div class="text-2xl font-bold text-blue-900">{{ $totalRequests ?? 0 }}</div>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <i class="fas fa-file-alt text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-blue-700 mb-1">Nouveaux ce mois</div>
                        <div class="text-2xl font-bold text-blue-900">{{ $clients->where('created_at', '>=', now()->startOfMonth())->count() ?? 0 }}</div>
                    </div>
                    <div class="bg-indigo-100 p-3 rounded-lg">
                        <i class="fas fa-user-plus text-indigo-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    <div id="filtersPanel" class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8" style="display: none;">
        <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6 mb-6 sm:mb-8">
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-center sm:text-left">
                    <h3 class="text-xl sm:text-2xl font-bold text-blue-800 mb-1 sm:mb-2">Filtres de recherche</h3>
                    <p class="text-sm sm:text-base lg:text-lg text-blue-700">Affinez votre recherche pour trouver les clients</p>
                </div>
                <button class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base" onclick="clearFilters()">
                    <i class="fas fa-times mr-2"></i>
                    Effacer
                </button>
            </div>
            <form action="{{ route('administrateur.clients.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <div>
                    <label class="block text-sm font-medium text-blue-800 mb-2">Nom</label>
                    <input type="text" name="name" value="{{ request('name') }}" placeholder="Rechercher par nom..." class="w-full px-3 py-2.5 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-blue-800 mb-2">Email</label>
                    <input type="email" name="email" value="{{ request('email') }}" placeholder="Rechercher par email..." class="w-full px-3 py-2.5 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-blue-800 mb-2">Statut</label>
                    <select name="status" class="w-full px-3 py-2.5 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">Tous les statuts</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Bloqué</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-blue-800 mb-2">Trier par</label>
                    <select name="sort" class="w-full px-3 py-2.5 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Date d'inscription</option>
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nom</option>
                        <option value="email" {{ request('sort') == 'email' ? 'selected' : '' }}>Email</option>
                        <option value="requests_count" {{ request('sort') == 'requests_count' ? 'selected' : '' }}>Nombre de demandes</option>
                        <option value="reviews_count" {{ request('sort') == 'reviews_count' ? 'selected' : '' }}>Nombre d'avis</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-blue-800 mb-2">Ordre</label>
                    <select name="direction" class="w-full px-3 py-2.5 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>Décroissant</option>
                        <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Croissant</option>
                    </select>
                </div>
                
                <div class="col-span-1 sm:col-span-2 lg:col-span-3 flex flex-col sm:flex-row gap-3 pt-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm">
                        <i class="fas fa-search mr-2"></i>
                        Rechercher
                    </button>
                    <a href="{{ route('administrateur.clients.index') }}" class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm">
                        <i class="fas fa-redo mr-2"></i>
                        Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Items Per Page -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div class="flex items-center gap-2">
                <label class="text-sm text-blue-700">Afficher</label>
                <select onchange="changeItemsPerPage(this.value)" class="px-3 py-2 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="text-sm text-blue-700">éléments</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
        <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <div class="text-xl font-bold text-blue-800">Liste des clients ({{ $clients->total() ?? 0 }})</div>
                <div class="flex gap-4 items-center">
                    <select onchange="changePerPage(this.value)" class="px-3 py-2 border border-blue-200 rounded-lg text-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 par page</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 par page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 par page</option>
                    </select>
                    

                </div>
            </div>
            
            <div class="space-y-4">
                @forelse($clients as $client)
                    <div class="bg-white border border-blue-200 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 p-6">
                        <div class="flex items-center justify-between">
                            <!-- Zone gauche: Checkbox + Infos client -->
                            <div class="flex items-center space-x-4 flex-1">
                                <input type="checkbox" class="client-checkbox w-4 h-4 text-blue-600 border-blue-300 rounded focus:ring-blue-500" value="{{ $client->id }}" onchange="updateBulkActionsVisibility()">
                                
                                <!-- Avatar du client -->
                                <div class="flex-shrink-0">
                                    <div class="w-16 h-16 rounded-full overflow-hidden bg-blue-100 flex items-center justify-center border border-blue-200">
                                        @if($client->user->profile_photo_url)
                                            <img src="{{ $client->user->profile_photo_url }}" alt="{{ $client->user->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-blue-600 font-bold text-xl">
                                                {{ substr($client->user->name, 0, 1) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Infos principales -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-bold text-blue-900 truncate">{{ $client->user->name }}</h3>
                                            <div class="text-sm text-blue-600 mb-2">{{ $client->user->email }}</div>
                                            <div class="flex items-center gap-2 mt-1">
                                                @if($client->user->is_blocked)
                                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium flex items-center">
                                                        <i class="fas fa-ban mr-1"></i>
                                                        Bloqué
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium flex items-center">
                                                        <i class="fas fa-check-circle mr-1"></i>
                                                        Actif
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Métriques -->
                                    <div class="flex items-center gap-4 text-xs text-gray-500">
                                        <div class="flex items-center gap-1">
                                            <i class="fas fa-calendar-check text-blue-500"></i>
                                            <span>{{ $client->bookings->count() }} demandes</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <i class="fas fa-star text-blue-500"></i>
                                            <span>{{ $client->reviews_count ?? $client->reviews->count() }} avis</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <i class="fas fa-calendar text-blue-500"></i>
                                            <span>Inscrit le {{ $client->created_at->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Zone droite: Actions rapides -->
                            <div class="flex flex-col gap-2 pl-6">
                                <div class="flex gap-2">
                                    <a href="{{ route('administrateur.clients.show', $client->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs font-medium transition duration-200 flex items-center" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if(auth()->id() != $client->user_id)
                                        @if($client->user->is_blocked)
                                            <button onclick="toggleBlockClient('{{ $client->id }}', 'unblock')" class="bg-green-100 hover:bg-green-200 text-green-800 px-3 py-2 rounded-lg text-xs font-medium transition duration-200 flex items-center" title="Débloquer">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                        @else
                                            <button onclick="toggleBlockClient('{{ $client->id }}', 'block')" class="bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-3 py-2 rounded-lg text-xs font-medium transition duration-200 flex items-center" title="Bloquer">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        @endif
                                        
                                        <button onclick="deleteClient('{{ $client->id }}')" class="bg-red-100 hover:bg-red-200 text-red-800 px-3 py-2 rounded-lg text-xs font-medium transition duration-200 flex items-center" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <i class="fas fa-users text-6xl text-blue-200 mb-4"></i>
                        <div class="text-xl font-semibold text-blue-800 mb-2">Aucun client trouvé</div>
                        <div class="text-blue-600">Essayez de modifier vos critères de recherche</div>
                    </div>
                @endforelse
            </div>
            
            @if($clients && $clients->hasPages())
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 mt-6 border-t-2 border-blue-200">
                    <div class="text-sm text-blue-700 font-medium">
                        Affichage de {{ $clients->firstItem() }} à {{ $clients->lastItem() }} sur {{ $clients->total() }} résultats
                    </div>
                    <div class="flex justify-center">
                        {{ $clients->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Bulk Actions -->
    <div id="bulkActions" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-white rounded-xl shadow-lg border border-blue-200 px-6 py-4 z-50" style="display: none;">
        <div class="flex flex-wrap gap-3 items-center">
            <span id="selectedCount" class="font-medium text-blue-900">0 sélectionné(s)</span>
            <button class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm" onclick="clearSelection()">
                <i class="fas fa-times mr-2"></i>
                Annuler
            </button>
            <button class="bg-green-100 hover:bg-green-200 text-green-800 font-bold py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm" onclick="bulkUnblock()">
                <i class="fas fa-unlock mr-2"></i>
                Débloquer
            </button>
            <button class="bg-yellow-100 hover:bg-yellow-200 text-yellow-800 font-bold py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm" onclick="bulkBlock()">
                <i class="fas fa-lock mr-2"></i>
                Bloquer
            </button>
            <button class="bg-red-100 hover:bg-red-200 text-red-800 font-bold py-2 px-4 rounded-lg transition duration-200 flex items-center text-sm" onclick="bulkDelete()">
                <i class="fas fa-trash mr-2"></i>
                Supprimer
            </button>
        </div>
    </div>
</div>

<script>
    // Toggle filters panel
    function toggleFilters() {
        const filtersPanel = document.getElementById('filtersPanel');
        filtersPanel.style.display = filtersPanel.style.display === 'none' ? 'block' : 'none';
    }
    
    // Clear filters
    function clearFilters() {
        window.location.href = '{{ route("administrateur.clients.index") }}';
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
        const checkboxes = document.querySelectorAll('.client-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        
        updateBulkActionsVisibility();
    }
    
    // Update bulk actions visibility
    function updateBulkActionsVisibility() {
        const checkboxes = document.querySelectorAll('.client-checkbox:checked');
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
        const checkboxes = document.querySelectorAll('.client-checkbox');
        
        selectAll.checked = false;
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        updateBulkActionsVisibility();
    }
    
    // Toggle block client
    function toggleBlockClient(clientId, action) {
        const message = action === 'block' ? 'Êtes-vous sûr de vouloir bloquer ce client ?' : 'Êtes-vous sûr de vouloir débloquer ce client ?';
        
        if (confirm(message)) {
            fetch(`{{ url('/administrateur/clients') }}/${clientId}/toggle-block`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Une erreur est survenue', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Une erreur est survenue', 'error');
            });
        }
    }
    
    // Delete client
    function deleteClient(clientId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce client ? Cette action est irréversible.')) {
            fetch(`{{ url('/administrateur/clients') }}/${clientId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Une erreur est survenue', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Une erreur est survenue', 'error');
            });
        }
    }
    
    // Bulk unblock
    function bulkUnblock() {
        const selectedIds = getSelectedIds();
        
        if (confirm(`Êtes-vous sûr de vouloir débloquer ${selectedIds.length} client(s) ?`)) {
            fetch('{{ route("administrateur.clients.bulk-unblock") }}', {
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
                    showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Une erreur est survenue', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Une erreur est survenue', 'error');
            });
        }
    }
    
    // Bulk block
    function bulkBlock() {
        const selectedIds = getSelectedIds();
        
        if (confirm(`Êtes-vous sûr de vouloir bloquer ${selectedIds.length} client(s) ?`)) {
            fetch('{{ route("administrateur.clients.bulk-block") }}', {
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
                    showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Une erreur est survenue', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Une erreur est survenue', 'error');
            });
        }
    }
    
    // Bulk delete
    function bulkDelete() {
        const selectedIds = getSelectedIds();
        
        if (confirm(`Êtes-vous sûr de vouloir supprimer ${selectedIds.length} client(s) ? Cette action est irréversible.`)) {
            fetch('{{ route("administrateur.clients.bulk-delete") }}', {
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
                    showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Une erreur est survenue', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Une erreur est survenue', 'error');
            });
        }
    }
    
    // Get selected IDs
    function getSelectedIds() {
        const checkboxes = document.querySelectorAll('.client-checkbox:checked');
        return Array.from(checkboxes).map(checkbox => checkbox.value);
    }
    


    // Show notification function
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => notification.classList.add('show'), 100);
        
        // Remove notification after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => document.body.removeChild(notification), 300);
        }, 3000);
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

<style>
/* Page Header Styles */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-header-content {
    flex: 1;
    min-width: 200px;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--dark);
    margin: 0;
}

.page-subtitle {
    color: var(--secondary);
    margin: 0.5rem 0 0 0;
    font-size: 0.9rem;
}

.page-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Button responsive text */
.btn-text {
    margin-left: 0.5rem;
}

/* Notification Styles */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    padding: 1rem;
    z-index: 1000;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    max-width: 400px;
    border-left: 4px solid;
}

.notification.show {
    transform: translateX(0);
}

.notification-success {
    border-left-color: var(--success);
}

.notification-error {
    border-left-color: var(--danger);
}

.notification-info {
    border-left-color: var(--primary);
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.notification-content i {
    font-size: 1.1rem;
}

.notification-success .notification-content i {
    color: var(--success);
}

.notification-error .notification-content i {
    color: var(--danger);
}

.notification-info .notification-content i {
    color: var(--primary);
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .page-actions {
        width: 100%;
        justify-content: flex-start;
    }
    
    .btn-text {
        display: none;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 1rem !important;
    }
    
    .filter-panel {
        padding: 1rem !important;
    }
    
    .filter-grid {
        grid-template-columns: 1fr !important;
    }
    
    .table-container {
        overflow-x: auto;
    }
    
    .table th,
    .table td {
        white-space: nowrap;
        min-width: 120px;
    }
    
    .table th:first-child,
    .table td:first-child {
        position: sticky;
        left: 0;
        background: white;
        z-index: 1;
    }
    
    .bulk-actions {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .bulk-actions .btn {
        width: 100%;
        justify-content: center;
    }
    
    .notification {
        right: 10px;
        left: 10px;
        max-width: none;
        transform: translateY(-100%);
    }
    
    .notification.show {
        transform: translateY(0);
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr !important;
    }
    
    .page-title {
        font-size: 1.25rem;
    }
    
    .table th,
    .table td {
        padding: 0.5rem;
        font-size: 0.875rem;
    }
}

/* Enhanced table responsiveness */
@media (max-width: 992px) {
    .table-responsive {
        border: none;
    }
    
    .table {
        font-size: 0.9rem;
    }
    
    .dropdown-menu {
        position: fixed !important;
        transform: none !important;
        left: auto !important;
        right: 10px !important;
    }
}
</style>
@endsection