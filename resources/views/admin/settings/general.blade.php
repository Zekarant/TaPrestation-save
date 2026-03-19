@extends('layouts.admin-modern')

@section('title', 'Paramètres généraux')
@section('page-title', 'Paramètres généraux')

@section('content')
<div class="page-header">
    <h1 class="page-title">⚙️ Paramètres généraux</h1>
    <p class="page-subtitle">Configurez les paramètres de base de votre plateforme</p>
</div>

<div class="card-base">
    <form action="{{ route('admin.settings.general.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nom du site -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nom du site</label>
                <input type="text" name="site_name" value="{{ $settings['site_name'] ?? config('app.name') }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Email de contact -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email de contact</label>
                <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Téléphone -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                <input type="tel" name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Adresse -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
                <input type="text" name="address" value="{{ $settings['address'] ?? '' }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Description du site -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description du site</label>
                <textarea name="site_description" rows="3" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ $settings['site_description'] ?? '' }}</textarea>
            </div>

            <!-- Logo -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
                @if(isset($settings['logo']))
                    <img src="{{ Storage::url($settings['logo']) }}" alt="Logo" class="h-16 mb-2">
                @endif
                <input type="file" name="logo" accept="image/*" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <!-- Favicon -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Favicon</label>
                <input type="file" name="favicon" accept="image/*" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <!-- Réseaux sociaux -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Facebook</label>
                <input type="url" name="social_facebook" value="{{ $settings['social_facebook'] ?? '' }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Instagram</label>
                <input type="url" name="social_instagram" value="{{ $settings['social_instagram'] ?? '' }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Twitter</label>
                <input type="url" name="social_twitter" value="{{ $settings['social_twitter'] ?? '' }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">LinkedIn</label>
                <input type="url" name="social_linkedin" value="{{ $settings['social_linkedin'] ?? '' }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-save mr-2"></i> Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
