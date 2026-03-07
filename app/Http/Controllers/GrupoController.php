<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Grupo;
use App\Models\TipoGrupo;
use App\Helpers\Helpers;
use App\Models\CampoInformeExcel;
use App\Models\CampoExtraGrupo;
use App\Models\ClasificacionAsistente;
use App\Models\GrupoExcluido;
use App\Models\Iglesia;
use App\Models\IntegranteGrupo;
use App\Models\MotivoNoReporteGrupo;
use App\Models\PasoCrecimiento;
use App\Models\ReporteGrupo;
use App\Models\Sede;
use App\Models\ServidorGrupo;
use App\Models\TipoUsuario;
use App\Models\TipoVivienda;
use App\Models\BloqueDashboardConsolidacion;
use App\Models\ReporteGrupoBajaAlta;
use App\Models\BloqueClasificacionAsistente;
use App\Models\User;
use Illuminate\Http\Request;
use \stdClass;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;



class GrupoController extends Controller
{
  public function listar(Request $request, $tipo = 'todos')
  {     
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('grupos.subitem_lista_grupos');

    $configuracion = Configuracion::find(1);

    $tiposGruposIds = TipoGrupo::where("seguimiento_actividad", "=", TRUE)->select('id')->pluck('id')->toArray();
    $tiposDeViviendas =  TipoVivienda::orderBy('nombre', 'asc')->get();
    $tiposDeGrupo = TipoGrupo::orderBy('nombre', 'asc')->get();
    $sedes = Sede::get();
    $grupos = [];
    $indicadoresGenerales = [];
    $indicadoresPortipoGrupo = [];
    $camposInformeExcel = CampoInformeExcel::where('selector_id', '=', 5)->orderBy('orden', 'asc')->get();

    $parametrosBusqueda = [];
    $parametrosBusqueda['buscar'] = $request->buscar;
    $parametrosBusqueda['filtroGrupo'] = $request->filtroGrupo;
    $parametrosBusqueda['filtroPorTipoDeGrupo'] = $request->filtroPorTipoDeGrupo;
    $parametrosBusqueda['filtroPorSedes'] = $request->filtroPorSedes;
    $parametrosBusqueda['filtroPorTiposDeViviendas'] = $request->filtroPorTiposDeViviendas;
    $parametrosBusqueda['bandera'] = '';
    $parametrosBusqueda['textoBusqueda'] = '';
    $parametrosBusqueda['tagsBusqueda'] = [];
    $parametrosBusqueda['tipo'] = $tipo;
    $parametrosBusqueda = (object) $parametrosBusqueda;

    if ($rolActivo->hasPermissionTo('grupos.lista_grupos_todos') || $rolActivo->hasPermissionTo('grupos.lista_grupos_solo_ministerio') || $rolActivo->lista_grupos_sede_id != NULL) {
      if ($rolActivo->hasPermissionTo('grupos.lista_grupos_todos') || isset(auth()->user()->iglesiaEncargada()->first()->id)) {
        $grupos = Grupo::leftJoin('encargados_grupo', 'grupos.id', '=', 'encargados_grupo.grupo_id')
          ->leftJoin('users', 'users.id', '=', 'encargados_grupo.user_id')
          ->select('grupos.*', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', 'users.segundo_apellido')
          ->take(5000)
          ->get()
          ->unique('id');

        $gruposParaIndicadores = clone $grupos;
      }

      if ($rolActivo->hasPermissionTo('grupos.lista_grupos_solo_ministerio')) {
        $grupos = auth()->user()->gruposMinisterio()->leftJoin('encargados_grupo', 'grupos.id', '=', 'encargados_grupo.grupo_id')
          ->leftJoin('users', 'users.id', '=', 'encargados_grupo.user_id')
          ->select('grupos.*', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', 'users.segundo_apellido')
          ->get()
          ->unique('id');

        $gruposParaIndicadores = clone $grupos;
      }
    }

    // Contadores
    $item = new stdClass();
    $item->nombre = 'Todos';
    $item->url = 'todos';
    $item->cantidad = $gruposParaIndicadores->where('dado_baja', FALSE)->pluck('id')->count();
    $item->color = 'bg-label-success';
    $item->imagen = 'icono_indicador.png';
    $indicadoresGenerales[] = $item;

    $item = new stdClass();
    $item->nombre = 'Nuevos';
    $item->url = 'nuevos';
    $item->cantidad = Grupo::gruposNuevos()->select('grupos.id')->count();
    $item->color = 'bg-label-info';
    $item->imagen = 'icono_indicador.png';
    $indicadoresGenerales[] = $item;

    $item = new stdClass();
    $item->nombre = 'Sin geo referencia';
    $item->url = 'sin-georreferencia';
    $item->cantidad = $gruposParaIndicadores->whereNull("latitud")->whereNull("longitud")->where('dado_baja', FALSE)->pluck('id')->count();
    $item->color = 'bg-label-danger';
    $item->imagen = 'icono_indicador.png';
    $indicadoresGenerales[] = $item;

    $item = new stdClass();
    $item->nombre = 'Grupos sin líderes';
    $item->url = 'grupos-sin-lideres';
    $item->cantidad = Grupo::gruposSinLider()->select('grupos.id')->count();
    $item->color = 'bg-label-danger';
    $item->imagen = 'icono_indicador.png';
    $indicadoresGenerales[] = $item;

    $item = new stdClass();
    $item->nombre = 'Sin actividad';
    $item->url = 'sin-actividad';
    $item->cantidad = $gruposParaIndicadores->where('dado_baja', FALSE)->whereIn('tipo_grupo_id', $tiposGruposIds)->filter(function ($grupo) {
      $fechaMaximaActividad = Carbon::now()
        ->subDays($grupo->tipoGrupo->tiempo_para_definir_inactivo_grupo)
        ->format('Y-m-d');

      return $grupo->ultimo_reporte_grupo < $fechaMaximaActividad ||
        $grupo->ultimo_reporte_grupo == null;
    })->pluck('id')->count();
    $item->color = 'bg-label-danger';
    $item->imagen = 'icono_indicador.png';
    $indicadoresGenerales[] = $item;

    $item = new stdClass();
    $item->nombre = 'Dados de baja';
    $item->url = 'dados-de-baja';
    $item->cantidad = $gruposParaIndicadores->where('dado_baja', TRUE)->pluck('id')->count();
    $item->color = 'bg-label-secondary';
    $item->imagen = 'icono_indicador.png';
    $indicadoresGenerales[] = $item;

    foreach ($tiposDeGrupo as $tipoGrupo) {
      $item = new stdClass();
      $item->nombre = $tipoGrupo->nombre;
      $item->url = $tipoGrupo->id;
      $item->cantidad = $gruposParaIndicadores
        ->where('dado_baja', FALSE)
        ->where('tipo_grupo_id', $tipoGrupo->id)
        ->count();
      $item->color = 'bg-label-success';
      $item->imagen = $tipoGrupo->imagen;
      $indicadoresPortipoGrupo[] = $item;
    }
    // Fin contadores

    $indicadoresGenerales = collect(array_merge($indicadoresGenerales, $indicadoresPortipoGrupo));

    // filtrado por tipo ejemplo: "Todos o nuevos, o sin georeferencia o por los tipos de grupo como abiertos o cerrados, grupo familiar etc..."
    $grupos = $this->filtroPorTipo($grupos, $parametrosBusqueda);

    // filtro por busqueda
    $grupos = $this->filtrosBusqueda($grupos, $parametrosBusqueda);

    if ($grupos->count() > 0) {
      $grupos = $grupos->toQuery()->orderBy('id', 'desc')->paginate(12);
    } else {
      $grupos = Grupo::whereRaw('1=2')->paginate(1);
    }

    //return $camposInformeExcel;
    $camposExtras = CampoExtraGrupo::where('visible', '=', true)->get();

    return view('contenido.paginas.grupos.listar', [
      'configuracion' => $configuracion,
      'rolActivo' => $rolActivo,
      'grupos' => $grupos,
      'tipo' => $tipo,
      'parametrosBusqueda' => $parametrosBusqueda,
      'indicadoresGenerales' => $indicadoresGenerales,
      //'indicadoresPortipoGrupo' => $indicadoresPortipoGrupo,
      'tiposDeGrupo' => $tiposDeGrupo,
      'tiposDeViviendas' => $tiposDeViviendas,
      'sedes' => $sedes,
      'camposInformeExcel' => $camposInformeExcel,
      'camposExtras' => $camposExtras
    ]);
  }

  public function filtroPorTipo($grupos, $parametrosBusqueda)
  {
    $tiposGruposIds = TipoGrupo::where("seguimiento_actividad", "=", TRUE)->select('id')->pluck('id')->toArray();

    // Filtro por tipo
    if ($parametrosBusqueda->tipo == "nuevos") {
      // la funcion gruposNuevos carga por defecto los dado_baja FALSE
      $grupos = Grupo::gruposNuevos()->leftJoin('encargados_grupo', 'grupos.id', '=', 'encargados_grupo.grupo_id')
        ->leftJoin('users', 'users.id', '=', 'encargados_grupo.user_id')
        ->select('grupos.*', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', 'users.segundo_apellido')
        ->get()
        ->unique('id');

      $parametrosBusqueda->textoBusqueda .= 'Nuevos';
    } elseif ($parametrosBusqueda->tipo == "sin-georreferencia") {
      $grupos = $grupos->whereNull("latitud")->whereNull("longitud")->where('dado_baja', FALSE);

      $parametrosBusqueda->textoBusqueda .= 'Sin geo referencia';
    } elseif ($parametrosBusqueda->tipo == "sin-actividad") {
      $grupos = $grupos->where('dado_baja', FALSE)->whereIn('tipo_grupo_id', $tiposGruposIds)->filter(function ($grupo) {
        $fechaMaximaActividad = Carbon::now()
          ->subDays($grupo->tipoGrupo->tiempo_para_definir_inactivo_grupo)
          ->format('Y-m-d');

        return $grupo->ultimo_reporte_grupo < $fechaMaximaActividad ||
          $grupo->ultimo_reporte_grupo == null;
      });

      $parametrosBusqueda->textoBusqueda .= 'Sin actividad';
    } elseif ($parametrosBusqueda->tipo == "dados-de-baja") {
      $grupos = $grupos->where('dado_baja', TRUE);
      $parametrosBusqueda->textoBusqueda .= 'Dados de baja';
    } elseif ($parametrosBusqueda->tipo == "grupos-sin-lideres") {
      // la funcion gruposSinLider carga por defecto los dado_baja FALSE
      $grupos = Grupo::gruposSinLider()->leftJoin('encargados_grupo', 'grupos.id', '=', 'encargados_grupo.grupo_id')
        ->leftJoin('users', 'users.id', '=', 'encargados_grupo.user_id')
        ->select('grupos.*', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', 'users.segundo_apellido')
        ->get()
        ->unique('id');

      $parametrosBusqueda->textoBusqueda .= 'Sin lideres';
    } elseif ($parametrosBusqueda->tipo == "todos") {
      $grupos = $grupos->where('dado_baja', FALSE);

      $parametrosBusqueda->textoBusqueda .= 'Todos';
    } else {
      $grupos = $grupos->where('dado_baja', FALSE)->where('tipo_grupo_id', '=', $parametrosBusqueda->tipo);
      $tipoGrupoSeleccionado = TipoGrupo::select('id', 'nombre')->first();
      $parametrosBusqueda->textoBusqueda .= $tipoGrupoSeleccionado->nombre;
    }

    return $grupos;
  }

  public function filtrosBusqueda($grupos, $parametrosBusqueda)
  {
    // Busqueda a partir del filtro de tipos de grupo
    if (isset($parametrosBusqueda->filtroPorTipoDeGrupo)) {
      $tipoGruposFiltro = TipoGrupo::select('nombre_plural', 'id')
        ->whereIn('id', $parametrosBusqueda->filtroPorTipoDeGrupo)
        ->get();

      $cantidad = $tipoGruposFiltro->count();
      $contador = 1;
      $parametrosBusqueda->textoBusqueda .= ' "';
      foreach ($tipoGruposFiltro as $tipo) {
        if ($contador == $cantidad) {
          $parametrosBusqueda->textoBusqueda .= $tipo->nombre_plural;
        } else {
          $parametrosBusqueda->textoBusqueda .= $tipo->nombre_plural . ', ';
        }
        $contador++;

        // Tag por tipo de grupo
        $tag = new stdClass();
        $tag->label = $tipo->nombre_plural;
        $tag->field = 'filtroPorTipoDeGrupo';
        $tag->value = $tipo->id;
        $tag->fieldAux = '';
        $parametrosBusqueda->tagsBusqueda[] = $tag;
      }
      $parametrosBusqueda->textoBusqueda .= '"';

      $grupos = $grupos->whereIn('tipo_grupo_id', $parametrosBusqueda->filtroPorTipoDeGrupo);
      $parametrosBusqueda->bandera = 1;
    }

    // Busqueda a partir del filtro de sedes
    if (isset($parametrosBusqueda->filtroPorSedes)) {
      $sedesFiltro = Sede::select('nombre', 'id')
        ->whereIn('id', $parametrosBusqueda->filtroPorSedes)
        ->get();

      $cantidad = $sedesFiltro->count();
      $contador = 1;
      $parametrosBusqueda->textoBusqueda .= ' "';
      foreach ($sedesFiltro as $sede) {
        if ($contador == $cantidad) {
          $parametrosBusqueda->textoBusqueda .= $sede->nombre;
        } else {
          $parametrosBusqueda->textoBusqueda .= $sede->nombre . ', ';
        }
        $contador++;

        // Tag por sede
        $tag = new stdClass();
        $tag->label = $sede->nombre;
        $tag->field = 'filtroPorSedes';
        $tag->value = $sede->id;
        $tag->fieldAux = '';
        $parametrosBusqueda->tagsBusqueda[] = $tag;
      }
      $parametrosBusqueda->textoBusqueda .= '"';

      $grupos = $grupos->whereIn('sede_id', $parametrosBusqueda->filtroPorSedes);
      $parametrosBusqueda->bandera = 1;
    }

    // Busqueda a partir del filtro de tipos de vivienda
    if (isset($parametrosBusqueda->filtroPorTiposDeViviendas)) {
      $tiposDeViviendaFiltro = TipoVivienda::select('nombre', 'id')
        ->whereIn('id', $parametrosBusqueda->filtroPorTiposDeViviendas)
        ->get();

      $cantidad = $tiposDeViviendaFiltro->count();
      $contador = 1;
      $parametrosBusqueda->textoBusqueda .= ' "';
      foreach ($tiposDeViviendaFiltro as $tipoDeVivienda) {
        if ($contador == $cantidad) {
          $parametrosBusqueda->textoBusqueda .= $tipoDeVivienda->nombre;
        } else {
          $parametrosBusqueda->textoBusqueda .= $tipoDeVivienda->nombre . ', ';
        }
        $contador++;

        // Tag por tipoDeVivienda
        $tag = new stdClass();
        $tag->label = $tipoDeVivienda->nombre;
        $tag->field = 'filtroPorTiposDeViviendas';
        $tag->value = $tipoDeVivienda->id;
        $tag->fieldAux = '';
        $parametrosBusqueda->tagsBusqueda[] = $tag;
      }
      $parametrosBusqueda->textoBusqueda .= '"';

      $grupos = $grupos->whereIn('tipo_vivienda_id', $parametrosBusqueda->filtroPorTiposDeViviendas);
      $parametrosBusqueda->bandera = 1;
    }

    // Busqueda por palabra clave
    if ($parametrosBusqueda->buscar != '') {
      $buscar = htmlspecialchars($parametrosBusqueda->buscar);
      $buscar = Helpers::sanearStringConEspacios($buscar);
      $buscar = str_replace(["'"], '', $buscar);
      $buscar_array = explode(' ', $buscar);

      foreach ($buscar_array as $palabra) {
        $grupos = $grupos->filter(function ($grupo) use ($palabra) {
          return false !== stristr(Helpers::sanearStringConEspacios($grupo->nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($grupo->direccion), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($grupo->primer_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($grupo->segundo_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($grupo->primer_apellido), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($grupo->segundo_apellido), $palabra) ||
            $grupo->id === $palabra;
        });
      }
      $parametrosBusqueda->textoBusqueda .= '<b>, con busqueda: </b>"' . $buscar . '" ';
      $parametrosBusqueda->bandera = 1;

      // Crear una tag
      $tag = new stdClass();
      $tag->label = $parametrosBusqueda->buscar;
      $tag->field = 'buscar';
      $tag->value = $buscar;
      $tag->fieldAux = '';
      $parametrosBusqueda->tagsBusqueda[] = $tag;
    }

    // Busqueda a partir de un grupo
    if ($parametrosBusqueda->filtroGrupo != '') {
      $grupoRaiz = Grupo::find($parametrosBusqueda->filtroGrupo);
      $gruposMinisterio = array_merge($grupoRaiz->gruposMinisterio("array"), [$parametrosBusqueda->filtroGrupo]);
      $grupos = $grupos->whereIn('id', $gruposMinisterio);

      $parametrosBusqueda->textoBusqueda .= '<b>, bajo la cobertura del grupo: </b>' . $grupoRaiz->nombre;
      $parametrosBusqueda->bandera = 1;

      // Crear la tag a partir de un grupo
      $tag = new stdClass();
      $tag->label = 'A partir de "' . $grupoRaiz->nombre . '"';
      $tag->field = 'filtroGrupo';
      $tag->value = $grupoRaiz->id;
      $tag->fieldAux = '';
      $parametrosBusqueda->tagsBusqueda[] = $tag;
    }


    return $grupos;
  }

  public function listadoFinalCsv(Request $request)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $parametrosBusqueda = json_decode($request->parametrosBusqueda);

    $configuracion = Configuracion::find(1);

    $arrayCamposInfoGrupo = $request->informacionPrincipal ? $request->informacionPrincipal : []; //$arrayCamposInfoGrupo

    $arrayCamposExtra = [];
    if ($configuracion->visible_seccion_campos_extra_grupo)
      $arrayCamposExtra = $request->informacionCamposExtras ? $request->informacionCamposExtras : []; // $arrayCamposExtra

    $camposInforme = CampoInformeExcel::whereIn('campos_informe_excel.id', $arrayCamposInfoGrupo)
      ->orderBy('orden', 'asc')
      ->get();

    $nombreArchivo = 'informe_grupos' . Carbon::now()->format('Y-m-d-H-i-s');
    $rutaArchivo = "/$configuracion->ruta_almacenamiento/informes/grupos/$nombreArchivo.csv";

    $archivo = fopen(storage_path('app/public') . $rutaArchivo, 'w');
    fputs($archivo, $bom = chr(0xef) . chr(0xbb) . chr(0xbf));

    /* Aquí se crean los encabezados */
    $arrayEncabezadoFila1 = [];
    $arrayEncabezadoFila2 = [];

    foreach ($camposInforme->pluck('nombre_campo_informe')->toArray() as $campo) {

      switch ($campo) {
        case '1':
          array_push($arrayEncabezadoFila1, $configuracion->label_campo_opcional1);
          break;
        case 'dia_planeacion':
          array_push($arrayEncabezadoFila1, $configuracion->label_campo_dia_planeacion_grupo);
          break;
        case 'hora_planeacion':
          array_push($arrayEncabezadoFila1, $configuracion->label_campo_hora_planeacion_grupo);
          break;
        case 'dia':
          array_push($arrayEncabezadoFila1, $configuracion->label_campo_dia_reunion_grupo);
          break;
        case 'hora':
          array_push($arrayEncabezadoFila1, $configuracion->label_campo_hora_reunion_grupo);
          break;
        default:
          array_push($arrayEncabezadoFila1, $campo);
      }

      array_push($arrayEncabezadoFila2, ' ');
    }

    // agrego los campos extra al encabezado
    $camposExtraSeleccionados = CampoExtraGrupo::whereIn('id', $arrayCamposExtra)
      ->orderBy('id', 'asc')
      ->get();

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

    $grupos = [];
    if ($rolActivo->hasPermissionTo('grupos.lista_grupos_todos') || $rolActivo->hasPermissionTo('grupos.lista_grupos_solo_ministerio') || $rolActivo->lista_grupos_sede_id != NULL) {
      if ($rolActivo->hasPermissionTo('grupos.lista_grupos_todos') || isset(auth()->user()->iglesiaEncargada()->first()->id)) {
        $grupos = Grupo::leftJoin('encargados_grupo', 'grupos.id', '=', 'encargados_grupo.grupo_id')
          ->leftJoin('users', 'users.id', '=', 'encargados_grupo.user_id')
          ->select('grupos.*', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', 'users.segundo_apellido')
          ->get()
          ->unique('id');
      }

      if ($rolActivo->hasPermissionTo('grupos.lista_grupos_solo_ministerio')) {
        $grupos = auth()->user()->gruposMinisterio()->leftJoin('encargados_grupo', 'grupos.id', '=', 'encargados_grupo.grupo_id')
          ->leftJoin('users', 'users.id', '=', 'encargados_grupo.user_id')
          ->select('grupos.*', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', 'users.segundo_apellido')
          ->get()
          ->unique('id');
      }
    }

    // filtrado por tipo ejemplo: "Todos o nuevos, o sin georeferencia o por los tipos de grupo como abiertos o cerrados, grupo familiar etc..."
    $grupos = $this->filtroPorTipo($grupos, $parametrosBusqueda);

    // filtro por busqueda
    $grupos = $this->filtrosBusqueda($grupos, $parametrosBusqueda);

    foreach ($grupos as $grupo) {
      $fila = [];

      // Nombre
      if ($camposInforme->where('nombre_campo_bd', 'nombre')->count() > 0) {
        array_push($fila, $grupo->nombre ? $grupo->nombre : 'Sin información');
      }

      //fecha_apertura
      if ($camposInforme->where('nombre_campo_bd', 'fecha_apertura')->count() > 0) {
        array_push($fila, $grupo->fecha_apertura ? $grupo->fecha_apertura : 'Sin información');
      }

      //tipo_vivienda
      if ($camposInforme->where('nombre_campo_bd', 'tipo_vivienda')->count() > 0) {
        array_push($fila, $grupo->tipoDeVivienda ? $grupo->tipoDeVivienda->nombre : 'Sin información');
      }

      //direccion
      if ($camposInforme->where('nombre_campo_bd', 'direccion')->count() > 0) {
        array_push($fila, $grupo->direccion ? $grupo->direccion : 'Sin información');
      }

      //telefono
      if ($camposInforme->where('nombre_campo_bd', 'telefono')->count() > 0) {
        array_push($fila, $grupo->telefono ? $grupo->telefono : 'Sin información');
      }

      //dia
      if ($camposInforme->where('nombre_campo_bd', 'dia')->count() > 0) {
        array_push($fila, Helpers::obtenerDiaDeLaSemana($grupo->dia) ? Helpers::obtenerDiaDeLaSemana($grupo->dia) : 'Sin información');
      }

      //hora
      if ($camposInforme->where('nombre_campo_bd', 'hora')->count() > 0) {
        array_push($fila, $grupo->hora ? $grupo->hora : 'Sin información');
      }

      //dia_planeacion
      if ($camposInforme->where('nombre_campo_bd', 'dia_planeacion')->count() > 0) {
        array_push($fila, Helpers::obtenerDiaDeLaSemana($grupo->dia_planeacion) ? Helpers::obtenerDiaDeLaSemana($grupo->dia_planeacion) : 'Sin información');
      }

      //hora_planeación
      if ($camposInforme->where('nombre_campo_bd', 'hora_planeacion')->count() > 0) {
        array_push($fila, $grupo->hora_planeacion ? $grupo->hora_planeacion : 'Sin información');
      }

      //encargados
      if ($camposInforme->where('nombre_campo_bd', 'encargados')->count() > 0) {
        //array_push($fila, $grupo->hora_planeación ? $grupo->hora_planeación : 'Sin información');
        $encargados = $grupo->encargados()->get();
        $texto = '';
        foreach ($encargados as $encargado) {
          $texto .= ($encargados->first()->id == $encargado->id) ? $encargado->nombre(3) : ", " . $encargado->nombre(3);
        }

        array_push($fila, $texto ? $texto : 'Sin información');
      }

      //fecha
      if ($camposInforme->where('nombre_campo_bd', 'fecha')->count() > 0) {
        array_push($fila, $grupo->ultimo_reporte_grupo ? Carbon::parse($grupo->ultimo_reporte_grupo)->format('Y-m-d') : 'Sin información');
      }

      //latitud
      if ($camposInforme->where('nombre_campo_bd', 'latitud')->count() > 0) {
        array_push($fila, $grupo->latitud ? 'Está georreferenciado' : 'Sin información');
      }

      //sede_id
      if ($camposInforme->where('nombre_campo_bd', 'sede_id')->count() > 0) {
        array_push($fila, $grupo->sede ? $grupo->sede->nombre : 'Sin información');
      }

      //cantidad_asistentes
      if ($camposInforme->where('nombre_campo_bd', 'cantidad_asistentes')->count() > 0) {
        array_push($fila, $grupo->asistentes()->select('grupos.id')->count());
      }

      //label_campo_opcional1
      if ($camposInforme->where('nombre_campo_bd', 'label_campo_opcional1')->count() > 0) {
        array_push($fila, $grupo->rhema ? $grupo->rhema : 'Sin información');
      }

      //tipo_grupo_id
      if ($camposInforme->where('nombre_campo_bd', 'tipo_grupo_id')->count() > 0) {
        array_push($fila, $grupo->tipoGrupo ? $grupo->tipoGrupo->nombre : 'Sin información');
      }

      //grupo_id
      if ($camposInforme->where('nombre_campo_bd', 'grupo_id')->count() > 0) {

        $encargado = $grupo->encargados()->first();
        $texto = '';
        if ($encargado) {
          $gruposDelEncargado = $encargado->gruposEncargados()->select('grupos.id', 'grupos.nombre')->get();

          foreach ($gruposDelEncargado as $grupoDelEncargado) {
            $texto .= ($gruposDelEncargado->first()->id == $grupoDelEncargado->id) ? $grupoDelEncargado->nombre : ", " . $grupoDelEncargado->nombre;
          }
        }
        array_push($fila, $texto ? $texto : 'Sin información');
      }

      //fecha_baja
      if ($camposInforme->where('nombre_campo_bd', 'fecha_baja')->count() > 0) {
        $reporte = $grupo->reportesBajaAlta()->where('dado_baja', true)->orderBy('created_at', 'desc')->first();
        array_push($fila, $reporte ? $reporte->fecha : 'Sin información');
      }

      //motivo_baja
      if ($camposInforme->where('nombre_campo_bd', 'motivo_baja')->count() > 0) {
        $reporte = $grupo->reportesBajaAlta()->where('dado_baja', true)->orderBy('created_at', 'desc')->first();
        array_push($fila, $reporte ? $reporte->motivo : 'Sin información');
      }

      //fecha_alta
      if ($camposInforme->where('nombre_campo_bd', 'fecha_alta')->count() > 0) {
        $reporte = $grupo->reportesBajaAlta()->where('dado_baja', false)->orderBy('created_at', 'desc')->first();
        array_push($fila, $reporte ? $reporte->fecha : 'Sin información');
      }

      //motivo_alta
      if ($camposInforme->where('nombre_campo_bd', 'motivo_alta')->count() > 0) {
        $reporte = $grupo->reportesBajaAlta()->where('dado_baja', false)->orderBy('created_at', 'desc')->first();
        array_push($fila, $reporte ? $reporte->motivo : 'Sin información');
      }


      //AQUI EMPIEZA EL CONSTRUCTOR DE PASOS EXTRA
      foreach ($camposExtraSeleccionados as $campo) {
        $campoExtraGrupo = $grupo
          ->camposExtras()
          ->where('campo_extra_grupo_id', $campo->id)
          ->first();

        if ($campo->tipo_de_campo == 1) {
          array_push($fila, $campoExtraGrupo ? $campoExtraGrupo->pivot->valor : 'Sin información');
        }

        if ($campo->tipo_de_campo == 2) {
          array_push($fila, $campoExtraGrupo ? $campoExtraGrupo->pivot->valor : 'Sin información');
        }

        if ($campo->tipo_de_campo == 3) {
          if ($campoExtraGrupo) {
            $json_opciones_campo = json_decode($campo->opciones_select);

            foreach ($json_opciones_campo as $opcion) {
              if ($opcion->value == $campoExtraGrupo->pivot->valor) {
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

          if (isset($campoExtraGrupo)) {
            $campo_usuario_opciones_seleccionadas = json_decode($campoExtraGrupo->pivot->valor);
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

      fputcsv($archivo, $fila, ';');
    }

    // Genera el archivo
    fclose($archivo);

    return Redirect::back()->with(
      'success',
      'El informe fue generado con éxito, <a href="' . Storage::url($rutaArchivo) . '" class=" link-success fw-bold" download="' . $nombreArchivo . '.csv"> descargalo aquí</a>'
    );
  }

  public function nuevo()
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('grupos.subitem_nuevo_grupo');

    $configuracion = Configuracion::find(1);
    $tipoGrupos = TipoGrupo::orderBy('orden', 'asc')->get();
    $tiposDeVivienda = TipoVivienda::orderBy('nombre', 'asc')->get();
    $camposExtras = CampoExtraGrupo::where('visible', '=', true)->get();

    return view('contenido.paginas.grupos.nuevo', [
      'tipoGrupos' => $tipoGrupos,
      'rolActivo' => $rolActivo,
      'configuracion' => $configuracion,
      'tiposDeVivienda' => $tiposDeVivienda,
      'camposExtras' => $camposExtras
    ]);
  }

  public function crear(Request $request)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $configuracion = Configuracion::find(1);

    // Validación
    $validacion = [];

    //nombre
    if ($configuracion->habilitar_nombre_grupo) {
      $validarNombre = ['max:100'];
      $configuracion->nombre_grupo_obligatorio ? array_push($validarNombre, 'required') : '';
      $validacion = array_merge($validacion, ['nombre' => $validarNombre]);
    }

    //  tipo_de_grupo
    if ($configuracion->habilitar_tipo_grupo) {
      $validarTipoGrupo = [];
      $configuracion->tipo_grupo_obligatorio ? array_push($validarTipoGrupo, 'required') : '';
      $validacion = array_merge($validacion, ['tipo_de_grupo' => $validarTipoGrupo]);
    }

    // fecha
    if ($configuracion->habilitar_fecha_creacion_grupo) {
      $validarFecha = [];
      $configuracion->fecha_creacion_grupo_obligatorio ? array_push($validarFecha, 'required') : '';
      $validacion = array_merge($validacion, ['fecha' => $validarFecha]);
    }

    // Tiene AMO
    if ($configuracion->version == 2)
      $validacion = array_merge($validacion, ['contiene_amo' => []]);


    // telefono
    if ($configuracion->habilitar_telefono_grupo) {
      $validarTelefono = [];
      $configuracion->telefono_grupo_obligatorio ? array_push($validarTelefono, 'required') : '';
      $validacion = array_merge($validacion, ['teléfono' => $validarTelefono]);
    }

    // tipo de vivienda
    if ($configuracion->habilitar_tipo_vivienda_grupo) {
      $validarTipoVivienda = [];
      $configuracion->tipo_vivienda_grupo_obligatorio ? array_push($validarTipoVivienda, 'required') : '';
      $validacion = array_merge($validacion, ['tipo_de_vivienda' => $validarTipoVivienda]);
    }

    // direccion
    if ($configuracion->habilitar_direccion_grupo) {
      $validarDireccion = [];
      $configuracion->direccion_grupo_obligatorio ? array_push($validarDireccion, 'required') : '';
      $validacion = array_merge($validacion, ['dirección' => $validarDireccion]);
    }

    // campo_opcional
    if ($configuracion->habilitar_campo_opcional1_grupo) {
      $validarCampoOpcional = [];
      $configuracion->campo_opcional1_obligatorio ? array_push($validarCampoOpcional, 'required') : '';
      $validacion = array_merge($validacion, ['adiccional' => $validarCampoOpcional]);
    }

    // dia de reunion
    if ($configuracion->habilitar_dia_reunion_grupo) {
      $validardiaReunion = [];
      $configuracion->dia_reunion_grupo_obligatorio ? array_push($validardiaReunion, 'required') : '';
      $validacion = array_merge($validacion, ['día_de_reunión' => $validardiaReunion]);
    }
    // hora de reunion
    if ($configuracion->habilitar_hora_reunion_grupo) {
      $validarHoraReunion = [];
      $configuracion->habilitar_hora_reunion_grupo ? array_push($validarHoraReunion, 'required') : '';
      $validacion = array_merge($validacion, ['hora_de_reunión' => $validarHoraReunion]);
    }

    /// seccion comprobacion campos extras
    if ($configuracion->visible_seccion_campos_extra_grupo == TRUE && $rolActivo->hasPermissionTo('grupos.visible_seccion_campos_extra_grupo')) {
      $camposExtras = CampoExtraGrupo::where('visible', '=', true)->get();

      foreach ($camposExtras as $campoExtra) {
        $validarCampoExtra = [];
        $campoExtra->required ? array_push($validarCampoExtra, 'required') : '';
        $validacion = array_merge($validacion, [$campoExtra->class_id => $validarCampoExtra]);
      }
    }

    // Validacion de datos
    $request->validate($validacion);

    $grupo = new Grupo;
    $grupo->nombre =  $request->nombre;
    $grupo->telefono = $request->teléfono;
    $grupo->direccion = $request->dirección;
    $grupo->barrio_id = $request->barrio_id ? $request->barrio_id : null;
    $grupo->barrio_auxiliar = $request->barrio_auxiliar;
    $grupo->tipo_vivienda_id = $request->tipo_de_vivienda;
    $grupo->tipo_grupo_id = $request->tipo_de_grupo;
    $grupo->rhema = $request->adiccional;
    $grupo->dia = $request->día_de_reunión;
    $grupo->hora = $request->hora_de_reunión;
    $grupo->contiene_amo = $request->amo ? TRUE : FALSE;
    $grupo->fecha_apertura = $request->fecha;
    $grupo->inactivo = 0;
    $grupo->dado_baja = 0;
    $grupo->usuario_creacion_id = auth()->user()->id;
    $grupo->rol_de_creacion_id = $rolActivo->id;
    $grupo->portada = 'default.png';
    $grupo->save();

    $grupo->indice_grafico_ministerial = $grupo->id;
    $grupo->save();
    $grupo->asignarSede();

    /// esta sección es para el guardado de los campos extra
    if ($configuracion->visible_seccion_campos_extra_grupo == TRUE) {
      $camposExtras = CampoExtraGrupo::where('visible', '=', true)->get();

      foreach ($camposExtras as $campo) {
        if ($campo->tipo_de_campo != 4)
          $grupo->camposExtras()->attach($campo->id, array('valor' => ucwords(mb_strtolower($request[$campo->class_id]))));
        else
          $grupo->camposExtras()->attach($campo->id, array('valor' => (json_encode($request[$campo->class_id]))));
      }
    }

    if ($grupo->save()) {

      // AÑADO LA PORTADA
      if ($request->foto) {
        if ($configuracion->version == 1) {
          $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/grupos/');
          !is_dir($path) && mkdir($path, 0777, true);

          $imagenPartes = explode(';base64,', $request->foto);
          $imagenBase64 = base64_decode($imagenPartes[1]);
          $nombreFoto = 'grupo' . $grupo->id . '.png';
          $imagenPath = $path . $nombreFoto;
          file_put_contents($imagenPath, $imagenBase64);
          $grupo->portada = $nombreFoto;
          $grupo->save();
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

    return back()->with('success', "El grupo <b>" . $grupo->nombre . "</b> fue creado con éxito.");
    /*return Redirect::to('/grupos/anadir-lideres/'.$grupo->id)->with(
			array(
				'status' => 'ok_new_grupo',
				'id_nuevo' => $grupo->id,
				'nombre_nuevo' => $grupo->nombre,
				)
		);*/
  }

  public function dashboard(Request $request)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('grupos.subitem_dashboard');

    $rangoFechas = $request->rango_fechas;
    $fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
    $fechaFin = Carbon::now()->format('Y-m-d');

    if ($rangoFechas) {
      $fechas = explode(' a ', $rangoFechas);
      // Ajustar fecha inicio al Lunes de esa semana
      $fechaInicio = Carbon::parse($fechas[0])->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
      
      // Ajustar fecha fin al Domingo de esa semana
      $fechaRawFin = isset($fechas[1]) ? $fechas[1] : $fechas[0];
      $fechaFin = Carbon::parse($fechaRawFin)->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');
    } else {
        // Si no hay request, construimos el string del rango por defecto para la vista
        $rangoFechas = $fechaInicio . ' a ' . $fechaFin;
    }
    
    $tiposGrupo = TipoGrupo::orderBy('nombre', 'asc')->get();
    
    if ($request->has('filtro_tipo_grupo')) {
        $tiposSeleccionados = $request->filtro_tipo_grupo;
    } else {
        $tiposSeleccionados = $tiposGrupo->where('tipo_evangelistico', true)->pluck('id')->toArray();
    }

    $bloques = BloqueDashboardConsolidacion::orderBy('nombre', 'asc')->get();

    if ($request->filled('filtro_bloques')) {
        $bloquesSeleccionados = [$request->filtro_bloques];
    } else {
        $bloquesSeleccionados = $bloques->pluck('id')->toArray();
    }
    
    if ($request->filled('filtro_sedes')) {
        $sedesSeleccionadas = [$request->filtro_sedes];
    } else {
        if ($request->filled('filtro_bloques')) {
             $sedesSeleccionadas = $bloques->where('id', $request->filtro_bloques)->first()->sedes->pluck('id')->toArray();
        } else {
            $sedesSeleccionadas = $bloques->pluck('sedes')->flatten()->pluck('id')->unique()->toArray();
        }
    }

    // Obtener estadísticas usando el método helper
    $stats = $this->getDashboardStats($fechaInicio, $fechaFin, $tiposSeleccionados, $bloquesSeleccionados, $sedesSeleccionadas);

    return view('contenido.paginas.grupos.dashboard', [
      'rolActivo' => $rolActivo,
      'fechaInicio' => $fechaInicio,
      'fechaFin' => $fechaFin,
      'rangoFechas' => $rangoFechas,
      'tiposGrupo' => $tiposGrupo,
      'tiposSeleccionados' => $tiposSeleccionados,
      'bloques' => $stats['bloques'], // Bloques procesados con datos
      'bloquesSeleccionados' => $bloquesSeleccionados,
      'sedesSeleccionadas' => $sedesSeleccionadas,
      'totalGrupos' => $stats['totalGrupos'],
      'gruposNuevos' => $stats['gruposNuevos'],
      'gruposBaja' => $stats['gruposBaja'],
      'gruposInactivos' => $stats['gruposInactivos'],
      'datosGraficaTipos' => $stats['datosGraficaTipos'],
      'bloquesEstadisticas' => $stats['bloquesEstadisticas']
    ]);
  }

  public function comparativo(Request $request)
  {
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
      // Reutilizamos permiso
      //$rolActivo->verificacionDelPermiso('grupos.subitem_dashboard'); 

      $tiposGrupo = TipoGrupo::orderBy('nombre', 'asc')->get();
      
      // Filtros comunes
      if ($request->has('filtro_tipo_grupo')) {
          $tiposSeleccionados = $request->filtro_tipo_grupo;
      } else {
          $tiposSeleccionados = $tiposGrupo->where('tipo_evangelistico', true)->pluck('id')->toArray();
      }
      
      $bloques = BloqueDashboardConsolidacion::orderBy('nombre', 'asc')->get();

      if ($request->filled('filtro_bloques')) {
          $bloquesSeleccionados = [$request->filtro_bloques];
      } else {
          $bloquesSeleccionados = $bloques->pluck('id')->toArray();
      }
      
      if ($request->filled('filtro_sedes')) {
          $sedesSeleccionadas = [$request->filtro_sedes];
      } else {
          if ($request->filled('filtro_bloques')) {
              $sedesSeleccionadas = $bloques->where('id', $request->filtro_bloques)->first()->sedes->pluck('id')->toArray();
          } else {
              $sedesSeleccionadas = $bloques->pluck('sedes')->flatten()->pluck('id')->unique()->toArray();
          }
      }

      // Rango A
      $rangoA = $request->rango_fechas_a;
      $fechaInicioA = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
      $fechaFinA = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
      
      if ($rangoA) {
          $fechas = explode(' a ', $rangoA);
          $fechaInicioA = Carbon::parse($fechas[0])->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
          $fechaRawFin = isset($fechas[1]) ? $fechas[1] : $fechas[0];
          $fechaFinA = Carbon::parse($fechaRawFin)->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');
      } else {
           $rangoA = $fechaInicioA . ' a ' . $fechaFinA;
      }

      // Rango B
      $rangoB = $request->rango_fechas_b;
      $fechaInicioB = Carbon::now()->startOfMonth()->format('Y-m-d');
      $fechaFinB = Carbon::now()->format('Y-m-d');

      if ($rangoB) {
          $fechas = explode(' a ', $rangoB);
          $fechaInicioB = Carbon::parse($fechas[0])->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
          $fechaRawFin = isset($fechas[1]) ? $fechas[1] : $fechas[0];
          $fechaFinB = Carbon::parse($fechaRawFin)->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');
      } else {
          $rangoB = $fechaInicioB . ' a ' . $fechaFinB;
      }

      // Obtener Estadísticas para ambos rangos
      $statsA = $this->getDashboardStats($fechaInicioA, $fechaFinA, $tiposSeleccionados, $bloquesSeleccionados, $sedesSeleccionadas);
      $statsB = $this->getDashboardStats($fechaInicioB, $fechaFinB, $tiposSeleccionados, $bloquesSeleccionados, $sedesSeleccionadas);

      return view('contenido.paginas.grupos.comparativo', [
          'rolActivo' => $rolActivo,
          'tiposGrupo' => $tiposGrupo,
          'bloques' => $bloques,
          
          'tiposSeleccionados' => $tiposSeleccionados,
          'bloquesSeleccionados' => $bloquesSeleccionados,
          'sedesSeleccionadas' => $sedesSeleccionadas,
          
          'rangoA' => $rangoA,
          'rangoB' => $rangoB,
          'statsA' => $statsA,
          'statsB' => $statsB
      ]);
  }

  private function getDashboardStats($fechaInicio, $fechaFin, $tiposSeleccionados, $bloquesSeleccionados, $sedesSeleccionadas)
  {
        // 1. Bloques y Sedes con conteo de grupos activos (Histórico)
        $bloques = BloqueDashboardConsolidacion::with(['sedes' => function($query) use ($tiposSeleccionados, $fechaInicio, $fechaFin) {
            $query->withCount(['grupos as grupos_activos_count' => function($q) use ($tiposSeleccionados, $fechaFin) {
                $q->where('fecha_apertura', '<=', $fechaFin);
                $q->where(function ($query) use ($fechaFin) {
                    $query->whereRaw('
                        (SELECT dado_baja FROM reportes_grupo_bajas_altas 
                        WHERE grupo_id = grupos.id AND fecha <= ? 
                        ORDER BY id DESC LIMIT 1) IS NULL OR 
                        (SELECT dado_baja FROM reportes_grupo_bajas_altas 
                        WHERE grupo_id = grupos.id AND fecha <= ? 
                        ORDER BY id DESC LIMIT 1) IS FALSE
                    ', [$fechaFin, $fechaFin]);
                });
                if (!empty($tiposSeleccionados)) {
                    $q->whereIn('tipo_grupo_id', $tiposSeleccionados);
                }
            }]);
        }])->orderBy('nombre', 'asc')->get();

        // 2. Semanas del periodo
        $periodo = CarbonPeriod::create($fechaInicio, '1 week', $fechaFin);
        $semanas = [];
        foreach ($periodo as $date) {
            $semanas[] = $date->format('o') . '-W' . $date->format('W');
        }

        // 3. Procesar datos por Sede (Gráficas y Asistencias)
        foreach ($bloques as $bloque) {
            foreach ($bloque->sedes as $sede) {
                $datosSemana = [];
                foreach ($semanas as $semana) {
                    sscanf($semana, "%d-W%d", $year, $week);
                    $fechaSemana = Carbon::now()->setISODate($year, $week);
                    $inicioSemana = $fechaSemana->copy()->startOfWeek()->format('Y-m-d');
                    $finSemana = $fechaSemana->copy()->endOfWeek()->format('Y-m-d');

                    // Reportes de la semana
                    $queryReportes = ReporteGrupo::whereBetween('fecha', [$inicioSemana, $finSemana])
                                                ->where('sede_id', $sede->id);
                    
                    if (!empty($tiposSeleccionados)) {
                        $queryReportes->whereHas('grupo', function($q) use ($tiposSeleccionados) {
                            $q->whereIn('tipo_grupo_id', $tiposSeleccionados);
                        });
                    }

                    $reportes = $queryReportes->get();
                    $realizados = $reportes->where('no_reporte', false)->count();
                    $noRealizados = $reportes->where('no_reporte', true)->count();

                    // Grupos Activos Históricos en esa semana
                    $queryGruposActivosSemana = Grupo::where('fecha_apertura', '<=', $finSemana)
                        ->where(function ($query) use ($finSemana) {
                            $query->whereRaw('
                                (SELECT dado_baja FROM reportes_grupo_bajas_altas 
                                WHERE grupo_id = grupos.id AND fecha <= ? 
                                ORDER BY id DESC LIMIT 1) IS NULL OR 
                                (SELECT dado_baja FROM reportes_grupo_bajas_altas 
                                WHERE grupo_id = grupos.id AND fecha <= ? 
                                ORDER BY id DESC LIMIT 1) IS FALSE
                            ', [$finSemana, $finSemana]);
                        })
                        ->where(function ($query) use ($sede, $finSemana) {
                            $query->whereRaw('
                                COALESCE(
                                    (SELECT sede_id_nuevo FROM bitacora_sedes_del_grupo 
                                    WHERE grupo_id = grupos.id AND created_at <= ? 
                                    ORDER BY id DESC LIMIT 1), 
                                    sede_id
                                ) = ?
                            ', [$finSemana . ' 23:59:59', $sede->id]);
                        });

                    if (!empty($tiposSeleccionados)) {
                        $queryGruposActivosSemana->where(function ($query) use ($tiposSeleccionados, $finSemana) {
                            $placeholders = implode(',', array_fill(0, count($tiposSeleccionados), '?'));
                            $query->whereRaw('
                                COALESCE(
                                    (SELECT tipo_grupo_id_nuevo FROM bitacora_tipos_grupo 
                                    WHERE grupo_id = grupos.id AND created_at <= ? 
                                    ORDER BY id DESC LIMIT 1), 
                                    tipo_grupo_id
                                ) IN (' . $placeholders . ')
                            ', array_merge([$finSemana . ' 23:59:59'], $tiposSeleccionados));
                        });
                    }

                    $totalActivosSemana = $queryGruposActivosSemana->count();
                    $noReportados = max(0, $totalActivosSemana - ($realizados + $noRealizados));

                    $inicioSemanaCarbon = Carbon::parse($inicioSemana);
                    $finSemanaCarbon = Carbon::parse($finSemana);
                    $labelRango = $inicioSemanaCarbon->translatedFormat('d M') . ' - ' . $finSemanaCarbon->translatedFormat('d M');

                    $datosSemana[] = [
                        'semana' => $finSemanaCarbon->format('y-m-d'),
                        'label_rango' => $labelRango,
                        'realizados' => $realizados,
                        'no_realizados' => $noRealizados,
                        'no_reportados' => $noReportados
                    ];
                }
                $sede->datos_grafica = $datosSemana;

                // Estadísticas de Asistencia por Sede
                $statsAsistencia = [];
                $tiposAsistencia = \App\Models\BloqueClasificacionAsistente::with(['clasificaciones'])->get(); 
                
                foreach ($tiposAsistencia as $tipo) {
                    $clasificacionesIds = $tipo->clasificaciones->pluck('id');
                    
                    $totalAsistentesSede = DB::table('clasificacion_asistente_reporte_grupo')
                        ->join('reporte_grupos', 'clasificacion_asistente_reporte_grupo.reporte_grupo_id', '=', 'reporte_grupos.id')
                        ->join('grupos', 'reporte_grupos.grupo_id', '=', 'grupos.id')
                        ->where('reporte_grupos.sede_id', $sede->id)
                        ->whereBetween('reporte_grupos.fecha', [$fechaInicio, $fechaFin])
                        ->whereIn('clasificacion_asistente_reporte_grupo.clasificacion_asistente_id', $clasificacionesIds);

                    if (!empty($tiposSeleccionados)) {
                        $totalAsistentesSede->whereIn('grupos.tipo_grupo_id', $tiposSeleccionados);
                    }
                    
                    $valor = $totalAsistentesSede->sum('clasificacion_asistente_reporte_grupo.cantidad');

                    if ($tipo->tipo_calculo == 'promedio') {
                        $semanasCount = max(1, count($semanas));
                        $valor = round($valor / $semanasCount);
                    }
                    
                    $statsAsistencia[] = (object) [
                        'nombre' => $tipo->nombre,
                        'valor' => $valor,
                        'tipo_calculo' => $tipo->tipo_calculo
                    ];
                }
                $sede->estadisticas_asistencia = $statsAsistencia;

                // --- CÁLCULO DE KPIS POR SEDE ---
                // Se ejecuta una vez por sede para obtener los totales del periodo completo.
                
                // A. Total Grupos (Final del periodo) -> Ya viene en $sede->grupos_activos_count
                $sede->kpi_total = $sede->grupos_activos_count;

                // B. Nuevos (En el periodo)
                $qNuevos = Grupo::whereBetween('fecha_apertura', [$fechaInicio, $fechaFin])
                                ->where('sede_id', $sede->id);
                if (!empty($tiposSeleccionados)) {
                    $qNuevos->whereIn('tipo_grupo_id', $tiposSeleccionados);
                }
                $sede->kpi_nuevos = $qNuevos->count();

                // C. Bajas (Acumulado histórico al final del periodo)
                $sede->kpi_bajas = Grupo::where('fecha_apertura', '<=', $fechaFin)
                    ->where(function ($query) use ($fechaFin) {
                        $query->whereRaw('
                            (SELECT dado_baja FROM reportes_grupo_bajas_altas 
                            WHERE grupo_id = grupos.id AND fecha <= ? 
                            ORDER BY id DESC LIMIT 1) IS TRUE
                        ', [$fechaFin]);
                    })
                    ->where(function ($query) use ($sede, $fechaFin) {
                        $query->whereRaw('
                            COALESCE(
                                (SELECT sede_id_nuevo FROM bitacora_sedes_del_grupo 
                                WHERE grupo_id = grupos.id AND created_at <= ? 
                                ORDER BY id DESC LIMIT 1), 
                                sede_id
                            ) = ?
                        ', [$fechaFin . " 23:59:59", $sede->id]);
                    })
                    ->where(function ($query) use ($tiposSeleccionados, $fechaFin) {
                        if (!empty($tiposSeleccionados)) {
                            $placeholders = implode(',', array_fill(0, count($tiposSeleccionados), '?'));
                            $query->whereRaw('
                                COALESCE(
                                    (SELECT tipo_grupo_id_nuevo FROM bitacora_tipos_grupo 
                                    WHERE grupo_id = grupos.id AND created_at <= ? 
                                    ORDER BY id DESC LIMIT 1), 
                                    tipo_grupo_id
                                ) IN (' . $placeholders . ')
                            ', array_merge([$fechaFin . ' 23:59:59'], $tiposSeleccionados));
                        }
                    })
                    ->count();

                // D. Inactivos (Activos históricos de esa sede sin reportes en el periodo)
                $qActivosHistoricosSede = Grupo::where('fecha_apertura', '<=', $fechaFin)
                    ->where(function ($query) use ($fechaFin) {
                        $query->whereRaw('
                            (SELECT dado_baja FROM reportes_grupo_bajas_altas 
                            WHERE grupo_id = grupos.id AND fecha <= ? 
                            ORDER BY id DESC LIMIT 1) IS NULL OR 
                            (SELECT dado_baja FROM reportes_grupo_bajas_altas 
                            WHERE grupo_id = grupos.id AND fecha <= ? 
                            ORDER BY id DESC LIMIT 1) IS FALSE
                        ', [$fechaFin, $fechaFin]);
                    })
                    ->where(function ($query) use ($sede, $fechaFin) {
                        $query->whereRaw('
                            COALESCE(
                                (SELECT sede_id_nuevo FROM bitacora_sedes_del_grupo 
                                WHERE grupo_id = grupos.id AND created_at <= ? 
                                ORDER BY id DESC LIMIT 1), 
                                sede_id
                            ) = ?
                        ', [$fechaFin . " 23:59:59", $sede->id]);
                    });
                    
                if (!empty($tiposSeleccionados)) {
                    $qActivosHistoricosSede->where(function ($query) use ($tiposSeleccionados, $fechaFin) {
                        $placeholders = implode(',', array_fill(0, count($tiposSeleccionados), '?'));
                        $query->whereRaw('
                            COALESCE(
                                (SELECT tipo_grupo_id_nuevo FROM bitacora_tipos_grupo 
                                WHERE grupo_id = grupos.id AND created_at <= ? 
                                ORDER BY id DESC LIMIT 1), 
                                tipo_grupo_id
                            ) IN (' . $placeholders . ')
                        ', array_merge([$fechaFin . ' 23:59:59'], $tiposSeleccionados));
                    });
                }

                $sede->kpi_inactivos = $qActivosHistoricosSede->whereDoesntHave('reportes', function($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
                })->count();

            }
        }

        // 4. Query Base Histórica Global
        $queryGruposHistoricos = Grupo::where('fecha_apertura', '<=', $fechaFin)
            ->where(function ($query) use ($fechaFin) {
                $query->whereRaw('
                    (SELECT dado_baja FROM reportes_grupo_bajas_altas 
                    WHERE grupo_id = grupos.id AND fecha <= ? 
                    ORDER BY id DESC LIMIT 1) IS NULL OR 
                    (SELECT dado_baja FROM reportes_grupo_bajas_altas 
                    WHERE grupo_id = grupos.id AND fecha <= ? 
                    ORDER BY id DESC LIMIT 1) IS FALSE
                ', [$fechaFin, $fechaFin]);
            })
            ->where(function ($query) use ($sedesSeleccionadas, $fechaFin) {
                $placeholders = implode(',', array_fill(0, count($sedesSeleccionadas), '?'));
                $query->whereRaw('
                    COALESCE(
                        (SELECT sede_id_nuevo FROM bitacora_sedes_del_grupo 
                        WHERE grupo_id = grupos.id AND created_at <= ? 
                        ORDER BY id DESC LIMIT 1), 
                        sede_id
                    ) IN (' . $placeholders . ')
                ', array_merge([$fechaFin . " 23:59:59"], $sedesSeleccionadas));
            })
            ->where(function ($query) use ($tiposSeleccionados, $fechaFin) {
                if (!empty($tiposSeleccionados)) {
                    $placeholders = implode(',', array_fill(0, count($tiposSeleccionados), '?'));
                    $query->whereRaw('
                        COALESCE(
                            (SELECT tipo_grupo_id_nuevo FROM bitacora_tipos_grupo 
                            WHERE grupo_id = grupos.id AND created_at <= ? 
                            ORDER BY id DESC LIMIT 1), 
                            tipo_grupo_id
                        ) IN (' . $placeholders . ')
                    ', array_merge([$fechaFin . ' 23:59:59'], $tiposSeleccionados));
                }
            });

        // 5. KPIs Globales
        $totalGrupos = (clone $queryGruposHistoricos)->count();

        $queryGruposNuevos = Grupo::whereBetween('fecha_apertura', [$fechaInicio, $fechaFin])
                                ->whereIn('sede_id', $sedesSeleccionadas);
        if (!empty($tiposSeleccionados)) {
            $queryGruposNuevos->whereIn('tipo_grupo_id', $tiposSeleccionados);
        }
        $gruposNuevos = $queryGruposNuevos->count();

        $gruposBaja = Grupo::where('fecha_apertura', '<=', $fechaFin)
            ->where(function ($query) use ($fechaFin) {
                $query->whereRaw('
                    (SELECT dado_baja FROM reportes_grupo_bajas_altas 
                    WHERE grupo_id = grupos.id AND fecha <= ? 
                    ORDER BY id DESC LIMIT 1) IS TRUE
                ', [$fechaFin]);
            })
            ->where(function ($query) use ($sedesSeleccionadas, $fechaFin) {
                $placeholders = implode(',', array_fill(0, count($sedesSeleccionadas), '?'));
                $query->whereRaw('
                    COALESCE(
                        (SELECT sede_id_nuevo FROM bitacora_sedes_del_grupo 
                        WHERE grupo_id = grupos.id AND created_at <= ? 
                        ORDER BY id DESC LIMIT 1), 
                        sede_id
                    ) IN (' . $placeholders . ')
                ', array_merge([$fechaFin . " 23:59:59"], $sedesSeleccionadas));
            })
            ->where(function ($query) use ($tiposSeleccionados, $fechaFin) {
                if (!empty($tiposSeleccionados)) {
                    $placeholders = implode(',', array_fill(0, count($tiposSeleccionados), '?'));
                    $query->whereRaw('
                        COALESCE(
                            (SELECT tipo_grupo_id_nuevo FROM bitacora_tipos_grupo 
                            WHERE grupo_id = grupos.id AND created_at <= ? 
                            ORDER BY id DESC LIMIT 1), 
                            tipo_grupo_id
                        ) IN (' . $placeholders . ')
                    ', array_merge([$fechaFin . ' 23:59:59'], $tiposSeleccionados));
                }
            })
            ->count();

        $gruposInactivos = (clone $queryGruposHistoricos)->whereDoesntHave('reportes', function($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        })->count();

        // 6. Gráfica de Tipos
        $tiposGrupoMap = TipoGrupo::all()->keyBy('id');
        $datosGraficaTipos = (clone $queryGruposHistoricos)
            ->select(DB::raw('
                COALESCE(
                    (SELECT tipo_grupo_id_nuevo FROM bitacora_tipos_grupo 
                    WHERE grupo_id = grupos.id AND created_at <= \'' . $fechaFin . ' 23:59:59\' 
                    ORDER BY id DESC LIMIT 1), 
                    tipo_grupo_id
                ) as tipo_historico_id
            '), DB::raw('count(*) as total'))
            ->groupBy('tipo_historico_id')
            ->get()
            ->map(function ($item) use ($tiposGrupoMap) {
                $tipo = $tiposGrupoMap->get($item->tipo_historico_id);
                return [
                    'label' => $tipo->nombre ?? 'Desconocido',
                    'value' => $item->total
                ];
            });

        // 7. KPIs Asistencia Global (Bloques)
        $bloquesEstadisticas = BloqueClasificacionAsistente::with(['clasificaciones'])->get();
        $gruposActivosIds = (clone $queryGruposHistoricos)->pluck('id');

        foreach ($bloquesEstadisticas as $bloque) {
            $clasificacionesIds = $bloque->clasificaciones->pluck('id');
            $totalAsistentes = DB::table('clasificacion_asistente_reporte_grupo')
                ->join('reporte_grupos', 'clasificacion_asistente_reporte_grupo.reporte_grupo_id', '=', 'reporte_grupos.id')
                ->whereIn('reporte_grupos.grupo_id', $gruposActivosIds)
                ->whereBetween('reporte_grupos.fecha', [$fechaInicio, $fechaFin])
                ->whereIn('clasificacion_asistente_reporte_grupo.clasificacion_asistente_id', $clasificacionesIds)
                ->sum('clasificacion_asistente_reporte_grupo.cantidad');

            if ($bloque->tipo_calculo == 'promedio') {
                $semanasCount = max(1, count($semanas));
                $bloque->valor = round($totalAsistentes / $semanasCount);
                $bloque->etiqueta_tipo = $bloque->tipo_calculo;
            } else {
                $bloque->valor = $totalAsistentes;
                $bloque->etiqueta_tipo = $bloque->tipo_calculo;
            }
        }

        return [
            'totalGrupos' => $totalGrupos,
            'gruposNuevos' => $gruposNuevos,
            'gruposBaja' => $gruposBaja,
            'gruposInactivos' => $gruposInactivos,
            'datosGraficaTipos' => $datosGraficaTipos,
            'bloquesEstadisticas' => $bloquesEstadisticas,
            'bloques' => $bloques
        ];
  }

  public function detalleKpi(Request $request)
  {
      $datos = $this->getDatosDetalleKpi($request);
      
      $grupos = $datos['query']->paginate(20)->withQueryString();

      return view('contenido.paginas.grupos.detalle_kpi', [
          'grupos' => $grupos,
          'kpi' => $datos['kpi'],
          'mapaSedes' => $datos['mapaSedes'],
          'mapaTipos' => $datos['mapaTipos'],
          'rangoFechas' => $datos['rangoFechas'],
          'tiposSeleccionados' => $datos['tiposSeleccionados'],
          'sedesSeleccionadas' => $datos['sedesSeleccionadas']
      ]);
  }

  public function exportarDetalleKpi(Request $request) 
  {
      $datos = $this->getDatosDetalleKpi($request);
      $grupos = $datos['query']->get(); // Obtener todos los resultados sin paginar

      return \Maatwebsite\Excel\Facades\Excel::download(
          new \App\Exports\DetalleGruposKpiExport(
              $grupos, 
              $datos['mapaSedes'], 
              $datos['mapaTipos'], 
              $datos['kpi']
          ), 
          'detalle_kpi_grupos.xlsx'
      );
  }

  private function getDatosDetalleKpi(Request $request)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    // Reutilizamos permiso de dashboard o visualización general
    //$rolActivo->verificacionDelPermiso('grupos.subitem_dashboard'); 

    $kpi = $request->kpi ?? 'total';
    $search = $request->buscar;

    $rangoFechas = $request->rango_fechas;
    $fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
    $fechaFin = Carbon::now()->format('Y-m-d');

    if ($rangoFechas) {
      $fechas = explode(' a ', $rangoFechas);
      // Ajustar fecha inicio al Lunes de esa semana (Igual que dashboard)
      $fechaInicio = Carbon::parse($fechas[0])->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
      
      // Ajustar fecha fin al Domingo de esa semana
      $fechaRawFin = isset($fechas[1]) ? $fechas[1] : $fechas[0];
      $fechaFin = Carbon::parse($fechaRawFin)->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');
    } else {
        // Fallback si no hay fechas, igual que dashboard
        $rangoFechas = $fechaInicio . ' a ' . $fechaFin; 
    }

    $tiposGrupo = TipoGrupo::orderBy('nombre', 'asc')->get();
    
    if ($request->has('filtro_tipo_grupo')) {
        $tiposSeleccionados = $request->filtro_tipo_grupo;
    } else {
        $tiposSeleccionados = $tiposGrupo->where('tipo_evangelistico', true)->pluck('id')->toArray();
    }

    $bloques = BloqueDashboardConsolidacion::orderBy('nombre', 'asc')->get();
    
    // Lógica Sedes (Igual que Dashboard)
    if ($request->filled('filtro_sedes')) {
        $sedesSeleccionadas = [$request->filtro_sedes];
    } else {
        if ($request->filled('filtro_bloques')) {
             $sedesSeleccionadas = $bloques->where('id', $request->filtro_bloques)->first()->sedes->pluck('id')->toArray();
        } else {
            $sedesSeleccionadas = $bloques->pluck('sedes')->flatten()->pluck('id')->unique()->toArray();
        }
    }

    // Query Base (Igual que Dashboard - Histórico)
    $queryGruposHistoricos = Grupo::where('fecha_apertura', '<=', $fechaFin)
        ->where(function ($query) use ($fechaFin) {
            $query->whereRaw('
                (SELECT dado_baja FROM reportes_grupo_bajas_altas 
                 WHERE grupo_id = grupos.id AND fecha <= ? 
                 ORDER BY id DESC LIMIT 1) IS NULL OR 
                (SELECT dado_baja FROM reportes_grupo_bajas_altas 
                 WHERE grupo_id = grupos.id AND fecha <= ? 
                 ORDER BY id DESC LIMIT 1) IS FALSE
            ', [$fechaFin, $fechaFin]);
        })
        ->where(function ($query) use ($sedesSeleccionadas, $fechaFin) {
            $placeholders = implode(',', array_fill(0, count($sedesSeleccionadas), '?'));
            $query->whereRaw('
                COALESCE(
                    (SELECT sede_id_nuevo FROM bitacora_sedes_del_grupo 
                     WHERE grupo_id = grupos.id AND created_at <= ? 
                     ORDER BY id DESC LIMIT 1), 
                    sede_id
                ) IN (' . $placeholders . ')
            ', array_merge([$fechaFin . " 23:59:59"], $sedesSeleccionadas));
        })
        ->where(function ($query) use ($tiposSeleccionados, $fechaFin) {
            if (!empty($tiposSeleccionados)) {
                $placeholders = implode(',', array_fill(0, count($tiposSeleccionados), '?'));
                $query->whereRaw('
                    COALESCE(
                        (SELECT tipo_grupo_id_nuevo FROM bitacora_tipos_grupo 
                         WHERE grupo_id = grupos.id AND created_at <= ? 
                         ORDER BY id DESC LIMIT 1), 
                        tipo_grupo_id
                    ) IN (' . $placeholders . ')
                ', array_merge([$fechaFin . ' 23:59:59'], $tiposSeleccionados));
            }
        });

    $query = null;

    // Preparar Mapas para la vista (para resolver nombres de IDs históricos)
    $mapaSedes = \App\Models\Sede::all()->keyBy('id');
    $mapaTipos = $tiposGrupo->keyBy('id');

    // Selección de columnas históricas
    $selectHistorico = [
        'grupos.*',
        DB::raw("
            COALESCE(
                (SELECT sede_id_nuevo FROM bitacora_sedes_del_grupo 
                 WHERE grupo_id = grupos.id AND created_at <= '$fechaFin 23:59:59' 
                 ORDER BY id DESC LIMIT 1), 
                sede_id
            ) as sede_historica_id
        "),
        DB::raw("
            COALESCE(
                (SELECT tipo_grupo_id_nuevo FROM bitacora_tipos_grupo 
                 WHERE grupo_id = grupos.id AND created_at <= '$fechaFin 23:59:59' 
                 ORDER BY id DESC LIMIT 1), 
                tipo_grupo_id
            ) as tipo_historico_id
        ")
    ];

    switch ($kpi) {
        case 'nuevos':
            $query = Grupo::select($selectHistorico)
                          ->whereBetween('fecha_apertura', [$fechaInicio, $fechaFin])
                          ->whereIn('sede_id', $sedesSeleccionadas);
            
            if (!empty($tiposSeleccionados)) {
                $query->whereIn('tipo_grupo_id', $tiposSeleccionados);
            }
            break;

        case 'bajas':
             $query = Grupo::select($selectHistorico)
                ->where('fecha_apertura', '<=', $fechaFin)
                ->where(function ($query) use ($fechaFin) {
                    $query->whereRaw('
                        (SELECT dado_baja FROM reportes_grupo_bajas_altas 
                         WHERE grupo_id = grupos.id AND fecha <= ? 
                         ORDER BY id DESC LIMIT 1) IS TRUE
                    ', [$fechaFin]);
                })
                ->where(function ($query) use ($sedesSeleccionadas, $fechaFin) {
                    $placeholders = implode(',', array_fill(0, count($sedesSeleccionadas), '?'));
                    $query->whereRaw('
                        COALESCE(
                            (SELECT sede_id_nuevo FROM bitacora_sedes_del_grupo 
                             WHERE grupo_id = grupos.id AND created_at <= ? 
                             ORDER BY id DESC LIMIT 1), 
                            sede_id
                        ) IN (' . $placeholders . ')
                    ', array_merge([$fechaFin . " 23:59:59"], $sedesSeleccionadas));
                })
                ->where(function ($query) use ($tiposSeleccionados, $fechaFin) {
                    if (!empty($tiposSeleccionados)) {
                        $placeholders = implode(',', array_fill(0, count($tiposSeleccionados), '?'));
                        $query->whereRaw('
                            COALESCE(
                                (SELECT tipo_grupo_id_nuevo FROM bitacora_tipos_grupo 
                                 WHERE grupo_id = grupos.id AND created_at <= ? 
                                 ORDER BY id DESC LIMIT 1), 
                                tipo_grupo_id
                            ) IN (' . $placeholders . ')
                        ', array_merge([$fechaFin . ' 23:59:59'], $tiposSeleccionados));
                    }
                });
            break;

        case 'inactivos':
             $baseInactivos = clone $queryGruposHistoricos;
             $query = $baseInactivos->select($selectHistorico)
                ->whereDoesntHave('reportes', function($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
                });
             break;

        case 'total':
        default:
             $baseTotal = clone $queryGruposHistoricos;
             $query = $baseTotal->select($selectHistorico);
             break;
    }

    if ($search) {
        // Búsqueda insensible a mayúsculas (y acentos según collation de BD)
        $query->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($search) . '%']);
    }

    return [
        'query' => $query,
        'kpi' => $kpi,
        'mapaSedes' => $mapaSedes,
        'mapaTipos' => $mapaTipos,
        'rangoFechas' => $rangoFechas,
        'tiposSeleccionados' => $tiposSeleccionados,
        'sedesSeleccionadas' => $sedesSeleccionadas
    ];
  }

  public function prototipo()
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $configuracion = Configuracion::find(1);
    $grupo = Grupo::find(4);

    $meses = Helpers::meses('largo');

    return view('contenido.paginas.grupos.perfil-prototipo', [
      'rolActivo' => $rolActivo,
      'configuracion' => $configuracion,
      'grupo' => $grupo,
      'meses' => $meses
    ]);
  }

  public function perfil(Grupo $grupo, User $encargado)
  {
    $grupos = null;
    if ($encargado)
      $grupos = $encargado->gruposEncargados;

    $configuracion = Configuracion::find(1);
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('grupos.opcion_ver_perfil_grupo');

    $encargados = $grupo->encargados()
      ->select('users.id', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'foto', 'tipo_usuario_id')
      ->with('tipoUsuario')
      ->get();

    $servidores = ServidorGrupo::where("grupo_id", "=", $grupo->id)
      ->leftJoin('users', 'user_id', '=', 'users.id')
      ->select('servidores_grupo.*', 'users.id as idUser', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'foto', 'tipo_usuario_id')
      ->get();

    $servidores = User::where('servidores_grupo.grupo_id', $grupo->id)
      ->leftJoin('servidores_grupo', 'users.id', '=', 'user_id')
      ->select('servidores_grupo.id as servidorId', 'users.id', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'foto', 'tipo_usuario_id')
      ->get();

    $servidores->map(function ($servidor) use ($grupo) {
      $servicios = $servidor->serviciosPrestadosEnGrupos($grupo->id)->pluck('nombre')->toArray();
      $servidor->servicios  = $servicios;
    });

    $dataUltimosReportes = [];
    $serieUltimosReportes = [];
    $idsUltimos10Reportes = $grupo->reportes()->orderBy('fecha', 'desc')->orderBy('fecha', 'asc')->take(10)->select('id')->pluck('id')->toArray();
    $ultimos10Reportes = ReporteGrupo::whereIn('id', $idsUltimos10Reportes)->select('id', 'tema', 'fecha', 'cantidad_asistencias')->get();

    $meses = Helpers::meses('corto');

    foreach ($ultimos10Reportes as $reporte) {
      $dataUltimosReportes[] = $reporte->cantidad_asistencias;
      $serieUltimosReportes[] = Carbon::parse($reporte->fecha)->day . '-' . $meses[Carbon::parse($reporte->fecha)->month - 1];
    }

    $dataUltimosMeses = [];
    $serieUltimosMeses = [];
    $mes = Carbon::now()->firstOfMonth()->month;
    $mesIni = Carbon::now()->firstOfMonth()->subMonth(5)->month;

    for ($i = 5; $i >= 0; $i--) {
      $fechaIni =  Carbon::now()->firstOfMonth()->subMonth($i)->format('Y-m-d');
      $fechaFin =  Carbon::now()->lastOfMonth()->subMonth($i)->format('Y-m-d');
      $serieUltimosMeses[] = $meses[Carbon::now()->firstOfMonth()->subMonth($i)->month - 1];
      $promedioMes =  $grupo->reportes()->where('fecha', '>=', $fechaIni)->where('fecha', '<=', $fechaFin)->avg('cantidad_asistencias');
      $dataUltimosMeses[] = $promedioMes;
    }


    $camposExtras = CampoExtraGrupo::where('visible', '=', true)->get();

    $camposExtras->map(function ($campoExtra) use ($grupo) {
      $grupoCampoExtra = $grupo->camposExtras()->where('campos_extra_grupo.id', $campoExtra->id)->first();

      if ($campoExtra->tipo_de_campo == 4) {
        $valor = [];

        if ($grupoCampoExtra) {
          foreach (json_decode($campoExtra->opciones_select) as $opcion) {
            if (in_array($opcion->value, json_decode($grupoCampoExtra->pivot->valor)))
              $valor[] = $opcion->nombre;
          }
        }

        $campoExtra->valor = count($valor) > 0 ? implode(",", $valor) : '';
      } elseif ($campoExtra->tipo_de_campo == 3) {
        $valor = '';
        if ($grupoCampoExtra) {
          foreach (json_decode($campoExtra->opciones_select) as $opcion) {
            if ($opcion->value == $grupoCampoExtra->pivot->valor)
              $valor = $opcion->nombre;
          }
        }
        $campoExtra->valor = $valor;
      } else {
        $campoExtra->valor = $grupoCampoExtra ? $grupoCampoExtra->pivot->valor : '';
      }
    });


    return view('contenido.paginas.grupos.perfil', [
      'configuracion' => $configuracion,
      'rolActivo' => $rolActivo,
      'grupo' => $grupo,
      'grupos' => $grupos,
      'encargado' => $encargado ? $encargado : null,
      'encargados' => $encargados,
      'servidores' => $servidores,
      'dataUltimosReportes' => $dataUltimosReportes,
      'serieUltimosReportes' => $serieUltimosReportes,
      'serieUltimosMeses' => $serieUltimosMeses,
      'dataUltimosMeses' => $dataUltimosMeses,
      'camposExtras' => $camposExtras,
      'ultimos10Reportes' => $ultimos10Reportes
    ]);
  }

  public function perfilEstadisticasGrupo(Request $request, Grupo $grupo, User $encargado)
  {

    $grupos = null;

    if ($encargado)
      $grupos = $encargado->gruposEncargados;


    $configuracion = Configuracion::find(1);
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('grupos.opcion_ver_perfil_grupo');

    $tipoUsuarioDefault = null;
    $personasTipoDefault = null;
    $personasInactivas = null;

    $fechaMaximaActividadGrupo = null;
    $indicadoresPortipoGrupo = [];

    $hoy = Carbon::now();
    $reportes = [];

    $meses = Helpers::meses('corto');

    // obtengo los rangos de fechas
    switch ($request->rango) {
      case "3m":
        $fechaInicio = Carbon::now()->subMonths(3)->startOfMonth();
        $fechaFin = Carbon::now()->subMonths(1)->endOfMonth();
        break;

      case "6m":
        $fechaInicio = Carbon::now()->subMonths(6)->startOfMonth();
        $fechaFin = Carbon::now()->subMonths(1)->endOfMonth();
        break;

      case "otroSemanas":
        list($añoIni, $semanaIni) = explode('-W', $request->semanaIni);
        $fechaInicio = Carbon::now()->setISODate($añoIni, $semanaIni)->startOfWeek();

        list($añoFin, $semanaFin) = explode('-W', $request->semanaFin);
        $fechaFin = Carbon::now()->setISODate($añoFin, $semanaFin)->endOfWeek();
        break;

      case "otroMeses":
        $fechaInicio = Carbon::createFromFormat('Y-m', $request->mesIni)->startOfMonth();
        $fechaFin = Carbon::createFromFormat('Y-m', $request->mesFin)->endOfMonth();
        break;

      default:
        $fechaInicio = Carbon::now()->subWeeks(4)->startOfWeek();
        $fechaFin = Carbon::now()->subWeeks(1)->endOfWeek();
    }

    // me aseguro que la fecha inicio sea lunes y que la fecha fin sea domingo, con el fin de obtener las semanas completas
    $fechaInicio = $fechaInicio->isMonday() ? $fechaInicio : $fechaInicio->next(Carbon::MONDAY);
    $fechaFin = $fechaFin->isSunday() ? $fechaFin : $fechaFin->next(Carbon::SUNDAY);


    // obtengo la cantidad de semanas del rango
    $cantidadSemanasRango = ceil($fechaInicio->diffInWeeks($fechaFin));
    $graficaTipo = $cantidadSemanasRango > 4 ? 'meses' : 'semanas';

    // doy formato a las fechas
    $fechaInicio = $fechaInicio->format('Y-m-d');
    $fechaFin = $fechaFin->format('Y-m-d');

    //return $fechaInicio."  ".$fechaFin;

    $clasificaciones = $grupo->tipoGrupo->clasificacionAsistentes()
      ->select('clasificaciones_asistentes.id', 'clasificaciones_asistentes.nombre')
      ->get();

    $reportesClasificacion = $grupo->reportes()
      ->whereIn('clasificacion_asistente_id', $clasificaciones->pluck('id')->toArray())
      ->whereBetween('fecha', [$fechaInicio, $fechaFin])
      ->leftJoin('clasificacion_asistente_reporte_grupo', 'reporte_grupos.id', '=', 'clasificacion_asistente_reporte_grupo.reporte_grupo_id')
      ->select('reporte_grupo_id', 'clasificacion_asistente_id', 'cantidad', 'fecha')
      ->get();

    //return $fechaInicio." --- ".$fechaFin;

    $reportes = $grupo->reportes()
      ->select('id', 'fecha', 'grupo_id', 'cantidad_asistencias')
      ->whereBetween('fecha', [$fechaInicio, $fechaFin])
      ->get();

    $tipoUsuarioDefault = TipoUsuario::where('default', true)->select('id', 'nombre', 'nombre_plural', 'icono')->first();

    $asistentes = $grupo->asistentes()
      ->select('users.id', 'tipo_usuario_id', 'foto', 'genero', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'ultimo_reporte_grupo', 'users.created_at')
      ->get();

    $personasTipoDefault = $asistentes->where('tipo_usuario_id', $tipoUsuarioDefault->id)->sortByDesc('created_at');
    $personasTipoDefault->map(function ($persona) use ($hoy) {
      $diasCreacion = round(Carbon::parse($persona->created_at)->diffInDays($hoy));
      $persona->diasCreacion =  "<b>" . Helpers::determinarTiempo($diasCreacion) . "</b>";;
    });

    $fechaMaximaActividadGrupo = Carbon::now()
      ->subDays($configuracion->tiempo_para_definir_inactivo_grupo)
      ->format('Y-m-d');

    $personasInactivas =  $asistentes->filter(function ($usuario) use ($fechaMaximaActividadGrupo) {
      return $usuario->ultimo_reporte_grupo < $fechaMaximaActividadGrupo || $usuario->ultimo_reporte_grupo == null;
    })->sortByDesc('ultimo_reporte_grupo');

    $personasInactivas->map(function ($persona) use ($hoy) {
      $diasInactivos = round(Carbon::parse($persona->ultimo_reporte_grupo)->diffInDays($hoy));
      $persona->inactividad =  "<b>" . Helpers::determinarTiempo($diasInactivos) . "</b>";
    });

    $clasificaciones->map(function ($clasificacion) use ($reportesClasificacion, $cantidadSemanasRango) {
      $clasificacion->promedio =  $reportesClasificacion->where('clasificacion_asistente_id', $clasificacion->id)->count() > 0
        ? round($reportesClasificacion->where('clasificacion_asistente_id', $clasificacion->id)->sum('cantidad') / $cantidadSemanasRango)
        : 0;
    });

    $personas = $grupo->asistentes()->select('users.id', 'fecha_nacimiento', 'genero', 'tipo_usuario_id', 'genero')->get();
    $personas->map(function ($persona) {
      $persona->edad =  $persona->edad();
    });

    // edades
    $rangoEdades = $configuracion->rangoEdad()->orderBy('id', 'asc')->get();
    $rangoEdades->map(function ($rango) use ($personas) {
      $rango->cantidad = $personas->where('edad', '>=', $rango->edad_minima)->where('edad', '<=', $rango->edad_maxima)->count();
    });

    $labelsRangoEdades = $rangoEdades->pluck('nombre')->toArray();
    $seriesRangoEdades = $rangoEdades->pluck('cantidad')->toArray();

    // Por sexo
    $tiposDeSexo = [];

    $cantidadMasculino = $personas->where('genero', 0)->count();
    $item = new stdClass();
    $item->nombre = 'Masculino';
    $item->cantidad = $cantidadMasculino;
    $tiposDeSexo[] = $item;

    $cantidadFemenino = $personas->where('genero', 1)->count();
    $item = new stdClass();
    $item->nombre = 'Femenino';
    $item->cantidad = $cantidadFemenino;
    $tiposDeSexo[] = $item;

    $labelsTiposSexos = ['Masculino', 'Femenino'];
    $seriesTiposSexos = [$cantidadMasculino, $cantidadFemenino];

    //Gráfica de crecimiento
    $graficaCrecimientoCategorias = [];
    $graficaCrecimientoDatos = [];

    if ($graficaTipo == "semanas") {
      $periodo = CarbonPeriod::create($fechaInicio, '1 week', $fechaFin);
      $bloquesPeriodos = [];
      foreach ($periodo as $fecha) {
        $inicioSemana = $fecha->copy();
        $finSemana = $fecha->copy()->endOfWeek();

        $graficaCrecimientoCategorias[] = $meses[$inicioSemana->month - 1] . " " . $inicioSemana->format('d') . "-" . $meses[$finSemana->month - 1] . " " . $finSemana->format('d');

        $sumatoriaMes =  $reportes->where('fecha', '>=', $inicioSemana->format('Y-m-d'))
          ->where('fecha', '<=', $finSemana->format('Y-m-d'))
          ->sum('cantidad_asistencias');

        $cantidadSemanasPeriodo = ceil($inicioSemana->diffInWeeks($finSemana));
        $graficaCrecimientoDatos[] = round($sumatoriaMes / $cantidadSemanasPeriodo);
      }
    } elseif ($graficaTipo == "meses") {
      $periodo = CarbonPeriod::create($fechaInicio, '1 month', $fechaFin);

      // Guarda los últimos 4 meses

      foreach ($periodo as $fecha) {
        $inicioMes = $fecha->copy()->startOfMonth();
        $finMes = $fecha->copy()->endOfMonth();

        $inicioMes = $inicioMes->isMonday() ? $inicioMes : $inicioMes->next(Carbon::MONDAY);
        $finMes = $finMes->isSunday() ?  $finMes :  $finMes->next(Carbon::SUNDAY);

        if (!Carbon::now()->isSameMonth($inicioMes)) {
          $graficaCrecimientoCategorias[] = $meses[$inicioMes->month - 1];

          $sumatoriaMes =  $reportes->where('fecha', '>=', $inicioMes->format('Y-m-d'))
            ->where('fecha', '<=', $finMes->format('Y-m-d'))
            ->sum('cantidad_asistencias');

          $cantidadSemanasPeriodo = ceil($inicioMes->diffInWeeks($finMes));
          $graficaCrecimientoDatos[] = round($sumatoriaMes / $cantidadSemanasPeriodo);
        }
      }
    }

    $graficasTab = [];

    $item = new stdClass();
    $item->id = 'graficaCrecimiento';
    $item->icono = 'ti ti-category';
    $item->tabActiva = true;
    $item->tabId = 'tab-crecimiento';
    $item->tabNombre = 'Crecimiento';
    $item->titulo = 'Gráfica de crecimiento';
    $item->descripcion = 'Hola2 subtitulo';
    $item->categorias = $graficaCrecimientoCategorias;
    $item->datos = $graficaCrecimientoDatos;
    $graficasTab[] = $item;
    // fin grafica de crecimiento


    // graficas por clasificacion
    foreach ($clasificaciones as $clasificacion) {
      $nombreFormateado = strtolower(str_replace(' ', '-', $clasificacion->nombre));
      $item = new stdClass();
      $item->id = 'grafica' . $nombreFormateado;
      $item->icono = 'ti ti-category';
      $item->tabActiva = false;
      $item->tabId = 'tab-' . $nombreFormateado;
      $item->tabNombre = $clasificacion->nombre;
      $item->titulo = 'Gráfica por ' . $clasificacion->nombre;
      $item->descripcion = '';

      $graficaCrecimientoCategoriasClasificacion = [];
      $graficaCrecimientoDatosClasificacion = [];

      // Calculo la data de cada categoria
      if ($graficaTipo == "semanas") {
        $periodo = CarbonPeriod::create($fechaInicio, '1 week', $fechaFin);
        foreach ($periodo as $fecha) {
          $inicioSemana = $fecha->copy();
          $finSemana = $fecha->copy()->endOfWeek();

          $graficaCrecimientoCategoriasClasificacion[] = $meses[$inicioSemana->month - 1] . " " . $inicioSemana->format('d') . "-" . $meses[$finSemana->month - 1] . " " . $finSemana->format('d');

          $sumatoriaSemana =  $reportesClasificacion->where('fecha', '>=', $inicioSemana->format('Y-m-d'))
            ->where('fecha', '<=', $finSemana->format('Y-m-d'))
            ->where('clasificacion_asistente_id', $clasificacion->id)
            ->sum('cantidad');

          $cantidadSemanasPeriodo = ceil($inicioSemana->diffInWeeks($finSemana));
          $graficaCrecimientoDatosClasificacion[] = round($sumatoriaSemana / $cantidadSemanasPeriodo);
        }
      } elseif ($graficaTipo == "meses") {
        $periodo = CarbonPeriod::create($fechaInicio, '1 month', $fechaFin);

        // Guarda los últimos 4 meses

        foreach ($periodo as $fecha) {
          $inicioMes = $fecha->copy()->startOfMonth();
          $finMes = $fecha->copy()->endOfMonth();

          $inicioMes = $inicioMes->isMonday() ? $inicioMes : $inicioMes->next(Carbon::MONDAY);
          $finMes = $finMes->isSunday() ?  $finMes :  $finMes->next(Carbon::SUNDAY);

          if (!Carbon::now()->isSameMonth($inicioMes)) {
            $graficaCrecimientoCategoriasClasificacion[] = $meses[$inicioMes->month - 1];

            $sumatoriaMes =  $reportesClasificacion->where('fecha', '>=', $inicioMes->format('Y-m-d'))
              ->where('fecha', '<=', $finMes->format('Y-m-d'))
              ->where('clasificacion_asistente_id', $clasificacion->id)
              ->sum('cantidad');

            $cantidadSemanasPeriodo = ceil($inicioMes->diffInWeeks($finMes));
            $graficaCrecimientoDatosClasificacion[] = round($sumatoriaMes / $cantidadSemanasPeriodo);
          }
        }
      }

      $item->categorias = $graficaCrecimientoCategoriasClasificacion;
      $item->datos = $graficaCrecimientoDatosClasificacion;
      $graficasTab[] = $item;
    }
    // fin graficas por clasificacion

    // para el listado de reportes
    $reportes =  $reportes->count() > 0
      ? $reportes->toQuery()->orderBy('fecha', 'desc')->orderBy('id', 'desc')->paginate(5)
      : ReporteGrupo::whereRaw('1=2')->paginate(1);

    return view('contenido.paginas.grupos.perfil-estadisticas-grupo', [
      'configuracion' => $configuracion,
      'rolActivo' => $rolActivo,
      'grupo' => $grupo,
      'grupos' => $grupos,
      'encargado' => $encargado ? $encargado : null,

      'reportes' => $reportes,

      'labelsRangoEdades' => $labelsRangoEdades,
      'seriesRangoEdades' => $seriesRangoEdades,
      'rangoEdades' => $rangoEdades,

      'seriesTiposSexos' => $seriesTiposSexos,
      'labelsTiposSexos' => $labelsTiposSexos,
      'tiposDeSexo' => $tiposDeSexo,

      'clasificaciones' => $clasificaciones,

      'tipoUsuarioDefault' => $tipoUsuarioDefault,
      'personasTipoDefault' => $personasTipoDefault,
      'personasInactivas' => $personasInactivas,
      'fechaMaximaActividadGrupo' => $fechaMaximaActividadGrupo,
      'indicadoresPortipoGrupo' => $indicadoresPortipoGrupo,

      'meses' => $meses,
      'fechaInicio' => $fechaInicio,
      'fechaFin' => $fechaFin,
      'request' => $request,

      'graficasTab' => $graficasTab
    ]);
  }

  public function perfilEstadisticasGrupo2(Request $request, Grupo $grupo, User $encargado)
  {
    /*
      // Define el rango de fechas
        $fechaInicio = Carbon::now()->subDays(30);
        $fechaFin = Carbon::now();

        // Asegúrate de que la fecha de inicio sea el lunes de esa semana
        $fechaInicio->startOfWeek();

        // Crea un periodo con incrementos semanales
        $periodo = CarbonPeriod::create($fechaInicio, '1 week', $fechaFin);

        // Guarda las últimas 4 semanas
        $ultimasCuatroSemanas = [];
        foreach ($periodo as $fecha) {
            $inicioSemana = $fecha->copy();
            $finSemana = $fecha->copy()->endOfWeek();
            $ultimasCuatroSemanas[] = [
                'inicio' => $inicioSemana->toDateString(),
                'fin' => $finSemana->toDateString()
            ];
        }

        // Obtén las últimas 4 semanas del array
        $ultimasCuatroSemanas = array_slice($ultimasCuatroSemanas, -4);
        echo "<br> ----<br>";
        foreach ($ultimasCuatroSemanas as $semana) {
            echo "Semana del " . $semana['inicio'] . " al " . $semana['fin'] . "<br>";
        }
        return "<br> fin";*/

    /*
    // Define el rango de fechas
    $fechaInicio = Carbon::now()->subMonths(4);
    $fechaFin = Carbon::now();

    // Asegúrate de que la fecha de inicio sea el primer día del mes
    $fechaInicio->startOfMonth();

    // Crea un periodo con incrementos mensuales
    $periodo = CarbonPeriod::create($fechaInicio, '1 month', $fechaFin);

    // Guarda los últimos 4 meses
    $ultimosCuatroMeses = [];
    foreach ($periodo as $fecha) {
        $inicioMes = $fecha->copy()->startOfMonth();
        $finMes = $fecha->copy()->endOfMonth();
        $ultimosCuatroMeses[] = [
            'inicio' => $inicioMes->toDateString(),
            'fin' => $finMes->toDateString()
        ];
    }

    // Obtén los últimos 4 meses del array
    $ultimosCuatroMeses = array_slice($ultimosCuatroMeses, -4);

    foreach ($ultimosCuatroMeses as $mes) {
        echo "Mes del " . $mes['inicio'] . " al " . $mes['fin'] . "<br>";
    }
    return "fin meses"; */
    /*
    // Define el rango de fechas
    $fechaInicio = Carbon::now()->subMonths(9);
    $fechaFin = Carbon::now();

    // Asegúrate de que la fecha de inicio sea el primer día del trimestre
    $fechaInicio->startOfQuarter();

    // Crea un periodo con incrementos trimestrales
    $periodo = CarbonPeriod::create($fechaInicio, '3 months', $fechaFin);

    // Guarda los últimos 2 trimestres
    $ultimosDosTrimestres = [];
    foreach ($periodo as $fecha) {
        $inicioTrimestre = $fecha->copy()->startOfQuarter();
        $finTrimestre = $fecha->copy()->endOfQuarter();
        $ultimosDosTrimestres[] = [
            'inicio' => $inicioTrimestre->toDateString(),
            'fin' => $finTrimestre->toDateString()
        ];
    }

    // Obtén los últimos 2 trimestres del array
    $ultimosDosTrimestres = array_slice($ultimosDosTrimestres, -3);

    foreach ($ultimosDosTrimestres as $trimestre) {
        echo "Trimestre del " . $trimestre['inicio'] . " al " . $trimestre['fin'] . "<br>";
    }
    return "<br> fin trimestres";*/


    $configuracion = Configuracion::find(1);
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $tipoUsuarioDefault = null;
    $personasTipoDefault = null;
    $personasInactivas = null;
    $fechaMaximaActividadGrupo = null;
    $indicadoresPortipoGrupo = [];
    $gruposInactivos = null;
    $hoy = Carbon::now();
    $reportes = [];

    $meses = Helpers::meses('largo');
    $tiposDeGrupo = TipoGrupo::orderBy('orden', 'asc')->get();

    // Filtro por fechas
    $filtroFechaIni = $request->filtroFechaIni ? Carbon::parse($request->filtroFechaIni)->format('Y-m-d') : Carbon::now()->subDays(30)->format('Y-m-d');
    $filtroFechaFin = $request->filtroFechaFin ? Carbon::parse($request->filtroFechaFin)->format('Y-m-d') : Carbon::now()->format('Y-m-d');

    //  conCobertura
    $filtroCobertura = $request->conCobertura ? $request->conCobertura : 0;

    // tiposGrupos
    $filtroTipoGrupos = $request->filtroPorTipoDeGrupo ? $request->filtroPorTipoDeGrupo : [];

    if ($filtroCobertura == 1) {
      // Cobertura
      $clasificaciones = ClasificacionAsistente::whereHas('tipoGrupos', function ($query) use ($filtroTipoGrupos) {
        $query->whereIn('clasificacion_asistente_tipo_grupo.tipo_grupo_id', $filtroTipoGrupos);
      })
        ->select('clasificaciones_asistentes.id', 'clasificaciones_asistentes.nombre')
        ->get();

      // trae los grupos de su ministerio que esten dados de alta
      $gruposCobertura = $grupo->gruposMinisterio()->select('id', 'nombre', 'tipo_grupo_id', 'ultimo_reporte_grupo')
        ->where('dado_baja', FALSE)
        ->get();

      $gruposCoberturaIds = $gruposCobertura
        ->where('tipo_grupo_id', $filtroTipoGrupos)
        ->pluck('id')->toArray();

      $reportesClasificacion = ReporteGrupo::whereIn('grupo_id', $gruposCoberturaIds)
        ->whereIn('clasificacion_asistente_id', $clasificaciones->pluck('id')->toArray())
        ->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin])
        ->leftJoin('clasificacion_asistente_reporte_grupo', 'reporte_grupos.id', '=', 'clasificacion_asistente_reporte_grupo.reporte_grupo_id')
        ->select('reporte_grupo_id', 'clasificacion_asistente_id', 'cantidad', 'fecha')
        ->get();

      $reportes = ReporteGrupo::select('id', 'fecha', 'grupo_id', 'cantidad_asistencias')
        ->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin])
        ->get();

      foreach ($tiposDeGrupo as $tipoGrupo) {
        $cantidad = $gruposCobertura
          ->where('tipo_grupo_id', $tipoGrupo->id)
          ->count();

        if ($cantidad > 0) {
          $item = new stdClass();
          $item->id = $tipoGrupo->id;
          $item->nombre = $tipoGrupo->nombre;
          $item->url = $tipoGrupo->id;
          $item->cantidad = $cantidad;
          $item->color = 'bg-label-success';
          $item->icono = 'ti ti-users-group';
          $indicadoresPortipoGrupo[] = $item;
        }
      }
      $indicadoresPortipoGrupo = collect($indicadoresPortipoGrupo);

      $tiposGruposConSeguimientoIds = TipoGrupo::where("seguimiento_actividad", "=", TRUE)->select('id')->pluck('id')->toArray();
      $gruposInactivos = $gruposCobertura->whereIn('tipo_grupo_id', $tiposGruposConSeguimientoIds)->filter(function ($grupo) {
        $fechaMaximaActividad = Carbon::now()
          ->subDays($grupo->tipoGrupo->tiempo_para_definir_inactivo_grupo)
          ->format('Y-m-d');

        return $grupo->ultimo_reporte_grupo < $fechaMaximaActividad || $grupo->ultimo_reporte_grupo == null;
      });

      $gruposInactivos->map(function ($grupo) use ($hoy) {
        $diasInactivos = round(Carbon::parse($grupo->ultimo_reporte_grupo)->diffInDays($hoy));
        $grupo->inactividad =  $diasInactivos == 0 ? '<b>Hoy</b>' : ($diasInactivos == 1 ? 'hace <b>1 día</b>' : 'hace <b>' . $diasInactivos . '</b> días');
        $grupo->color = 'bg-label-danger';
        $grupo->icono = 'ti ti-users-group';
      });
    } else {
      // solo grupo
      $clasificaciones = $grupo->tipoGrupo->clasificacionAsistentes()
        ->select('clasificaciones_asistentes.id', 'clasificaciones_asistentes.nombre')
        ->get();

      $reportesClasificacion = $grupo->reportes()
        ->whereIn('clasificacion_asistente_id', $clasificaciones->pluck('id')->toArray())
        ->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin])
        ->leftJoin('clasificacion_asistente_reporte_grupo', 'reporte_grupos.id', '=', 'clasificacion_asistente_reporte_grupo.reporte_grupo_id')
        ->select('reporte_grupo_id', 'clasificacion_asistente_id', 'cantidad', 'fecha')
        ->get();

      $reportes = $grupo->reportes()
        ->select('id', 'fecha', 'grupo_id', 'cantidad_asistencias')
        ->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin])
        ->get();

      $tipoUsuarioDefault = TipoUsuario::where('default', true)->select('id', 'nombre', 'nombre_plural', 'icono')->first();

      $asistentes = $grupo->asistentes()
        ->select('users.id', 'tipo_usuario_id', 'foto', 'genero', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'ultimo_reporte_grupo', 'users.created_at')
        ->get();

      $personasTipoDefault = $asistentes->where('tipo_usuario_id', $tipoUsuarioDefault->id)->sortByDesc('created_at');
      $personasTipoDefault->map(function ($persona) use ($hoy) {
        $diasCreacion = round(Carbon::parse($persona->created_at)->diffInDays($hoy));
        $persona->diasCreacion =  $diasCreacion == 0 ? '<b>hoy</b>' : ($diasCreacion == 1 ? 'hace <b>1 día</b>' : 'hace <b>' . $diasCreacion . '</b> días');
      });

      $fechaMaximaActividadGrupo = Carbon::now()
        ->subDays($configuracion->tiempo_para_definir_inactivo_grupo)
        ->format('Y-m-d');

      $personasInactivas =  $asistentes->filter(function ($usuario) use ($fechaMaximaActividadGrupo) {
        return $usuario->ultimo_reporte_grupo < $fechaMaximaActividadGrupo || $usuario->ultimo_reporte_grupo == null;
      })->sortByDesc('ultimo_reporte_grupo');

      $personasInactivas->map(function ($persona) use ($hoy) {
        $diasInactivos = round(Carbon::parse($persona->ultimo_reporte_grupo)->diffInDays($hoy));
        $persona->inactividad =  $diasInactivos == 0 ? '<b>Hoy</b>' : ($diasInactivos == 1 ? 'hace <b>1 día</b>' : 'hace <b>' . $diasInactivos . '</b> días');
      });
    }

    $clasificaciones->map(function ($clasificacion) use ($reportesClasificacion) {
      $clasificacion->promedio =  $reportesClasificacion->where('clasificacion_asistente_id', $clasificacion->id)->count() > 0
        ? round($reportesClasificacion->where('clasificacion_asistente_id', $clasificacion->id)->sum('cantidad') / $reportesClasificacion->where('clasificacion_asistente_id', $clasificacion->id)->count())
        : 0;
    });

    if (!$rolActivo->hasPermissionTo('grupos.opcion_ver_perfil_grupo')) return Redirect::to('pagina-no-encontrada');
    $meses = Helpers::meses('corto');

    $personas = $grupo->asistentes()->select('users.id', 'fecha_nacimiento', 'genero', 'tipo_usuario_id', 'genero')->get();
    $personas->map(function ($persona) {
      $persona->edad =  $persona->edad();
    });

    // edades
    $rangoEdades = $configuracion->rangoEdad()->orderBy('id', 'asc')->get();
    $rangoEdades->map(function ($rango) use ($personas) {
      $rango->cantidad = $personas->where('edad', '>=', $rango->edad_minima)->where('edad', '<=', $rango->edad_maxima)->count();
    });

    $labelsRangoEdades = $rangoEdades->pluck('nombre')->toArray();
    $seriesRangoEdades = $rangoEdades->pluck('cantidad')->toArray();

    // Por sexo
    $tiposDeSexo = [];

    $cantidadMasculino = $personas->where('genero', 0)->count();
    $item = new stdClass();
    $item->nombre = 'Masculino';
    $item->cantidad = $cantidadMasculino;
    $tiposDeSexo[] = $item;

    $cantidadFemenino = $personas->where('genero', 1)->count();
    $item = new stdClass();
    $item->nombre = 'Femenino';
    $item->cantidad = $cantidadFemenino;
    $tiposDeSexo[] = $item;

    $labelsTiposSexos = ['Masculino', 'Femenino'];
    $seriesTiposSexos = [$cantidadMasculino, $cantidadFemenino];


    // Por reportes
    $selectPeriodoGraficoAsistenciaReportes = $request->periodoGraficoAsistenciaReportes ? $request->periodoGraficoAsistenciaReportes : 3;
    $dataGraficoAsistenciaReportes = [];
    $fechaFin =  Carbon::now()->format('Y-m-d');
    $fechaIni =  Carbon::now()->subMonths($selectPeriodoGraficoAsistenciaReportes)->format('Y-m-d');

    // return $fechaIni;

    $idsUltimosReportes = $grupo->reportes()
      ->whereBetween('fecha', [$fechaIni, $fechaFin])
      ->select('reporte_grupos.id')
      ->pluck('reporte_grupos.id')
      ->toArray();

    $ultimosReportes = ReporteGrupo::whereIn('id', $idsUltimosReportes)->orderBy('fecha', 'asc')->orderBy('id', 'desc')->select('id', 'tema', 'fecha', 'cantidad_asistencias')->get();
    foreach ($ultimosReportes as $reporte) {
      $item = new stdClass();
      $item->x = Carbon::parse($reporte->fecha)->day . '-' . $meses[Carbon::parse($reporte->fecha)->month - 1];
      $item->y = $reporte->cantidad_asistencias;
      $dataGraficoAsistenciaReportes[]  = $item;
    }

    $sumaTotalAsistenciasReportes = $ultimosReportes->sum('cantidad_asistencias');

    $ultimosReportes =  $ultimosReportes->count() > 0
      ? $ultimosReportes->toQuery()->orderBy('fecha', 'desc')->orderBy('id', 'desc')->paginate(5)
      : ReporteGrupo::whereRaw('1=2')->paginate(1);

    //Por promedio de reportes
    $dataUltimosMeses = [];
    $serieUltimosMeses = [];
    $vuelta = 0;
    $cantidadReportes = $reportes->count();

    for ($i = Carbon::parse($filtroFechaIni)->month; $i <= Carbon::parse($filtroFechaFin)->month; $i++) {

      $fechaIni =  Carbon::parse($filtroFechaIni)->firstOfMonth()->addMonth($vuelta)->format('Y-m-d');
      $fechaFin =  Carbon::parse($filtroFechaIni)->lastOfMonth()->addMonth($vuelta)->format('Y-m-d');

      $serieUltimosMeses[] = $meses[Carbon::parse($filtroFechaIni)->firstOfMonth()->addMonth($vuelta)->month - 1];
      $promedioMes =  $reportes->where('fecha', '>=', $fechaIni)->where('fecha', '<=', $fechaFin)->sum('cantidad_asistencias');
      $dataUltimosMeses[] = $cantidadReportes > 0 ? $promedioMes / $cantidadReportes : 0;
      $vuelta++;
    }

    // return Carbon::parse($filtroFechaFin)->month.' '.Carbon::parse($filtroFechaIni)->month;
    //-----


    // estadistica de crecimiento



    return view('contenido.paginas.grupos.perfil-estadisticas-grupo', [
      'configuracion' => $configuracion,
      'rolActivo' => $rolActivo,
      'grupo' => $grupo,
      'serieUltimosMeses' => $serieUltimosMeses,
      'dataUltimosMeses' => $dataUltimosMeses,

      'ultimosReportes' => $ultimosReportes,
      'dataGraficoAsistenciaReportes' => $dataGraficoAsistenciaReportes,
      'selectPeriodoGraficoAsistenciaReportes' => $selectPeriodoGraficoAsistenciaReportes,
      'sumaTotalAsistenciasReportes' => $sumaTotalAsistenciasReportes,

      'labelsRangoEdades' => $labelsRangoEdades,
      'seriesRangoEdades' => $seriesRangoEdades,
      'rangoEdades' => $rangoEdades,

      'seriesTiposSexos' => $seriesTiposSexos,
      'labelsTiposSexos' => $labelsTiposSexos,
      'tiposDeSexo' => $tiposDeSexo,

      'clasificaciones' => $clasificaciones,

      'filtroFechaIni' => $filtroFechaIni,
      'filtroFechaFin' => $filtroFechaFin,
      'filtroCobertura' => $filtroCobertura,
      'filtroTipoGrupos' => $filtroTipoGrupos,

      'tipoUsuarioDefault' => $tipoUsuarioDefault,
      'personasTipoDefault' => $personasTipoDefault,
      'personasInactivas' => $personasInactivas,
      'gruposInactivos' => $gruposInactivos,
      'fechaMaximaActividadGrupo' => $fechaMaximaActividadGrupo,
      'indicadoresPortipoGrupo' => $indicadoresPortipoGrupo,

      'meses' => $meses,
      'tiposDeGrupo' => $tiposDeGrupo
    ]);
  }

  public function perfilEstadisticasCobertura(Request $request, Grupo $grupo, User $encargado)
  {
    $grupos = null;

    if ($encargado)
      $grupos = $encargado->gruposEncargados;

    $configuracion = Configuracion::find(1);
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('grupos.opcion_ver_perfil_grupo');

    $tiposDeGrupo = TipoGrupo::orderBy('nombre', 'asc')->get();
    $filtroTipoGrupos = $request->filtroPorTipoDeGrupo
      ? $request->filtroPorTipoDeGrupo
      : TipoGrupo::where('tipo_evangelistico', true)->select('id')->pluck('id')->toArray();

    $gruposInactivos = null;
    $personasInactivas = null;

    $fechaMaximaActividadGrupo = null;
    $indicadoresPortipoGrupo = [];

    $hoy = Carbon::now();
    $reportes = [];

    $meses = Helpers::meses('corto');

    // obtengo los rangos de fechas
    switch ($request->rango) {
      case "3m":
        $fechaInicio = Carbon::now()->subMonths(3)->startOfMonth();
        $fechaFin = Carbon::now()->subMonths(1)->endOfMonth();
        break;

      case "6m":
        $fechaInicio = Carbon::now()->subMonths(6)->startOfMonth();
        $fechaFin = Carbon::now()->subMonths(1)->endOfMonth();
        break;

      case "otroSemanas":
        list($añoIni, $semanaIni) = explode('-W', $request->semanaIni);
        $fechaInicio = Carbon::now()->setISODate($añoIni, $semanaIni)->startOfWeek();

        list($añoFin, $semanaFin) = explode('-W', $request->semanaFin);
        $fechaFin = Carbon::now()->setISODate($añoFin, $semanaFin)->endOfWeek();
        break;

      case "otroMeses":
        $fechaInicio = Carbon::createFromFormat('Y-m', $request->mesIni)->startOfMonth();
        $fechaFin = Carbon::createFromFormat('Y-m', $request->mesFin)->endOfMonth();
        break;

      default:
        $fechaInicio = Carbon::now()->subWeeks(4)->startOfWeek();
        $fechaFin = Carbon::now()->subWeeks(1)->endOfWeek();
    }

    // me aseguro que la fecha inicio sea lunes y que la fecha fin sea domingo, con el fin de obtener las semanas completas
    $fechaInicio = $fechaInicio->isMonday() ? $fechaInicio : $fechaInicio->next(Carbon::MONDAY);
    $fechaFin = $fechaFin->isSunday() ? $fechaFin : $fechaFin->next(Carbon::SUNDAY);

    // obtengo la cantidad de semanas del rango
    $cantidadSemanasRango = ceil($fechaInicio->diffInWeeks($fechaFin));
    $graficaTipo = $cantidadSemanasRango > 4 ? 'meses' : 'semanas';

    // doy formato a las fechas
    $fechaInicio = $fechaInicio->format('Y-m-d');
    $fechaFin = $fechaFin->format('Y-m-d');

    $clasificaciones = ClasificacionAsistente::whereHas('tipoGrupos', function ($query) use ($filtroTipoGrupos) {
      $query->whereIn('clasificacion_asistente_tipo_grupo.tipo_grupo_id', $filtroTipoGrupos);
    })
      ->select('clasificaciones_asistentes.id', 'clasificaciones_asistentes.nombre')
      ->get();

    // trae los grupos de su ministerio que esten dados de alta
    $gruposCobertura = $grupo->gruposMinisterio()->select('id', 'nombre', 'tipo_grupo_id', 'ultimo_reporte_grupo')
      ->where('dado_baja', FALSE)
      ->get();

    $gruposCoberturaIds = $gruposCobertura
      ->whereIn('tipo_grupo_id', $filtroTipoGrupos)
      ->pluck('id')->toArray();

    $reportesClasificacion = ReporteGrupo::whereIn('grupo_id', $gruposCoberturaIds)
      ->whereIn('clasificacion_asistente_id', $clasificaciones->pluck('id')->toArray())
      ->whereBetween('fecha', [$fechaInicio, $fechaFin])
      ->leftJoin('clasificacion_asistente_reporte_grupo', 'reporte_grupos.id', '=', 'clasificacion_asistente_reporte_grupo.reporte_grupo_id')
      ->select('reporte_grupo_id', 'clasificacion_asistente_id', 'cantidad', 'fecha')
      ->get();

    $reportes = ReporteGrupo::whereIn('grupo_id', $gruposCoberturaIds)
      ->select('id', 'fecha', 'grupo_id', 'cantidad_asistencias', 'cantidad_inasistencias', 'finalizado', 'no_reporte')
      ->whereBetween('fecha', [$fechaInicio, $fechaFin])
      ->get();

    foreach ($tiposDeGrupo as $tipoGrupo) {
      $cantidad = $gruposCobertura
        ->where('tipo_grupo_id', $tipoGrupo->id)
        ->count();

      if ($cantidad > 0) {
        $item = new stdClass();
        $item->id = $tipoGrupo->id;
        $item->nombre = $tipoGrupo->nombre;
        $item->url = $tipoGrupo->id;
        $item->cantidad = $cantidad;
        $item->color = $tipoGrupo->color;
        $item->icono = 'ti ti-users-group';
        $indicadoresPortipoGrupo[] = $item;
      }
    }

    $indicadoresPortipoGrupo = collect($indicadoresPortipoGrupo);

    // Extraemos los datos para ApexCharts
    $seriesTiposGrupos = $indicadoresPortipoGrupo->pluck('cantidad')->all();
    $labelsTiposGrupos = $indicadoresPortipoGrupo->pluck('nombre')->all();
    $colorsTiposGrupos = $indicadoresPortipoGrupo->pluck('color')->all();

    $tiposGruposConSeguimientoIds = TipoGrupo::where("seguimiento_actividad", "=", TRUE)->select('id')->pluck('id')->toArray();
    $gruposInactivos = $gruposCobertura->whereIn('tipo_grupo_id', $tiposGruposConSeguimientoIds)->filter(function ($grupo) {
      $fechaMaximaActividad = Carbon::now()
        ->subDays($grupo->tipoGrupo->tiempo_para_definir_inactivo_grupo)
        ->format('Y-m-d');

      return $grupo->ultimo_reporte_grupo < $fechaMaximaActividad || $grupo->ultimo_reporte_grupo == null;
    });

    $gruposInactivos->map(function ($grupo) use ($hoy) {
      $diasInactivos = round(Carbon::parse($grupo->ultimo_reporte_grupo)->diffInDays($hoy));
      $grupo->inactividad =  "<b>" . Helpers::determinarTiempo($diasInactivos) . "</b>";
      $grupo->color = 'bg-label-danger';
      $grupo->icono = 'ti ti-users-group';
    });

    $clasificaciones->map(function ($clasificacion) use ($reportesClasificacion, $cantidadSemanasRango) {
      $clasificacion->promedio =  $reportesClasificacion->where('clasificacion_asistente_id', $clasificacion->id)->count() > 0
        ? round($reportesClasificacion->where('clasificacion_asistente_id', $clasificacion->id)->sum('cantidad') / $cantidadSemanasRango)
        : 0;
    });

    $personas = $grupo->ministerioDelGrupo("objeto", "sin-eliminados", true,);
    $personas->map(function ($persona) {
      $persona->edad =  $persona->edad();
    });

    // edades
    $rangoEdades = $configuracion->rangoEdad()->orderBy('id', 'asc')->get();
    $rangoEdades->map(function ($rango) use ($personas) {
      $rango->cantidad = $personas->where('edad', '>=', $rango->edad_minima)->where('edad', '<=', $rango->edad_maxima)->count();
    });

    $labelsRangoEdades = $rangoEdades->pluck('nombre')->toArray();
    $seriesRangoEdades = $rangoEdades->pluck('cantidad')->toArray();

    // Por sexo
    $tiposDeSexo = [];

    $cantidadMasculino = $personas->where('genero', 0)->count();
    $item = new stdClass();
    $item->nombre = 'Masculino';
    $item->cantidad = $cantidadMasculino;
    $tiposDeSexo[] = $item;

    $cantidadFemenino = $personas->where('genero', 1)->count();
    $item = new stdClass();
    $item->nombre = 'Femenino';
    $item->cantidad = $cantidadFemenino;
    $tiposDeSexo[] = $item;

    $labelsTiposSexos = ['Masculino', 'Femenino'];
    $seriesTiposSexos = [$cantidadMasculino, $cantidadFemenino];

    //Gráfica de crecimiento
    $graficaCrecimientoCategorias = [];

    $datosAsistencias = [];
    $datosInasistencias = [];

    $datosEstadoReportes = [];
    $categoriasEstadoReportes = [];
    // Definimos las categorías de estado
    $estados = ['Finalizados', 'No reportado', 'No realizado'];

    if ($graficaTipo == "semanas") {
      $periodo = CarbonPeriod::create($fechaInicio, '1 week', $fechaFin);
      $bloquesPeriodos = [];
      foreach ($periodo as $fecha) {
        $inicioSemana = $fecha->copy();
        $finSemana = $fecha->copy()->endOfWeek();

        $graficaCrecimientoCategorias[] = $meses[$inicioSemana->month - 1] . " " . $inicioSemana->format('d') . "-" . $meses[$finSemana->month - 1] . " " . $finSemana->format('d');

        $sumatoriaAsistencias =  $reportes->where('fecha', '>=', $inicioSemana->format('Y-m-d'))
          ->where('fecha', '<=', $finSemana->format('Y-m-d'))
          ->sum('cantidad_asistencias');

        $sumatoriaInasistencias =  $reportes->where('fecha', '>=', $inicioSemana->format('Y-m-d'))
          ->where('fecha', '<=', $finSemana->format('Y-m-d'))
          ->sum('cantidad_inasistencias');

        $cantidadSemanasPeriodo = ceil($inicioSemana->diffInWeeks($finSemana));
        $datosAsistencias[] = round($sumatoriaAsistencias / $cantidadSemanasPeriodo);
        $datosInasistencias[] = round($sumatoriaInasistencias / $cantidadSemanasPeriodo);

        $reportesSemana = $reportes
          ->where('fecha', '>=', $inicioSemana->format('Y-m-d'))
          ->where('fecha', '<=', $finSemana->format('Y-m-d'));

        $datosEstadoReportes['Finalizados'][] = $reportesSemana->where('finalizado', true)->where('no_reporte', false)->count();
        $datosEstadoReportes['No reportado'][] = $reportesSemana->where('finalizado', false)->where('no_reporte', false)->count();
        $datosEstadoReportes['No realizado'][] = $reportesSemana->where('no_reporte', true)->count();
      }
    } elseif ($graficaTipo == "meses") {
      $periodo = CarbonPeriod::create($fechaInicio, '1 month', $fechaFin);

      // Guarda los últimos 4 meses

      foreach ($periodo as $fecha) {
        $inicioMes = $fecha->copy()->startOfMonth();
        $finMes = $fecha->copy()->endOfMonth();

        $inicioMes = $inicioMes->isMonday() ? $inicioMes : $inicioMes->next(Carbon::MONDAY);
        $finMes = $finMes->isSunday() ?  $finMes :  $finMes->next(Carbon::SUNDAY);

        if (!Carbon::now()->isSameMonth($inicioMes)) {
          $graficaCrecimientoCategorias[] = $meses[$inicioMes->month - 1];

          $sumatoriaAsistencias =  $reportes->where('fecha', '>=', $inicioMes->format('Y-m-d'))
            ->where('fecha', '<=', $finMes->format('Y-m-d'))
            ->sum('cantidad_asistencias');

          $sumatoriaInasistencias =  $reportes->where('fecha', '>=', $inicioMes->format('Y-m-d'))
            ->where('fecha', '<=', $finMes->format('Y-m-d'))
            ->sum('cantidad_inasistencias');

          $cantidadSemanasPeriodo = ceil($inicioMes->diffInWeeks($finMes));
          $datosAsistencias[] = round($sumatoriaAsistencias / $cantidadSemanasPeriodo);
          $datosInasistencias[] = round($sumatoriaInasistencias / $cantidadSemanasPeriodo);



          $reportesMes = $reportes
            ->where('fecha', '>=', $inicioMes->format('Y-m-d'))
            ->where('fecha', '<=', $finMes->format('Y-m-d'));

          $datosEstadoReportes['Finalizados'][] = $reportesMes->where('finalizado', true)->where('no_reporte', false)->count();
          $datosEstadoReportes['No reportado'][] = $reportesMes->where('finalizado', false)->where('no_reporte', false)->count();
          $datosEstadoReportes['No realizado'][] = $reportesMes->where('no_reporte', true)->count();
        }
      }
    }

    $graficasTab = [];

    $item = new stdClass();
    $item->id = 'graficaCrecimiento';
    $item->icono = 'ti ti-category';
    $item->tabActiva = true;
    $item->tabId = 'tab-crecimiento';
    $item->tabNombre = 'Crecimiento';
    $item->titulo = 'Gráfica de crecimiento';
    $item->descripcion = 'En este gráfico se muestra la cantidad de asistencias e inasistencias.';
    $item->categorias = $graficaCrecimientoCategorias;
    $item->datos = [
      [
        'name' => 'Asistencias',
        'data' => $datosAsistencias
      ],
      [
        'name' => 'Inasistencias',
        'data' => $datosInasistencias
      ]
    ];
    $graficasTab[] = $item;
    // fin grafica de crecimiento


    // grafi x

    // Preparamos los datos para ApexCharts
    $seriesEstadoReportes = [];
    foreach ($estados as $estado) {
      $seriesEstadoReportes[] = [
        'name' => $estado,
        'data' => $datosEstadoReportes[$estado] ?? []
      ];
    }

    $item = new stdClass();
    $item->id = 'graficaEstadoReportes';
    $item->icono = 'ti ti-clipboard-check';
    $item->tabActiva = false;
    $item->tabId = 'tab-estado-reportes';
    $item->tabNombre = 'Estado de Reportes';
    $item->titulo = 'Gráfica de estado de reportes';
    $item->descripcion = 'Muestra la cantidad de reportes por su estado (Finalizados, No reportados, No realizados).';
    $item->categorias = $graficaCrecimientoCategorias;
    $item->datos = $seriesEstadoReportes;
    $item->valorObjetivo = count($gruposCoberturaIds);
    $graficasTab[] = $item;

    // fin x

    // graficas por clasificacion
    foreach ($clasificaciones as $clasificacion) {
      $nombreFormateado = strtolower(str_replace(' ', '-', $clasificacion->nombre));
      $item = new stdClass();
      $item->id = 'grafica' . $nombreFormateado;
      $item->icono = 'ti ti-category';
      $item->tabActiva = false;
      $item->tabId = 'tab-' . $nombreFormateado;
      $item->tabNombre = $clasificacion->nombre;
      $item->titulo = 'Gráfica por ' . $clasificacion->nombre;
      $item->descripcion = '';

      $graficaCrecimientoCategoriasClasificacion = [];
      $graficaCrecimientoDatosClasificacion = [];

      // Calculo la data de cada categoria
      if ($graficaTipo == "semanas") {
        $periodo = CarbonPeriod::create($fechaInicio, '1 week', $fechaFin);
        foreach ($periodo as $fecha) {
          $inicioSemana = $fecha->copy();
          $finSemana = $fecha->copy()->endOfWeek();

          $graficaCrecimientoCategoriasClasificacion[] = $meses[$inicioSemana->month - 1] . " " . $inicioSemana->format('d') . "-" . $meses[$finSemana->month - 1] . " " . $finSemana->format('d');

          $sumatoriaSemana =  $reportesClasificacion->where('fecha', '>=', $inicioSemana->format('Y-m-d'))
            ->where('fecha', '<=', $finSemana->format('Y-m-d'))
            ->where('clasificacion_asistente_id', $clasificacion->id)
            ->sum('cantidad');

          $cantidadSemanasPeriodo = ceil($inicioSemana->diffInWeeks($finSemana));
          $graficaCrecimientoDatosClasificacion[] = round($sumatoriaSemana / $cantidadSemanasPeriodo);
        }
      } elseif ($graficaTipo == "meses") {
        $periodo = CarbonPeriod::create($fechaInicio, '1 month', $fechaFin);

        // Guarda los últimos 4 meses

        foreach ($periodo as $fecha) {
          $inicioMes = $fecha->copy()->startOfMonth();
          $finMes = $fecha->copy()->endOfMonth();

          $inicioMes = $inicioMes->isMonday() ? $inicioMes : $inicioMes->next(Carbon::MONDAY);
          $finMes = $finMes->isSunday() ?  $finMes :  $finMes->next(Carbon::SUNDAY);

          if (!Carbon::now()->isSameMonth($inicioMes)) {
            $graficaCrecimientoCategoriasClasificacion[] = $meses[$inicioMes->month - 1];

            $sumatoriaMes =  $reportesClasificacion->where('fecha', '>=', $inicioMes->format('Y-m-d'))
              ->where('fecha', '<=', $finMes->format('Y-m-d'))
              ->where('clasificacion_asistente_id', $clasificacion->id)
              ->sum('cantidad');

            $cantidadSemanasPeriodo = ceil($inicioMes->diffInWeeks($finMes));
            $graficaCrecimientoDatosClasificacion[] = round($sumatoriaMes / $cantidadSemanasPeriodo);
          }
        }
      }

      $item->categorias = $graficaCrecimientoCategoriasClasificacion;
      $item->datos = $graficaCrecimientoDatosClasificacion;
      $graficasTab[] = $item;
    }
    // fin graficas por clasificacion


    // para el listado de reportes
    $reportes =  $reportes->count() > 0
      ? $reportes->toQuery()->orderBy('fecha', 'desc')->orderBy('id', 'desc')->paginate(5)
      : ReporteGrupo::whereRaw('1=2')->paginate(1);


    // para clasificacion por pasos de crecimiento
    $graficaPasosTab = $grupo->dataEstadisticasPorPasoDeCrecimiento();

    return view('contenido.paginas.grupos.perfil-estadisticas-cobertura', [
      'configuracion' => $configuracion,
      'rolActivo' => $rolActivo,
      'grupo' => $grupo,

      'grupos' => $grupos,
      'encargado' => $encargado ? $encargado : null,

      'tiposDeGrupo' => $tiposDeGrupo,
      'filtroTipoGrupos' => $filtroTipoGrupos,

      'reportes' => $reportes,

      'labelsRangoEdades' => $labelsRangoEdades,
      'seriesRangoEdades' => $seriesRangoEdades,
      'rangoEdades' => $rangoEdades,

      'seriesTiposSexos' => $seriesTiposSexos,
      'labelsTiposSexos' => $labelsTiposSexos,
      'tiposDeSexo' => $tiposDeSexo,

      'clasificaciones' => $clasificaciones,

      'gruposInactivos' => $gruposInactivos,

      'personasInactivas' => $personasInactivas,
      'fechaMaximaActividadGrupo' => $fechaMaximaActividadGrupo,
      'indicadoresPortipoGrupo' => $indicadoresPortipoGrupo,
      'seriesTiposGrupos' => $seriesTiposGrupos,
      'labelsTiposGrupos' => $labelsTiposGrupos,
      'colorsTiposGrupos' => $colorsTiposGrupos,

      'meses' => $meses,
      'fechaInicio' => $fechaInicio,
      'fechaFin' => $fechaFin,
      'request' => $request,

      'graficasTab' => $graficasTab,
      'graficaPasosTab' => $graficaPasosTab
    ]);
  }

  public function perfilIntegrantes(Grupo $grupo, User $encargado)
  {
    $grupos = null;

    if ($encargado)
      $grupos = $encargado->gruposEncargados;

    $configuracion = Configuracion::find(1);
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('grupos.opcion_ver_perfil_grupo');

    $encargados = $grupo->encargados()
      ->select('users.id', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'foto', 'tipo_usuario_id')
      ->with('tipoUsuario')
      ->get();

    $servidores = ServidorGrupo::where("grupo_id", "=", $grupo->id)
      ->leftJoin('users', 'user_id', '=', 'users.id')
      ->select('servidores_grupo.*', 'users.id as idUser', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'foto', 'tipo_usuario_id')
      ->get();

    $servidores = User::where('servidores_grupo.grupo_id', $grupo->id)
      ->leftJoin('servidores_grupo', 'users.id', '=', 'user_id')
      ->select('servidores_grupo.id as servidorId', 'users.id', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'foto', 'tipo_usuario_id')
      ->get();

    $servidores->map(function ($servidor) use ($grupo) {
      $servicios = $servidor->serviciosPrestadosEnGrupos($grupo->id)->pluck('nombre')->toArray();
      $servidor->servicios  = $servicios;
    });

    $dataUltimosReportes = [];
    $serieUltimosReportes = [];
    $idsUltimos10Reportes = $grupo->reportes()->orderBy('fecha', 'desc')->orderBy('fecha', 'asc')->take(10)->select('id')->pluck('id')->toArray();
    $ultimos10Reportes = ReporteGrupo::whereIn('id', $idsUltimos10Reportes)->select('id', 'tema', 'fecha', 'cantidad_asistencias')->get();

    $meses = Helpers::meses('corto');

    foreach ($ultimos10Reportes as $reporte) {
      $dataUltimosReportes[] = $reporte->cantidad_asistencias;
      $serieUltimosReportes[] = Carbon::parse($reporte->fecha)->day . '-' . $meses[Carbon::parse($reporte->fecha)->month - 1];
    }

    $dataUltimosMeses = [];
    $serieUltimosMeses = [];
    $mes = Carbon::now()->firstOfMonth()->month;
    $mesIni = Carbon::now()->firstOfMonth()->subMonth(5)->month;

    for ($i = 5; $i >= 0; $i--) {
      $fechaIni =  Carbon::now()->firstOfMonth()->subMonth($i)->format('Y-m-d');
      $fechaFin =  Carbon::now()->lastOfMonth()->subMonth($i)->format('Y-m-d');
      $serieUltimosMeses[] = $meses[Carbon::now()->firstOfMonth()->subMonth($i)->month - 1];
      $promedioMes =  $grupo->reportes()->where('fecha', '>=', $fechaIni)->where('fecha', '<=', $fechaFin)->avg('cantidad_asistencias');
      $dataUltimosMeses[] = $promedioMes;
    }


    $camposExtras = CampoExtraGrupo::where('visible', '=', true)->get();

    $camposExtras->map(function ($campoExtra) use ($grupo) {
      $grupoCampoExtra = $grupo->camposExtras()->where('campos_extra_grupo.id', $campoExtra->id)->first();

      if ($campoExtra->tipo_de_campo == 4) {
        $valor = [];

        if ($grupoCampoExtra) {
          foreach (json_decode($campoExtra->opciones_select) as $opcion) {
            if (in_array($opcion->value, json_decode($grupoCampoExtra->pivot->valor)))
              $valor[] = $opcion->nombre;
          }
        }

        $campoExtra->valor = count($valor) > 0 ? implode(",", $valor) : '';
      } elseif ($campoExtra->tipo_de_campo == 3) {
        $valor = '';
        if ($grupoCampoExtra) {
          foreach (json_decode($campoExtra->opciones_select) as $opcion) {
            if ($opcion->value == $grupoCampoExtra->pivot->valor)
              $valor = $opcion->nombre;
          }
        }
        $campoExtra->valor = $valor;
      } else {
        $campoExtra->valor = $grupoCampoExtra ? $grupoCampoExtra->pivot->valor : '';
      }
    });


    return view('contenido.paginas.grupos.perfil-integrantes', [
      'configuracion' => $configuracion,
      'rolActivo' => $rolActivo,
      'grupo' => $grupo,
      'grupos' => $grupos,
      'encargado' => $encargado ? $encargado : null,
      'encargados' => $encargados,
      'servidores' => $servidores,
      'dataUltimosReportes' => $dataUltimosReportes,
      'serieUltimosReportes' => $serieUltimosReportes,
      'serieUltimosMeses' => $serieUltimosMeses,
      'dataUltimosMeses' => $dataUltimosMeses,
      'camposExtras' => $camposExtras,
      'ultimos10Reportes' => $ultimos10Reportes
    ]);
  }

  public function copiaperfil(Grupo $grupo)
  {
    $configuracion = Configuracion::find(1);
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    $encargados = $grupo->encargados()
      ->select('users.id', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'foto', 'tipo_usuario_id')
      ->with('tipoUsuario')
      ->get();

    $servidores = ServidorGrupo::where("grupo_id", "=", $grupo->id)
      ->leftJoin('users', 'user_id', '=', 'users.id')
      ->select('servidores_grupo.*', 'users.id as idUser', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'foto', 'tipo_usuario_id')
      ->get();

    $servidores = User::where('servidores_grupo.grupo_id', $grupo->id)
      ->leftJoin('servidores_grupo', 'users.id', '=', 'user_id')
      ->select('servidores_grupo.id as servidorId', 'users.id', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'foto', 'tipo_usuario_id')
      ->get();

    $servidores->map(function ($servidor) use ($grupo) {
      $servicios = $servidor->serviciosPrestadosEnGrupos($grupo->id)->pluck('nombre')->toArray();
      $servidor->servicios  = $servicios;
    });

    $dataUltimosReportes = [];
    $serieUltimosReportes = [];
    $idsUltimos10Reportes = $grupo->reportes()->orderBy('fecha', 'desc')->orderBy('fecha', 'asc')->take(10)->select('id')->pluck('id')->toArray();
    $ultimos10Reportes = ReporteGrupo::whereIn('id', $idsUltimos10Reportes)->select('id', 'tema', 'fecha', 'cantidad_asistencias')->get();

    $meses = Helpers::meses('corto');

    foreach ($ultimos10Reportes as $reporte) {
      $dataUltimosReportes[] = $reporte->cantidad_asistencias;
      $serieUltimosReportes[] = Carbon::parse($reporte->fecha)->day . '-' . $meses[Carbon::parse($reporte->fecha)->month - 1];
    }

    $dataUltimosMeses = [];
    $serieUltimosMeses = [];
    $mes = Carbon::now()->firstOfMonth()->month;
    $mesIni = Carbon::now()->firstOfMonth()->subMonth(5)->month;

    for ($i = 5; $i >= 0; $i--) {
      $fechaIni =  Carbon::now()->firstOfMonth()->subMonth($i)->format('Y-m-d');
      $fechaFin =  Carbon::now()->lastOfMonth()->subMonth($i)->format('Y-m-d');
      $serieUltimosMeses[] = $meses[Carbon::now()->firstOfMonth()->subMonth($i)->month - 1];
      $promedioMes =  $grupo->reportes()->where('fecha', '>=', $fechaIni)->where('fecha', '<=', $fechaFin)->avg('cantidad_asistencias');
      $dataUltimosMeses[] = $promedioMes;
    }


    $camposExtras = CampoExtraGrupo::where('visible', '=', true)->get();

    $camposExtras->map(function ($campoExtra) use ($grupo) {
      $grupoCampoExtra = $grupo->camposExtras()->where('campos_extra_grupo.id', $campoExtra->id)->first();

      if ($campoExtra->tipo_de_campo == 4) {
        $valor = [];

        if ($grupoCampoExtra) {
          foreach (json_decode($campoExtra->opciones_select) as $opcion) {
            if (in_array($opcion->value, json_decode($grupoCampoExtra->pivot->valor)))
              $valor[] = $opcion->nombre;
          }
        }

        $campoExtra->valor = count($valor) > 0 ? implode(",", $valor) : '';
      } elseif ($campoExtra->tipo_de_campo == 3) {
        $valor = '';
        if ($grupoCampoExtra) {
          foreach (json_decode($campoExtra->opciones_select) as $opcion) {
            if ($opcion->value == $grupoCampoExtra->pivot->valor)
              $valor = $opcion->nombre;
          }
        }
        $campoExtra->valor = $valor;
      } else {
        $campoExtra->valor = $grupoCampoExtra ? $grupoCampoExtra->pivot->valor : '';
      }
    });


    return view('contenido.paginas.grupos.perfil', [
      'configuracion' => $configuracion,
      'rolActivo' => $rolActivo,
      'grupo' => $grupo,
      'encargados' => $encargados,
      'servidores' => $servidores,
      'dataUltimosReportes' => $dataUltimosReportes,
      'serieUltimosReportes' => $serieUltimosReportes,
      'serieUltimosMeses' => $serieUltimosMeses,
      'dataUltimosMeses' => $dataUltimosMeses,
      'camposExtras' => $camposExtras,
      'ultimos10Reportes' => $ultimos10Reportes
    ]);
  }

  public function modificar(Grupo $grupo)
  {
    if (!isset($grupo)) return Redirect::to('pagina-no-encontrada');

    $configuracion = Configuracion::find(1);
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('grupos.opcion_modificar_grupo');
    $tipoGrupos = TipoGrupo::orderBy('orden', 'asc')->get();
    $tiposDeVivienda = TipoVivienda::orderBy('nombre', 'asc')->get();
    $camposExtras = CampoExtraGrupo::where('visible', '=', true)->get();

    $camposExtras->map(function ($campoExtra) use ($grupo) {
      $grupoCampoExtra = $grupo->camposExtras()->where('campos_extra_grupo.id', $campoExtra->id)->first();
      $campoExtra->valor = $grupoCampoExtra ? $grupoCampoExtra->pivot->valor : '';
    });

    return view('contenido.paginas.grupos.modificar', [
      'tipoGrupos' => $tipoGrupos,
      'rolActivo' => $rolActivo,
      'configuracion' => $configuracion,
      'tiposDeVivienda' => $tiposDeVivienda,
      'camposExtras' => $camposExtras,
      'grupo' => $grupo,
    ]);
  }

  public function editar(Request $request, Grupo $grupo)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $configuracion = Configuracion::find(1);

    // Validación
    $validacion = [];

    //nombre
    if ($configuracion->habilitar_nombre_grupo) {
      $validarNombre = ['string', 'max:100'];
      $configuracion->nombre_grupo_obligatorio ? array_push($validarNombre, 'required') : '';
      $validacion = array_merge($validacion, ['nombre' => $validarNombre]);
    }

    //  tipo_de_grupo
    if ($configuracion->habilitar_tipo_grupo) {
      $validarTipoGrupo = [];
      $configuracion->tipo_grupo_obligatorio ? array_push($validarTipoGrupo, 'required') : '';
      $validacion = array_merge($validacion, ['tipo_de_grupo' => $validarTipoGrupo]);
    }

    // fecha
    if ($configuracion->habilitar_fecha_creacion_grupo) {
      $validarFecha = [];
      $configuracion->fecha_creacion_grupo_obligatorio ? array_push($validarFecha, 'required') : '';
      $validacion = array_merge($validacion, ['fecha' => $validarFecha]);
    }

    // Tiene AMO
    if ($configuracion->version == 2)
      $validacion = array_merge($validacion, ['contiene_amo' => []]);


    // telefono
    if ($configuracion->habilitar_telefono_grupo) {
      $validarTelefono = [];
      $configuracion->telefono_grupo_obligatorio ? array_push($validarTelefono, 'required') : '';
      $validacion = array_merge($validacion, ['teléfono' => $validarTelefono]);
    }

    // tipo de vivienda
    if ($configuracion->habilitar_tipo_vivienda_grupo) {
      $validarTipoVivienda = [];
      $configuracion->tipo_vivienda_grupo_obligatorio ? array_push($validarTipoVivienda, 'required') : '';
      $validacion = array_merge($validacion, ['tipo_de_vivienda' => $validarTipoVivienda]);
    }

    // direccion
    if ($configuracion->habilitar_direccion_grupo) {
      $validarDireccion = [];
      $configuracion->direccion_grupo_obligatorio ? array_push($validarDireccion, 'required') : '';
      $validacion = array_merge($validacion, ['dirección' => $validarDireccion]);
    }

    // campo_opcional
    if ($configuracion->habilitar_campo_opcional1_grupo) {
      $validarCampoOpcional = [];
      $configuracion->campo_opcional1_obligatorio ? array_push($validarCampoOpcional, 'required') : '';
      $validacion = array_merge($validacion, ['adiccional' => $validarCampoOpcional]);
    }

    // dia de reunion
    if ($configuracion->habilitar_dia_reunion_grupo) {
      $validardiaReunion = [];
      $configuracion->dia_reunion_grupo_obligatorio ? array_push($validardiaReunion, 'required') : '';
      $validacion = array_merge($validacion, ['día_de_reunión' => $validardiaReunion]);
    }
    // hora de reunion
    if ($configuracion->habilitar_hora_reunion_grupo) {
      $validarHoraReunion = [];
      $configuracion->habilitar_hora_reunion_grupo ? array_push($validarHoraReunion, 'required') : '';
      $validacion = array_merge($validacion, ['hora_de_reunión' => $validarHoraReunion]);
    }

    /// seccion comprobacion campos extras
    if ($configuracion->visible_seccion_campos_extra_grupo == TRUE && $rolActivo->hasPermissionTo('grupos.visible_seccion_campos_extra_grupo')) {
      $camposExtras = CampoExtraGrupo::where('visible', '=', true)->get();

      foreach ($camposExtras as $campoExtra) {
        $validarCampoExtra = [];
        $campoExtra->required ? array_push($validarCampoExtra, 'required') : '';
        $validacion = array_merge($validacion, [$campoExtra->class_id => $validarCampoExtra]);
      }
    }

    // Validacion de datos
    $request->validate($validacion);

    $grupo->nombre =  $request->nombre;
    $grupo->telefono = $request->teléfono;
    $grupo->direccion = $request->dirección;
    $grupo->barrio_id = $request->barrio_id ? $request->barrio_id : null;
    $grupo->barrio_auxiliar = $request->barrio_auxiliar;
    $grupo->tipo_vivienda_id = $request->tipo_de_vivienda;
    $grupo->tipo_grupo_id = $request->tipo_de_grupo;
    $grupo->rhema = $request->adiccional;
    $grupo->dia = $request->día_de_reunión;
    $grupo->hora = $request->hora_de_reunión;
    $grupo->contiene_amo = $request->amo ? TRUE : FALSE;
    $grupo->fecha_apertura = $request->fecha;
    $grupo->inactivo = 0;
    $grupo->dado_baja = 0;
    $grupo->usuario_creacion_id = auth()->user()->id;
    $grupo->rol_de_creacion_id = $rolActivo->id;
    $grupo->save();

    $grupo->indice_grafico_ministerial = $grupo->id;
    $grupo->save();
    $grupo->asignarSede();

    /// esta sección es para el guardado de los campos extra
    if ($configuracion->visible_seccion_campos_extra_grupo == TRUE) {
      $camposExtras = CampoExtraGrupo::where('visible', '=', true)->get();

      foreach ($camposExtras as $campo) {
        $grupoCampoExtra = $grupo
          ->camposExtras()
          ->where('campo_extra_grupo_id', '=', $campo->id)
          ->first();

        if ($grupoCampoExtra) {
          if ($campo->tipo_de_campo != 4)
            $grupoCampoExtra->pivot->valor = ucwords(mb_strtolower($request[$campo->class_id]));
          else
            $grupoCampoExtra->pivot->valor = ucwords(mb_strtolower(json_encode($request[$campo->class_id])));

          $grupoCampoExtra->pivot->save();
        } else {
          if ($campo->tipo_de_campo != 4)
            $grupo->camposExtras()->attach($campo->id, array('valor' => ucwords(mb_strtolower($request[$campo->class_id]))));
          else
            $grupo->camposExtras()->attach($campo->id, array('valor' => (json_encode($request[$campo->class_id]))));
        }
      }
    }

    if ($grupo->save()) {

      // AÑADO LA PORTADA
      if ($request->foto) {
        if ($configuracion->version == 1) {
          $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/grupos/');
          !is_dir($path) && mkdir($path, 0777, true);

          $imagenPartes = explode(';base64,', $request->foto);
          $imagenBase64 = base64_decode($imagenPartes[1]);
          $nombreFoto = 'grupo' . $grupo->id . '.png';
          $imagenPath = $path . $nombreFoto;
          file_put_contents($imagenPath, $imagenBase64);
          $grupo->portada = $nombreFoto;
          $grupo->save();
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

    return back()->with('success', "El grupo <b>" . $grupo->nombre . "</b> se actualizó con éxito.");
    /*return Redirect::to('/grupos/anadir-lideres/'.$grupo->id)->with(
			array(
				'status' => 'ok_new_grupo',
				'id_nuevo' => $grupo->id,
				'nombre_nuevo' => $grupo->nombre,
				)
		);*/
  }

  public function gestionarEncargados(Grupo $grupo)
  {
    $configuracion = Configuracion::find(1);
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso(['grupos.pestana_anadir_lideres_grupo', 'grupos.opcion_anadir_lideres_grupo']);

    /* return $rolActivo->privilegiosTiposGrupo()->wherePivot("tipo_grupo_id", "=", $grupo->tipoGrupo->id)
    ->first();*/


    $idsEncargadosSeleccionados = $grupo->encargados()->select('users.id')
      ->pluck('users.id')
      ->toArray();


    $queUsuariosCargarEncargados = $rolActivo->hasPermissionTo('personas.ajax_obtiene_asistentes_solo_ministerio')
      ? 'discipulos'
      : 'todos';

    // Si es TRUE carga son los asistentes al grupo
    $queUsuariosCargarServidores = $grupo->tipoGrupo->servidores_solo_discipulos
      ? 'grupo'
      : 'todos';

    $idsServidoresSeleccionados = ServidorGrupo::where('grupo_id', '=', $grupo->id)
      ->pluck('user_id')
      ->toArray();

    /*return $rolActivo->privilegiosTiposGrupo()
    ->get();*/

    return view('contenido.paginas.grupos.gestionar-encargados', [
      'grupo' => $grupo,
      'configuracion' => $configuracion,
      'rolActivo' => $rolActivo,
      'queUsuariosCargarEncargados' => $queUsuariosCargarEncargados,
      'queUsuariosCargarServidores' => $queUsuariosCargarServidores,
      'idsServidoresSeleccionados' => $idsServidoresSeleccionados,
      'idsEncargadosSeleccionados' => $idsEncargadosSeleccionados,
    ]);
  }

  public function gestionarIntegrantes(Grupo $grupo)
  {
    $configuracion = Configuracion::find(1);
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso(['grupos.pestana_anadir_integrantes_grupo', 'grupos.opcion_anadir_integrantes_grupo']);

    $queUsuariosCargar = $rolActivo->hasPermissionTo('personas.ajax_obtiene_asistentes_solo_ministerio')
      ? 'discipulos'
      : 'todos';

    $idsIntegrantesSeleccionados = $grupo->asistentes()->select('users.id')
      ->pluck('users.id')
      ->toArray();

    return view('contenido.paginas.grupos.gestionar-integrantes', [
      'grupo' => $grupo,
      'queUsuariosCargar' => $queUsuariosCargar,
      'idsIntegrantesSeleccionados' => $idsIntegrantesSeleccionados,
      'configuracion' => $configuracion,
      'rolActivo' => $rolActivo
    ]);
  }

  public function georreferencia(Grupo $grupo)
  {

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso(['grupos.pestana_georreferencia_grupo', 'grupos.opcion_georreferencia_grupo']);

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
      $ubicacionInicial  = collect(json_decode($ubicacionInicial))->first();
      $longitudInicial = ($ubicacionInicial && $ubicacionInicial->lon) ? $ubicacionInicial->lon : -72.9088133;
      $latitudInicial = ($ubicacionInicial && $ubicacionInicial->lat) ? $ubicacionInicial->lat : 4.099917;

      if ($iglesia->latitud && $iglesia->longitud) {
        $iglesia->latitud = $longitudInicial;
        $iglesia->longitud = $longitudInicial;
        $iglesia->save();
      }
    }

    $configuracion = Configuracion::find(1);
    if (!isset($grupo)) {
      return Redirect::to('pagina-no-encontrada');
    }

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    return view('contenido.paginas.grupos.georreferencia', [
      'rolActivo' => $rolActivo,
      'configuracion' => $configuracion,
      //'ubicacionInicial' => $ubicacionInicial,
      'longitudInicial' => $longitudInicial,
      'latitudInicial' => $latitudInicial,
      'grupo' => $grupo
    ]);
  }

  public function excluir(Grupo $grupo)
  {
    $usuarioId = auth()->user()->id;
    $cantidadGrupoExcluido = GrupoExcluido::where("user_id", $usuarioId)->where("grupo_id", $grupo->id)->count();

    if ($cantidadGrupoExcluido > 0) {
      return Redirect::back()->with(
        'danger',
        'Esta exclusión de este grupo ya se había sido creada anteriormente.'
      );
    } else {

      $exclusion = new GrupoExcluido;
      $exclusion->grupo_id = $grupo->id;
      $exclusion->user_id = $usuarioId;
      $exclusion->save();

      return Redirect::back()->with(
        'success',
        'La exclusión del grupo "' . $grupo->nombre . '" se creo con éxito.'
      );
    }
  }

  public function verExclusiones()
  {

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('grupos.subitem_excluir_asistentes_grupos');

    $queUsuariosCargar = $rolActivo->hasPermissionTo('personas.ajax_obtiene_asistentes_solo_ministerio')
      ? 'discipulos'
      : 'todos';

    return view('contenido.paginas.grupos.exclusiones', [
      'queUsuariosCargar' => $queUsuariosCargar
    ]);
  }

  public function crearExclusion(Request $request)
  {

    $request->validate([
      'grupo' => 'required',
      'usuario' => 'required'
    ], [
      'required' => '¡ups! no fue posible crear la exclusión debido a que no seleccionaste un :attribute.'
    ]);

    $cantidadGrupoExcluido = GrupoExcluido::where("user_id", $request->usuario)->where("grupo_id", $request->grupo)->count();

    if ($cantidadGrupoExcluido > 0) {
      return Redirect::back()->with(
        'danger',
        '¡ups! no fue posible esta acción, esta exclusión ya había sido creada anteriormente.'
      );
    } else {

      $grupo = Grupo::find($request->grupo);
      $exclusion = new GrupoExcluido;
      $exclusion->grupo_id = $request->grupo;
      $exclusion->user_id = $request->usuario;
      $exclusion->save();

      return Redirect::back()->with(
        'success',
        'La exclusión del grupo "' . $grupo->nombre . '" se creo con éxito.'
      );
    }

    return $request;
  }

  public function mapaDeGrupos(Request $request)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('grupos.subitem_mapa_grupos');

    $configuracion = Configuracion::find(1);
    $iglesia = Iglesia::find(1);
    $tiposDeViviendas =  TipoVivienda::orderBy('nombre', 'asc')->get();
    $tiposDeGrupo = TipoGrupo::orderBy('nombre', 'asc')->get();
    $sedes = Sede::get();
    $grupos = [];

    $parametrosBusqueda = [];
    $parametrosBusqueda['buscar'] = $request->buscar;
    $parametrosBusqueda['filtroGrupo'] = $request->filtroGrupo;
    $parametrosBusqueda['filtroPorTipoDeGrupo'] = $request->filtroPorTipoDeGrupo;
    $parametrosBusqueda['filtroPorSedes'] = $request->filtroPorSedes;
    $parametrosBusqueda['filtroPorTiposDeViviendas'] = $request->filtroPorTiposDeViviendas;
    $parametrosBusqueda['bandera'] = '';
    $parametrosBusqueda['textoBusqueda'] = '';
    $parametrosBusqueda = (object) $parametrosBusqueda;

    if ($rolActivo->hasPermissionTo('grupos.lista_grupos_todos') || $rolActivo->hasPermissionTo('grupos.lista_grupos_solo_ministerio') || $rolActivo->lista_grupos_sede_id != NULL) {
      if ($rolActivo->hasPermissionTo('grupos.lista_grupos_todos') || isset(auth()->user()->iglesiaEncargada()->first()->id)) {
        $grupos = Grupo::whereNotNull('latitud')
          ->whereNotNull('longitud')
          ->leftJoin('encargados_grupo', 'grupos.id', '=', 'encargados_grupo.grupo_id')
          ->leftJoin('users', 'users.id', '=', 'encargados_grupo.user_id')
          ->select('grupos.*', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', 'users.segundo_apellido')
          ->get()
          ->unique('id');
      }

      if ($rolActivo->hasPermissionTo('grupos.lista_grupos_solo_ministerio')) {
        $grupos = auth()->user()->gruposMinisterio()->whereNotNull('grupos.latitud')
          ->whereNotNull('grupos.longitud')->leftJoin('encargados_grupo', 'grupos.id', '=', 'encargados_grupo.grupo_id')
          ->leftJoin('users', 'users.id', '=', 'encargados_grupo.user_id')
          ->select('grupos.*', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', 'users.segundo_apellido')
          ->get()
          ->unique('id');
      }
    }

    // filtro por busqueda
    $grupos = $this->filtrosBusqueda($grupos, $parametrosBusqueda);

    if ($grupos->count() > 0) {
      $grupos = $grupos->toQuery()->orderBy('id', 'desc')->get();

      $grupoLast = $grupos->last();

      $longitudInicial = $grupoLast->longitud ? $grupoLast->longitud : -72.9088133;
      $latitudInicial =  $grupoLast->latitud ?  $grupoLast->latitud : 4.099917;
    } else {
      $grupos = Grupo::whereRaw('1=2')->get();
      $longitudInicial = $iglesia->longitud ? $iglesia->longitud : -72.9088133;
      $latitudInicial = $iglesia->latitud ? $iglesia->latitud : 4.099917;
    }

    return view('contenido.paginas.grupos.mapa-de-grupos', [
      'configuracion' => $configuracion,
      'rolActivo' => $rolActivo,
      'parametrosBusqueda' => $parametrosBusqueda,
      'grupos' => $grupos,
      'tiposDeGrupo' => $tiposDeGrupo,
      'sedes' => $sedes,
      'tiposDeViviendas' => $tiposDeViviendas,
      'longitudInicial' => $longitudInicial,
      'latitudInicial' => $latitudInicial
    ]);
  }

  public function graficoDelMinisterio($id_nodo = "U-logueado", $maximos_niveles = 3)
  {

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('grupos.subitem_grafico_ministerio');

    $configuracion = Configuracion::find(1);
    if ($maximos_niveles != 0) {
      $maximos_niveles = $configuracion->maximos_niveles_grafico_ministerio;
    }

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    $identificadores = explode("-", $id_nodo);
    $tipo_nodo = $identificadores[0];
    $tipoDeNodo = $identificadores[0];
    $id = $identificadores[1];
    $usuario_seleccionado = "";
    $grupo_seleccionado = "";

    $array_ids_usuarios_no_dibujados = array();

    $mensaje = "";
    $contador = 0;

    $tamano_nodo_grupo = 50;
    $tamano_nodo_general = 50;
    $factor_vision = 10;
    $inicio_fila = $factor_vision * -1;
    $distancia_nodos_usuario = 0;
    $distancia_nodos_grupo = 0;
    $nodos = [];
    $aristas = [];
    $x_usuario = 0;
    $x_grupo = 0;
    $x = 0;
    $y = 0;
    $y_grupo = -750000;
    $array_ids_usuarios = array();
    $array_ids_grupos_dibujados = array();
    $array_ids_usuarios_dibujados = array();
    $array_aristas_usuario_grupo_dibujadas = array();
    $array_aristas_grupo_usuario_dibujadas = array();

    $id_nulo = 1;
    $cantidad_usuarios_grupo = 0;

    $nombre_grupo = "";
    $tipo_dibujo_grupo = "circle";



    /// esto es para identificar de donde viene la primera consulta del grafico
    if ($tipo_nodo == "U") // este la primera vez que entra siempre sera por aca
    {
      if ($rolActivo->hasPermissionTo('grupos.grafico_ministerio_todos') || isset(auth()->user()->iglesiaEncargada()->first()->id)) {
        $iglesia = Iglesia::find(1);
        $array_ids_usuarios = $iglesia->pastoresEncargados()->select('users.id')->pluck('users.id')->toArray();
        $mensaje = "Ministerio General";
        $tipoDeNodo = $tipoDeNodo . "-principal";
      }

      if ($rolActivo->hasPermissionTo('grupos.grafico_ministerio_solo_ministerio')) {
        $tipoDeNodo = $tipoDeNodo . "-encargado";
        $usuario = auth()->user();
        $usuario_seleccionado = $usuario;
        array_push($array_ids_usuarios, $usuario->id);
        $mensaje = "Ministerio del " . $usuario->tipoUsuario->nombre . " <a href='/usuario/" . $usuario->id . "/perfil' target='_blank' >" . $usuario->nombre(3) . "</a>";
      }
      //// luego aca es si dieron click dentro de un nodo del grafico a un usuario o asistente
    } else if ($tipo_nodo == "A") {
      $tipoDeNodo = $tipoDeNodo . "-encargado";
      array_push($array_ids_usuarios, $id);
      $usuario = User::find($id);
      $mensaje = "Ministerio del " . $usuario->tipoUsuario->nombre . " <a href='/usuario/" . $usuario->id . "/perfil' target='_blank' >" . $usuario->nombre(3) . "</a>";
      //// luego aca es si dieron click dentro de un nodo del grafico a un grupo
    } else if ($tipo_nodo == "G") {
      $tipoDeNodo = $tipoDeNodo . "-grupo";
      $grupo = Grupo::find($id);
      $array_ids_usuarios = $grupo->encargados()->select('users.id')->pluck('users.id')->toArray();
      $mensaje = "Ministerio a partir del grupo <a href='/grupo/" . $grupo->id . "/perfil' target='_blank' >" . $grupo->codigo . " " . $grupo->nombre . "</a>";
    }

    if ($maximos_niveles != 20) {
      $mensaje = $mensaje . "<br>
      Actualmente se están visualizando solamente algunos niveles. Si deseas ver el árbol con el ministerio completo, ingresa <a style='color:#3c8dbc' href='/grupo/grafico-del-ministerio/U-logueado/20' >aquí</a>";
    }

    if ($tipo_nodo == "A") {
      $usuario_seleccionado = $usuario;
      $mensaje = $mensaje . "<br> El índice actual de <b>" . $usuario->nombre(3) . "</b> que permite establecer su ubicación en el gráfico es: </a> <b>" . $usuario->indice_grafico_ministerial . "</b>. Si deseas modificarlo, da click <a class='mostrar-div-indice-asistente' > aquí </a>";
    } else if ($tipo_nodo == "G") {
      $grupo_seleccionado = $grupo;
      $mensaje = $mensaje . "<br> El índice actual del grupo <b>" . $grupo->nombre . "</b> que permite establecer su ubicación en el gráfico es: </a> <b>" . $grupo->indice_grafico_ministerial . "</b>. Si deseas modificarlo, da click <a class='mostrar-div-indice-grupo' > aquí </a>";
    }

    $contador_maximos_niveles = 0;


    while (count($array_ids_usuarios) > 0 && $contador_maximos_niveles < $maximos_niveles) {
      $contador_maximos_niveles = $contador_maximos_niveles + 1;
      /// aqui lo que hacemos es que tenemos los usuario de cada recorrido por nivel
      $usuarios = User::orderBy('users.indice_grafico_ministerial', 'asc')->whereIn('users.id', $array_ids_usuarios)->get();
      /// aqui se resetea la variable para que siempre se llene solo con los usuarios de cada nivel mas abajo se rellena con esos usuarios
      $array_ids_usuarios = array();

      $idsGruposTemporal = [];
      foreach ($usuarios as $usuario) {
        if (!in_array($usuario->id, $array_ids_usuarios_dibujados)) {
          $urlFoto = $configuracion->version == 1
            ? Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuario->foto)
            : Storage::url($configuracion->ruta_almacenamiento . '/img/usuarios/foto-usuario/' . $usuario->foto);

          /// aqui se creo los
          $item = new stdClass();
          $item->id = 'A-' . $usuario->id;
          $item->image =  $urlFoto;
          $item->title = $usuario->tipoUsuario->nombre . ': ' . $usuario->nombre(3);
          $item->level = $contador;
          $item->color = $usuario->tipoUsuario->color;
          $item->shape = 'circularImage';
          $item->size = 20;
          $item->borderWidth = 5;
          $nodos[] = $item;

          array_push($array_ids_usuarios_dibujados, $usuario->id);

          $grupos_excluidos = array();
          if (isset(auth()->user()->id)) {
            $usuario_logueado = auth()->user();
            $grupos_excluidos = GrupoExcluido::where("user_id", $usuario_logueado->id)->select('grupo_id')->pluck('grupo_id')->toArray();
          }

          $gruposUsuario = $usuario->gruposEncargados()->where('grupos.dado_baja', '=', 0)->whereNotIn('grupos.id', $grupos_excluidos)->orderBy('grupos.indice_grafico_ministerial', 'asc')->get();
          $idsGruposTemporal = array_merge($idsGruposTemporal, $gruposUsuario->pluck('id')->toArray());

          // aqui recorremos los grupos que dirije el usuario o persona que estoy recorriendo y le creo la arista que conecta la persona con los grupos que dirije
          foreach ($gruposUsuario as $grupoUsuario) {
            if (!in_array("Ar-ag-'.$usuario->id.'_'.$grupoUsuario->id.'", $array_aristas_usuario_grupo_dibujadas)) {
              $item = new stdClass();
              $item->from = 'A-' . $usuario->id;
              $item->to = 'G-' . $grupoUsuario->id;
              //$item->color= '#000';
              $item->width = 3;
              $aristas[] = $item;

              array_push($array_aristas_usuario_grupo_dibujadas, "Ar-ag-'.$usuario->id.'_'.$grupoUsuario->id.'");
            }
          }
        } else {
          array_push($array_ids_usuarios_no_dibujados, $usuario->id);
        }
      }
      //// aqui obtengo los grupos que dirije pero los voy a recorrer para graficar los nodos de los grupos
      $grupos = Grupo::whereIn('grupos.id', $idsGruposTemporal)->select('id', 'tipo_grupo_id', 'nombre')->get();

      $contador = count($idsGruposTemporal) > 0 ? $contador + 1 : '';

      foreach ($grupos as $grupo) {
        if (!in_array($grupo->id, $array_ids_grupos_dibujados)) {
          if (Sede::where('grupo_id', '=', $grupo->id)->select('sede.id')->count() > 0) {
            $nombre_grupo = 'Sede: ' . Sede::where('grupo_id', '=', $grupo->id)->first()->nombre . ' - ' . $grupo->tipoGrupo->nombre . ': ' . $grupo->nombre;
            $tipo_dibujo_grupo = "box";
          } else {
            $nombre_grupo = $grupo->tipoGrupo->nombre . ': ' . $grupo->nombre;
            $tipo_dibujo_grupo = "circle";
          }
          // aqui adentro creo el nodo del grupo
          $item = new stdClass();
          $item->id = 'G-' . $grupo->id;
          $item->color = $grupo->tipoGrupo->color;
          $item->title = $nombre_grupo;
          $item->level = $contador;
          $item->shape = $tipo_dibujo_grupo;
          $item->size = 20;
          $nodos[] = $item;

          array_push($array_ids_grupos_dibujados, $grupo->id);
          $personasGrupo = $grupo->asistentes()->orderBy('users.indice_grafico_ministerial', 'asc')->select('users.id')->pluck('users.id')->toArray();
          foreach ($personasGrupo as $personaGrupoId) {
            if (!in_array("Ar-ga-'.$grupo->id.'_'.$personaGrupoId.'", $array_aristas_usuario_grupo_dibujadas)) {
              if (!in_array($personaGrupoId, $array_ids_usuarios)) {
                ///esta es la arista que conecta el grupo con las personas que asisten a ese grupo
                $item = new stdClass();
                $item->from = 'G-' . $grupo->id;
                $item->to = 'A-' . $personaGrupoId;
                $item->color = '{ color: "red" }';
                $item->width = 3;
                $aristas[] = $item;

                array_push($array_aristas_usuario_grupo_dibujadas, "Ar-ga-'.$grupo->id.'_'.$personaGrupoId.'");
              } else {
                array_push($array_ids_usuarios_no_dibujados, $personaGrupoId);
              }
            }
          }
          ////aqui es donde guardo los usuarios que necesito para el siguiente recorrido del proximo nivel y asi se repite el ciclo

          $array_ids_usuarios = array_merge($array_ids_usuarios, $personasGrupo);
        }
      }

      $contador++;
      $tipo_nodo = "modificado";
    }

    // return $nodos;

    $usuarios_no_dibujados = User::whereIn("id", $array_ids_usuarios_no_dibujados)->get();

    return view('contenido.paginas.grupos.grafico-del-ministerio', [
      'nodos' => $nodos,
      'aristas' => $aristas,
      'mensaje' => $mensaje,
      'usuario_seleccionado' => $usuario_seleccionado,
      'grupo_seleccionado' => $grupo_seleccionado,
      'array_ids_usuarios_no_dibujados' => $array_ids_usuarios_no_dibujados,
      'usuarios_no_dibujados' => $usuarios_no_dibujados,
      'tipoDeNodo' => $tipoDeNodo,
      'maximos_niveles' => $maximos_niveles,
      'configuracion' => $configuracion,
      'rolActivo' => $rolActivo
    ]);
  }

  public function cambiarIndice(Request $request, $tipo, $id)
  {
    if ($tipo == "grupo") {
      $grupo = Grupo::find($id);
      $grupo->indice_grafico_ministerial = $request->cambioIndice;
      $grupo->save();

      return back()->with('success', "El índice del grupo <b>" . $grupo->nombre . "</b> se actualizó a  <b>" . $grupo->indice_grafico_ministerial . "</b> con éxito.");
    } elseif ($tipo == "usuario") {
      $usuario = User::find($id);
      $usuario->indice_grafico_ministerial = $request->cambioIndice;
      $usuario->save();

      return back()->with('success', "El índice de <b>" . $usuario->nombre(3) . "</b> se actualizó a <b>" . $usuario->indice_grafico_ministerial . "</b> con éxito.");
    }
  }


  public function cambiarPortada(Request $request, Grupo $grupo)
  {
    $configuracion = Configuracion::find(1);
    if ($request->foto) {
      if ($configuracion->version == 1) {

        $path = public_path('storage/' . $configuracion->ruta_almacenamiento . '/img/grupos/');
        !is_dir($path) && mkdir($path, 0777, true);

        $imagenPartes = explode(';base64,', $request->foto);
        $imagenBase64 = base64_decode($imagenPartes[1]);
        $nombreFoto = 'grupo' . $grupo->id . '.png';
        $imagenPath = $path . $nombreFoto;
        file_put_contents($imagenPath, $imagenBase64);
        $grupo->portada = $nombreFoto;
      } else {
        /*
        $s3 = AWS::get('s3');
        $s3->putObject(array(
          'Bucket'     => $_ENV['aws_bucket'],
          'Key'        => $_ENV['aws_carpeta']."/fotos/asistente-".$asistente->id.".jpg",
          'SourceFile' => "img/temp/".Input::get('foto-hide'),
        ));*/
      }
      $grupo->save();
    }

    return back()->with('success', "La foto de perfil de <b>" . $grupo->nombre(3) . "</b> fue actualizada con éxito.");
  }
}
