---
id: MOD-USUARIOS
title: Módulo de Usuarios, Roles y Permisos
artifact_type: domain-current-state
status: draft
language: es
last_reviewed: 2026-09-06
technical_review: partial
business_review: partial
owners:
  - por-definir
code:
  - app/Models/User.php
  - app/Models/Role.php
  - app/Models/TipoUsuario.php
  - app/Http/Controllers/UserController.php
  - app/Http/Controllers/RolController.php
  - app/Livewire/Usuarios/**
  - database/seeders/UserSeeder.php
  - database/seeders/RoleSeeder.php
  - database/seeders/TipoUsuarioSeeder.php
  - database/seeders/PermisoSeeder.php
  - tests/Feature/UserGroupPilotTest.php
aliases:
  - usuarios
  - usuario
  - personas
  - asistentes
  - roles
  - permisos
  - tipos de usuario
  - relaciones familiares
related_domains:
  - grupos
  - reuniones
  - consolidacion
  - peticiones
  - escuelas
  - consejeria
---

# Módulo de Usuarios, Roles y Permisos

> Piloto de documentación para construir una fuente de contexto por módulos. Este archivo no ejecuta código, no configura Kaddo y no reemplaza pruebas ni reglas de autorización. Su contenido debe revisarse antes de declararlo como fuente canónica.

## 1. Cómo interpretar este documento

Cada afirmación relevante debe conservar una clasificación de evidencia:

- **[CÓDIGO]**: verificada directamente en el repositorio el 2 de septiembre de 2026.
- **[DOC]**: tomada de documentación existente; requiere contraste con código o validación humana.
- **[NEGOCIO VALIDADO]**: confirmada por el responsable del producto a partir del recorrido funcional.
- **[IMAGEN OBSERVADA]**: comprobada en una captura entregada por el responsable del producto; sus valores visibles son ilustrativos.
- **[NEGOCIO POR VALIDAR]**: extraída de una reunión o inferida de su resumen; todavía requiere confirmación humana.
- **[PENDIENTE]**: pregunta abierta o comportamiento todavía no comprobado.
- **[RIESGO]**: diferencia, acoplamiento o ausencia que conviene revisar; no significa por sí sola que exista un defecto.

Orden de precedencia provisional: código y pruebas actuales > decisión de negocio validada > documentación anterior > inferencia de IA.

## 2. Propósito y límites

### Propósito provisional

**[DOC]** El módulo administra la identidad de las personas, su información personal y congregacional, sus relaciones familiares, su clasificación mediante tipos de usuario y su capacidad de operar mediante roles y permisos.

**[NEGOCIO VALIDADO]** En REDIL Cloud, usuario, persona y asistente nombran funcionalmente a la misma entidad: alguien registrado en REDIL y persistido en `users`. La elección de la palabra depende del contexto técnico, histórico o de comunicación con la iglesia.

**[NEGOCIO VALIDADO]** REDIL 1.0, construido sobre Laravel 4.2, separaba la información de la persona en `asistentes` y las credenciales en `users`. REDIL Cloud unificó ambos conceptos en `users`; por eso el código y la conversación del equipo todavía conservan nomenclatura histórica.

### Incluido en este contexto

- Gestión y consulta de usuarios.
- Perfil personal, familiar y congregacional.
- Relaciones familiares.
- Tipos de usuario y promociones entre tipos.
- Roles, permisos y selección de rol activo.
- Formularios configurables usados para crear o modificar usuarios.
- Entidades relacionadas y sedes cuando clasifican o contextualizan al usuario.

### Fuera del alcance primario

Grupos, reuniones, consolidación, escuelas y consejería consumen la identidad del usuario, pero sus reglas propias deben vivir en documentos de dominio separados. Este archivo solo conserva los puntos de integración necesarios para entender Usuarios.

## 3. Glosario operativo

| Concepto | Definición provisional | Evidencia |
|---|---|---|
| Usuario / persona / asistente | Nombres equivalentes para alguien registrado en REDIL Cloud. `User` concentra identidad, credenciales, datos personales y relaciones con otros dominios. | **[CÓDIGO]** `app/Models/User.php`; **[NEGOCIO VALIDADO]** recorrido funcional |
| `TipoUsuario` | Clasificación de negocio de la persona. Incluye visibilidad, puntaje, seguimiento, consolidación y un rol dependiente opcional. | **[CÓDIGO]** `app/Models/TipoUsuario.php` |
| `Role` | Agrupación técnica de capacidades basada en Spatie Permission y ampliada con relaciones propias de REDIL. | **[CÓDIGO]** `app/Models/Role.php` |
| Permiso | Capacidad granular asignada a roles mediante las tablas de Spatie Permission. | **[CÓDIGO]** migración `2023_11_17_223636_create_permission_tables.php` |
| Rol activo | Un usuario puede tener varios roles asignados, pero solo uno activo. Ese rol debe aportar las restricciones y permisos de la sesión. | **[CÓDIGO]** `User::switchActiveRole()`; **[NEGOCIO VALIDADO]** recorrido funcional |
| Entidad relacionada | Base técnica experimental para futuras líneas de negocio, como usar Escuelas en colegios cristianos o soportar radios y otras organizaciones. No se considera una funcionalidad de negocio lanzada. | **[CÓDIGO]** modelo y migraciones; **[NEGOCIO VALIDADO]** estado futuro |
| Formulario de usuario | Configuración de captura o edición de usuarios, con secciones, campos, tipos, edades y roles relacionados. | **[CÓDIGO]** modelos, migraciones y `FormularioUsuarioController` |

### Distinción que el agente debe preservar

`TipoUsuario` y `Role` no son sinónimos. El primero expresa clasificación y reglas del negocio; el segundo expresa capacidades de acceso. Un `TipoUsuario` puede señalar un rol dependiente, pero eso no convierte ambos conceptos en uno solo.

## 4. Flujos técnicos verificados

### 4.1 Consultar y administrar usuarios

**[CÓDIGO]** `UserController` contiene operaciones de listado, creación, modificación, perfil, perfil familiar, perfil congregacional, información congregacional, edición automática, exportación y cambio de rol. Las rutas principales están en `routes/app.php` y requieren autenticación y correo verificado por el grupo exterior.

**[CÓDIGO]** Parte de las páginas sensibles de un usuario utilizan el middleware `verificarUsuario`, incluyendo perfil, familia, congregación, modificación, historial de escuelas y geoasignación.

### 4.2 Seleccionar el rol activo

1. **[CÓDIGO]** La ruta `user.roles.switch` recibe el rol solicitado.
2. **[CÓDIGO]** `User::switchActiveRole()` comprueba que el usuario ya posea ese rol.
3. **[CÓDIGO]** Dentro de una transacción desactiva los roles del usuario y activa el seleccionado.
4. **[CÓDIGO]** Se limpia la caché de permisos de Spatie.

**[NEGOCIO VALIDADO]** Un usuario puede tener varios roles asignados —por ejemplo líder, maestro y tesorero—, pero únicamente uno debe estar activo y cargar las restricciones y permisos aplicables.

**[CÓDIGO Y PRUEBA]** `UserGroupPilotTest` confirma que exactamente un rol queda activo, que el cambio filtra por `model_type` y que `hasPermissionTo()`/`can()` no aceptan permisos provenientes de roles inactivos ni permisos directos del usuario.

### 4.3 Gestionar tipos de usuario

**[CÓDIGO]** `UsuarioConfiguracionController` expone listado, creación, edición, actualización y eliminación. `TipoUsuario` incluye reglas de visibilidad, puntaje, seguimiento de grupos y reuniones, consolidación, membresía oficial e inactividad.

**[CÓDIGO]** `User::promoverTipoUsuario()` puede cambiar el tipo según puntaje —o forzarlo— y, si existe `id_rol_dependiente`, sustituye roles dependientes, activa el nuevo y limpia la caché de permisos.

**[NEGOCIO VALIDADO + CÓDIGO Y PRUEBA]** Los roles independientes no están relacionados directamente con un tipo de usuario. Se asignan por separado con `dependiente = false` y se conservan cuando una promoción sustituye el rol dependiente vinculado al tipo. “Independiente” describe su relación con `TipoUsuario`; no los exime de la regla de rol activo.

**[PENDIENTE]** El equipo debe definir cuándo una modificación es “promoción”, quién puede forzarla y cuáles son las excepciones de negocio.

### 4.4 Configurar formularios de usuario

**[CÓDIGO]** `FormularioUsuarioController` gestiona formularios, campos, secciones y edición. Livewire participa en la administración de formularios y sus campos mediante componentes en `app/Livewire/FormulariosParaUsuarios/`.

**[CÓDIGO]** Los formularios se relacionan con roles y pueden definir límites de edad, tipo de usuario predeterminado y tipo de formulario.

**[VIDEO OBSERVADO + CÓDIGO]** El listado permite buscar formularios, incluir formularios ocultos y ejecutar acciones de edición, duplicación, ocultamiento/restauración y eliminación. Las tarjetas presentan el tipo, la validación de edad y los roles asociados.

**[VIDEO OBSERVADO + CÓDIGO]** La configuración principal incluye nombre, título, etiqueta, descripción, tipo de formulario, roles habilitados, tipo de usuario y sede predeterminados, límites de edad, mensaje de error y términos y condiciones. Una segunda etapa administra las secciones y los campos, incluyendo orden, obligatoriedad, ancho visual e información de apoyo.

### 4.4.1 Crear usuarios mediante formularios

**[VIDEO OBSERVADO + CÓDIGO]** La creación pública y la administrativa comparten el motor de `FormularioUsuario`, pero usan rutas y reglas de autorización distintas. El tipo del formulario determina su layout y vista; las secciones ordenadas determinan los pasos o bloques visibles.

**[VIDEO OBSERVADO]** El registro público mostrado recorre datos personales, contacto, información congregacional, contraseña y un resumen previo. La creación administrativa muestra un formulario interno compuesto por secciones configurables.

**[CÓDIGO]** `UserController::crear()` arma las validaciones a partir de los campos que pertenecen al formulario y de su marca de obligatoriedad. Cuando identificación o correo están presentes se comprueba unicidad; la validación de edad añade límites derivados de la configuración del formulario.

**[CÓDIGO]** Un formulario puede asignar tipo de usuario y sede predeterminados. El estado de aprobación resultante depende del tipo de formulario, de su configuración y, en el flujo administrativo, del permiso del rol activo.

**[NEGOCIO VALIDADO + CÓDIGO]** Los campos obligatorios se determinan en la asociación sección–campo del formulario. Al crear o editar, el controlador convierte esa configuración en validaciones requeridas o anulables y añade reglas específicas de formato, unicidad y edad.

**[NEGOCIO VALIDADO + CÓDIGO]** Un formulario ya utilizado puede eliminarse definitivamente porque `users` no almacena el formulario que originó el registro. Los valores configurables del usuario se relacionan con campos; la eliminación del formulario desvincula sus secciones y campos sin eliminar al usuario.

### 4.4.2 Editar usuarios mediante formularios

**[VIDEO E IMAGEN OBSERVADOS + CÓDIGO]** La edición reutiliza un formulario configurado y separa datos principales, información congregacional, geoasignación y relaciones familiares. La información congregacional integra tipo de usuario, membresía de grupos, procesos de crecimiento y roles independientes.

**[NEGOCIO VALIDADO]** Desde Información congregacional se puede asignar la persona como integrante de un grupo, administrar sus pasos de crecimiento y elegir su tipo de usuario. El panel “Asignar roles independientes” administra únicamente roles que no dependen directamente del tipo.

**[CÓDIGO]** `UserController::modificar()` aplica `modificarUsuarioPolitica`, carga el formulario y sus campos extra, y presenta los catálogos necesarios. La actualización principal, congregacional, geográfica, familiar y la autoedición por sección tienen rutas diferenciadas.

### 4.4.3 Consultar listado y perfil

**[IMAGEN OBSERVADA + CÓDIGO]** El listado ofrece indicadores, búsqueda, filtros, exportación, tarjetas y paginación. El menú contextual permite abrir perfil y áreas de administración, gestionar contraseñas y QR, dar de baja o eliminar, siempre según permisos y estado.

**[IMAGEN OBSERVADA]** El perfil organiza la consulta en Principal, Familia, Congregación, Escuelas e Hitos. Principal reúne QR e información personal configurable; Familia muestra relaciones y acudiente; Congregación consolida grupos, encargados, asistencia y crecimiento; Escuelas muestra el avance académico como integración con ese dominio.

**[LÍMITE]** Las pestañas agregan información alrededor de la persona, pero las reglas internas de Grupos, Escuelas y demás módulos continúan en sus documentos propios.

### 4.5 Gestionar relaciones familiares

**[CÓDIGO]** La relación usuario–pariente es autorreferenciada y usa `parientes_usuarios`, con tipo de parentesco y señal de responsable. `ParienteUsuarioController` gestiona consulta, creación, eliminación, informes y exportación.

**[NEGOCIO VALIDADO]** Los usuarios nuevos menores de edad deben ser registrados por su padre, madre o acudiente. Por razones legales no se debe almacenar información que permita contactar directamente al menor. El sistema puede asignar un correo técnico predeterminado y una contraseña inicial; las credenciales se envían al correo del responsable. El acceso autónomo, por ejemplo para mayores de 14 años, debe ajustarse a la legislación aplicable en cada país.

**[NEGOCIO VALIDADO]** En migraciones desde REDIL 1.0 se conserva la información histórica y se completa manualmente lo necesario para habilitar la cuenta. Estas excepciones pertenecen a la transición de datos y no modifican las reglas de registro para iglesias nuevas.

**[NEGOCIO POR VALIDAR]** Falta precisar cómo se mantiene la relación inversa, qué parentescos son válidos y cómo se elige al responsable cuando existen varios acudientes.

### 4.6 Identidad, correo y acceso

**[NEGOCIO VALIDADO]** En REDIL Cloud todo usuario ordinario debe tener un correo real y único, una contraseña y las verificaciones requeridas para ingresar. Ya no se mantienen “personas externas” como usuarios incompletos dentro de la plataforma.

**[NEGOCIO VALIDADO + CÓDIGO]** Después del registro, `MiVerificacionDeCorreo` envía un mensaje con un botón que contiene una URL temporal firmada. `CustomVerifyEmailController` valida la firma, el usuario y el hash, marca el correo mediante `markEmailAsVerified()`, inicia la sesión y redirige al dashboard. El nombre técnico correcto del campo en `users` es `email_verified_at`.

**[NEGOCIO VALIDADO + CÓDIGO]** Mientras `email_verified_at` sea nulo, el middleware `verified` impide el acceso normal a las rutas protegidas. Además, el listado general de usuarios y las consultas de cobertura aplican el filtro `whereNotNull('email_verified_at')`, por lo que la persona todavía no aparece en el listado operativo.

**[NEGOCIO VALIDADO]** Las iglesias migradas desde REDIL 1.0 pueden contener registros históricos sin correo real porque la versión anterior lo permitía. Durante la migración podrán recibir correos técnicos predeterminados y verificaciones administradas para habilitar las cuentas. Es una medida transitoria, no el estándar para nuevas iglesias.

**[NEGOCIO VALIDADO]** Una petición de oración pública es una interacción externa, no un registro de membresía. Si quien la envía está autenticado, la petición se asocia con su usuario; si no pertenece a la iglesia, puede suministrar los datos mínimos necesarios para solicitar oración sin que el sistema lo cree como `User`.

## 5. Mapa de componentes

### Núcleo de dominio

| Responsabilidad | Archivos principales |
|---|---|
| Identidad y relaciones transversales | `app/Models/User.php` |
| Clasificación de persona | `app/Models/TipoUsuario.php` |
| Roles y permisos | `app/Models/Role.php`, configuración y tablas de Spatie Permission |
| Entidades asociadas | `app/Models/EntidadRelacionada.php` |
| Formularios dinámicos | `app/Models/FormularioUsuario.php`, secciones, campos y pivotes asociados |

### Entrada y presentación

| Tipo | Componentes principales |
|---|---|
| Controladores | `UserController`, `RolController`, `UsuarioConfiguracionController`, `FormularioUsuarioController`, `ParienteUsuarioController` |
| Livewire | `FormulariosParaUsuarios/*`, `RolesPrivilegios/*`, `Usuarios/*` |
| Vistas | `resources/views/contenido/paginas/usuario/`, vistas de roles, tipos de usuario y formularios |
| Rutas | Bloques Usuarios, Familias, Formularios, Roles y Tipo-Usuario de `routes/app.php` |

### Persistencia principal

- `users` y sus migraciones incrementales.
- `tipo_usuarios`.
- `roles`, `permissions`, `model_has_roles`, `role_has_permissions` y tablas relacionadas.
- `formularios_usuario`, `formulario_usuario_rol`, secciones, campos y valores por usuario.
- `parientes_usuarios`.
- `entidades_relacionadas` y `users.entidad_relacionada_id`.
- Bitácoras de tipo de usuario, sede y estado civil.

### Datos iniciales relevantes

- `UserSeeder.php`
- `RoleSeeder.php`
- `TipoUsuarioSeeder.php`
- `PermisoSeeder.php`
- `FormularioUsuarioSeeder.php`
- `EntidadRelacionadaSeeder.php`

Los seeders son evidencia de configuración técnica, no una definición completa del negocio. Los identificadores fijos y supuestos sobre registros predeterminados deben validarse antes de documentarlos como reglas permanentes.

## 6. Relaciones con otros módulos

```text
Usuarios
├── Roles y permisos: determina capacidades operativas
├── Tipos de usuario: clasifica y puede orientar el rol dependiente
├── Formularios: captura y actualiza información configurable
├── Familias: relaciona usuarios entre sí
├── Grupos: asistencia, liderazgo, exclusiones y seguimiento
├── Reuniones: asistencia, reservas y seguimiento
├── Consolidación: tareas, asignaciones y tipos habilitados
├── Escuelas: historial, materias, niveles y crecimiento
└── Consejería: el usuario puede tener perfil de consejero
```

**[CÓDIGO]** Estas integraciones aparecen como relaciones en `User`, `TipoUsuario` y `Role`. Las reglas internas de cada módulo relacionado todavía no están descritas aquí.

## 7. Autorización, seguridad y multi-tenancy

- **[CÓDIGO]** Las rutas revisadas están dentro de un grupo con `auth` y `verified`.
- **[CÓDIGO]** Varias rutas de perfil usan además `verificarUsuario`.
- **[CÓDIGO]** Aunque la ruta del listado no declara el middleware de permiso específico, `UserController::listar()` obtiene el rol activo y exige `personas.subitem_lista_asistentes` antes de construir el listado.
- **[RIESGO]** La autorización combina comprobaciones manuales sobre el rol activo con comprobaciones directas sobre `User`. Esa combinación necesita una prueba de consistencia para impedir que un rol inactivo aporte permisos inesperados.
- **[CÓDIGO]** El repositorio utiliza migraciones bajo `database/migrations/tenant/` y `User` contiene personalizaciones conscientes del tenant, por ejemplo para suscripciones push.
- **[PENDIENTE]** Construir una matriz verificable: operación → rol → permiso → alcance por sede/tenant → archivo que aplica la regla.

## 8. Contratos, API y pruebas

### Contratos de entrada

**[CÓDIGO]** En este piloto se identificaron principalmente rutas web, controladores, vistas y componentes Livewire. No se detectó una especificación Swagger/OpenAPI como fuente del módulo durante el inventario inicial.

**[PENDIENTE]** Comprobar si existen consumidores externos o endpoints API en otros archivos antes de afirmar que el módulo es exclusivamente web.

### Cobertura automatizada observada

**[CÓDIGO Y PRUEBA]** `tests/Feature/UserGroupPilotTest.php` cubre el rol activo, cambio de rol, aislamiento polimórfico, membresía y desvinculación, jerarquía, asistencia histórica, hitos idempotentes y promoción automática de tipo/rol.

Pruebas prioritarias para una fase futura, sin implementarlas en este piloto:

1. Rechazo de un rol no asignado y limpieza efectiva de caché de permisos.
2. Promoción de tipo de usuario con mayor, igual y menor puntaje; caso forzado.
3. Autorización del listado, perfil, modificación y exportación de usuarios.
4. Creación y edición mediante formularios configurables.
5. Relaciones familiares, responsables y menores de edad.
6. Aislamiento entre tenants y alcance por sede.

## 9. Hallazgos del piloto

- **[RIESGO]** `User.php` supera ampliamente el tamaño de un modelo de identidad simple y concentra relaciones de numerosos dominios. Para el orquestador esto implica cargar primero una vista resumida y abrir solo las relaciones necesarias; cargar el archivo completo por defecto generaría contexto ruidoso.
- **[RIESGO]** La documentación anterior atribuye a `TipoUsuario` campos de entidad y clasificación que cambiaron en migraciones posteriores. Actualmente la entidad relacionada se asocia directamente a `User` y representa infraestructura para ideas futuras, no una capacidad funcional consolidada.
- **[RIESGO]** Las instrucciones del proyecto describen Laravel 11 y PHPUnit 10, mientras la instalación observada reporta Laravel 12.53.0 y PHPUnit 11.5.55. La guía técnica debe actualizarse en una fase separada para evitar que agentes sigan versiones incorrectas.
- **[RIESGO]** El escaneo actual de Kaddo identificó Vite/JavaScript como stack principal y solo 12 migraciones, por lo que su inventario no representa adecuadamente esta aplicación Laravel con migraciones tenant. No debe usarse todavía como fuente técnica canónica.

## 10. Reglas de enrutamiento para el futuro orquestador

### Cuándo cargar este documento

Cargarlo cuando la solicitud mencione de forma sustantiva usuarios, personas, asistentes, perfiles, roles, permisos, tipos de usuario, formularios de usuario, familias, parentescos, responsables, sedes o entidades relacionadas.

### Desambiguación necesaria

- “Asistentes” puede significar personas/usuarios o asistentes a una reunión. Si la intención no es clara, consultar primero el contexto del cambio.
- “Grupos” debe abrir primero el dominio Grupos y cargar Usuarios como dependencia cuando intervengan integrantes, encargados o permisos.
- “Consejeros” debe abrir primero Consejería cuando la solicitud trate citas o calendarios; Usuarios queda como dependencia de identidad y rol.
- “Permisos” abre este dominio y, además, el dominio funcional cuya operación se desea autorizar.

### Carga mínima recomendada

1. Leer este resumen.
2. Determinar el flujo concreto y los dominios relacionados.
3. Abrir únicamente controladores, modelos, rutas, vistas, migraciones y pruebas del flujo.
4. Consultar Laravel Boost para documentación compatible con las versiones instaladas cuando haya trabajo Laravel.
5. Usar Kaddo para localizar y mantener conocimiento del proyecto; usar Boost para conocer el framework y observar la aplicación. Ninguno reemplaza la revisión del código ni las pruebas.

## 11. Resultado del recorrido funcional

El resumen `documentacion_funcional_personas_consolidacion.md`, generado a partir de la videollamada, cubre Personas, Consolidación y Peticiones. Para evitar mezclar responsabilidades, este documento conserva solamente las reglas necesarias para Usuarios; Consolidación y Peticiones deberán tener sus propios `current-state.md` y enlazar Usuarios como dependencia.

### Decisiones validadas e incorporadas

- Usuario, persona y asistente son equivalentes en REDIL Cloud; “asistente” es nomenclatura heredada.
- `users` unifica la identidad y las credenciales que REDIL 1.0 almacenaba por separado.
- Cada usuario puede tener varios roles asignados y solamente uno activo.
- Los roles independientes se asignan fuera de `TipoUsuario` y se conservan cuando cambia el rol dependiente asociado al tipo.
- El correo real, único, la contraseña y la verificación son el estándar de las iglesias nuevas.
- Los correos técnicos de registros heredados son una excepción transitoria de migración.
- Los menores nuevos son registrados por un responsable y no deben almacenar datos de contacto directo.
- Una petición pública puede recibir datos mínimos sin crear un usuario externo.
- Las entidades relacionadas son infraestructura presente para posibilidades de negocio futuras.
- Los nombres propios de participantes de una reunión no representan actores del sistema; deben normalizarse a responsabilidades como administrador, líder o consolidador.

### Información que permanece pendiente

- Matriz completa de permisos para asignar tipo, roles dependientes e independientes.
- Confirmar las reglas funcionales de cada tipo de formulario y su política de aprobación.
- Definir qué roles pueden administrar, duplicar, ocultar y eliminar formularios.
- Reglas de parentesco inverso y selección de responsable.
- Matriz operación → permiso → rol → alcance territorial.
- Responsable formal de aprobar cada regla y decisión futura.
- Separación documental completa de Consolidación y Peticiones.

## 12. Criterios para cerrar el piloto

Este documento podrá pasar de `draft` a `reviewed` cuando:

- El responsable funcional valide propósito, actores, flujos y reglas.
- El responsable técnico valide inventario, autorización, dependencias y riesgos.
- Las contradicciones con documentos anteriores estén resueltas.
- Se defina qué archivos serán canónicos y cuáles quedarán como historial.
- El enrutamiento encuentre el módulo mediante varias expresiones sin cargar dominios innecesarios.
- Se acuerde una regla sencilla para actualizar esta documentación con cada cambio relevante.

## 13. Fuentes revisadas

### Código y configuración

- `app/Models/User.php`
- `app/Models/Role.php`
- `app/Models/TipoUsuario.php`
- `app/Models/EntidadRelacionada.php`
- `app/Http/Controllers/UserController.php`
- `app/Http/Controllers/RolController.php`
- `app/Http/Controllers/UsuarioConfiguracionController.php`
- `app/Http/Controllers/FormularioUsuarioController.php`
- `app/Http/Controllers/ParienteUsuarioController.php`
- `routes/app.php`
- Migraciones tenant y seeders relacionados.
- `composer.json`, `composer.lock` y versiones instaladas.

### Documentación previa

- `.agent/workflows/agenteUsuarios.md`
- `_docs_agente/modulos/usuarios.md`
- `knowledge/inventory.md`
- `.kaddo/scan.json`
- `documentacion_funcional_personas_consolidacion.md` — resumen externo de la videollamada; sus afirmaciones se incorporan únicamente cuando están clasificadas y validadas.
- `knowledge/tech/discovery/usuarios-crear-editar-formularios-video-2026-09-06.md` — revisión anonimizada del video `CREAR USUARIO.mov`, contrastada con código; la narración no se conserva como transcripción literal.
- `knowledge/tech/discovery/usuarios-vistas-perfil-y-administracion-imagenes-2026-09-06.md` — revisión anonimizada de ocho capturas de listado, formulario, administración y perfil, contrastada con código.

## 14. Historial de revisión

| Fecha | Estado | Cambio | Responsable |
|---|---|---|---|
| 2026-09-02 | Borrador técnico | Creación del piloto desde código y documentación existente. No incluye aún validación del recorrido funcional. | Codex + revisión pendiente del equipo |
| 2026-09-02 | Borrador funcional parcial | Incorporación del recorrido de Personas y validación de nomenclatura, roles, correo, migración, menores, peticiones externas y entidades futuras. | Responsable del producto + Codex |
| 2026-09-06 | Borrador funcional/técnico ampliado | Incorporación anonimizada del recorrido en video de creación pública y administrativa, edición y configuración de formularios. | Responsable del producto + Codex |
| 2026-09-06 | Reglas funcionales validadas | Confirmación de activación por `email_verified_at`, obligatoriedad configurable y eliminación de formularios utilizados. | Responsable del producto + Codex |
| 2026-09-06 | Evidencia visual ampliada | Incorporación del listado, menú, edición, perfil y administración congregacional; validación de la independencia entre roles adicionales y tipo de usuario. | Responsable del producto + Codex |
