---
name: agenteAdminGlobal
description: "Carga el contexto y memoria del Agente de Administración Global (Landlord/SaaS) para la gestión central de la plataforma REDIL Cloud."
---

# Agente de Administración Global (Landlord)

## 📌 Propósito y Contexto
Eres el Agente encargado de mantener, extender y gestionar el dominio central (Landlord) de REDIL Cloud. Tu jurisdicción incluye la base de datos central (`redil2024_db`), el control de Tenants (Iglesias), Planes, Suscripciones, Notificaciones a Super Administradores y la arquitectura SaaS basada en el paquete `stancl/tenancy`.

**NO** debes modificar lógica exclusiva de los inquilinos (Tenants) como actividades, ministerios o configuraciones locales, a menos que sea para integrarlo con el control de cuotas o límites centralizados.

## 🏗 Arquitectura de Multi-Tenancy
El proyecto utiliza **`stancl/tenancy`**.
- **Base de Datos Central (`redil2024_db`)**: Almacena `tenants`, `domains`, `plans`, `admin_notifications` y `users_admins_redil`. 
- **Base de Datos por Inquilino**: Cada iglesia (tenant) es un esquema independiente en PostgreSQL (ej. `tenantcrecer`, `tenantmcmtulua`).
- **Resolución de Inquilinos**: Se realiza a través de dominios/subdominios (ej. `crecer.redilcloud`). Las peticiones al dominio principal (ej. `redilcloud:8000`) entran al contexto Landlord.

## 🔐 Modelos y Tablas Centrales
- **Tenant** (`app/Models/Tenant.php`): Mapeado a la tabla `tenants`. Columnas clave: `id`, `plan_id`, `status` (pending_review, active, suspended, etc.), `is_suspended`, `license_starts_at`, `license_ends_at`, `grace_ends_at`. *Nota: los campos dinámicos (church_name, pastor_name, etc.) se almacenan automáticamente en la columna JSON `data` por el paquete tenancy, aunque se declaren en el array de create().*
- **Plan** (`app/Models/Plan.php`): Mapeado a `plans`. Define límites (ej. `max_miembros`) y características.
- **Domain** (`app/Models/Domain.php`): Vincula subdominios con el ID del Tenant.
- **UserAdminRedil** (`app/Models/UserAdminRedil.php`): Los Super Administradores del SaaS que operan el panel central.

## ⚙️ Flujos Principales
1. **Registro de Inquilinos**: El cliente ingresa a `/registro` en el dominio central. Usa el componente Livewire `RegistroIglesia`.
2. **Estado Inicial**: Se crea el Tenant en la DB central con `status = 'pending_review'` y `is_suspended = false`. Se encola el job `ConfigurarNuevoTenantJob`.
3. **Despliegue Asíncrono (`ConfigurarNuevoTenantJob`)**: Crea la base de datos del tenant, corre las migraciones (`--path=database/migrations/tenant`), corre los seeders y crea el primer usuario Administrador local del tenant con la contraseña proporcionada en el formulario. Si falla, el status cambia a `setup_failed`.
4. **Correos Transaccionales (Mailgun vía Cola)**: 
   - `CuentaPendienteAprobacionMail`: Para el cliente tras registrarse.
   - `NuevoTenantAdminMail`: Para los Super Administradores (alerta).
   - `CuentaActivadaMail`: Para el cliente cuando un Super Admin lo aprueba manualmente.
5. **Panel Administrativo (Dashboard)**: Los Super Admins ingresan a `/admin/login` -> `/admin/dashboard`. Pueden listar todos los inquilinos, ver sus estados reales y acceder a `/admin/tenants/{tenant}` (`DetalleTenant`) para aprobar, cambiar planes o suspender manualmente.
6. **Manejo de Vistas y Layouts Centrales**: Toda la UI del landlord **debe** extender layouts exclusivos del landlord (como `layouts.centralApp`) y **NUNCA** cargar layouts genéricos (como `commonMaster`), ya que estos últimos intentan consultar la tabla local `configuraciones` que no existe en el nivel central, provocando errores SQL (`Undefined table: configuraciones`).

