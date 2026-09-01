---
description: Agente Principal para la Gestión de Cursos (LMS)
---

# Agente de Cursos (LMS)

Este documento describe la arquitectura, base de datos y flujos implementados para el Módulo de Cursos (LMS) independiente.

## 1. Estructura de Base de Datos

El módulo utiliza una tabla principal `cursos` y múltiples tablas pivote para gestionar relaciones y restricciones.

### Tabla Principal: `cursos`

Almacena la información del curso.

- **Campos Clave**: `nombre`, `slug`, `descripcion_corta`, `descripcion_larga`, `imagen_portada`, `video_preview_url`.
- **Configuración**: `nivel_dificultad` (Principiante, Intermedio, Avanzado, Todas), `es_obligatorio`, `estado` (Borrador, Publicado, Inactivo), `orden_destacado`.
- **Acceso**: `cupos_totales` (Null = Ilimitado), `dias_acceso_limitado` (Null = De por vida), `fecha_inicio`.
- **Precios**: `es_gratuito`, `precio`, `precio_comparacion`, `moneda_id`.

### Tablas Pivote y Restricciones

Las relaciones Many-to-Many se gestionan con tablas pivote, algunas con campos adicionales para lógica de negocio.

1.  **Roles (`curso_roles_restriccion`)**:

    - Define qué roles pueden acceder al curso.
    - Si no hay registros, el curso es público para todos los roles (sujeto a otras restricciones).

2.  **Métodos de Pago (`curso_tipos_pago`)**:

    - Define qué métodos de pago se aceptan para este curso específico.

3.  **Pasos de Crecimiento (Requisitos/Progreso)**:

    - **`curso_paso_requisito`**: Pasos que el usuario debe tener en un estado específico para _inscribirse/ver_ el curso.
      - _Campos Pivote_: `estado` (del paso), `estado_paso_crecimiento_usuario_id`, `indice`.
    - **`curso_paso_iniciar`**: Pasos que se inician automáticamente al comenzar el curso.
    - **`curso_paso_culminar`**: Pasos que se marcan como culminados al finalizar el curso.

4.  **Tareas de Consolidación (Requisitos/Progreso)**:
    - **`curso_tarea_requisito`**: Tareas requeridas.
      - _Campos Pivote_: `estado_tarea_consolidacion_id`, `indice`.
    - **`curso_tarea_culminar`**: Tareas que se completan al finalizar el curso.

## 2. Modelos

### App\Models\Curso

- Relaciones: `rolesRestringidos`, `tiposPago`, `pasosRequisito`, `pasosIniciar`, `pasosCulminar`, `tareasRequisito`, `tareasCulminar`.
- Las relaciones con Pasos y Tareas usan ->withPivot('estado', 'indice', ...).

### App\Models\PasoCrecimiento y App\Models\TareaConsolidacion

- Se añadieron las relaciones inversas (`cursosRequisito`, `cursosIniciar`, etc.) para poder consultar desde el lado del requisito.

## 3. Rutas y Controladores

- **Archivo**: `routes/web.php`
- **Grupo**: `cursos` (LMS)
- **Grupo**: `cursos` (LMS)
  - `GET /cursos/gestionar` -> `CursoController::class, 'index'` (Nombre: `cursos.gestionar`)
  - `GET /cursos/crear` -> `App\Livewire\Cursos\CrearCurso` (Nombre: `cursos.crear`)
  - `GET /cursos/{curso}/estudiantes` -> `App\Livewire\Cursos\ListadoEstudiantesCurso` (Nombre: `cursos.estudiantes`)

## 4. Vistas y Componentes Livewire

### Gestión (Listado)

- **Vista**: `resources/views/contenido/paginas/cursos/gestionar-cursos.blade.php`
- **Componente**: `App\Livewire\Cursos\GestionarCursos`
- **Funcionalidad**:
  - Listado paginado de cursos.
  - Búsqueda por nombre.
  - Filtros por Estado (Borrador, Publicado, Inactivo).
  - Filtros por Nivel de Dificultad.

### Creación

- **Vista**: `resources/views/livewire/cursos/crear-curso.blade.php`
- **Componente**: `App\Livewire\Cursos\CrearCurso`
- **Funcionalidad**:
  - Formulario completo con validación.
  - Secciones: Información Básica, Precios y Configuración, Restricciones.
  - Lógica `save()`: Crea el curso y sincroniza las relaciones (`sync`) para roles, pagos, pasos y tareas.
  - Lógica `save()`: Crea el curso y sincroniza las relaciones (`sync`) para roles, pagos, pasos y tareas.
  - **Mejoras UX**: Formulario dividido en "Cards" (Info, Precios, Multimedia, Restricciones).
  - **Selectores Dinámicos**: Implementación robusta de Select2 con eventos `initSelect2` para manejar reactividad (Moneda -> Métodos de Pago).
  - **Multimedia**: Previsualización de imagen y normalización automática de URLs de YouTube.

### Edición

- **Vista**: `resources/views/livewire/cursos/editar-curso.blade.php`
- **Componente**: `App\Livewire\Cursos\EditarCurso`
- **Funcionalidad**:
  - Carga de datos existentes y relaciones (pivotes).
  - Actualización de imagen de portada (reemplazo en disco).
  - Sincronización exacta de lógica con `CrearCurso` (validaciones, selectores, multimedia).

### Gestión de Inscritos (Estudiantes)

- **Vista**: `resources/views/livewire/cursos/listado-estudiantes-curso.blade.php`
- **Componente**: `App\Livewire\Cursos\ListadoEstudiantesCurso`
- **Funcionalidad**:
  - Listado de estudiantes en formato Grid de tarjetas responsivas.
  - Las tarjetas muestran avatar, nombre, rol (`tipoUsuario`), correo completo, teléfono, y fecha de inscripción (`Y-m-d`).
  - Barra de progreso dinámica (Rojo <35%, Amarillo <80%, Verde >80%).
  - Búsqueda en tiempo real por nombre completo combinando (`primer_nombre` + `apellidos`), documento de identidad o correo usando `CONCAT_WS` y `LOWER`.
  - **Offcanvas de Filtros**: Implementación de filtros diferidos (esperan al click de 'Aplicar Filtros') por Rango de Año y Estado de Curso. Los filtros aplicados se renderizan como Etiquetas (Tags) sobre la grilla con opción de cierre individual.

## 5. Menú

- Se agregó la entrada "Cursos (LMS)" en `resources/views/layouts/sections/menu/verticalMenu.blade.php` con el icono `ti-device-laptop`.

## 6. Restricciones y Automatización (Culminación)

### Restricciones Generales

- **Vista Dedicada**: `resources/views/contenido/paginas/cursos/restricciones.blade.php`.
- **Componente**: `App\Livewire\Cursos\Restricciones\GestionarRestriccionesGenerales`.
- **Datos**: Almacenados en la tabla `cursos` (`genero`, `vinculacion_grupo`, `actividad_grupo`, `excluyente`) y tablas pivote (`curso_sede`, `curso_rango_edad`, `curso_estado_civil`, `curso_tipo_servicio`).

### Automatización al Finalizar

- Permite configurar qué **Pasos de Crecimiento** y **Tareas de Consolidación** se marcan automáticamente como completados cuando el estudiante finaliza el curso.
- **Componentes**:
  - `App\Livewire\Cursos\Restricciones\GestionarPasosCulminar`
  - `App\Livewire\Cursos\Restricciones\GestionarTareasCulminar`

## 7. Flujo de Inscripción, Carrito y Checkout

Para independizar las finanzas y la lógica de negocio de los cursos respecto a las actividades regulares de la iglesia, se ha implementado un esquema de compra segregado:

