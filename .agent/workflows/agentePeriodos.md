---
description: Agente para la gestión de Periodos Académicos y Duplicación de Configuraciones (Niveles y Materias)
---

# Agente de Periodos y Configuración Académica

Este agente se especializa en la orquestación de **Periodos Académicos**, coordinando la relación entre la escuela, los grados (niveles) y las materias, además de gestionar el sistema de duplicación inteligente entre ciclos.

## 1. Conceptos Fundamentales

- **Modelo**: `app/Models/Periodo.php` (Tabla: `periodos`).
- **Contexto de Escuela**: Los periodos pertenecen a una `Escuela` (`escuela_id`). La configuración de la escuela (`tipo_matricula`) determina cómo se gestionan las materias dentro del periodo.

## 2. Tipos de Gestión según Escuela

### A. Escuela de Materias Independientes (`materias_independientes`)
- El periodo contiene un listado plano de materias.
- **Relación**: `Periodo` -> `MateriaPeriodo`.
- **Componente UI**: `MateriaPeriodo.php`.

### B. Escuela de Niveles Agrupados (`niveles_agrupados`)
- El periodo se organiza jerárquicamente por grados.
- **Jerarquía**: `Periodo` -> `NivelPeriodo` (Grado) -> `MateriaPeriodo` (Materia).
- **Filtro Crítico**: Cada materia en el periodo debe tener un `nivel_id` que coincida con uno de los `niveles_periodo` registrados.

## 3. Sistema de Duplicación Inteligente

El sistema permite "clonar" la estructura académica de un periodo origen al periodo actual para ahorrar tiempo de configuración.

### Lógica de Clonación (Deep Copy)
1.  **Validación**: Verifica que el periodo origen pertenezca a la misma escuela.
2.  **Iteración**: Recorre los grados (si aplica) y las materias del origen.
3.  **Clonación de Instancias (`replicate`)**:
    - Se crea una copia exacta de `MateriaPeriodo`.
    - Se fuerza `finalizado = false`.
    - Se resetean campos transaccionales (como cupos disponibles en horarios).
4.  **Vinculación de Horarios**: Se replican los registros de `HorarioMateriaPeriodo` asociados a cada instancia de materia.
5.  **Mapeo de Evaluación (Items de Corte)**:
    - Es el paso más complejo. Busca los ítems de evaluación del origen.
    - **Resolución de Cortes**: Como los IDs de `CortePeriodo` cambian entre periodos, el sistema busca el "Corte Equivalente" usando el `corte_escuela_id` (la base maestra del corte).
    - Si existe un corte en el periodo destino que comparta el mismo `corte_escuela_id` que el ítem original, se crea la copia vinculada al nuevo ID de corte.

## 4. Componentes y Rutas Clave

- **Gestión de Grados**: `resources/views/livewire/escuelas/niveles-periodo.blade.php`.
- **Gestión de Materias**: `resources/views/livewire/escuelas/materia-periodo.blade.php`.
- **Relación de ítems**: La relación alias `itemsCorte()` en `MateriaPeriodo.php` es fundamental para el cargado eficiente (`with`) de los datos de evaluación.

## 5. Directivas Operativas

- **Transaccionalidad**: Todas las operaciones de duplicación deben estar envueltas en `DB::beginTransaction()` y `DB::commit()` para evitar configuraciones parciales en caso de error.
- **Contexto de Nivel**: Al duplicar materias dentro de un grado específico, asegúrate de filtrar el `ModeloMateriaPeriodo` por el `nivel_id` actual.
- **UX**: Siempre emitir mensajes flash (`mensaje_exito`) detallando cuántos grados, materias e ítems se duplicaron con éxito.

---
*Nota: Este agente centraliza la lógica de transición entre periodos, garantizando que la carga académica se mantenga consistente y facilitando la administración masiva de contenidos.*
