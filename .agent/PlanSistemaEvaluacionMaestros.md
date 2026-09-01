# PlanSistemaEvaluacionMaestros
## Módulo: `sistemaEvaluativoMaestros`
### Versión del plan: 1.0 — Mayo 2026

---

## Decisiones de Diseño Confirmadas

| Decisión | Resolución |
|---|---|
| Tipos de pregunta | Solo escala 1–10 + 1 comentario libre (no evaluativo) |
| Formularios activos por escuela | **Solo 1 activo a la vez** |
| Acceso a vistas | Permisos Spatie por rol del tenant |
| ¿Evaluación obligatoria? | No por defecto, pero **configurable por convocatoria** |
| Alumnos que no responden | NO afectan el promedio (avg solo sobre respuestas existentes) |
| El maestro ve comentarios | **No**. Solo ve sus promedios numéricos |
| Dashboard muestra | Último periodo por defecto; vista histórica separada |
| Puntaje mínimo de aprobación | Configurable por formulario |
| Auto-vínculo formulario ↔ periodo | **Automático** via `PeriodoObserver` al crear un periodo |

---

## Arquitectura de Base de Datos

### `evaluacion_formularios`
```
id
nombre                          string(200)
descripcion                     text, nullable
escuela_id                      FK → escuelas
puntaje_minimo_aprobacion       decimal(3,1), nullable   default 7.0
activo                          boolean, default true
timestamps
```
**Regla de negocio**: Al activar un formulario, se desactiva el anterior de esa escuela (en el controller). Solo 1 activo por escuela.

---

### `evaluacion_preguntas`
```
id
formulario_id       FK → evaluacion_formularios (cascade delete)
texto               string(500)
tipo                enum('escala', 'comentario')
orden               unsignedSmallInteger, default 0
activo              boolean, default true
timestamps
```
**Regla de negocio**: Máximo 1 pregunta tipo `comentario` por formulario. Validado en FormRequest.

---

### `evaluacion_periodos`
```
id
formulario_id       FK → evaluacion_formularios
periodo_id          FK → periodos
estado              enum('borrador', 'abierto', 'cerrado'), default 'borrador'
es_obligatorio      boolean, default false
fecha_apertura      datetime, nullable
fecha_cierre        datetime, nullable
timestamps

UNIQUE (formulario_id, periodo_id)
```
**Creación**: Automática vía `PeriodoObserver::created()`. El admin solo gestiona el estado (abrir / cerrar).

---

### `evaluacion_respuestas`
```
id
evaluacion_periodo_id           FK → evaluacion_periodos
horario_materia_periodo_id      FK → horarios_materia_periodo
token_anonimo                   char(64), UNIQUE   ← SHA-256
submitted_at                    timestamp
timestamps
```
**Sin user_id**. Anonimato garantizado. El token previene doble votación sin exponer identidad.

**Generación del token:**
```php
hash('sha256', $userId . '|' . $evaluacionPeriodoId . '|' . $hmpId . '|' . config('app.key'))
```

---

### `evaluacion_respuesta_items`
```
id
evaluacion_respuesta_id     FK → evaluacion_respuestas (cascade delete)
pregunta_id                 FK → evaluacion_preguntas
valor_escala                tinyint, nullable        (1–10, solo para tipo 'escala')
valor_comentario            text, nullable           (solo para tipo 'comentario')
timestamps
```

---

## Diagrama de Relaciones

```
Escuela ──────────────────────── EvaluacionFormulario (1 activo por escuela)
                                        │
                           EvaluacionPregunta (N preguntas)
                                        │
                            EvaluacionPeriodo ────────── Periodo
                                        │                  │
                                        │         [PeriodoObserver auto-crea]
                                        │
                            EvaluacionRespuesta ─── HorarioMateriaPeriodo ─── Maestro
                                        │
                          EvaluacionRespuestaItem ──── EvaluacionPregunta
```

---

## Observer: Auto-vínculo Formulario ↔ Periodo

**Archivo:** `app/Observers/PeriodoObserver.php`
**Registrado en:** `AppServiceProvider::boot()`

