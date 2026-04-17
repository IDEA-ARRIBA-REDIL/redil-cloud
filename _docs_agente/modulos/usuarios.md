# Gestión de Usuarios, Roles y Permisos

Este módulo gestiona la identidad, niveles de autoridad y permisos granulares de los usuarios en la plataforma.

## Componentes Principales

### 1. Modelo `User` (`app/Models/User.php`)
- **Multi-Role Activo**: El sistema permite que un usuario tenga múltiples roles asignados (Spatie), pero solo uno está **activo** a la vez.
- **Columna `activo`**: La tabla pivote `model_has_roles` ha sido extendida con una columna `activo` (booleano).
- **Método `switchActiveRole(Role $role)`**: Permite cambiar el rol activo del usuario, desactivando los demás en la tabla pivote y limpiando la caché de permisos.

### 2. Modelo `TipoUsuario` (`app/Models/TipoUsuario.php`)
- Define la categoría oficial del usuario (ej: Pastor, Lider, Hermano Mayor, Nuevo).
- **Relación `rolDependiente`**: Cada `TipoUsuario` apunta a un `Role` de Spatie por defecto.
- **Entidad Asociada**: Cada tipo puede estar vinculado a una `EntidadRelacionada` (ej: Liceo, Radio).
- **Flags de Clasificación**:
    - `es_administrativo`: Indica si el perfil tiene funciones de oficina/gestión.
    - `es_empleado`: Define si es personal contratado o contratista.
- Se utiliza para lógica de negocio de alto nivel (puntajes, visibilidad, seguimiento).

### 3. Modelo `Role` (`app/Models/Role.php`)
- Extiende `Spatie\Permission\Models\Role`.
- **Método `verificacionDelPermiso`**: Helper para abortar con 403 si el rol no tiene ciertos permisos.
- Relacionado con: Formuarios, tipos de usuario bloqueados, y privilegios sobre tipos de grupo.

### 4. Modelo `EntidadRelacionada` (`app/Models/EntidadRelacionada.php`)
- Gestiona las diferentes organizaciones vinculadas a la iglesia (SaaS/Multi-tenant).
- **Campos**: NIT, Dirección, Teléfono, Representante Legal.
- Permite segmentar usuarios por su vinculación laboral (ej: empleados del Colegio vs empleados de la Iglesia).
- El registro con ID 1 es siempre "Iglesia" (por defecto).

### 4. Permisos (`Spatie\Permission\Models\Permission`)
- Gestionados vía `PermisoSeeder.php`.
- Siguen la convención `modulo.accion` (ej: `personas.lista_asistentes_todos`).

## Flujo de Logeo y Autorización
1. El usuario se loguea.
2. El sistema verifica el rol que tiene marcado como `activo = true` en `model_has_roles`.
3. Spatie filtra las capacidades basadas en ese rol activo.
4. Si el usuario cambia de rol (ej: de "Lider" a "Administrativo"), se llama a `switchActiveRole`, lo que actualiza la DB y refresca los permisos en la sesión/caché.

## Seeders Críticos
- `EntidadRelacionadaSeeder.php`: Crea las entidades base (Iglesia, Liceo, Radio).
- `RoleSeeder.php`: Define los roles base y sus iconos/propiedades.
- `TipoUsuarioSeeder.php`: Crea las categorías de usuario y las vincula a los roles y entidades.
- `PermisoSeeder.php`: Crea la matriz de permisos y los asigna a los roles.
- `UserSeeder.php`: Crea usuarios de prueba y asigna sus roles iniciales (marcando uno como activo).
