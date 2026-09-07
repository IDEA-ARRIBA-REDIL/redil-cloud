---
type: chore
id: WI-001
title: "Preparar y conectar el orquestador Kaddo"
knowledge_level: K2
status: completed
phase: done
initiative: "RM-001"
domains:
  - usuarios
  - roles-permisos
  - grupos
code:
  - .kaddo/config.yml
  - .codex/config.toml
  - knowledge/**
capabilities:
  - "Domain: Usuarios y Roles/Permisos"
  - "Domain: Grupos"
created_at: 2026-09-04
completed_at: 2026-09-04
source: roadmap
source_id: WI-CANDIDATE-001
source_initiative: RM-001
source_roadmap_initiative: RM-001
source_work_item_candidate: WI-CANDIDATE-001
source_title: "Preparar y conectar el orquestador Kaddo"
source_context: "Materialized from roadmap candidate WI-CANDIDATE-001."
source_initiative_title: "Candidate Work Items"
summary: "El orquestador no dispone de una base Kaddo completa ni de una conexion MCP verificable, y el escaneo automatico representa incorrectamente Laravel y las migraciones tenant"
---

# Preparar y conectar el orquestador Kaddo

> Type: chore · Level: K2

## Source

- Source: roadmap
- Roadmap Initiative: RM-001 — Orquestador Kaddo acotado
- Work Item Candidate: WI-CANDIDATE-001

## Problem

El orquestador no dispone de una base Kaddo completa ni de una conexión MCP verificable, y el escaneo automático representa incorrectamente Laravel y las migraciones tenant.

## Expected Value

Codex puede consultar por MCP un contexto Kaddo curado y enrutar solo solicitudes de Usuarios, Roles o Grupos, rechazando los demás módulos.

## Context From Roadmap

Este elemento se materializó desde `WI-CANDIDATE-001` de la iniciativa `RM-001`.



**Source signals:** _Not provided in roadmap._

## Out of scope

- Cambiar lógica funcional de Laravel.
- Resolver USR-03, USR-04, USR-05 o USR-06.
- Incorporar Actividades u otros dominios al orquestador.

## Validation

- `kaddo status`, `context`, `understand`, `explain`, `graph export` y `guard` terminan correctamente.
- Codex reconoce el servidor `kaddo` en la configuración local del proyecto.
- El paquete de contexto incluye la propiedad de Usuarios y Grupos.
- Las reglas de enrutamiento rechazan dominios fuera del piloto.

## Definition of Done

- [x] El problema y el resultado esperado están definidos.
- [x] La base Business, Product, Tech y Delivery existe.
- [x] Los dominios y sus rutas de propiedad están declarados.
- [x] El servidor MCP está disponible después de reiniciar Codex.
- [x] Los casos de enrutamiento se validan desde una sesión con MCP activo.

## Learning

### Implementado

- Kaddo quedó configurado como servidor MCP local del proyecto.
- El contexto, roadmap, Work Items, preguntas y agentes son consultables desde Codex.
- El enrutamiento quedó limitado a Usuarios, Roles/Permisos y Grupos.

### Validado

- Una solicitud sobre rol activo selecciona Usuarios y Roles/Permisos.
- Una solicitud sobre encargados o integrantes selecciona Grupos y Usuarios/Roles.
- Una solicitud de Actividades queda fuera del piloto y requiere autorización para ampliar el alcance.
- Kaddo reportó propiedad completa, cero contexto faltante y cero preguntas bloqueantes.

### Aprendido

- El escaneo automático de Kaddo no identifica con precisión el stack Laravel ni las migraciones tenant; el inventario curado tiene precedencia.
- La documentación del dominio debe mantenerse junto con cada cambio para evitar que el contexto del orquestador quede desactualizado.

### Pendiente fuera de este Work Item

- Definir un procedimiento repetible para desarrollar y cerrar futuros features.
- Convertir posteriormente ese procedimiento en una guía HTML para el equipo.
- Mantener aplazados USR-03, USR-04, USR-05 y USR-06.
