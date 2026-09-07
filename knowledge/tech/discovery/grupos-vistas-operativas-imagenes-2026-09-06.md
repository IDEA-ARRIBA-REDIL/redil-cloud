---
type: discovery-evidence
id: DISC-GRUPOS-IMAGENES-001
title: Vistas de creación y administración operativa de Grupos
status: reviewed-against-code
domain: grupos
related_domains:
  - usuarios
  - roles-permisos
source_type: images
source_files:
  - NUEVO GRUPO.png
  - EDITAR GRUPO.png
  - ASIGNAR ENCARGADOS.png
  - ASIGNAR ASISTENTES.png
  - GEOREFERENCIA.png
source_date: 2026-09-06
privacy: description-only-no-personal-or-tenant-data-copied
reviewed_at: 2026-09-06
---

# Evidencia: vistas operativas de Grupos

## Alcance

Las cinco capturas complementan el recorrido de `GRUPOS.mp4` con evidencia estática legible de las pantallas para crear un grupo y, después de su creación, editar datos, administrar encargados e integrantes y asignar su georreferencia.

Las imágenes originales permanecen fuera del repositorio. Esta nota no reproduce nombres de personas, nombres de iglesias, fechas, ubicaciones ni valores de ejemplo visibles. Esos valores ilustran una sesión concreta y no constituyen reglas del producto.

## Secuencia funcional observada

```text
Crear grupo
    │
    └── Datos principales + horario + campos extra + portada
            │
            ▼
       Grupo persistido
            │
            ├── Datos principales
            ├── Encargados
            ├── Integrantes
            └── Georeferencia
```

La pantalla “Nuevo grupo” no presenta las cuatro pestañas porque todavía no existe un grupo sobre el cual asociar personas o coordenadas. Las cuatro áreas aparecen en las vistas posteriores y cada pestaña se muestra según permisos del rol activo.

## Inventario de vistas

### 1. Nuevo grupo

**[IMAGEN OBSERVADA]** La vista organiza el alta en una portada y tres bloques visibles:

- Información principal: nombre, tipo de grupo, fecha de creación, teléfono, calidad de vivienda, dirección y un campo opcional cuya etiqueta puede configurarse.
- Horario de reunión: día y hora.
- Campos extras: en la captura se muestran dos ejemplos, pero su nombre y composición no deben generalizarse.

La acción final es Guardar y existe una opción para cambiar la portada.

**[CÓDIGO]** `GrupoController::nuevo()` carga configuración, tipos de grupo, tipos de vivienda y campos extra. `crear()` determina visibilidad y obligatoriedad mediante `Configuracion`, persiste el grupo, asigna sede e índice ministerial, guarda campos extra y procesa una portada opcional.

### 2. Editar grupo / Datos principales

**[IMAGEN OBSERVADA]** La edición reutiliza la misma organización y presenta los valores actuales. Desde aquí aparece la navegación Datos principales → Encargados → Integrantes → Georeferencia. También permite cambiar la portada y guardar los cambios.

**[CÓDIGO]** `GrupoController::modificar()` carga los catálogos y valores existentes; `editar()` vuelve a construir las validaciones configurables y actualiza atributos, campos extra, sede, índice ministerial y portada.

**[CÓDIGO]** La pestaña Datos principales requiere `grupos.pestana_actualizar_grupo`; la acción exige además `grupos.opcion_modificar_grupo` en el controlador.

### 3. Gestionar encargados

**[IMAGEN OBSERVADA]** La pantalla presenta un buscador múltiple y tarjetas de personas ya seleccionadas. Cada tarjeta permite retirar la asignación. El texto funcional identifica a los encargados como quienes dirigen el grupo; la información secundaria de la persona puede mostrar una clasificación como “Líder”.

**[CÓDIGO]** La vista usa `UsuariosParaBusqueda` con módulo `encargados-grupo`, excluye personas dadas de baja y valida privilegios del tipo de grupo. Según el rol activo, la búsqueda abarca todos los usuarios o solamente los discípulos de su ministerio.

**[CÓDIGO]** `Grupo::asignarEncargado()` evita duplicados, crea la relación en `encargados_grupo`, sincroniza sede y dispara el hito de designación cuando aplica. La asignación o desvinculación puede generar un informe y solicitar motivo según permisos y configuración.

**[VOCABULARIO]** “Encargado” es la relación funcional con el grupo. Una etiqueta visual como “Líder” describe a la persona en esa sesión, pero no debe asumirse como nombre obligatorio de un rol técnico.

