---
type: discovery
id: WI-006
title: Documentar vistas operativas de Grupos
knowledge_level: K2
status: completed
phase: done
initiative: RM-003
domains:
  - grupos
  - usuarios
  - roles-permisos
code:
  - app/Http/Controllers/GrupoController.php
  - app/Livewire/Usuarios/UsuariosParaBusqueda.php
  - app/Livewire/Usuarios/MapaGeoAsignacion.php
  - app/Models/Grupo.php
  - app/Models/User.php
  - resources/views/contenido/paginas/grupos/nuevo.blade.php
  - resources/views/contenido/paginas/grupos/modificar.blade.php
  - resources/views/contenido/paginas/grupos/gestionar-encargados.blade.php
  - resources/views/contenido/paginas/grupos/gestionar-integrantes.blade.php
  - resources/views/contenido/paginas/grupos/georreferencia.blade.php
  - knowledge/tech/domains/grupos/current-state.md
  - knowledge/tech/discovery/grupos-vistas-operativas-imagenes-2026-09-06.md
  - knowledge/product/capabilities.md
capabilities:
  - Administración operativa de grupos
  - Integración Usuario–Grupo
created_at: 2026-09-06T00:00:00.000Z
source: user-images
summary: >-
  Incorporar como evidencia documental las vistas de creación, edición,
  encargados, integrantes y georreferencia de Grupos
ready_at: '2026-09-07'
completed_at: 2026-09-06
---

# Documentar vistas operativas de Grupos

## Problem

El recorrido en video demuestra el módulo de manera general, pero las vistas principales de creación y administración no estaban descritas campo por campo ni como una navegación operativa posterior a la creación.

## Expected Value

El equipo y Kaddo pueden recuperar la secuencia visual precisa para crear y completar un grupo, comprender cómo intervienen Usuarios y permisos, y diferenciar datos principales, liderazgo, membresía y ubicación.

## Evidence

- `NUEVO GRUPO.png`.
- `EDITAR GRUPO.png`.
- `ASIGNAR ENCARGADOS.png`.
- `ASIGNAR ASISTENTES.png`.
- `GEOREFERENCIA.png`.
- Contraste con vistas, controlador, modelos y componentes Livewire vigentes.

## Out of scope

- Modificar código o interfaz.
- Copiar al repositorio imágenes con datos de una iglesia o personas de prueba.
- Convertir valores visibles en las capturas en reglas generales.
- Resolver los riesgos técnicos ya documentados para georreferencia y autorización.

## Acceptance Criteria

- Las cinco capturas quedan inventariadas y descritas de forma anonimizada.
- La documentación distingue la creación inicial de la administración posterior del grupo.
- Los campos visibles y configurables se contrastan con el código.
- La relación entre encargado, integrante y usuario queda explícita.
- La georreferencia se documenta como asignación automática al pulsar el mapa.

## Validation

- Inspección visual de las cinco imágenes en resolución original.
- Contraste con `GrupoController`, las cinco vistas correspondientes, `UsuariosParaBusqueda`, `MapaGeoAsignacion`, `Grupo` y `User`.
- Regeneración del contexto y grafo de Kaddo.
- Ejecución de Kaddo Guard en modo CI.

## Definition of Done

- [x] La nota de evidencia visual está creada.
- [x] El estado actual y las capacidades están actualizados.
- [x] La privacidad y el carácter ilustrativo de los datos están documentados.
- [x] Kaddo indexa el WI y Guard termina correctamente.

## Learning

### Implementado

- Se creó una nota anonimizada para las cinco vistas y se vinculó con la evidencia del video.
- Se amplió el estado actual y la capacidad de administración operativa de Grupos.
- No se modificó código funcional ni se copiaron las imágenes al repositorio.

### Validado

- Las cinco imágenes se inspeccionaron en resolución original.
- Campos, navegación, permisos y persistencia se contrastaron con las vistas y el código vigente.
- Kaddo regeneró contexto y grafo; Guard terminó con código exitoso.

### Aprendido

- La creación inicial antecede a las asignaciones porque encargados, integrantes y coordenadas requieren un grupo persistido.
- Las cuatro áreas posteriores son pantallas separadas unidas por navegación condicionada por permisos.
- “Encargado” e “integrante” son los términos funcionales vigentes; “líder” y “asistente” aparecen como clasificación o vocabulario histórico.

### Pendiente

- Mantener los riesgos ya registrados sobre autorización directa e inicialización geográfica para un WI de implementación futuro; esta evidencia no solicita cambios.
