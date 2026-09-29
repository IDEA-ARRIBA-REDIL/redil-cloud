# REDIL Cloud - Architecture

## 1. Overview

**REDIL Cloud** es una plataforma SaaS multi-tenant para iglesias y organizaciones religiosas. Combina gestión eclesiástica, sistemas educativos, seguimiento financiero y herramientas de crecimiento espiritual.

### Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12, PHP 8.2 |
| Database | PostgreSQL (central + per-tenant) |
| Multi-Tenancy | `stancl/tenancy` |
| Frontend | Livewire 3, Alpine.js, Bootstrap 5 |
| Real-time | Laravel Reverb (WebSockets), PWA |
| Storage | Cloudflare R2 |
| Cache | Valkey (Redis fork) |
| Email | Mailgun |

---

## 2. Multi-Tenancy Architecture

### Central Database (`redil2024_db`)

Contiene:
- `tenants` — iglesias registradas
- `plans` — planes de suscripción con límites de miembros
- `domains` — mapeo dominio → tenant
- `admin_users` — usuarios globales del SaaS
- `license_keys` — gestión de licencias y caducidad

### Per-Tenant Database

Cada iglesia tiene un schema PostgreSQL aislado con todas sus tablas. Incluye:
- `users`, `grupos`, `reuniones`, `escuelas`, `cursos`, etc.

### Storage

- Archivos por tenant: `storage/tenant{id}/`
- Assets: `tenant_asset()` helper para URLs
- Temas CSS dinámicos generados desde `ThemeSetting`

### Tenant Resolution

1. Dominio o subdominio identifica al tenant
2. `TenantMiddleware` ejecuta ` tenancy->initialize()`
3. Conexión a BD del tenant activa para toda la request

---

## 3. Data Model (Core Entities)

```
Tenant (Iglesia)
├── User
│   ├── Role & Permission (RBAC)
│   ├── TipoUsuario (student/teacher/pastor)
│   ├── PasosCrecimiento (growth steps)
│   └── GrupoMember
├── Escuela
│   └── Periodo
│       └── Nivel
│           └── Materia
│               ├── HorarioMateriaPeriodo (scheduled class)
│               └── ItemCorteMateriaPeriodo (evaluation cutoff)
├── Matricula (enrollment)
├── Curso (LMS)
│   ├── Modulo
│   │   └── Item (polymorphic: lesson/video/evaluation/forum)
│   └── CursoItemUser (progress)
├── Reunione
│   └── ReporteReunione
│       ├── AsistenciaReunione (attendance)
│       └── Ofrenda (offering)
├── Grupa
│   └── ReporteGrupo
├── Actividade (event with fee)
│   └── Compra (registration)
├── IglesiaInfantil
│   └── Registro (check-in/out)
├── Peticione (prayer request)
├── PuntosDePago
│   └── Caja (cash box)
└── Notificacione
```

### Key Relationships

| Relationship | Type |
|--------------|------|
| Tenant → Users | 1:N |
| User → Grupo | N:N |
| Periodo → Niveles → Materias | 1:N:N |
| Materia → Prerrequisitos (self) | N:N |
| User → Matricula → HorarioMateriaPeriodo | N:N:N |
| ReporteReunion → Users | N:N (pivot) |
| Curso → Modulos → Items | 1:N:N |
| User → PasosCrecimiento | N:N (pivot) |

---

## 4. Modules

### A. Gestión Eclesiástica

| Module | Description |
|--------|-------------|
| **Reuniones** | Configuración de servicios (horario, capacidad, reservas) |
| **Reporte Reuniones** | Reportes con asistencia, ofrendas, clasificaciones |
| **Grupos** | Grupos celulares, ministerios y jerarquías |
| **Iglesia Infantil** | Check-in/out con códigos QR para pickup |
| **Consolidación** | Seguimiento de discipulado, counselings, KPIs |
| **Peticiones** | Gestión de peticiones de oración |
| **PWA** | Notificaciones push vía Progressive Web App |

### B. Sistema Educativo (LMS)

