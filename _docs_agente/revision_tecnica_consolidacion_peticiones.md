# Revisión técnica para Consolidación y Peticiones

**Fecha del análisis:** 2 de septiembre de 2026

**Estado:** Diagnóstico pendiente de validación e implementación

**Destinatario:** Equipo de desarrollo de REDIL Cloud

**Alcance:** Consolidación, Peticiones y regla transversal de rol activo

> Este documento no afirma que todos los puntos sean vulnerabilidades explotables. Registra comportamientos observados en el código que deben reproducirse, probarse y resolverse según las reglas de negocio. No se modificó código funcional durante esta revisión.

## 1. Reglas de negocio que deben preservarse

Antes de corregir cualquier punto, conservar estas decisiones:

1. Un usuario puede tener varios roles asignados, pero solamente uno activo.
2. Las restricciones y permisos de la sesión deben proceder del rol activo.
3. Usuario, persona y asistente representan la misma entidad `User` en REDIL Cloud.
4. Una persona externa puede enviar una petición de oración sin convertirse en `User`.
5. Los datos externos almacenados en una petición no representan membresía.
6. Los consolidadores solo deben operar sobre las personas permitidas por su cobertura, ministerio o zona.

## 2. Resumen y prioridad

| ID | Prioridad | Área | Hallazgo | Tipo |
|---|---|---|---|---|
| SEG-01 | Alta | Consolidación | Las acciones Livewire sobre tareas no muestran autorización por persona ni por asignación. | Autorización horizontal |
| SEG-02 | Alta | Peticiones | La comprobación pública de correo devuelve información del usuario. | Privacidad / enumeración |
| SEG-03 | Alta | Peticiones | Una petición pública puede asociarse a un `User` mediante un identificador sin prueba visible de propiedad. | Asociación de identidad |
| SEG-04 | Alta | Peticiones | Las eliminaciones no exigen explícitamente un permiso de eliminación en los métodos revisados. | Autorización / pérdida de datos |
| SEG-05 | Media | Consolidación | La administración de bloques y sedes tiene la verificación de permiso comentada. | Autorización administrativa |
| AUD-01 | Media | Consolidación | Procesos sin sesión atribuyen la bitácora al usuario con ID 1. | Trazabilidad |
| FUN-01 | Media | Peticiones | La relación `SeguimientoPeticion::peticion()` referencia `Peticio::class`. | Error funcional probable |
| INT-01 | Media | Peticiones | La integración bíblica desactiva validación TLS y usa llamadas directas sin controles claros de resiliencia. | Seguridad / integración |
| GOV-01 | Media | Peticiones | Falta una política documentada para datos personales de solicitantes externos. | Privacidad / gobierno de datos |
| ARQ-01 | Media | Transversal | Conviven comprobaciones sobre el rol activo y comprobaciones directas sobre `User`. | Consistencia de autorización |

Las prioridades indican el orden recomendado de revisión. No sustituyen una clasificación formal de seguridad.

---

## 3. Hallazgos detallados

## SEG-01 — Autorización de tareas de Consolidación

### Evidencia observada

- `routes/app.php`: la ruta `/consolidacion/gestionar-tareas/{usuario}` está protegida por `auth` y `verified`, pero no por un middleware de alcance sobre la persona.
- `ConsolidacionController::gestionarTareas()` exige `consolidacion.gestionar_tareas`, pero entrega directamente el `User` recibido por route model binding.
- `app/Livewire/Consolidacion/GestionarTareas.php` recibe identificadores públicos en acciones como:
  - `crearTarea($personaId)`
  - `editarTarea($tareaAsignadaId)`
  - `guardarTarea()`
  - `eliminarTarea($id)`
  - `actualizarEstado($tareaAsignadaId, $nuevoEstadoId)`
  - `crearTareaDefault($personaId, $tareaId, $nuevoEstadoId)`
  - `verHistorial($tareaAsignadaId)`
  - `agregarNuevoHistorial()`
  - `eliminarHistorial($id)`
- En esas acciones no se observó una llamada explícita a `authorize()`, Policy, Gate o verificación de que la persona/asignación pertenezca al alcance de `auth()->user()->consolidacion()`.

### Riesgo

