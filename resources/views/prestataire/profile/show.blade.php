@extends('layouts.app')

@section('title', $prestataire->user->name . ' - Prestataire')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/prestataire-profile.css') }}?v={{ time() }}">
@endpush

@section('content')
@php
    $totalReviews = $allReviews->count();
    $averageRating = $totalReviews > 0 ? round($allReviews->avg('rating'), 1) : 0;
    $hasProducts = $allUrgentSales->count() > 0;
    // Afficher Food si food_enabled OU si des produits existent
    $hasFood = isset($foodProducts) && $foodProducts->count() > 0;
    $hasVideos = $prestataire->videos && $prestataire->videos->count() > 0;
    $isBoutique = $hasProducts || $allUrgentSales->count() >= 3;
    $isRestaurant = $hasFood;
    $onlineStatus = $prestataire->user->online_status ?? 'Hors ligne';
    $isOnline = $prestataire->user->is_online ?? false;
@endphp

<div class="prestataire-profile-page">
    
    {{-- PWA Back Button --}}
    <div class="container mx-auto px-4 pt-4">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('services.index') }}" 
           class="inline-flex items-center px-3 py-2 bg-white rounded-xl shadow-sm border border-blue-200 text-blue-700 hover:bg-blue-50 transition-colors text-sm font-medium">
            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour
        </a>
    </div>
    
    {{-- HERO HEADER - SIMPLIFIÉ --}}
    <div class="profile-hero">
        <div class="profile-hero-content">
            {{-- Avatar + Nom + Spécialité --}}
            <div class="profile-avatar-container">
                <div class="profile-avatar">
                    @if($prestataire->photo)
                        <img src="{{ asset('storage/' . $prestataire->photo) }}" alt="{{ $prestataire->user->name }}">
                    @elseif($prestataire->user->avatar)
                        <img src="{{ asset('storage/' . $prestataire->user->avatar) }}" alt="{{ $prestataire->user->name }}">
                    @elseif($prestataire->user->profile_photo_url)
                        <img src="{{ $prestataire->user->profile_photo_url }}" alt="{{ $prestataire->user->name }}">
                    @else
                        <div class="profile-avatar-placeholder">
                            <svg fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                @if($prestataire->isVerified())
                    <div class="profile-verified-badge" title="Prestataire vérifié">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                @endif
            </div>
            
            <h1 class="profile-name">{{ $prestataire->user->name }}</h1>
            
            <div class="profile-specialty">
                {{ $prestataire->secteur_activite ?? 'Prestataire de services' }}
            </div>
            
            {{-- Statut en ligne --}}
            <div class="profile-online-status {{ $isOnline ? 'online' : 'offline' }}">
                {{ $onlineStatus }}
            </div>
            
            {{-- Note étoiles simple --}}
            @if($totalReviews > 0)
            <div class="profile-rating-inline" onclick="scrollToReviews()">
                <span class="rating-score">{{ $averageRating }}</span>
                <div class="rating-stars">
                    @for($i = 1; $i <= 5; $i++)
                        <svg fill="{{ $i <= round($averageRating) ? '#fbbf24' : '#d1d5db' }}" viewBox="0 0 24 24">
                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                        </svg>
                    @endfor
                </div>
                <span class="rating-count">({{ $totalReviews }})</span>
            </div>
            @endif
        </div>
    </div>
    
    {{-- BARRE D'ACTIONS STICKY --}}
    <div class="profile-action-bar">
        <div class="profile-action-bar-inner">
            {{-- Info rapide --}}
            <div class="action-bar-info">
                @if($prestataire->city || $prestataire->address)
                    <span class="action-bar-location">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ $prestataire->city ?? $prestataire->address }}
                    </span>
                @endif
            </div>
            
            {{-- Boutons clairs --}}
            <div class="action-bar-buttons">
                @auth
                    @if(auth()->user()->isClient())
                        <a href="{{ route('client.messaging.start', $prestataire) }}" class="action-btn action-btn-primary">
                            <i class="fas fa-comment-dots"></i>
                            <span>Contacter</span>
                        </a>
                        
                        @if($prestataire->phone && ($prestataire->phone_visible ?? true))
                            <a href="tel:{{ $prestataire->phone }}" class="action-btn action-btn-phone">
                                <i class="fas fa-phone"></i>
                                <span class="hide-mobile">Appeler</span>
                            </a>
                        @endif
                        
                        @if(auth()->user()->client && auth()->user()->client->isFollowing($prestataire->id))
                            <form action="{{ route('client.prestataire-follows.unfollow', $prestataire) }}" method="POST" class="action-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn action-btn-following">
                                    <i class="fas fa-heart"></i>
                                    <span class="hide-mobile">Abonné</span>
                                </button>
                            </form>
                        @else
                            <form action="{{ route('client.prestataire-follows.follow', $prestataire) }}" method="POST" class="action-form">
                                @csrf
                                <button type="submit" class="action-btn action-btn-follow">
                                    <i class="far fa-heart"></i>
                                    <span class="hide-mobile">Suivre</span>
                                </button>
                            </form>
                        @endif
                    @endif
                @else
                    <a href="{{ route('login') }}" class="action-btn action-btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Se connecter</span>
                    </a>
                @endauth
            </div>
        </div>
    </div>
    
    {{-- CONTENU PRINCIPAL --}}
    <div class="profile-content">
        
        {{-- SECTION: Description --}}
        @if($prestataire->description)
        <div class="profile-section">
            <div class="profile-section-header">
                <h2 class="profile-section-title">
                    <div class="profile-section-title-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    À propos
                </h2>
            </div>
            <div class="profile-section-body">
                <p class="profile-description">{{ $prestataire->description }}</p>
            </div>
        </div>
        @endif
        
        {{-- SECTION: Services --}}
        <div class="profile-section" id="services-section">
            <div class="profile-section-header">
                <h2 class="profile-section-title">
                    <div class="profile-section-title-icon">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    Services proposés
                </h2>
                @if($allServices->count() > 0)
                    <span class="profile-section-badge">{{ $allServices->count() }}</span>
                @endif
            </div>
            <div class="profile-section-body">
                @if($allServices->count() > 0)
                    <div class="profile-items-grid" id="services-grid">
                        @foreach($allServices->take(8) as $service)
                            <a href="{{ route('services.show', $service) }}" class="profile-item-card">
                                <div class="profile-item-image">
                                    @if($service->images && $service->images->count() > 0)
                                        <img src="{{ asset('storage/' . $service->images->first()->image_path) }}" alt="{{ $service->title }}" loading="lazy">
                                        @if($service->images->count() > 1)
                                            <div class="profile-item-badge">
                                                <i class="fas fa-images"></i>
                                                {{ $service->images->count() }}
                                            </div>
                                        @endif
                                    @else
                                        <div class="profile-item-placeholder">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            <span>Pas d'image</span>
                                        </div>
                                    @endif
                                    @if($service->price)
                                        <div class="profile-item-price">{{ number_format($service->price, 0, ',', ' ') }} €</div>
                                    @endif
                                </div>
                                <div class="profile-item-content">
                                    <h3 class="profile-item-title">{{ $service->title }}</h3>
                                    <p class="profile-item-desc">{{ Str::limit($service->description, 100) }}</p>
                                    <div class="profile-item-meta">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ $service->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    
                    @if($allServices->count() > 8)
                        <div class="profile-load-more">
                            <a href="{{ route('prestataires.services', $prestataire) }}" class="profile-load-more-btn" style="text-decoration: none;">
                                <span>Voir tous les services ({{ $allServices->count() }})</span>
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                            </a>
                        </div>
                        
                        {{-- Services cachés --}}
                        <div id="hidden-services" style="display: none;">
                            <div class="profile-items-grid" style="margin-top: 24px;">
                                @foreach($allServices->skip(8) as $service)
                                    <a href="{{ route('services.show', $service) }}" class="profile-item-card">
                                        <div class="profile-item-image">
                                            @if($service->images && $service->images->count() > 0)
                                                <img src="{{ asset('storage/' . $service->images->first()->image_path) }}" alt="{{ $service->title }}" loading="lazy">
                                            @else
                                                <div class="profile-item-placeholder">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span>Pas d'image</span>
                                                </div>
                                            @endif
                                            @if($service->price)
                                                <div class="profile-item-price">{{ number_format($service->price, 0, ',', ' ') }} €</div>
                                            @endif
                                        </div>
                                        <div class="profile-item-content">
                                            <h3 class="profile-item-title">{{ $service->title }}</h3>
                                            <p class="profile-item-desc">{{ Str::limit($service->description, 100) }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <div class="profile-empty">
                        <div class="profile-empty-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3>Aucun service disponible</h3>
                        <p>Ce prestataire n'a pas encore publié de services.</p>
                    </div>
                @endif
            </div>
        </div>
        
        {{-- SECTION: Équipements --}}
        <div class="profile-section" id="equipments-section">
            <div class="profile-section-header">
                <h2 class="profile-section-title">
                    <div class="profile-section-title-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-tools"></i>
                    </div>
                    Équipements à louer
                </h2>
                @if($allEquipments->count() > 0)
                    <span class="profile-section-badge" style="background: #10b981;">{{ $allEquipments->count() }}</span>
                @endif
            </div>
            <div class="profile-section-body">
                @if($allEquipments->count() > 0)
                    <div class="profile-items-grid">
                        @foreach($allEquipments->take(8) as $equipment)
                            <a href="{{ route('equipment.show', $equipment) }}" class="profile-item-card">
                                <div class="profile-item-image">
                                    @if($equipment->main_photo)
                                        <img src="{{ asset('storage/' . $equipment->main_photo) }}" alt="{{ $equipment->name }}" loading="lazy">
                                    @else
                                        <div class="profile-item-placeholder">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                            </svg>
                                            <span>Pas d'image</span>
                                        </div>
                                    @endif
                                    @if($equipment->price_per_day)
                                        <div class="profile-item-price">{{ number_format($equipment->price_per_day, 0, ',', ' ') }} €/jour</div>
                                    @endif
                                </div>
                                <div class="profile-item-content">
                                    <h3 class="profile-item-title">{{ $equipment->name }}</h3>
                                    <p class="profile-item-desc">{{ Str::limit($equipment->description, 100) }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    
                    @if($allEquipments->count() > 8)
                        <div class="profile-load-more">
                            <a href="{{ route('prestataires.equipements', $prestataire) }}" class="profile-load-more-btn" style="text-decoration: none;">
                                <span>Voir tous les équipements ({{ $allEquipments->count() }})</span>
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                            </a>
                        </div>
                    @endif
                @else
                    <div class="profile-empty">
                        <div class="profile-empty-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <h3>Aucun équipement disponible</h3>
                        <p>Ce prestataire n'a pas d'équipements à louer pour le moment.</p>
                    </div>
                @endif
            </div>
        </div>
        
        {{-- SECTION: Boutique (ventes urgentes / annonces) --}}
        @if($hasProducts)
        <div class="profile-section" id="boutique-section">
            <div class="profile-section-header">
                <h2 class="profile-section-title">
                    <div class="profile-section-title-icon" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">
                        <i class="fas fa-store"></i>
                    </div>
                    Boutique
                </h2>
                <span class="profile-section-badge" style="background: #f97316;">{{ $allUrgentSales->count() }}</span>
            </div>
            <div class="profile-section-body">
                <div class="profile-items-grid">
                    @foreach($allUrgentSales->take(6) as $sale)
                        <a href="{{ route('urgent-sales.show', $sale) }}" class="profile-item-card">
                            <div class="profile-item-image">
                                @if($sale->photos && count($sale->photos ?? []) > 0)
                                    @php $firstPhoto = $sale->photos[0]; @endphp
                                    @if(filter_var($firstPhoto, FILTER_VALIDATE_URL))
                                        <img src="{{ $firstPhoto }}" alt="{{ $sale->title }}" loading="lazy">
                                    @else
                                        <x-media-image :path="$firstPhoto" :alt="$sale->title" class="w-full h-full object-cover" />
                                    @endif
                                @else
                                    <div class="profile-item-placeholder" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);">
                                        <span style="font-size: 44px;">🛍️</span>
                                    </div>
                                @endif
                                @if($sale->price)
                                    <div class="profile-item-price" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">{{ number_format($sale->price, 2, ',', ' ') }} €</div>
                                @endif
                            </div>
                            <div class="profile-item-content">
                                <h3 class="profile-item-title">{{ $sale->title }}</h3>
                                <p class="profile-item-desc">{{ Str::limit($sale->description, 80) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if($allUrgentSales->count() > 6)
                    <div class="profile-load-more" style="margin-top: 24px;">
                        <a href="{{ route('prestataires.boutique', $prestataire) }}" class="profile-load-more-btn" style="text-decoration: none; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white;">
                            <span>Voir toute la boutique ({{ $allUrgentSales->count() }} annonces)</span>
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>
        @endif

        {{-- SECTION: Menu Food --}}
        @if(isset($foodProducts) && $foodProducts->count() > 0)
        <div class="profile-section" id="food-section">
            <div class="profile-section-header">
                <h2 class="profile-section-title">
                    <div class="profile-section-title-icon" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">
                        <i class="fas fa-utensils"></i>
                    </div>
                    Menu & Commandes
                </h2>
                <span class="profile-section-badge" style="background: #f97316;">{{ $foodProducts->count() }}</span>
            </div>
            <div class="profile-section-body">
                {{-- Lien vers le menu complet --}}
                <div style="margin-bottom: 24px; padding: 16px; background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border-radius: 12px; border: 1px solid #fed7aa;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <h4 style="font-weight: 600; color: #9a3412; margin-bottom: 4px;">🍽️ Commander chez ce prestataire</h4>
                            <p style="font-size: 14px; color: #c2410c;">Découvrez le menu complet et passez commande en ligne</p>
                        </div>
                        <a href="{{ route('food.menu', $prestataire) }}" 
                           style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white; font-weight: 600; border-radius: 12px; text-decoration: none; box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3); transition: all 0.3s;">
                            <i class="fas fa-shopping-bag"></i>
                            Voir le menu
                        </a>
                    </div>
                </div>
                
                {{-- Aperçu des produits --}}
                <div class="profile-items-grid">
                    @foreach($foodProducts->take(6) as $product)
                        <a href="{{ route('food.menu', $prestataire) }}" class="profile-item-card">
                            <div class="profile-item-image">
                                @if($product->image)
                                    <img src="{{ storage_asset_url($product->image) }}" alt="{{ $product->name }}" loading="lazy">
                                @else
                                    <div class="profile-item-placeholder" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);">
                                        <span style="font-size: 48px;">🍽️</span>
                                    </div>
                                @endif
                                @if($product->price)
                                    <div class="profile-item-price" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">{{ number_format($product->price, 2, ',', ' ') }} €</div>
                                @endif
                            </div>
                            <div class="profile-item-content">
                                <h3 class="profile-item-title">{{ $product->name }}</h3>
                                <p class="profile-item-desc">{{ Str::limit($product->description, 80) }}</p>
                                @if($product->category)
                                    <div class="profile-item-meta" style="color: #f97316;">
                                        <i class="fas fa-tag"></i>
                                        {{ \App\Models\FoodProduct::categories()[$product->category] ?? $product->category }}
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
                
                @if($foodProducts->count() > 6)
                    <div class="profile-load-more" style="margin-top: 24px;">
                        <a href="{{ route('food.menu', $prestataire) }}" class="profile-load-more-btn" style="text-decoration: none; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white;">
                            <span>Voir tout le menu ({{ $foodProducts->count() }} produits)</span>
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>
        @elseif($prestataire->food_enabled)
        {{-- Le prestataire a food_enabled mais pas de produits --}}
        <div class="profile-section" id="food-section">
            <div class="profile-section-header">
                <h2 class="profile-section-title">
                    <div class="profile-section-title-icon" style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);">
                        <i class="fas fa-utensils"></i>
                    </div>
                    Commandes Food
                </h2>
            </div>
            <div class="profile-section-body">
                <div style="padding: 16px; background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border-radius: 12px; border: 1px solid #fed7aa;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 32px;">🍽️</span>
                        <div>
                            <h4 style="font-weight: 600; color: #9a3412;">Ce prestataire propose des commandes</h4>
                            <p style="font-size: 14px; color: #c2410c;">Le menu sera bientôt disponible</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        {{-- SECTION: Vidéos --}}
        @if($hasVideos)
        <div class="profile-section" id="videos-section">
            <div class="profile-section-header">
                <h2 class="profile-section-title">
                    <div class="profile-section-title-icon" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);">
                        <i class="fas fa-video"></i>
                    </div>
                    Vidéos
                </h2>
                <span class="profile-section-badge" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);">{{ $prestataire->videos->count() }}</span>
            </div>
            <div class="profile-section-body">
                <div class="profile-videos-grid">
                    @foreach($prestataire->videos->take(6) as $video)
                        <div class="profile-video-card js-video-card" data-video-url="{{ asset('storage/' . $video->video_path) }}" data-video-title="{{ $video->title ?? 'Vidéo' }}">
                            @if($video->thumbnail)
                                <img src="{{ asset('storage/' . $video->thumbnail) }}" alt="{{ $video->title }}" loading="lazy">
                            @else
                                <video src="{{ asset('storage/' . $video->video_path) }}" preload="metadata" muted></video>
                            @endif
                            <div class="profile-video-overlay">
                                <div class="profile-video-play">
                                    <i class="fas fa-play"></i>
                                </div>
                                <div class="profile-video-title">{{ $video->title ?? 'Vidéo' }}</div>
                                @if($video->views_count)
                                    <div class="profile-video-views">
                                        <i class="fas fa-eye"></i>
                                        {{ number_format($video->views_count) }} vues
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                
                @if($prestataire->videos->count() > 6)
                    <div class="profile-load-more">
                        <button class="profile-load-more-btn" onclick="showAllVideos()" id="toggle-videos">
                            <span>Voir les {{ $prestataire->videos->count() - 6 }} autres vidéos</span>
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div id="hidden-videos" style="display: none;">
                        <div class="profile-videos-grid" style="margin-top: 12px;">
                            @foreach($prestataire->videos->skip(6) as $video)
                                <div class="profile-video-card js-video-card" data-video-url="{{ asset('storage/' . $video->video_path) }}" data-video-title="{{ $video->title ?? 'Vidéo' }}">
                                    @if($video->thumbnail)
                                        <img src="{{ asset('storage/' . $video->thumbnail) }}" alt="{{ $video->title }}" loading="lazy">
                                    @else
                                        <video src="{{ asset('storage/' . $video->video_path) }}" preload="metadata" muted></video>
                                    @endif
                                    <div class="profile-video-overlay">
                                        <div class="profile-video-play">
                                            <i class="fas fa-play"></i>
                                        </div>
                                        <div class="profile-video-title">{{ $video->title ?? 'Vidéo' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif
        
        {{-- SECTION: Avis Clients --}}
        <div class="profile-section" id="reviews-section">
            <div class="profile-section-header">
                <h2 class="profile-section-title">
                    <div class="profile-section-title-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="fas fa-star"></i>
                    </div>
                    Avis clients
                </h2>
                @if($totalReviews > 0)
                    <div class="profile-reviews-summary">
                        <span class="profile-reviews-avg">{{ $averageRating }}</span>
                        <div>
                            <div class="profile-reviews-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg fill="{{ $i <= round($averageRating) ? 'currentColor' : '#d1d5db' }}" viewBox="0 0 24 24">
                                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                                    </svg>
                                @endfor
                            </div>
                            <span>{{ $totalReviews }} avis</span>
                        </div>
                    </div>
                @endif
            </div>
            <div class="profile-section-body">
                {{-- Formulaire pour laisser un avis --}}
                @auth
                    @if(auth()->user()->client)
                        @if(!$existingReview && $hasInteracted)
                            {{-- Bouton pour afficher le formulaire --}}
                            <div class="review-form-toggle" id="review-toggle-btn" style="margin-bottom: 20px;">
                                <button onclick="toggleReviewForm()" style="display: flex; align-items: center; gap: 8px; padding: 12px 20px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Laisser un avis
                                </button>
                            </div>
                            
                            {{-- Formulaire masqué par défaut --}}
                            <div id="review-form" style="display: none; margin-bottom: 24px; padding: 24px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 16px; border: 1px solid #f59e0b;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                                    <h3 style="font-size: 18px; font-weight: 700; color: #92400e; margin: 0;">✍️ Votre avis compte !</h3>
                                    <button onclick="toggleReviewForm()" style="background: none; border: none; cursor: pointer; padding: 4px;">
                                        <svg width="24" height="24" fill="none" stroke="#92400e" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <form action="{{ route('reviews.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="prestataire_id" value="{{ $prestataire->id }}">
                                    
                                    {{-- Note en étoiles --}}
                                    <div style="margin-bottom: 20px;">
                                        <label style="display: block; font-size: 14px; font-weight: 600; color: #92400e; margin-bottom: 8px;">Note</label>
                                        <div id="star-rating" style="display: flex; gap: 4px;">
                                            @for($i = 1; $i <= 5; $i++)
                                                <button type="button" class="star-btn" data-rating="{{ $i }}" onclick="setRating({{ $i }})" style="background: none; border: none; cursor: pointer; padding: 0; transition: transform 0.2s;">
                                                    <svg width="32" height="32" fill="#d1d5db" viewBox="0 0 24 24" style="transition: fill 0.2s;">
                                                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                                                    </svg>
                                                </button>
                                            @endfor
                                        </div>
                                        <input type="hidden" name="rating" id="rating-input" required>
                                    </div>
                                    
                                    {{-- Commentaire --}}
                                    <div style="margin-bottom: 20px;">
                                        <label for="comment" style="display: block; font-size: 14px; font-weight: 600; color: #92400e; margin-bottom: 8px;">Commentaire</label>
                                        <textarea name="comment" id="comment" rows="4" maxlength="500" 
                                            style="width: 100%; padding: 12px; border: 2px solid #fbbf24; border-radius: 10px; font-size: 15px; resize: vertical; background: white;"
                                            placeholder="Partagez votre expérience avec ce prestataire..." oninput="updateCharCount()"></textarea>
                                        <div style="font-size: 12px; color: #92400e; margin-top: 4px;">
                                            <span id="char-count">0</span>/500 caractères
                                        </div>
                                    </div>
                                    
                                    {{-- Bouton d'envoi --}}
                                    <button type="submit" style="width: 100%; padding: 14px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border: none; border-radius: 10px; font-weight: 600; font-size: 16px; cursor: pointer; transition: all 0.3s ease;">
                                        ⭐ Envoyer mon avis
                                    </button>
                                </form>
                            </div>
                        @elseif(!$existingReview && !$hasInteracted)
                            <div style="margin-bottom: 20px; padding: 16px; background: #fef9c3; border: 1px solid #fbbf24; border-radius: 12px;">
                                <div style="display: flex; align-items: flex-start; gap: 10px;">
                                    <svg width="20" height="20" fill="#d97706" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 2px;">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"></path>
                                    </svg>
                                    <p style="color: #92400e; font-size: 14px; margin: 0;">
                                        Vous devez d'abord interagir avec ce prestataire (envoyer un message ou réserver un service) pour pouvoir laisser un avis.
                                    </p>
                                </div>
                            </div>
                        @elseif($existingReview)
                            <div style="margin-bottom: 20px; padding: 16px; background: #d1fae5; border: 1px solid #10b981; border-radius: 12px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <svg width="20" height="20" fill="#059669" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path>
                                    </svg>
                                    <p style="color: #065f46; font-size: 14px; margin: 0; font-weight: 500;">
                                        ✓ Vous avez déjà évalué ce prestataire. Merci pour votre avis !
                                    </p>
                                </div>
                            </div>
                        @endif
                    @endif
                @else
                    <div style="margin-bottom: 20px; padding: 16px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 12px;">
                        <p style="color: #4b5563; font-size: 14px; margin: 0;">
                            <a href="{{ route('login') }}" style="color: #2563eb; text-decoration: none; font-weight: 600;">Connectez-vous</a> pour laisser un avis sur ce prestataire.
                        </p>
                    </div>
                @endauth
                
                @if($totalReviews > 0)
                    @foreach($allReviews->take(5) as $review)
                        <div class="profile-review-card">
                            <div class="profile-review-header">
                                <div class="profile-review-avatar">
                                    {{ strtoupper(substr($review->client->name ?? 'A', 0, 1)) }}
                                </div>
                                <div class="profile-review-author">
                                    <div class="profile-review-name">{{ $review->client->name ?? 'Client anonyme' }}</div>
                                    <div class="profile-review-date">{{ $review->created_at->diffForHumans() }}</div>
                                </div>
                                <div class="profile-review-rating">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg fill="{{ $i <= $review->rating ? '#f59e0b' : '#d1d5db' }}" viewBox="0 0 24 24">
                                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                                        </svg>
                                    @endfor
                                </div>
                            </div>
                            @if($review->comment)
                                <p class="profile-review-text">{{ $review->comment }}</p>
                            @endif
                        </div>
                    @endforeach
                    
                    @if($totalReviews > 5)
                        <div class="profile-load-more">
                            <button class="profile-load-more-btn">
                                <span>Voir tous les {{ $totalReviews }} avis</span>
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                @else
                    <div class="profile-empty">
                        <div class="profile-empty-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                        </div>
                        <h3>Pas encore d'avis</h3>
                        <p>Soyez le premier à laisser un avis sur ce prestataire !</p>
                    </div>
                @endif
            </div>
        </div>
        
    </div>
</div>

<script>
function scrollToReviews() {
    const reviewsSection = document.getElementById('reviews-section');
    if (reviewsSection) {
        reviewsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function toggleServices() {
    const hidden = document.getElementById('hidden-services');
    const btn = document.getElementById('toggle-services');
    if (hidden.style.display === 'none') {
        hidden.style.display = 'block';
        btn.classList.add('expanded');
        btn.querySelector('span').textContent = 'Voir moins';
    } else {
        hidden.style.display = 'none';
        btn.classList.remove('expanded');
        btn.querySelector('span').textContent = 'Voir les {{ $allServices->count() - 8 }} autres services';
    }
}

function toggleEquipments() {
    const hidden = document.getElementById('hidden-equipments');
    const btn = document.getElementById('toggle-equipments');
    if (hidden && hidden.style.display === 'none') {
        hidden.style.display = 'block';
        btn.classList.add('expanded');
        btn.querySelector('span').textContent = 'Voir moins';
    } else if (hidden) {
        hidden.style.display = 'none';
        btn.classList.remove('expanded');
        btn.querySelector('span').textContent = 'Voir les {{ $allEquipments->count() - 8 }} autres équipements';
    }
}

// Review form functions
function toggleReviewForm() {
    const form = document.getElementById('review-form');
    const btn = document.getElementById('review-toggle-btn');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        btn.style.display = 'none';
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
        form.style.display = 'none';
        btn.style.display = 'block';
    }
}

function setRating(rating) {
    document.getElementById('rating-input').value = rating;
    const stars = document.querySelectorAll('#star-rating .star-btn svg');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.style.fill = '#f59e0b';
            star.parentElement.style.transform = 'scale(1.1)';
        } else {
            star.style.fill = '#d1d5db';
            star.parentElement.style.transform = 'scale(1)';
        }
    });
}

function updateCharCount() {
    const textarea = document.getElementById('comment');
    const counter = document.getElementById('char-count');
    if (textarea && counter) {
        counter.textContent = textarea.value.length;
    }
}

// Navigation par sections
function scrollToSection(sectionId, tabEl) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // Update active tab
        document.querySelectorAll('.profile-nav-tab').forEach(tab => tab.classList.remove('active'));
        if (tabEl && tabEl.classList) {
            tabEl.classList.add('active');
        }
    }
}

