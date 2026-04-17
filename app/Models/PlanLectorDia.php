<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanLectorDia extends Model
{
    use HasFactory;

    protected $table = 'plan_lector_dias';

    protected $fillable = [
        'plan_lector_id',
        'dia',
        'titulo',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanLector::class, 'plan_lector_id');
    }

    public function contenidos(): HasMany
    {
        return $this->hasMany(PlanLectorContenido::class, 'plan_lector_dia_id');
    }

    /**
     * Los usuarios que ya han marcado este día como completado.
     */
    public function usuariosCompletados(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'plan_lector_dia_users', 'plan_lector_dia_id', 'user_id')
                    ->withPivot('fecha_completado')
                    ->withTimestamps();
    }
}