**Lógica:**
```
Periodo::created →
    Busca EvaluacionFormulario activo para $periodo->escuela_id
    Si existe:
        EvaluacionPeriodo::create([
            'formulario_id' => $formulario->id,
            'periodo_id'    => $periodo->id,
            'estado'        => 'borrador',
        ])
    Si no existe:
        Log::warning(...)  // Aviso silencioso
```

---

## Módulos del Sistema

### Módulo 1 — Gestión de Formularios (Admin)
**Ruta base:** `/escuelas/evaluaciones/formularios`
**Permisos:** `evaluacion-formularios.ver`, `.crear`, `.editar`, `.eliminar`

**Vistas:**
- `gestionar-formularios.blade.php`: Cards por formulario con acciones (editar, ver preguntas, duplicar, activar/desactivar, eliminar)
- Offcanvas crear/editar: nombre, descripción, escuela, puntaje mínimo, estado
- **Duplicar**: Copia el formulario y todas sus preguntas. Escuela del clon = null (el admin la asigna antes de activar)

**Vista preguntas:** `gestionar-preguntas.blade.php`
- Ruta: `/escuelas/evaluaciones/formularios/{formulario}/preguntas`
- Listado con orden, tipo (badge), activo/inactivo
- Botones ↑↓ para reordenar (o drag-and-drop Alpine.js)
- Offcanvas crear/editar pregunta
- Si ya hay 1 `comentario`, el select solo muestra `escala`

---

### Módulo 2 — Gestión de Convocatorias (Admin)
**Ruta base:** `/escuelas/evaluaciones/convocatorias`
**Permisos:** `evaluacion-convocatorias.ver`, `.gestionar`

**Vista:** `gestionar-convocatorias.blade.php`
- Tabla: periodo | escuela | formulario | estado | # respuestas | acciones
- Filtros: escuela, periodo, estado
- Acciones:
  - `borrador` → **Abrir** (cambia a `abierto`, guarda `fecha_apertura`)
  - `abierto` → **Cerrar** (cambia a `cerrado`, guarda `fecha_cierre`) + toggle `es_obligatorio`
  - `cerrado` → **Ver resultados** (redirige al dashboard)
- Fallback manual: si no se creó automáticamente, botón "Vincular formulario"

---

### Módulo 3 — Evaluación del Alumno
**Vista index:** `mis-evaluaciones/index.blade.php`
- Lista clases del alumno en el periodo activo con evaluación `abierta`
- Badge: `Pendiente` / `Completada`

**Componente Livewire:** `EvaluarMaestro`
**Ruta:** `/mis-evaluaciones/{evaluacion_periodo_id}/{horario_materia_periodo_id}`

**Validaciones al cargar:**
1. Alumno matriculado en el HMP → 403 si no
2. Evaluación en estado `abierto` → 403 si no
3. Token ya existe → "Ya respondiste esta evaluación"

**Formulario:**
- Preguntas `escala`: rating visual 1–10 interactivo (Alpine.js)
- Pregunta `comentario`: textarea opcional
- Al enviar: genera token, guarda respuestas, muestra confirmación

---

### Módulo 4A — Dashboard Admin: Resultados del Periodo
**Ruta:** `/escuelas/evaluaciones/dashboard`
**Permiso:** `evaluacion-resultados.ver`
**Componente Livewire:** `DashboardEvaluaciones`

**Filtros reactivos:** Escuela → Periodo (último por defecto)

**Tarjetas resumen:**
- Maestros evaluados / Total en el periodo
- % participación de alumnos
- Maestros que aprueban / no aprueban

**Por maestro:**
- Foto, nombre, badge ✅/❌ aprueba
- Por cada clase: materia, horario, N° evaluaciones/total alumnos, promedio (barra visual), detalle por pregunta expandible

---

### Módulo 4B — Historial de Evaluaciones
**Ruta:** `/escuelas/evaluaciones/historial`
**Permiso:** `evaluacion-resultados.historial`
- Tabla con convocatorias cerradas, filtrada por escuela y año
- Click en fila → carga el dashboard con ese periodo

---

### Módulo 4C — Vista del Maestro: Mis Resultados
**Ruta:** `/mis-resultados-evaluacion`
**Permiso:** `evaluacion-resultados.propios`
- Solo sus propios promedios por clase y periodo
- **NO muestra comentarios**
- Selector de periodo para historial propio

---

## Permisos Spatie a Crear

