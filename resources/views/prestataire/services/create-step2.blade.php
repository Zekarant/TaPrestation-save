@extends('layouts.app')

@section('content')
<div class="bg-blue-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-blue-900 mb-2">Créer un nouveau service</h1>
                <p class="text-base sm:text-lg text-blue-700">Étape 2 : Prix et Catégorie</p>
            </div>

            <!-- Indicateur d'étapes -->
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-4 sm:p-6 mb-4 sm:mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        <a href="{{ route('prestataire.services.create.step1') }}" class="text-blue-600 hover:text-blue-900 transition-colors duration-200">
                            <i class="fas fa-arrow-left text-lg sm:text-xl"></i>
                        </a>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-blue-900">Étape 2 sur 4</h2>
                            <p class="text-sm sm:text-base text-blue-700 hidden sm:block">Prix et Catégorie</p>
                        </div>
                    </div>
                </div>
                
                <!-- Barre de progression -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-1 sm:space-x-2 lg:space-x-4 w-full overflow-x-auto">
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                <i class="fas fa-check text-xs"></i>
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-600 hidden sm:inline">Informations</span>
                            <span class="ml-1 text-xs font-medium text-green-600 sm:hidden">Info</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 rounded min-w-4">
                            <div class="h-1 bg-blue-600 rounded" style="width: 50%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                2
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-blue-600 hidden sm:inline">Prix & Catégorie</span>
                            <span class="ml-1 text-xs font-medium text-blue-600 sm:hidden">Prix</span>
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

            <form method="POST" action="{{ route('prestataire.services.create.step2.store') }}" id="step2Form">
                @csrf

                <!-- Prix -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
                    <h2 class="text-base sm:text-lg lg:text-xl font-bold text-blue-900 mb-3 sm:mb-4 border-b border-blue-200 pb-2">
                        <i class="fas fa-euro-sign text-green-600 mr-1 sm:mr-2 text-sm sm:text-base"></i>Prix du service
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 lg:gap-6">
                        <div>
                            <label for="price" class="block text-xs sm:text-sm font-medium text-blue-700 mb-1 sm:mb-2">Prix (€)</label>
                            <input type="number" id="price" name="price" value="{{ old('price', session('service_data.price')) }}" min="0" step="0.01" class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('price') border-red-500 @enderror">
                            @error('price')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="price_type" class="block text-xs sm:text-sm font-medium text-blue-700 mb-1 sm:mb-2">Type de tarification</label>
                            <select id="price_type" name="price_type" class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('price_type') border-red-500 @enderror">
                                <option value="">Sélectionnez un type</option>
                                <option value="fixe" {{ old('price_type', session('service_data.price_type')) == 'fixe' ? 'selected' : '' }}>Prix fixe</option>
                                <option value="heure" {{ old('price_type', session('service_data.price_type')) == 'heure' ? 'selected' : '' }}>Par heure</option>
                                <option value="jour" {{ old('price_type', session('service_data.price_type')) == 'jour' ? 'selected' : '' }}>Par jour</option>
                                <option value="projet" {{ old('price_type', session('service_data.price_type')) == 'projet' ? 'selected' : '' }}>Par projet</option>
                                <option value="devis" {{ old('price_type', session('service_data.price_type')) == 'devis' ? 'selected' : '' }}>Sur devis</option>
                            </select>
                            @error('price_type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Champ dynamique pour le nombre d'heures/jours -->
                    <div id="quantity-container" class="mt-4 sm:mt-6" style="display: none;">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 lg:gap-6">
                            <div>
                                <label id="quantity-label" for="quantity" class="block text-xs sm:text-sm font-medium text-blue-700 mb-1 sm:mb-2"></label>
                                <input type="number" id="quantity" name="quantity" min="1" value="{{ old('quantity', session('service_data.quantity')) }}" class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('quantity') border-red-500 @enderror">
                                @error('quantity')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Affichage du prix total -->
                    <div id="total-price-container" class="mt-4 sm:mt-6" style="display: none;">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm sm:text-base font-medium text-blue-700">Prix total estimé :</span>
                                <span id="total-price" class="text-lg sm:text-xl font-bold text-blue-900">0,00 €</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catégorie -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
                    <h2 class="text-base sm:text-lg lg:text-xl font-bold text-blue-900 mb-3 sm:mb-4 border-b border-blue-200 pb-2">
                        <i class="fas fa-tags text-purple-600 mr-1 sm:mr-2 text-sm sm:text-base"></i>Catégorie du service
                    </h2>
                    
                    <div class="space-y-3 sm:space-y-4">
                        <div>
                            <label for="category_id" class="block text-xs sm:text-sm font-medium text-blue-700 mb-1 sm:mb-2">Catégorie principale *</label>
                            <select id="category_id" name="category_id" required class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('category_id') border-red-500 @enderror">
                                <option value="">Sélectionnez une catégorie principale</option>
                                @foreach($categories->whereNull('parent_id') as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', session('service_data.category_id')) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div id="subcategory-group" style="display: none;">
                            <label for="subcategory_id" class="block text-xs sm:text-sm font-medium text-blue-700 mb-1 sm:mb-2">Sous-catégorie</label>
                            <select id="subcategory_id" name="subcategory_id" class="w-full px-3 py-2 sm:py-2.5 text-sm sm:text-base border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled>
                                <option value="">Veuillez d'abord choisir une catégorie</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row justify-between items-center pt-4 sm:pt-6 lg:pt-8 border-t border-blue-200 space-y-3 sm:space-y-0">
                    <a href="{{ route('prestataire.services.create.step1') }}" class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-3 sm:px-4 lg:px-6 py-2.5 sm:py-3 rounded-lg transition duration-200 font-medium text-center text-xs sm:text-sm lg:text-base">
                        <i class="fas fa-arrow-left mr-1 sm:mr-2 text-xs sm:text-sm"></i><span class="hidden xs:inline">Précédent</span><span class="xs:hidden">Retour</span>
                    </a>
                    <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 sm:px-6 lg:px-8 py-2.5 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-xs sm:text-sm lg:text-base">
                        <span class="hidden xs:inline">Suivant : Photos</span><span class="xs:hidden">Suivant</span><i class="fas fa-arrow-right ml-1 sm:ml-2 text-xs sm:text-sm"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Gestion des catégories
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    const subcategoryGroup = document.getElementById('subcategory-group');
    
    // Prevent form resubmission
    const form = document.getElementById('step2Form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Check availability before submitting
            if (!checkAvailability()) {
                e.preventDefault();
                return false;
            }
            
            // Disable the submit button to prevent double submission
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi...';
            }
        });
    }
    
    // Gestion du prix dynamique
    const priceInput = document.getElementById('price');
    const priceTypeSelect = document.getElementById('price_type');
    const quantityContainer = document.getElementById('quantity-container');
    const quantityLabel = document.getElementById('quantity-label');
    const quantityInput = document.getElementById('quantity');
    const totalPriceContainer = document.getElementById('total-price-container');
    const totalPriceDisplay = document.getElementById('total-price');
    
    // Availability warning element
    const availabilityWarning = document.createElement('div');
    availabilityWarning.id = 'availability-warning';
    availabilityWarning.className = 'mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800 text-sm hidden';
    availabilityWarning.innerHTML = `
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium">Attention : Durée dépassant vos disponibilités</h3>
                <div class="mt-2 text-sm">
                    <p>La durée que vous avez sélectionnée dépasse vos disponibilités actuelles.</p>
                    <p class="mt-1">Veuillez augmenter vos disponibilités pour offrir ce service.</p>
                </div>
            </div>
        </div>
    `;
    totalPriceContainer.parentNode.insertBefore(availabilityWarning, totalPriceContainer.nextSibling);
    
    // Get availability data from the server
    const availabilities = @json($availabilities ?? []);
    
    // Fonction pour mettre à jour le prix total
    function updateTotalPrice() {
        const price = parseFloat(priceInput.value) || 0;
        const quantity = parseFloat(quantityInput.value) || 0;
        const priceType = priceTypeSelect.value;
        
        // Afficher le conteneur de quantité uniquement pour les types "heure" ou "jour"
        if (priceType === 'heure' || priceType === 'jour') {
            quantityContainer.style.display = 'block';
            totalPriceContainer.style.display = 'block';
            
            // Mettre à jour le label en fonction du type
            quantityLabel.textContent = priceType === 'heure' ? 'Nombre d\'heures' : 'Nombre de jours';
            
            // Calculer et afficher le prix total
            const total = price * quantity;
            totalPriceDisplay.textContent = total.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
            
            // Check availability
            checkAvailability();
        } else {
            quantityContainer.style.display = 'none';
            totalPriceContainer.style.display = 'none';
            availabilityWarning.classList.add('hidden');
        }
    }
    
    // Fonction pour vérifier la disponibilité
    function checkAvailability() {
        const priceType = priceTypeSelect.value;
        const quantity = parseFloat(quantityInput.value) || 0;
        
        // Only check for hourly services
        if (priceType === 'heure' && quantity > 0) {
            // Find the maximum available hours in a single day
            let maxAvailableHoursInADay = 0;
            
            // Check each availability entry
            availabilities.forEach(availability => {
                if (availability.is_active) {
                    // Parse start and end times
                    const startTime = new Date(`1970-01-01T${availability.start_time}`);
                    const endTime = new Date(`1970-01-01T${availability.end_time}`);
                    
                    // Calculate working minutes (subtracting break time if exists)
                    let workingMinutes = (endTime - startTime) / (1000 * 60);
                    
                    // Subtract break time if it exists
                    if (availability.break_start_time && availability.break_end_time) {
                        const breakStartTime = new Date(`1970-01-01T${availability.break_start_time}`);
                        const breakEndTime = new Date(`1970-01-01T${availability.break_end_time}`);
                        const breakMinutes = (breakEndTime - breakStartTime) / (1000 * 60);
                        workingMinutes -= breakMinutes;
                    }
                    
                    // Convert to hours
                    const availableHours = workingMinutes / 60;
                    
                    // Update maximum if this day has more availability
                    if (availableHours > maxAvailableHoursInADay) {
                        maxAvailableHoursInADay = availableHours;
                    }
                }
            });
            
            // Check if quantity exceeds the maximum available hours in a single day
            if (quantity > maxAvailableHoursInADay) {
                availabilityWarning.classList.remove('hidden');
                return false;
            } else {
                availabilityWarning.classList.add('hidden');
                return true;
            }
        }
        
        availabilityWarning.classList.add('hidden');
        return true;
    }
    
    // Écouter les changements sur les champs de prix, type et quantité
    priceInput.addEventListener('input', updateTotalPrice);
    priceTypeSelect.addEventListener('change', updateTotalPrice);
    quantityInput.addEventListener('input', updateTotalPrice);
    
    // Initialiser l'affichage au chargement de la page
    updateTotalPrice();
    
    // Fonctions pour charger les catégories
    function loadMainCategories() {
        fetch('/categories/main')
            .then(response => response.json())
            .then(categories => {
                categorySelect.innerHTML = '<option value="">Sélectionnez une catégorie principale</option>';
                categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    categorySelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Erreur lors du chargement des catégories:', error);
            });
    }
    
    function loadSubcategories(categoryId) {
        if (!categoryId) {
            subcategoryGroup.style.display = 'none';
            subcategorySelect.disabled = true;
            subcategorySelect.innerHTML = '<option value="">Veuillez d\'abord choisir une catégorie</option>';
            return;
        }
        
        fetch(`/api/categories/${categoryId}/subcategories`)
            .then(response => response.json())
            .then(subcategories => {
                subcategorySelect.innerHTML = '';
                
                if (subcategories.length === 0) {
                    subcategorySelect.innerHTML = '<option value="">Pas de sous-catégorie disponible</option>';
                    subcategorySelect.disabled = true;
                } else {
                    subcategorySelect.innerHTML = '<option value="">Sélectionnez une sous-catégorie (optionnel)</option>';
                    subcategories.forEach(subcategory => {
                        const option = document.createElement('option');
                        option.value = subcategory.id;
                        option.textContent = subcategory.name;
                        subcategorySelect.appendChild(option);
                    });
                    subcategorySelect.disabled = false;
                }
                
                subcategoryGroup.style.display = 'block';
            })
            .catch(error => {
                console.error('Erreur lors du chargement des sous-catégories:', error);
                subcategorySelect.innerHTML = '<option value="">Erreur de chargement</option>';
                subcategorySelect.disabled = true;
                subcategoryGroup.style.display = 'block';
            });
    }
    
    // Charger les catégories principales au chargement de la page
    if (categorySelect) {
        loadMainCategories();
        
        // Gérer le changement de catégorie principale
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            loadSubcategories(categoryId);
        });
        
        // Si une catégorie est déjà sélectionnée (old input), charger les sous-catégories
        const selectedCategoryId = categorySelect.value;
        if (selectedCategoryId) {
            loadSubcategories(selectedCategoryId);
        }
    }
});
</script>
@endpush