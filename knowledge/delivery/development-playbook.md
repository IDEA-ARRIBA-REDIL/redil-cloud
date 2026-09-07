---
type: delivery-playbook
id: PLAYBOOK-FEATURE-KADDO
title: Ruta de desarrollo trazable con Kaddo
status: pilot
domains:
  - usuarios
  - roles-permisos
  - grupos
updated_at: 2026-09-06
---

# Ruta de desarrollo trazable con Kaddo

## Propósito

Esta es la ruta obligatoria del piloto para que un feature empiece con contexto suficiente y termine implementado, probado, documentado y recuperable por el orquestador. Aplica inicialmente a Usuarios, Roles/Permisos y Grupos.

El Work Item es el hilo conductor: enlaza la necesidad, el dominio, los archivos afectados, la validación y el aprendizaje final. Kaddo organiza el conocimiento; el código y las pruebas demuestran el comportamiento real.

## Flujo completo

```text
Solicitud o evidencia
        ↓
Puerta de alcance y dominio
        ↓
Work Item en borrador
        ↓
Contexto + criterios de aceptación
        ↓
WI listo y plan confirmado
        ↓
Implementación limitada al alcance
        ↓
Pruebas + formato + Kaddo Guard
        ↓
Actualizar dominio, capacidades y decisiones
        ↓
Capturar aprendizaje y cerrar el WI
        ↓
Revisión y commit del equipo
```

## Roles

| Rol | Responsabilidad |
|---|---|
| Solicitante o responsable funcional | Explica el problema, el resultado esperado y valida el comportamiento. |
| Desarrollador | Contrasta contexto con código, implementa, prueba y registra hallazgos. |
| Orquestador/Codex | Selecciona el dominio, recupera contexto, controla alcance y verifica trazabilidad. |
| Kaddo | Indexa conocimiento, Work Items, ownership, preguntas y relaciones. |
| Revisor | Comprueba código, pruebas, documentación y alcance antes de integrar. |

Una persona puede asumir varios roles, pero ninguna responsabilidad debe quedar implícita.

## Conceptos para quien nunca ha usado Kaddo

| Concepto | Explicación práctica |
|---|---|
| Kaddo | Organiza el conocimiento del proyecto y permite recuperar dominios, Work Items, ownership, decisiones y preguntas. El compañero no necesita dominar su CLI: puede pedirle a Codex que ejecute y explique cada operación. |
| Work Item o WI | Archivo Markdown que funciona como expediente del trabajo. Registra problema, alcance, criterios, archivos, validación y aprendizaje; no es una tarea informal del chat. |
| `draft` | El WI está siendo definido. Todavía pueden existir preguntas y no se programa. |
| `ready` | Alcance, criterios y plan están claros y fueron revisados. Aún falta autorización para proceder. |
| `in-progress` | La implementación autorizada está en ejecución. |
| `completed` | Criterios, pruebas, validación, documentación, Guard y Learning fueron revisados. |
| Contexto Kaddo | Resumen generado desde la documentación del repositorio para que una conversación nueva recupere lo aprendido anteriormente. |
| Kaddo Guard | Comprobación de trazabilidad: avisa cuando cambia código relacionado sin actualizar su conocimiento o WI. No prueba que el software funcione y no reemplaza PHPUnit ni la validación manual. |
| Learning | Sección final del WI que distingue lo implementado, lo validado, lo aprendido y lo que sigue pendiente. |

El flujo recomendado es asistido: el compañero describe la intención y copia el prompt de cada etapa; Codex crea o modifica los archivos, ejecuta Kaddo y muestra la evidencia. Quien prefiera usar la CLI puede hacerlo, pero conocer sus comandos no es un requisito para comenzar.

## A. Desarrollar un feature de un módulo habilitado

### 1. Registrar la solicitud

La solicitud debe responder, como mínimo:

- ¿Qué sucede actualmente?
- ¿Qué debería suceder?
- ¿Quién usa el flujo?
- ¿Cómo se comprueba que quedó correcto?
- ¿Hay video, captura, regla de negocio o caso real que sirva de evidencia?

