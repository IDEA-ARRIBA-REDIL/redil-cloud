---
type: architecture
id: ARCH-ORQUESTADOR-KADDO
title: Orquestador Kaddo para Usuarios, Roles y Grupos
status: pilot
domains:
  - usuarios
  - roles-permisos
  - grupos
updated_at: 2026-09-30
---

# Orquestador Kaddo del piloto (Lifecycle v2)

## Objetivo

Seleccionar el contexto mínimo necesario para solicitudes de Usuarios, Roles/Permisos y Grupos. Kaddo entrega conocimiento estructurado; el agente contrasta ese conocimiento con el código y las pruebas antes de responder o implementar.

## Enrutamiento

| Intención | Dominio principal | Dependencia mínima |
|---|---|---|
| Perfil, identidad o tipo de usuario | Usuarios | Roles si cambia autorización |
| Rol activo, rol o permiso | Usuarios / Roles | Dominio funcional autorizado |
| Integrante, encargado, servidor o exclusión | Grupos | Usuarios / Roles |
| Cobertura o jerarquía ministerial | Grupos | Usuarios / Roles |
| Alta por invitación, licencias o seguridad del panel central (WI-017 autorizado) | AdminGlobal | Workflow AdminGlobal, WI-017 y plan de despliegue; Usuarios/Grupos solo para las cuatro cuentas iniciales |
| Cualquier otro módulo | Fuera del piloto | Detener y solicitar autorización |

## Secuencia obligatoria (Kaddo Lifecycle v2 - 7 Etapas)

1. **Intención (Captured Intent):**
   - Identificar verbo, objeto y alcance de la solicitud.
   - Aplicar la puerta de alcance del piloto (Usuarios, Roles, Grupos o excepciones autorizadas como WI-017).
   - Crear el Work Item en borrador (`knowledge/delivery/work-items/draft/WI-XXX.md`).

2. **Definición (Refinement → Ready):**
   - El Refinement Agent contrasta la solicitud con `knowledge/` y el código existente.
   - Define Criterios de Aceptación (ACs) cuantitativos y verificables.
   - Mapea explícitamente los patrones de ownership con `code:` globs y el módulo asignado.
   - Asigna la sugerencia de rama Git según `.kaddo/git.yml` (`git_branch_suggestion: "{type}/WI-XXX-{slug}"`).
   - Identifica riesgos o brechas de conocimiento (*Knowledge Gaps*) y sugiere el nivel de contexto.
   - Requiere revisión y aprobación explícita humana (*Human-in-the-Loop*) para mover el WI a `ready/`.

3. **Planeación y Balanceo de Contexto (Context Balancing / Handoff):**
   - Seleccionar el nivel de contexto estricto para evitar saturar tokens en Gemini Flash:
     * **Lightweight:** Retoques visuales, textos o ajustes puntuales (archivos objetivo).
     * **Standard:** Features y fixes convencionales (archivos objetivo + interfaces directas + reglas del módulo).
     * **Deep:** Cambios arquitectónicos transversales, multi-tenancy core o migraciones globales.
   - Consultar los estándares globales transversales en `knowledge/tech/standards.md`, `security.md` y `stack.md`.
   - Generar el paquete con `kaddo context` y confirmar el plan de implementación con el desarrollador humano.

4. **Implementación Controlada:**
   - Mover el Work Item a `in-progress/`.
   - Modificar quirúrgicamente solo los archivos autorizados bajo los patrones `code:` globs del Work Item.
   - Respetar directrices de integridad de código anti-truncado (sin `...` ni omitir lógica previa).

5. **Evidencia Objetiva (Verdad Física en Git):**
   - Prohibido finalizar únicamente con *"Ya terminé"*.
   - Generar el **Reporte de Evidencia y Verificación**:
     * Estado de ejecución y sugerencia de rama.
     * Propuesta formal de mensaje de commit bajo **Conventional Commits** (ej. `feat(scope): mensaje (ref WI-XXX)`).
     * Resumen físico de archivos modificados/creados extraídos directamente del estado de `git diff` (`git diff --stat`).
     * Matriz de Criterios de Aceptación (ACs) 1:1 con estado y pruebas de cumplimiento.
     * Pruebas ejecutadas y resultados (PHPUnit/Pest, linter, inspección en navegador).
     * Desviaciones justificadas y Knowledge Gaps resueltos.

6. **Verificación y Guard:**
   - Ejecutar `kaddo verify` y `kaddo guard` para comprobar que no existan modificaciones no autorizadas fuera del alcance (*Knowledge Drift* entre `git diff` y `code:` globs).
   - Validar el comportamiento con el desarrollador humano.

