@extends('layouts.app')

@section('title', 'Contactez-nous')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-slate-900 mb-4">Contactez-nous</h1>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                Une question, une suggestion ou besoin d'aide ? Notre équipe est là pour vous accompagner.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Informations de contact -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Email -->
                <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-envelope text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2">Email</h3>
                    <p class="text-slate-600">contact@taprestation.com</p>
                    <a href="mailto:contact@taprestation.com" class="text-blue-600 hover:text-blue-700 text-sm font-medium mt-2 inline-block">
                        Envoyer un email →
                    </a>
                </div>

                <!-- Téléphone -->
                <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-phone text-2xl text-green-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2">Téléphone</h3>
                    <p class="text-slate-600">Support disponible 24/7</p>
                    <p class="text-green-600 font-medium mt-2">Bientôt disponible</p>
                </div>

                <!-- Réseaux sociaux -->
                <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-share-alt text-2xl text-purple-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Réseaux sociaux</h3>
                    <div class="flex space-x-3">
                        <a href="#" class="w-10 h-10 bg-blue-600 hover:bg-blue-700 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fab fa-facebook-f text-white"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-pink-600 hover:bg-pink-700 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fab fa-instagram text-white"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-blue-700 hover:bg-blue-800 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fab fa-linkedin-in text-white"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Formulaire de contact -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-slate-800 mb-6">Envoyez-nous un message</h2>
                    
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('contact.send') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-slate-700 mb-2">Nom complet *</label>
                                <input type="text" name="name" id="name" required
                                    class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    placeholder="Votre nom"
                                    value="{{ old('name', auth()->user()->name ?? '') }}">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email *</label>
                                <input type="email" name="email" id="email" required
                                    class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    placeholder="votre@email.com"
                                    value="{{ old('email', auth()->user()->email ?? '') }}">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-slate-700 mb-2">Sujet *</label>
                            <select name="subject" id="subject" required
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="">Sélectionnez un sujet</option>
                                <option value="question">Question générale</option>
                                <option value="support">Support technique</option>
                                <option value="partnership">Partenariat</option>
                                <option value="feedback">Suggestion / Feedback</option>
                                <option value="other">Autre</option>
                            </select>
                            @error('subject')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-slate-700 mb-2">Message *</label>
                            <textarea name="message" id="message" rows="5" required
                                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                                placeholder="Décrivez votre demande en détail...">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-4 px-6 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Envoyer le message
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- FAQ rapide -->
        <div class="mt-16">
            <h2 class="text-2xl font-bold text-slate-800 text-center mb-8">Questions fréquentes</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <h3 class="font-semibold text-slate-800 mb-2">Comment devenir prestataire ?</h3>
                    <p class="text-slate-600 text-sm">Inscrivez-vous et complétez votre profil pour commencer à proposer vos services.</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <h3 class="font-semibold text-slate-800 mb-2">Comment réserver un service ?</h3>
                    <p class="text-slate-600 text-sm">Parcourez les services, sélectionnez celui qui vous convient et effectuez votre réservation.</p>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <h3 class="font-semibold text-slate-800 mb-2">Les paiements sont-ils sécurisés ?</h3>
                    <p class="text-slate-600 text-sm">Oui, tous les paiements sont sécurisés via Stripe avec protection acheteur.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
