import './bootstrap';
import './video-upload.js';
import './snap-camera.js';
import './media-compress.js';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

Alpine.data('notificationDropdown', () => ({
    open: false,
}));

Alpine.data('dismissibleAlert', (delay = 5000) => ({
    show: true,

    init() {
        const timeout = Number(delay);

        if (!Number.isFinite(timeout) || timeout <= 0) {
            return;
        }

        window.setTimeout(() => {
            this.show = false;
        }, timeout);
    },
}));

Alpine.data('persistentVisibility', (storageKey, hiddenValue = 'true') => ({
    visible: true,

    init() {
        if (!storageKey) {
            return;
        }

        try {
            this.visible = window.localStorage.getItem(storageKey) !== String(hiddenValue);
        } catch {
            this.visible = true;
        }
    },

    dismiss() {
        this.visible = false;

        if (!storageKey) {
            return;
        }

        try {
            window.localStorage.setItem(storageKey, String(hiddenValue));
        } catch {
            // Ignore localStorage access failures.
        }
    },
}));

Alpine.data('persistentDisclosure', (storageKey, hiddenValue = 'true', defaultExpanded = true) => ({
    visible: true,
    expanded: Boolean(defaultExpanded),

    init() {
        if (!storageKey) {
            return;
        }

        try {
            this.visible = window.localStorage.getItem(storageKey) !== String(hiddenValue);
        } catch {
            this.visible = true;
        }
    },

    dismiss() {
        this.visible = false;

        if (!storageKey) {
            return;
        }

        try {
            window.localStorage.setItem(storageKey, String(hiddenValue));
        } catch {
            // Ignore localStorage access failures.
        }
    },
}));

Alpine.data('modalDialog', (initialShow = false, shouldFocus = false) => ({
    show: Boolean(initialShow),
    shouldFocus: Boolean(shouldFocus),

    init() {
        this.$watch('show', (value) => {
            if (value) {
                document.body.classList.add('overflow-y-hidden');

                if (this.shouldFocus) {
                    window.setTimeout(() => {
                        this.firstFocusable()?.focus();
                    }, 100);
                }

                return;
            }

            document.body.classList.remove('overflow-y-hidden');
        });
    },

    focusables() {
        const selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])';

        return Array.from(this.$el.querySelectorAll(selector)).filter((element) => !element.hasAttribute('disabled'));
    },

    firstFocusable() {
        return this.focusables()[0];
    },

    lastFocusable() {
        return this.focusables().slice(-1)[0];
    },

    nextFocusable() {
        return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable();
    },

    prevFocusable() {
        return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable();
    },

    nextFocusableIndex() {
        return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1);
    },

    prevFocusableIndex() {
        return Math.max(0, this.focusables().indexOf(document.activeElement)) - 1;
    },
}));

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('click', (event) => {
    const target = event.target;

    if (!(target instanceof Element)) {
        return;
    }

    const card = target.closest('[data-card-link]');

    if (!card) {
        return;
    }

    if (target.closest('a, button, input, select, textarea, label, [data-card-link-ignore]')) {
        return;
    }

    const href = card.dataset.href;

    if (href) {
        window.location.href = href;
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter' && event.key !== ' ') {
        return;
    }

    const target = event.target;

    if (!(target instanceof Element)) {
        return;
    }

    const card = target.closest('[data-card-link]');

    if (!card || target !== card) {
        return;
    }

    event.preventDefault();

    const href = card.dataset.href;

    if (href) {
        window.location.href = href;
    }
});

document.addEventListener('error', (event) => {
    const image = event.target;

    if (!(image instanceof HTMLImageElement)) {
        return;
    }

    const fallbackSrc = image.dataset.fallbackSrc;

    if (!fallbackSrc || image.dataset.fallbackApplied === '1') {
        return;
    }

    image.dataset.fallbackApplied = '1';
    image.src = fallbackSrc;
}, true);