Un operador con el permiso general podría intentar usar un identificador de otra persona, tarea o historial fuera de su zona. Ocultar botones o filtrar el listado no protege por sí solo las solicitudes Livewire.

### Recomendación

1. Definir una única regla reutilizable para determinar si el operador puede administrar a una persona en Consolidación.
2. Aplicarla al abrir la página y dentro de cada acción Livewire que lea o modifique identificadores.
3. Autorizar también la asignación o historial recuperado, no solamente el `User` inicial.
4. Considerar una Policy o servicio de alcance para evitar repetir consultas diferentes.
5. No confiar en propiedades Livewire ni identificadores enviados por eventos como evidencia de autorización.

### Criterios de aceptación

- Un operador global puede gestionar cualquier persona permitida dentro del tenant.
- Un operador de zona o ministerio solo puede gestionar personas de su alcance.
- Cambiar manualmente un ID de usuario, tarea, asignación o historial devuelve `403` o una respuesta segura equivalente.
- La misma regla se aplica al visualizar, crear, editar, cambiar estado y eliminar.
- No hay acceso cruzado entre tenants.

### Pruebas mínimas

- Operador global sobre persona válida.
- Operador limitado sobre persona dentro de su zona.
- Operador limitado sobre persona fuera de su zona.
- Manipulación del ID de asignación y del historial.
- Usuario autenticado sin `consolidacion.gestionar_tareas`.

---

## SEG-02 — Exposición en la comprobación pública de correo

### Evidencia observada

- `POST /peticion/publica/verificar-correo` es una ruta pública.
- `PeticionController::verificarCorreo()` busca el correo y, cuando existe, devuelve:
  - `exists: true`
  - ID del usuario
  - nombre completo
  - correo
  - URL de fotografía
- En el método revisado no se observó rate limiting, respuesta genérica o desafío de propiedad del correo.

### Riesgo

Permite consultar si una dirección pertenece a REDIL y obtener datos adicionales. Un tercero podría automatizar intentos con listas de correos para identificar miembros.

### Recomendación

- Definir primero si la interfaz realmente necesita mostrar identidad completa.
- Evitar devolver ID, nombre y fotografía en un endpoint público salvo que exista una verificación previa.
- Preferir una respuesta genérica o enviar un enlace/código al correo para que su propietario confirme la asociación.
- Añadir rate limiting y registrar intentos anómalos sin almacenar datos sensibles innecesarios.
- Evitar diferencias de respuesta que permitan enumeración si el negocio no necesita revelar existencia.

### Criterios de aceptación

- Una persona no puede obtener información de otra cuenta escribiendo su correo.
- El propietario legítimo puede asociar su petición mediante un mecanismo verificable.
- Los intentos repetidos están limitados.
- Las respuestas no incluyen información innecesaria.

### Pruebas mínimas

- Correo inexistente y existente producen respuestas seguras.
- Rate limit después del umbral acordado.
- No se exponen ID, foto ni nombre antes de comprobar propiedad.
- Asociación posterior con token válido, inválido, vencido y reutilizado.

---

## SEG-03 — Asociación pública de una petición con un usuario

### Evidencia observada

- `PeticionController::crear()` recibe `asociar_usuario_id` desde una solicitud no autenticada.
- Si el identificador existe, ejecuta `User::find($asociarUsuarioId)`.
- Después asigna directamente ese ID a `peticiones.user_id`.
- reCAPTCHA limita automatización, pero no demuestra que el solicitante controle el correo o la cuenta elegida.

### Riesgo

Una persona podría atribuir una petición a otra cuenta si conoce o consigue su identificador. Esto puede afectar historial, privacidad, notificaciones e indicadores.

### Recomendación

- No aceptar un ID de usuario como prueba de identidad.
- Para asociar la petición, exigir una sesión autenticada o un token de un solo uso enviado al correo del usuario.
- Si el visitante no completa la verificación, guardar la petición como externa sin `user_id`.
- Validar tenant, vigencia, propósito y uso único del token.

### Criterios de aceptación

- Una petición pública solo queda asociada a `User` si existe autenticación o comprobación verificable del correo.
- Un ID manipulado nunca asocia la petición a otra cuenta.
- Fallar la verificación no impide solicitar oración como externo.
- El flujo no revela si una cuenta existe más allá de lo estrictamente necesario.

