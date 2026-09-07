---
type: discovery
id: DISC-INFRA-MULTITENANCY-2026-09-06
title: Auditoría inicial de aislamiento multi-tenant en Laravel Cloud
status: reviewed
domains:
  - usuarios
  - roles-permisos
  - grupos
related_work_item: WI-011
reviewed_at: 2026-09-06
---

# Auditoría inicial de aislamiento multi-tenant en Laravel Cloud

## Objetivo y límite

Revisar cómo el piloto Usuarios–Roles–Grupos conserva el tenant en HTTP, PostgreSQL, archivos, caché, colas y tareas programadas. Esta auditoría no habilita otros módulos ni cambia comportamiento funcional.

## Arquitectura comprobada

- `stancl/tenancy` identifica el tenant por dominio en `routes/tenant.php` y bloquea los dominios centrales mediante `PreventAccessFromCentralDomains`.
- PostgreSQL usa una conexión central y `PostgreSQLSchemaManager`; cada tenant opera sobre un schema.
- Están activos los bootstrappers de base de datos, filesystem y colas.
- `tenant_asset()` se utiliza en los recorridos de Usuarios y Grupos para varios recursos del tenant.
- La aplicación declara R2 mediante el disco S3 compatible.
- Laravel Cloud es el destino de despliegue y la versión instalada observada es Laravel 12.53.0.

## Hallazgos priorizados

### A1 — Falta una prueba real con dos tenants — alto

`tests/Feature/UserGroupPilotTest.php` usa una única base SQLite en memoria. Sus siete escenarios y 41 aserciones validan reglas de Usuarios, Roles y Grupos, pero no prueban que dos dominios o schemas no puedan leer datos entre sí.

**Riesgo:** una regresión en inicialización de tenancy podría conservar las pruebas actuales en verde y aun así exponer datos de otra iglesia.

**Tratamiento propuesto:** agregar una suite PostgreSQL que cree dos tenants, datos homónimos y archivos con la misma ruta; probar HTTP, modelos, roles, grupos, caché, cola y finalización del contexto.

### A2 — Caché compartida o no distribuida — alto, condicionado al ambiente

`CacheTenancyBootstrapper` y `RedisTenancyBootstrapper` están desactivados. Además, `config/cache.php` lee `CACHE_DRIVER`, mientras `.env.example` declara `CACHE_STORE`; si producción solo configura la segunda clave, el valor efectivo puede caer en caché de archivos.

**Riesgo:** con Valkey/Redis, claves sin prefijo de tenant pueden colisionar; con archivos, las réplicas de Laravel Cloud no comparten locks ni caché de forma confiable.

**Tratamiento propuesto:** comprobar las variables efectivas en Cloud, escoger una estrategia única de Valkey y prefijado por tenant, y cubrirla con una prueba de dos tenants.

### A3 — R2/S3 no tiene aislamiento automático por tenant — alto, condicionado al ambiente

El filesystem bootstrapper aísla `local` y `public`, pero `s3` está comentado. El disco S3 usa una raíz global `AWS_CARPETA` y numerosos flujos escriben rutas relativas como `img/usuario/...` o `archivos/usuario/...`.

**Riesgo:** si `FILESYSTEM_DISK=s3`, dos tenants podrían generar la misma clave de objeto y sobrescribir o consultar archivos ajenos.

**Tratamiento propuesto:** definir explícitamente una raíz `tenants/{tenant_id}` o un disco tenant-aware para R2, separar recursos globales y privados, y probar claves idénticas en dos tenants.

### A4 — Scheduler de Grupos no demuestra ejecución por tenant — alto

`routes/console.php` programa `reportes:notificar-pendientes` cada 30 minutos sin `onOneServer()` ni `withoutOverlapping()`. El comando consulta modelos tenant, pero no recorre tenants ni inicializa uno cuando comienza en contexto central.

**Riesgo:** el proceso puede fallar en el schema central, no notificar a ninguna iglesia o ejecutarse varias veces al escalar réplicas.

**Tratamiento propuesto:** convertir la invocación central en un coordinador que recorra tenants activos de forma acotada y ejecute el proceso dentro de `$tenant->run(...)`, con locks compartidos e idempotencia.

### A5 — La programación efectiva difiere del Kernel legado — medio

`php artisan schedule:list` muestra `inspire` y `reportes:notificar-pendientes`. La sonda de pagos declarada en `app/Console/Kernel.php` no aparece en el scheduler efectivo de Laravel 12 y en `routes/console.php` está comentada.

**Riesgo:** el equipo puede creer que una tarea está activa basándose en el Kernel legado.

**Tratamiento propuesto:** consolidar el scheduler en la estructura realmente cargada y registrar para cada tarea dominio, tenant, frecuencia, lock e idempotencia. La lógica funcional de pagos queda fuera del piloto; solo se registra la discrepancia operativa.

### A6 — Managed Queues requiere actualización controlada — medio

Laravel Cloud exige actualmente Laravel 12.63.0 o superior para su generación vigente de Managed Queues; REDIL tiene Laravel 12.53.0.

**Riesgo:** habilitar Managed Queues ahora puede fallar en despliegue o cambiar inesperadamente `QUEUE_CONNECTION`.

**Tratamiento propuesto:** mantener el mecanismo actual hasta ejecutar una actualización con pruebas; decidir después entre Managed Queues y Worker Cluster. Todo job debe tolerar reintentos y recuperar el tenant correcto.

### A7 — Dominios de Preview requieren configuración explícita — medio

Los dominios centrales contienen valores de producción y `CENTRAL_DOMAIN`. Los Preview Environments generan dominios propios y no heredan automáticamente todas las variables del ambiente objetivo.

**Riesgo:** una preview puede interpretar su dominio como tenant inexistente o recibir configuración/secrets incorrectos.

**Tratamiento propuesto:** definir `CENTRAL_DOMAIN`, `APP_URL`, cookies y recursos aislados dentro de la automatización de preview; nunca compartir la base productiva.

### A8 — PostgreSQL productivo no tiene copias de seguridad — crítico

El recurso `redil_cloud` usa Laravel Serverless Postgres 17, pero el dashboard muestra retención de **0 días**, copias desactivadas y restauración a un punto en el tiempo desactivada.

**Riesgo:** una eliminación accidental, una migración defectuosa o una corrupción puede dejar sin un punto administrado de recuperación tanto la información central como los schemas de todas las iglesias.

**Tratamiento propuesto:** acordar RPO/RTO, habilitar una política de copias compatible con el presupuesto y ensayar una restauración sobre un recurso aislado. Esta auditoría no habilitó el servicio porque puede modificar costos y operación.

### A9 — Scheduler y procesos de fondo están desactivados — alto

En App compute, Scheduler está apagado, Wake-up interval está apagado y no hay Background processes. Tampoco se observa un recurso de caché ni un worker conectado al ambiente.

**Riesgo:** `reportes:notificar-pendientes` no se ejecuta desde Laravel Cloud. Los jobs en cola solo se procesarán si la conexión efectiva es síncrona o existe un consumidor externo no visible en este ambiente; esto no se puede afirmar sin inspeccionar valores efectivos y operación externa.

**Tratamiento propuesto:** decidir la estrategia de colas, actualizar Laravel antes de considerar Managed Queues, habilitar un consumidor controlado y activar el scheduler solamente después de corregir el recorrido por tenant, locks e idempotencia.

### A10 — Despliegue manual ejecuta todas las migraciones tenant en línea — alto

Push to deploy y Deploy hook están apagados. El deploy ejecuta `php artisan migrate --force` y luego `php artisan tenants:migrate --no-interaction`. El historial reciente contiene varios despliegues fallidos relacionados con migraciones y `composer.lock`.

**Riesgo:** cada despliegue intenta migrar todos los schemas dentro de la ventana de publicación, sin una etapa de pruebas configurada en Cloud, sin lote controlado y sin recuperación automática de datos.

**Tratamiento propuesto:** usar el runbook de `WI-012`, agregar una barrera de pruebas antes de producción, inventariar tenants, diseñar migraciones compatibles y registrar el resultado por tenant. El plan actual no incluye Preview Environments, por lo que debe utilizarse un ambiente de staging separado o una validación local equivalente antes de cualquier migración productiva.

### A11 — Observabilidad insuficiente para explicar tráfico 4XX — alto

Para el último día, Metrics mostró 3.988 solicitudes: 222 respuestas 1XX/2XX/3XX, 3.766 respuestas 4XX y 0 respuestas 5XX. Logs, con filtro Application y Last day, mostró “No logs found”.

**Riesgo:** no hay evidencia disponible para distinguir tráfico inválido, bots, dominios tenant mal resueltos, errores de autenticación o fallas de rutas. El dato no prueba por sí solo una falla del piloto.

**Tratamiento propuesto:** confirmar retención y fuentes de logs, registrar `tenant_id`, dominio, ruta, estado y request ID sin PII, y configurar alertas por proporción anormal de 4XX/5XX.

### A12 — Almacenamiento de objetos es externo a Cloud — medio

El ambiente no tiene buckets de Object Storage adjuntos. La aplicación configura R2/S3 mediante variables externas y el repositorio no demuestra copias, versionado o aislamiento por tenant del bucket.

