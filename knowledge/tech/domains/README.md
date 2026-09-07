---
id: DOMAIN-ROUTER
title: Índice y enrutador de dominios
artifact_type: domain-router
status: pilot
language: es
last_reviewed: 2026-09-04
---

# Índice y enrutador de dominios

> Punto de entrada provisional para agentes y desarrolladores. Este archivo selecciona contexto documental; no concede permisos, no ejecuta herramientas y no sustituye la lectura del código actual.

## Alcance activo del piloto

El orquestador solo puede seleccionar **Usuarios**, **Roles/Permisos** y **Grupos**. Roles/Permisos se documenta dentro de Usuarios y se carga junto al dominio funcional cuya operación se autoriza. Consolidación, Peticiones, Actividades, Escuelas y cualquier otro dominio permanecen fuera del alcance, aunque existan documentos previos en el repositorio.

Ante una solicitud fuera de este alcance, el orquestador debe detener el enrutamiento, informar que el dominio no está habilitado y solicitar autorización antes de ampliar el piloto.

## 1. Objetivo

Ante una solicitud de desarrollo o consulta:

1. Identificar la intención y el dominio principal.
2. Cargar un solo `current-state.md` principal.
3. Añadir únicamente los dominios relacionados que intervengan realmente.
4. Abrir después los archivos de código del flujo concreto.
5. Verificar Laravel con Boost y comprobar el comportamiento con pruebas.

## 2. Registro disponible

| Dominio | Estado | Disparadores principales | Documento | Dependencias frecuentes |
|---|---|---|---|---|
| Usuarios | Borrador funcional parcial | usuario, persona, asistente, perfil, familia, menor, rol, permiso, tipo de usuario, formulario de usuario | `usuarios/current-state.md` | grupos, reuniones, consolidación, peticiones, escuelas, consejería |
| Consolidación | Borrador funcional parcial | consolidación, Conecta, seguimiento, tarea de consolidación, cosecha, deserción, zona de pastoreo | `consolidacion/current-state.md` | usuarios, grupos, sedes, crecimiento, escuelas, consejería |
| Peticiones | Borrador funcional parcial | petición de oración, solicitud de oración, intercesión, intercesor, seguimiento de petición | `peticiones/current-state.md` | usuarios, notificaciones, sedes |
| Grupos | Borrador funcional/técnico parcial | grupo, célula, Mi Grupo, encargado, reporte de grupo, autoasistencia, cobertura, gráfico del ministerio, mapa de grupos, evidencia de grupo | `grupos/current-state.md` | usuarios, sedes, finanzas, crecimiento, reuniones |

## 3. Reglas de selección

### Usuarios como dominio principal

Elegir Usuarios cuando el cambio modifique identidad, credenciales, perfil, tipo, roles, permisos, formularios personales o relaciones familiares.

Ejemplos:

- “Un líder no puede editar la información congregacional de una persona”.
- “Necesito agregar un campo al perfil del usuario”.
- “El rol de maestro queda activo al iniciar sesión”.

### Consolidación como dominio principal

Elegir Consolidación cuando el objetivo sea seleccionar personas para seguimiento, asignar tareas, cambiar estados, medir desempeño o limitar operación por zona.

Ejemplos:

- “Las personas sin tareas no aparecen en Consolidación”.
- “El consolidador puede abrir usuarios de otra zona”.
- “Cambiar la fórmula de cosecha efectiva”.

Usuarios se carga como dependencia si intervienen `User`, `TipoUsuario`, rol activo o cobertura.

### Peticiones como dominio principal

Elegir Peticiones cuando el objetivo sea crear, asignar, responder, cerrar, eliminar o reportar solicitudes de oración.

Ejemplos:

- “El formulario público no envía la petición”.
- “Asignar automáticamente un intercesor”.
- “Restringir quién puede consultar una petición sensible”.

Usuarios se carga como dependencia cuando la petición pertenece a una cuenta o la autorización depende del rol activo.

### Grupos como dominio principal

Elegir Grupos cuando el objetivo sea administrar una célula o grupo, sus integrantes o encargados, reportar su actividad, registrar asistencia, gestionar ofrendas del reporte, consultar cobertura ministerial, mapas o evidencias.

Ejemplos:

- “El líder no puede finalizar el reporte de su célula”.
- “El enlace de autoasistencia sigue aceptando registros vencidos”.
- “Mostrar cuatro niveles en el gráfico del ministerio”.

