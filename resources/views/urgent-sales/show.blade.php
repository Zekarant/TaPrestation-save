@extends('layouts.app')

@php
    // Helper pour générer les URLs des fichiers storage
    $storageUrl = function($path) {
        return storage_asset_url($path, 'images/placeholder.svg');
    };
@endphp

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        /* Variables */
        :root {
            --urgent-primary: #dc2626;
            --urgent-primary-dark: #b91c1c;
            --urgent-light: #fef2f2;
            --urgent-accent: #f97316;
        }
        
        /* Page background */
        .urgent-page-bg {
            background: linear-gradient(180deg, #fef2f2 0%, #fff5f5 50%, #ffffff 100%);
            min-height: 100vh;
        }
        
        /* Navigation arrows */
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
        
        .nav-arrow.left {
            left: 12px;
        }
        
        .nav-arrow.right {
            right: 12px;
        }
        
        /* Image counter */
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
        
        /* Hide scrollbar for thumbnails */
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        
        /* Modal image animations */
        #modalImage {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .cursor-zoom-in { cursor: zoom-in; }
        .cursor-zoom-out { cursor: zoom-out; }
        
        /* Badge urgent animé */
        .urgent-badge {
            background: linear-gradient(135deg, var(--urgent-primary) 0%, var(--urgent-accent) 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
            animation: pulse-badge 2s infinite;
        }
        
        @keyframes pulse-badge {
            0%, 100% { box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35); }
            50% { box-shadow: 0 4px 20px rgba(220, 38, 38, 0.5); }
        }
        
        /* Prix style */
        .price-tag {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--urgent-primary) 0%, var(--urgent-accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        @media (min-width: 640px) {
            .price-tag { font-size: 32px; }
        }
        
        /* Cards améliorées */
        .card-modern {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(220, 38, 38, 0.08);
            transition: all 0.3s ease;
        }
        
        .card-modern:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }
        
        /* Image gallery */
        .gallery-container {
            border-radius: 16px;
            overflow: hidden;
            background: #f3f4f6;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .gallery-main-img {
            width: 100%;
            height: 240px;
            object-fit: contain;
            background: #f3f4f6;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        @media (min-width: 640px) {
            .gallery-main-img { height: 320px; }
        }
        
        @media (min-width: 1024px) {
            .gallery-main-img { height: 400px; }
        }
        
        /* Boutons action */
        .btn-action-primary {
            background: linear-gradient(135deg, var(--urgent-primary) 0%, var(--urgent-primary-dark) 100%);
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
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.3);
            border: none;
            cursor: pointer;
            width: 100%;
        }
        
        .btn-action-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.4);
        }
        
        .btn-action-secondary {
            background: #fef2f2;
            color: var(--urgent-primary);
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: 2px solid rgba(220, 38, 38, 0.2);
            cursor: pointer;
            width: 100%;
        }
        
        .btn-action-secondary:hover {
            background: #fee2e2;
            border-color: rgba(220, 38, 38, 0.3);
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
            background: #fef2f2;
            color: var(--urgent-primary);
            border-color: rgba(220, 38, 38, 0.3);
        }
        
        /* Vendeur card */
        .seller-card {
            background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
            border-radius: 16px;
            padding: 16px;
            border: 2px solid rgba(220, 38, 38, 0.1);
        }
        
        .seller-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .seller-avatar-placeholder {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--urgent-primary) 0%, var(--urgent-accent) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            font-weight: 700;
            border: 3px solid white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        /* Section titles */
        .section-title {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--urgent-primary);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Detail items */
        .detail-item {
            background: #f8fafc;
            padding: 12px;
            border-radius: 12px;
            text-align: center;
        }
        
        .detail-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #64748b;
            margin-bottom: 4px;
        }
        
        .detail-value {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
        }
        
        /* Similar items */
        .similar-card {
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }
        
        .similar-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        
        .similar-card img {
            height: 140px;
            width: 100%;
            object-fit: cover;
        }
    </style>
@endpush

@section('title', $urgentSale->title . ' - Vente urgente - TaPrestation')

