---
description: Carga el contexto y memoria del Agente de Campus y Avance de Estudiantes
---

# Agente de Campus y Avance de Estudiantes

Este documento actúa como la memoria técnica y guía detallada para el funcionamiento del "Campus Estudiantil", incluyendo las lógicas de progreso, bloqueos y validación de tipos de contenido.

## 1. Concepto General y Frontend (Campus)

El Campus (`resources/views/livewire/cursos/campus-curso.blade.php`) es la interfaz principal donde el estudiante consume el contenido del curso.

- **Estructura Visual**:
  - **Izquierda**: Visualizador de contenido principal dinámico basado en `$itemActivo->tipo->codigo` (Video, Iframe, PDF, etc.) y debajo el texto descriptivo (`contenido_html`).
  - **Derecha**: Barra de progreso y Acordeón del Temario.

## 2. Motor de Seguimiento y Progreso (Base de Datos)

El sistema lleva un control exacto de qué lección ha visto cada estudiante.

### Tabla y Modelo: `CursoItemUser` (`curso_item_user`)

- **Propósito**: Tabla pivote que relaciona un estudiante con un ítem del curso específico.
- **Campos Clave**: `curso_item_id`, `user_id`, `estado` ('iniciado', 'completado'), `fecha_completado`.
- **Aseguramiento**: Una llave única (`curso_item_id`, `user_id`) garantiza que no haya registros duplicados.

### Actualización General: `CursoUser`

- Mientras `CursoItemUser` guarda el detalle por lección, **`CursoUser`** mantiene el campo `porcentaje_progreso` general del curso, el cual se recalcula matemáticamente cada vez que el estudiante finaliza un ítem.

## 3. Lógica Backend de Restricciones (Livewire)

El corazón de los bloqueos reside en `App\Livewire\Cursos\CampusCurso`.

1. **`cargarProgreso()`**:

   - Extrae **todos** los ítems del curso en orden (iterando módulos).
   - Lee la BD para encontrar el estado real de cada ítem usando la tabla pivote.
   - **Regla Estricta Secuencial**: Un ítem solo está habilitado (`estado = 'iniciado'`) si el ítem INMEDIATAMENTE ANTERIOR tiene `estado = 'completado'`. De lo contrario, se marca como `bloqueado` de forma forzada en memoria (`$this->itemsProgreso`), impidiendo clics y accesos.

2. **`seleccionarItem($itemId)`**:

   - Valida si el ítem intentado está bloqueado. Si lo está, cancela la acción devolviendo un _flash message_ de error.
   - Al abrir un ítem no completado, automáticamente se registra en base de datos como **'iniciado'**.
   - Emite el evento `item-cambiado` para que el frontend reinicie sus bloqueos.

3. **`marcarCompletado($itemId)`**:
   - Cambia el estado en BD a 'completado'.
   - Recalcula el progreso (`cargarProgreso()`), desbloqueando mágicamente el siguiente ítem en la cadena.
   - Avanza automáticamente llamando a `avanzarSiguiente()`.

## 4. Validaciones Frontend (JavaScript Completeness)

Para evitar que los estudiantes marquen "Hecho" sin leer o ver el contenido, se implementaron detectores específicos usando el evento de Livewire:

- **Script Integrado**: Un `@push('scripts')` con un listener nativo de Livewire Navigated (`document.addEventListener('livewire:navigated')`).
- **Estados Dinámicos**:
  - **Botón Deshabilitado por Defecto**: El botón "Marcar como Hecho" carga en estado `disabled`.
  - **Videos (`tipo == 'video'`)**:
    - **YouTube**: Se inyecta la API `YT.Player` apuntando a `youtube-player-{id}`, la cual hace un sondeo (polling) cada 2 segundos con `getCurrentTime()` y `getDuration()`. Si supera el 95%, habilita el botón.
    - **Vimeo**: Se inyecta la API `@vimeo/player` apuntando a `vimeo-player-{id}`, suscribiéndose al evento discreto `timeupdate`. Si `data.percent >= 0.95`, habilita el botón y se desuscribe para liberar memoria.
    - **Externos/Genéricos**: Un fallback de 15 segundos garantiza que no haya bloqueos infinitos si la URL no es YouTube ni Vimeo.
  - **Textos y PDFs (`tipo == 'lectura' || 'texto'`)**: Instala un `window.addEventListener('scroll')` que evalúa si el usuario hizo desplazamiento (`window.scrollY`) hasta el fondo del contenedor.
  - **Iframes y Otros**: Temporizadores breves como mecanismo de gracia.

## 5. Diseño e Indicadores de Estado (UI)

Los elementos del temario se renderizan dinámicamente con estilos inmersivos y predictivos:

- **Ítem Activo**: Fondo blanco, elevación (shadow), borde izquierdo grueso color primario (UI Feedback).
- **Ítem Bloqueado**: Texto gris (`text-muted`), cursor `not-allowed`, ícono de **Candado**. Inhabilitado mediante lógica y estilos.
- **Ítem Completado**: Texto negro normal, ícono de **Check Verde**. Al seleccionarlo, sus botones de acción se transforman a un "Acabado" no clickeable y un "Siguiente Lección".
- **Ítem Iniciado (Listos para ver)**: Muestra su ícono representativo (Play = Video, Libro = Lectura, Archivo = Otro).

## 6. Siguientes Pasos (Futuro)

- Conectar formalmente las APIs de YouTube/Vimeo para la detección milimétrica del $95\%$ del video.
- Sincronizar el progreso de `CursoUser` con un Dashboard general del estudiante.
- Permitir al administrador configurar forzosa o libremente si desea aplicar restricciones o dejar todas las clases abiertas.
