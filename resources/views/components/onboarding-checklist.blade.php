{{--
    Onboarding Checklist Component - Shows progress for new users
    Usage: 
    <x-onboarding-checklist 
        :steps="[
            ['key' => 'profile', 'title' => 'Compléter profil', 'completed' => true, 'url' => '/profile'],
            ['key' => 'service', 'title' => 'Créer service', 'completed' => false, 'url' => '/services/create'],
        ]"
    />
--}}

@props([
    'steps' => [],
    'title' => '🚀 Pour bien démarrer',
    'dismissible' => true,
    'storageKey' => 'onboarding_checklist'
])

@php
    $completedCount = collect($steps)->where('completed', true)->count();
    $totalSteps = count($steps);
    $progress = $totalSteps > 0 ? round(($completedCount / $totalSteps) * 100) : 0;
    $allCompleted = $completedCount === $totalSteps;
@endphp

@if(!$allCompleted)
<div x-data="persistentDisclosure('{{ $storageKey }}_dismissed')"
     x-show="visible"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform -translate-y-4"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl border border-indigo-200 shadow-lg mb-6 overflow-hidden">
    
    <!-- Header -->
    <div class="p-4 flex items-center justify-between cursor-pointer" @click="expanded = !expanded">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                <span class="text-xl">🎯</span>
            </div>
            <div>
                <h3 class="font-bold text-indigo-900">{{ $title }}</h3>
                <p class="text-sm text-indigo-600">{{ $completedCount }}/{{ $totalSteps }} étapes complétées</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- Progress circle -->
            <div class="relative w-12 h-12">
                <svg class="w-12 h-12 transform -rotate-90">
                    <circle cx="24" cy="24" r="20" stroke="#E0E7FF" stroke-width="4" fill="none"/>
                    <circle cx="24" cy="24" r="20" stroke="#6366F1" stroke-width="4" fill="none"
                            stroke-dasharray="{{ 2 * 3.14159 * 20 }}"
                            stroke-dashoffset="{{ 2 * 3.14159 * 20 * (1 - $progress / 100) }}"
                            stroke-linecap="round"
                            class="transition-all duration-500"/>
                </svg>
                <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-indigo-600">{{ $progress }}%</span>
            </div>
            
            <!-- Expand/Collapse -->
            <button class="p-1 text-indigo-400 hover:text-indigo-600 transition-colors">
                <svg class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': !expanded }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            
            @if($dismissible)
            <button @click.stop="dismiss()" 
                    class="p-1 text-indigo-300 hover:text-indigo-500 transition-colors"
                    title="Masquer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            @endif
        </div>
    </div>
    
    <!-- Steps list -->
    <div x-show="expanded" x-collapse class="px-4 pb-4">
        <div class="space-y-2">
            @foreach($steps as $index => $step)
            <a href="{{ $step['url'] ?? '#' }}" 
               class="flex items-center gap-3 p-3 rounded-lg transition-all duration-200 {{ $step['completed'] ? 'bg-green-50 border border-green-200' : 'bg-white border border-gray-200 hover:border-indigo-300 hover:shadow-md' }}">
                
                <!-- Status icon -->
                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center {{ $step['completed'] ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-400' }}">
                    @if($step['completed'])
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    @else
                        <span class="text-sm font-bold">{{ $index + 1 }}</span>
                    @endif
                </div>
                
                <!-- Step info -->
                <div class="flex-1 min-w-0">
                    <p class="font-medium {{ $step['completed'] ? 'text-green-700 line-through' : 'text-gray-900' }}">
                        {{ $step['title'] }}
                    </p>
                    @if(isset($step['description']))
                    <p class="text-sm {{ $step['completed'] ? 'text-green-600' : 'text-gray-500' }} truncate">
                        {{ $step['description'] }}
                    </p>
                    @endif
                </div>
                
                <!-- Action arrow -->
                @if(!$step['completed'])
                <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                @endif
            </a>
            @endforeach
        </div>
        
        <!-- Completion message -->
        @if($progress >= 75 && !$allCompleted)
        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-sm text-yellow-700 flex items-center gap-2">
                <span class="text-lg">🎉</span>
                Vous y êtes presque ! Plus que {{ $totalSteps - $completedCount }} étape(s) pour terminer votre profil.
            </p>
        </div>
        @endif
    </div>
</div>
@endif
