<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvanceHabitoRv extends Model
{
    use HasFactory;

    protected $table = 'avance_habito_rv';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'periodo_inicio' => 'date',
            'puntaje' => 'integer',
        ];
    }

    public function habito(): BelongsTo
    {
        return $this->belongsTo(HabitoUsuarioRv::class, 'habito_usuario_rv_id');
    }
}
