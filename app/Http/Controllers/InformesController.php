<?php

namespace App\Http\Controllers;

use App\Exports\InformeAsistenciaSemanalGruposExport;
use App\Models\ClasificacionAsistente;
use App\Models\Grupo;
use App\Models\Informe;
use App\Models\ReporteGrupo;
use App\Models\SemanaDeshabilitada;
use App\Models\TipoGrupo;
use App\Models\TipoInforme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InformeDeGruposNoReportadosExport;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class InformesController extends Controller
{


  public function listar(Request $request, $tipoInforme = null)
  {
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('informes.item_informes');

    if ($tipoInforme && !$request->has('tipoInformeId')) {
      $request->merge(['tipoInformeId' => $tipoInforme]);
    }

    $tiposInformes = TipoInforme::get();
    $query = Informe::query();

    if ($request->filled('tipoInformeId')) {
        $query->where('tipo_informe_id', $request->tipoInformeId);
    }

    $informes = $query->orderBy('tipo_informe_id', 'asc')->orderBy('nombre', 'asc')->paginate(20);

    return view('contenido.paginas.informes.listar', [
        'tiposInformes' => $tiposInformes,
        'informes'      => $informes,
        'tipoInforme'   => $request->input('tipoInformeId'),
        'rolActivo' => $rolActivo
    ]);
  }

  public function configuracionSemanas ()
  {
    return view('contenido.paginas.informes.configurar-semanas');
  }

  public function cambiarEstado(Informe $informe)
	{

		if($informe->activo==TRUE)
    {
			$informe->activo=FALSE;
      $msn = "El informe <b>".$informe->nombre."</b> fue desactivado con éxito.";
    }else{
			$informe->activo=TRUE;
      $msn = "El informe <b>".$informe->nombre."</b> fue activado con éxito.";
    }

		$informe->save();
    return redirect()->back()->with('success', $msn);

	}

  public function informeDeGruposNoReportados(Informe $informe, Request $request)
  {
    $grupos = null;
    $grupoSeleccionado = null;
    $resumenReporte = null;

    $filtroTipoGrupos = [];
    if (!empty($request->query())) {
      $validatedData = $request->validate([
          'grupo' => ['required', 'integer', 'exists:grupos,id'],
          'semana' => ['required'],
          'filtroPorTipoDeGrupo' => ['required'],
      ]);
    }

    $tiposDeGrupo = TipoGrupo::orderBy('orden', 'asc')->get();

    if (!empty($validatedData))
    {
      $filtroTipoGrupos = $request->filtroPorTipoDeGrupo ?? [];

      $semanaString = $request->semana;    // Obtenemos el string de la semana, ej: "2025-W26"
      sscanf($semanaString, "%d-W%d", $year, $week);   // Usamos sscanf para extraer el año y el número de la semana de forma segura
      $fecha = Carbon::now()->setISODate($year, $week);
      $inicioDeSemana = $fecha->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
      $finDeSemana = $fecha->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

      $grupoId= (int) $request->grupo;
      $grupoSeleccionado = Grupo::find($grupoId);

      // PASO 1: Construir la consulta INTERNA que selecciona los grupos y calcula el estado.
      // Nota que esta consulta NO tiene `having` ni `paginate` todavía.
      $subconsulta = $grupoSeleccionado->gruposMinisterio()
      ->whereIn('tipo_grupo_id', $filtroTipoGrupos)
      ->selectRaw("
          grupos.*,
          CASE
              WHEN reportes.id IS NULL THEN 'No reportado'
              WHEN reportes.no_reporte = TRUE THEN 'No realizado'
              WHEN reportes.finalizado = FALSE THEN 'No reportado'
              ELSE 'Correcto'
          END as estado_reporte,
          motivos.nombre as nombre_motivo,
          reportes.descripcion_adicional_motivo
      ")
      ->leftJoin('reporte_grupos as reportes', function ($join) use ($inicioDeSemana, $finDeSemana) {
          $join->on('grupos.id', '=', 'reportes.grupo_id')
              ->whereBetween('reportes.fecha', [$inicioDeSemana, $finDeSemana]);
      })
      ->leftJoin('motivos_no_reporte_grupo as motivos', 'reportes.motivo_no_reporte_grupo_id', '=', 'motivos.id');


      // PASO 2: Construir la consulta EXTERNA usando la interna como una tabla virtual.
      // Ahora podemos usar un simple `WHERE` sobre la columna 'estado_reporte'.
      $grupos = DB::query()
      ->fromSub($subconsulta, 'sub') // Trata la consulta anterior como una tabla llamada 'sub'
      ->where('estado_reporte', '!=', 'Correcto')
      ->join('tipo_grupos', 'sub.tipo_grupo_id', '=', 'tipo_grupos.id')
      ->select('sub.id','sub.nombre','estado_reporte', 'tipo_grupos.nombre as nombreTipo', 'sub.nombre_motivo', 'sub.descripcion_adicional_motivo' )
      ->orderBy('nombre', 'asc')
      ->paginate(20);


      // --- Inicio del código para la tabla resumen ---

      // Consulta para obtener el resumen de estados de reporte para el grupo seleccionado
      $resumenReporte = DB::query()
          ->fromSub($subconsulta, 'resumen_sub') // Usamos la misma subconsulta base
          ->selectRaw("
              estado_reporte,
              COUNT(*) as cantidad
          ")
          ->whereIn('estado_reporte', ['No reportado', 'No realizado']) // Solo los estados que nos interesan para el resumen
          ->groupBy('estado_reporte')
          ->pluck('cantidad', 'estado_reporte') // Convierte el resultado a un array asociativo [estado => cantidad]
          ->toArray();

      // Aseguramos que las claves 'No reportado' y 'No realizado' siempre existan, incluso si la cantidad es 0
      $resumenReporte['No reportado'] = $resumenReporte['No reportado'] ?? 0;
      $resumenReporte['No realizado'] = $resumenReporte['No realizado'] ?? 0;

      // --- Fin del código para la tabla resumen ---

      // Obtener encargados de los grupos resultantes
      $grupoIds = $grupos->pluck('id');
      $datosEncargados = DB::table('encargados_grupo')
          ->join('users', 'encargados_grupo.user_id', '=', 'users.id')
          ->whereIn('encargados_grupo.grupo_id', $grupoIds)
          ->select('encargados_grupo.grupo_id', DB::raw("CONCAT(users.primer_nombre, ' ', users.primer_apellido) as nombre_completo"))
          ->get()
          ->groupBy('grupo_id');


    }

    return view('contenido.paginas.informes.informe-grupos-no-reportados', [
      'informe' => $informe,
      'grupoSeleccionado' => $grupoSeleccionado,
      'resumenReporte' => $resumenReporte,
      'request' => $request,
      'tiposDeGrupo' => $tiposDeGrupo,
      'filtroTipoGrupos' => $filtroTipoGrupos,
      'grupos' => $grupos,
      'datosEncargados' => $datosEncargados ?? collect([])
    ]);
  }

  public function exportarInformeDeGruposNoReportados(Informe $informe, Request $request)
  {
      // Reutilizamos la misma validación para asegurar que los filtros son correctos
      $validatedData = $request->validate([
          'grupo' => ['required', 'integer', 'exists:grupos,id'],
          'semana' => ['required'],
          'filtroPorTipoDeGrupo' => ['required', 'array'],
      ]);

      // Generamos un nombre de archivo dinámico
      $nombreGrupo = Grupo::find($validatedData['grupo'])->nombre;
      $nombreArchivo = 'grupos-no-reportados-' . $nombreGrupo . '-' . $validatedData['semana'] . '.xlsx';

      // El código no llegará aquí mientras dd() esté activo
      return Excel::download(
          new InformeDeGruposNoReportadosExport( // o GruposNoReportadosExport
              $validatedData['grupo'],
              $validatedData['semana'],
              $validatedData['filtroPorTipoDeGrupo']
          ),
          $nombreArchivo
      );
  }

  public function informeAsistenciaSemanalGrupos(Informe $informe, Request $request)
  {
    $semanasHabilitadas = null;
    $dataPivoteada = null;
    $encabezados = null;
    $encabezadosAgrupados = null;
    $resumenTotal = null;

    $periodos = [
      [
          'label' => 'Meses',
          'options' => ['1m' => 'Enero', '2m' => 'Febrero', '3m' => 'Marzo','4m' => 'Abril', '5m' => 'Mayo', '6m' => 'Junio','7m' => 'Julio', '8m' => 'Agosto', '9m' => 'Septiembre','10m' => 'Octubre', '11m' => 'Noviembre', '12m' => 'Diciembre']
      ],
      [
          'label' => 'Trimestres',
          'options' => ['1t' => '1er trimestre', '2t' => '2do trimestre','3t' => '3er trimestre', '4t' => '4to trimestre']
      ],
      [
          'label' => 'Semestres',
          'options' => ['1s' => '1er semestre', '2s' => '2do semestre']
      ],
      [
          'label' => 'Otros',
          'options' => ['anio' => 'Todo el año','semana' => 'Por semana']
      ]
    ];

    $tiposDeGrupo= TipoGrupo::select('id','nombre')->orderBy('orden','asc')->get();
		$clasificacionAsistentes= ClasificacionAsistente::orderBy('id','asc')->get();

    $filtroTipoGrupos = $request->filtroPorTipoDeGrupo ?? [];
    $filtroClasificacionAsistentes = $request->filtroPorClasificacionAsistentes ?? [];

    $clasificacionesSeleccionadas = ClasificacionAsistente::whereIn('id', $filtroClasificacionAsistentes)->get();

    if (!empty($request->query())) {

      $validatedData = $request->validate([
          'grupo' => ['required', 'integer', 'exists:grupos,id'],
          'periodo' => ['required'],
          'año' => $request->periodo != 'semana' ? ['required'] : [],
          'semana' => $request->periodo == 'semana' ? ['required'] : [],
          'filtroPorTipoDeGrupo' => ['required'],
      ]);
    }

    if (!empty($validatedData))
    {

      $fecha_inicio = null;
      $fecha_fin = null;
      $año = $request->año ?? date('Y');
      $periodo = $request->periodo ?? '1m';
      $semana = $request->semana;
      $grupoId= (int) $request->grupo;


      switch (true) {
          // CASO 1: Meses (ej: "1m", "2m", ..., "12m")
          case preg_match('/^(\d+)m$/', $periodo, $matches) === 1:
              $month = $matches[1]; // El número capturado está en $matches[1]
              $fecha_inicio = Carbon::create($año, $month, 1)->startOfMonth();
              $fecha_fin = Carbon::create($año, $month, 1)->endOfMonth();
              $entre = "si, en mes"; // Mensaje de depuración más claro
              break;

          // CASO 2: Trimestres (ej: "1t", "2t", "3t", "4t")
          case preg_match('/^(\d+)t$/', $periodo, $matches) === 1:
            $quarter = (int)$matches[1]; // Convertimos el número a entero
            $startMonth = ($quarter - 1) * 3 + 1;
            $fecha_inicio = Carbon::create($año, $startMonth, 1)->startOfMonth();
            $fecha_fin = Carbon::create($año, $startMonth, 1)->addMonths(2)->endOfMonth();
            $entre = "si, en trimestre";
            break;

          // CASO 3: Semestres (ej: "1s", "2s")
          case preg_match('/^(\d+)s$/', $periodo, $matches) === 1:
              $semester = $matches[1]; // El número capturado está en $matches[1]
              if ($semester == 1) {
                  $fecha_inicio = Carbon::create($año, 1, 1)->startOfYear();
                  $fecha_fin = Carbon::create($año, 6, 1)->endOfMonth();
              } else {
                  $fecha_inicio = Carbon::create($año, 7, 1)->startOfMonth();
                  $fecha_fin = Carbon::create($año, 12, 1)->endOfYear();
              }
              $entre = "si, en semestre";
              break;

          // CASO 4: Año completo
          case $periodo === 'anio':
              $fecha_inicio = Carbon::create($año)->startOfYear();
              $fecha_fin = Carbon::create($año)->endOfYear();
              break;

          // CASO 5: Por semana (usando el input 'week')
          case $periodo === 'semana' && !empty($semana):
              $fecha_inicio = Carbon::parse($semana)->startOfWeek();
              $fecha_fin = Carbon::parse($semana)->endOfWeek();
              break;
      }

      // --- Calculamos el número de semanas USANDO CARBON ---
      $semana_ini = $fecha_inicio ? $fecha_inicio->isoWeek : null;
      $semana_fin = $fecha_fin ? $fecha_fin->isoWeek : null;

      // --- AJUSTE PARA EL INICIO DE AÑO (Semana de Inicio) ---
      if ($fecha_inicio && $fecha_inicio->month == 1 && $semana_ini > 50) {
          $semana_ini = 1;
      }

      // --- AJUSTE PARA EL FIN DE AÑO (Semana de Fin) ---
      if ($fecha_fin && $fecha_fin->month == 12 && $semana_fin < 5) {
          $semana_fin = $fecha_fin->weeksInYear;
      }

      // Obtenemos la fecha y semana actual con Carbon
      $hoy = Carbon::now();

      // Si el año del periodo seleccionado es el año actual, ajustamos la semana final
      if ($año == $hoy->year) {
          $semana_fin = min($semana_fin, $hoy->isoWeek);
      }

      // Obtenemos el día de corte desde el request.
      $diaDeCorte = (int) $request->día_de_corte;

      // Verificamos si el día de corte es válido antes de continuar.
      if (isset($request->día_de_corte) && $diaDeCorte >= 1 && $diaDeCorte <= 7)
      {
          // El día de corte sirve para determinar si incluye la primera semana o la ultima del periodo seleccioando.

          // --- CONVERTIMOS TU ID DE DÍA DE CORTE AL ESTÁNDAR ISO-8601 (Lunes=1, Domingo=7) ---
          $diaDeCorteISO = match ($diaDeCorte) {
              1 => 7, // Tu Domingo (1) es el 7 para Carbon ISO.
              2 => 1, // Tu Lunes (2) es el 1 para Carbon ISO.
              3 => 2, // Tu Martes (3) es el 2...
              4 => 3,
              5 => 4,
              6 => 5,
              7 => 6, // Tu Sábado (7) es el 6 para Carbon ISO.
          };

          // --- AJUSTE PARA LA SEMANA DE INICIO ---
          $fechaIni = Carbon::now()->setISODate($año, $semana_ini);
          $primerDiaIni = $fechaIni->copy()->startOfWeek();
          $ultimoDiaIni = $fechaIni->copy()->endOfWeek();

          if ($primerDiaIni->month !== $ultimoDiaIni->month) {
              // Construimos la fecha de corte EXACTA usando el estándar ISO
              $fechaDeCorteIni = Carbon::now()->setISODate($año, $semana_ini, $diaDeCorteISO);

              if ($fechaDeCorteIni->month === $primerDiaIni->month) {
                  $semana_ini++;
              }
          }

          // --- AJUSTE PARA LA SEMANA DE FIN ---
          $fechaFin = Carbon::now()->setISODate($año, $semana_fin);
          $primerDiaFin = $fechaFin->copy()->startOfWeek();
          $ultimoDiaFin = $fechaFin->copy()->endOfWeek();

          if ($primerDiaFin->month !== $ultimoDiaFin->month) {
              // Construimos la fecha de corte EXACTA usando el estándar ISO
              $fechaDeCorteFin = Carbon::now()->setISODate($año, $semana_fin, $diaDeCorteISO);

              if ($fechaDeCorteFin->month === $primerDiaFin->month) {
                  $semana_fin--;
              }
          }
      }


      // Recalculo la fecha inicio del todo el periodo
      $fecha_inicio = Carbon::now()->setISODate($año, $semana_ini)->startOfWeek()->toDateString();

      // Recalculo la fecha fin del todo el periodo
      $fecha_fin = Carbon::now()->setISODate($año, $semana_fin)->endOfWeek()->toDateString();

      // Consulta que trae las semanas deshabilitadas del año
      $arraySemanasDeshabilitadas = SemanaDeshabilitada::where('anio', '=', $año)
      ->pluck('numero_semana')
      ->toArray();

      // Creas el rango completo de semanas para tu periodo
      $semanasDelPeriodo = range($semana_ini, $semana_fin);

      // Obtienes solo las semanas habilitadas restando las deshabilitadas
      $semanasHabilitadas = array_diff($semanasDelPeriodo, $arraySemanasDeshabilitadas);

      $encabezados = [];
      foreach ($semanasHabilitadas as $semana) {
          // Obtenemos el Lunes y Domingo de la semana correspondiente
          $fechaDeLaSemana = Carbon::now()->setISODate($año, $semana);
          $primerDia = $fechaDeLaSemana->copy()->startOfWeek();
          $ultimoDia = $fechaDeLaSemana->copy()->endOfWeek();

          $mesParaMostrar = '';

          // Verificamos si la semana abarca dos meses diferentes
          if ($primerDia->month !== $ultimoDia->month) {
              // Formateamos los nombres de los meses en mayúsculas (ej: DIC-ENE)
              // Nota: translatedFormat necesita el paquete de idioma "es" en Carbon
              $nombreMes1 =  str_replace(".", "",mb_strtoupper($primerDia->translatedFormat('M')));
              $nombreMes2 =  str_replace(".", "",mb_strtoupper($ultimoDia->translatedFormat('M')));
              $mesParaMostrar = $nombreMes1 . '-' . $nombreMes2;
          } else {
              // Si es el mismo mes, solo mostramos uno
              $mesParaMostrar = str_replace(".", "", mb_strtoupper($primerDia->translatedFormat('M')));
          }

          // Guardamos los datos que necesita la vista
          $encabezados[] = [
              'mes' => $mesParaMostrar,
              'dias' => $primerDia->format('d') . '-' . $ultimoDia->format('d'),
              'semana' => $semana // Guardamos la semana para usarla en el cuerpo de la tabla
          ];
      }

      $encabezadosAgrupados = [];
      foreach ($encabezados as $encabezado) {
          // Busca el último grupo en nuestro nuevo arreglo
          $ultimoGrupo = end($encabezadosAgrupados);

          // Si el mes es el mismo que el del último grupo, lo agrupamos
          if ($ultimoGrupo && $ultimoGrupo['mes'] === $encabezado['mes']) {
              // Obtenemos la llave del último elemento para poder modificarlo
              $llaveUltimoGrupo = key($encabezadosAgrupados);
              // Aumentamos su colspan
              $encabezadosAgrupados[$llaveUltimoGrupo]['colspan']++;
              // Añadimos la semana a ese grupo
              $encabezadosAgrupados[$llaveUltimoGrupo]['semanas'][] = [
                  'dias' => $encabezado['dias'],
                  'semana' => $encabezado['semana'],
              ];
          } else {
              // Si es un mes nuevo, creamos un nuevo grupo
              $encabezadosAgrupados[] = [
                  'mes' => $encabezado['mes'],
                  'colspan' => 1,
                  'semanas' => [
                      [
                          'dias' => $encabezado['dias'],
                          'semana' => $encabezado['semana'],
                      ]
                  ],
              ];
          }
      }


      $datosPrincipales = DB::table('reporte_grupos')
      ->join('grupos', 'reporte_grupos.grupo_id', '=', 'grupos.id')
      ->join('tipo_grupos', 'grupos.tipo_grupo_id', '=', 'tipo_grupos.id')
      ->select(
          'grupos.id as grupo_id',
          'grupos.nombre as grupo_nombre',
          'tipo_grupos.nombre as tipo_grupo_nombre',
          'grupos.fecha_apertura',
          DB::raw('EXTRACT(WEEK FROM reporte_grupos.fecha) as semana'),
          DB::raw('SUM(reporte_grupos.cantidad_asistencias) as total_asistencias'),
          DB::raw('SUM(reporte_grupos.cantidad_inasistencias) as total_inasistencias'),
          DB::raw('COUNT(reporte_grupos.id) as cantidad_reportes')
      )
      ->whereBetween('reporte_grupos.fecha', [$fecha_inicio, $fecha_fin])
      ->whereJsonContains('ids_grupos_ascendentes', $grupoId)
      ->whereIn('grupos.tipo_grupo_id', $filtroTipoGrupos)
      // Usamos el método when() para aplicar el filtro condicionalmente
      ->when(!$request->boolean('incluirGruposDadosDeBaja'), function ($query) {
        // añadiendo la condición para excluir los grupos dados de baja.
        $query->where('grupos.dado_baja', false);
      })
      ->when(!$request->boolean('incluirGruposNuevos'), function ($query) use ($fecha_fin) {
        // Añade la condición para incluir solo grupos cuya fecha de apertura sea menor o igual a la fecha final del periodo.
        $query->where('grupos.fecha_apertura', '<=', $fecha_fin);
      })
      ->groupBy('grupos.id', 'grupos.nombre', 'grupos.fecha_apertura', 'tipo_grupo_nombre' , 'semana')
      ->get();


      $selectsDinamicos = [
        'grupos.id as grupo_id',
        'grupos.nombre as grupo_nombre',
        DB::raw('EXTRACT(WEEK FROM reporte_grupos.fecha) as semana')
      ];

      $bindings = [];
      foreach ($filtroClasificacionAsistentes as $clasificacionId) {
          $alias = "total_clasificacion_" . (int)$clasificacionId;
          $selectsDinamicos[] = DB::raw("SUM(CASE WHEN pivot_clasificaciones.clasificacion_asistente_id = ? THEN pivot_clasificaciones.cantidad ELSE 0 END) as $alias");
          $bindings[] = (int)$clasificacionId;
      }

      // Consulta 2: Obtiene SOLO los totales de clasificaciones
      $datosClasificaciones = DB::table('reporte_grupos')
          ->join('grupos', 'reporte_grupos.grupo_id', '=', 'grupos.id')
          ->leftJoin('clasificacion_asistente_reporte_grupo as pivot_clasificaciones', 'reporte_grupos.id', '=', 'pivot_clasificaciones.reporte_grupo_id')
          ->select($selectsDinamicos)
          ->addBinding($bindings, 'select')
          ->whereBetween('reporte_grupos.fecha', [$fecha_inicio, $fecha_fin])
          ->whereJsonContains('ids_grupos_ascendentes', $grupoId)
          ->whereIn('grupos.tipo_grupo_id', $filtroTipoGrupos)
          ->groupBy('grupos.id', 'grupos.nombre', 'semana')
          ->get();


      // Consulta 3: Obtener los encaragdos de los grupos
      $grupoIds = $datosPrincipales->pluck('grupo_id')->unique();
      $datosEncargados = DB::table('encargados_grupo')
      ->join('users', 'encargados_grupo.user_id', '=', 'users.id')
      ->join('tipo_usuarios', 'users.tipo_usuario_id', '=', 'tipo_usuarios.id')
      ->whereIn('encargados_grupo.grupo_id', $grupoIds)
      ->select('encargados_grupo.grupo_id', 'users.name as encargado_nombre')
      ->select(
        'encargados_grupo.grupo_id',
        DB::raw("CONCAT_WS(' ', users.primer_nombre, users.segundo_nombre, users.primer_apellido) as encargado_nombre"),
        'tipo_usuarios.nombre as tipo_usuario_nombre',
        'tipo_usuarios.icono as tipo_usuario_icono'
      )
      ->get()
      ->groupBy('grupo_id');


      // Primero, creamos la estructura base con los datos principales
      $dataPivoteada = [];
      foreach ($datosPrincipales as $dato) {
        // Si es la primera vez que vemos este grupo, creamos su estructura base
        if (!isset($dataPivoteada[$dato->grupo_nombre])) {
            $dataPivoteada[$dato->grupo_nombre] = [
              'grupo_nombre'      => $dato->grupo_nombre,
              'tipo_grupo'        => $dato->tipo_grupo_nombre,
              'fecha_apertura'    => $dato->fecha_apertura,
              'encargados'        => $datosEncargados->get($dato->grupo_id)?->all() ?? [],
              'datos_semanales'   => []
            ];
        }

        // Añadimos los datos de la semana
        $dataPivoteada[$dato->grupo_nombre]['datos_semanales'][$dato->semana] = [
            'asistencias'   => $dato->total_asistencias,
            'inasistencias' => $dato->total_inasistencias,
            'reportes'      => $dato->cantidad_reportes,
            'clasificaciones' => [],
        ];
      }

      // Luego, inyectamos los datos de las clasificaciones en la estructura ya creada
      foreach ($datosClasificaciones as $datoClasificacion) {
          $clasificacionesDinamicas = [];
          foreach ($filtroClasificacionAsistentes as $clasificacionId) {
              $alias = "total_clasificacion_" . (int)$clasificacionId;
              $clasificacionesDinamicas[$clasificacionId] = $datoClasificacion->$alias;
          }

          // Verificamos si existe la celda antes de añadirle los datos
          if (isset($dataPivoteada[$datoClasificacion->grupo_nombre]['datos_semanales'][$datoClasificacion->semana])) {
            $dataPivoteada[$datoClasificacion->grupo_nombre]['datos_semanales'][$datoClasificacion->semana]['clasificaciones'] = $clasificacionesDinamicas;
          }
      }

      // Obtenemos el número de semanas que se están mostrando en el reporte
      // Es crucial para calcular el promedio correctamente.
      $numeroDeSemanas = count($semanasHabilitadas);
      $totalSemanasActivasGlobal = 0;


      // Nos aseguramos de no dividir por cero si no hay semanas
      if ($numeroDeSemanas > 0) {
        // Iteramos sobre el reporte pivoteado POR REFERENCIA (&) para poder modificarlo
        foreach ($dataPivoteada as $grupoId => &$datosGrupo) {


          $fechaApertura = Carbon::parse($datosGrupo['fecha_apertura']);
          $semanaApertura = 1; // Por defecto, asumimos que está activo desde la semana 1

          if ($fechaApertura->year > (int)$año) {
              // Si el grupo abre en un año futuro, ninguna semana es válida.
              // Usamos un número alto para deshabilitar todas las semanas en la vista.
              $semanaApertura = 99;
          } elseif ($fechaApertura->year === (int)$año) {
              // Si es el mismo año, usamos la semana de apertura real.
              $semanaApertura = $fechaApertura->isoWeek;
          }

          $datosGrupo['semana_apertura'] = $semanaApertura;

          $semanasActivas = array_filter(
              $semanasHabilitadas,
              fn($semana) => $semana >= $semanaApertura
          );
          $numeroDeSemanasActivas = count($semanasActivas);

          if ($numeroDeSemanasActivas > 0) {

            $totalSemanasActivasGlobal += $numeroDeSemanasActivas;

            // Usamos la potencia de las Colecciones de Laravel para sumar fácilmente
            $coleccionSemanas = collect($datosGrupo['datos_semanales']);

            // Calculamos los promedios
            $promedioAsistencias = $coleccionSemanas->sum('asistencias') / $numeroDeSemanasActivas;
            $promedioInasistencias = $coleccionSemanas->sum('inasistencias') / $numeroDeSemanasActivas;


              // Obtenemos el número real de reportes sumándolos
              $reportesReales = $coleccionSemanas->sum('reportes');

              // La cantidad esperada es el número de semanas activas para este grupo
              $reportesEsperados = $numeroDeSemanasActivas;

            $promediosClasificaciones = [];
            foreach ($filtroClasificacionAsistentes as $clasificacionId) {
                // Sumamos los valores de una clasificación específica a través de la colección
                $sumaClasificacion = $coleccionSemanas->sum(function ($semana) use ($clasificacionId) {
                    return $semana['clasificaciones'][$clasificacionId] ?? 0;
                });
                $promediosClasificaciones[$clasificacionId] = $sumaClasificacion / $numeroDeSemanasActivas;
            }

            // Añadimos un nuevo arreglo 'promedios' al grupo actual
            $datosGrupo['promedios'] = [
                'asistencias'   => $promedioAsistencias,
                'inasistencias' => $promedioInasistencias,
                'reportes'      => [
                  'real' => $reportesReales,
                  'esperado' => $reportesEsperados,
                ],
                'clasificaciones' => $promediosClasificaciones,
            ];
          }
        }
      }

      unset($datosGrupo); // con esto rompemos la referencia &$datosGrupo


      // Inicializamos la estructura para guardar los totales
      $resumenTotal = [
        'asistencias' => [],
        'inasistencias' => [],
        'reportes' => [],
      // 'clasificaciones' => [],
      ];

      // Llenamos la estructura sumando los datos de cada grupo
      foreach ($dataPivoteada as $datosGrupo) {
        // Obtenemos la semana de apertura que ya habíamos calculado para este grupo
        $semanaDeApertura = $datosGrupo['semana_apertura'];

        foreach ($datosGrupo['datos_semanales'] as $semana => $datosSemana) {

            // 👇 ¡AQUÍ ESTÁ LA LÓGICA CLAVE!
            // Solo procesamos la suma si la semana del reporte es válida para el grupo
            if ($semana >= $semanaDeApertura) {

                // Inicializamos los contadores para la semana si no existen
                $resumenTotal['asistencias'][$semana]   ??= 0;
                $resumenTotal['inasistencias'][$semana] ??= 0;
                $resumenTotal['reportes'][$semana]      ??= 0;

                // Sumamos los valores
                $resumenTotal['asistencias'][$semana]   += $datosSemana['asistencias'] ?? 0;
                $resumenTotal['inasistencias'][$semana] += $datosSemana['inasistencias'] ?? 0;
                $resumenTotal['reportes'][$semana]      += $datosSemana['reportes'] ?? 0;

                // Sumamos las clasificaciones dinámicas
                if (!empty($datosSemana['clasificaciones'])) {
                    foreach ($filtroClasificacionAsistentes as $clasificacionId) {
                        $resumenTotal['clasificaciones'][$clasificacionId][$semana] ??= 0;
                        $resumenTotal['clasificaciones'][$clasificacionId][$semana] += $datosSemana['clasificaciones'][$clasificacionId] ?? 0;
                    }
                }
            }
        }
      }

      // Calculamos los promedios para el resumen
      $resumenTotal['promedios'] = [];
      if ($numeroDeSemanas > 0) {
        $resumenTotal['promedios']['asistencias'] = array_sum($resumenTotal['asistencias']) / $numeroDeSemanas;
        $resumenTotal['promedios']['inasistencias'] = array_sum($resumenTotal['inasistencias']) / $numeroDeSemanas;
        $totalReportesReales = array_sum($resumenTotal['reportes']);
          $resumenTotal['promedios']['reportes'] = [
              'real' => $totalReportesReales,
              'esperado' => $totalSemanasActivasGlobal,
          ];

        $promediosClasifResumen = [];
        foreach ($filtroClasificacionAsistentes as $clasificacionId) {
            $sumaTotalClasif = array_sum($resumenTotal['clasificaciones'][$clasificacionId] ?? []);
            $promediosClasifResumen[$clasificacionId] = $sumaTotalClasif / $numeroDeSemanas;
        }
        $resumenTotal['promedios']['clasificaciones'] = $promediosClasifResumen;
      }

    }


    return view('contenido.paginas.informes.informe-asistencia-semanal-grupos', [
      'informe' => $informe,
      'request' => $request,
      'periodos' => $periodos,
      'tiposDeGrupo' => $tiposDeGrupo,
      'filtroTipoGrupos' => $filtroTipoGrupos,
      'clasificacionAsistentes' => $clasificacionAsistentes,
      'filtroClasificacionAsistentes' => $filtroClasificacionAsistentes,
      'semenasHabilitadas' => $semanasHabilitadas,
      'dataPivoteada' => $dataPivoteada,
      'encabezadosAgrupados' => $encabezadosAgrupados,
      'encabezados' => $encabezados,
      'clasificacionesSeleccionadas' =>  $clasificacionesSeleccionadas,
      'resumen' => $resumenTotal
    ]);
  }

  public function exportarInformeAsistenciaSemanalGrupos(Informe $informe, Request $request)
  {
    $semanasHabilitadas = null;
    $dataPivoteada = null;
    $encabezados = null;
    $encabezadosAgrupados = null;
    $resumenTotal = null;

    $periodos = [
      [
          'label' => 'Meses',
          'options' => ['1m' => 'Enero', '2m' => 'Febrero', '3m' => 'Marzo','4m' => 'Abril', '5m' => 'Mayo', '6m' => 'Junio','7m' => 'Julio', '8m' => 'Agosto', '9m' => 'Septiembre','10m' => 'Octubre', '11m' => 'Noviembre', '12m' => 'Diciembre']
      ],
      [
          'label' => 'Trimestres',
          'options' => ['1t' => '1er trimestre', '2t' => '2do trimestre','3t' => '3er trimestre', '4t' => '4to trimestre']
      ],
      [
          'label' => 'Semestres',
          'options' => ['1s' => '1er semestre', '2s' => '2do semestre']
      ],
      [
          'label' => 'Otros',
          'options' => ['anio' => 'Todo el año','semana' => 'Por semana']
      ]
    ];

    $tiposDeGrupo= TipoGrupo::select('id','nombre')->orderBy('orden','asc')->get();
    $clasificacionAsistentes= ClasificacionAsistente::orderBy('id','asc')->get();

    $filtroTipoGrupos = $request->filtroPorTipoDeGrupo ?? [];
    $filtroClasificacionAsistentes = $request->filtroPorClasificacionAsistentes ?? [];

    $clasificacionesSeleccionadas = ClasificacionAsistente::whereIn('id', $filtroClasificacionAsistentes)->get();

    if (!empty($request->query())) {

      $validatedData = $request->validate([
          'grupo' => ['required', 'integer', 'exists:grupos,id'],
          'periodo' => ['required'],
          'año' => $request->periodo != 'semana' ? ['required'] : [],
          'semana' => $request->periodo == 'semana' ? ['required'] : [],
          'filtroPorTipoDeGrupo' => ['required'],
      ]);
    }

    if (!empty($validatedData))
    {

      $fecha_inicio = null;
      $fecha_fin = null;
      $año = $request->año ?? date('Y');
      $periodo = $request->periodo ?? '1m';
      $semana = $request->semana;
      $grupoId= (int) $request->grupo;


      switch (true) {
          // CASO 1: Meses (ej: "1m", "2m", ..., "12m")
          case preg_match('/^(\d+)m$/', $periodo, $matches) === 1:
              $month = $matches[1]; // El número capturado está en $matches[1]
              $fecha_inicio = Carbon::create($año, $month, 1)->startOfMonth();
              $fecha_fin = Carbon::create($año, $month, 1)->endOfMonth();
              $entre = "si, en mes"; // Mensaje de depuración más claro
              break;

          // CASO 2: Trimestres (ej: "1t", "2t", "3t", "4t")
          case preg_match('/^(\d+)t$/', $periodo, $matches) === 1:
            $quarter = (int)$matches[1]; // Convertimos el número a entero
            $startMonth = ($quarter - 1) * 3 + 1;
            $fecha_inicio = Carbon::create($año, $startMonth, 1)->startOfMonth();
            $fecha_fin = Carbon::create($año, $startMonth, 1)->addMonths(2)->endOfMonth();
            $entre = "si, en trimestre";
            break;

          // CASO 3: Semestres (ej: "1s", "2s")
          case preg_match('/^(\d+)s$/', $periodo, $matches) === 1:
              $semester = $matches[1]; // El número capturado está en $matches[1]
              if ($semester == 1) {
                  $fecha_inicio = Carbon::create($año, 1, 1)->startOfYear();
                  $fecha_fin = Carbon::create($año, 6, 1)->endOfMonth();
              } else {
                  $fecha_inicio = Carbon::create($año, 7, 1)->startOfMonth();
                  $fecha_fin = Carbon::create($año, 12, 1)->endOfYear();
              }
              $entre = "si, en semestre";
              break;

          // CASO 4: Año completo
          case $periodo === 'anio':
              $fecha_inicio = Carbon::create($año)->startOfYear();
              $fecha_fin = Carbon::create($año)->endOfYear();
              break;

          // CASO 5: Por semana (usando el input 'week')
          case $periodo === 'semana' && !empty($semana):
              $fecha_inicio = Carbon::parse($semana)->startOfWeek();
              $fecha_fin = Carbon::parse($semana)->endOfWeek();
              break;
      }

      // --- Calculamos el número de semanas USANDO CARBON ---
      $semana_ini = $fecha_inicio ? $fecha_inicio->isoWeek : null;
      $semana_fin = $fecha_fin ? $fecha_fin->isoWeek : null;

      // --- AJUSTE PARA EL INICIO DE AÑO (Semana de Inicio) ---
      if ($fecha_inicio && $fecha_inicio->month == 1 && $semana_ini > 50) {
          $semana_ini = 1;
      }

      // --- AJUSTE PARA EL FIN DE AÑO (Semana de Fin) ---
      if ($fecha_fin && $fecha_fin->month == 12 && $semana_fin < 5) {
          $semana_fin = $fecha_fin->weeksInYear;
      }

      // Obtenemos la fecha y semana actual con Carbon
      $hoy = Carbon::now();

      // Si el año del periodo seleccionado es el año actual, ajustamos la semana final
      if ($año == $hoy->year) {
          $semana_fin = min($semana_fin, $hoy->isoWeek);
      }

      // Obtenemos el día de corte desde el request.
      $diaDeCorte = (int) $request->día_de_corte;

      // Verificamos si el día de corte es válido antes de continuar.
      if (isset($request->día_de_corte) && $diaDeCorte >= 1 && $diaDeCorte <= 7)
      {
          // El día de corte sirve para determinar si incluye la primera semana o la ultima del periodo seleccioando.

          // --- CONVERTIMOS TU ID DE DÍA DE CORTE AL ESTÁNDAR ISO-8601 (Lunes=1, Domingo=7) ---
          $diaDeCorteISO = match ($diaDeCorte) {
              1 => 7, // Tu Domingo (1) es el 7 para Carbon ISO.
              2 => 1, // Tu Lunes (2) es el 1 para Carbon ISO.
              3 => 2, // Tu Martes (3) es el 2...
              4 => 3,
              5 => 4,
              6 => 5,
              7 => 6, // Tu Sábado (7) es el 6 para Carbon ISO.
          };

          // --- AJUSTE PARA LA SEMANA DE INICIO ---
          $fechaIni = Carbon::now()->setISODate($año, $semana_ini);
          $primerDiaIni = $fechaIni->copy()->startOfWeek();
          $ultimoDiaIni = $fechaIni->copy()->endOfWeek();

          if ($primerDiaIni->month !== $ultimoDiaIni->month) {
              // Construimos la fecha de corte EXACTA usando el estándar ISO
              $fechaDeCorteIni = Carbon::now()->setISODate($año, $semana_ini, $diaDeCorteISO);

              if ($fechaDeCorteIni->month === $primerDiaIni->month) {
                  $semana_ini++;
              }
          }

          // --- AJUSTE PARA LA SEMANA DE FIN ---
          $fechaFin = Carbon::now()->setISODate($año, $semana_fin);
          $primerDiaFin = $fechaFin->copy()->startOfWeek();
          $ultimoDiaFin = $fechaFin->copy()->endOfWeek();

          if ($primerDiaFin->month !== $ultimoDiaFin->month) {
              // Construimos la fecha de corte EXACTA usando el estándar ISO
              $fechaDeCorteFin = Carbon::now()->setISODate($año, $semana_fin, $diaDeCorteISO);

              if ($fechaDeCorteFin->month === $primerDiaFin->month) {
                  $semana_fin--;
              }
          }
      }


      // Recalculo la fecha inicio del todo el periodo
      $fecha_inicio = Carbon::now()->setISODate($año, $semana_ini)->startOfWeek()->toDateString();

      // Recalculo la fecha fin del todo el periodo
      $fecha_fin = Carbon::now()->setISODate($año, $semana_fin)->endOfWeek()->toDateString();

      // Consulta que trae las semanas deshabilitadas del año
      $arraySemanasDeshabilitadas = SemanaDeshabilitada::where('anio', '=', $año)
      ->pluck('numero_semana')
      ->toArray();

      // Creas el rango completo de semanas para tu periodo
      $semanasDelPeriodo = range($semana_ini, $semana_fin);

      // Obtienes solo las semanas habilitadas restando las deshabilitadas
      $semanasHabilitadas = array_diff($semanasDelPeriodo, $arraySemanasDeshabilitadas);

      $encabezados = [];
      foreach ($semanasHabilitadas as $semana) {
          // Obtenemos el Lunes y Domingo de la semana correspondiente
          $fechaDeLaSemana = Carbon::now()->setISODate($año, $semana);
          $primerDia = $fechaDeLaSemana->copy()->startOfWeek();
          $ultimoDia = $fechaDeLaSemana->copy()->endOfWeek();

          $mesParaMostrar = '';

          // Verificamos si la semana abarca dos meses diferentes
          if ($primerDia->month !== $ultimoDia->month) {
              // Formateamos los nombres de los meses en mayúsculas (ej: DIC-ENE)
              // Nota: translatedFormat necesita el paquete de idioma "es" en Carbon
              $nombreMes1 =  str_replace(".", "",mb_strtoupper($primerDia->translatedFormat('M')));
              $nombreMes2 =  str_replace(".", "",mb_strtoupper($ultimoDia->translatedFormat('M')));
              $mesParaMostrar = $nombreMes1 . '-' . $nombreMes2;
          } else {
              // Si es el mismo mes, solo mostramos uno
              $mesParaMostrar = str_replace(".", "", mb_strtoupper($primerDia->translatedFormat('M')));
          }

          // Guardamos los datos que necesita la vista
          $encabezados[] = [
              'mes' => $mesParaMostrar,
              'dias' => $primerDia->format('d') . '-' . $ultimoDia->format('d'),
              'semana' => $semana // Guardamos la semana para usarla en el cuerpo de la tabla
          ];
      }

      $encabezadosAgrupados = [];
      foreach ($encabezados as $encabezado) {
          // Busca el último grupo en nuestro nuevo arreglo
          $ultimoGrupo = end($encabezadosAgrupados);

          // Si el mes es el mismo que el del último grupo, lo agrupamos
          if ($ultimoGrupo && $ultimoGrupo['mes'] === $encabezado['mes']) {
              // Obtenemos la llave del último elemento para poder modificarlo
              $llaveUltimoGrupo = key($encabezadosAgrupados);
              // Aumentamos su colspan
              $encabezadosAgrupados[$llaveUltimoGrupo]['colspan']++;
              // Añadimos la semana a ese grupo
              $encabezadosAgrupados[$llaveUltimoGrupo]['semanas'][] = [
                  'dias' => $encabezado['dias'],
                  'semana' => $encabezado['semana'],
              ];
          } else {
              // Si es un mes nuevo, creamos un nuevo grupo
              $encabezadosAgrupados[] = [
                  'mes' => $encabezado['mes'],
                  'colspan' => 1,
                  'semanas' => [
                      [
                          'dias' => $encabezado['dias'],
                          'semana' => $encabezado['semana'],
                      ]
                  ],
              ];
          }
      }


      $datosPrincipales = DB::table('reporte_grupos')
      ->join('grupos', 'reporte_grupos.grupo_id', '=', 'grupos.id')
      ->join('tipo_grupos', 'grupos.tipo_grupo_id', '=', 'tipo_grupos.id')
      ->select(
          'grupos.id as grupo_id',
          'grupos.nombre as grupo_nombre',
          'tipo_grupos.nombre as tipo_grupo_nombre',
          'grupos.fecha_apertura',
          DB::raw('EXTRACT(WEEK FROM reporte_grupos.fecha) as semana'),
          DB::raw('SUM(reporte_grupos.cantidad_asistencias) as total_asistencias'),
          DB::raw('SUM(reporte_grupos.cantidad_inasistencias) as total_inasistencias'),
          DB::raw('COUNT(reporte_grupos.id) as cantidad_reportes')
      )
      ->whereBetween('reporte_grupos.fecha', [$fecha_inicio, $fecha_fin])
      ->whereJsonContains('ids_grupos_ascendentes', $grupoId)
      ->whereIn('grupos.tipo_grupo_id', $filtroTipoGrupos)
      // Usamos el método when() para aplicar el filtro condicionalmente
      ->when(!$request->boolean('incluirGruposDadosDeBaja'), function ($query) {
        // añadiendo la condición para excluir los grupos dados de baja.
        $query->where('grupos.dado_baja', false);
      })
      ->when(!$request->boolean('incluirGruposNuevos'), function ($query) use ($fecha_fin) {
        // Añade la condición para incluir solo grupos cuya fecha de apertura sea menor o igual a la fecha final del periodo.
        $query->where('grupos.fecha_apertura', '<=', $fecha_fin);
      })
      ->groupBy('grupos.id', 'grupos.nombre', 'grupos.fecha_apertura', 'tipo_grupo_nombre' , 'semana')
      ->get();


      $selectsDinamicos = [
        'grupos.id as grupo_id',
        'grupos.nombre as grupo_nombre',
        DB::raw('EXTRACT(WEEK FROM reporte_grupos.fecha) as semana')
      ];

      $bindings = [];
      foreach ($filtroClasificacionAsistentes as $clasificacionId) {
          $alias = "total_clasificacion_" . (int)$clasificacionId;
          $selectsDinamicos[] = DB::raw("SUM(CASE WHEN pivot_clasificaciones.clasificacion_asistente_id = ? THEN pivot_clasificaciones.cantidad ELSE 0 END) as $alias");
          $bindings[] = (int)$clasificacionId;
      }

      // Consulta 2: Obtiene SOLO los totales de clasificaciones
      $datosClasificaciones = DB::table('reporte_grupos')
          ->join('grupos', 'reporte_grupos.grupo_id', '=', 'grupos.id')
          ->leftJoin('clasificacion_asistente_reporte_grupo as pivot_clasificaciones', 'reporte_grupos.id', '=', 'pivot_clasificaciones.reporte_grupo_id')
          ->select($selectsDinamicos)
          ->addBinding($bindings, 'select')
          ->whereBetween('reporte_grupos.fecha', [$fecha_inicio, $fecha_fin])
          ->whereJsonContains('ids_grupos_ascendentes', $grupoId)
          ->whereIn('grupos.tipo_grupo_id', $filtroTipoGrupos)
          ->groupBy('grupos.id', 'grupos.nombre', 'semana')
          ->get();


      // Consulta 3: Obtener los encaragdos de los grupos
      $grupoIds = $datosPrincipales->pluck('grupo_id')->unique();
      $datosEncargados = DB::table('encargados_grupo')
      ->join('users', 'encargados_grupo.user_id', '=', 'users.id')
      ->join('tipo_usuarios', 'users.tipo_usuario_id', '=', 'tipo_usuarios.id')
      ->whereIn('encargados_grupo.grupo_id', $grupoIds)
      ->select('encargados_grupo.grupo_id', 'users.name as encargado_nombre')
      ->select(
        'encargados_grupo.grupo_id',
        DB::raw("CONCAT_WS(' ', users.primer_nombre, users.segundo_nombre, users.primer_apellido) as encargado_nombre"),
        'tipo_usuarios.nombre as tipo_usuario_nombre',
        'tipo_usuarios.icono as tipo_usuario_icono'
      )
      ->get()
      ->groupBy('grupo_id');


      // Primero, creamos la estructura base con los datos principales
      $dataPivoteada = [];
      foreach ($datosPrincipales as $dato) {
        // Si es la primera vez que vemos este grupo, creamos su estructura base
        if (!isset($dataPivoteada[$dato->grupo_nombre])) {
            $dataPivoteada[$dato->grupo_nombre] = [
              'grupo_nombre'      => $dato->grupo_nombre,
              'tipo_grupo'        => $dato->tipo_grupo_nombre,
              'fecha_apertura'    => $dato->fecha_apertura,
              'encargados'        => $datosEncargados->get($dato->grupo_id)?->all() ?? [],
              'datos_semanales'   => []
            ];
        }

        // Añadimos los datos de la semana
        $dataPivoteada[$dato->grupo_nombre]['datos_semanales'][$dato->semana] = [
            'asistencias'   => $dato->total_asistencias,
            'inasistencias' => $dato->total_inasistencias,
            'reportes'      => $dato->cantidad_reportes,
            'clasificaciones' => [],
        ];
      }

      // Luego, inyectamos los datos de las clasificaciones en la estructura ya creada
      foreach ($datosClasificaciones as $datoClasificacion) {
          $clasificacionesDinamicas = [];
          foreach ($filtroClasificacionAsistentes as $clasificacionId) {
              $alias = "total_clasificacion_" . (int)$clasificacionId;
              $clasificacionesDinamicas[$clasificacionId] = $datoClasificacion->$alias;
          }

          // Verificamos si existe la celda antes de añadirle los datos
          if (isset($dataPivoteada[$datoClasificacion->grupo_nombre]['datos_semanales'][$datoClasificacion->semana])) {
            $dataPivoteada[$datoClasificacion->grupo_nombre]['datos_semanales'][$datoClasificacion->semana]['clasificaciones'] = $clasificacionesDinamicas;
          }
      }

      // Obtenemos el número de semanas que se están mostrando en el reporte
      // Es crucial para calcular el promedio correctamente.
      $numeroDeSemanas = count($semanasHabilitadas);
      $totalSemanasActivasGlobal = 0;


      // Nos aseguramos de no dividir por cero si no hay semanas
      if ($numeroDeSemanas > 0) {
        // Iteramos sobre el reporte pivoteado POR REFERENCIA (&) para poder modificarlo
        foreach ($dataPivoteada as $grupoId => &$datosGrupo) {


          $fechaApertura = Carbon::parse($datosGrupo['fecha_apertura']);
          $semanaApertura = 1; // Por defecto, asumimos que está activo desde la semana 1

          if ($fechaApertura->year > (int)$año) {
              // Si el grupo abre en un año futuro, ninguna semana es válida.
              // Usamos un número alto para deshabilitar todas las semanas en la vista.
              $semanaApertura = 99;
          } elseif ($fechaApertura->year === (int)$año) {
              // Si es el mismo año, usamos la semana de apertura real.
              $semanaApertura = $fechaApertura->isoWeek;
          }

          $datosGrupo['semana_apertura'] = $semanaApertura;

          $semanasActivas = array_filter(
              $semanasHabilitadas,
              fn($semana) => $semana >= $semanaApertura
          );
          $numeroDeSemanasActivas = count($semanasActivas);

          if ($numeroDeSemanasActivas > 0) {

            $totalSemanasActivasGlobal += $numeroDeSemanasActivas;

            // Usamos la potencia de las Colecciones de Laravel para sumar fácilmente
            $coleccionSemanas = collect($datosGrupo['datos_semanales']);

            // Calculamos los promedios
            $promedioAsistencias = $coleccionSemanas->sum('asistencias') / $numeroDeSemanasActivas;
            $promedioInasistencias = $coleccionSemanas->sum('inasistencias') / $numeroDeSemanasActivas;


              // Obtenemos el número real de reportes sumándolos
              $reportesReales = $coleccionSemanas->sum('reportes');

              // La cantidad esperada es el número de semanas activas para este grupo
              $reportesEsperados = $numeroDeSemanasActivas;

            $promediosClasificaciones = [];
            foreach ($filtroClasificacionAsistentes as $clasificacionId) {
                // Sumamos los valores de una clasificación específica a través de la colección
                $sumaClasificacion = $coleccionSemanas->sum(function ($semana) use ($clasificacionId) {
                    return $semana['clasificaciones'][$clasificacionId] ?? 0;
                });
                $promediosClasificaciones[$clasificacionId] = $sumaClasificacion / $numeroDeSemanasActivas;
            }

            // Añadimos un nuevo arreglo 'promedios' al grupo actual
            $datosGrupo['promedios'] = [
                'asistencias'   => $promedioAsistencias,
                'inasistencias' => $promedioInasistencias,
                'reportes'      => [
                  'real' => $reportesReales,
                  'esperado' => $reportesEsperados,
                ],
                'clasificaciones' => $promediosClasificaciones,
            ];
          }
        }
      }

      unset($datosGrupo); // con esto rompemos la referencia &$datosGrupo


      // Inicializamos la estructura para guardar los totales
      $resumenTotal = [
        'asistencias' => [],
        'inasistencias' => [],
        'reportes' => [],
      // 'clasificaciones' => [],
      ];

      // Llenamos la estructura sumando los datos de cada grupo
      foreach ($dataPivoteada as $datosGrupo) {
        // Obtenemos la semana de apertura que ya habíamos calculado para este grupo
        $semanaDeApertura = $datosGrupo['semana_apertura'];

        foreach ($datosGrupo['datos_semanales'] as $semana => $datosSemana) {

            // 👇 ¡AQUÍ ESTÁ LA LÓGICA CLAVE!
            // Solo procesamos la suma si la semana del reporte es válida para el grupo
            if ($semana >= $semanaDeApertura) {

                // Inicializamos los contadores para la semana si no existen
                $resumenTotal['asistencias'][$semana]   ??= 0;
                $resumenTotal['inasistencias'][$semana] ??= 0;
                $resumenTotal['reportes'][$semana]      ??= 0;

                // Sumamos los valores
                $resumenTotal['asistencias'][$semana]   += $datosSemana['asistencias'] ?? 0;
                $resumenTotal['inasistencias'][$semana] += $datosSemana['inasistencias'] ?? 0;
                $resumenTotal['reportes'][$semana]      += $datosSemana['reportes'] ?? 0;

                // Sumamos las clasificaciones dinámicas
                if (!empty($datosSemana['clasificaciones'])) {
                    foreach ($filtroClasificacionAsistentes as $clasificacionId) {
                        $resumenTotal['clasificaciones'][$clasificacionId][$semana] ??= 0;
                        $resumenTotal['clasificaciones'][$clasificacionId][$semana] += $datosSemana['clasificaciones'][$clasificacionId] ?? 0;
                    }
                }
            }
        }
      }

      // Calculamos los promedios para el resumen
      $resumenTotal['promedios'] = [];
      if ($numeroDeSemanas > 0) {
        $resumenTotal['promedios']['asistencias'] = array_sum($resumenTotal['asistencias']) / $numeroDeSemanas;
        $resumenTotal['promedios']['inasistencias'] = array_sum($resumenTotal['inasistencias']) / $numeroDeSemanas;
        $totalReportesReales = array_sum($resumenTotal['reportes']);
          $resumenTotal['promedios']['reportes'] = [
              'real' => $totalReportesReales,
              'esperado' => $totalSemanasActivasGlobal,
          ];

        $promediosClasifResumen = [];
        foreach ($filtroClasificacionAsistentes as $clasificacionId) {
            $sumaTotalClasif = array_sum($resumenTotal['clasificaciones'][$clasificacionId] ?? []);
            $promediosClasifResumen[$clasificacionId] = $sumaTotalClasif / $numeroDeSemanas;
        }
        $resumenTotal['promedios']['clasificaciones'] = $promediosClasifResumen;
      }

    }
      $nombreArchivo = 'informe-asistencia-semanal-' . date('Y-m-d') . '.xlsx';

      return Excel::download(new InformeAsistenciaSemanalGruposExport(
          $dataPivoteada,
          $resumenTotal,
          $encabezadosAgrupados,
          $encabezados,
          $clasificacionesSeleccionadas
      ), $nombreArchivo);
  }

    public function informeCompras($informe = null)
    {
        return view('contenido.paginas.informes.informe-compras', ['informe' => $informe]);
    }

    public function informePagos($informe = null)
    {
        return view('contenido.paginas.informes.informe-pagos', ['informe' => $informe]);
    }
}
