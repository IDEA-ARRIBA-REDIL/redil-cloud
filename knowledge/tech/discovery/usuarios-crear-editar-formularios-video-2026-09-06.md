---
type: discovery-evidence
id: DISC-USUARIOS-VIDEO-001
title: Recorrido de creación, edición y formularios de Usuarios
status: reviewed-against-code
domain: usuarios
source_type: video
source_file: CREAR USUARIO.mov
source_date: 2026-09-06
duration_seconds: 374.2
privacy: description-only-no-personal-data-copied
reviewed_at: 2026-09-06
---

# Evidencia: creación, edición y formularios de Usuarios

## Alcance de la revisión

El video suministrado recorre durante 6:14 minutos la creación pública de una cuenta, la creación administrativa de una persona, la administración de formularios configurables y varias áreas de edición del usuario. La revisión visual se contrastó con `UserController`, `FormularioUsuarioController`, las rutas de Usuarios, las vistas y los componentes Livewire de formularios.

Esta nota describe comportamiento observado y respaldado por código. No copia nombres, correos, teléfonos, identificaciones ni otras informaciones personales visibles en la grabación. La narración de voz no se transcribió literalmente; por tanto, no se incorporan como reglas confirmadas afirmaciones que dependan únicamente del audio.

## Línea de tiempo funcional

| Tiempo aproximado | Evidencia observada |
|---|---|
| 00:00–00:35 | Entrada desde la pantalla pública de autenticación hacia un proceso de registro. |
| 00:35–01:35 | Paso de datos personales: nombres, apellidos, fecha de nacimiento, tipo e identificación, sexo, estado civil, país y pregunta sobre hijos menores. |
| 01:35–02:15 | Paso de contacto: teléfono móvil, correo, dirección y pregunta condicional sobre residencia territorial. |
| 02:15–02:40 | Definición y confirmación de contraseña con indicación de complejidad. |
| 02:40–03:15 | Resumen previo del registro, posibilidad visual de regresar a secciones y transición por correo antes del acceso a la aplicación. |
| 03:15–04:15 | Creación administrativa de una persona mediante un formulario distribuido en secciones configuradas. |
| 04:15–05:10 | Listado y creación de formularios de usuario, con tipos, roles, usuario y sede predeterminados, límites de edad y términos y condiciones. |
| 05:10–05:25 | Administración de secciones y campos del formulario. |
| 05:25–05:45 | Ejecución de un formulario personalizado para crear otra persona. |
| 05:45–06:14 | Edición del usuario mediante áreas de datos principales, información congregacional, geoasignación y relaciones familiares. |

Los tiempos son índices para localizar la evidencia, no contratos exactos de interfaz.

## Flujos observados y contraste técnico

### 1. Registro público configurable

**[VIDEO OBSERVADO]** El registro público se presenta como un asistente por pasos. Agrupa datos personales, contacto, información congregacional y contraseña; antes de terminar presenta un resumen de la información.

**[CÓDIGO]** Las rutas públicas `usuario.nuevoExterior`, `usuario.nuevoExteriorConGrupo` y `usuario.crearInscripcion` usan un `FormularioUsuario`. `UserController::nuevo()` obtiene la vista y el layout desde el tipo de formulario, ordena sus secciones y prepara catálogos para los campos configurados.

**[CÓDIGO]** `UserController::crear()` construye las reglas de validación según los campos asociados al formulario y si cada campo fue marcado como requerido. Identificación y correo, cuando están presentes, se validan como únicos.

**[CÓDIGO]** La contraseña configurada exige al menos ocho caracteres e incluye mayúscula, minúscula, número, carácter especial y confirmación. Si el formulario no incluye contraseña, el controlador aplica una credencial inicial según la configuración existente.

**[NEGOCIO VALIDADO + CÓDIGO]** Al crear la cuenta se envía un correo con el botón “Verificar correo electrónico”. El botón abre una URL temporal firmada que valida el identificador y el hash, ejecuta `markEmailAsVerified()`, completa `users.email_verified_at`, autentica al usuario y lo dirige al dashboard. Antes de verificar, las rutas protegidas por `verified` no permiten operar normalmente en la aplicación.

**[NEGOCIO VALIDADO + CÓDIGO]** Los usuarios sin `email_verified_at` no aparecen en el listado de personas: tanto la consulta general como `User::discipulos()` aplican `whereNotNull('email_verified_at')` cuando solicitan usuarios verificados.

### 2. Creación administrativa

**[VIDEO OBSERVADO]** Desde Personas → Nueva persona se utiliza un formulario interno cuyas secciones y campos pueden tener títulos personalizados. El mismo motor presenta datos personales, contacto y datos congregacionales.

**[CÓDIGO]** La ruta autenticada `usuario.nuevo` ejecuta `UserController::nuevo()`. La policy `nuevoUsuarioPolitica` autoriza el acceso y, para formularios internos, se recupera el rol activo del operador.

**[CÓDIGO]** El resultado puede quedar aprobado o pendiente según el tipo de formulario, su configuración y el permiso `personas.privilegio_crear_asistentes_aprobados` del rol activo.

**[CÓDIGO]** El formulario puede asignar automáticamente tipo de usuario y sede; si no especifica tipo, se busca el `TipoUsuario` marcado como predeterminado. También puede asociar la persona a un grupo cuando el flujo recibe `grupoId`.

