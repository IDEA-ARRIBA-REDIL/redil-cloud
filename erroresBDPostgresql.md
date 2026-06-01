# 🗄️ Errores y Consideraciones de Base de Datos — REDIL Cloud (PostgreSQL)

> Revisión técnica de la estructura de migraciones. **Solo análisis, sin cambios aplicados.**
> Contexto: Laravel 11 + PostgreSQL (Laravel Cloud) + Eloquent ORM + Multi-Tenancy.
> Generado: 2026-05-28

---

## 📊 Resumen Ejecutivo

| Categoría | Hallazgos | Severidad |
|---|---|---|
| Índices faltantes en FK críticas | ~30+ columnas | 🔴 Alta |
| Foreign Keys sin `constrained()` | ~25+ columnas | 🔴 Alta |
| `double` en lugar de `decimal` para dinero | 3 columnas en `pagos` | 🔴 Alta |
| Unique constraints comentadas pero no implementadas | 2 tablas | 🟡 Media |
| Inconsistencias de tipo de dato en FK | ~15 columnas | 🟡 Media |
| Tablas duplicadas / redundantes | 3 casos | 🟡 Media |
| Desnormalización riesgosa | 4 casos | 🟡 Media |
| Columnas `text` para datos cortos | ~8 casos | 🟢 Baja |
| Campos de estado con `string` en lugar de `enum` | ~6 casos | 🟢 Baja |

---

## 🔴 ERROR 1 — Índices Faltantes en Foreign Keys (Crítico para Rendimiento)

**Este es el problema más impactante.**

En **PostgreSQL**, las FK **NO crean un índice automáticamente** (MySQL sí lo hace). Cada `JOIN` o consulta que filtre por estas columnas hará un _Sequential Scan_ (escaneo completo de la tabla) en lugar de un _Index Scan_.

---

### Tabla `users` — Sin índices en FK vitales

```php
// Todas estas columnas no tienen índice ni FK constrained:
$table->integer('tipo_usuario_id');
$table->integer('sede_id')->default(2);
$table->integer('estado_civil_id')->nullable();
$table->integer('tipo_vinculacion_id')->default(1);
$table->integer('barrio_id')->nullable();
$table->integer('localidad_id')->nullable();
$table->integer('pais_id')->nullable();
$table->integer('tipo_identificacion_id')->nullable();
```

**Impacto:** `users` es la tabla más consultada de toda la aplicación. Cualquier filtro por sede, tipo de usuario o estado civil hace un scan completo de la tabla.

**Índices que deberían existir:**
```php
$table->index('tipo_usuario_id');
$table->index('sede_id');
$table->index('deleted_at'); // para softDeletes
$table->index(['deleted_at', 'tipo_usuario_id']); // índice compuesto frecuente
```

---

### Tabla `grupos` — Sin ningún índice en FK

```php
$table->integer('tipo_grupo_id');  // Sin FK, sin índice
$table->integer('sede_id')->default(5); // Sin FK, sin índice
```

**Impacto:** Consultas como "traer grupos de una sede" o "grupos por tipo" harán scan completo.

---

### Tabla `reporte_grupos` — Tabla de crecimiento intenso, sin índices

```php
$table->integer('grupo_id');  // Sin FK constrained, sin índice ← 🔴 Crítico
// fecha tampoco tiene índice
```

**Impacto:** Esta tabla crece indefinidamente con cada reporte semanal. Sin índice en `grupo_id` y `fecha`, los dashboards de consolidación serán **exponencialmente más lentos** con el tiempo.

**Índices que deberían existir:**
```php
$table->index('grupo_id');
$table->index('fecha');
$table->index('sede_id');
$table->index(['grupo_id', 'fecha']); // para filtros combinados
```

---

### Tabla `reporte_reuniones` — Sin índices

```php
$table->integer('reunion_id'); // Sin FK constrained, sin índice ← 🔴 Crítico
// fecha sin índice
```

---

### Tabla `inscripciones` — FK como `integer` sin constrained ni índice

```php
$table->integer('user_id')->nullable();            // debería ser foreignId
$table->integer('actividad_categoria_id');          // Sin índice
$table->integer('compra_id')->nullable();           // Sin índice
```

---

### Tabla `pagos` — Tabla financiera sin índices

