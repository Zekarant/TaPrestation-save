{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF pour les formulaires & AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TaPrestation') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- Hero Animations CSS (optionnel, ne casse rien si le fichier manque) --}}
    <link rel="stylesheet" href="{{ asset('css/hero-animations.css') }}">

    {{-- CSS/JS compilés Laravel (Vite) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Flatpickr CSS (calendrier) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    {{-- Hero Animations JS --}}
    <script src="{{ asset('js/hero-animations.js') }}" defer></script>

    {{-- jQuery (utilisé par certains écrans) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- SweetAlert2 (popups) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Styles personnalisés par page --}}
    @stack('styles')
    @stack('head')
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        {{-- Navigation globale --}}
        @includeIf('layouts.navigation')

        {{-- En-tête de page optionnel --}}
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        {{-- Contenu principal --}}
        <main>
            @yield('content')
        </main>
    </div>

    {{-- Flatpickr JS --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    {{-- Scripts personnalisés par page --}}
    @stack('scripts')

    {{-- Bandeau de cookies --}}
    <div id="cookie-banner"
        class="fixed bottom-0 left-0 right-0 text-white p-4 shadow-2xl z-50 transform translate-y-full transition-transform duration-500"
        style="display: none; background-color: rgba(17, 24, 39, 0.97); backdrop-filter: blur(8px);">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3">
                <i class="fas fa-cookie-bite text-3xl text-blue-400 flex-shrink-0"></i>
                <div>
                    <p class="text-sm sm:text-base">
                        <strong>Ce site utilise des cookies</strong> pour améliorer votre expérience de navigation et
                        analyser notre trafic.
                        En continuant, vous acceptez notre utilisation des cookies.
                    </p>
                    <a href="{{ route('rgpd') }}" class="text-blue-400 hover:text-blue-300 text-sm underline">
                        En savoir plus sur notre politique de confidentialité
                    </a>
                </div>
            </div>
            <div class="flex gap-3 flex-shrink-0">
                <button onclick="acceptCookies()"
                    class="bg-blue-600 hover:bg-blue-700 px-6 py-2 rounded-lg font-semibold transition-colors duration-200">
                    Accepter
                </button>
                <button onclick="refuseCookies()"
                    class="bg-gray-700 hover:bg-gray-600 px-6 py-2 rounded-lg font-semibold transition-colors duration-200">
                    Refuser
                </button>
            </div>
        </div>
    </div>

    <script>
        // Vérifier si l'utilisateur a déjà fait un choix
        document.addEventListener('DOMContentLoaded', function () {
            const cookieConsent = localStorage.getItem('cookieConsent');
            if (!cookieConsent) {
                // Afficher le bandeau avec animation
                const banner = document.getElementById('cookie-banner');
                banner.style.display = 'block';
                setTimeout(() => {
                    banner.style.transform = 'translateY(0)';
                }, 100);
            }
        });

        function acceptCookies() {
            localStorage.setItem('cookieConsent', 'accepted');
            hideCookieBanner();
            // Ici vous pouvez activer vos scripts analytics, etc.
            console.log('Cookies acceptés');
        }

        function refuseCookies() {
            localStorage.setItem('cookieConsent', 'refused');
            hideCookieBanner();
            console.log('Cookies refusés');
        }

        function hideCookieBanner() {
            const banner = document.getElementById('cookie-banner');
            banner.style.transform = 'translateY(100%)';
            setTimeout(() => {
                banner.style.display = 'none';
            }, 500);
        }
    </script>
</body>

</html>