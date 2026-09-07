---
id: MOD-CONSOLIDACION
title: Módulo de Consolidación
artifact_type: domain-current-state
status: draft
language: es
last_reviewed: 2026-09-02
technical_review: partial
business_review: partial
owners:
  - por-definir
aliases:
  - consolidacion
  - conecta
  - ministerio conecta
  - seguimiento
  - tareas de consolidacion
  - zonas de pastoreo
related_domains:
  - usuarios
  - grupos
  - sedes
  - escuelas
  - consejeria
---

# Módulo de Consolidación

> Borrador separado del recorrido funcional de Personas, Consolidación y Peticiones. Este archivo documenta contexto; no configura Kaddo, no ejecuta procesos y no reemplaza las verificaciones de autorización ni las pruebas.

## 1. Clasificación de evidencia

- **[CÓDIGO]**: comprobado directamente en el repositorio el 2 de septiembre de 2026.
- **[NEGOCIO VALIDADO]**: confirmado por el responsable del producto.
- **[NEGOCIO POR VALIDAR]**: extraído del resumen de la videollamada y pendiente de confirmación específica.
- **[PENDIENTE]**: decisión o detalle todavía abierto.
- **[RIESGO]**: comportamiento técnico que debe revisarse; no implica por sí solo un defecto confirmado.

## 2. Propósito y límites

**[NEGOCIO POR VALIDAR]** Consolidación, también asociada en la conversación al ministerio “Conecta”, organiza el acompañamiento operativo de personas nuevas mediante llamadas, visitas, mensajes u otras tareas. Busca separar ese trabajo medible de los pasos formativos o espirituales de largo plazo.

### Incluido

- Selección de personas elegibles para consolidación.
- Alcance por rol activo, ministerio, zona, sede y localidad.
- Tareas de consolidación, estados, fechas e historial.
- Filtros configurables basados en tareas, estados y estado civil.
- Paneles, indicadores, detalle de KPI y reportes de desempeño.
- Configuración de zonas de pastoreo.

### Fuera del dominio principal

- La identidad, los datos personales y los roles pertenecen a Usuarios.
- Los pasos de crecimiento pertenecen al proceso de crecimiento, aunque Consolidación los consulta y puede integrarse con hitos.
- Los cursos y materias pertenecen a Escuelas.
- Las citas y tipos de consejería pertenecen a Consejería.
- Las peticiones de oración pertenecen a Peticiones.

## 3. Conceptos

| Concepto | Significado | Evidencia |
|---|---|---|
| Persona en consolidación | `User` cuyo tipo está habilitado para consolidación y que además se encuentra dentro del alcance permitido al operador. | **[CÓDIGO]** `TipoUsuario.habilitado_para_consolidacion`, `ConsolidacionController::listar()` |
| Tarea de consolidación | Actividad configurable asignable a una persona, con nombre, descripción, orden y señal de tarea predeterminada. | **[CÓDIGO]** `TareaConsolidacion` |
| Asignación de tarea | Registro que conecta persona, tarea, estado y fecha. | **[CÓDIGO]** `TareaConsolidacionUsuario` |
| Estado de tarea | Estado ordenable por puntaje usado para representar el avance de la tarea. | **[CÓDIGO]** `EstadoTareaConsolidacion` |
| Historial y bitácora | Evidencia de asignaciones, cambios de estado, autor, fecha, sede y zona. | **[CÓDIGO]** modelos de historial y bitácora |
| Filtro de consolidación | Segmento configurable que incluye o excluye combinaciones de tareas y estados y puede limitar estados civiles. | **[CÓDIGO]** `FiltroConsolidacion` |
| Zona de pastoreo | Agrupación configurable de sedes y localidades usada para delimitar el alcance de consolidación. | **[CÓDIGO]** `Zona`, `sede_zona`, `localidad_zona` |

## 4. Flujos verificados

### 4.1 Construir la lista de personas

