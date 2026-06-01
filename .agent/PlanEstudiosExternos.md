# Plan: Módulo de Estudios Externos y Créditos Académicos
## Módulo: `estudiosExternos`
### Versión del plan: 1.0 — Mayo 2026

---

## Contexto y Objetivo

Construir un sistema para registrar y hacer seguimiento del avance académico de miembros que estudian en instituciones externas (seminarios, universidades teológicas, etc.). El personal administrativo carga archivos Excel estandarizados — uno al inicio del semestre (matriculados) y otro al final (calificaciones) — y el sistema procesa esos datos para registrar matrículas externas, resultados académicos, créditos acumulados, y disparar cambios en pasos de crecimiento y prerrequisitos.

---

## Decisiones Confirmadas

| Decisión | Resolución |
|---|---|
| Formato Excel | Estandarizado por nosotros, el usuario se adapta |
| Matching de personas | Por campo `identificacion` (cédula/DNI) del User |
| Matching de materias | Por `materia_id` interno (se comparte al admin) |
| Archivos por materia | **1 Excel por materia** |
| Persona no encontrada | Se salta, se reporta en log de errores |
| Tabla de matrículas | **Nueva tabla `matriculas_externas`** (no reutilizar `matriculas`) |
| Créditos | Fijos por materia, campo en tabla `materias` |
| Semestre externo | String libre (label definido por el admin en la UI) |
| Escuelas externas | Flag `es_externa` en la tabla `escuelas` |

---

## Modificaciones a Tablas Existentes

### Tabla `escuelas` — agregar columna

```
es_externa      boolean, default false
```

> Permite distinguir escuelas presenciales internas de instituciones externas. Condiciona la UI para mostrar funcionalidades específicas (carga de Excel, dashboard de créditos) solo en escuelas externas.

---

### Tabla `materias` — agregar columna

```
creditos        unsignedSmallInteger, nullable, default null
```

> Los créditos son fijos por materia. Si una materia no tiene créditos (ej. materia presencial interna), queda en `null`. Solo las materias de escuelas externas usarán este campo típicamente, pero queda disponible para todas.

---

### Tabla `materias_aprobada_usuario` — agregar columnas

```
importacion_externa_id    integer, nullable    ← FK a importaciones_externas
semestre_externo          string(50), nullable  ← ej: "2025-1"
creditos_materia          unsignedSmallInteger, nullable  ← snapshot de los créditos al momento del registro
```

> Se agrega `importacion_externa_id` para trazabilidad (saber de qué archivo Excel vino ese resultado).
> Se agrega `semestre_externo` para distinguir registros del mismo alumno en la misma materia en semestres distintos.
> Se agrega `creditos_materia` como snapshot del valor de créditos al momento del registro (por si el pensum se modifica en el futuro).
> Los campos `materia_periodo_id` y `periodo_id` quedarán `null` para registros externos (ya son nullable).

---

## Tablas Nuevas

### Tabla 1: `importaciones_externas`
Log de cada archivo Excel subido al sistema.

```
id
escuela_id                  FK → escuelas
materia_id                  FK → materias
semestre_externo            string(50)             ej: "2025-1"
tipo                        enum('matriculados', 'calificaciones')
archivo_path                string(500)            ruta del archivo almacenado
archivo_nombre_original     string(255)            nombre original del archivo subido
total_filas                 unsignedInteger        total de filas de datos en el Excel
registros_exitosos          unsignedInteger, default 0
registros_con_error         unsignedInteger, default 0
detalle_errores             json, nullable         array de {fila, identificacion, error}
estado                      enum('procesando', 'completado', 'completado_con_errores', 'fallido')
subido_por_user_id          FK → users
timestamps
```

> Cada vez que se sube un Excel, se crea un registro aquí. Permite auditar quién subió qué archivo, cuándo, y cuántos registros se procesaron correctamente vs. con error. El `detalle_errores` guarda las filas que fallaron y por qué (ej. "identificación no encontrada", "materia no coincide con la escuela").

---

### Tabla 2: `matriculas_externas`
Registro de que un miembro está cursando (o cursó) una materia en una institución externa.

