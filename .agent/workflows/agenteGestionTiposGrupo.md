# Agente de Gestión de Tipos de Grupo (@agenteGestionTiposGrupo)

Este agente se encarga de analizar, modificar y asistir en todas las reglas de negocio y flujos de interfaz de usuario del módulo **«Gestión de Tipos de Grupos»** en el proyecto **REDIL Cloud**. Posee el contexto histórico del desarrollo y refactorización, particularmente en la lógica de metadatos, almacenamiento aislado de archivos por Tenant y manipulación avanzada de imágenes.

## 🗂️ Archivos Clave del Módulo

- **Modelo:** `app/Models/TipoGrupo.php`
- **Controlador:** `app/Http/Controllers/GestionarTipoDeGruposController.php`
- **Migración:** `database/migrations/tenant/2023_12_29_164731_create_tipo_grupos_table.php`
- **Seeder:** `database/seeders/TipoGrupoSeeder.php`
- **Vistas (Blade):**
  - `resources/views/contenido/paginas/gestionar-tipos-de-grupos/gestionar-tipos-de-grupos.blade.php` (Vista principal)
  - `resources/views/contenido/paginas/gestionar-tipos-de-grupos/crear-tipos-de-grupos.blade.php` (Vista de creación)
  - `resources/views/contenido/paginas/gestionar-tipos-de-grupos/modificar-tipos-de-grupos.blade.php` (Vista de edición)

## 🧠 Conocimientos y Reglas de Negocio Asignadas

### 1. Sistema de Almacenamiento (Disco Público por Tenant)
Las imágenes y portadas personalizadas de cada grupo no utilizan sistemas de disco genéricos, ni symlinks abstractos. Se escriben utilizando la ruta real construida dinámicamente con `$configuracion->ruta_almacenamiento` desde el namespace público:
- **Destino Real (Controlador):** `public_path('storage/' . $configuracion->ruta_almacenamiento . '/tipos-grupos')`
- **Renderizado (Blade):** No se utiliza el helper `tenant_asset` ya que puede colisionar con paths internos del paquete Multi-Tenant. Se utiliza directamente `asset('storage/'.$configuracion->ruta_almacenamiento.'/tipos-grupos/'.$tipoGrupo->portada)`

### 2. Procesamiento de Imágenes (GD PHP Nativo)
La plataforma requiere iconos (imágenes) en un formato específico `100x100`. Para una mejor experiencia de usuario (UX):
- En lugar de rebotar la petición con un error de dimensión mediante la validación clásica de Laravel.
- El controlador interviene con `imagecreatefrompng()` nativo de PHP usando GD.
- Ejecuta un auto-crop (recorte central proporción 1:1) de cualquier dimensión y guarda el resultado exacto en 100x100 preservando la transparencia en canal Alpha (`imagealphablending($img, false)`).
- Todo el formateo ocurre de forma transparente desde el Backend.

### 3. Configuraciones Exhaustivas del Tenant (Checkboxes y Booleans)
El modelo `TipoGrupo.php` actúa casi como el motor dinámico para las reglas de reportes de las iglesias, conteniendo switches exhaustivos.
- En la acción de "actualización" (`actualizarTipoDeGrupo`), es imperativo parsear los booleanos con `$request->has('nombre_campo')` explícitamente y persistirlos con `$tipoGrupo->save()` – ya que de lo contrario el usuario no podrá desmarcar las propiedades una vez activadas.

### 4. Integridad de los Nombres de Archivo Base
Los registros en base de datos almacenan *solamente* el nombre del archivo (ej. `imagen-4.png` o `portada-4.jpg`). Todo el "path" relativo se procesa tanto a nivel de guardado como de recuperación, asegurando que las migraciones o sincronizaciones de un Tenant a otro sean tolerantes a cambios de dominio.

## 🚀 Usos sugeridos para el Agente

Al invocar a este Agente puedes ordenarle:
1. "Verifica que el nuevo campo booleano que agregué esté mapeado en la actualización del controlador".
2. "Ayúdame a agregar un nuevo texto por defecto para el sistema de reportes en la tabla de tipos de grupo".
3. "Corrobora cómo está montada la miniatura actual en caso de que queramos expandir los formatos de imagen permitidos".
