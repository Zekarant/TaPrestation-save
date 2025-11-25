@extends('layouts.app')

@section('title', 'Laisser un avis')

@section('content')
@php
    // Get booking and service information
    $booking = $bookingId ? \App\Models\Booking::find($bookingId) : null;
    $prestataire = $prestataireId ? \App\Models\Prestataire::find($prestataireId) : null;
@endphp

<div class="bg-blue-50 min-h-screen">
    <div class="container mx-auto px-2 sm:px-4 py-4 sm:py-6">
        <div class="max-w-5xl mx-auto">
            <!-- Header -->
            <div class="mb-4 sm:mb-6 text-center">
                <h1 class="text-xl sm:text-2xl font-extrabold text-blue-900 mb-1">Laisser un avis</h1>
                @if($booking)
                    <p class="text-sm sm:text-base text-blue-700">{{ $booking->service->name }}</p>
                    <p class="text-xs text-blue-600 font-medium mt-1">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        {{ $booking->start_datetime->format('d/m/Y à H:i') }}
                    </p>
                @elseif($prestataire)
                    <p class="text-sm sm:text-base text-blue-700">{{ $prestataire->user->name }}</p>
                @endif
            </div>

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 p-4 rounded-xl shadow-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-green-800 font-medium text-base">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 p-4 rounded-xl shadow-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-red-800 font-medium text-base">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 p-4 rounded-xl shadow-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-red-800 font-bold text-lg mb-2">Erreurs détectées :</h3>
                            <ul class="list-disc list-inside text-red-700 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li class="text-base">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-4 sm:mb-6">
                <!-- Review Form Card -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow border border-blue-200 p-3 sm:p-4">
                        <form action="{{ route('reviews.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 sm:space-y-5">
                            @csrf
                            
                            <input type="hidden" name="prestataire_id" value="{{ $prestataireId }}">
                            @if($bookingId)
                                <input type="hidden" name="booking_id" value="{{ $bookingId }}">
                            @endif
                            
                            <!-- Note globale -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Note globale *</label>
                                <div class="flex items-center space-x-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="rating" value="{{ $i }}" class="sr-only rating-input" required>
                                            <svg class="w-8 h-8 text-gray-300 hover:text-yellow-400 transition-colors star-icon" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </label>
                                    @endfor
                                </div>
                                <div class="text-sm text-gray-500 mt-1" id="rating-label">Sélectionnez une note</div>
                                @error('rating')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Notation multi-critères -->
                            <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                                <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4">Évaluation détaillée</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                                    <!-- Ponctualité -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Ponctualité</label>
                                        <div class="flex items-center space-x-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="punctuality_rating" value="{{ $i }}" class="sr-only criteria-rating" data-criteria="punctuality">
                                                    <svg class="w-6 h-6 text-gray-300 hover:text-yellow-400 transition-colors criteria-star" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                    
                                    <!-- Qualité du service -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Qualité du service</label>
                                        <div class="flex items-center space-x-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="quality_rating" value="{{ $i }}" class="sr-only criteria-rating" data-criteria="quality">
                                                    <svg class="w-6 h-6 text-gray-300 hover:text-yellow-400 transition-colors criteria-star" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                    
                                    <!-- Rapport qualité/prix -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Rapport qualité/prix</label>
                                        <div class="flex items-center space-x-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="value_rating" value="{{ $i }}" class="sr-only criteria-rating" data-criteria="value">
                                                    <svg class="w-6 h-6 text-gray-300 hover:text-yellow-400 transition-colors criteria-star" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                    
                                    <!-- Communication -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Communication</label>
                                        <div class="flex items-center space-x-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="communication_rating" value="{{ $i }}" class="sr-only criteria-rating" data-criteria="communication">
                                                    <svg class="w-6 h-6 text-gray-300 hover:text-yellow-400 transition-colors criteria-star" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Commentaire -->
                            <div>
                                <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Commentaire</label>
                                <textarea name="comment" id="comment" rows="4" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Partagez votre expérience avec ce prestataire...">{{ old('comment') }}</textarea>
                                @error('comment')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Photos -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Photos (optionnel)</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 sm:p-6 text-center hover:border-gray-400 transition-colors">
                                    <input type="file" name="photos[]" id="photos" multiple accept="image/*" class="hidden" onchange="previewImages(this)">
                                    <label for="photos" class="cursor-pointer">
                                        <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="mt-2">
                                            <span class="text-blue-600 font-medium">Cliquez pour ajouter des photos</span>
                                            <p class="text-gray-500 text-xs sm:text-sm mt-1">PNG, JPG, GIF jusqu'à 2MB chacune</p>
                                        </div>
                                    </label>
                                </div>
                                <div id="image-preview" class="mt-3 sm:mt-4 grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4"></div>
                                @error('photos.*')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Boutons -->
                            <div class="flex justify-between pt-4 sm:pt-6">
                                <a href="{{ url()->previous() }}" class="px-4 py-2 text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors text-sm sm:text-base">
                                    <i class="fas fa-arrow-left mr-1"></i> Annuler
                                </a>
                                <button type="submit" class="px-4 py-2 sm:px-6 sm:py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors shadow hover:shadow-md text-sm sm:text-base">
                                    <i class="fas fa-paper-plane mr-1"></i> Publier l'avis
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Right Column: Booking/Prestataire Info -->
                <div class="space-y-4 sm:space-y-6">
                    <!-- Booking/Prestataire Info Card -->
                    <div class="bg-white rounded-xl shadow border border-blue-200 p-3 sm:p-4">
                        @if($booking)
                            <div class="text-center mb-4">
                                <h2 class="text-base sm:text-lg font-bold text-blue-800 flex items-center justify-center mb-3">
                                    <i class="fas fa-calendar-alt text-blue-500 mr-1.5"></i>
                                    Détails de la réservation
                                </h2>
                            </div>
                            
                            <div class="space-y-3">
                                <!-- Booking Number -->
                                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                    <div class="flex items-center">
                                        <i class="fas fa-hashtag text-blue-500 mr-2 w-4 text-sm"></i>
                                        <span class="text-gray-600 text-xs sm:text-sm">Réservation</span>
                                    </div>
                                    <span class="font-medium text-gray-900 text-xs sm:text-sm">{{ $booking->booking_number }}</span>
                                </div>
                                
                                <!-- Date and time -->
                                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar text-blue-500 mr-2 w-4 text-sm"></i>
                                        <span class="text-gray-600 text-xs sm:text-sm">Date</span>
                                    </div>
                                    <span class="font-medium text-gray-900 text-xs sm:text-sm">
                                        {{ $booking->start_datetime->format('d/m/Y à H:i') }}
                                    </span>
                                </div>
                                
                                <!-- Duration -->
                                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                    <div class="flex items-center">
                                        <i class="fas fa-clock text-green-500 mr-2 w-4 text-sm"></i>
                                        <span class="text-gray-600 text-xs sm:text-sm">Durée</span>
                                    </div>
                                    <span class="font-medium text-gray-900 text-xs sm:text-sm">{{ $booking->getDurationFormatted() }}</span>
                                </div>
                                
                                <!-- Price -->
                                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                    <div class="flex items-center">
                                        <i class="fas fa-euro-sign text-yellow-500 mr-2 w-4 text-sm"></i>
                                        <span class="text-gray-600 text-xs sm:text-sm">Prix</span>
                                    </div>
                                    <span class="font-bold text-green-600 text-xs sm:text-sm">{{ number_format($booking->total_price, 2) }} €</span>
                                </div>
                                
                                <!-- Status -->
                                <div class="flex items-center justify-between py-2">
                                    <div class="flex items-center">
                                        <i class="fas fa-tasks text-purple-500 mr-2 w-4 text-sm"></i>
                                        <span class="text-gray-600 text-xs sm:text-sm">Statut</span>
                                    </div>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $booking->getStatusBadgeClass() }}">
                                        {{ $booking->getStatusLabel() }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Service info -->
                            <div class="mt-4 pt-4 border-t border-gray-100 text-center">
                                <h3 class="text-sm font-semibold text-blue-800 mb-2 flex items-center justify-center">
                                    <i class="fas fa-cogs text-blue-500 mr-1.5"></i>
                                    Service
                                </h3>
                                <div class="bg-blue-50 rounded-lg p-3 text-center">
                                    <h4 class="font-medium text-blue-900 text-sm">{{ $booking->service->name }}</h4>
                                    @if($booking->service->description)
                                        <p class="text-blue-700 mt-2 text-xs">{{ Str::limit($booking->service->description, 100) }}</p>
                                    @endif
                                </div>
                            </div>
                        @elseif($prestataire)
                            <div class="text-center">
                                <h2 class="text-base sm:text-lg font-bold text-blue-800 flex items-center justify-center mb-3">
                                    <i class="fas fa-user-tie text-blue-500 mr-1.5"></i>
                                    Prestataire
                                </h2>
                            </div>
                            
                            <div class="flex flex-col items-center space-y-3">
                                <div class="relative flex-shrink-0">
                                    @if($prestataire->photo)
                                        <img src="{{ asset('storage/' . $prestataire->photo) }}" 
                                             alt="{{ $prestataire->user->name }}" 
                                             class="w-16 h-16 rounded-full object-cover border-2 border-blue-200">
                                    @elseif($prestataire->user->avatar)
                                        <img src="{{ asset('storage/' . $prestataire->user->avatar) }}" 
                                             alt="{{ $prestataire->user->name }}" 
                                             class="w-16 h-16 rounded-full object-cover border-2 border-blue-200">
                                    @else
                                        <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center border-2 border-blue-300">
                                            <span class="text-white font-bold text-xl">{{ substr($prestataire->user->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    @if($prestataire->isVerified())
                                        <div class="absolute -top-1 -right-1 w-5 h-5 bg-green-500 rounded-full flex items-center justify-center border-2 border-white">
                                            <i class="fas fa-check text-white text-xs"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-center">
                                    <div class="flex items-center justify-center space-x-1.5">
                                        <h3 class="text-sm font-medium text-gray-900">{{ $prestataire->user->name }}</h3>
                                        @if($prestataire->isVerified())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check mr-0.5"></i>Vérifié
                                            </span>
                                        @endif
                                    </div>
                                    @if($prestataire->location)
                                        <p class="text-gray-500 mt-1.5">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            {{ $prestataire->location }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Gestion des étoiles pour la note globale
const ratingInputs = document.querySelectorAll('.rating-input');
const starIcons = document.querySelectorAll('.star-icon');
const ratingLabel = document.getElementById('rating-label');

const ratingLabels = {
    1: 'Très mauvais',
    2: 'Mauvais', 
    3: 'Correct',
    4: 'Bon',
    5: 'Excellent'
};

ratingInputs.forEach((input, index) => {
    input.addEventListener('change', function() {
        const rating = parseInt(this.value);
        ratingLabel.textContent = ratingLabels[rating];
        
        starIcons.forEach((star, starIndex) => {
            if (starIndex < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    });
});

// Gestion des étoiles pour les critères
const criteriaInputs = document.querySelectorAll('.criteria-rating');
criteriaInputs.forEach(input => {
    input.addEventListener('change', function() {
        const criteria = this.dataset.criteria;
        const rating = parseInt(this.value);
        const criteriaStars = this.closest('div').querySelectorAll('.criteria-star');
        
        criteriaStars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    });
});

// Prévisualisation des images
function previewImages(input) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-20 sm:h-24 object-cover rounded-lg">
                        <button type="button" onclick="removeImage(this, ${index})" 
                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 sm:w-6 sm:h-6 flex items-center justify-center text-xs hover:bg-red-600">
                            ×
                        </button>
                    `;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

function removeImage(button, index) {
    const input = document.getElementById('photos');
    const dt = new DataTransfer();
    
    Array.from(input.files).forEach((file, i) => {
        if (i !== index) {
            dt.items.add(file);
        }
    });
    
    input.files = dt.files;
    button.closest('div').remove();
}

// Auto-hide flash messages after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const flashMessages = document.querySelectorAll('.bg-green-50, .bg-red-50');
    flashMessages.forEach(function(message) {
        setTimeout(function() {
            message.style.transition = 'opacity 0.5s ease-out';
            message.style.opacity = '0';
            setTimeout(function() {
                message.remove();
            }, 500);
        }, 5000);
    });
});
</script>
@endsection