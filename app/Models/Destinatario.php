<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Destinatario extends Model
{
    use HasFactory;

    protected $table = 'destinatarios';

    protected $guarded = [];

    public function actividadDestinatario()
    {
        return $this->belongsToMany(Activdad::class, 'actividad_destinatarios', 'destinatario_id', 'actividad_id')->withPivot(
            'created_at',
            'updated_at'
        );
    }

    public function tiposOfrenda(): BelongsToMany
    {
        return $this->belongsToMany(
            TipoOfrenda::class,
            'destinatario_tipo_ofrenda',
            'destinatario_id',
            'tipo_ofrenda_id'
        )->withTimestamps();
    }
}
