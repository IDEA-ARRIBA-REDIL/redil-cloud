<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraSede extends Model
{
    use HasFactory;

    protected $table = 'bitacora_sedes';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
