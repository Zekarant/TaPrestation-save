@extends('layouts.app')

@section('title', 'Demande de vérification')

@section('content')
<div class="min-h-screen bg-orange-50">
    <div class="container mx-auto py-4 sm:py-6 md:py-8 px-2 sm:px-4">
        <!-- En-tête amélioré -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-500 to-orange-700 rounded-xl shadow-lg">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-orange-900 mb-1 sm:mb-2">
                        Nouvelle demande de vérification
                    </h1>
                    <p class="text-sm sm:text-base md:text-lg text-orange-700">Soumettez vos documents pour faire vérifier votre compte</p>
                </div>
            </div>
            
            <!-- Bouton retour -->
            <div class="mt-4 sm:mt-6">
                <a href="{{ route('prestataire.verification.index') }}" 
                   class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 border border-orange-300 rounded-xl text-xs sm:text-sm font-bold text-orange-700 bg-white hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="hidden sm:inline">Retour à la liste</span>
                    <span class="sm:hidden">Retour</span>
                </a>
            </div>
        </div>

        <!-- Carte principale -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300">
            <!-- Information importante -->
            <div class="bg-orange-50 border-b border-orange-200 p-4 sm:p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-base sm:text-lg font-semibold text-orange-900 mb-2">Information importante</h3>
                        <p class="text-orange-700 text-xs sm:text-sm leading-relaxed">
                            Soumettez des documents officiels pour faire vérifier votre compte. 
                            Les documents acceptés incluent : pièce d'identité, certificats professionnels, 
                            patente d'entreprise, etc. Tous les documents doivent être lisibles et en cours de validité.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Formulaire -->
            <div class="p-4 sm:p-8">
                <form action="{{ route('prestataire.verification.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 sm:space-y-8">
                    @csrf
                    
                    <!-- Type de document -->
                    <div>
                        <label for="document_type" class="block text-xs sm:text-sm font-semibold text-gray-900 mb-2 sm:mb-3">
                            Type de document
                            <span class="text-red-500 ml-1">*</span>
                        </label>
                        <div class="relative">
                            <select name="document_type" id="document_type" 
                                    class="block w-full px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-300 @error('document_type') border-red-300 ring-red-500 @enderror text-sm sm:text-base" 
                                    required>
                                <option value="">Sélectionnez le type de document</option>
                                <option value="identity" {{ old('document_type') == 'identity' ? 'selected' : '' }}>
                                    Pièce d'identité (CIN, Passeport)
                                </option>
                                <option value="professional" {{ old('document_type') == 'professional' ? 'selected' : '' }}>
                                    Document professionnel (Certificat, Diplôme)
                                </option>
                                <option value="business" {{ old('document_type') == 'business' ? 'selected' : '' }}>
                                    Document d'entreprise (Patente, RC)
                                </option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        @error('document_type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Documents justificatifs -->
                    <div>
                        <label for="documents" class="block text-xs sm:text-sm font-semibold text-gray-900 mb-2 sm:mb-3">
                            Documents justificatifs
                            <span class="text-red-500 ml-1">*</span>
                        </label>
                        
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 sm:p-8 text-center hover:border-orange-400 transition-all duration-300 @error('documents') border-red-300 @enderror @error('documents.*') border-red-300 @enderror">
                            <div class="space-y-3 sm:space-y-4">
                                <div class="flex justify-center">
                                    <svg class="w-8 h-8 sm:w-12 sm:h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <div>
                                    <label for="documents" class="cursor-pointer">
                                        <span class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 border border-transparent rounded-lg text-sm sm:text-base font-medium text-white bg-orange-600 hover:bg-orange-700 transition-all duration-300">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            <span class="hidden sm:inline">Choisir les fichiers</span>
                                            <span class="sm:hidden">Choisir</span>
                                        </span>
                                    </label>
                                    <input type="file" name="documents[]" id="documents" 
                                           class="hidden" 
                                           multiple accept=".pdf,.jpg,.jpeg,.png" required>
                                </div>
                                <div class="text-xs sm:text-sm text-gray-500">
                                    <p>Formats acceptés : PDF, JPG, JPEG, PNG</p>
                                    <p class="hidden sm:block">Taille maximale : 5 MB par fichier • Maximum 5 fichiers</p>
                                    <p class="sm:hidden">Max : 5 MB • 5 fichiers</p>
                                </div>
                            </div>
                        </div>
                        
                        @error('documents')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('documents.*')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Prévisualisation des fichiers -->
                    <div id="file-preview" class="hidden">
                        <h4 class="text-sm font-semibold text-gray-900 mb-4">Fichiers sélectionnés</h4>
                        <div id="file-preview-content" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>
                    </div>

                    <!-- Conditions importantes -->
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 sm:p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-base sm:text-lg font-semibold text-orange-900 mb-2 sm:mb-3">Conditions importantes</h4>
                                <ul class="space-y-1 sm:space-y-2 text-xs sm:text-sm text-orange-800">
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Tous les documents doivent être lisibles et en cours de validité
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Les informations doivent correspondre à celles de votre profil
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Les documents seront examinés par notre équipe sous 48-72h
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Vous recevrez une notification du résultat de la vérification
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        En cas de rejet, vous pourrez soumettre une nouvelle demande
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Acceptation des conditions -->
                    <div class="bg-gray-50 rounded-lg p-4 sm:p-6">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" id="terms" 
                                       class="w-4 h-4 sm:w-5 sm:h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 focus:ring-2" 
                                       required>
                            </div>
                            <div class="ml-3">
                                <label for="terms" class="text-xs sm:text-sm text-gray-700 leading-relaxed cursor-pointer">
                                    Je certifie que les documents soumis sont authentiques et que les informations 
                                    fournies sont exactes. Je comprends que toute fausse déclaration peut entraîner 
                                    la suspension de mon compte.
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex flex-col sm:flex-row items-center justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-4 sm:pt-6 border-t border-gray-200">
                        <a href="{{ route('prestataire.verification.index') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-4 sm:px-6 py-2 sm:py-3 border border-orange-300 rounded-lg text-xs sm:text-sm font-medium text-orange-700 bg-white hover:bg-orange-50 transition-all duration-300">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Annuler
                        </a>
                        <button type="submit" 
                                class="w-full sm:w-auto inline-flex items-center justify-center px-6 sm:px-8 py-2 sm:py-3 border border-transparent rounded-lg text-xs sm:text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all duration-300">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            <span class="hidden sm:inline">Soumettre la demande</span>
                            <span class="sm:hidden">Soumettre</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Mise à jour de l'affichage lors de la sélection de fichiers
    $('#documents').on('change', function() {
        var files = this.files;
        showFilePreview(files);
    });
    
    function showFilePreview(files) {
        var preview = $('#file-preview');
        var previewContent = $('#file-preview-content');
        previewContent.empty();
        
        if (files.length === 0) {
            preview.addClass('hidden');
            return;
        }
        
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
            var fileType = file.type;
            var fileName = file.name;
            
            var icon = 'text-gray-500';
            var iconPath = 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
            
            if (fileType.includes('pdf')) {
                icon = 'text-red-500';
                iconPath = 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
            } else if (fileType.includes('image')) {
                icon = 'text-blue-500';
                iconPath = 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z';
            }
            
            var fileCard = `
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all duration-300">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 ${icon}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate" title="${fileName}">${fileName}</p>
                            <p class="text-xs text-gray-500">${fileSize} MB</p>
                        </div>
                    </div>
                </div>
            `;
            
            previewContent.append(fileCard);
        }
        
        preview.removeClass('hidden');
    }
    
    // Validation du formulaire
    $('form').on('submit', function(e) {
        var files = $('#documents')[0].files;
        var documentType = $('#document_type').val();
        var terms = $('#terms').is(':checked');
        
        if (!documentType) {
            e.preventDefault();
            alert('Veuillez sélectionner le type de document.');
            return false;
        }
        
        if (files.length === 0) {
            e.preventDefault();
            alert('Veuillez sélectionner au moins un document.');
            return false;
        }
        
        if (files.length > 5) {
            e.preventDefault();
            alert('Vous ne pouvez pas télécharger plus de 5 fichiers.');
            return false;
        }
        
        // Vérifier la taille des fichiers
        for (var i = 0; i < files.length; i++) {
            if (files[i].size > 5 * 1024 * 1024) { // 5MB
                e.preventDefault();
                alert('Le fichier "' + files[i].name + '" dépasse la taille maximale de 5 MB.');
                return false;
            }
        }
        
        if (!terms) {
            e.preventDefault();
            alert('Veuillez accepter les conditions.');
            return false;
        }
        
        // Afficher un indicateur de chargement
        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html(`
            <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Envoi en cours...
        `);
    });
});
</script>
@endpush
@endsection