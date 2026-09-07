---
type: capabilities
project_state: pre-ai
generated_by: kaddo-bootstrap
template_version: 1
---

> Idioma del proyecto: **español**. Escribe este conocimiento en español. Mantén en inglés el código, los nombres de archivo, los comandos y las claves de configuración.

# Capacidades existentes del piloto

> Discover the capabilities the system already has, grouped by **functional domain** (not technical folders), evidence-backed. Use the capability-agent to fill this in — run `kaddo add agents`, then feed it the context pack.

## Capability Domains

### Domain: Usuarios y Roles/Permisos

**Purpose:** Administrar identidad, clasificación y autorización.

#### Capability: Rol activo

- Status: implemented
- Capability type: business
- User-facing: yes
- Evidence:
  - `app/Models/User.php::switchActiveRole()` y `hasPermissionTo()`.
  - `tests/Feature/UserGroupPilotTest.php`.
- Current behavior: conserva varios roles, activa exactamente uno y solo ese rol aporta permisos.
- Known constraints: las comprobaciones deben conservar el filtro polimórfico `model_type`.

#### Capability: Promoción de tipo y rol dependiente

- Status: implemented
- Capability type: integration
- User-facing: internal
- Evidence:
  - `app/Models/User.php::promoverTipoUsuario()`.
  - `tests/Feature/UserGroupPilotTest.php`.
- Current behavior: promueve por puntaje, reemplaza el rol dependiente y conserva roles independientes.

#### Capability: Consulta y administración integral del usuario

- Status: implemented
- Capability type: business
- User-facing: yes
- Evidence:
  - `knowledge/tech/discovery/usuarios-vistas-perfil-y-administracion-imagenes-2026-09-06.md`.
  - `app/Http/Controllers/UserController.php` y `resources/views/contenido/paginas/usuario/`.
- Current behavior: permite buscar, filtrar, exportar y administrar personas desde el listado; el perfil consolida información principal, familiar, congregacional, académica e hitos. Las pantallas de edición separan datos principales, información congregacional, geoasignación y relaciones familiares.
- Known constraints: las pestañas integradas no trasladan a Usuarios las reglas internas de Grupos, Escuelas u otros dominios; permanece pendiente la matriz completa de permisos por acción.

#### Capability: Información congregacional y roles independientes

- Status: implemented
- Capability type: integration
- User-facing: yes
- Evidence:
  - `knowledge/tech/discovery/usuarios-vistas-perfil-y-administracion-imagenes-2026-09-06.md`.
  - `app/Http/Controllers/UserController.php::informacionCongregacional()` y `actualizarInformacionCongregacional()`.
  - `app/Models/User.php::promoverTipoUsuario()` y `tests/Feature/UserGroupPilotTest.php`.
- Current behavior: administra tipo de usuario, membresía como integrante de grupos, pasos de crecimiento y roles independientes. Los roles independientes no están vinculados directamente al tipo y se conservan cuando se reemplaza el rol dependiente; continúan sujetos a la selección de un único rol activo.
- Known constraints: la capacidad depende de permisos diferenciados para tipo, grupos y procesos; su matriz completa continúa pendiente.

#### Capability: Creación y edición configurable de usuarios

- Status: implemented
- Capability type: business
- User-facing: yes
- Evidence:
  - `knowledge/tech/discovery/usuarios-crear-editar-formularios-video-2026-09-06.md`.
  - `app/Http/Controllers/UserController.php::nuevo()`, `crear()`, `modificar()` y `editar()`.
- Current behavior: los flujos públicos y administrativos construyen la captura y edición desde un formulario con secciones y campos configurados. Los campos obligatorios se definen en el formulario. En el registro público, el correo se activa mediante una URL firmada que completa `email_verified_at`; antes de ello la cuenta no accede al flujo normal ni aparece en el listado operativo.
- Known constraints: todavía falta confirmar la política funcional de aprobación para cada tipo de formulario.

#### Capability: Administración de formularios de usuario

