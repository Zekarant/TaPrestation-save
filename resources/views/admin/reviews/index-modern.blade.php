@extends('layouts.admin-modern')

@section('page-title', 'Gestion des Avis')

@section('content')
<div class="bg-blue-50 min-h-screen">
    <!-- Bannière d'en-tête -->
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-blue-900 mb-2 leading-tight">
                    Avis et Évaluations
                </h1>
                <p class="text-base sm:text-lg text-blue-700 max-w-2xl mx-auto">
                    Modérez les avis clients et gérez la qualité du service
                </p>
            </div>
            <div class="flex justify-center gap-4">
                <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base" onclick="toggleFilters()">
                    <i class="fas fa-filter mr-2"></i>
                    Afficher les filtres
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
                        <div class="text-sm font-medium text-blue-700 mb-1">Total Avis</div>
                        <div class="text-2xl font-bold text-blue-900">{{ $reviews->total() ?? 0 }}</div>
                        <div class="text-xs text-green-600 flex items-center mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+15% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-star text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-blue-700 mb-1">Note Moyenne</div>
                        <div class="text-2xl font-bold text-blue-900">{{ number_format($averageRating ?? 4.2, 1) }}</div>
                        <div class="text-xs text-green-600 flex items-center mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>+0.2 ce mois</span>
                        </div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-thumbs-up text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-blue-700 mb-1">En Attente de Modération</div>
                        <div class="text-2xl font-bold text-blue-900">{{ $reviews->where('status', 'pending')->count() ?? 0 }}</div>
                        <div class="text-xs text-red-600 flex items-center mt-1">
                            <i class="fas fa-arrow-down mr-1"></i>
                            <span>-5% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-blue-700 mb-1">Avis Signalés</div>
                        <div class="text-2xl font-bold text-blue-900">{{ $reviews->where('is_reported', true)->count() ?? 0 }}</div>
                        <div class="text-xs text-green-600 flex items-center mt-1">
                            <i class="fas fa-arrow-down mr-1"></i>
                            <span>-8% ce mois</span>
                        </div>
                    </div>
                    <div class="bg-red-100 p-3 rounded-lg">
                        <i class="fas fa-flag text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Distribution Chart -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-6 sm:mb-8">
        <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-blue-900">Distribution des Notes</h3>
                <select class="bg-blue-50 border border-blue-300 text-blue-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                    <option>Ce mois</option>
                    <option>3 derniers mois</option>
                    <option>Cette année</option>
                </select>
            </div>
            <div class="grid grid-cols-5 gap-4">
                @for($i = 5; $i >= 1; $i--)
                    @php
                        $count = $reviews->where('rating', $i)->count() ?? 0;
                        $percentage = $reviews->count() > 0 ? ($count / $reviews->count()) * 100 : 0;
                    @endphp
                    <div class="text-center">
                        <div class="flex items-center justify-center gap-1 mb-2">
                            <span class="font-semibold text-blue-900">{{ $i }}</span>
                            <i class="fas fa-star text-yellow-400 text-sm"></i>
                        </div>
                        <div class="h-24 bg-blue-50 rounded relative mb-2">
                            <div class="absolute bottom-0 left-0 right-0 rounded transition-all duration-300" style="height: {{ $percentage }}%; background: linear-gradient(180deg, 
                                @if($i >= 4) #10b981 @elseif($i >= 3) #f59e0b @else #ef4444 @endif 0%, 
                                @if($i >= 4) #059669 @elseif($i >= 3) #d97706 @else #dc2626 @endif 100%);"></div>
                        </div>
                        <div class="text-sm font-medium text-blue-900">{{ $count }}</div>
                        <div class="text-xs text-blue-600">{{ number_format($percentage, 1) }}%</div>
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-6 sm:mb-8" id="filtersPanel" style="display: none;">
        <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-lg font-bold text-blue-900">Filtres de recherche</h4>
                <button class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center" onclick="clearFilters()">
                    <i class="fas fa-times mr-2"></i>
                    Effacer
                </button>
            </div>
            <form action="{{ route('administrateur.reviews.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-blue-700 mb-2">Client</label>
                    <input type="text" name="client" value="{{ request('client') }}" placeholder="Nom du client..." class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-blue-700 mb-2">Service</label>
                    <input type="text" name="service" value="{{ request('service') }}" placeholder="Nom du service..." class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-blue-700 mb-2">Note</label>
                    <select name="rating" class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Toutes les notes</option>
                        <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 étoiles</option>
                        <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 étoiles</option>
                        <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 étoiles</option>
                        <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 étoiles</option>
                        <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 étoile</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-blue-700 mb-2">Statut</label>
                    <select name="status" class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tous</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvé</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejeté</option>
                        <option value="reported" {{ request('status') == 'reported' ? 'selected' : '' }}>Signalé</option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center">
                        <i class="fas fa-search mr-2"></i>
                        Rechercher
                    </button>
                </div>
            </form>
        </div>
    </div>

