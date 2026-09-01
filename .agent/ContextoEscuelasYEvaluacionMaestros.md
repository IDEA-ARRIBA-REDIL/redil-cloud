# Contexto del Agente: Módulo de Escuelas + sistemaEvaluativoMaestros
## Referencia rápida para el agente — Mayo 2026

---

## PARTE 1: Estructura General de Escuelas

### Jerarquía de Entidades Académicas

```
Escuela
  └── NivelEscuela (Grado)       tabla: niveles_escuelas
        └── Materia               tabla: materias
  └── Periodo                    tabla: periodos
        └── MateriaPeriodo        tabla: materia_periodo
              └── HorarioMateriaPeriodo  tabla: horarios_materia_periodo
                    └── Maestro (pivot)  tabla: horario_materia_periodo_maestro
                    └── Matricula (alumno) tabla: matriculas
                    └── ItemCorteMateriaPeriodo tabla: item_corte_materia_periodo
  └── CorteEscuela (plantilla)   tabla: cortes_escuela
        └── CortePeriodo (instancia) tabla: cortes_periodo
```

---

## PARTE 2: Modelos y sus Relaciones

### `Escuela` [escuelas]
```
id, nombre, descripcion, tipo_matricula, diploma_id, habilitada_consolidacion

Relaciones:
- cortesEscuela() → HasMany(CorteEscuela)
- periodos()      → HasMany(Periodo)
- materias()      → HasMany(Materia)
- niveles()       → HasMany(NivelEscuela, 'escuela_id')
- nivelesAgrupacion() → HasMany(NivelAgrupacion)
- matriculas()    → HasMany(Matricula, 'escuela_id')
```

---

### `NivelEscuela` (Grado) [niveles_escuelas]
```
id, nombre, escuela_id, tipo_usuario_inicial_id, tipo_usuario_objetivo_id
+ 17 campos de configuración de paridad con Materia:
  habilitar_asistencias, habilitar_calificaciones, habilitar_inasistencias,
  habilitar_alerta_inasistencias, asistencias_minimas, asistencias_minima_alerta,
  limite_reporte_asistencias, dia_limite_reporte, tiene_dia_limite,
  cantidad_limite_reportes_semana, dias_plazo_reporte, habilitar_traslado,
  caracter_obligatorio, portada...

Relaciones:
- escuela()           → BelongsTo(Escuela)
- materias()          → HasMany(Materia, 'nivel_id')
- prerrequisitos()    → BelongsToMany(NivelEscuela, 'nivel_escuela_prerrequisitos',
                         'nivel_escuela_id_inicial', 'nivel_escuela_requerido_id')
                         withPivot('escuela_id')
- pasosCrecimiento()  → BelongsToMany(PasoCrecimiento, 'nivel_paso_crecimiento')
- procesosPrerrequisito() → BelongsToMany(PasoCrecimiento, 'nivel_proceso_prerrequisito')
- tareasRequisito()   → HasMany(NivelTareaRequisito, 'nivel_id')
- tareasCulminadas()  → HasMany(NivelTareaCulminada, 'nivel_id')
- matriculas()        → HasMany(MatriculaNivel, 'nivel_escuela_id')
```

**Convención UI**: Siempre llamar "Grado" en la interfaz. Internamente es "Nivel".
**Paridad**: Los 17 campos de configuración deben ser idénticos entre Materia y NivelEscuela.
**Herencia**: Si una Materia tiene `nivel_id`, hereda la configuración del nivel a través de accessors en el modelo.

---

