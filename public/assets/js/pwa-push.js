/**
 * PWA Push Notifications Manager - REDIL CLOUD
 * Gestiona los permisos y la suscripción al servicio de notificaciones Push.
 */

const PwaPush = {
  // Llave pública VAPID (se inyectará desde el layout)
  publicKey: window.VAPID_PUBLIC_KEY || null,

  /**
   * Inicializa el sistema de suscripción.
   */
  async init() {
    console.log('[PWA-Push] init() - Notification.permission:', Notification.permission);
    console.log('[PWA-Push] serviceWorker in navigator:', 'serviceWorker' in navigator);
    console.log('[PWA-Push] PushManager in window:', 'PushManager' in window);
    console.log('[PWA-Push] VAPID_PUBLIC_KEY:', this.publicKey ? 'PRESENTE' : 'FALTANTE');

    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
      console.warn('[PWA-Push] Este navegador no soporta notificaciones Push.');
      return;
    }

    if (this.publicKey) {
      console.log('[PWA-Push] VAPID_PUBLIC_KEY:', this.publicKey.substring(0, 20) + '...');
    }

    if (Notification.permission === 'granted') {
      console.log('[PWA-Push] Permiso ya concedido, suscribiendo...');
      this.subscribeUser();
    }
  },

  /**
   * Solicita permiso al usuario y se suscribe.
   */
  async requestPermission() {
    try {
      const permission = await Notification.requestPermission();
      if (permission === 'granted') {
        console.log('Permiso concedido para notificaciones.');
        await this.subscribeUser();
        return true;
      } else {
        console.warn('Permiso denegado para notificaciones.');
        return false;
      }
    } catch (error) {
      console.error('Error al solicitar permiso:', error);
      return false;
    }
  },

  /**
   * Obtiene la suscripción del navegador y la envía al servidor.
   */
  async subscribeUser() {
    try {
      console.log('[PWA-Push] subscribeUser() iniciado');
      const registration = await navigator.serviceWorker.ready;
      console.log('[PWA-Push] Service Worker ready:', registration);

      let subscription = await registration.pushManager.getSubscription();
      console.log('[PWA-Push] getSubscription():', subscription);

      if (subscription) {
        console.log('[PWA-Push] Suscripción existente encontrada, enviando al servidor...');
        await this.sendSubscriptionToServer(subscription);
        return;
      }

      const subscribeOptions = {
        userVisibleOnly: true,
        applicationServerKey: this.urlBase64ToUint8Array(this.publicKey)
      };
      console.log('[PWA-Push] Creando nueva suscripción con VAPID key...');

      subscription = await registration.pushManager.subscribe(subscribeOptions);
      console.log('[PWA-Push] Usuario suscrito correctamente:', subscription.endpoint);

      await this.sendSubscriptionToServer(subscription);
    } catch (error) {
      console.error('[PWA-Push] Error al suscribir al usuario:', error);
      console.error('[PWA-Push] Error stack:', error.stack);
    }
  },

  /**
   * Envía la suscripción al backend de Laravel.
   */
  async sendSubscriptionToServer(subscription) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    console.log('[PWA-Push] Enviando suscripción a /push-subscriptions...');

    try {
      const response = await fetch('/push-subscriptions', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(subscription)
      });

      const data = await response.json();
      console.log('[PWA-Push] Respuesta del servidor:', data);

      if (!response.ok) {
        throw new Error('Error al enviar la suscripción al servidor: ' + JSON.stringify(data));
      }

      console.log('[PWA-Push] Suscripción sincronizada con el servidor.');
    } catch (error) {
      console.error('[PWA-Push] Error de sincronización:', error);
    }
  },

  /**
   * Helper para convertir la llave VAPID al formato que requiere el navegador.
   */
  urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }
};

// Auto-inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
  PwaPush.init();
});
