@extends('layouts.app')

@section('title', 'Détails de la demande de vérification')

@section('content')
<div class="min-h-screen bg-orange-50">
    <div class="container mx-auto py-4 sm:py-6 md:py-8 px-2 sm:px-4">
        <!-- En-tête amélioré -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-green-500 to-green-700 rounded-xl shadow-lg">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-green-900 mb-1 sm:mb-2">
                        Demande de vérification #{{ $verificationRequest->id }}
                    </h1>
                    <p class="text-sm sm:text-base md:text-lg text-green-700">Détails de votre demande de vérification</p>
                </div>
                
                <!-- Badge de statut principal -->
                <div class="flex-shrink-0">
                    @if($verificationRequest->isPending())
                        <span class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 rounded-full text-sm sm:text-lg font-semibold bg-green-100 text-green-800">
                            <svg class="w-4 h-4 sm:w-6 sm:h-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            En attente
                        </span>
                    @elseif($verificationRequest->isApproved())
                        <span class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 rounded-full text-sm sm:text-lg font-semibold bg-green-100 text-green-800">
                            <svg class="w-4 h-4 sm:w-6 sm:h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Approuvée
                        </span>
                    @else
                        <span class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 rounded-full text-sm sm:text-lg font-semibold bg-red-100 text-red-800">
                            <svg class="w-4 h-4 sm:w-6 sm:h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            Rejetée
                        </span>
                    @endif
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 mb-6 sm:mb-8">
            <!-- Informations de la demande -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-4 sm:p-8 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4 sm:mb-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-green-50 rounded-xl">
                            <svg class="h-5 w-5 sm:h-6 sm:w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900">Informations de la demande</h3>
                        <p class="text-sm sm:text-base text-gray-600">Détails de votre soumission</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Date de soumission</span>
                        <span class="text-sm text-gray-900 font-semibold">{{ $verificationRequest->submitted_at->format('d/m/Y à H:i') }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Type de document</span>
                        <div>
                            @switch($verificationRequest->document_type)
                                @case('identity')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                        </svg>
                                        Pièce d'identité
                                    </span>
                                    @break
                                @case('professional')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2h8zM8 14v.01M12 14v.01M16 14v.01" />
                                        </svg>
                                        Document professionnel
                                    </span>
                                    @break
                                @case('business')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Document d'entreprise
                                    </span>
                                    @break
                            @endswitch
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <span class="text-sm font-medium text-gray-600">Nombre de documents</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ count($verificationRequest->documents ?? []) }} document(s)
                        </span>
                    </div>
                    
                    <div class="flex justify-between items-center py-3">
                        <span class="text-sm font-medium text-gray-600">Statut actuel</span>
                        <div>
                            @if($verificationRequest->isPending())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    En attente de révision
                                </span>
                            @elseif($verificationRequest->isApproved())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Approuvée
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Rejetée
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statut de révision -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-4 sm:p-8 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4 sm:mb-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 
                            @if($verificationRequest->isPending()) bg-green-50 @elseif($verificationRequest->isApproved()) bg-green-50 @else bg-red-50 @endif rounded-xl">
                            @if($verificationRequest->isPending())
                                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @elseif($verificationRequest->isApproved())
                                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </div>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900">Statut de révision</h3>
                        <p class="text-sm sm:text-base text-gray-600">État d'avancement de votre demande</p>
                    </div>
                </div>
                
                @if($verificationRequest->isPending())
                    <div class="text-center py-8">
                        <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 bg-green-50 rounded-full">
                            <svg class="w-10 h-10 text-green-600 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Examen en cours</h4>
                        <p class="text-gray-600 mb-4">
                            Votre demande est en cours d'examen par notre équipe. 
                            Vous recevrez une notification dès qu'elle sera traitée.
                        </p>
                        <div class="inline-flex items-center px-4 py-2 rounded-lg bg-green-50 text-green-800 text-sm font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Délai habituel : 48-72 heures
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-3 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Date de révision</span>
                            <span class="text-sm text-gray-900 font-semibold">{{ $verificationRequest->reviewed_at->format('d/m/Y à H:i') }}</span>
                        </div>
                        
                        @if($verificationRequest->reviewedBy)
                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <span class="text-sm font-medium text-gray-600">Révisé par</span>
                                <span class="text-sm text-gray-900 font-semibold">{{ $verificationRequest->reviewedBy->name }}</span>
                            </div>
                        @endif
                        
                        @if($verificationRequest->admin_comment)
                            <div class="py-3">
                                <span class="text-sm font-medium text-gray-600 block mb-3">Commentaire de l'administrateur</span>
                                <div class="p-4 rounded-lg @if($verificationRequest->isApproved()) bg-orange-50 border border-orange-200 @else bg-red-50 border border-red-200 @endif">
                                    <p class="text-sm @if($verificationRequest->isApproved()) text-green-800 @else text-red-800 @endif">
                                        {{ $verificationRequest->admin_comment }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Documents soumis -->
        @if($verificationRequest->documents && count($verificationRequest->documents) > 0)
            <div class="mt-8">
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center mb-6">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-12 h-12 bg-indigo-50 rounded-xl">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-xl font-bold text-gray-900">Documents soumis</h3>
                            <p class="text-gray-600">{{ count($verificationRequest->documents) }} document(s) téléchargé(s)</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($verificationRequest->documents as $index => $document)
                            @php
                                $extension = pathinfo($document, PATHINFO_EXTENSION);
                                $fileName = basename($document);
                                $isPdf = strtolower($extension) === 'pdf';
                                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png']);
                            @endphp
                            <div class="bg-gray-50 rounded-xl border border-gray-200 p-6 hover:shadow-md transition-all duration-300">
                                <div class="text-center">
                                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 
                                        @if($isPdf) bg-red-50 @elseif($isImage) bg-blue-50 @else bg-gray-100 @endif rounded-xl">
                                        @if($isPdf)
                                            <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                            </svg>
                                        @elseif($isImage)
                                            <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        @else
                                            <svg class="w-8 h-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <h4 class="text-sm font-semibold text-gray-900 mb-1 truncate" title="{{ $fileName }}">{{ $fileName }}</h4>
                                    <p class="text-xs text-gray-500 mb-4">{{ strtoupper($extension) }}</p>
                                    <a href="{{ route('prestataire.verification.download-document', [$verificationRequest, $index]) }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition-all duration-300" 
                                       target="_blank">
                                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Télécharger
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Actions et messages -->
        <div class="mt-8">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-12 h-12 bg-gray-50 rounded-xl">
                            <svg class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-xl font-bold text-gray-900">Actions disponibles</h3>
                        <p class="text-gray-600">Que souhaitez-vous faire ?</p>
                    </div>
                </div>
                
                <!-- Boutons d'action -->
                <div class="flex flex-wrap gap-4 mb-6">
                    <a href="{{ route('prestataire.verification.index') }}" 
                       class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all duration-300">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Retour à la liste
                    </a>
                    
                    @if($verificationRequest->isRejected())
                        <a href="{{ route('prestataire.verification.create') }}" 
                           class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-all duration-300">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Nouvelle demande
                        </a>
                    @endif
                </div>
                
                <!-- Messages contextuels -->
                @if($verificationRequest->isPending())
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-semibold text-blue-800 mb-1">Information</h4>
                                <p class="text-sm text-blue-700">
                                    Votre demande est en cours d'examen. Vous ne pouvez pas soumettre de nouvelle demande tant que celle-ci n'est pas traitée.
                                </p>
                            </div>
                        </div>
                    </div>
                @elseif($verificationRequest->isRejected())
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-semibold text-yellow-800 mb-1">Demande rejetée</h4>
                                <p class="text-sm text-yellow-700">
                                    Vous pouvez soumettre une nouvelle demande en tenant compte des commentaires de l'administrateur.
                                </p>
                            </div>
                        </div>
                    </div>
                @elseif($verificationRequest->isApproved())
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-semibold text-green-800 mb-1">Félicitations !</h4>
                                <p class="text-sm text-green-700">
                                    Votre compte a été vérifié avec succès. Le badge "Vérifié" est maintenant visible sur votre profil public.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection