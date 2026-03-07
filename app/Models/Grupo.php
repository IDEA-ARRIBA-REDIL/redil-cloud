<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;


class Grupo extends Model
{
  use HasFactory;
  protected $table = 'grupos';
  protected $guarded = [];

  protected static function booted(): void
  {
    static::created(function (Grupo $grupo) {
      if ($grupo->sede_id) {
        BitacoraSedeGrupo::create([
          'grupo_id' => $grupo->id,
          'sede_id_anterior' => null,
          'sede_id_nuevo' => $grupo->sede_id,
          'autor_id' => auth()->id(),
        ]);
      }

      if ($grupo->tipo_grupo_id) {
        BitacoraTipoGrupo::create([
          'grupo_id' => $grupo->id,
          'tipo_grupo_id_anterior' => null,
          'tipo_grupo_id_nuevo' => $grupo->tipo_grupo_id,
          'autor_id' => auth()->id(),
        ]);
      }
    });

    static::updating(function (Grupo $grupo) {
      if ($grupo->isDirty('sede_id')) {
        BitacoraSedeGrupo::create([
          'grupo_id' => $grupo->id,
          'sede_id_anterior' => $grupo->getOriginal('sede_id'),
          'sede_id_nuevo' => $grupo->sede_id,
          'autor_id' => auth()->id(),
        ]);
      }

      if ($grupo->isDirty('tipo_grupo_id')) {
        BitacoraTipoGrupo::create([
          'grupo_id' => $grupo->id,
          'tipo_grupo_id_anterior' => $grupo->getOriginal('tipo_grupo_id'),
          'tipo_grupo_id_nuevo' => $grupo->tipo_grupo_id,
          'autor_id' => auth()->id(),
        ]);
      }
    });
  }

  public function servidores(): BelongsToMany
  {
    return $this->belongsToMany(User::class, 'servidores_grupo', 'grupo_id', 'user_id')->withTimestamps();
  }

  public function asistentes(): BelongsToMany
  {
    return $this->belongsToMany(User::class, 'integrantes_grupo', 'grupo_id', 'user_id')->withTimestamps();
  }

  public function sede(): BelongsTo
  {
    return $this->belongsTo(Sede::class);
  }

  public function tipoGrupo(): BelongsTo
  {
    return $this->belongsTo(TipoGrupo::class);
  }

  // obtiene los encargados de un grupo
  public function encargados(): BelongsToMany
  {
    return $this->belongsToMany(User::class, 'encargados_grupo', 'grupo_id', 'user_id')
      ->withPivot('id')
      ->withTimestamps();
  }

  //funcion para crear relacion entre reportes de grupo y grupos
  public function reportes(): HasMany
  {
    return $this->hasMany(ReporteGrupo::class);
  }

  public function tipoDeVivienda(): BelongsTo
  {
    return $this->belongsTo(TipoVivienda::class, 'tipo_vivienda_id');
  }

  public function reportesBajaAlta(): HasMany
  {
      return $this->hasMany(ReporteGrupoBajaAlta::class);
  }

   public function informesEvidencias(): HasMany
  {
      return $this->hasMany(InformeEvidenciaGrupo::class);
  }


  public function usuarioCreacion(): BelongsTo
  {
    return $this->belongsTo(User::class, 'usuario_creacion_id');
  }

  public function camposExtras(): BelongsToMany
  {
    return $this->belongsToMany(CampoExtraGrupo::class, 'grupo_opcion_campo_extra', 'grupo_id', 'campo_extra_grupo_id')
      ->withPivot('valor')
      ->withTimestamps();
  }

  // obtiene los grupos excluidos
  public function gruposExcluidos(): BelongsToMany
  {
    return $this->belongsToMany(User::class, 'grupos_excluidos', 'grupo_id', 'user_id')->withTimestamps();
  }

  public function encargadosDirectos()
  {
    $lideres = $this->encargados()
      ->leftJoin('tipo_usuarios', 'users.tipo_usuario_id', '=', 'tipo_usuarios.id')
      ->selectRaw(
        "users.id, CONCAT(users.primer_nombre, ' ',users.primer_apellido) as nombre, users.primer_nombre, users.primer_apellido, users.segundo_nombre, users.segundo_apellido, foto,
        tipo_usuarios.nombre as tipo_usuario, tipo_usuarios.color, tipo_usuarios.icono"
      )
      ->get()
      ->unique('id');

    return $lideres;
  }

  public function gruposMinisterio($tipo = 'objeto', $listaAsistentes = 'sin-eliminados')
  {
    $array_ids_nuevos_grupos = [];
    $array_ids_grupos = [];

    if ($listaAsistentes == 'sin-eliminados') {
      //$array_ids_asistentes = $this->asistentes()->select('id')->lists('id');
      //$grupos_asistentes = Asistente::with("grupos")->whereIn('id', $array_ids_asistentes)->get()->lists("grupos");

      $grupos_asistentes = $this->asistentes()
        ->leftJoin('encargados_grupo', 'users.id', '=', 'encargados_grupo.user_id')
        ->whereNotNull('encargados_grupo.grupo_id')
        ->select('encargados_grupo.grupo_id')
        ->pluck('encargados_grupo.grupo_id')
        ->toArray();
    } elseif ($listaAsistentes == 'solo-eliminados') {
      // $array_ids_asistentes=$this->asistentes()->onlyTrashed()->select('id')->lists('id');
      // $grupos_asistentes=Asistente::onlyTrashed()->with("grupos")->whereIn('id', $array_ids_asistentes)->get()->lists("grupos");
      $grupos_asistentes = $this->asistentes()
        ->onlyTrashed()
        ->leftJoin('encargados_grupo', 'users.id', '=', 'encargados_grupo.user_id')
        ->whereNotNull('encargados_grupo.grupo_id')
        ->select('encargados_grupo.grupo_id')
        ->pluck('encargados_grupo.grupo_id')
        ->toArray();
    } else {
      /*$array_ids_asistentes = $this->asistentes()
        ->withTrashed()
        ->select('id')
        ->lists('id');
      $grupos_asistentes = Asistente::withTrashed()
        ->with('grupos')
        ->whereIn('id', $array_ids_asistentes)
        ->get()
        ->lists('grupos');*/

      $grupos_asistentes = $this->asistentes()
        ->withTrashed()
        ->leftJoin('encargados_grupo', 'users.id', '=', 'encargados_grupo.user_id')
        ->whereNotNull('encargados_grupo.grupo_id')
        ->select('encargados_grupo.grupo_id')
        ->pluck('encargados_grupo.grupo_id')
        ->toArray();
    }



    $grupos_excluidos = $this->encargados()
      ->leftJoin('grupos_excluidos', 'users.id', '=', 'grupos_excluidos.user_id')
      ->whereNotNull('encargados_grupo.grupo_id')
      ->select('grupos_excluidos.grupo_id')
      ->pluck('grupos_excluidos.grupo_id')
      ->toArray();
    /*
    $ids_lideres_grupo = Helper::obtenerArrayIds($this->encargados()->get());
    $grupos_excluidos = GrupoExcluido::whereIn('grupos_excluidos.asistente_id', $ids_lideres_grupo)
      ->get()
      ->lists('grupo_id');*/

    $array_ids_grupos_no_repetidos = [];
    array_push($array_ids_grupos_no_repetidos, $this->id);

    while (count($array_ids_grupos_no_repetidos) > 0) {
      $array_ids_nuevos_grupos = [];

      $array_ids_nuevos_grupos = array_merge($array_ids_nuevos_grupos, $grupos_asistentes);
      $array_ids_nuevos_grupos = array_values(array_unique($array_ids_nuevos_grupos));
      $array_ids_nuevos_grupos = array_diff($array_ids_nuevos_grupos, $grupos_excluidos);

      $array_ids_asistentes = IntegranteGrupo::whereIn('integrantes_grupo.grupo_id', $array_ids_nuevos_grupos)
        ->select('user_id')
        ->pluck('user_id')
        ->toArray();

      if ($listaAsistentes == 'sin-eliminados') {
        /*
        $grupos_asistentes = Asistente::with('grupos')
          ->whereIn('asistentes.id', $array_ids_asistentes)
          ->get()
          ->lists('grupos');*/

        $grupos_asistentes = User::whereIn('users.id', $array_ids_asistentes)
          ->leftJoin('encargados_grupo', 'users.id', '=', 'encargados_grupo.user_id')
          ->whereNotNull('encargados_grupo.grupo_id')
          ->select('encargados_grupo.grupo_id')
          ->pluck('encargados_grupo.grupo_id')
          ->toArray();
      } elseif ($listaAsistentes == 'solo-eliminados') {
        /*$grupos_asistentes = Asistente::onlyTrashed()
          ->with('grupos')
          ->whereIn('asistentes.id', $array_ids_asistentes)
          ->get()
          ->lists('grupos');*/

        $grupos_asistentes = User::onlyTrashed()
          ->whereIn('users.id', $array_ids_asistentes)
          ->leftJoin('encargados_grupo', 'users.id', '=', 'encargados_grupo.user_id')
          ->whereNotNull('encargados_grupo.grupo_id')
          ->select('encargados_grupo.grupo_id')
          ->pluck('encargados_grupo.grupo_id')
          ->toArray();
      } else {
        /*$grupos_asistentes = Asistente::withTrashed()
          ->with('grupos')
          ->whereIn('asistentes.id', $array_ids_asistentes)
          ->get()
          ->lists('grupos');*/

        $grupos_asistentes = User::withTrashed()
          ->whereIn('users.id', $array_ids_asistentes)
          ->leftJoin('encargados_grupo', 'users.id', '=', 'encargados_grupo.user_id')
          ->whereNotNull('encargados_grupo.grupo_id')
          ->select('encargados_grupo.grupo_id')
          ->pluck('encargados_grupo.grupo_id')
          ->toArray();
      }
      $array_ids_grupos_no_repetidos = array_diff($array_ids_nuevos_grupos, $array_ids_grupos);
      $array_ids_grupos_no_repetidos = array_values(array_unique($array_ids_grupos_no_repetidos));
      $array_ids_grupos = array_merge($array_ids_grupos, $array_ids_nuevos_grupos);
    }

    $array_ids_grupos = array_values(array_unique($array_ids_grupos));

    if ($tipo == 'objeto') {
      $grupos_ministerio = Grupo::whereIn('grupos.id', $array_ids_grupos);
    } else {
      $grupos_ministerio = $array_ids_grupos;
    }

    return $grupos_ministerio;
  }


  public function gruposAscendentes($tipo = "objeto")
  {
    $arrayEncargadosIds = [];

    // Obtener los IDs de usuarios excluidos del grupo
    $idsUsuariosExcluidos = $this->gruposExcluidos()->pluck('users.id')->toArray();

    // Obtener todos los encargados del grupo
    $encargados = $this->encargados()->get();
    $arrayEncargadosIds = $encargados->pluck('id')->toArray();

    foreach ($encargados as $encargado) {
      // Obtener los IDs de los líderes del encargado que no están excluidos
      $lideresIds = $encargado->lideres()->whereNotIn('users.id', $idsUsuariosExcluidos)->select('users.id')->pluck('users.id')->toArray();

      // Fusionar los IDs de líderes en el array principal, evitando duplicados
      $arrayEncargadosIds = array_unique(array_merge($arrayEncargadosIds, $lideresIds));
    }

    //return User::whereIn('id',$arrayEncargadosIds)->get();

    $idsGruposAscendentes = IntegranteGrupo::whereIn('integrantes_grupo.user_id', $arrayEncargadosIds)->select('grupo_id')->pluck('grupo_id')->toArray();

   // return IntegranteGrupo::whereIn('integrantes_grupo.user_id', $arrayEncargadosIds)->get();
    if ($tipo == "objeto") {
      return Grupo::whereIn('grupos.id', $idsGruposAscendentes)->orderby('tipo_grupo_id', 'asc')->get();
    }

    return $idsGruposAscendentes;
  }

