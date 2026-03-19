{{--
    Animated Counter Component - Animates number counting
    Usage: <x-animated-counter :value="1234" prefix="€" suffix="+" />
--}}

@props([
    'value' => 0,
    'prefix' => '',
    'suffix' => '',
    'duration' => 1500,
    'decimals' => 0
])

<span x-data="{ 
        current: 0, 
        target: {{ $value }},
        duration: {{ $duration }},
        decimals: {{ $decimals }},
        started: false,
        init() {
            // Use Intersection Observer to start animation when visible
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting && !this.started) {
                    this.started = true;
                    this.animate();
                }
            }, { threshold: 0.5 });
            observer.observe(this.$el);
        },
        animate() {
            const startTime = performance.now();
            const step = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / this.duration, 1);
                // Ease out cubic
                const eased = 1 - Math.pow(1 - progress, 3);
                this.current = this.target * eased;
                
                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    this.current = this.target;
                }
            };
            requestAnimationFrame(step);
        },
        get formatted() {
            return this.current.toLocaleString('fr-FR', { 
                minimumFractionDigits: this.decimals, 
                maximumFractionDigits: this.decimals 
            });
        }
     }"
     class="inline-block tabular-nums">
    <span>{{ $prefix }}</span><span x-text="formatted">0</span><span>{{ $suffix }}</span>
</span>