### `Materia` [materias] (SoftDeletes)
```
id, nombre, nivel_id (FK→NivelEscuela), escuela_id,
habilitar_calificaciones, habilitar_asistencias, asistencias_minimas,
descripcion, habilitar_alerta_inasistencias, habilitar_traslado,
caracter_obligatorio, portada, asistencias_minima_alerta,
habilitar_inasistencias, tipo_usuario_objetivo_id, tipo_usuario_inicial_id

Relaciones:
- nivel()              → BelongsTo(NivelEscuela, 'nivel_id')
- escuela()            → BelongsTo(Escuela)
- tipoUsuarioObjetivo() → BelongsTo(TipoUsuario, 'tipo_usuario_objetivo_id')
- tipoUsuarioInicial() → BelongsTo(TipoUsuario, 'tipo_usuario_inicial_id')
- materiasPeriodo()    → HasMany(MateriaPeriodo)
- itemPlantillas()     → HasMany(ItemPlantilla)
- prerrequisitosMaterias() → BelongsToMany(Materia, 'materia_prerrequisito',
                              'materia_id', 'materia_prerrequisito_id')
- procesosPrerrequisito() → BelongsToMany(PasoCrecimiento, 'materia_proceso_prerrequisito')
- pasosCrecimiento()   → BelongsToMany(PasoCrecimiento, 'materia_paso_crecimiento')
- tareasRequisito()    → HasMany(MateriaTareaRequisito)
- tareasCulminadas()   → HasMany(MateriaTareaCulminada)

Accessors (heredan del NivelEscuela si tiene nivel_id):
- getHabilitarAsistenciasAttribute()
- getHabilitarCalificacionesAttribute()
- getHabilitarInasistenciasAttribute()
- getHabilitarTrasladoAttribute()
- getCaracterObligatorioAttribute()
- getAsistenciasMinimasAttribute()
```

---

### `Periodo` [periodos]
```
id, nombre, escuela_id, fecha_inicio, fecha_fin, fecha_inicio_matricula,
fecha_fin_matricula, estado (bool), sistema_calificaciones_id,
fecha_maxima_entrega_notas, tiene_pagos

Relaciones:
- escuela()            → BelongsTo(Escuela)
- cortesPeriodo()      → HasMany(CortePeriodo)
- materiasPeriodo()    → HasMany(MateriaPeriodo)
- sedes()              → BelongsToMany(Sede, 'sedes_periodo')
- sistemaCalificaciones() → BelongsTo(SistemaCalificacion, 'sistema_calificaciones_id')
- matriculas()         → HasMany(Matricula, 'periodo_id')
- nivelesPeriodo()     → HasMany(NivelPeriodo)
```

---

### `CorteEscuela` (plantilla) [cortes_escuela]
```
id, escuela_id, nombre, orden

Relaciones:
- escuela()        → BelongsTo(Escuela)
- cortesPeriodo()  → HasMany(CortePeriodo)
- itemPlantillas() → HasMany(ItemPlantilla)
```

---

### `CortePeriodo` (instancia por periodo) [cortes_periodo]
```
id, periodo_id, corte_escuela_id, fecha_inicio, fecha_fin, porcentaje (decimal), cerrado (bool)

Relaciones:
- periodo()       → BelongsTo(Periodo)
- corteEscuela()  → BelongsTo(CorteEscuela)
- itemInstancias() → HasMany(ItemCorteMateriaPeriodo)
```

---

### `MateriaPeriodo` [materia_periodo]
```
id, materia_id, periodo_id, maestro_id (legacy), habilitar_calificaciones,
habilitar_asistencias, asistencias_minimas, auto_matricula, estado_auto_matricula,
finalizado, descripcion, cantidad_inasistencias_alerta, habilitar_alerta_inasistencias,
habilitar_traslado, nivel_id

Relaciones:
- materia()               → BelongsTo(Materia)
- periodo()               → BelongsTo(Periodo)
- nivel()                 → BelongsTo(NivelEscuela, 'nivel_id')
- itemInstancias()        → HasMany(ItemCorteMateriaPeriodo)  [alias: itemsCorte()]
- horariosMateriaPeriodo() → HasMany(HorarioMateriaPeriodo)
- actividadCategoria()    → HasMany(ActividadCategoria, 'materia_periodo_id')
```

---

