# Revisión técnica para Grupos y Reportes de Grupo

**Fecha del análisis:** 4 de septiembre de 2026

**Estado:** Diagnóstico pendiente de reproducción, decisión técnica e implementación

**Destinatario:** Equipo de desarrollo de REDIL Cloud

**Documento funcional relacionado:** `knowledge/tech/domains/grupos/current-state.md`

> Este documento no modifica la aplicación ni afirma que todos los hallazgos sean vulnerabilidades explotables. El desarrollador debe reproducir cada punto, confirmar su alcance y agregar pruebas antes o junto con la corrección.

## 1. Reglas de negocio que deben preservarse

1. La autoasistencia solo admite usuarios activos que ya pertenezcan al grupo; nunca crea usuarios.
2. El enlace de autoasistencia vence según la configuración del tipo de grupo y no acepta registros cuando el reporte está finalizado.
3. Un reporte no realizado exige motivo y queda aprobado automáticamente.
4. Un reporte aprobado se corrige mediante el procedimiento administrativo aprobado → desaprobado/corregido.
5. Consultas y operaciones dependen de los permisos del rol activo y del alcance autorizado.
6. Cada reporte monetario usa una sola moneda y jamás mezcla monedas.
7. El gráfico ministerial debe trabajar por ahora con cuatro niveles; no se solicitan otros cambios funcionales.

## 2. Resumen y prioridad

| ID | Prioridad | Hallazgo | Tipo |
|---|---|---|---|
| GRP-01 | Alta | El POST público de autoasistencia no vuelve a comprobar vigencia ni finalización. | Autorización temporal |
| GRP-02 | Alta | Resumen y eliminación de reportes no muestran autorización por permiso y alcance. | Autorización horizontal |
| GRP-03 | Alta | La búsqueda pública usa coincidencia parcial y respuestas que pueden revelar existencia o pertenencia. | Privacidad |
| GRP-04 | Alta | Las evidencias no comprueban explícitamente que `{informe}` pertenezca a `{grupo}`. | Autorización horizontal |
| GRP-05 | Alta | Aprobación, corrección, ofrendas e ingresos necesitan comprobar autorización y atomicidad. | Integridad financiera |
| GRP-06 | Media | La unicidad de reporte y asistencia depende de consultas sin restricciones únicas observadas. | Concurrencia / integridad |
| GRP-07 | Media | Existen rutas hacia métodos `crear()` y `finalizar()` ausentes del controlador. | Rutas legado |
| GRP-08 | Media | `georreferencia()` contiene una variable no definida y un bloque de persistencia incoherente. | Error funcional probable |
| GRP-09 | Media | Negocio define cuatro niveles, pero migración y seeder contienen valores iniciales diferentes. | Configuración |
| GRP-10 | Media | No se encontraron pruebas dedicadas por nombre a los flujos críticos de Grupos. | Cobertura |

---

## 3. Hallazgos detallados

## GRP-01 — Vencimiento no aplicado en el POST público

### Evidencia observada

- `ReporteGrupo::sePuedeCompartirLinkDeAsistencia()` comprueba fecha, hora, plazo y `finalizado`.
- `ReporteGrupoController::miAsistencia()` usa esa función para decidir si muestra el formulario.
- `ReporteGrupoController::reportarMiAsistancia()` guarda la asistencia sin volver a ejecutar la comprobación.

### Riesgo

Ocultar el formulario no impide enviar directamente una petición POST después del vencimiento o cuando el reporte ya finalizó.

### Recomendación

- Aplicar la regla en el servidor antes de buscar o adjuntar al usuario.
- Devolver una respuesta segura y consistente para enlace vencido, reporte finalizado o inexistente.
- Considerar URL firmada o token específico si la exposición del ID del reporte no es suficiente.

### Criterios de aceptación

- Dentro de la ventana se registra exactamente una asistencia válida.
- Antes o después de la ventana, y tras finalizar el reporte, no se modifica la base de datos.
- Manipular el ID no permite registrar en otro reporte o tenant.

## GRP-02 — Resumen y eliminación sin autorización explícita

### Evidencia observada

