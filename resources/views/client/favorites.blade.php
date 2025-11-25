@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">⭐ Mes prestataires favoris</h1>
                    <p class="text-gray-600 mt-1">Retrouvez tous vos prestataires favoris en un coup d'œil</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                        {{ $favorites->count() }} favori{{ $favorites->count() > 1 ? 's' : '' }}
                    </span>
                    <a href="{{ route('client.dashboard') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-all duration-200 shadow-sm hover:shadow-md border border-gray-300">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Retour au tableau de bord
                    </a>
                </div>
            </div>
        </div>

        @if($favorites->count() > 0)
            <!-- Liste des prestataires favoris -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($favorites as $prestataire)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 overflow-hidden">
                        <!-- Photo et informations principales -->
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="relative">
                                    @if($prestataire->photo)
                                        <img src="{{ Storage::url($prestataire->photo) }}" alt="{{ $prestataire->user ? $prestataire->user->name : 'Prestataire' }}" class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                                    @else
                                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl border-2 border-gray-200">
                                            {{ $prestataire->user ? strtoupper(substr($prestataire->user->name, 0, 1)) : 'P' }}
                                        </div>
                                    @endif
                                    @if($prestataire->isVerified())
                                        <div class="absolute -top-1 -right-1 w-5 h-5 bg-green-500 rounded-full flex items-center justify-center border-2 border-white">
                                            <i class="fas fa-check text-white text-xs"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $prestataire->user ? $prestataire->user->name : 'Prestataire supprimé' }}</h3>
                                    @if($prestataire->business_name)
                                        <p class="text-sm text-gray-600">{{ $prestataire->business_name }}</p>
                                    @endif
                                    @if($prestataire->location)
                                        <p class="text-sm text-gray-500 flex items-center mt-1">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            {{ $prestataire->location }}
                                        </p>
                                    @endif
                                </div>
                                <!-- Bouton favori -->
                                <form action="{{ route('favorites.toggle', $prestataire) }}" method="POST" class="ml-2">
                                    @csrf
                                    <button type="submit" class="text-yellow-500 hover:text-yellow-600 transition-colors duration-200" title="Retirer des favoris">
                                        <i class="fas fa-star text-xl"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- Services -->
                            @if($prestataire->services->count() > 0)
                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Services proposés :</h4>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($prestataire->services->take(3) as $service)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $service->title }}
                                            </span>
                                        @endforeach
                                        @if($prestataire->services->count() > 3)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                +{{ $prestataire->services->count() - 3 }} autres
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Note et avis -->
                            @if($prestataire->reviews->count() > 0)
                                <div class="flex items-center mb-4">
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= floor($prestataire->average_rating))
                                                <i class="fas fa-star text-yellow-400"></i>
                                            @elseif($i - 0.5 <= $prestataire->average_rating)
                                                <i class="fas fa-star-half-alt text-yellow-400"></i>
                                            @else
                                                <i class="far fa-star text-gray-300"></i>
                                            @endif
                                        @endfor
                                        <span class="ml-2 text-sm text-gray-600">
                                            {{ number_format($prestataire->average_rating, 1) }} ({{ $prestataire->reviews->count() }} avis)
                                        </span>
                                    </div>
                                </div>
                            @endif

                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <a href="{{ route('prestataires.show', $prestataire) }}" class="flex-1 bg-blue-600 text-white text-center py-2 px-4 rounded-lg hover:bg-blue-700 transition-all duration-200 text-sm font-medium">
                                    <i class="fas fa-eye mr-1"></i>
                                    Voir le profil
                                </a>
                                <a href="{{ route('messaging.index') }}" class="flex-1 bg-gray-100 text-gray-700 text-center py-2 px-4 rounded-lg hover:bg-gray-200 transition-all duration-200 text-sm font-medium border border-gray-300">
                                    <i class="fas fa-envelope mr-1"></i>
                                    Contacter
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- État vide -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <div class="max-w-md mx-auto">
                    <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-full flex items-center justify-center">
                        <i class="fas fa-star text-4xl text-yellow-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Aucun prestataire favori</h3>
                    <p class="text-gray-600 mb-6">
                        Vous n'avez pas encore ajouté de prestataires à vos favoris. Parcourez notre plateforme pour découvrir des prestataires de qualité.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ route('services.index') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-all duration-200 font-medium">
                            <i class="fas fa-search mr-2"></i>
                            Parcourir les services
                        </a>
                        <a href="{{ route('client.browse.prestataires') }}" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-200 transition-all duration-200 font-medium border border-gray-300">
                            <i class="fas fa-users mr-2"></i>
                            Voir les prestataires
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection