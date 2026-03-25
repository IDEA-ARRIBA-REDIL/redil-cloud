<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraIntegranteGrupo extends Model
{
    use HasFactory;

    protected $table = 'bitacora_integrantes_grupo';

    protected $fillable = [
        'grupo_id',
        'user_id',
        'estado_vinculacion',
        'autor_id',
    ];

    protected $casts = [
        'estado_vinculacion' => 'boolean',
    ];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
