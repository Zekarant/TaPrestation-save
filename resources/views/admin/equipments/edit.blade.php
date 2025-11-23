@extends('layouts.admin-modern')

@section('title', 'Modifier l\'équipement - Administration')

@section('content')
<div class="bg-green-50 min-h-screen">
    <div class="container mx-auto px-4 py-6">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-extrabold text-green-900 mb-2">
                            Modifier l'équipement
                        </h1>
                        <p class="text-lg text-green-700">
                            Modifiez les informations de l'équipement {{ $equipment->name }}
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('admin.equipments.show', $equipment) }}" class="bg-green-100 hover:bg-green-200 text-green-800 font-bold py-2 px-4 rounded-lg transition duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Retour aux détails
                        </a>
                        <a href="{{ route('admin.equipments.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-200">
                            <i class="fas fa-list mr-2"></i>
                            Liste des équipements
                        </a>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <div class="flex">
                        <div class="py-1">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                        </div>
                        <div>
                            <p class="font-bold">Erreurs de validation :</p>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('admin.equipments.update', $equipment) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Colonne principale -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Informations de base -->
                        <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6">
                            <h2 class="text-xl font-bold text-green-900 mb-4 border-b border-green-200 pb-2">
                                <i class="fas fa-info-circle mr-2"></i>
                                Informations de base
                            </h2>
                            <div class="space-y-6">
                                <!-- Nom de l'équipement -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-green-700 mb-2">Nom de l'équipement *</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $equipment->name) }}" required 
                                           placeholder="Ex: Perceuse sans fil Bosch" 
                                           class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>

                                <!-- Catégories -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="category_id" class="block text-sm font-medium text-green-700 mb-2">Catégorie principale *</label>
                                        <select id="category_id" name="category_id" required class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                            <option value="">Sélectionnez une catégorie</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id', $equipment->category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="subcategory_id" class="block text-sm font-medium text-green-700 mb-2">Sous-catégorie</label>
                                        <select id="subcategory_id" name="subcategory_id" class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                            <option value="">Sélectionnez une sous-catégorie</option>
                                            @if($equipment->subcategory)
                                                <option value="{{ $equipment->subcategory->id }}" selected>
                                                    {{ $equipment->subcategory->name }}
                                                </option>
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label for="description" class="block text-sm font-medium text-green-700 mb-2">Description *</label>
                                    <textarea id="description" name="description" rows="4" required 
                                              placeholder="Décrivez l'équipement, son état et ses caractéristiques principales" 
                                              class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">{{ old('description', $equipment->description) }}</textarea>
                                </div>

                                <!-- Spécifications techniques -->
                                <div>
                                    <label for="technical_specifications" class="block text-sm font-medium text-green-700 mb-2">Spécifications techniques</label>
                                    <textarea id="technical_specifications" name="technical_specifications" rows="3" 
                                              placeholder="Dimensions, poids, puissance, capacité..." 
                                              class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">{{ old('technical_specifications', $equipment->technical_specifications) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Détails techniques -->
                        <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6">
                            <h2 class="text-xl font-bold text-green-900 mb-4 border-b border-green-200 pb-2">
                                <i class="fas fa-cogs mr-2"></i>
                                Détails techniques
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="brand" class="block text-sm font-medium text-green-700 mb-2">Marque</label>
                                    <input type="text" name="brand" id="brand" value="{{ old('brand', $equipment->brand) }}" 
                                           class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label for="model" class="block text-sm font-medium text-green-700 mb-2">Modèle</label>
                                    <input type="text" name="model" id="model" value="{{ old('model', $equipment->model) }}" 
                                           class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label for="weight" class="block text-sm font-medium text-green-700 mb-2">Poids (kg)</label>
                                    <input type="number" step="0.1" name="weight" id="weight" value="{{ old('weight', $equipment->weight) }}" 
                                           class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label for="dimensions" class="block text-sm font-medium text-green-700 mb-2">Dimensions</label>
                                    <input type="text" name="dimensions" id="dimensions" value="{{ old('dimensions', $equipment->dimensions) }}" 
                                           placeholder="L x l x h" 
                                           class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label for="power_requirements" class="block text-sm font-medium text-green-700 mb-2">Alimentation</label>
                                    <input type="text" name="power_requirements" id="power_requirements" value="{{ old('power_requirements', $equipment->power_requirements) }}" 
                                           placeholder="220V, batterie, essence..." 
                                           class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label for="serial_number" class="block text-sm font-medium text-green-700 mb-2">Numéro de série</label>
                                    <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $equipment->serial_number) }}" 
                                           class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <!-- Tarification -->
                        <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6">
                            <h2 class="text-xl font-bold text-green-900 mb-4 border-b border-green-200 pb-2">
                                <i class="fas fa-euro-sign mr-2"></i>
                                Tarification
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div>
                                    <label for="price_per_hour" class="block text-sm font-medium text-green-700 mb-2">Prix par heure (€)</label>
                                    <input type="number" step="0.01" name="price_per_hour" id="price_per_hour" value="{{ old('price_per_hour', $equipment->price_per_hour) }}" 
                                           class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label for="daily_rate" class="block text-sm font-medium text-green-700 mb-2">Prix par jour (€) *</label>
                                    <input type="number" step="0.01" name="daily_rate" id="daily_rate" value="{{ old('daily_rate', $equipment->daily_rate) }}" required 
                                           class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label for="price_per_week" class="block text-sm font-medium text-green-700 mb-2">Prix par semaine (€)</label>
                                    <input type="number" step="0.01" name="price_per_week" id="price_per_week" value="{{ old('price_per_week', $equipment->price_per_week) }}" 
                                           class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label for="price_per_month" class="block text-sm font-medium text-green-700 mb-2">Prix par mois (€)</label>
                                    <input type="number" step="0.01" name="price_per_month" id="price_per_month" value="{{ old('price_per_month', $equipment->price_per_month) }}" 
                                           class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label for="deposit_amount" class="block text-sm font-medium text-green-700 mb-2">Caution (€) *</label>
                                    <input type="number" step="0.01" name="deposit_amount" id="deposit_amount" value="{{ old('deposit_amount', $equipment->deposit_amount) }}" required 
                                           class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Colonne latérale -->
                    <div class="space-y-6">
                        <!-- Photo principale -->
                        <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6">
                            <h3 class="text-lg font-bold text-green-900 mb-4">
                                <i class="fas fa-camera mr-2"></i>
                                Photo principale
                            </h3>
                            @if($equipment->main_photo)
                                <div class="mb-4">
                                    <img src="{{ Storage::url($equipment->main_photo) }}" alt="Photo actuelle" class="w-full h-48 object-cover rounded-lg">
                                    <p class="text-sm text-gray-600 mt-2">Photo actuelle</p>
                                </div>
                            @endif
                            <div>
                                <label for="main_photo" class="block text-sm font-medium text-green-700 mb-2">Nouvelle photo</label>
                                <input type="file" name="main_photo" id="main_photo" accept="image/*" 
                                       class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Formats acceptés : JPEG, PNG, WebP (max 5MB)</p>
                            </div>
                        </div>

                        <!-- État et statut -->
                        <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6">
                            <h3 class="text-lg font-bold text-green-900 mb-4">
                                <i class="fas fa-check-circle mr-2"></i>
                                État et statut
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="condition" class="block text-sm font-medium text-green-700 mb-2">État *</label>
                                    <select name="condition" id="condition" required 
                                            class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        <option value="">Sélectionner l'état</option>
                                        <option value="new" {{ old('condition', $equipment->condition) == 'new' ? 'selected' : '' }}>Neuf</option>
                                        <option value="excellent" {{ old('condition', $equipment->condition) == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                        <option value="very_good" {{ old('condition', $equipment->condition) == 'very_good' ? 'selected' : '' }}>Très bon</option>
                                        <option value="good" {{ old('condition', $equipment->condition) == 'good' ? 'selected' : '' }}>Bon</option>
                                        <option value="fair" {{ old('condition', $equipment->condition) == 'fair' ? 'selected' : '' }}>Correct</option>
                                        <option value="poor" {{ old('condition', $equipment->condition) == 'poor' ? 'selected' : '' }}>Mauvais</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="status" class="block text-sm font-medium text-green-700 mb-2">Statut *</label>
                                    <select name="status" id="status" required 
                                            class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        <option value="active" {{ old('status', $equipment->status) == 'active' ? 'selected' : '' }}>Actif</option>
                                        <option value="inactive" {{ old('status', $equipment->status) == 'inactive' ? 'selected' : '' }}>Inactif</option>
                                        <option value="maintenance" {{ old('status', $equipment->status) == 'maintenance' ? 'selected' : '' }}>En maintenance</option>
                                        <option value="rented" {{ old('status', $equipment->status) == 'rented' ? 'selected' : '' }}>Loué</option>
                                    </select>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="is_available" id="is_available" value="1" 
                                           {{ old('is_available', $equipment->is_available) ? 'checked' : '' }}
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                    <label for="is_available" class="ml-2 block text-sm text-gray-900">
                                        Disponible immédiatement
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Localisation -->
                        <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6">
                            <h3 class="text-lg font-bold text-green-900 mb-4">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                Localisation
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label for="address" class="block text-sm font-medium text-green-700 mb-2">Adresse</label>
                                    <input type="text" name="address" id="address" value="{{ old('address', $equipment->address) }}" 
                                           class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label for="city" class="block text-sm font-medium text-green-700 mb-2">Ville *</label>
                                        <input type="text" name="city" id="city" value="{{ old('city', $equipment->city) }}" 
                                               class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    </div>

                                    <div>
                                        <label for="postal_code" class="block text-sm font-medium text-green-700 mb-2">Code postal</label>
                                        <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $equipment->postal_code) }}" 
                                               class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    </div>
                                </div>

                                <div>
                                    <label for="country" class="block text-sm font-medium text-green-700 mb-2">Pays *</label>
                                    <input type="text" name="country" id="country" value="{{ old('country', $equipment->country) }}" 
                                           class="w-full px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="bg-white rounded-xl shadow-lg border border-green-200 p-6">
                            <div class="flex flex-col gap-3">
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                                    <i class="fas fa-save mr-2"></i>
                                    Enregistrer les modifications
                                </button>
                                <a href="{{ route('admin.equipments.show', $equipment) }}" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3 px-4 rounded-lg transition duration-200 text-center">
                                    <i class="fas fa-times mr-2"></i>
                                    Annuler
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Gestion des catégories et sous-catégories
document.getElementById('category_id').addEventListener('change', function() {
    const categoryId = this.value;
    const subcategorySelect = document.getElementById('subcategory_id');
    
    // Réinitialiser les sous-catégories
    subcategorySelect.innerHTML = '<option value="">Sélectionnez une sous-catégorie</option>';
    
    if (categoryId) {
        // Récupérer les sous-catégories via les données PHP
        const categories = @json($categories);
        const selectedCategory = categories.find(cat => cat.id == categoryId);
        
        if (selectedCategory && selectedCategory.children && selectedCategory.children.length > 0) {
            selectedCategory.children.forEach(function(subcategory) {
                const option = document.createElement('option');
                option.value = subcategory.id;
                option.textContent = subcategory.name;
                subcategorySelect.appendChild(option);
            });
        }
    }
});

// Déclencher l'événement au chargement pour les valeurs existantes
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id');
    if (categorySelect.value) {
        categorySelect.dispatchEvent(new Event('change'));
        
        // Sélectionner la sous-catégorie existante
        const existingSubcategoryId = '{{ old("subcategory_id", $equipment->subcategory_id) }}';
        if (existingSubcategoryId) {
            setTimeout(() => {
                document.getElementById('subcategory_id').value = existingSubcategoryId;
            }, 100);
        }
    }
});
</script>
@endsection