# Estado Actual del Proyecto CRECER

## Tareas Recientes (Memoria Implícita Recuperada)

- **Corrección Paginador**: Se arregló el error `Class 'App\Http\Controllers\Paginator' not found` en `proximas-actividades.blade.php`.
- **Refactor Vista Actividad**: Se implementó diseño de acordeón y toggle de estado en `actualizar.blade.php`.
- **Lógica de Periodos**:
  - **Escuelas**: (En proceso) Visualización ER completada.
- **Actividades**: (En proceso) Visualización ER completada.
- **Puntos de Pago**: (Nuevo) Creación de flujo y visualización de arquitectura financiera.
- **Grupos**: (Definido) Contexto cargado en `agenteGrupos.md`. Documentación en `_docs_agente/modulos/grupos.html`.
- **Consolidación**: (Definido) Contexto cargado en `agenteConsolidacion.md`. Documentación en `_docs_agente/modulos/consolidacion.html`.
- **Filtro Maestros**: Refinamiento de `MaestrosController` para filtrar por `sede_restringida_id`.
- **Validaciones**: Reglas de fechas en `ActividadController`.
- **Campo `visible_asistencia`**: Implementado en BD, Modelo, Livewire y Vistas.
- **Alerta Asistencia**: Implementada alerta con respuestas de formulario al registrar asistencia.
- **Fix Checkboxes**: Corrección de persistencia de estado en `FormularioActividad`.
- **Informe de Compras**:
  - **Refactor UI**: Cambio de vista de tablas a "Grid de Cards" para mejor legibilidad.
  - **Filtros Avanzados**: Implementación de lógica compleja para estados (Pendiente, Pagada, Anulada, Abonada).
  - **Exportación Excel**: Creación de `InformeComprasExport` y botón funcional.
  - **Data Seeding**: Actualización de `ActividadesManantialSeeder` para generar escenarios de pago diversos (Abonos, Rechazos) y métodos de pago aleatorios.
- **Arquitectura de Agentes**:
  - Creación de protocolo modular en `_docs_agente/modulos`.
  - Construcción del contexto para el agente de **Escuelas** (`escuelas.md`).
- **Refactorización Core (Idempotencia)**:
  - **Seeders**: Migración masiva de `::create()` a `::firstOrCreate()` en todos los seeders.
  - **Relaciones Seguras**: Implementación de lógica `wasRecentlyCreated` en `RoleSeeder` y `ActividadesManantialSeeder` para evitar duplicación de datos pivote (Permissions, Abonos, Monedas).
  - **Resolución de Conflictos**: Refactorización de `UserSeeder` para usar `email` como llave única y evitar errores de duplicados (`bcrypt` generaba hashes diferentes impidiendo comparaciones simples).
  - **Configuración Git/Deploy**:
    - Se eliminó la conexión SSH del VPS en Git (que causaba carga de archivos no sincronizados).
    - Se configuró el `origin` correctamente hacia GitHub: `https://github.com/IDEA-ARRIBA-REDIL/Crecer.git`.
    - Se validó el flujo con un commit y push exitoso.
- **Protocolo de Migraciones en Producción**:

  - **Regla de Oro**: Nunca editar migraciones existentes. Siempre crear nuevas (`additive migrations`).
  - **Comando de Producción**: Usar `php artisan migrate --force` (evita confirmaciones interactivas).
  - **Evitar Destrucción**: JAMÁS usar `migrate:fresh` o `migrate:refresh` en producción.
  - **Columnas Nuevas**: Al agregar columnas a tablas con datos, usar siempre `->nullable()` o `->default('valor')` para evitar errores de integridad.
  - **Workflow**:
    1. Crear migración: `php artisan make:migration agregar_campo_x --table=tabla_y`.
    2. Usar `Schema::table` (no `create`).
    3. Deploy & `migrate --force`. Laravel ejecutará solo el archivo nuevo.

- **Refactorización Pagos y Matrículas (Febrero 2026)**:
  - **Schema**: Cambio de `metodo_pago` (string) a `tipo_pago_id` (integer/FK) en tabla `matriculas`.
  - **Modelos**: Actualización de relación `Matricula -> belongsTo -> TipoPago`.
  - **Exportación**: Optimización de `AlumnosPeriodoExport` con eager loading directo `matricula.tipoPago`.
  - **Flujo Taquilla**: Asignación automática de `tipo_pago_id` al procesar matrícula presencial.
  - **Flujo Web**: Lógica dual (Null en Carrito -> Update en Checkout) para asignar medio de pago correcto.
  - **UI/UX**: Rediseño de tarjetas de alumnos (Altura uniforme, badges sutiles, limpieza visual) en `listado-alumnos-periodo`.
- **Solicitudes de Traslado (Febrero 2026)**:

  - **Estudiante**: Nueva interfaz `/escuelas/{usuario}/solicitar-traslado` con validaciones de elegibilidad (asistencia, notas, intentos) e historial de solicitudes.
  - **Admin**: Nueva interfaz de gestión `/escuelas/matriculas/solicitudes-traslado` con diseño de Cards responsive y botones de acción rápida.

  - **Notificaciones**: Implementación de correos automáticos (`TrasladoAprobado`, `TrasladoRechazado`) integrados en el flujo de aprobación.
  - **Arquitectura**: Migración a patrón Controlador-Vista Wrapper para mejor mantenimiento.

