@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-6 sm:py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl w-full bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Grid layout for password reset form and logo -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
            <!-- Left side: Password reset form -->
            <div class="p-6 sm:p-8 md:p-10 lg:p-12 xl:p-16 flex flex-col justify-center">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">
                        Réinitialisation du mot de passe
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Créez un nouveau mot de passe pour votre compte
                    </p>
                </div>

                <form class="mt-6 sm:mt-8 space-y-4 sm:space-y-6" action="{{ route('password.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="rounded-md shadow-sm space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Adresse e-mail</label>
                            <div class="mt-1">
                                <input id="email" name="email" type="email" autocomplete="email" required value="{{ $email ?? old('email') }}" class="appearance-none relative block w-full px-3 py-2 sm:px-4 sm:py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm @error('email') border-red-500 @enderror" placeholder="Votre adresse e-mail">
                            </div>
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="relative">
                            <label for="password" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                            <div class="mt-1">
                                <input id="password" name="password" type="password" autocomplete="new-password" required class="appearance-none relative block w-full px-3 py-2 sm:px-4 sm:py-3 pr-10 sm:pr-12 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm @error('password') border-red-500 @enderror" placeholder="Nouveau mot de passe">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center toggle-password" data-target="password">
                                    <i class="fas fa-eye text-gray-500 hover:text-gray-700"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="relative">
                            <label for="password-confirm" class="block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
                            <div class="mt-1">
                                <input id="password-confirm" name="password_confirmation" type="password" autocomplete="new-password" required class="appearance-none relative block w-full px-3 py-2 sm:px-4 sm:py-3 pr-10 sm:pr-12 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Confirmer le mot de passe">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center toggle-password" data-target="password-confirm">
                                    <i class="fas fa-eye text-gray-500 hover:text-gray-700"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-2 px-4 sm:py-3 sm:px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-blue-500 group-hover:text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            Réinitialiser le mot de passe
                        </button>
                    </div>
                </form>
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
                                <i class="fas fa-lock text-white text-2xl sm:text-3xl md:text-4xl lg:text-5xl"></i>
                            </div>
                        </div>
                        <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mb-2">TaPrestation</h3>
                        <p class="text-xs sm:text-sm md:text-base text-gray-600 max-w-xs mx-auto">
                            Créez un nouveau mot de passe sécurisé
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