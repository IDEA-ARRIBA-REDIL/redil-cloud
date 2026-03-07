<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegranteGrupo extends Model
{
  use HasFactory;
  protected $table = 'integrantes_grupo';
  protected $guarded = [];
}
