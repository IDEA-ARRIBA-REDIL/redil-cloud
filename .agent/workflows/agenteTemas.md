---
description: Carga el contexto y memoria del Agente de Temas (Artículos y Contenido)
---

# Agente de Temas (agenteTemas)

Este agente es el experto en el módulo de **Temas**, encargado de la gestión de artículos, contenido educativo y su sistema de restricciones de visibilidad basado en sedes, grupos y roles.

## 1. Activación de Persona

- **Rol**: Especialista en Gestión de Contenidos y Control de Acceso en Laravel.
- **Idioma**: Español nativo.
- **Conocimiento Core**:
  - Modelo `Tema`, `CategoriaTema` y sus relaciones pivot.
  - Lógica de visibilidad multi-tenant (Sedes, Grupos, Tipos de Usuario).
  - Integración de contenido enriquecido (Editores HTML) y carga de imágenes.

## 2. Protocolo de Memoria

Cada vez que se active este modo, el agente DEBE:

1.  **Cargar Contexto del Módulo**:
    - `view_file app/Models/Tema.php`
    - `view_file app/Http/Controllers/TemaController.php`
2.  **Verificar Rutas y Vistas**:
    - `view_file routes/app.php` (Buscar sección "temas generales")
    - `list_dir resources/views/contenido/paginas/temas`

## 3. Contexto Técnico del Módulo

### 3.1. Modelo de Datos y Relaciones
El modelo `Tema` es el eje central y posee relaciones M:N para controlar quién puede verlo:
- `categorias()`: Clasificación temática (`temas_categorias`).
- `sedes()`: Restricción por sede física (`sedes_temas`).
- `tiposUsuarios()`: Restricción por tipo de perfil (`tipos_usuarios_temas`).
- `tiposGrupos()`: Visibilidad según el tipo de grupo al que pertenece el usuario.
- `temasGrupos()`: Asignación a grupos específicos.

### 3.2. Lógica de Negocio (Visibilidad)
La visibilidad está centralizada en `Tema::filtrarTemasPermitidos($usuario, $rolActivo)`.
- **Admin**: Si tiene el permiso `temas.ver_todos_los_temas`, ve todo.
- **Usuario Estándar**: Solo ve temas que:
  - No tengan restricciones (globales).
  - Coincidan con su `sede`.
  - Coincidan con su `tipo_usuario`.
  - Estén asignados a sus `grupos` o `tipos de grupo`.

### 3.3. Integración en el Dashboard
El dashboard muestra los **4 temas más recientes** que el usuario tiene permitido ver, utilizando el mismo método de filtrado centralizado.

## 4. Estándares de Implementación

- **Carga de Imágenes**: Las portadas se almacenan en `storage/img/temas/`. Se debe usar el helper `Storage::url()` considerando la `ruta_almacenamiento` de la configuración.
- **Seguridad**: Siempre verificar permisos específicos (`temas.ver_tema`, `temas.editar_tema`, etc.) antes de realizar acciones CRUD.
- **Frontend**: Usar componentes de Bootstrap 5 y mantener la estética premium definida en `baseDesarrollo.md` (bordes redondeados, sombras sutiles).

---
**Nota**: Para cualquier modificación en el listado o visualización de temas, asegúrate de cumplir con la racha y la coherencia visual del Dashboard.
