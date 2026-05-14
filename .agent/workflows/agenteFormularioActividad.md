---
description: Carga el contexto y memoria del Agente de Formulario Dinámico de Actividades
---

1. Read the documentation file `_docs_agente/modulos/actividades.md`.
2. Read `app/Livewire/Actividades/FormularioActividad.php` and its associated view `resources/views/livewire/actividades/formulario-actividad.blade.php`.
3. Adopt the persona: "Expert in Dynamic Activity Forms & User Data Collection".
4. Confirm to the user: "📋 **Agente de Formulario de Actividades Activado**. Tengo cargado el contexto de relaciones dinámicas, tipos de datos, opciones y lógica de drag-and-drop."

### Contexto de Construcción de Formularios
*   **Gestión Estructural**: El componente `FormularioActividad` administra la colección de datos extra para las actividades (`ElementoFormularioActividad`).
*   **Tipos de Datos Soportados**: Gestiona tipos de datos basados en `TipoElementoFormularioActividad` (texto, numérico, archivos, imágenes, selección única/múltiple, entre otros).
*   **Replicación de Formularios**: Capacidad para realizar un despliegue rápido, copiando la estructura íntegra de preguntas y opciones de una actividad previamente configurada a una nueva.
*   **Interactividad**: Todo el manejo del DOM y el orden de renderizado es por Drag and Drop basado en `Sortable.js`, comunicando el nuevo `orden` al backend vía disparadores (`dispatch`).
*   **Edición Asíncrona**: Cada elemento puede ser configurado a fondo en una vista lateral envolvente (Offcanvas), permitiendo restricciones granulares como peso de archivos, validación por dimensiones, limitaciones longitudinales (min/max), y visualización condicional (e.g. `visible_asistencia`).

### Reglas Críticas de Implementación (Multi-Tenant & Checkout)
*   **Gestión de Archivos**: No utilizar el trait `WithFileUploads` de Livewire en entornos multi-tenant restrictivos. Implementar subida manual vía Alpine.js hacia un controlador dedicado que gestione el almacenamiento en el disco del tenant.
*   **Validación Estricta**: Al validar respuestas obligatorias, **NUNCA** usar `empty()`. PHP considera que `"0"` está vacío. Usar comparaciones estrictas: `($val === null || $val === '' || $val === [])`.
*   **Vinculación de Opciones**: En elementos de selección (`unica_respuesta`, `multiple_respuesta`), vincular el `value` de los inputs al `ID` de la opción de la base de datos para asegurar persistencia única.
*   **Visualización en Dashboard**: 
    *   Para opciones, resolver los IDs a texto legible usando `firstWhere('id', ...)` sobre la colección de opciones del elemento.
    *   Para archivos e imágenes, generar URLs públicas mediante el helper `tenant_asset()` concatenando la ruta de almacenamiento del tenant.
*   **Lógica de Guardado**: Al procesar el guardado del formulario en el backend (Livewire o Controller), iterar sobre los *elementos de la actividad* (las preguntas) en lugar de solo sobre el array de respuestas. Esto garantiza que archivos subidos asíncronamente se procesen correctamente incluso si no hay una respuesta de texto asociada.
*   **Optimización de Consultas**: Siempre aplicar Eager Loading en los dashboards (`elemento.opciones`, `user`, `compra`) para evitar consultas redundantes (N+1).
*   **Feedback de Usuario**: Incluir indicadores visuales de "Subiendo..." y confirmaciones de éxito al procesar archivos fuera del ciclo de vida estándar de Livewire.
