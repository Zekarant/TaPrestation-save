// Service Worker for TaPrestation PWA
// Version 5.0 - Security-hardened caching strategy

// Import OneSignal SDK
importScripts('https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.sw.js');

const CACHE_NAME = 'taprestation-v8-branding';
const OFFLINE_URL = '/offline.html';
const NOTIFICATION_CHECK_INTERVAL = 60000; // 60 secondes (économie batterie)

// Assets to cache for offline use
const ASSETS_TO_CACHE = [
    '/',
    '/offline.html',
    '/images/logo.png',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/css/mobile-app.css'
];

// Install event - cache essential assets
self.addEventListener('install', (event) => {
    console.log('[SW] Installing service worker...');
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[SW] Caching essential assets');
            return cache.addAll(ASSETS_TO_CACHE).catch(err => {
                console.log('[SW] Some assets failed to cache:', err);
            });
        })
    );
    self.skipWaiting();
});

// Fetch event - network first. Cache only static assets to avoid storing private HTML.
self.addEventListener('fetch', (event) => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') return;
    
    const url = new URL(event.request.url);
    const isSameOrigin = url.origin === self.location.origin;

    // Never intercept cross-origin requests
    if (!isSameOrigin) {
        return;
    }

    // Skip API and dynamic requests
    if (url.pathname.startsWith('/api/') || 
        url.pathname.startsWith('/sanctum/') ||
        url.pathname.includes('livewire')) {
        return;
    }

    // Demo images must stay fresh and should never be served from stale SW cache.
    if (url.pathname === '/serve-image.php' || url.pathname.startsWith('/storage/demo-marketplace/')) {
        event.respondWith(
            fetch(event.request, { cache: 'no-store' }).catch(() =>
                caches.match(event.request).then((response) => response || new Response('Offline', { status: 503 }))
            )
        );
        return;
    }

    // For HTML navigation: network only, fallback to offline page.
    if (event.request.mode === 'navigate' || event.request.destination === 'document') {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    const isStaticAssetRequest =
        ['style', 'script', 'image', 'font'].includes(event.request.destination) ||
        /\.(?:css|js|mjs|png|jpg|jpeg|gif|webp|svg|ico|woff2?|ttf|eot)$/i.test(url.pathname);

    // Do not cache other request types
    if (!isStaticAssetRequest) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Clone the response for caching
                const responseClone = response.clone();
                const cacheControl = (response.headers.get('Cache-Control') || '').toLowerCase();
                
                // Only cache successful responses
                if (response.status === 200 && !cacheControl.includes('no-store')) {
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                
                return response;
            })
            .catch(() => {
                // Network failed, try cache
                return caches.match(event.request).then((response) => {
                    if (response) {
                        return response;
                    }
                    
                    // Return offline page for navigation requests
                    if (event.request.mode === 'navigate') {
                        return caches.match(OFFLINE_URL);
                    }
                    
                    return new Response('Offline', { status: 503 });
                });
            })
    );
});