  public static function gruposNuevos($tipo="objeto")
  {
    $rolActivo= auth()->user()->roles()->wherePivot('activo', true)->first();

    $nuevaFecha = Carbon::now()->subDays(30)->format('Y-m-d');

    if ($rolActivo->hasPermissionTo('grupos.lista_grupos_todos')){
      $grupos = Grupo::where('fecha_apertura', '>', $nuevaFecha )->where('grupos.dado_baja', FALSE);
    }

    if ($rolActivo->hasPermissionTo('grupos.lista_grupos_solo_ministerio')){
      $grupos = auth()->user()->gruposMinisterio()->where('fecha_apertura', '>', $nuevaFecha )->where('grupos.dado_baja', FALSE);
    }

    return $grupos;
  }

  public static function gruposSinLider($tipo="objeto")
  {
      $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

      if ($rolActivo->hasPermissionTo('grupos.lista_grupos_todos') || isset(auth()->user()->iglesiaEncargada()->first()->id)){
          $grupos=Grupo::where('grupos.dado_baja', FALSE);
      }

      if($rolActivo->hasPermissionTo('grupos.lista_grupos_solo_ministerio')){
          $grupos = auth()->user()->gruposMinisterio()->where('grupos.dado_baja', FALSE);
      }

      $grupos= $grupos->leftJoin('encargados_grupo', 'grupos.id', '=', 'encargados_grupo.grupo_id')
              ->select("*", "grupos.id")
              ->where('encargados_grupo.grupo_id', '=', NULL)
              ->where('grupos.dado_baja', FALSE);

      $gruposIds= $grupos->select('grupos.id')
      ->pluck('grupos.id')
      ->toArray();

      $grupos=Grupo::whereIn('grupos.id', $gruposIds);
      return $grupos;

  }

  public function ultimoReporteDelGrupo()
  {
    return $this->reportes()->orderBy('fecha', 'desc')->first();
  }

  public function alDia()
  {
    $ultimoReporte= $this->ultimoReporteDelGrupo();

    $fechaHoy = Carbon::now()->format('Y-m-d');
    $fechaReporte = Carbon::parse($ultimoReporte->fecha)->addDays(8)->format('Y-m-d');

    if($ultimoReporte->finalizado)
    {
      if($fechaReporte>$fechaHoy)
      return true;
      else
      return false;
    }else{
      return false;
    }
  }

  public function asignarSede($user_id="")
  {
      if($user_id==""){
        //La sede con default TRUE es la sede principal
        $sedeDefault = Sede::where('default', TRUE)->first();

          if (auth()->check()) {
            $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();
              if (auth()->user()->sede_id) {
                $this->sede_id = auth()->user()->sede_id;
              }else{
                $sede = Sede::find($rolActivo->lista_asistentes_sede_id);
                $sede
                  ? $this->sede_id = $rolActivo->lista_asistentes_sede_id
                  : $this->sede_id = $sedeDefault->id;
              }
          } else {
            $this->sede_id = $sedeDefault->id;
          }
      }else{
        $user= User::find($user_id);
        $user
          ? $this->sede_id = $user->sede_id
          : '';
      }
      $this->save();

  }

  public function asignarEncargado($userId){
    if(!$this->encargados()->attach($userId))
    {
      $this->asignarSede($userId);
      return "true";
    }
    else{
      return "false";
    }
  }

  public function eliminarEncargado($userId){
    $this->encargados()->detach($userId);
    return "true";
  }

