@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
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

@section('title', $urgentSale->title . ' - Vente urgente - TaPrestation')

@section('content')
<div class="bg-red-50">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 py-2 sm:py-3 lg:py-4">
        <!-- Breadcrumb -->
        <nav class="flex mb-2 sm:mb-3" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 sm:space-x-2">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center text-xs sm:text-sm font-medium text-gray-700 hover:text-red-600">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        <span class="hidden sm:inline">Accueil</span>
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('urgent-sales.index') }}" class="ml-1 text-xs sm:text-sm font-medium text-gray-700 hover:text-red-600">Annonces</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-xs sm:text-sm font-medium text-gray-500">{{ Str::limit($urgentSale->title, 30) }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Messages de succès/erreur -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Succès!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" onclick="this.parentElement.parentElement.style.display='none'">
                        <title>Fermer</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <strong class="font-bold">Erreur!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" onclick="this.parentElement.parentElement.style.display='none'">
                        <title>Fermer</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4">
            <!-- Galerie d'images -->
            <div class="lg:col-span-2">
                <!-- Titre et prix au-dessus de l'image -->
                <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 mb-3 border border-red-100">
                    <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mb-1 sm:mb-2 leading-tight break-words">{{ $urgentSale->title }}</h1>
                    <div class="text-lg sm:text-xl lg:text-2xl font-bold text-red-600">{{ number_format($urgentSale->price, 2) }}€</div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    @if($urgentSale->photos && count($urgentSale->photos ?? []) > 0)
                        <div class="relative">
                            <!-- Image principale avec flèches de navigation -->
                            <div class="relative">
                                <img id="mainImage" src="{{ Storage::url($urgentSale->photos[0]) }}" alt="{{ $urgentSale->title }}" class="w-full h-32 sm:h-40 lg:h-48 object-cover cursor-pointer rounded-t-lg" onclick="openImageModal(0)">
                                
                                <!-- Flèche gauche -->
                                @if(count($urgentSale->photos ?? []) > 1)
                                    <button id="prevButton" onclick="navigateImage(-1)" class="nav-arrow left">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                    </button>
                                @endif
                                
                                <!-- Flèche droite -->
                                @if(count($urgentSale->photos ?? []) > 1)
                                    <button id="nextButton" onclick="navigateImage(1)" class="nav-arrow right">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                @endif
                                
                                <!-- Indicateur d'image -->
                                @if(count($urgentSale->photos ?? []) > 1)
                                    <div class="image-counter">
                                        <span id="imageCounter">1 / {{ count($urgentSale->photos ?? []) }}</span>
                                    </div>
                                @endif
                                
                                <div class="absolute top-2 right-2 bg-black/50 text-white px-2 py-1 rounded-full text-xs">
                                    État: {{ $urgentSale->condition_label }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="h-32 sm:h-40 lg:h-48 bg-gray-200 flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-8 h-8 sm:w-12 sm:h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-sm text-gray-500">Aucune photo disponible</p>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Description détaillée -->
                <div class="bg-white rounded-xl shadow-lg border border-red-200 p-3 sm:p-4 mt-3">
                    <h2 class="text-base sm:text-lg font-bold text-red-800 mb-2 sm:mb-3">Description de l'article</h2>
                    
                    <!-- Description principale -->
                    <div class="prose max-w-none text-gray-700 text-sm leading-relaxed mb-3">
                        {!! nl2br(e($urgentSale->description)) !!}
                    </div>
                    
                    @if($urgentSale->reason)
                        <div class="border-t border-gray-200 pt-3">
                            <div class="bg-red-50 p-2 rounded-lg">
                                <div class="flex items-center mb-1">
                                    <svg class="w-3 h-3 text-red-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <span class="text-xs font-medium text-red-800">Raison de la vente urgente</span>
                                </div>
                                <p class="text-red-700 text-xs">{{ $urgentSale->reason }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Sidebar d'informations -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm p-3 sm:p-4 lg:sticky lg:top-4">
                    
                    <!-- Informations du vendeur -->
                    <div class="mb-3 sm:mb-4">
                        <h3 class="text-base sm:text-lg font-bold text-red-800 mb-2">Vendeur</h3>
                        <a href="{{ route('prestataires.show', $urgentSale->prestataire) }}" class="block">
                            <div class="flex items-center mb-2 cursor-pointer hover:bg-red-50 p-2 rounded-lg transition-colors duration-200">
                                <div class="relative w-8 h-8 sm:w-10 sm:h-10 mr-2 flex-shrink-0">
                                    @if($urgentSale->prestataire->photo)
                                        <img src="{{ Storage::url($urgentSale->prestataire->photo) }}" alt="{{ $urgentSale->prestataire->user->name }}" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover">
                                    @elseif($urgentSale->prestataire->user->avatar)
                                        <img src="{{ Storage::url($urgentSale->prestataire->user->avatar) }}" alt="{{ $urgentSale->prestataire->user->name }}" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover">
                                    @else
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-sm text-gray-900 hover:text-red-600 transition-colors duration-200 truncate">{{ $urgentSale->prestataire->user->name }}</span>
                                        @if($urgentSale->prestataire->company_name)
                                            <span class="text-xs text-gray-600 truncate">{{ $urgentSale->prestataire->company_name }}</span>
                                        @endif
                                    </div>
                                    
                                    <!-- Évaluations compactes -->
                                    @php
                                        $averageRating = $urgentSale->prestataire->reviews()->avg('rating') ?? 0;
                                        $reviewCount = $urgentSale->prestataire->reviews()->count();
                                    @endphp
                                    @if($reviewCount > 0)
                                        <div class="flex items-center mt-1">
                                            <div class="flex items-center mr-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-3 h-3 {{ $i <= floor($averageRating) ? 'text-yellow-400' : 'text-gray-300' }} fill-current" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endfor
                                            </div>
                                            <span class="text-xs text-gray-600">{{ number_format($averageRating, 1) }} ({{ $reviewCount }})</span>
                                        </div>
                                    @else
                                        <div class="flex items-center mt-1">
                                            <span class="text-xs text-gray-500">Aucun avis</span>
                                        </div>
                                    @endif
                                    
                                    <div class="text-xs text-red-600 mt-1">Voir le profil</div>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Toggle button for more info -->
                        <button onclick="toggleVendeurInfo()" class="w-full text-xs text-red-600 hover:text-red-800 py-1 border-t border-gray-200 mt-2 pt-2 transition-colors duration-200">
                            <span id="vendeurToggleText">Voir plus d'informations</span>
                            <svg id="vendeurToggleIcon" class="w-3 h-3 inline-block ml-1 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Additional information (initially hidden) -->
                        <div id="vendeurExtraInfo" class="hidden mt-2 space-y-2 text-xs">
                            
                            <!-- Location info -->
                            @if($urgentSale->location)
                                <div class="bg-red-50 p-2 rounded-lg">
                                    <div class="flex items-start">
                                        <svg class="w-3 h-3 mr-1 flex-shrink-0 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <div class="font-medium text-red-800 text-xs mb-1">Localisation</div>
                                            <div class="text-gray-700 text-xs">{{ $urgentSale->location }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Contact info -->
                            @if($urgentSale->prestataire->phone || $urgentSale->prestataire->user->email)
                                <div class="bg-gray-50 p-2 rounded-lg">
                                    <div class="font-medium text-gray-800 text-xs mb-1">Contact</div>
                                    @if($urgentSale->prestataire->phone)
                                        <div class="flex items-center mb-1">
                                            <svg class="w-3 h-3 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            <span class="text-gray-700 text-xs">{{ $urgentSale->prestataire->phone }}</span>
                                        </div>
                                    @endif
                                    @if($urgentSale->prestataire->user->email)
                                        <div class="flex items-center">
                                            <svg class="w-3 h-3 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="text-gray-700 truncate text-xs">{{ Str::limit($urgentSale->prestataire->user->email, 25) }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                            
                            <!-- Publication time -->
                            <div class="flex items-center text-gray-600">
                                <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="truncate">Publié {{ $urgentSale->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        @if ($urgentSale->latitude && $urgentSale->longitude)
                            <div class="border-t border-gray-200 pt-2 mt-2">
                                <h3 class="text-sm font-semibold text-red-800 mb-2">Carte</h3>
                                <div id="map" style="height: 120px;" class="rounded-lg z-10"></div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Détails du produit -->
                    <div class="border-t border-gray-200 pt-3 mb-3">
                        <h3 class="text-sm font-semibold text-red-800 mb-2">Détails</h3>
                        
                        <!-- Informations principales (toujours visibles) -->
                        <div class="mb-2">
                            <div class="text-xs text-gray-600 mb-1">Quantité disponible</div>
                            <div class="text-sm font-medium text-gray-800">{{ $urgentSale->quantity }}</div>
                        </div>
                        
                        <!-- Toggle button for more details -->
                        <button onclick="toggleProductDetails()" class="w-full text-xs text-red-600 hover:text-red-800 py-1 border-t border-gray-200 mt-2 pt-2 transition-colors duration-200">
                            <span id="productDetailsToggleText">Voir plus de détails</span>
                            <svg id="productDetailsToggleIcon" class="w-3 h-3 inline-block ml-1 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Additional details (initially hidden) -->
                        <div id="productExtraDetails" class="hidden mt-2 space-y-2">
                            <!-- Informations principales -->
                            <div class="bg-gray-50 rounded-lg p-2 mb-2">
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <div class="text-gray-600 mb-0.5">État</div>
                                        <div class="font-medium text-gray-800">{{ $urgentSale->condition_label }}</div>
                                    </div>
                                    <div>
                                        <div class="text-gray-600 mb-0.5">Référence</div>
                                        <div class="font-medium text-gray-800">#{{ $urgentSale->id }}</div>
                                    </div>
                                    @if($urgentSale->quantity > 1)
                                        <div class="col-span-2">
                                            <div class="text-gray-600 mb-0.5">Quantité</div>
                                            <div class="font-medium text-gray-800">{{ $urgentSale->quantity }} unités disponibles</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Date de publication -->
                            <div class="bg-red-50 p-2 rounded-lg">
                                <div class="flex items-center text-gray-700 text-xs">
                                    <svg class="w-3 h-3 mr-1 flex-shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="font-medium text-red-800 mr-1">Publié:</span>
                                    <span>{{ $urgentSale->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="mt-2">
                        @auth
                            @if(auth()->user()->id !== $urgentSale->prestataire->user_id)
                                <div class="grid grid-cols-1 gap-2 mb-2">
                                    <button onclick="openContactModal('{{ addslashes($urgentSale->title) }}', '{{ $urgentSale->id }}', '{{ number_format($urgentSale->price, 2) }}')" class="w-full bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 transition duration-200 font-bold text-center text-sm shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                         <span class="truncate">Contacter le vendeur</span>
                                     </button>
                                     <button onclick="shareProduct()" class="w-full bg-red-100 text-red-800 px-3 py-2 rounded-lg hover:bg-red-200 transition duration-200 font-bold text-center text-sm">
                                         <span class="truncate">Partager</span>
                                     </button>
                                </div>
                                
                                <!-- Bouton de signalement -->
                                <button onclick="reportProduct()" class="w-full bg-gray-50 text-red-600 border border-red-200 px-3 py-1.5 rounded-lg hover:bg-red-50 hover:border-red-300 transition duration-200 text-center text-xs flex items-center justify-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <span>Signaler</span>
                                </button>
                            @else
                                <div class="bg-gray-100 text-gray-600 px-3 py-2 rounded-lg text-center text-sm">
                                    Votre annonce
                                </div>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="w-full bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 transition duration-200 font-bold text-center text-sm shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                 <span class="truncate">Se connecter</span>
                             </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Ventes similaires -->
        @if($similarSales && $similarSales->count() > 0)
            <div class="mt-4 sm:mt-6">
                <h2 class="text-lg sm:text-xl font-bold text-red-900 mb-3 px-4 sm:px-0">Ventes similaires</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4">
                    @foreach($similarSales as $sale)
                        <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition duration-200 overflow-hidden">
                            <a href="{{ route('urgent-sales.show', $sale) }}" class="block">
                                <div class="relative h-32 bg-gray-200">
                                    @if($sale->photos && count($sale->photos ?? []) > 0)
                                        <img src="{{ Storage::url($sale->first_photo) }}" alt="{{ $sale->title }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-6 h-6 md:w-8 md:h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    

                                </div>
                                
                                <div class="p-2">
                                    <h3 class="font-medium text-gray-900 mb-1 line-clamp-2 text-sm">{{ Str::limit($sale->title, 40) }}</h3>
                                    <div class="text-sm font-bold text-red-600 mb-1">{{ number_format($sale->price, 2) }}€</div>
                                    <div class="text-xs text-gray-500 truncate">{{ $sale->location ?? 'Non spécifié' }}</div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal d'affichage d'image plein écran -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden flex items-center justify-center p-4">
    <div class="relative max-w-7xl max-h-full">
        <!-- Image principale -->
        <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain">
        
        <!-- Bouton fermer -->
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        
        <!-- Flèche gauche -->
        <button id="prevButtonModal" onclick="navigateImageModal(-1)" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 transition-colors bg-black bg-opacity-50 rounded-full p-3 hover:bg-opacity-70">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        
        <!-- Flèche droite -->
        <button id="nextButtonModal" onclick="navigateImageModal(1)" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 transition-colors bg-black bg-opacity-50 rounded-full p-3 hover:bg-opacity-70">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
        
        <!-- Indicateur d'image -->
        @if($urgentSale->photos && count($urgentSale->photos) > 0)
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-white bg-black bg-opacity-50 px-3 py-1 rounded-full text-sm">
                <span id="imageCounterModal">1 / {{ count($urgentSale->photos) }}</span>
            </div>
        @endif
    </div>
</div>

<!-- Modal de contact -->
@auth
    @if(auth()->user()->id !== $urgentSale->prestataire->user_id)
        <div id="contactModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                    <div class="p-4 md:p-6">
                        <form action="{{ route('urgent-sales.contact', $urgentSale) }}" method="POST">
                            @csrf
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base md:text-lg font-semibold text-gray-900">Contacter le vendeur</h3>
                                <button type="button" onclick="closeContactModal()" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            @if ($errors->any())
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <div class="mb-4">
                                <label for="message" class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Votre message</label>
                                <textarea id="message" name="message" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 text-sm md:text-base" placeholder="Votre message..." required></textarea>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                                <button type="button" onclick="closeContactModal()" class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition duration-200 text-sm md:text-base">
                                    Annuler
                                </button>
                                <button type="submit" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition duration-200 text-sm md:text-base">
                                    Envoyer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endauth

@push('scripts')
    @if ($urgentSale->latitude && $urgentSale->longitude)
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var map = L.map('map').setView([{{ $urgentSale->latitude }}, {{ $urgentSale->longitude }}], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                L.marker([{{ $urgentSale->latitude }}, {{ $urgentSale->longitude }}]).addTo(map)
                    .bindPopup('Localisation approximative de l\'article.')
                    .openPopup();
            });
        </script>
    @endif
@endpush

<!-- Modal de signalement -->
<div id="reportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-4 md:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base md:text-lg font-semibold text-gray-900">Signaler cette annonce</h3>
                    <button onclick="closeReportModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <form action="{{ route('urgent-sales.report', $urgentSale) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="reason" class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Raison du signalement</label>
                        <select id="reason" name="reason" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 text-sm md:text-base">
                            <option value="">Sélectionnez une raison</option>
                            <option value="inappropriate">Contenu inapproprié</option>
                            <option value="fake">Annonce frauduleuse</option>
                            <option value="spam">Spam</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="details" class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Détails (optionnel)</label>
                        <textarea id="details" name="details" rows="3"
                                  placeholder="Décrivez le problème..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm md:text-base"></textarea>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <button type="button" onclick="closeReportModal()" class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition duration-200 text-sm md:text-base">
                            Annuler
                        </button>
                        <button type="submit" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition duration-200 text-sm md:text-base">
                            Signaler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Images de l'annonce pour le modal
const saleImages = [
    @if($urgentSale->photos && count($urgentSale->photos) > 0)
        @foreach($urgentSale->photos as $index => $photo)
            {
                url: '{{ Storage::url($photo) }}',
                alt: '{{ $urgentSale->title }} - Photo {{ $index + 1 }}'
            }{{ !$loop->last ? ',' : '' }}
        @endforeach
    @endif
];

let currentImageIndex = 0;

// Fonction pour naviguer entre les images avec navigation circulaire
function navigateImage(direction) {
    if (saleImages.length <= 1) return;
    
    // Calculer le nouvel index avec navigation circulaire
    currentImageIndex += direction;
    
    if (currentImageIndex < 0) {
        currentImageIndex = saleImages.length - 1; // Revenir à la dernière image
    } else if (currentImageIndex >= saleImages.length) {
        currentImageIndex = 0; // Revenir à la première image
    }
    
    // Mettre à jour l'image principale
    const mainImage = document.getElementById('mainImage');
    const imageCounter = document.getElementById('imageCounter');
    
    if (mainImage) {
        mainImage.src = saleImages[currentImageIndex].url;
        mainImage.alt = saleImages[currentImageIndex].alt;
    }
    
    if (imageCounter) {
        imageCounter.textContent = `${currentImageIndex + 1} / ${saleImages.length}`;
    }
}

function openImageModal(index) {
    if (saleImages.length === 0) return;
    
    currentImageIndex = index;
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const imageCounter = document.getElementById('imageCounterModal');
    const prevButton = document.getElementById('prevButtonModal');
    const nextButton = document.getElementById('nextButtonModal');
    
    if (modal && modalImage && imageCounter) {
        modalImage.src = saleImages[index].url;
        modalImage.alt = saleImages[index].alt;
        imageCounter.textContent = `${index + 1} / ${saleImages.length}`;
        
        // Afficher/masquer les boutons de navigation
        if (prevButton) prevButton.style.display = saleImages.length > 1 ? 'block' : 'none';
        if (nextButton) nextButton.style.display = saleImages.length > 1 ? 'block' : 'none';
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

// Fonction pour naviguer dans le modal avec navigation circulaire
function navigateImageModal(direction) {
    if (saleImages.length <= 1) return;
    
    // Calculer le nouvel index avec navigation circulaire
    currentImageIndex += direction;
    
    if (currentImageIndex < 0) {
        currentImageIndex = saleImages.length - 1; // Revenir à la dernière image
    } else if (currentImageIndex >= saleImages.length) {
        currentImageIndex = 0; // Revenir à la première image
    }
    
    const modalImage = document.getElementById('modalImage');
    const imageCounter = document.getElementById('imageCounterModal');
    
    if (modalImage && imageCounter) {
        modalImage.src = saleImages[currentImageIndex].url;
        modalImage.alt = saleImages[currentImageIndex].alt;
        imageCounter.textContent = `${currentImageIndex + 1} / ${saleImages.length}`;
        
        // Mettre à jour l'image principale aussi
        const mainImage = document.getElementById('mainImage');
        const mainImageCounter = document.getElementById('imageCounter');
        if (mainImage) {
            mainImage.src = saleImages[currentImageIndex].url;
            mainImage.alt = saleImages[currentImageIndex].alt;
        }
        if (mainImageCounter) {
            mainImageCounter.textContent = `${currentImageIndex + 1} / ${saleImages.length}`;
        }
    }
}

// Fonction pour toggler les informations du vendeur
function toggleVendeurInfo() {
    const extraInfo = document.getElementById('vendeurExtraInfo');
    const toggleText = document.getElementById('vendeurToggleText');
    const toggleIcon = document.getElementById('vendeurToggleIcon');
    
    if (extraInfo && toggleText && toggleIcon) {
        if (extraInfo.classList.contains('hidden')) {
            extraInfo.classList.remove('hidden');
            toggleText.textContent = 'Masquer les informations';
            toggleIcon.style.transform = 'rotate(180deg)';
        } else {
            extraInfo.classList.add('hidden');
            toggleText.textContent = 'Voir plus d\'informations';
            toggleIcon.style.transform = 'rotate(0deg)';
        }
    }
}

// Fonction pour toggler les détails du produit
function toggleProductDetails() {
    const extraDetails = document.getElementById('productExtraDetails');
    const toggleText = document.getElementById('productDetailsToggleText');
    const toggleIcon = document.getElementById('productDetailsToggleIcon');
    
    if (extraDetails && toggleText && toggleIcon) {
        if (extraDetails.classList.contains('hidden')) {
            extraDetails.classList.remove('hidden');
            toggleText.textContent = 'Masquer les détails';
            toggleIcon.style.transform = 'rotate(180deg)';
        } else {
            extraDetails.classList.add('hidden');
            toggleText.textContent = 'Voir plus de détails';
            toggleIcon.style.transform = 'rotate(0deg)';
        }
    }
}

// Gestion du clavier pour le modal d'image
document.addEventListener('keydown', function(event) {
    const modal = document.getElementById('imageModal');
    if (modal && !modal.classList.contains('hidden')) {
        if (event.key === 'Escape') {
            closeImageModal();
        } else if (event.key === 'ArrowLeft') {
            navigateImageModal(-1);
        } else if (event.key === 'ArrowRight') {
            navigateImageModal(1);
        }
    }
});

// Fermer le modal en cliquant à l'extérieur
const imageModal = document.getElementById('imageModal');
if (imageModal) {
    imageModal.addEventListener('click', function(event) {
        if (event.target === this) {
            closeImageModal();
        }
    });
}

function openContactModal(title, id, price) {
    const message = `Bonjour, je suis intéressé(e) par votre annonce : ${title} (#Référence : ${id}) au prix de ${price}€.`;
    document.getElementById('message').value = message;
    document.getElementById('contactModal').classList.remove('hidden');
}

function closeContactModal() {
    document.getElementById('contactModal').classList.add('hidden');
}

function reportProduct() {
    document.getElementById('reportModal').classList.remove('hidden');
}

function closeReportModal() {
    document.getElementById('reportModal').classList.add('hidden');
}

function shareProduct() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $urgentSale->title }}',
            text: 'Découvrez cette vente urgente sur TaPrestation',
            url: window.location.href
        });
    } else {
        // Fallback pour les navigateurs qui ne supportent pas l'API Web Share
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Lien copié dans le presse-papiers!');
        });
    }
}

// Fermer les modals en cliquant à l'extérieur
document.addEventListener('click', function(event) {
    const contactModal = document.getElementById('contactModal');
    const reportModal = document.getElementById('reportModal');
    
    if (event.target === contactModal) {
        closeContactModal();
    }
    
    if (event.target === reportModal) {
        closeReportModal();
    }
});
</script>
@endpush

@push('styles')
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush