@extends('layouts.app')

@section('title', 'Vérification du compte')

@section('content')
<div class="min-h-screen bg-orange-50">
    <div class="container mx-auto py-4 sm:py-6 md:py-8 px-2 sm:px-4">
        <!-- En-tête amélioré -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-500 to-orange-700 rounded-xl shadow-lg">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-orange-900 mb-1 sm:mb-2">
                        Vérification du compte
                    </h1>
                    <p class="text-sm sm:text-base md:text-lg text-orange-700">Gérez votre statut de vérification et vos demandes</p>
                </div>
                
                <!-- Badge de statut principal -->
                <div class="flex-shrink-0">
                    @if($prestataire->isVerified())
                        <span class="inline-flex items-center px-6 py-3 rounded-full text-lg font-semibold bg-green-100 text-green-800">
                            <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Vérifié
                        </span>
                    @else
                        <span class="inline-flex items-center px-6 py-3 rounded-full text-lg font-semibold bg-green-100 text-green-800">
                            <svg class="w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Non vérifié
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Messages de session stylisés -->
        @if(session('success'))
            <div class="mb-6 md:mb-8">
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-400 p-4 rounded-r-lg shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                        <div class="ml-auto pl-3">
                            <button type="button" class="inline-flex text-green-400 hover:text-green-600" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button type="button" class="inline-flex text-red-400 hover:text-red-600" onclick="this.parentElement.parentElement.parentElement.remove()">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-6 bg-orange-50 border border-orange-200 rounded-xl p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-orange-800">{{ session('info') }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button type="button" class="inline-flex text-orange-400 hover:text-orange-600" onclick="this.parentElement.parentElement.parentElement.remove()">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 mb-6 sm:mb-8">
            <!-- Statut actuel -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-4 sm:p-8 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4 sm:mb-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 
                            @if($prestataire->isVerified()) bg-green-50 @else bg-green-50 @endif rounded-xl">
                            @if($prestataire->isVerified())
                                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            @endif
                        </div>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900">Statut actuel</h3>
                        <p class="text-sm sm:text-base text-gray-600">État de votre vérification</p>
                    </div>
                </div>
                
                @if($prestataire->isVerified())
                    <div class="text-center py-4 sm:py-6">
                        <div class="flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-3 sm:mb-4 bg-orange-50 rounded-full">
                            <svg class="w-8 h-8 sm:w-10 sm:h-10 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h4 class="text-base sm:text-lg font-semibold text-orange-800 mb-2">Compte vérifié</h4>
                        <p class="text-sm sm:text-base text-orange-700 mb-2">{{ $prestataire->getVerificationType() }}</p>
                        <p class="text-xs sm:text-sm text-gray-600">
                            Vous bénéficiez du badge "Vérifié" sur votre profil public.
                        </p>
                    </div>
                @else
                    <div class="text-center py-6">
                        <div class="flex items-center justify-center w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-green-100 to-green-200 rounded-full shadow-lg">
                            <svg class="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-green-800 mb-2">Compte non vérifié</h4>
                        <p class="text-sm text-green-700 mb-4">
                            La vérification améliore votre crédibilité auprès des clients.
                        </p>
                        <a href="{{ route('prestataire.verification.create') }}" 
                           class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 border border-transparent text-xs sm:text-sm font-bold rounded-xl text-white bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-700 hover:to-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 w-full sm:w-auto justify-center">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span class="hidden sm:inline">Soumettre une demande</span>
                            <span class="sm:hidden">Soumettre</span>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Vérification automatique -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-4 sm:p-8 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4 sm:mb-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-green-50 rounded-xl">
                            <svg class="h-5 w-5 sm:h-6 sm:w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900">Vérification automatique</h3>
                        <p class="text-sm sm:text-base text-gray-600">Basée sur vos performances</p>
                    </div>
                </div>
                
                @if($automaticVerificationStatus['meets_criteria'])
                    <div class="text-center py-4 sm:py-6">
                        <div class="flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-3 sm:mb-4 bg-orange-50 rounded-full">
                            <svg class="w-8 h-8 sm:w-10 sm:h-10 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h4 class="text-base sm:text-lg font-semibold text-green-800 mb-2">Critères remplis</h4>
                        <p class="text-xs sm:text-sm text-gray-600 mb-3 sm:mb-4">
                            Vous remplissez tous les critères de vérification automatique.
                        </p>
                        @if(!$prestataire->isVerified())
                            <form action="{{ route('prestataire.verification.check-automatic') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 border border-transparent rounded-lg text-xs sm:text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition-all duration-300 w-full sm:w-auto justify-center">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="hidden sm:inline">Appliquer la vérification</span>
                                    <span class="sm:hidden">Appliquer</span>
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4 sm:py-6">
                        <div class="flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-3 sm:mb-4 bg-gray-50 rounded-full">
                            <svg class="w-8 h-8 sm:w-10 sm:h-10 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <h4 class="text-base sm:text-lg font-semibold text-gray-800 mb-2">Critères non remplis</h4>
                        <p class="text-xs sm:text-sm text-gray-600 mb-3 sm:mb-4">
                            Vous ne remplissez pas encore tous les critères.
                        </p>
                        <button type="button" class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 border border-orange-300 rounded-lg text-xs sm:text-sm font-medium text-orange-700 bg-white hover:bg-orange-50 transition-all duration-300 w-full sm:w-auto justify-center" onclick="document.getElementById('automaticCriteriaModal').classList.remove('hidden')">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="hidden sm:inline">Voir les critères</span>
                            <span class="sm:hidden">Critères</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Demande de vérification manuelle -->
        @if($canSubmitRequest)
            <div class="mb-6 sm:mb-8">
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-4 sm:p-8 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center mb-4 sm:mb-6">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-green-50 rounded-xl">
                                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900">Demande de vérification manuelle</h3>
                            <p class="text-sm sm:text-base text-gray-600">Soumettez des documents justificatifs</p>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 sm:p-6 mb-4 sm:mb-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs sm:text-sm text-green-700">
                                    Si vous ne remplissez pas les critères de vérification automatique, 
                                    vous pouvez soumettre des documents justificatifs pour une vérification manuelle.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <a href="{{ route('prestataire.verification.create') }}" 
                           class="inline-flex items-center px-6 sm:px-8 py-3 sm:py-4 border border-transparent rounded-lg text-sm sm:text-base font-medium text-white bg-green-600 hover:bg-green-700 transition-all duration-300 w-full sm:w-auto justify-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <span class="hidden sm:inline">Soumettre une demande</span>
                            <span class="sm:hidden">Soumettre</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Historique des demandes -->
        @if($verificationRequests->count() > 0)
            <div class="bg-white rounded-xl shadow-lg border border-orange-100 p-4 sm:p-6 md:p-8 hover:shadow-xl transition-all duration-300">
                <div class="flex flex-col sm:flex-row sm:items-center mb-4 sm:mb-6 gap-3 sm:gap-4">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-500 to-orange-700 rounded-xl shadow-lg">
                            <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-green-900">Historique des demandes</h3>
                        <p class="text-sm sm:text-base text-green-700">{{ $verificationRequests->count() }} demande(s) soumise(s)</p>
                    </div>
                </div>
                
                <div class="overflow-hidden rounded-xl border border-orange-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-orange-200">
                            <thead class="bg-gradient-to-r from-orange-50 to-amber-50">
                                <tr>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-bold text-orange-800 uppercase tracking-wider">Date de soumission</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-bold text-orange-800 uppercase tracking-wider">Type de document</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-bold text-orange-800 uppercase tracking-wider">Statut</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-bold text-orange-800 uppercase tracking-wider">Date de révision</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-bold text-orange-800 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-orange-100">
                                @foreach($verificationRequests as $request)
                                    <tr class="hover:bg-gradient-to-r hover:from-orange-50 hover:to-amber-50 transition-all duration-200">
                                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-orange-900">
                                            {{ $request->submitted_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                            @switch($request->document_type)
                                                @case('identity')
                                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-orange-100 to-amber-100 text-orange-800 shadow-sm">
                                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                                        </svg>
                                                        Pièce d'identité
                                                    </span>
                                                    @break
                                                @case('professional')
                                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-orange-100 to-amber-100 text-orange-800 shadow-sm">
                                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 712 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2h8zM8 14v.01M12 14v.01M16 14v.01" />
                                                        </svg>
                                                        Document professionnel
                                                    </span>
                                                    @break
                                                @case('business')
                                                    <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-orange-100 to-amber-100 text-orange-800 shadow-sm">
                                                        <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                        </svg>
                                                        Document d'entreprise
                                                    </span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                            @if($request->isPending())
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    En attente
                                                </span>
                                            @elseif($request->isApproved())
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    Approuvée
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                    </svg>
                                                    Rejetée
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-orange-900">
                                            {{ $request->reviewed_at ? $request->reviewed_at->format('d/m/Y H:i') : '-' }}
                                        </td>
                                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('prestataire.verification.show', $request) }}" 
                                               class="inline-flex items-center px-2 sm:px-3 py-2 border border-transparent text-xs sm:text-sm leading-4 font-bold rounded-lg text-white bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-700 hover:to-amber-700 shadow-md hover:shadow-lg transition-all duration-300">
                                                <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Voir
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-lg border border-orange-100 p-4 sm:p-6 md:p-8 hover:shadow-xl transition-all duration-300">
                <div class="flex flex-col sm:flex-row sm:items-center mb-4 sm:mb-6 gap-3 sm:gap-4">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-500 to-orange-700 rounded-xl shadow-lg">
                            <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-orange-900">Historique des demandes</h3>
                        <p class="text-sm sm:text-base text-orange-700">Aucune demande soumise</p>
                    </div>
                </div>
                
                <div class="text-center py-8 sm:py-12">
                    <div class="flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-4 sm:mb-6 bg-gradient-to-br from-orange-100 to-amber-100 rounded-full shadow-lg">
                        <svg class="w-8 h-8 sm:w-10 sm:h-10 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h4 class="text-lg sm:text-xl font-bold text-orange-900 mb-2">Aucune demande soumise</h4>
                    <p class="text-sm sm:text-base text-orange-700 mb-4 sm:mb-6">Vous n'avez pas encore soumis de demande de vérification.</p>
                    
                    <a href="{{ route('prestataire.verification.create') }}" 
                       class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 border border-transparent text-sm sm:text-base font-bold rounded-xl text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Commencer la vérification
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal pour les critères automatiques -->
<div id="automaticCriteriaModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 sm:top-20 mx-auto p-4 sm:p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-xl bg-white">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900">Critères de vérification automatique</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600" onclick="document.getElementById('automaticCriteriaModal').classList.add('hidden')">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div class="space-y-3 sm:space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 sm:p-4 bg-gray-50 rounded-lg gap-2 sm:gap-0">
                <span class="text-xs sm:text-sm font-medium text-gray-700">100 avis positifs minimum (≥4/5)</span>
                <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium 
                    {{ $automaticVerificationStatus['positive_reviews'] >= 100 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} self-start sm:self-auto">
                    {{ $automaticVerificationStatus['positive_reviews'] }}/100
                </span>
            </div>
            
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 sm:p-4 bg-gray-50 rounded-lg gap-2 sm:gap-0">
                <span class="text-xs sm:text-sm font-medium text-gray-700">Moins de 10 avis négatifs (<3/5)</span>
                <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium 
                    {{ $automaticVerificationStatus['negative_reviews'] < 10 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} self-start sm:self-auto">
                    {{ $automaticVerificationStatus['negative_reviews'] }}/10
                </span>
            </div>
            
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 sm:p-4 bg-gray-50 rounded-lg gap-2 sm:gap-0">
                <span class="text-xs sm:text-sm font-medium text-gray-700">Note moyenne ≥ 4/5</span>
                <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium 
                    {{ $automaticVerificationStatus['average_rating'] >= 4 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} self-start sm:self-auto">
                    {{ number_format($automaticVerificationStatus['average_rating'], 1) }}/5
                </span>
            </div>
            
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 sm:p-4 bg-gray-50 rounded-lg gap-2 sm:gap-0">
                <span class="text-xs sm:text-sm font-medium text-gray-700">Email vérifié</span>
                <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium 
                    {{ $automaticVerificationStatus['email_verified'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} self-start sm:self-auto">
                    {{ $automaticVerificationStatus['email_verified'] ? 'Oui' : 'Non' }}
                </span>
            </div>
            
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 sm:p-4 bg-gray-50 rounded-lg gap-2 sm:gap-0">
                <span class="text-xs sm:text-sm font-medium text-gray-700">Téléphone vérifié</span>
                <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium 
                    {{ $automaticVerificationStatus['phone_verified'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} self-start sm:self-auto">
                    {{ $automaticVerificationStatus['phone_verified'] ? 'Oui' : 'Non' }}
                </span>
            </div>
            
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 sm:p-4 bg-gray-50 rounded-lg gap-2 sm:gap-0">
                <span class="text-xs sm:text-sm font-medium text-gray-700">Connexion ≥ 5 fois/mois</span>
                <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 self-start sm:self-auto">
                    Vérifié automatiquement
                </span>
            </div>
            
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 sm:p-4 bg-gray-50 rounded-lg gap-2 sm:gap-0">
                <span class="text-xs sm:text-sm font-medium text-gray-700">Moins de 3 signalements</span>
                <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 self-start sm:self-auto">
                    Vérifié automatiquement
                </span>
            </div>
        </div>
        
        <div class="mt-4 sm:mt-6 flex justify-end">
            <button type="button" class="px-4 sm:px-6 py-2 sm:py-3 border border-orange-300 rounded-lg text-xs sm:text-sm font-medium text-orange-700 bg-white hover:bg-orange-50 transition-all duration-300" onclick="document.getElementById('automaticCriteriaModal').classList.add('hidden')">
                Fermer
            </button>
        </div>
    </div>
</div>
@endsection