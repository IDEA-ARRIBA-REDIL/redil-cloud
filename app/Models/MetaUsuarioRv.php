<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaUsuarioRv extends Model
{
    use HasFactory;

    protected $table = 'metas_usuario_rv';

    protected $guarded = [];

    public function ruedaDeLaVida(): BelongsTo
    {
        return $this->belongsTo(RuedaDeLaVidaUser::class, 'rueda_de_la_vida_id');
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(SeccionRv::class, 'seccion_rv_id');
    }

    public function habitos(): HasMany
    {
        return $this->hasMany(HabitoUsuarioRv::class, 'meta_usuario_rv_id');
    }
}