```
id
user_id                     FK → users
materia_id                  FK → materias
escuela_id                  FK → escuelas (denormalizado para consultas directas)
importacion_matricula_id    FK → importaciones_externas (el Excel de matriculados que lo creó)
importacion_calificacion_id FK → importaciones_externas, nullable (el Excel de calificaciones que lo actualizó)
semestre_externo            string(50)
estado                      enum('en_curso', 'aprobado', 'reprobado'), default 'en_curso'
nota_final                  decimal(5,2), nullable
observacion                 text, nullable
fecha_registro              date                   ← fecha en que se procesó el archivo inicial
fecha_calificacion          date, nullable         ← fecha en que se procesó el archivo final
timestamps

INDEX (user_id, materia_id, semestre_externo)
UNIQUE (user_id, materia_id, semestre_externo)  ← un alumno solo puede estar matriculado una vez en la misma materia+semestre
```

> **¿Por qué no usar `matriculas`?** Porque la tabla existente depende de `horario_materia_periodo_id`, `periodo_id`, campos de pago y otros que no aplican al contexto externo. `matriculas_externas` es limpia, sin dependencias, y no contamina queries ni dashboards existentes.

---

## Formato del Excel Estandarizado

### Excel Tipo 1: Matriculados (inicio de semestre)

| Columna | Nombre | Tipo | Requerido | Descripción |
|---|---|---|---|---|
| A | `identificacion` | Texto/Número | ✅ | Cédula o documento de identidad del miembro |

> Solo 1 columna. La `materia_id`, `escuela_id` y `semestre_externo` se seleccionan en la UI antes de subir el archivo. Cada fila = un miembro matriculado.

### Excel Tipo 2: Calificaciones (fin de semestre)

| Columna | Nombre | Tipo | Requerido | Descripción |
|---|---|---|---|---|
| A | `identificacion` | Texto/Número | ✅ | Cédula o documento de identidad |
| B | `nota_final` | Número decimal | ✅ | Nota final obtenida |
| C | `aprobado` | 1 o 0 | ✅ | 1 = aprobado, 0 = reprobado |
| D | `observacion` | Texto | ❌ | Observación opcional |

> El sistema busca la `matricula_externa` existente del alumno en esa materia+semestre y la actualiza. Si no existe matricula previa (ej. se saltó el primer archivo), la crea directamente con el estado final.

---

## Flujo Completo del Sistema

```
[1] ADMIN crea Escuela con es_externa = true
        → Crea materias con créditos asignados

[2] ADMIN sube Excel de Matriculados
        → Selecciona: Escuela → Materia → Semestre externo (texto libre)
        → Sube el archivo
        → SISTEMA procesa fila por fila:
            - Busca User por identificacion
            - Si existe: crea matricula_externa con estado 'en_curso'
            - Si no existe: registra error, salta a la siguiente
        → Crea registro en importaciones_externas con resumen

[3] ADMIN sube Excel de Calificaciones (semanas/meses después)
        → Selecciona: Escuela → Materia → Semestre externo (mismo del paso 2)
        → Sube el archivo
        → SISTEMA procesa fila por fila:
            - Busca User por identificacion
            - Busca matricula_externa existente (user + materia + semestre)
            - Si existe: actualiza estado, nota_final, observacion
            - Si no existe: crea matricula_externa nueva con estado final directamente
            - Crea registro en materias_aprobada_usuario:
                · user_id, materia_id (los del matching)
                · materia_periodo_id = null, periodo_id = null
                · aprobado = valor del Excel
                · nota_final = valor del Excel
                · es_homologacion = false
                · semestre_externo = el label seleccionado
                · creditos_materia = snapshot de materia.creditos
                · importacion_externa_id = id de esta importación
            - Si aprobado: verifica y dispara cambios en pasos de crecimiento
              y prerrequisitos de materias (misma lógica que el flujo presencial)
        → Crea registro en importaciones_externas con resumen

[4] ADMIN consulta dashboard de avance
        → Selecciona Escuela externa
        → Ve estadísticas globales: créditos totales del pensum, % completado
        → Por alumno: materias aprobadas, en curso, reprobadas, créditos acumulados
```

---

## Módulo 1 — Admin: Gestión de Escuelas Externas

No se necesita una UI nueva. Se agrega al flujo existente de crear/editar escuela un **switch "Es institución externa"**. Cuando `es_externa = true`:
- Se muestra la sección de **créditos** al editar cada materia
- Se habilita el menú de **Carga de Excel** en el sidebar de esa escuela
- Se habilita el **Dashboard de Créditos**

