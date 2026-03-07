<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CampoExtra extends Model
{
  use HasFactory;
  protected $table = 'campos_extra';
  protected $guarded = [];

  public function roles(): BelongsToMany
  {
    return $this->belongsToMany(
      Role::class,
      'campo_extra_rol_autogestion',
      'campo_extra_id',
      'rol_id'
    )->withPivot(
      'created_at',
      'updated_at',
      'requerido'
    );
  }
}
