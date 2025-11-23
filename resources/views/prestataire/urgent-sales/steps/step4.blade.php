@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 to-pink-100">
    <div class="max-w-6xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 py-4 sm:py-6 lg:py-8">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl font-bold text-red-900 mb-2">Créer une nouvelle annonce</h1>
                <p class="text-sm sm:text-base text-red-700 px-2 sm:px-0">Publiez votre équipement ou service à vendre</p>
            </div>
            
            <!-- Indicateur d'étapes -->
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl border border-red-200 p-4 sm:p-6 lg:p-8 mb-6 sm:mb-8">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        <a href="{{ route('prestataire.urgent-sales.index') }}" class="text-red-600 hover:text-red-900 transition-colors duration-200">
                            <i class="fas fa-arrow-left text-lg sm:text-xl"></i>
                        </a>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-red-900">Nouvelle annonce</h2>
                            <p class="text-sm sm:text-base text-red-700 hidden sm:block">Étape par étape</p>
                        </div>
                    </div>
                </div>
                
                <!-- Barre de progression -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-1 sm:space-x-2 lg:space-x-4 w-full overflow-x-auto">
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-red-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                1
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-red-600 hidden sm:inline">Informations</span>
                            <span class="ml-1 text-xs font-medium text-red-600 sm:hidden">Info</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 rounded min-w-4">
                            <div class="h-1 bg-red-600 rounded" style="width: 50%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-red-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                2
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-red-600 hidden sm:inline">Détails</span>
                            <span class="ml-1 text-xs font-medium text-red-600 sm:hidden">Détails</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 rounded min-w-4">
                            <div class="h-1 bg-red-600 rounded" style="width: 50%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-red-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                3
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-red-600 hidden sm:inline">Photos</span>
                            <span class="ml-1 text-xs font-medium text-red-600 sm:hidden">Photo</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 rounded min-w-4">
                            <div class="h-1 bg-red-600 rounded" style="width: 50%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-red-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                4
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-red-600 hidden sm:inline">Localisation</span>
                            <span class="ml-1 text-xs font-medium text-red-600 sm:hidden">Lieu</span>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-400 p-3 sm:p-4 mb-4 sm:mb-6 rounded-r-lg" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Veuillez corriger les erreurs suivantes :</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Étape 4: Révision et publication -->
            <div class="bg-white rounded-xl shadow-lg border border-red-200 p-3 sm:p-4 md:p-6 mb-4 sm:mb-6">
                <div class="flex items-center mb-3 sm:mb-4">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-red-600 text-white flex items-center justify-center text-xs sm:text-sm font-bold mr-2 sm:mr-3">
                        4
                    </div>
                    <h2 class="text-base sm:text-lg md:text-xl font-bold text-red-900">Révision et publication</h2>
                </div>
                
                <form id="urgentSaleStep4Form" action="{{ route('prestataire.urgent-sales.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="terms" value="1">
                    <input type="hidden" name="contact" value="1">
                    <div class="space-y-4 sm:space-y-6">
                        <!-- Message de confirmation -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 text-lg sm:text-xl mr-2 sm:mr-3"></i>
                                <div>
                                    <h3 class="text-green-800 font-semibold text-sm sm:text-base">Votre annonce est prête !</h3>
                                    <p class="text-green-700 text-xs sm:text-sm mt-1">Vérifiez les informations ci-dessous avant de publier.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Résumé des informations -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                            <!-- Informations principales -->
                            <div class="space-y-3 sm:space-y-4">
                                <h3 class="text-base sm:text-lg font-semibold text-red-900 border-b border-red-200 pb-2">
                                    <i class="fas fa-info-circle mr-1 sm:mr-2 text-sm sm:text-base"></i>Informations principales
                                </h3>
                                
                                <div class="space-y-2 sm:space-y-3">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1 sm:gap-4">
                                        <span class="text-xs sm:text-sm font-medium text-gray-600 flex-shrink-0">Titre :</span>
                                        <span id="review-title" class="text-xs sm:text-sm text-gray-900 font-medium break-words">{{ $step1Data['title'] ?? '-' }}</span>
                                    </div>
                                    
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1 sm:gap-4">
                                        <span class="text-xs sm:text-sm font-medium text-gray-600 flex-shrink-0">Prix :</span>
                                        <span id="review-price" class="text-xs sm:text-sm text-red-600 font-bold">{{ isset($step1Data['price']) ? number_format($step1Data['price'], 2, ',', ' ') . ' €' : '-' }}</span>
                                    </div>
                                    
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1 sm:gap-4">
                                        <span class="text-xs sm:text-sm font-medium text-gray-600 flex-shrink-0">État :</span>
                                        <span id="review-condition" class="text-xs sm:text-sm text-gray-900">
                                            @php
                                                $conditionLabels = [
                                                    'excellent' => 'Excellent',
                                                    'very_good' => 'Très bon',
                                                    'good' => 'Bon état',
                                                    'fair' => 'État correct',
                                                    'poor' => 'Mauvais état'
                                                ];
                                            @endphp
                                            {{ $conditionLabels[$step1Data['condition']] ?? $step1Data['condition'] ?? '-' }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1 sm:gap-4">
                                        <span class="text-xs sm:text-sm font-medium text-gray-600 flex-shrink-0">Quantité :</span>
                                        <span id="review-quantity" class="text-xs sm:text-sm text-gray-900">{{ $step1Data['quantity'] ?? '1' }}</span>
                                    </div>
                                    
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1 sm:gap-4">
                                        <span class="text-xs sm:text-sm font-medium text-gray-600 flex-shrink-0">Catégorie :</span>
                                        <span id="review-category" class="text-xs sm:text-sm text-gray-900 break-words">{{ $category->name ?? 'Non spécifiée' }}</span>
                                    </div>
                                    
                                    @if($subcategory)
                                    <div id="review-subcategory-container" class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1 sm:gap-4">
                                        <span class="text-xs sm:text-sm font-medium text-gray-600 flex-shrink-0">Sous-catégorie :</span>
                                        <span id="review-subcategory" class="text-xs sm:text-sm text-gray-900 break-words">{{ $subcategory->name ?? '-' }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Localisation -->
                            <div class="space-y-3 sm:space-y-4">
                                <h3 class="text-base sm:text-lg font-semibold text-red-900 border-b border-red-200 pb-2">
                                    <i class="fas fa-map-marker-alt mr-1 sm:mr-2 text-sm sm:text-base"></i>Localisation
                                </h3>
                                
                                <div class="space-y-2 sm:space-y-3">
                                    <div>
                                        <span class="text-xs sm:text-sm font-medium text-gray-600 block mb-1">Adresse :</span>
                                        <span id="review-location" class="text-xs sm:text-sm text-gray-900 block break-words">{{ $step2Data['location'] ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div class="space-y-3 sm:space-y-4">
                            <h3 class="text-base sm:text-lg font-semibold text-red-900 border-b border-red-200 pb-2">
                                <i class="fas fa-align-left mr-1 sm:mr-2 text-sm sm:text-base"></i>Description
                            </h3>
                            
                            <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
                                <p id="review-description" class="text-xs sm:text-sm text-gray-900 whitespace-pre-wrap break-words">{{ $step3Data['description'] ?? '-' }}</p>
                            </div>
                        </div>
                        
                        <!-- Photos -->
                        <div class="space-y-3 sm:space-y-4">
                            <h3 class="text-base sm:text-lg font-semibold text-red-900 border-b border-red-200 pb-2">
                                <i class="fas fa-images mr-1 sm:mr-2 text-sm sm:text-base"></i>Photos
                            </h3>
                            
                            <div id="review-photos" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 sm:gap-4">
                                @if(isset($step3Data['temp_image_paths']) && count($step3Data['temp_image_paths']) > 0)
                                    @foreach($step3Data['temp_image_paths'] as $index => $photoPath)
                                        <div class="relative">
                                            <img src="{{ asset('storage/' . $photoPath) }}" alt="Photo {{ $index + 1 }}" class="w-full h-24 object-cover rounded-lg">
                                            <div class="absolute bottom-1 left-1 bg-black bg-opacity-50 text-white text-xs px-1 rounded">
                                                Photo {{ $index + 1 }}
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-6 sm:py-8 text-gray-500 col-span-full">
                                        <i class="fas fa-image text-2xl sm:text-3xl mb-2"></i>
                                        <p class="text-xs sm:text-sm">Aucune photo ajoutée</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Actions de modification -->
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3 sm:p-4">
                            <h4 class="text-xs sm:text-sm font-semibold text-red-800 mb-2 sm:mb-3">
                                <i class="fas fa-edit mr-1 sm:mr-2 text-xs sm:text-sm"></i>Besoin de modifier quelque chose ?
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                <button type="button" onclick="goToStep(1)" class="text-xs bg-white border border-red-300 text-red-700 px-2 sm:px-3 py-2 rounded-md hover:bg-red-50 transition-colors">
                                    <i class="fas fa-info-circle mr-1 text-xs"></i><span class="hidden sm:inline">Informations</span><span class="sm:hidden">Info</span>
                                </button>
                                <button type="button" onclick="goToStep(2)" class="text-xs bg-white border border-red-300 text-red-700 px-2 sm:px-3 py-2 rounded-md hover:bg-red-50 transition-colors">
                                    <i class="fas fa-map-marker-alt mr-1 text-xs"></i><span class="hidden sm:inline">Localisation</span><span class="sm:hidden">Lieu</span>
                                </button>
                                <button type="button" onclick="goToStep(3)" class="text-xs bg-white border border-red-300 text-red-700 px-2 sm:px-3 py-2 rounded-md hover:bg-red-50 transition-colors">
                                    <i class="fas fa-align-left mr-1 text-xs"></i><span class="hidden sm:inline">Description</span><span class="sm:hidden">Desc</span>
                                </button>
                                <button type="button" onclick="goToStep(3)" class="text-xs bg-white border border-red-300 text-red-700 px-2 sm:px-3 py-2 rounded-md hover:bg-red-50 transition-colors">
                                    <i class="fas fa-images mr-1 text-xs"></i>Photos
                                </button>
                            </div>
                        </div>
                        
                        <!-- Conditions et publication -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 sm:p-4">
                            <h4 class="text-xs sm:text-sm font-semibold text-yellow-800 mb-2 sm:mb-3">
                                <i class="fas fa-exclamation-triangle mr-1 sm:mr-2 text-xs sm:text-sm"></i>Avant de publier
                            </h4>
                            <div class="space-y-2 sm:space-y-3">
                                <label class="flex items-start space-x-2 sm:space-x-3">
                                    <input type="checkbox" id="terms-checkbox" class="mt-0.5 sm:mt-1 rounded border-yellow-300 text-red-600 focus:ring-red-500 flex-shrink-0" required>
                                    <span class="text-xs sm:text-sm text-yellow-800">
                                        Je certifie que les informations fournies sont exactes et que je respecte les 
                                        <a href="#" class="text-red-600 hover:text-red-800 underline">conditions d'utilisation</a>
                                    </span>
                                </label>
                                
                                <label class="flex items-start space-x-2 sm:space-x-3">
                                    <input type="checkbox" id="contact-checkbox" class="mt-0.5 sm:mt-1 rounded border-yellow-300 text-red-600 focus:ring-red-500 flex-shrink-0" required>
                                    <span class="text-xs sm:text-sm text-yellow-800">
                                        J'accepte d'être contacté par les acheteurs intéressés
                                    </span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="flex justify-between mt-6 sm:mt-8">
                            <a href="{{ route('prestataire.urgent-sales.create.step3') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-sm sm:text-base">
                                <i class="fas fa-arrow-left mr-1 sm:mr-2"></i>Précédent
                            </a>
                            <button type="submit" id="final-publish-btn" class="bg-red-600 hover:bg-red-700 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-sm sm:text-base flex items-center">
                                <i class="fas fa-paper-plane mr-1 sm:mr-2"></i>Publier l'annonce
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Fonction pour aller à une étape spécifique
function goToStep(step) {
    // Redirect to the appropriate step
    switch(step) {
        case 1:
            window.location.href = "{{ route('prestataire.urgent-sales.create.step1') }}";
            break;
        case 2:
            window.location.href = "{{ route('prestataire.urgent-sales.create.step2') }}";
            break;
        case 3:
            window.location.href = "{{ route('prestataire.urgent-sales.create.step3') }}";
            break;
    }
}

// Prevent form resubmission
document.getElementById('urgentSaleStep4Form').addEventListener('submit', function() {
    const submitBtn = document.getElementById('final-publish-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Publication en cours...';
});
</script>
@endpush