Si una respuesta cambia materialmente la solución, debe aclararse antes de programar. Los videos son evidencia de descubrimiento; sus conclusiones deben quedar resumidas en texto para que Kaddo pueda recuperarlas.

### 2. Aplicar la puerta de alcance

El orquestador identifica verbo, objeto y dominio principal usando `knowledge/tech/domains/README.md`.

- Si pertenece a Usuarios, Roles/Permisos o Grupos, continúa.
- Si es ambiguo, formula una pregunta corta.
- Si pertenece a otro módulo, se detiene y solicita autorización para incorporarlo. No se programa ese módulo usando contexto parcial.

### 3. Crear el Work Item

Todo cambio funcional debe tener un WI antes de modificar código. El WI incluye:

- identificador, título, tipo, estado y nivel de conocimiento;
- problema actual y valor esperado;
- dominios y capacidades afectadas;
- archivos o patrones de ownership esperados;
- criterios de aceptación observables;
- exclusiones explícitas;
- validaciones técnicas y funcionales;
- preguntas o riesgos todavía abiertos.

El WI comienza en `draft`. Pasa a `ready` solamente cuando no quedan dudas bloqueantes y el alcance puede probarse. Al comenzar la implementación pasa a `in-progress`.

### 4. Cargar y contrastar contexto

Antes de proponer código:

1. Recuperar el WI y su contexto desde Kaddo.
2. Leer el `current-state.md` del dominio principal.
3. Cargar solo las dependencias estrictamente necesarias.
4. Revisar modelos, controladores o componentes, rutas, vistas, migraciones y pruebas del flujo real.
5. Consultar documentación compatible con la versión instalada cuando intervenga Laravel o Livewire.
6. Registrar cualquier diferencia entre negocio documentado y código; no ocultarla ni corregirla por inferencia.

La jerarquía de evidencia definida por el enrutador de dominios decide qué fuente prevalece.

### 5. Presentar y confirmar el plan

El plan previo debe declarar:

- alcance técnico;
- archivos esperados;
- pasos de implementación;
- riesgos y efectos secundarios;
- pruebas y validación manual;
- qué queda fuera;
- condiciones que obligan a detenerse y preguntar.

No se amplía el alcance silenciosamente. Si aparece una dependencia material no prevista, primero se actualiza el WI y se confirma el nuevo alcance.

### 6. Implementar

El desarrollador sigue las convenciones existentes del repositorio y realiza solo los cambios del WI. Debe preservar modificaciones ajenas presentes en el árbol de trabajo y evitar refactorizaciones no relacionadas.

Cada regla nueva o corregida debe quedar respaldada por una prueba cuando sea técnicamente viable. Una decisión estructural duradera se registra como ADR; una regla funcional se actualiza en el documento del dominio o en capacidades.

### 7. Verificar

La verificación mínima antes del cierre incluye:

1. Pruebas específicas del feature, con rutas exitosas, fallidas y casos límite relevantes.
2. Formato de los archivos PHP modificados con Pint.
3. Validación manual cuando el comportamiento visual o el flujo de usuario no esté completamente cubierto por pruebas.
4. Kaddo Guard para detectar cambios de código sin conocimiento u ownership asociado.
5. Revisión del diff para confirmar que no entraron cambios ajenos al WI.

No se declara éxito si una prueba relevante falla. Las limitaciones aceptadas deben quedar escritas.

### 8. Actualizar el conocimiento

Antes de cerrar, revisar qué cambió realmente:

| Cambio | Documento que debe revisarse |
|---|---|
| Regla de negocio o comportamiento | `knowledge/tech/domains/<dominio>/current-state.md` |
| Capacidad visible del producto | `knowledge/product/capabilities.md` |
| Decisión arquitectónica duradera | `knowledge/tech/decisions/` |
| Ownership o archivos del dominio | Frontmatter del dominio y del WI |
| Riesgo, deuda o incertidumbre | Pregunta abierta o sección de pendientes |
| Procedimiento de entrega | Este playbook o documentación del orquestador |

