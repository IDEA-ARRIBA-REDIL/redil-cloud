<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrecimientoUsuario extends Model
{
  use HasFactory;
  protected $table = 'crecimiento_usuario';
  protected $guarded = [];

  public function estado(): BelongsTo
  {
    return $this->belongsTo(EstadoPasoCrecimientoUsuario::class, 'estado_id');
  }
}
