---
description: Carga el contexto, memoria técnica y reglas de negocio del Agente de Homologaciones (Individual y Masiva), Culminación de Pasos de Crecimiento y Consolidado Académico por Escuela
---

# Agente de Homologaciones y Consolidado Académico

Este agente se especializa en la gestión integral del proceso de **Homologaciones** (tanto individual como masiva por Excel), el progreso en **Pasos de Crecimiento**, **Tareas de Consolidación**, **Tipos de Usuario (Roles)**, **Hitos** y el **Dashboard de Consolidado Académico** por Escuela.

---

## 1. Directivas Críticas del Proyecto

- **SIN COMANDOS ARTISAN AUTOMÁTICOS**: No intentes ejecutar `php artisan` (migraciones, seeders, etc.). El usuario se encarga de ejecutarlos manualmente si es necesario.
- **SIN VALIDACIONES AUTOMÁTICAS**: No ejecutes scripts de validación automática.
- **COMENTARIOS OBLIGATORIOS**: Cada bloque de código debe estar detalladamente comentado.
- **IDIOMA**: Toda la comunicación y documentación debe ser estrictamente en **ESPAÑOL**.
- **TRANSACCIONES ATÓMICAS**: Cualquier cambio que afecte homologaciones, crecimiento y tareas DEBE envolverse en `DB::beginTransaction()` y `DB::commit()`.
- **SINCRONIZACIÓN SFTP**: Recordar al usuario guardar los archivos en su editor (`Ctrl+S`) si su plugin SFTP no sube los cambios automáticamente al servidor VPS.

---

## 2. Arquitectura y Componentes

### Modelos y Servicios Principales

- **Homologaciones y Calificaciones**: `App\Models\MateriaAprobadaUsuario` (Materias) y `App\Models\NivelAprobadoUsuario` (Niveles).
- **Estructura Académica**: `App\Models\Materia.php`, `App\Models\NivelEscuela.php` y `App\Models\Escuela.php`.
- **Automatizaciones de Crecimiento**: `App\Models\CrecimientoUsuario.php` y `App\Models\EstadoPasoCrecimientoUsuario.php`.
- **Automatizaciones de Tareas**: `App\Models\TareaConsolidacionUsuario.php`, `App\Models\EstadoTareaConsolidacion.php` y tablas hijas `HistorialTareaConsolidacionUsuario`, `BitacoraConsolidacion`.
- **Gestión de Roles**: `App\Models\TipoUsuario.php`, `App\Models\User.php` (`promoverTipoUsuario()`) y `model_has_roles`.
- **Disparador de Hitos**: `App\Services\HitoTriggerService.php` (`onMateriaAprobada()`).

### Rutas y Controladores

- **Controladores**:
  - `App\Http\Controllers\HomologacionController.php`
  - `App\Http\Controllers\ConsolidadoAcademicoController.php`
- **Rutas**:
  - Individual: `/escuelas/homologaciones` (`escuelas.homologaciones`)
  - Masiva: `/escuelas/homologaciones/masivas` (`escuelas.homologaciones.masivas`)
  - Consolidado Académico: `/escuelas/consolidado-academico` (`escuelas.consolidado-academico`)

### Componentes Livewire

- **Gestión Individual**: `App\Livewire\Homologaciones\GestionarHomologaciones.php`
- **Gestión Masiva**: `App\Livewire\Homologaciones\HomologacionesMasivas.php`
- **Consolidado Académico**: `App\Livewire\Escuelas\ConsolidadoAcademico.php`

### Clases de Exportación e Importación Excel (`maatwebsite/excel`)

- **Plantilla Masiva**: `App\Exports\PlantillaHomologacionesMasivasExport.php` (4 columnas).
- **Parser Masivo**: `App\Imports\HomologacionesMasivasImport.php`.
- **Reporte de Errores**: `App\Exports\ReporteErroresHomologacionExport.php`.
- **Consolidado Académico**: `App\Exports\ConsolidadoAcademicoExport.php` (Sábana de notas con colores).

---

## 3. Lógica de Homologaciones y Automatizaciones

### A. Estados de Homologación

- `1`: **Aprobado** (Homologación aprobada con nota final).
- `2`: **En proceso** (Cursando o en trámite de validación).
- `0`: **Reprobado** (No superó los criterios de homologación).

