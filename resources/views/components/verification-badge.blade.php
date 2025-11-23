@props(['size' => 'md'])

@php
$sizeClasses = [
    'sm' => 'w-4 h-4 text-[8px]',
    'md' => 'w-5 h-5 text-xs',
    'lg' => 'w-6 h-6 text-sm'
];
$classes = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div class="absolute -top-1 -right-1 {{ $classes }} bg-green-500 rounded-full flex items-center justify-center border-2 border-white">
    <i class="fas fa-check text-white"></i>
</div>