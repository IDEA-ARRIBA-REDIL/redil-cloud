<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrupoDeGrupo extends Model
{
    use HasFactory;

    protected $table = 'grupos_de_grupos';

    protected $fillable = [
        'grupo_padre',
        'tipo_grupo_id_padre',
        'grupo_hijo',
        'tipo_grupo_id_hijo',
    ];

    public function grupoPadre(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_padre');
    }

    public function grupoHijo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_hijo');
    }

    public function tipoGrupoPadre(): BelongsTo
    {
        return $this->belongsTo(TipoGrupo::class, 'tipo_grupo_id_padre');
    }

    public function tipoGrupoHijo(): BelongsTo
    {
        return $this->belongsTo(TipoGrupo::class, 'tipo_grupo_id_hijo');
    }
}