Después se regenera el contexto y el grafo de Kaddo para que el nuevo conocimiento esté disponible en la siguiente tarea.

### 9. Cerrar el Work Item

El WI se mueve a `completed` únicamente cuando:

- todos los criterios de aceptación fueron comprobados;
- las pruebas requeridas pasan;
- la validación manual necesaria fue confirmada;
- Guard fue revisado y no existen omisiones sin explicar;
- la documentación afectada fue actualizada;
- la sección `Learning` distingue implementado, validado, aprendido y pendiente.

El cierre no realiza commits ni integra cambios automáticamente. El equipo revisa el diff y decide el commit o pull request.

## Prompts ejecutables por etapa

Cada prompt se envía cuando la etapa anterior ya produjo el resultado esperado. Los corchetes representan información que debe reemplazarse.

| Paso | Prompt para Codex | Resultado que debe entregar antes de continuar |
|---|---|---|
| 1. Solicitud | “Quiero resolver `[problema]`. Actualmente `[comportamiento]`; debería `[resultado]`. Lo usa `[actor]` y lo validaré con `[evidencia]`. No programes todavía: resume lo entendido y pregunta solo lo que cambie materialmente la solución.” | Resumen del problema, actor, resultado, evidencia y preguntas verdaderamente bloqueantes. |
| 2. Alcance | “Clasifica esta solicitud con el enrutador. Dime el dominio principal, dependencias mínimas y si está habilitado, existe sin contexto o aún no está implementado. No modifiques archivos.” | Clasificación explícita y decisión de continuar con feature o abrir descubrimiento. |
| 3. WI | “Crea el Work Item en borrador. Incluye problema, valor, alcance, exclusiones, criterios observables, validaciones, ownership esperado, riesgos y preguntas. Muéstrame su ruta y un resumen. No implementes.” | Archivo WI en `draft`, identificador y criterios comprensibles para el responsable. |
| 4. Contexto | “Carga el contexto de Kaddo y el `current-state.md` del dominio. Revisa el código real del flujo: rutas, controladores, modelos, Livewire, vistas, migraciones y pruebas. Separa lo comprobado, lo validado por negocio, las contradicciones y lo pendiente. No implementes.” | Informe breve respaldado por archivos, sin suposiciones ocultas. |
| 5. Plan | “Presenta el plan del WI con archivos, cambios, riesgos, efectos secundarios, pruebas, validación manual, documentación y condiciones para detenerte. Si no hay bloqueos, deja el WI listo y espera mi autorización.” | Plan revisable y WI en `ready`; todavía no debe existir implementación. |
| 6. Implementación | “Procede con el WI aprobado. Pásalo a `in-progress`, implementa únicamente el alcance, preserva cambios ajenos y añade las pruebas pertinentes. Informa si aparece una dependencia no prevista.” | Código y pruebas limitados al WI; cualquier ampliación se detiene para revisión. |
| 7. Verificación | “Verifica el feature: ejecuta pruebas específicas y casos límite, aplica Pint si cambió PHP, indica la validación manual, revisa el diff y ejecuta Kaddo Guard. No cierres si existe un fallo.” | Evidencia de pruebas, formato, revisión, Guard y cualquier limitación. |
| 8. Conocimiento | “Actualiza solamente el conocimiento afectado por lo implementado: estado del dominio, capacidades, ADR si corresponde, ownership, riesgos o pendientes. Regenera el contexto y el grafo de Kaddo y enumera los documentos modificados.” | Documentación alineada con el comportamiento real y nuevo contexto recuperable. |
| 9. Cierre | “Audita el WI contra su Definition of Done. Registra Learning separando implementado, validado, aprendido y pendiente. Ciérralo solo si criterios, pruebas, validación, documentación y Guard están completos. No hagas commit salvo que te lo solicite.” | WI en `completed`, resumen final, pruebas ejecutadas, archivos modificados y pendientes explícitos. |

