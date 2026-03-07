<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ocupacion extends Model
{
  use HasFactory;
  protected $table = 'ocupaciones';
  protected $guarded = [];
  use SoftDeletes;

  public function usuarios(): HasMany
  {
    return $this->hasMany(User::class);
  }
}
