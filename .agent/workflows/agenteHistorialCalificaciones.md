---
description: Agente para la gestión de Historial Académico y Generación de Boletines PDF (Organización por Niveles)
---

# Agente de Historial de Calificaciones

Este agente se especializa en la gestión, análisis y visualización del **Historial Académico** de los estudiantes, con un enfoque jerárquico organizado por **Niveles (Grados)** y la generación de reportes oficiales en formato PDF.

## 1. Directivas Críticas

- **JERARQUÍA POR NIVELES**: El historial NUNCA debe mostrarse como una lista plana de materias. Siempre debe agruparse por el `nivel_id` asociado a la materia.
- **ESTADO DE APROBACIÓN GLOBAL**: Antes de listar las materias, se debe verificar el estado de aprobación del nivel completo consultando la tabla `niveles_aprobado_usuario`.
- **DETALLE DE MATRÍCULA**: Los datos de horario, aula, sede y maestro deben obtenerse cruzando el registro de historial con la tabla de `matriculas` para el periodo correspondiente.
- **IDIOMA**: Toda la comunicación, comentarios en código y plantillas deben estar en **ESPAÑOL**.
- **UI PREMIUM**: Las vistas deben usar el diseño de tarjetas (cards) jerárquicas con insignias (badges) claras para los estados de aprobación.

## 2. Modelos y Base de Datos

- **`MateriaAprobadaUsuario`**: Almacena el resultado final de una materia cursada.
    - Relación `materia()`: Obtiene la definición de la materia (y su nivel).
    - Relación `nivel()`: Acceso directo al nivel a través de la materia.
    - Relación `periodo()`: Periodo en que se cursó.
- **`NivelAprobadoUsuario`**: Almacena la aprobación global de un grado/nivel.
    - Esencial para determinar si el estudiante "pasó el año" o completó el bloque educativo.
- **`User`**: El modelo de usuario incluye las relaciones `materiasAprobadasRelacion()` y `nivelesAprobados()`.

## 3. Lógica de Consulta (Controladores)

Para generar el historial correctamente, se debe seguir este flujo:
1. Obtener todas las `MateriaAprobadaUsuario` del usuario.
2. Obtener todos los `NivelAprobadoUsuario` del usuario para mapear estados de aprobación de grado.
3. Agrupar la colección de materias mediante `groupBy(fn($item) => $item->materia->nivel_id ?? 'sin_nivel')`.
4. Enriquecer cada registro con `detalles_matricula` (Maestro, Aula, Sede, Horario) consultando la relación con `Matricula`.

## 4. Generación de PDF (Boletines)

El boletín oficial se genera individualmente por materia cursada utilizando `dompdf` (`PDF` Facade):
- **Datos Requeridos**:
    - **Calificaciones Detalladas**: Extraídas de `AlumnoRespuestaItem`.
    - **Asistencias Paso a Paso**: Extraídas de `ReporteAsistenciaAlumnos`.
    - **Contexto**: Maestro (vía matrícula), Nivel (vía materia) y Periodo.
- **Plantilla**: `resources/views/contenido/paginas/escuelas/historial-calificaciones/boletin-materia.blade.php`.

## 5. Vistas y Componentes

- **Administración**: `resources/views/contenido/paginas/escuelas/historial-calificaciones/consultar-historial.blade.php`.
    - Utiliza búsqueda reactiva de alumnos y filtros por escuela.
- **Estudiante**: `resources/views/contenido/paginas/escuelas/alumnos/historial-academico.blade.php`.
    - Vista simplificada y personalizada para que el alumno vea su propio progreso.

## 6. Protocolo de Debugging (Errores Comunes)

1. **Variables Indefinidas**: Asegurarse de inicializar `$historialAgrupado` y `$nivelesAprobados` como colecciones vacías si no hay un usuario seleccionado.
2. **Importaciones**: Los modelos de aprobación deben importar explícitamente `Illuminate\Database\Eloquent\Relations\BelongsTo` para evitar errores de tipo de retorno.
3. **Corrupción de Blade**: Al modificar vistas complejas, preferir reescribir bloques completos o el archivo entero para evitar discrepancias en las directivas `@if`/`@else`/`@foreach`.

---
*Nota: Este agente garantiza una visión estructurada y profesional del progreso académico, asegurando que la información sea veraz, organizada y fácil de auditar mediante reportes PDF.*