```php
$table->integer('compra_id')->nullable();           // Sin índice
$table->integer('tipo_pago_id')->nullable();        // Sin índice
$table->integer('estado_pago_id')->nullable();      // Sin índice
// fecha tampoco tiene índice → reportes financieros lentos
```

---

### Tabla `ingresos` — Tabla de finanzas crítica sin índices explícitos

```php
// Ninguna de estas tiene índice:
$table->unsignedBigInteger('tipo_ofrenda_id');
$table->unsignedBigInteger('caja_finanzas_id');
$table->unsignedBigInteger('user_id')->nullable();
$table->unsignedBigInteger('sede_id')->nullable();
// Tampoco: fecha
```

**Índices que deberían existir:**
```php
$table->index('fecha');
$table->index('sede_id');
$table->index('tipo_ofrenda_id');
$table->index('caja_finanzas_id');
```

---

### Tabla `horarios_materia_periodo` — FK declaradas como `integer` sin FK real

```php
$table->integer('materia_periodo_id'); // integer, no foreignId — tiene índice compuesto pero no FK real
$table->integer('horario_base_id');    // integer, no foreignId — sin FK real
```

---

## 🔴 ERROR 2 — `double` para Valores Monetarios (Riesgo de Redondeo)

En la tabla `pagos`:

```php
$table->double('valor');       // ❌ Error grave
$table->double('comision');    // ❌ Error grave
$table->double('valor_neto');  // ❌ Error grave
```

`double` es punto flotante de 64 bits. Para dinero **genera errores de redondeo**:
- `10.00 * 3` puede resultar en `29.999999999999996`
- Comparaciones directas entre valores pueden fallar

**Corrección:** Usar `decimal(10, 2)` siempre para dinero. Las otras tablas financieras (`ingresos`, `matriculas`) sí lo hacen correctamente.

---

## 🔴 ERROR 3 — Foreign Keys sin `constrained()` (~25 columnas)

Laravel crea `foreignId()` como `unsignedBigInteger` con restricción. Usar `integer` para referencias es inconsistente y **rompe la integridad referencial a nivel de base de datos** (solo queda a nivel de aplicación).

### Casos más graves

| Tabla | Columna problemática | Tipo actual | Debería ser |
|---|---|---|---|
| `users` | `tipo_usuario_id` | `integer` | `foreignId()->constrained('tipo_usuarios')` |
| `users` | `sede_id` | `integer` | `foreignId()->constrained('sedes')` |
| `grupos` | `tipo_grupo_id` | `integer` | `foreignId()->constrained('tipo_grupos')` |
| `grupos` | `sede_id` | `integer` | `foreignId()->constrained('sedes')` |
| `reporte_grupos` | `grupo_id` | `integer` | `foreignId()->constrained('grupos')` |
| `reporte_reuniones` | `reunion_id` | `integer` | `foreignId()->constrained('reuniones')` |
| `pagos` | `compra_id` | `integer` | `foreignId()->constrained('compras')` |
| `pagos` | `tipo_pago_id` | `integer` | `foreignId()->constrained('tipos_pago')` |
| `inscripciones` | `user_id` | `integer` | `foreignId()->constrained('users')` |
| `calificaciones` | `sistema_calificacion_id` | `integer` | `foreignId()->constrained(...)` |
| `actividades` | `tipo_actividad_id` | `integer` | `foreignId()->constrained('tipos_actividad')` |
| `actividades` | `periodo_id` | `integer` | `foreignId()->constrained('periodos')` |
| `horarios_materia_periodo` | `materia_periodo_id` | `integer` | `foreignId()->constrained('materia_periodo')` |
| `periodos` | `sistema_calificaciones_id` | `integer` | `foreignId()->constrained(...)` |
| `periodos` | `tipo_corte_id` | `integer` | `foreignId()->constrained('tipo_cortes')` |

**Riesgo:** Sin `constrained()`, puedes tener **huérfanos** en la base de datos — registros que apuntan a IDs que ya no existen — sin que PostgreSQL lo detecte ni lo bloquee.

---

## 🟡 ERROR 4 — Unique Constraints Comentadas pero No Implementadas

### Tabla `matriculas`

```php
// El código comenta el problema pero NO lo implementa:
// "Un usuario no debería tener dos órdenes de matrícula/pago
//  para el mismo horario."
//
// FALTA:
// $table->unique(['user_id', 'horario_materia_periodo_id']);
```

### Tabla `matricula_horario_materia_periodo`

