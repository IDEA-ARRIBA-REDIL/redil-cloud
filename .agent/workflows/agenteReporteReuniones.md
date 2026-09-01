---
description: Carga el contexto y memoria del Agente de Reporte de Reuniones (Asistentes, Reservas y Finanzas)
---

# Agente de Reporte de Reuniones (`agenteReporteReuniones`)

## 1. Activación de Persona (desde `/baseDesarrollo`)

- **Rol**: Experto en Laravel 11, Livewire 3, Alpine.js, Bootstrap 5 aplicado al módulo de Reportes de Reunión (asistentes, reservas, ofrendas, QR).
- **Idioma**: Español nativo.
- **Convenciones**: variables/funciones en `camelCase`, comentarios numerados en bloques complejos.
- **UI/UX**: tablas con `dashed-border`, botones `rounded-pill`, SweetAlert2 para eliminaciones, Select2 con `wire:ignore`.

> Para la gestión del CRUD base de `Reunion` (crear, editar, listar, dar de baja), activar `/agenteReuniones`.

---

## 2. Tablas de Base de Datos

### `reporte_reuniones`

| Columna                                  | Tipo        | Descripción                             |
| ---------------------------------------- | ----------- | --------------------------------------- |
| `id`                                     | bigint PK   |                                         |
| `reunion_id`                             | integer     | FK a `reuniones`                        |
| `fecha`                                  | date        | Fecha del reporte                       |
| `hora`                                   | time        | Hora del reporte                        |
| `portada`                                | string(500) | Imagen del reporte                      |
| `predicador`                             | integer     | user_id del predicador interno          |
| `predicador_diezmos`                     | integer     | user_id del servidor de diezmos         |
| `predicador_invitado`                    | string(50)  | Nombre predicador externo               |
| `predicador_diezmos_invitado`            | string(50)  | Nombre servidor diezmos externo         |
| `observaciones`                          | text        | Notas del reporte                       |
| `invitados`                              | integer     | Conteo manual de invitados (default: 0) |
| `cantidad_asistencias`                   | integer     | Total de asistencias registradas        |
| `total_ofrendas`                         | integer     | Total de ofrendas recaudadas            |
| `autor_creacion`                         | integer     | user_id del creador del reporte         |
| `conteo_preliminar`                      | smallint    | Conteo previo (default: 0)              |
| `habilitar_reserva`                      | boolean     | Copia del flag de la reunión            |
| `dias_plazo_reserva`                     | integer     | Plazo para este reporte específico      |
| `aforo`                                  | integer     | Cupo máximo de este reporte             |
| `aforo_ocupado`                          | integer     | Cupos tomados actualmente               |
| `habilitar_reserva_invitados`            | boolean     |                                         |
| `cantidad_maxima_reserva_invitados`      | integer     |                                         |
| `habilitar_reserva_familiares`           | boolean     |                                         |
| `solo_reservados_pueden_asistir`         | boolean     |                                         |
| `url`                                    | text        | Link transmisión en vivo                |
| `iframe`                                 | text        | Código iframe para embeber              |
| `visualizaciones`                        | integer     | Contador de visualizaciones             |
| `habilitar_preregistro_iglesia_infantil` | boolean     |                                         |
| `created_at`, `updated_at`               | timestamps  |                                         |

**Relaciones del modelo `ReporteReunion`:**

- `reunion()` → `BelongsTo(Reunion::class)` — usar `withTrashed()` porque la reunión puede estar dada de baja
- `usuarios()` → `BelongsToMany(User::class, 'asistencia_reuniones')` con pivot: `asistio`, `reservacion`, `invitados`, `observacion`, `autor_creacion_reserva_id`, `autor_creacion_asistencia_id`
- `reservas()` → `HasMany(ReservaReunion::class, 'reporte_reunion_id')`
- `ofrendas()` → `BelongsToMany(Ofrenda::class, 'ofrenda_reuniones', 'reporte_reunion_id', 'ofrenda_id')`
- `clasificacionesAsistentes()` → `BelongsToMany(ClasificacionAsistente::class, 'clasificacion_asistente_reporte_reunion')` con pivot `cantidad`

---

### `clasificacion_asistente_reporte_reunion` (pivot)

| Columna                      | Tipo    | Descripción                                 |
| ---------------------------- | ------- | ------------------------------------------- |
| `reporte_reunion_id`         | integer | FK a `reporte_reuniones`                    |
| `clasificacion_asistente_id` | integer | FK a `clasificaciones_asistentes`           |
| `cantidad`                   | integer | Conteo de asistentes para esa clasificación |

---

### `asistencia_reuniones` (pivot implícito)

Pivot entre `users` y `reporte_reuniones`. Columnas clave: `user_id`, `reporte_reunion_id`, `asistio` (bool), `autor_creacion_asistencia_id`.

---

### `reservas_reuniones` — modelo `ReservaReunion`