1. **[CÓDIGO]** Se obtiene el rol activo y se exige `consolidacion.subitem_lista_consolidacion`.
2. **[CÓDIGO]** Con `lista_toda_consolidacion`, se consultan usuarios cuyos tipos están habilitados para consolidación.
3. **[CÓDIGO]** Con `lista_consolidacion_solo_ministerio`, se usa `User::consolidacion()` para limitar el alcance.
4. **[CÓDIGO]** El alcance puede depender de la zona configurada en el rol activo o de los grupos y personas creadas por el operador.
5. **[CÓDIGO]** Se aplican edad mínima, búsqueda y filtros personales, congregacionales y dinámicos.
6. **[CÓDIGO]** La salida se pagina y distingue personas sin tareas, filtros configurados y total accesible.

### 4.2 Asignar y actualizar tareas

1. **[CÓDIGO]** La gestión detallada exige `consolidacion.gestionar_tareas`.
2. **[CÓDIGO]** Livewire permite asignar tareas no predeterminadas que la persona todavía no tenga.
3. **[CÓDIGO]** La asignación guarda tarea, estado, fecha y un detalle opcional.
4. **[CÓDIGO]** Los cambios de estado generan bitácora y pueden disparar `HitoTriggerService`.
5. **[CÓDIGO]** `procesarTarea()` evita retroceder a un estado con puntaje igual o inferior.

**[NEGOCIO POR VALIDAR]** Las tareas representan acciones operativas como llamadas, visitas o envío de contenido; no equivalen a pasos de crecimiento. La integración con hitos debe documentarse como una consecuencia configurable, no como equivalencia conceptual.

### 4.3 Analítica y desempeño

**[CÓDIGO]** Existen dashboard, bloques, reporte de desempeño, detalles de KPI y exportaciones a Excel, con filtros temporales y territoriales.

**[NEGOCIO POR VALIDAR]** “Cosecha” representa personas nuevas, “deserción” personas dadas de baja y “cosecha efectiva” la diferencia. Deben confirmarse fórmula exacta, fecha que determina cada conteo y tratamiento de traslados o reactivaciones.

**[NEGOCIO POR VALIDAR]** El resumen de la reunión habla de un tablero tipo Kanban y actualización en tiempo real. El código confirma estados, tarjetas y componentes reactivos, pero falta validar si la experiencia completa cumple actualmente esos términos.

## 5. Mapa técnico

### Componentes principales

- Controladores: `ConsolidacionController`, `TareaConsolidacionController`, `FiltroConsolidacionController`, `ZonaController`.
- Modelos: `TareaConsolidacion`, `TareaConsolidacionUsuario`, `EstadoTareaConsolidacion`, `FiltroConsolidacion`, historiales, bitácoras, bloques y `Zona`.
- Livewire: `Consolidacion/GestionarTareas`, `Consolidacion/GestionarBloques`, `Zonas/GestionarZonas`.
- Vistas: `resources/views/contenido/paginas/consolidacion/`, tareas, filtros, zonas y dashboard de consolidación por sede.
- Exportaciones: dashboard y detalles de KPI de consolidación.

### Persistencia principal

- `tareas_consolidacion`
- `estados_tarea_consolidacion`
- `tarea_consolidacion_usuario`
- `historiales_tarea_consolidacion_usuario`
- `bitacora_tareas_consolidacion`
- `filtros_consolidacion` y pivotes de condiciones
- `bloques_dashboard_consolidacion`
- `zonas`, `sede_zona` y `localidad_zona`

### Datos iniciales

- `TareaConsolidacionSeeder.php`
- `EstadoTareaConsolidacionSeeder.php`
- `TareaConsolidacionUsuarioSeeder.php`
- `HistorialTareaConsolidacionUsuarioSeeder.php`
- `FiltroConsolidacionSeeder.php`
- `BloquesDashboardConsolidacionSeeder.php`
- `ZonaSeeder.php`

## 6. Autorización y alcance

- **[CÓDIGO]** Listado, gestión de tareas y dashboard consultan el rol activo y exigen permisos específicos.
- **[CÓDIGO]** El alcance limitado reutiliza `User::consolidacion()` y puede derivarse de zona, sedes, localidades, grupos o personas creadas por el operador.
- **[NEGOCIO POR VALIDAR]** El consolidador debe ver únicamente las personas correspondientes a su zona o cobertura autorizada.
- **[RIESGO]** La ruta de gestión recibe cualquier `User` y el método verifica el permiso general de gestionar tareas, pero no se observó allí una comprobación explícita de que ese usuario pertenezca al alcance territorial del operador.
- **[RIESGO]** `ConsolidacionController::bloques()` contiene comentada la verificación de permiso. Debe definirse y probarse la regla esperada antes de modificarla.
- **[RIESGO]** La bitácora usa `auth()->id() ?? 1`; los procesos sin sesión podrían atribuir acciones al usuario 1. Debe acordarse la identidad técnica correcta para automatizaciones.

