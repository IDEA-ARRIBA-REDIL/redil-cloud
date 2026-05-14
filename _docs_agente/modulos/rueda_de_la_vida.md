# Módulo: Rueda de la Vida (RDV)

Este documento describe la arquitectura completa, la base de datos, el flujo de usuario y los modelos del módulo "Rueda de la Vida".

---

## 1. Propósito del Módulo

La **Rueda de la Vida** es una herramienta de autodiagnóstico espiritual y de hábitos. El usuario califica sus hábitos en distintas áreas de su vida (del 1 al 10), obtiene promedios por sección, visualiza su estado general mediante gráficos polares (ApexCharts), y luego escribe sus metas y hábitos a mejorar. El resultado queda guardado en el historial del usuario.

---

## 2. Arquitectura de Base de Datos

### 2.1 Tablas Principales

#### `tipos_seccion_rv` — Tipos de sección
Define los comportamientos posibles de cada sección del formulario.

| Campo       | Tipo    | Descripción                                      |
|-------------|---------|--------------------------------------------------|
| `nombre`    | string  | `contador`, `promedios`, `encuesta`              |
| `min`       | int     | Valor mínimo permitido                           |
| `max`       | int     | Valor máximo permitido (por defecto 10)          |
| `validacion`| bool    | Si la sección tiene validación                   |
| `resumen`   | bool    | Si la sección muestra un resumen                 |
| `encuesta`  | bool    | Si la sección es tipo encuesta (metas/hábitos)   |
| `url_imagen`| string  | Imagen asociada (nullable)                        |

#### `secciones_rv` — Secciones del formulario
Cada sección corresponde a un paso en el wizard de la Rueda de la Vida. Tiene SoftDeletes.

| Campo                      | Descripción                                        |
|----------------------------|----------------------------------------------------|
| `titulo_barra`             | Texto del navbar                                   |
| `tipo_seccion_id`          | FK a `tipos_seccion_rv`                            |
| `icono`                    | Clase de ícono Tabler (ej. `ti ti-cloud-heart`)    |
| `orden`                    | Orden de aparición en el wizard                    |
| `titulo_steper`            | Subtítulo del paso                                 |
| `nombre_seccion`           | Nombre visible de la sección                       |
| `subtitulo_seccion`        | Texto descriptivo                                  |
| `color`                    | Color hexadecimal de la sección                    |
| `promedio_minimo`          | Valor mínimo esperado de promedio (ej. 6)         |
| `max`                      | Valor máximo del input (heredado de tipo_seccion)  |
| `label_*`                  | Labels configurables para botones y promedios      |

**Secciones predeterminadas (seeder):**
1. Espiritual (`tipo_seccion_id: 1` = contador)
2. Física (contador)
3. Intelectual (contador)
4. Familiar (contador)
5. Laboral y financiero (contador)
6. Emocional (contador)
7. Resumen promedios (`tipo_seccion_id: 2` = promedios)
8. Escribe tus metas y hábitos (`tipo_seccion_id: 3` = encuesta)

#### `campos_seccion_rv` — Campos por sección
Cada campo es un hábito calificable dentro de una sección tipo `contador`.

| Campo          | Descripción                                              |
|----------------|----------------------------------------------------------|
| `nombre`       | Nombre del hábito (ej. "Oración", "Ejercicio")           |
| `abierto`      | `true` = campo libre (el usuario escribe su propio hábito) |
| `seccion_rv_id`| FK a `secciones_rv`                                      |
| `orden`        | Orden del campo dentro de la sección                     |
| `color`        | Color hexadecimal para el gráfico polar                  |

> Cada sección tipo `contador` tiene **5 campos fijos** (`abierto: false`) + **1 campo abierto** (`abierto: true`) donde el usuario escribe su propio hábito.

#### `rueda_de_la_vida_user` — Registro del usuario (Modelo: `RuedaDeLaVidaUser`)
Tabla principal que guarda cada vez que el usuario completa la Rueda.

| Campo              | Descripción                               |
|--------------------|-------------------------------------------|
| `usuario_id`       | FK al usuario autenticado                 |
| `fecha`            | Fecha de realización (`Y-m-d`)            |
| `promedio_general` | Promedio calculado de todos los promedios |