- `reporte_reunion_id`, `user_id` (null si es invitado externo)
- `invitado` (bool), `nombre_invitado`, `email_invitado`
- `responsable_id` (usuario que gestionó la reserva)
- `autor_creacion_reserva_id`
- `registrada` (bool → se pone `true` cuando la reserva se convierte en asistencia)

---

## 3. Modelo `ReporteReunion` — Métodos de Negocio

- `puedeAñadirAsistentes()` → verifica permiso `privilegio_anadir_asistente_reporte_reunion_cualquier_fecha` **o** si la fecha actual está entre la fecha del reporte y `fecha + dias_plazo_reporte` antes de `hora_maxima_reportar_asistencia`.
- `puedeAñadirReservas()` → verifica permiso y si está dentro del plazo de reserva (`fecha - dias_plazo_reserva` hasta `fecha`).
- `elUsuarioPuedeReservar(User $user)` → validación completa en 6 pasos: (1) datos básicos del usuario, (2) reunión existe, (3) tipo de usuario permitido, (4) rango de edad, (5) género, (6) sede (vacío = sin restricción).
- `sePuedeReservar()` → si la fecha actual está dentro del plazo de reserva.
- `hayAforoDisponible()` → `aforo > aforo_ocupado`.
- `obtenerCantidadDisponible()` → `max(0, aforo - aforo_ocupado)`.
- `cantidadDisponibleInvitados(User $user)` → cuántos invitados más puede agregar este usuario.
- `tengoReservasEnEsteReporte(User $user)` → si el usuario ya tiene al menos una reserva.

---

## 4. Controlador `ReporteReunionController`

`app/Http/Controllers/ReporteReunionController.php`

| Método                                           | Permiso                                                              | Descripción                                                                      |
| ------------------------------------------------ | -------------------------------------------------------------------- | -------------------------------------------------------------------------------- |
| `lista()`                                        | `reporte_reuniones.subitem_lista_reportes_reunion`                   | Lista con filtros de fecha (defecto: últimos 30 días), reunión y sede            |
| `crear(Request, Reunion)`                        | `reporte_reuniones.nuevo_reporte_reunion`                            | Crea reporte. **Auto-crea clasificaciones de la reunión en cantidad `0`**        |
| `editar(ReporteReunion)`                         | `reporte_reuniones.opcion_modificar_reporte_reunion`                 | Vista de edición                                                                 |
| `actualizar(Request, ReporteReunion)`            | —                                                                    | Actualiza datos. Si `habilitarReserva = false` pone a null aforo, plazos, etc.   |
| `eliminar(ReporteReunion)`                       | —                                                                    | Detach ofrendas y clasificaciones, destroy ofrendas, luego delete reporte        |
| `añadirAsistentes(ReporteReunion)`               | `reporte_reuniones.opcion_anadir_asistentes_reporte_reunion`         | Vista para asistentes                                                            |
| `añadirReservas(ReporteReunion)`                 | `reporte_reuniones.opcion_anadir_asistentes_reservas_reunion`        | Vista para reservas                                                              |
| `añadirServidores(ReporteReunion)`               | `reporte_reuniones.opcion_subitem_anadir_servidores_reporte_reunion` | Vista para servidores                                                            |
| `registrarAsistenciaQr(Request, ReporteReunion)` | —                                                                    | API JSON: valida y registra asistencia por QR                                    |
| `miReserva(Request, ReporteReunion, ?User)`      | —                                                                    | Vista pública para que el usuario haga su reserva                                |
| `hacerMiReserva(Request, ReporteReunion, ?User)` | —                                                                    | POST de reserva con `DB::transaction()` + `lockForUpdate()`. Envía PDF por email |
| `resumenReserva(Request, ReporteReunion, User)`  | —                                                                    | Página de confirmación tras reserva exitosa                                      |
| `compartirLinkReserva(ReporteReunion)`           | —                                                                    | Vista para compartir link de reserva                                             |

**Patrón crítico al crear reporte** — las clasificaciones se deben auto-crear siempre:

```php
$clasificaciones = $reporteReunion->reunion->clasificacionesAsistentes;
foreach ($clasificaciones as $c) {
    $c->reportesReuniones()->attach($reporteReunion->id, ['cantidad' => 0]);
}
```

**Patrón de reserva con aforo protegido:**

```php
DB::transaction(function () use ($reporteReunion, $cuposSolicitados) {
    $reporteReunion = ReporteReunion::where('id', $reporteReunion->id)->lockForUpdate()->first();
    // validar aforo disponible...
    $reporteReunion->aforo_ocupado += $cuposSolicitados;
    $reporteReunion->save();
});
```

---

## 5. Componentes Livewire

### `Asistentes` — `App\Livewire\ReporteReuniones\Asistentes`

