# Propuesta: Módulo de Evaluación de Maestros
## Sistema: REDIL Cloud
### Fecha: Mayo 2026

---

## Descripción General

Se propone el desarrollo del módulo **sistemaEvaluativoMaestros**, una funcionalidad que permite a los estudiantes calificar de forma anónima el desempeño de sus maestros al cierre de cada periodo académico. El módulo se integra de manera nativa con los módulos de Escuelas, Maestros y Periodos ya existentes en la plataforma, y ofrece herramientas tanto para el personal administrativo como para los propios maestros.

---

## ¿Qué Problema Resuelve?

Actualmente, la evaluación de maestros se realiza de forma manual mediante exámenes físicos que el personal administrativo aplica cada seis meses. Este proceso presenta las siguientes limitaciones:

- No existe un canal formal para que el alumno exprese su percepción del docente.
- Los resultados no quedan registrados digitalmente, dificultando el análisis histórico.
- No hay mecanismos automáticos que apoyen la toma de decisiones sobre el estado de un maestro.

El módulo propuesto digitaliza y centraliza este proceso, generando datos cuantitativos que complementan la evaluación administrativa y sirven como insumo objetivo para las decisiones del equipo directivo.

---

## Componentes del Módulo

### 1. Gestión de Formularios de Evaluación

El personal administrativo podrá crear formularios de evaluación personalizados por escuela. Cada formulario contiene un conjunto de preguntas de tipo calificación en escala del 1 al 10, diseñadas para medir criterios específicos del desempeño docente (puntualidad, claridad, dominio del tema, actitud, etc.).

**Características:**
- Cada escuela puede tener un único formulario activo en cualquier momento.
- Las preguntas son completamente configurables: el administrador define el texto y el orden.
- Se permite incluir una pregunta abierta de texto libre (comentario del alumno) que no afecta el puntaje.
- Un formulario puede duplicarse para reutilizarlo en otra escuela con mínimos ajustes.
- Cada formulario tiene un **puntaje mínimo de aprobación** configurable (por ejemplo, 7.0 de 10).

---

### 2. Vinculación Automática con los Periodos Académicos

Cuando el personal administrativo crea un nuevo periodo académico para una escuela, el sistema detecta automáticamente el formulario de evaluación activo de esa escuela y lo vincula al periodo de forma inmediata, sin pasos manuales adicionales.

El estado inicial de esta evaluación es **Borrador**. El administrador únicamente decide cuándo abrirla y cuándo cerrarla, con un solo clic.

**Estados de la evaluación:**
- **Borrador**: Creada automáticamente, en espera de ser activada.
- **Abierta**: Los alumnos pueden ingresar y responder el formulario.
- **Cerrada**: La evaluación finalizó y los resultados están disponibles.

---

### 3. Evaluación por Parte del Alumno (Anónima)

Cuando la evaluación está abierta, el alumno verá en su panel una sección de **"Mis evaluaciones pendientes"**, listando cada clase en la que está matriculado y que aún no ha sido evaluada.

**Características clave:**
- **Anonimato garantizado**: El sistema no almacena la identidad del alumno junto a sus respuestas. Se utiliza una firma criptográfica de un solo sentido (hash SHA-256) que permite evitar respuestas duplicadas sin comprometer la identidad del evaluador.
- **Sin doble votación**: Si un alumno intenta acceder de nuevo a una evaluación que ya completó, el sistema le informa que ya respondió, sin revelar sus respuestas.
- **No obligatorio por defecto**: La evaluación es voluntaria, aunque el administrador puede cambiarla a obligatoria en cualquier momento desde la gestión de convocatorias.
- **Los alumnos que no responden NO afectan el promedio** del maestro. Solo se calcula sobre quienes efectivamente respondieron.
- El formulario presenta una interfaz visual de calificación (1 al 10) para cada pregunta, más un campo opcional de comentario libre.

---

### 4. Dashboard de Resultados (Administración)

Una vez cerrada la evaluación, el personal administrativo accede a un panel de control que muestra de forma clara y visual los resultados consolidados.

**Flujo de consulta:**
1. El administrador selecciona una **Escuela**.
2. Selecciona un **Periodo** (por defecto carga el más reciente).
3. El sistema muestra los resultados de todos los maestros activos en ese periodo.

