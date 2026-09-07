---
type: chore
id: WI-010
title: Convertir ciclo Kaddo en guía ejecutable
knowledge_level: K2
status: completed
phase: done
initiative: RM-002
domains:
  - usuarios
  - roles-permisos
  - grupos
code:
  - knowledge/delivery/development-playbook.md
  - knowledge/delivery/team-development-guide.html
capabilities:
  - Flujo de entrega trazable
created_at: 2026-09-06T00:00:00.000Z
source: user-feedback
summary: >-
  Explicar Kaddo y Work Items desde cero y entregar un prompt, una acción y un
  resultado verificable para cada etapa del ciclo
ready_at: '2026-09-07'
started_at: 2026-09-06
completed_at: 2026-09-06
---

# Convertir ciclo Kaddo en guía ejecutable

## Problem

El punto 04 describe nueve etapas, pero un compañero sin experiencia en Kaddo no sabe cómo crear un Work Item ni qué debe pedirle a Codex en cada momento.

## Expected Value

El compañero puede ejecutar cada etapa copiando un prompt, reconocer el resultado esperado y continuar sin conocer previamente los comandos o la estructura interna de Kaddo.

## Out of scope

- Enseñar internamente la implementación de Kaddo.
- Ejecutar un feature funcional.
- Exigir que el compañero opere manualmente la CLI cuando Codex puede hacerlo.

## Acceptance Criteria

- La guía define Kaddo, WI, estados, contexto, Guard y Learning en lenguaje sencillo.
- Cada uno de los nueve pasos explica acción humana, acción de Codex y resultado esperado.
- Cada paso incluye un prompt específico listo para copiar.
- Se aclara que Kaddo Guard no reemplaza las pruebas.

## Validation

- Contraste con el ciclo vigente y los WIs completados del piloto.
- Validación estructural del HTML y navegación.
- Regeneración de Kaddo y ejecución de Guard.

## Definition of Done

- [x] Glosario operativo añadido.
- [x] Nueve pasos convertidos en instrucciones ejecutables.
- [x] Prompts y resultados esperados incluidos.
- [x] HTML validado.
- [x] Kaddo actualizado y Guard revisado.

## Learning

### Implementado

- El playbook define Kaddo, Work Item, estados, contexto, Guard y Learning para personas sin experiencia previa.
- Cada etapa del HTML separa acción del compañero, acción de Codex/Kaddo, prompt copiable y resultado requerido.
- Se aclaró expresamente que Codex puede operar Kaddo y que el compañero no necesita memorizar comandos.

### Validado

- El HTML pasó HTML Tidy y análisis estructural.
- Se comprobaron identificadores, destinos de navegación y los nueve prompts con sus botones de copia.
- Kaddo regeneró contexto y grafo; Guard terminó correctamente.

### Aprendido

- Una guía de equipo no debe limitarse a nombrar artefactos: debe indicar quién actúa, qué debe pedir y qué evidencia permite avanzar.
- Kaddo Guard debe explicarse como trazabilidad, no como prueba funcional.

### Pendiente

- Observar si un compañero puede ejecutar los nueve prompts sin explicación verbal y ajustar el lenguaje con sus dudas reales.
