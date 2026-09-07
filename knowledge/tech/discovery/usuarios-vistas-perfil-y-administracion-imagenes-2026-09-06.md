---
type: discovery-evidence
id: DISC-USUARIOS-IMAGENES-001
title: Vistas de listado, perfil y administración congregacional de Usuarios
status: reviewed-against-code
domain: usuarios
related_domains:
  - roles-permisos
  - grupos
  - escuelas
source_type: images-and-business-validation
source_files:
  - LISTADO USUARIOS.png
  - LISTADO MENU.png
  - FORMULARIO EDITAR.png
  - INFORMACION CONGREGACIONAL.png
  - PERFIL PRINCIPAL.png
  - PERFIL RELACIONES FAMILIARES.png
  - PERFIL INFO CONGRACIONAL.png
  - PERFIL ESCULAS.png
source_date: 2026-09-06
privacy: description-only-no-personal-tenant-or-qr-data-copied
reviewed_at: 2026-09-06
---

# Evidencia: vistas de Usuarios

## Alcance y privacidad

Las ocho capturas complementan el recorrido en video de creación, edición y formularios con las vistas de listado, acciones administrativas y perfil de una persona.

Las imágenes originales permanecen fuera del repositorio. Esta nota no reproduce nombres, correos, teléfonos, identificaciones, sedes, iglesias, ubicaciones ni el contenido del código QR visibles. Esos datos pertenecen a una sesión concreta y no constituyen reglas del producto.

## Dos recorridos relacionados

```text
Listado de personas
├── Ver perfil
│   ├── Principal
│   ├── Familia
│   ├── Congregación
│   ├── Escuelas
│   └── Hitos
└── Administrar
    ├── Datos principales
    ├── Información congregacional
    ├── Geo asignación
    └── Relaciones familiares
```

El perfil presenta información consolidada para consulta. Las pantallas de administración modifican áreas específicas del mismo usuario y están condicionadas por autorización.

## Inventario de vistas

### 1. Listado de usuarios

**[IMAGEN OBSERVADA]** La vista presenta indicadores resumidos, búsqueda, filtros, descarga a Excel, tarjetas de personas y paginación. Cada tarjeta puede mostrar tipo de usuario, actividad en grupos y reuniones, roles y encargados, según los datos disponibles.

**[CÓDIGO]** `UserController::listar()` exige el permiso del listado mediante el rol activo y excluye del listado operativo a quienes todavía no tienen `email_verified_at`.

### 2. Menú de acciones del listado

**[IMAGEN OBSERVADA + CÓDIGO]** El menú contextual observado contiene acciones para ver el perfil, agendar una cita, editar, abrir información congregacional, relaciones familiares y geoasignación, cambiar o restablecer contraseña, obtener el código QR, dar de baja y eliminar. La disponibilidad real depende de permisos y del estado de la persona; la presencia visual del menú no reemplaza la autorización del servidor.

### 3. Edición mediante formulario

**[IMAGEN OBSERVADA]** La edición de datos principales se construye por secciones y campos configurables. La captura muestra una composición particular de secciones, por lo que sus nombres y campos no deben tratarse como un contrato fijo.

**[VIDEO OBSERVADO + CÓDIGO]** Visibilidad, orden, obligatoriedad, etiqueta y tipo de cada campo provienen de la configuración del formulario. Datos principales, información congregacional, geoasignación y relaciones familiares se guardan mediante flujos diferenciados.

### 4. Información congregacional editable

**[IMAGEN OBSERVADA + NEGOCIO VALIDADO]** Esta vista permite administrar sobre la misma persona:

- su tipo de usuario;
- los grupos a los que asiste como integrante;
- sus pasos de crecimiento, organizados por secciones y con estado, fecha y detalle;
- sus roles independientes.

**[NEGOCIO VALIDADO]** “Asignar roles independientes” se refiere a roles que no están relacionados directamente con un tipo de usuario. Deben documentarse y administrarse por separado del rol dependiente asociado a `TipoUsuario`.

