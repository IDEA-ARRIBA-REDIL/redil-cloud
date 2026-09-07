---
type: chore
id: WI-012
title: INFRA-02 — Definir despliegues y migraciones seguras
knowledge_level: K2
status: draft
phase: refinement
initiative: RM-003
domains:
  - usuarios
  - roles-permisos
  - grupos
code:
  - database/migrations/**
  - database/migrations/tenant/**
  - knowledge/delivery/**
capabilities:
  - Operación multi-tenant del piloto
created_at: 2026-09-06
source: WI-011
summary: Establecer un procedimiento repetible para desplegar y migrar schemas tenant sin afectar producción
---

# INFRA-02 — Definir despliegues y migraciones seguras

## Actor and outcome

El desarrollador despliega un cambio del piloto siguiendo una secuencia verificable para código, base central y schemas tenant.

## Current behavior

Producción se despliega manualmente. Cloud no ejecuta pruebas y corre `migrate --force` seguido de `tenants:migrate` durante cada deploy. El historial reciente incluye fallos de migraciones y `composer.lock`; PostgreSQL no tiene backups y el plan no incluye Preview Environments.

## Target behavior

Cada despliegue usa pruebas, preview aislada, migraciones compatibles, registro por tenant, smoke test y recuperación definida.

## Impact analysis

- Operaciones/release: affected.
- Base de datos: affected.
- Backend funcional: reviewed-not-affected.
- Interfaz: not-applicable.
- Documentación: affected.

## Scope unknowns

- Cantidad real de tenants y duración de la migración completa.
- Ambiente de staging que reemplazará la preview no disponible.
- RPO/RTO y política de backups que autoriza migraciones productivas.

## Scope confidence

Alto para documentar el proceso; medio para ejecutarlo porque faltan staging, backups y decisiones operativas.

## Acceptance Criteria

- El runbook separa build, deploy, migración central y migración tenant.
- Define migraciones backward-compatible y tratamiento de fallos parciales.
- Preview usa base, caché y almacenamiento aislados de producción.
- Incluye smoke test de Usuarios, Roles y Grupos.
- Incluye reversión de código y recuperación de datos.

## Out of scope

- Ejecutar una migración productiva durante la elaboración del runbook.
- Cambiar el esquema funcional.
- Ampliar dominios del piloto.

## Validation

- Ensayo completo en staging aislado o entorno local PostgreSQL equivalente mientras el plan no incluya Preview Environments.
- Registro del resultado por tenant sin incluir información sensible.
- `kaddo guard` sin omisiones inesperadas.

## Definition of Done

- [x] Configuración de Cloud inventariada sin secretos.
- [x] Runbook versionado en estado draft.
- [ ] Preview aislada validada.
- [ ] Procedimiento de migración y reversión ensayado.

## Open questions

- ¿Qué RPO y RTO acepta el responsable del producto?
- ¿Se creará un ambiente staging separado o se actualizará el plan?

## Evidencia actual

- Inventario de Cloud: `knowledge/tech/discovery/infraestructura-multitenancy-laravel-cloud-2026-09-06.md`.
- Runbook propuesto: `knowledge/delivery/laravel-cloud-deployment-runbook.md`.
- No se modificó Laravel Cloud ni se ejecutó un despliegue.

## Suggested ownership

- `database/migrations/**`
- `database/migrations/tenant/**`
- `knowledge/delivery/**`
