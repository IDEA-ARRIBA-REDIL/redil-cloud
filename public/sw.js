// SAFE Service Worker for REDIL CLOUD
// This version avoids capturing navigation requests to prevent redirect issues in Safari/Android.

const CACHE_NAME = 'redil-pwa-v3';
const ASSETS_TO_CACHE = [
    '/assets/img/favicon/logo_crecer.ico',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// SAFE Fetch Interceptor
self.addEventListener('fetch', (event) => {
    // 1. CRITICAL: Bypass Service Worker for navigation requests.
    // This lets Laravel handle redirections (auth, login, etc.) natively.
    if (event.request.mode === 'navigate') {
        return;
    }

    // 2. Only handle safe GET requests
    if (event.request.method !== 'GET' || !event.request.url.startsWith('http')) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((response) => {
            // Return from cache or fetch from network
            return response || fetch(event.request);
        })
    );
});

// Listener para notificaciones Push (Preparación para Fase 3)
self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};
    const title = data.titulo || 'REDIL CLOUD';
    const options = {
        body: data.mensaje || 'Nueva notificación',
        icon: '/assets/img/favicon/logo_crecer.ico',
        badge: '/assets/img/favicon/logo_crecer.ico',
        data: {
             url: data.url || '/'
        }
    };
    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(clients.openWindow(event.notification.data.url));
});