---

## Módulo 2 — Admin: Carga de Archivos Excel

**Ruta base:** `/escuelas/{escuela}/importaciones-externas`
**Permiso Spatie:** `estudios-externos.importar`

### Vista: `gestionar-importaciones.blade.php`

**Panel superior — Formulario de carga:**
- Select: Materia (filtrada por la escuela)
- Input: Semestre externo (texto libre, ej: "2025-1")
- Select: Tipo de archivo (`Matriculados` / `Calificaciones`)
- Input file: archivo Excel (.xlsx)
- Botón: "Procesar archivo"

**Panel inferior — Historial de importaciones:**
Tabla con todas las importaciones realizadas:

| Fecha | Materia | Semestre | Tipo | Total | Exitosos | Errores | Estado | Acciones |
|---|---|---|---|---|---|---|---|---|
| 2025-06-15 | Homilética | 2025-1 | Matriculados | 25 | 24 | 1 | ✅ Completado con errores | Ver errores \| Descargar original |

**Acción "Ver errores"** → Modal con tabla de errores:

| Fila | Identificación | Error |
|---|---|---|
| 12 | 1098765432 | Usuario no encontrado en el sistema |

---

## Módulo 3 — Admin: Vista de Matriculados Externos

**Ruta:** `/escuelas/{escuela}/matriculados-externos`
**Permiso Spatie:** `estudios-externos.ver-matriculados`

### Componente Livewire: `MatriculadosExternos`

**Filtros:**
- Materia
- Semestre externo
- Estado (En curso / Aprobado / Reprobado / Todos)

**Tabla:**

| Alumno | Identificación | Sede | Materia | Semestre | Estado | Nota | Créditos |
|---|---|---|---|---|---|---|---|
| Juan Pérez | 1234567890 | Norte | Homilética | 2025-1 | ✅ Aprobado | 8.5 | 3 |
| María López | 0987654321 | Sur | Homilética | 2025-1 | 🔵 En curso | — | 3 |

> La sede y grupo del alumno se obtienen directamente de `User::sede()` y `User::gruposDondeAsiste()`.

---

## Módulo 4 — Admin: Dashboard de Avance por Créditos

**Ruta:** `/escuelas/{escuela}/dashboard-creditos`
**Permiso Spatie:** `estudios-externos.dashboard`

### Componente Livewire: `DashboardCreditos`

**Tarjetas resumen (arriba):**
- Total de créditos del pensum (suma de `creditos` de todas las materias de la escuela)
- Total de alumnos con al menos 1 materia registrada
- % promedio de avance en créditos

**Filtros:** Semestre (o "Acumulado histórico")

**Tabla de avance por alumno:**

| Alumno | Identificación | Sede | Créditos aprobados | Créditos reprobados | Créditos en curso | % Avance | Último registro |
|---|---|---|---|---|---|---|---|
| Juan Pérez | 1234567890 | Norte | 18/45 | 3 | 6 | 40% | 2025-06 |

**Click en un alumno** → Detalle expandible:

| Materia | Créditos | Semestre | Estado | Nota |
|---|---|---|---|---|
| Homilética | 3 | 2025-1 | ✅ Aprobado | 8.5 |
| Hermenéutica | 3 | 2025-1 | ✅ Aprobado | 7.2 |
| Teología I | 4 | 2025-2 | 🔵 En curso | — |
| Griego I | 3 | 2024-2 | ❌ Reprobado | 4.1 |
| Griego I | 3 | 2025-1 | ✅ Aprobado | 7.8 |

> Nótese que Griego I aparece dos veces: reprobado en 2024-2, aprobado en 2025-1. Los créditos solo se cuentan una vez (por la aprobación).

---

## Lógica de Pasos de Crecimiento y Prerrequisitos

Cuando un registro de `materias_aprobada_usuario` se crea con `aprobado = true`, el sistema debe evaluar:

### 1. Prerrequisitos de Materias
```
¿La materia aprobada es prerrequisito de otra materia?
→ Materia::prerrequisitosMaterias() (relación inversa)
→ Si todas las materias prerrequisito están aprobadas, la materia destino queda "habilitada"
```

