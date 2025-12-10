// Animations pour le hero
document.addEventListener('DOMContentLoaded', function() {
    // Animation des compteurs
    function animateCounters() {
        const counters = document.querySelectorAll('.counter');
        
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target')) || parseInt(counter.textContent.replace(/\D/g, ''));
            const duration = 2000; // 2 secondes
            const increment = target / (duration / 16); // 60 FPS
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                
                // Formatage du nombre avec le suffixe approprié
                let displayValue = Math.floor(current);
                if (target >= 1000) {
                    displayValue = Math.floor(current) + '+';
                } else {
                    displayValue = Math.floor(current).toString();
                }
                
                counter.textContent = displayValue;
            }, 16);
        });
    }
    
    // Observer pour déclencher l'animation quand les stats sont visibles
    const statsSection = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-3');
    if (statsSection) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    setTimeout(animateCounters, 500); // Délai pour l'effet
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.5
        });
        
        observer.observe(statsSection);
    }
    
    // Effet de parallaxe léger pour l'élément décoratif
    const decorativeElement = document.querySelector('.hero-decoration');
    if (decorativeElement) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            decorativeElement.style.transform = `translateY(${rate}px)`;
        });
    }
    
    // Amélioration de l'accessibilité - respect des préférences de mouvement
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        // Désactiver toutes les animations pour les utilisateurs qui préfèrent moins de mouvement
        const animatedElements = document.querySelectorAll('.animate-fade-in-up, .counter, .hero-button');
        animatedElements.forEach(el => {
            el.style.animation = 'none';
            el.style.opacity = '1';
            el.style.transform = 'none';
        });
    }
    
    // Effet de focus amélioré pour les champs de recherche
    const searchInputs = document.querySelectorAll('#search-query, #search-location');
    searchInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.2s ease';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
    
    // Validation en temps réel pour améliorer l'UX
    const searchForm = document.querySelector('form[action*="search"]');
    if (searchForm) {
        const queryInput = searchForm.querySelector('#search-query');
        const submitButton = searchForm.querySelector('button[type="submit"]');
        
        if (queryInput && submitButton) {
            queryInput.addEventListener('input', function() {
                if (this.value.trim().length > 0) {
                    submitButton.classList.add('pulse');
                    submitButton.style.animation = 'pulse 2s infinite';
                } else {
                    submitButton.classList.remove('pulse');
                    submitButton.style.animation = 'none';
                }
            });
        }
    }
});

// Animation pulse pour le bouton de recherche
const pulseKeyframes = `
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}`;

// Ajouter les keyframes au document
const style = document.createElement('style');
style.textContent = pulseKeyframes;
document.head.appendChild(style);