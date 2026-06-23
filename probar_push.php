<?php

/**
 * Script temporal: Prueba de envío de Push Notification.
 *
 * Uso:
 *   1. Sube este archivo al servidor (raíz del proyecto)
 *   2. Ejecuta: php probar_push.php <tenant_id> <user_id>
 *   3. Ejemplo:  php probar_push.php crecer 1
 *   4. ELIMINA el archivo después de la prueba.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Notifications\NotificacionGeneral;
use Illuminate\Support\Facades\Notification;

$tenantId = $argv[1] ?? 'crecer';
$userId = (int) ($argv[2] ?? 1);

echo '=== Prueba de Push Notification ==='.PHP_EOL;
echo "Tenant: {$tenantId}".PHP_EOL;
echo "Usuario ID: {$userId}".PHP_EOL;
echo str_repeat('-', 40).PHP_EOL;

try {
    tenancy()->initialize($tenantId);
    echo "✅ Tenant inicializado: {$tenantId}".PHP_EOL;

    // Verificar suscripciones existentes
    $totalSubs = \App\Models\PushSubscription::count();
    echo "📊 Total suscripciones en la tabla: {$totalSubs}".PHP_EOL;

    if ($totalSubs === 0) {
        echo PHP_EOL;
        echo '⚠️  No hay suscripciones push registradas.'.PHP_EOL;
        echo '   Primero debes suscribirte desde la PWA en el navegador.'.PHP_EOL;
        echo PHP_EOL;
        echo '   Para simular una suscripción manualmente, descomenta el bloque'.PHP_EOL;
        echo "   'CREAR SUSCRIPCIÓN DE PRUEBA' en este script.".PHP_EOL;
        echo PHP_EOL;

        /*
         * BLOQUE: CREAR SUSCRIPCIÓN DE PRUEBA (para testing sin browser)
         * Descomenta esto solo para testing. Reemplaza los valores con datos reales
         * de una suscripción obtenida de web-push-libs.org/vapid-generator o similar.
         *
        $user = \App\Models\User::find($userId);
        if ($user) {
            $user->updatePushSubscription(
                endpoint: 'https://web.push.apple.com/TEST-ENDPOINT',
                publicKey: 'TEST_PUBLIC_KEY_BASE64',
                authToken: 'TEST_AUTH_TOKEN_BASE64',
                contentEncoding: 'aes128gcm'
            );
            echo "✅ Suscripción de prueba creada para user ID {$userId}" . PHP_EOL;
        }
        */

        tenancy()->end();
        exit(0);
    }

    // Obtener el usuario
    $user = \App\Models\User::find($userId);
    if (! $user) {
        echo "❌ Usuario ID {$userId} no encontrado en tenant {$tenantId}".PHP_EOL;
        tenancy()->end();
        exit(1);
    }

    $userSubs = $user->pushSubscriptions()->count();
    echo "👤 Usuario: {$user->primer_nombre} {$user->primer_apellido} (ID: {$user->id})".PHP_EOL;
    echo "📱 Suscripciones de este usuario: {$userSubs}".PHP_EOL;

    if ($userSubs === 0) {
        echo '⚠️  Este usuario no tiene suscripciones push.'.PHP_EOL;
        echo '   Prueba con otro user_id o suscríbete primero desde la PWA.'.PHP_EOL;
        tenancy()->end();
        exit(0);
    }

    // Enviar la notificación push
    echo PHP_EOL.'🚀 Enviando notificación push...'.PHP_EOL;

    Notification::sendNow($user, new NotificacionGeneral([
        'titulo' => '¡Prueba Push desde Servidor! 🎉',
        'mensaje' => 'Si ves esto en tu iPhone, el sistema de notificaciones push funciona correctamente.',
        'url' => '/dashboard',
        'icono' => 'ti-bell-ringing',
        'color' => 'success',
    ]));

    echo '✅ Notificación enviada. Revisa tu iPhone.'.PHP_EOL;

} catch (\Exception $e) {
    echo '❌ ERROR: '.$e->getMessage().PHP_EOL;
    echo PHP_EOL.'Trace:'.PHP_EOL;
    echo $e->getTraceAsString().PHP_EOL;
} finally {
    if (tenancy()->initialized) {
        tenancy()->end();
    }
}

echo PHP_EOL.'=== Fin de la prueba ==='.PHP_EOL;
