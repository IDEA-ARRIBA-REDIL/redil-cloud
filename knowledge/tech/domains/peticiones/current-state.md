---
id: MOD-PETICIONES
title: Módulo de Peticiones de Oración
artifact_type: domain-current-state
status: draft
language: es
last_reviewed: 2026-09-02
technical_review: partial
business_review: partial
owners:
  - por-definir
aliases:
  - peticiones
  - peticion
  - peticiones de oracion
  - solicitudes de oracion
  - intercesion
  - intercesores
related_domains:
  - usuarios
  - intercesores
  - notificaciones
  - sedes
---

# Módulo de Peticiones de Oración

> Borrador separado del recorrido funcional conjunto. Una petición de una persona externa no crea un usuario externo en REDIL. Este documento no cambia rutas, almacenamiento ni políticas de datos.

## 1. Clasificación de evidencia

- **[CÓDIGO]**: comprobado directamente en el repositorio el 2 de septiembre de 2026.
- **[NEGOCIO VALIDADO]**: confirmado por el responsable del producto.
- **[NEGOCIO POR VALIDAR]**: extraído del resumen de la videollamada y pendiente de confirmación específica.
- **[PENDIENTE]**: decisión o detalle todavía abierto.
- **[RIESGO]**: punto técnico o de privacidad que debe revisarse; no significa por sí solo que haya un incidente.

## 2. Propósito y frontera de identidad

**[NEGOCIO VALIDADO]** Peticiones permite que una persona solicite oración. Si ya está autenticada, la petición puede quedar asociada con su `User`. Si es externa y no pertenece a la iglesia, puede aportar datos mínimos para la atención sin convertirse en miembro ni generar un registro en `users`.

La distinción canónica es:

```text
Usuario REDIL autenticado → petición vinculada mediante user_id
Persona externa           → datos de contacto guardados en la petición, sin crear User
```

Una persona externa sí produce un registro de petición y puede dejar datos personales para ser atendida; lo que no se crea es una cuenta o membresía en REDIL.

## 3. Alcance

### Incluido

- Formulario público de petición.
- Peticiones creadas desde el panel autenticado.
- Asociación opcional con un usuario existente.
- Datos mínimos del solicitante externo.
- Tipos de petición y mensajes configurables.
- Estados, seguimientos y respuestas.
- Asignación a intercesores.
- Notificaciones por correo y contacto opcional por WhatsApp.
- Dashboard, filtros, indicadores y exportaciones.

### Fuera del alcance primario

- La membresía y autenticación pertenecen a Usuarios.
- La gestión general de usuarios con rol de intercesor debe documentarse como dependencia.
- Consejería no debe confundirse con una petición de oración, aunque una atención pueda originar procesos posteriores.

## 4. Conceptos y datos

| Concepto | Significado | Evidencia |
|---|---|---|
| Petición | Solicitud con descripción, tipo, fecha y estado. Puede apuntar a un usuario o contener datos externos. | **[CÓDIGO]** `Peticion` y migraciones |
| Tipo de petición | Clasificación configurable con orden, mensajes, banner, versículos e intercesores relacionados. | **[CÓDIGO]** `TipoPeticion` |
| Seguimiento | Interacción o respuesta realizada por un usuario operador sobre una petición. | **[CÓDIGO]** `SeguimientoPeticion`, Livewire |
| Intercesor asignado | Usuario relacionado mediante `asignacion_peticion_id` para atender la petición. | **[CÓDIGO]** `Peticion::asignado()` |
| Solicitante externo | Persona que deja nombre, correo, teléfono opcional, género y país en la petición, sin crear una cuenta. | **[CÓDIGO]** campos externos y validación pública; **[NEGOCIO VALIDADO]** frontera de identidad |
| Estado | `1` pendiente, `3` en proceso y `2` cerrada, según el uso actual del controlador y Livewire. | **[CÓDIGO]** comentarios y cálculos actuales |

## 5. Flujos verificados

### 5.1 Petición pública

1. **[CÓDIGO]** Las rutas de formulario, creación y comprobación de correo son públicas.
2. **[CÓDIGO]** El visitante elige tipo y escribe la descripción.
3. **[CÓDIGO]** Si no se asocia con un usuario, se exigen nombre, correo, género, país y reCAPTCHA; el teléfono es opcional.
4. **[CÓDIGO]** Si el correo coincide con un usuario, la interfaz puede ofrecer asociación con esa cuenta.
5. **[CÓDIGO]** La petición se crea pendiente y puede enviar un correo con el mensaje configurado y un versículo.
6. **[CÓDIGO]** La página de éxito exige que el identificador coincida con la petición recién guardada en sesión.

### 5.2 Petición desde una sesión autenticada

- **[CÓDIGO]** El usuario requiere permiso para abrir la creación interna.
- **[CÓDIGO]** Sin privilegio para crear en nombre de otros, la petición se asocia con el usuario autenticado.
- **[CÓDIGO]** Con `peticiones.crear_peticion_otros`, el operador puede elegir un usuario o registrar la petición de un solicitante externo.

### 5.3 Atención y seguimiento

- **[CÓDIGO]** La gestión usa el rol activo y distingue alcance total o ministerial.
- **[CÓDIGO]** Livewire permite responder, guardar seguimiento, cambiar estado y asignar intercesor.
- **[CÓDIGO]** Una respuesta puede enviarse por correo y presentar un enlace de contacto por WhatsApp cuando existe teléfono.
- **[CÓDIGO]** El operador puede usar versículos configurados o consultar una integración externa de Biblia.

### 5.4 Analítica y exportación

**[CÓDIGO]** El dashboard calcula total, pendientes, en proceso, cerradas, sin asignar y proporción de usuarios registrados frente a externos dentro de un periodo. Existen detalle de KPI y exportación a Excel.

## 6. Autorización, privacidad y retención

- **[CÓDIGO]** Las pantallas internas están en el grupo autenticado y varias operaciones validan permisos sobre el rol activo.
- **[NEGOCIO VALIDADO]** Los datos de una petición externa sirven exclusivamente para solicitar y atender oración; no representan membresía.
- **[PENDIENTE]** Definir aviso de privacidad, finalidad, consentimiento, plazo de retención y procedimiento de eliminación para nombre, correo, teléfono, género, país, descripción y respuestas de externos.
- **[PENDIENTE]** Definir quién puede ver información sensible según tipo de petición, sede, ministerio e intercesor asignado.
- **[PENDIENTE]** Aclarar si ciertas peticiones requieren confidencialidad reforzada o limitación del contenido enviado por correo.

## 7. Riesgos técnicos observados

- **[RIESGO]** `verificarCorreo()` es público y devuelve existencia, identificador, nombre, correo y fotografía de un usuario coincidente. Debe revisarse el nivel de información necesario para evitar enumeración o exposición de identidad.
- **[RIESGO]** En el flujo público, `asociar_usuario_id` permite buscar un `User` y asociar la petición sin que el código revisado demuestre control de propiedad del correo. Debe validarse el flujo completo antes de considerarlo seguro.
- **[RIESGO]** `SeguimientoPeticion::peticion()` referencia `Peticio::class`, nombre que no corresponde al modelo `Peticion`. Debe confirmarse con una prueba antes de corregir.
- **[RIESGO]** Las operaciones de eliminación individual y masiva revisadas no muestran dentro de esos métodos una exigencia explícita del permiso correspondiente. La restricción podría depender de otra capa y necesita trazabilidad.
- **[RIESGO]** La consulta bíblica construye URLs externas y desactiva la verificación TLS en una de sus llamadas. Esa integración merece una revisión separada de seguridad y resiliencia.
- **[RIESGO]** Los estados se representan mediante números repetidos en controladores y componentes. Una definición canónica —por ejemplo un Enum— reduciría ambigüedad, pero cualquier cambio queda fuera de este piloto.

## 8. Mapa técnico

### Componentes principales

