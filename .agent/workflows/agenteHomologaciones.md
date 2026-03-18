---
description: Carga el contexto y memoria del Agente de Homologaciones y Culminación de Pasos de Crecimiento
---

# Agente de Homologaciones

Este agente se especializa en la gestión del proceso de **Homologaciones**, permitiendo validar materias previas de alumnos y sincronizar automáticamente su progreso en el sistema de **Pasos de Crecimiento**.

## 1. Directivas Críticas del Usuario

- **SIN COMANDOS ARTISAN**: No intentes ejecutar `php artisan` (migraciones, seeders, etc.). El usuario se encarga de ejecutarlos manualmente si es necesario.
- **SIN VALIDACIONES AUTOMÁTICAS**: No ejecutes scripts de validación automática.
- **COMENTARIOS OBLIGATORIOS**: Cada bloque de código debe estar detalladamente comentado.
- **IDIOMA**: Toda la comunicación y documentación debe ser estrictamente en **ESPAÑOL**.
- **TRANSACCIONES**: Cualquier cambio que afecte homologaciones y crecimiento debe envolverse en `DB::beginTransaction()`.

## 2. Contexto de Arquitectura

- **Módulos de Homologación**: `MateriaAprobadaUsuario` (Materias) y `NivelAprobadoUsuario` (Niveles/Grados).
- **Modelo de Referencia**: `app/Models/Materia.php` y `app/Models/NivelEscuela.php`.
- **Integración de Crecimiento**: `app/Models/CrecimientoUsuario.php`.
- **Integración de Tareas**: `app/Models/TareaConsolidacionUsuario.php`.
- **Controlador**: `app/Http/Controllers/HomologacionController.php`.
- **Componente Livewire**: `app/Livewire/Homologaciones/GestionarHomologaciones.php`.

## 3. Lógica de Homologación, Crecimiento y Tareas

El proceso de homologación dispara efectos secundarios automatizados:

### A. Homologación de Materias
1.  **Registro**: Crea en `MateriaAprobadaUsuario`.
2.  **Crecimiento**: Completa el paso de crecimiento asociado (`al_iniciar = false`) en `CrecimientoUsuario` (Estado 3).

### B. Homologación de Niveles (Grados)
1.  **Registro**: Crea en `NivelAprobadoUsuario`.
2.  **Crecimiento**: Completa TODOS los pasos de crecimiento del nivel marcados como "al culminar" (`al_iniciar = false`).
3.  **Tareas**: Completa las tareas de consolidación definidas en `NivelTareaCulminada` mediante `TareaConsolidacionUsuario`.
4.  **Perfil de Usuario**: Actualiza `User.tipo_usuario_id` si el nivel tiene definido un `tipo_usuario_objetivo_id`.

## 4. UI y Flujo de Gestión

- **Modos**: El administrador puede alternar entre el modo "Materias" y "Niveles".
- **Buscador de Alumnos**: Utiliza el componente dinámico `@livewire('usuarios.usuarios-para-busqueda')`.
- **Filtro de Escuela**: El administrador debe seleccionar una `Escuela` para listar las materias o niveles homologables.
- **Visualización**: Solo se muestran como "Homologables" los ítems que el alumno NO tiene aprobados ni homologados previamente.
- **Modal de Homologación**: Requiere obligatoriamente:
    - Selección de **Sede**.
    - **Observación** de al menos 10 caracteres.

## 5. Protocolo de Trabajo

1.  **Análisis de Relaciones**: Antes de modificar la lógica, revisa las tablas pivote `materia_paso_crecimiento`, `nivel_paso_crecimiento` y `nivel_tarea_culminada`.
2.  **Regla de Transacción**: Toda homologación DEBE ejecutarse dentro de una transacción (`DB::beginTransaction()`) para asegurar que el registro de aprobación y los efectos secundarios (tareas, crecimiento) sean atómicos.
3.  **Verificación de Historial**: Al depurar, consulta tanto `materias_aprobada_usuario` como `niveles_aprobado_usuario`.
4.  **Feedback UI**: Usa `$this->dispatch('notificacion', [...])` con SweetAlert.

---
*Nota: Este agente asegura que el historial académico del alumno sea consistente con su avance espiritual/administrativo en los Pasos de Crecimiento.*
