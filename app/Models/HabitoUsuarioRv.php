<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HabitoUsuarioRv extends Model
{
    use HasFactory;

    protected $table = 'habitos_usuario_rv';

    protected $guarded = [];

    public function meta(): BelongsTo
    {
        return $this->belongsTo(MetaUsuarioRv::class, 'meta_usuario_rv_id');
    }

    public function avances(): HasMany
    {
        return $this->hasMany(AvanceHabitoRv::class, 'habito_usuario_rv_id')->orderBy('periodo_inicio', 'asc');
    }
}
