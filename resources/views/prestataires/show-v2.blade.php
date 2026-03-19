@extends('layouts.app')

@section('title', $prestataire->user->name . ' - Prestataire')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/prestataire-profile.css') }}">
@endpush

@section('content')
<div class="prestataire-profile-page">
    
    {{-- HERO HEADER --}}
    <div class="profile-hero">
        <div class="profile-hero-content">
            <div class="profile-header-main">
                {{-- Avatar --}}
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
                
                {{-- Infos principales --}}
                <div class="profile-header-info">
                    <h1 class="profile-name">{{ $prestataire->user->name }}</h1>
                    
                    <div class="profile-specialty">
                        <i class="fas fa-briefcase"></i>
                        {{ $prestataire->secteur_activite ?? 'Prestataire de services' }}
                    </div>
                    
                    {{-- Stats --}}
                    <div class="profile-header-stats">
                        <div class="profile-stat">
                            <span class="profile-stat-value">{{ $allServices->count() }}</span>
                            <span class="profile-stat-label">Services</span>
                        </div>
                        <div class="profile-stat">
                            <span class="profile-stat-value">{{ $allEquipments->count() }}</span>
                            <span class="profile-stat-label">Équipements</span>
                        </div>
                        @php
                            $totalReviews = $allReviews->count();
                            $averageRating = $totalReviews > 0 ? round($allReviews->avg('rating'), 1) : 0;
                        @endphp
                        <div class="profile-stat">
                            <span class="profile-stat-value">{{ $averageRating }}<small>/5</small></span>
                            <span class="profile-stat-label">{{ $totalReviews }} avis</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- CARTE DE CONTACT FLOTTANTE --}}
    <div class="profile-contact-card">
        <div class="profile-contact-grid">
            {{-- Téléphone --}}
            @if($prestataire->phone)
            <div class="profile-contact-item">
                <div class="profile-contact-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="profile-contact-info">
                    <div class="profile-contact-label">Téléphone</div>
                    <div class="profile-contact-value">{{ $prestataire->phone }}</div>
                </div>
            </div>
            @endif
            
            {{-- Localisation --}}
            @if($prestataire->city || $prestataire->address)
            <div class="profile-contact-item">
                <div class="profile-contact-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="profile-contact-info">
                    <div class="profile-contact-label">Localisation</div>
                    <div class="profile-contact-value">{{ $prestataire->city ?? $prestataire->address }}</div>
                </div>
            </div>
            @endif
            
            {{-- Rating --}}
            <div class="profile-rating-section" onclick="scrollToReviews()">
                @if($totalReviews > 0)
                    <div class="profile-rating-value">{{ $averageRating }}</div>
                    <div class="profile-rating-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <svg fill="{{ $i <= round($averageRating) ? '#f59e0b' : '#d1d5db' }}" viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                            </svg>
                        @endfor
                    </div>
                    <div class="profile-rating-count">{{ $totalReviews }} avis clients</div>
                @else
                    <div class="profile-rating-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <svg fill="#d1d5db" viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path>
                            </svg>
                        @endfor
                    </div>
                    <div class="profile-rating-count">Pas encore d'avis</div>
                @endif
            </div>
            
            {{-- Boutons d'action --}}
            <div class="profile-actions">
                @auth
                    @if(auth()->user()->isClient())
                        <a href="{{ route('client.messaging.start', $prestataire) }}" class="profile-btn profile-btn-primary">
                            <i class="fas fa-comment-dots"></i>
                            Contacter
                        </a>
                        
                        @if(auth()->user()->client && auth()->user()->client->isFollowing($prestataire->id))
                            <form action="{{ route('client.prestataire-follows.unfollow', $prestataire) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="profile-btn profile-btn-danger">
                                    <i class="fas fa-heart"></i>
                                    Abonné(e)
                                </button>
                            </form>
                        @else
                            <form action="{{ route('client.prestataire-follows.follow', $prestataire) }}" method="POST">
                                @csrf
                                <button type="submit" class="profile-btn profile-btn-secondary">
                                    <i class="far fa-heart"></i>
                                    S'abonner
                                </button>
                            </form>
                        @endif
                    @endif
                @else
                    <a href="{{ route('login') }}" class="profile-btn profile-btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Connectez-vous pour contacter
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
                            <button class="profile-load-more-btn" onclick="toggleServices()" id="toggle-services">
                                <span>Voir les {{ $allServices->count() - 8 }} autres services</span>
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
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
                                        <div class="profile-item-price" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">{{ number_format($equipment->price_per_day, 0, ',', ' ') }} €/jour</div>
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
                            <button class="profile-load-more-btn" onclick="toggleEquipments()" id="toggle-equipments" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <span>Voir les {{ $allEquipments->count() - 8 }} autres équipements</span>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <h3>Aucun équipement disponible</h3>
                        <p>Ce prestataire n'a pas d'équipements à louer pour le moment.</p>
                    </div>
                @endif
            </div>
        </div>
        
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
                            <span style="font-size: 0.85rem; color: #92400e;">{{ $totalReviews }} avis</span>
                        </div>
                    </div>
                @endif
            </div>
            <div class="profile-section-body">
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
                            <button class="profile-load-more-btn" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
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
</script>
@endsection
