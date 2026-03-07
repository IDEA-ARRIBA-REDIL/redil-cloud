<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use App\Helpers\Helpers;
use App\Mail\DefaultMail;
use App\Models\TipoUsuario;
use App\Models\Iglesia;
use App\Models\RangoEdad;
use App\Models\Sede;
use App\Models\Grupo;
use App\Models\IntegranteGrupo;
use App\Models\EstadoCivil;
use App\Models\TipoIdentificacion;
use App\Models\TipoVinculacion;
use App\Models\PasoCrecimiento;
use App\Models\CrecimientoUsuario;
use App\Models\Ocupacion;
use App\Models\Escuela;
use App\Models\SectorEconomico;
use App\Models\TipoVivienda;
use App\Models\NivelAcademico;
use App\Models\EstadoNivelAcademico;
use App\Models\TipoSangre;
use App\Models\Profesion;
use App\Models\CampoInformeExcel;
use App\Models\Configuracion;
use App\Models\Continente;
use App\Models\EstadoPasoCrecimientoUsuario;
use App\Models\FormularioUsuario;
use App\Models\CampoFormularioUsuario;
use App\Models\SeccionFormularioUsuario;
use App\Models\InformeGrupo;
use App\Models\Pais;
use App\Models\Peticion;
use App\Models\Barrio;
use App\Models\Role;
use App\Models\ServidorGrupo;
use App\Models\TipoGrupo;
use App\Models\TipoPeticion;
use App\Models\SeccionPasoCrecimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

use Illuminate\Support\Facades\Session;
use App\Models\TipoParentesco;
use App\Models\ParienteUsuario;

use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use \stdClass;
use Illuminate\Auth\Events\Registered;

class UserController extends Controller
{

  /**
   * Mark the authenticated user's email address as verified.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function __invoke(Request $request)
  {
    // 1. Encontrar al usuario por el ID que viene en la URL.
    // Usamos findOrFail para que muestre un error 404 si el ID no es válido.
    $user = User::findOrFail($request->route('id'));

    // 2. Comprobar que el hash del enlace coincide con el email del usuario.
    // Esto previene que alguien pueda verificar el email de otro usuario solo cambiando el ID.
    if (! hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
      // Si no coincide, es una acción no autorizada.
      abort(403);
    }

    // 3. Si el usuario ya tiene el email verificado, lo redirigimos.
    if ($user->hasVerifiedEmail()) {
      return redirect(config('app.frontend_url') . '/dashboard?verified=1');
    }

    // 4. Si pasa las comprobaciones, marcamos el email como verificado.
    if ($user->markEmailAsVerified()) {
      // Y disparamos el evento 'Verified'.
      event(new Verified($user));
    }

    // 5. (Opcional pero recomendado) Autenticamos al usuario automáticamente.
    Auth::login($user);

    // 6. Redirigimos al usuario a su panel de control (dashboard).
    return redirect(config('app.frontend_url') . '/dashboard?verified=1');
  }

  public function switchRole(Request $request, Role $role)
  {
    // Llamamos al método que creamos en el modelo User.
    // ¡Mira qué limpio queda el controlador!
    $request->user()->switchActiveRole($role);

    // Redirigimos al usuario a la página anterior con un mensaje de éxito.
    return back()->with('success', 'Has cambiado al rol: ' . $role->name);
  }

  public function listar(Request $request, $tipo = 'todos') //: View
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('personas.subitem_lista_asistentes');

    $indicadoresGenerales = [];
    $indicadoresPorTipoUsuario = [];

    $configuracion = Configuracion::find(1);
    $camposInformeExcel = CampoInformeExcel::orderBy('orden', 'asc')->get();
    $camposExtras = CampoFormularioUsuario::where('es_campo_extra', true)->get();

    $tiposUsuarios = TipoUsuario::orderBy('orden', 'asc')
      ->where('visible', true)
      ->where('tipo_pastor_principal', '!=', true)
      ->get();

    // Carga de catálogos para filtros
    $rangosEdad = RangoEdad::all();
    $estadosCiviles = EstadoCivil::all();
    $tiposVinculaciones = TipoVinculacion::withTrashed()->get();
    $pasosCrecimiento = PasoCrecimiento::orderBy('updated_at', 'asc')->get();
    $estadosPasosDeCrecimiento = EstadoPasoCrecimientoUsuario::orderBy('puntaje', 'asc')->get();
    $ocupaciones = Ocupacion::orderBy('nombre', 'asc')->get();
    $nivelesAcademicos = NivelAcademico::orderBy('nombre', 'asc')->get();
    $estadosNivelAcademico = EstadoNivelAcademico::orderBy('id', 'asc')->get();
    $profesiones = Profesion::orderBy('nombre', 'asc')->get();

    // Fechas para indicadores de inactividad
    $fechaMaximaActividadGrupo = Carbon::now()
      ->subDays($configuracion->tiempo_para_definir_inactivo_grupo)
      ->format('Y-m-d');

    $fechaMaximaActividadReunion = Carbon::now()
      ->subDays($configuracion->tiempo_para_definir_inactivo_reunion)
      ->format('Y-m-d');

    // Tipos de usuario con seguimiento
    $tipoUsuariosSeguimientoReunion = TipoUsuario::where('seguimiento_actividad_reunion', true)->pluck('id')->toArray();
    $tipoUsuariosSeguimientoGrupo = TipoUsuario::where('seguimiento_actividad_grupo', true)->pluck('id')->toArray();
    $tipoUsuariosSeguimientoTodos = array_intersect($tipoUsuariosSeguimientoReunion, $tipoUsuariosSeguimientoGrupo);

    $iglesia = Iglesia::find(1);
    $arrayPastoresPrincipal = $iglesia->pastoresEncargados()->pluck('users.id')->toArray();

    // Parametros de Búsqueda
    $parametrosBusqueda = new stdClass();
    $parametrosBusqueda->buscar = $request->buscar;
    $parametrosBusqueda->filtroPorSexo = $request->filtroPorSexo;
    $parametrosBusqueda->filtroPorTipoDeUsuario = $request->filtroPorTipoDeUsuario;
    $parametrosBusqueda->filtroPorRangoEdad = $request->filtroPorRangoEdad;
    $parametrosBusqueda->filtroPorEstadosCiviles = $request->filtroPorEstadosCiviles;
    $parametrosBusqueda->filtroPorTiposVinculaciones = $request->filtroPorTiposVinculaciones;
    $parametrosBusqueda->filtroPorOcupacion = $request->filtroPorOcupacion;
    $parametrosBusqueda->filtroPorProfesion = $request->filtroPorProfesion;
    $parametrosBusqueda->filtroPorNivelAcademico = $request->filtroPorNivelAcademico;
    $parametrosBusqueda->filtroPorEstadoNivelAcademico = $request->filtroPorEstadoNivelAcademico;
    $parametrosBusqueda->filtroPorPasosCrecimiento1 = $request->filtroPorPasosCrecimiento1;
    $parametrosBusqueda->filtroEstadoPasos1 = $request->filtroEstadoPasos1;
    $parametrosBusqueda->filtroFechasPasosCrecimiento1 = $request->filtroFechasPasosCrecimiento1;
    $parametrosBusqueda->filtroFechaIniPaso1 = $request->filtroFechaIniPaso1;
    $parametrosBusqueda->filtroFechaFinPaso1 = $request->filtroFechaFinPaso1;
    $parametrosBusqueda->filtroPorPasosCrecimiento2 = $request->filtroPorPasosCrecimiento2;
    $parametrosBusqueda->filtroEstadoPasos2 = $request->filtroEstadoPasos2;
    $parametrosBusqueda->filtroFechasPasosCrecimiento2 = $request->filtroFechasPasosCrecimiento2;
    $parametrosBusqueda->filtroFechaIniPaso2 = $request->filtroFechaIniPaso2;
    $parametrosBusqueda->filtroFechaFinPaso2 = $request->filtroFechaFinPaso2;
    $parametrosBusqueda->filtroGrupo = $request->filtroGrupo;
    $parametrosBusqueda->filtroTipoMinisterio = $request->filtroTipoMinisterio;
    $parametrosBusqueda->filtroCantidadDiasInactividadGrupos = $request->filtroCantidadDiasInactividadGrupos;
    $parametrosBusqueda->filtroCantidadDiasInactividadReuniones = $request->filtroCantidadDiasInactividadReuniones;

    $parametrosBusqueda->fechaMaximaActividadGrupo = $fechaMaximaActividadGrupo;
    $parametrosBusqueda->fechaMaximaActividadReunion = $fechaMaximaActividadReunion;
    $parametrosBusqueda->tipoUsuariosSeguimientoGrupo = $tipoUsuariosSeguimientoGrupo;
    $parametrosBusqueda->tipoUsuariosSeguimientoReunion = $tipoUsuariosSeguimientoReunion;
    $parametrosBusqueda->tipoUsuariosSeguimientoTodos = $tipoUsuariosSeguimientoTodos;
    $parametrosBusqueda->arrayPastoresPrincipal = $arrayPastoresPrincipal;
    $parametrosBusqueda->textoBusqueda = '';
    $parametrosBusqueda->tagsBusqueda = [];
    $parametrosBusqueda->bandera = '';
    $parametrosBusqueda->tipo = $tipo;


    // OBTENCIÓN DEL QUERY BUILDER BASE
    $query = null;

    if ($rolActivo->hasPermissionTo('personas.lista_asistentes_solo_ministerio')) {
         $query = auth()->user()->discipulos('todos', true, true); // true para returnQuery
    }

    if ($rolActivo->hasPermissionTo('personas.lista_asistentes_todos')) {
         $query = User::withTrashed()
            ->whereNotNull('email_verified_at')
            ->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
            ->whereIn('tipo_usuario_id', $tiposUsuarios->pluck('id')->toArray())
            ->select('users.*', 'integrantes_grupo.grupo_id as grupo_id')
            ->distinct('users.id');
            // Nota: discipulos() ya maneja lógica de joins complejos. Si 'todos' es realmente TODOS, este query directo está bien.
    }

    // Si no hay permisos o query es null, inicializamos para evitar error
    if (!$query) {
        $personas = User::whereRaw('1=2')->paginate(1);
    } else {

        // CÁLCULO DE INDICADORES (Usando el Query Builder Base CLONADO)
        // Optimizamos para no hacer count() sobre colecciones gigantes

        // 1. Todas
        $qTodas = clone $query;
        $countTodas = $qTodas->whereNull('users.deleted_at')->count('users.id'); // o count(DB::raw('DISTINCT users.id')) si fuera necesario

        $item = new stdClass();
        $item->nombre = 'Todas (Dadas de alta)';
        $item->url = 'todos';
        $item->cantidad = $countTodas;
        $item->color = 'bg-label-success';
        $item->imagen = 'icono_indicador.png';
        $item->icono = 'ti ti-asterisk';
        $indicadoresGenerales[] = $item;

        // 2. Sin grupo
        $qSinGrupo = clone $query;
        $countSinGrupo = $qSinGrupo->whereNull('users.deleted_at')
             ->whereNull('integrantes_grupo.grupo_id') // Asume que el join ya está hecho en discipulos() o arriba
             ->whereNotIn('users.id', $arrayPastoresPrincipal)
             ->count('users.id');

        $item = new stdClass();
        $item->nombre = 'Sin grupo';
        $item->url = 'sin-grupo';
        $item->cantidad = $countSinGrupo;
        $item->color = 'bg-label-primary';
        $item->imagen = 'icono_indicador.png';
        $item->icono = 'ti ti-users';
        $indicadoresGenerales[] = $item;

        // 3. Dadas de baja
        $qBajas = clone $query;
        $countBajas = $qBajas->whereNotNull('users.deleted_at')->count('users.id');

        $item = new stdClass();
        $item->nombre = 'Dadas de baja';
        $item->url = 'dados-de-baja';
        $item->cantidad = $countBajas;
        $item->color = 'bg-label-secondary';
        $item->imagen = 'icono_indicador.png';
        $item->icono = 'ti ti-user-off';
        $indicadoresGenerales[] = $item;

        // 4. Inactivas reunión
        $qInactivasReunion = clone $query;
        $countInactivasReunion = $qInactivasReunion->whereNull('users.deleted_at')
            ->whereIn('tipo_usuario_id', $tipoUsuariosSeguimientoReunion)
            ->where(function($q) use ($fechaMaximaActividadReunion) {
                 $q->where('ultimo_reporte_reunion', '<', $fechaMaximaActividadReunion)
                   ->orWhereNull('ultimo_reporte_reunion');
            })
            ->count('users.id');

        $item = new stdClass();
        $item->nombre = 'Inactivas en reunión';
        $item->url = 'inactivas-reunion';
        $item->cantidad = $countInactivasReunion;
        $item->color = 'bg-label-danger';
        $item->imagen = 'icono_indicador.png';
        $item->icono = 'ti ti-building-church';
        $indicadoresGenerales[] = $item;

        // 5. Inactivas grupo
        $qInactivasGrupo = clone $query;
        $countInactivasGrupo = $qInactivasGrupo->whereNull('users.deleted_at')
            ->whereIn('tipo_usuario_id', $tipoUsuariosSeguimientoGrupo)
            ->where(function($q) use ($fechaMaximaActividadGrupo) {
                 $q->where('ultimo_reporte_grupo', '<', $fechaMaximaActividadGrupo)
                   ->orWhereNull('ultimo_reporte_grupo');
            })
            ->count('users.id');

        $item = new stdClass();
        $item->nombre = 'Inactivas en grupo';
        $item->url = 'inactivas-grupo';
        $item->cantidad = $countInactivasGrupo;
        $item->color = 'bg-label-danger';
        $item->imagen = 'icono_indicador.png';
        $item->icono = 'ti ti-user-exclamation';
        $indicadoresGenerales[] = $item;

        // 6. Inactivas en todo
        $qInactivasTodo = clone $query;
        $countInactivasTodo = $qInactivasTodo->whereNull('users.deleted_at')
            ->whereIn('tipo_usuario_id', $tipoUsuariosSeguimientoTodos)
            ->where(function($q) use ($fechaMaximaActividadGrupo) {
                 $q->where('ultimo_reporte_grupo', '<', $fechaMaximaActividadGrupo)
                   ->orWhereNull('ultimo_reporte_grupo');
            })
            ->where(function($q) use ($fechaMaximaActividadReunion) {
                 $q->where('ultimo_reporte_reunion', '<', $fechaMaximaActividadReunion)
                   ->orWhereNull('ultimo_reporte_reunion');
            })
            ->count('users.id');

        $item = new stdClass();
        $item->nombre = 'Inactivas en todo';
        $item->url = 'inactivas-todo';
        $item->cantidad = $countInactivasTodo;
        $item->color = 'bg-label-danger';
        $item->imagen = 'icono_indicador.png';
        $item->icono = 'ti ti-x';
        $indicadoresGenerales[] = $item;

        // Indicadores por Tipo de Usuario
        // Optimizamos haciendo una sola consulta agrupada en lugar de N consultas dentro del foreach?
        // O simplemente consultas count por cada tipo (si son pocos tipos no es grave).
        // Hagamos loop por limpieza, clonando query.
        foreach ($tiposUsuarios as $tipoUsuario) {
            $qTipo = clone $query;
            $c = $qTipo->whereNull('users.deleted_at')
                       ->where('tipo_usuario_id', $tipoUsuario->id)
                       ->count('users.id');

            $item = new stdClass();
            $item->nombre = $tipoUsuario->nombre;
            $item->url = $tipoUsuario->id;
            $item->cantidad = $c;
            $item->color = $tipoUsuario->color;
            $item->imagen = 'icono_indicador.png';
            $item->icono = $tipoUsuario->icono;
            $indicadoresPorTipoUsuario[] = $item;
        }

        $indicadoresGenerales = collect(array_merge($indicadoresGenerales, $indicadoresPorTipoUsuario));
        // $indicadoresPorTipoUsuario se queda como array/collect para la vista si se necesitara

        // APLICACIÓN DE FILTROS AL QUERY PRINCIPAL
        // Modificamos $query por referencia o reasignación

        $this->filtroPorTipo($query, $parametrosBusqueda);

        $this->filtrosBusqueda($query, $tipo, $parametrosBusqueda);

        // Paginación final
        // orderBy debe ir al final
        $personas = $query->orderBy('users.id', 'desc')->paginate(12);
    }

    return view('contenido.paginas.usuario.listar', [
      'personas' => $personas,
      'tipo' => $tipo,
      'tiposUsuarios' => $tiposUsuarios,
      'rangosEdad' => $rangosEdad,
      'estadosCiviles' => $estadosCiviles,
      'parametrosBusqueda' => $parametrosBusqueda,
      'indicadoresGenerales' => $indicadoresGenerales,
      // 'indicadoresPorTipoUsuario' => $indicadoresPorTipoUsuario, // No se usa directamente en la vista, ya está en $indicadoresGenerales
      'tiposVinculaciones' => $tiposVinculaciones,
      'pasosCrecimiento' => $pasosCrecimiento,
      'estadosPasosDeCrecimiento' => $estadosPasosDeCrecimiento,
      'ocupaciones' => $ocupaciones,
      'nivelesAcademicos' => $nivelesAcademicos,
      'estadosNivelAcademico' => $estadosNivelAcademico,
      'profesiones' => $profesiones,
      'camposInformeExcel' => $camposInformeExcel,
      'rolActivo' => $rolActivo,
      'camposExtras' => $camposExtras,
      'configuracion' => $configuracion,
    ]);
  }

  public function filtrosBusqueda($personas, $tipo, $parametrosBusqueda)
  {
    if ($parametrosBusqueda->filtroCantidadDiasInactividadGrupos != '') {
      $nuevafecha_grupo = Carbon::now()
        ->subDays($parametrosBusqueda->filtroCantidadDiasInactividadGrupos)
        ->format('Y-m-d');
      // $personas es ahora un Builder
      $personas->where(function ($q) use ($nuevafecha_grupo) {
          $q->where('ultimo_reporte_grupo', '<', $nuevafecha_grupo)
            ->orWhereNull('ultimo_reporte_grupo');
      })->whereIn('tipo_usuario_id', $parametrosBusqueda->tipoUsuariosSeguimientoGrupo);

      $parametrosBusqueda->textoBusqueda .=
        '<b>, Días inactiviad en grupos: </b>' . $parametrosBusqueda->filtroCantidadDiasInactividadGrupos . ' ';
      $parametrosBusqueda->bandera = 1;
    }

    if ($parametrosBusqueda->filtroCantidadDiasInactividadReuniones != '') {
      $nuevafecha_reunion = Carbon::now()
        ->subDays($parametrosBusqueda->filtroCantidadDiasInactividadReuniones)
        ->format('Y-m-d');

      $personas->where(function ($q) use ($nuevafecha_reunion) {
          $q->where('ultimo_reporte_reunion', '<', $nuevafecha_reunion)
            ->orWhereNull('ultimo_reporte_reunion');
      })->whereIn('tipo_usuario_id', $parametrosBusqueda->tipoUsuariosSeguimientoReunion);

      $parametrosBusqueda->textoBusqueda .=
        '<b>, Días inactiviad en grupos: </b>' . $parametrosBusqueda->filtroCantidadDiasInactividadReuniones . ' ';
      $parametrosBusqueda->bandera = 1;
    }

    // Busqueda textual
    if ($parametrosBusqueda->buscar != '') {

      $buscar = htmlspecialchars($parametrosBusqueda->buscar);
      $buscarSaneado = Helpers::sanearStringConEspacios($buscar);
      $buscar = str_replace(["'"], '', $buscar);
      $buscar_array = explode(' ', $buscar);

      $personas->where(function ($q) use ($buscarSaneado, $buscar) {
        $q->whereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, segundo_nombre, primer_apellido, segundo_apellido ) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, primer_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', primer_nombre, segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', segundo_apellido, segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER(telefono_movil) LIKE LOWER(?)", [$buscar . '%'])
          ->orWhereRaw("LOWER(email) LIKE LOWER(?)", ['%' . $buscar . '%'])
          ->orWhereRaw("LOWER(identificacion) LIKE LOWER(?)", [$buscar . '%']);
      });

      $parametrosBusqueda->textoBusqueda .= '<b>, Con busqueda: </b>"' . $buscar . '" ';
      $parametrosBusqueda->bandera = 1;

      // Crear una tag
      $tag = new stdClass();
      $tag->label = $parametrosBusqueda->buscar;
      $tag->field = 'filtroBuscar';
      $tag->value = $buscar;
      $tag->fieldAux = '';
      $parametrosBusqueda->tagsBusqueda[] = $tag;
    }

    // Filtros específicos (ahora reciben un Builder)
    $this->filtrarEdad($personas, $parametrosBusqueda);
    $this->filtrarSexo($personas, $parametrosBusqueda);
    $this->filtrarEstadoCivil($personas, $parametrosBusqueda);
    $this->filtrarTipoVinculacion($personas, $parametrosBusqueda);

    $this->filtrarPasoCrecimiento(
      1,
      $personas,
      $parametrosBusqueda->filtroPorPasosCrecimiento1,
      $parametrosBusqueda->filtroEstadoPasos1,
      $parametrosBusqueda->filtroFechaIniPaso1,
      $parametrosBusqueda->filtroFechaFinPaso1,
      $parametrosBusqueda
    );

    $this->filtrarPasoCrecimiento(
      2,
      $personas,
      $parametrosBusqueda->filtroPorPasosCrecimiento2,
      $parametrosBusqueda->filtroEstadoPasos2,
      $parametrosBusqueda->filtroFechaIniPaso2,
      $parametrosBusqueda->filtroFechaFinPaso2,
      $parametrosBusqueda
    );

    //Filtro por ocupacion
    $this->filtrarOcupacion($personas, $parametrosBusqueda);

    //Filtro por nivel academico
    $this->filtrarNivelAcademico($personas, $parametrosBusqueda);

    //Filtro por estado nivel academico
    $this->filtrarEstadoNivelAcademico($personas, $parametrosBusqueda);

    //Filtro por profesion
    $this->filtrarProfesion($personas, $parametrosBusqueda);

    //Filtro a partir de un grupo
    $this->filtrarApartirGrupoSeleccionado($personas, $parametrosBusqueda);

    return $personas;
  }

  public function filtroPorTipo($personas, $parametrosBusqueda)
  {
    $parametrosBusqueda->textoBusqueda = '';

    // $personas es Builder

    if ($parametrosBusqueda->tipo != 'dados-de-baja') {
      if ($parametrosBusqueda->tipo == 'todos' || $parametrosBusqueda->tipo == '') {
        $parametrosBusqueda->textoBusqueda = 'Todas';
      }

      $personas->whereNull('users.deleted_at');

      if ($parametrosBusqueda->tipo == 'sin-grupo') {
        $parametrosBusqueda->textoBusqueda = 'Sin grupo';

        $personas->whereNull('integrantes_grupo.grupo_id')
                 ->whereNotIn('users.id', $parametrosBusqueda->arrayPastoresPrincipal);
      }

      if ($parametrosBusqueda->tipo == 'inactivas-reunion') {
        $parametrosBusqueda->textoBusqueda = 'Inactivas en reunión';
        $personas->where(function ($q) use ($parametrosBusqueda) {
             $q->where('ultimo_reporte_reunion', '<', $parametrosBusqueda->fechaMaximaActividadReunion)
               ->orWhereNull('ultimo_reporte_reunion');
        })->whereIn('tipo_usuario_id', $parametrosBusqueda->tipoUsuariosSeguimientoReunion);
      }

      if ($parametrosBusqueda->tipo == 'inactivas-grupo') {
        $parametrosBusqueda->textoBusqueda = 'Inactivas en grupo';

        $personas->where(function ($q) use ($parametrosBusqueda) {
             $q->where('ultimo_reporte_grupo', '<', $parametrosBusqueda->fechaMaximaActividadGrupo)
               ->orWhereNull('ultimo_reporte_grupo');
        })->whereIn('tipo_usuario_id', $parametrosBusqueda->tipoUsuariosSeguimientoGrupo);
      }

      if ($parametrosBusqueda->tipo == 'inactivas-todo') {
        $parametrosBusqueda->textoBusqueda = 'Inactivas en todo';

        $personas->whereIn('tipo_usuario_id', $parametrosBusqueda->tipoUsuariosSeguimientoTodos)
                 ->where(function ($q) use ($parametrosBusqueda) {
                      $q->where('ultimo_reporte_grupo', '<', $parametrosBusqueda->fechaMaximaActividadGrupo)
                        ->orWhereNull('ultimo_reporte_grupo');
                 })
                 ->where(function ($q) use ($parametrosBusqueda) {
                      $q->where('ultimo_reporte_reunion', '<', $parametrosBusqueda->fechaMaximaActividadReunion)
                        ->orWhereNull('ultimo_reporte_reunion');
                 });
      }

      if (is_numeric($parametrosBusqueda->tipo) && !isset($parametrosBusqueda->filtroPorTipoDeUsuario)) {
        $nombrePlural = TipoUsuario::where('id', $parametrosBusqueda->tipo)->value('nombre_plural');
        $parametrosBusqueda->textoBusqueda = $nombrePlural;
        $personas->where('tipo_usuario_id', $parametrosBusqueda->tipo);
      }
    } else {
      $parametrosBusqueda->textoBusqueda = 'Dadas de baja';
      $personas->whereNotNull('users.deleted_at');
    }

    if (isset($parametrosBusqueda->filtroPorTipoDeUsuario)) {
      $tiposUsuarios = TipoUsuario::whereIn('id', $parametrosBusqueda->filtroPorTipoDeUsuario)->get();

      $nombres = $tiposUsuarios->pluck('nombre_plural')->implode(', ');
      $parametrosBusqueda->textoBusqueda .= ' "' . $nombres . '"';

      $personas->whereIn('tipo_usuario_id', $parametrosBusqueda->filtroPorTipoDeUsuario);
      $parametrosBusqueda->bandera = 1;

      foreach ($tiposUsuarios as $tipoUsuario) {
        $tag = new stdClass();
        $tag->label = $tipoUsuario->nombre_plural;
        $tag->field = 'filtroPorTipoDeUsuario';
        $tag->value = $tipoUsuario->id;
        $tag->fieldAux = '';
        $parametrosBusqueda->tagsBusqueda[] = $tag;
      }
    }

    return $personas;
  }

  public function filtrarEdad($personas, $parametrosBusqueda)
  {
    if ($parametrosBusqueda->filtroPorRangoEdad) {
      $rangos = RangoEdad::whereIn('id', $parametrosBusqueda->filtroPorRangoEdad)->get();
      $edadesPermitidas = [];

      $parametrosBusqueda->textoBusqueda .=
        '<b>, Edades: </b>"' . implode(', ', $rangos->pluck('nombre')->toArray()) . '"';
      $parametrosBusqueda->bandera = 1;

      foreach ($rangos as $rango) {
        for ($x = $rango->edad_minima; $x <= $rango->edad_maxima; $x++) {
          $edadesPermitidas[] = $x;
        }

        // Crear una tag por cada rango de edad
        $tag = new stdClass();
        $tag->label = $rango->nombre;
        $tag->field = 'filtroPorRangoEdad';
        $tag->value = $rango->id; // Usamos el ID del rango como valor
        $tag->fieldAux = '';
        $parametrosBusqueda->tagsBusqueda[] = $tag;
      }

      $personas = $personas->filter(function ($persona) use ($edadesPermitidas) {
        $edadPersona = Carbon::parse($persona->fecha_nacimiento)->age;
        return in_array($edadPersona, $edadesPermitidas);
      });
    }

    return $personas;
  }

  public function filtrarSexo($personas, $parametrosBusqueda)
  {
    if (is_numeric($parametrosBusqueda->filtroPorSexo)) {
      $personas->where('genero', '=', $parametrosBusqueda->filtroPorSexo);

      $parametrosBusqueda->textoBusqueda .= $parametrosBusqueda->filtroPorSexo == 0 ? '<b>, Sexo: </b> Hombres' : '<b>, Sexo:</b> Mujeres';
      $sexoLabel = $parametrosBusqueda->filtroPorSexo == 0 ? 'Hombre' : 'Mujer';

      $parametrosBusqueda->bandera = 1;

      $tag = new stdClass();
      $tag->label = $sexoLabel;
      $tag->field = 'filtroPorSexo';
      $tag->value = $parametrosBusqueda->filtroPorSexo; // Guardar el valor del filtro
      $tag->fieldAux = '';
      $parametrosBusqueda->tagsBusqueda[] = $tag;
    }
    return $personas;
  }

  public function filtrarEstadoCivil($personas, $parametrosBusqueda)
  {
    if ($parametrosBusqueda->filtroPorEstadosCiviles) {
      $personas->whereIn('estado_civil_id', $parametrosBusqueda->filtroPorEstadosCiviles);

      $estadosCiviles = EstadoCivil::whereIn('id', $parametrosBusqueda->filtroPorEstadosCiviles)
        ->select('nombre')
        ->pluck('nombre')
        ->toArray();

      $parametrosBusqueda->textoBusqueda .= '<b>, Estados civiles: </b>"' . implode(', ', $estadosCiviles) . '"';
      $parametrosBusqueda->bandera = 1;

      // Crear las tags para cada estado civil
      $estadosCivilesSeleccionados = EstadoCivil::whereIn('id', $parametrosBusqueda->filtroPorEstadosCiviles)->get();
      foreach ($estadosCivilesSeleccionados as $estadoCivil) {
        $tag = new stdClass();
        $tag->label = $estadoCivil->nombre;
        $tag->field = 'filtroPorEstadosCiviles';
        $tag->value = $estadoCivil->id;
        $tag->fieldAux = '';
        $parametrosBusqueda->tagsBusqueda[] = $tag;
      }
    }
    return $personas;
  }

  public function filtrarTipoVinculacion($personas, $parametrosBusqueda)
  {
    if ($parametrosBusqueda->filtroPorTiposVinculaciones) {
      $personas->whereIn('tipo_vinculacion_id', $parametrosBusqueda->filtroPorTiposVinculaciones);

      $tiposVinculacion = TipoVinculacion::whereIn('id', $parametrosBusqueda->filtroPorTiposVinculaciones)
        ->select('nombre')
        ->pluck('nombre')
        ->toArray();

      $parametrosBusqueda->textoBusqueda .= '<b>, Tipos de vinculación:</b> "' . implode(', ', $tiposVinculacion) . '"';
      $parametrosBusqueda->bandera = 1;

      // Crear las tags para cada tipo de vinculación
      $tiposVinculacionSeleccionados = TipoVinculacion::whereIn('id', $parametrosBusqueda->filtroPorTiposVinculaciones)->get();
      foreach ($tiposVinculacionSeleccionados as $tipoVinculacion) {
        $tag = new stdClass();
        $tag->label = $tipoVinculacion->nombre;
        $tag->field = 'filtroPorTiposVinculaciones';
        $tag->value = $tipoVinculacion->id;
        $tag->fieldAux = '';
        $parametrosBusqueda->tagsBusqueda[] = $tag;
      }
    }
    return $personas;
  }

  public function filtrarPasoCrecimiento($numeroFiltro, $personas, $pasosCrecimiento, $estado, $fechaInicio, $fechaFin, $parametrosBusqueda)
  {
    if ($pasosCrecimiento) {
      $pasosDeCrecimiento = PasoCrecimiento::whereIn('id', $pasosCrecimiento)
        ->select('nombre')
        ->pluck('nombre')
        ->toArray();

      $parametrosBusqueda->textoBusqueda .= ', <b>Pasos de crecimiento';

      $personasPasoCrecimiento = CrecimientoUsuario::whereIn('paso_crecimiento_id', $pasosCrecimiento);
      $parametrosBusqueda->textoBusqueda .= '[ ';
      if ($fechaInicio && $fechaFin) {
        $personasPasoCrecimiento = $personasPasoCrecimiento->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        $parametrosBusqueda->textoBusqueda .= ' Del ' . $fechaInicio . ' al ' . $fechaFin . ' | ';
      }

      $estadoSeleccionado = EstadoPasoCrecimientoUsuario::find($estado);
      if ($estadoSeleccionado) {
        $parametrosBusqueda->textoBusqueda .= 'Estado ' . $estadoSeleccionado->nombre . ' ]:';

        if ($estadoSeleccionado->default) {
          $arrayIdsTodosEstados = EstadoPasoCrecimientoUsuario::where('default', false)
            ->select('id')
            ->pluck('id')
            ->toArray();

          $personasPasoCrecimiento = $personasPasoCrecimiento->whereNotIn('estado_id', $arrayIdsTodosEstados);
        } else {
          $personasPasoCrecimiento = $personasPasoCrecimiento->where('estado_id', $estadoSeleccionado->id);
        }
      }

      $parametrosBusqueda->textoBusqueda .= '</b>';

      $parametrosBusqueda->textoBusqueda .= '"' . implode(', ', $pasosDeCrecimiento) . '"';
      $parametrosBusqueda->bandera = 1;

      $idUserPasoCrecimiento = $personasPasoCrecimiento
        ->select('user_id')
        ->pluck('user_id')
        ->toArray();


      // Crear las tags para cada paso de crecimiento
      $pasosCrecimientoSeleccionados = PasoCrecimiento::whereIn('id', $pasosCrecimiento)->get();
      foreach ($pasosCrecimientoSeleccionados as $paso) {
        $tag = new stdClass();
        $tag->label = 'Paso ' . $numeroFiltro . ': ' . $paso->nombre;
        $tag->field = 'filtroPorPasosCrecimiento' . $numeroFiltro; // o 'filtroPorPasosCrecimiento2', dependiendo de cuál se esté usando
        $tag->value = $paso->id;
        $tag->fieldAux = '';
        $parametrosBusqueda->tagsBusqueda[] = $tag;
      }

      // Crear las tags para estado del paso de crecimiento
      if ($estadoSeleccionado) {
        $tag = new stdClass();
        $tag->label = 'Estado paso ' . $numeroFiltro . ': ' . $estadoSeleccionado->nombre;
        $tag->field = 'filtroEstadoPasos' . $numeroFiltro; // o 'filtroEstadoPasos2', dependiendo de cuál se esté usando
        $tag->fieldAux = '';
        $tag->value = $paso->id;
        $parametrosBusqueda->tagsBusqueda[] = $tag;
      }

      if ($fechaInicio && $fechaFin) {
        $tag = new stdClass();
        $tag->label = 'Rango paso ' . $numeroFiltro . ': ' . $fechaInicio . ' a ' . $fechaFin;
        $tag->field = 'filtroFechaIniPaso' . $numeroFiltro; // o 'filtroEstadoPasos2', dependiendo de cuál se esté usando
        $tag->fieldAux = 'filtroFechaFinPaso' . $numeroFiltro;
        $tag->value = '';
        $parametrosBusqueda->tagsBusqueda[] = $tag;
      }


      $personas = $personas->whereIn('id', $idUserPasoCrecimiento);
    }

    return $personas;
  }

  public function filtrarOcupacion($personas, $parametrosBusqueda)
  {
    if ($parametrosBusqueda->filtroPorOcupacion) {
      $personas = $personas->whereIn('ocupacion_id', $parametrosBusqueda->filtroPorOcupacion);

      $ocupaciones = Ocupacion::whereIn('id', $parametrosBusqueda->filtroPorOcupacion)
        ->select('nombre')
        ->pluck('nombre')
        ->toArray();

      $parametrosBusqueda->textoBusqueda .= '<b>, Ocupaciones: </b>"' . implode(', ', $ocupaciones) . '"';
      $parametrosBusqueda->bandera = 1;

      // Crear las tags para cada ocupación
      $ocupacionesSeleccionadas = Ocupacion::whereIn('id', $parametrosBusqueda->filtroPorOcupacion)->get();
      foreach ($ocupacionesSeleccionadas as $ocupacion) {
        $tag = new stdClass();
        $tag->label = $ocupacion->nombre;
        $tag->field = 'filtroPorOcupacion';
        $tag->value = $ocupacion->id;
        $tag->fieldAux = '';
        $parametrosBusqueda->tagsBusqueda[] = $tag;
      }
    }
    return $personas;
  }

  public function filtrarNivelAcademico($personas, $parametrosBusqueda)
  {
    if ($parametrosBusqueda->filtroPorNivelAcademico) {
      $personas = $personas->whereIn('nivel_academico_id', $parametrosBusqueda->filtroPorNivelAcademico);

      $nivelesAcademicos = NivelAcademico::whereIn('id', $parametrosBusqueda->filtroPorNivelAcademico)
        ->select('nombre')
        ->pluck('nombre')
        ->toArray();

      $parametrosBusqueda->textoBusqueda .= ', <b>Niveles académicos: </b>"' . implode(', ', $nivelesAcademicos) . '"';
      $parametrosBusqueda->bandera = 1;

      // Crear las tags para cada nivel académico
      $nivelesAcademicosSeleccionados = NivelAcademico::whereIn('id', $parametrosBusqueda->filtroPorNivelAcademico)->get();
      foreach ($nivelesAcademicosSeleccionados as $nivelAcademico) {
        $tag = new stdClass();
        $tag->label = $nivelAcademico->nombre;
        $tag->field = 'filtroPorNivelAcademico';
        $tag->value = $nivelAcademico->id;
        $tag->fieldAux = '';
        $parametrosBusqueda->tagsBusqueda[] = $tag;
      }
    }
    return $personas;
  }

  public function filtrarEstadoNivelAcademico($personas, $parametrosBusqueda)
  {
    if ($parametrosBusqueda->filtroPorEstadoNivelAcademico) {
      $personas = $personas->where(
        'estado_nivel_academico_id',
        '=',
        $parametrosBusqueda->filtroPorEstadoNivelAcademico
      );

      $estadoNivelAcademico = EstadoNivelAcademico::where('id', $parametrosBusqueda->filtroPorEstadoNivelAcademico)->first();

      $parametrosBusqueda->textoBusqueda .=
        '<b>, Estados niveles académicos: </b>"' . $estadoNivelAcademico->nombre . '"';
      $parametrosBusqueda->bandera = 1;

      // Crear la tag para el estado del nivel académico
      if ($estadoNivelAcademico) {
        $tag = new stdClass();
        $tag->label = "Estado nivel académico: " . $estadoNivelAcademico->nombre;
        $tag->field = 'filtroPorEstadoNivelAcademico';
        $tag->value = $estadoNivelAcademico->id;
        $tag->fieldAux = '';
        $parametrosBusqueda->tagsBusqueda[] = $tag;
      }
    }
    return $personas;
  }

  public function filtrarProfesion($personas, $parametrosBusqueda)
  {
    if ($parametrosBusqueda->filtroPorProfesion) {
      $personas = $personas->where('profesion_id', '=', $parametrosBusqueda->filtroPorProfesion);

      $profesiones = Profesion::whereIn('id', $parametrosBusqueda->filtroPorProfesion)
        ->select('nombre')
        ->pluck('nombre')
        ->toArray();

      $parametrosBusqueda->textoBusqueda .= '<b>, Profesiones: </b>"' . implode(', ', $profesiones) . '"';
      $parametrosBusqueda->bandera = 1;

      // Crear la tag para la profesión
      $profesionesSeleccionadas = Profesion::whereIn('id', $parametrosBusqueda->filtroPorProfesion)->get();
      foreach ($profesionesSeleccionadas as $profesion) {
        $tag = new stdClass();
        $tag->label = $profesion->nombre;
        $tag->field = 'filtroPorProfesion';
        $tag->value = $profesion->id;
        $tag->fieldAux = '';
        $parametrosBusqueda->tagsBusqueda[] = $tag;
      }
    }
    return $personas;
  }

  public function filtrarApartirGrupoSeleccionado($personas, $parametrosBusqueda)
  {
    if ($parametrosBusqueda->filtroGrupo != '') {
      $configuracion = Configuracion::find(1);
      $grupo = Grupo::find($parametrosBusqueda->filtroGrupo);

      $parametrosBusqueda->textoBusqueda .= '<b>, Grupo: </b>' . $grupo->nombre;

      if ($parametrosBusqueda->filtroTipoMinisterio == 0) {
        $gruposIds = $grupo->gruposMinisterio('array');
        $labelFiltroTipoMinisterio = ', ministerio completo';

        //Agrego el id del grupo que estoy consultado
        array_push($gruposIds, $grupo->id);

        $idsUsers = IntegranteGrupo::whereIn('grupo_id', $gruposIds)
          ->select('user_id')
          ->pluck('user_id')
          ->toArray();

        $personas = $personas->whereIn('id', $idsUsers);
        $parametrosBusqueda->textoBusqueda .= '"Ministerio completo"';
      } else {
        $labelFiltroTipoMinisterio = ', ministerio directo';
        $idsUsers = IntegranteGrupo::where('grupo_id', '=', $grupo->id)
          ->select('user_id')
          ->pluck('user_id')
          ->toArray();

        $personas = $personas->whereIn('id', $idsUsers);
        $parametrosBusqueda->textoBusqueda .= '"Ministerio directo"';
        $parametrosBusqueda->bandera = 1;
      }

      // Crear la tag a partir de un grupo
      $tag = new stdClass();
      $tag->label = 'A partir de "' . $grupo->nombre . ' ' . $labelFiltroTipoMinisterio . '"';
      $tag->field = 'filtroGrupo';
      $tag->value = $grupo->id;
      $tag->fieldAux = '';
      $parametrosBusqueda->tagsBusqueda[] = $tag;
    }
    return $personas;
  }

  public function listadoFinalCsv(Request $request)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    $parametrosBusqueda = json_decode($request->parametrosBusqueda);

    /* ESTA PARTE ES PARA OBTENER TODOS LOS CAMPOS SOLICITADOS DENTRO DE LOS SELECTORES QUE SOLO PERTENECEN AL ASISTENTE  */
    $html_pasos_crecimiento = '';
    $contador = 0;