```php
// El código comenta:
// "Un ALUMNO solo puede tener un registro de estado académico
//  para un horario_materia_periodo_id específico."
//
// FALTA el unique compuesto:
// $table->unique(['user_id', 'horario_materia_periodo_id'], 'alumno_horario_unique');
```

**Riesgo:** Sin estos constraints a nivel DB, es posible insertar duplicados aunque la lógica de la app lo intente prevenir. Un bug en cualquier parte del código o una importación masiva podría crear registros duplicados silenciosamente.

---

## 🟡 ERROR 5 — Tablas Duplicadas / Migraciones Redundantes

### Caso 1: `albumes` vs `albumnes` (typo)

```
2025_03_13_154709_create_albumes_table.php   → tabla 'albumes'
2025_03_13_154709_create_albumnes_table.php  → tabla 'albumnes' (typo en nombre)
```

Dos archivos con el mismo timestamp. `albumnes` es claramente un error tipográfico de `albumes`. Verificar cuál tabla se usa realmente en los modelos y si `albumnes` existe en producción.

---

### Caso 2: `reuniones_rangos_edad` vs `reuniones_rangos_edades`

```
2025_02_20_162956_create_reuniones_rangos_edades_table.php → 'reuniones_rangos_edades'
2025_02_20_212514_create_reuniones_rangos_edad_table.php   → 'reuniones_rangos_edad'
```

Dos migraciones para tablas casi idénticas creadas el mismo día. Verificar si ambas existen en producción y cuál modelo las referencia.

---

### Caso 3: `matriculas_nivel` — Migration duplicada

```
2026_02_10_210003_create_matriculas_nivel_table.php
2026_03_15_174500_create_matriculas_nivel_table.php
```

Dos migraciones intentan crear la misma tabla `matriculas_nivel`. La segunda **falla silenciosamente** o genera error en `php artisan migrate:fresh`.

---

## 🟡 ERROR 6 — Desnormalización con Riesgos

### Campos `ultimo_reporte_*` en `users`

```php
$table->dateTime('ultimo_reporte_grupo')->default('2016-01-01 05:00:01');
$table->dateTime('ultimo_reporte_grupo_auxiliar')->default('2016-01-01 05:00:01');
$table->dateTime('ultimo_reporte_reunion')->default('2016-01-01 05:00:01');
$table->dateTime('ultimo_reporte_reunion_auxiliar')->default('2016-01-01 05:00:01');
```

Son datos derivados (calculados desde otras tablas) almacenados en `users` como caché. **El riesgo es la inconsistencia** si el proceso que los actualiza falla o si hay una inserción directa en `reporte_grupos` sin pasar por el observer/listener. Si el objetivo es rendimiento, es aceptable **siempre que haya un proceso confiable (Observer o Job) que los sincronice.**

---

### JSON desnormalizados en `reporte_grupos`

```php
$table->json('informacion_del_grupo')->nullable();
$table->json('informacion_encargado_grupo')->nullable();
$table->json('encargados_ascendentes')->nullable();
$table->json('ids_grupos_ascendentes')->nullable();
$table->json('sumatoria_adicional_clasificacion')->nullable();
```

Estos JSON son snapshots del estado del grupo al momento del reporte — correcto para auditoría. El problema es que **no son consultables eficientemente** por Eloquent. Si necesitas filtrar reportes por encargado o tipo de grupo, tienes que cargar todos los JSON y procesarlos en PHP (N+1 potencial).

**Alternativa si se necesitan consultas:** En PostgreSQL, los campos `json` se almacenan como `jsonb` y soportan índices `GIN`. Si se consultan frecuentemente, considerar un índice GIN o extraer los campos más buscados a columnas propias.

---

### `grupos.inactivo` — Semántica invertida

```php
$table->boolean('inactivo')->default(1); // default 1 = inactivo por defecto
```

El campo está en negativo (`inactivo`) con `default(1)`, lo que significa "inactivo por defecto". Esto es semánticamente confuso. Preferible `activo` con `default(false)` o `estado` con un enum.

---

## 🟡 ERROR 7 — Inconsistencias de Tipos de Datos para FK

### Mezcla de `integer` y `unsignedBigInteger` para IDs relacionales