### `HorarioMateriaPeriodo` [horarios_materia_periodo]
```
id, materia_periodo_id, horario_base_id, habilitado, fecha_inicio_habilitado,
fecha_fin_habilitado, cupos_disponibles

Relaciones:
- materiaPeriodo()            → BelongsTo(MateriaPeriodo)
- horarioBase()               → BelongsTo(HorarioBase)
- maestros()                  → BelongsToMany(Maestro, 'horario_materia_periodo_maestro',
                                 'horario_materia_periodo_id', 'maestro_id') withTimestamps()
- itemsEvaluacion()           → HasMany(ItemCorteMateriaPeriodo, 'horario_materia_periodo_id')
- instanciasMatriculaAlumnos() → HasMany(MatriculaHorarioMateriaPeriodo)
- alumnosMatriculados()       → BelongsToMany(User, 'matriculas', 'horario_materia_periodo_id', 'user_id')
                                 withPivot(['id', 'periodo_id', 'pago_id', ...])
                                 as('detalles_matricula')
- reportesAsistencia()        → HasMany(ReporteAsistenciaClase, 'horario_materia_periodo_id')
- matriculasDeAlumnos()       → HasMany(Matricula, 'horario_materia_periodo_id')

Accessors:
- getCapacidadDefinidaAttribute() → retorna capacidad del HorarioBase
- getSedeAttribute()              → retorna la sede vía HorarioBase→Aula→Sede
```

---

### `Maestro` [maestros]
```
id, user_id (unique), activo (bool), descripcion

Relaciones:
- user()                  → BelongsTo(User)
- horariosMateriaPeriodo() → BelongsToMany(HorarioMateriaPeriodo,
                              'horario_materia_periodo_maestro',
                              'maestro_id', 'horario_materia_periodo_id') withTimestamps()
```

**Roles**: El rol asignado al maestro debe tener `es_maestro = true` en la tabla `roles`.
**Gestión**: `MaestroController::guardar` asigna el rol al usuario y crea el registro en `maestros`.

---

### `Matricula` [matriculas]
```
id, user_id, periodo_id, horario_materia_periodo_id, referencia_pago,
valor_a_pagar, valor_pagado, fecha_pago, tipo_pago_id, estado_pago_id,
fecha_matricula, observacion, material_sede_id, escuela_id, sede_id,
trasladado, fecha_bloqueo, bloqueado

Relaciones:
- user()                 → BelongsTo(User)
- periodo()              → BelongsTo(Periodo)
- horarioMateriaPeriodo() → BelongsTo(HorarioMateriaPeriodo)
- tipoPago()             → BelongsTo(TipoPago)
- estadoPago()           → BelongsTo(EstadoPago)
- escuela()              → BelongsTo(Escuela)
- sede()                 → BelongsTo(Sede)
- trasladosLog()         → HasMany(TrasladoMatriculaLog)
- estadoAcademicoClase() → HasOne(MatriculaHorarioMateriaPeriodo, 'matricula_id')
```

---

## PARTE 3: Sistema de Calificaciones (Evaluación Académica)

### Flujo de Calificaciones

```
Materia
  └── ItemPlantilla            [item_plantillas]           ← Definición base por materia+corte
        └── ItemCorteMateriaPeriodo  [item_corte_materia_periodo]  ← Instancia por HMP
              └── AlumnoRespuestaItem [alumno_respuesta_items]      ← Respuesta/nota del alumno
                    └── Calificaciones  [calificaciones]            ← Nota final sistema
```

---

### `ItemPlantilla` [item_plantillas]
```
id, materia_id, corte_escuela_id, tipo_item_id, nombre, contenido,
visible_predeterminado, entregable_predeterminado, porcentaje_sugerido, orden

Relaciones:
- materia()       → BelongsTo(Materia)
- corteEscuela()  → BelongsTo(CorteEscuela)
- tipoItem()      → BelongsTo(TipoItem)
- itemInstancias() → HasMany(ItemCorteMateriaPeriodo)
```

---