### 4. Gestionar integrantes

**[IMAGEN OBSERVADA]** La pantalla presenta un buscador múltiple y tarjetas removibles para las personas que asisten al grupo. Aunque el archivo entregado se denomina `ASIGNAR ASISTENTES.png`, la interfaz vigente usa “Integrantes”.

**[CÓDIGO]** La vista usa `UsuariosParaBusqueda` con módulo `integrantes-grupo`, sin personas dadas de baja. El rol activo limita la búsqueda a todos los usuarios o a su ministerio, y `TipoGrupo::tipoUsuariosPermitidos()` puede restringir qué tipos de usuario se asignan.

**[CÓDIGO]** `User::cambiarGrupo()` evita duplicados, registra la membresía y su bitácora, asigna sede y dispara el hito correspondiente. La asignación puede ejecutar automatizaciones de tipo de usuario y pasos de crecimiento. `desvincularDeGrupo()` elimina la membresía actual y conserva el cambio en bitácora; la asistencia histórica pertenece a los reportes y no se elimina por esta acción.

### 5. Gestionar georreferencia

**[IMAGEN OBSERVADA]** La vista contiene un buscador territorial y un mapa Leaflet/OpenStreetMap. La instrucción visible indica que al pulsar el mapa la ubicación se asigna automáticamente al grupo.

**[CÓDIGO]** El clic despacha `asignar-georreferencia-al-grupo` con identificador, latitud y longitud. `MapaGeoAsignacion::asignarGeorreferenciaAlGrupo()` persiste ambas coordenadas y presenta confirmación. El buscador consulta Nominatim después de ingresar más de tres caracteres y puede centrar el mapa en el resultado.

**[CÓDIGO]** La pestaña y la operación requieren los permisos `grupos.pestana_georreferencia_grupo` o `grupos.opcion_georreferencia_grupo`, además del alcance aplicado por `verificarGrupo`.

**[RIESGO YA DOCUMENTADO]** La inicialización geográfica del controlador contiene referencias y condiciones que deben reproducirse técnicamente antes de corregirse. Estas capturas demuestran la experiencia esperada, no que todos los casos de inicialización funcionen.

## Integración con Usuarios y Roles/Permisos

```text
User ── integrantes_grupo ──> Grupo
  │                            │
  └── encargados_grupo ────────┘

Rol activo
  ├── decide qué pestañas y acciones están disponibles
  ├── limita si la búsqueda carga todos los usuarios o solo el ministerio
  └── combina sus permisos con privilegios definidos por TipoGrupo
```

Las capturas confirman la experiencia visual; el código confirma que las asociaciones no son texto dentro del grupo, sino relaciones con usuarios existentes. Un mismo usuario puede aparecer en distintos contextos de membresía o liderazgo de acuerdo con las reglas y permisos aplicables.

## Reglas documentales derivadas

- Crear el grupo es el prerrequisito para asignar encargados, integrantes o coordenadas.
- Datos principales, encargados, integrantes y georreferencia son áreas independientes de administración sobre el mismo grupo.
- La presencia de una pestaña depende del permiso del rol activo.
- Los campos de creación y edición son configurables en visibilidad, etiqueta y obligatoriedad; los campos mostrados en una captura no forman un contrato rígido.
- Las selecciones de personas operan sobre usuarios existentes y generan relaciones persistentes y auditables.
- “Integrante” es el término de interfaz vigente; “asistente” se conserva para ciertos nombres históricos de código o archivos cuando sea necesario.

## Archivos contrastados

- `routes/app.php`
- `app/Http/Controllers/GrupoController.php`
- `app/Livewire/Usuarios/UsuariosParaBusqueda.php`
- `app/Livewire/Usuarios/MapaGeoAsignacion.php`
- `app/Models/Grupo.php`
- `app/Models/User.php`
- `resources/views/contenido/paginas/grupos/nuevo.blade.php`
- `resources/views/contenido/paginas/grupos/modificar.blade.php`
- `resources/views/contenido/paginas/grupos/gestionar-encargados.blade.php`
- `resources/views/contenido/paginas/grupos/gestionar-integrantes.blade.php`
- `resources/views/contenido/paginas/grupos/georreferencia.blade.php`

## Uso futuro

Esta evidencia sirve como referencia funcional y visual para futuros cambios de estas pantallas. No reemplaza pruebas de autorización, integridad, accesibilidad o aislamiento entre iglesias, y no autoriza por sí sola modificaciones funcionales.
