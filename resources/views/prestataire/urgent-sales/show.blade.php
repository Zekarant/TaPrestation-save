@extends('layouts.app')

@section('title', $urgentSale->title)

@section('content')
<style>
    .bg-red-gradient {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }
    
    .text-red-gradient {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 16px;
        font-weight: 600;
        font-size: 12px;
        border: 2px solid;
        transition: all 0.3s ease;
    }

    @media (min-width: 640px) {
        .status-badge {
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 20px;
        }
    }

    .status-badge.pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border-color: #f59e0b;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);
    }

    .status-badge.confirmed {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-color: #10b981;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
    }

    .status-badge.completed {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
        border-color: #3b82f6;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
    }

    .status-badge.refused, .status-badge.inactive {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        border-color: #ef4444;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
    }

    .status-badge i {
        margin-right: 4px;
        font-size: 12px;
    }

    @media (min-width: 640px) {
        .status-badge i {
            margin-right: 6px;
            font-size: 14px;
        }
    }
    
    /* Mobile optimizations */
    @media (max-width: 640px) {
        .mobile-stack {
            flex-direction: column !important;
        }
        .mobile-full {
            width: 100% !important;
        }
        .mobile-text-sm {
            font-size: 0.875rem !important;
        }
        .mobile-p-3 {
            padding: 0.75rem !important;
        }
        .mobile-gap-2 {
            gap: 0.5rem !important;
        }
    }

    /* Safe area for mobile */
    @supports (padding-bottom: env(safe-area-inset-bottom)) {
        .mobile-safe-bottom {
            padding-bottom: calc(5rem + env(safe-area-inset-bottom)) !important;
        }
    }
</style>

