@extends('layouts.app')

@section('title', 'Avis clients')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- En-tête avec statistiques -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Avis clients</h1>
                    <p class="text-gray-600">{{ $stats['total_reviews'] }} avis au total</p>
                </div>
                
                <div class="mt-4 lg:mt-0">
                    <div class="flex items-center space-x-6">
                        <!-- Note moyenne -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-900">{{ number_format($stats['average_rating'], 1) }}</div>
                            <div class="flex items-center justify-center mt-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= round($stats['average_rating']) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            <div class="text-sm text-gray-500 mt-1">Note moyenne</div>
                        </div>
                        
                        <!-- Statistiques détaillées -->
                        @if($stats['detailed_averages'])
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="text-center">
                                <div class="font-semibold text-gray-900">{{ number_format($stats['detailed_averages']['punctuality'], 1) }}/5</div>
                                <div class="text-gray-500">Ponctualité</div>
                            </div>
                            <div class="text-center">
                                <div class="font-semibold text-gray-900">{{ number_format($stats['detailed_averages']['quality'], 1) }}/5</div>
                                <div class="text-gray-500">Qualité</div>
                            </div>
                            <div class="text-center">
                                <div class="font-semibold text-gray-900">{{ number_format($stats['detailed_averages']['value'], 1) }}/5</div>
                                <div class="text-gray-500">Rapport Q/P</div>
                            </div>
                            <div class="text-center">
                                <div class="font-semibold text-gray-900">{{ number_format($stats['detailed_averages']['communication'], 1) }}/5</div>
                                <div class="text-gray-500">Communication</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Filtres -->
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('reviews.index', ['prestataire_id' => $prestataireId]) }}" 
                   class="px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium hover:bg-blue-200 transition-colors">
                    Tous les avis
                </a>
                <a href="{{ route('reviews.with-photos', ['prestataire_id' => $prestataireId]) }}" 
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full text-sm font-medium hover:bg-gray-200 transition-colors">
                    Avec photos ({{ $stats['reviews_with_photos'] }})
                </a>
                <a href="{{ route('reviews.certificates', ['prestataire_id' => $prestataireId]) }}" 
                   class="px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-medium hover:bg-green-200 transition-colors">
                    Certificats de satisfaction
                </a>
            </div>
        </div>
        
        <!-- Liste des avis -->
        <div class="space-y-6">
            @forelse($reviews as $review)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <!-- Avatar du client -->
                            <div class="w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center">
                                <span class="text-gray-600 font-medium">
                                    {{ strtoupper(substr($review->client_name, 0, 1)) }}
                                </span>
                            </div>
                            
                            <div class="flex-1">
                                <!-- Nom et date -->
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="font-medium text-gray-900">{{ $review->client_name }}</h3>
                                    @if($review->verified)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Vérifié
                                        </span>
                                    @endif
                                    <span class="text-sm text-gray-500">{{ $review->created_at->format('d/m/Y') }}</span>
                                </div>
                                
                                <!-- Note globale -->
                                <div class="flex items-center space-x-2 mb-3">
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-5 h-5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $review->rating }}/5</span>
                                </div>
                                
                                <!-- Notes détaillées -->
                                @if($review->hasDetailedRatings())
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                                    @if($review->punctuality_rating)
                                    <div class="text-sm">
                                        <div class="text-gray-600">Ponctualité</div>
                                        <div class="font-medium">{{ $review->punctuality_rating }}/5</div>
                                    </div>
                                    @endif
                                    @if($review->quality_rating)
                                    <div class="text-sm">
                                        <div class="text-gray-600">Qualité</div>
                                        <div class="font-medium">{{ $review->quality_rating }}/5</div>
                                    </div>
                                    @endif
                                    @if($review->value_rating)
                                    <div class="text-sm">
                                        <div class="text-gray-600">Rapport Q/P</div>
                                        <div class="font-medium">{{ $review->value_rating }}/5</div>
                                    </div>
                                    @endif
                                    @if($review->communication_rating)
                                    <div class="text-sm">
                                        <div class="text-gray-600">Communication</div>
                                        <div class="font-medium">{{ $review->communication_rating }}/5</div>
                                    </div>
                                    @endif
                                </div>
                                @endif
                                
                                <!-- Commentaire -->
                                @if($review->comment)
                                <p class="text-gray-700 mb-4">{{ $review->comment }}</p>
                                @endif
                                
                                <!-- Photos -->
                                @if($review->hasPhotos())
                                <div class="mb-4">
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        @foreach($review->photos as $photo)
                                        <div class="relative group cursor-pointer" onclick="openImageModal('{{ $photo }}')">
                                            <img src="{{ asset('storage/' . $photo) }}" alt="Photo de l'avis" 
                                                 class="w-full h-24 object-cover rounded-lg hover:opacity-90 transition-opacity">
                                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all rounded-lg flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                                </svg>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1l-4 4z"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun avis pour le moment</h3>
                    <p class="text-gray-500">Soyez le premier à laisser un avis sur ce prestataire.</p>
                </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        @if($reviews->hasPages())
        <div class="mt-8">
            {{ $reviews->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal pour les images -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-full">
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg">
    </div>
</div>

<script>
function openImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Fermer le modal en cliquant en dehors de l'image
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});

// Fermer le modal avec la touche Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});
</script>
@endsection