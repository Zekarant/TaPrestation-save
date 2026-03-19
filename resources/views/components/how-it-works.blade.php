{{--
    How It Works Component - Explains a process visually
    Usage: <x-how-it-works :steps="$steps" title="Comment ça marche ?" />
--}}

@props([
    'title' => 'Comment ça marche ?',
    'subtitle' => null,
    'steps' => [
        ['icon' => '1️⃣', 'title' => 'Étape 1', 'desc' => 'Description de la première étape'],
        ['icon' => '2️⃣', 'title' => 'Étape 2', 'desc' => 'Description de la deuxième étape'],
        ['icon' => '3️⃣', 'title' => 'Étape 3', 'desc' => 'Description de la troisième étape'],
    ],
    'layout' => 'horizontal' // horizontal, vertical, grid
])

<div class="how-it-works bg-gradient-to-br from-slate-50 to-blue-50 rounded-3xl p-6 sm:p-8 my-6">
    <!-- Header -->
    <div class="text-center mb-8">
        <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $title }}</h3>
        @if($subtitle)
        <p class="text-gray-500">{{ $subtitle }}</p>
        @endif
    </div>
    
    @if($layout === 'horizontal')
    <!-- Horizontal layout -->
    <div class="flex flex-col md:flex-row items-stretch gap-4 md:gap-0">
        @foreach($steps as $index => $step)
        <div class="flex-1 relative">
            <!-- Connector line -->
            @if($index < count($steps) - 1)
            <div class="hidden md:block absolute top-8 left-1/2 w-full h-0.5 bg-gradient-to-r from-blue-300 to-indigo-300"></div>
            @endif
            
            <div class="relative bg-white rounded-2xl p-6 text-center shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 mx-2">
                <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-3xl shadow-lg shadow-blue-200">
                    @if(isset($step['icon']))
                        @if(strlen($step['icon']) <= 4)
                            {{ $step['icon'] }}
                        @else
                            <img src="{{ $step['icon'] }}" alt="" class="w-8 h-8">
                        @endif
                    @else
                        <span class="text-white font-bold">{{ $index + 1 }}</span>
                    @endif
                </div>
                <h4 class="font-bold text-gray-900 mb-2">{{ $step['title'] }}</h4>
                <p class="text-sm text-gray-500 leading-relaxed">{{ $step['desc'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
    
    @elseif($layout === 'vertical')
    <!-- Vertical layout -->
    <div class="relative">
        <!-- Vertical line -->
        <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gradient-to-b from-blue-300 via-indigo-300 to-purple-300"></div>
        
        <div class="space-y-6">
            @foreach($steps as $index => $step)
            <div class="flex items-start gap-6 relative animate-fade-in-left" style="animation-delay: {{ $index * 0.1 }}s">
                <!-- Step number/icon -->
                <div class="relative z-10 w-16 h-16 flex-shrink-0 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-2xl shadow-lg shadow-blue-200">
                    @if(isset($step['icon']) && strlen($step['icon']) <= 4)
                        {{ $step['icon'] }}
                    @else
                        <span class="text-white font-bold">{{ $index + 1 }}</span>
                    @endif
                </div>
                
                <!-- Content -->
                <div class="flex-1 bg-white rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow duration-200">
                    <h4 class="font-bold text-gray-900 mb-1">{{ $step['title'] }}</h4>
                    <p class="text-sm text-gray-500">{{ $step['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    
    @else
    <!-- Grid layout -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ min(count($steps), 4) }} gap-4">
        @foreach($steps as $index => $step)
        <div class="bg-white rounded-2xl p-6 text-center shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 animate-fade-in-up" 
             style="animation-delay: {{ $index * 0.1 }}s">
            <div class="w-14 h-14 mx-auto mb-4 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-2xl shadow-md">
                @if(isset($step['icon']) && strlen($step['icon']) <= 4)
                    {{ $step['icon'] }}
                @else
                    <span class="text-white font-bold">{{ $index + 1 }}</span>
                @endif
            </div>
            <h4 class="font-bold text-gray-900 mb-2">{{ $step['title'] }}</h4>
            <p class="text-sm text-gray-500">{{ $step['desc'] }}</p>
        </div>
        @endforeach
    </div>
    @endif
</div>