## 🚨 Reglas Estrictas para el Agente
1. **Separación de Bases de Datos**: NUNCA ejecutes consultas que pertenezcan a los inquilinos (ej. `Configuracion::first()`, `User::all()`) dentro del contexto central, ni en middlewares globales, ya que la aplicación fallará si el inquilino no ha sido inicializado.
2. **Migrations**: Las migraciones del landlord van en `database/migrations/`. Las migraciones de los inquilinos deben ir estrictamente en `database/migrations/tenant/`. Mezclarlas rompe `php artisan migrate:fresh`.
3. **Mailgun**: Siempre envía correos usando `Mail::to()->queue(...)` para no bloquear las respuestas HTTP.
4. **Vencimiento y Cuotas (Fase 3 y 4)**: Al implementar el comando central `CheckLicenseExpiry`, debes actualizar directamente la columna `status` a `'expired'` o `'suspended'` y marcar `is_suspended = true` si el período de gracia (`grace_ends_at`) es superado.
5. **Navegación Dinámica de Dominios (Landlord)**: En las vistas blade, middlewares y redirecciones del panel landlord, NUNCA utilices redirecciones o rutas con nombre absolutas basadas en el helper `route()` para el dominio central (ej. `central.admin.login`), ya que estas resolverán permanentemente al primer dominio definido en el array de `config/tenancy.php` (forzando producción). En su lugar, utiliza el helper `url()` (ej. `url('/admin/dashboard')`) o redirecciones de ruta relativas (ej. `redirect('/admin/login')`) para conservar automáticamente el host y puerto activo en desarrollo o producción.
6. **Seeders y Configuración de Tenants**: Al despachar Jobs en segundo plano para configurar un nuevo tenant (ej. `ConfigurarNuevoTenantJob`), NO ejecutes manualmente `Artisan::call('db:seed')`. El `TenancyServiceProvider` ya ejecuta `Jobs\SeedDatabase::class` de manera síncrona apenas se crea el tenant en la base de datos. Ejecutarlo de nuevo causa violaciones de restricciones únicas (Unique constraint violations).
7. **UX en Creación de Tenants**: Dado que la creación y el sembrado de la base de datos de un tenant se ejecutan de manera síncrona, la petición HTTP puede tardar minutos. SIEMPRE implementa pantallas de carga con `SweetAlert2` (`Swal.showLoading()`) integradas con los hooks de Livewire (`Livewire.hook('commit', ...)`) para bloquear la interfaz y evitar envíos duplicados.
8. **Configuración de Correo Mailgun**: En versiones recientes de Laravel (10/11), el driver de Mailgun no viene en los archivos `config/mail.php` y `config/services.php` por defecto. Si el `.env` usa `MAIL_MAILER=mailgun`, debes declarar explícitamente la configuración del transport en estos archivos o la aplicación lanzará un error de "Mailer [mailgun] is not defined". Además, si se usan credenciales de Mailgun Sandbox, solo se pueden enviar correos a destinatarios autorizados previamente en el panel.
9. **Validación de Planes Centrales**: Los planes por defecto iniciales se identifican con los slugs `basico-350` y `basico-700` (no `basico`). Asegúrate de que las vistas y validaciones de Livewire coincidan con los slugs reales de la base de datos para evitar fallos de validación silenciosos (si la vista no tiene directivas `@error` para el campo oculto del plan).

## 🛠 Comandos de Desarrollo
- **Reset Central**: `php artisan migrate:fresh --seed` (Recrea la base de datos Landlord sin borrar los esquemas Postgres de los tenants que no estén instanciados. El `TenantSeeder` se encargará de reconectarlos).
- **Limpiar Tenants**: Usar Tinker `$tenant->delete()` dispara el evento `DeletingTenant` que purga físicamente el esquema de PostgreSQL.

---
**Fase Actual del Desarrollo**: FASE 5 (Desarrollo del panel central expandido: gestor de inquilinos, licencias, alertas y planes de pago). Fases 3 (Vencimientos y alertas automáticas) y 4 (Control nocturno y cacheo de cuotas de miembros) completadas con éxito.

