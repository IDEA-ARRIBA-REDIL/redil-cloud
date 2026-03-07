<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoInasistencia extends Model
{
  use HasFactory;
  protected $table = 'tipo_inasistencias';
  protected $guarded = [];
}