### Validación de Requisitos (`App\Models\Curso::validarRequisitosUsuarioCurso`)

El modelo `Curso` analiza centralizadamente si el usuario cumple con las restricciones (género numérico, rangos de edad, estado civil, y que cada paso o tarea de crecimiento exigida tenga el estado solicitado).

### Previsualización y Botón de Acción

- **Vista**: `resources/views/contenido/paginas/cursos/previsualizar.blade.php`
- **Componente**: `App\Livewire\Cursos\BotonInscripcionCurso`
- Si no cumple, se muestra _alert_ de error.
- Si es gratis, permite inscripción directa insertando en `curso_users`.
- Si es de pago, habilita "Comprar Ahora" o "Añadir al Carrito" (que cambia a "Ver Carrito" si ya está agregado).
- **Playlist Dinámico**: El panel derecho ahora itera sobre los módulos reales del curso (máximo 3) y muestra los ítems. Discrimina visualmente según `curso_item_tipo_id` (Videos con miniatura y candado vs Lecturas/Recursos con iconos de libros).

### El Carrito Independiente (`CarritoCursoUser`)

- Almacena temporalmente la selección en la tabla `carritos_curso_user`.
- Guarda el estado, user_id, y un JSON del arreglo `items` para flexibilidad.
- **Componente**: `App\Livewire\Cursos\CarritoCompras` -> Lista cursos para pagar o eliminar.

### El Checkout LMS (`CheckoutCursos`)

- **Controlador/Ruta**: `CursoController@checkout` -> `/cursos/checkout`
- **Componente**: `App\Livewire\Cursos\CheckoutCursos`
- Consolida el carrito o una compra directa en vuelo.
- Filtra `TipoPago` para renderizar **solo los métodos de pago habilitados** (`curso_tipos_pago`) en los cursos que están en el carrito actual. Si un curso en el carrito no comparte métodos con otro, o no tiene, requiere atención del admin.
- Provee la base para simular pagos (ej. efectivo/taquilla) matriculando exitosamente en `curso_users` y marcando el carrito como `completado`.

## 8. Seeders y Datos de Prueba

- **`CursoDemoSeeder.php`**: Crea 3 cursos de prueba (Con y Sin costo). Genera automáticamente Módulos y un surtido de lecciones/videos de prueba. También adjunta decenas de inscripciones simuladas (`curso_users`) asignando usuarios y porcentajes de progreso aleatorios, proveyendo un entorno rico para probar los filtros y UI del Listado de Inscritos.

## 9. Catálogo Público de Cursos

Se ha implementado una vista pública del catálogo de cursos accesible para todos los usuarios, con características especiales para usuarios autenticados:

- **Ruta**: `/cursos/catalogo`
- **Controlador**: `CursoController@catalogo`
- **Componente**: `App\Livewire\Cursos\CatalogoCursos`
- **Vista**: `resources/views/livewire/cursos/catalogo-cursos.blade.php` envuelta en `resources/views/contenido/paginas/cursos/catalogo.blade.php` (utilizando `blankLayout`).

### Funcionalidades Clave:

- **Mis Cursos (Usuario Autenticado)**: Si un usuario ha iniciado sesión y está inscrito en cursos, visualizará una cuadrícula superior con sus cursos en progreso, mostrando una barra porcentual leída desde la tabla pivote y un acceso rápido para continuar.
- **Búsqueda y Ordenamiento Avanzado**: Barra de búsqueda en tiempo real (debounced) por nombre de curso y selector para ordenar cronológica y alfabéticamente.
- **Multiselección de Categorías (Cross-Device)**:
  - **Desktop/Laptop**: Pestañas de estado activo que permiten apagar o encender selecciones dinámicamente basadas en el array `$categoriasSeleccionadas`.
  - **Móviles**: Adaptado vía `media-queries` para utilizar un selector de opción múltiple nativo atado al mismo estado de Livewire para una mejor UX táctil.