function initializeCompactFilters(root) {
    if (!(root instanceof HTMLElement) || root.dataset.compactFiltersReady === '1') {
        return;
    }

    root.dataset.compactFiltersReady = '1';

    const formAction = root.dataset.formAction || window.location.pathname;
    const form = root.querySelector('#compactFiltersForm');
    const text = root.querySelector('#filterToggleText');
    const chevron = root.querySelector('#filterChevron');
    const compactFiltersToggle = root.querySelector('#compactFiltersToggle');
    const mainCategorySelect = root.querySelector('#mainCategorySelect');
    const subcategoryContainer = root.querySelector('#subcategoryContainer');
    const subCategorySelect = root.querySelector('#subCategorySelect');
    const priceMinRange = root.querySelector('#priceMinRange');
    const priceMaxRange = root.querySelector('#priceMaxRange');
    const priceMinVal = root.querySelector('#priceMinVal');
    const priceMaxVal = root.querySelector('#priceMaxVal');
    const radiusRange = root.querySelector('#radiusRange');
    const radiusVal = root.querySelector('#radiusVal');
    const locationInput = root.querySelector('#locationInput');
    const latitudeInput = root.querySelector('#latitudeInput');
    const longitudeInput = root.querySelector('#longitudeInput');
    const suggestions = root.querySelector('#locationSuggestions');
    const gpsLocationButton = root.querySelector('#gpsLocationButton');
    const gpsIcon = root.querySelector('#gpsIcon');
    const compactFiltersReset = root.querySelector('#compactFiltersReset');
    const filterParamKeys = [
        'search', 'category', 'main_category', 'subcategory', 'sub_category',
        'price_min', 'price_max', 'location', 'city', 'radius', 'latitude', 'longitude',
        'sort', 'service_date', 'service_time', 'equipment_date_from', 'equipment_date_to',
        'available_from', 'available_to', 'verified_only', 'with_delivery', 'condition',
        'available_now', 'reservable', 'availability',
    ];

    let debounceTimer = null;
    let gpsInProgress = false;
    let subcategoriesData = {};

    try {
        subcategoriesData = JSON.parse(root.dataset.subcategories || '{}');
    } catch {
        subcategoriesData = {};
    }

    const toggleCompactFilters = (forceOpen = null) => {
        if (!form || !text || !chevron) {
            return;
        }

        const shouldOpen = forceOpen === null ? form.classList.contains('hidden') : Boolean(forceOpen);

        form.classList.toggle('hidden', !shouldOpen);
        root.classList.toggle('compact-filters--expanded', shouldOpen);
        root.classList.toggle('compact-filters--collapsed', !shouldOpen);
        text.textContent = shouldOpen ? 'Masquer' : 'Afficher';
        chevron.style.transform = shouldOpen ? 'rotate(180deg)' : 'rotate(0deg)';
    };

    const updatePriceMin = (value) => {
        const nextValue = Number.parseInt(value, 10);
        const maxValue = Number.parseInt(priceMaxRange?.value || '', 10);

        if (priceMinVal) {
            priceMinVal.textContent = `${value}€`;
        }

        if (Number.isFinite(nextValue) && Number.isFinite(maxValue) && nextValue > maxValue && priceMaxRange) {
            priceMaxRange.value = String(nextValue);

            if (priceMaxVal) {
                priceMaxVal.textContent = `${nextValue}€`;
            }
        }
    };

    const updatePriceMax = (value) => {
        const nextValue = Number.parseInt(value, 10);
        const minValue = Number.parseInt(priceMinRange?.value || '', 10);

        if (priceMaxVal) {
            priceMaxVal.textContent = `${value}€`;
        }

        if (Number.isFinite(nextValue) && Number.isFinite(minValue) && nextValue < minValue && priceMinRange) {
            priceMinRange.value = String(nextValue);

            if (priceMinVal) {
                priceMinVal.textContent = `${nextValue}€`;
            }
        }
    };

    const hideSuggestions = () => {
        if (suggestions) {
            suggestions.classList.add('hidden');
        }
    };

    const getLocationSuggestionName = (item) => item?.address?.city || item?.address?.town || item?.address?.village || item?.name || '';

    const selectLocation = (name, lat, lon) => {
        if (locationInput) {
            locationInput.value = name || '';
        }

        if (latitudeInput) {
            latitudeInput.value = lat || '';
        }

        if (longitudeInput) {
            longitudeInput.value = lon || '';
        }

        hideSuggestions();
    };

    const renderLocationSuggestions = (data) => {
        if (!suggestions) {
            return;
        }

        let hasItems = false;
        const fragment = document.createDocumentFragment();

        data.forEach((item) => {
            const name = getLocationSuggestionName(item);

            if (!name) {
                return;
            }

            hasItems = true;

            const suggestionItem = document.createElement('div');
            suggestionItem.className = 'suggestion-item';
            suggestionItem.dataset.name = name;
            suggestionItem.dataset.lat = item.lat;
            suggestionItem.dataset.lon = item.lon;

            const title = document.createElement('div');
            title.className = 'font-medium text-sm';
            title.textContent = name;

            const subtitle = document.createElement('div');
            const displayName = typeof item.display_name === 'string' ? item.display_name : name;
            subtitle.className = 'text-xs text-gray-500';
            subtitle.textContent = displayName.length > 50 ? `${displayName.slice(0, 50)}...` : displayName;

            suggestionItem.appendChild(title);
            suggestionItem.appendChild(subtitle);
            fragment.appendChild(suggestionItem);
        });

        suggestions.replaceChildren(fragment);
        suggestions.classList.toggle('hidden', !hasItems);
    };

    const createOption = (value, label) => {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = label;

        return option;
    };

    const updateSubcategories = (categoryId) => {
        if (!subcategoryContainer || !subCategorySelect) {
            return;
        }

        if (!categoryId) {
            subcategoryContainer.style.opacity = '0.5';
            subCategorySelect.disabled = true;
            subCategorySelect.replaceChildren(createOption('', 'Toutes'));
            return;
        }

        subcategoryContainer.style.opacity = '1';
        subCategorySelect.disabled = false;

        const children = Array.isArray(subcategoriesData[categoryId]) ? subcategoriesData[categoryId] : [];
        const fragment = document.createDocumentFragment();

        fragment.appendChild(createOption('', 'Toutes'));

        children.forEach((child) => {
            if (!child || child.id === undefined || child.id === null) {
                return;
            }

            fragment.appendChild(createOption(String(child.id), child.name || ''));
        });

        subCategorySelect.replaceChildren(fragment);
    };

    const finishGPSLookup = (iconClass = 'fas fa-crosshairs') => {
        if (gpsIcon) {
            gpsIcon.className = iconClass;
        }

        if (gpsLocationButton) {
            gpsLocationButton.disabled = false;
        }

        gpsInProgress = false;
    };

    const getGPSLocation = () => {
        if (gpsInProgress || !gpsLocationButton || !gpsIcon) {
            return;
        }

        gpsInProgress = true;
        gpsIcon.className = 'fas fa-spinner fa-spin';
        gpsLocationButton.disabled = true;

        if (!navigator.geolocation) {
            alert('Géolocalisation non supportée par votre navigateur');
            finishGPSLookup();
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;

                if (latitudeInput) {
                    latitudeInput.value = String(lat);
                }

                if (longitudeInput) {
                    longitudeInput.value = String(lon);
                }

                if (locationInput) {
                    locationInput.value = 'Position GPS...';
                }

                const controller = new AbortController();
                const timeoutId = window.setTimeout(() => controller.abort(), 8000);

                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=14&accept-language=fr`, {
                    signal: controller.signal,
                    headers: { 'User-Agent': 'TaPrestation/1.0' },
                })
                    .then((response) => response.json())
                    .then((data) => {
                        const address = data.address || {};
                        const city = address.city || address.town || address.village || address.municipality || address.county || '';
                        const postcode = address.postcode || '';

                        window.clearTimeout(timeoutId);

                        if (locationInput) {
                            locationInput.value = city + (postcode ? ` (${postcode})` : '');
                        }

                        if (gpsIcon) {
                            gpsIcon.className = 'fas fa-check text-green-500';
                            window.setTimeout(() => {
                                if (gpsIcon) {
                                    gpsIcon.className = 'fas fa-crosshairs';
                                }
                            }, 2500);
                        }
                    })
                    .catch(() => {
                        window.clearTimeout(timeoutId);

                        if (locationInput) {
                            locationInput.value = `Ma position (${lat.toFixed(2)}, ${lon.toFixed(2)})`;
                        }

                        if (gpsIcon) {
                            gpsIcon.className = 'fas fa-check text-green-500';
                            window.setTimeout(() => {
                                if (gpsIcon) {
                                    gpsIcon.className = 'fas fa-crosshairs';
                                }
                            }, 2500);
                        }
                    })
                    .finally(() => {
                        if (gpsLocationButton) {
                            gpsLocationButton.disabled = false;
                        }

                        gpsInProgress = false;
                    });
            },
            (error) => {
                let message = 'Erreur de géolocalisation: ';

                switch (error.code) {
                case error.PERMISSION_DENIED:
                    message += 'Permission refusée. Autorisez la localisation dans votre navigateur.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    message += 'Position indisponible.';
                    break;
                case error.TIMEOUT:
                    message += 'Délai dépassé. Réessayez.';
                    break;
                default:
                    message += 'Erreur inconnue.';
                    break;
                }

                alert(message);
                finishGPSLookup();
            },
            {
                enableHighAccuracy: false,
                timeout: 15000,
                maximumAge: 300000,
            },
        );
    };

    compactFiltersToggle?.addEventListener('click', () => {
        toggleCompactFilters();
    });

    mainCategorySelect?.addEventListener('change', (event) => {
        updateSubcategories(event.currentTarget.value);
    });

    priceMinRange?.addEventListener('input', (event) => {
        updatePriceMin(event.currentTarget.value);
    });

    priceMaxRange?.addEventListener('input', (event) => {
        updatePriceMax(event.currentTarget.value);
    });

    radiusRange?.addEventListener('input', (event) => {
        if (radiusVal) {
            radiusVal.textContent = event.currentTarget.value;
        }
    });

    gpsLocationButton?.addEventListener('click', () => {
        getGPSLocation();
    });

    compactFiltersReset?.addEventListener('click', () => {
        window.location.href = formAction;
    });

    locationInput?.addEventListener('input', (event) => {
        if (latitudeInput) {
            latitudeInput.value = '';
        }

        if (longitudeInput) {
            longitudeInput.value = '';
        }

        window.clearTimeout(debounceTimer);

        const query = event.currentTarget.value.trim();

        if (query.length < 3) {
            hideSuggestions();
            return;
        }

        debounceTimer = window.setTimeout(() => {
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=fr&limit=5`)
                .then((response) => response.json())
                .then((data) => {
                    if (!Array.isArray(data) || data.length === 0) {
                        hideSuggestions();
                        return;
                    }

                    renderLocationSuggestions(data);
                })
                .catch(() => {
                    hideSuggestions();
                });
        }, 300);
    });

    document.addEventListener('click', (event) => {
        if (!locationInput || !suggestions) {
            return;
        }

        const target = event.target;

        if (!(target instanceof Node)) {
            return;
        }

        if (!locationInput.contains(target) && !suggestions.contains(target)) {
            hideSuggestions();
        }
    });

    suggestions?.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof Element)) {
            return;
        }

        const item = target.closest('.suggestion-item');

        if (!(item instanceof HTMLElement)) {
            return;
        }

        selectLocation(item.dataset.name, item.dataset.lat, item.dataset.lon);
    });

    const params = new URLSearchParams(window.location.search);
    const hasFilterParams = filterParamKeys.some((key) => {
        const value = params.get(key);
        return value !== null && value !== '';
    });

    if (hasFilterParams) {
        toggleCompactFilters(true);
    }
}

