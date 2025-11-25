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

            <!-- Étape 3: Description et photos -->
            <div class="bg-white rounded-xl shadow-lg border border-red-200 p-3 sm:p-4 md:p-6 mb-4 sm:mb-6">
                <div class="flex items-center mb-3 sm:mb-4">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-red-600 text-white flex items-center justify-center text-xs sm:text-sm font-bold mr-2 sm:mr-3">
                        3
                    </div>
                    <h2 class="text-base sm:text-lg md:text-xl font-bold text-red-900">Description et photos</h2>
                </div>
                
                <form id="urgentSaleStep3Form" action="{{ route('prestataire.urgent-sales.create.step3.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4 sm:space-y-6">
                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-xs sm:text-sm font-medium text-red-700 mb-1 sm:mb-2">Description détaillée *</label>
                            <textarea id="description" name="description" required rows="5" class="w-full px-2 sm:px-3 py-2 text-sm sm:text-base border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 @error('description') border-red-500 @enderror" placeholder="Décrivez votre produit en détail : caractéristiques, raison de la vente, défauts éventuels...">{{ old('description') }}</textarea>
                            <div class="mt-1">
                                @error('description')
                                    <p class="text-red-500 text-xs sm:text-sm">{{ $message }}</p>
                                @enderror
                                <p class="text-red-600 text-xs">Soyez précis et complet dans votre description</p>
                            </div>
                        </div>
                        
                        <!-- Conseils pour la description -->
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3 sm:p-4">
                            <h4 class="text-xs sm:text-sm font-semibold text-red-800 mb-2">
                                <i class="fas fa-lightbulb mr-1 sm:mr-2 text-xs sm:text-sm"></i>Conseils pour une bonne description
                            </h4>
                            <ul class="text-xs sm:text-sm text-red-700 space-y-1">
                                <li>• <strong>Soyez précis :</strong> Marque, modèle, année, dimensions</li>
                                <li>• <strong>État réel :</strong> Mentionnez les défauts ou usures</li>
                                <li>• <strong>Raison de vente :</strong> Déménagement, changement, etc.</li>
                                <li>• <strong>Accessoires inclus :</strong> Boîte, notice, garantie</li>
                                <li>• <strong>Urgence :</strong> Précisez pourquoi c'est urgent</li>
                            </ul>
                        </div>
                        
                        <!-- Photos -->
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-red-900 mb-2 sm:mb-3">Photos</h3>
                            <div class="border-2 border-dashed border-red-300 rounded-lg p-3 sm:p-4 md:p-6 text-center hover:border-red-400 transition-colors">
                                <input type="file" id="photos" name="photos[]" multiple accept="image/*" class="hidden" onchange="previewImages(this)">
                                <div id="upload-area" class="cursor-pointer" onclick="document.getElementById('photos').click()">
                                    <i class="fas fa-cloud-upload-alt text-red-400 text-2xl sm:text-3xl md:text-4xl mb-2 sm:mb-3 md:mb-4"></i>
                                    <p class="text-red-600 mb-1 sm:mb-2 text-xs sm:text-sm md:text-base">Cliquez pour ajouter des photos ou glissez-déposez</p>
                                    <p class="text-red-500 text-xs">Maximum 5 photos, 5MB par photo</p>
                                </div>
                                
                                <!-- Prévisualisation des images -->
                                <div id="image-preview" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 sm:gap-4 mt-3 sm:mt-4 hidden"></div>
                            </div>
                            @error('photos')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            @error('photos.*')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Conseils pour les photos -->
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3 sm:p-4">
                            <h4 class="text-xs sm:text-sm font-semibold text-red-800 mb-2">
                                <i class="fas fa-camera mr-1 sm:mr-2 text-xs sm:text-sm"></i>Conseils pour de bonnes photos
                            </h4>
                            <ul class="text-xs sm:text-sm text-red-700 space-y-1">
                                <li>• <strong>Éclairage :</strong> Prenez les photos en pleine lumière</li>
                                <li>• <strong>Angles multiples :</strong> Vue d'ensemble, détails, défauts</li>
                                <li>• <strong>Netteté :</strong> Évitez les photos floues</li>
                                <li>• <strong>Contexte :</strong> Montrez l'objet en situation d'usage</li>
                                <li>• <strong>Honnêteté :</strong> N'hésitez pas à montrer les défauts</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="flex justify-between mt-6 sm:mt-8">
                        <a href="{{ route('prestataire.urgent-sales.create.step2') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-sm sm:text-base">
                            <i class="fas fa-arrow-left mr-1 sm:mr-2"></i>Précédent
                        </a>
                        <button type="submit" id="step3SubmitBtn" class="bg-red-600 hover:bg-red-700 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-sm sm:text-base flex items-center">
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
// Validation en temps réel pour la description
const descriptionInput = document.getElementById('description');

