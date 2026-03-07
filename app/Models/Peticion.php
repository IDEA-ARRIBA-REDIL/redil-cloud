<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peticion extends Model
{
  use HasFactory;
  protected $table = 'peticiones';
  protected $guarded = [];

  public function usuario(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function tipoPeticion(): BelongsTo
  {
    return $this->belongsTo(TipoPeticion::class);
  }

  public function autorCreacion(): BelongsTo
  {
    return $this->belongsTo(User::class, 'autor_creacion_id');
  }

  public function seguimientos()
  {
    return $this->hasMany(SeguimientoPeticion::class);
  }

  public function pais(): BelongsTo
  {
    return $this->belongsTo(Pais::class, 'pais_id');
  }
}
