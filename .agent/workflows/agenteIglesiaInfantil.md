---
description: Carga el contexto y memoria del Agente de Iglesia Infantil
---

# Agente de Iglesia Infantil (`agenteIglesiaInfantil`)

## 1. Activación de Persona (desde `/baseDesarrollo`)

- **Rol**: Experto en Laravel 11, Livewire 3, Alpine.js y el módulo de Iglesia Infantil de REDIL-CLOUD.
- **Idioma**: Español nativo.
- **Convenciones**: camelCase para variables/métodos, commentarios numerados en bloques complejos.
- **UI/UX**: Bootstrap 5, SweetAlert2 para confirmaciones, `waves-effect` en botones, `@section` (no `@push`) para scripts, `@section('page-style')` para SCSS y `@section('vendor-script')` para JS de vendors.
- **Estructura de vistas**: Las vistas del módulo son blade normales que extienden `layouts/layoutMaster`, y dentro de ellas se montan componentes Livewire con `@livewire(...)`. **Nunca** una vista Livewire como entrada directa de una ruta.

> Este es un módulo independiente y NO debe modificar `ReporteReunionController` ni `ReunionesController`.

---

## 2. Arquitectura del Módulo

### Controlador

`app/Http/Controllers/IglesiaInfantilController.php`

Único punto de entrada para toda la lógica del módulo. 15 métodos:

| Método                                                      | Ruta   | Descripción                                                  |
| ----------------------------------------------------------- | ------ | ------------------------------------------------------------ |
| `administracion()`                                          | GET    | Vista de CRUD de salones y estaciones                        |
| `crearSalon(Request)`                                       | POST   | Crea un nuevo salón                                          |
| `actualizarSalon(Request, SalonInfantil)`                   | PATCH  | Edita nombre/descripción/activo de un salón                  |
| `eliminarSalon(SalonInfantil)`                              | DELETE | Elimina salón si no tiene registros                          |
| `asignarEstacionesSalon(Request, SalonInfantil)`            | POST   | `sync()` estaciones a un salón                               |
| `crearEstacion(Request)`                                    | POST   | Crea nueva estación global                                   |
| `actualizarEstacion(Request, EstacionSalonInfantil)`        | PATCH  | Edita una estación                                           |
| `checkin()`                                                 | GET    | Vista operativa del flujo de check-in                        |
| `registrar(Request)`                                        | POST   | Registra el ingreso de un menor                              |
| `procesarRetiro(Request)`                                   | POST   | Marca un registro como `entregado`                           |
| `listaTurno(Request)`                                       | GET    | Lista de registros del turno (Carga vacía si no hay reporte) |
| `actualizarSalonEstacion(Request, RegistroIglesiaInfantil)` | PATCH  | Cambia salón/estación de un registro en custodia             |
| `eliminarRegistro(RegistroIglesiaInfantil)`                 | DELETE | Elimina un registro en custodia                              |
| `imprimirTicket(RegistroIglesiaInfantil)`                   | GET    | Ticket térmico con QR (sin layout)                           |
| `exportarExcel(Request)`                                    | GET    | Descarga Excel (Incluye "Adulto que registró")               |

### Rutas

Definidas en `routes/app.php` bajo el prefijo `iglesia-infantil` y el nombre `iglesiaInfantil.*`:

```php
// Administración
Route::get('administracion', ...)           // iglesiaInfantil.administracion
Route::post('salones', ...)                 // iglesiaInfantil.salones.crear
Route::patch('salones/{salon}', ...)        // iglesiaInfantil.salones.actualizar
Route::delete('salones/{salon}', ...)       // iglesiaInfantil.salones.eliminar
Route::post('salones/{salon}/estaciones',..)// iglesiaInfantil.salones.estaciones.asignar
Route::post('estaciones', ...)              // iglesiaInfantil.estaciones.crear
Route::patch('estaciones/{estacion}', ...)  // iglesiaInfantil.estaciones.actualizar

// Operaciones
Route::get('checkin', ...)                  // iglesiaInfantil.checkin
Route::post('checkin/registrar', ...)       // iglesiaInfantil.checkin.registrar
Route::post('checkin/retiro', ...)          // iglesiaInfantil.checkin.retiro
Route::get('lista-turno', ...)              // iglesiaInfantil.listaTurno
Route::patch('registros/{registro}/salon-estacion', ...) // iglesiaInfantil.registro.actualizarSalonEstacion
Route::delete('registros/{registro}', ...) // iglesiaInfantil.registro.eliminar
Route::get('registros/{registro}/ticket',..)// iglesiaInfantil.registro.ticket
Route::get('exportar', ...)                 // iglesiaInfantil.exportar
```

---

## 3. Base de Datos

### Tabla `salones_infantil`

| Columna                    | Tipo          | Descripción              |
| -------------------------- | ------------- | ------------------------ |
| `id`                       | bigint PK     |                          |
| `nombre`                   | string(150)   | Nombre visible del salón |
| `descripcion`              | text nullable | Descripción opcional     |
| `activo`                   | boolean       | Default `true`           |
| `created_at`, `updated_at` | timestamps    |                          |

