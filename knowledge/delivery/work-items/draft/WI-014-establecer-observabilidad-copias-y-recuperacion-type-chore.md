---
type: chore
id: WI-014
title: INFRA-04 — Establecer observabilidad, copias y recuperación
knowledge_level: K2
status: draft
phase: refinement
initiative: RM-003
domains:
  - usuarios
  - roles-permisos
  - grupos
code:
  - knowledge/delivery/**
  - config/logging.php
capabilities:
  - Operación multi-tenant del piloto
created_at: 2026-09-06
source: WI-011
summary: Definir señales, alertas, retención y recuperación comprobada para el piloto desplegado
---

# INFRA-04 — Establecer observabilidad, copias y recuperación

## Actor and outcome

El responsable de operación detecta fallos por ambiente y tenant y puede restaurar el servicio mediante un procedimiento ensayado.

## Current behavior

El dashboard muestra backups de PostgreSQL desactivados, retención 0 días y sin recuperación puntual. En el último día hubo 3.766 respuestas 4XX y 222 respuestas no erróneas, pero la vista de logs no devolvió registros. No hay Object Storage administrado; R2 es externo.

## Target behavior

Existe un runbook con señales mínimas, alertas responsables, retención, inventario de copias y simulacro de restauración no productivo.

## Impact analysis

- Operaciones: affected.
- Logging: affected.
- Base de datos y storage: affected.
- Seguridad/privacidad: affected.
- Funcionalidad: reviewed-not-affected.

## Scope unknowns

- Configuración real de notificaciones.
- Mecanismo de snapshots y recuperación de R2 contratado.
- Personas responsables de incidentes.

## Scope confidence

Medio: el inventario está comprobado, pero RPO/RTO, responsables, alertas y costos requieren decisión humana.

## Acceptance Criteria

- Logs operativos incluyen tenant, request/job y resultado sin PII ni secretos.
- Alertas cubren despliegues, comandos, clusters, colas y gasto.
- Se documentan retención y destino de evidencia histórica.
- RPO y RTO quedan aprobados.
- Una restauración no productiva queda ejecutada y registrada.

## Out of scope

- Copiar secretos o datos productivos a la documentación.
- Ejecutar una restauración destructiva sobre producción.
- Incorporar monitoreo funcional de módulos fuera del piloto.

## Validation

- Provocar un fallo inocuo en preview y confirmar alerta/log.
- Restaurar una copia en un recurso aislado.
- Ejecutar smoke test de Usuarios, Roles y Grupos sobre la restauración.
- `kaddo guard` sin omisiones inesperadas.

## Definition of Done

- [x] Inventario operativo no sensible completado.
- [ ] Alertas mínimas comprobadas.
- [ ] RPO/RTO aprobados.
- [ ] Simulacro de restauración superado.
- [ ] Runbook de incidente versionado.

## Open questions

- ¿Cuánto dato y tiempo de indisponibilidad son aceptables?
- ¿Quién recibe y atiende alertas de producción?
- ¿Cuánto tiempo deben conservarse logs de auditoría?

## Evidencia actual

- Revisión de solo lectura: `knowledge/tech/discovery/infraestructura-multitenancy-laravel-cloud-2026-09-06.md`.
- No se habilitaron backups, alertas ni recursos porque cambian costo y operación.

## Suggested ownership

- `config/logging.php`
- `knowledge/delivery/**`
