<?php

namespace App\Models;

use App\Notifications\MiVerificacionDeCorreo;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;



class User extends Authenticatable implements MustVerifyEmail
{
  use HasApiTokens, HasFactory, Notifiable;
  use HasRoles;
  use SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = ['name', 'email', 'password', 'mostrar_modal_agregar_hijos'];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = ['password', 'remember_token'];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
    'fecha_nacimiento' => 'datetime',
  ];

  protected static function booted()
  {
    static::created(function ($user) {
      if ($user->tipo_usuario_id) {
        \App\Models\BitacoraTipoUsuario::create([
          'user_id' => $user->id,
          'tipo_usuario_id_anterior' => null,
          'tipo_usuario_id_nuevo' => $user->tipo_usuario_id,
          'autor_id' => auth()->id() ?? $user->usuario_creacion_id,
        ]);
      }
      if ($user->estado_civil_id) {
        \App\Models\BitacoraEstadoCivil::create([
          'user_id' => $user->id,
          'estado_civil_id_anterior' => null,
          'estado_civil_id_nuevo' => $user->estado_civil_id,
          'autor_id' => auth()->id() ?? $user->usuario_creacion_id,
        ]);
      }
      if ($user->sede_id) {
        \App\Models\BitacoraSede::create([
          'user_id' => $user->id,
          'sede_id_anterior' => null,
          'sede_id_nuevo' => $user->sede_id,
          'autor_id' => auth()->id() ?? $user->usuario_creacion_id,
        ]);
      }
    });

    static::updating(function ($user) {
      if ($user->isDirty('tipo_usuario_id')) {
        \App\Models\BitacoraTipoUsuario::create([
          'user_id' => $user->id,
          'tipo_usuario_id_anterior' => $user->getOriginal('tipo_usuario_id'),
          'tipo_usuario_id_nuevo' => $user->tipo_usuario_id,
          'autor_id' => auth()->id(),
        ]);
      }
      if ($user->isDirty('estado_civil_id')) {
        \App\Models\BitacoraEstadoCivil::create([
          'user_id' => $user->id,
          'estado_civil_id_anterior' => $user->getOriginal('estado_civil_id'),
          'estado_civil_id_nuevo' => $user->estado_civil_id,
          'autor_id' => auth()->id(),
        ]);
      }
      if ($user->isDirty('sede_id')) {
        \App\Models\BitacoraSede::create([
          'user_id' => $user->id,
          'sede_id_anterior' => $user->getOriginal('sede_id'),
          'sede_id_nuevo' => $user->sede_id,
          'autor_id' => auth()->id(),
        ]);
      }
    });
  }

  // Array para evitar duplicados en la misma petición
  protected static $emailsVerificacionEnviados = [];

  public function sendEmailVerificationNotification()
  {
    // Si ya enviamos correo a este usuario en esta petición, paramos.
    if (in_array($this->id, self::$emailsVerificacionEnviados)) {
      \Log::info('sendEmailVerificationNotification BLOCKED for user ' . $this->id . ' (Duplicate detected)');
      return;
    }

    // Marcar como enviado
    self::$emailsVerificacionEnviados[] = $this->id;

    $this->notify(new MiVerificacionDeCorreo);
  }

  public function tipoUsuario(): BelongsTo
  {
    return $this->belongsTo(TipoUsuario::class);
  }

  public function consejero()
  {
    return $this->hasOne(Consejero::class);
  }

  public function switchActiveRole(Role $newActiveRole): void
  {
    // 1. Validar que el usuario realmente tenga el rol que intenta activar.
    if (!$this->hasRole($newActiveRole)) {
      // Si no lo tiene, detenemos la ejecución para evitar errores.
      // En un caso real, podrías lanzar una excepción.
      abort(403, 'No tienes permiso para activar este rol.');
    }

    // 2. Usamos una transacción para asegurar la integridad de los datos.
    // Si algo falla, se revierte todo.
    DB::transaction(function () use ($newActiveRole) {
      // LÍNEA CORREGIDA:
      // Apuntamos directamente a la tabla pivote para desactivar todos los roles
      // asociados a este usuario (model_id).
      DB::table('model_has_roles')
        ->where('model_id', $this->id)
        ->update(['activo' => false]);

      // Esta línea ya era correcta y activa el nuevo rol en la tabla pivote.
      $this->roles()->updateExistingPivot($newActiveRole->id, [
        'activo' => true,
      ]);
    });

    // 3. Limpiamos la caché de permisos de Spatie.
    // ¡Este paso es crucial! Sin él, los permisos no se actualizarán de inmediato.
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
  }

  /*
  public function cargoActividad(): BelongsToMany
  {
    return $this->belongsToMany(ActividadEncargado::class, 'actividad_encargados_cargo', 'user.id', 'user_id')->withPivot(
      'descripcion',
      'tipo_cargo_id',
      'actividad_id'
    );
  }*/


  // En el modelo User
  public function tiposCargo(): HasManyThrough
  {
    return $this->hasManyThrough(
      TipoCargoActividad::class,
      ActividadEncargado::class,
      'user_id', // Llave foránea en actividad_encargados_cargo
      'id', // Llave primaria en tipo_cargo_actividad
      'id', // Llave primaria en users
      'tipo_cargo_id' // Llave foránea en actividad_encargados_cargo que apunta a tipo_cargo_actividad
    );
  }

  public function actividadCargos(): HasMany
  {
    return $this->hasMany(ActividadEncargado::class);
  }

  public function nivelAcademico(): BelongsTo
  {
    return $this->belongsTo(NivelAcademico::class);
  }

  public function estadoNivelAcademico(): BelongsTo
  {
    return $this->belongsTo(EstadoNivelAcademico::class);
  }

  public function ocupacion(): BelongsTo
  {
    return $this->belongsTo(Ocupacion::class);
  }

  public function profesion(): BelongsTo
  {
    return $this->belongsTo(Profesion::class);
  }

  public function roles(): BelongsToMany
  {
    return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id')->withPivot(
      'activo',
      'dependiente'
    );
  }

  public function materiasAprobadasRelacion()
  {
    return $this->hasMany(MateriaAprobadaUsuario::class, 'user_id');
  }

  public function pasosCrecimiento(): BelongsToMany
  {
    return $this->belongsToMany(PasoCrecimiento::class, 'crecimiento_usuario', 'user_id', 'paso_crecimiento_id')->withPivot(
      'estado_id',
      'fecha',
      'detalle',
      'created_at',
      'updated_at'
    );
  }

  public function estadoCivil(): BelongsTo
  {
    return $this->belongsTo(EstadoCivil::class);
  }

  public function tipoVinculacion(): BelongsTo
  {
    return $this->belongsTo(TipoVinculacion::class);
  }

  public function tipoIdentificacion(): BelongsTo
  {
    return $this->belongsTo(TipoIdentificacion::class);
  }

  public function tipoIdentificacionAcudiente(): BelongsTo
  {
    return $this->belongsTo(TipoIdentificacion::class, 'tipo_identificacion_acudiente_id');
  }


  public function sectorEconomico(): BelongsTo
  {
    return $this->belongsTo(SectorEconomico::class);
  }

  public function tipoDeVivienda(): BelongsTo
  {
    return $this->belongsTo(TipoVivienda::class, 'tipo_vivienda_id');
  }

  public function tipoDeSangre(): BelongsTo
  {
    return $this->belongsTo(TipoSangre::class, 'tipo_sangre_id');
  }

  public function pais(): BelongsTo
  {
    return $this->belongsTo(Pais::class);
  }

  public function barrio(): BelongsTo
  {
    return $this->belongsTo(Barrio::class);
  }

  public function localidad(): BelongsTo
  {
    return $this->belongsTo(Localidad::class);
  }

  public function sede(): BelongsTo
  {
    return $this->belongsTo(Sede::class, 'sede_id');
  }

  public function reportesBajaAlta(): HasMany
  {
    return $this->hasMany(ReporteBajaAlta::class);
  }

  public function compras(): HasMany
  {
    return $this->hasMany(Compra::class);
  }

  public function usuarioCreacion(): BelongsTo
  {
    return $this->belongsTo(User::class, 'usuario_creacion_id');
  }

  public function peticiones(): HasMany
  {
    return $this->hasMany(Peticion::class);
  }

  public function tiemposConDios(): HasMany
  {
    return $this->hasMany(TiempoConDios::class);
  }

  public function ruedasDeLaVida(): HasMany
  {
    return $this->hasMany(RuedaDeLaVidaUser::class, 'usuario_id');
  }

  public function reportesReunion(): BelongsToMany
  {
    return $this->belongsToMany(ReporteReunion::class, "asistencia_reuniones")
      ->withPivot('autor_creacion_asistencia_id');
  }

  public function reservasReunion(): HasMany
  {
    return $this->hasMany(ReservaReunion::class, 'user_id');
  }

  public function reportesGrupo(): BelongsToMany
  {
    return $this->belongsToMany(ReporteGrupo::class, "asistencia_grupos")
      ->withPivot('asistio', 'observaciones', 'tipo_inasistencia_id', 'created_at', 'updated_at');
  }

  public function tareasConsolidacion(): BelongsToMany
  {
    return $this->belongsToMany(TareaConsolidacion::class, 'tarea_consolidacion_usuario', 'user_id', 'tarea_consolidacion_id')
      ->using(TareaConsolidacionUsuario::class)
      ->withPivot('id', 'estado_tarea_consolidacion_id', 'fecha')
      ->withTimestamps();
  }



  public function asignacionesConsolidacion(): HasMany
  {
    return $this->hasMany(TareaConsolidacionUsuario::class, 'user_id');
  }

  ///relacion de muchos a muchos entre usuarios y usuarios(Parientes)
  // Ejemplo Fabian es padre de Isabella
  public function parientesDelUsuario(): BelongsToMany
  {
    return $this->belongsToMany(User::class, "parientes_usuarios", "user_id", "pariente_user_id")
      ->withPivot('id', 'es_el_responsable', 'tipo_pariente_id', 'created_at', 'updated_at');
  }

  ///relacion de muchos a muchos entre  usuarios(Parientes) y usuarios
  // Ejemplo Isabella es Hija de Fabian
  public function usuariosDelPariente(): BelongsToMany
  {
    return $this->belongsToMany(User::class, "parientes_usuarios", "pariente_user_id", "user_id")
      ->withPivot('id', 'es_el_responsable', 'tipo_pariente_id', 'created_at', 'updated_at');
  }

  public function iglesiaEncargada(): BelongsToMany
  {
    return $this->belongsToMany(Iglesia::class, 'pastores_principales', 'user_id', 'iglesia_id')->withTimestamps();
  }

  public function camposExtras(): BelongsToMany
  {
    return $this->belongsToMany(CampoExtra::class, 'usuario_opcion_campo_extra', 'user_id', 'campo_extra_id')
      ->withPivot('valor')
      ->withTimestamps();
  }

  public function camposFormularioUsuario(): BelongsToMany
  {
    return $this->belongsToMany(CampoFormularioUsuario::class, 'usuario_campo_formulario_usuario', 'user_id', 'campo_formulario_usuario_id')
      ->withPivot('valor')
      ->withTimestamps();
  }


  // obtiene los grupos a los que asiste el usuario
  public function gruposDondeAsiste(): BelongsToMany
  {
    return $this->belongsToMany(Grupo::class, 'integrantes_grupo', 'user_id', 'grupo_id')->withTimestamps();
  }

  // obtiene los grupos donde el usuario el encargado
  public function gruposEncargados(): BelongsToMany
  {
    return $this->belongsToMany(Grupo::class, 'encargados_grupo', 'user_id', 'grupo_id')->withTimestamps();
  }

  // obtiene los grupos excluidos
  public function gruposExcluidos(): BelongsToMany
  {
    return $this->belongsToMany(Grupo::class, 'grupos_excluidos', 'user_id', 'grupo_id')->withTimestamps();
  }

  public function encargadosDirectos()
  {
    $lideres = $this->gruposDondeAsiste()
      ->leftJoin('encargados_grupo', 'grupos.id', '=', 'encargados_grupo.grupo_id')
      ->leftJoin('users AS encargados', 'encargados_grupo.user_id', '=', 'encargados.id')
      ->leftJoin('tipo_usuarios', 'encargados.tipo_usuario_id', '=', 'tipo_usuarios.id')
      ->whereNotNull('encargados.id')
      ->selectRaw(
        "encargados.id, CONCAT(encargados.primer_nombre, ' ',encargados.primer_apellido) as nombre, encargados.primer_nombre, encargados.primer_apellido, encargados.segundo_nombre, encargados.segundo_apellido, foto,
        tipo_usuarios.nombre as tipo_usuario, tipo_usuarios.color, tipo_usuarios.icono"
      )
      ->get()
      ->unique('id');

    return $lideres;
  }

  // trae los lideres ascendentes
  public function lideres($tipo = "objeto")
  {
    $array_lideres = [];
    $array_ids_lideres_no_repetidos = [];
    $array_ids_nuevos_lideres = [];

    $ids_grupos_asistentes = IntegranteGrupo::where('integrantes_grupo.user_id', $this->id)
      ->select('grupo_id')
      ->pluck('grupo_id')
      ->toArray();

    array_push($array_ids_lideres_no_repetidos, $this->id);
    //Nueva variable para evitar los lideres excluídos en la jerarquía de líderes
    $ids_grupos_recorridos = $ids_grupos_asistentes;

    while (count($array_ids_lideres_no_repetidos) > 0) {
      $array_ids_nuevos_lideres = [];
      $grupos = Grupo::whereIn('grupos.id', $ids_grupos_asistentes)->get();

      foreach ($grupos as $grupo) {
        $array_ids_nuevos_lideres = array_merge(
          $array_ids_nuevos_lideres,
          $grupo->encargados()->select('users.id')
            ->pluck('users.id')
            ->toArray()
        );
      }

      $array_ids_nuevos_lideres = array_values(array_unique($array_ids_nuevos_lideres));

      $ids_grupos_asistentes = IntegranteGrupo::whereIn('integrantes_grupo.user_id', $array_ids_nuevos_lideres)
        ->select('grupo_id')
        ->pluck('grupo_id')
        ->toArray();

      $array_ids_lideres_no_repetidos = array_diff($array_ids_nuevos_lideres, $array_lideres);
      $array_ids_lideres_no_repetidos = array_values(array_unique($array_ids_lideres_no_repetidos));
      $array_lideres = array_merge($array_lideres, $array_ids_nuevos_lideres);
      //Nuevo array merge para evitar los lideres excluídos en la jerarquía de líderes
      $ids_grupos_recorridos = array_merge($ids_grupos_recorridos, $ids_grupos_asistentes);
    }
    //Nuevas líneas para evitar los lideres excluídos en la jerarquía de líderes
    $ids_asistentes_excluidos = GrupoExcluido::whereIn('grupo_id', $ids_grupos_recorridos)
      ->select('user_id')
      ->pluck('user_id')
      ->toArray();

    $array_lideres = array_diff($array_lideres, $ids_asistentes_excluidos);

    if ($tipo == "objeto") {
      $array_lideres = User::whereIn('users.id', $array_lideres);
    }

    return $array_lideres;
  }

  // relacion para saber que usuarios sirven en un grupo
  public function grupoServicio(): BelongsToMany
  {
    return $this->belongsToMany(Grupo::class, 'servidores_grupo', 'user_id', 'grupo_id');
  }


  // funcion que devuelve la edad del usuario
  public function edad()
  {
    $edad = Carbon::parse($this->fecha_nacimiento)->age;
    return $edad;
  }

  // funcion que devuele el rango segun la edad de usuario
  public function rangoEdad()
  {
    $edad = Carbon::parse($this->fecha_nacimiento)->age;
    return RangoEdad::where('edad_maxima', '>=', $edad)->where('edad_minima', '<=', $edad)->first();
  }

  public function telefonoMovilPrefijo()
  {
    $iglesia = Iglesia::find(1);
    $prefijo = $this->pais ? $this->pais->prefijo : ($iglesia->pais ? $iglesia->pais->prefijo : '');
    $telefonoMovil = $this->telefono_movil;

    if ($telefonoMovil != "") {
      $validarTelefono = strpos($telefonoMovil, '+');
      if ($validarTelefono == FALSE) {
        $telefonoMovil = $prefijo . $telefonoMovil;
      }
    }

    return $telefonoMovil;
  }

  //
  public function nombre($tipo = 2)
  {

    switch ($tipo) {
      case 2:
        $nombre = $this->primer_nombre . " " . $this->segundo_nombre;
        break;
      case 3:
        $nombre = $this->primer_nombre . " " . $this->segundo_nombre . " " . $this->primer_apellido;
        break;
      case 4:
        $nombre = $this->primer_nombre . " " . $this->segundo_nombre . " " . $this->primer_apellido . " " . $this->segundo_apellido;
        break;
      default:
        $nombre = $this->primer_nombre;
    }

    return $nombre;
  }

  public function inicialesNombre()
  {
    $primerLetraNombre = mb_substr($this->primer_nombre, 0, 1);
    $primerLetraApellido = mb_substr($this->primer_apellido, 0, 1);
    return strtoupper($primerLetraNombre . "" . $primerLetraApellido);
  }

  // funcion que devuelve True/false segun el estado de la actividad usuario en los grupos
  public function estadoActividadGrupos()
  {
    $dias = Configuracion::select('tiempo_para_definir_inactivo_grupo')->find(1)->tiempo_para_definir_inactivo_grupo;
    $fechaUltimoReporte = Carbon::parse($this->ultimo_reporte_grupo)->format('Y-m-d');
    $fechaActividad = Carbon::now()
      ->subDays($dias)
      ->format('Y-m-d');

    $fechaUltimoReporte >= $fechaActividad ? ($activo = true) : ($activo = false);
    return $activo;
  }

  // funcion que devuelve True/false segun el estado de la actividad usuario en las reuniones (Cultos)
  public function estadoActividadReuniones()
  {
    $dias = Configuracion::select('tiempo_para_definir_inactivo_reunion')->find(1)
      ->tiempo_para_definir_inactivo_reunion;
    $fechaUltimoReporte = Carbon::parse($this->ultimo_reporte_reunion)->format('Y-m-d');
    $fechaActividad = Carbon::now()
      ->subDays($dias)
      ->format('Y-m-d');

    $fechaUltimoReporte >= $fechaActividad ? ($activo = true) : ($activo = false);
    return $activo;
  }

  // Funcion que devuelve el ultimo reporte de dado de baja de un usuario
  public function ultimoReporteDadoBaja()
  {
    return $this->reportesBajaAlta()
      ->where('dado_baja', '=', true)
      ->orderBy('created_at', 'DESC')
      ->first();
  }

  // obtiene el ultimo tipo de servicio que presta el usuario en los grupos
  public function ultimoTipoServicioGrupo()
  {
    $servidorGrupo = ServidorGrupo::where('user_id', '=', $this->id)
      ->orderBy('created_at', 'desc')
      ->first();
    $servidorGrupo ? ($tipoServicio = $servidorGrupo->tipoServicioGrupo->first()) : ($tipoServicio = null);
    return $tipoServicio;
  }

  // obtiene todos los tipos de servicios que presta el usuario en los grupos
  public function serviciosPrestadosEnGrupos($grupoId = null)
  {
    if ($grupoId) {
      $servidorGrupo = ServidorGrupo::where('user_id', '=', $this->id)
        ->leftJoin('servicios_servidores_grupo', 'servidores_grupo.id', '=', 'servicios_servidores_grupo.servidores_grupo_id')
        ->leftJoin('grupos', 'servidores_grupo.grupo_id', '=', 'grupos.id')
        ->leftJoin('tipo_grupos', 'grupos.tipo_grupo_id', '=', 'tipo_grupos.id')
        ->leftJoin('tipo_servicio_grupos', 'servicios_servidores_grupo.tipo_servicio_grupos_id', '=', 'tipo_servicio_grupos.id')
        ->whereNotNull('tipo_servicio_grupos.id')
        ->select('tipo_servicio_grupos.*', 'grupos.nombre as nombreGrupo', 'tipo_grupos.nombre as nombreTipoGrupo')
        ->get();
    } else {
      $servidorGrupo = ServidorGrupo::where('user_id', '=', $this->id)
        ->where('grupo_id', '=', $grupoId)
        ->leftJoin('servicios_servidores_grupo', 'servidores_grupo.id', '=', 'servicios_servidores_grupo.servidores_grupo_id')
        ->leftJoin('grupos', 'servidores_grupo.grupo_id', '=', 'grupos.id')
        ->leftJoin('tipo_grupos', 'grupos.tipo_grupo_id', '=', 'tipo_grupos.id')
        ->leftJoin('tipo_servicio_grupos', 'servicios_servidores_grupo.tipo_servicio_grupos_id', '=', 'tipo_servicio_grupos.id')
        ->whereNotNull('tipo_servicio_grupos.id')
        ->select('tipo_servicio_grupos.*', 'grupos.nombre as nombreGrupo', 'tipo_grupos.nombre as nombreTipoGrupo')
        ->get();
    }

    return $servidorGrupo;
  }
  public function asistenciasActividades()
  {
    return $this->hasMany(ActividadAsistencia::class);
  }
  public function inscripciones()
  {
    // Usamos 'asistente_id' como la llave foránea
    return $this->hasMany(Inscripcion::class, 'asistente_id');
  }

  //obtiene los formularios correspondientes al rol de usuario
  public function formularios($tipoId, $edad)
  {
    $rolActivo = $this->roles()
      ->wherePivot('activo', true)
      ->first();

    return $rolActivo
      ?->formularios()
      ->where('tipo_formulario_id', '=', $tipoId)
      ->where('edad_minima', '<=', $edad)
      ->where('edad_maxima', '>=', $edad)
      ->get();
  }

  // Función que devuelve todos los discipulos (directos e indirectos) de un asistente
  // lo que retorna puede luego apuntar a count o sum o where, etc
  public function discipulos($lista = 'sin-eliminados', $soloVerificados = true, $returnQuery = false)
  {
    $rolActivo = $this->roles()
      ->wherePivot('activo', true)
      ->first();

    $query = User::query()->select('users.*');

    // 1. Caso: Pastor Principal de Iglesia (acceso total)
    if (isset($this->iglesiaEncargada()->first()->id)) {
      $query->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
        ->addSelect('integrantes_grupo.grupo_id');
    }
    // 2. Caso: Administrativo de Sede
    elseif ($rolActivo && Sede::find($rolActivo->lista_asistentes_sede_id ?? null)) {
      $query->where('users.sede_id', $rolActivo->lista_asistentes_sede_id)
        ->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
        ->addSelect('integrantes_grupo.grupo_id');
    }
    // 3. Caso: Ministerio (Jerarquía de grupos)
    else {
      // Obtenemos solo los IDs de los grupos para minimizar carga
      $gruposIds = $this->gruposMinisterio(tipo: 'array');

      if (empty($gruposIds)) {
        $gruposIds = [0];
      }

      $asistentesSinGrupoIds = User::where('usuario_creacion_id', $this->id)
        ->doesntHave('gruposDondeAsiste')
        ->pluck('id')
        ->toArray();

      if (empty($asistentesSinGrupoIds)) {
        $asistentesSinGrupoIds = [0];
      }

      $query->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
        ->addSelect('integrantes_grupo.grupo_id')
        ->where(function ($q) use ($gruposIds, $asistentesSinGrupoIds) {
          $q->whereIn('integrantes_grupo.grupo_id', $gruposIds)
            ->orWhereIn('users.id', $asistentesSinGrupoIds);
        });
    }

    // Filtros de eliminados (SoftDeletes)
    if ($lista == 'solo-eliminados') {
      $query->onlyTrashed();
    } elseif ($lista == 'todos') {
      $query->withTrashed();
    }
    // 'sin-eliminados' es el default, no necesita llamada extra si no se usa withTrashed/onlyTrashed antes.

    // Filtro verificados
    if ($soloVerificados) {
      $query->whereNotNull('email_verified_at');
    }

    $query->distinct('users.id');

    if ($returnQuery) {
      return $query;
    }

    return $query->get();
  }

  public function gruposMinisterio($tipo = 'objeto')
  {
    $rolActivo = $this->roles()->wherePivot('activo', true)->first();

    // Verificación rápida de rol Sede
    if ($rolActivo && $rolActivo->lista_asistentes_sede_id) {
      $sedeId = $rolActivo->lista_grupos_sede_id;
      // Si el rol tiene una sede asignada para ver grupos, retornamos esos grupos
      if ($tipo == 'objeto') {
        return Grupo::whereHas('sede', function ($q) use ($sedeId) {
          $q->where('id', $sedeId);
        })->get();
      } else {
        return Grupo::whereHas('sede', function ($q) use ($sedeId) {
          $q->where('id', $sedeId);
        })->pluck('id')->toArray();
      }
    }

    // CTE Recursivo para jerarquía de ministerio
    // La lógica es: Usuario -> Encargado de Grupo -> (Grupo tiene Integrantes) -> Integrante es Encargado de otro Grupo...

    // 1. Grupos iniciales: donde el usuario actual es encargado
    // 2. Grupos excluidos: se detiene la recursión ahí.

    $userId = $this->id;

    $query = "
        WITH RECURSIVE ministerio_cte AS (
            -- ANCHOR: Grupos donde el usuario es encargado directo
            SELECT
                g.id as grupo_id,
                g.nombre
            FROM grupos g
            INNER JOIN encargados_grupo eg ON g.id = eg.grupo_id
            WHERE eg.user_id = ?
            AND NOT EXISTS (
                SELECT 1 FROM grupos_excluidos ge
                WHERE ge.user_id = ?
                AND ge.grupo_id = g.id
            )

            UNION DISTINCT

            -- RECURSIVE: Grupos donde los integrantes de los grupos anteriores son encargados
            SELECT
                g_child.id as grupo_id,
                g_child.nombre
            FROM grupos g_child
            INNER JOIN encargados_grupo eg_child ON g_child.id = eg_child.grupo_id
            INNER JOIN integrantes_grupo ig_parent ON eg_child.user_id = ig_parent.user_id
            INNER JOIN ministerio_cte parent_cte ON ig_parent.grupo_id = parent_cte.grupo_id
            WHERE NOT EXISTS (
                SELECT 1 FROM grupos_excluidos ge
                WHERE ge.user_id = ?
                AND ge.grupo_id = g_child.id
            )
        )
        SELECT * FROM ministerio_cte;
    ";

    // Nota sobre exclusión: La lógica original obtenía los grupos excluidos DEL USUARIO ACTUAL ($this->id).
    // Y los restaba de $array_ids_nuevos_grupos. Esto significa que si el usuario actual excluye el Grupo X,
    // entonces el Grupo X y sus descendientes no se procesan desde la perspectiva de este usuario.
    // El CTE arriba hace `WHERE NOT EXISTS` comparando con el `$userId` raíz en ambos niveles (base y recursivo).

    // Ejecutamos la consulta raw para obtener IDs.
    // Pasamos el ID 3 veces: 1 para el WHERE del Anchor, 1 para el Excluded del Anchor, 1 para el Excluded Recursivo.
    $result = DB::select($query, [$userId, $userId, $userId]);
    $groupIds = array_column($result, 'grupo_id');

    // Grupos sin líder creados por este asistente (lógica original preservada)
    $gruposSinLider = Grupo::where('usuario_creacion_id', $this->id)
      ->leftJoin('encargados_grupo', 'grupos.id', '=', 'encargados_grupo.grupo_id')
      ->whereNull('encargados_grupo.grupo_id')
      ->pluck('grupos.id')
      ->toArray();

    $todosGruposIds = array_unique(array_merge($groupIds, $gruposSinLider));

    if ($tipo == 'objeto') {
      return Grupo::whereIn('id', $todosGruposIds);
    } else {
      return $todosGruposIds;
    }
  }

  // --- RELACIONES PARA FORO DEL CURSO (LMS) ---
  public function hilosForo(): HasMany
  {
    return $this->hasMany(CursoForoHilo::class, 'user_id');
  }

  public function respuestasForo(): HasMany
  {
    return $this->hasMany(CursoForoRespuesta::class, 'user_id');
  }

  public function cambiarGrupo($grupo_id, $cambio = "sin-ministerio")
  {
    if ($grupo_id != NULL) {
      //Verifico si ya existe alguna relación de integrante entre este asistente y este grupo
      if (IntegranteGrupo::where("integrantes_grupo.user_id", $this->id)->where("grupo_id", $grupo_id)->count() == 0) {
        $relacion_asistente_grupo = new IntegranteGrupo;
        $relacion_asistente_grupo->grupo_id = $grupo_id;
        $relacion_asistente_grupo->user_id = $this->id;
        $relacion_asistente_grupo->save();
        $this->asignarSede($grupo_id);
        return true;
      }
    }
  }

  public function desvincularDeGrupo($grupo_id)
  {
    if ($grupo_id != NULL) {
      //Verifico si ya existe alguna relación de integrante entre este asistente y este grupo
      if (IntegranteGrupo::where("integrantes_grupo.user_id", $this->id)->where("grupo_id", $grupo_id)->count() > 0) {
        $relacion_asistente_grupo = IntegranteGrupo::where("integrantes_grupo.user_id", $this->id)->where("grupo_id", $grupo_id)->delete();
      }
    }
    return TRUE;
  }

  public function asignarSede($grupo_id = "")
  {
    if ($grupo_id == "") {
      //La sede con default TRUE es la sede principal
      $sedeDefault = Sede::where('default', TRUE)->first();

      if (auth()->check()) {
        $rolActivo = auth()->user()->roles()->wherePivot('activo', true)->first();

        if (auth()->user()->sede_id) {
          $this->sede_id = auth()->user()->sede_id;
        } else {
          $sede = Sede::find($rolActivo->lista_asistentes_sede_id);
          $sede
            ? $this->sede_id = $rolActivo->lista_asistentes_sede_id
            : $this->sede_id = $sedeDefault->id;
        }
      } else {
        $this->sede_id = $sedeDefault->id;
      }
    } else {
      $grupo = Grupo::find($grupo_id);
      $grupo
        ? $this->sede_id = $grupo->sede_id
        : '';
    }

    $this->save();
  }

  public function sedesEncargadas($tipo = "objeto")
  {

    $sedes = Sede::leftJoin('grupos', 'grupos.id', '=', 'sedes.grupo_id')
      ->leftJoin('encargados_grupo', 'grupos.id', '=', 'encargados_grupo.grupo_id')
      ->leftJoin('users', 'users.id', '=', 'encargados_grupo.user_id')
      ->where('users.id', '=', $this->id)
      ->select('sedes.*');

    if ($tipo == 'array') {
      return $sedes->select('sedes.id')
        ->pluck('sedes.id')
        ->toArray();
    } else {
      return $sedes->get();
    }
  }

  public function misPeticiones()
  {
    $rolActivo = $this->roles()
      ->wherePivot('activo', true)
      ->first();

    //Verifica si es administrador de una sede
    if (Sede::find($rolActivo->lista_peticiones_sede_id ?? null)) {
      $peticiones = Peticion::leftJoin('users', 'peticiones.user_id', '=', 'users.id')
        ->where('users.sede_id', $rolActivo->lista_peticiones_sede_id);
    } else {

      $gruposIds = $this->gruposMinisterio()
        ->select('id')
        ->pluck('id')
        ->toArray();

      $asistentes_sin_grupo_creados_por_este_asistente = User::where('usuario_creacion_id', $this->id)
        ->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
        ->whereNull('integrantes_grupo.user_id')
        ->select('users.id')
        ->pluck('users.id')
        ->unique('users.id')
        ->toArray();

      if (count($gruposIds) == 0) {
        $gruposIds = [0];
      }

      if (count($asistentes_sin_grupo_creados_por_este_asistente) == 0) {
        $asistentes_sin_grupo_creados_por_este_asistente = [0];
      }

      $peticiones = Peticion::leftJoin('users', 'peticiones.user_id', '=', 'users.id')
        ->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
        ->where(function ($query) use ($gruposIds, $asistentes_sin_grupo_creados_por_este_asistente) {
          $query->whereRaw(
            'integrantes_grupo.grupo_id IN (' .
              implode(',', $gruposIds) .
              ') or users.id IN (' .
              implode(',', $asistentes_sin_grupo_creados_por_este_asistente) .
              ')'
          );
        });
    }

    return $peticiones
      ->select('peticiones.*', 'users.foto', 'users.telefono_fijo', 'users.telefono_movil', 'users.telefono_otro', 'users.email', 'users.primer_nombre', 'users.segundo_nombre', 'users.primer_apellido', 'genero')
      ->get()
      ->unique('id');
  }

  public function consolidacion($lista = 'sin-eliminados')
  {
    $rolActivo = $this->roles()->wherePivot('activo', true)->first();


    //Verifica si es administrador de una sede
    if (Zona::find($rolActivo->zona_de_consolidacion_id ?? null)) {

      $zona = Zona::find($rolActivo->zona_de_consolidacion_id);
      $sedesIds = $zona->sedes()->select('sedes.id')
        ->pluck('sedes.id')
        ->toArray();

      $localidadesIds = $zona->localidades()->select('localidades.id')
        ->pluck('localidades.id')
        ->toArray();

      if ($lista == 'sin-eliminados') {
        $discipulosQuery = User::query(); // Inicia la consulta sin eliminados (por defecto)
      } elseif ($lista == 'solo-eliminados') {
        $discipulosQuery = User::onlyTrashed(); // Inicia la consulta solo con eliminados
      } else {
        $discipulosQuery = User::withTrashed(); // Inicia la consulta con y sin eliminados
      }

      $discipulos = $discipulosQuery->where(function ($query) use ($sedesIds, $localidadesIds) {
        if (!empty($sedesIds)) {
          $query->whereIn('users.sede_id', $sedesIds);
        }

        if (!empty($localidadesIds)) {
          $query->whereIn('users.localidad_id', $localidadesIds);
        }
      });
    } else {
      $gruposIds = $this->gruposMinisterio()
        ->select('id')
        ->pluck('id')
        ->toArray();

      $asistentes_sin_grupo_creados_por_este_asistente = User::where('usuario_creacion_id', $this->id)
        ->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
        ->whereNull('integrantes_grupo.user_id')
        ->select('users.id')
        ->pluck('users.id')
        ->unique('users.id')
        ->toArray();

      if (count($gruposIds) == 0) {
        $gruposIds = [0];
      }

      if (count($asistentes_sin_grupo_creados_por_este_asistente) == 0) {
        $asistentes_sin_grupo_creados_por_este_asistente = [0];
      }

      if ($lista == 'sin-eliminados') {
        $discipulos = User::leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id') // Dados de alta
          ->where(function ($query) use ($gruposIds, $asistentes_sin_grupo_creados_por_este_asistente) {
            $query->whereRaw(
              'integrantes_grupo.grupo_id IN (' .
                implode(',', $gruposIds) .
                ') or users.id IN (' .
                implode(',', $asistentes_sin_grupo_creados_por_este_asistente) .
                ')'
            );
          })
          ->whereNull('users.deleted_at'); // Dados de alta
      } elseif ($lista == 'solo-eliminados') {
        $discipulos = User::leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
          ->where(function ($query) use ($gruposIds, $asistentes_sin_grupo_creados_por_este_asistente) {
            $query->whereRaw(
              'integrantes_grupo.grupo_id IN (' .
                implode(',', $gruposIds) .
                ') or users.id IN (' .
                implode(',', $asistentes_sin_grupo_creados_por_este_asistente) .
                ')'
            );
          })
          ->whereNotNull('users.deleted_at'); // Dados de baja
      } else {
        $discipulos = User::withTrashed()
          ->leftJoin('integrantes_grupo', 'users.id', '=', 'integrantes_grupo.user_id')
          ->where(function ($query) use ($gruposIds, $asistentes_sin_grupo_creados_por_este_asistente) {
            $query->whereRaw(
              'integrantes_grupo.grupo_id IN (' .
                implode(',', $gruposIds) .
                ') or users.id IN (' .
                implode(',', $asistentes_sin_grupo_creados_por_este_asistente) .
                ')'
            );
          });
      }
    }

    $tipoUsuariosHabilitados = TipoUsuario::where('habilitado_para_consolidacion', true)
      ->pluck('id')
      ->toArray();

    return $discipulos
      ->whereIn('tipo_usuario_id', $tipoUsuariosHabilitados)
      ->select('users.*')
      ->get()
      ->unique('id');
  }

  public function aplicarFiltroDinamico($filtroId)
  {
    $filtro = FiltroConsolidacion::with('condiciones')->findOrFail($filtroId);
    $query = User::query();

    foreach ($filtro->condiciones as $condicion) {
      $tareaIdRequerida = $condicion->id;
      $estadoIdRequerido = $condicion->pivot->estado_tarea_consolidacion_id;

      // Verificamos si la condición es de inclusión (true) o exclusión (false)
      if ($condicion->pivot->incluir) {

        // SI 'incluir' es TRUE, usamos whereHas (el usuario DEBE tenerla)
        $query->whereHas('tareasConsolidacion', function ($subQuery) use ($tareaIdRequerida, $estadoIdRequerido) {
          $subQuery->where('tareas_consolidacion.id', $tareaIdRequerida)
            ->where('tarea_consolidacion_usuario.estado_tarea_consolidacion_id', $estadoIdRequerido);
        });
      } else {
        // SI 'incluir' es FALSE, usamos whereDoesntHave (el usuario NO DEBE tenerla)
        $query->whereDoesntHave('tareasConsolidacion', function ($subQuery) use ($tareaIdRequerida, $estadoIdRequerido) {
          $subQuery->where('tareas_consolidacion.id', $tareaIdRequerida)
            ->where('tarea_consolidacion_usuario.estado_tarea_consolidacion_id', $estadoIdRequerido);
        });
      }
    }

    $personasFiltradas = $query->get();
    return $personasFiltradas;
  }

  // retorna <td></td> con los campos necesarios
  public function dataTd($arrayCamposInfoPersonal, $arrayPasosCrecimiento, $arrayDatosCongregacionales, $arrayCamposExtra)
  {
    $configuracion = Configuracion::find(1);
    $html = '';

    /// aqui se cmezcla el array de todos los campos seleccionados, tanto de los congregacionales como de la información personal
    $arrayTotalCamposSeleccionados = array_merge($arrayCamposInfoPersonal, $arrayDatosCongregacionales);

    $camposInforme = CampoInformeExcel::whereIn('campos_informe_excel.id', $arrayTotalCamposSeleccionados)
      ->orderBy('orden', 'asc')
      ->get();

    // agrego los pasos de crecimiento al encabezado
    $pasosCrecimientoSeleccionados = PasoCrecimiento::whereIn('id', $arrayPasosCrecimiento)->get();

    // agrego los campos extra al encabezado
    $camposExtraSeleccionados = CampoExtra::whereIn('id', $arrayCamposExtra)
      ->orderBy('id', 'asc')
      ->get();

    //tipo identificación
    if ($camposInforme->where('nombre_campo_bd', 'tipo_identificacion')->count() > 0) {
      $html .= '<td>' . ($this->tipoIdentificacion ? $this->tipoIdentificacion->nombre : 'Sin información') . '</td>';
    }


    //identificación
    if ($camposInforme->where('nombre_campo_bd', 'identificacion')->count() > 0) {
      $html .= '<td>' . ($this->identificacion ? $this->identificacion : 'Sin información') . '</td>';
    }

    //edad
    if ($camposInforme->where('nombre_campo_bd', 'edad')->count() > 0) {
      $html .= '<td>' . ($this->fecha_nacimiento ? $this->edad() : 'Sin información') . '</td>';
    }

    //primer nombre
    if ($camposInforme->where('nombre_campo_bd', 'primer_nombre')->count() > 0) {
      $html .= '<td>' . ($this->primer_nombre ? $this->primer_nombre : 'Sin información') . '</td>';
    }

    //segundo nombre
    if ($camposInforme->where('nombre_campo_bd', 'segundo_nombre')->count() > 0) {
      $html .= '<td>' . ($this->segundo_nombre ? $this->segundo_nombre : 'Sin información') . '</td>';
    }

    //primer apellido
    if ($camposInforme->where('nombre_campo_bd', 'primer_apellido')->count() > 0) {
      $html .= '<td>' . ($this->primer_apellido ? $this->primer_apellido : 'Sin información') . '</td>';
    }

    //segundo apellido
    if ($camposInforme->where('nombre_campo_bd', 'segundo_apellido')->count() > 0) {
      $html .= '<td>' . ($this->segundo_apellido ? $this->segundo_apellido : 'Sin información') . '</td>';
    }

    //estado civil
    if ($camposInforme->where('nombre_campo_bd', 'estado_civil')->count() > 0) {
      $html .= '<td>' . ($this->estadoCivil ? $this->estadoCivil->nombre : 'Sin información') . '</td>';
    }

    //pais
    if ($camposInforme->where('nombre_campo_bd', 'pais_id')->count() > 0) {
      $html .= '<td>' . ($this->pais ? $this->pais->nombre : 'Sin información') . '</td>';
    }

    //telefono fijo
    if ($camposInforme->where('nombre_campo_bd', 'telefono_fijo')->count() > 0) {
      $html .= '<td>' . ($this->telefono_fijo ? $this->telefono_fijo : 'Sin información') . '</td>';
    }

    //telefono otro
    if ($camposInforme->where('nombre_campo_bd', 'telefono_otro')->count() > 0) {
      $html .= '<td>' . ($this->telefono_otro ? $this->telefono_otro : 'Sin información') . '</td>';
    }

    //telefono fijo
    if ($camposInforme->where('nombre_campo_bd', 'telefono_movil')->count() > 0) {
      $html .= '<td>' . ($this->telefono_movil ? $this->telefono_movil : 'Sin información') . '</td>';
    }

    //email - correo electronico
    if ($camposInforme->where('nombre_campo_bd', 'email')->count() > 0) {
      $html .= '<td>' . ($this->email ? $this->email : 'Sin información') . '</td>';
    }

    //direccion
    if ($camposInforme->where('nombre_campo_bd', 'direccion')->count() > 0) {
      $html .= '<td>' . ($this->direccion ? $this->direccion : 'Sin información') . '</td>';
    }

    //tipo vivienda
    if ($camposInforme->where('nombre_campo_bd', 'tipo_vivienda')->count() > 0) {
      $html .= '<td>' . ($this->tipoDeVivienda ? $this->tipoDeVivienda->nombre : 'Sin información') . '</td>';
    }

    //nivel educativo
    if ($camposInforme->where('nombre_campo_bd', 'nivel_academico')->count() > 0) {
      $html .= '<td>' . ($this->nivelAcademico ? $this->nivelAcademico->nombre : 'Sin información') . '</td>';
    }

    //estado nivel academico
    if ($camposInforme->where('nombre_campo_bd', 'estado_nivel_academico')->count() > 0) {
      $html .= '<td>' . ($this->estadoNivelAcademico ? $this->estadoNivelAcademico->nombre : 'Sin información') . '</td>';
    }

    //profesion
    if ($camposInforme->where('nombre_campo_bd', 'profesion')->count() > 0) {
      $html .= '<td>' . ($this->profesion ? $this->profesion->nombre : 'Sin información') . '</td>';
    }

    //sector economico
    if ($camposInforme->where('nombre_campo_bd', 'sector_economico')->count() > 0) {
      $html .= '<td>' . ($this->sectorEconomico ? $this->sectorEconomico->nombre : 'Sin información') . '</td>';
    }

    //tipo de sangre
    if ($camposInforme->where('nombre_campo_bd', 'tipo_sangre')->count() > 0) {
      $html .= '<td>' . ($this->tipoDeSangre ? $this->tipoDeSangre->nombre : 'Sin información') . '</td>';
    }

    //indicaciones medicas
    if ($camposInforme->where('nombre_campo_bd', 'indicaciones_medicas')->count() > 0) {
      $html .= '<td>' . ($this->indicaciones_medicas ? $this->indicaciones_medicas : 'Sin información') . '</td>';
    }

    ///informacion opcional
    if ($camposInforme->where('nombre_campo_bd', 'informacion_opcional')->count() > 0) {
      $html .= '<td>' . ($this->informacion_opcional ? $this->informacion_opcional : 'Sin información') . '</td>';
    }

    // dados baja
    if (
      $camposInforme->where('nombre_campo_bd', 'dado_baja')->count() > 0 ||
      $camposInforme->where('nombre_campo_bd', 'dado_alta')->count() > 0 ||
      $camposInforme->where('nombre_campo_bd', 'fecha_dado_baja')->count() > 0 ||
      $camposInforme->where('nombre_campo_bd', 'fecha_dado_alta')->count() > 0
    ) {
      $dadoBaja = $this
        ->reportesBajaAlta()
        ->orderBy('created_at', 'DESC')
        ->first();

      if ($camposInforme->where('nombre_campo_bd', 'dado_alta')->count() > 0) {
        $html .= '<td>' . ($dadoBaja && $dadoBaja->dado_baja == false ? $dadoBaja->tipo->nombre : 'Sin información') . '</td>';
      }

      if ($camposInforme->where('nombre_campo_bd', 'dado_baja')->count() > 0) {
        $html .= '<td>' . ($dadoBaja && $dadoBaja->dado_baja == true ? $dadoBaja->tipo->nombre : 'Sin información') . '</td>';
      }

      if ($camposInforme->where('nombre_campo_bd', 'fecha_dado_alta')->count() > 0) {
        $html .= '<td>' . ($dadoBaja && $dadoBaja->dado_baja == false ? $dadoBaja->fecha : 'Sin fecha de alta') . '</td>';
      }

      if ($camposInforme->where('nombre_campo_bd', 'fecha_dado_baja')->count() > 0) {
        $html .= '<td>' . ($dadoBaja && $dadoBaja->dado_baja == true ? $dadoBaja->fecha : 'Sin fecha de baja') . '</td>';
      }
    }

    // contactos acudientes menores
    $edad = $this->edad();
    if (
      $camposInforme->where('nombre_campo_bd', 'nombre_adulto_responsable')->count() > 0 ||
      $camposInforme->where('nombre_campo_bd', 'contacto_adulto_responsable')->count() > 0
    ) {
      if ($edad < $configuracion->limite_menor_edad) {
        $pariente = DB::table('parientes_usuarios')
          ->where('pariente_user_id', '=', $this->id)
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
            $html .= '<td>' . ($pariente->nombre(3)) . '</td>';
          }

          if ($camposInforme->where('nombre_campo_bd', 'contacto_adulto_responsable')->count() > 0) {
            if ($pariente->telefono_fijo) {
              $html .= '<td>' . $pariente->telefono_fijo . '</td>';
            } elseif ($pariente->telefono_movil) {
              $html .= '<td>' . $pariente->telefono_movil . '</td>';
            } else {
              $html .= '<td>Sin información</td>';
            }
          }
        } else {
          $html .= '<td> No Aplica</td>';
          $html .= '<td> No Aplica</td>';
        }
      } else {
        $html .= '<td> No Aplica</td>';
        $html .= '<td> No Aplica</td>';
      }
    }

    if ($camposInforme->where('nombre_campo_bd', 'nombre_acudiente')->count() > 0) {
      if ($edad < $configuracion->limite_menor_edad) {
        $html .= '<td>' . ($this->nombre_acudiente ? $this->nombre_acudiente : 'Sin información') . '</td>';
      } else {
        $html .= '<td>No Aplica</td>';
      }
    }

    if ($camposInforme->where('nombre_campo_bd', 'telefono_acudiente')->count() > 0) {
      if ($edad < $configuracion->limite_menor_edad) {
        $html .= '<td>' . ($this->telefono_acudiente ? $this->telefono_acudiente : 'Sin información') . '</td>';
      } else {
        $html .= '<td>No Aplica</td>';
      }
    }

    //fecha nacimiento
    if ($camposInforme->where('nombre_campo_bd', 'fecha_nacimiento')->count() > 0) {
      $html .= '<td>' . ($this->fecha_nacimiento ? $this->fecha_nacimiento : 'Sin información') . '</td>';
    }

    //sexo
    if ($camposInforme->where('nombre_campo_bd', 'genero')->count() > 0) {
      $html .= '<td>' . ($this->genero == 1 ? 'Femenino' : 'Masculino') . '</td>';
    }

    // Ultimo reporte grupo
    if ($camposInforme->where('nombre_campo_bd', 'ultimo_reporte_grupo')->count() > 0) {
      $html .= '<td>' . (
        $this->ultimo_reporte_grupo
        ? Carbon::parse($this->ultimo_reporte_grupo)->format('Y-m-d')
        : 'Sin información'
      ) . '</td>';
    }

    // Ultimo reporte reunion
    if ($camposInforme->where('nombre_campo_bd', 'ultimo_reporte_reunion')->count() > 0) {
      $html .= '<td>' . (
        $this->ultimo_reporte_reunion
        ? Carbon::parse($this->ultimo_reporte_reunion)->format('Y-m-d')
        : 'Sin información'
      ) . '</td>';
    }

    // tipo vinculacion
    if ($camposInforme->where('nombre_campo_bd', 'tipo_vinculacion_id')->count() > 0) {
      $html .= '<td>' . ($this->tipoVinculacion ? $this->tipoVinculacion->nombre : 'Sin información') . '</td>';
    }

    //tipo asistente
    if ($camposInforme->where('nombre_campo_bd', 'tipo_asistente_id')->count() > 0) {
      $html .= '<td>' . ($this->tipoUsuario ? $this->tipoUsuario->nombre : 'Sin información') . '</td>';
    }

    //grupo al que pertenece
    if ($camposInforme->where('nombre_campo_bd', 'grupo_id')->count() > 0) {
      $grupo = $this
        ->gruposDondeAsiste()
        ->orderBy('grupo_id', 'desc')
        ->first();
      $html .= '<td>' . ($grupo ? $grupo->nombre : 'Sin información') . '</td>';
    }

    //sede
    if ($camposInforme->where('nombre_campo_bd', 'sede_id')->count() > 0) {
      $html .= '<td>' . ($this->sede ? $this->sede->nombre : 'Sin información') . '</td>';
    }

    //Fecha Creación
    if ($camposInforme->where('nombre_campo_bd', 'created_at')->count() > 0) {
      $html .= '<td>' . ($this->created_at ? $this->created_at : 'Sin información') . '</td>';
    }

    //Usuario Creación
    // Antes tambien tenia asistente_de_creacion_id pero ya quedo obsoleto porque se uniero la tabla user y la tabla asistentes
    if ($camposInforme->where('nombre_campo_bd', 'usuario_creacion_id')->count() > 0) {
      $html .= '<td>' . ($this->usuarioCreacion ? $this->usuarioCreacion->nombre(3) : 'Formulario nuevos') . '</td>';
    }

    //Recepcion Conectate
    if ($camposInforme->where('nombre_campo_bd', 'formulario_conectados')->count() > 0) {
      $html .= '<td>' . ($this->formulario_conectados ? 'SI' : 'NO') . '</td>';
    }

    // AQUI EMPIEZA EL CONSTRUCTOR DE LOS PASOS DE CRECIMIENTO
    foreach ($pasosCrecimientoSeleccionados as $paso) {
      $pasoActual = $this
        ->pasosCrecimiento()
        ->where('paso_crecimiento_id', '=', $paso->id)
        ->first();

      if ($pasoActual) {
        $html .= '<td>' . ($pasoActual->pivot->fecha ? $pasoActual->pivot->fecha : 'Sin Fecha') . '</td>';
        $html .= '<td>' . (
          $pasoActual->pivot->estado == 1
          ? 'No Finalizado'
          : ($pasoActual->pivot->estado == 2
            ? 'En Curso'
            : ($pasoActual->pivot->estado == 3
              ? 'Finalizado'
              : 'Sin estado'))
        ) . '</td>';
        $html .= '<td>' . (
          $pasoActual->pivot->detalle
          ? preg_replace("[\n|\r|\n\r]", '', ucwords(mb_strtolower($pasoActual->pivot->detalle)))
          : 'Sin detalle'
        ) . '</td>';
      } else {
        $html .= '<td>Sin fecha</td>';
        $html .= '<td>Sin estado</td>';
        $html .= '<td>Sin detalle</td>';
      }
    }


    //AQUI EMPIEZA EL CONSTRUCTOR DE PASOS EXTRA
    foreach ($camposExtraSeleccionados as $campo) {
      $campoExtraUsuario = $this
        ->camposExtras()
        ->where('campo_extra_id', $campo->id)
        ->first();
      if ($campo->tipo_de_campo == 1) {
        $html .= '<td>' . ($campoExtraUsuario ? $campoExtraUsuario->pivot->valor : 'Sin información') . '</td>';
      }

      if ($campo->tipo_de_campo == 2) {
        $html .= '<td>' . ($campoExtraUsuario ? $campoExtraUsuario->pivot->valor : 'Sin información') . '</td>';
      }

      if ($campo->tipo_de_campo == 3) {
        if ($campoExtraUsuario) {
          $json_opciones_campo = json_decode($campo->opciones_select);

          foreach ($json_opciones_campo as $opcion) {
            if ($opcion->value == $campoExtraUsuario->pivot->valor) {
              $html .= '<td>' . $opcion->nombre . '</td>';
              break;
            }
          }
        } else {
          $html .= '<td>Sin información</td>';
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
            $html .= '<td>' . $opcion->nombre . '</td>';
          } else {
            $html .= '<td>' . 'Sin información' . '</td>';
          }
        }
      }
    }
    return $html;
  }

  public function cabezalTh($fila, $arrayCamposInfoPersonal = [], $arrayPasosCrecimiento = [], $arrayDatosCongregacionales = [], $arrayCamposExtra = [])
  {
    $htmlFila1 = '';
    $htmlFila2 = '';


    /// aqui se cmezcla el array de todos los campos seleccionados, tanto de los congregacionales como de la información personal
    $arrayTotalCamposSeleccionados = array_merge($arrayCamposInfoPersonal, $arrayDatosCongregacionales);

    $camposInforme = CampoInformeExcel::whereIn('campos_informe_excel.id', $arrayTotalCamposSeleccionados)
      ->orderBy('orden', 'asc')
      ->get();


    foreach ($camposInforme->pluck('nombre_campo_informe')->toArray() as $campo) {
      $htmlFila1 .= '<th><b>' . $campo . '</b></th>';
      $htmlFila2 .= '<th></th>';
    }

    // agrego los pasos de crecimiento al encabezado
    $pasosCrecimientoSeleccionados = PasoCrecimiento::whereIn('id', $arrayPasosCrecimiento)->get();
    foreach ($pasosCrecimientoSeleccionados as $paso) {
      $htmlFila1 .= '<th colspan="3"><b>' . $paso->nombre . '</b></th>';
      $htmlFila2 .= '<th><b>Fecha</b></th> <th><b>Estado</b></th> <th><b>Detalle</b></th>';
    }

    // agrego los campos extra al encabezado
    $camposExtraSeleccionados = CampoExtra::whereIn('id', $arrayCamposExtra)
      ->orderBy('id', 'asc')
      ->get();

    foreach ($camposExtraSeleccionados as $campo) {
      // array_push($arrayEncabezadoFila1, $campo->nombre);
      $colspan = 0;
      if ($campo->tipo_de_campo == 4) {
        $cantidad_opciones = $campo->opciones_select;
        $cantidad_opciones = json_decode($cantidad_opciones);

        foreach ($cantidad_opciones as $cantidad) {
          $colspan++;
          $htmlFila2 .= '<th><b>' . $cantidad->nombre . '</b></th>';
        }
        //$colspan = $colspan > 1 ? $colspan : 1;

      }

      $htmlFila1 .= '<th colspan="' . $colspan . '"><b>' . $campo->nombre . '</b></th>';
      $htmlFila2 .= $colspan == 0 ? '<th></th>' : '';
    }


    if ($fila == 2) {
      return $htmlFila2;
    } else {
      return $htmlFila1;
    }
  }

  public function obtenerRachaSemanalActual()
  {
    $tiempoConDiosHoy = $this
      ->tiemposConDios()
      ->where('fecha', Carbon::now()->format('Y-m-d'))
      ->count();

    $rachaSemanal = 0;

    if ($tiempoConDiosHoy > 0) {
      // Comienzo a calcular las secuencias
      $fechaFinRango = Carbon::now();
      $interrupcionSemana = false;
      do {
        $fechaInicioRango = $fechaFinRango->copy()->subDays(6); // Retroceder 6 días
        $historialSemana = $this
          ->tiemposConDios()
          ->whereBetween('fecha', [$fechaInicioRango->format('Y-m-d'), $fechaFinRango->format('Y-m-d')])
          ->count();

        // Verificar si hay al menos un registro en la semana
        if ($historialSemana != 7) {
          $interrupcionSemana = true;
        } else {
          $rachaSemanal++;
        }

        $fechaFinRango->subDays(7); // Retroceder 7 días para el siguiente rango
      } while ($interrupcionSemana == false);
    }

    return $rachaSemanal;
  }

  public function rachaSemanalActual(): Collection
  {
    $hoy = Carbon::now();
    $lunes = $hoy->copy()->startOfWeek(); // Carbon::MONDAY_START para Laravel 11
    $domingo = $hoy->copy()->endOfWeek(); // Carbon::SUNDAY_END para Laravel 11

    $tiempos = $this->tiemposConDios()
      ->whereBetween('fecha', [$lunes, $domingo])
      ->orderBy('fecha')
      ->get();

    $diasSemana = collect([
      'Lunes' => $lunes->copy(),
      'Martes' => $lunes->copy()->addDay(),
      'Miércoles' => $lunes->copy()->addDays(2),
      'Jueves' => $lunes->copy()->addDays(3),
      'Viernes' => $lunes->copy()->addDays(4),
      'Sábado' => $lunes->copy()->addDays(5),
      'Domingo' => $domingo->copy(),
    ]);

    $rachaDiaria = $diasSemana->map(function ($dia) use ($tiempos) {
      $estado = $tiempos->contains(function ($tiempo) use ($dia) {
        return Carbon::parse($tiempo->fecha)->isSameDay($dia);
      });

      return [
        'dia' => $dia->dayOfWeekIso, // Mantener el objeto Carbon original
        'estado' => $estado,
        'nombreCorto' => $dia->isoFormat('ddd')
      ];
    });


    return $rachaDiaria;
  }

  public function cantidadRachaSemanal(): int
  {
    $hoy = Carbon::now();
    $lunes = $hoy->copy()->startOfWeek(); // Carbon::MONDAY_START para Laravel 11
    $domingo = $hoy->copy()->endOfWeek(); // Carbon::SUNDAY_END para Laravel 11

    $tiempos = $this->tiemposConDios()
      ->whereBetween('fecha', [$lunes, $domingo])
      ->orderBy('fecha')
      ->get();

    $diasDeRacha = 0;
    $fechaActual = $hoy->copy(); // Clonamos $hoy para no modificarlo

    while ($fechaActual->gte($lunes)) { // Mientras la fecha actual sea mayor o igual al lunes
      $tieneTiempoConDios = $tiempos->contains(function ($tiempo) use ($fechaActual) {
        return Carbon::parse($tiempo->fecha)->isSameDay($fechaActual);
      });

      if ($tieneTiempoConDios) {
        $diasDeRacha++;
        $fechaActual->subDay(); // Restar un día
      } else {
        break; // Romper el ciclo si no hay TiempoConDios
      }

      if ($fechaActual->isSameDay($lunes)) {
        break; // Romper el ciclo si hemos llegado al lunes
      }
    }

    return $diasDeRacha;
  }

  public function cantidadRachaDiaria(): int
  {
    $hoy = Carbon::today();

    // 1. Verificamos si existe un registro para HOY.
    // La consulta ->exists() es muy rápida y devuelve solo true/false.
    $tieneRegistroHoy = $this->tiemposConDios()->whereDate('fecha', $hoy)->exists();

    // --- LÓGICA DE RACHA POSITIVA (OPTIMIZADA) ---
    if ($tieneRegistroHoy) {
      $diasDeRacha = 1;
      $fechaAnterior = $hoy->copy()->subDay();

      // 2. Iteramos hacia atrás, haciendo una consulta ->exists() por día.
      // El bucle se detiene en cuanto un día no tenga registro.
      while ($this->tiemposConDios()->whereDate('fecha', $fechaAnterior)->exists()) {
        $diasDeRacha++;
        $fechaAnterior->subDay();
      }

      return $diasDeRacha;
    } else {
      // 3. Buscamos la fecha del último registro.
      // La consulta ->max() también es muy eficiente.
      $ultimaFechaStr = $this->tiemposConDios()->max('fecha');

      // Si el usuario nunca ha tenido un registro, su racha es 0.
      if (!$ultimaFechaStr) {
        return 0;
      }

      // Calculamos la diferencia de días entre hoy y su último registro.
      $ultimaFecha = Carbon::parse($ultimaFechaStr);
      $diasDeAusencia = $ultimaFecha->diffInDays($hoy);

      // Devolvemos el número de días de ausencia como un entero negativo.
      return -$diasDeAusencia;
    }
  }

  public function ultimaRuedaDeLaVida()
  {
    $ultima = $this->ruedasDeLaVida()->orderBy('fecha', 'desc')->first();
    return $ultima ? $ultima->fecha : 'Sin actividad';
  }

  /// conexiones con escuelas

  /**
   * Obtener todos los horarios de materia-periodo a los que el usuario (alumno) está inscrito.
   * Esta relación usa la tabla 'matricula_horario_materia_periodo' como pivote.
   */
  public function matriculas()
  {
    return $this->hasMany(Matricula::class, 'user_id');
  }

  /**
   * Los horarios de materia-periodo a los que el usuario está directamente matriculado.
   * Esto se puede obtener a través de la relación de matrículas.
   */
  public function horariosMatriculados(): BelongsToMany
  {
    // Esta relación ahora se define a través de la tabla 'matriculas'
    return $this->belongsToMany(HorarioMateriaPeriodo::class, 'matriculas', 'user_id', 'horario_materia_periodo_id')
      ->withTimestamps() // Timestamps de la tabla 'matriculas'
      ->withPivot(['id', 'periodo_id', 'pago_id', 'fecha_matricula', 'estado_matricula', 'valor_matricula', 'referencia_pago', 'valor_pagado', 'fecha_pago', 'metodo_pago']) // Todos los campos de 'matriculas' que quieras en el pivot
      ->as('detalles_matricula'); // Alias para el objeto pivot
  }

  /**
   * Obtener todos los reportes de asistencia registrados para este usuario (como alumno).
   */
  public function asistenciasRegistradas(): HasMany
  {
    //               NOMBRE DEL MODELO CAMBIADO AQUÍ 👇
    return $this->hasMany(ReporteAsistenciaEscuela::class, 'alumno_user_id');
  }

  // Los métodos contarAsistenciasEnHorario y contarInasistenciasEnHorario
  // usarán automáticamente la relación actualizada, así que no necesitan cambios internos.
  public function contarAsistenciasEnHorario(int $horarioMateriaPeriodoId): int
  {
    return $this->asistenciasRegistradas()
      ->where('horario_materia_periodo_id', $horarioMateriaPeriodoId)
      ->where('asistio', true)
      ->count();
  }

  public function contarInasistenciasEnHorario(int $horarioMateriaPeriodoId): int
  {
    return $this->asistenciasRegistradas()
      ->where('horario_materia_periodo_id', $horarioMateriaPeriodoId)
      ->where('asistio', false)
      ->count();
  }

  /**
   * Obtiene todos los registros de materias aprobadas/reprobadas para este usuario.
   */
  public function resultadosMaterias(): HasMany
  {
    return $this->hasMany(MateriaAprobadaUsuario::class, 'user_id');
  }


  //// aqui para la gestion de puntos de pago y cajas
  /**
   * Obtiene las cajas que este usuario tiene asignadas.
   */
  public function cajasAsignadas(): HasMany
  {
    return $this->hasMany(Caja::class, 'user_id');
  }

  /**
   * Obtiene los puntos de pago que este usuario administra (Encargado).
   */
  public function puntosDePagoEncargado(): HasMany
  {
    return $this->hasMany(PuntoDePago::class, 'encargado_id');
  }

  public function ultimaActividadTarea()
  {
    return $this->tareasConsolidacion()
      ->orderByPivot('fecha', 'desc')
      ->first();
  }
  public function bitacorasTipoUsuario(): HasMany
  {
    return $this->hasMany(BitacoraTipoUsuario::class);
  }

  public function bitacorasSede(): HasMany
  {
    return $this->hasMany(BitacoraSede::class);
  }

  public function versiculosDiariosLike(): BelongsToMany
  {
    return $this->belongsToMany(VersiculoDiario::class, 'versiculo_usuario_like', 'usuario_id', 'versiculo_diario_id')
      ->withTimestamps();
  }

  /**
   * Publicaciones creadas por el usuario.
   */
  public function posts(): HasMany
  {
    return $this->hasMany(Post::class);
  }

  /**
   * Publicaciones que le han gustado al usuario.
   */
  public function postLikes(): HasMany
  {
    return $this->hasMany(PostLike::class);
  }
}