<!-- Reviews List -->
<div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-6 sm:mb-8">
    <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="font-semibold text-blue-900">Liste des avis ({{ $reviews->total() ?? 0 }})</div>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-blue-700">Afficher :</label>
                    <select onchange="changePerPage(this.value)" class="bg-blue-50 border border-blue-300 text-blue-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 par page</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 par page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 par page</option>
                    </select>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center" onclick="selectAllReviews()">
                    <i class="fas fa-check-square mr-2"></i>
                    Tout sélectionner
                </button>
                <button class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center" onclick="bulkDeleteReviews()" id="bulkDeleteBtn" style="display: none;">
                    <i class="fas fa-trash mr-2"></i>
                    Supprimer sélectionnés
                </button>
            </div>
        </div>
        
        <div class="space-y-4">
            @forelse($reviews ?? [] as $review)
                <div class="bg-blue-50 border-l-4 rounded-xl p-6 @if($review->rating >= 4) border-green-500 @elseif($review->rating >= 3) border-yellow-500 @else border-red-500 @endif">
                    <div class="flex flex-col lg:flex-row justify-between items-start gap-4 mb-4">
                        <div class="flex items-start gap-4 flex-1">
                            <input type="checkbox" name="selected_reviews[]" value="{{ $review->id }}" class="review-checkbox mt-1 w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                            
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold text-lg">
                                {{ $review->client ? substr($review->client->name, 0, 1) : 'C' }}
                            </div>
                            
                            <div class="flex-1">
                                <div class="font-semibold text-blue-900 mb-1">{{ $review->client_name }}</div>
                                <div class="text-sm text-blue-700 mb-2">{{ $review->client_email ?? 'Email non disponible' }}</div>
                                <div class="flex items-center gap-3">
                                    <div class="flex gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="fas fa-star text-yellow-400 text-sm"></i>
                                            @else
                                                <i class="far fa-star text-gray-300 text-sm"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="font-medium text-blue-900 text-sm">{{ $review->rating }}/5</span>
                                    <span class="text-blue-600 text-xs">• {{ $review->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-2">
                            @switch($review->status)
                                @case('approved')
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium flex items-center">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Approuvé
                                    </span>
                                    @break
                                @case('pending')
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium flex items-center">
                                        <i class="fas fa-clock mr-1"></i>
                                        En attente
                                    </span>
                                    @break
                                @case('rejected')
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium flex items-center">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Rejeté
                                    </span>
                                    @break
                            @endswitch
                            
                            @if($review->is_reported)
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium flex items-center">
                                    <i class="fas fa-flag mr-1"></i>
                                    Signalé
                                </span>
                            @endif
                            
                            <div class="flex gap-2">
                                @if($review->status === 'pending')
                                    <button onclick="approveReview({{ $review->id }})" class="bg-green-600 hover:bg-green-700 text-white p-2 rounded-lg transition duration-200" title="Approuver">
                                        <i class="fas fa-check text-sm"></i>
                                    </button>
                                    <button onclick="rejectReview({{ $review->id }})" class="bg-red-600 hover:bg-red-700 text-white p-2 rounded-lg transition duration-200" title="Rejeter">
                                        <i class="fas fa-times text-sm"></i>
                                    </button>
                                @endif
                                
                                <button onclick="deleteReview({{ $review->id }})" class="bg-gray-100 hover:bg-gray-200 text-gray-700 p-2 rounded-lg transition duration-200" title="Supprimer">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    @if($review->service)
                        <div class="mb-4">
                            <div class="font-medium text-blue-900 mb-2">Service évalué :</div>
                            <div class="flex items-center gap-3">
                                @if($review->service->image)
                                    <img src="{{ asset('storage/' . $review->service->image) }}" alt="{{ $review->service->title }}" class="w-10 h-10 rounded-lg object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-semibold">
                                        {{ substr($review->service->title, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium text-blue-900 text-sm">{{ $review->service->title }}</div>
                                    <div class="text-xs text-blue-600">par {{ $review->service->prestataire->user->name ?? 'Prestataire inconnu' }}</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mb-4">
                            <div class="font-medium text-blue-900 mb-2">Service évalué :</div>
                            <div class="text-blue-600 italic">Service non disponible</div>
                        </div>
                    @endif
                    
                    @if($review->comment)
                        <div class="bg-white p-4 rounded-lg border-l-4 border-blue-500 mb-4">
                            <div class="font-medium text-blue-900 mb-2">Commentaire :</div>
                            <div class="text-blue-700 leading-relaxed">{{ $review->comment }}</div>
                        </div>
                    @endif
                    
                    @if($review->response)
                        <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                            <div class="font-medium text-green-800 mb-2 flex items-center">
                                <i class="fas fa-reply mr-2"></i>
                                Réponse du prestataire :
                            </div>
                            <div class="text-green-700 leading-relaxed">{{ $review->response }}</div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-12 text-blue-600">
                    <i class="fas fa-star text-5xl mb-4 text-blue-300"></i>
                    <div class="text-lg font-medium mb-2 text-blue-900">Aucun avis trouvé</div>
                    <div class="text-blue-700">Essayez de modifier vos critères de recherche</div>
                </div>
            @endforelse
        </div>
        
        @if($reviews && $reviews->hasPages())
            <div class="border-t border-blue-200 px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-blue-700 text-sm">
                    Affichage de {{ $reviews->firstItem() }} à {{ $reviews->lastItem() }} sur {{ $reviews->total() }} résultats
                </div>
                <div>
                    {{ $reviews->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Bulk Actions -->
<div id="bulkActions" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-white rounded-xl shadow-2xl border border-blue-200 px-6 py-4 hidden z-50">
    <div class="flex flex-wrap items-center gap-4">
        <span class="font-medium text-blue-900">Actions groupées :</span>
        <button class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center" onclick="bulkApprove()">
            <i class="fas fa-check mr-2"></i>
            Approuver
        </button>
        <button class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center" onclick="bulkReject()">
            <i class="fas fa-times mr-2"></i>
            Rejeter
        </button>
        <button class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center" onclick="bulkDelete()">
            <i class="fas fa-trash mr-2"></i>
            Supprimer
        </button>
        <button class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center" onclick="clearSelection()">
            <i class="fas fa-times mr-2"></i>
            Annuler
        </button>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
// Toggle filters panel
function toggleFilters() {
    const panel = document.getElementById('filtersPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

// Clear filters
function clearFilters() {
    window.location.href = '{{ route("administrateur.reviews.index") }}';
}

// Change items per page
function changePerPage(value) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', value);
    window.location.href = url.toString();
}

// Update bulk actions visibility
function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.review-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    
    if (checkedBoxes.length > 0) {
        bulkActions.style.display = 'block';
    } else {
        bulkActions.style.display = 'none';
    }
}

// Add event listeners to checkboxes
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.review-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
});

// Clear selection
function clearSelection() {
    const checkboxes = document.querySelectorAll('.review-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updateBulkActions();
}

// Review actions
function approveReview(reviewId) {
    if (confirm('Êtes-vous sûr de vouloir approuver cet avis ?')) {
        // Implement approve review logic
        console.log('Approving review:', reviewId);
    }
}

function rejectReview(reviewId) {
    if (confirm('Êtes-vous sûr de vouloir rejeter cet avis ?')) {
        // Implement reject review logic
        console.log('Rejecting review:', reviewId);
    }
}

function deleteReview(reviewId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet avis ? Cette action est irréversible.')) {
        // Implement delete review logic
        console.log('Deleting review:', reviewId);
    }
}

// Bulk actions
function bulkApprove() {
    const checkedBoxes = document.querySelectorAll('.review-checkbox:checked');
    if (checkedBoxes.length === 0) return;
    
    if (confirm(`Êtes-vous sûr de vouloir approuver ${checkedBoxes.length} avis ?`)) {
        // Implement bulk approve logic
        console.log('Bulk approving reviews');
    }
}

function bulkReject() {
    const checkedBoxes = document.querySelectorAll('.review-checkbox:checked');
    if (checkedBoxes.length === 0) return;
    
    if (confirm(`Êtes-vous sûr de vouloir rejeter ${checkedBoxes.length} avis ?`)) {
        // Implement bulk reject logic
        console.log('Bulk rejecting reviews');
    }
}

function bulkDelete() {
    const checkedBoxes = document.querySelectorAll('.review-checkbox:checked');
    if (checkedBoxes.length === 0) return;
    
    if (confirm(`Êtes-vous sûr de vouloir supprimer ${checkedBoxes.length} avis ? Cette action est irréversible.`)) {
        // Implement bulk delete logic
        console.log('Bulk deleting reviews');
    }
}


</script>
@endpush