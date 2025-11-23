@extends('layouts.app')

@section('content')
<div class="bg-blue-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="mb-4 sm:mb-6 lg:mb-8 text-center">
                <h1 class="text-xl sm:text-2xl lg:text-3xl xl:text-4xl font-extrabold text-blue-900 mb-1 sm:mb-2">Créer un nouveau service</h1>
                <p class="text-sm sm:text-base lg:text-lg text-blue-700">Étape 3 : Photos</p>
            </div>

            <!-- Indicateur d'étapes -->
            <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <div class="flex items-center space-x-2 sm:space-x-3 lg:space-x-4">
                        <a href="{{ route('prestataire.services.create.step2') }}" class="text-blue-600 hover:text-blue-900 transition-colors duration-200">
                            <i class="fas fa-arrow-left text-base sm:text-lg lg:text-xl"></i>
                        </a>
                        <div>
                            <h2 class="text-base sm:text-lg lg:text-xl font-bold text-blue-900">Étape 3 sur 4</h2>
                            <p class="text-xs sm:text-sm lg:text-base text-blue-700 hidden sm:block">Photos</p>
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
                            <div class="h-1 bg-green-600 rounded" style="width: 100%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                <i class="fas fa-check text-xs"></i>
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-600 hidden sm:inline">Prix & Catégorie</span>
                            <span class="ml-1 text-xs font-medium text-green-600 sm:hidden">Prix</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 rounded min-w-4">
                            <div class="h-1 bg-blue-600 rounded" style="width: 75%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                3
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-blue-600 hidden sm:inline">Photos</span>
                            <span class="ml-1 text-xs font-medium text-blue-600 sm:hidden">Photo</span>
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

            <form method="POST" action="{{ route('prestataire.services.create.step3.store') }}" enctype="multipart/form-data" id="step3Form">
                @csrf

                <!-- Photos -->
                <div class="bg-white rounded-xl shadow-lg border border-blue-200 p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
                    <h2 class="text-base sm:text-lg lg:text-xl font-bold text-blue-900 mb-3 sm:mb-4 border-b border-blue-200 pb-2">
                        <i class="fas fa-camera text-purple-600 mr-1 sm:mr-2 text-sm sm:text-base"></i>Photos de votre service
                    </h2>
                    
                    <div class="mb-3 sm:mb-4">
                        <p class="text-xs sm:text-sm text-blue-700 mb-2">
                            <i class="fas fa-info-circle mr-1 text-xs sm:text-sm"></i>
                            Ajoutez des photos de qualité pour présenter votre service. Les photos aident les clients à mieux comprendre ce que vous proposez.
                        </p>
                        <div class="bg-blue-50 p-2 sm:p-3 rounded-lg">
                            <h4 class="font-semibold text-blue-800 mb-1 sm:mb-2 text-xs sm:text-sm">Conseils pour de bonnes photos :</h4>
                            <ul class="text-xs sm:text-sm text-blue-700 space-y-0.5 sm:space-y-1">
                                <li>• Utilisez un bon éclairage naturel</li>
                                <li>• Montrez votre travail sous différents angles</li>
                                <li>• Incluez des photos avant/après si applicable</li>
                                <li>• Évitez les photos floues ou sombres</li>
                                <li>• Vous pouvez réorganiser l'ordre en glissant-déposant les photos</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="border-2 border-dashed border-blue-300 rounded-lg p-3 sm:p-4 lg:p-6 text-center bg-blue-50 hover:border-blue-400 transition-colors">
                        <input type="file" id="images" name="images[]" multiple accept="image/*" class="hidden" onchange="previewImages(this)">
                        <div id="upload-area" class="cursor-pointer" onclick="document.getElementById('images').click()">
                            <i class="fas fa-cloud-upload-alt text-blue-400 text-2xl sm:text-3xl lg:text-4xl mb-2 sm:mb-3 lg:mb-4"></i>
                            <p class="text-blue-600 mb-1 sm:mb-2 text-xs sm:text-sm lg:text-base font-semibold">Cliquez pour ajouter des photos ou glissez-déposez</p>
                            <p class="text-blue-500 text-xs sm:text-sm">Maximum 5 photos • Formats acceptés : JPG, PNG, GIF • 5MB max par photo</p>
                        </div>
                        
                        <!-- Aperçu des images sélectionnées -->
                        <div id="image-preview" class="hidden mt-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4"></div>
                    </div>
                    
                    @error('images')
                        <p class="text-red-500 text-xs sm:text-sm mt-1 sm:mt-2">{{ $message }}</p>
                    @enderror
                    @error('images.*')
                        <p class="text-red-500 text-xs sm:text-sm mt-1 sm:mt-2">{{ $message }}</p>
                    @enderror
                    
                    <div class="mt-3 sm:mt-4 p-2 sm:p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-xs sm:text-sm text-yellow-800">
                            <i class="fas fa-lightbulb mr-1 text-xs sm:text-sm"></i>
                            <strong>Optionnel :</strong> Vous pouvez passer cette étape et ajouter des photos plus tard depuis votre tableau de bord.
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row justify-between items-center pt-4 sm:pt-6 lg:pt-8 border-t border-blue-200 space-y-3 sm:space-y-0">
                    <a href="{{ route('prestataire.services.create.step2') }}" class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-3 sm:px-4 lg:px-6 py-2.5 sm:py-3 rounded-lg transition duration-200 font-medium text-center text-xs sm:text-sm lg:text-base">
                        <i class="fas fa-arrow-left mr-1 sm:mr-2 text-xs sm:text-sm"></i><span class="hidden xs:inline">Précédent</span><span class="xs:hidden">Retour</span>
                    </a>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 lg:space-x-3 w-full sm:w-auto">
                        <button type="button" onclick="skipPhotos()" class="w-full sm:w-auto bg-yellow-500 hover:bg-yellow-600 text-white px-3 sm:px-4 lg:px-6 py-2.5 sm:py-3 rounded-lg transition duration-200 font-medium text-xs sm:text-sm lg:text-base">
                            <i class="fas fa-forward mr-1 sm:mr-2 text-xs sm:text-sm"></i><span class="hidden xs:inline">Passer cette étape</span><span class="xs:hidden">Passer</span>
                        </button>
                        <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 sm:px-6 lg:px-8 py-2.5 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-xs sm:text-sm lg:text-base">
                            <span class="hidden xs:inline">Suivant : Localisation</span><span class="xs:hidden">Suivant</span><i class="fas fa-arrow-right ml-1 sm:ml-2 text-xs sm:text-sm"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Image Preview
    const imageInput = document.getElementById('images');
    const previewContainer = document.getElementById('image-preview');
    const uploadArea = document.getElementById('upload-area');

    // Variable pour stocker les fichiers existants
    let existingFiles = [];
    let isAddingMore = false;
    
    window.previewImages = function(input) {
        // Si on ajoute des images supplémentaires, combiner avec les existantes
        if (isAddingMore && existingFiles.length > 0) {
            const newFiles = Array.from(input.files);
            const combinedFiles = new DataTransfer();
            
            // Ajouter les fichiers existants
            existingFiles.forEach(file => {
                if (combinedFiles.files.length < 5) {
                    combinedFiles.items.add(file);
                }
            });
            
            // Ajouter les nouveaux fichiers
            newFiles.forEach(file => {
                if (combinedFiles.files.length < 5) {
                    combinedFiles.items.add(file);
                }
            });
            
            input.files = combinedFiles.files;
            isAddingMore = false;
        }
        
        // Prevent form resubmission
        const form = document.getElementById('step3Form');
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
        
        // Stocker les fichiers actuels
        existingFiles = Array.from(input.files);
        
        previewContainer.innerHTML = '';
        if (input.files && input.files.length > 0) {
            previewContainer.classList.remove('hidden');
            uploadArea.classList.add('hidden');
            
            const files = Array.from(input.files).slice(0, 5);
            const validImageFiles = files.filter(file => file.type.startsWith('image/'));
            let loadedImages = 0;
            
            validImageFiles.forEach((file, index) => {
                // Try using URL.createObjectURL instead of FileReader
                const div = document.createElement('div');
                div.className = 'relative group bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-all duration-200 cursor-move';
                div.draggable = true;
                div.dataset.index = index;
                
                // Create image with URL.createObjectURL
                const img = document.createElement('img');
                const imageUrl = URL.createObjectURL(file);
                
                img.onload = function() {
                    console.log('Image loaded successfully with createObjectURL');
                    URL.revokeObjectURL(imageUrl); // Clean up memory
                };
                img.onerror = function() {
                    console.error('Failed to load image with createObjectURL');
                    URL.revokeObjectURL(imageUrl);
                };
                
                img.src = imageUrl;
                img.className = 'w-full h-24 sm:h-28 lg:h-32 object-cover';
                img.style.display = 'block';
                
                // Create overlay elements
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.onclick = () => removeImage(index);
                removeBtn.className = 'absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-7 h-7 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-all duration-200 shadow-lg hover:scale-110 z-10';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                
                const photoLabel = document.createElement('div');
                photoLabel.className = 'absolute bottom-2 left-2 bg-blue-600 text-white text-xs px-2 py-1 rounded-full font-medium';
                photoLabel.textContent = `Photo ${index + 1}`;
                
                const dragIcon = document.createElement('div');
                dragIcon.className = 'absolute top-2 left-2 bg-gray-800 bg-opacity-75 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-all duration-200';
                dragIcon.innerHTML = '<i class="fas fa-arrows-alt"></i>';
                
                // Append all elements
                 div.appendChild(img);
                 div.appendChild(removeBtn);
                 div.appendChild(photoLabel);
                 div.appendChild(dragIcon);
                 
                 // Ajouter les événements de drag and drop pour la réorganisation
                 div.addEventListener('dragstart', handleDragStart);
                 div.addEventListener('dragover', handleDragOver);
                 div.addEventListener('drop', handleImageDrop);
                 div.addEventListener('dragend', handleDragEnd);
                 
                 previewContainer.appendChild(div);
                 
                 loadedImages++;
                 
                 // Ajouter le bouton "Ajouter plus" après que toutes les images valides soient chargées
                 if (loadedImages === validImageFiles.length) {
                     if (validImageFiles.length < 5) {
                         addMoreButton(validImageFiles.length);
                     }
                 }
            });
            
            // Si aucun fichier n'est une image valide
            if (validImageFiles.length === 0) {
                resetDisplay();
                showNotification('Aucune image valide', ['Veuillez sélectionner des fichiers image (JPG, PNG, GIF, WebP)'], 'error');
            }

        } else {
            resetDisplay();
        }
    }
    
    // Fonction pour ajouter le bouton "Ajouter plus"
    function addMoreButton(currentCount) {
        if (currentCount < 5) {
            const addMore = document.createElement('div');
            addMore.className = 'flex items-center justify-center h-24 sm:h-28 lg:h-32 border-2 border-dashed border-blue-300 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all duration-200 bg-gray-50';
            addMore.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-plus text-blue-400 text-xl sm:text-2xl mb-1 sm:mb-2"></i>
                    <p class="text-xs sm:text-sm text-blue-600 font-medium">Ajouter une photo</p>
                    <p class="text-xs text-gray-500 mt-1">${5 - currentCount} restante(s)</p>
                </div>
            `;
            addMore.onclick = () => {
                isAddingMore = true;
                imageInput.click();
            };
            previewContainer.appendChild(addMore);
        }
    }
    
    // Fonction pour réinitialiser l'affichage
    function resetDisplay() {
        previewContainer.classList.add('hidden');
        uploadArea.classList.remove('hidden');
    }

    window.removeImage = function(index) {
        const dt = new DataTransfer();
        const files = imageInput.files;
        for (let i = 0; i < files.length; i++) {
            if (i !== index) {
                dt.items.add(files[i]);
            }
        }
        imageInput.files = dt.files;
        existingFiles = Array.from(imageInput.files);
        previewImages(imageInput);
        
        // Afficher une notification de suppression
        showNotification('Photo supprimée', ['La photo a été retirée de votre sélection'], 'success');
    }
    
    // Variables pour la réorganisation des images
    let draggedElement = null;
    let draggedIndex = null;
    
    // Fonctions pour la réorganisation des photos
    function handleDragStart(e) {
        draggedElement = this;
        draggedIndex = parseInt(this.dataset.index);
        this.style.opacity = '0.5';
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.outerHTML);
    }
    
    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        e.dataTransfer.dropEffect = 'move';
        return false;
    }
    
    function handleImageDrop(e) {
        if (e.stopPropagation) {
            e.stopPropagation();
        }
        
        if (draggedElement !== this) {
            const targetIndex = parseInt(this.dataset.index);
            reorderImages(draggedIndex, targetIndex);
        }
        
        return false;
    }
    
    function handleDragEnd(e) {
        this.style.opacity = '1';
        draggedElement = null;
        draggedIndex = null;
    }
    
    function reorderImages(fromIndex, toIndex) {
        const files = Array.from(imageInput.files);
        const movedFile = files.splice(fromIndex, 1)[0];
        files.splice(toIndex, 0, movedFile);
        
        const dt = new DataTransfer();
        files.forEach(file => dt.items.add(file));
        imageInput.files = dt.files;
        
        previewImages(imageInput);
        showNotification('Photos réorganisées', ['L\'ordre des photos a été modifié'], 'success');
    }
    
    // Fonction pour passer l'étape photos
    window.skipPhotos = function() {
        // Créer un formulaire temporaire pour passer à l'étape suivante sans photos
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("prestataire.services.create.step3.store") }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const skipInput = document.createElement('input');
        skipInput.type = 'hidden';
        skipInput.name = 'skip_photos';
        skipInput.value = '1';
        
        form.appendChild(csrfToken);
        form.appendChild(skipInput);
        document.body.appendChild(form);
        form.submit();
    }
    
    // Validation des fichiers avec feedback amélioré
    imageInput.addEventListener('change', function() {
        const files = this.files;
        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        let hasErrors = false;
        let errorMessages = [];
        
        // Vérifier le nombre de fichiers
        if (files.length > 5) {
            errorMessages.push('Vous ne pouvez sélectionner que 5 photos maximum.');
            hasErrors = true;
        }
        
        for (let i = 0; i < files.length && i < 5; i++) {
            const file = files[i];
            
            // Vérifier le type de fichier
            if (!allowedTypes.includes(file.type)) {
                errorMessages.push(`"${file.name}" : Format non supporté. Utilisez JPG, PNG, GIF ou WebP.`);
                hasErrors = true;
            }
            
            // Vérifier la taille du fichier
            if (file.size > maxSize) {
                const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
                errorMessages.push(`"${file.name}" : Fichier trop volumineux (${sizeMB}MB). Maximum 5MB.`);
                hasErrors = true;
            }
        }
        
        if (hasErrors) {
            // Afficher les erreurs dans une notification plus élégante
            showNotification('Erreur de validation', errorMessages, 'error');
            this.value = '';
            return;
        }
        
        // Afficher un message de succès si tout va bien
        if (files.length > 0) {
            const message = files.length === 1 ? '1 photo sélectionnée' : `${files.length} photos sélectionnées`;
            showNotification('Photos ajoutées', [message], 'success');
        }
    });
    
    // Fonction pour afficher des notifications
    function showNotification(title, messages, type) {
        const notification = document.createElement('div');
        const bgColor = type === 'error' ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200';
        const textColor = type === 'error' ? 'text-red-800' : 'text-green-800';
        const iconClass = type === 'error' ? 'fas fa-exclamation-triangle text-red-500' : 'fas fa-check-circle text-green-500';
        
        notification.className = `fixed top-4 right-4 max-w-md ${bgColor} border rounded-lg p-4 shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
        notification.innerHTML = `
            <div class="flex items-start">
                <i class="${iconClass} mt-0.5 mr-3"></i>
                <div class="flex-1">
                    <h4 class="font-semibold ${textColor} mb-1">${title}</h4>
                    <ul class="text-sm ${textColor} space-y-1">
                        ${messages.map(msg => `<li>• ${msg}</li>`).join('')}
                    </ul>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 ${textColor} hover:opacity-70">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animation d'entrée
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Suppression automatique après 5 secondes
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }
    
    // Drag and drop functionality amélioré
    const dropZone = document.querySelector('.border-dashed').parentElement;
    let dragCounter = 0;
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    dropZone.addEventListener('dragenter', function(e) {
        dragCounter++;
        highlight();
    });
    
    dropZone.addEventListener('dragleave', function(e) {
        dragCounter--;
        if (dragCounter === 0) {
            unhighlight();
        }
    });
    
    dropZone.addEventListener('dragover', function(e) {
        e.dataTransfer.dropEffect = 'copy';
    });
    
    dropZone.addEventListener('drop', function(e) {
        dragCounter = 0;
        unhighlight();
        handleDrop(e);
    });
    
    function highlight() {
        const uploadArea = document.getElementById('upload-area');
        const previewArea = document.getElementById('image-preview');
        
        dropZone.classList.add('ring-2', 'ring-blue-500', 'ring-opacity-50', 'bg-blue-50');
        
        if (!uploadArea.classList.contains('hidden')) {
            uploadArea.classList.add('border-blue-500', 'bg-blue-100');
            uploadArea.querySelector('i').classList.add('text-blue-600');
        }
        
        // Créer un overlay de drop si des images sont déjà présentes
        if (!previewArea.classList.contains('hidden')) {
            let overlay = document.getElementById('drop-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'drop-overlay';
                overlay.className = 'absolute inset-0 bg-blue-500 bg-opacity-20 border-2 border-dashed border-blue-500 rounded-lg flex items-center justify-center z-10';
                overlay.innerHTML = `
                    <div class="text-center text-blue-700">
                        <i class="fas fa-cloud-upload-alt text-4xl mb-2"></i>
                        <p class="font-semibold">Déposez vos nouvelles photos ici</p>
                    </div>
                `;
                dropZone.style.position = 'relative';
                dropZone.appendChild(overlay);
            }
        }
    }
    
    function unhighlight() {
        const uploadArea = document.getElementById('upload-area');
        const overlay = document.getElementById('drop-overlay');
        
        dropZone.classList.remove('ring-2', 'ring-blue-500', 'ring-opacity-50', 'bg-blue-50');
        
        if (!uploadArea.classList.contains('hidden')) {
            uploadArea.classList.remove('border-blue-500', 'bg-blue-100');
            uploadArea.querySelector('i').classList.remove('text-blue-600');
        }
        
        if (overlay) {
            overlay.remove();
        }
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            // Combiner les nouveaux fichiers avec les existants si il y en a
            const existingFiles = imageInput.files;
            const combinedFiles = new DataTransfer();
            
            // Ajouter les fichiers existants
            for (let i = 0; i < existingFiles.length && combinedFiles.files.length < 5; i++) {
                combinedFiles.items.add(existingFiles[i]);
            }
            
            // Ajouter les nouveaux fichiers
            for (let i = 0; i < files.length && combinedFiles.files.length < 5; i++) {
                combinedFiles.items.add(files[i]);
            }
            
            imageInput.files = combinedFiles.files;
            previewImages(imageInput);
        }
    }
});
</script>
@endpush