### Pruebas mínimas

- Asociación autenticada correcta.
- Asociación pública con token válido.
- ID modificado, token inválido, vencido o ya usado.
- Petición externa sin asociación.
- Intento de asociar un usuario de otro tenant.

---

## SEG-04 — Autorización para eliminar peticiones

### Evidencia observada

- Las rutas de eliminación están dentro de `auth` y `verified`.
- `PeticionController::eliminaciones()` limita inicialmente la colección mediante permisos de listado, pero no exige un permiso específico de eliminación antes de borrar peticiones y seguimientos.
- `PeticionController::eliminacion($id)` busca directamente la petición y elimina seguimientos y petición sin una verificación visible de permiso o alcance.
- Ambos endpoints usan `POST`, no `DELETE`; esto no causa por sí solo el problema de autorización, pero dificulta expresar el contrato HTTP.

### Riesgo

Un usuario autenticado podría borrar información que puede ver o cuyo ID conoce aunque no deba tener capacidad de eliminación. La eliminación incluye seguimientos y puede ser irreversible.

### Recomendación

1. Definir un permiso explícito para eliminación y una regla de alcance por petición.
2. Aplicar autorización dentro del método o mediante una Policy, tanto individual como masiva.
3. Construir la selección masiva siempre desde una consulta ya autorizada.
4. Evaluar `SoftDeletes` y auditoría si el negocio requiere recuperación o trazabilidad.
5. Cambiar a verbo `DELETE` cuando sea compatible con las vistas y convenciones del proyecto.

### Criterios de aceptación

- Ver una petición no concede automáticamente permiso para eliminarla.
- La eliminación masiva no amplía el alcance del operador.
- Un usuario no autorizado recibe `403` y no se elimina ningún registro.
- Existe una decisión documentada sobre eliminación definitiva, restauración y auditoría.

### Pruebas mínimas

- Administrador autorizado elimina una petición de su alcance.
- Usuario con permiso de listado, pero sin permiso de eliminación.
- Petición fuera del ministerio o zona.
- Eliminación masiva con filtros manipulados.
- Fallo durante borrado de seguimientos: comprobar atomicidad.

---

## SEG-05 — Administración de bloques sin permiso explícito

### Evidencia observada

- `ConsolidacionController::bloques()` obtiene el rol activo, pero la llamada a `verificacionDelPermiso()` está comentada.
- `GestionarBloques` permite crear y eliminar bloques, asignar sedes y desvincular sedes.
- Las acciones Livewire revisadas no contienen autorización explícita.

### Riesgo

Un usuario autenticado que alcance la ruta o invoque acciones Livewire podría alterar la organización territorial de los dashboards sin el privilegio administrativo esperado.

### Recomendación

- Acordar el permiso canónico para ver y para administrar bloques.
- Separar, si corresponde, permisos de lectura y escritura.
- Autorizar tanto la página como cada acción Livewire.
- Validar que una sede no quede en varios bloques si esa es la regla de negocio.

### Criterios de aceptación

- Solo roles autorizados crean, eliminan o modifican asignaciones de sedes.
- Acceder directamente a Livewire sin permiso no modifica datos.
- La regla de exclusividad de sede queda probada o explícitamente descartada.

---

## AUD-01 — Autor fijo en la bitácora

### Evidencia observada

`TareaConsolidacionUsuario::registrarBitacora()` establece:

```php
'autor_id' => auth()->id() ?? 1,
```

El evento también se ejecuta cuando una tarea se crea o actualiza desde procesos automáticos, seeders, jobs o comandos sin sesión HTTP.

### Riesgo

Las acciones automáticas pueden aparecer como realizadas por el usuario 1, aunque esa persona no haya intervenido. También se presupone que el ID 1 existe y representa lo mismo en cada tenant.

### Recomendación

- Diferenciar actor humano de actor de sistema.
- Permitir `autor_id` nulo o utilizar una identidad técnica explícita si el modelo de auditoría lo exige.
- Registrar adicionalmente el origen: web, job, importación, migración, seeder o automatización.
- No depender de un ID fijo entre tenants.

### Criterios de aceptación

