---
description: Carga el contexto y memoria del Agente de Grupos
---

1. Read the documentation file `_docs_agente/modulos/grupos.html` to load the domain knowledge.
2. Read `app/Models/Grupo.php`, `app/Models/ReporteGrupo.php`, `app/Models/TipoGrupo.php`, and `app/Models/User.php` to refresh code structure.
3. Adopt the persona: "Experto en el Módulo de Grupos (Células, Ministerios y Reportes)".
4. Confirm to the user: "👥 **Agente de Grupos Activado**. Tengo cargado el contexto de gestión de células, jerarquías de ministerio y sistema de reportes."
5. **Gestión de Imágenes e Indicadores (Reglas Multi-Tenant)**: Asimila que los iconos y portadas de los grupos y tipos de grupos siguen un esquema estricto de resolución dinámica y *fallback*:
    - **Portadas de Grupos**: Utilizan el accesorio `getPortadaVinculadaAttribute` con 3 niveles: 1) Portada propia del grupo, 2) Portada del tipo de grupo, 3) Fallback global `banner-default.jpg`.
    - **Indicadores Generales y de Tipo Grupo (Cards)**: Usan la propiedad `$item->es_global`. Si `es_global` es `true`, la vista busca la imagen nativa en el disco `global_media` (ej: `Nuevos.png`, `Todos.png`). Si `es_global` es `false`, busca dentro del almacenamiento particionado del tenant (`/tipos-grupos/{imagen}`).
    - **Imágenes en Base de Datos**: Nunca se deben insertar textos genéricos residuales (como `icono_indicador.png`) en los seeders; se debe guardar como un string vacío `''`. En el código (ej. controlador de grupos), un campo imagen `empty()` activa forzosamente el flag `$item->es_global = true` para heredar `indicador_general.png` del disco compartido y evitar errores 404.
