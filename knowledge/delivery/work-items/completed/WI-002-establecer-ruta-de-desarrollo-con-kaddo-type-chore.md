---
type: chore
id: WI-002
title: "Establecer la ruta de desarrollo con Kaddo"
knowledge_level: K2
status: completed
phase: done
initiative: "RM-002"
domains:
  - usuarios
  - roles-permisos
  - grupos
code:
  - knowledge/delivery/development-playbook.md
  - knowledge/tech/orchestrator.md
  - knowledge/tech/domains/README.md
capabilities:
  - "Flujo de entrega trazable"
created_at: 2026-09-04
completed_at: 2026-09-04
source: user-request
summary: "El equipo necesita una ruta repetible para que cada feature comience con contexto suficiente y termine probado, documentado y anexado a Kaddo"
---

# Establecer la ruta de desarrollo con Kaddo

## Problem

No existe todavía una guía única que conecte la solicitud de un feature con el Work Item, el contexto del dominio, la implementación, las pruebas, Kaddo Guard, la actualización documental y el cierre con aprendizaje.

## Expected Value

Un integrante del equipo puede desarrollar un feature sin dejar decisiones, cambios funcionales o conocimiento fuera de Kaddo y del orquestador.

## Out of scope

- Crear la guía HTML para distribución.
- Incorporar nuevos módulos al piloto.
- Modificar lógica funcional de Laravel.

## Validation

- La guía cubre el ciclo completo desde solicitud hasta cierre.
- Incluye una ruta específica para features de módulos existentes.
- Incluye una ruta de incorporación para módulos nuevos basados en video y documentación.
- Define evidencias, responsables y criterios de cierre.

## Definition of Done

- [x] El problema, resultado esperado y alcance están definidos.
- [x] El playbook de desarrollo está escrito y enlazado al orquestador.
- [x] La ruta de incorporación de nuevos módulos está definida.
- [x] Kaddo puede indexar el nuevo conocimiento.

## Learning

### Implementado

- Se documentó el ciclo completo desde la solicitud hasta el cierre y revisión del cambio.
- Se incluyó una ruta para features de módulos habilitados y otra para incorporar módulos nuevos desde videos y documentos.
- El orquestador enlaza ahora el playbook como procedimiento de entrega.

### Validado

- Kaddo recuperó `WI-002` y regeneró contexto y grafo sin errores.
- Kaddo Guard terminó correctamente; los avisos informativos corresponden a cambios preexistentes del árbol de trabajo.

### Aprendido

- El WI debe ser el hilo conductor que relaciona solicitud, contexto, código, validación y aprendizaje.
- Los videos deben convertirse en conocimiento textual revisable; no deben ser la única fuente operativa del agente.
- La futura guía HTML debe ser una presentación derivada y no otra fuente de verdad independiente.

### Pendiente fuera de este Work Item

- Validar el lenguaje de la guía con los compañeros.
- Crear la versión HTML navegable cuando se autorice.
- Incorporar nuevos módulos uno por uno mediante WIs de descubrimiento.
