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
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
      console.warn('Este navegador no soporta notificaciones Push.');
      return;
    }

    // Verificamos si ya tenemos permiso
    if (Notification.permission === 'granted') {
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
      const registration = await navigator.serviceWorker.ready;

      // Verificamos si ya existe una suscripción
      let subscription = await registration.pushManager.getSubscription();

      if (subscription) {
        // Si existe, la enviamos para asegurar que el servidor esté actualizado
        await this.sendSubscriptionToServer(subscription);
        return;
      }

      // Si no existe, creamos una nueva
      const subscribeOptions = {
        userVisibleOnly: true,
        applicationServerKey: this.urlBase64ToUint8Array(this.publicKey)
      };

      subscription = await registration.pushManager.subscribe(subscribeOptions);
      console.log('Usuario suscrito correctamente.');

      await this.sendSubscriptionToServer(subscription);
    } catch (error) {
      console.error('Error al suscribir al usuario:', error);
    }
  },

  /**
   * Envía la suscripción al backend de Laravel.
   */
  async sendSubscriptionToServer(subscription) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
      const response = await fetch('/push-subscriptions', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(subscription)
      });

      if (!response.ok) {
        throw new Error('Error al enviar la suscripción al servidor.');
      }

      console.log('Suscripción sincronizada con el servidor.');
    } catch (error) {
      console.error('Error de sincronización:', error);
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
