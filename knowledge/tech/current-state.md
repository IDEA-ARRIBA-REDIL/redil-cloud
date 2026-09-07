---
type: current-state
project_state: pre-ai
generated_by: kaddo-bootstrap
template_version: 1
---

> Idioma del proyecto: **español**. Escribe este conocimiento en español. Mantén en inglés el código, los nombres de archivo, los comandos y las claves de configuración.

# Estado técnico actual

## What exists today

- Piloto Usuarios–Grupos funcionalmente aprobado.
- Prueba automatizada con siete escenarios y 41 aserciones.
- Documentos de dominio para Usuarios y Grupos.
- Enrutador estricto para Usuarios, Roles/Permisos y Grupos.
- Kaddo inicializado en estado `pre-ai`.

## Observed technical signals

- Stack verificado: PHP 8.2.24, Laravel 12.53.0, Livewire 3.7.11 y Spatie Permission 6.24.1.
- Frontend con JavaScript/Vite y paquetes gestionados mediante pnpm.
- 11 migraciones base y 409 migraciones tenant.
- `kaddo scan` detecta solo JavaScript/Vite y las migraciones base; `knowledge/inventory.md` contiene la corrección curada.

## Inferred architecture

Aplicación Laravel modular dentro de un repositorio amplio. El orquestador usa Kaddo como capa de recuperación de conocimiento y luego abre selectivamente código y pruebas. Kaddo no sustituye la ejecución de Laravel ni la autorización del servidor.

## Known constraints

- Alcance limitado a Usuarios, Roles/Permisos y Grupos.
- Multi-tenancy y alcance por sede/grupo deben preservarse.
- Solo el rol activo aporta permisos.
- No cargar secretos, valores de entorno ni datos personales en los artefactos Kaddo.
- USR-03, USR-04, USR-05 y USR-06 permanecen aplazados.
- La aplicación está desplegada en Laravel Cloud; el alistamiento operativo se gestiona como una iniciativa transversal del piloto, sin incorporar nuevos dominios funcionales.
- La prueba `UserGroupPilotTest` valida reglas del piloto sobre una sola conexión SQLite y no demuestra todavía aislamiento entre dos tenants PostgreSQL.

## Risks of interpretation

- Confundir `TipoUsuario` con `Role`.
- Interpretar asistencia histórica como membresía vigente.
- Tomar `.kaddo/scan.json` como autoridad superior al inventario curado.
- Enrutar solicitudes a Actividades u otros dominios fuera del piloto.
- Confundir el aislamiento de infraestructura proporcionado por Laravel Cloud con el aislamiento lógico que REDIL debe aplicar a base de datos, caché, colas, scheduler y archivos.

## Open questions

- [open] Confirmar la instalación local de `@kaddo/mcp` y reiniciar el cliente Codex para activar el servidor.
- [open] Decidir si `.kaddo` generado debe conservarse localmente o versionarse parcialmente.
