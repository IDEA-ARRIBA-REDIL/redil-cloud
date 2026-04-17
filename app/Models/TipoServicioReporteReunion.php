<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoServicioReporteReunion extends Model
{
    use HasFactory;

    protected $table = 'tipo_servicios_reporte_reunion';

    protected $fillable = [
        'nombre',
    ];
}
