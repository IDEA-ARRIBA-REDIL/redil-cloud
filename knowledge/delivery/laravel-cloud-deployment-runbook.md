---
type: runbook
id: RUNBOOK-LARAVEL-CLOUD-PILOTO
title: Despliegue seguro del piloto multi-tenant en Laravel Cloud
status: draft
related_work_items:
  - WI-012
  - WI-013
  - WI-014
domains:
  - usuarios
  - roles-permisos
  - grupos
reviewed_at: 2026-09-27
---

# Despliegue seguro del piloto multi-tenant en Laravel Cloud

## Para qué sirve

Para decisiones de lanzamiento, ambientes, responsables y migración de iglesias, consultar primero [DESPLIEGUEREDILCLOUD](DESPLIEGUEREDILCLOUD.md). Ese plan recoge las aclaraciones posteriores del responsable: el montaje de Cloud no tiene usuarios actuales y AWS de Manantial es independiente. Las referencias a «producción» en el inventario histórico siguiente describen la configuración observada entonces, no acreditan uso productivo actual ni describen AWS/cPanel.

Este documento es una lista operativa para publicar cambios de Usuarios, Roles/Permisos y Grupos sin dejar migraciones, documentación o evidencia “en el aire”. No autoriza desplegar ni habilitar recursos; cada ejecución productiva necesita un responsable y una ventana aprobada.

## Evidencia histórica y verificación del ambiente

Los siguientes puntos proceden del inventario del **2026-09-06**, no de una inspección actual. La revisión del **2026-09-27** contrasta documentación oficial; no accedió al panel ni modificó recursos. Reconfirmar los puntos relevantes antes de operar:

- Producción se despliega manualmente desde `main`.
- Cloud construye dependencias y assets, pero no ejecuta pruebas.
- El deploy migra primero la base central y después todos los schemas tenant.
- En aquella revisión se registró que Preview Environments no estaba disponible en la cuenta. La documentación actual los ofrece en todos los planes; confirmar disponibilidad y configuración en la cuenta, sin asumir que ya están habilitados.
- PostgreSQL no tiene copias administradas ni restauración puntual.
- Scheduler está apagado y no hay procesos de fondo.

Por estas condiciones, **no debe probarse por primera vez una migración en producción**. Antes del próximo cambio con base de datos se necesita un staging aislado o, como mínimo transitorio, PostgreSQL local con dos tenants y la misma versión de motor.

En cada revisión registrar ambiente, fecha, fuente, resultado y responsable. Conservar los hallazgos históricos como evidencia y documentar su resolución por separado. La existencia de una capacidad en Cloud no demuestra su activación en REDIL.

## Reglas de Cloud que condicionan el desarrollo

Documentación consultada el **2026-09-27**; verificar de nuevo antes de aplicar cambios operativos.

### Build y deploy no son intercambiables

- `config:cache` y `optimize`, cuando se utilicen, van en build, no en deploy.
- Los cambios de filesystem hechos por comandos de deploy no persisten en la aplicación; no usar esa fase para generar archivos necesarios en ejecución.
- Build y comandos de deploy tienen un límite de 15 minutos cada uno. Estimar el conjunto de migraciones central/tenant; si no cabe con margen, diseñar un procedimiento por etapas antes de publicar.
- No añadir por rutina `queue:restart`, `horizon:terminate`, `optimize:clear` o `storage:link` a los comandos de deploy: Cloud documenta su gestión automática o advierte que son inadecuados allí.

