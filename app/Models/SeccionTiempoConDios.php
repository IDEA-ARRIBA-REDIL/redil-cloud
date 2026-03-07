<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class SeccionTiempoConDios extends Model
{
    use HasFactory;
    protected $table = 'secciones_tiempo_con_dios';
    protected $guarded = [];

    public function campos(): HasMany
    {
      return $this->hasMany(CampoTiempoConDios::class);
    }

}