- Status: implemented
- Capability type: configuration
- User-facing: yes
- Evidence:
  - `app/Http/Controllers/FormularioUsuarioController.php`.
  - `app/Livewire/FormulariosParaUsuarios/GestionarFormularios.php`.
  - `app/Livewire/FormulariosParaUsuarios/GestionarSeccionesYCampos.php`.
- Current behavior: permite crear, editar, buscar, duplicar, ocultar/restaurar y eliminar formularios; administra tipo, roles, valores predeterminados, edad, términos, secciones, campos y su orden. Un formulario usado puede eliminarse porque el usuario no conserva una relación directa con él.
- Known constraints: falta documentar la matriz de permisos para cada operación administrativa y las diferencias funcionales canónicas entre tipos de formulario.

### Domain: Grupos

**Purpose:** Administrar membresía, liderazgo, servicio, jerarquía y registro histórico.

#### Capability: Integración Usuario–Grupo

- Status: implemented
- Capability type: integration
- User-facing: yes
- Evidence:
  - `app/Models/User.php::cambiarGrupo()` y `desvincularDeGrupo()`.
  - `app/Models/Grupo.php::asignarEncargado()`.
  - `tests/Feature/UserGroupPilotTest.php`.
- Current behavior: evita duplicados, registra bitácora, conserva asistencia histórica y dispara hitos una sola vez.

#### Capability: Administración operativa de grupos

- Status: implemented
- Capability type: business
- User-facing: yes
- Evidence:
  - `knowledge/tech/discovery/grupos-recorrido-funcional-video-2026-09-06.md`.
  - `knowledge/tech/discovery/grupos-vistas-operativas-imagenes-2026-09-06.md`.
  - `app/Http/Controllers/GrupoController.php` y `resources/views/contenido/paginas/grupos/`.
- Current behavior: permite listar, filtrar, consultar perfil y crear el grupo mediante datos principales, horario, campos extra y portada. Una vez creado, ofrece áreas separadas para modificar datos, gestionar encargados e integrantes y asignar georreferencia, además de excluir, dar de baja o eliminar según permisos y alcance.
- Known constraints: la visibilidad de una acción no demuestra autorización suficiente; permanece pendiente la matriz de protección ruta por ruta.

#### Capability: Reportes, asistencia y ofrendas de grupo

- Status: implemented
- Capability type: business
- User-facing: yes
- Evidence:
  - `app/Livewire/ReporteGrupos/ModalNuevoReporte.php`.
  - `app/Livewire/ReporteGrupos/Asistencias.php`.
  - `app/Livewire/ReporteGrupos/GestionarAprobacionDesaprobacionDeReportes.php`.
- Current behavior: crea reportes realizados o no realizados, registra asistencia e inasistencia, comparte autoasistencia temporal, captura ofrendas, finaliza y permite revisión, aprobación o corrección administrativa.
- Known constraints: continúan pendientes las pruebas de autorización directa, concurrencia, expiración efectiva del envío público y consistencia transaccional con Finanzas.

#### Capability: Supervisión, territorio y analítica de grupos

- Status: implemented
- Capability type: business
- User-facing: yes
- Evidence:
  - `knowledge/tech/discovery/grupos-recorrido-funcional-video-2026-09-06.md`.
  - `app/Http/Controllers/GrupoController.php::graficoDelMinisterio()`, `mapaDeGrupos()`, `dashboard()` y `comparativo()`.
- Current behavior: permite consultar la jerarquía ministerial, el mapa de grupos, perfiles y estadísticas de cobertura, indicadores, comparaciones de períodos e informes de asistencia y ausencia de reportes.
- Known constraints: “ver montaje” no corresponde todavía a una capacidad identificada; requiere definición o minuto de referencia antes de incorporarse.

## Capability Gaps

- [gap] Hallazgos USR-03 a USR-06 aplazados por decisión del piloto.
  - Domain: Usuarios
  - Related capability: seguridad y creación de cuentas
  - Impact: high
  - Possible roadmap candidate: yes

## Roadmap Candidate Signals

- [candidate] Ampliar el orquestador a otros dominios solamente con autorización expresa.
  - Domain: Orquestador
  - Related capability: enrutamiento de contexto
  - Based on: business goal
