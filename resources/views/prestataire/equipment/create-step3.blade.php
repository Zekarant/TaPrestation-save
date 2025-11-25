@extends('layouts.app')

@section('title', 'Ajouter un équipement - Étape 3')

@section('content')
<div class="bg-green-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="mb-6 sm:mb-8 text-center">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-green-900 mb-2">Ajouter un équipement</h1>
                <p class="text-base sm:text-lg text-green-700">Étape 3 : Photos</p>
            </div>

            <!-- Barre de progression -->
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-green-900">Processus de création</h2>
                    <span class="text-sm text-green-600">Étape 3 sur 4</span>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-medium">
                            ✓
                        </div>
                        <span class="ml-2 text-sm font-medium text-green-900">Informations de base</span>
                    </div>
                    <div class="flex-1 h-1 bg-green-600 rounded"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-medium">
                            ✓
                        </div>
                        <span class="ml-2 text-sm font-medium text-green-900">Tarifs et conditions</span>
                    </div>
                    <div class="flex-1 h-1 bg-green-600 rounded"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-medium">
                            3
                        </div>
                        <span class="ml-2 text-sm font-medium text-green-900">Photos</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 rounded"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-medium">
                            4
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-500">Localisation et résumé</span>
                    </div>
                </div>
            </div>

            <!-- Formulaire Étape 3 -->
            <div class="bg-white rounded-xl shadow-lg border border-green-200 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 space-y-3 sm:space-y-0">
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('prestataire.equipment.create.step2') }}" class="text-green-600 hover:text-green-900 transition-colors duration-200 p-1">
                            <i class="fas fa-arrow-left text-base sm:text-lg"></i>
                        </a>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-green-900">Photos de l'équipement</h2>
                            <p class="text-xs sm:text-sm text-green-700">Ajoutez une photo principale de votre équipement</p>
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

                <form action="{{ route('prestataire.equipment.store.step3') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="space-y-4 sm:space-y-6">
                        <!-- Photos -->
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-green-900 mb-3 sm:mb-4">Photos de l'équipement *</h3>
                            <div class="border-2 border-dashed border-green-300 rounded-lg p-4 sm:p-6 text-center hover:border-green-400 transition-colors">
                                <input type="file" name="photos[]" id="photos" multiple required accept="image/*" class="hidden" onchange="previewImages(this)">
                                <div id="upload-area" class="cursor-pointer" onclick="document.getElementById('photos').click()">
                                    <i class="fas fa-cloud-upload-alt text-green-400 text-2xl sm:text-4xl mb-2 sm:mb-4"></i>
                                    <p class="text-green-600 mb-1 sm:mb-2 text-sm sm:text-base">Cliquez pour ajouter des photos ou glissez-déposez</p>
                                    <p class="text-green-500 text-xs sm:text-sm">Maximum 5 photos, 5MB par photo</p>
                                    <p class="text-gray-500 text-xs mt-1 sm:mt-2">Formats acceptés : JPG, PNG, GIF</p>
                                </div>
                                <div id="image-preview" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 sm:gap-4 mt-3 sm:mt-4 hidden"></div>
                            </div>
                            @error('photos')
                                <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                            @enderror
                            @error('photos.*')
                                <p class="text-red-500 text-xs sm:text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Conseils pour les photos -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
                            <div class="flex items-start">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-500 mt-0.5 mr-2 sm:mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="text-xs sm:text-sm text-blue-700">
                                    <p class="font-medium mb-1 sm:mb-2">Conseils pour de meilleures photos :</p>
                                    <ul class="list-disc list-inside space-y-0.5 sm:space-y-1">
                                        <li>Utilisez un bon éclairage naturel</li>
                                        <li>Montrez l'équipement sous différents angles</li>
                                        <li>Assurez-vous que l'équipement soit propre et bien visible</li>
                                        <li>Évitez les arrière-plans encombrés</li>
                                        <li>Incluez les accessoires importants dans la photo</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row justify-between items-center pt-6 border-t border-green-200 gap-3 sm:gap-0 mt-8">
                        <a href="{{ route('prestataire.equipment.create.step2') }}" class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-6 py-3 rounded-lg transition duration-200 font-medium text-center">
                            <i class="fas fa-arrow-left mr-2"></i>Précédent
                        </a>
                        
                        <button type="submit" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl">
                            Suivant : Localisation et résumé
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let existingFiles = [];
let isAddingMore = false;

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
    const remaining = 5 - currentCount;
    
    const addMore = document.createElement('div');
    addMore.className = 'flex flex-col items-center justify-center h-24 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-gray-400 transition-colors';
    addMore.innerHTML = `
        <i class="fas fa-plus text-gray-400 text-xl mb-1"></i>
        <span class="text-xs text-gray-500">Ajouter ${remaining} photo${remaining > 1 ? 's' : ''}</span>
    `;
    addMore.onclick = () => {
        isAddingMore = true;
        document.getElementById('photos').click();
    };
    preview.appendChild(addMore);
}

function removeImage(index) {
    const input = document.getElementById('photos');
    const dt = new DataTransfer();
    
    for (let i = 0; i < input.files.length; i++) {
        if (i !== index) {
            dt.items.add(input.files[i]);
        }
    }
    
    input.files = dt.files;
    existingFiles = Array.from(dt.files);
    previewImages(input);
}

// Drag and drop functionality
const uploadArea = document.getElementById('upload-area');
const mainPhotoInput = document.getElementById('main_photo');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    uploadArea.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    uploadArea.addEventListener(eventName, unhighlight, false);
});

function highlight(e) {
    uploadArea.classList.add('border-green-500', 'bg-green-50');
}

function unhighlight(e) {
    uploadArea.classList.remove('border-green-500', 'bg-green-50');
}

uploadArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    
    if (files.length > 0) {
        mainPhotoInput.files = files;
        previewMainImage(mainPhotoInput);
    }
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