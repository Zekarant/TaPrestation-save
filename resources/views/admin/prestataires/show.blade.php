@extends('layouts.admin-modern')

@section('title', 'Détails du prestataire')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" data-current-user-id="{{ auth()->id() }}">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('administrateur.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                    Dashboard
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <a href="{{ route('administrateur.prestataires.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Prestataires</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $prestataire->user->name }}</span>
                </div>
            </li>
        </ol>
    </nav>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 relative" role="alert">
            {{ session('success') }}
            <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
            </button>
        </div>
    @endif

    <!-- Section Identité -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 hover:shadow-md transition-shadow duration-300">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-t-lg px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
            <h2 class="text-base sm:text-lg font-semibold flex items-center gap-2">
                <i class="fas fa-user-circle"></i> Identité du Prestataire
            </h2>
        </div>
        <div class="p-3 sm:p-4 lg:p-6">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4 sm:gap-6">
                <div class="flex-shrink-0 text-center lg:text-left">
                    <div class="relative inline-block">
                        @if($prestataire->photo)
                            <img src="{{ asset('storage/' . $prestataire->photo) }}" alt="Photo de {{ $prestataire->user->name }}" class="w-20 h-20 sm:w-24 sm:h-24 lg:w-30 lg:h-30 rounded-full object-cover border-4 border-white shadow-lg hover:scale-105 transition-transform duration-300">
                        @elseif($prestataire->user->avatar)
                            <img src="{{ asset('storage/' . $prestataire->user->avatar) }}" alt="Photo de {{ $prestataire->user->name }}" class="w-20 h-20 sm:w-24 sm:h-24 lg:w-30 lg:h-30 rounded-full object-cover border-4 border-white shadow-lg hover:scale-105 transition-transform duration-300">
                        @elseif($prestataire->user->profile_photo_url)
                            <img src="{{ $prestataire->user->profile_photo_url }}" alt="Photo de {{ $prestataire->user->name }}" class="w-20 h-20 sm:w-24 sm:h-24 lg:w-30 lg:h-30 rounded-full object-cover border-4 border-white shadow-lg hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-20 h-20 sm:w-24 sm:h-24 lg:w-30 lg:h-30 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 text-white flex items-center justify-center border-4 border-white shadow-lg">
                                <span class="text-xl sm:text-2xl lg:text-3xl font-semibold">{{ substr($prestataire->user->name, 0, 1) }}</span>
                            </div>
                        @endif
                        @if($prestataire->isVerified())
                            <div class="absolute -top-2 -right-2 w-6 h-6 sm:w-8 sm:h-8 bg-green-500 text-white rounded-full flex items-center justify-center border-3 border-white">
                                <i class="fas fa-check text-xs sm:text-sm"></i>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex-grow min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-3">
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-900 truncate">{{ $prestataire->user->name }}</h3>
                        @if($prestataire->isVerified())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 self-start sm:self-center">
                                <i class="fas fa-check mr-1"></i> Vérifié
                            </span>
                        @endif
                    </div>
                    <p class="text-gray-600 mb-4 flex items-center text-sm sm:text-base break-all">
                        <i class="fas fa-envelope mr-2 flex-shrink-0"></i>{{ $prestataire->user->email }}
                    </p>
                    
                    <!-- Statut -->
                    <div class="mb-4">
                        @if($prestataire->user->blocked_at)
                            <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium bg-red-100 text-red-800">
                                <i class="fas fa-lock mr-1 sm:mr-2"></i> Bloqué
                            </span>
                        @elseif($prestataire->is_approved)
                            <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1 sm:mr-2"></i> Approuvé
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1 sm:mr-2"></i> En attente
                            </span>
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <div class="text-xs sm:text-sm font-medium text-gray-700 mb-1">Date d'inscription</div>
                            <div class="text-xs sm:text-sm text-gray-600">{{ $prestataire->created_at->format('d/m/Y') }}</div>
                        </div>
                        <div>
                            <div class="text-xs sm:text-sm font-medium text-gray-700 mb-1">Dernière mise à jour</div>
                            <div class="text-xs sm:text-sm text-gray-600">{{ $prestataire->updated_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>
                <div class="flex-shrink-0 w-full lg:w-auto">
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('administrateur.prestataires.index') }}" class="inline-flex items-center justify-center px-2 sm:px-3 py-1 sm:py-2 border border-gray-300 text-xs sm:text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-arrow-left mr-1 sm:mr-2"></i> Retour
                        </a>
                        <a href="{{ route('prestataires.show', $prestataire->id) }}" target="_blank" class="inline-flex items-center justify-center px-2 sm:px-3 py-1 sm:py-2 border border-blue-300 text-xs sm:text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-eye mr-1 sm:mr-2"></i> Profil public
                        </a>
                        @if(!$prestataire->is_approved)
                            <form method="POST" action="{{ route('administrateur.prestataires.approve', $prestataire) }}" class="inline">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center px-2 sm:px-3 py-1 sm:py-2 border border-transparent text-xs sm:text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                                    <i class="fas fa-check mr-1 sm:mr-2"></i> Approuver
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('administrateur.prestataires.toggle-block', $prestataire->id) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full inline-flex items-center justify-center px-2 sm:px-3 py-1 sm:py-2 border border-transparent text-xs sm:text-sm leading-4 font-medium rounded-md text-white {{ $prestataire->user->blocked_at ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500' }} focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200">
                                <i class="fas {{ $prestataire->user->blocked_at ? 'fa-unlock' : 'fa-lock' }} mr-1 sm:mr-2"></i> 
                                {{ $prestataire->user->blocked_at ? 'Débloquer' : 'Bloquer' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
           
        </div>
    </div>
    
    <!-- Section Présentation -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 hover:shadow-md transition-shadow duration-300">
        <div class="bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-t-lg px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
            <h2 class="text-base sm:text-lg font-semibold flex items-center gap-2">
                <i class="fas fa-user-edit"></i> Présentation
            </h2>
        </div>
        <div class="p-3 sm:p-4 lg:p-6">
            <div class="bg-gray-50 p-3 sm:p-4 rounded-lg text-xs sm:text-sm text-gray-700 leading-relaxed">
                {{ $prestataire->description ?? 'Aucune présentation fournie.' }}
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6">
        <!-- Section Services -->
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-300">
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-t-lg px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
                    <h2 class="text-base sm:text-lg font-semibold flex items-center gap-2">
                        <i class="fas fa-cogs"></i> Services ({{ $prestataire->services ? $prestataire->services->count() : 0 }})
                    </h2>
                </div>
                <div class="p-3 sm:p-4 lg:p-6">
                    @if($prestataire->services && $prestataire->services->count() > 0)
                        <div class="space-y-3 sm:space-y-4">
                            @foreach($prestataire->services->take(5) as $service)
                                <div class="border-b border-gray-200 pb-3 sm:pb-4 last:border-b-0 last:pb-0">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2 sm:gap-0">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-medium text-gray-900 mb-1 text-sm sm:text-base truncate">{{ $service->title }}</h3>
                                            <p class="text-xs sm:text-sm text-gray-600 mb-2 break-words">{{ Str::limit($service->description, 100) }}</p>
                                            <small class="text-xs text-gray-500">Créé le {{ $service->created_at->format('d/m/Y') }}</small>
                                        </div>
                                        <div class="self-start sm:ml-4">
                                            <span class="inline-flex items-center px-2 sm:px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ number_format($service->price, 2) }} €</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($prestataire->services->count() > 5)
                            <div class="text-center mt-3 sm:mt-4">
                                <a href="{{ route('administrateur.services.index', ['prestataire' => $prestataire->id]) }}" class="inline-flex items-center px-2 sm:px-3 py-1 sm:py-2 border border-blue-300 text-xs sm:text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <i class="fas fa-list mr-1 sm:mr-2"></i> Voir tous les services
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-center text-xs sm:text-sm">
                            <i class="fas fa-info-circle mr-2"></i> Aucun service proposé
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Section Avis clients -->
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-300">
                <div class="bg-gradient-to-r from-yellow-600 to-orange-600 text-white rounded-t-lg px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
                    <h2 class="text-base sm:text-lg font-semibold flex items-center gap-2">
                        <i class="fas fa-star"></i> Avis Clients ({{ $prestataire->reviews ? $prestataire->reviews->count() : 0 }})
                    </h2>
                </div>
                <div class="p-3 sm:p-4 lg:p-6">
                    @if($prestataire->reviews && $prestataire->reviews->count() > 0)
                        <div class="space-y-3 sm:space-y-4">
                            @foreach($prestataire->reviews->take(5) as $review)
                                <div class="border-b border-gray-200 pb-3 sm:pb-4 last:border-b-0 last:pb-0">
                                    <div class="flex flex-col gap-2">
                                        <div class="flex-1">
                                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mb-2">
                                                <h3 class="font-medium text-gray-900 text-sm sm:text-base">{{ $review->client_name }}</h3>
                                                <div class="flex items-center">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= $review->rating)
                                                            <i class="fas fa-star text-yellow-400 text-xs sm:text-sm"></i>
                                                        @else
                                                            <i class="far fa-star text-gray-300 text-xs sm:text-sm"></i>
                                                        @endif
                                                    @endfor
                                                    <span class="ml-1 text-xs text-gray-500">({{ $review->rating }}/5)</span>
                                                </div>
                                            </div>
                                            <p class="text-xs sm:text-sm text-gray-600 mb-2 break-words">{{ Str::limit($review->comment, 120) }}</p>
                                            <small class="text-xs text-gray-500">{{ $review->created_at->format('d/m/Y') }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($prestataire->reviews->count() > 5)
                            <div class="text-center mt-3 sm:mt-4">
                                <a href="{{ route('administrateur.reviews.index', ['prestataire' => $prestataire->id]) }}" class="inline-flex items-center px-2 sm:px-3 py-1 sm:py-2 border border-blue-300 text-xs sm:text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <i class="fas fa-comments mr-1 sm:mr-2"></i> Voir tous les avis
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-center text-xs sm:text-sm">
                            <i class="fas fa-info-circle mr-2"></i> Aucun avis reçu
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistiques -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-300">
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-t-lg px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
            <h2 class="text-base sm:text-lg font-semibold flex items-center gap-2">
                <i class="fas fa-chart-bar"></i> Statistiques
            </h2>
        </div>
        <div class="p-3 sm:p-4 lg:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-bold text-blue-600 mb-1 sm:mb-2">{{ $prestataire->services ? $prestataire->services->count() : 0 }}</div>
                    <div class="text-xs sm:text-sm font-medium text-gray-700">Services</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-bold text-green-600 mb-1 sm:mb-2">{{ $prestataire->reviews ? $prestataire->reviews->count() : 0 }}</div>
                    <div class="text-xs sm:text-sm font-medium text-gray-700">Avis</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-bold text-yellow-600 mb-1 sm:mb-2">{{ $prestataire->rating_average ? number_format($prestataire->rating_average, 1) : '0.0' }}</div>
                    <div class="text-xs sm:text-sm font-medium text-gray-700">Note moyenne</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection