# Agente de Gestión de Reportes de Asistencia de Clases

Este documento provee el contexto y memoria necesarios para que cualquier agente comprenda cómo funciona la lógica de creación, cálculo de fechas y validación de los Reportes de Asistencia dentro del módulo de Escuelas en REDIL Cloud.

## 1. Arquitectura de Datos

El sistema de asistencias se divide en dos niveles principales para optimizar el rendimiento y la organización:

*   **Cabecera (`ReporteAsistenciaClase`):**
    *   Guarda la "sesión de clase". Relaciona el horario, el maestro y la fecha específica en la que se dictó la clase.
    *   Alberga las observaciones generales de esa clase en particular.
*   **Detalle (`ReporteAsistenciaAlumnos`):**
    *   Contiene la asistencia individual de cada alumno (Llegó puntual, tarde, falta, etc.) vinculada al reporte de clase (la cabecera).
    *   **Regla de Negocio Crítica:** Si un estudiante tiene un reporte de asistencia (detalle), su matrícula **no puede ser eliminada** para proteger el historial académico.

---

## 2. Lógica de Permisos y Roles (El "Master Switch")

Toda la validación y flexibilidad del sistema de creación depende del permiso especial: `escuelas.reportar_asistencia_cualquier_dia`.

### 🛡️ Super Administrador (Con Permiso)
*   **Sin restricciones de Periodo:** Puede crear reportes en periodos vencidos o futuros libremente.
*   **Calendario (Flatpickr) Libre:** La interfaz le permite seleccionar cualquier día del calendario.
*   **Límites Numéricos Activos:** Solo está sujeto al límite de reportes totales por materia (`limite_reporte_asistencias`) y al límite semanal (`cantidad_limite_reportes_semana`).

### 👨‍🏫 Maestro Regular (Sin Permiso)
*   Está rígidamente atado al calendario del Periodo Académico (fecha de inicio y fin).
*   **Bloqueo de UI:** Solo puede reportar en las fechas específicamente calculadas por el sistema ("Fechas Permitidas"). Las fechas pasadas (omitidas) y futuras están deshabilitadas.
*   Sujeto a días de gracia y a la configuración estricta de la materia.

---

## 3. Lógica de Cálculo de Fechas (El Motor de Validaciones)

El núcleo lógico se encuentra en `MaestroController.php`, específicamente en la función `calcularEstadoFechasReporte($horarioAsignado, $periodo, $datosMateria)`.

Este algoritmo es el encargado de iterar semana a semana, desde el inicio hasta el fin del periodo, para encontrar los días de clase de la materia y clasificarlos en cuatro estados:

1.  **✅ Realizados:** Fechas teóricas que ya cuentan con un `ReporteAsistenciaClase` en la base de datos.
2.  **❌ Omitidos:** Fechas teóricas del pasado donde el maestro no hizo el reporte y **el plazo ya se venció** (según `dias_plazo_reporte` o `dia_limite_reporte`).
3.  **⏳ Pendientes:** La fecha teórica de la semana en curso en la que el maestro **aún está a tiempo** de reportar. Esta será la única fecha habilitada en el calendario para él.
4.  **🔮 Futuros:** Fechas de clases que corresponden a semanas venideras del periodo.

El resultado de este cálculo alimenta tanto la Interfaz de Usuario como las restricciones de Backend.

---

## 4. Validaciones de Backend al Guardar (`verificarCondicionesParaCrearReporte`)

Cuando un maestro envía la solicitud POST para guardar un reporte, la función `verificarCondicionesParaCrearReporte()` toma el control y simplifica las reglas basándose en el cálculo teórico:

*   **Paso 1: Límite Global:** Se rechaza de inmediato si la materia alcanzó el máximo de reportes totales.
*   **Paso 2: Bifurcación por Rol:**
    *   **Si es Admin:** Solo verifica que no exceda el límite numérico de reportes *en esa semana específica*.
    *   **Si es Maestro Regular:** Verifica estrictamente que la fecha solicitada exista dentro del array de `$estadoFechas['fechasPermitidasFlatpickr']`. Si la fecha solicitada figura como Omitida o Futura en el estado calculado, la creación es rechazada con un mensaje de error específico.

---

## 5. Interfaz de Usuario (UI) en Blade

El archivo principal es `reporte-asistencia-alumnos.blade.php`.

### Cuadro Informativo (Acordeón)
*   Justo encima del botón de "Nuevo Reporte", existe un componente "Informe de reportes propios del periodo".
*   Este acordeón muestra visualmente la clasificación de fechas calculada por el backend (Omitidas, Realizadas, Futuras).
*   Su propósito es dar claridad total al maestro sobre su cumplimiento de reportes. Si hay fechas "Omitidas", el acordeón muestra un 'badge' rojo de advertencia.

### Comportamiento del Calendario (Flatpickr)
El script de inicialización de `Flatpickr` es dinámico:
*   Recibe la variable `$estadoFechas['fechasPermitidasFlatpickr']`.
*   Si el usuario no es Admin, inyecta este array en la propiedad `enable` de Flatpickr. De esta manera, a nivel cliente (frontend), el usuario es físicamente incapaz de clickear o seleccionar una fecha vencida o futura, acoplando perfectamente el frontend con las validaciones del backend.
