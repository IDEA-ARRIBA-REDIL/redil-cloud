---
type: delivery-playbook
id: PLAYBOOK-FEATURE-KADDO
title: Ruta de desarrollo trazable con Kaddo
status: pilot
domains:
  - usuarios
  - roles-permisos
  - grupos
updated_at: 2026-09-30
---

# Ruta de desarrollo trazable con Kaddo

## Propósito

Esta es la ruta obligatoria del piloto para que un feature empiece con contexto suficiente y termine implementado, probado, documentado y recuperable por el orquestador. Aplica inicialmente a Usuarios, Roles/Permisos y Grupos.

El Work Item es el hilo conductor: enlaza la necesidad, el dominio, los archivos afectados, la validación y el aprendizaje final. Kaddo organiza el conocimiento; el código y las pruebas demuestran el comportamiento real.

## Flujo completo (Kaddo Lifecycle v2 - 7 Etapas)

```text
1. Intención (Captured Intent)
        ↓
2. Definición (Refinement → Ready con ACs y affected_files)
        ↓
3. Planeación & Balanceo de Contexto (Lightweight / Standard / Deep)
        ↓
4. Implementación Controlada (In-Progress, acotada al alcance)
        ↓
5. Evidencia Objetiva (AC Matrix, Git diffs, tests ejecutados)
        ↓
6. Verificación & Guard (kaddo verify, kaddo guard, revisión humana)
        ↓
7. Aprendizaje & Memoria Viva (kaddo learn, knowledge/, WI completed)
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

## A. Desarrollar un feature con Kaddo Lifecycle v2 (7 Etapas)

### Etapa 1: Intención (Captured Intent)

La solicitud se origina desde una conversación, issue, ticket o video. El responsable funcional debe aportar únicamente lo fundamental:
- **¿Qué sucede actualmente?** (Comportamiento actual).
- **¿Qué debería suceder?** (Resultado esperado).
- **¿Quién usa el flujo?** (Actor o rol).
- **¿Cómo se comprueba que quedó correcto?** (Evidencia observable).
- **Evidencia adjunta:** Captura, regla de negocio o video (resumiendo en texto sus conclusiones).

Se crea el Work Item en borrador bajo `knowledge/delivery/work-items/draft/WI-XXX.md`.

### Etapa 2: Definición (Refinement → Ready)

El **Refinement Agent** analiza la intención, contrasta con las 4 capas de conocimiento (`knowledge/business/`, `product/`, `tech/`, `delivery/`) y enriquece el Work Item:
- Define **Criterios de Aceptación (ACs)** cuantitativos, unívocos y verificables.
- Mapea de forma precisa el módulo principal (`assigned_module`) y los patrones de archivos (`code:` globs).
- Asigna la sugerencia de rama Git según `.kaddo/git.yml` (`git_branch_suggestion`).
- Recomienda el nivel de balanceo de contexto (`context_level`: `lightweight | standard | deep`).
- Señala riesgos o brechas abiertas (*Knowledge Gaps*) que requieran clarificación humana.

#### Plantilla Oficial de Work Item (Gemini Flash 3.8 v2 con Git)

```yaml
---
id: WI-XXX
title: "[Título descriptivo de la tarea]"
type: "feature | bugfix | hotfix | spike | chore"
status: draft
layer: "product | tech | delivery"
assigned_module: "nombre-del-modulo"
context_level: "lightweight | standard | deep"
git_branch_suggestion: "feature/WI-XXX-slug"
code:
  - "app/Http/Controllers/..."
  - "app/Livewire/..."
  - "resources/views/livewire/..."
dependencies:
  - WI-YYY
tags:
  - tag1
---

# WI-XXX: [Título de la Tarea]

## 1. Intención & Contexto de Negocio
- **Problema/Necesidad:** [Descripción breve de por qué se requiere este cambio]
- **Valor Requerido:** [Impacto esperado en el producto o ministerio]

