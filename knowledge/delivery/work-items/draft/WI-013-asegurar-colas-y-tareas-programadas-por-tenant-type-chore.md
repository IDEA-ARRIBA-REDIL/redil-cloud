---
type: chore
id: WI-013
title: INFRA-03 — Asegurar colas y tareas programadas por tenant
knowledge_level: K3
status: draft
phase: discovery
initiative: RM-003
domains:
  - usuarios
  - roles-permisos
  - grupos
code:
  - app/Jobs/**
  - app/Console/Commands/**
  - routes/console.php
  - config/queue.php
  - config/cache.php
  - config/tenancy.php
capabilities:
  - Operación multi-tenant del piloto
created_at: 2026-09-06
source: WI-011
summary: Garantizar tenant, locks, reintentos e idempotencia en procesos asincrónicos del piloto
---

# INFRA-03 — Asegurar colas y tareas programadas por tenant

## Actor and outcome

El operador puede confiar en que correos y tareas de Grupos se ejecutan una vez, para la iglesia correcta y sin contaminar el siguiente trabajo.

## Current behavior

El bootstrapper de colas está activo, pero no existe una prueba de dos tenants. El recordatorio de reportes se programa sin iteración tenant, `onOneServer()` ni `withoutOverlapping()`. En Cloud, Scheduler está apagado, no hay Background processes ni cache adjunta.

## Target behavior

Jobs y comandos transportan o inicializan explícitamente el tenant, son idempotentes, usan locks distribuidos y generan evidencia operativa con `tenant_id`.

## Impact analysis

- Backend: affected.
- Configuración: affected.
- Caché/locks: affected.
- Notificaciones: affected.
- Operaciones: affected.
- Interfaz: reviewed-not-affected.

## Scope unknowns

- Driver y topología de colas efectivos en producción.
- Estrategia aprobada: Managed Queues después de actualizar Laravel o Worker Cluster.
- Si existe un consumidor externo no visible en el ambiente de Cloud.

## Scope confidence

Medio-alto: Cloud confirma que no ejecuta scheduler ni procesos de fondo, pero el driver efectivo sigue sin verificarse para evitar revelar variables o despertar producción.

## Acceptance Criteria

- Pruebas con dos tenants demuestran contexto correcto y limpieza posterior.
- El scheduler recorre tenants activos de forma acotada.
- Las tareas únicas usan `onOneServer()` y las no concurrentes `withoutOverlapping()` con caché compartida.
- Reintentar un job no duplica el efecto observable.
- Fallos incluyen `tenant_id`, job y causa sin datos sensibles.
- Se documenta la compatibilidad de Laravel antes de habilitar Managed Queues.

## Out of scope

- Modificar lógica de Escuelas, Actividades o Finanzas.
- Habilitar Managed Queues sin actualización y validación previas.

## Validation

- Suite PostgreSQL de dos tenants para jobs y scheduler del piloto.
- `php artisan schedule:list` muestra frecuencia y locks esperados.
- Prueba controlada de reintento y fallo.
- `kaddo guard` sin omisiones inesperadas.

## Definition of Done

- [ ] Estrategia de cola aprobada.
- [ ] Contexto tenant probado en jobs.
- [ ] Scheduler de Grupos corregido y probado.
- [ ] Locks e idempotencia comprobados.
- [ ] Operación documentada.

## Open questions

- ¿Qué valores efectivos tienen `QUEUE_CONNECTION`, `CACHE_DRIVER` y `CACHE_STORE` en producción?
- ¿Se autoriza crear cache y worker, considerando el costo, después de actualizar y probar Laravel?

## Suggested ownership

- `app/Jobs/**`
- `app/Console/Commands/**`
- `routes/console.php`
- `config/{queue,cache,tenancy}.php`
- `tests/Feature/**`
