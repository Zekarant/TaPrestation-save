/**
 * UX ENHANCEMENTS JAVASCRIPT
 * TaPrestation - Animations, Tooltips, Tours & Feedback System
 */

(function () {
    'use strict';

    // ============================================
    // HELPERS (audit 5.5 - XSS prevention)
    // ============================================
    function escapeHtml(str) {
        if (typeof str !== 'string') return '';
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // ============================================
    // CONFIGURATION
    // ============================================
    const UX = {
        config: {
            toastDuration: 5000,
            animationDelay: 100,
            tourStorageKey: 'taprestation_tours_completed'
        },

        // ============================================
        // TOAST NOTIFICATIONS SYSTEM
        // ============================================
        toast: {
            container: null,

            init() {
                if (!this.container) {
                    this.container = document.createElement('div');
                    this.container.className = 'toast-container';
                    document.body.appendChild(this.container);
                }
            },

            show(type, title, message, duration = UX.config.toastDuration) {
                this.init();

                const icons = {
                    success: '✓',
                    error: '✕',
                    warning: '⚠',
                    info: 'ℹ'
                };

                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.innerHTML = `
                    <div class="toast-icon">${icons[type] || ''}</div>
                    <div class="toast-content">
                        <div class="toast-title">${escapeHtml(title)}</div>
                        <div class="toast-message">${escapeHtml(message)}</div>
                    </div>
                    <div class="toast-close" onclick="this.parentElement.remove()">✕</div>
                    <div class="toast-progress"></div>
                `;

                this.container.appendChild(toast);

                setTimeout(() => {
                    toast.style.animation = 'fadeInRight 0.3s ease-out reverse';
                    setTimeout(() => toast.remove(), 300);
                }, duration);

                return toast;
            },

            success(title, message) { return this.show('success', title, message); },
            error(title, message) { return this.show('error', title, message); },
            warning(title, message) { return this.show('warning', title, message); },
            info(title, message) { return this.show('info', title, message); }
        },

        // ============================================
        // GUIDED TOUR SYSTEM
        // ============================================
        tour: {
            overlay: null,
            tooltip: null,
            currentStep: 0,
            steps: [],
            onComplete: null,
            tourId: null,

            init() {
                if (!this.overlay) {
                    this.overlay = document.createElement('div');
                    this.overlay.className = 'spotlight-overlay';
                    this.overlay.innerHTML = '<div class="spotlight-hole"></div>';
                    document.body.appendChild(this.overlay);
                }
            },

            start(tourId, steps, onComplete = null) {
                // Check if already completed
                const completed = JSON.parse(localStorage.getItem(UX.config.tourStorageKey) || '[]');
                if (completed.includes(tourId)) return;

                this.init();
                this.tourId = tourId;
                this.steps = steps;
                this.currentStep = 0;
                this.onComplete = onComplete;

                this.overlay.classList.add('active');
                this.showStep(0);
            },

            showStep(index) {
                const step = this.steps[index];
                if (!step) return this.end();

                const element = document.querySelector(step.target);
                if (!element) return this.nextStep();

                // Position spotlight
                const rect = element.getBoundingClientRect();
                const hole = this.overlay.querySelector('.spotlight-hole');
                hole.style.cssText = `
                    top: ${rect.top - 8}px;
                    left: ${rect.left - 8}px;
                    width: ${rect.width + 16}px;
                    height: ${rect.height + 16}px;
                `;

                // Create/update tooltip
                if (this.tooltip) this.tooltip.remove();
                this.tooltip = document.createElement('div');
                this.tooltip.className = 'tour-tooltip';
                this.tooltip.innerHTML = `
                    <div class="tour-tooltip-header">
                        <div class="tour-tooltip-step">${index + 1}</div>
                        <div class="tour-tooltip-title">${escapeHtml(step.title)}</div>
                    </div>
                    <div class="tour-tooltip-content">${escapeHtml(step.content)}</div>
                    <div class="tour-tooltip-actions">
                        <span class="tour-tooltip-skip" onclick="UX.tour.skip()">Passer le guide</span>
                        <div class="tour-tooltip-nav">
                            ${index > 0 ? '<button class="tour-btn tour-btn-prev" onclick="UX.tour.prevStep()">Précédent</button>' : ''}
                            <button class="tour-btn tour-btn-next" onclick="UX.tour.nextStep()">
                                ${index === this.steps.length - 1 ? 'Terminer' : 'Suivant'}
                            </button>
                        </div>
                    </div>
                    <div class="tour-progress">
                        ${this.steps.map((_, i) => `<div class="tour-dot ${i === index ? 'active' : ''}"></div>`).join('')}
                    </div>
                `;

                document.body.appendChild(this.tooltip);

                // Position tooltip
                const tooltipRect = this.tooltip.getBoundingClientRect();
                let top = rect.bottom + 16;
                let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);

                // Adjust if off screen
                if (left < 20) left = 20;
                if (left + tooltipRect.width > window.innerWidth - 20) {
                    left = window.innerWidth - tooltipRect.width - 20;
                }
                if (top + tooltipRect.height > window.innerHeight - 20) {
                    top = rect.top - tooltipRect.height - 16;
                }

                this.tooltip.style.top = `${top}px`;
                this.tooltip.style.left = `${left}px`;

                // Scroll into view
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            },

            nextStep() {
                this.currentStep++;
                if (this.currentStep >= this.steps.length) {
                    this.end();
                } else {
                    this.showStep(this.currentStep);
                }
            },

            prevStep() {
                if (this.currentStep > 0) {
                    this.currentStep--;
                    this.showStep(this.currentStep);
                }
            },

            skip() {
                this.end();
            },

            end() {
                // Mark as completed
                const completed = JSON.parse(localStorage.getItem(UX.config.tourStorageKey) || '[]');
                if (this.tourId && !completed.includes(this.tourId)) {
                    completed.push(this.tourId);
                    localStorage.setItem(UX.config.tourStorageKey, JSON.stringify(completed));
                }

                this.overlay.classList.remove('active');
                if (this.tooltip) this.tooltip.remove();

                if (this.onComplete) this.onComplete();

                // Show completion toast
                UX.toast.success('Guide terminé ! 🎉', 'Vous êtes prêt à utiliser cette fonctionnalité.');
            },

            reset(tourId = null) {
                const completed = JSON.parse(localStorage.getItem(UX.config.tourStorageKey) || '[]');
                if (tourId) {
                    const index = completed.indexOf(tourId);
                    if (index > -1) completed.splice(index, 1);
                } else {
                    completed.length = 0;
                }
                localStorage.setItem(UX.config.tourStorageKey, JSON.stringify(completed));
            }
        },

        // ============================================
        // LOADING STATES
        // ============================================
        loading: {
            overlay: null,

            show(text = 'Chargement en cours...') {
                if (!this.overlay) {
                    this.overlay = document.createElement('div');
                    this.overlay.className = 'loading-overlay';
                    this.overlay.innerHTML = `
                        <div class="loading-spinner loading-spinner-lg"></div>
                        <div class="loading-text">${text}</div>
                    `;
                    document.body.appendChild(this.overlay);
                }

                this.overlay.querySelector('.loading-text').textContent = text;
                requestAnimationFrame(() => this.overlay.classList.add('active'));
            },

            hide() {
                if (this.overlay) {
                    this.overlay.classList.remove('active');
                }
            },

            // Button loading state
            button(btn, loading = true, text = null) {
                if (loading) {
                    btn.disabled = true;
                    btn.dataset.originalText = btn.innerHTML;
                    btn.innerHTML = `<span class="loading-spinner loading-spinner-sm"></span> ${text || 'Chargement...'}`;
                } else {
                    btn.disabled = false;
                    btn.innerHTML = btn.dataset.originalText;
                }
            }
        },

        // ============================================
        // SCROLL ANIMATIONS
        // ============================================
        scrollAnimations: {
            observer: null,

            init() {
                this.observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('animate-fade-in-up');
                            entry.target.style.opacity = '1';
                            this.observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.1 });

                document.querySelectorAll('[data-animate-on-scroll]').forEach(el => {
                    el.style.opacity = '0';
                    this.observer.observe(el);
                });
            }
        },

        // ============================================
        // HELP TOOLTIPS (EXTENDED)
        // ============================================
        help: {
            createTooltip(element, content, position = 'top') {
                const wrapper = document.createElement('span');
                wrapper.className = `tooltip-wrapper tooltip-${position}`;

                const trigger = document.createElement('span');
                trigger.className = 'tooltip-trigger';
                trigger.textContent = '?';

                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip-content';
                tooltip.innerHTML = content;

                wrapper.appendChild(trigger);
                wrapper.appendChild(tooltip);

                if (element.parentNode) {
                    element.parentNode.insertBefore(wrapper, element.nextSibling);
                }

                return wrapper;
            },

            // Initialize all data-help elements
            init() {
                document.querySelectorAll('[data-help]').forEach(el => {
                    const content = el.dataset.help;
                    const position = el.dataset.helpPosition || 'top';
                    this.createTooltip(el, content, position);
                });
            }
        },

        // ============================================
        // FORM ENHANCEMENTS
        // ============================================
        forms: {
            init() {
                // Add shake animation on invalid fields
                document.querySelectorAll('form').forEach(form => {
                    form.addEventListener('submit', function (e) {
                        const invalid = form.querySelectorAll(':invalid');
                        if (invalid.length > 0) {
                            invalid.forEach(field => {
                                field.classList.add('animate-shake');
                                field.addEventListener('animationend', () => {
                                    field.classList.remove('animate-shake');
                                }, { once: true });
                            });
                        }
                    });
                });

                // Add floating labels effect
                document.querySelectorAll('.floating-label-input').forEach(input => {
                    input.addEventListener('focus', () => input.parentElement.classList.add('focused'));
                    input.addEventListener('blur', () => {
                        if (!input.value) input.parentElement.classList.remove('focused');
                    });
                    if (input.value) input.parentElement.classList.add('focused');
                });
            }
        },

        // ============================================
        // CONFIRMATION MODALS
        // ============================================
        confirm: {
            show(options) {
                return new Promise((resolve) => {
                    const modal = document.createElement('div');
                    modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4';
                    modal.innerHTML = `
                        <div class="fixed inset-0 bg-black/50" onclick="this.parentElement.remove(); resolve(false);"></div>
                        <div class="relative bg-white rounded-2xl p-6 max-w-sm w-full animate-scale-in">
                            <div class="text-center">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-3xl
                                    ${options.type === 'danger' ? 'bg-red-100' : 'bg-blue-100'}">
                                    ${escapeHtml(options.icon || '❓')}
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">${escapeHtml(options.title)}</h3>
                                <p class="text-gray-500 text-sm mb-6">${escapeHtml(options.message)}</p>
                                <div class="flex gap-3">
                                    <button class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition" 
                                            onclick="this.closest('.fixed').remove()">
                                        ${escapeHtml(options.cancelText || 'Annuler')}
                                    </button>
                                    <button class="flex-1 px-4 py-2.5 ${options.type === 'danger' ? 'bg-red-500 hover:bg-red-600' : 'bg-blue-500 hover:bg-blue-600'} text-white rounded-lg font-medium transition confirm-btn">
                                        ${escapeHtml(options.confirmText || 'Confirmer')}
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;

                    document.body.appendChild(modal);

                    modal.querySelector('.confirm-btn').addEventListener('click', () => {
                        modal.remove();
                        resolve(true);
                    });

                    modal.querySelector('.bg-black\\/50').addEventListener('click', () => {
                        modal.remove();
                        resolve(false);
                    });
                });
            },

            danger(title, message) {
                return this.show({
                    type: 'danger',
                    icon: '⚠️',
                    title,
                    message,
                    confirmText: 'Supprimer',
                    cancelText: 'Annuler'
                });
            }
        },

        // ============================================
        // COUNTERS & NUMBER ANIMATIONS
        // ============================================
        animateNumbers: {
            run(element, target, duration = 1500) {
                const start = 0;
                const startTime = performance.now();

                const update = (currentTime) => {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);

                    // Easing function (ease-out)
                    const eased = 1 - Math.pow(1 - progress, 3);
                    const current = Math.round(start + (target - start) * eased);

                    element.textContent = current.toLocaleString('fr-FR');

                    if (progress < 1) {
                        requestAnimationFrame(update);
                    }
                };

                requestAnimationFrame(update);
            },

            init() {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const target = parseInt(entry.target.dataset.countTo);
                            this.run(entry.target, target);
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.5 });

                document.querySelectorAll('[data-count-to]').forEach(el => {
                    observer.observe(el);
                });
            }
        },

        // ============================================
        // INITIALIZATION
        // ============================================
        init() {
            // Wait for DOM
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this._init());
            } else {
                this._init();
            }
        },

        _init() {
            this.help.init();
            this.forms.init();
            this.scrollAnimations.init();
            this.animateNumbers.init();

            // Add hover effects to cards
            document.querySelectorAll('.hover-card').forEach(card => {
                card.classList.add('hover-lift');
            });

            // Initialize staggered animations
            document.querySelectorAll('.stagger-children').forEach(parent => {
                parent.querySelectorAll(':scope > *').forEach((child, i) => {
                    child.style.animationDelay = `${i * 0.05}s`;
                    child.classList.add('animate-fade-in-up');
                });
            });
        }
    };

    // Expose to global scope
    window.UX = UX;

    // Auto-initialize
    UX.init();

})();
