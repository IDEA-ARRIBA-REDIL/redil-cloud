---
type: discovery-evidence
id: DISC-GRUPOS-VIDEO-001
title: Recorrido funcional del módulo de Grupos
status: reviewed-against-code
domain: grupos
related_domains:
  - usuarios
  - roles-permisos
source_type: video
source_file: GRUPOS.mp4
source_date: 2026-09-06
duration_seconds: 2440.792
privacy: description-only-no-personal-data-copied
reviewed_at: 2026-09-06
---

# Evidencia: recorrido funcional del módulo de Grupos

## Alcance y calidad de la evidencia

El video suministrado recorre durante 40:41 minutos la operación y configuración del módulo de Grupos. Se realizó un barrido visual de toda la duración y se contrastaron las pantallas con rutas, modelos, controladores, vistas y componentes Livewire del repositorio.

La grabación es especialmente útil para documentar navegación, vocabulario visible, secuencia de tareas, estados y relación entre pantallas. El código permite confirmar persistencia, permisos y reglas que no se demuestran solo con la interfaz. La narración no fue transcrita literalmente; por ello, una afirmación que dependa exclusivamente de la voz permanece pendiente de confirmación textual.

No se copiaron a esta nota nombres, rostros, correos, teléfonos, ubicaciones precisas ni otros datos visibles de personas o iglesias.

## Cuánto aporta a la documentación

| Área | Aporte del video | Resultado documental |
|---|---|---|
| Navegación y operaciones sobre un grupo | Alto | Demuestra listado, perfil y menú de acciones. |
| Reporte de una reunión | Alto | Demuestra creación, enlace de asistencia, cierre, ofrendas y revisión. |
| Evidencias | Alto | Demuestra creación y consulta administrativa. |
| Cobertura y territorio | Alto | Demuestra gráfico ministerial, detalle de nodos y mapa. |
| Analítica e informes | Alto | Demuestra dashboard, comparación de períodos e informes especializados. |
| Configuración de tipos de grupo | Medio/alto | Demuestra que el comportamiento se parametriza, pero no permite leer con precisión todos los campos. |
| Autorización y seguridad | Bajo por sí solo | La visibilidad de botones no demuestra protección del servidor; se requiere código y pruebas. |
| Reglas explicadas únicamente por voz | Pendiente | Requieren transcripción o confirmación del responsable funcional. |

## Línea de tiempo funcional

| Tiempo aproximado | Evidencia observada |
|---|---|
| 00:00–02:00 | Introducción y entrada al sistema durante una videollamada. |
| 02:00–06:00 | Perfil de grupo, estadísticas, información básica y encargados. |
| 06:00–10:00 | Dashboard general, listado de grupos, filtros, tarjetas y menú de acciones. |
| 10:00–13:30 | Creación y administración de informes de evidencia. |
| 13:30–17:30 | Creación de reporte, enlace público de asistencia, confirmación de asistentes y ofrendas. |
| 17:30–21:30 | Resumen, revisión/aprobación y listado de reportes con estados. |
| 21:30–25:00 | Gráfico del ministerio, jerarquía de cobertura y detalle de personas o grupos. |
| 25:00–27:00 | Mapa y georreferencia de grupos. |
| 27:00–28:30 | Consulta administrativa de evidencias. |
| 28:30–33:30 | Dashboard de grupos, indicadores, gráficos y comparativo de períodos. |
| 33:30–36:00 | Catálogo de informes, asistencia semanal y configuración de tipos de grupo. |
| 36:00–38:00 | Grupos no reportados y reuniones no realizadas. |
| 38:00–40:41 | Estadísticas de pasos de crecimiento y cobertura desde el perfil del grupo. |

Los tiempos son índices aproximados para localizar la evidencia; no constituyen contratos de interfaz.

## Flujos observados y contraste técnico

### 1. Listado, perfil y administración de grupos

**[VIDEO OBSERVADO]** El listado usa tarjetas y filtros. Cada grupo puede mostrar un menú con acciones visibles: Perfil, Modificar, Gestionar encargados, Gestionar integrantes, Gestionar Georeferencia, Excluir grupo, Ver informes de evidencia, Dar de baja y Eliminar.

