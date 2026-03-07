<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Reunion extends Model
{
  use HasFactory;
  use SoftDeletes;

  protected $table = 'reuniones';

  protected $fillable = [
    'hora',
    'dia',
    'nombre',
    'descripcion',
    'sede_id',
    'dias_plazo_reporte',
    'habilitar_reserva',
    'dias_plazo_reserva',
    'aforo',
    'cantidad_maxima_reserva_invitados',
    'solo_reservados_pueden_asistir',
    'hora_maxima_reportar_asistencia',
    'habilitar_preregistro_iglesia_infantil',
  ];

  public function reportes(): HasMany
  {
    return $this->hasMany(ReporteReunion::class);
  }

  public function sedes(): BelongsToMany
  {
    return $this->belongsToMany(Sede::class, 'reuniones_sedes', 'reuniones_id', 'sedes_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }

  public function sede(): BelongsTo
  {
    return $this->belongsTo(Sede::class);
  }

  public function tiposOfrendas(): BelongsToMany
  {
    return $this->belongsToMany(TipoOfrenda::class, 'reunion_tipo_ofrenda', 'reuniones_id', 'tipo_ofrenda_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }

  public function rangosEdades(): BelongsToMany
  {
    return $this->belongsToMany(RangoEdad::class, 'reuniones_rangos_edades', 'reuniones_id', 'rangos_edades_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }

  public function tipoUsuarios(): BelongsToMany
  {
    return $this->belongsToMany(TipoUsuario::class, 'reunion_tipo_usuario', 'reunion_id', 'tipo_usuario_id');
  }

  public function clasificacionesAsistentes(): BelongsToMany
  {
    return $this->belongsToMany(
      ClasificacionAsistente::class,
      'clasificacion_asistente_reunion',
      'reunion_id',
      'clasificacion_asistente_id'
    )->withPivot('created_at', 'updated_at');
  }
}