    $arrayCamposInfoPersonal = $request->informacionPersonal ? $request->informacionPersonal : []; //$arrayCamposInfoPersonal
    $arrayPasosCrecimiento = $request->informacionMinisterial ? $request->informacionMinisterial : []; // $arrayPasosCrecimiento
    $arrayDatosCongregacionales = $request->informacionCongregacional ? $request->informacionCongregacional : []; // $arrayDatosCongregacionales
    $arrayCamposExtra = $request->informacionCamposExtras ? $request->informacionCamposExtras : []; // $arrayCamposExtra

    $configuracion = Configuracion::find(1);
    /*$tiposEstadosCiviles = EstadoCiviles();
      $tiposDeIdentificaciones = TipoIdentificacion();
      $tiposDeSangres = TipoSangre();
      $listadoNivelesAcademicos = NivelAcademico();
      $listadoEstadosNivelesAcademicos = EstadoNivelAcademico();
      $listadoProfesiones = Profesion();
      $listadoOcupaciones = Ocupacion();
      $listadoSectoresEconomicos = SectorEconomico();
      $tiposVivienda = TipoVivienda();*/

    /// aqui se cmezcla el array de todos los campos seleccionados, tanto de los congregacionales como de la información personal
    $arrayTotalCamposSeleccionados = array_merge($arrayCamposInfoPersonal, $arrayDatosCongregacionales);

    $camposInforme = CampoInformeExcel::whereIn('campos_informe_excel.id', $arrayTotalCamposSeleccionados)
      ->orderBy('orden', 'asc')
      ->get();

    $nombreArchivo = 'informe_personas' . Carbon::now()->format('Y-m-d-H-i-s');
    $rutaArchivo = "/$configuracion->ruta_almacenamiento/informes/personas/$nombreArchivo.csv";

    $archivo = fopen(storage_path('app/public') . $rutaArchivo, 'w');
    fputs($archivo, $bom = chr(0xef) . chr(0xbb) . chr(0xbf));

    /* Aquí se crean los encabezados */
    $arrayEncabezadoFila1 = [];
    $arrayEncabezadoFila2 = [];

    foreach ($camposInforme->pluck('nombre_campo_informe')->toArray() as $campo) {
      array_push($arrayEncabezadoFila1, $campo);
      array_push($arrayEncabezadoFila2, ' ');
    }

    // agrego los pasos de crecimiento al encabezado
    $pasosCrecimientoSeleccionados = PasoCrecimiento::whereIn('id', $arrayPasosCrecimiento)->get();
    foreach ($pasosCrecimientoSeleccionados as $paso) {
      $arrayEncabezadoFila1 = array_merge($arrayEncabezadoFila1, [$paso->nombre, '', '']);
      $arrayEncabezadoFila2 = array_merge($arrayEncabezadoFila2, ['Fecha', 'Estado', 'Detalle']);
    }

    // agrego los campos extra al encabezado
    //$camposExtraSeleccionados = CampoExtra::whereIn('id', $arrayCamposExtra)->orderBy('id', 'asc')->get();
    $camposExtraSeleccionados = CampoFormularioUsuario::where('es_campo_extra', true)->whereIn('id', $arrayCamposExtra)->orderBy('id', 'asc')->get();

    foreach ($camposExtraSeleccionados as $campo) {
      array_push($arrayEncabezadoFila1, $campo->nombre);

      if ($campo->tipo_de_campo == 4) {
        $cantidad_opciones = $campo->opciones_select;
        $cantidad_opciones = json_decode($cantidad_opciones);

        foreach ($cantidad_opciones as $cantidad) {
          array_push($arrayEncabezadoFila1, '');
          array_push($arrayEncabezadoFila2, $cantidad->nombre);
        }
      }

      array_push($arrayEncabezadoFila2, '');
    }

    //return $arrayEncabezadoFila1;
    fputcsv($archivo, $arrayEncabezadoFila1, ';');
    fputcsv($archivo, $arrayEncabezadoFila2, ';');

    // si es un usuario diferente al super administrador
    if ($rolActivo->hasPermissionTo('personas.lista_asistentes_solo_ministerio')) {
      $personas = auth()
        ->user()
        ->discipulos('todos');
    } elseif ($rolActivo->hasPermissionTo('personas.lista_asistentes_todos')) {
      $personas = User::withTrashed()
        ->whereNotNull('email_verified_at')
        ->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
        ->select('users.*', 'integrantes_grupo.grupo_id as grupo_id')
        ->get()
        ->unique('id');
    }

    // filtrado por tipo ejemplo: "Todos o inactivo reunion o por alguno de los tipos de usuario Pastor, lider, oveja etc..."
    $personas = $this->filtroPorTipo($personas, $parametrosBusqueda);

    // filtro por busqueda
    $personas = $this->filtrosBusqueda($personas, $parametrosBusqueda->tipo, $parametrosBusqueda);

