@extends('layouts.app')
 
@push('styles')
<link rel="stylesheet" href="{{ asset('css/client-dashboard-v2.css') }}">
@endpush
 
@section('content')
<div class="client-dashboard" x-data="clientDashboard()">
    <section class="dashboard-hero">
        <div class="hero-background"><div class="hero-pattern"></div></div>
        <div class="hero-content">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="hero-grid">
                    {{-- Profil --}}
                    <div class="user-profile-card">
                        <div class="avatar-container">
                            @if($client?->avatar)
                                <img src="{{ asset('storage/' . $client->avatar) }}" alt="{{ auth()->user()->name }}" class="avatar-image">
                            @elseif(auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="avatar-image">
                            @else
                                <div class="avatar-placeholder">
                                    <span>{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                                </div>
                            @endif
                            <div class="avatar-badge">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </div>
                        </div>
                        <div class="user-info">
                            <h1 class="user-name">{{ auth()->user()->name }}</h1>
                            <p class="user-role">
                                <span class="role-badge">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if(isset($isPrestataire) && $isPrestataire)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        @endif
                                    </svg>
                                    {{ isset($isPrestataire) && $isPrestataire ? 'Mode Client' : 'Client' }}
                                </span>
                                @if(isset($isDriverPresta) && $isDriverPresta)
                                    <span class="role-badge" style="background:rgba(16,185,129,.3);">🚴 Livreur</span>
                                @endif
                            </p>
                            <p class="user-since">Membre depuis {{ auth()->user()->created_at->translatedFormat('F Y') }}</p>
                        </div>
                        <a href="{{ route('client.profile.edit') }}" class="edit-profile-btn">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Profil
                        </a>
                        @if(isset($isPrestataire) && $isPrestataire)
                            <a href="{{ route('prestataire.dashboard') }}" style="margin-left:4px;display:inline-flex;align-items:center;padding:5px 10px;background:rgba(139,92,246,.85);color:#fff;font-size:11px;font-weight:700;border-radius:8px;text-decoration:none;">
                                ⚡ Prestataire
                            </a>
                        @endif
                    </div>
 
                    {{-- Stats — 8 cards compactes --}}
                    <div class="quick-stats">
                        <a href="{{ route('client.bookings.index') }}" class="stat-card stat-bookings">
                            <div class="stat-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                            <div class="stat-content"><span class="stat-value">{{ $recentBookings->count() }}</span><span class="stat-label">Réservations</span></div>
                            <div class="stat-arrow"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></div>
                        </a>
 
                        <a href="{{ route('messaging.index') }}" class="stat-card stat-messages">
                            <div class="stat-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></div>
                            <div class="stat-content"><span class="stat-value">{{ $unreadMessages ?? 0 }}</span><span class="stat-label">Messages</span></div>
                            @if(($unreadMessages ?? 0) > 0)<div class="stat-badge pulse">{{ $unreadMessages }}</div>@endif
                            <div class="stat-arrow"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></div>
                        </a>
 
                        <a href="{{ route('client.prestataire-follows.index') }}" class="stat-card stat-follows">
                            <div class="stat-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg></div>
                            <div class="stat-content"><span class="stat-value">{{ $recentFollowedPrestataires->count() }}</span><span class="stat-label">Suivis</span></div>
                            <div class="stat-arrow"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></div>
                        </a>
 
                        <a href="{{ route('client.equipment-rental-requests.index') }}" class="stat-card stat-equipment">
                            <div class="stat-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                            <div class="stat-content"><span class="stat-value">{{ $recentRentalRequests->count() }}</span><span class="stat-label">Locations</span></div>
                            <div class="stat-arrow"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></div>
                        </a>
 
                        <a href="{{ route('food.orders') }}" class="stat-card stat-food" style="background:linear-gradient(135deg,#ea580c,#f97316);">
                            <div class="stat-icon" style="background:rgba(255,255,255,.2);"><span style="font-size:18px;">🍽️</span></div>
                            <div class="stat-content"><span class="stat-value" style="color:#fff;">{{ $foodOrdersCount ?? 0 }}</span><span class="stat-label" style="color:rgba(255,255,255,.75);">Food</span></div>
                            <div class="stat-arrow" style="color:#fff;"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></div>
                        </a>
 
                        <a href="{{ route('client.escrow.index') }}" class="stat-card stat-escrow" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
                            <div class="stat-icon" style="background:rgba(255,255,255,.2);"><svg style="width:18px;height:18px;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div>
                            <div class="stat-content"><span class="stat-value" style="color:#fff;">{{ $escrowPendingCount ?? 0 }}</span><span class="stat-label" style="color:rgba(255,255,255,.75);">Paiements</span></div>
                            @if(($escrowPendingCount ?? 0) > 0)<div class="stat-badge pulse" style="background:#fbbf24;">{{ $escrowPendingCount }}</div>@endif
                            <div class="stat-arrow" style="color:#fff;"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></div>
                        </a>
 
                        @php
                            $driverProfiles = \App\Models\DeliveryDriver::query()->select(['id','is_internal','employer_prestataire_id'])->where('user_id', auth()->id())->get();
                            $externalDriverProfile = $driverProfiles->first(fn($d) => !((bool)($d->is_internal ?? false) && !empty($d->employer_prestataire_id)));
                            $hasExternalDriver = (bool) $externalDriverProfile;
                            $driverSpaceUrl = $hasExternalDriver ? route('driver.dashboard') : route('driver.register');
                            $driverSpaceValue = $hasExternalDriver ? 'Go' : '+';
                            $driverSpaceLabel = $hasExternalDriver ? 'Livreur' : 'Livrer';
                        @endphp
                        <a href="{{ $driverSpaceUrl }}" class="stat-card stat-driver" style="background:linear-gradient(135deg,#10b981,#059669);">
                            <div class="stat-icon" style="background:rgba(255,255,255,.2);"><span style="font-size:18px;">🚴</span></div>
                            <div class="stat-content"><span class="stat-value" style="color:#fff;">{{ $driverSpaceValue }}</span><span class="stat-label" style="color:rgba(255,255,255,.75);">{{ $driverSpaceLabel }}</span></div>
                            <div class="stat-arrow" style="color:#fff;"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></div>
                        </a>
 
                        @php
                            $myUrgentSalesCount = 0; $maxClientListings = 5;
                            try { $myUrgentSalesCount = \App\Models\UrgentSale::where('user_id', auth()->id())->whereNull('prestataire_id')->where('status','!=','sold')->count(); } catch (\Exception $e) {}
                        @endphp
                        <a href="{{ route('client.my-urgent-sales.index') }}" class="stat-card stat-urgent-sales" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                            <div class="stat-icon" style="background:rgba(255,255,255,.2);"><span style="font-size:18px;">⚡</span></div>
                            <div class="stat-content"><span class="stat-value" style="color:#fff;">{{ $myUrgentSalesCount }}/{{ $maxClientListings }}</span><span class="stat-label" style="color:rgba(255,255,255,.75);">Annonces</span></div>
                            @if($myUrgentSalesCount >= $maxClientListings)<div class="stat-badge" style="background:#ef4444;">Max</div>@endif
                            <div class="stat-arrow" style="color:#fff;"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
 
    @if(session('success') || session('error'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        @if(session('success'))
        <div class="alert alert-success" x-data="dismissibleAlert(5000)" x-show="show" x-transition>
            <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
            <button @click="show = false" class="alert-close"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-error" x-data="dismissibleAlert(5000)" x-show="show" x-transition>
            <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
            <button @click="show = false" class="alert-close"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        @endif
    </div>
    @endif
 
    @php $hasBookings = ($recentBookings ?? collect())->count() > 0 || ($recentRentalRequests ?? collect())->count() > 0; @endphp
    @if(!$hasBookings)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="welcome-guide" x-data="persistentVisibility('hide-welcome-guide')" x-show="visible" x-transition>
            <div class="welcome-header">
                <div class="welcome-icon"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
                <div class="welcome-text"><h3>Bienvenue ! 🎉</h3><p>Commencez en quelques étapes simples</p></div>
                <button @click="dismiss()" class="welcome-close"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="welcome-steps">
                <a href="{{ route('prestataires.index') }}" class="welcome-step"><div class="step-number">1</div><div class="step-content"><h4>Explorez</h4><p>Découvrez les prestataires</p></div><svg class="step-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
                <a href="{{ route('services.index') }}" class="welcome-step"><div class="step-number">2</div><div class="step-content"><h4>Choisissez</h4><p>Trouvez le service idéal</p></div><svg class="step-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
                <div class="welcome-step disabled"><div class="step-number">3</div><div class="step-content"><h4>Réservez</h4><p>Confirmez votre RDV</p></div><svg class="step-check" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>
            </div>
        </div>
    </div>
    @endif
 
    <section class="quick-actions-section">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="section-title"><svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>Actions rapides</h2>
            <div class="quick-actions-grid">
                <a href="{{ route('client.tenders.index') }}" class="quick-action-card tender-card"><div class="action-icon tender-icon"><i class="fas fa-bullhorn"></i></div><div class="action-content"><h3>Appels d'Offres</h3><p>Publiez vos besoins</p></div><svg class="action-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
                <a href="{{ route('client.tenders.create') }}" class="quick-action-card new-tender-card"><div class="action-icon new-tender-icon"><i class="fas fa-plus-circle"></i></div><div class="action-content"><h3>Nouvel Appel d'Offre</h3><p>Trouvez le prestataire idéal</p></div><svg class="action-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
                <a href="{{ route('client.invoices.index') }}" class="quick-action-card"><div class="action-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;"><i class="fas fa-file-invoice"></i></div><div class="action-content"><h3>Mes Factures</h3><p>Consultez et téléchargez</p></div><svg class="action-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
                @foreach($shortcuts as $shortcut)
                <a href="{{ $shortcut['url'] }}" class="quick-action-card"><div class="action-icon"><i class="{{ $shortcut['icon'] }}"></i></div><div class="action-content"><h3>{{ $shortcut['name'] }}</h3><p>{{ $shortcut['description'] }}</p></div><svg class="action-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
                @endforeach
            </div>
        </div>
    </section>
 
    @php $prest = auth()->user()->prestataire ?? null; @endphp
    @if(!$prest || !$prest->is_active)
    <section class="become-provider-section">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="become-provider-banner">
                <div class="banner-content"><div class="banner-icon"><i class="fas fa-rocket"></i></div><div class="banner-text"><h3>Vous proposez des services ?</h3><p>Rejoignez notre communauté de prestataires</p></div></div>
                <div class="banner-actions">
                    <a href="{{ route('client.become-provider.benefits') }}" class="btn-learn-more"><i class="fas fa-info-circle"></i> En savoir plus</a>
                    @if(!$prest)
                        <a href="{{ route('client.become-provider.index') }}" class="btn-become-provider"><i class="fas fa-user-plus"></i> Devenir Prestataire</a>
                    @else
                        <form action="{{ route('client.become-provider.reactivate') }}" method="POST" class="inline">@csrf<button type="submit" class="btn-become-provider"><i class="fas fa-power-off"></i> Réactiver</button></form>
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif
 
    <section class="main-content">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="content-grid">
                <div class="main-column">
                    <div class="tabs-container" x-data="{ activeTab: 'requests' }">
                        <div class="tabs-header">
                            <button class="tab-btn" :class="{ 'active': activeTab === 'requests' }" @click="activeTab = 'requests'">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <span class="hidden sm:inline">Mes demandes</span><span class="sm:hidden">Demandes</span>
                                @if($unifiedRequests->count() > 0)<span class="tab-badge">{{ $unifiedRequests->count() }}</span>@endif
                            </button>
                            <button class="tab-btn" :class="{ 'active': activeTab === 'activity' }" @click="activeTab = 'activity'">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                <span class="hidden sm:inline">Activités</span><span class="sm:hidden">Actus</span>
                            </button>
                        </div>
 
                        <div class="tab-panel" x-show="activeTab === 'requests'" x-transition>
                            @if($unifiedRequests->isEmpty())
                                <div class="empty-state">
                                    <div class="empty-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
                                    <h3>Aucune demande</h3><p>Explorez nos services et réservez</p>
                                    <a href="{{ route('prestataires.index') }}" class="empty-action"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>Rechercher</a>
                                </div>
                            @else
                                <div class="requests-list">
                                    @foreach($unifiedRequests as $request)
                                    <a href="@if($request['type']==='service'){{ route('client.bookings.show', $request['id']) }}@elseif($request['type']==='equipment'){{ route('client.equipment-rental-requests.show', $request['id']) }}@else{{ route('urgent-sales.show', $request['id']) }}@endif" class="request-card">
                                        <div class="request-image">
                                            @if(isset($request['image']) && $request['image'])<img src="{{ asset('storage/' . $request['image']) }}" alt="{{ $request['title'] }}">
                                            @else<div class="request-placeholder {{ $request['type']==='service' ? 'type-service' : 'type-equipment' }}">@if($request['type']==='service')<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>@else<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>@endif</div>@endif
                                        </div>
                                        <div class="request-content">
                                            <div class="request-header">
                                                <span class="request-type {{ $request['type'] }}">@if($request['type']==='service')Service @elseif($request['type']==='equipment')Matériel @else Vente @endif</span>
                                                <span class="request-status status-{{ $request['status'] }}">@switch($request['status'])@case('pending')En attente @break @case('confirmed')Confirmé @break @case('approved')Approuvé @break @case('completed')Terminé @break @case('active')En cours @break @case('cancelled')Annulé @break @case('rejected')Rejeté @break @case('responded')Répondu @break @default {{ ucfirst($request['status']) }} @endswitch</span>
                                            </div>
                                            <h4 class="request-title">{{ $request['title'] }}</h4>
                                            <p class="request-provider">{{ $request['prestataire'] }}</p>
                                            <p class="request-date"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>{{ $request['date']->format('d/m/Y à H:i') }}</p>
                                        </div>
                                        <svg class="request-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                    @endforeach
                                </div>
                                <div class="view-all-link"><a href="{{ route('client.bookings.index') }}">Voir toutes mes demandes <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a></div>
                            @endif
                        </div>
 
                        <div class="tab-panel" x-show="activeTab === 'activity'" x-transition>
                            @if($recentServicesFromFollowed->isEmpty())
                                <div class="empty-state">
                                    <div class="empty-icon green"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg></div>
                                    <h3>Aucune activité récente</h3><p>Suivez des prestataires pour voir leurs actus</p>
                                    <a href="{{ route('prestataires.index') }}" class="empty-action green"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>Explorer</a>
                                </div>
                            @else
                                <div class="activity-feed">
                                    @foreach($recentServicesFromFollowed as $service)
                                    <div class="activity-item">
                                        <div class="activity-avatar">
                                            @if($service->prestataire->photo)<img src="{{ asset('storage/' . $service->prestataire->photo) }}" alt="{{ $service->prestataire->user->name ?? 'Prestataire' }}">
                                            @elseif($service->prestataire->user?->avatar)<img src="{{ asset('storage/' . $service->prestataire->user->avatar) }}" alt="{{ $service->prestataire->user->name ?? 'Prestataire' }}">
                                            @else<div class="avatar-fallback small">{{ ($service->prestataire->user?->name) ? substr($service->prestataire->user->name, 0, 1) : 'P' }}</div>@endif
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-header"><span class="activity-author">{{ $service->prestataire->user->name ?? 'Prestataire' }}</span><span class="activity-action">nouveau service</span><span class="activity-time">{{ $service->created_at->diffForHumans() }}</span></div>
                                            <a href="{{ route('services.show', $service->id) }}" class="activity-card">
                                                @if($service->image)<img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->title }}" class="activity-image">@endif
                                                <div class="activity-details"><h4>{{ $service->title }}</h4><p>{{ Str::limit($service->description, 80) }}</p>@if($service->price)<span class="activity-price">{{ number_format($service->price, 2) }} €</span>@endif</div>
                                            </a>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
 
                <aside class="sidebar">
                    <div class="sidebar-card help-card">
                        <div class="sidebar-header"><svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Besoin d'aide ?</div>
                        <div class="help-links">
                            <a href="{{ route('client.help.index') }}" class="help-link"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>Centre d'aide</a>
                            <a href="{{ route('messaging.index') }}" class="help-link"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>Nous contacter</a>
                            <a href="{{ route('privacy') }}" class="help-link"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>Confidentialité</a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</div>
@endsection
 
@push('scripts')
<script>
function clientDashboard() {
    return {
        init() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('animate-in'); });
            }, { threshold: 0.1 });
            document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
        }
    }
}
</script>
@endpush