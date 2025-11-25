@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl w-full bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Grid layout for login form and logo -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
            <!-- Left side: Login form -->
            <div class="p-8 sm:p-10 md:p-12 lg:p-16 flex flex-col justify-center">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">
                        Connectez-vous à votre compte
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Ou <a href="/register" class="font-medium text-blue-600 hover:text-blue-500 underline">créez un nouveau compte</a>
                    </p>
                </div>
                
                @if (session('status'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-6" role="alert">
                        <span class="block sm:inline">{{ session('status') }}</span>
                    </div>
                @endif
                
                <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="rounded-md shadow-sm space-y-4">
                        <div>
                            <label for="email-address" class="sr-only">Adresse e-mail</label>
                            <input id="email-address" name="email" type="email" autocomplete="email" required class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm @error('email') border-red-500 @enderror" placeholder="Adresse e-mail" value="{{ old('email') }}">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="relative">
                            <label for="password" class="sr-only">Mot de passe</label>
                            <input id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none relative block w-full px-4 py-3 pr-12 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Mot de passe">
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center toggle-password" data-target="password">
                                <i class="fas fa-eye text-gray-500 hover:text-gray-700"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-end">
                        <div class="text-sm">
                            <a href="{{ route('password.request') }}" class="font-medium text-blue-600 hover:text-blue-500 underline">
                                Mot de passe oublié ?
                            </a>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            Se connecter
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Right side: Logo/Visual element -->
            <div class="hidden lg:flex items-center justify-center bg-gradient-to-br from-blue-50 to-white p-8 md:p-12 lg:p-16">
                <!-- Logo container with enhanced styling -->
                <div class="bg-gradient-to-br from-blue-50 to-white rounded-3xl p-8 w-full max-w-md flex items-center justify-center aspect-square relative">
                    <!-- Halo effect behind the logo -->
                    <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-blue-200/20 to-transparent blur-xl"></div>
                    
                    <div class="text-center relative z-10">
                        <div class="w-32 h-32 sm:w-40 sm:h-40 md:w-48 md:h-48 mx-auto flex items-center justify-center mb-6">
                            <!-- Circular background with gradient -->
                            <div class="absolute w-40 h-40 sm:w-48 sm:h-48 md:w-56 md:h-56 rounded-full bg-gradient-to-br from-blue-100 to-blue-50 opacity-80"></div>
                            
                            <!-- Main logo with floating effect -->
                            <div class="relative w-32 h-32 sm:w-40 sm:h-40 md:w-48 md:h-48 bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl flex items-center justify-center shadow-xl transform transition-transform duration-300">
                                <i class="fas fa-handshake text-white text-4xl sm:text-5xl md:text-6xl"></i>
                            </div>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">TaPrestation</h3>
                        <p class="text-sm sm:text-base text-gray-600 max-w-xs mx-auto">
                            Connectez-vous pour accéder à votre espace personnel
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password visibility toggle functionality
        const togglePasswordButtons = document.querySelectorAll('.toggle-password');
        
        togglePasswordButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (passwordInput && icon) {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }
            });
        });
    });
</script>
@endpush
@endsection