{{-- Composant boîte d'aide/conseil réutilisable --}}
@props([
    'type' => 'info', // info, tip, warning, success
    'title' => '',
    'dismissible' => false,
    'storageKey' => null // clé pour localStorage si dismissible
])

@php
$config = match($type) {
    'info' => [
        'bg' => 'bg-blue-50',
        'border' => 'border-blue-200',
        'icon_bg' => 'bg-blue-100',
        'icon_color' => 'text-blue-500',
        'title_color' => 'text-blue-800',
        'text_color' => 'text-blue-700',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
    ],
    'tip' => [
        'bg' => 'bg-yellow-50',
        'border' => 'border-yellow-200',
        'icon_bg' => 'bg-yellow-100',
        'icon_color' => 'text-yellow-500',
        'title_color' => 'text-yellow-800',
        'text_color' => 'text-yellow-700',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>'
    ],
    'warning' => [
        'bg' => 'bg-orange-50',
        'border' => 'border-orange-200',
        'icon_bg' => 'bg-orange-100',
        'icon_color' => 'text-orange-500',
        'title_color' => 'text-orange-800',
        'text_color' => 'text-orange-700',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>'
    ],
    'success' => [
        'bg' => 'bg-green-50',
        'border' => 'border-green-200',
        'icon_bg' => 'bg-green-100',
        'icon_color' => 'text-green-500',
        'title_color' => 'text-green-800',
        'text_color' => 'text-green-700',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
    ],
    default => [
        'bg' => 'bg-gray-50',
        'border' => 'border-gray-200',
        'icon_bg' => 'bg-gray-100',
        'icon_color' => 'text-gray-500',
        'title_color' => 'text-gray-800',
        'text_color' => 'text-gray-700',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
    ]
};
@endphp

<div 
    x-data="{ 
        show: true,
        init() {
            @if($storageKey)
                this.show = localStorage.getItem('{{ $storageKey }}') !== 'dismissed';
            @endif
        },
        dismiss() {
            this.show = false;
            @if($storageKey)
                localStorage.setItem('{{ $storageKey }}', 'dismissed');
            @endif
        }
    }"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform -translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="rounded-lg {{ $config['bg'] }} {{ $config['border'] }} border p-3 sm:p-4 mb-4 {{ $attributes->get('class') }}"
>
    <div class="flex items-start gap-3">
        <div class="flex-shrink-0 w-8 h-8 {{ $config['icon_bg'] }} rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 {{ $config['icon_color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $config['icon'] !!}
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            @if($title)
                <h4 class="text-sm font-semibold {{ $config['title_color'] }} mb-1">{{ $title }}</h4>
            @endif
            <div class="text-xs sm:text-sm {{ $config['text_color'] }}">
                {{ $slot }}
            </div>
        </div>
        @if($dismissible)
            <button @click="dismiss()" class="flex-shrink-0 p-1 rounded hover:bg-white/50 transition-colors" title="Fermer">
                <svg class="w-4 h-4 {{ $config['icon_color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @endif
    </div>
</div>