**Información visible por maestro:**
- Foto, nombre y estado (Activo / Inactivo).
- Indicador visual: ✅ **Aprueba** o ❌ **No aprueba** (según el puntaje mínimo del formulario).
- Por cada clase asignada al maestro:
  - Número de evaluaciones recibidas vs. total de alumnos matriculados.
  - Promedio general de la clase (barra de progreso visual).
  - Detalle desplegable por cada pregunta con su promedio individual.
- Tabla resumen exportable con todos los maestros y sus resultados.

> **Ejemplo:** Un maestro asignado a 3 clases puede tener promedios de 8.4, 7.1 y 5.9 — el sistema desglosa cada clase por separado, permitiendo identificar problemas específicos por grupo o materia.

---

### 5. Historial de Evaluaciones Pasadas

Desde una vista separada, el administrador puede consultar los resultados de convocatorias de evaluación anteriores, filtrando por escuela y año. Esto permite hacer seguimiento histórico del desempeño docente a lo largo del tiempo.

---

### 6. Vista del Maestro: Mis Resultados

Los maestros con el permiso correspondiente podrán consultar sus propios resultados de evaluación:

- Promedio general por clase y por periodo.
- Detalle de promedio por pregunta.
- Indicador de aprobación/no aprobación.
- Posibilidad de consultar periodos anteriores.

> **Importante:** El maestro **no tiene acceso a los comentarios de texto** individuales que dejaron los alumnos. Esta información es exclusiva del área administrativa, preservando la confianza en el proceso de evaluación.

---

### 7. Control de Acceso por Roles

Todas las funcionalidades del módulo están protegidas por el sistema de permisos existente en la plataforma (roles por tenant). El administrador de cada organización asigna los permisos correspondientes a cada rol:

| Funcionalidad | Permiso requerido |
|---|---|
| Ver y gestionar formularios | `evaluacion-formularios.*` |
| Abrir y cerrar convocatorias | `evaluacion-convocatorias.gestionar` |
| Ver dashboard de resultados | `evaluacion-resultados.ver` |
| Consultar historial | `evaluacion-resultados.historial` |
| Maestro ve sus propios resultados | `evaluacion-resultados.propios` |

---

## Tecnología Utilizada

El módulo se construirá sobre la misma pila tecnológica de la plataforma actual:

- **Backend**: Laravel 11 (PHP 8.2) con arquitectura Multi-Tenant.
- **Frontend reactivo**: Livewire 3 + Alpine.js para la interfaz dinámica sin recargas de página.
- **Base de datos**: PostgreSQL con 5 nuevas tablas (migración automatizada por tenant).
- **Seguridad**: Sistema de roles y permisos Spatie Permission.
- **Anonimato**: Tokens criptográficos SHA-256 para las respuestas de los alumnos.

---

## Estimación de Tiempo de Desarrollo

El desarrollo de este módulo se estima en **aproximadamente 3 semanas calendario**, trabajando a un ritmo de 30 horas semanales con apoyo de herramientas de Inteligencia Artificial para la generación de código base, pruebas y documentación.

| Componente | Tiempo estimado |
|---|---|
| Base de datos, modelos y vinculación automática | 1 semana |
| Gestión de formularios, preguntas y convocatorias | 1 semana |
| Evaluación del alumno y dashboard de resultados | 1 semana |
| **Total** | **~83 horas / 3 semanas** |

> **Nota**: Este estimado asume disponibilidad continua del desarrollador y acceso al entorno de desarrollo activo. Variaciones en los requisitos o cambios durante el desarrollo pueden ajustar este tiempo.

---

## Resumen de Valor para la Organización

| Antes del módulo | Con el módulo |
|---|---|
| Evaluación manual en papel | Evaluación digital, centralizada y anónima |
| Sin registro histórico digital | Historial completo por escuela y periodo |
| Sin participación formal del alumno | El alumno tiene voz en el proceso |
| Decisiones subjetivas sobre maestros | Respaldadas por datos cuantitativos |
| Proceso aislado del sistema | Integrado con periodos, maestros y roles |

---

*Documento preparado para revisión del cliente — REDIL Cloud / Mayo 2026*
