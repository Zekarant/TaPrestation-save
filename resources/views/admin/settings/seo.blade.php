@extends('layouts.admin-modern')

@section('title', 'Paramètres SEO')
@section('page-title', 'Paramètres SEO')

@section('content')
<div class="page-header">
    <h1 class="page-title">🔍 Paramètres SEO</h1>
    <p class="page-subtitle">Optimisez le référencement de votre site</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<div class="card-base">
    <form action="{{ route('admin.settings.seo.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Titre du site (meta title)</label>
                <input type="text" name="meta_title" value="{{ $settings['meta_title'] ?? '' }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg" maxlength="60">
                <p class="text-xs text-gray-500 mt-1">Maximum 60 caractères</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description (meta description)</label>
                <textarea name="meta_description" rows="3" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg" maxlength="160">{{ $settings['meta_description'] ?? '' }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Maximum 160 caractères</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mots-clés (meta keywords)</label>
                <input type="text" name="meta_keywords" value="{{ $settings['meta_keywords'] ?? '' }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="mot1, mot2, mot3">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Google Analytics ID</label>
                <input type="text" name="google_analytics_id" value="{{ $settings['google_analytics_id'] ?? '' }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="UA-XXXXXXXXX-X ou G-XXXXXXXXXX">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Vérification Google Search Console</label>
                <input type="text" name="google_site_verification" value="{{ $settings['google_site_verification'] ?? '' }}" 
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
