@extends('layouts.app')

@section('title', 'Modération des avis')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- En-tête -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Modération des avis</h1>
                    <p class="text-gray-600 mt-2">{{ $pendingReviews->count() }} avis en attente de modération</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                        {{ $pendingReviews->count() }} en attente
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres et statistiques -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['total'] ?? 0 }}</div>
                    <div class="text-sm text-blue-600">Total avis</div>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</div>
                    <div class="text-sm text-yellow-600">En attente</div>
                </div>
                <div class="bg-green-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $stats['approved'] ?? 0 }}</div>
                    <div class="text-sm text-green-600">Approuvés</div>
                </div>
                <div class="bg-red-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $stats['rejected'] ?? 0 }}</div>
                    <div class="text-sm text-red-600">Rejetés</div>
                </div>
            </div>
        </div>

        <!-- Liste des avis en attente -->
        @if($pendingReviews->count() > 0)
        <div class="space-y-6">
            @foreach($pendingReviews as $review)
            <div class="bg-white rounded-lg shadow-md overflow-hidden" id="review-{{ $review->id }}">
                <div class="p-6">
                    <!-- En-tête de l'avis -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center space-x-4 mb-2">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    {{ $review->prestataire->nom }}
                                </h3>
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="text-lg {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}">*</span>
                                    @endfor
                                    <span class="ml-2 text-sm text-gray-600">{{ $review->rating }}/5</span>
                                </div>
                                @if($review->verified)
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                    Vérifié
                                </span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-600">
                                <span>Par: {{ $review->client_name }}</span>
                                <span class="mx-2">•</span>
                                <span>{{ $review->created_at->format('d/m/Y à H:i') }}</span>
                                @if($review->booking_id)
                                <span class="mx-2">•</span>
                                <span>Réservation #{{ $review->booking_id }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="moderateReview({{ $review->id }}, 'approved')" 
                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                Approuver
                            </button>
                            <button onclick="moderateReview({{ $review->id }}, 'rejected')" 
                                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                Rejeter
                            </button>
                        </div>
                    </div>

                    <!-- Notes détaillées -->
                    @if($review->punctuality_rating || $review->quality_rating || $review->value_rating || $review->communication_rating)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        @if($review->punctuality_rating)
                        <div class="text-center">
                            <div class="text-sm text-gray-600">Ponctualité</div>
                            <div class="font-semibold">{{ $review->punctuality_rating }}/5</div>
                        </div>
                        @endif
                        @if($review->quality_rating)
                        <div class="text-center">
                            <div class="text-sm text-gray-600">Qualité</div>
                            <div class="font-semibold">{{ $review->quality_rating }}/5</div>
                        </div>
                        @endif
                        @if($review->value_rating)
                        <div class="text-center">
                            <div class="text-sm text-gray-600">Rapport Q/P</div>
                            <div class="font-semibold">{{ $review->value_rating }}/5</div>
                        </div>
                        @endif
                        @if($review->communication_rating)
                        <div class="text-center">
                            <div class="text-sm text-gray-600">Communication</div>
                            <div class="font-semibold">{{ $review->communication_rating }}/5</div>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Commentaire -->
                    @if($review->comment)
                    <div class="mb-4">
                        <h4 class="font-medium text-gray-800 mb-2">Commentaire:</h4>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-gray-700">{{ $review->comment }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Photos -->
                    @if($review->photos && count($review->photos) > 0)
                    <div class="mb-4">
                        <h4 class="font-medium text-gray-800 mb-2">Photos ({{ count($review->photos) }}):</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($review->photos as $index => $photo)
                            <div class="relative group cursor-pointer" 
                                 onclick="openPhotoModal('{{ asset('storage/' . $photo) }}', 'Photo {{ $index + 1 }}')">
                                <img src="{{ asset('storage/' . $photo) }}" 
                                     alt="Photo {{ $index + 1 }}"
                                     class="w-20 h-20 object-cover rounded-lg shadow-md group-hover:shadow-lg transition duration-200">
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition duration-200 flex items-center justify-center">
                                    <span class="text-white opacity-0 group-hover:opacity-100 transition duration-200 text-xs">
                                        Rechercher
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Actions de modération -->
                    <div class="border-t pt-4">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                ID: {{ $review->id }} • 
                                <a href="{{ route('reviews.show', $review->id) }}" 
                                   class="text-blue-600 hover:text-blue-800 transition duration-200"
                                   target="_blank">
                                    Voir le détail →
                                </a>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="showModerationModal({{ $review->id }}, 'approved')" 
                                        class="bg-green-100 hover:bg-green-200 text-green-800 px-3 py-1 rounded text-sm transition duration-200">
                                    Approuver avec commentaire
                                </button>
                                <button onclick="showModerationModal({{ $review->id }}, 'rejected')" 
                                        class="bg-red-100 hover:bg-red-200 text-red-800 px-3 py-1 rounded text-sm transition duration-200">
                                    Rejeter avec raison
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">OK</div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Aucun avis en attente</h3>
            <p class="text-gray-500">Tous les avis ont été modérés.</p>
        </div>
        @endif
    </div>