**[CÓDIGO]** `GrupoController::listar()` y `resources/views/contenido/paginas/grupos/listar.blade.php` construyen el listado y condicionan las acciones mediante permisos. Las rutas bajo `verificarGrupo` cubren perfil, modificación, encargados, integrantes y georreferencia.

**[VIDEO OBSERVADO + CÓDIGO]** El perfil reúne información general, encargados, integrantes y estadísticas del grupo o su cobertura mediante vistas y acciones separadas.

### 2. Integración con Usuarios y Roles/Permisos

**[VIDEO OBSERVADO]** Encargados e integrantes son personas existentes seleccionadas para un grupo. La jerarquía visible conecta grupos mediante integrantes que también lideran otros grupos.

**[CÓDIGO]** `Grupo::asistentes()`, `Grupo::encargados()`, `User::gruposDondeAsiste()` y `User::gruposEncargados()` implementan relaciones muchos a muchos. `Grupo::gruposMinisterio()` y `User::lideres()` recorren la red, respetando exclusiones.

**[CÓDIGO]** Las acciones visibles dependen de permisos del rol activo y el middleware `verificarGrupo` limita varias rutas por alcance general, ministerial o de liderazgo. Esto no reemplaza la revisión ruta por ruta de autorización en el servidor.

### 3. Informes de evidencia

**[VIDEO OBSERVADO]** Se muestra un formulario de evidencia asociado a un grupo y posteriormente una vista administrativa para consultar evidencias de grupos supervisados.

**[CÓDIGO]** `InformeEvidenciaGrupoController` y las rutas `grupo.informeEvidencia.*` permiten crear, listar, ver, editar, eliminar y descargar PDF. La configuración global habilita hasta tres campos adicionales y define sus etiquetas y obligatoriedad.

### 4. Crear y finalizar un reporte

**[VIDEO OBSERVADO]** El reporte parte del grupo y de una fecha. El recorrido distingue una reunión realizada de una no realizada; cuando continúa, permite gestionar asistencia, revisar el resumen y finalizar.

**[CÓDIGO]** `ModalNuevoReporte` aplica fecha, máximo semanal, plazo y motivo de no realización. `Asistencias` registra presencia o inasistencia, clasificaciones, instantáneas del grupo y encargados, ofrendas y estado finalizado.

### 5. Enlace público de asistencia

**[VIDEO OBSERVADO]** Durante un reporte abierto se muestra un “Link de asistencia” para que una persona registre su presencia y el operador confirma posteriormente el listado.

**[CÓDIGO]** Las rutas públicas `reporteGrupo.miAsistencia` y `reporteGrupo.reportarMiAsistancia` buscan una persona por identificación o correo dentro de los integrantes del grupo y evitan registrar la misma asistencia nuevamente mediante consulta de aplicación.

**[REGLA YA VALIDADA]** Este enlace solo debe aceptar integrantes activos existentes; no crea usuarios nuevos. La expiración real del envío directo sigue siendo un riesgo documentado en el estado actual.

### 6. Ofrendas, revisión y estados

**[VIDEO OBSERVADO]** El resumen presenta asistencia y valores de ofrenda. La administración permite revisar el reporte y el listado distingue estados de su procesamiento.

**[CÓDIGO]** Los tipos de ofrenda se configuran por `TipoGrupo`; el componente de aprobación/desaprobación gestiona el valor real, el estado, el motivo y el ingreso financiero relacionado.

**[REGLA YA VALIDADA]** Cada reporte monetario usa una sola moneda. Un reporte aprobado se corrige mediante el procedimiento administrativo que lo devuelve a un estado corregible.

### 7. Gráfico del ministerio y mapa

**[VIDEO OBSERVADO]** El gráfico representa la cobertura como una jerarquía navegable y permite consultar detalles de sus nodos. El mapa presenta la ubicación de grupos.

**[CÓDIGO]** `GrupoController::graficoDelMinisterio()` usa la jerarquía derivada de usuarios y grupos. `mapaDeGrupos()` y la vista asociada usan Leaflet/OpenStreetMap y ofrecen acceso al perfil del grupo y a Google Maps.

**[REGLA YA VALIDADA]** El gráfico ministerial debe mostrar por ahora cuatro niveles; la configuración persistida continúa pendiente de alineación y no se modifica en este descubrimiento.

### 8. Dashboard, comparativo e informes

