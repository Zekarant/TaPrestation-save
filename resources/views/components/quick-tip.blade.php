{{--
    Quick Tip Component - Shows helpful tips in context
    Usage: <x-quick-tip type="info|tip|warning" title="Title" :dismissible="true">Content</x-quick-tip>
--}}

@props([
    'type' => 'info',
    'title' => '',
    'icon' => null,
    'dismissible' => true,
    'id' => null
])

@php
$styles = [
    'info' => [
        'bg' => 'bg-gradient-to-r from-blue-50 to-indigo-50',
        'border' => 'border-blue-200',
        'bar' => 'from-blue-500 to-indigo-500',
        'icon_bg' => 'from-blue-500 to-indigo-500',
        'title' => 'text-blue-800',
        'text' => 'text-blue-700',
        'default_icon' => '💡'
    ],
    'tip' => [
        'bg' => 'bg-gradient-to-r from-green-50 to-emerald-50',
        'border' => 'border-green-200',
        'bar' => 'from-green-500 to-emerald-500',
        'icon_bg' => 'from-green-500 to-emerald-500',
        'title' => 'text-green-800',
        'text' => 'text-green-700',
        'default_icon' => '✨'
    ],
    'warning' => [
        'bg' => 'bg-gradient-to-r from-amber-50 to-orange-50',
        'border' => 'border-amber-200',
        'bar' => 'from-amber-500 to-orange-500',
        'icon_bg' => 'from-amber-500 to-orange-500',
        'title' => 'text-amber-800',
        'text' => 'text-amber-700',
        'default_icon' => '⚠️'
    ],
    'success' => [
        'bg' => 'bg-gradient-to-r from-emerald-50 to-teal-50',
        'border' => 'border-emerald-200',
        'bar' => 'from-emerald-500 to-teal-500',
        'icon_bg' => 'from-emerald-500 to-teal-500',
        'title' => 'text-emerald-800',
        'text' => 'text-emerald-700',
        'default_icon' => '🎉'
    ]
];

$style = $styles[$type] ?? $styles['info'];
$displayIcon = $icon ?? $style['default_icon'];
$tipId = $id ?? 'tip-' . uniqid();
@endphp

<div id="{{ $tipId }}" 
     class="quick-tip {{ $style['bg'] }} border {{ $style['border'] }} rounded-2xl p-5 relative overflow-hidden animate-fade-in-up"
     x-data="{ show: true }"
     x-show="show"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform scale-100"
     x-transition:leave-end="opacity-0 transform scale-95">
    
    <!-- Left accent bar -->
    <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b {{ $style['bar'] }}"></div>
    
    <div class="flex items-start gap-4 pl-2">
        <!-- Icon -->
        <div class="flex-shrink-0">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $style['icon_bg'] }} flex items-center justify-center text-white text-xl shadow-md">
                {!! $displayIcon !!}
            </div>
        </div>
        
        <!-- Content -->
        <div class="flex-1 min-w-0">
            @if($title)
            <h4 class="font-bold {{ $style['title'] }} text-base mb-1">{{ $title }}</h4>
            @endif
            <div class="{{ $style['text'] }} text-sm leading-relaxed">
                {{ $slot }}
            </div>
        </div>
        
        <!-- Dismiss button -->
        @if($dismissible)
        <button type="button" 
                @click="show = false"
                class="flex-shrink-0 w-8 h-8 rounded-full bg-white/60 hover:bg-white flex items-center justify-center text-gray-400 hover:text-gray-600 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        @endif
    </div>
</div>
