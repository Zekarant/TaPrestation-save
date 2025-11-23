@extends('layouts.admin-modern')

@section('title', 'Tous les signalements')

@section('content')
<div class="bg-blue-50">
    <!-- Bannière d'en-tête -->
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-blue-900 mb-2 leading-tight">
                    Gestion des Signalements
                </h1>
                <p class="text-base sm:text-lg text-blue-700 max-w-2xl mx-auto">
                    Vue d'ensemble de tous les signalements d'annonces et équipements.
                </p>
            </div>
            
            <!-- Actions Header -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                <div class="flex flex-wrap gap-2 sm:gap-3">
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
                        <div class="text-xs sm:text-sm font-medium text-blue-600 uppercase tracking-wide">Total</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ $stats['total'] }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+5% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-flag text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-orange-600 uppercase tracking-wide">En Attente</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ $stats['pending'] }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-red-600">
                            <i class="fas fa-arrow-down mr-1"></i>
                            <span>-2% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-clock text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-blue-600 uppercase tracking-wide">En Cours</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ $stats['under_review'] }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+8% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-search text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-green-600 uppercase tracking-wide">Résolus</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ $stats['resolved'] }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+12% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-red-600 uppercase tracking-wide">Ventes</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ $stats['urgent_sales'] }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-red-600">
                            <i class="fas fa-arrow-down mr-1"></i>
                            <span>-3% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-shopping-cart text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wide">Équipements</div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-900 mt-1">{{ $stats['equipments'] }}</div>
                        <div class="flex items-center mt-2 text-xs sm:text-sm text-green-600">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+7% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-gray-100 p-3 rounded-full">
                        <i class="fas fa-tools text-gray-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4" id="filtersPanel" style="display: none;">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtres</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('administrateur.reports.all.index') }}">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select name="type" id="type" class="form-control">
                                <option value="">Tous les types</option>
                                <option value="urgent_sales" {{ request('type') === 'urgent_sales' ? 'selected' : '' }}>Annonces</option>
                                <option value="equipments" {{ request('type') === 'equipments' ? 'selected' : '' }}>Équipements</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Statut</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">Tous les statuts</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>En cours</option>
                                <option value="investigating" {{ request('status') === 'investigating' ? 'selected' : '' }}>Investigation</option>
                                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Résolu</option>
                                <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Rejeté</option>
                                <option value="escalated" {{ request('status') === 'escalated' ? 'selected' : '' }}>Escaladé</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="search">Recherche</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Titre, description, utilisateur..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary btn-block">Filtrer</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des signalements -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Signalements ({{ $allReports->count() }})</h6>
        </div>
        <div class="card-body">
            @if($paginatedReports->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Élément</th>
                                <th>Raison</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paginatedReports as $report)
                                <tr>
                                    <td>{{ $report->id }}</td>
                                    <td>
                                        @if($report->report_type === 'urgent_sale')
                                            <span class="badge badge-danger">Vente urgente</span>
                                        @else
                                            <span class="badge badge-info">Équipement</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ $report->item_url }}" target="_blank">
                                            {{ Str::limit($report->item_title, 50) }}
                                        </a>
                                        @if($report->report_type === 'urgent_sale' && $report->user)
                                            <br><small class="text-muted">Par: {{ $report->user->name }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($report->report_type === 'urgent_sale')
                                            <span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $report->reason)) }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $report->reason }}</span>
                                            @if($report->category)
                                                <br><small class="text-muted">{{ ucfirst($report->category) }}</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @switch($report->status)
                                            @case('pending')
                                                <span class="badge badge-warning">En attente</span>
                                                @break
                                            @case('reviewed')
                                            @case('under_review')
                                                <span class="badge badge-info">En cours</span>
                                                @break
                                            @case('investigating')
                                                <span class="badge badge-primary">Investigation</span>
                                                @break
                                            @case('resolved')
                                                <span class="badge badge-success">Résolu</span>
                                                @break
                                            @case('dismissed')
                                                <span class="badge badge-secondary">Rejeté</span>
                                                @break
                                            @case('escalated')
                                                <span class="badge badge-danger">Escaladé</span>
                                                @break
                                            @default
                                                <span class="badge badge-light">{{ $report->status }}</span>
                                        @endswitch
                                    </td>
                                    <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($report->report_type === 'urgent_sale')
                                            <a href="{{ route('administrateur.reports.urgent-sales.show', $report) }}" 
                                               class="btn btn-sm btn-primary">Voir</a>
                                        @else
                                            <a href="{{ route('administrateur.reports.equipments.show', $report) }}" 
                                               class="btn btn-sm btn-primary">Voir</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination simple -->
                @if($allReports->count() > 20)
                    <div class="d-flex justify-content-center mt-3">
                        <nav>
                            <ul class="pagination">
                                @php
                                    $currentPage = request()->get('page', 1);
                                    $totalPages = ceil($allReports->count() / 20);
                                @endphp
                                
                                @if($currentPage > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}">Précédent</a>
                                    </li>
                                @endif
                                
                                @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                                    <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                                    </li>
                                @endfor
                                
                                @if($currentPage < $totalPages)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}">Suivant</a>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                @endif
            @else
                <div class="text-center py-4">
                    <i class="fas fa-flag fa-3x text-gray-300 mb-3"></i>
                    <p class="text-muted">Aucun signalement trouvé.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// Toggle filters panel
function toggleFilters() {
    const panel = document.getElementById('filtersPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}
</script>

@endsection