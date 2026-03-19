{{-- resources/views/layouts/app.blade.php - VERSION OPTIMISÉE --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#1e293b">
    {{-- AI Config (exposed to clients) --}}
    <meta name="ai-enabled" content="{{ isset($ai['enabled']) && $ai['enabled'] ? 'true' : 'false' }}">
    <meta name="ai-model" content="{{ $ai['model'] ?? 'gpt-5' }}">
    
    {{-- Performance: Preconnect to external resources --}}
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    
    {{-- PWA --}}
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('icons/icon-48x48.png') }}" type="image/png" sizes="48x48">
    <link rel="icon" href="{{ asset('icons/icon-192x192.png') }}" type="image/png" sizes="192x192">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}" sizes="180x180">

    {{-- CSRF pour les formulaires & AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TaPrestation') }}</title>

    {{-- Fonts - Display swap for faster text rendering --}}
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">

    {{-- CSS/JS compilés Laravel (Vite) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Mobile App Styling --}}
    <link rel="stylesheet" href="{{ asset('css/mobile-app.css') }}">

    {{-- Global Ergonomics - Improved UX across all pages --}}
    <link rel="stylesheet" href="{{ asset('css/global-ergonomics.css') }}">

    {{-- Flatpickr CSS (calendrier) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- SweetAlert2 (popups) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Styles personnalisés par page --}}
    @stack('styles')
    @stack('head')
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50 min-h-screen overflow-x-hidden flex flex-col">
    <div class="flex-1 min-h-screen flex flex-col">
        {{-- Navigation globale --}}
        @includeIf('layouts.navigation')

        {{-- En-tête de page optionnel --}}
        @if (isset($header))
            <header class="bg-linear-to-r from-slate-900 via-blue-800 to-slate-900 shadow-xl border-b border-blue-700/30">
                <div class="max-w-7xl mx-auto py-4 sm:py-6 px-4 sm:px-6 lg:px-8">
                    <div class="text-blue-200 text-sm sm:text-base">
                        {{ $header }}
                    </div>
                </div>
            </header>
        @endif

        {{-- Flash Messages avec animations --}}
        <x-flash-message />

        {{-- Contenu principal --}}
        <main class="flex-1 pb-24 sm:pb-8 lg:pb-0 md:pb-0">
            @yield('content')
        </main>

        {{-- Mobile Bottom Navigation --}}
        @includeIf('components.mobile-bottom-nav')
    </div>

    {{-- Scripts personnalisés par page --}}
    @stack('scripts')
    
    {{-- Flatpickr JS --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" defer></script>

    {{-- DÉSACTIVATION COMPLÈTE DU SERVICE WORKER --}}
    <script>
        (function() {
            // Désactiver les service workers et vider les caches
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations().then(function(registrations) {
                    registrations.forEach(function(registration) {
                        registration.unregister();
                    });
                });
            }
            if ('caches' in window) {
                caches.keys().then(function(names) {
                    names.forEach(function(name) {
                        caches.delete(name);
                    });
                });
            }
            // Expose AI config globally for client scripts
            window.AI_CONFIG = {
                enabled: document.querySelector('meta[name="ai-enabled"]').getAttribute('content') === 'true',
                model: document.querySelector('meta[name="ai-model"]').getAttribute('content')
            };
        })();
    </script>
</body>
</html>
