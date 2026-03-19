{{--
    Flash Message Component - Animated success/error/info/warning notifications
    Usage: <x-flash-message />
    Automatically displays session flash messages with animations
--}}

@props([
    'autoHide' => true,
    'duration' => 5000
])

@php
    $messages = [];
    
    if (session('success')) {
        $messages[] = ['type' => 'success', 'content' => session('success')];
    }
    if (session('error')) {
        $messages[] = ['type' => 'error', 'content' => session('error')];
    }
    if (session('warning')) {
        $messages[] = ['type' => 'warning', 'content' => session('warning')];
    }
    if (session('info')) {
        $messages[] = ['type' => 'info', 'content' => session('info')];
    }
    if (session('message')) {
        $messages[] = ['type' => 'info', 'content' => session('message')];
    }
    
    $typeConfig = [
        'success' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-400',
            'icon' => 'check-circle',
            'iconColor' => 'text-green-400',
            'textColor' => 'text-green-800',
            'progressColor' => 'bg-green-400'
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-400',
            'icon' => 'times-circle',
            'iconColor' => 'text-red-400',
            'textColor' => 'text-red-800',
            'progressColor' => 'bg-red-400'
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-400',
            'icon' => 'exclamation-triangle',
            'iconColor' => 'text-yellow-400',
            'textColor' => 'text-yellow-800',
            'progressColor' => 'bg-yellow-400'
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-400',
            'icon' => 'info-circle',
            'iconColor' => 'text-blue-400',
            'textColor' => 'text-blue-800',
            'progressColor' => 'bg-blue-400'
        ],
    ];
@endphp

@if(count($messages) > 0)
<div
    class="flash-messages fixed right-4 space-y-3 max-w-md w-full px-4 pointer-events-none"
    style="top: calc(var(--site-nav-h, 70px) + env(safe-area-inset-top, 0px) + 0.5rem); z-index: 10020;"
>
    @foreach($messages as $index => $message)
    @php
        $config = $typeConfig[$message['type']] ?? $typeConfig['info'];
    @endphp
    
    <div x-data="{ 
            show: false, 
            progress: 100,
            init() {
                setTimeout(() => this.show = true, {{ $index * 100 }});
                @if($autoHide)
                const interval = setInterval(() => {
                    this.progress -= (100 / ({{ $duration }} / 50));
                    if (this.progress <= 0) {
                        clearInterval(interval);
                        this.show = false;
                    }
                }, 50);
                @endif
            }
         }"
         x-show="show"
         x-transition:enter="transform transition ease-out duration-300"
         x-transition:enter-start="translate-x-full opacity-0"
         x-transition:enter-end="translate-x-0 opacity-100"
         x-transition:leave="transform transition ease-in duration-200"
         x-transition:leave-start="translate-x-0 opacity-100"
         x-transition:leave-end="translate-x-full opacity-0"
         class="pointer-events-auto {{ $config['bg'] }} border-l-4 {{ $config['border'] }} rounded-r-lg shadow-lg overflow-hidden"
         role="alert">
        
        <div class="p-4">
            <div class="flex items-start">
                <!-- Icon -->
                <div class="flex-shrink-0">
                    <i class="fas fa-{{ $config['icon'] }} {{ $config['iconColor'] }} text-xl"></i>
                </div>
                
                <!-- Content -->
                <div class="ml-3 flex-1">
                    <p class="{{ $config['textColor'] }} text-sm font-medium">
                        {{ $message['content'] }}
                    </p>
                </div>
                
                <!-- Close button -->
                <button @click="show = false" class="ml-4 flex-shrink-0 {{ $config['iconColor'] }} hover:opacity-75 transition-opacity">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Progress bar -->
        @if($autoHide)
        <div class="h-1 bg-gray-200">
            <div class="{{ $config['progressColor'] }} h-full transition-all duration-50 ease-linear"
                 :style="{ width: progress + '%' }"></div>
        </div>
        @endif
    </div>
    @endforeach
</div>
@endif
