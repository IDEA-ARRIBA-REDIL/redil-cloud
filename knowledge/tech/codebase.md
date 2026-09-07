---
type: codebase
project_state: pre-ai
generated_by: kaddo-bootstrap
template_version: 1
---

> Idioma del proyecto: **español**. Escribe este conocimiento en español. Mantén en inglés el código, los nombres de archivo, los comandos y las claves de configuración.

# Mapa del código del piloto

## Repository structure

- `app/Models`: entidades y relaciones Eloquent.
- `app/Http/Controllers`: entradas HTTP y coordinación de casos de uso.
- `app/Livewire`: componentes reactivos.
- `routes`: contratos de navegación y middleware.
- `database/migrations/tenant`: esquema funcional por tenant.
- `database/seeders`: catálogos, roles y permisos iniciales.
- `tests/Feature`: pruebas de integración.
- `knowledge`: conocimiento curado para Kaddo y el orquestador.

## Entry points

- `routes/app.php`.
- `app/Http/Controllers/UserController.php`.
- `app/Http/Controllers/GrupoController.php`.
- `app/Livewire/Usuarios/` y `app/Livewire/Grupos/`.

## Important modules

- Usuarios: `User`, `TipoUsuario`.
- Roles/Permisos: `Role`, Spatie Permission, `model_has_roles`.
- Grupos: `Grupo`, `TipoGrupo`, integrantes, encargados y reportes.

## How to run

- Desarrollo Laravel: `composer run dev` o el procedimiento local ya configurado por el equipo.
- No ejecutar comandos de producción desde el orquestador sin autorización.

## How to test

- Piloto: `APP_CONFIG_CACHE=/tmp/redil-user-pilot-config.php vendor/bin/phpunit --colors=never tests/Feature/UserGroupPilotTest.php`.
- Formato PHP modificado: `vendor/bin/pint --dirty --format agent`.

## Open questions

- [deferred] Actualizar por separado las instrucciones que todavía declaran Laravel 11/PHPUnit 10; la instalación observada usa Laravel 12/PHPUnit 11.
  - note: No pertenece al alcance funcional Usuarios–Roles–Grupos ni bloquea la configuración Kaddo.
