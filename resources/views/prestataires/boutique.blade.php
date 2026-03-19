@extends('layouts.app')

@section('title', 'Boutique - ' . $prestataire->user->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/prestataire-profile.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="prestataire-profile-page">
    <div class="container mx-auto px-4 pt-4">
        <a href="{{ route('prestataires.show', $prestataire) }}"
           class="inline-flex items-center px-3 py-2 bg-white rounded-xl shadow-sm border border-blue-200 text-blue-700 hover:bg-blue-50 transition-colors text-sm font-medium">
            <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour au profil
        </a>
    </div>

    <div class="profile-hero">
        <div class="profile-hero-content">
            <div class="profile-header-main">
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

                <div class="profile-header-info">
                    <h1 class="profile-name">{{ $prestataire->user->name }}</h1>
                    <div class="profile-specialty">
                        <i class="fas fa-briefcase"></i>
                        {{ $prestataire->secteur_activite ?? 'Prestataire de services' }}
                    </div>

                    <div class="profile-header-stats">
                        <div class="profile-stat">
                            <span class="profile-stat-value">{{ $counts['services'] ?? 0 }}</span>
                            <span class="profile-stat-label">Services</span>
                        </div>
                        <div class="profile-stat">
                            <span class="profile-stat-value">{{ $counts['boutique'] ?? 0 }}</span>
                            <span class="profile-stat-label">Boutique</span>
                        </div>
                        <div class="profile-stat">
                            <span class="profile-stat-value">{{ $counts['equipements'] ?? 0 }}</span>
                            <span class="profile-stat-label">Équipements</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="profile-content">
        <div class="profile-nav-tabs">
            <a class="profile-nav-tab {{ ($activeTab ?? '') === 'services' ? 'active' : '' }}" href="{{ route('prestataires.services', $prestataire) }}">
                <i class="fas fa-concierge-bell"></i>
                Services
                <span class="tab-count">{{ $counts['services'] ?? 0 }}</span>
            </a>
            <a class="profile-nav-tab {{ ($activeTab ?? '') === 'boutique' ? 'active' : '' }}" href="{{ route('prestataires.boutique', $prestataire) }}">
                <i class="fas fa-store"></i>
                Boutique
                <span class="tab-count">{{ $counts['boutique'] ?? 0 }}</span>
            </a>
            <a class="profile-nav-tab {{ ($activeTab ?? '') === 'equipements' ? 'active' : '' }}" href="{{ route('prestataires.equipements', $prestataire) }}">
                <i class="fas fa-tools"></i>
                Équipements
                <span class="tab-count">{{ $counts['equipements'] ?? 0 }}</span>
            </a>
        </div>

        <div class="profile-section boutique-style">
            <div class="profile-section-header">
                <h2 class="profile-section-title">
                    <div class="profile-section-title-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    Boutique
                </h2>
                <span class="profile-section-badge">{{ $urgentSales->total() }}</span>
            </div>

            <div class="profile-section-body">
                @if($urgentSales->count() > 0)
                    <div class="profile-items-grid">
                        @foreach($urgentSales as $sale)
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
                                        <div class="profile-item-placeholder">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 9h14l-2-9M10 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"></path>
                                            </svg>
                                            <span>Pas d'image</span>
                                        </div>
                                    @endif

                                    @if($sale->price)
                                        <div class="profile-item-price">{{ number_format($sale->price, 2, ',', ' ') }} €</div>
                                    @endif
                                </div>

                                <div class="profile-item-content">
                                    <h3 class="profile-item-title">{{ $sale->title }}</h3>
                                    <p class="profile-item-desc">{{ Str::limit($sale->description, 100) }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $urgentSales->links() }}
                    </div>
                @else
                    <div class="profile-empty">
                        <div class="profile-empty-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 9h14l-2-9"></path>
                            </svg>
                        </div>
                        <h3>Aucune annonce</h3>
                        <p>Ce prestataire n'a pas encore publié d'articles dans sa boutique.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
