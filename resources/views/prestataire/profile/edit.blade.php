@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    /* Gray color scheme and styling */
    .profile-card {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        overflow: hidden;
        background: white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    
    .profile-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border-color: #d1d5db;
    }
    
    /* Autocomplete dropdown styles */
    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        max-height: 280px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        margin-top: 4px;
    }
    
    .autocomplete-item {
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: background-color 0.15s ease;
    }
    
    .autocomplete-item:last-child {
        border-bottom: none;
    }
    
    .autocomplete-item:hover {
        background-color: #f9fafb;
    }
    
    .geoloc-btn {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .geoloc-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }
    
    .geoloc-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    .fade-in-up {
        animation: fadeInUp 0.5s ease-out;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .stat-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f9fafb;
    }
    
    .section-header {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .section-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-left: 0.75rem;
        color: #111827;
    }
    
    /* Enhanced button styles */
    .btn-primary {
        background-color: #4b5563;
        color: white;
        font-weight: 600;
        border-radius: 0.75rem;
        transition: all 0.2s ease-in-out;
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .btn-primary:hover {
        background-color: #374151;
        transform: translateY(-1px);
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    }
    
    .btn-secondary {
        background-color: #e5e7eb;
        color: #374151;
        font-weight: 600;
        border-radius: 0.75rem;
        transition: all 0.2s ease;
        border: none;
    }
    
    .btn-secondary:hover {
        background-color: #d1d5db;
        transform: translateY(-1px);
    }
    
    /* Enhanced action buttons */
    .action-button {
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .action-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    /* Delete avatar button */
    .delete-avatar-btn {
        background-color: #f9fafb;
        color: #4b5563;
        font-weight: 600;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        border: 1px solid #e5e7eb;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
    
    .delete-avatar-btn:hover {
        background-color: #f3f4f6;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    
    /* Status badge enhancement */
    .status-badge {
        border-radius: 9999px;
        padding: 0.25rem 0.75rem;
        font-weight: 600;
        font-size: 0.75rem;
    }
    
    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
    }
    
    .status-accepted {
        background-color: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
    }
    
    .status-completed {
        background-color: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }
    
    .status-rejected {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }
    
    /* Gray color variations */
    .bg-gray-50 {
        background-color: #f9fafb;
    }
    
    .text-gray-600 {
        color: #4b5563;
    }
    
    .bg-gray-100 {
        background-color: #f3f4f6;
    }
    
    .text-gray-700 {
        color: #374151;
    }
    
    .bg-gray-200 {
        background-color: #e5e7eb;
    }
    
    .text-gray-800 {
        color: #1f2937;
    }
    
    .bg-gray-500 {
        background-color: #6b7280;
    }
    
    .text-gray-900 {
        color: #111827;
    }
    
    .border-gray-200 {
        border-color: #e5e7eb;
    }
    
    .border-gray-300 {
        border-color: #d1d5db;
    }
    
    /* Password requirements styles */
    .password-requirements {
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-top: 0.5rem;
        transition: border-color 0.3s ease;
    }
    
    .password-requirements h4 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    
    .password-requirements ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
        color: #6b7280;
    }
    
    .password-requirements li {
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
    }
    
    .password-requirements li:last-child {
        margin-bottom: 0;
    }
    
    .password-requirements .requirement-icon {
        width: 1rem;
        height: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .password-requirements .requirement-met {
        color: #10b981;
    }
    
    .password-requirements .requirement-not-met {
        color: #ef4444;
    }
    
    .password-requirements .requirement-text {
        transition: color 0.3s ease;
    }
    
    /* Enhanced responsive improvements */
    @media (max-width: 1024px) {
        .grid-cols-lg-3 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        
        .lg\:col-span-3 {
            grid-column: span 1 / span 1;
        }
    }
    
    @media (max-width: 768px) {
        .section-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .section-title {
            margin-left: 0;
            margin-top: 0.5rem;
        }
        
        .stat-icon {
            width: 2rem;
            height: 2rem;
        }
        
        .profile-card {
            padding: 1rem;
        }
        
        .delete-avatar-btn {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }
        
        .grid-cols-md-6 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        
        .md\:col-span-3 {
            grid-column: span 1 / span 1;
        }
        
        .md\:col-span-6 {
            grid-column: span 1 / span 1;
        }
        
        .flex-col-md {
            flex-direction: column;
        }
        
        .space-y-md-3 {
            --tw-space-y-reverse: 0;
            margin-top: calc(0.75rem * calc(1 - var(--tw-space-y-reverse)));
            margin-bottom: calc(0.75rem * var(--tw-space-y-reverse));
        }
        
        .space-x-md-0 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 0;
            margin-right: calc(0px * var(--tw-space-x-reverse));
            margin-left: calc(0px * calc(1 - var(--tw-space-x-reverse)));
        }
        
        .w-md-auto {
            width: 100%;
        }
        
        .text-center-md {
            text-align: center;
        }
        
        /* Mobile-specific improvements for profile form */
        .form-grid {
            grid-template-columns: 1fr !important;
        }
        
        .form-col-span {
            grid-column: span 1 / span 1 !important;
        }
        
        .mobile-stack {
            flex-direction: column !important;
        }
        
        .mobile-full-width {
            width: 100% !important;
        }
        
        .mobile-text-center {
            text-align: center !important;
        }
        
        .mobile-space-y-3 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-y-reverse: 0;
            margin-top: calc(0.75rem * calc(1 - var(--tw-space-y-reverse)));
            margin-bottom: calc(0.75rem * var(--tw-space-y-reverse));
        }
        
        .mobile-space-x-0 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 0;
            margin-right: calc(0px * var(--tw-space-x-reverse));
            margin-left: calc(0px * calc(1 - var(--tw-space-x-reverse)));
        }
    }
    
    @media (max-width: 640px) {
        .px-4-sm {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .py-8-sm {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        
        .text-2xl-sm {
            font-size: 1.5rem;
            line-height: 2rem;
        }
        
        .text-lg-sm {
            font-size: 1.125rem;
            line-height: 1.75rem;
        }
        
        .p-4-sm {
            padding: 1rem;
        }
        
        .space-y-sm-4 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-y-reverse: 0;
            margin-top: calc(1rem * calc(1 - var(--tw-space-y-reverse)));
            margin-bottom: calc(1rem * var(--tw-space-y-reverse));
        }
        
        .space-x-sm-0 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 0;
            margin-right: calc(0px * var(--tw-space-x-reverse));
            margin-left: calc(0px * calc(1 - var(--tw-space-x-reverse)));
        }
        
        .flex-col-sm {
            flex-direction: column;
        }
        
        .items-start-sm {
            align-items: flex-start;
        }
        
        .w-sm-auto {
            width: 100%;
        }
        
        /* Additional mobile improvements */
        .mobile-p-3 {
            padding: 0.75rem !important;
        }
        
        .mobile-text-sm {
            font-size: 0.875rem !important;
            line-height: 1.25rem !important;
        }
        
        .mobile-mb-2 {
            margin-bottom: 0.5rem !important;
        }
        
        .mobile-mt-4 {
            margin-top: 1rem !important;
        }
    }
    
    /* Modal responsive improvements */
    @media (max-width: 640px) {
        #deleteModal .relative {
            width: 90%;
            margin: 0 auto;
            top: 10%;
        }
        
        #deleteModal .w-96 {
            width: 100%;
        }
        
        #deleteModal .p-5 {
            padding: 1rem;
        }
        
        #deleteModal .mt-4 {
            margin-top: 1rem;
        }
        
        #deleteModal .px-7 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        #deleteModal .py-3 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }
        
        /* Mobile button stacking in modal */
        #deleteModal .flex-col-sm {
            flex-direction: column !important;
        }
        
        #deleteModal .space-y-sm-3 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-y-reverse: 0;
            margin-top: calc(0.75rem * calc(1 - var(--tw-space-y-reverse)));
            margin-bottom: calc(0.75rem * var(--tw-space-y-reverse));
        }
        
        #deleteModal .space-x-sm-0 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 0;
            margin-right: calc(0px * var(--tw-space-x-reverse));
            margin-left: calc(0px * calc(1 - var(--tw-space-x-reverse)));
        }
        
        #deleteModal .mobile-full-width {
            width: 100% !important;
        }
    }
