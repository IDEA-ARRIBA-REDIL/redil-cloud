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