- Las rutas están dentro de `auth` y `verified`.
- `ReporteGrupoController::resumen()` recibe directamente `ReporteGrupo` y muestra personas y valores sin verificar permiso o alcance en el método.
- `eliminar()` elimina asistencias, clasificaciones, ofrendas, ingresos y el reporte sin verificación visible de `reportes_grupos.opcion_eliminar_reporte_grupo` ni de ministerio/sede.
- La interfaz sí oculta opciones según permisos, pero eso no protege una solicitud directa.

### Riesgo

Un usuario autenticado podría intentar consultar o eliminar un reporte fuera de su alcance conociendo el ID.

### Recomendación

- Crear una Policy o servicio único de alcance para `ReporteGrupo`.
- Autorizar dentro de resumen, edición, aprobación, corrección y eliminación.
- Ejecutar la eliminación completa dentro de una transacción y decidir recuperación/auditoría.

### Criterios de aceptación

- Permiso y alcance se comprueban independientemente.
- Cambiar manualmente el ID devuelve `403` o respuesta segura equivalente.
- Un fallo intermedio no deja ofrendas, ingresos o asistencias parcialmente eliminados.

## GRP-03 — Búsqueda pública parcial y enumeración

### Evidencia observada

- La identificación y el correo se consultan con `LIKE %valor%`.
- Se toma el primer resultado coincidente.
- Las respuestas distinguen entre ya registrado, pertenece al grupo, usuario existente fuera del grupo y persona inexistente.
- No se observó limitación de intentos específica en las rutas públicas.

### Riesgo

Puede haber coincidencias ambiguas y revelación de existencia o membresía. Los intentos automatizados podrían consultar datos parciales.

### Recomendación

- Usar coincidencia normalizada exacta para correo e identificación.
- Diseñar mensajes que no revelen información innecesaria.
- Añadir rate limiting y auditoría mínima sin registrar valores sensibles completos.
- Confirmar que `SoftDeletes` excluya usuarios dados de baja y agregar prueba explícita.

### Criterios de aceptación

- Solo un integrante activo con coincidencia exacta puede registrarse.
- Usuario dado de baja, usuario ajeno y valor ambiguo no modifican asistencia.
- Los intentos repetidos se limitan.

## GRP-04 — Informe de evidencia no ligado al grupo de la URL

### Evidencia observada

- Las rutas reciben `{grupo}` y `{informe}`.
- `verificarGrupo` autoriza el grupo solicitado.
- `InformeEvidenciaGrupoController` usa el informe enlazado sin comprobar explícitamente `informe.grupo_id === grupo.id` en ver, editar, actualizar, eliminar y descargar.

### Riesgo

Un usuario autorizado para un grupo podría sustituir el ID del informe por el de otro grupo.

### Recomendación

- Usar scoped bindings o una consulta anidada desde `$grupo->informesEvidencias()`.
- Autorizar también la acción concreta: ver, editar, eliminar o descargar.

### Criterios de aceptación

- Un informe solo se resuelve dentro de su grupo.
- Un ID cruzado no revela, modifica, descarga ni elimina datos.

## GRP-05 — Aprobación y consistencia financiera

### Evidencia observada

- `Asistencias` crea o actualiza `Ofrenda` e `Ingreso` mientras finaliza el reporte.
- `GestionarAprobacionDesaprobacionDeReportes` cambia `aprobado`, `valor_real` e ingresos asociados.
- Las acciones Livewire revisadas no muestran una comprobación interna completa de permiso y alcance.
- No toda la secuencia observada está envuelta en una única transacción.

### Riesgo

Un fallo parcial o una acción Livewire manipulada podría desalinear reporte, ofrenda e ingreso. También debe preservarse la regla de moneda única.

### Recomendación

- Autorizar cada acción Livewire en el servidor.
- Encapsular transiciones financieras relacionadas en transacciones.
- Validar que todas las ofrendas e ingresos del reporte usen la moneda definida para ese reporte/iglesia.
- Mantener auditoría de aprobado → corregido/desaprobado → aprobado.

### Criterios de aceptación

