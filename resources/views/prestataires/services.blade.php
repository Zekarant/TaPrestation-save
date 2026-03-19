@extends('layouts.app')

@section('title', 'Services - ' . $prestataire->user->name)

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

        <div class="profile-section">
            <div class="profile-section-header">
                <h2 class="profile-section-title">
                    <div class="profile-section-title-icon">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    Services proposés
                </h2>
                <span class="profile-section-badge">{{ $services->total() }}</span>
            </div>

            <div class="profile-section-body">
                @if($services->count() > 0)
                    <div class="profile-items-grid">
                        @foreach($services as $service)
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
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $services->links() }}
                    </div>
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
    </div>
</div>
@endsection
