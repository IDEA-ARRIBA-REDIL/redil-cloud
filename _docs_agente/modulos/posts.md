# Módulo de Publicaciones (Posts / Vive Manantial)

Este módulo gestiona el feed social dentro del dashboard, permitiendo a los administradores crear contenido segmentado para los usuarios. En el frontend se conoce comúnmente como "Vive Manantial" o "Feed".

## Modelos y Datos
- **Modelo**: `App\Models\Post`
- **Tabla**: `posts`
- **Relaciones**:
  - `user`: El autor de la publicación.
  - `likes`: Relación `belongsToMany` con usuarios.
  - **Restricciones**: Relaciones `belongsToMany` con `sedes`, `estados_civiles`, `rangos_edad`, `tipos_usuarios`, `pasos_crecimiento` y `tareas_consolidacion`.

## Lógica de Visibilidad (Scope For User)
El sistema utiliza un scope global `forUser(User $user)` que filtra las publicaciones según:
1.  **Visible Todos**: Si `visible_todos` es true, ignora el resto de filtros.
2.  **Género**: Filtra por el género del usuario (Masc/Fem/Ambos).
3.  **Sede**: Si el post tiene sedes asignadas, el usuario debe pertenecer a una de ellas.
4.  **Estado Civil**: Filtra por el estado civil del usuario.
5.  **Rango de Edad**: Compara la edad del usuario (calculada) con los rangos permitidos.
6.  **Tipo de Usuario**: Filtra por el rol/tipo del usuario.
7.  **Requisitos Académicos**:
    - **Pasos de Crecimiento**: El usuario debe tener un estado específico en un paso de crecimiento determinado.
    - **Tareas de Consolidación**: El usuario debe haber completado o estar en un estado específico de una tarea.

## Gestión de Imágenes
Las imágenes se almacenan por inquilino (tenant) para mantener la autonomía de cada iglesia.
- **Ruta Estándar**: `storage/app/public/{ruta_almacenamiento}/img/publicaciones/`
- **Generación**: El `PostController` maneja la carga de imágenes mediante base64 (recorte en cliente).

## Componentes Livewire
- **Widget Dashboard**: `App\Livewire\Dashboard\PostsWidget`
  - Implementa scroll infinito (`loadMore`).
  - Soporta "likes" reactivos.
  - Vista: `resources/views/livewire/dashboard/posts-widget.blade.php`.
  - **Modal Inmersivo**: Incluye un visualizador tipo "Reels/TikTok" con navegación vertical.

## Seeding
Para pruebas de sistema, el `PostSeeder` puede copiar imágenes desde `global_media` hacia la carpeta local del tenant, asegurando que el entorno de desarrollo tenga contenido visual.
