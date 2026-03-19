{{--
    Empty State Component - Shows when there's no data
    Usage: 
    <x-empty-state 
        icon="📦" 
        title="Aucun élément" 
        message="Description du contenu"
        :primaryAction="['url' => '/create', 'label' => 'Créer']"
        :secondaryAction="['url' => '/help', 'label' => 'Aide']"
        :tips="['Conseil 1', 'Conseil 2']"
        color="blue"
    />
--}}

@props([
    'icon' => '📭',
    'title' => 'Aucun élément',
    'message' => null,
    'primaryAction' => null,
    'secondaryAction' => null,
    'tips' => [],
    'color' => 'blue',
    // Legacy support
    'actionUrl' => null,
    'actionText' => 'Commencer',
    'secondaryUrl' => null,
    'secondaryText' => null
])

@php
    $colorClasses = [
        'blue' => ['bg' => 'bg-blue-100', 'border' => 'border-blue-200', 'text' => 'text-blue-600', 'button' => 'from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700'],
        'green' => ['bg' => 'bg-green-100', 'border' => 'border-green-200', 'text' => 'text-green-600', 'button' => 'from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700'],
        'purple' => ['bg' => 'bg-purple-100', 'border' => 'border-purple-200', 'text' => 'text-purple-600', 'button' => 'from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700'],
        'orange' => ['bg' => 'bg-orange-100', 'border' => 'border-orange-200', 'text' => 'text-orange-600', 'button' => 'from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700'],
        'red' => ['bg' => 'bg-red-100', 'border' => 'border-red-200', 'text' => 'text-red-600', 'button' => 'from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700'],
    ];
    $colors = $colorClasses[$color] ?? $colorClasses['blue'];
@endphp

<div class="empty-state bg-white rounded-xl shadow-lg border {{ $colors['border'] }} p-6 sm:p-8 md:p-12 text-center animate-fade-in-up">
    <!-- Icon with animation -->
    <div class="relative inline-block mb-6">
        <div class="w-20 h-20 sm:w-24 sm:h-24 md:w-28 md:h-28 {{ $colors['bg'] }} rounded-full flex items-center justify-center mx-auto">
            @if(strlen($icon) <= 3 && preg_match('/[\x{1F300}-\x{1F9FF}]/u', $icon))
                <span class="text-4xl sm:text-5xl animate-bounce">{{ $icon }}</span>
            @else
                <i class="fas fa-{{ $icon }} {{ $colors['text'] }} text-2xl sm:text-3xl animate-bounce"></i>
            @endif
        </div>
        <!-- Decorative circles -->
        <div class="absolute -top-2 -right-2 w-5 h-5 sm:w-6 sm:h-6 {{ $colors['bg'] }} rounded-full animate-pulse"></div>
        <div class="absolute -bottom-1 -left-3 w-3 h-3 sm:w-4 sm:h-4 {{ $colors['bg'] }} rounded-full animate-pulse" style="animation-delay: 0.5s"></div>
    </div>
    
    <!-- Title -->
    <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mb-3">{{ $title }}</h3>
    
    <!-- Description -->
    <p class="text-gray-600 max-w-lg mx-auto mb-6 sm:mb-8 text-sm sm:text-base leading-relaxed">
        {{ $message ?? $slot }}
    </p>
    
    <!-- Tips section -->
    @if(!empty($tips))
    <div class="bg-gray-50 rounded-lg p-4 mb-6 max-w-md mx-auto text-left">
        <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
            <svg class="w-4 h-4 {{ $colors['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Conseils pour bien démarrer
        </h4>
        <ul class="space-y-1">
            @foreach($tips as $tip)
                <li class="text-xs sm:text-sm text-gray-600 flex items-start gap-2">
                    <svg class="w-4 h-4 {{ $colors['text'] }} flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $tip }}
                </li>
            @endforeach
        </ul>
    </div>
    @endif
    
    <!-- Actions -->
    <div class="flex flex-col sm:flex-row flex-wrap items-center justify-center gap-3">
        @if($primaryAction)
        <a href="{{ $primaryAction['url'] }}" 
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 sm:px-6 sm:py-3 bg-gradient-to-r {{ $colors['button'] }} text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 text-sm sm:text-base">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ $primaryAction['label'] }}
        </a>
        @elseif($actionUrl)
        <a href="{{ $actionUrl }}" 
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 sm:px-6 sm:py-3 bg-gradient-to-r {{ $colors['button'] }} text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 text-sm sm:text-base">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ $actionText }}
        </a>
        @endif
        
        @if($secondaryAction)
        <a href="{{ $secondaryAction['url'] }}" 
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 sm:px-6 sm:py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition-colors duration-200 text-sm sm:text-base">
            {{ $secondaryAction['label'] }}
        </a>
        @elseif($secondaryUrl)
        <a href="{{ $secondaryUrl }}" 
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 sm:px-6 sm:py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition-colors duration-200 text-sm sm:text-base">
            {{ $secondaryText }}
        </a>
        @endif
    </div>
</div>
