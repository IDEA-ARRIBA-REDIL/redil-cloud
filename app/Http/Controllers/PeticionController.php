<?php

namespace App\Http\Controllers;

use App\Exports\PeticionesExport;
use App\Exports\DetallePeticionesKpiExport;
use App\Helpers\Helpers;
use App\Mail\DefaultMail;
use App\Models\CampoExtra;
use App\Models\CampoInformeExcel;
use App\Models\Configuracion;
use App\Models\Pais;
use App\Models\PasoCrecimiento;
use App\Models\Peticion;
use App\Models\SeguimientoPeticion;
use App\Models\Sede;
use App\Models\TipoPeticion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Exception;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use \stdClass;

class PeticionController extends Controller
{

  public function publicaNueva(Request $request)
  {
    $referer = $request->headers->get('referer');
    $hostReferer = $referer ? parse_url($referer, PHP_URL_HOST) : null;
    $hostActual = $request->getHost();

    if ($request->has('url_retorno')) {
      session(['peticion_retorno_url' => $request->query('url_retorno')]);
    } elseif ($referer && $hostReferer && $hostReferer !== $hostActual) {
      session(['peticion_retorno_url' => $referer]);
    }

    $configuracion = Configuracion::find(1);
    $paises = Pais::all();
    $tiposPeticiones = TipoPeticion::orderBy('orden', 'asc')->get();

    return view('contenido.paginas.peticiones.publica', [
      'configuracion' => $configuracion,
      'paises' => $paises,
      'tiposPeticiones' => $tiposPeticiones,
    ]);
  }

  public function dashboard(Request $request): View
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('peticiones.subitem_gestionar_peticiones');

