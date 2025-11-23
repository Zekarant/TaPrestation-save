@extends('layouts.app')

@section('title', 'Avis avec photos - ' . $prestataire->nom)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- En-tête -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Avis avec photos</h1>
                    <p class="text-gray-600 mt-2">{{ $prestataire->nom }} - {{ $reviews->count() }} avis avec photos</p>
                </div>
                <a href="{{ route('reviews.index', $prestataire->id) }}" 
                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition duration-200">
                    Tous les avis
                </a>
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="flex flex-wrap gap-4">
                <select id="ratingFilter" class="border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Toutes les notes</option>
                    <option value="5">5 étoiles</option>
                    <option value="4">4 étoiles</option>
                    <option value="3">3 étoiles</option>
                    <option value="2">2 étoiles</option>
                    <option value="1">1 étoile</option>
                </select>
                
                <select id="sortFilter" class="border border-gray-300 rounded-lg px-3 py-2">
                    <option value="recent">Plus récents</option>
                    <option value="oldest">Plus anciens</option>
                    <option value="rating_high">Note la plus élevée</option>
                    <option value="rating_low">Note la plus faible</option>
                </select>
            </div>
        </div>

        <!-- Grille des avis avec photos -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="reviewsGrid">
            @foreach($reviews as $review)
            <div class="review-card bg-white rounded-lg shadow-md overflow-hidden" 
                 data-rating="{{ $review->rating }}" 
                 data-date="{{ $review->created_at->timestamp }}">
                
                <!-- Photos -->
                <div class="relative h-48 bg-gray-200">
                    @if($review->photos && count($review->photos) > 0)
                        <div class="carousel relative h-full">
                            @foreach($review->photos as $index => $photo)
                            <img src="{{ asset('storage/' . $photo) }}" 
                                 alt="Photo avis {{ $index + 1 }}"
                                 class="carousel-image absolute inset-0 w-full h-full object-cover cursor-pointer {{ $index === 0 ? 'opacity-100' : 'opacity-0' }}"
                                 data-index="{{ $index }}"
                                 onclick="openPhotoModal('{{ asset('storage/' . $photo) }}', '{{ $review->client_name }}')">
                            @endforeach
                            
                            @if(count($review->photos) > 1)
                            <!-- Indicateurs -->
                            <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex space-x-1">
                                @foreach($review->photos as $index => $photo)
                                <button class="carousel-dot w-2 h-2 rounded-full {{ $index === 0 ? 'bg-white' : 'bg-white/50' }}"
                                        data-index="{{ $index }}"></button>
                                @endforeach
                            </div>
                            
                            <!-- Navigation -->
                            <button class="carousel-prev absolute left-2 top-1/2 transform -translate-y-1/2 bg-black/50 text-white p-1 rounded-full hover:bg-black/70">
                                ←
                            </button>
                            <button class="carousel-next absolute right-2 top-1/2 transform -translate-y-1/2 bg-black/50 text-white p-1 rounded-full hover:bg-black/70">
                                →
                            </button>
                            @endif
                            
                            <!-- Nombre de photos -->
                            <div class="absolute top-2 right-2 bg-black/50 text-white px-2 py-1 rounded text-sm">
                                {{ count($review->photos) }} photo{{ count($review->photos) > 1 ? 's' : '' }}
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Contenu de l'avis -->
                <div class="p-4">
                    <!-- Note globale -->
                    <div class="flex items-center justify-between mb-3">
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
                    
                    <!-- Commentaire -->
                    <p class="text-gray-700 text-sm mb-3 line-clamp-3">{{ $review->comment }}</p>
                    
                    <!-- Notes détaillées -->
                    @if($review->punctuality_rating || $review->quality_rating || $review->value_rating || $review->communication_rating)
                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 mb-3">
                        @if($review->punctuality_rating)
                        <div class="flex justify-between">
                            <span>Ponctualité:</span>
                            <span>{{ $review->punctuality_rating }}/5</span>
                        </div>
                        @endif
                        @if($review->quality_rating)
                        <div class="flex justify-between">
                            <span>Qualité:</span>
                            <span>{{ $review->quality_rating }}/5</span>
                        </div>
                        @endif
                        @if($review->value_rating)
                        <div class="flex justify-between">
                            <span>Rapport Q/P:</span>
                            <span>{{ $review->value_rating }}/5</span>
                        </div>
                        @endif
                        @if($review->communication_rating)
                        <div class="flex justify-between">
                            <span>Communication:</span>
                            <span>{{ $review->communication_rating }}/5</span>
                        </div>
                        @endif
                    </div>
                    @endif
                    
                    <!-- Métadonnées -->
                    <div class="flex justify-between items-center text-xs text-gray-500">
                        <span>{{ $review->client_name }}</span>
                        <span>{{ $review->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        @if($reviews->isEmpty())
        <div class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">Photos</div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Aucun avis avec photos</h3>
            <p class="text-gray-500">Ce prestataire n'a pas encore reçu d'avis avec photos.</p>
        </div>
        @endif
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
// Gestion des carrousels
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les carrousels
    document.querySelectorAll('.carousel').forEach(carousel => {
        const images = carousel.querySelectorAll('.carousel-image');
        const dots = carousel.querySelectorAll('.carousel-dot');
        const prevBtn = carousel.querySelector('.carousel-prev');
        const nextBtn = carousel.querySelector('.carousel-next');
        let currentIndex = 0;
        
        function showImage(index) {
            images.forEach((img, i) => {
                img.classList.toggle('opacity-100', i === index);
                img.classList.toggle('opacity-0', i !== index);
            });
            
            dots.forEach((dot, i) => {
                dot.classList.toggle('bg-white', i === index);
                dot.classList.toggle('bg-white/50', i !== index);
            });
            
            currentIndex = index;
        }
        
        // Navigation avec les boutons
        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const newIndex = currentIndex > 0 ? currentIndex - 1 : images.length - 1;
                showImage(newIndex);
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const newIndex = currentIndex < images.length - 1 ? currentIndex + 1 : 0;
                showImage(newIndex);
            });
        }
        
        // Navigation avec les points
        dots.forEach((dot, index) => {
            dot.addEventListener('click', (e) => {
                e.stopPropagation();
                showImage(index);
            });
        });
    });
    
    // Filtres
    const ratingFilter = document.getElementById('ratingFilter');
    const sortFilter = document.getElementById('sortFilter');
    const reviewsGrid = document.getElementById('reviewsGrid');
    
    function filterAndSort() {
        const cards = Array.from(reviewsGrid.querySelectorAll('.review-card'));
        const ratingValue = ratingFilter.value;
        const sortValue = sortFilter.value;
        
        // Filtrer par note
        cards.forEach(card => {
            const cardRating = card.dataset.rating;
            const shouldShow = !ratingValue || cardRating === ratingValue;
            card.style.display = shouldShow ? 'block' : 'none';
        });
        
        // Trier
        const visibleCards = cards.filter(card => card.style.display !== 'none');
        visibleCards.sort((a, b) => {
            switch(sortValue) {
                case 'oldest':
                    return parseInt(a.dataset.date) - parseInt(b.dataset.date);
                case 'rating_high':
                    return parseInt(b.dataset.rating) - parseInt(a.dataset.rating);
                case 'rating_low':
                    return parseInt(a.dataset.rating) - parseInt(b.dataset.rating);
                default: // recent
                    return parseInt(b.dataset.date) - parseInt(a.dataset.date);
            }
        });
        
        // Réorganiser les éléments
        visibleCards.forEach(card => reviewsGrid.appendChild(card));
    }
    
    ratingFilter.addEventListener('change', filterAndSort);
    sortFilter.addEventListener('change', filterAndSort);
});

// Modal pour les photos
function openPhotoModal(imageSrc, caption) {
    const modal = document.getElementById('photoModal');
    const modalImage = document.getElementById('modalImage');
    const modalCaption = document.getElementById('modalCaption');
    
    modalImage.src = imageSrc;
    modalCaption.textContent = caption;
    modal.classList.remove('hidden');
    
    // Fermer avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePhotoModal();
        }
    });
}

function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    modal.classList.add('hidden');
}

// Fermer le modal en cliquant à l'extérieur
document.getElementById('photoModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePhotoModal();
    }
});
</script>

<style>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.carousel-image {
    transition: opacity 0.3s ease-in-out;
}

.carousel-dot {
    transition: background-color 0.2s ease;
}

.review-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.review-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}
</style>
@endsection