---
description: Carga el contexto y memoria del Agente de Rueda de la Vida
---

1. Read the documentation file `_docs_agente/modulos/rueda_de_la_vida.md`.
2. Read the controller `app/Http/Controllers/RuedaDeLaVidaController.php` to refresh the creation and display logic.
3. Read the models `app/Models/RuedaDeLaVidaUser.php`, `app/Models/MetaUsuarioRv.php` and `app/Models/SeccionRv.php` to understand the core data relationships.
4. Read the main form view `resources/views/contenido/paginas/rueda-de-la-vida/nueva.blade.php` to understand the wizard and JS logic.
5. Adopt the persona: "Expert in Rueda de la Vida (Wheel of Life self-assessment module)".
6. Confirm to the user: "🌀 **Agente de Rueda de la Vida Activado**. Tengo cargado el contexto completo del módulo: wizard de secciones, gráficos polares, metas dinámicas de usuario, hábitos, historial y configuración multi-tenant."

---

## Contexto Rápido del Módulo

### Arquitectura Principal
- **Entidad central**: `RuedaDeLaVidaUser` (tabla `rueda_de_la_vida_user`) — un registro por cada vez que el usuario completa la rueda.
- **Secciones del wizard**: gestionadas por `SeccionRv` (tabla `secciones_rv`), ordenadas por `orden`.
- **Tipos de sección** (`TipoSeccionRv`): `contador` (hábitos calificables), `promedios` (resumen gráfico), `encuesta` (metas y hábitos en texto).
- **Campos calificables**: `CampoSeccionRv` (tabla `campos_seccion_rv`) — cada campo es un hábito con valor 0–10.
- **Configuración global**: `ConfiguracionRv::first()` — provee labels, nombre general y promedio mínimo.

### Tablas Pivote / Tablas de Detalle Clave
| Tabla                       | Relaciona                                          | Dato extra                              |
|-----------------------------|----------------------------------------------------|-----------------------------------------|
| `campo_rueda_de_la_vida`    | RuedaDeLaVidaUser ↔ CampoSeccionRv (pivote)        | `valor`, `nombre_campo_abierto`         |
| `metas_usuario_rv`          | RuedaDeLaVidaUser → MetaUsuarioRv (HasMany)        | `nombre`, `seccion_rv_id` (área)        |
| `habitos_usuario_rv`        | MetaUsuarioRv → HabitoUsuarioRv (HasMany)          | `nombre`                                |

> ⚠️ **Tablas obsoletas** (conservadas en BD, sin uso activo desde v2):
> `meta_rueda_de_la_vida`, `habitos_rueda_de_la_vida`, `metas`, `habitos_rueda_vida`.
> Fueron reemplazadas por `metas_usuario_rv` y `habitos_usuario_rv`. **No eliminar** las tablas;
> simplemente no se deben ejecutar los seeders de metas pre-configuradas (`SeccionRvSeeder` sección encuesta).

### Rutas Disponibles
- `GET /rueda-vida/gestor` — punto de entrada, redirige según historial.
- `GET /rueda-vida/nueva` — formulario wizard completo.
- `PATCH /rueda-vida/crear` — guarda el formulario.
- `GET /rueda-vida/historial` — historial paginado.
- `GET /rueda-vida/{rueda}/resumen` — detalle de una rueda específica.
- `GET /rueda-vida/finalizada` — pantalla de éxito.

### Convenciones de Names en el Formulario
- Campos calificables: `campo-{campo_id}-seccion-{seccion_id}`
- Texto del campo abierto: `campo-abierto-{campo_id}-seccion{seccion_id}` ← ⚠️ sin guión antes de "seccion"
- Metas: `inputMeta-{meta_id}`
- Hábitos de meta: `inputHabitoMeta-{habito_id}`
- Promedio general (oculto): `valorPromedioGeneralOculto`

### Notas Críticas
- ⚠️ Existe `RuedaDeLaVida` (tabla `rueda_de_la_vida`) y `RuedaDeLaVidaUser` (tabla `rueda_de_la_vida_user`). El controlador activo usa **`RuedaDeLaVidaUser`**. `RuedaDeLaVida` es posible legado.
- El campo `promedio_general` en `RuedaDeLaVidaUser` se calcula en JS (promedios de sección) y se envía como campo oculto.
- Los seeders (`SeccionRvSeeder`, `CampoSeccionRvSeeder`) usan `firstOrCreate` — son seguros para re-ejecutar.
- El campo abierto en cada sección tiene `nombre` vacío en BD (`nombre: ''`, `abierto: true`) y el usuario escribe su propio hábito.
- **Sección encuesta**: La UI dinámica (JS) reemplaza los metas pre-configuradas. El usuario crea sus propias metas en el último paso del wizard. Los inputs tienen names de array: `metas[i][nombre]`, `metas[i][seccion_rv_id]`, `metas[i][habitos][j]`.
- **Límites configurables**: `ConfiguracionRv.max_metas` y `ConfiguracionRv.max_habitos_por_meta` controlan cuántas metas/hábitos puede crear el usuario.
- **Modelos nuevos**: `MetaUsuarioRv` y `HabitoUsuarioRv` — siempre cargar con `->with(['habitos', 'seccion'])` para evitar N+1.
