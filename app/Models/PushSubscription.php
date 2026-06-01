<?php

namespace App\Models;

use NotificationChannels\WebPush\PushSubscription as BasePushSubscription;

/**
 * Modelo PushSubscription compatible con multi-tenancy.
 *
 * El modelo base del package tiene una conexión de BD configurada en su
 * constructor que no respeta la conexión dinámica del tenant. Al extenderlo
 * aquí y forzar $connection = null, Laravel usará siempre la conexión
 * activa en el momento (la del tenant autenticado).
 */
class PushSubscription extends BasePushSubscription
{
    /**
     * El constructor base del package fuerza la conexión a `config('webpush.database_connection')`.
     * Lo sobrescribimos para restablecer la conexión a null, forzando a Laravel
     * a usar la conexión dinámica del tenant.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection(null);
    }
}
