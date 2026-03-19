{{--
    Action Card Component - Prominent card for key actions
    Usage: <x-action-card icon="🚀" title="Titre" description="Description" url="/action" buttonText="Commencer" />
--}}

@props([
    'icon' => '🚀',
    'title' => 'Action',
    'description' => '',
    'url' => '#',
    'buttonText' => 'Commencer',
    'color' => 'blue', // blue, green, purple, orange, pink
    'size' => 'md', // sm, md, lg
    'badge' => null,
    'newBadge' => false
])

@php
$colorClasses = [
    'blue' => [
        'bg' => 'from-blue-500 to-indigo-600',
        'hover' => 'group-hover:from-blue-600 group-hover:to-indigo-700',
        'light' => 'from-blue-50 to-indigo-50',
        'border' => 'border-blue-200 hover:border-blue-300',
    ],
    'green' => [
        'bg' => 'from-green-500 to-emerald-600',
        'hover' => 'group-hover:from-green-600 group-hover:to-emerald-700',
        'light' => 'from-green-50 to-emerald-50',
        'border' => 'border-green-200 hover:border-green-300',
    ],
    'purple' => [
        'bg' => 'from-purple-500 to-indigo-600',
        'hover' => 'group-hover:from-purple-600 group-hover:to-indigo-700',
        'light' => 'from-purple-50 to-indigo-50',
        'border' => 'border-purple-200 hover:border-purple-300',
    ],
    'orange' => [
        'bg' => 'from-orange-500 to-amber-600',
        'hover' => 'group-hover:from-orange-600 group-hover:to-amber-700',
        'light' => 'from-orange-50 to-amber-50',
        'border' => 'border-orange-200 hover:border-orange-300',
    ],
    'pink' => [
        'bg' => 'from-pink-500 to-rose-600',
        'hover' => 'group-hover:from-pink-600 group-hover:to-rose-700',
        'light' => 'from-pink-50 to-rose-50',
        'border' => 'border-pink-200 hover:border-pink-300',
    ],
];
$colors = $colorClasses[$color] ?? $colorClasses['blue'];

$sizeClasses = [
    'sm' => 'p-4',
    'md' => 'p-5',
    'lg' => 'p-6',
];
$padding = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<a href="{{ $url }}" 
   class="action-card group block bg-gradient-to-br {{ $colors['light'] }} {{ $padding }} rounded-2xl border {{ $colors['border'] }} shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 relative overflow-hidden">
    
    {{-- Background decoration --}}
    <div class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-br {{ $colors['bg'] }} rounded-full opacity-10 group-hover:opacity-20 transition-opacity duration-300"></div>
    
    {{-- New badge --}}
    @if($newBadge)
    <div class="absolute top-3 right-3">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gradient-to-r from-green-400 to-emerald-500 text-white shadow-sm animate-pulse">
            NOUVEAU
        </span>
    </div>
    @elseif($badge)
    <div class="absolute top-3 right-3">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
            {{ $badge }}
        </span>
    </div>
    @endif
    
    <div class="relative z-10 flex items-start gap-4">
        {{-- Icon --}}
        <div class="flex-shrink-0 w-14 h-14 bg-gradient-to-br {{ $colors['bg'] }} {{ $colors['hover'] }} rounded-2xl flex items-center justify-center text-2xl shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:rotate-3">
            {{ $icon }}
        </div>
        
        {{-- Content --}}
        <div class="flex-1 min-w-0">
            <h3 class="font-bold text-gray-900 group-hover:text-gray-800 transition-colors">{{ $title }}</h3>
            <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $description }}</p>
            
            {{-- Button --}}
            <div class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-gray-700 group-hover:text-gray-900">
                {{ $buttonText }}
                <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </div>
    </div>
</a>
