// ============================================================================
// SAFE Service Worker para REDIL CLOUD - V4
// ============================================================================
// ¿CÓMO FUNCIONA ESTE ARCHIVO Y CÓMO CONECTA LA APP CON EL TELÉFONO?
//
// Este archivo es el "Service Worker" (SW). A diferencia del código JavaScript
// normal que vive y muere cuando cierras la pestaña del navegador, el SW es un
// proceso en segundo plano que el NAVEGADOR instala directamente en el SISTEMA
// OPERATIVO del usuario (iOS, Android, Windows).
//
// 1. App -> Navegador: Cuando el usuario entra a la web, registramos este archivo.
// 2. Navegador -> Teléfono: El navegador le dice al sistema "instala este worker
//    para esta web". Una vez instalado, el teléfono lo usa para interceptar red
//    y recibir notificaciones Push incluso si la app está cerrada.
// ============================================================================

const CACHE_NAME = 'redil-pwa-v4-force';
const ASSETS_TO_CACHE = ['/assets/img/favicon/logo_crecer.ico'];

// EVENTO INSTALL: Se ejecuta la primera vez que el teléfono descarga este archivo.
// Aquí guardamos en la memoria caché del teléfono los recursos más básicos.
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
  self.skipWaiting(); // Obliga a activar esta nueva versión inmediatamente sin esperar.
});

// EVENTO ACTIVATE: Se ejecuta después de la instalación.
// Aquí borramos cachés viejos para no llenar la memoria del teléfono de basura.
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim(); // Toma el control de todas las pestañas abiertas inmediatamente.
});

// EVENTO FETCH: Intercepta CADA petición de red (imágenes, páginas) que sale de la app hacia internet.
// Si no hay internet, podemos responder con lo que tengamos en caché.
self.addEventListener('fetch', event => {
  // 1. CRÍTICO: Ignoramos las peticiones de 'navegación' (cuando el usuario cambia de página).
  // Esto lo decidimos así para que la autenticación de Laravel (login, middleware)
  // no se rompa por culpa del caché del teléfono. Dejamos que Laravel controle el ruteo.
  if (event.request.mode === 'navigate') {
    return;
  }

  // 2. Solo interceptamos peticiones GET (imágenes, CSS, JS) seguras (http/https).
  if (event.request.method !== 'GET' || !event.request.url.startsWith('http')) {
    return;
  }

  // 3. Respondemos buscando en el caché primero; si no está, vamos a internet.
  event.respondWith(
    caches.match(event.request).then(response => {
      return response || fetch(event.request);
    })
  );
});

// ============================================================================
// CONEXIÓN DE NOTIFICACIONES: NAVEGADOR -> TELÉFONO
// ============================================================================

// EVENTO PUSH: Este evento es disparado directamente por el SISTEMA OPERATIVO
// (ej. Apple Push Notification Service o Google Firebase) hacia este Service Worker,
// INCLUSO SI LA APP ESTÁ CERRADA.
self.addEventListener('push', event => {
  const data = event.data ? event.data.json() : {};
  const title = data.titulo || 'REDIL CLOUD';
  const options = {
    body: data.mensaje || 'Nueva notificación',
    icon: '/pwa-icon.png',
    badge: '/pwa-icon.png', // El iconito blanco que sale en la barra superior del celular
    data: {
      url: data.url || '/' // Guardamos a dónde debe ir si toca la notificación
    }
  };
  // Le decimos al sistema operativo: "Muestra la alerta nativa en pantalla"
  event.waitUntil(self.registration.showNotification(title, options));
});

// EVENTO NOTIFICATIONCLICK: Se dispara cuando el usuario toca la notificación
// en su centro de notificaciones (burbuja de iOS o barra superior de Android).
self.addEventListener('notificationclick', event => {
  event.notification.close(); // Cerramos la alerta
  // Abrimos la app en la URL específica que venía en el payload
  event.waitUntil(clients.openWindow(event.notification.data.url));
});
