<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Abono extends Model
{

  use HasFactory;
  protected $table = 'abonos';
  protected $guarded = [];



  public function abonoCategorias()
  {
    return $this->hasMany(AbonoCategoria::class);
  }

  public function categorias(): BelongsToMany
  {
    return $this->belongsToMany(ActividadCategoria::class, 'abono_categoria', 'abono_id', 'actividad_categoria_id')->withPivot(
      'created_at',
      'updated_at',
      'valor',
      'moneda_id'
    );
  }
}