## B. Ejemplo: feature del módulo de Usuarios

Solicitud: “Permitir que un administrador cambie el rol activo desde el perfil”.

1. El orquestador selecciona Usuarios y Roles/Permisos.
2. Se crea un WI con el comportamiento actual, el esperado, quién puede ejecutarlo y qué permisos intervienen.
3. Se carga el contexto de Usuarios y se revisan `User`, manejo de rol activo, autorización, formulario y pruebas existentes.
4. El plan enumera los archivos previstos, validaciones y casos de prueba; el responsable confirma.
5. Se implementa sin tocar Grupos u otros módulos salvo que el WI documente esa dependencia.
6. Se prueba autorización, cambio exitoso, entrada inválida y preservación de roles no activos.
7. Se actualiza `usuarios/current-state.md` si cambió la regla, se ejecuta Guard y se registra el aprendizaje.
8. El WI se cierra y el equipo revisa el diff antes de integrar.

## C. Incorporar un módulo nuevo a partir de videos y documentación

Un módulo nuevo no se habilita solo porque exista código o un video. Primero debe pasar por una etapa de descubrimiento:

1. Reunir videos, documentos, capturas, flujos conocidos y responsables funcionales.
2. Crear un WI de descubrimiento separado de cualquier WI de implementación.
3. Convertir la evidencia en texto: propósito, actores, vocabulario, reglas, estados, permisos y casos excepcionales.
4. Crear `knowledge/tech/domains/<dominio>/current-state.md` con estado de borrador y fuentes identificadas.
5. Inventariar rutas, modelos, componentes, controladores, vistas, tablas, migraciones y pruebas existentes.
6. Declarar ownership, dependencias con otros dominios, términos ambiguos, riesgos y preguntas abiertas.
7. Añadir o actualizar las capacidades del producto.
8. Contrastar la documentación con código, pruebas y una validación funcional; clasificar contradicciones sin resolverlas automáticamente.
9. Ejecutar Kaddo para regenerar contexto, grafo y Guard.
10. Obtener autorización para agregar el dominio al enrutador.

Solo después se crean WIs de implementación. Así se evita programar desde una interpretación incompleta del video.

### C.1 Distinguir qué significa “módulo nuevo”

Antes de trabajar se debe identificar cuál de estas situaciones aplica:

| Situación | Qué significa | Primer trabajo |
|---|---|---|
| Módulo habilitado | Ya está documentado y registrado en el enrutador, como Usuarios o Grupos en este piloto. | Crear un WI del feature y cargar su contexto. |
| Módulo existente no habilitado | El software ya contiene código y pantallas, pero el orquestador todavía no dispone de contexto validado. | Crear un WI de descubrimiento, documentar y solicitar su incorporación al enrutador. |
| Funcionalidad no implementada | La necesidad todavía no existe en el producto o requiere una modificación real. | Definir primero el resultado y sus criterios; documentar el dominio si aún no está habilitado y después crear un WI de implementación separado. |

“No está en el piloto” no significa necesariamente “no existe en REDIL”. Significa que el agente no debe programarlo todavía usando contexto incompleto.

### C.2 Paquete para iniciar una conversación nueva

El compañero no necesita copiar una conversación anterior ni conocer todos los archivos. Debe entregar lo que ya tenga y declarar qué desea conseguir. El paquete recomendado es:

1. **Solicitud:** comportamiento actual, resultado esperado, actor y forma de validación.
2. **Arquitectura general:** `ARCHITECTURE.md`, si el cambio depende de estructura transversal, multi-tenancy o integraciones.
3. **Guía del agente del módulo:** por ejemplo `.agent/workflows/agenteUsuarios.md` o `agenteGrupos.md`. Es un punto de entrada histórico y operativo, no una verdad que se ejecute ciegamente.
4. **Documentación previa:** archivos como `_docs_agente/modulos/<modulo>.md`, manuales o notas de negocio.
5. **Evidencia visual:** videos, capturas y explicación textual de reglas que la interfaz no muestra por sí sola.
6. **Puntos técnicos conocidos:** modelos, controladores, componentes Livewire, vistas, rutas, migraciones, seeders y pruebas relacionados. Si el compañero no sabe cuáles son, debe decirlo; el agente los localiza en el repositorio.