Usuarios se carga si intervienen integrantes, encargados, menores, roles o credenciales. Finanzas se añade cuando se modifican conciliación, moneda, ofrendas o ingresos.

## 4. Desambiguación

| Término ambiguo | Regla |
|---|---|
| asistente | En identidad equivale a usuario/persona; en reuniones puede significar participante o registro de asistencia. |
| seguimiento | Abrir Consolidación si son tareas operativas; Peticiones si son respuestas a una solicitud; Crecimiento si son pasos o hitos. |
| petición | Abrir Peticiones solo si se trata de oración; una petición HTTP no es un dominio de negocio. |
| tarea | Abrir Consolidación cuando es acompañamiento de personas; no asumir que toda tarea del sistema pertenece a ese módulo. |
| externo | En Usuarios no existe una cuenta externa incompleta; en Peticiones sí puede existir un solicitante externo almacenado únicamente dentro de la petición. |
| rol | Abrir Usuarios y también el dominio funcional cuya capacidad está siendo autorizada. |
| reunión | Abrir Reuniones si es una reunión general; abrir Grupos si se habla de la reunión o reporte de una célula. |
| asistencia | Identificar primero si corresponde a grupo, reunión, actividad o clase; no cargar todos los dominios. |
| cobertura | Abrir Grupos para jerarquía ministerial; Sedes para alcance territorial administrativo. |
| ofrenda | Abrir Finanzas por defecto; usar Grupos como principal si nace del reporte de grupo. |

Si una frase sigue admitiendo dos interpretaciones materiales, el agente debe formular una pregunta corta antes de proponer cambios.

## 5. Protocolo de carga

```text
Solicitud
  ↓
identificar verbo y objeto del cambio
  ↓
seleccionar dominio principal
  ↓
leer su current-state.md
  ↓
añadir dependencias estrictamente necesarias
  ↓
localizar rutas, controladores, modelos, vistas, migraciones y pruebas
  ↓
contrastar documentación con código actual
  ↓
consultar Boost para APIs y convenciones Laravel
  ↓
proponer, implementar y verificar según autorización del usuario
```

## 6. Jerarquía de evidencia

Cuando dos fuentes discrepen:

1. Comportamiento probado automáticamente y código ejecutado actualmente.
2. Regla de negocio validada y fechada.
3. Código actual sin cobertura de prueba.
4. Documentación técnica revisada.
5. Resumen de reunión pendiente de validación.
6. Documentación histórica.
7. Inferencia de IA.

Una diferencia entre código y negocio debe registrarse; no se debe modificar automáticamente uno para hacerlo coincidir con el otro.

## 7. Responsabilidad de las herramientas

| Componente | Responsabilidad |
|---|---|
| Este índice | Elegir el contexto de negocio mínimo. |
| `current-state.md` | Explicar propósito, reglas, componentes, integraciones y dudas del dominio. |
| Código y pruebas | Mostrar y comprobar el comportamiento real. |
| Kaddo | Indexar y recuperar conocimiento propio del proyecto cuando su configuración esté validada. |
| Laravel Boost | Inspeccionar la aplicación y consultar documentación compatible con las versiones instaladas. |
| Git | Versionar, revisar y auditar cambios de código y documentación. |

## 8. Regla para añadir un dominio

Un nuevo módulo entra al registro cuando tenga:

- Un identificador único.
- Propósito y límites.
- Alias y términos ambiguos.
- Reglas clasificadas por evidencia.
- Inventario técnico inicial.
- Relaciones con otros dominios.
- Riesgos y preguntas abiertas.
- Responsable de revisión pendiente o asignado.

No es necesario terminar toda la documentación antes de registrarlo; debe quedar claramente marcado como borrador.

## 9. Próximos dominios sugeridos

1. Reuniones, por asistencia, reservas y seguimiento de actividad.
2. Iglesia infantil, por usuarios menores, responsables, grupos y reuniones.
3. Finanzas, por su acoplamiento con ofrendas e ingresos originados en reportes de grupo.

## 10. Limitaciones del piloto

- Solo existen cuatro dominios registrados.
- Kaddo todavía no representa correctamente el stack Laravel ni las migraciones tenant.
- El conocimiento nuevo debe añadirse explícitamente al control de versiones; los archivos derivados de `.kaddo/` permanecen ignorados.
- Guard detecta omisiones de trazabilidad, pero el equipo todavía debe ejecutarlo y revisar su resultado dentro del cierre del WI.
- Las matrices completas de permisos y pruebas aún están pendientes.
