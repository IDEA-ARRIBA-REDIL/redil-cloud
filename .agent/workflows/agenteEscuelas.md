---
description: Carga el contexto y memoria del Agente de Escuelas
---

1. Read the documentation file `_docs_agente/modulos/escuelas.md` to load the domain knowledge.
2. Read `app/Models/Matricula.php`, `app/Models/Escuela.php`, and `app/Models/Periodo.php` to refresh code structure.
3. Adopt the persona: "Expert in the Schools Module (Academic Logic)".
4. Confirm to the user: "🎓 **Agente de Escuelas Activado**. Tengo cargado el contexto de lógica académica, matrículas y periodos."

### Proceso Técnico de Maestros

1.  **Creación**:

    - Se gestiona en `MaestroController::guardar`.
    - Asigna el rol de 'Docente' (o el seleccionado) al usuario mediante `$usuario->roles()->attach(...)` con campos adicionales en la tabla pivote (`activo`, `dependiente`).
    - Crea el registro en la tabla `maestros` vinculado al usuario.

2.  **Asignación de Horarios**:

    - Se gestiona en el componente Livewire `AsignarHorarioModal`.
    - Usa la relación `horariosMateriaPeriodo()` en el modelo `Maestro` para vincular clases específicas (`HorarioMateriaPeriodo`).

3.  **Roles**:
    - Los roles elegibles para maestros deben tener la propiedad `es_maestro` en `true` en la tabla `roles`.

### Proceso de Traslados

1.  **Solicitud (Estudiante)**:

    - Ruta: `/escuelas/{usuario}/solicitar-traslado`.
    - Controlador: `MatriculaController@solicitarTraslado`.
    - Lógica: `App\Livewire\Matricula\SolicitarTraslado`. Valida reglas de negocio (asistencia, notas) y crea registro en `traslados_matricula_log` con estado `pendiente`.

2.  **Gestión (Admin)**:
    - Ruta: `/escuelas/matriculas/solicitudes-traslado`.
    - Controlador: `MatriculaController@gestionarSolicitudesTraslado`.
    - Lógica: `App\Livewire\Matricula\GestionarSolicitudesTraslado`.
    - **Aprobación**: Transacción DB que actualiza `matricula.horario_materia_periodo_id`, ajusta cupos, mueve registros académicos y envía correo `TrasladoAprobado`.
    - **Rechazo**: Actualiza estado a `rechazado`, guarda motivo y envía correo `TrasladoRechazado`.
