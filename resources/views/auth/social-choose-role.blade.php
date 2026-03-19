@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 px-4 py-6">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6 sm:p-8">
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl shadow-lg mb-4">
                <i class="fas fa-user-plus text-white text-2xl"></i>
            </div>
            <h1 class="text-xl font-bold text-gray-800">Bienvenue {{ $socialUser['name'] }} !</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $socialUser['email'] }}</p>
        </div>

        <!-- Message d'info -->
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-info-circle text-amber-600"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-amber-800 text-sm">Vous n'êtes pas encore inscrit</h4>
                    <p class="text-amber-700 text-xs mt-1">
                        Aucun compte n'existe avec cet email. Vous pouvez créer un compte automatiquement via Google.
                    </p>
                </div>
            </div>
        </div>

        <!-- Choix du type de compte -->
        <div class="mb-6">
            <p class="text-center text-gray-600 text-sm mb-4 font-medium">Quel type de compte souhaitez-vous créer ?</p>
            
            <div class="space-y-3">
                <!-- Option Client -->
                <form action="{{ route('social.create-account') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="client">
                    <input type="hidden" name="provider" value="{{ $provider }}">
                    <button type="submit" class="w-full flex items-center gap-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl hover:border-blue-400 hover:shadow-md transition-all group">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-200 transition">
                            <i class="fas fa-user text-blue-600 text-xl"></i>
                        </div>
                        <div class="text-left flex-1">
                            <h3 class="font-bold text-gray-800">Client</h3>
                            <p class="text-xs text-gray-500">Je recherche des prestataires pour mes besoins</p>
                        </div>
                        <i class="fas fa-arrow-right text-blue-400 group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </form>

                <!-- Option Prestataire -->
                <form action="{{ route('social.create-account') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="prestataire">
                    <input type="hidden" name="provider" value="{{ $provider }}">
                    <button type="submit" class="w-full flex items-center gap-4 p-4 bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-amber-200 rounded-xl hover:border-amber-400 hover:shadow-md transition-all group">
                        <div class="flex-shrink-0 w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center group-hover:bg-amber-200 transition">
                            <i class="fas fa-briefcase text-amber-600 text-xl"></i>
                        </div>
                        <div class="text-left flex-1">
                            <h3 class="font-bold text-gray-800">Prestataire</h3>
                            <p class="text-xs text-gray-500">Je propose mes services professionnels</p>
                        </div>
                        <i class="fas fa-arrow-right text-amber-400 group-hover:translate-x-1 transition-transform"></i>
                    </button>
                </form>
                <p class="text-xs text-center text-gray-400 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>Vous compléterez votre profil sur la page suivante
                </p>
            </div>
        </div>

        <!-- Séparateur -->
        <div class="flex items-center gap-3 mb-4">
            <div class="flex-1 h-px bg-gray-200"></div>
            <span class="text-xs text-gray-400">ou</span>
            <div class="flex-1 h-px bg-gray-200"></div>
        </div>

        <!-- Retour connexion -->
        <div class="text-center">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition">
                <i class="fas fa-arrow-left"></i>
                Retour à la connexion
            </a>
        </div>
    </div>
</div>
@endsection
