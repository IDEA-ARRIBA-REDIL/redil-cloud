// Minimalist Service Worker for REDIL CLOUD
const CACHE_NAME = 'redil-pwa-v2';

// Archivos críticos mínimos
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

// Interceptación de peticiones (Fetch)
self.addEventListener('fetch', (event) => {
    // IMPORTANTE: NO interceptamos peticiones de navegación.
    // Esto permite que Laravel maneje todas las redirecciones (auth, login, etc) de forma nativa.
    if (event.request.mode === 'navigate') {
        return;
    }

    // Solo manejamos peticiones GET de assets estáticos
    if (event.request.method !== 'GET' || !event.request.url.startsWith('http')) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request);
        })
    );
});

// Listener para notificaciones Push (Fase 3)
self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};
    self.registration.showNotification(data.titulo || 'REDIL', {
        body: data.mensaje || 'Nueva notificación',
        icon: '/assets/img/favicon/logo_crecer.ico'
    });
});

