@extends('layouts.admin-modern')

@section('title', 'Mentions légales')
@section('page-title', 'Mentions légales')

@section('content')
<div class="page-header">
    <h1 class="page-title">⚖️ Mentions légales</h1>
    <p class="page-subtitle">Gérez les pages légales de votre site</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<div class="card-base">
    <form action="{{ route('admin.settings.legal.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-8">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-file-contract mr-1"></i> Conditions générales d'utilisation
                </label>
                <textarea name="terms_of_service" rows="10" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg font-mono text-sm">{{ $settings['terms_of_service'] ?? '' }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user-shield mr-1"></i> Politique de confidentialité
                </label>
                <textarea name="privacy_policy" rows="10" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg font-mono text-sm">{{ $settings['privacy_policy'] ?? '' }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-cookie-bite mr-1"></i> Politique de cookies
                </label>
                <textarea name="cookie_policy" rows="6" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg font-mono text-sm">{{ $settings['cookie_policy'] ?? '' }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-building mr-1"></i> Mentions légales
                </label>
                <textarea name="legal_notice" rows="6" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg font-mono text-sm">{{ $settings['legal_notice'] ?? '' }}</textarea>
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
