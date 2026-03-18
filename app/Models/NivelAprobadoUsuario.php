<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NivelAprobadoUsuario extends Model
{
    use HasFactory;

    protected $table = 'niveles_aprobado_usuario';

    protected $fillable = [
        'user_id',
        'nivel_id',
        'periodo_id',
        'aprobado',
        'nota_final',
        'es_homologacion',
        'observacion_homologacion',
        'sede_id',
        'fecha_homologacion',
        'homologado_por_user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(NivelEscuela::class, 'nivel_id');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }
}
