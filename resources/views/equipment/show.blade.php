@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        :root {
            --detail-primary: #16a34a;
            --detail-primary-dark: #15803d;
            --detail-light: #f0fdf4;
            --detail-accent: #0ea5e9;
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
            border: 1px solid rgba(22, 163, 74, 0.12);
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
            box-shadow: 0 6px 20px rgba(22, 163, 74, 0.25);
            border: none;
            cursor: pointer;
            width: 100%;
            text-decoration: none;
        }

        .btn-action-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(22, 163, 74, 0.35);
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
            border: 2px solid rgba(22, 163, 74, 0.18);
            cursor: pointer;
            width: 100%;
            text-decoration: none;
        }

        .btn-action-secondary:hover {
            background: #dcfce7;
            border-color: rgba(22, 163, 74, 0.28);
        }

        .seller-card {
            background: linear-gradient(135deg, #ffffff 0%, var(--detail-light) 100%);
            border-radius: 16px;
            padding: 16px;
            border: 2px solid rgba(22, 163, 74, 0.12);
        }
    </style>
@endpush

@section('title', $equipment->name . ' - Location de matériel - TaPrestation')

@section('content')
<div class="detail-page-bg">
    <div class="max-w-6xl mx-auto px-4 py-6">
        <!-- PWA Back Button + Breadcrumb -->
        <div class="flex items-center gap-3 mb-2 sm:mb-3">
            <x-pwa-back-button class="flex-shrink-0" />
            
            <nav class="flex-1 hidden sm:flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 sm:space-x-2">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center text-xs sm:text-sm font-medium text-gray-700 hover:text-green-600">
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
                        <a href="{{ route('equipment.index') }}" class="ml-1 text-xs sm:text-sm font-medium text-gray-700 hover:text-green-600">Matériel</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-xs sm:text-sm font-medium text-gray-500">{{ Str::limit($equipment->name, 30) }}</span>
                    </div>
                </li>
            </ol>
            </nav>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            <!-- Galerie d'images -->
            <div class="lg:col-span-3 space-y-4">
                <div class="gallery-container">
                    @if($equipment->photos && count($equipment->photos) > 0)
                        <div class="relative">
                            <!-- Image principale avec flèches de navigation -->
                            <div class="relative">
                                <x-media-image :path="$equipment->photos[0]" :alt="$equipment->name" id="mainImage" onclick="openImageModal(0)" class="gallery-main-img" />
                                
                                <!-- Flèche gauche -->
                                @if(count($equipment->photos) > 1)
                                    <button id="prevButton" onclick="navigateImage(-1)" class="nav-arrow left">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                    </button>
                                @endif
                                
                                <!-- Flèche droite -->
                                @if(count($equipment->photos) > 1)
                                    <button id="nextButton" onclick="navigateImage(1)" class="nav-arrow right">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
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
                        </div>
                    @elseif($equipment->main_photo)
                        <div class="relative">
                            <x-media-image :path="$equipment->main_photo" :alt="$equipment->name" class="gallery-main-img" />
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

                <!-- Titre et prix -->
                <div class="card-modern p-5">
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900 mb-2">{{ $equipment->name }}</h1>
                    @if($equipment->price_per_day)
                        <div class="flex items-baseline gap-2">
                            <span class="price-tag">{{ number_format($equipment->price_per_day, 2) }}€</span>
                            <span class="text-sm text-gray-500">/ jour</span>
                            @if($equipment->price_per_hour)
                                <span class="text-xs text-gray-400">| {{ number_format($equipment->price_per_hour, 2) }}€/h</span>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Description -->
                <div class="card-modern p-5">
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 mb-2">Description</h2>
                    <div class="prose max-w-none text-gray-700 text-sm leading-relaxed">
                        {!! nl2br(e($equipment->description)) !!}
                    </div>
                </div>

                <!-- Spécifications techniques -->
                @if($equipment->technical_specifications)
                <div class="card-modern p-5">
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 mb-2">Spécifications techniques</h2>
                    <div class="prose max-w-none text-gray-700 text-sm leading-relaxed">
                        {!! nl2br(e($equipment->technical_specifications)) !!}
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-2 space-y-4">
                <div class="card-modern p-5 sticky top-4">
                    <!-- Propriétaire -->
                    <div class="seller-card mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-base font-bold text-gray-900">Propriétaire</h2>
                            <a href="{{ route('prestataires.show', $equipment->prestataire) }}" class="text-sm font-medium" style="color: var(--detail-primary);">Voir →</a>
                        </div>
                        <a href="{{ route('prestataires.show', $equipment->prestataire) }}" class="block hover:opacity-90 p-2 rounded-xl transition-opacity">
                            <div class="flex items-center space-x-3">
                                @if($equipment->prestataire && $equipment->prestataire->photo)
                                    <x-media-image :path="$equipment->prestataire->photo" :alt="$equipment->prestataire->user->name ?? ''" class="w-10 h-10 rounded-full object-cover" />
                                @else
                                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $equipment->prestataire->user->name ?? '' }}</h3>
                                    <div class="flex items-center text-xs text-gray-500 mt-1">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        </svg>
                                        <span class="truncate">{{ $equipment->city ?? 'Non spécifié' }}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="space-y-2">
                        @if(isset($isOwner) && $isOwner)
                            <a href="{{ route('prestataire.equipment.edit', $equipment) }}" class="btn-action-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Modifier
                            </a>
                        @else
                            @auth
                                @if(($equipment->status ?? 'active') === 'active' && ($equipment->is_available ?? true))
                                    <a href="{{ route('equipment.reserve', $equipment) }}" class="btn-action-primary">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Réserver
                                    </a>
                                @else
                                    <div class="bg-gray-100 text-gray-500 px-4 py-3 rounded-lg text-center text-sm">
                                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                        </svg>
                                        Équipement non disponible
                                    </div>
                                @endif
                                <a href="{{ route('messaging.start', $equipment->prestataire) }}" class="btn-action-secondary">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    Contacter
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn-action-primary">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                    </svg>
                                    Se connecter
                                </a>
                            @endauth
                        @endif
                    </div>

                    <!-- Localisation -->
                    @if ($equipment->latitude && $equipment->longitude)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">Localisation</h3>
                            <div id="map" class="h-32 rounded-lg"></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal pour afficher les images en plein écran --}}
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden items-center justify-center">
    <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
    
    <button id="prevBtnModal" onclick="navigateImageModal(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/30 text-white rounded-full p-3 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
    </button>
    
    <img id="modalImage" src="" alt="" class="max-h-[90vh] max-w-[90vw] object-contain">
    
    <button id="nextBtnModal" onclick="navigateImageModal(1)" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/30 text-white rounded-full p-3 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
    </button>
    
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white text-sm bg-black/50 px-4 py-2 rounded-full">
        <span id="currentImageNumber">1</span> / <span id="totalImages">1</span>
    </div>
