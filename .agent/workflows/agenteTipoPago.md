---
description: Carga el contexto y memoria del Agente de Tipo de Pago (Métodos de Pago Multi-Tenant)
---

# Agente de Tipo de Pago (Métodos de Pago Multi-Tenant)

Este documento describe el módulo de Tipos de Pago, su arquitectura, relaciones, almacenamiento de imágenes y su integración con el sistema multi-tenant.

## 1. Contexto del Módulo

Los **Tipos de Pago** representan los métodos de pago disponibles para cada iglesia (tenant). Cada tenant configura sus propios métodos (Zona Pagos, Efectivo PDP, PayPal, Efecty, etc.) con logos, fondos, credenciales de pasarela y configuraciones booleanas.

## 2. Modelo de Datos

### Tabla: `tipos_pago` (Base de datos TENANT)

La migración vive en `database/migrations/tenant/2024_11_01_213706_create_tipos_pago_table.php`.
La tabla ya está correctamente ubicada en la base de datos del tenant.

**Campos principales:**
- **Texto**: `nombre`, `enlace`, `cuenta_sap`, `client_id`, `key_id`, `bussines_id`, `url_retorno`, `identity_token`, `key_reservada`, `account_id`, `color`, `label_destinatario`, `observaciones`
- **Imágenes**: `imagen` (logo), `fondo` (imagen de fondo)
- **Numéricos**: `unica_moneda_id`, `porcentaje_tax1`, `porcentaje_tax2`, `transaccion_minima`, `transaccion_maxima`, `incremento_pdp`
- **Booleanos**: `activo`, `habilitado_punto_pago`, `subir_archivo_pagos`, `botones_valores_moneda`, `habilitado_donacion`, `tiene_limite_dinero_acumulado`, `punto_de_pago`, `permite_personas_externas`, `codigo_datafono`

### Modelo: `app/Models/TipoPago.php`

```php
class TipoPago extends Model
{
    protected $table = 'tipos_pago';
    // Relaciones: actividades() (BelongsToMany), estadosPago() (HasMany)
}
```

### Tablas Relacionadas (Pivote)
- `actividad_tipos_pago` — Vincula tipos de pago con actividades
- `curso_tipos_pago` — Vincula tipos de pago con cursos (LMS)

## 3. Controlador: `app/Http/Controllers/TipoPagosController.php`

### Acciones CRUD:
| Método | Ruta | Acción |
|--------|------|--------|
| `listarTipoPagos` | `GET /tipo_pagos/` | Lista paginada |
| `creacionTipoPagos` | `GET /tipo_pagos/crear` | Formulario de creación |
| `crearTipoPagos` | `POST /tipo_pagos/guardar` | Guardar nuevo |
| `actualizacionTipoPagos` | `GET /tipo_pagos/editar/{id}` | Formulario de edición |
| `actualizarTipoPagos` | `PUT /tipo_pagos/actualizar/{id}` | Guardar edición |
| `eliminarTipoPagos` | `DELETE /tipo_pagos/eliminar/{id}` | Eliminar |
| `toggleEstado` | `POST /tipo_pagos/cambiar-estado/{id}` | Toggle activo/inactivo (AJAX) |

### Rutas (en `routes/app.php` línea ~1071):
```php
Route::prefix('tipo_pagos')->name('tipo-pagos.')->group(function () { ... });
```

## 4. Almacenamiento de Imágenes (PROBLEMA ACTUAL)

### Situación Actual (Incorrecta)
Las imágenes se guardan con `Storage::disk('public')` en carpetas **globales compartidas**:
- `storage/app/public/logos/{nombre}.ext` — Logo del tipo de pago
- `storage/app/public/fondos/{nombre}.ext` — Fondo del tipo de pago

Esto causa que **todos los tenants compartan las mismas carpetas de archivos**, pudiendo sobreescribir o acceder a imágenes de otros tenants.

### Solución Correcta (Multi-Tenant)
En el entorno multi-tenant con `stancl/tenancy`, el Storage se aísla automáticamente por tenant. Cuando el tenant está inicializado:
- `Storage::disk('public')->put('logos/...', ...)` guarda en `storage/app/public/tenant{id}/logos/...`
- Para acceder a la URL pública: `asset('storage/' . tenant('id') . '/logos/' . $tipoPago->imagen)`

**Regla**: Si el `TenancyBootstrapper` de filesystem está habilitado, `Storage::disk('public')` ya apunta al directorio aislado del tenant. Solo se necesita verificar que `Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper` esté activo en `config/tenancy.php`.

## 5. Vistas Blade

| Vista | Ruta |
|-------|------|
| Listar | `resources/views/contenido/paginas/tipo-pagos/listar-tipo-pagos.blade.php` |
| Crear | `resources/views/contenido/paginas/tipo-pagos/crear-tipo-pagos.blade.php` |
| Editar | `resources/views/contenido/paginas/tipo-pagos/editar-tipo-pagos.blade.php` |

### Cropper.js
Las vistas de crear y editar usan **CropperJS** para recortar imágenes del logo y fondo antes de subirlas como Base64.

## 6. Integración con Checkout

### Componente Livewire: `app/Livewire/Carrito/Checkout.php`
- Carga tipos de pago de la actividad: `$this->actividad->tiposPago()->with('estadosPago')->get()`
- Vista: `resources/views/livewire/carrito/checkout.blade.php`

### Bug en la Vista Checkout (Línea 212)
```blade
{{-- BUG: exists() retorna true/false, no la URL --}}
<img src="{{ Storage::disk('public')->exists('logos/'.$tipo->imagen) }}" ...>
```
**Debería ser:**
```blade
<img src="{{ asset('storage/' . tenant('id') . '/logos/' . $tipo->imagen) }}" ...>
```

## 7. Instrucciones para el Agente

1. Read `app/Models/TipoPago.php` to understand the model structure and relationships.
2. Read `app/Http/Controllers/TipoPagosController.php` to understand the CRUD logic and image storage.
3. Read `app/Livewire/Carrito/Checkout.php` and `resources/views/livewire/carrito/checkout.blade.php` to understand how payment types are displayed in the checkout.
4. Read `config/tenancy.php` to verify the FilesystemTenancyBootstrapper configuration.
5. Adopt the persona: "Expert in Payment Methods & Multi-Tenant Storage".
6. Confirm to the user: "💳 **Agente de Tipo de Pago Activado**. Tengo cargado el contexto de Tipos de Pago, Storage Multi-Tenant y Checkout."