function initializeUrgentSaleDistances() {
    if (!navigator.geolocation) {
        return;
    }

    const badges = Array.from(document.querySelectorAll('.sale-card[data-lat][data-lon] .sale-distance'));

    if (badges.length === 0) {
        return;
    }

    navigator.geolocation.getCurrentPosition((position) => {
        const userLatitude = position.coords.latitude;
        const userLongitude = position.coords.longitude;

        badges.forEach((badge) => {
            const card = badge.closest('.sale-card');

            if (!(card instanceof HTMLElement)) {
                return;
            }

            const latitude = Number.parseFloat(card.dataset.lat || '');
            const longitude = Number.parseFloat(card.dataset.lon || '');

            if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
                return;
            }

            const deltaLat = (latitude - userLatitude) * Math.PI / 180;
            const deltaLon = (longitude - userLongitude) * Math.PI / 180;
            const a = Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2)
                + Math.cos(userLatitude * Math.PI / 180) * Math.cos(latitude * Math.PI / 180)
                * Math.sin(deltaLon / 2) * Math.sin(deltaLon / 2);
            const kilometers = 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            const text = badge.querySelector('.dist-text');

            if (text) {
                text.textContent = kilometers < 1
                    ? `${Math.round(kilometers * 1000)} m`
                    : `${Math.round(kilometers * 10) / 10} km`;
            }
        });
    }, () => {
        badges.forEach((badge) => {
            if (badge instanceof HTMLElement) {
                badge.style.display = 'none';
            }
        });
    }, { timeout: 5000 });
}

function initializeMarketplacePages() {
    document.querySelectorAll('[data-compact-filters]').forEach((root) => {
        initializeCompactFilters(root);
    });

    initializeUrgentSaleDistances();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMarketplacePages, { once: true });
} else {
    initializeMarketplacePages();
}

// Code existant
const actionButton = document.getElementById('actionButton');
if (actionButton) {
    actionButton.addEventListener('click', function() {
        alert('Button clicked!');
    });
}
