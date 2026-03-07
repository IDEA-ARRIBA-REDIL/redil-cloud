<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CursoUser extends Model
{
    use HasFactory;

    protected $table = 'curso_users';

    protected $fillable = [
        'curso_id',
        'user_id',
        'estado',
        'fecha_inscripcion',
        'fecha_vencimiento_acceso',
        'porcentaje_progreso'
    ];

    protected $casts = [
        'fecha_inscripcion' => 'datetime',
        'fecha_vencimiento_acceso' => 'datetime',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