### `ItemCorteMateriaPeriodo` [item_corte_materia_periodo]
```
id, materia_periodo_id, corte_periodo_id, item_plantilla_id,
tipo_item_id, horario_materia_periodo_id, nombre, contenido,
visible (bool), fecha_inicio, fecha_fin, habilitar_entregable,
porcentaje (decimal), orden

Relaciones:
- materiaPeriodo()      → BelongsTo(MateriaPeriodo)
- cortePeriodo()        → BelongsTo(CortePeriodo)
- itemPlantilla()       → BelongsTo(ItemPlantilla)
- tipoItem()            → BelongsTo(TipoItem)
- horarioMateriaPeriodo() → BelongsTo(HorarioMateriaPeriodo)
- respuestas()          → HasMany(AlumnoRespuestaItem, 'item_corte_materia_periodo_id')
```

---

### `AlumnoRespuestaItem` [alumno_respuesta_items]
```
id, user_id, item_corte_materia_periodo_id, nota_obtenida (decimal),
respuesta_alumno (text), enlace_documento_alumno, maestro_calificador_id,
fecha_calificacion, observaciones_maestro

Relaciones:
- alumno()          → BelongsTo(User, 'user_id')
- itemCalificado()  → BelongsTo(ItemCorteMateriaPeriodo)
- maestroCalificador() → BelongsTo(Maestro, 'maestro_calificador_id')

Accessor:
- archivoUrl → genera URL tenant_asset para el archivo entregado
  ruta: archivos/escuelas/periodo-{id}/respuestas/{filename}
```

---

### `Calificaciones` [calificaciones]
```
id, sistema_calificacion_id, ...

Relaciones:
- sistemaCalificacion() → BelongsTo(SistemaCalificacion, 'sistema_calificacion_id')
```

---

### `SistemaCalificacion` [sistema_calificaciones]
```
id, nombre, es_numerico (bool)

Relaciones:
- periodos()      → HasMany(Periodo)
- calificaciones() → HasMany(Calificaciones, 'sistema_calificacion_id')
```

---

### `MateriaAprobadaUsuario` [materias_aprobada_usuario]
Registro final del resultado académico de un alumno en una materia de un periodo.
```
id, user_id, materia_id, materia_periodo_id, periodo_id,
aprobado (bool), nota_final (decimal), total_asistencias,
motivo_reprobacion, es_homologacion, observacion_homologacion,
sede_id, fecha_homologacion, homologado_por_user_id

Relaciones:
- user()          → BelongsTo(User)
- materia()       → BelongsTo(Materia)
- materiaPeriodo() → BelongsTo(MateriaPeriodo)
- periodo()       → BelongsTo(Periodo)
```

---

## PARTE 4: Sistema de Prerrequisitos

### Prerrequisitos de Materia
- **Tabla**: `materia_prerrequisito`
- **Modelo**: `PrerequisitoMateria`
- **Relación**: `Materia::prerrequisitosMaterias()` → BelongsToMany(Materia)
- El alumno debe haber aprobado la(s) materia(s) prerrequisito para poder matricularse.

### Prerrequisitos de Nivel (Grado)
- **Tabla**: `nivel_escuela_prerrequisitos`
- **Modelo**: `PrerequisitoNivel`
- **Relación**: `NivelEscuela::prerrequisitos()` → BelongsToMany(NivelEscuela)
- **Campo pivote**: `escuela_id` (permite filtrar por contexto de escuela)
- El alumno debe haber completado el nivel prerrequisito para matricularse en el nivel actual.

### Prerrequisitos de Proceso (PasoCrecimiento)
- `Materia::procesosPrerrequisito()` → BelongsToMany(PasoCrecimiento, 'materia_proceso_prerrequisito')
  withPivot('estado_proceso', 'estado_paso_crecimiento_usuario_id', 'indice')
- `NivelEscuela::procesosPrerrequisito()` → BelongsToMany(PasoCrecimiento, 'nivel_proceso_prerrequisito')
  withPivot('id', 'estado_proceso', 'indice', 'estado_paso_crecimiento_usuario_id')

### Tareas Requisito / Tareas Culminadas
- `MateriaTareaRequisito` / `MateriaTareaCulminada`: Tareas que deben completarse antes/al culminar una materia.
- `NivelTareaRequisito` / `NivelTareaCulminada`: Mismo patrón para niveles.

---

## PARTE 5: Sistema de Asistencia

