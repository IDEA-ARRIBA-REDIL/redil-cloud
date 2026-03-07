<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Sede;
use App\Models\TipoUsuario;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\Helpers;
use App\Models\EstadoCivil;
use App\Models\EstadoNivelAcademico;
use App\Models\EstadoPasoCrecimientoUsuario;
use App\Models\EstadoTareaConsolidacion;
use App\Models\FiltroConsolidacion;
use App\Models\HistorialTareaConsolidacionUsuario;
use App\Models\NivelAcademico;
use App\Models\Ocupacion;
use App\Models\PasoCrecimiento;
use App\Models\Profesion;
use App\Models\RangoEdad;
use App\Models\TareaConsolidacion;
use App\Models\TipoVinculacion;
use App\Models\BitacoraTipoUsuario;
use App\Models\BloqueDashboardConsolidacion;
use Carbon\Carbon;
use App\Models\BitacoraSede;
use App\Models\Escuela;
use App\Models\Matricula;
use App\Models\BitacoraEstadoCivil;

use Illuminate\Support\Facades\DB;

use \stdClass;

class ConsolidacionController extends Controller
{
  public function bloques()
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    // Por ahora usamos el mismo permiso que el dashboard o uno específico si existe. 
    // Usaremos el de dashboard consolidación por el momento.
    //$rolActivo->verificacionDelPermiso('consolidacion.subitem_dashboard_consolidacion');

