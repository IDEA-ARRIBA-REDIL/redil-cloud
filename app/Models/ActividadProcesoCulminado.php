<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ActividadProcesoCulminado extends Pivot
{
    protected $table = 'actividad_procesos_culminados';

    public function estadoPasoCrecimiento()
    {
        return $this->belongsTo(EstadoPasoCrecimientoUsuario::class, 'estado_paso_crecimiento_usuario_id');
    }
}
