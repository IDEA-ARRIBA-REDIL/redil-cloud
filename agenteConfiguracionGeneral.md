# Agente de Configuración General

## Propósito

Este documento es la bitácora técnica viva del módulo de configuración general y marca blanca de REDIL Cloud. Debe actualizarse con las decisiones y cambios relevantes que se acuerden en este chat.

## Contexto arquitectónico

- REDIL Cloud es una aplicación SaaS multi-tenant con `stancl/tenancy`.
- Cada iglesia tiene su propia base de datos y su propia fila en `configuraciones`.
- Los usuarios, roles y permisos Spatie de una iglesia pertenecen al tenant.
- El equipo de Software Redil se autentica por separado con el guard central `admin` y el modelo `UserAdminRedil`.
- Los planes y sus prestaciones se almacenan en la base central.
- `plans.incluye_logo` controla la disponibilidad de logos personalizados.
- `plans.incluye_marca_blanca` controla la disponibilidad de marca blanca.

## Decisiones acordadas

### Configuración única por tenant

- Cada iglesia debe tener exactamente una configuración.
- Se agregó `singleton_key` con restricción única a la tabla `configuraciones`.
- El seeder busca exclusivamente el registro con ID `1` para evitar duplicados al volver a ejecutarse.

### Campos extra

Los campos reales y vigentes son:

- `label_seccion_campos_extra`.
- `visible_seccion_campos_extra`.
- `visible_seccion_campos_extra_grupo`.

El antiguo input numérico `seccionCamposExtra` no correspondía a una columna y fue retirado. `ruta_almacenamiento` no debe modificarse desde este módulo.

### Opciones de Escuelas

La sección Escuelas de la configuración general administra también:

- `opcion_material_sede`.
- `envio_material`.
- `cantidad_intentos_traslados`.

### Autorización de configuración general

- La página general y su acción de actualización requieren `configuraciones.subitem_general`.
- La autorización del PATCH está en `UpdateConfiguracionGeneralRequest` y no depende únicamente de ocultar elementos en Blade.

### Marca blanca y logos

- `logo_personalizado` y todos los campos de marca blanca se retiraron del formulario general del tenant.
- Un permiso Spatie oculto no es una frontera válida, porque los permisos pertenecen a la iglesia.
- La administración de marca blanca vive en el panel central de Software Redil.
- Por seguridad, Marca Blanca no se agrega a `ConfiguracionController` ni a `contenido/paginas/configuracion/index.blade.php`, porque ambos pertenecen al panel de la iglesia.
- El acceso parte del detalle central del tenant y está protegido por `auth:admin` y `RevisarSuspensionAdmin`.
- Cada acción Livewire vuelve a comprobar que exista un administrador Redil activo.
- Las consultas y escrituras de `Configuracion` se ejecutan dentro del contexto del tenant seleccionado mediante `$tenant->run()`.
- La activación efectiva se limita con `incluye_logo` e `incluye_marca_blanca` del plan central.
- El cliente no recibe una tarjeta, vista, permiso ni endpoint tenant para administrar esta funcionalidad.

## Implementación actual

### Uso operativo de Marca Blanca

No se agregaron columnas nuevas para Marca Blanca. La disponibilidad se controla desde la base de datos central mediante el plan asignado al tenant:

- `plans.incluye_logo`: permite activar y cargar logos y favicon.
- `plans.incluye_marca_blanca`: permite activar la marca blanca y editar sus textos de identidad.

El equipo de Software Redil debe ingresar al panel central, abrir el detalle de la iglesia y seleccionar **Configurar Marca Blanca**. Si una prestación aparece deshabilitada, debe habilitarse en el plan central o asignarse a la iglesia un plan que la incluya.

Al guardar, el componente entra temporalmente al contexto de la iglesia seleccionada y actualiza su única fila de `configuraciones`. Los archivos se almacenan en el almacenamiento aislado del tenant. No debe modificarse manualmente un permiso del cliente ni agregarse Marca Blanca a sus roles.

Distribución de los datos:

- Base central: tenant, dominio, plan asignado, `plans.incluye_logo`, `plans.incluye_marca_blanca` y administradores Redil.
- Base de cada tenant: `logo_personalizado`, `marca_blanca`, textos de identidad y nombres de los archivos, dentro de su fila de `configuraciones`.
- Almacenamiento de cada tenant: logos y favicon definitivos bajo `img/branding` usando el disco configurado de Laravel.
- Almacenamiento temporal central: Livewire recibe inicialmente la carga en `livewire-tmp`; al guardar, el componente lee el archivo temporal y lo copia al almacenamiento aislado de la iglesia seleccionada.

