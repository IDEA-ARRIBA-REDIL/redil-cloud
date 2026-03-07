<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorteMateriaPeriodo extends Model
{
    use HasFactory;
   protected $table = 'corte_materia_periodo';
    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'materia_periodo_id',
        'corte_periodo_id',
        'porcentaje',
        'cerrado',
    ];

    /**
     * Una relación pertenece a una materia-periodo.
     */
    public function materiaPeriodo()
    {
        return $this->belongsTo(MateriaPeriodo::class);
    }

    /**
     * Una relación pertenece a un corte-periodo.
     */
    public function cortePeriodo()
    {
        return $this->belongsTo(CortePeriodo::class);
    }
}