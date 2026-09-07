---
type: inventory
updated_at: 2026-09-04
source: kaddo-scan-with-curated-corrections
---

# Inventario técnico de REDIL Cloud

> `kaddo scan` detectó correctamente varias rutas, pero clasificó Vite como stack principal y no recorrió las migraciones tenant. Las correcciones siguientes se verificaron contra el repositorio y las dependencias instaladas.

## Contexto del proyecto

- Estado Kaddo: `pre-ai`.
- Estructura: un repositorio/monorepo.
- Idioma del conocimiento: español.
- Alcance del piloto: Usuarios, Roles/Permisos y Grupos.

## Stack verificado

| Capa | Tecnología |
|---|---|
| Lenguaje principal | PHP 8.2.24 |
| Framework | Laravel 12.53.0 |
| Interfaz reactiva | Livewire 3.7.11 |
| Autorización | Spatie Laravel Permission 6.24.1 |
| Frontend/build | JavaScript, Alpine.js y Vite |
| Paquetes backend | Composer |
| Paquetes frontend | pnpm 10.34.1 |
| Pruebas | PHPUnit 11 |

## Directorios relevantes para el piloto

- `app/Models/`
- `app/Http/Controllers/`
- `app/Livewire/Usuarios/`
- `app/Livewire/Grupos/`
- `resources/views/`
- `routes/`
- `database/migrations/tenant/`
- `database/seeders/`
- `tests/Feature/`

## Persistencia

- 11 migraciones base directamente en `database/migrations/`.
- 409 migraciones tenant en `database/migrations/tenant/`.
- Usuarios, roles, permisos y grupos se almacenan dentro del contexto multi-tenant de la aplicación.

## Contratos y entrada

- Las rutas web principales están en `routes/app.php` y archivos relacionados bajo `routes/`.
- La aplicación usa controladores Laravel y componentes Livewire como puntos de entrada.
- No se considera que una ruta visible o un botón reemplacen la autorización del servidor.

## Seguridad y privacidad del contexto

- El repositorio contiene nombres de variables de entorno sensibles, pero Kaddo y el orquestador no deben copiar sus valores al conocimiento generado.
- No se deben incorporar datos personales procedentes de la base de datos, seeders históricos o archivos de exportación.
- El rol activo es la única fuente de permisos de una sesión.
- Las consultas deben mantener el aislamiento por tenant y el alcance por sede/grupo cuando corresponda.

## Limitación conocida del detector

La salida automática `.kaddo/scan.json` conserva la detección original de Kaddo y puede reportar JavaScript/Vite como tecnología principal y solo las migraciones base. Este inventario curado tiene precedencia para el orquestador hasta que el detector soporte correctamente esta estructura Laravel.