## 7. Reglas de negocio provisionales

- **[NEGOCIO POR VALIDAR]** Consolidación acompaña a personas nuevas hasta su vinculación y seguimiento efectivo.
- **[CÓDIGO]** Solo participan automáticamente tipos de usuario con `habilitado_para_consolidacion` cuando se usa el alcance global.
- **[CÓDIGO]** Una persona puede tener varias tareas diferentes, cada una con un estado y fecha.
- **[CÓDIGO]** Una tarea marcada `default` se trata de forma distinta de una tarea manual en la interfaz de asignación.
- **[CÓDIGO]** Los estados tienen puntaje y los cambios automatizados no deben degradar el avance.
- **[NEGOCIO POR VALIDAR]** El desempeño debe medirse por zona, servidor, tarea y periodo sin confundir actividad operativa con crecimiento espiritual.

## 8. Pruebas y riesgos de conocimiento

**[RIESGO]** No se encontraron pruebas dedicadas por nombre a Consolidación, tareas, filtros o zonas. Debe comprobarse si existe cobertura indirecta.

Pruebas futuras prioritarias:

1. Alcance global frente a alcance por ministerio y zona.
2. Usuario fuera de la zona al abrir o modificar tareas.
3. Inclusión y exclusión en filtros dinámicos.
4. Progresión y no degradación por puntaje de estado.
5. Historial, bitácora y autor en acciones manuales y automáticas.
6. Fórmulas de dashboard, periodo, zona, sede y exportación.
7. Aislamiento entre tenants.

## 9. Enrutamiento del futuro orquestador

### Cargar este documento cuando

La solicitud mencione consolidación, Conecta, consolidadores, seguimiento de personas nuevas, tareas, estados de tarea, cosecha, deserción, desempeño o zonas de pastoreo.

### Dependencias de contexto

- Cargar Usuarios cuando intervengan identidad, tipo de usuario, rol activo o perfil.
- Cargar Grupos cuando el alcance provenga de cobertura ministerial.
- Cargar Sedes cuando haya segmentación territorial o dashboards por sede.
- Cargar Escuelas o Crecimiento solo cuando una tarea dispare hitos, cursos o pasos.
- Cargar Consejería cuando se configuren tareas relacionadas con tipos de consejería.

## 10. Preguntas pendientes

- ¿Cuál es el punto exacto de entrada y salida del proceso de consolidación?
- ¿Qué tareas y estados son obligatorios y cuáles puede configurar cada iglesia?
- ¿Cómo se calculan cosecha, deserción y cosecha efectiva?
- ¿Qué sucede cuando una persona cambia de sede, localidad, zona o grupo?
- ¿Quién puede reasignar, eliminar o retroceder tareas?
- ¿Qué acciones deben disparar hitos y cuáles deben permanecer independientes?
- ¿Cuál es la regla formal de acceso para `bloques()` y `gestionarTareas()`?

## 11. Fuentes

- `documentacion_funcional_personas_consolidacion.md`, resumen externo de la videollamada.
- `app/Http/Controllers/ConsolidacionController.php`
- `app/Http/Controllers/TareaConsolidacionController.php`
- `app/Http/Controllers/FiltroConsolidacionController.php`
- `app/Models/User.php`
- Modelos, Livewire, rutas, migraciones tenant, vistas, exportaciones y seeders relacionados.
- `knowledge/tech/domains/usuarios/current-state.md`

## 12. Historial

| Fecha | Estado | Cambio | Responsable |
|---|---|---|---|
| 2026-09-02 | Borrador funcional y técnico | Separación inicial de Consolidación desde la fuente conjunta y contraste con el repositorio. | Responsable del producto + Codex |
