@extends('layouts.admin-modern')

@section('title', 'Détails de la demande de vérification')

@section('content')
<div class="bg-orange-50 min-h-screen">
    {{-- ========================================
         SECTION 1: EN-TÊTE ET NAVIGATION
    ======================================== --}}
    <header class="bg-gradient-to-r from-orange-600 to-orange-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                <!-- Titre et breadcrumb -->
                <div class="flex-1">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-white mb-1 sm:mb-2">
                        Détails de la Vérification
                    </h1>
                    <nav class="flex items-center space-x-1 sm:space-x-2 text-xs sm:text-sm text-orange-100">
                        <a href="{{ route('admin.verifications.index') }}" class="hover:text-white transition duration-200">
                            <i class="fas fa-list mr-1"></i>Vérifications
                        </a>
                        <i class="fas fa-chevron-right text-orange-300"></i>
                        <span class="font-medium text-white">Demande #{{ $verificationRequest->id }}</span>
                    </nav>
                </div>
                
                <!-- Actions principales -->
                <div class="flex items-center space-x-2 sm:space-x-3">
                    <a href="{{ route('admin.verifications.index') }}" 
                       class="bg-white/20 hover:bg-white/30 text-white font-medium py-2 px-3 sm:px-4 rounded-lg transition duration-200 flex items-center text-sm sm:text-base">
                        <i class="fas fa-arrow-left mr-1 sm:mr-2"></i><span class="hidden sm:inline">Retour à la liste</span><span class="sm:hidden">Retour</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    {{-- ========================================
         SECTION 2: MESSAGES DE SESSION
    ======================================== --}}
    @if(session('success') || session('error'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 sm:pt-6">
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-3 sm:p-4 mb-3 sm:mb-4 rounded-r-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-400 mr-2 sm:mr-3 text-sm sm:text-base"></i>
                        <span class="text-green-800 font-medium text-sm sm:text-base">{{ session('success') }}</span>
                    </div>
                    <button type="button" class="text-green-400 hover:text-green-600 text-sm sm:text-base" onclick="this.parentElement.parentElement.style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-3 sm:p-4 mb-3 sm:mb-4 rounded-r-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-400 mr-2 sm:mr-3 text-sm sm:text-base"></i>
                        <span class="text-red-800 font-medium text-sm sm:text-base">{{ session('error') }}</span>
                    </div>
                    <button type="button" class="text-red-400 hover:text-red-600 text-sm sm:text-base" onclick="this.parentElement.parentElement.style.display='none'">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>
    @endif

    {{-- ========================================
         SECTION 3: CONTENU PRINCIPAL
    ======================================== --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-6 lg:gap-8">
            {{-- ========================================
                 SECTION 3.1: INFORMATIONS PRESTATAIRE
            ======================================== --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg sm:rounded-xl shadow-lg border border-orange-100 overflow-hidden h-fit">
                    <!-- En-tête de la carte -->
                    <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-4 sm:px-6 py-3 sm:py-4">
                        <h2 class="text-lg sm:text-xl font-bold text-white flex items-center">
                            <i class="fas fa-user-circle mr-2 sm:mr-3"></i>
                            <span class="hidden sm:inline">Informations du prestataire</span>
                            <span class="sm:hidden">Prestataire</span>
                        </h2>
                    </div>
                    
                    <!-- Contenu de la carte -->
                    <div class="p-4 sm:p-6">
                        <!-- Profil utilisateur -->
                        <div class="text-center mb-4 sm:mb-6">
                            @if($verificationRequest->prestataire->profile_photo)
                                <img src="{{ Storage::url($verificationRequest->prestataire->profile_photo) }}" 
                                     alt="{{ $verificationRequest->prestataire->nom }}" 
                                     class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover mx-auto mb-3 sm:mb-4 border-2 sm:border-4 border-orange-200">
                            @else
                                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                                    <i class="fas fa-user text-2xl sm:text-3xl text-orange-600"></i>
                                </div>
                            @endif
                            <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-1">
                                {{ $verificationRequest->prestataire->nom }} {{ $verificationRequest->prestataire->prenom }}
                            </h3>
                            <p class="text-xs sm:text-sm text-gray-600">
                                {{ $verificationRequest->prestataire->user->email }}
                            </p>
                            @if($verificationRequest->prestataire->telephone)
                                <p class="text-xs sm:text-sm text-gray-600 mt-1">{{ $verificationRequest->prestataire->telephone }}</p>
                            @endif
                            @if($verificationRequest->prestataire->isVerified())
                                <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium bg-green-100 text-green-800 mt-2">
                                    <i class="fas fa-check-circle mr-1"></i>Vérifié
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium bg-gray-100 text-gray-800 mt-2">
                                    Non vérifié
                                </span>
                            @endif
                        </div>
                        
                        <!-- Statistiques -->
                        <div class="space-y-3 sm:space-y-4">
                            <!-- Note moyenne -->
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-xs sm:text-sm font-medium text-gray-600">Note moyenne:</span>
                                <div class="flex items-center">
                                    <span class="text-sm sm:text-lg font-bold text-green-600 mr-1 sm:mr-2">
                                        {{ number_format($verificationRequest->prestataire->average_rating ?? 0, 2) }}
                                    </span>
                                    <div class="flex text-orange-400">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= ($verificationRequest->prestataire->average_rating ?? 0))
                                                <i class="fas fa-star text-xs"></i>
                                            @else
                                                <i class="far fa-star text-xs"></i>
                                            @endif
                                        @endfor
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Nombre d'avis -->
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-xs sm:text-sm font-medium text-gray-600">Avis:</span>
                                <span class="text-xs sm:text-sm font-semibold text-gray-800">
                                    {{ $verificationRequest->prestataire->reviews()->count() }}
                                </span>
                            </div>
                            
                            <!-- Secteur d'activité -->
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-xs sm:text-sm font-medium text-gray-600">Secteur:</span>
                                <span class="text-xs sm:text-sm font-semibold text-gray-800 text-right">
                                    {{ Str::limit($verificationRequest->prestataire->secteur_activite ?? 'Non spécifié', 20) }}
                                </span>
                            </div>
                            
                            <!-- Date d'inscription -->
                            <div class="flex justify-between items-center py-2">
                                <span class="text-xs sm:text-sm font-medium text-gray-600">Inscription:</span>
                                <span class="text-xs sm:text-sm font-semibold text-gray-800">
                                    {{ $verificationRequest->prestataire->created_at->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- ========================================
                 SECTION 3.2: DÉTAILS DE LA DEMANDE
            ======================================== --}}
            <div class="lg:col-span-3">
                <!-- Carte principale des détails -->
                <div class="bg-white rounded-lg sm:rounded-xl shadow-lg border border-orange-100 overflow-hidden mb-4 sm:mb-6 lg:mb-8">
                    <!-- En-tête de la carte -->
                    <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-4 sm:px-6 py-3 sm:py-4">
                        <h2 class="text-lg sm:text-xl font-bold text-white flex items-center">
                            <i class="fas fa-file-alt mr-2 sm:mr-3"></i>
                            <span class="hidden sm:inline">Détails de la demande de vérification</span>
                            <span class="sm:hidden">Détails de la demande</span>
                        </h2>
                    </div>
                    
                    <!-- Contenu de la carte -->
                    <div class="p-4 sm:p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 lg:gap-8">
                            <!-- Colonne 1: Informations de base -->
                            <div class="space-y-4 sm:space-y-6">
                                <div class="bg-green-50 rounded-lg p-3 sm:p-4">
                                    <h3 class="text-base sm:text-lg font-semibold text-green-800 mb-3 sm:mb-4 flex items-center">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <span class="hidden sm:inline">Informations générales</span>
                                        <span class="sm:hidden">Informations</span>
                                    </h3>
                                    <div class="space-y-2 sm:space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs sm:text-sm font-medium text-gray-600">ID:</span>
                                            <span class="text-xs sm:text-sm font-bold text-green-600">#{{ $verificationRequest->id }}</span>
                                        </div>
                                        
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs sm:text-sm font-medium text-gray-600">Statut:</span>
                                            @if($verificationRequest->isPending())
                                                <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i><span class="hidden sm:inline">En attente</span><span class="sm:hidden">Attente</span>
                                                </span>
                                            @elseif($verificationRequest->isApproved())
                                                <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check mr-1"></i>Approuvé
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-times mr-1"></i>Rejeté
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs sm:text-sm font-medium text-gray-600">Type:</span>
                                            <span class="text-xs sm:text-sm font-semibold text-gray-800 text-right">
                                                @switch($verificationRequest->document_type)
                                                    @case('identity')
                                                        <i class="fas fa-id-card text-orange-500 mr-1"></i><span class="hidden sm:inline">Pièce d'identité</span><span class="sm:hidden">Identité</span>
                                                        @break
                                                    @case('professional')
                                                        <i class="fas fa-briefcase text-orange-500 mr-1"></i><span class="hidden sm:inline">Document professionnel</span><span class="sm:hidden">Pro</span>
                                                        @break
                                                    @case('business')
                                                        <i class="fas fa-building text-orange-500 mr-1"></i><span class="hidden sm:inline">Document d'entreprise</span><span class="sm:hidden">Entreprise</span>
                                                        @break
                                                    @default
                                                        <i class="fas fa-file text-orange-500 mr-1"></i>{{ ucfirst($verificationRequest->document_type) }}
                                                @endswitch
                                            </span>
                                        </div>
                                        
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs sm:text-sm font-medium text-gray-600">Soumission:</span>
                                            <span class="text-xs sm:text-sm font-semibold text-gray-800">
                                                {{ $verificationRequest->submitted_at->format('d/m/Y') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Colonne 2: Informations de révision -->
                            <div class="space-y-4 sm:space-y-6">
                                @if($verificationRequest->reviewed_at || $verificationRequest->reviewedBy || $verificationRequest->admin_comment)
                                    <div class="bg-blue-50 rounded-lg p-3 sm:p-4">
                                        <h3 class="text-base sm:text-lg font-semibold text-blue-800 mb-3 sm:mb-4 flex items-center">
                                            <i class="fas fa-user-check mr-2"></i>
                                            <span class="hidden sm:inline">Informations de révision</span>
                                            <span class="sm:hidden">Révision</span>
                                        </h3>
                                        <div class="space-y-2 sm:space-y-3">
                                            @if($verificationRequest->reviewed_at)
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs sm:text-sm font-medium text-gray-600">Révision:</span>
                                                    <span class="text-xs sm:text-sm font-semibold text-gray-800">
                                                        {{ $verificationRequest->reviewed_at->format('d/m/Y') }}
                                                    </span>
                                                </div>
                                            @endif
                                            
                                            @if($verificationRequest->reviewedBy)
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs sm:text-sm font-medium text-gray-600">Par:</span>
                                                    <span class="text-xs sm:text-sm font-semibold text-gray-800">
                                                        {{ Str::limit($verificationRequest->reviewedBy->name, 15) }}
                                                    </span>
                                                </div>
                                            @endif
                                            
                                            @if($verificationRequest->admin_comment)
                                                <div class="mt-3 sm:mt-4">
                                                    <span class="text-xs sm:text-sm font-medium text-gray-600 block mb-2">Commentaires:</span>
                                                    <div class="bg-white rounded-lg p-2 sm:p-3 border border-blue-200">
                                                        <p class="text-xs sm:text-sm text-gray-700">{{ Str::limit($verificationRequest->admin_comment, 100) }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-gray-50 rounded-lg p-3 sm:p-4 text-center">
                                        <i class="fas fa-clock text-gray-400 text-xl sm:text-2xl mb-2"></i>
                                        <p class="text-xs sm:text-sm text-gray-600">Aucune révision effectuée</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        @if($verificationRequest->isPending() || $verificationRequest->prestataire->isVerified())
                            <div class="mt-6 sm:mt-8 pt-4 sm:pt-6 border-t border-gray-200">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4 flex items-center">
                                    <i class="fas fa-cogs mr-2"></i>
                                    <span class="hidden sm:inline">Actions disponibles</span>
                                    <span class="sm:hidden">Actions</span>
                                </h3>
                                <div class="flex flex-col sm:flex-row flex-wrap gap-2 sm:gap-3">
                                    @if($verificationRequest->isPending())
                                        <button type="button" class="inline-flex items-center justify-center px-4 sm:px-6 py-2 sm:py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg text-sm sm:text-base" onclick="approveRequest()">
                                            <i class="fas fa-check mr-2"></i> <span class="hidden sm:inline">Approuver la demande</span><span class="sm:hidden">Approuver</span>
                                        </button>
                                        <button type="button" class="inline-flex items-center justify-center px-4 sm:px-6 py-2 sm:py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg text-sm sm:text-base" onclick="rejectRequest()">
                                            <i class="fas fa-times mr-2"></i> <span class="hidden sm:inline">Rejeter la demande</span><span class="sm:hidden">Rejeter</span>
                                        </button>
                                    @endif
                                    
                                    @if($verificationRequest->prestataire->isVerified())
                                        <button type="button" class="inline-flex items-center justify-center px-4 sm:px-6 py-2 sm:py-3 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg text-sm sm:text-base" onclick="revokeVerification()">
                                            <i class="fas fa-ban mr-2"></i> <span class="hidden sm:inline">Révoquer la vérification</span><span class="sm:hidden">Révoquer</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            
            {{-- ========================================
                 SECTION 3.3: DOCUMENTS SOUMIS
            ======================================== --}}
            <div class="bg-white rounded-lg sm:rounded-xl shadow-lg border border-orange-100 overflow-hidden mb-4 sm:mb-6">
                <!-- En-tête de la section documents -->
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-4 sm:px-6 py-3 sm:py-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg sm:text-xl font-bold text-white flex items-center">
                            <i class="fas fa-folder-open mr-2 sm:mr-3"></i>
                            <span class="hidden sm:inline">Documents soumis</span>
                            <span class="sm:hidden">Documents</span>
                        </h2>
                        @if($verificationRequest->documents && count($verificationRequest->documents) > 0)
                            <span class="bg-white/20 text-white text-xs sm:text-sm px-2 sm:px-3 py-1 rounded-full">
                                {{ count($verificationRequest->documents) }} doc(s)
                            </span>
                        @endif
                    </div>
                </div>
                
                <!-- Contenu des documents -->
                <div class="p-4 sm:p-6">
                    @if($verificationRequest->documents && count($verificationRequest->documents) > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6">
                            @foreach($verificationRequest->documents as $index => $document)
                                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                                    <!-- En-tête du document -->
                                    <div class="bg-white px-3 sm:px-4 py-2 sm:py-3 border-b border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-xs sm:text-sm font-semibold text-gray-800 truncate flex items-center">
                                                <i class="fas fa-file-alt text-orange-500 mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                                                <span class="hidden sm:inline">Document {{ $index + 1 }}</span>
                                                <span class="sm:hidden">Doc {{ $index + 1 }}</span>
                                            </h3>
                                            @php
                                                $extension = pathinfo($document, PATHINFO_EXTENSION);
                                                $filename = basename($document);
                                            @endphp
                                            <span class="text-xs bg-orange-100 text-orange-700 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full">
                                                {{ strtoupper($extension) }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Contenu du document -->
                                    <div class="p-3 sm:p-4">
                                        @php
                                            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                            $isPdf = strtolower($extension) === 'pdf';
                                            $fileExists = Storage::disk('public')->exists($document);
                                        @endphp
                                        
                                        <!-- Aperçu du fichier -->
                                        <div class="mb-3 sm:mb-4">
                                            @if(!$fileExists)
                                                <div class="text-center py-6 sm:py-8 text-red-600">
                                                    <i class="fas fa-exclamation-triangle text-2xl sm:text-3xl mb-2"></i>
                                                    <div class="text-xs sm:text-sm font-medium">Fichier introuvable</div>
                                                </div>
                                            @elseif($isImage)
                                                <div class="relative group">
                                                    <img src="{{ Storage::url($document) }}" 
                                                         class="w-full h-24 sm:h-32 object-cover rounded-lg border border-gray-200"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                    <div class="text-center py-6 sm:py-8 text-red-600 hidden">
                                                        <i class="fas fa-image text-2xl sm:text-3xl mb-2"></i>
                                                        <div class="text-xs sm:text-sm">Erreur de chargement</div>
                                                    </div>
                                                    <!-- Overlay pour agrandir -->
                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100">
                                                        <button type="button" class="bg-white text-gray-800 px-2 sm:px-3 py-1 rounded-lg text-xs sm:text-sm font-medium" 
                                                                onclick="viewImage('{{ Storage::url($document) }}', '{{ $filename }}')">
                                                            <i class="fas fa-search-plus mr-1"></i> <span class="hidden sm:inline">Agrandir</span><span class="sm:hidden">+</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            @elseif($isPdf)
                                                <div class="text-center py-6 sm:py-8">
                                                    <i class="fas fa-file-pdf text-3xl sm:text-4xl text-red-500 mb-2"></i>
                                                    <div class="text-xs sm:text-sm font-medium text-gray-700">Document PDF</div>
                                                </div>
                                            @else
                                                <div class="text-center py-6 sm:py-8">
                                                    <i class="fas fa-file-alt text-3xl sm:text-4xl text-gray-400 mb-2"></i>
                                                    <div class="text-xs sm:text-sm font-medium text-gray-700">Fichier {{ strtoupper($extension) }}</div>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Informations du fichier -->
                                        <div class="space-y-1 sm:space-y-2 mb-3 sm:mb-4">
                                            <div class="text-xs sm:text-sm text-gray-700 font-medium" title="{{ $filename }}">
                                                {{ Str::limit($filename, 25) }}
                                            </div>
                                            @if($fileExists)
                                                @php
                                                    $fileSize = Storage::disk('public')->size($document);
                                                    $fileSizeFormatted = $fileSize > 1024 * 1024 
                                                        ? round($fileSize / (1024 * 1024), 1) . ' MB'
                                                        : round($fileSize / 1024, 1) . ' KB';
                                                @endphp
                                                <div class="text-xs text-gray-500">Taille: {{ $fileSizeFormatted }}</div>
                                            @endif
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="flex flex-col gap-1.5 sm:gap-2">
                                            @if($fileExists)
                                                <a href="{{ route('admin.verifications.download-document', [$verificationRequest, $index]) }}" 
                                                   class="w-full inline-flex items-center justify-center px-3 sm:px-4 py-1.5 sm:py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs sm:text-sm font-medium rounded-lg transition-colors duration-200" 
                                                   target="_blank">
                                                    <i class="fas fa-download mr-1 sm:mr-2"></i> <span class="hidden sm:inline">Télécharger</span><span class="sm:hidden">DL</span>
                                                </a>
                                                @if($isImage)
                                                    <button type="button" 
                                                            class="w-full inline-flex items-center justify-center px-3 sm:px-4 py-1.5 sm:py-2 bg-gray-600 hover:bg-gray-700 text-white text-xs sm:text-sm font-medium rounded-lg transition-colors duration-200" 
                                                            onclick="viewImage('{{ Storage::url($document) }}', '{{ $filename }}')">
                                                        <i class="fas fa-eye mr-1 sm:mr-2"></i> <span class="hidden sm:inline">Voir en grand</span><span class="sm:hidden">Voir</span>
                                                    </button>
                                                @endif
                                            @else
                                                <div class="w-full text-center py-1.5 sm:py-2 bg-red-100 text-red-800 text-xs sm:text-sm font-medium rounded-lg">
                                                    <i class="fas fa-times mr-1 sm:mr-2"></i> <span class="hidden sm:inline">Document indisponible</span><span class="sm:hidden">Indisponible</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 sm:py-12">
                            <div class="bg-gray-100 rounded-full w-16 h-16 sm:w-20 sm:h-20 flex items-center justify-center mx-auto mb-3 sm:mb-4">
                                <i class="fas fa-file-times text-2xl sm:text-3xl text-gray-400"></i>
                            </div>
                            <h3 class="text-base sm:text-lg font-medium text-gray-700 mb-2">Aucun document soumis</h3>
                            <p class="text-xs sm:text-sm text-gray-500">Le prestataire n'a pas encore fourni de documents pour cette demande de vérification.</p>
                        </div>
                    @endif
                </div>
            </div>
            </div>
        </main>
    </div>
</div>

{{-- ========================================
     SECTION 4: MODAUX ET INTERACTIONS
======================================== --}}

<!-- Modal d'approbation -->
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" id="approveModal">
    <div class="relative top-10 sm:top-20 mx-auto p-4 sm:p-5 border w-11/12 sm:w-96 max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-2 sm:mt-3">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="text-base sm:text-lg font-medium text-gray-900">Approuver la demande</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 p-1" onclick="closeModal('approveModal')">
                    <i class="fas fa-times text-sm sm:text-base"></i>
                </button>
            </div>
            <form action="{{ route('admin.verifications.approve', $verificationRequest) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-3 sm:mb-4">
                    <label for="approve_comment" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                        Commentaire (optionnel)
                    </label>
                    <textarea name="admin_comment" id="approve_comment" 
                              class="w-full px-2 sm:px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-xs sm:text-sm" 
                              rows="3" placeholder="Commentaire pour le prestataire..."></textarea>
                </div>
                <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                    <button type="button" 
                            class="px-3 sm:px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors text-xs sm:text-sm" 
                            onclick="closeModal('approveModal')">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-3 sm:px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-xs sm:text-sm">
                        <i class="fas fa-check mr-1 sm:mr-2"></i>Approuver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de rejet -->
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" id="rejectModal">
    <div class="relative top-10 sm:top-20 mx-auto p-4 sm:p-5 border w-11/12 sm:w-96 max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-2 sm:mt-3">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h3 class="text-base sm:text-lg font-medium text-gray-900">Rejeter la demande</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 p-1" onclick="closeModal('rejectModal')">
                    <i class="fas fa-times text-sm sm:text-base"></i>
                </button>
            </div>
            <form action="{{ route('admin.verifications.reject', $verificationRequest) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-3 sm:mb-4">
                    <label for="reject_comment" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                        Motif du rejet <span class="text-red-500">*</span>
                    </label>
                    <textarea name="admin_comment" id="reject_comment" 
                              class="w-full px-2 sm:px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 text-xs sm:text-sm" 
                              rows="3" placeholder="Expliquez pourquoi la demande est rejetée..." required></textarea>
                </div>
                <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                    <button type="button" 
                            class="px-3 sm:px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors text-xs sm:text-sm" 
                            onclick="closeModal('rejectModal')">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-3 sm:px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors text-xs sm:text-sm">
                        <i class="fas fa-times mr-1 sm:mr-2"></i>Rejeter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de visualisation d'image -->
<div class="fixed inset-0 bg-black bg-opacity-75 overflow-y-auto h-full w-full hidden" id="imageModal">
    <div class="relative top-5 sm:top-10 mx-auto p-2 sm:p-5 w-11/12 max-w-4xl">
        <div class="bg-white rounded-lg shadow-xl">
            <div class="flex items-center justify-between p-3 sm:p-4 border-b">
                <h3 class="text-sm sm:text-lg font-medium text-gray-900" id="imageModalTitle">Aperçu du document</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 p-1" onclick="closeModal('imageModal')">
                    <i class="fas fa-times text-lg sm:text-xl"></i>
                </button>
            </div>
            <div class="p-3 sm:p-4 text-center">
                <img id="modalImage" src="" class="max-w-full max-h-64 sm:max-h-96 mx-auto rounded-lg">
            </div>
        </div>
    </div>
</div>

{{-- ========================================
     SECTION 5: SCRIPTS ET INTERACTIONS
======================================== --}}

@push('scripts')
<script>
// Gestion des modaux Tailwind CSS
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Fonction pour visualiser les images
function viewImage(imageSrc, documentName) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModalTitle').textContent = documentName || 'Aperçu du document';
    openModal('imageModal');
}

function approveRequest() {
    openModal('approveModal');
}

function rejectRequest() {
    openModal('rejectModal');
}

function revokeVerification() {
    if (confirm('Êtes-vous sûr de vouloir révoquer la vérification de ce prestataire ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.verifications.revoke", $verificationRequest->prestataire) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PATCH';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

// Fermeture des modaux en cliquant à l'extérieur
window.onclick = function(event) {
    const modals = ['approveModal', 'rejectModal', 'imageModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            closeModal(modalId);
        }
    });
}

// Fermeture avec la touche Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = ['approveModal', 'rejectModal', 'imageModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (!modal.classList.contains('hidden')) {
                closeModal(modalId);
            }
        });
    }
});
</script>
@endpush
@endsection