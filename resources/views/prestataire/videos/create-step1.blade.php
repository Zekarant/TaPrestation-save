@extends('layouts.app')

@section('content')
<div class="bg-gray-50">
    <div class="container mx-auto px-2 sm:px-4 md:px-6 py-3 sm:py-4 md:py-6">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="mb-4 sm:mb-6 text-center">
                <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-gray-900 mb-1 sm:mb-2">Créer une nouvelle vidéo</h1>
                <p class="text-xs sm:text-sm md:text-base text-gray-700">Étape 1 : Importation de la vidéo</p>
            </div>
            
            <!-- Indicateur d'étapes -->
            <div class="bg-white rounded-lg sm:rounded-xl shadow-lg border border-gray-200 p-3 sm:p-4 md:p-5 mb-3 sm:mb-4 md:mb-5">
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <a href="{{ route('prestataire.videos.manage') }}" class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                            <i class="fas fa-arrow-left text-base sm:text-lg md:text-xl"></i>
                        </a>
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-gray-900">Étape 1 sur 2</h2>
                            <p class="text-xs sm:text-sm text-gray-700 hidden sm:block">Importation de la vidéo</p>
                        </div>
                    </div>
                </div>
                
                <!-- Barre de progression -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-1 sm:space-x-2 w-full overflow-x-auto">
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-5 h-5 sm:w-6 sm:h-6 md:w-7 md:h-7 bg-gray-800 text-white rounded-full flex items-center justify-center text-[10px] sm:text-xs font-bold">
                                1
                            </div>
                            <span class="ml-1 text-[10px] sm:text-xs font-medium text-gray-600 hidden sm:inline">Importation</span>
                            <span class="ml-1 text-[10px] font-medium text-gray-600 sm:hidden">Import</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 rounded min-w-3">
                            <div class="h-1 bg-gray-800 rounded" style="width: 50%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-5 h-5 sm:w-6 sm:h-6 md:w-7 md:h-7 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-[10px] sm:text-xs font-bold">
                                2
                            </div>
                            <span class="ml-1 text-[10px] sm:text-xs font-medium text-gray-500 hidden sm:inline">Informations</span>
                            <span class="ml-1 text-[10px] font-medium text-gray-500 sm:hidden">Info</span>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-400 p-2 sm:p-3 md:p-4 mb-3 sm:mb-4 md:mb-5 rounded-r-lg" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-2 sm:ml-3">
                        <h3 class="text-xs sm:text-sm font-medium text-red-800">Veuillez corriger les erreurs suivantes :</h3>
                        <div class="mt-1 text-xs sm:text-sm text-red-700">
                            <ul class="list-disc pl-4 sm:pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('prestataire.videos.create.step1.store') }}" enctype="multipart/form-data" id="step1Form">
                @csrf

                <!-- Importation de la vidéo -->
                <div class="bg-white rounded-lg sm:rounded-xl shadow-lg border border-gray-200 p-3 sm:p-4 md:p-5 mb-3 sm:mb-4 md:mb-5">
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 mb-2 sm:mb-3 border-b border-gray-200 pb-1.5 sm:pb-2">
                        <i class="fas fa-file-video text-gray-600 mr-1 text-xs sm:text-sm"></i>Importation de la vidéo
                    </h2>
                    
                    <!-- Video constraints information -->
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-2 sm:p-3 rounded mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-2 sm:ml-3">
                                <h3 class="text-xs sm:text-sm font-medium text-blue-800">Contraintes de la vidéo</h3>
                                <div class="mt-1 text-xs sm:text-sm text-blue-700">
                                    <ul class="list-disc pl-4 sm:pl-5 space-y-1">
                                        <li>La durée maximale autorisée est de <strong>60 secondes</strong></li>
                                        <li>La taille maximale autorisée est de <strong>100 Mo</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4 sm:space-y-5">
                        <div class="bg-gray-50 rounded-lg sm:rounded-xl p-4 sm:p-5 md:p-6 border-2 border-gray-200">
                            <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-3 sm:mb-4">Importer une vidéo</h3>
                            
                            <!-- Mobile-specific options -->
                            <div class="mb-4 sm:mb-5 md:mb-6 md:hidden">
                                <p class="text-gray-700 font-medium text-xs sm:text-sm mb-3 text-center">Choisissez une option :</p>
                                <div class="flex flex-col space-y-3">
                                    <!-- Import from files button -->
                                    <button type="button" id="importFromFileBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 text-sm flex items-center justify-center">
                                        <i class="fas fa-folder mr-2"></i>
                                        Choisir depuis vos fichiers
                                    </button>
                                    
                                    <!-- Record new video button -->
                                    <button type="button" id="recordVideoBtn" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 text-sm flex items-center justify-center">
                                        <i class="fas fa-video mr-2"></i>
                                        Prendre une vidéo avec votre appareil
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Desktop drag and drop area -->
                            <div class="file-upload-area bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-4 sm:p-5 md:p-6 text-center hover:bg-gray-200 transition duration-200 cursor-pointer hidden md:block" id="drop-area">
                                <input type="file" name="video" id="video" accept="video/*" capture="environment" style="display: none;">
                                <i class="fas fa-cloud-upload-alt text-gray-600 text-2xl sm:text-3xl md:text-4xl mb-3 sm:mb-4"></i>
                                <p class="text-gray-700 font-medium text-xs sm:text-sm mb-1 sm:mb-2">Choisir une vidéo depuis vos fichiers</p>
                                <p class="text-gray-700 font-medium text-xs sm:text-sm mb-1 sm:mb-2">ou</p>
                                <p class="text-gray-700 font-medium text-xs sm:text-sm mb-3 sm:mb-4">prendre une vidéo avec votre appareil</p>
                                <p id="file-name" class="font-bold text-gray-800 text-xs sm:text-sm" style="margin-top: 0.5rem;"></p>
                                <div class="text-gray-600 text-[10px] sm:text-xs mt-3 sm:mt-4">
                                    Taille maximale : 100MB
                                </div>
                            </div>
                            
                            <!-- Hidden input for mobile -->
                            <input type="file" id="video-mobile" accept="video/*" capture="environment" style="display: none;">
                            
                            <video id="video-preview" controls style="display:none; width: 100%; margin-top: 0.5rem;" class="rounded-lg"></video>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center pt-3 sm:pt-4 md:pt-5 border-t border-gray-200 space-y-2 sm:space-y-0 sm:space-x-2">
                    <a href="{{ route('prestataire.videos.manage') }}" class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-3 py-2 sm:px-4 sm:py-2.5 md:px-5 md:py-3 rounded-lg transition duration-200 font-medium text-center text-xs sm:text-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Retour
                    </a>
                    <button type="submit" class="w-full sm:w-auto bg-gray-800 hover:bg-gray-900 text-white px-3 py-2 sm:px-4 sm:py-2.5 md:px-6 md:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-xs sm:text-sm" id="submit-btn" disabled>
                        <span class="hidden sm:inline">Suivant : Informations de base</span>
                        <span class="sm:hidden">Suivant</span>
                        <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const importFromFileBtn = document.getElementById('importFromFileBtn');
    const recordVideoBtn = document.getElementById('recordVideoBtn');
    const videoInputMobile = document.getElementById('video-mobile');
    const dropArea = document.getElementById('drop-area');
    const videoInput = document.getElementById('video');
    const fileNameDisplay = document.getElementById('file-name');
    const submitBtn = document.getElementById('submit-btn');
    const videoPreview = document.getElementById('video-preview');

    // Mobile button handlers
    if (importFromFileBtn) {
        importFromFileBtn.addEventListener('click', function() {
            // Trigger file selection without camera
            videoInputMobile.removeAttribute('capture');
            videoInputMobile.click();
        });
    }

    if (recordVideoBtn) {
        recordVideoBtn.addEventListener('click', function() {
            // Trigger camera capture
            videoInputMobile.setAttribute('capture', 'environment');
            videoInputMobile.click();
        });
    }

    // Desktop drag and drop
    if (dropArea) {
        dropArea.addEventListener('click', (event) => {
            event.stopPropagation();
            videoInput.click();
        });

        dropArea.addEventListener('dragover', (event) => {
            event.preventDefault();
            dropArea.style.borderColor = '#4b5563';
        });

        dropArea.addEventListener('dragleave', () => {
            dropArea.style.borderColor = '#e5e7eb';
        });

        dropArea.addEventListener('drop', (event) => {
            event.preventDefault();
            dropArea.style.borderColor = '#e5e7eb';
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                videoInput.files = files;
                handleFileUpload({ target: { files } });
            }
        });
    }

    // File input change handlers
    if (videoInput) {
        videoInput.addEventListener('change', handleFileUpload);
    }

    if (videoInputMobile) {
        videoInputMobile.addEventListener('change', handleFileUpload);
    }

    function handleFileUpload(event) {
        const file = event.target.files[0];
        
        if (file) {
            fileNameDisplay.textContent = file.name;
            const objectURL = URL.createObjectURL(file);
            videoPreview.src = objectURL;
            videoPreview.style.display = 'block';
            
            // Copy file to the main video input for form submission
            if (event.target === videoInputMobile) {
                // Copy file from mobile input to main video input
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                videoInput.files = dataTransfer.files;
            }
            
            videoPreview.onloadedmetadata = function() {
                // Check if duration is available and valid
                if (isFinite(videoPreview.duration) && videoPreview.duration > 0) {
                    if (videoPreview.duration > 60) { // 60 seconds max
                        alert('La vidéo ne doit pas dépasser 60 secondes. Durée détectée: ' + videoPreview.duration.toFixed(2) + ' secondes.');
                        videoInput.value = '';
                        videoInputMobile.value = '';
                        fileNameDisplay.textContent = '';
                        videoPreview.style.display = 'none';
                        submitBtn.disabled = true;
                        return;
                    }
                    // Duration is valid and within limits
                    submitBtn.disabled = false;
                } else {
                    // Duration could not be determined, but we'll allow submission
                    // The server-side validation will handle this more thoroughly
                    submitBtn.disabled = false;
                }
            };
            
            // Handle case where metadata loading fails
            videoPreview.onerror = function() {
                // Still allow submission even if preview fails
                submitBtn.disabled = false;
            };
        } else {
            fileNameDisplay.textContent = '';
            videoPreview.style.display = 'none';
            submitBtn.disabled = true;
        }
    }
});
</script>
@endpush