Fuente: [Environments](https://laravel.com/cloud/docs/environments). No reutilizar cachés compiladas de producción en desarrollo: en este repositorio se observaron rutas locales cacheadas hacia `/home/redil2024/public_html`; su corrección es una tarea aparte, no una instrucción para borrar cachés productivas.

### Preview aislada y aprobada

Cloud anuncia previews en todos los planes; Starter incluye una automatización por organización. Sus recursos generan consumo. Confirmar acceso y presupuesto antes de habilitarla. Elegir base, caché y almacenamiento aislados: Cloud también permite compartir recursos del ambiente objetivo, lo que no es apropiado para ensayar migraciones sobre producción.

Las automatizaciones actuales definen sus propias variables; no heredan automáticamente las del ambiente objetivo. Comprobar aparte dominio central, resolución de tenants, cookies e integraciones externas de REDIL. Usar datos sintéticos y credenciales de prueba; el aislamiento de ambientes no sustituye las pruebas de aislamiento entre iglesias.

Fuente: [Preview Environments](https://laravel.com/cloud/docs/preview-environments).

### Scheduler, réplicas y reposo

Cloud captura `schedule:list` al desplegar y puede despertar aplicaciones Laravel para ejecutar las tareas registradas. Un cambio de horario requiere nuevo despliegue para actualizar esa captura. No atribuir una tarea ausente únicamente al reposo del ambiente: comprobar primero scheduler habilitado, horario efectivo y contexto tenant.

Con varias réplicas, controlar duplicación con `onOneServer()` cuando corresponda y solapamientos con `withoutOverlapping()`, usando locks compartidos. Evaluar también si la frecuencia de tareas impide volver al reposo y aumenta consumo.

Fuente: [Scheduled Tasks](https://laravel.com/cloud/docs/scheduled-tasks).

### Colas: compatibilidad y cambios de comportamiento

El `composer.lock` comprobado el 2026-09-27 declara Laravel 12.53.0. Para Managed Queues, la documentación exige al menos 12.63.0 en la rama Laravel 12 y `aws/aws-sdk-php`. No habilitarlas sin comprobar las dependencias vigentes y probar tenant, reintentos e idempotencia.

Cloud establece `QUEUE_CONNECTION=cloud` al desplegarlas: inventariar trabajos sin conexión explícita antes del cambio. Flex tiene un límite de ejecución de 90 segundos; seleccionar la alternativa adecuada para trabajos largos. Workers y Managed Queues no son configuraciones equivalentes.

**Aviso fechado:** Cloud anuncia el retiro de queue clusters y de la generación anterior de managed queues para el 2026-09-30. Verificar si la cuenta utiliza alguno; el inventario histórico no demuestra uso ni descarta cambios posteriores. No convertir este aviso en una migración automática.

Fuente: [Managed Queues](https://laravel.com/cloud/docs/queues).

### Evidencia de errores y retención

Distinguir Application logs de Access logs. Ante 4XX, consultar también Access logs; un filtro de aplicación vacío no demuestra ausencia de tráfico o fallos. Registrar intervalo, ambiente y filtros. Confirmar la retención y cuota del plan antes de depender de Cloud como archivo histórico; conservar evidencia sanitizada sin datos personales ni secretos.

Fuente: [Logs](https://laravel.com/cloud/docs/logs).

## Prompt para iniciar el trabajo con Codex

> Quiero implementar `[nombre del cambio]` únicamente en `[Usuarios, Roles/Permisos o Grupos]`. Revisa `ARCHITECTURE.md`, el agente del módulo en `.agent/workflows/`, `knowledge/delivery/development-playbook.md` y este runbook. Crea o refina el Work Item en Kaddo antes de programar. Identifica modelos, migraciones, Livewire/vistas, permisos, rutas, archivos y procesos en segundo plano afectados. No despliegues ni cambies Laravel Cloud. Propón pruebas con dos tenants y dime qué evidencia falta para aprobar el cambio.

## 1. Preparar el Work Item

1. Definir actor, problema y resultado esperado.
2. Adjuntar evidencia disponible: video, pantallazos, reglas narradas, rutas visibles y casos límite.
3. Enlazar los archivos `agente*.md`, modelos, componentes Livewire, vistas, rutas, políticas y migraciones relevantes.
4. Escribir criterios de aceptación que incluyan tenant A, tenant B, permisos y errores.
5. Registrar explícitamente qué módulos quedan fuera.
6. Clasificar impacto operativo como afectado, revisado-no-afectado o pendiente de verificar. Si aplica, enlazar WI-012, WI-013 o WI-014 y anotar la evidencia requerida; documentarlo no autoriza ejecutarlo.

El Work Item pasa a implementación solo cuando otra persona puede entender qué construir y cómo comprobarlo sin depender del chat original.

## 2. Validar antes de integrar

Ejecutar, como mínimo:

1. Pruebas unitarias o feature del cambio.
2. Suite del piloto `UserGroupPilotTest`.
3. Prueba PostgreSQL con dos tenants para cualquier cambio que toque datos, archivos, caché, roles, grupos, jobs o scheduler.
4. Build frontend si se modificaron Livewire, Blade, JavaScript o estilos.
5. Laravel Pint para PHP modificado y las validaciones de Kaddo.

Guardar en el Work Item los comandos, resultado y fecha. No copiar datos reales, tokens ni variables secretas.

## 3. Revisar una migración multi-tenant

Antes de publicar, la migración debe:

- ser compatible con la versión anterior del código durante el cambio de release;
- evitar renombrar o eliminar columnas en el mismo despliegue que deja de usarlas;
- poder repetirse o identificar con claridad un tenant ya migrado;
- probarse contra una copia sintética o staging, nunca contra el único respaldo productivo;
- estimar duración total y bloqueo según cantidad de tenants;
- producir un registro por `tenant_id`, migración, inicio, resultado y error sin PII.

Para cambios destructivos usar dos releases: primero añadir y migrar datos; después retirar el campo o comportamiento antiguo cuando se haya comprobado que nadie lo utiliza.

## 4. Puerta previa a producción

No iniciar el deploy si falta cualquiera de estos puntos:

- revisión de código aprobada;
- pruebas y build en verde;
- Kaddo y documentación actualizados;
- inventario de tenants y tiempo estimado de migración;
- copia recuperable y restauración ensayada cuando hay cambios de datos;
- responsable disponible durante despliegue y observación;
- plan de detención/reversión escrito.

Si se confirma que backups continúa en 0 días, o no hay evidencia de una copia recuperable, no aprobar cambios productivos de esquema hasta resolver la puerta de recuperación.

## 5. Desplegar

El responsable autorizado inicia manualmente el deploy y conserva la evidencia del identificador del despliegue y commit. Debe observar por separado:

1. build de Composer y pnpm;
2. migración central;
3. migración tenant;
4. arranque de la aplicación;
5. smoke test.

Si falla un tenant, no se debe reintentar a ciegas toda la publicación. Registrar qué schemas completaron, detener el avance y determinar si la migración es segura de repetir.

## 6. Smoke test del piloto

Usar dos iglesias de prueba autorizadas y comprobar:

- acceso por el dominio correcto y rechazo del dominio ajeno;
- listado, creación/edición y activación de usuarios según su permiso;
- asignación de tipo de usuario y roles independientes;
- creación/edición de grupo;
- asignación y retiro de encargados e integrantes;
- georreferencia del grupo;
- ausencia de datos, archivos o roles del otro tenant;
- logs sin nuevos 5XX y proporción 4XX explicable.

No ejecutar acciones destructivas sobre usuarios productivos para completar el smoke test.

## 7. Recuperación

- **Fallo de código sin migración destructiva:** volver al último release estable y repetir el smoke test.
- **Migración parcial repetible:** corregir la causa y continuar solamente sobre tenants pendientes, conservando la evidencia.
- **Pérdida o corrupción de datos:** detener escrituras y restaurar en un recurso aislado antes de decidir cualquier reemplazo productivo.
- **Fuga entre tenants:** tratar como incidente de seguridad, cortar el flujo afectado, preservar logs y no ocultar el alcance.

Un rollback de código no revierte automáticamente datos. Nunca improvisar un `down()` productivo sin copia y ensayo previo.

## 8. Cerrar el trabajo

Actualizar el Work Item con commit, despliegue, migraciones, tenants procesados, resultados de pruebas, evidencia visual y pendientes. Regenerar Kaddo y cerrar el WI solo cuando código, pruebas, documentación y operación cuenten la misma historia.

## Decisiones pendientes antes de declarar este runbook aprobado

- RPO: cuánto dato se acepta perder.
- RTO: cuánto tiempo puede estar indisponible el servicio.
- Responsable y suplente de despliegues e incidentes.
- Preview o staging aislado, con disponibilidad en la cuenta, recursos y presupuesto confirmados; alternativa local formal mientras no se habilite.
- Política de backup de PostgreSQL y R2.
- Estrategia de cola, scheduler, caché distribuida y alertas.

## Referencias oficiales

- [Environments: build y deploy](https://laravel.com/cloud/docs/environments)
- [Deployments](https://laravel.com/cloud/docs/deployments)
- [Preview Environments](https://laravel.com/cloud/docs/preview-environments)
- [Postgres](https://laravel.com/cloud/docs/resources/databases/postgres)
- [Queues](https://laravel.com/cloud/docs/queues)
- [Scheduled tasks](https://laravel.com/cloud/docs/scheduled-tasks)
- [Logs](https://laravel.com/cloud/docs/logs)