| Module | Description |
|--------|-------------|
| **Escuelas** | Escuelas académicas (Bíblica, Liderazgo, etc.) |
| **Niveles** | Jerarquía de grados (1er año, 2do año, etc.) |
| **Materias** | Cursos con prerrequisitos y configuraciones |
| **Periodos** | Períodos académicos con clonación profunda |
| **Matrículas** | Inscripción manual/admin y auto-servicio vía carrito |
| **Calificaciones** | Sistema de notas con items de corte |
| **Homologaciones** | Equivalencias de cursos |
| **Historial Calificaciones** | Historia académica y generación de PDF |
| **Campus Estudiantil** | Portal estudiantil que consume contenido LMS |
| **Cursos** | LMS completo: módulos, lecciones, evaluaciones, foros |

### C. Pagos y Finanzas

| Module | Description |
|--------|-------------|
| **Tipos de Pago** | Métodos de pago configurables por tenant |
| **Puntos de Pago** | Puntos físicos, cajas, tesorería |
| **Actividades** | Eventos con tarifas y sistema de carrito |
| **Carrito/Checkout** | Compra de cursos y actividades |

### D. Crecimiento Espiritual

| Module | Description |
|--------|-------------|
| **Pasos de Crecimiento** | Pasos de crecimiento con prerrequisitos y milestones |
| **Rueda de la Vida** | Auto-evaluación (wizard, metas, hábitos) |
| **Tiempo con Dios** | Devocionales diarios (lectura, música, racha, Bible API) |
| **Versículo Diario** | Widget de versículo bíblico diario |
| **Tareas de Consolidación** | Tareas como requisitos de graduación |

### E. Usuarios y Administración

| Module | Description |
|--------|-------------|
| **Usuarios** | Gestión con RBAC (roles, permisos) |
| **Tipos de Usuario** | Categorías: estudiante, maestro, pastor |
| **Tipos de Grupo** | Configuración de tipos de grupo |
| **Theme** | Personalización visual multi-tenant |
| **Posts** | Feed de contenido con restricciones de visibilidad |

### F. Administración Global SaaS (Landlord)

| Module | Description |
|--------|-------------|
| **Tenant Management** | Registro, activación, suspensión de iglesias |
| **Plans & Subscriptions** | Planes con límites de miembros y features |
| **License Management** | Seguimiento de caducidad y gracia |
| **Central Notifications** | Notificaciones para operadores del SaaS |

---

## 5. Technical Highlights

### Academic Flow

- **Restricciones de inscripción**: Prerrequisitos, pasos de crecimiento, tareas de consolidación
- **Cambios de rol automáticos**: `tipo_usuario_inicial_id` en inscripción, `tipo_usuario_objetivo_id` al completar
- **Clonación profunda**: Periodo → niveles → materias → horarios → items de corte

### LMS Features

- Contenido polimórfico (`curso_items` con `itemable_type`)
- Seguimiento de progreso (`CursoItemUser`, `CursoUser.porcentaje_progreso`)
- Desbloqueo secuencial (item N+1 bloqueado hasta completar N)
- Detección de completado de video (YouTube/Vimeo API polling al 95%)
- Aleatorización de preguntas en evaluaciones
- Foro comunitario por curso

### Children Check-in

- Gestión de salones/estaciones
- Generación de QR para pickup
- Notas médicas por sesión
- Exportación a Excel con todo el tracking

### Theme System

- `ThemeSetting` almacena colores hex por categoría
- `ThemeService` genera CSS dinámico desde la base de datos
- La edición de colores requiere una prestación de logo o marca blanca activa, además del permiso tenant correspondiente.
- La presentación del login tiene valores predeterminados en la base central y recursos opcionales en cada tenant. `LoginBrandingResolver` aplica la licencia y resuelve el fallback sin duplicar los archivos globales.
- CSS almacenado en `storage/{tenant_id}/theme/_custom-variables.css`

### Notification System

