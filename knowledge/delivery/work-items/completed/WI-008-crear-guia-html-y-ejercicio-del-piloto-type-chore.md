---
type: chore
id: WI-008
title: Crear guía HTML y ejercicio del piloto
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
  - knowledge/delivery/pilot-validation-exercise.md
  - knowledge/tech/orchestrator.md
capabilities:
  - Flujo de entrega trazable
created_at: 2026-09-06T00:00:00.000Z
source: user-authorization
summary: >-
  Convertir la ruta versionada del piloto en una guía navegable y preparar el
  ejercicio que validará si un compañero puede completar un feature sin ayuda
ready_at: '2026-09-07'
started_at: 2026-09-06
completed_at: 2026-09-06
---

# Crear guía HTML y ejercicio del piloto

## Problem

El procedimiento completo existe en Markdown, pero el equipo todavía no dispone de una presentación navegable ni de una ficha uniforme para comprobarlo con un feature real.

## Expected Value

Un compañero puede abrir una sola guía, entender el alcance del piloto, copiar los mensajes recomendados y registrar evidencia desde la solicitud hasta el cierre del WI.

## Out of scope

- Elegir o implementar un feature funcional sin una necesidad concreta del responsable del producto.
- Declarar superada la prueba con compañeros antes de observarla.
- Publicar o desplegar la guía fuera del repositorio.
- Incorporar módulos distintos de Usuarios, Roles/Permisos y Grupos.

## Acceptance Criteria

- Existe una guía HTML autónoma, navegable, imprimible y adaptable a móvil.
- La guía declara que el Markdown sigue siendo la fuente de verdad.
- Existe una ficha para elegir y observar un feature real pequeño.
- Los puntos que requieren participación humana permanecen señalados como pendientes.
- Kaddo indexa el WI y Guard termina correctamente.

## Validation

- Revisión del contenido contra el playbook y el orquestador.
- Verificación visual del HTML en navegador.
- Comprobación de navegación, copia de mensajes, checklist e impresión.
- Regeneración de contexto y grafo; ejecución de Kaddo Guard.

## Definition of Done

- [x] Guía HTML creada y verificada.
- [x] Ficha de ejercicio creada.
- [x] Playbook y orquestador enlazan la guía sin duplicar autoridad.
- [x] Pendientes humanos identificados.
- [x] Kaddo actualizado y Guard revisado.

## Learning

### Implementado

- Se creó una guía HTML autónoma con navegación adaptable, impresión, mensajes copiables y checklist persistente en el navegador.
- Se creó la ficha de selección y observación del primer feature real.
- El playbook y el orquestador enlazan ambos documentos y conservan el Markdown como fuente de verdad.

### Validado

- El HTML superó el análisis estructural de Python `HTMLParser` y la validación de errores con HTML Tidy.
- Los enlaces locales apuntan a documentos existentes.
- Kaddo regeneró contexto y grafo; Guard terminó correctamente.

### Aprendido

- La presentación para el equipo puede ser interactiva sin crear una segunda fuente normativa.
- La prueba humana debe separar las dudas de la persona de las deficiencias de la guía y convertir cada ayuda en una mejora documentada.

### Pendiente

- El responsable del producto debe elegir el feature real.
- Un compañero debe ejecutar la ruta sin ayuda material y registrar sus dudas.
- La revisión visual final en el navegador del equipo se integra a ese ejercicio; la automatización local del navegador no pudo abrir direcciones `file:` por su política de seguridad.