**Modelo**: `App\Models\SalonInfantil`

- Scope `activos()` → filtra `activo = true`
- Relación `estaciones()` → `belongsToMany(EstacionSalonInfantil, 'salon_infantil_estacion')`
- Relación `registros()` → `hasMany(RegistroIglesiaInfantil)`

---

### Tabla `estaciones_salon_infantil`

| Columna                    | Tipo          | Descripción                        |
| -------------------------- | ------------- | ---------------------------------- |
| `id`                       | bigint PK     |                                    |
| `nombre`                   | string(150)   | Ej: "General", "Cambio de Pañales" |
| `descripcion`              | text nullable |                                    |
| `created_at`, `updated_at` | timestamps    |                                    |

**Modelo**: `App\Models\EstacionSalonInfantil`

- Relación `salones()` → `belongsToMany(SalonInfantil, 'salon_infantil_estacion')`

---

### Tabla `salon_infantil_estacion` (pivot)

| Columna                      | Tipo                  |
| ---------------------------- | --------------------- |
| `salon_infantil_id`          | unsignedBigInteger FK |
| `estacion_salon_infantil_id` | unsignedBigInteger FK |

---

### Tabla `registros_iglesia_infantil`

| Columna                      | Tipo                           | Descripción                            |
| ---------------------------- | ------------------------------ | -------------------------------------- |
| `id`                         | bigint PK                      |                                        |
| `reporte_reunion_id`         | bigint FK                      | Reporte al que pertenece               |
| `menor_user_id`              | bigint FK                      | User del menor registrado              |
| `adulto_ingreso_user_id`     | bigint FK                      | User del adulto que entregó            |
| `adulto_retiro_user_id`      | bigint FK nullable             | User del adulto que retiró             |
| `servidor_ingreso_user_id`   | bigint FK                      | Staff que registró el ingreso          |
| `servidor_retiro_user_id`    | bigint FK nullable             | Staff que procesó el retiro            |
| `salon_infantil_id`          | bigint FK                      | Salón asignado                         |
| `estacion_salon_infantil_id` | bigint FK                      | Estación asignada                      |
| `estado`                     | enum `en_custodia`/`entregado` | Estado actual del menor                |
| `codigo_retiro`              | string(10)                     | Código único alfanumérico para retirar |
| `fecha`                      | date                           | Fecha del registro                     |
| `hora_entrada`               | time                           | Hora de ingreso                        |
| `hora_entrega`               | time nullable                  | Hora de retiro                         |
| `indicaciones_medicas`       | text nullable                  | Notas médicas del día                  |
| `created_at`, `updated_at`   | timestamps                     |                                        |

**Modelo**: `App\Models\RegistroIglesiaInfantil`

Relaciones: `menor`, `adultoIngreso`, `adultoRetiro`, `servidorIngreso`, `servidorRetiro`, `salon`, `estacion`, `reporteReunion`

Métodos:

- `estaEnCustodia(): bool` → estado === 'en_custodia'
- `fueEntregado(): bool` → estado === 'entregado'
- `static generarCodigoRetiro(int $reporteReunionId): string` → genera código único de 6 chars

---

## 4. Componente Livewire: `ReportesParaCheckin`

`App\Livewire\IglesiaInfantil\ReportesParaCheckin`
Vista: `resources/views/livewire/iglesia-infantil/reportes-para-checkin.blade.php`

**Propósito**: Buscador de `ReporteReunion` con `habilitar_preregistro_iglesia_infantil = true`. Ordenados por `fecha DESC`.

**Comportamiento**: Al seleccionar, dispara el evento JS `reporteIglesiaInfantilSeleccionado` con `{ reporteId, nombre }`. Además, el componente persiste el ID seleccionado al recargar la página.

**Optimización Móvil**:

- El buscador tiene un panel de resultados con `max-height: 300px` y scroll.
- Diseño compacto: íconos reducidos (32px), fuentes pequeñas (0.85rem) y truncado de texto para evitar desbordamientos.
- Persistencia: Inicializa el estado del reporte vía parámetro para sincronizarse con filtros de página.

**Uso en Blade**:

```blade
@livewire('IglesiaInfantil.reportes-para-checkin')
```

> **IMPORTANTE**: Para que aparezca un reporte en el selector, debe tener `habilitar_preregistro_iglesia_infantil = true` en la tabla `reporte_reuniones`.

---

## 5. Vistas Blade

| Archivo                    | Ruta                                  | Descripción                                                    |
| -------------------------- | ------------------------------------- | -------------------------------------------------------------- |
| `administracion.blade.php` | `/iglesia-infantil/administracion`    | CRUD de salones y estaciones (modales Bootstrap)               |
| `checkin.blade.php`        | `/iglesia-infantil/checkin`           | Flujo guiado de 5 pasos con Alpine.js (`x-data="checkinForm"`) |
| `lista-turno.blade.php`    | `/iglesia-infantil/lista-turno`       | Grid de tarjetas (cards) responsivas + Escáner QR integrado    |
| `ticket.blade.php`         | `/iglesia-infantil/ticket/{registro}` | Ticket térmico con QR (`@media print`), sin layout maestro     |

