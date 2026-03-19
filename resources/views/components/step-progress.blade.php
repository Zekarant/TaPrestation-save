{{--
    Step Progress Component - Shows multi-step process progress
    Usage: <x-step-progress :steps="['Étape 1', 'Étape 2', 'Étape 3']" :current="2" />
--}}

@props([
    'steps' => [],
    'current' => 1,
    'showLabels' => true
])

<div class="step-progress-wrapper py-6">
    <div class="flex items-center justify-between relative">
        <!-- Progress line background -->
        <div class="absolute top-5 left-0 right-0 h-1 bg-gray-200 rounded-full"></div>
        
        <!-- Progress line filled -->
        <div class="absolute top-5 left-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full transition-all duration-500"
             style="width: {{ (($current - 1) / max(count($steps) - 1, 1)) * 100 }}%"></div>
        
        @foreach($steps as $index => $step)
        @php
            $stepNum = $index + 1;
            $isCompleted = $stepNum < $current;
            $isActive = $stepNum === $current;
            $isPending = $stepNum > $current;
        @endphp
        
        <div class="relative flex flex-col items-center z-10" style="flex: 1;">
            <!-- Step circle -->
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300
                @if($isCompleted)
                    bg-gradient-to-br from-green-400 to-green-600 text-white shadow-lg shadow-green-200
                @elseif($isActive)
                    bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-200 ring-4 ring-blue-100 animate-pulse
                @else
                    bg-gray-200 text-gray-400
                @endif">
                @if($isCompleted)
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                @else
                    {{ $stepNum }}
                @endif
            </div>
            
            <!-- Step label -->
            @if($showLabels)
            <span class="absolute -bottom-6 whitespace-nowrap text-xs font-medium transition-colors duration-300
                @if($isCompleted) text-green-600
                @elseif($isActive) text-blue-600 font-semibold
                @else text-gray-400
                @endif">
                {{ $step }}
            </span>
            @endif
        </div>
        @endforeach
    </div>
</div>
