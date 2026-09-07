# Revisión técnica para Usuarios, Roles y Permisos

**Fecha del análisis:** 4 de septiembre de 2026

**Estado:** USR-01 y USR-02 corregidos y validados; USR-03 y USR-04 aplazados por decisión del piloto; demás hallazgos pendientes

**Destinatario:** Equipo de desarrollo de REDIL Cloud

**Documento funcional relacionado:** `knowledge/tech/domains/usuarios/current-state.md`

> Este documento no modifica la aplicación ni afirma que todos los hallazgos sean vulnerabilidades explotables. Su objetivo es entregar al desarrollador una lista reproducible para confirmar, corregir o justificar el comportamiento actual.

## 1. Reglas de negocio que deben preservarse

1. Usuario, persona y asistente representan la misma entidad `User` en REDIL Cloud.
2. Un usuario puede tener varios roles asignados, pero solo uno activo.
3. Todos los permisos y restricciones de la sesión deben proceder exclusivamente del rol activo.
4. Una cuenta ordinaria nueva requiere correo real y único, contraseña y verificaciones de acceso.
5. Los correos técnicos pertenecen únicamente a migraciones históricas o al procedimiento especial de menores.
6. Un menor nuevo debe ser registrado por su padre, madre o acudiente; no se deben almacenar datos que permitan contactarlo directamente.
7. Los módulos relacionados, como Grupos, deben reutilizar estas reglas y no crear usuarios por vías alternativas.

## 2. Resumen y prioridad

| ID | Prioridad | Hallazgo | Tipo |
|---|---|---|---|
| USR-01 | Alta | Las comprobaciones directas sobre `User` pueden considerar todos los roles asignados y no únicamente el activo. | Autorización |
| USR-02 | Alta | El cambio de rol activo actualiza `model_has_roles` por `model_id` sin filtrar `model_type`. | Integridad / autorización |
| USR-03 | Alta | Las rutas administrativas para cambiar contraseñas no muestran autorización sobre el usuario objetivo. | Autorización / cuentas |
| USR-04 | Alta | Se generan contraseñas iniciales predecibles usando identificación o `123456`. | Credenciales |
| USR-05 | Alta | Los formularios dinámicos pueden declarar correo o contraseña opcionales aunque negocio los exige para cuentas nuevas. | Consistencia funcional |
| USR-06 | Alta | La regla legal de menores y responsable no aparece como una invariante central de creación. | Privacidad / cumplimiento |
| USR-07 | Media | La eliminación de una relación familiar recibe directamente el identificador del pivote sin autorización visible. | Autorización horizontal |
| USR-08 | Media | No se localizaron pruebas dedicadas por nombre a roles activos, credenciales, menores o relaciones familiares. | Cobertura |

---

## 3. Hallazgos detallados

## USR-01 — Permisos de roles inactivos

**Estado al 4 de septiembre de 2026:** Corregido. `User::hasPermissionTo()` resuelve el permiso exclusivamente contra el rol activo. La prueba cubre tanto `hasPermissionTo()` como `can()` y confirma que un rol inactivo no autoriza.

### Evidencia observada

- La regla de negocio exige que solo el rol activo aporte permisos.
- Muchos controladores recuperan correctamente el rol activo con `roles()->wherePivot('activo', true)->first()` y consultan permisos sobre `Role`.
- En otros lugares se usa `$user->hasPermissionTo()` o `$user->can()` directamente.
- `User::roles()` devuelve todos los roles asociados y no filtra por `activo`.
- Spatie Permission calcula permisos heredados a través de la relación `roles()`.

### Riesgo

Un permiso asignado a un rol inactivo podría ser reconocido por una comprobación directa sobre `User`, contradiciendo el modelo funcional de REDIL.

### Recomendación

1. Crear primero pruebas que asignen dos roles al mismo usuario y dejen activo solo uno.
2. Comprobar `Role::hasPermissionTo()`, `$user->hasPermissionTo()`, `$user->can()` y middleware de permisos.
3. Elegir un mecanismo canónico para permisos del rol activo y reemplazar gradualmente las variantes inconsistentes.
4. Evitar modificar globalmente la relación requerida por Spatie sin analizar asignación, caché y compatibilidad.

### Criterios de aceptación

