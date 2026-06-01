Propuesta: Módulo de Estudios Externos y Créditos Académicos
Sistema: REDIL Cloud
Fecha: Mayo 2026


Descripción General

Se propone el desarrollo del módulo Estudios Externos, una funcionalidad que permite al personal administrativo registrar y hacer seguimiento del avance académico de los miembros que cursan estudios en instituciones externas (seminarios teológicos, universidades, institutos bíblicos, etc.).

El módulo se integra con la estructura de Escuelas ya existente en la plataforma, permitiendo registrar matrículas y calificaciones mediante la carga de archivos Excel estandarizados, y generando dashboards de seguimiento por créditos académicos.


¿Qué Problema Resuelve?

Actualmente, el seguimiento de miembros que estudian en instituciones externas se realiza de forma manual: se reciben archivos en Excel de cada institución al inicio y al final de cada semestre, se revisan manualmente, y no queda un registro centralizado en la plataforma. Esto genera las siguientes limitaciones:

- No hay visibilidad en tiempo real de quiénes están cursando estudios externos.
- No se puede determinar fácilmente cuántos créditos lleva acumulados cada persona.
- No hay forma de verificar automáticamente si un miembro completó los requisitos académicos para avanzar en sus pasos de crecimiento.
- El seguimiento depende de la memoria y los archivos personales de un número reducido de encargados.


Componentes del Módulo

1. Escuelas Externas

Las instituciones externas se registran en la plataforma como Escuelas con una marca especial que las distingue como "externas". Dentro de cada escuela externa, el administrador crea las materias del pensum tal como las ofrece la institución, asignando a cada materia su número de créditos académicos.

Esto permite que la plataforma maneje un espejo del pensum real de cada institución, facilitando la comparación y el seguimiento del avance.


2. Carga de Archivos Excel

El proceso de registro se divide en dos momentos del semestre:

Al inicio del semestre — Archivo de Matriculados:
- El administrador selecciona la escuela, la materia y escribe el nombre del semestre (ej. "2025-1").
- Sube un archivo Excel con una sola columna: el número de identificación de cada persona matriculada.
- El sistema verifica que cada persona exista en la plataforma, crea el registro de matrícula externa con estado "En curso", y reporta las filas que no pudieron procesarse (ej. persona no encontrada).

Al final del semestre — Archivo de Calificaciones:
- El administrador selecciona la misma escuela, materia y semestre.
- Sube un archivo Excel con cuatro columnas: identificación, nota final, si aprobó o no (1 o 0), y una observación opcional.
- El sistema actualiza automáticamente cada matrícula externa al estado correspondiente (Aprobado / Reprobado), registra los resultados en el historial académico del miembro, y verifica si se habilitan nuevos avances en el proceso de formación.

Seguridad y trazabilidad:
- Cada carga de archivo queda registrada: quién la subió, cuándo, cuántos registros se procesaron correctamente y cuántos fallaron.
- Los archivos originales se almacenan para referencia futura.
- Las filas que presenten errores se documentan con detalle (fila, identificación, motivo del error) para que el administrador pueda corregir y volver a intentar.


3. Seguimiento de Matriculados Externos

Una vista dedicada permite consultar en cualquier momento la lista de personas matriculadas en instituciones externas, con filtros por:
- Escuela (institución)
- Materia
- Semestre
- Estado (En curso / Aprobado / Reprobado)

Cada registro muestra: nombre del alumno, identificación, sede a la que pertenece, materia, semestre, estado, nota final y créditos de la materia.


4. Dashboard de Avance por Créditos

Un panel de control visual permite ver el progreso académico de los miembros en cada institución externa:

Resumen general:
- Total de créditos del pensum completo.
- Total de alumnos activos en la institución.
- Porcentaje promedio de avance en créditos del grupo.

Detalle por alumno:
- Créditos aprobados, reprobados y en curso.
- Porcentaje de avance individual.
- Fecha del último registro.
- Expandible para ver materia por materia, con nota y semestre.

Ejemplo: Si el pensum tiene 45 créditos y Juan Pérez ha aprobado materias que suman 18 créditos, su avance es del 40%. Si reprobó Griego I en 2024-2 (3 créditos) pero lo aprobó en 2025-1, los créditos solo se cuentan una vez.


5. Integración con Pasos de Crecimiento y Prerrequisitos

Cuando el sistema registra que un miembro aprobó una materia externa, evalúa automáticamente:

- Prerrequisitos de materias: Si la materia aprobada era prerequisito de otra, verifica si ahora se cumple la condición para habilitarla.
- Pasos de crecimiento: Si la materia tiene asociado un cambio en el proceso de formación del miembro (ej. avanzar de nivel, completar una etapa), el sistema lo actualiza automáticamente.

Esto elimina la necesidad de que el administrador revise manualmente caso por caso si alguien ya completó los requisitos para avanzar.


6. Control de Acceso por Roles

Todas las funcionalidades están protegidas por el sistema de permisos existente:

Funcionalidad                      Permiso requerido
Subir archivos Excel               estudios-externos.importar
Ver lista de matriculados          estudios-externos.ver-matriculados
Ver historial de importaciones     estudios-externos.ver-importaciones
Dashboard de créditos              estudios-externos.dashboard
Eliminar una importación           estudios-externos.eliminar


7. Plantillas Excel Descargables

El sistema ofrece plantillas Excel listas para descargar con las columnas correctas y un ejemplo, para que el personal administrativo las comparta con las instituciones externas o las llene directamente:

- Plantilla de Matriculados: 1 columna (identificación).
- Plantilla de Calificaciones: 4 columnas (identificación, nota final, aprobado, observación).


Tecnología Utilizada

El módulo se construye sobre la misma pila tecnológica de la plataforma:

- Backend: Laravel 11 (PHP 8.2) con arquitectura Multi-Tenant.
- Frontend reactivo: Livewire 3 + Alpine.js.
- Procesamiento de Excel: Laravel Excel (Maatwebsite/Excel), ya instalado en el proyecto.
- Base de datos: 2 tablas nuevas + 3 modificaciones a tablas existentes (migración automatizada por tenant).
- Seguridad: Permisos Spatie por rol.


Estimación de Tiempo de Desarrollo

Componente                                        Tiempo estimado
Estructura de datos y modelos                     1 semana
Procesamiento de Excel y lógica de negocio        1 semana
Total                                             ~58 horas / 2 semanas

Estimado a 30 horas semanales con apoyo de herramientas de Inteligencia Artificial. Sin IA, el mismo trabajo tomaría aproximadamente 3–4 semanas.


Resumen de Valor para la Organización

Antes del módulo                                  Con el módulo
Seguimiento manual en archivos personales         Registro centralizado en la plataforma
Sin visibilidad de quién está cursando            Estado en tiempo real: en curso / aprobado / reprobado
Sin conteo de créditos                            Dashboard con % de avance por créditos
Verificación manual de requisitos                 Actualización automática de pasos de crecimiento
Archivos Excel sueltos sin trazabilidad           Historial completo de importaciones con auditoría


Documento preparado para revisión del cliente — REDIL Cloud / Mayo 2026
