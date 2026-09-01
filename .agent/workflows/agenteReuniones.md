---
description: Carga el contexto y memoria del Agente de Reuniones y Reporte de Reuniones
---

# Agente de Reuniones (`agenteReuniones`)

## 1. Activación de Persona (desde `/baseDesarrollo`)

- **Rol**: Experto en Laravel 11, Livewire 3, Alpine.js, Bootstrap 5 aplicado al módulo de gestión de Reuniones (CRUD base).
- **Idioma**: Español nativo.
- **Convenciones**: variables/funciones en `camelCase`, comentarios numerados en bloques complejos.
- **UI/UX**: tablas con `dashed-border`, botones `rounded-pill`, SweetAlert2 para eliminaciones, Select2 con `wire:ignore`.

> Para todo lo relacionado a **Reportes, Asistentes y Reservas**, activar `/agenteReporteReuniones`.

---

## 2. Tabla `reuniones`

| Columna                                  | Tipo        | Descripción                                               |
| ---------------------------------------- | ----------- | --------------------------------------------------------- |
| `id`                                     | bigint PK   |                                                           |
| `hora`                                   | time        | Hora habitual de la reunión                               |
| `nombre`                                 | string(100) | Nombre de la reunión                                      |
| `portada`                                | string(500) | Imagen de portada (default: `default.png`)                |
| `descripcion`                            | text        | Descripción opcional                                      |
| `sede_id`                                | integer     | Sede principal                                            |
| `genero`                                 | string(100) | JSON de géneros permitidos (`["M","F"]`)                  |
| `habilitar_reserva`                      | boolean     | Si tiene sistema de reservas                              |
| `dias_plazo_reporte`                     | smallint    | Días desde la fecha del reporte para registrar asistencia |
| `hora_maxima_reportar_asistencia`        | time        | Hora límite del día final de plazo (default: `11:59 PM`)  |
| `dias_plazo_reserva`                     | smallint    | Días de anticipación para reservar                        |
| `aforo`                                  | integer     | Cupo máximo                                               |
| `habilitar_reserva_invitados`            | boolean     | Si se permiten invitados externos                         |
| `cantidad_maxima_reserva_invitados`      | integer     | Límite de invitados por responsable                       |
| `habilitar_reserva_familiares`           | boolean     | Si se pueden reservar familiares                          |
| `solo_reservados_pueden_asistir`         | boolean     | Si solo asisten los que reservaron                        |
| `habilitar_preregistro_iglesia_infantil` | boolean     | Pre-registro infantil                                     |
| `deleted_at`                             | timestamp   | Soft delete                                               |
| `created_at`, `updated_at`               | timestamps  |                                                           |

**Relaciones del modelo `Reunion`** (via tablas pivot):

- `sedes()` → sedes adicionales donde se permite asistencia (`sedes_id`)
- `rangosEdades()` → `rangos_edades_id`
- `tiposOfrendas()` → `tipo_ofrenda_id`
- `tipoUsuarios()` → `tipo_usuarios.id`
- `clasificacionesAsistentes()` → `clasificacion_asistente_id`
- `reportes()` → `hasMany(ReporteReunion::class)` (usa SoftDeletes)

---

## 3. Controlador `ReunionesController`

`app/Http/Controllers/ReunionesController.php`

| Método                         | Tipo   | Permiso                              | Descripción                                                                                     |
| ------------------------------ | ------ | ------------------------------------ | ----------------------------------------------------------------------------------------------- |
| `nueva()`                      | GET    | `reuniones.subitem_nueva_reunion`    | Muestra form. Carga sedes, rangosEdades, ofrendas, tipoUsuarios, clasificacionesAsistentes      |
| `crear(Request)`               | POST   | —                                    | Crea reunión y sincroniza relaciones con `attach()`. Guarda portada (Base64 → PNG)              |
| `lista(Request, $tipo)`        | GET    | `reuniones.subitem_lista_reuniones`  | Lista con búsqueda textual y filtro de sede. Si no es admin total, filtra por `sedesEncargadas` |
| `editar(Reunion)`              | GET    | `reuniones.opcion_modificar_reunion` | Form de edición con datos precargados de todas las relaciones                                   |
| `actualizar(Request, Reunion)` | PUT    | —                                    | Actualiza y sincroniza relaciones con `sync()`                                                  |
| `eliminar(Reunion)`            | DELETE | —                                    | `forceDelete()` solo si no tiene reportes; si tiene, redirige con error                         |
| `darBaja(Reunion)`             | POST   | —                                    | Soft delete solo si tiene reportes                                                              |

**Portadas**: guardadas en `storage/{ruta_almacenamiento}/img/reuniones/reunion{id}.png` decodificando Base64.

**Validaciones al crear/actualizar:**

- Siempre: `nombre`, `hora`, `díasDePlazo`, `LaSede`
- Si `habilitarReserva`: también `díasPlazoReserva`, `aforo`
- Si además `habilitarReservaInvitados`: también `cantidadInvitados`

---

## 4. Componentes Livewire

### `ReunionesParaBusqueda` — `App\Livewire\Reuniones\ReunionesParaBusqueda`

Buscador reutilizable para seleccionar una reunión en formularios. Recibe props `nameId`, `label`, `placeholder`, `reunionId` (para precargar). Usa ILIKE con `translate()` para búsqueda sin tildes en PostgreSQL. Filtra por sedes encargadas si el rol no tiene `reuniones.lista_reuniones_todas`. Dispatcha `informacionPrecargada` al seleccionar y `anularPrecargado` al quitar la selección.

```blade
<livewire:reuniones.reuniones-para-busqueda
    nameId="reunion_id"
    label="Reunión"
    placeholder="Busque una reunión..."
    :reunionId="$reunionId ?? null"
/>
```

---

### `ModalDarBaja` — `App\Livewire\Reuniones\ModalDarBaja`

Modal de confirmación para dar de baja, dar de alta o eliminar una reunión. Escucha eventos: `confirmacionBajaAlta`, `comprobarSiTieneRegistros`, `confirmarEliminacion`, `confirmarDarDeBajaAlta`. Si la reunión tiene reportes, recomienda dar de baja en lugar de eliminar. Las confirmaciones se muestran con SweetAlert2. Los métodos de acción son `darBajaAlta($id, $tipo)` y `eliminacionForzada($id)`.

---

## 5. Permisos del Módulo

- `reuniones.subitem_nueva_reunion` — Acceder al formulario de nueva reunión
- `reuniones.subitem_lista_reuniones` — Ver la lista de reuniones
- `reuniones.lista_reuniones_todas` — Ver TODAS sin filtro de sede (admin)
- `reuniones.opcion_modificar_reunion` — Editar una reunión

---

## 6. Vistas

**Blade (controlador):**

- `contenido.paginas.reuniones.nueva`
- `contenido.paginas.reuniones.listar`
- `contenido.paginas.reuniones.editar`

**Livewire:**

- `resources/views/livewire/reuniones/reuniones-para-busqueda.blade.php`
- `resources/views/livewire/reuniones/modal-dar-baja.blade.php`
