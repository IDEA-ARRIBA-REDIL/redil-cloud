<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphByMany;

class PasoCrecimiento extends Model
{
  use HasFactory;
  protected $table = 'pasos_crecimiento';
  protected $guarded = [];

  public function usuarios(): BelongsToMany
  {
    return $this->belongsToMany(User::class, 'crecimiento_usuario', 'paso_crecimiento_id', 'user_id')->withPivot(
      'estado_id',
      'fecha',
      'detalle',
      'created_at',
      'updated_at'
    );
  }

  public function seccion(): BelongsTo
  {
    return $this->belongsTo(SeccionPasoCrecimiento::class);
  }

  public function roles(): BelongsToMany
  {
    return $this->belongsToMany(Role::class, 'privilegios_pasos_crecimiento_roles', 'paso_crecimiento_id', 'rol_id')->withPivot(
      'created_at',
      'updated_at'
    );
  }


  public function actividadesRequisito(): BelongsToMany
  {
    return $this->belongsToMany(Actividad::class,  'actividad_procesos_requisito',  'paso_crecimiento_id','actividad_id')->withPivot(
      'created_at',
      'updated_at',
      'estado',
      'indice'
    );
  }

  public function actividadesCulminados(): BelongsToMany
  {
    return $this->belongsToMany(Actividad::class,  'actividad_procesos_culminados',  'paso_crecimiento_id','actividad_id')->withPivot(
      'created_at',
      'updated_at',
      'estado',
      'indice'
    );
  }
    public function materiasRequeridas()
  {
      return $this->morphedByMany(Materia::class, 'prerequisiteable', 'prerequisito_pasos');
  }

  public function nivelesRequeridos()
  {
      return $this->morphedByMany(Nivel::class, 'prerequisiteable', 'prerequisito_pasos');
  }

}
