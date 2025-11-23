@extends('layouts.app')

@section('content')
<div class="bg-green-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-green-900 mb-2">Ajouter un nouvel équipement</h1>
                <p class="text-base sm:text-lg text-green-700">Étape 1 : Informations de base</p>
            </div>
            
            <!-- Message d'information -->
            @if(session('equipment_just_created'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700">
                            {{ session('info') ?? "Vous avez déjà créé un équipement. Si vous souhaitez créer un nouvel équipement, vous pouvez le faire en cliquant sur le bouton ci-dessous." }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Indicateur d'étapes -->
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 sm:mb-4 space-y-2 sm:space-y-0">
                    <h2 class="text-base sm:text-lg font-semibold text-green-900">Processus de création</h2>
                    <span class="text-xs sm:text-sm text-green-600">Étape 1 sur 4</span>
                </div>
                <div class="flex items-center space-x-1 sm:space-x-2 lg:space-x-4 overflow-x-auto">
                    <div class="flex items-center flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-medium">
                            1
                        </div>
                        <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-900 hidden sm:inline">Informations de base</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 rounded min-w-4"></div>
                    <div class="flex items-center flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-medium">
                            2
                        </div>
                        <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-gray-500 hidden sm:inline">Tarifs et conditions</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 rounded min-w-4"></div>
                    <div class="flex items-center flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-medium">
                            3
                        </div>
                        <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-gray-500 hidden sm:inline">Photos</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 rounded min-w-4"></div>
                    <div class="flex items-center flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-medium">
                            4
                        </div>
                        <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-gray-500 hidden sm:inline">Localisation et résumé</span>
                    </div>
                </div>
                <!-- Labels mobiles -->
                <div class="flex justify-between mt-2 sm:hidden text-xs text-gray-600">
                    <span class="text-green-600 font-medium">Info</span>
                    <span>Tarifs</span>
                    <span>Photos</span>
                    <span>Résumé</span>
                </div>
            </div>

            <!-- Formulaire Étape 1 -->
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 space-y-3 sm:space-y-0">
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('prestataire.equipment.index') }}" class="text-green-600 hover:text-green-900 transition-colors duration-200 p-1">
                            <i class="fas fa-arrow-left text-base sm:text-lg"></i>
                        </a>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-green-900">Informations de base</h2>
                            <p class="text-xs sm:text-sm text-green-700">Décrivez votre équipement et choisissez sa catégorie</p>
                        </div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong class="font-bold">Oups!</strong>
                        <span class="block sm:inline">Quelque chose s'est mal passé.</span>
                        <ul class="mt-2">
                            @foreach ($errors->all() as $error)
                                <li>- {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('prestataire.equipment.store.step1') }}" method="POST">
                    @csrf
                    
                    <div class="space-y-4 sm:space-y-6">
                        <!-- Nom de l'équipement -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-green-700 mb-2">Nom de l'équipement *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', session('equipment_step1.name')) }}" required placeholder="Ex: Perceuse sans fil Bosch" class="w-full px-3 py-2 text-sm sm:text-base border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('name') border-red-500 @enderror">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mt-1 space-y-1 sm:space-y-0">
                                <div class="flex-1">
                                    @error('name')
                                        <p class="text-red-500 text-xs sm:text-sm">{{ $message }}</p>
                                    @enderror
                                </div>
                                <p class="text-gray-500 text-xs sm:text-sm flex-shrink-0 sm:ml-4"><span id="name-count">0</span>/70</p>
                            </div>
                        </div>

                        <!-- Catégorie principale -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-green-700 mb-2">Catégorie principale *</label>
                            <select id="category_id" name="category_id" required class="w-full px-3 py-2 text-sm sm:text-base border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('category_id') border-red-500 @enderror">
                                <option value="">Sélectionnez une catégorie principale</option>
                            </select>
                            @error('category_id')
                                <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sous-catégorie -->
                        <div id="subcategory-group" style="display: none;">
                            <label for="subcategory_id" class="block text-sm font-medium text-green-700 mb-2">Sous-catégorie</label>
                            <select id="subcategory_id" name="subcategory_id" class="w-full px-3 py-2 text-sm sm:text-base border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('subcategory_id') border-red-500 @enderror" disabled>
                                <option value="">Veuillez d'abord choisir une catégorie</option>
                            </select>
                            @error('subcategory_id')
                                <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-green-700 mb-2">Description courte *</label>
                            <textarea id="description" name="description" rows="4" required placeholder="Décrivez brièvement votre équipement, son état et ses caractéristiques principales" class="w-full px-3 py-2 text-sm sm:text-base border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 resize-none @error('description') border-red-500 @enderror">{{ old('description', session('equipment_step1.description')) }}</textarea>
                            <div class="mt-1">
                                @error('description')
                                    <p class="text-red-500 text-xs sm:text-sm">{{ $message }}</p>
                                @enderror
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mt-1 space-y-1 sm:space-y-0">
                                    <p class="text-gray-500 text-xs sm:text-sm flex-1">Décrivez votre équipement en détail</p>
                                    <p class="text-gray-500 text-xs sm:text-sm flex-shrink-0 sm:ml-4"><span id="description-count">0</span> caractères</p>
                                </div>
                            </div>
                        </div>

                        <!-- Spécifications techniques -->
                        <div>
                            <label for="technical_specifications" class="block text-sm font-medium text-green-700 mb-2">Spécifications techniques</label>
                            <textarea id="technical_specifications" name="technical_specifications" rows="3" placeholder="Dimensions, poids, puissance, capacité..." class="w-full px-3 py-2 text-sm sm:text-base border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 resize-none">{{ old('technical_specifications', session('equipment_step1.technical_specifications')) }}</textarea>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center pt-4 sm:pt-6 border-t border-green-200 gap-3 sm:gap-4 mt-6 sm:mt-8">
                        <a href="{{ route('prestataire.equipment.index') }}" class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg transition duration-200 font-medium text-center text-sm sm:text-base">
                            <i class="fas fa-times mr-2"></i>Annuler
                        </a>
                        
                        <button type="submit" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-6 sm:px-8 py-2.5 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-sm sm:text-base">
                            <span class="hidden sm:inline">Suivant : Tarifs et conditions</span>
                            <span class="sm:hidden">Suivant</span>
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Simple character counters without validation limits
const nameInput = document.getElementById('name');
const nameCount = document.getElementById('name-count');