    foreach ($personas as $persona) {
      $fila = [];

      //tipo identificación
      if ($camposInforme->where('nombre_campo_bd', 'tipo_identificacion')->count() > 0) {
        array_push($fila, $persona->tipoIdentificacion ? $persona->tipoIdentificacion->nombre : 'Sin información');
      }

      //identificación
      if ($camposInforme->where('nombre_campo_bd', 'identificacion')->count() > 0) {
        array_push($fila, $persona->identificacion ? $persona->identificacion : 'Sin información');
      }

      //edad
      if ($camposInforme->where('nombre_campo_bd', 'edad')->count() > 0) {
        array_push($fila, $persona->fecha_nacimiento ? $persona->edad() : 'Sin información');
      }

      //primer nombre
      if ($camposInforme->where('nombre_campo_bd', 'primer_nombre')->count() > 0) {
        array_push($fila, $persona->primer_nombre ? $persona->primer_nombre : 'Sin información');
      }

      //segundo nombre
      if ($camposInforme->where('nombre_campo_bd', 'segundo_nombre')->count() > 0) {
        array_push($fila, $persona->segundo_nombre ? $persona->segundo_nombre : 'Sin información');
      }

      //primer apellido
      if ($camposInforme->where('nombre_campo_bd', 'primer_apellido')->count() > 0) {
        array_push($fila, $persona->primer_apellido ? $persona->primer_apellido : 'Sin información');
      }

      //segundo apellido
      if ($camposInforme->where('nombre_campo_bd', 'segundo_apellido')->count() > 0) {
        array_push($fila, $persona->segundo_apellido ? $persona->segundo_apellido : 'Sin información');
      }

      //estado civil
      if ($camposInforme->where('nombre_campo_bd', 'estado_civil')->count() > 0) {
        array_push($fila, $persona->estadoCivil ? $persona->estadoCivil->nombre : 'Sin información');
      }

      //pais
      if ($camposInforme->where('nombre_campo_bd', 'pais_id')->count() > 0) {
        array_push($fila, $persona->pais ? $persona->pais->nombre : 'Sin información');
      }

      //telefono fijo
      if ($camposInforme->where('nombre_campo_bd', 'telefono_fijo')->count() > 0) {
        array_push($fila, $persona->telefono_fijo ? $persona->telefono_fijo : 'Sin información');
      }

      //telefono otro
      if ($camposInforme->where('nombre_campo_bd', 'telefono_otro')->count() > 0) {
        array_push($fila, $persona->telefono_otro ? $persona->telefono_otro : 'Sin información');
      }

      //telefono fijo
      if ($camposInforme->where('nombre_campo_bd', 'telefono_movil')->count() > 0) {
        array_push($fila, $persona->telefono_movil ? $persona->telefono_movil : 'Sin información');
      }

      //email - correo electronico
      if ($camposInforme->where('nombre_campo_bd', 'email')->count() > 0) {
        array_push($fila, $persona->email ? $persona->email : 'Sin información');
      }

      //direccion
      if ($camposInforme->where('nombre_campo_bd', 'direccion')->count() > 0) {
        array_push($fila, $persona->direccion ? $persona->direccion : 'Sin información');
      }

      //tipo vivienda
      if ($camposInforme->where('nombre_campo_bd', 'tipo_vivienda')->count() > 0) {
        array_push($fila, $persona->tipoDeVivienda ? $persona->tipoDeVivienda->nombre : 'Sin información');
      }

      //nivel educativo
      if ($camposInforme->where('nombre_campo_bd', 'nivel_academico')->count() > 0) {
        array_push($fila, $persona->nivelAcademico ? $persona->nivelAcademico->nombre : 'Sin información');
      }

      //estado nivel academico
      if ($camposInforme->where('nombre_campo_bd', 'estado_nivel_academico')->count() > 0) {
        array_push($fila, $persona->estadoNivelAcademico ? $persona->estadoNivelAcademico->nombre : 'Sin información');
      }

      //profesion
      if ($camposInforme->where('nombre_campo_bd', 'profesion')->count() > 0) {
        array_push($fila, $persona->profesion ? $persona->profesion->nombre : 'Sin información');
      }

      //sector economico
      if ($camposInforme->where('nombre_campo_bd', 'sector_economico')->count() > 0) {
        array_push($fila, $persona->sectorEconomico ? $persona->sectorEconomico->nombre : 'Sin información');
      }

      //tipo de sangre
      if ($camposInforme->where('nombre_campo_bd', 'tipo_sangre')->count() > 0) {
        array_push($fila, $persona->tipoDeSangre ? $persona->tipoDeSangre->nombre : 'Sin información');
      }

      //indicaciones medicas
      if ($camposInforme->where('nombre_campo_bd', 'indicaciones_medicas')->count() > 0) {
        array_push($fila, $persona->indicaciones_medicas ? $persona->indicaciones_medicas : 'Sin información');
      }

      ///informacion opcional
      if ($camposInforme->where('nombre_campo_bd', 'informacion_opcional')->count() > 0) {
        array_push($fila, $persona->informacion_opcional ? $persona->informacion_opcional : 'Sin información');
      }

      // dados baja
      if (
        $camposInforme->where('nombre_campo_bd', 'dado_baja')->count() > 0 ||
        $camposInforme->where('nombre_campo_bd', 'dado_alta')->count() > 0 ||
        $camposInforme->where('nombre_campo_bd', 'fecha_dado_baja')->count() > 0 ||
        $camposInforme->where('nombre_campo_bd', 'fecha_dado_alta')->count() > 0
      ) {
        $dadoBaja = $persona
          ->reportesBajaAlta()
          ->orderBy('created_at', 'DESC')
          ->first();

        if ($camposInforme->where('nombre_campo_bd', 'dado_alta')->count() > 0) {
          array_push($fila, $dadoBaja && $dadoBaja->dado_baja == false ? $dadoBaja->tipo->nombre : 'Sin información');
        }

        if ($camposInforme->where('nombre_campo_bd', 'dado_baja')->count() > 0) {
          array_push($fila, $dadoBaja && $dadoBaja->dado_baja == true ? $dadoBaja->tipo->nombre : 'Sin información');
        }

        if ($camposInforme->where('nombre_campo_bd', 'fecha_dado_alta')->count() > 0) {
          array_push($fila, $dadoBaja && $dadoBaja->dado_baja == false ? $dadoBaja->fecha : 'Sin fecha de alta');
        }

        if ($camposInforme->where('nombre_campo_bd', 'fecha_dado_baja')->count() > 0) {
          array_push($fila, $dadoBaja && $dadoBaja->dado_baja == true ? $dadoBaja->fecha : 'Sin fecha de baja');
        }
      }

      // contactos acudientes menores
      $edad = $persona->edad();
      if (
        $camposInforme->where('nombre_campo_bd', 'nombre_adulto_responsable')->count() > 0 ||
        $camposInforme->where('nombre_campo_bd', 'contacto_adulto_responsable')->count() > 0
      ) {
        if ($edad < $configuracion->limite_menor_edad) {
          $pariente = DB::table('parientes_usuarios')
            ->where('pariente_user_id', '=', $persona->id)
            ->where('es_el_responsable', '=', true)
            ->first();

          if ($pariente) {
            $pariente = User::select(
              'id',
              'primer_nombre',
              'segundo_nombre',
              'primer_apellido',
              'segundo_apellido',
              'telefono_fijo',
              'telefono_movil'
            )->find($pariente->user_id);

            if ($camposInforme->where('nombre_campo_bd', 'nombre_adulto_responsable')->count() > 0) {
              array_push($fila, $pariente->nombre(3));
            }

            if ($camposInforme->where('nombre_campo_bd', 'contacto_adulto_responsable')->count() > 0) {
              if ($pariente->telefono_fijo) {
                array_push($fila, $pariente->telefono_fijo);
              } elseif ($pariente->telefono_movil) {
                array_push($fila, $pariente->telefono_movil);
              } else {
                array_push($fila, 'Sin información');
              }
            }
          } else {
            array_push($fila, 'No Aplica');
            array_push($fila, 'No Aplica');
          }
        } else {
          array_push($fila, 'No Aplica');
          array_push($fila, 'No Aplica');
        }
      }

      if ($camposInforme->where('nombre_campo_bd', 'nombre_acudiente')->count() > 0) {
        if ($edad < $configuracion->limite_menor_edad) {
          array_push($fila, $persona->nombre_acudiente ? $persona->nombre_acudiente : 'Sin información');
        } else {
          array_push($fila, 'No Aplica');
        }
      }

      if ($camposInforme->where('nombre_campo_bd', 'telefono_acudiente')->count() > 0) {
        if ($edad < $configuracion->limite_menor_edad) {
          array_push($fila, $persona->telefono_acudiente ? $persona->telefono_acudiente : 'Sin información');
        } else {
          array_push($fila, 'No Aplica');
        }
      }

      //fecha nacimiento
      if ($camposInforme->where('nombre_campo_bd', 'fecha_nacimiento')->count() > 0) {
        array_push($fila, $persona->fecha_nacimiento ? $persona->fecha_nacimiento : 'Sin información');
      }

      //sexo
      if ($camposInforme->where('nombre_campo_bd', 'genero')->count() > 0) {
        array_push($fila, $persona->genero == 1 ? 'Femenino' : 'Masculino');
      }

      // Ultimo reporte grupo
      if ($camposInforme->where('nombre_campo_bd', 'ultimo_reporte_grupo')->count() > 0) {
        array_push(
          $fila,
          $persona->ultimo_reporte_grupo
            ? Carbon::parse($persona->ultimo_reporte_grupo)->format('Y-m-d')
            : 'Sin información'
        );
      }

      // Ultimo reporte reunion
      if ($camposInforme->where('nombre_campo_bd', 'ultimo_reporte_reunion')->count() > 0) {
        array_push(
          $fila,
          $persona->ultimo_reporte_reunion
            ? Carbon::parse($persona->ultimo_reporte_reunion)->format('Y-m-d')
            : 'Sin información'
        );
      }

      // tipo vinculacion
      if ($camposInforme->where('nombre_campo_bd', 'tipo_vinculacion_id')->count() > 0) {
        array_push($fila, $persona->tipoVinculacion ? $persona->tipoVinculacion->nombre : 'Sin información');
      }

      //tipo asistente
      if ($camposInforme->where('nombre_campo_bd', 'tipo_asistente_id')->count() > 0) {
        array_push($fila, $persona->tipoUsuario ? $persona->tipoUsuario->nombre : 'Sin información');
      }

      //grupo al que pertenece
      if ($camposInforme->where('nombre_campo_bd', 'grupo_id')->count() > 0) {
        $grupo = $persona
          ->gruposDondeAsiste()
          ->orderBy('grupo_id', 'desc')
          ->first();
        array_push($fila, $grupo ? $grupo->nombre : 'Sin información');
      }

      //sede
      if ($camposInforme->where('nombre_campo_bd', 'sede_id')->count() > 0) {
        array_push($fila, $persona->sede ? $persona->sede->nombre : 'Sin información');
      }

      //Fecha Creación
      if ($camposInforme->where('nombre_campo_bd', 'created_at')->count() > 0) {
        array_push($fila, $persona->created_at ? $persona->created_at : 'Sin información');
      }

      //Usuario Creación
      // Antes tambien tenia asistente_de_creacion_id pero ya quedo obsoleto porque se uniero la tabla user y la tabla asistentes
      if ($camposInforme->where('nombre_campo_bd', 'usuario_creacion_id')->count() > 0) {
        array_push($fila, $persona->usuarioCreacion ? $persona->usuarioCreacion->nombre(3) : 'Formulario nuevos');
      }

      //Recepcion Conectate
      if ($camposInforme->where('nombre_campo_bd', 'formulario_conectados')->count() > 0) {
        array_push($fila, $persona->formulario_conectados ? 'SI' : 'NO');
      }

      //AQUI EMPIEZA EL CONSTRUCTOR DE PASOS EXTRA
      foreach ($camposExtraSeleccionados as $campo) {
        $campoExtraUsuario = $persona
          ->camposFormularioUsuario()
          ->where('campos_formulario_usuario.id', $campo->id)
          ->first();
        if ($campo->tipo_de_campo == 1) {
          array_push($fila, $campoExtraUsuario ? $campoExtraUsuario->pivot->valor : 'Sin información');
        }

        if ($campo->tipo_de_campo == 2) {
          array_push($fila, $campoExtraUsuario ? $campoExtraUsuario->pivot->valor : 'Sin información');
        }

        if ($campo->tipo_de_campo == 3) {
          if ($campoExtraUsuario) {
            $json_opciones_campo = json_decode($campo->opciones_select);

            foreach ($json_opciones_campo as $opcion) {
              if ($opcion->value == $campoExtraUsuario->pivot->valor) {
                array_push($fila, $opcion->nombre);
                break;
              }
            }
          } else {
            array_push($fila, 'Sin información');
          }
        }

        if ($campo->tipo_de_campo == 4) {
          $campo_usuario_opciones_seleccionadas = null;

          if (isset($campoExtraUsuario)) {
            $campo_usuario_opciones_seleccionadas = json_decode($campoExtraUsuario->pivot->valor);
          }

          $campo_opciones_select = json_decode($campo->opciones_select);

          foreach ($campo_opciones_select as $opcion) {
            if (
              isset($campo_usuario_opciones_seleccionadas) &&
              in_array($opcion->value, $campo_usuario_opciones_seleccionadas)
            ) {
              array_push($fila, $opcion->nombre);
            } else {
              array_push($fila, 'Sin información');
            }
          }
          array_push($fila, '');
        }
      }

      // AQUI EMPIEZA EL CONSTRUCTOR DE LOS PASOS DE CRECIMIENTO
      foreach ($pasosCrecimientoSeleccionados as $paso) {
        $pasoActual = $persona
          ->pasosCrecimiento()
          ->where('paso_crecimiento_id', '=', $paso->id)
          ->first();

        if ($pasoActual) {
          array_push($fila, $pasoActual->pivot->fecha ? $pasoActual->pivot->fecha : 'Sin Fecha');
          array_push(
            $fila,
            $pasoActual->pivot->estado == 1
              ? 'No Finalizado'
              : ($pasoActual->pivot->estado == 2
                ? 'En Curso'
                : ($pasoActual->pivot->estado == 3
                  ? 'Finalizado'
                  : 'Sin estado'))
          );
          array_push(
            $fila,
            $pasoActual->pivot->detalle
              ? preg_replace("[\n|\r|\n\r]", '', ucwords(mb_strtolower($pasoActual->pivot->detalle)))
              : 'Sin detalle'
          );
        } else {
          array_push($fila, 'Sin fecha');
          array_push($fila, 'Sin estado');
          array_push($fila, 'Sin detalle');
        }
      }
      /// AQUI IMPRIME EN EL DOCUMENTO LA LINEA DE CADA ASISTENTE

      fputcsv($archivo, $fila, ';');
    }

    // Genera el archivo
    fclose($archivo);

