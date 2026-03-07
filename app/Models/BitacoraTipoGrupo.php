<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraTipoGrupo extends Model
{
    use HasFactory;

    protected $table = 'bitacora_tipos_grupo';

    protected $guarded = [];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function tipoGrupoAnterior(): BelongsTo
    {
        return $this->belongsTo(TipoGrupo::class, 'tipo_grupo_id_anterior');
    }

    public function tipoGrupoNuevo(): BelongsTo
    {
        return $this->belongsTo(TipoGrupo::class, 'tipo_grupo_id_nuevo');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
