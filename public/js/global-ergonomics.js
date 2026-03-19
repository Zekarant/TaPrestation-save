/**
 * GLOBAL ERGONOMICS - Enhanced User Experience JavaScript
 * TaPrestation - Améliorations globales de l'ergonomie
 */

(function() {
    'use strict';

    // ==================================================
    // 1. RIPPLE EFFECT SUR LES BOUTONS
    // ==================================================
    function createRipple(event) {
        const button = event.currentTarget;
        
        // Ne pas créer de ripple si désactivé ou si c'est un lien
        if (button.disabled || button.classList.contains('no-ripple')) return;
        
        const circle = document.createElement('span');
        const diameter = Math.max(button.clientWidth, button.clientHeight);
        const radius = diameter / 2;
        
        const rect = button.getBoundingClientRect();
        circle.style.width = circle.style.height = `${diameter}px`;
        circle.style.left = `${event.clientX - rect.left - radius}px`;
        circle.style.top = `${event.clientY - rect.top - radius}px`;
        circle.classList.add('ripple-effect');
        
        // Supprimer les anciens ripples
        const ripple = button.querySelector('.ripple-effect');
        if (ripple) {
            ripple.remove();
        }
        
        button.appendChild(circle);
        
        // Supprimer après l'animation
        setTimeout(() => circle.remove(), 600);
    }

    // Appliquer aux boutons et liens interactifs
    function initRippleEffect() {
        const buttons = document.querySelectorAll('button:not(.no-ripple), .btn:not(.no-ripple), .dashboard-stat-card, .card-clickable');
        buttons.forEach(button => {
            button.addEventListener('click', createRipple);
            button.style.position = button.style.position || 'relative';
            button.style.overflow = 'hidden';
        });
    }

    // ==================================================
    // 2. SMOOTH SCROLL AVEC OFFSET POUR HEADER
    // ==================================================
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    const headerOffset = 80;
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                    
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    // ==================================================
    // 3. LAZY LOAD DES IMAGES
    // ==================================================
    function initLazyLoad() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.classList.add('loaded');
                            img.removeAttribute('data-src');
                        }
                        observer.unobserve(img);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    // ==================================================
    // 4. FOCUS VISIBLE POUR ACCESSIBILITÉ
    // ==================================================
    function initFocusVisible() {
        let hadKeyboardEvent = false;
        const keyboardKeys = ['Tab', 'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Enter', ' '];
        
        document.addEventListener('keydown', (e) => {
            if (keyboardKeys.includes(e.key)) {
                hadKeyboardEvent = true;
            }
        });

        document.addEventListener('mousedown', () => {
            hadKeyboardEvent = false;
        });

        document.addEventListener('focusin', (e) => {
            if (hadKeyboardEvent) {
                e.target.classList.add('focus-visible');
            }
        });

        document.addEventListener('focusout', (e) => {
            e.target.classList.remove('focus-visible');
        });
    }

    // ==================================================
    // 5. ANIMATIONS AU SCROLL (INTERSECTION OBSERVER)
    // ==================================================
    function initScrollAnimations() {
        const animatedElements = document.querySelectorAll('.animate-on-scroll, .card, .stat-card, .dashboard-stat-card');
        
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        // Délai progressif pour effet cascade
                        setTimeout(() => {
                            entry.target.classList.add('animate-visible');
                        }, index * 50);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '0px 0px -50px 0px',
                threshold: 0.1
            });

            animatedElements.forEach(el => {
                el.classList.add('animate-hidden');
                observer.observe(el);
            });
        }
    }

    // ==================================================
    // 6. FEEDBACK HAPTIQUE (VIBRATION) SUR MOBILE
    // ==================================================
    function initHapticFeedback() {
        // Désactivé : Chrome bloque navigator.vibrate jusqu'à interaction utilisateur
        // Voir: https://www.chromestatus.com/feature/5644273861001216
        // La vibration sera activée après le premier tap utilisateur
        if ('vibrate' in navigator) {
            let userHasTapped = false;
            
            document.addEventListener('touchstart', () => {
                userHasTapped = true;
            }, { once: true, passive: true });
            
            document.querySelectorAll('button, .btn, .dashboard-stat-card').forEach(el => {
                el.addEventListener('touchstart', () => {
                    if (userHasTapped) {
                        try {
                            navigator.vibrate(10);
                        } catch (e) {
                            // Ignorer silencieusement
                        }
                    }
                }, { passive: true });
            });
        }
    }

    // ==================================================
    // 7. AUTO-EXPAND TEXTAREA
    // ==================================================
    function initAutoExpandTextarea() {
        document.querySelectorAll('textarea[data-auto-expand], textarea.auto-expand').forEach(textarea => {
            textarea.style.overflow = 'hidden';
            textarea.style.resize = 'none';
            
            const resize = () => {
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            };
            
            textarea.addEventListener('input', resize);
            resize();
        });
    }

    // ==================================================
    // 8. LOADING STATE POUR FORMULAIRES
    // ==================================================
    function initFormLoadingState() {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.classList.add('is-loading');
                    submitBtn.disabled = true;
                    
                    // Réactiver après 10s en cas d'erreur
                    setTimeout(() => {
                        submitBtn.classList.remove('is-loading');
                        submitBtn.disabled = false;
                    }, 10000);
                }
            });
        });
    }

    // ==================================================
    // 9. TOOLTIPS NATIFS AMÉLIORÉS
    // ==================================================
    function initEnhancedTooltips() {
        document.querySelectorAll('[title]').forEach(el => {
            const title = el.getAttribute('title');
            if (!title) return;
            
            el.removeAttribute('title');
            el.dataset.tooltip = title;
            
            el.addEventListener('mouseenter', function(e) {
                const tooltip = document.createElement('div');
                tooltip.className = 'enhanced-tooltip';
                tooltip.textContent = this.dataset.tooltip;
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.left = `${rect.left + rect.width / 2 - tooltip.offsetWidth / 2}px`;
                tooltip.style.top = `${rect.top - tooltip.offsetHeight - 8}px`;
                
                this._tooltip = tooltip;
                
                requestAnimationFrame(() => tooltip.classList.add('visible'));
            });
            
            el.addEventListener('mouseleave', function() {
                if (this._tooltip) {
                    this._tooltip.remove();
                    this._tooltip = null;
                }
            });
        });
    }

    // ==================================================
    // 10. STICKY HEADER AU SCROLL
    // ==================================================
    function initStickyHeader() {
        const header = document.querySelector('nav, header');
        if (!header) return;
        
        let lastScroll = 0;
        
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll <= 0) {
                header.classList.remove('scroll-up', 'scroll-down');
                return;
            }
            
            if (currentScroll > lastScroll && !header.classList.contains('scroll-down')) {
                // Scroll vers le bas
                header.classList.remove('scroll-up');
                header.classList.add('scroll-down');
            } else if (currentScroll < lastScroll && header.classList.contains('scroll-down')) {
                // Scroll vers le haut
                header.classList.remove('scroll-down');
                header.classList.add('scroll-up');
            }
            
            lastScroll = currentScroll;
        }, { passive: true });
    }

    // ==================================================
    // 11. PULL TO REFRESH (Mobile)
    // ==================================================
    function initPullToRefresh() {
        let startY = 0;
        let pulling = false;
        
        document.addEventListener('touchstart', (e) => {
            if (window.scrollY === 0) {
                startY = e.touches[0].pageY;
                pulling = true;
            }
        }, { passive: true });
        
        document.addEventListener('touchmove', (e) => {
            if (!pulling) return;
            
            const y = e.touches[0].pageY;
            const diff = y - startY;
            
            if (diff > 100) {
                document.body.classList.add('pull-to-refresh-active');
            }
        }, { passive: true });
        
        document.addEventListener('touchend', () => {
            if (document.body.classList.contains('pull-to-refresh-active')) {
                document.body.classList.remove('pull-to-refresh-active');
                window.location.reload();
            }
            pulling = false;
        }, { passive: true });
    }

    // ==================================================
    // 12. DEBOUNCE ET THROTTLE UTILITIES
    // ==================================================
    window.debounce = function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    };

    window.throttle = function(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    };

    // ==================================================
    // 13. COPY TO CLIPBOARD
    // ==================================================
    window.copyToClipboard = function(text, successMessage = 'Copié !') {
        navigator.clipboard.writeText(text).then(() => {
            // Afficher notification toast si disponible
            if (window.Toast) {
                window.Toast.success(successMessage);
            } else {
                alert(successMessage);
            }
        }).catch(err => {
            console.error('Erreur copie:', err);
        });
    };

    // Boutons de copie
    document.querySelectorAll('[data-copy]').forEach(btn => {
        btn.addEventListener('click', function() {
            const text = this.dataset.copy || this.textContent;
            window.copyToClipboard(text);
        });
    });

    // ==================================================
    // STYLES CSS INJECTÉS
    // ==================================================
    const styles = `
        /* Ripple Effect */
        .ripple-effect {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        /* Scroll animations */
        .animate-hidden {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        
        .animate-visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Enhanced tooltip */
        .enhanced-tooltip {
            position: fixed;
            background: #1e293b;
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            z-index: 10000;
            pointer-events: none;
            opacity: 0;
            transform: translateY(5px);
            transition: opacity 0.2s, transform 0.2s;
            max-width: 250px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        
        .enhanced-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-top-color: #1e293b;
        }
        
        .enhanced-tooltip.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Sticky header */
        nav.scroll-down,
        header.scroll-down {
            transform: translateY(-100%);
            transition: transform 0.3s ease;
        }
        
        nav.scroll-up,
        header.scroll-up {
            transform: translateY(0);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        /* Pull to refresh indicator */
        .pull-to-refresh-active::before {
            content: '';
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            border: 3px solid #e2e8f0;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            z-index: 10000;
            margin-top: 20px;
        }
        
        @keyframes spin {
            to { transform: translateX(-50%) rotate(360deg); }
        }
        
        /* Focus visible */
        .focus-visible {
            outline: 2px solid #3b82f6 !important;
            outline-offset: 2px !important;
        }
        
        /* Loading state */
        .is-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.7;
        }
        
        .is-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        /* Lazy load images */
        img[data-src] {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        img.loaded {
            opacity: 1;
        }
    `;

    // Injecter les styles
    const styleSheet = document.createElement('style');
    styleSheet.textContent = styles;
    document.head.appendChild(styleSheet);

    // ==================================================
    // INITIALISATION
    // ==================================================
    function init() {
        initRippleEffect();
        initSmoothScroll();
        initLazyLoad();
        initFocusVisible();
        initScrollAnimations();
        initHapticFeedback();
        initAutoExpandTextarea();
        initFormLoadingState();
        initEnhancedTooltips();
        // initStickyHeader(); // Désactivé par défaut, peut causer des problèmes
        // initPullToRefresh(); // Désactivé par défaut
        
    }

    // Attendre le DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Réinitialiser après les mises à jour AJAX/Livewire
    document.addEventListener('livewire:load', init);
    document.addEventListener('turbo:load', init);
    document.addEventListener('ajax:complete', init);

})();