#### `campo_rueda_de_la_vida` — Pivote: campos calificados
Relaciona cada `RuedaDeLaVidaUser` con los `CampoSeccionRv` y guarda el valor dado.

| Campo                  | Descripción                                   |
|------------------------|-----------------------------------------------|
| `rueda_de_la_vida_id`  | FK a `rueda_de_la_vida_user`                  |
| `campos_seccion_rv_id` | FK a `campos_seccion_rv`                      |
| `valor`                | Valor numérico asignado (0–10)                |
| `nombre_campo_abierto` | Texto libre si el campo es `abierto: true`    |

#### `metas` — Metas del formulario de encuesta
Las metas son las preguntas tipo texto de la sección `encuesta`. Están asociadas a una `SeccionRv` tipo encuesta.

| Campo       | Descripción                         |
|-------------|-------------------------------------|
| `nombre`    | Nombre de la meta (ej. "Meta 1")    |
| `requerida` | Si el campo es obligatorio          |

#### `meta_rueda_de_la_vida` — Pivote: respuestas a metas
| Campo                | Descripción                     |
|----------------------|---------------------------------|
| `rueda_de_la_vida_id`| FK a `rueda_de_la_vida_user`    |
| `metas_id`           | FK a `metas`                    |
| `valor`              | Respuesta en texto del usuario  |

#### `habitos_rueda_vida` — Hábitos configurados (Modelo: `HabitosRv`)
Los hábitos son los sub-inputs de texto de cada meta en la sección encuesta.

| Campo       | Descripción                            |
|-------------|----------------------------------------|
| `nombre`    | Nombre del hábito                      |
| `metas_id`  | FK a `metas`                           |
| `requerido` | Si el campo es obligatorio             |

#### `habitos_rueda_de_la_vida` — Pivote: respuestas a hábitos
| Campo                  | Descripción                        |
|------------------------|------------------------------------|
| `rueda_de_la_vida_id`  | FK a `rueda_de_la_vida_user`       |
| `habitos_rueda_vida_id`| FK a `habitos_rueda_vida`          |
| `valor`                | Respuesta en texto del usuario     |

#### `rueda_de_la_vida` — Tabla alternativa (Modelo: `RuedaDeLaVida`)
> ⚠️ Existe un modelo `RuedaDeLaVida` (tabla `rueda_de_la_vida`) y un modelo `RuedaDeLaVidaUser` (tabla `rueda_de_la_vida_user`). El controlador usa **`RuedaDeLaVidaUser`** como tabla de registro activo. `RuedaDeLaVida` parece ser una versión previa/legado.

#### `configuracion_rv` — Configuración del módulo (Modelo: `ConfiguracionRv`)
Tabla de configuración global del módulo (nombre, labels, promedio mínimo general).

| Campo clave             | Descripción                                          |
|-------------------------|------------------------------------------------------|
| `nombre_general`        | Nombre visible del módulo (ej. "Rueda de la Vida")   |
| `label_promedio_general`| Label de promedio en vistas (ej. "Promedio")         |
| `promedio_general`      | Valor mínimo del promedio general para marcar éxito  |
| `nombre_habitos`        | Label del bloque de hábitos en la encuesta           |

---

## 3. Modelos y Relaciones

```
RuedaDeLaVidaUser
  ├── campos()     → BelongsToMany(CampoSeccionRv)  via campo_rueda_de_la_vida
  │                   withPivot: valor, nombre_campo_abierto
  ├── metas()      → BelongsToMany(Metas)            via meta_rueda_de_la_vida
  │                   withPivot: valor, withTimestamps
  └── habitos()    → BelongsToMany(HabitosRv)        via habitos_rueda_de_la_vida
                      withPivot: valor, withTimestamps

SeccionRv
  ├── tipoSeccion() → BelongsTo(TipoSeccionRv)
  ├── campos()      → HasMany(CampoSeccionRv)
  ├── metas()       → HasMany(Metas)
  └── promedio($ruedaVidaId) → Calcula promedio de campos para una rueda específica

CampoSeccionRv
  ├── seccion()        → BelongsTo(SeccionRv)
  └── ruedasDeLaVida() → BelongsToMany(RuedaDeLaVida)

Metas
  ├── habitos()        → HasMany(HabitosRv)
  └── ruedasDeLaVida() → BelongsToMany(RuedaDeLaVida)

HabitosRv
  ├── metas()          → BelongsTo(Metas)
  └── ruedasDeLaVida() → BelongsToMany(RuedaDeLaVida)
```

