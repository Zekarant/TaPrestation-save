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
            
            <!-- Message d'information -->
            @if(session('urgent_sale_just_created'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            {{ session('info') ?? "Vous avez déjà créé une annonce. Si vous souhaitez créer une nouvelle annonce, vous pouvez le faire en cliquant sur le bouton ci-dessous." }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

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
                            <div class="h-1 bg-red-600 rounded" style="width: 0%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                2
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-gray-500 hidden sm:inline">Détails</span>
                            <span class="ml-1 text-xs font-medium text-gray-500 sm:hidden">Détails</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 rounded min-w-4">
                            <div class="h-1 bg-gray-200 rounded" style="width: 0%"></div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                3
                            </div>
                            <span class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-gray-500 hidden sm:inline">Photos</span>
                            <span class="ml-1 text-xs font-medium text-gray-500 sm:hidden">Photo</span>
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

            <!-- Boutons de navigation -->
            <div class="flex justify-center space-x-4 mb-4 sm:mb-6">
                <a href="{{ route('prestataire.urgent-sales.create.step1') }}" class="bg-red-600 hover:bg-red-700 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl text-sm sm:text-base">
                    <i class="fas fa-play mr-1 sm:mr-2"></i>Commencer
                </a>
            </div>

            <!-- Aperçu des étapes -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
                <div class="bg-white rounded-xl shadow-lg border border-red-200 p-3 sm:p-4 lg:p-6 text-center">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                        <i class="fas fa-info-circle text-red-600 text-lg sm:text-xl lg:text-2xl"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-red-900 mb-1 sm:mb-2">Étape 1</h3>
                    <p class="text-xs sm:text-sm text-red-700">Informations de base</p>
                    <p class="text-xs text-gray-600 mt-1 sm:mt-2 hidden sm:block">Titre, description, prix</p>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg border border-red-200 p-3 sm:p-4 lg:p-6 text-center">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                        <i class="fas fa-list-alt text-blue-600 text-lg sm:text-xl lg:text-2xl"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-red-900 mb-1 sm:mb-2">Étape 2</h3>
                    <p class="text-xs sm:text-sm text-red-700">Détails</p>
                    <p class="text-xs text-gray-600 mt-1 sm:mt-2 hidden sm:block">Catégorie, quantité, état</p>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg border border-red-200 p-3 sm:p-4 lg:p-6 text-center">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                        <i class="fas fa-camera text-purple-600 text-lg sm:text-xl lg:text-2xl"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-red-900 mb-1 sm:mb-2">Étape 3</h3>
                    <p class="text-xs sm:text-sm text-red-700">Photos</p>
                    <p class="text-xs text-gray-600 mt-1 sm:mt-2 hidden sm:block">Images de l'annonce</p>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg border border-red-200 p-3 sm:p-4 lg:p-6 text-center">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                        <i class="fas fa-map-marker-alt text-orange-600 text-lg sm:text-xl lg:text-2xl"></i>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-red-900 mb-1 sm:mb-2">Étape 4</h3>
                    <p class="text-xs sm:text-sm text-red-700">Localisation</p>
                    <p class="text-xs text-gray-600 mt-1 sm:mt-2 hidden sm:block">Où proposez-vous cette annonce</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Animation des cartes d'étapes
    const cards = document.querySelectorAll('.bg-white.rounded-xl');
    cards.forEach((card, index) => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>
@endpush