</style>
@endpush

@section('content')
@php
    $hasPhone = $prestataire && !empty($prestataire->phone);
    $hasAddress = $prestataire && !empty($prestataire->address);
    $hasCategory = $prestataire && !empty($prestataire->category_id);
    $hasDescription = $prestataire && !empty($prestataire->description) && strlen($prestataire->description) >= 50;
    $isProfileComplete = $hasPhone && $hasAddress && $hasCategory;
    $isGoogleUser = auth()->user()->isSocialAccount();
@endphp

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-4 px-3">
    <div class="max-w-6xl mx-auto">
        
        {{-- Alerte profil incomplet - TRÈS VISIBLE --}}
        @if(!$isProfileComplete)
            <div class="mb-4 bg-gradient-to-r from-red-500 to-orange-500 rounded-xl p-4 shadow-lg">
                <div class="flex items-center gap-3 text-white">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center animate-pulse">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-lg">⚠️ Profil incomplet !</p>
                        <p class="text-white/90 text-sm">
                            Complétez votre profil pour recevoir des clients :
                            @if(!$hasPhone) <span class="bg-white/20 px-2 py-0.5 rounded">📱 Téléphone</span> @endif
                            @if(!$hasAddress) <span class="bg-white/20 px-2 py-0.5 rounded">📍 Adresse</span> @endif
                            @if(!$hasCategory) <span class="bg-white/20 px-2 py-0.5 rounded">📂 Catégorie</span> @endif
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="mb-4 bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl p-3 shadow-lg">
                <div class="flex items-center gap-3 text-white">
                    <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-check text-lg"></i>
                    </div>
                    <p class="font-medium">✅ Profil complet - Vous êtes visible par les clients !</p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-3 rounded">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @if (session('success'))
            <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        {{-- Layout en 2 colonnes --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            
            {{-- COLONNE 1 : Identité + Activité (Fusionné) --}}
            <div class="bg-white rounded-xl shadow-md border-2 {{ ($hasPhone && $hasAddress && $hasCategory) ? 'border-green-400' : 'border-orange-300' }} overflow-hidden">
                <div class="px-4 py-3 {{ ($hasPhone && $hasAddress && $hasCategory) ? 'bg-green-50' : 'bg-orange-50' }} border-b flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 {{ ($hasPhone && $hasAddress && $hasCategory) ? 'bg-green-500' : 'bg-orange-500' }} rounded-lg flex items-center justify-center">
                            @if($hasPhone && $hasAddress && $hasCategory)
                                <i class="fas fa-check text-white"></i>
                            @else
                                <i class="fas fa-user text-white"></i>
                            @endif
                        </div>
                        <h3 class="font-bold text-gray-800">👤 Mon Profil</h3>
                    </div>
                    @if($hasPhone && $hasAddress && $hasCategory)
                        <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full font-medium">✓ Complet</span>
                    @endif
                </div>
                
                <form action="{{ route('prestataire.profile.update.personal') }}" method="POST" enctype="multipart/form-data" class="p-4">
                    @csrf
                    @method('PUT')
                    
                    {{-- Photo + Nom en ligne --}}
                    <div class="flex items-center gap-3 mb-4">
                        <div class="relative">
                            @if($prestataire && $prestataire->photo)
                                <img class="h-14 w-14 rounded-full object-cover border-2 border-blue-300" src="{{ asset('storage/' . $prestataire->photo) }}" alt="{{ auth()->user()->name ?? 'Photo de profil' }}">
                            @else
                                <div class="h-14 w-14 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white text-xl font-bold">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                            @endif
                            <label class="absolute -bottom-1 -right-1 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center cursor-pointer hover:bg-blue-600">
                                <i class="fas fa-camera text-white text-xs"></i>
                                <input type="file" name="photo" class="hidden" accept="image/*">
                            </label>
                        </div>
                        <div class="flex-1">
                            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" 
                                class="w-full text-lg font-semibold border-0 border-b-2 border-gray-200 focus:border-blue-500 focus:ring-0 px-0 py-1" required>
                            <p class="text-xs text-gray-500 flex items-center gap-1">
                                {{ auth()->user()->email }}
                                @if(auth()->user()->email_verified_at)
                                    <i class="fas fa-check-circle text-green-500"></i>
                                @endif
                                @if($isGoogleUser)
                                    <img src="https://www.google.com/favicon.ico" alt="" class="w-3 h-3">
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                    
                    {{-- Nom de l'enseigne --}}
                    <div class="mb-3">
                        <label class="text-sm font-medium text-gray-700 flex items-center gap-1">
                            🏢 Nom de l'enseigne / Entreprise
                            @if($prestataire && $prestataire->company_name) <i class="fas fa-check-circle text-green-500 text-xs"></i> @else <span class="text-red-500">*</span> @endif
                        </label>
                        <input type="text" name="company_name" value="{{ old('company_name', $prestataire->company_name ?? '') }}" 
                            placeholder="Ex: ABC Services, Auto-entrepreneur, etc."
                            class="mt-1 w-full rounded-lg border {{ ($prestataire && $prestataire->company_name) ? 'border-green-300 bg-green-50' : 'border-orange-300 bg-orange-50' }} px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required>
                        <p class="text-xs text-gray-500 mt-1">Le nom sous lequel vous exercez votre activité</p>
                    </div>
                    
                    {{-- Téléphone --}}
                    <div class="mb-3">
                        <label class="text-sm font-medium text-gray-700 flex items-center gap-1">
                            📱 Téléphone
                            @if($hasPhone) <i class="fas fa-check-circle text-green-500 text-xs"></i> @else <span class="text-red-500">*</span> @endif
                        </label>
                        <input type="tel" name="phone" value="{{ old('phone', $prestataire->phone ?? '') }}" 
                            placeholder="06 12 34 56 78"
                            class="mt-1 w-full rounded-lg border {{ $hasPhone ? 'border-green-300 bg-green-50' : 'border-orange-300 bg-orange-50' }} px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    {{-- Adresse --}}
                    <div class="mb-3">
                        <label class="text-sm font-medium text-gray-700 flex items-center gap-1">
                            📍 Adresse
                            @if($hasAddress) <i class="fas fa-check-circle text-green-500 text-xs"></i> @else <span class="text-red-500">*</span> @endif
                        </label>
                        <div class="relative">
                            <input type="text" name="address" id="address" value="{{ old('address', $prestataire->address ?? '') }}" 
                                placeholder="Votre ville ou adresse..."
                                autocomplete="off"
                                class="mt-1 w-full rounded-lg border {{ $hasAddress ? 'border-green-300 bg-green-50' : 'border-orange-300 bg-orange-50' }} px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required>
                            <div id="address-dropdown" class="autocomplete-dropdown"></div>
                        </div>
                        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $prestataire->latitude ?? '') }}">
                        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $prestataire->longitude ?? '') }}">
                        <button type="button" id="geoloc_btn" class="mt-1 text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1">
                            <i class="fas fa-location-arrow"></i> Ma position
                        </button>
                    </div>
                    
                    {{-- Séparateur Activité --}}
                    <div class="border-t border-gray-200 my-4 pt-4">
                        <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                            💼 Activité
                            @if($hasCategory) <i class="fas fa-check-circle text-green-500 text-sm"></i> @endif
                        </h4>
                    </div>
                    
                    {{-- Catégorie --}}
                    <div class="mb-3">
                        <label class="text-sm font-medium text-gray-700 flex items-center gap-1">
                            📂 Catégorie
                            @if($hasCategory) <i class="fas fa-check-circle text-green-500 text-xs"></i> @else <span class="text-red-500">*</span> @endif
                        </label>
                        <select name="category_id" id="category_id" 
                            class="mt-1 w-full rounded-lg border {{ $hasCategory ? 'border-green-300 bg-green-50' : 'border-orange-300 bg-orange-50' }} px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500" required>
                            <option value="">Choisir...</option>
                            @foreach($categories->where('parent_id', null) as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $prestataire->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Sous-catégorie --}}
                    <div class="mb-3">
                        <label class="text-sm font-medium text-gray-700">🏷️ Spécialité</label>
                        <select name="subcategory_id" id="subcategory_id" 
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                            <option value="">Choisir une catégorie d'abord</option>
                        </select>
                    </div>
                    
                    {{-- Secteur --}}
                    <div class="mb-3">
                        <label class="text-sm font-medium text-gray-700">🎯 Secteur</label>
                        <input type="text" name="secteur_activite" value="{{ old('secteur_activite', $prestataire->secteur_activite ?? '') }}" 
                            placeholder="Ex: Traiteur, Photographe..."
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                    
                    {{-- Compétences --}}
                    <div class="mb-3">
                        <label class="text-sm font-medium text-gray-700">⭐ Compétences</label>
                        <input type="text" name="competences" value="{{ old('competences', $prestataire->competences ?? '') }}" 
                            placeholder="Cuisine, Événementiel..."
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 rounded-lg font-medium hover:from-blue-600 hover:to-purple-700 transition text-base">
                        💾 Enregistrer mon profil
                    </button>
                </form>
            </div>
            
            {{-- COLONNE 3 : Description --}}
            <div class="bg-white rounded-xl shadow-md border-2 {{ $hasDescription ? 'border-green-400' : 'border-gray-200' }} overflow-hidden">
                <div class="px-4 py-3 {{ $hasDescription ? 'bg-green-50' : 'bg-gray-50' }} border-b flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 {{ $hasDescription ? 'bg-green-500' : 'bg-gray-400' }} rounded-lg flex items-center justify-center">
                            @if($hasDescription)
                                <i class="fas fa-check text-white"></i>
                            @else
                                <i class="fas fa-pen text-white"></i>
                            @endif
                        </div>
                        <h3 class="font-bold text-gray-800">📝 Présentation</h3>
                    </div>
                    @if($hasDescription)
                        <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full font-medium">✓ Complet</span>
                    @else
                        <span class="bg-gray-400 text-white text-xs px-2 py-1 rounded-full font-medium">Optionnel</span>
                    @endif
                </div>
                
                <form action="{{ route('prestataire.profile.update.personal') }}" method="POST" class="p-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="name" value="{{ auth()->user()->name }}">
                    <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                    <input type="hidden" name="phone" value="{{ $prestataire->phone ?? '' }}">
                    <input type="hidden" name="address" value="{{ $prestataire->address ?? '' }}">
                    <input type="hidden" name="category_id" value="{{ $prestataire->category_id ?? '' }}">
                    
                    <textarea name="description" id="description" rows="5" 
                        placeholder="Décrivez votre expertise, votre expérience..."
                        class="w-full rounded-lg border {{ $hasDescription ? 'border-green-300 bg-green-50' : 'border-gray-300' }} px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500">{{ old('description', $prestataire->description ?? '') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Minimum 50 caractères pour être complet</p>
                    
                    <button type="submit" class="w-full mt-3 bg-gradient-to-r from-amber-500 to-amber-600 text-white py-2 rounded-lg font-medium hover:from-amber-600 hover:to-amber-700 transition">
                        💾 Enregistrer
                    </button>
                </form>
            </div>
            
            {{-- COLONNE 4 : Sécurité + Danger --}}
            <div class="space-y-4">
                {{-- Sécurité --}}
                <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 bg-blue-50 border-b flex items-center gap-2">
                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-lock text-white"></i>
                        </div>
                        <h3 class="font-bold text-gray-800">🔐 Sécurité</h3>
                    </div>
                    
                    <form action="{{ route('prestataire.profile.update.security') }}" method="POST" class="p-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="is_google_user" value="{{ $isGoogleUser ? '1' : '0' }}">
                        
                        @if($isGoogleUser)
                            <p class="text-sm text-gray-600 mb-3 flex items-center gap-2">
                                <img src="https://www.google.com/favicon.ico" alt="" class="w-4 h-4">
                                Connecté via Google
                            </p>
                        @else
                            <div class="mb-3">
                                <label class="text-sm font-medium text-gray-700">Mot de passe actuel</label>
                                <input type="password" name="current_password" 
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                            </div>
                        @endif
                        
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div>
                                <label class="text-xs font-medium text-gray-700">Nouveau</label>
                                <input type="password" name="new_password" 
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-700">Confirmer</label>
                                <input type="password" name="new_password_confirmation" 
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg text-sm font-medium hover:bg-blue-600 transition">
                            Changer mot de passe
                        </button>
                    </form>
                </div>
                
                {{-- Supprimer compte --}}
                <div class="bg-white rounded-xl shadow-md border border-red-200 overflow-hidden">
                    <div class="p-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-trash text-red-500"></i>
                            </div>
                            <div>
                                <p class="font-medium text-red-600 text-sm">Supprimer le compte</p>
                                <p class="text-xs text-gray-500">Action irréversible</p>
                            </div>
                        </div>
                        <button type="button" onclick="openDeleteModal()" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-sm font-medium transition">
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-xl rounded-xl bg-white border-gray-200">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mt-3">Confirmer la suppression</h3>
            <div class="mt-4 px-7 py-3">
                <p class="text-gray-700 mb-4">
                    Pour confirmer la suppression de votre compte, veuillez :
                </p>
                <form id="deleteForm" method="POST" action="{{ route('prestataire.profile.destroy') }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="is_google_user" value="{{ $isGoogleUser ? '1' : '0' }}">
                    
                    @if($isGoogleUser)
                        <!-- Utilisateur Google sans mot de passe -->
                        <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-center gap-2">
                                <img src="https://www.google.com/favicon.ico" alt="" class="w-4 h-4">
                                <span class="text-sm text-blue-800">Compte Google - pas de mot de passe requis</span>
                            </div>
                        </div>
                    @else
                        <div class="mb-4">
                            <label for="delete_password" class="block text-sm font-medium text-gray-800 mb-2">Mot de passe :</label>
                            <input type="password" id="delete_password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    @endif
                    
                    <div class="mb-4">
                        <label for="confirmation" class="block text-sm font-medium text-gray-800 mb-2">Tapez "DELETE" :</label>
                        <input type="text" id="confirmation" name="confirmation" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="DELETE">
                    </div>
                    
                    <div class="flex gap-3 mt-4">
                        <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 text-sm font-medium">
                            Annuler
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium">
                            Supprimer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression de photo -->
<div id="deleteConfirmationModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-200" style="backdrop-filter: blur(3px); background-color: rgba(0, 0, 0, 0.45);">
    <div class="min-h-full w-full flex items-end sm:items-center justify-center p-3">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl p-4 sm:p-6 w-full sm:max-w-md border border-red-200 transform transition-all duration-200 scale-95 opacity-0 modal-show">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-50">
                    <svg class="h-7 w-7 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mt-3">Confirmation de suppression</h3>
                <p class="text-gray-600 mt-2 text-sm">
                    Êtes-vous sûr de vouloir supprimer 
                </p>
                <p id="photoTitle" class="text-base font-semibold text-blue-800 mt-2"></p>
                <div class="mt-5 flex flex-col sm:flex-row gap-3">
                    <button type="button" onclick="closeDeletePhotoModal()" class="flex-1 px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">
                        Annuler
                    </button>
                    <button type="button" onclick="confirmDeletePhoto()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 font-medium">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Character counter for description
    const description = document.getElementById('description');
    const charCount = document.getElementById('char-count');
    
    if (description && charCount) {
        description.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = `${length} / 200 caractères`;
            
            if (length >= 200) {
                charCount.classList.add('text-green-600');
                charCount.classList.remove('text-purple-600');
            } else {
                charCount.classList.add('text-purple-600');
                charCount.classList.remove('text-green-600');
            }
        });
        
        // Initialize character count
        description.dispatchEvent(new Event('input'));
    }
    
    // Delete photo function
    function deletePhoto() {
        // Set the photo title for the modal
        document.getElementById('photoTitle').textContent = 'votre photo de profil';
        // Show the modal
        const deleteModal = document.getElementById('deleteConfirmationModal');
        deleteModal.classList.remove('hidden');
        
        // Add animation classes
        setTimeout(() => {
            deleteModal.classList.remove('opacity-0');
            const modalContent = deleteModal.querySelector('.modal-show');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
            modalContent.classList.remove('opacity-0');
        }, 10);
    }
    
    // Function to close photo deletion modal with animation
    function closeDeletePhotoModal() {
        const deleteModal = document.getElementById('deleteConfirmationModal');
        const modalContent = deleteModal.querySelector('.modal-show');
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');
        modalContent.classList.add('opacity-0');
        deleteModal.classList.add('opacity-0');
        
        setTimeout(() => {
            deleteModal.classList.add('hidden');
        }, 300);
    }
    
    // Function to confirm deletion
    function confirmDeletePhoto() {
        // Create a form to send DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("prestataire.profile.delete-photo") }}';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        form.appendChild(csrfInput);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }
    
    // Password visibility toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Password visibility toggle functionality
        const togglePasswordButtons = document.querySelectorAll('.toggle-password');
        
        togglePasswordButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('svg');
                const eyeOpen = this.querySelectorAll('.eye-open');
                const eyeClosed = this.querySelector('.eye-closed');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeOpen.forEach(el => el.classList.add('hidden'));
                    eyeClosed.classList.remove('hidden');
                } else {
                    passwordInput.type = 'password';
                    eyeOpen.forEach(el => el.classList.remove('hidden'));
                    eyeClosed.classList.add('hidden');
                }
            });
        });

        // Add password validation feedback
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('new_password_confirmation');
        
        if (newPasswordInput) {
            newPasswordInput.addEventListener('input', function() {
                const password = this.value;
                const allRequirementsMet = updatePasswordRequirements(password);
            });
        }
        
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                const confirmPassword = this.value;
                const password = newPasswordInput ? newPasswordInput.value : '';
                const allRequirementsMet = updatePasswordRequirements(password);
            });
        }
        
        // Fermer le modal de suppression de photo en cliquant à l'extérieur
        const deletePhotoModal = document.getElementById('deleteConfirmationModal');
        if (deletePhotoModal) {
            deletePhotoModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeDeletePhotoModal();
                }
            });
        }
    });

    // Update password requirements display
    function updatePasswordRequirements(password) {
        // Get the requirements container
        const requirementsContainer = document.querySelector('.password-requirements');
        if (!requirementsContainer) return false;
        
        // Check each requirement
        const hasMinLength = password.length >= 8;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumber = /\d/.test(password);
        
        // Update the requirement items
        const requirements = requirementsContainer.querySelectorAll('li');
        if (requirements.length >= 4) {
            // Minimum length
            const lengthIcon = requirements[0].querySelector('.requirement-icon');
            const lengthText = requirements[0].querySelector('.requirement-text');
            if (hasMinLength) {
                lengthIcon.innerHTML = '<i class="fas fa-check text-green-500"></i>';
                lengthIcon.className = 'requirement-icon requirement-met mr-2';
                lengthText.classList.remove('text-gray-500');
                lengthText.classList.add('text-green-600');
            } else {
                lengthIcon.innerHTML = '<i class="fas fa-times text-red-500"></i>';
                lengthIcon.className = 'requirement-icon requirement-not-met mr-2';
                lengthText.classList.remove('text-green-600');
                lengthText.classList.add('text-gray-500');
            }
            
            // Uppercase letter
            const upperIcon = requirements[1].querySelector('.requirement-icon');
            const upperText = requirements[1].querySelector('.requirement-text');
            if (hasUpperCase) {
                upperIcon.innerHTML = '<i class="fas fa-check text-green-500"></i>';
                upperIcon.className = 'requirement-icon requirement-met mr-2';
                upperText.classList.remove('text-gray-500');
                upperText.classList.add('text-green-600');
            } else {
                upperIcon.innerHTML = '<i class="fas fa-times text-red-500"></i>';
                upperIcon.className = 'requirement-icon requirement-not-met mr-2';
                upperText.classList.remove('text-green-600');
                upperText.classList.add('text-gray-500');
            }
            
            // Lowercase letter
            const lowerIcon = requirements[2].querySelector('.requirement-icon');
            const lowerText = requirements[2].querySelector('.requirement-text');
            if (hasLowerCase) {
                lowerIcon.innerHTML = '<i class="fas fa-check text-green-500"></i>';
                lowerIcon.className = 'requirement-icon requirement-met mr-2';
                lowerText.classList.remove('text-gray-500');
                lowerText.classList.add('text-green-600');
            } else {
                lowerIcon.innerHTML = '<i class="fas fa-times text-red-500"></i>';
                lowerIcon.className = 'requirement-icon requirement-not-met mr-2';
                lowerText.classList.remove('text-green-600');
                lowerText.classList.add('text-gray-500');
            }
            
            // Number
            const numberIcon = requirements[3].querySelector('.requirement-icon');
            const numberText = requirements[3].querySelector('.requirement-text');
            if (hasNumber) {
                numberIcon.innerHTML = '<i class="fas fa-check text-green-500"></i>';
                numberIcon.className = 'requirement-icon requirement-met mr-2';
                numberText.classList.remove('text-gray-500');
                numberText.classList.add('text-green-600');
            } else {
                numberIcon.innerHTML = '<i class="fas fa-times text-red-500"></i>';
                numberIcon.className = 'requirement-icon requirement-not-met mr-2';
                numberText.classList.remove('text-green-600');
                numberText.classList.add('text-gray-500');
            }
        }
        
        // Return whether all requirements are met
        return hasMinLength && hasUpperCase && hasLowerCase && hasNumber;
    }
    
    // Ensure functions are available in global scope
    function openDeleteModal() {
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.getElementById('deleteForm').reset();
    }

    // ==========================================
    // AUTOCOMPLETION ADRESSE & GÉOLOCALISATION
    // ==========================================
    
    let autocompleteTimeout = null;
    const addressInput = document.getElementById('address');
    const addressDropdown = document.getElementById('address-dropdown');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const geolocBtn = document.getElementById('geoloc_btn');

    // Autocomplétion de l'adresse avec l'API gouvernementale
    if (addressInput && addressDropdown) {
        addressInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query.length < 3) {
                addressDropdown.style.display = 'none';
                return;
            }
            
            // Debounce
            if (autocompleteTimeout) {
                clearTimeout(autocompleteTimeout);
            }
            
            autocompleteTimeout = setTimeout(() => {
                // Afficher un indicateur de chargement
                addressDropdown.innerHTML = '<div class="autocomplete-item" style="color: #94a3b8; text-align: center;"><i class="fas fa-spinner fa-spin"></i> Recherche...</div>';
                addressDropdown.style.display = 'block';
                
                fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&limit=6&autocomplete=1`)
                    .then(response => response.json())
                    .then(data => {
                        addressDropdown.innerHTML = '';
                        
                        if (data.features && data.features.length > 0) {
                            data.features.forEach(feature => {
                                const props = feature.properties;
                                const coords = feature.geometry.coordinates;
                                
                                const item = document.createElement('div');
                                item.className = 'autocomplete-item';
                                item.innerHTML = `
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="color: #3b82f6;">📍</span>
                                        <div>
                                            <div style="font-weight: 500; color: #1e293b;">${props.label}</div>
                                            ${props.postcode ? `<div style="font-size: 0.75rem; color: #64748b;">${props.postcode} ${props.city || ''}</div>` : ''}
                                        </div>
                                    </div>
                                `;
                                
                                item.addEventListener('click', function() {
                                    addressInput.value = props.label;
                                    if (latitudeInput) latitudeInput.value = coords[1];
                                    if (longitudeInput) longitudeInput.value = coords[0];
                                    addressDropdown.style.display = 'none';
                                    
                                    // Retirer le style orange si rempli
                                    addressInput.classList.remove('border-orange-300', 'bg-orange-50');
                                });
                                
                                addressDropdown.appendChild(item);
                            });
                            addressDropdown.style.display = 'block';
                        } else {
                            addressDropdown.innerHTML = '<div class="autocomplete-item" style="color: #94a3b8; text-align: center;">Aucun résultat trouvé</div>';
                        }
                    })
                    .catch(() => {
                        addressDropdown.style.display = 'none';
                    });
            }, 300);
        });
        
        // Fermer le dropdown quand on clique ailleurs
        document.addEventListener('click', function(e) {
            if (!addressInput.contains(e.target) && !addressDropdown.contains(e.target)) {
                addressDropdown.style.display = 'none';
            }
        });
    }

    // Géolocalisation
    if (geolocBtn) {
        geolocBtn.addEventListener('click', function() {
            if (!navigator.geolocation) {
                alert('La géolocalisation n\'est pas supportée par votre navigateur');
                return;
            }
            
            // Afficher le chargement
            geolocBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Localisation en cours...</span>';
            geolocBtn.disabled = true;
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    
                    // Reverse geocoding avec l'API gouvernementale
                    fetch(`https://api-adresse.data.gouv.fr/reverse/?lon=${lon}&lat=${lat}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.features && data.features.length > 0) {
                                const addr = data.features[0].properties;
                                addressInput.value = addr.label;
                                if (latitudeInput) latitudeInput.value = lat;
                                if (longitudeInput) longitudeInput.value = lon;
                                
                                // Retirer le style orange
                                addressInput.classList.remove('border-orange-300', 'bg-orange-50');
                            }
                            
                            geolocBtn.innerHTML = '<i class="fas fa-check text-white"></i><span>Position trouvée !</span>';
                            geolocBtn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                            
                            setTimeout(() => {
                                geolocBtn.innerHTML = '<i class="fas fa-location-arrow"></i><span>Utiliser ma position actuelle</span>';
                                geolocBtn.style.background = 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)';
                                geolocBtn.disabled = false;
                            }, 2000);
                        })
                        .catch(() => {
                            geolocBtn.innerHTML = '<i class="fas fa-location-arrow"></i><span>Utiliser ma position actuelle</span>';
                            geolocBtn.disabled = false;
                        });
                },
                function(error) {
                    geolocBtn.innerHTML = '<i class="fas fa-location-arrow"></i><span>Utiliser ma position actuelle</span>';
                    geolocBtn.disabled = false;
                    
                    if (error.code === 1) {
                        alert('Veuillez autoriser l\'accès à votre position dans les paramètres de votre navigateur');
                    } else {
                        alert('Impossible d\'obtenir votre position. Veuillez réessayer.');
                    }
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    }

    // ==========================================
    // CHARGEMENT DES SOUS-CATÉGORIES
    // ==========================================
    
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');
    const currentSubcategoryId = {!! Js::from(old('subcategory_id', $prestataire->subcategory_id ?? '')) !!};

    function loadSubcategories(categoryId, selectedSubcategoryId = null) {
        if (!subcategorySelect) return;
        
        subcategorySelect.innerHTML = '<option value="">Chargement...</option>';
        
        if (!categoryId) {
            subcategorySelect.innerHTML = "<option value=\"\">Sélectionnez d'abord une catégorie</option>";
            return;
        }
        
        fetch(`/api/categories/${categoryId}/subcategories`)
            .then(response => response.json())
            .then(subcategories => {
                subcategorySelect.innerHTML = '<option value="">Sélectionnez une spécialité</option>';
                
                subcategories.forEach(sub => {
                    const option = document.createElement('option');
                    option.value = sub.id;
                    option.textContent = sub.name;
                    if (selectedSubcategoryId && sub.id == selectedSubcategoryId) {
                        option.selected = true;
                    }
                    subcategorySelect.appendChild(option);
                });
            })
            .catch(() => {
                subcategorySelect.innerHTML = '<option value="">Erreur de chargement</option>';
            });
    }

    if (categorySelect) {
        // Charger les sous-catégories au changement
        categorySelect.addEventListener('change', function() {
            loadSubcategories(this.value);
        });
        
        // Charger les sous-catégories initiales si une catégorie est déjà sélectionnée
        if (categorySelect.value) {
            loadSubcategories(categorySelect.value, currentSubcategoryId);
        }
    }

</script>
@endpush
