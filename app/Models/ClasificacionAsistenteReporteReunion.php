<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ClasificacionAsistenteReporteReunion extends Model
{
    use HasFactory;
    protected $table = 'clasificacion_asistente_reporte_reunion';
    protected $guarded = [];

    
    public function reporteReunion(): BelongsTo
    {
        return $this->belongsTo(ReporteReunion::class);
    }

    public function clasificacionAsistente(): BelongsTo
    {
        return $this->belongsTo(ClasificacionAsistente::class);
    }
}