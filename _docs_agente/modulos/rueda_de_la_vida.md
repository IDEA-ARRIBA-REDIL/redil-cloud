# Módulo: Rueda de la Vida (RDV)

Este documento describe la arquitectura completa, la base de datos, el flujo de usuario, el panel administrativo y los modelos del módulo "Rueda de la Vida" en REDIL Cloud.

---

## 1. Propósito del Módulo

La **Rueda de la Vida** es una herramienta de autodiagnóstico espiritual y de hábitos en REDIL Cloud. El usuario:
1. Califica sus hábitos en distintas áreas de su vida (del 0 al 10).
2. Visualiza su estado general mediante gráficos polares interactivos (ApexCharts) por área y en un consolidado general.
3. Establece metas dinámicas vinculadas a las áreas evaluadas y hábitos de mejora concretos.
4. Consulta su historial de evaluaciones previas.
5. **Realiza seguimiento periódico de sus hábitos**: registra avances por período (ej. cada 30 días) para medir su evolución a lo largo del tiempo.
6. **Administración amigable**: la iglesia cuenta con un panel administrativo Livewire para gestionar áreas (secciones) y hábitos (campos) sin manipular la base de datos.

---

## 2. Arquitectura de Base de Datos

### 2.1 Tablas Activas del Módulo

#### `tipos_seccion_rv` — Tipos de sección
Define los comportamientos posibles de cada sección del formulario.

| Campo        | Tipo    | Descripción                                      |
|--------------|---------|--------------------------------------------------|
| `nombre`     | string  | `contador` (1), `promedios` (2), `encuesta` (3)  |
| `min`        | int     | Valor mínimo permitido (0)                       |
| `max`        | int     | Valor máximo permitido (10)                      |
| `validacion` | bool    | Si la sección tiene validación                   |
| `resumen`    | bool    | Si la sección muestra un resumen                 |
| `encuesta`   | bool    | Si la sección es tipo encuesta (metas/hábitos)   |

#### `secciones_rv` — Secciones del formulario (Áreas de Vida)
Cada sección corresponde a un paso en el wizard de la Rueda. Tiene SoftDeletes.

| Campo              | Descripción                                           |
|--------------------|-------------------------------------------------------|
| `titulo_barra`     | Texto del navbar                                      |
| `tipo_seccion_id`  | FK a `tipos_seccion_rv`                               |
| `icono`            | Clase de ícono Tabler (ej. `ti ti-cloud-heart`)       |
| `orden`            | Orden de aparición en el wizard                       |
| `titulo_steper`    | Subtítulo del paso                                    |
| `nombre_seccion`   | Nombre visible del área (ej. Espiritual, Física, etc.)|
| `subtitulo_seccion`| Texto descriptivo o instrucción                       |
| `color`            | Color hexadecimal para gráficos y acentos visuales    |
| `promedio_minimo`  | Valor mínimo esperado de promedio (ej. 6)            |
| `min`, `max`       | Rango de calificación (0 a 10)                        |

#### `campos_seccion_rv` — Hábitos por Área
Cada campo es un hábito calificable dentro de una sección tipo `contador`.

| Campo          | Descripción                                                      |
|----------------|------------------------------------------------------------------|
| `nombre`       | Nombre del hábito (ej. "Oración", "Ejercicio")                   |
| `abierto`      | `true` = campo libre (el usuario escribe su propio hábito)       |
| `seccion_rv_id`| FK a `secciones_rv`                                              |
| `orden`        | Orden del campo dentro del área                                  |
| `color`        | Color hexadecimal para la serie polar de ApexCharts              |

#### `rueda_de_la_vida_user` — Cabecera de Evaluación
Guarda cada ocasión en que el usuario completa su autodiagnóstico.

| Campo              | Descripción                                     |
|--------------------|-------------------------------------------------|
| `usuario_id`       | FK al usuario autenticado                       |
| `fecha`            | Fecha de realización (`Y-m-d`)                  |
| `promedio_general` | Promedio global calculado de todas las áreas    |

#### `campo_rueda_de_la_vida` — Pivote de Calificaciones
Relaciona `RuedaDeLaVidaUser` con `CampoSeccionRv`.

| Campo                  | Descripción                                   |
|------------------------|-----------------------------------------------|
| `rueda_de_la_vida_id`  | FK a `rueda_de_la_vida_user`                  |
| `campos_seccion_rv_id` | FK a `campos_seccion_rv`                      |
| `valor`                | Valor numérico asignado (0–10)                |
| `nombre_campo_abierto` | Texto libre si el campo es `abierto: true`    |

#### `metas_usuario_rv` — Metas Dinámicas del Usuario
Metas creadas libremente por el usuario en el último paso del wizard.

