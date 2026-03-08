---
description: Carga el contexto y memoria del Agente de Multi-Tenancy (Arquitectura SaaS)
---

# Agente de Multi-Tenancy (Arquitectura SaaS)

Este documento describe la arquitectura multi-tenant implementada con `stancl/tenancy` en Laravel 12, detallando la configuración de bases de datos, rutas y la solución técnica para los seeders.

## 1. Arquitectura de Base de Datos

El sistema utiliza un esquema de **Multi-Base de Datos** (Database-per-tenant).

### Base de Datos Central (Landlord)

- **Nombre**: `redil2024_db`
- **Tablas Críticas**:
  - `tenants`: Almacena el ID del tenant (ej: `iglesia1`).
  - `domains`: Mapea subdominios a tenants (ej: `iglesia1.redilcloud`).
- **Uso**: Gestión global de clientes y suscripciones.

### Bases de Datos de Tenants

- **Prefijo**: `tenant` + `id_del_tenant` (ej: `tenantiglesia1`).
- **Conexión**: Se utiliza la conexión `pgsql` (PostgreSQL) como plantilla. El paquete `stancl/tenancy` cambia dinámicamente el nombre de la DB y el username/password en tiempo de ejecución.

## 2. Configuración de Tenancy

### TenancyServiceProvider

- Registrado en `bootstrap/providers.php`.
- Gestiona eventos como `SeedDatabase` y la inicialización de la conexión.
- **Rutas**: Carga `routes/tenant.php` automáticamente.

### Middleware

- `InitializeTenancyBySubdomain`: Identifica al tenant mediante el subdominio (ej: `iglesia1` de `iglesia1.redilcloud`).
- `PreventAccessFromCentralDomains`: Evita que se acceda a rutas de tenants desde el dominio principal.

## 3. Estructura de Rutas

Para permitir que la aplicación completa funcione dentro de cada tenant, se realizó la siguiente separación:

1.  **`routes/web.php` (Central)**:
    - Solo accesible desde el dominio principal (`redilcloud`).
    - Contiene la landing page y administración de tenants.
2.  **`routes/app.php` (Aplicación)**:
    - Contiene **toda** la lógica original del proyecto (Login, Dashboard, LMS, etc.).
    - No se carga directamente en `bootstrap/app.php`.
3.  **`routes/tenant.php` (Filtro Tenant)**:
    - Envuelve las rutas de `app.php` bajo el middleware de tenencia.
    - `require __DIR__ . '/app.php';` dentro del grupo de middleware del tenant.

## 4. Solución Técnica para Seeders

### Problema de Rutas (Storage)

En Laravel Multi-tenant, el disco `local` o `public` se aísla por tenant (`storage/app/tenant1/...`). Esto causaba que los seeders no encontraran los archivos SQL/JSON de desarrollo.

### Corrección

Todos los seeders fueron actualizados para usar la ruta absoluta de la instalación central:

- **Antes**: `Storage::path('archivo.sql')` (Busca en la ruta aislada del tenant).
- **Después**: `base_path('storage/app/archivos_desarrollador/archivo.sql')` + `file_get_contents()`.
- **Resultado**: Los seeders ahora pueden cargar datos compartidos (países, municipios, sedes) en cualquier base de datos de tenant sin fallar.

## 5. UserSeeder y Soft Deletes

Para evitar errores de "Unique violation" en el email durante el re-seeding:

- Se implementó `User::withTrashed()->firstOrCreate(...)`.
- Esto permite al seeder encontrar usuarios existentes que fueron eliminados suavemente (`deleted_at`) en lugar de intentar insertarlos de nuevo.

## 6. Comandos Útiles

### Sembrar un Tenant específico:

```bash
php artisan tenants:seed --tenants=iglesia1
```

### Crear un Tenant y su Dominio (Tinker):

```php
$t = \App\Models\Tenant::create(['id' => 'iglesia1']);
$t->domains()->create(['domain' => 'iglesia1.redilcloud']);
```

### Ejecutar migraciones de tenants:

```bash
php artisan tenants:migrate
```

## 8. Consideraciones sobre Seeders (Tenant vs Landlord)

- **Separación de responsabilidades**: Laravel Multi-tenant requiere que los seeders globales y los del tenant estén separados.
- **`TenantDatabaseSeeder.php`**: Creado exclusivamente para poblar datos **dentro** de la base de datos de cada inquilino (ej. Roles, Tipos de Documentos). Este archivo fue configurado como el `seeder_parameters` raíz en `config/tenancy.php`.
- **Conflictos de Identidad (IDs) en Postgres**: Al importar SQL crudo en los seeders (ej. `CampoInformeExcelSeeder`), los IDs se insertan directamente. Esto desincroniza la secuencia de auto-incremento de PostgreSQL. Para solucionarlo, siempre se debe ejecutar un `DB::statement("SELECT setval(pg_get_serial_sequence('table_name', 'id'), coalesce(max(id),0) + 1, false) FROM table_name;");` al finalizar la inserción de archivos SQL.

## 9. Archivos Públicos y Global Assets (CSS/JS)

Por defecto, la función `asset()` de Laravel trata de buscar recursos en el subdirectorio `/tenant/id/` cuando se entra a `iglesia1.redilcloud`. Para mantener un solo estilo o hoja de estilos base para todos:

- **`config/tenancy.php`**: La opción `'asset_helper_tenancy' => false` permite que llamadas a `asset('img/logo.ico')` apunten directamente a `public/img` resolviendo errores `404 Not Found` en el entorno Multi-tenant. No hay necesidad de usar `global_asset()`.

### Personalización de Estilos por Tenant (ThemeService)

- Si un inquilino edita sus colores (Theme Builder), el código estandarizado CSS debe guardarse en el almacenamiento público **aislado por tenant** usando **`.css`** (ej. `storage/iglesia1/theme/_custom-variables.css`). No se debe guardar en una ruta `global` o un tenant sobreescribirá los colores del otro.
- En la plantilla Layout (`commonMaster.blade.php`), la ruta debe inyectarse dinámicamente usando `asset('storage/' . tenant('id') . '/theme/_custom-variables.css')`.
- **Navegadores y SCSS**: Al incluir el estilo con un tag `<link>`, es obligatorio que tenga extensión `.css`. Los navegadores ignoran la importación de extensiones `.scss` directas.
- Considerar agregar un Query String (`?v={{ time() }}`) a la llamada al asset CSS para invalidar la caché intensa del navegador de los usuarios al cambiar de tema.

## 10. Fallos 419 Page Expired (CSRF) y Livewire

Livewire V3 procesa las solicitudes AJAX en `/livewire/update`. Múltiples componentes crasheaban (error `419`) porque Livewire no arrancaba el contexto del Tenant Inquilino, comparando la sesión contra la base de datos central en su lugar.

- **Solución Final**: En `app/Providers/AppServiceProvider.php`, se debe configurar el _Update Route_ manualmente para inyectar los middlewares del subdominio.

```php
\Livewire\Livewire::setUpdateRoute(function ($handle) {
    return \Illuminate\Support\Facades\Route::post('/livewire/update', $handle)
        ->middleware([
            'web',
            \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
            \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
        ]);
});
```

## 11. Problemas con Caché y ThemeScss (BadMethodCallException)

Laravel Multi-tenant intenta usar "Cache Tags" aislando la caché de un tenant (`[tenant_id] variable`).

- **El Problema**: El driver de caché configurado por defecto como `database` o `file` en Laravel **no soporta Cache Tags**. Al borrar caché internamente detonará un `BadMethodCallException: This cache store does not support tagging`.
- **Solución temporal**: Hasta que se implemente Redis o Memcached, se debe desactivar (comentar) el `Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class` en `config/tenancy.php`.