**[VIDEO OBSERVADO]** El dashboard presenta cantidades, porcentajes y gráficos de actividad por grupo, tipo, sede u otros cortes. El comparativo enfrenta períodos. El catálogo de informes incluye asistencia semanal y grupos no reportados/no realizados.

**[CÓDIGO]** `GrupoController::dashboard()`, `comparativo()` y `detalleKpi()` respaldan estas vistas. `InformesController` implementa informes de grupos no reportados y asistencia semanal, con exportación.

**[DISTINCIÓN FUNCIONAL]** “No realizado” es un reporte explícito con motivo; “no reportado” es la ausencia de un reporte esperado. El video muestra ambos conceptos en áreas de informe distintas.

### 9. Configuración de tipos de grupo

**[VIDEO OBSERVADO]** Se muestra la edición de un tipo de grupo con múltiples opciones que alteran su operación.

**[CÓDIGO]** `TipoGrupo` y `GestionarTipoDeGruposController` parametrizan seguimiento, servidores, reportes máximos, inasistencia, autoasistencia, ofrendas, textos de cierre, visibilidad territorial, tipos de usuario, roles y automatizaciones de crecimiento.

## Sobre “ver montaje”

No se encontró una etiqueta, ruta, permiso, vista o método denominado “montaje” en el dominio Grupos. Tampoco apareció esa opción en el menú visual revisado. Por tanto, esta expresión no se equipara automáticamente con “Mapa de grupos”, “Gestionar Georeferencia”, “Gráfico del ministerio” ni “Perfil”.

Para incorporarla correctamente hace falta el minuto aproximado del video o una breve definición funcional. Hasta entonces queda como vocabulario pendiente y no como capacidad del sistema.

## Reglas que esta evidencia permite afirmar

- Grupos reúne administración operativa, membresía, liderazgo, reportes, evidencias, territorio y analítica.
- Usuarios aporta las personas que actúan como integrantes, encargados y asistentes; Roles/Permisos determina qué operaciones puede ejecutar el actor activo.
- El reporte es un flujo compuesto: creación, asistencia o no realización, ofrendas, cierre, revisión y posible corrección.
- El gráfico ministerial no es una jerarquía independiente: se deriva de la relación entre pertenencia de usuarios y liderazgo de grupos.
- La georreferencia alimenta la vista territorial, mientras que el gráfico representa cobertura organizacional.
- El tipo de grupo es configuración central y modifica varios comportamientos del reporte y de la membresía.

## Lo que el video no prueba por sí solo

- Que todas las rutas estén protegidas aunque el botón esté oculto.
- Integridad bajo solicitudes concurrentes o aislamiento entre tenants.
- Expiración del POST público de autoasistencia.
- Asociación segura entre el grupo de la URL y cada evidencia consultada.
- Semántica exacta de cualquier explicación audible que no tenga confirmación textual.

## Archivos contrastados

- `routes/app.php`
- `app/Models/Grupo.php`
- `app/Models/ReporteGrupo.php`
- `app/Models/TipoGrupo.php`
- `app/Models/User.php`
- `app/Http/Controllers/GrupoController.php`
- `app/Http/Controllers/ReporteGrupoController.php`
- `app/Http/Controllers/InformeEvidenciaGrupoController.php`
- `app/Http/Controllers/GestionarTipoDeGruposController.php`
- `app/Http/Controllers/InformesController.php`
- `app/Livewire/Grupos/`
- `app/Livewire/ReporteGrupos/`
- `resources/views/contenido/paginas/grupos/`
- `resources/views/contenido/paginas/reportes-grupo/`
- `resources/views/contenido/paginas/informes-evidencias-grupo/`

## Uso futuro de esta evidencia

Esta nota aumenta el contexto del piloto, pero no autoriza correcciones funcionales. Cada cambio de comportamiento debe abrir un WI de implementación propio, recuperar este contexto y demostrar el resultado mediante pruebas y validación funcional.

## Evidencia visual complementaria

Las capturas posteriores de Nuevo grupo, Editar grupo, Encargados, Integrantes y Georeferencia se documentan en `knowledge/tech/discovery/grupos-vistas-operativas-imagenes-2026-09-06.md`. Esa nota precisa los campos visibles, la navegación posterior a la creación y la integración con Usuarios y Roles/Permisos.