<div class="bg-red-50 mobile-safe-bottom">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-8">
        <div class="max-w-6xl mx-auto">
            <!-- En-tête (même structure que les autres pages détails) -->
            <div class="flex items-start justify-between gap-4 mb-4 sm:mb-6">
                <div class="flex items-start gap-4 min-w-0">
                    <a href="{{ route('prestataire.urgent-sales.index') }}" class="text-red-600 hover:text-red-800 mt-1">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div class="min-w-0">
                        <h1 class="text-xl sm:text-3xl font-extrabold text-red-900 leading-tight">{{ $urgentSale->title }}</h1>
                        <div class="mt-1 text-sm sm:text-base text-red-700 truncate">
                            Publié le {{ $urgentSale->created_at->format('d/m/Y à H:i') }}
                        </div>
                    </div>
                </div>

                <div class="flex flex-col items-end gap-3">
                    <span class="status-badge {{ $urgentSale->status === 'active' ? 'confirmed' : ($urgentSale->status === 'sold' ? 'completed' : 'inactive') }}">
                        @if($urgentSale->status === 'active')
                            <i class="fas fa-check-circle"></i>
                        @elseif($urgentSale->status === 'sold')
                            <i class="fas fa-check-double"></i>
                        @else
                            <i class="fas fa-clock"></i>
                        @endif
                        {{ $urgentSale->status_label }}
                    </span>

                    <div class="flex flex-wrap gap-2 justify-end">
                        @if(($urgentSale->contacts_count ?? 0) > 0)
                            <a href="{{ route('prestataire.urgent-sales.contacts', $urgentSale) }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-users mr-1"></i>Gérer {{ $urgentSale->contacts_count ?? 0 }} contact(s)
                            </a>
                        @endif
                        
                        @if($urgentSale->canBeEdited())
                            <a href="{{ route('prestataire.urgent-sales.edit', $urgentSale) }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-sm">
                                <i class="fas fa-edit mr-1"></i>Modifier
                            </a>
                        @endif
                        
                        <button type="button" id="deleteUrgentSaleBtn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 text-sm">
                            <i class="fas fa-trash mr-1"></i>Supprimer
                        </button>
                    </div>
                </div>
            </div>
        
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-8">
            <!-- Colonne principale -->
            <div class="xl:col-span-2 space-y-4 sm:space-y-8">
                <!-- Informations principales -->
                <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6">
                    <div class="flex items-center space-x-3 mb-4 sm:mb-6">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-red-800 border-b-2 border-red-200 pb-2">Informations</h2>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 sm:gap-6">
                        <div class="space-y-3 sm:space-y-4">
                            <div class="bg-red-50 rounded-lg p-2.5 sm:p-3">
                                <div class="text-xs font-medium text-red-600 uppercase tracking-wide">Prix</div>
                                <div class="text-lg sm:text-2xl font-bold text-red-600 mt-1">{{ number_format($urgentSale->price, 0, ',', ' ') }} €</div>
                            </div>
                            <div class="bg-red-50 rounded-lg p-2.5 sm:p-3">
                                <div class="text-xs font-medium text-red-600 uppercase tracking-wide">État</div>
                                <div class="text-sm sm:text-lg font-semibold text-red-900 mt-1">{{ $urgentSale->condition_label }}</div>
                            </div>
                        </div>
                        <div class="space-y-3 sm:space-y-4">
                            <div class="bg-red-50 rounded-lg p-2.5 sm:p-3">
                                <div class="text-xs font-medium text-red-600 uppercase tracking-wide">Quantité en stock</div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-sm sm:text-lg font-semibold text-red-900">{{ $urgentSale->quantity ?? 'N/A' }}</span>
                                    @if($urgentSale->quantity !== null)
                                        @if($urgentSale->quantity > 5)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>En stock
                                            </span>
                                        @elseif($urgentSale->quantity > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>Stock faible
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1"></i>Épuisé
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="bg-red-50 rounded-lg p-2.5 sm:p-3">
                                <div class="text-xs font-medium text-red-600 uppercase tracking-wide">Localisation</div>
                                <div class="text-xs sm:text-sm font-semibold text-red-900 mt-1">
                                    <i class="fas fa-map-marker-alt text-red-400 mr-1"></i>{{ $urgentSale->location }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photos -->
                @if($urgentSale->photos && count($urgentSale->photos ?? []) > 0)
                    <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6">
                        <div class="flex items-center space-x-3 mb-4 sm:mb-6">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h2 class="text-lg sm:text-xl font-bold text-red-800 border-b-2 border-red-200 pb-2">Photos</h2>
                        </div>
                        
                        <!-- Photo principale -->
                        <div class="mb-4">
                            <x-media-image :path="$urgentSale->photos[0]" :alt="$urgentSale->title" id="main-image" class="w-full h-48 sm:h-64 object-cover rounded-lg" />
                        </div>
                        
                        <!-- Miniatures -->
                        @if(count($urgentSale->photos ?? []) > 1)
                            <div class="grid grid-cols-4 gap-2">
                                @foreach($urgentSale->photos ?? [] as $index => $photo)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->exists($photo) ? \Illuminate\Support\Facades\Storage::url($photo) : asset('images/placeholder.svg') }}" alt="Photo {{ $index + 1 }}" class="w-full h-12 sm:h-16 object-cover rounded cursor-pointer hover:opacity-75 transition-opacity {{ $index === 0 ? 'ring-2 ring-red-500' : '' }}" onclick="changeMainImage('{{ \Illuminate\Support\Facades\Storage::disk('public')->exists($photo) ? \Illuminate\Support\Facades\Storage::url($photo) : asset('images/placeholder.svg') }}', this)">
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
                
                <!-- Description -->
                <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6">
                    <div class="flex items-center space-x-3 mb-4 sm:mb-6">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-red-800 border-b-2 border-red-200 pb-2">Description</h2>
                    </div>
                    <div class="bg-red-50 rounded-lg p-3 sm:p-4">
                        <div class="text-sm text-gray-700 leading-relaxed">
                            {!! nl2br(e($urgentSale->description)) !!}
                        </div>
                    </div>
                </div>
                
                

                
            </div>
            
            <!-- Colonne latérale -->
            <div class="space-y-4 sm:space-y-6">
                <!-- Statistiques détaillées -->
                <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6">
                    <div class="flex items-center space-x-3 mb-4 sm:mb-6">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h2 class="text-lg sm:text-xl font-bold text-red-800 border-b-2 border-red-200 pb-2">Statistiques</h2>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 sm:gap-4">
                        <div class="text-center p-3 sm:p-4 bg-red-50 rounded-lg border border-red-200">
                            <div class="text-xl sm:text-2xl font-bold text-red-600">{{ $urgentSale->views_count }}</div>
                            <div class="text-xs sm:text-sm text-red-700">Vues</div>
                        </div>
                        
                        <div class="text-center p-3 sm:p-4 bg-red-50 rounded-lg border border-red-200">
                            <div class="text-xl sm:text-2xl font-bold text-red-600">{{ $urgentSale->contact_count }}</div>
                            <div class="text-xs sm:text-sm text-red-700">Contacts</div>
                        </div>
                        
                        <div class="text-center p-3 sm:p-4 bg-red-50 rounded-lg border border-red-200">
                            <div class="text-sm sm:text-base font-bold text-red-600">{{ $urgentSale->created_at->diffForHumans() }}</div>
                            <div class="text-xs sm:text-sm text-red-700">En ligne depuis</div>
                        </div>
                        
                        <div class="text-center p-3 sm:p-4 bg-red-50 rounded-lg border border-red-200">
                            <div class="text-sm sm:text-base font-bold text-red-600">{{ $urgentSale->updated_at->diffForHumans() }}</div>
                            <div class="text-xs sm:text-sm text-red-700">Dernière modif.</div>
                        </div>
                    </div>
                </div>
                
                <!-- Messages reçus -->
                @if($relatedMessages->count() > 0)
                    <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6">
                        <div class="flex justify-between items-center mb-4 sm:mb-6">
                            <div class="flex items-center space-x-3">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                                <h2 class="text-lg sm:text-xl font-bold text-red-800">Messages reçus</h2>
                            </div>
                            <span class="bg-red-100 text-red-800 px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-semibold">{{ $relatedMessages->count() }}</span>
                        </div>
                        
                        <div class="space-y-3 sm:space-y-4 max-h-80 sm:max-h-96 overflow-y-auto">
                            @foreach($relatedMessages as $contact)
                                <div class="bg-red-50 border border-red-200 rounded-lg p-3 sm:p-4">
                                    <div class="flex justify-between items-start mb-2 sm:mb-3">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center mr-2 sm:mr-3 overflow-hidden">
                                                @if($contact->user && ($contact->user->client && $contact->user->client->photo))
                                                    <img src="{{ asset('storage/' . $contact->user->client->photo) }}" alt="{{ $contact->user->name }}" class="w-full h-full object-cover">
                                                @elseif($contact->user && $contact->user->avatar)
                                                    <img src="{{ asset('storage/' . $contact->user->avatar) }}" alt="{{ $contact->user->name }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full bg-linear-to-r from-red-500 to-red-600 flex items-center justify-center">
                                                        <i class="fas fa-user text-white text-xs sm:text-sm"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="font-semibold text-red-900 text-sm sm:text-base">{{ $contact->user->name }}</span>
                                                <div class="text-xs text-red-600">
                                                    {{ $contact->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-xs sm:text-sm text-red-800 mb-3 line-clamp-2">{{ $contact->message }}</p>
                                    <a href="{{ route('prestataire.messages.show', $contact->user) }}" 
                                       class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-medium rounded-lg transition-colors duration-200">
                                        <i class="fas fa-reply mr-1.5"></i>Répondre
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4 text-center">
                            <a href="{{ route('prestataire.messages.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 hover:text-red-900 text-sm font-medium rounded-lg transition-colors duration-200 border border-red-300">
                                <i class="fas fa-envelope mr-2"></i>Voir tous les messages
                            </a>
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6 text-center">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-base sm:text-lg font-semibold text-red-900 mb-2">Aucun message</h3>
                        <p class="text-red-700 text-xs sm:text-sm">Vous n'avez pas encore reçu de message concernant cette vente.</p>
                    </div>
                @endif
            </div>
        </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function changeMainImage(src, element) {
    document.getElementById('main-image').src = src;
    
    // Retirer la bordure de toutes les miniatures
    document.querySelectorAll('.grid img').forEach(img => {
        img.classList.remove('ring-2', 'ring-blue-500');
    });
    
    // Ajouter la bordure à la miniature cliquée
    element.classList.add('ring-2', 'ring-blue-500');
}

// Delete urgent sale modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const deleteBtn = document.getElementById('deleteUrgentSaleBtn');
    const deleteModal = document.getElementById('deleteConfirmationModal');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            deleteModal.classList.remove('hidden');
            
            // Add animation classes
            setTimeout(() => {
                deleteModal.classList.remove('opacity-0');
                const modalContent = deleteModal.querySelector('.modal-show');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
                modalContent.classList.remove('opacity-0');
            }, 10);
        });
    }
    
    // Handle cancel delete
    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', function() {
            closeModal();
        });
    }
    
    // Handle confirm delete
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            // Create a form dynamically and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('prestataire.urgent-sales.destroy', $urgentSale) }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        });
    }
    
    // Close modal when clicking outside
    if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                closeModal();
            }
        });
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && deleteModal && !deleteModal.classList.contains('hidden')) {
            closeModal();
        }
    });
    
    // Function to close modal with animation
    function closeModal() {
        const modalContent = deleteModal.querySelector('.modal-show');
        if (modalContent) {
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            modalContent.classList.add('opacity-0');
        }
        if (deleteModal) {
            deleteModal.classList.add('opacity-0');
            
            setTimeout(() => {
                deleteModal.classList.add('hidden');
            }, 300);
        }
    }
});
</script>
@endpush

@push('styles')
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.prose {
    line-height: 1.6;
}
</style>
@endpush

<!-- Modal de confirmation de suppression -->
<div id="deleteConfirmationModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-200" style="backdrop-filter: blur(3px); background-color: rgba(0, 0, 0, 0.45);">
    <div class="min-h-full w-full flex items-end sm:items-center justify-center p-3">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl p-4 sm:p-6 w-full sm:max-w-md border border-red-200 transform transition-all duration-200 scale-95 opacity-0 modal-show">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-50">
                    <svg class="h-7 w-7 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mt-3">Confirmation de suppression</h3>
                <p class="text-gray-600 mt-2">
                    Êtes-vous sûr de vouloir supprimer cette vente urgente ?
                </p>
                <p id="urgentSaleTitle" class="text-base font-semibold text-red-700 mt-2">{{ $urgentSale->title }}</p>
                <div class="mt-5 flex flex-col sm:flex-row gap-3">
                    <button id="cancelDeleteBtn" class="flex-1 px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">
                        Annuler
                    </button>
                    <button id="confirmDeleteBtn" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 font-medium">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