---

## 4. Rutas

Definidas en `routes/app.php` (líneas 814–821), protegidas por autenticación y permiso `rueda_de_la_vida.item_rueda_de_la_vida`:

| Método | URI                              | Nombre                    | Acción                      |
|--------|----------------------------------|---------------------------|-----------------------------|
| GET    | `/rueda-vida/gestor`             | `ruedaDeLaVida.gestor`    | Redirige a historial o bienvenida |
| GET    | `/rueda-vida/bienvenida`         | `ruedaDeLaVida.bienvenida`| Vista de bienvenida          |
| GET    | `/rueda-vida/nueva`              | `ruedaDeLaVida.nueva`     | Formulario wizard            |
| PATCH  | `/rueda-vida/crear`              | `ruedaDeLaVida.crear`     | Guarda los datos del formulario |
| GET    | `/rueda-vida/historial`          | `ruedaDeLaVida.historial` | Historial paginado del usuario |
| GET    | `/rueda-vida/{rueda}/resumen`    | `ruedaDeLaVida.resumen`   | Detalle/resumen de una rueda |
| GET    | `/rueda-vida/finalizada`         | `ruedaDeLaVida.finalizada`| Pantalla de éxito            |

---

## 5. Controlador: `RuedaDeLaVidaController`

### `gestor()`
- Verifica permiso `rueda_de_la_vida.item_rueda_de_la_vida`.
- Si el usuario tiene ruedas → redirige a `historial`.
- Si no tiene → redirige a `bienvenida`.

### `bienvenida()`
- Muestra la pantalla de bienvenida con información y botón "Comenzar".
- Usa `ConfiguracionRv::first()` y `Configuracion::first()`.

### `nueva()`
- Carga todas las secciones ordenadas por `orden asc`.
- Carga secciones tipo contador (`tipo_seccion_id: 1`) con sus campos (para los gráficos polares).
- Pasa `$maximoId` (ID de la última sección) al wizard JS.

### `crear(Request $request)` — ⚠️ Lógica Central
1. Obtiene el usuario autenticado vía rol activo.
2. Crea el registro `RuedaDeLaVidaUser` con `promedio_general` del campo oculto `valorPromedioGeneralOculto`.
3. Itera sobre **secciones tipo contador** y sus **campos**:
   - Si `campo.abierto == true`: guarda `valor` + `nombre_campo_abierto`.
   - Si no: solo guarda `valor`.
   - Nombre del input: `campo-{campo_id}-seccion-{seccion_id}`.
   - Nombre del texto abierto: `campo-abierto-{campo_id}-seccion{seccion_id}`.
4. Itera sobre **metas** y guarda en `meta_rueda_de_la_vida`.
   - Input: `inputMeta-{meta_id}`.
5. Para cada meta, itera sobre sus **hábitos** y guarda en `habitos_rueda_de_la_vida`.
   - Input: `inputHabitoMeta-{habito_id}`.
6. Redirige a `ruedaDeLaVida.finalizada`.

### `resumen(RuedaDeLaVidaUser $rueda)`
- Muestra promedios por sección usando `$seccion->promedio($rueda->id)`.
- Muestra respuestas de metas con `$rueda->metas()->wherePivot('metas_id', $meta->id)->first()`.
- Muestra respuestas de hábitos con `$rueda->habitos()->wherePivot('habitos_rueda_vida_id', $habito->id)->first()`.

### `historial()`
- Lista paginada (10 por página) de `RuedaDeLaVidaUser` del usuario autenticado, ordenada `asc`.

---

## 6. Vistas

