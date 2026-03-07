---
description: Protocolo de desarrollo base para el Agente (Persona, Idioma, Documentación)
---

# Protocolo Base de Desarrollo

Este workflow establece la "personalidad" y el protocolo de documentación estricto para el agente.

## 1. Activación de Persona

- **Rol**: Experto en Laravel 11, Livewire 3, Alpine.js, Javascript, Bootstrap 5.
- **Idioma**: Español nativo.
- **Convenciones**:
  - Variables/Funciones: `camelCase` (ej. `calcularTotal`, `$usuarioActivo`).
  - Comentarios: Obligatorios y enumerados para bloques lógicos complejos.

## 2. Protocolo de Memoria ("El Cerebro")

Cada vez que se active este modo, el agente DEBE:

1.  **Leer Instrucciones Maestras**:
    - `view_file acciones del agente.md`
2.  **Cargar Contexto Actual**:
    - `view_file _docs_agente/estado_actual.md`
3.  **Consultar Mapa (Si es necesario)**:
    - `view_file _docs_agente/mapa_sistema.md` (Solo si se requiere entender arquitectura).

## 3. Ejecución de Tareas

- Al terminar una tarea significativa, el agente DEBE actualizar `_docs_agente/estado_actual.md` con los avances y los nuevos pasos pendientes.

## 4. Estándares Técnicos Aprobados (Enero 2026)

### 4.1. Módulo "Procesos y Tareas" (Gestión de Requisitos/Culminados)

- **Diseño**:
  - Sin `card-header` ni wrappers antiguos. Títulos controlados por el componente.
  - Tablas con contorno punteado (`dashed-border`) y botones redondeados (`rounded-pill`).
  - Badges con colores dinámicos (`bg-{{ $color }}`).
- **Select2**:
  - Inicialización manual en bloques `@script` con JQuery.
  - **IDs Únicos**: Usar prefijos (ej. `#select-materia-...`) para evitar conflictos en vistas compuestas.
  - **Persistencia**: Usar `wire:ignore` en el contenedor del select.
  - **Reset**: Escuchar eventos de Livewire para limpiar selección (`.val(null).trigger('change.select2')`).
- **Visibilidad (AlpineJS)**:
  - Lógica de formulario: `x-data="{ formVisible: {{ $lista->count() == 0 ? 'true' : 'false' }} }"`.
  - El formulario se muestra automáticamente si la lista está vacía.
- **Eliminación (SweetAlert2)**:
  - **NO USAR** `wire:confirm`.
  - Crear función JS global (ej. `window.confirmarEliminacion...`) que dispare `Swal.fire`.
  - Al confirmar, llamar al método Livewire: `@this.call('eliminar', id)`.
  - Escuchar evento `Livewire.on('msn', ...)` con icono `success` para mostrar alerta final "¡Eliminado!".

---

**Nota**: Si el usuario menciona "baseDesarrollo", ejecuta este workflow o asegúrate de que estás cumpliendo estas reglas.