**[CÓDIGO Y PRUEBA]** `TipoUsuario::rolDependiente()` usa `id_rol_dependiente`. La pantalla consulta roles con `Role.dependiente = false` y la actualización los adjunta con el pivote `model_has_roles.dependiente = false`. `User::promoverTipoUsuario()` reemplaza los roles dependientes cuando cambia el tipo y conserva los independientes; `UserGroupPilotTest` cubre esa conservación.

**[REGLA DE SESIÓN]** Ser independiente del tipo de usuario no significa quedar activo automáticamente. Un usuario puede tener varios roles asignados, pero solo uno debe estar activo para aportar permisos; los roles independientes nuevos se adjuntan inicialmente inactivos.

### 5. Perfil principal

**[IMAGEN OBSERVADA]** La pestaña Principal reúne el QR único de la persona y bloques de información personal, estudios y ocupación, información médica, datos del acudiente, archivos adjuntos, información adicional, campos extra y datos de creación.

**[PRIVACIDAD]** El QR se usa como identificador operativo para asistencias e inscripciones. Su contenido y los datos personales mostrados no se incorporan a la documentación.

### 6. Perfil familiar

**[IMAGEN OBSERVADA]** La pestaña Familia muestra el grupo familiar y los datos del acudiente, con estados vacíos cuando no existen relaciones. Esta vista es de consulta; la administración se realiza desde Relaciones familiares.

### 7. Perfil congregacional

**[IMAGEN OBSERVADA]** La pestaña Congregación consolida vinculación, sede, roles, servicios en grupos, grupos dirigidos, encargados, grupos a los que asiste, peticiones, actividad de asistencia y el estado de los procesos de crecimiento.

Esta vista relaciona Usuarios con Grupos y otros dominios, pero no traslada sus reglas internas al módulo de Usuarios.

### 8. Perfil de escuelas

**[IMAGEN OBSERVADA]** La pestaña Escuelas presenta el avance académico de la persona por escuela, materias y créditos, incluyendo estados disponibles para cursar.

**[LÍMITE DE DOMINIO]** Esta captura documenta el punto de integración dentro del perfil del usuario. Las reglas de materias, matrículas, créditos y aprobación pertenecen al dominio Escuelas y no se incorporan al alcance del piloto por esta evidencia.

## Relación entre tipo y roles

```text
TipoUsuario
    └── puede señalar un Role dependiente
            └── se reemplaza al promover/cambiar el tipo

User
    ├── pertenece a uno o más grupos como integrante
    ├── registra pasos de crecimiento
    └── puede recibir Roles independientes
            └── no dependen del TipoUsuario y se conservan al promoverlo

Roles asignados al User
    └── exactamente uno debe aportar permisos como rol activo
```

## Reglas documentales derivadas

- Tipo de usuario y rol no son sinónimos.
- El rol dependiente representa la relación técnica que puede derivarse del tipo de usuario.
- Los roles independientes se asignan de forma explícita y no se sustituyen por una promoción de tipo.
- La membresía de grupo registrada aquí es la misma relación de integrante administrada desde Grupos.
- Los pasos de crecimiento pertenecen a la información congregacional de la persona y guardan estado, fecha y detalle.
- El perfil combina datos de varios dominios para consulta; cada dominio conserva sus propias reglas y documentación.
- Las capturas son evidencia visual, no una matriz suficiente de autorización ni un contrato rígido de campos.

## Archivos contrastados

- `routes/app.php`
- `app/Http/Controllers/UserController.php`
- `app/Models/User.php`
- `app/Models/Role.php`
- `app/Models/TipoUsuario.php`
- `resources/views/contenido/paginas/usuario/listar.blade.php`
- `resources/views/contenido/paginas/usuario/perfil.blade.php`
- `resources/views/contenido/paginas/usuario/perfil-familia.blade.php`
- `resources/views/contenido/paginas/usuario/perfil-congregacion.blade.php`
- `resources/views/contenido/paginas/usuario/perfil-historial-escuelas.blade.php`
- `resources/views/contenido/paginas/usuario/informacion-congregacional.blade.php`
- `tests/Feature/UserGroupPilotTest.php`

## Uso futuro

Esta evidencia sirve para orientar cambios de Usuarios y para que el orquestador cargue las integraciones pertinentes. No autoriza cambios funcionales ni amplía el piloto a Escuelas u otros módulos.