El componente más complejo del módulo. Recibe `$reporteReunion` como parámetro obligatorio. Carga personas usando una UNION SQL entre usuarios y (si hay reservas) invitados de `reservas_reuniones`. Si `solo_reservados_pueden_asistir = true` usa INNER JOIN, si no LEFT JOIN. Aplica filtros de tipo_usuario, género, sede y edad del usuario. La paginación es manual (`cantidadPorCarga = 3`, `paginaActual`). Al registrar asistencia con `siAsistio($userId)` verifica el plazo, inserta en `asistencia_reuniones`, actualiza las `clasificacionesAsistentes` con sumatoria automática según tipo/edad/género/paso, actualiza `cantidad_asistencias` y marca la reserva como `registrada = true`. El QR scanner (`qrCodeScanned`) procesa dos tipos de QR: `tipo: 'perfil'` (registra por user_id) y `tipo: 'reserva'` (registra por ReservaReunion).

---

### `Reservas` — `App\Livewire\ReporteReuniones\Reservas`

Similar a `Asistentes` pero orientado a gestión de reservas. Mantiene `$aforo`, `$aforoOcupado`, `$aforoDisponible` actualizados en tiempo real con `actualizarAforo()`. Permite añadir invitados manuales vía modal (`añadirInvitado()` → `crearInvitado()`), creando un `ReservaReunion` sin `user_id` y enviando PDF por email al invitado.

---

### `ResumenFinanciero` — `App\Livewire\ReporteReuniones\ResumenFinanciero`

Gestiona todas las ofrendas de un reporte. Separa **ofrendas genéricas** (sin usuario, editadas via `modalEdicionOfrenda`) de **ofrendas específicas** (vinculadas a un usuario, editadas via `modalOfrendaEspecifica`). Cada ofrenda creada/editada también crea/edita un registro `Ingreso` con `ingreso_por_reunion = true` y `sede_id` tomada de `reporteReunion->reunion->sede_id`.

---

### `UsuariosParaBusqueda` — `App\Livewire\Usuarios\UsuariosParaBusqueda`

Buscador de usuarios reutilizable (usado en Reuniones para seleccionar predicadores y gestionar asistentes). Props clave: `tipoBuscador` (`'unico'`/`'multiple'`), `queUsuariosCargar` (`'todos'`/`'discipulos'`/`'grupo'`), `soloVerificados` (default `true`), `conDadosDeBaja` (`'no'` para excluir soft-deleted). Dispatcha evento `'usuario-seleccionado'` con el `id` del usuario al seleccionar.

---

## 6. Patrones de Búsqueda (PostgreSQL)

```php
// Búsqueda sin tildes con translate()
->whereRaw(
    "translate(nombre,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜ','aeiouAEIOUaeiouAEIOU') ILIKE ?",
    ["%{$buscar}%"]
)
```

---

## 7. Permisos del Módulo

**Grupo `reporte_reuniones`:**

- `reporte_reuniones.subitem_lista_reportes_reunion` — Listar reportes
- `reporte_reuniones.lista_reportes_reunion_todos` — Ver todos los reportes (admin total)
- `reporte_reuniones.nuevo_reporte_reunion` — Crear reporte
- `reporte_reuniones.opcion_modificar_reporte_reunion` — Editar reporte
- `reporte_reuniones.opcion_ver_perfil_reporte_reunion` — Ver perfil del reporte
- `reporte_reuniones.opcion_anadir_asistentes_reporte_reunion` — Gestionar asistentes
- `reporte_reuniones.opcion_subitem_anadir_servidores_reporte_reunion` — Gestionar servidores
- `reporte_reuniones.opcion_anadir_asistentes_reservas_reunion` — Gestionar reservas
- `reporte_reuniones.privilegio_anadir_asistente_reporte_reunion_cualquier_fecha` — Registrar asistencia sin límite de fecha/hora

**Grupo `personas`:**

- `personas.lista_asistentes_solo_ministerio` — Limita la lista al ministerio del usuario
- `personas.ajax_obtiene_asistentes_solo_ministerio` — Filtrado por ministerio

---

## 8. Vistas

**Blade (controlador):**

- `contenido.paginas.reporte-reuniones.listar`
- `contenido.paginas.reporte-reuniones.reportar`
- `contenido.paginas.reporte-reuniones.editar`
- `contenido.paginas.reporte-reuniones.anadir-asistentes`
- `contenido.paginas.reporte-reuniones.anadir-reservas`
- `contenido.paginas.reporte-reuniones.anadir-servidores`
- `contenido.paginas.reporte-reuniones.mi-reserva`
- `contenido.paginas.reporte-reuniones.mensaje-reserva-existosa`
- `contenido.paginas.reporte-reuniones.compartir-link-reserva`

**Livewire:**

- `resources/views/livewire/reporte-reuniones/asistentes.blade.php`
- `resources/views/livewire/reporte-reuniones/reservas.blade.php`
- `resources/views/livewire/reporte-reuniones/resumen-financiero.blade.php`