</div>

{{-- Modal pour signaler l'équipement --}}
<div id="reportModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 relative">
        <button onclick="closeReportModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <h3 class="text-lg font-bold text-gray-900 mb-4">Signaler cet équipement</h3>
        <form action="{{ route('equipment.report', $equipment) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                <select id="category" name="category" onchange="updateReason()" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                    <option value="inappropriate">Contenu inapproprié</option>
                    <option value="fraud">Annonce frauduleuse</option>
                    <option value="safety">Problème de sécurité</option>
                    <option value="condition">État de l'équipement</option>
                    <option value="pricing">Prix incorrect</option>
                    <option value="availability">Disponibilité incorrecte</option>
                    <option value="other">Autre</option>
                </select>
            </div>
            <input type="hidden" id="reason" name="reason" value="Contenu inapproprié">
            <div class="mb-4">
                <label for="details" class="block text-sm font-medium text-gray-700 mb-1">Détails (optionnel)</label>
                <textarea id="details" name="details" rows="3" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500" placeholder="Décrivez le problème..."></textarea>
            </div>
            <button type="submit" class="w-full bg-red-600 text-white py-3 rounded-xl font-semibold hover:bg-red-700 transition">Envoyer le signalement</button>
        </form>
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
                    "{{ \Illuminate\Support\Facades\Storage::disk('public')->exists($photo) ? \Illuminate\Support\Facades\Storage::url($photo) : asset('images/placeholder.svg') }}",
                @endforeach
        @elseif($equipment->main_photo)
            "{{ \Illuminate\Support\Facades\Storage::disk('public')->exists($equipment->main_photo) ? \Illuminate\Support\Facades\Storage::url($equipment->main_photo) : asset('images/placeholder.svg') }}"
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
        const modal = document.getElementById('reportModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeReportModal() {
        const modal = document.getElementById('reportModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
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
    const reportModal = document.getElementById('reportModal');
    if (reportModal) {
        reportModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeReportModal();
            }
        });
    }
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('imageModal');
        if (modal && !modal.classList.contains('hidden')) {
            if (e.key === 'Escape') {
                closeImageModal();
            } else if (e.key === 'ArrowLeft') {
                navigateImageModal(-1);
            } else if (e.key === 'ArrowRight') {
                navigateImageModal(1);
            }
        }
    });
    
    // Support du swipe tactile pour la modal d'images
    let touchStartX = 0;
    let touchEndX = 0;
    const swipeThreshold = 50; // Minimum distance pour considérer comme un swipe
    
    const imageModalSwipe = document.getElementById('imageModal');
    if (imageModalSwipe) {
        imageModalSwipe.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        
        imageModalSwipe.addEventListener('touchend', function(e) {
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
    
    // Close modal when clicking outside the image
    const imageModalClick = document.getElementById('imageModal');
    if (imageModalClick) {
        imageModalClick.addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });
    }
    
    // Delete equipment modal functionality
    document.addEventListener('DOMContentLoaded', function() {
        const deleteBtn = document.getElementById('deleteEquipmentBtn');
        const deleteModal = document.getElementById('deleteConfirmationModal');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
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
            cancelDeleteBtn.addEventListener('click', function() {
                closeModal();
            });
        }
        
        // Handle confirm delete
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
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
            deleteModal.addEventListener('click', function(e) {
                if (e.target === deleteModal) {
                    closeModal();
                }
            });
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
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
