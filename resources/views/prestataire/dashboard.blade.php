@extends('layouts.app')
@use('Illuminate\Support\Facades\Storage')
@section('title', 'Tableau de bord - Prestataire')

@push('styles')
<style>
    /* Dashboard moderne - Effets visuels améliorés */
    .dashboard-gradient-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        position: relative;
        overflow: hidden;
    }
    
    .dashboard-gradient-bg::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 50%);
        animation: shimmer 15s linear infinite;
    }
    
    @keyframes shimmer {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Cartes statistiques avec glassmorphism */
    .stat-card-glass {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    .stat-card-glass:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(31, 38, 135, 0.2);
    }
    
    .stat-card-glass .stat-icon {
        transition: all 0.3s ease;
    }
    
    .stat-card-glass:hover .stat-icon {
        transform: scale(1.15) rotate(5deg);
    }
    
    /* Cartes principales avec effet 3D */
    .main-card-3d {
        background: white;
        border-radius: 20px;
        box-shadow: 
            0 10px 40px rgba(0,0,0,0.08),
            0 0 0 1px rgba(0,0,0,0.02);
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
    }
    
    .main-card-3d::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--card-accent, #6366f1), var(--card-accent-end, #8b5cf6));
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .main-card-3d:hover::before {
        opacity: 1;
    }
    
    .main-card-3d:hover {
        transform: translateY(-5px);
        box-shadow: 
            0 25px 50px rgba(0,0,0,0.12),
            0 0 0 1px rgba(0,0,0,0.02);
    }
    
    /* Couleurs accent par carte */
    .main-card-3d.blue { --card-accent: #3b82f6; --card-accent-end: #60a5fa; }
    .main-card-3d.green { --card-accent: #10b981; --card-accent-end: #34d399; }
    .main-card-3d.red { --card-accent: #ef4444; --card-accent-end: #f87171; }
    .main-card-3d.purple { --card-accent: #8b5cf6; --card-accent-end: #a78bfa; }
    .main-card-3d.indigo { --card-accent: #6366f1; --card-accent-end: #818cf8; }
    .main-card-3d.orange { --card-accent: #f97316; --card-accent-end: #fb923c; }
    
    /* Boutons modernes avec gradient */
    .btn-gradient {
        background: linear-gradient(135deg, var(--btn-from) 0%, var(--btn-to) 100%);
        border: none;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .btn-gradient::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s ease;
    }
    
    .btn-gradient:hover::before {
        left: 100%;
    }
    
    .btn-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .btn-gradient.blue { --btn-from: #3b82f6; --btn-to: #2563eb; }
    .btn-gradient.green { --btn-from: #10b981; --btn-to: #059669; }
    .btn-gradient.red { --btn-from: #ef4444; --btn-to: #dc2626; }
    .btn-gradient.purple { --btn-from: #8b5cf6; --btn-to: #7c3aed; }
    .btn-gradient.orange { --btn-from: #f97316; --btn-to: #ea580c; }
    
    /* Animation pulse pour les badges */
    .badge-pulse {
        animation: pulse-badge 2s ease-in-out infinite;
    }
    
    @keyframes pulse-badge {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        50% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
    }
    
    /* Progress bar animée */
    .progress-animated {
        position: relative;
        overflow: hidden;
    }
    
    .progress-animated::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        animation: progress-shine 2s ease-in-out infinite;
    }
    
    @keyframes progress-shine {
        0% { left: -100%; }
        100% { left: 100%; }
    }
    
    /* Icônes avec fond gradient */
    .icon-gradient {
        background: linear-gradient(135deg, var(--icon-from) 0%, var(--icon-to) 100%);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .icon-gradient.purple { --icon-from: #c084fc; --icon-to: #a855f7; }
    .icon-gradient.blue { --icon-from: #60a5fa; --icon-to: #3b82f6; }
    .icon-gradient.green { --icon-from: #4ade80; --icon-to: #22c55e; }
    .icon-gradient.red { --icon-from: #fb7185; --icon-to: #ef4444; }
    .icon-gradient.cyan { --icon-from: #22d3ee; --icon-to: #06b6d4; }
    .icon-gradient.emerald { --icon-from: #34d399; --icon-to: #10b981; }
    .icon-gradient.indigo { --icon-from: #818cf8; --icon-to: #6366f1; }
    .icon-gradient.yellow { --icon-from: #fde047; --icon-to: #eab308; }
    .icon-gradient.orange { --icon-from: #fb923c; --icon-to: #f97316; }
    
    /* Titre avec gradient text */
    .title-gradient {
        background: linear-gradient(135deg, #1e293b 0%, #475569 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    /* Section header amélioré */
    .section-header {
        position: relative;
        padding-bottom: 12px;
    }
    
    .section-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
        border-radius: 3px;
    }
    
    /* Hover effect pour les liens */
    .link-hover-effect {
        position: relative;
    }
    
    .link-hover-effect::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
        transition: width 0.3s ease;
    }
    
    .link-hover-effect:hover::after {
        width: 100%;
    }
    
    /* Card avec bordure gradient */
    .card-gradient-border {
        position: relative;
        background: white;
        border-radius: 16px;
    }
    
    .card-gradient-border::before {
        content: '';
        position: absolute;
        inset: -2px;
        background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
        border-radius: 18px;
        z-index: -1;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .card-gradient-border:hover::before {
        opacity: 1;
    }
    
    /* Floating animation */
    .float-animation {
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-5px); }
    }
    
    /* Responsive améliorations */
    @media (max-width: 640px) {
        .stat-card-glass {
            padding: 12px 8px;
        }
        
        .main-card-3d {
            border-radius: 16px;
        }
    }
    
    /* ============================================
       CRITICAL: Very Small Screens (< 400px)
       Supports Galaxy S9+, iPhone SE, etc.
       ============================================ */
    @media (max-width: 400px) {
        /* Reduce main container padding */
        .px-2 {
            padding-left: 0.375rem !important;
            padding-right: 0.375rem !important;
        }
        
        /* Stats cards - more compact on very small screens */
        .stat-card-glass {
            padding: 0.5rem 0.375rem !important;
            border-radius: 0.75rem !important;
        }
        
        .stat-card-glass .stat-icon {
            width: 2rem !important;
            height: 2rem !important;
            margin-bottom: 0.25rem !important;
            border-radius: 0.5rem !important;
        }
        
        .stat-card-glass .stat-icon svg,
        .stat-card-glass .stat-icon span {
            width: 1rem !important;
            height: 1rem !important;
            font-size: 0.875rem !important;
        }
        
        .stat-card-glass > div:nth-child(2) {
            font-size: 0.875rem !important;
        }
        
        .stat-card-glass > div:nth-child(3) {
            font-size: 0.5625rem !important;
        }
        
        /* Main cards - make more compact */
        .main-card-3d {
            padding: 0.75rem !important;
            min-height: 180px !important;
            border-radius: 0.875rem !important;
        }
        
        .main-card-3d .flex.items-center.mb-4 {
            margin-bottom: 0.5rem !important;
        }
        
        .main-card-3d .w-12.h-12,
        .main-card-3d .sm\\:w-14.sm\\:h-14 {
            width: 2.25rem !important;
            height: 2.25rem !important;
        }
        
        .main-card-3d h3 {
            font-size: 0.8125rem !important;
        }
        
        .main-card-3d p {
            font-size: 0.625rem !important;
        }
        
        .main-card-3d .grid.grid-cols-2.gap-3 {
            gap: 0.375rem !important;
        }
        
        .main-card-3d .text-center.rounded-xl.p-3,
        .main-card-3d .text-center.rounded-xl.p-2 {
            padding: 0.375rem !important;
            border-radius: 0.5rem !important;
        }
        
        .main-card-3d .text-xl,
        .main-card-3d .sm\\:text-2xl {
            font-size: 0.9375rem !important;
        }
        
        .main-card-3d .text-xs.font-medium {
            font-size: 0.5625rem !important;
        }
        
        /* Buttons more compact */
        .btn-gradient {
            padding: 0.5rem 0.625rem !important;
            font-size: 0.6875rem !important;
        }
        
        .btn-gradient svg {
            width: 0.875rem !important;
            height: 0.875rem !important;
            margin-right: 0.25rem !important;
        }
        
        /* Grid adjustments for tenders section */
        .grid.grid-cols-4 {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 0.375rem !important;
        }
        
        /* Secondary dashboard cards */
        .dashboard-primary-card {
            padding: 0.625rem !important;
            min-height: 220px !important;
        }
        
        /* Welcome header */
        h1.text-xl {
            font-size: 1.0625rem !important;
        }
        
        p.text-base {
            font-size: 0.75rem !important;
        }
    }
    
    /* Small screens between 400-640px */
    @media (min-width: 401px) and (max-width: 640px) {
        .stat-card-glass {
            padding: 0.625rem !important;
        }
        
        .stat-card-glass .stat-icon {
            width: 2.25rem !important;
            height: 2.25rem !important;
        }
        
        .stat-card-glass .stat-icon svg {
            width: 1.125rem !important;
            height: 1.125rem !important;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6 py-4 sm:py-6">
        {{-- Guide de démarrage interactif pour les nouveaux prestataires --}}
        @php
            $hasServices = ($totalServices ?? 0) > 0;
            $hasEquipment = ($equipmentCount ?? 0) > 0;
            $hasInventory = ($inventoryCount ?? 0) > 0;
            $hasProfilePhoto = auth()->user()->profile_photo_path || auth()->user()->avatar;
            $hasQrViews = false; // À connecter avec les vraies stats
            $hasAvailability = isset($weeklyAvailability) && $weeklyAvailability && $weeklyAvailability->count() > 0;
            $hasUrgentSale = ($urgentProductsCount ?? 0) > 0;
            $hasPaymentsSetup = !empty($prestataire?->stripe_account_id);
            $hasFood = ($foodOrdersCount ?? 0) > 0;

            $isNewUser = (!$hasServices && !$hasEquipment && !$hasUrgentSale) || (($profileCompletion['percentage'] ?? 0) < 60);
            
            $onboardingSteps = [
                [
                    'key' => 'profile',
                    'title' => 'Compléter votre profil',
                    'description' => 'Photo, description, catégories et contact',
                    'completed' => $hasProfilePhoto,
                    'url' => route('prestataire.profile.edit')
                ],
                [
                    'key' => 'service',
                    'title' => 'Créer une prestation',
                    'description' => 'Événementiel, esthétique, travaux… + réservation',
                    'completed' => $hasServices,
                    'url' => route('prestataire.services.create')
                ],
                [
                    'key' => 'planning',
                    'title' => 'Activer le planning',
                    'description' => 'Horaires + disponibilités pour automatiser les réservations',
                    'completed' => $hasAvailability,
                    'url' => route('prestataire.availability.index')
                ],
                [
                    'key' => 'equipment',
                    'title' => 'Ajouter du matériel à louer',
                    'description' => 'Location + disponibilité + demandes',
                    'completed' => $hasEquipment,
                    'url' => route('prestataire.equipment.create')
                ],
                [
                    'key' => 'shop',
                    'title' => 'Créer une annonce (boutique)',
                    'description' => 'Produits + stock + inventaire',
                    'completed' => $hasUrgentSale || $hasInventory,
                    'url' => route('prestataire.urgent-sales.create')
                ],
                [
                    'key' => 'payments',
                    'title' => 'Configurer paiements',
                    'description' => 'Moyens de paiement + acompte/caution (blocage)',
                    'completed' => $hasPaymentsSetup,
                    'url' => (\Illuminate\Support\Facades\Route::has('prestataire.payments.connect') ? route('prestataire.payments.connect') : route('prestataire.payments.index'))
                ],
            ];
        @endphp
        
        {{-- Checklist d'onboarding pour les nouveaux utilisateurs --}}
        @if($isNewUser)
            <x-onboarding-checklist 
                :steps="$onboardingSteps"
                title="🚀 Bienvenue ! Voici quoi faire en premier"
                storageKey="prestataire_onboarding_v2"
            />

            <div class="mb-6">
                <div class="main-card-3d indigo p-4 sm:p-5" style="--card-accent: #3b82f6; --card-accent-end: #10b981;">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="icon-gradient blue w-10 h-10 rounded-2xl flex items-center justify-center text-white shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-base sm:text-lg font-extrabold text-slate-900">Démarrage rapide</div>
                                <div class="text-xs sm:text-sm text-slate-600">Où aller selon ton activité</div>
                            </div>
                        </div>
                        <div class="hidden sm:flex flex-wrap items-center gap-1.5 text-[11px] text-slate-600">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5">Services</span>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5">Matériel</span>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5">Annonces</span>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5">Mon menu</span>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-1 lg:grid-cols-2 gap-2">
                        <div class="rounded-2xl border border-slate-200 bg-linear-to-br from-blue-50 via-white to-white p-3">
                            <div class="text-sm font-bold text-slate-900">Services</div>
                            <div class="text-xs sm:text-sm text-slate-700">Si tu vends une prestation (coiffeuse, traiteur, décoratrice…), utilise <span class="font-semibold">Mes services</span>.</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-linear-to-br from-emerald-50 via-white to-white p-3">
                            <div class="text-sm font-bold text-slate-900">Matériel</div>
                            <div class="text-xs sm:text-sm text-slate-700">Si tu as du matériel à mettre en location, utilise <span class="font-semibold">Mon matériel</span>.</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-linear-to-br from-orange-50 via-white to-white p-3">
                            <div class="text-sm font-bold text-slate-900">Annonces</div>
                            <div class="text-xs sm:text-sm text-slate-700">Si tu es vendeur/vendeuse, utilise <span class="font-semibold">Mes annonces</span> (produits).</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-linear-to-br from-amber-50 via-white to-white p-3">
                            <div class="text-sm font-bold text-slate-900">Mon menu</div>
                            <div class="text-xs sm:text-sm text-slate-700">Si tu fais de la restauration rapide sur commande (repas du jour, tiramisu…), utilise <span class="font-semibold">Mon menu</span>.</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Zone de bienvenue - Design amélioré -->
        <div class="mb-6 sm:mb-8">
            <!-- Header avec gradient subtil -->
            <div class="text-center mb-6 sm:mb-8 relative">
                <div class="inline-block">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold title-gradient mb-2 flex flex-col sm:flex-row items-center justify-center gap-2"> 
                        <span>Tableau de bord</span>
                        @if(auth()->user()->is_verified)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-linear-to-r from-orange-400 to-amber-500 text-white shadow-lg badge-pulse mt-2 sm:mt-0">
                                <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Vérifié
                            </span>
                        @endif
                    </h1>
                </div>
                <p class="text-base sm:text-lg text-gray-500 font-medium">Gérez toutes vos activités depuis votre espace personnel</p>
            </div>
            
            <!-- Statistiques rapides - Design moderne avec glassmorphism -->
            <div class="flex justify-center mb-4 sm:mb-6">
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-10 gap-2 sm:gap-3 md:gap-4 max-w-7xl w-full px-1 sm:px-2">
                    <!-- Réservations -->
                    <a href="{{ route('prestataire.bookings.index') }}" class="stat-card-glass rounded-2xl p-3 sm:p-4 text-center group" title="Demandes de réservation reçues de vos clients">
                        <div class="stat-icon flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 icon-gradient purple rounded-xl text-white">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="text-lg sm:text-2xl font-bold text-gray-900">{{ $requestsCount ?? $bookingsCount ?? 0 }}</div>
                        <div class="text-xs sm:text-sm font-medium text-gray-500">Demandes</div>
                    </a>

                    <!-- Inventaire -->
                    <a href="{{ Route::has('prestataire.inventory.index') ? route('prestataire.inventory.index') : '#' }}" class="stat-card-glass rounded-2xl p-3 sm:p-4 text-center group" title="Gérez vos produits en stock">
                        <div class="stat-icon flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 icon-gradient indigo rounded-xl text-white">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div class="text-lg sm:text-2xl font-bold text-gray-900">{{ $inventoryCount ?? 0 }}</div>
                        <div class="text-xs sm:text-sm font-medium text-gray-500">Inventaire</div>
                    </a>

                    <!-- Livraison -->
                    <a href="{{ Route::has('prestataire.delivery.index') ? route('prestataire.delivery.index') : '#' }}" class="stat-card-glass rounded-2xl p-3 sm:p-4 text-center group" title="Suivez vos livraisons">
                        <div class="stat-icon flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 icon-gradient cyan rounded-xl text-white">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                            </svg>
                        </div>
                        <div class="text-lg sm:text-2xl font-bold text-gray-900">{{ $deliveriesCount ?? 0 }}</div>
                        <div class="text-xs sm:text-sm font-medium text-gray-500">Livraisons</div>
                    </a>

                    <!-- Finance -->
                    @if(function_exists('payment_feature_enabled') && payment_feature_enabled())
                    <a href="{{ Route::has('prestataire.payments.index') ? route('prestataire.payments.index') : (Route::has('prestataire.payments.connect') ? route('prestataire.payments.connect') : '#') }}" class="stat-card-glass rounded-2xl p-3 sm:p-4 text-center group" title="Paiements, factures, comptes et escrow">
                        <div class="stat-icon flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 bg-linear-to-br from-emerald-500 to-cyan-600 rounded-xl text-white">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m5 4H6a3 3 0 01-3-3V8a3 3 0 013-3h12a3 3 0 013 3v8a3 3 0 01-3 3z" />
                            </svg>
                        </div>
                        <div class="text-lg sm:text-2xl font-bold text-gray-900">{{ $financeCount ?? 0 }}</div>
                        <div class="text-xs sm:text-sm font-medium text-gray-500">Finance</div>
                    </a>
                    @endif

                    <!-- QR Code -->
                    <a href="{{ route('prestataire.qrcode.show') }}" class="stat-card-glass rounded-2xl p-3 sm:p-4 text-center group" title="Votre QR code personnel">
                        <div class="stat-icon flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 bg-linear-to-br from-gray-700 to-gray-900 rounded-xl text-white">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h2M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                        <div class="text-lg sm:text-2xl font-bold text-gray-900">QR</div>
                        <div class="text-xs sm:text-sm font-medium text-gray-500">Mon Code</div>
                    </a>

                    <!-- Mes Vidéos -->
                    <a href="{{ route('prestataire.videos.create') }}" class="stat-card-glass rounded-2xl p-3 sm:p-4 text-center group" title="Ajoutez des vidéos">
                        <div class="stat-icon flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 icon-gradient red rounded-xl text-white">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="text-lg sm:text-2xl font-bold text-gray-900">+</div>
                        <div class="text-xs sm:text-sm font-medium text-gray-500">Vidéo</div>
                    </a>

                    <!-- Agenda -->
                    <a href="{{ route('prestataire.agenda.index') }}" class="stat-card-glass rounded-2xl p-3 sm:p-4 text-center group" title="Gérez votre emploi du temps">
                        <div class="stat-icon flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 icon-gradient blue rounded-xl text-white">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="text-lg sm:text-2xl font-bold text-gray-900">📅</div>
                        <div class="text-xs sm:text-sm font-medium text-gray-500">Agenda</div>
                    </a>

                    <!-- Disponibilités -->
                    <a href="{{ route('prestataire.availability.index') }}" class="stat-card-glass rounded-2xl p-3 sm:p-4 text-center group" title="Gérez vos horaires">
                        <div class="stat-icon flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 icon-gradient emerald rounded-xl text-white">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-lg sm:text-2xl font-bold text-gray-900">
                            @php $activeDaysCount = isset($weeklyAvailability) ? $weeklyAvailability->where('is_active', true)->count() : 0; @endphp
                            {{ $activeDaysCount }}/7
                        </div>
                        <div class="text-xs sm:text-sm font-medium text-gray-500">Dispo</div>
                    </a>
                    
                    <!-- Devis -->
                    <a href="{{ route('prestataire.quotes.index') }}" class="stat-card-glass rounded-2xl p-3 sm:p-4 text-center group" title="Créez et envoyez des devis">
                        <div class="stat-icon flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 bg-linear-to-br from-violet-500 to-purple-600 rounded-xl text-white">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="text-lg sm:text-2xl font-bold text-gray-900">📄</div>
                        <div class="text-xs sm:text-sm font-medium text-gray-500">Devis</div>
                    </a>

                    <!-- Food Orders -->
                    <a href="{{ route('prestataire.food-orders.dashboard') }}" class="stat-card-glass rounded-2xl p-3 sm:p-4 text-center group" title="Gérez vos commandes food">
                        <div class="stat-icon flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 bg-linear-to-br from-orange-500 to-amber-500 rounded-xl text-white">
                            <span class="text-xl sm:text-2xl">🍽️</span>
                        </div>
                        <div class="text-lg sm:text-2xl font-bold text-gray-900">{{ $foodOrdersCount ?? 0 }}</div>
                        <div class="text-xs sm:text-sm font-medium text-gray-500">Food</div>
                    </a>

                    <!-- Espace livreur externe uniquement -->
                    @php
                        $driverProfilesPresta = \App\Models\DeliveryDriver::query()
                            ->select(['id', 'is_internal', 'employer_prestataire_id'])
                            ->where('user_id', auth()->id())
                            ->get();

                        $externalDriverProfilePresta = $driverProfilesPresta->first(function ($driverItem) {
                            return !((bool) ($driverItem->is_internal ?? false) && !empty($driverItem->employer_prestataire_id));
                        });
                        $internalDriverProfilePresta = $driverProfilesPresta->first(function ($driverItem) {
                            return (bool) ($driverItem->is_internal ?? false) && !empty($driverItem->employer_prestataire_id);
                        });

                        $hasExternalDriverPresta = (bool) $externalDriverProfilePresta;

                        // Règle métier: ce bouton est réservé au flux livreur EXTERNE.
                        $driverSpaceUrlPresta = $hasExternalDriverPresta
                            ? route('driver.dashboard')
                            : route('driver.register');

                        $driverSpaceValuePresta = $hasExternalDriverPresta ? 'Go' : '+';
                        $driverSpaceLabelPresta = $hasExternalDriverPresta
                            ? 'Mon espace livreur'
                            : 'Devenir livreur';
                    @endphp
                    <a href="{{ $driverSpaceUrlPresta }}" class="stat-card-glass rounded-2xl p-3 sm:p-4 text-center group" title="{{ $hasExternalDriverPresta ? 'Accéder à mon espace livreur externe' : 'Devenir livreur externe' }}">
                        <div class="stat-icon flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 bg-linear-to-br from-emerald-500 to-teal-500 rounded-xl text-white">
                            <span class="text-xl sm:text-2xl">🚴</span>
                        </div>
                        <div class="text-lg sm:text-2xl font-bold text-emerald-600">{{ $driverSpaceValuePresta }}</div>
                        <div class="text-xs sm:text-sm font-medium text-gray-500">{{ $driverSpaceLabelPresta }}</div>
                    </a>
                </div>
            </div>
            
            <!-- Bouton Mode Client - Accès à l'espace client pour commander chez d'autres prestataires -->
            <div class="flex justify-center mb-6 sm:mb-8">
                <a href="{{ route('client.dashboard') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold text-sm sm:text-base rounded-xl shadow-lg hover:from-green-600 hover:to-emerald-700 transform hover:scale-105 transition-all duration-300 hover:shadow-xl">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <span>Mode Client</span>
                    <span class="hidden sm:inline ml-2 text-green-200">- Réservez chez d'autres prestataires</span>
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Bloc principal : Mes services, Mon matériel, Mes annonces, Mon inventaire -->
        <div class="grid grid-cols-1 xs:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 md:gap-5 mb-4 sm:mb-6 md:mb-8">
            <!-- Mes services -->
            <div class="main-card-3d blue p-3 sm:p-4 md:p-6 flex flex-col min-h-[180px] sm:min-h-[220px] md:min-h-[300px]">
                <div class="flex items-center mb-4">
                    <div class="flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 icon-gradient blue rounded-2xl text-white shadow-lg">
                        <svg class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0h-8m8 0a2 2 0 012 2v6m-10 0V8a2 2 0 012-2" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-base sm:text-xl font-bold text-gray-900">Mes services</h3>
                        <p class="text-xs sm:text-sm text-gray-500">{{ $totalServices ?? 0 }} service(s)</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="text-center bg-blue-50 rounded-xl p-3">
                        <div class="text-xl sm:text-2xl font-bold text-blue-600">{{ $activeServices ?? 0 }}</div>
                        <div class="text-xs font-medium text-gray-600">En cours</div>
                    </div>
                    <div class="text-center bg-blue-50 rounded-xl p-3">
                        <div class="text-xl sm:text-2xl font-bold text-blue-600">{{ $totalServices ?? 0 }}</div>
                        <div class="text-xs font-medium text-gray-600">Total</div>
                    </div>
                </div>
                
                <div class="grow"></div>
                <div class="space-y-2 mt-auto">
                    <a href="{{ route('prestataire.services.index') }}" class="btn-gradient blue w-full inline-flex items-center justify-center px-4 py-2.5 text-xs sm:text-sm font-semibold rounded-xl text-white shadow-lg">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Gérer
                    </a>
                </div>
            </div>

            <!-- Mon matériel -->
            <div class="main-card-3d green p-3 sm:p-4 md:p-6 flex flex-col min-h-[180px] sm:min-h-[220px] md:min-h-[300px]">
                <div class="flex items-center mb-4">
                    <div class="flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 icon-gradient green rounded-2xl text-white shadow-lg">
                        <svg class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-base sm:text-xl font-bold text-gray-900">Mon matériel</h3>
                        <p class="text-xs sm:text-sm text-gray-500">{{ $equipmentCount ?? 0 }} équipement(s)</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="text-center bg-green-50 rounded-xl p-3">
                        <div class="text-xl sm:text-2xl font-bold text-green-600">{{ $equipmentRentalRequestsCount ?? 0 }}</div>
                        <div class="text-xs font-medium text-gray-600">Demandes</div>
                    </div>
                    <div class="text-center bg-green-50 rounded-xl p-3">
                        <div class="text-xl sm:text-2xl font-bold text-green-600">{{ $activeRentalsCount ?? 0 }}</div>
                        <div class="text-xs font-medium text-gray-600">Locations</div>
                    </div>
                </div>
                
                <div class="grow"></div>
                <div class="space-y-2 mt-auto">
                    <a href="{{ route('prestataire.equipment.index') }}" class="btn-gradient green w-full inline-flex items-center justify-center px-4 py-2.5 text-xs sm:text-sm font-semibold rounded-xl text-white shadow-lg">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Gérer
                    </a>
                </div>
            </div>

            <!-- Mes annonces -->
            <div class="main-card-3d red p-3 sm:p-4 md:p-6 flex flex-col min-h-[180px] sm:min-h-[220px] md:min-h-[300px]">
                <div class="flex items-center mb-4">
                    <div class="flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 icon-gradient red rounded-2xl text-white shadow-lg">
                        <svg class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-base sm:text-xl font-bold text-gray-900">Mes annonces</h3>
                        <p class="text-xs sm:text-sm text-gray-500">Offres & promos</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="text-center bg-red-50 rounded-xl p-3">
                        <div class="text-xl sm:text-2xl font-bold text-red-600">{{ $urgentSalesCount ?? 0 }}</div>
                        <div class="text-xs font-medium text-gray-600">Ventes</div>
                    </div>
                    <div class="text-center bg-red-50 rounded-xl p-3">
                        <div class="text-xl sm:text-2xl font-bold text-red-600">{{ $urgentProductsCount ?? 0 }}</div>
                        <div class="text-xs font-medium text-gray-600">Produits</div>
                    </div>
                </div>
                
                <div class="grow"></div>
                <div class="space-y-2 mt-auto">
                    <a href="{{ route('prestataire.urgent-sales.index') }}" class="btn-gradient red w-full inline-flex items-center justify-center px-4 py-2.5 text-xs sm:text-sm font-semibold rounded-xl text-white shadow-lg">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Gérer
                    </a>
                </div>
            </div>

            <!-- Mon inventaire -->
            <div class="main-card-3d indigo p-3 sm:p-4 md:p-6 flex flex-col min-h-[180px] sm:min-h-[220px] md:min-h-[300px]">
                <div class="flex items-center mb-4">
                    <div class="flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 icon-gradient indigo rounded-2xl text-white shadow-lg">
                        <svg class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-base sm:text-xl font-bold text-gray-900">Mon inventaire</h3>
                        <p class="text-xs sm:text-sm text-gray-500">Stock & produits</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="text-center bg-indigo-50 rounded-xl p-3">
                        <div class="text-xl sm:text-2xl font-bold text-indigo-600">{{ $inventoryCount ?? 0 }}</div>
                        <div class="text-xs font-medium text-gray-600">Articles</div>
                    </div>
                    <div class="text-center bg-indigo-50 rounded-xl p-3">
                        <div class="text-xl sm:text-2xl font-bold text-indigo-600">{{ $lowStockCount ?? 0 }}</div>
                        <div class="text-xs font-medium text-gray-600">Stock bas</div>
                    </div>
                </div>
                
                <div class="grow"></div>
                <div class="space-y-2 mt-auto">
                    <a href="{{ Route::has('prestataire.inventory.index') ? route('prestataire.inventory.index') : '#' }}" class="btn-gradient purple w-full inline-flex items-center justify-center px-4 py-2.5 text-xs sm:text-sm font-semibold rounded-xl text-white shadow-lg">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Gérer
                    </a>
                </div>
            </div>

            <!-- Commandes Alimentaires / Menu -->
            <div class="main-card-3d orange p-3 sm:p-4 md:p-6 flex flex-col min-h-[180px] sm:min-h-[220px] md:min-h-[300px]">
                <div class="flex items-center mb-4">
                    <div class="flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 icon-gradient orange rounded-2xl text-white shadow-lg">
                        <svg class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-base sm:text-xl font-bold text-gray-900">Mon Menu</h3>
                        <p class="text-xs sm:text-sm text-gray-500">Commandes alimentaires</p>
                    </div>
                </div>
                
                @php
                    $foodTablesExist = \Illuminate\Support\Facades\Schema::hasTable('food_products');
                    $foodProductsCount = 0;
                    $pendingFoodOrders = 0;
                    $preparingFoodOrders = 0;
                    if ($foodTablesExist) {
                        try {
                            $foodProductsCount = $prestataire->foodProducts()->count();
                            $pendingFoodOrders = $prestataire->foodOrders()->where('status', 'pending')->count();
                            $preparingFoodOrders = $prestataire->foodOrders()->whereIn('status', ['accepted', 'preparing'])->count();
                        } catch (\Exception $e) {
                            // Tables not ready yet
                        }
                    }
                @endphp
                
                @if(!$foodTablesExist)
                <div class="mb-3 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                    <p class="text-xs text-yellow-700 font-medium flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Migration requise : exécutez <code class="bg-yellow-100 px-1 rounded">php artisan migrate</code>
                    </p>
                </div>
                @else
                
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <div class="text-center bg-orange-50 rounded-xl p-2 sm:p-3">
                        <div class="text-lg sm:text-2xl font-bold text-orange-600">{{ $foodProductsCount }}</div>
                        <div class="text-[10px] sm:text-xs font-medium text-gray-600">Produits</div>
                    </div>
                    <div class="text-center bg-yellow-50 rounded-xl p-2 sm:p-3">
                        <div class="text-lg sm:text-2xl font-bold text-yellow-600">{{ $pendingFoodOrders }}</div>
                        <div class="text-[10px] sm:text-xs font-medium text-gray-600">En attente</div>
                    </div>
                    <div class="text-center bg-green-50 rounded-xl p-2 sm:p-3">
                        <div class="text-lg sm:text-2xl font-bold text-green-600">{{ $preparingFoodOrders }}</div>
                        <div class="text-[10px] sm:text-xs font-medium text-gray-600">En cours</div>
                    </div>
                </div>
                
                @if($pendingFoodOrders > 0)
                <div class="mb-3 p-2 bg-red-100 rounded-lg border border-red-200">
                    <p class="text-xs text-red-700 font-semibold flex items-center">
                        <svg class="w-4 h-4 mr-1 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        {{ $pendingFoodOrders }} commande(s) en attente !
                    </p>
                </div>
                @endif
                @endif
                
                <div class="grow"></div>
                <div class="space-y-2 mt-auto">
                    @if(Route::has('prestataire.food-orders.dashboard'))
                    <a href="{{ route('prestataire.food-orders.dashboard') }}" class="btn-gradient orange w-full inline-flex items-center justify-center px-4 py-2.5 text-xs sm:text-sm font-semibold rounded-xl text-white shadow-lg">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Tableau de bord cuisine
                    </a>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('prestataire.food-products.index') }}" class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-lg bg-orange-100 text-orange-700 hover:bg-orange-200 transition-colors">
                            <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Produits
                        </a>
                        <a href="{{ route('prestataire.food-orders.index') }}" class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium rounded-lg bg-green-100 text-green-700 hover:bg-green-200 transition-colors">
                            <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Commandes
                        </a>
                    </div>
                    @else
                    <div class="p-3 bg-gray-100 rounded-lg text-center">
                        <p class="text-xs text-gray-500">Fonctionnalité bientôt disponible</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bloc Appels d'Offres - Nouvelle section prominente -->
        <div class="mb-4 sm:mb-6">
            <div class="bg-linear-to-r from-blue-600 via-purple-600 to-blue-700 rounded-xl shadow-lg overflow-hidden">
                <div class="px-4 py-3 sm:px-6 sm:py-4">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <!-- En-tête avec stats -->
                        <div class="flex items-center gap-4">
                            <div class="shrink-0 w-12 h-12 sm:w-16 sm:h-16 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                </svg>
                            </div>
                            <div class="text-white">
                                <h3 class="text-lg sm:text-xl font-bold flex items-center gap-2">
                                    Appels d'Offres
                                    @if(($unreadInvitations ?? 0) > 0)
                                        <span class="px-2 py-0.5 bg-red-500 text-white text-xs font-bold rounded-full animate-pulse">
                                            {{ $unreadInvitations }} nouvelle(s)
                                        </span>
                                    @endif
                                </h3>
                                <p class="text-blue-100 text-sm">Trouvez des clients qui recherchent vos services</p>
                            </div>
                        </div>
                        
                        <!-- Stats rapides -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 sm:gap-4 text-white">
                            <div class="text-center bg-white/10 backdrop-blur rounded-lg px-1.5 py-1 sm:px-4 sm:py-2">
                                <div class="text-base sm:text-2xl font-bold">{{ $tenderStats['available'] ?? 0 }}</div>
                                <div class="text-[9px] sm:text-xs text-blue-100">Disponibles</div>
                            </div>
                            <div class="text-center bg-white/10 backdrop-blur rounded-lg px-1.5 py-1 sm:px-4 sm:py-2">
                                <div class="text-base sm:text-2xl font-bold">{{ $tenderStats['responded'] ?? 0 }}</div>
                                <div class="text-[9px] sm:text-xs text-blue-100">Propositions</div>
                            </div>
                            <div class="text-center bg-white/10 backdrop-blur rounded-lg px-1.5 py-1 sm:px-4 sm:py-2">
                                <div class="text-base sm:text-2xl font-bold">{{ $tenderStats['shortlisted'] ?? 0 }}</div>
                                <div class="text-[9px] sm:text-xs text-blue-100">Présélect.</div>
                            </div>
                            <div class="text-center bg-green-500/30 backdrop-blur rounded-lg px-1.5 py-1 sm:px-4 sm:py-2">
                                <div class="text-base sm:text-2xl font-bold">{{ $tenderStats['accepted'] ?? 0 }}</div>
                                <div class="text-[9px] sm:text-xs text-blue-100">Acceptées</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Appels d'offres récents -->
                @if(isset($recentTenders) && $recentTenders->count() > 0)
                <div class="bg-white/10 backdrop-blur border-t border-white/20 px-4 py-3 sm:px-6 sm:py-4">
                    <h4 class="text-white font-semibold mb-3 text-sm sm:text-base">📌 Appels d'offres récents pour vous</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3">
                        @foreach($recentTenders->take(3) as $tender)
                        <a href="{{ route('prestataire.tenders.show', $tender) }}" 
                           class="bg-white rounded-lg p-3 hover:shadow-lg transition-all duration-300 group">
                            <div class="flex items-start gap-2">
                                <div class="shrink-0 w-8 h-8 rounded-full bg-linear-to-br from-blue-100 to-purple-100 flex items-center justify-center">
                                    <span class="text-sm">{{ substr($tender->client->user->name ?? 'C', 0, 1) }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h5 class="font-semibold text-gray-900 text-sm truncate group-hover:text-blue-600 transition-colors">
                                        {{ Str::limit($tender->title, 30) }}
                                    </h5>
                                    <p class="text-xs text-gray-500">{{ $tender->city ?? 'Non précisé' }}</p>
                                    <div class="flex items-center justify-between mt-1">
                                        <span class="text-xs font-semibold text-blue-600">
                                            {{ number_format($tender->budget_min ?? 0, 0, ',', ' ') }} - {{ number_format($tender->budget_max ?? 0, 0, ',', ' ') }}€
                                        </span>
                                        @if($tender->urgency === 'urgent')
                                            <span class="px-1.5 py-0.5 bg-red-100 text-red-700 text-[10px] font-medium rounded">Urgent</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Actions -->
                <div class="bg-white/5 backdrop-blur border-t border-white/20 px-4 py-3 sm:px-6 sm:py-4">
                    <div class="flex flex-wrap gap-2 sm:gap-3">
                        <a href="{{ route('prestataire.tenders.index') }}" 
                           class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition-all duration-300 text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Explorer les appels
                        </a>
                        <a href="{{ route('prestataire.tenders.my-responses') }}" 
                           class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 bg-white/20 text-white font-semibold rounded-lg hover:bg-white/30 transition-all duration-300 text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Mes propositions
                        </a>
                        @if(auth()->user()->client)
                        <a href="{{ route('client.tenders.index') }}" 
                           class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition-all duration-300 text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Mes demandes créées
                        </a>
                        @endif
                        @if(($unreadInvitations ?? 0) > 0)
                        <a href="{{ route('prestataire.tenders.invitations') }}" 
                           class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition-all duration-300 text-sm animate-pulse">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Invitations ({{ $unreadInvitations }})
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Bloc secondaire : Profil et vérification -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 sm:gap-4">
            <!-- Colonne 1 : Mon profil -->
            <div class="dashboard-primary-card bg-white rounded-lg shadow-md border border-gray-100 p-2 sm:p-3 md:p-4 lg:p-5 hover:shadow-lg transition-all duration-300 flex flex-col min-h-[180px] sm:min-h-[200px] md:min-h-[220px]">
                @if(session('success'))
                    <div class="bg-green-100 border-l-2 border-green-500 text-green-700 p-2 mb-2" role="alert">
                        <p class="font-bold text-xs">Succès</p>
                        <p class="text-xs">{{ session('success') }}</p>
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-100 border-l-2 border-red-500 text-red-700 p-2 mb-2" role="alert">
                        <p class="font-bold text-xs">Erreur</p>
                        <ul class="text-xs">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex items-center justify-between mb-2 sm:mb-3">
                    <div class="flex items-center">
                        <div class="shrink-0">
                            <div class="flex items-center justify-center w-5 h-5 sm:w-6 sm:h-6 md:w-7 md:h-7 lg:w-8 lg:h-8 bg-purple-50 rounded-lg">
                                <svg class="h-2.5 w-2.5 sm:h-3 sm:w-3 md:h-3.5 md:w-3.5 lg:h-4 lg:w-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-1.5 sm:ml-2 md:ml-3">
                            <h3 class="text-sm sm:text-base md:text-lg lg:text-xl font-bold text-gray-900">Mon profil</h3>
                            <p class="text-xs sm:text-sm text-gray-600">Gérer mes informations</p>
                        </div>
                    </div>
                </div>
                
                <div class="grow"></div>
                
                <a href="{{ route('prestataire.profile.edit') }}" class="w-full inline-flex items-center justify-center px-2 sm:px-3 py-1.5 sm:py-2 border border-transparent text-xs font-semibold rounded-lg text-white bg-purple-600 hover:bg-purple-700 transition-all duration-300 shadow-sm hover:shadow mt-auto">
                    <svg class="-ml-1 mr-1 h-2.5 w-2.5 sm:h-3 sm:w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536a1 1 0 113.536 3.536L6.5 21.036H3v-3.5L16.732 3.732z" />
                    </svg>
                    <span class="hidden sm:inline">Modifier mon profil</span>
                    <span class="sm:hidden">Profil</span>
                </a>
                
            </div>

            <!-- Colonne 2 : Vérification de compte -->
            <div class="dashboard-primary-card bg-white rounded-lg shadow-md border border-gray-100 p-2 sm:p-3 md:p-4 lg:p-5 hover:shadow-lg transition-all duration-300 flex flex-col min-h-[180px] sm:min-h-[200px] md:min-h-[220px]">
                <div class="flex items-center justify-between mb-2 sm:mb-3">
                    <div class="flex items-center">
                        <div class="shrink-0">
                            <div class="flex items-center justify-center w-5 h-5 sm:w-6 sm:h-6 md:w-7 md:h-7 lg:w-8 lg:h-8 bg-blue-50 rounded-lg">
                                <svg class="h-2.5 w-2.5 sm:h-3 sm:w-3 md:h-3.5 md:w-3.5 lg:h-4 lg:w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-1.5 sm:ml-2 md:ml-3">
                            <h3 class="text-sm sm:text-base md:text-lg lg:text-xl font-bold text-gray-900">Vérification de compte</h3>
                            <p class="text-xs sm:text-sm text-gray-600">
                                @if(auth()->user()->prestataire->isVerified())
                                    <span class="text-orange-600 font-medium">Compte vérifié</span>
                                @else
                                    <span class="text-orange-600 font-medium">En attente de vérification</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    @if(auth()->user()->prestataire->isVerified())
                        <div class="shrink-0 hidden sm:block">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Vérifié
                            </span>
                        </div>
                    @endif
                </div>
                
                @if(auth()->user()->prestataire->isVerified())
                    <div class="text-center py-2 sm:py-3">
                        <div class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 lg:w-12 lg:h-12 mx-auto mb-1 sm:mb-2 bg-green-50 rounded-lg">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 lg:w-7 lg:h-7 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-xs sm:text-sm font-semibold text-gray-900 mb-0.5">Félicitations !</h3>
                        <p class="text-xs text-gray-500 mb-1 sm:mb-2">Votre compte est vérifié et bénéficie du badge "Prestataire Vérifié"</p>
                    </div>
                @else
                    <div class="text-center py-2 sm:py-3">
                        <div class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 lg:w-12 lg:h-12 mx-auto mb-1 sm:mb-2 bg-green-50 rounded-lg">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 lg:w-7 lg:h-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <h3 class="text-xs sm:text-sm font-semibold text-gray-900 mb-0.5">Vérifiez votre compte</h3>
                        <p class="text-xs text-gray-500 mb-1 sm:mb-2">Obtenez le badge "Prestataire Vérifié" pour gagner la confiance des clients</p>
                    </div>
                @endif
                
                <div class="grow"></div>
                <a href="{{ route('prestataire.verification.index') }}" class="w-full inline-flex items-center justify-center px-2 sm:px-3 py-1.5 sm:py-2 border border-transparent text-xs font-semibold rounded-lg text-white bg-orange-600 hover:bg-orange-700 transition-all duration-300 shadow-sm hover:shadow mt-auto">
                    <svg class="-ml-1 mr-1 h-2.5 w-2.5 sm:h-3 sm:w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    @if(auth()->user()->prestataire->isVerified())
                        <span class="hidden sm:inline">Gérer ma vérification</span>
                        <span class="sm:hidden">Vérification</span>
                    @else
                        <span class="hidden sm:inline">Vérifier mon compte</span>
                        <span class="sm:hidden">Vérifier</span>
                    @endif
                </a>
            </div>
        </div>
        
    </div>

    <!-- Section Idées & Conseils -->
    <x-usage-ideas 
        title="💡 Idées pour développer votre activité"
        :collapsible="true"
        :defaultOpen="false"
        :ideas="[
            ['icon' => '📸', 'title' => 'Photos professionnelles', 'desc' => 'Ajoutez des photos de qualité de vos réalisations pour attirer plus de clients', 'link' => route('prestataire.services.index')],
            ['icon' => '⭐', 'title' => 'Collectez des avis', 'desc' => 'Demandez à vos clients satisfaits de laisser un avis - ça booste votre visibilité !'],
            ['icon' => '🎁', 'title' => 'Créez des offres', 'desc' => 'Proposez des ventes flash ou promotions pour attirer de nouveaux clients', 'link' => route('prestataire.urgent-sales.create')],
            ['icon' => '📱', 'title' => 'Partagez votre QR Code', 'desc' => 'Imprimez votre QR Code sur vos cartes de visite ou flyers', 'link' => route('prestataire.qrcode.show')],
            ['icon' => '📦', 'title' => 'Louez votre matériel', 'desc' => 'Rentabilisez vos équipements en les proposant à la location', 'link' => route('prestataire.equipment.index')],
            ['icon' => '🚚', 'title' => 'Proposez la livraison', 'desc' => 'Configurez des options de livraison pour vos produits et services'],
        ]"
    />
</div>


@push('scripts')
<script>
    // Vérifier que les éléments existent avant d'ajouter les listeners
    const openVerificationBtn = document.getElementById('open-verification-modal');
    const verificationModal = document.getElementById('verificationModal');
    
    if (openVerificationBtn && verificationModal) {
        openVerificationBtn.addEventListener('click', function() {
            verificationModal.classList.remove('hidden');
        });
    }

    // Progress bars (évite du Blade dans style="width:...")
    document.querySelectorAll('[data-progress]').forEach((el) => {
        const raw = Number(el.getAttribute('data-progress'));
        if (!Number.isFinite(raw)) return;
        const value = Math.max(0, Math.min(100, raw));
        el.style.width = `${value}%`;
    });
</script>
@endpush
@endsection
