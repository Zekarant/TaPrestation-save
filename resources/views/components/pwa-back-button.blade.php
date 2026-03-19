{{-- PWA Back Button - Smart navigation with logical fallback --}}
@props(['class' => '', 'href' => null, 'fallback' => null])

@php
    // Déterminer l'URL de retour intelligente
    $backUrl = $href;
    $currentPath = request()->path();
    $segments = array_values(array_filter(explode('/', trim($currentPath, '/'))));
    $parentPath = '/';
    if (count($segments) > 1) {
        array_pop($segments);
        $parentPath = '/' . implode('/', $segments);
    }
    if ($parentPath === '/prestataire') {
        $parentPath = Route::has('prestataire.dashboard') ? route('prestataire.dashboard') : '/prestataire/dashboard';
    } elseif ($parentPath === '/prestataire/food') {
        $parentPath = Route::has('prestataire.food-orders.dashboard')
            ? route('prestataire.food-orders.dashboard')
            : '/prestataire/food/food-orders/dashboard';
    } elseif ($parentPath === '/client') {
        $parentPath = Route::has('client.dashboard') ? route('client.dashboard') : '/client/dashboard';
    } elseif ($parentPath === '/driver') {
        $parentPath = Route::has('driver.map') ? route('driver.map') : '/driver/map';
    } elseif ($parentPath === '/admin') {
        $parentPath = '/admin/dashboard';
    }

    // Si un href est passé mais c'est un dashboard générique, on remonte l'architecture.
    if ($backUrl) {
        $isDashboardHref = in_array($backUrl, array_filter([
            Route::has('prestataire.dashboard') ? route('prestataire.dashboard') : null,
            Route::has('client.dashboard') ? route('client.dashboard') : null,
            Route::has('driver.dashboard') ? route('driver.dashboard') : null,
            '/prestataire/dashboard',
            '/client/dashboard',
            '/driver/dashboard',
        ]), true);
        if ($isDashboardHref && $parentPath && $parentPath !== '/') {
            $backUrl = $parentPath;
        }
    }
    
    if (!$backUrl) {
        // Navigation intelligente basée sur le contexte
        $backUrl = match(true) {
            // Food orders client
            str_contains($currentPath, 'food-orders/') && !str_contains($currentPath, 'prestataire') => route('food.orders.index'),
            str_contains($currentPath, 'food/orders') => route('food.index'),
            str_contains($currentPath, 'food/cart') => route('food.index'),
            preg_match('/food\/prestataire\/\d+/', $currentPath) => route('food.index'),
            
            // Services
            preg_match('/services\/\d+/', $currentPath) => route('services.index'),
            preg_match('/prestataires\/\d+/', $currentPath) => route('services.index'),
            
            // Equipment
            preg_match('/equipment\/\d+/', $currentPath) => route('equipment.index'),
            
            // Reservations
            preg_match('/reservations\/\d+/', $currentPath) => route('reservations.index'),
            
            // Prestataire - cas spécifiques
            str_contains($currentPath, 'prestataire/food/food-orders/') => Route::has('prestataire.food-orders.index') ? route('prestataire.food-orders.index') : route('prestataire.dashboard'),
            str_contains($currentPath, 'prestataire/food-orders/') => Route::has('prestataire.food-orders.index') ? route('prestataire.food-orders.index') : route('prestataire.dashboard'),
            str_contains($currentPath, 'prestataire/bookings/') => Route::has('prestataire.agenda.index') ? route('prestataire.agenda.index') : route('prestataire.dashboard'),
            str_contains($currentPath, 'prestataire/services/') => Route::has('prestataire.services.index') ? route('prestataire.services.index') : route('prestataire.dashboard'),
            
            // Delivery
            str_contains($currentPath, 'delivery/orders/') => route('delivery.orders.index'),
            str_contains($currentPath, 'delivery/') => route('delivery.dashboard'),
            
            // Messages
            preg_match('/messaging\/\d+/', $currentPath) => route('messaging.index'),
            
            // Default: parent architectural path, sinon fallback
            default => $parentPath ?: ($fallback ?? url('/'))
        };
    }
    $smartFallback = $fallback ?? $backUrl ?? url('/');
@endphp

<a href="{{ $backUrl }}" 
   data-smart-back="true"
   data-fallback="{{ $smartFallback }}"
   {{ $attributes->merge(['class' => 'pwa-inline-back inline-flex items-center px-3 py-2 bg-white rounded-xl shadow-sm border border-blue-200 text-blue-700 hover:bg-blue-50 transition-colors text-sm font-medium ' . $class]) }}>
    <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    {{ $slot->isEmpty() ? 'Retour' : $slot }}
</a>
