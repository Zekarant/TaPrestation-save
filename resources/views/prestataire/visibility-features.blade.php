@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Fonctionnalités de Visibilité</h1>
            <p class="mt-2 text-gray-600">Gérez votre portfolio, vos documents et améliorez votre visibilité sur la plateforme.</p>
        </div>
        
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif
        
        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif
        
        <!-- Onglets de navigation -->
        <div class="border-b border-gray-200 mb-8">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button onclick="showTab('portfolio')" id="tab-portfolio" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-images mr-2"></i>
                    Portfolio Multimédia
                </button>
                <button onclick="showTab('documents')" id="tab-documents" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-file-alt mr-2"></i>
                    Documents & Certifications
                </button>

                <button onclick="showTab('qrcode')" id="tab-qrcode" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-qrcode mr-2"></i>
                    QR Code
                </button>
            </nav>
        </div>
        
        <!-- Contenu des onglets -->
        
        <!-- Onglet Portfolio -->
        <div id="content-portfolio" class="tab-content">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">
                    <i class="fas fa-images mr-2 text-blue-600"></i>
                    Gestion du Portfolio Multimédia
                </h2>
                
                <form action="{{ route('prestataire.portfolio.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Description du portfolio -->
                    <div class="mb-6">
                        <label for="portfolio_description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description du portfolio
                        </label>
                        <textarea id="portfolio_description" name="portfolio_description" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Décrivez votre travail, votre style, vos spécialités...">{{ old('portfolio_description', $prestataire->portfolio_description) }}</textarea>
                        @error('portfolio_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Upload d'images -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Images du portfolio
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                            <input type="file" id="portfolio_images" name="portfolio_images[]" multiple accept="image/*" class="hidden">
                            <label for="portfolio_images" class="cursor-pointer">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                                <p class="text-lg font-medium text-gray-900">Cliquez pour ajouter des images</p>
                                <p class="text-sm text-gray-500">PNG, JPG, GIF jusqu'à 5MB chacune (max 10 images)</p>
                            </label>
                        </div>
                        
                        <!-- Aperçu des images existantes -->
                        @if($prestataire->portfolio_images && count($prestataire->portfolio_images) > 0)
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Images actuelles :</h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    @foreach($prestataire->portfolio_images as $index => $image)
                                        <div class="relative group">
                                            <img src="{{ Storage::url($image) }}" alt="Portfolio {{ $index + 1 }}" 
                                                 class="w-full h-24 object-cover rounded-lg">
                                            <button type="button" onclick="removeImage('{{ $image }}', this)" 
                                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Upload de vidéos -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Vidéos du portfolio
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                            <input type="file" id="portfolio_videos" name="portfolio_videos[]" multiple accept="video/*" class="hidden">
                            <label for="portfolio_videos" class="cursor-pointer">
                                <i class="fas fa-video text-4xl text-gray-400 mb-4"></i>
                                <p class="text-lg font-medium text-gray-900">Cliquez pour ajouter des vidéos</p>
                                <p class="text-sm text-gray-500">MP4, MOV, AVI jusqu'à 50MB chacune (max 5 vidéos)</p>
                            </label>
                        </div>
                        
                        <!-- Aperçu des vidéos existantes -->
                        @if($prestataire->portfolio_videos && count($prestataire->portfolio_videos) > 0)
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Vidéos actuelles :</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($prestataire->portfolio_videos as $index => $video)
                                        <div class="relative group">
                                            <video class="w-full h-32 object-cover rounded-lg" controls>
                                                <source src="{{ Storage::url($video) }}" type="video/mp4">
                                            </video>
                                            <button type="button" onclick="removeVideo('{{ $video }}', this)" 
                                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                            <i class="fas fa-save mr-2"></i>
                            Enregistrer le portfolio
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Onglet Documents -->
        <div id="content-documents" class="tab-content hidden">
            <div class="space-y-6">
                <!-- Documents généraux -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-folder mr-2 text-blue-500"></i>
                        Documents Généraux
                    </h3>
                    
                    <form action="{{ route('prestataire.documents.store') }}" method="POST" enctype="multipart/form-data" class="mb-4">
                        @csrf
                        <input type="hidden" name="document_type" value="documents">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="document_name" class="block text-sm font-medium text-gray-700 mb-1">Nom du document</label>
                                <input type="text" id="document_name" name="document_name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="document_file" class="block text-sm font-medium text-gray-700 mb-1">Fichier</label>
                                <input type="file" id="document_file" name="document_file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Ajouter le document
                            </button>
                        </div>
                    </form>
                    
                    <!-- Liste des documents existants -->
                    @if($prestataire->documents && count($prestataire->documents) > 0)
                        <div class="space-y-2">
                            @foreach($prestataire->documents as $index => $document)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-file-alt text-blue-600 mr-3"></i>
                                        <span class="font-medium">{{ $document['name'] ?? 'Document' }}</span>
                                    </div>
                                    <button onclick="removeDocument('documents', {{ $index }})" 
                                            class="text-red-600 hover:text-red-800 transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                
                <!-- Certifications -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-certificate mr-2 text-yellow-500"></i>
                        Certifications
                    </h3>
                    
                    <form action="{{ route('prestataire.documents.store') }}" method="POST" enctype="multipart/form-data" class="mb-4">
                        @csrf
                        <input type="hidden" name="document_type" value="certifications">
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="certification_name" class="block text-sm font-medium text-gray-700 mb-1">Nom de la certification</label>
                                <input type="text" id="certification_name" name="document_name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="certification_issuer" class="block text-sm font-medium text-gray-700 mb-1">Organisme délivrant</label>
                                <input type="text" id="certification_issuer" name="issuer"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="certification_date" class="block text-sm font-medium text-gray-700 mb-1">Date d'obtention</label>
                                <input type="date" id="certification_date" name="date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label for="certification_file" class="block text-sm font-medium text-gray-700 mb-1">Fichier de certification</label>
                            <input type="file" id="certification_file" name="document_file" required accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Ajouter la certification
                            </button>
                        </div>
                    </form>
                    
                    <!-- Liste des certifications existantes -->
                    @if($prestataire->certifications && count($prestataire->certifications) > 0)
                        <div class="space-y-2">
                            @foreach($prestataire->certifications as $index => $certification)
                                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-award text-yellow-600 mr-3"></i>
                                        <div>
                                            <span class="font-medium">{{ $certification['name'] ?? 'Certification' }}</span>
                                            @if(isset($certification['issuer']))
                                                <p class="text-sm text-gray-600">{{ $certification['issuer'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <button onclick="removeDocument('certifications', {{ $index }})" 
                                            class="text-red-600 hover:text-red-800 transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                
                <!-- Diplômes -->
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-graduation-cap mr-2 text-purple-500"></i>
                        Diplômes
                    </h3>
                    
                    <form action="{{ route('prestataire.documents.store') }}" method="POST" enctype="multipart/form-data" class="mb-4">
                        @csrf
                        <input type="hidden" name="document_type" value="diplomas">
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="diploma_name" class="block text-sm font-medium text-gray-700 mb-1">Nom du diplôme</label>
                                <input type="text" id="diploma_name" name="document_name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="diploma_institution" class="block text-sm font-medium text-gray-700 mb-1">Établissement</label>
                                <input type="text" id="diploma_institution" name="institution"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label for="diploma_year" class="block text-sm font-medium text-gray-700 mb-1">Année d'obtention</label>
                                <input type="number" id="diploma_year" name="year" min="1950" max="2030"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label for="diploma_file" class="block text-sm font-medium text-gray-700 mb-1">Fichier du diplôme</label>
                            <input type="file" id="diploma_file" name="document_file" required accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Ajouter le diplôme
                            </button>
                        </div>
                    </form>
                    
                    <!-- Liste des diplômes existants -->
                    @if($prestataire->diplomas && count($prestataire->diplomas) > 0)
                        <div class="space-y-2">
                            @foreach($prestataire->diplomas as $index => $diploma)
                                <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-scroll text-purple-600 mr-3"></i>
                                        <div>
                                            <span class="font-medium">{{ $diploma['name'] ?? 'Diplôme' }}</span>
                                            @if(isset($diploma['institution']))
                                                <p class="text-sm text-gray-600">{{ $diploma['institution'] }} @if(isset($diploma['year'])) - {{ $diploma['year'] }} @endif</p>
                                            @endif
                                        </div>
                                    </div>
                                    <button onclick="removeDocument('diplomas', {{ $index }})" 
                                            class="text-red-600 hover:text-red-800 transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        

        
        <!-- Onglet QR Code -->
        <div id="content-qrcode" class="tab-content hidden">
            <x-qr-code-profile :prestataire="$prestataire" />
        </div>
    </div>
</div>

<script>
// Gestion des onglets
function showTab(tabName) {
    // Masquer tous les contenus
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Désactiver tous les boutons d'onglets
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Afficher le contenu sélectionné
    document.getElementById(`content-${tabName}`).classList.remove('hidden');
    
    // Activer le bouton d'onglet sélectionné
    const activeButton = document.getElementById(`tab-${tabName}`);
    activeButton.classList.remove('border-transparent', 'text-gray-500');
    activeButton.classList.add('border-blue-500', 'text-blue-600');
}

// Afficher le premier onglet par défaut
document.addEventListener('DOMContentLoaded', function() {
    showTab('portfolio');
});

// Fonctions pour supprimer des éléments
function removeImage(imagePath, button) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette image ?')) {
        // Ici, vous pouvez ajouter une requête AJAX pour supprimer l'image du serveur
        button.closest('.relative').remove();
    }
}

function removeVideo(videoPath, button) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette vidéo ?')) {
        // Ici, vous pouvez ajouter une requête AJAX pour supprimer la vidéo du serveur
        button.closest('.relative').remove();
    }
}

function removeDocument(type, index) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce document ?')) {
        // Ici, vous pouvez ajouter une requête AJAX pour supprimer le document du serveur
        // Pour l'instant, on recharge la page
        window.location.reload();
    }
}

// Aperçu des fichiers sélectionnés
document.getElementById('portfolio_images').addEventListener('change', function(e) {
    const files = e.target.files;
    if (files.length > 10) {
        alert('Vous ne pouvez sélectionner que 10 images maximum.');
        e.target.value = '';
        return;
    }
    
    for (let file of files) {
        if (file.size > 5 * 1024 * 1024) { // 5MB
            alert(`Le fichier ${file.name} est trop volumineux (max 5MB).`);
            e.target.value = '';
            return;
        }
    }
});

document.getElementById('portfolio_videos').addEventListener('change', function(e) {
    const files = e.target.files;
    if (files.length > 5) {
        alert('Vous ne pouvez sélectionner que 5 vidéos maximum.');
        e.target.value = '';
        return;
    }
    
    for (let file of files) {
        if (file.size > 50 * 1024 * 1024) { // 50MB
            alert(`Le fichier ${file.name} est trop volumineux (max 50MB).`);
            e.target.value = '';
            return;
        }
    }
});
</script>
@endsection