<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanLectorDiaUser extends Model
{
    use HasFactory;

    protected $table = 'plan_lector_dia_users';

    protected $fillable = [
        'plan_lector_dia_id',
        'user_id',
        'fecha_completado',
    ];

    protected $casts = [
        'fecha_completado' => 'datetime',
    ];

    /**
     * El día del plan lector que se completó.
     */
    public function planLectorDia(): BelongsTo
    {
        return $this->belongsTo(PlanLectorDia::class, 'plan_lector_dia_id');
    }

    /**
     * El usuario que completó el día.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