</div>

<!-- Modal pour la modération avec commentaire -->
<div id="moderationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 id="modalTitle" class="text-lg font-semibold mb-4"></h3>
        <form id="moderationForm">
            <input type="hidden" id="reviewId" name="review_id">
            <input type="hidden" id="moderationAction" name="action">
            
            <div class="mb-4">
                <label for="moderationComment" class="block text-sm font-medium text-gray-700 mb-2">
                    Commentaire (optionnel)
                </label>
                <textarea id="moderationComment" 
                          name="comment" 
                          rows="3" 
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Ajoutez un commentaire sur votre décision..."></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" 
                        onclick="closeModerationModal()" 
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 transition duration-200">
                    Annuler
                </button>
                <button type="submit" 
                        id="submitModeration"
                        class="px-4 py-2 rounded-lg text-white transition duration-200">
                    Confirmer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal pour afficher les photos -->
<div id="photoModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 flex items-center justify-center">
    <div class="relative max-w-4xl max-h-full p-4">
        <button onclick="closePhotoModal()" 
                class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300 z-10">
            ×
        </button>
        <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain">
        <div id="modalCaption" class="text-white text-center mt-4"></div>
    </div>
</div>

<script>
// Modération rapide
function moderateReview(reviewId, action) {
    if (confirm(`Êtes-vous sûr de vouloir ${action === 'approved' ? 'approuver' : 'rejeter'} cet avis ?`)) {
        submitModeration(reviewId, action, '');
    }
}

// Modal de modération avec commentaire
function showModerationModal(reviewId, action) {
    const modal = document.getElementById('moderationModal');
    const title = document.getElementById('modalTitle');
    const reviewIdInput = document.getElementById('reviewId');
    const actionInput = document.getElementById('moderationAction');
    const submitBtn = document.getElementById('submitModeration');
    
    title.textContent = action === 'approved' ? 'Approuver l\'avis' : 'Rejeter l\'avis';
    reviewIdInput.value = reviewId;
    actionInput.value = action;
    
    if (action === 'approved') {
        submitBtn.className = 'px-4 py-2 bg-green-500 hover:bg-green-600 rounded-lg text-white transition duration-200';
        submitBtn.textContent = 'Approuver';
    } else {
        submitBtn.className = 'px-4 py-2 bg-red-500 hover:bg-red-600 rounded-lg text-white transition duration-200';
        submitBtn.textContent = 'Rejeter';
    }
    
    modal.classList.remove('hidden');
}

function closeModerationModal() {
    const modal = document.getElementById('moderationModal');
    modal.classList.add('hidden');
    document.getElementById('moderationComment').value = '';
}

// Soumission du formulaire de modération
document.getElementById('moderationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const reviewId = document.getElementById('reviewId').value;
    const action = document.getElementById('moderationAction').value;
    const comment = document.getElementById('moderationComment').value;
    
    submitModeration(reviewId, action, comment);
    closeModerationModal();
});

// Fonction pour soumettre la modération
function submitModeration(reviewId, action, comment) {
    const formData = new FormData();
    formData.append('action', action);
    formData.append('comment', comment);
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'PATCH');
    
    fetch(`/reviews/${reviewId}/moderate`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Supprimer l'avis de la liste
            const reviewElement = document.getElementById(`review-${reviewId}`);
            if (reviewElement) {
                reviewElement.style.transition = 'opacity 0.3s ease';
                reviewElement.style.opacity = '0';
                setTimeout(() => {
                    reviewElement.remove();
                    // Mettre à jour le compteur
                    updatePendingCount();
                }, 300);
            }
            
            // Afficher un message de succès
            showNotification(`Avis ${action === 'approved' ? 'approuvé' : 'rejeté'} avec succès`, 'success');
        } else {
            showNotification('Erreur lors de la modération', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la modération', 'error');
    });
}

// Mettre à jour le compteur d'avis en attente
function updatePendingCount() {
    const remainingReviews = document.querySelectorAll('[id^="review-"]').length;
    const countElement = document.querySelector('.text-gray-600');
    if (countElement) {
        countElement.textContent = `${remainingReviews} avis en attente de modération`;
    }
    
    // Si plus d'avis en attente, afficher le message vide
    if (remainingReviews === 0) {
        location.reload();
    }
}

// Fonction pour afficher les notifications
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 transition-opacity duration-300 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Modal pour les photos
function openPhotoModal(imageSrc, caption) {
    const modal = document.getElementById('photoModal');
    const modalImage = document.getElementById('modalImage');
    const modalCaption = document.getElementById('modalCaption');
    
    modalImage.src = imageSrc;
    modalCaption.textContent = caption;
    modal.classList.remove('hidden');
}

function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    modal.classList.add('hidden');
}

// Fermer les modals en cliquant à l'extérieur
document.getElementById('moderationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModerationModal();
    }
});

document.getElementById('photoModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePhotoModal();
    }
});

// Fermer avec Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModerationModal();
        closePhotoModal();
    }
});
</script>
@endsection