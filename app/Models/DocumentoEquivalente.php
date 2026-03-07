<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentoEquivalente extends Model
{
  use HasFactory;

  protected $table = 'documento_equivalente';

  protected $fillable = [
    'nombre',
    'descripcion',
  ];

  public function egresos()
  {
    return $this->hasMany(Egreso::class);
  }
}
