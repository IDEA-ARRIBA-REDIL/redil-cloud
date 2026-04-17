<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RegistroIglesiaInfantil extends Model
{
    use HasFactory;

    protected $table = 'registros_iglesia_infantil';

    protected $guarded = [];

    /** 1. Reporte de reunión al que pertenece este registro */
    public function reporteReunion(): BelongsTo
    {
        return $this->belongsTo(ReporteReunion::class, 'reporte_reunion_id');
    }

    /** 2. El menor registrado */
    public function menor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'menor_user_id');
    }

    /** 3. El adulto que entregó al menor */
    public function adultoIngreso(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adulto_ingreso_user_id');
    }

    /** 4. El adulto que retiró al menor (null hasta la entrega) */
    public function adultoRetiro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adulto_retiro_user_id');
    }

    /** 5. El servidor de staff que registró la entrada */
    public function servidorIngreso(): BelongsTo
    {
        return $this->belongsTo(User::class, 'servidor_ingreso_user_id');
    }

    /** 6. El servidor de staff que procesó la salida (null hasta la entrega) */
    public function servidorRetiro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'servidor_retiro_user_id');
    }

    /** 7. Salón asignado al menor */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(SalonInfantil::class, 'salon_infantil_id');
    }

    /** 8. Estación asignada al menor dentro del salón */
    public function estacion(): BelongsTo
    {
        return $this->belongsTo(EstacionSalonInfantil::class, 'estacion_salon_infantil_id');
    }

    /**
     * 9. Genera un código de retiro único de 6 caracteres alfanuméricos (mayúsculas + números)
     *    para un reporte de reunión específico. Reintenta hasta encontrar uno sin colisión.
     */
    public static function generarCodigoRetiro(int $reporteReunionId): string
    {
        do {
            $codigo = strtoupper(Str::random(6));
            $existe = static::where('reporte_reunion_id', $reporteReunionId)
                ->where('codigo_retiro', $codigo)
                ->exists();
        } while ($existe);

        return $codigo;
    }

    /** 10. Verifica si el registro está en estado "en custodia" */
    public function estaEnCustodia(): bool
    {
        return $this->estado === 'en_custodia';
    }

    /** 11. Verifica si el registro ya fue entregado */
    public function fueEntregado(): bool
    {
        return $this->estado === 'entregado';
    }
}