@section('content')
<div class="urgent-page-bg">
    <div class="max-w-6xl mx-auto px-4 py-6">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 mb-6 text-sm">
            <a href="{{ route('home') }}" class="text-gray-500 hover:text-red-600 transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                </svg>
            </a>
            <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <a href="{{ route('urgent-sales.index') }}" class="text-gray-500 hover:text-red-600 transition-colors">Annonces</a>
            <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-gray-700 font-medium">{{ Str::limit($urgentSale->title, 30) }}</span>
        </nav>

        <!-- Messages de succès/erreur -->
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r-xl mb-6 flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-xl mb-6 flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            <!-- Galerie d'images -->
            <div class="lg:col-span-3 space-y-4">
                
                <!-- Galerie -->
                <div class="gallery-container">
                    @if($urgentSale->photos && count($urgentSale->photos ?? []) > 0)
                        <div class="relative">
                            <!-- Titre sur l'image -->
                            <div class="absolute top-4 left-4 right-4 z-10">
                                <h1 class="text-white text-lg sm:text-xl font-bold drop-shadow-lg bg-black/40 backdrop-blur-sm px-4 py-2 rounded-xl inline-block max-w-full truncate">
                                    {{ $urgentSale->title }}
                                </h1>
                            </div>
                            
                            <!-- Badge État -->
                            <div class="absolute top-4 right-4 z-10">
                                <span class="bg-black/60 backdrop-blur-sm text-white px-3 py-1.5 rounded-full text-xs font-medium">
                                    {{ $urgentSale->condition_label }}
                                </span>
                            </div>
                            
                            <!-- Image principale -->
                            @php $firstPhoto = $urgentSale->photos[0]; @endphp
                            @if(filter_var($firstPhoto, FILTER_VALIDATE_URL))
                                <img id="mainImage" src="{{ $firstPhoto }}" alt="{{ $urgentSale->title }}" class="gallery-main-img" onclick="openImageModal(0)" onerror="this.onerror=null; this.src='/images/placeholder.svg';">
                            @else
                                <img id="mainImage" src="{{ $storageUrl($firstPhoto) }}" alt="{{ $urgentSale->title }}" class="gallery-main-img" onclick="openImageModal(0)" onerror="this.onerror=null; this.src='/images/placeholder.svg';">
                            @endif
                            
                            <!-- Flèches de navigation -->
                            @if(count($urgentSale->photos ?? []) > 1)
                                <button id="prevButton" onclick="navigateImage(-1)" class="nav-arrow left">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </button>
                                <button id="nextButton" onclick="navigateImage(1)" class="nav-arrow right">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                                
                                <!-- Compteur -->
                                <div class="image-counter">
                                    <span id="imageCounter">1 / {{ count($urgentSale->photos ?? []) }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="h-64 sm:h-80 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-gray-500">Aucune photo disponible</p>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Prix (Mobile) - Le titre est sur l'image -->
                <div class="lg:hidden card-modern p-5">
                    <div class="price-tag text-center">{{ number_format($urgentSale->price, 2) }}€</div>
                </div>
                
                <!-- Description -->
                <div class="card-modern p-5">
                    <div class="section-title">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                        </svg>
                        Description
                    </div>
                    
                    <div class="prose max-w-none text-gray-700 text-sm leading-relaxed">
                        {!! nl2br(e($urgentSale->description)) !!}
                    </div>
                    
                    @if($urgentSale->reason)
                        <div class="mt-5 p-4 bg-red-50 border-l-4 border-red-400 rounded-r-xl">
                            <div class="flex items-center gap-2 text-red-700 font-semibold mb-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                Raison de la vente urgente
                            </div>
                            <p class="text-red-600 text-sm">{{ $urgentSale->reason }}</p>
                        </div>
                    @endif
                </div>
                
                <!-- Détails du produit -->
                <div class="card-modern p-5">
                    <div class="section-title">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Détails du produit
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @php
                            $topAvailableQty = ($urgentSale->quantity ?? 1) - ($urgentSale->reserved_quantity ?? 0) - ($urgentSale->sold_quantity ?? 0);
                        @endphp
                        <div class="detail-item">
                            <div class="detail-label">Disponible</div>
                            <div class="detail-value">{{ $topAvailableQty }} / {{ $urgentSale->quantity }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">État</div>
                            <div class="detail-value text-sm">{{ $urgentSale->condition_label }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Référence</div>
                            <div class="detail-value">#{{ $urgentSale->id }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Publié</div>
                            <div class="detail-value text-sm">{{ $urgentSale->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Carte -->
                @if($urgentSale->latitude && $urgentSale->longitude)
                <div class="card-modern p-5">
                    <div class="section-title">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Localisation
                    </div>
                    <div id="map" style="height: 200px;" class="rounded-xl z-10"></div>
                    @if($urgentSale->location)
                        <p class="mt-3 text-gray-600 text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            {{ $urgentSale->location }}
                        </p>
                    @endif
                </div>
                @endif
            </div>
            
            <!-- Sidebar -->
            <div class="lg:col-span-2 space-y-4">
                
                <!-- Vendeur -->
                <div class="seller-card">
                    <div class="section-title" style="color: #b91c1c;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Vendeur
                    </div>
                    
                    @if($urgentSale->prestataire)
                    {{-- Vendeur Prestataire --}}
                    <a href="{{ route('prestataires.show', $urgentSale->prestataire) }}" class="flex items-center gap-4 mb-4 hover:opacity-80 transition-opacity">
                        @if($urgentSale->prestataire->photo)
                            <img src="{{ $storageUrl($urgentSale->prestataire->photo) }}" alt="{{ $urgentSale->prestataire->user->name }}" class="seller-avatar">
                        @elseif($urgentSale->prestataire->user->avatar)
                            <img src="{{ $storageUrl($urgentSale->prestataire->user->avatar) }}" alt="{{ $urgentSale->prestataire->user->name }}" class="seller-avatar">
                        @else
                            <div class="seller-avatar-placeholder">
                                {{ strtoupper(substr($urgentSale->prestataire->user->name, 0, 1)) }}
                            </div>
                        @endif
                        
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900">{{ $urgentSale->prestataire->user->name }}</div>
                            @if($urgentSale->prestataire->company_name)
                                <div class="text-gray-500 text-sm">{{ $urgentSale->prestataire->company_name }}</div>
                            @endif
                            
                            @php
                                $averageRating = $urgentSale->prestataire->reviews()->avg('rating') ?? 0;
                                $reviewCount = $urgentSale->prestataire->reviews()->count();
                            @endphp
                            @if($reviewCount > 0)
                                <div class="flex items-center gap-1 mt-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-3.5 h-3.5 {{ $i <= floor($averageRating) ? 'text-yellow-400' : 'text-gray-300' }} fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                    <span class="text-gray-600 text-sm ml-1">{{ number_format($averageRating, 1) }} ({{ $reviewCount }})</span>
                                </div>
                            @else
                                <div class="text-gray-400 text-sm mt-1">Nouveau vendeur</div>
                            @endif
                        </div>
                        
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    @elseif($urgentSale->user)
                    {{-- Vendeur Client (particulier) --}}
                    <div class="flex items-center gap-4 mb-4">
                        @if($urgentSale->user->avatar)
                            <img src="{{ $storageUrl($urgentSale->user->avatar) }}" alt="{{ $urgentSale->user->name }}" class="seller-avatar">
                        @elseif($urgentSale->user->profile_photo_path)
                            <img src="{{ $storageUrl($urgentSale->user->profile_photo_path) }}" alt="{{ $urgentSale->user->name }}" class="seller-avatar">
                        @else
                            <div class="seller-avatar-placeholder">
                                {{ strtoupper(substr($urgentSale->user->name, 0, 1)) }}
                            </div>
                        @endif
                        
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900">{{ $urgentSale->user->name }}</div>
                            <div class="text-gray-400 text-sm mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-user mr-1"></i> Particulier
                                </span>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($urgentSale->location)
                        <div class="flex items-center gap-2 text-sm text-gray-600 mb-3 pb-3 border-b border-gray-200">
                            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            {{ $urgentSale->location }}
                        </div>
                    @endif
                    
                    @if($urgentSale->prestataire)
                    <a href="{{ route('prestataires.show', $urgentSale->prestataire) }}" class="block text-center text-red-600 hover:text-red-700 text-sm font-medium">
                        Voir le profil complet →
                    </a>
                    @endif
                </div>
                
                <!-- Actions -->
                <div class="card-modern p-5 space-y-3">
                    @auth
                        @php
                            $sellerId = $urgentSale->prestataire?->user_id ?? $urgentSale->user_id;
                        @endphp
                        @if(auth()->user()->id !== $sellerId)
                            <button onclick="openContactModal({{ Js::from($urgentSale->title) }}, '{{ $urgentSale->id }}', '{{ number_format($urgentSale->price, 2) }}')" class="btn-action-primary">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                Contacter le vendeur
                            </button>

                            {{-- Système d'achat / réservation --}}
                            @php
                                $availableQty = ($urgentSale->quantity ?? 1) - ($urgentSale->reserved_quantity ?? 0) - ($urgentSale->sold_quantity ?? 0);
                                $urgentSalePaymentRequirement = function_exists('normalize_payment_requirement_for_mode')
                                    ? normalize_payment_requirement_for_mode($urgentSale->payment_requirement ?? 'none')
                                    : ($urgentSale->payment_requirement ?? 'none');
                                $hasOnlinePayment = $urgentSalePaymentRequirement === 'full';
                                $hasStripeConnect = !empty($urgentSale->prestataire?->stripe_account_id);
                                $hasAnyPaymentMethod = $hasStripeConnect;
                            @endphp
                            
                            @if(($urgentSale->status ?? 'active') === 'active' && $availableQty > 0)
                                @if($hasOnlinePayment && $hasAnyPaymentMethod)
                                    {{-- Paiement en ligne activé et moyen de paiement configuré --}}
                                    @if(function_exists('feature_enabled') && feature_enabled('cart_enabled') && \Illuminate\Support\Facades\Route::has('client.cart.add.urgent-sale'))
                                        <form method="POST" action="{{ route('client.cart.add.urgent-sale', $urgentSale) }}" class="w-full">
                                            @csrf
                                            <div class="flex items-center gap-2">
                                                <input name="quantity" type="number" min="1" max="{{ (int) $availableQty }}" value="1" class="w-20 rounded-lg border-gray-300 text-center" />
                                                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-3 rounded-xl hover:bg-blue-700 transition font-bold text-sm flex items-center justify-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                    </svg>
                                                    Acheter maintenant
                                                </button>
                                            </div>
                                        </form>
                                    @else
                                        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-center text-sm">
                                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                            </svg>
                                            Paiement en ligne requis
                                        </div>
                                    @endif
                                @elseif($hasOnlinePayment && !$hasAnyPaymentMethod)
                                    {{-- Paiement requis MAIS vendeur n'a rien configuré --}}
                                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
                                        <p class="text-sm text-gray-600">Contactez le vendeur pour organiser le paiement</p>
                                    </div>
                                @else
                                    {{-- Pas de paiement en ligne - Mode réservation/contact --}}
                                    <button onclick="openReservationModal()" class="w-full bg-emerald-600 text-white px-4 py-3 rounded-xl hover:bg-emerald-700 transition font-bold text-sm flex items-center justify-center gap-2 shadow-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Demander une réservation
                                    </button>
                                    <p class="text-xs text-gray-500 text-center">Paiement hors plateforme - Arrangez-vous directement avec le vendeur</p>
                                @endif
                                
                                {{-- Indicateur de stock --}}
                                <div class="bg-gray-50 rounded-xl p-3">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Disponible :</span>
                                        <span class="font-bold {{ $availableQty <= 3 ? 'text-red-600' : 'text-emerald-600' }}">
                                            {{ $availableQty }} / {{ $urgentSale->quantity }}
                                        </span>
                                    </div>
                                    @if(($urgentSale->reserved_quantity ?? 0) > 0)
                                    <div class="flex items-center justify-between text-sm mt-1">
                                        <span class="text-gray-600">Réservé :</span>
                                        <span class="font-medium text-blue-600">{{ $urgentSale->reserved_quantity }}</span>
                                    </div>
                                    @endif
                                </div>
                            @else
                                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-center font-medium">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                    </svg>
                                    Stock épuisé
                                </div>
                            @endif

                            <button onclick="shareProduct()" class="btn-action-secondary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                </svg>
                                Partager l'annonce
                            </button>
                            
                            <!-- Bouton Signaler -->
                            <button onclick="reportProduct()" class="btn-report">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                Signaler cette annonce
                            </button>
                        @else
                            <a href="{{ route('prestataire.urgent-sales.inventory', $urgentSale) }}" class="btn-action-primary">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                Gérer l'inventaire
                            </a>
                            <div class="bg-gray-50 rounded-xl p-3 space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Stock total :</span>
                                    <span class="font-bold text-gray-800">{{ $urgentSale->quantity }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Disponible :</span>
                                    <span class="font-bold text-emerald-600">{{ $urgentSale->available_quantity ?? $urgentSale->quantity }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Réservé :</span>
                                    <span class="font-bold text-blue-600">{{ $urgentSale->reserved_quantity ?? 0 }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Vendu :</span>
                                    <span class="font-bold text-green-600">{{ $urgentSale->sold_quantity ?? 0 }}</span>
                                </div>
                            </div>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn-action-primary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            Se connecter pour réserver
                        </a>
                    @endauth
                </div>
                
                <!-- Conseils de sécurité -->
                <div class="card-modern p-5 bg-amber-50 border-amber-200">
                    <div class="section-title" style="color: #d97706;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Conseils de sécurité
                    </div>
                    <ul class="space-y-2 text-sm text-amber-800">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Vérifiez le produit avant de payer
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Privilégiez les lieux publics
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Méfiez-vous des prix trop bas
                        </li>
                    </ul>
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
                                        <img src="{{ $storageUrl($sale->first_photo) }}" alt="{{ $sale->title }}" class="w-full h-full object-cover">
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

<!-- Modal d'affichage d'image plein écran - AMÉLIORÉ -->
<div id="imageModal" class="fixed inset-0 z-50 hidden opacity-0 transition-all duration-300" style="background: rgba(0, 0, 0, 0.95); backdrop-filter: blur(10px);">
    <!-- Header du modal -->
    <div class="absolute top-0 left-0 right-0 z-20 p-4 flex items-center justify-between bg-gradient-to-b from-black/60 to-transparent">
        <div class="flex items-center gap-3">
            <span id="imageCounterModal" class="text-white font-semibold text-lg">1 / {{ count($urgentSale->photos ?? []) }}</span>
            <span class="text-white/60 text-sm hidden sm:inline">{{ $urgentSale->title }}</span>
        </div>
        <div class="flex items-center gap-2">
            <!-- Bouton Zoom -->
            <button onclick="toggleZoom()" id="zoomButton" class="text-white/80 hover:text-white p-2 rounded-full hover:bg-white/10 transition-all" title="Zoomer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                </svg>
            </button>
            <!-- Bouton Fermer -->
            <button onclick="closeImageModal()" class="text-white/80 hover:text-white p-2 rounded-full hover:bg-white/10 transition-all" title="Fermer">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Container image principal -->
    <div class="absolute inset-0 flex items-center justify-center p-4 pt-16 pb-28" id="imageContainer">
        <div class="relative w-full h-full flex items-center justify-center" id="imageWrapper">
            <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain transition-transform duration-300 cursor-zoom-in select-none" style="transform-origin: center center;" draggable="false">
            
            <!-- Loading spinner -->
            <div id="imageLoader" class="absolute inset-0 flex items-center justify-center hidden">
                <div class="w-12 h-12 border-4 border-white/30 border-t-white rounded-full animate-spin"></div>
            </div>
        </div>
    </div>
    
    <!-- Flèches de navigation -->
    @if(count($urgentSale->photos ?? []) > 1)
    <button id="prevButtonModal" onclick="navigateImageModal(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 flex items-center justify-center bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white rounded-full transition-all hover:scale-110 group">
        <svg class="w-6 h-6 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
    </button>
    
    <button id="nextButtonModal" onclick="navigateImageModal(1)" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 flex items-center justify-center bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white rounded-full transition-all hover:scale-110 group">
        <svg class="w-6 h-6 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
    </button>
    @endif
    
    <!-- Barre de miniatures en bas -->
    @if($urgentSale->photos && count($urgentSale->photos) > 1)
    <div class="absolute bottom-0 left-0 right-0 z-20 p-4 bg-gradient-to-t from-black/80 to-transparent">
        <div class="flex items-center justify-center gap-2 overflow-x-auto py-2 px-4 scrollbar-hide" id="thumbnailsContainer">
            @foreach($urgentSale->photos as $index => $photo)
                @php 
                    $thumbUrl = filter_var($photo, FILTER_VALIDATE_URL) ? $photo : $storageUrl($photo);
                @endphp
                <button onclick="goToImage({{ $index }})" class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 transition-all duration-200 hover:scale-105 thumbnail-btn {{ $index === 0 ? 'border-white ring-2 ring-white/50' : 'border-transparent opacity-60 hover:opacity-100' }}" data-index="{{ $index }}">
                    <img src="{{ $thumbUrl }}" alt="Miniature {{ $index + 1 }}" class="w-full h-full object-cover" onerror="this.src='/images/placeholder.svg';">
                </button>
            @endforeach
        </div>
    </div>
    @endif
</div>

<!-- Modal de contact -->
@auth
    @php
        $modalSellerId = $urgentSale->prestataire?->user_id ?? $urgentSale->user_id;
    @endphp
    @if(auth()->user()->id !== $modalSellerId)
        <div id="contactModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-200" style="backdrop-filter: blur(3px); background-color: rgba(0, 0, 0, 0.45);">
            <div class="min-h-full w-full flex items-end sm:items-center justify-center p-3">
                <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl p-4 sm:p-6 w-full sm:max-w-md border border-gray-200 transform transition-all duration-200 scale-95 opacity-0 modal-show">
                    <div class="p-0">
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

<!-- Modal de réservation -->
@auth
    @php
        $reservationSellerId = $urgentSale->prestataire?->user_id ?? $urgentSale->user_id;
    @endphp
    @if(auth()->user()->id !== $reservationSellerId)
        @php
            $modalAvailableQty = ($urgentSale->quantity ?? 1) - ($urgentSale->reserved_quantity ?? 0) - ($urgentSale->sold_quantity ?? 0);
        @endphp
        @if($modalAvailableQty > 0)
        <div id="reservationModal" class="fixed inset-0 z-[100] hidden opacity-0 transition-opacity duration-200" style="backdrop-filter: blur(3px); background-color: rgba(0, 0, 0, 0.45);">
            <div class="h-full w-full flex items-center justify-center p-3 overflow-y-auto" style="padding-bottom: env(safe-area-inset-bottom, 0px);">
                <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 w-full sm:max-w-md border border-emerald-200 transform transition-all duration-200 scale-95 opacity-0 modal-show max-h-[85vh] overflow-y-auto">
                    <div class="p-0">
                        <form action="{{ route('urgent-sales.reserve', $urgentSale) }}" method="POST">
                            @csrf
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="bg-emerald-100 p-2 rounded-full">
                                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-base md:text-lg font-semibold text-gray-900">Demander une réservation</h3>
                                </div>
                                <button type="button" onclick="closeReservationModal()" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- Info produit -->
                            <div class="bg-gray-50 rounded-xl p-3 mb-4">
                                <div class="font-medium text-gray-900 text-sm">{{ Str::limit($urgentSale->title, 50) }}</div>
                                <div class="text-emerald-600 font-bold">{{ number_format($urgentSale->price, 2) }}€ / unité</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="reservationQuantity" class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Quantité souhaitée</label>
                                <div class="flex items-center gap-3">
                                    <button type="button" onclick="decrementQty()" class="w-10 h-10 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-xl font-bold text-gray-600">-</button>
                                    <input id="reservationQuantity" name="quantity" type="number" min="1" max="{{ $modalAvailableQty }}" value="1" class="flex-1 text-center text-lg font-bold border-gray-300 rounded-lg" onchange="updateTotal()">
                                    <button type="button" onclick="incrementQty()" class="w-10 h-10 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-xl font-bold text-gray-600">+</button>
                                </div>
                                <div class="text-xs text-gray-500 mt-1 text-center">
                                    {{ $modalAvailableQty }} disponible(s)
                                </div>
                            </div>
                            
                            <!-- Total estimé -->
                            <div class="bg-emerald-50 rounded-xl p-3 mb-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Total estimé :</span>
                                    <span id="reservationTotal" class="text-xl font-bold text-emerald-600">{{ number_format($urgentSale->price, 2) }}€</span>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="reservationMessage" class="block text-xs md:text-sm font-medium text-gray-700 mb-2">Message au vendeur (optionnel)</label>
                                <textarea id="reservationMessage" name="message" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm md:text-base" placeholder="Précisez vos besoins, questions, ou votre disponibilité pour récupérer l'article..."></textarea>
                            </div>
                            
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-4 text-xs text-amber-800">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <strong>Comment ça marche ?</strong>
                                        <ul class="mt-1 space-y-0.5">
                                            <li>1. Vous faites une demande de réservation</li>
                                            <li>2. Le vendeur confirme et réserve le stock</li>
                                            <li>3. Vous vous arrangez pour le paiement et la récupération</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex flex-row space-x-3">
                                <button type="button" onclick="closeReservationModal()" class="flex-1 bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg hover:bg-gray-300 transition duration-200 text-sm font-medium">
                                    Annuler
                                </button>
                                <button type="submit" class="flex-1 bg-emerald-600 text-white px-4 py-2.5 rounded-lg hover:bg-emerald-700 transition duration-200 text-sm font-bold">
                                    Envoyer la demande
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
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
<div id="reportModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-200" style="backdrop-filter: blur(3px); background-color: rgba(0, 0, 0, 0.45);">
    <div class="min-h-full w-full flex items-end sm:items-center justify-center p-3">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl p-4 sm:p-6 w-full sm:max-w-md border border-red-200 transform transition-all duration-200 scale-95 opacity-0 modal-show">
            <div class="p-0">
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
                @if(filter_var($photo, FILTER_VALIDATE_URL))
                url: '{{ $photo }}',
                @else
                url: '{{ $storageUrl($photo) }}',
                @endif
                alt: {!! Js::from(($urgentSale->title ?? '') . ' - Photo ' . ($index + 1)) !!}
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
        mainImage.onerror = function() {
            this.onerror = null;
            this.src = '/images/placeholder.svg';
        };
    }
    
    if (imageCounter) {
        imageCounter.textContent = `${currentImageIndex + 1} / ${saleImages.length}`;
    }
}

let isZoomed = false;
let zoomLevel = 1;

function openImageModal(index) {
    if (saleImages.length === 0) return;
    
    currentImageIndex = index;
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const imageCounter = document.getElementById('imageCounterModal');
    const loader = document.getElementById('imageLoader');
    
    if (modal && modalImage && imageCounter) {
        // Reset zoom
        isZoomed = false;
        zoomLevel = 1;
        modalImage.style.transform = 'scale(1)';
        modalImage.classList.remove('cursor-zoom-out');
        modalImage.classList.add('cursor-zoom-in');
        
        // Show loader
        if (loader) loader.classList.remove('hidden');
        
        // Load image
        modalImage.onload = function() {
            if (loader) loader.classList.add('hidden');
        };
        modalImage.src = saleImages[index].url;
        modalImage.alt = saleImages[index].alt;
        modalImage.onerror = function() {
            if (loader) loader.classList.add('hidden');
            this.src = '/images/placeholder.svg';
        };
        imageCounter.textContent = `${index + 1} / ${saleImages.length}`;
        
        // Update thumbnails
        updateThumbnails(index);
        
        // Show modal with animation
        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            modal.classList.add('opacity-100');
            modal.classList.remove('opacity-0');
        });
        document.body.style.overflow = 'hidden';
    }
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.classList.add('opacity-0');
        modal.classList.remove('opacity-100');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            // Reset zoom
            isZoomed = false;
            zoomLevel = 1;
        }, 300);
    }
}

function toggleZoom() {
    const modalImage = document.getElementById('modalImage');
    if (!modalImage) return;
    
    if (isZoomed) {
        zoomLevel = 1;
        modalImage.style.transform = 'scale(1)';
        modalImage.classList.remove('cursor-zoom-out');
        modalImage.classList.add('cursor-zoom-in');
        isZoomed = false;
    } else {
        zoomLevel = 2;
        modalImage.style.transform = 'scale(2)';
        modalImage.classList.remove('cursor-zoom-in');
        modalImage.classList.add('cursor-zoom-out');
        isZoomed = true;
    }
}

function updateThumbnails(activeIndex) {
    const thumbnails = document.querySelectorAll('.thumbnail-btn');
    thumbnails.forEach((thumb, index) => {
        if (index === activeIndex) {
            thumb.classList.add('border-white', 'ring-2', 'ring-white/50');
            thumb.classList.remove('border-transparent', 'opacity-60');
            // Scroll to active thumbnail
            thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        } else {
            thumb.classList.remove('border-white', 'ring-2', 'ring-white/50');
            thumb.classList.add('border-transparent', 'opacity-60');
        }
    });
}

function goToImage(index) {
    if (index < 0 || index >= saleImages.length) return;
    
    currentImageIndex = index;
    const modalImage = document.getElementById('modalImage');
    const imageCounter = document.getElementById('imageCounterModal');
    const loader = document.getElementById('imageLoader');
    
    // Reset zoom on image change
    if (isZoomed) {
        isZoomed = false;
        zoomLevel = 1;
        if (modalImage) {
            modalImage.style.transform = 'scale(1)';
            modalImage.classList.remove('cursor-zoom-out');
            modalImage.classList.add('cursor-zoom-in');
        }
    }
    
    if (loader) loader.classList.remove('hidden');
    
    if (modalImage && imageCounter) {
        modalImage.onload = function() {
            if (loader) loader.classList.add('hidden');
        };
        modalImage.src = saleImages[index].url;
        modalImage.alt = saleImages[index].alt;
        modalImage.onerror = function() {
            if (loader) loader.classList.add('hidden');
            this.src = '/images/placeholder.svg';
        };
        imageCounter.textContent = `${index + 1} / ${saleImages.length}`;
        
        // Update thumbnails
        updateThumbnails(index);
        
        // Update main image too
        const mainImage = document.getElementById('mainImage');
        const mainImageCounter = document.getElementById('imageCounter');
        if (mainImage) {
            mainImage.src = saleImages[index].url;
            mainImage.alt = saleImages[index].alt;
        }
        if (mainImageCounter) {
            mainImageCounter.textContent = `${index + 1} / ${saleImages.length}`;
        }
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
    
    goToImage(currentImageIndex);
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
        } else if (event.key === '+' || event.key === '=') {
            if (!isZoomed) toggleZoom();
        } else if (event.key === '-') {
            if (isZoomed) toggleZoom();
        }
    }
});

// Fermer le modal en cliquant à l'extérieur de l'image
const imageModal = document.getElementById('imageModal');
const modalImage = document.getElementById('modalImage');

if (imageModal) {
    imageModal.addEventListener('click', function(event) {
        // Fermer si on clique sur le fond (pas sur l'image, les boutons, ou les miniatures)
        if (event.target === this || event.target.id === 'imageContainer' || event.target.id === 'imageWrapper') {
            closeImageModal();
        }
    });
}

// Click sur l'image pour zoom
if (modalImage) {
    modalImage.addEventListener('click', function(event) {
        event.stopPropagation();
        toggleZoom();
    });
}

// Support du swipe sur mobile
let touchStartX = 0;
let touchStartY = 0;
let touchEndX = 0;
let touchEndY = 0;

if (imageModal) {
    imageModal.addEventListener('touchstart', function(e) {
        if (isZoomed) return; // Pas de swipe en mode zoom
        touchStartX = e.changedTouches[0].screenX;
        touchStartY = e.changedTouches[0].screenY;
    }, { passive: true });
    
    imageModal.addEventListener('touchend', function(e) {
        if (isZoomed) return;
        touchEndX = e.changedTouches[0].screenX;
        touchEndY = e.changedTouches[0].screenY;
        handleSwipe();
    }, { passive: true });
}

function handleSwipe() {
    const diffX = touchStartX - touchEndX;
    const diffY = touchStartY - touchEndY;
    const minSwipeDistance = 50;
    
    // Horizontal swipe (plus important que le vertical)
    if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > minSwipeDistance) {
        if (diffX > 0) {
            // Swipe left - next image
            navigateImageModal(1);
        } else {
            // Swipe right - previous image
            navigateImageModal(-1);
        }
    }
    // Swipe down pour fermer
    else if (diffY < -80) {
        closeImageModal();
    }
}

function openContactModal(title, id, price) {
    const message = `Bonjour, je suis intéressé(e) par votre annonce : ${title} (#Référence : ${id}) au prix de ${price}€.`;
    document.getElementById('message').value = message;
    
    const modal = document.getElementById('contactModal');
    modal.classList.remove('hidden');
    // Add animation classes
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        const modalContent = modal.querySelector('.modal-show');
        modalContent.classList.remove('scale-95');
        modalContent.classList.add('scale-100');
        modalContent.classList.remove('opacity-0');
    }, 10);
}