  // Esta función me permite obtener todas las personas que se encuentran vinculadas en su cobertura
  // tiene la opcion de incluir las personas de este grupo o solo la cobertura
  public function ministerioDelGrupo($tipo="objeto", $lista="sin-eliminados", $incluirEsteGrupo = true)
  {
    // Obtenemos los IDs de los grupos del ministerio (subgrupos)
    $arrGruposIds = $this->gruposMinisterio()->pluck('id');

    // Añadimos el ID del grupo actual si se requiere
    if ($incluirEsteGrupo) {
        $arrGruposIds->push($this->id);
    }

    // Construimos la consulta base para los usuarios
    $query = User::query()
    ->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
    ->whereIn('integrantes_grupo.grupo_id', $arrGruposIds)
    ->select('users.*', 'integrantes_grupo.grupo_id as grupo_id');

    // Aplicamos el filtro de eliminados (Soft Deletes)
    match ($lista) {
        'solo-eliminados' => $query->onlyTrashed(),
        'todos' => $query->withTrashed(),
        default => null, // "sin-eliminados" es el comportamiento por defecto de Eloquent
    };

    // Obtenemos la colección de usuarios únicos por su ID
    $discipulos = $query->get()->unique('id');

     // Devolvemos el resultado según el tipo solicitado
     if ($tipo != 'objeto') {
      return $discipulos->pluck('id')->all(); // ->all() lo convierte en un array simple
    }

    return $discipulos;
   /* return $discipulos; // Por defecto, devuelve la colección de objetos User

    if( $tipo == "objeto")
    {
      if($lista=="sin-eliminados"){
        $discipulos = User::leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
        ->whereIn('grupo_id',$arrGruposIds)
        ->select('users.*', 'integrantes_grupo.grupo_id as grupo_id')
        ->get()
        ->unique('id');
      }
      else if($lista=="solo-eliminados"){
        $discipulos = User::onlyTrashed()->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
        ->whereIn('grupo_id',$arrGruposIds)
        ->select('users.*', 'integrantes_grupo.grupo_id as grupo_id')
        ->get()
        ->unique('id');
      }else{
        $discipulos = User::withTrashed()->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
        ->whereIn('grupo_id',$arrGruposIds)
        ->select('users.*', 'integrantes_grupo.grupo_id as grupo_id')
        ->get()
        ->unique('id');
      }
    } else{
      if($lista=="sin-eliminados"){
        $discipulos = User::leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
        ->whereIn('grupo_id',$arrGruposIds)
        ->select('users.*', 'integrantes_grupo.grupo_id as grupo_id')
        ->get()
        ->unique('id')
        ->pluck('id')
        ->array();
      }
      else if($lista=="solo-eliminados"){
        $discipulos = User::onlyTrashed()->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
        ->whereIn('grupo_id',$arrGruposIds)
        ->select('users.*', 'integrantes_grupo.grupo_id as grupo_id')
        ->get()
        ->unique('id')
        ->pluck('id')
        ->array();
      }else{
        $discipulos = User::withTrashed()->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
        ->whereIn('grupo_id',$arrGruposIds)
        ->select('users.*', 'integrantes_grupo.grupo_id as grupo_id')
        ->get()
        ->unique('id')
        ->pluck('id')
        ->array();
      }
    }

    return $discipulos;*/
  }