function validateDescription() {
    // Always show green border for any input
    descriptionInput.classList.remove('border-red-500', 'border-yellow-500', 'border-green-500');
    descriptionInput.classList.add('border-green-500');
}

descriptionInput.addEventListener('input', validateDescription);
descriptionInput.addEventListener('keyup', validateDescription);

// Variables globales pour la gestion des images
let existingFiles = [];
let isAddingMore = false;

// Prévisualisation des images
function previewImages(input) {
    const preview = document.getElementById('image-preview');
    const uploadArea = document.getElementById('upload-area');
    
    if (isAddingMore && existingFiles.length > 0) {
        // Combiner les fichiers existants avec les nouveaux
        const dt = new DataTransfer();
        
        // Ajouter les fichiers existants
        existingFiles.forEach(file => {
            dt.items.add(file);
        });
        
        // Ajouter les nouveaux fichiers (limiter le total à 5)
        const remainingSlots = 5 - existingFiles.length;
        const newFiles = Array.from(input.files).slice(0, remainingSlots);
        newFiles.forEach(file => {
            dt.items.add(file);
        });
        
        input.files = dt.files;
        existingFiles = Array.from(dt.files);
        isAddingMore = false;
    } else {
        existingFiles = Array.from(input.files);
    }
    
    preview.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        preview.classList.remove('hidden');
        uploadArea.classList.add('hidden');
        
        // Limiter à 5 images
        const files = Array.from(input.files).slice(0, 5);
        
        files.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const div = document.createElement('div');
                div.className = 'relative group';
                
                const img = document.createElement('img');
                img.className = 'w-full h-24 object-cover rounded-lg';
                img.style.display = 'block';
                img.style.backgroundColor = 'transparent';
                
                img.onload = function() {
                    URL.revokeObjectURL(this.src);
                };
                
                img.onerror = function() {
                    console.error('Erreur lors du chargement de l\'image');
                    URL.revokeObjectURL(this.src);
                };
                
                img.src = URL.createObjectURL(file);
                div.appendChild(img);
                
                // Bouton de suppression
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.onclick = () => removeImage(index);
                div.appendChild(removeBtn);
                
                // Label photo
                const label = document.createElement('div');
                label.className = 'absolute bottom-1 left-1 bg-black bg-opacity-50 text-white text-xs px-1 rounded';
                label.textContent = `Photo ${index + 1}`;
                div.appendChild(label);
                
                // Icône de drag
                const dragIcon = document.createElement('div');
                dragIcon.className = 'absolute top-1 left-1 text-white opacity-0 group-hover:opacity-100 transition-opacity';
                dragIcon.innerHTML = '<i class="fas fa-grip-vertical text-xs"></i>';
                div.appendChild(dragIcon);
                
                preview.appendChild(div);
            }
        });
        
        // Ajouter un bouton pour ajouter plus d'images si moins de 5
        if (files.length < 5) {
            addMoreButton(files.length);
        }
    } else {
        preview.classList.add('hidden');
        uploadArea.classList.remove('hidden');
    }
}

function addMoreButton(currentCount) {
    const preview = document.getElementById('image-preview');
    
    // Créer un bouton "Ajouter plus"
    const addButton = document.createElement('div');
    addButton.className = 'flex flex-col items-center justify-center border-2 border-dashed border-red-300 rounded-lg cursor-pointer hover:border-red-400 transition-colors p-4';
    addButton.id = 'add-more-btn';
    addButton.onclick = function() {
        isAddingMore = true;
        document.getElementById('photos').click();
    };
    
    addButton.innerHTML = `
        <i class="fas fa-plus text-red-400 text-xl mb-2"></i>
        <span class="text-red-600 text-xs sm:text-sm">Ajouter plus (${5 - currentCount} restantes)</span>
    `;
    
    preview.appendChild(addButton);
}

function removeImage(index) {
    // Retirer le fichier de la liste
    existingFiles.splice(index, 1);
    
    // Mettre à jour l'input file
    const dt = new DataTransfer();
    existingFiles.forEach(file => {
        dt.items.add(file);
    });
    
    const input = document.getElementById('photos');
    input.files = dt.files;
    
    // Mettre à jour l'aperçu
    previewImages(input);
}

// Initialiser la validation de la description
document.addEventListener('DOMContentLoaded', function() {
    validateDescription();
});

// Prevent form resubmission
document.getElementById('urgentSaleStep3Form').addEventListener('submit', function() {
    const submitBtn = document.getElementById('step3SubmitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>En cours...';
});
</script>
@endpush