- `NotificacionService` dispatch notificaciones
- Alcances: `global`, `individual`, `ministerio_directo`, `escala_ministerial`
- Filtro multi-sede y por rol
- Cola via Laravel `ShouldQueue`

---

## 6. Integrations

| Service | Purpose |
|---------|---------|
| **Bible API** | `bible-api.deno.dev` para devocionales |
| **Mailgun** | Emails transaccionales vía cola |
| **WhatsApp** | Notificaciones fallback para PWA |
| **YouTube/Vimeo** | Videos en LMS |
| **QR Codes** | Check-in y asistencia |
| **Laravel Cloud** | Deployment (Neon PostgreSQL, Valkey, Cloudflare R2) |

---

## 7. Project Structure

```
app/
├── Console/Kernel.php          # Commands, schedule
├── Exceptions/Handler.php       # Exception handling
├── Http/
│   ├── Controllers/             # API & Web controllers
│   ├── Middleware/              # Auth, tenant, locale
│   └── Kernel.php              # Middleware stack
├── Models/                      # Eloquent models
├── Services/                    # Business logic (ThemeService, NotificacionService, etc.)
├── Mail/                       # Email classes
├── Livewire/                    # Livewire components
├── Notifications/               # Notification classes
└── Providers/                  # Service providers

database/
├── migrations/                  # Central migrations
│   └── tenant/                 # Tenant migrations (run per tenant)
├── seeders/                    # Seeders
└── factories/                  # Model factories

resources/
├── views/                      # Blade views
│   ├── contenido/
│   │   ├── authentications/
│   │   ├── paginas/
│   │   │   ├── actividades/
│   │   │   ├── carrito/
│   │   │   ├── escuelas/
│   │   │   ├── grupos/
│   │   │   ├── usuarios/
│   │   │   └── ...
│   │   └── pages/
│   ├── layouts/
│   │   ├── sections/
│   │   └── commonMaster.blade.php
│   └── livewire/               # Livewire views
└── css/, js/

routes/
├── api.php                     # API routes
├── web.php                     # Web routes
└── app.php                     # Main app routes

storage/
├── tenant{id}/                 # Per-tenant files
│   ├── theme/                 # Generated CSS
│   └── uploads/              # User uploads
└── app/                       # Logs, cache

config/
├── tenancy.php                 # Multi-tenancy config
├── fortify.php                 # Auth config
└── filesystems.php            # Storage config
```

---

## 8. Security Model

### Autenticación y Autorización

- **Laravel Fortify** para authentication
- **Spatie Permission** para RBAC (roles, permisos)
- Políticas de modelo para autorización granular
- Middleware de suspensión de tenant

### Protección de Datos

- Mass assignment protection con `$fillable`/`$guarded`
- Sanitización de inputs en todos los formularios
- CSRF protection en todas las rutas web
- Rate limiting configurado

### Almacenamiento Seguro

- Uso de `tenant_asset()` para assets de tenants
- Aislamiento de storage por tenant
- No exposición de paths de filesystem

---

## 9. Workflow Agents (Documentation)

Los agentes de documentación están en `.agent/workflows/`:

| Agent | Purpose |
|-------|---------|
| `agenteActividades.md` | Gestión de eventos y actividades |
| `agenteAdminGlobal.md` | Administración del SaaS |
| `agenteCampusEstudiante.md` | Portal estudiantil LMS |
| `agenteConsolidacion.md` | Sistema de discipulado |
| `agenteContenidoDelCurso.md` | LMS contenido de cursos |
| `agenteCursoPrincipal.md` | Gestión de cursos |
| `agenteEscuelas.md` | Sistema académico |
| `agenteFormularioActividad.md` | Formularios de registro |
| `agenteGestionTiposGrupo.md` | Configuración de grupos |
| `agenteGrupos.md` | Grupos celulares |
| `agenteHistorialCalificaciones.md` | Reportes académicos |
| `agenteHomologaciones.md` | Equivalencias de cursos |
| `agenteIglesiaInfantil.md` | check-in infantil |
| `agenteLogin.md` | Autenticación |
| `agenteMaterias.md` | Gestión de materias |
| `agenteMatriculas.md` | Sistema de matrícula |
| `agenteMultiTenancy.md` | Arquitectura multi-tenant |
| `agenteNiveles.md` | Niveles académicos |
| `agenteNovedadesActividad.md` | Gestión de novedades e incidencias de inscripción |
| `agenteNotificaciones.md` | Sistema de notificaciones |
| `agentePeriodos.md` | Períodos académicos |
| `agentePeticiones.md` | Peticiones de oración |
| `agentePosts.md` | Feed de contenido |
| `agentePuntosDePago.md` | Sistema de cajas |
| `agentePWA.md` | Progressive Web App |
| `agenteReporteReuniones.md` | Reportes de servicios |
| `agenteReuniones.md` | Reuniones/cultos |
| `agenteRuedaVida.md` | Auto-evaluación |
| `agenteSistemaCalificaciones.md` | Calificaciones |
| `agenteTiempoConDios.md` | Devocionales |
| `agenteTipoPago.md` | Métodos de pago |
| `agenteUsuarios.md` | Gestión de usuarios |
| `agenteVersiculoDiario.md` | Versículos diarios |

---

## 10. Deployment

- **Laravel Cloud** como target de deployment
- **Neon** para PostgreSQL serverless
- **Valkey** para cache (Redis fork)
- **Cloudflare R2** para archivos estáticos

### 10.1. Sincronización asistida SFTP desde el equipo local

**Instrucción del responsable (2026-09-28):** al terminar una tarea de implementación autorizada, el agente debe subir sus archivos elegibles al cPanel de desarrollo mediante SFTP directo, sin exigir abrirlos y guardarlos uno por uno en Antigravity. No es necesario controlar el plugin del IDE. Esta alternativa sustituye el guardado manual descrito en la sección 7.2 de `.agent/skills/base-desarrollo/SKILL.md` únicamente cuando se cumplen las condiciones siguientes; no habilita comandos remotos ni modifica las demás restricciones del proyecto.

#### Condiciones de activación

- Ejecutarse en el Mac local del responsable, con usuario de sistema `macosxdarwin` y raíz real del proyecto `/Users/macosxdarwin/Desktop/REDIL-CLOUD`. Comprobar el entorno de ejecución real, no inferirlo de una ruta mencionada en el chat. Una copia en otro equipo, contenedor, worktree, CI o sesión alojada en un servidor no queda autorizada por esta regla. Ante dudas, preguntar.
- Leer las credenciales únicamente desde `.vscode/sftp.json` local, sin imprimirlas, copiarlas a documentación ni incluirlas en el lote. El destino autorizado es el cPanel de desarrollo de la cuenta `redil2024`, raíz `/home/redil2024/public_html`, no Laravel Cloud ni infraestructura de clientes. Si el perfil, host, cuenta o destino cambia respecto del destino previamente verificado, solicitar confirmación antes de escribir.
- Validar la identidad SSH contra una clave de host conocida. No aceptar automáticamente claves nuevas o modificadas. Respetar las aprobaciones de red y permisos que solicite la herramienta; esta instrucción no las omite.
- La instrucción aplica durante tareas de implementación, no como vigilancia permanente de archivos ni en consultas, diagnósticos o tareas exclusivamente documentales. Si el usuario pide trabajar solo en local o no desplegar, no subir nada.
- **Alcance ampliado por el responsable (2026-09-28):** subir mediante SFTP los archivos elegibles creados o modificados por el agente en cada tarea de implementación autorizada, de cualquier módulo o área del proyecto. No se limita a administración central. Incluye controladores, servicios, componentes, vistas, rutas, configuración y otros archivos necesarios de la tarea, respetando las exclusiones y validaciones siguientes. Esta autorización no incluye cambios previos ajenos a la tarea ni habilita una sincronización indiscriminada de todo el repositorio.

#### Procedimiento por tarea