- Controladores: `PeticionController`, `TipoPeticionesController`, `IntercesorController`.
- Modelos: `Peticion`, `TipoPeticion`, `SeguimientoPeticion` y modelos de intercesores.
- Livewire: `Peticiones/GestionarPeticiones`.
- Vistas: `resources/views/contenido/paginas/peticiones/`, tipos de petición e intercesores.
- Exportaciones: `PeticionesExport`, `DetallePeticionesKpiExport`.
- Correo: `DefaultMail` y mensajes configurados por tipo de petición.

### Persistencia principal

- `peticiones`
- `tipo_peticiones`
- `seguimientos_peticion`
- `intercesor_tipo_peticion`

### Datos iniciales

- `PeticionSeeder.php`
- `TipoPeticionSeeder.php`
- Seeders relacionados con intercesores, cuando correspondan.

## 9. Reglas de negocio validadas y provisionales

- **[NEGOCIO VALIDADO]** Un externo puede solicitar oración sin ser creado como usuario de REDIL.
- **[NEGOCIO VALIDADO]** Si el solicitante está autenticado, la petición se registra a su nombre.
- **[CÓDIGO]** Una petición externa pública requiere correo válido, pero ese correo no crea una cuenta.
- **[NEGOCIO POR VALIDAR]** Si el correo corresponde a un usuario existente, se debe invitar al solicitante a usar o vincular su identidad autenticada.
- **[NEGOCIO POR VALIDAR]** La atención sigue el ciclo pendiente → en proceso → cerrada.
- **[NEGOCIO POR VALIDAR]** Los tipos de petición determinan mensajes, recursos bíblicos e intercesores adecuados.

## 10. Pruebas pendientes

No se encontraron archivos de prueba dedicados por nombre al dominio Peticiones.

Pruebas prioritarias:

1. Creación pública como externo sin crear `User`.
2. Creación autenticada propia y en nombre de terceros.
3. Asociación segura cuando el correo ya pertenece a un usuario.
4. reCAPTCHA, validaciones y límites de datos externos.
5. Alcance total frente a ministerial e intercesor asignado.
6. Seguimiento, transiciones de estado y envío de correo.
7. Autorización de eliminación y exportación.
8. Retención y eliminación de información externa.
9. Aislamiento entre tenants.

## 11. Enrutamiento del futuro orquestador

### Cargar este documento cuando

La solicitud mencione petición, solicitud de oración, intercesión, intercesor, respuesta, seguimiento de petición, formulario público o dashboard de peticiones.

### Desambiguación

- “Petición” aquí significa petición de oración, no una solicitud HTTP ni una tarea de consolidación.
- Cargar Usuarios cuando la petición se asocie a una cuenta, rol activo o cobertura.
- Cargar Notificaciones cuando se modifiquen correos, mensajes o entregabilidad.
- Cargar Consejería solo si se propone convertir una petición en una cita o atención formal.

## 12. Preguntas pendientes

- ¿Cuál es el consentimiento exacto del solicitante externo y cuánto tiempo se conservan sus datos?
- ¿Quién puede consultar una petición sensible y sus seguimientos?
- ¿Cómo se verifica legítimamente la asociación pública con un usuario existente?
- ¿Quién puede reasignar, cerrar, reabrir o eliminar una petición?
- ¿Los estados son globales o deben ser configurables por iglesia?
- ¿Qué ocurre si falla el correo después de guardar la petición?
- ¿Qué tipos de petición e intercesores son obligatorios inicialmente?

## 13. Fuentes

- `documentacion_funcional_personas_consolidacion.md`, resumen externo de la videollamada.
- `app/Http/Controllers/PeticionController.php`
- `app/Livewire/Peticiones/GestionarPeticiones.php`
- `app/Models/Peticion.php`
- `app/Models/TipoPeticion.php`
- `app/Models/SeguimientoPeticion.php`
- Rutas, migraciones tenant, vistas, exportaciones y seeders relacionados.
- `knowledge/tech/domains/usuarios/current-state.md`

## 14. Historial

| Fecha | Estado | Cambio | Responsable |
|---|---|---|---|
| 2026-09-02 | Borrador funcional y técnico | Separación inicial de Peticiones desde la fuente conjunta, validación de la frontera con Usuarios y contraste con el repositorio. | Responsable del producto + Codex |
