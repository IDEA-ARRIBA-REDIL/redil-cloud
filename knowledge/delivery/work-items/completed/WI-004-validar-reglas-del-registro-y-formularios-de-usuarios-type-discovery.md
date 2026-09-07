---
type: discovery
id: WI-004
title: "Validar reglas del registro y formularios de Usuarios"
knowledge_level: K2
status: completed
phase: done
domains:
  - usuarios
  - roles-permisos
code:
  - routes/auth.php
  - routes/app.php
  - app/Http/Controllers/Auth/CustomVerifyEmailController.php
  - app/Notifications/MiVerificacionDeCorreo.php
  - app/Models/User.php
  - app/Http/Controllers/UserController.php
  - app/Livewire/FormulariosParaUsuarios/GestionarFormularios.php
  - knowledge/tech/domains/usuarios/current-state.md
  - knowledge/tech/discovery/usuarios-crear-editar-formularios-video-2026-09-06.md
  - knowledge/product/capabilities.md
capabilities:
  - "Creación y edición configurable de usuarios"
  - "Administración de formularios de usuario"
created_at: 2026-09-06
completed_at: 2026-09-06
source: business-validation
summary: "Registrar la validación funcional sobre activación por correo, obligatoriedad configurable de campos y eliminación de formularios usados"
---

# Validar reglas del registro y formularios de Usuarios

## Problem

Tres comportamientos del recorrido en video permanecían como preguntas: la acción realizada desde el correo, el origen de la obligatoriedad de campos y la posibilidad de eliminar formularios ya utilizados.

## Expected Value

El dominio diferencia claramente la regla funcional validada de su implementación técnica y elimina las preguntas ya resueltas.

## Definition of Done

- [x] El responsable funcional confirmó las tres reglas.
- [x] Se contrastó el nombre real de la columna y los filtros del listado con el código.
- [x] La evidencia, el dominio y las capacidades están actualizados.
- [x] Kaddo indexa los cambios y Guard termina correctamente.

## Out of scope

- Cambiar código funcional.
- Crear pruebas nuevas.
- Resolver las demás preguntas sobre tipos y permisos de formularios.

## Learning

### Implementado

- Se convirtieron tres aclaraciones del responsable del producto en reglas de negocio validadas.
- Se eliminaron de la evidencia las preguntas ya resueltas.
- Se actualizó el dominio y las capacidades recuperables por Kaddo.

### Validado

- El campo técnico es `users.email_verified_at`.
- La activación usa una URL temporal firmada, valida el hash, marca el correo, inicia sesión y redirige al dashboard.
- Las rutas protegidas y el listado operativo exigen verificación.
- La obligatoriedad se persiste en la asociación sección–campo y se traduce a reglas de validación.
- Los usuarios no conservan una relación directa con el formulario que los creó.
- Kaddo regeneró contexto y grafo; Guard terminó con código exitoso.

### Aprendido

- La explicación funcional permitió resolver tres límites que el recorrido visual no podía confirmar por sí solo.
- El lenguaje de negocio debe conservarse, pero los nombres técnicos se corrigen contra el código antes de documentarlos.

### Pendiente

- Confirmar la política de aprobación de cada tipo de formulario.
- Documentar la matriz de permisos de administración de formularios.
- Confirmar las diferencias funcionales entre los tipos de formulario.