  // Se usa para el perfil del grupo y obtener por los pasos de crecimiento seleccionados la cantidad de personas segun el estado
  public function dataEstadisticasPorPasoDeCrecimiento()
  {
      // 1. Obtenemos un array con los IDs de todos los usuarios del ministerio.
      //    Usamos la función refactorizada para máxima eficiencia.
      $userIds = $this->ministerioDelGrupo(tipo: 'array', lista: 'sin-eliminados');
      $totalUsuariosMinisterio = count($userIds);
      // Si no hay usuarios, devolvemos una colección vacía para evitar errores.
      if (empty($userIds)) {
          return collect();
      }

      // 2. Obtenemos todos los Pasos de Crecimiento y todos los Estados posibles.
      //    Los traemos de una vez para evitar múltiples consultas a la base de datos (N+1).
      $todosLosPasos = PasoCrecimiento::all();
      $pasoFirst = $todosLosPasos ? $todosLosPasos->first() : null;
      $todosLosEstados = EstadoPasoCrecimientoUsuario::all()->keyBy('id');
      $estadoDefault = $todosLosEstados->where('default')->pluck('id');

      // 3. Hacemos la consulta principal a la tabla pivote.
      //    Aquí contamos cuántos usuarios (de nuestra lista $userIds) hay
      //    por cada combinación de paso y estado.
      $conteoCrudo = DB::table('crecimiento_usuario')
          ->whereIn('user_id', $userIds)
          ->select('paso_crecimiento_id', 'estado_id', DB::raw('COUNT(DISTINCT user_id) as total_personas'))
          ->groupBy('paso_crecimiento_id', 'estado_id')
          ->get()
          ->groupBy('paso_crecimiento_id'); // Agrupamos por paso para fácil acceso

      // 4. Estructuramos el resultado final en el formato que necesitas.
      //    Iteramos sobre cada "Paso" y, para cada uno, construimos su lista de "Estados" con sus conteos.
      return $todosLosPasos->map(function ($paso) use ($conteoCrudo, $todosLosEstados, $totalUsuariosMinisterio, $estadoDefault, $pasoFirst ) {

          // Obtenemos las estadísticas para este paso específico, si existen.
          $statsDelPaso = $conteoCrudo->get($paso->id);

          // Mapeamos todos los estados posibles para asegurar que aparezcan incluso con 0 personas.
          $estadisticasPorEstado = $todosLosEstados->map(function ($estado) use ($conteoCrudo, $statsDelPaso, $totalUsuariosMinisterio, $estadoDefault, $paso) {

              if($estado->default == true)
              {
                $conteo = $totalUsuariosMinisterio - $conteoCrudo->get($paso->id)?->whereNotIn('estado_id', $estadoDefault)->sum('total_personas') ?? 0;
              }else{
                // Buscamos el conteo para este estado. Si no existe, es 0.
                $conteo = $statsDelPaso?->firstWhere('estado_id', $estado->id)?->total_personas ?? 0;
              }
              return [
                  'estado_id' => $estado->id,
                  'estado_nombre' => $estado->nombre,
                  'estado_color' => $estado->color,
                  'total_personas' => $conteo,
              ];
          })->pluck('total_personas');//->values(); // ->values() para resetear las llaves y obtener un array limpio.

          return [
              'id' => "graficaPaso".$paso->id,
              'icono' => "ti ti-versions",
              'tabActiva' => $pasoFirst->id == $paso->id ? true : false,
              'tabId' => "tab-paso-".$paso->id,
              'tabNombre' => $paso->nombre,
              'titulo' => $paso->nombre,
              'descripcion' => '',
              'categorias' => ['No realizado', 'En proceso', 'Finalizados'],
              'datos' => $estadisticasPorEstado
          ];
      });
  }

  public function varificarProcesoReporte()
  {

    $tipoGrupo = $this->tipoGrupo;
    $configuracion = Configuracion::find(1);
    $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

    $puedeReportar = true;
    $botonAccion = 'botonCrearReporte';

    if( $rolActivo->hasPermissionTo('reportes_grupos.privilegio_reportar_grupo_cualquier_fecha') )
    {
      // no se que fecha va a seleccionar, y este privilegio lo deja reportar en cualquier temporada
      $puedeReportar = true;
      $botonAccion = 'botonCrearReporte';

      // OJO: El resto de validaciones se hace en el formulario de nuevo cuando se seleccione la fecha

    }else{

      // Aquí obtengo la fecha del reporte automatica, solo cuando se da esta condicion
      if($tipoGrupo->cantidad_maxima_reportes_semana == 1 && $configuracion->reportar_grupo_cualquier_dia == false)
      {
        // 1: Domingo, ... 7: Sábado
        $diaGrupoUser = $this->dia;
        $diaGrupoCarbon = $diaGrupoUser - 1; // 0=Domingo, 6=Sábado
        $daysToAdd = ($diaGrupoCarbon + 6) % 7;

        $fechaReporte = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays($daysToAdd)->format('Y-m-d');

        // Con este codigo se determina cuantos reportes se ha realizado en la semana y si excede el maximo permitido por tipo de grupo
        $fechaCarbon = Carbon::parse($fechaReporte); // Convertimos la fecha a un objeto Carbon

        $fechaRangoInferior = $fechaCarbon->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $fechaRangoSuperior = $fechaCarbon->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');

        $reporteDeEstaSemana = ReporteGrupo::where('grupo_id', $this->id)
            ->whereDate('fecha', '>=', $fechaRangoInferior)
            ->whereDate('fecha', '<=', $fechaRangoSuperior)
            ->select('id','aprobado','fecha')
            ->first();

        if ($reporteDeEstaSemana)
        {
          if (!$reporteDeEstaSemana->aprobado)
          {
            $puedeReportar = $this->estaDentroDelRango($reporteDeEstaSemana->fecha, $configuracion);
            $botonAccion = $puedeReportar ? 'botonEditarReporte' : 'botonEditarDeshabilitado';
          }else{
            if($configuracion->tiene_sistema_aprobacion_de_reporte)
            {
              $botonAccion = 'botonEditarDeshabilitado';
            }else{
              $puedeReportar = $this->estaDentroDelRango($reporteDeEstaSemana->fecha, $configuracion);
              $botonAccion = $puedeReportar ? 'botonEditarReporte' : 'botonEditarDeshabilitado';
            }

          }
        }else{
          // Valido si esta dentro del rango para reportar
          if( !$rolActivo->hasPermissionTo('reportes_grupos.privilegio_reportar_grupo_cualquier_fecha'))
          {
            $puedeReportar = $this->estaDentroDelRango($fechaReporte, $configuracion);
            $botonAccion = $puedeReportar ? 'botonCrearReporte' : 'botonCrearDeshabilitado';
          }
        }
      }else{
        // Aqui no conozco la fecha que puede seleccionar

        $puedeReportar = true;
        $botonAccion = 'botonCrearReporte';

      }

    }

    return $botonAccion; // botonCrearReporte, botonCrearDeshabilitado, botonEditarReporte, botonDeshabilitado

  }