**Ruta de las vistas**: `resources/views/contenido/paginas/iglesia-infantil/`

---

## 6. Alpine.js — Flujo de Check-in

El componente `checkinForm` (registrado con `Alpine.data()` dentro de `alpine:init`) gestiona 5 pasos:

1. **Paso 0** — Selección del reporte de reunión (via evento Livewire `reporteIglesiaInfantilSeleccionado`)
2. **Paso 1** — Búsqueda del adulto responsable (via AJAX a `/api/usuarios/buscar`)
3. **Paso 2** — Selección del menor a cargo (via AJAX a `/api/usuarios/{id}/menores-a-cargo`)
4. **Paso 3** — Selección de salón, estación e indicaciones médicas del día
5. **Paso 4** — Resumen y envío del formulario `#formRegistro` via POST

Los salones y sus estaciones se pasan desde el controlador como colección PHP y se embeben en atributos `data-estaciones` de los `<option>` para evitar peticiones AJAX adicionales.

---

## 7. Excel Export

`App\Exports\IglesiaInfantilExport`

Implementa `FromCollection`, `WithHeadings`, `WithMapping`, `WithStyles`.
Recibe `$reporteReunionId` en el constructor.
Exporta 15 columnas: Fecha, Reunión, Nombre Menor, Edad, Adulto que registró (Adulto Ingreso), Adulto Retiro, Servidor Ingreso/Retiro, Salón, Estación, Indicaciones, Código, Hora Entrada/Entrega, Estado.

---

## 8. Permisos del Módulo

Definidos en `PermisoSeeder`. Asignados al rol `Super Administrador`:

| Permiso                                 | Descripción                                      |
| --------------------------------------- | ------------------------------------------------ |
| `iglesia_infantil.ver_administracion`   | Acceder a la configuración de salones/estaciones |
| `iglesia_infantil.gestionar_salones`    | Crear, editar, eliminar salones                  |
| `iglesia_infantil.gestionar_estaciones` | Crear y editar estaciones                        |
| `iglesia_infantil.ver_checkin`          | Acceder a la vista de check-in                   |
| `iglesia_infantil.registrar_menor`      | Registrar el ingreso de un menor                 |
| `iglesia_infantil.procesar_retiro`      | Procesar la entrega del menor                    |
| `iglesia_infantil.ver_lista_turno`      | Ver la lista del turno activo                    |

---

## 9. Menú

Ubicación en `verticalMenu.blade.php`: sección **Iglesia Infantil** con 3 subitems:

- Administración → `iglesiaInfantil.administracion`
- Check-in → `iglesiaInfantil.checkin`
- Lista del Turno → `iglesiaInfantil.listaTurno`

---

## 10. Seeder de Pruebas

`database/seeders/IglesiaInfantilSeeder.php`

Crea:

- 2 estaciones: `General` y `Cambio de Pañales`
- 4 salones: `Bebés (0-2 años)`, `Párvulos (2-4 años)`, `Preescolar (4-6 años)`, `Primaria (6-10 años)`
- 1 `ReporteReunion` de prueba con `habilitar_preregistro_iglesia_infantil = true`

Comando para ejecutar:

```bash
php artisan db:seed --class=IglesiaInfantilSeeder
```

---

## 11. Sistema de Retiro QR Modernizado

Se implementó una solución de retiro de alta velocidad basada en códigos QR para eliminar cuellos de botella durante la salida del servicio.

### Componentes Técnicos

- **Librería**: `html5-qrcode` accesible vía CDN.
- **Flujo Automático**: Al escanear un ticket válido, el sistema:
  1. Identifica el registro mediante el `codigo_retiro`.
  2. Valida que pertenezca al reporte activo.
  3. Cambia el estado a `entregado` y registra la `hora_entrega` automáticamente.
  4. Muestra un SweetAlert2 de éxito y recarga el listado.
- **Ubicación**: Integrado en la cabecera de la `lista-turno.blade.php` para fácil acceso.

---

## 12. Estándares de Diseño (Modernización 2026)

Para mejorar la usabilidad en tablets y móviles, el módulo sigue estos principios:

- **Cards vs Tables**: El listado de menores utiliza un sistema de **Cards Responsivas** (1 col en móvil, 2 en tablet, 3 en desktop).
- **Control de Carga**: No se muestran datos hasta que el usuario selecciona explícitamente un **Reporte de Reunión**, optimizando el rendimiento y privacidad.
- **UI de Ancho Completo**: Se eliminó la barra lateral en la lista de turnos para dar prioridad a los registros (`col-12`).
- **Input de Búsqueda**: El selector de reportes utiliza sombras suaves y un diseño compacto con altura máxima fija para no interrumpir el flujo visual.
