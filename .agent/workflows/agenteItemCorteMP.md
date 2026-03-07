---
description: Agente para la gestión de Ítems de Corte Materia Periodo (Evaluaciones del Maestro)
---

1. **Contexto**:

   - Este agente se encarga de asistir en la creación, edición y gestión de `ItemCorteMateriaPeriodo`.
   - Estos ítems son las actividades evaluativas (talleres, exámenes, etc.) asignadas a un `HorarioMateriaPeriodo` específico.
   - Los ítems pueden provenir de una plantilla (`ItemPlantilla`) o ser creados específicamente por el maestro para ese grupo.

2. **Reglas de Negocio**:

   - **Totalidad del Porcentaje**: La suma de los porcentajes de los ítems _calificables_ (`habilitar_entregable` o con porcentaje > 0) dentro de un corte debe verificar el 100% (o el porcentaje asignado al corte, dependiendo de la configuración global, pero usualmente se maneja que los ítems suman el 100% de la nota del corte).
   - **Edición de Plantillas**: Los ítems que vienen de plantilla (`item_plantilla_id` != null) pueden tener restricciones de edición según permisos. Los ítems creados por el maestro (`item_plantilla_id` == null) son totalmente editables por él.
   - **Eliminación**: No se pueden eliminar ítems que ya tengan calificaciones (`AlumnoRespuestaItem`) asociadas.

3. **Funcionalidades Clave**:

   - **Listado**: Vista de tarjetas (similar a `GestionItemPlantillas`) agrupadas por Corte.
   - **Creación/Edición**: Modal con editor de texto enriquecido (Quill/TinyMCE) para el contenido.
   - **Validación**: Al guardar, verificar que los porcentajes no excedan el límite permitido.

4. **Ubicación de Archivos**:

   - Lógica Livewire: `app/Livewire/Escuelas/GestionItemsCorteMateriaPeriodo.php`
   - Vista Blade: `resources/views/livewire/escuelas/gestion-items-corte-materia-periodo.blade.php`
   - Modelo: `app/Models/ItemCorteMateriaPeriodo.php`

5. **Interacción**:
   - El maestro accede a esta gestión desde su panel de clases/horarios.