### Modelos
- **`ReporteAsistenciaClase`** (cabecera): vincula HorarioMateriaPeriodo + fecha_clase_reportada
  - Campos: presentes_count, ausentes_count
  - Relación: `HorarioMateriaPeriodo::reportesAsistencia()` → HasMany
- **`ReporteAsistenciaAlumnos`** (detalle): vincula cabecera + alumno_user_id
  - Campos: asistio (bool), motivo_inasistencia_id

### Reglas de Negocio
- Validar que el alumno tenga matrícula activa en el HMP en la fecha reportada.
- Usar `DB::transaction` en reportes masivos.
- La materia hereda los límites de asistencia del nivel si tiene `nivel_id`.

---

## PARTE 6: sistemaEvaluativoMaestros (NUEVO MÓDULO)

> Ver plan completo: `.agent/PlanSistemaEvaluacionMaestros.md`
> Ver propuesta cliente: `.agent/PropuestaClienteSistemaEvaluacionMaestros.md`

### Resumen del Módulo
Sistema para que los alumnos califiquen anónimamente a sus maestros al cierre de cada periodo. Integrado nativamente con Escuelas, Maestros y Periodos.

### 5 Tablas Nuevas
```
evaluacion_formularios        ← Form de preguntas por escuela (1 activo por escuela)
evaluacion_preguntas          ← Preguntas del formulario (escala 1-10 o comentario)
evaluacion_periodos           ← Vínculo formulario+periodo (auto-creado por PeriodoObserver)
evaluacion_respuestas         ← Sesión anónima (sin user_id, con token SHA-256)
evaluacion_respuesta_items    ← Respuestas individuales por pregunta
```

### Auto-vínculo (clave del diseño)
Al crear un `Periodo`, el `PeriodoObserver::created()` busca el formulario activo de la escuela y crea automáticamente un `EvaluacionPeriodo` en estado `borrador`.

### 9 Permisos Spatie a crear
```
evaluacion-formularios.ver / .crear / .editar / .eliminar
evaluacion-convocatorias.ver / .gestionar
evaluacion-resultados.ver / .historial / .propios
```

### Puntos de Integración con el Módulo Existente
- `HorarioMateriaPeriodo` → es la unidad evaluada (una evaluación por clase, no por maestro global)
- `Matricula` → determina si el alumno puede evaluar (debe estar matriculado en el HMP)
- `Maestro::horariosMateriaPeriodo()` → permite agrupar resultados por maestro en el dashboard
- `Periodo` → el observer escucha `Periodo::created` para auto-crear la convocatoria

---

## PARTE 7: Reglas de Negocio Clave

| Regla | Descripción |
|---|---|
| 1 activo por escuela | Solo 1 formulario de evaluación activo por escuela |
| Anonimato | Las respuestas de alumnos NO tienen user_id; usan token SHA-256 |
| No duplicados | El token previene que un alumno responda la misma clase dos veces |
| Promedios honestos | Alumnos que no responden NO afectan el promedio del maestro |
| Herencia de config | Materia hereda config del NivelEscuela si tiene nivel_id asignado |
| Paridad niveles-materias | Los 17 campos de configuración deben ser idénticos en ambos modelos |
| Transacciones | Usar DB::transaction en matrículas, pagos y reportes de asistencia masivos |
| Prerrequisitos | Validar tanto de materia como de nivel antes de permitir matrícula |
| Maestro evaluado | La evaluación es por HorarioMateriaPeriodo, no por maestro global |

---

## PARTE 8: Convenciones de Nomenclatura

| Concepto UI | Concepto Código/BD |
|---|---|
| Grado | Nivel / NivelEscuela / niveles_escuelas |
| Clase | HorarioMateriaPeriodo |
| Corte | CortePeriodo (instancia) / CorteEscuela (plantilla) |
| Evaluación (académica) | ItemCorteMateriaPeriodo + AlumnoRespuestaItem |
| Evaluación (de maestros) | EvaluacionFormulario / EvaluacionPeriodo |
| Convocatoria | EvaluacionPeriodo (el vínculo formulario+periodo) |