No es obligatorio reunir todo antes de comenzar. La falta de un archivo técnico se resuelve mediante exploración; una decisión funcional que cambie la solución sí debe preguntarse al responsable.

### C.3 Cómo interpretar los archivos entregados

| Insumo | Qué aporta | Cómo debe tratarse |
|---|---|---|
| `AGENTS.md` | Convenciones generales y reglas obligatorias del repositorio. | Se aplica como instrucción del proyecto. |
| `.agent/workflows/agenteXxx.md` | Orden de lectura, conceptos y pistas históricas del módulo. | Se usa como punto de partida y se contrasta con el código actual. |
| `ARCHITECTURE.md` | Mapa transversal del sistema y sus tecnologías. | Orienta; sus versiones y relaciones se verifican contra la instalación y el código. |
| `_docs_agente/` u otros `.md` | Conocimiento previo, decisiones o explicaciones funcionales. | Se clasifica como documentación histórica o revisada según su evidencia. |
| Modelo Eloquent | Datos, relaciones y lógica de dominio concentrada en una entidad. | Se revisa con migraciones, consultas y pruebas; el modelo por sí solo no demuestra toda la interfaz. |
| Controlador | Entrada web, autorización, validaciones y coordinación del flujo. | Se revisa junto con rutas, policies, requests, modelos y respuestas. |
| Componente Livewire | Estado y acciones reactivas del servidor para una pantalla. | Se revisa junto con su vista Blade, eventos, autorización y pruebas. |
| Vista Blade | Campos, navegación y acciones que ve la persona. | Muestra experiencia esperada, pero ocultar un botón no sustituye autorización del servidor. |
| Rutas y middleware | Acceso, nombres de operación y capas iniciales de protección. | Se contrastan con autorización dentro del controlador o componente. |
| Migración o esquema | Estructura persistida, restricciones y pivotes. | Ayuda a confirmar relaciones; no define por sí sola la regla funcional. |
| Seeder | Configuración y datos iniciales usados por el sistema. | Es evidencia técnica, no una regla universal sin validación. |
| Prueba | Comportamiento reproducible y casos cubiertos. | Tiene prioridad cuando representa el código ejecutado actual. |

### C.4 Videos, audio y capturas como evidencia

- Preferir video MP4 con H.264 y audio AAC por compatibilidad. MOV también puede servir, pero puede requerir conversión.
- Un video con voz es más valioso cuando explica por qué existe una regla, qué permiso interviene o qué excepción debe respetarse. No es necesario narrar cada clic.
- Para recorridos largos, dividir por flujo o aportar capítulos y minutos. Como recomendación práctica, segmentos de 5 a 15 minutos son fáciles de revisar; no es un límite técnico rígido.
- Comenzar cada grabación diciendo módulo, actor, objetivo, resultado esperado y datos especiales que no deben generalizarse.
- Mostrar camino exitoso, validaciones, estado vacío, error relevante y diferencias por rol cuando apliquen.
- Nombrar archivos por acción: `USUARIOS-CREAR.mp4`, `GRUPOS-ASIGNAR-INTEGRANTE.png` o `ROLES-CAMBIO-ACTIVO.png`.
- Las capturas deben incluir una vista completa para orientación y acercamientos cuando un texto o estado no sea legible.
- Evitar o anonimizar nombres, correos, teléfonos, identificaciones, direcciones, secretos, tokens y códigos QR. Si aparecen, la documentación conserva solo una descripción.

La interfaz permite observar qué ocurre; la voz o explicación del responsable aporta intención; el código y las pruebas permiten comprobar cómo se ejecuta. Ninguna de esas fuentes debe tratarse aisladamente como verdad completa.

