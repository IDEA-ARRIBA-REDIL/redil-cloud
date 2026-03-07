<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteGrupoBajaAlta extends Model
{
    use HasFactory;
    protected $table = 'reportes_grupo_bajas_altas';
    protected $guarded = [];

    public function grupo(): BelongsTo
    {
      return $this->belongsTo(Grupo::class);
    }


}