function closeContactModal() {
    const modal = document.getElementById('contactModal');
    const modalContent = modal.querySelector('.modal-show');
    
    if (modalContent) {
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');
        modalContent.classList.add('opacity-0');
    }
    
    modal.classList.add('opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function reportProduct() {
    const modal = document.getElementById('reportModal');
    modal.classList.remove('hidden');
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
    }, 300);
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

// Variables pour le modal de réservation
const unitPrice = {{ $urgentSale->price }};
const maxQty = {{ max(1, ($urgentSale->quantity ?? 1) - ($urgentSale->reserved_quantity ?? 0) - ($urgentSale->sold_quantity ?? 0)) }};

function openReservationModal() {
    const modal = document.getElementById('reservationModal');
    if (!modal) return;
    
    // Cacher la barre de navigation mobile
    const bottomNav = document.getElementById('mobile-bottom-nav');
    if (bottomNav) bottomNav.style.display = 'none';
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        const modalContent = modal.querySelector('.modal-show');
        if (modalContent) {
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
            modalContent.classList.remove('opacity-0');
        }
    }, 10);
}

function closeReservationModal() {
    const modal = document.getElementById('reservationModal');
    if (!modal) return;
    
    const modalContent = modal.querySelector('.modal-show');
    if (modalContent) {
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');
        modalContent.classList.add('opacity-0');
    }
    
    modal.classList.add('opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        // Réafficher la barre de navigation mobile
        const bottomNav = document.getElementById('mobile-bottom-nav');
        if (bottomNav) bottomNav.style.display = '';
    }, 300);
}

function incrementQty() {
    const input = document.getElementById('reservationQuantity');
    if (parseInt(input.value) < maxQty) {
        input.value = parseInt(input.value) + 1;
        updateTotal();
    }
}

function decrementQty() {
    const input = document.getElementById('reservationQuantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
        updateTotal();
    }
}

function updateTotal() {
    const input = document.getElementById('reservationQuantity');
    const totalEl = document.getElementById('reservationTotal');
    if (input && totalEl) {
        const qty = parseInt(input.value) || 1;
        const total = qty * unitPrice;
        totalEl.textContent = total.toFixed(2).replace('.', ',') + '€';
    }
}

// Fermer les modals en cliquant à l'extérieur
document.addEventListener('click', function(event) {
    const contactModal = document.getElementById('contactModal');
    const reportModal = document.getElementById('reportModal');
    const reservationModal = document.getElementById('reservationModal');
    
    if (event.target === contactModal) {
        closeContactModal();
    }
    
    if (event.target === reportModal) {
        closeReportModal();
    }
    
    if (event.target === reservationModal) {
        closeReservationModal();
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