- Un permiso del rol activo autoriza la operación.
- Un permiso presente únicamente en un rol inactivo no la autoriza.
- Cambiar el rol activo cambia el resultado inmediatamente.
- La caché no conserva permisos del rol anterior.

## USR-02 — Actualización polimórfica incompleta al cambiar rol

**Estado al 4 de septiembre de 2026:** Corregido. La relación `roles()` usa nuevamente la relación polimórfica de Eloquent y `switchActiveRole()` limita la desactivación por `model_id` y `model_type`. La prueba confirma que una fila de otro tipo de modelo con el mismo ID permanece intacta.

### Evidencia observada

`User::switchActiveRole()` desactiva registros con:

```php
DB::table('model_has_roles')
    ->where('model_id', $this->id)
    ->update(['activo' => false]);
```

La tabla `model_has_roles` es polimórfica y también contiene `model_type`. Además, `User` reemplaza la relación polimórfica de Spatie por un `belongsToMany()` sin condición de tipo.

### Riesgo

Si otro modelo utiliza roles y comparte el mismo identificador numérico, el cambio podría desactivar sus pivotes. La relación de `User` también podría leer filas pertenecientes a otro tipo de modelo.

### Recomendación

- Añadir una prueba con dos tipos de modelo que compartan `model_id`.
- Limitar actualización y relación por `model_type = User::class` de una manera compatible con Spatie.
- Verificar asignación, retiro, consulta de roles y cambio de rol activo después del ajuste.

### Criterios de aceptación

- Cambiar el rol de un usuario solo modifica sus pivotes.
- Exactamente un rol del usuario queda activo.
- No se leen roles de otros modelos con el mismo identificador.

## USR-03 — Cambio administrativo de contraseña sin autorización visible

**Decisión del piloto:** Pendiente para una fase posterior. No se modifica el comportamiento actual durante la implementación de la integración Usuarios–Grupos.

### Evidencia observada

- Las rutas `usuarios/{usuario}/cambiar-contrasena` y `usuarios/{usuario}/cambiar-contrasena-default` están dentro de `auth` y `verified`, pero fuera del grupo `verificarUsuario`.
- `UserController::cambiarContrasena()` y `cambiarContrasenaDefault()` modifican directamente el usuario recibido.
- En los métodos revisados no se observa verificación de permiso ni alcance sobre el usuario objetivo.

### Riesgo

Un usuario autenticado podría intentar cambiar la contraseña de otra cuenta conociendo su ID. El impacto incluye toma de cuenta y bloqueo del propietario.

### Recomendación

- Definir un permiso administrativo específico y una regla de alcance.
- Autorizar dentro del método o mediante Policy; no depender solo de la visibilidad del botón.
- Registrar actor, objetivo, fecha, motivo y origen del cambio.
- Para autogestión, exigir contraseña actual o un flujo seguro de recuperación.

### Criterios de aceptación

- Un usuario sin permiso recibe `403` y la contraseña no cambia.
- Un administrador solo opera sobre usuarios de su alcance.
- El cambio queda auditado y el propietario recibe una notificación segura.

## USR-04 — Contraseñas iniciales predecibles

**Decisión del piloto:** Pendiente para una fase posterior. No se modifica el comportamiento actual durante la implementación de la integración Usuarios–Grupos.

### Evidencia observada

- La creación asigna como contraseña predeterminada la identificación o `123456`.
- `cambiarContrasenaDefault()` repite la misma regla y muestra la contraseña resultante en el mensaje de éxito.
- El cambio manual administrativo acepta un mínimo de cinco caracteres, mientras el formulario de creación puede exigir ocho y complejidad.

### Riesgo

Identificación y `123456` son valores predecibles. La política es inconsistente y puede permitir acceso no autorizado antes del cambio del usuario.

### Recomendación

- Sustituir contraseñas predeterminadas por un token de activación o contraseña aleatoria de un solo uso.
- Forzar cambio en el primer ingreso y establecer vencimiento.
- Unificar longitud y reglas mediante una clase o configuración canónica.
- No mostrar credenciales completas en respuestas HTML o bitácoras.

### Criterios de aceptación

- No se puede deducir la contraseña inicial desde datos personales.
- El token o clave inicial expira y no puede reutilizarse.
- La política es igual en creación, restablecimiento administrativo y autogestión.

