---
description: Carga el contexto y memoria del Agente de Novedades e Incidencias de Actividades
---

1. Read the documentation file `_docs_agente/modulos/actividades.md`.
2. Read the Kaddo domain file `knowledge/tech/domains/actividades/current-state.md`.
3. Read `app/Models/NovedadActividad.php`, `app/Models/TipoNovedad.php` and `app/Livewire/Actividades/GestionNovedades.php`.
4. Adopt the persona: "Experto en Gestión de Novedades e Incidencias de Inscripción de Actividades (Soporte Transaccional y Académico)".
5. Confirm to the user: "🎫 **Agente de Novedades de Actividades Activado**. Tengo cargado el contexto del flujo de reporte de incidencias, validación de prerrequisitos, gestión administrativa de estados, envío de respuestas por correo y exportación."

---

## 1. Propósito del Módulo

El subsistema de **Novedades de Actividades** captura, centraliza y gestiona las incidencias reportadas por los feligreses al intentar inscribirse o matricularse en una actividad (normal o de escuelas) cuando son rechazados por:
- Falta de prerrequisitos de materias no homologadas o pendientes tras migraciones de base de datos.
- Pasos de crecimiento no concluidos (Bautismo, Caminos a la Libertad, etc.).
- Errores de concordancia en número de cédula, identificación o datos de usuario.
- Restricciones de cupo, sede o errores en pasarela de pago / carrito.

---

## 2. Arquitectura de Datos y Permisos

### 2.1 Tablas Tenant
- **`tipos_novedad`**:
  - `id`, `nombre`, `descripcion`, `activo`, `timestamps`.
  - Opciones precargadas: prerrequisito de materia, paso de crecimiento, datos de cédula, cupos/sede, pago/inscripción, otro.
- **`novedades_actividad`**:
  - `id`, `actividad_id`, `user_id` (nullable), `tipo_novedad_id`.
  - `materia_id` (nullable, FK a `materias`), `materia_nombre` (nullable).
  - `nombre`, `identificacion`, `telefono`, `email`, `asunto`, `descripcion` (máx 500 caracteres).
  - `estado`: `no_revisado` (default), `iniciado`, `finalizado`.
  - `respuesta` (text, nullable), `respondido_por_id` (FK a `users`, nullable), `fecha_respuesta` (timestamp, nullable).

### 2.2 Permisos de Spatie
- `actividades.ver_novedades`: Permite el acceso al listado y filtros de novedades en el menú `Actividades > Novedades`.
- `actividades.gestionar_novedades`: Permite responder novedades (enviando email), cambiar estados y administrar el catálogo de tipos de novedad.

---

## 3. Flujo Transaccional y Componentes

### 3.1 Catálogo y Detección de Bloqueo
1. **`proximas-actividades.blade.php`**: Las actividades con requisitos pendientes permanecen visibles para el usuario autenticado con el badge `Requisitos pendientes`.
2. **`perfil-actividad.blade.php`**: Si `$hayDisponibles` es falso:
   - Se muestra un bloque consolidado con la lista de viñetas detallando cada motivo de rechazo (materias, pasos de crecimiento, tareas o requisitos demográficos).
   - Se renderiza el botón **"Registrar novedad"** con `target="_blank"` hacia la ruta `actividades.novedades.crear`.
   - **Regla estricta**: Si el usuario cumple requisitos o ya está inscrito/pagado, el botón **NUNCA** se muestra.

### 3.2 Formulario Público de Novedad
- **Ruta**: `GET /actividades/{actividad}/novedad` (`actividades.novedades.crear`)
- **Controlador**: `App\Http\Controllers\NovedadActividadController`
- **Vista**: `resources/views/contenido/paginas/actividades/novedades/registrar-novedad.blade.php`
- **Comportamiento**:
  - Precarga automática de datos de sesión si el usuario está autenticado.
  - Campo condicional **"Materia o Escuela a la que deseabas matricularte"** solo si `actividad->tipo->tipo_escuelas`.
  - Contador reactivo `0 / 500 caracteres` en el campo de descripción.
  - Alerta SweetAlert2 de confirmación al guardar con estado `no_revisado`.

### 3.3 Panel de Gestión Administrativa
- **Ruta**: `GET /actividades/novedades/gestion` (`actividades.novedades.gestion`)
- **Componente**: `App\Livewire\Actividades\GestionNovedades`
- **Vista**: `resources/views/livewire/actividades/gestion-novedades.blade.php`
- **Capacidades**:
  - Métricas rápidas y filtrado por pestañas de estado (`No revisado`, `Iniciado`, `Finalizado`).
  - Filtros avanzados: por actividad, tipo de novedad, fechas y buscador por texto.
  - **Contestar Novedad**:
    - Modal con historial y textarea de respuesta.
    - Actualiza el estado (`finalizado` o `iniciado`), registra asesor y fecha.
    - Envía correo electrónico de inmediato vía `Mail::to($novedad->email)->send(new DefaultMail($mailData))` usando el template oficial `resources/views/emails/default-mail.blade.php`.
  - **Gestión de Tipos**: Modal para agregar o activar/desactivar opciones de `TipoNovedad`.
  - **Exportar a Excel**: Descarga `.xlsx` mediante `App\Exports\NovedadesExport`.

---

## 4. Comandos de Mantenimiento para Servidor VPS

```bash
# Migraciones tenant
php artisan tenants:run migrate

# Sembrado de permisos
php artisan tenants:run db:seed --class=Database\\Seeders\\NovedadesPermisosSeeder

# Limpieza de caches
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```
