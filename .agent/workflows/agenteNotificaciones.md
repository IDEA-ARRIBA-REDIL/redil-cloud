---
description: Carga el contexto y memoria del Agente de Notificaciones (Arquitectura Event-Driven y Gatillos)
---

# Agente de Notificaciones (`agenteNotificaciones`)

## 1. Activación de Persona (desde `/baseDesarrollo`)

- **Rol**: Experto en Laravel 11, Event-Driven Architecture, Sistema de Notificaciones Multi-Tenant, Livewire 3 y PostgreSQL JSON Operations.
- **Idioma**: Español nativo.
- **Convenciones**: variables/funciones en `camelCase`, código testeable, uso estricto del patrón "Gatillo -> Despacho".

---

## 2. Visión General del Sistema (Event-Driven)

Este agente se encarga de extender y mantener el sistema interno de notificaciones de **REDIL CLOUD**. La lógica principal está centralizada en el `NotificacionService`, el cual actúa como el "Cerebro" de despachos, recibiendo llamados estandarizados (Gatillos) desde distintos puntos de la aplicación.

### Capacidades del NotificacionService:

- Resuelve dinámicamente la audiencia basándose en el alcance configurado (`global`, `individual`, `ministerio_directo`, `escala_ministerial`).
- Filtra notificaciones basándose en `sede_id` y `tipo_usuario_id`.
- Permite inyectar una fecha de expiración automática (`expira_en`).
- Registra logs detallados sobre el tamaño de la audiencia o la ausencia de la misma para auditoría (`storage/logs/laravel.log`).
- Obtiene automáticamente el **título** desde la base de datos (tabla `tipos_notificaciones`), evitando el "hardcodeo" en los controladores.
- La inserción real a la base de datos es asíncrona mediante colas (Queue), ya que la clase `NotificacionGeneral` implementa `ShouldQueue`. **(Requiere ejecutar `php artisan queue:work` o usar `QUEUE_CONNECTION=sync`)**.
- Se comunica directamente con la capa de persistencia nativa de Laravel (`DatabaseChannel`).

---

## 3. Implementación Estándar de un Gatillo

Cada vez que se desee enviar una notificación desde algún proceso (ej. crear grupo, aprobar pago, etc.), se debe inyectar el siguiente bloque `try/catch` para evitar que un fallo en notificaciones rompa el flujo principal de la aplicación:

```php
try {
    \App\Services\NotificacionService::dispatch(
        'slug_del_tipo_notificacion', // Slug registrado en la BD (ej. crear_persona)
        [
            // NOTA: No enviar 'titulo', el servicio lo lee de la BD
            'mensaje' => 'Cuerpo descriptivo de la notificación.',
            'url' => '/ruta/destino', // Opcional
            'icono' => 'ti ti-icon-name text-color', // Iconos de Tabler Icons
            'color' => 'primary', // success, danger, warning, info
        ],
        auth()->user() // Usuario originador (para evitar auto-notificarse si no es alcance individual)
    );
} catch (\Exception $e) {
    \Illuminate\Support\Facades\Log::error('Error disparando notificación [slug]: ' . $e->getMessage());
}
```

---

## 4. Registro de Gatillos Implementados

### Gatillos Activos en la Aplicación

1. **`crear_persona`** (Asistentes): Se dispara en `UserController@store` al registrar una nueva persona.
2. **`grupo_reporte_creado`** (Grupos): Se dispara en el componente Livewire `ModalNuevoReporte` al enviar la asistencia de un grupo.
3. **`grupo_creado`** (Grupos): Se dispara en `GrupoController@crear` cuando se da de alta un nuevo grupo en el sistema.

---

## 5. Archivos Clave del Módulo

| Archivo                                                    | Descripción                                                                    |
| ---------------------------------------------------------- | ------------------------------------------------------------------------------ |
| `app/Services/NotificacionService.php`                     | Servicio que resuelve la audiencia y despacha notificaciones.                  |
| `app/Models/TipoNotificacion.php`                          | Modelo de la tabla `tipos_notificaciones` (Guarda la configuración).           |
| `app/Notifications/NotificacionGeneral.php`                | Clase de Laravel Notification genérica (guarda datos en JSON).                 |
| `app/Livewire/Notificaciones/AdminTiposNotificaciones.php` | Interfaz administrativa para que el líder configure los alcances de cada slug. |

---

**Nota**: Este agente se activa con `/agenteNotificaciones`. Si un usuario necesita implementar un nuevo "Gatillo", se deberá agregar la llamada al servicio en el controlador correspondiente y actualizar el listado de gatillos en este documento.
