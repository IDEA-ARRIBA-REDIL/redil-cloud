---
type: product
project_state: pre-ai
generated_by: kaddo-bootstrap
template_version: 1
---

> Idioma del proyecto: **español**. Escribe este conocimiento en español. Mantén en inglés el código, los nombres de archivo, los comandos y las claves de configuración.

# Contexto de producto del piloto

## Existing product behavior

La aplicación permite crear y administrar usuarios, asignarles tipos y múltiples roles, seleccionar un rol activo, comprobar permisos, vincularlos a grupos como integrantes/encargados/servidores y conservar reportes históricos de asistencia.

## Main flows

1. El usuario inicia sesión y opera con los permisos de su rol activo.
2. Un operador autorizado vincula una persona como integrante de un grupo.
3. El tipo de grupo puede promover automáticamente el tipo de usuario y su rol dependiente.
4. Un operador autorizado asigna un encargado sin duplicar la relación ni el hito.
5. La desvinculación elimina la membresía vigente y conserva su bitácora.
6. Los reportes de grupo conservan la asistencia independientemente de la membresía actual.

## Inferred or uncertain behavior

- La cobertura integral de rutas de reportes, autoasistencia y evidencias no forma parte de esta fase.
- El inventario automático de Kaddo no reconoce correctamente el stack Laravel ni las migraciones tenant; debe usarse el inventario curado.

## Open questions

- [resolved] El orquestador permanece limitado a Usuarios, Roles/Permisos y Grupos durante este piloto.
  - note: Cualquier ampliación requerirá una nueva autorización expresa.
