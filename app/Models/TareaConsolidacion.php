<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TareaConsolidacion extends Model
{
  use HasFactory;

  /**
   * El nombre de la tabla asociada con el modelo.
   *
   * @var string
   */
  protected $table = 'tareas_consolidacion';

  /**
   * Los atributos que se pueden asignar masivamente.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'nombre',
    'descripcion',
    'orden',
    'default', // Añadido el nuevo campo
  ];

  /**
   * Los atributos que deben ser convertidos a tipos nativos.
   * Esto asegura que 'default' siempre sea un booleano (true/false).
   *
   * @var array
   */
  protected $casts = [
    'default' => 'boolean',
  ];


  public function usuarios(): BelongsToMany
  {

    $usuario->load(
      'asignaciones.tareaConsolidacion',
      'asignaciones.estado',
      'asignaciones.historial'
    );

    return $this->belongsToMany(User::class, 'tarea_consolidacion_usuario', 'tarea_consolidacion_id', 'user_id')
      ->using(TareaConsolidacionUsuario::class)
      ->withPivot('id', 'estado_tarea_consolidacion_id', 'fecha')
      ->withTimestamps();
  }

  public function tipoConsejerias(): BelongsToMany
  {
      return $this->belongsToMany(TipoConsejeria::class, 'tarea_consolidacion_tipo_consejeria', 'tarea_consolidacion_id', 'tipo_consejeria_id');
  }

  // Relaciones con Cursos (LMS)

  public function cursosRequisito(): BelongsToMany
  {
      return $this->belongsToMany(Curso::class, 'curso_tarea_requisito', 'tarea_consolidacion_id', 'curso_id')
          ->withPivot('estado_tarea_consolidacion_id', 'indice');
  }

  public function cursosCulminar(): BelongsToMany
  {
      return $this->belongsToMany(Curso::class, 'curso_tarea_culminar', 'tarea_consolidacion_id', 'curso_id')
          ->withPivot('estado_tarea_consolidacion_id', 'indice');
  }
}