| Campo                 | Descripción                                       |
|-----------------------|---------------------------------------------------|
| `rueda_de_la_vida_id` | FK a `rueda_de_la_vida_user`                      |
| `seccion_rv_id`       | FK a `secciones_rv` (área de vida a la que aplica)|
| `nombre`              | Nombre o enunciado de la meta                     |

#### `habitos_usuario_rv` — Hábitos de cada Meta
Acciones concretas que el usuario define para cumplir una meta.

| Campo                 | Descripción                                |
|-----------------------|--------------------------------------------|
| `meta_usuario_rv_id`  | FK a `metas_usuario_rv`                    |
| `nombre`              | Nombre o descripción del hábito            |

#### `avance_habito_rv` — Seguimiento Periódico de Hábitos
Registros históricos de avance para cada hábito.

| Campo                  | Descripción                                           |
|------------------------|-------------------------------------------------------|
| `habito_usuario_rv_id` | FK a `habitos_usuario_rv`                             |
| `puntaje`              | Puntaje alcanzado en el período (0–10)                |
| `periodo_inicio`       | Fecha de inicio del período (`date`)                  |

> **Restricción de unicidad**: `['habito_usuario_rv_id', 'periodo_inicio']` garantiza que solo se registre un avance por hábito en cada ciclo.

#### `configuracion_rv` — Parámetros Globales del Módulo

| Campo                    | Descripción                                                     |
|--------------------------|-----------------------------------------------------------------|
| `nombre_general`         | Nombre visible del módulo (ej. "Rueda de la Vida")              |
| `label_promedio_general` | Label para el promedio en vistas (ej. "Promedio")               |
| `promedio_general`       | Valor mínimo del promedio para resaltar éxito (ej. 6)           |
| `nombre_habitos`         | Label del bloque de hábitos en la encuesta                      |
| `max_metas`              | Máximo de metas permitidas por usuario en cada rueda            |
| `max_habitos_por_meta`   | Máximo de hábitos permitidos por meta                           |
| `periodicidad`           | Frecuencia en días entre evaluaciones de avance (default 30)    |

---

## 3. Modelos y Relaciones Eloquent

```
RuedaDeLaVidaUser
  ├── campos()       → BelongsToMany(CampoSeccionRv, 'campo_rueda_de_la_vida')
  │                     withPivot: valor, nombre_campo_abierto
  └── metasUsuario() → HasMany(MetaUsuarioRv, 'rueda_de_la_vida_id')

MetaUsuarioRv
  ├── ruedaDeLaVida() → BelongsTo(RuedaDeLaVidaUser, 'rueda_de_la_vida_id')
  ├── seccion()       → BelongsTo(SeccionRv, 'seccion_rv_id')
  └── habitos()       → HasMany(HabitoUsuarioRv, 'meta_usuario_rv_id')

HabitoUsuarioRv
  ├── meta()    → BelongsTo(MetaUsuarioRv, 'meta_usuario_rv_id')
  └── avances() → HasMany(AvanceHabitoRv, 'habito_usuario_rv_id') (orden asc por periodo_inicio)

AvanceHabitoRv
  └── habito()  → BelongsTo(HabitoUsuarioRv, 'habito_usuario_rv_id')

SeccionRv
  ├── tipoSeccion() → BelongsTo(TipoSeccionRv)
  └── campos()      → HasMany(CampoSeccionRv, 'seccion_rv_id') (orden asc)

CampoSeccionRv
  └── seccion()     → BelongsTo(SeccionRv, 'seccion_rv_id')
```

---

## 4. Rutas Registradas (`routes/app.php`)

| Método | URI                               | Nombre                              | Acción / Descripción                          |
|--------|-----------------------------------|-------------------------------------|-----------------------------------------------|
| GET    | `/rueda-vida/gestor`              | `ruedaDeLaVida.gestor`              | Redirige a historial o bienvenida             |
| GET    | `/rueda-vida/bienvenida`          | `ruedaDeLaVida.bienvenida`          | Pantalla de onboarding inicial                |
| GET    | `/rueda-vida/nueva`               | `ruedaDeLaVida.nueva`               | Formulario wizard de autoevaluación           |
| PATCH  | `/rueda-vida/crear`               | `ruedaDeLaVida.crear`               | Guarda la rueda (`DB::transaction`)           |
| GET    | `/rueda-vida/historial`           | `ruedaDeLaVida.historial`           | Listado paginado de evaluaciones              |
| GET    | `/rueda-vida/{rueda}/resumen`     | `ruedaDeLaVida.resumen`             | Resumen consolidado y seguimiento de avances  |
| GET    | `/rueda-vida/finalizada`          | `ruedaDeLaVida.finalizada`          | Pantalla de éxito tras diligenciar la rueda   |
| GET    | `/rueda-vida/gestionar`           | `ruedaDeLaVida.gestionar`           | Panel administrativo Livewire del módulo      |
| GET    | `/rueda-vida/meta/{meta}/avances` | `ruedaDeLaVida.avancesHabitos`      | JSON: Historial de avances de una meta        |
| POST   | `/rueda-vida/meta/{meta}/avance`  | `ruedaDeLaVida.guardarAvanceHabitos`| JSON: Guarda avances de hábitos de una meta   |
| GET    | `/rueda-vida/habito/{habito}/avances`| `ruedaDeLaVida.avancesHabito`   | JSON: Avances y estado de un hábito           |
| POST   | `/rueda-vida/habito/{habito}/avance` | `ruedaDeLaVida.guardarAvanceHabito`| JSON: Guarda avance de un hábito individual   |

