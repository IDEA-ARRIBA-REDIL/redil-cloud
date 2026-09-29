# Estado Actual del Proyecto CRECER

- **Filtro de Rango de Fechas en Historial de Pagos de Taquilla y Corrección en Exportación (Septiembre 2026, desplegado en Git, EC2 y cPanel)**:
  - **Filtro por Rango de Fechas con Flatpickr + Alpine.js**:
    - En [`app/Livewire/Taquilla/HistorialTransacciones.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/app/Livewire/Taquilla/HistorialTransacciones.php), se actualizó la propiedad `$fecha` para interpretar rangos de fechas (delimitados por `' to '` o `' a '`), manteniendo soporte de fecha individual y reseteo vacío. Inicializado por defecto en la fecha actual (`today()->toDateString()`).
    - En [`resources/views/livewire/taquilla/historial-transacciones.blade.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/resources/views/livewire/taquilla/historial-transacciones.blade.php), se reemplazó el input de fecha estático por un componente reactivo Flatpickr (`mode: 'range'`) encapsulado en Alpine.js con `wire:ignore`, formato amigable `d/m/Y`, botón de limpieza rápida `[X]` y botón de restablecimiento a "Hoy".
    - Tanto el Dashboard de Taquilla ([`dashboard.blade.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/resources/views/contenido/paginas/taquillas/dashboard.blade.php)) como el Historial del Cajero ([`historial.blade.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/resources/views/contenido/paginas/taquillas/historial.blade.php)) se benefician automáticamente de esta reactividad al consumir este mismo componente Livewire.
    - Se eliminó el script residual de Flatpickr jQuery en [`historial.blade.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/resources/views/contenido/paginas/taquillas/historial.blade.php).
  - **Soporte en Exportación Excel de Transacciones de Caja**:
    - En [`app/Exports/HistorialTransaccionesCajaExport.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/app/Exports/HistorialTransaccionesCajaExport.php), se adaptó la consulta de exportación para filtrar por rango de fechas (`startOfDay()` a `endOfDay()`) cuando se recibe un rango.
  - **Corrección de Excepción en `InformePagosExport.php`**:
    - En [`app/Exports/InformePagosExport.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/app/Exports/InformePagosExport.php), se resolvió el error fatal `Attempt to read property "user" on null` al exportar puntos de pago, aplicando navegación segura (`$compra->user?->...`) con fallback a `$compra->nombre_completo_comprador` y `$compra->identificacion_comprador` para transacciones de invitados o compradores no registrados en `users`.
  - **Despliegue y Sincronización Multi-Entorno**:
    - **Git (GitHub)**: Commit `9cd9a6f0ca` pusheado exitosamente a la rama `main`.
    - **Servidor EC2 (`i-0a846e8fb50d9a003`)**: Sincronizado vía `git pull origin main` y cachés optimizadas con `php artisan optimize:clear`.
    - **Servidor cPanel (`redil.ubicalo.com`)**: Desplegado por SFTP con respaldo preventivo en `/home/redil2024/redil-releases/taquillas-fechas-20260929204104` y validación de integridad SHA-256 para todos los 5 archivos.

  - **Entrega SFTP Asistida**: Lote de 10 archivos sincronizado con reemplazo atómico y comprobación SHA-256 completada al 100%. Respaldo privado remoto creado en `/home/redil2024/redil-releases/rueda-vida-gestion-20260929152416` con manifiesto `manifest.json`. Sin migraciones pendientes de base de datos ni comandos remotos ejecutados.
  - **Panel de Gestión Livewire (`GestionarRuedaDeLaVida.php` y `gestionar-rueda-de-la-vida.blade.php`)**:
    - Se creó un módulo administrativo interactivo para que administradores de la iglesia configuren áreas (`SeccionRv`) y hábitos (`CampoSeccionRv`) sin requerir acceso ni modificaciones directas en la base de datos.
    - Soporte completo para crear, editar, eliminar y reordenar áreas evaluables de hábitos (tipo `contador`), asignar íconos Tabler, paletas de colores y promedios mínimos requeridos.
    - Soporte para crear, editar, eliminar y reordenar hábitos fijos o abiertos (donde el usuario redacta su hábito personalizado) y definir su color para la serie polar de ApexCharts.
    - Pestaña de configuración global para editar parámetros de `ConfiguracionRv` (`max_metas`, `max_habitos_por_meta`, `periodicidad` en días para avances, nombre general y labels).
    - Integrado con modales Bootstrap 5, SweetAlert2 (`Swal.fire` y eventos `$this->dispatch('msn', ...)`).
  - **Ruta y Acceso Administrativo**:
    - Registrada ruta `GET /rueda-vida/gestionar` (`ruedaDeLaVida.gestionar`) y vista `resources/views/contenido/paginas/rueda-de-la-vida/gestionar.blade.php`.
    - Enlace añadido en el dashboard de configuraciones (`ConfiguracionController@index`) y botón directo en el historial (`historial.blade.php`) para usuarios administradores.
  - **Optimizaciones de Rendimiento y Limpieza de Código**:
    - En `RuedaDeLaVidaController::resumen()`, se eliminaron consultas a modelos legados (`Metas::get()` y `HabitosRv::get()`) y se implementó el precálculo de promedios por sección en una sola consulta SQL agregada, eliminando consultas N+1 en `resumen.blade.php`.
    - En `RuedaDeLaVidaController::crear()`, se blindó la persistencia de la rueda, campos y metas dentro de `DB::transaction()`, asegurando atomicidad y recálculo consistente del promedio general.
    - En `SeccionRv.php` y `CampoSeccionRv.php`, se definieron explícitamente las claves foráneas `seccion_rv_id` y orden predeterminado en las relaciones Eloquent.
  - **Actualización Documental Integral**:
    - Actualizados `.agent/workflows/agenteRuedaVida.md` y `_docs_agente/modulos/rueda_de_la_vida.md` incorporando la arquitectura activa v2/v3, el submódulo de avances periódicos de hábitos (`AvanceHabitoRv`), el panel Livewire y las convenciones correctas de inputs.


  - `CalificacionGrillaAlumnos` incorpora búsqueda reactiva con debounce de 300 ms sobre la colección ya cargada. Busca por nombres, apellidos, identificación y correo, ignora mayúsculas/tildes y admite palabras en cualquier orden; no repite la consulta académica ni altera la colección de notas.
  - La vista `calificacion-grilla-alumnos.blade.php` presenta barra de búsqueda con limpieza/carga, contadores, escala configurada, estados vacíos diferenciados, encabezado y columna de alumno fijos, datos identificativos, estados de bloqueo/traslado y celdas de nota más legibles. Se conservó `wire:model.live.debounce.500ms` y el método original de guardado automático.
  - Prueba focalizada: 1 prueba / 6 aserciones; compilación y render Blade correctos. Pint focalizado correcto; Pint global continúa bloqueado por el error ajeno en `app/Livewire/Actividades/CategoriasActividad.php:550`.
  - Componente y vista publicados y verificados por SHA-256. Respaldo privado: `/home/redil2024/redil-releases/calificacion-grilla-buscador-20260929-a00cf8a7`. Sin comandos remotos, migraciones ni compilación frontend. El `User.php` remoto tiene accessors adicionales de foto/WhatsApp frente a la copia local; se inspeccionó que conserva los campos y métodos requeridos y no fue modificado.

- **Gestión docente de ítems: acordeones por corte (2026-09-29, publicado por SFTP)**:
  - Ajuste exclusivamente visual en `resources/views/livewire/escuelas/gestion-items-corte-materia-periodo.blade.php`, componente incluido por `maestros/gestion-items.blade.php`. Cada corte tiene un acordeón separado con cantidad de ítems, porcentaje del corte y suma de porcentajes; el primer corte inicia abierto y los demás cerrados.
  - Se agregaron claves estables para cortes e ítems y `wire:ignore.self` en los elementos controlados por Bootstrap. Se conservaron sin cambios los formularios, modales, validaciones y acciones de crear, editar y eliminar.
  - Compilación Blade correcta y render verificado con dos cortes separados. Una vista publicada y comprobada por SHA-256; respaldo privado `/home/redil2024/redil-releases/maestros-items-acordeones-20260929-aa132287`. Sin comandos remotos, migraciones ni compilación frontend.

- **Modelo de calificación: acordeones por corte (2026-09-29, publicado por SFTP)**:
  - Ajuste exclusivamente visual en `resources/views/livewire/escuelas/gestion-item-plantillas.blade.php`, componente incluido por `gestionar-modelo-materia.blade.php`. Agrupa las tarjetas por corte, ordena los cortes según su orden configurado y muestra nombre/cantidad de ítems. Primer corte abierto; los demás pueden abrirse independientemente.
  - Claves estables por corte e ítem y `wire:ignore.self` en controles/paneles para conservar el estado de Bootstrap mientras Livewire actualiza su contenido. Formularios, métodos, acciones de edición/eliminación y scripts existentes intactos.
  - Verificada compilación Blade sin errores y render con dos cortes/tres ítems, orden y estado vacío. No se crearon pruebas para este ajuste reversible de presentación; pendiente revisión visual en tenant autenticado.
  - Una vista publicada y comprobada por SHA-256, con respaldo privado `/home/redil2024/redil-releases/modelo-acordeones-20260929-08048a14`. Copia remota previa y clase Livewire coincidentes con las locales; sin comandos remotos, migraciones ni compilación frontend. Documentación interna conservada localmente.

- **Ampliación del dashboard: acordeones, cierre e informes (2026-09-28, código publicado por SFTP)**:
  - Acordeones por materia con gráficos de resultados, género y asistencia. Cierre en segundo plano y reapertura con confirmación, únicamente mientras el periodo esté abierto; conserva las notas y asistencias originales al reabrir.
  - Excel mediante modal de sede cuando periodo y materia están cerrados: una hoja por horario con nota acumulada y asistencias finales, resultado y datos del alumno. Filtra por sede del aula, deduplica matrículas, conserva valores numéricos y evita fórmulas en cadenas.
  - Cálculo individual corregido para no multiplicar resultados por matrículas repetidas y utilizar agrupación compatible con PostgreSQL; valida configuraciones y horarios ambiguos. Reabrir no revierte efectos de aprobación históricos.
  - Verificación: 25 pruebas / 153 aserciones y Pint focalizado correcto. Persiste el error ajeno de Pint global en `CategoriasActividad.php:550`. Assets y dependencias comprobados por SFTP, sin ejecutar comandos remotos.
  - Publicados y verificados por SHA-256 los 12 archivos de aplicación del lote. Respaldo privado: `/home/redil2024/redil-releases/periodos-acordeones-20260928-5c96af4f`. Sin caché de rutas remota; sin migraciones, compilaciones ni comandos remotos. Documentación interna y pruebas conservadas localmente.
  - Pendiente confirmar worker de la cola predeterminada, actualización de su código y revisión visual autenticada. El cron documentado de `admin-security` no procesa estos cierres. No se ejecutó ningún cierre sobre datos reales ni se procesaron trabajos pendientes ajenos.

- **Dashboard académico por periodo (2026-09-28, publicado por SFTP)**:
  - Acceso «Dashboard del periodo» en las opciones de gestión, sin permiso nuevo. Ruta `periodo.dashboard`, controlador `DashboardPeriodoController`, servicio de lectura `ResumenPeriodoService` y vistas en la carpeta de periodos.
  - Estudiantes únicos y matrículas vigentes; género y resultados generales, por nivel y materia; notas ponderadas, porcentaje/promedio de asistencia, avance de calificación, bloqueos y motivos de riesgo.
  - Proyección en periodos abiertos; resultados almacenados en periodos o materias cerrados. Configuración incompleta y resultados ausentes quedan sin evaluar. No escribe datos académicos ni modifica el cierre existente.
  - Verificación: 14 pruebas / 79 aserciones (dashboard y regresión de bloqueo al cerrar); PHP y formato de archivos nuevos correctos. Pint global sigue impedido por el error previo de `CategoriasActividad.php:550`.
  - El responsable amplió expresamente la autorización SFTP a todos los módulos y archivos elegibles de cada implementación. Actualizados `ARCHITECTURE.md` sección 10.1, `base-desarrollo/SKILL.md` sección 7.2 y `acciones del agente.md`; se conservan exclusiones, identidad SSH conocida, respaldos y protección de cambios ajenos.
  - Publicados y verificados por SHA-256 seis archivos: `app/Services/ResumenPeriodoService.php`, `app/Http/Controllers/DashboardPeriodoController.php`, las vistas `dashboard-periodo.blade.php` y `resumen-dashboard-periodo.blade.php`, `routes/app.php` y `gestionar-periodos.blade.php`. Los dos archivos reemplazados coincidían con las referencias locales anteriores; los modelos dependientes coincidían con el servidor.
  - Respaldo y manifiestos privados: `/home/redil2024/redil-releases/periodos-dashboard-20260928-74d82984`. Sin caché de rutas remota y sin comandos remotos, migraciones ni compilaciones. Documentación interna y pruebas no se subieron. Pendiente revisión visual autenticada en tenant real.

- **Corrección de correo administrativo en cPanel (2026-09-28)**:
  - Detectados códigos encolados sin worker activo. Aplicados y verificados config/queue.php, CodigoAdminMail.php y la vista de login; cron dedicado cada minuto con flock para admin_security/admin-security. Primer ciclo correcto, conexión/autenticación SMTP correcta; recepción real pendiente del usuario.
  - Respaldo de tres archivos y cron en /home/redil2024/redil-central-releases/mail-fix-0Kab2W. Sin migraciones, seeders, cambios de cuentas ni procesamiento de los 25 trabajos de default. Accesos iniciales/provisioning requieren workers propios; esta corrección no acredita su funcionamiento.
  - Suite local: 22 pruebas / 93 aserciones y Pint focalizado correcto; Pint --dirty sigue bloqueado por CategoriasActividad.php:550, ajeno al alcance. WI-017 continúa abierto.
  - Los tres archivos remotos revisados antes del ajuste coincidían con el paquete previo. Esto no verifica la aplicación completa del paquete ni su migración.

- **Registro histórico: entrega SFTP del lote AdminGlobal, preparada sin activar en esa etapa (2026-09-28)**:
  - Por solicitud del responsable se inspeccionó cPanel con SFTP y clave SSH conocida, sin ejecutar PHP/Artisan remoto ni consultar BD. Se subió y verificó el paquete privado /home/redil2024/redil-central-releases/20260928-0Kab2W/central-admin.tar.gz: 44 archivos, respaldo previo y aplicador manual con comprobación de hashes y mantenimiento obligatorio. SHA-256: 1077f838b4e6e11f6a276e8fe4d117823e735a1e1e339b3931a312d3f8891efa.
  - Cero archivos activos modificados. routes/web.php y .env excluidos; UserSeeder/TenantDatabaseSeeder intactos. El paquete conserva tres permisos de gamificación presentes solo en el PermisoSeeder remoto mediante una variante de despliegue; no sustituirla después con la copia local sin reconciliar esas diferencias.
  - Rutas pendientes manuales: activar-cuenta, admin/invitaciones, admin/login-predeterminado; admin/planes ya existe. Ajustar orden del middleware administrativo. Aplicación requiere respaldo central, pausa coordinada de workers/scheduler, mantenimiento, aplicación del lote, migración central específica y limpieza selectiva de cachés. No confundir subida del paquete con despliegue completado ni con comprobación PostgreSQL/SMTP.

- **Gestión de planes y tema visual del panel central**:
  - Reutilizada GestionarPlanes en /admin/planes para crear, editar y activar/inactivar; validación central de slug, límite positivo o ilimitado, ID bloqueado y autorización por acción. Inactivar no suspende ni desasigna iglesias. La vista no ofrece eliminación destructiva.
  - Tema compartido exclusivo de layouts.centralApp: blanco, negro, turquesa/menta, puntos decorativos, botones redondeados y navegación responsive. Login central en dos columnas. No se modificó la marca de los tenants ni los seeders.
  - Estilos en layouts/sections/central-styles.blade.php sin entrada Vite nueva. Sin migraciones. Pendiente revisión visual en servidor tras sincronización; la referencia se interpretó como estilo, no se insertó la captura como fondo.
  - Verificación local ampliada: 21 pruebas / 84 aserciones correctas en AdminGlobalSecurityTest; formato de PHP de este cambio correcto. Persiste error de sintaxis ajeno en CategoriasActividad.php:550 que impide formato global limpio.

- **WI-017 — AdminGlobal: alta por invitación y separación de seeders (en verificación)**:
  - Se implementó un recorrido específico para el formulario: invitación vinculada a pago/correo/plan, tarea en cola central y NuevoTenantSeeder de cuatro cuentas. UserSeeder y TenantDatabaseSeeder no fueron sustituidos; los tenants sin onboarding_version=1 conservan el pipeline anterior.
  - Se agregaron controles de licencia/suspensión, autorización persistente Livewire, código de correo previo al acceso administrativo y accesos iniciales individuales sin contraseñas compartidas.
  - Documentación operativa: .agent/workflows/agenteAdminGlobal.md y knowledge/delivery/DESPLIEGUEREDILCLOUD.md. Pruebas focalizadas iniciales: 15 pruebas / 57 aserciones en memoria. Pendientes PostgreSQL, correo real, UI y seeding integral. Sin migraciones ejecutadas en servidores ni despliegue.

- **Optimización de Rendimiento en Lectura de Código QR y Duración de Modal en Asistencias (Septiembre 2026)**:
  - **Aceleración Hardware de Escaneo QR (`qr-scanner.blade.php` y `qr-asistencias.blade.php`)**: Se habilitó la aceleración nativa por hardware con `useBarCodeDetectorIfSupported: true` en `Html5Qrcode`, aumentando los fotogramas a `fps: 20` y transformando el `qrbox` a un cálculo responsivo (80% del visor) en lugar de un cuadro rígido de 250px, eliminando la latencia en la captura visual y el encuadre.
  - **Feedback Inmediato de Escaneo**: Se agregó vibración háptica (`navigator.vibrate`) y un overlay visual interactivo ("Procesando código QR...") que se activa en milisegundos tras la detección para evitar la percepción de pantalla congelada durante el viaje al servidor.
  - **Ampliación de Tiempo en Modal de Confirmación**: Se aumentó la permanencia de la alerta SweetAlert de confirmación de registro y asistencia previa a un mínimo de 4 segundos (`timer: 4000`, antes 2 segundos / 1500 ms) con barra de progreso reactiva.
  - **Optimización de Backend en Asistencias (`AsistenciasActividad.php` y `QrAsistencias.php`)**:
    - Se optimizó la verificación de asistencia previa evaluando instantáneamente en memoria (`$asistenciasRegistradasHoy`) en O(1) antes de consultar la BD.
    - Se sustituyeron las consultas `whereDate` (que forzaban un `CAST` de columna en PostgreSQL) por igualdad directa `'fecha', Carbon::today()->toDateString()`.
    - Se precargaron en `mount()` los elementos de formulario con `visible_asistencia` y se unificó el conteo de asistencias a una sola consulta.
    - Se implementó paginación (`WithPagination`, 25 registros por página) en `AsistenciasActividad.php` y controles de paginación en `asistencias-actividad.blade.php`, evitando la sobrecarga de hidratar cientos de modelos y diffing de miles de nodos DOM en cada escaneo QR.

- **Resolución de Errores de Sistema Reportados (Septiembre 2026)**:
  - **Error 1 (`UserPolicy::modificarUsuarioPolitica`)**: Se ajustó la firma a `?FormularioUsuario $formulario = null` y se aplicó null-safe en `$formulario?->tipo?->es_formulario_exterior` en [`app/Policies/UserPolicy.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/app/Policies/UserPolicy.php), evitando el fallo por `TypeError` cuando se accede a `/usuario/0/5/informacion-congregacional` sin formulario asignado para la edad.
  - **Error 6 (Unique violation `grupos_pkey` en PostgreSQL)**: Se añadió la sincronización de la secuencia `grupos_id_seq` tras la inserción manual en [`database/seeders/GruposYMiembrosSeeder.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/database/seeders/GruposYMiembrosSeeder.php) y se creó la migración tenant `2026_09_15_000001_sync_grupos_id_sequence.php` para restablecer el apuntador de la secuencia al ID máximo en las bases de datos tenant.
  - **Error 7 (Formulario de Actualización de Temas)**: En [`app/Http/Controllers/TemaController.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/app/Http/Controllers/TemaController.php) se modificó el método `update()` para redirigir a `route('tema.lista')` con mensaje de confirmación, y en [`resources/views/contenido/paginas/temas/actualizar-tema.blade.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/resources/views/contenido/paginas/temas/actualizar-tema.blade.php) se agregó el botón "Volver" con estilo `rounded-pill btn-outline-secondary`.
  - **Error 8 (`Attempt to read property "nombre" on null` en Reportes de Grupo)**: En [`resources/views/contenido/paginas/reportes-grupo/listar.blade.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/resources/views/contenido/paginas/reportes-grupo/listar.blade.php), se implementó el operador null-safe `{{ $reporte->grupo?->nombre ?? 'Sin grupo' }}` y validación previa antes de `estaDentroDelRango()`, garantizando protección ante grupos inexistentes o nulos (sin `withTrashed` ya que `Grupo` no utiliza el trait `SoftDeletes`).
  - **Error 9 (`VerificarReunion::handle()` retorno nulo)**: Se corrigió [`app/Http/Middleware/VerificarReunion.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/app/Http/Middleware/VerificarReunion.php) para retornar `return $next($request);` si `$validado` es verdadero o `return redirect()->route('pagina-no-encontrada');` si no cumple.
  - **Error 12 (`Call to undefined relationship [actividadCategoria]`)**: Se eliminó la relación inexistente en el eager loading de [`app/Exports/InformeComprasExport.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/app/Exports/InformeComprasExport.php) (que ya cargaba `categoriaActividad`) y se implementó el método alias `actividadCategoria(): BelongsTo` en [`app/Models/Inscripcion.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/app/Models/Inscripcion.php) para compatibilidad total.
  - **Error 13 (`Attempt to read property "nombre" on null` en Listado de Reuniones)**: En [`resources/views/contenido/paginas/reuniones/listar.blade.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/resources/views/contenido/paginas/reuniones/listar.blade.php), se reemplazó el acceso directo por el operador null-safe `{{ $reunion->sede?->nombre ?? 'Sin sede' }}` en la línea 303.
  - **Error 14 (`Attempt to read property "nombre" on null` en Gestión de Puntos de Pago)**: En [`resources/views/livewire/puntos-de-pago/gestionar-puntos-de-pago.blade.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/resources/views/livewire/puntos-de-pago/gestionar-puntos-de-pago.blade.php), se aplicó el operador null-safe `{{ $puntoDePago->sede?->nombre ?? 'Sin sede asignada' }}` en la línea 189, evitando el crash si un punto de pago tiene su sede nula o no asociada.

- **Estándar de Integridad Absoluta de Código y Directriz Anti-Truncamiento (Septiembre 2026)**:
  - **Inclusión en Documentación y Protocolos**: Se incorporó formalmente la directriz de prohibición de marcadores de posición (`/* ... */`, `// ... resto del código ...`, etc.) en [`ARCHITECTURE.md`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/ARCHITECTURE.md) (Sección 11), [`.agent/skills/base-desarrollo/SKILL.md`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/.agent/skills/base-desarrollo/SKILL.md) (Sección 2) y [`acciones del agente.md`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/acciones%20del%20agente.md) (Sección 4).
  - **Mandato**: Todo agente de IA o desarrollador tiene prohibido truncar o reemplazar lógica con comentarios resumen, debiendo siempre entregar código 100% completo, ejecutable y con preservación total de listeners y validaciones preexistentes.

- **Protección y Notificación de Punto de Pago o Sede Huérfana en Gestión de Cajas / Taquillas (Septiembre 2026)**:
  - **Resolución de `Attempt to read property "nombre" on null`**: En [`resources/views/livewire/puntos-de-pago/gestionar-taquillas.blade.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/resources/views/livewire/puntos-de-pago/gestionar-taquillas.blade.php), se agregaron validaciones condicionales para `$caja->puntoDePago` y `$caja->puntoDePago->sede`, evitando excepciones cuando una caja no tiene punto de pago asignado o cuando el punto o la sede fueron eliminados.
  - **Alertas Visuales y Badges**: Se incorporó un banner de advertencia en la tarjeta (`alert-warning`) indicando si falta el punto de pago o la sede, junto con textos destacados (`No asignado`, `Sin sede asignada`) y badge de `Eliminado` si el punto de pago está marcado como soft-deleted.
  - **Relación con Soft Deletes**: Se añadió `->withTrashed()` en la relación `puntoDePago()` de [`app/Models/Caja.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/app/Models/Caja.php) para permitir resolver y visualizar nombres de puntos de pago que hayan sido dados de baja sin romper la vista.
  - **Restauración de Scripts SweetAlert2 y Filtros**: Se restablecieron los listeners `@this.on('notificacion')` y `@this.on('confirmarEliminacion')` con SweetAlert2, y la lógica de descarte de chips de filtros (`remove-tag-taquilla`), que tenían comentarios placeholder incompletos (`/* ... */`) de un commit anterior.

- **Filtro de Rango de Fechas en Historial de Modificaciones / Anulaciones de Taquilla (Septiembre 2026)**:
  - **Rango por Defecto (Mes en Curso)**: Se modificó [`app/Livewire/Taquilla/HistorialModificaciones.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/app/Livewire/Taquilla/HistorialModificaciones.php) para reemplazar la fecha estática de un solo día por un rango (`$fechaInicio` y `$fechaFin`), inicializado por defecto en el mes en curso (`now()->startOfMonth()` y `now()->endOfMonth()`).
  - **Filtro Reactivo con Flatpickr + Alpine.js**: En [`resources/views/livewire/taquilla/historial-modificaciones.blade.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/resources/views/livewire/taquilla/historial-modificaciones.blade.php), se implementó un selector de rango Flatpickr (`mode: 'range'`) aislado con `wire:ignore` y Alpine.js, con formato visual amigable, botón rápido para volver al "Mes actual" y botón para limpiar fechas (`[x]`). Se agregó además un botón condicional "Limpiar filtros" para restablecer la vista.
  - **Soporte en Exportación Excel**: Se actualizó [`app/Exports/HistorialModificacionesExport.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/app/Exports/HistorialModificacionesExport.php) para filtrar la consulta respetando el rango de fechas (`fechaInicio` y `fechaFin`), manteniendo compatibilidad retroactiva.
  - **Limpieza de Scripts Obsoletos**: En [`resources/views/contenido/paginas/taquillas/historial-modificaciones.blade.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/resources/views/contenido/paginas/taquillas/historial-modificaciones.blade.php), se eliminó la inicialización jQuery de Flatpickr de fecha única que colisionaba con el nuevo comportamiento reactivo.

- **Descarga de Código QR en PDF para Invitados de Actividades (Septiembre 2026)**:
  - **Generación en PDF**: Se modificó `descargarQrInvitado()` en [`app/Livewire/Actividades/GestionarInvitados.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/app/Livewire/Actividades/GestionarInvitados.php) para generar y descargar un documento PDF (`qr-invitado-{slug}-{id}.pdf`) en lugar de enviar un PNG malformado en Base64.
  - **Reutilización de Ticket Oficial**: Utiliza `_generarPdfParaInscripcion()` cargando la vista oficial [`inscripcion-ticket.blade.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/resources/views/contenido/paginas/actividades/inscripcion-ticket.blade.php) con el código QR compatible con el lector de asistencias de actividades (`verificar_asistencia_inscripcion_usuario`).
  - **Mejora UI en Blade**: En [`gestionar-invitados.blade.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/resources/views/livewire/actividades/gestionar-invitados.blade.php), se actualizó el botón a "Descargar QR (PDF)" con estados de carga reactivos (`wire:loading`).
  - **Protección Anti-Pegar**: Se integraron validaciones en inputs de correo de invitados para prevenir copiar/pegar en la confirmación.

- **Flexibilización de Grupo en Creación y Edición de Sedes (Septiembre 2026)**:
  - **Campo Opcional**: Se modificó [`app/Http/Controllers/SedeController.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/app/Http/Controllers/SedeController.php) en los métodos `crear` y `editar` para que `grupoId` sea `nullable` en lugar de `required`, asignando `null` de forma segura si no se envía un grupo.
  - **Vistas**: Se configuró `'obligatorio' => false` en el componente `Grupos.grupos-para-busqueda` dentro de [`nueva.blade.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/resources/views/contenido/paginas/sedes/nueva.blade.php) y [`modificar.blade.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/resources/views/contenido/paginas/sedes/modificar.blade.php).
  - **Migración Tenant**: Se creó la migración `2026_09_11_000001_make_grupo_id_nullable_in_sedes_table.php` para cambiar `grupo_id` a `nullable` en la tabla `sedes`.

- **Integración de Configuración de Branding en Footer (Septiembre 2026)**:
  - **Sincronización con Modelo `Configuracion`**: Se actualizó [`resources/views/layouts/sections/footer/footer.blade.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/resources/views/layouts/sections/footer/footer.blade.php) (y `layouts2` / `footer-front`) para cargar los datos de marca blanca y creador almacenados en la base de datos a través de `App\Models\Configuracion` y configurados en [`configuracion-general.blade.php`](file:///Users/macosxdarwin/Desktop/REDIL-CLOUD/resources/views/contenido/paginas/configuracion-general/configuracion-general.blade.php).
  - **Resolución Dinámica de Créditos y Versión**: Si la configuración tiene `nombre_creador` y `url_creador` (o `marca_blanca`), el enlace de autoría se personaliza automáticamente con los datos del tenant; de lo contrario, utiliza los valores predeterminados de la aplicación. Se incluyó además la visualización de la versión del sistema (`version_app` / `version`).

- **Rediseño UI en Dashboard de Clase de Maestros (Septiembre 2026)**:
  - **Tarjetas de Métricas KPI**: Se reemplazaron los badges comprimidos del encabezado por tarjetas tipo KPI responsivas (Total Matriculados, Hombres con porcentaje, Mujeres con porcentaje y Otros condicional) idénticas al estilo de `gestion-novedades.blade.php`, con iconos y colores temáticos (`bg-label-primary`, `bg-label-info`, `bg-label-danger`, `bg-label-secondary`).
  - **Menú Desplegable de Acciones (3 Puntos)**: Se sustituyeron los botones estáticos de "Perfil" y "Bloquear" por un menú `<ul>` desplegable estándar con icono de tres puntos verticales (`ti ti-dots-vertical`), mejorando la distribución de la grilla tanto en desktop como en dispositivos móviles, e incorporando confirmación con SweetAlert2 para la acción de bloqueo de matrícula.

- **Corrección de Relación en Categorías de Actividad (Septiembre 2026)**:
  - **Resolución de `RelationNotFoundException`**: Se corrigieron las relaciones `procesosRequisito` y `procesosCulminados` en `App\Models\ActividadCategoria`, eliminando la llamada inválida `->with('pivot.estadoPasoCrecimiento:id,nombre,color')` que intentaba invocar una relación `pivot()` inexistente sobre el modelo `PasoCrecimiento`. Los datos de la relación en el modelo pivote (`ActividadCategoriaProcesoRequisito` y `ActividadCategoriaProcesoCulminado`) se resuelven dinámicamente a través de sus métodos `estadoPasoCrecimiento()`.
- **Módulo de Novedades y Unificación de Avisos de Actividades (Septiembre 2026)**:
  - **Unificación de Avisos de Bloqueo**: Centralización en `perfil-actividad.blade.php` de todos los motivos de bloqueo (materias no aprobadas, pasos de crecimiento pendientes, tareas de consolidación o restricciones demográficas) tanto para escuelas como para actividades estándar.
  - **Botón Condicional "Registrar novedad"**: Se configuró para mostrarse exclusivamente cuando el usuario es rechazado/bloqueado en el perfil de la actividad, abriendo en una pestaña independiente (`target="_blank"`).
  - **Visibilidad en Catálogo (`proximas-actividades`)**: Ajuste en `ActividadController::proximas` para no ocultar actividades a usuarios autenticados con prerrequisitos pendientes, mostrando un badge de aviso para permitirles consultar los motivos y registrar su novedad.
  - **Formulario Público de Novedades**: Vista dedicada (`actividades.novedades.crear`) con precarga de datos personales, campo condicional de materia/escuela deseada para escuelas, selector dinámico de tipo de novedad, asunto y descripción con contador (máx 500 caracteres). Al registrarse la novedad, se muestra un SweetAlert indicando que será atendida en las próximas 24 horas y, al confirmarse, se redirige automáticamente al perfil de la actividad.
  - **Panel Administrativo Livewire (`GestionNovedades`)**: Ubicado en `Actividades > Novedades`, con filtros reactivos por estado (`no_revisado`, `iniciado`, `finalizado`), fechas, actividad y tipo de novedad. Se migró de una tabla rígida a un Grid de Cards responsive (inspirado en Consolidación), optimizado para dispositivos móviles y tablets con accesos directos a WhatsApp, cambio rápido de estado y botón Ver/Contestar destacado.
  - **Contestación con Envío de Email**: Modal administrativo que actualiza el estado y despacha un correo electrónico personalizado al feligrés utilizando `DefaultMail` con el template corporativo oficial de la iglesia (`resources/views/emails/default-mail.blade.php`).
  - **Gestión de Tipos de Novedad y Excel**: Modal para crear y activar/desactivar opciones de tipos de novedad, y exportación a Excel con `NovedadesExport`.
  - **Migraciones y Permisos Tenant**: Creación de migraciones para `tipos_novedad` y `novedades_actividad`, junto con el seeder `NovedadesPermisosSeeder` (`actividades.ver_novedades` y `actividades.gestionar_novedades`).
  - **Documentación Kaddo**: Documentación del dominio en `knowledge/tech/domains/actividades/current-state.md` y actualización en `_docs_agente/modulos/actividades.md`.
  - **Rediseño UI/UX en Gestión de Formularios**: Transformación completa de `formulario-actividad.blade.php` eliminando franjas de colores estridentes en bordes, reemplazando badges pesados por micro-pills discretos (`Obligatorio`, `Asistencia`, `Oculto`), chips sutiles para opciones de respuesta, botones de acción limpios y un formato diferenciado tipo banner para las secciones/encabezados.
  - **Corrección de Selección Única (`@case(5)`)**: Se corrigió `formulario.blade.php` en el checkout para incluir `@case(5)`, resolviendo el fallo donde los selectores de preguntas de selección única nunca se renderizaban.
  - **Restricción Estricta de Formatos de Archivo**: Restricción tanto en frontend (`accept`) como en backend (`uploadArchivoFormulario`) a estrictamente `.pdf` en documentos (rechazando `.doc`, `.docx`) y `.png, .jpeg, .jpg` en imágenes (rechazando `.webp` u otros).
  - **Validación de Archivos Obligatorios**: Corrección en `guardarFormulario` de `CarritoController` para reconocer el input hidden generado tras la subida asíncrona de Alpine.js, evitando falsos rechazos de campos requeridos.
  - **Persistencia de Respuestas Negativas ("No" / "0")**: Se reemplazó `empty($valor)` por comparación estricta en `AbonoCarrito.php` y `EscuelasCarrito.php` para no omitir respuestas con valor `"0"`.
  - **Módulo de Asistencias**: Resolución dinámica del texto de opciones legibles (`valor_texto`) en lugar de IDs numéricos en `AsistenciasActividad.php`.
  - **Corrección de Selección Múltiple (Checkboxes Reactivos)**: Se solucionó el fallo donde al marcar una opción en preguntas de selección múltiple (checkbox) se marcaban automáticamente todas las demás opciones de la pregunta. Se implementó la inicialización forzada de arrays de strings (`inicializarRespuestasMultiples`) en `mount()`, `cargarRespuestasExistentes()` y `render()` de `Carrito.php`, `AbonoCarrito.php` y `EscuelasCarrito.php`, junto con directivas `wire:key`, migración a `wire:model`, corrección del valor de opción en abonos y asignación de IDs únicos por elemento.
  - **Optimizaciones Livewire**: Asignación automática de orden (`max + 1`), reseteo de variables de modal y refresco de colección `opciones` en `FormularioActividad.php`.

- **Calendario de Actividades en Dashboard (Septiembre 2026)**:
  - **Componente Livewire Reactivo**: Creación de `App\Livewire\Dashboard\CalendarioActividades` (`calendario-actividades.blade.php`) integrando FullCalendar 6 con soporte de vistas Mes, Semana, Día y Lista.
  - **Permiso de Visualización**: Condicionado al permiso `dashboard.dashboard_mostrar_calendario` tanto en Blade como dentro de la autorización del componente Livewire.
  - **Elegibilidad y Categorías Seguras**: Carga únicamente las actividades permitidas para el usuario autenticado (`Actividad::filtrarActividadesPermitidas`) y extrae dinámicamente los tags/categorías asociadas a esas actividades para el panel de filtros, evitando mostrar categorías o eventos no autorizados.
  - **Filtro Estricto de Fechas de Evento**: El calendario compara únicamente con las fechas en que ocurre el evento (`fecha_inicio` y `fecha_finalizacion`), desacoplándose de las fechas de inscripción/oferta (`fecha_visualizacion` y `fecha_cierre`).
  - **Corrección de Vigencia y Actividades Públicas**: Se corrigió `ActividadController::_buildActividadesQuery` y `routes/app.php` para validar vigencia con `fecha_finalizacion >= hoy` en lugar de `fecha_cierre >= hoy` (que ocultaba actividades activas cuando su periodo de inscripción había vencido). Además, se habilitó el bypass directo para actividades con `totalmente_publica = true` en `Actividad::validarAccesoGlobal` y `validarUsuarioEnCategoria`.

- **Sistema Multiformato de Tickets para Iglesia Infantil (Septiembre 2026)**:
  - **4 Modelos Soportados**: Estándar (58 mm), Térmica POS (80 mm con doble talón y corte), Dymo LabelWriter 450 (etiquetas adhesivas/manilla 102×59mm con tipografía grande) y Formato Alargado (20 cm × 9 cm con ficha y pase oficial).
  - **Doble Hoja Obligatoria**: Todos los formatos imprimen 2 hojas independientes: Hoja 1 para el menor (gafete de salón con datos médicos) y Hoja 2 para el adulto responsable (ticket de custodia con QR de salida rápida), con `page-break-after: always`.
  - **Selector Interactivo**: Paso 5 de Check-in con selector visual y persistencia en `localStorage`. Menú contextual de reimpresión rápida en Lista del Turno y barra flotante en la vista del ticket con alternador de modelos y filtros de impresión.

  - **Asistencia Semanal del Periodo (`attendanceTrendChart`)**: Gráfico de líneas/área con ApexCharts en fila dedicada `col-12` que itera semana a semana cubriendo todo el rango del periodo académico, contrastando asistencias (presentes) e inasistencias (ausentes).
  - **Ranking de Calificaciones (Leaderboard Table)**: Tabla/lista estilizada con scroll vertical suave, badges de podio (#1, #2, #3), avatares, barras de progreso y notas promedio destacadas ordenadas de mayor a menor.
- **Escáner QR Continuo en Asistencias (Agosto 2026)**:
  - **Reanudación Automática (`resumeScanner`)**: Corrección de bug donde el visor se quedaba congelado en "Scanner paused" tras registrar asistencia. Ahora se reanuda de forma inmediata tras cerrarse la alerta.
  - **Feedback Visual (SweetAlert2)**: Se reparó el listener de `showAlert` que no disparaba la alerta de confirmación. Ahora muestra el nombre del participante/invitado tanto en registros exitosos como en alertas de asistencia previa ("¡Ya Registrado hoy!").
  - **Escaneo Continuo**: Se optimizó `qr-scanner.blade.php` para mantener la cámara abierta y permitir la lectura fluida y consecutiva de múltiples códigos QR sin recargar la página.
- **Corrección Paginador**: Se arregló el error `Class 'App\Http\Controllers\Paginator' not found` en `proximas-actividades.blade.php`.
- **Corrección Relación `Inscripcion` / `User`**: Se corrigió y aseguró que la relación en `User` y el alias en `Inscripcion` utilicen explícitamente `user_id` en lugar del campo legacy `asistente_id` para evitar errores SQLSTATE[42703] en el listado de alumnos del periodo (`/periodos/{periodo}/alumnos`).
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

  - **Módulo Hitos y Gestión de Permisos (Septiembre 2026)**:
    - **Menú Vertical**: Se aplicó el control de acceso por permisos de Spatie en `verticalMenu.blade.php` para el ítem principal (`hitos.item_hitos`) y sus subítems (`hitos.muro`, `hitos.gestionar`, `hitos.crear`, `hitos.gestionar_denuncias`).
    - **Optimización de Roles y Privilegios**: Se refactorizó `EditarPermisos.php` y `editar-permisos.blade.php` para indexar los IDs de permisos en memoria y evitar consultas repetidas por nombre y excepciones de caché (`PermissionDoesNotExist`).
    - **Activación Masiva de Permisos por Bloque**: Se implementó en `EditarPermisos` y `GestionarRolesPrivilegios` la capacidad de activar y desactivar en bloque todos los permisos pertenecientes a cada grupo (Personas, Grupos, Escuelas, etc.), con indicador visual de cantidad de permisos activos (`X/Y activos`) y botones dedicados por grupo.
    - **Idempotencia de Seeders**: Se mejoró `PermisoSeeder.php` con `Role::firstOrCreate` y soporte para migración segura en entornos multi-tenant con `tenants:seed`.

## Plan Activo

- [ ] Construir Agentes Modulares:
  - [x] Agente Escuelas (Validado + Diagrama + HTML).
  - [x] Agente Actividades (`actividades.md` + Mapa Mental).
  - [x] Agente Actividades-Carrito (`carrito.md` + Flujos diferenciados).
- [x] Agente Usuarios (contexto + integración con Grupos + piloto automatizado de rol activo).
- [ ] Validar flujos de inscripción y compras en producción.
- [ ] Mantener actualizada esta bitácora.