- **Módulo Cursos (LMS) (Febrero 2026)**:
  - **Base de Datos**: Implementación de tablas `cursos`, `carreras`, `categorias_cursos` y pivotes complejos para roles, pagos y requisitos ("Growth Steps").
  - **Gestión (CRUD)**:
    - **Creación/Edición**: Formularios Livewire robustos divididos en secciones lógicas (Info, Precios, Multimedia).
    - **Multimedia**: Carga de imágenes optimizada y normalización de URLs de video (YouTube).
    - **Reactividad**: Selectores dinámicos (Select2) donde la moneda define los métodos de pago disponibles, con manejo de eventos JS/Livewire antiparpadeo.
  - **Restricciones y Culminación**:
    - **Vista Dedicada**: `restricciones.blade.php` accesible desde el menú de opciones del curso.
    - **Restricciones Generales**: Segmentación por Género, Sede, Edad, Estado Civil y Tipo de Servicio (Grupal).
    - **Automatización**: Lógica de "Culminación" para marcar requisitos (Pasos/Tareas) como completados al finalizar el curso.
  - **Inscripción y Checkout**:
    - **Validación Universal**: Motor `validarRequisitosUsuarioCurso` incorporado para evaluar pasos de crecimiento, demografía y matrícula previa antes de habilitar los botones.
    - **Carrito Específico LMS**: Tabla `carritos_curso_user` (JSON items) y componente Livewire para armar pedidos múltiples.
    - **Checkout Independiente**: `CheckoutCursos` renderiza métodos de pago provenientes estrictamente de los habilitados en la configuración de cada curso. Lógica para compra directa y matriculación (`curso_users`).
  - **Gestión de Inscritos (Estudiantes)**:
    - **UI**: Cuadrícula (Grid) de tarjetas Bootstrap rediseñada, ubicando info de contacto y rol dinámico en el body, y el progreso en el footer.
    - **Filtros Offcanvas Avanzados**: Búsqueda global en tiempo real combinada con filtros diferidos (Estado, Dificultad, Carrera, Año) en un Offcanvas.
    - **Tags de Filtro Activos**: Los filtros aplicados se renderizan como botones desestimables sobre la lista con un botón global de limpieza, sin conflictos entre Livewire y Select2.
  - **Previsualización de Curso Pública (`previsualizar.blade.php`)**:
    - **Playlist Dinámico**: Se refactorizó la 'Playlist' lateral para iterar los Módulos reales e Ítems del curso (con miniaturas y formato de candados condicional según apertura).
    - **Control Accesos**: Navbars dinámicos y restricción rigurosa a compras de usuarios no autenticados (`@auth` en Blade y redirecciones en Livewire al checkout).
  - **Catálogo Público de Cursos (`catalogo-cursos.blade.php`) - Febrero 2026**:
    - **Vista General**: Implementada en `/cursos/catalogo` accesible externamente. Muestra banner genérico con `default.png`, tarjetas optimizadas de "Mis Cursos" solo para usuarios autenticados con progreso de estudio dinámico.
    - **Filtros Dinámicos (Cross-Device)**: Búsqueda global, ordenamiento (Recientes, Antiguos, A-Z) y sistema híbrido de multiselección de categorías (Pestañas clickeables estilo `badge` en Desktop/Tablet y `select multiple` nativo en dispositivos Móviles manejado con Livewire arrays `$categoriasSeleccionadas`).
  - **Foro de Dudas y Comunidad**:
    - **Estructura**: Tablas `curso_foro_hilos` y `curso_foro_respuestas` para gestionar preguntas, respuestas y estados (pendiente, resuelto, cerrado).
    - **Estudiante**: Componente `ForoCursoEstudiante` para crear dudas, interactuar y visualizar hilos. Soporte para visualización dinámica de fotos de perfil (avatares) reales traídas con Storage y fallback a iniciales de usuario.
    - **Administración**: Panel global `PanelForoAsesor` con panel offcanvas para gestionar dudas de todos los cursos y emitir "respuestas oficiales".
  - **Campus Visualizador**:
    - División UI para priorizar la asimilación del contenido: El material primario (video, pdf, presentaciones e iframes) carga en el reproductor principal llamándose directamente desde las relaciones `itemable`. El subtexto de la clase (texto enriquecido `contenido_html`) se muestra como una caja independiente debajo para funcionar como instrucciones de la asignatura.
    - **Interfaz de Evaluación**: Carga de forma independiente a los reproductores de lecciones. Extrae y mezcla (`shuffle`) aleatoriamente las preguntas estructuradas en `curso_pregunta` para el ítem activo en la vista, con navegación independiente ("círculos amarillos") en un solo panel para evitar la pérdida de contexto que sucedería al recargar la página. Usa validaciones asíncronas con SweetAlert.
  - **Seeders y Datos Dummy**: Creación de `CursoDemoSeeder` que inyecta cursos, módulos, lecciones y decenas de inscritos con progreso aleatorio para pruebas fidedignas de paginación e UI. (Se incluye un examen bíblico de prueba "Mentoreo Espiritual").

## Plan Activo

- [ ] Construir Agentes Modulares:
  - [x] Agente Escuelas (Validado + Diagrama + HTML).
  - [x] Agente Actividades (`actividades.md` + Mapa Mental).
  - [x] Agente Actividades-Carrito (`carrito.md` + Flujos diferenciados).
- [ ] Construir Agente "Usuarios" (Sugerido).
- [ ] Validar flujos de inscripción y compras en producción.
- [ ] Mantener actualizada esta bitácora.
