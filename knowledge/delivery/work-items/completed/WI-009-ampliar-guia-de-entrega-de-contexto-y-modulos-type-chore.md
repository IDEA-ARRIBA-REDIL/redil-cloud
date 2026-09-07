---
type: chore
id: WI-009
title: Ampliar guía de entrega de contexto y módulos
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
capabilities:
  - Flujo de entrega trazable
created_at: 2026-09-06T00:00:00.000Z
source: user-feedback
summary: >-
  Explicar a un compañero sin contexto previo qué documentos, código y evidencia
  debe aportar y cómo incorporar módulos todavía no habilitados
ready_at: '2026-09-07'
started_at: 2026-09-06
completed_at: 2026-09-06
---

# Ampliar guía de entrega de contexto y módulos

## Problem

La guía describe el ciclo de desarrollo, pero presupone demasiado conocimiento de la conversación que originó el piloto. No explica suficientemente el papel de los archivos `agenteXxx.md`, qué archivos técnicos sirven como punto de entrada ni cómo adjuntar videos y capturas para incorporar otro módulo.

## Expected Value

Un compañero que conoce REDIL pero no leyó la conversación puede iniciar una tarea nueva, entregar un paquete mínimo de contexto y distinguir entre documentar un módulo existente, incorporar un dominio al orquestador e implementar funcionalidad nueva.

## Out of scope

- Incorporar ahora un dominio funcional nuevo.
- Modificar lógica Laravel.
- Convertir documentos históricos en fuente canónica sin contraste.

## Acceptance Criteria

- La guía explica qué es y cómo se usa cada clase de archivo documental y técnico.
- Incluye recomendaciones concretas para video, audio, capturas y datos sensibles.
- Distingue módulo existente no habilitado de módulo o feature aún no implementado.
- Presenta el aprendizaje del piloto como ejemplo repetible.
- Incluye un mensaje completo para comenzar una conversación nueva.

## Validation

- Contraste con el playbook, el enrutador y las evidencias de Usuarios y Grupos.
- Validación estructural del HTML, enlaces y contenido.
- Regeneración de Kaddo y ejecución de Guard.

## Definition of Done

- [x] Playbook ampliado como fuente normativa.
- [x] HTML ampliado para compañeros sin contexto previo.
- [x] Ficha de ejercicio alineada con el paquete de entrada.
- [x] Kaddo actualizado y Guard revisado.

## Learning

### Implementado

- La guía explica cómo iniciar una conversación nueva sin depender del historial del chat.
- Se documentó el papel de `AGENTS.md`, `agenteXxx.md`, arquitectura, documentación previa, modelos, controladores, Livewire, vistas, rutas, migraciones, seeders y pruebas.
- Se añadieron pautas prácticas para videos, voz, capturas, nombres de archivos y privacidad.
- Se distinguió entre dominio habilitado, módulo existente sin contexto y funcionalidad aún no implementada.

### Validado

- El HTML pasó HTML Tidy, análisis estructural, control de identificadores únicos y verificación de destinos de navegación.
- El contenido se contrastó con el playbook, el enrutador y las evidencias del piloto de Usuarios y Grupos.
- Kaddo regeneró contexto y grafo; Guard terminó correctamente.

### Aprendido

- La mayor brecha de la primera guía era pedagógica: describía el proceso, pero no enseñaba cómo construir el paquete de contexto inicial.
- `agenteXxx.md` debe presentarse como punto de entrada sujeto a contraste y no como fuente suficiente por sí sola.
- La combinación de interfaz, explicación funcional, código y pruebas ofrece una evidencia más completa que cualquiera de esas fuentes aislada.

### Pendiente

- Comprobar con un compañero si puede seguir la guía sin conocer esta conversación y ajustar las expresiones que todavía generen dudas.
