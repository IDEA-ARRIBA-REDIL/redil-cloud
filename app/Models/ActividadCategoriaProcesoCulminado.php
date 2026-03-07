<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ActividadCategoriaProcesoCulminado extends Pivot
{
    protected $table = 'actividad_categoria_procesos_culminados';

    public function estadoPasoCrecimiento()
    {
        return $this->belongsTo(EstadoPasoCrecimientoUsuario::class, 'estado_paso_crecimiento_usuario_id');
    }
}
