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