1. Antes de editar, identificar los archivos de la tarea y conservar su estado local y remoto de referencia. No tomar todos los cambios existentes del repositorio como parte de la entrega.
2. Revisar y validar los cambios; anunciar el lote que se va a subir. No publicar código con errores conocidos ni un lote incompleto. Si falta una referencia fiable para distinguir cambios previos o hay diferencias remotas no explicadas, detener la subida de ese lote y consultar; no sobrescribir el trabajo del compañero.
3. Excluir secretos (`.env` y variantes, claves, credenciales), `.vscode/`, `.git/`, dependencias, logs, cachés, archivos de usuarios, documentación interna y pruebas. No sincronizar directorios completos ni usar opciones de borrado espejo. No subir `UserSeeder` ni `TenantDatabaseSeeder` bajo esta autorización. Mantener `routes/web.php` como cambio manual hasta nueva autorización explícita, entregando al usuario las rutas necesarias.
4. Respaldar los archivos remotos que se reemplazarán en una carpeta privada fuera de `public_html`, con un manifiesto de rutas y hashes sin secretos. Revalidar el estado remoto justo antes de reemplazar. Subir a archivos temporales y reemplazar de forma atómica por archivo cuando el servidor lo permita; verificar el contenido final por checksum y conservar permisos adecuados. Esto no vuelve atómico un despliegue de varios archivos: si requiere mantenimiento o una transición coordinada, detenerse y acordar ese procedimiento.
5. No ejecutar automáticamente Artisan, migraciones, seeders, Composer, compilaciones, cambios de `.env`, cron, reinicios de workers ni comandos Git. Si el lote necesita estas acciones o cambios manuales de rutas para funcionar, explicar comandos, orden y riesgos y coordinar la activación antes de publicarlo. Una subida SFTP no acredita que migraciones, cachés, assets o procesos estén actualizados.
6. Informar qué archivos se subieron y verificaron, dónde quedó el respaldo y qué acciones manuales faltan. Registrar la entrega en el WI y la documentación del módulo cuando corresponda. Ante un fallo parcial, indicar exactamente lo aplicado y lo pendiente; no declarar éxito ni restaurar por encima de cambios remotos posteriores.

**Límite:** esta regla documenta cómo debe actuar el agente cuando tenga acceso a las herramientas y lea estas instrucciones; no instala un sincronizador, no configura el plugin y no garantiza que otros editores o agentes la carguen automáticamente. El flujo de producción en Laravel Cloud sigue siendo independiente y se rige por `knowledge/delivery/DESPLIEGUEREDILCLOUD.md`.

---

## 11. Integridad Absoluta del Código (Directrices Anti-Truncado para Agentes de IA)

Para garantizar la estabilidad del software y evitar la pérdida accidental de lógica de negocio en el código fuente, cualquier agente de IA o desarrollador DEBE acatar estrictamente:

### 11.1. Prohibición Absoluta de Marcadores de Omisión / Elipsis
- **NUNCA** utilizar marcadores de posición, pseudocódigo o comentarios abstractos de omisión como:
  - `/* ... */`
  - `// ... resto del código ...`
  - `// ... sin cambios ...`
  - `/* lógica previa */`
  - `<!-- ... -->`
- Todo bloque de código que se escriba o edite DEBE ser **100% completo, ejecutable y funcional**. Nunca asumir que el código existente se preservará si se reemplaza por un comentario de resumen.

### 11.2. Edición Quirúrgica y No Destructiva
- Preferir siempre modificaciones atómicas dirigidas sobre las líneas exactas a cambiar (`replace_file_content`), evitando sobreescribir bloques o métodos enteros innecesariamente.
- Si se reescribe un bloque contenedor, clase o vista, se DEBEN transferir y respetar íntegramente todos los métodos, callbacks, listeners de eventos (`@this.on`, Alpine, SweetAlert2), directivas y validaciones preexistentes.

### 11.3. Verificación de Completitud
- Antes de finalizar cualquier cambio, el agente debe auto-revisar que ninguna función o listener haya quedado vacío o con lógica simulada.
