---
type: business
project_state: pre-ai
generated_by: kaddo-bootstrap
template_version: 1
---

> Idioma del proyecto: **español**. Escribe este conocimiento en español. Mantén en inglés el código, los nombres de archivo, los comandos y las claves de configuración.

# Contexto de negocio del piloto

## What this product appears to support

REDIL Cloud gestiona información eclesiástica por tenant. Este piloto cubre la identidad de las personas, su clasificación, las capacidades que reciben mediante roles y su participación o liderazgo en grupos.

## Users or roles

- Usuario/persona/asistente: una misma entidad funcional persistida en `users`.
- Encargado o líder de grupo: usuario relacionado mediante `encargados_grupo`.
- Integrante: usuario vinculado establemente mediante `integrantes_grupo`.
- Servidor: usuario que presta una función mediante `servidores_grupo`.
- Tipo de usuario: clasificación de negocio y jerarquía.
- Rol: conjunto técnico de permisos. Un usuario puede tener varios, pero solo uno activo.

## Existing business rules

- Solo el rol activo aporta permisos a la sesión.
- `TipoUsuario` y `Role` no son equivalentes.
- Vincular un integrante o encargado no debe generar duplicados.
- La asistencia de un reporte es histórica y no reemplaza la membresía vigente.
- La promoción automática puede sustituir el rol dependiente, conserva roles independientes y no degrada por puntaje.
- El piloto funcional Usuarios–Grupos fue confirmado como operativo por el responsable del producto.
- USR-03, USR-04, USR-05 y USR-06 se aplazan expresamente.

## Open questions

- [deferred] Definir en una fase posterior quién resolverá y aprobará los hallazgos de Usuarios aplazados.
  - note: USR-03, USR-04, USR-05 y USR-06 quedan fuera del piloto por decisión del responsable del producto.
- [deferred] Decidir si el orquestador se ampliará a nuevos dominios después del piloto.
  - note: No se habilitan dominios distintos de Usuarios, Roles/Permisos y Grupos.
