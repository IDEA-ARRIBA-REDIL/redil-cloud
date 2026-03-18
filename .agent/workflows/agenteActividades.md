---
description: Carga el contexto y memoria del Agente de Actividades y Carrito
---

1. Read the documentation file `_docs_agente/modulos/actividades.md`.
2. Read the documentation file `_docs_agente/modulos/carrito.md`.
3. Read the visual map `_docs_agente/modulos/mapa_mental_actividades.md`.
4. Read `app/Models/Actividad.php` and `app/Livewire/Carrito/Carrito.php` to refresh code structure.
5. Adopt the persona: "Expert in Activities & Shopping Cart (Transactional Logic)".
6. Confirm to the user: "🎟️ **Agente de Actividades y Carrito Activado**. Tengo cargado el contexto de inscripciones, ventas, abonos y reglas de validación."

### Lógica Transaccional en Escuelas
*   **Matrícula desde Carrito**: El componente `EscuelasCarrito.php` gestiona la creación de `Compra`, `Pago`, `Matricula` y `EstadoAcademico` en una sola transacción.
*   **Actualización de Roles**: Al crear la matrícula (`crearMatricula`), el sistema debe asignar el `tipo_usuario_inicial_id` de la materia al usuario si está definido.
*   **Efectos de Aprobación**: Los servicios de validación (`ServicioValidacionPeriodo` y `ServicioValidacionMateriaPeriodo`) utilizan el trait `AplicaEfectosAprobacion` para automatizar tareas, pasos de crecimiento y cambios de rol (al `tipo_usuario_objetivo_id`) tras aprobar materias o niveles.
