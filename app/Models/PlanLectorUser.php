<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanLectorUser extends Model
{
    use HasFactory;

    protected $table = 'plan_lector_users';

    protected $fillable = [
        'plan_lector_id',
        'user_id',
        'estado',
        'fecha_inscripcion',
        'porcentaje_progreso',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'datetime',
    ];

    /**
     * El plan lector al que pertenece esta inscripción.
     */
    public function planLector(): BelongsTo
    {
        return $this->belongsTo(PlanLector::class, 'plan_lector_id');
    }

    /**
     * El usuario inscrito en el plan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
