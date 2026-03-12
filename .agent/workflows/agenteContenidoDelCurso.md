---
description: Carga el contexto y memoria del Agente de Contenido del Curso
---

# Agente de Contenido del Curso

Este documento actúa como la memoria técnica y guía para la gestión del contenido detallado de los cursos (Lecciones, Evaluaciones e Items). Complementa al `agenteCursoPrincipal`.

## 1. Estructura Relacional (Contenido)

El sistema utiliza un diseño polimórfico para permitir diferentes tipos de contenido dentro de un mismo flujo de módulos.

- **`curso_modulos`**: Agrupadores de contenido pertenecientes a un `curso`.
- **`curso_items`**: El "pegamento" polimórfico. Relaciona un módulo con un tipo de contenido específico (`itemable_type`).
- **`curso_item_tipos`**: Define las capacidades del ítem (`video`, `lectura`, `recurso`, `iframe`, `evaluacion`).
- **`curso_lecciones`**: Almacena el contenido físico:
  - `contenido_html`: Actúa como campo de texto enriquecido secundario (instrucciones, resúmenes, apoyo), y se renderiza debajo del contenido principal.
  - `video_url` / `video_id` / `video_plataforma`: Integración con YouTube/Vimeo (Contenido principal).
  - `archivo_path`: Ruta del recurso en storage (PDFs, Imágenes, PPTX - Contenido principal).
  - `iframe_code`: Código embebido directo (Canva, Genially, etc - Contenido principal).
- **`curso_evaluaciones`**: Configuración de exámenes (`minimo_aprobacion`, `limite_tiempo`).
  - Relacionado con `curso_preguntas` y `curso_pregunta_opciones`.

## 2. Funcionalidades Clave Implementadas

### A. Gestión de Lecciones Dinámicas

- **Estructura Visual Dedicada**: El Campus prioriza visualmente el "Contenido Principal" dictado por el tipo de ítem (`iframe`, `video`, `recurso`), cargándolo en la parte superior. Paralelamente, el campus aprovecha `contenido_html` como una caja de "Descripción/Instrucciones" en la zona inferior de cada clase.
- **Tipo Iframe**: Soporte para insertar códigos `<iframe ...>` directos. Especialmente optimizado para que contenidos responsivos (como Canva) no se corten.
- **Nomenclatura Automática**: El sistema distingue género gramatical al crear ítems (`Nuevo video`, `Nueva lectura`).
- **Carga de Archivos Segura**:
  - **Nombres Únicos**: Se añade el ID de la lección al nombre del archivo (`archivo-ID.ext`) para evitar que archivos con el mismo nombre se sobrescriban.
  - **Validación Estricta**: Solo se permiten PDFs, Imágenes y PowerPoint.
  - **Validación en Tiempo Real**: El sistema valida el formato en cuanto se selecciona el archivo, deteniendo el "Cargando..." e informando vía SweetAlert si el formato es inválido (ej: Excel).

### B. Evaluación y Preguntas

### B. Evaluación y Preguntas

- **UX Fluida**: Se eliminaron las confirmaciones intermedias (SweetAlert) al agregar preguntas para agilizar la creación de exámenes.
- **Tipo Verdadero/Falso**: Automatiza la creación de las dos opciones base al cambiar el tipo de pregunta.
- **Presentación Pública (Campus)**: El despliegue de la evaluación a los estudiantes (implementado en `CampusCurso`) utiliza un sistema de mezcla aleatoria (`collection->shuffle()`) al cargar las preguntas para dificultar copias. Dispone de navegación independiente por pasos (círculos y botones naranja) sin recargas de página, validando obligatoriedad antes del envío.

### C. Interfaz y Experiencia de Usuario (UI/UX)

- **Modo Visualizar/Editar**: Cada ítem alterna entre vista previa y edición sin recargar la página gracias a Livewire + AlpineJS.
- **Selectores Premium (Alpine.js)**: Para multiselección (ej: categorías), se prefiere el uso de componentes personalizados en Alpine.js sobre Select2. Esto evita conflictos de carga y permite un control Total sobre la estética "Wow" (bordes de 15px, sombras suaves, buscador interno y etiquetas tipo badge).
- **Diseño de Búsqueda y Filtros**: Los bloques de filtrado deben usar contenedores redondeados (`border-radius: 15px`) de color blanco con bordes sutiles, integrando los botones de acción dentro del mismo flujo visual para una apariencia de aplicación moderna.

## 3. Consideraciones Técnicas (Desarrollo)

- **JavaScript/Livewire**: Se debe tener especial cuidado con los listeners de eventos (`$wire.on`). En Livewire 3, los datos se reciben directamente en el parámetro, NO dentro de `event.detail`.
- **SortableJS**: El orden de modales e ítems se sincroniza automáticamente con la base de datos mediante los eventos `actualizarOrdenModulos` y `actualizarOrdenItems`.
- **Storage**: Los recursos se almacenan en `archivos/cursos/{curso_id}/`. Al eliminar un ítem o módulo, el sistema se encarga de limpiar los archivos físicos del disco.

## 4. Comandos de Utilidad

- **Actualizar Tipos**: `php artisan db:seed --class=CursoItemTipoSeeder` (Para asegurar que el tipo `iframe` y otros estén presentes).