- Las acciones humanas conservan el usuario autenticado correcto.
- Las automáticas no se atribuyen falsamente a una persona.
- La estrategia funciona en todos los tenants y durante migraciones.

---

## FUN-01 — Relación con nombre de modelo incorrecto

### Evidencia observada

En `app/Models/SeguimientoPeticion.php`:

```php
public function peticion(): BelongsTo
{
    return $this->belongsTo(Peticio::class);
}
```

No se encontró una clase `App\Models\Peticio`; el modelo existente es `Peticion`.

### Riesgo

Cuando se acceda a `$seguimiento->peticion`, Laravel puede intentar resolver una clase inexistente y producir un error. El problema puede permanecer oculto si la relación todavía no se utiliza en los recorridos comunes.

### Recomendación

- Crear primero una prueba pequeña que acceda a la relación.
- Cambiar la referencia a `Peticion::class` si la prueba confirma la intención.
- Comprobar también el nombre de la llave foránea `peticion_id`.

### Criterios de aceptación

- Un seguimiento recupera exactamente su petición.
- Una petición recupera sus seguimientos.
- Ambas relaciones funcionan con eager loading.

---

## INT-01 — Integración externa de Biblia

### Evidencia observada

- `GestionarPeticiones::buscarBibliaCita()` configura `verify_peer` y `verify_peer_name` como `false`.
- Las búsquedas usan `file_get_contents()` contra `api.biblia.com`.
- Los parámetros se concatenan en la URL.
- No se observan timeout, retry, validación formal de todos los parámetros ni manejo uniforme de respuestas HTTP.

### Riesgo

- Desactivar TLS impide verificar correctamente la identidad del servidor remoto.
- Una demora del proveedor puede bloquear la solicitud.
- Caracteres inesperados pueden alterar la consulta.
- Errores de red o respuestas inválidas pueden producir experiencias inconsistentes.

### Recomendación

- Usar el cliente HTTP de Laravel con TLS habilitado.
- Validar libro, capítulo, versículo y palabras clave.
- Usar parámetros de consulta codificados, timeout, retry limitado y manejo de errores.
- No registrar la clave del proveedor ni URLs completas que la contengan.
- Agregar pruebas con `Http::fake()`.

### Criterios de aceptación

- TLS permanece validado.
- La interfaz responde de forma controlada ante timeout, 4xx, 5xx o JSON inválido.
- La clave no aparece en logs ni mensajes.
- Las pruebas no realizan llamadas reales.

---

## GOV-01 — Datos personales de solicitantes externos

### Evidencia observada

Una petición externa puede almacenar:

- nombre;
- correo;
- teléfono;
- género;
- país;
- descripción de la petición;
- respuestas y seguimientos.

El flujo también puede enviar correos y construir enlaces de contacto por WhatsApp.

### Riesgo

Aunque no exista un `User`, siguen siendo datos personales y el texto de la petición puede contener información especialmente delicada. No se encontró en el alcance revisado una política técnica explícita de finalidad, retención, acceso o eliminación.

### Recomendación

- Validar con el responsable legal el texto de consentimiento y la política aplicable en cada país.
- Documentar finalidad, campos mínimos, personas autorizadas, retención y eliminación.
- Evitar solicitar datos que no sean necesarios para atender la oración.
- Determinar si la descripción o los seguimientos necesitan protección reforzada.
- Revisar qué contenido se envía por correo, se exporta a Excel y aparece en logs.

### Criterios de aceptación

- El formulario informa la finalidad del tratamiento.
- Existe plazo o criterio de conservación aprobado.
- Solo roles autorizados consultan y exportan los datos.
- Existe procedimiento de eliminación o anonimización.
- Las decisiones legales están documentadas por país; el desarrollador no debe inventarlas.

---

## ARQ-01 — Consistencia del rol activo

### Evidencia observada

- El negocio establece un único rol activo por sesión.
- Muchos controladores obtienen el rol mediante `roles()->wherePivot('activo', true)->first()` y validan sobre ese `Role`.
- También existen llamadas directas a `$user->can()` y `$user->hasPermissionTo()`.
- `User::roles()` devuelve todos los roles asignados y no aplica por defecto un filtro de `activo`.

### Riesgo

