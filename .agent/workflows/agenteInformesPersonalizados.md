---
name: agenteInformesPersonalizados
description: Agente para la gestión de Informes Personalizados (migración de MANANTIAL Laravel 4.2 a REDIL Cloud Laravel 12). Contiene el contexto completo, análisis de BD, funciones migradas y estado de implementación.
---

# Agente de Informes Personalizados

## Contexto del Proyecto

Este agente mantiene el contexto completo de la migración del módulo de **Informes Personalizados** desde la aplicación MANANTIAL (Laravel 4.2 / PHP 5.6 / PostgreSQL) hacia REDIL Cloud (Laravel 12 / PHP 8.4 / Multi-Tenant).

### Rutas de referencia
- **App legacy MANANTIAL:** `/Users/macosxdarwin/Desktop/MANANTIAL/app`
- **App destino REDIL Cloud:** `/Users/macosxdarwin/Desktop/REDIL-CLOUD`
- **Controlador original:** `MANANTIAL/app/controllers/GruposController.php` (línea 694 en adelante)
- **Vistas originales:** `MANANTIAL/app/views/grupos/informes-personalizados/`
- **Vistas Excel originales:** `MANANTIAL/app/views/grupos/excel/`

---

## Arquitectura del Módulo

### ¿Qué son los Informes Personalizados?

Son reportes Excel configurables por el usuario sobre la asistencia semanal de grupos. El flujo es:

1. El usuario selecciona un **grupo raíz** → el sistema toma todos los grupos del ministerio
2. El usuario elige un **rango de fechas** (mes, trimestre, semestre, año, o semana específica)
3. El usuario elige **campos de información** del grupo a mostrar
4. El usuario elige **clasificaciones de asistentes** a mostrar
5. El sistema construye una tabla HTML → la exporta como `.xlsx`

### Tabla `informes_personalizados` — cómo funciona

Cada registro tiene un campo `link` que apunta a la ruta del controller que lo atiende:

| id | nombre | link |
|---|---|---|
| 1 | Informe asistencia semanal CDV | `/grupos/informe-asistencia-semanal-personalizado` |
| 2 | Informe asistencia semanal a los grupos | `/grupos/informe-asistencia-semanal-grupos` |
| 4 | Informe asistencia obreros CDV | `/grupos/informe-asistencia-semanal-obreros-personalizado` |

### selector_id en `campos_informe_excel`

| selector_id | Para qué sirve |
|---|---|
| 1 | Campos de asistentes |
| 2 | Campos de asistentes (vinculación, tipo) |
| 4 | Campos de crecimiento espiritual |
| 5 | Campos de GRUPOS (informes personalizados) |
| 8 | Campos de OBREROS |

---

## Estado de Tablas en REDIL Cloud

| Tabla | Estado | Notas |
|---|---|---|
| `informes_personalizados` | ✅ CREADA | Migración: `2026_06_03_200957_create_informes_personalizados_table.php` |
| `informe_personalizado_tipo_usuario` | ✅ CREADA | Migración: `2026_06_03_200958_create_informe_personalizado_tipo_usuario_table.php` |
| `campos_informe_excel` | ✅ YA EXISTÍA | Tiene modelo `CampoInformeExcel`. Verificar que seeder incluya selector_id=8 |
| `semanas_deshabilitadas` | ✅ YA EXISTÍA | Modelo `SemanaDeshabilitada` |
| `clasificacion_asistente_reporte_grupo` | ✅ YA EXISTÍA | Tabla pivot en `ReporteGrupo` |
| `tipo_inasistencias` | ✅ YA EXISTÍA | Modelo `TipoInasistencia` |

---

## Archivos Creados en REDIL Cloud

### Migraciones (tenant)
- `database/migrations/tenant/2026_06_03_200957_create_informes_personalizados_table.php`
- `database/migrations/tenant/2026_06_03_200958_create_informe_personalizado_tipo_usuario_table.php`

