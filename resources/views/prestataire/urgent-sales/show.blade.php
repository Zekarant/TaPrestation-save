@extends('layouts.app')

@section('title', $urgentSale->title)

@section('content')
<div class="bg-red-50">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
        <!-- En-tête -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <a href="{{ route('prestataire.urgent-sales.index') }}" class="text-red-600 hover:text-red-800 mr-4">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-4xl font-extrabold text-red-900 mb-2">{{ $urgentSale->title }}</h1>
                    <div class="flex items-center mt-2 space-x-4">
                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                            @if($urgentSale->status === 'active') bg-green-100 text-green-800
                            @elseif($urgentSale->status === 'sold') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $urgentSale->status_label }}
                        </span>
                        
                        <span class="text-red-700 text-sm">
                            Publié le {{ $urgentSale->created_at->format('d/m/Y à H:i') }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-3">
                @if($urgentSale->canBeEdited())
                    <a href="{{ route('prestataire.urgent-sales.edit', $urgentSale) }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-edit mr-2"></i>Modifier
                    </a>
                @endif
                
                <button type="button" id="deleteUrgentSaleBtn" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-trash mr-2"></i>Supprimer
                </button>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Colonne de gauche - Informations -->
            <div class="space-y-6">
                <!-- Informations principales -->
                <div class="bg-white rounded-xl shadow-lg border border-red-200">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-red-800 mb-5 border-b-2 border-red-200 pb-3">Informations</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <span class="text-sm font-medium text-red-600">Prix</span>
                                <div class="text-3xl font-extrabold text-red-600">{{ number_format($urgentSale->price, 0, ',', ' ') }} €</div>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-red-600">État</span>
                                <div class="text-lg font-semibold text-red-900">{{ $urgentSale->condition_label }}</div>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-red-600">Quantité</span>
                                <div class="text-lg font-semibold text-red-900">{{ $urgentSale->quantity }}</div>
                            </div>
                            
                            <div>
                                <span class="text-sm font-medium text-red-600">Localisation</span>
                                <div class="text-lg font-semibold text-red-900">
                                    <i class="fas fa-map-marker-alt text-red-400 mr-1"></i>{{ $urgentSale->location }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photos -->
                @if($urgentSale->photos && count($urgentSale->photos ?? []) > 0)
                    <div class="bg-white rounded-xl shadow-lg border border-red-200">
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-red-800 mb-5 border-b-2 border-red-200 pb-3">Photos</h2>
                            
                            <!-- Photo principale -->
                            <div class="mb-4">
                                <img id="main-image" src="{{ Storage::url($urgentSale->photos[0]) }}" alt="{{ $urgentSale->title }}" class="w-full h-64 object-cover rounded-lg">
                            </div>
                            
                            <!-- Miniatures -->
                            @if(count($urgentSale->photos ?? []) > 1)
                                <div class="grid grid-cols-4 gap-2">
                                    @foreach($urgentSale->photos ?? [] as $index => $photo)
                                        <img src="{{ Storage::url($photo) }}" alt="Photo {{ $index + 1 }}" class="w-full h-16 object-cover rounded cursor-pointer hover:opacity-75 transition-opacity {{ $index === 0 ? 'ring-2 ring-blue-500' : '' }}" onclick="changeMainImage('{{ Storage::url($photo) }}', this)">
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                
                <!-- Description -->
                <div class="bg-white rounded-xl shadow-lg border border-red-200">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-red-800 mb-5 border-b-2 border-red-200 pb-3">Description</h2>
                        <div class="prose max-w-none">
                            {!! nl2br(e($urgentSale->description)) !!}
                        </div>
                    </div>
                </div>
                
                

                
            </div>
            
            <!-- Colonne de droite - Messages reçus -->
            <div>
                <div>
                    <!-- Statistiques détaillées -->
                <div class="bg-white rounded-xl shadow-lg border border-red-200">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-red-800 mb-5 border-b-2 border-red-200 pb-3">Statistiques</h2>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-4 bg-red-50 rounded-lg border border-red-200">
                                <div class="text-2xl font-bold text-red-600">{{ $urgentSale->views_count }}</div>
                                <div class="text-sm text-red-700">Vues</div>
                            </div>
                            
                            <div class="text-center p-4 bg-red-50 rounded-lg border border-red-200">
                                <div class="text-2xl font-bold text-red-600">{{ $urgentSale->contact_count }}</div>
                                <div class="text-sm text-red-700">Contacts</div>
                            </div>
                            
                            <div class="text-center p-4 bg-red-50 rounded-lg border border-red-200">
                                <div class="text-2xl font-bold text-red-600">{{ $urgentSale->created_at->diffForHumans() }}</div>
                                <div class="text-sm text-red-700">En ligne depuis</div>
                            </div>
                            
                            <div class="text-center p-4 bg-red-50 rounded-lg border border-red-200">
                                <div class="text-2xl font-bold text-red-600">{{ $urgentSale->updated_at->diffForHumans() }}</div>
                                <div class="text-sm text-red-700">Dernière modification</div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
                @if($relatedMessages->count() > 0)
                    <div class="bg-white rounded-xl shadow-lg border border-red-200 h-fit">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-5">
                                <h2 class="text-2xl font-bold text-red-800 border-b-2 border-red-200 pb-3">Messages reçus</h2>
                                <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-semibold">{{ $relatedMessages->count() }}</span>
                            </div>
                            
                            <div class="space-y-4 max-h-96 overflow-y-auto">
                                @foreach($relatedMessages as $contact)
                                    <div class="bg-white border border-red-200 rounded-xl p-5 shadow-sm hover:shadow-md transition-all duration-200">
                                        <div class="flex justify-between items-start mb-3">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 shadow-sm overflow-hidden">
                                                    @if($contact->user && ($contact->user->client && $contact->user->client->photo))
                                                        <img src="{{ asset('storage/' . $contact->user->client->photo) }}" alt="{{ $contact->user->name }}" class="w-full h-full object-cover">
                                                    @elseif($contact->user && $contact->user->avatar)
                                                        <img src="{{ asset('storage/' . $contact->user->avatar) }}" alt="{{ $contact->user->name }}" class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full bg-gradient-to-r from-red-500 to-red-600 flex items-center justify-center">
                                                            <i class="fas fa-user text-white text-sm"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <span class="font-semibold text-red-900 text-base">{{ $contact->user->name }}</span>
                                                    <div class="text-xs text-red-600 mt-1">
                                                        <i class="fas fa-clock mr-1"></i>{{ $contact->created_at->format('d/m/Y à H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="text-xs text-red-500 bg-red-100 px-2 py-1 rounded-full">{{ $contact->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="bg-red-50 rounded-lg p-4 mb-4 border border-red-200">
                                            <p class="text-red-800 leading-relaxed">
                                                Concernant votre vente urgente '{{ $urgentSale->title }}': {{ $contact->message }} (#Référence : {{ $contact->id }})
                                            </p>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <div class="text-xs text-red-600">
                                                <i class="fas fa-envelope mr-1"></i>Contact reçu
                                            </div>
                                            <a href="{{ route('prestataire.prestataire.messages.show', $contact->user) }}" 
                               class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                                <i class="fas fa-reply mr-2"></i>Répondre
                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="mt-6 text-center">
                                <a href="{{ route('prestataire.prestataire.messages.index') }}" 
                                   class="inline-flex items-center px-6 py-3 bg-red-100 hover:bg-red-200 text-red-700 hover:text-red-900 font-medium rounded-lg transition-colors duration-200 border border-red-300 hover:border-red-400">
                                    <i class="fas fa-envelope mr-2"></i>Voir tous les messages
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-xl shadow-lg border border-red-200 h-fit">
                        <div class="p-6 text-center">
                            <i class="fas fa-comments text-red-400 text-4xl mb-4"></i>
                            <h3 class="text-lg font-semibold text-red-900 mb-2">Aucun message</h3>
                            <p class="text-red-700 text-sm">Vous n'avez pas encore reçu de message concernant cette vente.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
<div id="deleteConfirmationModal" class="fixed inset-0 flex items-center justify-center z-50 hidden transition-opacity duration-300" style="backdrop-filter: blur(5px); background-color: rgba(239, 68, 68, 0.8);">
    <div class="bg-white rounded-xl shadow-2xl p-6 sm:p-8 max-w-md w-full mx-4 border-4 border-red-500 transform transition-all duration-300 scale-95 opacity-0 modal-show">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100">
                <svg class="h-10 w-10 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mt-4">Confirmation de suppression</h3>
            <p class="text-gray-600 mt-2">
                Êtes-vous sûr de vouloir supprimer cette vente urgente ?
            </p>
            <p id="urgentSaleTitle" class="text-lg font-semibold text-red-900 mt-2">{{ $urgentSale->title }}</p>
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <button id="cancelDeleteBtn" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200 font-medium">
                    Annuler
                </button>
                <button id="confirmDeleteBtn" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 font-medium">
                    Supprimer
                </button>
            </div>
        </div>
    </div>
</div>
