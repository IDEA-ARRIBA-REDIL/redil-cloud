<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipoUsuario extends Model
{
  use HasFactory;

  protected $table = 'tipo_usuarios';
  protected $guarded = [];
  protected $fillable = [
    'nombre',
    'descripcion',
    'color',
    'icono',
    'imagen',
    'nombre_plural',
    'tipo_pastor',
    'tipo_pastor_principal',
    'id_rol_dependiente',
    'orden',
    'seguimiento_actividad_grupo',
    'seguimiento_actividad_reunion',
    'habilitado_para_consolidacion',
    'puntaje',
    'visible',
    'default'
  ];

  public function actividadTipoUsuario()
  {
    return $this->belongsToMany(Actividad::class, 'actividad_tipos_usuarios', 'tipo_usuario_id', 'actividad_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }

  public function reuniones(): BelongsToMany
  {
    return $this->belongsToMany(Reunion::class, 'reunion_tipo_usuario', 'tipo_usuario_id', 'reunion_id');
  }

  public function clasificacionAsistentes(): BelongsToMany
  {
    return $this->belongsToMany(
      ClasificacionAsistente::class,
      'clasificacion_asistente_tipo_usuario',
      'tipo_usuario_id',
      'clasificacion_asistente_id'
    )->withPivot('edad_minima', 'edad_maxima', 'paso_id', 'estado_paso', 'fecha_ingreso_igual_fecha_reporte', 'fecha_dado_alta_igual_fecha_reporte', 'fecha_paso_igual_fecha_reporte')
      ->withTimestamps();
  }

  public function rolDependiente(): BelongsTo
  {
    return $this->belongsTo(Role::class, 'id_rol_dependiente');
  }
}
