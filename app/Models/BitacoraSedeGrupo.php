<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraSedeGrupo extends Model
{
    use HasFactory;

    protected $table = 'bitacora_sedes_del_grupo';

    protected $guarded = [];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function sedeAnterior(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_id_anterior');
    }

    public function sedeNueva(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_id_nuevo');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