## USR-05 — Correo y contraseña dependen del formulario

### Evidencia observada

- `UserController::crear()` construye validaciones según los campos y la marca `requerido` del formulario.
- Correo y contraseña pueden validarse como `nullable`, o el campo podría no formar parte del formulario.
- La tabla `users` define `email` y `password` como no nulos; negocio exige ambos para nuevas cuentas ordinarias.

### Riesgo

Una configuración de formulario podría contradecir la regla global, producir errores de base de datos o crear cuentas con credenciales predeterminadas no deseadas.

### Recomendación

- Separar invariantes de cuenta de los campos configurables de presentación.
- Exigir correo real/único y estrategia de credencial en todo flujo ordinario de alta.
- Modelar explícitamente las excepciones: migración histórica y menor gestionado por responsable.
- Probar todos los tipos de formulario exterior e interior.

### Criterios de aceptación

- Ningún formulario ordinario puede omitir las invariantes de cuenta.
- Las excepciones están identificadas, auditadas y no se activan por una simple configuración accidental.

## USR-06 — Menores y responsable sin regla central

### Evidencia observada

- Existen campos históricos de menor/acudiente y la relación `parientes_usuarios` con `es_el_responsable`.
- El formulario puede crear relaciones familiares cuando contiene `tipo_pariente_id`.
- No se observó una validación central que obligue, para toda alta de menor, a registrar un responsable válido y a impedir contacto directo.

### Riesgo

Alguna variante de formulario podría crear un menor sin responsable, almacenar correo/teléfono directo o enviar credenciales a un destino incorrecto.

### Recomendación

- Definir un servicio o regla de dominio única para altas de menores.
- Resolver edad según país/configuración y fecha de nacimiento.
- Exigir responsable, consentimiento y canal de entrega de credenciales.
- Bloquear datos de contacto directo prohibidos por la política aplicable.
- Mantener las migraciones históricas como flujo separado.

### Criterios de aceptación

- Todo menor nuevo tiene responsable verificable.
- Las credenciales se entregan únicamente al responsable autorizado.
- Los formularios no pueden saltarse la regla.

## USR-07 — Eliminación de relación familiar

### Evidencia observada

- La ruta de eliminación está autenticada, pero no usa `verificarUsuario`.
- `eliminarRelacionFamiliar(ParienteUsuario $pariente)` elimina las dos direcciones de la relación sin autorización visible sobre las personas involucradas.

### Riesgo

Un operador podría eliminar relaciones familiares ajenas mediante manipulación del identificador.

### Recomendación y aceptación

- Autorizar ambas personas y el pivote antes de eliminar.
- Probar relación propia, relación administrable, relación fuera del alcance y otro tenant.
- Registrar quién eliminó la relación y decidir si debe ser recuperable.

## USR-08 — Cobertura automatizada insuficientemente localizable

No se encontraron pruebas dedicadas por nombre a estos flujos. Crear pruebas pequeñas por hallazgo antes de refactorizar. No eliminar pruebas existentes.

## 4. Integración con Grupos

Antes de resolver autoasistencia, integrantes o encargados, preservar lo siguiente:

- Grupos no crea usuarios desde el enlace de autoasistencia.
- Solo un usuario activo y ya vinculado al grupo puede marcarse.
- La autorización administrativa sigue el rol activo y el alcance del grupo.
- El flujo de menores pertenece a Usuarios, aunque el menor posteriormente sea integrante de un grupo.

Cruzar esta revisión con `_docs_agente/revision_tecnica_grupos.md`.

## 5. Formato de respuesta del desarrollador

Para cada ID, responder:

```text
ID:
Estado: confirmado | no reproducible | comportamiento intencional | corregido | pendiente
Diagnóstico:
Archivos modificados:
Pruebas creadas o ejecutadas:
Resultado:
Riesgos o decisiones pendientes:
Commit o PR:
```

No agrupar varios hallazgos bajo una sola respuesta si tienen causas o soluciones diferentes.

## 6. Orden recomendado

1. USR-01 y USR-02, porque afectan autorización transversal.
2. USR-03 y USR-04, por impacto sobre cuentas y credenciales.
3. USR-05 y USR-06, para alinear creación con las reglas de negocio.
4. USR-07 y después cobertura adicional USR-08.