- Solo el permiso correcto puede aprobar o corregir.
- Cada transición registra actor y fecha.
- Nunca queda una ofrenda sin ingreso esperado ni un ingreso con valores divergentes.
- Un reporte no puede contener dos monedas.

## GRP-06 — Unicidad dependiente de consultas

### Evidencia observada

- La creación consulta si ya existe un reporte para el mismo grupo y fecha.
- La autoasistencia consulta si ya existe el usuario en el reporte.
- En las migraciones revisadas no aparecen índices únicos para `(grupo_id, fecha)` ni `(reporte_grupo_id, user_id)`.

### Riesgo

Dos solicitudes concurrentes pueden superar ambas consultas antes de guardar y producir duplicados.

### Recomendación y aceptación

- Confirmar si negocio permite más de un reporte por fecha en algún tipo de grupo.
- Si la regla es universal, agregar restricciones únicas mediante migración cuidadosamente revisada.
- Manejar la excepción de duplicado de manera amigable.
- Probar dos solicitudes concurrentes.

## GRP-07 — Rutas posiblemente obsoletas

### Evidencia observada

`routes/app.php` declara `ReporteGrupoController::crear()` y `finalizar()`, pero esos métodos no existen en el controlador revisado. El flujo vigente parece residir en componentes Livewire. Una vista antigua todavía referencia la ruta de creación.

### Recomendación y aceptación

- Determinar con `route:list`, navegación y pruebas si las rutas son alcanzables.
- Si son legado, retirar rutas y vista únicamente después de comprobar referencias.
- Si son vigentes, restaurar un contrato coherente sin duplicar la lógica Livewire.

## GRP-08 — Georreferencia

### Evidencia observada

- Cuando la iglesia carece de municipio/país, `georreferencia()` intenta usar `$usuario->pais`, pero `$usuario` no está definido.
- El bloque que aparentemente guarda coordenadas vuelve a exigir que la iglesia ya tenga latitud y longitud, aunque se entra a esa rama precisamente cuando falta alguna.
- Se usa Nominatim para obtener una ubicación inicial.

### Recomendación y aceptación

- Reproducir con iglesia sin coordenadas y sin municipio/país.
- Definir el fallback correcto y corregir la condición de persistencia.
- Manejar timeout, error HTTP y respuesta vacía de Nominatim.
- Probar coordenadas existentes, incompletas y proveedor no disponible.

## GRP-09 — Cuatro niveles frente a configuración inicial

### Evidencia observada

- Negocio confirmó cuatro niveles y pidió no cambiar ninguna otra conducta.
- La migración de `configuraciones` usa valor predeterminado 3.
- `ConfiguracionSeeder.php` contiene valor 2 en dos configuraciones.
- `GrupoController` consume el valor persistido y permite un modo extendido especial de 20.

### Recomendación y aceptación

- Consultar primero el valor real de cada tenant.
- Acordar si 4 será valor inicial, migración de datos existentes o configuración manual.
- No eliminar el modo de 20 ni cambiar otros comportamientos sin una decisión adicional.
- Probar que la vista ordinaria carga exactamente cuatro niveles cuando la configuración sea 4.

## GRP-10 — Pruebas

No se encontraron pruebas dedicadas por nombre a Grupos, Reportes de Grupo, autoasistencia o evidencias. Crear pruebas pequeñas por hallazgo y ejecutar primero el archivo específico.

## 4. Integración con Usuarios

Cruzar esta revisión con `_docs_agente/revision_tecnica_usuarios.md`:

- La definición de usuario activo debe ser única y compatible con `SoftDeletes` y `users.activo` si ambos estados intervienen.
- Los permisos del grupo deben proceder del rol activo.
- La autoasistencia no puede convertirse en un alta alternativa de usuarios.
- Las reglas de menores y credenciales permanecen en Usuarios.

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

## 6. Orden recomendado

1. GRP-01, GRP-02 y GRP-04 por exposición y autorización.
2. GRP-03 por privacidad del enlace público.
3. GRP-05 y GRP-06 por integridad financiera y concurrencia.
4. GRP-07 y GRP-08 por errores funcionales probables.
5. GRP-09 y GRP-10 para alinear configuración y consolidar cobertura.