### B. Efectos al Guardar como "Aprobado" (`1`)

1. **Registro Académico**: Guarda `MateriaAprobadaUsuario` (con `nota_final` y `creditos_aprobados` solo si la escuela es por materias) o `NivelAprobadoUsuario` (con `nota_final` sin créditos).
2. **Tareas de Consolidación**: Ejecuta `TareaConsolidacionUsuario::procesarTarea(...)` asignando el estado configurado en `MateriaTareaCulminada` o `NivelTareaCulminada`.
3. **Pasos de Crecimiento**: Ejecuta `CrecimientoUsuario::procesarPaso(...)` asignando el estado configurado en la relación pivote (`al_iniciar = false`).
4. **Tipo de Usuario (Rol)**: Llama a `User::promoverTipoUsuario($tipoUsuarioId, forzar: false)`. **Respeta los pesos/jerarquías** (solo asciende si el nuevo rol tiene puntaje mayor o igual al actual).
5. **Hitos**: Dispara `HitoTriggerService::onMateriaAprobada(...)`.

### C. Efectos al Modificar o Guardar como "En proceso" (`2`) o "Reprobado" (`0`)

- **Tareas de Consolidación**:
  - Si se elige _Sin asignar_: Elimina de forma segura las dependencias en cascada (`historial()`, `bitacora()`) y luego la tarea (`$tar->delete()`), evitando errores foráneos `SQLSTATE[23503]`.
  - Si se elige un _Estado específico_: Actualiza o crea la tarea con dicho estado.
- **Pasos de Crecimiento**:
  - Si se elige _Sin asignar_: Elimina el registro de `CrecimientoUsuario` (`$crec->delete()`).
  - Si se elige un _Estado específico_: Actualiza o crea el registro con dicho estado.
- **Tipo de Usuario (Rol)**:
  - Dispone de un **interruptor (switch)** interactivo:
    - _Respeta pesos (`forzar = false`)_: Solo aplica si el rol es igual o superior al actual.
    - _Forzado (`forzar = true`)_: Asigna directamente el rol ignorando la jerarquía (permite degradación).

---

## 4. Dashboard de Consolidado Académico por Escuela

### A. Criterios de Listado

- **Población**: Solo aparecen estudiantes que posean al menos un registro en `materia_aprobada_usuario` o `nivel_aprobado_usuario` para la escuela activa.
- **Modo Dinámico**:
  - Escuela con niveles agrupados o niveles existentes $\rightarrow$ Evalúa **Niveles**.
  - Escuela tradicional $\rightarrow$ Evalúa **Materias**.

### B. Filtro de Obligatorias vs Opcionales

- Evalúa la columna `caracter_obligatorio` (`boolean`) presente en `materias` y `niveles_escuelas`:
  - **Solo obligatorias**: calcula el pensum base y porcentaje sobre las materias indispensables para graduarse.
  - **Todas (obligatorias + opcionales)**: evalúa el catálogo académico completo.
  - **Solo opcionales**: evalúa materias complementarias.

### C. Matriz Interactiva (Sábana de Notas)

- Cabeceras fijas con badges de obligatoriedad.
- Celdas con código de color:
  - 🟢 **Verde suave**: Aprobada (con nota final o 'Aprobado', más flag `H` si es homologada).
  - 🟡 **Amarillo suave**: En proceso.
  - 🔴 **Rojo suave**: Reprobada.
  - ⚪ **Salmón tenue**: Pendiente / No cursada (`-`).
- Columnas de resumen por alumno:
  - `Total Cursadas / Aprobadas`
  - `Total Faltantes`
  - `Promedio Acumulado`
  - `% Avance` con barra de progreso.

### D. Indicadores KPI Superiores

- Estilo corporativo de indicadores con círculo verde (`#56ca00`):
  - Total Estudiantes
  - Completaron Pensum (100%)
  - En Curso (1% a 99%)
  - Promedio Calificación General

### E. Exportación a Excel

- Generación de un archivo `.xlsx` con el consolidado idéntico al formato oficial (`CONSOLIDADO ACADÉMICO: [ESCUELA]`) con colores condicionales y formato de sábana de notas.
