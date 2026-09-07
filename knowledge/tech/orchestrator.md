---
type: architecture
id: ARCH-ORQUESTADOR-KADDO
title: Orquestador Kaddo para Usuarios, Roles y Grupos
status: pilot
domains:
  - usuarios
  - roles-permisos
  - grupos
updated_at: 2026-09-06
---

# Orquestador Kaddo del piloto

## Objetivo

Seleccionar el contexto mínimo necesario para solicitudes de Usuarios, Roles/Permisos y Grupos. Kaddo entrega conocimiento estructurado; el agente contrasta ese conocimiento con el código y las pruebas antes de responder o implementar.

## Enrutamiento

| Intención | Dominio principal | Dependencia mínima |
|---|---|---|
| Perfil, identidad o tipo de usuario | Usuarios | Roles si cambia autorización |
| Rol activo, rol o permiso | Usuarios / Roles | Dominio funcional autorizado |
| Integrante, encargado, servidor o exclusión | Grupos | Usuarios / Roles |
| Cobertura o jerarquía ministerial | Grupos | Usuarios / Roles |
| Cualquier otro módulo | Fuera del piloto | Detener y solicitar autorización |

## Secuencia obligatoria

1. Identificar verbo, objeto y alcance de la solicitud.
2. Aplicar la puerta de alcance del piloto.
3. Cargar un dominio principal y solo sus dependencias estrictas.
4. Abrir los archivos de código y pruebas del flujo concreto.
5. Resolver diferencias usando: pruebas, código, negocio validado y documentación, en ese orden.
6. Implementar solo cuando exista autorización.
7. Ejecutar pruebas específicas y `kaddo guard`.
8. Actualizar el conocimiento si el cambio altera reglas, arquitectura o propiedad.

El ciclo detallado de creación, implementación, validación y cierre de Work Items está definido en `knowledge/delivery/development-playbook.md`.

La guía navegable para el equipo está en `knowledge/delivery/team-development-guide.html`; es una presentación derivada y no reemplaza al playbook. El ejercicio de validación humana se registra en `knowledge/delivery/pilot-validation-exercise.md`.

## Decisiones congeladas

- El piloto funcional Usuarios–Grupos se considera aprobado por el responsable del producto.
- Solo el rol activo aporta permisos.
- `TipoUsuario` y `Role` son conceptos distintos.
- USR-03, USR-04, USR-05 y USR-06 están aplazados.
- No se enrutan solicitudes de Actividades ni de otros módulos durante este piloto.

## Criterios de aceptación

- Una solicitud de rol activo carga Usuarios/Permisos.
- Una solicitud de encargado carga Grupos y Usuarios/Permisos.
- Una solicitud de Actividades se rechaza como fuera de alcance.
- El contexto no contiene valores de secretos ni datos personales de usuarios.
- Guard relaciona cambios en los archivos declarados con su documento de dominio.
