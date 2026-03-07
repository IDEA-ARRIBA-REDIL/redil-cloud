<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampoExtraActividad extends Model
{
  use HasFactory;
  protected $table = 'campos_adicionales_actividad';
  protected $guarded = [];
}
