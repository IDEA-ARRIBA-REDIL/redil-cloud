# Contexto de Agente: Módulo de Cursos (LMS)

## 1. Descripción General

El módulo de **Cursos** permite la gestión de programas educativos independientes de las "Escuelas". Funciona como un LMS tradicional donde los usuarios pueden inscribirse (gratis o pago), consumir contenido y progresar.

- **Entidad Principal**: `Curso`
- **Controlador**: `CursoController` (Gestión)
- **Componentes Livewire**: `GestionarCursos`, `CrearCurso`.

## 2. Lógica de Negocio y Restricciones

El acceso y comportamiento de cada curso está regido por múltiples capas de configuración:

### A. Restricciones de Acceso

1.  **Roles**: El curso puede estar visible/disponible solo para ciertos roles de usuario (`curso_roles_restriccion`). Si la lista está vacía, es público (dentro del sistema).
2.  **Pasos de Crecimiento (Requisito)**: El usuario debe haber alcanzado un estado específico en un paso de crecimiento (Ej: Haber completado "Bautismo") para inscribirse.

### B. Inscripción y Pagos

1.  **Tipos de Pago**: Define qué métodos se aceptan (Ej: Tarjeta, Transferencia, Efectivo).
2.  **Moneda y Precio**: Soporte para cursos gratuitos o pagos en múltiples monedas.

### C. Efectos de Progreso (Automatización)

Al interactuar con el curso, el sistema dispara acciones en otros módulos:

1.  **Al Iniciar**: Puede iniciar automáticamente un `PasoCrecimiento` en el perfil del usuario.
2.  **Al Culminar**:
    - Puede marcar como completado un `PasoCrecimiento`.
    - Puede marcar como completada una `TareaConsolidacion`.

## 3. Diagrama de Relaciones

```mermaid
classDiagram
    class Curso {
        +String nombre
        +String slug
        +Enum estado (Borrador, Publicado)
        +Enum nivel_dificultad
        +Boolean es_gratuito
        +belongsToMany Roles
        +belongsToMany TiposPago
    }

    class PasoCrecimiento {
        +String nombre
        +belongsToMany Curso (Requisito, Iniciar, Culminar)
    }

    class TareaConsolidacion {
        +String nombre
        +belongsToMany Curso (Requisito, Culminar)
    }

    class Pivot_Requisito {
        +Integer estado_requerido
        +Integer indice
    }

    Curso "1" --> "many" PasoCrecimiento : Requisito (Pivot)
    Curso "1" --> "many" PasoCrecimiento : Inicia (Pivot)
    Curso "1" --> "many" PasoCrecimiento : Culmina (Pivot)

    Curso "1" --> "many" TareaConsolidacion : Requisito (Pivot)
    Curso "1" --> "many" TareaConsolidacion : Culmina (Pivot)

    (Curso, PasoCrecimiento) .. Pivot_Requisito
```

## 4. Notas Técnicas

- **Rutas**: Prefijo `/cursos`.
- **Vistas**: `resources/views/contenido/paginas/cursos`.
- **Livewire**: Componentes en `App\Livewire\Cursos`.
- **Tablas Pivote**: Las relaciones con Pasos y Tareas son complejas y usan campos pivote clave (`estado`, `indice`, `estado_id`) que deben validarse cuidadosamente al crear/editar.
