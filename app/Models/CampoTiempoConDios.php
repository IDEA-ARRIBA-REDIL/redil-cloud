<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampoTiempoConDios extends Model
{
    use HasFactory;
    protected $table = 'campos_tiempo_con_dios';
    protected $guarded = [];

    public function tipo(): BelongsTo
    {
      return $this->belongsTo(TipoCampoTiempoConDios::class, 'tipo_campo_tiempo_con_dios_id');
    }

    public function tiempoConDios(): BelongsToMany
    {
      return $this->belongsToMany(TiempoConDios::class, 'campo_tiempo_con_dios', 'campo_tiempo_con_dios_id','tiempo_con_dios_id')
      ->withPivot('valor')
      ->withTimestamps();
    }



}
