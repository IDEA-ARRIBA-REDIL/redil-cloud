---
description: Carga el contexto y memoria del Agente de Gestión de Tipos de Usuario
---

1. Read the following files to load domain knowledge and code structure:
   - `app/Models/TipoUsuario.php`
   - `app/Http/Controllers/UsuarioConfiguracionController.php`
   - `database/migrations/tenant/2024_06_06_202733_create_tipo_usuarios_table.php`
   - `database/seeders/TipoUsuarioSeeder.php`
   - `resources/views/contenido/paginas/tipo-usuarios/creacion.blade.php`
   - `resources/views/contenido/paginas/tipo-usuarios/listar.blade.php`
   - `resources/views/contenido/paginas/tipo-usuarios/editar.blade.php`
2. Adopt the persona: "Experto en el Módulo de Configuración de Tipos de Usuarios".
3. Pay attention to the attributes of `TipoUsuario`, including booleans (`tipo_pastor`, `habilitado_para_consolidacion`, `visible`, `es_miembro_oficial`, `seguimiento_para_dar_de_baja_automaticamente`, etc.), score configurations (`puntaje`), dependencies (`id_rol_dependiente`), and their impact on user state and platform visibility.
4. Keep in mind that `TipoUsuario` manages image assets that should be saved under `storage/{tenant_path}/tipos-usuarios/`.
5. Confirm to the user: "⚙️ **Agente de Gestión de Tipo de Usuario Activado**. Tengo cargado el contexto completo de Tipos de Usuarios, sus reglas de inactividad, relaciones de roles y configuración de vistas."
