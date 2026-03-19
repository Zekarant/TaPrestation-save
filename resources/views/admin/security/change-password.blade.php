@extends('layouts.admin-modern')

@section('title', 'Changer le mot de passe')
@section('page-title', 'Changer le mot de passe')

@section('content')
<div class="page-header">
    <h1 class="page-title">🔐 Changer le mot de passe</h1>
    <p class="page-subtitle">Modifiez votre mot de passe administrateur</p>
</div>

<div class="max-w-xl mx-auto">
    <div class="card-base">
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-3">
                <i class="fas fa-check-circle text-green-500"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <div class="flex items-center gap-3 mb-2">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                    <span class="font-medium">Erreur(s) :</span>
                </div>
                <ul class="list-disc list-inside ml-6">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.security.change-password.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Mot de passe actuel -->
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-gray-400"></i>
                    Mot de passe actuel
                </label>
                <div class="relative">
                    <input type="password" 
                           name="current_password" 
                           id="current_password" 
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition"
                           placeholder="Entrez votre mot de passe actuel">
                    <button type="button" onclick="togglePassword('current_password', this)" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Nouveau mot de passe -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-key mr-2 text-gray-400"></i>
                    Nouveau mot de passe
                </label>
                <div class="relative">
                    <input type="password" 
                           name="password" 
                           id="password" 
                           required
                           minlength="8"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition"
                           placeholder="Entrez votre nouveau mot de passe">
                    <button type="button" onclick="togglePassword('password', this)" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Minimum 8 caractères
                </p>
            </div>

            <!-- Confirmer le nouveau mot de passe -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-check-double mr-2 text-gray-400"></i>
                    Confirmer le nouveau mot de passe
                </label>
                <div class="relative">
                    <input type="password" 
                           name="password_confirmation" 
                           id="password_confirmation" 
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition"
                           placeholder="Confirmez votre nouveau mot de passe">
                    <button type="button" onclick="togglePassword('password_confirmation', this)" 
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Indicateur de force du mot de passe -->
            <div id="password-strength" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Force du mot de passe</label>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="strength-bar" class="h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <p id="strength-text" class="mt-1 text-sm"></p>
            </div>

            <!-- Boutons -->
            <div class="flex items-center justify-between pt-4 border-t">
                <a href="{{ route('admin.security.dashboard') }}" class="text-gray-600 hover:text-gray-800 transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Retour
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    Modifier le mot de passe
                </button>
            </div>
        </form>
    </div>

    <!-- Conseils de sécurité -->
    <div class="mt-6 card-base bg-blue-50 border border-blue-100">
        <h3 class="font-semibold text-blue-800 mb-3 flex items-center gap-2">
            <i class="fas fa-shield-alt"></i>
            Conseils de sécurité
        </h3>
        <ul class="text-sm text-blue-700 space-y-2">
            <li class="flex items-start gap-2">
                <i class="fas fa-check text-blue-500 mt-0.5"></i>
                Utilisez un mot de passe unique que vous n'utilisez nulle part ailleurs
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-check text-blue-500 mt-0.5"></i>
                Mélangez majuscules, minuscules, chiffres et caractères spéciaux
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-check text-blue-500 mt-0.5"></i>
                Évitez les informations personnelles (nom, date de naissance, etc.)
            </li>
            <li class="flex items-start gap-2">
                <i class="fas fa-check text-blue-500 mt-0.5"></i>
                Changez régulièrement votre mot de passe
            </li>
        </ul>
    </div>
</div>

<script>
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Indicateur de force du mot de passe
document.getElementById('password').addEventListener('input', function(e) {
    const password = e.target.value;
    const strengthDiv = document.getElementById('password-strength');
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');
    
    if (password.length === 0) {
        strengthDiv.classList.add('hidden');
        return;
    }
    
    strengthDiv.classList.remove('hidden');
    
    let strength = 0;
    if (password.length >= 8) strength += 25;
    if (password.length >= 12) strength += 15;
    if (/[a-z]/.test(password)) strength += 15;
    if (/[A-Z]/.test(password)) strength += 15;
    if (/[0-9]/.test(password)) strength += 15;
    if (/[^a-zA-Z0-9]/.test(password)) strength += 15;
    
    strengthBar.style.width = strength + '%';
    
    if (strength < 30) {
        strengthBar.className = 'h-2 rounded-full transition-all duration-300 bg-red-500';
        strengthText.textContent = 'Faible';
        strengthText.className = 'mt-1 text-sm text-red-600';
    } else if (strength < 60) {
        strengthBar.className = 'h-2 rounded-full transition-all duration-300 bg-yellow-500';
        strengthText.textContent = 'Moyen';
        strengthText.className = 'mt-1 text-sm text-yellow-600';
    } else if (strength < 80) {
        strengthBar.className = 'h-2 rounded-full transition-all duration-300 bg-blue-500';
        strengthText.textContent = 'Bon';
        strengthText.className = 'mt-1 text-sm text-blue-600';
    } else {
        strengthBar.className = 'h-2 rounded-full transition-all duration-300 bg-green-500';
        strengthText.textContent = 'Excellent';
        strengthText.className = 'mt-1 text-sm text-green-600';
    }
});
</script>
@endsection