### C.5 Resultado documental esperado del descubrimiento

Al finalizar, el módulo debe dejar:

- una nota textual de evidencia para los videos o capturas;
- `knowledge/tech/domains/<dominio>/current-state.md` con propósito, vocabulario, reglas, flujos, componentes, relaciones, riesgos y pendientes;
- capacidades actualizadas en `knowledge/product/capabilities.md`;
- un WI con fuentes, ownership, validación y Learning;
- el dominio registrado en el enrutador solamente cuando exista autorización;
- contexto y grafo regenerados, además de Kaddo Guard revisado.

El ejemplo aplicado durante este piloto fue: comenzar con `ARCHITECTURE.md` y `agenteGrupos.md`, explicar la relación con Usuarios, validar dudas con el responsable, analizar videos, complementar con capturas, contrastar con modelos/controladores/Livewire/vistas/pruebas y cerrar cada aprendizaje dentro de Kaddo.

## D. Lista corta para revisión del equipo

Antes de aprobar un desarrollo, el revisor responde:

- [ ] ¿Existe un WI y coincide con el cambio real?
- [ ] ¿El dominio y las dependencias son correctos?
- [ ] ¿Los criterios de aceptación tienen evidencia?
- [ ] ¿Las pruebas relevantes pasan?
- [ ] ¿Se realizó la validación manual necesaria?
- [ ] ¿Guard fue revisado?
- [ ] ¿Cambió una regla, capacidad, decisión u ownership y se documentó?
- [ ] ¿El aprendizaje y los pendientes quedaron registrados?
- [ ] ¿El diff contiene únicamente el alcance autorizado?

Si alguna respuesta es “no”, el WI no debe cerrarse o la excepción debe quedar explícitamente aceptada.

## E. Frases recomendadas para trabajar con el orquestador

### Empezar

> Quiero implementar este feature. Primero identifica el dominio, revisa Kaddo y crea o refina el Work Item. No programes hasta presentarme el alcance, los criterios de aceptación y el plan.

### Implementar

> Procede con el WI aprobado. Limita los cambios al alcance, ejecuta las pruebas específicas y actualiza el conocimiento afectado.

### Cerrar

> Valida el WI contra su Definition of Done, ejecuta Guard, registra el aprendizaje y ciérralo solo si no quedan fallos ni documentación pendiente.

### Incorporar otro módulo

> Quiero incorporar el módulo `<nombre>` al orquestador. Usa estos videos y documentos como evidencia de descubrimiento, contrástalos con el código y crea su contexto de dominio antes de proponer features.

### Empezar en una conversación nueva sin contexto previo

> Quiero trabajar en el módulo `<nombre>`. Mi objetivo es `<resultado esperado>` y actualmente ocurre `<comportamiento actual>`. Lo usa `<actor>` y consideraré correcto el resultado cuando `<validación observable>`. Adjunto `ARCHITECTURE.md`, la guía `.agent/workflows/agente<Nombre>.md`, la documentación previa disponible y estas evidencias `<videos/capturas/notas>`. Primero distingue si el módulo ya está habilitado, si existe en el software pero falta incorporarlo al orquestador, o si la funcionalidad aún no está implementada. Contrasta la documentación con modelos, controladores, Livewire, vistas, rutas, migraciones y pruebas. Crea un WI de descubrimiento cuando falte contexto y no programes hasta presentarme hallazgos, preguntas, alcance y plan.

## Presentación y validación con el equipo

La ruta dispone de una presentación derivada en `knowledge/delivery/team-development-guide.html` y de una ficha de observación en `knowledge/delivery/pilot-validation-exercise.md`. Este documento Markdown continúa siendo la fuente versionada y consultable por Kaddo.

La preparación documental está completa. Permanecen pendientes la elección de un feature real pequeño, su ejecución por un compañero sin ayuda material y los ajustes de lenguaje que resulten de esa observación.
