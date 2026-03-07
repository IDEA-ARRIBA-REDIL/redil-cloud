<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sede extends Model
{
  use HasFactory;
  protected $table = 'sedes';
  protected $fillable = [
    'id',
    'nombre',
  ];
  protected $guarded = [];

  // obtiene los grupos de una sede
  public function grupos(): HasMany
  {
    return $this->hasMany(Grupo::class);
  }

  // grupo encargado de la sede
  public function grupo(): BelongsTo
  {
    return $this->belongsTo(Grupo::class, 'grupo_id');
  }

   public function reunion(): BelongsTo
  {
    return $this->belongsTo(Reunion::class, 'id');
  }



  public function tipo(): BelongsTo
  {
    return $this->belongsTo(TipoSede::class, 'tipo_sede_id');
  }

  // obtiene los usuarios de una sede
  public function usuarios(): HasMany
  {
    return $this->hasMany(User::class);
  }

   /**
     * Relación: Una Sede tiene muchas Aulas.
     * La llave foránea en la tabla de aulas es "sede_id".
     */
    public function aulas()
    {
        return $this->hasMany(Aula::class, 'sede_id');
    }

  public function encargados()
  {
    $encargados = collect();

    if ($this->grupo && $this->grupo_id)
      $encargados = $this->grupo->encargadosDirectos();

    return $encargados;
  }

  public function usuariosInactivosGrupos()
  {
    $configuracion = Configuracion::find(1);

    $fechaMaximaActividadGrupo = Carbon::now()
      ->subDays($configuracion->tiempo_para_definir_inactivo_grupo)
      ->format('Y-m-d');

    $tiposUsuarios = TipoUsuario::orderBy('orden', 'asc')
      ->where('visible', true)
      ->where('tipo_pastor_principal', '!=', true)
      ->get();

    $tipoUsuariosSeguimientoGrupo = TipoUsuario::where('seguimiento_actividad_grupo', '=', true)
      ->select('id')
      ->pluck('id')
      ->toArray();

    $usuarios = $this->usuarios()->select('id', 'deleted_at', 'ultimo_reporte_grupo', 'sede_id', 'tipo_usuario_id')
      ->get();

    $cantidad = $usuarios->whereNull('deleted_at')
      ->whereIn('tipo_usuario_id', $tiposUsuarios->pluck('id')->toArray())
      ->filter(function ($usuario) use ($fechaMaximaActividadGrupo) {
        return $usuario->ultimo_reporte_grupo < $fechaMaximaActividadGrupo || $usuario->ultimo_reporte_grupo == null;
      })
      ->whereIn('tipo_usuario_id', $tipoUsuariosSeguimientoGrupo)
      ->count();

    return $cantidad;
  }

  public function usuariosInactivosReuniones()
  {
    $configuracion = Configuracion::find(1);

    $fechaMaximaActividadReunion = Carbon::now()
      ->subDays($configuracion->tiempo_para_definir_inactivo_reunion)
      ->format('Y-m-d');

    $tiposUsuarios = TipoUsuario::orderBy('orden', 'asc')
      ->where('visible', true)
      ->where('tipo_pastor_principal', '!=', true)
      ->get();

    $tipoUsuariosSeguimientoReunion = TipoUsuario::where('seguimiento_actividad_reunion', '=', true)
      ->select('id')
      ->pluck('id')
      ->toArray();

    $usuarios = $this->usuarios()->select('id', 'deleted_at', 'ultimo_reporte_reunion', 'sede_id', 'tipo_usuario_id')
      ->get();

    $cantidad = $usuarios->whereNull('deleted_at')
      ->whereIn('tipo_usuario_id', $tiposUsuarios->pluck('id')->toArray())
      ->filter(function ($usuario) use ($fechaMaximaActividadReunion) {
        return $usuario->ultimo_reporte_reunion < $fechaMaximaActividadReunion ||
          $usuario->ultimo_reporte_reunion == null;
      })
      ->whereIn('tipo_usuario_id', $tipoUsuariosSeguimientoReunion)
      ->count();

    return $cantidad;
  }

  public function gruposNoReportados()
  {
    $tiposGruposIds = TipoGrupo::where("seguimiento_actividad", "=", TRUE)->select('id')->pluck('id')->toArray();
    $grupos = $this->grupos()->select('id', 'tipo_grupo_id', 'dado_baja', 'ultimo_reporte_grupo')->get();

    $cantidad = $grupos->where('dado_baja', FALSE)->whereIn('tipo_grupo_id', $tiposGruposIds)->filter(function ($grupo) {
      $fechaMaximaActividad = Carbon::now()
        ->subDays($grupo->tipoGrupo->tiempo_para_definir_inactivo_grupo)
        ->format('Y-m-d');

      return $grupo->ultimo_reporte_grupo < $fechaMaximaActividad || $grupo->ultimo_reporte_grupo == null;
    })->pluck('id')->count();

    return $cantidad;
  }

  public function resetearSede()
  {
    $sedeDefault = Sede::where('default', true)->first();

    //Asigno la sede por defecto al grupo principal de la sede
    $grupoPrincipal = $this->grupo()->first();
    $grupoPrincipal->sede_id = $sedeDefault->id;
    $grupoPrincipal->save();

    //Asigno la sede por defecto a los encargados del grupo principal de la sede
    foreach ($grupoPrincipal->encargados()->get() as $encargado) {
      $encargado->sede_id = $sedeDefault->id;
      $encargado->save();
    }

    //Asigno la sede por defecto a todos los usuarios de la sede
    $asistentes = $this->usuarios()->get();
    foreach ($asistentes as $asistente) {
      $asistente->sede_id = $sedeDefault->id;
      $asistente->save();
    }

    //Asigno la sede por defecto a todos los grupos de la sede
    $grupos = $this->grupos()->get();
    foreach ($grupos as $grupo) {
      $grupo->sede_id = $sedeDefault->id;
      $grupo->save();
    }

    return TRUE;
  }

  public function reuniones(): BelongsToMany
  {
    return $this->belongsToMany(Sede::class, 'reuniones_sedes', 'sedes_id', 'reuniones_id')->withPivot(

      'created_at',
      'updated_at'
    );
  }

  public function zonas(): BelongsToMany
  {
    return $this->belongsToMany(Sede::class, 'sede_zona', 'sede_id', 'zona_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }

  public function consejeros():BelongsToMany
  {
      return $this->belongsToMany(Consejero::class, 'consejero_sede');
  }
}
