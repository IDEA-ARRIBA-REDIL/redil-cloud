---
id: MOD-GRUPOS
title: Módulo de Grupos y Reportes de Grupo
artifact_type: domain-current-state
status: draft
language: es
last_reviewed: 2026-09-06
technical_review: partial
business_review: partial
owners:
  - por-definir
code:
  - app/Models/Grupo.php
  - app/Models/TipoGrupo.php
  - app/Http/Controllers/GrupoController.php
  - app/Livewire/Grupos/**
  - app/Livewire/Usuarios/UsuariosParaBusqueda.php
  - tests/Feature/UserGroupPilotTest.php
aliases:
  - grupos
  - grupo
  - células
  - mi grupo
  - cobertura
  - tráfico del ministerio
  - gráfico del ministerio
  - reportes de grupo
  - asistencia de grupo
  - evidencias de grupo
related_domains:
  - usuarios
  - reuniones
  - consolidacion
  - sedes
  - crecimiento
  - escuelas
  - finanzas
---

# Módulo de Grupos y Reportes de Grupo

> Piloto de documentación por dominios. Este archivo orienta a desarrolladores y agentes, pero no ejecuta código, no concede permisos y no reemplaza las pruebas ni la validación humana. Las decisiones confirmadas por el responsable funcional están marcadas como validadas; el resto de la explicación de la videollamada conserva su estado pendiente.

## 1. Cómo interpretar este documento

- **[CÓDIGO]**: comportamiento o estructura observado directamente en el repositorio el 4 de septiembre de 2026.
- **[NEGOCIO VALIDADO]**: decisión confirmada por el responsable funcional el 4 de septiembre de 2026.
- **[NEGOCIO POR VALIDAR]**: afirmación extraída de `documentacion-funcional-grupos-v3.md`; todavía requiere confirmación del responsable funcional.
- **[VIDEO OBSERVADO]**: navegación o comportamiento visible en `GRUPOS.mp4`, revisado el 6 de septiembre de 2026; no implica por sí solo autorización o integridad del servidor.
- **[IMÁGENES OBSERVADAS]**: estructura o texto visible en las cinco capturas operativas suministradas el 6 de septiembre de 2026; sus valores concretos son ejemplos y no reglas generales.
- **[PENDIENTE]**: pregunta abierta o comportamiento no demostrado.
- **[RIESGO]**: diferencia o debilidad que merece revisión; no significa automáticamente que exista un incidente.
- **[FUTURO]**: propuesta o intención que no debe confundirse con el estado actual.

Precedencia provisional: pruebas actuales > código actual > regla de negocio validada y fechada > resumen de reunión > documentación histórica > inferencia de IA.

## 2. Propósito y límites

### Propósito provisional

**[NEGOCIO VALIDADO]** Grupos administra células u otras unidades de trabajo de la iglesia, sus integrantes y encargados, el reporte periódico de actividad, la asistencia, las ofrendas, las evidencias y la supervisión territorial o ministerial.

**[CÓDIGO]** El tipo de grupo parametriza buena parte del comportamiento: frecuencia máxima de reportes, captura de inasistencias, textos del formulario, tipos de ofrenda, disponibilidad del enlace de autoasistencia, visibilidad en mapas y automatizaciones de crecimiento o tipo de usuario.

### Incluido en este dominio

- Creación, edición, baja, consulta y georreferencia de grupos.
- Tipos de grupo y campos configurables.
- Integrantes, encargados, servidores y exclusiones ministeriales.
- Reportes de grupo, asistencia e inasistencia.
- Ofrendas del reporte y su conciliación/aprobación.
- Informes de evidencia.
- Dashboard, comparativos, mapa y gráfico del ministerio.

### Fuera del alcance primario

- La identidad, credenciales, roles y relaciones familiares pertenecen a Usuarios.
- El registro de reuniones generales y reservas pertenece a Reuniones.
- Las tareas de acompañamiento pertenecen a Consolidación.
- La contabilidad completa pertenece a Finanzas, aunque Grupos origina ingresos por ofrendas.
- Pasos de crecimiento, sedes y escuelas conservan sus propias reglas de dominio.

## 3. Glosario operativo

| Concepto | Definición provisional | Evidencia |
|---|---|---|
| Grupo / célula | Unidad configurable con tipo, sede, horario, dirección, encargados e integrantes. | **[CÓDIGO]** `Grupo`, tabla `grupos` |
| Tipo de grupo | Configuración que determina presentación y reglas operativas de los grupos asociados. | **[CÓDIGO]** `TipoGrupo`, tabla `tipo_grupos` |
| Integrante | Usuario vinculado al grupo mediante `integrantes_grupo`. | **[CÓDIGO]** `Grupo::asistentes()` |
| Encargado / líder | Usuario responsable vinculado mediante `encargados_grupo`. “Líder”, “camillador” u otros nombres de cliente no deben convertirse automáticamente en roles técnicos. | **[CÓDIGO]** `Grupo::encargados()`; **[NEGOCIO POR VALIDAR]** terminología funcional |
| Mi Grupo | Experiencia limitada para que una persona gestione el grupo que lidera sin recibir acceso administrativo general. | **[NEGOCIO POR VALIDAR]** |
| Reporte realizado | Registro de una sesión que continúa hacia asistencia, clasificaciones y ofrendas antes de finalizarse. | **[CÓDIGO]** `ModalNuevoReporte`, `Asistencias` |
| No realizado | Reporte finalizado inmediatamente con motivo y, cuando corresponde, descripción adicional. En código se guarda con `no_reporte = true`. | **[CÓDIGO]** `ModalNuevoReporte::submitFormulario()` |
| No reportado | Ausencia de un reporte esperado; se calcula en informes o estadísticas y no equivale a un reporte “No realizado”. | **[NEGOCIO POR VALIDAR]** y vistas de estadísticas |
| Autoasistencia | Enlace público temporal para que un integrante se identifique por documento o correo y marque su asistencia. | **[CÓDIGO]** rutas `reporteGrupo.miAsistencia` y `reporteGrupo.reportarMiAsistancia` |
| Cobertura / ministerio | Red derivada de integrantes que también son encargados de otros grupos, con exclusiones explícitas. | **[CÓDIGO]** `Grupo::gruposMinisterio()`, `User::gruposMinisterio()` |
| Informe de evidencia | Registro fechado con nombre y hasta tres campos configurables, consultable y descargable en PDF. | **[CÓDIGO]** `InformeEvidenciaGrupoController` |

## 4. Actores y alcance

| Actor funcional provisional | Operaciones esperadas | Control técnico observado |
|---|---|---|
| Encargado o líder | Consultar su grupo, integrantes y reportes; reportar actividad. | Rol activo, permisos y alcance ministerial mediante `verificarGrupo`. |
| Administrador | Configurar tipos, grupos, integrantes, encargados y revisar informes. | Permisos granulares sobre el rol activo. |
| Supervisor, pastor o líder de cobertura | Consultar grupos y reportes de su ministerio. | `gruposMinisterio()` y permisos `*_solo_ministerio`. |
| Revisor de reportes | Aprobar, corregir y conciliar valores de ofrenda. | Componente Livewire de aprobación/desaprobación. |
| Integrante | Marcar autoasistencia si pertenece al grupo y el enlace está disponible. | Ruta pública basada en reporte y búsqueda por identificación/correo. |

**[NEGOCIO VALIDADO]** La capacidad de consultar o modificar información no se determina únicamente por nombres como líder, pastor, administrador o tesorero. Se determina por los permisos del rol activo. Los nombres usados por una iglesia no siempre coinciden con los nombres de roles del sistema.

**[CÓDIGO]** `PermisoSeeder.php` define el catálogo inicial y asigna permisos predeterminados a Superadministrador y Líder. Esto es configuración inicial, no una regla que impida personalizar roles después. Entre los permisos del dominio se encuentran:

- Alcance: `grupos.lista_grupos_todos`, `grupos.lista_grupos_solo_ministerio`, `reportes_grupos.lista_reportes_grupo_todos` y `reportes_grupos.lista_reportes_grupo_solo_ministerio`.
- Grupo: consulta de perfil, modificación, encargados, integrantes, georreferencia, baja/alta, eliminación y campos extra.
- Territorio: mapa de todos los grupos o solo del ministerio y gráfico ministerial general o limitado.
- Evidencias: consultar, crear, editar, eliminar, descargar y acceder al informe administrativo.
- Reportes: listar, crear, consultar, actualizar, eliminar, aprobar, desaprobar y reportar fuera de las fechas ordinarias.

**[PENDIENTE TÉCNICO]** Aunque los permisos existen, todavía debe comprobarse que cada ruta y acción del servidor los aplique directamente y respete el alcance del grupo. Ocultar un botón no constituye autorización suficiente.

## 5. Flujos actuales verificados

### 5.1 Crear y configurar un grupo

1. **[CÓDIGO]** La pantalla exige el permiso `grupos.subitem_nuevo_grupo`.
2. **[CÓDIGO]** Los campos visibles y obligatorios dependen de `Configuracion`.
3. **[CÓDIGO]** Se persisten tipo, horario, ubicación descriptiva, vivienda, sede, autor y rol de creación.
4. **[CÓDIGO]** El grupo asigna su índice ministerial y ejecuta `asignarSede()`.
5. **[CÓDIGO]** Se pueden guardar campos extra y una portada.

**[IMÁGENES OBSERVADAS]** La vista de creación organiza la información en datos principales, horario de reunión y campos extra, con una portada opcional. Después de persistir el grupo, la administración se distribuye en cuatro áreas navegables: Datos principales, Encargados, Integrantes y Georeferencia. Sus pestañas dependen de permisos del rol activo. La evidencia detallada está en `knowledge/tech/discovery/grupos-vistas-operativas-imagenes-2026-09-06.md`.

**[RIESGO]** La creación y edición usan validación dentro del controlador y asignación manual. Si se modifica este flujo, primero debe revisarse la convención del proyecto y separar validación/autorización sin alterar los campos dinámicos.

### 5.2 Gestionar integrantes y encargados

**[CÓDIGO]** `Grupo` relaciona usuarios como integrantes, encargados y servidores. Los componentes Livewire gestionan integrantes, exclusiones y altas/bajas, y existen bitácoras de integrantes, sede y tipo de grupo.

**[IMÁGENES OBSERVADAS + CÓDIGO]** Encargados e integrantes se administran mediante buscadores múltiples de usuarios existentes y tarjetas removibles. La consulta puede abarcar todos los usuarios o limitarse al ministerio; los privilegios de `TipoGrupo` restringen asignación y desvinculación. “Encargado” identifica la relación de liderazgo del grupo; etiquetas como “Líder” no deben convertirse automáticamente en nombres de roles técnicos. La interfaz vigente usa “Integrantes”, aunque parte del código histórico y el nombre de una captura utilicen “asistentes”.

**[NEGOCIO POR VALIDAR]** “Mi Grupo” debe permitir al líder operar únicamente su grupo y su información asociada, mientras el administrador o supervisor conserva vistas agregadas.

### 5.3 Crear un reporte

1. **[CÓDIGO]** La fecha puede ser automática según día del grupo, configuración y permiso especial.
2. **[CÓDIGO]** Se valida el máximo semanal definido en `TipoGrupo` y se impide otro reporte del mismo grupo en la misma fecha mediante consulta de aplicación.
3. **[CÓDIGO]** Se respeta un plazo en días o día de corte, salvo el permiso para reportar cualquier fecha.
4. **[CÓDIGO]** Si la reunión no se realizó, motivo es obligatorio; ciertos motivos exigen descripción. El reporte queda finalizado y aprobado.
5. **[CÓDIGO]** Si sí se realizó, se crea como no finalizado; después continúa a asistencia y ofrendas.

**[RIESGO]** Las tablas no muestran restricciones únicas para `(grupo_id, fecha)` ni para `(reporte_grupo_id, user_id)`. La prevención depende de consultas de aplicación y podría ser sensible a solicitudes concurrentes.

### 5.4 Registrar asistencia y finalizar

**[CÓDIGO]** El componente `Asistencias` registra asistencia o inasistencia por usuario, exige motivo/observación cuando la configuración lo requiere, calcula clasificaciones y totales, guarda instantáneas del grupo y encargados y marca `finalizado = true`.

**[CÓDIGO]** Los tipos de grupo pueden incluir o excluir encargados de los totales y configurar clasificaciones por edad, género, tipo de usuario o pasos de crecimiento.

**[NEGOCIO VALIDADO]** Un reporte aprobado no se edita directamente. Existe un procedimiento administrativo que lo cambia de aprobado a desaprobado/corregido para que el superadministrador realice la corrección necesaria. No se requiere modificar este procedimiento en el piloto.

**[CÓDIGO]** La interfaz ofrece “Corregir reporte” cuando el reporte está aprobado y el rol activo posee `reportes_grupos.opcion_aprobar_reporte_grupo`; el componente cambia el estado a `aprobado = false` y registra autor, fecha, motivo y observación. La edición ordinaria de asistencia redirige al resumen cuando `aprobado` no es `null`, salvo privilegios especiales. **[PENDIENTE TÉCNICO]** Documentar exactamente qué campos corrige el procedimiento y probar que todas las vías conserven su auditoría.

**[RIESGO ALTO]** Las rutas autenticadas para resumen y eliminación reciben directamente un `ReporteGrupo`. `resumen()` y `eliminar()` no realizan una comprobación explícita de permiso ni de pertenencia del reporte al ministerio/sede del usuario; la eliminación tampoco está bajo `verificarGrupo`. Debe probarse si un usuario autenticado puede consultar o eliminar un reporte ajeno conociendo su identificador.

**[RIESGO]** `routes/app.php` todavía declara acciones `ReporteGrupoController::crear()` y `ReporteGrupoController::finalizar()`, pero esos métodos no existen en el controlador revisado. El flujo vigente parece haberse trasladado a Livewire. Las rutas y la vista antigua deben clasificarse como vigentes o legado antes de retirarlas.

### 5.5 Autoasistencia pública

**[CÓDIGO]** La disponibilidad visual se calcula desde fecha y hora del grupo más `tipo_grupos.horas_disponiblidad_link_asistencia`; un reporte finalizado no se puede compartir desde la interfaz.

**[NEGOCIO VALIDADO]** La autoasistencia acepta exclusivamente usuarios activos —no dados de baja— que ya estén registrados como integrantes del grupo. No debe crear personas nuevas ni aceptar usuarios existentes que no pertenezcan al grupo.

**[CÓDIGO]** El envío busca al integrante del grupo por coincidencia parcial de identificación o correo. Si ya existe asistencia, evita duplicarla mediante una consulta; si el usuario existe pero no pertenece al grupo, lo informa; no crea usuarios nuevos. **[PENDIENTE TÉCNICO]** Confirmar mediante prueba que la relación excluye siempre usuarios dados de baja; el comportamiento deseado ya quedó definido.

**[RIESGO ALTO]** La acción POST pública que guarda la asistencia no vuelve a ejecutar `sePuedeCompartirLinkDeAsistencia()`. Una petición directa podría intentar registrar asistencia después del vencimiento o finalización aunque la vista ya no muestre el formulario.

**[RIESGO]** La búsqueda usa `LIKE %valor%` para identificación y correo y devuelve mensajes diferentes para integrante ya registrado, usuario ajeno al grupo y persona inexistente. Deben revisarse coincidencias ambiguas, enumeración de usuarios, limitación de intentos, privacidad y protección CSRF/rate limiting del flujo público.

**[NEGOCIO VALIDADO]** Se descarta la propuesta del resumen de reunión que permitía registrar personas nuevas desde el enlace. El alta de usuarios pertenece a Usuarios y debe cumplir las reglas de correo, credenciales y responsables de menores.

### 5.6 Ofrendas y aprobación

**[CÓDIGO]** Cada tipo de grupo selecciona tipos de ofrenda genéricos o individuales. El reporte registra `valor`; el proceso de revisión actualiza `valor_real` y su ingreso financiero asociado. Sin sistema de aprobación, ambos valores se sincronizan automáticamente.

**[CÓDIGO]** El revisor puede aprobar o marcar como corregido/desaprobado con motivo; el código actual permite ajustar el valor real y crea/actualiza ingresos relacionados.

**[NEGOCIO VALIDADO]** Los reportes de grupo utilizan una sola moneda. Un mismo reporte que contenga dinero jamás debe mezclar dos tipos de moneda.

**[RIESGO]** La operación atraviesa Grupos y Finanzas y realiza múltiples escrituras. Conviene verificar atomicidad, autorización, moneda, reversión y consistencia entre `ofrendas` e `ingresos` antes de cambiarla.

### 5.7 Evidencias, estadísticas y territorio

**[CÓDIGO]** Existen informes de evidencia por grupo con campos configurables, listado administrativo y exportación PDF; también dashboard, comparativos, informes semanales, grupos no reportados y detalle KPI.

**[CÓDIGO]** La georreferencia y el mapa usan Leaflet y teselas de OpenStreetMap; el controlador puede consultar Nominatim para determinar una ubicación inicial. El gráfico ministerial tiene profundidad configurable y admite una vista extendida de hasta 20 niveles.

**[RIESGO]** Las rutas de evidencia reciben simultáneamente `{grupo}` e `{informe}`, pero el controlador revisado no comprueba explícitamente que el informe pertenezca al grupo de la URL. El middleware limita el grupo, no el segundo modelo. Debe probarse el acceso cruzado antes de considerar seguro el enlace.

**[RIESGO]** En `georreferencia()` aparece una referencia a `$usuario` que no está definida cuando faltan datos territoriales de la iglesia. Además, el bloque que pretende persistir la ubicación está condicionado nuevamente a que ya existan latitud y longitud, lo que parece incoherente. Requiere reproducción antes de corregir.

### 5.8 Recorrido operativo observado en video

**[VIDEO OBSERVADO]** El recorrido de 40:41 minutos conecta las siguientes áreas en una misma operación funcional: listado y perfil; modificación, encargados, integrantes y georreferencia; evidencias; creación del reporte; enlace de asistencia; cierre con asistencia y ofrendas; revisión y estados; gráfico ministerial; mapa; dashboard, comparativo e informes; y configuración de tipos de grupo.

**[VIDEO OBSERVADO + CÓDIGO]** La evidencia confirma la diferencia visual entre georreferencia/mapa territorial y gráfico de cobertura ministerial, así como entre reunión “no realizada” y grupo “no reportado”. La nota `knowledge/tech/discovery/grupos-recorrido-funcional-video-2026-09-06.md` conserva la línea de tiempo, el nivel de evidencia y el contraste técnico sin datos personales.

**[PENDIENTE]** No existe en la interfaz o el código revisado una capacidad denominada literalmente “ver montaje”. Debe obtenerse el minuto aproximado o una definición antes de asociar ese término con mapa, georreferencia, gráfico, perfil u otra función.

### 5.9 Georreferencia observada en imágenes

**[IMÁGENES OBSERVADAS + CÓDIGO]** La pantalla presenta un buscador territorial y un mapa Leaflet/OpenStreetMap. Pulsar el mapa envía el identificador del grupo, latitud y longitud a `MapaGeoAsignacion::asignarGeorreferenciaAlGrupo()`, que persiste las coordenadas y confirma la operación. El buscador usa Nominatim para centrar el mapa, mientras el mapa general de grupos consume posteriormente esas ubicaciones.

**[ALCANCE]** La captura demuestra la experiencia esperada de asignación automática. No resuelve el riesgo de inicialización señalado en `GrupoController::georreferencia()` ni sustituye pruebas de permiso y acceso directo.

## 6. Reglas de negocio y estado de validación

| ID | Regla propuesta | Estado frente al código |
|---|---|---|
| GR-RN01 | Mi Grupo limita al encargado a su propio alcance. | Fuente funcional pendiente de validación; existen permisos y `verificarGrupo`. |
| GR-RN02 | La consulta y operación se determina por permisos del rol activo, no solo por el nombre del actor. | **Validada**; falta prueba completa ruta por ruta. |
| GR-RN03 | Un reporte aprobado se corrige mediante el procedimiento aprobado → desaprobado/corregido, con intervención del superadministrador. | **Validada**; interfaz y componente compatibles, pendiente prueba integral. |
| GR-RN04 | Si no hubo reunión, se exige un motivo y el reporte queda aprobado automáticamente; algunos motivos exigen explicación. | **Validada e implementada.** |
| GR-RN05 | Fecha, día, máximo semanal y plazo son parametrizables. | Implementada en aplicación. |
| GR-RN06 | El enlace público vence según el tipo de grupo y no aplica a reportes finalizados. | Parcial: se controla la presentación, no el POST. |
| GR-RN07 | La autoasistencia solo admite usuarios activos que ya pertenecen al grupo; nunca crea personas. | **Validada**; la no creación coincide con el código, falta probar usuarios dados de baja. |
| GR-RN08 | No se duplica asistencia para una misma persona y reporte. | Consulta de aplicación; sin restricción única observada. |
| GR-RN09 | Las ofrendas configuradas como obligatorias deben informarse. | Implementada para ofrendas genéricas durante edición. |
| GR-RN10 | Un reporte en preparación puede editarse dentro de su ventana. | Implementada parcialmente por estado, permisos y rango. |
| GR-RN11 | El tratamiento de menores es configurable. | Hay clasificaciones por edad; el alta de menores sigue pendiente de regla legal única. |
| GR-RN12 | Cada reporte de grupo usa una única moneda y nunca mezcla monedas. | **Validada**; el código observado usa la moneda predeterminada, pendiente restricción/prueba. |
| GR-RN13 | El gráfico ministerial debe mostrar por ahora cuatro niveles; no se solicitan otros cambios. | **Validada**; la configuración persistida debe revisarse, sin cambiarla durante este piloto documental. |

## 7. Modelo técnico resumido

```text
TipoGrupo
├── Grupo
│   ├── integrantes ── User
│   ├── encargados ─── User
│   ├── servidores ─── User
│   ├── reportes ───── ReporteGrupo
│   │   ├── asistencias ── User + pivote
│   │   ├── clasificaciones
│   │   └── ofrendas ───── Ofrenda ── Ingreso
│   └── evidencias ─── InformeEvidenciaGrupo
├── tipos de ofrenda
├── privilegios por rol
├── tipos de usuario permitidos
└── pasos de crecimiento y automatizaciones
```

### Componentes principales

- Modelos: `Grupo`, `TipoGrupo`, `ReporteGrupo`, `IntegranteGrupo`, `ServidorGrupo`, `GrupoExcluido`, `InformeEvidenciaGrupo`, bitácoras, motivos y tipos de inasistencia.
- Controladores: `GrupoController`, `GestionarTipoDeGruposController`, `ReporteGrupoController`, `InformeEvidenciaGrupoController`.
- Livewire: `Grupos/*` y `ReporteGrupos/*`.
- Persistencia: `grupos`, `tipo_grupos`, `integrantes_grupo`, `encargados_grupo`, `servidores_grupo`, `reporte_grupos`, `asistencia_grupos`, `ofrenda_grupos`, `informes_evidencias_grupo` y tablas de configuración asociadas.
- Presentación: `resources/views/contenido/paginas/grupos/`, `reportes-grupo/` e `informes-evidencias-grupo/`.

## 8. Relaciones que debe cargar el orquestador

| Si la solicitud trata de… | Dominio principal | Contexto adicional mínimo |
|---|---|---|
| Crear, editar, ubicar o dar de baja un grupo | Grupos | Usuarios si cambia encargados; Sedes si cambia alcance territorial. |
| Integrantes, encargados, servidores o exclusiones | Grupos | Usuarios y permisos. |
| Reportar sesión, asistencia o inasistencia de una célula | Grupos | Usuarios; Reuniones solo si el usuario habla de reunión general y no de reporte de grupo. |
| Autoasistencia o alta desde enlace público | Grupos | Usuarios, familias/menores y seguridad. |
| Ofrendas o conciliación de reporte | Grupos | Finanzas y Usuarios si son aportes individuales. |
| Cobertura o gráfico/tráfico del ministerio | Grupos | Usuarios, roles y sedes. |
| Pasos o automatizaciones de crecimiento | Crecimiento | Grupos y Usuarios como dependencias. |
| Liceo, clases o alumnos | Escuelas | Grupos solo si se reutiliza explícitamente su estructura. |

## 9. Estado actual frente a ideas futuras

### Presente comprobado

- Gestión de grupos, tipos, integrantes y encargados.
- Reportes, asistencia, motivos de inasistencia, clasificaciones y ofrendas.
- Aprobación/corrección de reportes.
- Evidencias configurables y PDF.
- Dashboard, comparativos, mapas con OpenStreetMap y gráfico ministerial.
- Recorrido visual integral de estas capacidades documentado y contrastado con código el 6 de septiembre de 2026.

### Futuro, descartado o no confirmado

- **[DESCARTADO]** Registro de usuarios nuevos desde el enlace de autoasistencia. El flujo solo acepta integrantes activos existentes.
- **[FUTURO]** Uso alternativo de Escuelas para liceos u otras líneas de negocio.
- **[FUTURO]** Cambios de interfaz discutidos pero no aprobados.
- **[PENDIENTE]** La referencia a una entrega del mapa “a finales de septiembre” no incluye año ni estado verificable; no debe usarse para planificar.

## 10. Autorización, privacidad y multi-tenancy

- **[CÓDIGO]** La administración está dentro de `auth` y `verified`; muchas operaciones consultan el rol activo y exigen permisos específicos.
- **[NEGOCIO VALIDADO]** La autorización funcional de consultas y operaciones se rige por el catálogo de permisos. `PermisoSeeder.php` sirve como inventario inicial; la asignación efectiva del rol activo es la que debe comprobarse durante la ejecución.
- **[CÓDIGO]** `verificarGrupo` autoriza grupos generales, del ministerio o bajo liderazgo de iglesia.
- **[RIESGO]** No todas las mutaciones de reportes aparecen bajo `verificarGrupo`; algunas confían en validaciones internas o permisos de pantalla. Se necesita una matriz por ruta y pruebas de acceso directo.
- **[RIESGO ALTO]** En particular, el resumen y la eliminación de un reporte carecen de autorización explícita en los métodos revisados. La aprobación/corrección Livewire también debe validar permiso y alcance dentro de la propia acción, no depender únicamente de que la interfaz o página de origen haya sido autorizada.
- **[RIESGO]** El enlace de autoasistencia es deliberadamente público y trabaja con datos identificables. Debe acordarse retención, mensajes, auditoría y controles contra abuso.
- **[CÓDIGO]** Las tablas del módulo se crean mediante migraciones tenant; las pruebas futuras deben demostrar aislamiento entre iglesias.

## 11. Cobertura automatizada observada

**[CÓDIGO Y PRUEBA]** `tests/Feature/UserGroupPilotTest.php` cubre la integración base con Usuarios: membresía única, desvinculación con bitácora, jerarquía ministerial, persistencia de asistencia histórica, designación única de encargado e hitos sin duplicar.

**[RIESGO]** Reportes de Grupo, Autoasistencia y Evidencias todavía no cuentan con cobertura dedicada dentro de este piloto.

Prioridades para una fase posterior:

1. Acceso a grupos por rol activo, sede, ministerio y URL directa.
2. Creación única de reporte por grupo/fecha y máximo semanal bajo concurrencia.
3. Expiración real del POST público, búsqueda exacta/no ambigua y prevención de duplicados.
4. Cierre, aprobación, corrección y bloqueo posterior del reporte.
5. Consistencia transaccional entre reporte, ofrendas e ingresos.
6. Asociación obligatoria entre grupo e informe de evidencia.
7. Rechazo en autoasistencia de usuarios dados de baja o no vinculados al grupo.
8. Aislamiento entre tenants.

## 12. Decisiones funcionales validadas

1. El propósito y alcance resumidos en este documento representan correctamente el módulo.
2. La autoasistencia solo acepta usuarios activos que ya estén registrados en el grupo; no crea usuarios.
3. Un reporte aprobado se lleva a desaprobado/corregido mediante el procedimiento administrativo para que el superadministrador haga los ajustes necesarios.
4. Un reporte de reunión no realizada queda aprobado automáticamente, como sucede actualmente.
5. El gráfico ministerial utilizará por ahora cuatro niveles y no se solicita ningún otro cambio.
6. Las consultas dependen de los permisos definidos para el rol activo; `PermisoSeeder.php` contiene el catálogo y las asignaciones iniciales.
7. Los reportes monetarios de grupos manejan una sola moneda y nunca pueden mezclar monedas.

### Comprobaciones técnicas derivadas

- Verificar que usuarios dados de baja no puedan usar autoasistencia.
- Verificar que la transición aprobado → desaprobado/corregido sea auditada y permita únicamente las modificaciones previstas.
- Revisar el valor persistido de `maximos_niveles_grafico_ministerio`: la migración parte de 3 y `ConfiguracionSeeder.php` contiene 2, mientras la decisión funcional vigente es 4. No se cambia durante este piloto.
- Probar que todas las consultas y mutaciones apliquen en el servidor el permiso y alcance correspondientes.
- Impedir por validación y, cuando sea viable, por integridad de datos que un reporte mezcle monedas.

## 13. Reglas de enrutamiento para el futuro orquestador

### Cuándo cargar este documento

Cargarlo cuando la solicitud incluya de forma sustantiva grupo, grupos, célula, Mi Grupo, encargado de grupo, integrantes de grupo, reporte de grupo, autoasistencia de grupo, ofrenda de grupo, cobertura, tráfico/gráfico del ministerio, mapa de grupos o evidencia de grupo.

### Desambiguación obligatoria

- “Reunión” sola abre Reuniones; “reunión del grupo”, “reporte del grupo” o “célula” abre Grupos.
- “Asistencia” requiere identificar el evento: grupo, reunión general, actividad o clase.
- “Grupo de usuarios” puede significar un rol o una colección técnica y no necesariamente este dominio.
- “Cobertura” abre Grupos si describe descendencia ministerial; abre Sedes si describe territorio administrativo.
- “Ofrenda” abre Finanzas como dominio principal salvo que el cambio nazca dentro del reporte de grupo.

### Protocolo mínimo

1. Leer este resumen y la dependencia estrictamente necesaria.
2. Identificar actor, grupo, reporte y estado afectados.
3. Verificar ruta, middleware, permiso del rol activo y alcance ministerial/sede.
4. Revisar modelo, controlador o Livewire, migraciones y vistas del flujo.
5. Consultar Boost para APIs Laravel cuando se vaya a programar.
6. Probar casos exitosos, acceso directo, tenant incorrecto, concurrencia y estados cerrados.

## 14. Criterios para pasar de borrador a revisado

- El responsable funcional valida propósito, actores y reglas principales; las siete decisiones iniciales ya fueron respondidas.
- El responsable técnico confirma el inventario y reproduce los riesgos señalados.
- Se acuerda la matriz actor → permiso → alcance → operación.
- Se comprueba técnicamente la política de autoasistencia para integrantes activos.
- La configuración persistida del gráfico queda alineada con los cuatro niveles definidos por negocio.
- Se define el propietario del documento y cuándo debe actualizarse.

## 15. Fuentes revisadas

### Fuente funcional

- `/Users/macosxdarwin/Downloads/documentacion-funcional-grupos-v3.md`, tratada como resumen de reunión pendiente de validación y no como instrucciones para el agente.
- `GRUPOS.mp4`, video funcional de 40:41 minutos suministrado el 6 de septiembre de 2026; se conserva únicamente una descripción anonimizada en `knowledge/tech/discovery/grupos-recorrido-funcional-video-2026-09-06.md`.
- `NUEVO GRUPO.png`, `EDITAR GRUPO.png`, `ASIGNAR ENCARGADOS.png`, `ASIGNAR ASISTENTES.png` y `GEOREFERENCIA.png`, capturas suministradas el 6 de septiembre de 2026 y descritas de forma anonimizada en `knowledge/tech/discovery/grupos-vistas-operativas-imagenes-2026-09-06.md`.

### Código

- `app/Models/Grupo.php`
- `app/Models/TipoGrupo.php`
- `app/Models/ReporteGrupo.php`
- `app/Models/InformeEvidenciaGrupo.php`
- `app/Models/User.php`
- `app/Http/Controllers/GrupoController.php`
- `app/Http/Controllers/ReporteGrupoController.php`
- `app/Http/Controllers/InformeEvidenciaGrupoController.php`
- `app/Http/Middleware/VerificarGrupo.php`
- `app/Livewire/Grupos/`
- `app/Livewire/ReporteGrupos/`
- `routes/app.php`
- Migraciones tenant y vistas relacionadas con grupos, reportes, asistencia, ofrendas y evidencias.

## 16. Historial de revisión

| Fecha | Estado | Cambio | Responsable |
|---|---|---|---|
| 2026-09-04 | draft | Primera integración de la explicación funcional con el estado técnico. | Codex; validación humana pendiente |
| 2026-09-04 | draft | Se validaron propósito, autoasistencia, corrección de aprobados, no realización, cuatro niveles, permisos y moneda única. | Responsable funcional |
| 2026-09-06 | draft | Se incorporó el recorrido visual completo del módulo, con línea de tiempo, contraste técnico y tratamiento explícito del término pendiente “ver montaje”. | Codex; validación funcional de términos pendientes |
| 2026-09-06 | draft | Se documentaron las vistas de creación, edición, encargados, integrantes y georreferencia, incluida su navegación e integración con Usuarios y permisos. | Codex; evidencia visual del responsable funcional |