  // Valida si esta dentro del rango para reportar
  public function estaDentroDelRango(string $fechaAVerificar, $configuracion): bool
  {
      $fechaReporte = Carbon::parse($fechaAVerificar);

      if (isset($configuracion->dias_plazo_reporte_grupo)) {
          $rangoDias = $configuracion->dias_plazo_reporte_grupo;
          $fechaMax = Carbon::now();
          $fechaMin = Carbon::now()->subDays($rangoDias);
          if (Carbon::parse($fechaReporte)->isBefore($fechaMin) || Carbon::parse($fechaReporte)->isAfter($fechaMax)) {
            return false;
          }else {
            return true;
          }
      } elseif (isset($configuracion->dia_corte_reportes_grupos)) {
          $diaCorteUser = $configuracion->dia_corte_reportes_grupos; // 1=Domingo, 7=Sábado
          $diaCorteCarbon = $diaCorteUser - 1;
          $daysToAddCorte = ($diaCorteCarbon + 6) % 7;

          // La ventana es desde el Lunes de esa semana de reporte hasta el día de corte
          $inicioSemanaReporte = $fechaReporte->copy()->startOfWeek(Carbon::MONDAY);
          $fechaCorteSemana = $inicioSemanaReporte->copy()->addDays($daysToAddCorte);

          if (Carbon::now()->format('Y-m-d') > $fechaCorteSemana->format('Y-m-d')) {
            return false;
          }
          return true;
      }

      return true; // Si no hay configuración de plazo o día de corte, se permite reportar
  }

  // Esta funcion nos ayuda a determinar si la fecha del reporte es automatica o no
  public function verificaFechaAutomaticaReporte()
  {
    $tipoGrupo = $this->tipoGrupo;
    $configuracion = Configuracion::first();
    $rolActivo= auth()->user()->roles()->wherePivot('activo', true)->first();

    if( !$rolActivo->hasPermissionTo('reportes_grupos.privilegio_reportar_grupo_cualquier_fecha') )
    {
      if($tipoGrupo->cantidad_maxima_reportes_semana == 1 && $configuracion->reportar_grupo_cualquier_dia == false)
      {
        return true;
      }else{
        return false;
      }

    }else{
      return false;
    }

  }

  public function inicialesNombre()
  {
    $primerLetraNombre = mb_substr($this->nombre, 0, 2);
    return strtoupper($primerLetraNombre);
  }


}
