@extends('layouts.admin-modern')

@section('title', 'Gestion des Notifications - Administration')

@section('content')
<div class="bg-blue-50">
    <!-- Bannière d'en-tête -->
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-blue-900 mb-2 leading-tight">
                    Gestion des Notifications
                </h1>
                <p class="text-base sm:text-lg text-blue-700 max-w-2xl mx-auto">
                    Gérez toutes les notifications du système et communiquez avec vos utilisateurs.
                </p>
            </div>
            
            <!-- Actions Header -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                <div class="flex flex-wrap gap-2 sm:gap-3">
                    <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base" data-toggle="modal" data-target="#sendNotificationModal">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Envoyer Notification
                    </button>
                    <button class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 flex items-center justify-center text-sm sm:text-base" onclick="toggleFilters()">
                    <i class="fas fa-filter mr-2"></i>
                    Afficher les filtres
                </button>
                    <a href="{{ route('administrateur.notifications.analytics') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Analyses
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
                        <div class="text-xs sm:text-sm font-medium text-blue-600 uppercase tracking-wide">Total Notifications</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ number_format($stats['total']) }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+5% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-bell text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-orange-600 uppercase tracking-wide">Non Lues</div>
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
                        <div class="text-xs sm:text-sm font-medium text-blue-600 uppercase tracking-wide">Taux de Lecture</div>
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
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-blue-900 flex items-center">
                <i class="fas fa-bolt text-blue-600 mr-2"></i>
                Actions Rapides
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <form method="POST" action="{{ route('administrateur.notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-check-double mr-2"></i>
                        Marquer Toutes comme Lues
                    </button>
                </form>
                <form method="POST" action="{{ route('administrateur.notifications.cleanup') }}" onsubmit="return confirm('Supprimer toutes les notifications de plus de 30 jours ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-broom mr-2"></i>
                        Nettoyer Anciennes
                    </button>
                </form>
                <button type="button" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center" data-toggle="modal" data-target="#bulkDeleteModal">
                    <i class="fas fa-trash-alt mr-2"></i>
                    Suppression en Masse
                </button>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6" id="filtersPanel" style="display: none;">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-blue-900 flex items-center">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                Filtres de Recherche
            </h3>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('administrateur.notifications.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select name="type" id="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Tous les types</option>
                            <option value="booking" {{ request('type') == 'booking' ? 'selected' : '' }}>Réservation</option>
                            <option value="message" {{ request('type') == 'message' ? 'selected' : '' }}>Message</option>
                            <option value="review" {{ request('type') == 'review' ? 'selected' : '' }}>Avis</option>
                            <option value="system" {{ request('type') == 'system' ? 'selected' : '' }}>Système</option>
                            <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>Paiement</option>
                        </select>
                    </div>
                    <div>
                        <label for="read_status" class="block text-sm font-medium text-gray-700 mb-2">Statut de Lecture</label>
                        <select name="read_status" id="read_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Tous</option>
                            <option value="read" {{ request('read_status') == 'read' ? 'selected' : '' }}>Lues</option>
                            <option value="unread" {{ request('read_status') == 'unread' ? 'selected' : '' }}>Non lues</option>
                        </select>
                    </div>
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date Début</label>
                        <input type="date" name="date_from" id="date_from" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ request('date_from') }}">
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Date Fin</label>
                        <input type="date" name="date_to" id="date_to" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ request('date_to') }}">
                    </div>
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Recherche</label>
                        <input type="text" name="search" id="search" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ request('search') }}" placeholder="Contenu, utilisateur...">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-search mr-2"></i>
                            Rechercher
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Notifications Cards -->
    <div class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i class="fas fa-bell text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Notifications</h3>
                        <p class="text-blue-100 text-sm">{{ $notifications->total() }} notification(s) au total</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <select class="bg-white/10 border border-white/20 text-white text-sm rounded-lg px-3 py-2 focus:ring-2 focus:ring-white/50" onchange="changePerPage(this.value)">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 par page</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 par page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 par page</option>
                    </select>

                </div>
            </div>
        </div>
        
        @if($notifications->count() > 0)
            <!-- Bulk Actions -->
            <div class="bg-blue-50 px-6 py-3 border-b border-blue-100">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="selectAll" class="rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                        <label for="selectAll" class="text-sm font-medium text-blue-900">Tout sélectionner</label>
                        <span id="selectedCount" class="text-sm text-blue-600">0 sélectionnée(s)</span>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="bg-green-100 hover:bg-green-200 text-green-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200" onclick="markSelectedAsRead()">
                            <i class="fas fa-check mr-1"></i>Marquer comme lues
                        </button>
                        <button type="button" class="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200" onclick="deleteSelected()">
                            <i class="fas fa-trash mr-1"></i>Supprimer
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Cards Container -->
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach($notifications as $notification)
                        @php
                            $typeColors = [
                                'booking' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'text-blue-600'],
                                'offer' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'text-green-600'],
                                'message' => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-800', 'icon' => 'text-cyan-600'],
                                'review' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'text-yellow-600'],
                                'system' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'text-gray-600'],
                                'payment' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'text-red-600']
                            ];
                            $typeIcons = [
                                'booking' => 'calendar-check',
                                'offer' => 'handshake',
                                'message' => 'envelope',
                                'review' => 'star',
                                'system' => 'cog',
                                'payment' => 'credit-card'
                            ];
                            $data = $notification->data;
                            $type = $data['type'] ?? 'system';
                            $colors = $typeColors[$type] ?? $typeColors['system'];
                        @endphp
                        
                        <div class="{{ $notification->read_at ? 'bg-white' : 'bg-yellow-50' }} border {{ $notification->read_at ? 'border-blue-100' : 'border-yellow-200' }} rounded-xl p-6 hover:shadow-lg transition-all duration-300 hover:border-blue-300">
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="notification_ids[]" value="{{ $notification->id }}" class="notification-checkbox rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                                    <div class="{{ $colors['bg'] }} p-2 rounded-lg">
                                        <i class="fas fa-{{ $typeIcons[$type] ?? 'bell' }} {{ $colors['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <span class="{{ $colors['bg'] }} {{ $colors['text'] }} px-3 py-1 rounded-full text-sm font-medium">
                                            {{ ucfirst($type) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($notification->read_at)
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                            <i class="fas fa-check mr-1"></i>Lue
                                        </span>
                                    @else
                                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                                            <i class="fas fa-exclamation mr-1"></i>Non lue
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Content -->
                            <div class="mb-4">
                                <h4 class="font-bold text-blue-900 text-lg mb-2">{{ $data['title'] ?? 'Notification' }}</h4>
                                <p class="text-gray-700 mb-3">{{ $data['message'] ?? 'Aucun contenu' }}</p>
                            </div>
                            
                            <!-- User Info -->
                            <div class="flex items-center gap-3 mb-4 p-3 bg-gray-50 rounded-lg">
                                @if($notification->notifiable && $notification->notifiable->profile_photo)
                                    <img src="{{ asset('storage/' . $notification->notifiable->profile_photo) }}" alt="Photo" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-blue-600"></i>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-900">{{ $notification->notifiable->name ?? 'Utilisateur supprimé' }}</p>
                                    <p class="text-gray-600 text-sm">{{ $notification->notifiable->email ?? 'N/A' }}</p>
                                </div>
                            </div>
                            
                            <!-- Date and Actions -->
                            <div class="flex justify-between items-center pt-4 border-t border-blue-100">
                                <div class="text-sm text-gray-600">
                                    <div class="font-medium">{{ $notification->created_at->format('d/m/Y H:i') }}</div>
                                    <div class="text-xs">{{ $notification->created_at->diffForHumans() }}</div>
                                    @if($notification->read_at)
                                        <div class="text-xs text-green-600">Lue le {{ $notification->read_at->format('d/m/Y H:i') }}</div>
                                    @endif
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('administrateur.notifications.show', $notification->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                                        <i class="fas fa-eye mr-1"></i>Voir
                                    </a>
                                    @if(!$notification->read_at)
                                        <form method="POST" action="{{ route('administrateur.notifications.mark-read', $notification->id) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="bg-green-100 hover:bg-green-200 text-green-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                                                <i class="fas fa-check mr-1"></i>Marquer lue
                                            </button>
                                        </form>
                                    @endif
                                    <button type="button" class="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200" onclick="confirmDelete('{{ $notification->id }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            @if($notifications && $notifications->hasPages())
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 mt-6 border-t-2 border-blue-200 px-6 pb-6">
                    <div class="text-sm text-blue-700 font-medium">
                        Affichage de {{ $notifications->firstItem() }} à {{ $notifications->lastItem() }} sur {{ $notifications->total() }} résultats
                    </div>
                    <div class="flex justify-center">
                        {{ $notifications->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-12 px-6">
                <i class="fas fa-bell-slash text-6xl text-blue-200 mb-4"></i>
                <div class="text-xl font-semibold text-blue-800 mb-2">Aucune notification trouvée</div>
                <div class="text-blue-600">Aucune notification ne correspond aux critères de recherche</div>
            </div>
        @endif
    </div>
</div>

<!-- Modal d'envoi de notification -->
<div class="modal fade" id="sendNotificationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Envoyer une Notification</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('administrateur.notifications.send') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="recipient_type">Type de Destinataire</label>
                        <select name="recipient_type" id="recipient_type" class="form-control" required>
                            <option value="all">Tous les utilisateurs</option>
                            <option value="clients">Tous les clients</option>
                            <option value="prestataires">Tous les prestataires</option>
                            <option value="specific">Utilisateurs spécifiques</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="specific_users_group" style="display: none;">
                        <label for="user_ids">Utilisateurs Spécifiques</label>
                        <select name="user_ids[]" id="user_ids" class="form-control" multiple>
                            @foreach(\App\Models\User::select('id', 'name', 'email')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Maintenez Ctrl/Cmd pour sélectionner plusieurs utilisateurs</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="notification_type">Type de Notification</label>
                        <select name="type" id="notification_type" class="form-control" required>
                            <option value="system">Système</option>
                            <option value="announcement">Annonce</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="promotion">Promotion</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="title">Titre</label>
                        <input type="text" name="title" id="title" class="form-control" required maxlength="255">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea name="message" id="message" class="form-control" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="action_url">URL d'Action (optionnel)</label>
                        <input type="url" name="action_url" id="action_url" class="form-control" placeholder="https://...">
                        <small class="form-text text-muted">URL vers laquelle rediriger l'utilisateur en cliquant sur la notification</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Envoyer</button>
                </div>
            </form>
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
                <p>Êtes-vous sûr de vouloir supprimer cette notification ? Cette action est irréversible.</p>
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
    $('#selectAll').change(function() {
        $('.notification-checkbox').prop('checked', this.checked);
        updateSelectedCount();
    });
    
    // Update count when individual checkbox changes
    $('.notification-checkbox').change(function() {
        updateSelectedCount();
    });
    
    // Show/hide specific users field
    $('#recipient_type').change(function() {
        if ($(this).val() === 'specific') {
            $('#specific_users_group').show();
            $('#user_ids').prop('required', true);
        } else {
            $('#specific_users_group').hide();
            $('#user_ids').prop('required', false);
        }
    });
    
    // Auto-submit form on filter change
    $('#type, #read_status').change(function() {
        $(this).closest('form').submit();
    });
});

function updateSelectedCount() {
    const count = $('.notification-checkbox:checked').length;
    $('#selectedCount').text(count);
    
    // Update select all checkbox state
    const total = $('.notification-checkbox').length;
    $('#selectAll').prop('indeterminate', count > 0 && count < total);
    $('#selectAll').prop('checked', count === total && total > 0);
}

function confirmDelete(notificationId) {
    const form = document.getElementById('deleteForm');
    form.action = `/administrateur/notifications/${notificationId}`;
    $('#deleteModal').modal('show');
}

function markSelectedAsRead() {
    const selected = $('.notification-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selected.length === 0) {
        alert('Veuillez sélectionner au moins une notification.');
        return;
    }
    
    if (confirm(`Marquer ${selected.length} notification(s) comme lue(s) ?`)) {
        // Create form and submit
        const form = $('<form>', {
            method: 'POST',
            action: '{{ route("administrateur.notifications.mark-selected-read") }}'
        });
        
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: '{{ csrf_token() }}'
        }));
        
        selected.forEach(id => {
            form.append($('<input>', {
                type: 'hidden',
                name: 'notification_ids[]',
                value: id
            }));
        });
        
        $('body').append(form);
        form.submit();
    }
}

function deleteSelected() {
    const selected = $('.notification-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selected.length === 0) {
        alert('Veuillez sélectionner au moins une notification.');
        return;
    }
    
    if (confirm(`Supprimer définitivement ${selected.length} notification(s) ?`)) {
        // Create form and submit
        const form = $('<form>', {
            method: 'POST',
            action: '{{ route("administrateur.notifications.bulk-delete") }}'
        });
        
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: '{{ csrf_token() }}'
        }));
        
        form.append($('<input>', {
            type: 'hidden',
            name: '_method',
            value: 'DELETE'
        }));
        
        selected.forEach(id => {
            form.append($('<input>', {
                type: 'hidden',
                name: 'notification_ids[]',
                value: id
            }));
        });
        
        $('body').append(form);
        form.submit();
    }
}

// Toggle filters panel
function toggleFilters() {
    const panel = document.getElementById('filtersPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}
</script>
@endpush