7. **Aprendizaje y Memoria Viva (Learning Loop):**
   - Ejecutar la captura de conocimiento (`kaddo learn`).
   - Registrar lecciones aprendidas y actualizar `knowledge/` (módulos, decisiones, ADRs).
   - Mover el Work Item a `completed/`.

## Fronteras y Límites Estrictos de Git (Agent Git Boundaries)

Para salvaguardar la integridad del repositorio y el control del desarrollador:

1. **NUNCA ejecutar comandos de Git autónomamente:** Ni la CLI de Kaddo ni los agentes de IA ejecutan `git branch`, `git checkout`, `git commit`, `git push` o `git merge`.
2. **Rol consultivo:** El agente solo sugiere el nombre de rama y propone la estructura exacta del commit respetando Conventional Commits y referenciando el WI.
3. **Ejecución humana:** El desarrollador humano revisa y ejecuta los comandos reales de Git en su entorno local.
4. **Referencia oficial:** Regido por `.kaddo/git.yml` y documentado en `knowledge/tech/git-strategy.md`.

El ciclo detallado de creación, implementación, validación y cierre de Work Items está definido en `knowledge/delivery/development-playbook.md`.

La guía navegable para el equipo está en `knowledge/delivery/team-development-guide.html`; es una presentación derivada y no reemplaza al playbook. El ejercicio de validación humana se registra en `knowledge/delivery/pilot-validation-exercise.md`.

## Contexto operativo transversal: Laravel Cloud

Para planificación del lanzamiento 2027, convivencia con cPanel y migraciones de iglesias, cargar primero `knowledge/delivery/DESPLIEGUEREDILCLOUD.md`. Distingue staging propuesto en Cloud, producción futura y AWS de Manantial administrado por el cliente; no atribuir los hallazgos de un entorno a otro. Es un plan pendiente de implementación, no autorización para operar infraestructura.

Si un WI del alcance autorizado afecta migraciones, persistencia de archivos, caché/locks, colas, tareas programadas, configuración de ambiente o despliegue, cargar `knowledge/delivery/laravel-cloud-deployment-runbook.md` antes de implementar. Añadir únicamente el WI operativo pertinente: WI-012 (despliegues), WI-013 (colas/scheduler) o WI-014 (observabilidad/recuperación). No cargar toda la documentación de Cloud para un cambio exclusivamente visual.

Registrar en el WI el impacto operativo, evidencia disponible y verificaciones pendientes. Distinguir siempre:

- **Capacidad de la plataforma:** documentación oficial consultada, URL y fecha; no demuestra que REDIL la tenga habilitada.
- **Estado observado del ambiente:** ambiente, fecha y evidencia sin secretos; no presentar un inventario histórico como comprobación actual.
- **Pendiente de verificar o decidir:** configuración efectiva, disponibilidad en la cuenta, costos, responsables y aprobación.

El inventario del 2026-09-06 es histórico. Antes de una decisión operativa hay que reconfirmar lo relevante, especialmente backups, previews, colas y scheduler. No declarar un control resuelto solo porque Cloud lo ofrece o porque pasan pruebas SQLite: estas no acreditan aislamiento entre schemas PostgreSQL.

Esta ruta selecciona conocimiento; no amplía dominios funcionales ni autoriza activar recursos, cambiar producción, desplegar o actualizar Kaddo. Las excepciones funcionales aprobadas, como la dependencia de Consolidación de WI-016, se consultan en `knowledge/tech/domains/README.md`.

## Decisiones congeladas

Excepción autorizada WI-017: cargar `.agent/workflows/agenteAdminGlobal.md` y `knowledge/delivery/work-items/in-progress/WI-017-endurecer-alta-por-invitaci-n-y-administraci-n-glo.md`. No extender esta autorización a otros módulos ni operar infraestructura. Preservar UserSeeder/TenantDatabaseSeeder: el nuevo seeder es exclusivo del formulario marcado por el servidor. Las validaciones PostgreSQL/SMTP pendientes impiden considerar el alta lista para producción.

- El piloto funcional Usuarios–Grupos se considera aprobado por el responsable del producto.
- Solo el rol activo aporta permisos.
- `TipoUsuario` y `Role` son conceptos distintos.
- USR-03, USR-04, USR-05 y USR-06 están aplazados.
- No se enrutan solicitudes de Actividades ni de otros módulos durante este piloto.

## Criterios de aceptación

- Una solicitud de rol activo carga Usuarios/Permisos.
- Una solicitud de encargado carga Grupos y Usuarios/Permisos.
- Una solicitud de Actividades se rechaza como fuera de alcance.
- El contexto no contiene valores de secretos ni datos personales de usuarios.
- Guard relaciona cambios en los archivos declarados con su documento de dominio.
- Un WI con impacto operativo carga el runbook y distingue capacidades de Cloud, observaciones fechadas y pendientes; un cambio solo documental no dispara operaciones de infraestructura.
