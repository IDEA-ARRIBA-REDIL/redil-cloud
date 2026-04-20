// Service Worker for REDIL CLOUD
const CACHE_NAME = 'redil-pwa-cache-v1';
const OFFLINE_URL = '/pagina-no-encontrada'; // Reutilizamos tu vista de error como fallback offline

// Archivos a cachear inicialmente (App Shell)
const ASSETS_TO_CACHE = [
    '/',
    '/manifest.json',
    '/pagina-no-encontrada',
    '/assets/img/favicon/logo_crecer.ico',
];

// Instalación
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
    self.skipWaiting();
});

// Activación
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
    // Solo manejamos peticiones GET
    if (event.request.method !== 'GET') return;

    event.respondWith(
        caches.match(event.request).then((response) => {
            // Retornar desde cache si existe
            if (response) {
                return response;
            }

            // Si no está en cache, intentar red
            return fetch(event.request).catch(() => {
                // Si falla la red y es una navegación de página, mostrar offline fallback
                if (event.request.mode === 'navigate') {
                    return caches.match(OFFLINE_URL);
                }
            });
        })
    );
});

// Listener para notificaciones Push (Preparación Fase 3)
self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};
    const title = data.titulo || 'Nueva notificación';
    const options = {
        body: data.mensaje || 'Tienes una nueva actualización en REDIL.',
        icon: data.icono_url || '/assets/img/favicon/logo_crecer.ico',
        badge: '/assets/img/favicon/logo_crecer.ico',
        data: {
            url: data.url || '/'
        }
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Clic en la notificación
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data.url)
    );
});
