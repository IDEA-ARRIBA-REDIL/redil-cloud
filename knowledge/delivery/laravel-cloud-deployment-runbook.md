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
reviewed_at: 2026-09-06
---

# Despliegue seguro del piloto multi-tenant en Laravel Cloud

## Para qué sirve

Este documento es una lista operativa para publicar cambios de Usuarios, Roles/Permisos y Grupos sin dejar migraciones, documentación o evidencia “en el aire”. No autoriza desplegar ni habilitar recursos; cada ejecución productiva necesita un responsable y una ventana aprobada.

## Estado actual que condiciona el procedimiento

- Producción se despliega manualmente desde `main`.
- Cloud construye dependencias y assets, pero no ejecuta pruebas.
- El deploy migra primero la base central y después todos los schemas tenant.
- Preview Environments no están incluidos en el plan.
- PostgreSQL no tiene copias administradas ni restauración puntual.
- Scheduler está apagado y no hay procesos de fondo.

Por estas condiciones, **no debe probarse por primera vez una migración en producción**. Antes del próximo cambio con base de datos se necesita un staging aislado o, como mínimo transitorio, PostgreSQL local con dos tenants y la misma versión de motor.

## Prompt para iniciar el trabajo con Codex

> Quiero implementar `[nombre del cambio]` únicamente en `[Usuarios, Roles/Permisos o Grupos]`. Revisa `ARCHITECTURE.md`, el agente del módulo en `.agent/workflows/`, `knowledge/delivery/development-playbook.md` y este runbook. Crea o refina el Work Item en Kaddo antes de programar. Identifica modelos, migraciones, Livewire/vistas, permisos, rutas, archivos y procesos en segundo plano afectados. No despliegues ni cambies Laravel Cloud. Propón pruebas con dos tenants y dime qué evidencia falta para aprobar el cambio.

## 1. Preparar el Work Item

1. Definir actor, problema y resultado esperado.
2. Adjuntar evidencia disponible: video, pantallazos, reglas narradas, rutas visibles y casos límite.
3. Enlazar los archivos `agente*.md`, modelos, componentes Livewire, vistas, rutas, políticas y migraciones relevantes.
4. Escribir criterios de aceptación que incluyan tenant A, tenant B, permisos y errores.
5. Registrar explícitamente qué módulos quedan fuera.

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

Mientras backups siga en 0 días, los cambios productivos de esquema quedan **no recomendados**.

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
- Staging aislado o alternativa formal mientras no haya Preview Environments.
- Política de backup de PostgreSQL y R2.
- Estrategia de cola, scheduler, caché distribuida y alertas.

## Referencias oficiales

- [Deployments](https://laravel.com/cloud/docs/deployments)
- [Preview Environments](https://laravel.com/cloud/docs/preview-environments)
- [Postgres](https://laravel.com/cloud/docs/resources/databases/postgres)
- [Queues](https://laravel.com/cloud/docs/queues)
- [Scheduled tasks](https://laravel.com/cloud/docs/scheduled-tasks)
- [Logs](https://laravel.com/cloud/docs/logs)
