{{-- Composant Tooltip d'aide réutilisable --}}
@props([
    'text' => '',
    'position' => 'top', // top, bottom, left, right
    'icon' => 'info'
])

@php
$positionClasses = match($position) {
    'top' => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
    'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
    'left' => 'right-full top-1/2 -translate-y-1/2 mr-2',
    'right' => 'left-full top-1/2 -translate-y-1/2 ml-2',
    default => 'bottom-full left-1/2 -translate-x-1/2 mb-2'
};

$arrowClasses = match($position) {
    'top' => 'top-full left-1/2 -translate-x-1/2 border-t-gray-900',
    'bottom' => 'bottom-full left-1/2 -translate-x-1/2 border-b-gray-900',
    'left' => 'left-full top-1/2 -translate-y-1/2 border-l-gray-900',
    'right' => 'right-full top-1/2 -translate-y-1/2 border-r-gray-900',
    default => 'top-full left-1/2 -translate-x-1/2 border-t-gray-900'
};
@endphp

<div class="relative inline-flex group">
    @if($icon === 'info')
        <svg class="w-4 h-4 text-gray-400 hover:text-blue-500 cursor-help transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    @elseif($icon === 'question')
        <svg class="w-4 h-4 text-gray-400 hover:text-blue-500 cursor-help transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    @elseif($icon === 'lightbulb')
        <svg class="w-4 h-4 text-yellow-400 hover:text-yellow-500 cursor-help transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
        </svg>
    @endif
    
    <div class="absolute {{ $positionClasses }} px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-50 pointer-events-none max-w-xs">
        {{ $text }}
        <div class="absolute {{ $arrowClasses }} border-4 border-transparent"></div>
    </div>
</div>
