@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-6 sm:py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl w-full bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Grid layout for verification form and logo -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
            <!-- Left side: Verification form -->
            <div class="p-6 sm:p-8 md:p-10 lg:p-12 xl:p-16 flex flex-col justify-center">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">
                        Vérifiez votre adresse e-mail
                    </h2>
                    @if (!session('verification_sent') && !session('resent'))
                        <p class="mt-2 text-sm text-gray-600">
                            Merci de vous être inscrit ! Pour continuer, veuillez vérifier votre adresse e-mail. Cliquez sur le bouton ci-dessous pour recevoir un e-mail de vérification.
                        </p>
                    @else
                        <p class="mt-2 text-sm text-gray-600">
                            <strong>L'e-mail de vérification a été envoyé !</strong> Veuillez vérifier votre boîte de réception et cliquez sur le lien de vérification dans l'e-mail.
                        </p>
                    @endif
                </div>

                @if (!session('verification_sent') && !session('resent'))
                    <div class="mt-6 sm:mt-8 bg-blue-50 p-4 sm:p-6 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <strong>Pour recevoir l'e-mail de vérification :</strong> Cliquez sur le bouton ci-dessous.
                        </p>
                        
                        <form class="mt-4 sm:mt-6" method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="group relative w-full flex justify-center py-2 px-4 sm:py-3 sm:px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M2.94 6.412A2 2 0 002 8.108V16a2 2 0 002 2h12a2 2 0 002-2V8.108a2 2 0 00-.94-1.696l-6-3.75a2 2 0 00-2.12 0l-6 3.75zm2.615 2.423a1 1 0 10-1.11 1.664l5 3.333a1 1 0 001.11 0l5-3.333a1 1 0 00-1.11-1.664L10 12.354 5.555 8.835z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                Envoyer l'e-mail de vérification
                            </button>
                        </form>
                    </div>
                @else
                    <div class="mt-6 sm:mt-8 bg-green-50 p-4 sm:p-6 rounded-lg">
                        <p class="text-sm text-green-800">
                            Un e-mail de vérification a été envoyé à votre adresse e-mail.
                        </p>
                    </div>
                @endif
                
                <div class="mt-4 sm:mt-6 text-center">
                    <a href="{{ route('logout') }}" 
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                       class="font-medium text-blue-600 hover:text-blue-500 underline">
                        Se déconnecter
                    </a>
                    
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
            
            <!-- Right side: Logo/Visual element -->
            <div class="hidden lg:flex items-center justify-center bg-gradient-to-br from-blue-50 to-white p-8 md:p-12 lg:p-16">
                <!-- Logo container with enhanced styling -->
                <div class="bg-gradient-to-br from-blue-50 to-white rounded-3xl p-6 sm:p-8 w-full max-w-md flex items-center justify-center aspect-square relative">
                    <!-- Halo effect behind the logo -->
                    <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-blue-200/20 to-transparent blur-xl"></div>
                    
                    <div class="text-center relative z-10">
                        <div class="w-24 h-24 sm:w-32 sm:h-32 md:w-40 md:h-40 mx-auto flex items-center justify-center mb-4 sm:mb-6">
                            <!-- Circular background with gradient -->
                            <div class="absolute w-32 h-32 sm:w-40 sm:h-40 md:w-48 md:h-48 rounded-full bg-gradient-to-br from-blue-100 to-blue-50 opacity-80"></div>
                            
                            <!-- Main logo with floating effect -->
                            <div class="relative w-24 h-24 sm:w-32 sm:h-32 md:w-40 md:h-40 bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl flex items-center justify-center shadow-xl transform transition-transform duration-300">
                                <i class="fas fa-envelope text-white text-2xl sm:text-3xl md:text-4xl lg:text-5xl"></i>
                            </div>
                        </div>
                        <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mb-2">TaPrestation</h3>
                        <p class="text-xs sm:text-sm md:text-base text-gray-600 max-w-xs mx-auto">
                            Vérifiez votre e-mail pour continuer
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection