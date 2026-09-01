---
description: Carga el contexto y memoria del Agente de Matrículas (Admin y Estudiante)
---

1. Read the research notes `research_notes.md` in the brain directory for the architectural overview.
2. Read `app/Models/Matricula.php`, `app/Models/MatriculaHorarioMateriaPeriodo.php`, and `app/Models/HorarioMateriaPeriodo.php`.
3. Read `app/Services/MatriculaService.php` to understand availability logic.
4. Read `app/Livewire/Matricula/MatriculaModal.php` (Manual) and `app/Livewire/Carrito/EscuelasCarrito.php` (Self-service).
5. Adopt the persona: "Expert in Enrollment Systems (Matriculas & academic logic)".
6. Confirm to the user: "📑 **Agente de Matrículas Activado**. Conozco los flujos de Taquilla, Carrito y Gestión Administrativa de inscripciones."

### Arquitectura de Matrículas

*   **Matrícula (Pago)**: `app/Models/Matricula.php` maneja la referencia de pago y el periodo.
*   **Estado Académico (Seguimiento)**: `app/Models/MatriculaHorarioMateriaPeriodo.php` maneja si el alumno está cursando o aprobó.
*   **Aprobaciones Históricas**: `app/Models/MateriaAprobadaUsuario.php` (materias) y la tabla `niveles_aprobado_usuario` (niveles) manejan homologaciones y aprobaciones permanentes.
*   **Horario específico**: `app/Models/HorarioMateriaPeriodo.php` es la instancia de clase con cupos limitados.

### Reglas de Negocio Críticas

1.  **Disponibilidad**: El `MatriculaService` filtra materias por prerrequisitos, tareas y pasos de crecimiento.
2.  **Integridad**: No se puede eliminar ni trasladar a un alumno que ya tenga **Asistencia** (`ReporteAsistenciaAlumnos`) o **Notas** (`AlumnoRespuestaItem`).
3.  **Cupos**: Se usa `lockForUpdate` del horario antes de decrementar cupos en la transacción.
4.  **Crecimiento**: Al matricularse (vía `MatriculaModal`), se asignan automáticamente los pasos de crecimiento marcados como "Al iniciar" en la configuración de la materia.

### Flujos Soportados

*   **Taquilla/Admin**: `Livewire\Taquilla\ProcesarMatriculaEscuela` y `Livewire\Matricula\MatriculaModal`.
*   **Auto-servicio**: `Livewire\Carrito\EscuelasCarrito`.
*   **Traslados**: `Livewire\Matricula\TrasladoModal` (Admin) y `Livewire\Matricula\SolicitarTraslado` (Estudiante).
*   **Por Niveles**: `Http\Controllers\MatriculaNivelController` y `Livewire\Matricula\MatriculaNivelModal`.

### Especificaciones de Matrícula por Niveles

1.  **Filtrado por Periodo**: Las materias se cargan vía `MateriaPeriodo` para asegurar que solo se inscriban ítems activos en el periodo actual.
2.  **Validación Previa**: Se debe invocar `MatriculaService::getReporteDisponibilidadNiveles` antes de abrir el modal para validar prerrequisitos y bloqueos.
3.  **Transaccionalidad**: El `MatriculaNivelService` envuelve la creación de `MatriculaNivel`, múltiples `Matricula` individuales y `MatriculaHorarioMateriaPeriodo` en una sola transacción con `lockForUpdate`.
4.  **Automatización de Pasos**: Al completar la inscripción por nivel, el sistema asigna automáticamente los pasos de crecimiento configurados como "Al iniciar" en cada materia.
5.  **Verificación de Aprobación**: El sistema debe verificar la aprobación tanto en `matriculas_nivel` (flujo normal) como en `niveles_aprobado_usuario` (homologaciones/histórico) para determinar si un nivel está "APROBADO".

41. ### Automatización de Roles y Efectos (Nuevo)
42. 
43. 1.  **Cambio de Rol al Iniciar**:
44.     *   Al matricular a un estudiante via `MatriculaNivelService` (Niveles) o `MatriculaModal` (Materias), el sistema actualiza automáticamente el `tipo_usuario_id` del alumno al `tipo_usuario_inicial_id` configurado en el nivel/materia respectivo.
45. 2.  **Trait `AplicaEfectosAprobacion`**: Centraliza los efectos secundarios de la culminación de materias (`ServicioValidacionPeriodo` y `ServicioValidacionMateriaPeriodo`).
46.     *   **Tareas de Consolidación**: Actualiza el estado de las tareas configuradas como "al culminar".
47.     *   **Pasos de Crecimiento**: Registra o actualiza el progreso en `CrecimientoUsuario`.
48.     *   **Roles y Tipos de Usuario**: Cambia el rol al `tipo_usuario_objetivo_id` de la materia, validando siempre la jerarquía por puntaje (no degrada rangos).
49. 3.  **Aprobación Automática de Nivel**:
50.     *   Dentro del trait, tras aprobar una materia, el sistema verifica si el alumno ha completado **todas las materias obligatorias** de su nivel asociado.
51.     *   Si se completa el nivel, se crea el registro en `niveles_aprobado_usuario` y se aplica el cambio de rol al Tipo de Usuario Objetivo del nivel.
52. 
53. ### Gestión de Imágenes y Portadas
54. 
55. *   **Materias**: Almacenadas en `[tenant]/img/materias/`. Fallback: `storage/global/img/escuelas/default.png`.
56. *   **Niveles (Grados)**: Almacenados en `[tenant]/img/niveles/`. Fallback: `storage/global/img/escuelas/default.png`.
57. *   **Rutas de Sistema**: Siempre usar `Storage::url($configuracion->ruta_almacenamiento . $path)` para archivos del tenant y `asset()` para archivos globales.
