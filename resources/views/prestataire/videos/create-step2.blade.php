@extends('layouts.app')

@section('content')
<div class="bg-gray-50">
    <div class="container mx-auto px-2 sm:px-4 md:px-6 py-3 sm:py-4 md:py-6">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="mb-4 sm:mb-6 text-center">
                <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-gray-900 mb-1 sm:mb-2">Créer une nouvelle vidéo</h1>
                <p class="text-xs sm:text-sm md:text-base text-gray-700">Étape 2 : Informations de base</p>
            </div>
            
            <!-- Indicateur d'étapes -->
            <div class="bg-white rounded-lg sm:rounded-xl shadow-lg border border-gray-200 p-3 sm:p-4 md:p-5 mb-3 sm:mb-4 md:mb-5">
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <a href="{{ route('prestataire.videos.create.step1') }}" class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                            <i class="fas fa-arrow-left text-base sm:text-lg md:text-xl"></i>
                        </a>
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-gray-900">Étape 2 sur 2</h2>
                            <p class="text-xs sm:text-sm text-gray-700 hidden sm:block">Informations de base</p>
                        </div>
                    </div>
                </div>
                
                <!-- Barre de progression -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-1 sm:space-x-2 w-full overflow-x-auto">
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-5 h-5 sm:w-6 sm:h-6 md:w-7 md:h-7 bg-green-600 text-white rounded-full flex items-center justify-center text-[10px] sm:text-xs font-bold">
                                <i class="fas fa-check text-[10px] sm:text-xs"></i>
                            </div>
                            <span class="ml-1 text-[10px] sm:text-xs font-medium text-green-600 hidden sm:inline">Importation</span>
                            <span class="ml-1 text-[10px] font-medium text-green-600 sm:hidden">Import</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 rounded min-w-3">
                            <div class="h-1 bg-green-600 rounded" style="width: 100%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-5 h-5 sm:w-6 sm:h-6 md:w-7 md:h-7 bg-gray-800 text-white rounded-full flex items-center justify-center text-[10px] sm:text-xs font-bold">
                                2
                            </div>
                            <span class="ml-1 text-[10px] sm:text-xs font-medium text-gray-600 hidden sm:inline">Informations</span>
                            <span class="ml-1 text-[10px] font-medium text-gray-600 sm:hidden">Info</span>
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

            <form method="POST" action="{{ route('prestataire.videos.create.step2.store') }}" id="step2Form">
                @csrf

                <!-- Résumé de la vidéo -->
                <div class="bg-white rounded-lg sm:rounded-xl shadow-lg border border-gray-200 p-3 sm:p-4 md:p-5 mb-3 sm:mb-4 md:mb-5">
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 mb-2 sm:mb-3 border-b border-gray-200 pb-1.5 sm:pb-2">
                        <i class="fas fa-file-video text-gray-600 mr-1 text-xs sm:text-sm"></i>Résumé de la vidéo
                    </h2>
                    
                    <div class="space-y-2 sm:space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2 sm:p-3 md:p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div>
                                <h3 class="font-medium text-gray-800 text-xs sm:text-sm">Fichier vidéo importé</h3>
                                <p class="text-[10px] sm:text-xs text-gray-600 mt-1">Le fichier a été correctement téléchargé</p>
                            </div>
                            <a href="{{ route('prestataire.videos.create.step1') }}" class="mt-2 sm:mt-0 text-[10px] sm:text-xs text-gray-600 hover:text-gray-800 font-medium">
                                <i class="fas fa-edit mr-1"></i>Modifier
                            </a>
                        </div>
                        
                        <!-- Video constraints information -->
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-2 sm:p-3 rounded">
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
                    </div>
                </div>

                <!-- Informations de base -->
                <div class="bg-white rounded-lg sm:rounded-xl shadow-lg border border-gray-200 p-3 sm:p-4 md:p-5 mb-3 sm:mb-4 md:mb-5">
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 mb-2 sm:mb-3 border-b border-gray-200 pb-1.5 sm:pb-2">
                        <i class="fas fa-info-circle text-gray-600 mr-1 text-xs sm:text-sm"></i>Informations de base
                    </h2>
                    
                    <div class="space-y-3 sm:space-y-4">
                        <!-- Titre -->
                        <div>
                            <label for="title" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Titre de la vidéo *</label>
                            <input type="text" id="title" name="title" value="{{ old('title') }}" required class="w-full px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 sm:focus:ring-2 focus:ring-gray-500 focus:border-gray-500 @error('title') border-red-500 @enderror">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mt-1 space-y-1 sm:space-y-0">
                                <div class="flex-1">
                                    @error('title')
                                        <p class="text-red-500 text-[10px] sm:text-xs">{{ $message }}</p>
                                    @enderror
                                </div>
                                <p class="text-gray-500 text-[10px] sm:text-xs flex-shrink-0 sm:ml-2"><span id="title-count">0</span> caractères</p>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Description détaillée</label>
                            <textarea id="description" name="description" rows="3" class="w-full px-2 py-1.5 sm:px-3 sm:py-2 text-xs sm:text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-1 sm:focus:ring-2 focus:ring-gray-500 focus:border-gray-500 @error('description') border-red-500 @enderror" placeholder="Décrivez en détail le contenu de votre vidéo...">{{ old('description') }}</textarea>
                            <div class="mt-1">
                                @error('description')
                                    <p class="text-red-500 text-[10px] sm:text-xs">{{ $message }}</p>
                                @enderror
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mt-1 space-y-1 sm:space-y-0">
                                    <p class="text-gray-600 text-[10px] sm:text-xs flex-1">Aucune limite de caractères - décrivez votre vidéo comme vous le souhaitez</p>
                                    <p class="text-gray-500 text-[10px] sm:text-xs flex-shrink-0 sm:ml-2"><span id="description-count">0</span> caractères</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center pt-3 sm:pt-4 md:pt-5 border-t border-gray-200 space-y-2 sm:space-y-0 sm:space-x-2">
                    <a href="{{ route('prestataire.videos.create.step1') }}" class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-3 py-2 sm:px-4 sm:py-2.5 md:px-5 md:py-3 rounded-lg transition duration-200 font-medium text-center text-xs sm:text-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Précédent
                    </a>
                    <button type="submit" class="w-full sm:w-auto bg-gray-800 hover:bg-gray-900 text-white px-3 py-2 sm:px-4 sm:py-2.5 md:px-5 md:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-xs sm:text-sm">
                        <span class="hidden sm:inline">Créer la vidéo</span>
                        <span class="sm:hidden">Créer</span>
                        <i class="fas fa-check ml-1"></i>
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
    // Simple character counter for title
    const titleInput = document.getElementById('title');
    const titleCount = document.getElementById('title-count');

    function updateTitleCount() {
        const length = titleInput.value.length;
        titleCount.textContent = length;
        
        // Always show gray border for any input
        titleInput.classList.remove('border-red-500', 'border-yellow-500', 'border-green-500');
        titleInput.classList.add('border-gray-300');
    }

    if (titleInput) {
        titleInput.addEventListener('input', updateTitleCount);
        updateTitleCount(); // Initial count
    }

    // Character counter for description
    const descriptionInput = document.getElementById('description');
    const descriptionCount = document.getElementById('description-count');

    function updateDescriptionCount() {
        const length = descriptionInput.value.length;
        descriptionCount.textContent = length;
        
        // Always show gray border for any input
        descriptionInput.classList.remove('border-red-500', 'border-yellow-500', 'border-green-500');
        descriptionInput.classList.add('border-gray-300');
    }

    if (descriptionInput) {
        descriptionInput.addEventListener('input', updateDescriptionCount);
        updateDescriptionCount(); // Initial count
    }
});
</script>
@endpush