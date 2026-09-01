---
description: Carga el contexto y memoria del Agente de Actividades y Carrito
---

1. Read `app/Models/Peticion.php` and `database/migrations/tenant/2024_04_03_161708_create_peticiones_table.php` to understand the data structure.
2. Read `app/Http/Controllers/PeticionController.php` for filtering logic and status-based indicators.
3. Read `app/Livewire/Peticiones/GestionarPeticiones.php` and its view `resources/views/livewire/peticiones/gestionar-peticiones.blade.php`.
4. Adopt the persona: "Expert in Prayer Petitions Management (LMS / Pastoral Care)".
5. Confirm to the user: "🙏 **Agente de Peticiones Activado**. Tengo cargado el contexto de gestión de peticiones, seguimiento y respuestas."

### Lógica de Estados
*   **1 - Pendiente**: Estado inicial de las peticiones creadas.
*   **3 - En proceso**: Antes llamado 'Resuelto'. Indica que se está dando seguimiento a la petición.
*   **2 - Cerrada**: Antes llamado 'Finalizada'. La petición ha sido atendida completamente.

### Convenciones de UI
*   **Offcanvas**: La gestión de respuestas se realiza a través de un `offcanvas-end` con ID `#modalResponder`.
*   **Editor Quill**: Tiene una altura fija de `200px` con scroll interno para evitar colapsos visuales.
*   **Footer de Acciones**: El pie de página con los botones de "Guardar" y "Cancelar" debe permanecer fijo (`flex-shrink-0`) mientras el cuerpo del formulario (`offcanvas-body`) es el que maneja el desplazamiento (`overflow-y-auto`).
*   **Backdrops**: Se ha implementado un listener de JS en la vista Livewire para limpiar manualmente cualquier rastro de `offcanvas-backdrop` o `modal-backdrop` al cerrar el panel.

### Comunicaciones
*   **Email**: Las respuestas pueden activar el envío de correos electrónicos informativos al peticionario.
*   **WhatsApp**: Se generan enlaces directos para chatear con el peticionario desde la vista de gestión.
