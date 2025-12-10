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

            .space-x-md-0> :not([hidden])~ :not([hidden]) {
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

            .mobile-space-y-3> :not([hidden])~ :not([hidden]) {
                --tw-space-y-reverse: 0;
                margin-top: calc(0.75rem * calc(1 - var(--tw-space-y-reverse)));
                margin-bottom: calc(0.75rem * var(--tw-space-y-reverse));
            }

            .mobile-space-x-0> :not([hidden])~ :not([hidden]) {
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

            .space-y-sm-4> :not([hidden])~ :not([hidden]) {
                --tw-space-y-reverse: 0;
                margin-top: calc(1rem * calc(1 - var(--tw-space-y-reverse)));
                margin-bottom: calc(1rem * var(--tw-space-y-reverse));
            }

            .space-x-sm-0> :not([hidden])~ :not([hidden]) {
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

            #deleteModal .space-y-sm-3> :not([hidden])~ :not([hidden]) {
                --tw-space-y-reverse: 0;
                margin-top: calc(0.75rem * calc(1 - var(--tw-space-y-reverse)));
                margin-bottom: calc(0.75rem * var(--tw-space-y-reverse));
            }

            #deleteModal .space-x-sm-0> :not([hidden])~ :not([hidden]) {
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
    <div class="bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6 sm:mb-8">
                <div class="text-center mb-6 sm:mb-8 fade-in-up">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 mb-2">Mon Profil</h1>
                    <p class="text-lg sm:text-xl text-gray-700 px-4">Gérez vos informations personnelles et vos préférences
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
                <div class="lg:col-span-3">
                    <div class="profile-card bg-white rounded-xl shadow-lg border border-gray-200 p-4 sm:p-6">
                        @if ($errors->any())
                            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative">
                                <ul class="list-disc list-inside text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative">
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- Profil utilisateur -->
                        <div class="profile-card bg-white rounded-xl shadow-lg border border-gray-200 p-4 sm:p-6 mb-6">
                            <div class="flex items-center gap-3 sm:gap-6 mb-4 sm:mb-6">
                                <div class="flex-shrink-0">
                                    <div
                                        class="flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full">
                                        @if($prestataire && $prestataire->photo)
                                            <img class="h-full w-full rounded-full object-cover shadow-lg"
                                                src="{{ asset('storage/' . $prestataire->photo) }}" alt="Photo de profil">
                                        @else
                                            <span
                                                class="text-xl sm:text-2xl font-medium text-gray-700">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ auth()->user()->name }}</h2>
                                    <div class="text-sm text-gray-700 mb-2 sm:mb-4">
                                        {{ auth()->user()->email }}
                                        @if(auth()->user()->email_verified_at)
                                            <svg class="inline-block w-4 h-4 text-green-700" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @else
                                            <svg class="inline-block w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <span
                                        class="text-sm @if(auth()->user()->email_verified_at) text-green-700 font-medium @else text-gray-500 @endif">Email
                                        vérifié</span>
                                </div>
                            </div>
                        </div>

                        <!-- Call to action for services -->
                        @if(!$prestataire || $prestataire->services()->count() == 0)
                            <div
                                class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-xl shadow-lg p-4 sm:p-6 mb-6">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="flex items-center justify-center w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-lg font-bold text-purple-900 mb-1">Ajoutez vos services</h4>
                                        <p class="text-sm text-purple-700 mb-3">Pour améliorer votre profil et attirer plus de
                                            clients, ajoutez au moins un service que vous proposez.</p>
                                        <a href="{{ route('prestataire.services.create') }}"
                                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-sm font-bold rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-all duration-200 shadow-sm">
                                            Ajouter un service
                                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Informations personnelles -->
                        <div class="profile-card bg-white rounded-xl shadow-lg border border-gray-200 p-4 sm:p-6 mb-6">
                            <div class="section-header">
                                <div class="stat-icon bg-gray-50">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="section-title text-gray-900">Informations personnelles</h3>
                                    <p class="text-sm text-gray-700">Ces informations seront visibles par les clients.</p>
                                </div>
                            </div>

                            <form action="{{ route('prestataire.profile.update.personal') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="mt-5 md:mt-0">
                                    <div class="grid grid-cols-1 sm:grid-cols-6 gap-4 sm:gap-6 form-grid">
                                        <!-- Photo -->
                                        <div class="col-span-1 sm:col-span-6 form-col-span">
                                            <label class="block text-sm font-semibold text-gray-700 mb-2 mobile-mb-2">Photo
                                                de profil</label>
                                            <div
                                                class="mt-1 flex flex-col sm:flex-row items-start sm:items-center space-y-4 sm:space-y-0 sm:space-x-5 mobile-stack">
                                                @if($prestataire && $prestataire->photo)
                                                    <img class="h-20 w-20 rounded-full object-cover shadow-lg"
                                                        src="{{ asset('storage/' . $prestataire->photo) }}"
                                                        alt="Photo actuelle">
                                                @else
                                                    <div
                                                        class="h-20 w-20 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center shadow-lg">
                                                        <span
                                                            class="text-xl font-medium text-gray-700">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                                    </div>
                                                @endif
                                                <div class="flex flex-col space-y-2 w-full sm:w-auto mobile-full-width">
                                                    <input type="file" name="photo" id="photo" accept="image/*"
                                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-600 hover:file:bg-gray-100 mobile-text-sm">
                                                    <p class="mt-1 text-xs text-gray-600 mobile-text-sm">Format recommandé :
                                                        JPEG, PNG. Taille max : 2MB</p>
                                                    @if($prestataire && $prestataire->photo)
                                                        <button type="button" onclick="deletePhoto()"
                                                            class="delete-avatar-btn">Supprimer la photo</button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Informations de base -->
                                        <div class="col-span-1 sm:col-span-3 form-col-span">
                                            <label for="name"
                                                class="block text-sm font-semibold text-gray-700 mb-2 mobile-mb-2">Nom
                                                complet</label>
                                            <input type="text" name="name" id="name"
                                                value="{{ old('name', auth()->user()->name) }}"
                                                class="mt-1 focus:ring-gray-500 focus:border-gray-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-lg px-4 py-3 transition duration-200 mobile-p-3 mobile-text-sm"
                                                required>
                                        </div>

                                        <!-- Email -->
                                        <div class="col-span-1 sm:col-span-3 form-col-span">
                                            <label for="email"
                                                class="block text-sm font-semibold text-gray-700 mb-2 mobile-mb-2">Email</label>
                                            <input type="email" name="email" id="email"
                                                value="{{ old('email', auth()->user()->email) }}"
                                                class="mt-1 focus:ring-gray-500 focus:border-gray-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-lg px-4 py-3 transition duration-200 mobile-p-3 mobile-text-sm"
                                                required>
                                        </div>

                                        <!-- Téléphone -->
                                        <div class="col-span-1 sm:col-span-3 form-col-span">
                                            <label for="phone"
                                                class="block text-sm font-semibold text-gray-700 mb-2 mobile-mb-2">Téléphone</label>
                                            <input type="tel" name="phone" id="phone"
                                                value="{{ old('phone', $prestataire->phone ?? '') }}"
                                                class="mt-1 focus:ring-gray-500 focus:border-gray-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-lg px-4 py-3 transition duration-200 mobile-p-3 mobile-text-sm">
                                        </div>

                                        <!-- Présentation / Biographie -->
                                        <div class="col-span-1 sm:col-span-6 form-col-span">
                                            <label for="description"
                                                class="block text-sm font-semibold text-gray-700 mb-2 mobile-mb-2">Présentation
                                                professionnelle</label>
                                            <textarea name="description" id="description" rows="4"
                                                class="mt-1 focus:ring-gray-500 focus:border-gray-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-lg px-4 py-3 transition duration-200 mobile-p-3 mobile-text-sm"
                                                placeholder="Présentez votre expertise, votre expérience, vos points forts et votre manière de travailler...">{{ old('description', $prestataire->description ?? '') }}</textarea>
                                            <p class="mt-2 text-sm text-gray-600 mobile-text-sm mobile-mt-4">Décrivez votre
                                                expertise, votre expérience et ce qui vous différencie. Minimum 200
                                                caractères.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bouton d'action pour les informations personnelles -->
                                <div
                                    class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 mt-6 mobile-stack">
                                    <button type="submit"
                                        class="inline-flex justify-center py-3 px-6 border border-transparent shadow-lg text-base font-bold rounded-lg text-white bg-gray-600 hover:bg-gray-700 transition duration-200 w-full sm:w-auto z-10 relative mobile-full-width">
                                        Enregistrer les informations personnelles
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Sécurité du compte -->
                        <div class="profile-card bg-white rounded-xl shadow-lg border border-gray-200 p-4 sm:p-6">
                            <div class="section-header">
                                <div class="stat-icon bg-gray-50">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="section-title text-gray-900">Sécurité du compte</h3>
                                    <p class="text-sm text-gray-700">Modifiez votre mot de passe pour renforcer la sécurité
                                        de votre compte.</p>
                                </div>
                            </div>

                            <form action="{{ route('prestataire.profile.update.security') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mt-5 md:mt-0">
                                    <div class="grid grid-cols-1 sm:grid-cols-6 gap-4 sm:gap-6 form-grid">
                                        <!-- Mot de passe actuel -->
                                        <div class="col-span-1 sm:col-span-6 form-col-span">
                                            <label for="current_password"
                                                class="block text-sm font-semibold text-gray-700 mb-2 mobile-mb-2">Mot de
                                                passe actuel</label>
                                            <input type="password" name="current_password" id="current_password"
                                                class="mt-1 focus:ring-gray-500 focus:border-gray-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-lg px-4 py-3 transition duration-200 mobile-p-3 mobile-text-sm"
                                                required>
                                            <p class="mt-2 text-sm text-gray-600 mobile-text-sm mobile-mt-4">Entrez votre
                                                mot de passe actuel pour confirmer les modifications.</p>
                                        </div>

                                        <!-- Nouveau mot de passe -->
                                        <div class="col-span-1 sm:col-span-3 form-col-span">
                                            <label for="new_password"
                                                class="block text-sm font-semibold text-gray-700 mb-2 mobile-mb-2">Nouveau
                                                mot de passe</label>
                                            <div class="relative">
                                                <input type="password" name="new_password" id="new_password"
                                                    class="mt-1 focus:ring-gray-500 focus:border-gray-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-lg px-4 py-3 pr-10 transition duration-200 mobile-p-3 mobile-text-sm"
                                                    required>
                                                <button type="button"
                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center toggle-password"
                                                    data-target="new_password">
                                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path class="eye-open" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path class="eye-open" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        <path class="eye-closed hidden" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"
                                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <!-- Password requirements explanation -->
                                            <div class="password-requirements mt-2">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                    <div class="flex items-center text-sm">
                                                        <span class="requirement-icon requirement-not-met mr-2">
                                                            <i class="fas fa-times text-red-500"></i>
                                                        </span>
                                                        <span class="requirement-text">Au moins 8 caractères</span>
                                                    </div>
                                                    <div class="flex items-center text-sm">
                                                        <span class="requirement-icon requirement-not-met mr-2">
                                                            <i class="fas fa-times text-red-500"></i>
                                                        </span>
                                                        <span class="requirement-text">Au moins une lettre majuscule</span>
                                                    </div>
                                                    <div class="flex items-center text-sm">
                                                        <span class="requirement-icon requirement-not-met mr-2">
                                                            <i class="fas fa-times text-red-500"></i>
                                                        </span>
                                                        <span class="requirement-text">Au moins une lettre minuscule</span>
                                                    </div>
                                                    <div class="flex items-center text-sm">
                                                        <span class="requirement-icon requirement-not-met mr-2">
                                                            <i class="fas fa-times text-red-500"></i>
                                                        </span>
                                                        <span class="requirement-text">Au moins un chiffre</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Confirmation du nouveau mot de passe -->
                                        <div class="col-span-1 sm:col-span-3 form-col-span">
                                            <label for="new_password_confirmation"
                                                class="block text-sm font-semibold text-gray-700 mb-2 mobile-mb-2">Confirmer
                                                le nouveau mot de passe</label>
                                            <div class="relative">
                                                <input type="password" name="new_password_confirmation"
                                                    id="new_password_confirmation"
                                                    class="mt-1 focus:ring-gray-500 focus:border-gray-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-lg px-4 py-3 pr-10 transition duration-200 mobile-p-3 mobile-text-sm"
                                                    required>
                                                <button type="button"
                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center toggle-password"
                                                    data-target="new_password_confirmation">
                                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path class="eye-open" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path class="eye-open" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        <path class="eye-closed hidden" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"
                                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bouton d'action pour la sécurité -->
                                <div
                                    class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 mt-6 mobile-stack">
                                    <button type="submit"
                                        class="inline-flex justify-center py-3 px-6 border border-transparent shadow-lg text-base font-bold rounded-lg text-white bg-gray-600 hover:bg-gray-700 transition duration-200 w-full sm:w-auto z-10 relative mobile-full-width">
                                        Mettre à jour le mot de passe
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Section Suppression du compte -->
                        <div class="profile-card bg-white rounded-xl shadow-lg border border-red-200 p-4 sm:p-6 mt-6">
                            <div class="section-header">
                                <div class="stat-icon bg-red-50">
                                    <svg class="h-5 w-5 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="section-title text-red-600">Zone dangereuse</h3>
                                    <p class="text-sm text-gray-700">Actions irréversibles concernant votre compte.</p>
                                </div>
                            </div>
                            <div class="mt-5">
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <div class="flex flex-col sm:flex-row mobile-stack">
                                        <div class="flex-shrink-0 mb-3 sm:mb-0">
                                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="sm:ml-4 flex-1">
                                            <h3 class="text-sm font-bold text-red-800">Supprimer définitivement mon compte
                                            </h3>
                                            <div class="mt-2 text-sm text-red-700">
                                                <p>Cette action est irréversible. Toutes vos données seront définitivement
                                                    supprimées :</p>
                                                <ul class="list-disc list-inside mt-2 space-y-1">
                                                    <li>Vos informations personnelles</li>
                                                    <li>Votre historique de services</li>
                                                    <li>Vos messages et conversations</li>
                                                    <li>Vos évaluations et avis</li>
                                                </ul>
                                            </div>
                                            <div class="mt-4">
                                                <button type="button" onclick="openDeleteModal()"
                                                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-bold transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 w-full sm:w-auto z-10 relative mobile-full-width">
                                                    Supprimer mon compte
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z" />
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

                        <div class="mb-4">
                            <label for="password" class="block text-md font-bold text-gray-800 mb-2">Saisissez votre mot de
                                passe :</label>
                            <input type="password" id="password" name="password" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition duration-200 mobile-p-3 mobile-text-sm">
                        </div>

                        <div class="mb-4">
                            <label for="confirmation" class="block text-md font-bold text-gray-800 mb-2">Tapez "DELETE" pour
                                confirmer :</label>
                            <input type="text" id="confirmation" name="confirmation" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition duration-200 mobile-p-3 mobile-text-sm"
                                placeholder="DELETE">
                        </div>

                        <div
                            class="flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-4 mt-6 mobile-stack mobile-space-y-3 mobile-space-x-0">
                            <button type="button" onclick="closeDeleteModal()"
                                class="px-6 py-3 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 transition-colors font-bold mobile-full-width">
                                Annuler
                            </button>
                            <button type="submit"
                                class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 mobile-full-width">
                                Supprimer définitivement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression de photo -->
    <div id="deleteConfirmationModal"
        class="fixed inset-0 flex items-center justify-center z-50 hidden transition-opacity duration-300"
        style="backdrop-filter: blur(5px); background-color: rgba(59, 130, 246, 0.8); display: none;">
        <div
            class="bg-white rounded-lg sm:rounded-xl shadow-2xl p-4 sm:p-6 md:p-8 max-w-xs sm:max-w-md w-full mx-4 border-2 sm:border-4 border-red-500 transform transition-all duration-300">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 sm:h-16 sm:w-16 rounded-full bg-red-100">
                    <svg class="h-6 w-6 sm:h-10 sm:w-10 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-base sm:text-xl font-bold text-gray-900 mt-3 sm:mt-4">Confirmation de suppression</h3>
                <p class="text-xs sm:text-gray-600 mt-1 sm:mt-2">
                    Êtes-vous sûr de vouloir supprimer
                </p>
                <p id="photoTitle" class="text-sm sm:text-lg font-semibold text-blue-900 mt-1 sm:mt-2"></p>
                <div class="mt-4 sm:mt-6 flex flex-col gap-2 sm:gap-3">
                    <button type="button" onclick="closeDeletePhotoModal()"
                        class="flex-1 px-3 py-2 sm:px-4 sm:py-2.5 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200 font-medium text-sm sm:text-base">
                        Annuler
                    </button>
                    <button type="button" onclick="confirmDeletePhoto()"
                        class="flex-1 px-3 py-2 sm:px-4 sm:py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 font-medium text-sm sm:text-base">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Character counter for description
    const description = document.getElementById('description');
    const charCount = document.getElementById('char-count');

    if (description && charCount) {
        description.addEventListener('input', function () {
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
        deleteModal.style.display = 'flex';
        deleteModal.classList.remove('hidden');
    }

    // Function to close photo deletion modal with animation
    function closeDeletePhotoModal() {
        const deleteModal = document.getElementById('deleteConfirmationModal');
        deleteModal.style.display = 'none';
        deleteModal.classList.add('hidden');
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
    document.addEventListener('DOMContentLoaded', function () {
        // Password visibility toggle functionality
        const togglePasswordButtons = document.querySelectorAll('.toggle-password');

        togglePasswordButtons.forEach(button => {
            button.addEventListener('click', function () {
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
            newPasswordInput.addEventListener('input', function () {
                const password = this.value;
                const allRequirementsMet = updatePasswordRequirements(password);
            });
        }

        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function () {
                const confirmPassword = this.value;
                const password = newPasswordInput ? newPasswordInput.value : '';
                const allRequirementsMet = updatePasswordRequirements(password);
            });
        }

        // Fermer le modal de suppression de photo en cliquant à l'extérieur
        const deletePhotoModal = document.getElementById('deleteConfirmationModal');
        if (deletePhotoModal) {
            deletePhotoModal.addEventListener('click', function (e) {
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

</script>