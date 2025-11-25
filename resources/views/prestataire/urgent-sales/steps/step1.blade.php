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
                            <div class="h-1 bg-red-600 rounded" style="width: 25%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                2
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-gray-500 hidden sm:inline">Détails</span>
                            <span class="ml-1 text-xs font-medium text-gray-500 sm:hidden">Détails</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 rounded min-w-4">
                            <div class="h-1 bg-gray-200 rounded" style="width: 0%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                3
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-gray-500 hidden sm:inline">Photos</span>
                            <span class="ml-1 text-xs font-medium text-gray-500 sm:hidden">Photo</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 rounded min-w-4">
                            <div class="h-1 bg-gray-200 rounded" style="width: 0%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                4
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-gray-500 hidden sm:inline">Localisation</span>
                            <span class="ml-1 text-xs font-medium text-gray-500 sm:hidden">Lieu</span>
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

            <!-- Étape 1: Informations de base -->
            <div class="bg-white rounded-xl shadow-lg border border-red-200 p-3 sm:p-4 md:p-6 mb-4 sm:mb-6">
                <div class="flex items-center mb-3 sm:mb-4">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-red-600 text-white flex items-center justify-center text-xs sm:text-sm font-bold mr-2 sm:mr-3">
                        1
                    </div>
                    <h2 class="text-base sm:text-lg md:text-xl font-bold text-red-900">Informations de base</h2>
                </div>
                
                <form id="urgentSaleStep1Form" action="{{ route('prestataire.urgent-sales.create.step1.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 md:gap-6 lg:gap-8">
                        <!-- Titre -->
                        <div class="md:col-span-2">
                            <label for="title" class="block text-xs sm:text-sm font-medium text-red-700 mb-1 sm:mb-2">Titre de la vente *</label>
                            <input type="text" id="title" name="title" value="{{ old('title') }}" required class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 @error('title') border-red-500 @enderror">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mt-1 gap-1 sm:gap-2">
                                <div class="flex-1">
                                    @error('title')
                                        <p class="text-red-500 text-xs sm:text-sm">{{ $message }}</p>
                                    @enderror
                                    <p class="text-red-600 text-xs">Soyez précis et descriptif</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Prix -->
                        <div>
                            <label for="price" class="block text-xs sm:text-sm font-medium text-red-700 mb-1 sm:mb-2">Prix (€) *</label>
                            <input type="number" id="price" name="price" value="{{ old('price') }}" required min="0" step="0.01" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 @error('price') border-red-500 @enderror">
                            @error('price')
                                <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- État -->
                        <div>
                            <label for="condition" class="block text-xs sm:text-sm font-medium text-red-700 mb-1 sm:mb-2">État *</label>
                            <select id="condition" name="condition" required class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 @error('condition') border-red-500 @enderror">
                                <option value="">Sélectionner l'état</option>
                                <option value="excellent" {{ old('condition') === 'excellent' ? 'selected' : '' }}>Excellent</option>
                                <option value="very_good" {{ old('condition') === 'very_good' ? 'selected' : '' }}>Très bon</option>
                                <option value="good" {{ old('condition') === 'good' ? 'selected' : '' }}>Bon état</option>
                                <option value="fair" {{ old('condition') === 'fair' ? 'selected' : '' }}>État correct</option>
                                <option value="poor" {{ old('condition') === 'poor' ? 'selected' : '' }}>Mauvais état</option>
                            </select>
                            @error('condition')
                                <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Catégorie principale -->
                        <div>
                            <label for="parent_category_id" class="block text-xs sm:text-sm font-medium text-red-700 mb-1 sm:mb-2">Catégorie principale *</label>
                            <select id="parent_category_id" name="parent_category_id" required class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 @error('parent_category_id') border-red-500 @enderror">
                                <option value="">Choisissez une catégorie principale</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('parent_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('parent_category_id')
                                <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Sous-catégorie -->
                        <div>
                            <label for="category_id" class="block text-xs sm:text-sm font-medium text-red-700 mb-1 sm:mb-2">Sous-catégorie</label>
                            <select id="category_id" name="category_id" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 @error('category_id') border-red-500 @enderror" disabled>
                                <option value="">Sélectionnez d'abord une catégorie principale</option>
                            </select>
                            @error('category_id')
                                <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Quantité -->
                        <div class="md:col-span-2">
                            <label for="quantity" class="block text-xs sm:text-sm font-medium text-red-700 mb-1 sm:mb-2">Quantité *</label>
                            <input type="number" id="quantity" name="quantity" value="{{ old('quantity') }}" required min="1" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 @error('quantity') border-red-500 @enderror">
                            @error('quantity')
                                <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="flex justify-between mt-6 sm:mt-8">
                        <a href="{{ route('prestataire.urgent-sales.create') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-sm sm:text-base">
                            <i class="fas fa-arrow-left mr-1 sm:mr-2"></i>Retour
                        </a>
                        <button type="submit" id="step1SubmitBtn" class="bg-red-600 hover:bg-red-700 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-sm sm:text-base flex items-center">
                            Suivant<i class="fas fa-arrow-right ml-1 sm:ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Validation en temps réel pour le titre
const titleInput = document.getElementById('title');

function validateTitle() {
    // Always show green border for any input
    titleInput.classList.remove('border-red-500', 'border-yellow-500', 'border-green-500');
    titleInput.classList.add('border-green-500');
}

titleInput.addEventListener('input', validateTitle);
titleInput.addEventListener('keyup', validateTitle);

// Gestion des catégories et sous-catégories
document.addEventListener('DOMContentLoaded', function() {
    const categoriesData = @json($categories->mapWithKeys(function($category) {
        return [$category->id => $category->children];
    }));
    
    const parentCategorySelect = document.getElementById('parent_category_id');
    const subcategorySelect = document.getElementById('category_id');
    
    parentCategorySelect.addEventListener('change', function() {
        const parentCategoryId = this.value;
        
        // Réinitialiser le select des sous-catégories
        subcategorySelect.innerHTML = '<option value="">Choisissez une sous-catégorie</option>';
        
        if (parentCategoryId && categoriesData[parentCategoryId]) {
            const subcategories = categoriesData[parentCategoryId];
            
            if (subcategories.length > 0) {
                subcategorySelect.disabled = false;
                subcategories.forEach(function(subcategory) {
                    const option = document.createElement('option');
                    option.value = subcategory.id;
                    option.textContent = subcategory.name;
                    if ('{{ old("category_id") }}' == subcategory.id) {
                        option.selected = true;
                    }
                    subcategorySelect.appendChild(option);
                });
            } else {
                subcategorySelect.innerHTML = '<option value="">Aucune sous-catégorie disponible</option>';
                subcategorySelect.disabled = true;
            }
        } else {
            subcategorySelect.innerHTML = '<option value="">Sélectionnez d\'abord une catégorie principale</option>';
            subcategorySelect.disabled = true;
        }
    });
    
    // Initialiser les sous-catégories si une catégorie principale est déjà sélectionnée
    if (parentCategorySelect.value) {
        parentCategorySelect.dispatchEvent(new Event('change'));
    }
    
    // Initialiser le compteur de titre au chargement
    validateTitle();
});

// Prevent form resubmission
document.getElementById('urgentSaleStep1Form').addEventListener('submit', function() {
    const submitBtn = document.getElementById('step1SubmitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>En cours...';
});
</script>
@endpush