## 2. Alcance Técnico y Ownership (`code:` globs)
- **Módulo Principal:** `knowledge/tech/modules/[modulo]` (o dominio en `knowledge/tech/domains/[dominio]`)
- **Archivos/Componentes Objetivo:**
  - `app/...`
  - `resources/views/...`
- **Sugerencia de Rama Git:** `feature/WI-XXX-slug`
- **Riesgos & Brechas Identificadas:** [Efectos secundarios o dependencias no resueltas]

## 3. Criterios de Aceptación (Acceptance Criteria - ACs)
- [ ] **AC-01:** [Dado... Cuando... Entonces...]
- [ ] **AC-02:** [El sistema debe responder con... / validar que...]
- [ ] **AC-03:** [Evidencia requerida: Test pasando / log / verificación en pantalla]

## 4. Recomendación de Handoff & Nivel de Contexto
- **Nivel Sugerido:** `[lightweight | standard | deep]`
- **Razón del Balanceo:** [Explicación de por qué este nivel evita saturar tokens]
```

El usuario revisa y aprueba el Work Item. Con su visto bueno, el archivo se traslada físicamente a:
`knowledge/delivery/work-items/ready/WI-XXX.md`.

### Etapa 3: Planeación & Balanceo de Contexto (Context Balancing / Handoff)

Actúa como balanceador de carga de contexto para Gemini Flash. En lugar de procesar archivos masivos o código innecesario, se aplica el nivel pactado:
- **Lightweight:** Retoques visuales, copys o ajustes en un solo método (solo archivos objetivo).
- **Standard:** Features y fixes convencionales (archivos objetivo + interfaces directas + reglas de dominio).
- **Deep:** Cambios arquitectónicos transversales, multi-tenancy core o refactorizaciones globales.

Se consulta la documentación transversal de `knowledge/tech/` (`standards.md`, `security.md`, `stack.md`, `git-strategy.md`) y se ejecuta `kaddo context --wi WI-XXX` para ensamblar el paquete acotado. El agente presenta el plan técnico y espera la confirmación explícita del desarrollador (*Human-in-the-loop*).

### Etapa 4: Implementación Controlada (In-Progress)

El archivo del Work Item pasa físicamente a:
`knowledge/delivery/work-items/in-progress/WI-XXX.md`.

- El **Implementation Agent** modifica exclusivamente los archivos que caen dentro de los patrones `code:` globs del Work Item.
- **Directrices anti-truncamiento obligatorias (Sección 11 de ARCHITECTURE.md):** Prohibido el uso de comentarios de omisión (`// ... resto del código ...`), pseudocódigo o métodos simulados. Todo cambio debe ser quirúrgico, atómico y 100% funcional.

### Etapa 5: Evidencia Objetiva (Git Diff & Physical Proof)

Al finalizar la edición, el agente **NUNCA** responde únicamente *"ya terminé"*. Extrae la verdad física desde `git diff` y entrega el **Reporte de Evidencia y Verificación**:

```markdown
# REPORTE DE EVIDENCIA Y VERIFICACIÓN: WI-XXX

## 1. Estado de Ejecución & Propuesta Git
- **Work Item:** WI-XXX - [Título]
- **Estado:** Ready → In-Progress → Completed
- **Rama Sugerida:** `feature/WI-XXX-slug`
- **Propuesta de Commit:** `feat(modulo): implementa cambio X (ref WI-XXX)`

## 2. Resumen de Cambios Físicos (Git Diff)
\`\`\`diff
[Resumen de archivos modificados y líneas impactadas vía git diff --stat]
\`\`\`

## 3. Matriz de Verificación de Criterios de Aceptación (AC Matrix)
| ID AC | Descripción del Criterio | Estado | Evidencia de Cumplimiento / Test |
|-------|--------------------------|--------|-----------------------------------|
| AC-01 | [Texto del AC]           | ✅ CUMPLE | `php artisan test` passing / Método `X` en línea Y |
| AC-02 | [Texto del AC]           | ✅ CUMPLE | Verificación en interfaz / Flujo comprobado |

## 4. Resultado de Kaddo Guard & Knowledge Drift
- **Alineación con `code:` globs:** ✅ 100% coincidente / ⚠️ Archivo adicional modificado
- **Estado Kaddo Guard:** [Sin alertas de Knowledge Drift / Alerta atendida]

## 5. Captura de Aprendizaje (Kaddo Learn / Knowledge Update)
- **Lección Aprendida para la Memoria Viva:** [Detalle técnico relevante descubierto]
- **Actualización de Conocimiento:** [Instrucción para actualizar knowledge/]
```

