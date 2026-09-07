---
type: spike
id: WI-011
title: INFRA-01 — Auditar aislamiento multi-tenant del piloto
knowledge_level: K2
status: completed
phase: done
initiative: RM-003
domains:
  - usuarios
  - roles-permisos
  - grupos
code:
  - config/tenancy.php
  - config/database.php
  - config/cache.php
  - config/filesystems.php
  - config/queue.php
  - routes/tenant.php
  - routes/console.php
  - app/Jobs/**
  - app/Console/Commands/**
  - tests/Feature/UserGroupPilotTest.php
capabilities:
  - Operación multi-tenant del piloto
created_at: 2026-09-06
started_at: 2026-09-06
completed_at: 2026-09-06
source: user-authorization
summary: Auditar el aislamiento del piloto desplegado en Laravel Cloud sin cambiar lógica funcional
---

# INFRA-01 — Auditar aislamiento multi-tenant del piloto

## Actor and outcome

El equipo técnico obtiene un mapa comprobable de los controles y riesgos multi-tenant antes de desplegar nuevos cambios.

## Problem

El piloto valida reglas funcionales sobre una sola conexión, pero no demuestra todavía aislamiento entre iglesias en todas las superficies de producción.

## Expected Value

Separar controles existentes, brechas comprobadas y verificaciones pendientes del dashboard para planear correcciones pequeñas y trazables.

## Out of scope

- Modificar lógica funcional.
- Resolver los hallazgos durante la auditoría.
- Habilitar módulos distintos de Usuarios, Roles/Permisos y Grupos.
- Registrar secretos o datos personales.

## Acceptance Criteria

- Se revisan HTTP, PostgreSQL, caché, almacenamiento, colas, scheduler, preview y operación.
- Cada hallazgo incluye evidencia, riesgo y tratamiento propuesto.
- Se distingue lo comprobable en repositorio de lo que requiere el dashboard de Laravel Cloud.
- El baseline funcional existente se ejecuta y registra.
- Los siguientes trabajos quedan separados por resultado operativo.

## Validation

- `vendor/bin/phpunit tests/Feature/UserGroupPilotTest.php --colors=never`.
- `php artisan schedule:list` usando configuración local no cacheada.
- `php artisan route:list --except-vendor --path=admin`.
- Contraste con `config/tenancy.php`, filesystem, caché, colas, comandos y jobs.

## Definition of Done

- [x] Superficies y límites documentados.
- [x] Hallazgos priorizados con evidencia.
- [x] Baseline ejecutado: 7 pruebas y 41 aserciones.
- [x] Verificaciones manuales de Cloud separadas.
- [x] WI-012, WI-013 y WI-014 definidos.

## Learning

### Implementado

- Se creó la auditoría `DISC-INFRA-MULTITENANCY-2026-09-06`.
- Se añadió la iniciativa RM-003 sin ampliar los dominios funcionales del piloto.

### Validado

- La base de datos, el filesystem y las colas tienen bootstrappers tenant-aware activos.
- Las pruebas funcionales existentes continúan en verde.
- No existe todavía una prueba automatizada con dos tenants.
- Caché, R2 y scheduler requieren tratamiento explícito antes de considerarlos aislados.

### Aprendido

- El aislamiento HTTP no demuestra aislamiento de procesos asincrónicos ni recursos compartidos.
- En Laravel Cloud, caché distribuida y locks son parte de la corrección funcional cuando hay más de una réplica.

### Pendiente

- Ejecutar los tres WI siguientes y completar la revisión no sensible del dashboard.