### 3. Administración de formularios

**[VIDEO OBSERVADO]** El listado muestra tarjetas con nombre, rango de edad, roles y tipo de formulario. Ofrece búsqueda, visualización de ocultos y acciones visibles para editar, duplicar, ocultar/mostrar y eliminar.

**[CÓDIGO]** `GestionarFormularios` implementa búsqueda, soft delete/restauración, duplicación de formulario con sus secciones y campos, y eliminación definitiva. La eliminación definitiva desvincula campos y elimina las secciones antes del formulario.

**[VIDEO OBSERVADO + CÓDIGO]** La configuración principal permite definir:

- nombre, título, etiqueta y descripción;
- tipo de formulario;
- roles que pueden usarlo;
- tipo de usuario y sede asignados por defecto;
- validación de edad con mínimo, máximo y mensaje de error;
- visibilidad y contenido de términos y condiciones.

**[CÓDIGO]** Nombre, título y tipo son obligatorios. Al activar edad se exigen límites numéricos; al activar términos se exige su contenido. Los roles asociados se adjuntan al crear y se sincronizan al editar.

**[NEGOCIO VALIDADO + CÓDIGO]** La obligatoriedad de los datos que captura un formulario se define al asociar cada campo con una sección mediante el atributo `requerido`. `UserController::crear()` traduce esa configuración en reglas `required` o `nullable`, además de aplicar validaciones especiales como formato, unicidad y límites de edad.

**[NEGOCIO VALIDADO + CÓDIGO]** Un formulario puede eliminarse aunque ya haya sido utilizado para crear o editar usuarios porque `users` no conserva una relación directa con el formulario. La eliminación definitiva desvincula sus campos de las secciones y elimina las secciones y el formulario; los valores de usuario se relacionan con los campos, no con `formularios_usuario`.

### 4. Secciones y campos

**[VIDEO OBSERVADO]** Existe una segunda etapa de configuración dedicada a las secciones y campos que compondrán el formulario.

**[CÓDIGO]** `GestionarSeccionesYCampos` permite crear, editar, ordenar y eliminar secciones; agregar, editar, mover, ordenar y retirar campos; y configurar por campo el ancho visual, obligatoriedad e información de apoyo.

**[CÓDIGO]** El orden de secciones y campos se persiste y determina la presentación usada por los flujos de creación y edición.

### 5. Edición del usuario

**[VIDEO OBSERVADO]** El perfil de administración organiza la edición en datos principales, información congregacional, geoasignación y relaciones familiares. La información congregacional muestra tipo de usuario, grupo, procesos de crecimiento y roles independientes.

**[CÓDIGO]** La ruta `usuario.modificar` usa `UserController::modificar()` y la policy `modificarUsuarioPolitica`. El método utiliza el formulario recibido para presentar los campos configurados y carga catálogos y campos extra.

**[CÓDIGO]** La edición principal se procesa mediante `usuario.editar`; existen rutas separadas para información congregacional, geoasignación, relaciones familiares y autoedición por sección.

## Reglas que esta evidencia permite afirmar

- La creación y edición de Usuarios no se basa en un formulario único rígido: depende de `FormularioUsuario`, sus secciones, campos y tipo.
- Existen entradas públicas y administrativas que comparten el motor de formularios, pero aplican autorización y resultados distintos.
- La obligatoriedad y validación de cada dato dependen tanto del campo configurado como de reglas especiales del controlador.
- Un formulario puede orientar la clasificación inicial mediante tipo de usuario y sede predeterminados.
- Los roles asociados permiten seleccionar formularios disponibles para el rol activo mediante `User::formularios()`; la autorización para crear o modificar se comprueba por separado en `UserPolicy` mediante permisos del rol activo.
- La edición congregacional, geográfica y familiar se separa de la edición principal aunque pertenezca al mismo usuario.

## Preguntas que permanecen abiertas

- ¿Qué tipo exacto de formulario se utilizó en el registro público del video y cuál es su política de aprobación?
- ¿Qué roles pueden crear, modificar, duplicar, ocultar o eliminar formularios?
- ¿Qué diferencias funcionales deben preservarse entre los tipos visibles: autoeditar, nuevo, nuevo externo, nuevo menor y modificar?

## Validaciones funcionales posteriores al video

El responsable del producto confirmó el 2026-09-06:

- La activación ocurre al pulsar el botón del correo; técnicamente se completa `users.email_verified_at`.
- Antes de la verificación la cuenta no puede entrar al flujo normal ni aparece en el listado de usuarios.
- Los campos obligatorios se definen dentro de la configuración del formulario.
- Un formulario utilizado puede eliminarse porque el usuario no conserva una relación directa con él.

## Archivos contrastados

- `routes/app.php`
- `app/Http/Controllers/UserController.php`
- `app/Http/Controllers/FormularioUsuarioController.php`
- `app/Livewire/FormulariosParaUsuarios/GestionarFormularios.php`
- `app/Livewire/FormulariosParaUsuarios/GestionarSeccionesYCampos.php`
- `resources/views/contenido/paginas/usuario/`
- `resources/views/contenido/paginas/formularios-usuarios/`

## Uso futuro de esta evidencia

Para convertir una observación de esta nota en regla canónica de negocio, el responsable funcional debe confirmarla explícitamente. Los cambios de implementación deberán abrir un WI independiente; esta revisión no autoriza modificaciones funcionales.
