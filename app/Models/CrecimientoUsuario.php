<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CrecimientoUsuario extends Pivot
{
    use HasFactory;

    protected $table = 'crecimiento_usuario';

    protected $guarded = [];

    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoPasoCrecimientoUsuario::class, 'estado_id');
    }

    protected static function booted()
    {
        static::created(function ($crecimiento) {
            $user = User::find($crecimiento->user_id);
            BitacoraCrecimientoUsuario::create([
                'user_id' => $crecimiento->user_id,
                'paso_crecimiento_id' => $crecimiento->paso_crecimiento_id,
                'estado_id_anterior' => null,
                'estado_id_nuevo' => $crecimiento->estado_id,
                'autor_id' => auth()->id(),
                'sede_id' => $user?->sede_id,
                'fecha' => $crecimiento->fecha ?? now(),
                'detalle' => $crecimiento->detalle,
            ]);
        });

        static::updated(function ($crecimiento) {
            if ($crecimiento->isDirty('estado_id')) {
                $user = User::find($crecimiento->user_id);
                BitacoraCrecimientoUsuario::create([
                    'user_id' => $crecimiento->user_id,
                    'paso_crecimiento_id' => $crecimiento->paso_crecimiento_id,
                    'estado_id_anterior' => $crecimiento->getOriginal('estado_id'),
                    'estado_id_nuevo' => $crecimiento->estado_id,
                    'autor_id' => auth()->id(),
                    'sede_id' => $user?->sede_id,
                    'fecha' => $crecimiento->fecha ?? now(),
                    'detalle' => $crecimiento->detalle,
                ]);
            }
        });
    }
}
