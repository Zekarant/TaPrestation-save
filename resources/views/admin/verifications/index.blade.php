@extends('layouts.admin-modern')

@section('title', 'Gestion des vérifications')

@section('content')
<div class="bg-orange-50 min-h-screen">
    <!-- Bannière d'en-tête -->
    <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-green-900 mb-2 leading-tight">
                    Gestion des Vérifications
                </h1>
                <p class="text-base sm:text-lg text-green-700 max-w-2xl mx-auto">
                    Gérez et validez les demandes de vérification des prestataires.
                </p>
            </div>
            <div class="text-center">
                <form action="{{ route('admin.verifications.run-automatic') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center text-sm sm:text-base mx-auto" onclick="return confirm('Lancer la vérification automatique pour tous les prestataires éligibles ?')">
                        <i class="fas fa-magic mr-2"></i>Vérification automatique
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 py-4 sm:py-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 border border-orange-100 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-orange-900">{{ $stats['pending'] }}</h4>
                        <p class="text-orange-700 font-medium">Demandes en attente</p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 border border-orange-100 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-orange-900">{{ $stats['approved'] }}</h4>
                        <p class="text-orange-700 font-medium">Approuvées ce mois</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 border border-orange-100 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-orange-900">{{ $stats['rejected'] }}</h4>
                        <p class="text-orange-700 font-medium">Rejetées ce mois</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 border border-orange-100 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-orange-900">{{ $stats['total'] }}</h4>
                        <p class="text-orange-700 font-medium">Prestataires vérifiés</p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-user-check text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages de session -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <!-- Section des filtres -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-8">
        <div class="bg-white rounded-xl shadow-md border border-orange-200">
            <div class="p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg sm:text-xl font-bold text-green-800">Filtres de recherche</h2>
                    <button type="button" id="toggleFilters" class="lg:hidden bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-filter mr-1"></i>
                        <span>Afficher les filtres</span>
                    </button>
                </div>
                
                <div id="filtersContent" class="hidden lg:block">
                    <form method="GET" action="{{ route('admin.verifications.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-green-700 mb-2">Statut</label>
                                <select name="status" id="status" class="w-full px-3 py-2 border border-green-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white">
                                    <option value="">Tous les statuts</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvées</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejetées</option>
                                </select>
                            </div>
                            <div>
                                <label for="document_type" class="block text-sm font-medium text-green-700 mb-2">Type de document</label>
                                <select name="document_type" id="document_type" class="w-full px-3 py-2 border border-green-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white">
                                    <option value="">Tous les types</option>
                                    <option value="identity" {{ request('document_type') == 'identity' ? 'selected' : '' }}>Pièce d'identité</option>
                                    <option value="professional" {{ request('document_type') == 'professional' ? 'selected' : '' }}>Document professionnel</option>
                                    <option value="business" {{ request('document_type') == 'business' ? 'selected' : '' }}>Document d'entreprise</option>
                                </select>
                            </div>
                            <div>
                                <label for="search" class="block text-sm font-medium text-green-700 mb-2">Rechercher</label>
                                <input type="text" name="search" id="search" class="w-full px-3 py-2 border border-green-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                       placeholder="Nom du prestataire..." value="{{ request('search') }}">
                            </div>
                            <div class="flex items-end space-x-2">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center">
                                    <i class="fas fa-search mr-2"></i>Filtrer
                                </button>
                                <a href="{{ route('admin.verifications.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center">
                                    <i class="fas fa-times mr-2"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des demandes -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 mb-8">
        <div class="bg-white rounded-xl shadow-md border border-orange-200">
            <div class="p-4 sm:p-6">
                <h2 class="text-lg sm:text-xl font-bold text-green-800 mb-6">Demandes de vérification</h2>
                
                @if($verificationRequests->count() > 0)
                    <div class="grid grid-cols-1 gap-4 sm:gap-6">
                        @foreach($verificationRequests as $request)
                            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-orange-100">
                                <div class="p-4 sm:p-6">
                                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                                        <!-- Informations du prestataire -->
                                        <div class="flex items-center space-x-4">
                                            <div class="relative">
                                                @if($request->prestataire->profile_photo)
                                                    <img src="{{ Storage::url($request->prestataire->profile_photo) }}" 
                                                         alt="{{ $request->prestataire->nom }}" 
                                                         class="w-12 h-12 rounded-full object-cover">
                                                @else
                                                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                                                        <span class="text-orange-600 font-bold text-lg">
                                                            {{ substr($request->prestataire->nom, 0, 1) }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-orange-900">{{ $request->prestataire->nom }} {{ $request->prestataire->prenom }}</h3>
                                                <p class="text-orange-700 text-sm">{{ $request->prestataire->user->email }}</p>
                                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                                    @switch($request->document_type)
                                                        @case('identity')
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                <i class="fas fa-id-card mr-1"></i>Pièce d'identité
                                                            </span>
                                                            @break
                                                        @case('professional')
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                                <i class="fas fa-briefcase mr-1"></i>Document professionnel
                                                            </span>
                                                            @break
                                                        @case('business')
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                                <i class="fas fa-building mr-1"></i>Document d'entreprise
                                                            </span>
                                                            @break
                                                    @endswitch
                                                    
                                                    @if($request->isPending())
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            <i class="fas fa-clock mr-1"></i>En attente
                                                        </span>
                                                    @elseif($request->isApproved())
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <i class="fas fa-check mr-1"></i>Approuvée
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            <i class="fas fa-times mr-1"></i>Rejetée
                                                        </span>
                                                    @endif
                                                    
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                        <i class="fas fa-file mr-1"></i>{{ count($request->documents ?? []) }} document(s)
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Date et actions -->
                                        <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                                            <div class="text-sm text-orange-700">
                                                <div><strong>Soumis:</strong> {{ $request->submitted_at->format('d/m/Y H:i') }}</div>
                                            </div>
                                            
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ route('admin.verifications.show', $request) }}" 
                                                   class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-2 rounded-lg transition duration-200 flex items-center text-sm" 
                                                   title="Voir les détails">
                                                    <i class="fas fa-eye mr-1"></i>Voir
                                                </a>
                                                
                                                @if($request->isPending())
                                                    <button type="button" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg transition duration-200 flex items-center text-sm" 
                                                            onclick="approveRequest({{ $request->id }})" title="Approuver">
                                                        <i class="fas fa-check mr-1"></i>Approuver
                                                    </button>
                                                    <button type="button" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg transition duration-200 flex items-center text-sm" 
                                                            onclick="rejectRequest({{ $request->id }})" title="Rejeter">
                                                        <i class="fas fa-times mr-1"></i>Rejeter
                                                    </button>
                                                @endif
                                                
                                                @if($request->prestataire->isVerified())
                                                    <button type="button" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded-lg transition duration-200 flex items-center text-sm" 
                                                            onclick="revokeVerification({{ $request->prestataire->id }})" title="Révoquer la vérification">
                                                        <i class="fas fa-ban mr-1"></i>Révoquer
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="flex justify-center mt-8">
                        {{ $verificationRequests->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="bg-orange-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-inbox text-orange-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-orange-900 mb-2">Aucune demande de vérification trouvée</h3>
                        <p class="text-orange-700">Les demandes de vérification apparaîtront ici une fois soumises par les prestataires.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal d'approbation -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approuver la demande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="approve_comment">Commentaire (optionnel)</label>
                        <textarea name="admin_comment" id="approve_comment" class="form-control" rows="3" 
                                  placeholder="Commentaire pour le prestataire..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Approuver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de rejet -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rejeter la demande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="reject_comment">Motif du rejet <span class="text-danger">*</span></label>
                        <textarea name="admin_comment" id="reject_comment" class="form-control" rows="3" 
                                  placeholder="Expliquez pourquoi la demande est rejetée..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Rejeter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    </div>
</div>

@push('scripts')
<script>
// Toggle des filtres sur mobile
document.getElementById('toggleFilters')?.addEventListener('click', function() {
    const filtersContent = document.getElementById('filtersContent');
    const isHidden = filtersContent.classList.contains('hidden');
    
    if (isHidden) {
        filtersContent.classList.remove('hidden');
        this.innerHTML = '<i class="fas fa-times mr-1"></i><span>Fermer</span>';
    } else {
        filtersContent.classList.add('hidden');
        this.innerHTML = '<i class="fas fa-filter mr-1"></i><span>Afficher les filtres</span>';
    }
});

function approveRequest(requestId) {
    // Bootstrap 5 syntax
    document.getElementById('approveForm').setAttribute('action', `/admin/verifications/${requestId}/approve`);
    const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
    approveModal.show();
}

function rejectRequest(requestId) {
    // Bootstrap 5 syntax
    document.getElementById('rejectForm').setAttribute('action', `/admin/verifications/${requestId}/reject`);
    const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
    rejectModal.show();
}

function revokeVerification(prestataireId) {
    if (confirm('Êtes-vous sûr de vouloir révoquer la vérification de ce prestataire ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/verifications/${prestataireId}/revoke`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PATCH';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection