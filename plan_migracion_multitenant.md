# Arquitectura Multi-Tenant (Múltiples Bases de Datos)

El proyecto actual está desarrollado como un **monolito clásico con una sola base de datos**. Aunque existen modelos como `Iglesia` y `Sede`, la aplicación no separa actualmente los datos físicamente por cliente (tenant) mediante múltiples bases de datos.

Para lograr tu objetivo de **"1 solo código, múltiples bases de datos (1 por cliente)"**, la herramienta estándar de la industria para Laravel es el paquete `stancl/tenancy`.

## User Review Required

> [!IMPORTANT]
> **División de Datos (Landlord vs Tenant)**
> Necesitamos definir qué tablas vivirán en la **Base de Datos Central (Landlord)** y cuáles vivirán en las **Bases de Datos de las Iglesias (Tenants)**.
> *   **Landlord (Central):** `tenants` (iglesias registradas), `domains` (subdominios como iglesia1.redil.co), y posiblemente la facturación global de tu SaaS.
> *   **Tenant (Iglesias):** `users`, `grupos`, `sedes`, `reuniones`, `matriculas`, `cursos`, etc. ¡El 95% de tus tablas actuales!

Requiero tu confirmación sobre este enfoque para proceder a instalar el paquete y separar las migraciones.

## Proposed Changes

La implementación requiere refactorizar cómo Laravel maneja la base de datos y las rutas.

### 1. Entorno de Desarrollo Local (Docker / Laravel Sail)
Ya tienes configurado `docker-compose` en tu proyecto mediante **Laravel Sail** (con PHP 8.2 y MySQL 8.0). Aprovecharemos esto para que todo el desarrollo y pruebas de Multi-Tenancy se haga localmente en tu Mac antes de tocar cualquier servidor (AWS/DigitalOcean).

#### Acciones en tu Mac:
1. Asegurarnos de que Docker Desktop esté corriendo.
2. Iniciar el entorno: `./vendor/bin/sail up -d`
3. Usaremos la terminal de Sail (`./vendor/bin/sail artisan ...`) para ejecutar comandos dentro del contenedor, garantizando que el entorno local sea idéntico al de producción.

---

### 2. Infraestructura Base (stancl/tenancy)
Instalar y configurar el paquete que interceptará el subdominio (`iglesia1.localhost`), cambiará silenciosamente la conexión de base de datos a la de esa iglesia, y luego procesará la petición de Laravel normalmente.

#### [MODIFY] `composer.json`
- Añadir dependencia `stancl/tenancy` versión 3.x.

#### [NEW] `config/tenancy.php`
- Configuración del paquete indicando qué modelos son Tenants y qué conexiones se usarán.

#### [MODIFY] `config/database.php`
- Añadir la conexión abstracta `tenant` que será sobreescrita dinámicamente por el paquete en cada petición.

---

### 3. Migraciones (Separación Landlord / Tenant)
Actualmente todas las migraciones están en `database/migrations/`. Debemos separarlas.

#### [NEW] Carpeta `database/migrations/tenant/`
- Moveremos aquí todas las migraciones que pertenecen a los datos de una iglesia (Usuarios, Grupos, Aulas, Cursos, etc.). Cuando crees un nuevo cliente, Laravel correrá estas migraciones automáticamente en la nueva base de datos.
- Modificar el sistema actual para que comandos como `php artisan migrate` operen sobre el esquema adecuado.

---

### 4. Rutas
Tus rutas actuales en `routes/web.php` no tienen noción de subdominios.

#### [NEW] `routes/tenant.php`
- Crearemos este archivo para migrar casi todas las rutas de `routes/web.php` aquí.
- Envolveremos estas rutas con el middleware `InitializeTenancyByDomain::class`. Esto garantiza que nadie pueda acceder a los datos si no entra por el subdominio correcto de una iglesia.

#### [MODIFY] `routes/web.php`
- Quedará solo para las rutas "Centrales" (Landlord), por ejemplo: la landing page de Redil, el registro para que una nueva iglesia compre el software, y el panel de administración global (super admin).

## Verification Plan

### Automated Tests
- Instalaremos el paquete de Tenancy y ejecutaremos pruebas unitarias (PHPUnit) que simulen el acceso a dos subdominios diferentes (`iglesia1.localhost` y `iglesia2.localhost`).
- Verificaremos que al pedir `User::all()` en `iglesia1` no vengan los datos de `iglesia2`.

### Manual Verification
- Configuraremos temporalmente en el archivo `/etc/hosts` de tu Mac dos subdominios apuntando a `127.0.0.1` (ej. `iglesia1.localhost` e `iglesia2.localhost`).
- Levantaremos el servidor con `./vendor/bin/sail up` y navegaremos a ambos subdominios para confirmar que cada uno conecta a una base de datos diferente (creadas dentro de tu contenedor MySQL de Docker) generada por el paquete.