- **Cursos Disponibles**: Listado paginado (12 cursos por vista) excluyendo borradores o archivados. Muestra atributos clave como precio, miniaturas, categorías y duración combinadas en tarjetas optimizadas para interacciones (Hover effects).

## 10. Foro de Dudas y Comunidad

Se ha incorporado un sistema de foros integrado a los cursos para fomentar la interacción entre estudiantes y permitir soporte o moderación por parte de los asesores.

### Base de Datos y Modelos

- **Tabla `curso_foro_hilos` / `App\Models\CursoForoHilo`**:
  - Representa la pregunta principal iniciada por un usuario matriculado en el curso.
  - Almacena estado (`pendiente`, `resuelto`, `cerrado`), relación opcional con una lección específica (`curso_item_id`), y campos `titulo` / `cuerpo`.
- **Tabla `curso_foro_respuestas` / `App\Models\CursoForoRespuesta`**:
  - Almacena las respuestas del hilo en formato chat.
  - Introduce una bandera `es_respuesta_oficial` para resaltar las respuestas provistas por asesores.

### Componentes Livewire

#### Foro del Estudiante (Dentro del Campus)

- **Componente**: `App\Livewire\Cursos\Foro\ForoCursoEstudiante`
- **Vista**: `resources/views/livewire/cursos/foro/foro-curso-estudiante.blade.php`
- **Flujo**:
  - Visualización paginada de hilos del curso actual.
  - Capacidad de filtrar por mis preguntas y término de búsqueda.
  - Vistas dinámicas internas para alternar entre "lista", "crear nueva pregunta", y "detalle de hilo".
  - Permite a los estudiantes interactuar y aportar respuestas a dudas de sus compañeros.
  - **Avatares Dinámicos**: Implementación de renderizado visual dual (`foto` provista desde Storage, con _fallback_ estilo "botón con iniciales" si `$h->user->foto` no existe o es igual a `default`). Se apoya en una llamada global al `Configuracion` local para construir el Path a la imagen.

#### Panel Global de Asesores (Administración)

- **Componente**: `App\Livewire\Cursos\Foro\PanelForoAsesor`
- **Vista**: `resources/views/livewire/cursos/foro/panel-foro-asesor.blade.php`
- **Flujo**:
  - Listado consolidado que cruza todos los cursos y hilos.
  - Opciones de filtrado avanzado: estado, curso específico y término de búsqueda libre.
  - Administra un panel estilo Offcanvas para leer los hilos completamente y aportar la respuesta.
  - Acciones administrativas para marcar hilos cerrados/resueltos e identificar su intervención como `es_respuesta_oficial`.

## 11. Interfaz de Evaluaciones (Campus)

- Se implementó la UI interactiva para cuando `$itemActivo` es del tipo `evaluacion`.
- **Backend (`CampusCurso.php`)**:
  - Al detectar una evaluación se cargan las preguntas asociadas a ella y se les aplica un `shuffle()` para renderizarlas de manera **aleatoria** a cada estudiante, dificultando la copia mediante la compartición de secuencia de opciones.
  - Propiedades para mantener en estado local `preguntaActualIndex` y un array de `respuestasEvaluacion`.
  - Diferenciación de selecciones únicas (`radio`) vs múltiples (`checkbox`) a través del método de array toggle.
- **Frontend (`campus-curso.blade.php`)**:
  - Panel superior destacado con "Círculos Amarillos" de navegación para saltar entre el índice de las preguntas cargadas (`irAPregunta()`), resaltando visualmente la actualmente activa y coloreando aquellas que ya fueron respondidas.
  - Botones naranjas inferiores de navegación secuencial (Siguiente/Anterior).
  - Al dar en el botón "Enviar", verifica mediante un listener y _SweetAlert_ si quedaron o no preguntas por contestar (Notificación Warning de "Evaluación incompleta"), o bien arroja el prompt de validación de certeza antes de proceder.