    return Redirect::back()->with(
      'success',
      'El informe fue generado con éxito, <a href="' . Storage::url($rutaArchivo) . '" class=" link-success fw-bold" download="' . $nombreArchivo . '.csv"> descargalo aquí</a>'
    );
  }

  public function perfil(User $usuario) //: View
  {
    // Esta línea ejecuta la policy. Si devuelve false, se lanza un error 403 automáticamente.
    $this->authorize('verPerfilUsuarioPolitica', [$usuario, 'principal']);

    //$usuario = User::withTrashed()->find($usuarioId);
    $configuracion = Configuracion::find(1);
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    $formulario = $rolActivo->formularios()->where('tipo_formulario_id', '=', 5)->first();
    $camposExtras = [];
    if ($formulario) {

      $camposExtras = CampoFormularioUsuario::leftJoin('campo_seccion_formulario_usuario', 'campos_formulario_usuario.id', '=', 'campo_seccion_formulario_usuario.campo_id')
        ->leftJoin('secciones_formulario_usuario', 'campo_seccion_formulario_usuario.seccion_id', '=', 'secciones_formulario_usuario.id')
        ->where('secciones_formulario_usuario.formulario_usuario_id', '=', $formulario->id)
        ->where('campos_formulario_usuario.es_campo_extra', true)
        ->select('campos_formulario_usuario.*', 'campo_seccion_formulario_usuario.requerido',  'campo_seccion_formulario_usuario.class')
        ->get();
    }

    $labelPaisNacimiento = null;

    // autogestionPerfil
    $camposExtraAutogestion = $rolActivo->camposExtraAutogestion;

    $camposExtrasHtml = '';

    $camposFormulario = $rolActivo->camposPerfil;


    $continentes = Continente::orderBy('nombre', 'asc')->get();
    $paises = Pais::orderBy('nombre', 'asc')->get();
    $tiposDeVinculacion = TipoVinculacion::orderBy('nombre', 'asc')->get();
    $tiposDeVivienda = TipoVivienda::orderBy('nombre', 'asc')->get();
    $nivelesAcademicos = NivelAcademico::orderBy('nombre', 'asc')->get();
    $estadosNivelesAcademicos = EstadoNivelAcademico::orderBy('nombre', 'asc')->get();
    $tiposDeSangres = TipoSangre::orderBy('nombre', 'asc')->get();
    $profesiones = Profesion::orderBy('nombre', 'asc')->get();
    $ocupaciones = Ocupacion::orderBy('nombre', 'asc')->get();
    $sectoresEconomicos = SectorEconomico::orderBy('nombre', 'asc')->get();
    $sedes = Sede::orderBy('nombre', 'asc')->get();
    $tiposIdentificaciones = TipoIdentificacion::orderBy('nombre', 'asc')->get();
    $tiposDeEstadosCiviles = EstadoCivil::orderBy('nombre', 'asc')->get();
    $sedes = Sede::orderBy('nombre', 'asc')->get();
    $tieneCampoPreguntaViveEn = [];
    if ($formulario) {
      $tieneCampoPreguntaViveEn = CampoFormularioUsuario::leftJoin('campo_seccion_formulario_usuario', 'campos_formulario_usuario.id', '=', 'campo_seccion_formulario_usuario.campo_id')
        ->leftJoin('secciones_formulario_usuario', 'campo_seccion_formulario_usuario.seccion_id', '=', 'secciones_formulario_usuario.id')
        ->where('secciones_formulario_usuario.formulario_usuario_id', '=', $formulario->id)
        ->where('nombre_bd', '=', 'pregunta_vives_en')
        ->select('campos_formulario_usuario.*', 'campo_seccion_formulario_usuario.requerido')
        ->first();
    }

    $dataQr = [
      'id' => $usuario->id,
      'nombre' => $usuario->nombre(3),
      'tipo' => 'perfil'
    ];

    $dataQr = json_encode($dataQr);

    return view('contenido.paginas.usuario.perfil', [
      'rolActivo' => $rolActivo,
      'usuario' => $usuario,
      'configuracion' => $configuracion,
      'labelPaisNacimiento' => $labelPaisNacimiento,
      'camposExtrasHtml' => $camposExtrasHtml,
      'camposFormulario' => $camposFormulario,
      'paises' => $paises,
      'tiposDeVivienda' => $tiposDeVivienda,
      'tiposIdentificaciones' => $tiposIdentificaciones,
      'tiposDeEstadosCiviles' => $tiposDeEstadosCiviles,
      'sedes' => $sedes,
      'nivelesAcademicos' => $nivelesAcademicos,
      'estadosNivelesAcademicos' => $estadosNivelesAcademicos,
      'profesiones' => $profesiones,
      'ocupaciones' => $ocupaciones,
      'sectoresEconomicos' => $sectoresEconomicos,
      'tiposDeSangres' => $tiposDeSangres,
      'tiposDeVinculacion' => $tiposDeVinculacion,
      'camposExtraAutogestion' => $camposExtraAutogestion,
      'formulario' => $formulario,
      'tieneCampoPreguntaViveEn' => $tieneCampoPreguntaViveEn,
      'dataQr' => $dataQr
    ]);
  }

  public function perfilFamilia(User $usuario): View
  {
    // Esta línea ejecuta la policy. Si devuelve false, se lanza un error 403 automáticamente.
    $this->authorize('verPerfilUsuarioPolitica', [$usuario, 'familia']);

    //$usuario = User::withTrashed()->find($usuarioId);
    $configuracion = Configuracion::find(1);
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    $parientes = $usuario
      ->parientesDelUsuario()
      ->leftJoin('tipos_parentesco', 'parientes_usuarios.tipo_pariente_id', '=', 'tipos_parentesco.id')
      ->select(
        'users.id',
        'users.foto',
        'users.identificacion',
        'users.primer_nombre',
        'users.segundo_nombre',
        'users.primer_apellido',
        'users.segundo_apellido',
        'users.tipo_identificacion_id',
        'tipos_parentesco.nombre as nombre_parentesco',
        'tipos_parentesco.nombre_masculino',
        'tipos_parentesco.nombre_femenino',
        'parientes_usuarios.es_el_responsable'
      )
      ->get();

    return view('contenido.paginas.usuario.perfil-familia', [
      'usuario' => $usuario,
      'rolActivo' => $rolActivo,
      'configuracion' => $configuracion,
      'parientes' => $parientes
    ]);
  }

  public function perfilCongregacion(User $usuario) //: View
  {
    // Esta línea ejecuta la policy. Si devuelve false, se lanza un error 403 automáticamente.
    $this->authorize('verPerfilUsuarioPolitica', [$usuario, 'congregacion']);

    $configuracion = Configuracion::find(1);
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    $roles = $usuario
      ->roles()
      ->wherePivot('dependiente', false)
      ->get()
      ->pluck('name')
      ->toArray();
    $encargadosAscendentes = $usuario
      ->lideres()
      ->select(
        'id',
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'tipo_usuario_id',
        'foto'
      )
      ->orderby('tipo_usuario_id', 'asc')
      ->get();

    $gruposAscendentes = $usuario
      ->lideres()
      ->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
      ->leftJoin('grupos', 'integrantes_grupo.grupo_id', '=', 'grupos.id')
      ->leftJoin('tipo_grupos', 'grupos.tipo_grupo_id', '=', 'tipo_grupos.id')
      ->whereNotNull('integrantes_grupo.grupo_id')
      ->select('grupos.id', 'grupos.nombre', 'tipo_grupos.nombre as nombreTipo')
      ->get();


    $gruposEncargados = $usuario->gruposEncargados;

    $totalGrupos = $usuario
      ->gruposMinisterio()
      ->where('dado_baja', 0)
      ->count();
    $totalGruposDirectos = $gruposEncargados->where('dado_baja', 0)->count();
    $totalGruposIndirectos = $totalGrupos - $totalGruposDirectos;

    $gruposExcluidos = $usuario->gruposExcluidos;

    $serviciosPrestadosEnGrupos = $usuario->serviciosPrestadosEnGrupos();

    $gruposDondeAsiste = $usuario->gruposDondeAsiste;
    $encargadosDirectos = $usuario->encargadosDirectos();

    $pasosDeCrecimiento = PasoCrecimiento::orderBy('updated_at', 'asc')
      ->select('id', 'nombre')
      ->get();

    $pasosDeCrecimiento->map(function ($paso) use ($usuario) {
      $pasoUsuario = CrecimientoUsuario::where('user_id', $usuario->id)
        ->where('paso_crecimiento_id', $paso->id)
        ->first();
      $paso->clase_color = 'danger';
      $paso->estado_fecha = null;
      $paso->estado_paso = 1;
      $paso->estado_nombre = 'No realizado';
      $paso->detalle_paso = '';
      $paso->bandera = 'default';

      if ($pasoUsuario) {
        $paso->clase_color = $pasoUsuario->estado->color;
        $paso->estado_fecha = $pasoUsuario->fecha;
        $paso->estado_paso = $pasoUsuario->estado_id;
        $paso->estado_nombre = $pasoUsuario->estado->nombre;
        $paso->detalle_paso = $pasoUsuario->detalle;
        $paso->bandera = 'si existe';
      }
    });

    $año = Carbon::now()->year;
    $cantidadMeses = 11;
    $fechaBase = Carbon::now()->format('Y-m-d');
    $meses = Helpers::meses('corto');
    $dataReportesReunion = [];
    $serieReporesReunion = [];

    $dataReportesGrupo = [];
    $serieReporesGrupo = [];

    //$grupoLast =  $usuario->gruposDondeAsiste()->get()->last();

    for ($i = $cantidadMeses; $i >= 0; $i--) {
      $fechaInicio = Carbon::parse($fechaBase)->subMonths($i)->startOfMonth()->format('Y-m-d');
      $fechaFin = Carbon::parse($fechaBase)->subMonths($i)->endOfMonth()->format('Y-m-d');
      $mesNumero = Carbon::parse($fechaBase)->subMonths($i)->month;

      $asistenciasReuniones = $usuario
        ->reportesReunion()
        ->where('reporte_reuniones.fecha', '>=', $fechaInicio)
        ->where('reporte_reuniones.fecha', '<=', $fechaFin)
        ->select('reporte_reuniones.id')
        ->get();

      $asistenciasGrupos = $usuario
        ->reportesGrupo()
        ->where('asistencia_grupos.asistio', true)
        ->where('reporte_grupos.fecha', '>=', $fechaInicio)
        ->where('reporte_grupos.fecha', '<=', $fechaFin)
        ->select('reporte_grupos.id')
        ->get();

      $dataReportesReunion[] = $asistenciasReuniones->count();
      $serieReporesReunion[] = $meses[$mesNumero - 1];

      $dataReportesGrupo[] = $asistenciasGrupos->count();
      $serieReporesGrupo[] = $meses[$mesNumero - 1];
    }

    $peticiones = $usuario->peticiones;

    return view('contenido.paginas.usuario.perfil-congregacion', [
      'rolActivo' => $rolActivo,
      'gruposExcluidos' => $gruposExcluidos,
      'usuario' => $usuario,
      'configuracion' => $configuracion,
      'roles' => $roles,
      'encargadosAscendentes' => $encargadosAscendentes,
      'gruposAscendentes' => $gruposAscendentes,
      'gruposEncargados' => $gruposEncargados,
      'totalGrupos' => $totalGrupos,
      'totalGruposDirectos' => $totalGruposDirectos,
      'totalGruposIndirectos' => $totalGruposIndirectos,
      'serviciosPrestadosEnGrupos' => $serviciosPrestadosEnGrupos,
      'gruposDondeAsiste' => $gruposDondeAsiste,
      'encargadosDirectos' => $encargadosDirectos,
      'pasosDeCrecimiento' => $pasosDeCrecimiento,
      'dataReportesReunion' => $dataReportesReunion,
      'serieReporesReunion' => $serieReporesReunion,
      'dataReportesGrupo' => $dataReportesGrupo,
      'serieReporesGrupo' => $serieReporesGrupo,
      'peticiones' => $peticiones,
    ]);
  }

  public function historialEscuelas(User $usuario)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('personas.subitem_lista_asistentes');
    $configuracion = Configuracion::first();

    // Cargamos las escuelas con sus materias, ordenadas académicamente si es posible
    // Y cargamos los resultados del usuario para esas materias
    $escuelas = Escuela::with(['materias' => function($query) {
        $query->orderBy('caracter_obligatorio', 'desc')
              ->orderBy('nombre', 'asc');
    }])->get();

    $materiasAprobadas = $usuario->materiasAprobadasRelacion()
        ->get()
        ->keyBy('materia_id');

    // Procesamos el progreso por escuela
    foreach ($escuelas as $escuela) {
        $totalObligatorias = $escuela->materias->where('caracter_obligatorio', true)->count();
        $aprobadasObligatorias = 0;

        foreach ($escuela->materias as $materia) {
            $resultado = $materiasAprobadas->get($materia->id);
            $materia->resultado = $resultado;

            if ($materia->caracter_obligatorio && $resultado && $resultado->aprobado) {
                $aprobadasObligatorias++;
            }
        }

        $escuela->progreso = $totalObligatorias > 0
            ? round(($aprobadasObligatorias / $totalObligatorias) * 100)
            : 0;
        $escuela->total_obligatorias = $totalObligatorias;
        $escuela->aprobadas_obligatorias = $aprobadasObligatorias;
    }

    return view('contenido.paginas.usuario.perfil-historial-escuelas', [
      'rolActivo' => $rolActivo,
      'usuario' => $usuario,
      'escuelas' => $escuelas,
      'configuracion'=>$configuracion
    ]);
  }

  public function descargarCodigoQr(User $usuario)
  {
    $configuracion = Configuracion::find(1);

    $foto = url('') . Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario' . '/' . $usuario->foto);

    if ($configuracion->version == 2) {
      $foto = $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario' . '/' . $usuario->foto;
    }

    $dataQr = [
      'id' => $usuario->id,
      'nombre' => $usuario->nombre(3),
      'tipo' => 'perfil'
    ];

    $dataQr = json_encode($dataQr);

    $pdf = PDF::loadView('contenido.paginas.usuario.codigoQr', [
      'title' => 'Mi QR',
      'usuario' => $usuario,
      'configuracion' => $configuracion,
      'foto' => $foto,
      'dataQr' => $dataQr
    ]);
    //return $pdf->stream();
    return $pdf->download('Mi QR-' . $usuario->nombre(2) . '.pdf');
  }

  public function nuevo(FormularioUsuario $formulario, $grupoId = null)
  {
    $configuracion = Configuracion::find(1);

    // Esta línea ejecuta la policy. Si devuelve false, se lanza un error 403 automáticamente.
    $this->authorize('nuevoUsuarioPolitica', [User::class, $formulario]);

    $layout = $formulario->tipo->layout;
    $view = $formulario->tipo->view;

    $fechaHoy = Carbon::now();
    $fechaDefault = Carbon::now()
      ->subYears($formulario->edad_minima)
      ->format('Y-m-d');

    $rolActivo =
      $formulario->tipo->es_formulario_exterior == false
      ? auth()->user()->roles()->wherePivot('activo', true)->first()
      : null;

    $continentes = Continente::orderBy('nombre', 'asc')->get();
    $paises = Pais::orderBy('nombre', 'asc')->get();
    $tiposDeVinculacion = TipoVinculacion::orderBy('nombre', 'asc')->get();
    $tiposDeVivienda = TipoVivienda::orderBy('nombre', 'asc')->get();
    $nivelesAcademicos = NivelAcademico::orderBy('nombre', 'asc')->get();
    $estadosNivelesAcademicos = EstadoNivelAcademico::orderBy('nombre', 'asc')->get();
    $tiposDeSangres = TipoSangre::orderBy('nombre', 'asc')->get();
    $profesiones = Profesion::orderBy('nombre', 'asc')->get();
    $ocupaciones = Ocupacion::orderBy('nombre', 'asc')->get();
    $sectoresEconomicos = SectorEconomico::orderBy('nombre', 'asc')->get();
    $sedes = Sede::orderBy('nombre', 'asc')->get();
    $tipoPeticiones = TipoPeticion::orderBy('orden', 'asc')->get();
    $tiposDeVinculacion = TipoVinculacion::orderBy('nombre', 'asc')->get();
    $tiposIdentificaciones = TipoIdentificacion::orderBy('nombre', 'asc')->get();
    $tiposDeEstadosCiviles = EstadoCivil::orderBy('nombre', 'asc')->get();
    $tiposParentescos = TipoParentesco::where('para_menores', true)->orderBy('nombre', 'asc')->get();

    $tipoParentescoDefault = TipoParentesco::where('default', true)->orderBy('nombre', 'asc')->first();


    $camposExtrasFormulario = $formulario
      ->camposExtras()
      ->orderBy('id')
      ->get();

    //$aux=Input::get('aux');
    $aux = null;

    $tieneCampoPreguntaViveEn = CampoFormularioUsuario::leftJoin('campo_seccion_formulario_usuario', 'campos_formulario_usuario.id', '=', 'campo_seccion_formulario_usuario.campo_id')
      ->leftJoin('secciones_formulario_usuario', 'campo_seccion_formulario_usuario.seccion_id', '=', 'secciones_formulario_usuario.id')
      ->where('secciones_formulario_usuario.formulario_usuario_id', '=', $formulario->id)
      ->where('nombre_bd', '=', 'pregunta_vives_en')
      ->select('campos_formulario_usuario.*', 'campo_seccion_formulario_usuario.requerido')
      ->first();

    $secciones = $formulario->secciones()->orderBy('orden', 'asc')->get();
    $cantidadTotalSecciones = $secciones->count();

    return view($view, [
      'configuracion' => $configuracion,
      'layout' => $layout,
      'formulario' => $formulario,
      'rolActivo' => $rolActivo,
      'continentes' => $continentes,
      'paises' => $paises,
      'tiposDeVivienda' => $tiposDeVivienda,
      'tiposDeVinculacion' => $tiposDeVinculacion,
      'nivelesAcademicos' => $nivelesAcademicos,
      'estadosNivelesAcademicos' => $estadosNivelesAcademicos,
      'tiposIdentificaciones' => $tiposIdentificaciones,
      'camposExtrasFormulario' => $camposExtrasFormulario,
      'profesiones' => $profesiones,
      'ocupaciones' => $ocupaciones,
      'sectoresEconomicos' => $sectoresEconomicos,
      'tiposDeSangres' => $tiposDeSangres,
      'tipoPeticiones' => $tipoPeticiones,
      'tiposDeEstadosCiviles' => $tiposDeEstadosCiviles,
      'sedes' => $sedes,
      'fechaDefault' => $fechaDefault,
      'fechaHoy' => $fechaHoy,
      'aux' => $aux,
      'tieneCampoPreguntaViveEn' => $tieneCampoPreguntaViveEn,
      'secciones' => $secciones,
      'cantidadTotalSecciones' => $cantidadTotalSecciones,
      'tiposParentescos' => $tiposParentescos,
      'tipoParentescoDefault' => $tipoParentescoDefault,
      'grupoId' => $grupoId
    ]);
  }

  public function crear(Request $request, FormularioUsuario $formulario)
  {
    $configuracion = Configuracion::find(1);
    $rolActivo = null;

    if ($formulario->tipo->es_formulario_exterior == false && auth()->user()) {
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    }
    $validacion = [];

    $campos = CampoFormularioUsuario::leftJoin('campo_seccion_formulario_usuario', 'campos_formulario_usuario.id', '=', 'campo_seccion_formulario_usuario.campo_id')
      ->leftJoin('secciones_formulario_usuario', 'campo_seccion_formulario_usuario.seccion_id', '=', 'secciones_formulario_usuario.id')
      ->where('secciones_formulario_usuario.formulario_usuario_id', '=', $formulario->id)
      ->select('campos_formulario_usuario.*', 'campo_seccion_formulario_usuario.requerido')
      ->get();

    $usuario = new User;

    // primer_nombre
    if ($campos->where('nombre_bd', 'primer_nombre')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'primer_nombre')->first();
      $validarPrimerNombre = $campoTemporal->requerido ? ['string', 'required', 'max:255'] : ['string', 'nullable', 'max:255'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarPrimerNombre]);
      $usuario->primer_nombre = $request[$campoTemporal->name_id];
    }

    // segundo_nombre
    if ($campos->where('nombre_bd', 'segundo_nombre')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'segundo_nombre')->first();
      $validarSegundoNombre = $campoTemporal->requerido ? ['string', 'required', 'max:255'] : ['string', 'nullable', 'max:255'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarSegundoNombre]);
      $usuario->segundo_nombre = $request[$campoTemporal->name_id];
    }

    // primer_apellido
    if ($campos->where('nombre_bd', 'primer_apellido')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'primer_apellido')->first();
      $validarPrimerApellido = $campoTemporal->requerido ? ['string', 'required', 'max:255'] : ['string', 'nullable', 'max:255'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarPrimerApellido]);
      $usuario->primer_apellido = $request[$campoTemporal->name_id];
    }

    // segundo_apellido
    if ($campos->where('nombre_bd', 'segundo_apellido')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'segundo_apellido')->first();
      $validarSegundoApellido = $campoTemporal->requerido ? ['string', 'required', 'max:255'] : ['string', 'nullable', 'max:255'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarSegundoApellido]);
      $usuario->segundo_apellido = $request[$campoTemporal->name_id];
    }

    //fecha_nacimiento
    if ($campos->where('nombre_bd', 'fecha_nacimiento')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'fecha_nacimiento')->first();
      $validarFechaNacimiento = $campoTemporal->requerido ? ['date', 'required'] : ['date', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarFechaNacimiento]);
      $usuario->fecha_nacimiento = $request[$campoTemporal->name_id];
    }

    // genero
    if ($campos->where('nombre_bd', 'genero')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'genero')->first();
      $validarGenero = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarGenero]);
      $usuario->genero = $request[$campoTemporal->name_id];
    }

    // estado_civil
    if ($campos->where('nombre_bd', 'estado_civil_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'estado_civil_id')->first();
      $validarEstadoCivil = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarEstadoCivil]);
      $usuario->estado_civil_id = $request[$campoTemporal->name_id];
    }

    // Tipo Identificacion
    if ($campos->where('nombre_bd', 'tipo_identificacion_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'tipo_identificacion_id')->first();
      $validarTipoIdentificacion = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoIdentificacion]);
      $usuario->tipo_identificacion_id = $request[$campoTemporal->name_id];
    }

    // Identificacion
    if ($campos->where('nombre_bd', 'identificacion')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'identificacion')->first();
      $validarIdentificacion = $campoTemporal->requerido ? ['string', 'required', 'max:255', Rule::unique('users', 'identificacion')] : ['string', 'nullable', 'max:255', Rule::unique('users', 'identificacion')];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarIdentificacion]);
      $usuario->identificacion = $request[$campoTemporal->name_id];
    }

    // pais_nacimiento
    if ($campos->where('nombre_bd', 'pais_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'pais_id')->first();
      $validarPaisNacimiento = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarPaisNacimiento]);
      $usuario->pais_id = $request[$campoTemporal->name_id];
    }

    // vivienda_en_calidad_de
    if ($campos->where('nombre_bd', 'tipo_vivienda_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'tipo_vivienda_id')->first();
      $validarViviendaEnCalidadDe = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarViviendaEnCalidadDe]);
      $usuario->tipo_vivienda_id = $request[$campoTemporal->name_id];
    }

    // direccion
    if ($campos->where('nombre_bd', 'direccion')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'direccion')->first();
      $validarDireccion = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarDireccion]);
      $usuario->direccion = $request[$campoTemporal->name_id];
    }

    // telefono_fijo
    if ($campos->where('nombre_bd', 'telefono_fijo')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'telefono_fijo')->first();
      $validarTelefonoFijo = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTelefonoFijo]);
      $usuario->telefono_fijo = $request[$campoTemporal->name_id];
    }

    // telefono_movil
    if ($campos->where('nombre_bd', 'telefono_movil')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'telefono_movil')->first();
      $validarTelefonoMovil = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTelefonoMovil]);
      $usuario->telefono_movil = $request[$campoTemporal->name_id];
    }

    // telefono_otro
    if ($campos->where('nombre_bd', 'telefono_otro')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'telefono_otro')->first();
      $validarTelefonoOtro = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTelefonoOtro]);
      $usuario->telefono_otro = $request[$campoTemporal->name_id];
    }

    // Email
    if ($campos->where('nombre_bd', 'email')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'email')->first();
      $validarEmail = $campoTemporal->requerido ? ['string', 'required', 'email', 'max:255', Rule::unique('users', 'email')] : ['string', 'nullable', 'email', 'max:255', Rule::unique('users', 'email')];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarEmail]);
      $usuario->email = strtolower($request[$campoTemporal->name_id]);
    }

    // nivel_academico
    if ($campos->where('nombre_bd', 'nivel_academico_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'nivel_academico_id')->first();
      $validarNivelAcademico = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarNivelAcademico]);
      $usuario->nivel_academico_id = $request[$campoTemporal->name_id];
    }

    // estado_nivel_academico
    if ($campos->where('nombre_bd', 'estado_nivel_academico_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'estado_nivel_academico_id')->first();
      $validarEstadoNivelAcademico = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarEstadoNivelAcademico]);
      $usuario->estado_nivel_academico_id = $request[$campoTemporal->name_id];
    }

    // profesion
    if ($campos->where('nombre_bd', 'profesion_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'profesion_id')->first();
      $validarProfesion = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarProfesion]);
      $usuario->profesion_id = $request[$campoTemporal->name_id];
    }

    // ocupacion
    if ($campos->where('nombre_bd', 'ocupacion_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'ocupacion_id')->first();
      $validarOcupacion = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarOcupacion]);
      $usuario->ocupacion_id = $request[$campoTemporal->name_id];
    }

    //sector_economico
    if ($campos->where('nombre_bd', 'sector_economico_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'sector_economico_id')->first();
      $validarSectorEconomico = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarSectorEconomico]);
      $usuario->sector_economico_id = $request[$campoTemporal->name_id];
    }


    //tipo_sangre
    if ($campos->where('nombre_bd', 'tipo_sangre_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'tipo_sangre_id')->first();
      $validarTipoSangre = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoSangre]);
      $usuario->tipo_sangre_id = $request[$campoTemporal->name_id];
    }

    //indicaciones_medicas
    if ($campos->where('nombre_bd', 'indicaciones_medicas')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'indicaciones_medicas')->first();
      $validarIndicacionesMedicas = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarIndicacionesMedicas]);
      $usuario->indicaciones_medicas = $request[$campoTemporal->name_id];
    }

    //tipo_identificacion_acudiente
    if ($campos->where('nombre_bd', 'tipo_identificacion_acudiente_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'tipo_identificacion_acudiente_id')->first();
      $validarTipoIdentificacionAcudiente = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoIdentificacionAcudiente]);
      $usuario->tipo_identificacion_acudiente_id = $request[$campoTemporal->name_id];
    }

    //identificacion_acudiente
    if ($campos->where('nombre_bd', 'identificacion_acudiente')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'identificacion_acudiente')->first();
      $validarIdentificacionAcudiente = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarIdentificacionAcudiente]);
      $usuario->identificacion_acudiente = $request[$campoTemporal->name_id];
    }

    //nombre_acudiente
    if ($campos->where('nombre_bd', 'nombre_acudiente')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'nombre_acudiente')->first();
      $validarNombreAcudiente = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarNombreAcudiente]);
      $usuario->nombre_acudiente = $request[$campoTemporal->name_id];
    }

    //telefono_acudiente
    if ($campos->where('nombre_bd', 'telefono_acudiente')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'telefono_acudiente')->first();
      $validarTelefonoAcudiente = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTelefonoAcudiente]);
      $usuario->telefono_acudiente = $request[$campoTemporal->name_id];
    }

    //archivo_a
    if ($campos->where('nombre_bd', 'archivo_a')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'archivo_a')->first();
      if ($request->hasFile('archivo_a')) {
        $validarArchivoA = $campoTemporal->requerido ? ['file', 'required', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')] : ['file', 'nullable', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarArchivoA]);
      }
    }

    //archivo_b
    if ($campos->where('nombre_bd', 'archivo_b')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'archivo_b')->first();
      if ($request->hasFile('archivo_b')) {
        $validarArchivoB = $campoTemporal->requerido ? ['file', 'required', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')] : ['file', 'nullable', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarArchivoB]);
      }
    }

    //archivo_c
    if ($campos->where('nombre_bd', 'archivo_c')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'archivo_c')->first();
      if ($request->hasFile('archivo_c')) {
        $validarArchivoC = $campoTemporal->requerido ? ['file', 'required', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')] : ['file', 'nullable', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarArchivoC]);
      }
    }

    //archivo_d
    if ($campos->where('nombre_bd', 'archivo_d')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'archivo_d')->first();
      if ($request->hasFile('archivo_d')) {
        $validarArchivoD = $campoTemporal->requerido ? ['file', 'required', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')] : ['file', 'nullable', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarArchivoD]);
      }
    }

    //informacion_opcional
    if ($campos->where('nombre_bd', 'informacion_opcional')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'informacion_opcional')->first();
      $validarInformacionOpcional = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarInformacionOpcional]);
      $usuario->informacion_opcional = $request[$campoTemporal->name_id];
    }

    if ($campos->where('nombre_bd', 'campo_reservado')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'campo_reservado')->first();
      $validarInformacionOpcional = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarInformacionOpcional]);
      $usuario->campo_reservado = $request[$campoTemporal->name_id];
    }

    //tipo_vinculacion
    if ($campos->where('nombre_bd', 'tipo_vinculacion_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'tipo_vinculacion_id')->first();
      $validarTipoVinculacion = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoVinculacion]);
      $usuario->tipo_vinculacion_id = $request[$campoTemporal->name_id];
    }

    // sede_id
    if ($campos->where('nombre_bd', 'sede_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'sede_id')->first();
      $validarSede = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarSede]);
      $usuario->sede_id = $request[$campoTemporal->name_id];
    }

    // ubicacion localidad y barrio
    if ($campos->where('nombre_bd', 'ubicacion')->count() > 0) {
      if ($campos->where('nombre_bd', 'pregunta_vives_en')->count() > 0) {
        $preguntaVivesEn = $campos->where('nombre_bd', 'pregunta_vives_en')->first();
        if ($request[$preguntaVivesEn->name_id]) {
          $campoTemporal = $campos->where('nombre_bd', 'ubicacion')->first();
          $validarUbicacion = $campoTemporal->requerido ? ['required'] : ['nullable'];
          $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarUbicacion]);
        }
      } else {
        $campoTemporal = $campos->where('nombre_bd', 'ubicacion')->first();
        $validarUbicacion = $campoTemporal->requerido ? ['required'] : ['nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarUbicacion]);
      }
    }

    // tienes una peticion
    if ($campos->where('nombre_bd', 'tienes_una_peticion')->count() > 0) {
      $campoTienesUnaPeticion = $campos->where('nombre_bd', 'tienes_una_peticion')->first();
      if ($campoTienesUnaPeticion && $request[$campoTienesUnaPeticion->name_id]) {

        // tipo_peticion_id
        if ($campos->where('nombre_bd', 'tipo_peticion_id')->count() > 0) {
          $campoTemporal = $campos->where('nombre_bd', 'tipo_peticion_id')->first();
          $validarTipoPeticion = $campoTemporal->requerido ? ['required'] : ['nullable'];
          $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoPeticion]);
        }

        // descripcion_peticion
        if ($campos->where('nombre_bd', 'descripcion_peticion')->count() > 0) {
          $campoTemporal = $campos->where('nombre_bd', 'descripcion_peticion')->first();
          $validarDescripcionPeticion = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
          $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarDescripcionPeticion]);
        }
      }
    }

    // password
    if ($campos->where('nombre_bd', 'password')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'password')->first();
      $validarPassword = $campoTemporal->requerido ? ['required', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[*.?\-&$#]).+$/', 'confirmed'] :  ['nullable', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[*.?\-&$#]).+$/', 'confirmed'];;
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarPassword]);
      $usuario->password = $request[$campoTemporal->name_id];
    }

    // tipo_pariente_id
    if ($campos->where('nombre_bd', 'tipo_pariente_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'tipo_pariente_id')->first();
      $validarTipoParentesco = $campoTemporal->requerido ? ['numeric', 'required'] : ['numeric', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoParentesco]);
    }

    /// seccion comprobacion campos extras
    foreach ($campos->where('es_campo_extra', true) as $campoExtra) {
      $validarCampoExtra = [];
      $campoExtra->requerido ? array_push($validarCampoExtra, 'required') : '';
      $validacion = array_merge($validacion, [$campoExtra->name_id => $validarCampoExtra]);
    }

    // Validacion de datos
    $request->validate($validacion, [
      'message.required' => 'The message field is required.',
    ]);

    // Validacion de datos
    $request->validate($validacion);

    // Foto default
    $usuario->foto = $request->genero == 0 ? 'default-m.png' : 'default-f.png';

    // contraseña por defecto
    $usuario->password = $configuracion->identificacion_obligatoria
      ? Hash::make($request->identificación)
      : Hash::make('123456');

    // si el campo password existe, redefino la contraseña
    if ($campos->where('nombre_bd', 'password')->count() > 0) {
      $campoPassword = $campos->where('nombre_bd', 'password')->first();
      if ($request[$campoPassword->name_id]) {
        $usuario->password = Hash::make($request[$campoPassword->name_id]);
      }
    }

    $usuario->activo = 1;


    $tipoUsuarioId = null;

    if (!empty($formulario->tipo_usuario_default_id)) {
      // Si el formulario especifica un ID, usamos ese.
      $tipoUsuarioId = $formulario->tipo_usuario_default_id;
    } else {
      // Si no, buscamos el que está marcado como 'default' en la BD.
      $tipoUsuarioId = TipoUsuario::where('default', true)->value('id');
    }

    if (!$tipoUsuarioId) {
      // Si no se encontró un tipo de usuario default, es una situación excepcional.
      throw new \Exception('No se pudo encontrar un tipo de usuario por defecto para asignar.');
    }

    $usuario->tipo_usuario_id = $tipoUsuarioId;
    $usuario->email = uniqid('user_') . '@correopordefecto.com';

    $tienesHijosMenoresDeEdad = $campos->where('nombre_bd', 'tienes_hijos_menores_de_edad')->first();
    $tieneHijos = false;
    if ($tienesHijosMenoresDeEdad && $request[$tienesHijosMenoresDeEdad->name_id] == 'Si') {
      $usuario->tiene_hijos = true;
      $usuario->mostrar_modal_agregar_hijos = true;
    }

    if ($usuario->save()) {
      /// esta sección es para el guardado de los campos extra
      foreach ($campos->where('es_campo_extra', true) as $campoExtra) {
        if ($campoExtra->tipo_de_campo != 4) {
          $usuarioCampoExtra = $usuario
            ->camposFormularioUsuario()
            ->where('campo_formulario_usuario_id', '=', $campoExtra->id)
            ->first();

          if ($usuarioCampoExtra) {
            $usuarioCampoExtra->pivot->valor = ucwords(mb_strtolower($request[$campoExtra->name_id]));
            $usuarioCampoExtra->pivot->save();
          } else {
            $usuario->camposFormularioUsuario()->attach($campoExtra->id, [
              'valor' => ucwords(mb_strtolower($request[$campoExtra->name_id]))
            ]);
          }
        } else {
          $usuarioCampoExtra = $usuario
            ->camposFormularioUsuario()
            ->where('campo_formulario_usuario_id', '=', $campoExtra->id)
            ->first();
          if ($usuarioCampoExtra) {
            $usuarioCampoExtra->pivot->valor = json_encode($request[$campoExtra->name_id]);
            $usuarioCampoExtra->pivot->save();
          } else {
            $usuario
              ->camposFormularioUsuario()
              ->attach($campoExtra->id, [
                'valor' => json_encode($request[$campoExtra->name_id])
              ]);
          }
        }
      }

      // Foto
      $campoFoto = $campos->where('nombre_bd', 'foto')->first();
      if ($campoFoto) {
        if ($request[$campoFoto->name_id]) {
          if ($configuracion->version == 1) {
            $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/');
            !is_dir($path) && mkdir($path, 0777, true);

            $imagenPartes = explode(';base64,', $request[$campoFoto->name_id]);
            $imagenBase64 = base64_decode($imagenPartes[1]);
            $nombreFoto = 'asistente-' . $usuario->id . '.jpg';
            $imagenPath = $path . $nombreFoto;
            file_put_contents($imagenPath, $imagenBase64);
            $usuario->foto = $nombreFoto;
          } else {
            /*
              $s3 = AWS::get('s3');
              $s3->putObject(array(
                'Bucket'     => $_ENV['aws_bucket'],
                'Key'        => $_ENV['aws_carpeta']."/fotos/asistente-".$asistente->id.".jpg",
                'SourceFile' => "img/temp/".Input::get('foto-hide'),
              ));*/
          }
        }
      }
      // fin Foto

      // Email
      $campoEmail = $campos->where('nombre_bd', 'email')->first();
      $email = empty($request[$campoEmail->name_id])
        ? $usuario->id . '@' . 'correopordefecto.com'
        : mb_strtolower($request[$campoEmail->name_id]);
      $usuario->email = $email;

      //documentos adjuntos
      $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/archivos' . '/');
      !is_dir($path) && mkdir($path, 0777, true);

      // archivo_a
      $campoArchivo = $campos->where('nombre_bd', 'archivo_a')->first();
      if ($campoArchivo && $request->hasFile($campoArchivo->name_id)) {
        $extension = $request[$campoArchivo->name_id]->extension();
        $nombreArchivoA = $formulario->label_archivo_a
          ? $formulario->label_archivo_a . $usuario->id . '.' . $extension
          : 'archivo-a' . $usuario->id . '.' . $extension;
        if ($configuracion->version == 1) {
          // elimino el archivo actual
          Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/archivos' . '/' . $usuario->archivo_a);

          $request[$campoArchivo->name_id]->storeAs(
            $configuracion->ruta_almacenamiento . '/archivos' . '/',
            $nombreArchivoA,
            'public'
          );
        } elseif ($configuracion->version == 2) {
          /*
              $s3 = AWS::get('s3');
              $s3->putObject(array(
              'Bucket'     => $_ENV['aws_bucket'],
              'Key'        => $_ENV['aws_carpeta']."/archivos"."/".$nombreArchivoA,
              'SourceFile' => "img/temp/archivo-a-temp-".$asistente->id.".".$extension,
              ));*/
        }
        $usuario->archivo_a = $nombreArchivoA;
        $usuario->save();
      }

      // archivo_b
      $campoArchivo = $campos->where('nombre_bd', 'archivo_b')->first();
      if ($campoArchivo && $request->hasFile($campoArchivo->name_id)) {
        $extension = $request[$campoArchivo->name_id]->extension();
        $nombreArchivoB = $formulario->label_archivo_b
          ? $formulario->label_archivo_b . $usuario->id . '.' . $extension
          : 'archivo-b' . $usuario->id . '.' . $extension;
        if ($configuracion->version == 1) {
          // elimino el archivo actual
          Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/archivos' . '/' . $usuario->archivo_b);

          $request[$campoArchivo->name_id]->storeAs(
            $configuracion->ruta_almacenamiento . '/archivos' . '/',
            $nombreArchivoB,
            'public'
          );
        } elseif ($configuracion->version == 2) {
          /*
              $s3 = AWS::get('s3');
              $s3->putObject(array(
              'Bucket'     => $_ENV['aws_bucket'],
              'Key'        => $_ENV['aws_carpeta']."/archivos"."/".$nombreArchivoB,
              'SourceFile' => "img/temp/archivo-a-temp-".$asistente->id.".".$extension,
              ));*/
        }
        $usuario->archivo_b = $nombreArchivoB;
        $usuario->save();
      }

      // archivo_c
      $campoArchivo = $campos->where('nombre_bd', 'archivo_c')->first();
      if ($campoArchivo && $request->hasFile($campoArchivo->name_id)) {
        $extension = $request[$campoArchivo->name_id]->extension();
        $nombreArchivoC = 'archivo-c' . $usuario->id . '.' . $extension;
        $nombreArchivoC = $formulario->label_archivo_c
          ? $formulario->label_archivo_c . $usuario->id . '.' . $extension
          : 'archivo-c' . $usuario->id . '.' . $extension;
        if ($configuracion->version == 1) {
          // elimino el archivo actual
          Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/archivos' . '/' . $usuario->archivo_c);

          $request[$campoArchivo->name_id]->storeAs(
            $configuracion->ruta_almacenamiento . '/archivos' . '/',
            $nombreArchivoC,
            'public'
          );
        } elseif ($configuracion->version == 2) {
          /*
              $s3 = AWS::get('s3');
              $s3->putObject(array(
              'Bucket'     => $_ENV['aws_bucket'],
              'Key'        => $_ENV['aws_carpeta']."/archivos"."/".$nombreArchivoC,
              'SourceFile' => "img/temp/archivo-a-temp-".$asistente->id.".".$extension,
              ));*/
        }
        $usuario->archivo_c = $nombreArchivoC;
        $usuario->save();
      }

      // archivo_d
      $campoArchivo = $campos->where('nombre_bd', 'archivo_d')->first();
      if ($campoArchivo && $request->hasFile($campoArchivo->name_id)) {
        $extension = $request[$campoArchivo->name_id]->extension();
        $nombreArchivoD = $formulario->label_archivo_d
          ? $formulario->label_archivo_d . $usuario->id . '.' . $extension
          : 'archivo-d' . $usuario->id . '.' . $extension;
        if ($configuracion->version == 1) {
          // elimino el archivo actual
          Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/archivos' . '/' . $usuario->archivo_d);

          $request[$campoArchivo->name_id]->storeAs(
            $configuracion->ruta_almacenamiento . '/archivos' . '/',
            $nombreArchivoD,
            'public'
          );
        } elseif ($configuracion->version == 2) {
          /*
              $s3 = AWS::get('s3');
              $s3->putObject(array(
              'Bucket'     => $_ENV['aws_bucket'],
              'Key'        => $_ENV['aws_carpeta']."/archivos"."/".$nombreArchivoD,
              'SourceFile' => "img/temp/archivo-a-temp-".$asistente->id.".".$extension,
              ));*/
        }
        $usuario->archivo_d = $nombreArchivoD;
        $usuario->save();
      }
      //fin documentos adjuntos

      // Creo todos los Pasos de Crecimiento por defecto
      $pasos_crecimiento = PasoCrecimiento::all();
      foreach ($pasos_crecimiento as $paso) {
        $usuario->pasosCrecimiento()->attach($paso->id, ['estado_id' => '1']);
      }

      if ($formulario->tipo->es_formulario_exterior == true) {
        $formulario->pendiente_por_aprobacion == true
          ? ($usuario->esta_aprobado = false)
          : ($usuario->esta_aprobado = true);
      } else {
        if (isset($rolActivo) && $rolActivo->hasPermissionTo('personas.privilegio_crear_asistentes_aprobados')) {
          $usuario->esta_aprobado = true;
        } else {
          $formulario->pendiente_por_aprobacion == true
            ? ($usuario->esta_aprobado = false)
            : ($usuario->esta_aprobado = true);
        }
      }

      // asignacion al grupo automatica
      $grupo = Grupo::find($request->grupoId);
      if ($grupo) {
        $usuario->cambiarGrupo($grupo->id);
        $tipoViculacionAutomatica = TipoVinculacion::where('por_grupo', true)->first();

        if ($tipoViculacionAutomatica) {
          $usuario->tipo_vinculacion_id = $tipoViculacionAutomatica->id;
          $usuario->save();
        }
      }

      // Peticiones
      $campoTienesUnaPeticion = $campos->where('nombre_bd', 'tienes_una_peticion')->first();
      if ($campoTienesUnaPeticion && $request[$campoTienesUnaPeticion->name_id]) {

        $campoTipoDePetición = $campos->where('nombre_bd', 'tipo_peticion_id')->first();
        $campoDescripcionDeLaPeticion = $campos->where('nombre_bd', 'descripcion_peticion')->first();

        if ($request[$campoTipoDePetición->name_id] && $request[$campoDescripcionDeLaPeticion->name_id]) {
          $fechaPeticion = Carbon::now()->format('Y-m-d');
          $peticion = new Peticion();
          $peticion->autor_creacion_id = auth()->user() ? auth()->user()->id : $usuario->id;
          $peticion->user_id = $usuario->id;
          $peticion->estado = 1;
          $peticion->descripcion = $request[$campoDescripcionDeLaPeticion->name_id];
          $peticion->tipo_peticion_id = $request[$campoTipoDePetición->name_id];
          $peticion->fecha = $fechaPeticion;
          $peticion->pais_id = $usuario->pais_id;
          $peticion->save();

          // Enviar el correo
          $mensaje = $peticion->tipoPeticion->mensaje_parte_1;
          if ($usuario->email != '' && $mensaje != '') {
            $key = config('variables.biblia_key');
            $arrContextOptions = [
              'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
              ],
            ];

            try {
              $jsonVersiculos = $peticion->tipoPeticion->json_versiculos;
              if ($jsonVersiculos != '') {
                $jsonVersiculos = json_decode($jsonVersiculos);
                $cantidadItems = count($jsonVersiculos);
                $random = rand(1, $cantidadItems);
                $respuestaText = file_get_contents(
                  'https://api.biblia.com/v1/bible/content/RVR60.txt?passage=' .
                    $jsonVersiculos[$random - 1]->cita .
                    '&key=' .
                    $key .
                    '&style=neVersePerLineFullReference&culture=es',
                  false,
                  stream_context_create($arrContextOptions)
                );
                $mensaje .=
                  '<I>' . $respuestaText . '</I> <B>(' . $jsonVersiculos[$random - 1]->titulo . ', RVR60)</B></p>';
              }
            } catch (Exception $e) {
            }

            $mensaje .= $peticion->tipoPeticion->mensaje_parte_2;

            $mailData = new stdClass();
            $mailData->subject = 'Petición';
            $mailData->nombre = $usuario->nombre(3);
            $mailData->mensaje = $mensaje;

            if ($peticion->tipoPeticion->banner_email != '') {
              $mailData->banner =
                $configuracion->version == 1
                ? Storage::url(
                  $configuracion->ruta_almacenamiento . '/img/email/' . $peticion->tipoPeticion->banner_email
                )
                : Storage::url(
                  $configuracion->ruta_almacenamiento . '/img/email/' . $peticion->tipoPeticion->banner_email
                );
            }

            Mail::to($usuario->email)->send(new DefaultMail($mailData));
            //Mail::to('softjuancarlos@gmail.com')->send(new DefaultMail($mailData));
          }
        }
      }

      // ubicacion localida o barrio
      $campoUbicacion = $campos->where('nombre_bd', 'ubicacion')->first();
      if ($campoUbicacion && $request[$campoUbicacion->name_id]) {

        if ($request->tipoUbicacion == 'Localidad') {
          $usuario->localidad_id = $request[$campoUbicacion->name_id];
        } elseif ($request->tipoUbicacion == 'Barrio') {
          $barrio = Barrio::find($request[$campoUbicacion->name_id]);

          if ($barrio) {
            $usuario->barrio_id = $barrio->id;

            if ($barrio->localidad) {
              $usuario->localidad_id = $barrio->localidad->id;
            }
          }
        }
      }

      // Asignacion de parentesco
      $campoTipoParentesco = $campos->where('nombre_bd', 'tipo_pariente_id')->first();
      if ($campoTipoParentesco && $request[$campoTipoParentesco->name_id]) {
        $parientePrincipal = auth()->user();
        $tiposParentescoSeleccionado = TipoParentesco::find($request[$campoTipoParentesco->name_id]);
        $tipoParentescoPariente = $tiposParentescoSeleccionado;

        if (isset($tiposParentescoSeleccionado->relacionado_con)) {
          $tipoParentescoPariente = TipoParentesco::find($tiposParentescoSeleccionado->relacionado_con);
        }

        $asistenteResponsable = true;
        $parienteResponsable = false;

        // Esta es la relacion del asistente con el pariente
        $parientePrincipal->usuariosDelPariente()->attach(
          $usuario->id,
          array(
            "es_el_responsable" => $parienteResponsable,
            "tipo_pariente_id" =>  $tipoParentescoPariente->id,
          )
        );

        // Esta es la relacion del pariente con el asistente
        $parientePrincipal->parientesDelUsuario()->attach(
          $usuario->id,
          array(
            "es_el_responsable" => $asistenteResponsable,
            "tipo_pariente_id" =>  $tiposParentescoSeleccionado->id,
            "acepto_terminos_condiciones" => true
          )
        );
      }

      //Asignamos el tipo ROL al usuario
      $usuario->roles()->attach($usuario->tipoUsuario->id_rol_dependiente, [
        'activo' => true,
        'dependiente' => true,
        'model_type' => 'App\Models\User',
      ]);

      //nuevo codigo para hacer seguimiento o bitacora de quien creó a la persona
      if (auth()->user() && $rolActivo) {
        // si existe un usuario logueado, realizo la vitacora con el
        $usuario->usuario_creacion_id = auth()->user()->id;
        $usuario->rol_de_creacion_id = $rolActivo->id;
      } else {
        // De lo contrario coloco los valores por defecto
        $usuario->usuario_creacion_id = null;
        $usuario->rol_de_creacion_id = null;
      }

      // Asigna la sede de quién lo crea en caso de no asignarle sede
      if (!isset($usuario->sede_id)) {
        $usuario->asignarSede();
      }

      $usuario->save();

      // Enviar correo de bienvenida
      if ($configuracion->enviar_correo_bienvenida_nuevo_asistente == TRUE) {
        $mailData = new stdClass();
        $mailData->subject = $configuracion->titulo_mensaje_bienvenida;
        $mailData->nombre = $usuario->nombre(3);
        $mailData->mensaje = $configuracion->mensaje_bienvenida;

        if ($configuracion->banner_mensaje_bienvenida) {
          $mailData->banner =
            $configuracion->version == 1
            ? Storage::url(
              $configuracion->ruta_almacenamiento . '/img/email/bienvenida_usuario.png'
            )
            : Storage::url(
              $configuracion->ruta_almacenamiento . '/img/email/bienvenida_usuario.png'
            );
        }

        Mail::to($usuario->email)->send(new DefaultMail($mailData));
        //Mail::to('softjuancarlos@gmail.com')->send(new DefaultMail($mailData));
      }

      event(new Registered($usuario));

      $nombre_completo = $usuario->nombre(3);

      if ($formulario->tipo->redirect != '') {
        if ($formulario->tipo->redirect == 'ingreso-exitoso') {

          Session::put('emailDefault', $usuario->email);

          return view('contenido.paginas.usuario.inscripcion-exitosa', [
            'tieneHijos' => $tieneHijos
          ]);
        }

        if ($formulario->tipo->redirect == 'ingreso-exitoso-hijos') {
          $mensajeTipo = 1;
          return view('contenido.paginas.usuario.inscripcion-exitosa', [
            'mensajeTipo' => $mensajeTipo,
            'usuario' => $usuario
          ]);
        }
      } else {
        return back()->with('success', "La persona <b>$nombre_completo</b> fue creada con éxito.");
      }
    }

    return 'error, no se guardo';
  }

  public function modificar(FormularioUsuario $formulario, User $usuario)
  {
    $configuracion = Configuracion::find(1);

    // Esta línea ejecuta la policy. Si devuelve false, se lanza un error 403 automáticamente.
    $this->authorize('modificarUsuarioPolitica', [User::class, $formulario]);


    $layout = $formulario->tipo->layout;
    $view = $formulario->tipo->view;

    $fechaHoy = Carbon::now();
    $fechaDefault = Carbon::now()
      ->subYears($formulario->edad_minima)
      ->format('Y-m-d');

    $rolActivo =
      $formulario->tipo->es_formulario_exterior == false
      ? auth()->user()->roles()->wherePivot('activo', true)->first()
      : null;

    $continentes = Continente::orderBy('nombre', 'asc')->get();
    $paises = Pais::orderBy('nombre', 'asc')->get();
    $tiposDeVinculacion = TipoVinculacion::orderBy('nombre', 'asc')->get();
    $tiposDeVivienda = TipoVivienda::orderBy('nombre', 'asc')->get();
    $nivelesAcademicos = NivelAcademico::orderBy('nombre', 'asc')->get();
    $estadosNivelesAcademicos = EstadoNivelAcademico::orderBy('nombre', 'asc')->get();
    $tiposDeSangres = TipoSangre::orderBy('nombre', 'asc')->get();
    $profesiones = Profesion::orderBy('nombre', 'asc')->get();
    $ocupaciones = Ocupacion::orderBy('nombre', 'asc')->get();
    $sectoresEconomicos = SectorEconomico::orderBy('nombre', 'asc')->get();
    $sedes = Sede::orderBy('nombre', 'asc')->get();
    $tipoPeticiones = TipoPeticion::orderBy('orden', 'asc')->get();
    $tiposDeVinculacion = TipoVinculacion::orderBy('nombre', 'asc')->get();
    $tiposIdentificaciones = TipoIdentificacion::orderBy('nombre', 'asc')->get();
    $tiposDeEstadosCiviles = EstadoCivil::orderBy('nombre', 'asc')->get();
    $camposExtrasFormulario = $formulario
      ->camposExtras()
      ->orderBy('id')
      ->get();

    //$aux=Input::get('aux');
    $aux = null;

    $tieneCampoPreguntaViveEn = CampoFormularioUsuario::leftJoin('campo_seccion_formulario_usuario', 'campos_formulario_usuario.id', '=', 'campo_seccion_formulario_usuario.campo_id')
      ->leftJoin('secciones_formulario_usuario', 'campo_seccion_formulario_usuario.seccion_id', '=', 'secciones_formulario_usuario.id')
      ->where('secciones_formulario_usuario.formulario_usuario_id', '=', $formulario->id)
      ->where('nombre_bd', '=', 'pregunta_vives_en')
      ->select('campos_formulario_usuario.*', 'campo_seccion_formulario_usuario.requerido')
      ->first();

    return view('contenido.paginas.usuario.modificar', [
      'configuracion' => $configuracion,
      'layout' => $layout,
      'formulario' => $formulario,
      'rolActivo' => $rolActivo,
      'continentes' => $continentes,
      'paises' => $paises,
      'tiposDeVivienda' => $tiposDeVivienda,
      'tiposDeVinculacion' => $tiposDeVinculacion,
      'nivelesAcademicos' => $nivelesAcademicos,
      'estadosNivelesAcademicos' => $estadosNivelesAcademicos,
      'tiposIdentificaciones' => $tiposIdentificaciones,
      'camposExtrasFormulario' => $camposExtrasFormulario,
      'profesiones' => $profesiones,
      'ocupaciones' => $ocupaciones,
      'sectoresEconomicos' => $sectoresEconomicos,
      'tiposDeSangres' => $tiposDeSangres,
      'tipoPeticiones' => $tipoPeticiones,
      'tiposDeEstadosCiviles' => $tiposDeEstadosCiviles,
      'sedes' => $sedes,
      'fechaDefault' => $fechaDefault,
      'fechaHoy' => $fechaHoy,
      'aux' => $aux,
      'usuario' => $usuario,
      'tieneCampoPreguntaViveEn' => $tieneCampoPreguntaViveEn
    ]);
  }

  public function editar(Request $request, FormularioUsuario $formulario, User $usuario)
  {
    $configuracion = Configuracion::find(1);
    $rolActivo = null;

    if ($formulario->tipo->es_formulario_exterior == false && auth()->user()) {
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    }
    $validacion = [];

    $campos = CampoFormularioUsuario::leftJoin('campo_seccion_formulario_usuario', 'campos_formulario_usuario.id', '=', 'campo_seccion_formulario_usuario.campo_id')
      ->leftJoin('secciones_formulario_usuario', 'campo_seccion_formulario_usuario.seccion_id', '=', 'secciones_formulario_usuario.id')
      ->where('secciones_formulario_usuario.formulario_usuario_id', '=', $formulario->id)
      ->select('campos_formulario_usuario.*', 'campo_seccion_formulario_usuario.requerido')
      ->get();


    // primer_nombre
    if ($campos->where('nombre_bd', 'primer_nombre')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'primer_nombre')->first();
      $validarPrimerNombre = $campoTemporal->requerido ? ['string', 'required', 'max:255'] : ['string', 'nullable', 'max:255'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarPrimerNombre]);
      $usuario->primer_nombre = $request[$campoTemporal->name_id];
    }

    // segundo_nombre
    if ($campos->where('nombre_bd', 'segundo_nombre')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'segundo_nombre')->first();
      $validarSegundoNombre = $campoTemporal->requerido ? ['string', 'required', 'max:255'] : ['string', 'nullable', 'max:255'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarSegundoNombre]);
      $usuario->segundo_nombre = $request[$campoTemporal->name_id];
    }

    // primer_apellido
    if ($campos->where('nombre_bd', 'primer_apellido')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'primer_apellido')->first();
      $validarPrimerApellido = $campoTemporal->requerido ? ['string', 'required', 'max:255'] : ['string', 'nullable', 'max:255'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarPrimerApellido]);
      $usuario->primer_apellido = $request[$campoTemporal->name_id];
    }

    // segundo_apellido
    if ($campos->where('nombre_bd', 'segundo_apellido')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'segundo_apellido')->first();
      $validarSegundoApellido = $campoTemporal->requerido ? ['string', 'required', 'max:255'] : ['string', 'nullable', 'max:255'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarSegundoApellido]);
      $usuario->segundo_apellido = $request[$campoTemporal->name_id];
    }

    //fecha_nacimiento
    if ($campos->where('nombre_bd', 'fecha_nacimiento')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'fecha_nacimiento')->first();
      $validarFechaNacimiento = $campoTemporal->requerido ? ['date', 'required'] : ['date', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarFechaNacimiento]);
      $usuario->fecha_nacimiento = $request[$campoTemporal->name_id];
    }

    // genero
    if ($campos->where('nombre_bd', 'genero')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'genero')->first();
      $validarGenero = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarGenero]);
      $usuario->genero = $request[$campoTemporal->name_id];
    }

    // estado_civil
    if ($campos->where('nombre_bd', 'estado_civil_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'estado_civil_id')->first();
      $validarEstadoCivil = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarEstadoCivil]);
      $usuario->estado_civil_id = $request[$campoTemporal->name_id];
    }

    // Tipo Identificacion
    if ($campos->where('nombre_bd', 'tipo_identificacion_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'tipo_identificacion_id')->first();
      $validarTipoIdentificacion = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoIdentificacion]);
      $usuario->tipo_identificacion_id = $request[$campoTemporal->name_id];
    }

    // Identificacion
    if ($campos->where('nombre_bd', 'identificacion')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'identificacion')->first();
      $validarIdentificacion = $campoTemporal->requerido ? ['string', 'required', 'max:255', Rule::unique('users', 'identificacion')->ignore($usuario->id)] : ['string', 'nullable', 'max:255', Rule::unique('users', 'identificacion')->ignore($usuario->id)];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarIdentificacion]);
      $usuario->identificacion = $request[$campoTemporal->name_id];
    }

    // pais_nacimiento
    if ($campos->where('nombre_bd', 'pais_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'pais_id')->first();
      $validarPaisNacimiento = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarPaisNacimiento]);
      $usuario->pais_id = $request[$campoTemporal->name_id];
    }

    // vivienda_en_calidad_de
    if ($campos->where('nombre_bd', 'tipo_vivienda_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'tipo_vivienda_id')->first();
      $validarViviendaEnCalidadDe = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarViviendaEnCalidadDe]);
      $usuario->tipo_vivienda_id = $request[$campoTemporal->name_id];
    }

    // direccion
    if ($campos->where('nombre_bd', 'direccion')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'direccion')->first();
      $validarDireccion = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarDireccion]);
      $usuario->direccion = $request[$campoTemporal->name_id];
    }

    // telefono_fijo
    if ($campos->where('nombre_bd', 'telefono_fijo')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'telefono_fijo')->first();
      $validarTelefonoFijo = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTelefonoFijo]);
      $usuario->telefono_fijo = $request[$campoTemporal->name_id];
    }

    // telefono_movil
    if ($campos->where('nombre_bd', 'telefono_movil')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'telefono_movil')->first();
      $validarTelefonoMovil = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTelefonoMovil]);
      $usuario->telefono_movil = $request[$campoTemporal->name_id];
    }

    // telefono_otro
    if ($campos->where('nombre_bd', 'telefono_otro')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'telefono_otro')->first();
      $validarTelefonoOtro = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTelefonoOtro]);
      $usuario->telefono_otro = $request[$campoTemporal->name_id];
    }

    // Email
    if ($campos->where('nombre_bd', 'email')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'email')->first();
      $validarEmail = $campoTemporal->requerido ? ['string', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)] : ['string', 'nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarEmail]);
      $usuario->email = strtolower($request[$campoTemporal->name_id]);
    }

    // nivel_academico
    if ($campos->where('nombre_bd', 'nivel_academico_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'nivel_academico_id')->first();
      $validarNivelAcademico = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarNivelAcademico]);
      $usuario->nivel_academico_id = $request[$campoTemporal->name_id];
    }

    // estado_nivel_academico
    if ($campos->where('nombre_bd', 'estado_nivel_academico_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'estado_nivel_academico_id')->first();
      $validarEstadoNivelAcademico = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarEstadoNivelAcademico]);
      $usuario->estado_nivel_academico_id = $request[$campoTemporal->name_id];
    }

    // profesion
    if ($campos->where('nombre_bd', 'profesion_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'profesion_id')->first();
      $validarProfesion = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarProfesion]);
      $usuario->profesion_id = $request[$campoTemporal->name_id];
    }

    // ocupacion
    if ($campos->where('nombre_bd', 'ocupacion_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'ocupacion_id')->first();
      $validarOcupacion = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarOcupacion]);
      $usuario->ocupacion_id = $request[$campoTemporal->name_id];
    }

    //sector_economico
    if ($campos->where('nombre_bd', 'sector_economico_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'sector_economico_id')->first();
      $validarSectorEconomico = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarSectorEconomico]);
      $usuario->sector_economico_id = $request[$campoTemporal->name_id];
    }


    //tipo_sangre
    if ($campos->where('nombre_bd', 'tipo_sangre_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'tipo_sangre_id')->first();
      $validarTipoSangre = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoSangre]);
      $usuario->tipo_sangre_id = $request[$campoTemporal->name_id];
    }

    //indicaciones_medicas
    if ($campos->where('nombre_bd', 'indicaciones_medicas')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'indicaciones_medicas')->first();
      $validarIndicacionesMedicas = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarIndicacionesMedicas]);
      $usuario->indicaciones_medicas = $request[$campoTemporal->name_id];
    }

    //tipo_identificacion_acudiente
    if ($campos->where('nombre_bd', 'tipo_identificacion_acudiente_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'tipo_identificacion_acudiente_id')->first();
      $validarTipoIdentificacionAcudiente = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoIdentificacionAcudiente]);
      $usuario->tipo_identificacion_acudiente_id = $request[$campoTemporal->name_id];
    }

    //identificacion_acudiente
    if ($campos->where('nombre_bd', 'identificacion_acudiente')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'identificacion_acudiente')->first();
      $validarIdentificacionAcudiente = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarIdentificacionAcudiente]);
      $usuario->identificacion_acudiente = $request[$campoTemporal->name_id];
    }

    //nombre_acudiente
    if ($campos->where('nombre_bd', 'nombre_acudiente')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'nombre_acudiente')->first();
      $validarNombreAcudiente = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarNombreAcudiente]);
      $usuario->nombre_acudiente = $request[$campoTemporal->name_id];
    }

    //telefono_acudiente
    if ($campos->where('nombre_bd', 'telefono_acudiente')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'telefono_acudiente')->first();
      $validarTelefonoAcudiente = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTelefonoAcudiente]);
      $usuario->telefono_acudiente = $request[$campoTemporal->name_id];
    }

    //archivo_a
    if ($campos->where('nombre_bd', 'archivo_a')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'archivo_a')->first();
      if ($request->hasFile('archivo_a') || $usuario->archivo_a == '') {
        $validarArchivoA = $campoTemporal->requerido ? ['file', 'required', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')] : ['file', 'nullable', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarArchivoA]);
      }
    }

    //archivo_b
    if ($campos->where('nombre_bd', 'archivo_b')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'archivo_b')->first();
      if ($request->hasFile('archivo_b') || $usuario->archivo_b == '') {
        $validarArchivoB = $campoTemporal->requerido ? ['file', 'required', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')] : ['file', 'nullable', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarArchivoB]);
      }
    }

    //archivo_c
    if ($campos->where('nombre_bd', 'archivo_c')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'archivo_c')->first();
      if ($request->hasFile('archivo_c') || $usuario->archivo_c == '') {
        $validarArchivoC = $campoTemporal->requerido ? ['file', 'required', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')] : ['file', 'nullable', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarArchivoC]);
      }
    }

    //archivo_d
    if ($campos->where('nombre_bd', 'archivo_d')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'archivo_d')->first();
      if ($request->hasFile('archivo_d') || $usuario->archivo_d == '') {
        $validarArchivoD = $campoTemporal->requerido ? ['file', 'required', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')] : ['file', 'nullable', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarArchivoD]);
      }
    }

    //informacion_opcional
    if ($campos->where('nombre_bd', 'informacion_opcional')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'informacion_opcional')->first();
      $validarInformacionOpcional = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarInformacionOpcional]);
      $usuario->informacion_opcional = $request[$campoTemporal->name_id];
    }

    if ($campos->where('nombre_bd', 'campo_reservado')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'campo_reservado')->first();
      $validarInformacionOpcional = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarInformacionOpcional]);
      $usuario->campo_reservado = $request[$campoTemporal->name_id];
    }

    //tipo_vinculacion
    if ($campos->where('nombre_bd', 'tipo_vinculacion_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'tipo_vinculacion_id')->first();
      $validarTipoVinculacion = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoVinculacion]);
      $usuario->tipo_vinculacion_id = $request[$campoTemporal->name_id];
    }

    // sede_id
    if ($campos->where('nombre_bd', 'sede_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'sede_id')->first();
      $validarSede = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarSede]);
      $usuario->sede_id = $request[$campoTemporal->name_id];
    }

    // ubicacion localidad y barrio
    if ($campos->where('nombre_bd', 'ubicacion')->count() > 0) {
      if ($campos->where('nombre_bd', 'pregunta_vives_en')->count() > 0) {
        $preguntaVivesEn = $campos->where('nombre_bd', 'pregunta_vives_en')->first();
        if ($request[$preguntaVivesEn->name_id]) {
          $campoTemporal = $campos->where('nombre_bd', 'ubicacion')->first();
          $validarUbicacion = $campoTemporal->requerido ? ['required'] : ['nullable'];
          $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarUbicacion]);
        }
      } else {
        $campoTemporal = $campos->where('nombre_bd', 'ubicacion')->first();
        $validarUbicacion = $campoTemporal->requerido ? ['required'] : ['nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarUbicacion]);
      }
    }

    /// seccion comprobacion campos extras
    foreach ($campos->where('es_campo_extra', true) as $campoExtra) {
      $validarCampoExtra = [];
      $campoExtra->requerido ? array_push($validarCampoExtra, 'required') : '';
      $validacion = array_merge($validacion, [$campoExtra->name_id => $validarCampoExtra]);
    }

    // Validacion de datos
    $request->validate($validacion);

    if ($usuario->save()) {
      //$usuario->foto= $request->genero == 0 ? "default-m.png" : "default-f.png";
      $usuario->fecha_actualizacion = Carbon::now()->format('Y-m-d');
      //$usuario->barrio_id = $request->barrio_id;
      //$usuario->barrio_auxiliar = $request->barrio_auxiliar;

      /// esta sección es para el guardado de los campos extra ($('#ministerio_asociado_principal option:selected').val());
      foreach ($campos->where('es_campo_extra', true) as $campoExtra) {
        if ($campoExtra->tipo_de_campo != 4) {
          $usuarioCampoExtra = $usuario
            ->camposFormularioUsuario()
            ->where('campo_formulario_usuario_id', '=', $campoExtra->id)
            ->first();

          if ($usuarioCampoExtra) {
            $usuarioCampoExtra->pivot->valor = ucwords(mb_strtolower($request[$campoExtra->name_id]));
            $usuarioCampoExtra->pivot->save();
          } else {
            $usuario->camposFormularioUsuario()->attach($campoExtra->id, [
              'valor' => ucwords(mb_strtolower($request[$campoExtra->name_id]))
            ]);
          }
        } else {
          $usuarioCampoExtra = $usuario
            ->camposFormularioUsuario()
            ->where('campo_formulario_usuario_id', '=', $campoExtra->id)
            ->first();
          if ($usuarioCampoExtra) {
            $usuarioCampoExtra->pivot->valor = json_encode($request[$campoExtra->name_id]);
            $usuarioCampoExtra->pivot->save();
          } else {
            $usuario
              ->camposFormularioUsuario()
              ->attach($campoExtra->id, [
                'valor' => json_encode($request[$campoExtra->name_id])
              ]);
          }
        }
      }

      // Foto
      $campoFoto = $campos->where('nombre_bd', 'foto')->first();
      if ($campoFoto) {
        if ($request[$campoFoto->name_id]) {
          if ($configuracion->version == 1) {
            $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/');
            !is_dir($path) && mkdir($path, 0777, true);

            $imagenPartes = explode(';base64,', $request[$campoFoto->name_id]);
            $imagenBase64 = base64_decode($imagenPartes[1]);
            $nombreFoto = 'asistente-' . $usuario->id . '.jpg';
            $imagenPath = $path . $nombreFoto;
            file_put_contents($imagenPath, $imagenBase64);
            $usuario->foto = $nombreFoto;
          } else {
            /*
              $s3 = AWS::get('s3');
              $s3->putObject(array(
                'Bucket'     => $_ENV['aws_bucket'],
                'Key'        => $_ENV['aws_carpeta']."/fotos/asistente-".$asistente->id.".jpg",
                'SourceFile' => "img/temp/".Input::get('foto-hide'),
              ));*/
          }
        }
      }
      // fin Foto

      //documentos adjuntos
      $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/archivos' . '/');
      !is_dir($path) && mkdir($path, 0777, true);

      // archivo_a
      $campoArchivo = $campos->where('nombre_bd', 'archivo_a')->first();
      if ($campoArchivo && $request->hasFile($campoArchivo->name_id)) {
        $extension = $request[$campoArchivo->name_id]->extension();
        $nombreArchivoA = $formulario->label_archivo_a
          ? $formulario->label_archivo_a . $usuario->id . '.' . $extension
          : 'archivo-a' . $usuario->id . '.' . $extension;
        if ($configuracion->version == 1) {
          // elimino el archivo actual
          Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/archivos' . '/' . $usuario->archivo_a);

          $request[$campoArchivo->name_id]->storeAs(
            $configuracion->ruta_almacenamiento . '/archivos' . '/',
            $nombreArchivoA,
            'public'
          );
        } elseif ($configuracion->version == 2) {
          /*
              $s3 = AWS::get('s3');
              $s3->putObject(array(
              'Bucket'     => $_ENV['aws_bucket'],
              'Key'        => $_ENV['aws_carpeta']."/archivos"."/".$nombreArchivoA,
              'SourceFile' => "img/temp/archivo-a-temp-".$asistente->id.".".$extension,
              ));*/
        }
        $usuario->archivo_a = $nombreArchivoA;
        $usuario->save();
      }

      // archivo_b
      $campoArchivo = $campos->where('nombre_bd', 'archivo_b')->first();
      if ($campoArchivo && $request->hasFile($campoArchivo->name_id)) {
        $extension = $request[$campoArchivo->name_id]->extension();
        $nombreArchivoB = $formulario->label_archivo_b
          ? $formulario->label_archivo_b . $usuario->id . '.' . $extension
          : 'archivo-b' . $usuario->id . '.' . $extension;
        if ($configuracion->version == 1) {
          // elimino el archivo actual
          Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/archivos' . '/' . $usuario->archivo_b);

          $request[$campoArchivo->name_id]->storeAs(
            $configuracion->ruta_almacenamiento . '/archivos' . '/',
            $nombreArchivoB,
            'public'
          );
        } elseif ($configuracion->version == 2) {
          /*
              $s3 = AWS::get('s3');
              $s3->putObject(array(
              'Bucket'     => $_ENV['aws_bucket'],
              'Key'        => $_ENV['aws_carpeta']."/archivos"."/".$nombreArchivoB,
              'SourceFile' => "img/temp/archivo-a-temp-".$asistente->id.".".$extension,
              ));*/
        }
        $usuario->archivo_b = $nombreArchivoB;
        $usuario->save();
      }

      // archivo_c
      $campoArchivo = $campos->where('nombre_bd', 'archivo_c')->first();
      if ($campoArchivo && $request->hasFile($campoArchivo->name_id)) {
        $extension = $request[$campoArchivo->name_id]->extension();
        $nombreArchivoC = 'archivo-c' . $usuario->id . '.' . $extension;
        $nombreArchivoC = $formulario->label_archivo_c
          ? $formulario->label_archivo_c . $usuario->id . '.' . $extension
          : 'archivo-c' . $usuario->id . '.' . $extension;
        if ($configuracion->version == 1) {
          // elimino el archivo actual
          Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/archivos' . '/' . $usuario->archivo_c);

          $request[$campoArchivo->name_id]->storeAs(
            $configuracion->ruta_almacenamiento . '/archivos' . '/',
            $nombreArchivoC,
            'public'
          );
        } elseif ($configuracion->version == 2) {
          /*
              $s3 = AWS::get('s3');
              $s3->putObject(array(
              'Bucket'     => $_ENV['aws_bucket'],
              'Key'        => $_ENV['aws_carpeta']."/archivos"."/".$nombreArchivoC,
              'SourceFile' => "img/temp/archivo-a-temp-".$asistente->id.".".$extension,
              ));*/
        }
        $usuario->archivo_c = $nombreArchivoC;
        $usuario->save();
      }

      // archivo_d
      $campoArchivo = $campos->where('nombre_bd', 'archivo_d')->first();
      if ($campoArchivo && $request->hasFile($campoArchivo->name_id)) {
        $extension = $request[$campoArchivo->name_id]->extension();
        $nombreArchivoD = $formulario->label_archivo_d
          ? $formulario->label_archivo_d . $usuario->id . '.' . $extension
          : 'archivo-d' . $usuario->id . '.' . $extension;
        if ($configuracion->version == 1) {
          // elimino el archivo actual
          Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/archivos' . '/' . $usuario->archivo_d);

          $request[$campoArchivo->name_id]->storeAs(
            $configuracion->ruta_almacenamiento . '/archivos' . '/',
            $nombreArchivoD,
            'public'
          );
        } elseif ($configuracion->version == 2) {
          /*
              $s3 = AWS::get('s3');
              $s3->putObject(array(
              'Bucket'     => $_ENV['aws_bucket'],
              'Key'        => $_ENV['aws_carpeta']."/archivos"."/".$nombreArchivoD,
              'SourceFile' => "img/temp/archivo-a-temp-".$asistente->id.".".$extension,
              ));*/
        }
        $usuario->archivo_d = $nombreArchivoD;
        $usuario->save();
      }
      //fin documentos adjuntos

      // ubicacion localida o barrio
      $campoUbicacion = $campos->where('nombre_bd', 'ubicacion')->first();
      if ($campoUbicacion && $request[$campoUbicacion->name_id]) {


        $usuario->localidad_id = null;
        $usuario->barrio_id = null;

        if ($request[$campoUbicacion->name_id]) {
          if ($request->tipoUbicacion == 'Localidad') {
            $usuario->localidad_id = $request[$campoUbicacion->name_id];
          } elseif ($request->tipoUbicacion == 'Barrio') {
            $barrio = Barrio::find($request[$campoUbicacion->name_id]);

            if ($barrio) {
              $usuario->barrio_id = $barrio->id;

              if ($barrio->localidad) {
                $usuario->localidad_id = $barrio->localidad->id;
              }
            }
          }
        }
      }

      $usuario->save();

      $nombre_completo = $usuario->nombre(3);
      if ($formulario->redirect != '') {
        if ($formulario->redirect == '/') {
          return Redirect::to('/inicio')->with([
            'status' => 'ok_update_asistente',
            'mensaje' => 'La persona <b>' . $nombre_completo . '</b> fue actualizada con éxito.',
          ]);
        } elseif ($formulario->redirect == 'peticiones-online') {
          return Redirect::to('/peticiones/formulario-peticiones/' . $usuario->id . '/asistente')->with([
            'status' => 'ok_update_asistente',
            'mensaje' => 'La persona <b>' . $nombre_completo . '</b> fue actualizada con éxito.',
          ]);
        } elseif ($formulario->redirect == 'donaciones-online') {
          return Redirect::to('/ofrendas/formulario-donaciones/' . $usuario->id . '/asistente')->with([
            'status' => 'ok_update_asistente',
            'mensaje' => 'La persona <b>' . $nombre_completo . '</b> fue actualizada con éxito.',
          ]);
        } elseif ($formulario->redirect == 'actividades') {
          $actividad_id = $request->aux;
          return Redirect::to(
            '/actividades/perfil/' . $actividad_id . '/' . 'website/' . $usuario->id . '/asistente'
          )->with([
            'status' => 'ok_update_asistente',
            'mensaje' => 'La persona <b>' . $nombre_completo . '</b> fue actualizada con éxito.',
          ]);
        } elseif ($formulario->redirect == 'listado') {
          return Redirect::to('/asistentes/lista')->with([
            'status' => 'ok_update_asistente',
            'mensaje' => 'La persona <b>' . $nombre_completo . '</b> fue actualizada con éxito.',
          ]);
        } else {
          return Redirect::to($formulario->redirect . '' . $usuario->id)->with([
            'status' => 'ok_update_asistente',
            'mensaje' => 'La persona <b>' . $nombre_completo . '</b> fue actualizada con éxito.',
          ]);
        }
      } else {
        return back()->with('success', "La persona <b>$nombre_completo</b> fue actualizada con éxito.");
      }
    }
  }

  public function autoeditar(Request $request, int $formulario, int $seccion, User $usuario)
  {
    $configuracion = Configuracion::find(1);
    $rolActivo = null;


    $formulario = FormularioUsuario::find($formulario);
    $validacion = [];

    $campos = $campos = CampoFormularioUsuario::leftJoin('campo_seccion_formulario_usuario', 'campos_formulario_usuario.id', '=', 'campo_seccion_formulario_usuario.campo_id')
      ->leftJoin('secciones_formulario_usuario', 'campo_seccion_formulario_usuario.seccion_id', '=', 'secciones_formulario_usuario.id')
      ->where('secciones_formulario_usuario.formulario_usuario_id', '=', $formulario->id)
      ->where('secciones_formulario_usuario.id', '=', $seccion)
      ->select('campos_formulario_usuario.*', 'campo_seccion_formulario_usuario.requerido')
      ->get();

    // primer_nombre
    if ($campos->where('nombre_bd', 'primer_nombre')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'primer_nombre')->first();
      $validarPrimerNombre = $campoTemporal->requerido ? ['string', 'required', 'max:255'] : ['string', 'nullable', 'max:255'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarPrimerNombre]);
      $usuario->primer_nombre = $request[$campoTemporal->name_id];
    }

    // segundo_nombre
    if ($campos->where('nombre_bd', 'segundo_nombre')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'segundo_nombre')->first();
      $validarSegundoNombre = $campoTemporal->requerido ? ['string', 'required', 'max:255'] : ['string', 'nullable', 'max:255'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarSegundoNombre]);
      $usuario->segundo_nombre = $request[$campoTemporal->name_id];
    }

    // primer_apellido
    if ($campos->where('nombre_bd', 'primer_apellido')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'primer_apellido')->first();
      $validarPrimerApellido = $campoTemporal->requerido ? ['string', 'required', 'max:255'] : ['string', 'nullable', 'max:255'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarPrimerApellido]);
      $usuario->primer_apellido = $request[$campoTemporal->name_id];
    }

    // segundo_apellido
    if ($campos->where('nombre_bd', 'segundo_apellido')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'segundo_apellido')->first();
      $validarSegundoApellido = $campoTemporal->requerido ? ['string', 'required', 'max:255'] : ['string', 'nullable', 'max:255'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarSegundoApellido]);
      $usuario->segundo_apellido = $request[$campoTemporal->name_id];
    }

    //fecha_nacimiento
    if ($campos->where('nombre_bd', 'fecha_nacimiento')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'fecha_nacimiento')->first();
      $validarFechaNacimiento = $campoTemporal->requerido ? ['date', 'required'] : ['date', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarFechaNacimiento]);
      $usuario->fecha_nacimiento = $request[$campoTemporal->name_id];
    }

    // genero
    if ($campos->where('nombre_bd', 'genero')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'genero')->first();
      $validarGenero = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarGenero]);
      $usuario->genero = $request[$campoTemporal->name_id];
    }

    // estado_civil
    if ($campos->where('nombre_bd', 'estado_civil_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'estado_civil_id')->first();
      $validarEstadoCivil = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarEstadoCivil]);
      $usuario->estado_civil_id = $request[$campoTemporal->name_id];
    }

    // Tipo Identificacion
    if ($campos->where('nombre_bd', 'tipo_identificacion_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'tipo_identificacion_id')->first();
      $validarTipoIdentificacion = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoIdentificacion]);
      $usuario->tipo_identificacion_id = $request[$campoTemporal->name_id];
    }

    // Identificacion
    if ($campos->where('nombre_bd', 'identificacion')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'identificacion')->first();
      $validarIdentificacion = $campoTemporal->requerido ? ['string', 'required', 'max:255', Rule::unique('users', 'identificacion')->ignore($usuario->id)] : ['string', 'nullable', 'max:255', Rule::unique('users', 'identificacion')->ignore($usuario->id)];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarIdentificacion]);
      $usuario->identificacion = $request[$campoTemporal->name_id];
    }

    // pais_nacimiento
    if ($campos->where('nombre_bd', 'pais_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'pais_id')->first();
      $validarPaisNacimiento = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarPaisNacimiento]);
      $usuario->pais_id = $request[$campoTemporal->name_id];
    }

    // vivienda_en_calidad_de
    if ($campos->where('nombre_bd', 'tipo_vivienda_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'tipo_vivienda_id')->first();
      $validarViviendaEnCalidadDe = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarViviendaEnCalidadDe]);
      $usuario->tipo_vivienda_id = $request[$campoTemporal->name_id];
    }

    // direccion
    if ($campos->where('nombre_bd', 'direccion')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'direccion')->first();
      $validarDireccion = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarDireccion]);
      $usuario->direccion = $request[$campoTemporal->name_id];
    }

    // telefono_fijo
    if ($campos->where('nombre_bd', 'telefono_fijo')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'telefono_fijo')->first();
      $validarTelefonoFijo = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTelefonoFijo]);
      $usuario->telefono_fijo = $request[$campoTemporal->name_id];
    }

    // telefono_movil
    if ($campos->where('nombre_bd', 'telefono_movil')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'telefono_movil')->first();
      $validarTelefonoMovil = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTelefonoMovil]);
      $usuario->telefono_movil = $request[$campoTemporal->name_id];
    }

    // telefono_otro
    if ($campos->where('nombre_bd', 'telefono_otro')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'telefono_otro')->first();
      $validarTelefonoOtro = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTelefonoOtro]);
      $usuario->telefono_otro = $request[$campoTemporal->name_id];
    }

    // Email
    if ($campos->where('nombre_bd', 'email')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'email')->first();
      $validarEmail = $campoTemporal->requerido ? ['string', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)] : ['string', 'nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarEmail]);
      $usuario->email = strtolower($request[$campoTemporal->name_id]);
    }

    // nivel_academico
    if ($campos->where('nombre_bd', 'nivel_academico_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'nivel_academico_id')->first();
      $validarNivelAcademico = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarNivelAcademico]);
      $usuario->nivel_academico_id = $request[$campoTemporal->name_id];
    }

    // estado_nivel_academico
    if ($campos->where('nombre_bd', 'estado_nivel_academico_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'estado_nivel_academico_id')->first();
      $validarEstadoNivelAcademico = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarEstadoNivelAcademico]);
      $usuario->estado_nivel_academico_id = $request[$campoTemporal->name_id];
    }

    // profesion
    if ($campos->where('nombre_bd', 'profesion_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'profesion_id')->first();
      $validarProfesion = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarProfesion]);
      $usuario->profesion_id = $request[$campoTemporal->name_id];
    }

    // ocupacion
    if ($campos->where('nombre_bd', 'ocupacion_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'ocupacion_id')->first();
      $validarOcupacion = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarOcupacion]);
      $usuario->ocupacion_id = $request[$campoTemporal->name_id];
    }

    //sector_economico
    if ($campos->where('nombre_bd', 'sector_economico_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'sector_economico_id')->first();
      $validarSectorEconomico = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarSectorEconomico]);
      $usuario->sector_economico_id = $request[$campoTemporal->name_id];
    }


    //tipo_sangre
    if ($campos->where('nombre_bd', 'tipo_sangre_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'tipo_sangre_id')->first();
      $validarTipoSangre = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoSangre]);
      $usuario->tipo_sangre_id = $request[$campoTemporal->name_id];
    }

    //indicaciones_medicas
    if ($campos->where('nombre_bd', 'indicaciones_medicas')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'indicaciones_medicas')->first();
      $validarIndicacionesMedicas = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarIndicacionesMedicas]);
      $usuario->indicaciones_medicas = $request[$campoTemporal->name_id];
    }

    //tipo_identificacion_acudiente
    if ($campos->where('nombre_bd', 'tipo_identificacion_acudiente_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'tipo_identificacion_acudiente_id')->first();
      $validarTipoIdentificacionAcudiente = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoIdentificacionAcudiente]);
      $usuario->tipo_identificacion_acudiente_id = $request[$campoTemporal->name_id];
    }

    //identificacion_acudiente
    if ($campos->where('nombre_bd', 'identificacion_acudiente')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'identificacion_acudiente')->first();
      $validarIdentificacionAcudiente = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarIdentificacionAcudiente]);
      $usuario->identificacion_acudiente = $request[$campoTemporal->name_id];
    }

    //nombre_acudiente
    if ($campos->where('nombre_bd', 'nombre_acudiente')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'nombre_acudiente')->first();
      $validarNombreAcudiente = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarNombreAcudiente]);
      $usuario->nombre_acudiente = $request[$campoTemporal->name_id];
    }

    //telefono_acudiente
    if ($campos->where('nombre_bd', 'telefono_acudiente')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'telefono_acudiente')->first();
      $validarTelefonoAcudiente = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTelefonoAcudiente]);
      $usuario->telefono_acudiente = $request[$campoTemporal->name_id];
    }

    //archivo_a
    if ($campos->where('nombre_bd', 'archivo_a')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'archivo_a')->first();
      if ($request->hasFile('archivo_a') || $usuario->archivo_a == '') {
        $validarArchivoA = $campoTemporal->requerido ? ['file', 'required', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')] : ['file', 'nullable', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarArchivoA]);
      }
    }

    //archivo_b
    if ($campos->where('nombre_bd', 'archivo_b')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'archivo_b')->first();
      if ($request->hasFile('archivo_b') || $usuario->archivo_b == '') {
        $validarArchivoB = $campoTemporal->requerido ? ['file', 'required', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')] : ['file', 'nullable', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarArchivoB]);
      }
    }

    //archivo_c
    if ($campos->where('nombre_bd', 'archivo_c')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'archivo_c')->first();
      if ($request->hasFile('archivo_c') || $usuario->archivo_c == '') {
        $validarArchivoC = $campoTemporal->requerido ? ['file', 'required', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')] : ['file', 'nullable', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarArchivoC]);
      }
    }

    //archivo_d
    if ($campos->where('nombre_bd', 'archivo_d')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'archivo_d')->first();
      if ($request->hasFile('archivo_d') || $usuario->archivo_d == '') {
        $validarArchivoD = $campoTemporal->requerido ? ['file', 'required', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')] : ['file', 'nullable', File::types(['png', 'jpg', 'jpeg', 'gif', 'pdf'])->max('5mb')];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarArchivoD]);
      }
    }

    //informacion_opcional
    if ($campos->where('nombre_bd', 'informacion_opcional')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'informacion_opcional')->first();
      $validarInformacionOpcional = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarInformacionOpcional]);
      $usuario->informacion_opcional = $request[$campoTemporal->name_id];
    }

    if ($campos->where('nombre_bd', 'campo_reservado')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'campo_reservado')->first();
      $validarInformacionOpcional = $campoTemporal->requerido ? ['string', 'required'] : ['string', 'nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarInformacionOpcional]);
      $usuario->campo_reservado = $request[$campoTemporal->name_id];
    }

    //tipo_vinculacion
    if ($campos->where('nombre_bd', 'tipo_vinculacion_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'tipo_vinculacion_id')->first();
      $validarTipoVinculacion = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarTipoVinculacion]);
      $usuario->tipo_vinculacion_id = $request[$campoTemporal->name_id];
    }

    // sede_id
    if ($campos->where('nombre_bd', 'sede_id')->count() > 0) {
      $campoTemporal = $campos->where('nombre_bd', 'sede_id')->first();
      $validarSede = $campoTemporal->requerido ? ['required'] : ['nullable'];
      $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarSede]);
      $usuario->sede_id = $request[$campoTemporal->name_id];
    }

    // ubicacion localidad y barrio
    if ($campos->where('nombre_bd', 'ubicacion')->count() > 0) {
      if ($campos->where('nombre_bd', 'pregunta_vives_en')->count() > 0) {
        $preguntaVivesEn = $campos->where('nombre_bd', 'pregunta_vives_en')->first();
        if ($request[$preguntaVivesEn->name_id]) {
          $campoTemporal = $campos->where('nombre_bd', 'ubicacion')->first();
          $validarUbicacion = $campoTemporal->requerido ? ['required'] : ['nullable'];
          $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarUbicacion]);
        }
      } else {
        $campoTemporal = $campos->where('nombre_bd', 'ubicacion')->first();
        $validarUbicacion = $campoTemporal->requerido ? ['required'] : ['nullable'];
        $validacion = array_merge($validacion, [$campoTemporal->name_id => $validarUbicacion]);
      }
    }

    /// seccion comprobacion campos extras
    foreach ($campos->where('es_campo_extra', true) as $campoExtra) {
      $validarCampoExtra = [];
      $campoExtra->requerido ? array_push($validarCampoExtra, 'required') : '';
      $validacion = array_merge($validacion, [$campoExtra->name_id => $validarCampoExtra]);
    }

    // Validacion de datos
    $request->validate($validacion);

    if ($usuario->save()) {
      //$usuario->foto= $request->genero == 0 ? "default-m.png" : "default-f.png";
      $usuario->fecha_actualizacion = Carbon::now()->format('Y-m-d');
      //$usuario->barrio_id = $request->barrio_id;
      //$usuario->barrio_auxiliar = $request->barrio_auxiliar;

      /// esta sección es para el guardado de los campos extra ($('#ministerio_asociado_principal option:selected').val());
      foreach ($campos->where('es_campo_extra', true) as $campoExtra) {
        if ($campoExtra->tipo_de_campo != 4) {
          $usuarioCampoExtra = $usuario
            ->camposFormularioUsuario()
            ->where('campo_formulario_usuario_id', '=', $campoExtra->id)
            ->first();

          if ($usuarioCampoExtra) {
            $usuarioCampoExtra->pivot->valor = ucwords(mb_strtolower($request[$campoExtra->name_id]));
            $usuarioCampoExtra->pivot->save();
          } else {
            $usuario->camposFormularioUsuario()->attach($campoExtra->id, [
              'valor' => ucwords(mb_strtolower($request[$campoExtra->name_id]))
            ]);
          }
        } else {
          $usuarioCampoExtra = $usuario
            ->camposFormularioUsuario()
            ->where('campo_formulario_usuario_id', '=', $campoExtra->id)
            ->first();
          if ($usuarioCampoExtra) {
            $usuarioCampoExtra->pivot->valor = json_encode($request[$campoExtra->name_id]);
            $usuarioCampoExtra->pivot->save();
          } else {
            $usuario
              ->camposFormularioUsuario()
              ->attach($campoExtra->id, [
                'valor' => json_encode($request[$campoExtra->name_id])
              ]);
          }
        }
      }

      // Foto
      $campoFoto = $campos->where('nombre_bd', 'foto')->first();
      if ($campoFoto) {
        if ($request[$campoFoto->name_id]) {
          if ($configuracion->version == 1) {
            $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/');
            !is_dir($path) && mkdir($path, 0777, true);

            $imagenPartes = explode(';base64,', $request[$campoFoto->name_id]);
            $imagenBase64 = base64_decode($imagenPartes[1]);
            $nombreFoto = 'asistente-' . $usuario->id . '.jpg';
            $imagenPath = $path . $nombreFoto;
            file_put_contents($imagenPath, $imagenBase64);
            $usuario->foto = $nombreFoto;
          } else {
            /*
              $s3 = AWS::get('s3');
              $s3->putObject(array(
                'Bucket'     => $_ENV['aws_bucket'],
                'Key'        => $_ENV['aws_carpeta']."/fotos/asistente-".$asistente->id.".jpg",
                'SourceFile' => "img/temp/".Input::get('foto-hide'),
              ));*/
          }
        }
      }
      // fin Foto

      //documentos adjuntos
      $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/archivos' . '/');
      !is_dir($path) && mkdir($path, 0777, true);

      // archivo_a
      $campoArchivo = $campos->where('nombre_bd', 'archivo_a')->first();
      if ($campoArchivo && $request->hasFile($campoArchivo->name_id)) {
        $extension = $request[$campoArchivo->name_id]->extension();
        $nombreArchivoA = $formulario->label_archivo_a
          ? $formulario->label_archivo_a . $usuario->id . '.' . $extension
          : 'archivo-a' . $usuario->id . '.' . $extension;
        if ($configuracion->version == 1) {
          // elimino el archivo actual
          Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/archivos' . '/' . $usuario->archivo_a);

          $request[$campoArchivo->name_id]->storeAs(
            $configuracion->ruta_almacenamiento . '/archivos' . '/',
            $nombreArchivoA,
            'public'
          );
        } elseif ($configuracion->version == 2) {
          /*
              $s3 = AWS::get('s3');
              $s3->putObject(array(
              'Bucket'     => $_ENV['aws_bucket'],
              'Key'        => $_ENV['aws_carpeta']."/archivos"."/".$nombreArchivoA,
              'SourceFile' => "img/temp/archivo-a-temp-".$asistente->id.".".$extension,
              ));*/
        }
        $usuario->archivo_a = $nombreArchivoA;
        $usuario->save();
      }

      // archivo_b
      $campoArchivo = $campos->where('nombre_bd', 'archivo_b')->first();
      if ($campoArchivo && $request->hasFile($campoArchivo->name_id)) {
        $extension = $request[$campoArchivo->name_id]->extension();
        $nombreArchivoB = $formulario->label_archivo_b
          ? $formulario->label_archivo_b . $usuario->id . '.' . $extension
          : 'archivo-b' . $usuario->id . '.' . $extension;
        if ($configuracion->version == 1) {
          // elimino el archivo actual
          Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/archivos' . '/' . $usuario->archivo_b);

          $request[$campoArchivo->name_id]->storeAs(
            $configuracion->ruta_almacenamiento . '/archivos' . '/',
            $nombreArchivoB,
            'public'
          );
        } elseif ($configuracion->version == 2) {
          /*
              $s3 = AWS::get('s3');
              $s3->putObject(array(
              'Bucket'     => $_ENV['aws_bucket'],
              'Key'        => $_ENV['aws_carpeta']."/archivos"."/".$nombreArchivoB,
              'SourceFile' => "img/temp/archivo-a-temp-".$asistente->id.".".$extension,
              ));*/
        }
        $usuario->archivo_b = $nombreArchivoB;
        $usuario->save();
      }

      // archivo_c
      $campoArchivo = $campos->where('nombre_bd', 'archivo_c')->first();
      if ($campoArchivo && $request->hasFile($campoArchivo->name_id)) {
        $extension = $request[$campoArchivo->name_id]->extension();
        $nombreArchivoC = 'archivo-c' . $usuario->id . '.' . $extension;
        $nombreArchivoC = $formulario->label_archivo_c
          ? $formulario->label_archivo_c . $usuario->id . '.' . $extension
          : 'archivo-c' . $usuario->id . '.' . $extension;
        if ($configuracion->version == 1) {
          // elimino el archivo actual
          Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/archivos' . '/' . $usuario->archivo_c);

          $request[$campoArchivo->name_id]->storeAs(
            $configuracion->ruta_almacenamiento . '/archivos' . '/',
            $nombreArchivoC,
            'public'
          );
        } elseif ($configuracion->version == 2) {
          /*
              $s3 = AWS::get('s3');
              $s3->putObject(array(
              'Bucket'     => $_ENV['aws_bucket'],
              'Key'        => $_ENV['aws_carpeta']."/archivos"."/".$nombreArchivoC,
              'SourceFile' => "img/temp/archivo-a-temp-".$asistente->id.".".$extension,
              ));*/
        }
        $usuario->archivo_c = $nombreArchivoC;
        $usuario->save();
      }

      // archivo_d
      $campoArchivo = $campos->where('nombre_bd', 'archivo_d')->first();
      if ($campoArchivo && $request->hasFile($campoArchivo->name_id)) {
        $extension = $request[$campoArchivo->name_id]->extension();
        $nombreArchivoD = $formulario->label_archivo_d
          ? $formulario->label_archivo_d . $usuario->id . '.' . $extension
          : 'archivo-d' . $usuario->id . '.' . $extension;
        if ($configuracion->version == 1) {
          // elimino el archivo actual
          Storage::delete('public/' . $configuracion->ruta_almacenamiento . '/archivos' . '/' . $usuario->archivo_d);

          $request[$campoArchivo->name_id]->storeAs(
            $configuracion->ruta_almacenamiento . '/archivos' . '/',
            $nombreArchivoD,
            'public'
          );
        } elseif ($configuracion->version == 2) {
          /*
              $s3 = AWS::get('s3');
              $s3->putObject(array(
              'Bucket'     => $_ENV['aws_bucket'],
              'Key'        => $_ENV['aws_carpeta']."/archivos"."/".$nombreArchivoD,
              'SourceFile' => "img/temp/archivo-a-temp-".$asistente->id.".".$extension,
              ));*/
        }
        $usuario->archivo_d = $nombreArchivoD;
        $usuario->save();
      }
      //fin documentos adjuntos

      // ubicacion localida o barrio
      $campoUbicacion = $campos->where('nombre_bd', 'ubicacion')->first();
      if ($campoUbicacion && $request[$campoUbicacion->name_id]) {


        $usuario->localidad_id = null;
        $usuario->barrio_id = null;

        if ($request[$campoUbicacion->name_id]) {
          if ($request->tipoUbicacion == 'Localidad') {
            $usuario->localidad_id = $request[$campoUbicacion->name_id];
          } elseif ($request->tipoUbicacion == 'Barrio') {
            $barrio = Barrio::find($request[$campoUbicacion->name_id]);

            if ($barrio) {
              $usuario->barrio_id = $barrio->id;

              if ($barrio->localidad) {
                $usuario->localidad_id = $barrio->localidad->id;
              }
            }
          }
        }
      }

      $usuario->save();

      $nombre_completo = $usuario->nombre(3);
      if ($formulario->redirect != '') {
        if ($formulario->redirect == '/') {
          return Redirect::to('/inicio')->with([
            'status' => 'ok_update_asistente',
            'mensaje' => 'La persona <b>' . $nombre_completo . '</b> fue actualizada con éxito.',
          ]);
        } elseif ($formulario->redirect == 'peticiones-online') {
          return Redirect::to('/peticiones/formulario-peticiones/' . $usuario->id . '/asistente')->with([
            'status' => 'ok_update_asistente',
            'mensaje' => 'La persona <b>' . $nombre_completo . '</b> fue actualizada con éxito.',
          ]);
        } elseif ($formulario->redirect == 'donaciones-online') {
          return Redirect::to('/ofrendas/formulario-donaciones/' . $usuario->id . '/asistente')->with([
            'status' => 'ok_update_asistente',
            'mensaje' => 'La persona <b>' . $nombre_completo . '</b> fue actualizada con éxito.',
          ]);
        } elseif ($formulario->redirect == 'actividades') {
          $actividad_id = $request->aux;
          return Redirect::to(
            '/actividades/perfil/' . $actividad_id . '/' . 'website/' . $usuario->id . '/asistente'
          )->with([
            'status' => 'ok_update_asistente',
            'mensaje' => 'La persona <b>' . $nombre_completo . '</b> fue actualizada con éxito.',
          ]);
        } elseif ($formulario->redirect == 'listado') {
          return Redirect::to('/asistentes/lista')->with([
            'status' => 'ok_update_asistente',
            'mensaje' => 'La persona <b>' . $nombre_completo . '</b> fue actualizada con éxito.',
          ]);
        } else {
          return Redirect::to($formulario->redirect . '' . $usuario->id)->with([
            'status' => 'ok_update_asistente',
            'mensaje' => 'La persona <b>' . $nombre_completo . '</b> fue actualizada con éxito.',
          ]);
        }
      } else {
        return back()->with('success', "La persona <b>$nombre_completo</b> fue actualizada con éxito.");
      }
    }
  }

  public function cambiarFoto(Request $request, User $usuario)
  {
    $configuracion = Configuracion::find(1);

    if ($request->foto) {
      if ($configuracion->version == 1) {
        $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/');
        !is_dir($path) && mkdir($path, 0777, true);

        $imagenPartes = explode(';base64,', $request->foto);
        $imagenBase64 = base64_decode($imagenPartes[1]);
        $nombreFoto = 'asistente-' . $usuario->id . '.jpg';
        $imagenPath = $path . $nombreFoto;
        file_put_contents($imagenPath, $imagenBase64);
        $usuario->foto = $nombreFoto;
      } else {
        /*
          $s3 = AWS::get('s3');
          $s3->putObject(array(
            'Bucket'     => $_ENV['aws_bucket'],
            'Key'        => $_ENV['aws_carpeta']."/fotos/asistente-".$asistente->id.".jpg",
            'SourceFile' => "img/temp/".Input::get('foto-hide'),
          ));*/
      }
      $usuario->save();
    }
    return back()->with('success', "La foto de perfil de <b>" . $usuario->nombre(3) . "</b> fue actualizada con éxito.");
  }

  public function cambiarPortada(Request $request, User $usuario)
  {
    $configuracion = Configuracion::find(1);
    if ($request->foto) {
      if ($configuracion->version == 1) {
        $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img//usuarios/banner-usuario/');
        !is_dir($path) && mkdir($path, 0777, true);

        $imagenPartes = explode(';base64,', $request->foto);
        $imagenBase64 = base64_decode($imagenPartes[1]);
        $nombreFoto = 'banner-' . $usuario->id . '.jpg';
        $imagenPath = $path . $nombreFoto;
        file_put_contents($imagenPath, $imagenBase64);
        $usuario->portada = $nombreFoto;
      } else {
        /*
          $s3 = AWS::get('s3');
          $s3->putObject(array(
            'Bucket'     => $_ENV['aws_bucket'],
            'Key'        => $_ENV['aws_carpeta']."/fotos/asistente-".$asistente->id.".jpg",
            'SourceFile' => "img/temp/".Input::get('foto-hide'),
          ));*/
      }
      $usuario->save();
    }

    return back()->with('success', "La foto de perfil de <b>" . $usuario->nombre(3) . "</b> fue actualizada con éxito.");
  }

  public function informacionCongregacional(?int $formulario = 0, User $usuario, int $tipoUsuarioSugeridoId = 0)
  {
    // Esta línea ejecuta la policy. Si devuelve false, se lanza un error 403 automáticamente.
    $this->authorize('informacionCongregacionalPolitica', $usuario);

    $tipoUsuarioSugerido = null;
    if ($tipoUsuarioSugeridoId) {
      $tipoUsuarioSugerido = TipoUsuario::find($tipoUsuarioSugeridoId);
    }

    $configuracion = Configuracion::find(1);
    if (!isset($usuario)) {
      return Redirect::to('pagina-no-encontrada');
    }

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    $edad = $usuario->edad();

    if ($formulario == 0) {
      $formulario = $rolActivo
        ->formularios()
        ->where('tipo_formulario_id', '=', 2)
        ->where('edad_minima', '<=', $edad)
        ->where('edad_maxima', '>=', $edad)
        ->first();
    } else {
      $formulario = FormularioUsuario::find($formulario);
    }

    $idsGruposPadres = $usuario->gruposDondeAsiste->pluck('id')->toArray();

    $cantidadTiposTipoUsuariosBloqueados = $rolActivo->tipoUsuariosBloqueados()->count();
    if ($cantidadTiposTipoUsuariosBloqueados > 0) {
      $arrayTiposUsuariosBloqueados = $rolActivo
        ->tipoUsuariosBloqueados()
        ->select('tipo_usuarios.id')
        ->pluck('id')
        ->toArray();

      if (in_array($usuario->tipoUsuario->id, $arrayTiposUsuariosBloqueados)) {
        $tiposUsuarios = null;
      } else {
        $tiposUsuarios = TipoUsuario::whereNotIn('id', $arrayTiposUsuariosBloqueados)
          ->orderBy('orden', 'asc')
          ->where('visible', true)
          ->get();
      }
    } else {
      $tiposUsuarios = TipoUsuario::orderBy('orden', 'asc')
        ->where('visible', true)
        ->get();
    }

    if ($rolActivo->hasPermissionTo('personas.privilegio_gestionar_todos_los_pasos_de_crecimiento')) {
      $pasosDeCrecimiento = PasoCrecimiento::orderBy('updated_at', 'asc')
        ->select('id', 'nombre', 'seccion_paso_crecimiento_id')
        ->get();
    } else {
      $pasosDeCrecimiento = $rolActivo->pasosCrecimiento()->orderBy('updated_at', 'asc')->get();
    }


    $seccionesIds = $pasosDeCrecimiento->pluck('seccion_paso_crecimiento_id')->toArray();
    $seccionesPasoDeCrecimiento = SeccionPasoCrecimiento::whereIn('id', $seccionesIds)->orderBy('orden', 'asc')->get();

    $seccionesPasoDeCrecimiento->map(function ($seccion) use ($usuario, $pasosDeCrecimiento) {

      $pasosDeLaSeccion = $pasosDeCrecimiento->where('seccion_paso_crecimiento_id', $seccion->id);
      $pasosDeLaSeccion->map(function ($paso) use ($usuario) {
        $pasoUsuario = CrecimientoUsuario::where('user_id', $usuario->id)
          ->where('paso_crecimiento_id', $paso->id)
          ->first();
        $paso->clase_color = 'danger';
        $paso->estado_fecha = null;
        $paso->estado_paso = 1;
        $paso->estado_nombre = 'No realizado';
        $paso->detalle_paso = '';
        $paso->bandera = 'default';
        if ($pasoUsuario) {
          $paso->clase_color = $pasoUsuario->estado->color;
          $paso->estado_fecha = $pasoUsuario->fecha;
          $paso->estado_paso = $pasoUsuario->estado_id;
          $paso->estado_nombre = $pasoUsuario->estado->nombre;
          $paso->detalle_paso = $pasoUsuario->detalle;
          $paso->bandera = 'si existe';
        }
      });

      $seccion->pasos = $pasosDeLaSeccion;
    });

    $rolesNoDependientes = Role::where('dependiente', 'FALSE')
      ->orderBy('name', 'asc')
      ->select('id', 'name')
      ->get();
    $rolesNoDependientes->map(function ($rol) use ($usuario) {
      $rolUsuario = $usuario
        ->roles()
        ->where('roles.id', $rol->id)
        ->first();
      $rol->tiene = $rolUsuario ? 'si' : 'no';
    });

    $estados = EstadoPasoCrecimientoUsuario::get();
    $gruposDondeAsisteIds = $usuario->gruposDondeAsiste->pluck('id')->toArray();

    //return $tipoUsuarioSugerido;

    return view('contenido.paginas.usuario.informacion-congregacional', [
      'formulario' => $formulario,
      'usuario' => $usuario,
      'idsGruposPadres' => $idsGruposPadres,
      'tiposUsuarios' => $tiposUsuarios,
      'configuracion' => $configuracion,
      'rolesNoDependientes' => $rolesNoDependientes,
      'rolActivo' => $rolActivo,
      //  'pasosDeCrecimiento' => $pasosDeCrecimiento,
      'estados' => $estados,
      'gruposDondeAsisteIds' => $gruposDondeAsisteIds,
      'tipoUsuarioSugerido' => $tipoUsuarioSugerido,
      'seccionesPasoDeCrecimiento' => $seccionesPasoDeCrecimiento
    ]);
  }

  public function actualizarInformacionCongregacional(Request $request, User $usuario)
  {

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $automatizacionTipoUsuarios = [];
    $automatizacionPasosCrecimiento = collect();
    $sugerencia = false;

    if (!isset($usuario)) {
      return Redirect::to('pagina-no-encontrada');
    }

    // Asigno los grupos
    if ($rolActivo->hasPermissionTo('personas.panel_asignar_grupo_al_asistente')) {

      $idsGrupos = json_decode($request->inputGrupos);
      $grupos = Grupo::leftJoin('tipo_grupos', 'grupos.tipo_grupo_id', '=', 'tipo_grupos.id')
        ->whereIn('grupos.id', $idsGrupos)
        ->select('grupos.id', 'grupos.nombre', 'grupos.tipo_grupo_id', 'tipo_grupos.automatizacion_tipo_usuario_id', 'tipo_grupos.nombre as nameTipo')
        ->get();

      //Validar si privilegio_asignar_asistente_todo_tipo_asistente_a_un_grupo
      if (!$rolActivo->hasPermissionTo('grupos.privilegio_asignar_asistente_todo_tipo_asistente_a_un_grupo')) {
        foreach ($grupos as $grupo) {
          $tipoGrupo = $grupo->tipoGrupo;
          $usuarioPermintidos = $tipoGrupo->tipoUsuariosPermitidos()
            ->wherePivot('para_asistentes', '=', TRUE)
            ->where('tipo_usuario_id', '=', $request->tipo_usuario)
            ->count();

          if ($usuarioPermintidos <= 0) {
            return back()->with('danger', 'No es posible asignar un <b>' . $usuario->tipoUsuario->nombre . '</b> a un grupo tipo <b>' . $grupo->tipoGrupo->nombre . '</b>. Por favor, consulte a su administrador.');
          }
        }
      }

      $gruposActualesIds = $usuario->gruposDondeAsiste()->select('grupos.id')->pluck('grupos.id')->toArray();
      $gruposNuevosIds = array_diff($idsGrupos, $gruposActualesIds);
      $gruposNuevos = $grupos->whereIn('id', $gruposNuevosIds);

      $automatizacionPasosCrecimiento = TipoGrupo::whereIn('tipo_grupos.id', $gruposNuevos->pluck('tipo_grupo_id')->toArray())
        ->leftJoin('automatizaciones_tipo_grupo_paso_crecimiento', 'tipo_grupos.id', '=', 'automatizaciones_tipo_grupo_paso_crecimiento.tipo_grupo_id')
        ->leftJoin('estados_pasos_crecimiento_usuario', 'automatizaciones_tipo_grupo_paso_crecimiento.estado_por_defecto', '=', 'estados_pasos_crecimiento_usuario.id')

        ->select('tipo_grupos.id', 'paso_crecimiento_id', 'estado_por_defecto', 'descripcion_por_defecto', 'puntaje')
        ->get();

      $automatizacionTipoUsuarios += $gruposNuevos->whereNotNull('automatizacion_tipo_usuario_id')->pluck('automatizacion_tipo_usuario_id')->toArray();
      $usuario->gruposDondeAsiste()->sync($idsGrupos);

      //asigno la sede al usuario del ultimo grupo agregado
      if ($idsGrupos && count($idsGrupos) > 0)
        $usuario->asignarSede(end($idsGrupos));

      if ($request->bitacora) {
        foreach (json_decode($request->bitacora) as $informe) {

          // (1) "Asignación de líder" (2) "Asignación de asistente" (3) "Desvinculacion de líder" (4) "Desvinculacion del asistente"
          $tipoInforme = $informe->bitacora == 'desvinculacion' ? 4 : 2;

          InformeGrupo::create([
            'user_id' => $usuario->id,
            'grupo_id' => $informe->grupoId,
            'observaciones' => $informe->observacion,
            'tipo_asignacion_id' => $informe->motivoId,
            'tipo_informe' => $tipoInforme,
            'user_autor_asignacion' => auth()->user()->id
          ]);

          if ($informe->bitacora == 'desvinculacion' && $informe->desvincularServicios == 'si') {
            //Desvinculo los servicios
            $servidores = ServidorGrupo::where('user_id', $usuario->id)->where('grupo_id', $informe->grupoId)->get();
            foreach ($servidores as $servidor) {
              $servidor->tipoServicioGrupo()->detach();
              $servidor->delete();
            }
          }
        }
      }
    }

    // asigna los procesos de crecimiento
    if ($rolActivo->hasPermissionTo('personas.panel_procesos_asistente') && $rolActivo->hasPermissionTo('personas.editar_procesos_asistente')) {
      $pasosCrecimiento = PasoCrecimiento::all();

      foreach ($pasosCrecimiento as $paso) {
        // Busco si tiene pasos de crecimiento
        $pasoActual = $usuario
          ->pasosCrecimiento()
          ->where('pasos_crecimiento.id', $paso->id)
          ->first();

        if ($pasoActual) {
          if ($pasoActual->pivot->estado_id != $request['estado_paso_' . $paso->id] && $request['estado_paso_' . $paso->id]) {
            $pasoActual->pivot->estado_id = $request['estado_paso_' . $paso->id];
          }

          if ($request['estado_paso_' . $paso->id] != 1) {
            $pasoActual->pivot->fecha = $request['fecha_paso_' . $paso->id];
          } else {
            $pasoActual->pivot->fecha = null;
          }
          $pasoActual->pivot->detalle = $request['detalle_paso_' . $paso->id] ? $request['detalle_paso_' . $paso->id] : '';
          $pasoActual->pivot->save();
        } else {
          // Si el usuario no tiene el paso, lo creo
          if ($request['estado_paso_' . $paso->id] == 1 || !$request['estado_paso_' . $paso->id]) {

            $usuario->pasosCrecimiento()->attach($paso->id, [
              'estado_id' => $request['estado_paso_' . $paso->id] ? $request['estado_paso_' . $paso->id] : 1,
              'detalle' => $request['detalle_paso_' . $paso->id] ? $request['detalle_paso_' . $paso->id] : '',
            ]);
          } else {
            $usuario->pasosCrecimiento()->attach($paso->id, [
              'estado_id' => $request['estado_paso_' . $paso->id],
              'fecha' => $request['fecha_paso_' . $paso->id],
              'detalle' => $request['detalle_paso_' . $paso->id],
            ]);
          }
        }
      }
    }

    // asignar los roles dependientes == false, es decir los independientes
    if ($rolActivo->hasPermissionTo('personas.ver_panel_asignar_tipo_usuario')) {
      $rolesNoDependientes = Role::orderBy('id', 'asc')
        ->where('dependiente', '=', 'FALSE')
        ->get();

      foreach ($rolesNoDependientes as $rol) {
        $registros = $usuario
          ->roles()
          ->where('id', $rol->id)
          ->get();

        if ($registros->count() > 0) {
          if (!$request->get('rolIndependiente' . $rol->id)) {
            $usuario->removeRole($rol);
          }
        } else {
          if ($request->get('rolIndependiente' . $rol->id)) {
            $usuario->roles()->attach($rol->id, ['dependiente' => 'false', 'activo' => 'false', 'model_type' => 'App\Models\User']);
          }
        }
      }

      $cantidadActivos = $usuario
        ->roles()
        ->wherePivot('activo', '=', true)
        ->count();
      if ($cantidadActivos < 1) {
        // Con esto nos aseguramosd que tenga minimo un rol dependiente activo
        $rolDependiente = $usuario
          ->roles()
          ->wherePivot('dependiente', '=', true)
          ->first();

        if ($rolDependiente) {
          $rolDependiente->pivot->activo = true;
          $rolDependiente->pivot->dependiente = true;
          $rolDependiente->pivot->save();
        } else {
          $rol = $usuario->roles()->first();
          if ($rol) {
            $rol->pivot->activo = true;
            $rol->pivot->save();
          }
        }
      }
    }

    // Asigno tipo usuario
    if ($rolActivo->hasPermissionTo('personas.panel_tipos_asistente') && $rolActivo->hasPermissionTo('personas.editar_tipos_asistente')) {
      if ($request->tipo_usuario) {
        $usuario->tipo_usuario_id = $request->tipo_usuario;
        $usuario->save();
      }
    }

    $tipoUsuarioAutomatico = TipoUsuario::whereIn('id', $automatizacionTipoUsuarios)->orderBy('puntaje', 'DESC')->first();

    // Automatizacion de tipoUsuario
    $tipoUsuarioActual = TipoUsuario::find($usuario->tipo_usuario_id);
    if ($tipoUsuarioAutomatico && $tipoUsuarioAutomatico->puntaje > $tipoUsuarioActual->puntaje) {
      $usuario->tipo_usuario_id = $tipoUsuarioAutomatico->id;
      $usuario->save();
    }

    //Además, le cambio a la usuario el rol dependiente
    $rolDependiente = $usuario
      ->roles()
      ->wherePivot('dependiente', '=', true)
      ->first();

    if ($rolDependiente && $tipoUsuarioActual->id_rol_dependiente != $rolDependiente->id) {
      $usuario->roles()->attach($tipoUsuarioActual->id_rol_dependiente, ['activo' => $rolDependiente->pivot->activo, 'dependiente' => true, 'model_type' => 'App\Models\User']);
      $usuario->removeRole($rolDependiente);
    }
    $usuario->save();

    // Automatizacion de pasos de crecimiento
    if ($automatizacionPasosCrecimiento->count() > 0) {
      $pasosCrecimiento = CrecimientoUsuario::where('user_id', $usuario->id)
        ->leftJoin('estados_pasos_crecimiento_usuario', 'crecimiento_usuario.estado_id', '=', 'estados_pasos_crecimiento_usuario.id')
        ->select('crecimiento_usuario.*', 'puntaje')
        ->get();

      foreach ($pasosCrecimiento as $paso) {
        // obtengo el paso de crecimiento automatizado con mas puntaje
        $pasoAutomatico = $automatizacionPasosCrecimiento->where('paso_crecimiento_id', $paso->paso_crecimiento_id)
          ->sortByDesc('puntaje')
          ->first();

        if ($pasoAutomatico && $pasoAutomatico->puntaje > $paso->puntaje) {
          // guardo la automatizacion
          $pasoParaAutomatizar = $usuario
            ->pasosCrecimiento()
            ->where('pasos_crecimiento.id', $pasoAutomatico->paso_crecimiento_id)
            ->first();

          $pasoParaAutomatizar->pivot->estado_id = $pasoAutomatico->estado_por_defecto;
          $pasoParaAutomatizar->pivot->fecha = Carbon::now()->format('Y-m-d');
          $pasoParaAutomatizar->pivot->detalle = $pasoAutomatico->descripcion_por_defecto;
          $pasoParaAutomatizar->pivot->save();
        }
      }
    }

    return back()->with('success', 'La información congregacional <b>' . $usuario->nombre(3) . '</b> se actualizo con éxito.');
  }

  public function geoAsignacion(?int $formulario = 0, User $usuario)
  {
    // Esta línea ejecuta la policy. Si devuelve false, se lanza un error 403 automáticamente.
    $this->authorize('geoasignacionUsuarioPolitica', $usuario);

    $iglesia = Iglesia::find(1);
    if ($iglesia->latitud && $iglesia->longitud) {
      $longitudInicial = $iglesia->longitud;
      $latitudInicial = $iglesia->latitud;
    } else {
      $busqueda = '';
      $busqueda .= $iglesia->municipio ? $iglesia->municipio->nombre . ' ' : '';
      $busqueda .= $iglesia->pais ? $iglesia->pais->nombre . ' ' : '';

      if ($busqueda == '') {
        $busqueda .= $usuario->pais ? $usuario->pais->nombre . '' : '';
      }
      $ubicacionInicial = Http::get("https://nominatim.openstreetmap.org/search?q=$busqueda$&format=json");
      $ubicacionInicial = collect(json_decode($ubicacionInicial))->first();
      $longitudInicial = ($ubicacionInicial && $ubicacionInicial->lon) ? $ubicacionInicial->lon : -72.9088133;
      $latitudInicial = ($ubicacionInicial && $ubicacionInicial->lat) ? $ubicacionInicial->lat : 4.099917;

      if ($iglesia->latitud && $iglesia->longitud) {
        $iglesia->latitud = $longitudInicial;
        $iglesia->longitud = $longitudInicial;
        $iglesia->save();
      }
    }

    $configuracion = Configuracion::find(1);
    if (!isset($usuario)) {
      return Redirect::to('pagina-no-encontrada');
    }

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $edad = $usuario->edad();

    if ($formulario == 0) {
      $formulario = $rolActivo
        ->formularios()
        ->where('tipo_formulario_id', '=', 2)
        ->where('edad_minima', '<=', $edad)
        ->where('edad_maxima', '>=', $edad)
        ->first();
    } else {
      $formulario = FormularioUsuario::find($formulario);
    }

    if ($rolActivo->hasPermissionTo('personas.mostrar_todos_los_grupos_en_geoasignacion')) {
      $grupos = Grupo::with("tipoGrupo")->select("id", "nombre", "latitud", "longitud", "direccion", "tipo_grupo_id")->get();
    } else {
      $grupos = $usuario->gruposMinisterio()->select("id", "nombre", "latitud", "longitud", "direccion", "tipo_grupo_id")->get();
    }

    return view('contenido.paginas.usuario.geo-asignacion', [
      'rolActivo' => $rolActivo,
      'usuario' => $usuario,
      'formulario' => $formulario,
      'configuracion' => $configuracion,
      //'ubicacionInicial' => $ubicacionInicial,
      'longitudInicial' => $longitudInicial,
      'latitudInicial' => $latitudInicial,
      'grupos' => $grupos
    ]);
  }

  public function relacionesFamiliares(?int $formulario = 0, User $usuario)
  {

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    // Esta línea ejecuta la policy. Si devuelve false, se lanza un error 403 automáticamente.
    $this->authorize('relacionesFamiliaresUsuarioPolitica', $usuario);

    $configuracion = Configuracion::find(1);
    $userId = $usuario->id;
    $parientes = array();
    $tiposParentesco = TipoParentesco::get();
    $html = '';
    $edad = $usuario->edad();

    if ($formulario == 0) {
      $formulario = $rolActivo
        ->formularios()
        ->where('tipo_formulario_id', '=', 2)
        ->where('edad_minima', '<=', $edad)
        ->where('edad_maxima', '>=', $edad)
        ->first();
    } else {
      $formulario = FormularioUsuario::find($formulario);
    }

    if (isset($usuario->id)) {

      $parientes = $usuario
        ->parientesDelUsuario()
        ->leftJoin('tipos_parentesco', 'parientes_usuarios.tipo_pariente_id', '=', 'tipos_parentesco.id')
        ->select(
          'users.id',
          'users.foto',
          'users.identificacion',
          'users.primer_nombre',
          'users.segundo_nombre',
          'users.primer_apellido',
          'users.segundo_apellido',
          'users.tipo_identificacion_id',
          'tipos_parentesco.nombre as nombre_parentesco',
          'tipos_parentesco.nombre_masculino',
          'tipos_parentesco.nombre_femenino',
          'parientes_usuarios.es_el_responsable',
          'parientes_usuarios.id'
        )
        ->get();
    }

    return view('contenido.paginas.usuario.relaciones-familiares', [

      'configuracion' => $configuracion,
      'userId' => $userId,
      'parientes' => $parientes,
      'usuario' => $usuario,
      'tiposParentesco' => $tiposParentesco,
      'formulario' => $formulario,
      'rolActivo' => $rolActivo
    ]);
  }

  public function eliminarRelacionFamiliar(ParienteUsuario $pariente)
  {

    $usuarioSeleccioanado = $pariente->user_id;
    $relacion1 = ParienteUsuario::where('user_id', $pariente->user_id)->where('pariente_user_id', $pariente->pariente_user_id)->first();

    $relacion2 = ParienteUsuario::where('pariente_user_id', $pariente->user_id)->where('user_id', $pariente->pariente_user_id)->first();

    if (isset($relacion1))
      $relacion1->delete();

    if (isset($relacion2))
      $relacion2->delete();


    return back()->with('success', " La relación fue eliminada con éxito.");
  }

  public function cambiarContrasenaDefault(User $usuario)
  {
    $configuracion = Configuracion::find(1); //dentificacion_obligatoria
    $nuevaContrasena = $configuracion->identificacion_obligatoria ? $usuario->identificacion : "123456";

    $usuario->password = Hash::make($nuevaContrasena);
    $usuario->save();

    $mailData = new stdClass();
    $mailData->subject = 'Cambio de contraseña';
    $mailData->nombre = $usuario->nombre(3);
    $mailData->mensaje = 'Su contraseña ha sido cambiada satisfactoriamente por parte del administrador, su nueva contraseña es:
      <br> <center><p class="centrar-text" style="font:18px/1.25em ' . 'Century Gothic' . ',Arial,Helvetica;color:#939393"><b>Nueva clave: ' . $nuevaContrasena . '</b></p></center>    ';

    try {
      Mail::to($usuario->email)->send(new DefaultMail($mailData));
    } catch (Exception $e) {
    }

    return back()->with('success', "La contraseña de <b>" . $usuario->nombre(3) . "</b> fue cambiada con éxito a <b>" . $nuevaContrasena . "</b>.");
  }

  public function cambiarContrasena(Request $request, User $usuario)
  {
    //Este cambio de contraseña es que usa el admin para cambiar a los demas usuarios
    $request->validate([
      'password' => 'required|confirmed|min:5',
    ]);

    $usuario->password = Hash::make($request->password);
    $usuario->save();

    $mailData = new stdClass();
    $mailData->subject = 'Cambio de contraseña';
    $mailData->nombre = $usuario->nombre(3);
    $mailData->mensaje = 'Su contraseña ha sido cambiada satisfactoriamente por parte del administrador, su nueva contraseña es:
      <br> <center><p class="centrar-text" style="font:18px/1.25em ' . 'Century Gothic' . ',Arial,Helvetica;color:#939393"><b>Nueva clave: ' . $request->password . '</b></p></center>    ';

    try {
      Mail::to($usuario->email)->send(new DefaultMail($mailData));
    } catch (Exception $e) {
    }

    return back()->with('success', "La contraseña de <b>" . $usuario->nombre(3) . "</b> fue cambiada con éxito.");
  }

  public function validarPaso(Request $request)
  {

    return;
  }

  public function noVolverMostrarModalAgregarHijos(Request $request)
  {
    // 1. Obtiene el usuario que ha iniciado sesión.
    $user = auth()->user();

    // 2. Comprueba si el usuario existe (es una buena práctica).
    if ($user) {
      // 3. Actualiza el campo a 'true' para que no se vuelva a mostrar.
      $user->mostrar_modal_agregar_hijos = false;

      // 4. Guarda el cambio en la base de datos.
      $user->save();

      // 5. Devuelve una respuesta JSON para confirmar que todo salió bien.
      return response()->json(['status' => 'success']);
    }

    // Si por alguna razón no hay un usuario, devuelve un error.
    return response()->json(['status' => 'error', 'message' => 'Usuario no autenticado.'], 401);
  }
}
