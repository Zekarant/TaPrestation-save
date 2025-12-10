@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        /* Animations et transitions */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pulse-green {
            animation: pulseGreen 2s infinite;
        }

        @keyframes pulseGreen {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
            }

            50% {
                box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
            }
        }

        /* Amélioration des cartes */
        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Badge de statut */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-available {
            background-color: #dcfce7;
            color: #166534;
        }

        /* Navigation arrows */
        .nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s;
            z-index: 10;
        }

        .nav-arrow:hover {
            background-color: rgba(0, 0, 0, 0.7);
        }

        .nav-arrow.left {
            left: 10px;
        }

        .nav-arrow.right {
            right: 10px;
        }

        /* Image counter */
        .image-counter {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
    </style>
@endpush

@section('title', $equipment->name . ' - Location de matériel - TaPrestation')

@section('content')
    <div class="min-h-screen bg-green-50">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 py-2 sm:py-3 lg:py-4">
            <!-- Breadcrumb -->
            <nav class="flex mb-2 sm:mb-3 lg:mb-4 overflow-x-auto" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 sm:space-x-2 md:space-x-3 whitespace-nowrap">
                    <li class="inline-flex items-center">
                        <a href="{{ route('home') }}"
                            class="inline-flex items-center text-xs sm:text-sm font-medium text-green-700 hover:text-green-600">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                                </path>
                            </svg>
                            <span class="hidden sm:inline">Accueil</span>
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-gray-400 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <a href="{{ route('equipment.index') }}"
                                class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-700 hover:text-green-600 truncate">Matériel
                                à louer</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-gray-400 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span
                                class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-500 truncate max-w-32 sm:max-w-none">{{ Str::limit($equipment->name, 30) }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Titre et prix par jour au-dessus de l'image -->
            <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 mb-3 sm:mb-4 border border-green-100">
                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mb-1 sm:mb-2 leading-tight break-words">
                    {{ $equipment->name }}</h1>

                <!-- Prix par jour uniquement -->
                @if($equipment->price_per_day)
                    <div class="text-lg sm:text-xl lg:text-2xl font-bold text-green-600">
                        {{ number_format($equipment->price_per_day, 2) }}€ / jour</div>
                @endif
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
                <!-- Image et description -->
                <div class="xl:col-span-2">
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-green-100 card-hover fade-in">
                        @if($equipment->photos && count($equipment->photos) > 0)
                            <div class="relative">
                                <!-- Image principale avec flèches de navigation -->
                                <div class="relative">
                                    <img id="mainImage" src="{{ Storage::url($equipment->photos[0]) }}"
                                        alt="{{ $equipment->name }}"
                                        class="w-full h-48 sm:h-56 lg:h-64 object-cover cursor-pointer"
                                        onclick="openImageModal(0)">

                                    <!-- Flèche gauche -->
                                    @if(count($equipment->photos) > 1)
                                        <button id="prevButton" onclick="navigateImage(-1)" class="nav-arrow left">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 19l-7-7 7-7"></path>
                                            </svg>
                                        </button>
                                    @endif

                                    <!-- Flèche droite -->
                                    @if(count($equipment->photos) > 1)
                                        <button id="nextButton" onclick="navigateImage(1)" class="nav-arrow right">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </button>
                                    @endif

                                    <!-- Indicateur d'image -->
                                    @if(count($equipment->photos) > 1)
                                        <div class="image-counter">
                                            <span id="imageCounter">1 / {{ count($equipment->photos) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div
                                    class="absolute top-2 sm:top-4 left-2 sm:left-4 status-badge status-available pulse-green text-xs sm:text-sm">
                                    Disponible
                                </div>
                            </div>
                        @elseif($equipment->main_photo)
                            <div class="relative">
                                <div class="relative">
                                    <img id="mainImage" src="{{ Storage::url($equipment->main_photo) }}"
                                        alt="{{ $equipment->name }}"
                                        class="w-full h-48 sm:h-56 lg:h-64 object-cover cursor-pointer"
                                        onclick="openImageModal(0)">
                                </div>
                                <div
                                    class="absolute top-2 sm:top-4 left-2 sm:left-4 status-badge status-available pulse-green text-xs sm:text-sm">
                                    Disponible
                                </div>
                            </div>
                        @else
                            <div class="h-48 sm:h-56 lg:h-64 bg-gray-200 flex items-center justify-center">
                                <div class="text-center px-4">
                                    <svg class="w-12 h-12 sm:w-16 sm:h-16 text-gray-400 mx-auto mb-3 sm:mb-4" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <p class="text-sm sm:text-base text-gray-500">Aucune photo disponible</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Description -->
                    <div
                        class="bg-white rounded-lg shadow-sm p-3 sm:p-4 mt-3 sm:mt-4 border border-green-100 card-hover fade-in">
                        <h2 class="text-base sm:text-lg font-semibold text-green-900 mb-2 sm:mb-3">Description</h2>
                        <div class="prose max-w-none text-sm sm:text-base text-green-700 leading-relaxed">
                            {!! nl2br(e($equipment->description)) !!}
                        </div>
                    </div>



                    <!-- Spécifications techniques -->
                    @if($equipment->technical_specifications)
                        <div
                            class="bg-white rounded-lg shadow-sm p-3 sm:p-4 mt-3 sm:mt-4 border border-green-100 card-hover fade-in">
                            <h2 class="text-base sm:text-lg font-semibold text-green-900 mb-2 sm:mb-3">Spécifications techniques
                            </h2>
                            <div class="prose max-w-none text-sm sm:text-base text-green-700 leading-relaxed">
                                {!! nl2br(e($equipment->technical_specifications)) !!}
                            </div>
                        </div>
                    @endif

                    <!-- Accessoires -->
                    @if($equipment->included_accessories || $equipment->optional_accessories)
                        <div
                            class="bg-white rounded-lg shadow-sm p-3 sm:p-4 mt-3 sm:mt-4 border border-green-100 card-hover fade-in">
                            <h2 class="text-base sm:text-lg font-semibold text-green-900 mb-2 sm:mb-3">Accessoires</h2>

                            @if($equipment->included_accessories && count($equipment->included_accessories) > 0)
                                <div class="mb-2 sm:mb-3">
                                    <h3 class="font-medium text-sm sm:text-base text-green-900 mb-1">Inclus dans la location</h3>
                                    <ul class="list-disc list-inside space-y-0.5 text-xs sm:text-sm text-green-700 ml-2">
                                        @foreach($equipment->included_accessories as $accessory)
                                            <li class="break-words">{{ $accessory }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($equipment->optional_accessories && count($equipment->optional_accessories) > 0)
                                <div>
                                    <h3 class="font-medium text-sm sm:text-base text-green-900 mb-1">Accessoires optionnels</h3>
                                    <ul class="list-disc list-inside space-y-0.5 text-xs sm:text-sm text-green-700 ml-2">
                                        @foreach($equipment->optional_accessories as $accessory)
                                            <li class="break-words">{{ $accessory }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Conditions de location -->
                    @if($equipment->rental_conditions)
                        <div
                            class="bg-white rounded-lg shadow-sm p-3 sm:p-4 mt-3 sm:mt-4 border border-green-100 card-hover fade-in">
                            <h2 class="text-base sm:text-lg font-semibold text-green-900 mb-2 sm:mb-3">Conditions de location
                            </h2>
                            <div class="prose max-w-none text-sm sm:text-base text-green-700 leading-relaxed">
                                {!! nl2br(e($equipment->rental_conditions)) !!}
                            </div>
                        </div>
                    @endif

                    <!-- Instructions d'utilisation -->
                    @if($equipment->usage_instructions)
                        <div class="bg-white rounded-lg shadow-sm p-6 mt-6 card-hover fade-in">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Instructions d'utilisation</h2>
                            <div class="prose max-w-none text-gray-700">
                                {!! nl2br(e($equipment->usage_instructions)) !!}
                            </div>
                        </div>
                    @endif

                    <!-- Instructions de sécurité -->
                    @if($equipment->safety_instructions)
                        <div class="bg-white rounded-lg shadow-sm p-6 mt-6 card-hover fade-in">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Instructions de sécurité</h2>
                            <div
                                class="prose max-w-none text-gray-700 bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-400">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <div>{!! nl2br(e($equipment->safety_instructions)) !!}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="xl:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 sticky top-4 card-hover fade-in">
                        <!-- Propriétaire -->
                        <div class="mb-3 sm:mb-4">
                            <h2 class="text-base sm:text-lg font-semibold text-green-900 mb-2 sm:mb-3">Propriétaire</h2>
                            <a href="{{ route('prestataires.show', $equipment->prestataire) }}"
                                class="block hover:bg-green-50 p-2 rounded-lg transition-colors duration-200">
                                <div class="flex items-center space-x-2 sm:space-x-3">
                                    @if($equipment->prestataire && $equipment->prestataire->photo)
                                        <img src="{{ Storage::url($equipment->prestataire->photo) }}"
                                            alt="{{ $equipment->prestataire->user->name ?? '' }}"
                                            class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover border-2 border-green-200">
                                    @else
                                        <div
                                            class="w-8 h-8 sm:w-10 sm:h-10 bg-green-100 rounded-full flex items-center justify-center border-2 border-green-200">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <h3
                                            class="text-sm sm:text-base font-semibold text-green-900 truncate hover:text-green-700">
                                            {{ $equipment->prestataire->user->name ?? '' }}</h3>

                                        <!-- Évaluations avec étoiles -->
                                        @php
                                            $averageRating = $equipment->prestataire->reviews()->avg('rating') ?? 0;
                                            $reviewCount = $equipment->prestataire->reviews()->count();
                                        @endphp
                                        @if($reviewCount > 0)
                                            <div class="flex items-center mt-1 mb-2">
                                                <div class="flex items-center mr-2">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= floor($averageRating))
                                                            <svg class="w-3 h-3 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                                                <path
                                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                            </svg>
                                                        @elseif($i == ceil($averageRating) && $averageRating - floor($averageRating) >= 0.5)
                                                            <svg class="w-3 h-3 text-yellow-400" viewBox="0 0 20 20">
                                                                <defs>
                                                                    <linearGradient id="half-fill-equipment-{{ $i }}">
                                                                        <stop offset="50%" stop-color="currentColor" />
                                                                        <stop offset="50%" stop-color="#e5e7eb" />
                                                                    </linearGradient>
                                                                </defs>
                                                                <path fill="url(#half-fill-equipment-{{ $i }})"
                                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                            </svg>
                                                        @else
                                                            <svg class="w-3 h-3 text-gray-300 fill-current" viewBox="0 0 20 20">
                                                                <path
                                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                            </svg>
                                                        @endif
                                                    @endfor
                                                </div>
                                                <span class="text-xs text-gray-600">{{ number_format($averageRating, 1) }}
                                                    ({{ $reviewCount }} avis)</span>
                                            </div>
                                        @else
                                            <div class="flex items-center mt-1 mb-2">
                                                <span class="text-xs text-gray-500">Aucun avis pour le moment</span>
                                            </div>
                                        @endif

                                        <div class="flex items-center text-xs sm:text-sm text-green-600 mt-1">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                                </path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span class="truncate">
                                                @if($equipment->city)
                                                    {{ $equipment->city }}
                                                    @if($equipment->postal_code), {{ $equipment->postal_code }}@endif
                                                @else
                                                    {{ $equipment->address ?? 'Localisation non spécifiée' }}
                                                @endif
                                            </span>
                                        </div>
                                        <div class="flex items-center text-xs sm:text-sm text-green-600 mt-1">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>Publié {{ $equipment->created_at->diffForHumans() }}</span>
                                        </div>
                                        @if($equipment->view_count)
                                            <div class="flex items-center text-xs sm:text-sm text-green-600 mt-1">
                                                <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span>{{ $equipment->view_count }} vue(s)</span>
                                            </div>
                                        @endif
                                        @if($equipment->total_rentals)
                                            <div class="flex items-center text-xs sm:text-sm text-green-600 mt-1">
                                                <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span>{{ $equipment->total_rentals }} location(s) réalisée(s)</span>
                                            </div>
                                        @endif
                                        <div class="text-xs text-green-600 mt-1">Cliquez pour voir le profil</div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Détails -->
                        <div class="mb-3 sm:mb-4">
                            <div class="flex items-center justify-between mb-2 sm:mb-3">
                                <h2 class="text-base sm:text-lg font-semibold text-green-900">Détails</h2>
                            </div>

                            <!-- Catégories (toujours visibles) -->
                            @if($equipment->category || $equipment->subcategory)
                                <div class="space-y-2 sm:space-y-3 mb-3">
                                    @if($equipment->category)
                                        <div class="text-sm sm:text-base">
                                            <span class="font-medium text-green-900">Catégorie :</span>
                                            <span class="text-green-700">{{ $equipment->category->name }}</span>
                                        </div>
                                    @endif

                                    @if($equipment->subcategory && $equipment->subcategory->id !== $equipment->category_id)
                                        <div class="text-sm sm:text-base">
                                            <span class="font-medium text-green-900">Sous-catégorie :</span>
                                            <span class="text-green-700">{{ $equipment->subcategory->name }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Bouton View more details -->
                            <button id="toggleDetailsBtn" onclick="toggleEquipmentDetails()"
                                class="w-full text-left bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg p-2 mb-3 transition-colors duration-200 flex items-center justify-between">
                                <span class="text-sm font-medium text-green-700">Voir plus de détails</span>
                                <svg id="toggleDetailsIcon"
                                    class="w-4 h-4 text-green-500 transform transition-transform duration-200" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                    </path>
                                </svg>
                            </button>

                            <!-- Détails complets (cachés par défaut) -->
                            <div id="equipmentDetailsContent" class="space-y-2 sm:space-y-3 hidden">
                                <!-- Autres prix -->
                                @if($equipment->price_per_hour)
                                    <div class="text-sm sm:text-base">
                                        <span class="font-medium text-green-900">Prix horaire :</span>
                                        <span class="text-green-700">{{ number_format($equipment->price_per_hour, 2) }}€ /
                                            heure</span>
                                    </div>
                                @endif

                                @if($equipment->price_per_week)
                                    <div class="text-sm sm:text-base">
                                        <span class="font-medium text-green-900">Prix semaine :</span>
                                        <span class="text-green-700">{{ number_format($equipment->price_per_week, 2) }}€ /
                                            semaine</span>
                                    </div>
                                @endif

                                @if($equipment->price_per_month)
                                    <div class="text-sm sm:text-base">
                                        <span class="font-medium text-green-900">Prix mensuel :</span>
                                        <span class="text-green-700">{{ number_format($equipment->price_per_month, 2) }}€ /
                                            mois</span>
                                    </div>
                                @endif

                                <!-- Caution et frais -->
                                @if($equipment->security_deposit)
                                    <div class="text-sm sm:text-base">
                                        <span class="font-medium text-green-900">Caution :</span>
                                        <span
                                            class="text-green-700">{{ number_format($equipment->security_deposit, 2) }}€</span>
                                    </div>
                                @endif

                                @if($equipment->delivery_fee && !$equipment->delivery_included)
                                    <div class="text-sm sm:text-base">
                                        <span class="font-medium text-green-900">Frais de livraison :</span>
                                        <span class="text-green-700">{{ number_format($equipment->delivery_fee, 2) }}€</span>
                                    </div>
                                @elseif($equipment->delivery_included)
                                    <div class="text-sm sm:text-base">
                                        <span class="font-medium text-green-900">Livraison :</span>
                                        <span class="text-green-600">Incluse</span>
                                    </div>
                                @endif

                                @if($equipment->brand)
                                    <div class="text-sm sm:text-base">
                                        <span class="font-medium text-green-900">Marque :</span>
                                        <span class="text-green-700 break-words">{{ $equipment->brand }}</span>
                                    </div>
                                @endif

                                @if($equipment->model)
                                    <div class="text-sm sm:text-base">
                                        <span class="font-medium text-green-900">Modèle :</span>
                                        <span class="text-green-700 break-words">{{ $equipment->model }}</span>
                                    </div>
                                @endif

                                @if($equipment->condition)
                                    <div class="text-sm sm:text-base">
                                        <span class="font-medium text-green-900">État :</span>
                                        <span class="text-green-700">{{ $equipment->formatted_condition }}</span>
                                    </div>
                                @endif

                                @if($equipment->weight)
                                    <div class="text-sm sm:text-base">
                                        <span class="font-medium text-green-900">Poids :</span>
                                        <span class="text-green-700">{{ $equipment->weight }} kg</span>
                                    </div>
                                @endif

                                @if($equipment->dimensions)
                                    <div class="text-sm sm:text-base">
                                        <span class="font-medium text-green-900">Dimensions :</span>
                                        <span class="text-green-700 break-words">{{ $equipment->dimensions }}</span>
                                    </div>
                                @endif

                                @if($equipment->power_requirements)
                                    <div class="text-sm sm:text-base">
                                        <span class="font-medium text-green-900">Alimentation :</span>
                                        <span class="text-green-700 break-words">{{ $equipment->power_requirements }}</span>
                                    </div>
                                @endif

                                @if($equipment->minimum_age)
                                    <div class="text-sm sm:text-base">
                                        <span class="font-medium text-green-900">Âge minimum :</span>
                                        <span class="text-green-700">{{ $equipment->minimum_age }} ans</span>
                                    </div>
                                @endif

                                @if($equipment->requires_license)
                                    <div class="text-sm sm:text-base">
                                        <span class="font-medium text-green-900">Permis requis :</span>
                                        <span class="text-red-600">{{ $equipment->required_license_type ?? 'Oui' }}</span>
                                    </div>
                                @endif

                                @if($equipment->minimum_rental_duration || $equipment->maximum_rental_duration)
                                    <div class="border-t border-green-200 pt-3 sm:pt-4 mt-4">
                                        <h3 class="font-medium text-sm sm:text-base text-green-900 mb-2">Durée de location</h3>
                                        <div class="space-y-1 text-xs sm:text-sm text-green-700">
                                            @if($equipment->minimum_rental_duration)
                                                <div>Durée minimum : {{ $equipment->minimum_rental_duration }} jour(s)</div>
                                            @endif
                                            @if($equipment->maximum_rental_duration)
                                                <div>Durée maximum : {{ $equipment->maximum_rental_duration }} jour(s)</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="border-t border-green-200 pt-3 sm:pt-4 mt-3 sm:mt-4">
                            @if(isset($isOwner) && $isOwner)
                                <!-- Pour le propriétaire: un seul bouton Modifier -->
                                <a href="{{ route('prestataire.equipment.edit', $equipment) }}"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl flex items-center justify-center text-center">
                                    <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                    <span>Modifier l'équipement</span>
                                </a>

                                <!-- Bouton de suppression -->
                                <button type="button" id="deleteEquipmentBtn"
                                    class="w-full mt-3 bg-red-100 hover:bg-red-200 text-red-700 border border-red-300 px-4 py-3 rounded-lg transition duration-200 font-medium flex items-center justify-center text-center">
                                    <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-2-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                    <span>Supprimer l'équipement</span>
                                </button>
                            @else
                                <!-- Pour les autres utilisateurs: trois boutons -->
                                <div class="flex gap-2">
                                    <a href="{{ route('equipment.reserve', $equipment) }}"
                                        class="flex-1 bg-green-600 hover:bg-green-700 text-white px-2 py-2 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl flex items-center justify-center text-center text-xs">
                                        <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0V6a2 2 0 012-2h4a2 2 0 012 2v1m-6 0h6m-6 0l-.5 3.5A2 2 0 003.5 13H20.5a2 2 0 002-2l-.5-3.5m-15 0h15">
                                            </path>
                                        </svg>
                                        <span>Réserver</span>
                                    </a>

                                    <a href="{{ route('messaging.start', $equipment->prestataire) }}"
                                        class="flex-1 bg-white hover:bg-gray-50 text-green-600 border border-green-600 px-2 py-2 rounded-lg transition duration-200 font-semibold flex items-center justify-center text-center text-xs">
                                        <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9 8s9 3.582 9 8z">
                                            </path>
                                        </svg>
                                        <span>Contacter</span>
                                    </a>

                                    <button onclick="openReportModal()"
                                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-2 py-2 rounded-lg transition duration-200 font-medium flex items-center justify-center text-center text-xs">
                                        <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-1.333-1.964-1.333-2.732 0L4 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9 8s9 3.582 9 8z">
                                            </path>
                                        </svg>
                                        <span>Signaler</span>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Location Map -->
                        @if ($equipment->latitude && $equipment->longitude)
                            <div class="border-t border-green-200 pt-3 sm:pt-4 mt-3 sm:mt-4">
                                <h3 class="text-sm sm:text-base font-semibold text-green-900 mb-1 sm:mb-2">Localisation sur
                                    carte</h3>
                                <div id="map" style="height: 150px;" class="sm:h-48 rounded-lg z-10"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Similar Equipment -->
            <div class="mt-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Équipements similaires</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
                    @if(isset($similarEquipment) && $similarEquipment->count() > 0)
                        @foreach($similarEquipment as $item)
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                                <a href="{{ route('equipment.show', $item) }}">
                                    <img src="{{ $item->main_photo ? Storage::url($item->main_photo) : (isset($item->photos[0]) ? Storage::url($item->photos[0]) : 'https://via.placeholder.com/300x150?text=No+Image') }}"
                                        alt="{{ $item->name }}" class="h-32 w-full object-cover">
                                </a>
                                <div class="p-2">
                                    <h3 class="font-semibold text-sm mb-1"><a
                                            href="{{ route('equipment.show', $item) }}">{{ Str::limit($item->name, 25) }}</a></h3>
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="font-bold text-green-600 text-xs">{{ number_format($item->daily_rate, 0) }}€/j</span>
                                        <a href="{{ route('equipment.show', $item) }}"
                                            class="text-green-600 hover:text-green-800 font-semibold text-xs">Voir</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-gray-500">Aucun équipement similaire trouvé.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de signalement -->
    <div id="reportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full border border-green-200">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Signaler cet équipement</h3>
                        <button onclick="closeReportModal()"
                            class="text-gray-400 hover:text-gray-600 transition duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form action="{{ route('equipment.report', $equipment) }}" method="POST">
                        @csrf
                        <input type="hidden" name="reason" id="reason" value="">
                        <div class="mb-4">
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Catégorie du
                                signalement</label>
                            <select id="category" name="category" required onchange="updateReason()"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200">
                                <option value="">Sélectionnez une catégorie</option>
                                <option value="inappropriate">Contenu inapproprié</option>
                                <option value="fraud">Annonce frauduleuse</option>
                                <option value="safety">Problème de sécurité</option>
                                <option value="condition">État de l'équipement</option>
                                <option value="pricing">Prix incorrect</option>
                                <option value="availability">Disponibilité incorrecte</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description
                                détaillée</label>
                            <textarea id="description" name="description" rows="4" required minlength="20" maxlength="1000"
                                placeholder="Décrivez le problème en détail (minimum 20 caractères)..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200"></textarea>
                        </div>

                        <div class="flex space-x-3">
                            <button type="button" onclick="closeReportModal()"
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-6 py-3 rounded-lg transition duration-200 font-medium">
                                Annuler
                            </button>
                            <button type="submit"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition duration-200 font-semibold shadow-lg hover:shadow-xl">
                                Signaler
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden flex items-center justify-center p-4">
        <div class="relative max-w-7xl max-h-full">
            <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain">

            <!-- Close button -->
            <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Navigation arrows -->
            <button id="prevBtnModal" onclick="navigateImageModal(-1)"
                class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 z-10">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>

            <button id="nextBtnModal" onclick="navigateImageModal(1)"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 z-10">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            <!-- Image counter -->
            <div
                class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-white bg-black bg-opacity-50 px-3 py-1 rounded-full text-sm">
                <span id="currentImageNumber">1</span> / <span id="totalImages">1</span>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="deleteConfirmationModal"
        class="fixed inset-0 flex items-center justify-center z-50 hidden transition-opacity duration-300"
        style="backdrop-filter: blur(5px); background-color: rgba(16, 185, 129, 0.8);">
        <div
            class="bg-white rounded-xl shadow-2xl p-6 sm:p-8 max-w-md w-full mx-4 border-4 border-red-500 transform transition-all duration-300">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100">
                    <svg class="h-10 w-10 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mt-4">Confirmation de suppression</h3>
                <p class="text-gray-600 mt-2">
                    Êtes-vous sûr de vouloir supprimer cet équipement ?
                </p>
                <p id="equipmentName" class="text-lg font-semibold text-green-900 mt-2">{{ $equipment->name }}</p>
                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <button id="cancelDeleteBtn"
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200 font-medium">
                        Annuler
                    </button>
                    <button id="confirmDeleteBtn"
                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 font-medium">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
@endsection

@push('scripts')
    @if ($equipment->latitude && $equipment->longitude)
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            var map = L.map('map').setView([{{ $equipment->latitude }}, {{ $equipment->longitude }}], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            L.marker([{{ $equipment->latitude }}, {{ $equipment->longitude }}]).addTo(map)
                .bindPopup('Localisation approximative de l\'équipement.')
                .openPopup();
        </script>
    @endif

    <script>
        // Image gallery functionality
        let currentImageIndex = 0;
        const images = [
            @if($equipment->photos && count($equipment->photos) > 0)
                @foreach($equipment->photos as $photo)
                    '{{ Storage::url($photo) }}',
                @endforeach
            @elseif($equipment->main_photo)
                '{{ Storage::url($equipment->main_photo) }}'
            @endif
        ];

        // Fonction pour naviguer entre les images avec navigation circulaire (image principale)
        function navigateImage(direction) {
            if (images.length <= 1) return;

            // Calculer le nouvel index avec navigation circulaire
            currentImageIndex += direction;

            if (currentImageIndex < 0) {
                currentImageIndex = images.length - 1; // Revenir à la dernière image
            } else if (currentImageIndex >= images.length) {
                currentImageIndex = 0; // Revenir à la première image
            }

            // Mettre à jour l'image principale
            const mainImage = document.getElementById('mainImage');
            const imageCounter = document.getElementById('imageCounter');

            if (mainImage) {
                mainImage.src = images[currentImageIndex];
            }

            if (imageCounter) {
                imageCounter.textContent = `${currentImageIndex + 1} / ${images.length}`;
            }
        }

        function openImageModal(index) {
            currentImageIndex = index;
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const currentNumber = document.getElementById('currentImageNumber');
            const totalImages = document.getElementById('totalImages');
            const prevBtn = document.getElementById('prevBtnModal');
            const nextBtn = document.getElementById('nextBtnModal');

            modalImage.src = images[currentImageIndex];
            currentNumber.textContent = currentImageIndex + 1;
            totalImages.textContent = images.length;

            // Show/hide navigation buttons based on images length
            if (images.length <= 1) {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
            } else {
                prevBtn.style.display = 'block';
                nextBtn.style.display = 'block';
            }

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Fonction pour naviguer dans le modal avec navigation circulaire
        function navigateImageModal(direction) {
            if (images.length <= 1) return;

            // Calculer le nouvel index avec navigation circulaire
            currentImageIndex += direction;

            if (currentImageIndex < 0) {
                currentImageIndex = images.length - 1; // Revenir à la dernière image
            } else if (currentImageIndex >= images.length) {
                currentImageIndex = 0; // Revenir à la première image
            }

            const modalImage = document.getElementById('modalImage');
            const currentNumber = document.getElementById('currentImageNumber');

            modalImage.src = images[currentImageIndex];
            currentNumber.textContent = currentImageIndex + 1;
        }

        // Equipment details toggle functionality
        function toggleEquipmentDetails() {
            const content = document.getElementById('equipmentDetailsContent');
            const btn = document.getElementById('toggleDetailsBtn');
            const icon = document.getElementById('toggleDetailsIcon');
            const btnText = btn.querySelector('span');

            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.add('rotate-90');
                btnText.textContent = 'Masquer les détails';
            } else {
                content.classList.add('hidden');
                icon.classList.remove('rotate-90');
                btnText.textContent = 'Voir plus de détails';
            }
        }

        function openReportModal() {
            document.getElementById('reportModal').classList.remove('hidden');
        }

        function closeReportModal() {
            document.getElementById('reportModal').classList.add('hidden');
        }

        function updateReason() {
            const category = document.getElementById('category').value;
            const reasonField = document.getElementById('reason');

            const reasonMap = {
                'inappropriate': 'Contenu inapproprié',
                'fraud': 'Annonce frauduleuse',
                'safety': 'Problème de sécurité',
                'condition': 'État de l\'équipement',
                'pricing': 'Prix incorrect',
                'availability': 'Disponibilité incorrecte',
                'other': 'Autre'
            };

            reasonField.value = reasonMap[category] || '';
        }

        // Fermer le modal en cliquant à l'extérieur
        document.getElementById('reportModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeReportModal();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function (e) {
            const modal = document.getElementById('imageModal');
            if (!modal.classList.contains('hidden')) {
                if (e.key === 'Escape') {
                    closeImageModal();
                } else if (e.key === 'ArrowLeft') {
                    navigateImageModal(-1);
                } else if (e.key === 'ArrowRight') {
                    navigateImageModal(1);
                }
            }
        });

        // Close modal when clicking outside the image
        document.getElementById('imageModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeImageModal();
            }
        });

        // Delete equipment modal functionality
        document.addEventListener('DOMContentLoaded', function () {
            const deleteBtn = document.getElementById('deleteEquipmentBtn');
            const deleteModal = document.getElementById('deleteConfirmationModal');
            const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

            if (deleteBtn) {
                deleteBtn.addEventListener('click', function () {
                    deleteModal.classList.remove('hidden');

                    // Add animation classes
                    setTimeout(() => {
                        deleteModal.classList.remove('opacity-0');
                        const modalContent = deleteModal.querySelector('.modal-show');
                        modalContent.classList.remove('scale-95');
                        modalContent.classList.add('scale-100');
                        modalContent.classList.remove('opacity-0');
                    }, 10);
                });
            }

            // Handle cancel delete
            if (cancelDeleteBtn) {
                cancelDeleteBtn.addEventListener('click', function () {
                    closeModal();
                });
            }

            // Handle confirm delete
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function () {
                    // Create a form dynamically and submit it
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('prestataire.equipment.destroy', $equipment) }}';

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';

                    form.appendChild(csrfToken);
                    form.appendChild(methodField);
                    document.body.appendChild(form);
                    form.submit();
                });
            }

            // Close modal when clicking outside
            if (deleteModal) {
                deleteModal.addEventListener('click', function (e) {
                    if (e.target === deleteModal) {
                        closeModal();
                    }
                });
            }

            // Close modal with Escape key
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && deleteModal && !deleteModal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            // Function to close modal with animation
            function closeModal() {
                const modalContent = deleteModal.querySelector('.modal-show');
                if (modalContent) {
                    modalContent.classList.remove('scale-100');
                    modalContent.classList.add('scale-95');
                    modalContent.classList.add('opacity-0');
                }
                if (deleteModal) {
                    deleteModal.classList.add('opacity-0');

                    setTimeout(() => {
                        deleteModal.classList.add('hidden');
                    }, 300);
                }
            }
        });
    </script>
@endpush