**Tratamiento propuesto:** inventariar el bucket externo sin documentar secretos, comprobar versionado/retención y aplicar prefijos tenant antes de confiar en este almacenamiento para archivos privados.

## Controles favorables ya presentes

- Separación declarada entre conexión central y conexión tenant.
- Inicialización HTTP por dominio y prevención de acceso desde dominios centrales.
- `QueueTenancyBootstrapper` activo.
- `ConfigurarNuevoTenantJob` encierra operaciones tenant en `$tenant->run(...)`.
- Comandos como `SyncTenantMembers` y `VerificarPagosPendientes` ya muestran el patrón de recorrer tenants con `$tenant->run(...)`, aunque su programación y alcance deben revisarse por separado.
- Baseline funcional del piloto en verde: siete pruebas y 41 aserciones.

## Matriz de verificación

| Superficie | Estado | Evidencia o siguiente comprobación |
|---|---|---|
| Dominio → tenant | Parcialmente comprobado | Middleware y rutas presentes; falta prueba HTTP con dos dominios. |
| PostgreSQL schemas | Configurado | Manager por schemas presente; falta prueba de aislamiento contra PostgreSQL real. |
| Usuarios/Roles/Grupos | Baseline comprobado | `UserGroupPilotTest`: 7 pruebas, 41 aserciones. |
| Caché/locks | Riesgo abierto | Bootstrappers desactivados y variable de configuración inconsistente. |
| R2 | Riesgo abierto | Disco global sin prefijo tenant demostrable. |
| Colas | Parcialmente comprobado | Bootstrapper activo; falta prueba de payload, reintento y limpieza de contexto. |
| Preview | No disponible en el plan | Usar staging separado o validación local; nunca compartir recursos productivos. |
| Backups/restauración | Crítico | Retención 0 días y recuperación puntual desactivada. |
| Scheduler | Desactivado y con riesgo de tenancy | Corregir recorrido tenant y locks antes de habilitarlo. |
| Procesos de fondo | No configurados | Confirmar driver efectivo y definir consumidor. |
| Cache administrada | No configurada | Necesaria para locks distribuidos si se escala. |
| Object Storage administrado | No configurado | R2/S3 es externo; falta inventario de aislamiento y recuperación. |
| Logs/alertas | Insuficiente | Sin logs del último día pese a 3.988 solicitudes y 3.766 respuestas 4XX. |

## Inventario no sensible de Laravel Cloud

Revisión de solo lectura realizada el 6 de septiembre de 2026. No se revelaron secretos, no se ejecutaron comandos y no se modificó el ambiente.

| Elemento | Estado observado |
|---|---|
| Aplicación / ambiente | `redil-cloud` / `main`, rama `main` |
| Dominio | `redil.cloud`, verificado |
| Estado de compute | `Stopped`, compatible con Scale to zero activo después de 5 minutos |
| Región / tamaño | US East (Ohio), Flex 512 MiB, 1 vCPU |
| Runtime | PHP 8.4 y Node 24 |
| Despliegue | Manual; Push to deploy y Deploy hook apagados |
| Build | Composer sin dev, instalación pnpm y `pnpm run build` |
| Migraciones | Central y luego todos los tenants durante el deploy |
| Base de datos | Serverless Postgres 17, 1/4 unidad, scale to zero |
| Backups | Desactivados, retención 0 días, sin point-in-time restore |
| Scheduler | Apagado |
| Background processes | Ninguno |
| Cache | Ningún recurso adjunto |
| Object Storage | Ningún bucket adjunto; integración externa por variables |
| Preview Environments | No incluidos en el plan actual |
| Variables | Existe una variable inyectada sobrescrita; su nombre y valor no se documentaron |

## Evidencia ejecutada

- `vendor/bin/phpunit tests/Feature/UserGroupPilotTest.php --colors=never`: OK, 7 pruebas y 41 aserciones.
- `php artisan schedule:list`: solo aparecen `inspire` y `reportes:notificar-pendientes`.
- `php artisan route:list --except-vendor --path=admin`: las rutas centrales se materializan para los dominios configurados.
- La ejecución local necesitó omitir el cache de configuración generado para producción; el archivo cacheado apunta a `/home/redil2024/public_html/storage/logs` y bloquea comandos locales. Esto debe documentarse en el procedimiento de desarrollo/despliegue.
- Dashboard de Laravel Cloud revisado en modo de solo lectura: compute, deploy, PostgreSQL, backups, scheduler, procesos de fondo, cache, object storage, preview, métricas y logs.

## Próxima decisión

No aplicar correcciones aisladas dentro de esta auditoría. Ejecutar en orden `WI-012`, `WI-013` y `WI-014`, comenzando por una estrategia de despliegue que permita introducir y probar los cambios de aislamiento sin afectar producción.
