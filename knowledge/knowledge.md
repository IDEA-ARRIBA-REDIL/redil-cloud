---
type: current-state
updated_at: 2026-09-04
---

# REDIL Cloud — Conocimiento actual

> What is true about this product right now.

## Purpose

REDIL Cloud es una aplicación SaaS multi-tenant para la gestión eclesiástica. Este piloto de conocimiento cubre exclusivamente Usuarios, Roles/Permisos y Grupos.

## Architecture overview

Aplicación Laravel con Livewire, Eloquent y Spatie Permission. Los datos funcionales pertenecen a cada tenant. El orquestador selecciona primero un dominio documentado, carga solamente sus dependencias estrictas y después contrasta el contexto con el código y las pruebas.

## Key domains

- **Usuarios:** identidad, perfiles y tipos de usuario.
- **Roles y permisos:** capacidades técnicas; solo el rol activo autoriza.
- **Grupos:** integrantes, encargados, servidores, jerarquía ministerial y reportes.

Los demás dominios quedan fuera del piloto del orquestador y no deben cargarse automáticamente.

## Active constraints

- Usuario, persona y asistente representan la misma entidad `User`.
- Un usuario puede conservar varios roles, pero solo uno está activo.
- `TipoUsuario` expresa clasificación de negocio; `Role` expresa autorización.
- Grupos depende de Usuarios cuando intervienen integrantes, encargados o permisos.
- USR-03, USR-04, USR-05 y USR-06 están aplazados por decisión del piloto.
- Kaddo organiza conocimiento; el código y las pruebas siguen siendo la evidencia ejecutable.