| Vista                     | Descripción                                             |
|---------------------------|---------------------------------------------------------|
| `bienvenida.blade.php`    | Pantalla split: texto + imagen de fondo (Storage global)|
| `nueva.blade.php`         | Wizard multi-paso con gráficos ApexCharts polares      |
| `historial.blade.php`     | Listado de ruedas completadas con promedio y fecha      |
| `resumen.blade.php`       | Detalle de una rueda: promedios + metas + hábitos       |
| `exitosa.blade.php`       | Pantalla de confirmación tras guardar                   |

Todas extienden `layouts/blankLayout` y usan SweetAlert2 + ApexCharts via Vite.

---

## 7. Lógica JavaScript de la Vista `nueva.blade.php`

### Wizard de Pasos
- Usa `#step-N` para mostrar/ocultar con clase `d-none`.
- Botones `.next-step` y `.prev-step` controlan la navegación.
- El último paso cambia el botón a `type="submit"`.
- Se bloquea Enter excepto en el último paso.

### Gráficos Polares por Sección (`#polarChart-{seccion_id}`)
- Un gráfico ApexCharts de tipo `polarArea` por cada sección tipo `contador`.
- Los colores vienen de `$campo->color`.
- Al cambiar inputs, `procesarCambioInput()` actualiza el gráfico y el promedio visible.

### Gráfico General (`#polarPromedioGeneral`)
- Se actualiza al pasar de paso con `actualizarGrafico()`.
- Lee los inputs `.promedioGeneral` (ocultos) para calcular el promedio global.
- Actualiza `#valorPromedioGeneralOculto` (enviado en el form) y `#valorPromedioGeneralVisible`.

### Validación de Rango
- Los inputs tienen `min="0" max="{{$seccion->max}}"`.
- La función `procesarCambioInput()` clampea el valor: `Math.max(min, Math.min(max, value))`.

---

## 8. Notas y Consideraciones Importantes

### ⚠️ Dos modelos para la misma entidad
- `RuedaDeLaVida` → tabla `rueda_de_la_vida` (posible legado).
- `RuedaDeLaVidaUser` → tabla `rueda_de_la_vida_user` (tabla activa usada por el controlador).
- Las relaciones en `RuedaDeLaVida` y `RuedaDeLaVidaUser` son idénticas. Ambos usan la pivote `campo_rueda_de_la_vida`.

### ⚠️ Inconsistencia en el name del campo abierto
En el controlador `crear()`, el input del nombre abierto se lee como:
```php
$request->input("campo-abierto-" . $campo->id . "-seccion" . $seccion->id)
// Falta el guión antes de "seccion"
```
Pero en la vista se define como:
```html
name="campo-abierto-{{$campo->id}}-seccion{{$seccion->id}}"
// También sin guión (son consistentes entre sí)
```

### Configuración Multi-Tenant
- `ConfiguracionRv::first()` — se usa en todas las vistas para leer labels y promedios mínimos.
- Las secciones y campos son globales para todos los usuarios del tenant.

### Permiso de acceso
- Se requiere `rueda_de_la_vida.item_rueda_de_la_vida` verificado en `gestor()`.

---

## 9. Flujo Completo del Usuario

```
Acceso a /rueda-vida/gestor
    ↓
¿Tiene ruedas previas?
    ├─ Sí → /rueda-vida/historial
    └─ No → /rueda-vida/bienvenida
                ↓
         Click "Comenzar"
                ↓
         /rueda-vida/nueva (Wizard)
         [Paso 1–6: Secciones tipo contador]
         → Usuario asigna valores 0–10 a cada hábito
         → Gráfico polar se actualiza en tiempo real
         → Se calcula promedio por sección
         [Paso 7: Resumen promedios]
         → Gráfico general con todos los promedios
         [Paso 8: Encuesta metas/hábitos]
         → Usuario escribe sus metas y hábitos de mejora
         [Botón Guardar] → PATCH /rueda-vida/crear
                ↓
         /rueda-vida/finalizada
                ↓
         Usuario puede ver /rueda-vida/historial
         → Click "ver mis metas" → /rueda-vida/{id}/resumen
```
