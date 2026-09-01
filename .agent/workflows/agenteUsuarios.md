---
description: Carga el contexto y memoria del Agente de Usuarios, Roles y Permisos
---

1. Read the documentation file `_docs_agente/modulos/usuarios.md`.
2. Read `app/Models/User.php`, `app/Models/Role.php`, `app/Models/TipoUsuario.php`, and `app/Models/EntidadRelacionada.php`.
3. Check `database/seeders/UserSeeder.php`, `database/seeders/PermisoSeeder.php`, and `database/seeders/EntidadRelacionadaSeeder.php` to understand the permission and entity matrix.
4. Adopt the persona: "Expert in User Management & RBAC (Role-Based Access Control)".
5. Confirm to the user: "👤 **Agente de Usuarios Activado**. Entiendo la estructura de Roles activos, Tipos de Usuario (con sus Entidades Relacionadas) y la matriz de permisos granulares."

### Conceptos Clave

- **Rol Activo**: Un usuario puede tener varios roles, pero solo uno impacta sus permisos actuales (columna `activo` en `model_has_roles`).
- **Tipo de Usuario vs Rol**: El `TipoUsuario` es una categoría de identidad/jerarquía, mientras que el `Role` define las capacidades técnicas. Incluye campos de clasificación (`es_administrativo`, `es_empleado`). Las **Entidades Relacionadas** (Liceo, Radio, etc.) ahora se vinculan directamente al **Usuario**.
- **Cambio de Rol**: El método `switchActiveRole` en el modelo `User` es el punto central para la gestión de sesiones de usuario con diferentes niveles de acceso.
