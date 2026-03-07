<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraEstadoCivil extends Model
{
    use HasFactory;

    protected $table = 'bitacora_estados_civiles';

    protected $fillable = [
        'user_id',
        'estado_civil_id_anterior',
        'estado_civil_id_nuevo',
        'autor_id'
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estadoCivilAnterior(): BelongsTo
    {
        return $this->belongsTo(EstadoCivil::class, 'estado_civil_id_anterior');
    }

    public function estadoCivilNuevo(): BelongsTo
    {
        return $this->belongsTo(EstadoCivil::class, 'estado_civil_id_nuevo');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
