{{--
    Feature Guide Component
    Usage: <x-feature-guide :steps="$steps" tourId="unique-id" title="Guide Title" />
--}}

@props([
    'tourId' => 'default-tour',
    'title' => 'Découvrez cette fonctionnalité',
    'steps' => [],
    'autoStart' => false
])

@if(count($steps) > 0)
<div id="guide-{{ $tourId }}" class="feature-guide-trigger" data-tour-id="{{ $tourId }}">
    <!-- Start Guide Button -->
    <button type="button" 
            onclick="startFeatureGuide('{{ $tourId }}')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl font-medium text-sm shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ $title }}</span>
    </button>
</div>

<script>
function startFeatureGuide(tourId) {
    const tours = {
        '{{ $tourId }}': @json($steps)
    };
    
    if (window.UX && window.UX.tour) {
        // Reset this tour to allow restart
        UX.tour.reset(tourId);
        UX.tour.start(tourId, tours[tourId]);
    }
}

@if($autoStart)
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        if (window.UX && window.UX.tour) {
            UX.tour.start('{{ $tourId }}', @json($steps));
        }
    }, 1000);
});
@endif
</script>
@endif