### Modelos
- `app/Models/InformePersonalizado.php` — con `tiposUsuarios()` BelongsToMany

### Controllers
- `app/Http/Controllers/InformesPersonalizadosController.php` — Controller DEDICADO (no en GruposController)

### Exports (maatwebsite/excel v3)
- `app/Exports/InformeObrerosExport.php` — clase FromView

### Rutas (IMPLEMENTADAS):
- `GET /informes-personalizados` → `InformesPersonalizadosController@index`
- `GET /informes-personalizados/obreros/{id}` → `InformesPersonalizadosController@showInformeObreros`
- `POST /informes-personalizados/obreros/{id}/exportar` → `InformesPersonalizadosController@exportarInformeObreros`

### Vistas (Moviendo de Livewire directo a Vista+Livewire):
- `resources/views/contenido/paginas/informes-personalizados/index.blade.php` (Vista Blade que renderiza el Livewire de index).
- `resources/views/contenido/paginas/informes-personalizados/informe-asistencia-obreros.blade.php` (Formulario migrado y funcional).
- `resources/views/informes-personalizados/excel/informe-obreros.blade.php` (Vista para exportar).

---

## Informes Implementados

### ✅ InformeAsistenciaSemanalObreros (id=4)

**Métodos en `InformesPersonalizadosController`:**
- `index()` — Renderiza la vista base que carga el Livewire del listado.
- `showInformeObreros(int $id)` — GET, muestra el formulario.
- `exportarInformeObreros(Request $request, int $id)` — POST, genera el Excel.

**Mejoras aplicadas:**
- Se reemplazó el input manual de `grupo_id` por el componente `@livewire('grupos.grupos-para-busqueda')` para buscar inteligentemente el ministerio.
- Se agregó un event listener en JS para atrapar el evento `grupo-id-anidado` y poblar el `<input type="hidden" name="grupo_id">`.
- Validación Frontend en Javascript para que no permita el `submit` si no se ha seleccionado un grupo.
- El modelo legado `ClasificacionAsistenteReporteGrupo` fue actualizado a `ClasificacionAsistente`. (Nota: en este informe específico, las clasificaciones no se grafican en la tabla, solo llenan el `<select>` del filtro).
- **Refactorización de Base de Datos (`asistentes` -> `users`):**
  - Se eliminaron las referencias a la tabla obsoleta `asistentes`. Ahora todo interactúa con `users` y `App\Models\User`.
  - La relación de asistentes en un reporte grupal es `$reporte->usuarios()`, eliminando las consultas con `asistentes.id`.
  - El campo `tipo_asistente_id` se migró hacia `tipo_usuario_id`.
  - `CampoInformeExcelSeeder` ya no lee un `.sql` sin procesar; fue convertido a seeders nativos mediante `updateOrCreate`.
- **Dualidad en el Documento Excel (Dos Estilos de Visualización):**
  - Se modificó la interfaz para permitir al usuario elegir entre dos estilos de informe:
  - **1. Estilo por Bloques:** `construirTablaObreros` genera un formato agrupado. Un bloque con la info condensada del grupo (suma de `cantidad_asistencias` directa del reporte), seguido de bloques específicos para encargados y asistentes evaluando su asistencia individualmente.
  - **2. Estilo Plano / Condensado:** `construirTablaObrerosPlano` recrea fielmente el estilo original (legacy) de Manantial. Una lista plana donde se mezclan encargados y asistentes, y se visualiza su asistencia semanal (ej. `Ene 23-01 Semana 09`), imprimiendo `Sin Reporte` si el grupo no cargó el informe de esa semana.
  - Ambas funciones respetan las preferencias de `incluir_encargados` e `incluir_asistentes`.