---

## 5. Panel Administrativo Livewire (`GestionarRuedaDeLaVida`)

Ubicado en `app/Livewire/RuedaDeLaVida/GestionarRuedaDeLaVida.php` y renderizado en `rueda-vida/gestionar`.

### Capacidades:
1. **Pestaña Áreas y Hábitos**:
   - Lista todas las secciones configuradas en formato acordeón con su color distintivo e ícono Tabler.
   - **Crear / Editar / Eliminar / Reordenar Áreas** (`SeccionRv` tipo 1):
     - Nombre, subtítulo, ícono, color (color picker) y promedio mínimo.
   - **Crear / Editar / Eliminar / Reordenar Hábitos** (`CampoSeccionRv`):
     - Configurar hábitos fijos o abiertos (donde el usuario redacta libremente).
     - Asignar color para el gráfico polar y orden dentro del área.
2. **Pestaña Configuración General**:
   - Edición de `ConfiguracionRv`: nombre general, labels, promedio mínimo, límites de metas y periodicidad de avances.
3. **Seguridad y Feedback**:
   - Confirmaciones de eliminación con SweetAlert2 (`Swal.fire`).
   - Alertas de éxito reactivas mediante `$this->dispatch('msn', ...)`.

---

## 6. Submódulo de Seguimiento de Hábitos (`AvanceHabitoRv`)

En la pantalla de resumen (`/rueda-vida/{rueda}/resumen`):
- Los usuarios pueden ver sus metas y hábitos asignados.
- Cada hábito tiene un botón *"Registrar avance"*, que abre un modal con:
  - **Pestaña Período Actual**: Permite calificar el hábito (0 a 10) para el ciclo actual. Si ya fue evaluado, se bloquea y se muestra la fecha del próximo período.
  - **Pestaña Historial**: Muestra una tabla con las calificaciones históricas y un gráfico ApexCharts de evolución temporal.
- El cálculo de períodos se efectúa con:
  `$periodosCompletos = floor($fechaCreacion->diffInDays(today()) / $periodicidad);`
  `$periodoInicio = $fechaCreacion->copy()->addDays($periodosCompletos * $periodicidad);`

---

## 7. Notas de Compatibilidad y Limpieza

- Las tablas `metas`, `habitos_rueda_vida`, `meta_rueda_de_la_vida`, `habitos_rueda_de_la_vida` y `rueda_de_la_vida` corresponden a la versión 1 legada. Se conservan en el schema por seguridad histórica pero no son consultadas ni alimentadas por el flujo activo.
- El controlador `RuedaDeLaVidaController` ejecuta `crear()` bajo transacciones ACID (`DB::transaction`) y precalcula los promedios en `resumen()` en una sola consulta agrupada para eliminar problemas de N+1.

---

## 8. Hábitos de Campo Abierto y Visualización en Resumen

- **Formulario (`nueva.blade.php`)**:
  - Los hábitos configurados como abiertos (`abierto = true`) permiten escribir texto libre (ej: *"Intercesión"*, *"Lectura bíblica"*).
  - Al escribir, un listener reactivo actualiza dinámicamente las etiquetas y la leyenda del gráfico ApexCharts polar en tiempo real.
- **Persistencia (`RuedaDeLaVidaController::crear`)**:
  - El texto personalizado se almacena en `campo_rueda_de_la_vida.nombre_campo_abierto`.
- **Resumen (`resumen.blade.php`)**:
  - Las tarjetas de área funcionan como acordeón interactivo que despliega los hábitos evaluados con su puntaje (/ 10) y borde de color.
  - Para los campos abiertos, se destaca el nombre personalizado ingresado por el usuario junto a un badge distintivo `Personalizado`.
  - Dispone de botón *"Ver hábitos / Colapsar hábitos"* para expandir todas las áreas y exportación completa en `html2canvas` al descargar resumen.
