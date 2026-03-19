{{--
    Animated Stat Card Component - Stats with counting animation and sparkline
    Usage: 
    <x-stat-card 
        value="156"
        label="Réservations"
        icon="calendar-check"
        color="blue"
        :trend="12"
        link="/bookings"
    />
--}}

@props([
    'value' => 0,
    'label' => 'Stat',
    'icon' => 'chart-line',
    'color' => 'blue',
    'trend' => null,
    'trendLabel' => 'vs mois dernier',
    'link' => null,
    'description' => null,
    'prefix' => '',
    'suffix' => '',
    'animate' => true
])

@php
    $colorClasses = [
        'blue' => ['bg' => 'bg-blue-50', 'icon' => 'text-blue-600', 'border' => 'border-blue-200', 'trend' => 'text-blue-600'],
        'green' => ['bg' => 'bg-green-50', 'icon' => 'text-green-600', 'border' => 'border-green-200', 'trend' => 'text-green-600'],
        'purple' => ['bg' => 'bg-purple-50', 'icon' => 'text-purple-600', 'border' => 'border-purple-200', 'trend' => 'text-purple-600'],
        'orange' => ['bg' => 'bg-orange-50', 'icon' => 'text-orange-600', 'border' => 'border-orange-200', 'trend' => 'text-orange-600'],
        'red' => ['bg' => 'bg-red-50', 'icon' => 'text-red-600', 'border' => 'border-red-200', 'trend' => 'text-red-600'],
        'cyan' => ['bg' => 'bg-cyan-50', 'icon' => 'text-cyan-600', 'border' => 'border-cyan-200', 'trend' => 'text-cyan-600'],
        'indigo' => ['bg' => 'bg-indigo-50', 'icon' => 'text-indigo-600', 'border' => 'border-indigo-200', 'trend' => 'text-indigo-600'],
        'emerald' => ['bg' => 'bg-emerald-50', 'icon' => 'text-emerald-600', 'border' => 'border-emerald-200', 'trend' => 'text-emerald-600'],
    ];
    $colors = $colorClasses[$color] ?? $colorClasses['blue'];
    $tag = $link ? 'a' : 'div';
@endphp

<{{ $tag }} 
    @if($link) href="{{ $link }}" @endif
    x-data="{ 
        displayValue: 0, 
        targetValue: {{ is_numeric($value) ? $value : 0 }},
        animated: false,
        init() {
            if (!{{ $animate ? 'true' : 'false' }}) {
                this.displayValue = this.targetValue;
                return;
            }
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !this.animated) {
                        this.animated = true;
                        this.animateValue();
                    }
                });
            }, { threshold: 0.5 });
            
            observer.observe(this.$el);
        },
        animateValue() {
            const duration = 1500;
            const start = performance.now();
            const startValue = 0;
            
            const step = (timestamp) => {
                const progress = Math.min((timestamp - start) / duration, 1);
                const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                this.displayValue = Math.floor(startValue + (this.targetValue - startValue) * easeOutQuart);
                
                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    this.displayValue = this.targetValue;
                }
            };
            
            requestAnimationFrame(step);
        }
    }"
    class="stat-card bg-white rounded-xl shadow-md border {{ $colors['border'] }} p-4 hover:shadow-lg transition-all duration-300 group {{ $link ? 'cursor-pointer' : '' }}"
    @if($description) title="{{ $description }}" @endif
>
    <div class="flex items-start justify-between">
        <!-- Icon -->
        <div class="p-2.5 rounded-lg {{ $colors['bg'] }} group-hover:scale-110 transition-transform duration-300">
            <i class="fas fa-{{ $icon }} {{ $colors['icon'] }} text-lg"></i>
        </div>
        
        <!-- Trend indicator -->
        @if($trend !== null)
        <div class="flex items-center gap-1 text-xs font-medium {{ $trend >= 0 ? 'text-green-600' : 'text-red-600' }}">
            <svg class="w-3 h-3 {{ $trend < 0 ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
            </svg>
            <span>{{ $trend >= 0 ? '+' : '' }}{{ $trend }}%</span>
        </div>
        @endif
    </div>
    
    <!-- Value -->
    <div class="mt-3">
        <p class="text-2xl font-bold text-gray-900">
            {{ $prefix }}<span x-text="displayValue.toLocaleString('fr-FR')">{{ $value }}</span>{{ $suffix }}
        </p>
        <p class="text-sm text-gray-500 mt-0.5">{{ $label }}</p>
    </div>
    
    @if($trend !== null)
    <p class="text-xs text-gray-400 mt-2">{{ $trendLabel }}</p>
    @endif
    
    <!-- Hover arrow for links -->
    @if($link)
    <div class="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </div>
    @endif
</{{ $tag }}>