Si las llamadas directas agregan permisos de todos los roles asignados, un rol inactivo podría aportar capacidades inesperadas. El equipo indica que este comportamiento está estandarizado, pero debe quedar demostrado por pruebas para que futuros cambios no rompan la regla.

### Recomendación

- Documentar cuál API es la única aprobada para comprobar permisos del rol activo.
- Crear pruebas de regresión con un usuario que tenga dos roles contradictorios y solo uno activo.
- Inventariar las llamadas directas antes de hacer reemplazos masivos.
- Centralizar la resolución del rol activo y definir el comportamiento cuando no exista o haya más de uno.

### Criterios de aceptación

- Un permiso perteneciente solo a un rol inactivo nunca autoriza una operación.
- Cambiar el rol activo actualiza inmediatamente las capacidades.
- Cero roles activos y varios activos tienen una respuesta definida y segura.

---

## 4. Orden sugerido de trabajo

### Fase 1 — Proteger identidad y datos

1. SEG-03: asociación pública de usuario.
2. SEG-02: comprobación pública de correo.
3. SEG-04: eliminación de peticiones.
4. SEG-01: alcance de tareas de consolidación.

### Fase 2 — Cerrar administración y trazabilidad

5. SEG-05: bloques y sedes.
6. ARQ-01: prueba de rol activo.
7. AUD-01: actor de bitácora.

### Fase 3 — Corregir integración y defecto puntual

8. FUN-01: relación `Peticion`.
9. INT-01: cliente de Biblia.
10. GOV-01: aplicar decisiones de privacidad aprobadas.

No se recomienda resolver todos los puntos en un único cambio. Cada hallazgo debería tener pruebas y revisión independientes.

## 5. Procedimiento recomendado para el desarrollador

Para cada hallazgo:

1. Confirmar la regla de negocio.
2. Reproducir el comportamiento con una prueba que inicialmente falle cuando corresponda.
3. Consultar documentación compatible mediante Laravel Boost.
4. Implementar el cambio más pequeño que cierre el riesgo.
5. Ejecutar la prueba específica.
6. Ejecutar las pruebas relacionadas del módulo.
7. Formatear PHP con `vendor/bin/pint --dirty --format agent`.
8. Registrar decisión, archivos, pruebas y cualquier deuda restante.

No eliminar pruebas existentes ni cambiar dependencias sin aprobación.

## 6. Formato de respuesta para devolver al orquestador

Tu compañero puede responder por cada hallazgo utilizando este bloque:

```markdown
### Hallazgo: SEG-XX

- Decisión aplicada:
- Regla de negocio confirmada:
- Archivos modificados:
- Migraciones creadas:
- Pruebas agregadas o modificadas:
- Comandos de prueba ejecutados:
- Resultado:
- Cambio visible para el usuario:
- Riesgos o preguntas que permanecen:
- Commit o rama, si aplica:
```

Con esa información se actualizarán los `current-state.md` sin depender únicamente del historial mental de quien implementó la solución.

## 7. Archivos principales revisados

- `routes/app.php`
- `app/Http/Controllers/ConsolidacionController.php`
- `app/Http/Controllers/PeticionController.php`
- `app/Livewire/Consolidacion/GestionarTareas.php`
- `app/Livewire/Consolidacion/GestionarBloques.php`
- `app/Livewire/Peticiones/GestionarPeticiones.php`
- `app/Models/TareaConsolidacionUsuario.php`
- `app/Models/SeguimientoPeticion.php`
- Migraciones tenant de Consolidación y Peticiones.

## 8. Definición global de terminado

La revisión estará cerrada cuando:

- Cada hallazgo tenga decisión explícita: corregido, aceptado, descartado o aplazado con responsable.
- Las reglas de autorización estén aplicadas en el servidor, no solo en la interfaz.
- Las rutas públicas no permitan atribuir peticiones a terceros ni revelar identidad innecesaria.
- Las operaciones destructivas tengan permiso, alcance y estrategia de recuperación definidos.
- La bitácora distinga actores humanos y automáticos.
- Existan pruebas del rol activo y de los límites territoriales.
- La política de datos externos haya sido validada por el responsable legal.
- La documentación de Usuarios, Consolidación y Peticiones refleje el resultado final.
