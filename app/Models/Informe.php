<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Informe extends Model
{
  use HasFactory;
  protected $table = 'informes';
  protected $guarded = [];

  public function tipoInforme(): BelongsTo
  {
      return $this->belongsTo(TipoInforme::class, 'tipo_informe_id');
  }

  public function roles(): BelongsToMany
  {
      return $this->belongsToMany(
          Role::class,       // El modelo con el que se relaciona
          'informe_rol',     // El nombre de tu tabla pivote
          'informe_id',      // La clave foránea de este modelo en la tabla pivote
          'rol_id'           // La clave foránea del modelo relacionado en la tabla pivote
      );
  }
}
