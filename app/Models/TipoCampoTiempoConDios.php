<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoCampoTiempoConDios extends Model
{
    use HasFactory;
    protected $table = 'tipos_campo_tiempo_con_dios';
    protected $guarded = [];

    public function campos(): HasMany
    {
      return $this->hasMany(CampoTiempoConDios::class, 'tipo_campo_tiempo_con_dios_id');
    }
}
