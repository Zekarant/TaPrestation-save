{{--
    Contextual Help Component - Shows a small help icon with detailed explanation
    Usage: <x-contextual-help title="Titre">Contenu détaillé de l'aide</x-contextual-help>
--}}

@props([
    'title' => 'Aide',
    'position' => 'top', // top, bottom, left, right
    'size' => 'md', // sm, md, lg
    'icon' => '?',
    'color' => 'blue' // blue, green, purple, orange
])

@php
$colors = [
    'blue' => 'from-blue-500 to-indigo-600',
    'green' => 'from-green-500 to-emerald-600',
    'purple' => 'from-purple-500 to-indigo-600',
    'orange' => 'from-orange-500 to-amber-600',
];
$bgColor = $colors[$color] ?? $colors['blue'];

$sizes = [
    'sm' => 'w-4 h-4 text-[10px]',
    'md' => 'w-5 h-5 text-xs',
    'lg' => 'w-6 h-6 text-sm',
];
$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<span class="inline-flex items-center" 
      x-data="{ show: false }" 
      @mouseenter="show = true" 
      @mouseleave="show = false"
      @click="show = !show">
    
    {{-- Help trigger --}}
    <span class="{{ $sizeClass }} bg-gradient-to-br {{ $bgColor }} text-white rounded-full flex items-center justify-center cursor-help font-bold shadow-sm hover:shadow-md transition-shadow duration-200 ml-1">
        {{ $icon }}
    </span>
    
    {{-- Tooltip content --}}
    <div x-show="show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @class([
             'absolute z-50 bg-gray-900 text-white rounded-xl shadow-xl p-4 max-w-xs',
             'bottom-full mb-2' => $position === 'top',
             'top-full mt-2' => $position === 'bottom',
             'right-full mr-2' => $position === 'left',
             'left-full ml-2' => $position === 'right',
         ])
         style="min-width: 200px;">
        
        {{-- Arrow --}}
        <div @class([
            'absolute w-3 h-3 bg-gray-900 transform rotate-45',
            'bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2' => $position === 'top',
            'top-0 left-1/2 -translate-x-1/2 -translate-y-1/2' => $position === 'bottom',
            'right-0 top-1/2 translate-x-1/2 -translate-y-1/2' => $position === 'left',
            'left-0 top-1/2 -translate-x-1/2 -translate-y-1/2' => $position === 'right',
        ])></div>
        
        @if($title)
        <h4 class="font-bold text-sm mb-2 flex items-center gap-2">
            <span class="text-base">💡</span>
            {{ $title }}
        </h4>
        @endif
        
        <div class="text-xs text-gray-300 leading-relaxed">
            {{ $slot }}
        </div>
    </div>
</span>
