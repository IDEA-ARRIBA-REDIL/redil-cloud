<?php

use App\Http\Controllers\ActividadController;
use App\Http\Controllers\AlumnoEscuelasController;
use App\Http\Controllers\AsesorPdpController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\BannerEscuelaController;
use App\Http\Controllers\BannerGeneralController;
use App\Http\Controllers\BloqueClasificacionController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\ConfiguracionGeneralController;
use App\Http\Controllers\ConsejeriaController;
use App\Http\Controllers\ConsolidacionController;
use App\Http\Controllers\CorteEscuelaController;
use App\Http\Controllers\CumpleanosController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\EscuelaController;
use App\Http\Controllers\FileViewerController;
use App\Http\Controllers\FiltroConsolidacionController;
use App\Http\Controllers\FinanzaController;
use App\Http\Controllers\FormularioUsuarioController;
use App\Http\Controllers\GestionarTipoDeGruposController;
use App\Http\Controllers\GestionVideosController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\HistorialCalificacionesController;
use App\Http\Controllers\HomologacionController;
use App\Http\Controllers\IglesiaController;
use App\Http\Controllers\InformeEvidenciaGrupoController;
use App\Http\Controllers\InformesController;
use App\Http\Controllers\ListaReproducionController;
use App\Http\Controllers\MaestroController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\NivelAgrupacionController;
use App\Http\Controllers\NivelEscuelaController;
use App\Http\Controllers\NivelesEscuelasController;
use App\Http\Controllers\ParienteUsuarioController;
use App\Http\Controllers\PasosDeCrecimientoController;
use App\Http\Controllers\PeriodoController;
use App\Http\Controllers\PeticionController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PuntoDePagoController;
use App\Http\Controllers\RangoEdadController;
use App\Http\Controllers\RecursoGeneralEscuelaController;
use App\Http\Controllers\ReporteEscuelaController;
use App\Http\Controllers\ReporteGrupoController;
use App\Http\Controllers\ReporteReunionController;
use App\Http\Controllers\ReunionesController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\RuedaDeLaVidaController;
use App\Http\Controllers\SedeController;
use App\Http\Controllers\TaquillaController;
use App\Http\Controllers\TareaConsolidacionController;
use App\Http\Controllers\TemaController;
use App\Http\Controllers\TestPermissionController;
use App\Http\Controllers\ThemeSettingController;
use App\Http\Controllers\TiempoConDiosController;
use App\Http\Controllers\TipoOfrendaController;
use App\Http\Controllers\TipoPagosController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UsuarioConfiguracionController;
use App\Http\Controllers\VersiculoDiarioController;
use App\Http\Controllers\ZonaController;
use App\Http\Controllers\ZonaPagosController;
use App\Livewire\Escuelas\AdminDashboard;
use App\Models\Actividad;
use App\Models\BannerGeneral;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    } else {
        return redirect()->route('login');
    }
});