### 2. Pasos de Crecimiento
```
¿La materia tiene pasos de crecimiento asociados?
→ Materia::pasosCrecimiento() — con pivots 'estado', 'al_iniciar'
→ Si al_iniciar = false (o sea, al culminar): actualizar CrecimientoUsuario
```

### 3. Procesos Prerrequisito
```
¿La materia tiene procesos prerrequisito?
→ Materia::procesosPrerrequisito()
→ Verificar si el estado del proceso se cumple
```

> Esta lógica se encapsula en un **Service** (`EstudiosExternosService`) que se invoca tanto al procesar el Excel de calificaciones como si en el futuro se necesita un registro manual.

---

## Permisos Spatie a Crear

```
estudios-externos.importar           (subir archivos Excel)
estudios-externos.ver-matriculados   (ver lista de matriculados)
estudios-externos.ver-importaciones  (ver historial de importaciones)
estudios-externos.dashboard          (dashboard de créditos)
estudios-externos.eliminar           (eliminar importación + sus registros)
```

---

## Estructura de Archivos Nuevos

```
app/
├── Models/
│   ├── ImportacionExterna.php
│   └── MatriculaExterna.php
├── Services/
│   └── EstudiosExternosService.php       ← Lógica de procesamiento de Excel + pasos crecimiento
├── Imports/
│   ├── MatriculadosExternosImport.php    ← Clase Laravel Excel para tipo 'matriculados'
│   └── CalificacionesExternasImport.php  ← Clase Laravel Excel para tipo 'calificaciones'
├── Http/
│   ├── Controllers/
│   │   └── ImportacionExternaController.php
│   └── Requests/
│       └── SubirImportacionExternaRequest.php
└── Livewire/
    └── EstudiosExternos/
        ├── MatriculadosExternos.php
        └── DashboardCreditos.php

database/migrations/tenant/
├── ..._add_es_externa_to_escuelas_table.php
├── ..._add_creditos_to_materias_table.php
├── ..._add_campos_externos_to_materias_aprobada_usuario_table.php
├── ..._create_importaciones_externas_table.php
└── ..._create_matriculas_externas_table.php

resources/views/contenido/paginas/escuelas/estudios-externos/
├── gestionar-importaciones.blade.php
├── matriculados-externos.blade.php
└── dashboard-creditos.blade.php
```

---

## Plantillas Excel Descargables

El sistema debe ofrecer un botón para **descargar la plantilla Excel vacía** con las columnas correctas y un ejemplo:

- `plantilla-matriculados.xlsx` → 1 columna: `identificacion`
- `plantilla-calificaciones.xlsx` → 4 columnas: `identificacion`, `nota_final`, `aprobado`, `observacion`

Estas plantillas se generan dinámicamente con Laravel Excel o se almacenan como archivos estáticos.

---

## Estimación de Tiempo (30 horas/semana con IA)

| Componente | Horas estimadas |
|---|---|
| Migraciones (5 archivos: 3 alter + 2 create) + Modelos | 5h |
| Permisos Spatie + Rutas | 2h |
| Plantillas Excel + clases Import (Laravel Excel) | 8h |
| EstudiosExternosService (lógica de procesamiento + pasos crecimiento) | 10h |
| Módulo Carga de Excel (UI + controller + validaciones) | 8h |
| Módulo Matriculados Externos (Livewire + filtros) | 6h |
| Módulo Dashboard de Créditos (Livewire + queries + gráficas) | 10h |
| Switch es_externa en Escuelas + campo créditos en Materias | 3h |
| Pruebas, integración y edge cases | 6h |
| **Total estimado** | **~58 horas ≈ 2 semanas** |

---

## Preguntas Resueltas — Resumen

| # | Pregunta | Respuesta |
|---|---|---|
| 1 | ¿Formato Excel fijo? | Sí, estandarizado por nosotros |
| 2 | ¿IDs en el Excel? | Sí, matching directo por materia_id |
| 3 | ¿Múltiples materias por Excel? | No, 1 Excel por materia |
| 4 | ¿Semestre formal o label? | Label string libre |
| 5 | ¿Usar tabla matriculas existente? | No, tabla nueva matriculas_externas |
| 6 | ¿Créditos cambian entre semestres? | No, fijos en la materia |
| 7 | ¿Persona no encontrada? | Se salta, se registra en log de errores |
| 8 | ¿Escuela como contenedor? | Sí, escuela con es_externa=true |
