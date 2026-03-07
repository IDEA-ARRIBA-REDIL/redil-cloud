<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class TiempoConDios extends Model
{
    use HasFactory;
    protected $table = 'tiempos_con_dios';
    protected $guarded = [];

    public function usuarios(): BelongsTo
    {
      return $this->belongsTo(User::class);
    }

    public function campos(): BelongsToMany
    {
      return $this->belongsToMany(CampoTiempoConDios::class, 'campo_tiempo_con_dios', 'tiempo_con_dios_id', 'campo_tiempo_con_dios_id')
      ->withPivot('valor')
      ->withTimestamps();
    }

}