En `ingresos`:
```php
$table->unsignedBigInteger('tipo_ofrenda_id');      // ✅ correcto
$table->unsignedBigInteger('user_id')->nullable();   // ✅ correcto
$table->integer('centro_de_costos_ingresos_id');     // ❌ inconsistente, debería ser unsignedBigInteger
```

En `reporte_reuniones`:
```php
$table->integer('predicador')->nullable();           // ❌ es un user_id, debería ser unsignedBigInteger
$table->integer('predicador_diezmos')->nullable();   // ❌ igual
$table->integer('autor_creacion')->nullable();       // ❌ igual
```

**Regla:** Toda columna que sea FK o que referencie un `id` de otra tabla debe ser `unsignedBigInteger` (o `foreignId()` que lo hace automáticamente). Mezclar `integer` (que acepta negativos) con `unsignedBigInteger` puede causar problemas en joins.

---

## 🟢 ERROR 8 — Campos de Estado como `string` en lugar de `enum`

### `matriculas.estado_pago_matricula`

```php
$table->string('estado_pago_matricula')->default('pendiente');
// Valores posibles no documentados en la migración
```

### `matricula_horario_materia_periodo.estado_aprobacion`

```php
$table->string('estado_aprobacion')->default('cursando');
// Valores: 'aprobado', 'no_aprobado', 'retirado_oficialmente', 'cursando'
```

**Alternativas mejores:**
- `enum(['cursando', 'aprobado', 'no_aprobado', 'retirado_oficialmente'])` — más seguro a nivel DB
- `tinyInteger` con constantes en el modelo — más eficiente en espacio
- Un Enum de PHP 8.1 respaldado en el cast del modelo

---

## 🟢 ERROR 9 — Columnas `text` para Datos de Longitud Corta/Media

```php
// reporte_reuniones:
$table->text('url');        // varchar(2048) comunica mejor la intención
$table->text('iframe');     // varchar suficiente

// actividades:
$table->text('label_destinatario')->nullable(); // ¿realmente necesita TEXT ilimitado?

// inscripciones:
$table->json('json_campos_adicionales')->nullable(); // ✅ json está bien para datos variables
```

> En PostgreSQL, `text` y `varchar` tienen rendimiento idéntico en almacenamiento, pero usar `varchar` con un límite razonable comunica la intención del dato y puede prevenir datos inesperadamente grandes.

---

## 🔍 Resumen por Módulo — Índices Recomendados

### Módulo Escuelas (LMS)

| Tabla | Columnas sin índice | Query afectada |
|---|---|---|
| `matriculas` | `user_id`, `periodo_id`, `escuela_id` | Matrículas de un alumno en un periodo |
| `alumno_respuesta_items` | `calificador_user_id` | Calificaciones pendientes del maestro |
| `item_corte_materia_periodo` | ✅ Tiene índices | — |
| `horarios_materia_periodo` | ✅ Tiene índice compuesto | — |
| `materia_periodo` | ✅ Tiene índice compuesto | — |

### Módulo Grupos / Consolidación

| Tabla | Columnas sin índice | Query afectada |
|---|---|---|
| `reporte_grupos` | `grupo_id`, `fecha`, `sede_id` | Dashboard de consolidación |
| `integrantes_grupo` | `grupo_id`, `user_id` | Miembros del grupo |
| `encargados_grupo` | `grupo_id`, `user_id` | Encargados del grupo |
| `asistencia_grupos` | `grupo_id`, `user_id` | Lista de asistencia |

### Módulo Reuniones

| Tabla | Columnas sin índice | Query afectada |
|---|---|---|
| `reporte_reuniones` | `reunion_id`, `fecha` | Historial de reuniones |
| `asistencia_reuniones` | `reporte_reunion_id`, `user_id` | Lista de asistentes |
| `reservas_reuniones` | `user_id`, `reporte_reunion_id` | Reservas activas |

### Módulo Finanzas

| Tabla | Columnas sin índice | Query afectada |
|---|---|---|
| `ingresos` | `fecha`, `sede_id`, `tipo_ofrenda_id` | Reportes por período |
| `pagos` | `compra_id`, `estado_pago_id`, `fecha` | Estado de pagos |
| `inscripciones` | `user_id`, `actividad_categoria_id` | Inscripciones activas |

---

## 📋 Plan de Corrección Priorizado

### 🔴 Prioridad Alta — Impacto Inmediato en Rendimiento

