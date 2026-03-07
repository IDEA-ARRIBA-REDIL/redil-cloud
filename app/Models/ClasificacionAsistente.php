<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClasificacionAsistente extends Model
{
  use HasFactory;
  protected $table = 'clasificaciones_asistentes';
  protected $guarded = [];

  // relacion de muchos a muchos de TIPO GRUPO  con la tabla CLASIFICACION_ASISTENTES_REPORTE_GRUPO
  public function tipoGrupos(): BelongsToMany
  {
    return $this->belongsToMany(
      TipoGrupo::class,
      'clasificacion_asistente_tipo_grupo',
      'clasificacion_asistente_id',
      'tipo_grupo_id'
    )->withPivot('created_at', 'updated_at');
  }

  public function reportesReuniones(): BelongsToMany
  {
    return $this->belongsToMany(
      ReporteReunion::class,
      'clasificacion_asistente_reporte_reunion',
      'clasificacion_asistente_id',
      'reporte_reunion_id',
    )->withPivot('cantidad')->withTimestamps();
  }

  public function reuniones(): BelongsToMany
  {
    return $this->belongsToMany(
      Reunion::class,
      'clasificacion_asistente_reunion',
      'clasificacion_asistente_id',
      'reunion_id'
    )->withTimestamps();
  }

  public function tipoUsuarios(): BelongsToMany
  {
    return $this->belongsToMany(
      TipoUsuario::class,
      'clasificacion_asistente_tipo_usuario',
      'clasificacion_asistente_id',
      'tipo_usuario_id'
    )->withPivot('edad_minima','edad_maxima','paso_id','estado_paso','fecha_ingreso_igual_fecha_reporte','fecha_dado_alta_igual_fecha_reporte','fecha_paso_igual_fecha_reporte')
    ->withTimestamps();
  }

  public function bloques(): BelongsToMany
  {
      return $this->belongsToMany(
          BloqueClasificacionAsistente::class,
          'bloque_clasif_asistente_clasif_asistente',
          'clasificacion_asistente_id',
          'bloque_id'
      );
  }
}
