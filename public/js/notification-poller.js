/**
 * TaPrestation Notification Poller
 * Polls for new notifications and shows local push notifications
 * Same system as the test button - uses browser's showNotification API
 */

(function() {
    'use strict';

    // Configuration
    const POLL_INTERVAL = 15000; // 15 seconds
    const NOTIFICATION_CHECK_URL = '/api/notifications/check-new';
    let lastCheckTime = Date.now();
    let isPolling = false;
    let pollIntervalId = null;

    // Track shown notifications to avoid duplicates
    const shownNotificationIds = new Set();
    
    // Load shown IDs from localStorage
    try {
        const saved = localStorage.getItem('shownNotificationIds');
        if (saved) {
            JSON.parse(saved).forEach(id => shownNotificationIds.add(id));
            // Keep only last 100 to prevent memory bloat
            if (shownNotificationIds.size > 100) {
                const arr = Array.from(shownNotificationIds);
                shownNotificationIds.clear();
                arr.slice(-100).forEach(id => shownNotificationIds.add(id));
            }
        }
    } catch (e) {
    }

    function saveShownIds() {
        try {
            localStorage.setItem('shownNotificationIds', JSON.stringify(Array.from(shownNotificationIds)));
        } catch (e) {
        }
    }

    /**
     * Check if notifications are supported and permission is granted
     */
    function canShowNotifications() {
        if (!('Notification' in window)) {
            return false;
        }
        
        if (!('serviceWorker' in navigator)) {
            return false;
        }
        
        if (Notification.permission !== 'granted') {
            return false;
        }
        
        return true;
    }

    /**
     * Show a local push notification using service worker
     * This is the same method as the test button
     */
    async function showLocalNotification(notification) {
        try {
            const registration = await navigator.serviceWorker.ready;
            
            // Build notification options
            const options = {
                body: notification.body || notification.message || '',
                icon: notification.icon || '/icons/icon-192x192.png',
                badge: notification.badge || '/icons/icon-72x72.png',
                vibrate: [200, 100, 200],
                tag: notification.id || 'notification-' + Date.now(),
                data: {
                    url: notification.url || '/notifications',
                    notificationId: notification.id
                },
                requireInteraction: notification.requireInteraction || false,
                actions: notification.actions || [
                    { action: 'view', title: 'Voir' },
                    { action: 'dismiss', title: 'Ignorer' }
                ]
            };

            // Show the notification
            await registration.showNotification(notification.title || 'TaPrestation', options);
            
            // Mark as shown
            if (notification.id) {
                shownNotificationIds.add(notification.id);
                saveShownIds();
            }
            
            return true;
        } catch (error) {
            console.error('[NotificationPoller] Error showing notification:', error);
            return false;
        }
    }

    /**
     * Check for new notifications from the server
     */
    async function checkNewNotifications() {
        if (!canShowNotifications()) {
            return;
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                return;
            }

            const response = await fetch(NOTIFICATION_CHECK_URL + '?since=' + lastCheckTime, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                // User might not be logged in, or endpoint not available
                if (response.status === 401 || response.status === 404) {
                }
                return;
            }

            const data = await response.json();
            
            if (data.notifications && Array.isArray(data.notifications)) {
                for (const notification of data.notifications) {
                    // Skip if already shown
                    if (notification.id && shownNotificationIds.has(notification.id)) {
                        continue;
                    }
                    
                    // Show the notification
                    await showLocalNotification(notification);
                }
            }

            // Update last check time
            lastCheckTime = Date.now();
            
        } catch (error) {
            console.error('[NotificationPoller] Error checking notifications:', error);
        }
    }

    /**
     * Start polling for notifications
     */
    function startPolling() {
        if (isPolling) return;
        
        if (!canShowNotifications()) {
            return;
        }
        
        isPolling = true;

        // Check immediately
        checkNewNotifications();
        
        // Then check periodically
        pollIntervalId = setInterval(checkNewNotifications, POLL_INTERVAL);
    }

    /**
     * Stop polling
     */
    function stopPolling() {
        if (pollIntervalId) {
            clearInterval(pollIntervalId);
            pollIntervalId = null;
        }
        isPolling = false;
    }

    /**
     * Show a notification immediately (for use from other scripts)
     */
    function showNotificationNow(title, body, url = '/notifications', icon = null) {
        return showLocalNotification({
            id: 'manual-' + Date.now(),
            title: title,
            body: body,
            url: url,
            icon: icon
        });
    }

    // Expose globally
    window.TaPrestationNotifications = {
        startPolling: startPolling,
        stopPolling: stopPolling,
        checkNow: checkNewNotifications,
        showNotification: showNotificationNow,
        canShowNotifications: canShowNotifications
    };

    // Auto-start polling when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a bit to ensure user is authenticated
            setTimeout(startPolling, 2000);
        });
    } else {
        setTimeout(startPolling, 2000);
    }

    // Handle page visibility changes
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Page is hidden, reduce polling or stop
            // Keep polling to show notifications even when tab is hidden
        } else {
            // Page is visible again, check immediately
            if (isPolling) {
                checkNewNotifications();
            }
        }
    });

    // Start/stop based on user activity
    let idleTimeout = null;
    const IDLE_TIMEOUT = 5 * 60 * 1000; // 5 minutes

    function resetIdleTimer() {
        if (idleTimeout) {
            clearTimeout(idleTimeout);
        }
        
        if (!isPolling) {
            startPolling();
        }
        
        idleTimeout = setTimeout(function() {
            // User is idle, but keep polling for important notifications
        }, IDLE_TIMEOUT);
    }

    // Track user activity
    ['mousemove', 'keypress', 'scroll', 'click', 'touchstart'].forEach(function(event) {
        document.addEventListener(event, resetIdleTimer, { passive: true });
    });

})();