- [ ] Agregar índices en `reporte_grupos`: `grupo_id`, `fecha`, `sede_id`
- [ ] Agregar índices en `users`: `tipo_usuario_id`, `sede_id`, `deleted_at`
- [ ] Agregar índices en `ingresos`: `fecha`, `sede_id`, `tipo_ofrenda_id`, `caja_finanzas_id`
- [ ] Agregar índices en `pagos`: `compra_id`, `estado_pago_id`, `fecha`
- [ ] Agregar índices en `inscripciones`: `user_id`, `actividad_categoria_id`
- [ ] Corregir `double` → `decimal(10, 2)` en tabla `pagos` (riesgo de redondeo monetario)

### 🟡 Prioridad Media — Segunda Fase

- [ ] Agregar `unique(['user_id', 'horario_materia_periodo_id'])` en `matriculas`
- [ ] Agregar `unique(['user_id', 'horario_materia_periodo_id'])` en `matricula_horario_materia_periodo`
- [ ] Investigar tablas duplicadas: `albumnes`/`albumes`
- [ ] Investigar tablas duplicadas: `reuniones_rangos_edad`/`reuniones_rangos_edades`
- [ ] Resolver `matriculas_nivel` con dos migraciones que crean la misma tabla
- [ ] Homologar tipos de FK: `integer` → `unsignedBigInteger` en columnas que referencian IDs
- [ ] Agregar índices en `grupos`: `tipo_grupo_id`, `sede_id`
- [ ] Agregar índices en `reporte_reuniones`: `reunion_id`, `fecha`

### 🟢 Prioridad Baja — Refactoring Futuro

- [ ] Cambiar campos de estado `string` a `enum` donde aplique
- [ ] Evaluar si los campos `ultimo_reporte_*` en `users` siguen siendo necesarios
- [ ] Revisar semántica de `grupos.inactivo` vs `activo`
- [ ] Documentar valores posibles de los campos `estado` en código o migración

---

## 💡 Consideraciones Especiales para PostgreSQL

A diferencia de MySQL, PostgreSQL tiene particularidades que afectan directamente a este proyecto:

### 1. FK no crean índice automáticamente
```sql
-- En MySQL, esto crea el índice automáticamente.
-- En PostgreSQL, NO. Hay que crearlo manualmente:
ALTER TABLE reporte_grupos ADD INDEX idx_grupo_id (grupo_id);
-- O en la migración:
$table->index('grupo_id');
```

### 2. Búsqueda de texto con `LIKE` no usa índices normales
Para búsquedas por nombre de usuario en PostgreSQL:
```sql
-- Esto NO usa índice:
SELECT * FROM users WHERE primer_nombre LIKE '%Juan%';

-- Solución: Extensión pg_trgm + índice GIN
CREATE EXTENSION IF NOT EXISTS pg_trgm;
CREATE INDEX users_nombre_trgm_idx ON users USING gin (primer_nombre gin_trgm_ops);
```

### 3. `json` en Laravel se almacena como `jsonb` en PostgreSQL
Los campos `json()` de las migraciones se guardan como `jsonb` en PostgreSQL, lo que **sí soporta índices GIN**:
```sql
-- Para consultar dentro del JSON de reporte_grupos:
CREATE INDEX rg_ids_grupos_gin ON reporte_grupos USING gin (ids_grupos_ascendentes);
```

### 4. Partial Indexes — muy eficientes para soft deletes y estados
```sql
-- Solo indexa usuarios activos (no soft-deleted):
CREATE INDEX users_active_tipo_usuario_idx
  ON users (tipo_usuario_id)
  WHERE deleted_at IS NULL;

-- Solo reportes no aprobados:
CREATE INDEX rg_no_aprobados_idx
  ON reporte_grupos (grupo_id, fecha)
  WHERE aprobado IS NULL OR aprobado = false;
```

### 5. `ILIKE` para búsqueda case-insensitive
```sql
-- Para búsquedas por nombre en PostgreSQL:
SELECT * FROM users WHERE primer_nombre ILIKE '%juan%';
-- Asegurarse de que el índice soporte esto con pg_trgm
```

---

## 🎯 Diagnóstico en Una Frase

> El problema número uno es que **`reporte_grupos` y `users` no tienen índices en sus FK más usadas**, lo que significa que cada consulta del dashboard de consolidación y cada listado de usuarios hace un _Sequential Scan_ completo — esto **empeorará exponencialmente** con el crecimiento de datos en cada tenant.