// Push notification event
self.addEventListener('push', (event) => {
    console.log('[SW] Push notification received');
    
    let data = {
        title: 'TaPrestation',
        body: 'Vous avez une nouvelle notification',
        icon: '/icons/icon-192x192.png',
        badge: '/icons/icon-72x72.png',
        url: '/notifications',
        tag: 'default'
    };
    
    try {
        if (event.data) {
            const payload = event.data.json();
            data = { ...data, ...payload };
        }
    } catch (e) {
        console.log('[SW] Error parsing push data:', e);
    }
    
    const options = {
        body: data.body,
        icon: data.icon,
        badge: data.badge,
        tag: data.tag,
        renotify: true,
        requireInteraction: false,
        vibrate: [200, 100, 200],
        data: {
            url: data.url,
            notificationId: data.notificationId
        },
        actions: [
            {
                action: 'view',
                title: 'Voir'
            },
            {
                action: 'dismiss',
                title: 'Ignorer'
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Notification click event
self.addEventListener('notificationclick', (event) => {
    console.log('[SW] Notification clicked');
    
    event.notification.close();
    
    const action = event.action;
    const url = event.notification.data?.url || '/notifications';
    
    if (action === 'dismiss') {
        return;
    }
    
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // Check if there's already a window open
            for (const client of clientList) {
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    client.focus();
                    client.navigate(url);
                    return;
                }
            }
            
            // Open new window if none exists
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});

// Notification close event
self.addEventListener('notificationclose', (event) => {
    console.log('[SW] Notification closed');
});

// Background sync for offline actions
self.addEventListener('sync', (event) => {
    console.log('[SW] Background sync:', event.tag);
    
    if (event.tag === 'sync-notifications' || event.tag === 'check-notifications') {
        event.waitUntil(checkAndShowNotifications());
    }
});

// Periodic background sync (si supporté)
self.addEventListener('periodicsync', (event) => {
    console.log('[SW] Periodic sync:', event.tag);
    if (event.tag === 'check-notifications') {
        event.waitUntil(checkAndShowNotifications());
    }
});

// Message handler pour recevoir des commandes du client
self.addEventListener('message', (event) => {
    console.log('[SW] Message received:', event.data);
    
    if (event.data && event.data.type === 'START_BACKGROUND_CHECK') {
        startBackgroundNotificationCheck();
    }
    
    if (event.data && event.data.type === 'CHECK_NOTIFICATIONS_NOW') {
        checkAndShowNotifications();
    }
});

// Variable pour le timer en arrière-plan
let backgroundCheckTimer = null;
const shownNotificationIds = new Set();
const shownNotificationQueue = [];
const MAX_TRACKED_NOTIFICATIONS = 200;

// Démarrer la vérification en arrière-plan
function startBackgroundNotificationCheck() {
    if (backgroundCheckTimer) {
        clearInterval(backgroundCheckTimer);
    }
    
    console.log('[SW] Starting background notification check every 60s');
    
    // Vérifier immédiatement
    checkAndShowNotifications();
    
    // Puis toutes les 60 secondes
    backgroundCheckTimer = setInterval(() => {
        checkAndShowNotifications();
    }, NOTIFICATION_CHECK_INTERVAL);
}

// Vérifier et afficher les notifications
async function checkAndShowNotifications() {
    try {
        const response = await fetch('/notifications/unpushed', {
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            return;
        }
        
        const data = await response.json();
        
        if (data.notifications && data.notifications.length > 0) {
            console.log('[SW] Background: Found', data.notifications.length, 'notifications');
            
            for (const notif of data.notifications) {
                const notifId = String(notif.id || '');
                if (notifId && shownNotificationIds.has(notifId)) {
                    continue;
                }

                await self.registration.showNotification(notif.title || 'TaPrestation', {
                    body: notif.body || 'Nouvelle notification',
                    icon: notif.icon || '/icons/icon-192x192.png',
                    badge: notif.badge || '/icons/icon-72x72.png',
                    tag: notif.tag || 'notification-' + notif.id,
                    vibrate: [200, 100, 200],
                    requireInteraction: true,
                    data: {
                        url: notif.url || '/notifications',
                        notificationId: notif.id
                    },
                    actions: [
                        { action: 'view', title: 'Voir' },
                        { action: 'dismiss', title: 'Ignorer' }
                    ]
                });

                if (notifId) {
                    shownNotificationIds.add(notifId);
                    shownNotificationQueue.push(notifId);
                    if (shownNotificationQueue.length > MAX_TRACKED_NOTIFICATIONS) {
                        const oldest = shownNotificationQueue.shift();
                        if (oldest) {
                            shownNotificationIds.delete(oldest);
                        }
                    }
                }
            }
        }
    } catch (error) {
        console.log('[SW] Background check error:', error.message);
    }
}

async function syncNotifications() {
    try {
        const response = await fetch('/notifications/unread-count');
        const data = await response.json();
        console.log('[SW] Synced notifications:', data);
    } catch (error) {
        console.log('[SW] Sync failed:', error);
    }
}

// Démarrer automatiquement la vérification au chargement du SW
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating service worker...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('[SW] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            // Démarrer la vérification en arrière-plan
            startBackgroundNotificationCheck();
            return self.clients.claim();
        })
    );
});
