<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiltroConsolidacion extends Model
{
  use HasFactory;

  /**
   * El nombre de la tabla asociada con el modelo.
   *
   * @var string
   */
  protected $table = 'filtros_consolidacion';

  /**
   * Los atributos que se pueden asignar masivamente.
   * Esto es una medida de seguridad para proteger contra la asignación masiva no deseada.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'nombre',
    'descripcion',
    'orden',
  ];

  public function condiciones()
  {
    return $this->belongsToMany(TareaConsolidacion::class, 'filtro_tarea_estado')
      ->withPivot('estado_tarea_consolidacion_id', 'incluir');
  }

  public function estadosCiviles()
  {
      return $this->belongsToMany(
          EstadoCivil::class,
          'estado_civil_filtro_consolidacion',
          'filtro_consolidacion_id',
          'estado_civil_id'
      );
  }
}
