<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoSede extends Model
{
  use HasFactory;
  protected $table = 'tipo_sedes';
  protected $guarded = [];
}
