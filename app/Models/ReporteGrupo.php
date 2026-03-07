<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


use Carbon\Carbon;

class ReporteGrupo extends Model
{
  use HasFactory;
  protected $table = 'reporte_grupos';
  protected $guarded = [];

  protected static function booted(): void
  {
    static::creating(function (ReporteGrupo $reporte) {
      if (!$reporte->sede_id && $reporte->grupo_id) {
        $grupo = Grupo::find($reporte->grupo_id);
        if ($grupo) {
          $reporte->sede_id = $grupo->sede_id;
        }
      }
    });
  }

   protected $casts = [
      'datos' => 'json', // o 'object' si prefieres objetos PHP
      'informacion_del_grupo' => 'json',
      'informacion_encargado_grupo' => 'json',
      'encargados_ascendentes' => 'json',
      'sumatoria_adicional_clasificacion' => 'json',
      'ids_grupos_ascendentes' => 'json'
    ];

  //funcion para crear relacion uno a muchos entre Reporte_Grupos y Grupo
  public function grupo(): BelongsTo
  {
    return $this->belongsTo(Grupo::class,'grupo_id');
  }

  //funcion para crear relacion uno a muchos entre Reporte_Grupos y MotivoNoReporteGrupo
  public function motivo(): BelongsTo
  {
    return $this->belongsTo(MotivoNoReporteGrupo::class,'motivo_no_reporte_grupo_id');
  }

  //funcion para crear relacion uno a muchos entre Reporte_Grupos y MotivoDesaprobacionReporte
  public function motivoDesaprobacion(): BelongsTo
  {
    return $this->belongsTo(MotivoDesaprobacionReporteGrupo::class,'movito_desaprobacion_id');
  }

  //funcion para crear relacion muchos a muchos entre Reporte_Grupos y usuarios(Asistentes)
  public function usuarios(): BelongsToMany
  {
    return $this->belongsToMany(User::class, "asistencia_grupos")
    ->withPivot('asistio','observaciones','tipo_inasistencia_id','created_at','updated_at');
  }

  // relacion de muchos a muchos de REPORTES_GRUPO  con la tabla CLASIFICACION_ASISTENTES
  public function clasificaciones(): BelongsToMany
  {
     return $this->belongsToMany(
       ClasificacionAsistente::class,
       'clasificacion_asistente_reporte_grupo',
       'reporte_grupo_id',
       'clasificacion_asistente_id'
     )->withPivot('created_at', 'updated_at', 'cantidad');
  }

  // relacion para saber las ofrendas que pertenecen a un reporte de grupo
  public function ofrendas(): BelongsToMany
  {
      return $this->belongsToMany(
        Ofrenda::class,
        "ofrenda_grupos",
        'reporte_grupo_id',
        'ofrenda_id'
      )->withTimestamps();
  }

  public function sePuedeCompartirLinkDeAsistencia() :bool
  {
    $grupo = $this->grupo;
    if (!$this->grupo || !$this->fecha || !$this->grupo->hora) {
      return false;
    }

    if($this->finalizado)
    return false;

    $plazoParaLinkDeAsistencia = $grupo->tipoGrupo->horasDisponiblidadLinkAsistencia;
    $fechaHoraReunion = Carbon::parse($this->fecha . ' ' . $grupo->hora);
    $limiteFechaHoraLinkAsistencia = $fechaHoraReunion->copy()->addHours($plazoParaLinkDeAsistencia)->format('Y-m-d H:i:s');

    $ahora = Carbon::now();

    // 4. Comparar: ¿Es la hora actual menor o igual a la hora límite?
    // $ahora->lte($limiteParaCompartir) significa "ahora es Less Than or Equal (menor o igual que) el límite"
    return $ahora->lte($limiteFechaHoraLinkAsistencia);

  }

  public function totalOfrendas($tipo='valor'){

    $total = $this->ofrendas()->sum($tipo);
    return $total;
  }

  public function totalOfrendasDeLaPersona($userId, $tipo="valor")
  {
    $total = $this->ofrendas()->where('user_id', $userId)->sum($tipo);
    return $total;
  }

}