- **Correcciones Lógicas Implementadas:**
  - Se solucionó el crash de `json_decode` sobre `informacion_encargado_grupo` ya que Eloquent, con la propiedad `$casts`, lo transforma automáticamente a array, logrando así una lectura segura.
  - Se implementó la resolución dinámica de propiedades vs métodos (como `edad()` en el modelo `User`) para evitar que al exportar el Excel, Eloquent intente cargarlos como si fueran relaciones de base de datos inexistentes.

---

## Informes PENDIENTES de implementar

| id | Nombre | Link original | Estado |
|---|---|---|---|
| 1 | Informe asistencia semanal CDV | `/grupos/informe-asistencia-semanal-personalizado` | ⏳ Pendiente |
| 2 | Informe asistencia semanal a los grupos | `/grupos/informe-asistencia-semanal-grupos` | ⏳ Pendiente |
| 3 | Informe de promedios de asistencia mensual | `/informes-grupo/informe-asistencia-mensual-grupos` | ⏳ Pendiente |

---

## Cambios API Aplicados (MANANTIAL → Laravel 12)

| Patrón antiguo | Patrón nuevo |
|---|---|
| `$_POST['campo']` | `$request->input('campo')` |
| `->lists('campo')` | `->pluck('campo')->toArray()` |
| `Helper::finalMes($anio, $mes)` | `Carbon::create($anio, $mes)->endOfMonth()->format('Y-m-d')` |
| `Excel::create(...)->export('xls')` | `Excel::download(new Export($data), 'nombre.xlsx')` |
| `{{$tablaCompleta}}` en vista Excel | `{!! $tablaCompleta !!}` (HTML crudo) |
| `DB::table('informes_personalizados')->first()` | `InformePersonalizado::findOrFail($id)` |

---

## Seeder

El `InformesPersonalizadosSeeder` se configuró para sembrar los informes dentro de la base de datos tenant y conectarlos al `tipo_informe` (si existiera). 

### Registros `selector_id=8` (campos obreros) de MANANTIAL:
*(NOTA: Estos deben estar en `CampoInformeExcelSeeder`, NO en `InformesPersonalizadosSeeder`)*
```sql
(63, 'tipo_asistente_id', 'Tipo Asistente',    8, 'asistentes.', 't', 'f', 63),
(64, 'grupo_id',          'Grupo Directo',      8, 'asistentes.', 't', 'f', 64),
(65, 'grupo_pertenece',   'Grupo',              8, 'grupos.',      't', 'f', 65),
```

---

## Notas Técnicas Importantes

1. **`gruposMinisterio()`** ya existe en `app/Models/Grupo.php` de REDIL Cloud (línea 170) — compatible.
2. La tabla HTML se construye completamente en el **controller** y la vista Excel solo la imprime con `{!! $tablaCompleta !!}`.
3. Los campos `tipo_de_campo` en `campos_extra_grupo` funcionan así: 1=texto, 2=número, 3=select simple, 4=select múltiple.
4. El campo `informacion_encargado_grupo` en `reporte_grupos` es un JSON con estructura `[{id, asistio}]`.
5. **ATENCIÓN PARA PRÓXIMOS INFORMES (CDV y Grupos):** En MANANTIAL la sumatoria de clasificaciones (ej: Niños, Jóvenes) se guardaba en el JSON `sumatoria_adicional_clasificacion` de `ReporteGrupo`. Ahora en REDIL Cloud esto se guarda mediante la tabla pivote `clasificacion_asistente_reporte_grupo`. La lógica para contar esto **debe re-escribirse** para los próximos reportes.

### ✅ Listado de Informes (Index)

**Componente Livewire y Vista:**
- `app/Livewire/InformesPersonalizados/Index.php`
- `resources/views/contenido/paginas/informes-personalizados/index.blade.php` (Blade Padre)

**Funcionalidad:**
- Lista todos los informes.
- Permite activar/desactivar un informe (cambio de estado).
- Permite asignar o quitar tipos de usuario (roles) al informe a través de un modal usando `$informeSeleccionado->tiposUsuarios()->sync()`.

