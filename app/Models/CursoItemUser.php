<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CursoItemUser extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'curso_item_user';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'curso_item_id',
        'user_id',
        'estado', // Puede ser 'iniciado' o 'completado'
        'fecha_completado'
    ];

    // Casteo de fechas para que Laravel las trate como instancias de Carbon
    protected $casts = [
        'fecha_completado' => 'datetime',
    ];

    /**
     * Relación: Este registro de progreso pertenece a un ítem de curso específico.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(CursoItem::class, 'curso_item_id');
    }

    /**
     * Relación: Este registro de progreso pertenece a un estudiante (usuario).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