function updateNameCount() {
    const length = nameInput.value.length;
    nameCount.textContent = length;
}

nameInput.addEventListener('input', updateNameCount);
updateNameCount(); // Initial count

// Simple character counter for description
const descriptionInput = document.getElementById('description');
const descriptionCount = document.getElementById('description-count');

function updateDescriptionCount() {
    const length = descriptionInput.value.length;
    descriptionCount.textContent = length;
}

descriptionInput.addEventListener('input', updateDescriptionCount);
updateDescriptionCount(); // Initial count

// Simple form submission without character limit validation
document.querySelector('form').addEventListener('submit', function(e) {
    // Only check for basic requirements
    if (!nameInput.value.trim()) {
        e.preventDefault();
        nameInput.focus();
        alert('Veuillez entrer un nom pour l\'équipement.');
        return false;
    }
    
    if (!descriptionInput.value.trim()) {
        e.preventDefault();
        descriptionInput.focus();
        alert('Veuillez entrer une description.');
        return false;
    }
});

// Gestion des catégories et sous-catégories
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    
    if (categorySelect) {
        loadMainCategories();
        
        // Gérer le changement de catégorie principale
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            loadSubcategories(categoryId);
        });
    }
});

// Fonctions pour la gestion des catégories
function loadMainCategories() {
    fetch('/categories/main')
        .then(response => response.json())
        .then(categories => {
            const categorySelect = document.getElementById('category_id');
            categorySelect.innerHTML = '<option value="">Sélectionnez une catégorie principale</option>';
            
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                if (category.id == '{{ old("category_id", session("equipment_step1.category_id")) }}') {
                    option.selected = true;
                }
                categorySelect.appendChild(option);
            });
            
            // Si une catégorie est sélectionnée, charger les sous-catégories
            if (categorySelect.value) {
                loadSubcategories(categorySelect.value);
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des catégories:', error);
        });
}

function loadSubcategories(categoryId) {
    const subcategorySelect = document.getElementById('subcategory_id');
    const subcategoryGroup = document.getElementById('subcategory-group');
    
    if (!categoryId) {
        subcategoryGroup.style.display = 'none';
        subcategorySelect.disabled = true;
        subcategorySelect.innerHTML = '<option value="">Veuillez d\'abord choisir une catégorie</option>';
        return;
    }
    
    fetch(`/api/categories/${categoryId}/subcategories`)
        .then(response => response.json())
        .then(subcategories => {
            subcategorySelect.innerHTML = '<option value="">Sélectionnez une sous-catégorie</option>';
            
            if (subcategories.length > 0) {
                subcategories.forEach(subcategory => {
                    const option = document.createElement('option');
                    option.value = subcategory.id;
                    option.textContent = subcategory.name;
                    if (subcategory.id == '{{ old("subcategory_id", session("equipment_step1.subcategory_id")) }}') {
                        option.selected = true;
                    }
                    subcategorySelect.appendChild(option);
                });
                subcategoryGroup.style.display = 'block';
                subcategorySelect.disabled = false;
            } else {
                subcategorySelect.innerHTML = '<option value="">Pas de sous-catégorie disponible</option>';
                subcategoryGroup.style.display = 'block';
                subcategorySelect.disabled = true;
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des sous-catégories:', error);
            subcategorySelect.innerHTML = '<option value="">Erreur de chargement</option>';
            subcategoryGroup.style.display = 'block';
            subcategorySelect.disabled = true;
        });
}

// Prevent form resubmission
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function() {
            // Disable the submit button to prevent double submission
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi...';
            }
        });
    }
});
</script>
@endsection