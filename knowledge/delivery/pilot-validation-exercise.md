---
type: delivery-validation
id: PILOT-VALIDATION-EXERCISE
title: Ejercicio de validación del flujo de desarrollo
status: ready-for-human-run
domains:
  - usuarios
  - roles-permisos
  - grupos
updated_at: 2026-09-06
---

# Ejercicio de validación del piloto

## Objetivo

Comprobar con un feature real pequeño que un compañero puede seguir la ruta de desarrollo sin explicaciones adicionales y que el resultado queda implementado, probado, documentado y relacionado con Kaddo.

Este documento registra la prueba. La ruta normativa continúa en `development-playbook.md` y su presentación navegable en `team-development-guide.html`.

## 1. Elegir el feature

El responsable del producto debe proponer una necesidad real de Usuarios, Roles/Permisos o Grupos que:

- tenga un solo resultado observable;
- pueda completarse idealmente en una sesión corta;
- no requiera migraciones masivas, integraciones nuevas ni decisiones arquitectónicas;
- incluya al menos un caso exitoso y uno de rechazo o borde;
- no corresponda a USR-03, USR-04, USR-05 o USR-06, que siguen aplazados.

Ejemplos de tamaño —no son autorización para implementarlos—:

- agregar un filtro pequeño al listado de personas;
- ajustar una validación puntual al asignar un integrante;
- mejorar un mensaje o estado vacío de una pantalla del piloto;
- restringir una acción concreta mediante un permiso ya existente.

## 2. Ficha del ejercicio

| Campo | Respuesta |
|---|---|
| Fecha | Por completar |
| Compañero que ejecuta | Por completar |
| Revisor | Por completar |
| Feature elegido | Por completar |
| Dominio principal | Por completar |
| Dependencias estrictas | Por completar |
| Comportamiento actual | Por completar |
| Comportamiento esperado | Por completar |
| Usuario que usa el flujo | Por completar |
| Evidencia funcional | Por completar |
| Tiempo de inicio y cierre | Por completar |

### Paquete que recibe el compañero

- [ ] Descripción del feature con comportamiento actual y esperado.
- [ ] Actor que usa el flujo y forma observable de validarlo.
- [ ] Enlace a `team-development-guide.html`.
- [ ] `ARCHITECTURE.md` cuando el cambio necesite contexto transversal.
- [ ] Archivo `.agent/workflows/agenteXxx.md` y documentación previa disponibles.
- [ ] Videos, capturas o notas funcionales pertinentes, anonimizados cuando sea necesario.

No se le debe exigir que identifique previamente todos los modelos, controladores, componentes Livewire, vistas o pruebas. Parte del ejercicio es comprobar que el procedimiento le permite localizarlos y contrastarlos.

## 3. Instrucción inicial que recibe el compañero

> Quiero implementar este feature. Primero identifica el dominio, revisa Kaddo y crea o refina el Work Item. No programes hasta presentarme el alcance, los criterios de aceptación y el plan. Durante el trabajo, limita los cambios al alcance, ejecuta las pruebas específicas y actualiza el conocimiento afectado. Cierra el WI solo después de revisar su Definition of Done y Kaddo Guard.

El observador entrega únicamente esta instrucción, la descripción del feature y un enlace a la guía HTML. No explica verbalmente el proceso salvo que exista un bloqueo; cada ayuda adicional se registra como una mejora necesaria de la guía.

## 4. Observación

- [ ] Identificó correctamente el dominio principal.
- [ ] Rechazó o consultó una ampliación fuera del piloto.
- [ ] Creó el WI antes de modificar código.
- [ ] Definió criterios de aceptación observables.
- [ ] Consultó el contexto de Kaddo y el `current-state.md` pertinente.
- [ ] Contrastó documentación con código y pruebas actuales.
- [ ] Presentó el plan antes de implementar.
- [ ] Preservó cambios ajenos del árbol de trabajo.
- [ ] Ejecutó pruebas específicas, casos límite y Pint cuando correspondía.
- [ ] Realizó o solicitó la validación manual necesaria.
- [ ] Actualizó reglas, capacidades, decisiones o pendientes afectados.
- [ ] Ejecutó y revisó Kaddo Guard.
- [ ] Registró aprendizaje y cerró el WI correctamente.
- [ ] El diff final contiene solamente el alcance autorizado.

## 5. Registro de dificultades

Por cada duda o ayuda requerida, completar:

| Momento | Duda o bloqueo | Ayuda proporcionada | Cambio propuesto a la guía |
|---|---|---|---|
| Por completar | Por completar | Por completar | Por completar |

No se considera un fallo de la persona. Cada duda revela lenguaje, contexto o una decisión que la documentación todavía debe aclarar.

## 6. Criterio de resultado

La prueba se considera satisfactoria cuando:

- el feature cumple sus criterios y pruebas;
- no hubo cambios fuera del alcance;
- la documentación y Kaddo reflejan el resultado;
- el compañero pudo completar la ruta sin explicación verbal material;
- cualquier excepción o ayuda quedó registrada.

## 7. Estado actual

- Preparación documental: **completa**.
- Feature real: **pendiente de elección por el responsable del producto**.
- Ejecución por un compañero: **pendiente**.
- Ajuste del lenguaje según observación: **pendiente de los resultados del ejercicio**.
