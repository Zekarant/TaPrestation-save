{{--
    Feature Highlight Component - Highlights new or important features
    Usage: 
    <x-feature-highlight 
        title="Nouvelle fonctionnalité !"
        description="Découvrez notre nouveau système de réservation en ligne."
        icon="sparkles"
        :new="true"
        ctaText="Essayer"
        ctaUrl="/feature"
    />
--}}

@props([
    'title' => 'Nouvelle fonctionnalité',
    'description' => '',
    'icon' => 'sparkles',
    'new' => true,
    'ctaText' => null,
    'ctaUrl' => null,
    'dismissible' => true,
    'storageKey' => null,
    'color' => 'indigo',
    'image' => null
])

@php
    $key = $storageKey ?? 'feature_' . \Illuminate\Support\Str::slug($title);
    
    $colorClasses = [
        'indigo' => ['from' => 'from-indigo-500', 'to' => 'to-purple-600', 'light' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'border' => 'border-indigo-200'],
        'blue' => ['from' => 'from-blue-500', 'to' => 'to-cyan-600', 'light' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-200'],
        'green' => ['from' => 'from-green-500', 'to' => 'to-emerald-600', 'light' => 'bg-green-50', 'text' => 'text-green-600', 'border' => 'border-green-200'],
        'orange' => ['from' => 'from-orange-500', 'to' => 'to-red-600', 'light' => 'bg-orange-50', 'text' => 'text-orange-600', 'border' => 'border-orange-200'],
    ];
    $colors = $colorClasses[$color] ?? $colorClasses['indigo'];
@endphp

<div x-data="persistentVisibility('{{ $key }}_dismissed')"
     x-show="visible"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="relative overflow-hidden rounded-xl shadow-lg mb-6 bg-gradient-to-r {{ $colors['from'] }} {{ $colors['to'] }}">
    
    {{-- Background pattern --}}
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
            <defs>
                <pattern id="feature-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                    <circle cx="10" cy="10" r="1.5" fill="white"/>
                </pattern>
            </defs>
            <rect width="100" height="100" fill="url(#feature-pattern)"/>
        </svg>
    </div>
    
    <div class="relative p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center gap-4">
        {{-- Icon --}}
        <div class="flex-shrink-0 w-14 h-14 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
            @if($image)
                <img src="{{ $image }}" alt="" class="w-10 h-10 object-contain">
            @else
                <i class="fas fa-{{ $icon }} text-white text-2xl"></i>
            @endif
        </div>
        
        {{-- Content --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
                @if($new)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-white/20 text-white animate-pulse">
                    ✨ NOUVEAU
                </span>
                @endif
                <h3 class="text-lg font-bold text-white">{{ $title }}</h3>
            </div>
            <p class="text-white/90 text-sm">{{ $description }}</p>
        </div>
        
        {{-- CTA --}}
        @if($ctaText && $ctaUrl)
        <a href="{{ $ctaUrl }}" 
           class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-800 rounded-lg font-semibold text-sm hover:bg-gray-50 transition-colors shadow-md hover:shadow-lg">
            {{ $ctaText }}
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endif
        
        {{-- Dismiss button --}}
        @if($dismissible)
        <button @click="dismiss()"
                class="absolute top-3 right-3 p-1 text-white/60 hover:text-white transition-colors"
                title="Masquer">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        @endif
    </div>
</div>