Route::get('/dashboard', function () {
    $usuario = auth()->user();
    $rolActivo = $usuario->roles()->wherePivot('activo', true)->first();

    if (! $rolActivo) {
        // Fallback: Si no hay rol activo, redirigir a una página de selección de rol o mostrar error amigable
        Auth::logout();

        return redirect()->route('login')->with('error', 'Su usuario aún no tiene un rol activo asignado. Por favor contacte al administrador.');
    }

    $formularioMenores = $rolActivo->formularios()->where('tipo_formulario_id', '=', 4)->get();

    $hoy = now()->toDateString();

    // Filtrar actividades por vigencia
    $actividadesVigentes = Actividad::where('activa', true)
        ->where(function ($query) use ($hoy) {
            $query->whereNull('fecha_visualizacion')
                ->orWhere('fecha_visualizacion', '<=', $hoy);
        })
        ->where(function ($query) use ($hoy) {
            $query->whereNull('fecha_cierre')
                ->orWhere('fecha_cierre', '>=', $hoy);
        })
        ->get();

    // Filtrar actividades por elegibilidad del usuario usando la nueva lógica del modelo
    $permitidasIds = Actividad::filtrarActividadesPermitidas($usuario, $actividadesVigentes);
    $actividades = $actividadesVigentes->whereIn('id', $permitidasIds);

    $banners = BannerGeneral::where('visible', true)
        ->where(function ($query) use ($hoy) {
            $query->whereNull('fecha_inicio')
                ->orWhere('fecha_inicio', '<=', $hoy);
        })
        ->where(function ($query) use ($hoy) {
            $query->whereNull('fecha_fin')
                ->orWhere('fecha_fin', '>=', $hoy);
        })
        ->get();

    $configuracion = Configuracion::find(1);

    return view('contenido.paginas.dashboard', [
        'configuracion' => $configuracion,
        'formularioMenores' => $formularioMenores,
        'actividades' => $actividades,
        'banners' => $banners,
        'rolActivo' => $rolActivo,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/pagina-no-encontrada', function () {
    return view('contenido.paginas.pages-misc-error');
})->name('pagina-no-encontrada');

Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
Route::get('/productos/{producto}/show', [ProductoController::class, 'show'])->name('productos.show');
Route::get('/productos/store', [ProductoController::class, 'store'])->name('productos.store');
Route::post('/productos/create', [ProductoController::class, 'create'])->name('productos.create');
Route::get('/productos/{producto}/edit', [ProductoController::class, 'edit'])->name('productos.edit');
Route::patch('/productos/{producto}/update', [ProductoController::class, 'update'])->name('productos.update');
Route::delete('/productos/{producto}/destroy', [ProductoController::class, 'destroy'])->name('productos.destroy');

Route::get('/pagos/zonapagos/verificar/{pago}', [ZonaPagosController::class, 'verificarEstadoPago'])->name('zonapagos.verificarEstadoPago');

// Usuarios
// Cualquier cosa - guardar darwin

// / ruta de respuesta zonpagos api
Route::get('/pagos/zonapagos/callback', [ZonaPagosController::class, 'handleCallback'])->name('zonapagos.handleCallback');
// Ruta para verificar el estado de un pago de ZonaPagos

// / actividades rutas publicas
Route::get('/actividades/proximas-actividades', [ActividadController::class, 'proximas'])->name('actividades.proximas');
Route::get('/actividades/{actividad}/perfil', [ActividadController::class, 'perfil'])->name('actividades.perfil');
Route::get('/actividades/{inscripcion}/gestionar-inscripciones', [ActividadController::class, 'gestionarInscripciones'])->name('actividades.gestionarInscripciones');
Route::get('/actividades/{actividad}/asistencias', [ActividadController::class, 'asistenciasActividad'])->name('actividades.asistenciasActividad');
Route::get('/inscripcion/{inscripcion}/ticket', [CarritoController::class, 'descargarTicketPdf'])->name('inscripcion.ticket');

Route::get('/preview-email', function () {
    $user = App\Models\User::first() ?? new App\Models\User(['email' => 'ejemplo@correo.com', 'name' => 'Usuario de Prueba']);

    // Pasa datos de ejemplo a tu notificación
    $nombreEjemplo = $user->name;
    $idEjemplo = 'PREVIEW-999';

    return (new App\Notifications\MiVerificacionDeCorreo)->toMail($user);
});

// / checkout rutas publicas
Route::get('/carrito/{actividad}/carrito', [CarritoController::class, 'carrito'])->name('carrito.carrito');
Route::get('/carrito/{compra?}/{actividad}/{primeraVez}/abono', [CarritoController::class, 'abonoCarrito'])->name('carrito.abonoCarrito');
Route::get('/carrito/{compra?}/{actividad}/{primeraVez}/escuelas', [CarritoController::class, 'escuelasCarrito'])->name('carrito.escuelasCarrito');

// Flujo de PAGO
Route::get('/carrito/{compra}/{actividad}/formulario', [CarritoController::class, 'formulario'])->name('carrito.formulario');
Route::post('/carrito/{compra}/guardar-formulario', [CarritoController::class, 'guardarFormulario'])->name('carrito.guardarFormulario');

Route::get('/carrito/{compra}/{actividad}/checkout', [CarritoController::class, 'checkout'])->name('carrito.checkout');
Route::get('/carrito/{actividad}/destinatario', [CarritoController::class, 'destinatario'])->name('carrito.destinatario');
Route::get('/carrito/{pago}/compra-finalizada', [CarritoController::class, 'compraFinalizada'])->name('carrito.compraFinalizada');
Route::get('/carrito/{pago}/descargar-comprobante', [CarritoController::class, 'descargarComprobantePago'])->name('carrito.descargarComprobante'); // Nueva Ruta PDF
Route::get('/carrito/{inscripcion}/{actividad}/inscripcion-finalizada', [CarritoController::class, 'inscripcionFinalizada'])->name('carrito.inscripcionFinalizada');
// En: routes/web.php

Route::get('/carrito/{compra}/eliminar-respuesta/{respuesta}', [CarritoController::class, 'eliminarRespuesta'])->name('carrito.eliminarRespuesta');

// Esta ruta gestionará el clic inicial desde el perfil de la actividad
Route::get('/carrito/{actividad}/iniciar-abono', [CarritoController::class, 'iniciarProcesoAbono'])->name('carrito.iniciarProcesoAbono');

// / rutas publicas escuelas
Route::get('/maestros/{horarioAsignado}/{reporte}/reportar-auto-asistencia', [MaestroController::class, 'reportarAutoAsistencia'])->name('maestros.reportarAutoAsistencia');
Route::get('/maestros/{maestro}/{horarioAsignado}/calificacion-grilla', [MaestroController::class, 'calificacionGrilla'])->name('maestros.calificacionGrilla');
// Nueva ruta para procesar el formulario (POST)
Route::post('/maestros/clase/{horarioAsignado}/asistencia/{reporte}/auto-reporte', [MaestroController::class, 'registrarAutoAsistenciaEstudiante'])
    ->name('maestros.registrarAutoAsistenciaEstudiante');

// usuarios rutas publicas
Route::get('/usuario/{formulario}/inscripcion', [UserController::class, 'nuevo'])->name('usuario.nuevoExterior');
Route::get('/usuario/{formulario}/inscripcion/{grupoId}', [UserController::class, 'nuevo'])->name('usuario.nuevoExteriorConGrupo');
Route::post('/usuario/{formulario}/crear-inscripcion', [UserController::class, 'crear'])->name('usuario.crearInscripcion');

// reuniones rutas publicas
Route::get('/proximas-reuniones', [ReporteReunionController::class, 'proximasReuniones'])->name('reporteReunion.proximasReuniones');
Route::get('/reporteReunion/{reserva}/resumen-de-la-reserva', [ReporteReunionController::class, 'resumenReservaInvitado'])->name('reporteReunion.resumenReservaInvitado');
Route::post('/reporteReunion/reservar-invitado', [ReporteReunionController::class, 'reservarComoInvitado'])->name('reporteReunion.reservarComoInvitado');
Route::get('/reporteReunion/{reserva}/qr-reserva', [ReporteReunionController::class, 'descargarQrReserva'])->name('reporteReunion.descargarQrReserva');
Route::delete('/reserva-reunion/{reserva}/eliminar', [ReporteReunionController::class, 'eliminarReserva'])->name('reporteReunion.eliminarReserva');

// Checkout LMS Cursos Independiente
Route::get('/cursos/campus', [CursoController::class, 'campus'])->name('cursos.campus');
Route::get('/cursos/carrito', [CursoController::class, 'carrito'])->name('cursos.carrito');
Route::get('/cursos/checkout', [CursoController::class, 'checkout'])->name('cursos.checkout');
Route::get('/cursos/compra/finalizada/{carrito}', [CursoController::class, 'compraFinalizada'])->name('cursos.compraFinalizada');
Route::get('/cursos/previsualizar/{slug}', [CursoController::class, 'previsualizar'])->name('cursos.previsualizar');

// Rutas públicas (fuera del middleware auth) /// ESTO NUNCA PERO NUNCA BORRAR  SIRVE PARA LAS RUTAS PUBLICAS
// / voy dejar esto comentariado, para las redirecciones usar el ejemplo de la vista proximas-actividades,
// // ESTO ES LA CONTINUACION DEL PASO 2, LO QUE SUCEDE ACA ES QUE CON EL AJAX QUE ESTA EN LA VISTA EL ACA GUARDA EN UNA VARIABLE DE SESSION LA RUTA QUE DESEAMOS REDIRIGIR CUANDO SE LOGUEE
// / EL PRIMERO LA GUARDA EN UN VARIABLE INTENDED, QUE ES UNA VARIABLE DE SESSION Y LA GUARDA EN CACHE PARA LUEGO TRATARLA EN EL ARCHIVO AUTHENTICATEDSESSIONCONTROLLER.PHP
// / PASO 3 VER EL ARCHIVO
Route::post('/save-redirect-url', function (Request $request) {
    session(['url.intended' => $request->intended_url]);

    return response()->json(['success' => true]);
})->name('save.redirect');

//

// reporte grupo rutas publicas
Route::get('/reportes-grupo/{reporte}/mi-asistencia', [ReporteGrupoController::class, 'miAsistencia'])->name('reporteGrupo.miAsistencia');
Route::post('/reportes-grupo/{reporte}/reportar-mi-asistencia', [ReporteGrupoController::class, 'reportarMiAsistancia'])->name('reporteGrupo.reportarMiAsistancia');

Route::get('/reporteReunion/{reporteReunion}/compartir-link-reserva', [ReporteReunionController::class, 'compartirLinkReserva'])->name('reporteReunion.compartirLinkReserva');

Route::middleware(['auth', 'verified'])->group(function () {

    // Reuniones
    Route::get('/reuniones/nueva', [ReunionesController::class, 'nueva'])->name('reuniones.nueva');
    Route::post('/reuniones/crear', [ReunionesController::class, 'crear'])->name('reuniones.crear');
    Route::get('/reuniones/lista/{tipo?}', [ReunionesController::class, 'lista'])->name('reuniones.lista');
    Route::get('/reuniones/{reunion}/editar', [ReunionesController::class, 'editar'])->name('reuniones.editar');
    Route::delete('/reuniones/{reunion}/dar-baja', [ReunionesController::class, 'darBaja'])->name('reuniones.darBaja');
    Route::delete('/reuniones/{reunion}/eliminar', [ReunionesController::class, 'eliminar'])->name('reuniones.eliminar');
    Route::patch('/reuniones/{reunion}/actualizar', [ReunionesController::class, 'actualizar'])->name('reuniones.actualizar');

    // Reporte Reuniones
    Route::get('/iglesia-virtual', [ReporteReunionController::class, 'iglesiaVirtual'])->name('reporteReunion.iglesiaVirtual');
    Route::post('/reporteReunion/crear/{reunion}', [ReporteReunionController::class, 'crear'])->name('reporteReunion.crear');
    Route::get('/reporteReunion/perfil', [ReporteReunionController::class, 'perfil'])->name('reporteReunion.perfil');
    Route::get('/reporteReunion/nuevo/{reunion}', [ReporteReunionController::class, 'reporte'])->name('reporteReunion.nuevo');
    Route::get('/reporteReuniones/lista/{tipo?}', [ReporteReunionController::class, 'lista'])->name('reporteReunion.lista');
    Route::get('/reporteReunion/{reporteReunion}/editar', [ReporteReunionController::class, 'editar'])->name('reporteReunion.editar');
    Route::delete('/reporteReunion/{reporteReunion}/eliminar', [ReporteReunionController::class, 'eliminar'])->name('reporteReunion.eliminar');
    Route::patch('/reporteReunion/{reporteReunion}/actualizar', [ReporteReunionController::class, 'actualizar'])->name('reporteReunion.actualizar');
    Route::get('/reporteReunion/{reporteReunion}/anadir-servidores', [ReporteReunionController::class, 'añadirServidores'])->name('reporteReunion.añadirServidores');
    Route::get('/reporteReunion/{reporteReunion}/anadir-asistentes', [ReporteReunionController::class, 'añadirAsistentes'])->name('reporteReunion.añadirAsistentes');
    Route::get('/reporteReunion/{reporteReunion}/anadir-reservas', [ReporteReunionController::class, 'añadirReservas'])->name('reporteReunion.añadirReservas');
    Route::get('/reporteReunion/{reporteReunion}/anadir-ingresos', [ReporteReunionController::class, 'añadirIngresos'])->name('reporteReunion.añadirIngresos');

    Route::get('/reportes-reunion/{reporte}/mi-reserva/{user?}', [ReporteReunionController::class, 'miReserva'])->name('reporteReunion.miReserva');
    Route::get('/reporteReunion/{reporteReunion}/{user}/resumen-de-la-reserva', [ReporteReunionController::class, 'resumenReserva'])->name('reporteReunion.resumenReserva');

    Route::get('/reportes-reunion/{reporteReunionId}/exportar-reservas', [ReporteReunionController::class, 'exportarReservasExcel'])->name('reporteReunion.exportarReservasExcel');
    Route::get('/reportes-reunion/{reporteReunionId}/exportar-asistencias', [ReporteReunionController::class, 'exportarAsistenciasExcel'])->name('reporteReunion.exportarAsistenciasExcel');

    Route::post('/reporteReunion/{reporteReunion}/hacer-mi-reserva/{user?}', [ReporteReunionController::class, 'hacerMiReserva'])->name('reporteReunion.hacerMiReserva');

    // Finanzas
    Route::get('/finanzas/ingreso', [FinanzaController::class, 'ingreso'])->name('finanzas.ingreso');

    Route::get('/finanzas/gestionar-ingresos', [FinanzaController::class, 'gestionarIngresos'])->name('finanzas.gestionarIngresos');
    Route::get('/finanzas/exportar-excel', [FinanzaController::class, 'exportarIngresosExcel'])->name('finanzas.exportarIngresosExcel');
    Route::get('/finanzas/exportar-excel-egresos', [FinanzaController::class, 'exportarEgresosExcel'])->name('finanzas.exportarEgresosExcel');
    Route::get('/finanzas/limpiarFiltros', [FinanzaController::class, 'limpiarFiltros'])->name('finanzas.limpiarFiltros');
    Route::get('/finanzas/limpiarFiltrosEgresos', [FinanzaController::class, 'limpiarFiltrosEgresos'])->name('finanzas.limpiarFiltrosEgresos');
    Route::get('/finanzas/egreso', [FinanzaController::class, 'egreso'])->name('finanzas.egreso');
    Route::get('/finanzas/gestionar-egresos', [FinanzaController::class, 'gestionarEgresos'])->name('finanzas.gestionarEgresos');
    Route::get('/finanzas/nuevo', [FinanzaController::class, 'nuevo'])->name('finanzas.nuevo');
    Route::get('/finanzas/imprimir-ingreso/{ingreso}', [FinanzaController::class, 'imprimirIngreso'])->name('finanzas.imprimirIngreso');
    Route::get('/finanzas/imprimir-egreso/{egreso}', [FinanzaController::class, 'imprimirEgreso'])->name('finanzas.imprimirEgreso');

    Route::post('/finanzas/crear-proveedor', [FinanzaController::class, 'crearProveedor'])->name('finanzas.crearProveedor');
    Route::get('/finanzas/crear-documentos', [FinanzaController::class, 'documento'])->name('finanzas.documento');
    Route::post('/finanzas/anular', [FinanzaController::class, 'anular'])->name('finanzas.anular');
    Route::post('/finanzas/anularEgreso', [FinanzaController::class, 'anularEgreso'])->name('finanzas.anularEgreso');
    Route::post('/finanzas/crear-egreso', [FinanzaController::class, 'crearEgreso'])->name('finanzas.crearEgreso');
    Route::post('/finanzas/crear-ingreso', [FinanzaController::class, 'crearIngreso'])->name('finanzas.crearIngreso');
    Route::delete('/finanzas/eliminar-egreso/{finanza}', [FinanzaController::class, 'eliminarEgreso'])->name('finanzas.eliminarEgreso');
    Route::delete('/finanzas/eliminar-ingreso/{finanza}', [FinanzaController::class, 'eliminarIngreso'])->name('finanzas.eliminarIngreso');
    Route::get('/finanzas/estadisticas', [FinanzaController::class, 'estadisticas'])->name('finanzas.estadisticas');

    // / Configuracion Iglesia
    Route::get('/iglesia/{iglesia}/perfil', [IglesiaController::class, 'perfil'])->name('iglesia.perfil');
    Route::patch('/iglesia/{iglesia}/update', [IglesiaController::class, 'update'])->name('iglesia.update');

    // Cumpleaños
    Route::get('/cumpleanos-completos', [CumpleanosController::class, 'listarCumpleanos'])->name('cumpleanos.listarCumpleanos');
    Route::post('/cumpleanos/enviar-correo', [CumpleanosController::class, 'enviarCorreo'])->name('cumpleanos.enviarCorreo');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Prueba (esta se puede eliminar )
    Route::get('/test-permission', [TestPermissionController::class, 'check'])->middleware('auth');

    // Usuarios
    Route::post('/user/roles/switch/{role}', [UserController::class, 'switchRole'])->name('user.roles.switch');
    // Route::get('/usuarios/{tipo?}', [UserController::class, 'listar'])->middleware('permission:personas.subitem_lista_asistentes')->name('usuario.lista');
    Route::get('/usuarios/{tipo?}', [UserController::class, 'listar'])->name('usuario.lista');
    Route::get('/usuario/{formulario}/nuevo', [UserController::class, 'nuevo'])->name('usuario.nuevo');

    Route::middleware('verificarUsuario')->group(function () {
        Route::get('/usuario/{usuario}/perfil', [UserController::class, 'perfil'])->name('usuario.perfil')->withTrashed();
        Route::get('/usuario/{usuario}/perfil-familia', [UserController::class, 'perfilFamilia'])->name('usuario.perfil.familia')->withTrashed();
        Route::get('/usuario/{usuario}/perfil-congregacion', [UserController::class, 'perfilCongregacion'])->name('usuario.perfil.congregacion')->withTrashed();
        Route::get('/usuario/{formulario}/{usuario}/relaciones-familiares', [UserController::class, 'relacionesFamiliares'])->name('usuario.relacionesFamiliares')->withTrashed();
        Route::get('/usuario/{usuario}/historial-escuelas', [UserController::class, 'historialEscuelas'])->name('usuario.historial-escuelas')->withTrashed();
        Route::get('/usuario/{usuario}/descargar-codigo-qr', [UserController::class, 'descargarCodigoQr'])->name('usuario.descargarCodigoQr')->withTrashed();
        Route::get('/usuario/{formulario}/{usuario}/modificar', [UserController::class, 'modificar'])->name('usuario.modificar')->withTrashed();
        Route::get('/usuario/{formulario?}/{usuario}/informacion-congregacional/{tipoUsuarioSugerido?}', [UserController::class, 'informacionCongregacional'])->name('usuario.informacionCongregacional')->withTrashed();
        Route::get('/usuario/{formulario?}/{usuario}/geo-asignacion', [UserController::class, 'geoAsignacion'])->name('usuario.geoAsignacion')->withTrashed();
    });

    Route::post('/usuario/{formulario}/crear', [UserController::class, 'crear'])->name('usuario.crear');
    Route::post('/usuario/{pariente}/eliminar-relacion-familiar', [UserController::class, 'eliminarRelacionFamiliar'])->name('usuario.eliminarRelacionFamiliar');
    Route::post('/usuarios/excel', [UserController::class, 'listadoFinalCsv'])->name('usuario.listadoFinalCsv');
    Route::post('/usuarios/{usuario}/cambiar-contrasena', [UserController::class, 'cambiarContrasena'])->name('usuario.cambiarContrasena');
    Route::post('/usuarios/{usuario}/cambiar-contrasena-default', [UserController::class, 'cambiarContrasenaDefault'])->name('usuario.cambiarContrasenaDefault');
    Route::post('/usuario/no-volver-mostrar-modal-agregar-hijos', [UserController::class, 'noVolverMostrarModalAgregarHijos'])->name('usuario.noVolverMostrarModalAgregarHijos');

    Route::patch('/usuario/{usuario}/informacion-congregacional', [UserController::class, 'actualizarInformacionCongregacional'])->name('usuario.actualizarInformacionCongregacional');
    Route::patch('/usuario/{formulario}/{seccion}/{usuario}/autoeditar', [UserController::class, 'autoeditar'])->name('usuario.autoeditar');
    Route::patch('/usuario/{formulario}/{usuario}/editar', [UserController::class, 'editar'])->name('usuario.editar');
    Route::patch('/usuario/{usuario}/cambiar-foto', [UserController::class, 'cambiarFoto'])->name('usuario.cambiarFoto');
    Route::patch('/usuario/{usuario}/cambiar-portada', [UserController::class, 'cambiarPortada'])->name('usuario.cambiarPortada');

    // Grupos
    Route::get('/grupos/{tipo?}', [GrupoController::class, 'listar'])->name('grupo.lista');

    Route::get('/grupo/nuevo', [GrupoController::class, 'nuevo'])->name('grupo.nuevo');
    Route::get('/grupo/mapa-de-grupos', [GrupoController::class, 'mapaDeGrupos'])->name('grupo.mapaDeGrupos');
    Route::get('/grupo/grafico-del-ministerio/{idNodo?}/{maximosNiveles?}', [GrupoController::class, 'graficoDelMinisterio'])->name('grupo.graficoDelMinisterio');
    Route::get('/grupo/ver-exclusiones', [GrupoController::class, 'verExclusiones'])->name('grupo.verExclusiones');

    Route::get('/grupos/prototipo/dashboard', [GrupoController::class, 'prototipo'])->name('grupo.prototipo');

    Route::get('/grupo/dashboard', [GrupoController::class, 'dashboard'])->name('grupos.dashboard');
    Route::get('/grupo/comparativo', [GrupoController::class, 'comparativo'])->name('grupos.comparativo');
    Route::get('/grupo/dashboard/detalle-kpi', [GrupoController::class, 'detalleKpi'])->name('grupos.detalle-kpi');
    Route::get('/grupo/dashboard/detalle-kpi/exportar', [GrupoController::class, 'exportarDetalleKpi'])->name('grupos.detalle-kpi.exportar');

    Route::middleware('verificarGrupo')->group(function () {
        Route::get('/grupo/{grupo}/perfil/informacion/{encargado?}', [GrupoController::class, 'perfil'])->name('grupo.perfil');
        Route::get('/grupo/{grupo}/perfil/estadisticas-grupo/{encargado?}', [GrupoController::class, 'perfilEstadisticasGrupo'])->name('grupo.perfil.estadisticasGrupo');
        Route::get('/grupo/{grupo}/perfil/estadisticas-cobertura/{encargado?}', [GrupoController::class, 'perfilEstadisticasCobertura'])->name('grupo.perfil.estadisticasCobertura');
        Route::get('/grupo/{grupo}/perfil/integrantes/{encargado?}', [GrupoController::class, 'perfilIntegrantes'])->name('grupo.perfil.integrantes');
        Route::get('/grupo/{grupo}/modificar', [GrupoController::class, 'modificar'])->name('grupo.modificar');
        Route::get('/grupo/{grupo}/gestionar-encargados', [GrupoController::class, 'gestionarEncargados'])->name('grupo.gestionarEncargados');
        Route::get('/grupo/{grupo}/gestionar-integrantes', [GrupoController::class, 'gestionarIntegrantes'])->name('grupo.gestionarIntegrantes');
        Route::get('/grupo/{grupo}/georreferencia', [GrupoController::class, 'georreferencia'])->name('grupo.georreferencia');
    });

    Route::post('/grupo/crear', [GrupoController::class, 'crear'])->name('grupo.crear');
    Route::post('/grupos/excel', [GrupoController::class, 'listadoFinalCsv'])->name('grupo.listadoFinalCsv');
    Route::post('/grupo/crear-exclusion', [GrupoController::class, 'crearExclusion'])->name('grupo.crearExclusion');
    Route::post('/grupo/{grupo}/excluir', [GrupoController::class, 'excluir'])->name('grupo.excluir');
    Route::patch('/grupo/{grupo}/editar', [GrupoController::class, 'editar'])->name('grupo.editar');
    Route::patch('/grupo/{grupo}/cambiar-portada', [GrupoController::class, 'cambiarPortada'])->name('grupo.cambiarPortada');
    Route::patch('/grupo/{tipo}/{id}/cambiar-indicie', [GrupoController::class, 'cambiarIndice'])->name('grupo.cambiarIndice');

    // reporte grupos
    Route::get('/reportes-grupo/{tipo?}', [ReporteGrupoController::class, 'listar'])->name('reporteGrupo.lista');
    Route::get('/reportes-grupo/{reporte}/creado-con-exito', [ReporteGrupoController::class, 'mensajeExitoso'])->name('reporteGrupo.mensajeExitoso');
    Route::get('/reportes-grupo/{reporte}/finalizado', [ReporteGrupoController::class, 'mensajeReporteFinalizado'])->name('reporteGrupo.mensajeReporteFinalizado');
    Route::get('/reportes-grupo/{reporte}/resumen', [ReporteGrupoController::class, 'resumen'])->name('reporteGrupo.resumen');
    Route::get('/reportes-grupo/{reporte}/asistencia', [ReporteGrupoController::class, 'asistencia'])->name('reporteGrupo.asistencia');
    Route::get('/reporte-grupo/{grupo}/nuevo', [ReporteGrupoController::class, 'nuevoReporte'])->name('reporteGrupo.nuevoReporte');

    Route::middleware('verificarGrupo')->group(function () {
        Route::get('/reporte-grupo/{grupo}/nuevo', [ReporteGrupoController::class, 'nuevo'])->name('reporteGrupo.nuevo');
    });

    Route::post('/reporte-grupo/{grupo}/crear', [ReporteGrupoController::class, 'crear'])->name('reporteGrupo.crear');
    Route::post('/reporte-grupo/{reporte}/eliminar', [ReporteGrupoController::class, 'eliminar'])->name('reporteGrupo.eliminar');
    Route::patch('/reporte-grupo/{reporte}/finalizar', [ReporteGrupoController::class, 'finalizar'])->name('reporteGrupo.finalizar');

    // Informe Evidencias Grupo
    Route::middleware('verificarGrupo')->group(function () {
        Route::get('/grupo/{grupo}/crear-informe-evidencia', [InformeEvidenciaGrupoController::class, 'crear'])->name('grupo.informeEvidencia.crear');
        Route::post('/grupo/{grupo}/guardar-informe-evidencia', [InformeEvidenciaGrupoController::class, 'store'])->name('grupo.informeEvidencia.store');
        Route::get('/grupo/{grupo}/listar-informe-evidencia', [InformeEvidenciaGrupoController::class, 'listar'])->name('grupo.informeEvidencia.listar');
        Route::get('/grupo/{grupo}/ver-informe-evidencia/{informe}', [InformeEvidenciaGrupoController::class, 'ver'])->name('grupo.informeEvidencia.ver');
        Route::get('/grupo/{grupo}/editar-informe-evidencia/{informe}', [InformeEvidenciaGrupoController::class, 'editar'])->name('grupo.informeEvidencia.editar');
        Route::patch('/grupo/{grupo}/actualizar-informe-evidencia/{informe}', [InformeEvidenciaGrupoController::class, 'update'])->name('grupo.informeEvidencia.update');
        Route::delete('/grupo/{grupo}/eliminar-informe-evidencia/{informe}', [InformeEvidenciaGrupoController::class, 'eliminar'])->name('grupo.informeEvidencia.eliminar');
        Route::get('/grupo/{grupo}/descargar-informe-evidencia/{informe}', [InformeEvidenciaGrupoController::class, 'descargar'])->name('grupo.informeEvidencia.descargar');
    });

    Route::get('/informes-evidencias-grupo/administrativo', [InformeEvidenciaGrupoController::class, 'listarAdministrativo'])->name('grupo.informesEvidenciaAdministrativo');

    // Sedes
    Route::get('/sedes', [SedeController::class, 'listar'])->name('sede.lista');
    Route::get('/sede/nueva', [SedeController::class, 'nueva'])->name('sede.nueva');
    Route::get('/sede/{sede}/modificar', [SedeController::class, 'modificar'])->name('sede.modificar');
    Route::get('/sede/{sede}/perfil', [SedeController::class, 'perfil'])->name('sede.perfil');

    Route::post('/sede/crear', [SedeController::class, 'crear'])->name('sede.crear');
    Route::post('/sede/{sede}/eliminar', [SedeController::class, 'eliminar'])->name('sede.eliminar');
    Route::patch('/sede/{sede}/editar', [SedeController::class, 'editar'])->name('sede.editar');
    Route::get('/sede/mapa-sedes', [SedeController::class, 'mapa'])->name('sedes.mapa');

    // Peticiones
    Route::get('/peticiones/panel-peticiones', [PeticionController::class, 'panel'])->name('peticion.panel');
    Route::get('/peticiones/gestionar/{tipo?}', [PeticionController::class, 'gestionar'])->name('peticion.gestionar');
    Route::get('/peticion/nueva', [PeticionController::class, 'nueva'])->name('peticion.nueva');
    Route::post('/peticion/crear', [PeticionController::class, 'crear'])->name('peticion.crear');
    Route::post('/peticion/{tipo}/eliminaciones', [PeticionController::class, 'eliminaciones'])->name('peticion.eliminaciones');
    Route::post('/peticion/{id}/eliminacion', [PeticionController::class, 'eliminacion'])->name('peticion.eliminacion');
    Route::post('/peticion/{tipo}/generar-excel', [PeticionController::class, 'generarExcel'])->name('peticion.generarExcel');

    // temas generales
    Route::get('/temas', [TemaController::class, 'listar'])->name('tema.lista');
    Route::get('/tema/nuevo', [TemaController::class, 'nuevo'])->name('tema.nuevo');
    Route::get('/tema/{tema}/ver', [TemaController::class, 'ver'])->name('tema.ver');
    Route::get('/tema/{tema}/actualizar', [TemaController::class, 'actualizar'])->name('tema.actualizar');
    Route::post('/tema/{tema}/eliminar', [TemaController::class, 'eliminar'])->name('tema.eliminar');
    Route::patch('/tema/{tema}/update', [TemaController::class, 'update'])->name('tema.update');
    Route::post('/tema/crear', [TemaController::class, 'crear'])->name('tema.crear');
    Route::post('/tema/cargar', [TemaController::class, 'cargar'])->name('tema.cargar');

    //  relaciones familiares
    Route::get('/familias/gestionar/{userId?}', [ParienteUsuarioController::class, 'gestionar'])->name('familias.gestionar');
    Route::get('/familias/crear', [ParienteUsuarioController::class, 'crear'])->name('familias.crear');
    Route::get('/familias/informes', [ParienteUsuarioController::class, 'informes'])->name('familias.informes');
    Route::post('/familias/{pariente}/eliminar', [ParienteUsuarioController::class, 'eliminar'])->name('familias.eliminar');
    Route::post('/familias/generar-excel', [ParienteUsuarioController::class, 'generarExcel'])->name('familias.generarExcel');

    // actividades
    Route::get('/actividades/nueva', [ActividadController::class, 'nueva'])->name('actividades.nueva');
    Route::get('/actividades/listado', [ActividadController::class, 'listado'])->name('actividades.listado');
    // DESPUÉS
    Route::get('/actividades', [ActividadController::class, 'index'])->name('actividades.index'); // o el nombre que prefieras

    // Mantén las rutas antiguas si las necesitas para otras cosas, pero la principal debe ser 'index'

    Route::post('actividades/{actividad}/duplicar', [ActividadController::class, 'duplicar'])->name('actividades.duplicar');
    Route::get('/actividades/{actividad}/actualizar', [ActividadController::class, 'actualizar'])->name('actividades.actualizar');
    Route::get('/actividades/{actividad}/categorias', [ActividadController::class, 'categorias'])->name('actividades.categorias');
    Route::get('/actividades/{actividad}/dashboard-formularios', [ActividadController::class, 'dashboardFormularios'])->name('actividades.dashboardFormularios');
    Route::get('/actividades/{actividad}/crear-categorias', [ActividadController::class, 'crearCategorias'])->name('actividades.crearCategorias');
    Route::get('/actividades/{actividad}/categorias-escuelas', [ActividadController::class, 'categoriasEscuelas'])->name('actividades.categoriasEscuelas');
    Route::get('/actividades/{actividad}/abonos', [ActividadController::class, 'abonos'])->name('actividades.abonos');
    Route::get('/actividades/{actividad}/crear-categoria', [ActividadController::class, 'crearCategoria'])->name('actividades.crearCategoria');
    Route::get('/actividades/{actividad}/formulario-actividad', [ActividadController::class, 'formularioActividad'])->name('actividades.formularioActividad');
    Route::get('/actividades/{actividad}/encargados', [ActividadController::class, 'encargadosActividad'])->name('actividades.encargadosActividad');

    Route::get('/actividades/{actividad}/multimedia', [ActividadController::class, 'multimedia'])->name('actividades.multimedia');
    // Usamos Route Model Binding para que Laravel nos entregue el objeto Categoria directamente.
    Route::get('/actividades/categorias/{categoria}/editar', [ActividadController::class, 'editarCategoria'])->name('actividades.editarCategoria');

    // Procesa la actualización de una categoría existente.
    Route::put('/actividades/categorias/{categoria}', [ActividadController::class, 'updateCategoria'])->name('actividades.updateCategoria');

    // Muestra el formulario para crear una nueva categoría de tipo escuela.
    Route::get('/actividades/{actividad}/escuelas/categorias/crear', [ActividadController::class, 'crearCategoriaEscuela'])->name('actividades.crearCategoriaEscuela');

    // Guarda la nueva categoría de escuela en la base de datos.
    Route::post('/actividades/{actividad}/escuelas/categorias', [ActividadController::class, 'storeCategoriaEscuela'])->name('actividades.storeCategoriaEscuela');

    // Muestra el formulario para editar una categoría de escuela existente.
    Route::get('/actividades/escuelas/categorias/{categoria}/editar', [ActividadController::class, 'editarCategoriaEscuela'])->name('actividades.editarCategoriaEscuela');

    // Procesa la actualización de una categoría de escuela existente.
    Route::put('/actividades/escuelas/categorias/{categoria}', [ActividadController::class, 'updateCategoriaEscuela'])->name('actividades.updateCategoriaEscuela');

    Route::get('/actividades/{actividad}/checkout', [ActividadController::class, 'checkout'])->name('actividades.checkout');
    Route::post('/actividades/crear', [ActividadController::class, 'crear'])->name('actividades.crear');
    Route::post('/actividades/{banner}/eliminar-banner', [ActividadController::class, 'eliminarBanner'])->name('actividades.eliminarBanner');
    Route::post('/actividades/{banner}/eliminar-video', [ActividadController::class, 'eliminarVideo'])->name('actividades.eliminarVideo');
    Route::patch('/actividades/{actividad}/update', [ActividadController::class, 'update'])->name('actividades.update');
    Route::patch('/actividades/{actividad}/update-escuelas', [ActividadController::class, 'updateEscuelas'])->name('actividades.updateEscuelas');
    Route::post('/actividades/{actividad}/cancelar', [ActividadController::class, 'cancelar'])->name('actividades.cancelar');
    Route::post('/actividades/{actividad}/activar', [ActividadController::class, 'activar'])->name('actividades.activar');
    Route::post('/actividades/{categoria}/eliminar-categoria', [ActividadController::class, 'eliminarCategoria'])->name('actividades.eliminarCategoria');
    Route::patch('/actividades/{actividad}/upload-banner', [ActividadController::class, 'uploadBanner'])->name('actividades.uploadBanner');
    Route::patch('/actividades/{actividad}/new-video', [ActividadController::class, 'newVideo'])->name('actividades.newVideo');

    Route::post('/actividades/{actividad}/categorias', [ActividadController::class, 'storeCategoria'])->name('actividades.storeCategoria');

    // Ruta para el módulo de matrículas
    Route::get('gestionar-matriculas/{user}', [MatriculaController::class, 'gestionar'])->name('matriculas.gestionar');
    Route::get('/matriculas/gestionar-traslados/{user?}', [MatriculaController::class, 'gestionarTraslados'])->name('matriculas.gestionarTraslados');
    Route::get('/matriculas/{matricula}/{user}/eliminar-admin', [MatriculaController::class, 'eliminarMatricula'])->name('matriculas.eliminarMatricula');

    // SOLICITUDES DE TRASLADO (Estudiante)
    Route::get('/escuelas/{usuario?}/solicitar-traslado', [MatriculaController::class, 'solicitarTraslado'])->name('matriculas.solicitarTraslado');

    // GESTION DE SOLICITUDES DE TRASLADO (Admin)
    Route::get('/escuelas/matriculas/solicitudes-traslado', [MatriculaController::class, 'gestionarSolicitudesTraslado'])->name('matriculas.solicitudesTraslado');

    // puntos de pago
    Route::get('/puntos-de-pago', [PuntoDePagoController::class, 'listar'])->name('puntosDePago.listado');
    Route::post('/puntos-de-pago/crear', [PuntoDePagoController::class, 'crear'])->name('puntosDePago.crear');
    Route::get('/puntos-de-pago/{puntoDePago}/informe', [PuntoDePagoController::class, 'verInforme'])->name('puntos_de_pago.ver_informe'); // Nuevo reporte
    Route::get('/puntos-de-pago/gestionar', [PuntoDePagoController::class, 'gestionar'])->name('puntosDePago.gestionar');
    Route::get('/taquillas/gestionar', [CajaController::class, 'gestionar'])->name('taquillas.gestionar');

    Route::controller(AsesorPdpController::class)->prefix('asesores-pdp')->name('asesores_pdp.')->group(function () {
        Route::get('/gestionar', 'gestionar')->name('gestionar');
        Route::post('/guardar', 'guardar')->name('guardar');
        Route::post('/eliminar', 'eliminar')->name('eliminar');
        Route::post('/activar/{asesor}', 'activar')->name('activar');
        Route::post('/desactivar/{asesor}', 'desactivar')->name('desactivar');
    });

    // / taquillas donde se hace todo el procedimiento de venta
    // ¡NUEVA RUTA!
    // Esta es la página de selección de caja.
    Route::get('/mis-cajas', [TaquillaController::class, 'misCajas'])->name('taquilla.mis-cajas');

    // ¡RUTA MODIFICADA!
    // Ahora acepta el ID de la caja.
    Route::get('/taquilla/operar/{cajaActiva}', [TaquillaController::class, 'operar'])->name('taquilla.operar'); // <-- ¡Nombre cambiado!
    /**
     * ¡RUTA MODIFICADA!
     * Esta es la ruta de la página de checkout.
     * Ahora acepta un ID de {comprador} (quien paga) y un ID de {inscrito} (quien asiste).
     */
    Route::get('/taquilla/{cajaActiva}/procesar/{comprador}/{inscrito}/{actividad}/{categoria}/{modo}', [TaquillaController::class, 'mostrarPaginaDePago'])
        ->name('taquilla.mostrarPaginaDePago'); // <- Nombre de ruta actualizado

    Route::get('/taquilla/procesar-venta/{usuario}/{actividad}/{categoria}', [TaquillaController::class, 'procesarVentaMockup'])->name('taquilla.procesarVentaMockup');
    Route::get('/taquilla/compra-finalizada/{compra}', [TaquillaController::class, 'compraFinalizada'])->name('taquilla.compraFinalizada');
    Route::get('/taquilla/historial/{cajaActiva}', [TaquillaController::class, 'historialTransacciones'])->name('taquilla.historialTransacciones');

    // Rutas para modificación de pagos y auditoría
    // Rutas de Anulación
    Route::post('/taquilla/solicitar-anulacion/{compra}', [TaquillaController::class, 'solicitarAnulacion'])->name('taquilla.solicitarAnulacion');
    Route::get('/taquilla/solicitudes-anulacion', [TaquillaController::class, 'listarSolicitudesAnulacion'])->name('taquilla.listarSolicitudesAnulacion');
    Route::post('/taquilla/autorizar-anulacion/{compra}', [TaquillaController::class, 'autorizarAnulacion'])->name('taquilla.autorizarAnulacion');
    Route::post('/taquilla/rechazar-anulacion/{compra}', [TaquillaController::class, 'rechazarAnulacion'])->name('taquilla.rechazarAnulacion');

    Route::get('/taquilla/historial-modificaciones', [TaquillaController::class, 'historialModificaciones'])->name('taquilla.historialModificaciones');

    // // escuelas
    Route::get('/escuelas/dashboard', [EscuelaController::class, 'panel'])->name('escuelas.dashboard');
    Route::get('/escuelas/gestionar', [EscuelaController::class, 'gestionarEscuelas'])->name('escuelas.gestionarEscuelas');
    Route::get('/escuelas/{escuela}/actualizar', [EscuelaController::class, 'actualizar'])->name('escuelas.actualizar');
    Route::get('/escuelas/{escuela}/materias', [EscuelaController::class, 'materias'])->name('escuelas.materias');
    Route::get('/escuela/recursos-generales', [RecursoGeneralEscuelaController::class, 'index'])->name('escuela.recursos-generales');
    Route::get('/escuelas/dashboard-administrativo', AdminDashboard::class)->name('escuelas.adminDashboard');
    Route::get('/escuelas/historial/boletin/{materiaAprobadaUsuario}', [HistorialCalificacionesController::class, 'exportarBoletin'])
        ->name('escuelas.historial.exportar-boletin');
    Route::get('/escuelas/alumno/mis-calificaciones', [AlumnoEscuelasController::class, 'historialAcademico'])->name('escuelas.alumno.historial');
    // Cambiamos 'mis-recursos' por 'misRecursos'
    Route::get('/escuela/mis-recursos', [RecursoGeneralEscuelaController::class, 'misRecursos'])->name('escuela.mis-recursos');
    Route::post('/escuelas/guardar', [EscuelaController::class, 'guardar'])->name('escuelas.guardar');
    Route::post('/escuelas/{escuela}/update', [EscuelaController::class, 'update'])->name('escuelas.update');
    Route::post('/escuelas/{user}/gestionar-horarios', [EscuelaController::class, 'gestionarHorarios'])->name('escuelas.gestionarHorarios');
    Route::get('/escuelas/{escuela}/exportar-matriculas-activas', [EscuelaController::class, 'exportarMatriculasActivas'])
        ->name('escuelas.exportarMatriculasActivas');

    // / Banners escuelas
    Route::get('/banner-escuelas/gestionar', [BannerEscuelaController::class, 'gestionar'])->name('banner-escuela.gestionar');

    // // alumnos
    Route::get('/alumnos/{horario}/mi-materia', [AlumnoEscuelasController::class, 'perfilMateria'])->name('alumnos.perfilMateria');
    Route::get('/alumnos/{user}/dashboard', [AlumnoEscuelasController::class, 'dashboard'])->name('alumnos.dashboard');

    // Ruta para mostrar/editar/eliminar una escuela específica

    // /ruta historial calificaciones
    Route::get('/escuelas/historial-calificaciones', [HistorialCalificacionesController::class, 'index'])->name('escuelas.historialCalificaciones');

    // / cursos (LMS)
    Route::get('/cursos/gestionar', [CursoController::class, 'index'])->name('cursos.gestionar');
    Route::get('/cursos/crear', [CursoController::class, 'crear'])->name('cursos.crear');
    Route::get('/cursos/{curso}/editar', [CursoController::class, 'editar'])->name('cursos.editar');
    Route::get('/cursos/{curso}/restricciones', [CursoController::class, 'restricciones'])->name('cursos.restricciones');
    Route::get('/cursos/{curso}/detalle', [CursoController::class, 'detalle'])->name('cursos.detalle');
    Route::put('/cursos/{curso}/detalle/descripcion', [CursoController::class, 'actualizarDescripcion'])->name('cursos.actualizarDescripcion');
    Route::get('/cursos/{curso}/contenido', [CursoController::class, 'contenido'])->name('cursos.contenido');
    // Nuevo: Gestión de Estudiantes
    Route::get('/cursos/{curso}/inscritos', [CursoController::class, 'inscritos'])->name('cursos.inscritos');

    // Nuevo: Panel Foro (LMS)
    Route::get('/cursos/foro', [CursoController::class, 'foro'])->name('cursos.foro');

    // Nuevo: Campus del Alumno (LMS)
    Route::get('/cursos/{slug}/mi-campus', function ($slug) {
        $curso = \App\Models\Curso::where('slug', $slug)->firstOrFail();

        return view('contenido.paginas.cursos.campus', [
            'slug' => $slug,
            'curso' => $curso,
        ]);
    })->name('cursos.mi-campus');

    Route::get('/cursos/{curso}/equipo', [CursoController::class, 'equipo'])->name('cursos.equipo');
    Route::post('/cursos/{curso}/equipo', [CursoController::class, 'guardarEquipo'])->name('cursos.equipo.guardar');
    Route::post('/cursos/equipo/activar/{miembro}', [CursoController::class, 'activarEquipo'])->name('cursos.equipo.activar');
    Route::post('/cursos/equipo/desactivar/{miembro}', [CursoController::class, 'desactivarEquipo'])->name('cursos.equipo.desactivar');
    Route::post('/cursos/equipo/eliminar', [CursoController::class, 'eliminarEquipo'])->name('cursos.equipo.eliminar');
    Route::get('/cursos/recurso/preview/{leccionId}', [FileViewerController::class, 'preview'])->name('cursos.recurso.preview');
    // Rutas Públicas de Cursos (Catálogo / Detalles front-end)

    // / materias
    Route::get('/materias/{escuela}/crear', [MateriaController::class, 'crear'])->name('materias.crear');
    Route::get('/materias/{materia}/gestionar', [MateriaController::class, 'gestionar'])->name('materias.gestionar');
    Route::get('/materias/{materia}/horarios', [MateriaController::class, 'horarios'])->name('materias.horarios');
    Route::get('/materias/{materia}/modelo', [MateriaController::class, 'modelo'])->name('materias.modelo');
    Route::post('/materias/{escuela}/guardar', [MateriaController::class, 'guardar'])->name('materias.guardar');
    Route::post('/materias/{materia}/actualizar', [MateriaController::class, 'actualizar'])->name('materias.actualizar');
    Route::post('/materias/{materia}', [MateriaController::class, 'eliminar'])->name('materias.eliminar');



    // / NivelesEscuelas (Sistema desde cero)
    Route::get('/niveles-escuelas/{escuela}/crear', [NivelesEscuelasController::class, 'crear'])->name('niveles-escuelas.crear');
    Route::post('/niveles-escuelas/{escuela}/guardar', [NivelesEscuelasController::class, 'guardar'])->name('niveles-escuelas.guardar');

    Route::put('/materias-rapido/{materia}', [MateriaController::class, 'actualizarMateriaRapido'])->name('materias.actualizarRapido');

    // / aulas
    Route::get('/aulas/gestionar', [AulaController::class, 'gestionar'])->name('aulas.gestionar');
    Route::get('/aulas/{aula}/eliminar', [AulaController::class, 'eliminar'])->name('aulas.eliminar');
    Route::post('/aulas/guardar', [AulaController::class, 'guardar'])->name('aulas.guardar');
    Route::post('/aulas/{aula}/editar', [AulaController::class, 'editar'])->name('aulas.editar');
    Route::put('/aulas/actualizar', [AulaController::class, 'update'])->name('aulas.actualizar');
    Route::get('/aulas/exportar', [AulaController::class, 'exportarExcel'])->name('aulas.exportar');

    // periodos
    Route::get('/periodos/gestionar', [PeriodoController::class, 'gestionar'])->name('periodo.gestionar');
    Route::get('/periodos/crear', [PeriodoController::class, 'crear'])->name('periodo.crear');
    Route::get('/periodos/{periodo}/actualizar', [PeriodoController::class, 'actualizar'])->name('periodo.actualizar');
    Route::get('/periodos/{periodo}/cortes', [PeriodoController::class, 'cortes'])->name('periodo.cortes');
    Route::get('/periodos/{materiaPeriodo}/horarios', [PeriodoController::class, 'horarios'])->name('periodo.horarios');
    Route::get('/periodos/{periodo}/materias', [PeriodoController::class, 'materias'])->name('periodo.materias');
    Route::get('/periodos/{periodo}/alumnos', [PeriodoController::class, 'alumnos'])->name('periodo.alumnos');
    Route::get('/periodos/{periodo}/exportar-horarios', [PeriodoController::class, 'exportarHorarios'])->name('periodo.exportarHorarios');
    Route::get('/periodos/{periodo}/informe-final', [PeriodoController::class, 'informeFinal'])->name('periodo.informe-final');
    Route::post('/periodos/guardar', [PeriodoController::class, 'guardar'])->name('periodo.guardar');
    Route::post('/periodos/{periodo}', [PeriodoController::class, 'procesarActualizacion'])->name('periodo.procesarActualizacion');
    Route::post('/periodos/{periodo}/finalizar', [PeriodoController::class, 'finalizar'])->name('periodo.finalizar');
    Route::post('/periodos/{periodo}/activar', [PeriodoController::class, 'activar'])->name('periodo.activar');

    Route::get('/periodos/materia/{materiaPeriodo}/exportar-informe', [PeriodoController::class, 'exportarInformeFinalMateria'])->name('periodo.materia.exportar-informe');

    // // Homologaciones
    Route::get('/escuelas/homologaciones', [HomologacionController::class, 'index'])->name('escuelas.homologaciones');

    // / Reportes escuelas
    // Ruta para mostrar el formulario de filtros del reporte
    Route::get('/reportes/escuelas/asistencia', [ReporteEscuelaController::class, 'vistaFiltros'])
        ->name('reporteEscuela.vistaFiltros');

    // Ruta para procesar el formulario y mostrar el reporte generado
    Route::post('/reportes/escuelas/asistencia/generar', [ReporteEscuelaController::class, 'generarReporte'])
        ->name('reporteEscuela.generarReporte');

    Route::post('/reportes/escuela/exportar', [ReporteEscuelaController::class, 'exportarReporte'])->name('reporteEscuela.exportarReporte');
    // routes/web.php
    Route::post('/reportes/escuela/exportar-resumen', [ReporteEscuelaController::class, 'exportarReporteResumen'])->name('reporteEscuela.exportarResumen');

    // cortes escyeka
    Route::put('/cortes-escuela/{corte}/update', [CorteEscuelaController::class, 'update'])->name('cortes_escuela.update');
    Route::delete('/cortes-escuela/{corte}/destroy', [CorteEscuelaController::class, 'destroy'])->name('cortes_escuela.destroy');

    // / maestros
    Route::get('/maestros/gestionar', [MaestroController::class, 'gestionar'])->name('maestros.gestionar');
    Route::get('/maestros/{maestro}/horarios-asignados', [MaestroController::class, 'horariosAsignados'])->name('maestros.horariosAsignados');

    Route::get('/maestros/{maestro}/{horarioAsignado}/editar-reporte/{reporte}', [MaestroController::class, 'editarReporte'])
        ->name('maestros.editarReporte');

    Route::get('/maestros/{user}/mis-horarios', [MaestroController::class, 'misHorarios'])->name('maestros.misHorarios');
    Route::get('/maestros/{maestro}/{horarioAsignado}/gestionar-clase', [MaestroController::class, 'gestionarClase'])
        ->name('maestros.gestionarClase');
    Route::get('/maestros/{maestro}/{horarioAsignado}/calificacion-multiple', [MaestroController::class, 'calificacionMultiple'])
        ->name('maestros.calificacionMultiple');
    Route::get('/maestros/{maestro}/{horarioAsignado}/reporte-asistencia', [MaestroController::class, 'reporteAsistencia'])
        ->name('maestros.reporteAsistencia');
    Route::get('/maestros/{maestro}/{horarioAsignado}/dashboard-clase', [MaestroController::class, 'dashboardClase'])
        ->name('maestros.dashboardClase');

    Route::get('/maestros/{maestro}/{horarioAsignado}/{alumno}/gestionar-alumno', [MaestroController::class, 'gestionarAlumno'])
        ->name('maestros.gestionarAlumno');
    Route::post('/maestros/guardar', [MaestroController::class, 'guardar'])->name('maestros.guardar');
    Route::post('/maestros/eliminar', [MaestroController::class, 'eliminar'])->name('maestros.eliminar');
    Route::get('/maestros/{horarioAsignado}/{maestro}/recursos', [MaestroController::class, 'recursosAlumnos'])->name('maestros.recursosAlumnos');
    Route::post('/maestros/{maestro}/activar', [MaestroController::class, 'activar'])->name('maestros.activar');
    Route::post('/maestros/{maestro}/desactivar', [MaestroController::class, 'desactivar'])->name('maestros.desactivar');

    // En routes/web.php (o tu archivo de rutas)
    Route::post('/maestros/{maestro}/horarios/{horarioAsignado}/reporte-asistencia/guardar-cabecera', [MaestroController::class, 'guardarNuevoReporteAsistenciaClase'])->name('maestros.guardarNuevoReporteAsistenciaClase');
    // Rutas para la asignación de horarios a maestros
    Route::get('/maestros/{maestro}/horarios-asignados', [MaestroController::class, 'horariosAsignados'])->name('maestros.horariosAsignados');
    Route::post('/maestros/{maestro}/horarios-asignados', [MaestroController::class, 'guardarHorarioAsignado'])->name('maestros.guardarHorarioAsignado');

    // Nueva ruta para gestionar ítems de evaluación
    Route::get('/maestros/{horarioAsignado}/gestionar-items', [MaestroController::class, 'gestionarItems'])->name('maestros.gestionarItems');

    Route::delete('/maestros/{maestro}/horarios-asignados/{horarioMateriaPeriodo}', [MaestroController::class, 'eliminarHorarioAsignado'])->name('maestros.eliminarHorarioAsignado'); // Usando Route Model Binding para ambos

    // / rueda de la vida
    Route::get('/rueda-vida/nueva', [RuedaDeLaVidaController::class, 'nueva'])->name('ruedaDeLaVida.nueva');
    Route::get('/rueda-vida/gestor', [RuedaDeLaVidaController::class, 'gestor'])->name('ruedaDeLaVida.gestor');
    Route::get('/rueda-vida/historial', [RuedaDeLaVidaController::class, 'historial'])->name('ruedaDeLaVida.historial');
    Route::get('/rueda-vida/bienvenida', [RuedaDeLaVidaController::class, 'bienvenida'])->name('ruedaDeLaVida.bienvenida');
    Route::get('/rueda-vida/finalizada', [RuedaDeLaVidaController::class, 'finalizada'])->name('ruedaDeLaVida.finalizada');
    Route::get('/rueda-vida/{rueda}/resumen', [RuedaDeLaVidaController::class, 'resumen'])->name('ruedaDeLaVida.resumen');
    Route::patch('/rueda-vida/crear', [RuedaDeLaVidaController::class, 'crear'])->name('ruedaDeLaVida.crear');

    // Consejerias o Calendario de citas

    Route::get('/consejeria/mis-citas/{tipo?}', [ConsejeriaController::class, 'misCitas'])->name('consejeria.misCitas');
    Route::get('/consejeria/gestionar-consejeros', [ConsejeriaController::class, 'gestionarConsejeros'])->name('consejeria.gestionarConsejeros');
    Route::get('/consejeria/nueva-cita/{usuario}', [ConsejeriaController::class, 'nuevaCita'])->name('consejeria.nuevaCita');
    Route::get('/consejeria/reprogramar-cita/{cita}', [ConsejeriaController::class, 'reprogramarCita'])->name('consejeria.reprogramarCita');
    Route::get('/consejeria/{consejero}/configurar-horarios-consejero', [ConsejeriaController::class, 'configurarHorariosCosejero'])->name('consejeria.configurarHorariosCosejero');
    Route::get('/consejeria/{consejero}/calendario-de-fechas-consejero', [ConsejeriaController::class, 'calendarioDeFechasConsejero'])->name('consejeria.calendarioDeFechasConsejero');
    Route::get('/consejeria/{consejero}/obtener-horarios-calendario-consejero', [ConsejeriaController::class, 'obtenerHorariosCalendario'])->name('consejeria.obtenerHorariosCalendario');
    Route::get('/consejeria/{cita}/cita-exitosa', [ConsejeriaController::class, 'mensajeExitoso'])->name('consejeria.mensajeExitoso');
    Route::get('/consejeria/calendario-citas', [ConsejeriaController::class, 'calendarioCitas'])->name('consejeria.calendarioCitas');
    Route::get('/consejeria/obtener-citas-calendario', [ConsejeriaController::class, 'obtenerCitasCalendario'])->name('consejeria.obtenerCitasCalendario');

    Route::post('/consejeria/crear-cita', [ConsejeriaController::class, 'crearCita'])->name('consejeria.crearCita');
    Route::post('/consejeria/crear-consejero', [ConsejeriaController::class, 'crearConsejero'])->name('consejeria.crearConsejero');
    Route::post('/consejeria/{consejero}/actualizar-horario-habitual', [ConsejeriaController::class, 'actualizarHorarioHabitual'])->name('consejeria.actualizarHorarioHabitual');
    Route::post('/consejeros/{consejero}/horario-extendido', [ConsejeriaController::class, 'addHorarioExtendido'])->name('consejeria.addHorarioExtendido');
    Route::post('/consejeros/{consejero}/horario-bloqueado', [ConsejeriaController::class, 'addHorarioBloqueado'])->name('consejeria.addHorarioBloqueado');
    Route::post('/consejeria/{cita}/cancelar-cita', [ConsejeriaController::class, 'cancelarCita'])->name('consejeria.cancelarCita');
    Route::post('/consejeria/{cita}/concluir', [ConsejeriaController::class, 'concluirCita'])->name('consejeria.concluirCita');

    Route::patch('/consejeria/{consejero}/activar', [ConsejeriaController::class, 'activar'])->name('consejeria.activar');
    Route::patch('/consejeria/{consejero}/desactivar', [ConsejeriaController::class, 'desactivar'])->name('consejeria.desactivar');
    Route::patch('/consejeria/actualizar/{consejero}', [ConsejeriaController::class, 'actualizarConsejero'])->name('consejeria.actualizarConsejero');
    Route::patch('/consejeria/horario-adicional/{id}', [ConsejeriaController::class, 'actualizarHorarioAdicional'])->name('consejeria.actualizarHorarioAdicional');
    Route::patch('/consejeria/horario-bloqueado/{id}', [ConsejeriaController::class, 'actualizarHorarioBloqueado'])->name('consejeria.actualizarHorarioBloqueado');

    Route::delete('/consejeria/{consejero}/eliminar-consejero}', [ConsejeriaController::class, 'eliminarConsejero'])->name('consejeria.eliminarConsejero'); // Usando Route Model Binding para ambos
    Route::delete('/consejeria/horario-adicional/{id}', [ConsejeriaController::class, 'eliminarHorarioAdicional'])->name('consejeria.eliminarHorarioAdicional');
    Route::delete('/consejeria/horario-bloqueado/{id}', [ConsejeriaController::class, 'eliminarHorarioBloqueado'])->name('consejeria.eliminarHorarioBloqueado');

    // Tiempo con DIOS
    Route::get('/tiempo-con-Dios/nuevo', [TiempoConDiosController::class, 'nuevo'])->name('tiempoConDios.nuevo');
    Route::get('/tiempo-con-Dios/historial', [TiempoConDiosController::class, 'historial'])->name('tiempoConDios.historial');
    Route::get('/tiempo-con-Dios/bienvenida', [TiempoConDiosController::class, 'bienvenida'])->name('tiempoConDios.bienvenida');
    Route::get('/tiempo-con-Dios/{tiempoConDios}/resumen', [TiempoConDiosController::class, 'resumen'])->name('tiempoConDios.resumen');
    Route::post('/tiempo-con-Dios/crear', [TiempoConDiosController::class, 'crear'])->name('tiempoConDios.crear');

    // Versículos Diarios
    Route::resource('versiculos', VersiculoDiarioController::class);

    // Publicaciones (Posts)
    Route::get('/posts/gestionar', [PostController::class, 'gestionar'])->name('posts.gestionar');
    Route::get('/posts/crear', [PostController::class, 'crear'])->name('posts.crear');
    Route::post('/posts/store', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::patch('/posts/{post}/update', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}/destroy', [PostController::class, 'destroy'])->name('posts.destroy');

    // informes
    Route::get('/informe/{tipoInforme?}', [InformesController::class, 'listar'])->name('informe.lista');
    Route::get('/informes/configuracion-semanas', [InformesController::class, 'configuracionSemanas'])->name('informe.configuracionSemanas');
    Route::get('/informes/cambiar-estado/{informe}', [InformesController::class, 'cambiarEstado'])->name('informe.cambiarEstado');

    Route::get('/informe/grupos-no-reportados/{informe}', [InformesController::class, 'informeDeGruposNoReportados'])->name('informe.informeDeGruposNoReportados');
    Route::get('/informes/{informe}/exportar', [InformesController::class, 'exportarInformeDeGruposNoReportados'])->name('informes.exportarInformeDeGruposNoReportados');

    Route::get('/informe/asistencia-semanal-grupos/{informe}', [InformesController::class, 'informeAsistenciaSemanalGrupos'])->name('informe.informeAsistenciaSemanalGrupos');
    Route::get('/informes/asistencia-semanal-grupos/{informe}/exportar', [InformesController::class, 'exportarInformeAsistenciaSemanalGrupos'])->name('informes.exportarInformeAsistenciaSemanalGrupos');

    Route::get('/informes/compras/{informe?}', [InformesController::class, 'informeCompras'])->name('informes.compras');
    Route::get('/informes/pagos/{informe?}', [InformesController::class, 'informePagos'])->name('informes.pagos');

    // Bloques Clasificacion Asistentes
    Route::get('/bloques-clasificacion', [BloqueClasificacionController::class, 'index'])->name('bloques-clasificacion');

    // Route::get('/', [HomePage::class, 'index'])->name('pages-home');
    // Route::get('/page-2', [Page2::class, 'index'])->name('pages-page-2');

    // pages
    // Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');

    // authentication
    // Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
    // Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');

    // gestionar formularios
    Route::get('/formularios', [FormularioUsuarioController::class, 'listar'])->name('formularioUsuario.lista');
    Route::get('/formularios/campos', [FormularioUsuarioController::class, 'listarCampos'])->name('formularioUsuario.listaCampos');
    Route::get('/formulario/nuevo', [FormularioUsuarioController::class, 'nuevo'])->name('formularioUsuario.nuevo');
    Route::get('/formulario/{formulario}/modificar', [FormularioUsuarioController::class, 'modificar'])->name('formularioUsuario.modificar');
    Route::get('/formulario/{formulario}/secciones-y-campos', [FormularioUsuarioController::class, 'seccionesCampos'])->name('formularioUsuario.seccionesCampos');

    Route::post('/formulario/crear', [FormularioUsuarioController::class, 'crear'])->name('formularioUsuario.crear');
    Route::patch('/formulario/{formulario}/editar', [FormularioUsuarioController::class, 'editar'])->name('formularioUsuario.editar');

    // gestionar lista de reproducción
    Route::get('/gestionar-lista-de-reproduccion', [ListaReproducionController::class, 'listar'])->name('configuracion.gestionar-lista-reproduccion');

    // roles y privilegios
    Route::get('/configuracion/gestionar-roles', [RolController::class, 'gestionar'])->name('configuracion.gestionar-roles');

    // Nueva ruta para editar permisos de un rol (Standalone)
    Route::get('/configuracion/gestionar-roles/{role}/permisos', [RolController::class, 'editarPermisos'])->name('configuracion.editar-permisos-rol');

    // zonas
    Route::get('/configuracion/gestionar-zonas', [ZonaController::class, 'gestionar'])->name('configuracion.gestionar-zonas');

    // Configuracion general
    Route::get('/configuracion-general', [ConfiguracionGeneralController::class, 'configuracionGeneral'])->name('configuracion-general.configuracionGeneral');
    Route::patch('/configuracion-general/actualizar', [ConfiguracionGeneralController::class, 'actualizar'])->name('configuracion-general.actualizar');

    // Gestionar pasos de crecimiento
    Route::get('/gestionar-pasos-de-crecimiento', [PasosDeCrecimientoController::class, 'pasosDeCrecimiento'])->name('gestionar-pasos-de-crecimiento.pasosDeCrecimiento');
    Route::patch('/gestionar-pasos-de-crecimiento/crear', [PasosDeCrecimientoController::class, 'crear'])->name('gestionar-pasos-de-crecimiento.crear');

    // Gestionar tipos de grupo
    Route::get('/gestionar-tipos-de-grupos/modificar', [GestionarTipoDeGruposController::class, 'modificarTipoDeGrupo'])->name('gestionar-tipos-de-grupos.modificarTipoDeGrupo'); // Muestra la lista
    // En web.php

    // Ruta para procesar la actualización de un grupo específico
    Route::patch('/gestionar-tipos-de-grupos/{tipoGrupo}', [GestionarTipoDeGruposController::class, 'actualizarTipoDeGrupo'])->name('gestionar-tipos-de-grupos.actualizarTipoDeGrupo');

    Route::get('/gestionar-tipos-de-grupos/listar', [GestionarTipoDeGruposController::class, 'listar'])->name('gestionar-tipos-de-grupos.listar'); // Muestra el formulario para crear
    Route::get('/gestionar-tipos-de-grupos/nuevo', [GestionarTipoDeGruposController::class, 'nuevo'])->name('gestionar-tipos-de-grupos.nuevo');
    Route::post('/gestionar-tipos-de-grupos/crear', [GestionarTipoDeGruposController::class, 'crearTipoDeGrupo'])->name('gestionar-tipos-de-grupos.crearTipoDeGrupo'); // Procesa la creación

    // RUTAS SUGERIDAS PARA EDITAR Y ELIMINAR
    // Ruta para mostrar el formulario de edición de un grupo específico
    Route::get('/gestionar-tipos-de-grupos/{tipoGrupo}/editar', [GestionarTipoDeGruposController::class, 'editarTipoDeGrupo'])->name('gestionar-tipos-de-grupos.editarTipoDeGrupo');
    // En web.php

    Route::patch('/gestionar-tipos-de-grupos/{tipoGrupo}/cambiar-estado', [GestionarTipoDeGruposController::class, 'cambiarEstadoTipoDeGrupo'])
        ->name('gestionar-tipos-de-grupos.cambiarEstadoTipoDeGrupo');

    // Gestionar Rangos de Edad
    Route::prefix('rangos-edad')->group(function () {
        Route::get('/', [RangoEdadController::class, 'listar'])->name('rangos-edad.listar');
        Route::post('/', [RangoEdadController::class, 'crearRangoDeEdad'])->name('rangos-edad.crearRangoDeEdad');
        Route::patch('/{rango}', [RangoEdadController::class, 'actualizarRangoDeEdad'])->name('rangos-edad.actualizarRangoDeEdad');
        Route::delete('/{rango}', [RangoEdadController::class, 'eliminarRangoDeEdad'])->name('rangos-edad.eliminarRangoDeEdad');
    });

    // Gestionar Tipo de Ofrenda
    Route::prefix('tipo-ofrenda')->group(function () {
        Route::get('/', [TipoOfrendaController::class, 'listar'])->name('tipo-ofrenda.listar');
        Route::post('/', [TipoOfrendaController::class, 'crear'])->name('tipo-ofrenda.crear');
        Route::put('/{tipoOfrenda}', [TipoOfrendaController::class, 'actualizar'])->name('tipo-ofrenda.actualizar');
        Route::delete('/{tipoOfrenda}', [TipoOfrendaController::class, 'eliminar'])->name('tipo-ofrenda.eliminar');
    });

    // Consolidacion
    Route::get('/consolidacion/lista/{tipo?}', [ConsolidacionController::class, 'listar'])->name('consolidacion.lista');
    Route::get('/consolidacion/gestionar-tareas/{usuario}', [ConsolidacionController::class, 'gestionarTareas'])->name('consolidacion.gestionarTareas');

    Route::get('/consolidacion/dashboard', [ConsolidacionController::class, 'dashboard'])->name('consolidacion.dashboard');
    Route::get('/consolidacion/reporte-desempeño', [ConsolidacionController::class, 'reporteDesempeño'])->name('consolidacion.reporteDesempeño');
    Route::get('/consolidacion/bloques', [ConsolidacionController::class, 'bloques'])->name('consolidacion.bloques');

    // Tipo-Usuario
    Route::prefix('tipo-usuario')->group(function () {
        Route::get('/listar', [UsuarioConfiguracionController::class, 'listar'])->name('tipo-usuario.listar');
        Route::get('/creacion', [UsuarioConfiguracionController::class, 'creacion'])->name('tipo-usuario.creacion');
        Route::post('/crear', [UsuarioConfiguracionController::class, 'crear'])->name('tipo-usuario.crear');
        Route::get('/editar/{tipoUsuario}', [UsuarioConfiguracionController::class, 'editar'])->name('tipo-usuario.editar');
        Route::put('/actualizar/{tipoUsuario}', [UsuarioConfiguracionController::class, 'actualizar'])->name('tipo-usuario.actualizar');
        Route::delete('/eliminar/{tipoUsuario}', [UsuarioConfiguracionController::class, 'eliminar'])->name('tipo-usuario.eliminar');
    });

    // --- Rutas CRUD para Filtros (existentes) ---
    Route::prefix('filtros-consolidacion')->name('filtros-consolidacion.')->group(function () {
        Route::get('/listar-filtros-consolidacion', [FiltroConsolidacionController::class, 'listarFiltrosConsolidacion'])->name('listarFiltrosConsolidacion');
        Route::post('/crear-filtro-consolidacion', [FiltroConsolidacionController::class, 'crearFiltroConsolidacion'])->name('crearFiltroConsolidacion');
        Route::put('/actualizar-filtro-consolidacion/{filtro}', [FiltroConsolidacionController::class, 'actualizarFiltroConsolidacion'])->name('actualizarFiltroConsolidacion');
        Route::delete('/eliminar-filtro-consolidacion/{filtro}', [FiltroConsolidacionController::class, 'eliminarFiltroConsolidacion'])->name('eliminarFiltroConsolidacion');

        // --- !!! NUEVAS RUTAS para Tareas !!! ---
        // Usamos POST para asignar (aunque podrías usar PUT si creas un recurso específico)
        Route::post('/{filtro}/asignar-tarea/{tarea}', [FiltroConsolidacionController::class, 'asignarTarea'])->name('asignarTarea');
        // Usamos DELETE para desasignar
        Route::delete('/{filtro}/desasignar-tarea/{tarea}', [FiltroConsolidacionController::class, 'desasignarTarea'])->name('desasignarTarea');
    });

    // --- Rutas para la gestión de Tareas de Consolidación ---
    Route::prefix('tareas-consolidacion')->name('tareas-consolidacion.')->group(function () {

        // GET /tareas-consolidacion/listar-tareas-consolidacion
        Route::get('/listar-tareas-consolidacion', [TareaConsolidacionController::class, 'listarTareasConsolidacion'])
            ->name('listarTareasConsolidacion');

        // POST /tareas-consolidacion/crear-tarea-consolidacion
        Route::post('/crear-tarea-consolidacion', [TareaConsolidacionController::class, 'crearTareaConsolidacion'])
            ->name('crearTareaConsolidacion');

        // PUT /tareas-consolidacion/actualizar-tarea-consolidacion/{tarea}
        Route::put('/actualizar-tarea-consolidacion/{tarea}', [TareaConsolidacionController::class, 'actualizarTareaConsolidacion'])
            ->name('actualizarTareaConsolidacion');

        // DELETE /tareas-consolidacion/eliminar-tarea-consolidacion/{tarea}
        Route::delete('/eliminar-tarea-consolidacion/{tarea}', [TareaConsolidacionController::class, 'eliminarTareaConsolidacion'])
            ->name('eliminarTareaConsolidacion');
    });

    // Banner
    Route::get('/banner-general', [BannerGeneralController::class, 'listarBanners'])->name('banner-general.listarBanners');
    Route::post('/banner-general/crear', [BannerGeneralController::class, 'crearBanner'])->name('banner-general.crearBanner');
    Route::put('/banner-general/actualizar/{banner}', [BannerGeneralController::class, 'actualizarBanner'])->name('banner-general.actualizarBanner');
    Route::delete('/banner-general/eliminar-banner/{banner}', [BannerGeneralController::class, 'eliminarBanner'])->name('banner-general.eliminarBanner');

    // Gestionar videos
    Route::get('/gestion-videos', [GestionVideosController::class, 'listarVideos'])->name('gestion-videos.listarVideos');
    Route::post('/gestion-videos/crear', [GestionVideosController::class, 'crearVideos'])->name('gestion-videos.crearVideos');
    Route::put('/gestion-videos/actualizar', [GestionVideosController::class, 'actualizarVideos'])->name('gestion-videos.actualizarVideos');
    Route::delete('/gestion-videos/eliminar-video/{video}', [GestionVideosController::class, 'eliminarVideo'])->name('gestion-videos.eliminarVideos');

    // Tipo pagos
    Route::prefix('tipo_pagos')->name('tipo-pagos.')->group(function () {
        Route::get('/', [TipoPagosController::class, 'listarTipoPagos'])->name('listarTipoPagos');

        // Crear
        Route::get('/crear', [TipoPagosController::class, 'creacionTipoPagos'])->name('creacionTipoPagos');
        Route::post('/guardar', [TipoPagosController::class, 'crearTipoPagos'])->name('crearTipoPagos');

        // Editar
        Route::get('/editar/{id}', [TipoPagosController::class, 'actualizacionTipoPagos'])->name('actualizacionTipoPagos');
        Route::put('/actualizar/{id}', [TipoPagosController::class, 'actualizarTipoPagos'])->name('actualizarTipoPagos');

        // Eliminar
        Route::delete('/eliminar/{id}', [TipoPagosController::class, 'eliminarTipoPagos'])->name('eliminarTipoPagos');

        // NUEVA RUTA: Cambiar Estado (AJAX)
        Route::post('/cambiar-estado/{id}', [TipoPagosController::class, 'toggleEstado'])->name('toggleEstado');
    });

    Route::get('/theme-setting/index', [ThemeSettingController::class, 'index'])->name('theme-setting.index');
});

require __DIR__.'/auth.php';