    return view('contenido.paginas.consolidacion.bloques');
  }

  public function listar(Request $request, $tipo = 'todos')
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('consolidacion.subitem_lista_consolidacion');

    $tiposUsuarios = TipoUsuario::orderBy('orden', 'asc')
      ->where('visible', true)
      ->where('tipo_pastor_principal', '!=', true)
      ->get();

    $rangosEdad = RangoEdad::all();
    $estadosCiviles = EstadoCivil::all();
    $tiposVinculaciones = TipoVinculacion::withTrashed()->get();
    $pasosCrecimiento = PasoCrecimiento::orderBy('updated_at', 'asc')->get();
    $estadosPasosDeCrecimiento = EstadoPasoCrecimientoUsuario::orderBy('puntaje', 'asc')->get();
    $ocupaciones = Ocupacion::orderBy('nombre', 'asc')->get();
    $nivelesAcademicos = NivelAcademico::orderBy('nombre', 'asc')->get();
    $estadosNivelAcademico = EstadoNivelAcademico::orderBy('id', 'asc')->get();
    $profesiones = Profesion::orderBy('nombre', 'asc')->get();

    $configuracion = Configuracion::find(1);
    $meses = Helpers::meses('largo');


   /* $filtroFechaIni = $request->filtroFechaIni ? Carbon::parse($request->filtroFechaIni)->format('Y-m-d') : Carbon::now()->subDays(30)->format('Y-m-d');
    $filtroFechaFin = $request->filtroFechaFin ? Carbon::parse($request->filtroFechaFin)->format('Y-m-d') : Carbon::now()->format('Y-m-d');*/


    $parametrosBusqueda['buscar'] = $request->buscar ? $request->buscar : '';

    $parametrosBusqueda['filtroPorSexo'] = $request->filtroPorSexo;
    $parametrosBusqueda['filtroPorTipoDeUsuario'] = $request->filtroPorTipoDeUsuario;
    $parametrosBusqueda['filtroPorRangoEdad'] = $request->filtroPorRangoEdad;
    $parametrosBusqueda['filtroPorEstadosCiviles'] = $request->filtroPorEstadosCiviles;
    $parametrosBusqueda['filtroPorTiposVinculaciones'] = $request->filtroPorTiposVinculaciones;
    $parametrosBusqueda['filtroPorOcupacion'] = $request->filtroPorOcupacion;
    $parametrosBusqueda['filtroPorProfesion'] = $request->filtroPorProfesion;
    $parametrosBusqueda['filtroPorNivelAcademico'] = $request->filtroPorNivelAcademico;
    $parametrosBusqueda['filtroPorEstadoNivelAcademico'] = $request->filtroPorEstadoNivelAcademico;

    $parametrosBusqueda['textoBusqueda'] = '';
    $parametrosBusqueda['tagsBusqueda'] = [];
    $parametrosBusqueda['bandera'] = '';
    $parametrosBusqueda['tipo'] = $tipo;

    $parametrosBusqueda = (object) $parametrosBusqueda;

    $personas = collect();
    if ($rolActivo->hasPermissionTo('consolidacion.lista_toda_consolidacion') || $rolActivo->hasPermissionTo('consolidacion.lista_consolidacion_solo_ministerio')) {
      if ($rolActivo->hasPermissionTo('consolidacion.lista_consolidacion_solo_ministerio')) {
        $personas = auth()->user()->consolidacion();
      }

      if ($rolActivo->hasPermissionTo('consolidacion.lista_toda_consolidacion')) {

          $tipoUsuariosHabilitados = TipoUsuario::where('habilitado_para_consolidacion', true)
          ->pluck('id')
          ->unique('id')
          ->toArray();

          $personas = User::withTrashed()
          ->whereIn('tipo_usuario_id', $tipoUsuariosHabilitados)
          ->get()
          ->unique('id');
      }

    }

    //  Empezamos con un Constructor de Consultas (Query Builder) en lugar de una colección vacía.
    $personasQuery = User::query();

    //  Aplicamos la lógica de permisos directamente a la consulta.
    if ($rolActivo->hasPermissionTo('consolidacion.lista_toda_consolidacion')) {

        $tipoUsuariosHabilitados = TipoUsuario::where('habilitado_para_consolidacion', true)->pluck('id');
        $personasQuery->withTrashed()->whereIn('tipo_usuario_id', $tipoUsuariosHabilitados);

    } elseif ($rolActivo->hasPermissionTo('consolidacion.lista_consolidacion_solo_ministerio')) {

        // Asumiendo que auth()->user()->consolidacion() devuelve una relación o una colección de usuarios,
        // obtenemos sus IDs para filtrar la consulta principal.
        $idsPersonasDelMinisterio = auth()->user()->consolidacion()->pluck('id');
        $personasQuery->whereIn('id', $idsPersonasDelMinisterio);
    } else {
        // Si no tiene ninguno de los permisos, forzamos a que no devuelva resultados.
        $personasQuery->whereRaw('1=2');
    }

    if (isset($configuracion->edad_minima_consolidacion) && is_numeric($configuracion->edad_minima_consolidacion)) {

        $edadMinima = (int) $configuracion->edad_minima_consolidacion;

        // Solo aplicamos el filtro si la edad mínima es mayor que 0
        if ($edadMinima > 0) {
            // Usamos whereRaw para aplicar la función de edad de PostgreSQL
            // y pasamos el valor como un "binding" (?) para seguridad.
            $personasQuery->whereRaw('EXTRACT(YEAR FROM AGE(fecha_nacimiento)) >= ?', [$edadMinima]);
        }
    }

    // Calculamos los indicadores ANTES de aplicar los filtros de tipo ('todos', 'sin-tareas')
    // Clonamos la consulta para no afectarla.
    $indicadoresQuery = clone $personasQuery;
    $indicadoresGenerales = [];

    $item = new stdClass();
    $item->nombre = 'Todas';
    $item->url = 'todos';
    $item->cantidad = (clone $indicadoresQuery)->count(); // Usamos clone para no alterar la consulta
    $item->color = '#fff';
    $item->icono = 'ti ti-asterisk';
    $indicadoresGenerales[] = $item;

    $item = new stdClass();
    $item->nombre = 'Sin tareas';
    $item->url = 'sin-tareas';
    $item->cantidad = (clone $indicadoresQuery)->doesntHave('tareasConsolidacion')->count();
    $item->color = '#fff';
    $item->icono = 'ti ti-user-off';
    $indicadoresGenerales[] = $item;

    $filtrosDinamicos = FiltroConsolidacion::with('condiciones')->orderBy('orden')->get();

    foreach ($filtrosDinamicos as $filtro) {
        $queryParaContar = clone $indicadoresQuery;

        $estadosCivilesFiltro = $filtro->estadosCiviles()->pluck('estados_civiles.id')->toArray();

        // Aquí filtro por los estados civiles del filtro
        if($estadosCivilesFiltro)
        {
          $queryParaContar->whereIn('estado_civil_id', $estadosCivilesFiltro);
        }

        foreach ($filtro->condiciones as $condicion) {

            // --- INICIO DE LA LÓGICA IF/ELSE ---
            if ($condicion->pivot->incluir) {
                // Si es INCLUIR, usamos whereHas
                $queryParaContar->whereHas('tareasConsolidacion', function ($subQuery) use ($condicion) {
                    $subQuery->where('tareas_consolidacion.id', $condicion->id)
                             ->where('tarea_consolidacion_usuario.estado_tarea_consolidacion_id', $condicion->pivot->estado_tarea_consolidacion_id);
                });
            } else {
                // Si es EXCLUIR, usamos whereDoesntHave
                $queryParaContar->whereDoesntHave('tareasConsolidacion', function ($subQuery) use ($condicion) {
                    $subQuery->where('tareas_consolidacion.id', $condicion->id)
                             ->where('tarea_consolidacion_usuario.estado_tarea_consolidacion_id', $condicion->pivot->estado_tarea_consolidacion_id);
                });
            }
            // --- FIN DE LA LÓGICA IF/ELSE ---
        }

        $item = new stdClass();
        $item->nombre = $filtro->nombre;
        $item->url = 'filtro-' . $filtro->id;
        $item->cantidad = $queryParaContar->count();
        $item->color = $filtro->color ?? '#fff';
        $item->icono = $filtro->icono ?? 'ti ti-filter';
        $indicadoresGenerales[] = $item;
    }

    //  APLICAMOS LOS FILTROS DE TIPO
    if ($tipo == 'sin-tareas') {
        $personasQuery->doesntHave('tareasConsolidacion');

      // ----> APLICACIÓN DE FILTROS DINÁMICOS (ACTUALIZADO) <----
    } elseif (str_starts_with($tipo, 'filtro-')) {
        $filtroId = substr($tipo, 7);
        $filtro = FiltroConsolidacion::with('condiciones')->find($filtroId);

        if ($filtro) {

            $estadosCivilesFiltro = $filtro->estadosCiviles()->pluck('estados_civiles.id')->toArray();

            // Aquí filtro por los estados civiles del filtro
            if($estadosCivilesFiltro)
            {
              $personasQuery->whereIn('estado_civil_id', $estadosCivilesFiltro);
            }

            foreach ($filtro->condiciones as $condicion) {

                // --- INICIO DE LA LÓGICA IF/ELSE ---
                if ($condicion->pivot->incluir) {
                    // Si es INCLUIR, usamos whereHas
                    $personasQuery->whereHas('tareasConsolidacion', function ($subQuery) use ($condicion) {
                        $subQuery->where('tareas_consolidacion.id', $condicion->id)
                                 ->where('tarea_consolidacion_usuario.estado_tarea_consolidacion_id', $condicion->pivot->estado_tarea_consolidacion_id);
                    });
                } else {
                    // Si es EXCLUIR, usamos whereDoesntHave
                    $personasQuery->whereDoesntHave('tareasConsolidacion', function ($subQuery) use ($condicion) {
                        $subQuery->where('tareas_consolidacion.id', $condicion->id)
                                 ->where('tarea_consolidacion_usuario.estado_tarea_consolidacion_id', $condicion->pivot->estado_tarea_consolidacion_id);
                    });
                }
                // --- FIN DE LA LÓGICA IF/ELSE ---
            }
        }
    }

    $personasQuery = $this->filtrosBusqueda($personasQuery, $parametrosBusqueda);

    // 5. Finalmente, ordenamos y paginamos
    $personas = $personasQuery->orderBy('id', 'desc')->paginate(12);
    $indicadoresGenerales = collect($indicadoresGenerales);



    // Obtenemos todas las tareas marcadas como 'default' para pasarlas a la vista.
    $tareasDefault = TareaConsolidacion::where('default', true)->orderBy('orden')->get();

    $estados = EstadoTareaConsolidacion::orderBy('puntaje', 'asc')->get();

    return view('contenido.paginas.consolidacion.listar', [
      'rolActivo' => $rolActivo,
      'personas' => $personas,
      'configuracion' => $configuracion,
      'tareasDefault' => $tareasDefault,
      //'filtroFechaIni' => $filtroFechaIni,
      //'filtroFechaFin' => $filtroFechaFin,
      'meses' => $meses,
      'estados' => $estados,
      'indicadoresGenerales' => $indicadoresGenerales,
      'parametrosBusqueda' => $parametrosBusqueda,
      'tipo' => $tipo,
      'tiposUsuarios' => $tiposUsuarios,
      'rangosEdad' => $rangosEdad,
      'estadosCiviles' => $estadosCiviles,
      'tiposVinculaciones' => $tiposVinculaciones,
      'pasosCrecimiento' => $pasosCrecimiento,
      'estadosPasosDeCrecimiento' => $estadosPasosDeCrecimiento,
      'ocupaciones' => $ocupaciones,
      'nivelesAcademicos' => $nivelesAcademicos,
      'estadosNivelAcademico' => $estadosNivelAcademico,
      'profesiones' => $profesiones
    ]);
  }

  public function filtrosBusqueda($personas, $parametrosBusqueda)
  {
    ///si el usuario ejecutó una busqueda se añaden las consultas necesarias
    if ($parametrosBusqueda->buscar != '') {
        $buscarSaneado = htmlspecialchars($parametrosBusqueda->buscar);
        $buscarSaneado = Helpers::sanearStringConEspacios($parametrosBusqueda->buscar);
        $buscar = str_replace(["'"], '', $parametrosBusqueda->buscar);

        $personas->where(function ($q) use ($buscarSaneado, $buscar) {
          $q->whereRaw("LOWER( translate( CONCAT_WS(' ', users.primer_nombre, users.segundo_nombre, users.primer_apellido, users.segundo_apellido ) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'] )
          ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', users.primer_nombre, users.primer_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', users.primer_nombre, users.segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', users.segundo_apellido, users.segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER(users.email) LIKE LOWER(?)", ['%'. $buscar . '%'])
          ->orWhereRaw("LOWER(users.identificacion) LIKE LOWER(?)", [ $buscar . '%']);
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

    //Filtro por sexo
    $personas = $this->filtrarSexo($personas, $parametrosBusqueda);

    //Filtro por tipo de usuario
    $personas = $this->filtroPorTipoUsuario($personas, $parametrosBusqueda);

    //Filtro por rango de edad
    $personas = $this->filtrarEdad($personas, $parametrosBusqueda);

    //Filtro por esatdos civiles
    $personas = $this->filtrarEstadoCivil($personas, $parametrosBusqueda);

    //Filtro por tipo vinculacion
    $personas = $this->filtrarTipoVinculacion($personas, $parametrosBusqueda);


    //Filtro por ocupacion
    $personas = $this->filtrarOcupacion($personas, $parametrosBusqueda);

    //Filtro por nivel academico
    $personas = $this->filtrarNivelAcademico($personas, $parametrosBusqueda);

    //Filtro por estado nivel academico
    $personas = $this->filtrarEstadoNivelAcademico($personas, $parametrosBusqueda);

    //Filtro por profesion
    $personas = $this->filtrarProfesion($personas, $parametrosBusqueda);


    return $personas;
  }

  public function filtroPorTipoUsuario($personas, $parametrosBusqueda)
  {
    if ($parametrosBusqueda->filtroPorTipoDeUsuario) {
      $personas = $personas->whereIn('tipo_usuario_id', $parametrosBusqueda->filtroPorTipoDeUsuario);

      $tiposUsuarios = TipoUsuario::select('id', 'nombre')
        ->whereIn('id', $parametrosBusqueda->filtroPorTipoDeUsuario)
        ->pluck('nombre')
        ->toArray();

      $parametrosBusqueda->textoBusqueda .= '<b>, Tipos de usuario: </b>"' . implode(', ', $tiposUsuarios) . '"';
      $parametrosBusqueda->bandera = 1;

      // Crear las tags para cada tipo de usuario

      $tiposUsuariosSeleccionados = TipoUsuario::whereIn('id', $parametrosBusqueda->filtroPorTipoDeUsuario)->get();
      foreach ($tiposUsuariosSeleccionados as $tipoUsuario) {
        $tag = new stdClass();
        $tag->label = $tipoUsuario->nombre;
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

      /*$personas = $personas->filter(function ($persona) use ($edadesPermitidas) {
        $edadPersona = Carbon::parse($persona->fecha_nacimiento)->age;
        return in_array($edadPersona, $edadesPermitidas);
      });*/

      $personas = $personas->where(function ($query) use ($rangos) {
          // Preparamos la expresión SQL para calcular la edad en PostgreSQL
          $sqlCalculoEdad = DB::raw('EXTRACT(YEAR FROM AGE(fecha_nacimiento))');

          // Por cada rango, añadimos una condición 'OR WHERE'
          //    Ej: (edad BETWEEN 18 AND 25) OR (edad BETWEEN 30 AND 40)
          foreach ($rangos as $rango) {
              $query->orWhereBetween($sqlCalculoEdad, [$rango->edad_minima, $rango->edad_maxima]);
          }
      });
    }

    return $personas;
  }

  public function filtrarSexo($personas, $parametrosBusqueda)
  {
    if (is_numeric($parametrosBusqueda->filtroPorSexo)) {
      $personas = $personas->where('genero', '=', $parametrosBusqueda->filtroPorSexo);

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
      $personas = $personas->whereIn('estado_civil_id', $parametrosBusqueda->filtroPorEstadosCiviles);

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
      $personas = $personas->whereIn('tipo_vinculacion_id', $parametrosBusqueda->filtroPorTiposVinculaciones);

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

  public function gestionarTareas(User $usuario)
  {
    //return HistorialTareaConsolidacionUsuario::orderBy('id', 'desc')->get();
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('consolidacion.gestionar_tareas');
    return view('contenido.paginas.consolidacion.gestionar-tareas', [
      'usuario' => $usuario
    ]);
  }


  public function dashboard(Request $request)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('consolidacion.dashboard_consolidacion');

    // Lógica para Rango de Fechas (Semanas)
    $rangoFechas = $request->rango_fechas;

    if ($rangoFechas) {
        $fechas = explode(' a ', $rangoFechas);
        if (count($fechas) >= 2) {
            $inicio = Carbon::parse(trim($fechas[0]))->startOfDay();
            $fin = Carbon::parse(trim($fechas[1]))->endOfDay();
        } else {
             $inicio = Carbon::parse(trim($fechas[0]))->startOfDay();
             $fin = Carbon::parse(trim($fechas[0]))->endOfDay();
        }
    } else {
        // Default: Este mes
        $inicio = Carbon::now()->startOfMonth();
        $fin = Carbon::now()->endOfMonth();
        $rangoFechas = $inicio->format('Y-m-d') . ' a ' . $fin->format('Y-m-d');
    }

    // --- LÓGICA DE FILTROS Y VISTAS ---
    
    // 1. Verificar si estamos en "Vista Detalle" (Drill Down)
    $bloqueDetalleId = $request->bloque_detalle_id ?? null;
    $esVistaDetalle = !empty($bloqueDetalleId);
    $bloqueActual = null;
    $sedesDisponibles = collect();
    $sedesSeleccionadas = [];
    
    // Switch de filtros
    $bloquesDisponibles = collect();
    $bloquesSeleccionados = [];

    // IDs finales sobre los cuales filtrar la data general
    $sedesIdsFiltrar = [];

    // DATA PARA LA VISTA
    $datosDesglose = []; // Ya sea por Bloque o por Sede
    $tipoDesglose = 'bloque'; // 'bloque' o 'sede'
    
    $esPeticionFiltro = $request->has('rango_fechas');

    // Caso 1: VISTA DETALLE (Viendo un bloque específico)
    if ($esVistaDetalle) {
        $bloqueActual = BloqueDashboardConsolidacion::with('sedes')->find($bloqueDetalleId);
        
        if ($bloqueActual) {
            $tipoDesglose = 'sede';
            $sedesDisponibles = $bloqueActual->sedes; // Sedes de ESTE bloque
            
            // Si el selector no está en el request, asumimos "seleccionar todo" (cambio de vista o primer ingreso).
            if ($request->has('sedes_seleccionadas')) {
                $sedesSeleccionadas = $request->sedes_seleccionadas;
            } else {
                $sedesSeleccionadas = $sedesDisponibles->pluck('id')->toArray();
            }

            // IDs filtrar son exactamente los seleccionados (que son validos para este bloque)
            // Filtramos $sedesSeleccionadas para asegurar que pertenezcan al bloque (seguridad)
            $sedesIdsFiltrar = $sedesDisponibles->whereIn('id', $sedesSeleccionadas)->pluck('id')->toArray();

        } else {
             // Si el bloque no existe, volver a vista general (fallback)
             $esVistaDetalle = false; 
        }
    }

    // Caso 2: VISTA GENERAL (Viendo todos los bloques)
    if (!$esVistaDetalle) {
        $bloquesDisponibles = BloqueDashboardConsolidacion::with('sedes')->get();

        if ($request->has('bloques_seleccionados')) {
             $bloquesSeleccionados = $request->bloques_seleccionados;
        } else {
             $bloquesSeleccionados = $bloquesDisponibles->pluck('id')->toArray();
        }

        if (!empty($bloquesSeleccionados)) {
            $bloquesFiltrados = $bloquesDisponibles->whereIn('id', $bloquesSeleccionados);
            foreach ($bloquesFiltrados as $bloque) {
                $sedesIdsFiltrar = array_merge($sedesIdsFiltrar, $bloque->sedes->pluck('id')->toArray());
            }
        }
    }
    
    $sedesIdsFiltrar = array_unique($sedesIdsFiltrar);

    // --- CALLBACK DE FILTRO GENERAL (Aplica para ambos casos) ---
    $filtroSedesCallback = function($query) use ($inicio, $fin, $sedesIdsFiltrar) {
        if (!empty($sedesIdsFiltrar)) {
            $query->whereHas('bitacorasSede', function ($subQuery) use ($inicio, $fin, $sedesIdsFiltrar) {
                $subQuery->whereBetween('created_at', [$inicio, $fin])
                    ->whereIn('sede_id_nuevo', $sedesIdsFiltrar)
                    ->whereRaw('id = (
                        SELECT MAX(bs.id) 
                        FROM bitacora_sedes as bs
                        WHERE bs.user_id = bitacora_sedes.user_id 
                        AND bs.created_at BETWEEN ? AND ?
                    )', [$inicio, $fin]);
            });
        } else {
             $query->whereRaw('1 = 0'); 
        }
    };

    // --- CÁLCULOS GLOBALES (Afectados por el filtro actual) ---
    $totalCosecha = User::withTrashed()
      ->whereBetween('created_at', [$inicio, $fin])
      ->where(function ($query) use ($inicio, $fin) {
        $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
          $subQuery->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->whereHas('tipoUsuarioNuevo', function ($q) { $q->where('habilitado_para_consolidacion', true); });
        });
      })
      ->tap($filtroSedesCallback)
      ->count();


    // Lógica para Cosecha Efectiva
    $cosechaEfectiva = User::withTrashed()
      ->whereBetween('created_at', [$inicio, $fin])
      ->where(function ($query) use ($inicio, $fin) {
        $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
          $subQuery->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->whereHas('tipoUsuarioNuevo', function ($q) { $q->where('habilitado_para_consolidacion', true); });
        });
      })
      ->where(function ($query) use ($inicio, $fin) {
        $query->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
          $sub->whereBetween('created_at', [$inicio, $fin]);
        })
          ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
            $sub->whereBetween('created_at', [$inicio, $fin])
              ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
              ->where('dado_baja', false);
          });
      })
      ->tap($filtroSedesCallback)
      ->count();

    $porcentajeEfectividad = $totalCosecha > 0 ? round(($cosechaEfectiva / $totalCosecha) * 100, 2) : 0;

    // Vinculaciones Globales
    $userIdsCosecha = User::withTrashed()
      ->whereBetween('created_at', [$inicio, $fin])
      ->where(function ($query) use ($inicio, $fin) {
        $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
          $subQuery->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->whereHas('tipoUsuarioNuevo', function ($q) { $q->where('habilitado_para_consolidacion', true); });
        });
      })
      ->tap($filtroSedesCallback)
      ->pluck('id');

    $vinculacionesCosecha = TipoVinculacion::withCount(['usuarios' => function ($query) use ($userIdsCosecha) {
      $query->whereIn('users.id', $userIdsCosecha);
    }])->get();

    // --- Helpers para Desglose y Métricas ---

    $limiteEdad = Configuracion::where('id', 1)->value('limite_menor_edad') ?? 18;

    $calcDistribucion = function($coleccion, $limite) {
        $adultos = 0;
        $menores = 0;
        foreach ($coleccion as $m) {
            if ($m->user && $m->user->fecha_nacimiento) {
                $fechaMatricula = Carbon::parse($m->fecha_matricula);
                $edad = $m->user->fecha_nacimiento->diffInYears($fechaMatricula);
                if ($edad < $limite) {
                    $menores++;
                } else {
                    $adultos++;
                }
            } else {
                $adultos++;
            }
        }
        return ['adultos' => $adultos, 'menores' => $menores];
    };

    // --- Lógica para Desglose (Bloques o Sedes) ---
    
    // Helper para calcular métricas de un conjunto de IDs de Sede
    $calcularMetricasIdsSedes = function($idsSedes) use ($inicio, $fin) {
        // Callback local
        $filtroLocal = function($query) use ($inicio, $fin, $idsSedes) {
             if (!empty($idsSedes)) {
                $query->whereHas('bitacorasSede', function ($subQuery) use ($inicio, $fin, $idsSedes) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                        ->whereIn('sede_id_nuevo', $idsSedes)
                        ->whereRaw('id = (SELECT MAX(bs.id) FROM bitacora_sedes as bs WHERE bs.user_id = bitacora_sedes.user_id AND bs.created_at BETWEEN ? AND ?)', [$inicio, $fin]);
                });
             } else { $query->whereRaw('1 = 0'); }
        };
        
        $total = User::withTrashed()->whereBetween('created_at', [$inicio, $fin])
           ->where(function ($q) use ($inicio, $fin) {
              $q->whereHas('bitacorasTipoUsuario', function ($sub) use ($inicio, $fin) {
                 $sub->whereBetween('created_at', [$inicio, $fin])->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?) AND tipo_usuario_id_nuevo IN (SELECT id FROM tipos_usuario WHERE habilitado_para_consolidacion = true)', [$inicio, $fin]);
              });
           })->tap($filtroLocal)->count();
           
        return $total;
    };
    
    // Por simplicidad, rehago el calculo completo iterativo para asegurar consistencia con el código anterior
    // pero adaptado al tipo de desglose
    
    $itemsAProcesar = []; // Lista de objetos (Bloques o Sedes)
    
    if ($tipoDesglose == 'bloque') {
         $itemsAProcesar = $bloquesDisponibles->whereIn('id', $bloquesSeleccionados);
    } else {
         // En vista detalle, mostramos las sedes FILTRADAS.
         // Si todas, son todas las del bloque. Si seleccionó, son subset.
         $itemsAProcesar = $sedesDisponibles->whereIn('id', $sedesIdsFiltrar);
    }

    foreach ($itemsAProcesar as $item) {
        // Determinar IDs de sede para este item específico
        $sedesItemIds = [];
        if ($tipoDesglose == 'bloque') {
            $sedesItemIds = $item->sedes->pluck('id')->toArray();
        } else {
            $sedesItemIds = [$item->id];
        }
        
        // Callback item
        $filtroItem = function($query) use ($inicio, $fin, $sedesItemIds) {
             if (!empty($sedesItemIds)) {
                $query->whereHas('bitacorasSede', function ($subQuery) use ($inicio, $fin, $sedesItemIds) {
                    $subQuery->whereBetween('created_at', [$inicio, $fin])
                        ->whereIn('sede_id_nuevo', $sedesItemIds)
                        ->whereRaw('id = (SELECT MAX(bs.id) FROM bitacora_sedes as bs WHERE bs.user_id = bitacora_sedes.user_id AND bs.created_at BETWEEN ? AND ?)', [$inicio, $fin]);
                });
             } else { $query->whereRaw('1 = 0'); }
        };

        // 1. Total
        $totalItem = User::withTrashed()->whereBetween('created_at', [$inicio, $fin])
          ->where(function ($q) use ($inicio, $fin) {
            $q->whereHas('bitacorasTipoUsuario', function ($sub) use ($inicio, $fin) {
                $sub->whereBetween('created_at', [$inicio, $fin])
                ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                ->whereHas('tipoUsuarioNuevo', function ($q) { $q->where('habilitado_para_consolidacion', true); });
            });
          })->tap($filtroItem)->count();

        // 2. Efectiva
        $efectivaItem = User::withTrashed()->whereBetween('created_at', [$inicio, $fin])
          ->where(function ($q) use ($inicio, $fin) {
            $q->whereHas('bitacorasTipoUsuario', function ($sub) use ($inicio, $fin) {
                $sub->whereBetween('created_at', [$inicio, $fin])
                ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                ->whereHas('tipoUsuarioNuevo', function ($q) { $q->where('habilitado_para_consolidacion', true); });
            });
          })
          ->where(function ($q) use ($inicio, $fin) {
             $q->whereDoesntHave('reportesBajaAlta', function ($sub) use ($inicio, $fin) { $sub->whereBetween('created_at', [$inicio, $fin]); })
             ->orWhereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
                $sub->whereBetween('created_at', [$inicio, $fin])->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])->where('dado_baja', false);
             });
          })->tap($filtroItem)->count();

        // 3. Vinculaciones
        $idsItem = User::withTrashed()->whereBetween('created_at', [$inicio, $fin])
          ->where(function ($q) use ($inicio, $fin) {
             $q->whereHas('bitacorasTipoUsuario', function ($sub) use ($inicio, $fin) {
                $sub->whereBetween('created_at', [$inicio, $fin])
                ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                ->whereHas('tipoUsuarioNuevo', function ($q) { $q->where('habilitado_para_consolidacion', true); });
            });
          })->tap($filtroItem)->pluck('id');

        $vinculacionesItem = TipoVinculacion::withCount(['usuarios' => function ($query) use ($idsItem) {
            $query->whereIn('users.id', $idsItem);
        }])->get();

        // --- DATOS ESCUELAS (Desglose) ---
        $subQueryLatestDateItem = Matricula::whereIn('user_id', $idsItem)
            ->whereBetween('fecha_matricula', [$inicio, $fin])
            ->select('user_id', DB::raw('MAX(fecha_matricula) as max_fecha'))
            ->groupBy('user_id');

        $latestMatriculaIdsItem = Matricula::joinSub($subQueryLatestDateItem, 'latest_dates_item', function ($join) {
                $join->on('matriculas.user_id', '=', 'latest_dates_item.user_id')
                     ->on('matriculas.fecha_matricula', '=', 'latest_dates_item.max_fecha');
            })
            ->select(DB::raw('MAX(matriculas.id) as max_id'))
            ->groupBy('matriculas.user_id')
            ->pluck('max_id');

        $matriculasCollectionItem = Matricula::whereIn('id', $latestMatriculaIdsItem)
            ->whereHas('escuela', function($q) { $q->where('habilitada_consilidacion', true); })
            ->with(['horarioMateriaPeriodo.horarioBase.aula.tipo', 'user:id,fecha_nacimiento'])
            ->get();
            
        $totalMatriculasItem = $matriculasCollectionItem->count();
        
        // Sector vs Templo
        $sectorItem = $matriculasCollectionItem->filter(function($m) { 
            return optional(optional(optional(optional($m->horarioMateriaPeriodo)->horarioBase)->aula)->tipo)->sector == true; 
        });
        $temploItem = $matriculasCollectionItem->filter(function($m) { 
            return optional(optional(optional(optional($m->horarioMateriaPeriodo)->horarioBase)->aula)->tipo)->sector == false; 
        });
        
        $matriculasSectorItem = $sectorItem->count();
        $matriculasTemploItem = $temploItem->count();
        
        // Edades
        $distSectorItem = $calcDistribucion($sectorItem, $limiteEdad);
        $distTemploItem = $calcDistribucion($temploItem, $limiteEdad);
        $sectorAdultosItem = $distSectorItem['adultos'];
        $sectorMenoresItem = $distSectorItem['menores'];
        $temploAdultosItem = $distTemploItem['adultos'];
        $temploMenoresItem = $distTemploItem['menores'];
        
        // Union Libre vs Aptos
        $userIdsMatriculadosItem = $matriculasCollectionItem->pluck('user_id')->unique();
        $matriculasUnionLibreItem = 0;
        
        if ($userIdsMatriculadosItem->isNotEmpty()) {
             $subQueryBitacoraItem = BitacoraEstadoCivil::whereIn('user_id', $userIdsMatriculadosItem) 
                ->whereBetween('created_at', [$inicio, $fin])
                ->select('user_id', DB::raw('MAX(created_at) as max_created_at'))
                ->groupBy('user_id');
             
            $latestBitacoraIdsItem = BitacoraEstadoCivil::joinSub($subQueryBitacoraItem, 'latest_bitacora_item', function ($join) {
                    $join->on('bitacora_estados_civiles.user_id', '=', 'latest_bitacora_item.user_id')
                         ->on('bitacora_estados_civiles.created_at', '=', 'latest_bitacora_item.max_created_at');
                })
                ->select(DB::raw('MAX(bitacora_estados_civiles.id) as max_id'))
                ->groupBy('bitacora_estados_civiles.user_id')
                ->pluck('max_id');
                
            $matriculasUnionLibreItem = BitacoraEstadoCivil::whereIn('id', $latestBitacoraIdsItem)
                ->whereHas('estadoCivilNuevo', function($q) { $q->where('es_union_libre', true); })
                ->count();
        }
        $matriculasAptosItem = $totalMatriculasItem - $matriculasUnionLibreItem;
        
        // Effectiveness
        $matriculasDesercionesItem = $matriculasCollectionItem->where('bloqueado', true)->count();
        $matriculasEfectivosItem = $totalMatriculasItem - $matriculasDesercionesItem;
        $porcentajeEfectividadMatriculasItem = $totalMatriculasItem > 0 ? round(($matriculasEfectivosItem / $totalMatriculasItem) * 100, 2) : 0;
        
        // --- GRÁFICAS POR ITEM (Semanal y Vinculación) ---
        
        // 1. Cosecha Semanal Item
        $fechasCosechaItem = User::withTrashed()
          ->whereBetween('created_at', [$inicio, $fin])
          ->where(function ($q) use ($inicio, $fin) {
             $q->whereHas('bitacorasTipoUsuario', function ($sub) use ($inicio, $fin) {
                $sub->whereBetween('created_at', [$inicio, $fin])
                ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                ->whereHas('tipoUsuarioNuevo', function ($q) { $q->where('habilitado_para_consolidacion', true); });
            });
          })->tap($filtroItem)->pluck('created_at');

        // 2. Desercion Semanal Item
        $fechasDesercionItem = User::withTrashed()
          ->whereBetween('created_at', [$inicio, $fin])
          ->where(function ($q) use ($inicio, $fin) {
             $q->whereHas('bitacorasTipoUsuario', function ($sub) use ($inicio, $fin) {
                $sub->whereBetween('created_at', [$inicio, $fin])
                ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                ->whereHas('tipoUsuarioNuevo', function ($q) { $q->where('habilitado_para_consolidacion', true); });
            });
          })
          ->whereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
              $sub->whereBetween('created_at', [$inicio, $fin])
                ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                ->where('dado_baja', true);
          })->tap($filtroItem)->pluck('created_at');
          
        $cosechaPorSemanaItem = $fechasCosechaItem->groupBy(function($d) { return Carbon::parse($d)->startOfWeek()->format('Y-m-d'); });
        $desercionPorSemanaItem = $fechasDesercionItem->groupBy(function($d) { return Carbon::parse($d)->startOfWeek()->format('Y-m-d'); });

        $itemGraficaSemanal = [];
        $periodoItem = \Carbon\CarbonPeriod::create($inicio->copy()->startOfWeek(), '1 week', $fin->copy()->startOfWeek()->max($inicio->copy()->startOfWeek())); // Ensure valid range

        foreach ($periodoItem as $fecha) {
            $lunes = $fecha->format('Y-m-d');
            $domingoLabel = $fecha->copy()->endOfWeek()->format('y-m-d');
            
            $cant = isset($cosechaPorSemanaItem[$lunes]) ? $cosechaPorSemanaItem[$lunes]->count() : 0;
            $cantDes = isset($desercionPorSemanaItem[$lunes]) ? $desercionPorSemanaItem[$lunes]->count() : 0;
            
            $itemGraficaSemanal[] = ['x' => $domingoLabel, 'y' => $cant, 'y_desercion' => $cantDes];
        }

        // 3. Vinculacion Semanal Item
        $cosechaVinculadaItem = User::withTrashed()->whereBetween('created_at', [$inicio, $fin])
            ->where(function ($q) use ($inicio, $fin) {
                 $q->whereHas('bitacorasTipoUsuario', function ($sub) use ($inicio, $fin) {
                    $sub->whereBetween('created_at', [$inicio, $fin])
                    ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
                    ->whereHas('tipoUsuarioNuevo', function ($q) { $q->where('habilitado_para_consolidacion', true); });
                });
            })->tap($filtroItem)->select('id', 'created_at', 'tipo_vinculacion_id')->get();
            
        $agrupadoVincItem = $cosechaVinculadaItem->groupBy(function($u) { return Carbon::parse($u->created_at)->startOfWeek()->format('Y-m-d'); })
                                                 ->map(function($s) { return $s->groupBy('tipo_vinculacion_id'); });
                                                 
        $itemGraficaVinculacion = ['labels' => [], 'series' => []];
        $tiposVinculacion = TipoVinculacion::all(); // Cached or efficient enough
        foreach ($tiposVinculacion as $tv) {
            $itemGraficaVinculacion['series'][$tv->id] = ['name' => $tv->nombre, 'data' => []];
        }
        
        foreach ($periodoItem as $fecha) {
            $lunes = $fecha->format('Y-m-d');
            $domingoLabel = $fecha->copy()->endOfWeek()->format('y-m-d');
            $itemGraficaVinculacion['labels'][] = $domingoLabel;
            
            foreach ($tiposVinculacion as $tv) {
                $c = (isset($agrupadoVincItem[$lunes]) && isset($agrupadoVincItem[$lunes][$tv->id])) ? $agrupadoVincItem[$lunes][$tv->id]->count() : 0;
                $itemGraficaVinculacion['series'][$tv->id]['data'][] = $c;
            }
        }
        $itemGraficaVinculacion['series'] = array_values($itemGraficaVinculacion['series']);



        // 4. Matriculas Semanal Item
        $fechasMatriculasItem = $matriculasCollectionItem->pluck('fecha_matricula');
        $matriculasPorSemanaItem = $fechasMatriculasItem->groupBy(function($date) {
            return Carbon::parse($date)->locale('es')->startOfWeek()->format('Y-m-d');
        });
        
        $itemGraficaMatriculasSemanal = [];
        foreach ($periodoItem as $fecha) {
            $lunes = $fecha->format('Y-m-d');
            $domingoLabel = $fecha->copy()->endOfWeek()->format('y-m-d');
            $cant = isset($matriculasPorSemanaItem[$lunes]) ? $matriculasPorSemanaItem[$lunes]->count() : 0;
            $itemGraficaMatriculasSemanal[] = ['x' => $domingoLabel, 'y' => $cant];
        }

        $datosDesglose[] = (object) [
            'id' => $item->id,
            'nombre' => $item->nombre,
            'totalCosecha' => $totalItem,
            'cosechaEfectiva' => $efectivaItem,
            'porcentajeEfectividad' => $totalItem > 0 ? round(($efectivaItem / $totalItem) * 100, 2) : 0,
            'vinculacionesCosecha' => $vinculacionesItem,
            
            // New School Metrics
            'totalMatriculas' => $totalMatriculasItem,
            'matriculasSector' => $matriculasSectorItem,
            'matriculasTemplo' => $matriculasTemploItem,
            'sectorAdultos' => $sectorAdultosItem,
            'sectorMenores' => $sectorMenoresItem,
            'temploAdultos' => $temploAdultosItem,
            'temploMenores' => $temploMenoresItem,
            'matriculasUnionLibre' => $matriculasUnionLibreItem,
            'matriculasAptos' => $matriculasAptosItem,
            'matriculasDeserciones' => $matriculasDesercionesItem,
            'matriculasEfectivos' => $matriculasEfectivosItem,
            'porcentajeEfectividadMatriculas' => $porcentajeEfectividadMatriculasItem,
            
            'graficaSemanal' => $itemGraficaSemanal,
            'graficaVinculacion' => $itemGraficaVinculacion,
            'graficaMatriculasSemanal' => $itemGraficaMatriculasSemanal
        ];
    }


    //return User::withTrashed()->whereIn('id', $userIdsCosecha)->select('id', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido')->get();

    // --- INDICADOR 2: ESCUELAS ---
    // Obtenemos los IDs de las matrículas más recientes por usuario dentro del rango (por fecha_matricula)
    $subQueryLatestDate = Matricula::whereIn('user_id', $userIdsCosecha)
        ->whereBetween('fecha_matricula', [$inicio, $fin])
        ->select('user_id', DB::raw('MAX(fecha_matricula) as max_fecha'))
        ->groupBy('user_id');

    $latestMatriculaIds = Matricula::joinSub($subQueryLatestDate, 'latest_dates', function ($join) {
            $join->on('matriculas.user_id', '=', 'latest_dates.user_id')
                 ->on('matriculas.fecha_matricula', '=', 'latest_dates.max_fecha');
        })
        ->select(DB::raw('MAX(matriculas.id) as max_id'))
        ->groupBy('matriculas.user_id')
        ->pluck('max_id');

    $matriculasCosecha = Matricula::whereIn('id', $latestMatriculaIds)
        ->whereHas('escuela', function($q) {
            $q->where('habilitada_consilidacion', true);
        })
        ->get();

    $totalMatriculas = $matriculasCosecha->count();

    $datosEscuelas = Escuela::where('habilitada_consilidacion', true)
        ->withCount(['matriculas' => function ($query) use ($latestMatriculaIds) {
            $query->whereIn('id', $latestMatriculaIds);
        }])
        ->get();

    // --- INDICADOR: SECTOR VS TEMPLO ---
    $matriculasSectorBase = Matricula::whereIn('id', $latestMatriculaIds)
        ->whereHas('horarioMateriaPeriodo.horarioBase.aula.tipo', function($q) {
            $q->where('sector', true);
        });
    
    $matriculasSector = $matriculasSectorBase->count();

    $matriculasTemploBase = Matricula::whereIn('id', $latestMatriculaIds)
        ->whereHas('horarioMateriaPeriodo.horarioBase.aula.tipo', function($q) {
            $q->where('sector', false);
        });

    $matriculasTemplo = $matriculasTemploBase->count();

    // --- INDICADOR: DISTRIBUCIÓN POR EDAD (Adultos vs Menores) ---
    $config = Configuracion::first(); // Asumiendo que hay una única configuración global
    $limiteEdad = $config->limite_menor_edad ?? 18;

    // Obtener matrículas con fecha de nacimiento para el cálculo
    $matriculasSectorData = $matriculasSectorBase->with('user:id,fecha_nacimiento')->get();
    $matriculasTemploData = $matriculasTemploBase->with('user:id,fecha_nacimiento')->get();

    $calcDistribucion = function($coleccion, $limite, $inicioRange) {
        $adultos = 0;
        $menores = 0;
        foreach ($coleccion as $m) {
            if ($m->user && $m->user->fecha_nacimiento) {
                // Edad al momento de la matrícula
                $fechaMatricula = Carbon::parse($m->fecha_matricula);
                $edad = $m->user->fecha_nacimiento->diffInYears($fechaMatricula);
                if ($edad < $limite) {
                    $menores++;
                } else {
                    $adultos++;
                }
            } else {
                // Fallback si no hay fecha de nacimiento: Adulto por defecto (ajustable según negocio)
                $adultos++;
            }
        }
        return ['adultos' => $adultos, 'menores' => $menores];
    };

    $distSector = $calcDistribucion($matriculasSectorData, $limiteEdad, $inicio);
    $distTemplo = $calcDistribucion($matriculasTemploData, $limiteEdad, $inicio);

    $sectorAdultos = $distSector['adultos'];
    $sectorMenores = $distSector['menores'];
    $temploAdultos = $distTemplo['adultos'];
    $temploMenores = $distTemplo['menores'];

    // --- INDICADOR: UNIÓN LIBRE VS APTOS ---
    // 1. Obtener los IDs de las BitacoraEstadoCivil más recientes para los usuarios de la cosecha en el rango
    // Usamos created_at para determinar cuál es el más reciente, tal como se solicitó.
    
    // Primero obtenemos los user_ids de las matrículas únicas
    $userIdsMatriculados = Matricula::whereIn('id', $latestMatriculaIds)->pluck('user_id')->unique();

    $subQueryLatestBitacora = BitacoraEstadoCivil::whereIn('user_id', $userIdsMatriculados) 
        ->whereBetween('created_at', [$inicio, $fin])
        ->select('user_id', DB::raw('MAX(created_at) as max_created_at'))
        ->groupBy('user_id');

    $latestBitacoraIds = BitacoraEstadoCivil::joinSub($subQueryLatestBitacora, 'latest_bitacora', function ($join) {
            $join->on('bitacora_estados_civiles.user_id', '=', 'latest_bitacora.user_id')
                 ->on('bitacora_estados_civiles.created_at', '=', 'latest_bitacora.max_created_at');
        })
        ->select(DB::raw('MAX(bitacora_estados_civiles.id) as max_id')) // Desempate por ID si tienen mismo created_at
        ->groupBy('bitacora_estados_civiles.user_id')
        ->pluck('max_id');

    // 2. Contar cuántos de estos registros corresponden a un estado civil "Unión Libre"
    $matriculasUnionLibre = BitacoraEstadoCivil::whereIn('id', $latestBitacoraIds)
        ->whereHas('estadoCivilNuevo', function($q) {
            $q->where('es_union_libre', true);
        })
        ->count();

    // 3. Los "Aptos" son el resto de las matrículas únicas
    // Total de matriculas unicas ($totalMatriculas) - Union Libre
    $matriculasAptos = $totalMatriculas - $matriculasUnionLibre;

    // --- INDICADOR: DESERCIONES VS EFECTIVOS ---
    $matriculasDeserciones = Matricula::whereIn('id', $latestMatriculaIds)
        ->where('bloqueado', true)
        ->count();
    
    $matriculasEfectivos = $totalMatriculas - $matriculasDeserciones;

    // --- Lógica para Gráfica Semanal (Cosecha) ---
    $porcentajeEfectividadMatriculas = $totalMatriculas > 0 ? round(($matriculasEfectivos / $totalMatriculas) * 100, 2) : 0;

    $datosGraficaSemanal = [];
    
    // Obtenemos las fechas de creación de la cosecha filtrada
    // Reusamos la lógica de $totalCosecha pero solo obtenemos pluck('created_at')
    $fechasCosecha = User::withTrashed()
      ->whereBetween('created_at', [$inicio, $fin])
      ->where(function ($query) use ($inicio, $fin) {
        $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
          $subQuery->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->whereHas('tipoUsuarioNuevo', function ($q) { $q->where('habilitado_para_consolidacion', true); });
        });
      })
      ->tap($filtroSedesCallback)
      ->pluck('created_at');

    // Obtenemos las fechas de creación de las DESERCIONES (Bajas)
    // Usuarios creados en el rango, que TIENEN una baja activa (último reporte es baja)
    $fechasDesercion = User::withTrashed()
      ->whereBetween('created_at', [$inicio, $fin])
      ->where(function ($query) use ($inicio, $fin) {
        $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
          $subQuery->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->whereHas('tipoUsuarioNuevo', function ($q) { $q->where('habilitado_para_consolidacion', true); });
        });
      })
      ->whereHas('reportesBajaAlta', function ($sub) use ($inicio, $fin) {
          $sub->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(r2.id) FROM reporte_bajas_altas as r2 WHERE r2.user_id = reporte_bajas_altas.user_id AND r2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->where('dado_baja', true);
      })
      ->tap($filtroSedesCallback)
      ->pluck('created_at');

    // Agrupamos por semana (Lunes a Domingo)
    // El formato de la key será el Lunes de esa semana
    $cosechaPorSemana = $fechasCosecha->groupBy(function($date) {
        return Carbon::parse($date)->locale('es')->startOfWeek()->format('Y-m-d');
    });

    $desercionPorSemana = $fechasDesercion->groupBy(function($date) {
        return Carbon::parse($date)->locale('es')->startOfWeek()->format('Y-m-d');
    });

    // Generamos el periodo completo de semanas para rellenar huecos
    // Ajustamos inicio y fin al Lunes de la semana correspondiente
    $inicioSemana = $inicio->copy()->startOfWeek();
    $finSemana = $fin->copy()->startOfWeek();
    
    // Si el rango es menor a una semana, al menos mostramos esa semana
    if ($finSemana->lt($inicioSemana)) {
        $finSemana = $inicioSemana->copy();
    }

    $periodo = \Carbon\CarbonPeriod::create($inicioSemana, '1 week', $finSemana);

    foreach ($periodo as $fecha) {
        $lunes = $fecha->format('Y-m-d');
        // El usuario prefiere solo el último día de la semana en formato año-mes-día (ej: 26-01-11)
        $domingoLabel = $fecha->copy()->endOfWeek()->format('y-m-d');
        
        $cantidad = isset($cosechaPorSemana[$lunes]) ? $cosechaPorSemana[$lunes]->count() : 0;
        $cantidadDesercion = isset($desercionPorSemana[$lunes]) ? $desercionPorSemana[$lunes]->count() : 0;
        
        $datosGraficaSemanal[] = [
            'x' => $domingoLabel,
            'y' => $cantidad,
            'y_desercion' => $cantidadDesercion
        ];
    }

    // --- Lógica para Gráfica Semanal por Vinculación ---
    $datosVinculacionSemanal = [
        'labels' => [], // Fechas (Domingos)
        'series' => []  // [ {name: 'Amigo', data: [...]}, ... ]
    ];

    // Obtenemos todos los tipos de vinculación para tener las series completas
    $tiposVinculacion = TipoVinculacion::all();
    
    // Obtenemos los usuarios con su vinculación
    $cosechaVinculada = User::withTrashed()
      ->whereBetween('created_at', [$inicio, $fin])
      ->where(function ($query) use ($inicio, $fin) {
        $query->whereHas('bitacorasTipoUsuario', function ($subQuery) use ($inicio, $fin) {
          $subQuery->whereBetween('created_at', [$inicio, $fin])
            ->whereRaw('id = (SELECT MAX(b2.id) FROM bitacora_tipos_usuarios as b2 WHERE b2.user_id = bitacora_tipos_usuarios.user_id AND b2.created_at BETWEEN ? AND ?)', [$inicio, $fin])
            ->whereHas('tipoUsuarioNuevo', function ($q) { $q->where('habilitado_para_consolidacion', true); });
        });
      })
      ->tap($filtroSedesCallback)
      ->select('id', 'created_at', 'tipo_vinculacion_id')
      ->get();

    // Agrupamos por semana y luego por vinculación
    $agrupadoVinc = $cosechaVinculada->groupBy(function($u) {
        return Carbon::parse($u->created_at)->startOfWeek()->format('Y-m-d');
    })->map(function($semana) {
        return $semana->groupBy('tipo_vinculacion_id');
    });

    // Inicializamos las series
    foreach ($tiposVinculacion as $tv) {
        $datosVinculacionSemanal['series'][$tv->id] = [
            'name' => $tv->nombre,
            'data' => []
        ];
    }

    // Recorremos el periodo para llenar las labels y los datos
    foreach ($periodo as $fecha) {
        $lunes = $fecha->format('Y-m-d');
        $domingoLabel = $fecha->copy()->endOfWeek()->format('y-m-d');
        $datosVinculacionSemanal['labels'][] = $domingoLabel;

        foreach ($tiposVinculacion as $tv) {
            $count = 0;
            if (isset($agrupadoVinc[$lunes]) && isset($agrupadoVinc[$lunes][$tv->id])) {
                $count = $agrupadoVinc[$lunes][$tv->id]->count();
            }
            $datosVinculacionSemanal['series'][$tv->id]['data'][] = $count;
        }
    }

    // Convertimos las series a array indexado para JS
    $datosVinculacionSemanal['series'] = array_values($datosVinculacionSemanal['series']);

    // --- Lógica para Gráfica Semanal (Escuelas / Matrículas) ---
    $datosMatriculasSemanal = [];
    
    // Agrupamos las matrículas (ya filtradas) por fecha de matrícula
    $fechasMatriculas = $matriculasCosecha->pluck('fecha_matricula');
    
    $matriculasPorSemana = $fechasMatriculas->groupBy(function($date) {
        return Carbon::parse($date)->locale('es')->startOfWeek()->format('Y-m-d');
    });
    
    // Reusamos $periodo ya calculado
    foreach ($periodo as $fecha) {
        $lunes = $fecha->format('Y-m-d');
        $domingoLabel = $fecha->copy()->endOfWeek()->format('y-m-d');
        
        $cantidad = isset($matriculasPorSemana[$lunes]) ? $matriculasPorSemana[$lunes]->count() : 0;
        
        $datosMatriculasSemanal[] = [
            'x' => $domingoLabel,
            'y' => $cantidad
        ];
    }



    $bloquesDisponiblesView = $esVistaDetalle ? collect() : $bloquesDisponibles;

    return view('contenido.paginas.consolidacion.dashboard', compact(
      'rangoFechas',
       'totalCosecha',
      'cosechaEfectiva',
      'porcentajeEfectividad',
      'vinculacionesCosecha',
      'esVistaDetalle',
      'bloqueActual',
      'tipoDesglose',
      'datosDesglose', // Reemplaza a datosPorBloque
      // Variables Filtro Bloque
      'bloquesDisponibles',
      'bloquesSeleccionados',
      // Variables Filtro Sede
      'sedesDisponibles',
      'sedesSeleccionadas',
      // Indicador 2
      'totalMatriculas',
      'datosEscuelas',
      'matriculasSector',
      'matriculasTemplo',
      'sectorAdultos',
      'sectorMenores',
      'temploAdultos',
      'temploMenores',
      'matriculasUnionLibre',
      'matriculasAptos',
      'matriculasDeserciones',
      'matriculasEfectivos',
      'porcentajeEfectividadMatriculas',
      'datosGraficaSemanal',
      'datosVinculacionSemanal',
      'datosMatriculasSemanal'
    ));
  }

  /* public function dashboard(Request $request)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('consolidacion.dashboard_consolidacion');

    $anio = $request->anio ?? date('Y');
    $semana = $request->semana ?? (int)date('W');

    $anios = range(date('Y') + 1, 2022);
    $semanas = range(1, 52);

    // Cálculo de fechas para la semana seleccionada
    $fechaInicioSemana = Carbon::now()->setISODate($anio, $semana)->startOfWeek()->format('Y-m-d');
    $fechaFinSemana = Carbon::now()->setISODate($anio, $semana)->endOfWeek()->format('Y-m-d');

    // 1. Obtener tipos de usuario habilitados para consolidación
    $tiposConsolidables = TipoUsuario::where('habilitado_para_consolidacion', true)->pluck('id');

    // 2. Obtener IDs únicos de usuarios que entraron a consolidación en ese rango (según bitácora)
    $userIdsSemanales = BitacoraTipoUsuario::whereBetween('created_at', [$fechaInicioSemana . ' 00:00:00', $fechaFinSemana . ' 23:59:59'])
      ->whereIn('tipo_usuario_id_nuevo', $tiposConsolidables)
      ->distinct()
      ->pluck('user_id');

    // 3. Estadísticas para la pestaña Semanal: Usuarios clasificados por Tipo de Vinculación
    $vinculacionesSemanales = TipoVinculacion::withCount(['usuarios' => function ($query) use ($userIdsSemanales) {
      $query->whereIn('id', $userIdsSemanales);
    }])->get();

    return view('contenido.paginas.consolidacion.dashboard', compact(
      'anio',
      'semana',
      'anios',
      'semanas',
      'vinculacionesSemanales',
      'fechaInicioSemana',
      'fechaFinSemana'
    ));
  }*/
}
