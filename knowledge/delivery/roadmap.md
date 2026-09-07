---
type: roadmap
updated_at: 2026-09-04
generated_by: human-reviewed-kaddo-pilot
template_version: 1
---

# REDIL Cloud — Roadmap del piloto Kaddo

> What we intend to build and why.

## Initiatives

### RM-001 — Orquestador Kaddo acotado

**Goal:** Conectar Kaddo con Codex y enrutar solicitudes solamente a Usuarios, Roles/Permisos y Grupos.
**Domain:** usuarios, roles-permisos, grupos
**Related capabilities:** rol activo, promoción de tipo/rol, integración Usuario–Grupo.
**Impact:** entregar a los agentes contexto preciso y reducir la carga de módulos no relacionados.
**Risk:** el detector automático representa incorrectamente el stack Laravel y podría inducir decisiones técnicas equivocadas.
**Suggested Knowledge Level:** K2
**Dependencies:** Kaddo CLI local, documentos de dominio y servidor `@kaddo/mcp`.
**Why now:** el piloto funcional ya está validado y constituye una frontera segura para probar el orquestador.
**Source signals:**
- `knowledge/tech/domains/usuarios/current-state.md`.
- `knowledge/tech/domains/grupos/current-state.md`.
- `tests/Feature/UserGroupPilotTest.php` con siete escenarios y 41 aserciones.

**Candidate Work Items:**
- WI-CANDIDATE-001 — Preparar y conectar el orquestador Kaddo
  - Type: chore
  - Suggested Knowledge Level: K2
  - Expected Value: contexto MCP verificable y enrutamiento limitado.
  - Notes: no modificar lógica funcional ni ampliar dominios.

## Assumptions

- Kaddo será una fuente de contexto de solo lectura durante la ejecución del agente.
- El inventario curado tiene precedencia sobre `scan.json` cuando discrepan.

**Resolved questions:**

- El servidor MCP quedó activo y fue validado desde Codex el 2026-09-04.

## Suggested execution order

1. Consolidar las capas Business, Product y Tech.
2. Generar contexto, explicación y grafo.
3. Instalar y registrar el servidor MCP.
4. Validar casos de enrutamiento y Guard.

**Status:** Completada el 2026-09-04.

## Not now

- Ampliar el orquestador a otros dominios.
- Resolver USR-03, USR-04, USR-05 o USR-06.

### RM-002 — Ruta repetible de desarrollo

**Goal:** Establecer cómo un feature se registra, implementa, prueba, documenta y cierra con Kaddo.
**Domain:** delivery, usuarios, roles-permisos, grupos
**Expected Value:** ningún desarrollo del piloto queda separado de su contexto, evidencia y aprendizaje.
**Status:** Completada el 2026-09-04 mediante `WI-002`.

### RM-003 — Alistamiento de producción multi-tenant

**Goal:** Comprobar y endurecer la operación de Usuarios, Roles/Permisos y Grupos sobre Laravel Cloud sin ampliar el piloto a nuevos módulos funcionales.
**Domain:** usuarios, roles-permisos, grupos
**Expected Value:** reducir el riesgo de acceso cruzado entre iglesias y establecer una ruta verificable para despliegues, procesos en segundo plano y recuperación.
**Status:** En curso desde el 2026-09-06.
**Dependencies:** Laravel Cloud, PostgreSQL por schemas, `stancl/tenancy`, R2, colas y scheduler.
**Why now:** la aplicación ya está desplegada y el siguiente riesgo material no es agregar otra pantalla, sino asegurar que el modelo multi-tenant se conserva fuera de la petición HTTP normal.

**Candidate Work Items:**

- WI-011 / INFRA-01 — Auditar aislamiento multi-tenant del piloto.
- WI-012 / INFRA-02 — Definir despliegues y migraciones seguras.
- WI-013 / INFRA-03 — Asegurar colas y tareas programadas por tenant.
- WI-014 / INFRA-04 — Establecer observabilidad, copias y recuperación.

**Scope rule:** estos trabajos son transversales al piloto vigente; no habilitan Actividades, Escuelas, Finanzas ni otros dominios en el enrutador.
