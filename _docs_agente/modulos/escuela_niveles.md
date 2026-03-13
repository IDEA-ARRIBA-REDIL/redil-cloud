# Agente de Gestión de Grados Escolares (Niveles)

Este agente se especializa en la gestión de la estructura de **Grados** (técnicamente referenciados como `niveles_escuelas` en la base de datos) dentro del módulo de Escuelas. Su propósito es mantener la paridad de configuración con el sistema de materias y gestionar las dependencias jerárquicas entre grados.

## Core Conceptual (Grados vs Materias)

- **Paridad de Campos (17 Campos)**: Los grados deben compartir exactamente las mismas opciones de configuración que las materias (asistencias, alertas, inasistencias, límites de reporte, plazos, etc.).
- **Diferencia Estructural**: Mientras las materias son unidades de enseñanza, los **Grados** actúan como contenedores jerárquicos (ej: "Grado Primero" contiene Matemáticas, Español, etc.).

## Estructura Técnica

### Modelos Relacionados
- `App\Models\NivelEscuela`: Modelo principal para los grados.
- `App\Models\Escuela`: Relación padre.
- `App\Models\TipoUsuario`: Define el público objetivo del grado.

### Base de Datos (Tenant)
- **Tabla**: `niveles_escuelas`
- **Campos Críticos de Paridad**:
    - `habilitar_asistencias`, `habilitar_calificaciones`, `habilitar_inasistencias`.
    - `limite_reporte_asistencias`: Cantidad máxima de reportes permitidos.
    - `dia_limite_reporte` / `tiene_dia_limite`: Restricción temporal de fin de semana o día específico.
    - `cantidad_limite_reportes_semana`: Frecuencia de reportes permitida.
    - `dias_plazo_reporte`: Días adicionales para reportar tras el evento.

### Sistema de Prerrequisitos
- **Tabla Pivote**: `nivel_escuela_prerrequisitos`
- **Campos**:
    - `nivel_escuela_id_inicial`: El grado que requiere el prerrequisito.
    - `nivel_escuela_requerido_id`: El grado que debe haber sido completado/aprobado.
    - `escuela_id`: Contexto de la escuela para filtrar rápidamente.

## Convenciones de Interfaz (UI)

- **Terminología**: Siempre usar la palabra **"Grado"** en etiquetas, botones y mensajes de cara al usuario. Internamente (código/BD) se mantiene como **"Nivel"**.
- **Vista Principal**: `resources/views/contenido/paginas/escuelas/niveles-escuelas/crear-nivel-escuela.blade.php`.
- **Lógica Dinámica**:
    - Si `habilitarAsistencias` es falso, se ocultan todos los campos de límites y plazos.
    - Si `diaLimiteHabilitado` es verdadero, se activa el selector de día y se desactiva la cantidad semanal y días de plazo.

## Flujo de Guardado
- **Controlador**: `App\Http\Controllers\NivelesEscuelasController`.
- **Proceso**:
    1. Validar paridad de 17 campos.
    2. Guardar entidad principal.
    3. Sincronizar prerrequisitos inyectando el `escuela_id` en la tabla pivote.
    4. Procesar y recortar portada (1693x376 px).

## Reglas de Oro para este Agente
1. Nunca romper la paridad con `MateriaController`. Si se agrega una regla a materias, debe replicarse en grados.
2. Mantener la consistencia del `escuela_id` en las relaciones de prerrequisitos.
3. Asegurar que las validaciones de `cantidadInasistencias` coincidan con el input `asistencias_minima_alerta` en base de datos.