### Etapa 6: Verificación & Guard (Verification & Guard)

- Se ejecutan pruebas automatizadas (`php artisan test --filter=...`), formateo con Pint (`vendor/bin/pint --format agent`) y comprobación en el navegador.
- Se ejecuta `kaddo verify` y `kaddo guard` para comprobar que ningún archivo fue modificado fuera de los `code:` globs (*Knowledge Drift*).

### Etapa 7: Aprendizaje & Memoria Viva (Learning Loop - Closing)

- Se ejecuta la captura de conocimiento (`kaddo learn`).
- Se actualizan los documentos correspondientes en `knowledge/tech/domains/`, `capabilities.md` o ADRs según corresponda.
- El archivo del Work Item se traslada a:
  `knowledge/delivery/work-items/completed/WI-XXX.md`.

---

## Fronteras Estrictas de Git (Agent Git Boundaries)

1. **NUNCA ejecutar comandos de Git autónomamente:** Ni la CLI de Kaddo ni los agentes de IA ejecutan `git branch`, `git checkout`, `git commit`, `git push` o `git merge`.
2. **Rol consultivo y propuesta:** El agente formula la sugerencia de rama y la propuesta de mensaje de commit siguiendo **Conventional Commits** y referenciando el WI.
3. **El desarrollador humano ejecuta:** El desarrollador humano revisa el diff en su terminal y ejecuta los comandos de Git correspondientes.

---

## Prompts ejecutables por etapa (Lifecycle v2 con Git)

| Etapa | Prompt para el Agente | Resultado que debe entregar antes de continuar |
|---|---|---|
| **1. Intención** | *"Quiero resolver `[problema]`. Actualmente `[comportamiento]`; debería `[resultado]`. Lo usa `[actor]` y lo validaré con `[evidencia]`. Crea el WI en `draft/` sin programar."* | Archivo `draft/WI-XXX.md` creado con el problema y alcance preliminar. |
| **2. Definición** | *"Actúa como Refinement Agent. Contrasta el WI-XXX con el conocimiento de Kaddo, define ACs medibles, declara patrones code: globs, sugiere rama Git según .kaddo/git.yml y recomienda context_level. Déjalo en `ready/`."* | WI completo con YAML frontmatter estructurado, ACs, sugerencia de rama y sin dudas bloqueantes. |
| **3. Planeación** | *"Genera el paquete de contexto para WI-XXX con nivel `[lightweight/standard/deep]` y presenta el plan de implementación. Espera mi aprobación."* | Plan de archivos, pasos de implementación y confirmación del alcance. |
| **4. Implementación** | *"Pasa el WI-XXX a `in-progress/` e implementa únicamente los archivos bajo code: globs, respetando las reglas anti-truncamiento de ARCHITECTURE.md."* | Código quirúrgico implementado dentro del alcance autorizado. |
| **5. Evidencia** | *"Genera el Reporte de Evidencia y Verificación oficial para WI-XXX con la matriz de ACs, git diff --stat, propuesta de commit Conventional Commits y validaciones."* | Reporte estructurado con tabla de ACs 1:1, diffs y propuesta de commit. |
| **6. Verificación** | *"Ejecuta pruebas, Pint y Kaddo Guard para comprobar que no haya deriva de código ni errores."* | Reporte de Guard limpio y pruebas pasando sin errores. |
| **7. Aprendizaje** | *"Ejecuta kaddo learn, documenta la lección aprendida en knowledge/ y traslada el WI a `completed/`."* | Conocimiento del proyecto actualizado y WI archivado como completado. |

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
