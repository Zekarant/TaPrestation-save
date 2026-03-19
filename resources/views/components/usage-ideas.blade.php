{{--
    Usage Ideas Component - Shows suggestions and ideas for using a feature
    Usage: <x-usage-ideas :ideas="$ideas" title="Idées d'utilisation" />
--}}

@props([
    'title' => '💡 Idées d\'utilisation',
    'ideas' => [],
    'collapsible' => true,
    'defaultOpen' => false
])

<div class="usage-ideas-container my-6" 
     x-data="{ open: {{ $defaultOpen ? 'true' : 'false' }} }">
    
    @if($collapsible)
    <button @click="open = !open" 
            class="w-full flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-indigo-50 hover:from-purple-100 hover:to-indigo-100 rounded-2xl border border-purple-200 transition-all duration-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                <span class="text-xl">💡</span>
            </div>
            <span class="font-semibold text-gray-900">{{ $title }}</span>
            <span class="text-sm text-purple-600 bg-purple-100 px-2 py-0.5 rounded-full">{{ count($ideas) }} idées</span>
        </div>
        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" 
             :class="{ 'rotate-180': open }"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    @endif
    
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        
        @foreach($ideas as $idea)
        <div class="bg-white rounded-xl p-4 border border-gray-100 hover:border-purple-200 hover:shadow-md transition-all duration-200 group">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-purple-100 to-indigo-100 group-hover:from-purple-200 group-hover:to-indigo-200 rounded-lg flex items-center justify-center text-xl transition-colors duration-200">
                    {{ $idea['icon'] ?? '✨' }}
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-900 text-sm mb-1">{{ $idea['title'] }}</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">{{ $idea['desc'] }}</p>
                    @if(isset($idea['link']))
                    <a href="{{ $idea['link'] }}" class="inline-flex items-center gap-1 mt-2 text-xs text-purple-600 hover:text-purple-700 font-medium">
                        Essayer
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
