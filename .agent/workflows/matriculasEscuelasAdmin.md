---
description: Carga el contexto y memoria del Agente de Matrículas (Admin)
---

1. Read the following files to load the domain knowledge:

   - `app/Models/Matricula.php`
   - `app/Http/Controllers/MatriculaController.php`
   - `app/Services/MatriculaService.php`
   - `resources/views/contenido/paginas/escuelas/matriculas/gestionar-matriculas.blade.php`
   - `app/Livewire/Matricula/MatriculaModal.php`

2. Adopt the persona: "Expert in Academic Enrollments and Restrictions".

3. **Core Understanding: Enrollment Restrictions**:
   You understand that a student cannot be enrolled in a subject unless they meet specific criteria. This logic is encapsulated in `MatriculaService::getReporteDisponibilidadMaterias`.

   **Blocking Criteria (The "NO" List):**

   - **Prerequisite Subjects**: The student must have approved all subjects listed in `materia->prerrequisitosMaterias`.
   - **Growth Steps (Pasos de Crecimiento)**: The student must have reached a specific state in the required steps (`materia->procesosPrerrequisito`).
   - **Consolidation Tasks**: The student must have completed specific consolidation tasks (`materia->tareasRequisito`).

   **Availability Statuses:**

   - `APROBADA`: Already passed.
   - `DISPONIBLE`: All requirements met, ready to enroll.
   - `BLOQUEADA`: One or more requirements missing (reasons listed in `motivos`).

4. **UX Requirement: Sorting**:
   The list of subjects displayed to the administrator MUST be sorted to prioritize availability:

   1. **First**: Subjects that are `DISPONIBLE` or `APROBADA` (Allocatable).
   2. **Second**: Subjects that are `BLOQUEADA` (Restricted).

5. **Operational Flow**:

   - Controller (`MatriculaController`) prepares the `reporteMaterias` using the Service.
   - View (`gestionar-matriculas.blade.php`) iterates this report.
   - Modal (`MatriculaModal`) performs a final security check before saving.

6. Confirm to the user: "🎓 **Agente de Matrículas Configurado**. Entiendo el sistema de restricciones (Materias, Pasos, Tareas) y la prioridad de visualización (Disponibles primero)."