// Video modal
function openVideoModal(videoUrl, title) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('video-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'video-modal';
        modal.className = 'video-modal';
        modal.innerHTML = `
            <div class="video-modal-content">
                <button class="video-modal-close" onclick="closeVideoModal()">
                    <i class="fas fa-times"></i>
                </button>
                <video id="modal-video" controls autoplay playsinline></video>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Close on background click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeVideoModal();
            }
        });
    }
    
    const video = document.getElementById('modal-video');
    video.src = videoUrl;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeVideoModal() {
    const modal = document.getElementById('video-modal');
    const video = document.getElementById('modal-video');
    if (modal) {
        modal.classList.remove('active');
        video.pause();
        video.src = '';
        document.body.style.overflow = '';
    }
}

document.addEventListener('click', function(e) {
    const card = e.target.closest('.js-video-card');
    if (!card) return;
    openVideoModal(card.dataset.videoUrl, card.dataset.videoTitle || 'Vidéo');
});

// Show all videos
function showAllVideos() {
    const hidden = document.getElementById('hidden-videos');
    const btn = document.getElementById('toggle-videos');
    if (hidden && hidden.style.display === 'none') {
        hidden.style.display = 'block';
        btn.querySelector('span').textContent = 'Voir moins';
    } else if (hidden) {
        hidden.style.display = 'none';
        btn.querySelector('span').textContent = 'Voir les {{ $prestataire->videos ? $prestataire->videos->count() - 6 : 0 }} autres vidéos';
    }
}

// Close video modal on escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeVideoModal();
    }
});
</script>
@endsection