La vista central no duplica los valores de branding. Solo administra de forma segura la configuración que consumirá la aplicación de cada iglesia.

### Acceso de administradores Redil

- Las cuentas centrales utilizan `UserAdminRedil` y la tabla central `users_admins_redil`.
- Existe `database/seeders/AdminSeeder.php` con cuentas iniciales de desarrollo.
- `DatabaseSeeder` no invoca actualmente `AdminSeeder`, por lo que `php artisan db:seed` no crea ni restablece estas cuentas automáticamente.
- Como usa `firstOrCreate`, ejecutar `AdminSeeder` tampoco cambia la contraseña de una cuenta que ya exista.
- La recuperación segura debe actualizar el hash de contraseña de una cuenta central existente o crear una nueva cuenta central de forma controlada.

### Livewire en dominios centrales y tenants

- Las rutas internas de Livewire son universales porque se usan tanto en el panel central como en las iglesias.
- En un dominio tenant, `InitializeTenancyByDomain` identifica la iglesia y activa su contexto.
- En un dominio central como `redil.ubicalo.com`, la marca `universal` permite continuar sin tenant y conserva la conexión central.
- La misma lógica se aplica a las cargas temporales y vistas previas de archivos de Livewire.
- Después de desplegar cambios de rutas o configuración se deben limpiar y reconstruir las cachés de Laravel.
- Síntoma conocido: si `/admin/tenants/{tenant}/marca-blanca` devuelve 404 después del despliegue, comprobar `php artisan route:list --path=marca-blanca`. Si no aparece, la causa es una caché de rutas anterior; ejecutar `php artisan optimize:clear` y `php artisan optimize`.
- Diagnóstico de despliegue: si `app/Livewire/Central/MarcaBlancaTenant.php` existe en el servidor pero `grep -n "marca-blanca" routes/web.php` no devuelve resultados, el despliegue fue parcial y falta subir `routes/web.php`. La ruta no debe trasladarse a `routes/app.php` porque ese archivo se ejecuta dentro del contexto tenant.
- El seeder se ejecuta directamente con `php artisan db:seed --class=AdminSeeder --no-interaction`. En producción se debe agregar `--force` de manera consciente.

### Configuración general del tenant

- Controlador: `app/Http/Controllers/ConfiguracionGeneralController.php`.
- Form Request: `app/Http/Requests/UpdateConfiguracionGeneralRequest.php`.
- Vista: `resources/views/contenido/paginas/configuracion-general/configuracion-general.blade.php`.
- Modelo: `app/Models/Configuracion.php`.

Incluye validación centralizada, sanitización del mensaje de bienvenida, casts de tipos, invalidación de `configuracion_global` y manejo de las opciones generales autorizadas.

### Marca blanca central

- Componente: `app/Livewire/Central/MarcaBlancaTenant.php`.
- Vista: `resources/views/livewire/central/marca-blanca-tenant.blade.php`.
- Entrada: detalle del tenant en `resources/views/livewire/central/detalle-tenant.blade.php`.
- Ruta central: `/admin/tenants/{tenant}/marca-blanca`.

La pantalla administra:

- Activación de logo personalizado.
- Activación de marca blanca.
- Nombre y URL del creador.
- Color del nombre de la aplicación.
- Descripción del login.
- Sufijo SEO.
- Versión personalizada.
- Logo principal.
- Logo para fondos claros.
- Favicon.

Los logos aceptan PNG o JPEG, máximo 2 MB y hasta 4096 px por lado. Ya no se rechazan por su proporción: al guardar se convierten automáticamente a PNG de 300 × 150 px, centrando y escalando el contenido sobre un lienzo transparente para evitar recortes. El favicon acepta ICO o PNG y máximo 512 KB. Los archivos anteriores se eliminan únicamente después de actualizar correctamente la configuración.

Si una carga falla antes de guardar la configuración, los nuevos archivos parciales se eliminan. Los recursos anteriores solo se borran después de persistir el reemplazo.

## Verificación técnica

- Los archivos PHP modificados pasan la validación de sintaxis.
- Laravel Pint se ejecuta sobre los archivos PHP intervenidos.
- Las plantillas Blade compilan correctamente.
- La ruta central de Marca Blanca aparece para todos los dominios centrales y aplica los middleware `auth:admin` y `RevisarSuspensionAdmin`.
- No se crearon pruebas automatizadas para el controlador, de acuerdo con lo solicitado.

## Exclusiones pendientes

- Las inconsistencias de rutas de archivos en PWA se revisarán en una tarea posterior.
- `ruta_almacenamiento` no se modifica.

## Personalización de la pantalla de acceso

La implementación se divide en configuración predeterminada central y configuración particular de cada tenant:

- Software Redil administra el valor predeterminado desde `/admin/login-predeterminado` con el guard central `admin`.
- Los metadatos predeterminados viven en `login_branding_defaults` y `login_branding_default_slides`, dentro de la base central.
- Los archivos predeterminados viven en `global_media/login-branding/default`. El recurso histórico `global_media/Banner-login.png` continúa como respaldo final.
- Cada tenant tiene una sola fila en `login_personalizaciones` y hasta 20 registros ordenados en `login_carrusel_imagenes`.
- Los modelos tenant declaran ambos nombres de tabla explícitamente porque el pluralizador de Laravel no reconoce correctamente estos nombres en español.
- Los archivos particulares se guardan en el disco público aislado del tenant bajo `img/login-branding`.
- La resolución del login usa esta prioridad: recurso personalizado tenant habilitado, recurso predeterminado central y, finalmente, `Banner-login.png`.
- Fondo izquierdo y carrusel heredan el valor predeterminado de manera independiente cuando el tenant no ha cargado contenido propio.
- El fondo personalizado se aplica directamente a la columna izquierda con `background-size: cover`, posición centrada y sin repetición. No se agrega una capa oscura automática sobre la imagen.
- Cada archivo acepta JPG, PNG o WebP, máximo 5 MB y 6000 px por lado. El carrusel permite máximo 20 imágenes almacenadas.
- Cada diapositiva puede guardar una URL HTTP o HTTPS opcional. Al hacer clic en la imagen del login, el destino se abre en una pestaña nueva con protección `noopener noreferrer`.
- El carrusel usa Bootstrap, `object-fit: cover`, carga diferida excepto en la primera imagen y pausa el movimiento automático cuando el navegador solicita movimiento reducido.

### Control comercial y de permisos

La capacidad se activa únicamente cuando se cumple al menos una de estas condiciones:

`(plan.incluye_logo && configuracion.logo_personalizado) || (plan.incluye_marca_blanca && configuracion.marca_blanca)`

- El permiso `configuraciones.subitem_personalizacion_login` permite decidir qué rol del tenant administra la pantalla, pero nunca concede la prestación comercial.
- La tarjeta **Personalización del login** exige simultáneamente capacidad comercial y permiso.
- La ruta tenant carga primero una vista Blade tradicional con `layouts/contentNavbarLayout`; dentro de su sección `content` se monta el componente `login.personalizacion-login`. No se utiliza el componente como página completa porque ese layout trabaja con `@yield('content')`, no con `$slot`.
- La tarjeta **Plantilla** y las acciones del gestor de colores también exigen capacidad comercial y su permiso existente `configuraciones.subitem_plantilla`.
- Todas las acciones Livewire vuelven a comprobar ambos requisitos en el servidor.
- Al bajar de plan, los datos y archivos se conservan, pero no se sirven en el login ni se permite administrarlos. Se reactivan al recuperar la prestación.

## Migraciones pendientes de despliegue

- `database/migrations/tenant/2026_09_13_165342_add_singleton_key_to_configuraciones_table.php`.
- `database/migrations/2026_09_13_185158_create_login_branding_defaults_table.php`.
- `database/migrations/2026_09_13_185200_create_login_branding_default_slides_table.php`.
- `database/migrations/tenant/2026_09_13_185202_create_login_personalizaciones_table.php`.
- `database/migrations/tenant/2026_09_13_185205_create_login_carrusel_imagenes_table.php`.
- `database/migrations/2026_09_14_103704_add_link_url_to_login_branding_default_slides_table.php`.
- `database/migrations/tenant/2026_09_14_103708_add_link_url_to_login_carrusel_imagenes_table.php`.

Las migraciones sin el segmento `/tenant/` se ejecutan en la base central; las ubicadas en `database/migrations/tenant` se ejecutan con el mecanismo de migraciones tenant del proyecto. Después se ejecutan `LoginBrandingDefaultSeeder` en la base central y el seeder incremental `LoginBrandingPermissionSeeder` en cada tenant. El permiso también permanece en `PermisoSeeder` para instalaciones nuevas completas.

## Historial de acuerdos

### 2026-09-13

- Se revisó el flujo completo de configuración general, migraciones y consumidores.
- Se corrigieron campos sin persistencia, autorización, validación, tipos, caché e IDs duplicados.
- Se acordó separar totalmente Marca Blanca del panel administrable por la iglesia.
- Se eligió el guard central de Software Redil como frontera de seguridad.
- Se creó esta bitácora viva para registrar los acuerdos posteriores del chat.
- Se implementó la presentación predeterminada y personalizada del login, con aislamiento de archivos, carrusel y control por licencia.
- Se limitó el editor de colores a tenants con una prestación de personalización activa.