    $peticiones = collect();
    if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_todas') || $rolActivo->hasPermissionTo('peticiones.lista_peticiones_solo_ministerio')) {
      if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_solo_ministerio')) {
        $peticiones = auth()->user()->misPeticiones();
      }

      if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_todas')) {
        $peticiones = Peticion::leftJoin('users', 'peticiones.user_id', '=', 'users.id')
          ->select('peticiones.*', 'users.foto', 'users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', 'users.sede_id', \DB::raw('COALESCE(users.genero, peticiones.genero_externo) as genero'))
          ->get();
      }

      // Evitar problema N+1 usando eager loading en la colección cargada
      $peticiones->load(['pais', 'tipoPeticion']);
    }

    // Lógica para Rango de Fechas
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
      // Por defecto: Este mes
      $inicio = Carbon::now()->startOfMonth();
      $fin = Carbon::now()->endOfMonth();
      $rangoFechas = $inicio->format('Y-m-d') . ' a ' . $fin->format('Y-m-d');
    }

    // Filtrar peticiones por fecha
    $peticionesFiltradas = $peticiones->filter(function ($peticion) use ($inicio, $fin) {
      if (!$peticion->fecha) {
        return false;
      }
      $fechaPeticion = Carbon::parse($peticion->fecha);
      return $fechaPeticion->between($inicio, $fin);
    });

    // Calcular KPIs
    $totalPeticiones = $peticionesFiltradas->count();
    $pendientes = $peticionesFiltradas->where('estado', 1)->count();
    $enProceso = $peticionesFiltradas->where('estado', 3)->count();
    $cerradas = $peticionesFiltradas->where('estado', 2)->count();
    $sinAsignar = $peticionesFiltradas->whereNull('asignacion_peticion_id')->count();

    // Calcular proporción de usuarios registrados vs externos
    $registrados = $peticionesFiltradas->whereNotNull('user_id')->count();
    $externos = $peticionesFiltradas->whereNull('user_id')->count();

    // Calcular diferencia en días para decidir la granularidad del gráfico histórico
    $diasDiferencia = $inicio->diffInDays($fin);
    $datosGraficaLineas = [];

    if ($diasDiferencia <= 30) {
      // Agrupación Diaria
      $current = $inicio->copy();
      while ($current->lte($fin)) {
        $fechaStr = $current->format('Y-m-d');
        $totalDia = $peticionesFiltradas->filter(function ($p) use ($fechaStr) {
          return Carbon::parse($p->fecha)->format('Y-m-d') === $fechaStr;
        })->count();

        $datosGraficaLineas[] = [
          'x' => $current->format('d-m'),
          'y' => $totalDia
        ];
        $current->addDay();
      }
    } else {
      // Agrupación Mensual
      $current = $inicio->copy()->startOfMonth();
      $finMes = $fin->copy()->endOfMonth();
      $mesesNombres = [
        1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
        7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
      ];
      while ($current->lte($finMes)) {
        $anoMes = $current->format('Y-m');
        $totalMes = $peticionesFiltradas->filter(function ($p) use ($anoMes) {
          return Carbon::parse($p->fecha)->format('Y-m') === $anoMes;
        })->count();

        $datosGraficaLineas[] = [
          'x' => $mesesNombres[$current->month] . ' ' . $current->format('Y'),
          'y' => $totalMes
        ];
        $current->addMonth();
      }
    }

    // Agrupar por países
    $peticionesPorPais = $peticionesFiltradas->groupBy('pais_id')
      ->map(function ($items, $paisId) {
        $pais = $items->first()->pais;
        if (!$pais && $paisId) {
          $pais = Pais::find($paisId);
        }
        return [
          'id' => $paisId,
          'nombre' => $pais ? $pais->nombre : 'Sin especificar',
          'codigo_alpha' => $pais ? strtolower($pais->codigo_alpha) : '',
          'total' => $items->count()
        ];
      })
      ->sortByDesc('total')
      ->values();

    // Agrupar por tipos de petición
    $peticionesPorTipo = $peticionesFiltradas->groupBy('tipo_peticion_id')
      ->map(function ($items, $tipoId) {
        $tipo = $items->first()->tipoPeticion;
        if (!$tipo && $tipoId) {
          $tipo = TipoPeticion::find($tipoId);
        }
        return [
          'id' => $tipoId,
          'nombre' => $tipo ? $tipo->nombre : 'Sin especificar',
          'total' => $items->count()
        ];
      })
      ->sortByDesc('total')
      ->values();

    return view('contenido.paginas.peticiones.dashboard', [
      'rolActivo' => $rolActivo,
      'rangoFechas' => $rangoFechas,
      'totalPeticiones' => $totalPeticiones,
      'pendientes' => $pendientes,
      'enProceso' => $enProceso,
      'cerradas' => $cerradas,
      'sinAsignar' => $sinAsignar,
      'registrados' => $registrados,
      'externos' => $externos,
      'datosGraficaLineas' => $datosGraficaLineas,
      'diasDiferencia' => $diasDiferencia,
      'peticionesPorPais' => $peticionesPorPais,
      'peticionesPorTipo' => $peticionesPorTipo,
      'configuracion' => Configuracion::find(1),
    ]);
  }

  private function getDatosFiltradosDetalleKpi(Request $request)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('peticiones.subitem_gestionar_peticiones');

    $peticiones = collect();
    if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_todas') || $rolActivo->hasPermissionTo('peticiones.lista_peticiones_solo_ministerio')) {
      if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_solo_ministerio')) {
        $peticiones = auth()->user()->misPeticiones();
      }

      if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_todas')) {
        $peticiones = Peticion::leftJoin('users', 'peticiones.user_id', '=', 'users.id')
          ->select('peticiones.*', 'users.foto', 'users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', 'users.sede_id', \DB::raw('COALESCE(users.genero, peticiones.genero_externo) as genero'))
          ->get();
      }
    }

    // Filtro por Fechas
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
      $inicio = Carbon::now()->startOfMonth();
      $fin = Carbon::now()->endOfMonth();
      $rangoFechas = $inicio->format('Y-m-d') . ' a ' . $fin->format('Y-m-d');
    }

    $peticionesFiltradas = $peticiones->filter(function ($peticion) use ($inicio, $fin) {
      if (!$peticion->fecha) {
        return false;
      }
      $fechaPeticion = Carbon::parse($peticion->fecha);
      return $fechaPeticion->between($inicio, $fin);
    });

    // Filtro por KPI
    $kpi = $request->kpi;
    if ($kpi && $kpi !== 'total') {
      switch ($kpi) {
        case 'pendientes':
          $peticionesFiltradas = $peticionesFiltradas->where('estado', 1);
          break;
        case 'en_proceso':
          $peticionesFiltradas = $peticionesFiltradas->where('estado', 3);
          break;
        case 'cerradas':
          $peticionesFiltradas = $peticionesFiltradas->where('estado', 2);
          break;
        case 'sin_asignar':
          $peticionesFiltradas = $peticionesFiltradas->whereNull('asignacion_peticion_id');
          break;
      }
    }

    // Filtro por País
    $paisId = $request->pais_id;
    if ($paisId) {
      $peticionesFiltradas = $peticionesFiltradas->where('pais_id', $paisId);
    }

    // Filtro por Tipo de Petición
    $tipoPeticionId = $request->tipo_peticion_id;
    if ($tipoPeticionId) {
      $peticionesFiltradas = $peticionesFiltradas->where('tipo_peticion_id', $tipoPeticionId);
    }

    // Búsqueda por palabra clave (buscar)
    if ($request->filled('buscar')) {
      $buscar = strtolower(trim($request->buscar));
      $peticionesFiltradas = $peticionesFiltradas->filter(function ($p) use ($buscar) {
        $nombre = '';
        if ($p->user_id) {
          $nombre = $p->primer_nombre . ' ' . $p->segundo_nombre . ' ' . $p->primer_apellido;
        } else {
          $nombre = $p->nombre_externo;
        }
        $nombre = strtolower($nombre);

        $email = strtolower($p->user_id ? $p->email : $p->email_externo);

        $telefono = '';
        if ($p->user_id) {
          $telefono = $p->telefono_fijo . ' ' . $p->telefono_movil . ' ' . $p->telefono_otro;
        } else {
          $telefono = $p->telefono_externo;
        }
        $telefono = strtolower($telefono);

        $descripcion = strtolower($p->descripcion);

        return str_contains($nombre, $buscar) ||
               str_contains($email, $buscar) ||
               str_contains($telefono, $buscar) ||
               str_contains($descripcion, $buscar);
      });
    }

    return [
      'peticionesFiltradas' => $peticionesFiltradas,
      'kpi' => $kpi,
      'rangoFechas' => $rangoFechas,
      'paisId' => $paisId,
      'tipoPeticionId' => $tipoPeticionId,
    ];
  }

  public function detalleKpi(Request $request): View
  {
    $datos = $this->getDatosFiltradosDetalleKpi($request);
    $peticionesFiltradas = $datos['peticionesFiltradas'];

    // Paginación o vacía
    if ($peticionesFiltradas->isNotEmpty()) {
      $peticionesPaginadas = $peticionesFiltradas->toQuery()
        ->leftJoin('users', 'peticiones.user_id', '=', 'users.id')
        ->select('peticiones.*', 'users.foto', 'users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', 'users.sede_id')
        ->with(['pais', 'tipoPeticion', 'asignado'])
        ->orderBy('peticiones.id', 'desc')
        ->paginate(25)
        ->withQueryString();
    } else {
      $peticionesPaginadas = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);
    }

    $paisDetalle = $datos['paisId'] ? Pais::find($datos['paisId']) : null;
    $tipoDetalle = $datos['tipoPeticionId'] ? TipoPeticion::find($datos['tipoPeticionId']) : null;

    return view('contenido.paginas.peticiones.detalle-kpi', [
      'peticiones' => $peticionesPaginadas,
      'kpi' => $datos['kpi'],
      'rangoFechas' => $datos['rangoFechas'],
      'paisId' => $datos['paisId'],
      'paisDetalle' => $paisDetalle,
      'tipoPeticionId' => $datos['tipoPeticionId'],
      'tipoDetalle' => $tipoDetalle,
      'buscar' => $request->buscar
    ]);
  }

  public function exportarDetalleKpi(Request $request)
  {
    $datos = $this->getDatosFiltradosDetalleKpi($request);
    $peticionesFiltradas = $datos['peticionesFiltradas'];

    if ($peticionesFiltradas->isNotEmpty()) {
      $peticionesCompletas = $peticionesFiltradas->toQuery()
        ->leftJoin('users', 'peticiones.user_id', '=', 'users.id')
        ->select('peticiones.*', 'users.foto', 'users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', 'users.sede_id')
        ->with(['pais', 'tipoPeticion', 'asignado'])
        ->orderBy('peticiones.id', 'desc')
        ->get();
    } else {
      $peticionesCompletas = collect();
    }

    $paisDetalle = $datos['paisId'] ? Pais::find($datos['paisId']) : null;
    $tipoDetalle = $datos['tipoPeticionId'] ? TipoPeticion::find($datos['tipoPeticionId']) : null;

    $exportDatos = [
      'kpi' => $datos['kpi'],
      'rangoFechas' => $datos['rangoFechas'],
      'paisDetalle' => $paisDetalle,
      'tipoDetalle' => $tipoDetalle,
    ];

    return Excel::download(
      new DetallePeticionesKpiExport($peticionesCompletas, $exportDatos),
      'detalle_kpi_dashboard_peticiones.xlsx'
    );
  }

  public function gestionar(Request $request, $tipo = 'pendientes')
  {

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    // verificar si cumple el permiso
    $rolActivo->verificacionDelPermiso('peticiones.subitem_gestionar_peticiones');

    $camposPeticiones = Helpers::camposPeticiones();
    $configuracion = Configuracion::find(1);
    $tiposPeticiones = TipoPeticion::orderBy('orden', 'asc')->get();
    $paises = Pais::select('id', 'nombre')->orderBy('nombre', 'asc')->get();
    $sedes = Sede::select('id', 'nombre')->orderBy('nombre', 'asc')->get();
    $peticiones = collect();
    $indicadores = [];
    $buscar = '';
    $textoBusqueda = '';
    $tagsBusqueda = [];
    $bandera = 0;
    $persona = null;

    $queUsuariosCargar = $rolActivo->hasPermissionTo('personas.ajax_obtiene_asistentes_solo_ministerio')
      ? 'discipulos'
      : 'todos';

    if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_todas') || $rolActivo->hasPermissionTo('peticiones.lista_peticiones_solo_ministerio')) {
      if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_solo_ministerio')) {
        $peticiones = auth()->user()->misPeticiones();
      }

      if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_todas')) {
        $peticiones = Peticion::leftJoin('users', 'peticiones.user_id', '=', 'users.id')
          ->select('peticiones.*', 'users.foto', 'users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', 'users.sede_id', \DB::raw('COALESCE(users.genero, peticiones.genero_externo) as genero'))
          ->get();
      }
    }

    $item = new stdClass();
    $item->nombre = 'Pendientes';
    $item->url = 'pendientes';
    $item->cantidad = $peticiones->where('estado', 1)->count();
    $item->color = 'bg-label-primary';
    $item->imagen = Storage::disk('global_media')->url('peticiones/indicadores/pendientes.png');
    $item->icono = 'ti ti-clock';
    $indicadores[] = $item;

    $item = new stdClass();
    $item->nombre = 'En proceso';
    $item->url = 'en-proceso';
    $item->cantidad = $peticiones->where('estado', 3)->count();
    $item->color = 'bg-label-info';
    $item->imagen =  Storage::disk('global_media')->url('peticiones/indicadores/en_proceso.png');
    $item->icono = 'ti ti-loader';
    $indicadores[] = $item;

    $item = new stdClass();
    $item->nombre = 'Cerradas';
    $item->url = 'cerradas';
    $item->cantidad = $peticiones->where('estado', 2)->count();
    $item->color = 'bg-label-success';
    $item->imagen = Storage::disk('global_media')->url('peticiones/indicadores/cerradas.png');
    $item->icono = 'ti ti-check';
    $indicadores[] = $item;

    $indicadores = collect($indicadores);

    if ($tipo == 'pendientes' || $tipo == 'sin-responder') {
      $peticiones = $peticiones->where('estado', 1);
      $textoBusqueda .= '<b> Tipo: </b>"Pendientes"';
    } elseif ($tipo == 'cerradas' || $tipo == 'finalizadas') {
      $peticiones = $peticiones->where('estado', 2);
      $textoBusqueda .= '<b> Tipo: </b>"Cerradas"';
    } elseif ($tipo == 'en-proceso' || $tipo == 'resueltas' || $tipo == 'con-seguimiento') {
      $peticiones = $peticiones->where('estado', 3);
      $textoBusqueda .= '<b> Tipo: </b>"En proceso"';
    }

    // Filtro por persona
    if ($request->persona_id) {
      $peticiones = $peticiones->whereIn('user_id', $request->persona_id);
      $persona = User::withTrashed()->select('id', 'primer_nombre', 'segundo_nombre', 'primer_apellido')->find($request->persona_id);
      $textoBusqueda .= '<b>, Peticiones de: </b>"' . $persona->nombre(3) . '"';
      $bandera = 1;

      // Tag por persona
      $tag = new stdClass();
      $tag->label = $persona->nombre(3);
      $tag->field = 'persona_id';
      $tag->value = $persona->id;
      $tag->fieldAux = '';
      $tagsBusqueda[] = $tag;
    }

    // filtro por tipo peticiones
    $filtroTipoPeticiones = [];
    if ($request->filtroTipoPeticiones) {
      $filtroTipoPeticiones = $request->filtroTipoPeticiones;
      $peticiones = $peticiones->whereIn('tipo_peticion_id', $request->filtroTipoPeticiones);

      $tps = TipoPeticion::whereIn('id', $request->filtroTipoPeticiones)
        ->select('nombre')
        ->pluck('nombre')
        ->toArray();

      $textoBusqueda .= '<b>, Tipo de peticiones: </b>"' . implode(', ', $tps) . '"';
      $bandera = 1;

      $tiposDePeticiones = TipoPeticion::whereIn('id', $request->filtroTipoPeticiones)->select('id','nombre')->get();
      foreach( $tiposDePeticiones as $tipo)
      {
        // Tag por tipo de grupo
        $tag = new stdClass();
        $tag->label = $tipo->nombre;
        $tag->field = 'filtroTipoPeticiones';
        $tag->value = $tipo->id;
        $tag->fieldAux = '';
        $tagsBusqueda[] = $tag;
      }
    }

    // filtro por paises
    $filtroPaises = [];
    if ($request->filtroPaises) {
      $filtroPaises = $request->filtroPaises;
      $peticiones = $peticiones->whereIn('pais_id', $request->filtroPaises);

      $textoPaises = Pais::whereIn('id', $request->filtroPaises)
        ->select('nombre')
        ->pluck('nombre')
        ->toArray();

      $textoBusqueda .= '<b>, Paises: </b>"' . implode(', ', $textoPaises) . '"';
      $bandera = 1;
    }

    // filtro por sedes
    $filtroSedes = [];
    if ($request->filtroSedes) {
      $filtroSedes = $request->filtroSedes;
      $peticiones = $peticiones->whereIn('sede_id', $request->filtroSedes);

      $textoSedes = Sede::whereIn('id', $request->filtroSedes)
        ->select('nombre')
        ->pluck('nombre')
        ->toArray();

      $textoBusqueda .= '<b>, Sedes: </b>"' . implode(', ', $textoSedes) . '"';
      $bandera = 1;

      $sedesFiltro = Sede::whereIn('id', $request->filtroSedes)->select('id', 'nombre')->get();
      foreach ($sedesFiltro as $sede) {
        $tag = new stdClass();
        $tag->label = $sede->nombre;
        $tag->field = 'filtroSedes';
        $tag->value = $sede->id;
        $tag->fieldAux = '';
        $tagsBusqueda[] = $tag;
      }
    }

    // filtro por asignacion
    $filtroAsignacion = '';
    if ($request->filtroAsignacion) {
      $filtroAsignacion = $request->filtroAsignacion;
      if ($filtroAsignacion === 'asignadas') {
        $peticiones = $peticiones->whereNotNull('asignacion_peticion_id');
        $labelAsignacion = 'Asignadas';
      } elseif ($filtroAsignacion === 'sin_asignar') {
        $peticiones = $peticiones->whereNull('asignacion_peticion_id');
        $labelAsignacion = 'Sin asignar';
      } elseif ($filtroAsignacion === 'asignadas_a_mi') {
        $peticiones = $peticiones->where('asignacion_peticion_id', auth()->id());
        $labelAsignacion = 'Asignadas a mí';
      }

      if (isset($labelAsignacion)) {
        $textoBusqueda .= '<b>, Estado de asignación: </b>"' . $labelAsignacion . '"';
        $bandera = 1;

        $tag = new stdClass();
        $tag->label = $labelAsignacion;
        $tag->field = 'filtroAsignacion';
        $tag->value = $filtroAsignacion;
        $tag->fieldAux = '';
        $tagsBusqueda[] = $tag;
      }
    }

    // filtro por rango de fecha
    $filtroFechaIni = $request->filtroFechaIni ? $request->filtroFechaIni : Carbon::now()->firstOfYear()->format('Y-m-d');
    $filtroFechaFin = $request->filtroFechaFin ? $request->filtroFechaFin : Carbon::now()->format('Y-m-d');
    $peticiones = $peticiones->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin]);
    $textoBusqueda .= '<b>, Rango </b> Del ' . $filtroFechaIni . ' al ' . $filtroFechaFin;


    // Busqueda por palabra clave
    if ($request->buscar) {
      $buscar = htmlspecialchars($request->buscar);
      $buscar = Helpers::sanearStringConEspacios($buscar);
      $buscar = str_replace(["'"], '', $buscar);
      $buscar_array = explode(' ', $buscar);

      foreach ($buscar_array as $palabra) {
        $peticiones = $peticiones->filter(function ($peticion) use ($palabra) {
          $respuesta  = false !== stristr(Helpers::sanearStringConEspacios($peticion->primer_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->segundo_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->primer_apellido), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->segundo_apellido), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->identificacion), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->direccion), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->telefono_movil), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->email), $palabra);

          return $respuesta;
        });
      }

      $buscar = $request->buscar;
      $textoBusqueda .=  '<b>, Con busqueda: </b>"' . $buscar . '" ';
      $bandera = 1;
    }

    if ($peticiones->count() > 0) {

      $peticiones = $peticiones->toQuery()
        ->with([
          'autorCreacion' => function ($query) {
            $query->withTrashed();
          },
          'asignado' => function ($query) {
            $query->withTrashed();
          }
        ])
        ->leftJoin('users', 'peticiones.user_id', '=', 'users.id')
        ->select('peticiones.*', 'users.foto', 'users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', 'users.sede_id', \DB::raw('COALESCE(users.genero, peticiones.genero_externo) as genero'))
        ->orderBy('peticiones.id', 'desc')->paginate(12);
      $peticiones->map(function ($peticion) {

        if ($peticion->user_id) {
          $peticion->nombreUsuario = $peticion->primer_nombre . ' ' . $peticion->segundo_nombre . ' ' . $peticion->primer_apellido;
          $peticion->fotoUsuario = $peticion->foto;

          $telefonosArray = [];
          $peticion->telefono_fijo ? array_push($telefonosArray, $peticion->telefono_fijo) : '';
          $peticion->telefono_movil ? array_push($telefonosArray, $peticion->telefono_movil) : '';
          $peticion->telefono_otro ? array_push($telefonosArray, $peticion->telefono_otro) : '';

          $peticion->telefonosUsuario = $telefonosArray && is_array($telefonosArray) ? implode(", ", $telefonosArray) : ' Sin datos';
          $peticion->emailUsuario = $peticion->email ?: 'Sin dato';
        } else {
          $peticion->nombreUsuario = $peticion->nombre_externo . ' (Externo)';
          $peticion->fotoUsuario = null;
          $peticion->telefonosUsuario = $peticion->telefono_externo ?: 'Sin dato';
          $peticion->emailUsuario = $peticion->email_externo ?: 'Sin dato';
        }

        // usuarioCreacion =
        $usuarioCreacion = $peticion->autorCreacion;
        $peticion->usuarioCreacion = ($usuarioCreacion && $peticion->user_id != $usuarioCreacion->id)
          ? $usuarioCreacion->nombre(3)
          : 'Autogestión';

        // usuarioAsignado =
        $usuarioAsignado = $peticion->asignado;
        $peticion->usuarioAsignadoNombre = $usuarioAsignado
          ? $usuarioAsignado->nombre(3)
          : 'Sin asignar';
      });
    } else {
      $peticiones = User::whereRaw('1=2')->paginate(1);
    }

    $camposInformeExcel = CampoInformeExcel::orderBy('orden', 'asc')->get();
    $pasosCrecimiento = PasoCrecimiento::orderBy('updated_at', 'asc')->get();
    $camposExtras = CampoExtra::where('visible', '=', true)->get();
    $meses = Helpers::meses('largo');


    return view('contenido.paginas.peticiones.gestionar', [
      'rolActivo' => $rolActivo,
      'peticiones' => $peticiones,
      'configuracion' => $configuracion,
      'indicadores' => $indicadores,
      'tipo' => $tipo,
      'tiposPeticiones' => $tiposPeticiones,
      'filtroTipoPeticiones' => $filtroTipoPeticiones,
      'buscar' => $buscar,
      'filtroFechaIni' => $filtroFechaIni,
      'filtroFechaFin' => $filtroFechaFin,
      'textoBusqueda' => $textoBusqueda,
      'tagsBusqueda' => $tagsBusqueda,
      'bandera' =>  $bandera,
      'queUsuariosCargar' => $queUsuariosCargar,
      'persona' => $persona,
      'camposInformeExcel' => $camposInformeExcel,
      'pasosCrecimiento' => $pasosCrecimiento,
      'camposExtras' => $camposExtras,
      'camposPeticiones' => $camposPeticiones,
      'paises' => $paises,
      'filtroPaises' => $filtroPaises,
      'sedes' => $sedes,
      'filtroSedes' => $filtroSedes,
      'filtroAsignacion' => $filtroAsignacion,
      'meses' => $meses
    ]);
  }

  public function nueva()
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    // verificar si cumple el permiso
    $rolActivo->verificacionDelPermiso('peticiones.subitem_nueva_peticion');

    $queUsuariosCargar = $rolActivo->hasPermissionTo('personas.ajax_obtiene_asistentes_solo_ministerio')
      ? 'discipulos'
      : 'todos';

    $tiposPeticiones = TipoPeticion::orderBy('orden', 'asc')->get();
    $paises = Pais::orderBy('nombre', 'asc')->get();

    $crearPeticionOtros = $rolActivo->hasPermissionTo('peticiones.crear_peticion_otros');

    return view('contenido.paginas.peticiones.nueva', [
      'rolActivo' => $rolActivo,
      'queUsuariosCargar' => $queUsuariosCargar,
      'tiposPeticiones'  => $tiposPeticiones,
      'paises' => $paises,
      'crearPeticionOtros' => $crearPeticionOtros
    ]);
  }

  public function exito(Request $request, Peticion $peticion)
  {
    if (session('peticion_reciente_id') != $peticion->id) {
      if (auth()->check()) {
        return redirect()->route('dashboard');
      } else {
        return redirect()->route('peticion.publica.nueva');
      }
    }

    $origen = $request->query('origen', 'interna');

    return view('contenido.paginas.peticiones.mensaje-peticion-exitosa', [
      'peticion' => $peticion,
      'origen' => $origen
    ]);
  }

  public function verificarCorreo(Request $request): \Illuminate\Http\JsonResponse
  {
    $email = trim($request->input('email'));
    if (empty($email)) {
      return response()->json(['exists' => false]);
    }

    $emailNorm = strtolower($email);
    $user = User::whereRaw('LOWER(email) = ?', [$emailNorm])->first();

    if ($user) {
      $nombreCompleto = method_exists($user, 'nombre') ? $user->nombre(3) : trim($user->primer_nombre . ' ' . $user->segundo_nombre . ' ' . $user->primer_apellido . ' ' . $user->segundo_apellido);
      return response()->json([
        'exists' => true,
        'user' => [
          'id' => $user->id,
          'nombre' => $nombreCompleto,
          'email' => $user->email,
          'foto_url' => $user->foto_url,
        ]
      ]);
    }

    return response()->json(['exists' => false]);
  }

  public function crear(Request $request)
  {
    $user = auth()->user();
    $crearPeticionOtros = false;

    if ($user) {
      $rolActivo = $user->roles()->wherePivot('activo', true)->first();
      $crearPeticionOtros = $rolActivo ? $rolActivo->hasPermissionTo('peticiones.crear_peticion_otros') : false;
    }

    $asociarUsuarioId = $request->input('asociar_usuario_id');
    $usuarioAsociado = null;
    if (!auth()->check() && $asociarUsuarioId) {
      $usuarioAsociado = User::find($asociarUsuarioId);
    }

    // Detectar si la petición viene del formulario público o del panel administrativo.
    $esPeticionPublica = $request->routeIs('peticion.publica.crear');

    // Reglas de validación para la petición
    $reglas = [
      'persona' => 'nullable|integer',
      'tipo_de_petición' => 'required',
      'descripción' => 'required'
    ];

    $mensajes = [
      'tipo_de_petición.required' => 'El motivo de tu petición es obligatorio.',
      'descripción.required' => 'La descripción de tu petición es obligatoria.',
    ];

    // Si es petición pública
    if ($esPeticionPublica) {
      if ($usuarioAsociado) {
        $reglas['g-recaptcha-response'] = ['required', new \App\Rules\Recaptcha];
        $mensajes['g-recaptcha-response.required'] = 'Por favor, verifica que no eres un robot.';
      } else {
        $reglas['nombre_externo'] = 'required|string|max:255';
        $reglas['email_externo'] = 'required|email|max:255';
        $reglas['telefono_externo'] = 'nullable|string|max:50';
        $reglas['genero_externo'] = 'required|in:0,1';
        $reglas['pais_id'] = 'required|integer';
        $reglas['g-recaptcha-response'] = ['required', new \App\Rules\Recaptcha];

        $mensajes['nombre_externo.required'] = 'El nombre completo es obligatorio.';
        $mensajes['email_externo.required'] = 'El correo electrónico es obligatorio.';
        $mensajes['email_externo.email'] = 'Por favor, ingresa un correo electrónico válido.';
        $mensajes['genero_externo.required'] = 'El género es obligatorio.';
        $mensajes['pais_id.required'] = 'El país es obligatorio.';
        $mensajes['g-recaptcha-response.required'] = 'Por favor, verifica que no eres un robot.';
      }
    } else {
      // Si está autenticado y tiene el permiso de crear para otros (flujo administrativo)
      if (auth()->check()) {
        if ($crearPeticionOtros) {
          if ($request->es_externo == '1') {
            $reglas['nombre_externo'] = 'required|string|max:255';
            $reglas['email_externo'] = 'nullable|email|max:255';
            $reglas['telefono_externo'] = 'nullable|string|max:50';
            $reglas['genero_externo'] = 'required|in:0,1';
            $reglas['pais_id'] = 'required|integer';

            $mensajes['nombre_externo.required'] = 'El nombre completo es obligatorio.';
            $mensajes['email_externo.email'] = 'Por favor, ingresa un correo electrónico válido.';
            $mensajes['genero_externo.required'] = 'El género es obligatorio.';
            $mensajes['pais_id.required'] = 'El país es obligatorio.';
          } else {
            $reglas['persona'] = 'required|integer';
            $mensajes['persona.required'] = 'Debes seleccionar a una persona de la lista.';
          }
        }
      }
    }

    $request->validate($reglas, $mensajes);

    $configuracion = Configuracion::find(1);
    $peticion = new Peticion;

    if ($esPeticionPublica) {
      if ($usuarioAsociado) {
        // Es un usuario registrado que decidió asociar la petición a su cuenta
        $peticion->user_id = $usuarioAsociado->id;
        $peticion->pais_id = $usuarioAsociado->pais_id;
        $emailDestino = $usuarioAsociado->email;
        $nombreDestino = method_exists($usuarioAsociado, 'nombre') ? $usuarioAsociado->nombre(3) : trim($usuarioAsociado->primer_nombre . ' ' . $usuarioAsociado->primer_apellido);
      } else {
        // Es externo (invitado)
        $peticion->nombre_externo = $request->nombre_externo;
        $peticion->email_externo = $request->email_externo;
        $peticion->telefono_externo = $request->telefono_externo;
        $peticion->genero_externo = $request->genero_externo;
        $peticion->pais_id = $request->pais_id;
        $emailDestino = $request->email_externo;
        $nombreDestino = $request->nombre_externo;
      }
    } else {
      // Flujo administrativo
      if (auth()->check()) {
        if ($crearPeticionOtros && $request->es_externo == '1') {
          // Es externo (creado por admin para alguien no registrado)
          $peticion->nombre_externo = $request->nombre_externo;
          $peticion->email_externo = $request->email_externo;
          $peticion->telefono_externo = $request->telefono_externo;
          $peticion->genero_externo = $request->genero_externo;
          $peticion->pais_id = $request->pais_id;
          $emailDestino = $request->email_externo;
          $nombreDestino = $request->nombre_externo;
        } elseif ($crearPeticionOtros && $request->persona) {
          // Es un usuario registrado
          $usuario = User::find($request->persona);
          $peticion->user_id = $usuario->id;
          $peticion->pais_id = $usuario->pais_id;
          $emailDestino = $usuario->email;
          $nombreDestino = $usuario->nombre(3);
        } else {
          // La petición es para el usuario logueado
          $usuario = auth()->user();
          $peticion->user_id = $usuario->id;
          $peticion->pais_id = $usuario->pais_id;
          $emailDestino = $usuario->email;
          $nombreDestino = $usuario->nombre(3);
        }
      }
    }

    $peticion->descripcion = $request->descripción;
    $peticion->tipo_peticion_id = $request->tipo_de_petición;
    $peticion->autor_creacion_id = auth()->check() ? auth()->user()->id : null;
    $peticion->estado = 1; // 1=Pendiente, 3=En proceso, 2=Cerrada
    $peticion->fecha = Carbon::now()->format('Y-m-d');
    $peticion->save();

    // Enviar el correo
    $mensaje = $peticion->tipoPeticion->mensaje_parte_1;
    if ($emailDestino != '' && $mensaje != '') {
      try {
        $jsonVersiculos = $peticion->tipoPeticion->json_versiculos;
        if ($jsonVersiculos != '') {
          $jsonVersiculos = json_decode($jsonVersiculos);
          $cantidadItems = count($jsonVersiculos);
          if ($cantidadItems > 0) {
            $random = rand(1, $cantidadItems);
            $versoSeleccionado = $jsonVersiculos[$random - 1];
            $respuestaText = $versoSeleccionado->cita ?? '';
            $titulo = $versoSeleccionado->titulo ?? '';
            $mensaje .=
              '<I>' . $respuestaText . '</I> <B>(' . $titulo . ', RVR60)</B></p>';
          }
        }

        $mensaje .= $peticion->tipoPeticion->mensaje_parte_2;

        $mailData = new stdClass();
        $mailData->subject = 'Petición';
        $mailData->nombre = $nombreDestino;
        $mailData->mensaje = $mensaje;

        if ($peticion->tipoPeticion->banner_email_url != '') {
          $mailData->banner = $peticion->tipoPeticion->banner_email_url;
        }

        Mail::to($emailDestino)->send(new DefaultMail($mailData));

      } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("Error enviando correo de creacion de peticion ID {$peticion->id}: " . $e->getMessage());
      }
    }

    session()->flash('peticion_reciente_id', $peticion->id);

    return redirect()->route('peticion.exito', [$peticion->id, 'origen' => $esPeticionPublica ? 'publica' : 'interna'])->with('success', "La petición de <b>" . $nombreDestino . "</b> fue creada con éxito.");
  }

  public function eliminaciones(Request $request, $tipo)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $peticiones = [];
    $parametrosBusqueda = json_decode($request->parametrosBusqueda);


    if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_todas') || $rolActivo->hasPermissionTo('peticiones.lista_peticiones_solo_ministerio')) {
      if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_solo_ministerio')) {
        $peticiones = auth()->user()->misPeticiones();
      }

      if ($rolActivo->hasPermissionTo('peticiones.lista_peticiones_todas')) {
        $peticiones = Peticion::leftJoin('users', 'peticiones.user_id', '=', 'users.id')
          ->select('peticiones.*', 'users.foto', 'users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido')
          ->get();
      }
    }

    if ($tipo == 'sin-responder') {
      $peticiones = $peticiones->where('estado', 1);
    } elseif ($tipo == 'finalizadas') {
      $peticiones = $peticiones->where('estado', 2);
    } elseif ($tipo == 'con-seguimiento') {
      $peticiones = $peticiones->where('estado', 3);
    }

    // Filtro por fechas
    $filtroFechaIni = isset($parametrosBusqueda->filtroFechaIni) ? $parametrosBusqueda->filtroFechaIni : Carbon::now()->firstOfYear()->format('Y-m-d');
    $filtroFechaFin = isset($parametrosBusqueda->filtroFechaFin) ? $parametrosBusqueda->filtroFechaFin : Carbon::now()->format('Y-m-d');
    $peticiones = $peticiones->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin]);


    // Filtro por persona
    if ($parametrosBusqueda && isset($parametrosBusqueda->persona_id)) {
      $peticiones = $peticiones->whereIn('user_id', $parametrosBusqueda->persona_id);
    }

    // filtro por tipo peticiones
    if ($parametrosBusqueda && isset($parametrosBusqueda->filtroTipoPeticiones)) {
      $peticiones = $peticiones->whereIn('tipo_peticion_id', $parametrosBusqueda->filtroTipoPeticiones);
    }

    // Busqueda por palabra clave
    if ($parametrosBusqueda &&  isset($parametrosBusqueda->buscar)) {
      $buscar = htmlspecialchars($parametrosBusqueda->buscar);
      $buscar = Helpers::sanearStringConEspacios($buscar);
      $buscar = str_replace(["'"], '', $buscar);
      $buscar_array = explode(' ', $buscar);

      foreach ($buscar_array as $palabra) {
        $peticiones = $peticiones->filter(function ($peticion) use ($palabra) {
          $respuesta  = false !== stristr(Helpers::sanearStringConEspacios($peticion->primer_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->segundo_nombre), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->primer_apellido), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->segundo_apellido), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->identificacion), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->direccion), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->telefono_movil), $palabra) ||
            false !== stristr(Helpers::sanearStringConEspacios($peticion->email), $palabra);

          return $respuesta;
        });
      }
    }

    //Elimino
    $cantidad = $peticiones->count();
    foreach ($peticiones as $peticion) {
      $peticion->seguimientos()->delete();
      $peticion->delete();
    }

    $mensaje = $cantidad > 1
      ? "Las <b>" . $cantidad . "</b> peticiones fueron eliminadas con éxito."
      : "La petición fue eliminada con éxito.";

    return back()->with('success', $mensaje);
  }

  public function eliminacion($id)
  {
    $peticion = Peticion::find($id);
    $peticion->seguimientos()->delete();
    $peticion->delete();

    return back()->with('success', "La petición fue eliminada con éxito.");
  }

  public function generarExcel(Request $request, $tipo)
  {

     $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    // verificar si cumple el permiso
    $rolActivo->verificacionDelPermiso('peticiones.boton_descargar_excel');

    $configuracion = Configuracion::find(1);
    $camposPeticiones = collect(Helpers::camposPeticiones());
    $camposPeticiones =  $camposPeticiones->whereIn('id', $request->informacionCamposPeticiones);
    $parametrosBusqueda = json_decode($request->parametrosBusqueda);

    $arrayCamposInfoPersonal = $request->informacionPersonal ? $request->informacionPersonal : []; //$arrayCamposInfoPersonal
    $arrayPasosCrecimiento = $request->informacionMinisterial ? $request->informacionMinisterial : []; // $arrayPasosCrecimiento
    $arrayDatosCongregacionales = $request->informacionCongregacional ? $request->informacionCongregacional : []; // $arrayDatosCongregacionales
    $arrayCamposExtra = $request->informacionCamposExtras ? $request->informacionCamposExtras : []; // $arrayCamposExtra

    $nombreArchivo = 'informe_peticiones_' . Carbon::now()->format('Y-m-d-H-i-s') . '.xlsx';
    $directorio = 'archivos/peticiones';
    $rutaArchivo = $directorio . '/' . $nombreArchivo;

    Excel::store(
      new PeticionesExport($tipo, $parametrosBusqueda, $camposPeticiones, $arrayCamposInfoPersonal, $arrayPasosCrecimiento, $arrayDatosCongregacionales, $arrayCamposExtra),
      $rutaArchivo
    );

    $downloadUrl = tenant_asset($rutaArchivo);

    return back()->with(
      'success',
      'El informe fue generado con éxito, <a href="' . $downloadUrl . '" class=" link-success fw-bold" download="' . $nombreArchivo . '"> descargalo aquí</a>'
    );
  }
}
