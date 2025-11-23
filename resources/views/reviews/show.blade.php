@extends('layouts.app')

@section('title', 'Détail de l\'avis - ' . $review->prestataire->nom)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Navigation -->
        <div class="mb-6">
            <a href="{{ route('reviews.index', $review->prestataire_id) }}" 
               class="inline-flex items-center text-blue-600 hover:text-blue-800 transition duration-200">
                ← Retour aux avis
            </a>
        </div>

        <!-- Carte principale de l'avis -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- En-tête -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">Avis détaillé</h1>
                        <p class="text-blue-100 mt-1">{{ $review->prestataire->nom }}</p>
                    </div>
                    @if($review->verified)
                    <div class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                        Avis vérifié
                    </div>
                    @endif
                </div>
            </div>

            <!-- Contenu principal -->
            <div class="p-6">
                <!-- Informations de base -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Note globale -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Note globale</h3>
                        <div class="flex items-center">
                            <div class="flex text-3xl">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="{{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}">*</span>
                                @endfor
                            </div>
                            <span class="ml-3 text-2xl font-bold text-gray-800">{{ $review->rating }}/5</span>
                        </div>
                    </div>

                    <!-- Métadonnées -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Informations</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Client:</span>
                                <span class="font-medium">{{ $review->client_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Date:</span>
                                <span class="font-medium">{{ $review->created_at->format('d/m/Y à H:i') }}</span>
                            </div>
                            @if($review->booking_id)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Réservation:</span>
                                <span class="font-medium">#{{ $review->booking_id }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600">Statut:</span>
                                <span class="px-2 py-1 rounded text-xs font-medium
                                    @if($review->status === 'approved') bg-green-100 text-green-800
                                    @elseif($review->status === 'rejected') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    @if($review->status === 'approved') Approuvé
                                    @elseif($review->status === 'rejected') Rejeté
                                    @else En attente @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes détaillées -->
                @if($review->punctuality_rating || $review->quality_rating || $review->value_rating || $review->communication_rating)
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Évaluation détaillée</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($review->punctuality_rating)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-medium text-gray-700">Ponctualité</span>
                                <span class="text-lg font-bold text-gray-800">{{ $review->punctuality_rating }}/5</span>
                            </div>
                            <div class="flex">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="text-lg {{ $i <= $review->punctuality_rating ? 'text-yellow-400' : 'text-gray-300' }}">*</span>
                                @endfor
                            </div>
                        </div>
                        @endif

                        @if($review->quality_rating)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-medium text-gray-700">Qualité du service</span>
                                <span class="text-lg font-bold text-gray-800">{{ $review->quality_rating }}/5</span>
                            </div>
                            <div class="flex">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="text-lg {{ $i <= $review->quality_rating ? 'text-yellow-400' : 'text-gray-300' }}">*</span>
                                @endfor
                            </div>
                        </div>
                        @endif

                        @if($review->value_rating)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-medium text-gray-700">Rapport qualité/prix</span>
                                <span class="text-lg font-bold text-gray-800">{{ $review->value_rating }}/5</span>
                            </div>
                            <div class="flex">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="text-lg {{ $i <= $review->value_rating ? 'text-yellow-400' : 'text-gray-300' }}">*</span>
                                @endfor
                            </div>
                        </div>
                        @endif

                        @if($review->communication_rating)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-medium text-gray-700">Communication</span>
                                <span class="text-lg font-bold text-gray-800">{{ $review->communication_rating }}/5</span>
                            </div>
                            <div class="flex">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="text-lg {{ $i <= $review->communication_rating ? 'text-yellow-400' : 'text-gray-300' }}">*</span>
                                @endfor
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Commentaire -->
                @if($review->comment)
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Commentaire</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-700 leading-relaxed">{{ $review->comment }}</p>
                    </div>
                </div>
                @endif

                <!-- Photos -->
                @if($review->photos && count($review->photos) > 0)
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Photos ({{ count($review->photos) }})</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($review->photos as $index => $photo)
                        <div class="relative group cursor-pointer" 
                             onclick="openPhotoModal('{{ asset('storage/' . $photo) }}', 'Photo {{ $index + 1 }}')">
                                <img src="{{ asset('storage/' . $photo) }}" 
                                 alt="Photo {{ $index + 1 }}"
                                 class="w-full h-32 object-cover rounded-lg shadow-md group-hover:shadow-lg transition duration-200">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-lg transition duration-200 flex items-center justify-center">
                                <span class="text-white opacity-0 group-hover:opacity-100 transition duration-200">
                                    Rechercher
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="border-t pt-6">
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('reviews.index', $review->prestataire_id) }}" 
                           class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition duration-200">
                            Voir tous les avis
                        </a>
                        
                        @if($review->photos && count($review->photos) > 0)
                        <a href="{{ route('reviews.with-photos', $review->prestataire_id) }}" 
                           class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-2 rounded-lg transition duration-200">
                            Avis avec photos
                        </a>
                        @endif
                        
                        <a href="{{ route('reviews.certificates', $review->prestataire_id) }}" 
                           class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition duration-200">
                            Certificats de satisfaction
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avis similaires -->
        @if($similarReviews && $similarReviews->count() > 0)
        <div class="mt-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Autres avis récents</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($similarReviews as $similarReview)
                <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition duration-200">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="text-sm {{ $i <= $similarReview->rating ? 'text-yellow-400' : 'text-gray-300' }}">*</span>
                            @endfor
                            <span class="ml-2 text-sm text-gray-600">{{ $similarReview->rating }}/5</span>
                        </div>
                        @if($similarReview->verified)
                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Vérifié</span>
                        @endif
                    </div>
                    
                    @if($similarReview->comment)
                    <p class="text-gray-700 text-sm mb-2 line-clamp-2">{{ Str::limit($similarReview->comment, 100) }}</p>
                    @endif
                    
                    <div class="flex justify-between items-center text-xs text-gray-500">
                        <span>{{ $similarReview->client->nom ?? 'Client anonyme' }}</span>
                        <span>{{ $similarReview->created_at->format('d/m/Y') }}</span>
                    </div>
                    
                    <a href="{{ route('reviews.show', $similarReview->id) }}" 
                       class="inline-block mt-2 text-blue-600 hover:text-blue-800 text-sm transition duration-200">
                        Voir le détail →
                    </a>
                </div>
                @endforeach
            </div>
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
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection