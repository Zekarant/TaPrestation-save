@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        :root {
            --detail-primary: #2563eb;
            --detail-primary-dark: #1d4ed8;
            --detail-light: #eff6ff;
            --detail-accent: #8b5cf6;
        }

        .detail-page-bg {
            background: linear-gradient(180deg, var(--detail-light) 0%, #f8fafc 55%, #ffffff 100%);
            min-height: 100vh;
        }

        .nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.95);
            color: #374151;
            border: none;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .nav-arrow:hover {
            background: white;
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .nav-arrow.left { left: 12px; }
        .nav-arrow.right { right: 12px; }

        .image-counter {
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            color: white;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
        }

        .card-modern {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(37, 99, 235, 0.10);
            transition: all 0.3s ease;
        }

        .card-modern:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.10);
        }

        .gallery-container {
            border-radius: 16px;
            overflow: hidden;
            background: #f3f4f6;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.10);
        }

        .gallery-main-img {
            width: 100%;
            height: 240px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        @media (min-width: 640px) {
            .gallery-main-img { height: 320px; }
        }

        @media (min-width: 1024px) {
            .gallery-main-img { height: 400px; }
        }

        .price-tag {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--detail-primary) 0%, var(--detail-accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @media (min-width: 640px) {
            .price-tag { font-size: 32px; }
        }

        .btn-action-primary {
            background: linear-gradient(135deg, var(--detail-primary) 0%, var(--detail-primary-dark) 100%);
            color: white;
            padding: 14px 24px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.25);
            border: none;
            cursor: pointer;
            width: 100%;
            text-decoration: none;
        }

        .btn-action-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.35);
        }

        .btn-action-secondary {
            background: var(--detail-light);
            color: var(--detail-primary);
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: 2px solid rgba(37, 99, 235, 0.18);
            cursor: pointer;
            width: 100%;
            text-decoration: none;
        }

        .btn-action-secondary:hover {
            background: #dbeafe;
            border-color: rgba(37, 99, 235, 0.28);
        }

        .btn-report {
            background: #f8fafc;
            color: #64748b;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            width: 100%;
        }

        .btn-report:hover {
            background: #eff6ff;
            color: var(--detail-primary);
            border-color: rgba(37, 99, 235, 0.28);
        }

        .seller-card {
            background: linear-gradient(135deg, #ffffff 0%, var(--detail-light) 100%);
            border-radius: 16px;
            padding: 16px;
            border: 2px solid rgba(37, 99, 235, 0.10);
        }
    </style>
@endpush

@section('title', $service->title . ' - Service - TaPrestation')

@section('content')
<div class="detail-page-bg">
    <div class="max-w-6xl mx-auto px-4 py-6">
        <!-- PWA Back Button + Breadcrumb -->
        <div class="flex items-center gap-3 mb-2 sm:mb-3">
            <x-pwa-back-button class="flex-shrink-0" />
            
            <nav class="flex-1 hidden sm:flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 sm:space-x-2">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center text-xs sm:text-sm font-medium text-gray-700 hover:text-blue-600">
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
                        <a href="{{ route('services.index') }}" class="ml-1 text-xs sm:text-sm font-medium text-gray-700 hover:text-blue-600">Services</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-xs sm:text-sm font-medium text-gray-500">{{ Str::limit($service->title, 30) }}</span>
                    </div>
                </li>
            </ol>
            </nav>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
            <!-- Galerie d'images -->
            <div class="lg:col-span-2 space-y-4">
                <div class="gallery-container">
                    @if($service->images && count($service->images) > 0)
                        <div class="relative">
                            <!-- Image principale avec flèches de navigation -->
                            <div class="relative">
                                <x-media-image :path="$service->images[0]->image_path" :alt="$service->title" id="mainImage" onclick="openImageModal(0)" class="gallery-main-img" />
                                
                                <!-- Flèche gauche -->
                                @if(count($service->images) > 1)
                                    <button id="prevButton" onclick="navigateImage(-1)" class="nav-arrow left">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                    </button>
                                @endif
                                
                                <!-- Flèche droite -->
                                @if(count($service->images) > 1)
                                    <button id="nextButton" onclick="navigateImage(1)" class="nav-arrow right">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                @endif
                                
                                <!-- Indicateur d'image -->
                                @if(count($service->images) > 1)
                                    <div class="image-counter">
                                        <span id="imageCounter">1 / {{ count($service->images) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="h-64 sm:h-80 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-8 h-8 sm:w-12 sm:h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-sm text-gray-500">Aucune photo disponible</p>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Description et informations détaillées -->
                <div id="description-section" class="card-modern p-5 scroll-mt-20">
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 mb-3">Description du service</h2>
                    
                    <!-- Description principale -->
                    <div class="prose max-w-none text-gray-700 text-sm leading-relaxed mb-3">
                        {!! nl2br(e($service->description)) !!}
                    </div>
                    
                    <!-- Informations complémentaires -->
                    @if($service->delivery_time || $service->price_type)
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <h3 class="text-sm font-semibold text-gray-900 mb-3">Informations pratiques</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @if($service->delivery_time)
                                    <div class="bg-blue-50 p-3 rounded-xl">
                                        <div class="flex items-center mb-1">
                                            <svg class="w-3 h-3 text-blue-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="text-xs font-medium text-blue-800">Délai de livraison</span>
                                        </div>
                                        <p class="text-gray-700 text-xs">{{ $service->delivery_time }}</p>
                                    </div>
                                @endif
                                
                                @if($service->price_type)
                                    <div class="bg-green-50 p-3 rounded-xl">
                                        <div class="flex items-center mb-1">
                                            <svg class="w-3 h-3 text-green-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                            </svg>
                                            <span class="text-xs font-medium text-green-800">Tarification</span>
                                        </div>
                                        <p class="text-gray-700 text-xs">
                                            @if($service->price_type === 'heure' || $service->price_type === 'jour')
                                                @if($service->quantity)
                                                    {{ $service->quantity }} {{ $service->price_type === 'heure' ? 'heures' : 'jours' }} à {{ number_format($service->price, 2) }}€ l'unité = {{ number_format($service->price * $service->quantity, 2) }}€
                                                @else
                                                    Prix unitaire: {{ number_format($service->price, 2) }}€ / {{ $service->price_type === 'heure' ? 'heure' : 'jour' }}
                                                @endif
                                            @else
                                                {{ number_format($service->price, 2) }}€ / {{ $service->price_type }}
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <!-- Matériel inclus -->
                    @if($service->equipment->count() > 0)
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <h3 class="text-sm font-semibold text-gray-900 mb-3">Matériel inclus</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($service->equipment as $item)
                                    <div class="bg-orange-50 p-2 rounded-lg">
                                        <div class="flex items-center mb-1">
                                            <svg class="w-3 h-3 text-orange-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span class="text-xs font-medium text-orange-800">{{ $item->name }}</span>
                                        </div>
                                        @if($item->description)
                                            <p class="text-gray-700 text-xs truncate">{{ Str::limit($item->description, 50) }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Disponibilité et réservation -->
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        @if($service->reservable)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-2">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-xs font-medium text-green-800">Réservation directe</span>
                                </div>
                                <p class="text-green-700 text-xs mt-1">Ce service peut être réservé directement en ligne sans validation du prestataire.</p>
                            </div>
                        @else
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-2">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-xs font-medium text-blue-800">Sur demande</span>
                                </div>
                                <p class="text-blue-700 text-xs mt-1">Ce service nécessite une demande de réservation. Le prestataire validera votre demande.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Sidebar d'informations -->
            <div class="lg:col-span-1 space-y-4">
                <div class="card-modern p-5">
                    <!-- Titre et prix -->
                    <div class="mb-4">
                        <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mb-2 leading-tight break-words">{{ $service->title }}</h1>
                        <div class="price-tag">
                            @if($service->price_type === 'heure' || $service->price_type === 'jour')
                                <div class="flex flex-col">
                                    <div class="flex items-baseline">
                                        <span>{{ number_format($service->price, 2) }}€</span>
                                        <span class="text-sm font-normal text-gray-500 ml-1">/ {{ $service->price_type }}</span>
                                    </div>
                                    
                                    @if($service->quantity)
                                        <div class="mt-2 bg-blue-50 border border-blue-200 rounded-lg p-2.5 text-base">
                                            <div class="grid grid-cols-1 gap-2 text-sm">
                                                <div class="flex justify-between">
                                                    <div class="text-gray-600">Prix unitaire</div>
                                                    <div class="font-semibold text-blue-800">{{ number_format($service->price, 2) }}€ / {{ $service->price_type === 'heure' ? 'heure' : 'jour' }}</div>
                                                </div>
                                                <div class="flex justify-between">
                                                    <div class="text-gray-600">Nombre de {{ $service->price_type === 'heure' ? 'heures' : 'jours' }}</div>
                                                    <div class="font-semibold text-blue-800">{{ $service->quantity }}</div>
                                                </div>
                                                <div class="flex justify-between pt-2 border-t border-blue-200">
                                                    <div class="text-gray-600 font-medium">Prix total</div>
                                                    <div class="font-bold text-blue-900 text-lg">{{ number_format($service->price * $service->quantity, 2) }}€</div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-2 bg-blue-50 border border-blue-200 rounded-lg p-2.5 text-base">
                                            <div class="grid grid-cols-2 gap-2 text-sm">
                                                <div>
                                                    <div class="text-gray-600 text-xs mb-1">Prix unitaire</div>
                                                    <div class="font-semibold text-blue-800">{{ number_format($service->price, 2) }}€ / {{ $service->price_type === 'heure' ? 'heure' : 'jour' }}</div>
                                                </div>
                                                <div>
                                                    <div class="text-gray-600 text-xs mb-1">Exemple</div>
                                                    <div class="font-semibold text-blue-800">
                                                        @if($service->price_type === 'heure')
                                                            Pour 3 heures: {{ number_format($service->price * 3, 2) }}€
                                                        @else
                                                            Pour 3 jours: {{ number_format($service->price * 3, 2) }}€
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-2 text-xs text-blue-600">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Le prix total dépendra de la durée sélectionnée lors de la réservation
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                {{ number_format($service->price, 2) }}€ 
                                <span class="text-sm font-normal text-gray-500">/ {{ $service->price_type }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Informations du vendeur -->
                    <div class="seller-card mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-base font-bold text-gray-900">Prestataire</h3>
                            <a href="{{ route('prestataires.show', $service->prestataire) }}" class="text-sm font-medium" style="color: var(--detail-primary);">Voir →</a>
                        </div>
                        <a href="{{ route('prestataires.show', $service->prestataire) }}" class="block">
                            <div class="flex items-center cursor-pointer hover:opacity-90 p-2 rounded-xl transition-opacity duration-200">
                                <div class="relative w-8 h-8 sm:w-10 sm:h-10 mr-2 flex-shrink-0">
                                    @if($service->prestataire->photo)
                                        <x-media-image :path="$service->prestataire->photo" :alt="$service->prestataire->user->name" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover" />
                                    @elseif($service->prestataire->user->avatar)
                                        <x-media-image :path="$service->prestataire->user->avatar" :alt="$service->prestataire->user->name" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover" />
                                    @else
                                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    @if($service->prestataire->isVerified())
                                        <div class="absolute -top-1 -right-1 w-3 h-3 sm:w-4 sm:h-4 bg-green-500 rounded-full flex items-center justify-center border-2 border-white">
                                            <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-sm text-gray-900 hover:text-blue-600 transition-colors duration-200 truncate">{{ $service->prestataire->user->name }}</span>
                                        @if($service->prestataire->isVerified())
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 self-start mt-1">
                                                <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Vérifié
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Évaluations compactes -->
                                    @php
                                        $averageRating = $service->prestataire->reviews()->avg('rating') ?? 0;
                                        $reviewCount = $service->prestataire->reviews()->count();
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
                                    @endif
                                    
                                    <div class="text-xs text-gray-600 mt-1">Voir le profil</div>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Toggle button for more info -->
                        <button onclick="togglePrestataireInfo()" class="w-full text-xs py-1 border-t border-gray-200 mt-2 pt-2 transition-colors duration-200" style="color: var(--detail-primary);">
                            <span id="toggleText">Voir plus d'informations</span>
                            <svg id="toggleIcon" class="w-3 h-3 inline-block ml-1 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Additional information (initially hidden) -->
                        <div id="prestataireExtraInfo" class="hidden mt-2 space-y-2 text-xs">
                            @if($service->prestataire->company_name)
                                <div class="text-gray-600"><strong>Société:</strong> {{ $service->prestataire->company_name }}</div>
                            @endif
                            
                            <!-- Location info -->
                            @if($service->city || $service->address || $service->prestataire->city)
                                <div class="bg-blue-50 p-3 rounded-xl">
                                    <div class="flex items-start">
                                        <svg class="w-3 h-3 mr-1 flex-shrink-0 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <div class="font-medium text-blue-800 text-xs mb-1">Localisation</div>
                                            @if($service->city)
                                                <div class="text-gray-700 text-xs">{{ $service->city }} @if($service->postal_code)({{ $service->postal_code }})@endif</div>
                                            @elseif($service->prestataire->city)
                                                <div class="text-gray-600 text-xs">{{ $service->prestataire->city }} @if($service->prestataire->postal_code)({{ $service->prestataire->postal_code }})@endif</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Contact info -->
                            @if($service->prestataire->phone || $service->prestataire->user->email)
                                <div class="bg-gray-50 p-2 rounded-lg">
                                    <div class="font-medium text-gray-800 text-xs mb-1">Contact</div>
                                    @if($service->prestataire->phone)
                                        <div class="flex items-center mb-1">
                                            <svg class="w-3 h-3 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            <span class="text-gray-700 text-xs">{{ $service->prestataire->phone }}</span>
                                        </div>
                                    @endif
                                    @if($service->prestataire->user->email)
                                        <div class="flex items-center">
                                            <svg class="w-3 h-3 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="text-gray-700 truncate text-xs">{{ Str::limit($service->prestataire->user->email, 25) }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                            
                            <!-- Service publication time -->
                            <div class="flex items-center text-gray-600">
                                <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="truncate">Publié {{ $service->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        @if ($service->latitude && $service->longitude)
                            <div class="border-t border-gray-200 pt-3 mt-3">
                                <h3 class="text-sm font-semibold text-gray-900 mb-2">Carte</h3>
                                <div id="map" style="height: 120px;" class="rounded-lg z-10"></div>
                            </div>
                        @endif
                    </div>

                    <!-- Détails du service -->
                    <div class="border-t border-gray-200 pt-4 mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Détails</h3>
                        
                        <!-- Catégories (toujours visibles) -->
                        @if($service->categories->count() > 0)
                            <div class="mb-2">
                                <div class="text-xs text-gray-600 mb-1">Catégories</div>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($service->categories->take(3) as $category)
                                        <a href="{{ route('services.index', ['category' => $category->id]) }}" class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full hover:bg-blue-200 transition-colors duration-200">
                                            {{ Str::limit($category->name, 15) }}
                                        </a>
                                    @endforeach
                                    @if($service->categories->count() > 3)
                                        <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">
                                            +{{ $service->categories->count() - 3 }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        <!-- Toggle button for more details -->
                        <button onclick="toggleServiceDetails()" class="w-full text-xs py-1 border-t border-gray-200 mt-2 pt-2 transition-colors duration-200" style="color: var(--detail-primary);">
                            <span id="detailsToggleText">Voir plus de détails</span>
                            <svg id="detailsToggleIcon" class="w-3 h-3 inline-block ml-1 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Additional details (initially hidden) -->
                        <div id="serviceExtraDetails" class="hidden mt-2 space-y-2">
                            <!-- Informations principales -->
                            <div class="bg-gray-50 rounded-lg p-2 mb-2">
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <div class="text-gray-600 mb-0.5">Référence</div>
                                        <div class="font-medium text-gray-800">#{{ $service->id }}</div>
                                    </div>
                                    <div>
                                        <div class="text-gray-600 mb-0.5">Vues</div>
                                        <div class="font-medium text-gray-800">{{ number_format($service->views) }}</div>
                                    </div>
                                    @if($service->delivery_time)
                                        <div class="col-span-2">
                                            <div class="text-gray-600 mb-0.5">Délai</div>
                                            <div class="font-medium text-gray-800 text-xs">{{ Str::limit($service->delivery_time, 20) }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Statuts -->
                            <div class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600">Statut</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $service->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <div class="w-1 h-1 rounded-full {{ $service->status === 'active' ? 'bg-green-500' : 'bg-red-500' }} mr-1"></div>
                                        {{ $service->status === 'active' ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600">Réservation</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $service->reservable ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        <svg class="w-2 h-2 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($service->reservable)
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            @endif
                                        </svg>
                                        {{ $service->reservable ? 'Directe' : 'Sur demande' }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Service publication time -->
                            <div class="bg-blue-50 p-2 rounded-lg">
                                <div class="flex items-center text-gray-700 text-xs">
                                    <svg class="w-3 h-3 mr-1 flex-shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="font-medium text-blue-800 mr-1">Publié:</span>
                                    <span>{{ $service->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="card-modern p-5 space-y-3">
                        @auth
                            @if(in_array(auth()->user()->role, ['client', 'prestataire']) && auth()->user()->id !== $service->prestataire->user_id)
                                <div class="grid grid-cols-1 gap-3">
                                    <!-- Bouton Voir Détails -->
                                    <a href="#description-section" onclick="scrollToDetails()" class="btn-action-secondary">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>Voir les détails</span>
                                    </a>
                                    @if(function_exists('feature_enabled') && feature_enabled('booking_payment_enabled') && ($service->status ?? 'active') === 'active')
                                        <a href="{{ route('bookings.create', $service) }}" class="btn-action-primary">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="truncate">{{ ($service->reservable ?? true) ? 'Réserver' : 'Demander une réservation' }}</span>
                                        </a>
                                    @endif
                                    <a href="{{ route('client.messaging.start', $service->prestataire) }}" class="btn-action-secondary">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                        <span class="truncate">Contacter</span>
                                    </a>
                                </div>
                                
                                <!-- Bouton de signalement -->
                                <button onclick="openReportModal()" class="btn-report">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <span>Signaler</span>
                                </button>
                            @elseif(auth()->user()->id === $service->prestataire->user_id)
                                <a href="{{ route('prestataire.services.edit', $service) }}" class="btn-action-primary">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    <span class="truncate">Modifier</span>
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn-action-primary">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                <span class="truncate">Se connecter</span>
                            </a>
                        @endauth
                    </div>
            </div>
        </div>

    <!-- Modal d'affichage d'image plein écran -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden items-center justify-center p-4">
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
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-white bg-black bg-opacity-50 px-3 py-1 rounded-full text-sm">
                <span id="imageCounterModal">1 / {{ $service->images->count() }}</span>
            </div>
        </div>
    </div>

    <!-- Modal de signalement -->
    @auth
        @if(in_array(auth()->user()->role, ['client', 'prestataire']) && auth()->user()->id !== $service->prestataire->user_id)
            <div id="reportModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-200" style="backdrop-filter: blur(3px); background-color: rgba(0, 0, 0, 0.45);">
                <div class="min-h-full w-full flex items-end sm:items-center justify-center p-3">
                    <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl p-4 sm:p-6 w-full sm:max-w-md border border-red-200 transform transition-all duration-200 scale-95 opacity-0 modal-show max-h-[90vh] overflow-y-auto">
                        <div class="p-0">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Signaler ce service</h3>
                            <button onclick="closeReportModal()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <form id="reportForm" action="{{ route('services.report', $service) }}" method="POST">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Catégorie du signalement *</label>
                                <select name="category" id="category" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                    <option value="">Sélectionnez une catégorie</option>
                                    <option value="inappropriate_content">Contenu inapproprié</option>
                                    <option value="false_information">Informations fausses</option>
                                    <option value="spam">Spam ou publicité</option>
                                    <option value="fraud">Fraude ou arnaque</option>
                                    <option value="copyright">Violation de droits d'auteur</option>
                                    <option value="other">Autre</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priorité *</label>
                                <select name="priority" id="priority" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                    <option value="">Sélectionnez une priorité</option>
                                    <option value="low">Faible</option>
                                    <option value="medium">Moyenne</option>
                                    <option value="high">Élevée</option>
                                    <option value="urgent">Urgente</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Raison du signalement *</label>
                                <textarea name="reason" id="reason" rows="3" required placeholder="Décrivez brièvement le problème..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none"></textarea>
                            </div>
                            
                            <div class="mb-6">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description détaillée</label>
                                <textarea name="description" id="description" rows="4" placeholder="Fournissez plus de détails si nécessaire..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none"></textarea>
                            </div>
                            
                            <div class="flex gap-3">
                                <button type="button" onclick="closeReportModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                                    Annuler
                                </button>
                                <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                                    Envoyer le signalement
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            </div>
        @endif
    @endauth

        <!-- Services similaires -->
        <div class="mt-4 sm:mt-6">
            <h2 class="text-lg sm:text-xl font-bold text-blue-900 mb-3 px-4 sm:px-0">Services similaires</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4">
                @forelse($similarServices as $similarService)
                    @include('components.service-card', ['service' => $similarService])
                @empty
                    <div class="col-span-full text-center text-gray-500 px-4 sm:px-0 py-4">
                        Aucun service similaire trouvé.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @if ($service->latitude && $service->longitude)
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            var map = L.map('map').setView([{{ $service->latitude }}, {{ $service->longitude }}], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            L.marker([{{ $service->latitude }}, {{ $service->longitude }}]).addTo(map)
                .bindPopup({!! Js::from($service->title) !!})
                .openPopup();
        </script>
    @endif
    <script>
        // Fonction pour scroller vers les détails
        function scrollToDetails() {
            const descriptionSection = document.getElementById('description-section');
            if (descriptionSection) {
                descriptionSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                // Ajouter un effet de highlight temporaire
                descriptionSection.classList.add('ring-2', 'ring-blue-400', 'ring-offset-2');
                setTimeout(() => {
                    descriptionSection.classList.remove('ring-2', 'ring-blue-400', 'ring-offset-2');
                }, 2000);
            }
        }
        
        // Images du service pour le modal
        const serviceImages = [
            @foreach($service->images as $image)
                {
                    url: "{{ \Illuminate\Support\Facades\Storage::disk('public')->exists($image->image_path) ? \Illuminate\Support\Facades\Storage::url($image->image_path) : asset('images/placeholder.svg') }}",
                    alt: {!! Js::from($service->title . ' - Photo ' . ($loop->index + 1)) !!}
                }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ];
        
        let currentImageIndex = 0;
        
        // Fonction pour naviguer entre les images avec navigation circulaire
        function navigateImage(direction) {
            if (serviceImages.length <= 1) return;
            
            // Calculer le nouvel index avec navigation circulaire
            currentImageIndex += direction;
            
            if (currentImageIndex < 0) {
                currentImageIndex = serviceImages.length - 1; // Revenir à la dernière image
            } else if (currentImageIndex >= serviceImages.length) {
                currentImageIndex = 0; // Revenir à la première image
            }
            
            // Mettre à jour l'image principale
            const mainImage = document.getElementById('mainImage');
            const imageCounter = document.getElementById('imageCounter');
            
            if (mainImage) {
                mainImage.src = serviceImages[currentImageIndex].url;
                mainImage.alt = serviceImages[currentImageIndex].alt;
            }
            
            if (imageCounter) {
                imageCounter.textContent = `${currentImageIndex + 1} / ${serviceImages.length}`;
            }
        }
        
        function openImageModal(index) {
            currentImageIndex = index;
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const imageCounter = document.getElementById('imageCounter');
            const prevButton = document.getElementById('prevButton');
            const nextButton = document.getElementById('nextButton');
            
            modalImage.src = serviceImages[index].url;
            modalImage.alt = serviceImages[index].alt;
            imageCounter.textContent = `${index + 1} / ${serviceImages.length}`;
            
            // Afficher/masquer les boutons de navigation
            prevButton.style.display = serviceImages.length > 1 ? 'block' : 'none';
            nextButton.style.display = serviceImages.length > 1 ? 'block' : 'none';
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
        
        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
        
        // Fonction pour naviguer dans le modal avec navigation circulaire
        function navigateImageModal(direction) {
            if (serviceImages.length <= 1) return;
            
            // Calculer le nouvel index avec navigation circulaire
            currentImageIndex += direction;
            
            if (currentImageIndex < 0) {
                currentImageIndex = serviceImages.length - 1; // Revenir à la dernière image
            } else if (currentImageIndex >= serviceImages.length) {
                currentImageIndex = 0; // Revenir à la première image
            }
            
            const modalImage = document.getElementById('modalImage');
            const imageCounter = document.getElementById('imageCounter');
            
            modalImage.src = serviceImages[currentImageIndex].url;
            modalImage.alt = serviceImages[currentImageIndex].alt;
            imageCounter.textContent = `${currentImageIndex + 1} / ${serviceImages.length}`;
        }
        
        // Gestion du clavier
        document.addEventListener('keydown', function(event) {
            const modal = document.getElementById('imageModal');
            if (!modal.classList.contains('hidden')) {
                if (event.key === 'Escape') {
                    closeImageModal();
                } else if (event.key === 'ArrowLeft') {
                    navigateImageModal(-1);
                } else if (event.key === 'ArrowRight') {
                    navigateImageModal(1);
                }
            }
        });
        
        // Support du swipe tactile pour la modal d'images
        let touchStartX = 0;
        let touchEndX = 0;
        const swipeThreshold = 50; // Minimum distance pour considérer comme un swipe
        
        const imageModalElement = document.getElementById('imageModal');
        if (imageModalElement) {
            imageModalElement.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });
            
            imageModalElement.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            }, { passive: true });
        }
        
        function handleSwipe() {
            const swipeDistance = touchEndX - touchStartX;
            if (Math.abs(swipeDistance) > swipeThreshold) {
                if (swipeDistance > 0) {
                    // Swipe vers la droite -> image précédente
                    navigateImageModal(-1);
                } else {
                    // Swipe vers la gauche -> image suivante
                    navigateImageModal(1);
                }
            }
        }
        
        // Fermer le modal en cliquant à l'extérieur
        const imageModal = document.getElementById('imageModal');
        if (imageModal) {
            imageModal.addEventListener('click', function(event) {
                if (event.target === this) {
                    closeImageModal();
                }
            });
        }

        // Fonctions pour le modal de signalement
        function openReportModal() {
            const modal = document.getElementById('reportModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Add animation classes
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                const modalContent = modal.querySelector('.modal-show');
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
                modalContent.classList.remove('opacity-0');
            }, 10);
        }

        function closeReportModal() {
            const modal = document.getElementById('reportModal');
            const modalContent = modal.querySelector('.modal-show');
            
            if (modalContent) {
                modalContent.classList.remove('scale-100');
                modalContent.classList.add('scale-95');
                modalContent.classList.add('opacity-0');
            }
            
            modal.classList.add('opacity-0');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
                // Réinitialiser le formulaire
                document.getElementById('reportForm').reset();
            }, 300);
        }
        
        // Fonction pour toggler les informations du prestataire
        function togglePrestataireInfo() {
            const extraInfo = document.getElementById('prestataireExtraInfo');
            const toggleText = document.getElementById('toggleText');
            const toggleIcon = document.getElementById('toggleIcon');
            
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
        
        // Fonction pour toggler les détails du service
        function toggleServiceDetails() {
            const extraDetails = document.getElementById('serviceExtraDetails');
            const toggleText = document.getElementById('detailsToggleText');
            const toggleIcon = document.getElementById('detailsToggleIcon');
            
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

        // Fermer le modal en cliquant à l'extérieur
        const reportModal = document.getElementById('reportModal');
        if (reportModal) {
            reportModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeReportModal();
                }
            });
        }

        // Gérer la soumission du formulaire
        const reportForm = document.getElementById('reportForm');
        if (reportForm) {
            reportForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;
                
                submitButton.disabled = true;
                submitButton.textContent = 'Envoi en cours...';
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Votre signalement a été envoyé avec succès. Nous examinerons votre demande dans les plus brefs délais.');
                        closeReportModal();
                    } else {
                        alert('Une erreur est survenue. Veuillez réessayer.');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue. Veuillez réessayer.');
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                });
            });
        }
    </script>
@endpush
