<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\ClasificacionAsistente;
use App\Models\Configuracion;
use App\Models\FormularioUsuario;
use App\Models\Grupo;
use App\Models\Ingreso;
use App\Models\Moneda;
use App\Models\Ofrenda;
use App\Models\ReporteGrupo;
use App\Models\Sede;
use App\Models\TipoGrupo;
use App\Models\TipoInasistencia;
use App\Models\TipoOfrenda;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use stdClass;

class ReporteGrupoController extends Controller
{
  public function listar(Request $request, $tipo = 'todos')
  {

    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('reportes_grupos.subitem_lista_reportes_grupo');

    $configuracion = Configuracion::find(1);
    $meses = Helpers::meses('largo');
    $textoInformativo = 'Todos';
    $tagsBusqueda = [];
    $bandera = 0;
    $indicadoresGenerales = [];
    $buscar = $request->input('buscar'); // Es mejor usar input() para obtener datos de la request

    $query = ReporteGrupo::query();
    if( $rolActivo->hasPermissionTo('reportes_grupos.lista_reportes_grupo_todos')
      || $rolActivo->hasPermissionTo('reportes_grupos.lista_reportes_grupo_solo_ministerio')
      || $rolActivo->lista_reportes_grupo_sede_id
    ) {

      if ($rolActivo->hasPermissionTo('reportes_grupos.lista_reportes_grupo_solo_ministerio')) {
          //este es para conocer todos los grupos indirectos del usuario logueado
          $gruposIds = auth()->user()->gruposMinisterio('array'); // Asegúrate que este método exista y funcione como esperas
          $query->whereIn('reporte_grupos.grupo_id', $gruposIds);
      } elseif ($rolActivo->lista_reportes_grupo_sede_id) {
          // con este se carga los reportes de los grupos que pertenecen a la sede
          $sede = Sede::find($rolActivo->lista_reportes_grupo_sede_id);
          if ($sede) { // Verificar que la sede exista
              $gruposIds = $sede->grupos()->select('id')->pluck('id')->toArray();
              $query->whereIn('reporte_grupos.grupo_id', $gruposIds);
          }
      }

      // Filtro por fechas
      $filtroFechaIni = $request->filtroFechaIni ? Carbon::parse($request->filtroFechaIni)->format('Y-m-d') : Carbon::now()->firstOfMonth()->format('Y-m-d');
      $filtroFechaFin = $request->filtroFechaFin ? Carbon::parse($request->filtroFechaFin)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
      $query->whereBetween('fecha', [$filtroFechaIni, $filtroFechaFin]);



      // AQUÍ es donde duplicamos la consulta para el conteo ANTES de más filtros
      $queryParaIndicadores = clone $query;
      $queryParaIndicadores = $queryParaIndicadores->select('id','finalizado','aprobado')->get();


      $item = new stdClass();
      $item->nombre = 'Todos';
      $item->url = 'todos';
      $item->cantidad = $queryParaIndicadores->pluck('id')->count();
      $item->color = 'bg-label-success';
      $item->imagen = 'icono_indicador.png';
      $indicadoresGenerales[] = $item;

      if($configuracion->tiene_sistema_aprobacion_de_reporte)
      {
        $item = new stdClass();
        $item->nombre = 'Sin revisar';
        $item->url = 'sin-revisar';
        $item->cantidad = $queryParaIndicadores->whereNull('aprobado')->pluck('id')->count();
        $item->color = 'bg-label-success';
        $item->imagen = 'icono_indicador.png';
        $indicadoresGenerales[] = $item;

        $item = new stdClass();
        $item->nombre = 'Corregidos';
        $item->url = 'corregidos';
        $item->cantidad = $queryParaIndicadores->whereNotNull('aprobado')->where('aprobado', '=', FALSE)->pluck('id')->count();
        $item->color = 'bg-label-success';
        $item->imagen = 'icono_indicador.png';
        $indicadoresGenerales[] = $item;

        $item = new stdClass();
        $item->nombre = 'Aprobados';
        $item->url = 'aprobados';
        $item->cantidad = $queryParaIndicadores->where('aprobado', '=', TRUE)->pluck('id')->count();
        $item->color = 'bg-label-success';
        $item->imagen = 'icono_indicador.png';
        $indicadoresGenerales[] = $item;

      }

      $item = new stdClass();
      $item->nombre = 'Sin finalizar';
      $item->url = 'sin-finalizar';
      $item->cantidad = $queryParaIndicadores->where('finalizado', '=', FALSE)->pluck('id')->count();
      $item->color = 'bg-label-success';
      $item->imagen = 'icono_indicador.png';
      $indicadoresGenerales[] = $item;



      if ($tipo=="aprobados")
      {
        $textoInformativo = 'Aprobados';
				$query->where('aprobado', '=', TRUE);
      } else if ($tipo=="corregidos"){
        $textoInformativo = 'Corregidos';
        $query->where('aprobado', '=', FALSE);
      } elseif ($tipo=="sin-finalizar"){
        $textoInformativo = 'Sin finalizar';
        $query->where('finalizado', '=', FALSE);
      } elseif ($tipo=="sin-revisar") {
        $textoInformativo = 'Sin revisar';
				$query->whereNull('aprobado');
      }

      $textoInformativo.= ", entre ".$filtroFechaIni." y ".$filtroFechaFin;


      if ($buscar) {
        $buscarSaneado = htmlspecialchars($buscar);
        $buscarSaneado = Helpers::sanearStringConEspacios($buscar);
        $buscar = str_replace(["'"], '', $buscar);
        $buscar_array = explode(' ', $buscar);

        $query->leftJoin('grupos', 'reporte_grupos.grupo_id', '=', 'grupos.id')
        ->leftJoin('encargados_grupo', 'grupos.id', '=', 'encargados_grupo.grupo_id')
        ->leftJoin('users AS encargado', 'encargado.id', '=', 'encargados_grupo.user_id');

        $query->where(function ($q) use ($buscarSaneado, $buscar) {
          $q->whereRaw("LOWER( translate( CONCAT_WS(' ', encargado.primer_nombre, encargado.segundo_nombre, encargado.primer_apellido, encargado.segundo_apellido ) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'] )
          ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', encargado.primer_nombre, encargado.primer_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', encargado.primer_nombre, encargado.segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER( translate( CONCAT_WS(' ', encargado.segundo_apellido, encargado.segundo_apellido) ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER( translate( reporte_grupos.tema ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER( translate( grupos.nombre ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER( translate( grupos.codigo ,'áéíóúÁÉÍÓÚäëïöüÄËÏÖÜÑñ','aeiouAEIOUaeiouAEIOUNn')) LIKE LOWER(?)", ['%' . $buscarSaneado . '%'])
          ->orWhereRaw("LOWER(encargado.email) LIKE LOWER(?)", ['%'. $buscar . '%'])
          ->orWhereRaw("LOWER(encargado.identificacion) LIKE LOWER(?)", [ $buscar . '%']);
        });

        // Crear una tag
        $tag = new stdClass();
        $tag->label = $buscar;
        $tag->field = 'buscar';
        $tag->value = $buscar;
        $tag->fieldAux = '';
        $tagsBusqueda[] = $tag;

        $bandera = 1;
      }
    }else {
      $query->whereRaw('1 = 0');
    }

    $indicadoresGenerales = collect($indicadoresGenerales);
		$reportes= $query->orderBy('reporte_grupos.fecha', 'desc')->orderBy('reporte_grupos.id', 'desc')->paginate(9);

    return view('contenido.paginas.reportes-grupo.listar',
      [
        'configuracion' => $configuracion,
        'reportes' => $reportes,
        'buscar' => $buscar,
        'indicadoresGenerales' => $indicadoresGenerales,
        'tipo' => $tipo,
        'meses' => $meses,
        'filtroFechaIni' => $filtroFechaIni,
        'filtroFechaFin' => $filtroFechaFin,
        'tagsBusqueda' => $tagsBusqueda,
        'bandera' => $bandera,
        'textoInformativo' => $textoInformativo,
        'rolActivo' => $rolActivo,
        'configuracion' => $configuracion
      ]
    );

  }

  public function nuevoReporte(): View
  {
    $configuracion = Configuracion::find(1);
    $grupoId = null;
    return view('contenido.paginas.reportes-grupo.nuevo',
      [
        'grupoId' => $grupoId,
        'configuracion' => $configuracion
      ]
    );
  }

  public function nuevo(Grupo $grupo)//: View
  {
    $configuracion = Configuracion::find(1);
    $grupoId = $grupo->id;

    $fechaFin =  Carbon::now()->format('Y-m-d');
    $fechaIni =  Carbon::now()->subMonths(6)->format('Y-m-d');

    $eventos = [];
    $reportes = $grupo->reportes()
    ->whereBetween('fecha', [$fechaIni, $fechaFin])
    ->select()
    ->get();

    foreach($reportes as $reporte)
    {
      $puntos = strlen($reporte->tema)>20 ? '...' : '';
      $eventos[] = [
        'title' => substr($reporte->tema, 0, 20).$puntos ,
        'start' => $reporte->fecha,
        'end' => $reporte->fecha,
        'allDay' =>true,
        'url' => route('dashboard'),
        'classNames' => $reporte->reporte_a_tiempo ? ['fc','fc-event-primary','fs-6','p-1'] : ['fc','fc-event-danger','fs-6','p-1']
      ];
    }

    return view('contenido.paginas.reportes-grupo.nuevo',
      [
        'grupo' => $grupo,
        'grupoId' => $grupoId,
        'configuracion' => $configuracion,
        'eventos' => $eventos
      ]
    );
  }

  public function mensajeExitoso(ReporteGrupo $reporte)
  {
      return view('contenido.paginas.reportes-grupo.reporte-exitoso',
        [
          'reporte' => $reporte
        ]
      );
  }

  public function mensajeReporteFinalizado(ReporteGrupo $reporte)
  {
    $moneda = Moneda::where('default', true)->first();

    $idsEncargados = array_column($reporte->informacion_encargado_grupo, 'id');
    $encargados = User::withTrashed()->whereIn('id', $idsEncargados)->select('id', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido')->get();


    $ofrendasGenericas = $reporte->ofrendas()
    ->whereHas('tipoOfrenda', function ($query) {
        $query->orderBy('generica', 'asc');
    })
    ->get();


    //return $reporte->ofrendas;




    return view('contenido.paginas.reportes-grupo.reporte-finalizado',
      [
        'reporte' => $reporte,
        'encargados' =>  $encargados,
        'moneda' => $moneda,
        'ofrendasGenericas' => $ofrendasGenericas

      ]
    );
  }

  public function resumen(ReporteGrupo $reporte)
  {
   // return  ReporteGrupo::whereJsonContains('ids_grupos_ascendentes', 1)->get();
    $moneda = Moneda::where('default', true)->first();

    $encargados = [];
    if($reporte->informacion_encargado_grupo)
    {

      $idsEncargados = array_column($reporte->informacion_encargado_grupo, 'id');
      $encargados = User::withTrashed()->whereIn('id', $idsEncargados)->select('id', 'primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido')->get();
    }

    // Obtén los integrantes actuales del grupo que no están en la lista de asistentes del reporte
    $personas = $reporte->usuarios()->withTrashed()
    ->select('users.id','foto','email','primer_nombre','segundo_nombre','primer_apellido','segundo_nombre','identificacion','tipo_identificacion_id','tipo_usuario_id')
    ->get();

    // Mapeamos la colección $personas para añadir el nombre del tipo de inasistencia
    $personas = $personas->map(function ($persona) {
      // Verificamos si existe tipo_inasistencia_id en el pivot y no es vacío
      if (!empty($persona->pivot->tipo_inasistencia_id)) {
          // Hacemos la consulta para obtener el TipoInasistencia
          $tipoInasistencia = TipoInasistencia::find($persona->pivot->tipo_inasistencia_id);

          if ($tipoInasistencia) {
              // Si se encuentra, asignamos el nombre al pivot
              $persona->pivot->nombre_tipo_inasistencia = $tipoInasistencia->nombre;
          } else {
              // Si no se encuentra (aunque no debería pasar si el ID es válido), asignamos null
              $persona->pivot->nombre_tipo_inasistencia = null;
          }
      } else {
          // Si no hay tipo_inasistencia_id, asignamos null
          $persona->pivot->nombre_tipo_inasistencia = null;
      }
      return $persona; // map() espera que se retorne el ítem modificado
    });


    $ofrendasGenericas = $reporte->ofrendas()
    ->whereHas('tipoOfrenda', function ($query) {
        $query->where('generica', true);
    })
    ->get();


    $ofrendasNoGenericas = $reporte->ofrendas()
    ->whereHas('tipoOfrenda', function ($query) {
        $query->where('generica', false); // Tipo de ofrenda NO genérica
    })
    ->with('tipoOfrenda') // Carga la relación tipoOfrenda para acceso fácil
    ->get();


    $totalOfrendasNoGenericas = $ofrendasNoGenericas->groupBy(function ($ofrenda) {
        // Agrupamos por el nombre del tipo de ofrenda.
        // Accedemos a la relación 'tipoOfrenda' (que ya cargaste con with())
        // y luego a su propiedad 'nombre'.
        return $ofrenda->tipoOfrenda->nombre;
    })->map(function ($ofrendasAgrupadas, $nombreDelTipo) {
        // Para cada grupo de ofrendas (todas del mismo tipo):
        // $ofrendasAgrupadas es una colección de ofrendas de este tipo.
        // $nombreDelTipo es la clave por la que se agrupó (ej. "Acción de Gracias").

        // Sumamos la propiedad 'valor' de cada ofrenda en este grupo.
        $sumaTotal = $ofrendasAgrupadas->sum('valor');
        $sumaTotalReal = $ofrendasAgrupadas->sum('valor_real');
        return [
            'nombre' => $nombreDelTipo,
            'valor' => $sumaTotal,
            'valor_real' => $sumaTotalReal
            // Puedes añadir más información si la necesitas, por ejemplo, el ID del tipo:
            // 'tipo_ofrenda_id' => $ofrendasAgrupadas->first()->tipoOfrenda->id,
            // 'cantidad_ofrendas' => $ofrendasAgrupadas->count()
        ];
    });


    // return $reporte->informacion_del_grupo;


    $informacionArray = $reporte->informacion_del_grupo;
    $tipoGrupo = null;
    if (isset($informacionArray['tipo_grupo_id'])) {
        $tipoGrupo = TipoGrupo::find($informacionArray['tipo_grupo_id']);
    }

    $diaFormateado = null;
    if (isset($informacionArray['dia'])) {
        $diaFormateado = Helpers::obtenerDiaDeLaSemana($informacionArray['dia']);
    }

    $infoGrupo = [
        'nombre' => $informacionArray['nombre'] ?? null,
        'codigo' => $informacionArray['codigo'] ?? null,
        'dia' => $diaFormateado,
        'tipo_grupo' => $tipoGrupo ? $tipoGrupo->nombre : null, // O el campo que uses para el nombre
        'telefono' => $informacionArray['telefono'] ?? null,
        'direccion' => $informacionArray['direccion'] ?? null,
    ];


    $encargadosAscendentes = null;

    if($reporte->encargados_ascendentes)
    {
      $encargadosAscendentes  = User::whereIn('users.id', $reporte->encargados_ascendentes)
      ->select('users.id','foto','email','primer_nombre','segundo_nombre','primer_apellido','segundo_nombre','identificacion','tipo_identificacion_id','tipo_usuario_id')
      ->get();
    }


    $configuracion = Configuracion::find(1);
    return view('contenido.paginas.reportes-grupo.resumen',
      [
        'reporte' => $reporte,
        'encargados' =>  $encargados,
        'moneda' => $moneda,
        'personas' => $personas,
        'ofrendasGenericas' => $ofrendasGenericas,
        'totalOfrendasNoGenericas' => $totalOfrendasNoGenericas,
        'configuracion' => $configuracion,
        'infoGrupo' => $infoGrupo,
        'encargadosAscendentes' => $encargadosAscendentes
      ]
    );
  }

  public function miAsistencia (ReporteGrupo $reporte)
  {
    $formulario = FormularioUsuario::where('tipo_formulario_id', '=', 3)
    ->select('id', 'nombre', 'label')->first();

    $puedeReportar = $reporte->sePuedeCompartirLinkDeAsistencia();

    return view('contenido.paginas.reportes-grupo.mi-asistencia',
      [
        'reporte' => $reporte,
        'formulario' => $formulario,
        'puedeReportar' => $puedeReportar
      ]
    );
  }

  public function reportarMiAsistancia (ReporteGrupo $reporte, Request $request)
  {
     $request->validate(
      ['buscar' => 'required'],
      ['buscar.required' => 'Por favor, ingresa la información' ]
     );

    $busqueda = $request->input('buscar');
    $buscarSaneadoIdentificacion = str_replace(['.', ' '], '', $busqueda);
    $buscarSaneadoEmail = strtolower($busqueda); // Convertimos la búsqueda a minúsculas para el email


    // return $reporte->grupo->asistentes;
    $asistente = $reporte->grupo->asistentes()
    ->where(function ($query) use ($buscarSaneadoIdentificacion, $buscarSaneadoEmail) {
      $query->whereRaw("REPLACE(REPLACE(identificacion, '.', ''), ' ', '') LIKE ?", ["%$buscarSaneadoIdentificacion%"])
       ->orWhereRaw('LOWER(email) LIKE ?', ["%$buscarSaneadoEmail%"]);
    })
    ->first();


    if($asistente)
    {
      //La persona asiste al grupo
      $registroDeAsistencia = $reporte->usuarios()->where('users.id',$asistente->id)->first();

      if($registroDeAsistencia)
      {
        return redirect()->back()->withErrors(['success' => 'Ya estas registrado.'])->withInput();
      }else{
        $reporte->usuarios()->attach($asistente->id, ['asistio' => 'true']);
        return redirect()->back()->withErrors(['success' => 'Registro exitoso.'])->withInput();
      }

    }else{
      // la persona no pertence al grupo
      $usuario = User::where(function ($query) use ($buscarSaneadoIdentificacion, $buscarSaneadoEmail) {
        $query->whereRaw("REPLACE(REPLACE(identificacion, '.', ''), ' ', '') LIKE ?", ["%$buscarSaneadoIdentificacion%"])
        ->orWhereRaw('LOWER(email) LIKE ?', ["%$buscarSaneadoEmail%"]);
      })
      ->first();

      if($usuario)
      {
        // La persona es un usuario
        return redirect()->back()->withErrors(['error' => 'No te encuentras registrado en este grupo.'])->withInput();
      }else{
        // La persona no existe en la BD
        return redirect()->back()->withErrors(['no_existe' => 'No existe en la BD.'])->withInput();
      }

    }


    return $asistente;
  }

  public function asistencia (ReporteGrupo $reporte)
  {
    $grupo = $reporte->grupo;
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
    $rolActivo->verificacionDelPermiso('reportes_grupos.opcion_actualizar_reporte_grupo');

    $configuracion = Configuracion::find(1);


    if($rolActivo->hasPermissionTo('reportes_grupos.privilegio_reportar_grupo_cualquier_fecha'))
    {
      $mostrarLinkAsistencia =  $reporte->sePuedeCompartirLinkDeAsistencia();
      return view('contenido.paginas.reportes-grupo.asistencias',
          [
            'reporte' => $reporte,
            'mostrarLinkAsistencia' => $mostrarLinkAsistencia
          ]
        );
    }else{
      if($reporte->aprobado !== null && $configuracion->tiene_sistema_aprobacion_de_reporte)
      {
        return redirect()->route('reporteGrupo.resumen', [$reporte]);
      }else{
         $dentroDelRango = $grupo->estaDentroDelRango($reporte->fecha, $configuracion);

         if($dentroDelRango)
         {
          $mostrarLinkAsistencia =  $reporte->sePuedeCompartirLinkDeAsistencia();
          return view('contenido.paginas.reportes-grupo.asistencias',
            [
              'reporte' => $reporte,
              'mostrarLinkAsistencia' => $mostrarLinkAsistencia
            ]
          );
         }
      }
    }

  }

  public function eliminar (ReporteGrupo $reporte)
  {

    // Inicia una transacción de base de datos para asegurar la atomicidad.
    DB::beginTransaction();

    try {

      $grupo= Grupo::find($reporte->grupo_id);
      $fecha_ultimo_reporte = Carbon::parse($grupo->ultimo_reporte_grupo)->format('Y-m-d');

      if($reporte->fecha == $fecha_ultimo_reporte )
      {
        $grupo->ultimo_reporte_grupo=$grupo->ultimo_reporte_grupo_auxiliar;
        $grupo->save();
      }

      $idsOfredas = $reporte->ofrendas()->pluck('ofrendas.id')->toArray();
      // elimino las ofrendas
      $reporte->ofrendas()->detach();
      Ofrenda::whereIn('id', $idsOfredas)->delete();

      // elimino los ingresos
      Ingreso::whereIn('ofrenda_id', $idsOfredas)->delete();

      // elimino las asistencias
      $asistencias = $reporte->usuarios;
      foreach ($asistencias as $asistencia)
      {

        if($asistencia->pivot->asistio == false)
        {
          //Actualizo las fechas de ultimo_reporte_grupo
          if( $reporte->fecha == Carbon::parse($asistencia->ultimo_reporte_grupo)->format('Y-m-d') ){
            $asistencia->ultimo_reporte_grupo=$asistencia->ultimo_reporte_grupo_auxiliar;
          }

        }else{
          if(Carbon::parse($asistencia->ultimo_reporte_grupo)->format('Y-m-d') < $reporte->fecha ){
            $asistencia->ultimo_reporte_grupo_auxiliar = $asistencia->ultimo_reporte_grupo;
            $asistencia->ultimo_reporte_grupo = $reporte->fecha;
          }elseif($reporte->fecha < Carbon::parse($asistencia->ultimo_reporte_grupo)->format('Y-m-d') && $reporte->fecha > Carbon::parse($asistencia->ultimo_reporte_grupo_auxiliar)->format('Y-m-d')){
            $asistencia->ultimo_reporte_grupo_auxiliar=$reporte->fecha;
          }

        }

      }
      $reporte->usuarios()->detach();

      // elimino las clasificaciones
      $reporte->clasificaciones()->detach();

      // elimino el reporte
      $reporte->delete();

      // Si todas las operaciones fueron exitosas, confirma la transacción.
      DB::commit();

      return redirect()->back()->with('success', "El reporte N° <b>".$reporte->id."</b> fue eliminado con éxito.");

    } catch (\Exception $e) {
      // Si ocurre algún error, revierte todas las operaciones de la base de datos.
      DB::rollBack();

      // Registra el error para depuración (revisa el directorio storage/logs).
      Log::error("Error al eliminar ReporteGrupo {$reporte->id}: " . $e->getMessage(), ['exception' => $e]);

      // Redirige con un mensaje de error.
      return redirect()->back()->withErrors(['danger' => "Hubo un error al intentar eliminar el reporte N° ".$reporte->id.". Por favor, inténtalo de nuevo."]);
    }
  }

}
