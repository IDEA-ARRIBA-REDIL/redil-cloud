<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoServicioReporteReunion extends Model
{
    use HasFactory;

    protected $table = 'tipo_servicios_reporte_reunion';

    protected $fillable = [
        'nombre',
    ];

    public function reuniones(): HasMany
    {
        return $this->hasMany(Reunion::class, 'tipo_servicio_reporte_reunion_id');
    }
}
