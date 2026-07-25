---
description: Carga el contexto y memoria del Agente de Matrículas (Admin y Estudiante)
---

1. Read the research notes `research_notes.md` in the brain directory for the architectural overview.
2. Read `app/Models/Matricula.php`, `app/Models/MatriculaHorarioMateriaPeriodo.php`, and `app/Models/HorarioMateriaPeriodo.php`.
3. Read `app/Services/MatriculaService.php` to understand availability logic.
4. Read `app/Livewire/Matricula/MatriculaModal.php` (Manual) and `app/Livewire/Carrito/EscuelasCarrito.php` (Self-service).
5. Adopt the persona: "Expert in Enrollment Systems (Matriculas & academic logic)".
6. Confirm to the user: "📑 **Agente de Matrículas Activado**. Conozco los flujos de Taquilla, Carrito, Sonda ZonaPagos, SoftDeletes y Gestión Administrativa de inscripciones."

### Arquitectura de Matrículas

*   **Matrícula (Pago)**: `app/Models/Matricula.php` maneja la referencia de pago, el periodo y cuenta con el trait `SoftDeletes` (`deleted_at` y `deleted_by`).
*   **Estado Académico (Seguimiento)**: `app/Models/MatriculaHorarioMateriaPeriodo.php` maneja si el alumno está cursando o aprobó (tabla física: `matricula_horario_materia_periodo`).
*   **Aprobaciones Históricas**: `app/Models/MateriaAprobadaUsuario.php` (materias) y la tabla `niveles_aprobado_usuario` (niveles) manejan homologaciones y aprobaciones permanentes.
*   **Horario específico**: `app/Models/HorarioMateriaPeriodo.php` es la instancia de clase con cupos limitados.

### Reglas de Negocio Críticas

1.  **Disponibilidad**: El `MatriculaService` filtra materias por prerrequisitos, tareas y pasos de crecimiento.
2.  **Integridad**: No se puede eliminar ni trasladar a un alumno que ya tenga **Asistencia** (`ReporteAsistenciaAlumnos`) o **Notas** (`AlumnoRespuestaItem`).
3.  **Cupos**: Se usa `lockForUpdate` del horario antes de decrementar cupos en la transacción.
4.  **Crecimiento**: Al matricularse (vía `MatriculaModal`), se asignan automáticamente los pasos de crecimiento marcados como "Al iniciar" en la configuración de la materia.
5.  **SoftDeletes y Trazabilidad Contable**: Al cancelar o eliminar una matrícula desde el módulo administrativo o por rechazo en ZonaPagos, la matrícula no se borra físicamente. En su lugar, se desvinculan los pivotes de clase (`matricula_horario_materia_periodo`) para liberar el cupo, y se registra la fecha (`deleted_at`) y el usuario responsable (`deleted_by`) para auditoría.

### Flujos Soportados

*   **Taquilla/Admin**: `Livewire\Taquilla\ProcesarMatriculaEscuela` y `Livewire\Matricula\MatriculaModal`.
*   **Auto-servicio**: `Livewire\Carrito\EscuelasCarrito`.
*   **Sonda de Verificación y Barrido**: `Console\Commands\VerificarPagosPendientes`.
*   **Traslados**: `Livewire\Matricula\TrasladoModal` (Admin) y `Livewire\Matricula\SolicitarTraslado` (Estudiante).
*   **Por Niveles**: `Http\Controllers\MatriculaNivelController` y `Livewire\Matricula\MatriculaNivelModal`.
*   **Gestión e Historial de Eliminadas**: `Http\Controllers\MatriculaController` (`gestionar`, `eliminarMatricula`, `historialEliminadas`).

### Especificaciones de Eliminación de Matrículas y SoftDeletes

1.  **Eliminación Administrativa**:
    *   Método: `MatriculaController::eliminarMatricula($matricula, $user)`.
    *   Verificación de Permisos: Requiere `escuelas.opcion_eliminar_materia` o `escuelas.opcion_eliminar_matricula`.
    *   Auto-Reparación en cPanel: Comprueba que existan las columnas `deleted_at` y `deleted_by` en la tabla `matriculas` en caliente.
    *   Alertas Diferenciadas (SweetAlert2): Si la matrícula posee compra/pago registrado por PSE ($XX.XXX), despliega una advertencia especial notificando que el registro contable se preservará en el historial.
2.  **Vista del Historial de Eliminadas**:
    *   Ruta: `/matriculas/historial-eliminadas/{user?}` (`matriculas.historialEliminadas`).
    *   Buscador Multi-Campo: Filtra por **Identificación del estudiante**, **Nombres/Apellidos**, **#ID Matrícula**, **Nombre del Periodo** y **Nombre de Materia**.
    *   Filtro por Periodo Académico (Select Desplegable) y opción de limpiar filtros.
    *   Visualización de usuario responsable (`deleted_by`), fecha/hora exacta de cancelación y referencia contable de compra/pago.

### Automatización de Roles y Efectos

1.  **Cambio de Rol al Iniciar**:
    *   Al matricular a un estudiante vía `MatriculaNivelService` (Niveles) o `MatriculaModal` (Materias), el sistema actualiza automáticamente el `tipo_usuario_id` del alumno al `tipo_usuario_inicial_id` configurado en el nivel/materia respectivo.
2.  **Trait `AplicaEfectosAprobacion`**: Centraliza los efectos secundarios de la culminación de materias (`ServicioValidacionPeriodo` y `ServicioValidacionMateriaPeriodo`).
    *   **Tareas de Consolidación**: Actualiza el estado de las tareas configuradas como "al culminar".
    *   **Pasos de Crecimiento**: Registra o actualiza el progreso en `CrecimientoUsuario`.
    *   **Roles y Tipos de Usuario**: Cambia el rol al `tipo_usuario_objetivo_id` de la materia, validando siempre la jerarquía por puntaje (no degrada rangos).
3.  **Aprobación Automática de Nivel**:
    *   Dentro del trait, tras aprobar una materia, el sistema verifica si el alumno ha completado **todas las materias obligatorias** de su nivel asociado.
    *   Si se completa el nivel, se crea el registro en `niveles_aprobado_usuario` y se aplica el cambio de rol al Tipo de Usuario Objetivo del nivel.

### Gestión de Imágenes y Portadas

*   **Materias**: Almacenadas en `[tenant]/img/materias/`. Fallback: `storage/global/img/escuelas/default.png`.
*   **Niveles (Grados)**: Almacenados en `[tenant]/img/niveles/`. Fallback: `storage/global/img/escuelas/default.png`.
*   **Rutas de Sistema**: Siempre usar `Storage::url($configuracion->ruta_almacenamiento . $path)` para archivos del tenant y `asset()` para archivos globales.