```
evaluacion-formularios.ver
evaluacion-formularios.crear
evaluacion-formularios.editar
evaluacion-formularios.eliminar
evaluacion-convocatorias.ver
evaluacion-convocatorias.gestionar
evaluacion-resultados.ver
evaluacion-resultados.historial
evaluacion-resultados.propios
```

---

## Estructura de Archivos

```
app/
├── Models/
│   ├── EvaluacionFormulario.php
│   ├── EvaluacionPregunta.php
│   ├── EvaluacionPeriodo.php
│   ├── EvaluacionRespuesta.php
│   └── EvaluacionRespuestaItem.php
├── Observers/
│   └── PeriodoObserver.php
├── Http/
│   ├── Controllers/
│   │   ├── EvaluacionFormularioController.php
│   │   ├── EvaluacionConvocatoriaController.php
│   │   └── EvaluacionAlumnoController.php
│   └── Requests/
│       ├── GuardarEvaluacionFormularioRequest.php
│       ├── GuardarEvaluacionPreguntaRequest.php
│       └── EnviarEvaluacionAlumnoRequest.php
└── Livewire/
    └── Evaluaciones/
        ├── DashboardEvaluaciones.php
        └── EvaluarMaestro.php

database/migrations/tenant/
├── ..._create_evaluacion_formularios_table.php
├── ..._create_evaluacion_preguntas_table.php
├── ..._create_evaluacion_periodos_table.php
├── ..._create_evaluacion_respuestas_table.php
└── ..._create_evaluacion_respuesta_items_table.php

resources/views/contenido/paginas/escuelas/evaluaciones/
├── gestionar-formularios.blade.php
├── gestionar-preguntas.blade.php
├── gestionar-convocatorias.blade.php
├── dashboard-evaluaciones.blade.php
└── historial-evaluaciones.blade.php

resources/views/contenido/paginas/alumno/mis-evaluaciones/
├── index.blade.php
└── evaluar.blade.php

resources/views/contenido/paginas/maestros/mis-resultados-evaluacion/
└── index.blade.php
```

---

## Estimación de Tiempo (30 horas/semana con IA)

| Componente | Horas estimadas |
|---|---|
| Migraciones, Modelos y Observer | 6h |
| Permisos Spatie + rutas | 3h |
| Módulo 1: Formularios + Preguntas (CRUD completo) | 14h |
| Módulo 2: Convocatorias (gestión de estados) | 8h |
| Módulo 3: Evaluación del Alumno (Livewire + anonimato) | 14h |
| Módulo 4A: Dashboard de resultados (Livewire + queries) | 18h |
| Módulo 4B: Historial de evaluaciones | 5h |
| Módulo 4C: Vista del maestro (mis resultados) | 5h |
| UI polish, pruebas e integración | 10h |
| **Total estimado** | **~83 horas ≈ 3 semanas** |

> Sin IA el mismo trabajo tomaría aproximadamente 5–6 semanas.

---

## Flujo Completo del Sistema

```
[1] ADMIN crea Formulario de evaluación para Escuela X
        → Define preguntas (escala 1-10) + 1 pregunta de comentario
        → Activa el formulario (desactiva el anterior de esa escuela)

[2] ADMIN crea Periodo para Escuela X
        → PeriodoObserver detecta el evento Periodo::created
        → Busca el formulario activo de Escuela X
        → Crea EvaluacionPeriodo en estado "borrador" automáticamente

[3] ADMIN abre la convocatoria
        → Cambia estado: borrador → abierto
        → Opcionalmente configura es_obligatorio y fechas

[4] ALUMNO inicia sesión y ve sus evaluaciones pendientes
        → Ve lista de clases con evaluación abierta
        → Responde el formulario de forma anónima (token hash)
        → No puede responder dos veces la misma clase

[5] ADMIN cierra la convocatoria
        → Cambia estado: abierto → cerrado

[6] ADMIN consulta resultados en el dashboard
        → Selecciona Escuela + Periodo
        → Ve promedios por maestro y por clase
        → Identifica maestros que no alcanzan el puntaje mínimo
        → Toma decisiones (activar/inactivar maestros desde el módulo de maestros)

[7] MAESTRO (con permiso) consulta sus propios resultados
        → Ve sus promedios por clase (sin comentarios individuales)
```
