<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genero extends Model
{
  use HasFactory;

  protected $fillable = ['nombre'];

  public function reuniones()
  {
    return $this->belongsToMany(Reunion::class, 'reuniones_generos');
  }
}
