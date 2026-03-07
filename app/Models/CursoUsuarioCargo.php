<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CursoUsuarioCargo extends Pivot
{
    protected $table = 'curso_usuario_cargo';

    protected $fillable = [
        'curso_id',
        'usuario_id',
        'tipo_cargo_curso_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function tipoCargo(): BelongsTo
    {
        return $this->belongsTo(TipoCargoCurso::class, 'tipo_cargo_curso_id');
    }
}
