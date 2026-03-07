<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoParentesco extends Model
{
  use HasFactory;
  protected $table = 'tipos_parentesco';
  protected $guarded = [];
}
